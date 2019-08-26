<?php
/**
* Mod_VikRentCar_ConfirmationNumber
* http://www.extensionsforjoomla.com
*/
 
// no direct access
defined('_JEXEC') or die('Restricted Area');

//Joomla 3.0
if(!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
//

require_once (dirname(__FILE__).DS.'helper.php');

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root().'modules/mod_vikrentcar_confirmationnumber/mod_vikrentcar_confirmationnumber.css');

$vrcdateformat = modVikrentcarConfirmationNumberHelper::getDateFormat();
if ($vrcdateformat == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($vrcdateformat == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}

require JModuleHelper::getLayoutPath('mod_vikrentcar_confirmationnumber', $params->get('layout', 'default'));
