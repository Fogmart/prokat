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

/* mobile menu open buttons */
.dj-megamenu-select-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn,
.dj-megamenu-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn,
.dj-megamenu-accordion-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn {
	display: inline-block;
	cursor: pointer;
    margin: 0 auto;
    width: auto;
	height: 42px;
	font-size: 42px;
	line-height: 1;
	padding: 3px 8px;
	background: <?php echo $params->get('mobilebtnbg'); ?>;
	color: <?php echo $params->get('mobilebtncolor'); ?>;
	text-align: center;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	-webkit-transition: background-color 0.2s ease-out, color 0.2s ease-out;
	transition: background-color 0.2s ease-out, color 0.2s ease-out;
}
.dj-megamenu-select-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn span + span,
.dj-megamenu-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn span + span,
.dj-megamenu-accordion-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn span + span {
	margin-left: 12px;
}
.dj-megamenu-select-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:focus,
.dj-megamenu-select-<?php echo $params->get('mobiletheme') ?>:hover .dj-mobile-open-btn,
.dj-megamenu-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:hover,
.dj-megamenu-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:focus,
.dj-megamenu-accordion-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:hover,
.dj-megamenu-accordion-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:focus {
	background: <?php echo $params->get('mobilebtncolor'); ?>;
	color: <?php echo $params->get('mobilebtnbg'); ?>;
	text-decoration: none;
}

/* select menu general styles */
.dj-megamenu-select-<?php echo $params->get('mobiletheme') ?>.select-input select {
	margin:10px;
	padding:5px;
	max-width:95%;
	height:auto;
	font-size:1.5em;
	color: #434343;
}
.dj-megamenu-select-<?php echo $params->get('mobiletheme') ?>.select-input .dj-mobile-open-btn {
	display: none !important;
}
.dj-megamenu-select-<?php echo $params->get('mobiletheme') ?>.select-button {
	position: relative;
	margin: 0 auto;
    width: auto;
	overflow: hidden;
}
.dj-megamenu-select-<?php echo $params->get('mobiletheme') ?>.select-button select {
	position: absolute;
	top: 0;
	left: 0;
	background: transparent;
    border: 0;
    margin: 0;
    cursor: pointer;
    height: 48px;
    width: auto;
    max-width: 9999px;
    outline: none;
    text-indent: 9999px;
    font-size:1.5em;
    color: #434343;
    box-shadow: none;
}

/* offcanvas menu general styles */
.dj-offcanvas-<?php echo $params->get('mobiletheme') ?> {
	background: <?php echo $params->get('mobilebg'); ?>;
	color: <?php echo $params->get('mobilemodcolor'); ?>;
}
.dj-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-offcanvas-top {
	background: <?php echo adjustBrightness($params->get('mobilebg'), 1.1); ?>;
	text-align: right;
}
.dj-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-offcanvas-close-btn {
	display: inline-block;
	font-size: 20px;
	line-height: 1;
	color: <?php echo $params->get('mobilecolor'); ?>;
	cursor: pointer;
	padding: 10px 12px;
}
.dj-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-offcanvas-logo {
	padding: 15px;
	text-align: center;
}
.dj-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-offcanvas-logo img {
	max-width: 100%;
}
.dj-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-offcanvas-content {
	padding: 0 15px 15px;
}
.dj-offcanvas-modules {
	padding: 15px;
}

/* accordion menu general styles */
.dj-megamenu-accordion-<?php echo $params->get('mobiletheme') ?> {
	position: relative;
	text-align: center;
}
.dj-megamenu-accordion-<?php echo $params->get('mobiletheme') ?>.dj-align-left {
	text-align: left;
}
.dj-megamenu-accordion-<?php echo $params->get('mobiletheme') ?>.dj-align-right {
	text-align: right;
}
.dj-pos-absolute .dj-accordion-<?php echo $params->get('mobiletheme') ?> {
	position: absolute;
	top: 42px;	
	z-index: 999;
}
.dj-pos-absolute.dj-align-left .dj-accordion-<?php echo $params->get('mobiletheme') ?> {
	left: 0;
} 
.dj-pos-absolute.dj-align-right .dj-accordion-<?php echo $params->get('mobiletheme') ?> {
	right: 0;
}
.dj-pos-absolute.dj-align-center .dj-accordion-<?php echo $params->get('mobiletheme') ?> {
	left: 50%;
	margin-left: -150px;
}
.dj-pos-absolute .dj-accordion-<?php echo $params->get('mobiletheme') ?> .dj-accordion-in {
	width: 300px;
}
.dj-accordion-<?php echo $params->get('mobiletheme') ?> .dj-accordion-in {
	display: none;
	margin: 10px 0;
	padding: 0 15px;
	background: <?php echo $params->get('mobilebg'); ?>;
	color: <?php echo $params->get('mobilemodcolor'); ?>;
	-webkit-box-sizing: border-box;
	box-sizing: border-box;
}

