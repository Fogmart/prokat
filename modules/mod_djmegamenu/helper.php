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

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_ROOT . DS .'modules' . DS . 'mod_menu' . DS . 'helper.php');

class modDJMMHelper extends modMenuHelper {
	
	private static $subwidth = array();
	private static $subcols = array();
	private static $expand = array();
	private static $rows = array();
	public static $modules = null;
	public static $mobilemodules = null;
	private static $version = null;
	
	public static function parseParams(&$params) {
	
		$params->def('menutype', $params->get('menu','mainmenu'));
		$params->def('startLevel', 1);
		$params->def('endLevel', 0);
		$params->def('showAllChildren', 1);
		$params->def('mobiletheme', 'dark');
		$params->set('column_width', (int)$params->get('column_width',200));
		$params->def('width', 979);
		$params->def('select_type', 'button');
		$params->def('accordion_pos', 'static');
		$params->def('accordion_align', 'center');
		$params->def('accordion_collapsed', 0);
		$params->def('icons', '2');
		$params->def('subtitles', '2');
		if($params->get('pro')) {
			$params->def('fixed_logo', 0);
			$params->def('fixed_logo_align', 'right');
			$params->def('orientation', 'horizontal');
		} else {
			$params->set('fixed', 0);
			$params->set('openDelay', 0);
			$params->set('orientation', 'horizontal');
			$params->set('mobile_button', 'icon');
			if($params->get('theme')=='_custom') $params->set('theme', 'default');
			if($params->get('mobiletheme')=='_custom') $params->set('mobiletheme', 'default');
		}
		if($params->get('orientation') == 'vertical') {
			$params->set('fixed', 0);
			$params->set('wrapper', '');
		}
	}
	
	public static function getActive(&$params) {
		
		$menu = JFactory::getApplication()->getMenu();

		// Get active menu item from parameters
		if ($params->get('active')) {
			$active = $menu->getItem($params->get('active'));
		} else {
			$active = false;
		}

		// If no active menu, use current or default
		if (!$active) {
			$active = ($menu->getActive()) ? $menu->getActive() : $menu->getDefault();
		}

		return $active;		
	}	
	
