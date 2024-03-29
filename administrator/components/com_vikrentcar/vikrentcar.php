<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') or die('Restricted access');

/* Portability and Adapters */
include(JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_vikrentcar" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "defines.php");
include(JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_vikrentcar" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "request.php");
include(JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_vikrentcar" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "error.php");

/* A high level of error reporting may disturb the ajax responses, so we shut it up */
$er_l = VikRequest::getString('error_reporting');
$er_l = strlen($er_l) && intval($er_l == '-1') ? -1 : 0;

defined('VIKRENTCAR_ERROR_REPORTING') OR define('VIKRENTCAR_ERROR_REPORTING', $er_l);
error_reporting(VIKRENTCAR_ERROR_REPORTING);

/* ACL */
if (!JFactory::getUser()->authorise('core.manage', 'com_vikrentcar')) {
	return VikError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

/* Main libraries */
require_once(VRC_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikrentcar.php");
require_once(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "helper.php");
require_once(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "jv_helper.php");

/* Load assets for CSS and JS */
VikRentCar::loadFontAwesome(true);
$document = JFactory::getDocument();
$document->addStyleSheet(VRC_ADMIN_URI . 'vikrentcar.css');
$document->addStyleSheet(VRC_ADMIN_URI . 'resources/fonts/vrcicomoon.css');
JHtml::_('jquery.framework', true, true);
if (VikRentCar::loadJquery()) {
	JHtml::_('script', VRC_SITE_URI . 'resources/jquery-1.12.4.min.js', false, true, false, false);
}
VikRentCar::getVrcApplication()->normalizeBackendStyles();

/* Framework Rendering */
jimport('joomla.application.component.controller');
$controller = JControllerVikRentCar::getInstance('VikRentCar');
$controller->execute(VikRequest::getCmd('task'));
$controller->redirect();
