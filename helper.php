<?php

function TT_check_return_user_type($user_id)
{
    global $db, $user, $config;
    //$session = new user();
    $user_id = intval($user_id);
    $user_row = TT_get_user_by_id($user_id);
    $sql = "SELECT group_name FROM " . USER_GROUP_TABLE . " AS ug LEFT JOIN " .GROUPS_TABLE. " AS g ON ug.group_id = g.group_id WHERE user_id = " . $user_id;
    $query = $db->sql_query($sql);
    $is_ban = $user->check_ban($user_id,false,false,true);
    $user_groups = array();
    while($row = $db->sql_fetchrow($query))
    {
        $user_groups[] = $row['group_name'];
    }
    if(!empty($is_ban ))
    {
        $user_type = 'banned';
    }
    else if(in_array('ADMINISTRATORS', $user_groups))
    {
        $user_type = 'admin';
    }
    else if(in_array('GLOBAL_MODERATORS', $user_groups))
    {
        $user_type = 'mod';
    }
    else if($user_row['user_type'] == USER_INACTIVE && $config['require_activation'] == USER_ACTIVATION_ADMIN)
    {
    	$user_type = 'unapproved';
    }
    else if($user_row['user_type'] == USER_INACTIVE)
    {
    	$user_type = 'inactive';
    }
    else
    {
        $user_type = 'normal';
    }
    return $user_type;
}

function TT_get_user_by_id($uid)
{
    global $db;
    $sql = 'SELECT *
        FROM ' . USERS_TABLE . "
        WHERE user_id = '" . $db->sql_escape($uid) . "'";
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);
    $db->sql_freeresult($result);
    return $row;
}
