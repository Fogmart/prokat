<?php

defined('_JEXEC') or die('Restricted access');

if (!defined('VRC_ADMIN_URI')) {
	//this library could be loaded by modules, so we need to load at least the Defines Adapter file.
	include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "defines.php");
}

if (!function_exists('showSelect')) {
	function showSelect($err) {
		include(VRC_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'error_form.php');
	}
}

class VikRentCar {

	public static function addJoomlaUser($name, $username, $email, $password) {
		//new method
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_users');
		$user = new JUser;
		$data = array();
		//Get the default new user group, Registered if not specified.
		$system = $params->get('new_usertype', 2);
		$data['groups'] = array();
		$data['groups'][] = $system;
		$data['name'] = $name;
		$data['username'] = $username;
		$data['email'] = self::getVrcApplication()->emailToPunycode($email);
		$data['password'] = $password;
		$data['password2'] = $password;
		$data['sendEmail'] = 0; //should the user receive system mails?
		//$data['block'] = 0;
		if (!$user->bind($data)) {
			VikError::raiseWarning('', JText::_($user->getError()));
			return false;
		}
		if (!$user->save()) {
			VikError::raiseWarning('', JText::_($user->getError()));
			return false;
		}
		return $user->id;
	}
	
	public static function userIsLogged() {
		$user = JFactory::getUser();
		if ($user->guest) {
			return false;
		}
		return true;
	}

	public static function prepareViewContent() {
		$menu = JFactory::getApplication()->getMenu()->getActive();
		//Joomla 3.7.x - property params is now protected, before it was public
		$menuParams = null;
		if (method_exists($menu, 'getParams')) {
			$menuParams = $menu->getParams();
		} elseif (isset($menu->params)) {
			//Until Joomla 3.6.5
			$menuParams = $menu->params;
		}
		//
		if ($menuParams !== null) {
			$document = JFactory::getDocument();
			if ( intval($menuParams->get('show_page_heading')) == 1 && strlen($menuParams->get('page_heading')) ) {
				echo '<div class="page-header'.(strlen($clazz = $menuParams->get('pageclass_sfx')) ? ' '.$clazz : '' ).'"><h1>'.$menuParams->get('page_heading').'</h1></div>';
			}
			if ( strlen($menuParams->get('menu-meta_description')) ) {
				$document->setDescription($menuParams->get('menu-meta_description'));
			}
			if ( strlen($menuParams->get('menu-meta_keywords')) ) {
				$document->setMetadata('keywords', $menuParams->get('menu-meta_keywords'));
			}
			if ( strlen($menuParams->get('robots')) ) {
				$document->setMetadata('robots', $menuParams->get('robots'));
			}
		}
	}

	public static function isFontAwesomeEnabled($skipsession = false) {
		if (!$skipsession) {
			$session = JFactory::getSession();
			$s = $session->get('vrcfaw', '');
			if (strlen($s)) {
				return ((int)$s == 1);
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='usefa';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			if (!$skipsession) {
				$session->set('vrcfaw', $s);
			}
			return ((int)$s == 1);
		}
		$q = "INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('usefa', '1');";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$skipsession) {
			$session->set('vrcfaw', '1');
		}
		return true;
	}

	public static function loadFontAwesome($force_load = false) {
		if (!self::isFontAwesomeEnabled() && !$force_load) {
			return false;
		}
		$document = JFactory::getDocument();
		$document->addStyleSheet(VRC_ADMIN_URI.'resources/fontawesome-all.min.css');

		return true;
	}

