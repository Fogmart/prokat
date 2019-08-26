<?php
/**
 * @package Helix3 Framework
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2014 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/
//no direct accees
defined ('_JEXEC') or die('resticted aceess');

class Helix3FeatureAbout {

	private $helix3;
	public $position;

	public function __construct($helix3){
		$this->helix3 = $helix3;
		$this->position = $this->helix3->getParam('about_position');
		$this->load_pos = $this->helix3->getParam('about_load_pos');
	}

	public function renderFeature() {
		
		if($this->helix3->getParam('enable_about')) {
			$output = '<div class="sp-about-wrapper">';
			if($this->helix3->getParam('about_title')){
				$output .= '<div class="sp-module-title style-title2">';
				$output .= '<h3 class="modtitle">';
				$output .= '<span class="title">'.$this->helix3->getParam('about_title').'</span>';
				$output .= '</h3>';
				$output .= '</div>';
			}
			
			if($this->helix3->getParam('about_logo')){
				jimport('joomla.image.image');
				
				if( $this->helix3->getParam('about_logo_image') ) {
					$path = JPATH_ROOT . '/' . $this->helix3->getParam('about_logo_image');
				} else {
					$path = JPATH_ROOT . '/templates/' . $this->helix3->getTemplate() . '/images/presets/' . $this->helix3->Preset() . '/logo.png';
				}
				if(file_exists($path)) {
					$image = new JImage( $path );
					$width 	= $image->getWidth();
					$height = $image->getHeight();
				} else {
					$width 	= '';
					$height = '';
				}
			}
			
			$sitename = JFactory::getApplication()->get('sitename');
			
			if( $this->helix3->getParam('about_logo')) {
				
				$output .= '<div class="logo">';
					$output .= '<a href="' . JURI::base(true) . '/">';
					if( $this->helix3->getParam('about_logo_image') ) {
						$output .= '<img class="sp-default-logo" src="' . $this->helix3->getParam('about_logo_image') . '" alt="'. $sitename .'">';
					} else {
						$output .= '<img class="sp-default-logo" src="' . $this->helix3->getTemplateUri() . '/images/presets/' . $this->helix3->Preset() . '/logo.png" alt="'. $sitename .'">';
					}
					$output .= '</a>';
				$output .= '</div>';
				
			}
			if($this->helix3->getParam('about_text')){
				$output .= '<div class="about-text">'.$this->helix3->getParam('about_text').'</div>';
			}
			$output .= '</div>';
			return $output;
		}
		
	}    
}