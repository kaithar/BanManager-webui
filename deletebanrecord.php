<?php
/*  BanManagement ? 2012, a web interface for the Bukkit plugin BanManager
    by James Mortemore of http://www.frostcast.net
	is licenced under a Creative Commons
	Attribution-NonCommercial-ShareAlike 2.0 UK: England & Wales.
	Permissions beyond the scope of this licence 
	may be available at http://creativecommons.org/licenses/by-nc-sa/2.0/uk/.
	Additional licence terms at https://raw.github.com/confuser/Ban-Management/master/banmanagement/licence.txt
*/
if (!defined('INTERNAL'))
    die("Don't call me directly!");

if(!isset($_SESSION['admin']) || (isset($_SESSION['admin']) && !$_SESSION['admin']))
	die('Hacking attempt');
else if(!isset($_GET['authid']) || (isset($_GET['authid']) && $_GET['authid'] != sha1($settings['password'])))
	die('Hacking attempt');
else if(!isset($_GET['server']) || !is_numeric($_GET['server']))
	die('Hacking attempt');
else if(!isset($settings['servers'][$_GET['server']]))
	die('Hacking attempt');
else if(!isset($_GET['id']) || !is_numeric($_GET['id']))
	die('Hacking attempt');
else {
	// Get the server details
	$server = $settings['servers'][$_GET['server']];
	
	if(!connect($server))
		$error = 'Unable to connect to database';
	else {
		$pastBans = mysql_query("SELECT ban_record_id FROM ".$server['recordTable']." WHERE ban_record_id = '".$_GET['id']."'");
		
		if(mysql_num_rows($pastBans) == 0)
			$error = 'That record does not exist';
		else {
			mysql_query("DELETE FROM ".$server['recordTable']." WHERE ban_record_id = '".$_GET['id']."'");
			
			// Clear the cache
			clearCache($_GET['server'].'/players');
			
			$array['success'] = 'true';
		}
	}
}
if(isset($error))
	$array['error'] = $error;
echo json_encode($array);
?>