	public static function allowMultiLanguage($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='multilang';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return intval($s[0]['setting']) == 1 ? true : false;
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vrcMultiLang', '');
			if (!empty($sval)) {
				return intval($sval) == 1 ? true : false;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='multilang';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vrcMultiLang', $s[0]['setting']);
				return intval($s[0]['setting']) == 1 ? true : false;
			}
		}
	}

	public static function getTranslator() {
		if (!class_exists('VikRentCarTranslator')) {
			require_once(VRC_SITE_PATH . DS . "helpers" . DS . "translator.php");
		}
		return new VikRentCarTranslator();
	}

	public static function getCPinIstance() {
		if (!class_exists('VikRentCarCustomersPin')) {
			require_once(VRC_SITE_PATH . DS . "helpers" . DS . "cpin.php");
		}
		return new VikRentCarCustomersPin();
	}

	public static function getFirstCustDataField($custdata) {
		$first_field = '----';
		if (empty($custdata)) {
			return $first_field;
		}
		$parts = explode("\n", $custdata);
		foreach ($parts as $part) {
			if (!empty($part)) {
				$field = explode(':', trim($part));
				if (!empty($field[1])) {
					return trim($field[1]);
				}
			}
		}
		return $first_field;
	}
	
	public static function getTheme() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='theme';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s=$dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getGoogleMapsKey() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='gmapskey' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			return $dbo->loadResult();
		}
		return '';
	}
	
	public static function getFooterOrdMail() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentcar_texts` WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		return $ft[0]['setting'];
	}
	
	public static function requireLogin() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='requirelogin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}

	public static function todayBookings() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='todaybookings';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}
	
	public static function couponsEnabled() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='enablecoupons';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}

	public static function customersPinEnabled() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='enablepin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}
	
	public static function applyExtraHoursChargesBasp() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='ehourschbasp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		//true is before special prices, false is after
		return intval($s[0]['setting']) == 1 ? true : false;
	}

	public static function getDamageShowType() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='damageshowtype';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']);
	}
	
	public static function loadJquery($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='loadjquery';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return intval($s[0]['setting']) == 1 ? true : false;
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('loadJquery', '');
			if (!empty($sval)) {
				return intval($sval) == 1 ? true : false;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='loadjquery';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('loadJquery', $s[0]['setting']);
				return intval($s[0]['setting']) == 1 ? true : false;
			}
		}
	}
	
	public static function calendarType($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='calendar';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('calendarType', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='calendar';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('calendarType', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function setDropDatePlus($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='setdropdplus';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('setDropDatePlus', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='setdropdplus';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('setDropDatePlus', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function getSiteLogo() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='sitelogo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}
	
	public static function numCalendars() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='numcalendars';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getThumbnailsWidth() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='thumbswidth';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']);
	}

	public static function getNextInvoiceNumber () {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='invoiceinum';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s=$dbo->loadAssocList();
		return (intval($s[0]['setting']) + 1);
	}
	
	public static function getInvoiceNumberSuffix () {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='invoicesuffix';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s=$dbo->loadAssocList();
		return $s[0]['setting'];
	}
	
	public static function getInvoiceCompanyInfo () {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='invcompanyinfo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s=$dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getIcalSecretKey() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='icalkey';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s=$dbo->loadAssocList();
		return $s[0]['setting'];
	}
	
	public static function showPartlyReserved() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='showpartlyreserved';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}

	public static function getDisclaimer($vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentcar_texts` WHERE `param`='disclaimer';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (is_object($vrc_tn)) {
			$vrc_tn->translateContents($ft, '#__vikrentcar_texts');
		}
		return $ft[0]['setting'];
	}

	public static function showFooter() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='showfooter';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$s = $dbo->loadAssocList();
			return (intval($s[0]['setting']) == 1 ? true : false);
		} else {
			return false;
		}
	}

	public static function getSendEmailWhen() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='emailsendwhen' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			return intval($dbo->loadResult()) > 1 ? 2 : 1;
		}
		return 1;
	}

	public static function getIcalEndType() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='icalendtype' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			return $dbo->loadResult() == 'drop' ? 'drop' : 'pick';
		}
		return 'pick';
	}
	
	public static function loadPreviousUserData($uid) {
		$ret = array();
		$ret['customfields'] = array();
		$dbo = JFactory::getDBO();
		if (!empty($uid) && intval($uid) > 0) {
			$q = "SELECT * FROM `#__vikrentcar_usersdata` WHERE `ujid`='".intval($uid)."';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$olddata = $dbo->loadAssocList();
				return json_decode($olddata[0]['data'], true);
			}
		}
		return $ret;
	}
	
	public static function formatLocationClosingDays($clostr) {
		$ret = array();
		$cur_time = time();
		$x = explode(",", $clostr);
		foreach ($x as $y) {
			if (strlen(trim($y)) > 0) {
				$parts = explode("-", trim($y));
				$date_ts = mktime(0, 0, 0, (int)$parts[1], (int)str_replace(':w', '', $parts[2]), (int)$parts[0]);
				$date = date('Y-n-j', $date_ts);
				if (strlen($date) > 0 && $date_ts >= $cur_time) {
					$ret[] = '"'.$date.'"';
				}
				if (strpos($parts[2], ':w') !== false) {
					$info_ts = getdate($date_ts);
					$ret[] = '"'.$info_ts['wday'].'"';
				}
			}
		}
		return $ret;
	}
	
	public static function getPriceName($idp, $vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_prices` WHERE `id`='" . $idp . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$n = $dbo->loadAssocList();
			if (is_object($vrc_tn)) {
				$vrc_tn->translateContents($n, '#__vikrentcar_prices');
			}
			return $n[0]['name'];
		}
		return "";
	}

	public static function getPriceAttr($idp, $vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`attr` FROM `#__vikrentcar_prices` WHERE `id`='" . $idp . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$n = $dbo->loadAssocList();
			if (is_object($vrc_tn)) {
				$vrc_tn->translateContents($n, '#__vikrentcar_prices');
			}
			return $n[0]['attr'];
		}
		return "";
	}

	public static function getAliq($idal) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `aliq` FROM `#__vikrentcar_iva` WHERE `id`='" . $idal . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			return 0;
		}
		$n = $dbo->loadAssocList();
		return $n[0]['aliq'];
	}

	public static function getTimeOpenStore($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='timeopenstore';";
			$dbo->setQuery($q);
			$dbo->execute();
			$n = $dbo->loadAssocList();
			if (empty ($n[0]['setting']) && $n[0]['setting'] != "0") {
				return false;
			} else {
				$x = explode("-", $n[0]['setting']);
				if (!empty ($x[1]) && $x[1] != "0") {
					return $x;
				}
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getTimeOpenStore', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='timeopenstore';";
				$dbo->setQuery($q);
				$dbo->execute();
				$n = $dbo->loadAssocList();
				if (empty ($n[0]['setting']) && $n[0]['setting'] != "0") {
					return false;
				} else {
					$x = explode("-", $n[0]['setting']);
					if (!empty ($x[1]) && $x[1] != "0") {
						$session->set('getTimeOpenStore', $x);
						return $x;
					}
				}
			}
		}
		return false;
	}

	public static function getHoursMinutes($secs) {
		if ($secs >= 3600) {
			$op = $secs / 3600;
			$hours = floor($op);
			$less = $hours * 3600;
			$newsec = $secs - $less;
			$optwo = $newsec / 60;
			$minutes = floor($optwo);
		} else {
			$hours = "0";
			$optwo = $secs / 60;
			$minutes = floor($optwo);
		}
		$x[] = $hours;
		$x[] = $minutes;
		return $x;
	}

	public static function showPlacesFront($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='placesfront';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$s = $dbo->loadAssocList();
				return (intval($s[0]['setting']) == 1 ? true : false);
			} else {
				return false;
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('showPlacesFront', '');
			if (strlen($sval) > 0) {
				return (intval($sval) == 1 ? true : false);
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='placesfront';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$s = $dbo->loadAssocList();
					$session->set('showPlacesFront', $s[0]['setting']);
					return (intval($s[0]['setting']) == 1 ? true : false);
				} else {
					return false;
				}
			}
		}
	}

	public static function showCategoriesFront($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='showcategories';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$s = $dbo->loadAssocList();
				return (intval($s[0]['setting']) == 1 ? true : false);
			} else {
				return false;
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('showCategoriesFront', '');
			if (strlen($sval) > 0) {
				return (intval($sval) == 1 ? true : false);
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='showcategories';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$s = $dbo->loadAssocList();
					$session->set('showCategoriesFront', $s[0]['setting']);
					return (intval($s[0]['setting']) == 1 ? true : false);
				} else {
					return false;
				}
			}
		}
	}

	public static function useCharatsFilter($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='charatsfilter';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$s = $dbo->loadAssocList();
				return (intval($s[0]['setting']) == 1 ? true : false);
			} else {
				return false;
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('useCharatsFilter', '');
			if (strlen($sval) > 0) {
				return (intval($sval) == 1 ? true : false);
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='charatsfilter';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$s = $dbo->loadAssocList();
					$session->set('useCharatsFilter', $s[0]['setting']);
					return (intval($s[0]['setting']) == 1 ? true : false);
				} else {
					return false;
				}
			}
		}
	}

	public static function allowRent() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='allowrent';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$s = $dbo->loadAssocList();
			return (intval($s[0]['setting']) == 1 ? true : false);
		} else {
			return false;
		}
	}

	public static function getDisabledRentMsg($vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentcar_texts` WHERE `param`='disabledrentmsg';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		if (is_object($vrc_tn)) {
			$vrc_tn->translateContents($s, '#__vikrentcar_texts');
		}
		return $s[0]['setting'];
	}

	public static function getDateFormat($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='dateformat';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getDateFormat', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='dateformat';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('getDateFormat', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}

	public static function getTimeFormat($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='timeformat';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getTimeFormat', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='timeformat';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('getTimeFormat', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}

	public static function getHoursMoreRb() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='hoursmorerentback';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getHoursCarAvail() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='hoursmorecaravail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getFrontTitle($vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentcar_texts` WHERE `param`='fronttitle';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (is_object($vrc_tn)) {
			$vrc_tn->translateContents($ft, '#__vikrentcar_texts');
		}
		return $ft[0]['setting'];
	}

	public static function getFrontTitleTag() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='fronttitletag';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		return $ft[0]['setting'];
	}

	public static function getFrontTitleTagClass() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='fronttitletagclass';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		return $ft[0]['setting'];
	}

	public static function getCurrencyName() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		return $ft[0]['setting'];
	}

	public static function getCurrencySymb($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='currencysymb';";
			$dbo->setQuery($q);
			$dbo->execute();
			$ft = $dbo->loadAssocList();
			return $ft[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getCurrencySymb', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='currencysymb';";
				$dbo->setQuery($q);
				$dbo->execute();
				$ft = $dbo->loadAssocList();
				$session->set('getCurrencySymb', $ft[0]['setting']);
				return $ft[0]['setting'];
			}
		}
	}
	
	public static function getNumberFormatData($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='numberformat';";
			$dbo->setQuery($q);
			$dbo->execute();
			$ft = $dbo->loadAssocList();
			return $ft[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getNumberFormatData', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='numberformat';";
				$dbo->setQuery($q);
				$dbo->execute();
				$ft = $dbo->loadAssocList();
				$session->set('getNumberFormatData', $ft[0]['setting']);
				return $ft[0]['setting'];
			}
		}
	}
	
	public static function numberFormat($num, $skipsession = false) {
		$formatvals = self::getNumberFormatData($skipsession);
		$formatparts = explode(':', $formatvals);
		return number_format($num, (int)$formatparts[0], $formatparts[1], $formatparts[2]);
	}
	
	public static function getCurrencyCodePp() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='currencycodepp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		return $ft[0]['setting'];
	}

	public static function getSubmitName($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `id`,`setting` FROM `#__vikrentcar_texts` WHERE `param`='searchbtnval';";
			$dbo->setQuery($q);
			$dbo->execute();
			$ft = $dbo->loadAssocList();
			if (!empty ($ft[0]['setting'])) {
				return $ft[0]['setting'];
			} else {
				return "";
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getSubmitName', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `id`,`setting` FROM `#__vikrentcar_texts` WHERE `param`='searchbtnval';";
				$dbo->setQuery($q);
				$dbo->execute();
				$ft = $dbo->loadAssocList();
				if (!empty ($ft[0]['setting'])) {
					$session->set('getSubmitName', $ft[0]['setting']);
					return $ft[0]['setting'];
				} else {
					return JText::_('VRCSEARCHBUTTON');
				}
			}
		}
	}

	public static function getSubmitClass($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='searchbtnclass';";
			$dbo->setQuery($q);
			$dbo->execute();
			$ft = $dbo->loadAssocList();
			return $ft[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getSubmitClass', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='searchbtnclass';";
				$dbo->setQuery($q);
				$dbo->execute();
				$ft = $dbo->loadAssocList();
				$session->set('getSubmitClass', $ft[0]['setting']);
				return $ft[0]['setting'];
			}
		}
	}

	public static function getIntroMain($vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentcar_texts` WHERE `param`='intromain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (is_object($vrc_tn)) {
			$vrc_tn->translateContents($ft, '#__vikrentcar_texts');
		}
		return $ft[0]['setting'];
	}

	public static function getClosingMain($vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentcar_texts` WHERE `param`='closingmain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (is_object($vrc_tn)) {
			$vrc_tn->translateContents($ft, '#__vikrentcar_texts');
		}
		return $ft[0]['setting'];
	}

	public static function getFullFrontTitle($vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentcar_texts` WHERE `param`='fronttitle';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (is_object($vrc_tn)) {
			$vrc_tn->translateContents($ft, '#__vikrentcar_texts');
		}
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='fronttitletag';";
		$dbo->setQuery($q);
		$dbo->execute();
		$fttag = $dbo->loadAssocList();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='fronttitletagclass';";
		$dbo->setQuery($q);
		$dbo->execute();
		$fttagclass = $dbo->loadAssocList();
		if (empty ($ft[0]['setting'])) {
			return "";
		} else {
			if (empty ($fttag[0]['setting'])) {
				return $ft[0]['setting'] . "<br/>\n";
			} else {
				$tag = str_replace("<", "", $fttag[0]['setting']);
				$tag = str_replace(">", "", $tag);
				$tag = str_replace("/", "", $tag);
				$tag = trim($tag);
				return "<" . $tag . "" . (!empty ($fttagclass) ? " class=\"" . $fttagclass[0]['setting'] . "\"" : "") . ">" . $ft[0]['setting'] . "</" . $tag . ">";
			}
		}
	}

	public static function dateIsValid($date) {
		$df = self::getDateFormat();
		if (strlen($date) != "10") {
			return false;
		}
		$x = explode("/", $date);
		if ($df == "%d/%m/%Y") {
			if (strlen($x[0]) != "2" || $x[0] > 31 || strlen($x[1]) != "2" || $x[1] > 12 || strlen($x[2]) != "4") {
				return false;
			}
		} elseif ($df == "%m/%d/%Y") {
			if (strlen($x[1]) != "2" || $x[1] > 31 || strlen($x[0]) != "2" || $x[0] > 12 || strlen($x[2]) != "4") {
				return false;
			}
		} else {
			if (strlen($x[2]) != "2" || $x[2] > 31 || strlen($x[1]) != "2" || $x[1] > 12 || strlen($x[0]) != "4") {
				return false;
			}
		}
		return true;
	}

	public static function sayDateFormat() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		if ($s[0]['setting'] == "%d/%m/%Y") {
			return JText::_('VRCONFIGONETWELVE');
		} elseif ($s[0]['setting'] == "%m/%d/%Y") {
			return JText::_('VRCONFIGUSDATEFORMAT');
		} else {
			return JText::_('VRCONFIGONETENTHREE');
		}
	}

	public static function getDateTimestamp($date, $h, $m) {
		$df = self::getDateFormat();
		$x = explode("/", $date);
		if ($df == "%d/%m/%Y") {
			$dts = strtotime($x[1] . "/" . $x[0] . "/" . $x[2]);
		} elseif ($df == "%m/%d/%Y") {
			$dts = strtotime($x[0] . "/" . $x[1] . "/" . $x[2]);
		} else {
			$dts = strtotime($x[1] . "/" . $x[2] . "/" . $x[0]);
		}
		$hts = 3600 * $h;
		$mts = 60 * $m;
		return ($dts + $hts + $mts);
	}

	public static function ivaInclusa($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return (intval($s[0]['setting']) == 1 ? true : false);
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('ivaInclusa', '');
			if (strlen($sval) > 0) {
				return (intval($sval) == 1 ? true : false);
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='ivainclusa';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('ivaInclusa', $s[0]['setting']);
				return (intval($s[0]['setting']) == 1 ? true : false);
			}
		}
	}

	public static function showTaxOnSummaryOnly($skipsession = false) {
		$dbo = JFactory::getDBO();
		if ($skipsession) {
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='taxsummary';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return (intval($s[0]['setting']) == 1 ? true : false);
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vrcshowTaxOnSummaryOnly', '');
			if (strlen($sval) > 0) {
				return (intval($sval) == 1 ? true : false);
			} else {
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='taxsummary';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vrcshowTaxOnSummaryOnly', $s[0]['setting']);
				return (intval($s[0]['setting']) == 1 ? true : false);
			}
		}
	}

	public static function tokenForm() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='tokenform';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}

	public static function getPaypalAcc() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='ccpaypal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getAccPerCent() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='payaccpercent';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getTypeDeposit($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='typedeposit';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vrcTypeDeposit', '');
			if (strlen($sval) > 0) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='typedeposit';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vrcTypeDeposit', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}

	public static function getAdminMail() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			return '';
		}
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getSenderMail() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='senderemail' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$sendermail = $dbo->loadResult();
			if (!empty($sendermail)) {
				return $sendermail;
			}
		}
		return self::getAdminMail();
	}

	public static function getPaymentName() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentcar_texts` WHERE `param`='paymentname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getMinutesLock($conv = false) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='minuteslock';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		if ($conv) {
			$op = $s[0]['setting'] * 60;
			return (time() + $op);
		} else {
			return $s[0]['setting'];
		}
	}

	public static function carNotLocked($idcar, $units, $first, $second) {
		$dbo = JFactory::getDBO();
		$actnow = time();
		$booked = array ();
		$q = "DELETE FROM `#__vikrentcar_tmplock` WHERE `until`<" . $actnow . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		//vikrentcar 1.5
		$secdiff = $second - $first;
		$daysdiff = $secdiff / 86400;
		if (is_int($daysdiff)) {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			}
		} else {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			} else {
				$sum = floor($daysdiff) * 86400;
				$newdiff = $secdiff - $sum;
				$maxhmore = self::getHoursMoreRb() * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}
		$groupdays = self::getGroupDays($first, $second, $daysdiff);
		$check = "SELECT `id`,`ritiro`,`realback` FROM `#__vikrentcar_tmplock` WHERE `idcar`=" . $dbo->quote($idcar) . " AND `until`>=" . $actnow . ";";
		$dbo->setQuery($check);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$busy = $dbo->loadAssocList();
			foreach ($groupdays as $gday) {
				$bfound = 0;
				foreach ($busy as $bu) {
					if ($gday >= $bu['ritiro'] && $gday <= $bu['realback']) {
						$bfound++;
					}
				}
				if ($bfound >= $units) {
					return false;
				}
			}
		}
		//
		return true;
	}
	
	public static function getGroupDays($first, $second, $daysdiff) {
		$ret = array();
		$ret[] = $first;
		if ($daysdiff > 1) {
			$start = getdate($first);
			$end = getdate($second);
			$endcheck = mktime(0, 0, 0, $end['mon'], $end['mday'], $end['year']);
			for ($i = 1; $i < $daysdiff; $i++) {
				$checkday = $start['mday'] + $i;
				$dayts = mktime(0, 0, 0, $start['mon'], $checkday, $start['year']);
				if ($dayts != $endcheck) {				
					$ret[] = $dayts;
				}
			}
		}
		$ret[] = $second;
		return $ret;
	}
	
	public static function checkValidClosingDays($groupdays, $pickup, $dropoff) {
		$errorstr = '';
		$compare = array();
		$compare[] = date('Y-m-d', $groupdays[0]);
		$compare[] = date('Y-m-d', end($groupdays));
		$pick_info = getdate($groupdays[0]);
		$drop_info = getdate($groupdays[(count($groupdays) - 1)]);
		$dbo = JFactory::getDBO();
		$df = self::getDateFormat();
		$df = str_replace('%', '', $df);
		if ($pickup == $dropoff) {
			$q = "SELECT `id`,`name`,`closingdays` FROM `#__vikrentcar_places` WHERE `id`='".intval($pickup)."';";
		} else {
			$q = "SELECT `id`,`name`,`closingdays` FROM `#__vikrentcar_places` WHERE `id`='".intval($pickup)."' OR `id`='".intval($dropoff)."';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$getclosing = $dbo->loadAssocList();
		if (count($getclosing) > 0) {
			foreach ($getclosing as $closed) {
				if (!empty($closed['closingdays'])) {
					$closingdates = explode(",", $closed['closingdays']);
					foreach ($closingdates as $clod) {
						if (!empty($clod)) {
							if ((int)$closed['id'] == (int)$pickup && str_replace(':w', '', $clod) == $compare[0]) {
								$dateparts = explode("-", $clod);
								$errorstr = JText::sprintf('VRCERRLOCATIONCLOSEDON', $closed['name'], date($df, mktime(0, 0, 0, $dateparts[1], (int)str_replace(':w', '', $dateparts[2]), $dateparts[0])));
								break 2;
							} elseif ((int)$closed['id'] == (int)$dropoff && str_replace(':w', '', $clod) == $compare[1]) {
								$dateparts = explode("-", $clod);
								$errorstr = JText::sprintf('VRCERRLOCATIONCLOSEDON', $closed['name'], date($df, mktime(0, 0, 0, $dateparts[1], (int)str_replace(':w', '', $dateparts[2]), $dateparts[0])));
								break 2;
							} elseif (strpos($clod, ':w') !== false) {
								//VRC 1.11 Weekly closing days
								$dateparts = explode("-", $clod);
								$clod_info = getdate(mktime(0, 0, 0, $dateparts[1], (int)str_replace(':w', '', $dateparts[2]), $dateparts[0]));
								if ((int)$closed['id'] == (int)$pickup && $pick_info['wday'] == $clod_info['wday']) {
									$errorstr = JText::sprintf('VRCERRLOCATIONCLOSEDON', $closed['name'], date($df, mktime(0, 0, 0, $pick_info['mon'], $pick_info['mday'], $pick_info['year'])));
									break 2;
								} elseif ((int)$closed['id'] == (int)$dropoff && $drop_info['wday'] == $clod_info['wday']) {
									$errorstr = JText::sprintf('VRCERRLOCATIONCLOSEDON', $closed['name'], date($df, mktime(0, 0, 0, $drop_info['mon'], $drop_info['mday'], $drop_info['year'])));
									break 2;
								}
							}
						}
					}
				}
			}
		}
		return $errorstr;
	}
	
	/**
	 * Checks whether a specific car ID can be booked on the passed dates.
	 * The field 'realback' is used rather than 'consegna' to check the car
	 * drop off date. To use 'consegna' we should have added the seconds of
	 * $morehst = VikRentCar::getHoursCarAvail() * 3600;
	 *
	 * @param 	int 	$idcar 		the ID of the car to check.
	 * @param 	int 	$units 		the number of units available in full for this car.
	 * @param 	int 	$first 		pick up timestamp.
	 * @param 	int 	$second 	drop off timestamp.
	 * @param 	array 	[$busy] 	the list of busy records to compare with the dates.
	 *
	 * @return 	boolean 			True if the car can be booked, false otherwise.
	 *
	 * @since 	1.12 	(the current arguments structure)
	 */
	public static function carBookable($idcar, $units, $first, $second, $busy = null) {
		$dbo = JFactory::getDBO();

		$idcar = (int)$idcar;
		$secdiff = $second - $first;
		$daysdiff = $secdiff / 86400;
		if (is_int($daysdiff)) {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			}
		} else {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			} else {
				$sum = floor($daysdiff) * 86400;
				$newdiff = $secdiff - $sum;
				$maxhmore = self::getHoursMoreRb() * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}
		
		if (is_null($busy) || !is_array($busy)) {
			$busy = array();
			$check = "SELECT `id`,`ritiro`,`realback`,`stop_sales` FROM `#__vikrentcar_busy` WHERE `idcar`=" . $dbo->quote($idcar) . " AND `consegna` > ".time().";";
			$dbo->setQuery($check);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$busy = $dbo->loadAssocList();
			}
		}
		if (!count($busy)) {
			return true;
		}
		
		$groupdays = self::getGroupDays($first, $second, $daysdiff);
		foreach ($groupdays as $kgd => $gday) {
			$bfound = 0;
			foreach ($busy as $bu) {
				if ($gday >= $bu['ritiro'] && $gday <= $bu['realback']) {
					$bfound++;
					if ($bu['stop_sales'] == 1) {
						$bfound = $units;
						break;
					}
				} elseif (count($groupdays) == 2 && $gday == $groupdays[0]) {
					//VRC 1.7
					if ($groupdays[0] < $bu['ritiro'] && $groupdays[0] < $bu['realback'] && $groupdays[1] > $bu['ritiro'] && $groupdays[1] > $bu['realback']) {
						$bfound++;
						if ($bu['stop_sales'] == 1) {
							$bfound = $units;
							break;
						}
					} elseif ($groupdays[0] < $bu['ritiro'] && $groupdays[0] < $bu['realback'] && $groupdays[1] > $bu['ritiro'] && $groupdays[1] <= $bu['realback']) {
						// VRC 1.12 - rentals lasting one day or less touching other hourly/daily rentals with different pickup/dropoff times
						$bfound++;
						if ($bu['stop_sales'] == 1) {
							$bfound = $units;
							break;
						}
					}
				} elseif (isset($groupdays[($kgd + 1)]) && (($bu['realback'] - $bu['ritiro']) < 86400) && $gday < $bu['ritiro'] && $groupdays[($kgd + 1)] > $bu['realback']) {
					// VRC 1.10 availability check whith hourly rentals
					$bfound++;
					if ($bu['stop_sales'] == 1) {
						$bfound = $units;
						break;
					}
				} elseif (count($groupdays) > 2 && array_key_exists(($kgd - 1), $groupdays) && array_key_exists(($kgd + 1), $groupdays)) {
					//VRC 1.10 gday is at midnight and the pickup for this date may be at a later time
					if ($groupdays[($kgd - 1)] < $bu['ritiro'] && $groupdays[($kgd - 1)] < $bu['realback'] && $gday < $bu['ritiro'] && $groupdays[($kgd + 1)] > $bu['ritiro'] && $gday <= $bu['realback']) {
						$bfound++;
						if ($bu['stop_sales'] == 1) {
							$bfound = $units;
							break;
						}
					}
				}
			}
			if ($bfound >= $units) {
				return false;
			}
		}

		return true;
	}

	public static function payTotal() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='paytotal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}
	
	public static function getCouponInfo($code) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentcar_coupons` WHERE `code`=".$dbo->quote($code).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$c = $dbo->loadAssocList();
			return $c[0];
		} else {
			return "";
		}
	}
	
	public static function getCarInfo($idcar, $vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentcar_cars` WHERE `id`='" . $idcar . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			return array();
		}
		$s = $dbo->loadAssocList();
		if (is_object($vrc_tn)) {
			$vrc_tn->translateContents($s, '#__vikrentcar_cars');
		}
		return $s[0];
	}

	public static function sayCategory($ids, $vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$split = explode(";", $ids);
		$say = "";
		foreach ($split as $k => $s) {
			if (strlen($s)) {
				$q = "SELECT `id`,`name` FROM `#__vikrentcar_categories` WHERE `id`='" . $s . "';";
				$dbo->setQuery($q);
				$dbo->execute();
				$nam = $dbo->loadAssocList();
				if (is_object($vrc_tn)) {
					$vrc_tn->translateContents($nam, '#__vikrentcar_categories');
				}
				$say .= $nam[0]['name'];
				$say .= (strlen($split[($k +1)]) && end($split) != $s ? ", " : "");
			}
		}
		return $say;
	}

	public static function getCarCarat($idc) {
		$dbo = JFactory::getDBO();
		$split = explode(";", $idc);
		$carat = "";
		$dbo = JFactory::getDBO();
		$arr = array ();
		$where = array();
		foreach ($split as $s) {
			if (!empty($s)) {
				$where[] = (int)$s;
			}
		}
		if (count($where) > 0) {
			$q = "SELECT `id`,`name`,`icon`,`align`,`textimg` FROM `#__vikrentcar_caratteristiche` WHERE `id` IN (".implode(",", $where).");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$arr = $dbo->loadAssocList();
			}
		}
		if (@count($arr) > 0) {
			$carat .= "<table class=\"vrcsearchcaratt\">";
			foreach ($arr as $a) {
				if (!empty ($a['textimg'])) {
					if ($a['align'] == "left") {
						$carat .= "<tr><td align=\"center\">" . $a['textimg'] . "</td>" . (!empty ($a['icon']) ? "<td align=\"center\"><img src=\"".VRC_ADMIN_URI."resources/" . $a['icon'] . "\"/></td></tr>" : "</tr>");
					}
					elseif ($a['align'] == "center") {
						$carat .= "<tr><td align=\"center\">" . (!empty ($a['icon']) ? "<img src=\"".VRC_ADMIN_URI."resources/" . $a['icon'] . "\"/><br/>" : "") . $a['textimg'] . "</td></tr>";
					} else {
						$carat .= "<tr>" . (!empty ($a['icon']) ? "<td align=\"center\"><img src=\"".VRC_ADMIN_URI."resources/" . $a['icon'] . "\"/></td>" : "") . "<td align=\"center\">" . $a['textimg'] . "</td></tr>";
					}
				} else {
					$carat .= (!empty ($a['icon']) ? "<tr><td align=\"center\"><img src=\"".VRC_ADMIN_URI."resources/" . $a['icon'] . "\" alt=\"" . $a['name'] . "\" title=\"" . $a['name'] . "\"/></td></tr>" : "");
				}
			}
			$carat .= "</table>\n";
		}
		return $carat;
	}

	public static function getCarCaratFly($idc) {
		$dbo = JFactory::getDBO();
		$split = explode(";", $idc);
		$carat = "";
		$dbo = JFactory::getDBO();
		$arr = array ();
		$where = array();
		foreach ($split as $s) {
			if (!empty($s)) {
				$where[] = (int)$s;
			}
		}
		if (count($where) > 0) {
			$q = "SELECT * FROM `#__vikrentcar_caratteristiche` WHERE `id` IN (".implode(",", $where).") ORDER BY `#__vikrentcar_caratteristiche`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$arr = $dbo->loadAssocList();
			}
		}
		if (@count($arr) > 0) {
			$carat .= "<table><tr>";
			foreach ($arr as $a) {
				if (!empty ($a['textimg'])) {
					if ($a['align'] == "left") {
						$carat .= "<td valign=\"top\">" . $a['textimg'] . (!empty ($a['icon']) ? " <img src=\"" . VRC_ADMIN_URI . "resources/" . $a['icon'] . "\"/></td>" : "</td>");
					}
					elseif ($a['align'] == "center") {
						$carat .= "<td align=\"center\" valign=\"top\">" . (!empty ($a['icon']) ? "<img src=\"" . VRC_ADMIN_URI . "resources/" . $a['icon'] . "\"/><br/>" : "") . $a['textimg'] . "</td>";
					} else {
						$carat .= "<td valign=\"top\">" . (!empty ($a['icon']) ? "<img src=\"" . VRC_ADMIN_URI . "resources/" . $a['icon'] . "\"/> " : "") . $a['textimg'] . "</td>";
					}
				} else {
					$carat .= (!empty ($a['icon']) ? "<td valign=\"top\"><img src=\"" . VRC_ADMIN_URI . "resources/" . $a['icon'] . "\" alt=\"" . $a['name'] . "\" title=\"" . $a['name'] . "\"/></td>" : "");
				}
			}
			$carat .= "</tr></table>\n";
		}
		return $carat;
	}

	public static function getCarCaratOriz($idc, $map = array(), $vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$split = explode(";", $idc);
		$carat = "";
		$arr = array ();
		$where = array();
		foreach ($split as $s) {
			if (!empty($s)) {
				$where[] = (int)$s;
			}
		}
		if (count($where) > 0) {
			if (count($map) > 0) {
				foreach ($where as $c_id) {
					if (array_key_exists($c_id, $map)) {
						$arr[] = $map[$c_id];
					}
				}
			} else {
				$q = "SELECT * FROM `#__vikrentcar_caratteristiche` WHERE `id` IN (".implode(",", $where).") ORDER BY `#__vikrentcar_caratteristiche`.`ordering` ASC;";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$arr = $dbo->loadAssocList();
					if (is_object($vrc_tn)) {
						$vrc_tn->translateContents($arr, '#__vikrentcar_caratteristiche');
					}
				}
			}
		}
		if (count($arr) > 0) {
			$carat .= "<div class=\"vrccaratsdiv\">";
			foreach ($arr as $a) {
				$carat .= "<div class=\"vrccarcarat\">";
				if (!empty ($a['textimg'])) {
					//tooltip icon text is not empty
					if (!empty($a['icon'])) {
						//an icon has been uploaded: display the image
						$carat .= "<span class=\"vrc-carat-cont\"><span class=\"vrc-expl\" ><img src=\"".VRC_ADMIN_URI."resources/".$a['icon']."\" alt=\"" . $a['name'] . "\" /><span class=\"vrc-expltext\">".$a['textimg']."</span></span></span>\n";
					} else {
						if (strpos($a['textimg'], '</i>') !== false) {
							//the tooltip icon text is a font-icon, we can use the name as tooltip
							$carat .= "<span class=\"vrc-carat-cont\"><span class=\"vrc-expl\" data-vrc-expl=\"".$a['name']."\">".$a['textimg']."</span></span>\n";
						} else {
							//display just the text
							$carat .= "<span class=\"vrc-carat-cont\">".$a['textimg']."</span>\n";
						}
					}
				} else {
					$carat .= (!empty($a['icon']) ? "<span class=\"vrc-carat-cont\"><img src=\"".VRC_ADMIN_URI."resources/" . $a['icon'] . "\" alt=\"" . $a['name'] . "\" title=\"" . $a['name'] . "\"/></span>\n" : "<span class=\"vrc-carat-cont\">".$a['name']."</span>\n");
				}
				$carat .= "</div>";
			}
			$carat .= "</div>\n";
		}
		return $carat;
	}

	public static function getCarOptionals($idopts, $vrc_tn = null) {
		$split = explode(";", $idopts);
		$dbo = JFactory::getDBO();
		$arr = array ();
		$where = array ();
		foreach ($split as $s) {
			if (!empty($s)) {
				$where[] = (int)$s;
			}
		}
		if (@ count($where) > 0) {
			$q = "SELECT * FROM `#__vikrentcar_optionals` WHERE `id` IN (".implode(", ", $where).") ORDER BY `#__vikrentcar_optionals`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$arr = $dbo->loadAssocList();
				if (is_object($vrc_tn)) {
					$vrc_tn->translateContents($arr, '#__vikrentcar_optionals');
				}
			}
		}
		if (@count($arr) > 0) {
			return $arr;
		}
		return "";
	}

	public static function dayValidTs($days, $first, $second) {
		$secdiff = $second - $first;
		$daysdiff = $secdiff / 86400;
		if (is_int($daysdiff)) {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			}
		} else {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='hoursmorerentback';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$sum = floor($daysdiff) * 86400;
				$newdiff = $secdiff - $sum;
				$maxhmore = $s[0]['setting'] * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}
		return ($daysdiff == $days ? true : false);
	}
	
	public static function registerLocationTaxRate($idpickuplocation) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$register = '';
		$q = "SELECT `p`.`name`,`i`.`aliq` FROM `#__vikrentcar_places` AS `p` LEFT JOIN `#__vikrentcar_iva` `i` ON `p`.`idiva`=`i`.`id` WHERE `p`.`id`='".intval($idpickuplocation)."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$getdata = $dbo->loadAssocList();
			if (!empty($getdata[0]['aliq'])) {
				$register = $getdata[0]['aliq'];
			}
		}
		$session->set('vrcLocationTaxRate', $register);
		return true;
	}
	
	public static function sayCostPlusIva($cost, $idprice, $order=array()) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('ivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('ivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 0) {
			//VRC 1.7 Rev.2
			$locationvat = strlen($order['locationvat']) > 0 ? $order['locationvat'] : (count($order) == 0 ? $session->get('vrcLocationTaxRate', '') : '');
			if (strlen($locationvat) > 0) {
				$subt = 100 + $locationvat;
				$op = ($cost * $subt / 100);
				return $op;
			}
			//
			$q = "SELECT `idiva` FROM `#__vikrentcar_prices` WHERE `id`='" . $idprice . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$pidiva = $dbo->loadAssocList();
				$q = "SELECT `aliq` FROM `#__vikrentcar_iva` WHERE `id`='" . $pidiva[0]['idiva'] . "';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$paliq = $dbo->loadAssocList();
					$subt = 100 + $paliq[0]['aliq'];
					$op = ($cost * $subt / 100);
					return $op;
				}
			}
		}
		return $cost;
	}

	public static function sayCostMinusIva($cost, $idprice, $order=array()) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('ivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('ivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 1) {
			//VRC 1.7 Rev.2
			$locationvat = strlen($order['locationvat']) > 0 ? $order['locationvat'] : (count($order) == 0 ? $session->get('vrcLocationTaxRate', '') : '');
			if (strlen($locationvat) > 0) {
				$subt = 100 + $locationvat;
				$op = ($cost * 100 / $subt);
				return $op;
			}
			//
			$q = "SELECT `idiva` FROM `#__vikrentcar_prices` WHERE `id`='" . $idprice . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$pidiva = $dbo->loadAssocList();
				$q = "SELECT `aliq` FROM `#__vikrentcar_iva` WHERE `id`='" . $pidiva[0]['idiva'] . "';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$paliq = $dbo->loadAssocList();
					$subt = 100 + $paliq[0]['aliq'];
					$op = ($cost * 100 / $subt);
					return $op;
				}
			}
		}
		return $cost;
	}

	public static function sayCustCostMinusIva($cost, $aliq_id) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `aliq` FROM `#__vikrentcar_iva` WHERE `id`='" . (int)$aliq_id . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$paliq = $dbo->loadAssocList();
			$subt = 100 + $paliq[0]['aliq'];
			$op = ($cost * 100 / $subt);
			return $op;
		}
		return $cost;
	}

	public static function sayOptionalsPlusIva($cost, $idiva, $order=array()) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('ivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('ivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 0) {
			//VRC 1.7 Rev.2
			$locationvat = strlen($order['locationvat']) > 0 ? $order['locationvat'] : (count($order) == 0 ? $session->get('vrcLocationTaxRate', '') : '');
			if (strlen($locationvat) > 0) {
				$subt = 100 + $locationvat;
				$op = ($cost * $subt / 100);
				return $op;
			}
			//
			$q = "SELECT `aliq` FROM `#__vikrentcar_iva` WHERE `id`='" . $idiva . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * $subt / 100);
				return $op;
			}
		}
		return $cost;
	}

	public static function sayOptionalsMinusIva($cost, $idiva, $order=array()) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('ivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('ivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 1) {
			//VRC 1.7 Rev.2
			$locationvat = strlen($order['locationvat']) > 0 ? $order['locationvat'] : (count($order) == 0 ? $session->get('vrcLocationTaxRate', '') : '');
			if (strlen($locationvat) > 0) {
				$subt = 100 + $locationvat;
				$op = ($cost * 100 / $subt);
				return $op;
			}
			//
			$q = "SELECT `aliq` FROM `#__vikrentcar_iva` WHERE `id`='" . $idiva . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * 100 / $subt);
				return $op;
			}
		}
		return $cost;
	}

	public static function getSecretLink() {
		$sid = mt_rand();
		$dbo = JFactory::getDBO();
		$q = "SELECT `sid` FROM `#__vikrentcar_orders`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (@ $dbo->getNumRows() > 0) {
			$all = $dbo->loadAssocList();
			foreach ($all as $s) {
				$arr[] = $s['sid'];
			}
			if (in_array($sid, $arr)) {
				while (in_array($sid, $arr)) {
					$sid++;
				}
			}
		}
		return $sid;
	}

	public static function buildCustData($arr, $sep) {
		$cdata = "";
		foreach ($arr as $k => $e) {
			if (strlen($e)) {
				$cdata .= (strlen($k) > 0 ? $k . ": " : "") . $e . $sep;
			}
		}
		return $cdata;
	}

	public static function sendAdminMail($to, $subject, $ftitle, $ts, $custdata, $carname, $first, $second, $pricestr, $optstr, $tot, $status, $place = "", $returnplace = "", $maillocfee = "", $mailoohfee = "", $payname = "", $couponstr = "") {
		$sendwhen = self::getSendEmailWhen();
		if ($sendwhen > 1 && $status == JText::_('VRSTANDBY')) {
			return true;
		}

		$parts = explode(';;', $to);
		$to = $parts[0];
		//$useremail is the email address of the customer
		$useremail = $parts[1];
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$currencyname = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$formdate = $dbo->loadResult();
		$nowtf = self::getTimeFormat();
		if ($formdate == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($formdate == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$msg = $ftitle . "\n\n";
		$msg .= JText::_('VRLIBONE') . " " . date($df . ' ' . $nowtf, $ts) . "\n";
		$msg .= JText::_('VRLIBTWO') . ":\n" . $custdata . "\n";
		
		
		$msg .= JText::_('VRLIBTHREE') . ": " . $carname . "\n";
		
		
		
		
		$msg .= JText::_('VRLIBFOUR') . " " . date($df . ' ' . $nowtf, $first) . "\n";
		$msg .= JText::_('VRLIBFIVE') . " " . date($df . ' ' . $nowtf, $second) . "\n";
		$msg .= (!empty ($place) ? JText::_('VRRITIROCAR') . ": " . $place . "\n" : "");
		$msg .= (!empty ($returnplace) ? JText::_('VRRETURNCARORD') . ": " . $returnplace . "\n" : "");
		$msg .= $pricestr . "\n";
		$msg .= $optstr . "\n";
		if (!empty ($maillocfee) && $maillocfee > 0) {
			$msg .= JText::_('VRLOCFEETOPAY') . ": " . self::numberFormat($maillocfee) . " " . $currencyname . "\n\n";
		}
		if (!empty ($mailoohfee) && $mailoohfee > 0) {
			$msg .= JText::_('VRCOOHFEEAMOUNT') . ": " . self::numberFormat($mailoohfee) . " " . $currencyname . "\n\n";
		}
		//vikrentcar 1.6 coupon
		if (strlen($couponstr) > 0) {
			$expcoupon = explode(";", $couponstr);
			$msg .= JText::_('VRCCOUPON')." ".$expcoupon[2].": -" . $expcoupon[1] . " " . $currencyname . "\n\n";
		}
		//
		$msg .= JText::_('VRLIBSIX') . ": " . $tot . " " . $currencyname . "\n\n";
		if (!empty ($payname)) {
			$msg .= JText::_('VRLIBPAYNAME') . ": " . $payname . "\n\n";
		}
		$msg .= JText::_('VRLIBSEVEN') . ": " . $status;

		$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

		$vrc_app = self::getVrcApplication();
		$adsendermail = self::getSenderMail();
		$vrc_app->sendMail($adsendermail, $adsendermail, $to, $useremail, $subject, $msg, false);
		
		return true;
	}
	
	public static function loadEmailTemplate($orderid) {
		define('_VIKRENTCAREXEC', '1');
		$order_details = array();
		if (!empty($orderid)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=".(int)$orderid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$order_details = $dbo->loadAssoc();
			}
		}
		ob_start();
		include VRC_SITE_PATH . DS . "helpers" . DS ."email_tmpl.php";
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	public static function loadPdfTemplate($orderid) {
		defined('_VIKRENTCAREXEC') OR define('_VIKRENTCAREXEC', '1');
		$order_details = array();
		if (!empty($orderid)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=".(int)$orderid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$order_details = $dbo->loadAssoc();
			}
		}
		ob_start();
		include VRC_SITE_PATH . DS . "helpers" . DS ."pdf_tmpl.php";
		$content = ob_get_contents();
		ob_end_clean();
		$default_params = array(
			'show_header' => 0,
			'header_data' => array(),
			'show_footer' => 0,
			'pdf_page_orientation' => 'PDF_PAGE_ORIENTATION',
			'pdf_unit' => 'PDF_UNIT',
			'pdf_page_format' => 'PDF_PAGE_FORMAT',
			'pdf_margin_left' => 'PDF_MARGIN_LEFT',
			'pdf_margin_top' => 'PDF_MARGIN_TOP',
			'pdf_margin_right' => 'PDF_MARGIN_RIGHT',
			'pdf_margin_header' => 'PDF_MARGIN_HEADER',
			'pdf_margin_footer' => 'PDF_MARGIN_FOOTER',
			'pdf_margin_bottom' => 'PDF_MARGIN_BOTTOM',
			'pdf_image_scale_ratio' => 'PDF_IMAGE_SCALE_RATIO',
			'header_font_size' => '10',
			'body_font_size' => '10',
			'footer_font_size' => '8'
		);
		if (defined('_VIKRENTCAR_PAGE_PARAMS') && isset($page_params) && @count($page_params) > 0) {
			$default_params = array_merge($default_params, $page_params);
		}
		return array($content, $default_params);
	}

	public static function loadCheckinPdfTemplate($order_details) {
		defined('_VIKRENTCAREXEC') OR define('_VIKRENTCAREXEC', '1');
		$order_details = $order_details;
		ob_start();
		include VRC_SITE_PATH . DS . "helpers" . DS ."checkin_pdf_tmpl.php";
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	public static function loadInvoiceTmpl($order_details) {
		defined('_VIKRENTCAREXEC') or define('_VIKRENTCAREXEC', '1');
		$order_details = $order_details;
		ob_start();
		include VRC_SITE_PATH . DS . "helpers" . DS . "invoices" . DS ."invoice_tmpl.php";
		$content = ob_get_contents();
		ob_end_clean();
		$default_params = array(
			'show_header' => 0,
			'header_data' => array(),
			'show_footer' => 0,
			'pdf_page_orientation' => 'PDF_PAGE_ORIENTATION',
			'pdf_unit' => 'PDF_UNIT',
			'pdf_page_format' => 'PDF_PAGE_FORMAT',
			'pdf_margin_left' => 'PDF_MARGIN_LEFT',
			'pdf_margin_top' => 'PDF_MARGIN_TOP',
			'pdf_margin_right' => 'PDF_MARGIN_RIGHT',
			'pdf_margin_header' => 'PDF_MARGIN_HEADER',
			'pdf_margin_footer' => 'PDF_MARGIN_FOOTER',
			'pdf_margin_bottom' => 'PDF_MARGIN_BOTTOM',
			'pdf_image_scale_ratio' => 'PDF_IMAGE_SCALE_RATIO',
			'header_font_size' => '10',
			'body_font_size' => '10',
			'footer_font_size' => '8'
		);
		if (defined('_VIKRENTCAR_INVOICE_PARAMS') && isset($invoice_params) && @count($invoice_params) > 0) {
			$default_params = array_merge($default_params, $invoice_params);
		}
		return array($content, $default_params);
	}

	public static function printTrackingCode() {
		include VRC_SITE_PATH . DS . "helpers" . DS ."tracking_code_tmpl.php";
	}

	public static function printConversionCode($oid) {
		$dbo = JFactory::getDBO();
		$q = "UPDATE `#__vikrentcar_orders` SET `seen`=1 WHERE `id`=".(int)$oid." LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		include VRC_SITE_PATH . DS . "helpers" . DS ."conversion_code_tmpl.php";
	}

	public static function parseInvoiceTemplate($tmpl, $record, $car, $orderinfo) {
		$dbo = JFactory::getDBO();
		$vrc_tn = self::getTranslator();
		$parsed = $tmpl;
		$orderinfo['custom_fields'] = $record['custdata'];
		$orderinfo['invoice_totalnet'] = 0;
		$orderinfo['invoice_totaltax'] = 0;
		$parsed = str_replace("{company_logo}", $orderinfo['company_logo'], $parsed);
		$parsed = str_replace("{company_info}", $orderinfo['company_info'], $parsed);
		$parsed = str_replace("{invoice_number}", $orderinfo['invoice_number'], $parsed);
		$parsed = str_replace("{invoice_suffix}", $orderinfo['invoice_suffix'], $parsed);
		$parsed = str_replace("{invoice_date}", $orderinfo['invoice_date'], $parsed);
		$inv_rows = '';
		$extra_rows = 0;
		foreach ($orderinfo['invoice_products_descriptions'] as $k => $v) {
			$inv_rows .= '<tr>'."\n";
			$inv_rows .= '<td>'.$v.'</td>'."\n";
			$inv_rows .= '<td>'.$orderinfo['currencyname'].' '.self::numberformat($orderinfo['invoice_products_netprices'][$k]).'</td>'."\n";
			$orderinfo['invoice_totalnet'] += $orderinfo['invoice_products_netprices'][$k];
			$inv_rows .= '<td>'.$orderinfo['currencyname'].' '.self::numberformat($orderinfo['invoice_products_taxes'][$k]).'</td>'."\n";
			$orderinfo['invoice_totaltax'] += $orderinfo['invoice_products_taxes'][$k];
			$tot_product = $orderinfo['invoice_products_netprices'][$k] + $orderinfo['invoice_products_taxes'][$k];
			$inv_rows .= '<td>'.$orderinfo['currencyname'].' '.self::numberformat($tot_product).'</td>'."\n";
			$inv_rows .= '</tr>'."\n";
		}

		$usedcoupon = false;
		if (strlen($record['coupon']) > 0) {
			$orig_isdue = $orderinfo['invoice_grandtotal'];
			$expcoupon = explode(";", $record['coupon']);
			$usedcoupon = $expcoupon;
			$orderinfo['invoice_grandtotal'] = $orderinfo['invoice_grandtotal'] - (float)$expcoupon[1];
			if ($orderinfo['invoice_grandtotal'] != $orig_isdue) {
				// lower total net and taxes proportionally
				$orderinfo['invoice_totalnet'] = $orderinfo['invoice_grandtotal'] * $orderinfo['invoice_totalnet'] / $orig_isdue;
				$orderinfo['invoice_totaltax'] = $orderinfo['invoice_grandtotal'] * $orderinfo['invoice_totaltax'] / $orig_isdue;
			}
		}

		// if discount print row
		if ($usedcoupon !== false) {
			$inv_rows .= '<tr>'."\n";
			$inv_rows .= '<td></td><td></td><td></td><td></td>'."\n";
			$inv_rows .= '</tr>'."\n";
			$inv_rows .= '<tr>'."\n";
			$inv_rows .= '<td>'.$usedcoupon[2].'</td>'."\n";
			$inv_rows .= '<td></td>'."\n";
			$inv_rows .= '<td></td>'."\n";
			$inv_rows .= '<td>- '.$orderinfo['currencyname'].' '.self::numberformat($usedcoupon[1]).'</td>'."\n";
			$inv_rows .= '</tr>'."\n";
			$extra_rows += 2;
		}
		//

		$min_records = 7;
		if ((count($orderinfo['invoice_products_descriptions']) + $extra_rows) < $min_records) {
			for ($i=1; $i <= ($min_records - count($orderinfo['invoice_products_descriptions']) - $extra_rows); $i++) { 
				$inv_rows .= '<tr>'."\n";
				$inv_rows .= '<td></td>'."\n";
				$inv_rows .= '<td></td>'."\n";
				$inv_rows .= '<td></td>'."\n";
				$inv_rows .= '</tr>'."\n";
			}
		}

		$parsed = str_replace("{invoice_products_descriptions}", $inv_rows, $parsed);
		$parsed = str_replace("{customer_info}", nl2br(rtrim($orderinfo['custom_fields'], "\n")), $parsed);
		$parsed = str_replace("{invoice_totalnet}", $orderinfo['currencyname'].' '.self::numberformat($orderinfo['invoice_totalnet']), $parsed);
		$parsed = str_replace("{invoice_totaltax}", $orderinfo['currencyname'].' '.self::numberformat($orderinfo['invoice_totaltax']), $parsed);
		$parsed = str_replace("{invoice_grandtotal}", $orderinfo['currencyname'].' '.self::numberformat($orderinfo['invoice_grandtotal']), $parsed);

		//custom fields replace
		preg_match_all('/\{customfield ([0-9]+)\}/U', $parsed, $matches);
		if (is_array($matches[1]) && @count($matches[1]) > 0) {
			$cfids = array();
			foreach ($matches[1] as $cfid ) {
				$cfids[] = $cfid;
			}
			$q = "SELECT * FROM `#__vikrentcar_custfields` WHERE `id` IN (".implode(", ", $cfids).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$cfields = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
			$vrc_tn->translateContents($cfields, '#__vikrentcar_custfields');
			$cfmap = array();
			if (is_array($cfields)) {
				foreach ($cfields as $cf) {
					$cfmap[trim(JText::_($cf['name']))] = $cf['id'];
				}
			}
			$cfmapreplace = array();
			$partsreceived = explode("\n", $record['custdata']);
			if (count($partsreceived) > 0) {
				foreach ($partsreceived as $pst) {
					if (!empty($pst)) {
						$tmpdata = explode(":", $pst);
						if (array_key_exists(trim($tmpdata[0]), $cfmap)) {
							$cfmapreplace[$cfmap[trim($tmpdata[0])]] = trim($tmpdata[1]);
						}
					}
				}
			}
			foreach ($matches[1] as $cfid ) {
				if (array_key_exists($cfid, $cfmapreplace)) {
					$parsed = str_replace("{customfield ".$cfid."}", $cfmapreplace[$cfid], $parsed);
				} else {
					$parsed = str_replace("{customfield ".$cfid."}", "", $parsed);
				}
			}
		}
		//end custom fields replace
		
		return $parsed;
	}
	
	public static function parseEmailTemplate($tmpl, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $tcustdata, $tiname, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tprices, $topts, $tlocfee, $toohfee, $ttot, $tlink, $tfootm, $couponstr) {
		$dbo = JFactory::getDBO();
		$vrc_tn = self::getTranslator();
		$parsed = $tmpl;
		//get sid and ts
		$lparts = explode("&sid=", $tlink);
		$lpartstwo = explode("&ts=", $lparts[1]);
		$sid = $lpartstwo[0];
		$lpartsthree = explode("&", $lpartstwo[1]);
		$ts = $lpartsthree[0];
		//
		//Confirmation Number
		if ($status == JText::_('VRCOMPLETED')) {
			$parsed = str_replace("{confirmnumb}", $sid.'_'.$ts, $parsed);
		} else {
			$parsed = preg_replace('#('.preg_quote('{confirmnumb_delimiter}').')(.*)('.preg_quote('{/confirmnumb_delimiter}').')#si', '$1'.' '.'$3', $parsed);
		}
		$parsed = str_replace("{confirmnumb_delimiter}", "", $parsed);
		$parsed = str_replace("{/confirmnumb_delimiter}", "", $parsed);
		//
		$parsed = str_replace("{logo}", $tlogo, $parsed);
		$parsed = str_replace("{company_name}", $tcname, $parsed);
		$parsed = str_replace("{order_id}", $orderid, $parsed);
		$statusclass = $status == JText::_('VRCOMPLETED') ? "confirmed" : "standby";
		$parsed = str_replace("{order_status_class}", $statusclass, $parsed);
		$parsed = str_replace("{order_status}", $status, $parsed);
		$parsed = str_replace("{order_date}", $todate, $parsed);
		//PIN Code
		if ($status == JText::_('VRCOMPLETED') && self::customersPinEnabled()) {
			$cpin = self::getCPinIstance();
			$customer_pin = $cpin->getPinCodeByOrderId($orderid);
			if (!empty($customer_pin)) {
				$tcustdata .= '<h3>'.JText::_('VRYOURPIN').': '.$customer_pin.'</h3>';
			}
		}
		//
		$parsed = str_replace("{customer_info}", $tcustdata, $parsed);
		$parsed = str_replace("{item_name}", $tiname, $parsed);
		$parsed = str_replace("{pickup_date}", $tpickupdate, $parsed);
		$parsed = str_replace("{pickup_location}", $tpickupplace, $parsed);
		$parsed = str_replace("{dropoff_date}", $tdropdate, $parsed);
		$parsed = str_replace("{dropoff_location}", $tdropplace, $parsed);
		//order details
		$orderdetails = "";
		$expdet = explode("\n", $tprices);
		$faredets = explode(":", $expdet[0]);
		$orderdetails .= '<div class="hireordata"><span class="Stile9">'.$faredets[0];
		if (!empty($expdet[1])) {
			$attrfaredets = explode(":", $expdet[1]);
			if (strlen($attrfaredets[1]) > 0) {
				$orderdetails .= ' - '.$attrfaredets[0].':'.$attrfaredets[1];
			}
		}
		$fareprice = trim(str_replace($currencyname, "", $faredets[1]));
		$orderdetails .= '</span><div align="right"><span class="Stile9">'.self::numberFormat($fareprice).' '.$currencyname.'</span></div></div>';
		//options
		if (strlen($topts) > 0) {
			$expopts = explode("\n", $topts);
			foreach ($expopts as $optinfo) {
				if (!empty($optinfo)) {
					$splitopt = explode(":", $optinfo);
					$optprice = trim(str_replace($currencyname, "", $splitopt[1]));
					$orderdetails .= '<div class="hireordata"><span class="Stile9">'.$splitopt[0].'</span><div align="right"><span class="Stile9">'.self::numberFormat($optprice).' '.$currencyname.'</span></div></div>';
				}
			}
		}
		//
		//locations fee
		if (!empty($tlocfee) && $tlocfee > 0) {
			$orderdetails .= '<div class="hireordata"><span class="Stile9">'.JText::_('VRLOCFEETOPAY').'</span><div align="right"><span class="Stile9">'.self::numberFormat($tlocfee).' '.$currencyname.'</span></div></div>';
		}
		//
		//out of hours fee
		if (!empty($toohfee) && $toohfee > 0) {
			$orderdetails .= '<div class="hireordata"><span class="Stile9">'.JText::_('VRCOOHFEEAMOUNT').'</span><div align="right"><span class="Stile9">'.self::numberFormat($toohfee).' '.$currencyname.'</span></div></div>';
		}
		//
		//coupon
		if (strlen($couponstr) > 0) {
			$expcoupon = explode(";", $couponstr);
			$orderdetails .= '<br/><div class="hireordata"><span class="Stile9">'.JText::_('VRCCOUPON').' '.$expcoupon[2].'</span><div align="right"><span class="Stile9">- '.self::numberFormat($expcoupon[1]).' '.$currencyname.'</span></div></div>';
		}
		//
		//discount payment method
		$q = "SELECT `idpayment` FROM `#__vikrentcar_orders` WHERE `id`=".(int)$orderid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$idpayment = $dbo->loadResult();
			if (!empty ($idpayment)) {
				$exppay = explode('=', $idpayment);
				$payment = self::getPayment($exppay[0], $vrc_tn);
				if (is_array($payment)) {
					if ($payment['charge'] > 0.00 && $payment['ch_disc'] != 1) {
						//Discount (not charge)
						if ($payment['val_pcent'] == 1) {
							//fixed value
							$ttot -= $payment['charge'];
							$orderdetails .= '<br/><div class="hireordata"><span class="Stile9">'.$payment['name'].'</span><div align="right"><span class="Stile9">- '.self::numberFormat($payment['charge']).' '.$currencyname.'</span></div></div>';
						} else {
							//percent value
							$percent_disc = $ttot * $payment['charge'] / 100;
							$ttot -= $percent_disc;
							$orderdetails .= '<br/><div class="hireordata"><span class="Stile9">'.$payment['name'].'</span><div align="right"><span class="Stile9">- '.self::numberFormat($percent_disc).' '.$currencyname.'</span></div></div>';
						}
					}
				}
			}
		}
		//
		$parsed = str_replace("{order_details}", $orderdetails, $parsed);
		//
		$parsed = str_replace("{order_total}", self::numberFormat($ttot).' '.$currencyname,  $parsed);
		$parsed = str_replace("{order_link}", '<a href="'.$tlink.'">'.$tlink.'</a>', $parsed);
		$parsed = str_replace("{footer_emailtext}", $tfootm, $parsed);
		//deposit
		$deposit_str = '';
		if ($status != JText::_('VRCOMPLETED') && !self::payTotal()) {
			$percentdeposit = self::getAccPerCent();
			if ($percentdeposit > 0) {
				if (self::getTypeDeposit() == "fixed") {
					$deposit_amount = $percentdeposit;
				} else {
					$deposit_amount = $ttot * $percentdeposit / 100;
				}
				if ($deposit_amount > 0) {
					$deposit_str = '<div class="hireordata hiredeposit"><span class="Stile9">'.JText::_('VRLEAVEDEPOSIT').'</span><div align="right"><strong>'.self::numberFormat($deposit_amount).' '.$currencyname.'</strong></div></div>';
				}
			}
		}
		$parsed = str_replace("{order_deposit}", $deposit_str, $parsed);
		//
		//Amount Paid - Remaining Balance
		$totpaid_str = '';
		$q = "SELECT `totpaid` FROM `#__vikrentcar_orders` WHERE `id`=".(int)$orderid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$tot_paid = $dbo->loadResult();
			$diff_topay = (float)$ttot - (float)$tot_paid;
			if ((float)$tot_paid > 0) {
				$totpaid_str .= '<div class="hireordata hiredeposit"><span class="Stile9">'.JText::_('VRCAMOUNTPAID').'</span><div align="right"><strong>'.self::numberFormat($tot_paid).' '.$currencyname.'</strong></div></div>';
				//only in case the remaining balance is greater than 1 to avoid commissions issues
				if ($diff_topay > 1) {
					$totpaid_str .= '<div class="hireordata hiredeposit"><span class="Stile9">'.JText::_('VRCTOTALREMAINING').'</span><div align="right"><strong>'.self::numberFormat($diff_topay).' '.$currencyname.'</strong></div></div>';
				}
			}
		}
		$parsed = str_replace("{order_total_paid}", $totpaid_str, $parsed);
		//
		
		return $parsed;
	}
	
	public static function parsePdfTemplate($tmpl, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $custdata, $tiname, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tprices, $topts, $tlocfee, $toohfee, $ttot, $tlink, $tfootm, $couponstr, $arrayinfopdf = array()) {
		$dbo = JFactory::getDBO();
		$vrc_tn = self::getTranslator();
		$parsed = $tmpl;
		/**
		 * We do not need to use relative paths for the logo, as they would be full paths not 
		 * compatible with some Virtual Servers. So the full URL is the preferred method.
		 */
		$parsed = str_replace("{logo}", $tlogo, $parsed);
		//
		//get sid and ts
		$lparts = explode("&sid=", $tlink);
		$lpartstwo = explode("&ts=", $lparts[1]);
		$sid = $lpartstwo[0];
		$lpartsthree = explode("&", $lpartstwo[1]);
		$ts = $lpartsthree[0];
		//
		//Confirmation Number
		if ($status == JText::_('VRCOMPLETED')) {
			$parsed = str_replace("{confirmnumb}", $sid.'_'.$ts, $parsed);
		} else {
			$parsed = str_replace("{confirmnumb}", '--------', $parsed);
		}
		//
		$parsed = str_replace("{company_name}", $tcname, $parsed);
		$parsed = str_replace("{order_id}", $orderid, $parsed);
		$statusclass = $status == JText::_('VRCOMPLETED') ? "green" : "red";
		$parsed = str_replace("{order_status_class}", $statusclass, $parsed);
		$parsed = str_replace("{order_status}", $status, $parsed);
		$parsed = str_replace("{order_date}", $todate, $parsed);
		$parsed = str_replace("{customer_info}", nl2br($custdata), $parsed);
		$parsed = str_replace("{item_name}", $tiname, $parsed);
		$parsed = str_replace("{pickup_date}", $tpickupdate, $parsed);
		$parsed = str_replace("{pickup_location}", $tpickupplace, $parsed);
		$parsed = str_replace("{dropoff_date}", $tdropdate, $parsed);
		$parsed = str_replace("{dropoff_location}", $tdropplace, $parsed);
		//order details
		$totalnet = 0;
		$totaltax = 0;
		$totalnet += $arrayinfopdf['tarminusiva'];
		$totaltax += $arrayinfopdf['tartax'];
		$orderdetails = "";
		$expdet = explode("\n", $tprices);
		$faredets = explode(":", $expdet[0]);
		$orderdetails .= '<tr><td align="left" style="border: 1px solid #DDDDDD;">'.$tiname.'<br/>'.$faredets[0];
		if (!empty($expdet[1])) {
			$attrfaredets = explode(":", $expdet[1]);
			if (strlen($attrfaredets[1]) > 0) {
				$orderdetails .= ' - '.$attrfaredets[0].':'.$attrfaredets[1];
			}
		}
		$fareprice = trim(str_replace($currencyname, "", $faredets[1]));
		$orderdetails .= '</td><td align="center" style="border: 1px solid #DDDDDD;">'.$arrayinfopdf['days'].'</td><td align="left" style="border: 1px solid #DDDDDD;">'.self::numberFormat($arrayinfopdf['tarminusiva']).' '.$currencyname.'</td><td align="left" style="border: 1px solid #DDDDDD;">'.self::numberFormat($arrayinfopdf['tartax']).' '.$currencyname.'</td><td align="left" style="border: 1px solid #DDDDDD;">'.self::numberFormat($fareprice).' '.$currencyname.'</td></tr>';
		//options
		if (strlen($topts) > 0) {
			$expopts = explode("\n", $topts);
			foreach ($expopts as $kexpopt=>$optinfo) {
				if (!empty($optinfo)) {
					$splitopt = explode(":", $optinfo);
					$optprice = trim(str_replace($currencyname, "", $splitopt[1]));
					$orderdetails .= '<tr><td align="left" style="border: 1px solid #DDDDDD;">'.$splitopt[0].'</td><td align="center" style="border: 1px solid #DDDDDD;">'.$arrayinfopdf['days'].'</td><td align="left" style="border: 1px solid #DDDDDD;">'.self::numberFormat($arrayinfopdf['opttaxnet'][$kexpopt]).' '.$currencyname.'</td><td align="left" style="border: 1px solid #DDDDDD;">'.self::numberFormat(($optprice - $arrayinfopdf['opttaxnet'][$kexpopt])).' '.$currencyname.'</td><td align="left" style="border: 1px solid #DDDDDD;">'.self::numberFormat($optprice).' '.$currencyname.'</td></tr>';
					$totalnet += $arrayinfopdf['opttaxnet'][$kexpopt];
					$totaltax += ($optprice - $arrayinfopdf['opttaxnet'][$kexpopt]);
				}
			}
		}
		//
		//locations fee
		if (!empty($tlocfee) && $tlocfee > 0) {
			$orderdetails .= '<tr><td align="left" style="border: 1px solid #DDDDDD;">'.JText::_('VRLOCFEETOPAY').'</td><td align="center" style="border: 1px solid #DDDDDD;">'.$arrayinfopdf['days'].'</td><td align="left" style="border: 1px solid #DDDDDD;">'.self::numberFormat($arrayinfopdf['locfeenet']).' '.$currencyname.'</td><td align="left" style="border: 1px solid #DDDDDD;">'.self::numberFormat(($tlocfee - $arrayinfopdf['locfeenet'])).' '.$currencyname.'</td><td align="left" style="border: 1px solid #DDDDDD;">'.self::numberFormat($tlocfee).' '.$currencyname.'</td></tr>';
			$totalnet += $arrayinfopdf['locfeenet'];
			$totaltax += ($tlocfee - $arrayinfopdf['locfeenet']);
		}
		//
		//out of hours fee
		if (!empty($toohfee) && $toohfee > 0) {
			$orderdetails .= '<tr><td align="left" style="border: 1px solid #DDDDDD;">'.JText::_('VRCOOHFEEAMOUNT').'</td><td align="center" style="border: 1px solid #DDDDDD;">'.$arrayinfopdf['days'].'</td><td align="left" style="border: 1px solid #DDDDDD;">'.self::numberFormat($arrayinfopdf['oohfeenet']).' '.$currencyname.'</td><td align="left" style="border: 1px solid #DDDDDD;">'.self::numberFormat(($toohfee - $arrayinfopdf['oohfeenet'])).' '.$currencyname.'</td><td align="left" style="border: 1px solid #DDDDDD;">'.self::numberFormat($toohfee).' '.$currencyname.'</td></tr>';
			$totalnet += $arrayinfopdf['oohfeenet'];
			$totaltax += ($toohfee - $arrayinfopdf['oohfeenet']);
		}
		//
		//coupon
		if (strlen($couponstr) > 0) {
			$expcoupon = explode(";", $couponstr);
			$orderdetails .= '<tr><td><br/></td><td></td><td></td><td></td><td></td></tr>';
			$orderdetails .= '<tr><td align="left" style="border: 1px solid #DDDDDD;">'.JText::_('VRCCOUPON').' '.$expcoupon[2].'</td><td style="border: 1px solid #DDDDDD;"></td><td style="border: 1px solid #DDDDDD;"></td><td style="border: 1px solid #DDDDDD;"></td><td align="left" style="border: 1px solid #DDDDDD;">- '.self::numberFormat($expcoupon[1]).' '.$currencyname.'</td></tr>';
		}
		//
		$parsed = str_replace("{order_details}", $orderdetails, $parsed);
		//
		//order total
		$strordtotal = '<tr><td><br/></td><td></td><td></td><td></td><td></td></tr>';
		$strordtotal .= '<tr><td align="left" bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"><strong>'.JText::_('VRLIBSIX').'</strong></td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"></td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;">'.self::numberFormat($totalnet).' '.$currencyname.'</td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;">'.self::numberFormat($totaltax).' '.$currencyname.'</td><td align="left" bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"><strong>'.self::numberFormat($ttot).' '.$currencyname.'</strong></td></tr>';
		if (array_key_exists('tot_paid', $arrayinfopdf) && floatval($arrayinfopdf['tot_paid']) > 0.00 && number_format($ttot, 2) != number_format($arrayinfopdf['tot_paid'], 2)) {
			$strordtotal .= '<tr><td align="left" bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"><strong>'.JText::_('VRCAMOUNTPAID').'</strong></td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"></td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"> </td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"> </td><td align="left" bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"><strong>'.self::numberFormat($arrayinfopdf['tot_paid']).' '.$currencyname.'</strong></td></tr>';
			// remaining balance
			$remaining = (float)$ttot - (float)$arrayinfopdf['tot_paid'];
			if ($remaining > 0.01) {
				$strordtotal .= '<tr><td align="left" bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"><strong>'.JText::_('VRCTOTALREMAINING').'</strong></td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"></td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"> </td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"> </td><td align="left" bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"><strong>'.self::numberFormat($remaining).' '.$currencyname.'</strong></td></tr>';
			}
		}
		$parsed = str_replace("{order_total}", $strordtotal, $parsed);
		//
						
		$parsed = str_replace("{order_link}", '<a href="'.$tlink.'">'.$tlink.'</a>', $parsed);
		$parsed = str_replace("{footer_emailtext}", $tfootm, $parsed);
		
		//custom fields replace
		preg_match_all('/\{customfield ([0-9]+)\}/U', $parsed, $matches);
		if (is_array($matches[1]) && @count($matches[1]) > 0) {
			$cfids = array();
			foreach ($matches[1] as $cfid ) {
				$cfids[] = $cfid;
			}
			$q = "SELECT * FROM `#__vikrentcar_custfields` WHERE `id` IN (".implode(", ", $cfids).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$cfields = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
			$vrc_tn->translateContents($cfields, '#__vikrentcar_custfields');
			$cfmap = array();
			if (is_array($cfields)) {
				foreach ($cfields as $cf) {
					$cfmap[trim(JText::_($cf['name']))] = $cf['id'];
				}
			}
			$cfmapreplace = array();
			$partsreceived = explode("\n", $custdata);
			if (count($partsreceived) > 0) {
				foreach ($partsreceived as $pst) {
					if (!empty($pst)) {
						$tmpdata = explode(":", $pst);
						if (array_key_exists(trim($tmpdata[0]), $cfmap)) {
							$cfmapreplace[$cfmap[trim($tmpdata[0])]] = trim($tmpdata[1]);
						}
					}
				}
			}
			foreach ($matches[1] as $cfid ) {
				if (array_key_exists($cfid, $cfmapreplace)) {
					$parsed = str_replace("{customfield ".$cfid."}", $cfmapreplace[$cfid], $parsed);
				} else {
					$parsed = str_replace("{customfield ".$cfid."}", "", $parsed);
				}
			}
		}
		//end custom fields replace
		
		return $parsed;
	}
	
	public static function sendCustMail($to, $subject, $ftitle, $ts, $custdata, $carname, $first, $second, $pricestr, $optstr, $tot, $link, $status, $place = "", $returnplace = "", $maillocfee = "", $mailoohfee = "", $orderid = "", $strcouponeff = "", $arrayinfopdf = array()) {
		$sendwhen = self::getSendEmailWhen();
		if ($sendwhen > 1 && $status == JText::_('VRSTANDBY')) {
			return true;
		}

		$origsubject = $subject;
		$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
		$dbo = JFactory::getDBO();
		$vrc_tn = self::getTranslator();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$currencyname = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$adminemail = $dbo->loadResult();
		$q = "SELECT `id`,`setting` FROM `#__vikrentcar_texts` WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		$vrc_tn->translateContents($ft, '#__vikrentcar_texts');
		$q = "SELECT `id`,`setting` FROM `#__vikrentcar_config` WHERE `param`='sendjutility';";
		$dbo->setQuery($q);
		$dbo->execute();
		$sendmethod = $dbo->loadAssocList();
		$useju = intval($sendmethod[0]['setting']) == 1 ? true : false;
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='sitelogo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$sitelogo = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$formdate = $dbo->loadResult();
		$nowtf = self::getTimeFormat();
		if ($formdate == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($formdate == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$footerordmail = $ft[0]['setting'];
		$textfooterordmail = strip_tags($footerordmail);
		//text part
		$msg = $ftitle . "\n\n";
		$msg .= JText::_('VRLIBEIGHT') . " " . date($df . ' ' . $nowtf, $ts) . "\n";
		$msg .= JText::_('VRLIBNINE') . ":\n" . $custdata . "\n";
		$msg .= JText::_('VRLIBTEN') . ": " . $carname . "\n";
		$msg .= JText::_('VRLIBELEVEN') . " " . date($df . ' ' . $nowtf, $first) . "\n";
		$msg .= JText::_('VRLIBTWELVE') . " " . date($df . ' ' . $nowtf, $second) . "\n";
		$msg .= (!empty ($place) ? JText::_('VRRITIROCAR') . ": " . $place . "\n" : "");
		$msg .= (!empty ($returnplace) ? JText::_('VRRETURNCARORD') . ": " . $returnplace . "\n" : "");
		$msg .= $pricestr . "\n";
		$msg .= $optstr . "\n";
		if (!empty ($maillocfee) && $maillocfee > 0) {
			$msg .= JText::_('VRLOCFEETOPAY') . ": " . self::numberFormat($maillocfee) . " " . $currencyname . "\n\n";
		}
		if (!empty ($mailoohfee) && $mailoohfee > 0) {
			$msg .= JText::_('VRCOOHFEEAMOUNT') . ": " . self::numberFormat($mailoohfee) . " " . $currencyname . "\n\n";
		}
		$msg .= JText::_('VRLIBSIX') . " " . $tot . " " . $currencyname . "\n";
		$msg .= JText::_('VRLIBSEVEN') . ": " . $status . "\n\n";
		$msg .= JText::_('VRLIBTENTHREE') . ": \n" . $link;
		$msg .= (strlen(trim($textfooterordmail)) > 0 ? "\n" . $textfooterordmail : "");
		//
		//html part
		$from_name = $adminemail;
		$from_address = $adminemail;
		$reply_name = $from_name;
		$reply_address = $from_address;
		$reply_address = $from_address;
		$error_delivery_name = $from_name;
		$error_delivery_address = $from_address;
		$to_name = $to;
		$to_address = $to;
		//vikrentcar 1.5
		$tmpl = self::loadEmailTemplate($orderid);
		//
		if (!$useju) {
			require_once (VRC_SITE_PATH.DS."class".DS."email_message.php");
			$email_message = new email_message_class;
			$email_message->SetEncodedEmailHeader("To", $to_address, $to_name);
			$email_message->SetEncodedEmailHeader("From", $from_address, $from_name);
			$email_message->SetEncodedEmailHeader("Reply-To", $reply_address, $reply_name);
			$email_message->SetHeader("Sender", $from_address);
			//			if (defined("PHP_OS")
			//			&& strcmp(substr(PHP_OS,0,3),"WIN"))
			//				$email_message->SetHeader("Return-Path",$error_delivery_address);

			$email_message->SetEncodedHeader("Subject", $subject);
			$attachlogo = false;
			if (!empty ($sitelogo) && @ file_exists(VRC_ADMIN_PATH . DS . 'resources' . DS . $sitelogo)) {
				$image = array (
				"FileName" => VRC_ADMIN_URI . "resources/" . $sitelogo, "Content-Type" => "automatic/name", "Disposition" => "inline");
				$email_message->CreateFilePart($image, $image_part);
				$image_content_id = $email_message->GetPartContentID($image_part);
				$attachlogo = true;
			}
			$tlogo = ($attachlogo ? "<img src=\"cid:" . $image_content_id . "\" alt=\"imglogo\"/>\n" : "");
		} else {
			$attachlogo = false;
			if (!empty ($sitelogo) && @ file_exists(VRC_ADMIN_PATH . DS . 'resources' . DS . $sitelogo)) {
				$attachlogo = true;
			}
			$tlogo = ($attachlogo ? "<img src=\"" . VRC_ADMIN_URI . "resources/" . $sitelogo . "\" alt=\"imglogo\"/>\n" : "");
		}
		//vikrentcar 1.5
		$tcname = $ftitle."\n";
		$todate = date($df . ' ' . $nowtf, $ts)."\n";
		$tcustdata = nl2br($custdata)."\n";
		$tiname = $carname."\n";
		$tpickupdate = date($df . ' ' . $nowtf, $first)."\n";
		$tdropdate = date($df . ' ' . $nowtf, $second)."\n";
		$tpickupplace = (!empty ($place) ? $place."\n" : "");
		$tdropplace = (!empty ($returnplace) ? $returnplace."\n" : "");
		$tprices = $pricestr;
		$topts = $optstr;
		$tlocfee = $maillocfee;
		$toohfee = $mailoohfee;
		$ttot = $tot."\n";
		$tlink = $link;
		$tfootm = $footerordmail;
		$hmess = self::parseEmailTemplate($tmpl, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $tcustdata, $tiname, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tprices, $topts, $tlocfee, $toohfee, $ttot, $tlink, $tfootm, $strcouponeff);
		//
		
		if (!$useju) {
			$email_message->CreateQuotedPrintableHTMLPart($hmess, "", $html_part);
			$email_message->CreateQuotedPrintableTextPart($email_message->WrapText($msg), "", $text_part);
			$alternative_parts = array (
				$text_part,
				$html_part
			);
			$email_message->CreateAlternativeMultipart($alternative_parts, $alternative_part);
			$related_parts = array (
				$alternative_part,
				$image_part
			);
			$email_message->AddRelatedMultipart($related_parts);
			$error = $email_message->Send();
			if (strcmp($error, "")) {
				//$msg = utf8_decode($msg);
				@ mail($to, $subject, $msg, "MIME-Version: 1.0" . "\r\n" . "Content-type: text/plain; charset=UTF-8");
			}
		} else {
			//VikRentCar 1.7 PDF
			$attachment = null;
			if ($status == JText::_('VRCOMPLETED') && VikRentCar::sendPDF() && file_exists(VRC_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php')) {
				list($pdfcont, $pdfparams) = self::loadPdfTemplate($orderid);
				$pdfhtml = self::parsePdfTemplate($pdfcont, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $custdata, $tiname, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tprices, $topts, $tlocfee, $toohfee, $ttot, $tlink, $tfootm, $strcouponeff, $arrayinfopdf);
				require_once(VRC_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php');
				$savepdfname = VRC_SITE_PATH . DS . "resources" . DS . "pdfs" . DS . $orderid.'_'.$ts.'.pdf';
				if (file_exists($savepdfname)) {
					unlink($savepdfname);
				}
				if (file_exists(VRC_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . "fonts" . DS . "dejavusans.php")) {
					$usepdffont = 'dejavusans';
				} else {
					$usepdffont = 'helvetica';
				}
				//Encoding could be also 'ISO-8859-1' rather than 'UTF-8'
				$pdf_page_format = is_array($pdfparams['pdf_page_format']) ? $pdfparams['pdf_page_format'] : constant($pdfparams['pdf_page_format']);
				$pdf = new TCPDF(constant($pdfparams['pdf_page_orientation']), constant($pdfparams['pdf_unit']), $pdf_page_format, true, 'UTF-8', false);
				$pdf->SetTitle($origsubject);
				//Header for each page of the pdf. Img, Img width (default 30mm), Title, Subtitle
				if ($pdfparams['show_header'] == 1 && count($pdfparams['header_data']) > 0) {
					$pdf->SetHeaderData($pdfparams['header_data'][0], $pdfparams['header_data'][1], $pdfparams['header_data'][2], $pdfparams['header_data'][3], $pdfparams['header_data'][4], $pdfparams['header_data'][5]);
				}
				//Change some currencies to their unicode (decimal) value
				$unichr_map = array('EUR' => 8364, 'USD' => 36, 'AUD' => 36, 'CAD' => 36, 'GBP' => 163);
				if (array_key_exists($currencyname, $unichr_map)) {
					$pdfhtml = str_replace($currencyname, $pdf->unichr($unichr_map[$currencyname]), $pdfhtml);
				}
				//header and footer fonts
				$pdf->setHeaderFont(array($usepdffont, '', $pdfparams['header_font_size']));
				$pdf->setFooterFont(array($usepdffont, '', $pdfparams['footer_font_size']));
				//margins
				$pdf->SetMargins(constant($pdfparams['pdf_margin_left']), constant($pdfparams['pdf_margin_top']), constant($pdfparams['pdf_margin_right']));
				$pdf->SetHeaderMargin(constant($pdfparams['pdf_margin_header']));
				$pdf->SetFooterMargin(constant($pdfparams['pdf_margin_footer']));
				//
				$pdf->SetAutoPageBreak(true, constant($pdfparams['pdf_margin_bottom']));
				$pdf->setImageScale(constant($pdfparams['pdf_image_scale_ratio']));
				$pdf->SetFont($usepdffont, '', (int)$pdfparams['body_font_size']);

				if ($pdfparams['show_header'] == 0 || !(count($pdfparams['header_data']) > 0)) {
					$pdf->SetPrintHeader(false);
				}
				if ($pdfparams['show_footer'] == 0) {
					$pdf->SetPrintFooter(false);
				}
				$pdfhtmlpages = explode('{vrc_add_pdf_page}', $pdfhtml);
				foreach ($pdfhtmlpages as $htmlpage) {
					if (strlen(str_replace(' ', '', trim($htmlpage))) > 0) {
						$pdf->AddPage();
						$pdf->writeHTML($htmlpage, true, false, true, false, '');
						$pdf->lastPage();
					}
				}
				$pdf->Output($savepdfname, 'F');
				$attachment = $savepdfname;
			}
			//end VikRentCar 1.7 PDF
			$hmess = '<html>'."\n".'<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>'."\n".'<body>'.$hmess.'</body>'."\n".'</html>';

			$vrc_app = self::getVrcApplication();
			$adsendermail = self::getSenderMail();
			$vrc_app->sendMail($adsendermail, $adsendermail, $to, $reply_address, $subject, $hmess, true, 'base64', $attachment);
		}
		//
		
		return true;
	}

	public static function sendCustMailFromBack($to, $subject, $ftitle, $ts, $custdata, $carname, $first, $second, $pricestr, $optstr, $tot, $link, $status, $place = "", $returnplace = "", $maillocfee = "", $mailoohfee = "", $orderid = "", $strcouponeff = "", $arrayinfopdf = "", $sendpdf = true) {
		//this function is called from the administrator section only
		$origsubject = $subject;
		$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
		$dbo = JFactory::getDBO();
		$vrc_tn = self::getTranslator();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$currencyname = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$adminemail = $dbo->loadResult();
		$q = "SELECT `id`,`setting` FROM `#__vikrentcar_texts` WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		$vrc_tn->translateContents($ft, '#__vikrentcar_texts');
		$q = "SELECT `id`,`setting` FROM `#__vikrentcar_config` WHERE `param`='sendjutility';";
		$dbo->setQuery($q);
		$dbo->execute();
		$sendmethod = $dbo->loadAssocList();
		$useju = intval($sendmethod[0]['setting']) == 1 ? true : false;
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='sitelogo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$sitelogo = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$formdate = $dbo->loadResult();
		$nowtf = self::getTimeFormat();
		if ($formdate == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($formdate == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$footerordmail = $ft[0]['setting'];
		$textfooterordmail = strip_tags($footerordmail);
		//text part
		$msg = $ftitle . "\n\n";
		$msg .= JText::_('VRLIBEIGHT') . " " . date($df . ' ' . $nowtf, $ts) . "\n";
		$msg .= JText::_('VRLIBNINE') . ":\n" . $custdata . "\n";
		$msg .= JText::_('VRLIBTEN') . ": " . $carname . "\n";
		$msg .= JText::_('VRLIBELEVEN') . " " . date($df . ' ' . $nowtf, $first) . "\n";
		$msg .= JText::_('VRLIBTWELVE') . " " . date($df . ' ' . $nowtf, $second) . "\n";
		$msg .= (!empty ($place) ? JText::_('VRRITIROCAR') . ": " . $place . "\n" : "");
		$msg .= (!empty ($returnplace) ? JText::_('VRRETURNCARORD') . ": " . $returnplace . "\n" : "");
		$msg .= $pricestr . "\n";
		$msg .= $optstr . "\n";
		if (!empty ($maillocfee) && $maillocfee > 0) {
			$msg .= JText::_('VRLOCFEETOPAY') . ": " . self::numberFormat($maillocfee) . " " . $currencyname . "\n\n";
		}
		if (!empty ($mailoohfee) && $mailoohfee > 0) {
			$msg .= JText::_('VRCOOHFEEAMOUNT') . ": " . self::numberFormat($mailoohfee) . " " . $currencyname . "\n\n";
		}
		$msg .= JText::_('VRLIBSIX') . " " . $tot . " " . $currencyname . "\n";
		$msg .= JText::_('VRLIBSEVEN') . ": " . $status . "\n\n";
		$msg .= JText::_('VRLIBTENTHREE') . ": \n" . $link;
		$msg .= (strlen(trim($textfooterordmail)) > 0 ? "\n" . $textfooterordmail : "");
		//
		//html part
		$from_name = $adminemail;
		$from_address = $adminemail;
		$reply_name = $from_name;
		$reply_address = $from_address;
		$reply_address = $from_address;
		$error_delivery_name = $from_name;
		$error_delivery_address = $from_address;
		$to_name = $to;
		$to_address = $to;
		//vikrentcar 1.5
		$tmpl = self::loadEmailTemplate($orderid);
		//
		if (!$useju) {
			require_once (VRC_SITE_PATH.DS."class".DS."email_message.php");
			$email_message = new email_message_class;
			$email_message->SetEncodedEmailHeader("To", $to_address, $to_name);
			$email_message->SetEncodedEmailHeader("From", $from_address, $from_name);
			$email_message->SetEncodedEmailHeader("Reply-To", $reply_address, $reply_name);
			$email_message->SetHeader("Sender", $from_address);
			//			if (defined("PHP_OS")
			//			&& strcmp(substr(PHP_OS,0,3),"WIN"))
			//				$email_message->SetHeader("Return-Path",$error_delivery_address);

			$email_message->SetEncodedHeader("Subject", $subject);
			$attachlogo = false;
			if (!empty ($sitelogo) && @ file_exists(VRC_ADMIN_PATH . DS . 'resources' . DS . $sitelogo)) {
				$image = array (
				"FileName" => VRC_ADMIN_URI . "resources/" . $sitelogo, "Content-Type" => "automatic/name", "Disposition" => "inline");
				$email_message->CreateFilePart($image, $image_part);
				$image_content_id = $email_message->GetPartContentID($image_part);
				$attachlogo = true;
			}
			$tlogo = ($attachlogo ? "<img src=\"cid:" . $image_content_id . "\" alt=\"imglogo\"/>\n" : "");
		} else {
			$attachlogo = false;
			if (!empty ($sitelogo) && @ file_exists(VRC_ADMIN_PATH . DS . 'resources' . DS . $sitelogo)) {
				$attachlogo = true;
			}
			$tlogo = ($attachlogo ? "<img src=\"" . VRC_ADMIN_URI . "resources/" . $sitelogo . "\" alt=\"imglogo\"/>\n" : "");
		}
		//vikrentcar 1.5
		$tcname = $ftitle."\n";
		$todate = date($df . ' ' . $nowtf, $ts)."\n";
		$tcustdata = nl2br($custdata)."\n";
		$tiname = $carname."\n";
		$tpickupdate = date($df . ' ' . $nowtf, $first)."\n";
		$tdropdate = date($df . ' ' . $nowtf, $second)."\n";
		$tpickupplace = (!empty ($place) ? $place."\n" : "");
		$tdropplace = (!empty ($returnplace) ? $returnplace."\n" : "");
		$tprices = $pricestr;
		$topts = $optstr;
		$tlocfee = $maillocfee;
		$toohfee = $mailoohfee;
		$ttot = $tot."\n";
		$tlink = $link;
		$tfootm = $footerordmail;
		$hmess = self::parseEmailTemplate($tmpl, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $tcustdata, $tiname, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tprices, $topts, $tlocfee, $toohfee, $ttot, $tlink, $tfootm, $strcouponeff);
		//
		
		if (!$useju) {
			$email_message->CreateQuotedPrintableHTMLPart($hmess, "", $html_part);
			$email_message->CreateQuotedPrintableTextPart($email_message->WrapText($msg), "", $text_part);
			$alternative_parts = array (
				$text_part,
				$html_part
			);
			$email_message->CreateAlternativeMultipart($alternative_parts, $alternative_part);
			$related_parts = array (
				$alternative_part,
				$image_part
			);
			$email_message->AddRelatedMultipart($related_parts);
			$error = $email_message->Send();
			if (strcmp($error, "")) {
				//$msg = utf8_decode($msg);
				@ mail($to, $subject, $msg, "MIME-Version: 1.0" . "\r\n" . "Content-type: text/plain; charset=UTF-8");
			}
		} else {
			//VikRentCar 1.7 PDF
			$attachment = null;
			if ($status == JText::_('VRCOMPLETED') && $sendpdf && VikRentCar::sendPDF() && file_exists(VRC_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php')) {
				list($pdfcont, $pdfparams) = self::loadPdfTemplate($orderid);
				$pdfhtml = self::parsePdfTemplate($pdfcont, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $custdata, $tiname, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tprices, $topts, $tlocfee, $toohfee, $ttot, $tlink, $tfootm, $strcouponeff, $arrayinfopdf);
				//images with src images/ must be converted into ../images/ for teh PDF
				$pdfhtml = str_replace('<img src="images/', '<img src="../images/', $pdfhtml);
				//
				require_once(VRC_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php');
				$savepdfname = VRC_SITE_PATH . DS . "resources" . DS . "pdfs" . DS . $orderid.'_'.$ts.'.pdf';
				if (file_exists($savepdfname)) {
					unlink($savepdfname);
				}
				if (file_exists(VRC_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . "fonts" . DS . "dejavusans.php")) {
					$usepdffont = 'dejavusans';
				} else {
					$usepdffont = 'helvetica';
				}
				//Encoding could be also 'ISO-8859-1' rather than 'UTF-8'
				$pdf_page_format = is_array($pdfparams['pdf_page_format']) ? $pdfparams['pdf_page_format'] : constant($pdfparams['pdf_page_format']);
				$pdf = new TCPDF(constant($pdfparams['pdf_page_orientation']), constant($pdfparams['pdf_unit']), $pdf_page_format, true, 'UTF-8', false);
				$pdf->SetTitle($origsubject);
				//Header for each page of the pdf. Img, Img width (default 30mm), Title, Subtitle
				if ($pdfparams['show_header'] == 1 && count($pdfparams['header_data']) > 0) {
					$pdf->SetHeaderData($pdfparams['header_data'][0], $pdfparams['header_data'][1], $pdfparams['header_data'][2], $pdfparams['header_data'][3], $pdfparams['header_data'][4], $pdfparams['header_data'][5]);
				}
				//Change some currencies to their unicode (decimal) value
				$unichr_map = array('EUR' => 8364, 'USD' => 36, 'AUD' => 36, 'CAD' => 36, 'GBP' => 163);
				if (array_key_exists($currencyname, $unichr_map)) {
					$pdfhtml = str_replace($currencyname, $pdf->unichr($unichr_map[$currencyname]), $pdfhtml);
				}
				//header and footer fonts
				$pdf->setHeaderFont(array($usepdffont, '', $pdfparams['header_font_size']));
				$pdf->setFooterFont(array($usepdffont, '', $pdfparams['footer_font_size']));
				//margins
				$pdf->SetMargins(constant($pdfparams['pdf_margin_left']), constant($pdfparams['pdf_margin_top']), constant($pdfparams['pdf_margin_right']));
				$pdf->SetHeaderMargin(constant($pdfparams['pdf_margin_header']));
				$pdf->SetFooterMargin(constant($pdfparams['pdf_margin_footer']));
				//
				$pdf->SetAutoPageBreak(true, constant($pdfparams['pdf_margin_bottom']));
				$pdf->setImageScale(constant($pdfparams['pdf_image_scale_ratio']));
				$pdf->SetFont($usepdffont, '', (int)$pdfparams['body_font_size']);

				if ($pdfparams['show_header'] == 0 || !(count($pdfparams['header_data']) > 0)) {
					$pdf->SetPrintHeader(false);
				}
				if ($pdfparams['show_footer'] == 0) {
					$pdf->SetPrintFooter(false);
				}
				//
				$pdfhtmlpages = explode('{vrc_add_pdf_page}', $pdfhtml);
				foreach ($pdfhtmlpages as $htmlpage) {
					if (strlen(str_replace(' ', '', trim($htmlpage))) > 0) {
						$pdf->AddPage();
						$pdf->writeHTML($htmlpage, true, false, true, false, '');
						$pdf->lastPage();
					}
				}
				$pdf->Output($savepdfname, 'F');
				$attachment = $savepdfname;
			}
			//end VikRentCar 1.7 PDF
			$hmess = '<html>'."\n".'<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>'."\n".'<body>'.$hmess.'</body>'."\n".'</html>';
			
			$vrc_app = self::getVrcApplication();
			$adsendermail = self::getSenderMail();
			$vrc_app->sendMail($adsendermail, $adsendermail, $to, $reply_address, $subject, $hmess, true, 'base64', $attachment);
		}
		//
		
		return true;
	}

	public static function parseCheckinPdfTemplate($tmpl, $damage_png, $distinctive_features, $car_damages, $order_details) {
		$dbo = JFactory::getDBO();
		$vrc_tn = self::getTranslator();
		$parsed = $tmpl;
		$parsed = str_replace("{car_damages_image}", '<img src="'.$damage_png.'"/>', $parsed);
		//car distinctive features replace
		preg_match_all('/\{carfeature ([a-zA-Z0-9]+)\}/U', $parsed, $matches);
		if (is_array($matches[1]) && @count($matches[1]) > 0) {
			$docheck = (bool)(count($distinctive_features) > 0);
			foreach ($matches[1] as $reqf) {
				$feature_found = false;
				if ($docheck) {
					foreach ($distinctive_features as $dfk => $dfv) {
						if (stripos($dfk, $reqf) !== false) {
							$feature_found = $dfk;
							if (strlen(trim($dfk)) == strlen(trim($reqf))) {
								break;
							}
						}
					}
				}
				if ($feature_found !== false && strlen($distinctive_features[$feature_found]) > 0) {
					$parsed = str_replace("{carfeature ".$reqf."}", JText::_($feature_found).': '.$distinctive_features[$feature_found].'<br/>', $parsed);
				} else {
					$parsed = str_replace("{carfeature ".$reqf."}", "", $parsed);
				}
			}
		}
		//end car distinctive features
		//damages list
		if (count($car_damages) > 0 && self::getDamageShowType() > 2) {
			$car_damages = array_reverse($car_damages, true);
			$damage_string = '<ol>'."\n";
			foreach ($car_damages as $key => $damage) {
				$damage_string .= '<li>'.$damage['notes'].'</li>'."\n";
			}
			$damage_string .= '</ol>'."\n";
			$parsed = str_replace("{car_damages_explanation}", $damage_string, $parsed);
		} else {
			$parsed = str_replace("{car_damages_explanation}", "", $parsed);
		}
		//end damages list
		//other common fields parsing
		$custdata = $order_details['custdata'];
		$parsed = str_replace("{order_id}", $order_details['id'], $parsed);
		$parsed = str_replace("{order_date}", $order_details['order_date'], $parsed);
		$parsed = str_replace("{customer_info}", nl2br($custdata), $parsed);
		$parsed = str_replace("{item_name}", $order_details['item_name'], $parsed);
		$parsed = str_replace("{pickup_date}", $order_details['pickup_date'], $parsed);
		$parsed = str_replace("{pickup_location}", $order_details['pickup_location'], $parsed);
		$parsed = str_replace("{dropoff_date}", $order_details['dropoff_date'], $parsed);
		$parsed = str_replace("{dropoff_location}", $order_details['dropoff_location'], $parsed);
		//custom fields replace
		preg_match_all('/\{customfield ([0-9]+)\}/U', $parsed, $matches);
		if (is_array($matches[1]) && @count($matches[1]) > 0) {
			$cfids = array();
			foreach ($matches[1] as $cfid ) {
				$cfids[] = $cfid;
			}
			$q = "SELECT * FROM `#__vikrentcar_custfields` WHERE `id` IN (".implode(", ", $cfids).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$cfields = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
			$vrc_tn->translateContents($cfields, '#__vikrentcar_custfields');
			$cfmap = array();
			if (is_array($cfields)) {
				foreach ($cfields as $cf) {
					$cfmap[trim(JText::_($cf['name']))] = $cf['id'];
				}
			}
			$cfmapreplace = array();
			$partsreceived = explode("\n", $custdata);
			if (count($partsreceived) > 0) {
				foreach ($partsreceived as $pst) {
					if (!empty($pst)) {
						$tmpdata = explode(":", $pst);
						if (array_key_exists(trim($tmpdata[0]), $cfmap)) {
							$cfmapreplace[$cfmap[trim($tmpdata[0])]] = trim($tmpdata[1]);
						}
					}
				}
			}
			foreach ($matches[1] as $cfid ) {
				if (array_key_exists($cfid, $cfmapreplace)) {
					$parsed = str_replace("{customfield ".$cfid."}", $cfmapreplace[$cfid], $parsed);
				} else {
					$parsed = str_replace("{customfield ".$cfid."}", "", $parsed);
				}
			}
		}
		//end custom fields replace
		//
		
		return $parsed;
	}

	public static function generateCheckinPdf($to, $subject, $ftitle, $ts, $custdata, $carname, $first, $second, $pricestr, $optstr, $tot, $link, $status, $place = "", $returnplace = "", $maillocfee = "", $mailoohfee = "", $orderid = "", $strcouponeff = "", $arrayinfopdf = "") {
		//this function is called in the administrator site
		$origsubject = $subject;
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$currencyname = $dbo->loadResult();
		$q = "SELECT `id`,`setting` FROM `#__vikrentcar_texts` WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='sitelogo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$sitelogo = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$formdate = $dbo->loadResult();
		$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=".(int)$orderid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		$order_details = $dbo->getNumRows() > 0 ? $dbo->loadAssoc() : array();
		$nowtf = self::getTimeFormat();
		if ($formdate == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($formdate == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$footerordmail = $ft[0]['setting'];
		$attachlogo = false;
		if (!empty ($sitelogo) && @ file_exists(VRC_ADMIN_PATH . DS . 'resources' . DS . $sitelogo)) {
			$attachlogo = true;
		}
		$tlogo = ($attachlogo ? "<img src=\"" . VRC_ADMIN_URI . "resources/" . $sitelogo . "\" alt=\"imglogo\"/>\n" : "");
		//vikrentcar 1.5
		$tcname = $ftitle."\n";
		$todate = date($df . ' ' . $nowtf, $ts)."\n";
		$tcustdata = nl2br($custdata)."\n";
		$tiname = $carname."\n";
		$tpickupdate = date($df . ' ' . $nowtf, $first)."\n";
		$tdropdate = date($df . ' ' . $nowtf, $second)."\n";
		$tpickupplace = (!empty ($place) ? $place."\n" : "");
		$tdropplace = (!empty ($returnplace) ? $returnplace."\n" : "");
		$tprices = $pricestr;
		$topts = $optstr;
		$tlocfee = $maillocfee;
		$toohfee = $mailoohfee;
		$ttot = $tot."\n";
		$tlink = $link;
		$tfootm = $footerordmail;
		//
		if ($status == JText::_('VRCOMPLETED') && file_exists(VRC_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php')) {
			list($pdfcont, $pdfparams) = self::loadPdfTemplate($orderid);
			$pdfhtml = self::parsePdfTemplate($pdfcont, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $custdata, $tiname, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tprices, $topts, $tlocfee, $toohfee, $ttot, $tlink, $tfootm, $strcouponeff, $arrayinfopdf);
			//images with src images/ must be converted into ../images/ for the PDF
			$pdfhtml = str_replace('<img src="images/', '<img src="../images/', $pdfhtml);
			//
			require_once(VRC_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php');
			$savepdfname = VRC_SITE_PATH . DS . "resources" . DS . "pdfs" . DS . $orderid.'_'.$ts.'_checkin.pdf';
			if (file_exists($savepdfname)) {
				unlink($savepdfname);
			}
			if (file_exists(VRC_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . "fonts" . DS . "dejavusans.php")) {
				$usepdffont = 'dejavusans';
			} else {
				$usepdffont = 'helvetica';
			}
			//Encoding could be also 'ISO-8859-1' rather than 'UTF-8'
			$pdf_page_format = is_array($pdfparams['pdf_page_format']) ? $pdfparams['pdf_page_format'] : constant($pdfparams['pdf_page_format']);
			$pdf = new TCPDF(constant($pdfparams['pdf_page_orientation']), constant($pdfparams['pdf_unit']), $pdf_page_format, true, 'UTF-8', false);
			$pdf->SetTitle($origsubject);
			//Header for each page of the pdf. Img, Img width (default 30mm), Title, Subtitle
			if ($pdfparams['show_header'] == 1 && count($pdfparams['header_data']) > 0) {
				$pdf->SetHeaderData($pdfparams['header_data'][0], $pdfparams['header_data'][1], $pdfparams['header_data'][2], $pdfparams['header_data'][3], $pdfparams['header_data'][4], $pdfparams['header_data'][5]);
			}
			//Change some currencies to their unicode (decimal) value
			$unichr_map = array('EUR' => 8364, 'USD' => 36, 'AUD' => 36, 'CAD' => 36, 'GBP' => 163);
			if (array_key_exists($currencyname, $unichr_map)) {
				$pdfhtml = str_replace($currencyname, $pdf->unichr($unichr_map[$currencyname]), $pdfhtml);
			}
			//header and footer fonts
			$pdf->setHeaderFont(array($usepdffont, '', $pdfparams['header_font_size']));
			$pdf->setFooterFont(array($usepdffont, '', $pdfparams['footer_font_size']));
			//margins
			$pdf->SetMargins(constant($pdfparams['pdf_margin_left']), constant($pdfparams['pdf_margin_top']), constant($pdfparams['pdf_margin_right']));
			$pdf->SetHeaderMargin(constant($pdfparams['pdf_margin_header']));
			$pdf->SetFooterMargin(constant($pdfparams['pdf_margin_footer']));
			//
			$pdf->SetAutoPageBreak(true, constant($pdfparams['pdf_margin_bottom']));
			$pdf->setImageScale(constant($pdfparams['pdf_image_scale_ratio']));
			$pdf->SetFont($usepdffont, '', (int)$pdfparams['body_font_size']);

			if ($pdfparams['show_header'] == 0 || !(count($pdfparams['header_data']) > 0)) {
				$pdf->SetPrintHeader(false);
			}
			if ($pdfparams['show_footer'] == 0) {
				$pdf->SetPrintFooter(false);
			}
			//
			$pdfhtmlpages = explode('{vrc_add_pdf_page}', $pdfhtml);
			foreach ($pdfhtmlpages as $htmlpage) {
				if (strlen(str_replace(' ', '', trim($htmlpage))) > 0) {
					$pdf->AddPage();
					$pdf->writeHTML($htmlpage, true, false, true, false, '');
					$pdf->lastPage();
				}
			}
			//BEGIN VRC 1.9 - Car Damage and Statuses + Signature spaces
			$q = "SELECT `o`.`idcar`,`o`.`carindex`,`c`.`params` FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_cars` `c` ON `o`.`idcar`=`c`.`id` WHERE `o`.`id`=".(int)$orderid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			$car_data = $dbo->loadAssoc();
			if (!empty($car_data['carindex']) && !empty($car_data['params'])) {
				$damage_png = VRC_SITE_URI.'helpers/car_damages/'.$car_data['idcar'].'_'.$car_data['carindex'].'.png';
				if (!file_exists(VRC_SITE_PATH.DS.'helpers'.DS.'car_damages'.DS.$car_data['idcar'].'_'.$car_data['carindex'].'.png')) {
					$damage_png = VRC_SITE_URI.'helpers/car_damages/car_inspection.png';
				}
				//Distinctive Features
				$distinctive_features = array();
				$car_params = json_decode($car_data['params'], true);
				$car_damages = is_array($car_params) && array_key_exists('damages', $car_params) && array_key_exists($car_data['carindex'], $car_params['damages']) ? $car_params['damages'][$car_data['carindex']] : array();
				if (is_array($car_params) && @count($car_params['features']) > 0) {
					foreach ($car_params['features'] as $cind => $cfeatures) {
						if ($cind != $car_data['carindex']) {
							continue;
						}
						foreach ($cfeatures as $fname => $fval) {
							if (strlen($fval)) {
								$distinctive_features[$fname] = $fval;
							}
						}
					}
				}
				//
				//prepare data for additional fields replace in customer check-in document
				$order_details['order_date'] = $todate;
				$order_details['item_name'] = $tiname;
				$order_details['pickup_date'] = $tpickupdate;
				$order_details['pickup_location'] = $tpickupplace;
				$order_details['dropoff_date'] = $tdropdate;
				$order_details['dropoff_location'] = $tdropplace;
				//
				$checkinpdfcont = self::loadCheckinPdfTemplate($order_details);
				$checkinpdfhtml = self::parseCheckinPdfTemplate($checkinpdfcont, $damage_png, $distinctive_features, $car_damages, $order_details);
				$pdf->AddPage();
				$pdf->writeHTML($checkinpdfhtml, true, false, true, false, '');
				$pdf->lastPage();
			}
			//END - Car Damage and Statuses + Signature spaces
			$pdf->Output($savepdfname, 'F');
			if (!file_exists($savepdfname)) {
				return false;
			}
		}
		
		return true;
	}

	public static function parseSpecialTokens($order, $tmpl) {
		$dbo = JFactory::getDBO();
		$vrc_tn = self::getTranslator();
		$currency = self::getCurrencyName();
		$vrcdateformat = self::getDateFormat();
		$nowtf = self::getTimeFormat();
		if ($vrcdateformat == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($vrcdateformat == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$parsed = $tmpl;

		$cust_name = '';
		if (!empty($order['customer_name'])) {
			$cust_name = $order['customer_name'];
		}
		$pickloc = '';
		if (!empty($order['idplace'])) {
			$pickloc = self::getPlaceName($order['idplace'], $vrc_tn);
		}
		$droploc = '';
		if (!empty($order['idreturnplace'])) {
			$droploc = self::getPlaceName($order['idreturnplace'], $vrc_tn);
		}
		$car_name = '';
		if (!empty($order['idcar'])) {
			$car_info = self::getCarInfo($order['idcar'], $vrc_tn);
			if (count($car_info)) {
				$car_name = $car_info['name'];
			}
		}
		$remaining_bal = $order['order_total'] - (float)$order['totpaid'];

		$parsed = str_replace("{order_id}", $order['id'], $parsed);
		$parsed = str_replace("{customer_name}", $cust_name, $parsed);
		$parsed = str_replace("{pickup_date}", date($df.' '.$nowtf, $order['ritiro']), $parsed);
		$parsed = str_replace("{dropoff_date}", date($df.' '.$nowtf, $order['consegna']), $parsed);
		$parsed = str_replace("{pickup_place}", $pickloc, $parsed);
		$parsed = str_replace("{dropoff_place}", $droploc, $parsed);
		$parsed = str_replace("{num_days}", $order['days'], $parsed);
		$parsed = str_replace("{car_name}", $car_name, $parsed);
		$parsed = str_replace("{total}", $currency . ' ' . self::numberFormat($order['order_total']), $parsed);
		$parsed = str_replace("{total_paid}", $currency . ' ' . self::numberFormat($order['totpaid']), $parsed);
		$parsed = str_replace("{remaining_balance}", $currency . ' ' . self::numberFormat($remaining_bal), $parsed);

		return $parsed;
	}

	public static function paypalForm($imp, $tax, $sid, $ts, $carname, $currencysymb = "") {
		$dbo = JFactory::getDBO();
		$depositmess = "";
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='paytotal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		if (intval($s[0]['setting']) == 0) {
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='payaccpercent';";
			$dbo->setQuery($q);
			$dbo->execute();
			$per = $dbo->loadAssocList();
			if ($per[0]['setting'] > 0) {
				$imp = $imp * $per[0]['setting'] / 100;
				$tax = $tax * $per[0]['setting'] / 100;
				$depositmess = "<p><strong>" . JText::_('VRLEAVEDEPOSIT') . " " . (number_format($imp + $tax, 2)) . " " . $currencysymb . "</strong></p><br/>";
			}
		}
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='ccpaypal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$acc = $dbo->loadAssocList();
		$q = "SELECT `id`,`setting` FROM `#__vikrentcar_texts` WHERE `param`='paymentname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$payname = $dbo->loadAssocList();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='currencycodepp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$paypalcurcode = trim($dbo->loadResult());
		$itname = (empty ($payname[0]['setting']) ? $carname : $payname[0]['setting']);
		$returl = JURI::root() . "index.php?option=com_vikrentcar&task=vieworder&sid=" . $sid . "&ts=" . $ts;
		$notifyurl = JURI::root() . "index.php?option=com_vikrentcar&task=notifypayment&sid=" . $sid . "&ts=" . $ts;
		$form = "<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">\n";
		$form .= "<input type=\"hidden\" name=\"business\" value=\"" . $acc[0]['setting'] . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"cmd\" value=\"_xclick\"/>\n";
		$form .= "<input type=\"hidden\" name=\"amount\" value=\"" . number_format($imp, 2) . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"item_name\" value=\"" . $itname . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"item_number\" value=\"" . $carname . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"quantity\" value=\"1\"/>\n";
		$form .= "<input type=\"hidden\" name=\"tax\" value=\"" . number_format($tax, 2) . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"shipping\" value=\"0.00\"/>\n";
		$form .= "<input type=\"hidden\" name=\"currency_code\" value=\"" . $paypalcurcode . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"no_shipping\" value=\"1\"/>\n";
		$form .= "<input type=\"hidden\" name=\"rm\" value=\"2\"/>\n";
		$form .= "<input type=\"hidden\" name=\"notify_url\" value=\"" . $notifyurl . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"return\" value=\"" . $returl . "\"/>\n";
		$form .= "<input type=\"image\" src=\"https://www.paypal.com/en_US/i/btn/btn_paynow_SM.gif\" name=\"submit\" alt=\"PayPal - The safer, easier way to pay online!\">\n";
		$form .= "</form>\n";
		return $depositmess . $form;
	}
	
	public static function sendPDF() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='sendpdf';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}
	
	/**
	 * @deprecated 	1.12 - no longer configurable but still used.
	 * 
	 * We use the CMS's internal and native email sending functions.
	 */
	public static function sendJutility() {
		// always use the native email sending function
		return true;
		
		/*
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='sendjutility';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
		*/
	}

	public static function saveOldOrders() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='oldorders';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}

	public static function allowStats() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='allowstats';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}

	public static function sendMailStats() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='sendmailstats';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}

	public static function getPlaceName($idplace, $vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_places` WHERE `id`=" . $dbo->quote($idplace) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			return '';
		}
		$p = $dbo->loadAssocList();
		if (is_object($vrc_tn)) {
			$vrc_tn->translateContents($p, '#__vikrentcar_places');
		}
		return $p[0]['name'];
	}

	public static function getPlaceInfo($idplace, $vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentcar_places` WHERE `id`=" . intval($idplace) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			return array();
		}
		$p = $dbo->loadAssocList();
		if (is_object($vrc_tn)) {
			$vrc_tn->translateContents($p, '#__vikrentcar_places');
		}
		return $p[0];
	}

	public static function getCategoryName($idcat, $vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_categories` WHERE `id`=" . $dbo->quote($idcat) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			return '';
		}
		$p = $dbo->loadAssocList();
		if (is_object($vrc_tn)) {
			$vrc_tn->translateContents($p, '#__vikrentcar_categories');
		}
		return $p[0]['name'];
	}
	
	public static function getOutOfHoursFees($place, $returnplace, $from, $to, $car, $multiple_cars = false) {
		$dbo = JFactory::getDBO();
		$nowtf = VikRentCar::getTimeFormat();
		$oohfees = array();
		$pickup_fee = array();
		$dropoff_fee = array();
		$id_cars = array();
		if (count($car) > 0) {
			if (array_key_exists(0, $car)) {
				//multiple cars
				foreach ($car as $c) {
					$id_cars[$c['id']] = (int)$c['id'];
				}
			} else {
				//single car
				$id_cars[$car['id']] = (int)$car['id'];
			}
		}
		if (!empty($place) && !empty($returnplace)) {
			$base_from = mktime(0, 0, 0, date('n', $from), date('j', $from), date('Y', $from));
			$pickup_seconds = $from - $base_from;
			$base_to = mktime(0, 0, 0, date('n', $to), date('j', $to), date('Y', $to));
			$dropoff_seconds = $to - $base_to;
			$info_from = getdate($from);
			$info_to = getdate($to);
			$pickup_wday = $info_from['wday'];
			$dropoff_wday = $info_to['wday'];
			$q = "SELECT `oohf`.*,`oohf_x`.`idlocation` FROM `#__vikrentcar_oohfees` AS `oohf` LEFT JOIN `#__vikrentcar_oohfees_locxref` `oohf_x` ON `oohf_x`.`idooh`=`oohf`.`id` WHERE ".
				"( (`oohf`.`from` <= ".$pickup_seconds." AND `oohf`.`to` >= ".$pickup_seconds." AND `oohf`.`from` < `oohf`.`to`) OR ".
				"(`oohf`.`from` > ".$pickup_seconds." AND `oohf`.`to` >= ".$pickup_seconds." AND `oohf`.`from` > `oohf`.`to`) OR ".
				"(`oohf`.`from` <= ".$pickup_seconds." AND `oohf`.`to` < ".$pickup_seconds." AND `oohf`.`from` > `oohf`.`to`) )".
				"AND (`oohf_x`.`idlocation`=".(int)$place." AND `oohf`.`wdays` LIKE '%-".(int)$pickup_wday."-%' AND (`oohf`.`type`=1 OR `oohf`.`type`=3)) GROUP BY `oohf`.`id` ORDER BY `oohf`.`id` DESC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$pickup_fee = $dbo->loadAssocList();
				foreach ($pickup_fee as $k => $v) {
					$car_valid = false;
					$fee_cars = explode(',', $v['idcars']);
					foreach ($fee_cars as $fc) {
						$fc = intval(str_replace("-", "", $fc));
						if ($multiple_cars === true && $fc > 0) {
							$id_cars[$fc] = $fc;
							$car_valid = true;
							continue;
						}
						if (in_array($fc, $id_cars)) {
							$car_valid = true;
						}
					}
					if ($car_valid === false) {
						unset($pickup_fee[$k]);
					}
				}
			}
			$q = "SELECT `oohf`.*,`oohf_x`.`idlocation` FROM `#__vikrentcar_oohfees` AS `oohf` LEFT JOIN `#__vikrentcar_oohfees_locxref` `oohf_x` ON `oohf_x`.`idooh`=`oohf`.`id` WHERE ".
				"( (`oohf`.`from` <= ".$dropoff_seconds." AND `oohf`.`to` >= ".$dropoff_seconds." AND `oohf`.`from` < `oohf`.`to`) OR ".
				"(`oohf`.`from` > ".$dropoff_seconds." AND `oohf`.`to` >= ".$dropoff_seconds." AND `oohf`.`from` > `oohf`.`to`) OR ".
				"(`oohf`.`from` <= ".$dropoff_seconds." AND `oohf`.`to` < ".$dropoff_seconds." AND `oohf`.`from` > `oohf`.`to`) )".
				"AND (`oohf_x`.`idlocation`=".(int)$returnplace." AND `oohf`.`wdays` LIKE '%-".(int)$dropoff_wday."-%' AND (`oohf`.`type`=2 OR `oohf`.`type`=3)) GROUP BY `oohf`.`id` ORDER BY `oohf`.`id` DESC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$dropoff_fee = $dbo->loadAssocList();
				foreach ($dropoff_fee as $k => $v) {
					$car_valid = false;
					$fee_cars = explode(',', $v['idcars']);
					foreach ($fee_cars as $fc) {
						$fc = intval(str_replace("-", "", $fc));
						if ($multiple_cars === true && $fc > 0) {
							$id_cars[$fc] = $fc;
							$car_valid = true;
							continue;
						}
						if (in_array($fc, $id_cars)) {
							$car_valid = true;
						}
					}
					if ($car_valid === false) {
						unset($dropoff_fee[$k]);
					}
				}
			}
			if (count($pickup_fee) > 0 || count($dropoff_fee) > 0) {
				$oohfees['idiva'] = '';
				$oohfees['idcars'] = $id_cars;
				$oohfees['pickup'] = 0;
				$oohfees['pickup_ooh'] = '';
				$oohfees['dropoff'] = 0;
				$oohfees['dropoff_ooh'] = '';
				$oohfees['cost'] = 0;
				if ($multiple_cars === true) {
					foreach ($id_cars as $idcar) {
						$oohfees[$idcar]['cost'] = 0;
						if (count($pickup_fee) > 0) {
							foreach ($pickup_fee as $k => $v) {
								$fee_cars = explode(',', $v['idcars']);
								if (in_array("-".$idcar."-", $fee_cars)) {
									$oohfees[$idcar]['pickup'] = 1;
									$oohfees[$idcar]['pickup_ooh'] = date($nowtf, ($base_from + $v['from'])).' - '.date($nowtf, ($base_from + $v['to']));
									$oohfees[$idcar]['cost'] += $pickup_fee[$k]['pickcharge'];
									break;
								}
							}
						}
						if (count($dropoff_fee) > 0) {
							foreach ($dropoff_fee as $k => $v) {
								$fee_cars = explode(',', $v['idcars']);
								if (in_array("-".$idcar."-", $fee_cars)) {
									$oohfees[$idcar]['dropoff'] = 1;
									$oohfees[$idcar]['dropoff_ooh'] = date($nowtf, ($base_from + $v['from'])).' - '.date($nowtf, ($base_from + $v['to']));
									$oohfees[$idcar]['cost'] += $dropoff_fee[$k]['dropcharge'];
									$oohfees[$idcar]['cost'] = $dropoff_fee[$k]['maxcharge'] > 0 && $dropoff_fee[$k]['maxcharge'] < $oohfees[$idcar]['cost'] ? $dropoff_fee[$k]['maxcharge'] : $oohfees[$idcar]['cost'];
									break;
								}
							}
						}
					}
				} else {
					if (count($pickup_fee) > 0) {
						reset($pickup_fee);
						$oohfees['pickup'] = 1;
						$ind = key($pickup_fee);
						$oohfees['idiva'] = $pickup_fee[$ind]['idiva'];
						$oohfees['pickup_ooh'] = date($nowtf, ($base_from + $pickup_fee[$ind]['from'])).' - '.date($nowtf, ($base_from + $pickup_fee[$ind]['to']));
						$oohfees['cost'] += $pickup_fee[$ind]['pickcharge'];
					}
					if (count($dropoff_fee) > 0) {
						reset($dropoff_fee);
						$oohfees['dropoff'] = 1;
						$ind = key($dropoff_fee);
						$oohfees['idiva'] = $dropoff_fee[$ind]['idiva'];
						$oohfees['dropoff_ooh'] = date($nowtf, ($base_to + $dropoff_fee[$ind]['from'])).' - '.date($nowtf, ($base_to + $dropoff_fee[$ind]['to']));
						$oohfees['cost'] += $dropoff_fee[$ind]['dropcharge'];
						$oohfees['cost'] = $dropoff_fee[$ind]['maxcharge'] > 0 && $dropoff_fee[$ind]['maxcharge'] < $oohfees['cost'] ? $dropoff_fee[$ind]['maxcharge'] : $oohfees['cost'];
					}
				}
			}
		}
		
		return $oohfees;
	}

	public static function sayOohFeePlusIva($cost, $idiva) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('ivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('ivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 0) {
			$q = "SELECT `aliq` FROM `#__vikrentcar_iva` WHERE `id`='" . $idiva . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * $subt / 100);
				return $op;
			}
		}
		return $cost;
	}

	public static function sayOohFeeMinusIva($cost, $idiva) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('ivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('ivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 1) {
			$q = "SELECT `aliq` FROM `#__vikrentcar_iva` WHERE `id`='" . $idiva . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * 100 / $subt);
				return $op;
			}
		}
		return $cost;
	}

	public static function getLocFee($from, $to) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentcar_locfees` WHERE (`from`=" . $dbo->quote($from) . " AND `to`=" . $dbo->quote($to) . ") OR (`to`=" . $dbo->quote($from) . " AND `from`=" . $dbo->quote($to) . " AND `invert`='1');";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$res = $dbo->loadAssocList();
			return $res[0];
		}
		return false;
	}

	public static function sayLocFeePlusIva($cost, $idiva, $order=array()) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('ivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('ivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 0) {
			//VRC 1.7 Rev.2
			$locationvat = strlen($order['locationvat']) > 0 ? $order['locationvat'] : (count($order) == 0 ? $session->get('vrcLocationTaxRate', '') : '');
			if (strlen($locationvat) > 0) {
				$subt = 100 + $locationvat;
				$op = ($cost * $subt / 100);
				return $op;
			}
			//
			$q = "SELECT `aliq` FROM `#__vikrentcar_iva` WHERE `id`='" . $idiva . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * $subt / 100);
				return $op;
			}
		}
		return $cost;
	}

	public static function sayLocFeeMinusIva($cost, $idiva, $order=array()) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('ivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('ivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 1) {
			//VRC 1.7 Rev.2
			$locationvat = strlen($order['locationvat']) > 0 ? $order['locationvat'] : (count($order) == 0 ? $session->get('vrcLocationTaxRate', '') : '');
			if (strlen($locationvat) > 0) {
				$subt = 100 + $locationvat;
				$op = ($cost * 100 / $subt);
				return $op;
			}
			//
			$q = "SELECT `aliq` FROM `#__vikrentcar_iva` WHERE `id`='" . $idiva . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * 100 / $subt);
				return $op;
			}
		}
		return $cost;
	}
	
	public static function sortCarPrices($arr) {
		$newarr = array ();
		foreach ($arr as $k => $v) {
			$newarr[$k] = $v['cost'];
		}
		asort($newarr);
		$sorted = array ();
		foreach ($newarr as $k => $v) {
			$sorted[$k] = $arr[$k];
		}
		return $sorted;
	}
	
	public static function sortResults($arr) {
		$newarr = array ();
		foreach ($arr as $k => $v) {
			$newarr[$k] = $v[0]['cost'];
		}
		asort($newarr);
		$sorted = array ();
		foreach ($newarr as $k => $v) {
			$sorted[$k] = $arr[$k];
		}
		return $sorted;
	}

	public static function filterNightsSeasonsCal ($arr_nights) {
		$nights = array();
		foreach ($arr_nights as $night) {
			if (intval(trim($night)) > 0) {
				$nights[] = intval(trim($night));
			}
		}
		sort($nights);
		return array_unique($nights);
	}

	/**
	 * Needed for the rates overview View of the admin.
	 * We return a static list of default nights for the overview.
	 * 
	 * @return 	string
	 */
	public static function getSeasoncalNights() {
		return '1,3,7,10';
	}

	public static function getSeasonRangeTs($from, $to, $year) {
		$sfrom = 0;
		$sto = 0;
		$tsbase = mktime(0, 0, 0, 1, 1, $year);
		$curyear = $year;
		$tsbasetwo = $tsbase;
		$curyeartwo = $year;
		if ($from > $to) {
			//between two years
			$curyeartwo += 1;
			$tsbasetwo = mktime(0, 0, 0, 1, 1, $curyeartwo);
		}
		$sfrom = ($tsbase + $from);
		$sto = ($tsbasetwo + $to);
		if ($curyear % 4 == 0 && ($curyear % 100 != 0 || $curyear % 400 == 0)) {
			//leap years
			$infoseason = getdate($sfrom);
			$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
			if ($infoseason[0] >= $leapts) {
				$sfrom += 86400;
				if ($curyear == $curyeartwo) {
					$sto += 86400;
				}
			}
		} elseif ($curyeartwo % 4 == 0 && ($curyeartwo % 100 != 0 || $curyeartwo % 400 == 0)) {
			//leap years
			$infoseason = getdate($sto);
			$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
			if ($infoseason[0] >= $leapts) {
				$sto += 86400;
			}
		}
		return array($sfrom, $sto);
	}

	public static function sortSeasonsRangeTs($all_seasons) {
		$sorted = array();
		$map = array();
		foreach ($all_seasons as $key => $season) {
			$map[$key] = $season['from_ts'];
		}
		asort($map);
		foreach ($map as $key => $s) {
			$sorted[] = $all_seasons[$key];
		}
		return $sorted;
	}

	public static function formatSeasonDates($from_ts, $to_ts) {
		$one = getdate($from_ts);
		$two = getdate($to_ts);
		$months_map = array(
			1 => JText::_('VRSHORTMONTHONE'),
			2 => JText::_('VRSHORTMONTHTWO'),
			3 => JText::_('VRSHORTMONTHTHREE'),
			4 => JText::_('VRSHORTMONTHFOUR'),
			5 => JText::_('VRSHORTMONTHFIVE'),
			6 => JText::_('VRSHORTMONTHSIX'),
			7 => JText::_('VRSHORTMONTHSEVEN'),
			8 => JText::_('VRSHORTMONTHEIGHT'),
			9 => JText::_('VRSHORTMONTHNINE'),
			10 => JText::_('VRSHORTMONTHTEN'),
			11 => JText::_('VRSHORTMONTHELEVEN'),
			12 => JText::_('VRSHORTMONTHTWELVE')
		);
		$mday_map = array(
			1 => JText::_('VRMDAYFRIST'),
			2 => JText::_('VRMDAYSECOND'),
			3 => JText::_('VRMDAYTHIRD'),
			'generic' => JText::_('VRMDAYNUMGEN')
		);
		if ($one['year'] == $two['year']) {
			return $one['year'].' '.$months_map[(int)$one['mon']].' '.$one['mday'].'<sup>'.(array_key_exists((int)substr($one['mday'], -1), $mday_map) && ($one['mday'] < 10 || $one['mday'] > 20) ? $mday_map[(int)substr($one['mday'], -1)] : $mday_map['generic']).'</sup> - '.$months_map[(int)$two['mon']].' '.$two['mday'].'<sup>'.(array_key_exists((int)substr($two['mday'], -1), $mday_map) && ($two['mday'] < 10 || $two['mday'] > 20) ? $mday_map[(int)substr($two['mday'], -1)] : $mday_map['generic']).'</sup>';
		}
		return $months_map[(int)$one['mon']].' '.$one['mday'].'<sup>'.(array_key_exists((int)substr($one['mday'], -1), $mday_map) && ($one['mday'] < 10 || $one['mday'] > 20) ? $mday_map[(int)substr($one['mday'], -1)] : $mday_map['generic']).'</sup> '.$one['year'].' - '.$months_map[(int)$two['mon']].' '.$two['mday'].'<sup>'.(array_key_exists((int)substr($two['mday'], -1), $mday_map) && ($two['mday'] < 10 || $two['mday'] > 20) ? $mday_map[(int)substr($two['mday'], -1)] : $mday_map['generic']).'</sup> '.$two['year'];
	}

	public static function loadRestrictions($filters = true, $cars = array()) {
		$restrictions = array();
		$dbo = JFactory::getDBO();
		if (!$filters) {
			$q = "SELECT * FROM `#__vikrentcar_restrictions`;";
		} else {
			if (count($cars) == 0) {
				$q = "SELECT * FROM `#__vikrentcar_restrictions` WHERE `allcars`=1;";
			} else {
				$clause = array();
				foreach ($cars as $idr) {
					if (empty($idr)) continue;
					$clause[] = "`idcars` LIKE '%-".intval($idr)."-%'";
				}
				if (count($clause) > 0) {
					$q = "SELECT * FROM `#__vikrentcar_restrictions` WHERE `allcars`=1 OR (`allcars`=0 AND (".implode(" OR ", $clause)."));";
				} else {
					$q = "SELECT * FROM `#__vikrentcar_restrictions` WHERE `allcars`=1;";
				}
			}
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$allrestrictions = $dbo->loadAssocList();
			foreach ($allrestrictions as $k=>$res) {
				if (!empty($res['month'])) {
					$restrictions[$res['month']] = $res;
				} else {
					if (!isset($restrictions['range'])) {
						$restrictions['range'] = array();
					}
					$restrictions['range'][$k] = $res;
				}
			}
		}
		return $restrictions;
	}

	public static function globalRestrictions($restrictions) {
		$ret = array();
		if (count($restrictions) > 0) {
			foreach($restrictions as $kr => $rr) {
				if ($kr == 'range') {
					foreach ($rr as $kd => $dr) {
						if ($dr['allcars'] == 1) {
							$ret['range'][$kd] = $restrictions[$kr][$kd];
						}
					}
				} else {
					if ($rr['allcars'] == 1) {
						$ret[$kr] = $restrictions[$kr];
					}
				}
			}
		}
		return $ret;
	}

	public static function parseSeasonRestrictions ($first, $second, $daysdiff, $restrictions) {
		$season_restrictions = array();
		$restrcheckin = getdate($first);
		$restrcheckout = getdate($second);
		if (array_key_exists($restrcheckin['mon'], $restrictions)) {
			//restriction found for this month, checking:
			$season_restrictions['id'] = $restrictions[$restrcheckin['mon']]['id'];
			$season_restrictions['name'] = $restrictions[$restrcheckin['mon']]['name'];
			$season_restrictions['allowed'] = true; //set to false when these nights are not allowed
			if (strlen($restrictions[$restrcheckin['mon']]['wday']) > 0) {
				//Week Day Arrival Restriction
				$rvalidwdays = array($restrictions[$restrcheckin['mon']]['wday']);
				if (strlen($restrictions[$restrcheckin['mon']]['wdaytwo']) > 0) {
					$rvalidwdays[] = $restrictions[$restrcheckin['mon']]['wdaytwo'];
				}
				$season_restrictions['wdays'] = $rvalidwdays;
			} elseif (!empty($restrictions[$restrcheckin['mon']]['ctad']) || !empty($restrictions[$restrcheckin['mon']]['ctdd'])) {
				if (!empty($restrictions[$restrcheckin['mon']]['ctad'])) {
					$season_restrictions['cta'] = explode(',', $restrictions[$restrcheckin['mon']]['ctad']);
				}
				if (!empty($restrictions[$restrcheckin['mon']]['ctdd'])) {
					$season_restrictions['ctd'] = explode(',', $restrictions[$restrcheckin['mon']]['ctdd']);
				}
			}
			if (!empty($restrictions[$restrcheckin['mon']]['maxlos']) && $restrictions[$restrcheckin['mon']]['maxlos'] > 0 && $restrictions[$restrcheckin['mon']]['maxlos'] > $restrictions[$restrcheckin['mon']]['minlos']) {
				$season_restrictions['maxlos'] = $restrictions[$restrcheckin['mon']]['maxlos'];
				if ($daysdiff > $restrictions[$restrcheckin['mon']]['maxlos']) {
					$season_restrictions['allowed'] = false;
				}
			}
			if ($daysdiff < $restrictions[$restrcheckin['mon']]['minlos']) {
				$season_restrictions['allowed'] = false;
			}
			$season_restrictions['minlos'] = $restrictions[$restrcheckin['mon']]['minlos'];
		} elseif (array_key_exists('range', $restrictions)) {
			foreach($restrictions['range'] as $restr) {
				if ($restr['dfrom'] <= $first && $restr['dto'] >= $first) {
					//restriction found for this date range, checking:
					$season_restrictions['id'] = $restr['id'];
					$season_restrictions['name'] = $restr['name'];
					$season_restrictions['allowed'] = true; //set to false when these nights are not allowed
					if (strlen($restr['wday']) > 0) {
						//Week Day Arrival Restriction
						$rvalidwdays = array($restr['wday']);
						if (strlen($restr['wdaytwo']) > 0) {
							$rvalidwdays[] = $restr['wdaytwo'];
						}
						$season_restrictions['wdays'] = $rvalidwdays;
					} elseif (!empty($restr['ctad']) || !empty($restr['ctdd'])) {
						if (!empty($restr['ctad'])) {
							$season_restrictions['cta'] = explode(',', $restr['ctad']);
						}
						if (!empty($restr['ctdd'])) {
							$season_restrictions['ctd'] = explode(',', $restr['ctdd']);
						}
					}
					if (!empty($restr['maxlos']) && $restr['maxlos'] > 0 && $restr['maxlos'] > $restr['minlos']) {
						$season_restrictions['maxlos'] = $restr['maxlos'];
						if ($daysdiff > $restr['maxlos']) {
							$season_restrictions['allowed'] = false;
						}
					}
					if ($daysdiff < $restr['minlos']) {
						$season_restrictions['allowed'] = false;
					}
					$season_restrictions['minlos'] = $restr['minlos'];
				}
			}
		}

		return $season_restrictions;
	}

	public static function compareSeasonRestrictionsNights($restrictions) {
		$base_compare = array();
		$base_nights = 0;
		foreach ($restrictions as $nights => $restr) {
			$base_compare = $restr;
			$base_nights = $nights;
			break;
		}
		foreach ($restrictions as $nights => $restr) {
			if ($nights == $base_nights) {
				continue;
			}
			$diff = array_diff($base_compare, $restr);
			if (count($diff) > 0 && array_key_exists('id', $diff)) {
				//return differences only if the Restriction ID is different: ignore allowed, wdays, minlos, maxlos.
				//only one Restriction per time should be applied to certain Season Dates but check just in case.
				return $diff;
			}
		}

		return array();
	}
	
	public static function carRestrictions($carid, $restrictions) {
		$ret = array();
		if (!empty($carid) && count($restrictions) > 0) {
			foreach($restrictions as $kr => $rr) {
				if ($kr == 'range') {
					foreach ($rr as $kd => $dr) {
						if ($dr['allcars'] == 0 && !empty($dr['idcars'])) {
							$allcars = explode(';', $dr['idcars']);
							if (in_array('-'.$carid.'-', $allcars)) {
								$ret['range'][$kd] = $restrictions[$kr][$kd];
							}
						}
					}
				} else {
					if ($rr['allcars'] == 0 && !empty($rr['idcars'])) {
						$allcars = explode(';', $rr['idcars']);
						if (in_array('-'.$carid.'-', $allcars)) {
							$ret[$kr] = $restrictions[$kr];
						}
					}
				}
			}
		}
		return $ret;
	}
	
	public static function validateCarRestriction($carrestr, $restrcheckin, $restrcheckout, $daysdiff) {
		$restrictionerrmsg = '';
		$restrictions_affcount = 0;
		if (array_key_exists($restrcheckin['mon'], $carrestr)) {
			//restriction found for this month, checking:
			$restrictions_affcount++;
			if (strlen($carrestr[$restrcheckin['mon']]['wday']) > 0) {
				$rvalidwdays = array($carrestr[$restrcheckin['mon']]['wday']);
				if (strlen($carrestr[$restrcheckin['mon']]['wdaytwo']) > 0) {
					$rvalidwdays[] = $carrestr[$restrcheckin['mon']]['wdaytwo'];
				}
				if (!in_array($restrcheckin['wday'], $rvalidwdays)) {
					$restrictionerrmsg = JText::sprintf('VRRESTRTIPWDAYARRIVAL', self::sayMonth($restrcheckin['mon']), self::sayWeekDay($carrestr[$restrcheckin['mon']]['wday']).(strlen($carrestr[$restrcheckin['mon']]['wdaytwo']) > 0 ? '/'.self::sayWeekDay($carrestr[$restrcheckin['mon']]['wdaytwo']) : ''));
				} elseif ($carrestr[$restrcheckin['mon']]['multiplyminlos'] == 1) {
					if (($daysdiff % $carrestr[$restrcheckin['mon']]['minlos']) != 0) {
						$restrictionerrmsg = JText::sprintf('VRRESTRTIPMULTIPLYMINLOS', self::sayMonth($restrcheckin['mon']), $carrestr[$restrcheckin['mon']]['minlos']);
					}
				}
				$comborestr = self::parseJsDrangeWdayCombo($carrestr[$restrcheckin['mon']]);
				if (count($comborestr) > 0) {
					if (array_key_exists($restrcheckin['wday'], $comborestr)) {
						if (!in_array($restrcheckout['wday'], $comborestr[$restrcheckin['wday']])) {
							$restrictionerrmsg = JText::sprintf('VRRESTRTIPWDAYCOMBO', self::sayMonth($restrcheckin['mon']), self::sayWeekDay($comborestr[$restrcheckin['wday']][0]).(count($comborestr[$restrcheckin['wday']]) == 2 ? '/'.self::sayWeekDay($comborestr[$restrcheckin['wday']][1]) : ''), self::sayWeekDay($restrcheckin['wday']));
						}
					}
				}
			} elseif (!empty($carrestr[$restrcheckin['mon']]['ctad']) || !empty($carrestr[$restrcheckin['mon']]['ctdd'])) {
				if (!empty($carrestr[$restrcheckin['mon']]['ctad'])) {
					$ctarestrictions = explode(',', $carrestr[$restrcheckin['mon']]['ctad']);
					if (in_array('-'.$restrcheckin['wday'].'-', $ctarestrictions)) {
						$restrictionerrmsg = JText::sprintf('VRRESTRERRWDAYCTAMONTH', self::sayWeekDay($restrcheckin['wday']), self::sayMonth($restrcheckin['mon']));
					}
				}
				if (!empty($carrestr[$restrcheckin['mon']]['ctdd'])) {
					$ctdrestrictions = explode(',', $carrestr[$restrcheckin['mon']]['ctdd']);
					if (in_array('-'.$restrcheckout['wday'].'-', $ctdrestrictions)) {
						$restrictionerrmsg = JText::sprintf('VRRESTRERRWDAYCTDMONTH', self::sayWeekDay($restrcheckout['wday']), self::sayMonth($restrcheckin['mon']));
					}
				}
			}
			if (!empty($carrestr[$restrcheckin['mon']]['maxlos']) && $carrestr[$restrcheckin['mon']]['maxlos'] > 0 && $carrestr[$restrcheckin['mon']]['maxlos'] > $carrestr[$restrcheckin['mon']]['minlos']) {
				if ($daysdiff > $carrestr[$restrcheckin['mon']]['maxlos']) {
					$restrictionerrmsg = JText::sprintf('VRRESTRTIPMAXLOSEXCEEDED', self::sayMonth($restrcheckin['mon']), $carrestr[$restrcheckin['mon']]['maxlos']);
				}
			}
			if ($daysdiff < $carrestr[$restrcheckin['mon']]['minlos']) {
				$restrictionerrmsg = JText::sprintf('VRRESTRTIPMINLOSEXCEEDED', self::sayMonth($restrcheckin['mon']), $carrestr[$restrcheckin['mon']]['minlos']);
			}
		} elseif (array_key_exists('range', $carrestr)) {
			$restrictionsvalid = true;
			foreach($carrestr['range'] as $restr) {
				if ($restr['dfrom'] <= $restrcheckin[0] && ($restr['dto'] + 82799) >= $restrcheckin[0]) {
					//restriction found for this date range, checking:
					$restrictions_affcount++;
					if (strlen($restr['wday']) > 0) {
						$rvalidwdays = array($restr['wday']);
						if (strlen($restr['wdaytwo']) > 0) {
							$rvalidwdays[] = $restr['wdaytwo'];
						}
						if (!in_array($restrcheckin['wday'], $rvalidwdays)) {
							$restrictionsvalid = false;
							$restrictionerrmsg = JText::sprintf('VRRESTRTIPWDAYARRIVALRANGE', self::sayWeekDay($restr['wday']).(strlen($restr['wdaytwo']) > 0 ? '/'.self::sayWeekDay($restr['wdaytwo']) : ''));
						} elseif ($restr['multiplyminlos'] == 1) {
							if (($daysdiff % $restr['minlos']) != 0) {
								$restrictionsvalid = false;
								$restrictionerrmsg = JText::sprintf('VRRESTRTIPMULTIPLYMINLOSRANGE', $restr['minlos']);
							}
						}
						$comborestr = self::parseJsDrangeWdayCombo($restr);
						if (count($comborestr) > 0) {
							if (array_key_exists($restrcheckin['wday'], $comborestr)) {
								if (!in_array($restrcheckout['wday'], $comborestr[$restrcheckin['wday']])) {
									$restrictionsvalid = false;
									$restrictionerrmsg = JText::sprintf('VRRESTRTIPWDAYCOMBORANGE', self::sayWeekDay($comborestr[$restrcheckin['wday']][0]).(count($comborestr[$restrcheckin['wday']]) == 2 ? '/'.self::sayWeekDay($comborestr[$restrcheckin['wday']][1]) : ''), self::sayWeekDay($restrcheckin['wday']));
								}
							}
						}
					} elseif (!empty($restr['ctad']) || !empty($restr['ctdd'])) {
						if (!empty($restr['ctad'])) {
							$ctarestrictions = explode(',', $restr['ctad']);
							if (in_array('-'.$restrcheckin['wday'].'-', $ctarestrictions)) {
								$restrictionerrmsg = JText::sprintf('VRRESTRERRWDAYCTARANGE', self::sayWeekDay($restrcheckin['wday']));
							}
						}
						if (!empty($restr['ctdd'])) {
							$ctdrestrictions = explode(',', $restr['ctdd']);
							if (in_array('-'.$restrcheckout['wday'].'-', $ctdrestrictions)) {
								$restrictionerrmsg = JText::sprintf('VRRESTRERRWDAYCTDRANGE', self::sayWeekDay($restrcheckout['wday']));
							}
						}
					}
					if (!empty($restr['maxlos']) && $restr['maxlos'] > 0 && $restr['maxlos'] > $restr['minlos']) {
						if ($daysdiff > $restr['maxlos']) {
							$restrictionsvalid = false;
							$restrictionerrmsg = JText::sprintf('VRRESTRTIPMAXLOSEXCEEDEDRANGE', $restr['maxlos']);
						}
					}
					if ($daysdiff < $restr['minlos']) {
						$restrictionsvalid = false;
						$restrictionerrmsg = JText::sprintf('VRRESTRTIPMINLOSEXCEEDEDRANGE', $restr['minlos']);
					}
					if ($restrictionsvalid == false) {
						break;
					}
				}
			}
		}
		// Check global restriction of Min LOS
		if (empty($restrictionerrmsg) && count($carrestr) && $restrictions_affcount <= 0) {
			// Check global MinLOS (only in case there are no restrictions affecting these dates or no restrictions at all)
			$globminlos = self::setDropDatePlus();
			if ($globminlos > 1 && $daysdiff < $globminlos) {
				$restrictionerrmsg = JText::sprintf('VRRESTRERRMINLOSEXCEEDEDRANGE', $globminlos);
			}
		}
		//

		return $restrictionerrmsg;
	}

	public static function parseJsDrangeWdayCombo ($drestr) {
		$combo = array();
		if (strlen($drestr['wday']) > 0 && strlen($drestr['wdaytwo']) > 0 && !empty($drestr['wdaycombo'])) {
			$cparts = explode(':', $drestr['wdaycombo']);
			foreach($cparts as $kc => $cw) {
				if (!empty($cw)) {
					$nowcombo = explode('-', $cw);
					$combo[intval($nowcombo[0])][] = intval($nowcombo[1]);
				}
			}
		}
		return $combo;
	}

	public static function applySeasonalPrices($arr, $from, $to, $pickup) {
		$dbo = JFactory::getDBO();
		$vrc_tn = self::getTranslator();
		$carschange = array();
		$one = getdate($from);
		//leap years
		if (($one['year'] % 4) == 0 && ($one['year'] % 100 != 0 || $one['year'] % 400 == 0)) {
			$isleap = true;
		} else {
			$isleap = false;
		}
		//
		$baseone = mktime(0, 0, 0, 1, 1, $one['year']);
		$tomidnightone = intval($one['hours']) * 3600;
		$tomidnightone += intval($one['minutes']) * 60;
		$sfrom = $from - $baseone - $tomidnightone;
		$fromdayts = mktime(0, 0, 0, $one['mon'], $one['mday'], $one['year']);
		$two = getdate($to);
		$basetwo = mktime(0, 0, 0, 1, 1, $two['year']);
		$tomidnighttwo = intval($two['hours']) * 3600;
		$tomidnighttwo += intval($two['minutes']) * 60;
		$sto = $to - $basetwo - $tomidnighttwo;
		//Hourly Prices
		if ($sfrom === $sto) {
			$sto += 86399;
		}
		//End Hourly Prices
		//leap years, last day of the month of the season
		if ($isleap) {
			$leapts = mktime(0, 0, 0, 2, 29, $two['year']);
			if ($two[0] >= $leapts) {
				$sfrom -= 86400;
				$sto -= 86400;
			}
		}
		//
		$q = "SELECT * FROM `#__vikrentcar_seasons` WHERE (`locations`='0' OR `locations`='" . $pickup . "') AND (" .
		 ($sto > $sfrom ? "(`from`<=" . $sfrom . " AND `to`>=" . $sto . ") " : "") .
		 ($sto > $sfrom ? "OR (`from`<=" . $sfrom . " AND `to`>=" . $sfrom . ") " : "(`from`<=" . $sfrom . " AND `to`<=" . $sfrom . " AND `from`>`to`) ") .
		 ($sto > $sfrom ? "OR (`from`<=" . $sto . " AND `to`>=" . $sto . ") " : "OR (`from`>=" . $sto . " AND `to`>=" . $sto . " AND `from`>`to`) ") .
		 ($sto > $sfrom ? "OR (`from`>=" . $sfrom . " AND `from`<=" . $sto . " AND `to`>=" . $sfrom . " AND `to`<=" . $sto . ")" : "OR (`from`>=" . $sfrom . " AND `from`>" . $sto . " AND `to`<" . $sfrom . " AND `to`<=" . $sto . " AND `from`>`to`)") .
		 ($sto > $sfrom ? " OR (`from`<=" . $sfrom . " AND `from`<=" . $sto . " AND `to`<" . $sfrom . " AND `to`<" . $sto . " AND `from`>`to`) OR (`from`>" . $sfrom . " AND `from`>" . $sto . " AND `to`>=" . $sfrom . " AND `to`>=" . $sto . " AND `from`>`to`)" : " OR (`from` <=" . $sfrom . " AND `to` >=" . $sfrom . " AND `from` >" . $sto . " AND `to` >" . $sto . " AND `from` < `to`)") .
		 ($sto > $sfrom ? " OR (`from` >=" . $sfrom . " AND `from` <" . $sto . " AND `to` <" . $sfrom . " AND `to` <" . $sto . " AND `from` > `to`)" : " OR (`from` <" . $sfrom . " AND `to` >=" . $sto . " AND `from` <=" . $sto . " AND `to` <" . $sfrom . " AND `from` < `to`)"). //VRC 1.12 Else part is for Season Jan 6 to Feb 12 - Booking Dec 31 to Jan 8
		 ($sto > $sfrom ? " OR (`from` >" . $sfrom . " AND `from` >" . $sto . " AND `to` >=" . $sfrom . " AND `to` <" . $sto . " AND `from` > `to`)" : " OR (`from` >=" . $sfrom . " AND `from` >" . $sto . " AND `to` >" . $sfrom . " AND `to` >" . $sto . " AND `from` < `to`) OR (`from` <" . $sfrom . " AND `from` <" . $sto . " AND `to` <" . $sfrom . " AND `to` <=" . $sto . " AND `from` < `to`)"). //VRC 1.12 Else part for seasons Dec 25 to Dec 31, Jan 2 to Jan 5 - Booking Dec 20 to Jan 7
		");";
		$dbo->setQuery($q);
		$dbo->execute();
		$totseasons = $dbo->getNumRows();
		if ($totseasons > 0) {
			$seasons = $dbo->loadAssocList();
			$vrc_tn->translateContents($seasons, '#__vikrentcar_seasons');
			$applyseasons = false;
			$mem = array();
			foreach ($arr as $k => $a) {
				$mem[$k]['daysused'] = 0;
				$mem[$k]['sum'] = array();
			}
			foreach ($seasons as $s) {
				//Special Price tied to the year
				if (!empty($s['year']) && $s['year'] > 0) {
					//VRC 1.12 - do not skip seasons tied to the year for bookings between two years
					if ($one['year'] != $s['year'] && $two['year'] != $s['year']) {
						//VRC 1.12 - tied to the year can be set for prev year (Dec 27 to Jan 3) and booking can be Jan 1 to Jan 3 - do not skip in this case
						if (($one['year'] - $s['year']) != 1 || $s['from'] < $s['to']) {
							continue;
						}
						//VRC 1.12 - tied to 2016 going through Jan 2017: dates of December 2017 should skip this speacial price
						if (($one['year'] - $s['year']) == 1 && $s['from'] > $s['to']) {
							$calc_ends = mktime(0, 0, 0, 1, 1, ($s['year'] + 1)) + $s['to'];
							if ($calc_ends < ($from - $tomidnightone)) {
								continue;
							}
						}
					} elseif ($one['year'] < $s['year'] && $two['year'] == $s['year']) {
						//VRC 1.12 - season tied to the year 2017 accross 2018 and we are parsing dates accross prev year 2016-2017
						if ($s['from'] > $s['to']) {
							continue;
						}
					} elseif ($one['year'] == $s['year'] && $two['year'] == $s['year'] && $s['from'] > $s['to']) {
						//VRC 1.12 - season tied to the year 2017 accross 2018 and we are parsing dates at the beginning of 2017 due to beginning loop in 2016 (Rates Overview)
						if (($baseone + $s['from']) > $to) {
							continue;
						}
					}
				}
				//
				$allcars = explode(",", $s['idcars']);
				$allprices = !empty($s['idprices']) ? explode(",", $s['idprices']) : array();
				$inits = $baseone + $s['from'];
				if ($s['from'] < $s['to']) {
					$ends = $basetwo + $s['to'];
					//VRC 1.12 check if the inits must be set to the year after
					//ex. Season Jan 6 to Feb 12 - Booking Dec 31 to Jan 8 to charge Jan 6,7
					if ($sfrom > $s['from'] && $sto >= $s['from'] && $sfrom > $s['to'] && $sto <= $s['to'] && $s['from'] < $s['to'] && $sfrom > $sto) {
						$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] + 1));
						$inits = $tmpbase + $s['from'];
					} elseif ($sfrom >= $s['from'] && $sfrom <= $s['to'] && $sto < $s['from'] && $sto < $s['to'] && $sfrom > $sto) {
						//VRC 1.12 - Season Dec 23 to Dec 29 - Booking Dec 29 to Jan 5
						$ends = $baseone + $s['to'];
					} elseif ($sfrom <= $s['from'] && $sfrom <= $s['to'] && $sto < $s['from'] && $sto < $s['to'] && $sfrom > $sto) {
						//VRC 1.12 - Season Dec 30 to Dec 31 - Booking Dec 29 to Jan 5
						$ends = $baseone + $s['to'];
					} elseif ($sfrom > $s['from'] && $sfrom > $s['to'] && $sto >= $s['from'] && ($sto >= $s['to'] || $sto <= $s['to']) && $sfrom > $sto) {
						//VRC 1.12 - Season Jan 1 to Jan 2 - Booking Dec 29 to Jan 5
						$inits = $basetwo + $s['from'];
					}
				} else {
					//between 2 years
					if ($baseone < $basetwo) {
						//ex. 29/12/2012 - 14/01/2013
						$ends = $basetwo + $s['to'];
					} else {
						if (($sfrom >= $s['from'] && $sto >= $s['from']) OR ($sfrom < $s['from'] && $sto >= $s['from'] && $sfrom > $s['to'] && $sto > $s['to'])) {
							//ex. 25/12 - 30/12 with init season on 20/12 OR 27/12 for counting 28,29,30/12
							$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] + 1));
							$ends = $tmpbase + $s['to'];
						} else {
							//ex. 03/01 - 09/01
							$ends = $basetwo + $s['to'];
							$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] - 1));
							$inits = $tmpbase + $s['from'];
						}
					}
				}
				//leap years
				if ($isleap == true) {
					$infoseason = getdate($inits);
					$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
					//VRC 1.12 added below && $infoseason['year'] == $one['year']
					//for those seasons like 2015 Dec 14 to 2016 Jan 5 and booking dates like 2016 Jan 1 to Jan 6 where 2015 is not leap
					if ($infoseason[0] >= $leapts && $infoseason['year'] == $one['year']) {
						$inits += 86400;
						$ends += 86400;
					}
				}
				//
				//Promotions
				$promotion = array();
				if ($s['promo'] == 1) {
					$daysadv = (($inits - time()) / 86400);
					$daysadv = $daysadv > 0 ? (int)ceil($daysadv) : 0;
					if (!empty($s['promodaysadv']) && $s['promodaysadv'] > $daysadv) {
						continue;
					} else {
						$promotion['todaydaysadv'] = $daysadv;
						$promotion['promodaysadv'] = $s['promodaysadv'];
						$promotion['promotxt'] = $s['promotxt'];
					}
				}
				//
				//week days
				$filterwdays = !empty($s['wdays']) ? true : false;
				$wdays = $filterwdays == true ? explode(';', $s['wdays']) : '';
				if (is_array($wdays) && count($wdays) > 0) {
					foreach ($wdays as $kw=>$wd) {
						if (strlen($wd) == 0) {
							unset($wdays[$kw]);
						}
					}
				}
				//
				//pickup must be after the begin of the season
				if ($s['pickupincl'] == 1) {
					$pickupinclok = false;
					if ($s['from'] < $s['to']) {
						if ($sfrom >= $s['from'] && $sfrom <= $s['to']) {
							$pickupinclok = true;
						}
					} else {
						if (($sfrom >= $s['from'] && $sfrom > $s['to']) || ($sfrom < $s['from'] && $sfrom <= $s['to'])) {
							$pickupinclok = true;
						}
					}
				} else {
					$pickupinclok = true;
				}
				//
				if ($pickupinclok == true) {
					foreach ($arr as $k => $a) {
						//Applied only to some types of price
						if (count($allprices) > 0 && !empty($allprices[0])) {
							if (!in_array("-" . $a[0]['idprice'] . "-", $allprices)) {
								continue;
							}
						}
						//
						if (in_array("-" . $a[0]['idcar'] . "-", $allcars)) {
							$affdays = 0;
							$season_fromdayts = $fromdayts;
							$is_dst = date('I', $season_fromdayts);
							for ($i = 0; $i < $a[0]['days']; $i++) {
								$todayts = $season_fromdayts + ($i * 86400);
								$is_now_dst = date('I', $todayts);
								if ($is_dst != $is_now_dst) {
									//Daylight Saving Time has changed, check how
									if ((bool)$is_dst === true) {
										$todayts += 3600;
										$season_fromdayts += 3600;
									} else {
										$todayts -= 3600;
										$season_fromdayts -= 3600;
									}
									$is_dst = $is_now_dst;
								}
								//VRC 1.7 rev2
								if ($s['keepfirstdayrate'] == 1) {
									if ($fromdayts >= $inits && $fromdayts <= $ends) {
										$affdays = $a[0]['days'];
									} else {
										$affdays = 0;
									}
									break;
								}
								//end VRC 1.7 rev2
								if ($todayts >= $inits && $todayts <= $ends) {
									//week days
									if ($filterwdays == true) {
										$checkwday = getdate($todayts);
										if (in_array($checkwday['wday'], $wdays)) {
											$affdays++;
										}
									} else {
										$affdays++;
									}
									//
								}
							}
							if ($affdays > 0) {
								$applyseasons = true;
								$dailyprice = $a[0]['cost'] / $a[0]['days'];
								$a[0]['days'] = intval($a[0]['days']);
								$pctval = 0;
								$absval = 0;
								//VikRentCar 1.7 for abs or pcent and values overrides
								if (intval($s['val_pcent']) == 2) {
									//percentage value
									$pctval = $s['diffcost'];
									if (strlen($s['losoverride']) > 0) {
										//values overrides
										$arrvaloverrides = array();
										$valovrparts = explode('_', $s['losoverride']);
										foreach ($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												if (strstr($ovrinfo[0], '-i') != false) {
													$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
													if ((int)$ovrinfo[0] < $a[0]['days']) {
														$arrvaloverrides[$a[0]['days']] = $ovrinfo[1];
													}
												}
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($a[0]['days'], $arrvaloverrides)) {
											$pctval = $arrvaloverrides[$a[0]['days']];
										}
									}
									if (intval($s['type']) == 1) {
										//charge
										$cpercent = 100 + $pctval;
									} else {
										//discount
										$cpercent = 100 - $pctval;
									}
									$newprice = ($dailyprice * $cpercent / 100) * $affdays;
								} else {
									//absolute value
									$absval = $s['diffcost'];
									if (strlen($s['losoverride']) > 0) {
										//values overrides
										$arrvaloverrides = array();
										$valovrparts = explode('_', $s['losoverride']);
										foreach ($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												if (strstr($ovrinfo[0], '-i') != false) {
													$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
													if ((int)$ovrinfo[0] < $a[0]['days']) {
														$arrvaloverrides[$a[0]['days']] = $ovrinfo[1];
													}
												}
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($a[0]['days'], $arrvaloverrides)) {
											$absval = $arrvaloverrides[$a[0]['days']];
										}
									}
									if (intval($s['type']) == 1) {
										//charge
										$newprice = ($dailyprice + $absval) * $affdays;
									} else {
										//discount
										$newprice = ($dailyprice - $absval) * $affdays;
									}
								}
								//end VikRentCar 1.7 for abs or pcent and values overrides
								//VikRentCar 1.8
								if (!empty($s['roundmode'])) {
									$newprice = round($newprice, 0, constant($s['roundmode']));
								}
								//
								//Promotions (only if no value overrides set the amount to 0)
								if (count($promotion) > 0 && ($absval > 0 || $pctval > 0)) {
									$mem[$k]['promotion'] = $promotion;
								}
								//
								$mem[$k]['sum'][] = $newprice;
								$mem[$k]['daysused'] += $affdays;
								$carschange[] = $a[0]['idcar'];
							}
						}
					}
				}
			}
			if ($applyseasons) {
				foreach ($mem as $k => $v) {
					if ($v['daysused'] > 0 && @ count($v['sum']) > 0) {
						$newprice = 0;
						$dailyprice = $arr[$k][0]['cost'] / $arr[$k][0]['days'];
						$restdays = $arr[$k][0]['days'] - $v['daysused'];
						$addrest = $restdays * $dailyprice;
						$newprice += $addrest;
						foreach ($v['sum'] as $add) {
							$newprice += $add;
						}
						//Promotions
						if (array_key_exists('promotion', $v)) {
							$arr[$k][0]['promotion'] = $v['promotion'];
						}
						//
						$arr[$k][0]['cost'] = $newprice;
						$arr[$k][0]['affdays'] = $v['daysused'];
					}
				}
			}
		}
		//week days with no season
		$carschange = array_unique($carschange);
		$q = "SELECT * FROM `#__vikrentcar_seasons` WHERE (`locations`='0' OR `locations`=" . $dbo->quote($pickup) . ") AND ((`from` = 0 AND `to` = 0) OR (`from` IS NULL AND `to` IS NULL));";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$specials = $dbo->loadAssocList();
			$vrc_tn->translateContents($specials, '#__vikrentcar_seasons');
			$applyseasons = false;
			unset($mem);
			$mem = array();
			foreach ($arr as $k => $a) {
				$mem[$k]['daysused'] = 0;
				$mem[$k]['sum'] = array ();
			}
			foreach ($specials as $s) {
				//Special Price tied to the year
				if (!empty($s['year']) && $s['year'] > 0) {
					if ($one['year'] != $s['year']) {
						continue;
					}
				}
				//
				$allcars = explode(",", $s['idcars']);
				$allprices = !empty($s['idprices']) ? explode(",", $s['idprices']) : array();
				//week days
				$filterwdays = !empty($s['wdays']) ? true : false;
				$wdays = $filterwdays == true ? explode(';', $s['wdays']) : '';
				if (is_array($wdays) && count($wdays) > 0) {
					foreach ($wdays as $kw=>$wd) {
						if (strlen($wd) == 0) {
							unset($wdays[$kw]);
						}
					}
				}
				//
				foreach ($arr as $k => $a) {
					//only cars with no price modifications from seasons
					//Applied only to some types of price
					if (count($allprices) > 0 && !empty($allprices[0])) {
						if (!in_array("-" . $a[0]['idprice'] . "-", $allprices)) {
							continue;
						}
					}
					//
					if (in_array("-" . $a[0]['idcar'] . "-", $allcars) && !in_array($a[0]['idcar'], $carschange)) {
						$affdays = 0;
						$season_fromdayts = $fromdayts;
						$is_dst = date('I', $season_fromdayts);
						for ($i = 0; $i < $a[0]['days']; $i++) {
							$todayts = $season_fromdayts + ($i * 86400);
							$is_now_dst = date('I', $todayts);
							if ($is_dst != $is_now_dst) {
								//Daylight Saving Time has changed, check how
								if ((bool)$is_dst === true) {
									$todayts += 3600;
									$season_fromdayts += 3600;
								} else {
									$todayts -= 3600;
									$season_fromdayts -= 3600;
								}
								$is_dst = $is_now_dst;
							}
							//week days
							if ($filterwdays == true) {
								$checkwday = getdate($todayts);
								if (in_array($checkwday['wday'], $wdays)) {
									$affdays++;
								}
							}
							//
						}
						if ($affdays > 0) {
							$applyseasons = true;
							$dailyprice = $a[0]['cost'] / $a[0]['days'];
							$a[0]['days'] = intval($a[0]['days']);
							//VikRentCat 1.7 for abs or pcent and values overrides
							if (intval($s['val_pcent']) == 2) {
								//percentage value
								$pctval = $s['diffcost'];
								if (strlen($s['losoverride']) > 0) {
									//values overrides
									$arrvaloverrides = array();
									$valovrparts = explode('_', $s['losoverride']);
									foreach ($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											if (strstr($ovrinfo[0], '-i') != false) {
												$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
												if ((int)$ovrinfo[0] < $a[0]['days']) {
													$arrvaloverrides[$a[0]['days']] = $ovrinfo[1];
												}
											}
											$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists($a[0]['days'], $arrvaloverrides)) {
										$pctval = $arrvaloverrides[$a[0]['days']];
									}
								}
								if (intval($s['type']) == 1) {
									//charge
									$cpercent = 100 + $pctval;
								} else {
									//discount
									$cpercent = 100 - $pctval;
								}
								$newprice = ($dailyprice * $cpercent / 100) * $affdays;
							} else {
								//absolute value
								$absval = $s['diffcost'];
								if (strlen($s['losoverride']) > 0) {
									//values overrides
									$arrvaloverrides = array();
									$valovrparts = explode('_', $s['losoverride']);
									foreach ($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											if (strstr($ovrinfo[0], '-i') != false) {
												$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
												if ((int)$ovrinfo[0] < $a[0]['days']) {
													$arrvaloverrides[$a[0]['days']] = $ovrinfo[1];
												}
											}
											$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists($a[0]['days'], $arrvaloverrides)) {
										$absval = $arrvaloverrides[$a[0]['days']];
									}
								}
								if (intval($s['type']) == 1) {
									//charge
									$newprice = ($dailyprice + $absval) * $affdays;
								} else {
									//discount
									$newprice = ($dailyprice - $absval) * $affdays;
								}
							}
							//end VikRentCar 1.7 for abs or pcent and values overrides
							//VikRentCar 1.8
							if (!empty($s['roundmode'])) {
								$newprice = round($newprice, 0, constant($s['roundmode']));
							}
							//
							$mem[$k]['sum'][] = $newprice;
							$mem[$k]['daysused'] += $affdays;
						}
					}
				}
			}
			if ($applyseasons) {
				foreach ($mem as $k => $v) {
					if ($v['daysused'] > 0 && @ count($v['sum']) > 0) {
						$newprice = 0;
						$dailyprice = $arr[$k][0]['cost'] / $arr[$k][0]['days'];
						$restdays = $arr[$k][0]['days'] - $v['daysused'];
						$addrest = $restdays * $dailyprice;
						$newprice += $addrest;
						foreach ($v['sum'] as $add) {
							$newprice += $add;
						}
						$arr[$k][0]['cost'] = $newprice;
						$arr[$k][0]['affdays'] = $v['daysused'];
					}
				}
			}
		}
		//end week days with no season
		return $arr;
	}

	/**
	 * Applies the special prices over an array of tariffs for one car.
	 *
	 * @param 	array  		$arr 			array of tariffs taken from the DB
	 * @param 	int  		$from 			pick up timestamp
	 * @param 	int  		$to 			drop off timestamp
	 * @param 	int 		$pickup 		the ID of the pick up place, or null for the administrator
	 * @param 	array  		$parsed_season 	array of a season to parse (used to render the seasons calendars in back-end and front-end - VRC 1.12)
	 *
	 * @return 	array
	 */
	public static function applySeasonsCar($arr, $from, $to, $pickup = null, $parsed_season = array()) {
		$dbo = JFactory::getDBO();
		$vrc_tn = self::getTranslator();
		$carschange = array();
		$one = getdate($from);
		//leap years
		if ($one['year'] % 4 == 0 && ($one['year'] % 100 != 0 || $one['year'] % 400 == 0)) {
			$isleap = true;
		} else {
			$isleap = false;
		}
		//
		$baseone = mktime(0, 0, 0, 1, 1, $one['year']);
		$tomidnightone = intval($one['hours']) * 3600;
		$tomidnightone += intval($one['minutes']) * 60;
		$sfrom = $from - $baseone - $tomidnightone;
		$fromdayts = mktime(0, 0, 0, $one['mon'], $one['mday'], $one['year']);
		$two = getdate($to);
		$basetwo = mktime(0, 0, 0, 1, 1, $two['year']);
		$tomidnighttwo = intval($two['hours']) * 3600;
		$tomidnighttwo += intval($two['minutes']) * 60;
		$sto = $to - $basetwo - $tomidnighttwo;
		//Hourly Prices
		if ($sfrom === $sto) {
			$sto += 86399;
		}
		//End Hourly Prices
		//leap years, last day of the month of the season
		if ($isleap) {
			$leapts = mktime(0, 0, 0, 2, 29, $two['year']);
			if ($two[0] >= $leapts) {
				$sfrom -= 86400;
				$sto -= 86400;
			} elseif ($sto < $sfrom && $one['year'] < $two['year']) {
				//lower pickup date when in leap year but not for drop off
				$sfrom -= 86400;
			}
		}
		//
		$totseasons = 0;
		if (count($parsed_season) == 0) {
			$q = "SELECT * FROM `#__vikrentcar_seasons` WHERE ".($pickup !== null ? "(`locations`='0' OR `locations`='" . (int)$pickup . "') AND " : "")."(" .
		 	($sto > $sfrom ? "(`from`<=" . $sfrom . " AND `to`>=" . $sto . ") " : "") .
		 	($sto > $sfrom ? "OR (`from`<=" . $sfrom . " AND `to`>=" . $sfrom . ") " : "(`from`<=" . $sfrom . " AND `to`<=" . $sfrom . " AND `from`>`to`) ") .
		 	($sto > $sfrom ? "OR (`from`<=" . $sto . " AND `to`>=" . $sto . ") " : "OR (`from`>=" . $sto . " AND `to`>=" . $sto . " AND `from`>`to`) ") .
		 	($sto > $sfrom ? "OR (`from`>=" . $sfrom . " AND `from`<=" . $sto . " AND `to`>=" . $sfrom . " AND `to`<=" . $sto . ")" : "OR (`from`>=" . $sfrom . " AND `from`>" . $sto . " AND `to`<" . $sfrom . " AND `to`<=" . $sto . " AND `from`>`to`)") .
		 	($sto > $sfrom ? " OR (`from`<=" . $sfrom . " AND `from`<=" . $sto . " AND `to`<" . $sfrom . " AND `to`<" . $sto . " AND `from`>`to`) OR (`from`>" . $sfrom . " AND `from`>" . $sto . " AND `to`>=" . $sfrom . " AND `to`>=" . $sto . " AND `from`>`to`)" : " OR (`from` <=" . $sfrom . " AND `to` >=" . $sfrom . " AND `from` >" . $sto . " AND `to` >" . $sto . " AND `from` < `to`)") .
		 	($sto > $sfrom ? " OR (`from` >=" . $sfrom . " AND `from` <" . $sto . " AND `to` <" . $sfrom . " AND `to` <" . $sto . " AND `from` > `to`)" : " OR (`from` <" . $sfrom . " AND `to` >=" . $sto . " AND `from` <=" . $sto . " AND `to` <" . $sfrom . " AND `from` < `to`)"). //VRC 1.12 Else part is for Season Jan 6 to Feb 12 - Booking Dec 31 to Jan 8
		 	($sto > $sfrom ? " OR (`from` >" . $sfrom . " AND `from` >" . $sto . " AND `to` >=" . $sfrom . " AND `to` <" . $sto . " AND `from` > `to`)" : " OR (`from` >=" . $sfrom . " AND `from` >" . $sto . " AND `to` >" . $sfrom . " AND `to` >" . $sto . " AND `from` < `to`) OR (`from` <" . $sfrom . " AND `from` <" . $sto . " AND `to` <" . $sfrom . " AND `to` <=" . $sto . " AND `from` < `to`)"). //VRC 1.12 Else part for seasons Dec 25 to Dec 31, Jan 2 to Jan 5 - Booking Dec 20 to Jan 7
			");";
			$dbo->setQuery($q);
			$dbo->execute();
			$totseasons = $dbo->getNumRows();
		}
		if ($totseasons > 0 || count($parsed_season) > 0) {
			if ($totseasons > 0) {
				$seasons = $dbo->loadAssocList();
			} else {
				$seasons = array($parsed_season);
			}
			$vrc_tn->translateContents($seasons, '#__vikrentcar_seasons');
			$applyseasons = false;
			$mem = array ();
			foreach ($arr as $k => $a) {
				$mem[$k]['daysused'] = 0;
				$mem[$k]['sum'] = array ();
				$mem[$k]['spids'] = array();
			}
			foreach ($seasons as $s) {
				//Special Price tied to the year
				if (!empty($s['year']) && $s['year'] > 0) {
					//VRC 1.12 - do not skip seasons tied to the year for bookings between two years
					if ($one['year'] != $s['year'] && $two['year'] != $s['year']) {
						//VRC 1.12 - tied to the year can be set for prev year (Dec 27 to Jan 3) and booking can be Jan 1 to Jan 3 - do not skip in this case
						if (($one['year'] - $s['year']) != 1 || $s['from'] < $s['to']) {
							continue;
						}
						//VRC 1.12 - tied to 2016 going through Jan 2017: dates of December 2017 should skip this speacial price
						if (($one['year'] - $s['year']) == 1 && $s['from'] > $s['to']) {
							$calc_ends = mktime(0, 0, 0, 1, 1, ($s['year'] + 1)) + $s['to'];
							if ($calc_ends < ($from - $tomidnightone)) {
								continue;
							}
						}
					} elseif ($one['year'] < $s['year'] && $two['year'] == $s['year']) {
						//VRC 1.12 - season tied to the year 2017 accross 2018 and we are parsing dates accross prev year 2016-2017
						if ($s['from'] > $s['to']) {
							continue;
						}
					} elseif ($one['year'] == $s['year'] && $two['year'] == $s['year'] && $s['from'] > $s['to']) {
						//VRC 1.12 - season tied to the year 2017 accross 2018 and we are parsing dates at the beginning of 2017 due to beginning loop in 2016 (Rates Overview)
						if (($baseone + $s['from']) > $to) {
							continue;
						}
					}
				}
				//
				$allcars = explode(",", $s['idcars']);
				$allprices = !empty($s['idprices']) ? explode(",", $s['idprices']) : array();
				$inits = $baseone + $s['from'];
				if ($s['from'] < $s['to']) {
					$ends = $basetwo + $s['to'];
					//VRC 1.12 check if the inits must be set to the year after
					//ex. Season Jan 6 to Feb 12 - Booking Dec 31 to Jan 8 to charge Jan 6,7
					if ($sfrom > $s['from'] && $sto >= $s['from'] && $sfrom > $s['to'] && $sto <= $s['to'] && $s['from'] < $s['to'] && $sfrom > $sto) {
						$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] + 1));
						$inits = $tmpbase + $s['from'];
					} elseif ($sfrom >= $s['from'] && $sfrom <= $s['to'] && $sto < $s['from'] && $sto < $s['to'] && $sfrom > $sto) {
						//VRC 1.12 - Season Dec 23 to Dec 29 - Booking Dec 29 to Jan 5
						$ends = $baseone + $s['to'];
					} elseif ($sfrom <= $s['from'] && $sfrom <= $s['to'] && $sto < $s['from'] && $sto < $s['to'] && $sfrom > $sto) {
						//VRC 1.12 - Season Dec 30 to Dec 31 - Booking Dec 29 to Jan 5
						$ends = $baseone + $s['to'];
					} elseif ($sfrom > $s['from'] && $sfrom > $s['to'] && $sto >= $s['from'] && ($sto >= $s['to'] || $sto <= $s['to']) && $sfrom > $sto) {
						//VRC 1.12 - Season Jan 1 to Jan 2 - Booking Dec 29 to Jan 5
						$inits = $basetwo + $s['from'];
					}
				} else {
					//between 2 years
					if ($baseone < $basetwo) {
						//ex. 29/12/2012 - 14/01/2013
						$ends = $basetwo + $s['to'];
					} else {
						if (($sfrom >= $s['from'] && $sto >= $s['from']) OR ($sfrom < $s['from'] && $sto >= $s['from'] && $sfrom > $s['to'] && $sto > $s['to'])) {
							//ex. 25/12 - 30/12 with init season on 20/12 OR 27/12 for counting 28,29,30/12
							$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] + 1));
							$ends = $tmpbase + $s['to'];
						} else {
							//ex. 03/01 - 09/01
							$ends = $basetwo + $s['to'];
							$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] - 1));
							$inits = $tmpbase + $s['from'];
						}
					}
				}
				//leap years
				if ($isleap == true) {
					$infoseason = getdate($inits);
					$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
					//VRC 1.12 added below && $infoseason['year'] == $one['year']
					//for those seasons like 2015 Dec 14 to 2016 Jan 5 and booking dates like 2016 Jan 1 to Jan 6 where 2015 is not leap
					if ($infoseason[0] >= $leapts && $infoseason['year'] == $one['year']) {
						$inits += 86400;
						$ends += 86400;
					}
				}
				//
				//Promotions
				$promotion = array();
				if ($s['promo'] == 1) {
					$daysadv = (($inits - time()) / 86400);
					$daysadv = $daysadv > 0 ? (int)ceil($daysadv) : 0;
					if (!empty($s['promodaysadv']) && $s['promodaysadv'] > $daysadv) {
						continue;
					} else {
						$promotion['todaydaysadv'] = $daysadv;
						$promotion['promodaysadv'] = $s['promodaysadv'];
						$promotion['promotxt'] = $s['promotxt'];
					}
				}
				//
				//week days
				$filterwdays = !empty($s['wdays']) ? true : false;
				$wdays = $filterwdays == true ? explode(';', $s['wdays']) : '';
				if (is_array($wdays) && count($wdays) > 0) {
					foreach ($wdays as $kw=>$wd) {
						if (strlen($wd) == 0) {
							unset($wdays[$kw]);
						}
					}
				}
				//
				//pickup must be after the begin of the season
				if ($s['pickupincl'] == 1) {
					$pickupinclok = false;
					if ($s['from'] < $s['to']) {
						if ($sfrom >= $s['from'] && $sfrom <= $s['to']) {
							$pickupinclok = true;
						}
					} else {
						if (($sfrom >= $s['from'] && $sfrom > $s['to']) || ($sfrom < $s['from'] && $sfrom <= $s['to'])) {
							$pickupinclok = true;
						}
					}
				} else {
					$pickupinclok = true;
				}
				//
				if ($pickupinclok == true) {
					foreach ($arr as $k => $a) {
						//Applied only to some types of price
						if (count($allprices) > 0 && !empty($allprices[0])) {
							if (!in_array("-" . $a['idprice'] . "-", $allprices)) {
								continue;
							}
						}
						//
						if (in_array("-" . $a['idcar'] . "-", $allcars)) {
							$affdays = 0;
							$season_fromdayts = $fromdayts;
							$is_dst = date('I', $season_fromdayts);
							for ($i = 0; $i < $a['days']; $i++) {
								$todayts = $season_fromdayts + ($i * 86400);
								$is_now_dst = date('I', $todayts);
								if ($is_dst != $is_now_dst) {
									//Daylight Saving Time has changed, check how
									if ((bool)$is_dst === true) {
										$todayts += 3600;
										$season_fromdayts += 3600;
									} else {
										$todayts -= 3600;
										$season_fromdayts -= 3600;
									}
									$is_dst = $is_now_dst;
								}
								//VRC 1.7 rev2
								if ($s['keepfirstdayrate'] == 1) {
									if ($fromdayts >= $inits && $fromdayts <= $ends) {
										$affdays = $a['days'];
									} else {
										$affdays = 0;
									}
									break;
								}
								//end VRC 1.7 rev2
								if ($todayts >= $inits && $todayts <= $ends) {
									//week days
									if ($filterwdays == true) {
										$checkwday = getdate($todayts);
										if (in_array($checkwday['wday'], $wdays)) {
											$affdays++;
										}
									} else {
										$affdays++;
									}
									//
								}
							}
							if ($affdays > 0) {
								$applyseasons = true;
								$dailyprice = $a['cost'] / $a['days'];
								$a['days'] = intval($a['days']);
								$pctval = 0;
								$absval = 0;
								//VikRentCar 1.7 for abs or pcent and values overrides
								if (intval($s['val_pcent']) == 2) {
									//percentage value
									$pctval = $s['diffcost'];
									if (strlen($s['losoverride']) > 0) {
										//values overrides
										$arrvaloverrides = array();
										$valovrparts = explode('_', $s['losoverride']);
										foreach ($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												if (strstr($ovrinfo[0], '-i') != false) {
													$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
													if ((int)$ovrinfo[0] < $a['days']) {
														$arrvaloverrides[$a['days']] = $ovrinfo[1];
													}
												}
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($a['days'], $arrvaloverrides)) {
											$pctval = $arrvaloverrides[$a['days']];
										}
									}
									if (intval($s['type']) == 1) {
										//charge
										$cpercent = 100 + $pctval;
									} else {
										//discount
										$cpercent = 100 - $pctval;
									}
									$dailysum = ($dailyprice * $cpercent / 100);
									$newprice = $dailysum * $affdays;
								} else {
									//absolute value
									$absval = $s['diffcost'];
									if (strlen($s['losoverride']) > 0) {
										//values overrides
										$arrvaloverrides = array();
										$valovrparts = explode('_', $s['losoverride']);
										foreach ($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												if (strstr($ovrinfo[0], '-i') != false) {
													$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
													if ((int)$ovrinfo[0] < $a['days']) {
														$arrvaloverrides[$a['days']] = $ovrinfo[1];
													}
												}
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($a['days'], $arrvaloverrides)) {
											$absval = $arrvaloverrides[$a['days']];
										}
									}
									if (intval($s['type']) == 1) {
										//charge
										$dailysum = ($dailyprice + $absval);
										$newprice = $dailysum * $affdays;
									} else {
										//discount
										$dailysum = ($dailyprice - $absval);
										$newprice = $dailysum * $affdays;
									}
								}
								//end VikRentCar 1.7 for abs or pcent and values overrides
								//VikRentCar 1.8
								if (!empty($s['roundmode'])) {
									$newprice = round($newprice, 0, constant($s['roundmode']));
								}
								//
								//Promotions (only if no value overrides set the amount to 0)
								if (count($promotion) > 0 && ($absval > 0 || $pctval > 0)) {
									$mem[$k]['promotion'] = $promotion;
								}
								//
								if (!in_array($s['id'], $mem[$k]['spids'])) {
									$mem[$k]['spids'][] = $s['id'];
								}
								$mem[$k]['sum'][] = $newprice;
								$mem[$k]['daysused'] += $affdays;
								$carschange[] = $a['idcar'];
							}
						}
					}
				}
			}
			if ($applyseasons) {
				foreach ($mem as $k => $v) {
					if ($v['daysused'] > 0 && @ count($v['sum']) > 0) {
						$newprice = 0;
						$dailyprice = $arr[$k]['cost'] / $arr[$k]['days'];
						$restdays = $arr[$k]['days'] - $v['daysused'];
						$addrest = $restdays * $dailyprice;
						$newprice += $addrest;
						foreach ($v['sum'] as $add) {
							$newprice += $add;
						}
						//Promotions
						if (array_key_exists('promotion', $v)) {
							$arr[$k]['promotion'] = $v['promotion'];
						}
						//
						$arr[$k]['cost'] = $newprice;
						$arr[$k]['affdays'] = $v['daysused'];
						if (array_key_exists('spids', $v) && count($v['spids']) > 0) {
							$arr[$k]['spids'] = $v['spids'];
						}
					}
				}
			}
		}
		//week days with no season
		$carschange = array_unique($carschange);
		$q = "SELECT * FROM `#__vikrentcar_seasons` WHERE ".($pickup !== null ? "(`locations`='0' OR `locations`=" . $dbo->quote($pickup) . ") AND " : "")."((`from` = 0 AND `to` = 0) OR (`from` IS NULL AND `to` IS NULL));";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$specials = $dbo->loadAssocList();
			$vrc_tn->translateContents($specials, '#__vikrentcar_seasons');
			$applyseasons = false;
			unset($mem);
			$mem = array();
			foreach ($arr as $k => $a) {
				$mem[$k]['daysused'] = 0;
				$mem[$k]['sum'] = array();
				$mem[$k]['spids'] = array();
			}
			foreach ($specials as $s) {
				//Special Price tied to the year
				if (!empty($s['year']) && $s['year'] > 0) {
					if ($one['year'] != $s['year']) {
						continue;
					}
				}
				//
				$allcars = explode(",", $s['idcars']);
				$allprices = !empty($s['idprices']) ? explode(",", $s['idprices']) : array();
				//week days
				$filterwdays = !empty($s['wdays']) ? true : false;
				$wdays = $filterwdays == true ? explode(';', $s['wdays']) : '';
				if (is_array($wdays) && count($wdays) > 0) {
					foreach ($wdays as $kw=>$wd) {
						if (strlen($wd) == 0) {
							unset($wdays[$kw]);
						}
					}
				}
				//
				foreach ($arr as $k => $a) {
					//only cars with no price modifications from seasons
					//Applied only to some types of price
					if (count($allprices) > 0 && !empty($allprices[0])) {
						if (!in_array("-" . $a['idprice'] . "-", $allprices)) {
							continue;
						}
					}
					//
					if (in_array("-" . $a['idcar'] . "-", $allcars) && !in_array($a['idcar'], $carschange)) {
						$affdays = 0;
						$season_fromdayts = $fromdayts;
						$is_dst = date('I', $season_fromdayts);
						for ($i = 0; $i < $a['days']; $i++) {
							$todayts = $season_fromdayts + ($i * 86400);
							$is_now_dst = date('I', $todayts);
							if ($is_dst != $is_now_dst) {
								//Daylight Saving Time has changed, check how
								if ((bool)$is_dst === true) {
									$todayts += 3600;
									$season_fromdayts += 3600;
								} else {
									$todayts -= 3600;
									$season_fromdayts -= 3600;
								}
								$is_dst = $is_now_dst;
							}
							//week days
							if ($filterwdays == true) {
								$checkwday = getdate($todayts);
								if (in_array($checkwday['wday'], $wdays)) {
									$affdays++;
								}
							}
							//
						}
						if ($affdays > 0) {
							$applyseasons = true;
							$dailyprice = $a['cost'] / $a['days'];
							$a['days'] = intval($a['days']);
							//VikRentCar 1.7 for abs or pcent and values overrides
							if (intval($s['val_pcent']) == 2) {
								//percentage value
								$pctval = $s['diffcost'];
								if (strlen($s['losoverride']) > 0) {
									//values overrides
									$arrvaloverrides = array();
									$valovrparts = explode('_', $s['losoverride']);
									foreach ($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											if (strstr($ovrinfo[0], '-i') != false) {
												$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
												if ((int)$ovrinfo[0] < $a['days']) {
													$arrvaloverrides[$a['days']] = $ovrinfo[1];
												}
											}
											$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists($a['days'], $arrvaloverrides)) {
										$pctval = $arrvaloverrides[$a['days']];
									}
								}
								if (intval($s['type']) == 1) {
									//charge
									$cpercent = 100 + $pctval;
								} else {
									//discount
									$cpercent = 100 - $pctval;
								}
								$dailysum = ($dailyprice * $cpercent / 100);
								$newprice = $dailysum * $affdays;
							} else {
								//absolute value
								$absval = $s['diffcost'];
								if (strlen($s['losoverride']) > 0) {
									//values overrides
									$arrvaloverrides = array();
									$valovrparts = explode('_', $s['losoverride']);
									foreach ($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											if (strstr($ovrinfo[0], '-i') != false) {
												$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
												if ((int)$ovrinfo[0] < $a['days']) {
													$arrvaloverrides[$a['days']] = $ovrinfo[1];
												}
											}
											$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists($a['days'], $arrvaloverrides)) {
										$absval = $arrvaloverrides[$a['days']];
									}
								}
								if (intval($s['type']) == 1) {
									//charge
									$dailysum = ($dailyprice + $absval);
									$newprice = $dailysum * $affdays;
								} else {
									//discount
									$dailysum = ($dailyprice - $absval);
									$newprice = $dailysum * $affdays;
								}
							}
							//end VikRentCar 1.7 for abs or pcent and values overrides
							//VikRentCar 1.8
							if (!empty($s['roundmode'])) {
								$newprice = round($newprice, 0, constant($s['roundmode']));
							}
							//
							if (!in_array($s['id'], $mem[$k]['spids'])) {
								$mem[$k]['spids'][] = $s['id'];
							}
							$mem[$k]['sum'][] = $newprice;
							$mem[$k]['daysused'] += $affdays;
						}
					}
				}
			}
			if ($applyseasons) {
				foreach ($mem as $k => $v) {
					if ($v['daysused'] > 0 && @ count($v['sum']) > 0) {
						$newprice = 0;
						$dailyprice = $arr[$k]['cost'] / $arr[$k]['days'];
						$restdays = $arr[$k]['days'] - $v['daysused'];
						$addrest = $restdays * $dailyprice;
						$newprice += $addrest;
						foreach ($v['sum'] as $add) {
							$newprice += $add;
						}
						$arr[$k]['cost'] = $newprice;
						$arr[$k]['affdays'] = $v['daysused'];
						if (array_key_exists('spids', $v) && count($v['spids']) > 0) {
							$arr[$k]['spids'] = $v['spids'];
						}
					}
				}
			}
		}
		//end week days with no season
		return $arr;
	}

	public static function getCarRplansClosingDates($idcar) {
		$dbo = JFactory::getDBO();
		$closingd = array();
		$q = "SELECT * FROM `#__vikrentcar_prices` WHERE `closingd` IS NOT NULL;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$price_records = $dbo->loadAssocList();
			foreach ($price_records as $prec) {
				if (empty($prec['closingd'])) {
					continue;
				}
				$price_closing = json_decode($prec['closingd'], true);
				if (!is_array($price_closing) || !(count($price_closing) > 0) || !array_key_exists($idcar, $price_closing) || !(count($price_closing[$idcar]) > 0)) {
					continue;
				}
				//check expired dates and clean up
				$today_midnight = mktime(0, 0, 0);
				$cleaned = false;
				foreach ($price_closing[$idcar] as $k => $v) {
					if (strtotime($v) < $today_midnight) {
						$cleaned = true;
						unset($price_closing[$idcar][$k]);
					}
				}
				//
				if (!(count($price_closing[$idcar]) > 0)) {
					unset($price_closing[$idcar]);
				} elseif ($cleaned === true) {
					//reset array keys for smaller JSON size
					$price_closing[$idcar] = array_values($price_closing[$idcar]);
				}
				if ($cleaned === true) {
					$q = "UPDATE `#__vikrentcar_prices` SET `closingd`=".(count($price_closing) > 0 ? $dbo->quote(json_encode($price_closing)) : "NULL")." WHERE `id`=".$prec['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
				if (!(count($price_closing[$idcar]) > 0) || !(count($price_closing[$idcar]) > 0)) {
					continue;
				}
				$closingd[$prec['id']] = $price_closing[$idcar];
			}
		}
		return $closingd;
	}

	public static function getCarRplansClosedInDates($carids, $pickupts, $numdays) {
		$dbo = JFactory::getDBO();
		$closingd = array();
		$q = "SELECT * FROM `#__vikrentcar_prices` WHERE `closingd` IS NOT NULL;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0 && count($carids) > 0) {
			$price_records = $dbo->loadAssocList();
			$info_start = getdate($pickupts);
			$checkin_midnight = mktime(0, 0, 0, $info_start['mon'], $info_start['mday'], $info_start['year']);
			$all_days = array();
			for ($i=0; $i < (int)$numdays; $i++) {
				$next_midnight = mktime(0, 0, 0, $info_start['mon'], ($info_start['mday'] + $i), $info_start['year']);
				$all_days[] = date('Y-m-d', $next_midnight);
			}
			foreach ($price_records as $prec) {
				if (empty($prec['closingd'])) {
					continue;
				}
				$price_closing = json_decode($prec['closingd'], true);
				if (!is_array($price_closing) || !(count($price_closing) > 0)) {
					continue;
				}
				foreach ($price_closing as $idcar => $rclosedd) {
					if (!in_array($idcar, $carids) || !is_array($rclosedd)) {
						continue;
					}
					if (!array_key_exists($idcar, $closingd)) {
						$closingd[$idcar] = array();
					}
					foreach ($all_days as $day) {
						if (in_array($day, $rclosedd)) {
							if (array_key_exists($prec['id'], $closingd[$idcar])) {
								$closingd[$idcar][$prec['id']][] = $day;
							} else {
								$closingd[$idcar][$prec['id']] = array($day);
							}
						}
					}
				}
			}
		}

		return $closingd;
	}

	public static function areTherePayments() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id` FROM `#__vikrentcar_gpayments` WHERE `published`='1';";
		$dbo->setQuery($q);
		$dbo->execute();
		return $dbo->getNumRows() > 0 ? true : false;
	}

	public static function getPayment($idp, $vrc_tn = null) {
		if (!empty ($idp)) {
			if (strstr($idp, '=') !== false) {
				$parts = explode('=', $idp);
				$idp = $parts[0];
			}
			$dbo = JFactory::getDBO();
			$q = "SELECT * FROM `#__vikrentcar_gpayments` WHERE `id`=" . $dbo->quote($idp) . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$payment = $dbo->loadAssocList();
				if (is_object($vrc_tn)) {
					$vrc_tn->translateContents($payment, '#__vikrentcar_gpayments');
				}
				return $payment[0];
			} else {
				return false;
			}
		}
		return false;
	}
	
	public static function applyHourlyPrices($arrtar, $hoursdiff) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `hours`='" . $hoursdiff . "' ORDER BY `#__vikrentcar_dispcosthours`.`cost` ASC, `#__vikrentcar_dispcosthours`.`idcar` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$hourtars = $dbo->loadAssocList();
			$hourarrtar = array();
			foreach ($hourtars as $tar) {
				$hourarrtar[$tar['idcar']][] = $tar;
			}
			foreach ($arrtar as $idcar => $tar) {
				if (array_key_exists($idcar, $hourarrtar)) {
					foreach ($tar as $ind => $fare) {
						//check if idprice exists in $hourarrtar
						foreach ($hourarrtar[$idcar] as $hind => $hfare) {
							if ($fare['idprice'] == $hfare['idprice']) {
								$arrtar[$idcar][$ind]['id'] = $hourarrtar[$idcar][$hind]['id'];
								$arrtar[$idcar][$ind]['cost'] = $hourarrtar[$idcar][$hind]['cost'];
								$arrtar[$idcar][$ind]['attrdata'] = $hourarrtar[$idcar][$hind]['attrdata'];
								$arrtar[$idcar][$ind]['hours'] = $hourarrtar[$idcar][$hind]['hours'];
							}
						}
					}
				}
			}
		}
		return $arrtar;
	}
	
	public static function applyHourlyPricesCar($arrtar, $hoursdiff, $idcar, $filterprice = false) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `hours`='" . $hoursdiff . "' AND `idcar`=" . $dbo->quote($idcar) . "".($filterprice == true ? "  AND `idprice`='".$arrtar[0]['idprice']."'" : "")." ORDER BY `#__vikrentcar_dispcosthours`.`cost` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$arrtar = $dbo->loadAssocList();
			foreach ($arrtar as $k => $v) {
				$arrtar[$k]['days'] = 1;
			}
		}
		return $arrtar;
	}
	
	public static function extraHoursSetPreviousFare($arrtar, $ehours, $daysdiff) {
		//set the fare to the days of rental - 1 where hours charges exist
		//to be used when the hours charges need to be applied after the special prices
		$dbo = JFactory::getDBO();
		$idcars = array_keys($arrtar);
		if (count($idcars) > 0 && $daysdiff > 1) {
			$q = "SELECT * FROM `#__vikrentcar_hourscharges` WHERE `ehours`='".$ehours."' AND `idcar` IN (".implode(",", $idcars).");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$ehcharges = $dbo->loadAssocList();
				$arrehcharges = array();
				foreach ($ehcharges as $ehc) {
					$arrehcharges[$ehc['idcar']][]=$ehc;
				}
				$idcars = array_keys($arrehcharges);
				$newdaysdiff = $daysdiff - 1;
				$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `days`='".$newdaysdiff."' AND `idcar` IN (".implode(",", $idcars).");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					//only if there are fares for ($daysdiff - 1) otherwise dont apply extra hours charges
					$prevdaytars = $dbo->loadAssocList();
					$prevdayarrtar = array();
					foreach ($prevdaytars as $pdtar) {
						$prevdayarrtar[$pdtar['idcar']][]=$pdtar;
					}
					//set fares for 1 day before of rental
					$newdispcostvals = array();
					$newdispcostattr = array();
					foreach ($arrehcharges as $idc => $ehc) {
						if (array_key_exists($idc, $prevdayarrtar)) {
							foreach ($prevdayarrtar[$idc] as $vp) {
								foreach ($ehc as $hc) {
									if ($vp['idprice'] == $hc['idprice']) {
										$newdispcostvals[$idc][$hc['idprice']] = $vp['cost'];
										$newdispcostattr[$idc][$hc['idprice']] = $vp['attrdata'];
									}
								}
							}
						}
					}
					if (count($newdispcostvals) > 0) {
						foreach ($arrtar as $idc => $tar) {
							if (array_key_exists($idc, $newdispcostvals)) {
								foreach ($tar as $krecp => $recp) {
									if (array_key_exists($recp['idprice'], $newdispcostvals[$idc])) {
										$arrtar[$idc][$krecp]['cost'] = $newdispcostvals[$idc][$recp['idprice']];
										$arrtar[$idc][$krecp]['attrdata'] = $newdispcostattr[$idc][$recp['idprice']];
										$arrtar[$idc][$krecp]['days'] = $newdaysdiff;
										$arrtar[$idc][$krecp]['ehours'] = $ehours;
									}
								}
							}
						}
					}
					//
				}
			}
		}
		return $arrtar;
	}
	
	public static function extraHoursSetPreviousFareCar($tar, $idcar, $ehours, $daysdiff, $filterprice = false) {
		//set the fare to the days of rental - 1 where hours charges exist
		//to be used when the hours charges need to be applied after the special prices
		$dbo = JFactory::getDBO();
		if ($daysdiff > 1) {
			$q = "SELECT * FROM `#__vikrentcar_hourscharges` WHERE `ehours`='".$ehours."' AND `idcar`='".$idcar."'".($filterprice == true ? " AND `idprice`='".$tar[0]['idprice']."'" : "").";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$ehcharges = $dbo->loadAssocList();
				$newdaysdiff = $daysdiff - 1;
				$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `days`='".$newdaysdiff."' AND `idcar`='".$idcar."'".($filterprice == true ? " AND `idprice`='".$tar[0]['idprice']."'" : "").";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					//only if there are fares for ($daysdiff - 1) otherwise dont apply extra hours charges
					$prevdaytars = $dbo->loadAssocList();
					//set fares for 1 day before of rental
					$newdispcostvals = array();
					$newdispcostattr = array();
					foreach ($ehcharges as $ehc) {
						foreach ($prevdaytars as $vp) {
							if ($vp['idprice'] == $ehc['idprice']) {
								$newdispcostvals[$ehc['idprice']] = $vp['cost'];
								$newdispcostattr[$ehc['idprice']] = $vp['attrdata'];
							}
						}
					}
					if (count($newdispcostvals) > 0) {
						foreach ($tar as $kp => $f) {
							if (array_key_exists($f['idprice'], $newdispcostvals)) {
								$tar[$kp]['cost'] = $newdispcostvals[$f['idprice']];
								$tar[$kp]['attrdata'] = $newdispcostattr[$f['idprice']];
								$tar[$kp]['days'] = $newdaysdiff;
								$tar[$kp]['ehours'] = $ehours;
							}
						}
					}
					//
				}
			}
		}
		return $tar;
	}
	
	public static function applyExtraHoursChargesPrices($arrtar, $ehours, $daysdiff, $aftersp = false) {
		$dbo = JFactory::getDBO();
		$idcars = array_keys($arrtar);
		if (count($idcars) > 0 && $daysdiff > 1) {
			$q = "SELECT * FROM `#__vikrentcar_hourscharges` WHERE `ehours`='".$ehours."' AND `idcar` IN (".implode(",", $idcars).");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$ehcharges = $dbo->loadAssocList();
				$arrehcharges = array();
				foreach ($ehcharges as $ehc) {
					$arrehcharges[$ehc['idcar']][]=$ehc;
				}
				$idcars = array_keys($arrehcharges);
				$newdaysdiff = $daysdiff - 1;
				if ($aftersp == true) {
					//after having applied special prices, dont consider the fares for ($daysdiff - 1)
					//apply extra hours charges
					$newdispcostvals = array();
					$newdispcostattr = array();
					foreach ($arrehcharges as $idc => $ehc) {
						if (array_key_exists($idc, $arrtar)) {
							foreach ($arrtar[$idc] as $vp) {
								foreach ($ehc as $hc) {
									if ($vp['idprice'] == $hc['idprice']) {
										$newdispcostvals[$idc][$hc['idprice']] = $vp['cost'] + $hc['cost'];
										$newdispcostattr[$idc][$hc['idprice']] = $vp['attrdata'];
									}
								}
							}
						}
					}
					if (count($newdispcostvals) > 0) {
						foreach ($arrtar as $idc => $tar) {
							if (array_key_exists($idc, $newdispcostvals)) {
								foreach ($tar as $krecp => $recp) {
									if (array_key_exists($recp['idprice'], $newdispcostvals[$idc])) {
										$arrtar[$idc][$krecp]['cost'] = $newdispcostvals[$idc][$recp['idprice']];
										$arrtar[$idc][$krecp]['attrdata'] = $newdispcostattr[$idc][$recp['idprice']];
										$arrtar[$idc][$krecp]['days'] = $newdaysdiff;
										$arrtar[$idc][$krecp]['ehours'] = $ehours;
									}
								}
							}
						}
					}
					//
				} else {
					//before applying special prices
					$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `days`='".$newdaysdiff."' AND `idcar` IN (".implode(",", $idcars).");";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						//only if there are fares for ($daysdiff - 1) otherwise dont apply extra hours charges
						$prevdaytars = $dbo->loadAssocList();
						$prevdayarrtar = array();
						foreach ($prevdaytars as $pdtar) {
							$prevdayarrtar[$pdtar['idcar']][]=$pdtar;
						}
						//apply extra hours charges
						$newdispcostvals = array();
						$newdispcostattr = array();
						foreach ($arrehcharges as $idc => $ehc) {
							if (array_key_exists($idc, $prevdayarrtar)) {
								foreach ($prevdayarrtar[$idc] as $vp) {
									foreach ($ehc as $hc) {
										if ($vp['idprice'] == $hc['idprice']) {
											$newdispcostvals[$idc][$hc['idprice']] = $vp['cost'] + $hc['cost'];
											$newdispcostattr[$idc][$hc['idprice']] = $vp['attrdata'];
										}
									}
								}
							}
						}
						if (count($newdispcostvals) > 0) {
							foreach ($arrtar as $idc => $tar) {
								if (array_key_exists($idc, $newdispcostvals)) {
									foreach ($tar as $krecp => $recp) {
										if (array_key_exists($recp['idprice'], $newdispcostvals[$idc])) {
											$arrtar[$idc][$krecp]['cost'] = $newdispcostvals[$idc][$recp['idprice']];
											$arrtar[$idc][$krecp]['attrdata'] = $newdispcostattr[$idc][$recp['idprice']];
											$arrtar[$idc][$krecp]['days'] = $newdaysdiff;
											$arrtar[$idc][$krecp]['ehours'] = $ehours;
										}
									}
								}
							}
						}
						//
					}
				}
			}
		}
		return $arrtar;
	}
	
	public static function applyExtraHoursChargesCar($tar, $idcar, $ehours, $daysdiff, $aftersp = false, $filterprice = false, $retarray = false) {
		$dbo = JFactory::getDBO();
		$newdaysdiff = $daysdiff;
		if ($daysdiff > 1) {
			$q = "SELECT * FROM `#__vikrentcar_hourscharges` WHERE `ehours`='".$ehours."' AND `idcar`='".$idcar."'".($filterprice == true ? " AND `idprice`='".$tar[0]['idprice']."'" : "").";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$ehcharges = $dbo->loadAssocList();
				$newdaysdiff = $daysdiff - 1;
				if ($aftersp == true) {
					//after having applied special prices, dont consider the fares for ($daysdiff - 1) because done already
					//apply extra hours charges
					$newdispcostvals = array();
					$newdispcostattr = array();
					foreach ($ehcharges as $ehc) {
						foreach ($tar as $vp) {
							if ($vp['idprice'] == $ehc['idprice']) {
								$newdispcostvals[$ehc['idprice']] = $vp['cost'] + $ehc['cost'];
								$newdispcostattr[$ehc['idprice']] = $vp['attrdata'];
							}
						}
					}
					if (count($newdispcostvals) > 0) {
						foreach ($tar as $kt => $f) {
							if (array_key_exists($f['idprice'], $newdispcostvals)) {
								$tar[$kt]['cost'] = $newdispcostvals[$f['idprice']];
								$tar[$kt]['attrdata'] = $newdispcostattr[$f['idprice']];
								$tar[$kt]['days'] = $newdaysdiff;
								$tar[$kt]['ehours'] = $ehours;
							}
						}
					}
					//
				} else {
					//before applying special prices
					$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `days`='".$newdaysdiff."' AND `idcar`='".$idcar."'".($filterprice == true ? " AND `idprice`='".$tar[0]['idprice']."'" : "").";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						//only if there are fares for ($daysdiff - 1) otherwise dont apply extra hours charges
						$prevdaytars = $dbo->loadAssocList();
						//apply extra hours charges
						$newdispcostvals = array();
						$newdispcostattr = array();
						foreach ($ehcharges as $ehc) {
							foreach ($prevdaytars as $vp) {
								if ($vp['idprice'] == $ehc['idprice']) {
									$newdispcostvals[$ehc['idprice']] = $vp['cost'] + $ehc['cost'];
									$newdispcostattr[$ehc['idprice']] = $vp['attrdata'];
								}
							}
						}
						if (count($newdispcostvals) > 0) {
							foreach ($tar as $kt => $f) {
								if (array_key_exists($f['idprice'], $newdispcostvals)) {
									$tar[$kt]['cost'] = $newdispcostvals[$f['idprice']];
									$tar[$kt]['attrdata'] = $newdispcostattr[$f['idprice']];
									$tar[$kt]['days'] = $newdaysdiff;
									$tar[$kt]['ehours'] = $ehours;
								}
							}
						}
						//
					}
				}
			}
		}
		if ($retarray == true) {
			$ret = array();
			$ret['return'] = $tar;
			$ret['days'] = $newdaysdiff;
			return $ret;
		} else {
			return $tar;
		}
	}

	public static function sayWeekDay($wd) {
		switch ($wd) {
			case '6' :
				$ret = JText::_('VRWEEKDAYSIX');
				break;
			case '5' :
				$ret = JText::_('VRWEEKDAYFIVE');
				break;
			case '4' :
				$ret = JText::_('VRWEEKDAYFOUR');
				break;
			case '3' :
				$ret = JText::_('VRWEEKDAYTHREE');
				break;
			case '2' :
				$ret = JText::_('VRWEEKDAYTWO');
				break;
			case '1' :
				$ret = JText::_('VRWEEKDAYONE');
				break;
			default :
				$ret = JText::_('VRWEEKDAYZERO');
				break;
		}
		return $ret;
	}
	
	public static function sayMonth($idm) {
		switch ($idm) {
			case '12' :
				$ret = JText::_('VRMONTHTWELVE');
				break;
			case '11' :
				$ret = JText::_('VRMONTHELEVEN');
				break;
			case '10' :
				$ret = JText::_('VRMONTHTEN');
				break;
			case '9' :
				$ret = JText::_('VRMONTHNINE');
				break;
			case '8' :
				$ret = JText::_('VRMONTHEIGHT');
				break;
			case '7' :
				$ret = JText::_('VRMONTHSEVEN');
				break;
			case '6' :
				$ret = JText::_('VRMONTHSIX');
				break;
			case '5' :
				$ret = JText::_('VRMONTHFIVE');
				break;
			case '4' :
				$ret = JText::_('VRMONTHFOUR');
				break;
			case '3' :
				$ret = JText::_('VRMONTHTHREE');
				break;
			case '2' :
				$ret = JText::_('VRMONTHTWO');
				break;
			default :
				$ret = JText::_('VRMONTHONE');
				break;
		}
		return $ret;
	}
	
	public static function valuecsv ($value) {
		if (preg_match("/\"/", $value)) {
			$value = '"'.str_replace('"', '""', $value).'"';
		}
		$value = str_replace(',', ' ', $value);
		$value = str_replace(';', ' ', $value);
		return $value;
	}
	
	public static function getMinDaysAdvance($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='mindaysadvance';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return (int)$s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vrcminDaysAdvance', '');
			if (!empty($sval)) {
				return (int)$sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='mindaysadvance';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vrcminDaysAdvance', $s[0]['setting']);
				return (int)$s[0]['setting'];
			}
		}
	}
	
	public static function getMaxDateFuture($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='maxdate';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vrcmaxDateFuture', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='maxdate';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vrcmaxDateFuture', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function getFirstWeekDay($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='firstwday';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vrcfirstWeekDay', '');
			if (strlen($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='firstwday';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vrcfirstWeekDay', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}

	public static function getDefaultDistinctiveFeatures() {
		$features = array();
		$features['VRCDEFAULTDISTFEATUREONE'] = '';
		$features['VRCDEFAULTDISTFEATURETWO'] = '';
		$features['VRCDEFAULTDISTFEATURETHREE'] = '';
		$features['VRCDEFAULTDISTFEATUREFOUR'] = '';
		return $features;
	}

	public static function getCarUnitNumsUnavailable($order) {
		$dbo = JFactory::getDBO();
		$unavailable_indexes = array();
		$first = $order['ritiro'];
		$second = $order['consegna'];
		$secdiff = $second - $first;
		$daysdiff = $secdiff / 86400;
		if (is_int($daysdiff)) {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			}
		} else {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			} else {
				$sum = floor($daysdiff) * 86400;
				$newdiff = $secdiff - $sum;
				$maxhmore = self::getHoursMoreRb() * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}
		$groupdays = self::getGroupDays($first, $second, $daysdiff);
		$q = "SELECT `b`.`id`,`b`.`ritiro`,`b`.`realback`,`b`.`stop_sales`,`o`.`id` AS `idorder`,`o`.`carindex` FROM `#__vikrentcar_busy` AS `b` LEFT JOIN `#__vikrentcar_orders` `o` ON `b`.`id`=`o`.`idbusy` WHERE `b`.`idcar`=" . $dbo->quote($order['idcar']) . " AND `b`.`consegna` > ".time()." AND `o`.`id`!=".$order['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$busy = $dbo->loadAssocList();
			foreach ($groupdays as $gday) {
				$bfound = 0;
				foreach ($busy as $bu) {
					if (empty($bu['carindex']) || empty($bu['idorder'])) {
						continue;
					}
					if ($gday >= $bu['ritiro'] && $gday <= $bu['realback']) {
						$unavailable_indexes[$bu['idorder']] = $bu['carindex'];
					} elseif (count($groupdays) == 2 && $gday == $groupdays[0]) {
						if ($groupdays[0] < $bu['ritiro'] && $groupdays[0] < $bu['realback'] && $groupdays[1] > $bu['ritiro'] && $groupdays[1] > $bu['realback']) {
							$unavailable_indexes[$bu['idorder']] = $bu['carindex'];
						}
					}
				}
			}
		}

		return $unavailable_indexes;
	}
	
	public static function pushCarCharacteristics($current, $characts_str) {
		if (!empty($characts_str)) {
			$all_characts = explode(';', $characts_str);
			foreach ($all_characts as $charact) {
				if (!empty($charact)) {
					if (!array_key_exists($charact, $current)) {
						$current[$charact] = 1;
					} else {
						$current[$charact]++;
					}
				}
			}
		}

		return $current;
	}

	public static function loadCharacteristics($ids, $vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$characterists = array();
		if (count($ids) > 0) {

			$q = "SELECT * FROM `#__vikrentcar_caratteristiche` WHERE `id` IN (".implode(",", $ids).") ORDER BY `#__vikrentcar_caratteristiche`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$fetch = $dbo->loadAssocList();
				if (is_object($vrc_tn)) {
					$vrc_tn->translateContents($fetch, '#__vikrentcar_caratteristiche');
				}
				foreach ($fetch as $value) {
					$characterists[$value['id']] = $value;
				}
			}
		}
		return $characterists;
	}

	public static function sortCharacteristics($current, $map) {

		$sorted = array();
		$sortmap = array();
		foreach ($current as $k => $v) {
			$sortmap[$k] = $map[$k]['ordering'];
		}
		asort($sortmap);
		foreach ($sortmap as $k => $v) {
			$sorted[$k] = $current[$k];
		}
		return $sorted;
	}

	public static function getLoginReturnUrl($url = '', $xhtml = false) {
		if ( empty($url) ) {
			// get current URL
			$url = JURI::current();

			$qs = JFactory::getApplication()->input->server->get('QUERY_STRING', '', 'string');
			// concat query string is not empty
			return $url . (strlen($qs) ? '?'.$qs : '');
		}
		// parse given URL
		$parts = parse_url(Juri::root());
		// build host
		$host = (!empty($parts['scheme']) ? $parts['scheme'] . '://' : '') . (!empty($parts['host']) ? $parts['host'] : '');
		// concat host (use trailing slash if not exists) and routed URL (remove first slash if exists)
		return $host.(!strlen($host) || $host[strlen($host)-1] != '/' ? '/' : '').(strlen($route = JRoute::_($url, $xhtml)) && $route[0] == '/' ? substr($route, 1) : $route);
	}

	public static function displayPaymentParameters ($pfile, $pparams = '') {
		$html = '---------';
		$arrparams = !empty($pparams) ? json_decode($pparams, true) : array();
		if (file_exists(VRC_ADMIN_PATH.DS.'payments'.DS.$pfile) && !empty($pfile)) {
			require_once(VRC_ADMIN_PATH.DS.'payments'.DS.$pfile);
			if (method_exists('vikRentCarPayment', 'getAdminParameters')) {
				$pconfig = vikRentCarPayment::getAdminParameters();
				if (count($pconfig) > 0) {
					$html = '';
					foreach ($pconfig as $value => $cont) {
						if (empty($value)) {
							continue;
						}
						$labelparts = explode('//', $cont['label']);
						$label = $labelparts[0];
						$labelhelp = $labelparts[1];
						$html .= '<div class="vikpaymentparam">';
						if (strlen($label) > 0) {
							$html .= '<span class="vikpaymentparamlabel">'.$label.'</span>';
						}
						switch ($cont['type']) {
							case 'custom':
								$html .= $cont['html'];
								break;
							case 'select':
								$html .= '<span class="vikpaymentparaminput">' .
									'<select name="vikpaymentparams['.$value.']">';
								foreach ($cont['options'] as $poption) {
									$html .= '<option value="'.$poption.'"'.(array_key_exists($value, $arrparams) && $poption == $arrparams[$value] ? ' selected="selected"' : '').'>'.$poption.'</option>';
								}
								$html .= '</select></span>';
								break;
							default:
								$html .= '<span class="vikpaymentparaminput">' .
									'<input type="text" name="vikpaymentparams['.$value.']" value="'.(array_key_exists($value, $arrparams) ? $arrparams[$value] : '').'" size="20"/>' .
									'</span>';
								break;
						}
						if (strlen($labelhelp) > 0) {
							$html .= '<span class="vikpaymentparamlabelhelp">'.$labelhelp.'</span>';
						}
						$html .= '</div>';
					}
				}
			}
		}
		return $html;
	}

	public static function getVrcApplication() {
		if (!class_exists('VrcApplication')) {
			require_once(VRC_ADMIN_PATH.DS.'helpers'.DS.'jv_helper.php');
		}
		return new VrcApplication();
	}

	public static function totElements($arr) {
		$n = 0;
		if (is_array($arr)) {
			foreach ($arr as $a) {
				if (!empty ($a)) {
					$n++;
				}
			}
			return $n;
		}
		return false;
	}

	public static function validEmail($email) {
		$isValid = true;
		$atIndex = strrpos($email, "@");
		if (is_bool($atIndex) && !$atIndex) {
			$isValid = false;
		} else {
			$domain = substr($email, $atIndex +1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if ($localLen < 1 || $localLen > 64) {
				// local part length exceeded
				$isValid = false;
			} else
				if ($domainLen < 1 || $domainLen > 255) {
					// domain part length exceeded
					$isValid = false;
				} else
					if ($local[0] == '.' || $local[$localLen -1] == '.') {
						// local part starts or ends with '.'
						$isValid = false;
					} else
						if (preg_match('/\\.\\./', $local)) {
							// local part has two consecutive dots
							$isValid = false;
						} else
							if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
								// character not valid in domain part
								$isValid = false;
							} else
								if (preg_match('/\\.\\./', $domain)) {
									// domain part has two consecutive dots
									$isValid = false;
								} else
									if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
										// character not valid in local part unless 
										// local part is quoted
										if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
											$isValid = false;
										}
									}
			if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
				// domain not found in DNS
				$isValid = false;
			}
		}
		return $isValid;
	}

	public static function caniWrite($path) {
		if ($path[strlen($path) - 1] == '/') {
			// ricorsivo return a temporary file path
			return self::caniWrite($path . uniqid(mt_rand()) . '.tmp');
		}
		if (is_dir($path)) {
			return self::caniWrite($path . '/' . uniqid(mt_rand()) . '.tmp');
		}
		// check tmp file for read/write capabilities
		$rm = file_exists($path);
		$f = @fopen($path, 'a');
		if ($f === false) {
			return false;
		}
		fclose($f);
		if (!$rm) {
			unlink($path);
		}
		return true;
	}

	/**
	 * Alias method of JFile::upload to unify any
	 * upload function into one.
	 * 
	 * @param   string   $src 			The name of the php (temporary) uploaded file.
	 * @param   string   $dest 			The path (including filename) to move the uploaded file to.
	 * @param   boolean  [$copy_only] 	Whether to skip the file upload and just copy the file.
	 * 
	 * @return  boolean  True on success.
	 * 
	 * @since 	1.12 - For compatibility with the VikWP Framework.
	 */
	public static function uploadFile($src, $dest, $copy_only = false) {
		// always attempt to include the File class
		jimport('joomla.filesystem.file');

		// upload the file
		if (!$copy_only) {
			$result = JFile::upload($src, $dest);
		} else {
			// this is to avoid the use of the PHP function copy() and allow files mirroring in WP (triggerUploadBackup)
			$result = JFile::copy($src, $dest);
		}

		// return upload result
		return $result;
	}

}

