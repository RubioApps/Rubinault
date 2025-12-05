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
<section>
    <div class="fs-3 text-center"><?= $page->data->brand . ' ' . $page->data->model; ?></div>
    <div class="fs-5 text-secondary text-center"><?= $page->data->plate; ?></div>
    <div class="row d-flex mt-5">
        <div class="col-12 col-sm-6">
            <div id="carouselPictures" class="carousel carousel-dark slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($page->data->images as $key => $src): ?>
                        <div class="carousel-item<?= (!$key ? ' active' : ''); ?>">
                            <img src="<?= $src; ?>" class="d-block w-100 pb-2" alt="" />
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-indicators">
                    <?php foreach ($page->data->images as $key => $src) : ?>
                        <button type="button" data-bs-target="#carouselPictures" class="bg-white m-1<?= (!$key ? ' active' : ''); ?>" data-bs-slide-to="<?= $key; ?>"></button>
                    <?php endforeach; ?>
                </div>                
            </div>
        </div>
        <div class="col-12 col-sm-6">
            <div class="accordion accordion-flush" id="accordionView">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingInfo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfo" aria-expanded="false" aria-controls="collapseInfo">
                            <?= Text::_('INFO'); ?>
                        </button>
                    </h2>
                    <div id="collapseInfo" class="accordion-collapse collapse" aria-labelledby="headingInfo" data-bs-parent="#accordionView">
                        <div class="accordion-body">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($page->data->properties as $key => $prop): ?>
                                    <?php if (isset($prop['label']) && $prop['label']): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-0 me-auto">
                                                <div class="fs-5 fw-bold"><?= Text::_(strtoupper($key)); ?></div>
                                                <span class="fs-6 text-secondary">
                                                    <?= Text::_($prop['label']); ?><?= $prop['code'] ? ' (' . $prop['code'] . ')' : ''; ?>
                                                </span>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingContracts">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContracts" aria-expanded="false" aria-controls="collapseContracts">
                            <?= Text::_('CONTRACTS'); ?>
                        </button>
                    </h2>
                    <div id="collapseContracts" class="accordion-collapse collapse" aria-labelledby="headingContracts" data-bs-parent="#accordionView">
                        <div class="accordion-body">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($page->data->contracts as $contract): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-0 me-auto">
                                            <div class="fs-5 fw-bold"><?= $contract->description; ?></div>
                                            <span class="fs-6 text-secondary me-2"><?= Text::_('START'); ?>: <?= $contract->startDate ?? '-'; ?></span>
                                            <span class="fs-6 text-secondary me-2"><?= Text::_('END'); ?>: <?= $contract->endDate; ?></span>
                                            <span class="fs-6 text-secondary"><?= Text::_('STATUS'); ?>: <?= Text::_($contract->status); ?></span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingExtra">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExtra" aria-expanded="false" aria-controls="collapseExtra">
                            <?= Text::_('EXTRA'); ?>
                        </button>
                    </h2>
                    <div id="collapseExtra" class="accordion-collapse collapse" aria-labelledby="headingExtra" data-bs-parent="#accordionView">
                        <div class="accordion-body">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($page->data->extra as $key => $prop): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-0 me-auto">
                                            <div class="fs-5 fw-bold"><?= Text::_(strtoupper($key)); ?></div>
                                            <span class="fs-6 text-secondary"><?= Text::_($prop); ?></span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>