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

// import Joomla view library
jimport('joomla.application.component.view');

class VikRentCarViewChoosebusy extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$rows = "";
		$navbut = "";
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pts = VikRequest::getInt('ts', '', 'request');
		$pidcar = VikRequest::getInt('idcar', '', 'request');
		if (empty($pts) || empty($pidcar)) {
			VikError::raiseWarning('', 'Not found.');
			$mainframe->redirect("index.php?option=com_vikrentcar&task=orders");
			exit;
		}
		//ultimo secondo del giorno scelto
		$realritiro = $pts + 86399;
		//
		$q = "SELECT COUNT(*) FROM `#__vikrentcar_busy` AS `b` WHERE `b`.`idcar`=".$dbo->quote($pidcar)." AND `b`.`ritiro`<=".$dbo->quote($realritiro)." AND `b`.`consegna`>=".$dbo->quote($pts)."";
		$dbo->setQuery($q);
		$dbo->execute();
		$totres = $dbo->loadResult();

		$lim = $mainframe->getUserStateFromRequest("com_vikrentcar.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$q = "SELECT SQL_CALC_FOUND_ROWS `b`.`id`,`b`.`idcar`,`b`.`ritiro`,`b`.`consegna`,`b`.`realback`,`o`.`id` AS `idorder`,`o`.`custdata`,`o`.`ts`,`o`.`country`,`o`.`carindex`,`o`.`nominative`,`c`.`name`,`c`.`img`,`c`.`units`,`c`.`params` FROM `#__vikrentcar_busy` AS `b`,`#__vikrentcar_orders` AS `o`,`#__vikrentcar_cars` AS `c` WHERE `b`.`idcar`=".$dbo->quote($pidcar)." AND `b`.`ritiro`<=".$dbo->quote($realritiro)." AND `b`.`consegna`>=".$dbo->quote($pts)." AND `o`.`idbusy`=`b`.`id` AND `c`.`id`=`b`.`idcar` ORDER BY `b`.`ritiro` ASC";
		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rows = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
		} else {
			VikError::raiseWarning('', 'No records.');
			$mainframe->redirect("index.php?option=com_vikrentcar&task=orders");
			exit;
		}
		
		$this->rows = &$rows;
		$this->lim0 = &$lim0;
		$this->navbut = &$navbut;
		$this->totres = &$totres;
		$this->pts = &$pts;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		$dbo = JFactory::getDBO();
		$pgoto = VikRequest::getString('goto', '', 'request');
		$pts = VikRequest::getInt('ts', '', 'request');
		$pidcar = VikRequest::getInt('idcar', '', 'request');
		$q = "SELECT `name` FROM `#__vikrentcar_cars` WHERE `id`=".$dbo->quote($pidcar).";";
		$dbo->setQuery($q);
		$dbo->execute();
		$cname=$dbo->loadResult();
		JToolBarHelper::title(JText::_('VRMAINCHOOSEBUSY')." ".$cname.", ".date('Y-M-d', $pts), 'vikrentcar');
		JToolBarHelper::cancel( ($pgoto == 'overv' ? 'canceloverv' : 'cancelcalendar'), JText::_('VRBACK'));
	}

}
