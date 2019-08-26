<?php
/**
 *
 * Show the products in a category
 *
 * @package    VirtueMart
 * @subpackage
 * @author RolandD
 * @author Max Milbers
 * @todo add pagination
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default.php 9288 2016-09-12 15:20:56Z Milbo $
 */

defined ('_JEXEC') or die('Restricted access');
 
//Include Helix3 plugin
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';

if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $helix3 = helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}

$show_vmcategory_image 	= $helix3->getParam('show_vmcategory_image', 1);
$show_vmcategory_name 	= $helix3->getParam('show_vmcategory_name', 1);
$show_vmcategory_des 	= $helix3->getParam('show_vmcategory_des', 1);
$vm_view_mode 			= $helix3->getParam('vm_view_mode', 1);

if(vRequest::getInt('dynamic')){
	if (!empty($this->products)) {
		if($this->fallback){
			$p = $this->products;
			$this->products = array();
			$this->products[0] = $p;
			vmdebug('Refallback');
		}

		echo shopFunctionsF::renderVmSubLayout($this->productsLayout,array('products'=>$this->products,'currency'=>$this->currency,'products_per_row'=>$this->perRow,'showRating'=>$this->showRating));

	}

	return ;
}?>
 
<div class="category-view"> <?php
	$js = "
	jQuery(document).ready(function () {
		jQuery('.orderlistcontainer').hover(
			function() { jQuery(this).find('.orderlist').stop().show()},
			function() { jQuery(this).find('.orderlist').stop().hide()}
		)
	}); ";
	vmJsApi::addJScript('vm.hover',$js);

	if($this->category->category_name && $show_vmcategory_name): ?>
		<h1><?php echo vmText::_($this->category->category_name); ?></h1>
	<?php endif;
	
	if (empty($this->keyword) and !empty($this->category) and $this->category->category_description && $show_vmcategory_des ) { ?>
		<div class="category_description">
			<?php echo $this->category->category_description; ?>
		</div><?php
	}
	
	if( $show_vmcategory_image && empty($this->keyword) && $this->category->images[0]->file_url && $this->category->images[0]->file_title):  ?>
		<div class="cat_image effect-scale">
			<?php echo $this->category->images[0]->displayMediaFull("",false); ?>
		</div>
	<?php endif;
	
	// Show child categories
	if ($this->showcategory and empty($this->keyword)) {
		if (!empty($this->category->haschildren)) {
			echo ShopFunctionsF::renderVmSubLayout('categories',array('categories'=>$this->category->children));
		}
	}

	if($this->showproducts){?>
		<div class="browse-view">
		
			<?php

			if ($this->showsearch or !empty($this->keyword)) {
				//id taken in the view.html.php could be modified
				$category_id  = vRequest::getInt ('virtuemart_category_id', 0); ?>

				<!--BEGIN Search Box -->
				<div class="virtuemart_search">
					<form class="form-inline" action="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=category&limitstart=0', FALSE); ?>" method="get">
						<?php if(!empty($this->searchCustomList)) { ?>
						<div class="vm-search-custom-list">
							<?php echo $this->searchCustomList ?>
						</div>
						<?php } ?>

						<?php if(!empty($this->searchCustomValues)) { ?>
						<div class="vm-search-custom-values">
							<?php echo $this->searchCustomValues ?>
						</div>
						<?php } ?>
						<div class="vm-search-custom-search-input">
							<div class="form-group">
								<input name="keyword" class="inputbox" type="text" size="40" value="<?php echo $this->keyword ?>"/>
							</div>
							<div class="form-group">
								<input type="submit" value="<?php echo vmText::_ ('COM_VIRTUEMART_SEARCH') ?>" class="button btn btn-default" onclick="this.form.keyword.focus();"/>
							</div>
							<?php //echo VmHtml::checkbox ('searchAllCats', (int)$this->searchAllCats, 1, 0, 'class="changeSendForm"'); ?>
							<span class="vm-search-descr"> <?php echo vmText::_('COM_VM_SEARCH_DESC') ?></span>
						</div>

						<!-- input type="hidden" name="showsearch" value="true"/ -->
						<input type="hidden" name="view" value="category"/>
						<input type="hidden" name="option" value="com_virtuemart"/>
						<input type="hidden" name="virtuemart_category_id" value="<?php echo $category_id; ?>"/>
						<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>"/>
					</form>
				</div>
				<!-- End Search Box -->
				<?php
				/*if(!empty($this->keyword)){
					?><h3><?php echo vmText::sprintf('COM_VM_SEARCH_KEYWORD_FOR', $this->keyword); ?></h3><?php
				}*/
				$j = 'jQuery(document).ready(function() {

				jQuery(".changeSendForm")
					.off("change",Virtuemart.sendCurrForm)
					.on("change",Virtuemart.sendCurrForm);
				})';

				vmJsApi::addJScript('sendFormChange',$j);
			} ?>

			<?php // Show child categories

			if(!empty($this->orderByList)) { ?>
			<div class="orderby-displaynumber">
				
				<?php if($vm_view_mode): ?>
				<div class="view-mode pull-left">
					<div class="title hidden">Display</div>
					<div class="btn-group span2">
						<a href="javascript:viewMode('grid');" class="mode-grid btn btn-default <?php echo ($this->productsLayout == 'products') ? ' btn-primary' : ''; ?>" title="Grid">
							<span class="fa fa-th "></span>
						</a>
						<a href="javascript:viewMode('list');" class="mode-list btn btn-default	<?php echo ($this->productsLayout == 'products_horizon') ? 'btn-primary' : ''; ?>" title="List">
							<span class="fa fa-th-list fa-white "></span>
						</a>
					</div>
				</div>
				<?php endif; ?>
				
				<!--<div class="vm-pagination vm-pagination-top">
					<?php echo $this->vmPagination->getPagesLinks (); ?>
					<span class="vm-page-counter"><?php echo $this->vmPagination->getPagesCounter (); ?></span>
				</div>-->
				
				<div class="pull-right display-number">
					<?php //echo $this->vmPagination->getResultsCounter();?>
					
					<div class="title"><?php echo JText::_('View');?></div>
					<?php echo $this->vmPagination->getLimitBox ($this->category->limit_list_step); ?>
				</div>
				<div class="pull-left vm-order-list">
					<?php echo $this->orderByList['orderby']; ?>
					<?php echo $this->orderByList['manufacturer']; ?>
				</div>
				
				<div class="clear"></div>
			</div> <!-- end of orderby-displaynumber -->
			<?php } ?>
			
			<?php 
			if (!empty($this->products)) {
				
				//revert of the fallback in the view.html.php, will be removed vm3.2
				if($this->fallback){
					$p = $this->products;
					$this->products = array();
					$this->products[0] = $p;
					vmdebug('Refallback');
				}
				if(!isset($this->currency)) $this->currency = "";
				echo shopFunctionsF::renderVmSubLayout($this->productsLayout,array('products'=>$this->products,'currency'=>$this->currency,'products_per_row'=>$this->perRow,'showRating'=>$this->showRating));

				if(!empty($this->orderByList)) { ?>
					<div class="vm-pagination vm-pagination-bottom">
						<?php echo $this->vmPagination->getPagesLinks (); ?>
						<!--<span class="vm-page-counter"><?php echo $this->vmPagination->getPagesCounter (); ?></span>-->
					</div>
				<?php }
			} elseif (!empty($this->keyword)) {
				echo vmText::_ ('COM_VIRTUEMART_NO_RESULT') . ($this->keyword ? ' : (' . $this->keyword . ')' : '');
			}
			?>	
		</div>
	<?php } ?>
</div>

<?php
if(VmConfig::get ('jdynupdate', TRUE)){
	$j = "Virtuemart.container = jQuery('.category-view');
	Virtuemart.containerSelector = '.category-view';";

	//vmJsApi::addJScript('ajaxContent',$j);
}