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

define('_RBNOEXEC', 1);

define('RBNO_BASE', dirname(__FILE__));
require_once RBNO_BASE . '/includes/defines.php';

// Load Factory
require_once RBNO_INCLUDES . '/factory.php';
$factory    = new Rubinault\Framework\Factory();

// Initialize
$factory->initialize();

// Get configuration and locale
$config     = $factory->getConfig();

// Get the language
$language   = $factory->getLanguage();

// Get the router
$router     = $factory->getRouter();

// Get the page
$page = $factory->getPage();

// Get the user
$user = $factory->getUser();
    
// Get the current task
$task       = $factory->getTask();

// Bridge to the JS Framework
$factory->jsBridge();

// Dispatch
require_once $router->dispatch();

// Finalize
$factory->finalize();