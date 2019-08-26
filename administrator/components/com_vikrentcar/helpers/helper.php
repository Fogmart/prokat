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

class VikRentCarHelper {

	public static function printHeader($highlight="") {
		$cookie = JFactory::getApplication()->input->cookie;
		$vrc_auth_cars = JFactory::getUser()->authorise('core.vrc.cars', 'com_vikrentcar');
		$vrc_auth_prices = JFactory::getUser()->authorise('core.vrc.prices', 'com_vikrentcar');
		$vrc_auth_orders = JFactory::getUser()->authorise('core.vrc.orders', 'com_vikrentcar');
		$vrc_auth_gsettings = JFactory::getUser()->authorise('core.vrc.gsettings', 'com_vikrentcar');
		$vrc_auth_management = JFactory::getUser()->authorise('core.vrc.management', 'com_vikrentcar');
		?>
		<div class="vrc-menu-container">
			<div class="vrc-menu-left"><img src="<?php echo VRC_ADMIN_URI; ?>vikrentcar.png" alt="VikRentCar Logo" /></div>
			<div class="vrc-menu-right">
				<ul class="vrc-menu-ul">
					<?php
					if ($vrc_auth_prices || $vrc_auth_gsettings) {
					?>
					<li class="vrc-menu-parent-li">
						<span><i class="vrcicn-key2"></i> <a href="javascript: void(0);"><?php echo JText::_('VRMENUONE'); ?></a></span>
						<ul class="vrc-submenu-ul">
						<?php
						if ($vrc_auth_prices) {
							?>
							<li><span class="<?php echo ($highlight=="2" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=iva"><?php echo JText::_('VRMENUNINE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="1" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=prices"><?php echo JText::_('VRMENUFIVE'); ?></a></span></li>
							<?php
						}
						if ($vrc_auth_gsettings) {
							?>
							<li><span class="<?php echo ($highlight=="3" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=places"><?php echo JText::_('VRMENUTENTHREE'); ?></a></span></li>
							<?php
						}
						if ($vrc_auth_prices) {
							?>
							<li><span class="<?php echo ($highlight=="restrictions" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=restrictions"><?php echo JText::_('VRMENURESTRICTIONS'); ?></a></span></li>
							<?php
						}
						?>
						</ul>
					</li>
					<?php
					}
					if ($vrc_auth_cars) {
					?>
					<li class="vrc-menu-parent-li">
						<span><i class="vrcicn-truck"></i> <a href="javascript: void(0);"><?php echo JText::_('VRMENUTWO'); ?></a></span>
						<ul class="vrc-submenu-ul">
							<li><span class="<?php echo ($highlight=="4" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=categories"><?php echo JText::_('VRMENUSIX'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="6" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=optionals"><?php echo JText::_('VRMENUTENFIVE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="5" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=carat"><?php echo JText::_('VRMENUTENFOUR'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="7" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=cars"><?php echo JText::_('VRMENUTEN'); ?></a></span></li>
						</ul>
					</li>
					<?php
					}
					if ($vrc_auth_prices) {
					?>
					<li class="vrc-menu-parent-li">
						<span><i class="vrcicn-calculator"></i> <a href="javascript: void(0);"><?php echo JText::_('VRCMENUFARES'); ?></a></span>
						<ul class="vrc-submenu-ul">
							<li><span class="<?php echo ($highlight=="fares" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=tariffs"><?php echo JText::_('VRCMENUPRICESTABLE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="13" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=seasons"><?php echo JText::_('VRMENUTENSEVEN'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="12" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=locfees"><?php echo JText::_('VRMENUTENSIX'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="20" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=oohfees"><?php echo JText::_('VRCMENUOOHFEES'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="ratesoverv" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=ratesoverv"><?php echo JText::_('VRCMENURATESOVERVIEW'); ?></a></span></li>
						</ul>
					</li>
					<?php
					}
					?>
					<li class="vrc-menu-parent-li">
						<span><i class="vrcicn-credit-card"></i> <a href="javascript: void(0);"><?php echo JText::_('VRMENUTHREE'); ?></a></span>
						<ul class="vrc-submenu-ul">
						<?php
						if ($vrc_auth_orders) {
						?>
							<li><span class="<?php echo ($highlight=="8" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=orders"><?php echo JText::_('VRMENUSEVEN'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="19" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=calendar"><?php echo JText::_('VRCMENUQUICKRES'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="15" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=overv"><?php echo JText::_('VRMENUTENNINE'); ?></a></span></li>
						<?php
						}
						?>
							<li><span class="<?php echo ($highlight=="18" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar"><?php echo JText::_('VRCMENUDASHBOARD'); ?></a></span></li>
						</ul>
					</li>
					<?php
					if ($vrc_auth_management) {
					?>
					<li class="vrc-menu-parent-li">
						<span><i class="vrcicn-credit-card"></i> <a href="javascript: void(0);"><?php echo JText::_('VRCMENUMANAGEMENT'); ?></a></span>
						<ul class="vrc-submenu-ul">
							<li><span class="<?php echo ($highlight=="customers" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=customers"><?php echo JText::_('VRCMENUCUSTOMERS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="17" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=coupons"><?php echo JText::_('VRCMENUCOUPONS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="22" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=graphs"><?php echo JText::_('VRMENUGRAPHS'); ?></a></span></li>
						</ul>
					</li>
					<?php
					}
					if ($vrc_auth_gsettings) {
					?>
					<li class="vrc-menu-parent-li">
						<span><i class="vrcicn-cogs"></i> <a href="javascript: void(0);"><?php echo JText::_('VRMENUFOUR'); ?></a></span>
						<ul class="vrc-submenu-ul">
							<li><span class="<?php echo ($highlight=="11" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=config"><?php echo JText::_('VRMENUTWELVE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="21" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=translations"><?php echo JText::_('VRMENUTRANSLATIONS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="14" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=payments"><?php echo JText::_('VRMENUTENEIGHT'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="16" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=customf"><?php echo JText::_('VRMENUTENTEN'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="10" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=stats"><?php echo JText::_('VRMENUEIGHT'); ?></a></span></li>
						</ul>
					</li>
					<?php
					}
					?>
				</ul>
				<div class="vrc-menu-updates">
				<?php
				if ($highlight == '18' || $highlight == '11') {
					//VikUpdater
					JPluginHelper::importPlugin('e4j');
					$dispatcher = JEventDispatcher::getInstance();
					$callable 	= $dispatcher->trigger('isCallable');
					if (count($callable) && $callable[0]) {
						//Plugin enabled
						$params = new stdClass;
						$params->version 	= E4J_SOFTWARE_VERSION;
						$params->alias 		= 'com_vikrentcar';
						
						$upd_btn_text = strrev('setadpU kcehC');
						$ready_jsfun = '';
						$result = $dispatcher->trigger('getVersionContents', array(&$params));
						if (count($result) && $result[0]) {
							$upd_btn_text = $result[0]->response->shortTitle;
						} else {
							$ready_jsfun = 'jQuery("#vik-update-btn").trigger("click");';
						}
						?>
						<button type="button" id="vik-update-btn" onclick="<?php echo count($result) && $result[0] && $result[0]->response->compare == 1 ? 'document.location.href=\'index.php?option=com_vikrentcar&task=updateprogram\'' : 'checkVersion(this);'; ?>">
							<i class="vrcicn-cloud"></i> 
							<span><?php echo $upd_btn_text; ?></span>
						</button>
						<script type="text/javascript">
						function checkVersion(button) {
							jQuery(button).find('span').text('Checking...');
							jQuery.ajax({
								type: 'POST',
								url: 'index.php?option=com_vikrentcar&task=checkversion&tmpl=component',
								data: {}
							}).done(function(resp){
								var obj = jQuery.parseJSON(resp);
								console.log(obj);
								if (obj.status == 1 && obj.response.status == 1) {
									jQuery(button).find('span').text(obj.response.shortTitle);
									if (obj.response.compare == 1) {
										jQuery(button).attr('onclick', 'document.location.href="index.php?option=com_vikrentcar&task=updateprogram"');
									}
								}
							}).fail(function(resp){
								console.log(resp);
							});
						}
						jQuery(document).ready(function() {
							<?php echo $ready_jsfun; ?>
						});
						</script>
						<?php
					} else {
						//Plugin disabled
						//we display an empty button
						?>
						<button type="button" id="vik-update-btn" onclick="alert('The plugin Vik Updater is either disabled or not installed');">
							<i class="vrcicn-cloud"></i> 
							<span></span>
						</button>
						<?php
					}
				}
				?>
				</div>
			</div>
		</div>
		<script type="text/javascript">
		var vrc_menu_type = <?php echo (int)$cookie->get('vrcMenuType', '0', 'string') ?>;
		var vrc_menu_on = ((vrc_menu_type % 2) == 0);
		//
		function vrcDetectMenuChange(e) {
			e = e || window.event;
			if ((e.which == 77 || e.keyCode == 77) && e.altKey) {
				//ALT+M
				vrc_menu_type++;
				vrc_menu_on = ((vrc_menu_type % 2) == 0);
				console.log(vrc_menu_type, vrc_menu_on);
				//Set Cookie for next page refresh
				var nd = new Date();
				nd.setTime(nd.getTime() + (365*24*60*60*1000));
				document.cookie = "vrcMenuType="+vrc_menu_type+"; expires=" + nd.toUTCString() + "; path=/";
			}
		}
		document.onkeydown = vrcDetectMenuChange;
		//
		jQuery(document).ready(function(){
			jQuery('.vrc-menu-parent-li').click(function() {
				if (jQuery(this).find('ul.vrc-submenu-ul').is(':visible')) {
					vrc_menu_on = false;
					return;
				}
				jQuery('ul.vrc-submenu-ul').hide();
				jQuery(this).find('ul.vrc-submenu-ul').show();
				vrc_menu_on = true;
			});
			jQuery('.vrc-menu-parent-li').hover(
				function() {
					if (vrc_menu_on === true) {
						jQuery(this).addClass('vrc-menu-parent-li-opened');
						jQuery(this).find('ul.vrc-submenu-ul').show();
					}
				},function() {
					if (vrc_menu_on === true) {
						jQuery(this).removeClass('vrc-menu-parent-li-opened');
						jQuery(this).find('ul.vrc-submenu-ul').hide();
					}
				}
			);
			var targetY = jQuery('.vrc-menu-right').offset().top + jQuery('.vrc-menu-right').outerHeight() + 150;
			jQuery(document).click(function(event) { 
				if (!jQuery(event.target).closest('.vrc-menu-right').length && parseInt(event.which) == 1 && event.pageY < targetY) {
					jQuery('ul.vrc-submenu-ul').hide();
					vrc_menu_on = true;
				}
			});

			if (jQuery('.vmenulinkactive').length) {
				jQuery('.vmenulinkactive').parent('li').parent('ul').parent('li').addClass('vrc-menu-parent-li-active');
				if ((vrc_menu_type % 2) != 0) {
					jQuery('.vmenulinkactive').parent('li').parent('ul').show();
				}
			}
		});
		</script>
		<?php
	}
	
	public static function printFooter() {
		echo '<br clear="all" />' . '<div id="hmfooter">' . JText::sprintf('VRCVERSION', E4J_SOFTWARE_VERSION) . ' <a href="https://joomlok.com/">by - joomlok.com</a></div>';
	}
	
	/**
	 * Returns a BS-compatible dropdown menu that submits
	 * the form whenever a value is selected.
	 * 
	 * @param 	array		$arr_values
	 * @param 	string 		$current_key
	 * @param 	string 		$empty_value
	 * @param 	string 		$default
	 * @param 	string 		$input_name
	 *
	 * @return 	string
	 */
	public static function getDropDown($arr_values, $current_key, $empty_value, $default, $input_name) {
		$dropdown = '';
		$x = rand(1, 999);
		if (defined('JVERSION') && version_compare(JVERSION, '2.6.0') < 0) {
			//Joomla 2.5
			$dropdown .= '<select name="'.$input_name.'" onchange="document.adminForm.submit();">'."\n";
			$dropdown .= '<option value="">'.$default.'</option>'."\n";
			$list = "\n";
			foreach ($arr_values as $k => $v) {
				$dropdown .= '<option value="'.$k.'"'.($k == $current_key ? ' selected="selected"' : '').'>'.$v.'</option>'."\n";
			}
			$dropdown .= '</select>'."\n";
		} else {
			//Joomla 3.x
			$dropdown .= '<script type="text/javascript">'."\n";
			$dropdown .= 'function dropDownChange'.$x.'(setval) {'."\n";
			$dropdown .= '	document.getElementById("dropdownval'.$x.'").value = setval;'."\n";
			$dropdown .= '	document.adminForm.submit();'."\n";
			$dropdown .= '}'."\n";
			$dropdown .= '</script>'."\n";
			$dropdown .= '<input type="hidden" name="'.$input_name.'" value="'.$current_key.'" id="dropdownval'.$x.'"/>'."\n";
			$list = "\n";
			foreach ($arr_values as $k => $v) {
				if($k == $current_key) {
					$default = $v;
				}
				$list .= '<li><a href="javascript: void(0);" onclick="dropDownChange'.$x.'(\''.$k.'\');">'.$v.'</a></li>'."\n";
			}
			$list .= '<li class="divider"></li>'."\n".'<li><a href="javascript: void(0);" onclick="dropDownChange'.$x.'(\'\');">'.$empty_value.'</a></li>'."\n";
			$dropdown .= '<div class="btn-group">
		<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">'.$default.' <span class="caret"></span></button>
		<ul class="dropdown-menu" role="menu">'.
			$list.
		'</ul>
	</div>';
		}

		return $dropdown;
	}

	//VikUpdater plugin methods - Start
	public static function pUpdateProgram($version)
	{
		?>
		<form name="adminForm" action="index.php" method="post" enctype="multipart/form-data" id="adminForm">
	
			<div class="span12">
				<fieldset class="form-horizontal">
					<legend><?php $version->shortTitle ?></legend>
					<div class="control"><strong><?php echo $version->title; ?></strong></div>

					<div class="control" style="margin-top: 10px;">
						<button type="button" class="btn btn-primary" onclick="downloadSoftware(this);">
							<?php echo JText::_($version->compare == 1 ? 'VRDOWNLOADUPDATEBTN1' : 'VRDOWNLOADUPDATEBTN0'); ?>
						</button>
					</div>

					<div class="control vik-box-error" id="update-error" style="display: none;margin-top: 10px;"></div>

					<?php if ( isset($version->changelog) && count($version->changelog) ) { ?>

						<div class="control vik-update-changelog" style="margin-top: 10px;">

							<?php echo self::digChangelog($version->changelog); ?>

						</div>

					<?php } ?>
				</fieldset>
			</div>

			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="option" value="com_vikrentcar"/>
		</form>

		<div id="vikupdater-loading" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999999 !important; background-color: rgba(0,0,0,0.5);">
			<div id="vikupdater-loading-content" style="position: fixed; left: 33.3%; top: 30%; width: 33.3%; height: auto; z-index: 101; padding: 10px; border-radius: 5px; background-color: #fff; box-shadow: 5px 5px 5px 0 #000; overflow: auto; text-align: center;">
				<span id="vikupdater-loading-message" style="display: block; text-align: center;"></span>
				<span id="vikupdater-loading-dots" style="display: block; font-weight: bold; font-size: 25px; text-align: center; color: green;">.</span>
			</div>
		</div>
		
		<script type="text/javascript">
		var isRunning = false;
		var loadingInterval;

		function vikLoadingAnimation() {
			var dotslength = jQuery('#vikupdater-loading-dots').text().length + 1;
			if (dotslength > 10) {
				dotslength = 1;
			}
			var dotscont = '';
			for (var i = 1; i <= dotslength; i++) {
				dotscont += '.';
			}
			jQuery('#vikupdater-loading-dots').text(dotscont);
		}

		function openLoadingOverlay(message) {
			jQuery('#vikupdater-loading-message').html(message);
			jQuery('#vikupdater-loading').fadeIn();
			loadingInterval = setInterval(vikLoadingAnimation, 1000);
		}

		function closeLoadingOverlay() {
			jQuery('#vikupdater-loading').fadeOut();
			clearInterval(loadingInterval);
		}

		function downloadSoftware(btn) {

			if ( isRunning ) {
				return;
			}

			switchRunStatus(btn);
			setError(null);

			var jqxhr = jQuery.ajax({
				url: "index.php?option=com_vikrentcar&task=updateprogramlaunch&tmpl=component",
				type: "POST",
				data: {}
			}).done(function(resp) {

				try {
					var obj = jQuery.parseJSON(resp);
				} catch (e) {
					console.log(resp);
					return;
				}
				
				if ( obj === null ) {

					// connection failed. Something gone wrong while decoding JSON
					alert('<?php echo addslashes('Connection Error'); ?>');

				} else if ( obj.status ) {

					document.location.href = 'index.php?option=com_vikrentcar';
					return;

				} else {

					console.log("### ERROR ###");
					console.log(obj);

					if ( obj.hasOwnProperty('error') ) {
						setError(obj.error);
					} else {
						setError('Your website does not own a valid support license!<br />Please visit <a href="https://extensionsforjoomla.com" target="_blank">extensionsforjoomla.com</a> to purchase a license or to receive assistance.');
					}

				}

				switchRunStatus(btn);

			}).fail(function(resp) {
				console.log('### FAILURE ###');
				console.log(resp);
				alert('<?php echo addslashes('Connection Error'); ?>');

				switchRunStatus(btn);
			}); 
		}

		function switchRunStatus(btn) {
			isRunning = !isRunning;

			jQuery(btn).prop('disabled', isRunning);

			if ( isRunning ) {
				// start loading
				openLoadingOverlay('The process may take a few minutes to complete.<br />Please wait without leaving the page or closing the browser.');
			} else {
				// stop loading
				closeLoadingOverlay();
			}
		}

		function setError(err) {

			if ( err !== null && err !== undefined && err.length ) {
				jQuery('#update-error').show();
			} else {
				jQuery('#update-error').hide();
			}

			jQuery('#update-error').html(err);

		}

	</script>
		<?php
	}

	/**
	 * Scan changelog structure.
	 *
	 * @param 	array 	$arr 	The list containing changelog elements.
	 * @param 	mixed 	$html 	The html built. 
	 * 							Specify false to echo the structure immediately.
	 *
	 * @return 	string|void 	The HTML structure or nothing.
	 */
	private static function digChangelog(array $arr, $html = '') {

		foreach ( $arr as $elem ):

			if ( isset($elem->tag) ):

				// build attributes

				$attributes = "";
				if ( isset($elem->attributes) ) {

					foreach ( $elem->attributes as $k => $v ) {
						$attributes .= " $k=\"$v\"";
					}

				}

				// build tag opening

				$str = "<{$elem->tag}$attributes>";

				if ( $html ) {
					$html .= $str;
				} else {
					echo $str;
				}

				// display contents

				if ( isset($elem->content) ) {

					if ( $html ) {
						$html .= $elem->content;
					} else {
						echo $elem->content;
					}

				}

				// recursive iteration for elem children

				if ( isset($elem->children) ) {
					self::digChangelog($elem->children, $html);
				}

				// build tag closure

				$str = "</{$elem->tag}>";

				if ( $html ) {
					$html .= $str;
				} else {
					echo $str;
				}

			endif;

		endforeach;

		return $html;
	}
	//VikUpdater plugin methods - End

}
