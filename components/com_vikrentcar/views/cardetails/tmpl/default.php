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

$car = $this->car;
$car_params = $this->car_params;
$busy = $this->busy;
$vrc_tn = $this->vrc_tn;

$vrc_app = VikRentCar::getVrcApplication();

//load jQuery lib and navigation
$document = JFactory::getDocument();
if (VikRentCar::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
	JHtml::_('script', VRC_SITE_URI.'resources/jquery-1.12.4.min.js', false, true, false, false);
}
$document->addStyleSheet(VRC_SITE_URI.'resources/jquery.fancybox.css');
JHtml::_('script', VRC_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);
$navdecl = '
jQuery(document).ready(function() {
	jQuery(".vrcmodal").fancybox({
		"helpers": {
			"overlay": {
				"locked": false
			}
		},"padding": 0
	});
	jQuery(".vrcmodalframe").fancybox({
		"helpers": {
			"overlay": {
				"locked": false
			}
		},
		"width": "75%",
		"height": "75%",
	    "autoScale": false,
	    "transitionIn": "none",
		"transitionOut": "none",
		"padding": 0,
		"type": "iframe"
	});
});';
$document->addScriptDeclaration($navdecl);
//

$currencysymb = VikRentCar::getCurrencySymb();
$showpartlyres = VikRentCar::showPartlyReserved();
$numcalendars = VikRentCar::numCalendars();

$carats = VikRentCar::getCarCaratOriz($car['idcarat'], array(), $vrc_tn);

