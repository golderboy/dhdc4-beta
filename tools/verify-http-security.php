<?php

$options = getopt('', [
    'frontend-url:',
    'backend-url::',
    'frontend-user-cookie::',
    'frontend-admin-cookie::',
    'backend-admin-cookie::',
    'timeout::',
]);

$frontendUrl = isset($options['frontend-url']) ? rtrim($options['frontend-url'], '/') : null;
$backendUrl = isset($options['backend-url']) ? rtrim($options['backend-url'], '/') : null;
$frontendUserCookie = isset($options['frontend-user-cookie']) ? (string)$options['frontend-user-cookie'] : '';
$frontendAdminCookie = isset($options['frontend-admin-cookie']) ? (string)$options['frontend-admin-cookie'] : '';
$backendAdminCookie = isset($options['backend-admin-cookie']) ? (string)$options['backend-admin-cookie'] : '';
$timeout = isset($options['timeout']) ? max(1, (int)$options['timeout']) : 10;

if (!$frontendUrl) {
    fwrite(STDERR, "Usage: php tools/verify-http-security.php --frontend-url=http://host [--backend-url=http://host]\n");
    exit(2);
}

$failures = [];

function fetchUrl(string $url, int $timeout, string $cookie = '', string $method = 'GET', string $body = ''): array
{
    $headers = "User-Agent: dhdc-security-smoke/1.0\r\n";
    if ($cookie !== '') {
        $headers .= "Cookie: $cookie\r\n";
    }
    if ($method === 'POST') {
        $headers .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $headers .= 'Content-Length: ' . strlen($body) . "\r\n";
    }

    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'ignore_errors' => true,
            'timeout' => $timeout,
            'max_redirects' => 0,
            'header' => $headers,
            'content' => $body,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    $headers = isset($http_response_header) ? $http_response_header : [];
    $status = 0;
    if (isset($headers[0]) && preg_match('/\s(\d{3})\s/', $headers[0], $matches)) {
        $status = (int)$matches[1];
    }

    $normalizedHeaders = [];
    foreach ($headers as $header) {
        $parts = explode(':', $header, 2);
        if (count($parts) === 2) {
            $name = strtolower(trim($parts[0]));
            $normalizedHeaders[$name][] = trim($parts[1]);
        }
    }

    return [
        'status' => $status,
        'body' => $body === false ? '' : $body,
        'headers' => $normalizedHeaders,
        'rawHeaders' => $headers,
    ];
}

function expectStatus(string $label, string $baseUrl, string $path, array $allowed, int $timeout, array &$failures, string $cookie = '', string $method = 'GET', string $body = ''): void
{
    $result = fetchUrl($baseUrl . $path, $timeout, $cookie, $method, $body);
    if (!in_array($result['status'], $allowed, true)) {
        $failures[] = "$label $method $path expected " . implode('/', $allowed) . ", got {$result['status']}";
        return;
    }

    echo "[OK] $label $method $path => {$result['status']}\n";
}

function expectHeader(string $label, array $result, string $header, string $contains, array &$failures): void
{
    $name = strtolower($header);
    $values = $result['headers'][$name] ?? [];
    $joined = implode(' ', $values);
    if ($joined === '' || stripos($joined, $contains) === false) {
        $failures[] = "$label header $header expected to contain '$contains'";
        return;
    }

    echo "[OK] $label header $header contains $contains\n";
}

function checkHeaders(string $label, string $baseUrl, int $timeout, array &$failures): void
{
    $result = fetchUrl($baseUrl . '/site/login', $timeout);
    if ($result['status'] !== 200) {
        $failures[] = "$label /site/login expected 200, got {$result['status']}";
        return;
    }

    expectHeader($label, $result, 'X-Frame-Options', 'SAMEORIGIN', $failures);
    expectHeader($label, $result, 'X-Content-Type-Options', 'nosniff', $failures);
    expectHeader($label, $result, 'Referrer-Policy', 'strict-origin-when-cross-origin', $failures);
    expectHeader($label, $result, 'Content-Security-Policy', "frame-ancestors 'self'", $failures);
}

function runStatusChecks(string $label, string $baseUrl, array $checks, int $timeout, array &$failures, string $cookie = ''): void
{
    foreach ($checks as $check) {
        $path = $check[0];
        $allowed = $check[1];
        $method = $check[2] ?? 'GET';
        $body = $check[3] ?? '';
        expectStatus($label, $baseUrl, $path, $allowed, $timeout, $failures, $cookie, $method, $body);
    }
}

checkHeaders('frontend', $frontendUrl, $timeout, $failures);

