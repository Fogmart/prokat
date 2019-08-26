<?php  
/**
 * Copyright (c) Extensionsforjoomla.com - E4J - Alessio <tech@extensionsforjoomla.com>
 * 
 * You should have received a copy of the License
 * along with this program.  If not, see <http://www.extensionsforjoomla.com/>.
 * 
 * For any bug, error please contact <tech@extensionsforjoomla.com>
 * We will try to fix it.
 * 
 * Extensionsforjoomla.com - All Rights Reserved
 * 
 */

defined('_JEXEC') or die('Restricted Area');

?>

<div class="vikrentcarconfirmationnumber <?php echo $params->get('moduleclass_sfx'); ?>">
	<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=userorders'); ?>" method="post">
		<input type="hidden" name="option" value="com_vikrentcar"/>
		<input type="hidden" name="view" value="userorders"/>
		<input type="hidden" name="searchorder" value="1"/>
		<div class="vrcmcfdivinternal">
			<label for="vrcconfnummod"><?php echo JText::_('VRCMODCONFNUMCONFNUM'); ?></label>
			<input type="text" name="confirmnum" value="" size="25" id="vrcconfnummod"/>
		</div>
		<div class="vrcmcfdivinternalinput">
			<input type="submit" name="searchconfnum" value="<?php echo JText::_('VRCMODCONFNUMSEARCH'); ?>"/>
		</div>
	</form>
</div>
