<?php
if (!defined('INTERNAL'))
    die("Don't call me directly!");

/**
 * Snippet from php.net by bohwaz
 * below function kills register globals
 * to remove any possible security threats if it is on
 */
if(ini_get('register_globals')) {
    function unregister_globals() {
        foreach(func_get_args() as $name) {
            foreach($GLOBALS[$name] as $key => $value) {
                if(isset($GLOBALS[$key]))
                    unset($GLOBALS[$key]);
            }
        }
    }
    unregister_globals('_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES', '_SESSION');
}

// REQUEST_URI fix for hosts using IIS (Windows)
if(!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
    if($_SERVER['QUERY_STRING']) {
        $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
    }
}

// mysql_real_escape_string that doesn't require an active database connection
function mysql_escape_mimic($inp) {
    if(is_array($inp))
        return array_map(__METHOD__, $inp);

    if(!empty($inp) && is_string($inp))
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
 
    return $inp;
}

/** 
 * Encodes HTML within below globals, takes into account magic quotes.
 * Note: $_SERVER is not sanitised, be aware of this when using it.
 * Why repeat it twice? Checking magic quotes everytime in a loop is slow and so is any additional if statements ;)
 */
$in = array(&$_GET, &$_POST);
if(get_magic_quotes_gpc()) {
    while(list($k, $v) = each($in)) {
        foreach($v as $key => $val) {
            if(!is_array($val)) 
                $in[$k][mysql_escape_mimic(htmlspecialchars(stripslashes($key), ENT_QUOTES))] = mysql_escape_mimic(htmlspecialchars(stripslashes($val), ENT_QUOTES));
            else
                $in[] =& $in[$k][$key];
        }
    }
} else {
    while(list($k, $v) = each($in)) {
        foreach($v as $key => $val) {
            if(!is_array($val))
                $in[$k][mysql_escape_mimic(htmlspecialchars($key, ENT_QUOTES))] = mysql_escape_mimic(htmlspecialchars($val, ENT_QUOTES));
            else
                $in[] =& $in[$k][$key];
        }
    }
}

if(!function_exists('json_encode')) {
    function json_encode($a = false) {
        /**
        * This function encodes a PHP array into JSON
        * Function from php.net by Steve
        * Returns: @JSON
        */
        if(is_null($a))
            return 'null';
        if($a === false)
            return 'false';
        if($a === true)
            return 'true';
        if(is_scalar($a)) {
            if(is_float($a))
                return floatval(str_replace(",", ".", strval($a))); // Always use "." for floats.
            if(is_string($a)) {
                static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
                return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
            } else
                return $a;
        }
        $isList = true;
        for($i = 0, reset($a); $i < count($a); $i++, next($a)) {
            if(key($a) !== $i) {
                $isList = false;
                break;
            }
        }
        $result = array();
        if($isList) {
            foreach ($a as $v)
                $result[] = json_encode($v);
            return '[' . join(',', $result) . ']';
        } else {
            foreach ($a as $k => $v)
                $result[] = json_encode($k).':'.json_encode($v);
            return '{' . join(',', $result) . '}';
        }
    }
}
?>