$pitemid = VikRequest::getInt('Itemid', '', 'request');
$vrcdateformat = VikRentCar::getDateFormat();
$nowtf = VikRentCar::getTimeFormat();
if ($vrcdateformat == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($vrcdateformat == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$nowts = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

?>
<div class="vrc-cdetails-cinfo">
	<div class="vrc-cdetails-img">
	<?php
	if (!empty ($car['img'])) {
		$imgpath = file_exists(VRC_ADMIN_PATH.DS.'resources'.DS.$car['img']) ? VRC_ADMIN_URI.'resources/'.$car['img'] : VRC_ADMIN_URI.'resources/'.$car['img'];
		?>
		<div class="vrc-cdetails-cmainimg">
			<img src="<?php echo $imgpath; ?>"/>
		</div>
		<?php
	}
	?>
	<?php
	if (strlen($car['moreimgs']) > 0) {
		$moreimages = explode(';;', $car['moreimgs']);
		?>
		<div class="cardetails_moreimages">
		<?php
		foreach ($moreimages as $mimg) {
			if (!empty($mimg)) {
			?>
			<a href="<?php echo VRC_ADMIN_URI; ?>resources/big_<?php echo $mimg; ?>" rel="vrcgroup<?php echo $car['id']; ?>" target="_blank" class="vrcmodal"><img src="<?php echo VRC_ADMIN_URI; ?>resources/thumb_<?php echo $mimg; ?>"/></a>
			<?php
			}
		}
		?>
		</div>
	<?php
	}
	?>
	</div>
	<div class="vrc-cdetails-infocar">
		<div class="vrc-cdetails-cgroup">
			<span class="vrclistcarname"><?php echo $car['name']; ?></span>
			<span class="vrclistcarcat"><?php echo VikRentCar::sayCategory($car['idcat'], $vrc_tn); ?></span>
		</div>
		<div class="vrc-cdetails-cardesc">
		
		<div class="vrc-car-carats">
	<?php echo $carats; ?>
</div>
		
		<?php
		//BEGIN: Joomla Content Plugins Rendering
		JPluginHelper::importPlugin('content');
		$myItem = &JTable::getInstance('content');
		$dispatcher = &JDispatcher::getInstance();
		$myItem->text = $car['info'];
		$dispatcher->trigger('onContentPrepare', array('com_vikrentcar.cardetails', &$myItem, &$params, 0));
		$car['info'] = $myItem->text;
		//END: Joomla Content Plugins Rendering
		echo $car['info'];
		?>
		</div>
		<?php

		// VRC 1.12 - Car Params Contact Request
		if (isset($car_params['reqinfo']) && (bool)$car_params['reqinfo']) {
			$cur_user = JFactory::getUser();
			$cur_email = '';
			if (property_exists($cur_user, 'email') && !empty($cur_user->email)) {
				$cur_email = $cur_user->email;
			}
			?>
		<div class="vrc-reqinfo-cont">
			<span><a href="Javascript: void(0);" onclick="vrcShowRequestInfo();" class="vrc-reqinfo-opener"><?php echo JText::_('VRCCARREQINFOBTN'); ?></a></span>
		</div>
		<div id="vrcdialog-overlay" style="display: none;">
			<a class="vrcdialog-overlay-close" href="javascript: void(0);"></a>
			<div class="vrcdialog-inner vrcdialog-reqinfo">
				<h3><?php echo JText::sprintf('VRCCARREQINFOTITLE', $car['name']); ?></h3>
				<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar&task=reqinfo'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" method="post" onsubmit="return vrcValidateReqInfo();">
					<input type="hidden" name="carid" value="<?php echo $car['id']; ?>" />
					<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>" />
					<div class="vrcdialog-reqinfo-formcont">
						<div class="vrcdialog-reqinfo-formentry">
							<label for="reqname"><?php echo JText::_('VRCCARREQINFONAME'); ?></label>
							<input type="text" name="reqname" id="reqname" value="" placeholder="<?php echo JText::_('VRCCARREQINFONAME'); ?>" />
						</div>
						<div class="vrcdialog-reqinfo-formentry">
							<label for="reqemail"><?php echo JText::_('VRCCARREQINFOEMAIL'); ?></label>
							<input type="text" name="reqemail" id="reqemail" value="<?php echo $cur_email; ?>" placeholder="<?php echo JText::_('VRCCARREQINFOEMAIL'); ?>" />
						</div>
						<div class="vrcdialog-reqinfo-formentry">
							<label for="reqmess"><?php echo JText::_('VRCCARREQINFOMESS'); ?></label>
							<textarea name="reqmess" id="reqmess" placeholder="<?php echo JText::_('VRCCARREQINFOMESS'); ?>"></textarea>
						</div>
					<?php
					if (count($this->terms_fields)) {
						if (!empty($this->terms_fields['poplink'])) {
							$fname = "<a href=\"" . $this->terms_fields['poplink'] . "\" id=\"vrcf\" rel=\"{handler: 'iframe', size: {x: 750, y: 600}}\" target=\"_blank\" class=\"vrcmodalframe\">" . JText::_($this->terms_fields['name']) . "</a>";
						} else {
							$fname = "<label id=\"vrcf\" for=\"vrcf-inp\" style=\"display: inline-block;\">" . JText::_($this->terms_fields['name']) . "</label>";
						}
						?>
						<div class="vrcdialog-reqinfo-formentry vrcdialog-reqinfo-formentry-ckbox">
							<?php echo $fname; ?>
							<input type="checkbox" name="vrcf" id="vrcf-inp" value="<?php echo JText::_('VRYES'); ?>"/>
						</div>
						<?php
					} else {
						?>
						<div class="vrcdialog-reqinfo-formentry vrcdialog-reqinfo-formentry-ckbox">
							<label id="vrcf" for="vrcf-inp" style="display: inline-block;"><?php echo JText::_('ORDER_TERMSCONDITIONS'); ?></label>
							<input type="checkbox" name="vrcf" id="vrcf-inp" value="<?php echo JText::_('VRYES'); ?>"/>
						</div>
						<?php
					}
					if ($vrc_app->isCaptcha()) {
						?>
						<div class="vrcdialog-reqinfo-formentry vrcdialog-reqinfo-formentry-captcha">
							<div><?php echo $vrc_app->reCaptcha(); ?></div>
						</div>
						<?php
					}
					?>
						<div class="vrcdialog-reqinfo-formentry vrcdialog-reqinfo-formsubmit">
							<button type="submit" class="btn"><?php echo JText::_('VRCCARREQINFOSEND'); ?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<script type="text/javascript">
		var vrcdialog_on = false;
		function vrcShowRequestInfo() {
			jQuery("#vrcdialog-overlay").fadeIn();
			vrcdialog_on = true;
		}
		function vrcHideRequestInfo() {
			jQuery("#vrcdialog-overlay").fadeOut();
			vrcdialog_on = false;
		}
		jQuery(function() {
			jQuery(document).mouseup(function(e) {
				if (!vrcdialog_on) {
					return false;
				}
				var vrcdialog_cont = jQuery(".vrcdialog-inner");
				if (!vrcdialog_cont.is(e.target) && vrcdialog_cont.has(e.target).length === 0) {
					vrcHideRequestInfo();
				}
			});
			jQuery(document).keyup(function(e) {
				if (e.keyCode == 27 && vrcdialog_on) {
					vrcHideRequestInfo();
				}
			});
		});
		function vrcValidateReqInfo() {
			if (document.getElementById('vrcf-inp').checked) {
				return true;
			}
			alert('<?php echo addslashes(JText::_('VRFILLALL')); ?>');
			return false;
		}
		</script>
			<?php
		}
		//


		if ($car['cost'] > 0) {
		?>
		<div class="vrc-cdetails-cost">
			<span class="vrcliststartfrom"><?php echo JText::_('VRCLISTSFROM'); ?></span>
			<span class="car_cost"> <span class="vrc_price"><?php echo strlen($car['startfrom']) > 0 ? VikRentCar::numberFormat($car['startfrom']) : VikRentCar::numberFormat($car['cost']); ?></span> <span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
			<span class="vrcliststartfrom"><?php echo JText::_('VRCLISTSFROMS'); ?></span>
		</div>
		<?php
		}

		?>
		
		<a href="#0" class="cd-popup-trigger">Оформить аренду</a>
		
	</div>
</div>
<?php
if (!empty($carats)) {
?>




<?php
}

$pmonth = VikRequest::getInt('month', '', 'request');
$pday = VikRequest::getInt('day', '', 'request');

$viewingdayts = !empty($pday) && $pday >= $nowts ? $pday : $nowts;
$show_hourly_cal = (array_key_exists('shourlycal', $car_params) && intval($car_params['shourlycal']) > 0);

$arr = getdate();
$mon = $arr['mon'];
$realmon = ($mon < 10 ? "0".$mon : $mon);
$year = $arr['year'];
$day = $realmon."/01/".$year;
$dayts = strtotime($day);
$validmonth = false;
if ($pmonth > 0 && $pmonth >= $dayts) {
	$validmonth=true;
}
$moptions="";
for ($i=0; $i < 12; $i++) {
	$moptions .= "<option value=\"".$dayts."\"".($validmonth && $pmonth == $dayts ? " selected=\"selected\"" : "").">".VikRentCar::sayMonth($arr['mon'])." ".$arr['year']."</option>\n";
	$next = $arr['mon'] + 1;
	$dayts = mktime(0, 0, 0, $next, 1, $arr['year']);
	$arr = getdate($dayts);
}

if ($numcalendars > 0) {
?>
<div class="vrcdetsep"></div>


<?php
}
?>

<div class="vrcdetsep"></div>

<?php
$check = (is_array($busy));
if ($validmonth) {
	$arr=getdate($pmonth);
	$mon=$arr['mon'];
	$realmon=($mon < 10 ? "0".$mon : $mon);
	$year=$arr['year'];
	$day=$realmon."/01/".$year;
	$dayts=strtotime($day);
	$newarr=getdate($dayts);
} else {
	$arr=getdate();
	$mon=$arr['mon'];
	$realmon=($mon < 10 ? "0".$mon : $mon);
	$year=$arr['year'];
	$day=$realmon."/01/".$year;
	$dayts=strtotime($day);
	$newarr=getdate($dayts);
}

$firstwday = (int)VikRentCar::getFirstWeekDay();
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
for ( $i = 0; $i < 7; $i++ ) {
	$days_indexes[$i] = (6-($firstwday-$i)+1)%7;
}

$push_disabled_in = array();
$push_disabled_out = array();
$previousdayclass = "";

?>


<?php
if ($show_hourly_cal) {
	//VRC 1.11 Hourly Availability Calendar
?>
	<div class="vrc-hourlycal-container">
		<h4 class="vrc-medium-header"><?php echo JText::sprintf('VRCAVAILSINGLEDAY', date($df, $viewingdayts)); ?></h4>
		<div class="table-responsive">
			<table class="vrccal table">
				<tr>
					<td style="text-align: center;"><?php echo JText::_('VRCLEGH'); ?></td>
<?php
for ($h = 0; $h <= 23; $h++) {
	if ($nowtf == 'H:i') {
		$sayh = $h < 10 ? "0".$h : $h;
	} else {
		$ampm = $h < 12 ? ' am' : ' pm';
		$ampmh = $h > 12 ? ($h - 12) : $h;
		$sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
	}
	?>
					<td style="text-align: center;"><?php echo $sayh; ?></td>
	<?php
}
?>
				</tr>
				<tr class="vrc-hourlycal-rowavail">
					<td style="text-align: center;"> </td>
<?php
for ($h = 0; $h <= 23; $h++) {
	$checkhourts = ($viewingdayts + ($h * 3600));
	$dclass = "vrctdfree";
	$dalt = "";
	$bid = "";
	if ($check) {
		$totfound = 0;
		foreach ($busy as $b) {
			$tmpone = getdate($b['ritiro']);
			$rit = ($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
			$ritts = strtotime($rit);
			$tmptwo = getdate($b['consegna']);
			$con = ($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
			$conts = strtotime($con);
			if ($viewingdayts >= $ritts && $viewingdayts <= $conts) {
				if ($b['stop_sales'] == 1) {
					$totfound = $car['units'];
					break;
				}
				if ($checkhourts >= $b['ritiro'] && $checkhourts <= $b['consegna']) {
					$totfound++;
				}
			}
		}
		if ($totfound >= $car['units']) {
			$dclass="vrctdbusy";
		} elseif ($totfound > 0) {
			if ($showpartlyres) {
				$dclass="vrctdwarning";
			}
		}
		$hourlydisp = $car['units'] - $totfound;
		$hourlydisp = $hourlydisp < 0 ? 0 : $hourlydisp;
	} else {
		$hourlydisp = $car['units'];
	}
	//Do not print the actual number of units available but rather just the cell with the background color
	//comment the line below to print the number of units available
	$hourlydisp = ' ';
	//
	?>
					<td style="text-align: center;" class="<?php echo $dclass; ?>"><?php echo $hourlydisp; ?></td>
	<?php
}
?>
				</tr>
			</table>
		</div>
	</div>
<?php
}
?>

<div class="vrcdetsep"></div>



<?php

if (VikRentCar::allowRent()) {
	$dbo = JFactory::getDBO();
	//vikrentcar 1.5
	$calendartype = VikRentCar::calendarType();
	$indvrcplace = 0;
	$indvrcreturnplace = 0;
	$restrictions = VikRentCar::loadRestrictions(true, array($car['id']));
	$def_min_los = VikRentCar::setDropDatePlus();
	$document = JFactory::getDocument();
	//load jQuery UI
	if ($calendartype == "jqueryui") {
		$document->addStyleSheet(VRC_SITE_URI.'resources/jquery-ui.min.css');
		//load jQuery UI
		JHtml::_('script', VRC_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);
	}
	//
	$ptmpl = VikRequest::getString('tmpl', '', 'request');
	$ppickup = VikRequest::getInt('pickup', 0, 'request');
	$ppromo = VikRequest::getInt('promo', 0, 'request');
	$coordsplaces = array();
	$selform = "<div class=\"vrcdivsearch\"><form action=\"".JRoute::_('index.php?option=com_vikrentcar'.(!empty($pitemid) ? '&Itemid='.$pitemid : ''))."\" method=\"get\" onsubmit=\"return vrcValidateSearch();\">\n";
	$selform .= "<input type=\"hidden\" name=\"option\" value=\"com_vikrentcar\"/>\n";
	$selform .= "<input type=\"hidden\" name=\"task\" value=\"search\"/>\n";
	$selform .= "<input type=\"hidden\" name=\"cardetail\" value=\"".$car['id']."\"/>\n";
	if ($ptmpl == 'component') {
		$selform .= "<input type=\"hidden\" name=\"tmpl\" value=\"component\"/>\n";
	}
	// start the DIV container for the pick up part
	$selform .= "<div class=\"vrc-searchf-section-pickup\">\n";
	//

	$diffopentime = false;
	$closingdays = array();
	$declclosingdays = '';
	if (VikRentCar::showPlacesFront()) {
		$q = "SELECT * FROM `#__vikrentcar_places` ORDER BY `#__vikrentcar_places`.`ordering` ASC, `#__vikrentcar_places`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$places = $dbo->loadAssocList();
			$vrc_tn->translateContents($places, '#__vikrentcar_places');
			$plapick = explode(';', $car['idplace']);
			$pladrop = explode(';', $car['idretplace']);
			foreach ($places as $kpla=>$pla) {
				if (!in_array($pla['id'], $plapick) && !in_array($pla['id'], $pladrop)) {
					unset($places[$kpla]);
				}
			}
			if (count($places) == 0) {
				$places = '';
			}
		} else {
			$places = '';
		}
		if (is_array($places)) {
			//check if some place has a different opening time (1.6)
			foreach ($places as $kpla => $pla) {
				if (!empty($pla['opentime'])) {
					$diffopentime = true;
				}
				//check if some place has closing days
				if (!empty($pla['closingdays'])) {
					$closingdays[$pla['id']] = $pla['closingdays'];
				}
				//
				if (empty($indvrcplace)) {
					$indvrcplace = $kpla;
					$indvrcreturnplace = $kpla;
				}
			}
			// VRC 1.12 - location override opening time on some weekdays
			$wopening_pick = array();
			if (isset($places[$indvrcplace]) && !empty($places[$indvrcplace]['wopening'])) {
				$wopening_pick = json_decode($places[$indvrcplace]['wopening'], true);
				$wopening_pick = !is_array($wopening_pick) ? array() : $wopening_pick;
			}
			$wopening_drop = array();
			if (isset($places[$indvrcreturnplace]) && !empty($places[$indvrcreturnplace]['wopening'])) {
				$wopening_drop = json_decode($places[$indvrcreturnplace]['wopening'], true);
				$wopening_drop = !is_array($wopening_drop) ? array() : $wopening_drop;
			}
			//
			//locations closing days (1.11)
			if (count($closingdays) > 0) {
				foreach ($closingdays as $idpla => $clostr) {
					$jsclosingdstr = VikRentCar::formatLocationClosingDays($clostr);
					if (count($jsclosingdstr) > 0) {
						$declclosingdays .= 'var loc'.$idpla.'closingdays = ['.implode(", ", $jsclosingdstr).'];'."\n";
					}
				}
			}
			$onchangeplaces = $diffopentime == true ? " onchange=\"javascript: vrcSetLocOpenTime(this.value, 'pickup');\"" : "";
			$onchangeplacesdrop = $diffopentime == true ? " onchange=\"javascript: vrcSetLocOpenTime(this.value, 'dropoff');\"" : "";
			if ($diffopentime == true) {
				$onchangedecl = '
var vrc_location_change = false;
var vrc_wopening_pick = '.json_encode($wopening_pick).';
var vrc_wopening_drop = '.json_encode($wopening_drop).';
var vrc_hopening_pick = null;
var vrc_hopening_drop = null;
var vrc_mopening_pick = null;
var vrc_mopening_drop = null;
function vrcSetLocOpenTime(loc, where) {
	if (where == "dropoff") {
		vrc_location_change = true;
	}
	jQuery.ajax({
		type: "POST",
		url: "'.JRoute::_('index.php?option=com_vikrentcar&task=ajaxlocopentime&tmpl=component').'",
		data: { idloc: loc, pickdrop: where }
	}).done(function(res) {
		var vrcobj = jQuery.parseJSON(res);
		if (where == "pickup") {
			jQuery("#vrccomselph").html(vrcobj.hours);
			jQuery("#vrccomselpm").html(vrcobj.minutes);
			if (vrcobj.hasOwnProperty("wopening")) {
				vrc_wopening_pick = vrcobj.wopening;
				vrc_hopening_pick = vrcobj.hours;
			}
		} else {
			jQuery("#vrccomseldh").html(vrcobj.hours);
			jQuery("#vrccomseldm").html(vrcobj.minutes);
			if (vrcobj.hasOwnProperty("wopening")) {
				vrc_wopening_drop = vrcobj.wopening;
				vrc_hopening_drop = vrcobj.hours;
			}
		}
		if (where == "pickup" && vrc_location_change === false) {
			jQuery("#returnplace").val(loc).trigger("change");
			vrc_location_change = false;
		}
	});
}';
				$document->addScriptDeclaration($onchangedecl);
			}
			//end check if some place has a different opningtime (1.6)
			$selform .= "<div class=\"vrcsfentrycont\"><label for=\"place\">" . JText::_('VRPPLACE') . "</label><div class=\"vrcsfentryselect\"><select name=\"place\" id=\"place\"".$onchangeplaces.">";
			foreach ($places as $pla) {
				$selform .= "<option value=\"" . $pla['id'] . "\" id=\"place".$pla['id']."\">" . $pla['name'] . "</option>\n";
				if (!empty($pla['lat']) && !empty($pla['lng'])) {
					$coordsplaces[] = $pla;
				}
			}
			$selform .= "</select></div></div>\n";
		}
	}
	
	if ($diffopentime == true && is_array($places) && strlen($places[0]['opentime']) > 0) {
		$parts = explode("-", $places[0]['opentime']);
		if (is_array($parts) && $parts[0] != $parts[1]) {
			$opent = VikRentCar::getHoursMinutes($parts[0]);
			$closet = VikRentCar::getHoursMinutes($parts[1]);
			$i = $opent[0];
			$imin = $opent[1];
			$j = $closet[0];
		} else {
			$i = 0;
			$imin = 0;
			$j = 23;
		}
	} else {
		$timeopst = VikRentCar::getTimeOpenStore();
		if (is_array($timeopst) && $timeopst[0] != $timeopst[1]) {
			$opent = VikRentCar::getHoursMinutes($timeopst[0]);
			$closet = VikRentCar::getHoursMinutes($timeopst[1]);
			$i = $opent[0];
			$imin = $opent[1];
			$j = $closet[0];
		} else {
			$i = 0;
			$imin = 0;
			$j = 23;
		}
	}
	$hours = "";
	//VRC 1.9
	$pickhdeftime = !empty($places[0]['defaulttime']) ? ((int)$places[0]['defaulttime'] / 3600) : '';
	if (!($i < $j)) {
		while (intval($i) != (int)$j) {
			$sayi = $i < 10 ? "0".$i : $i;
			if ($nowtf != 'H:i') {
				$ampm = $i < 12 ? ' am' : ' pm';
				$ampmh = $i > 12 ? ($i - 12) : $i;
				$sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
			} else {
				$sayh = $sayi;
			}
			$hours .= "<option value=\"" . (int)$i . "\"".($pickhdeftime == (int)$i ? ' selected="selected"' : '').">" . $sayh . "</option>\n";
			$i++;
			$i = $i > 23 ? 0 : $i;
		}
		$sayi = $i < 10 ? "0".$i : $i;
		if ($nowtf != 'H:i') {
			$ampm = $i < 12 ? ' am' : ' pm';
			$ampmh = $i > 12 ? ($i - 12) : $i;
			$sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
		} else {
			$sayh = $sayi;
		}
		$hours .= "<option value=\"" . (int)$i . "\">" . $sayh . "</option>\n";
	} else {
		while ($i <= $j) {
			$sayi = $i < 10 ? "0".$i : $i;
			if ($nowtf != 'H:i') {
				$ampm = $i < 12 ? ' am' : ' pm';
				$ampmh = $i > 12 ? ($i - 12) : $i;
				$sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
			} else {
				$sayh = $sayi;
			}
			$hours .= "<option value=\"" . (int)$i . "\"".($pickhdeftime == (int)$i ? ' selected="selected"' : '').">" . $sayh . "</option>\n";
			$i++;
		}
	}
	//
	$minutes = "";
	for ($i = 0; $i < 60; $i += 15) {
		if ($i < 10) {
			$i = "0" . $i;
		} else {
			$i = $i;
		}
		$minutes .= "<option value=\"" . (int)$i . "\"".((int)$i == $imin ? " selected=\"selected\"" : "").">" . $i . "</option>\n";
	}
	
	//vikrentcar 1.5
	if ($calendartype == "jqueryui") {
		if ($vrcdateformat == "%d/%m/%Y") {
			$juidf = 'dd/mm/yy';
		} elseif ($vrcdateformat == "%m/%d/%Y") {
			$juidf = 'mm/dd/yy';
		} else {
			$juidf = 'yy/mm/dd';
		}
		//lang for jQuery UI Calendar
		$ldecl = '
jQuery(function($){'."\n".'
	$.datepicker.regional["vikrentcar"] = {'."\n".'
		closeText: "'.JText::_('VRCJQCALDONE').'",'."\n".'
		prevText: "'.JText::_('VRCJQCALPREV').'",'."\n".'
		nextText: "'.JText::_('VRCJQCALNEXT').'",'."\n".'
		currentText: "'.JText::_('VRCJQCALTODAY').'",'."\n".'
		monthNames: ["'.JText::_('VRMONTHONE').'","'.JText::_('VRMONTHTWO').'","'.JText::_('VRMONTHTHREE').'","'.JText::_('VRMONTHFOUR').'","'.JText::_('VRMONTHFIVE').'","'.JText::_('VRMONTHSIX').'","'.JText::_('VRMONTHSEVEN').'","'.JText::_('VRMONTHEIGHT').'","'.JText::_('VRMONTHNINE').'","'.JText::_('VRMONTHTEN').'","'.JText::_('VRMONTHELEVEN').'","'.JText::_('VRMONTHTWELVE').'"],'."\n".'
		monthNamesShort: ["'.mb_substr(JText::_('VRMONTHONE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTWO'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTHREE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHFOUR'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHFIVE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHSIX'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHSEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHEIGHT'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHNINE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHELEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTWELVE'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNames: ["'.JText::_('VRCJQCALSUN').'", "'.JText::_('VRCJQCALMON').'", "'.JText::_('VRCJQCALTUE').'", "'.JText::_('VRCJQCALWED').'", "'.JText::_('VRCJQCALTHU').'", "'.JText::_('VRCJQCALFRI').'", "'.JText::_('VRCJQCALSAT').'"],'."\n".'
		dayNamesShort: ["'.mb_substr(JText::_('VRCJQCALSUN'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCJQCALMON'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCJQCALTUE'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCJQCALWED'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCJQCALTHU'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCJQCALFRI'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCJQCALSAT'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNamesMin: ["'.mb_substr(JText::_('VRCJQCALSUN'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCJQCALMON'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCJQCALTUE'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCJQCALWED'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCJQCALTHU'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCJQCALFRI'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCJQCALSAT'), 0, 2, 'UTF-8').'"],'."\n".'
		weekHeader: "'.JText::_('VRCJQCALWKHEADER').'",'."\n".'
		dateFormat: "'.$juidf.'",'."\n".'
		firstDay: '.VikRentCar::getFirstWeekDay().','."\n".'
		isRTL: false,'."\n".'
		showMonthAfterYear: false,'."\n".'
		yearSuffix: ""'."\n".'
	};'."\n".'
	$.datepicker.setDefaults($.datepicker.regional["vikrentcar"]);'."\n".'
});
function vrcGetDateObject(dstring) {
	var dparts = dstring.split("-");
	return new Date(dparts[0], (parseInt(dparts[1]) - 1), parseInt(dparts[2]), 0, 0, 0, 0);
}
function vrcFullObject(obj) {
	var jk;
	for(jk in obj) {
		return obj.hasOwnProperty(jk);
	}
}';
		$document->addScriptDeclaration($ldecl);
		//
		// VRC 1.12 - Restrictions Start
		$totrestrictions = count($restrictions);
		if ($totrestrictions > 0) {
			$wdaysrestrictions = array();
			$wdaystworestrictions = array();
			$wdaysrestrictionsrange = array();
			$wdaysrestrictionsmonths = array();
			$monthscomborestr = array();
			$minlosrestrictions = array();
			$minlosrestrictionsrange = array();
			$maxlosrestrictions = array();
			$maxlosrestrictionsrange = array();
			$notmultiplyminlosrestrictions = array();
			foreach($restrictions as $rmonth => $restr) {
				if($rmonth != 'range') {
					if (strlen($restr['wday']) > 0) {
						$wdaysrestrictions[] = "'".($rmonth - 1)."': '".$restr['wday']."'";
						$wdaysrestrictionsmonths[] = $rmonth;
						if (strlen($restr['wdaytwo']) > 0) {
							$wdaystworestrictions[] = "'".($rmonth - 1)."': '".$restr['wdaytwo']."'";
							$monthscomborestr[($rmonth - 1)] = VikRentCar::parseJsDrangeWdayCombo($restr);
						}
					}
					if ($restr['multiplyminlos'] == 0) {
						$notmultiplyminlosrestrictions[] = $rmonth;
					}
					$minlosrestrictions[] = "'".($rmonth - 1)."': '".$restr['minlos']."'";
					if (!empty($restr['maxlos']) && $restr['maxlos'] > 0 && $restr['maxlos'] > $restr['minlos']) {
						$maxlosrestrictions[] = "'".($rmonth - 1)."': '".$restr['maxlos']."'";
					}
				}else {
					foreach ($restr as $kr => $drestr) {
						if (strlen($drestr['wday']) > 0) {
							$wdaysrestrictionsrange[$kr][0] = date('Y-m-d', $drestr['dfrom']);
							$wdaysrestrictionsrange[$kr][1] = date('Y-m-d', $drestr['dto']);
							$wdaysrestrictionsrange[$kr][2] = $drestr['wday'];
							$wdaysrestrictionsrange[$kr][3] = $drestr['multiplyminlos'];
							$wdaysrestrictionsrange[$kr][4] = strlen($drestr['wdaytwo']) > 0 ? $drestr['wdaytwo'] : -1;
							$wdaysrestrictionsrange[$kr][5] = VikRentCar::parseJsDrangeWdayCombo($drestr);
						}
						$minlosrestrictionsrange[$kr][0] = date('Y-m-d', $drestr['dfrom']);
						$minlosrestrictionsrange[$kr][1] = date('Y-m-d', $drestr['dto']);
						$minlosrestrictionsrange[$kr][2] = $drestr['minlos'];
						if (!empty($drestr['maxlos']) && $drestr['maxlos'] > 0 && $drestr['maxlos'] > $drestr['minlos']) {
							$maxlosrestrictionsrange[$kr] = $drestr['maxlos'];
						}
					}
					unset($restrictions['range']);
				}
			}
			
			$resdecl = "
var vrcrestrmonthswdays = [".implode(", ", $wdaysrestrictionsmonths)."];
var vrcrestrmonths = [".implode(", ", array_keys($restrictions))."];
var vrcrestrmonthscombojn = jQuery.parseJSON('".json_encode($monthscomborestr)."');
var vrcrestrminlos = {".implode(", ", $minlosrestrictions)."};
var vrcrestrminlosrangejn = jQuery.parseJSON('".json_encode($minlosrestrictionsrange)."');
var vrcrestrmultiplyminlos = [".implode(", ", $notmultiplyminlosrestrictions)."];
var vrcrestrmaxlos = {".implode(", ", $maxlosrestrictions)."};
var vrcrestrmaxlosrangejn = jQuery.parseJSON('".json_encode($maxlosrestrictionsrange)."');
var vrcrestrwdaysrangejn = jQuery.parseJSON('".json_encode($wdaysrestrictionsrange)."');
var vrccombowdays = {};
function vrcRefreshDropoff(darrive) {
	if(vrcFullObject(vrccombowdays)) {
		var vrctosort = new Array();
		for(var vrci in vrccombowdays) {
			if(vrccombowdays.hasOwnProperty(vrci)) {
				var vrcusedate = darrive;
				vrctosort[vrci] = vrcusedate.setDate(vrcusedate.getDate() + (vrccombowdays[vrci] - 1 - vrcusedate.getDay() + 7) % 7 + 1);
			}
		}
		vrctosort.sort(function(da, db) {
			return da > db ? 1 : -1;
		});
		for(var vrcnext in vrctosort) {
			if(vrctosort.hasOwnProperty(vrcnext)) {
				var vrcfirstnextd = new Date(vrctosort[vrcnext]);
				jQuery('#releasedate').datepicker( 'option', 'minDate', vrcfirstnextd );
				jQuery('#releasedate').datepicker( 'setDate', vrcfirstnextd );
				break;
			}
		}
	}
}
var vrcDropMaxDateSet = false;
function vrcSetMinDropoffDate () {
	var vrcDropMaxDateSetNow = false;
	var minlos = ".(intval($def_min_los) > 0 ? $def_min_los : '0').";
	var maxlosrange = 0;
	var nowpickup = jQuery('#pickupdate').datepicker('getDate');
	var nowd = nowpickup.getDay();
	var nowpickupdate = new Date(nowpickup.getTime());
	vrccombowdays = {};
	if(vrcFullObject(vrcrestrminlosrangejn)) {
		for (var rk in vrcrestrminlosrangejn) {
			if(vrcrestrminlosrangejn.hasOwnProperty(rk)) {
				var minldrangeinit = vrcGetDateObject(vrcrestrminlosrangejn[rk][0]);
				if(nowpickupdate >= minldrangeinit) {
					var minldrangeend = vrcGetDateObject(vrcrestrminlosrangejn[rk][1]);
					if(nowpickupdate <= minldrangeend) {
						minlos = parseInt(vrcrestrminlosrangejn[rk][2]);
						if(vrcFullObject(vrcrestrmaxlosrangejn)) {
							if(rk in vrcrestrmaxlosrangejn) {
								maxlosrange = parseInt(vrcrestrmaxlosrangejn[rk]);
							}
						}
						if(rk in vrcrestrwdaysrangejn && nowd in vrcrestrwdaysrangejn[rk][5]) {
							vrccombowdays = vrcrestrwdaysrangejn[rk][5][nowd];
						}
					}
				}
			}
		}
	}
	var nowm = nowpickup.getMonth();
	if(vrcFullObject(vrcrestrmonthscombojn) && vrcrestrmonthscombojn.hasOwnProperty(nowm)) {
		if(nowd in vrcrestrmonthscombojn[nowm]) {
			vrccombowdays = vrcrestrmonthscombojn[nowm][nowd];
		}
	}
	if(jQuery.inArray((nowm + 1), vrcrestrmonths) != -1) {
		minlos = parseInt(vrcrestrminlos[nowm]);
	}
	nowpickupdate.setDate(nowpickupdate.getDate() + minlos);
	jQuery('#releasedate').datepicker( 'option', 'minDate', nowpickupdate );
	if(maxlosrange > 0) {
		var diffmaxminlos = maxlosrange - minlos;
		var maxdropoffdate = new Date(nowpickupdate.getTime());
		maxdropoffdate.setDate(maxdropoffdate.getDate() + diffmaxminlos);
		jQuery('#releasedate').datepicker( 'option', 'maxDate', maxdropoffdate );
		vrcDropMaxDateSet = true;
		vrcDropMaxDateSetNow = true;
	}
	if(nowm in vrcrestrmaxlos) {
		var diffmaxminlos = parseInt(vrcrestrmaxlos[nowm]) - minlos;
		var maxdropoffdate = new Date(nowpickupdate.getTime());
		maxdropoffdate.setDate(maxdropoffdate.getDate() + diffmaxminlos);
		jQuery('#releasedate').datepicker( 'option', 'maxDate', maxdropoffdate );
		vrcDropMaxDateSet = true;
		vrcDropMaxDateSetNow = true;
	}
	if(!vrcFullObject(vrccombowdays)) {
		jQuery('#releasedate').datepicker( 'setDate', nowpickupdate );
		if (!vrcDropMaxDateSetNow && vrcDropMaxDateSet === true) {
			// unset maxDate previously set
			jQuery('#releasedate').datepicker( 'option', 'maxDate', null );
		}
	}else {
		vrcRefreshDropoff(nowpickup);
	}
}";
			
			if(count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0) {
				$resdecl .= "
var vrcrestrwdays = {".implode(", ", $wdaysrestrictions)."};
var vrcrestrwdaystwo = {".implode(", ", $wdaystworestrictions)."};
function vrcIsDayDisabled(date) {
	var actd = jQuery.datepicker.formatDate('yy-mm-dd', date);
	".(strlen($declclosingdays) > 0 ? "var loc_closing = pickupClosingDays(date); if (!loc_closing[0]) {return loc_closing;}" : "")."
	".(count($push_disabled_in) ? "var vrc_fulldays = [".implode(', ', $push_disabled_in)."]; if (jQuery.inArray(actd, vrc_fulldays) >= 0) {return [false];}" : "")."
	var m = date.getMonth(), wd = date.getDay();
	if(vrcFullObject(vrcrestrwdaysrangejn)) {
		for (var rk in vrcrestrwdaysrangejn) {
			if(vrcrestrwdaysrangejn.hasOwnProperty(rk)) {
				var wdrangeinit = vrcGetDateObject(vrcrestrwdaysrangejn[rk][0]);
				if(date >= wdrangeinit) {
					var wdrangeend = vrcGetDateObject(vrcrestrwdaysrangejn[rk][1]);
					if(date <= wdrangeend) {
						if(wd != vrcrestrwdaysrangejn[rk][2]) {
							if(vrcrestrwdaysrangejn[rk][4] == -1 || wd != vrcrestrwdaysrangejn[rk][4]) {
								return [false];
							}
						}
					}
				}
			}
		}
	}
	if(vrcFullObject(vrcrestrwdays)) {
		if(jQuery.inArray((m+1), vrcrestrmonthswdays) == -1) {
			return [true];
		}
		if(wd == vrcrestrwdays[m]) {
			return [true];
		}
		if(vrcFullObject(vrcrestrwdaystwo)) {
			if(wd == vrcrestrwdaystwo[m]) {
				return [true];
			}
		}
		return [false];
	}
	return [true];
}
function vrcIsDayDisabledDropoff(date) {
	var actd = jQuery.datepicker.formatDate('yy-mm-dd', date);
	".(strlen($declclosingdays) > 0 ? "var loc_closing = dropoffClosingDays(date); if (!loc_closing[0]) {return loc_closing;}" : "")."
	".(count($push_disabled_out) ? "var vrc_fulldays = [".implode(', ', $push_disabled_out)."]; if (jQuery.inArray(actd, vrc_fulldays) >= 0) {return [false];}" : "")."
	var m = date.getMonth(), wd = date.getDay();
	if(vrcFullObject(vrccombowdays)) {
		if(jQuery.inArray(wd, vrccombowdays) != -1) {
			return [true];
		}else {
			return [false];
		}
	}
	if(vrcFullObject(vrcrestrwdaysrangejn)) {
		for (var rk in vrcrestrwdaysrangejn) {
			if(vrcrestrwdaysrangejn.hasOwnProperty(rk)) {
				var wdrangeinit = vrcGetDateObject(vrcrestrwdaysrangejn[rk][0]);
				if(date >= wdrangeinit) {
					var wdrangeend = vrcGetDateObject(vrcrestrwdaysrangejn[rk][1]);
					if(date <= wdrangeend) {
						if(wd != vrcrestrwdaysrangejn[rk][2] && vrcrestrwdaysrangejn[rk][3] == 1) {
							return [false];
						}
					}
				}
			}
		}
	}
	if(vrcFullObject(vrcrestrwdays)) {
		if(jQuery.inArray((m+1), vrcrestrmonthswdays) == -1 || jQuery.inArray((m+1), vrcrestrmultiplyminlos) != -1) {
			return [true];
		}
		if(wd == vrcrestrwdays[m]) {
			return [true];
		}
		return [false];
	}
	return [true];
}";
			}
			$document->addScriptDeclaration($resdecl);
		}
		// VRC 1.12 - Restrictions End
		//locations closing days (1.7)
		if (strlen($declclosingdays) > 0) {
			$declclosingdays .= '
function pickupClosingDays(date) {
	var dmy = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
	var wday = date.getDay().toString();
	var arrlocclosd = jQuery("#place").val();
	var checklocarr = window["loc"+arrlocclosd+"closingdays"];
	if (jQuery.inArray(dmy, checklocarr) == -1 && jQuery.inArray(wday, checklocarr) == -1) {
		return [true, ""];
	} else {
		return [false, "", "'.addslashes(JText::_('VRCLOCDAYCLOSED')).'"];
	}
}
function dropoffClosingDays(date) {
	var dmy = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
	var wday = date.getDay().toString();
	var arrlocclosd = jQuery("#returnplace").val();
	var checklocarr = window["loc"+arrlocclosd+"closingdays"];
	if (jQuery.inArray(dmy, checklocarr) == -1 && jQuery.inArray(wday, checklocarr) == -1) {
		return [true, ""];
	} else {
		return [false, "", "'.addslashes(JText::_('VRCLOCDAYCLOSED')).'"];
	}
}';
			$document->addScriptDeclaration($declclosingdays);
		}
		//
		//Minimum Num of Days of Rental (VRC 1.8)
		$dropdayplus = $def_min_los;
		$forcedropday = "jQuery('#releasedate').datepicker( 'option', 'minDate', selectedDate );";
		if (strlen($dropdayplus) > 0 && intval($dropdayplus) > 0) {
			$forcedropday = "
var nowpick = jQuery(this).datepicker('getDate');
if (nowpick) {
	var nowpickdate = new Date(nowpick.getTime());
	nowpickdate.setDate(nowpickdate.getDate() + ".$dropdayplus.");
	jQuery('#releasedate').datepicker( 'option', 'minDate', nowpickdate );
	jQuery('#releasedate').datepicker( 'setDate', nowpickdate );
}";
		}
		//
		$sdecl = "
var vrc_fulldays_in = [".implode(', ', $push_disabled_in)."];
var vrc_fulldays_out = [".implode(', ', $push_disabled_out)."];
function vrcIsDayFullIn(date) {
	var actd = jQuery.datepicker.formatDate('yy-mm-dd', date);
	if (jQuery.inArray(actd, vrc_fulldays_in) == -1) {
		return ".(strlen($declclosingdays) > 0 ? 'pickupClosingDays(date)' : '[true]').";
	}
	return [false];
}
function vrcIsDayFullOut(date) {
	var actd = jQuery.datepicker.formatDate('yy-mm-dd', date);
	if (jQuery.inArray(actd, vrc_fulldays_out) == -1) {
		return ".(strlen($declclosingdays) > 0 ? 'dropoffClosingDays(date)' : '[true]').";
	}
	return [false];
}
function vrcLocationWopening(mode) {
	if (typeof vrc_wopening_pick === 'undefined') {
		return true;
	}
	if (mode == 'pickup') {
		vrc_mopening_pick = null;
	} else {
		vrc_mopening_drop = null;
	}
	var loc_data = mode == 'pickup' ? vrc_wopening_pick : vrc_wopening_drop;
	var def_loc_hours = mode == 'pickup' ? vrc_hopening_pick : vrc_hopening_drop;
	var sel_d = jQuery((mode == 'pickup' ? '#pickupdate' : '#releasedate')).datepicker('getDate');
	if (!sel_d) {
		return true;
	}
	var sel_wday = sel_d.getDay();
	if (!vrcFullObject(loc_data) || !loc_data.hasOwnProperty(sel_wday) || !loc_data[sel_wday].hasOwnProperty('fh')) {
		if (def_loc_hours !== null) {
			// populate the default opening time dropdown
			jQuery((mode == 'pickup' ? '#vrccomselph' : '#vrccomseldh')).html(def_loc_hours);
		}
		return true;
	}
	if (mode == 'pickup') {
		vrc_mopening_pick = new Array(loc_data[sel_wday]['fh'], loc_data[sel_wday]['fm']);
	} else {
		vrc_mopening_drop = new Array(loc_data[sel_wday]['th'], loc_data[sel_wday]['tm']);
	}
	var hlim = loc_data[sel_wday]['fh'] < loc_data[sel_wday]['th'] ? loc_data[sel_wday]['th'] : (24 + loc_data[sel_wday]['th']);
	hlim = loc_data[sel_wday]['fh'] == 0 && loc_data[sel_wday]['th'] == 0 ? 23 : hlim;
	var hopts = '';
	var def_hour = jQuery((mode == 'pickup' ? '#vrccomselph' : '#vrccomseldh')).find('select').val();
	def_hour = def_hour.length > 1 && def_hour.substr(0, 1) == '0' ? def_hour.substr(1) : def_hour;
	def_hour = parseInt(def_hour);
	for (var h = loc_data[sel_wday]['fh']; h <= hlim; h++) {
		var viewh = h > 23 ? (h - 24) : h;
		hopts += '<option value=\''+viewh+'\''+(viewh == def_hour ? ' selected' : '')+'>'+(viewh < 10 ? '0'+viewh : viewh)+'</option>';
	}
	jQuery((mode == 'pickup' ? '#vrccomselph' : '#vrccomseldh')).find('select').html(hopts);
	if (mode == 'pickup') {
		setTimeout(function() {
			vrcLocationWopening('dropoff');
		}, 750);
	}
}
function vrcInitElems() {
	if (typeof vrc_wopening_pick === 'undefined') {
		return true;
	}
	vrc_hopening_pick = jQuery('#vrccomselph').find('select').clone();
	vrc_hopening_drop = jQuery('#vrccomseldh').find('select').clone();
}
jQuery(function() {
	vrcInitElems();
	jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ '' ] );
	jQuery('#pickupdate').datepicker({
		showOn: 'focus',".(count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0 ? "\nbeforeShowDay: vrcIsDayDisabled,\n" : "\nbeforeShowDay: vrcIsDayFullIn,\n")."
		onSelect: function( selectedDate ) {
			".($totrestrictions > 0 ? "vrcSetMinDropoffDate();" : $forcedropday)."
			vrcLocationWopening('pickup');
		}
	});
	jQuery('#pickupdate').datepicker( 'option', 'dateFormat', '".$juidf."');
	jQuery('#pickupdate').datepicker( 'option', 'minDate', '".VikRentCar::getMinDaysAdvance()."d');
	jQuery('#pickupdate').datepicker( 'option', 'maxDate', '".VikRentCar::getMaxDateFuture()."');
	jQuery('#releasedate').datepicker({
		showOn: 'focus',".(count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0 ? "\nbeforeShowDay: vrcIsDayDisabledDropoff,\n" : "\nbeforeShowDay: vrcIsDayFullOut,\n")."
		onSelect: function( selectedDate ) {
			vrcLocationWopening('dropoff');
		}
	});
	jQuery('#releasedate').datepicker( 'option', 'dateFormat', '".$juidf."');
	jQuery('#releasedate').datepicker( 'option', 'minDate', '".VikRentCar::getMinDaysAdvance()."d');
	jQuery('#releasedate').datepicker( 'option', 'maxDate', '".VikRentCar::getMaxDateFuture()."');
	jQuery('#pickupdate').datepicker( 'option', jQuery.datepicker.regional[ 'vikrentcar' ] );
	jQuery('#releasedate').datepicker( 'option', jQuery.datepicker.regional[ 'vikrentcar' ] );
	jQuery('.vr-cal-img, .vrc-caltrigger').click(function() {
		var jdp = jQuery(this).prev('input.hasDatepicker');
		if (jdp.length) {
			jdp.focus();
		}
	});
});";
		$document->addScriptDeclaration($sdecl);
		$selform .= "<div class=\"vrcsfentrycont\"><div class=\"vrcsfentrylabsel\"><div class=\"vrcsfentrydate\"><input type=\"text\" name=\"pickupdate\" id=\"pickupdate\" placeholder=\"Выберите дату\" required size=\"10\" autocomplete=\"off\"/><i class=\"fa fa-calendar vrc-caltrigger\"></i></div></div><div class=\"vrcsfentrytime\"><span id=\"vrccomselph\"><select name=\"pickuph\">" . $hours . "</select></span></span><span class=\"vrctimesep\">:</span><span id=\"vrccomselpm\"><select name=\"pickupm\">" . $minutes . "</select></span></div></div>\n";
		// close pickup part, and start the DIV container for the drop off part
		$selform .= "</div><div class=\"vrc-searchf-section-dropoff\">\n";
		//
		
		if (@ is_array($places)) {
		$selform .= "<div class=\"vrcsfentrycont\"><label for=\"returnplace\">" . JText::_('VRRETURNCARORD') . "</label><div class=\"vrcsfentryselect\"><select name=\"returnplace\" id=\"returnplace\"".(strlen($onchangeplacesdrop) > 0 ? $onchangeplacesdrop : "").">";
		foreach ($places as $pla) {
			$selform .= "<option value=\"" . $pla['id'] . "\" id=\"returnplace".$pla['id']."\">" . $pla['name'] . "</option>\n";
		}
		$selform .= "</select></div></div>\n";
	}
		
		$selform .= "<div class=\"vrcsfentrycont\"><div class=\"vrcsfentrylabsel\"><div class=\"vrcsfentrydate\"><input type=\"text\" name=\"releasedate\" id=\"releasedate\" placeholder=\"Выберите дату\" required size=\"10\" autocomplete=\"off\"/><i class=\"fa fa-calendar vrc-caltrigger\"></i></div></div><div class=\"vrcsfentrytime\"><span id=\"vrccomseldh\"><select name=\"releaseh\">" . $hours . "</select></span></span><span class=\"vrctimesep\">:</span><span id=\"vrccomseldm\"><select name=\"releasem\">" . $minutes . "</select></span></div></div>";
	} else {
		//default Joomla Calendar
		JHTML::_('behavior.calendar');
		$selform .= "<div class=\"vrcsfentrycont\"><div class=\"vrcsfentrylabsel\"><label for=\"pickupdate\">" . JText::_('VRPICKUPCAR') . "</label><div class=\"vrcsfentrydate\">" . JHTML::_('calendar', '', 'pickupdate', 'pickupdate', $vrcdateformat, array (
			'class' => '',
			'size' => '10',
			'maxlength' => '19'
		)) . "</div></div><div class=\"vrcsfentrytime\"><label>" . JText::_('VRALLE') . "</label><span id=\"vrccomselph\"><select name=\"pickuph\">" . $hours . "</select></span><span class=\"vrctimesep\">:</span><span id=\"vrccomselpm\"><select name=\"pickupm\">" . $minutes . "</select></span></div></div>\n";
		// close pickup part, and start the DIV container for the drop off part
		$selform .= "</div><div class=\"vrc-searchf-section-dropoff\">\n";
		//
		
		if (@ is_array($places)) {
		$selform .= "<div class=\"vrcsfentrycont\"><label for=\"returnplace\">" . JText::_('VRRETURNCARORD') . "</label><div class=\"vrcsfentryselect\"><select name=\"returnplace\" id=\"returnplace\"".(strlen($onchangeplacesdrop) > 0 ? $onchangeplacesdrop : "").">";
		foreach ($places as $pla) {
			$selform .= "<option value=\"" . $pla['id'] . "\" id=\"returnplace".$pla['id']."\">" . $pla['name'] . "</option>\n";
		}
		$selform .= "</select></div></div>\n";
	}
		
		$selform .= "<div class=\"vrcsfentrycont\"><div class=\"vrcsfentrylabsel\"><label for=\"releasedate\">" . JText::_('VRRETURNCAR') . "</label><div class=\"vrcsfentrydate\">" . JHTML::_('calendar', '', 'releasedate', 'releasedate', $vrcdateformat, array (
			'class' => '',
			'size' => '10',
			'maxlength' => '19'
		)) . "</div></div><div class=\"vrcsfentrytime\"><label>" . JText::_('VRALLEDROP') . "</label><span id=\"vrccomseldh\"><select name=\"releaseh\">" . $hours . "</select></span><span class=\"vrctimesep\">:</span><span id=\"vrccomseldm\"><select name=\"releasem\">" . $minutes . "</select></span></div></div>";
	}
	//
	
	// close drop off part
	$selform .= "</div>\n";
	//
	// start submit part
	$selform .= "<div class=\"vrc-searchf-section-sbmt\">";
	//
	$selform .= "<div class=\"vrcsfentrycont\"><div class=\"vrcsfentrysubmit\"><input type=\"submit\" name=\"search\" value=\"" . JText::_('VRCBOOKTHISCAR') . "\" class=\"vrcdetbooksubmit\"/></div></div>\n";
	// close submit part
	$selform .= "</div>";
	//
	$selform .= "\n";
	//locations on google map
	if (count($coordsplaces) > 0) {
		$selform .= '<div class="vrclocationsbox"><div class="vrclocationsmapdiv"><a href="'.JURI::root().'index.php?option=com_vikrentcar&view=locationsmap&tmpl=component" class="vrcmodalframe" target="_blank">'.JText::_('VRCLOCATIONSMAP').'</a></div></div>';
	}
	//
	$selform .= (!empty ($pitemid) ? "<input type=\"hidden\" name=\"Itemid\" value=\"" . $pitemid . "\"/>" : "") . "</form></div>";
	?>
	
	
	
	<div class="cd-popup" role="alert">
	<div class="cd-popup-container">
		<h2>Параметры аренды автомобиля</h2>
		     <div>	
	<?php
	echo $selform;
?>
</div>
 
		<a href="#0" class="cd-popup-close img-replace">Закрыть</a>
	</div>
</div>


	

	


<?php
	/**
	 * Form submit JS validation (mostly used for the opening/closing minutes).
	 * This piece of code should be always printed in the DOM as the main form
	 * calls this function when going on submit.
	 * 
	 * @since 	1.12
	 */
	?>
	<script type="text/javascript">
	function vrcCleanNumber(snum) {
		if (snum.length > 1 && snum.substr(0, 1) == '0') {
			return parseInt(snum.substr(1));
		}
		return parseInt(snum);
	}
	function vrcValidateSearch() {
		if (typeof jQuery === 'undefined' || typeof vrc_wopening_pick === 'undefined') {
			return true;
		}
		if (vrc_mopening_pick !== null) {
			// pickup time
			var pickh = jQuery('#vrccomselph').find('select').val();
			var pickm = jQuery('#vrccomselpm').find('select').val();
			if (!pickh || !pickh.length || !pickm) {
				return true;
			}
			pickh = vrcCleanNumber(pickh);
			pickm = vrcCleanNumber(pickm);
			if (pickh == vrc_mopening_pick[0]) {
				if (pickm < vrc_mopening_pick[1]) {
					// location is still closed at this time
					jQuery('#vrccomselpm').find('select').html('<option value="'+vrc_mopening_pick[1]+'">'+(vrc_mopening_pick[1] < 10 ? '0'+vrc_mopening_pick[1] : vrc_mopening_pick[1])+'</option>').val(vrc_mopening_pick[1]);
				}
			}
		}

		if (vrc_mopening_drop !== null) {
			// dropoff time
			var droph = jQuery('#vrccomseldh').find('select').val();
			var dropm = jQuery('#vrccomseldm').find('select').val();
			if (!droph || !droph.length || !dropm) {
				return true;
			}
			droph = vrcCleanNumber(droph);
			dropm = vrcCleanNumber(dropm);
			if (droph == vrc_mopening_drop[0]) {
				if (dropm > vrc_mopening_drop[1]) {
					// location is already closed at this time
					jQuery('#vrccomseldm').find('select').html('<option value="'+vrc_mopening_drop[1]+'">'+(vrc_mopening_drop[1] < 10 ? '0'+vrc_mopening_drop[1] : vrc_mopening_drop[1])+'</option>').val(vrc_mopening_drop[1]);
				}
			}
		}

		return true;
	}
	</script>
	<?php
	//
	if (!empty($ppickup)) {
		?>
		<script type="text/javascript">
		jQuery(document).ready(function() {
		<?php
		if ($calendartype == "jqueryui") {
			?>
			jQuery("#pickupdate").datepicker("setDate", new Date(<?php echo date('Y', $ppickup); ?>, <?php echo ((int)date('n', $ppickup) - 1); ?>, <?php echo date('j', $ppickup); ?>));
			jQuery(".ui-datepicker-current-day").click();
			<?php
		} else {
			?>
			jQuery("#pickupdate").val("<?php echo date($df, $ppickup); ?>");
			<?php
		}
		?>
		});
		</script>
		<?php
	}
	if (!empty($viewingdayts) && !empty($pday) && $viewingdayts >= $nowts) {
		?>
		<script type="text/javascript">
		jQuery(document).ready(function() {
		<?php
		if (!count($push_disabled_in) || !in_array('"'.date('Y-m-d', $viewingdayts).'"', $push_disabled_in)) {
		?>
			jQuery("#pickupdate").datepicker("setDate", new Date(<?php echo date('Y', $viewingdayts); ?>, <?php echo ((int)date('n', $viewingdayts) - 1); ?>, <?php echo date('j', $viewingdayts); ?>));
		<?php
		}
		?>
			if (jQuery(".vrc-hourlycal-container").length) {
				jQuery('html,body').animate({ scrollTop: (jQuery(".vrc-hourlycal-container").offset().top - 5) }, { duration: 'slow' });	
			}
		});
		</script>
		<?php
	}

} else {
	echo VikRentCar::getDisabledRentMsg();
}
VikRentCar::printTrackingCode();
