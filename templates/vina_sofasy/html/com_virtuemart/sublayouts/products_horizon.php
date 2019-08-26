<?php
/**
 * sublayout products
 *
 * @package	VirtueMart
 * @author Max Milbers
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL2, see LICENSE.php
 * @version $Id: cart.php 7682 2014-02-26 17:07:20Z Milbo $
 */

defined('_JEXEC') or die('Restricted access');
$doc = JFactory::getDocument();
//Chung Pham
JHTML::_('behavior.modal');

//Include Helix3 plugin
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';

if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $helix3 = helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}
$vm_labels_new 			= $helix3->getParam('vm_labels_new', 1);
$vm_labels_sales 		= $helix3->getParam('vm_labels_sales', 1);
$vm_product_desc_limit 	= $helix3->getParam('vm_product_desc_limit', 60);
$show_two_image 		= $helix3->getParam('show_two_image', 1);
$lazy_load 				= $helix3->getParam('lazy_load', 0);
$days 					= VmConfig::get('latest_products_days');
$productModel 			= VmModel::getModel('product');
//End Chung Pham

$products_per_row 	= empty($viewData['products_per_row'])? 1:$viewData['products_per_row'] ;
$currency 			= $viewData['currency'];
$showRating 		= $viewData['showRating'];
$verticalseparator 	= " vertical-separator";

echo shopFunctionsF::renderVmSubLayout('askrecomjs');

$ItemidStr = '';
$Itemid = shopFunctionsF::getLastVisitedItemId();
if(!empty($Itemid)){
	$ItemidStr = '&Itemid='.$Itemid;
}

$dynamic = false;
if (vRequest::getInt('dynamic',false)) {
	$dynamic = true;
}

