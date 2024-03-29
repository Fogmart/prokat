<?php
/**
 *
 * Show the product details page
 *
 * @package    VirtueMart
 * @subpackage
 * @author Max Milbers
 * @todo handle child products
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_addtocart.php 7833 2014-04-09 15:04:59Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$product = $viewData['product'];

if(isset($viewData['rowHeights'])){
	$rowHeights = $viewData['rowHeights'];
} else {
	$rowHeights['customfields'] = TRUE;
}

$init = 1;
if(isset($viewData['init'])){
	$init = $viewData['init'];
}

if(!empty($product->min_order_level) and $init<$product->min_order_level){
	$init = $product->min_order_level;
}

$step=1;
if (!empty($product->step_order_level)){
	$step=$product->step_order_level;
	if(!empty($init)){
		if($init<$step){
			$init = $step;
		} else {
			$init = ceil($init/$step) * $step;

		}
	}
	if(empty($product->min_order_level) and !isset($viewData['init'])){
		$init = $step;
	}
}

$maxOrder= '';
if (!empty($product->max_order_level)){
	$maxOrder = ' max="'.$product->max_order_level.'" ';
}

$addtoCartButton = '';
if(!VmConfig::get('use_as_catalog', 0)){
	if(!$product->addToCartButton and $product->addToCartButton!==''){
		$addtoCartButton = shopFunctionsF::getAddToCartButton ($product->orderable);
	} else {
		$addtoCartButton = $product->addToCartButton;
	}

}
$position = 'addtocart';
//if (!empty($product->customfieldsSorted[$position]) or !empty($addtoCartButton)) {


if (!VmConfig::get('use_as_catalog', 0)  ) { ?>

	<div class="addtocart-bar">
	<?php
	// Display the quantity box
	$stockhandle = VmConfig::get ('stockhandle', 'none');
	if (($stockhandle == 'disableit' or $stockhandle == 'disableadd') and ($product->product_in_stock - $product->product_ordered) < 1) { ?>
		<span class="addtocart-button ">
			<i class="fa fa-bell-o"></i>
			<a href="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&layout=notify&virtuemart_product_id=' . $product->virtuemart_product_id); ?>" class="notify addtocart-button btn btn-default">
				<span><?php echo vmText::_ ('COM_VIRTUEMART_CART_NOTIFY') ?></span>
			</a>
		</span><?php
	} else {
		$tmpPrice = (float) $product->prices['costPrice'];
		if (!( VmConfig::get('askprice', true) and empty($tmpPrice) )) { 
			$quantityClass = 'hidden';
			if ($product->orderable && JRequest::getVar('view') == 'productdetails') { 
				$quantityClass = '';
			}
			if ($product->orderable) { ?>
				<!--<label for="quantity<?php echo $product->virtuemart_product_id; ?>" class="quantity_box <?php echo $quantityClass ?>"><?php echo vmText::_ ('COM_VIRTUEMART_CART_QUANTITY'); ?>: </label>-->
				
				<span class="quantity-box <?php echo $quantityClass ?>"> 
					<button type="button" value="&#160;" class="quantity-controls quantity-minus"><span class="zmdi zmdi-minus"></span></button>
					<input type="text" class="quantity-input js-recalculate" name="quantity[]"
						data-errStr="<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>"
						value="<?php echo $init; ?>" init="<?php echo $init; ?>" step="<?php echo $step; ?>" <?php echo $maxOrder; ?> />
					<button type="button" value="&#160;" class="quantity-controls quantity-plus"><span class="zmdi zmdi-plus"></span></button>
				</span>
				<?php 
			}
			
			if(!empty($addtoCartButton)){
				if(JRequest::getVar('view') != 'productdetails' && !$product->orderable) { ?>
					<span class="addtocart-button">
						<i class="zmdi zmdi-menu"></i>
						<a class="addtocart-button btn btn-default" title ="<?php echo vmText::_ ('COM_VIRTUEMART_ADDTOCART_CHOOSE_VARIANT') ?>" href="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id); ?>">
							<span><?php echo vmText::_ ('COM_VIRTUEMART_ADDTOCART_CHOOSE_VARIANT') ?></span>
						</a>
					</span>
				<?php } else { ?>
					<span class="addtocart-button <?php echo (!$product->orderable) ? 'choose-variant' : ''; ?>">
						<?php echo $addtoCartButton ?>
					</span> <?php
				}
			} ?>
			<input type="hidden" name="virtuemart_product_id[]" value="<?php echo $product->virtuemart_product_id ?>"/>
			<noscript><input type="hidden" name="task" value="add"/></noscript> <?php
		}
	} ?>
	</div>
	<?php
	
} ?>