class vikResizer {

	function proportionalImage($fileimg, $dest, $towidth, $toheight) {
		if (!file_exists($fileimg)) {
			return false;
		}
		if (empty ($towidth) && empty ($toheight)) {
			copy($fileimg, $dest);
			return true;
		}

		list ($owid, $ohei, $type) = getimagesize($fileimg);

		if ($owid > $towidth || $ohei > $toheight) {
			$xscale = $owid / $towidth;
			$yscale = $ohei / $toheight;
			if ($yscale > $xscale) {
				$new_width = round($owid * (1 / $yscale));
				$new_height = round($ohei * (1 / $yscale));
			} else {
				$new_width = round($owid * (1 / $xscale));
				$new_height = round($ohei * (1 / $xscale));
			}

			$imageresized = imagecreatetruecolor($new_width, $new_height);

			switch ($type) {
				case '1' :
					$imagetmp = imagecreatefromgif ($fileimg);
					break;
				case '2' :
					$imagetmp = imagecreatefromjpeg($fileimg);
					break;
				default :
					$imagetmp = imagecreatefrompng($fileimg);
					break;
			}

			imagecopyresampled($imageresized, $imagetmp, 0, 0, 0, 0, $new_width, $new_height, $owid, $ohei);

			switch ($type) {
				case '1' :
					imagegif ($imageresized, $dest);
					break;
				case '2' :
					imagejpeg($imageresized, $dest);
					break;
				default :
					imagepng($imageresized, $dest);
					break;
			}

			imagedestroy($imageresized);
			return true;
		} else {
			copy($fileimg, $dest);
		}
		return true;
	}

