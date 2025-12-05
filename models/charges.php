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

use DateInterval;
use Rubinault\Framework\Helpers;
use Rubinault\Framework\Language\Text;
use stdClass;

class modelCharges extends Model
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
		$item->online = false;

		if (Helpers::isEV($vin)) {
			$status 		= Kamereon::Read('BatteryStatus');
			if (!is_object($status)) {

				if ($status !== null) {
					$item->error = $status;
				} else {
					$item->error = 'Not found';
				}
			} else {
				$item->online	= true;
				$item->status 	= $status;
				$item->history 	= $this->_batteryCharges();
				//$item->mode 	= Kamereon::Read('ChargeMode');				
			}
		}
		return $item;
	}

	/**
	 * Charges 
	 */
	protected function _batteryCharges()
	{
		$config = Factory::getConfig();
		$period = Request::getVar('period', null, 'GET');

		$start	= strtotime('first day of -5 month');
		$end	= strtotime('now');

		if (!$period) $period = date('Ym', strtotime('now'));

		$month = date('m', strtotime($period . '01'));
		$year = date('Y', strtotime($period . '01'));
		$lastday = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		$to = strtotime($year . $month . $lastday);
		$from	= strtotime(date('Ym01', $to));

		$query	= [
			'type'	=> 'day',
			'start' => date('Ymd', $start),
			'end'	=> date('Ymd', $end),
		];

		$remote = Kamereon::Read('Charges', $query);
		if (!is_object($remote)) return null;

		$result = new stdClass;
		$result->total		= 0;
		$result->charges	= [];
		$result->datasets	= [];
		$result->consumption = [];
		$result->levelStart = [];
		$result->levelEnd	= [];

		$format = new \IntlDateFormatter($config->wired['locale'], \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, NULL, NULL, "MMMM YYYY");
		$result->period = ucfirst($format->format($from));

		// This month
		if (date('Ym', $to) == date('Ym', $end)) {
			$result->next = null;
		} else {
			$result->next = date('Ym', $to + 86400);
		}
		if (date('Ym', $from) == date('Ym', $start)) {
			$result->prev = null;
		} else {
			$result->prev = date('Ym', $from - 1);
		}

		$total = 0;
		foreach ($remote as $charge) {
			$item = new stdClass;
			$day = '';
			$level = 0;
			$energy = 0;
			foreach ($charge as $key => $attr) {
				switch ($key) {
					case 'recovered':
						$energy += floatval($attr);
						break;
					case 'start_level':
						$levelStart = $attr;
						break;
					case 'end_level':
						$levelEnd = $attr;
						break;
					case 'end_date':
						$datetime = strtotime($attr);
						break;
				}
				$item->$key = $attr;
			}
			//Only take the items with the defined period of time
			if ($datetime >= $from && $datetime <= $to) {
				$total += $energy;
				$result->charges[$datetime] = $item;
				$day = date('Y-m-d', $datetime);
				if (!isset($result->levels[$day])) {
					$result->levelStart[$day] = (int) $levelStart;
					$result->levelEnd[$day] = (int) $levelEnd;
				} else {
					$result->levelStart[$day] = (int) ($result->levelStart[$day] + $level) / 2;
					$result->levelend[$day] = (int) ($result->levelEnd[$day] + $level) / 2;
				}
			}
		}
		ksort($result->charges);
		ksort($result->levelStart);
		ksort($result->levelEnd);

		$result->datasets[] = ['label' => Text::_('CHARGE_START'), 'data' => $result->levelStart, 'backgroundColor' => '#842029'];
		$result->datasets[] = ['label' => Text::_('CHARGE_END'), 'data' => $result->levelEnd, 'backgroundColor' => '#198754'];
		$result->count = count($result->charges);
		$result->total = $total;
		return $result;
	}

	/**
	 * Charge Settings: schedule the charge timeslot
	 */
	protected function _chargingSettings()
	{
		/*
		$remote = Kamereon::Read('ChargeHistory');
		$remote   = Kamereon::Read('ChargingSettings');
		$array = json_decode($remote, true);
		if (!isset($array['data'])) return null;

		$weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

		if ($array['data']['attributes']['mode'] == 'scheduled') {
			$return = [];
			$schedules = $array['data']['attributes']['schedules'];
			foreach ($schedules as $schedule) {
				$index = $schedule['id'];
				$plan = new stdClass;
				$plan->active = (bool) $schedule['activated'];
				$plan->day = [];
				foreach ($weekdays as $key) {
					$plan->day[$key] = new stdClass;
					$plan->day[$key]->startTime = $schedule[$key]['startTime'];
					$plan->day[$key]->duration  = $schedule[$key]['duration'];
				}
				$return[$index] = $plan;
			}
			return $return;
		}
		*/
		return null;
	}
}
