<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrEtPm');

/**
 * private message write class
 */
Class MbqWrEtPm extends MbqBaseWrEtPm {

    public function __construct() {
    }
    /**
     * add private message
     */
    public function addMbqEtPm($oMbqEtPm) {
        global $db, $user, $auth, $config, $phpbb_root_path, $phpEx;
        $user->setup('ucp');

        include_once($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
        include_once($phpbb_root_path . 'includes/ucp/ucp_pm_compose.' . $phpEx);
        include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);

        // Flood check
        $current_time = time();
        $last_post_time = $user->data['user_lastpost_time'];

        if ($last_post_time && ($current_time - $last_post_time) < intval($config['flood_interval']))
        {
            return getSystemString('FLOOD_ERROR');
        }

        $user_name  = $oMbqEtPm->userNames->oriValue;
        $subject    = utf8_normalize_nfc($oMbqEtPm->msgTitle->oriValue);
        $text_body  = utf8_normalize_nfc($oMbqEtPm->msgContent->oriValue);
        require_once MBQ_APPEXTENTION_PATH . 'emoji.php';
        $text_body = emoji_unified_to_names($text_body);


        $action = 'post';   // default action

        if ($oMbqEtPm->isReply->oriValue)
        {
            $action = 'reply';
            $msg_id = $oMbqEtPm->toMsgId->oriValue;
            if (!$msg_id) return getSystemString('NO_MESSAGE');
        }
        else if ($oMbqEtPm->isForward->oriValue)
        {
            $action = 'forward';
            $msg_id = $oMbqEtPm->toMsgId->oriValue;
            if (!$msg_id) return getSystemString('NO_MESSAGE');
        }

        if (($action == 'post' || $action == 'reply')  && (!$auth->acl_get('u_sendpm')))
        {
            return getSystemString('NO_AUTH_SEND_MESSAGE');
        }

        if ($action == 'forward' && (!$config['forward_pm'] || !$auth->acl_get('u_pm_forward')))
        {
            return getSystemString('NO_AUTH_FORWARD_MESSAGE');
        }

        // Do NOT use request_var or specialchars here
        $address_list = array('u' => array());

        foreach($user_name as $msg_to_name)
        {
            $user_id = get_user_id_by_name(trim($msg_to_name));
            if ($user_id)
            {
                $address_list['u'][$user_id] = 'to';
            }
            else
            {
                return getSystemString('PM_NO_USERS');
            }
        }

        $sql = '';

        // What is all this following SQL for? Well, we need to know
        // some basic information in all cases before we do anything.
        if ($action != 'post')
        {
            $sql = 'SELECT t.folder_id, p.*, u.username as quote_username
                FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . ' p, ' . USERS_TABLE . ' u
                WHERE t.user_id = ' . $user->data['user_id'] . "
                AND p.author_id = u.user_id
                AND t.msg_id = p.msg_id
                AND p.msg_id = $msg_id";
        }

        if ($sql)
        {
            $result = $db->sql_query($sql);
            $post = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);

            if (!$post)
            {
                return getSystemString('NO_MESSAGE');
            }

            if (!$post['author_id'] || $post['author_id'] == ANONYMOUS)
            {
                return getSystemString('NO_AUTHOR');
            }
        }

        include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
        $message_parser = new parse_message();

        // Get maximum number of allowed recipients
        if($config['version'] > '3.0.3')
        {
            $sql = 'SELECT MAX(g.group_max_recipients) as max_recipients
        FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
        WHERE ug.user_id = ' . $user->data['user_id'] . '
            AND ug.user_pending = 0
            AND ug.group_id = g.group_id';
            $result = $db->sql_query($sql);
            $max_recipients = (int) $db->sql_fetchfield('max_recipients');
            $db->sql_freeresult($result);

            $max_recipients = (!$max_recipients) ? $config['pm_max_recipients'] : $max_recipients;
        }
        else
        {
            $max_recipients = 10;
        }


        // If this is a quote/reply "to all"... we may increase the max_recpients to the number of original recipients
        if (($action == 'reply' || $action == 'quote') && $max_recipients)
        {
            // We try to include every previously listed member from the TO Header
            $list = rebuild_header(array('to' => $post['to_address']));

            // Can be an empty array too ;)
            $list = (!empty($list['u'])) ? $list['u'] : array();
            $list[$post['author_id']] = 'to';

            if (isset($list[$user->data['user_id']]))
            {
                unset($list[$user->data['user_id']]);
            }

            $max_recipients = ($max_recipients < sizeof($list)) ? sizeof($list) : $max_recipients;

            unset($list);
        }

        // Handle User/Group adding/removing
        $remove_u = null;
        $remove_g = null;
        $add_to = null;
        $add_bcc = null;
        handle_message_list_actions($address_list, $error, $remove_u, $remove_g, $add_to, $add_bcc);

        if ($error)
        {
            $error_msg = trim(strip_tags(implode("\n", $error)));
            trigger_error($error_msg);
        }

        // Check mass pm to group permission
        if ((!$config['allow_mass_pm'] || !$auth->acl_get('u_masspm_group')) && !empty($address_list['g']))
        {
            $address_list = array();
            return getSystemString('NO_AUTH_GROUP_MESSAGE');
        }

        // Check mass pm to users permission
        if ((!$config['allow_mass_pm'] || !$auth->acl_get('u_masspm')) && num_recipients($address_list) > 1 && $action != 'reply' && $action != 'quote')
        {
            if(function_exists('get_recipients'))
            {
                $address_list = get_recipients($address_list, 1);
                return getSystemString('TOO_MANY_RECIPIENTS');
            }
            else if(function_exists('get_recipient_pos'))
            {
                $address_list = get_recipient_pos($address_list, 1);
            }
        }

        // Check for too many recipients
        if (!empty($address_list['u']) && $max_recipients && sizeof($address_list['u']) > $max_recipients)
        {
            if(function_exists('get_recipients'))
            {
                $address_list = get_recipients($address_list, $max_recipients);
                return getSystemString('TOO_MANY_RECIPIENTS');
            }
            else if(function_exists('get_recipient_pos'))
            {
                $address_list = get_recipient_pos($address_list, $max_recipients);
            }
        }

        $enable_bbcode  = ($config['allow_bbcode'] && $config['auth_bbcode_pm'] && $auth->acl_get('u_pm_bbcode')) ? true : false;
        $enable_smilies = ($config['allow_smilies'] && $config['auth_smilies_pm'] && $auth->acl_get('u_pm_smilies')) ? true : false;
        $img_status     = ($config['auth_img_pm'] && $auth->acl_get('u_pm_img')) ? true : false;
        $flash_status   = ($config['auth_flash_pm'] && $auth->acl_get('u_pm_flash')) ? true : false;
        $enable_urls    = true;
        $enable_sig     = false;

        $message_parser->message_subject = $text_body;
        $message_parser->message = $text_body;


        // Parse message
        $message_parser->parse($enable_bbcode, ($config['allow_post_links']) ? $enable_urls : false, $enable_smilies, $img_status, $flash_status, true, $config['allow_post_links']);

        $pm_data = array(
            'from_user_id'          => $user->data['user_id'],
            'from_user_ip'          => $user->ip,
            'from_username'         => $user->data['username'],
             'icon_id'               => 0,
            'enable_sig'            => (bool) $enable_sig,
            'enable_bbcode'         => (bool) $enable_bbcode,
            'enable_smilies'        => (bool) $enable_smilies,
            'enable_urls'           => (bool) $enable_urls,
            'bbcode_bitfield'       => $message_parser->bbcode_bitfield,
            'bbcode_uid'            => $message_parser->bbcode_uid,
            'message'               => $message_parser->message,
            'attachment_data'       => $message_parser->attachment_data,
            'filename_data'         => $message_parser->filename_data,
            'address_list'          => $address_list
        );
        if( $oMbqEtPm->isReply->hasSetOriValue() &&  $oMbqEtPm->isReply->oriValue)
        {
            //            'reply_from_root_level' => (isset($post['root_level'])) ? (int) $post['root_level'] : 0,
            $pm_data['reply_from_msg_id'] = $oMbqEtPm->toMsgId->oriValue;
        }
        $msg_id = submit_pm($action, $subject, $pm_data);

        $oMbqEtPm->msgId->setOriValue($msg_id);
        return $oMbqEtPm;
    }
    public function deleteMbqEtPmMessage($oMbqEtPm) {
        global $db, $user, $config, $phpbb_root_path, $phpEx;

        $user->setup('ucp');


        // get folder id from parameters
        $msg_id = $oMbqEtPm->msgId->oriValue;
        $user_id = $user->data['user_id'];

        if (!$msg_id) return getSystemString('NO_MESSAGE');

        $sql = 'SELECT folder_id
            FROM ' . PRIVMSGS_TO_TABLE . "
            WHERE user_id = $user_id
            AND msg_id = $msg_id";
        $result = $db->sql_query_limit($sql, 1);
        $folder_id = (int) $db->sql_fetchfield('folder_id');
        $db->sql_freeresult($result);

        include_once($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
        $result = delete_pm($user_id, $msg_id, $folder_id);
        return $result;
    }


    /**
     * report post
     */
    public function reportPm($oMbqEtPm, $reason) {
        global $user,$phpbb_root_path, $phpEx, $template, $request;

        overwriteRequestParam('pm', $oMbqEtPm->msgId->oriValue);
        overwriteRequestParam('reason_id', 4);
        overwriteRequestParam('report_text', $reason);
        overwriteRequestParam('submit', true, \phpbb\request\request_interface::POST);

        requireExtLibrary('report_clone');
        requireExtLibrary('fake_template');
        $template = new fake_template();
        $error = $template->getTemplateVar('ERROR');
        if(isset($error) && !empty($error))
        {
            $errors = explode('<br />', $error);
            if(is_array($errors))
            {
                return $errors[0];
            }
            return $error;
        }
        return true;
    }

    public function markPmUnread($oMbqEtPm){
        global $db, $user, $phpbb_container;

        $msg_id = $oMbqEtPm->msgId->oriValue;
        $folder_id = $oMbqEtPm->boxId->oriValue;
        $user_id = $user->data['user_id'];
        $phpbb_notifications = $phpbb_container->get('notification_manager');

        $phpbb_notifications->mark_notifications_read('notification.type.pm', $msg_id, $user_id);

        $sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . "
		SET pm_unread = 1
		WHERE msg_id = $msg_id
			AND user_id = $user_id
			AND folder_id = $folder_id";
        $db->sql_query($sql);

        $sql = 'UPDATE ' . USERS_TABLE . "
		SET user_unread_privmsg = user_unread_privmsg + 1
		WHERE user_id = $user_id";
        $db->sql_query($sql);

        if ($user->data['user_id'] == $user_id)
        {
            $user->data['user_unread_privmsg']++;

            // Try to cope with previous wrong conversions...
            if ($user->data['user_unread_privmsg'] < 0)
            {
                $sql = 'UPDATE ' . USERS_TABLE . "
				SET user_unread_privmsg = 0
				WHERE user_id = $user_id";
                $db->sql_query($sql);

                $user->data['user_unread_privmsg'] = 0;
            }
        }
        return true;
    }

    public function markPmRead($oMbqEtPm){
        global $db, $user, $phpbb_container;

        $msg_id = $oMbqEtPm->msgId->oriValue;
        $folder_id = $oMbqEtPm->boxId->oriValue;
        $user_id = $user->data['user_id'];
        $phpbb_notifications = $phpbb_container->get('notification_manager');

        $phpbb_notifications->mark_notifications_read('notification.type.pm', $msg_id, $user_id);

        $sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . "
		SET pm_unread = 0
		WHERE msg_id = $msg_id
			AND user_id = $user_id
			AND folder_id = $folder_id";
        $db->sql_query($sql);

        $sql = 'UPDATE ' . USERS_TABLE . "
		SET user_unread_privmsg = user_unread_privmsg - 1
		WHERE user_id = $user_id";
        $db->sql_query($sql);

        if ($user->data['user_id'] == $user_id)
        {
            $user->data['user_unread_privmsg']--;

            // Try to cope with previous wrong conversions...
            if ($user->data['user_unread_privmsg'] < 0)
            {
                $sql = 'UPDATE ' . USERS_TABLE . "
				SET user_unread_privmsg = 0
				WHERE user_id = $user_id";
                $db->sql_query($sql);

                $user->data['user_unread_privmsg'] = 0;
            }
        }
        return true;
    }
    public function markAllPmRead(){
        global $db, $user, $phpbb_container;

        $user_id = $user->data['user_id'];
        $phpbb_notifications = $phpbb_container->get('notification_manager');

        $sql = 'SELECT msg_id FROM ' . PRIVMSGS_TO_TABLE . "
		WHERE  pm_unread = 1
			AND user_id = $user_id";
        $sqlResult = $db->sql_query($sql);

        while($row = $db->sql_fetchrow($sqlResult))
        {
            $phpbb_notifications->mark_notifications_read('notification.type.pm', $row['msg_id'], $user_id);
        }
		$db->sql_freeresult($sqlResult);

        $sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . "
		SET pm_unread = 0
	    WHERE  pm_unread = 1
			AND user_id = $user_id";
        $db->sql_query($sql);

        $sql = 'UPDATE ' . USERS_TABLE . "
				SET user_unread_privmsg = 0
				WHERE user_id = $user_id";
        $db->sql_query($sql);

        $user->data['user_unread_privmsg'] = 0;
        return true;
    }

}