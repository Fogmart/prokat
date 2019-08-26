<?php
/**
* @package Helix Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2015 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('resticted aceess');


/**
 * sp_xhtml chrome.
 *
 * 
 */
function modChrome_sp_xhtml($module, $params, $attribs) {

	$moduleTag     	= $params->get('module_tag', 'div');
	$bootstrapSize 	= (int) $params->get('bootstrap_size', 0);
	$moduleClass   	= $bootstrapSize != 0 ? ' col-sm-' . $bootstrapSize : '';
	$headerTag     	= htmlspecialchars($params->get('header_tag', 'h3'));
	$icon_sfx 		= "";
	if(strpos($params->get('header_class'), '@')===false){
		$headerClass   	= htmlspecialchars($params->get('header_class', 'sp-module-title'));
	}else{
		$headerClass 	= explode("@", htmlspecialchars($params->get('header_class')));
		$icon_sfx 		= '<i class="fa fa-'.trim($headerClass[1]) . '"></i>';
		$headerClass 	= $headerClass[0] ? trim($headerClass[0]) : 'sp-module-title';
	}
	
	if ($module->content) {
		echo '<' . $moduleTag . ' class="sp-module' . htmlspecialchars($params->get('moduleclass_sfx')) . $moduleClass . '">';

			if ($module->showtitle)
			{	
				if ($icon_sfx ){
					echo '<div class="' . $headerClass . '"><' . $headerTag . ' class="modtitle" ><span class="title">' . $icon_sfx . '</span></' . $headerTag . '></div>';
					
				}else{
					echo '<div class="' . $headerClass . '"><' . $headerTag . ' class="modtitle" ><span class="title">' . str_replace(array('{', '}', '[', ']'), array('<span class="word-small">','</span>', '<em style="display: none;">','</em>'), $module->title) . '</span></' . $headerTag . '></div>';
				}
			}

			echo '<div class="sp-module-content">';
			echo $module->content;
			echo '</div>';

		echo '</' . $moduleTag . '>';
	}
}


/**
 * vinaTabs chrome.
 *
 * @since   3.0
 */
 
function modChrome_vinaTabs($module, $params, $attribs)
{
	$area = 'vinaTabs-'.$module->position;

	static $modulecount;
	static $modules;

	if ($modulecount < 1)
	{
		$modulecount = count(JModuleHelper::getModules($module->position));
		$modules = array();
	}

	if ($modulecount == 1)
	{
		$temp = new stdClass;
		$temp->content = $module->content;
		$temp->title = $module->title;
		$temp->params = $module->params;
		$temp->id = $module->id;
		$modules[] = $temp;
		// list of moduletitles
		// list of moduletitles
		echo '<div id="'. $area.'" class="vinaTabs ' .$area. '">';
		foreach ($modules as $rendermodule)
		{ ?>
			<div style="display:none">
				<div class="tab-padding">
					<h2 style="display:none" class="title">
					<span id="<?php echo (preg_replace('/\s+/', '_',strtolower($rendermodule->title))); ?>" class="sptab-title">
						<?php echo str_replace(array('{', '}', '[', ']'), array('<span class="word-small">','</span>', '<em style="display: none;">','</em>'), $rendermodule->title) ; ?>
					</span>
					</h2>
					<?php echo $rendermodule->content; ?>
					<div style="clear:both"></div>
				</div>
			</div> <?php 
		}
		$modulecount--;
		echo '</div>';
	} else {
		$temp = new stdClass;
		$temp->content = $module->content;
		$temp->params = $module->params;
		$temp->title = $module->title;
		$temp->id = $module->id;
		$modules[] = $temp;
		$modulecount--;
	}
}