<?php

function TT_check_return_user_type($user_row, $checkBan = false)
{
    global $db, $user, $config, $auth;
    //$session = new user();
    $user_id = $user_row['user_id'];
    if($checkBan)
    {
        $is_ban = $user->check_ban($user_id,false,false,true);
        if(!empty($is_ban) && $is_ban)
        {
            return 'banned';
        }
    }
    $auth2 = new \phpbb\auth\auth();
    $auth2->acl($user_row);
    if ($auth2->acl_gets('a_'))
    {
        return 'admin';
    }
    else if($auth2->acl_gets('m_'))
    {
        return 'mod';
    }
    else if($user_row['user_type'] == USER_INACTIVE && $config['require_activation'] == USER_ACTIVATION_ADMIN)
    {
    	return 'unapproved';
    }
    else if($user_row['user_type'] == USER_INACTIVE)
    {
    	return'inactive';
    }
    return 'normal';
}
