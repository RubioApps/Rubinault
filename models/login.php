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

namespace Rubinault\Framework;

defined('_RBNOEXEC') or die;

use Rubinault\Framework\Factory;
use Rubinault\Framework\Language\Text;

class modelLogin extends Model
{
    public function display($tpl = null)
    {
        $user = new User();

        $this->page->title      = Text::_('LOGIN');

        if(is_array($_POST) && isset($_POST['username']) && isset($_POST['password']))
        {            
            $username   = $_POST['username'];
            $password   = $_POST['password'];    
            
            if( Factory::checkToken() && $user->Login($username , $password) )
            {                    
                //Log the user                
                if($user->Logon())
                {
                    //Serve the JSON
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['error' => false , 'message' => Text::_('LOGIN_SUCCESS')]);
                    exit(0);
                }
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => true , 'message' => Text::_('LOGIN_ERROR')]);
            exit(0);
        } 

        if($user->isLogged())
        {          
            $config = Factory::getConfig();
            header('Location:' . $config->live_site);            
            exit(0);             
        }      
    }

    public function out()
    {
        $this->page->setFile('logout.php');                  
    }

    public function off()
    {
        $user = new User();
        $user->Logoff();
    }

}