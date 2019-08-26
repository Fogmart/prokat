<?php
/**
* Mod_VikRentCar_ConfirmationNumber
* http://www.extensionsforjoomla.com
*/

defined('_JEXEC') or die('Restricted Area');

class modVikrentcarConfirmationNumberHelper
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
}
