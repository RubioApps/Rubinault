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

$weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
error_log(print_r($page->data->programs, true));
?>
<?php if ($page->data->online || $config->test): ?>
    <section>
        <div class="accordion accordion-flush" id="accordionPrograms">
            <?php
            foreach ($page->data->programs as $key => $p):
                $p = (object) $p;
            ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="program-<?= $key; ?>-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#program-<?= $key; ?>-details">
                            <?= Text::_('CHARGE_PROGRAM') . ' ' . Text::_('CHARGE_AT') . ' ' . $p->start; ?>
                        </button>
                    </h2>
                    <div id="program-<?= $key; ?>-details" class="accordion-collapse collapse p-2" aria-labelledby="program-<?= $key; ?>-header" data-bs-parent="#accordionPrograms">
                        <div class="container bg-dark bg-gradient">
                            <div class="row justify-content-start">
                                <div class="col-auto form-check form-switch my-auto">
                                    <div class="form-check">
                                        <input id="program-<?= $key; ?>-enabled" class="form-check-input" type="checkbox" <?= $p->status ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                                <label class="col-auto col-form-label" for="program-<?= $key; ?>-enabled"><?= Text::_('CHARGE_ENABLED'); ?></label>
                            </div>
                            <div class="row justify-content-start">
                                <div class="col-auto form-check form-switch my-auto">
                                    <div class="form-check">
                                        <input id="precond-<?= $key; ?>" class="form-check-input" type="checkbox" <?= $p->type == 'PRECONDITIONING' ? 'checked' : ''; ?> />
                                    </div>
                                </div>
                                <label class="col-auto col-form-label" for="precond-<?= $key; ?>"><?= Text::_('CHARGE_PRECONDITIONING'); ?></label>
                            </div>
                            <div class="row justify-content-start">
                                <div class="col-auto form-check form-switch my-auto">
                                    <div class="form-check">
                                        <input id="schedule-<?= $key; ?>" class="form-check-input" type="checkbox" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#pane-<?= $key; ?>"  
                                            <?= $p->type == 'V2G' ? 'checked' : ''; ?>
                                            />
                                    </div>
                                </div>
                                <label class="col-auto col-form-label" for="schedule-<?= $key; ?>"><?= Text::_('CHARGE_TYPE'); ?></label>
                            </div>
                            <div class="row ms-1 justify-content-start my-3 <?= $p->type == 'V2G' ? '' : 'collapse'; ?>" id="pane-<?= $key; ?>">
                                <div class="btn-group" role="group" aria-label="Basic example">
                                    <input id="type-<?= $key; ?>-plan" name="type-<?= $key; ?>" class="btn-check" type="radio" <?= $p->type == 'V2G' ? '' : 'checked'; ?>>
                                    <label class="btn btn-outline-primary w-50" for="type-<?= $key; ?>-plan">
                                        <?= Text::_('CHARGE_TYPE_SCHEDULED'); ?>
                                    </label>

                                    <input id="type-<?= $key; ?>-v2g" name="type-<?= $key; ?>" class="btn-check" type="radio" <?= $p->type == 'V2G' ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary w-50" for="type-<?= $key; ?>-v2g">
                                        <?= Text::_('CHARGE_TYPE_V2G'); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="row ms-1 justify-content-center">
                                <label class="col-form-label col-sm-2" for="program-<?= $key; ?>-start"><?= Text::_('CHARGE_START'); ?></label>
                                <div class="col-sm-10">
                                    <input id="program-<?= $key; ?>-start" class="form-control" type="time" min="00:00" max="23:59" step="300" value="<?= $p->start; ?>" />
                                </div>
                            </div>
                            <div class="row m-2 justify-content-center">
                                <?php foreach ($weekdays as $day): ?>
                                    <div class="col-auto g-2">
                                        <?= $p->day; ?>
                                        <button id="program-<?= $key; ?>-<?= $day; ?>"
                                            type="button" class="btn btn-outline-secondary <?= ($p->$day ? 'active' : ''); ?>"
                                            data-bs-toggle="button">
                                            <?= Helpers::formatDate($day . ' this week', 'EEEEE'); ?>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="row rounded rbno-program">
            <div class="col text-end">
                <button id="program-add" type="button" class="btn border rounded-circle">
                    <span class="bi bi-plus"></span>
                </button>
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

            //Charge Mode
            const ChargeMode = $('#charge-mode');
            ChargeMode.data('payload', {
                'action': ChargeMode.is(':checked') ? 'always_charging' : 'schedule_mode'
            });
            $.rbno.kamereon(ChargeMode);

            const ToggleHvacStatus = $('#ToggleHvacStatus');
            //HVAC is off, then start
            if (!ToggleHvacStatus.is(':checked')) {
                ToggleHvacStatus.data('payload', {
                    'action': 'start',
                    'targetTemperature': '20'
                });
                //HVAC is on, then stop
            } else {
                ToggleHvacStatus.data('payload', {
                    'action': 'cancel'
                });
            }

            $.rbno.kamereon(ToggleHvacStatus, function(response) {
                const ToggleHvacStatus = $('#ToggleHvacStatus');
                if (response.success) {
                    if (ToggleHvacStatus.is(':checked')) {
                        ToggleHvacStatus.data('payload', {
                            'action': 'start',
                            'targetTemperature': '20'
                        });
                    } else {
                        ToggleHvacStatus.data('payload', {
                            'action': 'cancel'
                        });
                    }
                }
                return response.success;
            });

            //Reset the time
            $('button.btn-reset-ready').on('click', function(e) {
                e.preventDefault();
                const target = $(this).attr('data-reset');
                $('#' + target).val('');
            });

            $('#btn-schedule-save').on('click', function(e) {
                e.preventDefault();
                let schedule = {
                    'mode': 'scheduled',
                    'schedules': {}
                };
                const weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

                let active = false;
                let dayValue = null;
                let cast = {};

                for (i = 0; i < 5; i++) {
                    active = $('#schedule-active-' + i).is(':checked');
                    schedule.schedules[i + 1] = {
                        'id': i + 1,
                        'activated': active
                    };
                    if (active) {
                        for (j = 1; j <= 7; j++) {
                            dayValue = $('#ready-' + i + '-' + weekdays[j - 1]).val();
                            if (dayValue) {
                                date = new Date('1970-01-01 ' + dayValue + ':00');
                                formattedDate = $.format.date(date, 'THH:mmZ');
                                cast = {
                                    [weekdays[j - 1]]: {
                                        'readyAtTime': formattedDate
                                    }
                                };
                                schedule.schedules[i + 1] = $.extend(schedule.schedules[i + 1], cast);
                            }
                        }
                    }
                }

                const ScheduleSave = $('#hvac-schedule-save');
                ScheduleSave.data('payload', schedule);
                $.rbno.kamereon(ScheduleSave, function(data) {
                    console.log(data);
                });
                ScheduleSave.trigger('click');

            });

            $.getScript($.rbno.livesite + '/templates/default/assets/jquery.knob.js', () => {
                $('#setpoint').knob({
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
                });
                $('#setpoint').val(20).trigger('change');
            });

        } else {
            console.log('<?= addslashes($page->data->error); ?>');
            $.rbno.toast('<?= Text::_($page->data->error, true); ?>', true);
        }
    });
</script>