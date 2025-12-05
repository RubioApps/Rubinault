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
use Rubinault\Framework\User;
use Rubinault\Framework\Language\Text;
use stdClass;

class modelEv extends Model
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
		$this->page->title      = $this->data->brand . ' ' . $this->data->model . ' ' . $this->data->plate;
		$this->page->data       = $this->data;
		parent::display($tpl);
	}

	/**
	 * Collect data for the current page
	 */
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
		$item->model        = Helpers::getCarModel($vin);
		$item->online 		= false;

		if (Helpers::isEV($vin)) {			
			$status = Kamereon::Read('BatteryStatus');
			if (!is_object($status)) {
				if ($status !== null) {
					$item->error = $status;
				} else {
					$item->error = 'Not found';
				}
			} else {
				$item->online	= true;				
				$item->status 	= $status;
				$item->hvac 	= Kamereon::Read('HvacStatus');
				//Kamereon::Read('ChargeMode');
				//Kamereon::Read('ChargeStart');
			}
		}
		return $item;
	}

}


/**
+---------------------------------------------------------------------	
| battery-status
+---------------------------------------------------------------------		
|	"data": {
|		"type": "Car",
|		"id": "VF1AAAAA555777999",
|		"attributes": {
|			"timestamp": "2020-01-12T21:40:16Z",
|			"batteryLevel": 60,
|			"batteryTemperature": 20,
|			"batteryAutonomy": 141,
|			"batteryCapacity": 0,
|			"batteryAvailableEnergy": 31,
|			"plugStatus": 1,
|			"chargingStatus": 1.0,
|			"chargingRemainingTime": 145,
|			"chargingInstantaneousPower": 27.0
|		}
|	}
+---------------------------------------------------------------------	
| charges
+---------------------------------------------------------------------	
|	"data": 
|	{
|		"type": "Car",
|		"id": "VF1AAAAA555777999",
|       "attributes": {
|           "charges": [
|               {
|                   "chargeStartDate": "2025-02-24T10:17:42Z",
|                   "chargeEndDate": "2025-02-24T10:18:07Z",
|                   "chargeEndStatus": "ok",
|                   "chargeStartBatteryLevel": 100,
|                   "chargeEndBatteryLevel": 100,
|                   "chargeEnergyRecovered": 0.099998474,
|                   "chargeDuration": 0
|               },...
|		}
|	}
+---------------------------------------------------------------------	
| charge-history
+---------------------------------------------------------------------	
|	"data": 
|	{
|		"type": "Car",
|		"id": "VF1AAAAA555777999",
|		"attributes": 
|		{
|			"chargeSummaries": [
|				{
|				"day": "20201208",
|				"totalChargesNumber": 2,
|				"totalChargesDuration": 495,
|				"totalChargesErrors": 0
|				},
|				{
|				"day": "20201205",
|				"totalChargesNumber": 1,
|				"totalChargesDuration": 657,
|				"totalChargesErrors": 0
|				}
|			]
|		}
|	}
+---------------------------------------------------------------------	
| charge-mode
+---------------------------------------------------------------------	
|	"data":
|	{
|		"type": "Car",
|		"id": "VF1AAAAA555777999",
|		"attributes": 
|		{ 
|			"chargeMode": "always" or "scheduled"
|		}
|	}  
+---------------------------------------------------------------------	
| actions/charging-start
+---------------------------------------------------------------------	
|	"data":
|	{
|		"action": start|stop,
|	}  
|	"data":
|	{
|		"type": "Car",
|		"id": "VF1AAAAA555777999",
|		"attributes": 
|		{ 
|			"chargeMode": "always" or "scheduled"
|		}
|	}  
+---------------------------------------------------------------------	
| charge-settings
+---------------------------------------------------------------------	
|	"data": 
|	{
|		"type": "Car",
|		"id": "VF1AAAAA555777999",
|		"attributes": { 
|			"mode": "scheduled",
|			"schedules": [
|				{
|				"id": 1,
|				"activated": true,
|				"monday": {
|					"startTime": "T12:00Z",
|					"duration": 15
|					},
|				...
|				"sunday": {
|					"startTime": "T12:45Z",
|					"duration": 45
|					}				
|				}
|			]
|		}
|	}
 */

