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

jimport('joomla.form.formfield');

class JFormFieldVrccategory extends JFormField { 
	protected $type = 'vrccategory';
	
	function getInput() {
		$key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
		$val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		$categories="";
		$dbo = JFactory::getDBO();
		$q="SELECT * FROM `#__vikrentcar_categories` ORDER BY `#__vikrentcar_categories`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$allvrcc=$dbo->loadAssocList();
			foreach($allvrcc as $vrcc) {
				$categories.='<option value="'.$vrcc['id'].'"'.($this->value == $vrcc['id'] ? " selected=\"selected\"" : "").'>'.$vrcc['name'].'</option>';
			}
		}
		$html = '<select class="inputbox" name="' . $this->name . '" >';
		$html .= '<option value=""></option>';
		$html .= $categories;
		$html .='</select>';
		return $html;
    }
}


?>