<?php
/*
# ------------------------------------------------------------------------
# Vina Product Carousel for VirtueMart for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum: http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.modal');
$doc = JFactory::getDocument();
/*
if(!defined('_OWL_CAROUSEL')) {
	$doc->addScript('modules/' . $module->module . '/assets/js/owl.carousel.min.js', 'text/javascript');
	$doc->addStyleSheet('modules/' . $module->module . '/assets/css/owl.carousel.min.css');
	$doc->addStyleSheet('modules/' . $module->module . '/assets/css/owl.theme.default.min.css');
	define('_OWL_CAROUSEL', 1);
}

if(!defined('_VINA_CAROUSEL_VIRTUEMART')){
	$doc->addStyleSheet('modules/' . $module->module . '/assets/css/custom.css');
	define('_VINA_CAROUSEL_VIRTUEMART', 1);
}*/
//Include Helix3 plugin
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';

if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $helix3 = helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}

$vm_product_desc_limit 	= $helix3->getParam('vm_product_desc_limit', 60);
$show_two_image 		= $helix3->getParam('show_two_image', 1);
$days 					= VmConfig::get('latest_products_days');
$lazy_load 				= $helix3->getParam('lazy_load', 0);
$productModel 			= VmModel::getModel('product');
$productModel 			= VmModel::getModel('product');
$productModel->addImages($products, 0);
//End Chung Pham

// Add styles
$styleModule 	 	= '';
$styleItem  	 	= '';
$styleItemLink 	 	= '';
$styleProductFields = (!$productFields) ? 'display: none' : '';
$styleProductQuantity = (!$productQuantity) ? 'display: none' : '';
if($moduleConfig) {
	$styleModule .= 'width:' . $moduleWidth . ';';
	$styleModule .= 'height:' . $moduleHeight . ';';
	$styleModule .= 'margin:' . $moduleMargin. ';';
	$styleModule .= 'padding:' . $modulePadding . ';';
	$styleModule .= ($isBgColor) ? "background-color: {$bgColor};" : '';
	$styleModule .= ($bgImage != '') ? "background: url({$bgImage}) repeat scroll 0 0;" : '';
	
}
if($itemConfig) {
	$styleItem 		.= ($isItemBgColor) ? "background-color: {$itemBgColor};" : "";
	$styleItem 		.= 'margin:' . $itemMargin . ';';
	$styleItem 		.= 'padding:' .$itemPadding .';';
	$styleItem 		.= ($itemTextColor) ? "color: {$itemTextColor}" : '';
	$styleItemLink 	.= ($itemLinkColor) ? "color: {$itemLinkColor}" : '';
}
$style = '#vina-carousel-virtuemart'.$module->id .'{'
		. 'overflow: initial;'
		. $styleModule . 
	'}' .
	'#vina-carousel-virtuemart'.$module->id .' .item-i{'
		. 'overflow: hidden;'
		. $styleItem. 
	'}' . 	
	'#vina-carousel-virtuemart' .$module->id . ' .item-i a {'
		. $styleItemLink. 
	'}'.	
	'#vina-carousel-virtuemart' .$module->id . ' .product-fields {'
		. $styleProductFields . 
	'}' .
	'#vina-carousel-virtuemart' .$module->id . ' .quantity-box,' .
	'#vina-carousel-virtuemart' .$module->id . ' .quantity-controls{'
		. $styleProductQuantity . 
	'}';	
$doc->addStyleDeclaration($style);
?>

