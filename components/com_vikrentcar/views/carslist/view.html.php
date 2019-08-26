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

class VikrentcarViewCarslist extends JViewVikRentCar {
	function display($tpl = null) {
		VikRentCar::prepareViewContent();
		$dbo = JFactory::getDBO();
		$vrc_tn = VikRentCar::getTranslator();
		$pcategory_id = VikRequest::getInt('category_id', '', 'request');
		$category = "";
		if ($pcategory_id > 0) {
			$q="SELECT * FROM `#__vikrentcar_categories` WHERE `id`='".$pcategory_id."';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$category = $dbo->loadAssocList();
				$category = $category[0];
				$vrc_tn->translateContents($category, '#__vikrentcar_categories');
			}
		}
		if (is_array($category)) {
			$q = "SELECT `id`,`name`,`img`,`idcat`,`idcarat`,`info`,`startfrom`,`short_info` FROM `#__vikrentcar_cars` WHERE `avail`='1' AND (`idcat`='".$category['id'].";' OR `idcat` LIKE '".$category['id'].";%' OR `idcat` LIKE '%;".$category['id'].";%' OR `idcat` LIKE '%;".$category['id'].";');";
		} else {
			$q = "SELECT `id`,`name`,`img`,`idcat`,`idcarat`,`info`,`startfrom`,`short_info` FROM `#__vikrentcar_cars` WHERE `avail`='1';";
		}
		$orderby = VikRequest::getString('orderby', 'price', 'request');
		$ordertype = VikRequest::getString('ordertype', 'asc', 'request');
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cars=$dbo->loadAssocList();
			$vrc_tn->translateContents($cars, '#__vikrentcar_cars');
			foreach($cars as $k=>$c) {
				if ($orderby == 'customprice') {
					$startfrom = floatval($c['startfrom']);
					if ($startfrom > 0) {
						$cars[$k]['cost'] = $startfrom;
						continue;
					}
				}
				$q="SELECT `id`,`cost` FROM `#__vikrentcar_dispcost` WHERE `idcar`=".$dbo->quote($c['id'])." AND `days`='1' ORDER BY `#__vikrentcar_dispcost`.`cost` ASC LIMIT 1;";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$tar=$dbo->loadAssocList();
					$cars[$k]['cost']=$tar[0]['cost'];
				} else {
					$q="SELECT `id`,`days`,`cost` FROM `#__vikrentcar_dispcost` WHERE `idcar`=".$dbo->quote($c['id'])." ORDER BY `#__vikrentcar_dispcost`.`cost` ASC LIMIT 1;";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() == 1) {
						$tar=$dbo->loadAssocList();
						$cars[$k]['cost']=($tar[0]['cost'] / $tar[0]['days']);
					} else {
						$cars[$k]['cost']=0;
					}
				}
			}
			$cars = VikRentCar::sortCarPrices($cars);
			if ($orderby == 'name') {
				$sortmap = array();
				foreach ($cars as $k => $v) {
					$sortmap[$k] = $v['name'];
				}
				asort($sortmap);
				$sorted = array();
				foreach ($sortmap as $k => $v) {
					$sorted[$k] = $cars[$k];
				}
				$cars = $sorted;
			}
			if ($ordertype == 'desc') {
				$cars = array_reverse($cars, true);
			}
			//pagination
			$lim = VikRequest::getVar('lim', 20, '', 'int'); //results limit
			$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination(count($cars), $lim0, $lim);
			$navig = $pageNav->getPagesLinks();
			$this->navig = &$navig;
			$cars = array_slice($cars, $lim0, $lim, true);
			//
			
			$this->cars = &$cars;
			$this->category = &$category;
			$this->vrc_tn = &$vrc_tn;
			//theme
			$theme = VikRentCar::getTheme();
			if ($theme != 'default') {
				$thdir = VRC_SITE_PATH.DS.'themes'.DS.$theme.DS.'carslist';
				if (is_dir($thdir)) {
					$this->_setPath('template', $thdir.DS);
				}
			}
			//
			parent::display($tpl);
		}
	}
}
