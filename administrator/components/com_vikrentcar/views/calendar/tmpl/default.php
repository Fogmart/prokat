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

$car = $this->car;
$msg = $this->msg;
$allc = $this->allc;
$payments = $this->payments;
$busy = $this->busy;
$vmode = $this->vmode;
$pickuparr = $this->pickuparr;
$dropoffarr = $this->dropoffarr;

//header
$dbo = JFactory::getDBO();
$vrc_app = VikRentCar::getVrcApplication()->loadSelect2();
$document = JFactory::getDocument();
$document->addStyleSheet(VRC_SITE_URI.'resources/jquery-ui.min.css');
JHtml::_('jquery.framework', true, true);
JHtml::_('script', VRC_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);
$vrc_df = VikRentCar::getDateFormat(true);
$juidf = $vrc_df == "%d/%m/%Y" ? 'dd/mm/yy' : ($vrc_df == "%m/%d/%Y" ? 'mm/dd/yy' : 'yy/mm/dd');
$pritiro = VikRequest::getString('ritiro', '', 'request');
if (!empty($pritiro)) {
	$pritiro = date(str_replace('%', '', $vrc_df), strtotime($pritiro));
}
$pconsegna = VikRequest::getString('consegna', '', 'request');
if (!empty($pconsegna)) {
	$pconsegna = date(str_replace('%', '', $vrc_df), strtotime($pconsegna));
}
$ptmpl = VikRequest::getString('tmpl', '', 'request');
$ldecl = '
jQuery(function($){'."\n".'
	$.datepicker.regional["vikrentcar"] = {'."\n".'
		closeText: "'.JText::_('VRCJQCALDONE').'",'."\n".'
		prevText: "'.JText::_('VRCJQCALPREV').'",'."\n".'
		nextText: "'.JText::_('VRCJQCALNEXT').'",'."\n".'
		currentText: "'.JText::_('VRCJQCALTODAY').'",'."\n".'
		monthNames: ["'.JText::_('VRMONTHONE').'","'.JText::_('VRMONTHTWO').'","'.JText::_('VRMONTHTHREE').'","'.JText::_('VRMONTHFOUR').'","'.JText::_('VRMONTHFIVE').'","'.JText::_('VRMONTHSIX').'","'.JText::_('VRMONTHSEVEN').'","'.JText::_('VRMONTHEIGHT').'","'.JText::_('VRMONTHNINE').'","'.JText::_('VRMONTHTEN').'","'.JText::_('VRMONTHELEVEN').'","'.JText::_('VRMONTHTWELVE').'"],'."\n".'
		monthNamesShort: ["'.mb_substr(JText::_('VRMONTHONE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTWO'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTHREE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHFOUR'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHFIVE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHSIX'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHSEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHEIGHT'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHNINE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHELEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTWELVE'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNames: ["'.JText::_('VRCSUNDAY').'", "'.JText::_('VRCMONDAY').'", "'.JText::_('VRCTUESDAY').'", "'.JText::_('VRCWEDNESDAY').'", "'.JText::_('VRCTHURSDAY').'", "'.JText::_('VRCFRIDAY').'", "'.JText::_('VRCSATURDAY').'"],'."\n".'
		dayNamesShort: ["'.mb_substr(JText::_('VRCSUNDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCMONDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCTUESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCWEDNESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCTHURSDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCFRIDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCSATURDAY'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNamesMin: ["'.mb_substr(JText::_('VRCSUNDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCMONDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCTUESDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCWEDNESDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCTHURSDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCFRIDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCSATURDAY'), 0, 2, 'UTF-8').'"],'."\n".'
		weekHeader: "'.JText::_('VRCJQCALWKHEADER').'",'."\n".'
		dateFormat: "'.$juidf.'",'."\n".'
		firstDay: '.VikRentCar::getFirstWeekDay().','."\n".'
		isRTL: false,'."\n".'
		showMonthAfterYear: false,'."\n".'
		yearSuffix: ""'."\n".'
	};'."\n".'
	$.datepicker.setDefaults($.datepicker.regional["vikrentcar"]);'."\n".'
});';
$document->addScriptDeclaration($ldecl);
$fquick = "";
if (strlen($msg) > 0 && intval($msg) > 0) {
	$fquick .= "<br/><p class=\"successmade\" style=\"margin-top: -15px;\">".JText::_('VRBOOKMADE')." - <a href=\"index.php?option=com_vikrentcar&task=editorder&cid[]=".intval($msg)."\"><i class=\"vrcicn-eye\"></i> ".JText::_('VRCVIEWBOOKINGDET')."</a></p>";

} elseif (strlen($msg) > 0 && $msg == "0") {
	$fquick .= "<br/><p class=\"err\" style=\"margin-top: -15px;\">".JText::_('VRBOOKNOTMADE')."</p>";
}
$fquick .= "<form name=\"newb\" method=\"post\" action=\"index.php?option=com_vikrentcar\" onsubmit=\"javascript: if (!document.newb.pickupdate.value.match(/\S/)){alert('".JText::_('VRMSGTHREE')."'); return false;} if (!document.newb.releasedate.value.match(/\S/)){alert('".JText::_('VRMSGFOUR')."'); return false;} return true;\">";

$timeopst = VikRentCar::getTimeOpenStore();
if (is_array($timeopst) && $timeopst[0]!=$timeopst[1]) {
	$opent = VikRentCar::getHoursMinutes($timeopst[0]);
	$closet = VikRentCar::getHoursMinutes($timeopst[1]);
	$i = $opent[0];
	$j = $closet[0];
} else {
	$i = 0;
	$j = 23;
}
$hours = $minutes = '';
while ($i <= $j) {
	if ($i < 10) {
		$i = "0".$i;
	} else {
		$i = $i;
	}
	$hours .= "<option value=\"".$i."\">".$i."</option>\n";
	$i++;
}
for ($i = 0; $i < 60; $i++) {
	if ($i < 10) {
		$i = "0".$i;
	} else {
		$i = $i;
	}
	$minutes .= "<option value=\"".$i."\">".$i."</option>\n";
}

$formatparts = explode(':', VikRentCar::getNumberFormatData());
$currencysymb = VikRentCar::getCurrencySymb(true);
$selpayments = '<select name="payment"><option value="">'.JText::_('VRCQUICKRESNONE').'</option>';
if (is_array($payments) && @count($payments) > 0) {
	foreach ($payments as $pay) {
		$selpayments .= '<option value="'.$pay['id'].'">'.$pay['name'].'</option>';
	}
}
$selpayments .= '</select>';
//Custom Fields
$cfields_cont = '';
$q = "SELECT * FROM `#__vikrentcar_custfields` ORDER BY `#__vikrentcar_custfields`.`ordering` ASC;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$all_cfields = $dbo->loadAssocList();
	$q = "SELECT * FROM `#__vikrentcar_countries` ORDER BY `#__vikrentcar_countries`.`country_name` ASC;";
	$dbo->setQuery($q);
	$dbo->execute();
	$all_countries = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
	foreach ($all_cfields as $cfield) {
		if ($cfield['type'] == 'text') {
			$cfields_cont .= '<div class="vrc-calendar-cfield-entry"><label for="cfield'.$cfield['id'].'" data-fieldid="'.$cfield['id'].'">'.JText::_($cfield['name']).'</label><span><input type="text" id="cfield'.$cfield['id'].'" data-isemail="'.($cfield['isemail'] == 1 ? '1' : '0').'" data-isnominative="'.($cfield['isnominative'] == 1 ? '1' : '0').'" data-isphone="'.($cfield['isphone'] == 1 ? '1' : '0').'" value="" size="35"/></span></div>'."\n";
		} elseif ($cfield['type'] == 'textarea') {
			$cfields_cont .= '<div class="vrc-calendar-cfield-entry"><label for="cfield'.$cfield['id'].'" data-fieldid="'.$cfield['id'].'">'.JText::_($cfield['name']).'</label><span><textarea id="cfield'.$cfield['id'].'" rows="4" cols="35"></textarea></span></div>'."\n";
		} elseif ($cfield['type'] == 'country') {
			$cfields_cont .= '<div class="vrc-calendar-cfield-entry"><label for="cfield'.$cfield['id'].'" data-fieldid="'.$cfield['id'].'">'.JText::_($cfield['name']).'</label><span><select id="cfield'.$cfield['id'].'"><option value=""> </option>'."\n";
			foreach ($all_countries as $country) {
				$cfields_cont .= '<option value="'.$country['country_name'].'" data-ccode="'.$country['country_3_code'].'">'.$country['country_name'].'</option>';
			}
			$cfields_cont .= '</select></span></div>'."\n";
		}
	}
}
//
$wiva = "";
$q = "SELECT * FROM `#__vikrentcar_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	foreach ($ivas as $kiv => $iv) {
		$wiva .= "<option value=\"".$iv['id']."\" data-aliqid=\"".$iv['id']."\"".($kiv < 1 ? ' selected="selected"' : '').">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']." - ".$iv['aliq']."%")."</option>\n";
	}
}

