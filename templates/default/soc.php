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
<div class="fs-3 py-3 mb-1 d-flex">
    <a class="framed" href="<?= $factory->Link('ev', $page->data->vin); ?>">
        <div class="btn border rounded me-2">
            <span class="bi bi-arrow-left"></span>
        </div>
    </a>
    <div class="fw"><?= Text::_('CHARGE_LIMITS'); ?></div>
</div>
<?php if ($page->data->online): ?>
    <section class="mx-auto" style="max-width: 600px;">
        <div class="threshold-container">
            <img src="<?= $page->data->images[0]; ?>" class="threshold-car" />
            <img src="<?= RBNO_BLANK; ?>" class="threshold-min-overlay" />
            <img src="<?= RBNO_BLANK; ?>" class="threshold-max-overlay" />
            <div class="threshold-min-text p-2"></div>
            <div class="threshold-max-text p-2"></div>
        </div>
        <div id="socLimits"></div>
    </section>
    <section class="mt-5">
        <div class="fs-3">
            <button id="ChargingSettings" type="button" class="btn btn-primary w-100 border rounded" data-method="post" data-endpoint="ChargeSettings">
                <span class="bi bi-floppy"></span>
                <?= Text::_('SAVE'); ?>
            </button>
        </div>
    </section>
<?php else: ?>
    <div class="rounded border p-3 w-75 w-md-50 mx-auto text-center">
        <span class="fs-3 bi bi-exclamation-triangle"></span>
        <span class="h3"><?= Text::_('NOT_AVAILABLE'); ?></span>
    </div>
<?php endif; ?>
<script type="text/javascript">
    jQuery(document).ready(function() {

        $.rbno.showMenu();
        $.rbno.framed();

        $.rbno.online = <?= ($page->data->online || $config->test ? 'true' : 'false'); ?>;
        if ($.rbno.online || true) {

            let min = parseInt('<?= $page->data->min; ?>');
            let max = parseInt('<?= $page->data->max; ?>');

            //Set charge threshold
            const ChargingSettings = $('#ChargingSettings');
            $.rbno.kamereon(ChargingSettings);

            //State Of Charge     
            const socLimits = $('#socLimits');
            $('.threshold-min-overlay').css('width', min + '%');
            $('.threshold-max-overlay').css('width', (100 - max) + '%');
            $('.threshold-min-text').html(min + '%');
            $('.threshold-max-text').html(max + '%');
            socLimits.slider({
                range: true,
                min: 0,
                max: 100,
                step: 5,
                values: [min, max],
                slide: function(event, ui) {
                    $('.threshold-min-overlay').css('width', ui.values[0] + '%');
                    $('.threshold-max-overlay').css('width', (100 - ui.values[1]) + '%');
                    $('.threshold-min-text').html(ui.values[0] + '%');
                    $('.threshold-max-text').html(ui.values[1] + '%');
                },
                change: function(event, ui) {
                    ChargingSettings.data('payload', {
                        'socMin': socLimits.slider('values', 0),
                        'socTarget': socLimits.slider('values', 1),
                    });
                    $.rbno.kamereon(ChargingSettings);
                }
            });

        } else {
            console.log('<?= addslashes($page->data->error); ?>');
            $.rbno.toast('<?= addslashes($page->data->error); ?>', true);
        }
    });
</script>