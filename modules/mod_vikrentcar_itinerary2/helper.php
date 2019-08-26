<?php
/**
* Mod_VikRentCar
* http://www.extensionsforjoomla.com
*/

defined('_JEXEC') or die('Restricted Area');

class modVikrentcarItineraryHelper
{
    function getDateFormat()
    {
    	$session = JFactory::getSession();
    	$sval = $session->get('getDateFormat', '');
    	if (!empty($sval)) {
    		return $sval;
    	} else {
    		$dbo = JFactory::getDBO();
    		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='dateformat';";
    		$dbo->setQuery($q);
    		$dbo->execute();
    		$s = $dbo->loadAssocList();
    		return $s[0]['setting'];
    	}
    }
	
	function getLocationName($idloc)
    {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_places` WHERE `id`=".intval($idloc).";";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['name'];
	}
	
}
