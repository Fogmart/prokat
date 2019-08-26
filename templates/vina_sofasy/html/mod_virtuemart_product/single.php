<?php // no direct access
defined( '_JEXEC' ) or die('Restricted access');

//Chung Pham Include Helix3 plugin
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';

if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $helix3 = helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}

$vm_product_labels 		=  $helix3->getParam('vm_product_labels', 1);
$newLabel_date 			=  $helix3->getParam('vm_product_label_newdate', 1);
$newLabel_limit 		=  $helix3->getParam('vm_product_label_newlimit', 1);
$popup_quickview 		=  $helix3->getParam('popup_quickview', 1);
$show_two_image 		=  $helix3->getParam('show_two_image', 1);
$vm_product_desc_limit 	=  $helix3->getParam('vm_product_desc_limit', 60);

if (!function_exists('vinaResizeImage'))
{
	function vinaResizeImage($type, $file, $prefix, $width, $height, $module)
	 {
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
	  
		// Set noimage if the image isn't exists
		if(!JFile::exists($file)) {
			//$file = $current_template_path."/images/noimage.jpg";
			$app 		= JFactory::getApplication();
			$file = "templates/". $app->getTemplate() ."/images/noimage.jpg";
		}
	  
		// Check if new image is exists
		$newFile = "cache/". $module->module ."/". date("Y") ."/". $prefix . basename($file);
		if(JFile::exists($newFile)) {
			return JURI::base() . $newFile;
		}
		else {
			JFolder::create(dirname($newFile));
		}
	  
		// Instantiate our JImage object
		$image    = new JImage($file);
	  
		if ( $type == 4 )
		{
			$srcHeight  = $image->getHeight();
			$srcWidth  = $image->getWidth();
			$left   = round(($srcWidth - $width) / 2);
			$top   = round(($srcHeight - $height) / 2);
	   
			$resizedImage = $image->crop($width, $height, $left, $top, true); 
		} 
		elseif( $type == 5 ) 
		{
			$resizedImage = $image->cropResize($width, $height, true);
		} 
		else {
			$resizedImage  = $image->resize($width, $height, true, $type);
		}
	  
		$properties  = JImage::getImageFileProperties($file);
		$mime    = $properties->mime;
		if($mime == 'image/jpeg') {
			$type = IMAGETYPE_JPEG;
		}
		elseif($mime = 'image/png') {
			$type = IMAGETYPE_PNG;
		}
		elseif($mime = 'image/gif') {
			$type = IMAGETYPE_GIF;
		}
	  
		// Store the resized image to a new file
		$resizedImage->toFile($newFile, $type);
	  
		return JURI::base() . $newFile;
	}
}
 
vmJsApi::jPrice();
?>

<div class="vmgroup product-single">

	<?php if($headerText) { ?>
		<div class="vmheader"></div>
	<?php } ?>

	<div class="vmproduct productdetails">
		<?php foreach( $products as $key=>$product ) {?>
			<div class="product product-i">
				<div class="product-container <?php echo ($key%2 !=0) ? 'image-right' : 'image-left'; ?>">
					<div class= "vm-product-media-container">
						<div class= "image-block">
							<?php
							$pImage = (!empty($product->images[0])) ? $product->images[0]->file_url : '';
							$pImage = vinaResizeImage(4, $pImage, 'thumb_', 960, 760, $module);
							$image 	= '<img src="' . $pImage . '" alt="'.$product->product_name.'" title="'.$product->product_name.'" />';
							echo JHTML::_( 'link', JRoute::_( 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id ), $image, array('title' => $product->product_name) );?>
						</div>
					</div> 
					<div class="text-block">
						<?php
						$url = JRoute::_( 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.
						$product->virtuemart_category_id ); ?>
						<h3 class="product-title">
							<a href="<?php echo $url ?>"><?php echo $product->product_name ?></a>
						</h3>
						
						<?php // $product->prices is not set when show_prices in config is unchecked
					
			
						echo '<div class="productdetails">';
						if($show_price and isset($product->prices)) {
							echo  '<div class="product-price">';
							
							// 		echo $currency->priceDisplay($product->prices['salesPrice']);
							if(!empty($product->prices['salesPrice'])) echo $currency->createPriceDiv( 'salesPrice', '', $product->prices, true );
							// 		if ($product->prices['salesPriceWithDiscount']>0) echo $currency->priceDisplay($product->prices['salesPriceWithDiscount']);
							if(!empty($product->prices['salesPriceWithDiscount'])) echo $currency->createPriceDiv( 'salesPriceWithDiscount', '', $product->prices, true );
							echo '</div>';
						}
						echo '</div>';
						
						
						if($show_addtocart) 
							echo shopFunctionsF::renderVmSubLayout( 'addtocart', array('product' => $product) );
						?>
					</div>
				</div>
			</div>
		<?php } ?>
		<?php if($footerText) { ?>
			<div class="vmheader"><?php echo $footerText ?></div>
		<?php } ?>
	</div>
</div>