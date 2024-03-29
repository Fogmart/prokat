<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined("_JEXEC") or die('Restricted Access');

defined("VRC_ROUTER_DEBUG") or define("VRC_ROUTER_DEBUG", false);
defined("VRC_ROUTER_BUILD_DEBUG") or define("VRC_ROUTER_BUILD_DEBUG", false);

class VikRentCarRouter {

    private $debug = false;
    private $build_debug = false;

    public function __construct($debug=false, $build_debug=false) {
        $this->debug = $debug;
        $this->build_debug = $build_debug;
    }

    public function build(&$query) {

        $app = JFactory::getApplication();
        $menu = $app->getMenu();
        $active = $menu->getActive();

        $dbo = JFactory::getDBO();

        $segments = array();

        if($this->build_debug) {
            echo '<div style="border: 1px solid #f00;padding: 5px;margin: 5px;">';
            echo '<pre>'.print_r($_REQUEST, true).'</pre><br/>';echo '<pre>'.print_r($query, true).'</pre><br/>';echo '<pre>'.print_r($active, true).'</pre><br/>';
            echo '</div>';
        }

        if( isset($query['view']) ) {

            if( $query['view'] == 'cardetails' && isset($query['carid']) ) {
                
                if( empty($active->query['view']) ) {
                    $segments[] = $query['view'];
                }

                $q = "SELECT `id`,`alias` FROM `#__vikrentcar_cars` WHERE `id`=".intval($query['carid'])." LIMIT 1;";
                $dbo->setQuery($q);
                $dbo->execute();
                if( $dbo->getNumRows() > 0 ) {
                    if( $active->query['view'] != 'cardetails' ) {
                        $car_data = $dbo->loadAssoc();
                        $segments[] = $this->renderTag( $car_data['alias'] );
                    }
                    unset($query['carid']);
                }

                unset($query['view']);

            }

        }

        return $segments;
    }

    public function parse($segments) {
        $total = count($segments);
        
        $dbo = JFactory::getDBO();

        $app = JFactory::getApplication();
        $menu = $app->getMenu();
        $active = $menu->getActive();
        
        $query_view = ( empty($active->query['view']) ? '' : $active->query['view'] );
    
        $vars = array();
        
        if($this->debug) {
            echo '$query_view: '.$query_view.'<br/><pre>'.print_r($_REQUEST, true).'</pre><br/>';echo '<pre>'.print_r($active, true).'</pre><br/>';echo '<pre>'.print_r($segments, true).'</pre><br/>';
        }

        if( $total > 0 ) {
            if( ($query_view == 'carslist' || $query_view == 'promotions' || $query_view == 'availability') ) {
                $vars['view'] = 'cardetails';
                $itemid = $this->getProperItemID($menu, $vars['view']);
                if( !empty($itemid) ) {
                    $vars['Itemid'] = $itemid;
                }
                $q = "SELECT `id` FROM `#__vikrentcar_cars` WHERE `alias`=".$dbo->quote($this->aliasNoSlug($segments[0]))." LIMIT 1;";
                $dbo->setQuery($q);
                $dbo->execute();
                if( $dbo->getNumRows() > 0 ) {
                    $vars['carid'] = $dbo->loadResult();
                }   
            }
        }

        return $vars;
    }

    private function renderTag($str) {
        $str = JFilterOutput::stringURLSafe($str);
        return $str;
    }

    private function aliasNoSlug($alias) {
        $name = str_replace(':', '-', $alias);
        return trim($name);
    }

    private function getAliasFromSegments($segments) {
        foreach ($segments as $value) {
            if(strpos($value, ':') !== false) {
                return $value;
            }
        }
        return '';
    }
    
    private function getProperItemID($menu, $itemtype) {
        foreach( $menu->getMenu() as $itemid => $item ) {
            if( $item->query['option'] == 'com_vikrentcar' && $item->query['view'] == $itemtype ) {
                return $itemid;
            }
        }
        return 0;
    }

}

/**
*
*
*/

function vikrentcarBuildRoute(&$query) {
    $router = new VikRentCarRouter(VRC_ROUTER_DEBUG, VRC_ROUTER_BUILD_DEBUG);
    return $router->build($query);
}

function vikrentcarParseRoute($segments) {
    $router = new VikRentCarRouter(VRC_ROUTER_DEBUG, VRC_ROUTER_BUILD_DEBUG);
    return $router->parse($segments);
}

?>