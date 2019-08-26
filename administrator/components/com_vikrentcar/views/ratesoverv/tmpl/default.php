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

$all_cars = $this->all_cars;
$carrows = $this->carrows;
$seasoncal_days = $this->seasons_cal_days;
$seasons_cal = $this->seasons_cal;
$tsstart = $this->tsstart;
$carrates = $this->carrates;
$booked_dates = $this->booked_dates;

$vrc_app = new VrcApplication();
$pdebug = VikRequest::getInt('e4j_debug', '', 'request');
$document = JFactory::getDocument();
$document->addStyleSheet(VRC_SITE_URI.'resources/jquery-ui.min.css');
JHtml::_('jquery.framework', true, true);
JHtml::_('script', VRC_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);
$currencysymb = VikRentCar::getCurrencySymb();
$vrc_df = VikRentCar::getDateFormat();
$df = $vrc_df == "%d/%m/%Y" ? 'd/m/Y' : ($vrc_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
$juidf = $vrc_df == "%d/%m/%Y" ? 'dd/mm/yy' : ($vrc_df == "%m/%d/%Y" ? 'mm/dd/yy' : 'yy/mm/dd');
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
});
var vrcMapWdays = ["'.mb_substr(JText::_('VRCSUNDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCMONDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCTUESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCWEDNESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCTHURSDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCFRIDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCSATURDAY'), 0, 3, 'UTF-8').'"];
var vrcMapMons = ["'.mb_substr(JText::_('VRMONTHONE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTWO'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTHREE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHFOUR'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHFIVE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHSIX'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHSEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHEIGHT'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHNINE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHELEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTWELVE'), 0, 3, 'UTF-8').'"];';
$document->addScriptDeclaration($ldecl);
$price_types_show = true;
$los_show = true;
$cookie = JFactory::getApplication()->input->cookie;
$cookie_tab = $cookie->get('vrcRovwRab', 'cal', 'string');
?>
<div class="vrc-ratesoverview-carsel-block">
	<form method="get" action="index.php?option=com_vikrentcar" name="vrcratesovwform">
	<input type="hidden" name="option" value="com_vikrentcar" />
		<input type="hidden" name="task" value="ratesoverv" />
		<div class="vrc-ratesoverview-carsel-entry">
			<label for="carsel"><?php echo JText::_('VRRATESOVWCAR'); ?></label>
			<select name="cid[]" onchange="document.vrcratesovwform.submit();" id="carsel">
	<?php
	foreach ($all_cars as $car) {
		?>
			<option value="<?php echo $car['id']; ?>"<?php echo $car['id'] == $carrows['id'] ? ' selected="selected"' : ''; ?>><?php echo $car['name']; ?></option>
		<?php
	}
	?>
			</select>
			<button type="button" class="btn" style="vertical-align: top;" onclick="document.vrcratesovwform.submit();"><i class="vrcicn-loop2"></i></button>
		</div>
		<div class="vrc-ratesoverview-carsel-entry vrc-ratesoverview-carsel-entry-los"<?php echo (!empty($cookie_tab) && $cookie_tab == 'cal' ? ' style="display: none;"' : ''); ?>>
			<label><?php echo JText::_('VRRATESOVWNUMNIGHTSACT'); ?></label>
	<?php
	foreach ($seasoncal_days as $numdays) {
		?>
			<span class="vrc-ratesoverview-numday" id="numdays<?php echo $numdays; ?>"><?php echo $numdays; ?></span>
			<input type="hidden" name="days_cal[]" id="inpnumdays<?php echo $numdays; ?>" value="<?php echo $numdays; ?>" />
		<?php
	}
	?>
			<input type="number" id="vrc-addnumnight" value="<?php echo ($numdays + 1); ?>" min="1"/>
			<span id="vrc-addnumnight-act"><i class="fas fa-plus-square"></i></span>
			<button type="button" class="btn vrc-apply-los-btn" onclick="document.vrcratesovwform.submit();"><?php echo JText::_('VRRATESOVWAPPLYLOS'); ?></button>
		</div>
		<div class="vrc-ratesoverview-carsel-entry">
			<label for="carselcalc"><?php echo JText::_('VRRATESOVWRATESCALCULATOR'); ?></label>
			<span class="vrc-ratesoverview-entryinline"><?php echo $vrc_app->getCalendar('', 'pickupdate', 'pickupdate', '%Y-%m-%d', array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true', 'placeholder'=>JText::_('VRPICKUPAT'))); ?></span>
			<span class="vrc-ratesoverview-entryinline"><span><?php echo JText::_('VRDAYS'); ?></span> <input type="number" id="vrc-numdays" value="1" min="1" max="999" step="1" /></span>
			<span class="vrc-ratesoverview-entryinline"><button type="button" class="btn" id="vrc-ratesoverview-calculate"><?php echo JText::_('VRRATESOVWRATESCALCULATORCALC'); ?></button></span>
		</div>
	</form>
</div>
<div class="vrc-ratesoverview-right-block">
	<div class="vrc-ratesoverview-right-inner"></div>
</div>
<br clear="all" />
<div class="vrc-ratesoverview-calculation-response"></div>
<div class="vrc-ratesoverview-roomdetails">
	<h3><i class="fas fa-car"></i> <?php echo $carrows['name']; ?></h3>
</div>

<div class="vrc-ratesoverview-tabscont">
	<div class="vrc-ratesoverview-tab-cal <?php echo (!empty($cookie_tab) && $cookie_tab == 'cal' ? 'vrc-ratesoverview-tab-active' : 'vrc-ratesoverview-tab-unactive'); ?>"><i class="vrcicn-calendar"></i> <?php echo JText::_('VRRATESOVWTABCALENDAR'); ?></div>
	<div class="vrc-ratesoverview-tab-los <?php echo (!empty($cookie_tab) && $cookie_tab == 'cal' ? 'vrc-ratesoverview-tab-unactive' : 'vrc-ratesoverview-tab-active'); ?>"><i class="vrcicn-clock"></i> <?php echo JText::_('VRRATESOVWTABLOS'); ?></div>
</div>
<br clear="all" />

<div class="vrc-ratesoverview-caltab-cont" style="display: <?php echo (!empty($cookie_tab) && $cookie_tab == 'cal' ? 'block' : 'none'); ?>;">
	<div class="vrc-ratesoverview-caltab-wrapper">
		<div class="vrc-table-responsive">
			<table class="vrcverviewtable vrcratesoverviewtable vrc-table">
				<tbody>
					<tr class="vrc-roverviewrowone">
						<td class="bluedays">
							<form name="vrcratesoverview" method="post" action="index.php?option=com_vikrentcar&amp;task=ratesoverv">
								<div class="vrc-roverview-datecmd-top">
									<div class="vrc-roverview-datecmd-date">
										<span>
											<i class="fa fa-calendar"></i>
											<input type="text" autocomplete="off" value="<?php echo date($df, $tsstart); ?>" class="vrcdatepicker" id="vrcdatepicker" name="startdate" />
										</span>
									</div>
								</div>
								<div class="vrc-roverview-datecmd">
									<a class="vrcosprevweek" onclick="prevWeek();" href="javascript: void(0);"><i class="fas fa-angle-double-left"></i></a>
									<a class="vrcosprevday" onclick="prevDay();" href="javascript: void(0);"><i class="fas fa-angle-left"></i></a>
									<a class="vrcosnextday" onclick="nextDay();" href="javascript: void(0);"><i class="fas fa-angle-right"></i></a>
									<a class="vrcosnextweek" onclick="nextWeek();" href="javascript: void(0);"><i class="fas fa-angle-double-right"></i></a>
									<input type="hidden" name="cid[]" value="<?php echo $carrows['id']; ?>" />
								</div>
							</form>
						</td>
					<?php
					$nowts = getdate($tsstart);
					$days_labels = array(
						JText::_('VRSUN'),
						JText::_('VRMON'),
						JText::_('VRTUE'),
						JText::_('VRWED'),
						JText::_('VRTHU'),
						JText::_('VRFRI'),
						JText::_('VRSAT')
					);
					$months_labels = array(
						JText::_('VRMONTHONE'),
						JText::_('VRMONTHTWO'),
						JText::_('VRMONTHTHREE'),
						JText::_('VRMONTHFOUR'),
						JText::_('VRMONTHFIVE'),
						JText::_('VRMONTHSIX'),
						JText::_('VRMONTHSEVEN'),
						JText::_('VRMONTHEIGHT'),
						JText::_('VRMONTHNINE'),
						JText::_('VRMONTHTEN'),
						JText::_('VRMONTHELEVEN'),
						JText::_('VRMONTHTWELVE')
					);
					foreach( $months_labels as $i => $v ) {
						$months_labels[$i] = mb_substr($v, 0, 3, 'UTF-8');
					}
					$cell_count = 0;
					$MAX_DAYS = 60;
					$MAX_TO_DISPLAY = 14;
					$pcheckinh = 0;
					$pcheckinm = 0;
					$pcheckouth = 0;
					$pcheckoutm = 0;
					$timeopst = VikRentCar::getTimeOpenStore();
					if (is_array($timeopst)) {
						$opent = VikRentCar::getHoursMinutes($timeopst[0]);
						$closet = VikRentCar::getHoursMinutes($timeopst[1]);
						$pcheckinh = $opent[0];
						$pcheckinm = $opent[1];
						// set default drop off time equal to pick up time to avoid getting extra days of rental
						$pcheckouth = $pcheckinh;
						$pcheckoutm = $pcheckinm;
					}
					$start_day_id = 'cell-'.$nowts['mday'].'-'.$nowts['mon'];
					$end_day_id = '';
					$weekend_arr = array(0, 6);
					while ($cell_count < $MAX_DAYS) {
						$style = '';
						if ( $cell_count >= $MAX_TO_DISPLAY ) {
							$style = 'style="display: none;"';
						} else {
							$end_day_id = 'cell-'.$nowts['mday'].'-'.$nowts['mon'];
						}
						?>
						<td class="bluedays <?php echo 'cell-'.$nowts['mday'].'-'.$nowts['mon']; ?><?php echo in_array((int)$nowts['wday'], $weekend_arr) ? ' vrc-roverw-tablewday-wend' : ''; ?>" <?php echo $style; ?>>
							<span class="vrc-roverw-tablewday"><?php echo $days_labels[$nowts['wday']]; ?></span>
							<span class="vrc-roverw-tablemday"><?php echo $nowts['mday']; ?></span>
							<span class="vrc-roverw-tablemonth"><?php echo $months_labels[$nowts['mon']-1]; ?></span>
						</td>
						<?php
						$next = $nowts['mday'] + 1;
						$dayts = mktime(0, 0, 0, $nowts['mon'], $next, $nowts['year']);
						$nowts = getdate($dayts);
						$cell_count++;
					}
					?>
					</tr>
				<?php
				$closed_carrateplans = VikRentCar::getCarRplansClosingDates($carrows['id']);
				foreach ($carrates as $carrate) {
					$nowts = getdate($tsstart);
					$cell_count = 0;
					?>
					<tr class="vrc-roverviewtablerow" id="vrc-roverw-<?php echo $carrate['id']; ?>">
						<td data-defrate="<?php echo $carrate['cost']; ?>"><span class="vrc-rplan-name"><?php echo $carrate['name']; ?></span></td>
					<?php
					while( $cell_count < $MAX_DAYS ) {
						$style = '';
						if ( $cell_count >= $MAX_TO_DISPLAY ) {
							$style = ' style="display: none;"';
						}
						$dclass = "vrc-roverw-rplan-on";
						if (count($closed_carrateplans) > 0 && array_key_exists($carrate['idprice'], $closed_carrateplans) && in_array(date('Y-m-d', $nowts[0]), $closed_carrateplans[$carrate['idprice']])) {
							$dclass = "vrc-roverw-rplan-off";
						}
						$id_block = "cell-".$nowts['mday'].'-'.$nowts['mon']."-".$nowts['year']."-".$carrate['idprice'];
						$dclass .= ' day-block';

						$today_tsin = mktime($pcheckinh, $pcheckinm, 0, $nowts['mon'], $nowts['mday'], $nowts['year']);
						$today_tsout = mktime($pcheckouth, $pcheckoutm, 0, $nowts['mon'], ($nowts['mday'] + 1), $nowts['year']);

						$tars = VikRentCar::applySeasonsCar(array($carrate), $today_tsin, $today_tsout);

						?>
						<td align="center" class="<?php echo $dclass.' cell-'.$nowts['mday'].'-'.$nowts['mon']; ?>" id="<?php echo $id_block; ?>" data-vrcprice="<?php echo $tars[0]['cost']; ?>" data-vrcdate="<?php echo date('Y-m-d', $nowts[0]); ?>" data-vrcdateread="<?php echo $months_labels[$nowts['mon']-1].' '.$nowts['mday'].' '.$days_labels[$nowts['wday']]; ?>" data-vrcspids="<?php echo (array_key_exists('spids', $tars[0]) && count($tars[0]['spids']) > 0 ? implode('-', $tars[0]['spids']) : ''); ?>"<?php echo $style; ?>>
							<span class="vrc-rplan-currency"><?php echo $currencysymb; ?></span>
							<span class="vrc-rplan-price"><?php echo $tars[0]['cost']; ?></span>
						</td>
						<?php

						$next = $nowts['mday'] + 1;
						$dayts = mktime(0, 0, 0, $nowts['mon'], $next, $nowts['year']);
						$nowts = getdate($dayts);
						
						$cell_count++;
					}
					?>
					</tr>
					<?php
				}
				?>
					<tr class="vrc-roverviewtableavrow">
						<td><span class="vrc-roverview-roomunits"><?php echo $carrows['units']; ?></span><span class="vrc-roverview-uleftlbl"><?php echo JText::_('VRPCHOOSEBUSYCAVAIL'); ?></span></td>
					<?php
					$nowts = getdate($tsstart);
					$cell_count = 0;
					while ($cell_count < $MAX_DAYS) {
						$style = '';
						if ($cell_count >= $MAX_TO_DISPLAY) {
							$style = ' style="display: none;"';
						}
						$dclass = "vrc-roverw-daynotbusy";
						$id_block = "cell-".$nowts['mday'].'-'.$nowts['mon']."-".$nowts['year']."-avail";

						$totfound = 0;
						$last_bid = 0;
						if (array_key_exists($carrows['id'], $booked_dates) && is_array($booked_dates[$carrows['id']])) {
							foreach ($booked_dates[$carrows['id']] as $b) {
								$tmpone = getdate($b['ritiro']);
								$rit = ($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
								$ritts = strtotime($rit);
								$tmptwo = getdate($b['consegna']);
								$con = ($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
								$conts = strtotime($con);
								if ($nowts[0] >= $ritts && $nowts[0] < $conts) {
									$dclass = "vrc-roverw-daybusy";
									$last_bid = $b['idorder'];
									if ($b['stop_sales'] > 0) {
										$totfound = $carrows['units'];
									} else {
										$totfound++;
									}
								}
							}
						}
						$units_remaining = $carrows['units'] - $totfound;
						if ($units_remaining > 0 && $units_remaining < $carrows['units'] && $carrows['units'] > 1) {
							$dclass .= " vrc-roverw-daybusypartially";
						} elseif ($units_remaining <= 0 && $carrows['units'] <= 1 && !empty($last_bid)) {
							// no booking color tag.
						}

						?>
						<td align="center" class="<?php echo $dclass.' cell-'.$nowts['mday'].'-'.$nowts['mon']; ?>" id="<?php echo $id_block; ?>" data-vrcdateread="<?php echo $months_labels[$nowts['mon']-1].' '.$nowts['mday'].' '.$days_labels[$nowts['wday']]; ?>"<?php echo $style; ?>>
							<span class="vrc-roverw-curunits"><?php echo $units_remaining; ?></span>
						</td>
						<?php

						$next = $nowts['mday'] + 1;
						$dayts = mktime(0, 0, 0, ($nowts['mon'] < 10 ? "0".$nowts['mon'] : $nowts['mon']), ($next < 10 ? "0".$next : $next), $nowts['year']);
						$nowts = getdate($dayts);
						
						$cell_count++;
					}
					?>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="vrc-ratesoverview-period-container">
			<div class="vrc-ratesoverview-period-inner">
				<div class="vrc-ratesoverview-period-lbl">
					<span><?php echo JText::_('VRROVWSELPERIOD'); ?></span>
				</div>
				<div class="vrc-ratesoverview-period-boxes">
					<div class="vrc-ratesoverview-period-boxes-inner">
						<div class="vrc-ratesoverview-period-box-left">
							<div class="vrc-ratesoverview-period-box-lbl">
								<span><?php echo JText::_('VRROVWSELPERIODFROM'); ?></span>
							</div>
							<div class="vrc-ratesoverview-period-box-val">
								<div id="vrc-ratesoverview-period-from">
									<span class="vrc-ratesoverview-period-wday"></span>
									<span class="vrc-ratesoverview-period-mday"></span>
									<span class="vrc-ratesoverview-period-month"></span>
								</div>
								<span id="vrc-ratesoverview-period-from-icon"><i class="fa fa-calendar"></i></span>
							</div>
						</div>
						<div class="vrc-ratesoverview-period-box-right">
							<div class="vrc-ratesoverview-period-box-lbl">
								<span><?php echo JText::_('VRROVWSELPERIODTO'); ?></span>
							</div>
							<div class="vrc-ratesoverview-period-box-val">
								<div id="vrc-ratesoverview-period-to">
									<span class="vrc-ratesoverview-period-wday"></span>
									<span class="vrc-ratesoverview-period-mday"></span>
									<span class="vrc-ratesoverview-period-month"></span>
								</div>
								<span id="vrc-ratesoverview-period-to-icon"><i class="fa fa-calendar"></i></span>
							</div>
						</div>
					</div>
					<div class="vrc-ratesoverview-period-box-cals" style="display: none;">
						<div class="vrc-ratesoverview-period-box-cals-inner">
							<div class="vrc-ratesoverview-period-cal-left">
								<h4><?php echo JText::_('VRROVWSELPERIODFROM'); ?></h4>
								<div id="vrc-period-from"></div>
								<input type="hidden" id="vrc-period-from-val" value="" />
							</div>
							<div class="vrc-ratesoverview-period-cal-right">
								<h4><?php echo JText::_('VRROVWSELPERIODTO'); ?></h4>
								<div id="vrc-period-to"></div>
								<input type="hidden" id="vrc-period-to-val" value="" />
							</div>
							<div class="vrc-ratesoverview-period-cal-cmd">
								<h4><?php echo JText::_('VRROVWSELRPLAN'); ?></h4>
								<div class="vrc-ratesoverview-period-cal-cmd-inner">
									<select id="vrc-selperiod-rplanid" onchange="vrcUpdateRplan();">
									<?php
									foreach ($carrates as $krr => $carrate) {
										?>
										<option value="<?php echo $carrate['idprice']; ?>" data-defrate="<?php echo $carrate['cost']; ?>"<?php echo $krr < 1 ? ' selected="selected"' : ''; ?>><?php echo $carrate['name']; ?></option>
										<?php
									}
									?>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="vrc-ratesoverview-lostab-cont"<?php echo (!empty($cookie_tab) && $cookie_tab == 'cal' ? ' style="display: none;"' : ''); ?>>
	<?php
	if (count($seasons_cal) > 0) {
		//Special Prices Timeline
		if (isset($seasons_cal['seasons']) && count($seasons_cal['seasons'])) {
			?>
	<div class="vrc-timeline-container">
		<ul id="vrc-timeline">
			<?php
			foreach ($seasons_cal['seasons'] as $ks => $timeseason) {
				$s_val_diff = '';
				if ($timeseason['val_pcent'] == 2) {
					//percentage
					$s_val_diff = (($timeseason['diffcost'] - abs($timeseason['diffcost'])) > 0.00 ? VikRentCar::numberFormat($timeseason['diffcost']) : intval($timeseason['diffcost']))." %";
				} else {
					//absolute
					$s_val_diff = $currencysymb.''.VikRentCar::numberFormat($timeseason['diffcost']);
				}
				$s_explanation = array();
				if (empty($timeseason['year'])) {
					$s_explanation[] = JText::_('VRSEASONANYYEARS');
				}
				if (!empty($timeseason['losoverride'])) {
					$s_explanation[] = JText::_('VRSEASONBASEDLOS');
				}
				?>
			<li data-fromts="<?php echo $timeseason['from_ts']; ?>" data-tots="<?php echo $timeseason['to_ts']; ?>">
				<input type="radio" name="timeline" class="vrc-timeline-radio" id="vrc-timeline-dot<?php echo $ks; ?>" <?php echo $ks === 0 ? 'checked="checked"' : ''; ?>/>
				<div class="vrc-timeline-relative">
					<label class="vrc-timeline-label" for="vrc-timeline-dot<?php echo $ks; ?>"><?php echo $timeseason['spname']; ?></label>
					<span class="vrc-timeline-date"><?php echo VikRentCar::formatSeasonDates($timeseason['from_ts'], $timeseason['to_ts']); ?></span>
					<span class="vrc-timeline-circle" onclick="Javascript: jQuery('#vrc-timeline-dot<?php echo $ks; ?>').trigger('click');"></span>
				</div>
				<div class="vrc-timeline-content">
					<p>
						<span class="vrc-seasons-calendar-slabel vrc-seasons-calendar-season-<?php echo $timeseason['type'] == 2 ? 'discount' : 'charge'; ?>"><?php echo $timeseason['type'] == 2 ? '-' : '+'; ?> <?php echo $s_val_diff; ?> <?php echo JText::_('VRSEASONPERDAY'); ?></span>
						<br/>
						<?php
						if (count($s_explanation) > 0) {
							echo implode(' - ', $s_explanation);
						}
						?>
					</p>
				</div>
			</li>
				<?php
			}
			?>
		</ul>
	</div>
	<script>
	jQuery(document).ready(function(){
		jQuery('.vrc-timeline-container').css('min-height', (jQuery('.vrc-timeline-container').outerHeight() + 20));
	});
	</script>
			<?php
		}
		//
		//Begin Seasons Calendar
		?>
	<table class="vrc-seasons-calendar-table">
		<tr class="vrc-seasons-calendar-nightsrow">
			<td>&nbsp;</td>
		<?php
		foreach ($seasons_cal['offseason'] as $numdays => $ntars) {
			?>
			<td><span><?php echo JText::sprintf(($numdays > 1 ? 'VRSEASONCALNUMDAYS' : 'VRSEASONCALNUMDAY'), $numdays); ?></span></td>
			<?php
		}
		?>
		</tr>
		<tr class="vrc-seasons-calendar-offseasonrow">
			<td>
				<span class="vrc-seasons-calendar-offseasonname"><?php echo JText::_('VRSEASONSCALOFFSEASONPRICES'); ?></span>
			</td>
		<?php
		foreach ($seasons_cal['offseason'] as $numdays => $tars) {
			?>
			<td>
				<div class="vrc-seasons-calendar-offseasoncosts">
					<?php
					foreach ($tars as $tar) {
						?>
					<div class="vrc-seasons-calendar-offseasoncost">
						<?php
						if ($price_types_show) {
						?>
						<span class="vrc-seasons-calendar-pricename"><?php echo $tar['name']; ?></span>
						<?php
						}
						?>
						<span class="vrc-seasons-calendar-pricecost">
							<span class="vrc_currency"><?php echo $currencysymb; ?></span><span class="vrc_price"><?php echo VikRentCar::numberFormat($tar['cost']); ?></span>
						</span>
					</div>
						<?php
						if (!$price_types_show) {
							break;
						}
					}
					?>
				</div>
			</td>
			<?php
		}
		?>
		</tr>
		<?php
		if (!isset($seasons_cal['seasons'])) {
			$seasons_cal['seasons'] = array();
		}
		foreach ($seasons_cal['seasons'] as $s_id => $s) {
			$restr_diff_nights = array();
			if ($los_show && array_key_exists($s_id, $seasons_cal['restrictions'])) {
				$restr_diff_nights = VikRentCar::compareSeasonRestrictionsNights($seasons_cal['restrictions'][$s_id]);
			}
			$s_val_diff = '';
			if ($s['val_pcent'] == 2) {
				//percentage
				$s_val_diff = (($s['diffcost'] - abs($s['diffcost'])) > 0.00 ? VikRentCar::numberFormat($s['diffcost']) : intval($s['diffcost']))." %";
			} else {
				//absolute
				$s_val_diff = $currencysymb.''.VikRentCar::numberFormat($s['diffcost']);
			}
			?>
		<tr class="vrc-seasons-calendar-seasonrow">
			<td>
				<div class="vrc-seasons-calendar-seasondates">
					<span class="vrc-seasons-calendar-seasonfrom"><?php echo date($df, $s['from_ts']); ?></span>
					<span class="vrc-seasons-calendar-seasondates-separe">-</span>
					<span class="vrc-seasons-calendar-seasonto"><?php echo date($df, $s['to_ts']); ?></span>
				</div>
				<div class="vrc-seasons-calendar-seasonchargedisc">
					<span class="vrc-seasons-calendar-slabel vrc-seasons-calendar-season-<?php echo $s['type'] == 2 ? 'discount' : 'charge'; ?>"><span class="vrc-seasons-calendar-operator"><?php echo $s['type'] == 2 ? '-' : '+'; ?></span><?php echo $s_val_diff; ?></span>
				</div>
				<span class="vrc-seasons-calendar-seasonname"><a href="index.php?option=com_vikrentcar&amp;task=editseason&amp;cid[]=<?php echo $s['id']; ?>" target="_blank"><?php echo $s['spname']; ?></a></span>
			<?php
			if ($los_show && array_key_exists($s_id, $seasons_cal['restrictions']) && count($restr_diff_nights) == 0) {
				//Season Restrictions
				$season_restrictions = array();
				foreach ($seasons_cal['restrictions'][$s_id] as $restr) {
					$season_restrictions = $restr;
					break;
				}
				?>
				<div class="vrc-seasons-calendar-restrictions">
				<?php
				if ($season_restrictions['minlos'] > 1) {
					?>
					<span class="vrc-seasons-calendar-restriction-minlos"><?php echo JText::_('VRRESTRMINLOS'); ?><span class="vrc-seasons-calendar-restriction-minlos-badge"><?php echo $season_restrictions['minlos']; ?></span></span>
					<?php
				}
				if (array_key_exists('maxlos', $season_restrictions) && $season_restrictions['maxlos'] > 1) {
					?>
					<span class="vrc-seasons-calendar-restriction-maxlos"><?php echo JText::_('VRRESTRMAXLOS'); ?><span class="vrc-seasons-calendar-restriction-maxlos-badge"><?php echo $season_restrictions['maxlos']; ?></span></span>
					<?php
				}
				if (array_key_exists('wdays', $season_restrictions) && count($season_restrictions['wdays']) > 0) {
					?>
					<div class="vrc-seasons-calendar-restriction-wdays">
						<label><?php echo JText::_((count($season_restrictions['wdays']) > 1 ? 'VRRESTRARRIVWDAYS' : 'VRRESTRARRIVWDAY')); ?></label>
					<?php
					foreach ($season_restrictions['wdays'] as $wday) {
						?>
						<span class="vrc-seasons-calendar-restriction-wday"><?php echo VikRentCar::sayWeekDay($wday); ?></span>
						<?php
					}
					?>
					</div>
					<?php
				} elseif ((array_key_exists('cta', $season_restrictions) && count($season_restrictions['cta']) > 0) || (array_key_exists('ctd', $season_restrictions) && count($season_restrictions['ctd']) > 0)) {
					if (array_key_exists('cta', $season_restrictions) && count($season_restrictions['cta']) > 0) {
						?>
					<div class="vrc-seasons-calendar-restriction-wdays vrc-seasons-calendar-restriction-cta">
						<label><?php echo JText::_('VRCRESTRWDAYSCTA'); ?></label>
						<?php
						foreach ($season_restrictions['cta'] as $wday) {
							?>
						<span class="vrc-seasons-calendar-restriction-wday"><?php echo VikRentCar::sayWeekDay(str_replace('-', '', $wday)); ?></span>
							<?php
						}
						?>
					</div>
						<?php
					}
					if (array_key_exists('ctd', $season_restrictions) && count($season_restrictions['ctd']) > 0) {
						?>
					<div class="vrc-seasons-calendar-restriction-wdays vrc-seasons-calendar-restriction-ctd">
						<label><?php echo JText::_('VRCRESTRWDAYSCTD'); ?></label>
						<?php
						foreach ($season_restrictions['ctd'] as $wday) {
							?>
						<span class="vrc-seasons-calendar-restriction-wday"><?php echo VikRentCar::sayWeekDay(str_replace('-', '', $wday)); ?></span>
							<?php
						}
						?>
					</div>
						<?php
					}
				}
				?>
				</div>
				<?php
			}
			?>
			</td>
			<?php
			if (array_key_exists($s_id, $seasons_cal['season_prices']) && count($seasons_cal['season_prices'][$s_id]) > 0) {
				foreach ($seasons_cal['season_prices'][$s_id] as $numdays => $tars) {
					$show_day_cost = true;
					if ($los_show && array_key_exists($s_id, $seasons_cal['restrictions']) && array_key_exists($numdays, $seasons_cal['restrictions'][$s_id])) {
						if ($seasons_cal['restrictions'][$s_id][$numdays]['allowed'] === false) {
							$show_day_cost = false;
						}
					}
					?>
			<td>
				<?php
				if ($show_day_cost) {
				?>
				<div class="vrc-seasons-calendar-seasoncosts">
					<?php
					foreach ($tars as $tar) {
						//print the types of price that are not being modified by this special price with opacity
						$not_affected = (!array_key_exists('origdailycost', $tar));
						//
						?>
					<div class="vrc-seasons-calendar-seasoncost<?php echo ($not_affected ? ' vrc-seasons-calendar-seasoncost-notaffected' : ''); ?>">
						<?php
						if ($price_types_show) {
						?>
						<span class="vrc-seasons-calendar-pricename"><?php echo $tar['name']; ?></span>
						<?php
						}
						?>
						<span class="vrc-seasons-calendar-pricecost">
							<span class="vrc_currency"><?php echo $currencysymb; ?></span><span class="vrc_price"><?php echo VikRentCar::numberFormat($tar['cost']); ?></span>
						</span>
					</div>
						<?php
						if (!$price_types_show) {
							break;
						}
					}
					?>
				</div>
				<?php
				} else {
					?>
					<div class="vrc-seasons-calendar-seasoncosts-disabled"></div>
					<?php
				}
				?>
			</td>
					<?php
				}
			}
			?>
		</tr>
			<?php
		}
		?>
	</table>
		<?php
		//End Seasons Calendar
	} else {
		?>
	<p class="vrc-warning"><?php echo JText::_('VRNOPRICESFOUND'); ?></p>
		<?php
	}
	?>
</div>

<div class="vrc-info-overlay-block">
	<a class="vrc-info-overlay-close" href="javascript: void(0);"></a>
	<div class="vrc-info-overlay-content vrc-info-overlay-content-rovervw">
		<div class="vrc-roverw-infoblock">
			<span id="rovervw-carname"></span>
			<span id="rovervw-rplan"></span>
			<span id="rovervw-fromdate"></span> - <span id="rovervw-todate"></span>
		</div>
		<div class="vrc-roverw-alldays">
			<div class="vrc-roverw-alldays-inner"></div>
		</div>
		<div class="vrc-roverw-setnewrate">
			<h4><i class="vrcicn-calculator"></i><?php echo JText::_('VRRATESOVWSETNEWRATE'); ?></h4>
			<div class="vrc-roverw-setnewrate-inner">
				<span class="vrc-roverw-setnewrate-currency"><?php echo $currencysymb; ?></span> <input type="number" step="any" min="0" id="roverw-newrate" value="" placeholder="" size="7" />
				<div class="vrc-roverw-setnewrate-btns">
					<button type="button" class="btn btn-danger" onclick="hideVrcDialog();"><?php echo JText::_('VRANNULLA'); ?></button>
					<button type="button" class="btn btn-success" onclick="setNewRates();"><i class="vrcicn-checkmark"></i><?php echo JText::_('VRAPPLY'); ?></button>
				</div>
			</div>
		</div>
		<div class="vrc-roverw-closeopenrp">
			<h4><i class="vrcicn-switch"></i><?php echo JText::_('VRRATESOVWCLOSEOPENRRP'); ?> <span id="rovervw-closeopen-rplan"></span></h4>
			<div class="vrc-roverw-closeopenrp-btns">
				<button type="button" class="btn btn-danger" onclick="modRoomRatePlan('close');"><i class="vrcicn-exit"></i><?php echo JText::_('VRRATESOVWCLOSERRP'); ?></button>
				<button type="button" class="btn btn-success" onclick="modRoomRatePlan('open');"><i class="vrcicn-enter"></i><?php echo JText::_('VRRATESOVWOPENRRP'); ?></button>
				<br clear="all" /><br />
				<button type="button" class="btn btn-danger" onclick="hideVrcDialog();"><?php echo JText::_('VRANNULLA'); ?></button>
			</div>
		</div>
	</div>
	<div class="vrc-info-overlay-loading">
		<div><?php echo JText::_('VIKLOADING'); ?></div>
	</div>
</div>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikrentcar">
</form>
<script type="text/Javascript">
function vrcFormatCalDate(idc) {
	var vrc_period = document.getElementById('vrc-'+idc+'-val').value;
	if (!vrc_period || !vrc_period.length) {
		return false;
	}
	var vrc_period_parts = vrc_period.split("/");
	if ('%d/%m/%Y' == '<?php echo $vrc_df; ?>') {
		var period_date = new Date(vrc_period_parts[2], (parseInt(vrc_period_parts[1]) - 1), parseInt(vrc_period_parts[0], 10), 0, 0, 0, 0);
		var data = [parseInt(vrc_period_parts[0], 10), parseInt(vrc_period_parts[1]), vrc_period_parts[2]];
	} else if ('%m/%d/%Y' == '<?php echo $vrc_df; ?>') {
		var period_date = new Date(vrc_period_parts[2], (parseInt(vrc_period_parts[0]) - 1), parseInt(vrc_period_parts[1], 10), 0, 0, 0, 0);
		var data = [parseInt(vrc_period_parts[1], 10), parseInt(vrc_period_parts[0]), vrc_period_parts[2]];
	} else {
		var period_date = new Date(vrc_period_parts[0], (parseInt(vrc_period_parts[1]) - 1), parseInt(vrc_period_parts[2], 10), 0, 0, 0, 0);
		var data = [parseInt(vrc_period_parts[2], 10), parseInt(vrc_period_parts[1]), vrc_period_parts[0]];
	}
	var elcont = jQuery('#vrc-ratesoverview-'+idc);
	elcont.find('.vrc-ratesoverview-period-wday').text(vrcMapWdays[period_date.getDay()]);
	elcont.find('.vrc-ratesoverview-period-mday').text(period_date.getDate());
	elcont.find('.vrc-ratesoverview-period-month').text(vrcMapMons[period_date.getMonth()]);
	jQuery('#vrc-ratesoverview-'+idc+'-icon').hide();
	data.push(jQuery('#vrc-selperiod-rplanid').val());
	data.push(jQuery('#vrc-selperiod-rplanid option:selected').text());
	data.push(jQuery('#vrc-selperiod-rplanid option:selected').attr('data-defrate'));
	var struct = getPeriodStructure(data);
	if (idc.indexOf('from') >= 0) {
		//period from date selected
		if (!vrclistener.pickFirst(struct)) {
			//first already picked: update it
			vrclistener.first = struct;
		}
	}
	if (idc.indexOf('to') >= 0) {
		//period to date selected
		if (!vrclistener.pickFirst(struct)) {
			//first already picked
			if ((vrclistener.first.isBeforeThan(struct) || vrclistener.first.isSameDay(struct)) && vrclistener.first.isSameRplan(struct)) {
				//last > first: pick last
				if (vrclistener.pickLast(struct)) {
					showVrcDialogPeriod();
				}
			}
		}
	}
}
jQuery(document).ready(function() {
	jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ '' ] );
	jQuery('#vrcdatepicker').datepicker({
		showOn: 'focus',
		dateFormat: '<?php echo $juidf; ?>',
		minDate: '0d',
		numberOfMonths: 2,
		onSelect: function( selectedDate ) {
			document.vrcratesoverview.submit();
		}
	});
	jQuery('#vrc-period-from').datepicker({
		dateFormat: '<?php echo $juidf; ?>',
		minDate: '0d',
		altField: '#vrc-period-from-val',
		onSelect: function( selectedDate ) {
			jQuery('#vrc-period-to').datepicker("option", "minDate", selectedDate);
			vrcFormatCalDate('period-from');
		}
	});
	jQuery('#vrc-period-to').datepicker({
		dateFormat: '<?php echo $juidf; ?>',
		minDate: '0d',
		altField: '#vrc-period-to-val',
		onSelect: function( selectedDate ) {
			jQuery('#vrc-period-from').datepicker("option", "maxDate", selectedDate);
			vrcFormatCalDate('period-to');
		}
	});
	jQuery('.vrc-ratesoverview-period-box-left, .vrc-ratesoverview-period-box-right').click(function() {
		jQuery('.vrc-ratesoverview-period-box-cals').fadeToggle();
	});
});
var _START_DAY_ = '<?php echo $start_day_id; ?>';
var _END_DAY_ = '<?php echo $end_day_id; ?>';
<?php
if ($df == "Y/m/d") {
	?>
Date.prototype.format = "yy/mm/dd";
	<?php
} elseif ($df == "m/d/Y") {
	?>
Date.prototype.format = "mm/dd/yy";
	<?php
} else {
	?>
Date.prototype.format = "dd/mm/yy";
	<?php
}
?>
var dropcars = document.getElementById("carsel");
var carid = dropcars.value;
var carname = dropcars.options[dropcars.selectedIndex].text;
var currencysymb = '<?php echo $currencysymb; ?>';
var debug_mode = '<?php echo $pdebug; ?>';
var roverw_messages = {
	"setNewRatesMissing": "<?php echo addslashes(JText::_('VRRATESOVWERRNEWRATE')); ?>",
	"modRplansMissing": "<?php echo addslashes(JText::_('VRRATESOVWERRMODRPLANS')); ?>",
	"openSpLink": "<?php echo addslashes(JText::_('VRRATESOVWOPENSPL')); ?>"
};
</script>
<script type="text/Javascript">
/* Dates navigation - Start */
function prevDay() {
	if ( canPrev(_START_DAY_) ) {
		jQuery('.'+_START_DAY_).prev().show();
		jQuery('.'+_END_DAY_).hide();
		
		if ( canPrev(_START_DAY_) ) {
			var start = jQuery('.'+_START_DAY_).first();
			var end = jQuery('.'+_END_DAY_).first();
			
			_START_DAY_ = start.prev().prop('class').split(' ')[1];
			_END_DAY_ = end.prev().prop('class').split(' ')[1];
			
			return true;
		} 
	}
	
	return false;
}

function nextDay() {
	if ( canNext(_END_DAY_) ) {
		jQuery('.'+_START_DAY_).hide();
		jQuery('.'+_END_DAY_).next().show();
		
		if ( canNext(_END_DAY_) ) {
			var start = jQuery('.'+_START_DAY_).first();
			var end = jQuery('.'+_END_DAY_).first();
			
			_START_DAY_ = start.next().prop('class').split(' ')[1];
			_END_DAY_ = end.next().prop('class').split(' ')[1];
			
			return true;
		} 
	}
	
	return false;
}

function prevWeek() {
	var i = 0;
	while( i++ < 7 && prevDay() );
}

function nextWeek() {
	var i = 0;
	while( i++ < 7 && nextDay() );
}

function canPrev(start) {
	return ( jQuery('.'+start).first().prev().prop('class').split(' ').length > 1 );
}

function canNext(end) {
	return ( jQuery('.'+end).first().next().length > 0 );
}
/* Dates navigation - End */
/* Dates selection - Start */
var vrclistener = null;
var vrcdialog_on = false;
jQuery(document).ready(function() {
	vrclistener = new CalendarListener();
	jQuery('.day-block').click(function() {
		pickBlock( jQuery(this).attr('id') );
	});
	jQuery('.day-block').hover(
		function() {
			if ( vrclistener.isFirstPicked() && !vrclistener.isLastPicked() ) {
				var struct = initBlockStructure(jQuery(this).attr('id'));
				var all_blocks = getAllBlocksBetween(vrclistener.first, struct, false);
				if ( all_blocks !== false ) {
					jQuery.each(all_blocks, function(k, v){
						if ( !v.hasClass('block-picked-middle') ) {
							v.addClass('block-picked-middle');
						}
					});
					jQuery(this).addClass('block-picked-end');
				}
			}
		},
		function() {
			if ( !vrclistener.isLastPicked() ) {
				jQuery('.day-block').removeClass('block-picked-middle block-picked-end');
			}
		}
	);
	jQuery(document).keydown(function(e) {
		if ( e.keyCode == 27 ) {
			hideVrcDialog();
		}
	});
	jQuery(document).mouseup(function(e) {
		if (!vrcdialog_on) {
			return false;
		}
		var vrc_overlay_cont = jQuery(".vrc-info-overlay-content");
		if (!vrc_overlay_cont.is(e.target) && vrc_overlay_cont.has(e.target).length === 0) {
			hideVrcDialog();
		}
	});
	jQuery("body").on("click", ".vrc-roverw-daymod-infospids", function() {
		var helem = jQuery(this).next('.vrc-roverw-daymod-infospids-outcont');
		if (helem.length && helem.is(":visible")) {
			jQuery(this).removeClass("vrc-roverw-daymod-infospids-on");
			helem.hide();
		} else {
			jQuery(".vrc-roverw-daymod-infospids-on").removeClass("vrc-roverw-daymod-infospids-on");
			jQuery(".vrc-roverw-daymod-infospids-outcont").hide();
			jQuery(this).addClass("vrc-roverw-daymod-infospids-on");
			helem.show();
		}
	});
	jQuery('.vrc-roverw-closeopenrp h4').click(function() {
		jQuery('.vrc-roverw-closeopenrp-btns').fadeToggle();
	});
});

function showVrcDialog() {
	var format = new Date().format;
	jQuery("#rovervw-carname").html(carname);
	jQuery("#rovervw-rplan").html(vrclistener.first.rplanName);
	jQuery("#rovervw-closeopen-rplan").html('"'+vrclistener.first.rplanName+'"');
	jQuery("#rovervw-fromdate").html(vrclistener.first.toDate(format));
	jQuery("#rovervw-todate").html(vrclistener.last.toDate(format));
	jQuery(".vrc-roverw-alldays-inner").html("");
	var all_blocks = getAllBlocksBetween(vrclistener.first, vrclistener.last, true);
	if ( all_blocks !== false ) {
		var newdayscont = '';
		jQuery.each(all_blocks, function(k, v) {
			var spids = jQuery(v).attr("data-vrcspids").split("-");
			var spids_det = '';
			if (jQuery(v).attr("data-vrcspids").length > 0 && spids.length > 0) {
				spids_det += "<div class=\"vrc-roverw-daymod-infospids\"><span><i class=\"fa fa-info\"></i></span></div>";
				spids_det += "<div class=\"vrc-roverw-daymod-infospids-outcont\">";
				spids_det += "<div class=\"vrc-roverw-daymod-infospids-incont\"><ul>";
				for(var x = 0; x < spids.length; x++) {
					spids_det += "<li><a target=\"_blank\" href=\"index.php?option=com_vikrentcar&task=editseason&cid[]="+spids[x]+"\">"+roverw_messages.openSpLink.replace("%d", spids[x])+"</a></li>";
				}
				spids_det += "</ul></div></div>";
			}
			newdayscont += "<div class=\"vrc-roverw-daymod\"><div class=\"vrc-roverw-daymod-inner\"><div class=\"vrc-roverw-daymod-innercell\"><span class=\"vrc-roverw-daydate\">"+jQuery(v).attr("data-vrcdateread")+"</span><span class=\"vrc-roverw-dayprice\">"+v.html()+"</span>"+spids_det+"</div></div></div>";
		});
		jQuery(".vrc-roverw-alldays-inner").html(newdayscont);
		//jQuery("#roverw-newrate").attr("placeholder", vrclistener.first.defRate);
		jQuery("#roverw-newrate").val(vrclistener.first.defRate);
	}

	jQuery(".vrc-info-overlay-block").fadeIn();
	vrcdialog_on = true;
}

function showVrcDialogPeriod() {
	var format = new Date().format;
	jQuery('.vrc-ratesoverview-period-box-cals').fadeOut();
	jQuery("#rovervw-carname").html(carname);
	jQuery("#rovervw-rplan").html(vrclistener.first.rplanName);
	jQuery("#rovervw-closeopen-rplan").html('"'+vrclistener.first.rplanName+'"');
	jQuery("#rovervw-fromdate").html(vrclistener.first.toDate(format));
	jQuery("#rovervw-todate").html(vrclistener.last.toDate(format));
	jQuery(".vrc-roverw-alldays-inner").html("");
	jQuery(".vrc-info-overlay-block").fadeIn();
	vrcdialog_on = true;
}

function hideVrcDialog() {
	vrclistener.clear();
	jQuery('.day-block').removeClass('block-picked-start block-picked-middle block-picked-end');
	if (vrcdialog_on === true) {
		jQuery(".vrc-info-overlay-block").fadeOut(400, function () {
			jQuery(".vrc-info-overlay-content").show();
		});
		//reset period selection
		jQuery('#vrc-ratesoverview-period-from').find('span').text('');
		jQuery('#vrc-ratesoverview-period-from-icon').show();
		jQuery('#vrc-ratesoverview-period-to').find('span').text('');
		jQuery('#vrc-ratesoverview-period-to-icon').show();
		//
		vrcdialog_on = false;
	}
}

jQuery(document.body).on('click', '.vrc-ratesoverview-vcmwarn-close', function() {
	jQuery('.vrc-ratesoverview-right-inner').hide().html('');
});

function setNewRates() {
	var all_blocks = getAllBlocksBetween(vrclistener.first, vrclistener.last, true);
	var toval = jQuery("#roverw-newrate").val();
	var tovalint = parseFloat(toval);
	if (all_blocks !== false && toval.length > 0 && !isNaN(tovalint) && tovalint > 0.00) {
		jQuery(".vrc-info-overlay-content").hide();
		jQuery(".vrc-info-overlay-loading").prepend('<i class="fas fa-sync fa-spin fa-3x fa-fw"></i>').fadeIn();
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikrentcar", task: "setnewrates", tmpl: "component", e4j_debug: debug_mode, id_car: carid, id_price: vrclistener.first.rplan, rate: toval, fromdate: vrclistener.first.toDate("yy-mm-dd"), todate: vrclistener.last.toDate("yy-mm-dd") }
		}).done(function(res) {
			if (res.indexOf('e4j.error') >= 0) {
				console.log(res);
				alert(res.replace("e4j.error.", ""));
				jQuery(".vrc-info-overlay-content").show();
				jQuery(".vrc-info-overlay-loading").hide().find("i").remove();
			} else {
				//display new rates in all_blocks IDs
				var obj_res = JSON.parse(res);
				jQuery.each(obj_res, function(k, v) {
					if (k == 'vcm') {
						return true;
					}
					var elem = jQuery("#cell-"+k);
					if (elem.length) {
						elem.find(".vrc-rplan-price").html(v.cost);
						var spids = '';
						if (v.hasOwnProperty('spids')) {
							jQuery.each(v.spids, function(spk, spv) {
								spids += spv+'-';
							});
							//right trim dash
							spids = spids.replace(/-+$/, '');
						}
						elem.attr('data-vrcspids', spids);
					}
				});
				jQuery(".vrc-info-overlay-loading").hide().find("i").remove();
				hideVrcDialog();
			}
		}).fail(function() { 
			alert("Request Failed");
			jQuery(".vrc-info-overlay-content").show();
			jQuery(".vrc-info-overlay-loading").hide().find("i").remove();
		});
	} else {
		alert(roverw_messages.setNewRatesMissing);
		return false;
	}
}

