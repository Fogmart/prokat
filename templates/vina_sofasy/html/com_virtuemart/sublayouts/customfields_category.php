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
	<div class="sp-module mod-title <?php echo $class?>">
		<?php
		if($customTitle and isset($product->customfieldsSorted[$position][0])){
			$field = $product->customfieldsSorted[$position][0]; ?>
		<div class="product-fields-title-wrapper"><span class="product-fields-title"><strong><?php echo vmText::_ ($field->custom_title) ?></strong></span>
			<?php if ($field->custom_tip) {
				echo JHtml::tooltip (vmText::_($field->custom_tip), vmText::_ ($field->custom_title), 'tooltip.png');
			} ?>
		</div> <?php
		}
		$custom_title = null; ?>
		<div class="<?php echo $class; ?>-carousel owl-carousel owl-theme">
			<?php
			foreach ($product->customfieldsSorted[$position] as $field) {
				if ( $field->is_hidden || empty($field->display)) continue; //OSP http://forum.virtuemart.net/index.php?topic=99320.0
				?>
				<div class="prouct">
					<div class="product-field product-field-type-<?php echo $field->field_type ?>">
						<?php if (!$customTitle and $field->custom_title != $custom_title and $field->show_title) { ?>
							<!--<span class="product-fields-title-wrapper"><span class="product-fields-title"><strong><?php echo vmText::_ ($field->custom_title) ?></strong></span>
								<?php if ($field->custom_tip) {
									echo JHtml::tooltip (vmText::_($field->custom_tip), vmText::_ ($field->custom_title), 'tooltip.png');
								} ?></span> -->
						<?php }
						if (!empty($field->display)){
							?><div class="product-field-display"><?php echo $field->display ?></div><?php
						}
						if (!empty($field->custom_desc)){
							?><!--<div class="product-field-desc"><?php echo vmText::_($field->custom_desc) ?></div>--> <?php
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
$js =  '
	jQuery(document).ready(function($) {
		$(".' . $class . '-carousel").owlCarousel({'
			.'items : 				4,'
			.'margin: 				5,'
			.'rtl: 					' . (($doc->direction == 'rtl') ? 'true' : 'false') . ','
			.'nav:					true,'
			.'navText: 				[ "prev", "next" ],'
			.'dots: 				false,'
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
					.'items: 3,' // from this breakpoint 980 to 1199
				.'},'
				.'1200:{'
					.'items: 4,'
				.'}'
			.'}'
		.'});'
	.'})
';
$doc->addScriptdeclaration($js);