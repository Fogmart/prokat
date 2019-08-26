<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

jimport('joomla.application.component.view');

class VikrentcarViewStandbyorder extends JViewVikRentCar {
	function display($tpl = null) {
		$sid = VikRequest::getString('sid', '', 'request');
		$ts = VikRequest::getString('ts', '', 'request');
		$dbo = JFactory::getDBO();
		$vrc_tn = VikRentCar::getTranslator();
		$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `sid`=" . $dbo->quote($sid) . " AND `ts`=" . $dbo->quote($ts) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		$order = $dbo->loadAssocList();
		$tar = array("");
		$is_cust_cost = (!empty($order[0]['cust_cost']) && $order[0]['cust_cost'] > 0);
		if (!empty($order[0]['idtar'])) {
			//vikrentcar 1.5
			if ($order[0]['hourly'] == 1) {
				$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `id`='" . $order[0]['idtar'] . "';";
			} else {
				$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='" . $order[0]['idtar'] . "';";
			}
			//
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$tar = $dbo->loadAssocList();
			}
		} elseif ($is_cust_cost) {
			//Custom Rate
			$tar = array(0 => array(
				'id' => -1,
				'idcar' => $order[0]['idcar'],
				'days' => $order[0]['days'],
				'idprice' => -1,
				'cost' => $order[0]['cust_cost'],
				'attrdata' => '',
			));
		}
		//vikrentcar 1.5
		if ($order[0]['hourly'] == 1) {
			foreach($tar as $kt => $vt) {
				$tar[$kt]['days'] = 1;
			}
		}
		//
		//vikrentcar 1.6
		$checkhourscharges = 0;
		$hoursdiff = 0;
		$ppickup = $order[0]['ritiro'];
		$prelease = $order[0]['consegna'];
		$secdiff = $prelease - $ppickup;
		$daysdiff = $secdiff / 86400;
		if (is_int($daysdiff)) {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			}
		} else {
			if ($daysdiff < 1) {
				$daysdiff = 1;
				$checkhourly = true;
				$ophours = $secdiff / 3600;
				$hoursdiff = intval(round($ophours));
				if ($hoursdiff < 1) {
					$hoursdiff = 1;
				}
			} else {
				$sum = floor($daysdiff) * 86400;
				$newdiff = $secdiff - $sum;
				$maxhmore = VikRentCar::getHoursMoreRb() * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
					//vikrentcar 1.6
					$ehours = intval(round(($newdiff - $maxhmore) / 3600));
					$checkhourscharges = $ehours;
					if ($checkhourscharges > 0) {
						$aehourschbasp = VikRentCar::applyExtraHoursChargesBasp();
					}
					//
				}
			}
		}
		if ($checkhourscharges > 0 && $aehourschbasp == true && !$is_cust_cost) {
			$ret = VikRentCar::applyExtraHoursChargesCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, false, true, true);
			$tar = $ret['return'];
			$calcdays = $ret['days'];
		}
		if ($checkhourscharges > 0 && $aehourschbasp == false && !$is_cust_cost) {
			$tar = VikRentCar::extraHoursSetPreviousFareCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, true);
			$tar = VikRentCar::applySeasonsCar($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
			$ret = VikRentCar::applyExtraHoursChargesCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, true, true, true);
			$tar = $ret['return'];
			$calcdays = $ret['days'];
		} else {
			if (!$is_cust_cost) {
				//Seasonal prices only if not a custom rate
				$tar = VikRentCar::applySeasonsCar($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
			}
		}
		//
		$exppay = explode('=', $order[0]['idpayment']);
		$payment = VikRentCar::getPayment($exppay[0], $vrc_tn);
		//vikrentcar 1.6
		if ($checkhourscharges > 0) {
			$this->calcdays = &$calcdays;
		}
		//
		$this->ord = &$order[0];
		$this->tar = &$tar[0];
		$this->payment = &$payment;
		$this->vrc_tn = &$vrc_tn;
		//theme
		$theme = VikRentCar::getTheme();
		if ($theme != 'default') {
			$thdir = VRC_SITE_PATH.DS.'themes'.DS.$theme.DS.'standbyorder';
			if (is_dir($thdir)) {
				$this->_setPath('template', $thdir.DS);
			}
		}
		//
		parent::display($tpl);
	}
}
