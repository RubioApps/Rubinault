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
<div class="fs-3 py-3 mb-1 d-flex">
    <a class="framed" href="<?= $factory->Link('ev', $page->data->vin); ?>">
        <div class="btn border rounded me-2">
            <span class="bi bi-arrow-left"></span>
        </div>
    </a>
    <div class="fw"><?= Text::_('PROGRAM_SETTINGS'); ?></div>
</div>
<?php if ($page->data->online || $config->test): ?>
    <section>
        <div class="accordion accordion-flush" id="accordionSettings">
            <!--  Charge Settings -->
            <div class="accordion-item">
                <div class="accordion-header" id="charge-settings-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#charge-settings" aria-expanded="false" aria-controls="charge-settings">
                        <?= Text::_('HVAC_SETTINGS'); ?>
                    </button>
                </div>
                <div id="charge-settings" class="accordion-collapse collapse" aria-labelledby="charge-settings-header" data-bs-parent="#accordionSettings">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item p-3">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold"><?= Text::_('HVAC_SCHEDULE_OPTIONS'); ?></div>
                                <div class="text-secondary mb-3"><small><?= Text::_('HVAC_SCHEDULE_OPTIONS_TXT'); ?></small></div>
                                <div class="form-check form-switch my-2">
                                    <input id="HvacEnabled" class="form-check-input" type="checkbox" value="<?= $page->data->settings->hvac; ?>" />
                                    <label class="form-check-label" for="HvacEnabled"><?= Text::_('HVAC_ENABLED'); ?></label>
                                </div>
                                <div class="form-check form-switch my-2">
                                    <input id="SteeringWheel" class="form-check-input" type="checkbox" value="<?= $page->data->settings->steering_wheel; ?>" />
                                    <label class="form-check-label" for="SteeringWheel"><?= Text::_('HVAC_STEERING_WHEEL'); ?></label>
                                </div>
                                <div class="form-check form-switch my-2">
                                    <input id="SeatLeft" class="form-check-input" type="checkbox" value="<?= $page->data->settings->seat_left; ?>" />
                                    <label class="form-check-label" for="SeatLeft"><?= Text::_('HVAC_SEAT_LEFT'); ?></label>
                                </div>
                                <div class="form-check form-switch my-2">
                                    <input id="SeatRight" class="form-check-input" type="checkbox" value="<?= $page->data->settings->seat_right; ?>" />
                                    <label class="form-check-label" for="SeatRight"><?= Text::_('HVAC_SEAT_RIGHT'); ?></label>
                                </div>
                            </div>
                        </li>
                        <?php if ($page->data->hvac->threshold): ?>
                            <li class="list-group-item p-3">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><?= Text::_('HVAC_SOC_THRESHOLD'); ?></div>
                                    <div class="text-secondary mb-3"><small><?= Text::_('HVAC_SOC_THRESHOLD_TXT'); ?></small></div>
                                    <span id="HvacThreshold" class="text-secondary" data-method="post" data-endpoint="HvacThreshold" data-value="<?= $page->data->hvac->threshold; ?>">
                                        <?= $page->data->hvac->threshold; ?>%
                                    </span>
                                    <div id="HvacThresholdSlider" class="m-2"></div>
                                </div>
                            </li>
                        <?php endif; ?>
                        <li class="list-group-item p-3">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold"><?= Text::_('HVAC_SETPOINT'); ?></div>
                                <div class="m-3 mx-auto text-center">
                                    <input id="HvacSetpoint" type="text" value="<?= $page->data->hvac->setpoint; ?>" />
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <!--  HVAC Status -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="hvac-status-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hvac-status" aria-expanded="false" aria-controls="hvac-status">
                        <?= Text::_('HVAC_STATUS'); ?> (<?= ucfirst(Helpers::formatDate($page->data->hvac->timestamp, 'EEE d ' . Text::_('CHARGE_AT') . ' H:m')); ?>)
                    </button>
                </h2>
                <div id="hvac-status" class="accordion-collapse collapse" aria-labelledby="hvac-status-header" data-bs-parent="#accordionSettings">
                    <div class="accordion-body p-1">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="ms-2 me-auto">
                                    <div class="d-flex">
                                        <div class="fw-bold w-100"><?= Text::_('HVAC_STARTED'); ?></div>
                                        <div class="me-0">
                                            <div class="form-check form-switch">
                                                <input id="ToggleHvacStatus" class="form-check-input command" type="checkbox" role="switch"
                                                    data-method="post"
                                                    data-endpoint="HvacStart"
                                                    <?= ($page->data->hvac->status != 'off') ? 'checked' : ''; ?> />
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-secondary"><?= ($page->data->hvac->status != 'off') ? 'ON' : 'OFF'; ?> </span>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">
                                        <?= Text::_('HVAC_INTERNAL_TEMPERATURE'); ?>
                                    </div>
                                    <span class="text-secondary">
                                        <?= $page->data->status->temperature ?? '--'; ?>°C
                                    </span>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><?= Text::_('HVAC_EXTERNAL_TEMPERATURE'); ?></div>
                                    <span class="text-secondary"><?= $page->data->hvac->outside ?? '--'; ?>°C</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
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

        $.rbno.online = <?= ($page->data->online || $config->test ? 'true' : 'false'); ?>;
        if ($.rbno.online) {

            const ToggleHvacStatus = $('#ToggleHvacStatus');
            function toggleHVAC() {
                if (ToggleHvacStatus.is(':checked')) {
                    ToggleHvacStatus.attr('data-endpoint', 'HvacStop');
                } else {
                    ToggleHvacStatus.attr('data-endpoint', 'HvacStart');
                    ToggleHvacStatus.data('payload', {
                        'targetTemperature': $('#HvacSetpoint').val()
                    });
                }
                $.rbno.kamereon(ToggleHvacStatus, function(response) {
                    toggleHVAC();                    
                    return response.success;
                });
            }

            //Toggle HVAC
            toggleHVAC();

            const HvacThreshold = $('#HvacThreshold');
            const HvacThresholdSlider = $('#HvacThresholdSlider');
            HvacThresholdSlider.slider({
                range: 'min',
                min: 0,
                max: 100,
                step: 5,
                value: HvacThreshold.attr('data-value'),
                slide: function(event, ui) {
                    HvacThreshold.attr('data-value', ui.value);
                    HvacThreshold.html(ui.value + '%');
                },
                change: function(event, ui) {
                    HvacThreshold.data('payload', {
                        'socThreshold': HvacThresholdSlider.slider('value')
                    });
                    $.rbno.kamereon(HvacThreshold);
                    HvacThreshold.trigger('click');
                }
            });



            $.getScript($.rbno.livesite + '/templates/default/assets/jquery.knob.js', () => {
                const HvacSetpoint = $('#HvacSetpoint');
                HvacSetpoint.knob({
                    min: 15,
                    max: 25,
                    step: 1,
                    angleOffset: -115,
                    angleArc: 230,
                    stopper: true,
                    skin: 'tron',
                    thickness: 0.25,
                    width: 200,
                    height: 200,
                    displayPrevious: false,
                    fgColor: 'darkorange',
                    inputColor: 'darkorange',
                    bgColor: '#343a40',
                    'release': function(v) {
                        ToggleHvacStatus.data('payload', {
                            'targetTemperature': v
                        });
                        $.rbno.kamereon(ToggleHvacStatus);
                    }
                });
                //HvacSetpoint.trigger('change');
            });

        } else {
            console.log('<?= addslashes($page->data->error); ?>');
            $.rbno.toast('<?= Text::_($page->data->error, true); ?>', true);
        }
    });
</script>