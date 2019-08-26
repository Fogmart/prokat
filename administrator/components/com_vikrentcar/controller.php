<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');

class VikRentCarController extends JControllerVikRentCar {

	/**
	 * Default controller's method when no task is defined,
	 * or no method exists for that task. If a View is requested.
	 * attempts to set it, otherwise sets the default View.
	 */
	function display($cachable = false, $urlparams = array()) {

		$view = VikRequest::getVar('view', '');
		$header_val = '';

		if (!empty($view)) {
			VikRequest::setVar('view', $view);
		} else {
			$header_val = '18';
			VikRequest::setVar('view', 'dashboard');
		}

		VikRentCarHelper::printHeader($header_val);
		
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function places() {
		VikRentCarHelper::printHeader("3");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'places'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newplace() {
		VikRentCarHelper::printHeader("3");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageplace'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editplace() {
		VikRentCarHelper::printHeader("3");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageplace'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function createplace() {
		$dbo = JFactory::getDBO();
		$pname = VikRequest::getString('placename', '', 'request');
		$paddress = VikRequest::getString('address', '', 'request');
		$plat = VikRequest::getString('lat', '', 'request');
		$plng = VikRequest::getString('lng', '', 'request');
		$ppraliq = VikRequest::getString('praliq', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		$popentimefh = VikRequest::getString('opentimefh', '', 'request');
		$popentimefm = VikRequest::getInt('opentimefm', '', 'request');
		$popentimeth = VikRequest::getString('opentimeth', '', 'request');
		$popentimetm = VikRequest::getInt('opentimetm', '', 'request');
		$pclosingdays = VikRequest::getString('closingdays', '', 'request');
		$psuggopentimeh = VikRequest::getInt('suggopentimeh', '', 'request');
		$pwopeningfh = VikRequest::getVar('wopeningfh', array());
		$pwopeningfm = VikRequest::getVar('wopeningfm', array());
		$pwopeningth = VikRequest::getVar('wopeningth', array());
		$pwopeningtm = VikRequest::getVar('wopeningtm', array());
		$opentime = "";
		$suggopentimeh = !empty($psuggopentimeh) ? ($psuggopentimeh * 3600) : '';
		if (strlen($popentimefh) > 0 && strlen($popentimeth) > 0) {
			$openingh = $popentimefh * 3600;
			$openingm = $popentimefm * 60;
			$openingts = $openingh + $openingm;
			$closingh = $popentimeth * 3600;
			$closingm = $popentimetm * 60;
			$closingts = $closingh + $closingm;
			if ($closingts > $openingts || $openingts > $closingts) {
				$opentime = $openingts."-".$closingts;
			}
		}
		if (!empty($pname)) {
			$q = "SELECT `ordering` FROM `#__vikrentcar_places` ORDER BY `#__vikrentcar_places`.`ordering` DESC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$getlast = $dbo->loadResult();
				$newsortnum = $getlast + 1;
			} else {
				$newsortnum = 1;
			}
			
			// VRC 1.12 - override opening time
			$wopening = array();
			if (count($pwopeningfh)) {
				foreach ($pwopeningfh as $d_ind => $fh) {
					if (!strlen($fh) || isset($wopeing[$d_ind]) || $d_ind > 6 || !isset($pwopeningth[$d_ind]) || !strlen($pwopeningth[$d_ind])) {
						continue;
					}
					$wopening[$d_ind] = array(
						'fh' => (int)$fh,
						'fm' => (int)$pwopeningfm[$d_ind],
						'th' => (int)$pwopeningth[$d_ind],
						'tm' => (int)$pwopeningtm[$d_ind]
					);
				}
			}
			//

			$q = "INSERT INTO `#__vikrentcar_places` (`name`,`lat`,`lng`,`descr`,`opentime`,`closingdays`,`idiva`,`defaulttime`,`ordering`,`address`,`wopening`) VALUES(".$dbo->quote($pname).", ".$dbo->quote($plat).", ".$dbo->quote($plng).", ".$dbo->quote($pdescr).", '".$opentime."', ".$dbo->quote($pclosingdays).", ".(!empty($ppraliq) ? intval($ppraliq) : "NULL").", ".(!empty($suggopentimeh) ? "'".$suggopentimeh."'" : "NULL").", ".$newsortnum.", ".$dbo->quote($paddress).", ".$dbo->quote(json_encode($wopening)).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=places");
	}

	function updateplace() {
		$dbo = JFactory::getDBO();
		$pname = VikRequest::getString('placename', '', 'request');
		$paddress = VikRequest::getString('address', '', 'request');
		$plat = VikRequest::getString('lat', '', 'request');
		$plng = VikRequest::getString('lng', '', 'request');
		$ppraliq = VikRequest::getString('praliq', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$popentimefh = VikRequest::getString('opentimefh', '', 'request');
		$popentimefm = VikRequest::getInt('opentimefm', '', 'request');
		$popentimeth = VikRequest::getString('opentimeth', '', 'request');
		$popentimetm = VikRequest::getInt('opentimetm', '', 'request');
		$pclosingdays = VikRequest::getString('closingdays', '', 'request');
		$psuggopentimeh = VikRequest::getInt('suggopentimeh', '', 'request');
		$pwopeningfh = VikRequest::getVar('wopeningfh', array());
		$pwopeningfm = VikRequest::getVar('wopeningfm', array());
		$pwopeningth = VikRequest::getVar('wopeningth', array());
		$pwopeningtm = VikRequest::getVar('wopeningtm', array());
		$opentime = "";
		$suggopentimeh = !empty($psuggopentimeh) ? ($psuggopentimeh * 3600) : '';
		if (strlen($popentimefh) > 0 && strlen($popentimeth) > 0) {
			$openingh = $popentimefh * 3600;
			$openingm = $popentimefm * 60;
			$openingts = $openingh + $openingm;
			$closingh = $popentimeth * 3600;
			$closingm = $popentimetm * 60;
			$closingts = $closingh + $closingm;
			if ($closingts > $openingts || $openingts > $closingts) {
				$opentime = $openingts."-".$closingts;
			}
		}
		if (!empty($pname)) {
			
			// VRC 1.12 - override opening time
			$wopening = array();
			if (count($pwopeningfh)) {
				foreach ($pwopeningfh as $d_ind => $fh) {
					if (!strlen($fh) || isset($wopeing[$d_ind]) || $d_ind > 6 || !isset($pwopeningth[$d_ind]) || !strlen($pwopeningth[$d_ind])) {
						continue;
					}
					$wopening[$d_ind] = array(
						'fh' => (int)$fh,
						'fm' => (int)$pwopeningfm[$d_ind],
						'th' => (int)$pwopeningth[$d_ind],
						'tm' => (int)$pwopeningtm[$d_ind]
					);
				}
			}
			//

			$q = "UPDATE `#__vikrentcar_places` SET `name`=".$dbo->quote($pname).",`lat`=".$dbo->quote($plat).",`lng`=".$dbo->quote($plng).",`descr`=".$dbo->quote($pdescr).",`opentime`='".$opentime."',`closingdays`=".$dbo->quote($pclosingdays).",`idiva`=".(!empty($ppraliq) ? intval($ppraliq) : "NULL").",`defaulttime`=".(!empty($suggopentimeh) ? "'".$suggopentimeh."'" : "NULL").",`address`=".$dbo->quote($paddress).",`wopening`=".$dbo->quote(json_encode($wopening))." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=places");
	}

	function removeplace() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_places` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=places");
	}

	function cancelplace() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=places");
	}

	function iva() {
		VikRentCarHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'iva'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newiva() {
		VikRentCarHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageiva'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editiva() {
		VikRentCarHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageiva'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function createiva() {
		$paliqname = VikRequest::getString('aliqname', '', 'request');
		$paliqperc = VikRequest::getString('aliqperc', '', 'request');
		if (!empty($paliqperc)) {
			$dbo = JFactory::getDBO();
			$q = "INSERT INTO `#__vikrentcar_iva` (`name`,`aliq`) VALUES(".$dbo->quote($paliqname).", ".floatval($paliqperc).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=iva");
	}

	function updateiva() {
		$paliqname = VikRequest::getString('aliqname', '', 'request');
		$paliqperc = VikRequest::getString('aliqperc', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		if (!empty($paliqperc)) {
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikrentcar_iva` SET `name`=".$dbo->quote($paliqname).",`aliq`=".floatval($paliqperc)." WHERE `id`=".intval($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=iva");
	}

	function removeiva() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_iva` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=iva");
	}

	function canceliva() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=iva");
	}

	function prices() {
		VikRentCarHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'prices'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newprice() {
		VikRentCarHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageprice'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editprice() {
		VikRentCarHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageprice'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function createprice() {
		$pprice = VikRequest::getString('price', '', 'request');
		$pattr = VikRequest::getString('attr', '', 'request');
		$ppraliq = VikRequest::getString('praliq', '', 'request');
		if (!empty($pprice)) {
			$dbo = JFactory::getDBO();
			$q = "INSERT INTO `#__vikrentcar_prices` (`name`,`attr`,`idiva`) VALUES(".$dbo->quote($pprice).", ".$dbo->quote($pattr).", ".(!empty($ppraliq) ? intval($ppraliq) : 'NULL').");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=prices");
	}

	function updateprice() {
		$pprice = VikRequest::getString('price', '', 'request');
		$pattr = VikRequest::getString('attr', '', 'request');
		$ppraliq = VikRequest::getString('praliq', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		if (!empty($pprice)) {
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikrentcar_prices` SET `name`=".$dbo->quote($pprice).",`attr`=".$dbo->quote($pattr).",`idiva`=".(!empty($ppraliq) ? intval($ppraliq) : 'NULL')." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=prices");
	}

	function removeprice() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_prices` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=prices");
	}

	function cancelprice() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=prices");
	}

	function categories() {
		VikRentCarHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'categories'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newcat() {
		VikRentCarHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecat'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editcat() {
		VikRentCarHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecat'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function createcat() {
		$pcatname = VikRequest::getString('catname', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		if (!empty($pcatname)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `ordering` FROM `#__vikrentcar_categories` ORDER BY `#__vikrentcar_categories`.`ordering` DESC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$getlast = $dbo->loadResult();
				$newsortnum = $getlast + 1;
			} else {
				$newsortnum = 1;
			}
			$q = "INSERT INTO `#__vikrentcar_categories` (`name`,`descr`,`ordering`) VALUES(".$dbo->quote($pcatname).", ".$dbo->quote($pdescr).", ".(int)$newsortnum.");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=categories");
	}

	function updatecat() {
		$pcatname = VikRequest::getString('catname', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		if (!empty($pcatname)) {
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikrentcar_categories` SET `name`=".$dbo->quote($pcatname).", `descr`=".$dbo->quote($pdescr)." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=categories");
	}

	function removecat() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_categories` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=categories");
	}

	function cancelcat() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=categories");
	}

	function carat() {
		VikRentCarHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'carat'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newcarat() {
		VikRentCarHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecarat'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editcarat() {
		VikRentCarHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecarat'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function createcarat() {
		$pcaratname = VikRequest::getString('caratname', '', 'request');
		$pcaratmix = VikRequest::getString('caratmix', '', 'request');
		$pcarattextimg = VikRequest::getString('carattextimg', '', 'request', VIKREQUEST_ALLOWHTML);
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		if (!empty($pcaratname)) {
			if (intval($_FILES['caraticon']['error']) == 0 && VikRentCar::caniWrite(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR) && trim($_FILES['caraticon']['name'])!="") {
				jimport('joomla.filesystem.file');
				if (@is_uploaded_file($_FILES['caraticon']['tmp_name'])) {
					$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['caraticon']['name'])));
					if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
						$j = 1;
						while (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
							$j++;
						}
						$pwhere = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
					} else {
						$j = "";
						$pwhere = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
					}
					VikRentCar::uploadFile($_FILES['caraticon']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon = "";
					} else {
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize == "1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			$dbo = JFactory::getDBO();
			$q = "SELECT `ordering` FROM `#__vikrentcar_caratteristiche` ORDER BY `#__vikrentcar_caratteristiche`.`ordering` DESC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$getlast = $dbo->loadResult();
				$newsortnum = $getlast + 1;
			} else {
				$newsortnum = 1;
			}
			$q = "INSERT INTO `#__vikrentcar_caratteristiche` (`name`,`icon`,`align`,`textimg`,`ordering`) VALUES(".$dbo->quote($pcaratname).", ".$dbo->quote($picon).", ".$dbo->quote($pcaratmix).", ".$dbo->quote($pcarattextimg).", '".$newsortnum."');";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=carat");
	}

	function updatecarat() {
		$pcaratname = VikRequest::getString('caratname', '', 'request');
		$pcaratmix = VikRequest::getString('caratmix', '', 'request');
		$pcarattextimg = VikRequest::getString('carattextimg', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		if (!empty($pcaratname)) {
			if (intval($_FILES['caraticon']['error']) == 0 && VikRentCar::caniWrite(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR) && trim($_FILES['caraticon']['name'])!="") {
				jimport('joomla.filesystem.file');
				if (@is_uploaded_file($_FILES['caraticon']['tmp_name'])) {
					$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['caraticon']['name'])));
					if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
						$j = 1;
						while (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
							$j++;
						}
						$pwhere=VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
					} else {
						$j = "";
						$pwhere = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
					}
					VikRentCar::uploadFile($_FILES['caraticon']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon = "";
					} else {
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize == "1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikrentcar_caratteristiche` SET `name`=".$dbo->quote($pcaratname).",".(strlen($picon) > 0 ? "`icon`='".$picon."'," : "")."`align`=".$dbo->quote($pcaratmix).",`textimg`=".$dbo->quote($pcarattextimg)." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=carat");
	}

	function removecarat() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "SELECT `icon` FROM `#__vikrentcar_caratteristiche` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					if (!empty($rows[0]['icon']) && file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$rows[0]['icon'])) {
						@unlink(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$rows[0]['icon']);
					}
				}	
				$q = "DELETE FROM `#__vikrentcar_caratteristiche` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=carat");
	}

	function cancelcarat() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=carat");
	}

	function optionals() {
		VikRentCarHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'optionals'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newoptional() {
		VikRentCarHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageopt'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editoptional() {
		VikRentCarHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageopt'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function createoptional() {
		$poptname = VikRequest::getString('optname', '', 'request');
		$poptdescr = VikRequest::getString('optdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$poptcost = VikRequest::getString('optcost', '', 'request');
		$poptperday = VikRequest::getString('optperday', '', 'request');
		$pmaxprice = VikRequest::getString('maxprice', '', 'request');
		$popthmany = VikRequest::getString('opthmany', '', 'request');
		$poptaliq = VikRequest::getString('optaliq', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pforcesel = VikRequest::getString('forcesel', '', 'request');
		$pforceval = VikRequest::getString('forceval', '', 'request');
		$pforceifdays = VikRequest::getInt('forceifdays', '', 'request');
		$pforcevalperday = VikRequest::getString('forcevalperday', '', 'request');
		$pforcesel = $pforcesel == "1" ? 1 : 0;
		if ($pforcesel == 1) {
			$strforceval = intval($pforceval)."-".($pforcevalperday == "1" ? "1" : "0");
		} else {
			$strforceval = "";
		}
		if (!empty($poptname)) {
			if (intval($_FILES['optimg']['error']) == 0 && VikRentCar::caniWrite(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR) && trim($_FILES['optimg']['name'])!="") {
				jimport('joomla.filesystem.file');
				if (@is_uploaded_file($_FILES['optimg']['tmp_name'])) {
					$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['optimg']['name'])));
					if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
						$j = 1;
						while (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
							$j++;
						}
						$pwhere = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
					} else {
						$j = "";
						$pwhere = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
					}
					VikRentCar::uploadFile($_FILES['optimg']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon = "";
					} else {
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize == "1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			$poptperday = ($poptperday == "each" ? "1" : "0");
			($popthmany == "yes" ? $popthmany = "1" : $popthmany = "0");
			$dbo = JFactory::getDBO();
			$q = "SELECT `ordering` FROM `#__vikrentcar_optionals` ORDER BY `#__vikrentcar_optionals`.`ordering` DESC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$getlast = $dbo->loadResult();
				$newsortnum = $getlast + 1;
			} else {
				$newsortnum = 1;
			}
			$q = "INSERT INTO `#__vikrentcar_optionals` (`name`,`descr`,`cost`,`perday`,`hmany`,`img`,`idiva`,`maxprice`,`forcesel`,`forceval`,`ordering`,`forceifdays`) VALUES(".$dbo->quote($poptname).", ".$dbo->quote($poptdescr).", ".$dbo->quote($poptcost).", ".$dbo->quote($poptperday).", ".$dbo->quote($popthmany).", '".$picon."', ".$dbo->quote($poptaliq).", ".$dbo->quote($pmaxprice).", '".$pforcesel."', '".$strforceval."', '".$newsortnum."', '".$pforceifdays."');";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=optionals");
	}

	function updateoptional() {
		$poptname = VikRequest::getString('optname', '', 'request');
		$poptdescr = VikRequest::getString('optdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$poptcost = VikRequest::getString('optcost', '', 'request');
		$poptperday = VikRequest::getString('optperday', '', 'request');
		$pmaxprice = VikRequest::getString('maxprice', '', 'request');
		$popthmany = VikRequest::getString('opthmany', '', 'request');
		$poptaliq = VikRequest::getString('optaliq', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pforcesel = VikRequest::getString('forcesel', '', 'request');
		$pforceval = VikRequest::getString('forceval', '', 'request');
		$pforceifdays = VikRequest::getInt('forceifdays', '', 'request');
		$pforcevalperday = VikRequest::getString('forcevalperday', '', 'request');
		$pforcesel = $pforcesel == "1" ? 1 : 0;
		if ($pforcesel == 1) {
			$strforceval = intval($pforceval)."-".($pforcevalperday == "1" ? "1" : "0");
		} else {
			$strforceval = "";
		}
		if (!empty($poptname)) {
			if (intval($_FILES['optimg']['error']) == 0 && VikRentCar::caniWrite(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR) && trim($_FILES['optimg']['name'])!="") {
				jimport('joomla.filesystem.file');
				if (@is_uploaded_file($_FILES['optimg']['tmp_name'])) {
					$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['optimg']['name'])));
					if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
						$j = 1;
						while (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
							$j++;
						}
						$pwhere = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
					} else {
						$j = "";
						$pwhere = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
					}
					VikRentCar::uploadFile($_FILES['optimg']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon = "";
					} else {
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize == "1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			($poptperday == "each" ? $poptperday="1" : $poptperday="0");
			($popthmany == "yes" ? $popthmany="1" : $popthmany="0");
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikrentcar_optionals` SET `name`=".$dbo->quote($poptname).",`descr`=".$dbo->quote($poptdescr).",`cost`=".$dbo->quote($poptcost).",`perday`=".$dbo->quote($poptperday).",`hmany`=".$dbo->quote($popthmany).",".(strlen($picon)>0 ? "`img`='".$picon."'," : "")."`idiva`=".$dbo->quote($poptaliq).", `maxprice`=".$dbo->quote($pmaxprice).", `forcesel`='".$pforcesel."', `forceval`='".$strforceval."', `forceifdays`='".$pforceifdays."' WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=optionals");
	}

	function removeoptionals() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "SELECT `img` FROM `#__vikrentcar_optionals` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					if (!empty($rows[0]['img']) && file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$rows[0]['img'])) {
						@unlink(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$rows[0]['img']);
					}
				}	
				$q = "DELETE FROM `#__vikrentcar_optionals` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=optionals");
	}

	function canceloptional() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=optionals");
	}

	function stats() {
		VikRentCarHelper::printHeader("10");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'stats'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function removestats() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_stats` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=stats");
	}

	function cancelstats() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=stats");
	}

	function cars() {
		VikRentCarHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'cars'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newcar() {
		VikRentCarHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecar'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editcar() {
		VikRentCarHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecar'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function createcar() {
		$mainframe = JFactory::getApplication();
		$pcname = VikRequest::getString('cname', '', 'request');
		$pccat = VikRequest::getVar('ccat', array(0));
		$pcdescr = VikRequest::getString('cdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pshort_info = VikRequest::getString('short_info', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcplace = VikRequest::getVar('cplace', array(0));
		$pcretplace = VikRequest::getVar('cretplace', array(0));
		$pccarat = VikRequest::getVar('ccarat', array(0));
		$pcoptional = VikRequest::getVar('coptional', array(0));
		$pcavail = VikRequest::getString('cavail', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pautoresizemore = VikRequest::getString('autoresizemore', '', 'request');
		$presizetomore = VikRequest::getString('resizetomore', '', 'request');
		$punits = VikRequest::getInt('units', '', 'request');
		$pimages = VikRequest::getVar('cimgmore', null, 'files', 'array');
		$pstartfrom = VikRequest::getString('startfrom', '', 'request');
		$psdailycost = VikRequest::getString('sdailycost', '', 'request');
		$psdailycost = intval($psdailycost) == 1 ? 1 : 0;
		$pshourlycal = VikRequest::getString('shourlycal', '', 'request');
		$pshourlycal = intval($pshourlycal) == 1 ? 1 : 0;
		$preqinfo = VikRequest::getInt('reqinfo', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pcustptitle = VikRequest::getString('custptitle', '', 'request');
		$pcustptitlew = VikRequest::getString('custptitlew', '', 'request');
		$pcustptitlew = in_array($pcustptitlew, array('before', 'after', 'replace')) ? $pcustptitlew : 'before';
		$pmetakeywords = VikRequest::getString('metakeywords', '', 'request');
		$pmetadescription = VikRequest::getString('metadescription', '', 'request');
		$psefalias = VikRequest::getString('sefalias', '', 'request');
		$psefalias = empty($psefalias) ? JFilterOutput::stringURLSafe($pcname) : JFilterOutput::stringURLSafe($psefalias);
		jimport('joomla.filesystem.file');
		if (!empty($pcname)) {
			if (intval($_FILES['cimg']['error']) == 0 && VikRentCar::caniWrite(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR) && trim($_FILES['cimg']['name'])!="") {
				if (@is_uploaded_file($_FILES['cimg']['tmp_name'])) {
					$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['cimg']['name'])));
					if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
						$j=1;
						while (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
							$j++;
						}
						$pwhere=VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
					} else {
						$j="";
						$pwhere=VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
					}
					VikRentCar::uploadFile($_FILES['cimg']['tmp_name'], $pwhere);
					if (!($mainimginfo = getimagesize($pwhere))) {
						@unlink($pwhere);
						$picon="";
					} else {
						@chmod($pwhere, 0644);
						$picon=$j.$safename;
						if ($pautoresize=="1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon='r_'.$j.$safename;
							}
						}
						$thumbs_width = VikRentCar::getThumbnailsWidth();
						//VikRentCar 1.7 - Thumbnail for better CSS forcing result
						if ($mainimginfo[0] > $thumbs_width) {
							$eforj = new vikResizer();
							$eforj->proportionalImage(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$picon, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'vthumb_'.$picon, $thumbs_width, $thumbs_width);
						}
						//end VikRentCar 1.7 - Thumbnail for better CSS forcing result
					}
				} else {
					$picon="";
				}
			} else {
				$picon="";
			}
			//more images
			$creativik = new vikResizer();
			$bigsdest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
			$thumbsdest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
			$dest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
			$moreimagestr="";
			foreach ($pimages['name'] as $kk=>$ci) if (!empty($ci)) $arrimgs[]=$kk;
			if (is_array($arrimgs)) {
				foreach ($arrimgs as $imgk) {
					if (strlen(trim($pimages['name'][$imgk]))) {
						$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pimages['name'][$imgk])));
						$src = $pimages['tmp_name'][$imgk];
						$j="";
						if (file_exists($dest.$filename)) {
							$j=rand(171, 1717);
							while (file_exists($dest.$j.$filename)) {
								$j++;
							}
						}
						$finaldest=$dest.$j.$filename;
						$check=getimagesize($pimages['tmp_name'][$imgk]);
						if ($check[2] & imagetypes()) {
							if (VikRentCar::uploadFile($src, $finaldest)) {
								$gimg=$j.$filename;
								//orig img
								$origmod = true;
								if ($pautoresizemore == "1" && !empty($presizetomore)) {
									$origmod = $creativik->proportionalImage($finaldest, $bigsdest.'big_'.$j.$filename, $presizetomore, $presizetomore);
								} else {
									copy($finaldest, $bigsdest.'big_'.$j.$filename);
								}
								//thumb
								$thumb = $creativik->proportionalImage($finaldest, $thumbsdest.'thumb_'.$j.$filename, 500, 500);
								if (!$thumb || !$origmod) {
									if (file_exists($bigsdest.'big_'.$j.$filename)) @unlink($bigsdest.'big_'.$j.$filename);
									if (file_exists($thumbsdest.'thumb_'.$j.$filename)) @unlink($thumbsdest.'thumb_'.$j.$filename);
									VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
								} else {
									$moreimagestr.=$j.$filename.";;";
								}
								@unlink($finaldest);
							} else {
								VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
							}
						} else {
							VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
						}
					}
				}
			}
			//end more images
			if (!empty($pcplace) && @count($pcplace)) {
				$pcplacedef="";
				foreach ($pcplace as $cpla) {
					$pcplacedef.=$cpla.";";
				}
			} else {
				$pcplacedef="";
			}
			if (!empty($pcretplace) && @count($pcretplace)) {
				$pcretplacedef="";
				foreach ($pcretplace as $cpla) {
					$pcretplacedef.=$cpla.";";
				}
			} else {
				$pcretplacedef="";
			}
			if (!empty($pccat) && @count($pccat)) {
				foreach ($pccat as $ccat) {
					$pccatdef.=$ccat.";";
				}
			} else {
				$pccatdef="";
			}
			if (!empty($pccarat) && @count($pccarat)) {
				foreach ($pccarat as $ccarat) {
					$pccaratdef.=$ccarat.";";
				}
			} else {
				$pccaratdef="";
			}
			if (!empty($pcoptional) && @count($pcoptional)) {
				foreach ($pcoptional as $coptional) {
					$pcoptionaldef.=$coptional.";";
				}
			} else {
				$pcoptionaldef="";
			}
			$pcavaildef=($pcavail=="yes" ? "1" : "0");
			//params
			$car_params = array();
			$car_params['sdailycost'] = $psdailycost;
			$car_params['reqinfo'] = $preqinfo;
			$car_params['email'] = $pemail;
			$car_params['custptitle'] = $pcustptitle;
			$car_params['custptitlew'] = $pcustptitlew;
			$car_params['metakeywords'] = $pmetakeywords;
			$car_params['metadescription'] = $pmetadescription;
			$car_params['shourlycal'] = $pshourlycal;
			//distinctive features
			$car_params['features'] = array();
			if ($punits > 0) {
				for ($i=1; $i <= $punits; $i++) { 
					$distf_name = VikRequest::getVar('feature-name'.$i, array(0));
					$distf_lang = VikRequest::getVar('feature-lang'.$i, array(0));
					$distf_value = VikRequest::getVar('feature-value'.$i, array(0));
					foreach ($distf_name as $distf_k => $distf) {
						if (strlen($distf) > 0 && strlen($distf_value[$distf_k]) > 0) {
							$use_key = strlen($distf_lang[$distf_k]) > 0 ? $distf_lang[$distf_k] : $distf;
							$car_params['features'][$i][$use_key] = $distf_value[$distf_k];
						}
					}
				}
			}
			//
			$dbo = JFactory::getDBO();
			$q = "INSERT INTO `#__vikrentcar_cars` (`name`,`img`,`idcat`,`idcarat`,`idopt`,`info`,`idplace`,`avail`,`units`,`idretplace`,`moreimgs`,`startfrom`,`short_info`,`params`,`alias`) VALUES(".$dbo->quote($pcname).",".$dbo->quote($picon).",".$dbo->quote($pccatdef).",".$dbo->quote($pccaratdef).",".$dbo->quote($pcoptionaldef).",".$dbo->quote($pcdescr).",".$dbo->quote($pcplacedef).",".$dbo->quote($pcavaildef).",".($punits > 0 ? $dbo->quote($punits) : "'1'").",".$dbo->quote($pcretplacedef).", ".$dbo->quote($moreimagestr).", ".(strlen($pstartfrom) > 0 ? "'".$pstartfrom."'" : "null").", ".$dbo->quote($pshort_info).", ".$dbo->quote(json_encode($car_params)).", ".$dbo->quote($psefalias).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$lid = $dbo->insertid();
			if (!empty($lid)) {
				$mainframe->enqueueMessage(JText::_('VRCCARSAVEOK'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=tariffs&cid[]=".$lid);
			} else {
				$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
		}
	}

	function updatecar() {
		$this->do_updatecar();
	}

	function updatecarapply() {
		$this->do_updatecar(true);
	}

	private function do_updatecar($remain = false) {
		$mainframe = JFactory::getApplication();
		$pcname = VikRequest::getString('cname', '', 'request');
		$pccat = VikRequest::getVar('ccat', array(0));
		$pcdescr = VikRequest::getString('cdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pshort_info = VikRequest::getString('short_info', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcplace = VikRequest::getVar('cplace', array(0));
		$pcretplace = VikRequest::getVar('cretplace', array(0));
		$pccarat = VikRequest::getVar('ccarat', array(0));
		$pcoptional = VikRequest::getVar('coptional', array(0));
		$pcavail = VikRequest::getString('cavail', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pautoresizemore = VikRequest::getString('autoresizemore', '', 'request');
		$presizetomore = VikRequest::getString('resizetomore', '', 'request');
		$punits = VikRequest::getInt('units', '', 'request');
		$pimages = VikRequest::getVar('cimgmore', null, 'files', 'array');
		$pactmoreimgs = VikRequest::getString('actmoreimgs', '', 'request');
		$pstartfrom = VikRequest::getString('startfrom', '', 'request');
		$psdailycost = VikRequest::getString('sdailycost', '', 'request');
		$psdailycost = intval($psdailycost) == 1 ? 1 : 0;
		$pshourlycal = VikRequest::getString('shourlycal', '', 'request');
		$pshourlycal = intval($pshourlycal) == 1 ? 1 : 0;
		$preqinfo = VikRequest::getInt('reqinfo', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pcustptitle = VikRequest::getString('custptitle', '', 'request');
		$pcustptitlew = VikRequest::getString('custptitlew', '', 'request');
		$pcustptitlew = in_array($pcustptitlew, array('before', 'after', 'replace')) ? $pcustptitlew : 'before';
		$pmetakeywords = VikRequest::getString('metakeywords', '', 'request');
		$pmetadescription = VikRequest::getString('metadescription', '', 'request');
		$psefalias = VikRequest::getString('sefalias', '', 'request');
		$psefalias = empty($psefalias) ? JFilterOutput::stringURLSafe($pcname) : JFilterOutput::stringURLSafe($psefalias);
		jimport('joomla.filesystem.file');
		if (!empty($pcname)) {
			if (intval($_FILES['cimg']['error']) == 0 && VikRentCar::caniWrite(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR) && trim($_FILES['cimg']['name'])!="") {
				if (@is_uploaded_file($_FILES['cimg']['tmp_name'])) {
					$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['cimg']['name'])));
					if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
						$j=1;
						while (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
							$j++;
						}
						$pwhere=VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
					} else {
						$j="";
						$pwhere=VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
					}
					VikRentCar::uploadFile($_FILES['cimg']['tmp_name'], $pwhere);
					if (!($mainimginfo = getimagesize($pwhere))) {
						@unlink($pwhere);
						$picon="";
					} else {
						@chmod($pwhere, 0644);
						$picon=$j.$safename;
						if ($pautoresize=="1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon='r_'.$j.$safename;
							}
						}
						$thumbs_width = VikRentCar::getThumbnailsWidth();
						//VikRentCar 1.7 - Thumbnail for better CSS forcing result
						if ($mainimginfo[0] > $thumbs_width) {
							$eforj = new vikResizer();
							$eforj->proportionalImage(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$picon, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'vthumb_'.$picon, $thumbs_width, $thumbs_width);
						}
						//end VikRentCar 1.7 - Thumbnail for better CSS forcing result
					}
				} else {
					$picon="";
				}
			} else {
				$picon="";
			}
			//more images
			$creativik = new vikResizer();
			$bigsdest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
			$thumbsdest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
			$dest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
			$moreimagestr=$pactmoreimgs;
			foreach ($pimages['name'] as $kk=>$ci) if (!empty($ci)) $arrimgs[]=$kk;
			if (@count($arrimgs) > 0) {
				foreach ($arrimgs as $imgk) {
					if (strlen(trim($pimages['name'][$imgk]))) {
						$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pimages['name'][$imgk])));
						$src = $pimages['tmp_name'][$imgk];
						$j="";
						if (file_exists($dest.$filename)) {
							$j=rand(171, 1717);
							while (file_exists($dest.$j.$filename)) {
								$j++;
							}
						}
						$finaldest=$dest.$j.$filename;
						$check=getimagesize($pimages['tmp_name'][$imgk]);
						if ($check[2] & imagetypes()) {
							if (VikRentCar::uploadFile($src, $finaldest)) {
								$gimg=$j.$filename;
								//orig img
								$origmod = true;
								if ($pautoresizemore == "1" && !empty($presizetomore)) {
									$origmod = $creativik->proportionalImage($finaldest, $bigsdest.'big_'.$j.$filename, $presizetomore, $presizetomore);
								} else {
									copy($finaldest, $bigsdest.'big_'.$j.$filename);
								}
								//thumb
								$thumb = $creativik->proportionalImage($finaldest, $thumbsdest.'thumb_'.$j.$filename, 500, 500);
								if (!$thumb || !$origmod) {
									if (file_exists($bigsdest.'big_'.$j.$filename)) @unlink($bigsdest.'big_'.$j.$filename);
									if (file_exists($thumbsdest.'thumb_'.$j.$filename)) @unlink($thumbsdest.'thumb_'.$j.$filename);
									VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
								} else {
									$moreimagestr.=$j.$filename.";;";
								}
								@unlink($finaldest);
							} else {
								VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
							}
						} else {
							VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
						}
					}
				}
			}
			//end more images
			if (!empty($pcplace) && @count($pcplace)) {
				$pcplacedef="";
				foreach ($pcplace as $cpla) {
					$pcplacedef.=$cpla.";";
				}
			} else {
				$pcplacedef="";
			}
			if (!empty($pcretplace) && @count($pcretplace)) {
				$pcretplacedef="";
				foreach ($pcretplace as $cpla) {
					$pcretplacedef.=$cpla.";";
				}
			} else {
				$pcretplacedef="";
			}
			if (!empty($pccat) && @count($pccat)) {
				foreach ($pccat as $ccat) {
					$pccatdef.=$ccat.";";
				}
			} else {
				$pccatdef="";
			}
			if (!empty($pccarat) && @count($pccarat)) {
				foreach ($pccarat as $ccarat) {
					$pccaratdef.=$ccarat.";";
				}
			} else {
				$pccaratdef="";
			}
			if (!empty($pcoptional) && @count($pcoptional)) {
				foreach ($pcoptional as $coptional) {
					$pcoptionaldef.=$coptional.";";
				}
			} else {
				$pcoptionaldef="";
			}
			$pcavaildef=($pcavail=="yes" ? "1" : "0");
			//params
			$car_params = array();
			$car_params['sdailycost'] = $psdailycost;
			$car_params['reqinfo'] = $preqinfo;
			$car_params['email'] = $pemail;
			$car_params['custptitle'] = $pcustptitle;
			$car_params['custptitlew'] = $pcustptitlew;
			$car_params['metakeywords'] = $pmetakeywords;
			$car_params['metadescription'] = $pmetadescription;
			$car_params['shourlycal'] = $pshourlycal;
			//distinctive features
			$car_params['features'] = array();
			$damages = array();
			$damage_show_type = VikRentCar::getDamageShowType();
			$damage_png_path = VRC_ADMIN_PATH.DS.'resources'.DS.'damage_mark.png';
			$cstatus_png_path = VRC_SITE_PATH.DS.'helpers'.DS.'car_damages'.DS.'car_inspection.png';
			$damage_font = 'helsinki';
			$damage_font_size = 11;
			// Set the enviroment variable for PHP-GD
			//font residing in VRC_ADMIN_PATH/resources/ (arial.ttf by default)
			putenv('GDFONTPATH=' . realpath(VRC_ADMIN_PATH.DS.'resources'));
			//$font = 'dejavusans'; //i.e. for loading the file dejavusans.ttf or use a custom font
			//
			$gd_available = function_exists('imagecreatefrompng');
			if ($gd_available) {
				$damage_png = imagecreatefrompng($damage_png_path);
				imagesavealpha($damage_png, true);
				imagealphablending($damage_png, true);
				list($damage_png_width, $damage_png_height) = getimagesize($damage_png_path);
				list($cstatus_png_width, $cstatus_png_height) = getimagesize($cstatus_png_path);
			}
			if ($punits > 0) {
				for ($i=1; $i <= $punits; $i++) {
					$distf_name = VikRequest::getVar('feature-name'.$i, array(0));
					$distf_lang = VikRequest::getVar('feature-lang'.$i, array(0));
					$distf_value = VikRequest::getVar('feature-value'.$i, array(0));
					foreach ($distf_name as $distf_k => $distf) {
						if (strlen($distf) > 0 && strlen($distf_value[$distf_k]) > 0 && (!empty($distf) && !empty($distf_value[$distf_k]))) {
							$use_key = strlen($distf_lang[$distf_k]) > 0 ? $distf_lang[$distf_k] : $distf;
							$car_params['features'][$i][$use_key] = $distf_value[$distf_k];
						}
					}
					//damages
					$damage_notes = VikRequest::getVar('car-'.$i.'-damage', array(0));
					$damage_notes_x = VikRequest::getVar('car-'.$i.'-damage-x', array(0));
					$damage_notes_y = VikRequest::getVar('car-'.$i.'-damage-y', array(0));
					$dind = 1;
					foreach ($damage_notes as $dk => $damage) {
						if (strlen($damage) && strlen($damage_notes_x[$dk]) && strlen($damage_notes_y[$dk]) && (!empty($damage) && !empty($damage_notes_x[$dk]))) {
							$damages[$i][$dind]['notes'] = $damage;
							$damages[$i][$dind]['x'] = $damage_notes_x[$dk];
							$damages[$i][$dind]['y'] = $damage_notes_y[$dk];
							$dind++;
						}
					}
					$tot_dmg = count($damages[$i]);
					if ($tot_dmg > 0 && $gd_available) {
						//generate PNG
						$base_png = imagecreatefrompng($cstatus_png_path);
						imagesavealpha($base_png, true);
						imagealphablending($base_png, true);
						$unit_png = imagecreatetruecolor($cstatus_png_width, $cstatus_png_height);
						$white = imagecolorallocate($unit_png, 255, 255, 255);
						$black = imagecolorallocate($unit_png, 0, 0, 0);
						imagefill($unit_png, 0, 0, $black);
						imagecopy($unit_png, $base_png, 0, 0, 0, 0, $cstatus_png_width, $cstatus_png_height);
						$dk = $tot_dmg;
						foreach ($damages[$i] as $dind => $dmg_point) {
							//damage PNG
							$allocate_x = (int)((int)$dmg_point['x'] - ((int)$damage_png_width / 2));
							$allocate_y = (int)((int)$dmg_point['y'] - ((int)$damage_png_height / 2));
							imagecopy($unit_png, $damage_png, $allocate_x, $allocate_y, 0, 0, $damage_png_width, $damage_png_height);
							if ($damage_show_type > 1) {
								$type_space = imagettfbbox($damage_font_size, 0, $damage_font, (string)$dk);
								$type_width = floor($type_space[4] - $type_space[0]);
								$type_height = floor($type_space[5] - $type_space[1]);
								$allocate_x = ceil((int)$dmg_point['x'] - ((int)$type_width / 2));
								$allocate_y = floor((int)$dmg_point['y'] - ((int)$type_height / 2));
								imagettftext($unit_png, $damage_font_size, 0, $allocate_x, $allocate_y, $white, $damage_font, (string)$dk);
							}
							$dk--;
						}
						imagepng($unit_png, VRC_SITE_PATH.DS.'helpers'.DS.'car_damages'.DS.$pwhereup.'_'.$i.'.png');
						imagedestroy($unit_png);
					} else {
						if (file_exists(VRC_SITE_PATH.DS.'helpers'.DS.'car_damages'.DS.$pwhereup.'_'.$i.'.png')) {
							unlink(VRC_SITE_PATH.DS.'helpers'.DS.'car_damages'.DS.$pwhereup.'_'.$i.'.png');
						}
					}
				}
				if (count($damages) > 0) {
					$car_params['damages'] = $damages;
				}
			}
			//
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikrentcar_cars` SET `name`=".$dbo->quote($pcname).",".(strlen($picon) > 0 ? "`img`='".$picon."'," : "")."`idcat`=".$dbo->quote($pccatdef).",`idcarat`=".$dbo->quote($pccaratdef).",`idopt`=".$dbo->quote($pcoptionaldef).",`info`=".$dbo->quote($pcdescr).",`idplace`=".$dbo->quote($pcplacedef).",`avail`=".$dbo->quote($pcavaildef).",`units`=".($punits > 0 ? $dbo->quote($punits) : "'1'").",`idretplace`=".$dbo->quote($pcretplacedef).",`moreimgs`=".$dbo->quote($moreimagestr).",`startfrom`=".(strlen($pstartfrom) > 0 ? "'".$pstartfrom."'" : "null").",`short_info`=".$dbo->quote($pshort_info).",`params`=".$dbo->quote(json_encode($car_params)).",`alias`=".$dbo->quote($psefalias)." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VRCCARUPDATEOK'));
			if ($remain === true) {
				$mainframe->redirect("index.php?option=com_vikrentcar&task=editcar&cid[]=".$pwhereup);
				exit;
			}
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
	}

	function removecar() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_cars` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikrentcar_dispcost` WHERE `idcar`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
	}

	function modavail() {
		$cid = VikRequest::getVar('cid', array(0));
		$car = $cid[0];
		if (!empty($car)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `avail` FROM `#__vikrentcar_cars` WHERE `id`=".$dbo->quote($car).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$get = $dbo->loadAssocList();
			$q = "UPDATE `#__vikrentcar_cars` SET `avail`='".(intval($get[0]['avail'])==1 ? 0 : 1)."' WHERE `id`=".$dbo->quote($car).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
	}

	function tariffs() {
		VikRentCarHelper::printHeader("fares");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'tariffs'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function removetariffs() {
		$ids = VikRequest::getVar('cid', array(0));
		$pcarid = VikRequest::getString('carid', '', 'request');
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $r) {
				$x=explode(";", $r);
				foreach ($x as $rm) {
					if (!empty($rm)) {
						$q = "DELETE FROM `#__vikrentcar_dispcost` WHERE `id`=".$dbo->quote($rm).";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=tariffs&cid[]=".$pcarid);
	}

	function tariffshours() {
		VikRentCarHelper::printHeader("fares");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'tariffshours'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function removetariffshours() {
		$ids = VikRequest::getVar('cid', array(0));
		$pcarid = VikRequest::getString('carid', '', 'request');
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $r) {
				$x = explode(";", $r);
				foreach ($x as $rm) {
					if (!empty($rm)) {
						$q = "DELETE FROM `#__vikrentcar_dispcosthours` WHERE `id`=".$dbo->quote($rm).";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=tariffshours&cid[]=".$pcarid);
	}

	function hourscharges() {
		VikRentCarHelper::printHeader("fares");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'hourscharges'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function removehourscharges() {
		$ids = VikRequest::getVar('cid', array(0));
		$pcarid = VikRequest::getString('carid', '', 'request');
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $r) {
				$x=explode(";", $r);
				foreach ($x as $rm) {
					if (!empty($rm)) {
						$q = "DELETE FROM `#__vikrentcar_hourscharges` WHERE `id`=".$dbo->quote($rm).";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=hourscharges&cid[]=".$pcarid);
	}

	function cancel() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
	}

	function calendar() {
		VikRentCarHelper::printHeader("19");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'calendar'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function cancelcalendar() {
		$pidcar = VikRequest::getString('idcar', '', 'request');
		$preturn = VikRequest::getString('return', '', 'request');
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$mainframe = JFactory::getApplication();
		if ($preturn == 'order' && !empty($pidorder)) {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$pidorder);
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=calendar&cid[]=".$pidcar);
		}
	}

	function goconfig() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=config");
	}

	function config() {
		VikRentCarHelper::printHeader("11");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'config'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function saveconfig() {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$pallowrent = VikRequest::getString('allowrent', '', 'request');
		$pdisabledrentmsg = VikRequest::getString('disabledrentmsg', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptimeopenstorealw = VikRequest::getString('timeopenstorealw', '', 'request');
		$ptimeopenstorefh = VikRequest::getString('timeopenstorefh', '', 'request');
		$ptimeopenstorefm = VikRequest::getString('timeopenstorefm', '', 'request');
		$ptimeopenstoreth = VikRequest::getString('timeopenstoreth', '', 'request');
		$ptimeopenstoretm = VikRequest::getString('timeopenstoretm', '', 'request');
		$phoursmorerentback = VikRequest::getString('hoursmorerentback', '', 'request');
		$phoursmorecaravail = VikRequest::getString('hoursmorecaravail', '', 'request');
		$pplacesfront = VikRequest::getString('placesfront', '', 'request');
		$pdateformat = VikRequest::getString('dateformat', '', 'request');
		$ptimeformat = VikRequest::getString('timeformat', '', 'request');
		$pshowcategories = VikRequest::getString('showcategories', '', 'request');
		$pcharatsfilter = VikRequest::getString('charatsfilter', '', 'request');
		$pcharatsfilter = $pcharatsfilter == 'yes' ? 1 : 0;
		$pdamageshowtype = VikRequest::getInt('damageshowtype', '', 'request');
		$pdamageshowtype = $pdamageshowtype > 0 && $pdamageshowtype < 4 ? $pdamageshowtype : 1;
		$ptokenform = VikRequest::getString('tokenform', '', 'request');
		$padminemail = VikRequest::getString('adminemail', '', 'request');
		$psenderemail = VikRequest::getString('senderemail', '', 'request');
		$picalkey = VikRequest::getString('icalkey', '', 'request');
		$picalkey = str_replace(' ', '', $picalkey);
		$pminuteslock = VikRequest::getString('minuteslock', '', 'request');
		$pfooterordmail = VikRequest::getString('footerordmail', '', 'request', VIKREQUEST_ALLOWHTML);
		$prequirelogin = VikRequest::getString('requirelogin', '', 'request');
		$pusefa = VikRequest::getInt('usefa', '', 'request');
		$pusefa = $pusefa > 0 ? 1 : 0;
		$ploadjquery = VikRequest::getString('loadjquery', '', 'request');
		$ploadjquery = $ploadjquery == "yes" ? "1" : "0";
		$pcalendar = VikRequest::getString('calendar', '', 'request');
		$pcalendar = $pcalendar == "joomla" ? "joomla" : "jqueryui";
		$pehourschbasp = VikRequest::getString('ehourschbasp', '', 'request');
		$pehourschbasp = $pehourschbasp == "1" ? 1 : 0;
		$penablecoupons = VikRequest::getString('enablecoupons', '', 'request');
		$penablecoupons = $penablecoupons == "1" ? 1 : 0;
		$penablepin = VikRequest::getInt('enablepin', 0, 'request');
		$penablepin = $penablepin > 0 ? 1 : 0;
		$ptodaybookings = VikRequest::getString('todaybookings', '', 'request');
		$ptodaybookings = $ptodaybookings == "1" ? 1 : 0;
		$psetdropdplus = VikRequest::getString('setdropdplus', '', 'request');
		$psetdropdplus = !empty($psetdropdplus) ? intval($psetdropdplus) : '';
		$pmindaysadvance = VikRequest::getInt('mindaysadvance', '', 'request');
		$pmindaysadvance = $pmindaysadvance < 0 ? 0 : $pmindaysadvance;
		$pmaxdate = VikRequest::getString('maxdate', '', 'request');
		$pmaxdate = intval($pmaxdate) < 1 ? 2 : $pmaxdate;
		$pmaxdateinterval = VikRequest::getString('maxdateinterval', '', 'request');
		$pmaxdateinterval = !in_array($pmaxdateinterval, array('d', 'w', 'm', 'y')) ? 'y' : $pmaxdateinterval;
		$maxdate_str = '+'.$pmaxdate.$pmaxdateinterval;
		$pvrcsef = VikRequest::getInt('vrcsef', '', 'request');
		$vrcsef = file_exists(VRC_SITE_PATH.DS.'router.php');
		if ($pvrcsef === 1) {
			if (!$vrcsef) {
				rename(VRC_SITE_PATH.DS.'_router.php', VRC_SITE_PATH.DS.'router.php');
			}
		} else {
			if ($vrcsef) {
				rename(VRC_SITE_PATH.DS.'router.php', VRC_SITE_PATH.DS.'_router.php');
			}
		}
		$pmultilang = VikRequest::getString('multilang', '', 'request');
		$pmultilang = $pmultilang == "1" ? 1 : 0;
		$picon="";
		if (intval($_FILES['sitelogo']['error']) == 0 && trim($_FILES['sitelogo']['name'])!="") {
			jimport('joomla.filesystem.file');
			if (@is_uploaded_file($_FILES['sitelogo']['tmp_name'])) {
				$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['sitelogo']['name'])));
				if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
					$j=1;
					while (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
						$j++;
					}
					$pwhere=VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
				} else {
					$j="";
					$pwhere=VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
				}
				VikRentCar::uploadFile($_FILES['sitelogo']['tmp_name'], $pwhere);
				if (!getimagesize($pwhere)) {
					@unlink($pwhere);
					$picon="";
				} else {
					@chmod($pwhere, 0644);
					$picon=$j.$safename;
				}
			}
			if (!empty($picon)) {
				$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($picon)." WHERE `param`='sitelogo';";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		if (empty($pallowrent) || $pallowrent!="1") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='allowrent';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='allowrent';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pplacesfront) || $pplacesfront!="yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='placesfront';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='placesfront';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pshowcategories) || $pshowcategories!="yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='showcategories';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='showcategories';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$pcharatsfilter." WHERE `param`='charatsfilter';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$pdamageshowtype." WHERE `param`='damageshowtype';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($ptokenform) || $ptokenform!="yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='tokenform';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='tokenform';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($pfooterordmail)." WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($pdisabledrentmsg)." WHERE `param`='disabledrentmsg';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($padminemail)." WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		//Sender email address
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='senderemail' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($psenderemail)." WHERE `param`='senderemail';";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$q = "INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('senderemail',".$dbo->quote($psenderemail).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($picalkey)." WHERE `param`='icalkey';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$pmultilang."' WHERE `param`='multilang';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pdateformat)) {
			$pdateformat="%d/%m/%Y";
		}
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pdateformat)." WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$session->set('getDateFormat', '');
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($ptimeformat)." WHERE `param`='timeformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pminuteslock)." WHERE `param`='minuteslock';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!empty($ptimeopenstorealw)) {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='' WHERE `param`='timeopenstore';";
		} else {
			$openingh=$ptimeopenstorefh * 3600;
			$openingm=$ptimeopenstorefm * 60;
			$openingts=$openingh + $openingm;
			$closingh=$ptimeopenstoreth * 3600;
			$closingm=$ptimeopenstoretm * 60;
			$closingts=$closingh + $closingm;
			if ($closingts <= $openingts) {
				$q = "UPDATE `#__vikrentcar_config` SET `setting`='' WHERE `param`='timeopenstore';";
			} else {
				$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$openingts."-".$closingts."' WHERE `param`='timeopenstore';";
			}
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (!ctype_digit($phoursmorerentback)) {
			$phoursmorerentback="0";
		}
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$phoursmorerentback."' WHERE `param`='hoursmorerentback';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!ctype_digit($phoursmorecaravail)) {
			$phoursmorecaravail="0";
		}
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$phoursmorecaravail."' WHERE `param`='hoursmorecaravail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".($prequirelogin == "1" ? "1" : "0")."' WHERE `param`='requirelogin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".(string)$pusefa."' WHERE `param`='usefa';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$ploadjquery."' WHERE `param`='loadjquery';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$pcalendar."' WHERE `param`='calendar';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$pehourschbasp."' WHERE `param`='ehourschbasp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$penablecoupons."' WHERE `param`='enablecoupons';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$penablepin."' WHERE `param`='enablepin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$ptodaybookings."' WHERE `param`='todaybookings';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$psetdropdplus."' WHERE `param`='setdropdplus';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$pmindaysadvance."' WHERE `param`='mindaysadvance';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$maxdate_str."' WHERE `param`='maxdate';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$pfronttitle = VikRequest::getString('fronttitle', '', 'request');
		$pfronttitletag = VikRequest::getString('fronttitletag', '', 'request');
		$pfronttitletagclass = VikRequest::getString('fronttitletagclass', '', 'request');
		$psearchbtnval = VikRequest::getString('searchbtnval', '', 'request');
		$psearchbtnclass = VikRequest::getString('searchbtnclass', '', 'request');
		$pshowfooter = VikRequest::getString('showfooter', '', 'request');
		$pintromain = VikRequest::getString('intromain', '', 'request', VIKREQUEST_ALLOWHTML);
		$pclosingmain = VikRequest::getString('closingmain', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencyname = VikRequest::getString('currencyname', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencysymb = VikRequest::getString('currencysymb', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencycodepp = VikRequest::getString('currencycodepp', '', 'request');
		$pnumdecimals = VikRequest::getString('numdecimals', '', 'request');
		$pnumdecimals = intval($pnumdecimals);
		$pdecseparator = VikRequest::getString('decseparator', '', 'request');
		$pdecseparator = empty($pdecseparator) ? '.' : $pdecseparator;
		$pthoseparator = VikRequest::getString('thoseparator', '', 'request');
		$numberformatstr = $pnumdecimals.':'.$pdecseparator.':'.$pthoseparator;
		$pshowpartlyreserved = VikRequest::getString('showpartlyreserved', '', 'request');
		$pshowpartlyreserved = $pshowpartlyreserved == "yes" ? 1 : 0;
		$pnumcalendars = VikRequest::getInt('numcalendars', '', 'request');
		$pnumcalendars = $pnumcalendars > -1 ? $pnumcalendars : 3;
		$pthumbswidth = VikRequest::getInt('thumbswidth', '', 'request');
		$pthumbswidth = $pthumbswidth > 0 ? $pthumbswidth : 200;
		$pfirstwday = VikRequest::getString('firstwday', '', 'request');
		$pfirstwday = intval($pfirstwday) >= 0 && intval($pfirstwday) <= 6 ? $pfirstwday : '0';
		//Google Maps API Key
		$pgmapskey = VikRequest::getString('gmapskey', '', 'request');
		$q = "SELECT * FROM `#__vikrentcar_config` WHERE `param`='gmapskey';";
		$dbo->setQuery($q);
		$dbo->Query($q);
		if ($dbo->getNumRows() > 0) {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pgmapskey)." WHERE `param`='gmapskey';";
			$dbo->setQuery($q);
			$dbo->Query($q);
		} else {
			$q = "INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('gmapskey', ".$dbo->quote($pgmapskey).");";
			$dbo->setQuery($q);
			$dbo->Query($q);
		}
		//theme
		$ptheme = VikRequest::getString('theme', '', 'request');
		if (empty($ptheme) || $ptheme == 'default') {
			$ptheme = 'default';
		} else {
			$validtheme = false;
			$themes = glob(VRC_SITE_PATH.DS.'themes'.DS.'*');
			if (count($themes) > 0) {
				$strip = VRC_SITE_PATH.DS.'themes'.DS;
				foreach ($themes as $th) {
					if (is_dir($th)) {
						$tname = str_replace($strip, '', $th);
						if ($tname == $ptheme) {
							$validtheme = true;
							break;
						}
					}
				}
			}
			if ($validtheme == false) {
				$ptheme = 'default';
			}
		}
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($ptheme)." WHERE `param`='theme';";
		$dbo->setQuery($q);
		$dbo->execute();
		//
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pshowpartlyreserved)." WHERE `param`='showpartlyreserved';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pnumcalendars)." WHERE `param`='numcalendars';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pthumbswidth)." WHERE `param`='thumbswidth';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pfirstwday)." WHERE `param`='firstwday';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($pfronttitle)." WHERE `param`='fronttitle';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pfronttitletag)." WHERE `param`='fronttitletag';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pfronttitletagclass)." WHERE `param`='fronttitletagclass';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($psearchbtnval)." WHERE `param`='searchbtnval';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($psearchbtnclass)." WHERE `param`='searchbtnclass';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pshowfooter) || $pshowfooter!="yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='showfooter';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='showfooter';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($pintromain)." WHERE `param`='intromain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($pclosingmain)." WHERE `param`='closingmain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pcurrencyname)." WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pcurrencysymb)." WHERE `param`='currencysymb';";
		$dbo->setQuery($q);
		$dbo->execute();
		$session->set('getCurrencySymb', '');
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pcurrencycodepp)." WHERE `param`='currencycodepp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($numberformatstr)." WHERE `param`='numberformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$pivainclusa = VikRequest::getString('ivainclusa', '', 'request');
		$ptaxsummary = VikRequest::getString('taxsummary', '', 'request');
		$ptaxsummary = empty($ptaxsummary) || $ptaxsummary != "yes" ? "0" : "1";
		$pccpaypal = VikRequest::getString('ccpaypal', '', 'request');
		$ppaytotal = VikRequest::getString('paytotal', '', 'request');
		$ppayaccpercent = VikRequest::getString('payaccpercent', '', 'request');
		$ptypedeposit = VikRequest::getString('typedeposit', '', 'request');
		$ptypedeposit = $ptypedeposit == 'fixed' ? 'fixed' : 'pcent';
		$ppaymentname = VikRequest::getString('paymentname', '', 'request');
		if (empty($pivainclusa) || $pivainclusa!="yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='ivainclusa';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='ivainclusa';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$ptaxsummary."' WHERE `param`='taxsummary';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($ppaytotal) || $ppaytotal!="yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='paytotal';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='paytotal';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pccpaypal)." WHERE `param`='ccpaypal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($ppaymentname)." WHERE `param`='paymentname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($ppayaccpercent)." WHERE `param`='payaccpercent';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($ptypedeposit)." WHERE `param`='typedeposit';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$psendpdf = VikRequest::getString('sendpdf', '', 'request');
		$pallowstats = VikRequest::getString('allowstats', '', 'request');
		$psendmailstats = VikRequest::getString('sendmailstats', '', 'request');
		$pdisclaimer = VikRequest::getString('disclaimer', '', 'request', VIKREQUEST_ALLOWHTML);
		//Deprecated and Removed since VRC 1.11
		/*
		$poldorders = VikRequest::getString('oldorders', '', 'request');
		if (empty($poldorders) || $poldorders!="yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='oldorders';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='oldorders';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		*/
		//
		
		/**
		 * @deprecated 	1.12 - configuration setting no longer used 
		 * 
		if (empty($psendjutility) || $psendjutility!="yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='sendjutility';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='sendjutility';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		*/

		if (empty($psendpdf) || $psendpdf!="yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='sendpdf';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='sendpdf';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pallowstats) || $pallowstats!="yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='allowstats';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='allowstats';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($psendmailstats) || $psendmailstats!="yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='sendmailstats';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='sendmailstats';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$psendemailwhen = VikRequest::getInt('sendemailwhen', '', 'request');
		$psendemailwhen = $psendemailwhen > 1 ? 2 : 1;
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($psendemailwhen)." WHERE `param`='emailsendwhen';";
		$dbo->setQuery($q);
		$dbo->execute();

		$picalendtype = VikRequest::getString('icalendtype', '', 'request');
		$picalendtype = $picalendtype == 'pick' ? 'pick' : 'drop';
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($picalendtype)." WHERE `param`='icalendtype';";
		$dbo->setQuery($q);
		$dbo->execute();

		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($pdisclaimer)." WHERE `param`='disclaimer';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$mainframe = JFactory::getApplication();
		$mainframe->enqueueMessage(JText::_('VRSETTINGSAVED'));
		$mainframe->redirect("index.php?option=com_vikrentcar&task=config");
	}

	function renewsession() {
		$dbo = JFactory::getDBO();
		$q = "TRUNCATE TABLE `#__session`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=config");
	}