	public static function getList(&$params) {
		
		$list = parent::getList($params);
		
		// array with submenu wrapper widths
		if(!isset(self::$subwidth[$params->get('module_id')])) {
			
			self::$subwidth[$params->get('module_id')] = array();
			self::$subcols[$params->get('module_id')] = array();
			self::$expand[$params->get('module_id')] = array();
			self::$rows[$params->get('module_id')] = array();
			
			$first = false;
			$parent = null;
			$hasSubtitles = false;
			$startLevel = $params->get('startLevel');
			
			foreach($list as $item) {
				
				if($params->get('orientation')=='vertical' && $item->params->get('djmegamenu-fullwidth')) {
					$item->params->set('djmegamenu-fullwidth', 0);
					$item->params->set('djmegamenu-column_width', '');
				}
				
				if($parent || $item->params->get('djmegamenu-column_break',0)) {
					
					if(!$params->get('pro')) {
						$item->params->set('djmegamenu-column_width', $params->get('column_width'));
					}
					
					if(isset(self::$rows[$params->get('module_id')][$item->parent_id])) { // child of full width submenu
						
						if(!isset(self::$subwidth[$params->get('module_id')][$item->parent_id])) self::$subwidth[$params->get('module_id')][$item->parent_id] = 0;
						
						$width = (int)$item->params->get('djmegamenu-column_width',$params->get('percent_width', 25));
						
						if($width > 100) $width = 100;
						
						if($width + self::$subwidth[$params->get('module_id')][$item->parent_id] > 100) {
							$item->params->set('djmegamenu-row_break', 1);
							self::$rows[$params->get('module_id')][$item->parent_id]++;
							self::$subwidth[$params->get('module_id')][$item->parent_id] = 0;
						}
						
						self::$subwidth[$params->get('module_id')][$item->parent_id] += $width;
						
						if($parent) {
							$parent->params->set('djmegamenu-first_column_width', $width.'%');
							$parent=null;
						} else {
							$item->params->set('djmegamenu-column_width', $width.'%');
						}
						
					} else { // pixels widths
						
						$width = (int)$item->params->get('djmegamenu-column_width',$params->get('column_width'));
						
						if($parent) {
							$parent->params->set('djmegamenu-first_column_width', $width.'px');
							$parent=null;
						} else {
							$item->params->set('djmegamenu-column_width', $width.'px');
						}
						
						// calculate width of the sum
						if(!isset(self::$subwidth[$params->get('module_id')][$item->parent_id])) self::$subwidth[$params->get('module_id')][$item->parent_id] = 0;
						self::$subwidth[$params->get('module_id')][$item->parent_id] += (int)$item->params->get('djmegamenu-column_width',$params->get('column_width'));
						
					}
					
					// count number of columns for this submenu
					if(!isset(self::$subcols[$params->get('module_id')][$item->parent_id])) self::$subcols[$params->get('module_id')][$item->parent_id] = 1;
					else self::$subcols[$params->get('module_id')][$item->parent_id]++;
				}
				
				if($item->deeper) {
					$first = true;
					$parent = $item;
					
					if($params->get('pro') && $item->level == $startLevel && $item->params->get('djmegamenu-fullwidth')) {
						self::$rows[$params->get('module_id')][$item->id] = 1;
						//echo "<pre>".print_r($item, true)."</pre>";
					}
				}
				
				// load module if position set
				if($params->get('pro') && $position = $item->params->get('djmegamenu-module_pos')) {
					$item->modules = modDJMegaMenuHelper::loadModules($position,$item->params->get('djmegamenu-module_style','xhtml'));
				}
				// load module if position set
				if($params->get('pro') && $position = $item->params->get('djmobilemenu-module_pos')) {
					$item->mobilemodules = modDJMegaMenuHelper::loadModules($position,$item->params->get('djmobilemenu-module_style','xhtml'));
				}
				
				$subtitle = htmlspecialchars($item->params->get('djmegamenu-subtitle'));
				if(empty($subtitle) && $params->get('usenote')) $subtitle = htmlspecialchars($item->note);
				if($item->menu_image && !$item->params->get('menu_text', 1)) $subtitle = null;
				$item->params->set('djmegamenu-subtitle', $subtitle);
				
				if($item->level == $startLevel && !empty($subtitle)) $hasSubtitles = true;
				
				if($item->parent) self::$expand[$params->get('module_id')][$item->id] = $item->params->get('djmegamenu-expand', 
						isset(self::$expand[$params->get('module_id')][$item->parent_id]) ? self::$expand[$params->get('module_id')][$item->parent_id] : $params->get('expand','dropdown'));
			}
			
			$params->def('hasSubtitles',$hasSubtitles);
		}
		
		return $list;
	}
	
	public static function getSubWidth(&$params) {
		
		if(!isset(self::$subwidth[$params->get('module_id')])) self::getList($params);
		
		return self::$subwidth[$params->get('module_id')];
	}
	
	public static function getSubCols(&$params) {
	
		if(!isset(self::$subcols[$params->get('module_id')])) self::getList($params);
	
		return self::$subcols[$params->get('module_id')];
	}
	
	public static function getExpand(&$params) {
		
		if(!isset(self::$expand[$params->get('module_id')])) self::getList($params);
		
		return self::$expand[$params->get('module_id')];
	}
	