function modRoomRatePlan(mode) {
	var all_blocks = getAllBlocksBetween(vrclistener.first, vrclistener.last, true);
	if ( all_blocks !== false && mode.length > 0 ) {
		jQuery(".vrc-info-overlay-content").hide();
		jQuery(".vrc-info-overlay-loading").prepend('<i class="fas fa-sync fa-spin fa-3x fa-fw"></i>').fadeIn();
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikrentcar", task: "modcarrateplans", tmpl: "component", e4j_debug: debug_mode, id_car: carid, id_price: vrclistener.first.rplan, type: mode, fromdate: vrclistener.first.toDate("yy-mm-dd"), todate: vrclistener.last.toDate("yy-mm-dd") }
		}).done(function(res) {
			if (res.indexOf('e4j.error') >= 0 ) {
				console.log(res);
				alert(res.replace("e4j.error.", ""));
				jQuery(".vrc-info-overlay-content").show();
				jQuery(".vrc-info-overlay-loading").hide().find("i").remove();
			} else {
				//apply new classes in all_blocks IDs
				var obj_res = JSON.parse(res);
				jQuery.each(obj_res, function(k, v) {
					var elem = jQuery("#cell-"+k);
					if (elem.length) {
						elem.removeClass(v.oldcls).addClass(v.newcls);
					}
				});
				jQuery(".vrc-info-overlay-loading").hide().find("i").remove();
				hideVrcDialog();
			}
		}).fail(function() { 
			alert("Request Failed");
			jQuery(".vrc-info-overlay-content").show();
			jQuery(".vrc-info-overlay-loading").hide().find("i").remove();
		});
	} else {
		alert(roverw_messages.modRplansMissing);
		return false;
	}
}

