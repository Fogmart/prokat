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

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @link   http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since  11.1
 */
class JFormFieldDJOnlyPro extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'DJOnlyPro';
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$lang = JFactory::getLanguage();
		$lang->load('mod_djmegamenu', JPATH_ROOT, 'en-GB', true, false);
    	$lang->load('mod_djmegamenu', JPATH_ROOT . '/modules/mod_djmegamenu', 'en-GB', true, false);
    	$lang->load('mod_djmegamenu', JPATH_ROOT, null, true, false);
    	$lang->load('mod_djmegamenu', JPATH_ROOT . '/modules/mod_djmegamenu', null, true, false);
    	
		$html = JText::sprintf('MOD_DJMEGAMENU_GET_PRO_LINK', JText::_('MOD_DJMEGAMENU_ONLY_PRO'));

		return $html;
	}
}
