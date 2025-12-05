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

// Global definitions
$parts = explode(DIRECTORY_SEPARATOR, RBNO_BASE);

// Paths
define('RBNO_ROOT', implode(DIRECTORY_SEPARATOR, $parts));
define('RBNO_SITE', RBNO_ROOT);
define('RBNO_CONFIGURATION', RBNO_ROOT);
define('RBNO_INCLUDES', RBNO_ROOT . DIRECTORY_SEPARATOR . 'includes');
define('RBNO_MODELS', RBNO_ROOT . DIRECTORY_SEPARATOR . 'models');
define('RBNO_STATIC', RBNO_ROOT . DIRECTORY_SEPARATOR . 'includes');
define('RBNO_THEMES', RBNO_BASE . DIRECTORY_SEPARATOR . 'templates');
define('RBNO_VENDOR', RBNO_BASE . DIRECTORY_SEPARATOR . 'vendor');
define('RBNO_USERS', RBNO_BASE . DIRECTORY_SEPARATOR . 'users');
define('RBNO_MAPPING', RBNO_BASE . DIRECTORY_SEPARATOR . 'mapping');

// Errors
define('ERR_NONE', 0);
define('ERR_INVALID_TOKEN', 500);

// Blank image
define('RBNO_BLANK','data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=');

// Security
define('IV_KEY', '8w)kz^r71Z^V]*X');