function vrcUpdateRplan() {
	if (vrclistener === null || vrclistener.first === null) {
		return true;
	}
	vrclistener.first.rplan = jQuery('#vrc-selperiod-rplanid').val();
	vrclistener.first.rplanName = jQuery('#vrc-selperiod-rplanid option:selected').text();
	vrclistener.first.defRate = jQuery('#vrc-selperiod-rplanid option:selected').attr('data-defrate');
}

function pickBlock(id) {
	var struct = initBlockStructure(id);
	
	if ( !vrclistener.pickFirst(struct) ) {
		// first already picked
		if ( ( vrclistener.first.isBeforeThan(struct) || vrclistener.first.isSameDay(struct) ) && vrclistener.first.isSameRplan(struct) ) {
			// last > first : pick last
			if ( vrclistener.pickLast(struct) ) {
				var all_blocks = getAllBlocksBetween(vrclistener.first, vrclistener.last, false);
				if ( all_blocks !== false ) {
					jQuery.each(all_blocks, function(k, v){
						if ( !v.hasClass('block-picked-middle') ) {
							v.addClass('block-picked-middle');
						}
					});
					jQuery('#'+vrclistener.last.id).addClass('block-picked-end');
					showVrcDialog();
				}
			}
		} else {
			// last < first : clear selection
			vrclistener.clear();
			jQuery('.day-block').removeClass('block-picked-start block-picked-middle block-picked-end');
		}
	} else {
		// first picked
		jQuery('#'+vrclistener.first.id).addClass('block-picked-start');
	}
}

