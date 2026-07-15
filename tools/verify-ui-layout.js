const fs = require("fs");
const path = require("path");
const { chromium } = require("playwright");

function readArg(name, fallback) {
  const prefix = `--${name}=`;
  const arg = process.argv.find((item) => item.startsWith(prefix));
  return arg ? arg.slice(prefix.length) : fallback;
}

function slug(value) {
  return value.replace(/[^a-z0-9]+/gi, "-").replace(/^-|-$/g, "").toLowerCase();
}

const baseUrl = readArg("base", "http://127.0.0.1:18170");
const baseOrigin = new URL(baseUrl).origin;
const outputDir = path.resolve(readArg("out", path.join("output", "playwright", "layout")));
const username = readArg("username", "");
const password = readArg("password", "");

const routes = [
  { name: "import-dashboard", path: "/import/default/dashboard" },
  { name: "import-upload-index", path: "/import/upload/index" },
  { name: "import-upload-view", path: "/import/upload/view?id=1" },
  { name: "import-count-file", path: "/import/count-file/index" },
  { name: "qc-dashboard", path: "/qc/default/index" },
  { name: "hdc-index", path: "/hdc/default/index" },
  { name: "hdc-report-group", path: `/hdc/default/report-group?cat_id=03b912ab9ccb4c07280a89bf05e5900e&cat_name=${encodeURIComponent("ข้อมูลเพื่อตอบสนอง Service Plan สาขา RDU")}` },
  { name: "hdcex-index", path: "/hdcex/default/index" },
  { name: "hdcex-report-list", path: `/hdcex/default/report-list?cat_id=1ed90bc32310b503b7ca9b32af425ae5&cat_name=${encodeURIComponent("อนามัยแม่และเด็ก")}` },
  { name: "plugin-index", path: "/plugin/default/index" },
  { name: "qof-dashboard", path: "/Qof/default/index" },
  { name: "frontend-login", path: "/site/login" },
  { name: "frontend-user-login", path: "/user/login" },
  { name: "protected-student-redirect", path: "/student/default/index" },
];

const authenticatedRoutes = [
  { name: "auth-hdc-report-id", path: `/hdc/default/report-id?id=1125b85d4faa63e6769794336caed049&rpt=${encodeURIComponent("ร้อยละของเด็กวัยเรียน (6-14 ปี) มีส่วนสูงระดับดีและรูปร่างสมส่วน")}` },
  { name: "auth-hdcex-report-id", path: `/hdcex/default/report-id?ex_id=04c9d8ec0f34d6206bac042033e166b5&title=${encodeURIComponent("รายชื่อผู้ป่วยกลุ่ม4_Scoreตั้งแต่30และน้อยกว่า40")}` },
  { name: "auth-ehr-index", path: "/ehr/default/index" },
  { name: "auth-vaccine-index", path: "/vaccine/default/index" },
  { name: "auth-special-index", path: "/special/default/index" },
  { name: "auth-sqlquery-index", path: "/sqlquery/default/index" },
  { name: "auth-gis-index", path: "/gis/default/index" },
  { name: "auth-population-index", path: "/population/default/index" },
  { name: "auth-population-map", path: "/population/default/map" },
  { name: "auth-hrp-index", path: "/hrp/hrpinput/index" },
  { name: "auth-student-index", path: "/student/default/index" },
  { name: "auth-tbmaps-index", path: "/Tbmaps/map/index" },
];

const viewports = [
  { name: "mobile", width: 390, height: 844 },
  { name: "tablet", width: 768, height: 1024 },
  { name: "desktop", width: 1366, height: 900 },
  { name: "wide", width: 1440, height: 1000 },
];

async function loginFrontend(page) {
  const response = await page.goto(`${baseUrl}/user/login`, { waitUntil: "domcontentloaded", timeout: 45000 });
  await page.waitForLoadState("networkidle", { timeout: 8000 }).catch(() => {});
  const status = response ? response.status() : 0;
  if (status < 200 || status >= 400) {
    throw new Error(`login page returned HTTP ${status}`);
  }

  await page.locator('input[name="login-form[login]"]').fill(username);
  await page.locator('input[name="login-form[password]"]').fill(password);
  const remember = page.locator('input[name="login-form[rememberMe]"]');
  if (await remember.count()) {
    await remember.first().check().catch(() => {});
  }

  await Promise.all([
    page.waitForNavigation({ waitUntil: "networkidle", timeout: 45000 }).catch(() => null),
    page.locator('button[type="submit"], input[type="submit"]').first().click(),
  ]);
  await page.waitForLoadState("networkidle", { timeout: 8000 }).catch(() => {});

  const bodyText = await page.locator("body").innerText({ timeout: 10000 });
  if (/Incorrect username or password|login-form/i.test(bodyText) && /\/user\/login/i.test(page.url())) {
    throw new Error("frontend login failed or remained on login page");
  }
}

async function gotoPage(page, url) {
  const response = await page.goto(url, { waitUntil: "domcontentloaded", timeout: 45000 });
  await page.waitForLoadState("networkidle", { timeout: 8000 }).catch(() => {});
  return response;
}