	function bandedImage($fileimg, $dest, $towidth, $toheight, $rgb) {
		if (!file_exists($fileimg)) {
			return false;
		}
		if (empty ($towidth) && empty ($toheight)) {
			copy($fileimg, $dest);
			return true;
		}

		$exp = explode(",", $rgb);
		if (count($exp) == 3) {
			$r = trim($exp[0]);
			$g = trim($exp[1]);
			$b = trim($exp[2]);
		} else {
			$r = 0;
			$g = 0;
			$b = 0;
		}

		list ($owid, $ohei, $type) = getimagesize($fileimg);

		if ($owid > $towidth || $ohei > $toheight) {
			$xscale = $owid / $towidth;
			$yscale = $ohei / $toheight;
			if ($yscale > $xscale) {
				$new_width = round($owid * (1 / $yscale));
				$new_height = round($ohei * (1 / $yscale));
				$ydest = 0;
				$diff = $towidth - $new_width;
				$xdest = ($diff > 0 ? round($diff / 2) : 0);
			} else {
				$new_width = round($owid * (1 / $xscale));
				$new_height = round($ohei * (1 / $xscale));
				$xdest = 0;
				$diff = $toheight - $new_height;
				$ydest = ($diff > 0 ? round($diff / 2) : 0);
			}

			$imageresized = imagecreatetruecolor($towidth, $toheight);

			$bgColor = imagecolorallocate($imageresized, (int) $r, (int) $g, (int) $b);
			imagefill($imageresized, 0, 0, $bgColor);

			switch ($type) {
				case '1' :
					$imagetmp = imagecreatefromgif ($fileimg);
					break;
				case '2' :
					$imagetmp = imagecreatefromjpeg($fileimg);
					break;
				default :
					$imagetmp = imagecreatefrompng($fileimg);
					break;
			}

			imagecopyresampled($imageresized, $imagetmp, $xdest, $ydest, 0, 0, $new_width, $new_height, $owid, $ohei);

			switch ($type) {
				case '1' :
					imagegif ($imageresized, $dest);
					break;
				case '2' :
					imagejpeg($imageresized, $dest);
					break;
				default :
					imagepng($imageresized, $dest);
					break;
			}

			imagedestroy($imageresized);

			return true;
		} else {
			copy($fileimg, $dest);
		}
		return true;
	}