function getAllBlocksBetween(start, end, outers_included) {
	if ( !start.isSameRplan(end) ) {
		return false;
	}
	
	if ( start.isAfterThan(end) ) {
		return false;
	}
	
	var queue = new Array();
	
	if ( outers_included ) {
		queue.push(jQuery('#'+start.id));
	}
	
	if ( start.isSameDay(end) ) {
		return queue;
	}

	var node = jQuery('#'+start.id).next();
	var end_id = jQuery('#'+end.id).attr('id');
	while( node.length > 0 && node.attr('id') != end_id ) {
		queue.push(node);
		node = node.next();
	}
	
	if ( outers_included ) {
		queue.push(jQuery('#'+end.id));
	}
	
	return queue;
}

function getPeriodStructure(data) {
	return {
		"day": parseInt(data[0]),
		"month": parseInt(data[1]),
		"year": parseInt(data[2]),
		"rplan": data[3],
		"rplanName": data[4],
		"defRate": data[5],
		"id": "cell-"+parseInt(data[0])+"-"+parseInt(data[1])+"-"+parseInt(data[2])+"-"+data[3],
		"isSameDay" : function(block) {
			return ( this.month == block.month && this.day == block.day && this.year == block.year );
		},
		"isBeforeThan" : function(block) {
			return ( 
				( this.year < block.year ) || 
				( this.year == block.year && this.month < block.month ) || 
				( this.year == block.year &&  this.month == block.month && this.day < block.day ) );
		},
		"isAfterThan" : function(block) {
			return ( 
				( this.year > block.year ) || 
				( this.year == block.year && this.month > block.month ) || 
				( this.year == block.year && this.month == block.month && this.day > block.day ) );
		},
		"isSameRplan" : function(block) {
			return ( this.rplan == block.rplan );
		},
		"toDate" : function(format) {
			return format.replace(
				'dd', ( this.day < 10 ? '0' : '' )+this.day
			).replace(
				'mm', ( this.month < 10 ? '0' : '' )+this.month
			).replace(
				'yy', this.year
			);
		}
	};
}

