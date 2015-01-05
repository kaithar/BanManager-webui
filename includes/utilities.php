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
    
function redirect($location, $code = '302') {
    switch($code) {
        case '301';
            header("HTTP/1.1 301 Moved Permanently");
        break;
        case '303';
            header("HTTP/1.1 303 See Other");
        break;
        case '404';
            header('HTTP/1.1 404 Not Found');
        break;
    }
    //remove any &amp; in the url to prevent any problems
    $location = str_replace('&amp;', '&', $location);
    header("Location: $location");
    //kill the script from running and output a link for browsers which enable turning off header redirects *cough Opera cough* :P
    exit('<a href="'.$location.'">If you were not redirected automatically please click here</a>');
}


function errors($message) {
    echo '
        <div class="container">
        <div id="error" class="alert alert-danger">
            <button class="close" data-dismiss="alert">&times;</button>
            <h1>Uh oh, we\'ve found an error.</h1>';
    if(is_array($message)) {
        foreach($message as $e)
            echo $e;
    } else {
        echo $message;
    }
        echo '</div></div>';
}
/*
 * Convert seconds to human readable text.
 * http://csl.sublevel3.org/php-secs-to-human-text/
 */
function secs_to_h($secs) {
    $units = array(
        "week"   => 7*24*3600,
        "day"    =>   24*3600,
        "hour"   =>      3600,
        "minute" =>        60,
        "second" =>         1,
    );

    // specifically handle zero
    if ( $secs == 0 )
        return "0 seconds";
    $s = '';
    foreach ( $units as $name => $divisor ) {
        if ( $quot = intval($secs / $divisor) ) {
            $s .= "$quot $name";
            $s .= (abs($quot) > 1 ? "s" : "") . ", ";
            $secs -= $quot * $divisor;
        }
    }
    return substr($s, 0, -2);
}

function secs_to_hmini($secs) {
    $units = array(
        "w"   => 7*24*3600,
        "d"    =>   24*3600,
        "h"   =>      3600,
        "m" =>        60,
        "s" =>         1,
    );

    // specifically handle zero
    if ( $secs == 0 )
        return "0s";
    $s = '';
    foreach ( $units as $name => $divisor ) {
        if ( $quot = intval($secs / $divisor) ) {
            if($quot > 0) {
                $s .= $quot.$name;
                $s .= (abs($quot) > 1 && $name == 's' ? 's' : ''). ' ';
            }
            $secs -= $quot * $divisor;
        }
    }
    return substr($s, 0, -2);
}

function is_alphanum($string) {
    if(function_exists('ctype_alnum'))
        return ctype_alnum($string);
    else
        return (preg_match("~^[a-z0-9]*$~iD", $string) !== 0 ? true : false);
}

function is_alphanumdash($string) {
    return (preg_match("~^[a-z0-9_-]*$~iD", $string) !== 0 ? true : false);
}
?>