	function croppedImage($fileimg, $dest, $towidth, $toheight) {
		if (!file_exists($fileimg)) {
			return false;
		}
		if (empty ($towidth) && empty ($toheight)) {
			copy($fileimg, $dest);
			return true;
		}

		list ($owid, $ohei, $type) = getimagesize($fileimg);

		if ($owid <= $ohei) {
			$new_width = $towidth;
			$new_height = ($towidth / $owid) * $ohei;
		} else {
			$new_height = $toheight;
			$new_width = ($new_height / $ohei) * $owid;
		}

		switch ($type) {
			case '1' :
				$img_src = imagecreatefromgif ($fileimg);
				$img_dest = imagecreate($new_width, $new_height);
				break;
			case '2' :
				$img_src = imagecreatefromjpeg($fileimg);
				$img_dest = imagecreatetruecolor($new_width, $new_height);
				break;
			default :
				$img_src = imagecreatefrompng($fileimg);
				$img_dest = imagecreatetruecolor($new_width, $new_height);
				break;
		}

		imagecopyresampled($img_dest, $img_src, 0, 0, 0, 0, $new_width, $new_height, $owid, $ohei);

		switch ($type) {
			case '1' :
				$cropped = imagecreate($towidth, $toheight);
				break;
			case '2' :
				$cropped = imagecreatetruecolor($towidth, $toheight);
				break;
			default :
				$cropped = imagecreatetruecolor($towidth, $toheight);
				break;
		}

		imagecopy($cropped, $img_dest, 0, 0, 0, 0, $owid, $ohei);

		switch ($type) {
			case '1' :
				imagegif ($cropped, $dest);
				break;
			case '2' :
				imagejpeg($cropped, $dest);
				break;
			default :
				imagepng($cropped, $dest);
				break;
		}

		imagedestroy($img_dest);
		imagedestroy($cropped);

		return true;
	}

}

