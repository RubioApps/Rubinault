<?php

/**
 +-------------------------------------------------------------------------+
 | Rubinault  - Webapp Renault API                                         |
 | Version 1.0.0                                                           |
 |                                                                         |
 | This program is free software: you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License as published by    |
 | the Free Software Foundation.                                           |
 |                                                                         |
 | This file forms part of the Rubify software.                            |
 |                                                                         |
 | If you wish to use this file in another project or create a modified    |
 | version that will not be part of the Rubify Software, you               |
 | may remove the exception above and use this source code under the       |
 | original version of the license.                                        |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the            |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License       |
 | along with this program.  If not, see http://www.gnu.org/licenses/.     |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Jaime Rubio <jaime@rubiogafsi.com>                              |
 +-------------------------------------------------------------------------+
 */

defined('_RBNOEXEC') or die;

use Rubinault\Framework\Language\Text;

?>
<?php if ($page->data->online || $config->test): ?>
    <section>
        <div class="row mb-3">
            <div class="col">
                <button id="RefreshLocation" type="button" class="btn border rounded" data-method="post" data-endpoint="RefreshLocation">
                    <span class="bi bi-arrow-clockwise"></span>
                    <div class="d-block d-none d-sm-inline"><?= Text::_('REFRESH_LOCATION'); ?></div>
                </button>
            </div>
        </div>
    </section>
    <section>
        <div id="map"></div>
    </section>
<?php else: ?>
    <div class="rounded border p-3 w-75 w-md-50 mx-auto text-center">
        <span class="fs-3 bi bi-exclamation-triangle"></span>
        <span class="h3"><?= Text::_('NOT_AVAILABLE'); ?></span>
    </div>
<?php endif; ?>

<script type="text/javascript">
    jQuery(document).ready(function() {

        function setHeight() {
            windowHeight = $(window).height() - $('header').innerHeight() - $('footer').innerHeight();
            $('#map').css('min-height', windowHeight * .85);
        };

        $(window).resize(function() {
            setHeight();
        });

        $.rbno.online = <?= ($page->data->online || $config->test ? 'true' : 'false'); ?>;

        if ($.rbno.online) {        

            let map = null;
            let layer = null;
            let marker = null;            

            //Map
            setHeight();
            const opts = {
                center: [<?= $page->data->latitude; ?>, <?= $page->data->longitude; ?>],
                zoom: 17
            };

            $.getScript($.rbno.livesite+'/templates/default/assets/leaflet/leaflet.js', () => {

                $('head').append(
                    $('<link rel="stylesheet" type="text/css" />')
                    .attr('href', $.rbno.livesite+'/templates/default/assets/leaflet/leaflet.css')
                );

                map = new L.map('map', opts);
                layer = new L.TileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png');
                map.addLayer(layer);

                marker = new L.Marker([<?= $page->data->latitude; ?>, <?= $page->data->longitude; ?>]);
                marker.addTo(map);

                //Refresh the current location (+15s) then get the new gps position (+15s)
                let last = {
                    lat: 0,
                    lon: 0,
                    time: null
                };
                let s = null;
                $.rbno.timer = setInterval(() => {
                    $.getJSON($.rbno.livesite + '/?task=remote.post&vin=' + $.rbno.qs('vin') + '&endpoint=RefreshLocation', function(response) {
                        if (response.success && response.data) {
                            $.getJSON($.rbno.livesite + '/?task=map.gps&vin=' + $.rbno.qs('vin') + '&format=json', function(data) {
                                if (data.success) {
                                    marker.setLatLng([data.latitude, data.longitude]);
                                    map.panTo(new L.LatLng(data.latitude, data.longitude));
                                }
                            });
                        }
                    });
                }, 300 * 1000);
            });

            //Refresh Location
            const RefreshLocation = $('#RefreshLocation');
            $.rbno.kamereon(RefreshLocation, () => {
                $.getJSON($.rbno.livesite + '/?task=map.gps&vin=' + $.rbno.qs('vin') + '&format=json', function(data) {
                    if (data.success) {
                        marker.setLatLng([data.latitude, data.longitude]);
                        map.panTo(new L.LatLng(data.latitude, data.longitude));
                    }
                });
            });

        } else {
            console.log('<?= addslashes($page->data->error); ?>');
            $.rbno.toast('<?= Text::_($page->data->error, true); ?>', true);
        }
    });
</script>
