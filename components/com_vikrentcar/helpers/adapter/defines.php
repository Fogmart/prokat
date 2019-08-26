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

/* URI Constants for admin and site sections (with trailing slash) */
defined('VRC_ADMIN_URI') or define('VRC_ADMIN_URI', JUri::root().'administrator/components/com_vikrentcar/');
defined('VRC_SITE_URI') or define('VRC_SITE_URI', JUri::root().'components/com_vikrentcar/');
defined('VRC_ADMIN_URI_REL') or define('VRC_ADMIN_URI_REL', './administrator/components/com_vikrentcar/');
defined('VRC_SITE_URI_REL') or define('VRC_SITE_URI_REL', './components/com_vikrentcar/');

/* Path Constants for admin and site sections (with NO trailing directory separator) */
defined('VRC_ADMIN_PATH') or define('VRC_ADMIN_PATH', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_vikrentcar');
defined('VRC_SITE_PATH') or define('VRC_SITE_PATH', JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_vikrentcar');

/* Other Constants that may not be available in the framework */
defined('JPATH_COMPONENT_SITE') or define('JPATH_COMPONENT_SITE', JPATH_SITE . DIRECTORY_SEPARATOR . 'com_vikrentcar');
defined('JPATH_COMPONENT_ADMINISTRATOR') or define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'com_vikrentcar');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

/* Adapter for Controller and View Classes for compatiblity with the various frameworks */
if (!class_exists('JViewVikRentCar') && class_exists('JViewLegacy')) {

	class JViewVikRentCar extends JViewLegacy {
		/* adapter for JViewLegacy */
	}

	class JControllerVikRentCar extends JControllerLegacy {
		/* adapter for JControllerLegacy */
	}

} elseif (!class_exists('JViewVikRentCar') && class_exists('JView')) {

	class JViewVikRentCar extends JView {
		/* adapter for JView */
	}

	class JControllerVikRentCar extends JController {
		/* adapter for JController */
	}

}
