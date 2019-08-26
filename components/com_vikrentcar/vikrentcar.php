<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

/* --- Joomla portability --- */
include(JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_vikrentcar" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "defines.php");
include(JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_vikrentcar" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "request.php");
include(JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_vikrentcar" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "error.php");
/* --- */

$er_l = isset($_REQUEST['error_reporting']) && intval($_REQUEST['error_reporting'] == '-1') ? -1 : 0;
defined('VIKRENTCAR_ERROR_REPORTING') OR define('VIKRENTCAR_ERROR_REPORTING', $er_l);
error_reporting(VIKRENTCAR_ERROR_REPORTING);

/* Main library */
require_once(VRC_SITE_PATH . DS . "helpers" . DS . "lib.vikrentcar.php");

/* Load assets */
$document = JFactory::getDocument();
VikRentCar::loadFontAwesome();
$document->addStyleSheet(VRC_SITE_URI.'vikrentcar_styles.css');
$document->addStyleSheet(VRC_SITE_URI.'vikrentcar_custom.css');

jimport('joomla.application.component.controller');
$controller = JControllerVikRentCar::getInstance('Vikrentcar');
$controller->execute(VikRequest::getCmd('task'));
$controller->redirect();
