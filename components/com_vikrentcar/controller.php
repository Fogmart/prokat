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

jimport('joomla.application.component.controller');

class VikrentcarController extends JControllerVikRentCar {
	
	function display($cachable = false, $urlparams = array()) {
		$view=VikRequest::getVar('view', '');
		if ($view == 'carslist') {
			VikRequest::setVar('view', 'carslist');
		} elseif ($view == 'cardetails') {
			VikRequest::setVar('view', 'cardetails');
		} elseif ($view == 'loginregister') {
			VikRequest::setVar('view', 'loginregister');
		} elseif ($view == 'locationsmap') {
			VikRequest::setVar('view', 'locationsmap');
		} elseif ($view == 'locationslist') {
			VikRequest::setVar('view', 'locationslist');
		} elseif ($view == 'userorders') {
			VikRequest::setVar('view', 'userorders');
		} elseif ($view == 'promotions') {
			VikRequest::setVar('view', 'promotions');
		} else {
			VikRequest::setVar('view', 'vikrentcar');
		}
		parent::display();
	}

	function search() {
		VikRequest::setVar('view', 'search');
		parent::display();
	}

	function showprc() {
		VikRequest::setVar('view', 'showprc');
		parent::display();
	}

	function oconfirm() {
		$requirelogin = VikRentCar::requireLogin();
		if ($requirelogin) {
			if (VikRentCar::userIsLogged()) {
				VikRequest::setVar('view', 'oconfirm');
			} else {
				VikRequest::setVar('view', 'loginregister');
			}
		} else {
			VikRequest::setVar('view', 'oconfirm');
		}
		parent::display();
	}
	
	function register() {
		$mainframe = JFactory::getApplication();
		$dbo = JFactory::getDBO();
		$vrc_app = VikRentCar::getVrcApplication();
		//user data
		$pname = VikRequest::getString('name', '', 'request');
		$plname = VikRequest::getString('lname', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pusername = VikRequest::getString('username', '', 'request');
		$ppassword = VikRequest::getString('password', '', 'request');
		$pconfpassword = VikRequest::getString('confpassword', '', 'request');
		//
		//order data
		$ppriceid = VikRequest::getString('priceid', '', 'request');
		$pplace = VikRequest::getString('place', '', 'request');
		$preturnplace = VikRequest::getString('returnplace', '', 'request');
		$pcarid = VikRequest::getString('carid', '', 'request');
		$pdays = VikRequest::getString('days', '', 'request');
		$ppickup = VikRequest::getString('pickup', '', 'request');
		$prelease = VikRequest::getString('release', '', 'request');
		$pitemid = VikRequest::getString('Itemid', '', 'request');
		$copts = array();
		$q = "SELECT * FROM `#__vikrentcar_optionals`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$optionals = $dbo->loadAssocList();
			foreach ($optionals as $opt) {
				$tmpvar = VikRequest::getString('optid' . $opt['id'], '', 'request');
				if (!empty($tmpvar)) {
					$copts[$opt['id']] = $tmpvar;
				}
			}
		}
		$chosenopts = "";
		if (is_array($copts) && @count($copts) > 0) {
			foreach ($copts as $idopt => $quanopt) {
				$chosenopts .= "&optid".$idopt."=".$quanopt;
			}
		}
		$qstring = "priceid=".$ppriceid."&place=".$pplace."&returnplace=".$preturnplace."&carid=".$pcarid."&days=".$pdays."&pickup=".$ppickup."&release=".$prelease.(!empty($chosenopts) ? $chosenopts : "").(!empty($pitemid) ? "&Itemid=".$pitemid : "");
		//
		if (!VikRentCar::userIsLogged()) {
			// captcha verification
			if ($vrc_app->isCaptcha() && !$vrc_app->reCaptcha('check')) {
				VikError::raiseWarning('', 'Invalid Captcha');
				$mainframe->redirect(JRoute::_('index.php?option=com_vikrentcar&view=loginregister&'.$qstring, false));
				exit;
			}
			//
			if (!empty($pname) && !empty($plname) && !empty($pusername) && VikRentCar::validEmail($pemail) && $ppassword == $pconfpassword) {
				//save user
				$newuserid = VikRentCar::addJoomlaUser($pname." ".$plname, $pusername, $pemail, $ppassword);
				if ($newuserid && strlen($newuserid)) {
					//registration success
					$credentials = array('username' => $pusername, 'password' => $ppassword );
					//autologin
					$mainframe->login($credentials);
					$currentUser = JFactory::getUser();
					$currentUser->setLastVisit(time());
					$currentUser->set('guest', 0);
					//
					if (!empty($ppriceid)) {
						$mainframe->redirect(JRoute::_('index.php?option=com_vikrentcar&task=oconfirm&'.$qstring, false));
					} else {
						$mainframe->redirect(JRoute::_('index.php?option=com_vikrentcar&view=userorders', false));
					}
				} else {
					//error while saving new user
					VikError::raiseWarning('', JText::_('VRCREGERRSAVING'));
					$mainframe->redirect(JRoute::_('index.php?option=com_vikrentcar&view=loginregister&'.$qstring, false));
				}
			} else {
				//invalid data
				VikError::raiseWarning('', JText::_('VRCREGERRINSDATA'));
				$mainframe->redirect(JRoute::_('index.php?option=com_vikrentcar&view=loginregister&'.$qstring, false));
			}
		} else {
			//user is already logged in, proceed
			$mainframe->redirect(JRoute::_('index.php?option=com_vikrentcar&task=oconfirm&'.$qstring, false));
		}
	}
	
