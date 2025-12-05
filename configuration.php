<?php

/**
 +-------------------------------------------------------------------------+
 | Rubinault  - Webapp Renault API                                            |
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

namespace Rubinault\Framework;

defined('_RBNOEXEC') or die;

class RbnoConfig
{
        public $sitename        = 'Rubinault';
        public $live_site       = 'https://famillerubio.com/rubinault';
        public $log_path        = '/var/www/rubinault/log';
        public $tmp_path        = '/var/www/rubinault/tmp';
        public $debug           = false;
        public $test            = false;
        public $use_cache       = true;
        public $anti_throttle   = 300;
        public $key             = 'x28DL"?(xu`"N%st4E[JosX\d$iHu:|J%.S_L7boav0bX:yFS`sEH0gw-d=/&%Oq';
        public $list_limit      = 36;
        public $slider_limit    = 20;
        public $theme           = 'default';   
        public $gigya           = [ 
                'country'       => 'FR',
                'url'           => 'https://accounts.eu1.gigya.com',
                'apikey'        => '3_e8d4g4SE_Fo8ahyHwwP7ohLGZ79HKNN2T8NjQqoNnk6Epj6ilyYwKdHUyCw3wuxz'
        ];
        public $wired           = [
                'country'       => 'FR',
                'locale'        => 'fr_FR',
                'url'           => 'https://api-wired-prod-1-euw1.wrd-aws.com',
                'apikey'        => 'YjkKtHmGfaceeuExUDKGxrLZGGvtVS0J'
        ];            
}