<!-- HTML Block -->
<div id="vina-carousel-virtuemart<?php echo $module->id; ?>" class="vina-carousel-virtuemart owl-carousel owl-theme horizontal <?php echo $classSuffix; ?>">
	<?php
		$col 	= 1;
		$nb 	= 1;
		foreach ($products as $product) :
			$nb ++;
			$pImage 	= (!empty($product->images[0])) ? $product->images[0]->file_url : '';
			$pName  	= $product->product_name;
			$sDesc  	= $product->product_s_desc;
			$pDesc  	= (!empty($sDesc)) ? shopFunctionsF::limitStringByWord($sDesc, 60, ' ...') : '';
			$stock  	= $productModel->getStockIndicator($product);
			$sLevel 	= $stock->stock_level;
			$sTip   	= $stock->stock_tip;
			$pLink  	= JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id);
			$detail 	= JHTML::link($pLink, vmText::_('COM_VIRTUEMART_PRODUCT_DETAILS'), array('title' => $pName, 'class' => 'product-details'));
			$handle 	= shopFunctionsF::renderVmSubLayout('stockhandle', array('product' => $product));
			$pPrice 	= shopFunctionsF::renderVmSubLayout('prices', array('product' => $product, 'currency' => $currency));
			$sPrice 	= $currency->createPriceDiv('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
			$dPrice 	= $currency->createPriceDiv('salesPriceWithDiscount', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
			$paddtocart 	= shopFunctionsF::renderVmSubLayout( 'addtocart', array('product' => $product) );
			$pImage2 	= (!empty($product->images[1])) ? $product->images[1]->file_url : '';
			if($resizeImage) {
				$pImage = modVinaCarouselVirtueMartHelper::resizeImage($resizeType, $pImage, 'thumb_', $imageWidth, $imageHeight, $module);
				$pImage2 = modVinaCarouselVirtueMartHelper::resizeImage($resizeType, $pImage2, 'thumb_', $imageWidth, $imageHeight, $module);			
			}
			//Chung Pham								
			$isSaleLabel = (!empty($product->prices['discountAmount'])) ? 1 : 0;
			$isNewLabel = false;
			if((strtotime('now') - strtotime ($product->created_on)) < ($days * 86400)) {
				$isNewLabel = true;	
			}
			
			$quickview 		= $pLink.'&amp;tmpl=component'; 
			//$quickview 	= JHTML::link($quickview, vmText::_('VINA_QUICK_VIEW'), array('title' => $pName, 'class' => 'btn btn-default vina-quickview modal' ));
			$quickview 		= JHTML::link($quickview, vmText::_('<i class="zmdi zmdi-fullscreen"></i>'), array('title' => vmText::_('VINA_QUICK_VIEW'), 'class' => 'btn btn-default vina-quickview modal' ));
			$detail 		= JHTML::link($pLink, vmText::_('<i class="zmdi zmdi-eye"></i><span>'.JText::_('VINA_DETAIL').'</span>'), array('title' => $pName, 'class' => 'btn btn-default'));
			//End Chung Pham
	?>
	<?php if($col == 1) :?>
	<div class="products">
	<?php endif; ?>
		<div class="product product-i">
			<div class="product-container">
				<!-- Image Block -->
				<?php if($productImage && !empty($pImage)) : ?>
				<div class="vm-product-media-container">
					
					<div class="image-block <?php echo ($show_two_image && $product->images[1]) ? 'two-image' : ''; ?>">
						<a href="<?php echo $pLink; ?>" title="<?php echo $pName; ?>">	
							<div class="pro-image first-image">
							<?php
							
							if($lazy_load) { ?>
								<img class="browseProductImage lazy" data-src="<?php echo $pImage; ?>" alt="<?php echo $pName; ?>" title="<?php echo $pName; ?>" /> <?php
							} else { ?>
								<img class="browseProductImage" src="<?php echo $pImage; ?>" alt="<?php echo $pName; ?>" title="<?php echo $pName; ?>" /> <?php
							} 
							?>
							</div>
							<?php
							if($show_two_image && $product->images[1]) { ?>
								<div class="pro-image second-image"> 
									<?php 
									if($lazy_load) { ?>
										<img class="browseProductImage lazy" data-src="<?php echo $pImage2; ?>" alt="<?php echo $pName; ?>" title="<?php echo $pName; ?>" /> <?php
									} else { ?>
										<img class="browseProductImage" src="<?php echo $pImage2; ?>" alt="<?php echo $pName; ?>" title="<?php echo $pName; ?>" /> <?php
									} ?>
								</div> <?php
							} ?>					
						</a>
					</div>
				</div>
				<?php endif; ?>
				
				<!-- Text Block -->
				<div class="text-block">
					<!-- Product Name -->
					<?php if($productName) : ?>
					<h3 class="product-title"><a href="<?php echo $pLink; ?>" title="<?php echo $pName; ?>"><?php echo $pName; ?></a></h3>
					<?php endif; ?>
					
					<!-- Product Rating -->
					<?php if($productRating && $ratingModel) : ?>
						<div class="vm-product-rating-container">
							<?php
							$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
							$rating = $ratingModel->getRatingByProduct($product->virtuemart_product_id);
							$reviews = $ratingModel->getReviewsByProduct($product->virtuemart_product_id);
							if(empty($rating->rating)) { ?>						
								<div class="ratingbox dummy" title="<?php echo vmText::_('COM_VIRTUEMART_UNRATED'); ?>" ></div><?php 
							} else {						
								$ratingwidth = $rating->rating * 14; ?>
								<div title=" <?php echo (vmText::_("COM_VIRTUEMART_RATING_TITLE") . round($rating->rating) . '/' . $maxrating) ?>" class="ratingbox" >
								  <div class="stars-orange" style="width:<?php echo $ratingwidth.'px'; ?>"></div>
								</div><?php 
							}
							
							if(!empty($reviews)) {					
								$count_review = 0;
								foreach($reviews as $k=>$review) {
									$count_review ++;
								} ?>
								<span class="amount">
									<a href="<?php echo $pLink; ?>" target="_blank" ><?php echo $count_review.' '.JText::_('VM_LANG_REVIEWS');?></a>
								</span>
							<?php } ?>

							<!-- Product Stock -->
							<?php if($productStock) : ?>
								<span class="vmicon vm2-<?php echo $sLevel; ?>" title="<?php echo $sTip; ?>"></span>
								<?php echo $handle; ?>
							<?php endif; ?>							
						</div>
					<?php endif; ?>
					
					<!-- Product Price -->
					<?php if($productPrice) : ?>						
						<?php echo $pPrice; ?>				
					<?php endif; ?>
					
					<!-- Product Description -->
					<?php if($productDesc && !empty($pDesc)) : ?>
					<div class="product-description"><?php echo $pDesc; ?></div>
					<?php endif; ?>
					
					<!-- Add to Cart Button & View Details Button -->
					<?php if($addtocart || $viewDetails) : ?>
					<div class="button-groups">
						<!-- Product Add To Cart -->
						<?php if($addtocart) : ?>
							<?php echo $paddtocart; ?>
						<?php endif; ?>
						
						<!-- View Details Button -->
						<?php if($viewDetails) : ?>
							<div class="button-group vm-details-button"><?php echo $detail; ?></div>
							
						<?php endif; ?>
						
						<!-- View Details wishlist -->
						<?php if(is_dir(JPATH_BASE."/components/com_wishlist/")) :
							$app = JFactory::getApplication();
						?>
							<div class="button-group btn-wishlist">							
								<?php require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); ?>													
							</div>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php 
	if($col == $itemInCol || $nb > count($products)) { 
		$col = 1;
	echo '</div>';
	}else {
		$col ++;
	}
	endforeach; ?>
</div>
<?php
//Javascript Block
//$prev = ($doc->direction == 'rtl') ? "<i class='fa fa-angle-right'></i>" : "<i class='fa fa-angle-left'></i>";
//$next = ($doc->direction == 'rtl') ? "<i class='fa fa-angle-left'></i>" : "<i class='fa fa-angle-right'></i>";
$js =  '
	jQuery(document).ready(function($) {
		$("#vina-carousel-virtuemart' . $module->id . '").owlCarousel({'
			
			.'items : 				' . $items . ','
			.'startPosition:    	' . $startPosition . ','
			.'margin: 				' . $margin . ','
			.'stagePadding:			' . $stagePadding . ','
			.'loop: 				' . ($loop ? 'true' : 'false') . ','
			.'center: 				' . ($center ? 'true' : 'false') . ','
			.'rtl: 					' . (($doc->direction == 'rtl') ? 'true' : 'false') . ','
			.'slideBy: 				' . $slideBy . ','
			.'autoplay: 			' . $autoPlay . ','
			.'autoplayTimeout: 		' . $autoplayTimeout . ','
			.'autoplaySpeed: 		' . $autoplaySpeed . ','
			.'autoplayHoverPause: 	' . ($stopOnHover ? 'true' : 'false') . ','
			.'nav:					' . ($navigation ? 'true' : 'false') . ','
			.'navRewind:			' . ($rewindNav ? 'true' : 'false') . ','
			//.'navText: 				[ "'.$prev.'", "'.$next.'" ],'
			.'navSpeed: 			' . $navigationSpeed . ','
			.'dots: 				' . ($pagination ? 'true' : 'false') . ','
			.'dotsSpeed: 			' . $paginationSpeed . ','
			.'autoWidth: 			' . ($autoWidth ? 'true' : 'false') . ','	
			.'autoHeight: 			' . ($autoHeight ? 'true' : 'false') . ','
			.'mouseDrag: 			' . ($mouseDrag ? 'true' : 'false') . ','
			.'touchDrag: 			' . ($touchDrag ? 'true' : 'false') . ',';
			if($responsive) {
			$js .= 'responsive:{'
				. '0:{'
					. 'items: ' . $itemsMobile . ',' // In this configuration 1 is enabled from 0px up to 479px screen size 
				.'},'
				.'480:{'
					.'items: ' . $itemsTabletSmall . ',' // from 480 to 767 
				.'},'
				.'768:{'
					.'items: ' . $itemsTablet . ',' // from this breakpoint 768 to 991
				.'},'
				.'992:{'
					.'items: ' . $itemsDesktopSmall . ',' // from this breakpoint 980 to 1199
				.'},'
				.'1200:{'
					.'items: ' . $itemsDesktop . ','
				.'}'
			.'}';
			}
			
		$js .= '});'
	.'});
';
$js .= 'jQuery(function($) {';
	if($lazy_load) {
		$js .= '$(".lazy").lazy();';
	}
	$js .= '});';
$doc->addScriptdeclaration($js);