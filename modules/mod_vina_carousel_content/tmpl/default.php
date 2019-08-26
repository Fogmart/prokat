<?php
/*
# ------------------------------------------------------------------------
# Vina Articles Carousel for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2016 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum: http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();
if(!defined('_OWL_CAROUSEL')) {
	$doc->addScript('modules/' . $module->module . '/assets/js/owl.carousel.min.js', 'text/javascript');
	$doc->addStyleSheet('modules/' . $module->module . '/assets/css/owl.carousel.min.css');
	$doc->addStyleSheet('modules/' . $module->module . '/assets/css/owl.theme.default.min.css');
	define('_OWL_CAROUSEL', 1);
}

// Add styles
$styleModule 	 = '';
$styleItem  	 = '';
$styleItemLink   = '';

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
	$styleItem 		.= ($itemTextColor) ? "color: {$itemTextColor}" : '';
	$styleItemLink 	.= ($itemLinkColor) ? "color: {$itemLinkColor}" : '';
}

$style = 
	'#vina-carousel-content'. $module->id .'{'
		. 'overflow: hidden;'
		. '-webkit-box-sizing: border-box;'
		. '-moz-box-sizing: border-box;' 
		. 'box-sizing: border-box;'
		. $styleModule .
    '}' .
	'#vina-carousel-content'.$module->id . ' .item {'
		. $styleItem .
	'}' .
	'#vina-carousel-content' . $module->id . ' .item a {'
		. $styleItemLink .
	'}'; 
$doc->addStyleDeclaration($style);
?>


<div id="vina-carousel-content<?php echo $module->id; ?>" class="vina-carousel-content owl-carousel owl-theme <?php echo $classSuffix; ?>">
	<!-- Items Block -->
	<?php 
		$col 	= 1;
		$nb 	= 1;
		foreach ($list as $item) :
			$nb ++;
			$title 		= $item->title;
			$link   	= $item->link;
			$images 	= json_decode($item->images);
			$image = '';
			if($images ) {
				$image  	= (!is_null($images->image_fulltext)) ? $images->image_fulltext : (!is_null($images->image_intro) ? $images->image_intro : "");
			}

			$category 	= $item->displayCategoryTitle;
			$hits  		= $item->displayHits;
			$introtext 	= $item->displayIntrotext;
			$created   	= $item->displayDate;
			
			if($resizeImage) {
				$image = ModVinaArticlesCarouselHelper::resizeImage($resizeType, $image, 'thumb_', $imageWidth, $imageHeight, $module);		
			}
	?>
	<?php if($col == 1) :?>
	<div class="items">
	<?php endif; ?>
		<div class="item">
			<!-- Image Block -->
			<?php if($showImage && ($image)) : ?>
			<div class="image-block">
				<a href="<?php echo $link; ?>" title="<?php echo $title; ?>">
					<img src="<?php echo $image; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>"/>
				</a>
			</div>
			<?php endif; ?>
			
			<!-- Text Block -->
			<?php if($showTitle || $introText || $showCategory || $showCreatedDate || $showHits || $readmore) : ?>
			<div class="text-block">
				<!-- Title Block -->
				<?php if($showTitle) :?>
				<h3 class="title">
					<a href="<?php echo $link; ?>" title="<?php echo $title; ?>"><?php echo $title; ?></a>
				</h3>
				<?php endif; ?>
				
				<!-- Info Block -->
				<?php if($showCategory || $showCreatedDate || $showHits) : ?>
				<div class="info">
					<?php if($showCreatedDate) : ?>
					<span><?php echo JTEXT::_('VINA_PUBLISHED'); ?>: <?php echo JHTML::_('date', $created, 'F d, Y');?></span>
					<?php endif; ?>
					
					<?php if($showCategory) : ?>
					<span><?php echo JTEXT::_('VINA_CATEGORY'); ?>: <?php echo $category; ?></span>
					<?php endif; ?>
					
					<?php if($showHits) : ?>
					<span><?php echo JTEXT::_('VINA_HITS'); ?>: <?php echo $hits; ?></span>
					<?php endif; ?>
				</div>
				<?php endif; ?>
				
				<!-- Intro text Block -->
				<?php if($introText) : ?>
				<div class="introtext"><?php echo htmlentities($introtext); ?></div>
				<?php endif; ?>
				
				<!-- Readmore Block -->
				<?php if($readmore) : ?>
				<div class="readmore">
					<a class="buttonlight morebutton" href="<?php echo $link; ?>" title="<?php echo $title; ?>">
						<?php echo JText::_('VINA_READ_MORE'); ?>
					</a>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	<?php 
	if($col == $itemInCol || $nb > count($list)) { 
		$col = 1;
	echo '</div>';
	}else {
		$col ++;
	}	
	endforeach; ?>
</div>
<?php
//Javascript Block
$js =  '
	jQuery(document).ready(function($) {
		$("#vina-carousel-content' . $module->id . '").owlCarousel({'
			
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
			.'navText: 				[ "prev", "next" ],'
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