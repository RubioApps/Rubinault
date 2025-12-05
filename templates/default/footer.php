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

use Rubinault\Framework\Request;
use Rubinault\Framework\Helpers;
use Rubinault\Framework\Language\Text;

$vin = Request::getVar('vin', null, 'GET');
?>
<div id="car-toolbar" class="container-xxl row row-cols-5 mx-auto h-100 p-1">
    <div class="col text-center">
        <a class="rbno-links-link" data-task="cockpit" data-scope="tcu" title="<?= Text::_('MENU_COCKPIT'); ?>">
            <span class="btn m-auto bi bi-speedometer"></span>
        </a>
    </div>
    <div class="col text-center">
        <a class="rbno-links-link" data-task="map" data-scope="tcu" title="<?= Text::_('MENU_MAP'); ?>">
            <span class="btn m-auto bi bi-globe"></span>
        </a>
    </div>
    <div class="col text-center">
        <a class="rbno-links-link" data-task="view" title="<?= Text::_('HOME'); ?>">
            <span class="btn m-auto bi bi-house"></span>
        </a>
    </div>
    <div class="col text-center">
        <a class="rbno-links-link" data-task="ev" data-scope="ev" title="<?= Text::_('MENU_EV'); ?>">
            <span class="btn m-auto bi bi-ev-front"></span>
        </a>
    </div>
    <div class="col text-center">
        <a class="rbno-links-link" data-task="schedule" data-scope="ev" title="<?= Text::_('MENU_SCHEDULE'); ?>">
            <span class="btn m-auto bi bi-calendar-check"></span>
        </a>
    </div>
</div>