function initBlockStructure(id) {
	var s = id.split("-");
	if ( s.length != 5 ) {
		return {};
	}
	var elem = jQuery("#"+id);
	return {
		"day":parseInt(s[1]),
		"month":parseInt(s[2]),
		"year":parseInt(s[3]),
		"rplan":s[4],
		"rplanName": elem.parent("tr").find("td").first().text(),
		"defRate": elem.parent("tr").find("td").first().attr("data-defrate"),
		"id":id,
		"isSameDay" : function(block) {
			return ( this.month == block.month && this.day == block.day && this.year == block.year );
		},
		"isBeforeThan" : function(block) {
			return ( 
				( this.year < block.year ) || 
				( this.year == block.year && this.month < block.month ) || 
				( this.year == block.year &&  this.month == block.month && this.day < block.day ) );
		},
		"isAfterThan" : function(block) {
			return ( 
				( this.year > block.year ) || 
				( this.year == block.year && this.month > block.month ) || 
				( this.year == block.year && this.month == block.month && this.day > block.day ) );
		},
		"isSameRplan" : function(block) {
			return ( this.rplan == block.rplan );
		},
		"toDate" : function(format) {
			return format.replace(
				'dd', ( this.day < 10 ? '0' : '' )+this.day
			).replace(
				'mm', ( this.month < 10 ? '0' : '' )+this.month
			).replace(
				'yy', this.year
			);
		}
	};
}