/* offcanvas and accordion menu */
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> {
	margin: 0 -15px;
	padding: 0;
	text-align: left;
}
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem {
	list-style: none outside;
	position: relative;
	margin: 0;
	padding: 0;
	background: none;
}
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem > a {
	display: block;
	font-size: 14px;
	line-height: 1;
	background: <?php echo $params->get('mobilebg'); ?>;
    color: <?php echo $params->get('mobilecolor'); ?>;
    text-decoration: none;
    padding: 20px 15px;
    cursor: pointer;
    border-top: 1px solid <?php echo adjustBrightness($params->get('mobilebg'), 1.2); ?>;
    -webkit-transition: background-color 0.2s ease-out;
	transition: background-color 0.2s ease-out;
}
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem:hover > a,
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem.active > a {
	background: <?php echo $params->get('mobilebg_a'); ?>;
    color: <?php echo $params->get('mobilecolor_a'); ?>;
}

ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem > a .subtitle {
	font-size: 0.85em;
	color: <?php echo $params->get('mobilestcolor'); ?>;
}
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem:hover > a .subtitle,
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem.active > a .subtitle {
	color: <?php echo $params->get('mobilestcolor_a'); ?>;
}

ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem > a img,
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem > a i {
	vertical-align: middle;
	margin: 0 10px 0 0;
}
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem > a.withsubtitle img,
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem > a.withsubtitle i {
	float: left;
}
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem > a span.image-title {
}

ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem.parent > a {
	padding-right: 35px;
}
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem.parent > a span.toggler {
	display: inline-block;
    font-family: FontAwesome;
	position: absolute;
	right: 0;
	top: 5px;
	padding: 16px 20px; /* make the button a little bigger */
	font-size: 12px;
	line-height: 14px;
	font-style: normal;
	font-weight: normal;
}
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem.parent > a span.toggler:before {
	content: "";
}
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem.parent.active > a span.toggler:before {
	content: "";
}

ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem > ul {
	display: block;
	max-height: 0px;
	overflow: auto;
	margin: 0;
	padding: 0;
}
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem.active > ul {
	max-height: 1000px;
	-webkit-transition: max-height 0.3s ease-in;
	transition: max-height 0.3s ease-in;
}

/* 2nd level */
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem > a {
	background: <?php echo $params->get('mobilesubbg'); ?>;
    color: <?php echo $params->get('mobilesubcolor'); ?>;
    border-top: 1px solid <?php echo adjustBrightness($params->get('mobilesubbg'), 1.2); ?>;
    padding-left: 30px;
}
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem:hover > a,
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem.active > a {
	background: <?php echo $params->get('mobilesubbg_a'); ?>;
    color: <?php echo $params->get('mobilesubcolor_a'); ?>;
}

ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem > a .subtitle {
	color: <?php echo $params->get('mobilesubstcolor'); ?>;
}
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem:hover > a .subtitle,
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem.active > a .subtitle {
	color: <?php echo $params->get('mobilesubstcolor_a'); ?>;
}

ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem.parent > a span.toggler {
	font-size: 11px;
}
<?php 
$bg = adjustBrightness($params->get('mobilesubbg'), 0.9);
$bga = adjustBrightness($params->get('mobilesubbg_a'), 0.9);
?>
/* 3rd level */
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul ul li.dj-mobileitem > a {
    background: <?php echo $bg; ?>;
    border-top: 1px solid <?php echo adjustBrightness($bg, 1.2); ?>;
    padding-left: 45px;
}
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul ul li.dj-mobileitem:hover > a,
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul ul li.dj-mobileitem.active > a {
	background: <?php echo $bga; ?>;
}
<?php 
$bg = adjustBrightness($bg, 0.9);
$bga = adjustBrightness($bga, 0.9);
?>
/* 4th level */
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul ul ul li.dj-mobileitem > a {
    background: <?php echo $bg; ?>;
    border-top: 1px solid <?php echo adjustBrightness($bg, 1.2); ?>;
    padding-left: 60px;
}
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul ul ul li.dj-mobileitem:hover > a,
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul ul ul li.dj-mobileitem.active > a {
	background: <?php echo $bga; ?>;
}
<?php 
$bg = adjustBrightness($bg, 0.9);
?>
/* 5th level */
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul ul ul ul li.dj-mobileitem > a {
	background: <?php echo $bg; ?>;
}

/* mobile modules */
ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> .modules-wrap {
	padding: 5px 15px;
}
