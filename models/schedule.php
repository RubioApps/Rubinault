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
use stdClass;

class modelSchedule extends Model
{
	/**
	 * Default display method
	 */
	public function display($tpl = null)
	{
		$vin    = Request::getVar('vin', null, 'GET');
		if (!$vin) {
			$this->page->title = Text::_('PAGE_NOT_FOUND');
			return parent::display('404.php');
		}

		$this->data             = $this->_data();
		$this->page->title      = $this->data->brand . ' ' . $this->data->model . ' ' . $this->data->plate;
		$this->page->data       = $this->data;
		parent::display($tpl);
	}

	/**
	 * Collects the information for the HVAC page
	 */
	protected function _data()
	{
		$config = Factory::getConfig();

		//Get the vehicule
		$vin    = trim(strtoupper(Request::getVar('vin', null, 'GET')));

		//Build the data
		$item = new \stdClass();
		$item->vin          = $vin;
		$item->brand        = Helpers::getInfo($vin, 'brand');
		$item->images       = Helpers::getInfo($vin, 'images');
		$item->properties   = Helpers::getInfo($vin, 'properties');
		$item->model        = Helpers::getCarModel($vin);
		$item->online 		= false;

		if (Helpers::isEV($vin)) {
			$hvac           = Kamereon::Read('HvacStatus');
			if (!is_object($hvac)) {
				if ($item->status !== null) {
					$item->error = $hvac;
				} else {
					$item->error = 'Not found';
				}
			} else {
				$item->online = true;
				$item->hvac = $hvac;
				$item->programs	= (array) Kamereon::Read('EVPrograms');				
			}
		}
		return $item;
	}

	/**
	 * Get HVAC Schedule
	 */
	protected function _HvacSettings()
	{
		$remote = Kamereon::Read('HvacSettings');						
		$array = json_decode((string) $remote, true);

		$result = [];
		$weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

		//Fulfill 5 available schedules schedule
		for ($i = 1; $i <= 5; $i++) {
			$plan = new \stdClass;
			$plan->active = false;
			$plan->ready = [];
			foreach ($weekdays as $key) {
				$plan->ready[$key] =  '';
			}
			$result[] = $plan;
		}

		if ($array['data']['attributes']['mode'] == 'scheduled') {

			$schedules = $array['data']['attributes']['schedules'];
			foreach ($schedules as $schedule) {
				$index = $schedule['id'];
				$plan = new \stdClass;
				$plan->active = (bool) $schedule['activated'];
				$plan->ready = [];
				foreach ($weekdays as $key) {
					$plan->ready[$key] = isset($schedule[$key]) ? date('H:i', strtotime($schedule[$key]['readyAtTime'])) : null;
				}
				$result[$index] = $plan;
			}
		}
		return $result;
	}
}

/**
|---------------------------------------------------------------------	
| hvac-status: Depending on the model, the return might be different
|---------------------------------------------------------------------	
|	"data": {
|		"type": "Car",
|		"id": "UU1AAAAA555777123",
|		"attributes": {
|			"socThreshold": 30.0,
|			"hvacStatus": "off",
|			"lastUpdateTime": "2020-12-03T00:00:00Z"
|		}
|	}	
|
|	"data": {
|		"type": "Car",
|		"id": "VF1AAAAA555777999",
|		"attributes": { 
|			"externalTemperature": 8.0, 
|			"hvacStatus": "off" 
|		}
|	}
|
|	{
|	"data": {
|		"type": "Car",
|		"id": "VF1AAAAA555777999",
|		"attributes": { 
|			"socThreshold": 40, 
|			"hvacStatus": "on" 
|		}
|	}
|
/*


	{
		"name" : "hvac-status",
		"url" : "hvac-status",
		"payload" : {},
		"description" : "Show if On/off,target temperature,no schedule",
		"response" : [{"name" : "hvacStatus"},{"name" : "internalTemperature"},]
	},


	{
		"name" : "hvac-settings",
		"url" : "hvac-settings",
		"payload" : {},
		"description" : "Show HVAC schedule grouped by id",
		"response" : [{"name" : "mode"},{"name" : "globalTargetTemperature"}, {"name" : "schedules"},]
	},


	{
		"name" : "hvac-schedule",
		"url" : "hvac-schedule",
		"payload" : {},
		"description" : "Show HVAC schedule groupd by day",
		"response" : [{"name" : "calendar"}]
	},


	{
		"name" : "hvac-history",
		"url" : "hvac-history?start=20210201&end=20210301&type=day",
		"payload":  {},
		"description" : "-",
		"response" : [{"name" : ""},{"name" : ""},]
	},


	{
		"name" : "hvac-sessions",
		"url" : "hvac-sessions?start=20210201&end=20210301",
		"payload" : {},
		"description" : "-",
		"response" : [{"name" : ""},{"name" : ""},]
	},

	{
		"name" : "Refresh HVAC status",
		"url" : "actions/refresh-hvac-status"	,
		"payload" : {'data':{'type':'RefreshHvacStatus'}},
		"description" : "Refresh HVAC  data retrieved nby  hvac-status endpoint"
	},   
    
	{
		"name" : "Start HVAC immediately",
		"url" : "actions/hvac-start"	,
		"payload" : {'data':{'type':'HvacStart','attributes':{'action':'start','targetTemperature':'21'}}},
		"description" : ""
	},

	{
		"name" : "Stop HVAC immediately",
		"url" : "actions/hvac-start"	,
		"payload" : {'data':{'type':'HvacStart','attributes':{'action':'stop'}}},
		"description" : "(not working)"
	},
    

 *  */