$fquick .= "<fieldset class=\"adminform\"><table cellspacing=\"1\" class=\"admintable table\"><tbody><tr><td width=\"200\" class=\"vrc-config-param-cell\"><strong>".JText::_('VRDATEPICKUP').":</strong> </td><td><div class=\"input-append\"><input type=\"text\" autocomplete=\"off\" name=\"pickupdate\" id=\"pickupdate\" size=\"10\" /><button type=\"button\" class=\"btn vrcdatepicker-trig-icon\"><span class=\"icon-calendar\"></span></button></div> <span style=\"display: inline-block; margin-left: 10px;\">".JText::_('VRAT')." <select name=\"pickuph\">".$hours."</select> : <select name=\"pickupm\">".$minutes."</select></td></tr>\n";
$fquick .= "<tr><td class=\"vrc-config-param-cell\"><strong>".JText::_('VRDATERELEASE').":</strong> </td><td><div class=\"input-append\"><input type=\"text\" autocomplete=\"off\" name=\"releasedate\" id=\"releasedate\" size=\"10\" /><button type=\"button\" class=\"btn vrcdatepicker-trig-icon\"><span class=\"icon-calendar\"></span></button></div> <span style=\"display: inline-block; margin-left: 10px;\">".JText::_('VRAT')." <select name=\"releaseh\">".$hours."</select> : <select name=\"releasem\">".$minutes."</select><span style=\"display: inline-block; margin-left: 25px; font-weight: bold;\" id=\"vrjstotnights\"></span></td></tr>";
$fquick .= "<tr><td class=\"vrc-config-param-cell\"><span class=\"vrclosecarsp\"><i class=\"fa fa-ban\"></i><label for=\"setclosed\"><strong>".JText::_('VRCSTOPRENTALS').":</strong></label></span> </td><td><input type=\"checkbox\" name=\"setclosed\" id=\"setclosed\" value=\"1\" onclick=\"javascript: vrcCloseRoom();\"/></td></tr>\n";
if (false && $car['units'] > 1) {
	$num_cars_vals = range(1, $car['units']);
	$num_cars_opts = '';
	foreach ($num_cars_vals as $nrv) {
		$num_cars_opts .= '<option value="'.$nrv.'">'.$nrv.'</option>'."\n";
	}
	$fquick .= "<tr><td class=\"vrc-config-param-cell\"><strong>".JText::_('VRPVIEWCARSEVEN').":</strong> </td><td><span id=\"vrspannumcars\"><select name=\"num_cars\">".$num_cars_opts."</select></span></td></tr>\n";
} else {
	$fquick .= '<input type="hidden" name="num_cars" value="1"/>';
}
// places
if (count($pickuparr) && count($dropoffarr)) {
	$pickopts = '';
	$dropopts = '';
	foreach ($pickuparr as $locv) {
		$pickopts .= '<option value="'.$locv['id'].'">'.$locv['name'].'</option>'."\n";
	}
	foreach ($dropoffarr as $locv) {
		$dropopts .= '<option value="'.$locv['id'].'">'.$locv['name'].'</option>'."\n";
	}
	$fquick .= "<tr><td class=\"vrc-config-param-cell\"><strong>".JText::_('VRCQUICKRESLOCATIONS').":</strong> </td><td><span class=\"vrc-quickres-selwrap\"><select name=\"pickuploc\" id=\"pickuploc\"><option></option>".$pickopts."</select></span><span class=\"vrc-quickres-selwrap\"><select name=\"dropoffloc\" id=\"dropoffloc\"><option></option>".$dropopts."</select></span></td></tr>\n";
}
//
$fquick .= "<tr><td class=\"vrc-config-param-cell\"><strong>".JText::_('VRCQUICKRESORDSTATUS').":</strong> </td><td><span id=\"vrspanbstat\"><select name=\"newstatus\"><option value=\"confirmed\">".JText::_('VRCONFIRMED')."</option><option value=\"standby\">".JText::_('VRSTANDBY')."</option></select></span></td></tr>\n";
$fquick .= "<tr><td class=\"vrc-config-param-cell\"><strong>".JText::_('VRCQUICKRESMETHODOFPAYMENT').":</strong> </td><td><span id=\"vrspanbpay\">".$selpayments."</span></td></tr>\n";
$fquick .= "<tr><td class=\"vrc-config-param-cell\">&nbsp;</td><td><span class=\"vrc-assign-customer\" id=\"vrfillcustfields\"><i class=\"fa fa-user-circle\"></i> <span>".JText::_('VRFILLCUSTFIELDS')."</span></span></td></tr>\n";
$fquick .= "<tr><td class=\"vrc-config-param-cell\"><strong>".JText::_('VRQRCUSTMAIL').":</strong> </td><td><span id=\"vrspancmail\"><input type=\"text\" name=\"custmail\" id=\"custmailfield\" value=\"\" size=\"25\"/></span></td></tr>\n";
$fquick .= "<tr><td class=\"vrc-config-param-cell\"><strong>".JText::_('VRCUSTINFO').":</strong> </td><td><textarea name=\"custdata\" id=\"vrcustdatatxtarea\" rows=\"5\" cols=\"70\" style=\"min-width: 300px;\"></textarea></td></tr>\n";
$fquick .= "<tr><td class=\"vrc-config-param-cell\"><strong>".JText::_('VRCRENTCUSTRATEPLANADD').":</strong> </td><td><span id=\"vrspcustcost\">".$currencysymb." <input name=\"cust_cost\" id=\"cust_cost\" value=\"\" onfocus=\"document.getElementById('taxid').style.display = 'inline-block';\" onkeyup=\"vrCalcDailyCost(this.value);\" onchange=\"vrCalcDailyCost(this.value);\" type=\"number\" step=\"any\" style=\"min-width: 75px; margin: 0 5px 0 0;\"><select name=\"taxid\" id=\"taxid\" style=\"display: none; margin: 0;\"><option value=\"\">".JText::_('VRNEWOPTFOUR')."</option>".$wiva."</select><span id=\"avg-daycost\" style=\"display: inline-block; margin-left: 15px; font-weight: bold;\"></span></span></td></tr>\n";
$fquick .= "<tr><td class=\"vrc-config-param-cell\">&nbsp;</td><td><button type=\"submit\" id=\"quickbsubmit\" class=\"btn btn-success btn-large\"><i class=\"icon-save\"></i> <span>".JText::_('VRMAKERESERV')."</span></button></td></tr>\n";
$fquick .= "</tbody></table></fieldset>";
if ($ptmpl == 'component') {
	$fquick .= "<input type=\"hidden\" name=\"tmpl\" value=\"component\" />\n";
}
$fquick .= "<input type=\"hidden\" name=\"customer_id\" value=\"\" id=\"customer_id_inpfield\"/><input type=\"hidden\" name=\"countrycode\" value=\"\" id=\"ccode_inpfield\"/><input type=\"hidden\" name=\"t_first_name\" value=\"\" id=\"t_first_name_inpfield\"/><input type=\"hidden\" name=\"t_last_name\" value=\"\" id=\"t_last_name_inpfield\"/><input type=\"hidden\" name=\"phone\" value=\"\" id=\"phonefield\"/><input type=\"hidden\" name=\"task\" value=\"calendar\"/><input type=\"hidden\" name=\"cid[]\" value=\"".$car['id']."\"/></form>\n";
//search customer
$search_funct = '<div class="vrc-calendar-cfields-search"><label for="vrc-searchcust" style="display: block;"><strong>'.JText::_('VRCSEARCHEXISTCUST').'</strong></label><span id="vrc-searchcust-loading"><i class="vrcicn-hour-glass"></i></span><input type="text" id="vrc-searchcust" autocomplete="off" value="" placeholder="'.JText::_('VRCSEARCHCUSTBY').'" size="35" /><div id="vrc-searchcust-res"></div></div>';
//
//custom fields
$fquick .= '<div class="vrc-calendar-cfields-filler-overlay"><a class="vrc-info-overlay-close" href="javascript: void(0);"></a><div class="vrc-calendar-cfields-filler"><h4>'.JText::_('VRCUSTINFO').'</h4>'.$search_funct.'<div class="vrc-calendar-cfields-inner">'.$cfields_cont.'</div><div class="vrc-calendar-cfields-bottom"><button type="button" class="btn" onclick="hideCustomFields();">'.JText::_('VRANNULLA').'</button> <button type="button" class="btn btn-success" onclick="applyCustomFieldsContent();"><i class="icon-edit"></i> '.JText::_('VRAPPLY').'</button></div></div></div>';
//
$fquick .= '
<script type="text/javascript">
var vrc_glob_sel_nights = 0;
var cfields_overlay = false;
var customers_search_vals = "";
function vrcCloseRoom() {
	if (document.getElementById("setclosed").checked == true) {
		if (document.getElementById("vrspannumcars")) {
			document.getElementById("vrspannumcars").style.display = "none";
		}
		document.getElementById("vrspanbstat").style.display = "none";
		document.getElementById("vrspcustcost").style.display = "none";
		document.getElementById("vrspancmail").style.display = "none";
		document.getElementById("vrfillcustfields").style.display = "none";
		document.getElementById("vrspanbpay").style.display = "none";
		document.getElementById("vrcustdatatxtarea").value = "'.addslashes(JText::_('VRDBTEXTROOMCLOSED')).'";
		jQuery("#quickbsubmit").removeClass("btn-success").addClass("btn-danger").find("span").text("'.addslashes(JText::_('VRSUBMCLOSEROOM')).'");
	} else {
		if (document.getElementById("vrspannumcars")) {
			document.getElementById("vrspannumcars").style.display = "inline-block";
		}
		document.getElementById("vrspanbstat").style.display = "block";
		document.getElementById("vrspcustcost").style.display = "block";
		document.getElementById("vrspancmail").style.display = "block";
		document.getElementById("vrfillcustfields").style.display = "inline-block";
		document.getElementById("vrspanbpay").style.display = "block";
		document.getElementById("vrcustdatatxtarea").value = "";
		jQuery("#quickbsubmit").removeClass("btn-danger").addClass("btn-success").find("span").text("'.addslashes(JText::_('VRMAKERESERV')).'");
	}
}
function showCustomFields() {
	cfields_overlay = true;
	jQuery(".vrc-calendar-cfields-filler-overlay, .vrc-calendar-cfields-filler").fadeIn();
}
function hideCustomFields() {
	cfields_overlay = false;
	jQuery(".vrc-calendar-cfields-filler-overlay").fadeOut();
}
function applyCustomFieldsContent() {
	var cfields_cont = "";
	var cfields_labels = new Array;
	var nominatives = new Array;
	var tot_rows = 1;
	jQuery(".vrc-calendar-cfields-inner .vrc-calendar-cfield-entry").each(function(){
		var cfield_name = jQuery(this).find("label").text();
		var cfield_input = jQuery(this).find("span").find("input");
		var cfield_textarea = jQuery(this).find("span").find("textarea");
		var cfield_select = jQuery(this).find("span").find("select");
		var cfield_cont = "";
		if (cfield_input.length) {
			cfield_cont = cfield_input.val();
			if (cfield_input.attr("data-isemail") == "1" && cfield_cont.length) {
				jQuery("#custmailfield").val(cfield_cont);
			}
			if (cfield_input.attr("data-isphone") == "1") {
				jQuery("#phonefield").val(cfield_cont);
			}
			if (cfield_input.attr("data-isnominative") == "1") {
				nominatives.push(cfield_cont);
			}
		} else if (cfield_textarea.length) {
			cfield_cont = cfield_textarea.val();
		} else if (cfield_select.length) {
			cfield_cont = cfield_select.val();
			if (cfield_cont.length) {
				var country_code = jQuery("option:selected", cfield_select).attr("data-ccode");
				if (country_code.length) {
					jQuery("#ccode_inpfield").val(country_code);
				}
			}
		}
		if (cfield_cont.length) {
			cfields_cont += cfield_name+": "+cfield_cont+"\r\n";
			tot_rows++;
			cfields_labels.push(cfield_name+":");
		}
	});
	if (cfields_cont.length) {
		cfields_cont = cfields_cont.replace(/\r\n+$/, "");
	}
	if (nominatives.length > 1) {
		jQuery("#t_first_name_inpfield").val(nominatives[0]);
		jQuery("#t_last_name_inpfield").val(nominatives[1]);
	}
	jQuery("#vrcustdatatxtarea").val(cfields_cont);
	jQuery("#vrcustdatatxtarea").attr("rows", tot_rows);
	hideCustomFields();
}
function vrCalcNights() {
	vrc_glob_sel_nights = 0;
	var vrritiro = document.getElementById("pickupdate").value;
	var vrconsegna = document.getElementById("releasedate").value;
	if (vrritiro.length > 0 && vrconsegna.length > 0) {
		var vrritirop = vrritiro.split("/");
		var vrconsegnap = vrconsegna.split("/");
		var vrc_df = "'.$vrc_df.'";
		if (vrc_df == "%d/%m/%Y") {
			var vrinmonth = parseInt(vrritirop[1]);
			vrinmonth = vrinmonth - 1;
			var vrinday = parseInt(vrritirop[0], 10);
			var vrritirod = new Date(vrritirop[2], vrinmonth, vrinday);
			var vrcutmonth = parseInt(vrconsegnap[1]);
			vrcutmonth = vrcutmonth - 1;
			var vrcutday = parseInt(vrconsegnap[0], 10);
			var vrconsegnad = new Date(vrconsegnap[2], vrcutmonth, vrcutday);
		} else if (vrc_df == "%m/%d/%Y") {
			var vrinmonth = parseInt(vrritirop[0]);
			vrinmonth = vrinmonth - 1;
			var vrinday = parseInt(vrritirop[1], 10);
			var vrritirod = new Date(vrritirop[2], vrinmonth, vrinday);
			var vrcutmonth = parseInt(vrconsegnap[0]);
			vrcutmonth = vrcutmonth - 1;
			var vrcutday = parseInt(vrconsegnap[1], 10);
			var vrconsegnad = new Date(vrconsegnap[2], vrcutmonth, vrcutday);
		} else {
			var vrinmonth = parseInt(vrritirop[1]);
			vrinmonth = vrinmonth - 1;
			var vrinday = parseInt(vrritirop[2], 10);
			var vrritirod = new Date(vrritirop[0], vrinmonth, vrinday);
			var vrcutmonth = parseInt(vrconsegnap[1]);
			vrcutmonth = vrcutmonth - 1;
			var vrcutday = parseInt(vrconsegnap[2], 10);
			var vrconsegnad = new Date(vrconsegnap[0], vrcutmonth, vrcutday);
		}
		var vrdivider = 1000 * 60 * 60 * 24;
		var vrints = vrritirod.getTime();
		var vrcutts = vrconsegnad.getTime();
		if (vrcutts > vrints) {
			//var vrnights = Math.ceil((vrcutts - vrints) / (vrdivider));
			var utc1 = Date.UTC(vrritirod.getFullYear(), vrritirod.getMonth(), vrritirod.getDate());
			var utc2 = Date.UTC(vrconsegnad.getFullYear(), vrconsegnad.getMonth(), vrconsegnad.getDate());
			var vrnights = Math.ceil((utc2 - utc1) / vrdivider);
			if (vrnights > 0) {
				vrc_glob_sel_nights = vrnights;
				document.getElementById("vrjstotnights").innerHTML = "'.addslashes(JText::_('VRDAYS')).': "+vrnights;
			} else {
				document.getElementById("vrjstotnights").innerHTML = "";
			}
		} else {
			document.getElementById("vrjstotnights").innerHTML = "";
		}
	} else {
		document.getElementById("vrjstotnights").innerHTML = "";
	}
}
function vrCalcDailyCost(cur_val) {
	var avg_cost_str = "";
	if (cur_val.length && !isNaN(cur_val) && vrc_glob_sel_nights > 0) {
		var avg_cost = (parseFloat(cur_val) / vrc_glob_sel_nights).toFixed('.(int)$formatparts[0].');
		avg_cost_str = "'.$currencysymb.' "+avg_cost+"/'.addslashes(JText::_('VRDAY')).'";
	}
	document.getElementById("avg-daycost").innerHTML = avg_cost_str;
}
jQuery(document).ready(function(){
	jQuery("#vrfillcustfields").click(function(){
		showCustomFields();
	});
	jQuery(document).mouseup(function(e) {
		if (!cfields_overlay) {
			return false;
		}
		var vrdialogcf_cont = jQuery(".vrc-calendar-cfields-filler");
		if (!vrdialogcf_cont.is(e.target) && vrdialogcf_cont.has(e.target).length === 0) {
			hideCustomFields();
		}
	});
	//Search customer - Start
	var vrccustsdelay = (function(){
		var timer = 0;
		return function(callback, ms){
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();
	function vrcCustomerSearch(words) {
		jQuery("#vrc-searchcust-res").hide().html("");
		jQuery("#vrc-searchcust-loading").show();
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikrentcar", task: "searchcustomer", kw: words, tmpl: "component" }
		}).done(function(cont) {
			if (cont.length) {
				var obj_res = JSON.parse(cont);
				customers_search_vals = obj_res[0];
				jQuery("#vrc-searchcust-res").html(obj_res[1]);
			} else {
				customers_search_vals = "";
				jQuery("#vrc-searchcust-res").html("----");
			}
			jQuery("#vrc-searchcust-res").show();
			jQuery("#vrc-searchcust-loading").hide();
		}).fail(function() {
			jQuery("#vrc-searchcust-loading").hide();
			alert("Error Searching.");
		});
	}
	jQuery("#vrc-searchcust").keyup(function(event) {
		vrccustsdelay(function() {
			var keywords = jQuery("#vrc-searchcust").val();
			var chars = keywords.length;
			if (chars > 1) {
				if ((event.which > 96 && event.which < 123) || (event.which > 64 && event.which < 91) || event.which == 13) {
					vrcCustomerSearch(keywords);
				}
			} else {
				if (jQuery("#vrc-searchcust-res").is(":visible")) {
					jQuery("#vrc-searchcust-res").hide();
				}
			}
		}, 600);
	});
	//Search customer - End
	//Datepickers - Start
	jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "" ] );
	jQuery("#pickupdate").datepicker({
		showOn: "focus",
		dateFormat: "'.$juidf.'",
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			var nowritiro = jQuery("#pickupdate").datepicker("getDate");
			var nowpickupdate = new Date(nowritiro.getTime());
			nowpickupdate.setDate(nowpickupdate.getDate() + 1);
			jQuery("#releasedate").datepicker( "option", "minDate", nowpickupdate );
			vrCalcNights();
		}
	});
	jQuery("#releasedate").datepicker({
		showOn: "focus",
		dateFormat: "'.$juidf.'",
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			vrCalcNights();
		}
	});
	jQuery(".vrcdatepicker-trig-icon").click(function(){
		var jdp = jQuery(this).prev("input.hasDatepicker");
		if (jdp.length) {
			jdp.focus();
		}
	});
	//Datepickers - End
	'.(!empty($pritiro) ? 'jQuery("#pickupdate").datepicker("setDate", "'.$pritiro.'");'."\n" : '').'
	'.(!empty($pconsegna) ? 'jQuery("#releasedate").datepicker("setDate", "'.$pconsegna.'");'."\n" : '').'
	'.(!empty($pritiro) || !empty($pconsegna) ? 'jQuery(".ui-datepicker-current-day").click();'."\n" : '').'
});
jQuery("body").on("click", ".vrc-custsearchres-entry", function() {
	var custid = jQuery(this).attr("data-custid");
	var custemail = jQuery(this).attr("data-email");
	var custphone = jQuery(this).attr("data-phone");
	var custcountry = jQuery(this).attr("data-country");
	var custfirstname = jQuery(this).attr("data-firstname");
	var custlastname = jQuery(this).attr("data-lastname");
	jQuery("#customer_id_inpfield").val(custid);
	if (customers_search_vals.hasOwnProperty(custid)) {
		jQuery.each(customers_search_vals[custid], function(cfid, cfval) {
			var fill_field = jQuery("#cfield"+cfid);
			if (fill_field.length) {
				fill_field.val(cfval);
			}
		});
	} else {
		jQuery("input[data-isnominative=\"1\"]").each(function(k, v) {
			if (k == 0) {
				jQuery(this).val(custfirstname);
				return true;
			}
			if (k == 1) {
				jQuery(this).val(custlastname);
				return true;
			}
			return false;
		});
		jQuery("input[data-isemail=\"1\"]").val(custemail);
		jQuery("input[data-isphone=\"1\"]").val(custphone);
		//Populate main calendar form
		jQuery("#custmailfield").val(custemail);
		jQuery("#t_first_name_inpfield").val(custfirstname);
		jQuery("#t_last_name_inpfield").val(custlastname);
		//
	}
	applyCustomFieldsContent();
	if (custcountry.length) {
		jQuery("#ccode_inpfield").val(custcountry);
	}
	if (custphone.length) {
		jQuery("#phonefield").val(custphone);
	}
});
</script>';
//vikrentcar 1.1
$chcarsel = "<select id=\"vrc-calendar-changecar\" name=\"cid[]\" onchange=\"javascript: document.vrchcar.submit();\">\n";
foreach ($allc as $cc) {
	$chcarsel .= "<option value=\"".$cc['id']."\"".($cc['id'] == $car['id'] ? " selected=\"selected\"" : "").">".$cc['name']."</option>\n";
}
$chcarsel .= "</select>\n";
if ($ptmpl == 'component') {
	$chcarsel .= "<input type=\"hidden\" name=\"tmpl\" value=\"component\" />\n";
}
$chcarf = "<form name=\"vrchcar\" method=\"post\" action=\"index.php?option=com_vikrentcar\"><input type=\"hidden\" name=\"task\" value=\"calendar\"/>".$chcarsel."</form>";
//
echo "<div class=\"vrc-quickres-wrapper\"><table style=\"width: 95%;\"><tr><td valign=\"top\" align=\"left\"><div class=\"vrc-quickres-head\"><h4>".$car['name'].", ".JText::_('VRQUICKBOOK')."</h4> <div class=\"vrc-quickres-head-right\">".$chcarf."</div></div></td></tr><tr><td valign=\"top\" align=\"left\">".$fquick."</td></tr></table></div>\n";
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("#vrc-calendar-changecar").select2();
	jQuery("#pickuploc").select2({placeholder: '<?php echo addslashes(JText::_('VRRITIROCAR')); ?>'});
	jQuery("#dropoffloc").select2({placeholder: '<?php echo addslashes(JText::_('VRRETURNCARORD')); ?>'});
});
</script>

