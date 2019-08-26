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

$btnText = $params->get('mobile_button_text', JText::_('MOD_DJMEGAMENU_DEFAULT_BUTTON_TEXT'));
?>
<a href="#" class="dj-mobile-open-btn" aria-label="<?php echo JText::_('MOD_DJMEGAMENU_OPEN_MENU_BTN'); ?>"><?php 
	if($params->get('mobile_button', 'icon') == 'icon' || $params->get('mobile_button') == 'icon_text') { ?><span class="fa fa-bars" aria-hidden="true"></span><?php }
	if($params->get('mobile_button', 'icon') == 'text' || $params->get('mobile_button') == 'icon_text') { ?><span class="dj-mobile-open-btn-lbl"><?php echo $btnText ?></span><?php }
?></a>