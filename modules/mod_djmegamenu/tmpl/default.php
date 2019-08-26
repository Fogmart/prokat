<?php
/**
 * @version $Id$
 * @package DJ-MegaMenu
 * @copyright Copyright (C) 2017 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MegaMenu is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MegaMenu is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MegaMenu. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die;
// Note. It is important to remove spaces between elements.

$stack = array();
?>
<?php if($params->get('fixed')) { ?>
<div id="dj-megamenu<?php echo $module->id; ?>sticky" class="dj-megamenu <?php echo 'dj-megamenu-'.$params->get('theme') . ' ' . $class_sfx; ?> dj-megamenu-sticky" style="display: none;">
	<?php if($params->get('fixed_logo', 0)) { ?>
		<div id="dj-megamenu<?php echo $module->id; ?>stickylogo" class="dj-stickylogo dj-align-<?php echo $params->get('fixed_logo_align') ?>">
			<a href="<?php echo JURI::base(); ?>">
				<img src="<?php echo $params->get('fixed_logo') ?>" alt="<?php echo $app->getCfg('sitename') ?>" />
			</a>
		</div>
	<?php } ?>
</div>
<?php } ?>

<ul id="dj-megamenu<?php echo $module->id; ?>" class="dj-megamenu <?php echo 'dj-megamenu-'.$params->get('theme') . ' ' . $params->get('orientation') . 'Menu ' . $class_sfx; ?>"
	<?php echo !empty($options) ? "data-options='".$options."'":""; ?> data-trigger="<?php echo (int)$params->get('width') ?>">
<?php
$first = true;
foreach ($list as $index => &$item) :
	$class = '';
	$aclass = '';

	if($item->level == $startLevel) {
		$class .= 'dj-up';
		$aclass.= 'dj-up_a ';
	}
	$class .= ' itemid'.$item->id;
	if($first) {
		$class .= ' first';
		$first = false;
	} else if($item->level > $startLevel+1 && $expand[$item->parent_id]=='tree') {
		// don't break into column in expanded submenu tree
	} else if($item->level > $startLevel && $item->params->get('djmegamenu-column_break',0)) { // start new column if break point is set
		echo '</ul></div>';
		if($item->params->get('djmegamenu-row_break', 0)) {
			echo '<div class="djsubrow_separator"></div>'; 
		}
		echo '<div class="dj-subcol" style="width:'.$item->params->get('djmegamenu-column_width').'"><ul class="dj-submenu">';
		$class .= ' first';
	}
	if ($item->id == $active_id) {
		$class .= ' current';
	}
	if (in_array($item->id, $path)) {
		$class .= ' active';
		$aclass .= ($item->level > $startLevel && $item->parent ? '-active active' : 'active');
	}
	elseif ($item->type == 'alias') {
		$aliasToId = $item->params->get('aliasoptions');
		if (in_array($aliasToId, $path)) {
			$class .= ' active';
			$aclass .= ($item->level > $startLevel && $item->parent ? '-active active' : 'active');
		}
	}

	if ($item->parent && $item->level == $startLevel && $item->params->get('djmegamenu-fullwidth')) {
		$class .= ' fullsub';
	}

	if ($item->parent && (!$endLevel || $item->level < $endLevel)) {
		$class .= ' parent';
		if($item->level > $startLevel) {
			$aclass = 'dj-more'.$aclass;
		}
	}
	
	if($item->type=='separator') {
		$class .= ' separator';
	}

	if(isset($item->modules)){
		$class .= ' withmodule';
	}
	
	if($item->params->get('djmegamenu-show') == 'mobile') {
		$class .= ' dj-hideitem';
	}
	
	if($item->parent && $item->level > $startLevel && $expand[$item->id]=='tree') {
		$class .= ' subtree';
	}
	
	if (!empty($class)) {
		$class = ' class="'.trim($class) .'"';
	}
	
	echo '<li'.$class.'>';
	
	if($item->params->get('djmegamenu-module_show_link',0) || (!isset($item->mobilemodules) && !isset($item->modules))) {
		// Render the menu item.
		require JModuleHelper::getLayoutPath('mod_djmegamenu', 'default_url');
	}
	if(isset($item->modules)) {
		echo '<div class="modules-wrap">'.$item->modules.'</div>';
	}
	// echo $item->level;
	// The next item is deeper.
	if ($item->deeper) {
		$stack[] = $item->id;
		if($item->level > $startLevel && $expand[$item->id]=='tree') {
			echo '<ul class="dj-subtree">';
		} else {
			
			if($item->params->get('djmegamenu-fullwidth')){
				$style = 'width: 100%;';
				$style_in = 'width: 100%;';
				$open_dir = '';
			} else {
				$style = '';
				$style_in = 'width:'.$subwidth[$item->id].'px;';
				
				$open_dir = $item->params->get('djmegamenu-dropdown_dir', $params->get('dropdown_dir'), '');
				if(!empty($open_dir)) $open_dir = 'open-'.$open_dir;
			}
				
			$image = $item->params->get('djmegamenu-bg_image','');
			if(!empty($image)) {
				$style_in .= ' background-image: url('.$image.'); '
						. ' background-position: '.$item->params->get('djmegamenu-bg_pos_hor', 'right').' '.$item->params->get('djmegamenu-bg_pos_ver', 'bottom').';'
						. ' background-repeat: no-repeat;';
			}
			
			echo '<div class="dj-subwrap '.$open_dir.' '.($subcols[$item->id] > 1 ? 'multiple_cols':'single_column').' subcols'.$subcols[$item->id].'" style="'.$style.'"><div class="dj-subwrap-in" style="'.$style_in.'">';
			echo '<div class="dj-subcol" style="width:'.$item->params->get('djmegamenu-first_column_width').'"><ul class="dj-submenu">';
		}
		$first = true;
	}
	// The next item is shallower.
	elseif ($item->shallower) {
		echo '</li>';
		for($i = $item->level - 1; $i >= $item->level - $item->level_diff; $i--) {
			$parent = array_pop($stack);
			if($expand[$parent] == 'tree' && $i > $startLevel) {
				echo '</ul></li>';
			} else {
				echo '</ul></div><div style="clear:both;height:0"></div></div></div></li>';
			}
		}
	}
	// The next item is on the same level.
	else {
		echo '</li>';
	}
endforeach;
?></ul>

<?php 
if($mobilemenu == '1') { ?>

	<div id="dj-megamenu<?php echo $module->id; ?>mobile" class="dj-megamenu-select <?php echo 'dj-megamenu-select-'.$params->get('mobiletheme') . ' select-' . $params->get('select_type') . ' ' . $class_sfx; ?>">
		<?php require(JModuleHelper::getLayoutPath('mod_djmegamenu', 'default_btn')); ?>
		<label class="hidden" aria-hidden="true" for="dj-megamenu<?php echo $module->id; ?>select"><?php echo $module->title ?></label>
	</div>

<?php } else if($mobilemenu == '2') { ?>

	<div id="dj-megamenu<?php echo $module->id; ?>mobile" class="dj-megamenu-offcanvas <?php echo 'dj-megamenu-offcanvas-'.$params->get('mobiletheme') . ' ' . $class_sfx; ?>">
		<?php require(JModuleHelper::getLayoutPath('mod_djmegamenu', 'default_btn')); ?>
		
		<aside id="dj-megamenu<?php echo $module->id; ?>offcanvas" class="dj-offcanvas <?php echo 'dj-offcanvas-'.$params->get('mobiletheme') . ' ' . $class_sfx; ?>" data-effect="<?php echo $params->get('offcanvas_effect') ?>">
			<div class="dj-offcanvas-top">
				<a href="#" class="dj-offcanvas-close-btn" aria-label="<?php echo JText::_('MOD_DJMEGAMENU_CLOSE_MENU_BTN'); ?>"><span class="fa fa-close" aria-hidden="true"></span></a>
			</div>
			<?php if($params->get('offcanvas_logo')) { ?>
				<div class="dj-offcanvas-logo">
					<a href="<?php echo JURI::base(); ?>">
						<img src="<?php echo $params->get('offcanvas_logo') ?>" alt="<?php echo $app->getCfg('sitename') ?>" />
					</a>
				</div>
			<?php } ?>
			
			<?php if(!empty($offmodules['top'])) { ?>
				<div class="dj-offcanvas-modules">
					<?php echo $offmodules['top'] ?>
				</div>
			<?php } ?>
			
			<div class="dj-offcanvas-content">			
				<?php require(JModuleHelper::getLayoutPath('mod_djmegamenu', 'default_mobile')); ?>
			</div>
			
			<?php if(!empty($offmodules['bottom'])) { ?>
				<div class="dj-offcanvas-modules">
					<?php echo $offmodules['bottom'] ?>
				</div>
			<?php } ?>
			
			<div class="dj-offcanvas-end" tabindex="0"></div>
		</aside>
	</div>

<?php } else if($mobilemenu == '3') { ?>

	<div id="dj-megamenu<?php echo $module->id; ?>mobile" class="dj-megamenu-accordion <?php echo 'dj-megamenu-accordion-'.$params->get('mobiletheme') .' dj-pos-'.$params->get('accordion_pos') . ' ' . ' dj-align-'.$params->get('accordion_align') . ' ' . $class_sfx; ?>">
		<?php require(JModuleHelper::getLayoutPath('mod_djmegamenu', 'default_btn')); ?>
		
		<div class="dj-accordion <?php echo 'dj-accordion-'.$params->get('mobiletheme') . ' ' . $class_sfx; ?>">
			<div class="dj-accordion-in">
				<?php require(JModuleHelper::getLayoutPath('mod_djmegamenu', 'default_mobile')); ?>
			</div>
		</div>
	</div>

<?php }
