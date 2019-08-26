<?php
/**
 * @package     VikRentCar
 * @subpackage  mod_vikrentcar_cars
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

// no direct access
defined('_JEXEC') or die;

$currencysymb = $params->get('currency');
$get_cars_layout = $params->get('layoutlist');
$widthroom = $params->get('widthroom');

$numb_total = $params->get('numb');
$numb_xrow = $params->get('numb_carrow');
$autoplayparam = $params->get('autoplay');

$calc_item_width = 100 / $numb_xrow;

if($autoplayparam == 1) {
	$autoplayparam_status = "true";
	
} else {
	$autoplayparam_status = "false";
}
$pagination = $params->get('pagination');

if($pagination == 1) {
	$pagination_status = "true";
} else {
	$pagination_status = "false";
}

$navigation = $params->get('navigation');

if($navigation == 1) {
	$navigation_status = "true";
} else {
	$navigation_status = "false";
}

if ($get_cars_layout == 1) {
	$document = JFactory::getDocument();
	$document->addStyleSheet(JURI::root().'modules/mod_vikrentcar_cars/src/owl.carousel.css');
	$document->addStyleSheet(JURI::root().'modules/mod_vikrentcar_cars/src/owl.theme.css');

	if (intval($params->get('loadjq')) == 1 ) {
		JHtml::_('jquery.framework', true, true);
		JHtml::_('script', JURI::root().'modules/mod_vikrentcar_cars/src/jquery.min.js', false, true, false, false);
	}
	JHtml::_('script', JURI::root().'modules/mod_vikrentcar_cars/src/owl.carousel.js', false, true, false, false);
}

$randid = isset($module) && is_object($module) && property_exists($module, 'id') ? $module->id : rand(1, 999);

	
?>
<div class="vrcmodcarsgridcontainer column-container <?php echo ($get_cars_layout) ? 'wrap ' : 'container-fluid'; ?>">
	<div>
		<div id="vrc-modcars-<?php echo $randid; ?>" class="<?php echo ($get_cars_layout) ? 'owl-carousel ' : ''; ?>vrcmodcarsgridcont-items vrcmodcarsgridhorizontal row-fluid">
		<?php
		foreach ($cars as $c) {

			$carats = modvikrentcar_carsHelper::getCarCaratOriz($c['idcarat'], array(), modvikrentcar_carsHelper::getTranslator());
			?>
			<div class="vrc-modcars-item <?php echo ($get_cars_layout) ? '' : 'vrc-modcars-grid-item'; ?>" style="<?php echo ($get_cars_layout) ? '' : 'width: '.$calc_item_width.'%;' ; ?>" data-groups='["<?php echo $c['catname']; ?>"]'>

				<figure class="vrcmodcarsgridcont-item">
					<div class="vrcmodcarsgridboxdiv">	
						<?php
						if (!empty($c['img'])) {
						?>
						<img src="<?php echo JURI::root(); ?>administrator/components/com_vikrentcar/resources/<?php echo $c['img']; ?>" class="vrcmodcarsgridimg"/>
						<?php
						}
						?>
						<div class="vrcmodcarsgrid-item_details">
						<figcaption class="vrcmodcarsgrid-item_title"><?php echo $c['name']; ?></figcaption>
				        <?php if ($params->get('show_desc')) { ?>
				       		<div class="vrcmodcarsgrid-item-desc"><?php echo $c['short_info']; ?></div>
				        <?php
						}
						?>
						<?php
						if ($c['cost'] > 0) {
						?>
						<div class="vrcmodcarsgrid-box-cost">
							<span class="vrcmodcarsgridstartfrom"><?php echo JText::_('VRCMODCARSTARTFROM'); ?></span>
							<span class="vrcmodcarsgridcarcost"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo modvikrentcar_carsHelper::numberFormat($c['cost']); ?></span></span>
						</div>
						<?php
						}
						?>
				        </div>
						<div class="vrcmodcarsgridview"><a class="btn btn-vrcmodcarsgrid-btn" href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid='.$c['id'].'&Itemid='.$params->get('itemid')); ?>"><?php echo JText::_('VRCMODCARCONTINUE'); ?></a></div>
						<div class="vrcmodcarsgrid-item-btm">
					        <?php
							if ($showcatname) {
							?>
							<div class="vrcmodcarsgrid-item_cat"><?php echo $c['catname']; ?></div>
							<?php
							}
							?>
							<div class="vrcmodcarsgrid-item_carat"><?php echo $carats; ?></div>
						</div>
					</div>	
				</figure>
			</div>
			<?php
		}
		?>
		</div>
	</div>
</div>

<?php if ($get_cars_layout == 1) { ?>
	<script type="text/javascript">
	jQuery(document).ready(function(){ 
		jQuery("#vrc-modcars-<?php echo $randid; ?>").owlCarousel({
			items : <?php echo $numb_xrow; ?>,
			autoPlay : <?php echo $autoplayparam_status; ?>,
			navigation : <?php echo $navigation_status; ?>,
			pagination : <?php echo $pagination_status; ?>,
			lazyLoad : true
		});
	});
	</script>
<?php } ?>
