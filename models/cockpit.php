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

class modelCockpit extends Model
{
    /**
     * Default display
     */
    public function display($tpl = null)
    {
        $this->data             = $this->_data();
        $this->page->title      = $this->data->brand . ' ' . $this->data->model . ' ' . $this->data->plate;
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
        $item->images       = Helpers::getInfo($vin, 'images');
        $item->properties   = Helpers::getInfo($vin, 'properties');
        $item->plate        = Helpers::getInfo($vin, 'registrationNumber');
        $item->model        = Helpers::getCarModel($vin);
        $item->online       = false;
        $item->alerts       = null;
        $item->lock         = null;

        if (Helpers::isConnected($vin)) {
            $cockpit = Kamereon::Read('Cockpit');
            if (!is_object($cockpit)) {
                if ($cockpit != null) {
                    $item->error = $cockpit;
                } else {
                    $item->error = 'Not found';
                }
            } else {
                $item->online   = true;
                $item->hvac     = 'off';
                $item = (object) array_merge((array) $item, (array) $cockpit);

                if ($item->ev = Helpers::isEV($vin)) {
                    $item->remaining .= '%';
                    $item->hvac     = Kamereon::Read('HvacStatus');
                } else {
                    $item->remaining .= 'L';
                }

                //Is this locked?
                if($lock = Kamereon::Read('LockStatus')){
                    $item->lock = $lock;
                }

                //Get Alerts
                if ($alerts= Kamereon::getProperty($vin, 'alerts')) {
                    $array = json_decode($alerts, true);
                    if (isset($array['success']) && $array['success']) {
                        $item->alerts = [];
                        unset($array['success']);
                        foreach ($array as $key => $c) {
                            $alert= new \stdClass;
                            foreach ($c as $k => $v) {
                                $alert->$k = $v;
                            }
                            $item->alerts[] = $alert;
                        }
                    } 
                }
            }
        }
        return $item;
    }
}

/**
 * 
+---------------------------------------------------------------------	
| cockpit
+---------------------------------------------------------------------		
|    "data": {
|       "type": "Car",
|       "id": "VF1AAAAA555777123",
|       "attributes": {
|            "fuelAutonomy": 35.0,
|            "fuelQuantity": 3.0,
|            "totalMileage": 5566.78
|        }
|    }        
+---------------------------------------------------------------------	
| location
+---------------------------------------------------------------------		      
|   "data": {
|       "type": "Car",
|       "id": "VF1AAAAA555777999",
|       "attributes": {
|           "gpsLatitude": 48.1234567,
|           "gpsLongitude": 11.1234567,
|           "lastUpdateTime": "2020-02-18T16:58:38Z"
|       }
|   }          
+---------------------------------------------------------------------
| lock-status
+---------------------------------------------------------------------		
|   "data": {
|       "type": "Car",
|       "id": "VF1AAAAA555777999",
|       "attributes": {
|           "lockStatus": "locked",
|           "doorStatusRearLeft": "closed",
|           "doorStatusRearRight": "closed",
|           "doorStatusDriver": "closed",
|           "doorStatusPassenger": "closed",
|           "hatchStatus": "closed",
|           "lastUpdateTime": "2022-02-02T13:51:13Z"
|       }
|   } 
+---------------------------------------------------------------------
| hvac-start
+---------------------------------------------------------------------		
|   "data": {
|       "type": "Car",
|       "id": "VF1AAAAA555777999",
|       "attributes": {
|           "action": "start|stop|cancel", 
|           "targetTemperature": 16-26,
|           "startDateTime": "date isoformat,
|       }
|   } 
+---------------------------------------------------------------------
| alerts
+---------------------------------------------------------------------	
    "data": [
        {
            "id": xxxxxxxxxx,
            "alertType": "proactive",
            "canArchitecture": "204",
            "alertCode": "crashairbagmalfunction",
            "trueValues": "1",
            "priority": 0,
            "minAge": 1,
            "minMileage": 100,
            "generateTime": 1440,
            "generateMileage": 15,
            "closeTime": 1440,
            "closeMileage": 36,
            "criticity": 3,
            "generateOccurrence": 3,
            "closeOccurrence": 10,
            "generateLead": 1,
            "notifSMS": 0,
            "notifMail": 1,
            "notifMybrand": 1,
            "notifSourceEvent": "CRIT3.RDVDEALER",
            "leadContactDays": 90,
            "notifContactDays": 5,
            "notifReminderDay": 15,
            "notifReminderCount": 0,
            "notifReminderCounter": 0,
            "notifReminderDate": "2024-03-15T00:00:00.000Z",
            "vin": "xxxxxxxxxxx",
            "registrationNumber": "xxxxxxxxxxxx",
            "countryCode": "IT",
            "deliveryDate": "2020-10-30T00:00:00.000Z",
            "brandComCode": "REN",
            "modelCode": "HJB",
            "partyId": "xxxxxxxxxxxxxxx",
            "partyCountry": "IT",
            "partyOrg": 0,
            "partyStaff": 0,
            "partyPhone": 1,
            "partySms": null,
            "partyMail": null,
            "partyPostal": null,
            "localeDefault": "it-IT",
            "dealerNumber": "xxxxxxxxxxx",
            "dealerNumberOrigin": "dealer_myr",
            "isConnected": 1,
            "mileageDate": "2024-02-29T00:00:00.000Z",
            "mileage": 63593,
            "yearMileage": 16064,
            "operationCode": null,
            "operationName": null,
            "operationType": null,
            "nextDate": null,
            "nextMileage": null,
            "reconductCount": 0,
            "isConnectedOperation": 0,
            "genReason": null,
            "endMileage": 70068,
            "status": 0,
            "creationDateTime": "2024-02-29T14:21:02.382Z",
            "lastUpdateDateTime": "2024-10-29T02:08:45.671Z",
            "inVehicleDateTime": "2024-02-29T14:21:15.000Z",
            "closureReason": "archived",
            "function": "the airbag",
            "shortDescription": "WARNING: Check Airbag",
            "longDescription": "WARNING: Check Airbag",
            "criticityName": "Major alert",
            "alertCodeTitle": "Defective airbag",
            "alertCodeSubTitle": "Details of the event",
            "alertCodeDescription": "The airbag must be inspected",
            "alertCodeDetailDescription": "We have detected a malfunction in your airbag system.\nYou must inspect it to ensure that the front passengers are protected if there is an impact. For further information, refer to the user manual of your vehicle below.",
            "iconUrl": "https:\/\/cap-static-medias.ope.apps.renault.com\/images\/crashAirbag.png",
            "detailImageUrl": "https:\/\/cap-static-medias.ope.apps.renault.com\/images\/crashAirbag.png",
            "userGuideUrl": "https:\/\/it.e-guide.renault.com\/ita\/",
            "mileageUnit": "km"
        }
 */
