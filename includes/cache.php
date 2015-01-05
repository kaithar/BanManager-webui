<?php
/*  BanManagement © 2012, a web interface for the Bukkit plugin BanManager
    by James Mortemore of http://www.frostcast.net
    is licenced under a Creative Commons
    Attribution-NonCommercial-ShareAlike 2.0 UK: England & Wales.
    Permissions beyond the scope of this licence 
    may be available at http://creativecommons.org/licenses/by-nc-sa/2.0/uk/.
    Additional licence terms at https://raw.github.com/confuser/Ban-Management/master/banmanagement/licence.txt
*/

if (!defined('INTERNAL'))
    die("Don't call me directly!");

$apc_status = extension_loaded('apc') && ini_get('apc.enabled');
if($apc_status) {
    if(!function_exists('apc_exists')) {
        if(version_compare(phpversion('apc'), '3.1.4', '<')) {
            function apc_exists($key) { 
                return (bool) apc_fetch($key);
            }
        }
    }
}


function cache($query, $time, $folder = '', $server = array(), $name = '') {
    global $settings;
    $md5 = md5($query);
    $file = '';
    if ($folder != '') $file = $folder.'/';
    if (!empty($name))
        $file .= $name;
    else
        $file .= $md5;

    if($settings['apc_enabled']) {
        if(apc_exists($file))
            return apc_fetch($file);
        else {
            return createCache($query, $server, $file, $time);
        }
    } else {
        $file = IN_PATH.'cache/'.$file.'.php';
        if($folder != '' && !is_dir(IN_PATH.'cache/'.$folder))
            mkdir(IN_PATH.'cache/'.$folder, 0777, true);
        if(file_exists($file)) {
            if(time() - filemtime($file) > $time) {
                // Needs recache
                return createCache($query, $server, $file); // Return the fresh data
            } else {
                // Serve the cache
                return unserialize(file_get_contents($file, NULL, NULL, 16));
            }
        } else {
            // Cache needs creating
            return createCache($query, $server, $file); // Return the fresh data
        }
    }
}

function createCache($query, $server, $file, $time = 0) {
    global $settings;

    if(!empty($server)) {
        if(isset($settings['last_connection'])) {
            $diff = array_diff($settings['last_connection'], $server);
            if(!empty($diff))
                connect($server);
        } else
            connect($server);
    }
    $sql = mysql_query($query);
    $data = array();
    if(mysql_num_rows($sql) > 0) {
        while($fetch = mysql_fetch_array($sql)) // Loop through the data
            array_push($data, $fetch);
    }
    // Check if its only one row
    if(count($data) == 1)
        $data = $data[0];
    // Now save it
    if(!$settings['apc_enabled'])
        file_put_contents($file, "<?php die(); ?>\n".serialize($data)); // Create the file
    else
        apc_store($file, $data, $time);
    return $data; // Return the fresh data
}

function rglob($pattern='*', $flags = 0, $path='') {
    $paths = glob($path.'*', GLOB_MARK|GLOB_ONLYDIR|GLOB_NOSORT);
    $files = glob($path.$pattern, $flags);
    if($path !== false && $files !== false) {
        foreach($paths as $path)
            $files = array_merge($files, rglob($pattern, $flags, $path));
    } else
        $files = array();
    return $files;
}

function clearCache($folder = '', $olderThan = 0) {
    global $settings;

    if($settings['apc_enabled']) {
        apc_delete($folder);
        return;
    }
    
    $timeNow = time();
    if(empty($folder))
        $files = rglob('*.php', null, IN_PATH.'cache');
    else
        $files = rglob('*.php', null, IN_PATH.'cache/'.$folder);
    foreach($files as $file) {
        if($olderThan == 0)
            unlink($file);
        else if($timeNow - filemtime($file) > $olderThan) {
            unlink($file);
        }
    }
}
?>
