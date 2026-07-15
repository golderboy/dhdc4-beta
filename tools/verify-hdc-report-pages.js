const fs = require("fs");
const path = require("path");
const { spawnSync } = require("child_process");
const { chromium } = require("playwright");

function readArg(name, fallback) {
  const prefix = `--${name}=`;
  const arg = process.argv.find((item) => item.startsWith(prefix));
  return arg ? arg.slice(prefix.length) : fallback;
}

function slug(value) {
  return String(value).replace(/[^a-z0-9]+/gi, "-").replace(/^-|-$/g, "").toLowerCase() || "report";
}

function fetchReports(moduleName, limit) {
  const sql = moduleName === "hdcex"
    ? "SELECT ex_id AS id, title AS report_name FROM sys_data_exchange WHERE active=1 ORDER BY cat_id, weight, ex_id"
    : "SELECT r.id, r.report_name FROM sys_report_dhdc r INNER JOIN hdc_rpt_sql s ON s.rpt_id = r.id WHERE r.id NOT IN (SELECT id FROM sys_report_drop) ORDER BY r.report_id, r.id";
  const php = String.raw`
$root = getcwd();
require $root . '/common/config/connect_database.php';
$dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
$pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$sql = ${JSON.stringify(sql)};
$rows = $pdo->query($sql)->fetchAll();
echo json_encode($rows, JSON_UNESCAPED_UNICODE);
`;
  const result = spawnSync("php", ["-r", php], { encoding: "utf8", cwd: process.cwd() });
  if (result.status !== 0) {
    throw new Error(result.stderr || result.stdout || "Unable to fetch HDC reports");
  }
  const rows = JSON.parse(result.stdout);
  return limit > 0 ? rows.slice(0, limit) : rows;
}

function createTestSession(username) {
  const args = ["tools/create-yii-test-session.php"];
  if (username) {
    args.push(`--username=${username}`);
  }
  const result = spawnSync("php", args, { encoding: "utf8", cwd: process.cwd() });
  if (result.status !== 0) {
    throw new Error(result.stderr || result.stdout || "Unable to create Yii test session");
  }
  return JSON.parse(result.stdout);
}

async function gotoPage(page, url) {
  const response = await page.goto(url, { waitUntil: "domcontentloaded", timeout: 60000 });
  await page.waitForLoadState("networkidle", { timeout: 10000 }).catch(() => {});
  return response;
}

async function login(page, baseUrl, username, password) {
  const response = await gotoPage(page, `${baseUrl}/user/login`);
  const status = response ? response.status() : 0;
  if (status < 200 || status >= 400) {
    throw new Error(`login page returned HTTP ${status}`);
  }

  const loginField = page.locator('[name="login-form[login]"]').first();
  const tagName = await loginField.evaluate((element) => element.tagName.toLowerCase());
  if (tagName === "select") {
    await loginField.selectOption({ label: username }).catch(async () => {
      await loginField.selectOption(username);
    });
  } else {
    await loginField.fill(username);
  }

  const passwordField = page.locator('[name="login-form[password]"]').first();
  if ((await passwordField.count()) > 0) {
    await passwordField.fill(password);
  }

  const remember = page.locator('[name="login-form[rememberMe]"]').first();
  if ((await remember.count()) > 0) {
    await remember.check().catch(() => {});
  }

  await Promise.all([
    page.waitForNavigation({ waitUntil: "networkidle", timeout: 60000 }).catch(() => null),
    page.locator('button[type="submit"], input[type="submit"]').first().click(),
  ]);
  await page.waitForLoadState("networkidle", { timeout: 10000 }).catch(() => {});

  if (/\/user\/login|\/site\/login/i.test(page.url())) {
    throw new Error("login failed or remained on login page");
  }
}

async function inspectPage(page) {
  return page.evaluate(() => {
    const doc = document.documentElement;
    const body = document.body;
    const viewportWidth = window.innerWidth;
    const scrollWidth = Math.max(doc.scrollWidth, body.scrollWidth);
    const escaped = [];
    const selectors = [".wrap", ".mui-web-page-container", ".dhdc-page-header", ".dhdc-panel", ".grid-view", ".table-responsive", ".panel"];

    document.querySelectorAll(selectors.join(",")).forEach((element) => {
      const rect = element.getBoundingClientRect();
      if (rect.width <= 0) {
        return;
      }
      if (rect.left < -2 || rect.right > viewportWidth + 2) {
        escaped.push({
          selector: element.className || element.tagName,
          left: Math.round(rect.left),
          right: Math.round(rect.right),
          width: Math.round(rect.width),
        });
      }
    });

    const bodyText = body.innerText || "";
    const alertText = Array.from(document.querySelectorAll(".alert-danger")).map((item) => item.innerText.trim()).filter(Boolean).join(" | ");

    return {
      title: document.title,
      url: window.location.href,
      viewportWidth,
      scrollWidth,
      horizontalOverflow: Math.max(0, scrollWidth - viewportWidth),
      hasYiiError: /Database Exception|SQLSTATE|PHP Warning|PHP Notice|Fatal error/i.test(bodyText),
      hasReportShell: Boolean(document.querySelector(".dhdc-page-header, .hdc-report-header, .hdc-individual-panel, .hdc-summary-panel")),
      alertText,
      escaped,
    };
  });
}

