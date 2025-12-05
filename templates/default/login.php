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

use \Rubinault\Framework\Language\Text;

?>
<header class="navbar navbar-expand-xxl navbar-dark rbno-navbar sticky-top" data-type="header">
    <nav class="container-xxl flex-wrap flex-xxl-nowrap justify-content-center">
        <a class="navbar-brand rbno-brand framed text-center text-truncate" href="<?= $config->live_site; ?>">
            <div class="text-center">
                <img class="p-0" src="<?= $factory->getAssets() . '/favicons/rubinault.png'; ?>" width="30" />
                <span class="h4 fw-bold" style="position:relative;top:3px"><?= $config->sitename; ?></span>
            </div>
        </a>
    </nav>
</header>
<!-- Login -->
<main role="main" class="container container-md mx-auto my-auto">
    <form>
        <?= $factory->getToken(); ?>
        <div class="row justify-content-center p-3 flex-nowrap mt-5">
            <div class="col-auto border rounded">
                <div class="input-group mt-3 mb-3">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input class="form-control" id="usr" name="user" placeholder="<?= Text::_('USER'); ?>" value="">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input class="form-control" type="password" id="pwd" name="password" placeholder="<?= Text::_('PASSWORD'); ?>" value="">
                    <span class="input-group-text">
                        <i class="bi bi-eye" id="eye" style="cursor: pointer"></i>
                    </span>
                </div>
                <div class="row p-1">
                    <div class="col text-center mb-2">
                        <button id="btn-submit" type="button" class="btn btn-primary">
                            <?= Text::_('SUBMIT'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>    
</main>
<!-- JS -->
<script type="text/javascript">
    jQuery(document).ready(function () { 

        const togglePassword = $('#eye');
        const password = $('#pwd');
        togglePassword.on('click', function () {   
            const type = password.attr('type') === 'password' ? 'text' : 'password';
            password.attr('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        $.rbno.logged = <?= $user->isLogged()? 'true':'false';?>;
        $.rbno.login('#btn-submit'); 
    }); 
</script>