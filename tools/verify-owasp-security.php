<?php

$root = dirname(__DIR__);
$failures = [];
$releaseArtifact = in_array('--release-artifact', $argv, true);

$checks = [
    'frontend/web/index.php must not hard-code YII_DEBUG=true' => [
        'file' => 'frontend/web/index.php',
        'absent' => "define('YII_DEBUG', true)",
    ],
    'frontend debug/gii must be gated by YII_ENV_DEV' => [
        'file' => 'frontend/config/main-local.php',
        'present' => 'YII_ENV_DEV',
    ],
    'backend/web/index.php must not hard-code YII_DEBUG=true' => [
        'file' => 'backend/web/index.php',
        'absent' => "define('YII_DEBUG', true)",
    ],
    'backend debug/gii must be gated by YII_ENV_DEV' => [
        'file' => 'backend/config/main-local.php',
        'present' => 'YII_ENV_DEV',
    ],
    'Frontend cookie key must come from environment or ignored local file' => [
        'file' => 'frontend/config/main-local.php',
        'present' => "getenv('DHDC_FRONTEND_COOKIE_VALIDATION_KEY')",
    ],
    'Backend cookie key must come from environment or ignored local file' => [
        'file' => 'backend/config/main-local.php',
        'present' => "getenv('DHDC_BACKEND_COOKIE_VALIDATION_KEY')",
    ],
    'SQL runner must not create temporary procedures' => [
        'file' => 'modules/sqlquery/controllers/RunqueryController.php',
        'absent' => 'tmp_store_proc',
    ],
    'SQL runner must be admin-only' => [
        'file' => 'modules/sqlquery/controllers/RunqueryController.php',
        'present' => "'roles' => ['Admin']",
    ],
    'SQL runner security logging must be present' => [
        'file' => 'modules/sqlquery/controllers/RunqueryController.php',
        'present' => 'security.sqlquery',
    ],
    'Frontend response headers must be configured' => [
        'file' => 'frontend/config/main.php',
        'present' => 'Content-Security-Policy',
    ],
    'Backend response headers must be configured' => [
        'file' => 'backend/config/main.php',
        'present' => 'Content-Security-Policy',
    ],
    'Frontend HSTS must be configured for secure requests' => [
        'file' => 'frontend/config/main.php',
        'present' => 'Strict-Transport-Security',
    ],
    'Backend HSTS must be configured for secure requests' => [
        'file' => 'backend/config/main.php',
        'present' => 'Strict-Transport-Security',
    ],
    'Session SameSite must be configured' => [
        'file' => 'frontend/config/main.php',
        'present' => 'SAME_SITE_LAX',
    ],
    'Backend session SameSite must be configured' => [
        'file' => 'backend/config/main.php',
        'present' => 'SAME_SITE_LAX',
    ],
    'Frontend logs must not capture request superglobals' => [
        'file' => 'frontend/config/main.php',
        'present' => "'logVars' => []",
    ],
    'Backend logs must not capture request superglobals' => [
        'file' => 'backend/config/main.php',
        'present' => "'logVars' => []",
    ],
    'Import zip extraction must use safe entry list' => [
        'file' => 'frontend/modules/import/controllers/AjaxController.php',
        'present' => 'extractZipToSafeDir',
    ],
    'Import ajax must enforce HTTP verbs' => [
        'file' => 'frontend/modules/import/controllers/AjaxController.php',
        'present' => 'VerbFilter::className()',
    ],
    'Import security logging must be present' => [
        'file' => 'frontend/modules/import/controllers/AjaxController.php',
        'present' => 'security.import',
    ],
    'Import error logging must use bound params' => [
        'file' => 'frontend/modules/import/controllers/AjaxController.php',
        'present' => ':err',
    ],
    'Import2 zip extraction must use safe entry list' => [
        'file' => 'frontend/modules/import2/controllers/AjaxController.php',
        'present' => 'extractZipToSafeDir',
    ],
    'Import2 ajax must enforce HTTP verbs' => [
        'file' => 'frontend/modules/import2/controllers/AjaxController.php',
        'present' => 'VerbFilter::className()',
    ],
    'Import2 security logging must be present' => [
        'file' => 'frontend/modules/import2/controllers/AjaxController.php',
        'present' => 'security.import2',
    ],
    'Import2 error logging must use bound params' => [
        'file' => 'frontend/modules/import2/controllers/AjaxController.php',
        'present' => ':err',
    ],
    'EHR controller must keep CSRF enabled' => [
        'file' => 'modules/ehr/controllers/DefaultController.php',
        'absent' => 'enableCsrfValidation = false',
    ],
    'HDC report setup must keep CSRF enabled' => [
        'file' => 'backend/modules/hdcreportsetup/controllers/HdcsqlController.php',
        'absent' => 'enableCsrfValidation = false',
    ],
    'HDC report setup must use bound params' => [
        'file' => 'backend/modules/hdcreportsetup/controllers/HdcsqlController.php',
        'present' => ':report_name',
    ],
    'HDC data exchange report list must require access control' => [
        'file' => 'frontend/modules/hdcex/controllers/DefaultController.php',
        'present' => "'report-list'",
    ],
    'GIS JSON importer must require access control' => [
        'file' => 'modules/gis/controllers/JsonController.php',
        'present' => 'AccessControl::className()',
    ],
    'HRP JSON importer must require access control' => [
        'file' => 'modules/hrp/controllers/JsonController.php',
        'present' => 'AccessControl::className()',
    ],
    'TB maps JSON importer must require access control' => [
        'file' => 'modules/Tbmaps/controllers/JsonController.php',
        'present' => 'AccessControl::className()',
    ],
    'GIS map village lookup must use bound params' => [
        'file' => 'modules/gis/controllers/DefaultController.php',
        'present' => ':dolacode',
    ],
    'HRP map village lookup must use bound params' => [
        'file' => 'modules/hrp/controllers/MapController.php',
        'present' => ':dolacode',
    ],
    'TB maps village lookup must use bound params' => [
        'file' => 'modules/Tbmaps/controllers/MapController.php',
        'present' => ':dolacode',
    ],
    'Import detail ZIP lookup must use bound params' => [
        'file' => 'frontend/modules/import/views/upload/detail.php',
        'present' => ':zipname',
    ],
    'Import2 detail ZIP lookup must use bound params' => [
        'file' => 'frontend/modules/import2/views/upload/detail.php',
        'present' => ':zipname',
    ],
    'Backend transform runner must require access control' => [
        'file' => 'backend/modules/exec/controllers/TransformController.php',
        'present' => 'AccessControl::className()',
    ],
    'Backend transform runner must validate procedure names' => [
        'file' => 'backend/modules/exec/controllers/TransformController.php',
        'present' => 'ข้อมูลกระบวนการไม่ถูกต้อง',
    ],
    'Backend QC runner must require access control' => [
        'file' => 'backend/modules/exec/controllers/QcController.php',
        'present' => 'AccessControl::className()',
    ],
    'Backend gate must require access control' => [
        'file' => 'backend/modules/gate/controllers/DefaultController.php',
        'present' => 'AccessControl::className()',
    ],
    'Project root must deny sensitive paths' => [
        'file' => '.htaccess',
        'present' => 'common|components|console|docs|environments|modules|tools|update',
    ],
    'Frontend must deny direct access to import work files' => [
        'file' => 'frontend/web/.htaccess',
        'present' => 'fortythree|fortythreebackup|unzip|sql_upload_file',
    ],
    'Release archives must exclude the legacy updater' => [
        'file' => '.gitattributes',
        'present' => '/update export-ignore',
    ],
    'Legacy update endpoint must be disabled' => [
        'file' => 'update/update.php',
        'present' => "http_response_code(404)",
    ],
    'Legacy update endpoint must not extract archives' => [
        'file' => 'update/update.php',
        'absent' => 'extractTo',
    ],
    'Legacy download endpoint must be disabled' => [
        'file' => 'update/download.php',
        'present' => "http_response_code(404)",
    ],
    'Hospital setup CRUD must require access control' => [
        'file' => 'backend/modules/setup/controllers/ChospitalController.php',
        'present' => 'AccessControl::className()',
    ],
    'Import error CRUD must require access control' => [
        'file' => 'frontend/modules/import/controllers/ImportErrorController.php',
        'present' => 'AccessControl::className()',
    ],
    'Import2 error CRUD must require access control' => [
        'file' => 'frontend/modules/import2/controllers/ImportErrorController.php',
        'present' => 'AccessControl::className()',
    ],
    'Population data generator must require access control' => [
        'file' => 'modules/population/controllers/DefaultController.php',
        'present' => "'only' => ['gen-data']",
    ],
    'Population data generator must require POST' => [
        'file' => 'modules/population/controllers/DefaultController.php',
        'present' => "'gen-data' => ['post']",
    ],
    'Population web generator must delegate to the protected Transform workflow' => [
        'file' => 'modules/population/controllers/DefaultController.php',
        'present' => 'protected Transform workflow only',
    ],
    'Population hospital lookup must use a bound parameter' => [
        'file' => 'modules/population/controllers/DefaultController.php',
        'present' => 'HOSPCODE = :hospcode',
    ],
    'Population hospital lookup must not interpolate input' => [
        'file' => 'modules/population/controllers/DefaultController.php',
        'absent' => 'HOSPCODE = $hospcode',
    ],
    'Database config example must read credentials from environment' => [
        'file' => 'common/config/connect_database.example.php',
        'present' => "getenv('DHDC_DB_PASSWORD')",
    ],
    'Update database config example must read credentials from environment' => [
        'file' => 'common/config/connect_update.example.php',
        'present' => "getenv('DHDC_UPDATE_DB_PASSWORD')",
    ],
    'Production database config must read credentials from environment' => [
        'file' => 'environments/prod/common/config/main-local.php',
        'present' => "getenv('DHDC_DB_DSN')",
    ],
    'Production mailer must read transport from environment' => [
        'file' => 'environments/prod/common/config/main-local.php',
        'present' => "getenv('DHDC_MAILER_DSN')",
    ],
    'Production mailer must not write email to local files' => [
        'file' => 'environments/prod/common/config/main-local.php',
        'present' => "'useFileTransport' => false",
    ],
    'Release verifier must not expose database password on the process command line' => [
        'file' => 'tools/verify-release.ps1',
        'absent' => '--password=$DbPassword',
    ],
    'Readonly smoke must not expose database password on the process command line' => [
        'file' => 'tools/smoke-ui-readonly.ps1',
        'absent' => '--password=$DbPassword',
    ],
    'Authenticated smoke must not expose database password on the process command line' => [
        'file' => 'tools/smoke-ui-authenticated.ps1',
        'absent' => '--password=$DbPassword',
    ],
    'Release builder must install production dependencies only' => [
        'file' => 'tools/build-release.ps1',
        'present' => 'composer install --no-dev --classmap-authoritative',
    ],
    'Release builder must reject the legacy updater' => [
        'file' => 'tools/build-release.ps1',
        'present' => 'Release contains forbidden development path',
    ],
    'Readonly smoke must reject root database accounts' => [
        'file' => 'tools/smoke-ui-readonly.ps1',
        'present' => 'Application database account must not be root',
    ],
    'Readonly smoke must reject global database privileges' => [
        'file' => 'tools/smoke-ui-readonly.ps1',
        'present' => 'Application database account must not have global privileges other than USAGE',
    ],
    'Readonly smoke must allow MariaDB global USAGE only' => [
        'file' => 'tools/smoke-ui-readonly.ps1',
        'present' => 'GRANT\\s+USAGE\\s+ON',
    ],
    'Authenticated smoke must reject GRANT OPTION' => [
        'file' => 'tools/smoke-ui-authenticated.ps1',
        'present' => 'Application database account must not have GRANT OPTION',
    ],
    'Production initializer must generate cryptographic cookie keys' => [
        'file' => 'init',
        'present' => 'random_bytes($length)',
    ],
    'Production initializer must not create world-writable directories' => [
        'file' => 'init',
        'absent' => 'chmod("$root/$writable", 0777)',
    ],
    'Production initializer must create missing writable directories' => [
        'file' => 'init',
        'present' => '@mkdir($path, 0775, true)',
    ],
    'Production initializer must fail when setup callbacks fail' => [
        'file' => 'init',
        'present' => 'exit(4)',
    ],
    'Production initializer must support artifacts without development templates' => [
        'file' => 'init',
        'present' => '$otherEnvironmentRoot',
    ],
    'Apache production template must deny the project root' => [
        'file' => 'docs/apache-dhdc4.conf.example',
        'present' => 'Require all denied',
    ],
    'Apache production template must configure HSTS' => [
        'file' => 'docs/apache-dhdc4.conf.example',
        'present' => 'Strict-Transport-Security',
    ],
    'Apache production template must allow TLS 1.2 and 1.3 only' => [
        'file' => 'docs/apache-dhdc4.conf.example',
        'present' => 'SSLProtocol -all +TLSv1.2 +TLSv1.3',
    ],
    'Apache production template must hide server details' => [
        'file' => 'docs/apache-dhdc4.conf.example',
        'present' => 'ServerTokens Prod',
    ],
    'Apache production template must restrict the backend network' => [
        'file' => 'docs/apache-dhdc4.conf.example',
        'present' => 'Require ip 10.0.0.0/8',
    ],
    'Release archives must exclude development environments' => [
        'file' => '.gitattributes',
        'present' => '/environments/dev export-ignore',
    ],
    'Smartcard URL must reject non-loopback HTTP endpoints' => [
        'file' => 'modules/smcread/views/default/index.php',
        'present' => '$isLoopbackEndpoint',
    ],
];