function encryptCookie($str) {
	for ($i = 0; $i < 5; $i++) {
		$str = strrev(base64_encode($str));
	}
	return $str;
}

function decryptCookie($str) {
	for ($i = 0; $i < 5; $i++) {
		$str = base64_decode(strrev($str));
	}
	return $str;
}

function read($str) {
	$var = '';
	for ($i = 0; $i < strlen($str); $i += 2)
		$var .= chr(hexdec(substr($str, $i, 2)));
	return $var;
}

function checkComp($lf, $h, $n) {
	$a = $lf[0];
	$b = $lf[1];
	for ($i = 0; $i < 5; $i++) {
		$a = base64_decode(strrev($a));
		$b = base64_decode(strrev($b));
	}
	if ($a == $h || $b == $h || $a == $n || $b == $n) {
		return true;
	} else {
		$a = str_replace('www.', "", $a);
		$b = str_replace('www.', "", $b);
		if ((!empty ($a) && (preg_match("/" . $a . "/i", $h) || preg_match("/" . $a . "/i", $n))) || (!empty ($b) && (preg_match("/" . $b . "/i", $h) || preg_match("/" . $b . "/i", $n)))) {
			return true;
		}
	}
	return false;
}

define('CREATIVIKAPP', 'com_vikrentcar');
defined('E4J_SOFTWARE_VERSION') or define('E4J_SOFTWARE_VERSION', '1.12');

