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

class VikRentCarViewOrders extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$rows = "";
		$navbut = "";
		$all_locations = '';
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$session = JFactory::getSession();
		$lim = $mainframe->getUserStateFromRequest("com_vikrentcar.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');

		$q = "SELECT `id`,`name` FROM `#__vikrentcar_places` ORDER BY `#__vikrentcar_places`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$all_locations = $dbo->loadAssocList();
		}
		$allcars = array();
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_cars` ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$allcars = $dbo->loadAssocList();
		}
		$plocation = VikRequest::getInt('location', '', 'request');
		$plocationw = VikRequest::getString('locationw', '', 'request');
		$plocationw = empty($plocationw) || !in_array($plocationw, array('pickup', 'dropoff', 'both')) ? 'pickup' : $plocationw;
		$pvrcorderby = VikRequest::getString('vrcorderby', '', 'request');
		$pvrcordersort = VikRequest::getString('vrcordersort', '', 'request');
		$pfiltnc = VikRequest::getString('filtnc', '', 'request');
		$validorderby = array('id', 'ts', 'carname', 'pickupts', 'dropoffts', 'days', 'total', 'status');
		$orderby = $session->get('vrcViewOrdersOrderby', 'id');
		$ordersort = $session->get('vrcViewOrdersOrdersort', 'DESC');
		if (!empty($pvrcorderby) && in_array($pvrcorderby, $validorderby)) {
			$orderby = $pvrcorderby;
			$session->set('vrcViewOrdersOrderby', $orderby);
			if (!empty($pvrcordersort) && in_array($pvrcordersort, array('ASC', 'DESC'))) {
				$ordersort = $pvrcordersort;
				$session->set('vrcViewOrdersOrdersort', $ordersort);
			}
		}

		$pidcar = VikRequest::getInt('idcar', '', 'request');
		$pcust_id = VikRequest::getInt('cust_id', '', 'request');
		$pdatefilt = VikRequest::getInt('datefilt', '', 'request');
		$pdatefiltfrom = VikRequest::getString('datefiltfrom', '', 'request');
		$pdatefiltto = VikRequest::getString('datefiltto', '', 'request');
		$dates_filter = '';
		if (!empty($pdatefilt) && (!empty($pdatefiltfrom) || !empty($pdatefiltto))) {
			$dates_filter_field = '`o`.`ts`';
			if ($pdatefilt == 2) {
				$dates_filter_field = '`o`.`ritiro`';
			} elseif ($pdatefilt == 3) {
				$dates_filter_field = '`o`.`consegna`';
			}
			$dates_filter_clauses = array();
			if (!empty($pdatefiltfrom)) {
				$dates_filter_clauses[] = $dates_filter_field.'>='.VikRentCar::getDateTimestamp($pdatefiltfrom, '0', '0');
			}
			if (!empty($pdatefiltto)) {
				$dates_filter_clauses[] = $dates_filter_field.'<='.VikRentCar::getDateTimestamp($pdatefiltto, 23, 60);
			}
			$dates_filter = implode(' AND ', $dates_filter_clauses);
		}
		$pstatus = VikRequest::getString('status', '', 'request');
		$status_filter = !empty($pstatus) && in_array($pstatus, array('confirmed', 'standby', 'cancelled')) ? "`o`.`status`='".$pstatus."'" : '';
		$pidpayment = VikRequest::getInt('idpayment', '', 'request');
		$payment_filter = '';
		if (!empty($pidpayment)) {
			$payment_filter = "`o`.`idpayment` LIKE '".$pidpayment."=%'";
		}
		$ordersfound = false;

		$orderby_col = '`o`.`'.$orderby.'`';
		if ($orderby == 'carname') {
			$orderby_col = '`c`.`name`';
		} elseif ($orderby == 'pickupts') {
			$orderby_col = '`o`.`ritiro`';
		} elseif ($orderby == 'dropoffts') {
			$orderby_col = '`o`.`consegna`';
		} elseif ($orderby == 'total') {
			$orderby_col = '`o`.`order_total`';
		}

		if (!empty($pfiltnc)) {
			$q = "SELECT SQL_CALC_FOUND_ROWS `o`.*,`b`.`stop_sales`,`c`.`name` AS `carname` FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_busy` `b` ON `b`.`id`=`o`.`idbusy` LEFT JOIN `#__vikrentcar_cars` `c` ON `o`.`idcar`=`c`.`id` WHERE (CONCAT_WS('_', `o`.`sid`, `o`.`ts`) = ".$dbo->quote($pfiltnc)." OR `o`.`id`=".$dbo->quote($pfiltnc)." OR `o`.`sid`=".$dbo->quote(str_replace('_', '', trim($pfiltnc)))." OR `o`.`custdata` LIKE ".$dbo->quote('%'.$pfiltnc.'%')." OR `o`.`nominative` LIKE ".$dbo->quote('%'.$pfiltnc.'%').") ORDER BY ".$orderby_col." ".$ordersort;
			$dbo->setQuery($q, $lim0, $lim);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rows = $dbo->loadAssocList();
				$dbo->setQuery('SELECT FOUND_ROWS();');
				$totres = $dbo->loadResult();
				if ($totres == 1 && count($rows) == 1) {
					$mainframe->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$rows[0]['id']);
					exit;
				} else {
					$ordersfound = true;
					jimport('joomla.html.pagination');
					$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
					$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
				}
			}
		}

		$where_clauses = array();
		if ($plocation > 0) {
			if ($plocationw == 'both') {
				$where_clauses[] = '(`o`.`idplace`='.$plocation.' OR `o`.`idreturnplace`='.$plocation.")";
			} elseif ($plocationw == 'dropoff') {
				$where_clauses[] = '`o`.`idreturnplace`='.$plocation;
			} elseif ($plocationw == 'pickup') {
				$where_clauses[] = '`o`.`idplace`='.$plocation;
			}
		}
		if (!empty($pidcar)) {
			$where_clauses[] = '`o`.`idcar`='.$pidcar;
		}
		if (!empty($dates_filter)) {
			$where_clauses[] = $dates_filter;
		}
		if (!empty($payment_filter)) {
			$where_clauses[] = $payment_filter;
		}
		if (!empty($status_filter)) {
			$where_clauses[] = $status_filter;
		}

		if (!$ordersfound) {
			if (!empty($pcust_id)) {
				$q = "SELECT SQL_CALC_FOUND_ROWS `o`.*,`b`.`stop_sales`,`c`.`name` AS `carname`,`co`.`idcustomer`,CONCAT_WS(' ', `cust`.`first_name`, `cust`.`last_name`) AS `customer_fullname` FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_busy` `b` ON `b`.`id`=`o`.`idbusy` LEFT JOIN `#__vikrentcar_cars` `c` ON `o`.`idcar`=`c`.`id` LEFT JOIN `#__vikrentcar_customers_orders` `co` ON `co`.`idorder`=`o`.`id` LEFT JOIN `#__vikrentcar_customers` `cust` ON `cust`.`id`=`co`.`idcustomer` AND `cust`.`id`=".$pcust_id." WHERE ".(!empty($dates_filter) ? $dates_filter.' AND ' : '').(!empty($payment_filter) ? $payment_filter.' AND ' : '').(!empty($status_filter) ? $status_filter.' AND ' : '')."`co`.`idcustomer`=".$pcust_id." ORDER BY ".$orderby_col." ".$ordersort;
			} else {
				$q = "SELECT SQL_CALC_FOUND_ROWS `o`.*,`b`.`stop_sales`,`c`.`name` AS `carname` FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_busy` `b` ON `b`.`id`=`o`.`idbusy` LEFT JOIN `#__vikrentcar_cars` `c` ON `o`.`idcar`=`c`.`id`".(count($where_clauses) ? ' WHERE '.implode(' AND ', $where_clauses) : '')." ORDER BY ".$orderby_col." ".$ordersort;
			}
			$dbo->setQuery($q, $lim0, $lim);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rows = $dbo->loadAssocList();
				$dbo->setQuery('SELECT FOUND_ROWS();');
				jimport('joomla.html.pagination');
				$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
				$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
			}
		}
		
		$this->rows = &$rows;
		$this->lim0 = &$lim0;
		$this->navbut = &$navbut;
		$this->all_locations = &$all_locations;
		$this->plocation = &$plocation;
		$this->plocationw = &$plocationw;
		$this->orderby = &$orderby;
		$this->ordersort = &$ordersort;
		$this->allcars = &$allcars;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRMAINORDERTITLE'), 'vikrentcar');
		if (JFactory::getUser()->authorise('core.create', 'com_vikrentcar')) {
			JToolBarHelper::custom( 'export', 'download', 'download', JText::_('VRMAINORDERSEXPORT'), false, false);
			JToolBarHelper::custom( 'orders', 'file-2', 'file-2', JText::_('VRCGENINVOICE'), true);
		}
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrentcar')) {
			JToolBarHelper::editList('editorder', JText::_('VRMAINORDEREDIT'));
		}
		if (JFactory::getUser()->authorise('core.delete', 'com_vikrentcar')) {
			JToolBarHelper::deleteList(JText::_('VRCDELCONFIRM'), 'removeorders', JText::_('VRMAINORDERDEL'));
		}
	}

}
