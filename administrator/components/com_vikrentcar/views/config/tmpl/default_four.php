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

$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$vrc_app = VikRentCar::getVrcApplication();
$sitelogo = VikRentCar::getSiteLogo();
?>
<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRPANELFOUR'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGFOURLOGO'); ?></b> </td>
				<td><input type="file" name="sitelogo" size="35"/> <?php echo (strlen($sitelogo) > 0 ? '&nbsp;&nbsp;<a href="'.VRC_ADMIN_URI.'resources/'.$sitelogo.'" class="vrcmodal" target="_blank">'.$sitelogo.'</a>' : ''); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCSENDPDF'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('sendpdf', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::sendPDF() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>

			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCSENDEMAILSWHEN'); ?></b> </td>
				<td>
					<?php
					$sendwhen = VikRentCar::getSendEmailWhen();
					?>
					<select name="sendemailwhen">
						<option value="1"<?php echo $sendwhen < 2 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCSENDEMAILSWHENBOTH'); ?></option>
						<option value="2"<?php echo $sendwhen > 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCSENDEMAILSWHENCONF'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCICALEVENDDTTYPE'); ?></b> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCICALEVENDDTTYPE'), 'content' => JText::_('VRCICALEVENDDTTYPEHELP'))); ?></td>
				<td>
					<?php
					$icalendtype = VikRentCar::getIcalEndType();
					?>
					<select name="icalendtype">
						<option value="pick"<?php echo $icalendtype == 'pick' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCICALEVENDDTPICK'); ?></option>
						<option value="drop"<?php echo $icalendtype == 'drop' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCICALEVENDDTDROP'); ?></option>
					</select>
				</td>
			</tr>

			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTRACKCODETEMPLATE'); ?></b> </td>
				<td><button type="button" class="btn vrc-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRC_SITE_PATH.DS.'helpers'.DS.'tracking_code_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGCONVCODETEMPLATE'); ?></b> </td>
				<td><button type="button" class="btn vrc-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRC_SITE_PATH.DS.'helpers'.DS.'conversion_code_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></td>
			</tr>

			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGFOURTWO'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('allowstats', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::allowStats() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGFOURTHREE'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('sendmailstats', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::sendMailStats() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGFOURORDMAILFOOTER'); ?></b> </td>
				<td><?php echo $editor->display( "footerordmail", VikRentCar::getFooterOrdMail(), 500, 350, 70, 20 ); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGFOURFOUR'); ?></b> </td>
				<td><textarea name="disclaimer" rows="7" cols="50"><?php echo VikRentCar::getDisclaimer(); ?></textarea></td>
			</tr>
		</tbody>
	</table>
</fieldset>