	function locfees() {
		VikRentCarHelper::printHeader("12");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'locfees'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newlocfee() {
		VikRentCarHelper::printHeader("12");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managelocfee'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editlocfee() {
		VikRentCarHelper::printHeader("12");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managelocfee'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function createlocfee() {
		$mainframe = JFactory::getApplication();
		$pfrom = VikRequest::getInt('from', '', 'request');
		$pto = VikRequest::getInt('to', '', 'request');
		$pcost = VikRequest::getString('cost', '', 'request');
		$pdaily = VikRequest::getString('daily', '', 'request');
		$paliq = VikRequest::getInt('aliq', '', 'request');
		$pinvert = VikRequest::getString('invert', '', 'request');
		$pinvert = $pinvert == "1" ? 1 : 0;
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
		$dbo = JFactory::getDBO();
		if (!empty($pfrom) && !empty($pto)) {
			$losverridestr = "";
			if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
				foreach ($pnightsoverrides as $ko => $no) {
					if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
						$losverridestr .= $no.':'.trim($pvaluesoverrides[$ko]).'_';
					}
				}
			}
			$q = "INSERT INTO `#__vikrentcar_locfees` (`from`,`to`,`daily`,`cost`,`idiva`,`invert`,`losoverride`) VALUES(".$dbo->quote($pfrom).", ".$dbo->quote($pto).", ".(intval($pdaily) == 1 ? "1" : "0").", ".$dbo->quote($pcost).", ".$dbo->quote($paliq).", '".$pinvert."', '".$losverridestr."');";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VRLOCFEESAVED'));
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=locfees");
	}