function CalendarListener() {
	this.first = null;
	this.last = null;
}

CalendarListener.prototype.pickFirst = function(struct) {
	if ( !this.isFirstPicked() ) {
		this.first = struct;
		return true;
	}
	return false;
}

CalendarListener.prototype.pickLast = function(struct) {
	if ( !this.isLastPicked() && this.isFirstPicked() ) {
		this.last = struct;
		return true;
	}
	return false;
}

CalendarListener.prototype.clear = function() {
	this.first = null;
	this.last = null;
}

CalendarListener.prototype.isFirstPicked = function() {
	return this.first != null;
}

CalendarListener.prototype.isLastPicked = function() {
	return this.last != null;
}

/* Dates selection - End */
var timeline_height_set = false;
jQuery(document).ready(function() {
	jQuery(".vrc-ratesoverview-tab-los").click(function() {
		var nd = new Date();
		nd.setTime(nd.getTime() + (365*24*60*60*1000));
		document.cookie = "vrcRovwRab=los; expires=" + nd.toUTCString() + "; path=/";
		jQuery(this).removeClass("vrc-ratesoverview-tab-unactive").addClass("vrc-ratesoverview-tab-active");
		jQuery(".vrc-ratesoverview-tab-cal").removeClass("vrc-ratesoverview-tab-active").addClass("vrc-ratesoverview-tab-unactive");
		jQuery(".vrc-ratesoverview-carsel-entry-los").show();
		jQuery(".vrc-ratesoverview-caltab-cont").hide();
		jQuery(".vrc-ratesoverview-lostab-cont").fadeIn();
		if (!timeline_height_set) {
			jQuery('.vrc-timeline-container').css('min-height', (jQuery('.vrc-timeline-container').outerHeight() + 20));
			timeline_height_set = true;
		}
	});
	jQuery(".vrc-ratesoverview-tab-cal").click(function() {
		var nd = new Date();
		nd.setTime(nd.getTime() + (365*24*60*60*1000));
		document.cookie = "vrcRovwRab=cal; expires=" + nd.toUTCString() + "; path=/";
		jQuery(this).removeClass("vrc-ratesoverview-tab-unactive").addClass("vrc-ratesoverview-tab-active");
		jQuery(".vrc-ratesoverview-tab-los").removeClass("vrc-ratesoverview-tab-active").addClass("vrc-ratesoverview-tab-unactive");
		jQuery(".vrc-ratesoverview-carsel-entry-los").hide();
		jQuery(".vrc-ratesoverview-lostab-cont").hide();
		jQuery(".vrc-ratesoverview-caltab-cont").fadeIn();
	});
	if (window.location.hash == '#tabcal') {
		jQuery(".vrc-ratesoverview-tab-cal").trigger("click");
	}
	jQuery("body").on("click", ".vrc-ratesoverview-numday", function() {
		var inpday = jQuery(this).attr('id');
		if (jQuery('.vrc-ratesoverview-numday').length > 1) {
			jQuery('#inp'+inpday).remove();
			jQuery(this).remove();
		}
	});
	jQuery("body").on("dblclick", ".vrc-calcrates-rateblock", function() {
		jQuery(this).remove();
	});
	jQuery('#vrc-addnumnight-act').click(function() {
		var setdays = jQuery('#vrc-addnumnight').val();
		if (parseInt(setdays) > 0) {
			var los_exists = false;
			jQuery('.vrc-ratesoverview-numday').each(function() {
				if (parseInt(jQuery(this).text()) == parseInt(setdays)) {
					los_exists = true;
				}
			});
			if (!los_exists) {
				jQuery('.vrc-ratesoverview-numday').last().after("<span class=\"vrc-ratesoverview-numday\" id=\"numdays"+setdays+"\">"+setdays+"</span><input type=\"hidden\" name=\"days_cal[]\" id=\"inpnumdays"+setdays+"\" value=\""+setdays+"\" />");
			} else {
				jQuery('#vrc-addnumnight').val((parseInt(setdays) + 1));
			}
		}
	});
	jQuery('#vrc-ratesoverview-calculate').click(function() {
		jQuery(this).text('<?php echo addslashes(JText::_('VRRATESOVWRATESCALCULATORCALCING')); ?>').prop('disabled', true);
		jQuery('.vrc-ratesoverview-calculation-response').html('');
		var pickupdate = jQuery("#pickupdate").val();
		if (!(pickupdate.length > 0)) {
			pickupdate = '<?php echo date('Y-m-d') ?>';
			jQuery("#pickupdate").val(pickupdate);
		}
		var days = jQuery("#vrc-numdays").val();
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikrentcar", task: "calc_rates", tmpl: "component", id_car: "<?php echo $carrows['id']; ?>", pickup: pickupdate, num_days: days }
		}).done(function(res) {
			res = JSON.parse(res);
			res = res[0];
			if (res.indexOf('e4j.error') >= 0 ) {
				jQuery(".vrc-ratesoverview-calculation-response").html("<p class='vrc-warning'>" + res.replace("e4j.error.", "") + "</p>").fadeIn();
			} else {
				jQuery(".vrc-ratesoverview-calculation-response").html(res).fadeIn();
			}
			jQuery('#vrc-ratesoverview-calculate').text('<?php echo addslashes(JText::_('VRRATESOVWRATESCALCULATORCALC')); ?>').prop('disabled', false);
		}).fail(function() { 
			jQuery(".vrc-ratesoverview-calculation-response").fadeOut();
			jQuery('#vrc-ratesoverview-calculate').text('<?php echo addslashes(JText::_('VRRATESOVWRATESCALCULATORCALC')); ?>').prop('disabled', false);
			alert("Error Performing Ajax Request"); 
		});
	});
});
</script>
