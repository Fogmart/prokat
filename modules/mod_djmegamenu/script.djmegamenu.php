<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
class Mod_DJMegamenuInstallerScript
{
	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 */
		
	function postflight( $type, $parent ) {
		
		$db = JFactory::getDBO();
		$config = JFactory::getConfig();
		
		if($type == 'install') {
			
			$db->setQuery("UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element='djmegamenu'");
			$db->query();
		}
		
		if($type == 'update') {
			
			// we need to handle the update server for package here
			require_once(JPath::clean(JPATH_ROOT.'/modules/mod_djmegamenu/fields/djupdater.php'));
			JFormFieldDJUpdater::setUpdateServer('MegaMenu', true);
			
			// disable old update server
			$db->setQuery("UPDATE #__update_sites SET enabled=0 WHERE name='DJ-MegaMenu Package' AND type='extension'");
			$db->query();
		}
	
	}
}
