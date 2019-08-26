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
$formatvals = VikRentCar::getNumberFormatData(true);
$formatparts = explode(':', $formatvals);
?>
<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRCCONFIGCURRENCYPART'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHREECURNAME'); ?></b> </td>
				<td><input type="text" name="currencyname" value="<?php echo VikRentCar::getCurrencyName(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHREECURSYMB'); ?></b> </td>
				<td><input type="text" name="currencysymb" value="<?php echo VikRentCar::getCurrencySymb(true); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHREECURCODEPP'); ?></b> </td>
				<td><input type="text" name="currencycodepp" value="<?php echo VikRentCar::getCurrencyCodePp(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGNUMDECIMALS'); ?></b> </td>
				<td><input type="number" name="numdecimals" min="0" value="<?php echo $formatparts[0]; ?>"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGNUMDECSEPARATOR'); ?></b> </td>
				<td><input type="text" name="decseparator" value="<?php echo $formatparts[1]; ?>" size="2"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGNUMTHOSEPARATOR'); ?></b> </td>
				<td><input type="text" name="thoseparator" value="<?php echo $formatparts[2]; ?>" size="2"/></td>
			</tr>
		</tbody>
	</table>
</fieldset>

<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRCCONFIGPAYMPART'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTWOFIVE'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('ivainclusa', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::ivaInclusa(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTAXSUMMARY'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('taxsummary', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::showTaxOnSummaryOnly(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTWOTHREE'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('paytotal', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::payTotal() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTWOFOUR'); ?></b> </td>
				<td><input type="number" step="any" min="0" name="payaccpercent" value="<?php echo VikRentCar::getAccPerCent(); ?>"/> <select id="typedeposit" name="typedeposit"><option value="pcent">%</option><option value="fixed"<?php echo (VikRentCar::getTypeDeposit(true) == "fixed" ? ' selected="selected"' : ''); ?>><?php echo VikRentCar::getCurrencySymb(); ?></option></select></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTWOSIX'); ?></b> </td>
				<td><input type="text" name="paymentname" value="<?php echo VikRentCar::getPaymentName(); ?>" size="25"/></td>
			</tr>
		</tbody>
	</table>
</fieldset>
