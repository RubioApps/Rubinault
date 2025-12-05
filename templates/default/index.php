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
<!DOCTYPE html>
<html lang="<?= $language->getTag(); ?>" dir="<?= ($language->isRtl() ? 'rtl' : 'ltr'); ?>" data-bs-theme="<?= $page->params['mode']; ?>">

<head>
    <meta charset="utf-8">
    <title><?= $page->title . ' - ' . $config->sitename; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex,nofollow" />
    <meta name="keywords" content="brave, search" />
    <meta name="description" content="brave, search" />
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <link rel="manifest" href="<?= $config->live_site; ?>/manifest.json">
    <!-- Icons @see: https://github.com/audreyr/favicon-cheat-sheet -->
    <link rel="shortcut icon" href="<?= $factory->getAssets(); ?>/favicons/rubinault.png" type="image/png">
    <link rel="icon" href="<?= $factory->getAssets(); ?>/favicons/rubinault.png">
    <!-- Basic Jquery -->
    <?= $page->addCDN('js', 'https://code.jquery.com/jquery-3.7.1.min.js', 'sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=', 'anonymous'); ?>
    <?= $page->addCDN('js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.min.js', 'sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=', 'anonymous'); ?>
    <?= $page->addCDN('css', 'https://code.jquery.com/ui/1.13.3/themes/dark-hive/jquery-ui.css'); ?>
    <?= $page->addCDN('js', $factory->getAssets() . '/jquery.ui.touch-punch.min'); ?>
    <?= $page->addCDN('js', $factory->getAssets() . '/jquery-dateformat.min.js'); ?>
    <?= $page->addCDN('js', $factory->getAssets() . '/rubinault.js'); ?>
    <!-- Bootstrap v5 -->
    <?= $page->addCDN('css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', 'sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH', 'anonymous'); ?>
    <?= $page->addCDN('js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', 'sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz', 'anonymous'); ?>
    <?= $page->addCDN('css', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css'); ?>
    <!-- Leaflet --> 
     <!--//
    <?= $page->addCDN('js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=', '');?>
    //-->
    <?= $page->addCDN('css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=', '');?>    
    <!-- Additional styles -->
    <?= $page->addCDN('css', $factory->getAssets() . '/default.css'); ?>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            $.rbno.init('<?= $config->live_site; ?>');
            $.rbno.logged = <?= $user->isLogged() ? 'true' : 'false'; ?>;
        });
    </script>
</head>

<body>
    <?php if ($user->isLogged()): ?>
        <header class="rbno-header navbar navbar-expand-xxl navbar-dark sticky-top" data-type="header">
            <?php require_once $page->getFile('header'); ?>
        </header>
        <main class="rbno-main container-xl mt-3 p-3 mb-5 pb-5 mt-xxl-5">
            <?php require_once $page->getFile(); ?>
        </main>
        <footer class="rbno-footer fixed-bottom p-0 d-none">
            <?php require_once $page->getFile('footer'); ?>
        </footer>
    <?php else: ?>
        <div class="container mx-auto">
            <?php require_once $page->getFile(); ?>
        </div>
    <?php endif; ?>
    <?php require_once $page->getFile('modal'); ?>
    <?php require_once $page->getFile('toast'); ?>
    <div id="rbno-overlay" class="d-none">
        <div class="popup-spinner text-center">
            <div class="d-block" style="width: 18rem;">
                <img src="<?= RBNO_BLANK;?>" class="spinner-border" />
            </div>
        </div>
    </div>
    <?php $page->getJScripts(); ?>    
</body>

</html>