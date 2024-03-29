<?php
/*
# ------------------------------------------------------------------------
# Vina Articles Carousel for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum: http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once dirname(__FILE__) . '/helper.php';

$input 		= JFactory::getApplication()->input;
$idbase 	= $params->get('catid');
$cacheid 	= md5(serialize(array ($idbase, $module->module)));

$cacheparams = new stdClass;
$cacheparams->cachemode 	= 'id';
$cacheparams->class 		= 'ModVinaArticlesCarouselHelper';
$cacheparams->method 		= 'getList';
$cacheparams->methodparams 	= $params;
$cacheparams->modeparams 	= $cacheid;

$list = JModuleHelper::moduleCache($module, $params, $cacheparams);

if(empty($list))
{
	echo 'No item found! Please check your config!';
	return;
}

// get params
$classSuffix 	= $params->get('moduleclass_sfx', '');
$moduleConfig 	= $params->get('moduleConfig', 		1);
$moduleWidth	= $params->get('moduleWidth', 	'100%');
$moduleHeight	= $params->get('moduleHeight', 	'auto');
$moduleMargin	= $params->get('moduleMargin', 	'0px');
$modulePadding	= $params->get('modulePadding', '0px');
$bgImage		= $params->get('bgImage', 		null);
if($bgImage != '') {
	if(strpos($bgImage, 'http://') === FALSE) {
		$bgImage = JURI::base() . $bgImage;
	}
}
$isBgColor		= $params->get('isBgColor', 	1);
$bgColor		= $params->get('bgColor', 		'#CCCCCC');

$itemConfig 	= $params->get('itemConfig', 	1);
$itemMargin		= $params->get('itemMargin', 	'15px 0px');
$itemPadding	= $params->get('itemPadding', 	'10px');
$isItemBgColor	= $params->get('isItemBgColor', 1);
$itemBgColor	= $params->get('itemBgColor', 	'#FFFFFF');
$itemTextColor	= $params->get('itemTextColor', null);
$itemLinkColor	= $params->get('itemLinkColor', null);

// display params
$showImage			= $params->get('showImage', 	1);
$resizeImage		= $params->get('resizeImage', 	1);
$resizeType			= $params->get('resizeType',	1);
$imageWidth			= $params->get('imageWidth', 	100);
$imageHeight		= $params->get('imageHeight', 	100);
$showTitle			= $params->get('showTitle', 	1);
$showCreatedDate	= $params->get('show_date', 	0);
$showCategory		= $params->get('show_category', 0);
$showHits			= $params->get('show_hits', 	0);
$introText			= $params->get('show_introtext',1);
$readmore			= $params->get('show_readmore', 1);

// Carousel Params
$items				= $params->get('items', 			4);
$itemInCol			= $params->get('itemInCol', 		1);
$responsive			= $params->get('responsive', 		1);
$itemsDesktop		= $params->get('itemsDesktop', 		4);
$itemsDesktopSmall	= $params->get('itemsDesktopSmall', 3);
$itemsTabletSmall	= $params->get('itemsTabletSmall', 	2);
$itemsTablet		= $params->get('itemsTablet', 		2);
$itemsMobile		= $params->get('itemsMobile', 		1);
$startPosition		= $params->get('startPosition', 	0);
$margin				= $params->get('margin', 			15);
$stagePadding		= $params->get('stagePadding', 		15);
$loop				= $params->get('loop', 				0);
$center				= $params->get('center', 			0);
$slideBy			= $params->get('slideBy', 			1);
$autoPlay			= $params->get('autoPlay', 			1);
$autoplayTimeout	= $params->get('autoplayTimeout', 	5000);
$autoplaySpeed		= $params->get('autoplaySpeed', 	800);
$stopOnHover		= $params->get('stopOnHover', 		1);
$navigation			= $params->get('navigation', 		1);
$navigationSpeed	= $params->get('navigationSpeed', 	800);
$rewindNav			= $params->get('rewindNav', 		1);
$pagination			= $params->get('pagination', 		0);
$paginationSpeed	= $params->get('paginationSpeed',   800);
$autoHeight			= $params->get('autoHeight', 		0);
$autoWidth			= $params->get('autoWidth', 		0);
$mouseDrag			= $params->get('mouseDrag', 		1);
$touchDrag			= $params->get('touchDrag', 		1);

// include layout
require JModuleHelper::getLayoutPath('mod_vina_carousel_content', $params->get('layout', 'default'));