if ($releaseArtifact) {
    foreach ([
        'Legacy update endpoint must be disabled',
        'Legacy update endpoint must not extract archives',
        'Legacy download endpoint must be disabled',
    ] as $sourceOnlyCheck) {
        unset($checks[$sourceOnlyCheck]);
    }
}

$mapViews = [
    'modules/gis/views/default/map.php',
    'modules/hrp/views/map/map.php',
    'modules/Tbmaps/views/map/map.php',
];

foreach ($mapViews as $mapView) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $mapView);
    if (!is_file($path)) {
        $failures[] = "Map security check: missing $mapView";
        continue;
    }

    $contents = file_get_contents($path);
    foreach ([
        'AIza',
        'http://aratcliffe.github.io',
        'http://203.157.',
        'http://{s}.google.com',
        'maps.googleapis.com',
        '.google.com/vt/',
        'google.maps.',
        'DHDC_GOOGLE_MAPS_API_KEY',
        'http://rain.tvis.in.th',
        'ajax.googleapis.com/ajax/libs/jquery',
        'maxcdn.bootstrapcdn.com/bootstrap',
        'api.mapbox.com/mapbox.js',
        'npmcdn.com/@turf',
    ] as $forbidden) {
        if (strpos($contents, $forbidden) !== false) {
            $failures[] = "$mapView: found forbidden map resource '$forbidden'";
        }
    }

    foreach ([
        'DHDC_RAIN_RADAR_BASE_URL',
        'vendor/jquery-3.7.1/jquery.min.js',
        'vendor/bootstrap-3.4.1/js/transition.js',
        'vendor/bootstrap-3.4.1/js/modal.js',
        'vendor/mapbox-3.1.1/mapbox.js',
        'vendor/leaflet-draw-0.4.10/dist/leaflet.draw.js',
        'vendor/leaflet-markercluster-1.0.0/dist/leaflet.markercluster.js',
        'vendor/leaflet-locatecontrol-0.43.0/dist/L.Control.Locate.min.js',
        'vendor/leaflet-hash-0.2.1/leaflet-hash.js',
        'vendor/turf-compat-7.3.5/turf-compat.min.js',
        'escapeHtml(data.properties.title)',
        '"OSM ถนน": osm_street.addTo(map)',
    ] as $required) {
        if (strpos($contents, $required) === false) {
            $failures[] = "$mapView: expected secure map setting '$required'";
        }
    }
}

