<?php
/*  BanManagement © 2012, a web interface for the Bukkit plugin BanManager
    by James Mortemore of http://www.frostcast.net
	is licenced under a Creative Commons
	Attribution-NonCommercial-ShareAlike 2.0 UK: England & Wales.
	Permissions beyond the scope of this licence 
	may be available at http://creativecommons.org/licenses/by-nc-sa/2.0/uk/.
	Additional licence terms at https://raw.github.com/confuser/Ban-Management/master/banmanagement/licence.txt
*/

define('INTERNAL', true);
session_name("BanManagement");
session_start();
ob_start();
error_reporting(0); // Disable error reports for security

if(!isset($_SESSION['initiated'])) {
    session_regenerate_id();
    $_SESSION['initiated'] = true;
}

define('IN_PATH', realpath('.') . '/'); // This allows us to use absolute urls

require_once('includes/compat.php');
require_once('includes/utilities.php');
require_once('includes/cache.php');

function connect($server) {
	global $settings;
	
	if(!mysql_connect($server['host'], $server['username'], $server['password']))
		return false;
	else if(!mysql_select_db($server['database']))
		return false;
	$settings['last_connection'] = $server;
	
	if(isset($settings['utf8']) && $settings['utf8'])
		mysql_query("SET NAMES 'utf8'");
	
	return true;
}

require_once('includes/searchBy.php');

$actions = array(
	'addserver',
	'admin',
	'deleteban',
	'deletebanrecord',
	'deletecache',
	'deleteipban',
	'deleteipbanrecord',
	'deletekickrecord',
	'deletemute',
	'deletemuterecord',
	'deleteserver',
	'deletewarning',
	'editserver',
	'logout',
	'reorderserver',
	'searchplayer',
	'searchip',
	'servers',
	'updateban',
	'updateipban',
	'updatemute',
	'updatesettings',
	'viewip',
	'viewplayer'
);
if(file_exists('settings.php')){
	include('settings.php');
}
else{
	errors('Unable to located the settings.php file. If you haven\'t renamed settingsRename.php yet, please go do that now to make Ban Management functional.');
}

// IE8 frame busting, well thats the only good thing it has :P (Now supported by Firefox woot)
if((isset($settings['iframe_protection']) && $settings['iframe_protection']) || !isset($settings['iframe_protection']))
	header('X-FRAME-OPTIONS: SAMEORIGIN');
	
$settings['servers'] = unserialize($settings['servers']);

// Check if APC is enabled to use that instead of file cache
$settings['apc_enabled'] = $apc_status;

if(!isset($_GET['ajax']) || (isset($_GET['ajax']) && !$_GET['ajax']))
	include('header.php');

if(isset($_GET['action']) && in_array($_GET['action'], $actions))
	include($_GET['action'].'.php');
else if(!isset($_GET['action']))
	include('home.php');
else
	echo 'Action not found, possible hacking attempt';
if(!isset($_GET['ajax']) || (isset($_GET['ajax']) && !$_GET['ajax']))
	include('footer.php');
?>
