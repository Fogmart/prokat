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

$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$dbo = JFactory::getDBO();
$q = "SELECT * FROM `#__vikrentcar_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	$wiva = "<select name=\"optaliq\"><option value=\"\"> </option>\n";
	foreach ($ivas as $iv) {
		$wiva .= "<option value=\"".$iv['id']."\"".(count($row) && $row['idiva']==$iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
	}
	$wiva .= "</select>\n";
} else {
	$wiva = "<a href=\"index.php?option=com_vikrentcar&task=iva\">".JText::_('VRNOIVAFOUND')."</a>";
}
$currencysymb = VikRentCar::getCurrencySymb(true);
//vikrentcar 1.6
if (count($row) && strlen($row['forceval']) > 0) {
	$forceparts = explode("-", $row['forceval']);
	$forcedq = $forceparts[0];
	$forcedqperday = intval($forceparts[1]) == 1 ? true : false;
} else {
	$forcedq = "1";
	$forcedqperday = false;
}
//
?>
<script type="text/javascript">
function showResizeSel() {
	if (document.adminForm.autoresize.checked == true) {
		document.getElementById('resizesel').style.display='block';
	} else {
		document.getElementById('resizesel').style.display='none';
	}
	return true;
}
function showForceSel() {
	if (document.adminForm.forcesel.checked == true) {
		document.getElementById('forcevalspan').style.display='block';
	} else {
		document.getElementById('forcevalspan').style.display='none';
	}
	return true;
}
</script>

<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<table class="admintable table">
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTONE'); ?></b> </td><td><input type="text" name="optname" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTTWO'); ?></b> </td><td><?php echo $editor->display( "optdescr", (count($row) ? $row['descr'] : ''), 400, 200, 70, 20 ); ?></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTTHREE'); ?></b> </td><td><?php echo $currencysymb; ?> <input type="number" step="any" name="optcost" value="<?php echo count($row) ? $row['cost'] : ''; ?>" /></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTFOUR'); ?></b> </td><td><?php echo $wiva; ?></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTFIVE'); ?></b> </td><td><input type="checkbox" name="optperday" value="each"<?php echo (count($row) && intval($row['perday']) == 1 ? " checked=\"checked\"" : ""); ?>/></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTEIGHT'); ?></b> </td><td><?php echo $currencysymb; ?> <input type="number" step="any" name="maxprice" value="<?php echo count($row) ? $row['maxprice'] : ''; ?>"/></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTSIX'); ?></b> </td><td><input type="checkbox" name="opthmany" value="yes"<?php echo (count($row) && intval($row['hmany']) == 1 ? " checked=\"checked\"" : ""); ?>/></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRCNEWOPTFORCEVALIFDAYS'); ?></b> </td><td><input type="number" min="0" name="forceifdays" value="<?php echo count($row) ? $row['forceifdays'] : ''; ?>"/></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTSEVEN'); ?></b> </td><td><?php echo (count($row) && !empty($row['img']) && file_exists(VRC_ADMIN_PATH.DS.'resources'.DS.$row['img']) ? "<img src=\"".VRC_ADMIN_URI."resources/".$row['img']."\" class=\"maxfifty\"/> &nbsp;" : ""); ?><input type="file" name="optimg" size="35"/><br/><label for="autoresize"><?php echo JText::_('VRNEWOPTNINE'); ?></label> <input type="checkbox" id="autoresize" name="autoresize" value="1" onclick="showResizeSel();"/> <span id="resizesel" style="display: none;">&nbsp;<?php echo JText::_('VRNEWOPTTEN'); ?>: <input type="text" name="resizeto" value="50" size="3"/> px</span></td></tr>
		<tr><td class="vrc-config-param-cell" width="200" valign="top"> <b><?php echo JText::_('VRCNEWOPTFORCESEL'); ?></b> </td><td><input type="checkbox" name="forcesel" value="1" onclick="showForceSel();"<?php echo (count($row) && intval($row['forcesel']) == 1 ? " checked=\"checked\"" : ""); ?>/> <span id="forcevalspan" style="display: <?php echo (count($row) && intval($row['forcesel']) == 1 ? "block" : "none"); ?>;"><?php echo JText::_('VRCNEWOPTFORCEVALT'); ?> <input type="text" name="forceval" value="<?php echo $forcedq; ?>" size="2"/><br/><?php echo JText::_('VRCNEWOPTFORCEVALTPDAY'); ?> <input type="checkbox" name="forcevalperday" value="1"<?php echo ($forcedqperday == true ? " checked=\"checked\"" : ""); ?>/></span></td></tr>
	</table>
	<input type="hidden" name="task" value="">
<?php
if (count($row)) {
	?>
	<input type="hidden" name="whereup" value="<?php echo $row['id']; ?>">
	<?php
}
?>
	<input type="hidden" name="option" value="com_vikrentcar" />
</form>
