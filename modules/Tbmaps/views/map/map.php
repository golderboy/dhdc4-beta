<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\bootstrap\Modal;

$web = \Yii::getAlias('@web');
$googleMapsApiKey = trim((string) getenv('DHDC_GOOGLE_MAPS_API_KEY'));
$secureServiceUrl = static function (string $environmentName): string {
    $value = trim((string) getenv($environmentName));
    if ($value === '') {
        return '';
    }

    $parts = parse_url($value);
    return filter_var($value, FILTER_VALIDATE_URL) !== false
        && strtolower((string) ($parts['scheme'] ?? '')) === 'https'
        && (string) ($parts['host'] ?? '') !== ''
        ? rtrim($value, '/')
        : '';
};
$rainRadarBaseUrl = $secureServiceUrl('DHDC_RAIN_RADAR_BASE_URL');
$floodWmsBaseUrl = $secureServiceUrl('DHDC_FLOOD_WMS_BASE_URL');
$floodPercentWmsBaseUrl = $secureServiceUrl('DHDC_FLOOD_PERCENT_WMS_BASE_URL');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset=utf-8 />
        <meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />
        <title>DHDC 3.0 GIS</title>
        <link rel="stylesheet" href="<?= $web ?>/css/mui-web/tokens.css" />
        <link rel="stylesheet" href="<?= $web ?>/css/mui-web/layout.css" />
        <link rel="stylesheet" href="<?= $web ?>/css/mui-web/components.css" />
        <script src="<?= $web ?>/lib/map/vendor/jquery-3.7.1/jquery.min.js"></script>
        <?php if ($googleMapsApiKey !== ''): ?>
            <script src="https://maps.googleapis.com/maps/api/js?key=<?= Html::encode(rawurlencode($googleMapsApiKey)) ?>"></script>
        <?php endif; ?>
        <link rel="stylesheet" href="<?= $web ?>/lib/map/vendor/bootstrap-3.4.1/dist/css/bootstrap.min.css">
        <script src="<?= $web ?>/lib/map/vendor/bootstrap-3.4.1/js/transition.js"></script>
        <script src="<?= $web ?>/lib/map/vendor/bootstrap-3.4.1/js/modal.js"></script>

        <link href="<?= $web ?>/lib/map/vendor/mapbox-3.1.1/mapbox.css" rel="stylesheet" />
        <script src="<?= $web ?>/lib/map/vendor/mapbox-3.1.1/mapbox.js"></script>

        <script src="<?= $web ?>/lib/map/vendor/leaflet-draw-0.4.10/dist/leaflet.draw.js"></script>
        <link href="<?= $web ?>/lib/map/vendor/leaflet-draw-0.4.10/dist/leaflet.draw.css" rel="stylesheet" />

        <script src="<?= $web ?>/lib/map/vendor/leaflet-markercluster-1.0.0/dist/leaflet.markercluster.js"></script>
        <link href="<?= $web ?>/lib/map/vendor/leaflet-markercluster-1.0.0/dist/MarkerCluster.css" rel="stylesheet" />
        <link href="<?= $web ?>/lib/map/vendor/leaflet-markercluster-1.0.0/dist/MarkerCluster.Default.css" rel="stylesheet" />

        <script src="<?= $web ?>/lib/map/vendor/leaflet-locatecontrol-0.43.0/dist/L.Control.Locate.min.js"></script>
        <link href="<?= $web ?>/lib/map/vendor/leaflet-locatecontrol-0.43.0/dist/L.Control.Locate.mapbox.min.css" rel="stylesheet" />
        <!--[if lt IE 9]>
          <link href="<?= $web ?>/lib/map/vendor/leaflet-locatecontrol-0.43.0/dist/L.Control.Locate.ie.min.css" rel="stylesheet" />
        <![endif]-->
        <link href="<?= $web ?>/lib/map/vendor/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" />

        <script src="<?= $web ?>/js/Leaflet.Control.Custom.js"></script> 

        <link href="<?= $web ?>/lib/map/leaflet-contextmenu/leaflet.contextmenu.min.css" rel="stylesheet"/>
        <script src="<?= $web ?>/lib/map/leaflet-contextmenu/leaflet.contextmenu.min.js"></script>

        <style>
            body { margin:0; padding:0; }
            .title{
                font-size: 1.5em;
                position: absolute;
                top:0;
                left: 0;
                right: 0;
                height: 35px;
                background-color: rgba(77, 106, 106, 1);
                color: white;  
                text-align: center;
            }
            #map { position:absolute; top:0; bottom:0; width:100%; margin-top: 35px;}
            .show-latlng{
                position:absolute;
                bottom:0;
                z-index: 10;

            }
            .leaflet-control-draw-measure {
                background-image: url(<?= $web ?>/images/measure-control.png);
            }
            .point-label {  white-space: nowrap;background:null;}
        </style>
    </head>
    <body class="mui-web-scope" data-mui-web-color-scheme="light">
        <script src="<?= $web ?>/lib/map/vendor/leaflet-hash-0.2.1/leaflet-hash.js"></script>
        <link rel="stylesheet" href="<?= $web ?>/lib/map/ruler/leaflet-ruler.css" />
        <script src="<?= $web ?>/lib/map/ruler/leaflet-ruler.js"></script>

        <!-- search-->
        <link rel="stylesheet" type="text/css" href="<?= $web ?>/lib/map/leaflet-search/dist/leaflet-search.min.css"/>
        <script src="<?= $web ?>/lib/map/leaflet-search/dist/leaflet-search.min.js"></script>

        <script src="<?= $web ?>/lib/map/vendor/turf-compat-7.3.5/turf-compat.min.js"></script>

        <script src="<?= $web ?>/lib/map/polyline/polyline.js"></script>

        <div class="title">แผนที่ผู้ป่วย TB</div>
        <div id='map'></div>
        <div class="show-latlng">
            <input type="text" id="txt-latlng" style="width: 290px"/>
        </div>
        <script>
            function escapeHtml(value) {
                var element = document.createElement('div');
                element.textContent = value == null ? '' : String(value);
                return element.innerHTML;
            }


