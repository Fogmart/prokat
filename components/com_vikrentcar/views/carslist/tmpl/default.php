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

$cars=$this->cars;
$category=$this->category;
$vrc_tn=$this->vrc_tn;
$navig=$this->navig;

$currencysymb = VikRentCar::getCurrencySymb();

$pitemid = VikRequest::getString('Itemid', '', 'request');

if (is_array($category)) {
	?>
	<h3 class="vrcclistheadt"><?php echo $category['name']; ?></h3>
	<?php
	if(strlen($category['descr']) > 0) {
		?>
		<div class="vrccatdescr">
			<?php echo $category['descr']; ?>
		</div>
		<?php
	}
} else {
	echo VikRentCar::getFullFrontTitle($vrc_tn);
}

?>

<div class="row">
<div class="col-sm-12 col-md-12 hidden-sm hidden-xs">
<div>
<?php
jimport( 'joomla.application.module.helper' ); // подключаем требуемый класс
$module = JModuleHelper::getModules('position-748'); // заполняем массив модулями, опубликованными в позиции position-748
$attribs['style'] = 'none'; // указываем стиль вывода модуля none (так как при использовании стиля xhtml наблюдается дублирование заголовков модуля)
echo JModuleHelper::renderModule($module[0], $attribs); // выводим первый модуль из заданной позиции
?>
</div>
</div>
</div>

<div class="vrc-search-results-block">

<?php
foreach ($cars as $c) {
	$carats = VikRentCar::getCarCaratOriz($c['idcarat'], array(), $vrc_tn);
	$vcategory = VikRentCar::sayCategory($c['idcat'], $vrc_tn);
	?>
	<div class="car_result">
		<div class="vrc-car-result-left">
		<span class="vrc-car-name"><span class="carlistlink"><?php echo JText::_('VRCCARMODEL'); ?></span> <a class="carlistlink" href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid='.$c['id'].(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>"> <?php echo $c['name']; ?></a><span class="categorycarlist"><?php echo $vcategory; ?><?php echo strlen($vcategory) > 0 ? '' : ''; ?> </span></span>
		<?php
		if (!empty($c['img'])) {
			$imgpath = file_exists(VRC_ADMIN_PATH.DS.'resources'.DS.'vthumb_'.$c['img']) ? VRC_ADMIN_URI.'resources/vthumb_'.$c['img'] : VRC_ADMIN_URI.'resources/'.$c['img'];
			?>
			<img class="imgresult" alt="<?php echo $c['name']; ?>" src="<?php echo $imgpath; ?>"/>
			<?php
		}
		?>
		
		<?php
						if(!empty($carats)) {
							?>
							<div class="vrc-car-characteristics">
								<?php echo $carats; ?>
							</div>
							<?php
						}
						?>
		</div>
		<div class="vrc-car-result-right">
			<div class="vrc-car-result-rightinner">
				<div class="vrc-car-result-rightinner-deep">
					<div class="vrc-car-result-inner">
						
						<div class="vrc-car-price">
							<div class="">
							<?php
							if($c['cost'] > 0) {
							?>
								<span class="vrcstartfrom"><?php echo JText::_('VRCLISTSFROM'); ?></span>
								<span class="car_cost"> <span class="vrc_price"><?php echo strlen($c['startfrom']) > 0 ? VikRentCar::numberFormat($c['startfrom']) : VikRentCar::numberFormat($c['cost']); ?></span> <span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
							<?php
							}
							?>
							</div>
						</div>
						
						<div class="vrc-car-result-description">
						<?php
						if(!empty($c['short_info'])) {
							//BEGIN: Joomla Content Plugins Rendering
							JPluginHelper::importPlugin('content');
							$myItem = &JTable::getInstance('content');
							$dispatcher = &JDispatcher::getInstance();
							$myItem->text = $c['short_info'];
							$dispatcher->trigger('onContentPrepare', array('com_vikrentcar.carslist', &$myItem, &$params, 0));
							$c['short_info'] = $myItem->text;
							//END: Joomla Content Plugins Rendering
							echo $c['short_info'];
						}else {
							echo (strlen(strip_tags($c['info'])) > 250 ? substr(strip_tags($c['info']), 0, 250).' ...' : $c['info']);
						}
						?>
						</div>
						
					</div>
					<div class="vrc-car-lastblock">
						
						<div class="vrc-car-bookingbtn">
							<span class="vrclistgoon"><a class="btn" href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid='.$c['id'].(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>"><?php echo JText::_('VRCLISTPICK'); ?></a></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="car_separator"></div>
	<?php
}
?>
</div>

<?php
//pagination
if(strlen($navig) > 0) {
	?>
<div class="vrc-pagination"><?php echo $navig; ?></div>
	<?php
}

?>
<script type="text/javascript">
jQuery(document).ready(function() {
	if (jQuery('.car_result').length) {
		jQuery('.car_result').each(function() {
			var car_img = jQuery(this).find('.vrc-car-result-left').find('img');
			if(car_img.length) {
				jQuery(this).find('.vrc-car-result-right').find('.vrc-car-result-rightinner').find('.vrc-car-result-rightinner-deep').find('.vrc-car-result-inner').css('min-height', car_img.height()+'px');
			}
		});
	};
});
</script>
<?php
VikRentCar::printTrackingCode();
?>