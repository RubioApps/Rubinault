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
    <div class="fw"><?= Text::_('CHARGES'); ?></div>
</div>
<?php if ($page->data->online): ?>
    <section>
        <div id="cockpit" class="p-3">
            <div class="row text-center">
                <div class="col w-1">
                    <?php if ($page->data->history->prev): ?>
                        <a class="framed" href="<?= $factory->Link('charges', $page->data->vin, 'period=' . $page->data->history->prev); ?>">
                            <span class="bi bi-caret-left"></span>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="col w-8">
                    <h1><?= $page->data->history->period; ?></h1>
                </div>
                <div class="col w-1">
                    <?php if ($page->data->history->next): ?>
                        <a class="framed" href="<?= $factory->Link('charges', $page->data->vin, 'period=' . $page->data->history->next); ?>">
                            <span class="bi bi-caret-right"></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col text-center">
                    <span class="fs-5 text-secondary"><?= Text::_('CHARGE_TOTAL_ENERGY'); ?></span>
                    <p class="fs-2 fw p-0">
                        <span class="bi bi-plug me-1"></span>
                        <span class="d-block"><?= (int) $page->data->history->total; ?>&nbsp;Kwh</span>
                    </p>
                </div>
                <div class="col text-center">
                    <span class="fs-5 text-secondary"><?= Text::_('CHARGE_COUNT'); ?></span>
                    <p class="fs-2 fw p-0">
                        <span class="bi bi-hash me-1"></span>
                        <span class="d-block"><?= $page->data->history->count; ?></span>
                    </p>
                </div>
            </div>
        </div>
    </section>
    <section class="mt-5">
        <canvas id="charge-runchart"></canvas>
    </section>
    <section>
        <div class="accordion accordion-flush" id="accordionCharge">
            <?php foreach ($page->data->history->charges as $key => $charge): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="charge-header-<?= $key; ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#charge-content-<?= $key; ?>" aria-expanded="false" aria-controls="charge-content-<?= $key; ?>">
                            <div class="batteryContainer me-1 d-flex">
                                <div class="batteryOuter">
                                    <div class="batteryLevel" data-level="<?= $charge->end_level; ?>"></div>
                                </div>
                                <div class="batteryBump"></div>
                            </div>
                            <span class="fs-6 text-secondary me-2"><small><?= $charge->end_level; ?>%</small></span>
                            <?= ucfirst(Helpers::formatDate($charge->start_date, 'EEE d ' . Text::_('CHARGE_AT') . ' H:m')); ?>
                        </button>
                    </h2>
                    <div id="charge-content-<?= $key; ?>" class="accordion-collapse collapse" aria-labelledby="hvac-status-header" data-bs-parent="#accordionCharge">
                        <div class="accordion-body p-1">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold"><?= Text::_('CHARGE_START_LEVEL'); ?></div>
                                        <span class="text-secondary"><?= $charge->start_level; ?>%</span>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold"><?= Text::_('CHARGE_END_DATE'); ?></div>
                                        <span class="text-secondary">
                                            <?= ucfirst(Helpers::formatDate($charge->end_date, 'EEE d ' . Text::_('CHARGE_AT') . ' H:m')); ?>
                                        </span>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold"><?= Text::_('CHARGE_DURATION'); ?></div>
                                        <span class="text-secondary"><?= $charge->duration; ?> min.</span>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold"><?= Text::_('CHARGE_ENERGY_RECOVERED'); ?></div>
                                        <span class="text-secondary"><?= sprintf('%.2f', $charge->recovered); ?> Kwh</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php else: ?>
    <div class="rounded border p-3 w-75 w-md-50 mx-auto text-center">
        <span class="fs-3 bi bi-exclamation-triangle"></span>
        <span class="h3"><?= Text::_('NOT_AVAILABLE'); ?></span>
    </div>
<?php endif; ?>
<?php if ($page->data->online): ?>
    <script type="text/javascript">
        jQuery(document).ready(function() {

            $.rbno.showMenu();
            $.rbno.framed();

            $.rbno.online = <?= ($page->data->online || $config->test ? 'true' : 'false'); ?>;
            if ($.rbno.online || true) {

                $('.batteryLevel').each(function() {
                    let v = parseInt($(this).attr('data-level'));
                    let w = $(this).parent().width();
                    $bg = v > 25 ? '#73AD21' : '#EE0000';
                    $(this).css({
                        'width': (w * v / 100) + 'px',
                        'background-color': $bg
                    });
                });

                $.getScript($.rbno.livesite + '/templates/default/assets/chart.js', () => {
                    const data = {
                        datasets: <?= json_encode($page->data->history->datasets); ?>
                    };
                    const config = {
                        type: 'bar',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    };
                    const ctx = document.getElementById('charge-runchart');
                    new Chart(ctx, config);
                });

            } else {
                console.log('<?= addslashes($page->data->error); ?>');
                $.rbno.toast('<?= addslashes($page->data->error); ?>', true);
            }
        });
    </script>
<?php endif; ?>