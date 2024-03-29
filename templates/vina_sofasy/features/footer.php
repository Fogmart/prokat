<?php
/**
 * @package Helix3 Framework
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2015 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/
//no direct accees
defined ('_JEXEC') or die('resticted aceess');

class Helix3FeatureFooter {

	private $helix3;

	public function __construct($helix3){
		$this->helix3 = $helix3;
		$this->position = $this->helix3->getParam('copyright_position');
		$this->load_pos = $this->helix3->getParam('copyright_load_pos');
	}

	public function renderFeature() {

		if($this->helix3->getParam('enabled_copyright')) {
			$output = '';
			//Copyright
			if( $this->helix3->getParam('copyright') ) {
				$back_link = ' <a href="http://vinagecko.com">VinaGecko.com</a>';
				$output .= '<span class="sp-copyright">' . str_ireplace(array('{year}', '{VinaGecko.com}'),array(date('Y'),$back_link), $this->helix3->getParam('copyright')) . '</span>';
			}
			
			return $output;
		}
		
	}    
}