foreach ($viewData['products'] as $type => $products ) {
	
	$productModel->addImages($products, 0);
	$col = 1;
	$nb = 1;
	$row = 1;

	if($dynamic){
		$rowsHeight[$row]['product_s_desc'] = 1;
		$rowsHeight[$row]['price'] = 1;
		$rowsHeight[$row]['customfields'] = 1;
		$col = 2;
		$nb = 2;
	} else {
	$rowsHeight = shopFunctionsF::calculateProductRowsHeights($products,$currency,$products_per_row);

		if( (!empty($type) and count($products)>0) or (count($viewData['products'])>1 and count($products)>0)){
			$productTitle = vmText::_('COM_VIRTUEMART_'.strtoupper($type).'_PRODUCT'); ?>
	<div class="<?php echo $type ?>-view">
		<h4><?php echo $productTitle ?></h4>
			<?php // Start the Output
		}
	}

	// Calculating Products Per Row
	$cellwidth = ' width'.floor ( 100 / $products_per_row );

	$BrowseTotalProducts = count($products);

	
	foreach ( $products as $product ) {
		$link 		= empty($product->link)? $product->canonical:$product->link;
					
		$detail 	= JHtml::link($link.$ItemidStr,vmText::_ ( '<i class="zmdi zmdi-eye"></i><span>'.JText::_('VINA_DETAIL').'</span>' ), array ('title' => $product->product_name, 'class' => 'btn btn-default' ) );
		$quickview 	= $link.$ItemidStr.'&amp;tmpl=component'; 		
		$quickview  = JHTML::link($quickview, vmText::_('<i class="zmdi zmdi-fullscreen"></i>'), array('title' => vmText::_('VINA_QUICK_VIEW'), 'class' => 'btn btn-default vina-quickview modal' ));
		
		// Show the horizontal seperator
		if ($col == 1 && $nb > $products_per_row) { ?>
	<!--<div class="horizontal-separator"></div>-->
		<?php }

		// this is an indicator wether a row needs to be opened or not
		if ($col == 1) { ?>
	<div class="row">
		<?php }

		// Show the vertical seperator
		if ($nb == $products_per_row or $nb % $products_per_row == 0) {
			$show_vertical_separator = ' ';
		} else {
			$show_vertical_separator = $verticalseparator;
		}
	
		if(!is_object($product) or empty($product->link)) {
			vmdebug('$product is not object or link empty',$product);
			continue;
		}
		// End

		// Chung Pham Show Label Sale Or New								
		$isSaleLabel = (!empty($product->prices['discountAmount'])) ? 1 : 0;
		$isNewLabel = false;
		if((strtotime('now') - strtotime ($product->created_on)) < ($days * 86400)) {
			$isNewLabel = true;	
		}
		// Percentage
		if ($isSaleLabel) {
			$dtaxs = array();
			if($product->prices["DATax"]) $dtaxs = $product->prices["DATax"];
			if($product->prices["DBTax"]) $dtaxs = $product->prices["DBTax"];				
			foreach($dtaxs as $dtax){
				if(!empty($dtax)) {
					$discount = rtrim(rtrim($dtax[1],'0'),'.');
					$operation = $dtax[2];
					$percentage = "";					
					switch($operation) {
						case '-':
							$percentage = "-".$discount;
							break;
						case '+':
							$percentage = "+".$discount;
							break;
						case '-%':
							$percentage = "-".$discount."%";
							break;
						case '+%':
							$percentage = "+".$discount."%";
							break;
						default:
							return true;	
					}
				}					
			}
		}
		// End Percentage
    // Show Products ?>
	<div class="product vm-products-horizon vm-col<?php echo ' vm-col-' .  $products_per_row . $show_vertical_separator ?>">
		<div class="spacer product-container">
			<div class="vm-product-media-container">
			
				<!-- Check Product Label -->
				<?php if($vm_labels_new && $vm_labels_sales) { ?>
					<div class="product-status">									
						<?php if(($isSaleLabel != 0) && $vm_labels_sales) : ?>
							<div class="label-pro status-sale"><span><?php echo $percentage; ?></span></div>
						<?php endif; ?>
						<?php if($isNewLabel && $vm_labels_new) : ?>
							<div class="label-pro status-new"><span><?php echo JTEXT::_('VM_LANG_NEW'); ?></span></div>
						<?php endif; ?>
					</div>
				<?php }?>
				
				<div class="image-block <?php echo ($show_two_image && $product->images[1]) ? 'two-image' : ''; ?>">
					<a title="<?php echo $product->product_name ?>" href="<?php echo $product->link.$ItemidStr; ?>">
						<div class="pro-image first-image"> 
							<?php
							
							if($lazy_load) { ?>
								<img class="browseProductImage lazy" data-src="<?php echo JURI::base().$product->images[0]->file_url; ?>" alt="<?php echo $product->images[0]->file_title; ?>"> <?php
							} else {
								echo $product->images[0]->displayMediaThumb('class="browseProductImage"', false);
							} 
							?>
						</div>
						<?php
						if($show_two_image && $product->images[1]) { ?>
							<div class="pro-image second-image"> 
								<?php 
								if($lazy_load) { ?>
									<img class="browseProductImage lazy" data-src="<?php echo JURI::base().$product->images[1]->file_url; ?>" alt="<?php echo $product->images[1]->file_title; ?>"><?php
								} else {
									echo $product->images[1]->displayMediaThumb('class="browseProductImage"', false); 
								}?>
							</div><?php
						} ?>
					</a>
				</div>
			</div>
			<div class="text-block">
				<h3 class="product-title product-title-horizon">
						<?php echo JHtml::link ($product->link.$ItemidStr, $product->product_name); ?>
				</h3>
				<h3 class="product-title">
					<?php echo JHtml::link ($product->link.$ItemidStr, $product->product_name); ?>
				</h3>
				<!--<div class="vm-product-descr-container-<?php echo $rowsHeight[$row]['product_s_desc'] ?>"> -->
				<?php if(!empty($rowsHeight[$row]['product_s_desc'])){?>
				<div class="vm-product-descr-container">
					<p class="product_s_desc">
						<?php // Product Short Description
						if (!empty($product->product_s_desc)) {
							echo shopFunctionsF::limitStringByWord ($product->product_s_desc, $vm_product_desc_limit, '') ?>
						<?php } ?>
					</p>
				</div>
				<?php } ?>
				<div class="vm-product-rating-container">
					<?php echo shopFunctionsF::renderVmSubLayout('rating',array('showRating'=>$showRating, 'product'=>$product));
					if ( VmConfig::get ('display_stock', 1)) { ?>
						<span class="vmicon vm2-<?php echo $product->stock->stock_level ?>" title="<?php echo $product->stock->stock_tip ?>"></span>
					<?php }
					echo shopFunctionsF::renderVmSubLayout('stockhandle',array('product'=>$product));
					?>
				</div>
				<?php //echo $rowsHeight[$row]['price'] ?>
				<!--<div class="vm3pr-<?php echo $rowsHeight[$row]['price'] ?>"> --> <?php
					echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$product,'currency'=>$currency)); ?>
					<div class="clear"></div>
				<!--</div>-->
				<?php echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product)); ?>
				<div class="button-groups">
					<?php if(is_dir(JPATH_BASE."/components/com_wishlist/")) :
						$app = JFactory::getApplication();
					?>
					<div class="button-group btn-wishlist">							
						<?php require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); ?>
					</div>
					<?php endif; ?>
					<div class="button-group vm-details-button"><?php echo $detail; ?></div>
				</div>
			</div>
			<?php if(vRequest::getInt('dynamic')){
				echo vmJsApi::writeJS();
			} ?>
		</div>
	</div>

	<?php
    $nb ++;

		// Do we need to close the current row now?
		if ($nb>$BrowseTotalProducts) { 
		//if ($col == $products_per_row || $nb>$BrowseTotalProducts) { ?>
		<!--<div class="clear"></div>-->
	</div> 
	<?php
		$col = 1;
		$row++;
	} else {
	$col ++;
	}
	}

      if(!empty($type)and count($products)>0){
        // Do we need a final closing row tag?
        //if ($col != 1) {
      ?>
    <div class="clear"></div>
  </div>
    <?php
    // }
    }
  }
	$js = 'jQuery(function($) {';
	if($lazy_load) {
		$js .= '$(".lazy").lazy();';
	}
	$js .= '});';
$doc->addScriptdeclaration($js);