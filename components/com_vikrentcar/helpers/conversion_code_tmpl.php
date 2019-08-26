<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

// What is this file supposed to do?
// If you are using some external services to convert your tracking code and to monitor your sales, then you may require
// to add some JavaScript code for converting the actions and behaviors of your customers on your site.
// This code will be executed only on the "thank you" page, which is the order details page displayed for the first time after a successfull payment.
// Google Analytics, Adwords or other tracking/conversion services should provide you with the necessary code to add.

// Use this template file ONLY if you need to track your sales with a conversion code and if you are in possess of the code. Leave it as is otherwise.

// Keep the following line of PHP code for security reasons
defined('_JEXEC') OR die('Restricted Area');

// You can perform some additional checks via PHP code if necessary, for example to access the ID of the order or the total amount.
// You can easily access the name of the current View by using the code below:
// $order_total = $ord['order_total'];
// The variable $order_total will contain the actual amount of the rental order.
// Need to access other variables? Just debug the content of the request ($_REQUEST) or the content of the order array ($ord).

// Add your JavaScript/HTML Conversion Code after (under) the PHP closing tag, which is at the line immediately below:
?>