async function inspectPage(page) {
  return page.evaluate(() => {
    const doc = document.documentElement;
    const body = document.body;
    const viewportWidth = window.innerWidth;
    const scrollWidth = Math.max(doc.scrollWidth, body.scrollWidth);
    const horizontalOverflow = Math.max(0, scrollWidth - viewportWidth);
    const selectors = [
      ".wrap",
      ".mui-web-page-container",
      ".navbar-custom",
      ".footer",
      ".dhdc-page-header",
      ".dhdc-stat-grid",
      ".dhdc-app-grid",
      ".dhdc-link-grid",
      ".panel",
      ".well",
      ".grid-view",
      ".table-responsive",
      ".dhdc-panel",
      ".dhdc-login-shell",
    ];
    const escaped = [];

    document.querySelectorAll(selectors.join(",")).forEach((element) => {
      const rect = element.getBoundingClientRect();
      const width = Math.round(rect.width);
      const left = Math.round(rect.left);
      const right = Math.round(rect.right);
      if (width <= 0) {
        return;
      }
      if (left < -2 || right > viewportWidth + 2) {
        escaped.push({
          selector: element.className || element.tagName,
          left,
          right,
          width,
        });
      }
    });

    return {
      title: document.title,
      url: window.location.href,
      viewportWidth,
      scrollWidth,
      horizontalOverflow,
      hasMuiScope: body.classList.contains("mui-web-scope"),
      hasMuiAsset: Boolean(document.querySelector('link[href*="mui-web/"]')),
      hasYiiError: /Database Exception|PHP Warning|PHP Notice|Fatal error/i.test(body.innerText),
      escaped,
    };
  });
}

(async () => {
  fs.mkdirSync(outputDir, { recursive: true });
  const browser = await chromium.launch();
  const failures = [];
  const results = [];

  try {
    for (const viewport of viewports) {
      const context = await browser.newContext({
        viewport: { width: viewport.width, height: viewport.height },
        deviceScaleFactor: 1,
      });
      await context.route("**/*", (route) => {
        const requestUrl = route.request().url();
        const protocol = new URL(requestUrl).protocol;
        if (protocol === "data:" || protocol === "blob:" || new URL(requestUrl).origin === baseOrigin) {
          return route.continue();
        }
        return route.abort();
      });
      const page = await context.newPage();

      for (const route of routes) {
        const url = `${baseUrl}${route.path}`;
        const response = await gotoPage(page, url);
        const status = response ? response.status() : 0;
        const metrics = await inspectPage(page);
        const screenshot = path.join(outputDir, `${route.name}-${viewport.name}.png`);
        await page.screenshot({ path: screenshot, fullPage: true });

        const item = {
          route: route.name,
          viewport: viewport.name,
          status,
          screenshot,
          ...metrics,
        };
        results.push(item);

        if (status < 200 || status >= 400) {
          failures.push(`${route.name}/${viewport.name}: HTTP ${status}`);
        }
        if (!metrics.hasMuiScope || !metrics.hasMuiAsset) {
          failures.push(`${route.name}/${viewport.name}: mui-web scope or asset missing`);
        }
        if (metrics.hasYiiError) {
          failures.push(`${route.name}/${viewport.name}: Yii/PHP error text rendered`);
        }
        if (metrics.horizontalOverflow > 2) {
          failures.push(`${route.name}/${viewport.name}: body horizontal overflow ${metrics.horizontalOverflow}px`);
        }
        if (metrics.escaped.length > 0) {
          failures.push(`${route.name}/${viewport.name}: escaped layout elements ${JSON.stringify(metrics.escaped.slice(0, 3))}`);
        }
      }

      if (username && password) {
        try {
          await loginFrontend(page);
        } catch (error) {
          failures.push(`login/${viewport.name}: ${error.message}`);
        }

        for (const route of authenticatedRoutes) {
          const url = `${baseUrl}${route.path}`;
          const response = await gotoPage(page, url);
          const status = response ? response.status() : 0;
          const metrics = await inspectPage(page);
          const screenshot = path.join(outputDir, `${route.name}-${viewport.name}.png`);
          await page.screenshot({ path: screenshot, fullPage: true });

          const item = {
            route: route.name,
            viewport: viewport.name,
            status,
            authenticated: true,
            screenshot,
            ...metrics,
          };
          results.push(item);

          if (status < 200 || status >= 400) {
            failures.push(`${route.name}/${viewport.name}: HTTP ${status}`);
          }
          if (/\/user\/login/i.test(metrics.url)) {
            failures.push(`${route.name}/${viewport.name}: redirected to login after authentication`);
          }
          if (!metrics.hasMuiScope || !metrics.hasMuiAsset) {
            failures.push(`${route.name}/${viewport.name}: mui-web scope or asset missing`);
          }
          if (metrics.hasYiiError) {
            failures.push(`${route.name}/${viewport.name}: Yii/PHP error text rendered`);
          }
          if (metrics.horizontalOverflow > 2) {
            failures.push(`${route.name}/${viewport.name}: body horizontal overflow ${metrics.horizontalOverflow}px`);
          }
          if (metrics.escaped.length > 0) {
            failures.push(`${route.name}/${viewport.name}: escaped layout elements ${JSON.stringify(metrics.escaped.slice(0, 3))}`);
          }
        }
      }

      await context.close();
    }
  } finally {
    await browser.close();
  }

  const reportPath = path.join(outputDir, "layout-report.json");
  fs.writeFileSync(reportPath, JSON.stringify({ baseUrl, results, failures }, null, 2));
  console.log(`verify-ui-layout: wrote ${reportPath}`);

  if (failures.length > 0) {
    console.error(failures.join("\n"));
    process.exit(1);
  }

  console.log("verify-ui-layout: OK");
})();
