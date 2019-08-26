<?php defined('_JEXEC') or die('Restricted access');

$related = $viewData['related'];
$customfield = $viewData['customfield'];
$thumb = $viewData['thumb'];
$ratingModel = VmModel::getModel('ratings');
$isSale = (!empty($related->prices['discountAmount'])) ? 1 : 0;
$pName  	= $related->product_name;
$paddtocart 	= shopFunctionsF::renderVmSubLayout( 'addtocart', array('product' => $related) );
$pLink  	= JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $related->virtuemart_product_id . '&virtuemart_category_id=' . $related->virtuemart_category_id);
$detail 		= JHTML::link($pLink, vmText::_('<i class="zmdi zmdi-eye"></i>'), array('title' => $pName, 'class' => 'btn btn-default'));
?>
<div class="vm-product-media-container">
	<div class="image-block">
		<?php
		//juri::root() For whatever reason, we used this here, maybe it was for the mails
		echo JHtml::link (JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $related->virtuemart_product_id . '&virtuemart_category_id=' . $related->virtuemart_category_id), $thumb , array('title' => $related->product_name,'target'=>'_blank')); ?>
		<div class="button-groups">
			<div class="button-group vm-details-button"><?php echo $detail; ?></div>
			
		<?php if(is_dir(JPATH_BASE."/components/com_wishlist/")) :
			$app = JFactory::getApplication();
		?>
			<div class="button-group btn-wishlist">							
				<?php 
					$product = $related;
					require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); 
				?>													
			</div>
		<?php endif; ?>
	</div>
	<?php echo $paddtocart; ?>
	</div>
</div>

<div class="text-block">
	<h3 class="product-title">
		<?php
		//juri::root() For whatever reason, we used this here, maybe it was for the mails
		echo JHtml::link (JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $related->virtuemart_product_id . '&virtuemart_category_id=' . $related->virtuemart_category_id), $related->product_name); ?>
	</h3>
	<div class="product-price">
	<?php 
	if($customfield->wPrice){
		$currency = calculationHelper::getInstance()->_currencyDisplay;
		echo $currency->createPriceDiv ('salesPrice', 'COM_VIRTUEMART_PRODUCT_SALESPRICE', $related->prices);
			if ($isSale) {
				echo '<div class="price-crossed" >'.$currency->createPriceDiv ('basePrice', 'COM_VIRTUEMART_PRODUCT_BASEPRICE', $related->prices).'</div>';	
		}
	}
	if($customfield->wDescr){
		echo '<p class="product_s_desc">'.$related->product_s_desc.'</p>';
	} ?>
	</div>
</div>