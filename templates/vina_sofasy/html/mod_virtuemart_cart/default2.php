<?php // no direct access
defined('_JEXEC') or die('Restricted access');

vmJsApi::removeJScript("/modules/mod_virtuemart_cart/assets/js/update_cart.js");

//dump ($cart,'mod cart');
// Ajax is displayed in vm_cart_products
// ALL THE DISPLAY IS Done by Ajax using "hiddencontainer" ?>

<!-- Virtuemart 2 Ajax Card -->
<div class="vmCartModule <?php echo $params->get('moduleclass_sfx'); ?>" id="vmCartModule<?php echo $params->get('moduleid_sfx'); ?>">
	<?php if ($show_product_list) { ?>
		<div class="sp-module vina-dropdown">			                       					
			<div class="sp-module-title">
				<h3 class="modtitle shopping_cart">
					<span class="iconcart"><i class="zmdi zmdi-shopping-basket"></i></span>
					<span class="mini-title"><?php echo JText::_('VINA_SHOPPINGCART')?></span>
					<span class="number"><?php echo  $data->totalProduct; ?></span>
					<span class="cart-item"><?php echo JText::_('VINA_ITEM')?></span>
					<span class="cart-price"><?php echo ($data->billTotal_net); ?></span>
				</h3>
			</div>
			<div class="sp-module-content">
				<div id="hiddencontainer" class="hiddencontainer" style=" display: none; ">
					<div class="vmcontainer">
						<div class="product_row">
							<span class="quantity"></span>&nbsp;x&nbsp;<span class="product_name"></span>
							<?php if ($show_price and $currencyDisplay->_priceConfig['salesPrice'][0]) { ?>
								<div class="subtotal_with_tax" style="float: right;"></div>
							<?php } ?>
							<div class="customProductData"></div><br>
						</div>
					</div>
				</div>
				<div class="vm_cart_products">
					<div class="vmcontainer">
						<?php if(empty($data->products)) { ?>
							<p class="empty"><?php echo JText::_('COM_VIRTUEMART_EMPTY_CART')?></p>
						<?php } else { ?>
							<?php foreach ($data->products as $product){ ?>
								<div class="product_row media">
									<span class="quantity"><?php echo  $product['quantity'] ?></span>&nbsp;x&nbsp;
									<span class="product_name"><?php echo  $product['product_name'] ?></span>
									<?php if ($show_price and $currencyDisplay->_priceConfig['salesPrice'][0]) { ?>
										<div class="subtotal_with_tax" style="display: inline-block;font-weight: 700;"><?php echo $product['subtotal_with_tax'] ?></div>
									<?php } ?>
									<?php if ( !empty($product['customProductData']) ) { ?>
										<div class="customProductData"><?php echo $product['customProductData'] ?></div>
									<?php } ?>
								</div>
							<?php } ?>
						<?php } ?>
					</div>
					<div class="vm_cart_bottom">
						<?php
						if(vRequest::getCmd('view')!='cart'){
								?><div class="payments-signin-button" ></div><?php
							}
						?>
					</div>
				</div>	
				<?php if ($data->totalProduct) { ?>
				<div class="vm_cart_footer">
					<div class="show_cart">
						<?php echo  $data->cart_show; ?>
					</div>
				</div>
				<?php } ?>
			</div>
						
		</div>
	<?php } ?>
	<noscript>
		<?php echo vmText::_('MOD_VIRTUEMART_CART_AJAX_CART_PLZ_JAVASCRIPT') ?>
	</noscript>
	
	<script>
		if (typeof Virtuemart === "undefined")
		Virtuemart = {};
		
		jQuery(function($) {
			Virtuemart.customUpdateVirtueMartCartModule = function(el, options){
				var base 	= this;
				var $this	= $(this);
				base.$el 	= $(".vmCartModule");

				base.options 	= $.extend({}, Virtuemart.customUpdateVirtueMartCartModule.defaults, options);
					
				base.init = function(){
					$.ajaxSetup({ cache: false })
					$.getJSON(window.vmSiteurl + "index.php?option=com_virtuemart&nosef=1&view=cart&task=viewJS&format=json" + window.vmLang,
						function (datas, textStatus) {
							base.$el.each(function( index ,  module ) {
								if (datas.totalProduct > 0) {
									$(module).find(".vm_cart_products").html("");
									$.each(datas.products, function (key, val) {
										//jQuery("#hiddencontainer .vmcontainer").clone().appendTo(".vmcontainer .vm_cart_products");
										$(module).find(".hiddencontainer .vmcontainer .product_row").clone().appendTo( $(module).find(".vm_cart_products") );
										$.each(val, function (key, val) {
											$(module).find(".vm_cart_products ." + key).last().html(val);
										});
									});
								}
								$(module).find(".show_cart").html(datas.cart_show);
								//$(module).find(".total_products").html(	datas.totalProductTxt);
								$(module).find(".number").html(datas.totalProduct);
								$(module).find(".cart-price").html(datas.billTotal_net);
							});
						}
					);			
				};
				base.init();
			};
			// Definition Of Defaults
			Virtuemart.customUpdateVirtueMartCartModule.defaults = {
				name1: 'value1'
			};

		});

		jQuery(document).ready(function( $ ) {
			jQuery(document).off("updateVirtueMartCartModule","body",Virtuemart.customUpdateVirtueMartCartModule);
			jQuery(document).on("updateVirtueMartCartModule","body",Virtuemart.customUpdateVirtueMartCartModule);
		});
	</script>
</div>
