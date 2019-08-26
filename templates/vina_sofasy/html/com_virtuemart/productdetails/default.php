<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Eugen Stranz, Max Galt
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default.php 9292 2016-09-19 08:07:15Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//Chung Pham
//Include Helix3 plugin
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';

if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $helix3 = helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}

/* Parameter */
$vm_social_product 		= $helix3->getParam('vm_social_product', 1);
$vm_social_product_code = $helix3->getParam('vm_social_product_code');
$vm_richSnippets		= $helix3->getParam('vm_richSnippets');
//End Chung Pham

/* Let's see if we found the product */
if (empty($this->product)) {
	echo vmText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND');
	echo '<br /><br />  ' . $this->continue_link_html;
	return;
}
echo shopFunctionsF::renderVmSubLayout('askrecomjs',array('product'=>$this->product));

if(vRequest::getInt('print',false)){ ?>
<body onload="javascript:print();">
<?php } ?>

<div class="product-container productdetails-view productdetails" >
	<?php

	// event onContentBeforeDisplay
	echo $this->product->event->beforeDisplayContent; ?>
	<div class="vm-product-container row">
		<div class="col-md-5">
			<div class="vm-product-media-container">
			<?php
				echo $this->loadTemplate('images');
			?>
			<?php
			$count_images = count ($this->product->images);
			if ($count_images > 1) {
				echo $this->loadTemplate('images_additional');
			}
			?>
			</div>
		</div>
		<div class="col-md-7">
			<div class="vm-product-details-container">
				<?php
				// Product Navigation
				if (VmConfig::get('product_navigation', 1)) { ?>
					<div class="product-neighbours"> <?php
						if (!empty($this->product->neighbours ['previous'][0])) {
						$prev_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['previous'][0] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id, FALSE);
							echo JHtml::_('link', $prev_link, $this->product->neighbours ['previous'][0]['product_name'], array('rel'=>'prev', 'class' => 'previous-page','data-dynamic-update' => '1'));
						}
						if (!empty($this->product->neighbours ['next'][0])) {
							$next_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['next'][0] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id, FALSE);
							echo JHtml::_('link', $next_link, $this->product->neighbours ['next'][0] ['product_name'], array('rel'=>'next','class' => 'next-page','data-dynamic-update' => '1'));
						} ?>
						<div class="clear"></div>
					</div> <?php 
				} // Product Navigation END?>
				
				<?php // Back To Category Button
				if ($this->product->virtuemart_category_id) {
					$catURL =  JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$this->product->virtuemart_category_id, FALSE);
					$categoryName = vmText::_($this->product->category_name) ;
				} else {
					$catURL =  JRoute::_('index.php?option=com_virtuemart');
					$categoryName = vmText::_('COM_VIRTUEMART_SHOP_HOME') ;
				} ?>
				<!--<div class="back-to-category">
					<a href="<?php echo $catURL ?>" class="product-details" title="<?php echo $categoryName ?>"><?php echo vmText::sprintf('COM_VIRTUEMART_CATEGORY_BACK_TO',$categoryName) ?></a>
				</div>-->
				
				<?php // Product Title   ?>
				<h1 itemprop="name"><?php echo $this->product->product_name ?></h1>
				<?php // afterDisplayTitle Event
				echo $this->product->event->afterDisplayTitle ?>
				
				<?php // Product Edit Link
				echo $this->edit_link;?>
				<?php
				// PDF - Print - Email printicon
				if (VmConfig::get('show_emailfriend') || VmConfig::get('show_printicon') || VmConfig::get('pdf_icon')) {?>
				<div class="icons">
					<?php
					$link = 'index.php?tmpl=component&option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->virtuemart_product_id;

					$pdf_icon = $this->linkIcon($link . '&format=pdf', 'COM_VIRTUEMART_PDF', 'pdf_button', 'pdf_icon', false);
					//echo $this->linkIcon($link . '&print=1', 'COM_VIRTUEMART_PRINT', 'printButton', 'show_printicon');
					
					$show_printicon = $this->linkIcon($link . '&print=1', 'COM_VIRTUEMART_PRINT', 'printButton', 'show_printicon',false,true,false,'class="printModal"');
					?>
					<div class="btn-group">
						<a href="#" class="btn-toggle dropdown-toggle btn btn-default" data-toggle="dropdown" aria-expanded="false">
							<span class="icon-cog"></span>
							<span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<?php  if (VmConfig::get('pdf_icon')) { ?>
							<li>
								<?php echo $pdf_icon; ?>
							</li>
							<?php } ?>
							<?php  if (VmConfig::get('show_printicon')) { ?>
								<li>
									<?php echo $show_printicon; ?>
								</li>
							<?php } ?>
						</ul>
					</div>
					<div class="clear"></div>
				</div>
				<?php } // PDF - Print - Email Icon END?>
				
				<div class="product-rating">
				<?php //rating
					echo shopFunctionsF::renderVmSubLayout('rating',array('showRating'=>$this->showRating,'product'=>$this->product)); ?>
					<?php if(VmConfig::get('showReviewFor', 'none') != 'none') { ?>
						<span class="separator">|</span>
						<span class="add_review"><a href="javascript:void(0)" class="to_review"><?php echo JText::_('VM_LANG_ADD_YOUR_REVIEW'); ?></a></span>
					<?php } ?>
				</div>
				<?php //customfields ontop
					echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'ontop'));
				?>
				<?php // Stock ?>				
					<p class="in-stock">
						<?php echo "<span>".vmText::_('COM_VIRTUEMART_AVAILABILITY')."</span>: "; ?>
						
						<?php if($this->product->product_in_stock > 0) {
							echo vmText::_('COM_VIRTUEMART_PRODUCT_FORM_IN_STOCK');
						}
						else {
							echo vmText::_('COM_VIRTUEMART_STOCK_LEVEL_OUT');
						}?>	
					</p>
				<?php
				
					/*if (is_array($this->productDisplayShipments)) {
						foreach ($this->productDisplayShipments as $productDisplayShipment) {
						echo $productDisplayShipment . '<br />';
						}
					}
					if (is_array($this->productDisplayPayments)) {
						foreach ($this->productDisplayPayments as $productDisplayPayment) {
						echo $productDisplayPayment . '<br />';
						}
					}*/
				
					//In case you are not happy using everywhere the same price display fromat, just create your own layout
					//in override /html/fields and use as first parameter the name of your file
					echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$this->product,'currency'=>$this->currency));
					?>
				
				<?php
					// Product Short Description
					if (!empty($this->product->product_s_desc)) {
					?>
						<div class="product-short-description">
						<?php
						/** @todo Test if content plugins modify the product description */
						echo nl2br($this->product->product_s_desc);
						?>
						</div>
					<?php
					} // Product Short Description END?>
				<div class="spacer-buy-area">
					<?php
					// TODO in Multi-Vendor not needed at the moment and just would lead to confusion
					/* $link = JRoute::_('index2.php?option=com_virtuemart&view=virtuemart&task=vendorinfo&virtuemart_vendor_id='.$this->product->virtuemart_vendor_id);
					  $text = vmText::_('COM_VIRTUEMART_VENDOR_FORM_INFO_LBL');
					  echo '<span class="bold">'. vmText::_('COM_VIRTUEMART_PRODUCT_DETAILS_VENDOR_LBL'). '</span>'; ?><a class="modal" href="<?php echo $link ?>"><?php echo $text ?></a><br />
					 */
					?>
					<?php
						echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'normal'));
						echo shopFunctionsF::renderVmSubLayout('stockhandle',array('product'=>$this->product));
						echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$this->product)); ?>
					<div class="button-groups">
						
						<!-- View Details wishlist -->
						<?php if(is_dir(JPATH_BASE."/components/com_wishlist/")) :
							$app = JFactory::getApplication();
							$product = '';
						?>
							<div class="button-group btn-wishlist">							
								<?php require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); ?>													
							</div>
						<?php endif; ?>
						
						<?php   //show_emailfriend
						if (VmConfig::get('show_emailfriend')) { 
							$MailLink = 'index.php?option=com_virtuemart&view=productdetails&task=recommend&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component';
						
							//$show_emailfriend = $this->linkIcon($MailLink, 'COM_VIRTUEMART_EMAIL', 'emailButton', 'show_emailfriend', false,true,false,'class="recommened-to-friend"');
							$show_emailfriend = $this->linkIcon($MailLink, 'VM_VIRTUEMART_EMAIL', 'emailButton', 'show_emailfriend', false,false,false,'class="recommened-to-friend zmdi zmdi-email"');
						?>
							<!--<div class="button-group btn-email">
								<?php echo $show_emailfriend; ?>
							</div>-->
						<?php } ?>
					</div>
					
				</div>
				<?php // Product Packaging
				$product_packaging = '';
				
				if ($this->product->product_box) { ?>
					<!--<div class="product-box">
					<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_UNITS_IN_BOX') .$this->product->product_box; ?>
					</div>-->
				<?php } // Product Packaging END ?>
				
				<?php // Manufacturer of the Product
				if (VmConfig::get('show_manufacturers', 1) && !empty($this->product->virtuemart_manufacturer_id)) {
					echo $this->loadTemplate('manufacturer');
				}
				?>
				
				<?php // Show child categories
				if (VmConfig::get('showCategory', 1)) {
					echo $this->loadTemplate('showcategory');
				} ?>
	
				<?php if($vm_social_product) : ?>
				<!-- Social Button -->																	
				<div class="link-share">
					<span class="pull-left" >Share this: </span>
					<div class="pull-left">
					<?php if ($vm_social_product_code):
						echo $vm_social_product_code;
					else:?>
						<!-- AddThis Button BEGIN -->
						<div class="addthis_inline_share_toolbox"></div>
				
						<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-58b3ea985e51405a"></script>
						<!-- AddThis Button END --> 
					<?php endif;?>
					</div>
				</div>	
				<!-- End Social Button -->
				<?php endif; ?>
				<?php // Ask a question about this product
			if (VmConfig::get('ask_question', 0) == 1) {
				$askquestion_url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&task=askquestion&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component', FALSE);
				?>
				<div class="ask-a-question">
					<a class="ask-a-question" href="<?php echo $askquestion_url ?>" rel="nofollow" >
						<i class="fa fa-question" aria-hidden="true"></i>
						<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL') ?>
					</a>
				</div>
			<?php
			}
			?>
				
			</div>
		</div>
	</div>
	<!-- Tabs Full Description + Review + comment -->
	<div id="tab-block" role="tabpanel">
		<!-- Nav tabs -->
		<ul id="tabs-detail-product" class="nav nav-tabs" role="tablist">
			
			<!-- Product Description -->
			<?php if (!empty($this->product->product_desc)) : ?>
			<li role="presentation" class="tab_des active">
				<a href="#vina-description" aria-controls="vina-description" role="tab" data-toggle="tab">
					<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_DESC_TITLE'); ?>
				</a>
			</li>
			<?php endif; ?>
			
			<!-- reviews -->
			<li role="presentation" class="tab_review">
				<a href="#vina-reviews" aria-controls="vina-reviews" role="tab" data-toggle="tab">
					<?php echo JText::_('COM_VIRTUEMART_REVIEWS'); ?>
				</a>
			</li>		
		</ul>

		<!-- Tab panes -->
		<div id="vinaTabContent" class="tab-content">
		
			<!-- Product Description -->
			<?php if (!empty($this->product->product_desc)) : ?>
			<div role="tabpanel" class="tab-pane active" id="vina-description">
				<?php echo $this->product->product_desc; ?>
			</div>
			<?php endif; ?>
			
			<!-- reviews -->
			<div role="tabpanel" class="tab-pane" id="vina-reviews">
				<?php echo $this->loadTemplate('reviews'); ?>
			</div>			
		</div>
	</div>
	
    <?php 
	echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'onbot'));

	echo shopFunctionsF::renderVmSubLayout('customfields_product',array('product'=>$this->product,'position'=>'related_products','class'=> 'product-related-products','customTitle' => true ));

	echo shopFunctionsF::renderVmSubLayout('customfields_category',array('product'=>$this->product,'position'=>'related_categories','class'=> 'product-related-categories'));

	?>

	<?php // onContentAfterDisplay event
	echo $this->product->event->afterDisplayContent;


	$j = 'jQuery(document).ready(function($) {
		$("form.js-recalculate").each(function(){
			if ($(this).find(".product-fields").length && !$(this).find(".no-vm-bind").length) {
				var id= $(this).find(\'input[name="virtuemart_product_id[]"]\').val();
				Virtuemart.setproducttype($(this),id);

			}
		});
	});';
	//vmJsApi::addJScript('recalcReady',$j);

	if(VmConfig::get ('jdynupdate', TRUE)){

		/** GALT
		 * Notice for Template Developers!
		 * Templates must set a Virtuemart.container variable as it takes part in
		 * dynamic content update.
		 * This variable points to a topmost element that holds other content.
		 */
		$j = "Virtuemart.container = jQuery('.productdetails-view');
		Virtuemart.containerSelector = '.productdetails-view';
		//Virtuemart.recalculate = true;	//Activate this line to recalculate your product after ajax
		";

		vmJsApi::addJScript('ajaxContent',$j);

		$j = "jQuery(document).ready(function($) {
			Virtuemart.stopVmLoading();
			var msg = '';
			$('a[data-dynamic-update=\"1\"]').off('click', Virtuemart.startVmLoading).on('click', {msg:msg}, Virtuemart.startVmLoading);
			$('[data-dynamic-update=\"1\"]').off('change', Virtuemart.startVmLoading).on('change', {msg:msg}, Virtuemart.startVmLoading);
		});";

		vmJsApi::addJScript('vmPreloader',$j);
	}

	echo vmJsApi::writeJS();

	if ($this->product->prices['salesPrice'] > 0) {
	  echo shopFunctionsF::renderVmSubLayout('snippets',array('product'=>$this->product, 'currency'=>$this->currency, 'showRating'=>$this->showRating));
	}
	
	//Chung Pham
	if($vm_richSnippets ){ 
	
		$config = JFactory::getConfig();
		$categoryItemName ='';
		$currencieName = '';
		foreach ( $this->product->categoryItem as $categoryItem ) {
			$categoryItemName .= $categoryItem['category_name'] . ', ';
		} 
		$currencyModel = VmModel::getModel('currency');
		$vendorId = vRequest::getInt('vendorid', 1);
		$currencies = $currencyModel->getVendorAcceptedCurrrenciesList($vendorId);
		//$rating = $this->product->rating ? $this->product->rating : 0;
		foreach ( $currencies as $currencie ) {
			$currencieName .= $currencie->currency_txt . ', ';
		} 
		?>
		
		<script type="application/ld+json">
		{
			"@context": "http://schema.org/",
			"@type": "Product",
			"name": "<?php echo $this->product->product_name; ?>",
			"image": "<?php echo JURI::base() . $this->product->file_url; ?>",
			"description": "<?php echo strip_tags(nl2br($this->product->product_s_desc));?>",
			"mpn": "<?php echo $this->product->id; ?>",
			"brand": {
				"@type": "Thing",
				"name": "<?php echo $categoryItemName; ?>"
			},
			<?php if($this->rating_reviews && $this->product->rating ) :?>
			"aggregateRating": {
				"@type": "AggregateRating",
				"ratingValue": "<?php echo $this->product->rating; ?>",
				"reviewCount": "<?php echo count($this->rating_reviews); ?>"
			},
			<?php endif; ?>
			"offers": {
				"@type": "Offer",
				"priceCurrency": "<?php echo $currencieName; ?>",
				"price": "<?php echo $this->product->prices['salesPrice']; ?>",
				"priceValidUntil": "2020-14-04",
				"itemCondition": "http://schema.org/UsedCondition",
				"availability": "http://schema.org/InStock",
				"seller": {
					"@type": "Organization",
					"name": "<?php echo $config['sitename'];?>"
				}
			}
		}
		</script> <?php 
	}
	$app= JFactory::getApplication();	
	$doc = JFactory::getDocument();
	
	if(VmConfig::get('showReviewFor', 'none') != 'none') {					
		$doc->addScriptDeclaration("
			jQuery(function($) {							
				$('.to_review, .count_review').click(function() {
					$('html, body').animate({
						scrollTop: ($('#tab-block').offset().top - 120)
					},500);									
					$('#tabs-detail-product li').removeClass('active');
					$('#tabs-detail-product li.tab_review').addClass('active');
					$('#vinaTabContent >div').removeClass('active');
					$('#vinaTabContent #vina-reviews').addClass('active');
				});
			})
		");
	}
//end Chung Pham	
?>
</div>



