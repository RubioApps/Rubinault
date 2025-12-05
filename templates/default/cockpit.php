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

use Rubinault\Framework\Helpers;
use Rubinault\Framework\Language\Text;

?>
<?php if ($page->data->online || $config->test): ?>
    <section>
        <div id="cockpit" class="p-3">
            <div class="row mx-auto mt-3 text-center">
                <div class="col">
                    <div class="bi bi-arrow-bar-right fs-3" data-title="<?= Text::_(strtoupper('fuelAutonomy')); ?>"></div>
                    <span class="value"><?= $page->data->autonomy; ?>&nbsp;Km</span>
                </div>
                <div class="col">
                    <div class="bi bi-<?= $page->data->ev ? 'battery-charging' : 'fuel-pump'; ?> fs-3" data-title="<?= Text::_(strtoupper('fuelQuantity')); ?>"></div>
                    <span class="value"><?= $page->data->remaining; ?></span>
                </div>
                <div class="col">
                    <div class="bi bi-arrows fs-3" data-title="<?= Text::_(strtoupper('totalMileage')); ?>"></div>
                    <span class="value"><?= $page->data->mileage; ?>&nbsp;Km</span>
                </div>
            </div>
        </div>        
            <div class="container mt-3">
                <div class="row">
                    <div class="col m-1">
                        <button id="InstantHorn" type="button" class="btn border w-100" data-method="post" data-endpoint="Horn">
                            <span class="text-truncate d-block"><?= Text::_('HORN'); ?></span>
                            <span class="bi bi-volume-up fs-1"></span>
                        </button>
                    </div>
                    <div class="col m-1">
                        <button id="InstantLights" type="button" class="btn border w-100" data-method="post" data-endpoint="Lights">
                            <span class="text-truncate d-block"><?= Text::_('LIGHTS'); ?></span>
                            <span class="bi bi-lightbulb fs-1"></span>
                        </button>
                    </div>
                    <div class="col m-1">
                        <button id="HornAndLights" type="button" class="btn border w-100" data-method="post" data-endpoint="HornAndLights">
                            <span class="text-truncate d-block"><?= Text::_('HORN_LIGHTS'); ?></span>
                            <span class="bi bi-question-circle fs-1"></span>
                        </button>
                    </div>                    
                    <?php if (Helpers::isEV($page->data->vin)): ?>
                    <div class="col m-1">
                        <button id="InstantHVAC" type="button" class="btn border w-100"
                            data-method="post"
                            data-endpoint="HvacStart"
                            data-status="<?= $page->data->hvac->status; ?>">
                            <span class="text-truncate d-block"><?= Text::_('HVAC_START'); ?></span>
                            <span class="bi bi-fan fs-1"></span>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (Helpers::isEV($page->data->vin)): ?>
            <div class="container mt-3">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-0 me-auto">
                            <div class="fs-5 fw-bold"><?= Text::_('HVAC_INTERNAL_TEMPERATURE'); ?></div>
                            <span class="fs-6 text-secondary">
                                <?= $page->data->hvac->temperature ?? '--'; ?>°C
                            </span>
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-0 me-auto">
                            <div class="fs-5 fw-bold"><?= Text::_('HVAC_SETPOINT'); ?></div>
                            <span class="fs-6 text-secondary">
                                <?= $page->data->hvac->setpoint ?? '--'; ?>°C
                            </span>
                        </div>
                    </li>
                </ul>
            </div>
        <?php endif; ?>

        <?php if($page->data->alerts):?>
        <div class="row row-cols-2 mt-5">
            <div class="col-6">
                <div class="mb-2 text-center">
                    <div class="btn border car-door-status">
                        <span class="car-door-status-icon bi bi-lock"></span>
                        <span class="car-door-status-label d-none d-sm-line"></span>
                    </div>
                </div>
                <div class="row justiy-content-center mx-auto" style="width:90px">
                    <div class="col mt-3 p-0 text-center">
                        <div class="m-0">
                            <img class="car-door" data-target="car-door-driver" src="<?= RBNO_BLANK ?>" width="15" />
                        </div>
                        <div class="mt-1">
                            <img class="car-door" data-target="car-door-rear-left" src="<?= RBNO_BLANK ?>" width="15" />
                        </div>
                    </div>
                    <div class="col p-0">
                        <img class="p-0 m-0" src="<?= $factory->getAssets() . '/images/car-top-view.png'; ?>" width="45" />
                        <img class="p-0 m-0" src="<?= $factory->getAssets() . '/images/car-hatch.png'; ?>" width="45" />
                    </div>
                    <div class="col mt-3 p-0 text-center">
                        <div class="m-0">
                            <img class="car-door" data-target="car-door-passenger" src="<?= RBNO_BLANK ?>" width="15" />
                        </div>
                        <div class="mt-1">
                            <img class="car-door" data-target="car-door-rear-right" src="<?= RBNO_BLANK ?>" width="15" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="mb-2 text-center">
                    <div class="btn border car-door-status">
                        <span class="car-pressure-icon bi bi-exclamation-circle"></span>
                        <span class="car-pressure-label d-none d-sm-line"></span>
                    </div>
                </div>
                <div class="row justiy-content-center mx-auto" style="width:60px">
                    <div class="col p-0 text-center">
                        <div class="mt-2 p-0">
                            <img src="<?= $factory->getAssets() . '/images/car-wheel-white.png'; ?>" width="5" />
                        </div>
                        <div class="mt-4 p-0">
                            <img src="<?= $factory->getAssets() . '/images/car-wheel-white.png'; ?>" width="5" />
                        </div>
                    </div>
                    <div class="col p-0">
                        <img src="<?= $factory->getAssets() . '/images/car-top-view.png'; ?>" width="45" />
                    </div>
                    <div class="col p-0 text-center">
                        <div class="mt-2 p-0">
                            <img src="<?= $factory->getAssets() . '/images/car-wheel-white.png'; ?>" width="5" />
                        </div>
                        <div class="mt-4 p-0">
                            <img src="<?= $factory->getAssets() . '/images/car-wheel-white.png'; ?>" width="5" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </section>
