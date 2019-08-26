<?php
/**
 * @package     VikRentCar
 * @subpackage  mod_vikrentcar_search
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2017 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') or die('Restricted Area');

//Joomla 3.0
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
//

require_once (dirname(__FILE__).DS.'helper.php');

$params->def('showloc', 0);
$params->def('showcat', 0);

modVikrentcarSearchHelper::importVrcLib();
modVikrentcarSearchHelper::loadFontAwesome();
$vrtext  = modVikrentcarSearchHelper::getFormattingText($params);

require JModuleHelper::getLayoutPath('mod_vikrentcar_search', $params->get('layout', 'default'));
