<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen

 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2012 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_showcategory.php 9227 2016-05-27 10:55:25Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined ( '_JEXEC' ) or die ( 'Restricted access' ); ?>

<div class="category-children">
<?php
	if ($this->category->haschildren) {

		// Start the Output
		if (!empty($this->category->children)) { ?>
			<span class="pull-left"><?php echo vmText::_('VM_LANG_CATEGORIES') . ": ";?></span>
		   <?php foreach ($this->category->children as $category) { ?> 
			<a href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $category->virtuemart_category_id, FALSE); ?>" title="<?php echo vmText::_($category->category_name) ?>">
				<?php echo vmText::_($category->category_name) ?>

			</a>
		<?php }
		}
	} ?>
</div>