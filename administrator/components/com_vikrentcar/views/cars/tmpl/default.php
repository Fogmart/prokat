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

$rows = $this->rows;
$lim0 = $this->lim0;
$navbut = $this->navbut;
$orderby = $this->orderby;
$ordersort = $this->ordersort;

if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VRNOCARSFOUND'); ?></p>
	<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikrentcar" />
	</form>
	<?php
} else {
	?>
<script type="text/javascript">
function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'removecar') {
		if (confirm('<?php echo JText::_('VRJSDELCAR'); ?>?')) {
			submitform( pressbutton );
			return;
		} else {
			return false;
		}
	}

	// do field validation
	try {
		document.adminForm.onsubmit();
	}
	catch(e) {}
	submitform( pressbutton );
}
</script>
<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">
<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped">
	<thead>
	<tr>
		<th width="20">
			<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
		</th>
		<th class="title center" align="center" width="30"><a href="index.php?option=com_vikrentcar&amp;task=cars&amp;vrcorderby=id&amp;vrcordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vrcsortasc" : ($orderby == "id" ? "vrcsortdesc" : "")); ?>">ID</a></th>
		<th class="title left" width="150"><a href="index.php?option=com_vikrentcar&amp;task=cars&amp;vrcorderby=name&amp;vrcordersort=<?php echo ($orderby == "name" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "name" && $ordersort == "ASC" ? "vrcsortasc" : ($orderby == "name" ? "vrcsortdesc" : "")); ?>"><?php echo JText::_( 'VRPVIEWCARONE' ); ?></a></th>
		<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWCARTWO' ); ?></th>
		<th class="title center" align="center" width="150"><?php echo JText::_( 'VRPVIEWCARTHREE' ); ?></th>
		<th class="title center" align="center" width="150"><?php echo JText::_( 'VRPVIEWCARFOUR' ); ?></th>
		<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWCARFIVE' ); ?></th>
		<th class="title center" align="center" width="100"><a href="index.php?option=com_vikrentcar&amp;task=cars&amp;vrcorderby=units&amp;vrcordersort=<?php echo ($orderby == "units" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "units" && $ordersort == "ASC" ? "vrcsortasc" : ($orderby == "units" ? "vrcsortdesc" : "")); ?>"><?php echo JText::_( 'VRPVIEWCARSEVEN' ); ?></a></th>
		<th class="title center" align="center" width="100"><?php echo JText::_( 'VRPVIEWCARSIX' ); ?></th>
	</tr>
	</thead>
	<?php

	$dbo = JFactory::getDBO();
	$kk = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		$q = "SELECT COUNT(*) AS `totdisp` FROM `#__vikrentcar_dispcost` WHERE `idcar`=".(int)$row['id']." ORDER BY `#__vikrentcar_dispcost`.`days`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$lines = $dbo->loadAssocList();
		$tot = $lines[0]['totdisp'];
		if (!empty($row['idcat'])) {
			$validcats = false;
			$categories = "";
			$cat = explode(";", $row['idcat']);
			$q = "SELECT `name` FROM `#__vikrentcar_categories` WHERE ";
			foreach ($cat as $k=>$cc) {
				if (!empty($cc)) {
					$validcats = true;
					$q .= "`id`=".$dbo->quote($cc)." ";
					if ($cc != end($cat) && !empty($cat[($k + 1)])) {
						$q .= "OR ";
					}
				}
			}
			$q .= ";";
			if ($validcats) {
				$dbo->setQuery($q);
				$dbo->execute();
				$lines = $dbo->loadAssocList();
				if (is_array($lines)) {
					$categories = array();
					foreach ($lines as $ll) {
						$categories[] = $ll['name'];
					}
					$categories = implode(", ", $categories);
				} else {
					$categories = "";
				}
			} else {
				$categories = "";
			}
		} else {
			$categories = "";
		}
		
		if (!empty($row['idcarat'])) {
			$tmpcarat = explode(";", $row['idcarat']);
			$caratteristiche = VikRentCar::totElements($tmpcarat);
		} else {
			$caratteristiche = "";
		}
		
		if (!empty($row['idopt'])) {
			$tmpopt = explode(";", $row['idopt']);
			$optionals = VikRentCar::totElements($tmpopt);
		} else {
			$optionals = "";
		}
		
		if (!empty($row['idplace'])) {
			$explace = explode(";", $row['idplace']);
			$q = "SELECT `id`,`name` FROM `#__vikrentcar_places` WHERE `id`=".$dbo->quote($explace[0]).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$lines = $dbo->loadAssocList();
			$luogo = $lines[0]['name'];
			if (@count($explace) > 2) {
				$luogo .= " ...";
			}
		} else {
			$luogo = "";
		}
		
		?>
		<tr class="row<?php echo $kk; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td class="center"><?php echo $row['id']; ?></td>
			<td><a href="index.php?option=com_vikrentcar&amp;task=editcar&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
			<td><?php echo $categories; ?></td>
			<td class="center"><?php echo $caratteristiche; ?></td>
			<td class="center"><?php echo $optionals; ?></td>
			<td><?php echo $luogo; ?></td>
			<td class="center"><?php echo $row['units']; ?></td>
            <td class="center"><a href="index.php?option=com_vikrentcar&amp;task=modavail&amp;cid[]=<?php echo $row['id']; ?>"><?php echo (intval($row['avail'])=="1" ? "<i class=\"fa fa-check vrc-icn-img\" style=\"color: #099909;\" title=\"".JText::_('VRMAKENOTAVAIL')."\"></i>" : "<i class=\"fa fa-times-circle vrc-icn-img\" style=\"color: #ff0000;\" title=\"".JText::_('VRMAKENOTAVAIL')."\"></i>"); ?></a></td>
		</tr>
		<?php
		$kk = 1 - $kk;
		unset($categories);
	}
	?>
	
	</table>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="cars" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
