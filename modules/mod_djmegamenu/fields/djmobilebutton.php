<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die();
defined('JPATH_BASE') or die;
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldDJMobilebutton extends JFormFieldText {
	
	protected $type = 'DJMobilebutton';
	
	protected function getInput()
	{
		$app = JFactory::getApplication();
		
		$attr = 'readonly="true"';
		$attr.= ' onclick="this.select();"';
		$attr.= ' style="cursor: pointer;"';
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		
		$moduleid = $app->input->get('id');
		$value = '';
		
		if($moduleid) {
			$value = '<div id="dj-megamenu'.$moduleid.'mobileWrap"></div>';
		} else {
			$attr .= ' placeholder="'.JText::_('MOD_DJMEGAMENU_MOBILE_MENU_WRAPPER_PLACEHOLDER').'"';
		}
				
		$html = '<input type="text" id="' . $this->id . '"' . ' value="'. htmlspecialchars($value, ENT_COMPAT, 'UTF-8') .'" ' . $attr . ' />';
		
		return ($html);
		
	}
}
?>