<?php else: ?>
    <div class="rounded border p-3 w-75 w-md-50 mx-auto text-center">
        <span class="fs-3 bi bi-exclamation-triangle"></span>
        <span class="h3"><?= Text::_('NOT_AVAILABLE'); ?></span>
    </div>
<?php endif; ?>

<script type="text/javascript">
    jQuery(document).ready(function() {

        $.rbno.online = <?= ($page->data->online || $config->test ? 'true' : 'false'); ?>;

        if ($.rbno.online) {

            //Horn         
            const InstantHorn = $('#InstantHorn');
            $.rbno.kamereon(InstantHorn);

            //Lights
            const InstantLights = $('#InstantLights');
            $.rbno.kamereon(InstantLights, () => {
                InstantLights.addClass('text-warning');
                setTimeout(function() {
                    InstantLights.removeClass('text-warning');
                }, 3000);
            });

            const HornAndLights = $('#HornAndLights');
            $.rbno.kamereon(HornAndLights);

            //HVAC            
            const InstantHVAC = $('#InstantHVAC');
            let HvacStatus = InstantHVAC.attr('data-status');
            HvacStatus = (HvacStatus != 'on' ? 'off' : 'on');
            if (HvacStatus == 'off') {
                InstantHVAC.removeClass('text-warning');
                InstantHVAC.attr('data-endpoint', 'HvacStart');
                InstantHVAC.data('payload', {
                    'targetTemperature': 20
                });
            } else {
                InstantHVAC.addClass('text-warning');
                InstantHVAC.attr('data-endpoint', 'HvacStop');
            }

            $.rbno.kamereon(InstantHVAC, (response) => {
                if (response.success) {
                    HvacStatus = InstantHVAC.attr('data-status');
                    HvacStatus = (HvacStatus != 'on' ? 'on' : 'off');
                    InstantHVAC.attr('data-status',HvacStatus);
                    if (HvacStatus == 'off') {
                        InstantHVAC.removeClass('text-warning');
                        InstantHVAC.attr('data-endpoint', 'HvacStart');
                        InstantHVAC.data('payload', {
                            'targetTemperature': 20
                        });
                    } else {
                        InstantHVAC.addClass('text-warning');
                        InstantHVAC.attr('data-endpoint', 'HvacStop');
                    }
                }

            });

            const assets = '<?= $factory->getAssets() . '/images/'; ?>';
            const blank = '<?= RBNO_BLANK; ?>';
            const locked = <?= $page->data->lock->lockStatus == 'unlocked' ? 'false' : 'true'; ?>;

            let doorStates = {
                'car-door-driver': <?= $page->data->lock->doorStatusDriver == 'closed' ? 'false' : 'true'; ?>,
                'car-door-passenger': <?= $page->data->lock->doorStatusPassenger == 'closed' ? 'false' : 'true'; ?>,
                'car-door-rear-left': <?= $page->data->lock->doorStatusRearLeft == 'closed' ? 'false' : 'true'; ?>,
                'car-door-rear-right': <?= $page->data->lock->doorStatusRearRight == 'closed' ? 'false' : 'true'; ?>
            };

            if (locked) {
                $('.car-door-status-icon').removeClass('bi-unlock').addClass('bi-lock');
                $('.car-door-status-label').html($.rbno.labels['locked']);
            } else {
                $('.car-door-status-icon').removeClass('bi-lock').addClass('bi-unlock');
                $('.car-door-status-label').html($.rbno.labels['unlocked']);
            }

            $('img.car-door').each(function() {
                const target = $(this).attr('data-target');
                if (doorStates[target]) $(this).attr('src', assets + target + '.png');
                else $(this).attr('src', blank);
            });

        } else {
            console.log('<?= addslashes($page->data->error); ?>');
            $.rbno.toast('<?= Text::_($page->data->error, true); ?>', true);
        }
    });
</script>