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
$cats = $this->cats;
$carats = $this->carats;
$optionals = $this->optionals;
$places = $this->places;

$vrc_app = VikRentCar::getVrcApplication();
$document = JFactory::getDocument();
$document->addStyleSheet(VRC_SITE_URI.'resources/jquery.fancybox.css');
JHtml::_('script', VRC_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);
$currencysymb=VikRentCar::getCurrencySymb(true);
$arrcats = array();
$arrcarats = array();
$arropts = array();
if (count($row)) {
	$oldcats = explode(";", $row['idcat']);
	foreach ($oldcats as $oc) {
		if (!empty($oc)) {
			$arrcats[$oc] = $oc;
		}
	}
	$oldcarats = explode(";", $row['idcarat']);
	foreach ($oldcarats as $ocr) {
		if (!empty($ocr)) {
			$arrcarats[$ocr] = $ocr;
		}
	}
	$oldopts = explode(";", $row['idopt']);
	foreach ($oldopts as $oopt) {
		if (!empty($oopt)) {
			$arropts[$oopt] = $oopt;
		}
	}
}
if (is_array($cats)) {
	$wcats = "<tr><td class=\"vrc-config-param-cell\" width=\"200\"> <b>".JText::_('VRNEWCARONE')."</b> </td><td>";
	$wcats .= "<select name=\"ccat[]\" multiple=\"multiple\" size=\"".(count($cats) + 1)."\">";
	foreach ($cats as $cat) {
		$wcats .= "<option value=\"".$cat['id']."\"".(array_key_exists($cat['id'], $arrcats) ? " selected=\"selected\"" : "").">".$cat['name']."</option>\n";
	}
	$wcats .= "</select></td></tr>\n";
} else {
	$wcats = "";
}
if (is_array($places)) {
	$wplaces = "<tr><td class=\"vrc-config-param-cell\" width=\"200\"> <b>".JText::_('VRNEWCARTWO')."</b> </td><td>";
	$wretplaces = "<tr><td class=\"vrc-config-param-cell\" width=\"200\"> <b>".JText::_('VRNEWCARDROPLOC')."</b> </td><td>";
	$wplaces .= "<select name=\"cplace[]\" id=\"cplace\" multiple=\"multiple\" size=\"".(count($places) + 1)."\" onchange=\"vrcSelDropLocation();\">";
	$wretplaces .= "<select name=\"cretplace[]\" id=\"cretplace\" multiple=\"multiple\" size=\"".(count($places) + 1)."\">";
	$actplac = count($row) ? explode(";", $row['idplace']) : array();
	$actretplac = count($row) ? explode(";", $row['idretplace']) : array();
	foreach ($places as $place) {
		$wplaces .= "<option value=\"".$place['id']."\"".(in_array($place['id'], $actplac) ? " selected=\"selected\"" : "").">".$place['name']."</option>\n";
		$wretplaces .= "<option value=\"".$place['id']."\"".(in_array($place['id'], $actretplac) ? " selected=\"selected\"" : "").">".$place['name']."</option>\n";
	}
	$wplaces .= "</select></td></tr>\n";
	$wretplaces .= "</select></td></tr>\n";
} else {
	$wplaces = "";
	$wretplaces = "";
}
if (is_array($carats)) {
	$wcarats = "<tr><td class=\"vrc-config-param-cell\" width=\"200\"> <b>".JText::_('VRNEWCARTHREE')."</b> </td><td>";
	$wcarats .= "<table><tr><td valign=\"top\">";
	$nn = 0;
	$jj = 0;
	foreach ($carats as $carat) {
		$wcarats .= "<div class=\"vrc-mngcar-serv-entry\">
        <select  name=\"ccarat[]\" id=\"carat".$carat['id']."\" >";
        $wcarats .= "<option value=''></option>";
		foreach ( explode(";",$carat["value"]) as $val){
            $wcarats .= "<option value='".$carat['id']."|". $val."' ".(array_key_exists($carat['id']."|".$val, $arrcarats) ? " selected=\"selected\"" : "")."            >".$val."</option>";
        }
        $wcarats .= "</select><label for=\"carat".$carat['id']."\">".$carat['name']."</label></div>\n";
		$nn++;
		if (($nn % 3) == 0) {
			$jj++;
			if (($jj % 3) == 0) {
				$wcarats .= "</td></tr><td valign=\"top\">";
			} else {
				$wcarats .= "</td><td valign=\"top\">\n";
			}
		}
	}
	$wcarats .= "</td></tr></table>\n";
	$wcarats .= "</td></tr>\n";
} else {
	$wcarats = "";
}
if (is_array($optionals)) {
	$woptionals = "<tr><td class=\"vrc-config-param-cell\" width=\"200\"> <b>".JText::_('VRNEWCARFOUR')."</b> </td><td>";
	$woptionals .= "<table><tr><td valign=\"top\">";
	$nn = 0;
	$jj = 0;
	foreach ($optionals as $optional) {
		$woptionals .= "<div class=\"vrc-mngcar-serv-entry\"><input type=\"checkbox\" name=\"coptional[]\" id=\"opt".$optional['id']."\" value=\"".$optional['id']."\"".(array_key_exists($optional['id'], $arropts) ? " checked=\"checked\"" : "")."/> <label for=\"opt".$optional['id']."\">".$optional['name']." ".$currencysymb."".$optional['cost']."</label></div>\n";
		$nn++;
		if (($nn % 3) == 0) {
			$jj++;
			if (($jj % 3) == 0) {
				$woptionals .= "</td></tr><td valign=\"top\">";
			} else {
				$woptionals .= "</td><td valign=\"top\">\n";
			}
		}
	}
	$woptionals .= "</td></tr></table>\n";
	$woptionals .= "</td></tr>\n";
} else {
	$woptionals = "";
}
//more images
$morei = count($row) ? explode(';;', $row['moreimgs']) : array();
$actmoreimgs = "";
if (count($morei) > 0) {
	$notemptymoreim = false;
	foreach ($morei as $ki => $mi) {
		if (!empty($mi)) {
			$notemptymoreim = true;
			$actmoreimgs .= '<div style="float: left; margin-right: 5px;">';
			$actmoreimgs .= '<img src="'.VRC_ADMIN_URI.'resources/thumb_'.$mi.'" class="maxfifty"/>';
			$actmoreimgs .= '<a style="margin-left: -20px;width: 30px;z-index: 100;" href="index.php?option=com_vikrentcar&task=removemoreimgs&carid='.$row['id'].'&imgind='.$ki.'"><img src="'.VRC_ADMIN_URI.'resources/images/remove.png" style="border: 0;"/></a>';
			$actmoreimgs .= '</div>';
		}
	}
	if ($notemptymoreim) {
		$actmoreimgs .= '<br clear="all"/>';
	}
}
//end more images
$car_params = count($row) && !empty($row['params']) ? json_decode($row['params'], true) : array('sdailycost' => '', 'email' => '', 'custptitle' => '', 'custptitlew' => '', 'metakeywords' => '', 'metadescription' => '', 'shourlycal' => '');
if (!array_key_exists('features', $car_params)) {
	$car_params['features'] = array();
}
if (!array_key_exists('damages', $car_params)) {
	$car_params['damages'] = array();
	if (count($row)) {
		for ($i=1; $i <= $row['units']; $i++) {
			$car_params['damages'][$i] = array();
		}
	}
}
if (!(count($car_params['features']) > 0)) {
	$default_features = VikRentCar::getDefaultDistinctiveFeatures();
	if (count($row)) {
		for ($i=1; $i <= $row['units']; $i++) {
			$car_params['features'][$i] = $default_features;
		}
	}
}
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
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
function vrcSelDropLocation() {
	var picksel = document.getElementById('cplace');
	var dropsel = document.getElementById('cretplace');
	for (i = 0; i < picksel.length; i++) {
		if (picksel.options[i].selected == false) {
			if (dropsel.options[i].selected == true) {
				dropsel.options[i].selected = false;
			}
		} else {
			if (dropsel.options[i].selected == false) {
				dropsel.options[i].selected = true;
			}
		}
	}
}
function showResizeSelMore() {
	if (document.adminForm.autoresizemore.checked == true) {
		document.getElementById('resizeselmore').style.display='block';
	} else {
		document.getElementById('resizeselmore').style.display='none';
	}
	return true;
}
function addMoreImages() {
	var ni = document.getElementById('myDiv');
	var numi = document.getElementById('moreimagescounter');
	var num = (document.getElementById('moreimagescounter').value -1)+ 2;
	numi.value = num;
	var newdiv = document.createElement('div');
	var divIdName = 'my'+num+'Div';
	newdiv.setAttribute('id',divIdName);
	newdiv.innerHTML = '<input type=\'file\' name=\'cimgmore[]\' size=\'35\'/><br/>';
	ni.appendChild(newdiv);
}
jQuery.noConflict();
var cur_units = <?php echo count($row) ? $row['units'] : 1; ?>;
jQuery(document).ready(function() {
	jQuery('.vrc-features-btn').click(function() {
		jQuery(this).toggleClass('vrc-features-btn-active');
		jQuery('.vrc-distfeatures-block').fadeToggle();
	});
	jQuery('#vrc-units-inp').change(function() {
		var to_units = parseInt(jQuery(this).val());
		if (to_units > cur_units) {
			var diff_units = (to_units - cur_units);
			for (var i = 1; i <= diff_units; i++) {
				var unit_html = "<div class=\"vrc-cunit-features-cont\" id=\"cunit-features-"+(i + cur_units)+"\">"+
								"	<span class=\"vrc-cunit-num\"><?php echo addslashes(JText::_('VRCDISTFEATURECUNIT')); ?>"+(i + cur_units)+"</span>"+
								"	<div class=\"vrc-cunit-features\">"+
								"		<div class=\"vrc-cunit-feature\">"+
								"			<input type=\"text\" name=\"feature-name"+(i + cur_units)+"[]\" value=\"\" size=\"20\" placeholder=\"<?php echo JText::_('VRCDISTFEATURETXT'); ?>\"/>"+
								"			<input type=\"hidden\" name=\"feature-lang"+(i + cur_units)+"[]\" value=\"\"/>"+
								"			<input type=\"text\" name=\"feature-value"+(i + cur_units)+"[]\" value=\"\" size=\"20\" placeholder=\"<?php echo JText::_('VRCDISTFEATUREVAL'); ?>\"/>"+
								"			<span class=\"vrc-feature-remove\">&nbsp;</span>"+
								"		</div>"+
								"		<span class=\"vrc-feature-add\"><?php echo addslashes(JText::_('VRCDISTFEATUREADD')); ?></span>"+
								"	</div>"+
								"</div>";
				jQuery('.vrc-distfeatures-cont').append(unit_html);
			}
			cur_units = to_units;
		} else if (to_units < cur_units) {
			for (var i = cur_units; i > to_units; i--) {
				jQuery('#cunit-features-'+i).remove();
			}
			cur_units = to_units;
		}
	});
	jQuery(document.body).on('click', '.vrc-feature-add', function() {
		var cfeature_id = jQuery(this).parent('div').parent('div').attr('id').split('cunit-features-');
		if (cfeature_id[1].length) {
			jQuery(this).before("<div class=\"vrc-cunit-feature\">"+
								"	<input type=\"text\" name=\"feature-name"+cfeature_id[1]+"[]\" value=\"\" size=\"20\" placeholder=\"<?php echo JText::_('VRCDISTFEATURETXT'); ?>\"/>"+
								"	<input type=\"hidden\" name=\"feature-lang"+cfeature_id[1]+"[]\" value=\"\"/>"+
								"	<input type=\"text\" name=\"feature-value"+cfeature_id[1]+"[]\" value=\"\" size=\"20\" placeholder=\"<?php echo JText::_('VRCDISTFEATUREVAL'); ?>\"/>"+
								"	<span class=\"vrc-feature-remove\">&nbsp;</span>"+
								"</div>"
								);
		}
	});
	jQuery(document.body).on('click', '.vrc-feature-remove', function() {
		jQuery(this).parent('div').remove();
	});
	jQuery(document.body).on('click', '.vrc-open-damages', function() {
		var cunit_id = jQuery(this).parent('div').attr('id').split('cunit-features-');
		if (cunit_id[1].length && jQuery('#vrc-feature-damage-block-'+cunit_id[1])) {
			var cname = jQuery('#cname').val();
			jQuery.fancybox({
				"href" : '#vrc-feature-damage-block-'+cunit_id[1],
				"title" : (cname.length ? cname+' - ' : '')+jQuery(this).parent('div').find('.vrc-cunit-num').text() + ' - <?php echo addslashes(JText::_('VRCDISTFEATURECDAMAGES')); ?>',
				"helpers": {
					"overlay": {
						"locked": false
					}
				},"padding": 0
			});
		}
	});
	jQuery(document.body).on('click', '.vrc-feature-damage-imgcont img', function(e) {
		var click_x = (e.pageX - jQuery(this).parent('div').offset().left);
		var click_y = (e.pageY - jQuery(this).parent('div').offset().top);
		var cunit_id = jQuery(this).parent('div').closest('div.vrc-feature-damage-block').attr('id').split('vrc-feature-damage-block-');
		if (cunit_id[1].length) {
			jQuery('#vrc-no-damage-'+cunit_id[1]).remove();
			var tot_damages = jQuery('.vrc-feature-car-damage-'+cunit_id[1]).length;
			var damage_ind = !(tot_damages > 0) ? 1 : (tot_damages + 1);
			jQuery(this).parent('div').append("<span class=\"vrc-feature-damage-circle\" id=\"vrc-damage-circle-"+cunit_id[1]+"-"+damage_ind+"\" style=\"left: "+click_x+"px; top: "+click_y+"px;\">"+damage_ind+"</span>");
			jQuery(this).parent('div').next('div.vrc-feature-damage-actions').prepend("<div class=\"vrc-feature-car-damage vrc-feature-car-damage-"+cunit_id[1]+"\" id=\"vrc-feature-car-damage-"+cunit_id[1]+"-"+damage_ind+"\">"+
																						"	<span class=\"vrc-feature-car-damage-count\">"+damage_ind+"</span>"+
																						"	<span class=\"vrc-feature-damage-remove\">&nbsp;</span>"+
																						"	<div class=\"vrc-feature-car-damage-details\">"+
																						"		<span class=\"vrc-feature-car-damage-detail\"><?php echo addslashes(JText::_('VRCDISTFEATURECDAMAGENOTES')); ?></span>"+
																						"		<span class=\"vrc-feature-car-damage-cont\"><textarea name=\"car-"+cunit_id[1]+"-damage[]\"></textarea></span>"+
																						"		<input type=\"hidden\" name=\"car-"+cunit_id[1]+"-damage-x[]\" value=\""+click_x+"\"/>"+
																						"		<input type=\"hidden\" name=\"car-"+cunit_id[1]+"-damage-y[]\" value=\""+click_y+"\"/>"+
																						"	</div>"+
																						"</div>");
		}
	});
	jQuery(document.body).on('click', '.vrc-feature-damage-remove', function() {
		var id_damage = jQuery(this).parent('div').attr('id').split('vrc-feature-car-damage-');
		var cunit_id = id_damage[1].split('-');
		jQuery('#vrc-damage-circle-'+id_damage[1]).remove();
		jQuery(this).parent('div').remove();
		var tot_damages = jQuery('.vrc-feature-car-damage-'+cunit_id[0]).length;
		if (tot_damages < 1) {
			jQuery('#vrc-feature-damage-block-'+cunit_id[0]).find('div.vrc-feature-damage-actions').html("<span class=\"vrc-no-damage\" id=\"vrc-no-damage-"+cunit_id[0]+"\"><?php echo addslashes(JText::_('VRCDISTFEATURENODAMAGE')); ?></span>");
		}
	});
});
</script>
<input type="hidden" value="0" id="moreimagescounter" />