class CreativikDotIt {
	function __construct() {
		$this->headers = array (
			"Referer" => "",
			"User-Agent" => "CreativikDotIt/1.0",
			"Connection" => "close"
		);
		$this->version = "1.1";
		$this->ctout = 15;
		$this->f_redha = false;
	}

	function exeqer($url) {
		$rcodes = array (
			301,
			302,
			303,
			307
		);
		$rmeth = array (
			'GET',
			'HEAD'
		);
		$rres = false;
		$this->fd_redhad = false;
		$ppred = array ();
		do {
			$rres = $this->sendout($url);
			$url = false;
			if ($this->f_redha && in_array($this->edocser, $rcodes)) {
				if (($this->edocser == 303) || in_array($this->method, $rmeth)) {
					$url = $this->resphh['Location'];
				}
			}
			if ($url && strlen($url)) {
				if (isset ($ppred[$url])) {
					$this->rore = "tceriderpool";
					$rres = false;
					break;
				}
				if (is_numeric($this->f_redha) && (count($ppred) > $this->f_redha)) {
					$this->rore = "tceriderynamoot";
					$rres = false;
					break;
				}
				$ppred[$url] = true;
			}
		} while ($url && strlen($url));
		$rep_qer_daeh = array (
			'Host',
			'Content-Length'
		);
		foreach ($rep_qer_daeh as $k => $v)
			unset ($this->headers[$v]);
		if (count($ppred) > 1)
			$this->fd_redhad = array_keys($ppred);
		return $rres;
	}