// direction
            var layer_line = L.mapbox.featureLayer();
            var direction = function (origin, destination) {
                if (typeof google === 'undefined' || !google.maps) {
                    return Promise.reject('ยังไม่ได้กำหนด DHDC_GOOGLE_MAPS_API_KEY');
                }
                var directionsService = new google.maps.DirectionsService();
                var directionsRequest = {
                    origin: origin,
                    destination: destination,
                    travelMode: google.maps.DirectionsTravelMode.DRIVING,
                    unitSystem: google.maps.UnitSystem.METRIC
                };
                return new Promise(function (resolve, reject) {
                    directionsService.route(directionsRequest, function (response, status) {
                        if (status == google.maps.DirectionsStatus.OK) {
                            var route = response.routes[0].overview_polyline;
                            var descript = response.routes[0].legs[0];
                            var data = {
                                route: route,
                                descript: {
                                    distance: descript.distance.text,
                                    duration: descript.duration.text
                                }

                            };
                            resolve(data)
                        } else {
                            reject("ผิดพลาด:" + status)
                        }
                    })
                });
            };

            function calDirect() {
                var origin = markerA.getLatLng().lat + ',' + markerA.getLatLng().lng;
                var destination = markerB.getLatLng().lat + ',' + markerB.getLatLng().lng;
                direction(origin, destination).then(function (result) {
                    var json_line = polyline.toGeoJSON(result.route);
                    layer_line.remove();
                    layer_line.setGeoJSON(json_line)
                            .setStyle({
                                weight: 5,
                                color: 'blue'
                            }).addTo(map);
                    var pop = result.descript.distance + " ," + result.descript.duration;
                    layer_line.bindPopup('รถยนต์ : ' + pop);
                    layer_line.openPopup();
                }, function (err) {
                    alert(err)
                });
            }
