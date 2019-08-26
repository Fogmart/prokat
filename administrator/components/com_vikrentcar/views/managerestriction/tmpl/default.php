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

$data = $this->data;
$cars = $this->cars;

$vrc_app = new VrcApplication();
$df = VikRentCar::getDateFormat(true);
if ($df == "%d/%m/%Y") {
	$cdf = 'd/m/Y';
} elseif ($df == "%m/%d/%Y") {
	$cdf = 'm/d/Y';
} else {
	$cdf = 'Y/m/d';
}
$carsel = '';
if (is_array($cars) && count($cars) > 0) {
	$nowcars = count($data) && !empty($data['idcars']) && $data['allcars'] == 0 ? explode(';', $data['idcars']) : array();
	$carsel = '<select name="idcars[]" multiple="multiple">'."\n";
	foreach ($cars as $r) {
		$carsel .= '<option value="'.$r['id'].'"'.(in_array('-'.$r['id'].'-', $nowcars) ? ' selected="selected"' : '').'>'.$r['name'].'</option>'."\n";
	}
	$carsel .= '</select>';
}
//CTA and CTD
$cur_setcta = count($data) && !empty($data['ctad']) ? explode(',', $data['ctad']) : array();
$cur_setctd = count($data) && !empty($data['ctdd']) ? explode(',', $data['ctdd']) : array();
$wdaysmap = array('0' => JText::_('VRCSUNDAY'), '1' => JText::_('VRCMONDAY'), '2' => JText::_('VRCTUESDAY'), '3' => JText::_('VRCWEDNESDAY'), '4' => JText::_('VRCTHURSDAY'), '5' => JText::_('VRCFRIDAY'), '6' => JText::_('VRCSATURDAY'));
$ctasel = '<select name="ctad[]" multiple="multiple" size="7">'."\n";
foreach ($wdaysmap as $wdk => $wdv) {
	$ctasel .= '<option value="'.$wdk.'"'.(in_array('-'.$wdk.'-', $cur_setcta) ? ' selected="selected"' : '').'>'.$wdv.'</option>'."\n";
}
$ctasel .= '</select>';
$ctdsel = '<select name="ctdd[]" multiple="multiple" size="7">'."\n";
foreach ($wdaysmap as $wdk => $wdv) {
	$ctdsel .= '<option value="'.$wdk.'"'.(in_array('-'.$wdk.'-', $cur_setctd) ? ' selected="selected"' : '').'>'.$wdv.'</option>'."\n";
}
$ctdsel .= '</select>';
//
$dfromval = count($data) && !empty($data['dfrom']) ? date($cdf, $data['dfrom']) : '';
$dtoval = count($data) && !empty($data['dto']) ? date($cdf, $data['dto']) : '';
$vrcra1 = '';
$vrcra2 = '';
$vrcrb1 = '';
$vrcrb2 = '';
$vrcrc1 = '';
$vrcrc2 = '';
$vrcrd1 = '';
$vrcrd2 = '';
if (count($data) && strlen($data['wdaycombo']) > 0) {
	$vrccomboparts = explode(':', $data['wdaycombo']);
	foreach($vrccomboparts as $kc => $cb) {
		if (!empty($cb)) {
			$nowcombo = explode('-', $cb);
			if ($kc == 0) {
				$vrcra1 = $nowcombo[0];
				$vrcra2 = $nowcombo[1];
			} elseif ($kc == 1) {
				$vrcrb1 = $nowcombo[0];
				$vrcrb2 = $nowcombo[1];
			} elseif ($kc == 2) {
				$vrcrc1 = $nowcombo[0];
				$vrcrc2 = $nowcombo[1];
			} elseif ($kc == 3) {
				$vrcrd1 = $nowcombo[0];
				$vrcrd2 = $nowcombo[1];
			}
		}
	}
}
$arrwdays = array(1 => JText::_('VRCMONDAY'),
		2 => JText::_('VRCTUESDAY'),
		3 => JText::_('VRCWEDNESDAY'),
		4 => JText::_('VRCTHURSDAY'),
		5 => JText::_('VRCFRIDAY'),
		6 => JText::_('VRCSATURDAY'),
		0 => JText::_('VRCSUNDAY')
);
?>
<script type="text/javascript">
function vrcSecondArrWDay() {
	var wdayone = document.adminForm.wday.value;
	if (wdayone != "") {
		document.getElementById("vrwdaytwodivid").style.display = "inline-block";
		document.adminForm.cta.checked = false;
		document.adminForm.ctd.checked = false;
		vrcToggleCta();
		vrcToggleCtd();
	} else {
		document.getElementById("vrwdaytwodivid").style.display = "none";
	}
	vrComboArrWDay();
}
function vrComboArrWDay() {
	var wdayone = document.adminForm.wday;
	var wdaytwo = document.adminForm.wdaytwo;
	if (wdayone.value != "" && wdaytwo.value != "" && wdayone.value != wdaytwo.value) {
		var comboa = wdayone.options[wdayone.selectedIndex].text;
		var combob = wdaytwo.options[wdaytwo.selectedIndex].text;
		document.getElementById("vrccomboa1").innerHTML = comboa;
		document.getElementById("vrccomboa2").innerHTML = combob;
		document.getElementById("vrccomboa").value = wdayone.value+"-"+wdaytwo.value;
		document.getElementById("vrccombob1").innerHTML = combob;
		document.getElementById("vrccombob2").innerHTML = comboa;
		document.getElementById("vrccombob").value = wdaytwo.value+"-"+wdayone.value;
		document.getElementById("vrccomboc1").innerHTML = comboa;
		document.getElementById("vrccomboc2").innerHTML = comboa;
		document.getElementById("vrccomboc").value = wdayone.value+"-"+wdayone.value;
		document.getElementById("vrccombod1").innerHTML = combob;
		document.getElementById("vrccombod2").innerHTML = combob;
		document.getElementById("vrccombod").value = wdaytwo.value+"-"+wdaytwo.value;
		document.getElementById("vrwdaycombodivid").style.display = "block";
	} else {
		document.getElementById("vrwdaycombodivid").style.display = "none";
	}
}
function vrcToggleCars() {
	if (document.adminForm.allcars.checked == true) {
		document.getElementById("vrcrestrcarsdiv").style.display = "none";
	} else {
		document.getElementById("vrcrestrcarsdiv").style.display = "block";
	}
}
function vrcToggleCta() {
	if (document.adminForm.cta.checked != true) {
		document.getElementById("vrcrestrctadiv").style.display = "none";
	} else {
		document.getElementById("vrcrestrctadiv").style.display = "block";
		document.adminForm.wday.value = "";
		document.adminForm.wdaytwo.value = "";
		vrcSecondArrWDay();

	}
}
function vrcToggleCtd() {
	if (document.adminForm.ctd.checked != true) {
		document.getElementById("vrcrestrctddiv").style.display = "none";
	} else {
		document.getElementById("vrcrestrctddiv").style.display = "block";
		document.adminForm.wday.value = "";
		document.adminForm.wdaytwo.value = "";
		vrcSecondArrWDay();
	}
}
</script>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<fieldset class="adminform">
		<table cellspacing="1" class="admintable table">
			<tbody>
				<tr>
					<td class="vrc-config-param-cell" width="200"><?php echo $vrc_app->createPopover(array('title' => JText::_('VRRESTRICTIONSHELPTITLE'), 'content' => JText::_('VRRESTRICTIONSSHELP'))); ?> <b><?php echo JText::_('VRNEWRESTRICTIONNAME'); ?>*</b></td>
					<td><input type="text" name="name" value="<?php echo count($data) ? $data['name'] : ''; ?>" size="40"/></td>
				</tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRNEWRESTRICTIONONE'); ?>*</b></td>
					<td><select name="month"><option value="0">----</option><option value="1"<?php echo (count($data) && $data['month'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHONE'); ?></option><option value="2"<?php echo (count($data) && $data['month'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHTWO'); ?></option><option value="3"<?php echo (count($data) && $data['month'] == 3 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHTHREE'); ?></option><option value="4"<?php echo (count($data) && $data['month'] == 4 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHFOUR'); ?></option><option value="5"<?php echo (count($data) && $data['month'] == 5 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHFIVE'); ?></option><option value="6"<?php echo (count($data) && $data['month'] == 6 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHSIX'); ?></option><option value="7"<?php echo (count($data) && $data['month'] == 7 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHSEVEN'); ?></option><option value="8"<?php echo (count($data) && $data['month'] == 8 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHEIGHT'); ?></option><option value="9"<?php echo (count($data) && $data['month'] == 9 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHNINE'); ?></option><option value="10"<?php echo (count($data) && $data['month'] == 10 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHTEN'); ?></option><option value="11"<?php echo (count($data) && $data['month'] == 11 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHELEVEN'); ?></option><option value="12"<?php echo (count($data) && $data['month'] == 12 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHTWELVE'); ?></option></select></td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRNEWRESTRICTIONOR'); ?>*</b></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRNEWRESTRICTIONDATERANGE'); ?>*</b></td>
					<td><div style="display: block; margin-bottom: 3px;"><?php echo '<span class="vrcrestrdrangesp">'.JText::_('VRNEWRESTRICTIONDFROMRANGE').'</span>'.$vrc_app->getCalendar($dfromval, 'dfrom', 'dfrom', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></div><div style="display: block; margin-bottom: 3px;"><?php echo '<span class="vrcrestrdrangesp">'.JText::_('VRNEWRESTRICTIONDTORANGE').'</span>'.$vrc_app->getCalendar($dtoval, 'dto', 'dto', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></div></td>
				</tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRNEWRESTRICTIONWDAY'); ?></b></td>
					<td>
						<select name="wday" onchange="vrcSecondArrWDay();"><option value=""></option><option value="0"<?php echo (count($data) && strlen($data['wday']) > 0 && $data['wday'] == 0 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCSUNDAY'); ?></option><option value="1"<?php echo (count($data) && $data['wday'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCMONDAY'); ?></option><option value="2"<?php echo (count($data) && $data['wday'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCTUESDAY'); ?></option><option value="3"<?php echo (count($data) && $data['wday'] == 3 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCWEDNESDAY'); ?></option><option value="4"<?php echo (count($data) && $data['wday'] == 4 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCTHURSDAY'); ?></option><option value="5"<?php echo (count($data) && $data['wday'] == 5 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCFRIDAY'); ?></option><option value="6"<?php echo (count($data) && $data['wday'] == 6 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCSATURDAY'); ?></option></select>
						<div class="vrwdaytwodiv" id="vrwdaytwodivid" style="display: <?php echo (count($data) && strlen($data['wday']) > 0 ? 'inline-block' : 'none'); ?>;"><span><?php echo JText::_('VRNEWRESTRICTIONOR'); ?></span> 
						<select name="wdaytwo" onchange="vrComboArrWDay();"><option value=""></option><option value="0"<?php echo (count($data) && strlen($data['wdaytwo']) > 0 && $data['wdaytwo'] == 0 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCSUNDAY'); ?></option><option value="1"<?php echo (count($data) && $data['wdaytwo'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCMONDAY'); ?></option><option value="2"<?php echo (count($data) && $data['wdaytwo'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCTUESDAY'); ?></option><option value="3"<?php echo (count($data) && $data['wdaytwo'] == 3 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCWEDNESDAY'); ?></option><option value="4"<?php echo (count($data) && $data['wdaytwo'] == 4 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCTHURSDAY'); ?></option><option value="5"<?php echo (count($data) && $data['wdaytwo'] == 5 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCFRIDAY'); ?></option><option value="6"<?php echo (count($data) && $data['wdaytwo'] == 6 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCSATURDAY'); ?></option></select></div>
						<div class="vrwdaycombodiv" id="vrwdaycombodivid" style="display: <?php echo (count($data) && !empty($data['wdaycombo']) && strlen($data['wdaycombo']) > 3 ? 'block' : 'none'); ?>;"><span class="vrwdaycombosp"><?php echo JText::_('VRNEWRESTRICTIONALLCOMBO'); ?></span><span class="vrwdaycombohelp"><?php echo JText::_('VRNEWRESTRICTIONALLCOMBOHELP'); ?></span>
						<p class="vrwdaycombop"><label for="vrccomboa" style="display: inline-block; vertical-align: top;"><span id="vrccomboa1"><?php echo strlen($vrcra1) ? $arrwdays[intval($vrcra1)] : ''; ?></span> - <span id="vrccomboa2"><?php echo strlen($vrcra2) ? $arrwdays[intval($vrcra2)] : ''; ?></span></label> <input type="checkbox" name="comboa" id="vrccomboa" value="<?php echo strlen($vrcra1) ? $vrcra1.'-'.$vrcra2 : ''; ?>"<?php echo (strlen($vrcra1) && $vrccomboparts[0] == $vrcra1.'-'.$vrcra2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/></p>
						<p class="vrwdaycombop"><label for="vrccombob" style="display: inline-block; vertical-align: top;"><span id="vrccombob1"><?php echo strlen($vrcrb1) ? $arrwdays[intval($vrcrb1)] : ''; ?></span> - <span id="vrccombob2"><?php echo strlen($vrcrb2) ? $arrwdays[intval($vrcrb2)] : ''; ?></span></label> <input type="checkbox" name="combob" id="vrccombob" value="<?php echo strlen($vrcrb1) ? $vrcrb1.'-'.$vrcrb2 : ''; ?>"<?php echo (strlen($vrcrb1) && $vrccomboparts[1] == $vrcrb1.'-'.$vrcrb2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/></p>
						<p class="vrwdaycombop"><label for="vrccomboc" style="display: inline-block; vertical-align: top;"><span id="vrccomboc1"><?php echo strlen($vrcrc1) ? $arrwdays[intval($vrcrc1)] : ''; ?></span> - <span id="vrccomboc2"><?php echo strlen($vrcrc2) ? $arrwdays[intval($vrcrc2)] : ''; ?></span></label> <input type="checkbox" name="comboc" id="vrccomboc" value="<?php echo strlen($vrcrc1) ? $vrcrc1.'-'.$vrcrc2 : ''; ?>"<?php echo (strlen($vrcrc1) && $vrccomboparts[2] == $vrcrc1.'-'.$vrcrc2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/></p>
						<p class="vrwdaycombop"><label for="vrccombod" style="display: inline-block; vertical-align: top;"><span id="vrccombod1"><?php echo strlen($vrcrd1) ? $arrwdays[intval($vrcrd1)] : ''; ?></span> - <span id="vrccombod2"><?php echo strlen($vrcrd2) ? $arrwdays[intval($vrcrd2)] : ''; ?></span></label> <input type="checkbox" name="combod" id="vrccombod" value="<?php echo strlen($vrcrd1) ? $vrcrd1.'-'.$vrcrd2 : ''; ?>"<?php echo (strlen($vrcrd1) && $vrccomboparts[3] == $vrcrd1.'-'.$vrcrd2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/></p>
						</div>
					</td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRNEWRESTRICTIONMINLOS'); ?>*</b></td>
					<td><input type="number" name="minlos" value="<?php echo count($data) ? $data['minlos'] : '1'; ?>" min="1" size="3" style="width: 60px !important;" /></td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"><?php echo $vrc_app->createPopover(array('title' => JText::_('VRNEWRESTRICTIONMULTIPLYMINLOS'), 'content' => JText::_('VRNEWRESTRICTIONMULTIPLYMINLOSHELP'))); ?> <b><?php echo JText::_('VRNEWRESTRICTIONMULTIPLYMINLOS'); ?></b></td>
					<td><input type="checkbox" name="multiplyminlos" value="1"<?php echo (count($data) && $data['multiplyminlos'] == 1 ? ' checked="checked"' : ''); ?>/></td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRNEWRESTRICTIONMAXLOS'); ?></b></td>
					<td><input type="number" name="maxlos" value="<?php echo count($data) ? $data['maxlos'] : '0'; ?>" min="0" size="3" style="width: 60px !important;" /></td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRNEWRESTRICTIONSETCTA'); ?></b></td>
					<td><input type="checkbox" name="cta" value="1" onclick="vrcToggleCta();"<?php echo count($cur_setcta) > 0 ? ' checked="checked"' : ''; ?>/><div id="vrcrestrctadiv" style="display: <?php echo count($cur_setcta) > 0 ? ' block' : 'none'; ?>;"><span class="vrcrestrcarssp"><?php echo JText::_('VRNEWRESTRICTIONWDAYSCTA'); ?></span><?php echo $ctasel; ?></div></td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRNEWRESTRICTIONSETCTD'); ?></b></td>
					<td><input type="checkbox" name="ctd" value="1" onclick="vrcToggleCtd();"<?php echo count($cur_setctd) > 0 ? ' checked="checked"' : ''; ?>/><div id="vrcrestrctddiv" style="display: <?php echo count($cur_setctd) > 0 ? ' block' : 'none'; ?>;"><span class="vrcrestrcarssp"><?php echo JText::_('VRNEWRESTRICTIONWDAYSCTD'); ?></span><?php echo $ctdsel; ?></div></td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRNEWRESTRICTIONALLCARS'); ?></b></td>
					<td><input type="checkbox" name="allcars" value="1" onclick="vrcToggleCars();"<?php echo ((count($data) && $data['allcars'] == 1) || !count($data) ? ' checked="checked"' : ''); ?>/><div id="vrcrestrcarsdiv" style="display: <?php echo ((count($data) && $data['allcars'] == 1) || !count($data) ? 'none' : 'block'); ?>;"><span class="vrcrestrcarssp"><?php echo JText::_('VRNEWRESTRICTIONCARSAFF'); ?></span><?php echo $carsel; ?></div></td>
				</tr>
			</tbody>
		</table>
	</fieldset>
<?php
if (count($data)) :
?>
	<input type="hidden" name="where" value="<?php echo $data['id']; ?>">
<?php
endif;
?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikrentcar">
</form>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#dfrom').val('<?php echo $dfromval; ?>').attr('data-alt-value', '<?php echo $dfromval; ?>');
	jQuery('#dto').val('<?php echo $dtoval; ?>').attr('data-alt-value', '<?php echo $dtoval; ?>');
});
<?php
if (count($data) && strlen($data['wday']) > 0 && strlen($data['wdaytwo']) > 0) {
	?>
vrComboArrWDay();
	<?php
}
?>
</script>