/**  
  			{"name" : "chargeStatus"},
			{"name" : "rangeHvacOn"},
			{"name" : "batteryLevel"},
			{"name" : "batteryTemperature"},
			{"name" : "batteryAutonomy"},
			{"name" : "batteryCapacity"},
			{"name" : "batteryAvailableEnergy"},
			{
				"name" : "plugStatus",
				"values" : [
					{"number" : 0, "text" : "UNPLUGGED"},
					{"number" : 1, "text" : "PLUGGED"},
					{"number" : -1, "text" : "PLUG_ERROR "},
					{"number" : -2147483648, "text" : "PLUG_UNKNOWN "},
				]
			},
			{
				"name" : "chargingStatus",
				"values" : [
                    // https://github.com/hacf-fr/renault-api/blob/main/src/renault_api/kamereon/enums.py
				    {"number" : 0.0, "text" : "NOT_IN_CHARGE"},
				    {"number" : 0.1, "text" : "WAITING_FOR_A_PLANNED_CHARGE"},
				    {"number" : 0.2, "text" : "CHARGE_ENDED"},
				    {"number" : 0.3, "text" : "WAITING_FOR_CURRENT_CHARGE"},
				    {"number" : 0.4, "text" : "ENERGY_FLAP_OPENED"},
				    {"number" : 1.0, "text" : "CHARGE_IN_PROGRESS"},
				    {"number" : -1.0, "text" : "CHARGE_ERROR"},
				    {"number" : -1.1, "text" : "UNAVAILABLE"},
				]
			},
			{"name" : "chargingRemainingTime"},
			{"name" : "chargingInstantaneousPower"},
 */


/**
 	{
		 "name" : "Show charging settings",
		"url" : "charging-settings",
		"payload" : {},
		"description" : "Show charge schedule by id",
		"response" : [{"name" : "mode"},{"name" : "schedules"},]
	},


	{
		"name" : "Show charge schedule",
		"url" : "charge-schedule"	,
		"payload" : {},
		"description" : "Show charge schedule by day",
		"response" : [{"name" : "calendar"}]
	},


	{
		"name" : "Show charge history",
		"url" : "charge-history?start=20220101&end=20220322&type=day",
		"payload" : {},
		"description" : "Show number of charges and count of kWh",
		"response" : [{"name" : ""}]
	},


	{
		"name" : "Show charges",
		"url" : "charges?start=20220101&end=20220328&type=day",
		"payload" : {},
		"description" : ""
	},

	{
		"name" : "----- Actions ------",
		"url" : "",
		"payload" : {},
		"description" : ""
	},

	{
		"name" : "Refresh battery status",
		"url" : "actions/refresh-battery-status",
		"payload" : {'data':{'type':'RefreshBatteryStatus'}},
		"description" : "Refresh battery data retrieved by  battery-status endpoint"
	},


	{ "name" : "Start charging",						"url" : "actions/charging-start",								"payload" : {'data':{'type':'ChargingStart','attributes':{'action':'start'}}},					"description" : "Start charging immediately"},
	{ "name" : "Stop charging",							"url" : "actions/charging-start",								"payload" : {'data':{'type':'ChargingStart','attributes':{'action':'stop'}}},					"description" : "Stop charging immediately"},
	{ "name" : "Set charge mode to ALWAYS",				"url" : "actions/charge-mode"	,								"payload" : {'data':{'type':'ChargeMode','attributes':{'action':'always_charging'}}},			"description" : "Set charge mode"},
	{ "name" : "Set charge mode to SCHEDULED",			"url" : "actions/charge-mode"	,								"payload" : {'data':{'type':'ChargeMode','attributes':{'action':'schedule_mode'}}},				"description" : "Set charge mode"},



 *  */