$frontendChecks = [
    ['/debug', [404]],
    ['/gii', [404]],
    ['/sqlquery/runquery/index', [302, 403]],
    ['/sqlquery/sqlscript/index', [302, 403]],
    ['/hdc/default/report-id?id=1125b85d4faa63e6769794336caed049&rpt=test', [302, 403]],
    ['/hdc/default/show-sql?id=1125b85d4faa63e6769794336caed049&rpt=test', [302, 403]],
    ['/hdcex/default/report-list?cat_id=test', [302, 403]],
    ['/hdcex/default/report-id?ex_id=test', [302, 403]],
    ['/ehr', [302, 403]],
    ['/import/upload/index', [302, 403]],
    ['/import/count-file/index', [302, 403]],
    ['/import/upload/detail?filename=test.zip', [302, 403]],
    ['/import/ajax/import-all?fortythree=test.zip&upload_date=2026-07-09&upload_time=120000', [302, 403, 405]],
    ['/import/ajax/truncate', [302, 403, 405]],
    ['/import/ajax/update', [302, 403, 405]],
    ['/import2/upload/index', [302, 403]],
    ['/import2/count-file/index', [302, 403]],
    ['/import2/upload/detail?filename=test.zip', [302, 403]],
    ['/import2/ajax/import-all?fortythree=test.zip&upload_date=2026-07-09&upload_time=120000', [302, 403, 405]],
    ['/import2/ajax/truncate', [302, 403, 405]],
    ['/import2/ajax/update', [302, 403, 405]],
    ['/exec/transform/setup', [302, 403, 405]],
    ['/exec/transform/exec', [302, 403, 405]],
    ['/exec/qc/exec', [302, 403, 405]],
    ['/exec/qc/truncate', [302, 403, 405]],
    ['/exec/default/check-process?p=err_all', [302, 403, 405]],
    ['/gate/default/index', [302, 403]],
    ['/gis/json/read?file=composer.json', [302, 403, 405]],
    ['/hrp/json/read?file=composer.json', [302, 403, 405]],
    ['/Tbmaps/json/read?file=composer.json', [302, 403, 405]],
    ['/lib/map/leaflet-search/examples/search.php', [404, 500]],
];

runStatusChecks('frontend', $frontendUrl, $frontendChecks, $timeout, $failures);

if ($frontendUserCookie !== '') {
    $frontendUserChecks = [
        ['/import/upload/index', [200]],
        ['/import/count-file/index', [200]],
        ['/import2/upload/index', [200]],
        ['/import2/count-file/index', [200]],
        ['/ehr', [200]],
        ['/sqlquery/sqlscript/index', [200]],
        ['/sqlquery/runquery/index', [403]],
        ['/hdc/default/show-sql?id=1125b85d4faa63e6769794336caed049&rpt=test', [403]],
        ['/hdcex/default/report-list?cat_id=test', [200]],
    ];
    runStatusChecks('frontend-user', $frontendUrl, $frontendUserChecks, $timeout, $failures, $frontendUserCookie);
}

if ($frontendAdminCookie !== '') {
    $frontendAdminChecks = [
        ['/sqlquery/runquery/index', [200]],
        ['/hdc/default/show-sql?id=1125b85d4faa63e6769794336caed049&rpt=test', [200]],
        ['/import/ajax/import-all?fortythree=test.zip&upload_date=2026-07-09&upload_time=120000', [405]],
        ['/import2/ajax/import-all?fortythree=test.zip&upload_date=2026-07-09&upload_time=120000', [405]],
        ['/exec/transform/exec', [405]],
        ['/exec/qc/truncate', [405]],
        ['/gis/json/read?file=composer.json', [405]],
        ['/hrp/json/read?file=composer.json', [405]],
        ['/Tbmaps/json/read?file=composer.json', [405]],
        ['/sqlquery/runquery/index', [400], 'POST', 'sql_code=select+1%3B'],
        ['/import/ajax/import-all', [400], 'POST', 'fortythree=fake.zip&upload_date=2026-07-09&upload_time=120000'],
        ['/import2/ajax/import-all', [400], 'POST', 'fortythree=fake.zip&upload_date=2026-07-09&upload_time=120000'],
    ];
    runStatusChecks('frontend-admin', $frontendUrl, $frontendAdminChecks, $timeout, $failures, $frontendAdminCookie);
}

if ($backendUrl) {
    checkHeaders('backend', $backendUrl, $timeout, $failures);

    $backendChecks = [
        ['/debug', [404]],
        ['/gii', [404]],
        ['/gate/default/index', [302, 403]],
        ['/exec/default/check-process?p=err_all', [302, 403, 405]],
        ['/exec/transform/setup', [302, 403, 405]],
        ['/exec/transform/exec', [302, 403, 405]],
        ['/exec/qc/exec', [302, 403, 405]],
        ['/exec/qc/truncate', [302, 403, 405]],
        ['/hdcreportsetup/hdcsql/index', [302, 403]],
        ['/hdcreportsetup/hdcsql/create', [302, 403]],
        ['/hdcreportsetup/hdcsql/export?id=test', [302, 403]],
    ];

    runStatusChecks('backend', $backendUrl, $backendChecks, $timeout, $failures);

    if ($backendAdminCookie !== '') {
        $backendAdminChecks = [
            ['/hdcreportsetup/hdcsql/index', [200]],
            ['/hdcreportsetup/hdcsql/create', [200]],
            ['/exec/transform/setup', [405]],
            ['/exec/transform/exec', [405]],
            ['/exec/qc/exec', [405]],
            ['/exec/qc/truncate', [405]],
            ['/hdcreportsetup/hdcsql/create', [400], 'POST', ''],
        ];
        runStatusChecks('backend-admin', $backendUrl, $backendAdminChecks, $timeout, $failures, $backendAdminCookie);
    }
}

if (!empty($failures)) {
    echo "\nHTTP security smoke failed:\n";
    foreach ($failures as $failure) {
        echo "- $failure\n";
    }
    exit(1);
}

echo "\nHTTP security smoke passed.\n";
