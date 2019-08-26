<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen

 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_images.php 7784 2014-03-25 00:18:44Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>
<div id="product-additional-images" class="additional-images">
	<div class="owl-carousel owl-theme">
		<?php
		$start_image = VmConfig::get('add_img_main', 1) ? 0 : 1;
		for ($i = $start_image; $i < count($this->product->images); $i++) {
			$image = $this->product->images[$i];
			?>
			<div class="floatleft">
				<?php
				if(VmConfig::get('add_img_main', 1)) {
					echo $image->displayMediaThumb('class="product-image" style="cursor: pointer"',false,$image->file_description);
					echo '<a href="'. $image->file_url .'"  class="product-image image-'. $i .'" style="display:none;" title="'. $image->file_meta .'" rel="vm-additional-images"></a>';
				} else {
					echo $image->displayMediaThumb("",true,"rel='vm-additional-images'",true,$image->file_description);
				}
				?>
			</div>
		<?php
		}
		?>
	</div>
</div>
<?php
$doc = JFactory::getDocument();
$prev = ($doc->direction == 'rtl') ? "<i class='fa fa-angle-right'></i>" : "<i class='fa fa-angle-left'></i>";
$next = ($doc->direction == 'rtl') ? "<i class='fa fa-angle-left'></i>" : "<i class='fa fa-angle-right'></i>";
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$("#product-additional-images .owl-carousel").owlCarousel({
		items : 	3,
		margin: 	20,
		loop: 		true,
		rtl: 		false,
		nav:		true,
		navText: 	["<?php echo $prev; ?>", "<?php echo $next; ?>"],
		dots: 		false,
		responsive:	{
			0:		{items: 2},
			480:	{items: 3},
			768:	{items: 3},
			992:	{items: 3},
			1200:	{items: 3}
		}
	});
})
</script>