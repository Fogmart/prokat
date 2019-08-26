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

require_once (dirname(__FILE__) . DS . 'helper.php');

class modDJMegaMenuHelper extends modDJMMHelper {
	
	public static function parseParams(&$params) {
	
		// determine if this is a Pro version
		$params->set('pro', 1);
		
		parent::parseParams($params);
	}
	
	public static function loadModules($position, $style = 'xhtml')
	{
		if (!isset(self::$modules[$position])) {
			self::$modules[$position] = '';
			if($style == '0') $style = 'xhtml';
			$document	= JFactory::getDocument();
			$renderer	= $document->loadRenderer('module');
			$modules	= JModuleHelper::getModules($position);
			$params		= array('style' => preg_replace('/^[\w]+\-/i', '', $style));
			ob_start();
				
			foreach ($modules as $module) {
				echo $renderer->render($module, $params);
			}
	
			self::$modules[$position] = ob_get_clean();
		}
		return self::$modules[$position];
	}
		
}

if(!function_exists('adjustBrightness')) {
	function adjustBrightness($rgba, $factor) {
		
		// check for backward compatibility with hex color format
		if(strstr($rgba, '#')) {
			// Normalize into a six character long hex string
			$hex = str_replace('#', '', $rgba);
			if (strlen($hex) == 3) {
				$hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
			}
			// Split into three parts: R, G and B
			$color_parts = str_split($hex, 2);
			$rgba = 'rgba(';
			foreach ($color_parts as $color) {
				$rgba.= hexdec($color).', ';
			}
			$rgba.= '1)';
		}
		
		$color_parts = explode(',', $rgba);
		
		if(count($color_parts) != 4) return $rgba;
		
		$rgba = 'rgba(';
		foreach ($color_parts as $n => $color) {
			$color   = trim($color, 'rgba(), ');
			if($n == 3) {
				$rgba.= $color.')'; // copy opacity
				break;
			} else {
				$rgba.= ' '.max(0,min(255,(int) ($color * $factor))).', '; // Adjust color				
			}
		}

		return $rgba;
	}
}