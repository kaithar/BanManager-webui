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

function searchPlayers($search, $serverID, $server, $sortByCol = 'name', $sortBy = 'ASC', $past = true) {

    switch($sortByCol) {
        default:
        case 0: // Name
            $sort['bans'] = $sort['banrecords'] = 'banned';
            $sort['mutes'] = $sort['muterecords'] = 'muted';
            $sort['kicks'] = 'kicked';
            $sort['warnings'] = 'warned';
        break;
        case 1: // Type
            $sort['bans'] = $sort['banrecords'] = 'banned';
            $sort['mutes'] = $sort['muterecords'] = 'muted';
            $sort['kicks'] = 'kicked';
            $sort['warnings'] = 'warned';
        break;
        case 2: // By
            $sort['bans'] = $sort['banrecords'] = 'banned_by';
            $sort['mutes'] = $sort['muterecords'] = 'muted_by';
            $sort['kicks'] = 'kicked_by';
            $sort['warnings'] = 'warned_by';
        break;
        case 3: // Reason
            $sort['bans'] = $sort['banrecords'] = 'ban_reason';
            $sort['mutes'] = $sort['muterecords'] = 'mute_reason';
            $sort['kicks'] = 'kick_reason';
            $sort['warnings'] = 'warn_reason';
        break;
        case 4: // Expires
            $sort['bans'] = 'ban_expires_on';
            $sort['banrecords'] = 'ban_expired_on';
            $sort['mutes'] = 'mute_expires_on';
            $sort['muterecords'] = 'mute_expired_on';
            $sort['kicks'] = 'kick_id';
            $sort['warnings'] = 'warn_id';
        break;
        case 5: // Date
            $sort['bans'] = $sort['banrecords'] = 'ban_time';
            $sort['mutes'] = $sort['muterecords'] = 'mute_time';
            $sort['kicks'] = 'kick_time';
            $sort['warnings'] = 'warn_time';
        break;
    }

    // Found results
    $found = array();

    if((isset($settings['player_current_ban']) && $settings['player_current_ban']) || !isset($settings['player_current_ban'])) {
        // Current Bans
        $result = cache("SELECT banned, banned_by, ban_reason, ban_time, ban_expires_on FROM ".$server['bansTable']." WHERE banned LIKE '%".$search."%' ORDER BY ".$sort['bans']." $sortBy", 300, $serverID.'/search', $server);
        if(isset($result[0]) && !is_array($result[0]) && !empty($result[0]))
            $result = array($result);
        
        if(count($result) > 0) {
            foreach($result as $r)
                $found[$r['banned']] = array('by' => $r['banned_by'], 'reason' => $r['ban_reason'], 'type' => 'Ban', 'time' => $r['ban_time'], 'expires' => $r['ban_expires_on']);
        }
    }
    
    if((isset($settings['player_previous_bans']) && $settings['player_previous_bans']) || !isset($settings['player_previous_bans'])) {
        if($past) {
            // Past Bans
            $result = cache("SELECT banned, banned_by, ban_reason, ban_time, ban_expired_on FROM ".$server['recordTable']." WHERE banned LIKE '%".$search."%' ORDER BY ".$sort['banrecords']." $sortBy", 300, $serverID.'/search', $server);
            if(isset($result[0]) && !is_array($result[0]) && !empty($result[0]))
                $result = array($result);
            
            if(count($result) > 0) {
                foreach($result as $r) {
                    if(!isset($found[$r['banned']]))
                        $found[$r['banned']] = array('by' => $r['banned_by'], 'reason' => $r['ban_reason'], 'type' => 'Ban', 'time' => $r['ban_time'], 'expires' => $r['ban_expired_on'], 'past' => true);
                    else if($found[$r['banned']]['time'] < $r['ban_time'])
                        $found[$r['banned']] = array('by' => $r['banned_by'], 'reason' => $r['ban_reason'], 'type' => 'Ban', 'time' => $r['ban_time'], 'expires' => $r['ban_expired_on'], 'past' => true);
                }
            }
        }
    }
    
    if((isset($settings['player_current_mute']) && $settings['player_current_mute']) || !isset($settings['player_current_mute'])) {
        // Current Mutes
        $result = cache("SELECT muted, muted_by, mute_reason, mute_time, mute_expires_on FROM ".$server['mutesTable']." WHERE muted LIKE '%".$search."%' ORDER BY ".$sort['mutes']." $sortBy", 300, $serverID.'/search', $server);
        if(isset($result[0]) && !is_array($result[0]) && !empty($result[0]))
            $result = array($result);
        
        if(count($result) > 0) {
            foreach($result as $r) {
                if(!isset($found[$r['muted']]))
                    $found[$r['muted']] = array('by' => $r['muted_by'], 'reason' => $r['mute_reason'], 'type' => 'Mute', 'time' => $r['mute_time'], 'expires' => $r['mute_expires_on']);
            }
        }
    }
    
    if($past) {
        if((isset($settings['player_previous_mutes']) && $settings['player_previous_mutes']) || !isset($settings['player_previous_mutes'])) {
            // Past Mutes
            $result = cache("SELECT muted, muted_by, mute_reason, mute_time, mute_expired_on FROM ".$server['mutesRecordTable']." WHERE muted LIKE '%".$search."%' ORDER BY ".$sort['muterecords']." $sortBy", 300, $serverID.'/search', $server);
            if(isset($result[0]) && !is_array($result[0]) && !empty($result[0]))
                $result = array($result);
            
            if(count($result) > 0) {
                foreach($result as $r) {
                    if(!isset($found[$r['muted']]))
                        $found[$r['muted']] = array('by' => $r['muted_by'], 'reason' => $r['mute_reason'], 'type' => 'Mute', 'time' => $r['mute_time'], 'expires' => $r['mute_expired_on'], 'past' => true);
                    else if($found[$r['muted']]['time'] < $r['mute_time'])
                        $found[$r['muted']] = array('by' => $r['muted_by'], 'reason' => $r['mute_reason'], 'type' => 'Mute', 'time' => $r['mute_time'], 'expires' => $r['mute_expired_on'], 'past' => true);
                }
            }
        }

        if((isset($settings['player_kicks']) && $settings['player_kicks']) || !isset($settings['player_kicks'])) {      
            // Kicks
            $result = cache("SELECT kicked, kicked_by, kick_reason, kick_time FROM ".$server['kicksTable']." WHERE kicked LIKE '%".$search."%' ORDER BY ".$sort['kicks']." $sortBy", 300, $serverID.'/search', $server);
            if(isset($result[0]) && !is_array($result[0]) && !empty($result[0]))
                $result = array($result);
                
            if(count($result) > 0) {
                foreach($result as $r) {
                    if(!isset($found[$r['kicked']]))
                        $found[$r['kicked']] = array('by' => $r['kicked_by'], 'reason' => $r['kick_reason'], 'type' => 'Kick', 'time' => $r['kick_time'], 'expires' => 0, 'past' => true);
                    else if($found[$r['kicked']]['time'] < $r['kick_time'])
                        $found[$r['kicked']] = array('by' => $r['kicked_by'], 'reason' => $r['kick_reason'], 'type' => 'Kick', 'time' => $r['kick_time'], 'expires' => 0, 'past' => true);
                }
            }
        }
    }
    
    if((isset($settings['player_warnings']) && $settings['player_warnings']) || !isset($settings['player_warnings'])) {
        // Warnings
        $result = cache("SELECT warned, warned_by, warn_reason, warn_time FROM ".$server['warningsTable']." WHERE warned LIKE '%".$search."%' ORDER BY ".$sort['warnings']." $sortBy", 300, $serverID.'/search', $server);
        if(isset($result[0]) && !is_array($result[0]) && !empty($result[0]))
            $result = array($result);
        
        if(count($result) > 0) {
            foreach($result as $r) {
                if(!isset($found[$r['warned']]))
                    $found[$r['warned']] = array('by' => $r['warned_by'], 'reason' => $r['warn_reason'], 'type' => 'Warning', 'time' => $r['warn_time'], 'expires' => 0, 'past' => true);
                else if($found[$r['warned']]['time'] < $r['warn_time'])
                    $found[$r['warned']] = array('by' => $r['warned_by'], 'reason' => $r['warn_reason'], 'type' => 'Warning', 'time' => $r['warn_time'], 'expires' => 0, 'past' => true);
            }
        }
    }
    
    if(count($found) == 0)
        return false;
    else if(count($found) == 1) {
        // Redirect!
        $p = array_keys($found);
        redirect('index.php?action=viewplayer&player='.$p[0].'&server='.$serverID);
    } else {
        // STUFF
        return $found;
    }
}