	public static function addTheme(&$params, $direction) {
		
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		
		$ver = self::getVersion($params);
		
		if($params->get('theme')=='_custom') {
			
			$params->set('theme', 'custom'.$params->get('module_id'));
			$css = 'media/djmegamenu/themes/'.$params->get('theme').($direction=='rtl'?'_rtl':'').'.css';
			$path = JPATH_ROOT . DS . 'modules/mod_djmegamenu/themes/custom.css.php';
			
			// generate custom theme css if it doesn't already exist or the source file is newer
			if(!JFile::exists(JPATH_ROOT . DS . $css) || filemtime($path) > filemtime(JPath::clean(JPATH_ROOT . DS . $css))) {
				
				ob_start();
				include($path);
				$buffer = ob_get_clean();
				
				JFile::write(JPATH_ROOT . DS . $css, $buffer);
			}
			
			$doc->addStyleSheet(JURI::root(true).'/'.$css.'?v='.$ver);
		
		} else {
		
			if($params->get('theme')!='_override') {
				$css = 'modules/mod_djmegamenu/themes/'.$params->get('theme','default').'/css/djmegamenu.css';
			} else {
				$params->set('theme', 'override');
				$css = 'templates/'.$app->getTemplate().'/css/djmegamenu.css';
			}
		
			// load theme only if file exists or ef4 template in use
			if(JFile::exists(JPATH_ROOT . DS . $css) || defined('JMF_EXEC')) {
				$doc->addStyleSheet(JURI::root(true).'/'.$css.'?v='.$ver);
			}
			if($direction == 'rtl') { // load rtl theme css if file exists or ef4 template in use
				$css_rtl = JFile::stripExt($css).'_rtl.css';
				if(JFile::exists(JPATH_ROOT . DS . $css_rtl) || defined('JMF_EXEC')) {
					$doc->addStyleSheet(JURI::root(true).'/'.$css_rtl.'?v='.$ver);
				}
			}
		}
	}
	
	public static function addMobileTheme(&$params, $direction) {
	
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		
		$ver = self::getVersion($params);
		
		if($params->get('mobiletheme')=='_custom') {
			
			$params->set('mobiletheme', 'custom'.$params->get('module_id'));
			$css = 'media/djmegamenu/mobilethemes/'.$params->get('mobiletheme').($direction=='rtl'?'_rtl':'').'.css';
			$path = JPATH_ROOT . DS . 'modules/mod_djmegamenu/mobilethemes/custom.css.php';
				
			// generate custom mobile theme css if it doesn't already exist or the source file is newer
			if(!JFile::exists(JPATH_ROOT . DS . $css) || filemtime($path) > filemtime(JPath::clean(JPATH_ROOT . DS . $css))) {
			
				ob_start();
				include($path);
				$buffer = ob_get_clean();
			
				JFile::write(JPATH_ROOT . DS . $css, $buffer);
			}
				
			$doc->addStyleSheet(JURI::root(true).'/'.$css.'?v='.$ver);
			
		} else {
		
			if($params->get('mobiletheme')!='_override') {
				$css = 'modules/mod_djmegamenu/mobilethemes/'.$params->get('mobiletheme','dark').'/djmobilemenu.css';
			} else {
				$params->set('mobiletheme', 'override');
				$css = 'templates/'.$app->getTemplate().'/css/djmobilemenu.css';
			}
		
			// add only if theme file exists
			if(JFile::exists(JPATH_ROOT . DS . $css)) {
				$doc->addStyleSheet(JURI::root(true).'/'.$css.'?v='.$ver);
			}
			if($direction == 'rtl') { // load rtl css if exists in theme or joomla template
				$css_rtl = JFile::stripExt($css).'_rtl.css';
				if(JFile::exists(JPATH_ROOT . DS . $css_rtl)) {
					$doc->addStyleSheet(JURI::root(true).'/'.$css_rtl.'?v='.$ver);
				}
			}
		}
	}
	
	public static function getVersion($params) {
		
		if(is_null(self::$version)) {
			
			$db = JFactory::getDBO();
			$db->setQuery("SELECT manifest_cache FROM #__extensions WHERE element='mod_djmegamenu' LIMIT 1");
			$ver = json_decode($db->loadResult());
			self::$version = $ver->version . ($params->get('pro', 0) ? '.pro' : '.free');
		}
		
		return self::$version;
	}
}

?>