$populationMapView = 'modules/population/views/default/map.php';
$populationMapPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $populationMapView);
if (!is_file($populationMapPath)) {
    $failures[] = "Map security check: missing $populationMapView";
} else {
    $contents = file_get_contents($populationMapPath);
    foreach ([
        'ajax.googleapis.com/ajax/libs/jquery',
        'api.mapbox.com/mapbox.js',
        'npmcdn.com/@turf',
    ] as $forbidden) {
        if (strpos($contents, $forbidden) !== false) {
            $failures[] = "$populationMapView: found forbidden map resource '$forbidden'";
        }
    }
    foreach ([
        'vendor/mapbox-3.1.1/mapbox.js',
        'vendor/mapbox-3.1.1/mapbox.css',
        'vendor/turf-compat-7.3.5/turf-compat.min.js',
        'escapeHtml(layer.feature.properties.TAM_NAME)',
    ] as $required) {
        if (strpos($contents, $required) === false) {
            $failures[] = "$populationMapView: expected secure map setting '$required'";
        }
    }
}

foreach ([
    'frontend/web/lib/map/vendor/jquery-3.7.1/jquery.min.js',
    'frontend/web/lib/map/vendor/bootstrap-3.4.1/dist/css/bootstrap.min.css',
    'frontend/web/lib/map/vendor/bootstrap-3.4.1/js/transition.js',
    'frontend/web/lib/map/vendor/bootstrap-3.4.1/js/modal.js',
    'frontend/web/lib/map/vendor/mapbox-3.1.1/mapbox.css',
    'frontend/web/lib/map/vendor/mapbox-3.1.1/mapbox.js',
    'frontend/web/lib/map/vendor/leaflet-draw-0.4.10/dist/leaflet.draw.js',
    'frontend/web/lib/map/vendor/leaflet-draw-0.4.10/dist/leaflet.draw.css',
    'frontend/web/lib/map/vendor/leaflet-markercluster-1.0.0/dist/leaflet.markercluster.js',
    'frontend/web/lib/map/vendor/leaflet-locatecontrol-0.43.0/dist/L.Control.Locate.min.js',
    'frontend/web/lib/map/vendor/leaflet-hash-0.2.1/leaflet-hash.js',
    'frontend/web/lib/map/vendor/turf-compat-7.3.5/turf-compat.min.js',
    'frontend/web/lib/map/vendor/turf-compat-7.3.5/LICENSE-area',
    'frontend/web/lib/map/vendor/font-awesome-4.7.0/css/font-awesome.min.css',
    'frontend/web/lib/map/vendor/THIRD_PARTY_NOTICES.md',
] as $asset) {
    if (!is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $asset))) {
        $failures[] = "Vendored map asset is missing: $asset";
    }
}

