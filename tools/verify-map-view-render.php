<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = $root . '/frontend/web/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';

defined('YII_DEBUG') || define('YII_DEBUG', false);
defined('YII_ENV') || define('YII_ENV', 'test');

require $root . '/vendor/autoload.php';
require $root . '/vendor/yiisoft/yii2/Yii.php';

foreach ([
    'DHDC_GOOGLE_MAPS_API_KEY',
    'DHDC_RAIN_RADAR_BASE_URL',
    'DHDC_FLOOD_WMS_BASE_URL',
    'DHDC_FLOOD_PERCENT_WMS_BASE_URL',
] as $variable) {
    putenv($variable);
}

$application = new yii\web\Application([
    'id' => 'map-render-verifier',
    'basePath' => $root,
    'components' => [
        'request' => ['cookieValidationKey' => 'map-render-verifier-only'],
        'urlManager' => ['enablePrettyUrl' => true, 'showScriptName' => false],
    ],
]);
$controller = new yii\web\Controller('map-render-verifier', $application);
$application->controller = $controller;

$requiredAssets = [
    'vendor/jquery-3.7.1/jquery.min.js',
    'vendor/bootstrap-3.4.1/js/transition.js',
    'vendor/bootstrap-3.4.1/js/modal.js',
    'vendor/mapbox-3.1.1/mapbox.js',
    'vendor/leaflet-draw-0.4.10/dist/leaflet.draw.js',
    'vendor/leaflet-markercluster-1.0.0/dist/leaflet.markercluster.js',
    'vendor/leaflet-locatecontrol-0.43.0/dist/L.Control.Locate.min.js',
    'vendor/leaflet-hash-0.2.1/leaflet-hash.js',
    'vendor/turf-compat-7.3.5/turf-compat.min.js',
    'leaflet-contextmenu/leaflet.contextmenu.min.js',
    'polyline/polyline.js',
];
$forbiddenRuntimeSources = [
    'maps.googleapis.com/maps/api/js?key=',
    'ajax.googleapis.com/ajax/libs/jquery',
    'maxcdn.bootstrapcdn.com/bootstrap',
    'api.mapbox.com/mapbox.js',
    'npmcdn.com/@turf',
    'http://rain.',
    'http://tile.',
    'http://203.157.',
];

foreach ([
    'modules/gis/views/default/map.php',
    'modules/hrp/views/map/map.php',
    'modules/Tbmaps/views/map/map.php',
] as $file) {
    $html = $application->view->renderFile(
        $root . '/' . $file,
        ['tambon_pol' => '[]'],
        $controller
    );

    foreach ($requiredAssets as $required) {
        if (strpos($html, $required) === false) {
            throw new RuntimeException("$file did not render local asset: $required");
        }
    }
    foreach ($forbiddenRuntimeSources as $forbidden) {
        if (strpos($html, $forbidden) !== false) {
            throw new RuntimeException("$file rendered forbidden runtime source: $forbidden");
        }
    }
    foreach ([
        'var rain = base_url ? L.layerGroup() : null;',
        'var flood_update = floodWmsBaseUrl ? L.tileLayer.wms',
        'var flood_percent = floodPercentWmsBaseUrl ? L.tileLayer.wms',
    ] as $optionalLayerGuard) {
        if (strpos($html, $optionalLayerGuard) === false) {
            throw new RuntimeException("$file is missing optional map layer guard: $optionalLayerGuard");
        }
    }
    if (strpos($html, 'escapeHtml(data.properties.title)') === false) {
        throw new RuntimeException("$file is missing map property HTML escaping");
    }

    echo "[OK] $file rendered secure local map runtime\n";
}

$populationMap = 'modules/population/views/default/map.php';
$populationHtml = $application->view->renderFile($root . '/' . $populationMap, [], $controller);
foreach ([
    'vendor/mapbox-3.1.1/mapbox.js',
    'vendor/mapbox-3.1.1/mapbox.css',
    'vendor/turf-compat-7.3.5/turf-compat.min.js',
    'escapeHtml(layer.feature.properties.TAM_NAME)',
] as $required) {
    if (strpos($populationHtml, $required) === false) {
        throw new RuntimeException("$populationMap did not render secure local runtime setting: $required");
    }
}
foreach ($forbiddenRuntimeSources as $forbidden) {
    if (strpos($populationHtml, $forbidden) !== false) {
        throw new RuntimeException("$populationMap rendered forbidden runtime source: $forbidden");
    }
}
echo "[OK] $populationMap rendered secure local map runtime\n";

echo "Map view render verification passed.\n";