<?php
//calendar content

if ($vrc_df == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($vrc_df == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
?>
<div class="vrc-avcalendars-wrapper">
	<div class="vrc-avcalendars-carphoto">
	<?php
	if (file_exists(VRC_SITE_PATH.DS.'resources'.DS.$car['img'])) {
		$img = '<img alt="Car Image" src="' . VRC_SITE_URI . 'resources/'.$car['img'].'" />';
	} else {
		$img = '<img alt="Vik Rent Car Logo" src="' . VRC_ADMIN_URI . 'vikrentcar.png' . '" />';
	}
	echo $img;
	?>
	</div>
<?php
$check = false;
$nowtf = VikRentCar::getTimeFormat(true);
if (empty($busy)) {
	echo "<p class=\"warn\">".JText::_('VRNOFUTURERES')."</p>";
} else {
	$check = true;
	$icalurl = JURI::root().'index.php?option=com_vikrentcar&task=ical&car='.$car['id'].'&key='.VikRentCar::getIcalSecretKey();
	?>
	<p>
		<a class="vrmodelink<?php echo $vmode == 3 ? ' vrmodelink-active' : ''; ?>" href="index.php?option=com_vikrentcar&amp;task=calendar&amp;cid[]=<?php echo $car['id'].($ptmpl == 'component' ? '&tmpl=component' : ''); ?>&amp;vmode=3"><i class="fa fa-calendar"></i> <span><?php echo JText::_('VRTHREEMONTHS'); ?></span></a>
		<a class="vrmodelink<?php echo $vmode == 6 ? ' vrmodelink-active' : ''; ?>" href="index.php?option=com_vikrentcar&amp;task=calendar&amp;cid[]=<?php echo $car['id'].($ptmpl == 'component' ? '&tmpl=component' : ''); ?>&amp;vmode=6"><i class="fa fa-calendar"></i> <span><?php echo JText::_('VRSIXMONTHS'); ?></span></a>
		<a class="vrmodelink<?php echo $vmode == 12 ? ' vrmodelink-active' : ''; ?>" href="index.php?option=com_vikrentcar&amp;task=calendar&amp;cid[]=<?php echo $car['id'].($ptmpl == 'component' ? '&tmpl=component' : ''); ?>&amp;vmode=12"><i class="fa fa-calendar"></i> <span><?php echo JText::_('VRTWELVEMONTHS'); ?></span></a>
		<a class="vrmodelink" href="javascript: void(0);" onclick="jQuery('#icalsynclinkinp').attr('size', (jQuery('#icalsynclinkinp').val().length + 5)).fadeToggle().focus();"><i class="icon-link"></i> <span><?php echo JText::_('VRCICALLINK'); ?></span></a>
		<input id="icalsynclinkinp" style="display: none;" type="text" value="<?php echo $icalurl; ?>" readonly="readonly" size="40" onfocus="jQuery('#icalsynclinkinp').select();"/>
	</p>
	<?php
}
?>
	<div class="table-responsive">
	<table class="table" align="center"><tr>
<?php
$arr = getdate();
$mon = $arr['mon'];
$realmon = ($mon < 10 ? "0".$mon : $mon);
$year = $arr['year'];
$day = $realmon."/01/".$year;
$dayts = strtotime($day);
$newarr = getdate($dayts);

$firstwday = (int)VikRentCar::getFirstWeekDay(true);
$days_labels = array(
		JText::_('VRSUN'),
		JText::_('VRMON'),
		JText::_('VRTUE'),
		JText::_('VRWED'),
		JText::_('VRTHU'),
		JText::_('VRFRI'),
		JText::_('VRSAT')
);
$days_indexes = array();
for ($i = 0; $i < 7; $i++) {
	$days_indexes[$i] = (6-($firstwday-$i)+1)%7;
}

for ($jj = 1; $jj <= $vmode; $jj++) {
	$d_count = 0;
	echo "<td valign=\"top\">";
	$cal="";
	?>
	<table class="vrcadmincaltable">
	<tr class="vrcadmincaltrmon"><td colspan="7" align="center"><?php echo VikRentCar::sayMonth($newarr['mon'])." ".$newarr['year']; ?></td></tr>
	<tr class="vrcadmincaltrmdays">
	<?php
	for ($i = 0; $i < 7; $i++) {
		$d_ind = ($i + $firstwday) < 7 ? ($i + $firstwday) : ($i + $firstwday - 7);
		echo '<td>'.$days_labels[$d_ind].'</td>';
	}
	?>
	</tr>
	<tr>
	<?php
	for ($i=0, $n = $days_indexes[$newarr['wday']]; $i < $n; $i++, $d_count++) {
		$cal .= "<td align=\"center\">&nbsp;</td>";
	}
	while ($newarr['mon'] == $mon) {
		if ($d_count > 6) {
			$d_count = 0;
			$cal .= "</tr>\n<tr>";
		}
		$dclass = "free";
		$dalt = "";
		$bid = "";
		$totfound = 0;
		if ($check) {
			foreach ($busy as $b) {
				$tmpone = getdate($b['ritiro']);
				$rit = ($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
				$ritts = strtotime($rit);
				$tmptwo = getdate($b['consegna']);
				$con = ($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
				$conts = strtotime($con);
				if ($newarr[0] >= $ritts && $newarr[0] <= $conts) {
					$dclass = "busy";
					$bid = $b['idorder'];
					if ($newarr[0] == $ritts) {
						$dalt = JText::_('VRPICKUPAT')." ".date($nowtf, $b['ritiro']);
					} elseif ($newarr[0] == $conts) {
						$dalt = JText::_('VRRELEASEAT')." ".date($nowtf, $b['consegna']);
					}
					$totfound++;
				}
			}
		}
		$useday = ($newarr['mday'] < 10 ? "0".$newarr['mday'] : $newarr['mday']);
		if ($totfound > 0 && $totfound < $car['units']) {
			$dclass .= " vrc-partially";
		}
		if ($totfound == 1) {
			$dlnk = "<a href=\"index.php?option=com_vikrentcar&task=editbusy&cid[]=".$bid."\"".($ptmpl == 'component' ? ' target="_blank"' : '').">".$useday."</a>";
			$cal .= "<td align=\"center\" data-daydate=\"".date($df, $newarr[0])."\" class=\"".$dclass."\"".(!empty($dalt) ? " title=\"".$dalt."\"" : "").">".$dlnk."</td>\n";
		} elseif ($totfound > 1) {
			$dlnk = "<a href=\"index.php?option=com_vikrentcar&task=choosebusy&idcar=".$car['id']."&ts=".$newarr[0]."\"".($ptmpl == 'component' ? ' target="_blank"' : '').">".$useday."</a>";
			$cal .= "<td align=\"center\" data-daydate=\"".date($df, $newarr[0])."\" class=\"".$dclass."\">".$dlnk."</td>\n";
		} else {
			$dlnk = $useday;
			$cal .= "<td align=\"center\" data-daydate=\"".date($df, $newarr[0])."\" class=\"".$dclass."\">".$dlnk."</td>\n";
		}
		$next = $newarr['mday'] + 1;
		$dayts = mktime(0, 0, 0, ($newarr['mon'] < 10 ? "0".$newarr['mon'] : $newarr['mon']), ($next < 10 ? "0".$next : $next), $newarr['year']);
		$newarr = getdate($dayts);
		$d_count++;
	}
	
	for ($i = $d_count; $i <= 6; $i++) {
		$cal.="<td align=\"center\">&nbsp;</td>";
	}
	
	echo $cal;
	?>
	</tr>
	</table>
	<?php
	echo "</td>";
	if ($mon == 12) {
		$mon = 1;
		$year += 1;
		$dayts = mktime(0, 0, 0, ($mon < 10 ? "0".$mon : $mon), 01, $year);
	} else {
		$mon += 1;
		$dayts = mktime(0, 0, 0, ($mon < 10 ? "0".$mon : $mon), 01, $year);
	}
	$newarr = getdate($dayts);
	
	if (($jj % 4) == 0 && $vmode > 4) {
		echo "</tr>\n<tr>";
	}
}

?>
	</tr>
	</table>
	</div>
</div>
<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrentcar" />
</form>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('td.free').click(function() {
		var indate = jQuery('#pickupdate').val();
		var outdate = jQuery('#releasedate').val();
		var clickdate = jQuery(this).attr('data-daydate');
		if (!(indate.length > 0)) {
			jQuery('#pickupdate').val(clickdate);
		} else if (!(outdate.length > 0) && clickdate != indate) {
			jQuery('#releasedate').val(clickdate);
		} else {
			jQuery('#pickupdate').val(clickdate);
			jQuery('#releasedate').val('');
		}
	});
});
</script>
<br clear="all" />
