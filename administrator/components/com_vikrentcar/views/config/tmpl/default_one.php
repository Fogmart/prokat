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

$vrc_app = VikRentCar::getVrcApplication();
$timeopst = VikRentCar::getTimeOpenStore(true);
if (is_array($timeopst) && $timeopst[0]!=$timeopst[1]) {
	$wtos="<input type=\"checkbox\" name=\"timeopenstorealw\" value=\"yes\"/> ".JText::_('VRCONFIGONEONE')."<br/><br/><b>".JText::_('VRCONFIGONETWO')."</b>:<br/><table><tr><td valign=\"top\">".JText::_('VRCONFIGONETHREE')."</td><td><select name=\"timeopenstorefh\">";
	$openat=VikRentCar::getHoursMinutes($timeopst[0]);
	for ($i=0; $i <= 23; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$stat=($openat[0]==$i ? " selected=\"selected\"" : "");
		$wtos.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
	}
	$wtos.="</select> <select name=\"timeopenstorefm\">";
	for ($i=0; $i <= 59; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$stat=($openat[1]==$i ? " selected=\"selected\"" : "");
		$wtos.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
	}
	$wtos.="</select></td></tr><tr><td>".JText::_('VRCONFIGONEFOUR')."</td><td><select name=\"timeopenstoreth\">";
	$closeat=VikRentCar::getHoursMinutes($timeopst[1]);
	for ($i=0; $i <= 23; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$stat=($closeat[0]==$i ? " selected=\"selected\"" : "");
		$wtos.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
	}
	$wtos.="</select> <select name=\"timeopenstoretm\">";
	for ($i=0; $i <= 59; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$stat=($closeat[1]==$i ? " selected=\"selected\"" : "");
		$wtos.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
	}
	$wtos.="</select></td></tr></table>";
} else {
	$wtos="<input type=\"checkbox\" name=\"timeopenstorealw\" value=\"yes\" checked=\"checked\"/> ".JText::_('VRCONFIGONEONE')."<br/><br/><b>".JText::_('VRCONFIGONETWO')."</b>:<br/><table><tr><td valign=\"top\">".JText::_('VRCONFIGONETHREE')."</td><td><select name=\"timeopenstorefh\">";
	for ($i=0; $i <= 23; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$wtos.="<option value=\"".$i."\">".$in."</option>\n";
	}
	$wtos.="</select> <select name=\"timeopenstorefm\">";
	for ($i=0; $i <= 59; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$wtos.="<option value=\"".$i."\">".$in."</option>\n";
	}
	$wtos.="</select></td></tr><tr><td>".JText::_('VRCONFIGONEFOUR')."</td><td><select name=\"timeopenstoreth\">";
	for ($i=0; $i <= 23; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$wtos.="<option value=\"".$i."\">".$in."</option>\n";
	}
	$wtos.="</select> <select name=\"timeopenstoretm\">";
	for ($i=0; $i <= 59; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$wtos.="<option value=\"".$i."\">".$in."</option>\n";
	}
	$wtos.="</select></td></tr></table>";
}
$calendartype = VikRentCar::calendarType(true);
$aehourschbasp = VikRentCar::applyExtraHoursChargesBasp();
$damageshowtype = VikRentCar::getDamageShowType();
$nowdf = VikRentCar::getDateFormat(true);
$nowtf = VikRentCar::getTimeFormat(true);

$maxdatefuture = VikRentCar::getMaxDateFuture(true);
$maxdate_val = intval(substr($maxdatefuture, 1, (strlen($maxdatefuture) - 1)));
$maxdate_interval = substr($maxdatefuture, -1, 1);

$vrcsef = file_exists(VRC_SITE_PATH.DS.'router.php');
?>

