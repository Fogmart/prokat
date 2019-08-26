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
$product = $viewData['product'];
$position = $viewData['position'];
$customTitle = isset($viewData['customTitle'])? $viewData['customTitle']: false;;
if(isset($viewData['class'])){
	$class = $viewData['class'];
} else {
	$class = 'product-fields';
}

if (!empty($product->customfieldsSorted[$position])) {
	?>
	<div class="sp-module <?php echo $class?> style-title1">
		
		<?php
		if($customTitle and isset($product->customfieldsSorted[$position][0])){
			$field = $product->customfieldsSorted[$position][0]; ?>
		<div class="sppb-section-title">
			<h3 class="modtitle">
				<?php echo vmText::_ ($field->custom_title) ?>
				<?php if ($field->custom_tip) {
					echo JHtml::tooltip (vmText::_($field->custom_tip), vmText::_ ($field->custom_title), 'tooltip.png');
				} ?>
			</h3>
		</div>
		<?php
		}
		$custom_title = null; ?>
		<div class="<?php echo $class; ?>-carousel owl-carousel owl-theme">
			<?php
			foreach ($product->customfieldsSorted[$position] as $field) {
				if ( $field->is_hidden || empty($field->display)) continue; //OSP http://forum.virtuemart.net/index.php?topic=99320.0
				?>
				<div class="product">
					<div class="product-container product-field product-field-type-<?php echo $field->field_type ?>">
						<?php if (!$customTitle and $field->custom_title != $custom_title and $field->show_title) { ?>
							<!--<span class="product-fields-title-wrapper"><span class="product-fields-title"><strong><?php echo vmText::_ ($field->custom_title) ?></strong></span>
								<?php if ($field->custom_tip) {
									echo JHtml::tooltip (vmText::_($field->custom_tip), vmText::_ ($field->custom_title), 'tooltip.png');
								} ?></span> -->
						<?php }
						if (!empty($field->display)){
							?><div class="product-field-display"><?php echo $field->display ?></div><?php
						}
						
						?>
					</div> <?php
					//$custom_title = $field->custom_title; ?>
				</div>
				<?php 
			} ?>
		</div>
	</div>
<?php
}
$prev = ($doc->direction == 'rtl') ? "<i class='fa fa-angle-right'></i>" : "<i class='fa fa-angle-left'></i>";
$next = ($doc->direction == 'rtl') ? "<i class='fa fa-angle-left'></i>" : "<i class='fa fa-angle-right'></i>";
$js =  '
	jQuery(document).ready(function($) {
		$(".' . $class . '-carousel").owlCarousel({'
			.'items : 				4,'
			.'margin: 				30,'
			.'rtl: 					' . (($doc->direction == 'rtl') ? 'true' : 'false') . ','
			.'nav:					true,'
			.'navText: 				[ "'.$prev.'", "'.$next.'" ],'
			.'dots: 				false,'
			.'loop: 				true,'
			.'responsive:{'
				. '0:{'
					. 'items: 1,' // In this configuration 1 is enabled from 0px up to 479px screen size 
				.'},'
				.'480:{'
					.'items: 2,' // from 480 to 767 
				.'},'
				.'768:{'
					.'items: 2,' // from this breakpoint 768 to 991
				.'},'
				.'992:{'
					.'items: 2,' // from this breakpoint 980 to 1199
				.'},'
				.'1200:{'
					.'items: 3,'
				.'}'
			.'}'
		.'});'
	.'})
';
$doc->addScriptdeclaration($js);