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

class modelRemote extends Model
{
	/**
	 * Read a remote attribute. Check the JQuery library rubinault.js to know how this function is called
	 */

    public function get()
    {		
		$user		= Factory::getUser();
		$vin 		= Request::getVar('vin',null,'GET');				
		$endpoint	= Request::getVar('endpoint',null,'GET');				

		if($user->isLogged() && $endpoint && $vin){			
			$result = Kamereon::Read($endpoint);			
		} else {
			$result = json_encode([
				'success' => false , 
				'errors' => ['errorCode' => ERR_INVALID_TOKEN , 'errorMessage' => 'Could not get ' . $endpoint]
			]);
		}
		header('Content-Type: application/json; charset=utf-8'); 
        echo $result;		
		exit(0);
    }

	/**
	 * Write a remote attribute. Check the JQuery library rubinault.js to know how this function is called
	 */
    public function post()
    {		
		$user		= Factory::getUser();
		$vin 		= Request::getVar('vin',null,'GET');
		$endpoint	= Request::getVar('endpoint',null,'GET');
		$post 		= (array) Request::get('post');
				
		if($user->isLogged() && $endpoint && $vin){			
			if($result = Kamereon::Write($endpoint,$post)){	
				$array = json_decode( (string) $result,true);

				//Once the command is triggered, the sttus might change. Then, ensure the cache is deleted
				if($array['success']){					
					$filename = $user->userfolder . DIRECTORY_SEPARATOR . $vin . DIRECTORY_SEPARATOR . 'attributes.json';
					if(file_exists($filename)) unlink($filename);
				}				
			}
		} else {
			$result = json_encode([
				'success' => false , 
				'errors' => ['errorCode' => ERR_INVALID_TOKEN , 'errorMessage' => 'Could not set ' . $endpoint]
			]);
		}		    
		header('Content-Type: application/json; charset=utf-8'); 
        echo $result;		
		exit(0);
    }	

}
/**
 * 
_KCM_POST_ENDPOINTS: dict[str, Any] = {
    "charge/pause-resume": {"version": 1, "type": "ChargePauseResume"},
}

	{
		"name" : "Refresh battery status",
		"url" : "actions/refresh-battery-status",
		"payload" : {'data':{'type':'RefreshBatteryStatus'}},
		"description" : "Refresh battery data retrieved by  battery-status endpoint"
	},

	{
		"name" : "Refresh HVAC status",
		"url" : "actions/refresh-hvac-status"	,
		"payload" : {'data':{'type':'RefreshHvacStatus'}},
		"description" : "Refresh HVAC  data retrieved nby  hvac-status endpoint"
	},

	{
		"name" : "Refresh location",
		"url" : "actions/refresh-location",
		"payload" : {'data':{'type':'RefreshLocation'}},
		"description" : "Refresh GPS position"
	},


	{ "name" : "Start charging",						"url" : "actions/charging-start",								"payload" : {'data':{'type':'ChargingStart','attributes':{'action':'start'}}},					"description" : "Start charging immediately"},
	{ "name" : "Stop charging",							"url" : "actions/charging-start",								"payload" : {'data':{'type':'ChargingStart','attributes':{'action':'stop'}}},					"description" : "Stop charging immediately"},
	{ "name" : "Set charge mode to ALWAYS",				"url" : "actions/charge-mode"	,								"payload" : {'data':{'type':'ChargeMode','attributes':{'action':'always_charging'}}},			"description" : "Set charge mode"},
	{ "name" : "Set charge mode to SCHEDULED",			"url" : "actions/charge-mode"	,								"payload" : {'data':{'type':'ChargeMode','attributes':{'action':'schedule_mode'}}},				"description" : "Set charge mode"},

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


	{
		"name" : "actions/send-navigation",
		"url" : "actions/send-navigation"	,
		"payload" : {
		    'data': {
		        'type': 'SendNavigation',
		        'attributes': {
		            'downloadTrafficInfo': false,
		            'destinations': [
		                {
		                    'id': 1,
		                    'latitude': 42,
		                    'longitude': 12,
		                    'calculationCondition': 0
		                }
		            ]
		            }
		        }
			},
		"description" : "Send coordinates of 1 to 5 destinations"
	},

	{ "name" : "actions/horn-lights",					"url" : "actions/horn-lights",								"payload" : {},			"description" : "-"},
	{
		"name" : "Engine start",
		"url" : "actions/engine-start",
		"payload" : {'data':{'type':'EngineStart','attributes':{'action':'start'}}},
		"description" : "(Requires SRP authentication)"
	},

	{
		"name" : "Engine stop",
		"url" : "actions/engine-start",
		"payload" : {'data':{'type':'EngineStart','attributes':{'action':'stop'}}},
		"description" : "(Requires SRP authentication)"
	},


	{
		"name" : "res-state",
		"url" : "res-state"	,
		"payload" : {},
		"description" : "Show status of Internal Combustion Engine"
	},
	{
		"name" : "Refresh lock status",
		"url" : "actions/refresh-lock-status",
		"payload" : {'data':{'type':'RefreshLockStatus'}},
		"description" : "Refresh status of door locking"
	},
 *  */    