// direction
            //base map
            var googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}&hl=th', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            });
            var googleStreet = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}&hl=th', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            });
            var googleSat = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}&hl=th', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            });
            var googleTerrain = L.tileLayer('https://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}&hl=th', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            });
            var osm_street = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            });
            var satellite = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri'
            });
            var markerA, markerB;
            var map = L.mapbox.map('map', null, {
                contextmenu: true,
                contextmenuWidth: 140,
                contextmenuItems: [
                    {
                        text: 'จากที่นี่',
                        callback: function (e) {
                            if (markerA) {
                                markerA.remove();
                            }
                            if (markerB) {
                                markerB.remove();
                            }
                            if (layer_line) {
                                layer_line.remove();
                            }
                            markerA = L.marker(e.latlng, {
                                'draggable': 'true',
                                icon: L.mapbox.marker.icon({
                                    'marker-symbol': 'a'
                                })
                            }).addTo(map);
                        }
                    },
                    {
                        text: 'ถึงที่นี่',
                        callback: function (e) {
                            if (!markerA) {
                                return;
                            }
                            if (markerB) {
                                markerB.remove();
                            }
                            markerB = L.marker(e.latlng, {
                                'draggable': true,
                                icon: L.mapbox.marker.icon({
                                    'marker-symbol': 'b'
                                })
                            }).addTo(map);
                            calDirect();
                            markerA.on('dragend', function () {
                                calDirect();
                            });
                            markerB.on('dragend', function () {
                                calDirect();
                            });


                        }
                    },
                ]
            }).setView([16, 100], 6);
            var hash = L.hash(map);
            L.control.locate().addTo(map);



            var clusterHome = new L.MarkerClusterGroup().addTo(map);

            var baseLayers = {
                "OSM ภูมิประเทศ": osm_street,
                "OSM ถนน": L.tileLayer('//{s}.tile.osm.org/{z}/{x}/{y}.png'),
                "ภาพถ่ายดาวเทียม": satellite,
                "Google Hybrid": googleHybrid,
                "Google Street": googleStreet.addTo(map),
                "Google ภูมิประเทศ": googleTerrain,
            }; // base map 

            //crosshair
            var crosshairIcon = L.icon({
                iconUrl: "<?= $web ?>/images/crosshair.png",
                iconSize: [25, 25], // size of the icon
                //iconAnchor:   [10, 10], // point of the icon which will correspond to marker's location
            });
            crosshair = new L.marker(map.getCenter(), {icon: crosshairIcon, clickable: false});
            crosshair.addTo(map);

            // control
            L.control.ruler({position: 'topleft'}).addTo(map);

            var featureGroupDraw = L.featureGroup().addTo(map);
            var drawControl = new L.Control.Draw({
                draw: {
                    circle: false,
                    rectangle: false,
                    marker: false,
                    polyline: false
                },
                edit: {
                    featureGroup: featureGroupDraw,
                    remove: false,
                    edit: false
                }
            }).addTo(map);

            L.control.custom({
                position: 'topleft',
                content: '<button type="button" class="btn btn-default btn-circle" title="รัศมี...">' +
                        '    <i class="glyphicon glyphicon-record"></i>' +
                        '</button>' +
                        '<button type="button" class="btn btn-default btn-reload" title="reload...">' +
                        '    <i class="glyphicon glyphicon-refresh"></i>' +
                        '</button>'
                ,
                classes: 'btn-group-vertical btn-group-sm',
                style:
                        {
                            margin: '10px',
                            padding: '0px 0 0 0',
                            cursor: 'pointer'
                        },
            }).addTo(map);



            //end control



            var villGroup = L.featureGroup();
            var tambonGroup = L.featureGroup();
            var hospitalGroup = L.featureGroup();

            var tambon = L.mapbox.featureLayer()
                    .setGeoJSON(<?= $tambon_pol ?>);
            tambon.eachLayer(function (layer) {
                var json = layer.feature;
                var feature = L.mapbox.featureLayer(json);
                feature.bindTooltip(escapeHtml(json.properties.title), {permanent: 'true'});
                feature.setStyle({weight: 1, fillOpacity: 0, dashArray: 4});
                feature.addTo(tambonGroup);
            });

            map.fitBounds(tambon.getBounds());


