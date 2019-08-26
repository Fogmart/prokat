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

class VikrentcarViewShowprc extends JViewVikRentCar {
	function display($tpl = null) {
		$mainframe = JFactory::getApplication();
		$pcaropt = VikRequest::getInt('caropt', '', 'request');
		$pdays = VikRequest::getString('days', '', 'request');
		$ppickup = VikRequest::getString('pickup', '', 'request');
		$prelease = VikRequest::getString('release', '', 'request');
		$pplace = VikRequest::getInt('place', '', 'request');
		$preturnplace = VikRequest::getInt('returnplace', '', 'request');
		$nowdf = VikRentCar::getDateFormat();
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$dbo = JFactory::getDBO();
		$vrc_tn = VikRentCar::getTranslator();
		$q = "SELECT `units` FROM `#__vikrentcar_cars` WHERE `id`=" . $dbo->quote($pcaropt) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			VikError::raiseWarning('', JText::_('VRCARNOTFND'));
			$mainframe->redirect("index.php");
			exit;
		}
		$units = $dbo->loadResult();
		//vikrentcar 1.5
		$checkhourly = false;
		//vikrentcar 1.6
		$checkhourscharges = 0;
		//
		$hoursdiff = 0;
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
		$goonunits = VikRentCar::carBookable($pcaropt, $units, $ppickup, $prelease);
		if ($goonunits) {
			// VRC 1.12 - Closed rate plans on these dates
			$carrpclosed = VikRentCar::getCarRplansClosedInDates(array($pcaropt), $ppickup, $daysdiff);
			//
			$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `days`=" . $dbo->quote($pdays) . " AND `idcar`=" . $dbo->quote($pcaropt) . " ORDER BY `#__vikrentcar_dispcost`.`cost` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$tars = $dbo->loadAssocList();
				// VRC 1.12 - Closed rate plans on these dates
				if (count($carrpclosed) > 0 && array_key_exists($pcaropt, $carrpclosed)) {
					foreach ($tars as $kk => $tt) {
						if (array_key_exists('idprice', $tt) && array_key_exists($tt['idprice'], $carrpclosed[$pcaropt])) {
							unset($tars[$kk]);
						}
					}
				}
				//vikrentcar 1.5
				if ($checkhourly) {
					$tars = VikRentCar::applyHourlyPricesCar($tars, $hoursdiff, $pcaropt);
				}
				//
				//vikrentcar 1.6
				if ($checkhourscharges > 0 && $aehourschbasp == true) {
					$tars = VikRentCar::applyExtraHoursChargesCar($tars, $pcaropt, $checkhourscharges, $daysdiff);
				}
				//
				$q = "SELECT * FROM `#__vikrentcar_cars` WHERE `id`=" . $dbo->quote($pcaropt) . "" . (!empty ($pplace) ? " AND `idplace` LIKE ".$dbo->quote("%".$pplace.";%") : "") . ";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$car = $dbo->loadAssocList();
					$vrc_tn->translateContents($car, '#__vikrentcar_cars');
					if (intval($car[0]['avail']) == 1) {
						if (VikRentCar::dayValidTs($pdays, $ppickup, $prelease)) {
							//vikrentcar 1.6
							if ($checkhourscharges > 0 && $aehourschbasp == false) {
								$tars = VikRentCar::extraHoursSetPreviousFareCar($tars, $pcaropt, $checkhourscharges, $daysdiff);
								$tars = VikRentCar::applySeasonsCar($tars, $ppickup, $prelease, $pplace);
								$tars = VikRentCar::applyExtraHoursChargesCar($tars, $pcaropt, $checkhourscharges, $daysdiff, true);
							} else {
								$tars = VikRentCar::applySeasonsCar($tars, $ppickup, $prelease, $pplace);
							}
							//
							//apply locations fee
							if (!empty ($pplace) && !empty ($preturnplace)) {
								$locfee = VikRentCar::getLocFee($pplace, $preturnplace);
								if ($locfee) {
									//VikRentCar 1.7 - Location fees overrides
									if (strlen($locfee['losoverride']) > 0) {
										$arrvaloverrides = array();
										$valovrparts = explode('_', $locfee['losoverride']);
										foreach($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($pdays, $arrvaloverrides)) {
											$locfee['cost'] = $arrvaloverrides[$pdays];
										}
									}
									//end VikRentCar 1.7 - Location fees overrides
									$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $pdays) : $locfee['cost'];
									$lfarr = array ();
									foreach ($tars as $kat => $at) {
										$newcost = $at['cost'] + $locfeecost;
										$at['cost'] = $newcost;
										$lfarr[$kat] = $at;
									}
									$tars = $lfarr;
								}
							}
							//
							//VRC 1.9 - Out of Hours Fees
							$oohfee = VikRentCar::getOutOfHoursFees($pplace, $preturnplace, $ppickup, $prelease, $car[0]);
							if (count($oohfee) > 0) {
								foreach ($tars as $kat => $at) {
									$newcost = $at['cost'] + $oohfee['cost'];
									$tars[$kat]['cost'] = $newcost;
								}
							}
							//
							$this->tars = &$tars;
							$this->car = &$car[0];
							$this->pickup = &$ppickup;
							$this->release = &$prelease;
							$this->place = &$pplace;
							$this->vrc_tn = &$vrc_tn;
							//theme
							$theme = VikRentCar::getTheme();
							if ($theme != 'default') {
								$thdir = VRC_SITE_PATH.DS.'themes'.DS.$theme.DS.'showprc';
								if (is_dir($thdir)) {
									$this->_setPath('template', $thdir.DS);
								}
							}
							//
							parent::display($tpl);
						} else {
							showSelect(JText::_('VRERRCALCTAR'));
						}
					} else {
						showSelect(JText::_('VRCARNOTAV'));
					}
				} else {
					showSelect(JText::_('VRCARNOTFND'));
				}
			} else {
				showSelect(JText::_('VRNOTARFNDSELO'));
			}
		} else {
			showSelect(JText::_('VRCARNOTRIT') . " " . date($df . ' H:i', $ppickup) . " " . JText::_('VRCARNOTCONSTO') . " " . date($df . ' H:i', $prelease));
		}
	}
}
