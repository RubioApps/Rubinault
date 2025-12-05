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
<nav class="container-xxl flex-wrap flex-xxl-nowrap">
    <div class="d-flex">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainmenu">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
    <a class="navbar-brand rbno-brand text-center text-truncate" href="<?= $config->live_site; ?>">
        <div class="text-center">
            <img class="p-0" src="<?= $factory->getAssets() . '/favicons/rubinault.png'; ?>" width="30" />
            <span class="h4 fw-bold" style="position:relative;top:3px"><?= $config->sitename; ?></span>
        </div>
    </a>
    <div class="collapse navbar-collapse" id="mainmenu">
        <?php if (is_array($page->menu)): ?>
            <ul class="navbar-nav mt-1 ms-2 w-100">
                <?php foreach ($page->menu as $item): ?>
                    <li class="nav-item">
                        <a class="nav-link framed" href="<?= $item->link; ?>">
                            <div class="d-flex">
                                <div class="float-start">
                                    <img class="img-fluid" src="<?= $item->image; ?>" width="96" />
                                </div>
                                <div class="float-start">
                                    <div class="row g-0 text-nowrap"><?= Text::_($item->label) ?></div>
                                    <div class="row g-0 fs-6 text-secondary"><?= $item->plate; ?></div>
                                </div>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($user->isLogged()): ?>
            <div class="mt-1 mb-2">
                <a class="nav-link p-0 mt-0 ms-1" href="<?= $factory->Link('login.off'); ?>">
                    <div class="btn btn-secondary">
                        <span class="bi bi-power"></span>
                        <span class="d-inline"><?= Text::_('LOGOUT'); ?></span>
                    </div>
                </a>
            </div>
        <?php endif; ?>
    </div>
</nav>