(async () => {
  const baseUrl = readArg("base", "http://127.0.0.1:18170");
  const outputDir = path.resolve(readArg("out", path.join("output", "playwright", "hdc-report-pages")));
  const sampleHospcode = readArg("sample-hospcode", "06879");
  const moduleName = readArg("module", "hdc");
  const username = readArg("username", "");
  const password = readArg("password", "");
  const authSession = readArg("auth-session", "");
  const onlyId = readArg("only-id", "");
  const limit = Number(readArg("limit", "0")) || 0;
  const screenshotMode = readArg("screenshots", "failures");
  const fetchedReports = fetchReports(moduleName, 0);
  const filteredReports = onlyId ? fetchedReports.filter((report) => report.id === onlyId) : fetchedReports;
  const reports = limit > 0 ? filteredReports.slice(0, limit) : filteredReports;
  if (onlyId && reports.length === 0) {
    throw new Error(`No ${moduleName} report found for --only-id=${onlyId}`);
  }
  const viewports = [
    { name: "mobile", width: 390, height: 844 },
    { name: "tablet", width: 768, height: 1024 },
    { name: "desktop", width: 1366, height: 900 },
    { name: "wide", width: 1440, height: 1000 },
  ];

  fs.mkdirSync(outputDir, { recursive: true });

  const browser = await chromium.launch();
  const results = [];
  const failures = [];
  const session = authSession === "auto" ? createTestSession(username) : null;

  try {
    for (const viewport of viewports) {
      const context = await browser.newContext({
        viewport: { width: viewport.width, height: viewport.height },
        deviceScaleFactor: 1,
      });
      if (session) {
        await context.addCookies([{
          name: session.sessionName,
          value: session.sessionId,
          url: baseUrl,
          httpOnly: true,
        }]);
      }
      const page = await context.newPage();
      if (!session && username) {
        await login(page, baseUrl, username, password);
      }

      for (let index = 0; index < reports.length; index += 1) {
        const report = reports[index];
        const params = moduleName === "hdcex"
          ? new URLSearchParams({ ex_id: report.id, title: report.report_name || report.id, hospcode: sampleHospcode })
          : new URLSearchParams({ id: report.id, rpt: report.report_name || report.id, hospcode: sampleHospcode });
        const url = `${baseUrl}/${moduleName}/default/report-id?${params.toString()}`;
        const response = await gotoPage(page, url);
        const status = response ? response.status() : 0;
        const metrics = await inspectPage(page);
        const item = {
          index: index + 1,
          id: report.id,
          reportName: report.report_name,
          viewport: viewport.name,
          status,
          ...metrics,
        };

        const pageFailures = [];
        if (status < 200 || status >= 400) {
          pageFailures.push(`HTTP ${status}`);
        }
        if (/\/user\/login|\/site\/login/i.test(metrics.url)) {
          pageFailures.push("redirected to login");
        }
        if (!metrics.hasReportShell) {
          pageFailures.push("report shell missing");
        }
        if (metrics.hasYiiError || metrics.alertText) {
          pageFailures.push(`rendered error: ${metrics.alertText || "Yii/PHP error text"}`);
        }
        if (metrics.horizontalOverflow > 2) {
          pageFailures.push(`body horizontal overflow ${metrics.horizontalOverflow}px`);
        }
        if (metrics.escaped.length > 0) {
          pageFailures.push(`escaped layout elements ${JSON.stringify(metrics.escaped.slice(0, 3))}`);
        }

        if (pageFailures.length > 0) {
          const message = `${report.id}/${viewport.name}: ${pageFailures.join("; ")}`;
          failures.push(message);
          item.failures = pageFailures;
        }

        if (screenshotMode === "all" || (screenshotMode === "failures" && pageFailures.length > 0)) {
          const screenshot = path.join(outputDir, `${String(index + 1).padStart(3, "0")}-${slug(report.id)}-${viewport.name}.png`);
          await page.screenshot({ path: screenshot, fullPage: true });
          item.screenshot = screenshot;
        }

        results.push(item);
      }

      await context.close();
    }
  } finally {
    await browser.close();
  }

  const reportPath = path.join(outputDir, "hdc-report-pages.json");
  fs.writeFileSync(reportPath, JSON.stringify({
    baseUrl,
    module: moduleName,
    sampleHospcode,
    count: reports.length,
    authSession: session ? {
      sessionName: session.sessionName,
      userId: session.userId,
      username: session.username,
      canAccessUserRole: session.canAccessUserRole,
    } : null,
    results,
    failures,
  }, null, 2));
  console.log(`verify-hdc-report-pages: checked ${moduleName} ${reports.length} reports across ${viewports.length} viewports`);
  console.log(`verify-hdc-report-pages: wrote ${reportPath}`);

  if (failures.length > 0) {
    console.error(failures.slice(0, 50).join("\n"));
    if (failures.length > 50) {
      console.error(`... ${failures.length - 50} more failures`);
    }
    process.exit(1);
  }

  console.log("verify-hdc-report-pages: OK");
})();