<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRCCONFIGBOOKINGPART'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONEFIVE'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('allowrent', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::allowRent(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONESIX'); ?></b> </td>
				<td><textarea name="disabledrentmsg" rows="5" cols="50"><?php echo VikRentCar::getDisabledRentMsg(); ?></textarea></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONETENSIX'); ?></b> </td>
				<td><input type="text" name="adminemail" value="<?php echo VikRentCar::getAdminMail(); ?>" size="30"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGSENDERMAIL'); ?></b> </td>
				<td><input type="text" name="senderemail" value="<?php echo VikRentCar::getSenderMail(); ?>" size="30"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONESEVEN'); ?></b> </td>
				<td><?php echo $wtos; ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONEELEVEN'); ?></b> </td>
				<td>
					<select name="dateformat">
						<option value="%d/%m/%Y"<?php echo ($nowdf == "%d/%m/%Y" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCONFIGONETWELVE'); ?></option>
						<option value="%Y/%m/%d"<?php echo ($nowdf=="%Y/%m/%d" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCONFIGONETENTHREE'); ?></option>
						<option value="%m/%d/%Y"<?php echo ($nowdf == "%m/%d/%Y" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCONFIGUSDATEFORMAT'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTIMEFORMAT'); ?></b> </td>
				<td>
					<select name="timeformat">
						<option value="H:i"<?php echo ($nowtf=="H:i" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCONFIGTIMEFORMATLAT'); ?></option>
						<option value="h:i A"<?php echo ($nowtf=="h:i A" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCONFIGTIMEFORMATENG'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONEEIGHT'); ?></b> </td>
				<td><input type="number" name="hoursmorerentback" value="<?php echo VikRentCar::getHoursMoreRb(); ?>" min="0"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGEHOURSBASP'); ?></b> </td>
				<td>
					<select name="ehourschbasp">
						<option value="1"<?php echo ($aehourschbasp == true ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCONFIGEHOURSBEFORESP'); ?></option>
						<option value="0"<?php echo ($aehourschbasp == false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCONFIGEHOURSAFTERSP'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCCONFIGDAMAGESHOWTYPE'); ?></b> </td>
				<td>
					<select name="damageshowtype">
						<option value="1"<?php echo ($damageshowtype == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCCONFIGDAMAGETYPEONE'); ?></option>
						<option value="2"<?php echo ($damageshowtype == 2 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCCONFIGDAMAGETYPETWO'); ?></option>
						<option value="3"<?php echo ($damageshowtype == 3 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCCONFIGDAMAGETYPETHREE'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONENINE'); ?></b> </td>
				<td><input type="number" name="hoursmorecaravail" value="<?php echo VikRentCar::getHoursCarAvail(); ?>" min="0"/> <?php echo JText::_('VRCONFIGONETENEIGHT'); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCTODAYBOOKINGS'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('todaybookings', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::todayBookings(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONECOUPONS'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('enablecoupons', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::couponsEnabled(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGENABLECUSTOMERPIN'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('enablepin', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::customersPinEnabled(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONETENFIVE'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('tokenform', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::tokenForm() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGREQUIRELOGIN'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('requirelogin', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::requireLogin(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCICALKEY'); ?></b> </td>
				<td><input type="text" name="icalkey" value="<?php echo VikRentCar::getIcalSecretKey(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONETENSEVEN'); ?></b> </td>
				<td><input type="number" name="minuteslock" value="<?php echo VikRentCar::getMinutesLock(); ?>" min="0"/></td>
			</tr>
		</tbody>
	</table>
</fieldset>

<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRCCONFIGSEARCHPART'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONEDROPDPLUS'); ?></b> </td>
				<td><input type="number" name="setdropdplus" value="<?php echo VikRentCar::setDropDatePlus(true); ?>" min="0"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGMINDAYSADVANCE'); ?></b> </td>
				<td><input type="number" name="mindaysadvance" value="<?php echo VikRentCar::getMinDaysAdvance(true); ?>" min="0"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGMAXDATEFUTURE'); ?></b> </td>
				<td><input type="number" name="maxdate" value="<?php echo $maxdate_val; ?>" min="0" style="float: none; vertical-align: top; max-width: 50px;"/> <select name="maxdateinterval" style="float: none; margin-bottom: 0;"><option value="d"<?php echo $maxdate_interval == 'd' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGMAXDATEDAYS'); ?></option><option value="w"<?php echo $maxdate_interval == 'w' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGMAXDATEWEEKS'); ?></option><option value="m"<?php echo $maxdate_interval == 'm' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGMAXDATEMONTHS'); ?></option><option value="y"<?php echo $maxdate_interval == 'y' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGMAXDATEYEARS'); ?></option></select></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONETEN'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('placesfront', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::showPlacesFront(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONETENFOUR'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('showcategories', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::showCategoriesFront(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCCONFIGSEARCHFILTCHARACTS'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('charatsfilter', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::useCharatsFilter(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
		</tbody>
	</table>
</fieldset>

<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRCCONFIGSYSTEMPART'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCCONFENMULTILANG'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('multilang', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::allowMultiLanguage(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCCONFSEFROUTER'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('vrcsef', JText::_('VRYES'), JText::_('VRNO'), (int)$vrcsef, 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCLOADFA'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('usefa', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::isFontAwesomeEnabled(true), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONEJQUERY'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('loadjquery', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::loadJquery(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGONECALENDAR'); ?></b> </td>
				<td>
					<select name="calendar">
						<option value="jqueryui"<?php echo ($calendartype == "jqueryui" ? " selected=\"selected\"" : ""); ?>>jQuery UI</option>
						<option value="joomla"<?php echo ($calendartype == "joomla" ? " selected=\"selected\"" : ""); ?>>Joomla</option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b>Google Maps API Key</b> </td>
				<td><input type="text" name="gmapskey" value="<?php echo VikRentCar::getGoogleMapsKey(); ?>" size="30" /></td>
			</tr>
		</tbody>
	</table>
</fieldset>