foreach ($checks as $label => $check) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $check['file']);
    if (!is_file($path)) {
        $failures[] = "$label: missing {$check['file']}";
        continue;
    }
    $contents = file_get_contents($path);
    if (isset($check['present']) && strpos($contents, $check['present']) === false) {
        $failures[] = "$label: expected '{$check['present']}'";
    }
    if (isset($check['absent']) && strpos($contents, $check['absent']) !== false) {
        $failures[] = "$label: found forbidden '{$check['absent']}'";
    }
}

$deletedFiles = [
    'info.php',
    'frontend/web/index-test.php',
    'backend/web/index-test.php',
    'modules/ehr/controllers/DefaultController_.php',
    'frontend/modules/import/views/upload/importall_1.php',
    'frontend/modules/import/views/upload/view_1.php',
    'frontend/modules/import2/views/upload/importall_1.php',
    'frontend/modules/import2/views/upload/view_1.php',
];

foreach ($deletedFiles as $file) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    if (file_exists($path)) {
        $failures[] = "legacy file must be removed: $file";
    }
}

$activePhpFiles = [
    'frontend/modules/import/controllers/AjaxController.php',
    'frontend/modules/import2/controllers/AjaxController.php',
    'modules/sqlquery/controllers/RunqueryController.php',
    'modules/ehr/controllers/DefaultController.php',
    'backend/modules/hdcreportsetup/controllers/HdcsqlController.php',
    'backend/modules/exec/controllers/DefaultController.php',
    'backend/modules/exec/controllers/TransformController.php',
    'backend/modules/exec/controllers/QcController.php',
    'backend/modules/gate/controllers/DefaultController.php',
    'modules/gis/controllers/JsonController.php',
    'modules/gis/controllers/DefaultController.php',
    'modules/hrp/controllers/JsonController.php',
    'modules/hrp/controllers/MapController.php',
    'modules/Tbmaps/controllers/JsonController.php',
    'modules/Tbmaps/controllers/MapController.php',
    'frontend/modules/import/views/upload/detail.php',
    'frontend/modules/import2/views/upload/detail.php',
    'backend/config/main.php',
    'backend/config/main-local.php',
    'backend/web/index.php',
    'backend/modules/setup/controllers/ChospitalController.php',
    'frontend/modules/import/controllers/ImportErrorController.php',
    'frontend/modules/import2/controllers/ImportErrorController.php',
    'modules/population/controllers/DefaultController.php',
    'modules/smcread/views/default/index.php',
    'modules/gis/views/default/map.php',
    'modules/hrp/views/map/map.php',
    'modules/Tbmaps/views/map/map.php',
    'update/update.php',
    'update/download.php',
    'update/chk_version.php',
    'common/config/connect_database.example.php',
    'common/config/connect_update.example.php',
    'environments/prod/common/config/main-local.php',
    'tools/configure-database.php',
    'tools/rotate-database-admin.php',
    'tools/manage-release-test-user.php',
    'tools/verify-production-init.php',
    'tools/verify-map-view-render.php',
    'init',
];

if ($releaseArtifact) {
    $activePhpFiles = array_values(array_filter(
        $activePhpFiles,
        static function (string $file): bool {
            return strpos($file, 'update/') !== 0;
        }
    ));
}

foreach ($activePhpFiles as $file) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    $output = [];
    $status = 0;
    exec('php -l ' . escapeshellarg($path) . ' 2>&1', $output, $status);
    if ($status !== 0) {
        $failures[] = "$file: " . implode(' ', $output);
    }
}

if (!empty($failures)) {
    echo "OWASP regression check failed:\n";
    foreach ($failures as $failure) {
        echo "- $failure\n";
    }
    exit(1);
}

echo "OWASP regression check passed.\n";
