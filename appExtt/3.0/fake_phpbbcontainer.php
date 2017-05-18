<?php
/*
 * this class make a fake $phpbb_container like phpbb31 so we can use the same code on both forum systems
 * 
 */


class fake_phpbbcontainer
{
    function get($container)
    {
        global $phpbb_root_path,$phpEx;
        if($container == 'profilefields.manager')
        {
            include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);
            $cp = new custom_profile();
            return $cp;
        }
        else if($container == 'notification_manager')
        {
            return new fake_notification_manager();
        }
        else if($container == 'passwords.manager')
        {
            return new fake_passwords_manager();
        }
        return null;
    }
}
class fake_passwords_manager
{
    function hash($password)
    {
        return phpbb_hash($password);
    }
}
class fake_notification_manager
{
    function mark_notifications_read($notificationType, $msg_id, $user_id)
    {
    }
}
$phpbb_container = new fake_phpbbcontainer();

?>