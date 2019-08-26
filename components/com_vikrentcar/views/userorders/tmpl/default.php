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

$rows = $this->rows;
$searchorder = $this->searchorder;
$islogged = $this->islogged;
$pagelinks = $this->pagelinks;

$nowdf = VikRentCar::getDateFormat();
$nowtf = VikRentCar::getTimeFormat();
if ($nowdf=="%d/%m/%Y") {
	$df='d/m/Y';
} elseif ($nowdf=="%m/%d/%Y") {
	$df='m/d/Y';
} else {
	$df='Y/m/d';
}

$pitemid = VikRequest::getString('Itemid', '', 'request');

if ($searchorder == 1) {
	?>
	<div class="vrcsearchconfnumb">
		<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=userorders'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" method="post">
			<input type="hidden" name="option" value="com_vikrentcar"/>
			<input type="hidden" name="view" value="userorders"/>
			<input type="hidden" name="searchorder" value="1"/>
			<div class="vrcconfnumbinp">
				<label for="vrcconfnum"><?php echo JText::_('VRCCONFNUMBERLBL'); ?></label>
				<input type="text" name="confirmnum" value="" size="25" id="vrcconfnum"/>
			</div>
			<div class="vrcconfnumbsubm">
				<input type="submit" name="searchconfnum" value="<?php echo JText::_('VRCCONFNUMBERSEARCHBTN'); ?>"/>
			</div>
		</form>
	</div>
	<?php
}

if ($islogged == 1) {
	?>
	<h3><?php echo JText::_('VRCYOURRESERVATIONS'); ?></h3>
	<?php
} else {
	?>
	<p><a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=loginregister'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>"><?php echo JText::_('VRCRESERVATIONSLOGIN'); ?></a></p>
	<?php
}

if (is_array($rows) && count($rows) > 0) {
	?>
	<table class="vrcuserorderstable">
		<tr class="vrcuserorderstablerow"><td><?php echo JText::_('VRCUSERRESDATE'); ?></td><td><?php echo JText::_('VRCUSERRESSTATUS'); ?></td></tr>
	<?php
	foreach ($rows as $ord) {
		$status_lbl = '';
		if ($ord['status'] == 'confirmed') {
			$status_lbl = '<span style="color: green;">'.JText::_('VRCONFIRMED').'</span>';
		} elseif ($ord['status'] == 'standby') {
			$status_lbl = '<span style="color: #f89406;">'.JText::_('VRSTANDBY').'</span>';
		} elseif ($ord['status'] == 'cancelled') {
			$status_lbl = '<span style="color: red;">'.JText::_('VRCANCELLED').'</span>';
		}
		?>
		<tr><td><a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&task=vieworder&sid='.$ord['sid'].'&ts='.$ord['ts'].(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>"><?php echo date($df.' '.$nowtf, $ord['ts']); ?></a></td><td><?php echo $status_lbl; ?></td></tr>
		<?php
	}
	?>
	</table>
	<?php
} else {
	?>
	<p class="vrcuserordersparag"><?php echo JText::_('VRCNOUSERRESFOUND'); ?></p>
	<?php
}

echo $pagelinks;
