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
$document = JFactory::getDocument();
$document->addStyleSheet(VRC_SITE_URI.'resources/jquery.fancybox.css');
JHtml::_('script', VRC_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);
$themesel = '<select name="theme">';
$themesel .= '<option value="default">default</option>';
$themes = glob(VRC_SITE_PATH.DS.'themes'.DS.'*');
$acttheme = VikRentCar::getTheme();
if (count($themes) > 0) {
	$strip = VRC_SITE_PATH.DS.'themes'.DS;
	foreach ($themes as $th) {
		if (is_dir($th)) {
			$tname = str_replace($strip, '', $th);
			if ($tname != 'default') {
				$themesel .= '<option value="'.$tname.'"'.($tname == $acttheme ? ' selected="selected"' : '').'>'.$tname.'</option>';
			}
		}
	}
}
$themesel .= '</select>';
$firstwday = VikRentCar::getFirstWeekDay(true);
?>
<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRCCONFIGPAYMPART'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGFIRSTWDAY'); ?></b> </td>
				<td><select name="firstwday" style="float: none;"><option value="0"<?php echo $firstwday == '0' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCSUNDAY'); ?></option><option value="1"<?php echo $firstwday == '1' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCMONDAY'); ?></option><option value="2"<?php echo $firstwday == '2' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCTUESDAY'); ?></option><option value="3"<?php echo $firstwday == '3' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCWEDNESDAY'); ?></option><option value="4"<?php echo $firstwday == '4' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCTHURSDAY'); ?></option><option value="5"<?php echo $firstwday == '5' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCFRIDAY'); ?></option><option value="6"<?php echo $firstwday == '6' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCSATURDAY'); ?></option></select></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHREETEN'); ?></b> </td>
				<td><input type="text" name="numcalendars" value="<?php echo VikRentCar::numCalendars(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHUMBSIZE'); ?></b> </td>
				<td><input type="text" name="thumbswidth" value="<?php echo VikRentCar::getThumbnailsWidth(); ?>" size="4"/> px</td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHREENINE'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('showpartlyreserved', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::showPartlyReserved() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>

			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGEMAILTEMPLATE'); ?></b> </td>
				<td><button type="button" class="btn vrc-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRC_SITE_PATH.DS.'helpers'.DS.'email_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGPDFTEMPLATE'); ?></b> </td>
				<td><button type="button" class="btn vrc-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRC_SITE_PATH.DS.'helpers'.DS.'pdf_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGPDFCHECKINTEMPLATE'); ?></b> </td>
				<td><button type="button" class="btn vrc-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRC_SITE_PATH.DS.'helpers'.DS.'checkin_pdf_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGPDFINVOICETEMPLATE'); ?></b> </td>
				<td><button type="button" class="btn vrc-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRC_SITE_PATH.DS.'helpers'.DS.'invoices'.DS.'invoice_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCCONFIGCUSTCSSTPL'); ?></b> </td>
				<td><button type="button" class="btn vrc-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRC_SITE_PATH.DS.'vikrentcar_custom.css'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></td>
			</tr>

			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHREEONE'); ?></b> </td>
				<td><input type="text" name="fronttitle" value="<?php echo VikRentCar::getFrontTitle(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHREETWO'); ?></b> </td>
				<td><input type="text" name="fronttitletag" value="<?php echo VikRentCar::getFrontTitleTag(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHREETHREE'); ?></b> </td>
				<td><input type="text" name="fronttitletagclass" value="<?php echo VikRentCar::getFrontTitleTagClass(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHREEFOUR'); ?></b> </td>
				<td><input type="text" name="searchbtnval" value="<?php echo VikRentCar::getSubmitName(true); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHREEFIVE'); ?></b> </td>
				<td><input type="text" name="searchbtnclass" value="<?php echo VikRentCar::getSubmitClass(true); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHREESIX'); ?></b> </td>
				<td><?php echo $vrc_app->printYesNoButtons('showfooter', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::showFooter() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHEME'); ?></b> </td>
				<td><?php echo $themesel; ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHREESEVEN'); ?></b> </td>
				<td><?php echo $editor->display( "intromain", VikRentCar::getIntroMain(), 500, 350, 70, 20 ); ?></td>
			</tr>
			<tr>
				<td width="200" class="vrc-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHREEEIGHT'); ?></b> </td>
				<td><textarea name="closingmain" rows="5" cols="50"><?php echo VikRentCar::getClosingMain(); ?></textarea></td>
			</tr>
		</tbody>
	</table>
</fieldset>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(".vrc-edit-tmpl").click(function() {
		var vrc_tmpl_path = jQuery(this).attr("data-tmpl-path");
		jQuery.fancybox({
			"helpers": {
				"overlay": {
					"locked": false
				}
			},
			"href": "index.php?option=com_vikrentcar&task=edittmplfile&path="+vrc_tmpl_path+"&tmpl=component",
			"width": "75%",
			"height": "75%",
			"autoScale": false,
			"transitionIn": "none",
			"transitionOut": "none",
			//"padding": 0,
			"type": "iframe"
		});
	});
});
</script>
