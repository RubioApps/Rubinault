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

class modelView extends Model
{
    public function display($tpl = null)
    {
        $vin    = Request::getVar('vin', null, 'GET');
        if (!$vin) {
            $this->page->title = Text::_('PAGE_NOT_FOUND');
            return parent::display('404.php');
        }

        $this->data             = $this->_data();
        $this->page->title      = $this->data->brand . ' ' . $this->data->model  . ' ' . $this->data->plate;
        $this->page->data       = $this->data;
        parent::display($tpl);
    }

    public function availability()
    {
        $vin = Request::getVar('vin', null, 'GET');
        $result = ['tcu' => false, 'ev' => false];

        if ($vin && Helpers::isConnected($vin)) $result['tcu'] = true;
        if ($vin && Helpers::isEV($vin)) $result['ev'] = true;

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit(0);
    }

    public function model()
    {
        $config = Factory::getConfig();
        $vin    = Request::getVar('vin', null, 'GET');
        $model  = Helpers::getCarModel($vin, 'code');

        $filename   = RBNO_MAPPING . DIRECTORY_SEPARATOR . $model . '.json';
        if (file_exists($filename)) {
            $result     = [
                'success' => true,
                'mapping' => $config->live_site . '/mapping/' . $model . '.json'
            ];
        } else {
            $result     = [
                'success' => false,
                'mapping' => null
            ];
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit(0);
    }

    protected function _data()
    {
        //Get the vehicule
        $vin    = trim(strtoupper(Request::getVar('vin', null, 'GET')));

        //Build the data
        $item = new \stdClass();
        $item->vin          = $vin;
        $item->brand        = Helpers::getInfo($vin, 'brand');
        $item->images       = Helpers::getInfo($vin, 'images');
        $item->properties   = Helpers::getInfo($vin, 'properties');
        $item->extra        = Helpers::getInfo($vin, 'others');
        $item->plate        = Helpers::getInfo($vin, 'registrationNumber');
        $item->model        = Helpers::getCarModel($vin);

        //Get contracts
        $options = [
            'brand' => $item->brand,
            'warranty' => true,
            'connectedServicesContracts' => true,
            'warrantyMaintenanceContracts' => true
        ];

        if ($contracts = Kamereon::getProperty($vin, 'contracts', $options)) {
            $array = json_decode($contracts, true);
            if (isset($array['success']) && $array['success']) {
                $item->contracts = [];
                unset($array['success']);
                foreach ($array as $key => $c) {
                    $contract = new \stdClass;
                    foreach ($c as $k => $v) {
                        $contract->$k = $v;
                    }
                    $item->contracts[] = $contract;
                }
            }
        }

        //Preferred dealer
        $item->dealer = null; //Kamereon::getDealer($vin);

        return $item;
    }
}