	function saveorder() {
		$dbo = JFactory::getDBO();
		$vrc_tn = VikRentCar::getTranslator();
		$pcar = VikRequest::getString('car', '', 'request');
		$pdays = VikRequest::getString('days', '', 'request');
		//vikrentcar 1.6
		$porigdays = VikRequest::getString('origdays', '', 'request');
		$pcouponcode = VikRequest::getString('couponcode', '', 'request');
		//
		$ppickup = VikRequest::getString('pickup', '', 'request');
		$prelease = VikRequest::getString('release', '', 'request');
		$pprtar = VikRequest::getString('prtar', '', 'request');
		$poptionals = VikRequest::getString('optionals', '', 'request');
		$ptotdue = VikRequest::getString('totdue', '', 'request');
		$pplace = VikRequest::getString('place', '', 'request');
		$preturnplace = VikRequest::getString('returnplace', '', 'request');
		$pgpayid = VikRequest::getString('gpayid', '', 'request');
		$ppriceid = VikRequest::getString('priceid', '', 'request');
		$phourly = VikRequest::getString('hourly', '', 'request');
		$pitemid = VikRequest::getInt('Itemid', '', 'request');
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$validtoken = false;
		if (VikRentCar::tokenForm()) {
			$pviktoken = VikRequest::getString('viktoken', '', 'request');
			session_start();
			if (!empty($pviktoken) && $_SESSION['vikrtoken'] == $pviktoken) {
				unset ($_SESSION['vikrtoken']);
				$validtoken = true;
			}
		} else {
			$validtoken = true;
		}
		if ($validtoken) {
			$q = "SELECT * FROM `#__vikrentcar_custfields` ORDER BY `#__vikrentcar_custfields`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$cfields = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
			$vrc_tn->translateContents($cfields, '#__vikrentcar_custfields');
			$suffdata = true;
			$useremail = "";
			$usercountry = '';
			$nominatives = array();
			$t_first_name = '';
			$t_last_name = '';
			$nominative_str = '';
			$phone_number = '';
			$fieldflags = array();
			if (@ is_array($cfields)) {
				foreach ($cfields as $cf) {
					if (intval($cf['required']) == 1 && $cf['type'] != 'separator') {
						$tmpcfval = VikRequest::getString('vrcf' . $cf['id'], '', 'request');
						if (strlen(str_replace(" ", "", trim($tmpcfval))) <= 0) {
							$suffdata = false;
							break;
						}
					}
				}
				//save user email, nominatives, phone number and create custdata array
				$arrcustdata = array ();
				$arrcfields = array();
				$nextorderdata = array();
				$nextorderdata['customfields'] = array();
				$emailwasfound = false;
				foreach ($cfields as $cf) {
					if (intval($cf['isemail']) == 1 && $emailwasfound == false) {
						$useremail = trim(VikRequest::getString('vrcf' . $cf['id'], '', 'request'));
						$emailwasfound = true;
					}
					if ($cf['isnominative'] == 1) {
						$tmpcfval = VikRequest::getString('vrcf' . $cf['id'], '', 'request');
						if (strlen(str_replace(" ", "", trim($tmpcfval))) > 0) {
							$nominatives[] = $tmpcfval;
						}
					}
					if ($cf['isphone'] == 1) {
						$tmpcfval = VikRequest::getString('vrcf' . $cf['id'], '', 'request');
						if (strlen(str_replace(" ", "", trim($tmpcfval))) > 0) {
							$phone_number = $tmpcfval;
						}
					}
					if (!empty($cf['flag'])) {
						$tmpcfval = VikRequest::getString('vrcf' . $cf['id'], '', 'request');
						if (strlen(str_replace(" ", "", trim($tmpcfval))) > 0) {
							$fieldflags[$cf['flag']] = $tmpcfval;
						}
					}
					if ($cf['type'] != 'separator' && $cf['type'] != 'country' && ( $cf['type'] != 'checkbox' || ($cf['type'] == 'checkbox' && intval($cf['required']) != 1) ) ) {
						$arrcustdata[JText::_($cf['name'])] = VikRequest::getString('vrcf' . $cf['id'], '', 'request');
						$arrcfields[$cf['id']] = VikRequest::getString('vrcf' . $cf['id'], '', 'request');
						$nextorderdata['customfields'][$cf['id']] = VikRequest::getString('vrcf' . $cf['id'], '', 'request');
					} elseif ($cf['type'] == 'country') {
						$countryval = VikRequest::getString('vrcf' . $cf['id'], '', 'request');
						if (!empty($countryval) && strstr($countryval, '::') !== false) {
							$countryparts = explode('::', $countryval);
							$usercountry = $countryparts[0];
							$arrcustdata[JText::_($cf['name'])] = $countryparts[1];
							$nextorderdata['customfields'][$cf['id']] = $countryparts[0];
						} else {
							$arrcustdata[JText::_($cf['name'])] = '';
						}
					}
				}
				if (count($nominatives) > 0) {
					$nominative_str = implode(" ", $nominatives);
				}
				if (count($nominatives) >= 2) {
					$t_last_name = array_pop($nominatives);
					$t_first_name = array_pop($nominatives);
				}
				//
			}
			if ($suffdata == true) {
				//Customer Data for Next Order
				$currentUser = JFactory::getUser();
				if (!empty($currentUser->id) && intval($currentUser->id) > 0) {
					$storenextdata = json_encode($nextorderdata);
					$q = "SELECT `id` FROM `#__vikrentcar_usersdata` WHERE `ujid`='".(int)$currentUser->id."';";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$oldnextid = $dbo->loadAssocList();
						$q = "UPDATE `#__vikrentcar_usersdata` SET `data`=".$dbo->quote($storenextdata)." WHERE `id`='".(int)$oldnextid[0]['id']."';";
					} else {
						$q = "INSERT INTO `#__vikrentcar_usersdata` (`ujid`,`data`) VALUES('".(int)$currentUser->id."', ".$dbo->quote($storenextdata).");";
					}
					$dbo->setQuery($q);
					$dbo->execute();
				}
				//
				//vikrentcar 1.6 for dayValidTs()
				if (strlen($porigdays) > 0) {
					$calcdays = $pdays;
					$pdays = $porigdays;
				} else {
					$calcdays = $pdays;
				}
				//
				if (VikRentCar::dayValidTs($pdays, $ppickup, $prelease)) {
					$currencyname = VikRentCar::getCurrencyName();
					//vikrentcar 1.5
					if (intval($phourly) > 0) {
						$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `id`=" . $dbo->quote($pprtar) . " AND `idcar`=" . $dbo->quote($pcar) . " AND `hours`=" . $dbo->quote($phourly) . ";";
						$usedhourly = true;
					} else {
						//vikrentcar 1.6 for extra hours charges
						if (strlen($porigdays) > 0) {
							$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`=" . $dbo->quote($pprtar) . " AND `idcar`=" . $dbo->quote($pcar) . " AND `days`=" . $dbo->quote($calcdays) . ";";
						} else {
							$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`=" . $dbo->quote($pprtar) . " AND `idcar`=" . $dbo->quote($pcar) . " AND `days`=" . $dbo->quote($pdays) . ";";
						}
						//
						$usedhourly = false;
					}
					//
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() == 1) {
						$tar = $dbo->loadAssocList();
						//vikrentcar 1.5
						if ($usedhourly) {
							foreach ($tar as $kt => $vt) {
								$tar[$kt]['days'] = 1;
							}
						}
						//
						//vikrentcar 1.6
						$secdiff = $prelease - $ppickup;
						$daysdiff = $secdiff / 86400;
						if (is_int($daysdiff)) {
							if ($daysdiff < 1) {
								$daysdiff = 1;
							}
						} else {
							if ($daysdiff < 1) {
								$daysdiff = 1;
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
									$ehours = intval(round(($newdiff - $maxhmore) / 3600));
									$checkhourscharges = $ehours;
									if ($checkhourscharges > 0) {
										$aehourschbasp = VikRentCar::applyExtraHoursChargesBasp();
									}
								}
							}
						}
						if ($checkhourscharges > 0 && $aehourschbasp == true) {
							$ret = VikRentCar::applyExtraHoursChargesCar($tar, $pcar, $checkhourscharges, $daysdiff, false, true, true);
							$tar = $ret['return'];
							$calcdays = $ret['days'];
						}
						if ($checkhourscharges > 0 && $aehourschbasp == false) {
							$tar = VikRentCar::extraHoursSetPreviousFareCar($tar, $pcar, $checkhourscharges, $daysdiff, true);
							$tar = VikRentCar::applySeasonsCar($tar, $ppickup, $prelease, $pplace);
							$ret = VikRentCar::applyExtraHoursChargesCar($tar, $pcar, $checkhourscharges, $daysdiff, true, true, true);
							$tar = $ret['return'];
							$calcdays = $ret['days'];
						} else {
							$tar = VikRentCar::applySeasonsCar($tar, $ppickup, $prelease, $pplace);
						}
						//
						$isdue = VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice']);
						$optstr = "";
						$optarrtaxnet = array();
						if (!empty($poptionals)) {
							$stepo = explode(";", $poptionals);
							foreach ($stepo as $oo) {
								if (!empty($oo)) {
									$stept = explode(":", $oo);
									$q = "SELECT * FROM `#__vikrentcar_optionals` WHERE `id`=" . $dbo->quote($stept[0]) . ";";
									$dbo->setQuery($q);
									$dbo->execute();
									if ($dbo->getNumRows() == 1) {
										$actopt = $dbo->loadAssocList();
										$vrc_tn->translateContents($actopt, '#__vikrentcar_optionals');
										$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $calcdays * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
										$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $calcdays) : $actopt[0]['cost'];
										if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
											$realcost = $actopt[0]['maxprice'];
											if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
												$realcost = $actopt[0]['maxprice'] * $stept[1];
											}
										}
										$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva']);
										$isdue += $tmpopr;
										$optnetprice = VikRentCar::sayOptionalsMinusIva($realcost, $actopt[0]['idiva']);
										$optarrtaxnet[] = $optnetprice;
										$optstr .= ($stept[1] > 1 ? $stept[1] . " " : "") . $actopt[0]['name'] . ": " . $tmpopr . " " . $currencyname . "\n";
									}
								}
							}
						}
						$maillocfee = "";
						$locfeewithouttax = 0;
						$validlocations = true;
						if (!empty($pplace) && !empty($preturnplace)) {
							$locfee = VikRentCar::getLocFee($pplace, $preturnplace);
							if ($locfee) {
								//VikRentCar 1.7 - Location fees overrides
								if (strlen($locfee['losoverride']) > 0) {
									$arrvaloverrides = array();
									$valovrparts = explode('_', $locfee['losoverride']);
									foreach ($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											$arrvaloverrides[(int)$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists((int)$calcdays, $arrvaloverrides)) {
										$locfee['cost'] = $arrvaloverrides[$calcdays];
									}
								}
								//end VikRentCar 1.7 - Location fees overrides
								$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $calcdays) : $locfee['cost'];
								$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva']);
								$isdue += $locfeewith;
								$locfeewithouttax = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva']);
								$maillocfee = $locfeewith;
							}
							//check valid locations
							$q = "SELECT `id`,`idplace`,`idretplace` FROM `#__vikrentcar_cars` WHERE `id`=" . $dbo->quote($pcar) . ";";
							$dbo->setQuery($q);
							$dbo->execute();
							$infoplaces = $dbo->loadAssocList();
							if (!empty($infoplaces[0]['idplace']) && !empty($infoplaces[0]['idretplace'])) {
								$actplaces = explode(";", $infoplaces[0]['idplace']);
								$actretplaces = explode(";", $infoplaces[0]['idretplace']);
								if (!in_array($pplace, $actplaces) || !in_array($preturnplace, $actretplaces)) {
									$validlocations = false;
								}
							}
							//
						}
						//VRC 1.9 - Out of Hours Fees
						$oohfee = VikRentCar::getOutOfHoursFees($pplace, $preturnplace, $ppickup, $prelease, array('id' => (int)$pcar));
						$mailoohfee = "";
						$oohfeewithouttax = 0;
						if (count($oohfee) > 0) {
							$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
							$isdue += $oohfeewith;
							$oohfeewithouttax = VikRentCar::sayOohFeeMinusIva($oohfee['cost'], $oohfee['idiva']);
							$mailoohfee = $oohfeewith;
						}
						//
						//vikrentcar 1.6
						$origtotdue = $isdue;
						$usedcoupon = false;
						if (strlen($pcouponcode) > 0) {
							$coupon = VikRentCar::getCouponInfo($pcouponcode);
							if (is_array($coupon)) {
								$coupondateok = true;
								if (strlen($coupon['datevalid']) > 0) {
									$dateparts = explode("-", $coupon['datevalid']);
									$pickinfo = getdate($ppickup);
									$dropinfo = getdate($prelease);
									$checkpick = mktime(0, 0, 0, $pickinfo['mon'], $pickinfo['mday'], $pickinfo['year']);
									$checkdrop = mktime(0, 0, 0, $dropinfo['mon'], $dropinfo['mday'], $dropinfo['year']);
									if (!($checkpick >= $dateparts[0] && $checkpick <= $dateparts[1] && $checkdrop >= $dateparts[0] && $checkdrop <= $dateparts[1])) {
										$coupondateok = false;
									}
								}
								if ($coupondateok == true) {
									$couponcarok = true;
									if ($coupon['allvehicles'] == 0) {
										if (!(preg_match("/;".$pcar.";/i", $coupon['idcars']))) {
											$couponcarok = false;
										}
									}
									if ($couponcarok == true) {
										$coupontotok = true;
										if (strlen($coupon['mintotord']) > 0) {
											if ($isdue < $coupon['mintotord']) {
												$coupontotok = false;
											}
										}
										if ($coupontotok == true) {
											$usedcoupon = true;
											if ($coupon['percentot'] == 1) {
												//percent value
												$minuscoupon = 100 - $coupon['value'];
												$coupondiscount = $isdue * $coupon['value'] / 100;
												$isdue = $isdue * $minuscoupon / 100;
											} else {
												//total value
												$coupondiscount = $coupon['value'];
												$isdue = $isdue - $coupon['value'];
											}
											$strcouponeff = $coupon['id'].';'.$coupondiscount.';'.$coupon['code'];
										}
									}
								}
							}
						}
						//
						$strisdue = number_format($isdue, 2)."vikrentcar";
						$ptotdue = number_format($ptotdue, 2)."vikrentcar";
						if ($strisdue == $ptotdue) {
							$nowts = time();
							$checkts = $nowts;
							$today_bookings = VikRentCar::todayBookings();
							if ($today_bookings) {
								$checkts = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
							}
							if ($checkts <= $ppickup && $checkts < $prelease && $ppickup < $prelease) {
								if ($validlocations == true) {
									$q = "SELECT `units` FROM `#__vikrentcar_cars` WHERE `id`=" . $dbo->quote($pcar) . ";";
									$dbo->setQuery($q);
									$dbo->execute();
									$units = $dbo->loadResult();
									if (VikRentCar::carNotLocked($pcar, $units, $ppickup, $prelease)) {
										if (VikRentCar::carBookable($pcar, $units, $ppickup, $prelease)) {
											//vikrentcar 1.6 restore $pdays to the actual days used
											if (strlen($porigdays) > 0) {
												$pdays = $calcdays;
											}
											//
											$sid = VikRentCar::getSecretLink();
											$custdata = VikRentCar::buildCustData($arrcustdata, "\n");
											$viklink = JURI::root() . "index.php?option=com_vikrentcar&task=vieworder&sid=" . $sid . "&ts=" . $nowts . (!empty($pitemid) ? "&Itemid=" . $pitemid : "");
											$admail = VikRentCar::getAdminMail();
											$ftitle = VikRentCar::getFrontTitle($vrc_tn);
											$carinfo = VikRentCar::getCarInfo($pcar, $vrc_tn);
											$costplusiva = VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice']);
											$costminusiva = VikRentCar::sayCostMinusIva($tar[0]['cost'], $tar[0]['idprice']);
											$pricestr = VikRentCar::getPriceName($tar[0]['idprice'], $vrc_tn) . ": " . $costplusiva . (!empty($tar[0]['attrdata']) ? " " . $currencyname . "\n" . VikRentCar::getPriceAttr($tar[0]['idprice'], $vrc_tn) . ": " . $tar[0]['attrdata'] : "");
											$ritplace = (!empty($pplace) ? VikRentCar::getPlaceName($pplace, $vrc_tn) : "");
											$consegnaplace = (!empty($preturnplace) ? VikRentCar::getPlaceName($preturnplace, $vrc_tn) : "");
											$currentUser = JFactory::getUser();
											$arrayinfopdf = array('days' => $pdays, 'tarminusiva' => $costminusiva, 'tartax' => ($costplusiva - $costminusiva), 'opttaxnet' => $optarrtaxnet, 'locfeenet' => $locfeewithouttax, 'oohfeenet' => $oohfeewithouttax);
											//VRC 1.7 Rev.2
											$session = JFactory::getSession();
											$locationvat = $session->get('vrcLocationTaxRate', '');
											//unset session vals for mod_vikrentcar_itinerary
											$session->set('vrcpickupts', '');
											$session->set('vrcplace', '');
											// customer booking
											$cpin = VikRentCar::getCPinIstance();
											$cpin->setCustomerExtraInfo($fieldflags);
											$cpin->saveCustomerDetails($t_first_name, $t_last_name, $useremail, $phone_number, $usercountry, $arrcfields);
											//
											$langtag = $vrc_tn->current_lang;
											$car_params = !empty($carinfo['params']) ? json_decode($carinfo['params'], true) : array();
											if (VikRentCar::areTherePayments()) {
												$payment = VikRentCar::getPayment($pgpayid, $vrc_tn);
												$realback = VikRentCar::getHoursCarAvail() * 3600;
												$realback += $prelease;
												if (is_array($payment)) {
													if (intval($payment['setconfirmed']) == 1) {
														$q = "INSERT INTO `#__vikrentcar_busy` (`idcar`,`ritiro`,`consegna`,`realback`) VALUES(" . $dbo->quote($pcar) . ", " . $dbo->quote($ppickup) . ", " . $dbo->quote($prelease) . ",'" . $realback . "');";
														$dbo->setQuery($q);
														$dbo->execute();
														$lid = $dbo->insertid();
														$q = "INSERT INTO `#__vikrentcar_orders` (`idbusy`,`custdata`,`ts`,`status`,`idcar`,`days`,`ritiro`,`consegna`,`idtar`,`optionals`,`custmail`,`sid`,`idplace`,`idreturnplace`,`totpaid`,`idpayment`,`ujid`,`hourly`,`coupon`,`order_total`,`locationvat`,`lang`,`country`,`phone`,`nominative`) VALUES('" . $lid . "', " . $dbo->quote($custdata) . ",'" . $nowts . "','confirmed'," . $dbo->quote($pcar) . "," . $dbo->quote($pdays) . "," . $dbo->quote($ppickup) . "," . $dbo->quote($prelease) . "," . $dbo->quote($pprtar) . "," . $dbo->quote($poptionals) . "," . $dbo->quote($useremail) . ",'" . $sid . "'," . $dbo->quote($pplace) . "," . $dbo->quote($preturnplace) . "," . $dbo->quote($isdue) . "," . $dbo->quote($payment['id'] . '=' . $payment['name']) . ",'".$currentUser->id."','".($usedhourly ? "1" : "0")."', ".$dbo->quote($strcouponeff).", '".$isdue."', ".(strlen($locationvat) > 0 ? "'".$locationvat."'" : "null").", ".$dbo->quote($langtag).", ".(!empty($usercountry) ? $dbo->quote($usercountry) : 'null').", ".$dbo->quote($phone_number).", ".$dbo->quote($nominative_str).");";
														$dbo->setQuery($q);
														$dbo->execute();
														$neworderid = $dbo->insertid();
														//Customer Booking
														$cpin->saveCustomerBooking($neworderid);
														//end Customer Booking
														if ($usedcoupon == true && $coupon['type'] == 2) {
															$q = "DELETE FROM `#__vikrentcar_coupons` WHERE `id`='".$coupon['id']."';";
															$dbo->setQuery($q);
															$dbo->execute();
														}
														VikRentCar::sendAdminMail($admail.';;'.$useremail, JText::_('VRORDNOL'), $ftitle, $nowts, $custdata, $carinfo['name'], $ppickup, $prelease, $pricestr, $optstr, $isdue, JText::_('VRCOMPLETED'), $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $payment['name'], $strcouponeff);
														VikRentCar::sendCustMail($useremail, strip_tags($ftitle) . " " . JText::_('VRORDNOL'), $ftitle, $nowts, $custdata, $carinfo['name'], $ppickup, $prelease, $pricestr, $optstr, $isdue, $viklink, JText::_('VRCOMPLETED'), $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $neworderid, $strcouponeff, $arrayinfopdf);
														if (!empty($car_params['email'])) {
															VikRentCar::sendCustMail($car_params['email'], strip_tags($ftitle) . " " . JText::_('VRORDNOL'), $ftitle, $nowts, $custdata, $carinfo['name'], $ppickup, $prelease, $pricestr, $optstr, $isdue, $viklink, JText::_('VRCOMPLETED'), $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $neworderid, $strcouponeff, $arrayinfopdf);
														}
														$mainframe = JFactory::getApplication();
														$mainframe->redirect(JRoute::_("index.php?option=com_vikrentcar&task=vieworder&sid=" . $sid . "&ts=" . $nowts . (!empty($pitemid) ? "&Itemid=" . $pitemid : "") . ($ptmpl == 'component' ? '&tmpl=component' : ''), false));
													} else {
														$q = "INSERT INTO `#__vikrentcar_orders` (`custdata`,`ts`,`status`,`idcar`,`days`,`ritiro`,`consegna`,`idtar`,`optionals`,`custmail`,`sid`,`idplace`,`idreturnplace`,`idpayment`,`ujid`,`hourly`,`coupon`,`order_total`,`locationvat`,`lang`,`country`,`phone`,`nominative`) VALUES(" . $dbo->quote($custdata) . ",'" . $nowts . "','standby'," . $dbo->quote($pcar) . "," . $dbo->quote($pdays) . "," . $dbo->quote($ppickup) . "," . $dbo->quote($prelease) . "," . $dbo->quote($pprtar) . "," . $dbo->quote($poptionals) . "," . $dbo->quote($useremail) . ",'" . $sid . "'," . $dbo->quote($pplace) . "," . $dbo->quote($preturnplace) . "," . $dbo->quote($payment['id'] . '=' . $payment['name']) . ",'".$currentUser->id."','".($usedhourly ? "1" : "0")."', ".$dbo->quote($strcouponeff).", '".$isdue."', ".(strlen($locationvat) > 0 ? "'".$locationvat."'" : "null").", ".$dbo->quote($langtag).", ".(!empty($usercountry) ? $dbo->quote($usercountry) : 'null').", ".$dbo->quote($phone_number).", ".$dbo->quote($nominative_str).");";
														$dbo->setQuery($q);
														$dbo->execute();
														$neworderid = $dbo->insertid();
														//Customer Booking
														$cpin->saveCustomerBooking($neworderid);
														//end Customer Booking
														if ($usedcoupon == true && $coupon['type'] == 2) {
															$q = "DELETE FROM `#__vikrentcar_coupons` WHERE `id`='".$coupon['id']."';";
															$dbo->setQuery($q);
															$dbo->execute();
														}
														$q = "INSERT INTO `#__vikrentcar_tmplock` (`idcar`,`ritiro`,`consegna`,`until`,`realback`,`idorder`) VALUES(" . $dbo->quote($pcar) . "," . $dbo->quote($ppickup) . "," . $dbo->quote($prelease) . ",'" . VikRentCar::getMinutesLock(true) . "','" . $realback . "', ".(int)$neworderid.");";
														$dbo->setQuery($q);
														$dbo->execute();
														VikRentCar::sendAdminMail($admail.';;'.$useremail, JText::_('VRORDNOL'), $ftitle, $nowts, $custdata, $carinfo['name'], $ppickup, $prelease, $pricestr, $optstr, $isdue, JText::_('VRINATTESA'), $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $payment['name'], $strcouponeff);
														VikRentCar::sendCustMail($useremail, strip_tags($ftitle) . " " . JText::_('VRORDNOL'), $ftitle, $nowts, $custdata, $carinfo['name'], $ppickup, $prelease, $pricestr, $optstr, $isdue, $viklink, JText::_('VRINATTESA'), $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $neworderid, $strcouponeff, $arrayinfopdf);
														$mainframe = JFactory::getApplication();
														$mainframe->redirect(JRoute::_("index.php?option=com_vikrentcar&task=vieworder&sid=" . $sid . "&ts=" . $nowts . (!empty($pitemid) ? "&Itemid=" . $pitemid : "") . ($ptmpl == 'component' ? '&tmpl=component' : ''), false));
													}
												} else {
													VikError::raiseWarning('', JText::_('ERRSELECTPAYMENT'));
													$mainframe = JFactory::getApplication();
													$mainframe->redirect(JRoute::_("index.php?option=com_vikrentcar&priceid=" . $ppriceid . "&place=" . $pplace . "&returnplace=" . $preturnplace . "&carid=" . $pcar . "&days=" . $pdays . "&pickup=" . $ppickup . "&release=" . $prelease . "&task=oconfirm" . ($ptmpl == 'component' ? '&tmpl=component' : '') . (!empty($pitemid) ? "&Itemid=" . $pitemid : ""), false));
												}
											} else {
												$realback = VikRentCar::getHoursCarAvail() * 3600;
												$realback += $prelease;
												$q = "INSERT INTO `#__vikrentcar_busy` (`idcar`,`ritiro`,`consegna`,`realback`) VALUES(" . $dbo->quote($pcar) . ", " . $dbo->quote($ppickup) . ", " . $dbo->quote($prelease) . ",'" . $realback . "');";
												$dbo->setQuery($q);
												$dbo->execute();
												$lid = $dbo->insertid();
												$q = "INSERT INTO `#__vikrentcar_orders` (`idbusy`,`custdata`,`ts`,`status`,`idcar`,`days`,`ritiro`,`consegna`,`idtar`,`optionals`,`custmail`,`sid`,`idplace`,`idreturnplace`,`totpaid`,`ujid`,`hourly`,`coupon`,`order_total`,`locationvat`,`lang`,`country`,`phone`,`nominative`) VALUES('" . $lid . "', " . $dbo->quote($custdata) . ",'" . $nowts . "','confirmed'," . $dbo->quote($pcar) . "," . $dbo->quote($pdays) . "," . $dbo->quote($ppickup) . "," . $dbo->quote($prelease) . "," . $dbo->quote($pprtar) . "," . $dbo->quote($poptionals) . "," . $dbo->quote($useremail) . ",'" . $sid . "'," . $dbo->quote($pplace) . "," . $dbo->quote($preturnplace) . "," . $dbo->quote($isdue) . ",'".$currentUser->id."','".($usedhourly ? "1" : "0")."', ".$dbo->quote($strcouponeff).", '".$isdue."', ".(strlen($locationvat) > 0 ? "'".$locationvat."'" : "null").", ".$dbo->quote($langtag).", ".(!empty($usercountry) ? $dbo->quote($usercountry) : 'null').", ".$dbo->quote($phone_number).", ".$dbo->quote($nominative_str).");";
												$dbo->setQuery($q);
												$dbo->execute();
												$neworderid = $dbo->insertid();
												//Customer Booking
												$cpin->saveCustomerBooking($neworderid);
												//end Customer Booking
												if ($usedcoupon == true && $coupon['type'] == 2) {
													$q = "DELETE FROM `#__vikrentcar_coupons` WHERE `id`='".$coupon['id']."';";
													$dbo->setQuery($q);
													$dbo->execute();
												}
												VikRentCar::sendAdminMail($admail.';;'.$useremail, JText::_('VRORDNOL'), $ftitle, $nowts, $custdata, $carinfo['name'], $ppickup, $prelease, $pricestr, $optstr, $isdue, JText::_('VRCOMPLETED'), $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $strcouponeff);
												VikRentCar::sendCustMail($useremail, strip_tags($ftitle) . " " . JText::_('VRORDNOL'), $ftitle, $nowts, $custdata, $carinfo['name'], $ppickup, $prelease, $pricestr, $optstr, $isdue, $viklink, JText::_('VRCOMPLETED'), $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $neworderid, $strcouponeff, $arrayinfopdf);
												if (!empty($car_params['email'])) {
													VikRentCar::sendCustMail($car_params['email'], strip_tags($ftitle) . " " . JText::_('VRORDNOL'), $ftitle, $nowts, $custdata, $carinfo['name'], $ppickup, $prelease, $pricestr, $optstr, $isdue, $viklink, JText::_('VRCOMPLETED'), $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $neworderid, $strcouponeff, $arrayinfopdf);
												}
												echo VikRentCar::getFullFrontTitle();
												?>
												<p class="successmade"><?php echo JText::_('VRTHANKSONE'); ?></p>
												<br/>
												<p>&bull; <?php echo JText::_('VRTHANKSTWO'); ?> <a href="<?php echo $viklink; ?>"><?php echo JText::_('VRTHANKSTHREE'); ?></a></p>
												<?php
											}
										} else {
											showSelect(JText::_('VRCARBOOKEDBYOTHER'));
										}
									} else {
										showSelect(JText::_('VRCARISLOCKED'));
									}
								} else {
									showSelect(JText::_('VRINVALIDLOCATIONS'));
								}
							} else {
								showSelect(JText::_('VRINVALIDDATES'));
							}
						} else {
							showSelect(JText::_('VRINCONGRTOT'));
						}
					} else {
						showSelect(JText::_('VRINCONGRDATAREC'));
					}
				} else {
					showSelect(JText::_('VRINCONGRDATA'));
				}
			} else {
				showSelect(JText::_('VRINSUFDATA'));
			}
		} else {
			showSelect(JText::_('VRINVALIDTOKEN'));
		}
	}

	function vieworder() {
		$sid = VikRequest::getString('sid', '', 'request');
		$ts = VikRequest::getString('ts', '', 'request');
		if (!empty($sid) && !empty($ts)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `sid`=" . $dbo->quote($sid) . " AND `ts`=" . $dbo->quote($ts) . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$order = $dbo->loadAssocList();
				if ($order[0]['status'] == "confirmed") {
					VikRequest::setVar('view', 'confirmedorder');
					parent::display();
				} else {
					$q = "SELECT `units` FROM `#__vikrentcar_cars` WHERE `id`='" . $order[0]['idcar'] . "';";
					$dbo->setQuery($q);
					$dbo->execute();
					$cunits = $dbo->loadResult();
					$caravail = VikRentCar::carBookable($order[0]['idcar'], $cunits, $order[0]['ritiro'], $order[0]['consegna']);
					$today_time = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
					if ($today_time > $order[0]['ritiro']) {
						$caravail = false;
					}
					if ($caravail == true) {
						//SHOW PAYMENT FORM
						VikRequest::setVar('view', 'standbyorder');
						parent::display();
					} else {
						$q = "DELETE FROM `#__vikrentcar_orders` WHERE `id`='" . $order[0]['id'] . "' LIMIT 1;";
						$dbo->setQuery($q);
						$dbo->execute();
						if (!empty($order[0]['idbusy'])) {
							$q = "DELETE FROM `#__vikrentcar_busy` WHERE `id`='" . $order[0]['idbusy'] . "' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						if ($today_time > $order[0]['ritiro']) {
							showSelect("");
						} else {
							showSelect(JText::_('VRERRREPSEARCH'));
						}
					}
				}
			} else {
				showSelect(JText::_('VRORDERNOTFOUND'));
			}
		} else {
			showSelect(JText::_('VRINSUFDATA'));
		}
	}
	
	function cancelrequest() {
		$psid = VikRequest::getString('sid', '', 'request');
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$pitemid = VikRequest::getString('Itemid', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (!empty($psid) && !empty($pidorder)) {
			$q = sprintf("SELECT * FROM `#__vikrentcar_orders` WHERE `id`='%d' AND `sid`=".$dbo->quote($psid).";", $pidorder);
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$order = $dbo->loadAssocList();
				$pemail = VikRequest::getString('email', '', 'request');
				$preason = VikRequest::getString('reason', '', 'request');
				if (!empty($pemail) && !empty($preason)) {
					$to = VikRentCar::getAdminMail();
					$subject = JText::_('VRCCANCREQUESTEMAILSUBJ');
					$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
					$msg = JText::sprintf('VRCCANCREQUESTEMAILHEAD', $order[0]['id'], JURI::root().'index.php?option=com_vikrentcar&task=vieworder&sid='.$order[0]['sid'].'&ts='.$order[0]['ts'])."\n\n".$preason;
					$vrc_app = VikRentCar::getVrcApplication();
					$vrc_app->sendMail($pemail, $pemail, $to, $pemail, $subject, $msg, false);
					$mainframe->enqueueMessage(JText::_('VRCCANCREQUESTMAILSENT'));
					$mainframe->redirect(JRoute::_("index.php?option=com_vikrentcar&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts']."&Itemid=".$pitemid, false));
				} else {
					$mainframe->redirect(JRoute::_("index.php?option=com_vikrentcar&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts']."&Itemid=".$pitemid, false));
				}
			} else {
				$mainframe->redirect("index.php");
			}
		} else {
			$mainframe->redirect("index.php");
		}
	}

	function validatepin() {
		$ppin = VikRequest::getString('pin', '', 'request');
		$cpin = VikRentCar::getCPinIstance();
		$response = array();
		$customer = $cpin->getCustomerByPin($ppin);
		if (count($customer) > 0) {
			$response = $customer;
			$response['success'] = 1;
		}
		echo json_encode($response);
		exit;
	}
	
	function notifypayment() {
		$psid = VikRequest::getString('sid', '', 'request');
		$pts = VikRequest::getString('ts', '', 'request');
		$dbo = JFactory::getDBO();
		$nowdf = VikRentCar::getDateFormat();
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		if (strlen($psid) && strlen($pts)) {
			$admail = VikRentCar::getAdminMail();
			$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `ts`=" . $dbo->quote($pts) . " AND `sid`=" . $dbo->quote($psid) . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rows = $dbo->loadAssocList();
				//check if the language in use is the same as the one used during the checkout
				if (!empty($rows[0]['lang'])) {
					$lang = JFactory::getLanguage();
					if ($lang->getTag() != $rows[0]['lang']) {
						$lang->load('com_vikrentcar', JPATH_SITE, $rows[0]['lang'], true);
					}
				}
				//
				$vrc_tn = VikRentCar::getTranslator();
				if ($rows[0]['status'] != 'confirmed' && $rows[0]['status'] != 'cancelled') {
					$rows[0]['admin_email'] = $admail;
					$exppay = explode('=', $rows[0]['idpayment']);
					$payment = VikRentCar::getPayment($exppay[0], $vrc_tn);
					require_once(VRC_ADMIN_PATH . DS . "payments" . DS . $payment['file']);
					$obj = new vikRentCarPayment($rows[0], json_decode($payment['params'], true));
					$array_result = $obj->validatePayment();
					$newpaymentlog = date('c')."\n".$array_result['log']."\n----------\n".$rows[0]['paymentlog'];
					if ($array_result['verified'] == 1) {
						//valid payment
						$ritplace = (!empty($rows[0]['idplace']) ? VikRentCar::getPlaceName($rows[0]['idplace'], $vrc_tn) : "");
						$consegnaplace = (!empty($rows[0]['idreturnplace']) ? VikRentCar::getPlaceName($rows[0]['idreturnplace'], $vrc_tn) : "");
						$realback = VikRentCar::getHoursCarAvail() * 3600;
						$realback += $rows[0]['consegna'];
						//send mails
						$ftitle = VikRentCar::getFrontTitle($vrc_tn);
						$nowts = time();
						$carinfo = VikRentCar::getCarInfo($rows[0]['idcar'], $vrc_tn);
						$car_params = !empty($carinfo['params']) ? json_decode($carinfo['params'], true) : array();
						$viklink = JURI::root() . "index.php?option=com_vikrentcar&task=vieworder&sid=" . $psid . "&ts=" . $pts;
						$is_cust_cost = (!empty($rows[0]['cust_cost']) && $rows[0]['cust_cost'] > 0);
						if (!empty($rows[0]['idtar'])) {
							//vikrentcar 1.5
							if ($rows[0]['hourly'] == 1) {
								$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `id`='" . $rows[0]['idtar'] . "';";
							} else {
								$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='" . $rows[0]['idtar'] . "';";
							}
							//
							$dbo->setQuery($q);
							$dbo->execute();
							$tar = $dbo->loadAssocList();
						} elseif ($is_cust_cost) {
							//Custom Rate
							$tar = array(0 => array(
								'id' => -1,
								'idcar' => $rows[0]['idcar'],
								'days' => $rows[0]['days'],
								'idprice' => -1,
								'cost' => $rows[0]['cust_cost'],
								'attrdata' => '',
							));
						}
						//vikrentcar 1.5
						if ($rows[0]['hourly'] == 1) {
							foreach ($tar as $kt => $vt) {
								$tar[$kt]['days'] = 1;
							}
						}
						//
						//vikrentcar 1.6
						$checkhourscharges = 0;
						$hoursdiff = 0;
						$ppickup = $rows[0]['ritiro'];
						$prelease = $rows[0]['consegna'];
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
							$ret = VikRentCar::applyExtraHoursChargesCar($tar, $rows[0]['idcar'], $checkhourscharges, $daysdiff, false, true, true);
							$tar = $ret['return'];
							$calcdays = $ret['days'];
						}
						if ($checkhourscharges > 0 && $aehourschbasp == false && !$is_cust_cost) {
							$tar = VikRentCar::extraHoursSetPreviousFareCar($tar, $rows[0]['idcar'], $checkhourscharges, $daysdiff, true);
							$tar = VikRentCar::applySeasonsCar($tar, $rows[0]['ritiro'], $rows[0]['consegna'], $rows[0]['idplace']);
							$ret = VikRentCar::applyExtraHoursChargesCar($tar, $rows[0]['idcar'], $checkhourscharges, $daysdiff, true, true, true);
							$tar = $ret['return'];
							$calcdays = $ret['days'];
						} else {
							if (!$is_cust_cost) {
								//Seasonal prices only if not a custom rate
								$tar = VikRentCar::applySeasonsCar($tar, $rows[0]['ritiro'], $rows[0]['consegna'], $rows[0]['idplace']);
							}
						}
						//
						$costplusiva = $is_cust_cost ? $tar[0]['cost'] : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $rows[0]);
						$costminusiva = $is_cust_cost ? VikRentCar::sayCustCostMinusIva($tar[0]['cost'], $rows[0]['cust_idiva']) : VikRentCar::sayCostMinusIva($tar[0]['cost'], $tar[0]['idprice'], $rows[0]);
						$pricestr = ($is_cust_cost ? JText::_('VRCRENTCUSTRATEPLAN') : VikRentCar::getPriceName($tar[0]['idprice'], $vrc_tn)) . ": " . $costplusiva . (!empty($tar[0]['attrdata']) ? "\n" . VikRentCar::getPriceAttr($tar[0]['idprice'], $vrc_tn) . ": " . $tar[0]['attrdata'] : "");
						$isdue = $is_cust_cost ? $tar[0]['cost'] : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $rows[0]);
						$currencyname = VikRentCar::getCurrencyName();
						$optstr = "";
						$optarrtaxnet = array();
						if (!empty($rows[0]['optionals'])) {
							$stepo = explode(";", $rows[0]['optionals']);
							foreach ($stepo as $oo) {
								if (!empty($oo)) {
									$stept = explode(":", $oo);
									$q = "SELECT * FROM `#__vikrentcar_optionals` WHERE `id`=" . $dbo->quote($stept[0]) . ";";
									$dbo->setQuery($q);
									$dbo->execute();
									if ($dbo->getNumRows() == 1) {
										$actopt = $dbo->loadAssocList();
										$vrc_tn->translateContents($actopt, '#__vikrentcar_optionals');
										$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $rows[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
										$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $rows[0]['days']) : $actopt[0]['cost'];
										if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
											$realcost = $actopt[0]['maxprice'];
											if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
												$realcost = $actopt[0]['maxprice'] * $stept[1];
											}
										}
										$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva'], $rows[0]);
										$isdue += $tmpopr;
										$optnetprice = VikRentCar::sayOptionalsMinusIva($realcost, $actopt[0]['idiva'], $rows[0]);
										$optarrtaxnet[] = $optnetprice;
										$optstr .= ($stept[1] > 1 ? $stept[1] . " " : "") . $actopt[0]['name'] . ": " . $tmpopr . " " . $currencyname . "\n";
									}
								}
							}
						}
						//custom extra costs
						if (!empty($rows[0]['extracosts'])) {
							$cur_extra_costs = json_decode($rows[0]['extracosts'], true);
							foreach ($cur_extra_costs as $eck => $ecv) {
								$efee_cost = VikRentCar::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $rows[0]);
								$isdue += $efee_cost;
								$efee_cost_without = VikRentCar::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $rows[0]);
								$optarrtaxnet[] = $efee_cost_without;
								$optstr.=$ecv['name'].": ".$efee_cost." ".$currencyname."\n";
							}
						}
						//
						$maillocfee = "";
						$locfeewithouttax = 0;
						if (!empty($rows[0]['idplace']) && !empty($rows[0]['idreturnplace'])) {
							$locfee = VikRentCar::getLocFee($rows[0]['idplace'], $rows[0]['idreturnplace']);
							if ($locfee) {
								//VikRentCar 1.7 - Location fees overrides
								if (strlen($locfee['losoverride']) > 0) {
									$arrvaloverrides = array();
									$valovrparts = explode('_', $locfee['losoverride']);
									foreach ($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											$arrvaloverrides[(int)$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists((int)$rows[0]['days'], $arrvaloverrides)) {
										$locfee['cost'] = $arrvaloverrides[$rows[0]['days']];
									}
								}
								//end VikRentCar 1.7 - Location fees overrides
								$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $rows[0]['days']) : $locfee['cost'];
								$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $rows[0]);
								$isdue += $locfeewith;
								$locfeewithouttax = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $rows[0]);
								$maillocfee = $locfeewith;
							}
						}
						//VRC 1.9 - Out of Hours Fees
						$oohfee = VikRentCar::getOutOfHoursFees($rows[0]['idplace'], $rows[0]['idreturnplace'], $rows[0]['ritiro'], $rows[0]['consegna'], array('id' => $rows[0]['idcar']));
						$mailoohfee = "";
						$oohfeewithouttax = 0;
						if (count($oohfee) > 0) {
							$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
							$isdue += $oohfeewith;
							$oohfeewithouttax = VikRentCar::sayOohFeeMinusIva($oohfee['cost'], $oohfee['idiva']);
							$mailoohfee = $oohfeewith;
						}
						//
						//vikrentcar 1.6 coupon
						$usedcoupon = false;
						$origisdue = $isdue;
						if (strlen($rows[0]['coupon']) > 0) {
							$usedcoupon = true;
							$expcoupon = explode(";", $rows[0]['coupon']);
							$isdue = $isdue - $expcoupon[1];
						}
						//
						$shouldpay = $isdue;
						if ($payment['charge'] > 0.00) {
							if ($payment['ch_disc'] == 1) {
								//charge
								if ($payment['val_pcent'] == 1) {
									//fixed value
									$shouldpay += $payment['charge'];
								} else {
									//percent value
									$percent_to_pay = $shouldpay * $payment['charge'] / 100;
									$shouldpay += $percent_to_pay;
								}
							} else {
								//discount
								if ($payment['val_pcent'] == 1) {
									//fixed value
									$shouldpay -= $payment['charge'];
								} else {
									//percent value
									$percent_to_pay = $shouldpay * $payment['charge'] / 100;
									$shouldpay -= $percent_to_pay;
								}
							}
						}
						if (!VikRentCar::payTotal()) {
							$percentdeposit = VikRentCar::getAccPerCent();
							if ($percentdeposit > 0) {
								if (VikRentCar::getTypeDeposit() == "fixed") {
									$shouldpay = $percentdeposit;
								} else {
									$shouldpay = $shouldpay * $percentdeposit / 100;
								}
							}
						}
						//check if the total amount paid is the same as the total order
						if (array_key_exists('tot_paid', $array_result)) {
							$shouldpay = round($shouldpay, 2);
							$totreceived = round($array_result['tot_paid'], 2);
							if ($shouldpay != $totreceived) {
								//the amount paid is different than the total order
								//fares might have changed or the deposit might be different
								//Sending just an email to the admin that will have to check it
								$vrc_app = VikRentCar::getVrcApplication();
								$vrc_app->sendMail($admail, $admail, $admail, $admail, JText::_('VRCTOTPAYMENTINVALID'), JText::sprintf('VRCTOTPAYMENTINVALIDTXT', $rows[0]['id'], $totreceived." (".$array_result['tot_paid'].")", $shouldpay), false);
							}
						}
						//
						$arrayinfopdf = array('days' => $rows[0]['days'], 'tarminusiva' => $costminusiva, 'tartax' => ($costplusiva - $costminusiva), 'opttaxnet' => $optarrtaxnet, 'locfeenet' => $locfeewithouttax, 'oohfeenet' => $oohfeewithouttax, 'order_id' => $rows[0]['id'], 'tot_paid' => $array_result['tot_paid']);
						$q = "INSERT INTO `#__vikrentcar_busy` (`idcar`,`ritiro`,`consegna`,`realback`) VALUES('" . $rows[0]['idcar'] . "','" . $rows[0]['ritiro'] . "','" . $rows[0]['consegna'] . "','" . $realback . "');";
						$dbo->setQuery($q);
						$dbo->execute();
						$busynow = $dbo->insertid();
						$q = "UPDATE `#__vikrentcar_orders` SET `status`='confirmed', `idbusy`='" . $busynow . "'" . ($array_result['tot_paid'] ? ", `totpaid`=" . $dbo->quote($array_result['tot_paid']) : "") . (!empty($array_result['log']) ? ", `paymentlog`=".$dbo->quote($newpaymentlog) : "") . " WHERE `id`='" . $rows[0]['id'] . "';";
						$dbo->setQuery($q);
						$dbo->execute();
						//VRC 1.10 : unlock car for other imminent bookings
						$q = "DELETE FROM `#__vikrentcar_tmplock` WHERE `idorder`=" . intval($rows[0]['id']) . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						//
						VikRentCar::sendAdminMail($admail.';;'.$rows[0]['custmail'], JText::_('VRRENTALORD'), $ftitle, $nowts, $rows[0]['custdata'], $carinfo['name'], $rows[0]['ritiro'], $rows[0]['consegna'], $pricestr, $optstr, $isdue, JText::_('VRCOMPLETED'), $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $payment['name'], $rows[0]['coupon']);
						VikRentCar::sendCustMail($rows[0]['custmail'], strip_tags($ftitle) . " " . JText::_('VRRENTALORD'), $ftitle, $rows[0]['ts'], $rows[0]['custdata'], $carinfo['name'], $rows[0]['ritiro'], $rows[0]['consegna'], $pricestr, $optstr, $isdue, $viklink, JText::_('VRCOMPLETED'), $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $rows[0]['id'], $rows[0]['coupon'], $arrayinfopdf);
						if (!empty($car_params['email'])) {
							VikRentCar::sendCustMail($car_params['email'], strip_tags($ftitle) . " " . JText::_('VRRENTALORD'), $ftitle, $rows[0]['ts'], $rows[0]['custdata'], $carinfo['name'], $rows[0]['ritiro'], $rows[0]['consegna'], $pricestr, $optstr, $isdue, $viklink, JText::_('VRCOMPLETED'), $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $rows[0]['id'], $rows[0]['coupon'], $arrayinfopdf);
						}
						if (method_exists($obj, 'afterValidation')) {
							$obj->afterValidation(1);
						}
					} else {
						if (!array_key_exists('skip_email', $array_result) || $array_result['skip_email'] != 1) {
							$vrc_app = VikRentCar::getVrcApplication();
							$vrc_app->sendMail($admail, $admail, $admail, $admail, JText::_('VRPAYMENTNOTVER'), JText::_('VRSERVRESP') . ":\n\n" . $array_result['log'], false);
						}
						if (!empty($array_result['log'])) {
							$q = "UPDATE `#__vikrentcar_orders` SET `paymentlog`=".$dbo->quote($newpaymentlog)." WHERE `id`='" . $rows[0]['id'] . "';";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						if (method_exists($obj, 'afterValidation')) {
							$obj->afterValidation(0);
						}
					}
				}
			}
		}
		exit;
		return true;
	}
	
	function ajaxlocopentime() {
		$dbo = JFactory::getDBO();
		$nowtf = VikRentCar::getTimeFormat();
		$pidloc = VikRequest::getInt('idloc', '', 'request');
		$ppickdrop = VikRequest::getString('pickdrop', '', 'request');
		$ret = array();
		$location = array();
		$q = "SELECT `id`,`opentime`,`defaulttime`,`wopening` FROM `#__vikrentcar_places` WHERE `id`=".$pidloc.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$location = $dbo->loadAssoc();
		}
		$opentime = isset($location['opentime']) ? $location['opentime'] : '';
		if (strlen($opentime) > 0) {
			//load location time
			$parts = explode("-", $opentime);
			$opent = VikRentCar::getHoursMinutes($parts[0]);
			$closet = VikRentCar::getHoursMinutes($parts[1]);
			if ($opent != $closet) {
				$i = $opent[0];
				$imin = $opent[1];
				$j = $closet[0];
			} else {
				$i = 0;
				$imin = 0;
				$j = 23;
			}
		} else {
			//load global time
			$timeopst = VikRentCar::getTimeOpenStore();
			if (is_array($timeopst) && $timeopst[0] != $timeopst[1]) {
				$opent = VikRentCar::getHoursMinutes($timeopst[0]);
				$closet = VikRentCar::getHoursMinutes($timeopst[1]);
				$i = $opent[0];
				$imin = $opent[1];
				$j = $closet[0];
			} else {
				$i = 0;
				$imin = 0;
				$j = 23;
			}
		}
		$hours = "";
		//VRC 1.9
		$pickhdeftime = isset($location['defaulttime']) && !empty($location['defaulttime']) ? ((int)$location['defaulttime'] / 3600) : '';
		if (!($i < $j)) {
			while (intval($i) != (int)$j) {
				$sayi = $i < 10 ? "0".$i : $i;
				if ($nowtf != 'H:i') {
					$ampm = $i < 12 ? ' am' : ' pm';
					$ampmh = $i > 12 ? ($i - 12) : $i;
					$sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
				} else {
					$sayh = $sayi;
				}
				$hours .= "<option value=\"" . (int)$i . "\"".($pickhdeftime == (int)$i ? ' selected="selected"' : '').">" . $sayh . "</option>\n";
				$i++;
				$i = $i > 23 ? 0 : $i;
			}
			$sayi = $i < 10 ? "0".$i : $i;
			if ($nowtf != 'H:i') {
				$ampm = $i < 12 ? ' am' : ' pm';
				$ampmh = $i > 12 ? ($i - 12) : $i;
				$sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
			} else {
				$sayh = $sayi;
			}
			$hours .= "<option value=\"" . (int)$i . "\">" . $sayh . "</option>\n";
		} else {
			while ((int)$i <= $j) {
				$sayi = $i < 10 ? "0".$i : $i;
				if ($nowtf != 'H:i') {
					$ampm = $i < 12 ? ' am' : ' pm';
					$ampmh = $i > 12 ? ($i - 12) : $i;
					$sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
				} else {
					$sayh = $sayi;
				}
				$hours .= "<option value=\"" . (int)$i . "\"".($pickhdeftime == (int)$i ? ' selected="selected"' : '').">" . $sayh . "</option>\n";
				$i++;
			}
		}
		//
		$minutes = "";
		for ($i = 0; $i < 60; $i += 15) {
			if ($i < 10) {
				$i = "0" . $i;
			} else {
				$i = $i;
			}
			$minutes .= "<option value=\"" . (int)$i . "\"".((int)$i == $imin ? " selected=\"selected\"" : "").">" . $i . "</option>\n";
		}
		$suffix = $ppickdrop == 'pickup' ? 'pickup' : 'release';
		
		$ret['hours'] = '<select name="'.$suffix.'h">'.$hours.'</select>';
		$ret['minutes'] = '<select name="'.$suffix.'m">'.$minutes.'</select>';

		// VRC 1.12 - opening time overrides for week days
		$wopening = array();
		if (isset($location['wopening']) && !empty($location['wopening'])) {
			$wopening = json_decode($location['wopening'], true);
			$wopening = !is_array($wopening) ? array() : $wopening;
		}
		$ret['wopening'] = $wopening;
		//
		
		echo json_encode($ret);
		exit;
	}

	function currencyconverter() {
		$session = JFactory::getSession();
		$pprices = VikRequest::getVar('prices', array(0));
		$pfromsymbol = VikRequest::getString('fromsymbol', '', 'request');
		$ptocurrency = VikRequest::getString('tocurrency', '', 'request');
		$pfromcurrency = VikRequest::getString('fromcurrency', '', 'request');
		$default_cur = !empty($pfromcurrency) ? $pfromcurrency : VikRentCar::getCurrencyName();
		$response = array();
		if (!empty($default_cur) && !empty($pprices) && count($pprices) > 0 && !empty($ptocurrency)) {
			require_once(VRC_SITE_PATH . DS . "helpers" . DS ."currencyconverter.php");
			if ($default_cur != $ptocurrency) {
				$format = VikRentCar::getNumberFormatData();
				$converter = new vrcCurrencyConverter($default_cur, $ptocurrency, $pprices, explode(':', $format));
				$exchanged = $converter->convert();
				if (count($exchanged) > 0) {
					$response = $exchanged;
					$session->set('vrcLastCurrency', $ptocurrency);
				} else {
					$response['error'] = JText::_('VRCERRCURCONVINVALIDDATA');
				}
			} else {
				$session->set('vrcLastCurrency', $ptocurrency);
				foreach ($pprices as $i => $price) {
					$response[$i]['symbol'] = $pfromsymbol;
					$response[$i]['price'] = $price;
				}
			}
		} else {
			$response['error'] = JText::_('VRCERRCURCONVNODATA');
		}
		if (array_key_exists('error', $response)) {
			$session->set('vrcLastCurrency', $ptocurrency);
		}
		echo json_encode($response);
		exit;
	}

	function ical() {
		$dbo = JFactory::getDBO();
		$evendtype = VikRentCar::getIcalEndType();
		$pcar = VikRequest::getInt('car', '', 'request');
		$pkey = VikRequest::getString('key', '', 'request');
		$icsname = date('Y-m-d_H_i_s');
		$icscontent = "BEGIN:VCALENDAR\n";
		$icscontent .= "VERSION:2.0\n";
		$icscontent .= "PRODID:-//e4j//VikRentCar//EN\n";
		$icscontent .= "CALSCALE:GREGORIAN\n";
		if (!empty($pkey) && $pkey == VikRentCar::getIcalSecretKey()) {
			$icsname .= '_'.($pcar > 0 ? $pcar.'_' : '').$pkey;
			$q = "SELECT `o`.*,`lp`.`name` AS `pickup_location_name`,`ld`.`name` AS `dropoff_location_name` FROM `#__vikrentcar_orders` AS `o` ".
				"LEFT JOIN `#__vikrentcar_places` `lp` ON `o`.`idplace`=`lp`.`id` ".
				"LEFT JOIN `#__vikrentcar_places` `ld` ON `o`.`idreturnplace`=`ld`.`id` WHERE `o`.`status`='confirmed' AND `o`.`ritiro` > ".time().($pcar > 0 ? " AND `o`.`idcar`=".$pcar : "")." ORDER BY `o`.`ritiro` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rows = $dbo->loadAssocList();
				$icalstr = "";
				foreach ($rows as $r) {
					$uri = JURI::root().'index.php?option=com_vikrentcar&task=vieworder&sid='.$r['sid'].'&ts='.$r['ts'];
					$pickloc = VikRentCar::getPlaceName($r['idplace']);
					$car = VikRentCar::getCarInfo($r['idcar']);
					//$custdata = preg_replace('/\s+/', ' ', trim($r['custdata']));
					//$description = $car['name']."\\n".$r['custdata'];
					$description = $car['name']."\\n".str_replace("\n", "\\n", trim($r['custdata']));
					$icalstr .= "BEGIN:VEVENT\n";
					if ($evendtype == 'drop') {
						// Event End Date set to Drop off Date
						$icalstr .= "DTEND:".date('Ymd\THis\Z', $r['consegna'])."\n";
					} else {
						// Event End Date set to Pick up Date
						$icalstr .= "DTEND:".date('Ymd\THis\Z', $r['ritiro'])."\n";
					}
					//
					$icalstr .= "UID:".$r['id'].'_'.$r['sid']."\n";
					$icalstr .= "DTSTAMP:".date('Ymd\THis\Z')."\n";
					$icalstr .= "LOCATION:".preg_replace('/([\,;])/','\\\$1', $pickloc)."\n";
					$icalstr .= ((strlen($description) > 0 ) ? "DESCRIPTION:".preg_replace('/([\,;])/','\\\$1', $description)."\n" : "");
					$icalstr .= "URL;VALUE=URI:".preg_replace('/([\,;])/','\\\$1', $uri)."\n";
					//$icalstr .= "SUMMARY:".JText::sprintf('VRCICSEXPSUMMARY', date('H:i', $r['ritiro']))."\n";
					$icalstr .= "SUMMARY:".JText::sprintf('VRCICSEXPSUMMARY', $r['pickup_location_name'])."\n";
					$icalstr .= "DTSTART:".date('Ymd\THis\Z', $r['ritiro'])."\n";
					$icalstr .= "END:VEVENT\n";
				}
				$icscontent .= $icalstr;
			}
		}
		$icscontent .= "END:VCALENDAR\n";
		header('Content-type: text/calendar; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $icsname.'.ics');
		echo $icscontent;

		exit;
	}

	function reqinfo() {
		$pcarid = VikRequest::getInt('carid', '', 'request');
		$pitemid = VikRequest::getInt('Itemid', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$vrc_app = VikRentCar::getVrcApplication();
		if (!empty($pcarid)) {
			$q = "SELECT `id`,`name`,`params` FROM `#__vikrentcar_cars` WHERE `id`=".(int)$pcarid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$car = $dbo->loadAssocList();
				$car_params = !empty($car[0]['params']) ? json_decode($car[0]['params'], true) : array();
				$goto = JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid='.$car[0]['id'].'&Itemid='.$pitemid, false);
				$preqname = VikRequest::getString('reqname', '', 'request');
				$preqemail = VikRequest::getString('reqemail', '', 'request');
				$preqmess = VikRequest::getString('reqmess', '', 'request');
				if (!empty($preqemail) && !empty($preqmess)) {
					// captcha verification
					if ($vrc_app->isCaptcha() && !$vrc_app->reCaptcha('check')) {
						VikError::raiseWarning('', 'Invalid Captcha');
						$mainframe->redirect($goto);
						exit;
					}
					//
					$to = VikRentCar::getAdminMail();
					if (strpos($to, ',') !== false) {
						$all_recipients = explode(',', $to);
						foreach ($all_recipients as $k => $v) {
							if (empty($v)) {
								unset($all_recipients[$k]);
							}
						}
						if (count($all_recipients) > 0) {
							$to = $all_recipients;
						}
					}
					if (!empty($car_params['email'])) {
						if (is_array($to)) {
							array_push($to, $car_params['email']);
						} else {
							$to = array($to, $car_params['email']);
						}
					}
					$subject = JText::sprintf('VRCCARREQINFOSUBJ', $car[0]['name']);
					$msg = JText::_('VRCCARREQINFONAME').": ".$preqname."\n\n".JText::_('VRCCARREQINFOEMAIL').": ".$preqemail."\n\n".JText::_('VRCCARREQINFOMESS').":\n\n".$preqmess;
					$vrc_app->sendMail($adsendermail, $adsendermail, $to, $preqemail, $subject, $msg, false);
					$mainframe->enqueueMessage(JText::_('VRCCARREQINFOSENTOK'));
					$mainframe->redirect($goto);
				} else {
					VikError::raiseWarning('', JText::_('VRCCARREQINFOMISSFIELD'));
					$mainframe->redirect($goto);
				}
			} else {
				$mainframe->redirect("index.php");
			}
		} else {
			$mainframe->redirect("index.php");
		}
	}
	
}
