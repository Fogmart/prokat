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

$row = $this->row;
$wselcars = $this->wselcars;
$wselplaces = $this->wselplaces;

$currencysymb = VikRentCar::getCurrencySymb(true);
$df = VikRentCar::getDateFormat(true);
$fromsel = "<select name=\"from\">\n";
for ($i=0; $i <= 23; $i++) {
	$h = $i < 10 ? '0'.$i : $i;
	$seconds = $i * 3600;
	for ($j=0; $j < 60; $j+=15) {
		$seconds += $j > 0 ? (15 * 60) : 0;
		$m = $j < 10 ? '0'.$j : $j;
		$fromsel .= '<option value="'.$seconds.'"'.(count($row) && $row['from'] == $seconds ? ' selected="selected"' : '').'>'.$h.' : '.$m.'</option>'."\n";
	}
}
$fromsel .= "</select>\n";
$tosel = "<select name=\"to\">\n";
for ($i=0; $i <= 23; $i++) {
	$h = $i < 10 ? '0'.$i : $i;
	$seconds = $i * 3600;
	for ($j=0; $j < 60; $j+=15) {
		$seconds += $j > 0 ? (15 * 60) : 0;
		$m = $j < 10 ? '0'.$j : $j;
		$tosel .= '<option value="'.$seconds.'"'.(count($row) && $row['to'] == $seconds ? ' selected="selected"' : '').'>'.$h.' : '.$m.'</option>'."\n";
	}

}
$tosel .= "</select>\n";
$dbo = JFactory::getDBO();
$q = "SELECT * FROM `#__vikrentcar_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	$wiva = "<select name=\"aliq\"><option value=\"\"> </option>\n";
	foreach ($ivas as $iv) {
		$wiva .= "<option value=\"".$iv['id']."\"".(count($row) && $row['idiva'] == $iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
	}
	$wiva .= "</select>\n";
} else {
	$wiva = "<a href=\"index.php?option=com_vikrentcar&task=iva\">".JText::_('VRNOIVAFOUND')."</a>";
}
$wselwdays = "<select name=\"wdays[]\" multiple=\"multiple\" size=\"7\">\n";
$cur_wdays = count($row) ? explode(',', $row['wdays']) : array();
$wdays_map = array(JText::_('VRCSUNDAY'), JText::_('VRCMONDAY'), JText::_('VRCTUESDAY'), JText::_('VRCWEDNESDAY'), JText::_('VRCTHURSDAY'), JText::_('VRCFRIDAY'), JText::_('VRCSATURDAY'));
for ($oj=0; $oj < 7; $oj++) { 
	$wselwdays .= "<option value=\"".$oj."\"".(in_array('-'.$oj.'-', $cur_wdays) ? " selected=\"selected\"" : "").">".$wdays_map[$oj]."</option>\n";
}
$wselwdays .= "</select>\n";
?>
<script type="text/javascript">
function vrcMaxChargeOohf() {
	var pick_charge = jQuery("#pickcharge").val().length ? parseFloat(jQuery("#pickcharge").val()) : 0.00;
	var drop_charge = jQuery("#dropcharge").val().length ? parseFloat(jQuery("#dropcharge").val()) : 0.00;
	var max_charge = pick_charge + drop_charge;
	jQuery("#maxcharge").val(max_charge.toFixed(2));
}
jQuery(document).ready(function() {
	jQuery(".vrc-select-all").click(function() {
		jQuery(this).next("select").find("option").prop('selected', true);
	});
	jQuery("#pickcharge, #dropcharge").keyup(function() {
		vrcMaxChargeOohf();
	});
});
</script>
<form name="adminForm" action="index.php" method="post" id="adminForm">
	<table class="admintable table">
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRCPVIEWOOHFEESONE'); ?></b> </td><td><input type="text" name="name" value="<?php echo count($row) ? htmlspecialchars($row['oohname']) : ''; ?>" size="40"/></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRCPVIEWOOHFEESTWO'); ?></b> </td><td><?php echo $fromsel; ?></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRCPVIEWOOHFEESTHREE'); ?></b> </td><td><?php echo $tosel; ?></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRCPVIEWOOHFEESFOUR'); ?></b> </td><td><input type="number" step="any" id="pickcharge" name="pickcharge" placeholder="0.00" value="<?php echo count($row) ? $row['pickcharge'] : ''; ?>"/> <?php echo $currencysymb; ?></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRCPVIEWOOHFEESFIVE'); ?></b> </td><td><input type="number" step="any" id="dropcharge" name="dropcharge" placeholder="0.00" value="<?php echo count($row) ? $row['dropcharge'] : ''; ?>"/> <?php echo $currencysymb; ?></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRCPVIEWOOHFEESSIX'); ?></b> </td><td><input type="number" step="any" id="maxcharge" name="maxcharge" value="<?php echo count($row) ? $row['maxcharge'] : ''; ?>"/> <?php echo $currencysymb; ?></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRCWEEKDAYS'); ?></b> </td><td><span class="vrc-select-all"><?php echo JText::_('VRCSELECTALL'); ?></span><?php echo $wselwdays; ?></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRCPVIEWOOHFEESSEVEN'); ?></b> </td><td><span class="vrc-select-all"><?php echo JText::_('VRCSELECTALL'); ?></span><?php echo $wselcars; ?></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRCPVIEWOOHFEESEIGHT'); ?></b> </td><td><span class="vrc-select-all"><?php echo JText::_('VRCSELECTALL'); ?></span><?php echo $wselplaces; ?></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRCPVIEWOOHFEESNINE'); ?></b> </td><td><select name="type"><option value="1"<?php echo count($row) && $row['type'] == 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCPVIEWOOHFEESTEN'); ?></option><option value="2"<?php echo count($row) && $row['type'] == 2 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCPVIEWOOHFEESELEVEN'); ?></option><option value="3"<?php echo count($row) && $row['type'] == 3 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCPVIEWOOHFEESTWELVE'); ?></option></select></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRCPVIEWOOHFEESTAX'); ?></b> </td><td><?php echo $wiva; ?></td></tr>
	</table>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikrentcar" />
<?php
if (count($row)) {
	?>
	<input type="hidden" name="where" value="<?php echo $row['id']; ?>">
	<?php
}
?>
</form>