<?php
$json_home_route = Url::to(['point-home']);
$json_vill_route = Url::to(['point-vill']);
$json_hosp_route = Url::to(['point-hosp']);
$json_risk_route = Url::to(['point-tb']);
?>
            var home = L.mapbox.featureLayer().loadURL('<?= $json_home_route ?>');
            var labelHomeLayer = L.featureGroup().addTo(map);
            home.on('ready', function () {
                home.addTo(clusterHome);
                var homeGeojson = home.getGeoJSON();
                var homeCollection = turf.featureCollection(homeGeojson);

                //click circle
                $('.btn-circle').click(function () {
                    var r = prompt("ระบุรัศมี (เมตร)", 100);
                    var circleRadius = L.circle(map.getCenter(), Number(r), {color: 'yellow', 'dashArray': 4, weight: 2}).addTo(map);
                    circleRadius.on('click', function (e) {
                        var layer = e.target;
                        var latlng = layer.getLatLng();
                        var circleJson = turf.circle([latlng.lng, latlng.lat], Number(r) / 1000, 100, 'kilometers', {});

                        var circleCollection = turf.featureCollection([circleJson]);

                        var resGeojson = turf.within(homeCollection, circleCollection);
                        var countHome = resGeojson.features.length;
                        var list = "";
                        //labelHomeLayer.remove();
                        resGeojson.features.forEach(function (data) {
                            list += "บ้านเลขที่ " + escapeHtml(data.properties.title) + "<br>";

                            var latLng = [data.geometry.coordinates[1], data.geometry.coordinates[0]];
                            var lbHtml = '<span style="background-color:#FFF8DC;">';
                            lbHtml += escapeHtml(data.properties.title);
                            lbHtml += '<span>';
                            L.marker(latLng, {icon: L.divIcon({className: 'point-label', html: lbHtml})}).addTo(labelHomeLayer);

                        });
                        //alert("<b>พื้นที่นี้มี  <u>" + countHome + "</u> หลังคาเรือน</b>" + list)
                        $('#modal').modal('show').find('#modalContent').html("<h4>ทั้งหมด " + countHome + " หลัง</h4><br>" + list);

                    })
                });//  end click circle

                //drawing 

                map.on(L.Draw.Event.CREATED, function (e) {
                    var type = e.layerType;
                    var layer = e.layer;
                    featureGroupDraw.addLayer(layer);
                    if (type == 'polygon') {
                        var polygonCollection = turf.featureCollection([layer.toGeoJSON()]);
                        var resGeojson = turf.within(homeCollection, polygonCollection);
                        var countHome = resGeojson.features.length;
                        var list = "";
                        //labelHomeLayer.remove();
                        resGeojson.features.forEach(function (data) {
                            list += "บ้านเลขที่ " + escapeHtml(data.properties.title) + "<br>";

                            var latLng = [data.geometry.coordinates[1], data.geometry.coordinates[0]];
                            var lbHtml = '<span style="background-color:#FFF8DC;">';
                            lbHtml += escapeHtml(data.properties.title);
                            lbHtml += '<span>';
                            L.marker(latLng, {icon: L.divIcon({className: 'point-label', html: lbHtml})}).addTo(labelHomeLayer);

                        });
                        //alert("<b>พื้นที่นี้มี  <u>" + countHome + "</u> หลังคาเรือน</b>" + list)
                        layer.on('click', function () {
                            $('#modal').modal('show').find('#modalContent').html("<h4>ทั้งหมด " + countHome + " หลัง</h4><br>" + list);

                        });
                    }


                }); //end drawing


            })

            var villages = L.mapbox.featureLayer().loadURL('<?= $json_vill_route ?>');
            villages.on('ready', function () {
                villages.eachLayer(function (layer) {
                    var latLng = [layer.feature.geometry.coordinates[1], layer.feature.geometry.coordinates[0]];
                    var tambon_code = layer.feature.properties.DOLACODE.substring(0, 6) * 1;
                    var marker_vill = L.marker(latLng, {
                        icon: L.mapbox.marker.icon({
                            'marker-symbol': 'circle-stroked',
                            'marker-color': tambon_code % 2 == 0 ? '#7CFC00' : '#87CEFA',
                            'marker-size': 'large'
                        }),
                    });
                    var title = "หมู่ที่ " + escapeHtml(layer.feature.properties.VILL_NO);
                    title += " บ." + escapeHtml(layer.feature.properties.MUBAN);
                    title += "<br>ต." + escapeHtml(layer.feature.properties.TAMBOL);

                    var tips = "หมู่ที่ " + escapeHtml(layer.feature.properties.VILL_NO);
                    tips += " บ." + escapeHtml(layer.feature.properties.MUBAN);

                    //marker_vill.bindPopup(title);
                    marker_vill.bindTooltip(tips, {permanent: 'true'});
                    marker_vill.addTo(villGroup);
                });

            });

            var hospital = L.mapbox.featureLayer();
            hospital.loadURL('<?= $json_hosp_route ?>');
            hospital.on('ready', function (e) {
                var json = e.target.getGeoJSON();
                json.forEach(function (feature) {
                    var pointHosp = L.mapbox.featureLayer();
                    pointHosp.bindTooltip(escapeHtml(feature.properties.title));
                    pointHosp.setGeoJSON(feature);
                    pointHosp.addTo(hospitalGroup);

                })
            });

            var risk = L.mapbox.featureLayer();
            risk.loadURL('<?= $json_risk_route ?>');
            risk.on('ready', function (e) {
                var json = e.target.getGeoJSON();
            });

            //เริ่มwms

            //ฝน
            var base_url = <?= Json::htmlEncode($rainRadarBaseUrl) ?>;
            var radar = 'NongKham';
            var radars = '["NongKham","KKN","PHS","CRI","UBN","OMK"]';
            var latlng_topright = '["15.09352819610486,101.7458188486135","18.793550,105.026265","19.094393,102.475537","22.305437,102.143387","17.558854,107.095363","19.904425,100.770048"]';
            var latlng_bottomleft = '["12.38196058009694,98.97206140040996","14.116192,100.541459","14.411350,97.983591","17.596297,97.611690","12.918883,102.646771","15.630408,96.114592"]';
            var d = new Date();
            var time = d.getTime();
            radars = JSON.parse(radars);
            latlng_topright = JSON.parse(latlng_topright);
            latlng_bottomleft = JSON.parse(latlng_bottomleft);
            var rain = base_url ? L.layerGroup() : null;
            var urllast;
            var boundlast;
            if (rain) {
                $.each(radars, function (key, value) {
                var top_right = latlng_topright[key].split(",");
                var bottom_left = latlng_bottomleft[key].split(",");

                var imageUrl = base_url + "/output/" + value + ".png?" + time,
                        imageBounds = [[top_right[0], top_right[1]], [bottom_left[0], bottom_left[1]]];
                    L.imageOverlay(imageUrl, imageBounds).addTo(rain).setOpacity(0.95);

                });
            }
            //จบฝน



            //นำท่วม

            var floodWmsBaseUrl = <?= Json::htmlEncode($floodWmsBaseUrl) ?>;
            var floodPercentWmsBaseUrl = <?= Json::htmlEncode($floodPercentWmsBaseUrl) ?>;
            var flood_update = floodWmsBaseUrl ? L.tileLayer.wms(floodWmsBaseUrl, {
                layers: "floodarea_tambon",
                transparent: true,
                format: 'image/png',
                tiles: true,
                attribution: '<b>GISTDA THAILAND</b>'
            }) : null;
            var flood_percent = floodPercentWmsBaseUrl ? L.tileLayer.wms(floodPercentWmsBaseUrl, {
                layers: "flood:flood_percent",
                transparent: true,
                format: 'image/png',
                //opacity:1,
                tiles: true,
                attribution: '<b>GISTDA THAILAND</b>'
            }) : null;
            //จบน้ำท่วม

            //จบ wms

            var overlays = {
                'ผู้ป่วย TB': risk.addTo(map),
                'โรงพยาบาล': hospitalGroup.addTo(map),
                'หลังคาเรือน': clusterHome,
                'ขอบเขตตำบล': tambonGroup,
                'หมู่บ้าน': villGroup,
            };
            if (rain) {
                overlays['เรดาห์น้ำฝน'] = rain;
            }
            if (flood_percent) {
                overlays['พื้นที่น้ำท่วมรายตำบลรอบ 7 วัน'] = flood_percent;
            }
            if (flood_update) {
                overlays['พื้นที่น้ำท่วมรอบ7วัน'] = flood_update;
            }
            L.control.layers(baseLayers, overlays).addTo(map);
            tambon.eachLayer(function (layer) {
                var originColor = layer.feature.properties.fill;
                layer.setStyle({
                    dashArray: 3,
                });
                layer.on('mouseover', function (e) {
                    layer.setStyle({
                        weight: 5,
                    });
                });
                layer.on('mouseout', function (e) {
                    layer.setStyle({
                        fillColor: originColor,
                        weight: 2
                    });

                    layer.closePopup();
                });
                layer.on('click', function (e) {
                    map.fitBounds(layer.getBounds());
                    layer.bindPopup(escapeHtml(layer.feature.properties.TAM_NAMT));
                    layer.openPopup();
                });
            });



            $('.btn-reload').click(function () {
                location.reload();
            });




            map.on('move', function (e) {
                crosshair.setLatLng(map.getCenter());

            });

            map.on('moveend', function (e) {
                var latlng = crosshair.getLatLng();
                $('#txt-latlng').val(latlng.lat + "," + latlng.lng)
            });
            $('#txt-latlng').val(map.getCenter().lat + "," + map.getCenter().lng)
            $('#txt-latlng').click(function (e) {
                $(this).select();
            });




            // search control

            var searchControl = new L.Control.Search({layer: clusterHome});
            map.addControl(searchControl);
            searchControl.on('search:locationfound', function (data) {
                var latLngs = [data.latlng];
                var pointFoundBounds = L.latLngBounds(latLngs);
                map.fitBounds(pointFoundBounds);
                data.layer.openPopup();
            });
        </script>

        <?php
        Modal::begin([
            'header' => 'บ้านที่อยู่ในรัศมี',
            'size' => 'modal-md',
            'id' => 'modal',
        ]);
        echo "<div id='modalContent'>Loading...</div>";
        Modal::end();
        ?>

    </body>
</html>