	function updatelocfee() {
		$mainframe = JFactory::getApplication();
		$pwhere = VikRequest::getString('where', '', 'request');
		$pfrom = VikRequest::getInt('from', '', 'request');
		$pto = VikRequest::getInt('to', '', 'request');
		$pcost = VikRequest::getString('cost', '', 'request');
		$pdaily = VikRequest::getString('daily', '', 'request');
		$paliq = VikRequest::getInt('aliq', '', 'request');
		$pinvert = VikRequest::getString('invert', '', 'request');
		$pinvert = $pinvert == "1" ? 1 : 0;
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
		$dbo = JFactory::getDBO();
		if (!empty($pwhere) && !empty($pfrom) && !empty($pto)) {
			$losverridestr = "";
			if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
				foreach ($pnightsoverrides as $ko => $no) {
					if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
						$losverridestr .= $no.':'.trim($pvaluesoverrides[$ko]).'_';
					}
				}
			}
			$q = "UPDATE `#__vikrentcar_locfees` SET `from`=".$dbo->quote($pfrom).",`to`=".$dbo->quote($pto).",`daily`=".(intval($pdaily) == 1 ? "1" : "0").",`cost`=".$dbo->quote($pcost).",`idiva`=".$dbo->quote($paliq).",`invert`='".$pinvert."',`losoverride`='".$losverridestr."' WHERE `id`=".$dbo->quote($pwhere).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VRLOCFEEUPDATE'));
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=locfees");
	}

	function removelocfee() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_locfees` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=locfees");
	}

	function cancellocfee() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=locfees");
	}

	function seasons() {
		VikRentCarHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'seasons'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newseason() {
		VikRentCarHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageseason'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editseason() {
		VikRentCarHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageseason'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function createseason() {
		$mainframe = JFactory::getApplication();
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$pdiffcost = VikRequest::getString('diffcost', '', 'request');
		$pidlocation = VikRequest::getInt('idlocation', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array(0));
		$pidprices = VikRequest::getVar('idprices', array(0));
		$pwdays = VikRequest::getVar('wdays', array());
		$pspname = VikRequest::getString('spname', '', 'request');
		$ppickupincl = VikRequest::getString('pickupincl', '', 'request');
		$ppickupincl = $ppickupincl == 1 ? 1 : 0;
		$pkeepfirstdayrate = VikRequest::getString('keepfirstdayrate', '', 'request');
		$pkeepfirstdayrate = $pkeepfirstdayrate == 1 ? 1 : 0;
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = $pval_pcent == "1" ? 1 : 2;
		$proundmode = VikRequest::getString('roundmode', '', 'request');
		$proundmode = (!empty($proundmode) && in_array($proundmode, array('PHP_ROUND_HALF_UP', 'PHP_ROUND_HALF_DOWN')) ? $proundmode : '');
		$pyeartied = VikRequest::getString('yeartied', '', 'request');
		$pyeartied = $pyeartied == "1" ? 1 : 0;
		$tieyear = 0;
		$ppromo = VikRequest::getInt('promo', '', 'request');
		$ppromodaysadv = VikRequest::getInt('promodaysadv', '', 'request');
		$ppromotxt = VikRequest::getString('promotxt', '', 'request', VIKREQUEST_ALLOWHTML);
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
		$pandmoreoverride = VikRequest::getVar('andmoreoverride', array());
		$dbo = JFactory::getDBO();
		if ((!empty($pfrom) && !empty($pto)) || count($pwdays) > 0) {
			$skipseason = false;
			if (empty($pfrom) || empty($pto)) {
				$skipseason = true;
			}
			$skipdays = false;
			$wdaystr = null;
			if (count($pwdays) == 0) {
				$skipdays = true;
			} else {
				$wdaystr = "";
				foreach ($pwdays as $wd) {
					$wdaystr .= $wd.';';
				}
			}
			$carstr="";
			if (@count($pidcars) > 0) {
				foreach ($pidcars as $car) {
					$carstr.="-".$car."-,";
				}
			}
			$pricestr="";
			if (@count($pidprices) > 0) {
				foreach ($pidprices as $price) {
					if (empty($price)) {
						continue;
					}
					$pricestr.="-".$price."-,";
				}
			}
			$valid = true;
			$sfrom = null;
			$sto = null;
			if (!$skipseason) {
				$first = VikRentCar::getDateTimestamp($pfrom, 0, 0);
				$second = VikRentCar::getDateTimestamp($pto, 0, 0);
				if ($second > 0 && $second == $first) {
					$second += 86399;
				}
				if ($second > $first) {
					$baseone = getdate($first);
					$basets = mktime(0, 0, 0, 1, 1, $baseone['year']);
					$sfrom = $baseone[0] - $basets;
					$basetwo = getdate($second);
					$basets = mktime(0, 0, 0, 1, 1, $basetwo['year']);
					$sto = $basetwo[0] - $basets;
					//check leap year
					if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
						$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
						if ($baseone[0] >= $leapts) {
							$sfrom -= 86400;
							$sto -= 86400;
						}
					}
					//end leap year
					//tied to the year
					if ($pyeartied == 1) {
						$tieyear = $baseone['year'];
					}
					//
					//check if seasons dates are valid
					$q = "SELECT `id` FROM `#__vikrentcar_seasons` WHERE `from`<=".$dbo->quote($sfrom)." AND `to`>=".$dbo->quote($sfrom)." AND `idcars`=".$dbo->quote($carstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totfirst = $dbo->getNumRows();
					if ($totfirst > 0) {
						$valid=false;
					}
					$q = "SELECT `id` FROM `#__vikrentcar_seasons` WHERE `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sto)." AND `idcars`=".$dbo->quote($carstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totsecond=@$dbo->getNumRows();
					if ($totsecond > 0) {
						$valid=false;
					}
					$q = "SELECT `id` FROM `#__vikrentcar_seasons` WHERE `from`>=".$dbo->quote($sfrom)." AND `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sfrom)." AND `to`<=".$dbo->quote($sto)." AND `idcars`=".$dbo->quote($carstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totthird = $dbo->getNumRows();
					if ($totthird > 0) {
						$valid=false;
					}
					//
				} else {
					VikError::raiseWarning('', JText::_('ERRINVDATESEASON'));
					$mainframe->redirect("index.php?option=com_vikrentcar&task=newseason");
				}
			}
			if ($valid) {
				$losverridestr = "";
				if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
					foreach ($pnightsoverrides as $ko => $no) {
						if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
							$infiniteclause = intval($pandmoreoverride[$ko]) == 1 ? '-i' : '';
							$losverridestr .= intval($no).$infiniteclause.':'.trim($pvaluesoverrides[$ko]).'_';
						}
					}
				}
				$q = "INSERT INTO `#__vikrentcar_seasons` (`type`,`from`,`to`,`diffcost`,`idcars`,`locations`,`spname`,`wdays`,`pickupincl`,`val_pcent`,`losoverride`,`keepfirstdayrate`,`roundmode`,`year`,`idprices`,`promo`,`promodaysadv`,`promotxt`) VALUES('".($ptype == "1" ? "1" : "2")."', ".$dbo->quote($sfrom).", ".$dbo->quote($sto).", ".$dbo->quote($pdiffcost).", ".$dbo->quote($carstr).", ".$dbo->quote($pidlocation).", ".$dbo->quote($pspname).", ".$dbo->quote($wdaystr).", '".$ppickupincl."', '".$pval_pcent."', ".$dbo->quote($losverridestr).", '".$pkeepfirstdayrate."', ".(!empty($proundmode) ? "'".$proundmode."'" : "null").", ".($pyeartied == 1 ? $tieyear : "NULL").", ".$dbo->quote($pricestr).", ".($ppromo == 1 ? '1' : '0').", ".(!empty($ppromodaysadv) ? $ppromodaysadv : "null").", ".$dbo->quote($ppromotxt).");";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VRSEASONSAVED'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=seasons");
			} else {
				VikError::raiseWarning('', JText::_('ERRINVDATECARSLOCSEASON'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=newseason");
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=newseason");
		}
	}

	function updateseason() {
		$mainframe = JFactory::getApplication();
		$pwhere = VikRequest::getString('where', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$pdiffcost = VikRequest::getString('diffcost', '', 'request');
		$pidlocation = VikRequest::getInt('idlocation', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array(0));
		$pidprices = VikRequest::getVar('idprices', array(0));
		$pwdays = VikRequest::getVar('wdays', array());
		$pspname = VikRequest::getString('spname', '', 'request');
		$ppickupincl = VikRequest::getString('pickupincl', '', 'request');
		$ppickupincl = $ppickupincl == 1 ? 1 : 0;
		$pkeepfirstdayrate = VikRequest::getString('keepfirstdayrate', '', 'request');
		$pkeepfirstdayrate = $pkeepfirstdayrate == 1 ? 1 : 0;
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = $pval_pcent == "1" ? 1 : 2;
		$proundmode = VikRequest::getString('roundmode', '', 'request');
		$proundmode = (!empty($proundmode) && in_array($proundmode, array('PHP_ROUND_HALF_UP', 'PHP_ROUND_HALF_DOWN')) ? $proundmode : '');
		$pyeartied = VikRequest::getString('yeartied', '', 'request');
		$pyeartied = $pyeartied == "1" ? 1 : 0;
		$tieyear = 0;
		$ppromo = VikRequest::getInt('promo', '', 'request');
		$ppromo = $ppromo == 1 ? 1 : 0;
		$ppromodaysadv = VikRequest::getInt('promodaysadv', '', 'request');
		$ppromotxt = VikRequest::getString('promotxt', '', 'request', VIKREQUEST_ALLOWHTML);
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
		$pandmoreoverride = VikRequest::getVar('andmoreoverride', array());
		$dbo = JFactory::getDBO();
		if ((!empty($pfrom) && !empty($pto)) || count($pwdays) > 0) {
			$skipseason = false;
			if (empty($pfrom) || empty($pto)) {
				$skipseason = true;
			}
			$skipdays = false;
			$wdaystr = null;
			if (count($pwdays) == 0) {
				$skipdays = true;
			} else {
				$wdaystr = "";
				foreach ($pwdays as $wd) {
					$wdaystr .= $wd.';';
				}
			}
			$carstr="";
			if (@count($pidcars) > 0) {
				foreach ($pidcars as $car) {
					$carstr.="-".$car."-,";
				}
			}
			$pricestr="";
			if (@count($pidprices) > 0) {
				foreach ($pidprices as $price) {
					if (empty($price)) {
						continue;
					}
					$pricestr.="-".$price."-,";
				}
			}
			$valid = true;
			$sfrom = null;
			$sto = null;
			if (!$skipseason) {
				$first = VikRentCar::getDateTimestamp($pfrom, 0, 0);
				$second = VikRentCar::getDateTimestamp($pto, 0, 0);
				if ($second > 0 && $second == $first) {
					$second += 86399;
				}
				if ($second > $first) {
					$baseone = getdate($first);
					$basets = mktime(0, 0, 0, 1, 1, $baseone['year']);
					$sfrom = $baseone[0] - $basets;
					$basetwo = getdate($second);
					$basets = mktime(0, 0, 0, 1, 1, $basetwo['year']);
					$sto = $basetwo[0] - $basets;
					//check leap year
					if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
						$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
						if ($baseone[0] >= $leapts) {
							$sfrom -= 86400;
							$sto -= 86400;
						}
					}
					//end leap year
					//tied to the year
					if ($pyeartied == 1) {
						$tieyear = $baseone['year'];
					}
					//
					//check if seasons dates are valid
					$q = "SELECT `id` FROM `#__vikrentcar_seasons` WHERE `from`<=".$dbo->quote($sfrom)." AND `to`>=".$dbo->quote($sfrom)." AND `id`!=".$dbo->quote($pwhere)." AND `idcars`=".$dbo->quote($carstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totfirst=@$dbo->getNumRows();
					if ($totfirst > 0) {
						$valid=false;
					}
					$q = "SELECT `id` FROM `#__vikrentcar_seasons` WHERE `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sto)." AND `id`!=".$dbo->quote($pwhere)." AND `idcars`=".$dbo->quote($carstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totsecond=@$dbo->getNumRows();
					if ($totsecond > 0) {
						$valid=false;
					}
					$q = "SELECT `id` FROM `#__vikrentcar_seasons` WHERE `from`>=".$dbo->quote($sfrom)." AND `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sfrom)." AND `to`<=".$dbo->quote($sto)." AND `id`!=".$dbo->quote($pwhere)." AND `idcars`=".$dbo->quote($carstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totthird=@$dbo->getNumRows();
					if ($totthird > 0) {
						$valid=false;
					}
					//
				} else {
					VikError::raiseWarning('', JText::_('ERRINVDATESEASON'));
					$mainframe->redirect("index.php?option=com_vikrentcar&task=editseason&cid[]=".$pwhere);
				}
			}
			if ($valid) {
				$losverridestr = "";
				if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
					foreach ($pnightsoverrides as $ko => $no) {
						if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
							$infiniteclause = intval($pandmoreoverride[$ko]) == 1 ? '-i' : '';
							$losverridestr .= intval($no).$infiniteclause.':'.trim($pvaluesoverrides[$ko]).'_';
						}
					}
				}
				$q = "UPDATE `#__vikrentcar_seasons` SET `type`='".($ptype == "1" ? "1" : "2")."',`from`=".$dbo->quote($sfrom).",`to`=".$dbo->quote($sto).",`diffcost`=".$dbo->quote($pdiffcost).",`idcars`=".$dbo->quote($carstr).",`locations`=".$dbo->quote($pidlocation).",`spname`=".$dbo->quote($pspname).",`wdays`='".$wdaystr."',`pickupincl`='".$ppickupincl."',`val_pcent`='".$pval_pcent."',`losoverride`=".$dbo->quote($losverridestr).",`keepfirstdayrate`='".$pkeepfirstdayrate."',`roundmode`=".(!empty($proundmode) ? "'".$proundmode."'" : "null").",`year`=".($pyeartied == 1 ? $tieyear : "NULL").",`idprices`=".$dbo->quote($pricestr).",`promo`=".$ppromo.",`promodaysadv`=".(!empty($ppromodaysadv) ? $ppromodaysadv : "null").",`promotxt`=".$dbo->quote($ppromotxt)." WHERE `id`=".$dbo->quote($pwhere).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VRSEASONUPDATED'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=seasons");
			} else {
				VikError::raiseWarning('', JText::_('ERRINVDATECARSLOCSEASON'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=editseason&cid[]=".$pwhere);
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editseason&cid[]=".$pwhere);
		}
	}

	function removeseasons() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_seasons` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=seasons");
	}

	function cancelseason() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=seasons");
	}

	function payments() {
		VikRentCarHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'payments'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newpayment() {
		VikRentCarHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managepayment'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editpayment() {
		VikRentCarHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managepayment'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function createpayment() {
		$mainframe = JFactory::getApplication();
		$pname = VikRequest::getString('name', '', 'request');
		$ppayment = VikRequest::getString('payment', '', 'request');
		$ppublished = VikRequest::getString('published', '', 'request');
		$pcharge = VikRequest::getString('charge', '', 'request');
		$psetconfirmed = VikRequest::getString('setconfirmed', '', 'request');
		$pshownotealw = VikRequest::getString('shownotealw', '', 'request');
		$pnote = VikRequest::getString('note', '', 'request', VIKREQUEST_ALLOWHTML);
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = !in_array($pval_pcent, array('1', '2')) ? 1 : $pval_pcent;
		$pch_disc = VikRequest::getString('ch_disc', '', 'request');
		$pch_disc = !in_array($pch_disc, array('1', '2')) ? 1 : $pch_disc;
		$vikpaymentparams = VikRequest::getVar('vikpaymentparams', array(0));
		$payparamarr = array();
		$payparamstr = '';
		if (count($vikpaymentparams) > 0) {
			foreach ($vikpaymentparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$payparamarr[$setting] = $cont;
				}
			}
			if (count($payparamarr) > 0) {
				$payparamstr = json_encode($payparamarr);
			}
		}
		$dbo = JFactory::getDBO();
		if (!empty($pname) && !empty($ppayment)) {
			$setpub=$ppublished=="1" ? 1 : 0;
			$psetconfirmed=$psetconfirmed=="1" ? 1 : 0;
			$pshownotealw=$pshownotealw=="1" ? 1 : 0;
			$q = "SELECT `id` FROM `#__vikrentcar_gpayments` WHERE `file`=".$dbo->quote($ppayment).";";
			$dbo->setQuery($q);
			$dbo->execute();
			//VikRentCar 1.8 : no longer block payment methods that are using the same PHP file
			if ($dbo->getNumRows() >= 0) {
				$q = "INSERT INTO `#__vikrentcar_gpayments` (`name`,`file`,`published`,`note`,`charge`,`setconfirmed`,`shownotealw`,`val_pcent`,`ch_disc`,`params`) VALUES(".$dbo->quote($pname).",".$dbo->quote($ppayment).",".$dbo->quote($setpub).",".$dbo->quote($pnote).",".$dbo->quote($pcharge).",".$dbo->quote($psetconfirmed).",".$dbo->quote($pshownotealw).",'".$pval_pcent."','".$pch_disc."',".$dbo->quote($payparamstr).");";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VRPAYMENTSAVED'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=payments");
			} else {
				VikError::raiseWarning('', JText::_('ERRINVFILEPAYMENT'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=newpayment");
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=newpayment");
		}
	}

	function updatepayment() {
		$mainframe = JFactory::getApplication();
		$pwhere = VikRequest::getString('where', '', 'request');
		$pname = VikRequest::getString('name', '', 'request');
		$ppayment = VikRequest::getString('payment', '', 'request');
		$ppublished = VikRequest::getString('published', '', 'request');
		$pcharge = VikRequest::getString('charge', '', 'request');
		$psetconfirmed = VikRequest::getString('setconfirmed', '', 'request');
		$pshownotealw = VikRequest::getString('shownotealw', '', 'request');
		$pnote = VikRequest::getString('note', '', 'request', VIKREQUEST_ALLOWHTML);
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = !in_array($pval_pcent, array('1', '2')) ? 1 : $pval_pcent;
		$pch_disc = VikRequest::getString('ch_disc', '', 'request');
		$pch_disc = !in_array($pch_disc, array('1', '2')) ? 1 : $pch_disc;
		$vikpaymentparams = VikRequest::getVar('vikpaymentparams', array(0));
		$payparamarr = array();
		$payparamstr = '';
		if (count($vikpaymentparams) > 0) {
			foreach ($vikpaymentparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$payparamarr[$setting] = $cont;
				}
			}
			if (count($payparamarr) > 0) {
				$payparamstr = json_encode($payparamarr);
			}
		}
		$dbo = JFactory::getDBO();
		if (!empty($pname) && !empty($ppayment) && !empty($pwhere)) {
			$setpub=$ppublished=="1" ? 1 : 0;
			$psetconfirmed=$psetconfirmed=="1" ? 1 : 0;
			$pshownotealw=$pshownotealw=="1" ? 1 : 0;
			$q = "SELECT `id` FROM `#__vikrentcar_gpayments` WHERE `file`=".$dbo->quote($ppayment)." AND `id`!='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
			//VikRentCar 1.8 : no longer block payment methods that are using the same PHP file
			if ($dbo->getNumRows() >= 0) {
				$q = "UPDATE `#__vikrentcar_gpayments` SET `name`=".$dbo->quote($pname).",`file`=".$dbo->quote($ppayment).",`published`=".$dbo->quote($setpub).",`note`=".$dbo->quote($pnote).",`charge`=".$dbo->quote($pcharge).",`setconfirmed`=".$dbo->quote($psetconfirmed).",`shownotealw`=".$dbo->quote($pshownotealw).",`val_pcent`='".$pval_pcent."',`ch_disc`='".$pch_disc."',`params`=".$dbo->quote($payparamstr)." WHERE `id`=".$dbo->quote($pwhere).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VRPAYMENTUPDATED'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=payments");
			} else {
				VikError::raiseWarning('', JText::_('ERRINVFILEPAYMENT'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=editpayment&cid[]=".$pwhere);
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editpayment&cid[]=".$pwhere);
		}
	}

	function removepayments() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_gpayments` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=payments");
	}

	function cancelpayment() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=payments");
	}

	function setordconfirmed() {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = $cid[0];
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=".(int)$oid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order=$dbo->loadAssocList();
			$vrc_tn = VikRentCar::getTranslator();
			//check if the language in use is the same as the one used during the checkout
			if (!empty($order[0]['lang'])) {
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $order[0]['lang']) {
					$lang->load('com_vikrentcar', JPATH_ADMINISTRATOR, $order[0]['lang'], true);
					$vrc_tn::$force_tolang = $order[0]['lang'];
				}
			}
			//
			$q = "SELECT `units` FROM `#__vikrentcar_cars` WHERE `id`='".$order[0]['idcar']."';";
			$dbo->setQuery($q);
			$dbo->execute();
			$units = $dbo->loadResult();
			$realback = VikRentCar::getHoursCarAvail() * 3600;
			$realback += $order[0]['consegna'];
			if (VikRentCar::carBookable($order[0]['idcar'], $units, $order[0]['ritiro'], $order[0]['consegna'])) {
				$q = "INSERT INTO `#__vikrentcar_busy` (`idcar`,`ritiro`,`consegna`,`realback`) VALUES('".$order[0]['idcar']."','".$order[0]['ritiro']."','".$order[0]['consegna']."','".$realback."');";
				$dbo->setQuery($q);
				$dbo->execute();
				$busynow = $dbo->insertid();
			} else {
				$busynow="";
				VikError::raiseWarning('', JText::_('ERRCONFORDERCARNA'));
			}
			$q = "UPDATE `#__vikrentcar_orders` SET `status`='confirmed', `idbusy`='".$busynow."' WHERE `id`='".$order[0]['id']."';";
			$dbo->setQuery($q);
			$dbo->execute();
			//send mail
			$ftitle = VikRentCar::getFrontTitle($vrc_tn);
			$nowts = $order[0]['ts'];
			$carinfo = VikRentCar::getCarInfo($order[0]['idcar'], $vrc_tn);
			$viklink = JURI::root()."index.php?option=com_vikrentcar&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts'];
			$is_cust_cost = (!empty($order[0]['cust_cost']) && $order[0]['cust_cost'] > 0);
			if (!empty($order[0]['idtar'])) {
				//vikrentcar 1.5
				if ($order[0]['hourly'] == 1) {
					$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `id`='".$order[0]['idtar']."';";
				} else {
					$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$order[0]['idtar']."';";
				}
				//
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					if ($order[0]['hourly'] == 1) {
						$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$order[0]['idtar']."';";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$tar = $dbo->loadAssocList();
						}
					}
				} else {
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
			if ($order[0]['hourly'] == 1 && !empty($tar[0]['hours'])) {
				foreach ($tar as $kt => $vt) {
					$tar[$kt]['days'] = 1;
				}
			}
			//
			//vikrentcar 1.6
			$checkhourscharges = 0;
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
			$ritplace=(!empty($order[0]['idplace']) ? VikRentCar::getPlaceName($order[0]['idplace'], $vrc_tn) : "");
			$consegnaplace=(!empty($order[0]['idreturnplace']) ? VikRentCar::getPlaceName($order[0]['idreturnplace'], $vrc_tn) : "");
			$costplusiva = $is_cust_cost ? $tar[0]['cost'] : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$costminusiva = $is_cust_cost ? VikRentCar::sayCustCostMinusIva($tar[0]['cost'], $order[0]['cust_idiva']) : VikRentCar::sayCostMinusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$pricestr = ($is_cust_cost ? JText::_('VRCRENTCUSTRATEPLAN') : VikRentCar::getPriceName($tar[0]['idprice'], $vrc_tn)).": ".$costplusiva.(!empty($tar[0]['attrdata']) ? "\n".VikRentCar::getPriceAttr($tar[0]['idprice'], $vrc_tn).": ".$tar[0]['attrdata'] : "");
			$isdue = $is_cust_cost ? $tar[0]['cost'] : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$optstr = "";
			$optarrtaxnet = array();
			if (!empty($order[0]['optionals'])) {
				$stepo = explode(";", $order[0]['optionals']);
				foreach ($stepo as $oo) {
					if (!empty($oo)) {
						$stept = explode(":", $oo);
						$q = "SELECT `name`,`cost`,`perday`,`hmany`,`idiva`,`maxprice` FROM `#__vikrentcar_optionals` WHERE `id`=".$dbo->quote($stept[0]).";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$actopt=$dbo->loadAssocList();
							$vrc_tn->translateContents($actopt, '#__vikrentcar_optionals');
							$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
							$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days']) : $actopt[0]['cost'];
							if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
								$realcost = $actopt[0]['maxprice'];
								if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
									$realcost = $actopt[0]['maxprice'] * $stept[1];
								}
							}
							$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva'], $order[0]);
							$isdue += $tmpopr;
							$optnetprice = VikRentCar::sayOptionalsMinusIva($realcost, $actopt[0]['idiva'], $order[0]);
							$optarrtaxnet[] = $optnetprice;
							$optstr .= ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name'].": ".$tmpopr."\n";
						}
					}
				}
			}
			//custom extra costs
			if (!empty($order[0]['extracosts'])) {
				$cur_extra_costs = json_decode($order[0]['extracosts'], true);
				foreach ($cur_extra_costs as $eck => $ecv) {
					$efee_cost = VikRentCar::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $order[0]);
					$isdue += $efee_cost;
					$efee_cost_without = VikRentCar::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $order[0]);
					$optarrtaxnet[] = $efee_cost_without;
					$optstr.=$ecv['name'].": ".$efee_cost."\n";
				}
			}
			//
			$maillocfee="";
			$locfeewithouttax = 0;
			if (!empty($order[0]['idplace']) && !empty($order[0]['idreturnplace'])) {
				$locfee=VikRentCar::getLocFee($order[0]['idplace'], $order[0]['idreturnplace']);
				if ($locfee) {
					//VikRentCar 1.7 - Location fees overrides
					if (strlen($locfee['losoverride']) > 0) {
						$arrvaloverrides = array();
						$valovrparts = explode('_', $locfee['losoverride']);
						foreach ($valovrparts as $valovr) {
							if (!empty($valovr)) {
								$ovrinfo = explode(':', $valovr);
								$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
							}
						}
						if (array_key_exists($order[0]['days'], $arrvaloverrides)) {
							$locfee['cost'] = $arrvaloverrides[$order[0]['days']];
						}
					}
					//end VikRentCar 1.7 - Location fees overrides
					$locfeecost=intval($locfee['daily']) == 1 ? ($locfee['cost'] * $order[0]['days']) : $locfee['cost'];
					$locfeewith=VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $order[0]);
					$isdue+=$locfeewith;
					$locfeewithouttax = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $order[0]);
					$maillocfee=$locfeewith;
				}
			}
			//VRC 1.9 - Out of Hours Fees
			$oohfee = VikRentCar::getOutOfHoursFees($order[0]['idplace'], $order[0]['idreturnplace'], $order[0]['ritiro'], $order[0]['consegna'], array('id' => $order[0]['idcar']));
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
			if (strlen($order[0]['coupon']) > 0) {
				$usedcoupon = true;
				$expcoupon = explode(";", $order[0]['coupon']);
				$isdue = $isdue - $expcoupon[1];
			}
			//
			if (!empty($busynow)) {
				$arrayinfopdf = array('days' => $order[0]['days'], 'tarminusiva' => $costminusiva, 'tartax' => ($costplusiva - $costminusiva), 'opttaxnet' => $optarrtaxnet, 'locfeenet' => $locfeewithouttax, 'oohfeenet' => $oohfeewithouttax, 'order_id' => $order[0]['id'], 'tot_paid' => $order[0]['totpaid']);
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('VRORDERSETASCONF'));
				VikRentCar::sendCustMailFromBack($order[0]['custmail'], strip_tags($ftitle)." ".JText::_('VRRENTALORD'), $ftitle, $nowts, $order[0]['custdata'], $carinfo['name'], $order[0]['ritiro'], $order[0]['consegna'], $pricestr, $optstr, $isdue, $viklink, JText::_('VRCOMPLETED'), $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $order[0]['id'], $order[0]['coupon'], $arrayinfopdf);
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$oid);
	}

	function overv() {
		VikRentCarHelper::printHeader("15");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'overv'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function canceloverv() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=overv");
	}

	function cancelbusy() {
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$pidorder.($pgoto == 'overv' ? '&goto=overv' : ''));
	}

	function customf() {
		VikRentCarHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'customf'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newcustomf() {
		VikRentCarHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomf'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editcustomf() {
		VikRentCarHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomf'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function createcustomf() {
		$pname = VikRequest::getString('name', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptype = VikRequest::getString('type', '', 'request');
		$pchoose = VikRequest::getVar('choose', array(0));
		$prequired = VikRequest::getString('required', '', 'request');
		$prequired = $prequired == "1" ? 1 : 0;
		$pisemail = VikRequest::getString('isemail', '', 'request');
		$pisemail = $pisemail == "1" ? 1 : 0;
		$pisnominative = VikRequest::getString('isnominative', '', 'request');
		$pisnominative = $pisnominative == "1" && $ptype == 'text' ? 1 : 0;
		$pisphone = VikRequest::getString('isphone', '', 'request');
		$pisphone = $pisphone == "1" && $ptype == 'text' ? 1 : 0;
		$pisaddress = VikRequest::getString('isaddress', '', 'request');
		$pisaddress = $pisaddress == "1" && $ptype == 'text' ? 1 : 0;
		$piscity = VikRequest::getString('iscity', '', 'request');
		$piscity = $piscity == "1" && $ptype == 'text' ? 1 : 0;
		$piszip = VikRequest::getString('iszip', '', 'request');
		$piszip = $piszip == "1" && $ptype == 'text' ? 1 : 0;
		$piscompany = VikRequest::getString('iscompany', '', 'request');
		$piscompany = $piscompany == "1" && $ptype == 'text' ? 1 : 0;
		$pisvat = VikRequest::getString('isvat', '', 'request');
		$pisvat = $pisvat == "1" && $ptype == 'text' ? 1 : 0;
		$fieldflag = '';
		if ($pisaddress == 1) {
			$fieldflag = 'address';
		} elseif ($piscity == 1) {
			$fieldflag = 'city';
		} elseif ($piszip == 1) {
			$fieldflag = 'zip';
		} elseif ($piscompany == 1) {
			$fieldflag = 'company';
		} elseif ($pisvat == 1) {
			$fieldflag = 'vat';
		}
		$ppoplink = VikRequest::getString('poplink', '', 'request');
		$choosestr = "";
		if (@count($pchoose) > 0) {
			foreach ($pchoose as $ch) {
				if (!empty($ch)) {
					$choosestr .= $ch.";;__;;";
				}
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `ordering` FROM `#__vikrentcar_custfields` ORDER BY `#__vikrentcar_custfields`.`ordering` DESC LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$getlast = $dbo->loadResult();
			$newsortnum = $getlast + 1;
		} else {
			$newsortnum = 1;
		}
		$q = "INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`,`flag`) VALUES(".$dbo->quote($pname).", ".$dbo->quote($ptype).", ".$dbo->quote($choosestr).", ".$dbo->quote($prequired).", ".$dbo->quote($newsortnum).", ".$dbo->quote($pisemail).", ".$dbo->quote($ppoplink).", ".$pisnominative.", ".$pisphone.", ".$dbo->quote($fieldflag).");";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=customf");
	}

	function updatecustomf() {
		$pname = VikRequest::getString('name', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptype = VikRequest::getString('type', '', 'request');
		$pchoose = VikRequest::getVar('choose', array(0));
		$prequired = VikRequest::getString('required', '', 'request');
		$prequired = $prequired == "1" ? 1 : 0;
		$pisemail = VikRequest::getString('isemail', '', 'request');
		$pisemail = $pisemail == "1" ? 1 : 0;
		$pisnominative = VikRequest::getString('isnominative', '', 'request');
		$pisnominative = $pisnominative == "1" && $ptype == 'text' ? 1 : 0;
		$pisphone = VikRequest::getString('isphone', '', 'request');
		$pisphone = $pisphone == "1" && $ptype == 'text' ? 1 : 0;
		$pisaddress = VikRequest::getString('isaddress', '', 'request');
		$pisaddress = $pisaddress == "1" && $ptype == 'text' ? 1 : 0;
		$piscity = VikRequest::getString('iscity', '', 'request');
		$piscity = $piscity == "1" && $ptype == 'text' ? 1 : 0;
		$piszip = VikRequest::getString('iszip', '', 'request');
		$piszip = $piszip == "1" && $ptype == 'text' ? 1 : 0;
		$piscompany = VikRequest::getString('iscompany', '', 'request');
		$piscompany = $piscompany == "1" && $ptype == 'text' ? 1 : 0;
		$pisvat = VikRequest::getString('isvat', '', 'request');
		$pisvat = $pisvat == "1" && $ptype == 'text' ? 1 : 0;
		$fieldflag = '';
		if ($pisaddress == 1) {
			$fieldflag = 'address';
		} elseif ($piscity == 1) {
			$fieldflag = 'city';
		} elseif ($piszip == 1) {
			$fieldflag = 'zip';
		} elseif ($piscompany == 1) {
			$fieldflag = 'company';
		} elseif ($pisvat == 1) {
			$fieldflag = 'vat';
		}
		$ppoplink = VikRequest::getString('poplink', '', 'request');
		$pwhere = VikRequest::getInt('where', '', 'request');
		$choosestr = "";
		if (@count($pchoose) > 0) {
			foreach ($pchoose as $ch) {
				if (!empty($ch)) {
					$choosestr .= $ch.";;__;;";
				}
			}
		}
		$dbo = JFactory::getDBO();
		$q = "UPDATE `#__vikrentcar_custfields` SET `name`=".$dbo->quote($pname).",`type`=".$dbo->quote($ptype).",`choose`=".$dbo->quote($choosestr).",`required`=".$dbo->quote($prequired).",`isemail`=".$dbo->quote($pisemail).",`poplink`=".$dbo->quote($ppoplink).",`isnominative`=".$pisnominative.",`isphone`=".$pisphone.",`flag`=".$dbo->quote($fieldflag)." WHERE `id`=".$dbo->quote($pwhere).";";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=customf");
	}

	function removecustomf() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_custfields` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=customf");
	}

	function cancelcustomf() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=customf");
	}

	function sortfield() {
		$sortid = VikRequest::getVar('cid', array(0));
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentcar_custfields` ORDER BY `#__vikrentcar_custfields`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering'])==intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_custfields` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering'])==intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_custfields` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=customf");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar");
		}
	}

	function removemoreimgs() {
		$mainframe = JFactory::getApplication();
		$pcarid = VikRequest::getInt('carid', '', 'request');
		$pimgind = VikRequest::getInt('imgind', '', 'request');
		if (!empty($pcarid) && strlen($pimgind) > 0) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `moreimgs` FROM `#__vikrentcar_cars` WHERE `id`='".$pcarid."';";
			$dbo->setQuery($q);
			$dbo->execute();
			$actmore = $dbo->loadResult();
			if (strlen($actmore) > 0) {
				$actsplit = explode(';;', $actmore);
				if (array_key_exists($pimgind, $actsplit)) {
					@unlink(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'big_'.$actsplit[$pimgind]);
					@unlink(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'thumb_'.$actsplit[$pimgind]);
					unset($actsplit[$pimgind]);
					$newstr = "";
					foreach ($actsplit as $oi) {
						if (!empty($oi)) {
							$newstr .= $oi.';;';
						}
					}
					$q = "UPDATE `#__vikrentcar_cars` SET `moreimgs`=".$dbo->quote($newstr)." WHERE `id`='".$pcarid."';";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editcar&cid[]=".$pcarid);
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar");
		}
	}

	function coupons() {
		VikRentCarHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'coupons'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newcoupon() {
		VikRentCarHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecoupon'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editcoupon() {
		VikRentCarHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecoupon'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function createcoupon() {
		$mainframe = JFactory::getApplication();
		$pcode = VikRequest::getString('code', '', 'request');
		$pvalue = VikRequest::getFloat('value', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array(0));
		$ptype = VikRequest::getString('type', '', 'request');
		$ptype = $ptype == "1" ? 1 : 2;
		$ppercentot = VikRequest::getString('percentot', '', 'request');
		$ppercentot = $ppercentot == "1" ? 1 : 2;
		$pallvehicles = VikRequest::getString('allvehicles', '', 'request');
		$pallvehicles = $pallvehicles == "1" ? 1 : 0;
		$pmintotord = VikRequest::getString('mintotord', '', 'request');
		$stridcars = "";
		if (@count($pidcars) > 0 && $pallvehicles != 1) {
			foreach ($pidcars as $ch) {
				if (!empty($ch)) {
					$stridcars .= ";".$ch.";";
				}
			}
		}
		$strdatevalid = "";
		if (strlen($pfrom) > 0 && strlen($pto) > 0) {
			$first = VikRentCar::getDateTimestamp($pfrom, 0, 0);
			$second = VikRentCar::getDateTimestamp($pto, 0, 0);
			if ($first < $second) {
				$strdatevalid .= $first."-".$second;
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentcar_coupons` WHERE `code`=".$dbo->quote($pcode).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			VikError::raiseWarning('', JText::_('VRCCOUPONEXISTS'));
		} else {
			$mainframe->enqueueMessage(JText::_('VRCCOUPONSAVEOK'));
			$q = "INSERT INTO `#__vikrentcar_coupons` (`code`,`type`,`percentot`,`value`,`datevalid`,`allvehicles`,`idcars`,`mintotord`) VALUES(".$dbo->quote($pcode).",'".$ptype."','".$ppercentot."',".$dbo->quote($pvalue).",'".$strdatevalid."','".$pallvehicles."','".$stridcars."', ".$dbo->quote($pmintotord).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=coupons");
	}

	function updatecoupon() {
		$mainframe = JFactory::getApplication();
		$pcode = VikRequest::getString('code', '', 'request');
		$pvalue = VikRequest::getFloat('value', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array(0));
		$pwhere = VikRequest::getString('where', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$ptype = $ptype == "1" ? 1 : 2;
		$ppercentot = VikRequest::getString('percentot', '', 'request');
		$ppercentot = $ppercentot == "1" ? 1 : 2;
		$pallvehicles = VikRequest::getString('allvehicles', '', 'request');
		$pallvehicles = $pallvehicles == "1" ? 1 : 0;
		$pmintotord = VikRequest::getString('mintotord', '', 'request');
		$stridcars = "";
		if (@count($pidcars) > 0 && $pallvehicles != 1) {
			foreach ($pidcars as $ch) {
				if (!empty($ch)) {
					$stridcars .= ";".$ch.";";
				}
			}
		}
		$strdatevalid = "";
		if (strlen($pfrom) > 0 && strlen($pto) > 0) {
			$first = VikRentCar::getDateTimestamp($pfrom, 0, 0);
			$second = VikRentCar::getDateTimestamp($pto, 0, 0);
			if ($first < $second) {
				$strdatevalid .= $first."-".$second;
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentcar_coupons` WHERE `code`=".$dbo->quote($pcode)." AND `id`!='".$pwhere."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			VikError::raiseWarning('', JText::_('VRCCOUPONEXISTS'));
		} else {
			$mainframe->enqueueMessage(JText::_('VRCCOUPONSAVEOK'));
			$q = "UPDATE `#__vikrentcar_coupons` SET `code`=".$dbo->quote($pcode).",`type`='".$ptype."',`percentot`='".$ppercentot."',`value`=".$dbo->quote($pvalue).",`datevalid`='".$strdatevalid."',`allvehicles`='".$pallvehicles."',`idcars`='".$stridcars."',`mintotord`=".$dbo->quote($pmintotord)." WHERE `id`='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=coupons");
	}

	function removecoupons() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_coupons` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=coupons");
	}

	function cancelcoupon() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=coupons");
	}

	function resendordemail() {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = (int)$cid[0];
		$this->do_resendordemail($oid);
	}

	private function do_resendordemail($oid, $checkdbsendpdf = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=".$oid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order = $dbo->loadAssocList();
			$vrc_tn = VikRentCar::getTranslator();
			//check if the language in use is the same as the one used during the checkout
			if (!empty($order[0]['lang'])) {
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $order[0]['lang']) {
					$lang->load('com_vikrentcar', JPATH_ADMINISTRATOR, $order[0]['lang'], true);
					$vrc_tn::$force_tolang = $order[0]['lang'];
				}
			}
			//
			//send mail
			$ftitle = VikRentCar::getFrontTitle($vrc_tn);
			$nowts = $order[0]['ts'];
			$carinfo = VikRentCar::getCarInfo($order[0]['idcar'], $vrc_tn);
			$viklink = JURI::root()."index.php?option=com_vikrentcar&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts'];
			$is_cust_cost = (!empty($order[0]['cust_cost']) && $order[0]['cust_cost'] > 0);
			if (!empty($order[0]['idtar'])) {
				//vikrentcar 1.5
				if ($order[0]['hourly'] == 1) {
					$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `id`='".$order[0]['idtar']."';";
				} else {
					$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$order[0]['idtar']."';";
				}
				//
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					if ($order[0]['hourly'] == 1) {
						$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$order[0]['idtar']."';";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$tar = $dbo->loadAssocList();
						}
					}
				} else {
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
			if ($order[0]['hourly'] == 1 && !empty($tar[0]['hours'])) {
				foreach ($tar as $kt => $vt) {
					$tar[$kt]['days'] = 1;
				}
			}
			//
			//vikrentcar 1.6
			$checkhourscharges = 0;
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
			$ritplace = (!empty($order[0]['idplace']) ? VikRentCar::getPlaceName($order[0]['idplace'], $vrc_tn) : "");
			$consegnaplace = (!empty($order[0]['idreturnplace']) ? VikRentCar::getPlaceName($order[0]['idreturnplace'], $vrc_tn) : "");
			$costplusiva = $is_cust_cost ? $tar[0]['cost'] : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$costminusiva = $is_cust_cost ? VikRentCar::sayCustCostMinusIva($tar[0]['cost'], $order[0]['cust_idiva']) : VikRentCar::sayCostMinusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$pricestr = ($is_cust_cost ? JText::_('VRCRENTCUSTRATEPLAN') : VikRentCar::getPriceName($tar[0]['idprice'], $vrc_tn)).": ".$costplusiva.(!empty($tar[0]['attrdata']) ? "\n".VikRentCar::getPriceAttr($tar[0]['idprice'], $vrc_tn).": ".$tar[0]['attrdata'] : "");
			$isdue = $is_cust_cost ? $tar[0]['cost'] : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$optstr = "";
			$optarrtaxnet = array();
			if (!empty($order[0]['optionals'])) {
				$stepo = explode(";", $order[0]['optionals']);
				foreach ($stepo as $oo) {
					if (!empty($oo)) {
						$stept = explode(":", $oo);
						$q = "SELECT `name`,`cost`,`perday`,`hmany`,`idiva`,`maxprice` FROM `#__vikrentcar_optionals` WHERE `id`=".$dbo->quote($stept[0]).";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$actopt = $dbo->loadAssocList();
							$vrc_tn->translateContents($actopt, '#__vikrentcar_optionals');
							$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
							$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days']) : $actopt[0]['cost'];
							if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
								$realcost = $actopt[0]['maxprice'];
								if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
									$realcost = $actopt[0]['maxprice'] * $stept[1];
								}
							}
							$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva'], $order[0]);
							$isdue += $tmpopr;
							$optnetprice = VikRentCar::sayOptionalsMinusIva($realcost, $actopt[0]['idiva'], $order[0]);
							$optarrtaxnet[] = $optnetprice;
							$optstr .= ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name'].": ".$tmpopr."\n";
						}
					}
				}
			}
			//custom extra costs
			if (!empty($order[0]['extracosts'])) {
				$cur_extra_costs = json_decode($order[0]['extracosts'], true);
				foreach ($cur_extra_costs as $eck => $ecv) {
					$efee_cost = VikRentCar::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $order[0]);
					$isdue += $efee_cost;
					$efee_cost_without = VikRentCar::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $order[0]);
					$optarrtaxnet[] = $efee_cost_without;
					$optstr .= $ecv['name'].": ".$efee_cost."\n";
				}
			}
			//
			$maillocfee = "";
			$locfeewithouttax = 0;
			if (!empty($order[0]['idplace']) && !empty($order[0]['idreturnplace'])) {
				$locfee = VikRentCar::getLocFee($order[0]['idplace'], $order[0]['idreturnplace']);
				if ($locfee) {
					//VikRentCar 1.7 - Location fees overrides
					if (strlen($locfee['losoverride']) > 0) {
						$arrvaloverrides = array();
						$valovrparts = explode('_', $locfee['losoverride']);
						foreach ($valovrparts as $valovr) {
							if (!empty($valovr)) {
								$ovrinfo = explode(':', $valovr);
								$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
							}
						}
						if (array_key_exists($order[0]['days'], $arrvaloverrides)) {
							$locfee['cost'] = $arrvaloverrides[$order[0]['days']];
						}
					}
					//end VikRentCar 1.7 - Location fees overrides
					$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $order[0]['days']) : $locfee['cost'];
					$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $order[0]);
					$isdue += $locfeewith;
					$locfeewithouttax = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $order[0]);
					$maillocfee = $locfeewith;
				}
			}
			//VRC 1.9 - Out of Hours Fees
			$oohfee = VikRentCar::getOutOfHoursFees($order[0]['idplace'], $order[0]['idreturnplace'], $order[0]['ritiro'], $order[0]['consegna'], array('id' => $order[0]['idcar']));
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
			if (strlen($order[0]['coupon']) > 0) {
				$usedcoupon = true;
				$expcoupon = explode(";", $order[0]['coupon']);
				$isdue = $isdue - $expcoupon[1];
			}
			//
			if (!empty($order[0]['custmail'])) {
				$arrayinfopdf = array('days' => $order[0]['days'], 'tarminusiva' => $costminusiva, 'tartax' => ($costplusiva - $costminusiva), 'opttaxnet' => $optarrtaxnet, 'locfeenet' => $locfeewithouttax, 'oohfeenet' => $oohfeewithouttax, 'order_id' => $order[0]['id'], 'tot_paid' => $order[0]['totpaid']);
				$sendpdf = true;
				if (!$checkdbsendpdf) {
					$psendpdf = VikRequest::getString('sendpdf', '', 'request');
					if ($psendpdf != "1") {
						$sendpdf = false;
					}
				}
				$mainframe->enqueueMessage(JText::sprintf('VRORDERMAILRESENT', $order[0]['custmail']));
				$saystatus = $order[0]['status'] == 'confirmed' ? JText::_('VRCOMPLETED') : ($order[0]['status'] == 'standby' ? JText::_('VRSTANDBY') : JText::_('VRCANCELLED'));
				VikRentCar::sendCustMailFromBack($order[0]['custmail'], strip_tags($ftitle)." ".JText::_('VRRENTALORD'), $ftitle, $nowts, $order[0]['custdata'], $carinfo['name'], $order[0]['ritiro'], $order[0]['consegna'], $pricestr, $optstr, $isdue, $viklink, $saystatus, $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $order[0]['id'], $order[0]['coupon'], $arrayinfopdf, $sendpdf);
			} else {
				VikError::raiseWarning('', JText::_('VRORDERMAILRESENTNOREC'));
			}
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$oid);
	}

	function sortcarat() {
		$mainframe = JFactory::getApplication();
		$sortid = VikRequest::getVar('cid', array(0));
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDBO();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentcar_caratteristiche` ORDER BY `#__vikrentcar_caratteristiche`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr = $dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering'])==intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_caratteristiche` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_caratteristiche` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_caratteristiche` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_caratteristiche` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_caratteristiche` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_caratteristiche` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=carat");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar");
		}
	}

	function sortoptional() {
		$mainframe = JFactory::getApplication();
		$sortid = VikRequest::getVar('cid', array(0));
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDBO();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentcar_optionals` ORDER BY `#__vikrentcar_optionals`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_optionals` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_optionals` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=optionals");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar");
		}
	}

	function export() {
		VikRentCarHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'export'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function doexport() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$oids = VikRequest::getVar('cid', array(0));
		$oids = count($oids) > 0 && intval($oids[key($oids)]) > 0 ? $oids : array();
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pdatetype = VikRequest::getString('datetype', '', 'request');
		$pdatetype = $pdatetype == 'ts' ? 'ts' : 'ritiro';
		$plocation = VikRequest::getString('location', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$ptype = $ptype == "csv" ? "csv" : ($ptype == "xml" ? "xml" : "ics");
		$pstatus = VikRequest::getString('status', '', 'request');
		$pdateformat = VikRequest::getString('dateformat', '', 'request');
		$pxml_file = VikRequest::getString('xml_file', '', 'request');
		$nowdf = VikRentCar::getDateFormat(true);
		$nowtf = VikRentCar::getTimeFormat(true);
		$pdateformat .= ' '.$nowtf;
		$tf = $nowtf;
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$clauses = array();
		if (count($oids) > 0) {
			$clauses[] = "`o`.`id` IN(".implode(',', $oids).")";
		}
		if ($pstatus == "C") {
			$clauses[] = "`o`.`status`='confirmed'";
		}
		if (!empty($pfrom) && VikRentCar::dateIsValid($pfrom)) {
			$fromts = VikRentCar::getDateTimestamp($pfrom, '0', '0');
			$clauses[] = "`o`.`".$pdatetype."`>=".$fromts;
		}
		if (!empty($pto) && VikRentCar::dateIsValid($pto)) {
			$tots = VikRentCar::getDateTimestamp($pto, '23', '59');
			$clauses[] = "`o`.`".$pdatetype."`<=".$tots;
		}
		if (!empty($plocation)) {
			$clauses[] = "(`o`.`idplace`=".intval($plocation)." OR `o`.`idreturnplace`=".intval($plocation).")";
		}
		$download_string = '';
		$q = "SELECT `o`.*,`lp`.`name` AS `pickup_location_name`,`ld`.`name` AS `dropoff_location_name` FROM `#__vikrentcar_orders` AS `o` ".
		"LEFT JOIN `#__vikrentcar_places` `lp` ON `o`.`idplace`=`lp`.`id` ".
		"LEFT JOIN `#__vikrentcar_places` `ld` ON `o`.`idreturnplace`=`ld`.`id`".(count($clauses) > 0 ? " WHERE ".implode(' AND ', $clauses) : "")." ORDER BY `o`.`ritiro` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rows = $dbo->loadAssocList();
			if ($ptype == "csv") {
				//init csv creation
				$csvlines = array();
				$csvlines[] = array('ID', JText::_('VRCEXPCSVPICK'), JText::_('VRCEXPCSVDROP'), JText::_('VRCEXPCSVCAR'), JText::_('VRCEXPCSVPICKLOC'), JText::_('VRCEXPCSVDROPLOC'), JText::_('VRCEXPCSVCUSTINFO'), JText::_('VRCEXPCSVPAYMETH'), JText::_('VRCEXPCSVORDSTATUS'), JText::_('VRCEXPCSVTOT'), JText::_('VRCEXPCSVTOTPAID'));
				foreach ($rows as $r) {
					$pickdate = $pdateformat == 'ts' ? $r['ritiro'] : date($pdateformat, $r['ritiro']);
					$dropdate = $pdateformat == 'ts' ? $r['consegna'] : date($pdateformat, $r['consegna']);
					$car = VikRentCar::getCarInfo($r['idcar']);
					$pickloc = VikRentCar::getPlaceName($r['idplace']);
					$droploc = VikRentCar::getPlaceName($r['idreturnplace']);
					$custdata = preg_replace('/\s+/', ' ', trim($r['custdata']));
					$payment = VikRentCar::getPayment($r['idpayment']);
					$saystatus = ($r['status']=="confirmed" ? JText::_('VRCONFIRMED') : ($r['status'] == 'standby' ? JText::_('VRSTANDBY') : JText::_('VRCANCELLED')));
					$csvlines[] = array($r['id'], $pickdate, $dropdate, $car['name'], $pickloc, $droploc, $custdata, $payment['name'], $saystatus, number_format($r['order_total'], 2), number_format($r['totpaid'], 2));
				}
				//end csv creation
			} elseif ($ptype == "ics") {
				//init ics creation
				$icslines = array();
				$icscontent = "BEGIN:VCALENDAR\n";
				$icscontent .= "VERSION:2.0\n";
				$icscontent .= "PRODID:-//e4j//VikRentCar//EN\n";
				$icscontent .= "CALSCALE:GREGORIAN\n";
				$str = "";
				foreach ($rows as $r) {
					$uri = JURI::root().'index.php?option=com_vikrentcar&task=vieworder&sid='.$r['sid'].'&ts='.$r['ts'];
					$pickloc = VikRentCar::getPlaceName($r['idplace']);
					$car = VikRentCar::getCarInfo($r['idcar']);
					//$custdata = preg_replace('/\s+/', ' ', trim($r['custdata']));
					//$description = $car['name']."\\n".$r['custdata'];
					$description = $car['name']."\\n".str_replace("\n", "\\n", trim($r['custdata']));
					$str .= "BEGIN:VEVENT\n";
					//End of the Event set as Pickup Date, decomment line below to have it on Drop Off Date
					//$str .= "DTEND:".date('Ymd\THis\Z', $r['consegna'])."\n";
					$str .= "DTEND:".date('Ymd\THis\Z', $r['ritiro'])."\n";
					//
					$str .= "UID:".uniqid()."\n";
					$str .= "DTSTAMP:".date('Ymd\THis\Z', time())."\n";
					$str .= "LOCATION:".preg_replace('/([\,;])/','\\\$1', $pickloc)."\n";
					$str .= ((strlen($description) > 0 ) ? "DESCRIPTION:".preg_replace('/([\,;])/','\\\$1', $description)."\n" : "");
					$str .= "URL;VALUE=URI:".preg_replace('/([\,;])/','\\\$1', $uri)."\n";
					$str .= "SUMMARY:".JText::sprintf('VRCICSEXPSUMMARY', date($tf, $r['ritiro']))."\n";
					$str .= "DTSTART:".date('Ymd\THis\Z', $r['ritiro'])."\n";
					$str .= "END:VEVENT\n";
				}
				$icscontent .= $str;
				$icscontent .= "END:VCALENDAR\n";
				$download_string = $icscontent;
				//end ics creation
			} elseif ($ptype == "xml") {
				//init xml creation
				if (!empty($pxml_file) && file_exists(VRC_ADMIN_PATH.DS.'xml_export'.DS.$pxml_file)) {
					require_once(VRC_ADMIN_PATH.DS.'xml_export'.DS.$pxml_file);
					foreach ($rows as $key => $row) {
						$rows[$key]['car_details'] = VikRentCar::getCarInfo($row['idcar']);
						$rows[$key]['price_info'] = '';
						$q = "SELECT `c`.`idprice`,`c`.`cost`,`c`.`attrdata`,`p`.`name`,`p`.`idiva`,`t`.`aliq` FROM `#__vikrentcar_dispcost` AS `c` LEFT JOIN `#__vikrentcar_prices` `p` ON `c`.`idprice`=`p`.`id` LEFT JOIN `#__vikrentcar_iva` `t` ON `p`.`idiva`=`t`.`id` WHERE `c`.`id`=".(intval($row['idtar'])).";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() > 0) {
							$price_info = $dbo->loadAssoc();
							$rows[$key]['price_info'] = $price_info;
						}
						$rows[$key]['car_details']['category_name'] = '';
						if (!empty($rows[$key]['car_details']['idcat'])) {
							$all_cats = explode(';', $rows[$key]['car_details']['idcat']);
							$rows[$key]['car_details']['category_name'] = VikRentCar::getCategoryName($all_cats[0]);
						}
					}
					$obj = new vikRentCarXmlExport($rows);
					$download_string = $obj->generateXml();
				} else {
					VikError::raiseWarning('', JText::_('VRCEXPORTERRFILE'));
					$mainframe->redirect("index.php?option=com_vikrentcar&task=orders");
				}
				//end xml creation
			}
			//download file from buffer
			$dfilename = 'export_'.date('Y-m-d_H_i').'.'.$ptype;
			if ($ptype == "csv") {
				header("Content-type: text/csv");
				header("Cache-Control: no-store, no-cache");
				header('Content-Disposition: attachment; filename="'.$dfilename.'"');
				$outstream = fopen("php://output", 'w');
				foreach ($csvlines as $csvline) {
					fputcsv($outstream, $csvline);
				}
				fclose($outstream);
				exit;
			} else {
				if ($ptype == "xml") {
					header("Content-Type: text/xml; ");
				} else {
					header("Content-Type: application/octet-stream; ");
				}
				header("Cache-Control: no-store, no-cache");
				header("Content-Disposition: attachment; filename=\"".$dfilename."\"");
				$f = fopen('php://output', "w");
				fwrite($f, $download_string);
				fclose($f);
				exit;
			}
		} else {
			VikError::raiseWarning('', JText::_('VRCEXPORTERRNOREC'));
			$mainframe->redirect("index.php?option=com_vikrentcar&task=orders");
		}
	}

	function oohfees() {
		VikRentCarHelper::printHeader("20");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'oohfees'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newoohfee() {
		VikRentCarHelper::printHeader("20");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageoohfee'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editoohfee() {
		VikRentCarHelper::printHeader("20");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageoohfee'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function createoohfee() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pname = VikRequest::getString('name', '', 'request');
		$pfrom = VikRequest::getInt('from', '', 'request');
		$pto = VikRequest::getInt('to', '', 'request');
		$ppickcharge = (float)VikRequest::getString('pickcharge', '', 'request');
		$pdropcharge = (float)VikRequest::getString('dropcharge', '', 'request');
		$pmaxcharge = (float)VikRequest::getString('maxcharge', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array(0));
		$pidplace = VikRequest::getVar('idplace', array(0));
		$ptype = VikRequest::getInt('type', '', 'request');
		$ptype = $ptype > 1 && $ptype <= 3 ? $ptype : 1;
		$paliq = VikRequest::getInt('aliq', '', 'request');
		$pwdays = VikRequest::getVar('wdays', array(0));
		if (!(empty($pfrom) && empty($pto)) && $pfrom != $pto && $pfrom < 86400 && $pto < 86400) {
			$wdays_str = '';
			foreach ($pwdays as $wday) {
				if (!strlen($wday) > 0) {
					continue;
				}
				$wdays_str .= '-'.(int)$wday.'-,';
			}
			$wdays_str = rtrim($wdays_str, ',');
			$cars_str = '';
			foreach ($pidcars as $idcar) {
				if (empty($idcar)) {
					continue;
				}
				$cars_str .= "-".$idcar."-,";
			}
			$q = "INSERT INTO `#__vikrentcar_oohfees` (`oohname`,`pickcharge`,`dropcharge`,`maxcharge`,`idcars`,`from`,`to`,`type`,`idiva`,`wdays`) VALUES(".$dbo->quote($pname).", ".$dbo->quote($ppickcharge).", ".$dbo->quote($pdropcharge).", ".$dbo->quote($pmaxcharge).", ".$dbo->quote($cars_str).", ".$pfrom.", ".$pto.", ".$ptype.", ".(!empty($paliq) ? $paliq : 'NULL').", ".$dbo->quote($wdays_str).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$lid = $dbo->insertid();
			if (!empty($lid)) {
				foreach ($pidplace as $idplace) {
					if (empty($idplace)) {
						continue;
					}
					$q = "INSERT INTO `#__vikrentcar_oohfees_locxref` (`idooh`,`idlocation`) VALUES(".$lid.", ".(int)$idplace.");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
				$mainframe->enqueueMessage(JText::_('VRCOOHFEESAVED'));
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=oohfees");
		} else {
			VikError::raiseWarning('', JText::_('VRCOOHERRTIME'));
			$mainframe->redirect("index.php?option=com_vikrentcar&task=newoohfee");
		}
	}

	function updateoohfee() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pname = VikRequest::getString('name', '', 'request');
		$pfrom = VikRequest::getInt('from', '', 'request');
		$pto = VikRequest::getInt('to', '', 'request');
		$ppickcharge = (float)VikRequest::getString('pickcharge', '', 'request');
		$pdropcharge = (float)VikRequest::getString('dropcharge', '', 'request');
		$pmaxcharge = (float)VikRequest::getString('maxcharge', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array(0));
		$pidplace = VikRequest::getVar('idplace', array(0));
		$ptype = VikRequest::getInt('type', '', 'request');
		$ptype = $ptype > 1 && $ptype <= 3 ? $ptype : 1;
		$paliq = VikRequest::getInt('aliq', '', 'request');
		$pwdays = VikRequest::getVar('wdays', array(0));
		$pwhere = VikRequest::getInt('where', '', 'request');
		if (!(empty($pfrom) && empty($pto)) && $pfrom != $pto && $pfrom < 86400 && $pto < 86400 && !empty($pwhere)) {
			$wdays_str = '';
			foreach ($pwdays as $wday) {
				if (!strlen($wday) > 0) {
					continue;
				}
				$wdays_str .= '-'.(int)$wday.'-,';
			}
			$wdays_str = rtrim($wdays_str, ',');
			$cars_str = '';
			foreach ($pidcars as $idcar) {
				if (empty($idcar)) {
					continue;
				}
				$cars_str .= "-".$idcar."-,";
			}
			$q = "UPDATE `#__vikrentcar_oohfees` SET `oohname`=".$dbo->quote($pname).",`pickcharge`=".$dbo->quote($ppickcharge).",`dropcharge`=".$dbo->quote($pdropcharge).",`maxcharge`=".$dbo->quote($pmaxcharge).",`idcars`=".$dbo->quote($cars_str).",`from`=".$pfrom.",`to`=".$pto.",`type`=".$ptype.",`idiva`=".(!empty($paliq) ? $paliq : 'NULL').",`wdays`=".$dbo->quote($wdays_str)." WHERE `id`=".$pwhere.";";
			$dbo->setQuery($q);
			$dbo->execute();
			$q = "DELETE FROM `#__vikrentcar_oohfees_locxref` WHERE `idooh`=".$pwhere.";";
			$dbo->setQuery($q);
			$dbo->execute();
			foreach ($pidplace as $idplace) {
				if (empty($idplace)) {
					continue;
				}
				$q = "INSERT INTO `#__vikrentcar_oohfees_locxref` (`idooh`,`idlocation`) VALUES(".$pwhere.", ".(int)$idplace.");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			$mainframe->enqueueMessage(JText::_('VRCOOHFEESAVED'));
			$mainframe->redirect("index.php?option=com_vikrentcar&task=oohfees");
		} else {
			VikError::raiseWarning('', JText::_('VRCOOHERRTIME'));
			$mainframe->redirect("index.php?option=com_vikrentcar&task=oohfees");
		}
	}

	function removeoohfees() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_oohfees` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikrentcar_oohfees_locxref` WHERE `idooh`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=oohfees");
	}

	function canceloohfee() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=oohfees");
	}

	function customercheckin() {
		$dbo = JFactory::getDBO();
		$cid = VikRequest::getVar('cid', array(0));
		$oid = (int)$cid[0];
		$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=".$oid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order = $dbo->loadAssocList();
			$vrc_tn = VikRentCar::getTranslator();
			//check if the language in use is the same as the one used during the checkout
			if (!empty($order[0]['lang'])) {
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $order[0]['lang']) {
					$lang->load('com_vikrentcar', JPATH_ADMINISTRATOR, $order[0]['lang'], true);
					$vrc_tn::$force_tolang = $order[0]['lang'];
				}
			}
			//
			//send mail
			$ftitle = VikRentCar::getFrontTitle();
			$nowts = $order[0]['ts'];
			$carinfo = VikRentCar::getCarInfo($order[0]['idcar']);
			$viklink = JURI::root()."index.php?option=com_vikrentcar&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts'];
			$is_cust_cost = (!empty($order[0]['cust_cost']) && $order[0]['cust_cost'] > 0);
			if (!empty($order[0]['idtar'])) {
				//vikrentcar 1.5
				if ($order[0]['hourly'] == 1) {
					$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `id`='".$order[0]['idtar']."';";
				} else {
					$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$order[0]['idtar']."';";
				}
				//
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					if ($order[0]['hourly'] == 1) {
						$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$order[0]['idtar']."';";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$tar = $dbo->loadAssocList();
						}
					}
				} else {
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
			if ($order[0]['hourly'] == 1 && !empty($tar[0]['hours'])) {
				foreach ($tar as $kt => $vt) {
					$tar[$kt]['days'] = 1;
				}
			}
			//
			//vikrentcar 1.6
			$checkhourscharges = 0;
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
			$ritplace = (!empty($order[0]['idplace']) ? VikRentCar::getPlaceName($order[0]['idplace']) : "");
			$consegnaplace = (!empty($order[0]['idreturnplace']) ? VikRentCar::getPlaceName($order[0]['idreturnplace']) : "");
			$costplusiva = $is_cust_cost ? $tar[0]['cost'] : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$costminusiva = $is_cust_cost ? VikRentCar::sayCustCostMinusIva($tar[0]['cost'], $order[0]['cust_idiva']) : VikRentCar::sayCostMinusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$pricestr = ($is_cust_cost ? JText::_('VRCRENTCUSTRATEPLAN') : VikRentCar::getPriceName($tar[0]['idprice'])).": ".$costplusiva.(!empty($tar[0]['attrdata']) ? "\n".VikRentCar::getPriceAttr($tar[0]['idprice']).": ".$tar[0]['attrdata'] : "");
			$isdue = $is_cust_cost ? $tar[0]['cost'] : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$optstr = "";
			$optarrtaxnet = array();
			if (!empty($order[0]['optionals'])) {
				$stepo=explode(";", $order[0]['optionals']);
				foreach ($stepo as $oo) {
					if (!empty($oo)) {
						$stept = explode(":", $oo);
						$q = "SELECT `name`,`cost`,`perday`,`hmany`,`idiva`,`maxprice` FROM `#__vikrentcar_optionals` WHERE `id`=".$dbo->quote($stept[0]).";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$actopt = $dbo->loadAssocList();
							$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
							$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days']) : $actopt[0]['cost'];
							if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
								$realcost = $actopt[0]['maxprice'];
								if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
									$realcost = $actopt[0]['maxprice'] * $stept[1];
								}
							}
							$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva'], $order[0]);
							$isdue += $tmpopr;
							$optnetprice = VikRentCar::sayOptionalsMinusIva($realcost, $actopt[0]['idiva'], $order[0]);
							$optarrtaxnet[] = $optnetprice;
							$optstr .= ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name'].": ".$tmpopr."\n";
						}
					}
				}
			}
			//custom extra costs
			if (!empty($order[0]['extracosts'])) {
				$cur_extra_costs = json_decode($order[0]['extracosts'], true);
				foreach ($cur_extra_costs as $eck => $ecv) {
					$efee_cost = VikRentCar::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $order[0]);
					$isdue += $efee_cost;
					$efee_cost_without = VikRentCar::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $order[0]);
					$optarrtaxnet[] = $efee_cost_without;
					$optstr.=$ecv['name'].": ".$efee_cost."\n";
				}
			}
			//
			$maillocfee = "";
			$locfeewithouttax = 0;
			if (!empty($order[0]['idplace']) && !empty($order[0]['idreturnplace'])) {
				$locfee = VikRentCar::getLocFee($order[0]['idplace'], $order[0]['idreturnplace']);
				if ($locfee) {
					//VikRentCar 1.7 - Location fees overrides
					if (strlen($locfee['losoverride']) > 0) {
						$arrvaloverrides = array();
						$valovrparts = explode('_', $locfee['losoverride']);
						foreach ($valovrparts as $valovr) {
							if (!empty($valovr)) {
								$ovrinfo = explode(':', $valovr);
								$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
							}
						}
						if (array_key_exists($order[0]['days'], $arrvaloverrides)) {
							$locfee['cost'] = $arrvaloverrides[$order[0]['days']];
						}
					}
					//end VikRentCar 1.7 - Location fees overrides
					$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $order[0]['days']) : $locfee['cost'];
					$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $order[0]);
					$isdue += $locfeewith;
					$locfeewithouttax = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $order[0]);
					$maillocfee = $locfeewith;
				}
			}
			//VRC 1.9 - Out of Hours Fees
			$oohfee = VikRentCar::getOutOfHoursFees($order[0]['idplace'], $order[0]['idreturnplace'], $order[0]['ritiro'], $order[0]['consegna'], array('id' => $order[0]['idcar']));
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
			if (strlen($order[0]['coupon']) > 0) {
				$usedcoupon = true;
				$expcoupon = explode(";", $order[0]['coupon']);
				$isdue = $isdue - $expcoupon[1];
			}
			//
			$arrayinfopdf = array('days' => $order[0]['days'], 'tarminusiva' => $costminusiva, 'tartax' => ($costplusiva - $costminusiva), 'opttaxnet' => $optarrtaxnet, 'locfeenet' => $locfeewithouttax, 'oohfeenet' => $oohfeewithouttax, 'order_id' => $order[0]['id'], 'tot_paid' => $order[0]['totpaid']);
			$saystatus = $order[0]['status'] == 'confirmed' ? JText::_('VRCOMPLETED') : ($order[0]['status'] == 'standby' ? JText::_('VRSTANDBY') : JText::_('VRCANCELLED'));
			VikRentCar::generateCheckinPdf($order[0]['custmail'], strip_tags($ftitle)." ".JText::_('VRRENTALORD'), $ftitle, $nowts, $order[0]['custdata'], $carinfo['name'], $order[0]['ritiro'], $order[0]['consegna'], $pricestr, $optstr, $isdue, $viklink, $saystatus, $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $order[0]['id'], $order[0]['coupon'], $arrayinfopdf);
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$oid);
	}

	function sortlocation() {
		$cid = VikRequest::getVar('cid', array(0));
		$sortid = (int)$cid[0];
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentcar_places` ORDER BY `#__vikrentcar_places`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering'])==intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_places` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_places` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_places` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_places` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_places` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_places` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=places");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar");
		}
	}

	function geninvoices() {
		$ids = VikRequest::getVar('cid', array(0));
		$mainframe = JFactory::getApplication();
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			require_once(VRC_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php');
			if (file_exists(VRC_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . "fonts" . DS . "dejavusans.php")) {
				$usepdffont = 'dejavusans';
			} else {
				$usepdffont = 'helvetica';
			}
			$pinvoice_num = VikRequest::getInt('invoice_num', '', 'request');
			$pinvoice_num = $pinvoice_num <= 0 ? 1 : $pinvoice_num;
			$pinvoice_suff = VikRequest::getString('invoice_suff', '', 'request');
			$pinvoice_date = VikRequest::getString('invoice_date', '', 'request');
			$pcompany_info = VikRequest::getString('company_info', '', 'request', VIKREQUEST_ALLOWHTML);
			$pinvoice_send = VikRequest::getString('invoice_send', '', 'request');
			$pinvoice_send = $pinvoice_send == '1' ? 1 : 0;
			$nowdf = VikRentCar::getDateFormat(true);
			$nowtf = VikRentCar::getTimeFormat(true);
			$tf = $nowtf;
			if ($nowdf == "%d/%m/%Y") {
				$df = 'd/m/Y';
			} elseif ($nowdf == "%m/%d/%Y") {
				$df = 'm/d/Y';
			} else {
				$df = 'Y/m/d';
			}
			$today = date($df);
			$admail = VikRentCar::getAdminMail();
			$currencyname = VikRentCar::getCurrencyName();
			$companylogo = VikRentCar::getSiteLogo();
			$uselogo = '';
			if (!empty($companylogo)) {
				$uselogo = '<img src="'.VRC_ADMIN_URI.'resources/'.$companylogo.'"/>';
			}
			$totinvgen = 0;
			sort($ids);
			$vrc_tn = VikRentCar::getTranslator();
			foreach ($ids as $oid) {
				$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=".(int)$oid." AND `status`='confirmed';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$order = $dbo->loadAssocList();
					$isdue = 0;
					$descriptions = array();
					$netprices = array();
					$taxes = array();
					//check if the language in use is the same as the one used during the checkout
					if (!empty($order[0]['lang'])) {
						$lang = JFactory::getLanguage();
						if ($lang->getTag() != $order[0]['lang']) {
							$lang->load('com_vikrentcar', JPATH_ADMINISTRATOR, $order[0]['lang'], true);
							$vrc_tn::$force_tolang = $order[0]['lang'];
						}
					}
					//
					$car = VikRentCar::getCarInfo($order[0]['idcar']);
					$is_cust_cost = (!empty($order[0]['cust_cost']) && $order[0]['cust_cost'] > 0);
					if (!empty($order[0]['idtar'])) {
						if ($order[0]['hourly'] == 1) {
							$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `id`='".$order[0]['idtar']."';";
						} else {
							$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$order[0]['idtar']."';";
						}
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 0) {
							if ($order[0]['hourly'] == 1) {
								$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$order[0]['idtar']."';";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() == 1) {
									$tar = $dbo->loadAssocList();
								}
							}
						} else {
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
					if ($order[0]['hourly'] == 1 && !empty($tar[0]['hours'])) {
						foreach ($tar as $kt => $vt) {
							$tar[$kt]['days'] = 1;
						}
					}
					$checkhourscharges = 0;
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
					$ritplace=(!empty($order[0]['idplace']) ? VikRentCar::getPlaceName($order[0]['idplace']) : "");
					$consegnaplace=(!empty($order[0]['idreturnplace']) ? VikRentCar::getPlaceName($order[0]['idreturnplace']) : "");
					$costplusiva = $is_cust_cost ? $tar[0]['cost'] : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
					$costminusiva = $is_cust_cost ? VikRentCar::sayCustCostMinusIva($tar[0]['cost'], $order[0]['cust_idiva']) : VikRentCar::sayCostMinusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
					$pricestr = JText::sprintf('VRCINVDESCRCONT', $car['name'], date($df.' '.$tf, $order[0]['ritiro']))."\n";
					$pricestr .= ($is_cust_cost ? JText::_('VRCRENTCUSTRATEPLAN') : VikRentCar::getPriceName($tar[0]['idprice'])).(!empty($tar[0]['attrdata']) ? "\n".VikRentCar::getPriceAttr($tar[0]['idprice']).": ".$tar[0]['attrdata'] : "");
					//description
					$descriptions[] = nl2br(rtrim($pricestr, "\n"));
					//Prices
					$netprices[] = $costminusiva;
					$taxes[] = ($costplusiva - $costminusiva);
					$isdue = $costplusiva;
					//Options
					if (!empty($order[0]['optionals'])) {
						$stepo=explode(";", $order[0]['optionals']);
						foreach ($stepo as $oo) {
							if (!empty($oo)) {
								$stept=explode(":", $oo);
								$q = "SELECT `name`,`cost`,`perday`,`hmany`,`idiva`,`maxprice` FROM `#__vikrentcar_optionals` WHERE `id`=".intval($stept[0]).";";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() == 1) {
									$actopt = $dbo->loadAssocList();
									$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
									$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days']) : $actopt[0]['cost'];
									if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
										$realcost = $actopt[0]['maxprice'];
										if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
											$realcost = $actopt[0]['maxprice'] * $stept[1];
										}
									}
									$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva'], $order[0]);
									$isdue += $tmpopr;
									$optnetprice = VikRentCar::sayOptionalsMinusIva($realcost, $actopt[0]['idiva'], $order[0]);
									$descriptions[] = ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name'].": ".$tmpopr;
									$netprices[] = $optnetprice;
									$taxes[] = ($tmpopr - $optnetprice);
								}
							}
						}
					}
					//Location Fees
					if (!empty($order[0]['idplace']) && !empty($order[0]['idreturnplace'])) {
						$locfee=VikRentCar::getLocFee($order[0]['idplace'], $order[0]['idreturnplace']);
						if ($locfee) {
							if (strlen($locfee['losoverride']) > 0) {
								$arrvaloverrides = array();
								$valovrparts = explode('_', $locfee['losoverride']);
								foreach ($valovrparts as $valovr) {
									if (!empty($valovr)) {
										$ovrinfo = explode(':', $valovr);
										$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
									}
								}
								if (array_key_exists($order[0]['days'], $arrvaloverrides)) {
									$locfee['cost'] = $arrvaloverrides[$order[0]['days']];
								}
							}
							$locfeecost=intval($locfee['daily']) == 1 ? ($locfee['cost'] * $order[0]['days']) : $locfee['cost'];
							$locfeewith=VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $order[0]);
							$isdue+=$locfeewith;
							$locfeewithouttax = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $order[0]);
							$descriptions[] = JText::_('VRLOCFEETOPAY');
							$netprices[] = $locfeewithouttax;
							$taxes[] = ($locfeewith - $locfeewithouttax);
						}
					}
					//Out of Hours Fees
					$oohfee = VikRentCar::getOutOfHoursFees($order[0]['idplace'], $order[0]['idreturnplace'], $order[0]['ritiro'], $order[0]['consegna'], array('id' => $order[0]['idcar']));
					if (count($oohfee) > 0) {
						$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
						$isdue += $oohfeewith;
						$oohfeewithouttax = VikRentCar::sayOohFeeMinusIva($oohfee['cost'], $oohfee['idiva']);
						$mailoohfee = $oohfeewith;
						$descriptions[] = JText::_('VRCOOHFEEAMOUNT');
						$netprices[] = $oohfeewithouttax;
						$taxes[] = ($oohfeewith - $oohfeewithouttax);
					}
					//custom extra costs
					if (!empty($order[0]['extracosts'])) {
						$cur_extra_costs = json_decode($order[0]['extracosts'], true);
						foreach ($cur_extra_costs as $eck => $ecv) {
							$efee_cost = VikRentCar::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $order[0]);
							$isdue += $efee_cost;
							$efee_cost_without = VikRentCar::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $order[0]);
							$descriptions[] = $ecv['name'];
							$netprices[] = $efee_cost_without;
							$taxes[] = ($efee_cost - $efee_cost_without);
						}
					}
					//
					//date
					$usedate = $pinvoice_date == '0' ? date($df, $order[0]['ts']) : $today;
					//compose body
					list($invoicetpl, $pdfparams) = VikRentCar::loadInvoiceTmpl($order[0]);
					$hbody = VikRentCar::parseInvoiceTemplate($invoicetpl, $order[0], $car, array('currencyname' => $currencyname, 'company_logo' => $uselogo, 'company_info' => nl2br($pcompany_info), 'invoice_number' => $pinvoice_num, 'invoice_suffix' => $pinvoice_suff, 'invoice_date' => $usedate, 'invoice_products_descriptions' => $descriptions, 'invoice_products_netprices' => $netprices, 'invoice_products_taxes' => $taxes, 'invoice_grandtotal' => $isdue));
					//generate PDF
					$pdffname = $order[0]['id'] . '_' . $order[0]['sid'] . '.pdf';
					$pathpdf = VRC_SITE_PATH . DS . "helpers" . DS . "invoices" . DS . "generated" . DS . $pdffname;
					if (file_exists($pathpdf)) @unlink($pathpdf);
					$pdf_page_format = is_array($pdfparams['pdf_page_format']) ? $pdfparams['pdf_page_format'] : constant($pdfparams['pdf_page_format']);
					$pdf = new TCPDF(constant($pdfparams['pdf_page_orientation']), constant($pdfparams['pdf_unit']), $pdf_page_format, true, 'UTF-8', false);
					$pdf->SetTitle(JText::_('VRCINVNUM').' '.$pinvoice_num);
					//Header for each page of the pdf
					if ($pdfparams['show_header'] == 1 && count($pdfparams['header_data']) > 0) {
						$pdf->SetHeaderData($pdfparams['header_data'][0], $pdfparams['header_data'][1], $pdfparams['header_data'][2], $pdfparams['header_data'][3], $pdfparams['header_data'][4], $pdfparams['header_data'][5]);
					}
					//Change some currencies to their unicode (decimal) value
					$unichr_map = array('EUR' => 8364, 'USD' => 36, 'AUD' => 36, 'CAD' => 36, 'GBP' => 163);
					if (array_key_exists($currencyname, $unichr_map)) {
						$hbody = str_replace($currencyname, $pdf->unichr($unichr_map[$currencyname]), $hbody);
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
					$pdf->AddPage();
					$pdf->writeHTML($hbody, true, false, true, false, '');
					$pdf->lastPage();
					$pdf->Output($pathpdf, 'F');
					if (file_exists($pathpdf)) {
						if ($pinvoice_send == 1) {
							//send invoice via email
							$vrc_app = new VrcApplication();
							$vrc_app->sendMail($admail, $admail, $order[0]['custmail'], $admail, JText::_('VRCINVMAILSUBJ'), JText::_('VRCINVMAILCONT'), true, 'base64', $pathpdf);
							unset($mailer);
						}
						$totinvgen++;
						$pinvoice_num++;
					}
				}
			}
			$mainframe->enqueueMessage(JText::sprintf('VRCTOTINVGEN', $totinvgen));
			//update values used
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='".($pinvoice_num - 1)."' WHERE `param`='invoiceinum';";
			$dbo->setQuery($q);
			$dbo->execute();
			$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pinvoice_suff)." WHERE `param`='invoicesuffix';";
			$dbo->setQuery($q);
			$dbo->execute();
			$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pcompany_info)." WHERE `param`='invcompanyinfo';";
			$dbo->setQuery($q);
			$dbo->execute();
			//
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=orders");
	}

	function loadpaymentparams() {
		//to be called via ajax
		$html = '---------';
		$phpfile = VikRequest::getString('phpfile', '', 'request');
		if (!empty($phpfile)) {
			$html = VikRentCar::displayPaymentParameters($phpfile);
		}
		echo $html;
		exit;
	}

	function translations() {
		VikRentCarHelper::printHeader("21");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'translations'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function savetranslation() {
		$this->do_savetranslation();
	}

	function savetranslationstay() {
		$this->do_savetranslation(true);
	}

	private function do_savetranslation($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$vrc_tn = VikRentCar::getTranslator();
		$table = VikRequest::getString('vrc_table', '', 'request');
		$cur_langtab = VikRequest::getString('vrc_lang', '', 'request');
		$langs = $vrc_tn->getLanguagesList();
		$xml_tables = $vrc_tn->getTranslationTables();
		if (!empty($table) && array_key_exists($table, $xml_tables)) {
			$tn = VikRequest::getVar('tn', array(), 'request', 'array', VIKREQUEST_ALLOWHTML);
			$tn_saved = 0;
			$table_cols = $vrc_tn->getTableColumns($table);
			$table = $vrc_tn->replacePrefix($table);
			foreach ($langs as $ltag => $lang) {
				if ($ltag == $vrc_tn->default_lang) {
					continue;
				}
				if (array_key_exists($ltag, $tn) && count($tn[$ltag]) > 0) {
					foreach ($tn[$ltag] as $reference_id => $translation) {
						$lang_translation = array();
						foreach ($table_cols as $field => $fdetails) {
							if (!array_key_exists($field, $translation)) {
								continue;
							}
							$ftype = $fdetails['type'];
							if ($ftype == 'skip') {
								continue;
							}
							if ($ftype == 'json') {
								$translation[$field] = json_encode($translation[$field]);
							}
							$lang_translation[$field] = $translation[$field];
						}
						if (count($lang_translation) > 0) {
							$q = "SELECT `id` FROM `#__vikrentcar_translations` WHERE `table`=".$dbo->quote($table)." AND `lang`=".$dbo->quote($ltag)." AND `reference_id`=".$dbo->quote((int)$reference_id).";";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() > 0) {
								$last_id = $dbo->loadResult();
								$q = "UPDATE `#__vikrentcar_translations` SET `content`=".$dbo->quote(json_encode($lang_translation))." WHERE `id`=".(int)$last_id.";";
							} else {
								$q = "INSERT INTO `#__vikrentcar_translations` (`table`,`lang`,`reference_id`,`content`) VALUES (".$dbo->quote($table).", ".$dbo->quote($ltag).", ".$dbo->quote((int)$reference_id).", ".$dbo->quote(json_encode($lang_translation)).");";
							}
							$dbo->setQuery($q);
							$dbo->execute();
							$tn_saved++;
						}
					}
				}
			}
			if ($tn_saved > 0) {
				$mainframe->enqueueMessage(JText::_('VRCTRANSLSAVEDOK'));
			}
		} else {
			VikError::raiseWarning('', JText::_('VRCTRANSLATIONERRINVTABLE'));
		}
		$mainframe->redirect("index.php?option=com_vikrentcar".($stay ? '&task=translations&vrc_table='.$vrc_tn->replacePrefix($table).'&vrc_lang='.$cur_langtab : ''));
	}

	function sortcategory() {
		$cid = VikRequest::getVar('cid', array(0));
		$sortid = (int)$cid[0];
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentcar_categories` ORDER BY `#__vikrentcar_categories`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_categories` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_categories` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_categories` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_categories` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_categories` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_categories` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=categories");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar");
		}
	}

	function edittmplfile() {
		//modal box, so we do not set menu or footer

		VikRequest::setVar('view', VikRequest::getCmd('view', 'edittmplfile'));
	
		parent::display();
	}

	function savetmplfile() {
		$fpath = VikRequest::getString('path', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcont = VikRequest::getString('cont', '', 'request', VIKREQUEST_ALLOWHTML);
		$mainframe = JFactory::getApplication();
		$exists = file_exists($fpath) ? true : false;
		if (!$exists) {
			$fpath = urldecode($fpath);
		}
		$fpath = file_exists($fpath) ? $fpath : '';
		if (!empty($fpath)) {
			$fp = fopen($fpath, 'wb');
			$byt = (int)fwrite($fp, $pcont);
			fclose($fp);
			if ($byt > 0) {
				$mainframe->enqueueMessage(JText::_('VRCUPDTMPLFILEOK'));
			} else {
				VikError::raiseWarning('', JText::_('VRCUPDTMPLFILENOBYTES'));
			}
		} else {
			VikError::raiseWarning('', JText::_('VRCUPDTMPLFILEERR'));
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=edittmplfile&path=".$fpath."&tmpl=component");

		exit;
	}

	function unlockrecords() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_tmplock` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar");
	}

	function graphs() {
		VikRentCarHelper::printHeader("22");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'graphs'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function choosebusy() {
		VikRentCarHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'choosebusy'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function orders() {
		VikRentCarHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'orders'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function removeorders() {
		$mainframe = JFactory::getApplication();
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			//$moveoldor=VikRentCar::saveOldOrders();
			foreach ($ids as $d) {
				$q = "SELECT `o`.*,`b`.`stop_sales` FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_busy` `b` ON `b`.`id`=`o`.`idbusy` WHERE `o`.`id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					if (!empty($rows[0]['idbusy'])) {
						$q = "DELETE FROM `#__vikrentcar_busy` WHERE `id`='".$rows[0]['idbusy']."';";
						$dbo->setQuery($q);
						$dbo->execute();
					}
					/*
					//Deprecated and removed since VRC 1.11
					if ($moveoldor && $rows[0]['stop_sales'] != 1) {
						$q = "INSERT INTO `#__vikrentcar_oldorders` (`tsdel`,`custdata`,`ts`,`status`,`idcar`,`days`,`ritiro`,`consegna`,`idtar`,`optionals`,`custmail`,`sid`,`idplace`,`idreturnplace`,`totpaid`,`hourly`,`coupon`) VALUES('".time()."',".$dbo->quote($rows[0]['custdata']).",'".$rows[0]['ts']."','".$rows[0]['status']."','".$rows[0]['idcar']."','".$rows[0]['days']."','".$rows[0]['ritiro']."','".$rows[0]['consegna']."','".$rows[0]['idtar']."','".$rows[0]['optionals']."','".$rows[0]['custmail']."','".$rows[0]['sid']."','".$rows[0]['idplace']."','".$rows[0]['idreturnplace']."','".$rows[0]['totpaid']."','".$rows[0]['hourly']."',".$dbo->quote($rows[0]['coupon']).");";
						$dbo->setQuery($q);
						$dbo->execute();
					}
					*/
					$q = "DELETE FROM `#__vikrentcar_tmplock` WHERE `idorder`=" . intval($rows[0]['id']) . ";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($rows[0]['status'] == 'cancelled') {
						$q = "DELETE FROM `#__vikrentcar_orders` WHERE `id`='".$rows[0]['id']."';";
					} else {
						$q = "UPDATE `#__vikrentcar_orders` SET `idbusy`=NULL,`status`='cancelled' WHERE `id`='".$rows[0]['id']."';";
					}
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			$mainframe->enqueueMessage(JText::_('VRMESSDELBUSY'));
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=orders");
	}

	function canceledorder() {
		$pgoto = VikRequest::getString('goto', '', 'request');
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=".($pgoto == 'overv' ? 'overv' : 'orders'));
	}

	function removebusy() {
		$mainframe = JFactory::getApplication();
		$pidbusy = VikRequest::getString('idbusy', '', 'request');
		$pidorder = VikRequest::getInt('idorder', '', 'request');
		$pidcar = VikRequest::getString('idcar', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$preturn = VikRequest::getString('return', '', 'request');
		if (!empty($pidorder) && !empty($pidcar)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `o`.*,`b`.`stop_sales` FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_busy` `b` ON `b`.`id`=`o`.`idbusy` WHERE `o`.`id`=".$dbo->quote($pidorder).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$ord = $dbo->loadAssocList();
				/*
				//Deprecated and removed since VRC 1.11
				if (VikRentCar::saveOldOrders() && $ord[0]['stop_sales'] != 1) {
					$q = "INSERT INTO `#__vikrentcar_oldorders` (`tsdel`,`custdata`,`ts`,`status`,`idcar`,`days`,`ritiro`,`consegna`,`idtar`,`optionals`,`custmail`,`sid`) VALUES('".time()."',".$dbo->quote($ord[0]['custdata']).",'".$ord[0]['ts']."','".$ord[0]['status']."','".$ord[0]['idcar']."','".$ord[0]['days']."','".$ord[0]['ritiro']."','".$ord[0]['consegna']."','".$ord[0]['idtar']."','".$ord[0]['optionals']."','".$ord[0]['custmail']."','".$ord[0]['sid']."');";
					$dbo->setQuery($q);
					$dbo->execute();
				}
				*/
				$q = "DELETE FROM `#__vikrentcar_tmplock` WHERE `idorder`=" . intval($ord[0]['id']) . ";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($ord[0]['status'] == 'cancelled') {
					$q = "DELETE FROM `#__vikrentcar_orders` WHERE `id`='".$ord[0]['id']."' LIMIT 1;";
				} else {
					$q = "UPDATE `#__vikrentcar_orders` SET `idbusy`=NULL,`status`='cancelled' WHERE `id`='".$ord[0]['id']."';";
				}
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VRMESSDELBUSY'));
			}
			if (!empty($pidbusy)) {
				$q = "DELETE FROM `#__vikrentcar_busy` WHERE `id`=".$dbo->quote($pidbusy)." LIMIT 1;";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		if ($preturn == 'order' && $ord[0]['status'] != 'cancelled') {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$pidorder);
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=".($pgoto == 'overv' ? 'overv' : 'orders'));
		}
	}

	function updatebusy() {
		$mainframe = JFactory::getApplication();
		$pidbusy = VikRequest::getString('idbusy', '', 'request');
		$pidorder = VikRequest::getInt('idorder', '', 'request');
		$preturn = VikRequest::getString('return', '', 'request');
		$porder_total = VikRequest::getString('order_total', '', 'request');
		$pnewidcar = VikRequest::getInt('newidcar', '', 'request');
		$pidplace = VikRequest::getInt('idplace', '', 'request');
		$pidreturnplace = VikRequest::getInt('idreturnplace', '', 'request');
		$ppickupdate = VikRequest::getString('pickupdate', '', 'request');
		$preleasedate = VikRequest::getString('releasedate', '', 'request');
		$ppickuph = VikRequest::getString('pickuph', '', 'request');
		$ppickupm = VikRequest::getString('pickupm', '', 'request');
		$preleaseh = VikRequest::getString('releaseh', '', 'request');
		$preleasem = VikRequest::getString('releasem', '', 'request');
		$pidcar = VikRequest::getString('idcar', '', 'request');
		$origidcar = $pidcar;
		if (!empty($pnewidcar) && $pnewidcar > 0) {
			$pidcar = $pnewidcar;
		}
		$pcustdata = VikRequest::getString('custdata', '', 'request');
		$pareprices = VikRequest::getString('areprices', '', 'request');
		$ppriceid = VikRequest::getInt('priceid', '', 'request');
		$ptotpaid = VikRequest::getString('totpaid', '', 'request');
		//VikRentCar 1.7
		$pstandbyquick = VikRequest::getString('standbyquick', '', 'request');
		$pstandbyquick = $pstandbyquick == "1" ? 1 : 0;
		$pnotifycust = VikRequest::getString('notifycust', '', 'request');
		$pnotifycust = $pnotifycust == "1" ? 1 : 0;
		//
		$pcust_cost = VikRequest::getFloat('cust_cost', '', 'request');
		$paliq = VikRequest::getInt('aliq', '', 'request');
		$pextracn = VikRequest::getVar('extracn', array());
		$pextracc = VikRequest::getVar('extracc', array());
		$pextractx = VikRequest::getVar('extractx', array());
		$isdue = 0;
		$dbo = JFactory::getDBO();
		$actnow = time();
		$nowdf = VikRentCar::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		if (!empty($pidorder)) {
			$first = VikRentCar::getDateTimestamp($ppickupdate, $ppickuph, $ppickupm);
			$second = VikRentCar::getDateTimestamp($preleasedate, $preleaseh, $preleasem);
			if ($second > $first) {
				$q = "SELECT `units` FROM `#__vikrentcar_cars` WHERE `id`=".$dbo->quote($pidcar).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$units=$dbo->loadResult();
				//vikrentcar 1.5
				$checkhourly = false;
				$hoursdiff = 0;
				$secdiff=$second - $first;
				$daysdiff=$secdiff / 86400;
				if (is_int($daysdiff)) {
					if ($daysdiff < 1) {
						$daysdiff=1;
					}
				} else {
					if ($daysdiff < 1) {
						$daysdiff=1;
						$checkhourly = true;
						$ophours = $secdiff / 3600;
						$hoursdiff = intval(round($ophours));
						if ($hoursdiff < 1) {
							$hoursdiff = 1;
						}
					} else {
						$sum=floor($daysdiff) * 86400;
						$newdiff=$secdiff - $sum;
						$maxhmore=VikRentCar::getHoursMoreRb() * 3600;
						if ($maxhmore >= $newdiff) {
							$daysdiff=floor($daysdiff);
						} else {
							$daysdiff=ceil($daysdiff);
						}
					}
				}
				$groupdays = VikRentCar::getGroupDays($first, $second, $daysdiff);
				$opertwounits = true;
				$check = "SELECT `id`,`ritiro`,`realback`,`stop_sales` FROM `#__vikrentcar_busy` WHERE `idcar`=" . (int)$pidcar . " AND `id`!=".$dbo->quote($pidbusy).";";
				$dbo->setQuery($check);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$busy = $dbo->loadAssocList();
					$opertwounits = VikRentCar::carBookable($pidcar, $units, $first, $second, $busy);
				}
				//
				if (VikRentCar::carNotLocked($pidcar, $units, $first, $second)) {
					if ($opertwounits) {
						$doup = false;
						//vikrentcar 1.5
						if ($checkhourly) {
							$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `idcar`='".$pidcar."' AND `hours`='".$hoursdiff."' AND `idprice`='".$ppriceid."';";
						} else {
							$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `idcar`='".$pidcar."' AND `days`='".$daysdiff."' AND `idprice`='".$ppriceid."';";
						}
						//
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$dispcost = $dbo->loadAssocList();
							//vikrentcar 1.5
							if ($checkhourly) {
								foreach ($dispcost as $kt => $vt) {
									$dispcost[$kt]['days'] = 1;
								}
							}
							$doup = true;
						} else {
							//there are no hourly prices
							if ($checkhourly) {
								$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `idcar`='".$pidcar."' AND `days`='".$daysdiff."' AND `idprice`='".$ppriceid."';";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() == 1) {
									$dispcost = $dbo->loadAssocList();
									$doup = true;
								}
							}
						}
						//VRC 1.11 Custom Rate
						$set_custom_rate = 0;
						if (!$doup && empty($ppriceid) && !empty($pcust_cost) && floatval($pcust_cost) > 0) {
							$doup = true;
							$set_custom_rate = $pcust_cost;
						}
						//
						if ($doup === true || intval($pidcar) != intval($origidcar)) {
							$realback = VikRentCar::getHoursCarAvail() * 3600;
							$realback += $second;
							if (!empty($pidbusy)) {
								$q = "UPDATE `#__vikrentcar_busy` SET `idcar`=".(int)$pidcar.",`ritiro`='".$first."', `consegna`='".$second."', `realback`='".$realback."' WHERE `id`=".$dbo->quote($pidbusy).";";
								$dbo->setQuery($q);
								$dbo->execute();
							}
							$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=".$pidorder.";";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$orderdata = $dbo->loadAssocList();
								$qfinal="UPDATE `#__vikrentcar_orders` SET `custdata`=".$dbo->quote($pcustdata).", `idcar`=".(int)$pidcar.", `days`='".$daysdiff."', `ritiro`='".$first."', `consegna`='".$second."'";
								if (is_array($dispcost) && $doup === true && empty($set_custom_rate)) {
									$qfinal .= ", `idtar`='".$dispcost[0]['id']."', `cust_cost`=NULL, `cust_idiva`=NULL";
									$dispcost = VikRentCar::applySeasonsCar($dispcost, $first, $second, $pidplace);
									$isdue += VikRentCar::sayCostPlusIva($dispcost[0]['cost'], $dispcost[0]['idprice']);
								} elseif ($set_custom_rate > 0) {
									$qfinal .= ", `idtar`=NULL, `cust_cost`=".$dbo->quote($set_custom_rate).(!empty($paliq) ? ", `cust_idiva`=".(int)$paliq : '');
									$isdue += $set_custom_rate;
								} elseif ($doup === false) {
									$qfinal .= ", `idtar`=NULL";
									if (intval($pidcar) != intval($origidcar)) {
										VikError::raiseNotice('', JText::_('VRCUPDBUSYCARSWITCHED'));
									}
								}
								$q = "SELECT * FROM `#__vikrentcar_optionals`;";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() > 0) {
									$toptionals = $dbo->loadAssocList();
									$wop = '';
									foreach ($toptionals as $opt) {
										$tmpvar = VikRequest::getString('optid'.$opt['id'], '', 'request');
										if (!empty($tmpvar)) {
											$wop .= $opt['id'].":".$tmpvar.";";
											$realcost = intval($opt['perday']) == 1 ? ($opt['cost'] * $daysdiff * $tmpvar) : ($opt['cost'] * $tmpvar);
											$basequancost = intval($opt['perday']) == 1 ? ($opt['cost'] * $daysdiff) : $opt['cost'];
											if (!empty($opt['maxprice']) && $opt['maxprice'] > 0 && $basequancost > $opt['maxprice']) {
												$realcost = $opt['maxprice'];
												if (intval($opt['hmany']) == 1 && intval($tmpvar) > 1) {
													$realcost = $opt['maxprice'] * $tmpvar;
												}
											}
											$isdue += VikRentCar::sayOptionalsPlusIva($realcost, $opt['idiva']);
										}
									}
									if (strlen($wop)) {
										$qfinal .= ", `optionals`='".$wop."'";
									}
								}
								if ($pidplace != $orderdata[0]['idplace']) {
									$qfinal .= ", `idplace`='".$pidplace."'";
								}
								if ($pidreturnplace != $orderdata[0]['idreturnplace']) {
									$qfinal .= ", `idreturnplace`='".$pidreturnplace."'";
								}
								if (strlen($ptotpaid) > 0) {
									$qfinal .= ", `totpaid`='".floatval($ptotpaid)."'";
								} else {
									$qfinal .= ", `totpaid`=null";
								}
								//calculate the extra costs and increase taxes + isdue
								$extracosts_arr = array();
								if (count($pextracn) > 0) {
									foreach ($pextracn as $eck => $ecn) {
										if (strlen($ecn) > 0 && array_key_exists($eck, $pextracc) && floatval($pextracc[$eck]) >= 0.00) {
											$ecidtax = array_key_exists($eck, $pextractx) && intval($pextractx[$eck]) > 0 ? (int)$pextractx[$eck] : '';
											$extracosts_arr[] = array('name' => $ecn, 'cost' => (float)$pextracc[$eck], 'idtax' => $ecidtax);
											$ecplustax = !empty($ecidtax) ? VikRentCar::sayOptionalsPlusIva((float)$pextracc[$eck], $ecidtax, $orderdata[0]) : (float)$pextracc[$eck];
											$isdue += $ecplustax;
										}
									}
								}
								if (count($extracosts_arr) > 0) {
									$qfinal .= ", `extracosts`=".$dbo->quote(json_encode($extracosts_arr));
								} else {
									$qfinal .= ", `extracosts`=NULL";
								}
								//end extra costs
								if (strlen($porder_total) > 0) {
									// the order total amount can be forced manually to a specific value
									$qfinal .= ", `order_total`='".floatval($porder_total)."'";
								} elseif ($isdue > 0) {
									// VRC 1.12 if no order total specified, update it to what the value would be at today's date
									$qfinal .= ", `order_total`='".floatval($isdue)."'";
								}
								$qfinal .= " WHERE `id`=".(int)$orderdata[0]['id'].";";
								$dbo->setQuery($qfinal);
								$dbo->execute();
								$mainframe->enqueueMessage(JText::_('RESUPDATED'));
								//VikRentCar 1.7
								if ($pstandbyquick == 1) {
									//remove busy because this is an order from quick reservation with standby status
									$q = "DELETE FROM `#__vikrentcar_busy` WHERE `id`=".(int)$pidbusy.";";
									$dbo->setQuery($q);
									$dbo->execute();
									$q = "UPDATE `#__vikrentcar_orders` SET `idbusy`=NULL WHERE `id`=".(int)$orderdata[0]['id'].";";
									$dbo->setQuery($q);
									$dbo->execute();
								}
								if ($pnotifycust == 1) {
									$this->resendordemail($orderdata[0]['id'], true);
									return;
								}
								//
							}
						}
					} else {
						VikError::raiseWarning('', JText::_('VRCARNOTRIT')." ".date($df.' H:i', $first)." ".JText::_('VRCARNOTCONSTO')." ".date($df.' H:i', $second));
					}
				} else {
					VikError::raiseWarning('', JText::_('ERRCARLOCKED'));
				}
			} else {
				VikError::raiseWarning('', JText::_('ERRPREV'));
			}
			if (intval($pidcar) != intval($origidcar)) {
				$mainframe->redirect("index.php?option=com_vikrentcar&task=editbusy&return=".$preturn."&cid[]=".$pidorder);
			} elseif ($preturn == 'order') {
				$mainframe->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$pidorder);
			} else {
				$mainframe->redirect("index.php?option=com_vikrentcar&task=calendar&cid[]=".$pidcar);
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=orders");
		}
	}

	function editorder() {
		VikRentCarHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'editorder'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editbusy() {
		VikRentCarHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'editbusy'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function checkversion() {
		$params = new stdClass;
		$params->version 	= E4J_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikrentcar';

		JPluginHelper::importPlugin('e4j');
		$dispatcher = JEventDispatcher::getInstance();

		$result = $dispatcher->trigger('checkVersion', array(&$params));

		if (!count($result)) {
			$result = new stdClass;
			$result->status = 0;
		} else {
			$result = $result[0];
		}

		echo json_encode($result);
		exit;
	}

	function updateprogram() {
		$params = new stdClass;
		$params->version 	= E4J_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikrentcar';

		JPluginHelper::importPlugin('e4j');
		$dispatcher = JEventDispatcher::getInstance();
		
		$result = $dispatcher->trigger('getVersionContents', array(&$params));

		if (!count($result) || !$result[0]) {
			$result = $dispatcher->trigger('checkVersion', array(&$params));
		}

		if (!count($result) || !$result[0]->status || !$result[0]->response->status) {
			exit('Error, plugin disabled');
		}

		JToolbarHelper::title(JText::_('VRMAINTITLEUPDATEPROGRAM'));

		VikRentCarHelper::pUpdateProgram($result[0]->response);
	}

	function updateprogramlaunch() {
		$params = new stdClass;
		$params->version 	= E4J_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikrentcar';

		JPluginHelper::importPlugin('e4j');
		$dispatcher = JEventDispatcher::getInstance();

		$json = new stdClass;
		$json->status = false;

		try {

			$result = $dispatcher->trigger('doUpdate', array(&$params));

			if ( count($result) ) {
				$json->status = (bool) $result[0];
			} else {
				$json->error = 'plugin disabled.';
			}

		} catch(Exception $e) {

			$json->status = false;
			$json->error = $e->getMessage();

		}

		echo json_encode($json);
		exit;
	}

	function customers() {
		VikRentCarHelper::printHeader("customers");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'customers'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newcustomer() {
		VikRentCarHelper::printHeader("customers");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomer'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editcustomer() {
		VikRentCarHelper::printHeader("customers");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomer'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function removecustomers() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			$cpin = VikRentCar::getCPinIstance();
			foreach ($ids as $d) {
				$cpin->pluginCustomerSync($d, 'delete');
				$q = "DELETE FROM `#__vikrentcar_customers` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=customers");
	}

	function savecustomer() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pfirst_name = VikRequest::getString('first_name', '', 'request');
		$plast_name = VikRequest::getString('last_name', '', 'request');
		$pcompany = VikRequest::getString('company', '', 'request');
		$pvat = VikRequest::getString('vat', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pphone = VikRequest::getString('phone', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$ppin = VikRequest::getString('pin', '', 'request');
		$pujid = VikRequest::getInt('ujid', '', 'request');
		$paddress = VikRequest::getString('address', '', 'request');
		$pcity = VikRequest::getString('city', '', 'request');
		$pzip = VikRequest::getString('zip', '', 'request');
		$pgender = VikRequest::getString('gender', '', 'request');
		$pgender = in_array($pgender, array('F', 'M')) ? $pgender : '';
		$pbdate = VikRequest::getString('bdate', '', 'request');
		$ppbirth = VikRequest::getString('pbirth', '', 'request');
		$pdoctype = VikRequest::getString('doctype', '', 'request');
		$pdocnum = VikRequest::getString('docnum', '', 'request');
		$pnotes = VikRequest::getString('notes', '', 'request');
		$pscandocimg = VikRequest::getString('scandocimg', '', 'request');
		$pischannel = VikRequest::getInt('ischannel', '', 'request');
		$pcommission = VikRequest::getFloat('commission', '', 'request');
		$pcalccmmon = VikRequest::getInt('calccmmon', '', 'request');
		$papplycmmon = VikRequest::getInt('applycmmon', '', 'request');
		$pchname = VikRequest::getString('chname', '', 'request');
		$pchcolor = VikRequest::getString('chcolor', '', 'request');
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$pbid = VikRequest::getInt('bid', '', 'request');
		if (!empty($pfirst_name) && !empty($plast_name)) {
			$cpin = VikRentCar::getCPinIstance();
			$q = "SELECT * FROM `#__vikrentcar_customers` WHERE `email`=".$dbo->quote($pemail)." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 0) {
				if (empty($ppin)) {
					$ppin = $cpin->generateUniquePin();
				} elseif ($cpin->pinExists($ppin)) {
					$ppin = $cpin->generateUniquePin();
				}
				//file upload
				$pimg = VikRequest::getVar('docimg', null, 'files', 'array');
				jimport('joomla.filesystem.file');
				$gimg = "";
				if (isset($pimg) && strlen(trim($pimg['name']))) {
					$filename = JFile::makeSafe(rand(100, 9999).str_replace(" ", "_", strtolower($pimg['name'])));
					$src = $pimg['tmp_name'];
					$dest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'idscans'.DIRECTORY_SEPARATOR;
					$j = "";
					if (file_exists($dest.$filename)) {
						$j = rand(171, 1717);
						while (file_exists($dest.$j.$filename)) {
							$j++;
						}
					}
					$finaldest = $dest.$j.$filename;
					$check = getimagesize($pimg['tmp_name']);
					if ($check[2] & imagetypes()) {
						if (VikRentCar::uploadFile($src, $finaldest)) {
							$gimg = $j.$filename;
						} else {
							VikError::raiseWarning('', 'Error while uploading image');
						}
					} else {
						VikError::raiseWarning('', 'Uploaded file is not an Image');
					}
				} elseif (!empty($pscandocimg)) {
					$gimg = $pscandocimg;
				}
				//
				$q = "INSERT INTO `#__vikrentcar_customers` (`first_name`,`last_name`,`email`,`phone`,`country`,`pin`,`ujid`,`address`,`city`,`zip`,`doctype`,`docnum`,`docimg`,`notes`,`company`,`vat`,`gender`,`bdate`,`pbirth`) VALUES(".$dbo->quote($pfirst_name).", ".$dbo->quote($plast_name).", ".$dbo->quote($pemail).", ".$dbo->quote($pphone).", ".$dbo->quote($pcountry).", ".$dbo->quote($ppin).", ".$dbo->quote($pujid).", ".$dbo->quote($paddress).", ".$dbo->quote($pcity).", ".$dbo->quote($pzip).", ".$dbo->quote($pdoctype).", ".$dbo->quote($pdocnum).", ".$dbo->quote($gimg).", ".$dbo->quote($pnotes).", ".$dbo->quote($pcompany).", ".$dbo->quote($pvat).", ".$dbo->quote($pgender).", ".$dbo->quote($pbdate).", ".$dbo->quote($ppbirth).");";
				$dbo->setQuery($q);
				$dbo->execute();
				$lid = $dbo->insertid();
				$cpin->pluginCustomerSync($lid, 'insert');
				if (!empty($lid)) {
					$mainframe->enqueueMessage(JText::_('VRCUSTOMERSAVED'));
				}
			} else {
				//email already exists
				$ex_customer = $dbo->loadAssoc();
				VikError::raiseWarning('', JText::_('VRERRCUSTOMEREMAILEXISTS').'<br/><a href="index.php?option=com_vikrentcar&task=editcustomer&cid[]='.$ex_customer['id'].'" target="_blank">'.$ex_customer['first_name'].' '.$ex_customer['last_name'].'</a>');
			}
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=customers");
	}

	function updatecustomer() {
		$this->do_updatecustomer();
	}

	function updatecustomerstay() {
		$this->do_updatecustomer(true);
	}

	private function do_updatecustomer($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pfirst_name = VikRequest::getString('first_name', '', 'request');
		$plast_name = VikRequest::getString('last_name', '', 'request');
		$pcompany = VikRequest::getString('company', '', 'request');
		$pvat = VikRequest::getString('vat', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pphone = VikRequest::getString('phone', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$ppin = VikRequest::getString('pin', '', 'request');
		$pujid = VikRequest::getInt('ujid', '', 'request');
		$paddress = VikRequest::getString('address', '', 'request');
		$pcity = VikRequest::getString('city', '', 'request');
		$pzip = VikRequest::getString('zip', '', 'request');
		$pgender = VikRequest::getString('gender', '', 'request');
		$pgender = in_array($pgender, array('F', 'M')) ? $pgender : '';
		$pbdate = VikRequest::getString('bdate', '', 'request');
		$ppbirth = VikRequest::getString('pbirth', '', 'request');
		$pdoctype = VikRequest::getString('doctype', '', 'request');
		$pdocnum = VikRequest::getString('docnum', '', 'request');
		$pnotes = VikRequest::getString('notes', '', 'request');
		$pscandocimg = VikRequest::getString('scandocimg', '', 'request');
		$pischannel = VikRequest::getInt('ischannel', '', 'request');
		$pcommission = VikRequest::getFloat('commission', '', 'request');
		$pcalccmmon = VikRequest::getInt('calccmmon', '', 'request');
		$papplycmmon = VikRequest::getInt('applycmmon', '', 'request');
		$pchname = VikRequest::getString('chname', '', 'request');
		$pchcolor = VikRequest::getString('chcolor', '', 'request');
		$pwhere = VikRequest::getInt('where', '', 'request');
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$pbid = VikRequest::getInt('bid', '', 'request');
		if (!empty($pwhere) && !empty($pfirst_name) && !empty($plast_name)) {
			$q = "SELECT * FROM `#__vikrentcar_customers` WHERE `id`=".(int)$pwhere." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$customer = $dbo->loadAssoc();
			} else {
				$mainframe->redirect("index.php?option=com_vikrentcar&task=customers");
				exit;
			}
			$q = "SELECT * FROM `#__vikrentcar_customers` WHERE `email`=".$dbo->quote($pemail)." AND `id`!=".(int)$pwhere." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 0) {
				$cpin = VikRentCar::getCPinIstance();
				if (empty($ppin)) {
					$ppin = $customer['pin'];
				} elseif ($cpin->pinExists($ppin, $customer['pin'])) {
					$ppin = $cpin->generateUniquePin();
				}
				//file upload
				$pimg = VikRequest::getVar('docimg', null, 'files', 'array');
				jimport('joomla.filesystem.file');
				$gimg = "";
				if (isset($pimg) && strlen(trim($pimg['name']))) {
					$filename = JFile::makeSafe(rand(100, 9999).str_replace(" ", "_", strtolower($pimg['name'])));
					$src = $pimg['tmp_name'];
					$dest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'idscans'.DIRECTORY_SEPARATOR;
					$j = "";
					if (file_exists($dest.$filename)) {
						$j = rand(171, 1717);
						while (file_exists($dest.$j.$filename)) {
							$j++;
						}
					}
					$finaldest = $dest.$j.$filename;
					$check = getimagesize($pimg['tmp_name']);
					if ($check[2] & imagetypes()) {
						if (VikRentCar::uploadFile($src, $finaldest)) {
							$gimg = $j.$filename;
						} else {
							VikError::raiseWarning('', 'Error while uploading image');
						}
					} else {
						VikError::raiseWarning('', 'Uploaded file is not an Image');
					}
				} elseif (!empty($pscandocimg)) {
					$gimg = $pscandocimg;
				}
				//
				$q = "UPDATE `#__vikrentcar_customers` SET `first_name`=".$dbo->quote($pfirst_name).",`last_name`=".$dbo->quote($plast_name).",`email`=".$dbo->quote($pemail).",`phone`=".$dbo->quote($pphone).",`country`=".$dbo->quote($pcountry).",`pin`=".$dbo->quote($ppin).",`ujid`=".$dbo->quote($pujid).",`address`=".$dbo->quote($paddress).",`city`=".$dbo->quote($pcity).",`zip`=".$dbo->quote($pzip).",`doctype`=".$dbo->quote($pdoctype).",`docnum`=".$dbo->quote($pdocnum).(!empty($gimg) ? ",`docimg`=".$dbo->quote($gimg) : "").",`notes`=".$dbo->quote($pnotes).",`company`=".$dbo->quote($pcompany).",`vat`=".$dbo->quote($pvat).",`gender`=".$dbo->quote($pgender).",`bdate`=".$dbo->quote($pbdate).",`pbirth`=".$dbo->quote($ppbirth)." WHERE `id`=".(int)$pwhere.";";
				$dbo->setQuery($q);
				$dbo->execute();
				$cpin->pluginCustomerSync($pwhere, 'update');
				$mainframe->enqueueMessage(JText::_('VRCUSTOMERSAVED'));
			} else {
				//email already exists
				$ex_customer = $dbo->loadAssoc();
				VikError::raiseWarning('', JText::_('VRERRCUSTOMEREMAILEXISTS').'<br/><a href="index.php?option=com_vikrentcar&task=editcustomer&cid[]='.$ex_customer['id'].'" target="_blank">'.$ex_customer['first_name'].' '.$ex_customer['last_name'].'</a>');
					$mainframe->redirect("index.php?option=com_vikrentcar&task=editcustomer&cid[]=".$pwhere);
					exit;
			}
		}
		if ($stay) {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editcustomer&cid[]=".$pwhere);
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=customers");
		}
	}

	function cancelcustomer() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=customers");
	}

	function searchcustomer() {
		//to be called via ajax
		$kw = VikRequest::getString('kw', '', 'request');
		$nopin = VikRequest::getInt('nopin', '', 'request');
		$cstring = '';
		if (strlen($kw) > 0) {
			$dbo = JFactory::getDBO();
			if ($nopin > 0) {
				//page all bookings
				$q = "SELECT * FROM `#__vikrentcar_customers` WHERE CONCAT_WS(' ', `first_name`, `last_name`) LIKE ".$dbo->quote("%".$kw."%")." OR `email` LIKE ".$dbo->quote("%".$kw."%")." ORDER BY `first_name` ASC LIMIT 30;";
			} else {
				//page calendar
				$q = "SELECT * FROM `#__vikrentcar_customers` WHERE CONCAT_WS(' ', `first_name`, `last_name`) LIKE ".$dbo->quote("%".$kw."%")." OR `email` LIKE ".$dbo->quote("%".$kw."%")." OR `pin` LIKE ".$dbo->quote("%".$kw."%")." ORDER BY `first_name` ASC;";
			}
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$customers = $dbo->loadAssocList();
				$cust_old_fields = array();
				$cstring_search = '';
				foreach ($customers as $k => $v) {
					$cstring_search .= '<div class="vrc-custsearchres-entry" data-custid="'.$v['id'].'" data-email="'.$v['email'].'" data-phone="'.addslashes($v['phone']).'" data-country="'.$v['country'].'" data-pin="'.$v['pin'].'" data-firstname="'.addslashes($v['first_name']).'" data-lastname="'.addslashes($v['last_name']).'">'."\n";
					$cstring_search .= '<span class="vrc-custsearchres-name hasTooltip" title="'.$v['email'].'">'.$v['first_name'].' '.$v['last_name'].'</span>'."\n";
					if (!($nopin > 0)) {
						$cstring_search .= '<span class="vrc-custsearchres-pin">'.$v['pin'].'</span>'."\n";
					}
					if (!empty($v['country'])) {
						if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$v['country'].'.png')) {
							$cstring_search .= '<span class="vrc-custsearchres-cflag"><img src="'.VRC_ADMIN_URI.'resources/countries/'.$v['country'].'.png'.'" title="'.$v['country'].'" class="vrc-country-flag"/></span>'."\n";
						}
					}
					$cstring_search .= '</div>'."\n";
					if (!empty($v['cfields'])) {
						$oldfields = json_decode($v['cfields'], true);
						if (is_array($oldfields) && count($oldfields)) {
							$cust_old_fields[$v['id']] = $oldfields;
						}
					}
				}
				$cstring = json_encode(array(($nopin > 0 ? '' : $cust_old_fields), $cstring_search));
			}
		}
		echo $cstring;
		exit;
	}

	function exportcustomers() {
		//we do not set the menu for this view
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'exportcustomers'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function exportcustomerslaunch() {
		$cid = VikRequest::getVar('cid', array(0));
		$dbo = JFactory::getDBO();
		$pnotes = VikRequest::getInt('notes', '', 'request');
		$pscanimg = VikRequest::getInt('scanimg', '', 'request');
		$ppin = VikRequest::getInt('pin', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		$pdatefilt = VikRequest::getInt('datefilt', '', 'request');
		$clauses = array();
		if (count($cid) > 0 && !empty($cid[0])) {
			$clauses[] = "`c`.`id` IN (".implode(', ', $cid).")";
		}
		if (!empty($pcountry)) {
			$clauses[] = "`c`.`country`=".$dbo->quote($pcountry);
		}
		$datescol = '`bk`.`ts`';
		if ($pdatefilt > 0) {
			if ($pdatefilt == 1) {
				$datescol = '`bk`.`ts`';
			} elseif ($pdatefilt == 2) {
				$datescol = '`bk`.`ritiro`';
			} elseif ($pdatefilt == 3) {
				$datescol = '`bk`.`consegna`';
			}
		}
		if (!empty($pfromdate)) {
			$from_ts = VikRentCar::getDateTimestamp($pfromdate, 0, 0);
			$clauses[] = $datescol.">=".$from_ts;
		}
		if (!empty($ptodate)) {
			$to_ts = VikRentCar::getDateTimestamp($ptodate, 23, 59);
			$clauses[] = $datescol."<=".$to_ts;
		}
		//this query below is safe with the error #1055 when sql_mode=only_full_group_by
		$q = "SELECT `c`.`id`,`c`.`first_name`,`c`.`last_name`,`c`.`email`,`c`.`phone`,`c`.`country`,`c`.`cfields`,`c`.`pin`,`c`.`ujid`,`c`.`address`,`c`.`city`,`c`.`zip`,`c`.`doctype`,`c`.`docnum`,`c`.`docimg`,`c`.`notes`,`c`.`company`,`c`.`vat`,`c`.`gender`,`c`.`bdate`,`c`.`pbirth`,".
			"(SELECT COUNT(*) FROM `#__vikrentcar_customers_orders` AS `co` WHERE `co`.`idcustomer`=`c`.`id`) AS `tot_bookings`,".
			"`cy`.`country_3_code`,`cy`.`country_name` ".
			"FROM `#__vikrentcar_customers` AS `c` LEFT JOIN `#__vikrentcar_countries` `cy` ON `cy`.`country_3_code`=`c`.`country` ".
			"LEFT JOIN `#__vikrentcar_customers_orders` `co` ON `co`.`idcustomer`=`c`.`id` ".
			"LEFT JOIN `#__vikrentcar_orders` `bk` ON `bk`.`id`=`co`.`idorder`".
			(count($clauses) > 0 ? " WHERE ".implode(' AND ', $clauses) : "")." 
			GROUP BY `c`.`id`,`c`.`first_name`,`c`.`last_name`,`c`.`email`,`c`.`phone`,`c`.`country`,`c`.`cfields`,`c`.`pin`,`c`.`ujid`,`c`.`address`,`c`.`city`,`c`.`zip`,`c`.`doctype`,`c`.`docnum`,`c`.`docimg`,`c`.`notes`,`c`.`company`,`c`.`vat`,`c`.`gender`,`c`.`bdate`,`c`.`pbirth`,`cy`.`country_3_code`,`cy`.`country_name` ".
			"ORDER BY `c`.`last_name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!($dbo->getNumRows() > 0)) {
			VikError::raiseWarning('', JText::_('VRCNORECORDSCSVCUSTOMERS'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikrentcar&task=customers");
			exit;
		}
		$customers = $dbo->loadAssocList();
		$csvlines = array();
		$csvheadline = array('ID', JText::_('VRCUSTOMERLASTNAME'), JText::_('VRCUSTOMERFIRSTNAME'), JText::_('VRCUSTOMEREMAIL'), JText::_('VRCUSTOMERPHONE'), JText::_('VRCUSTOMERADDRESS'), JText::_('VRCUSTOMERCITY'), JText::_('VRCUSTOMERZIP'), JText::_('VRCUSTOMERCOUNTRY'), JText::_('VRCUSTOMERTOTBOOKINGS'));
		if ($ppin > 0) {
			$csvheadline[] = JText::_('VRCUSTOMERPIN');
		}
		if ($pscanimg > 0) {
			$csvheadline[] = JText::_('VRCUSTOMERDOCTYPE');
			$csvheadline[] = JText::_('VRCUSTOMERDOCNUM');
			$csvheadline[] = JText::_('VRCUSTOMERDOCIMG');
		}
		if ($pnotes > 0) {
			$csvheadline[] = JText::_('VRCUSTOMERNOTES');
		}
		$csvlines[] = $csvheadline;
		foreach ($customers as $customer) {
			$csvcustomerline = array($customer['id'], $customer['last_name'], $customer['first_name'], $customer['email'], $customer['phone'], $customer['address'], $customer['city'], $customer['zip'], $customer['country_name'], $customer['tot_bookings']);
			if ($ppin > 0) {
				$csvcustomerline[] = $customer['pin'];
			}
			if ($pscanimg > 0) {
				$csvcustomerline[] = $customer['doctype'];
				$csvcustomerline[] = $customer['docnum'];
				$csvcustomerline[] = (!empty($customer['docimg']) ? VRC_ADMIN_URI.'resources/idscans/'.$customer['docimg'] : '');
			}
			if ($pnotes > 0) {
				$csvcustomerline[] = $customer['notes'];
			}	
			$csvlines[] = $csvcustomerline;
		}
		header("Content-type: text/csv");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="customers_export_'.(!empty($pcountry) ? strtolower($pcountry).'_' : '').date('Y-m-d').'.csv"');
		$outstream = fopen("php://output", 'w');
		foreach ($csvlines as $csvline) {
			fputcsv($outstream, $csvline);
		}
		fclose($outstream);
		exit;
	}

	function sendcustomemail() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$vrc_tn = VikRentCar::getTranslator();
		$pbid = VikRequest::getInt('bid', '', 'request');
		$pemailsubj = VikRequest::getString('emailsubj', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pemailcont = VikRequest::getString('emailcont', '', 'request', VIKREQUEST_ALLOWRAW);
		$pemailfrom = VikRequest::getString('emailfrom', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$pgoto = !empty($pgoto) ? urldecode($pgoto) : 'index.php?option=com_vikrentcar';
		if (!empty($pemail) && !empty($pemailcont)) {
			$email_attach = null;
			jimport('joomla.filesystem.file');
			$pemailattch = VikRequest::getVar('emailattch', null, 'files', 'array');
			if (isset($pemailattch) && strlen(trim($pemailattch['name']))) {
				$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pemailattch['name'])));
				$src = $pemailattch['tmp_name'];
				$dest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
				$j = "";
				if (file_exists($dest.$filename)) {
					$j = rand(171, 1717);
					while (file_exists($dest.$j.$filename)) {
						$j++;
					}
				}
				$finaldest = $dest.$j.$filename;
				if (VikRentCar::uploadFile($src, $finaldest)) {
					$email_attach = $finaldest;
				} else {
					VikError::raiseWarning('', 'Error uploading the attachment. Email not sent.');
					$mainframe->redirect($pgoto);
					exit;
				}
			}
			//VRC 1.12 - special tags for the custom email template files and messages
			$orig_mail_cont = $pemailcont;
			if (strpos($pemailcont, '{') !== false && strpos($pemailcont, '}') !== false) {
				$order = array();
				$q = "SELECT `o`.*,`co`.`idcustomer`,CONCAT_WS(' ',`c`.`first_name`,`c`.`last_name`) AS `customer_name`,`c`.`pin` AS `customer_pin`,`nat`.`country_name` FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_customers_orders` `co` ON `co`.`idorder`=`o`.`id` AND `co`.`idorder`=".(int)$pbid." LEFT JOIN `#__vikrentcar_customers` `c` ON `c`.`id`=`co`.`idcustomer` LEFT JOIN `#__vikrentcar_countries` `nat` ON `nat`.`country_3_code`=`o`.`country` WHERE `o`.`id`=".(int)$pbid.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$order = $dbo->loadAssoc();
				}
				// parse the special tokens to build the message
				$pemailcont = VikRentCar::parseSpecialTokens($order, $pemailcont);
			}
			//
			$is_html = (strpos($pemailcont, '<') !== false && strpos($pemailcont, '>') !== false);
			$pemailcont = $is_html ? nl2br($pemailcont) : $pemailcont;
			$vrc_app = new VrcApplication();
			$vrc_app->sendMail($pemailfrom, $pemailfrom, $pemail, $pemailfrom, $pemailsubj, $pemailcont, $is_html, 'base64', $email_attach);
			$mainframe->enqueueMessage(JText::_('VRSENDEMAILOK'));
			if ($email_attach !== null) {
				@unlink($email_attach);
			}
			//Save email template for future sending
			$config_rec_exists = false;
			$emtpl = array(
				'emailsubj' => $pemailsubj,
				'emailcont' => $orig_mail_cont,
				'emailfrom' => $pemailfrom
			);
			$cur_emtpl = array();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='customemailtpls';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$config_rec_exists = true;
				$cur_emtpl = $dbo->loadResult();
				$cur_emtpl = empty($cur_emtpl) ? array() : json_decode($cur_emtpl, true);
				$cur_emtpl = is_array($cur_emtpl) ? $cur_emtpl : array();
			}
			if (count($cur_emtpl) > 0) {
				$existing_subj = false;
				foreach ($cur_emtpl as $emk => $emv) {
					if (array_key_exists('emailsubj', $emv) && $emv['emailsubj'] == $emtpl['emailsubj']) {
						$cur_emtpl[$emk] = $emtpl;
						$existing_subj = true;
						break;
					}
				}
				if ($existing_subj === false) {
					$cur_emtpl[] = $emtpl;
				}
			} else {
				$cur_emtpl[] = $emtpl;
			}
			if (count($cur_emtpl) > 10) {
				//Max 10 templates to avoid problems with the size of the field and truncated json strings
				$exceed = count($cur_emtpl) - 10;
				for ($tl=0; $tl < $exceed; $tl++) { 
					unset($cur_emtpl[$tl]);
				}
				$cur_emtpl = array_values($cur_emtpl);
			}
			if ($config_rec_exists === true) {
				$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote(json_encode($cur_emtpl))." WHERE `param`='customemailtpls';";
				$dbo->setQuery($q);
				$dbo->execute();
			} else {
				$q = "INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('customemailtpls', ".$dbo->quote(json_encode($cur_emtpl)).");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			//
		} else {
			VikError::raiseWarning('', JText::_('VRSENDEMAILERRMISSDATA'));
		}
		$mainframe->redirect($pgoto);
	}

	function rmcustomemailtpl() {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = $cid[0];
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$tplind = VikRequest::getInt('tplind', '', 'request');
		if (empty($oid) || !(strlen($tplind) > 0)) {
			VikError::raiseWarning('', 'Missing Data.');
			$mainframe->redirect('index.php?option=com_vikrentcar');
			exit;
		}
		$cur_emtpl = array();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='customemailtpls';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cur_emtpl = $dbo->loadResult();
			$cur_emtpl = empty($cur_emtpl) ? array() : json_decode($cur_emtpl, true);
			$cur_emtpl = is_array($cur_emtpl) ? $cur_emtpl : array();
		} else {
			VikError::raiseWarning('', 'Missing Templates Record.');
			$mainframe->redirect('index.php?option=com_vikrentcar');
			exit;
		}
		if (array_key_exists($tplind, $cur_emtpl)) {
			unset($cur_emtpl[$tplind]);
			$cur_emtpl = count($cur_emtpl) > 0 ? array_values($cur_emtpl) : array();
			$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote(json_encode($cur_emtpl))." WHERE `param`='customemailtpls';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe->redirect('index.php?option=com_vikrentcar&task=editorder&cid[]='.$oid.'&customemail=1');
		exit;
	}

	function ratesoverv() {
		VikRentCarHelper::printHeader("ratesoverv");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'ratesoverv'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function restrictions() {
		VikRentCarHelper::printHeader("restrictions");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'restrictions'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function newrestriction() {
		VikRentCarHelper::printHeader("restrictions");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managerestriction'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function editrestriction() {
		VikRentCarHelper::printHeader("restrictions");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managerestriction'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	function createrestriction() {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$pname = VikRequest::getString('name', '', 'request');
		$pmonth = VikRequest::getInt('month', '', 'request');
		$pmonth = empty($pmonth) ? 0 : $pmonth;
		$pname = empty($pname) ? 'Restriction '.$pmonth : $pname;
		$pdfrom = VikRequest::getString('dfrom', '', 'request');
		$pdto = VikRequest::getString('dto', '', 'request');
		$pwday = VikRequest::getString('wday', '', 'request');
		$pwdaytwo = VikRequest::getString('wdaytwo', '', 'request');
		$pwdaytwo = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday == $pwdaytwo ? '' : $pwdaytwo;
		$pcomboa = VikRequest::getString('comboa', '', 'request');
		$pcomboa = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboa : '';
		$pcombob = VikRequest::getString('combob', '', 'request');
		$pcombob = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombob : '';
		$pcomboc = VikRequest::getString('comboc', '', 'request');
		$pcomboc = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboc : '';
		$pcombod = VikRequest::getString('combod', '', 'request');
		$pcombod = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombod : '';
		$combostr = '';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboa) ? $pcomboa.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombob) ? $pcombob.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboc) ? $pcomboc.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombod) ? $pcombod : '';
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pmaxlos = VikRequest::getInt('maxlos', '', 'request');
		$pmaxlos = empty($pmaxlos) ? 0 : $pmaxlos;
		$pmultiplyminlos = VikRequest::getString('multiplyminlos', '', 'request');
		$pmultiplyminlos = empty($pmultiplyminlos) ? 0 : 1;
		$pallcars = VikRequest::getString('allcars', '', 'request');
		$pallcars = $pallcars == "1" ? 1 : 0;
		$pidcars = VikRequest::getVar('idcars', array(0));
		$ridr = '';
		$caridsforsess = array();
		if (!empty($pidcars) && @count($pidcars) && $pallcars == 0) {
			foreach ($pidcars as $idr) {
				if (empty($idr)) {
					continue;
				}
				$ridr .= '-'.$idr.'-;';
				$caridsforsess[] = (int)$idr;
			}
		} elseif ($pallcars > 0) {
			$q = "SELECT `id` FROM `#__vikrentcar_cars`;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$fetchids = $dbo->loadAssocList();
				foreach ($fetchids as $fetchid) {
					$caridsforsess[] = (int)$fetchid['id'];
				}
			}
		}
		$pcta = VikRequest::getInt('cta', '', 'request');
		$pctd = VikRequest::getInt('ctd', '', 'request');
		$pctad = VikRequest::getVar('ctad', array());
		$pctdd = VikRequest::getVar('ctdd', array());
		if ($pminlos == 1 && strlen($pwday) == 0 && empty($pctad) && empty($pctdd)) {
			VikError::raiseWarning('', JText::_('VRUSELESSRESTRICTION'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikrentcar&task=newrestriction");
		} else {
			//check if there are restrictions for this month
			if ($pmonth > 0) {
				$q = "SELECT `id` FROM `#__vikrentcar_restrictions` WHERE `month`='".$pmonth."';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					VikError::raiseWarning('', JText::_('VRRESTRICTIONMONTHEXISTS'));
					$mainframe = JFactory::getApplication();
					$mainframe->redirect("index.php?option=com_vikrentcar&task=newrestriction");
				}
				$pdfrom = 0;
				$pdto = 0;
			} else {
				//dates range
				if (empty($pdfrom) || empty($pdto)) {
					VikError::raiseWarning('', JText::_('VRRESTRICTIONERRDRANGE'));
					$mainframe = JFactory::getApplication();
					$mainframe->redirect("index.php?option=com_vikrentcar&task=newrestriction");
				} else {
					$pdfrom = VikRentCar::getDateTimestamp($pdfrom, 0, 0);
					$pdto = VikRentCar::getDateTimestamp($pdto, 0, 0);
				}
			}
			//CTA and CTD
			$setcta = array();
			$setctd = array();
			if ($pcta > 0 && count($pctad) > 0) {
				foreach ($pctad as $ctwd) {
					if (strlen($ctwd)) {
						$setcta[] = '-'.(int)$ctwd.'-';
					}
				}
			}
			if ($pctd > 0 && count($pctdd) > 0) {
				foreach ($pctdd as $ctwd) {
					if (strlen($ctwd)) {
						$setctd[] = '-'.(int)$ctwd.'-';
					}
				}
			}
			//
			$q = "INSERT INTO `#__vikrentcar_restrictions` (`name`,`month`,`wday`,`minlos`,`multiplyminlos`,`maxlos`,`dfrom`,`dto`,`wdaytwo`,`wdaycombo`,`allcars`,`idcars`,`ctad`,`ctdd`) VALUES(".$dbo->quote($pname).", '".$pmonth."', ".(strlen($pwday) > 0 ? "'".$pwday."'" : "NULL").", '".$pminlos."', '".$pmultiplyminlos."', '".$pmaxlos."', ".$pdfrom.", ".$pdto.", ".(strlen($pwday) > 0 && strlen($pwdaytwo) > 0 ? intval($pwdaytwo) : "NULL").", ".(strlen($combostr) > 0 ? $dbo->quote($combostr) : "NULL").", ".$pallcars.", ".(strlen($ridr) > 0 ? $dbo->quote($ridr) : "NULL").", ".(count($setcta) > 0 ? $dbo->quote(implode(',', $setcta)) : "NULL").", ".(count($setctd) > 0 ? $dbo->quote(implode(',', $setctd)) : "NULL").");";
			$dbo->setQuery($q);
			$dbo->execute();
			$lid = $dbo->insertid();
			if (!empty($lid)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('VRRESTRICTIONSAVED'));
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikrentcar&task=restrictions");
			} else {
				VikError::raiseWarning('', 'Error while saving');
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikrentcar&task=newrestriction");
			}
		}
	}

	function updaterestriction() {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$pwhere = VikRequest::getInt('where', '', 'request');
		$pname = VikRequest::getString('name', '', 'request');
		$pmonth = VikRequest::getInt('month', '', 'request');
		$pmonth = empty($pmonth) ? 0 : $pmonth;
		$pname = empty($pname) ? 'Restriction '.$pmonth : $pname;
		$pdfrom = VikRequest::getString('dfrom', '', 'request');
		$pdto = VikRequest::getString('dto', '', 'request');
		$pwday = VikRequest::getString('wday', '', 'request');
		$pwdaytwo = VikRequest::getString('wdaytwo', '', 'request');
		$pwdaytwo = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday == $pwdaytwo ? '' : $pwdaytwo;
		$pcomboa = VikRequest::getString('comboa', '', 'request');
		$pcomboa = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboa : '';
		$pcombob = VikRequest::getString('combob', '', 'request');
		$pcombob = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombob : '';
		$pcomboc = VikRequest::getString('comboc', '', 'request');
		$pcomboc = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboc : '';
		$pcombod = VikRequest::getString('combod', '', 'request');
		$pcombod = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombod : '';
		$combostr = '';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboa) ? $pcomboa.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombob) ? $pcombob.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboc) ? $pcomboc.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombod) ? $pcombod : '';
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pmaxlos = VikRequest::getInt('maxlos', '', 'request');
		$pmaxlos = empty($pmaxlos) ? 0 : $pmaxlos;
		$pmultiplyminlos = VikRequest::getString('multiplyminlos', '', 'request');
		$pmultiplyminlos = empty($pmultiplyminlos) ? 0 : 1;
		$pallcars = VikRequest::getString('allcars', '', 'request');
		$pallcars = $pallcars == "1" ? 1 : 0;
		$pidcars = VikRequest::getVar('idcars', array(0));
		$ridr = '';
		$caridsforsess = array();
		if (!empty($pidcars) && @count($pidcars) && $pallcars == 0) {
			foreach ($pidcars as $idr) {
				if (empty($idr)) {
					continue;
				}
				$ridr .= '-'.$idr.'-;';
				$caridsforsess[] = (int)$idr;
			}
		} elseif ($pallcars > 0) {
			$q = "SELECT `id` FROM `#__vikrentcar_cars`;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$fetchids = $dbo->loadAssocList();
				foreach ($fetchids as $fetchid) {
					$caridsforsess[] = (int)$fetchid['id'];
				}
			}
		}
		$pcta = VikRequest::getInt('cta', '', 'request');
		$pctd = VikRequest::getInt('ctd', '', 'request');
		$pctad = VikRequest::getVar('ctad', array());
		$pctdd = VikRequest::getVar('ctdd', array());
		if ($pminlos == 1 && strlen($pwday) == 0 && empty($pctad) && empty($pctdd)) {
			VikError::raiseWarning('', JText::_('VRUSELESSRESTRICTION'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editrestriction&cid[]=".$pwhere);
		} else {
			//check if there are restrictions for this month
			if ($pmonth > 0) {
				$q = "SELECT `id` FROM `#__vikrentcar_restrictions` WHERE `month`='".$pmonth."' AND `id`!='".$pwhere."';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					VikError::raiseWarning('', JText::_('VRRESTRICTIONMONTHEXISTS'));
					$mainframe = JFactory::getApplication();
					$mainframe->redirect("index.php?option=com_vikrentcar&task=editrestriction&cid[]=".$pwhere);
				}
				$pdfrom = 0;
				$pdto = 0;
			} else {
				//dates range
				if (empty($pdfrom) || empty($pdto)) {
					VikError::raiseWarning('', JText::_('VRRESTRICTIONERRDRANGE'));
					$mainframe = JFactory::getApplication();
					$mainframe->redirect("index.php?option=com_vikrentcar&task=editrestriction&cid[]=".$pwhere);
				} else {
					$pdfrom = VikRentCar::getDateTimestamp($pdfrom, 0, 0);
					$pdto = VikRentCar::getDateTimestamp($pdto, 0, 0);
				}
			}
			//CTA and CTD
			$setcta = array();
			$setctd = array();
			if ($pcta > 0 && count($pctad) > 0) {
				foreach ($pctad as $ctwd) {
					if (strlen($ctwd)) {
						$setcta[] = '-'.(int)$ctwd.'-';
					}
				}
			}
			if ($pctd > 0 && count($pctdd) > 0) {
				foreach ($pctdd as $ctwd) {
					if (strlen($ctwd)) {
						$setctd[] = '-'.(int)$ctwd.'-';
					}
				}
			}
			//
			$q = "UPDATE `#__vikrentcar_restrictions` SET `name`=".$dbo->quote($pname).",`month`='".$pmonth."',`wday`=".(strlen($pwday) > 0 ? "'".$pwday."'" : "NULL").",`minlos`='".$pminlos."',`multiplyminlos`='".$pmultiplyminlos."',`maxlos`='".$pmaxlos."',`dfrom`=".$pdfrom.",`dto`=".$pdto.",`wdaytwo`=".(strlen($pwday) > 0 && strlen($pwdaytwo) > 0 ? intval($pwdaytwo) : "NULL").",`wdaycombo`=".(strlen($combostr) > 0 ? $dbo->quote($combostr) : "NULL").",`allcars`=".$pallcars.",`idcars`=".(strlen($ridr) > 0 ? $dbo->quote($ridr) : "NULL").", `ctad`=".(count($setcta) > 0 ? $dbo->quote(implode(',', $setcta)) : "NULL").", `ctdd`=".(count($setctd) > 0 ? $dbo->quote(implode(',', $setctd)) : "NULL")." WHERE `id`='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('VRRESTRICTIONSAVED'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikrentcar&task=restrictions");
		}
	}

	function removerestrictions() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_restrictions` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=restrictions");
	}

	function cancelrestriction() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=restrictions");
	}

	function setnewrates() {
		$dbo = JFactory::getDBO();
		$currencysymb = VikRentCar::getCurrencySymb();
		$vrc_df = VikRentCar::getDateFormat();
		$df = $vrc_df == "%d/%m/%Y" ? 'd/m/Y' : ($vrc_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
		$pcheckinh = 0;
		$pcheckinm = 0;
		$pcheckouth = 0;
		$pcheckoutm = 0;
		$timeopst = VikRentCar::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikRentCar::getHoursMinutes($timeopst[0]);
			$closet = VikRentCar::getHoursMinutes($timeopst[1]);
			$pcheckinh = $opent[0];
			$pcheckinm = $opent[1];
			// set drop off time equal to pick up time to avoid getting extra days of rent
			$pcheckouth = $pcheckinh;
			$pcheckoutm = $pcheckinm;
		}
		$pid_car = VikRequest::getInt('id_car', '', 'request');
		$pid_price = VikRequest::getInt('id_price', '', 'request');
		$prate = VikRequest::getString('rate', '', 'request');
		$prate = (float)$prate;
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		if (empty($pid_car) || empty($pid_price) || empty($prate) || !($prate > 0) || empty($pfromdate) || empty($ptodate)) {
			echo 'e4j.error.'.addslashes(JText::_('VRRATESOVWERRNEWRATE'));
			exit;
		}
		$carrates = array();
		//read the rates for the lowest number of nights
		//the old query below used to cause an error #1055 when sql_mode=only_full_group_by
		//$q = "SELECT `r`.*,`p`.`name` FROM `#__vikrentcar_dispcost` AS `r` INNER JOIN (SELECT MIN(`days`) AS `min_days` FROM `#__vikrentcar_dispcost` WHERE `idcar`=".(int)$pid_car." AND `idprice`=".(int)$pid_price." GROUP BY `idcar`) AS `r2` ON `r`.`days`=`r2`.`min_days` LEFT JOIN `#__vikrentcar_prices` `p` ON `p`.`id`=`r`.`idprice` AND `p`.`id`=".(int)$pid_price." WHERE `r`.`idcar`=".(int)$pid_car." AND `r`.`idprice`=".(int)$pid_price." GROUP BY `r`.`idprice` ORDER BY `r`.`days` ASC, `r`.`cost` ASC;";
		$q = "SELECT `r`.`id`,`r`.`idcar`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` FROM `#__vikrentcar_dispcost` AS `r` INNER JOIN (SELECT MIN(`days`) AS `min_days` FROM `#__vikrentcar_dispcost` WHERE `idcar`=".(int)$pid_car." AND `idprice`=".(int)$pid_price." GROUP BY `idcar`) AS `r2` ON `r`.`days`=`r2`.`min_days` LEFT JOIN `#__vikrentcar_prices` `p` ON `p`.`id`=`r`.`idprice` AND `p`.`id`=".(int)$pid_price." WHERE `r`.`idcar`=".(int)$pid_car." AND `r`.`idprice`=".(int)$pid_price." GROUP BY `r`.`id`,`r`.`idcar`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` ORDER BY `r`.`days` ASC, `r`.`cost` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$carrates = $dbo->loadAssocList();
			foreach ($carrates as $rrk => $rrv) {
				$carrates[$rrk]['cost'] = round(($rrv['cost'] / $rrv['days']), 2);
				$carrates[$rrk]['days'] = 1;
			}
		}
		//
		if (!(count($carrates) > 0)) {
			echo 'e4j.error.'.addslashes(JText::_('VRRATESOVWERRNORATES'));
			exit;
		}
		$carrates = $carrates[0];
		$current_rates = array();
		$start_ts = strtotime($pfromdate);
		$end_ts = strtotime($ptodate);
		$infostart = getdate($start_ts);
		while ($infostart[0] > 0 && $infostart[0] <= $end_ts) {
			$today_tsin = VikRentCar::getDateTimestamp(date($df, $infostart[0]), $pcheckinh, $pcheckinm);
			$today_tsout = VikRentCar::getDateTimestamp(date($df, mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year'])), $pcheckouth, $pcheckoutm);

			$tars = VikRentCar::applySeasonsCar(array($carrates), $today_tsin, $today_tsout);
			$current_rates[(date('Y-m-d', $infostart[0]))] = $tars[0];

			$infostart = getdate(mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']));
		}
		if (!(count($current_rates) > 0)) {
			echo 'e4j.error.'.addslashes(JText::_('VRRATESOVWERRNORATES').'.');
			exit;
		}
		$all_days = array_keys($current_rates);
		$season_intervals = array();
		$firstind = 0;
		$firstdaycost = $current_rates[$all_days[0]]['cost'];
		$nextdaycost = false;
		for ($i=1; $i < count($all_days); $i++) {
			$ind = $all_days[$i];
			$nextdaycost = $current_rates[$ind]['cost'];
			if ($firstdaycost != $nextdaycost) {
				$interval = array(
					'from' => $all_days[$firstind],
					'to' => $all_days[($i - 1)],
					'cost' => $firstdaycost
				);
				$season_intervals[] = $interval;
				$firstdaycost = $nextdaycost;
				$firstind = $i;
			}
		}
		if ($nextdaycost === false) {
			$interval = array(
				'from' => $all_days[$firstind],
				'to' => $all_days[$firstind],
				'cost' => $firstdaycost
			);
			$season_intervals[] = $interval;
		} elseif ($firstdaycost == $nextdaycost) {
			$interval = array(
				'from' => $all_days[$firstind],
				'to' => $all_days[($i - 1)],
				'cost' => $firstdaycost
			);
			$season_intervals[] = $interval;
		}
		foreach ($season_intervals as $sik => $siv) {
			if ((float)$siv['cost'] == $prate) {
				unset($season_intervals[$sik]);
			}
		}
		if (!(count($season_intervals) > 0)) {
			echo 'e4j.error.'.addslashes(JText::_('VRRATESOVWERRNORATESMOD'));
			exit;
		}
		foreach ($season_intervals as $sik => $siv) {
			$first = strtotime($siv['from']);
			$second = strtotime($siv['to']);
			if ($second > 0 && $second == $first) {
				$second += 86399;
			}
			if (!($second > $first)) {
				unset($season_intervals[$sik]);
				continue;
			}
			$baseone = getdate($first);
			$basets = mktime(0, 0, 0, 1, 1, $baseone['year']);
			$sfrom = $baseone[0] - $basets;
			$basetwo = getdate($second);
			$basets = mktime(0, 0, 0, 1, 1, $basetwo['year']);
			$sto = $basetwo[0] - $basets;
			//check leap year
			if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
				$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
				if ($baseone[0] >= $leapts) {
					$sfrom -= 86400;
				}
			}
			if ($basetwo['year'] % 4 == 0 && ($basetwo['year'] % 100 != 0 || $basetwo['year'] % 400 == 0)) {
				$leapts = mktime(0, 0, 0, 2, 29, $basetwo['year']);
				if ($basetwo[0] >= $leapts) {
					$sto -= 86400;
				}
			}
			//end leap year
			$tieyear = $baseone['year'];
			$ptype = (float)$siv['cost'] > $prate ? "2" : "1";
			$pdiffcost = $ptype == "1" ? ($prate - (float)$siv['cost']) : ((float)$siv['cost'] - $prate);
			$roomstr = "-".$pid_car."-,";
			$pspname = date('Y-m-d H:i').' - '.substr($baseone['month'], 0, 3).' '.$baseone['mday'].($siv['from'] != $siv['to'] ? '/'.($baseone['month'] != $basetwo['month'] ? substr($basetwo['month'], 0, 3).' ' : '').$basetwo['mday'] : '');
			$pval_pcent = 1;
			$pricestr = "-".$pid_price."-,";
			$q = "INSERT INTO `#__vikrentcar_seasons` (`type`,`from`,`to`,`diffcost`,`idcars`,`spname`,`wdays`,`pickupincl`,`val_pcent`,`losoverride`,`roundmode`,`year`,`idprices`,`promo`,`promotxt`,`promodaysadv`) VALUES('".($ptype == "1" ? "1" : "2")."', ".$dbo->quote($sfrom).", ".$dbo->quote($sto).", ".$dbo->quote($pdiffcost).", ".$dbo->quote($roomstr).", ".$dbo->quote($pspname).", '', '0', '".$pval_pcent."', '', NULL, ".$tieyear.", ".$dbo->quote($pricestr).", 0, '', NULL);";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//prepare output by re-calculating the rates in real-time
		$current_rates = array();
		$start_ts = strtotime($pfromdate);
		$end_ts = strtotime($ptodate);
		$infostart = getdate($start_ts);
		while ($infostart[0] > 0 && $infostart[0] <= $end_ts) {
			$today_tsin = VikRentCar::getDateTimestamp(date($df, $infostart[0]), $pcheckinh, $pcheckinm);
			$today_tsout = VikRentCar::getDateTimestamp(date($df, mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year'])), $pcheckouth, $pcheckoutm);

			$tars = VikRentCar::applySeasonsCar(array($carrates), $today_tsin, $today_tsout);
			$indkey = $infostart['mday'].'-'.$infostart['mon'].'-'.$infostart['year'].'-'.$pid_price;
			$current_rates[$indkey] = $tars[0];

			$infostart = getdate(mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']));
		}
		
		$pdebug = VikRequest::getInt('e4j_debug', '', 'request');
		if ($pdebug == 1) {
			echo "e4j.error.\n".print_r($carrates, true)."\n";
			echo print_r($current_rates, true)."\n\n";
			echo print_r($season_intervals, true)."\n";
			echo $pid_car.' - '.$pid_price.' - '.$prate.' - '.$pfromdate.' - '.$ptodate."\n";
		}
		echo json_encode($current_rates);
		exit;
	}

	function modcarrateplans() {
		$dbo = JFactory::getDBO();
		$pid_car = VikRequest::getInt('id_car', '', 'request');
		$pid_price = VikRequest::getInt('id_price', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		if (empty($pid_car) || empty($pid_price) || empty($ptype) || empty($pfromdate) || empty($ptodate) || !(strtotime($pfromdate) > 0)  || !(strtotime($ptodate) > 0)) {
			echo 'e4j.error.'.addslashes(JText::_('VRRATESOVWERRMODRPLANS'));
			exit;
		}
		$price_record = array();
		$q = "SELECT * FROM `#__vikrentcar_prices` WHERE `id`=".$pid_price.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$price_record = $dbo->loadAssoc();
		}
		if (!count($price_record) > 0) {
			echo 'e4j.error.'.addslashes(JText::_('VRRATESOVWERRMODRPLANS')).'.';
			exit;
		}
		$current_closed = array();
		if (!empty($price_record['closingd'])) {
			$current_closed = json_decode($price_record['closingd'], true);
			if (!is_array($current_closed)) {
				$current_closed = array();
			}
		}
		$start_ts = strtotime($pfromdate);
		$end_ts = strtotime($ptodate);
		$infostart = getdate($start_ts);
		$all_days = array();
		$output = array();
		while ($infostart[0] > 0 && $infostart[0] <= $end_ts) {
			$all_days[] = date('Y-m-d', $infostart[0]);
			$indkey = $infostart['mday'].'-'.$infostart['mon'].'-'.$infostart['year'].'-'.$pid_price;
			$output[$indkey] = array();
			$infostart = getdate(mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']));
		}
		if ($ptype == 'close') {
			if (!array_key_exists($pid_car, $current_closed)) {
				$current_closed[$pid_car] = array();
			}
			foreach ($all_days as $daymod) {
				if (!in_array($daymod, $current_closed[$pid_car])) {
					$current_closed[$pid_car][] = $daymod;
				}
			}
		} else {
			//open
			if (array_key_exists($pid_car, $current_closed)) {
				foreach ($all_days as $daymod) {
					if (in_array($daymod, $current_closed[$pid_car])) {
						foreach ($current_closed[$pid_car] as $ck => $cv) {
							if ($daymod == $cv) {
								unset($current_closed[$pid_car][$ck]);
							}
						}
					}
				}
			} else {
				$current_closed[$pid_car] = array();
			}
		}
		if (!count($current_closed[$pid_car]) > 0) {
			unset($current_closed[$pid_car]);
		}
		$q = "UPDATE `#__vikrentcar_prices` SET `closingd`=".(count($current_closed) > 0 ? $dbo->quote(json_encode($current_closed)) : "NULL")." WHERE `id`=".(int)$pid_price.";";
		$dbo->setQuery($q);
		$dbo->execute();
		$oldcsscls = $ptype == 'close' ? 'vrc-roverw-rplan-on' : 'vrc-roverw-rplan-off';
		$newcsscls = $ptype == 'close' ? 'vrc-roverw-rplan-off' : 'vrc-roverw-rplan-on';
		foreach ($output as $ok => $ov) {
			$output[$ok] = array('oldcls' => $oldcsscls, 'newcls' => $newcsscls);
		}
		
		$pdebug = VikRequest::getInt('e4j_debug', '', 'request');
		if ($pdebug == 1) {
			echo "e4j.error.\n".print_r($current_closed, true)."\n";
			echo print_r($output, true)."\n\n";
			echo print_r($all_days, true)."\n";
		}
		echo json_encode($output);
		exit;
	}

	function calc_rates() {
		$response = 'e4j.error.ErrorCode(1) Server is blocking the self-request';
		$currencysymb = VikRentCar::getCurrencySymb();
		$vrc_df = VikRentCar::getDateFormat();
		$df = $vrc_df == "%d/%m/%Y" ? 'd/m/Y' : ($vrc_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
		$pcheckinh = 0;
		$pcheckinm = 0;
		$pcheckouth = 0;
		$pcheckoutm = 0;
		$timeopst = VikRentCar::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikRentCar::getHoursMinutes($timeopst[0]);
			$closet = VikRentCar::getHoursMinutes($timeopst[1]);
			$pcheckinh = $opent[0];
			$pcheckinm = $opent[1];
			// set drop off time equal to pick up time to avoid getting extra days of rent
			$pcheckouth = $pcheckinh;
			$pcheckoutm = $pcheckinm;
		}
		$id_car = VikRequest::getInt('id_car', '', 'request');
		$pickup = VikRequest::getString('pickup', '', 'request');
		$days = VikRequest::getInt('num_days', 1, 'request');
		$pickup_ts = strtotime($pickup);
		if (empty($pickup_ts)) {
			$pickup = date('Y-m-d');
			$pickup_ts = strtotime($pickup);
		}
		$is_dst = date('I', $pickup_ts);
		$dropoff_ts = $pickup_ts;
		for ($i = 1; $i <= $days; $i++) { 
			$dropoff_ts += 86400;
			$is_now_dst = date('I', $dropoff_ts);
			if ($is_dst != $is_now_dst) {
				if ((int)$is_dst == 1) {
					$dropoff_ts += 3600;
				} else {
					$dropoff_ts -= 3600;
				}
				$is_dst = $is_now_dst;
			}
		}
		$checkout = date('Y-m-d', $dropoff_ts);
		if (function_exists('curl_init')) {
			$endpoint = JURI::root().'index.php?option=com_vikrentcar&task=search';
			$endpoint = 'http://2.39.160.3/joomla/j3/index.php?option=com_vikrentcar&task=search';
			$rates_data = 'e4jauth=%s&getjson=1&pickupdate='.date($df, $pickup_ts).'&pickuph='.$pcheckinh.'&pickupm='.$pcheckinm.'&releasedate='.date($df, $dropoff_ts).'&releaseh='.$pcheckouth.'&releasem='.$pcheckoutm;
			$ch = curl_init($endpoint);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, sprintf($rates_data, md5('vrc.e4j.vrc')));
			$cua = VikRequest::getString('HTTP_USER_AGENT', '', 'server');
			if (!empty($ua)) {
				curl_setopt($ch, CURLOPT_USERAGENT, $cua);
			}
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
			$res = curl_exec($ch);
			if ($curl_errno = curl_errno($ch)) {
				$response = "e4j.error.curl Error (".curl_errno($ch)."): ".curl_error($ch);
			} else {
				$arr_res = json_decode($res, true);
				if (is_array($arr_res)) {
					if (!array_key_exists('e4j.error', $arr_res)) {
						if (array_key_exists($id_car, $arr_res)) {
							$response = '';
							foreach ($arr_res[$id_car] as $rate) {
								$response .= '<div class="vrc-calcrates-rateblock">';
								$response .= '<span class="vrc-calcrates-ratename">'.$rate['pricename'].'</span>';
								$tot = $rate['cost'];
								$tot = round($tot, 2);
								$response .= '<span class="vrc-calcrates-ratetotal"><span>'.JText::_('VRCALCRATESTOT').'</span>'.$currencysymb.' '.VikRentCar::numberFormat($tot).'</span>';
								if (array_key_exists('affdays', $rate) && $rate['affdays'] > 0) {
									$response .= '<span class="vrc-calcrates-ratespaffdays"><span>'.JText::_('VRCALCRATESSPAFFDAYS').'</span>'.$rate['affdays'].'</span>';
								}
								$response .= '</div>';
							}
							//Debug
							//$response .= '<br/><pre>'.print_r($arr_res, true).'</pre><br/>';
						} else {
							$response = 'e4j.error.'.JText::sprintf('VRCALCRATESCARNOTAVAILCOMBO', date($df, $pickup_ts), date($df, $dropoff_ts));
						}
					} else {
						$response = 'e4j.error.'.$arr_res['e4j.error'];
					}
				} else {
					$response = (strpos($res, 'e4j.error') === false ? 'e4j.error' : '').$res;
				}
			}
			curl_close($ch);
		}
		
		// Do not do only echo trim($response); or the currency symbol may not be encoded on some servers
		echo json_encode(array(trim($response)));
		exit;
	}

}
