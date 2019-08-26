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

if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VRNOCARATFOUND'); ?></p>
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
	if (pressbutton == 'removecarat') {
		if (confirm('<?php echo JText::_('VRJSDELCARAT'); ?> ?')) {
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
		<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWCARATONE' ); ?></th>
		<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWCARATTWO' ); ?></th>
		<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWCARATTHREE' ); ?></th>
		<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCORDERING' ); ?></th>
	</tr>
	</thead>
	<?php

	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikrentcar&amp;task=editcarat&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
			<td>
			<?php 
				echo (file_exists(VRC_ADMIN_PATH.DS.'resources'.DS.$row['icon']) && !empty($row['icon']) ? "<span>".$row['icon']." &nbsp;&nbsp;<img align=\"middle\" src=\"".VRC_ADMIN_URI."resources/".$row['icon']."\"/></span>" : $row['icon']); 
			?>
			</td>
			<td><?php echo $row['textimg']; ?></td>
			<td class="center">
				<a href="index.php?option=com_vikrentcar&amp;task=sortcarat&amp;cid[]=<?php echo $row['id']; ?>&amp;mode=up"><i class="fa fa-arrow-up vrc-icn-img"></i></a> 
				<a href="index.php?option=com_vikrentcar&amp;task=sortcarat&amp;cid[]=<?php echo $row['id']; ?>&amp;mode=down"><i class="fa fa-arrow-down vrc-icn-img"></i></a>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	
	</table>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="carat" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
