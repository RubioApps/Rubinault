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

use Rubinault\Framework\Stats;
use Rubinault\Framework\Language\Text;

class modelHome extends Model
{
    public function display($tpl = null)
    {       
        $this->data             = $this->_data();
        $this->page->title      = Text::_('HOME');                            
        $this->page->data       = $this->data;
        parent::display($tpl);
    }

    protected function _data()
    {
        //Get vehicules
        $array = [];
        $user = Factory::getUser();    
        if($user->isLogged()){
            $data = $user->get();
            foreach($data->vehicles as $veh)
            {   
                $vin    = $veh['vin'];                             
                $images = Helpers::getInfo($vin, 'images');

                $item = new \stdClass();
                $item->vin          = $vin;
                $item->brand        = Helpers::getInfo($vin, 'brand');
                $item->image        = $images[0];
                $item->properties   = Helpers::getInfo($vin, 'properties');
                $item->plate        = Helpers::getInfo($vin, 'registrationNumber');
                $item->model        = Helpers::getCarModel($vin);
                $item->label        = $item->brand . ' ' . $item->model;
                $item->alias        = Helpers::encode($item->vin);
                $item->link         = Factory::Link('view', $item->vin );                
                $array[] = $item;                          
            }
        }         
        return $array;        
    }    

}