function searchIps($search, $serverID, $server, $sortByCol = 'name', $sortBy = 'ASC', $past = true) {
    $found = array();

    switch($sortByCol) {
        default:
        case 0: // Name
            $sort['bans'] = $sort['banrecords'] = 'banned';
        break;
        case 1: // Type
            $sortByType = true;
            $sort['bans'] = $sort['banrecords'] = 'banned';
        break;
        case 2: // By
            $sort['bans'] = $sort['banrecords'] = 'banned_by';
        break;
        case 3: // Reason
            $sort['bans'] = $sort['banrecords'] = 'ban_reason';
        break;
        case 4: // Expires
            $sort['bans'] = 'ban_expires_on';
            $sort['banrecords'] = 'ban_expired_on';
        break;
        case 5: // Date
            $sort['bans'] = $sort['banrecords'] = 'ban_time';
        break;
    }

    // Found results
    $found = array();

    // Current Bans
    $result = cache("SELECT banned, banned_by, ban_reason, ban_time, ban_expires_on FROM ".$server['ipTable']." WHERE banned LIKE '%".$search."%' ORDER BY ".$sort['bans']." $sortBy", 300, $serverID.'/search', $server);
    if(isset($result[0]) && !is_array($result[0]) && !empty($result[0]))
        $result = array($result);
    
    if(count($result) > 0) {
        foreach($result as $r)
            $found[$r['banned']] = array('by' => $r['banned_by'], 'reason' => $r['ban_reason'], 'type' => 'Ban', 'time' => $r['ban_time'], 'expires' => $r['ban_expires_on']);
    }
    
    if($past) {
        // Past Bans
        $result = cache("SELECT banned, banned_by, ban_reason, ban_time, ban_expired_on FROM ".$server['ipRecordTable']." WHERE banned LIKE '%".$search."%' ORDER BY ".$sort['banrecords']." $sortBy", 300, $serverID.'/search', $server);
        if(isset($result[0]) && !is_array($result[0]) && !empty($result[0]))
            $result = array($result);
        
        if(count($result) > 0) {
            foreach($result as $r) {
                if(!isset($found[$r['banned']]))
                    $found[$r['banned']] = array('by' => $r['banned_by'], 'reason' => $r['ban_reason'], 'type' => 'Ban', 'time' => $r['ban_time'], 'expires' => $r['ban_expired_on'], 'past' => true);
                else if($found[$r['banned']]['time'] < $r['ban_time'])
                    $found[$r['banned']] = array('by' => $r['banned_by'], 'reason' => $r['ban_reason'], 'type' => 'Ban', 'time' => $r['ban_time'], 'expires' => $r['ban_expired_on'], 'past' => true);
            }
        }
    }
    
    if(count($found) == 0)
        return false;
    else if(count($found) == 1) {
        // Redirect!
        $p = array_keys($found);
        redirect('index.php?action=viewip&ip='.$p[0].'&server='.$serverID);
    } else {
        // STUFF
        return $found;
    }
    
    if(count($found) == 0)
        return false;
    else if(count($found) == 1) {
        // Redirect!
        $p = array_keys($found);
        redirect('index.php?action=viewip&ip='.$p[0].'&server='.$serverID);
    } else {
        // STUFF
        return $found;
    }
}
?>
