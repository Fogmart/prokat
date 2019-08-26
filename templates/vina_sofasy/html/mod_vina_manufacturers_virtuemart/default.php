<?php
/*
# ------------------------------------------------------------------------
# Vina Manufacturers Carousel for VirtueMart for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2016 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum:    http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/
defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();
if(!defined('_OWL_CAROUSEL')) {
	//$doc->addScript('modules/' . $module->module . '/assets/js/owl.carousel.min.js', 'text/javascript');
	//$doc->addStyleSheet('modules/' . $module->module . '/assets/css/owl.carousel.min.css');
	//$doc->addStyleSheet('modules/' . $module->module . '/assets/css/owl.theme.default.min.css');
	define('_OWL_CAROUSEL', 1);
}

// Add styles
$styleModule 			= '';
$styleItem 				= '';
$stylecaptionBgColor 	= '';
$stylecaptionColor 		= '';

if($moduleConfig) {
	$styleModule .= 'width:' . $moduleWidth . ';';
	$styleModule .= 'height:' . $moduleHeight . ';';
	$styleModule .= 'margin:' . $moduleMargin. ';';
	$styleModule .= 'padding:' . $modulePadding . ';';
	$styleModule .= ($bgImage != '') ? "background-image: url({$bgImage});" : '';
	$styleModule .= ($isBgColor) ? "background-color: {$bgColor};" : '';
}
if($itemConfig) {
	$styleItem 		.= ($isItemBgColor) ? "background-color: {$itemBgColor};" : "";
	$styleItem 		.= 'margin:' . $itemMargin . ';';
	$styleItem 		.= 'padding:' .$itemPadding .';';
	$stylecaptionBgColor 	= ($captionBgColor) ? "background-color: {$captionBgColor};" : "";
	$stylecaptionColor 		= ($captionColor) ? "color: {$captionColor};" : "";
}

$style = 
	'#vina-manufacturers-virtuemart'. $module->id .'{'
		. 'overflow: initial;'
		. '-webkit-box-sizing: border-box;'
		. '-moz-box-sizing: border-box;' 
		. 'box-sizing: border-box;'
		. $styleModule .
    '}' .
	'#vina-manufacturers-virtuemart'. $module->id . ' .item {'
		. 'overflow: hidden;' 
		. $styleItem .
	'}' .
	'#vina-manufacturers-virtuemart'. $module->id . ' .item .vina-caption {'
		. $stylecaptionBgColor
	. '}'
	. '#vina-manufacturers-virtuemart'. $module->id . ' .item .vina-caption,'
	. '#vina-manufacturers-virtuemart'. $module->id . ' .item .vina-caption a {'
		. $stylecaptionColor
	. '}';
$doc->addStyleDeclaration($style);

?>
<div id="vina-manufacturers-virtuemart<?php echo $module->id; ?>" class="vina-manufacturers-virtuemart owl-carousel owl-theme <?php echo $classSuffix; ?>">
	<?php
		$col 	= 1;
		$nb 	= 1;
		foreach($manufacturers as $manufacturer) : 
			$nb ++;
			$mid   = $manufacturer->virtuemart_manufacturer_id;
			$mlink = JROUTE::_('index.php?option=com_virtuemart&view=manufacturer&virtuemart_manufacturer_id=' . $mid);
			$mname = $manufacturer->mf_name;
			$mlogo = (!empty($manufacturer->images[0])) ? $manufacturer->images[0]->file_url : '';
			
			if($resizeImage) {
				$mlogo = modVinaManufacturersVirtueMartHelper::resizeImage($resizeType, $mlogo, 'thumb_', $imageWidth, $imageHeight, $module);		
			}
	?>
	<?php if($col == 1) :?>
	<div class="items">
	<?php endif; ?>
		<div class="item">
			<!-- Image Block -->
			<?php if($showImage && $mlogo): ?>
				<div class="image-block">
					<?php if($linkOnImage): ?>
						<a href="<?php echo $mlink; ?>" title="<?php echo $mname; ?>">
							<img src="<?php echo $mlogo; ?>" alt="<?php echo $mname; ?>" />
						</a>
					<?php else: ?>
						<img src="<?php echo $mlogo; ?>" alt="<?php echo $mname; ?>" />
					<?php endif; ?>
				</div>
			<?php endif; ?>
			
			<!-- Caption Block -->
			<?php if($showName): ?>
			<div class="vina-caption">
				<?php if($linkOnName): ?>
					<a href="<?php echo $mlink; ?>" title="<?php echo $mname; ?>"><?php echo $mname; ?></a>
				<?php else: ?>
					<?php echo $mname; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	<?php 
	if($col == $itemInCol || $nb > count($manufacturers)) { 
		$col = 1;
	echo '</div>';
	}else {
		$col ++;
	}
	endforeach; ?>
</div>
<?php
//Javascript Block
$prev = ($doc->direction == 'rtl') ? "<i class='fa fa-angle-right'></i>" : "<i class='fa fa-angle-left'></i>";
$next = ($doc->direction == 'rtl') ? "<i class='fa fa-angle-left'></i>" : "<i class='fa fa-angle-right'></i>";
$js =  '
	jQuery(document).ready(function($) {
		$("#vina-manufacturers-virtuemart' . $module->id . '").owlCarousel({'
			
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
			.'navText: 				[ "'.$prev.'", "'.$next.'" ],'
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
$doc->addScriptdeclaration($js);