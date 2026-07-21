<?php

use yii\helpers\Json;
use yii\helpers\Url;

$web = \Yii::getAlias('@web');
$jsonTambonRoute = Url::to(['json-tambon']);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset=utf-8 />
        <title>ความหนาแน่นประชากรรายตำบล</title>
        <meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />
        <link rel="stylesheet" href="<?= $web ?>/css/mui-web/tokens.css" />
        <link rel="stylesheet" href="<?= $web ?>/css/mui-web/layout.css" />
        <link rel="stylesheet" href="<?= $web ?>/css/mui-web/components.css" />
        <script src="<?= $web ?>/lib/map/vendor/mapbox-3.1.1/mapbox.js"></script>
        <link href="<?= $web ?>/lib/map/vendor/mapbox-3.1.1/mapbox.css" rel="stylesheet" />
        <script src="<?= $web ?>/lib/map/vendor/turf-compat-7.3.5/turf-compat.min.js"></script>

        <style>
            body { margin:0; padding:0; }
            #map { position:absolute; top:0; bottom:0; width:100%; }
            .info { padding: 6px 8px; font: 14px/16px Arial, Helvetica, sans-serif; background: white; background: rgba(255,255,255,0.8); box-shadow: 0 0 15px rgba(0,0,0,0.2); border-radius: 5px; } 
            .info h4 { margin: 0 0 5px; color: #777; }
            .legend { text-align: left; line-height: 18px; color: #555; } 
            .legend i { width: 18px; height: 18px; float: left; margin-right: 8px; opacity: 0.7; }
        </style>
    </head>
    <body class="mui-web-scope mui-web-map-body" data-mui-web-color-scheme="light">
        <div id='map'></div>
        <script>
            var jsonTambonRoute = <?= Json::htmlEncode($jsonTambonRoute) ?>;

            function escapeHtml(value) {
                var element = document.createElement('div');
                element.textContent = value == null ? '' : String(value);
                return element.innerHTML;
            }

            var map = L.mapbox.map('map', null).setView([16, 100], 8);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            var tambonLayer = L.mapbox.featureLayer();
            tambonLayer.loadURL(jsonTambonRoute).on('ready', function (e) {
                var allLayer = e.target;
                map.fitBounds(allLayer.getBounds());
                allLayer.eachLayer(function (layer) {
                    var tam_name = escapeHtml(layer.feature.properties.TAM_NAME);
                    var pop = Number(layer.feature.properties.POP) || 0;
                    var area_km = turf.area(layer.feature) / 1000000;
                    var popup = "<h3>ต." + tam_name + "</h3>  มีพื้นที่ <b>" + area_km.toFixed(2) + "</b> ตร.กม.";
                    popup += "<p> มีประชากรอาสัยอยู่จำนวน <b>" + pop + "</b> คน";
                    popup += "<p> ความหนาแน่น <b>" + (pop / area_km).toFixed(2) + "</b> คน/ตร.กม.";
                    layer.bindPopup(popup);
                    layer.setStyle({
                        'weigth': 1,
                        'dashArray': 3,
                        'fillColor': style((pop / area_km))
                    });
                    layer.addTo(map);
                    layer.on('mouseover', function (e) {
                        e.target.setStyle({
                            'weight': 5
                        });
                    })
                    layer.on('mouseout', function (e) {
                        e.target.setStyle({
                            'weight': 1,
                            'dashArray': 3
                        });
                    })
                });


            })

            function style(a) {
                if (a <= 50)
                    return 'lime';
                if (a < 100)
                    return 'orange';
                if (a > 100)
                    return 'red';
            }

            var legend = L.control({position: 'topright'});

            legend.onAdd = function (map) {

                var div = L.DomUtil.create('div', 'info legend');
                var labels = ['<b>คำอธิบาย</b>'];
               
                labels.push('<i style="background:lime"></i>0 - 50 คน/ตร.กม.');
                labels.push('<i style="background:orange"></i>51 - 100 คน/ตร.กม.');
                labels.push('<i style="background:red"></i>มากกว่า 100 คน/ตร.กม. ');
                labels.push('');
                labels.push('ข้อมูลจากตาราง t_person_cid ');
                labels.push('ประชากรที่ยังมีชีวิตอยู่ type 1,3,5');



                div.innerHTML = labels.join('<br>');
                return div;
            };

            legend.addTo(map);

        </script>
    </body>
</html>

