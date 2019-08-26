<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: https://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @link   http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since  11.1
 */
class JFormFieldDJUpdater extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'DJUpdater';
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$app	= JFactory::getApplication();
		$doc	= JFactory::getDocument();
		
		$module = $this->form->getData()->get('module');
		
		if($module) {
			$lang = JFactory::getLanguage();
			$lang->load($module, JPATH_ROOT, 'en-GB', true, false);
			$lang->load($module, JPATH_ROOT . '/modules/'.$module, 'en-GB', true, false);
			$lang->load($module, JPATH_ROOT, null, true, false);
			$lang->load($module, JPATH_ROOT . '/modules/'.$module, null, true, false);
			
			$doc->addStylesheet(JURI::root(true).'/modules/'.$module.'/assets/css/forms.css');
		}
		
		$extension = $this->element['extension'];
		$pro = (int)$this->element['pro'];
		
		$task = $app->input->get('djtask');
		if($task) {
			ob_clean();
			echo 'DJUPDATERRESPONSE'.$this->$task($extension);
			$app->close();
		}
		
		self::setUpdateServer('MegaMenu', $pro);
		
		$html = self::getSubscription($extension, $pro);
		
		return $html;
	}

	public static function getSubscription($ext, $pro){
		
		if (!in_array('curl', get_loaded_extensions())) {
			return self::renderAlert(JText::_('DJUPDATER_CURL_NOT_INSTALLED'), 'error');
		}
		
		$app	= JFactory::getApplication();
		
		$ch = curl_init();
		$db = JFactory::getDBO();
		$query = "SELECT manifest_cache FROM #__extensions WHERE element ='".$ext."'";
		$db->setQuery($query);
		$mc = json_decode($db->loadResult());
		$version = $mc->version;
		$config = JFactory::getConfig();
		$secret_file = JFile::makeSafe('license_'.$config->get('secret').'.txt');
		$license_file = JPath::clean(dirname(__FILE__).'/../'.$secret_file);
		
		if(JFile::exists($license_file)){
			$license = JFile::read($license_file);
		}else{
			$license = '';
		}
		
		curl_setopt($ch, CURLOPT_URL,'https://dj-extensions.com/index.php?option=com_djsubscriptions&view=checkDomain&license='.$license.'&ext='.$ext.'&v='.$version.'');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$u = JFactory::getURI();

		curl_setopt ($ch, CURLOPT_REFERER, $u->getHost());

		$contents = curl_exec ($ch);

		if(curl_errno($ch)) {
			$err = curl_error($ch);
			@curl_close ($ch);
			return self::renderAlert(JText::_('DJUPDATER_CURL_ERROR').'<p>CURL ERROR: '.$err.'</p>', 'error');

		}
		curl_close ($ch);

		$res = explode(';', $contents);
		//echo self::renderAlert(print_r($res, true));
		if($pro) {
			$html  = '<h4>'.JText::_('MOD_DJMEGAMENU_MODULE_DESC').'  '.$version.'</h4>';
		} else {
			$html  = '<h4>'.JText::_('MOD_DJMEGAMENU_LIGHT_MODULE_DESC').'  '.$version.'</h4>';
		}
		
		if(strstr($res[0], 'E')){
			$t = JRequest::getVar('task','');
			if($t!='license' && $t!='Savelicense'){
				if($license==''){
					//$app->enqueueMessage(JText::_('COM_DJCLASSIFIEDS_DJLIC_ENTER_LICENSE'),'Error');
				}else{
					$app->enqueueMessage(end($res),'Error');
				}
			}

			if(isset($res[3])){
				$msg_type = 'Error';
				if(isset($res[4])){
					$msg_type = $res[4];
				}
				$app->enqueueMessage($res[3],$msg_type);
			}
			
			if(version_compare($version, $res[1], '<')) $html .= '<h5 class="text-error">'. JText::_('DJUPDATER_UPDATE_AVAILABLE').' <span>'.$res[1].'</span></h5>';
			else $html .= '<h5 class="text-success">'. JText::_('DJUPDATER_LATEST_VERSION_INSTALLED').'</h5>';
			
			if($pro) {
				$html .= '<p>'.JText::_('DJUPDATER_LICENSE_INFO').'</p>';
			} else {
				$html .= '<p>'.JText::_('DJUPDATER_UPGRADE_TO_PRO').'</p>';
			}
			
			$html .= '<input id="license" type="text" name="license" class="input input-large" placeholder="'. JText::_('DJUPDATER_PASTE_KEY').'" /> ';
			$html .= '<button id="register" class="btn btn-info" href="#">'.($pro ? JText::_('DJUPDATER_REGISTER_KEY') : JText::_('DJUPDATER_REGISTER_UPGRADE')).'</button>';
			if(!$pro) $html .= JText::sprintf('MOD_DJMEGAMENU_GET_PRO_LINK', JText::_('DJUPDATER_BUY_LICENSE'));
			
			$js = "
				jQuery(document).ready(function(){
					
					var button = jQuery('#register');
					var loader = jQuery('<span class=\"icon-refresh djspin\" />');
					
					button.click(function(e){
						button.prop('disabled', true);
						button.prepend(loader);
						e.preventDefault();
					
						jQuery.ajax({
							data: {
								license: jQuery('#license').val(),
								djtask: 'save'
							}
						}).done(function(data) {
							var message = data.substr(data.lastIndexOf('DJUPDATERRESPONSE')+17);
							button.closest('.alert').before(jQuery(message));
							setTimeout(function(){ location.reload(); }, 1000);
						})
						.fail(function() {
							alert( 'connection error' );
							button.prop('disabled', false);
							loader.detach();
						});
					});
					
				});
			";
			
			JFactory::getDocument()->addScriptDeclaration($js);
			
			return self::renderAlert($html, 'info');

		}else{

			if(isset($res[5])){
				$msg_type = 'Error';
				if(isset($res[6])){
					$msg_type = $res[6];
				}
				$app->enqueueMessage($res[5],$msg_type);
			}
			
			if(version_compare($version, $res[3], '<')) $html .= '<h5 class="text-error">'. JText::_('DJUPDATER_UPDATE_AVAILABLE').' <span>'.$res[3].'</span></h5>';
			else $html .= '<h5 class="text-success">'. JText::_('DJUPDATER_LATEST_VERSION_INSTALLED').'</h5>';
						
			$html .= '<p>'.JText::sprintf('DJUPDATER_VALID_LICENSE_INFO', $license, date("d.m.Y", strtotime($res[2]))).'</p>';
			
			if($pro) {
				if(version_compare($version, $res[3], '<')) {
					$html .= '<button id="update" class="btn btn-success">'.JText::_('DJUPDATER_UPDATE').'</button>';
				}
			} else {
				$html .= '<button id="update" class="btn btn-success">'.JText::_('DJUPDATER_UPGRADE').'</button>';
			}
			
			$js = "
				jQuery(document).ready(function(){
			
					var button = jQuery('#update');
					var loader = jQuery('<span class=\"icon-refresh djspin\" />');
					
					if(button.length) {
						button.click(function(e){
							button.prop('disabled', true);
							button.prepend(loader);
							e.preventDefault();
				
							jQuery.ajax({
								url: 'index.php',
								method: 'post',
								data: {
									option: 'com_installer',
									view: 'install',
									task: 'install.install',
									installtype: 'url',
									'".JSession::getFormToken()."': 1,
									install_url: 'https://dj-extensions.com/index.php?option=com_djsubscriptions&view=getUpdate&license=$license&ext=$ext&v=".$version.($pro ? '':'.free')."'
								}
							}).done(function(data) {
								var hidden = jQuery('<div class=\"hidden\">'+data+'</div>');
								jQuery(document.body).append(hidden);
								
								var message = hidden.find('.alert');
								if(!message.length) message = data;
								button.closest('.alert').before(message);
								setTimeout(function(){ location.reload(); }, 1000);
								
								hidden.remove();
							})
							.fail(function() {
								alert( 'connection error' );
								button.prop('disabled', false);
								loader.detach();
							});
						});
					}
				});
			";
			
			JFactory::getDocument()->addScriptDeclaration($js);
			
			return self::renderAlert($html, 'success');
		}
	}
	
	public static function renderAlert($msg, $type = '', $title = '') {
		
		if(!in_array($type, array('success', 'error', 'info', ''))) $type = 'info';
		
		$html = 	'<div class="alert alert-'.$type.'">'
				.		(!empty($title) ? '<h3>'.$title.'</h3>' : '')
				.		'<div class="alert-body">'.$msg.'</div>'
				.	'</div>';
		
		return $html;
		
	}
	
	private function save($ext){
		
		$app	= JFactory::getApplication();
		$config = JFactory::getConfig();
		$db = JFactory::getDbo();
		
		$license = JRequest::getVar('license');
		$r = JRequest::getString('release', '0');
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,'https://dj-extensions.com/index.php?option=com_djsubscriptions&view=registerLicense&license='.$license.'&ext='.$ext.'&r='.$r);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$u = JFactory::getURI();
		curl_setopt ($ch, CURLOPT_REFERER, $u->getHost());
	
		if(!curl_errno($ch))
		{
			$contents = curl_exec ($ch);
		}
	
		curl_close ($ch);
		$res = explode(';', $contents);
		
		$secret_file = JFile::makeSafe('license_'.$config->get('secret').'.txt');
		$license_file = JPath::clean(dirname(__FILE__).'/../'.$secret_file);
		
		if(strstr($res[0], 'E')){
			$query = "UPDATE #__update_sites SET extra_query='' WHERE name='DJ-MegaMenu' AND type='extension' ";
			$db->setQuery($query);
			$db->query();
			
			return self::renderAlert(end($res), 'error');
		} else if(strstr($res[0], 'R')){
			$query = "UPDATE #__update_sites SET extra_query='' WHERE name='DJ-MegaMenu' AND type='extension' ";
			$db->setQuery($query);
			$db->query();
			
			JFile::delete($license_file);
		} else{
			$query = "SELECT manifest_cache FROM #__extensions WHERE element='pkg_dj-megamenu' AND type='package' ";
			$db->setQuery($query);
			$mc = json_decode($db->loadResult());
			$version = $mc->version;
			
			$extra_query = 'license='.$license.'&v='.$version.'&site='.JURI::root();
			$query = "UPDATE #__update_sites SET extra_query='".addslashes($extra_query)."' WHERE name='DJ-MegaMenu' AND type='extension' ";
			$db->setQuery($query);
			$db->query();
			
			JFile::write($license_file, $license);
		}
		
		return self::renderAlert(end($res), 'success');
	}
	
	public static function setUpdateServer($name = 'MegaMenu', $pro = false) {
	
		if(empty($name)) return;
		// update the update server information for package
		$db = JFactory::getDbo();
		$config = JFactory::getConfig();
		$secret_file = JFile::makeSafe('license_'.$config->get('secret').'.txt');
		$license_file = JPath::clean(JPATH_ROOT.'/modules/mod_dj'.strtolower($name).'/'.$secret_file);
			
		if($pro && JFile::exists($license_file)){
			$license = JFile::read($license_file);
		}else{
			$license = '';
		}
	
		$query = "SELECT extension_id, manifest_cache FROM #__extensions WHERE element='pkg_dj-".strtolower($name)."' AND type='package' ";
		$db->setQuery($query);
		$pkg = $db->loadObject();
			
		if($pkg) {
			$mc = json_decode($pkg->manifest_cache);
			$version = $mc->version;
	
			$extra_query = $pro ? 'license='.$license.'&v='.$version.'&site='.JURI::root() : '';
	
			$db->setQuery("SELECT COUNT(*) FROM #__update_sites WHERE name='DJ-".$name."' AND type='extension'");
			if ($db->loadResult() > 0) {
				$db->setQuery("UPDATE #__update_sites SET
						location='https://dj-extensions.com/api/getUpdateInfo?extension=dj".strtolower($name).($pro ? '':'_light').".xml',
	                    extra_query='".addslashes($extra_query)."'
	                    WHERE name='DJ-".$name."' AND type='extension'");
			} else {
				$db->setQuery("INSERT INTO #__update_sites (`name`, `type`, `location`, `enabled`, `extra_query`) VALUES
	                    ('DJ-".$name."', 'extension', 'https://dj-extensions.com/api/getUpdateInfo?extension=dj".strtolower($name).($pro ? '':'_light').".xml', 1, '".addslashes($extra_query)."')");
				$db->query();
					
				$update_site_id = $db->insertid();
				$db->setQuery("INSERT INTO #__update_sites_extensions (`update_site_id`, `extension_id`)
						VALUES (".$update_site_id.", ".$pkg->extension_id.")");
			}
			$db->query();
		}
	}
}