<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<table class="admintable table">
		<tr>
			<td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWCARFIVE'); ?></b> </td>
			<td><input type="text" name="cname" id="cname" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></td>
		</tr>
		<tr>
			<td class="vrc-config-param-cell" width="200" valign="top"> <b><?php echo JText::_('VRNEWCARSIX'); ?></b> </td>
			<td><?php echo (count($row) && !empty($row['img']) && file_exists(VRC_ADMIN_PATH.DS.'resources'.DS.$row['img']) ? "<img src=\"".VRC_ADMIN_URI."resources/".$row['img']."\" class=\"maxfifty\"/> &nbsp;" : ""); ?><input type="file" name="cimg" size="35"/><br/><label for="autoresize" style="display: inline-block;"><?php echo JText::_('VRNEWOPTNINE'); ?></label> <input type="checkbox" id="autoresize" name="autoresize" value="1" onclick="showResizeSel();"/> <span id="resizesel" style="display: none;">&nbsp;<?php echo JText::_('VRNEWOPTTEN'); ?>: <input type="text" name="resizeto" value="250" size="3"/> px</span></td>
		</tr>
		<tr>
			<td class="vrc-config-param-cell" width="200" valign="top"> <b><?php echo JText::_('VRMOREIMAGES'); ?></b><br/>&nbsp;&nbsp;<a href="javascript: void(0);" onclick="addMoreImages();"><?php echo JText::_('VRADDIMAGES'); ?></a></td>
			<td><?php echo $actmoreimgs; ?><input type="file" name="cimgmore[]" size="35"/><div id="myDiv" style="display: block;"></div><label for="autoresizemore" style="display: inline-block;"><?php echo JText::_('VRRESIZEIMAGES'); ?></label> <input type="checkbox" id="autoresizemore" name="autoresizemore" value="1" onclick="showResizeSelMore();"/> <span id="resizeselmore" style="display: none;">&nbsp;<?php echo JText::_('VRNEWOPTTEN'); ?>: <input type="text" name="resizetomore" value="600" size="3"/> px</span></td>
		</tr>
		<?php echo $wcats; ?>
		<tr>
			<td class="vrc-config-param-cell" width="200" valign="top"> <b><?php echo JText::_('VRCSHORTDESCRIPTIONCAR'); ?></b> </td>
			<td><textarea name="short_info" rows="4" cols="60"><?php echo count($row) ? $row['short_info'] : ''; ?></textarea></td>
		</tr>
		<tr>
			<td class="vrc-config-param-cell" width="200" valign="top"> <b><?php echo JText::_('VRNEWCARSEVEN'); ?></b> </td>
			<td><?php echo $editor->display( "cdescr", (count($row) ? $row['info'] : ''), 400, 200, 70, 20 ); ?></td>
		</tr>
		<?php echo $wplaces; ?>
		<?php echo $wretplaces; ?>
		<?php echo $wcarats; ?>
		<?php echo $woptionals; ?>
		<tr>
			<td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWCARNINE'); ?></b> </td>
			<td><input type="number" min="1" name="units" id="vrc-units-inp" value="<?php echo count($row) ? $row['units'] : ''; ?>" size="3" onfocus="this.select();"/><span class="vrc-features-btn"><?php echo JText::_('VRCDISTFEATURESMNG'); ?></span></td>
		</tr>
		<tr>
			<td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRCUSTSTARTINGFROM'); ?></b> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCUSTSTARTINGFROM'), 'content' => JText::_('VRCUSTSTARTINGFROMHELP'))); ?></td>
			<td><input type="number" step="any" name="startfrom" value="<?php echo count($row) ? $row['startfrom'] : ''; ?>"/> <?php echo $currencysymb; ?></td>
		</tr>
		<tr>
			<td class="vrc-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWCAREIGHT'); ?></b> </td>
			<td><?php echo $vrc_app->printYesNoButtons('cavail', JText::_('VRYES'), JText::_('VRNO'), ((count($row) && intval($row['avail']) == 1) || !count($row) ? 'yes' : 0), 'yes', 0); ?></td>
		</tr>
	</table>

	<fieldset class="adminform">
		<legend class="adminlegend"><?php echo JText::_('VRCPARAMSCAR'); ?></legend>
		<table cellspacing="1" class="admintable table">
			<tbody>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCPARAMDAILYCOST'); ?></b> </td>
					<td><?php echo $vrc_app->printYesNoButtons('sdailycost', JText::_('VRYES'), JText::_('VRNO'), (count($row) ? (int)$car_params['sdailycost'] : 0), 1, 0); ?></td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCPARAMHOURLYCAL'); ?></b> </td>
					<td><?php echo $vrc_app->printYesNoButtons('shourlycal', JText::_('VRYES'), JText::_('VRNO'), (count($row) ? (int)$car_params['shourlycal'] : 0), 1, 0); ?></td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCPARAMREQINFO'); ?></b> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPARAMREQINFO'), 'content' => JText::_('VRCPARAMREQINFOHELP'))); ?></td>
					<td><?php echo $vrc_app->printYesNoButtons('reqinfo', JText::_('VRYES'), JText::_('VRNO'), (count($row) && isset($car_params['reqinfo']) ? (int)$car_params['reqinfo'] : 0), 1, 0); ?></td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCPARAMCAREMAIL'); ?></b> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPARAMCAREMAIL'), 'content' => JText::_('VRCPARAMCAREMAILHELP'))); ?></td>
					<td><input type="text" id="car_email" name="email" value="<?php echo count($row) ? $car_params['email'] : ''; ?>"/></td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCPARAMPAGETITLE'); ?></b> </td>
					<td>
						<input type="text" id="custptitle" name="custptitle" value="<?php echo count($row) ? $car_params['custptitle'] : ''; ?>"/>
						<span>
							<select name="custptitlew">
								<option value="before"<?php echo count($row) && $car_params['custptitlew'] == 'before' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCPARAMPAGETITLEBEFORECUR'); ?></option>
								<option value="after"<?php echo count($row) && $car_params['custptitlew'] == 'after' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCPARAMPAGETITLEAFTERCUR'); ?></option>
								<option value="replace"<?php echo count($row) && $car_params['custptitlew'] == 'replace' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCPARAMPAGETITLEREPLACECUR'); ?></option>
							</select>
						</span>
					</td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCPARAMKEYWORDSMETATAG'); ?></b> </td>
					<td><textarea name="metakeywords" id="metakeywords" rows="3" cols="40"><?php echo count($row) ? $car_params['metakeywords'] : ''; ?></textarea></td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCPARAMDESCRIPTIONMETATAG'); ?></b> </td>
					<td><textarea name="metadescription" id="metadescription" rows="4" cols="40"><?php echo count($row) ? $car_params['metadescription'] : ''; ?></textarea></td>
				</tr>
				<tr>
					<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCARSEFALIAS'); ?></b> </td>
					<td><input type="text" id="sefalias" name="sefalias" value="<?php echo count($row) ? $row['alias'] : ''; ?>" placeholder="city-car-group-a"/></td>
				</tr>
			</tbody>
		</table>
	</fieldset>

	<div class="vrc-distfeatures-block">
		<div class="vrc-distfeatures-inner">
			<fieldset>
				<legend><?php echo JText::_('VRCDISTFEATURES'); ?></legend>
				<div class="vrc-distfeatures-cont">
				<?php
				for ($i=1; $i <= (count($row) ? $row['units'] : 1); $i++) {
					$damage_img = VRC_SITE_URI.'helpers/car_damages/car_inspection.png';
				?>
					<div class="vrc-cunit-features-cont" id="cunit-features-<?php echo $i; ?>">
						<span class="vrc-cunit-num"><?php echo JText::_('VRCDISTFEATURECUNIT'); ?><?php echo $i; ?></span>
						<span class="vrc-open-damages"><?php echo JText::_('VRCDISTFEATURECDAMAGES'); ?></span>
						<div class="vrc-cunit-features">
					<?php
					if (array_key_exists($i, $car_params['features'])) {
						foreach ($car_params['features'][$i] as $fkey => $fval) {
							?>
							<div class="vrc-cunit-feature">
								<input type="text" name="feature-name<?php echo $i; ?>[]" value="<?php echo JText::_($fkey); ?>" size="20"/>
								<input type="hidden" name="feature-lang<?php echo $i; ?>[]" value="<?php echo $fkey; ?>"/>
								<input type="text" name="feature-value<?php echo $i; ?>[]" value="<?php echo $fval; ?>" size="20"/>
								<span class="vrc-feature-remove">&nbsp;</span>
							</div>
							<?php
						}
					}
					?>
							<span class="vrc-feature-add"><?php echo JText::_('VRCDISTFEATUREADD'); ?></span>
						</div>
					<?php
					if (count($row)) {
					?>
						<div class="vrc-feature-damage-block" id="vrc-feature-damage-block-<?php echo $i; ?>">
							<div class="vrc-feature-damage-imgcont">
								<img src="<?php echo $damage_img; ?>"/>
						<?php
						$tot_dmg = count($car_params['damages'][$i]);
						if ($tot_dmg > 0) {
							$dk = $tot_dmg;
							foreach ($car_params['damages'][$i] as $damage) {
								?>
								<span class="vrc-feature-damage-circle" id="vrc-damage-circle-<?php echo $i; ?>-<?php echo $dk; ?>" style="left: <?php echo $damage['x']; ?>px; top: <?php echo $damage['y']; ?>px;"><?php echo $dk; ?></span>
								<?php
								$dk--;
							}
						}
						?>
							</div>
							<div class="vrc-feature-damage-actions">
						<?php
						$tot_dmg = count($car_params['damages'][$i]);
						if ($tot_dmg > 0) {
							$dk = $tot_dmg;
							foreach ($car_params['damages'][$i] as $damage) {
								?>
								<div class="vrc-feature-car-damage vrc-feature-car-damage-<?php echo $i; ?>" id="vrc-feature-car-damage-<?php echo $i; ?>-<?php echo $dk; ?>">
									<span class="vrc-feature-car-damage-count"><?php echo $dk; ?></span>
									<span class="vrc-feature-damage-remove">&nbsp;</span>
									<div class="vrc-feature-car-damage-details">
										<span class="vrc-feature-car-damage-detail"><?php echo JText::_('VRCDISTFEATURECDAMAGENOTES'); ?></span>
										<span class="vrc-feature-car-damage-cont"><textarea name="car-<?php echo $i; ?>-damage[]"><?php echo $damage['notes']; ?></textarea></span>
										<input type="hidden" name="car-<?php echo $i; ?>-damage-x[]" value="<?php echo $damage['x']; ?>" />
										<input type="hidden" name="car-<?php echo $i; ?>-damage-y[]" value="<?php echo $damage['y']; ?>" />
									</div>
								</div>
								<?php
								$dk--;
							}
						} else {
							?>
								<span class="vrc-no-damage" id="vrc-no-damage-<?php echo $i; ?>"><?php echo JText::_('VRCDISTFEATURENODAMAGE'); ?></span>
							<?php
						}
						?>
							</div>
						</div>
					<?php
					}
					?>
					</div>
				<?php
				}
				?>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="">
<?php
if (count($row)) {
	?>
	<input type="hidden" name="whereup" value="<?php echo $row['id']; ?>">
	<input type="hidden" name="actmoreimgs" value="<?php echo $row['moreimgs']; ?>">
	<?php
}
?>
	<input type="hidden" name="option" value="com_vikrentcar" />
</form>
