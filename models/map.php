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

use Rubinault\Framework\Helpers;
use Rubinault\Framework\Language\Text;

class modelMap extends Model
{
    /**
     * Default display
     */
    public function display($tpl = null)
    {
        $vin    = Request::getVar('vin', null, 'GET');
        if (!$vin) {
            $this->page->title = Text::_('PAGE_NOT_FOUND');
            return parent::display('404.php');
        }

        $this->data             = $this->_data();
        $this->page->title      = $this->data->brand . ' ' . $this->data->properties['model']->label . ' ' . $this->data->plate;
        $this->page->data       = $this->data;
        parent::display($tpl);
    }

    /**
     * Collect the data for the current page
     */
    protected function _data()
    {
        //Get the vehicule
        $vin    = trim(strtoupper(Request::getVar('vin', null, 'GET')));

        //Build the data
        $item = new \stdClass();
        $item->vin          = $vin;
        $item->brand        = Helpers::getInfo($vin, 'brand');
        $item->properties   = Helpers::getInfo($vin, 'properties');
        $item->plate        = Helpers::getInfo($vin, 'registrationNumber');        
        $item->online       = false;

        if (Helpers::isConnected($vin)) {
            $location     = Kamereon::Read('Location');
            if (!is_object($location)) {
                if ($location !== null) {
                    $item->error = $location;
                } else {
                    $item->error = 'Not found';
                }
            } else {
                $item->online = true;
                $item = (object) array_merge( (array) $item, (array) $location);
            }
        }
        return $item;
    }

    /** 
     * Get the current CPS coordinates    
     */
    public function gps()
    {
        $success    = false;
        $timestamp  = null;
        $latitude   = 0;
        $longitude  = 0;
        $remote     = null;

        $location = Kamereon::Read('Location');
        if (is_object($location)) {
            $latitude   = $location->latitude;
            $longitude  = $location->longitude;
            $timestamp  = $location->timestamp;
            $success    = true;
        }        
        $result = [
            'success'   => $success, 
            'timestamp' => $timestamp, 
            'latitude'  => $latitude, 
            'longitude' => $longitude, 
            'response'  => $remote
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit(0);
    }    
}

/**
+---------------------------------------------------------------------	
| actions/refresh-location
+---------------------------------------------------------------------		
|   'data':{
|       'type':'RefreshLocation'
|   }            
+---------------------------------------------------------------------	
| actions/send-navigation
+---------------------------------------------------------------------	
|	'data': {
|		'type': 'SendNavigation',
|		'attributes': {
|		    'downloadTrafficInfo': false,
|		    'destinations': [
|		        {
|		        'id': 1,
|		        'latitude': 42,
|		        'longitude': 12,
|		        'calculationCondition': 0		                
|               }
|		    ]			
|		}
|	} 
+---------------------------------------------------------------------	
 */
