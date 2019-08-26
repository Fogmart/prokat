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
$vrc_app = VikRentCar::getVrcApplication();
JHTML::_('behavior.calendar');

$firstwday = (int)VikRentCar::getFirstWeekDay(true);
$days_labels = array(
	JText::_('VRCSUNDAY'),
	JText::_('VRCMONDAY'),
	JText::_('VRCTUESDAY'),
	JText::_('VRCWEDNESDAY'),
	JText::_('VRCTHURSDAY'),
	JText::_('VRCFRIDAY'),
	JText::_('VRCSATURDAY')
);
$days_indexes = array();
for ($i = 0; $i < 7; $i++) {
	$days_indexes[$i] = (6-($firstwday-$i)+1)%7;
}

$wopening = count($row) && !empty($row['wopening']) ? json_decode($row['wopening'], true) : array();
$wopening = !is_array($wopening) ? array() : $wopening;

$difftime = false;
if (count($row) && !empty($row['opentime'])) {
	$difftime = true;
	$parts = explode("-", $row['opentime']);
	$openat = VikRentCar::getHoursMinutes($parts[0]);
	$closeat = VikRentCar::getHoursMinutes($parts[1]);
}
$hours = "<option value=\"\"> </option>\n";
$hours_ovw = "<option value=\"\"> </option>\n";
for ($i=0; $i <= 23; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$stat = ($difftime == true && (int)$openat[0] == $i ? " selected=\"selected\"" : "");
	$hours .= "<option value=\"".$i."\"".$stat.">".$in."</option>\n";
	$hours_ovw .= "<option value=\"".$i."\" data-val=\";".$i.";\">".$in."</option>\n";
}
$sugghours = "<option value=\"\"> </option>\n";
$defhour = count($row) && !empty($row['defaulttime']) ? ((int)$row['defaulttime'] / 3600) : '';
for ($i=0; $i <= 23; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$stat = (strlen($defhour) && $defhour == $i ? " selected=\"selected\"" : "");
	$sugghours.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$minutes = "<option value=\"\"> </option>\n";
$minutes_ovw = "<option value=\"\"> </option>\n";
for ($i=0; $i <= 59; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$stat = ($difftime == true && (int)$openat[1] == $i ? " selected=\"selected\"" : "");
	$minutes .= "<option value=\"".$i."\"".$stat.">".$in."</option>\n";
	$minutes_ovw .= "<option value=\"".$i."\" data-val=\";".$i.";\">".$in."</option>\n";
}
$hoursto = "<option value=\"\"> </option>\n";
for ($i=0; $i <= 23; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$stat = ($difftime == true && (int)$closeat[0] == $i ? " selected=\"selected\"" : "");
	$hoursto.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$minutesto = "<option value=\"\"> </option>\n";
for ($i=0; $i <= 59; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$stat = ($difftime == true && (int)$closeat[1] == $i ? " selected=\"selected\"" : "");
	$minutesto.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$dbo = JFactory::getDBO();
$wiva = "<select name=\"praliq\">\n";
$wiva .= "<option value=\"\"> ------ </option>\n";
$q = "SELECT * FROM `#__vikrentcar_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	foreach ($ivas as $iv) {
		$wiva .= "<option value=\"".$iv['id']."\"".(count($row) && $row['idiva'] == $iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
	}
}
$wiva .= "</select>\n";
?>
<script type="text/javascript">
function vrcAddClosingDate() {
	var closingdadd = document.getElementById('insertclosingdate').value;
	var closingdintv = document.getElementById('closingintv').value;
	if (closingdadd.length > 0) {
		document.getElementById('closingdays').value += closingdadd + closingdintv + ',';
		document.getElementById('insertclosingdate').value = '';
		document.getElementById('closingintv').value = '';
	}
}
function vrcToggleWopening(mode, ind) {
	if (mode == 'on') {
		// plus button
		jQuery('#vrc-wopen-on-'+ind).hide();
		jQuery('#vrc-wopen-off-'+ind).fadeIn();
		jQuery('#wopening-'+ind).show();
	} else {
		// minus button
		jQuery('#vrc-wopen-off-'+ind).hide();
		jQuery('#vrc-wopen-on-'+ind).fadeIn();
		jQuery('#wopening-'+ind).hide().find('select').val('');
	}
}
</script>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<table class="admintable table">
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VREDITPLACEONE'); ?></b> </td><td><input type="text" name="placename" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></td></tr>
		<tr><td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRCLOCADDRESS'); ?></b> </td><td><input type="text" name="address" value="<?php echo count($row) ? htmlspecialchars($row['address']) : ''; ?>" size="40"/></td></tr>
		<tr><td class="vrc-config-param-cell" width="150"> <b><?php echo JText::_('VRCPLACELAT'); ?></b> </td><td><input type="text" name="lat" value="<?php echo count($row) ? $row['lat'] : ''; ?>" size="30"/></td></tr>
		<tr><td class="vrc-config-param-cell" width="150"> <b><?php echo JText::_('VRCPLACELNG'); ?></b> </td><td><input type="text" name="lng" value="<?php echo count($row) ? $row['lng'] : ''; ?>" size="30"/></td></tr>
		<tr><td class="vrc-config-param-cell" width="150"> <b><?php echo JText::_('VRCPLACEOVERRIDETAX'); ?></b> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPLACEOVERRIDETAX'), 'content' => JText::_('VRCPLACEOVERRIDETAXTXT'))); ?></td><td><?php echo $wiva; ?></td></tr>
		<tr>
			<td class="vrc-config-param-cell" width="150" valign="top"> <b><?php echo JText::_('VRCPLACEOPENTIME'); ?></b> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPLACEOPENTIME'), 'content' => JText::_('VRCPLACEOPENTIMETXT'))); ?></td>
			<td>
				<table style="width: auto !important;">
					<tr>
						<td style="vertical-align: middle;"><?php echo JText::_('VRCPLACEOPENTIMEFROM'); ?>:</td>
						<td style="vertical-align: middle;"><select style="margin: 0;" name="opentimefh"><?php echo $hours; ?></select></td>
						<td style="vertical-align: middle;">:</td>
						<td style="vertical-align: middle;"><select style="margin: 0;" name="opentimefm"><?php echo $minutes; ?></select></td>
					</tr>
					<tr>
						<td style="vertical-align: middle;"><?php echo JText::_('VRCPLACEOPENTIMETO'); ?>:</td>
						<td style="vertical-align: middle;"><select style="margin: 0;" name="opentimeth"><?php echo $hoursto; ?></select></td>
						<td style="vertical-align: middle;">:</td>
						<td style="vertical-align: middle;"><select style="margin: 0;" name="opentimetm"><?php echo $minutesto; ?></select></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="vrc-config-param-cell" width="150" valign="top"> <b><?php echo JText::_('VRCPLACESUGGOPENTIME'); ?></b> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPLACESUGGOPENTIME'), 'content' => JText::_('VRCPLACESUGGOPENTIMETXT'))); ?></td>
			<td>
				<select name="suggopentimeh"><?php echo $sugghours; ?></select>
			</td>
		</tr>
		<tr>
			<td class="vrc-config-param-cell" width="150" valign="top"> <b><?php echo JText::_('VRCPLACEOVROPENTIME'); ?></b> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPLACEOVROPENTIME'), 'content' => JText::_('VRCPLACEOVROPENTIMEHELP'))); ?></td>
			<td>
				<table>
					<tr>
					<?php
					for ($i = 0; $i < 7; $i++) {
						$d_ind = ($i + $firstwday) < 7 ? ($i + $firstwday) : ($i + $firstwday - 7);
						$fhopt = isset($wopening[$d_ind]) ? str_replace('data-val=";'.$wopening[$d_ind]['fh'].';"', 'selected="selected"', $hours_ovw) : $hours_ovw;
						$fmopt = isset($wopening[$d_ind]) ? str_replace('data-val=";'.$wopening[$d_ind]['fm'].';"', 'selected="selected"', $minutes_ovw) : $minutes_ovw;
						$thopt = isset($wopening[$d_ind]) ? str_replace('data-val=";'.$wopening[$d_ind]['th'].';"', 'selected="selected"', $hours_ovw) : $hours_ovw;
						$tmopt = isset($wopening[$d_ind]) ? str_replace('data-val=";'.$wopening[$d_ind]['tm'].';"', 'selected="selected"', $minutes_ovw) : $minutes_ovw;
						?>
						<td>
							<h4><?php echo $days_labels[$d_ind]; ?></h4>
							<a style="display: <?php echo isset($wopening[$d_ind]) ? 'none' : 'block'; ?>; text-align: center; color: green;" href="javascript: void(0);" id="vrc-wopen-on-<?php echo $d_ind; ?>" onclick="vrcToggleWopening('on', '<?php echo $d_ind; ?>');"><i class="fas fa-plus-circle"></i></a>
							<a style="display: <?php echo isset($wopening[$d_ind]) ? 'block' : 'none'; ?>; text-align: center; color: red;" href="javascript: void(0);" id="vrc-wopen-off-<?php echo $d_ind; ?>" onclick="vrcToggleWopening('off', '<?php echo $d_ind; ?>');"><i class="fas fa-minus-circle"></i></a>
							<table style="width: auto !important; display: <?php echo isset($wopening[$d_ind]) ? 'block' : 'none'; ?>;" id="wopening-<?php echo $d_ind; ?>">
								<tr>
									<td style="vertical-align: middle;"><?php echo JText::_('VRCPLACEOPENTIMEFROM'); ?>:</td>
									<td style="vertical-align: middle;"><select style="margin: 0;" name="wopeningfh[<?php echo $d_ind; ?>]"><?php echo $fhopt; ?></select></td>
									<td style="vertical-align: middle;">:</td>
									<td style="vertical-align: middle;"><select style="margin: 0;" name="wopeningfm[<?php echo $d_ind; ?>]"><?php echo $fmopt; ?></select></td>
								</tr>
								<tr>
									<td style="vertical-align: middle;"><?php echo JText::_('VRCPLACEOPENTIMETO'); ?>:</td>
									<td style="vertical-align: middle;"><select style="margin: 0;" name="wopeningth[<?php echo $d_ind; ?>]"><?php echo $thopt; ?></select></td>
									<td style="vertical-align: middle;">:</td>
									<td style="vertical-align: middle;"><select style="margin: 0;" name="wopeningtm[<?php echo $d_ind; ?>]"><?php echo $tmopt; ?></select></td>
								</tr>
							</table>
						</td>
						<?php
					}
					?>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="vrc-config-param-cell" width="150"> <b><?php echo JText::_('VRCPLACEDESCR'); ?></b> </td>
			<td><?php echo $editor->display("descr", (count($row) ? $row['descr'] : ''), 400, 200, 70, 20); ?></td>
		</tr>
		<tr>
			<td class="vrc-config-param-cell" width="150" valign="top"> <b><?php echo JText::_('VRNEWPLACECLOSINGDAYS'); ?></b> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRNEWPLACECLOSINGDAYS'), 'content' => JText::_('VRNEWPLACECLOSINGDAYSHELP'))); ?></td>
			<td><?php echo JHTML::_('calendar', '', 'insertclosingdate', 'insertclosingdate', '%Y-%m-%d', array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?> <span class="vrc-loc-closeintv"><select id="closingintv"><option value=""><?php echo JText::_('VRNEWPLACECLOSINGDAYSINGLE'); ?></option><option value=":w"><?php echo JText::_('VRNEWPLACECLOSINGDAYWEEK'); ?></option></select></span> <span class="vrcspandateadd" onclick="javascript: vrcAddClosingDate();"><?php echo JText::_('VRNEWPLACECLOSINGDAYSADD'); ?></span><br/><textarea name="closingdays" id="closingdays" rows="5" cols="44"><?php echo count($row) ? $row['closingdays'] : ''; ?></textarea>
			</td>
		</tr>
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
