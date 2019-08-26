<?php
/**
* Mod_VikRentCar_Itinerary
* http://www.extensionsforjoomla.com
*/
 
// no direct access
defined('_JEXEC') or die('Restricted Area');

//Joomla 3.0
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
//

require_once (dirname(__FILE__).DS.'helper.php');

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root().'modules/mod_vikrentcar_itinerary/mod_vikrentcar_itinerary.css');

$params->def('timeformat', 1);

if (intval($params->get('timeformat')) == 1) {
	$tf = 'H:i';
} else {
	$tf = 'h:i a';
}

$vrcdateformat = modVikrentcarItineraryHelper::getDateFormat();
if ($vrcdateformat == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($vrcdateformat == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}

$session = JFactory::getSession();
$modpickuplocation = $session->get('vrcplace', '');
$moddropofflocation = $session->get('vrcreturnplace', '');
$modpickupts = $session->get('vrcpickupts', '');
$moddropoffts = $session->get('vrcreturnts', '');

require JModuleHelper::getLayoutPath('mod_vikrentcar_itinerary', $params->get('layout', 'default'));
