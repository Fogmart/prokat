<?php 
/**
 * @version $Id$
 * @package DJ-MegaMenu
 * @copyright Copyright (C) 2017 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MegaMenu is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MegaMenu is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MegaMenu. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
?>

/* DJ-MegaMenu general styles */
.dj-megamenu-<?php echo $params->get('theme') ?> {
    padding: 0 !important;
    margin: 0 !important;
    list-style: none;
    height: auto;
    position: relative;
    z-index: 500;
    font-family: Arial, Helvetica, sans-serif;
    width: auto;
    background: <?php echo $params->get('megabg'); ?>;
}
.dj-megamenu-<?php echo $params->get('theme') ?>:after {
    content: "";
    clear: both;
    display: table;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li.dj-up {
	position: relative;
    display: block;
    float: left;
    padding: 0 !important;
    margin: 0 !important;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a {
    display: block;
    float: left;
    height: 60px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
    text-decoration: none;
    padding: 0 20px;
    cursor: pointer;
    /*background: <?php echo $params->get('megabg'); ?>;*/
    color: <?php echo $params->get('megacolor'); ?>;
    border-right: 1px solid <?php echo adjustBrightness($params->get('megabg'), 0.8); ?>;
    border-left: 1px solid <?php echo adjustBrightness($params->get('megabg'), 1.2); ?>;
    -webkit-transition: all 0.2s ease-out;
	transition: all 0.2s ease-out;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li.first a.dj-up_a {
	border-left-width: 0;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li:last-child a.dj-up_a {
	border-right-width: 0;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a span {
    float: left;
    display: block;
    padding: 0 0 0 !important;
    height: 60px;
    line-height: 60px;
    background: transparent;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a.withsubtitle span {
	line-height: 48px;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a span.dj-drop {
    padding: 0 20px 0 0 !important;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a .arrow {
	display: inline-block;
    font-family: FontAwesome;
	position: absolute;
	right: 16px;
	top: calc(50% - 6px);
	font-size: 1em;
	line-height: 1;
	font-style: normal;
	font-weight: normal;
	-webkit-transition: all 0.2s ease-out;
	transition: all 0.2s ease-out;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a .arrow:before {
	content: "\f107";
}
.dj-megamenu-<?php echo $params->get('theme') ?> li:hover a.dj-up_a .arrow,
.dj-megamenu-<?php echo $params->get('theme') ?> li.hover a.dj-up_a .arrow,
.dj-megamenu-<?php echo $params->get('theme') ?> li.active a.dj-up_a .arrow {
	top: calc(50% - 4px);
}
.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a small.subtitle {
	display: block;
    font-size: 12px;
    font-weight: normal; 
    line-height: 1;
    text-transform: none;
    color: <?php echo $params->get('megastcolor'); ?>;
    margin-top: -12px;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li:hover a.dj-up_a,
.dj-megamenu-<?php echo $params->get('theme') ?> li.hover a.dj-up_a, 
.dj-megamenu-<?php echo $params->get('theme') ?> li.active a.dj-up_a {
    background: <?php echo $params->get('megabg_a'); ?>;
    color: <?php echo $params->get('megacolor_a'); ?>;
    border-right-color: <?php echo adjustBrightness($params->get('megabg_a'), 0.8); ?>;
    border-left-color: <?php echo adjustBrightness($params->get('megabg_a'), 1.2); ?>;
    
}
.dj-megamenu-<?php echo $params->get('theme') ?> li:hover a.dj-up_a small.subtitle,
.dj-megamenu-<?php echo $params->get('theme') ?> li.hover a.dj-up_a small.subtitle, 
.dj-megamenu-<?php echo $params->get('theme') ?> li.active a.dj-up_a small.subtitle {
	color: <?php echo $params->get('megastcolor_a'); ?>;
}

.dj-megamenu-<?php echo $params->get('theme') ?> li.separator > a {
    cursor: default;
}

.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a span span.image-title {
	background: none;
	padding: 0 !important;
	margin: 0 0 0 15px;
	display: inline-block;
	float: none;
	height: auto;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a img {
	border: 0;
	margin: 0;
	max-height: 32px;
	vertical-align: middle;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a.withsubtitle img {
	margin-top: -24px;
}
/* Default list styling */
.dj-megamenu-<?php echo $params->get('theme') ?> li:hover,
.dj-megamenu-<?php echo $params->get('theme') ?> li.hover {
    position: relative;
    z-index: 200;
}
/* Hide submenus */

.dj-megamenu-<?php echo $params->get('theme') ?> li div.dj-subwrap, .dj-megamenu-<?php echo $params->get('theme') ?> li:hover div.dj-subwrap li div.dj-subwrap,
.dj-megamenu-<?php echo $params->get('theme') ?> li.hover div.dj-subwrap li div.dj-subwrap {
    position: absolute;
    left: -9999px;
    top: -9999px;
    margin: 0;
    padding: 0;
    list-style: none;
}
/* Show first level submenu */
.dj-megamenu-<?php echo $params->get('theme') ?> li:hover div.dj-subwrap,
.dj-megamenu-<?php echo $params->get('theme') ?> li.hover div.dj-subwrap {
	left: 0;
    top: 100%;
    background: <?php echo $params->get('megasubbg'); ?>;
    height: auto;
    z-index: 300;
    -webkit-box-shadow:  0px 0px 5px 0px rgba(0, 0, 0, 0.2);
    box-shadow:  0px 0px 5px 0px rgba(0, 0, 0, 0.2);
}
/* Show higher level submenus */
.dj-megamenu-<?php echo $params->get('theme') ?> li:hover div.dj-subwrap li:hover > div.dj-subwrap,
.dj-megamenu-<?php echo $params->get('theme') ?> li.hover div.dj-subwrap li.hover > div.dj-subwrap {
    left: 100%;
    top: 5px;
    margin: 0 0 0 10px;
    background: <?php echo $params->get('megasubbg'); ?>;
    height: auto;
    z-index: 400;
    -webkit-box-shadow:  0px 0px 5px 0px rgba(0, 0, 0, 0.2);
    box-shadow:  0px 0px 5px 0px rgba(0, 0, 0, 0.2);
}

/* Submenu elements styles - drop down */
.dj-megamenu-<?php echo $params->get('theme') ?> li div.dj-subcol {
	float: left;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu {	
    padding: 0px;
    margin: 0 10px;
    height: auto;
    width: auto;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li {
	list-style: none outside;
    display: block;
    height: auto;
    position: relative;
    width: auto;
    border-top: 1px solid <?php echo adjustBrightness($params->get('megasubbg'), 1.2); ?>;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li.first {
	border: 0;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li > a {
    display: block;
    font-size: 13px;
    font-weight: normal;
    line-height: 16px;
    color: <?php echo $params->get('megasubcolor'); ?>;
    /*background: <?php echo $params->get('megasubbg'); ?>;*/
    text-decoration: none;
    padding: 12px 20px;
    margin: 0 -10px;
    -webkit-transition: all 0.2s ease-out;
	transition: all 0.2s ease-out;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li > a span.image-title {
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li > a img,
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li > a .fa, 
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li > a [class^="icon-"] {
	float: left;
	border: 0;
	margin: 0 15px 8px 0;
	vertical-align: middle;
	max-height: 16px;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li > a small.subtitle {
	color: <?php echo $params->get('megasubstcolor'); ?>;
    display: block;
    font-size: 10px;
    line-height: 1;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li > a:hover,
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li > a.active,
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li.hover:not(.subtree) > a {
	background: <?php echo $params->get('megasubbg_a'); ?>;
	color: <?php echo $params->get('megasubcolor_a'); ?>;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li > a:hover small.subtitle,
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li > a.active small.subtitle,
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li.hover:not(.subtree) > a small.subtitle {
	color: <?php echo $params->get('megasubstcolor_a'); ?>;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li.parent > a .arrow {
    display: inline-block;
    font-family: FontAwesome;
	position: absolute;
	right: 6px;
	top: 13px;
	font-size: 1em;
	line-height: 1;
	font-style: normal;
	font-weight: normal;
	-webkit-transition: all 0.2s ease-out;
	transition: all 0.2s ease-out;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li.parent > a .arrow:before {
	content: "\f105";
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li.parent:not(.subtree) > a:hover .arrow {
	right: 4px;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li.subtree > a .arrow:before {
	content: "\f107";
}

/* Submenu elements styles - tree */
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-subtree {
	list-style: none outside;
	padding: 0 !important;
    margin: 5px 0 5px 25px !important;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-subtree > li {
	list-style: square outside;
	padding: 0;
    margin: 0;
    color: <?php echo $params->get('megasubstcolor'); ?>;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-subtree > li > a {
	display: block;
    font-size: 12px;
    font-weight: normal;
    line-height: 13px;
    color: <?php echo $params->get('megasubcolor'); ?>;
    background: transparent;
    text-decoration: none;
    padding: 5px 0;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-subtree > li > a:hover {
	color: <?php echo $params->get('megasubcolor_a'); ?>;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-subtree > li.active > a {
	text-decoration: underline;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-subtree > li > a small.subtitle {
	color: <?php echo $params->get('megasubstcolor'); ?>;
    display: block;
    font-size: 0.9em;
    line-height: 1;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-subtree > li > a:hover small.subtitle {
    color: <?php echo $params->get('megasubstcolor_a'); ?>;
}

/* modules loaded into menu */
.dj-megamenu-<?php echo $params->get('theme') ?> .modules-wrap {
	padding: 0px 10px;
	color: <?php echo $params->get('megamodcolor'); ?>;
}
.dj-megamenu-<?php echo $params->get('theme') ?> .modules-wrap p {
	display: block !important;
	padding: 0 !important;
}

/* sticky menu */
.dj-megamenu-<?php echo $params->get('theme') ?>.dj-megamenu-fixed {
	position: fixed;
	-webkit-backface-visibility: hidden;
	-webkit-transform: translateZ(0) scale(1.0, 1.0);
	transform: translateZ(0);
}
.dj-megamenu-<?php echo $params->get('theme') ?>.dj-megamenu-sticky {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: auto;
}
.dj-megamenu-<?php echo $params->get('theme') ?> .dj-stickylogo {
	position: absolute;
	z-index: 550;
}
.dj-megamenu-<?php echo $params->get('theme') ?> .dj-stickylogo img {
	max-height: 100%;
	width: auto;
}
.dj-megamenu-<?php echo $params->get('theme') ?> .dj-stickylogo.dj-align-center {
	position: static;
	text-align: center;
	margin: 10px 0;
}
.dj-megamenu-<?php echo $params->get('theme') ?> .dj-stickylogo.dj-align-left {
	left: 20px;
	max-height: 50px;
	margin: 5px 0;
}
.dj-megamenu-<?php echo $params->get('theme') ?> .dj-stickylogo.dj-align-right {
	right: 20px;
	max-height: 50px;
	margin: 5px 0;
}

/* full width submenu */
.dj-megamenu-<?php echo $params->get('theme') ?> li.dj-up.fullsub {
	position: static !important;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li.dj-up.fullsub a.dj-up_a {
	position: relative;
}
.dj-megamenu-<?php echo $params->get('theme') ?> .djsubrow_separator {
	clear:both;
	border-bottom: 1px solid <?php echo adjustBrightness($params->get('megasubbg'), 1.2); ?>;
	padding: 0 0 20px;
	margin: 0 0 20px;
}

/* vertical menu orientation */
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu {
	height: auto;
	max-width: 360px;
	z-index: 400;
}
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li.dj-up {
	float: none;
}
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li a.dj-up_a {
    float: none;
    height: auto;
    padding-top: 15px;
    padding-bottom: 15px;
    border-left: 0px;
    border-right: 0px;
    border-bottom: 1px solid <?php echo adjustBrightness($params->get('megabg'), 1.2); ?>;
    border-top: 1px solid <?php echo adjustBrightness($params->get('megabg'), 0.8); ?>;
}
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li.first a.dj-up_a {
	border-top: 0;
}
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li:last-child a.dj-up_a {
	border-bottom: 0;
}
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li a.dj-up_a small.subtitle {
	margin-top: 0;
}
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li a.dj-up_a span.dj-drop {
	display: table-row;
}
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li a.dj-up_a span {
	float: none;
	height: auto;
	line-height: 20px;
}
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li a.dj-up_a span em {
	display: table-cell;
	vertical-align: middle;
}
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li a.dj-up_a span span.image-title {
	display: table-cell;
	vertical-align: middle;
	padding: 0 15px !important;
	margin: 0;
}
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li a.dj-up_a .arrow:before {
	content: "\f105";
}
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li:hover div.dj-subwrap,
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li.hover div.dj-subwrap,
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li:hover div.dj-subwrap.open-right,
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li.hover div.dj-subwrap.open-right {
	right: auto !important;
	left: 100% !important;
	top: 0;
}
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li:hover div.dj-subwrap.open-left,
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li.hover div.dj-subwrap.open-left {
	left: auto !important;
	right: 100% !important;
}

<?php if($direction=='rtl') {
/* DJ-MegaMenu RTL styles */ ?>
.dj-megamenu-<?php echo $params->get('theme') ?> li.dj-up {
    float: right;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a {
    float: right;
    border-left: 1px solid <?php echo adjustBrightness($params->get('megabg'), 0.8); ?>;
    border-right: 1px solid <?php echo adjustBrightness($params->get('megabg'), 1.2); ?>;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li.first a.dj-up_a {
	border-left-width: 1px;
	border-right-width: 0;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li:last-child a.dj-up_a {
	border-right-width: 1px;
	border-left-width: 0;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a span {
    float: right;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a span.dj-drop {
    padding: 0 0 0 20px !important;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a .arrow {
	right: auto;
	left: 16px;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li a.dj-up_a span span.image-title {
	margin: 0 15px 0 0;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li:hover a.dj-up_a,
.dj-megamenu-<?php echo $params->get('theme') ?> li.hover a.dj-up_a, 
.dj-megamenu-<?php echo $params->get('theme') ?> li.active a.dj-up_a {
    border-left-color: <?php echo adjustBrightness($params->get('megabg_a'), 0.8); ?>;
    border-right-color: <?php echo adjustBrightness($params->get('megabg_a'), 1.2); ?>;
}

/* Show first level submenu */
.dj-megamenu-<?php echo $params->get('theme') ?> li:hover div.dj-subwrap,
.dj-megamenu-<?php echo $params->get('theme') ?> li.hover div.dj-subwrap {
	left: auto;
	right: 0;
}
/* Show higher level submenus */
.dj-megamenu-<?php echo $params->get('theme') ?> li:hover div.dj-subwrap li:hover > div.dj-subwrap,
.dj-megamenu-<?php echo $params->get('theme') ?> li.hover div.dj-subwrap li.hover > div.dj-subwrap {
    left: auto;
    right: 100%;
    margin: 0 10px 0 0;
}

/* Submenu elements styles - drop down */
.dj-megamenu-<?php echo $params->get('theme') ?> li div.dj-subcol {
	float: right;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li > a img,
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li > a .fa, 
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li > a [class^="icon-"] {
	float: right;
	margin: 0 0 8px 15px;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li.parent > a .arrow {
    right: auto;
    left: 6px;
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li.parent:not(.subtree) > a .arrow:before {
	content: "\f104";
}
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-submenu > li.parent:not(.subtree) > a:hover .arrow {
	right: auto;
	left: 4px;
}

/* Submenu elements styles - tree */
.dj-megamenu-<?php echo $params->get('theme') ?> li ul.dj-subtree {
    margin: 5px 25px 5px 0 !important;
}

/* vertical menu orientation */
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li a.dj-up_a .arrow:before {
	content: "\f104";
}
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li:hover div.dj-subwrap,
.dj-megamenu-<?php echo $params->get('theme') ?>.verticalMenu li.hover div.dj-subwrap {
	left: auto !important;
	right: 100% !important;
}
<?php } ?>