	function dliubh() {
		$daeh = "";
		foreach ($this->headers as $name => $value) {
			$value = trim($value);
			if (empty ($value))
				continue;
			$daeh .= "{$name}: $value\r\n";
		}
		$daeh .= "\r\n";
		return $daeh;
	}

	function sendout($url) {
		$time_request_start = time();
		$urldata = parse_url($url);
		if (!$urldata["port"])
			$urldata["port"] = ($urldata["scheme"] == "https") ? 443 : 80;
		if (!$urldata["path"])
			$urldata["path"] = '/';
		if ($this->version > "1.0")
			$this->headers["Host"] = $urldata["host"];
		unset ($this->headers['Authorization']);
		if (!empty ($urldata["query"]))
			$urldata["path"] .= "?" . $urldata["query"];
		$request = $this->method . " " . $urldata["path"] . " HTTP/" . $this->version . "\r\n";
		$request .= $this->dliubh();
		$this->tise = "";
		$hostname = $urldata['host'];
		$time_connect_start = time();
		$fp = @ fsockopen($hostname, $urldata["port"], $errno, $errstr, $this->ctout);
		$connect_time = time() - $time_connect_start;
		if ($fp) {
			stream_set_timeout($fp, 3);
			fputs($fp, $request);
			$meta = stream_get_meta_data($fp);
			if ($meta['timed_out']) {
				$this->rore = "sdnoceseerhtfotuoemitetirwtekcosdedeecxe";
				return false;
			}
			$cerdaeh = false;
			$data_length = false;
			$chunked = false;
			while (!feof($fp)) {
				if ($data_length > 0) {
					$line = fread($fp, $data_length);
					$data_length -= strlen($line);
				} else {
					$line = fgets($fp, 10240);
					if ($chunked) {
						$line = trim($line);
						if (!strlen($line))
							continue;
						list ($data_length,) = explode(';', $line);
						$data_length = (int) hexdec(trim($data_length));
						if ($data_length == 0) {
							break;
						}
						continue;
					}
				}
				$this->tise .= $line;
				if ((!$cerdaeh) && (trim($line) == "")) {
					$cerdaeh = true;
					if (preg_match('/\nContent-Length: ([0-9]+)/i', $this->tise, $matches)) {
						$data_length = (int) $matches[1];
					}
					if (preg_match("/\nTransfer-Encoding: chunked/i", $this->tise, $matches)) {
						$chunked = true;
					}
				}
				$meta = stream_get_meta_data($fp);
				if ($meta['timed_out']) {
					$this->rore = "sceseerhttuoemitdaertekcos";
					return false;
				}
				if (time() - $time_request_start > 5) {
					$this->rore = "maxtransfertimefivesecs";
					return false;
					break;
				}
			}
			fclose($fp);
		} else {
			$this->rore = $urldata['scheme'] . " otdeliafnoitcennoc " . $hostname . " trop " . $urldata['port'];
			return false;
		}
		do {
			$neldaeh = strpos($this->tise, "\r\n\r\n");
			$serp_daeh = explode("\r\n", substr($this->tise, 0, $neldaeh));
			$pthats = trim(array_shift($serp_daeh));
			foreach ($serp_daeh as $line) {
				list ($k, $v) = explode(":", $line, 2);
				$this->resphh[trim($k)] = trim($v);
			}
			$this->tise = substr($this->tise, $neldaeh +4);
			if (!preg_match("/^HTTP\/([0-9\.]+) ([0-9]+) (.*?)$/", $pthats, $matches)) {
				$matches = array (
					"",
					$this->version,
					0,
					"HTTP request error"
				);
			}
			list (, $pserver, $this->edocser, $this->txet) = $matches;
		} while (($this->edocser == 100) && ($neldaeh));
		$ok = ($this->edocser == 200);
		return $ok;
	}

	function ksa($url) {
		$this->method = "GET";
		return $this->exeqer($url);
	}

}

function validEmail($email) {
	$isValid = true;
	$atIndex = strrpos($email, "@");
	if (is_bool($atIndex) && !$atIndex) {
		$isValid = false;
	} else {
		$domain = substr($email, $atIndex +1);
		$local = substr($email, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);
		if ($localLen < 1 || $localLen > 64) {
			// local part length exceeded
			$isValid = false;
		} else
			if ($domainLen < 1 || $domainLen > 255) {
				// domain part length exceeded
				$isValid = false;
			} else
				if ($local[0] == '.' || $local[$localLen -1] == '.') {
					// local part starts or ends with '.'
					$isValid = false;
				} else
					if (preg_match('/\\.\\./', $local)) {
						// local part has two consecutive dots
						$isValid = false;
					} else
						if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
							// character not valid in domain part
							$isValid = false;
						} else
							if (preg_match('/\\.\\./', $domain)) {
								// domain part has two consecutive dots
								$isValid = false;
							} else
								if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
									// character not valid in local part unless 
									// local part is quoted
									if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
										$isValid = false;
									}
								}
		if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
			// domain not found in DNS
			$isValid = false;
		}
	}
	return $isValid;
}

function checkCodiceFiscale($cf) {
	if ($cf == '')
		return false;
	if (strlen($cf) != 16)
		return false;
	$cf = strtoupper(str_replace(" ", "", $cf));
	if (!ereg("^[A-Z0-9]+$", $cf)) {
		return false;
	}
	$s = 0;
	for ($i = 1; $i <= 13; $i += 2) {
		$c = $cf[$i];
		if ('0' <= $c && $c <= '9')
			$s += ord($c) - ord('0');
		else
			$s += ord($c) - ord('A');
	}
	for ($i = 0; $i <= 14; $i += 2) {
		$c = $cf[$i];
		switch ($c) {
			case '0' :
				$s += 1;
				break;
			case '1' :
				$s += 0;
				break;
			case '2' :
				$s += 5;
				break;
			case '3' :
				$s += 7;
				break;
			case '4' :
				$s += 9;
				break;
			case '5' :
				$s += 13;
				break;
			case '6' :
				$s += 15;
				break;
			case '7' :
				$s += 17;
				break;
			case '8' :
				$s += 19;
				break;
			case '9' :
				$s += 21;
				break;
			case 'A' :
				$s += 1;
				break;
			case 'B' :
				$s += 0;
				break;
			case 'C' :
				$s += 5;
				break;
			case 'D' :
				$s += 7;
				break;
			case 'E' :
				$s += 9;
				break;
			case 'F' :
				$s += 13;
				break;
			case 'G' :
				$s += 15;
				break;
			case 'H' :
				$s += 17;
				break;
			case 'I' :
				$s += 19;
				break;
			case 'J' :
				$s += 21;
				break;
			case 'K' :
				$s += 2;
				break;
			case 'L' :
				$s += 4;
				break;
			case 'M' :
				$s += 18;
				break;
			case 'N' :
				$s += 20;
				break;
			case 'O' :
				$s += 11;
				break;
			case 'P' :
				$s += 3;
				break;
			case 'Q' :
				$s += 6;
				break;
			case 'R' :
				$s += 8;
				break;
			case 'S' :
				$s += 12;
				break;
			case 'T' :
				$s += 14;
				break;
			case 'U' :
				$s += 16;
				break;
			case 'V' :
				$s += 10;
				break;
			case 'W' :
				$s += 22;
				break;
			case 'X' :
				$s += 25;
				break;
			case 'Y' :
				$s += 24;
				break;
			case 'Z' :
				$s += 23;
				break;
		}
	}
	if (chr($s % 26 + ord('A')) != $cf[15])
		return false;
	return true;
}

function secureString($string) {
	$search = array (
		'/<\?((?!\?>).)*\?>/s'
	);
	return preg_replace($search, '', $string);
}

function cleanString4Db($str) {
	$var = $str;
	if (get_magic_quotes_gpc()) {
		$var = stripslashes($str);
	}
	$var = str_replace("'", "`", $var);
	return secureString($var);
}

function realInt($num) {
	for ($i = 0; $i < strlen($num); $i++) {
		if (!ctype_digit($num {
			$i })) {
			return false;
		}
	}
	return true;
}

function realDecimal($num) {
	for ($i = 0; $i < strlen($num); $i++) {
		if (!ctype_digit($num {
			$i }) && $num {
			$i }
		!= "." && $num {
			$i }
		!= ",") {
			return false;
		}
	}
	return true;
}
