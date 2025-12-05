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
<?php if ($page->data->online): ?>
    <section>
        <div class="bg-body-tertiary rounded p-3">
            <div class="row">
                <div class="col text-center">
                    <span class="fs-5 text-secondary"><?= Text::_('BATTERY_LEVEL'); ?></span>
                    <p class="fs-2 fw p-0">
                        <span class="bi bi-battery-charging mx-1 d-block"></span>
                        <?= $page->data->status->remaining ?? '-'; ?>%
                    </p>
                </div>
                <div class="col text-center">
                    <span class="fs-5 text-secondary"><?= Text::_('BATTERY_AUTONOMY'); ?></span>
                    <p class="fs-2 fw p-0">
                        <span class="bi bi-arrow-bar-right mx-1 d-block"></span>
                        <?= $page->data->status->autonomy ?? '-'; ?>&nbsp;Km
                    </p>
                </div>
                <div class="col text-center">
                    <span class="fs-5 text-secondary"><?= Text::_('CHARGE_PLUGGED'); ?></span>
                    <p class="fs-2 fw p-0">
                        <span class="bi bi-plug mx-1 d-block"></span>
                        <?= $page->data->status->plugged ? Text::_('ON') : Text::_('OFF'); ?>
                    </p>
                </div>                
                <div class="col text-center">
                    <span class="fs-5 text-secondary"><?= Text::_('CHARGE_STATUS'); ?></span>
                    <p class="fs-2 fw p-0">
                        <span class="bi bi-lightning-charge mx-1 d-block"></span>
                        <?= $page->data->status->chargestatus ? Text::_('ON') : Text::_('OFF'); ?>
                    </p>
                </div>
                <div class="col text-center">
                    <span class="fs-5 text-secondary"><?= Text::_('HVAC_STATUS'); ?></span>
                    <p class="fs-2 fw p-0">
                        <span class="bi bi-cup-hot mx-1 d-block"></span>
                        <?= ($page->data->hvac->status != 'off') ? Text::_('ON') : Text::_('OFF'); ?>
                    </p>
                </div>                
            </div>
        </div>
    </section>
    <section class="mt-5">
        <?php if ($page->data->online || $config->test): ?>
            <div class="container mt-3">
                <div class="row row-cols-3">
                    <div class="col">
                        <button id="RefreshBatteryStatus" type="button" class="btn border w-100" data-method="post" data-endpoint="RefreshBatteryStatus">
                            <span class="text-truncate d-block"><?= Text::_('REFRESH'); ?></span>
                            <span class="bi bi-arrow-clockwise fs-1 d-block"></span>
                        </button>
                    </div>
                    <div class="col">
                        <button id="InstantCharge" type="button" class="btn border w-100"
                            data-method="post"
                            data-endpoint="ChargingStart"
                            data-status="<?= $page->data->charge_status; ?>">
                            <span class="text-truncate d-block"><?= Text::_('CHARGE_START'); ?></span>
                            <span class="bi bi-lightning-charge fs-1"></span>
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="rounded border p-3 w-75 w-md-50 mx-auto text-center">
                <span class="fs-3 bi bi-exclamation-triangle"></span>
                <span class="h3"><?= Text::_('NOT_AVAILABLE'); ?></span>
            </div>
        <?php endif; ?>
    </section>
    <section class="mt-5">
        <div class="fs-3">
            <div class="row bg-body-tertiary border m-2">
                <div class="col p-3">
                    <a class="nav-link fw framed" href="<?= $factory->Link('settings', $page->data->vin); ?>">
                        <span class="bi bi-sliders"></span>
                        <?= Text::_('PROGRAM_SETTINGS'); ?>
                    </a>
                </div>
            </div>
            <div class="row bg-body-tertiary border m-2">
                <div class="col p-3">
                    <a class="nav-link fw framed" href="<?= $factory->Link('soc', $page->data->vin); ?>">
                        <span class="bi bi-bounding-box"></span>
                        <?= Text::_('CHARGE_LIMITS'); ?>
                    </a>
                </div>
            </div>
            <div class="row bg-body-tertiary border m-2">
                <div class="col p-3">
                    <a class="nav-link fw framed" href="<?= $factory->Link('charges', $page->data->vin); ?>">
                        <span class="bi bi-calendar-week"></span>
                        <?= Text::_('CHARGE_HISTORY'); ?>
                    </a>
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

        $.rbno.showMenu();
        $.rbno.framed();

        $.rbno.online = <?= ($page->data->online || $config->test ? 'true' : 'false'); ?>;
        if ($.rbno.online) {

            //Refresh Charge status
            const RefreshBatteryStatus = $('#RefreshBatteryStatus');
            $.rbno.kamereon(RefreshBatteryStatus);

            //Instant Charge      
            const InstantCharge = $('#InstantCharge');
            let ChargeStatus = InstantCharge.attr('data-status');
            ChargeStatus = (ChargeStatus != 'on' ? 'off' : 'on');
            if (ChargeStatus == 'off') {
                InstantCharge.attr('data-endpoint', 'ChargingStart');
            } else {
                InstantCharge.attr('data-endpoint', 'ChargingStop');
            }
            $.rbno.kamereon(InstantCharge, () => {
                InstantCharge.attr('data-status', ChargeStatus != 'on' ? 'on' : 'off');
            });            

        } else {
            console.log('<?= addslashes($page->data->error); ?>');
            $.rbno.toast('<?= Text::_($page->data->error, true); ?>', true);
        }
    });
</script>