<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrEtUser');



Class MbqWrEtUser extends MbqBaseWrEtUser {

    public function __construct() {
    }

    /**
     * register user
     */
    public function registerUser($username, $password, $email, $verified, $custom_register_fields, $profile, &$errors) {
        global $config, $mobiquo_config,$db, $user, $auth, $template, $phpbb_dispatcher, $phpbb_root_path, $phpEx,$user_info,$register,$phpbb_container, $request, $cache;
        require_once($phpbb_root_path .'/includes/functions_user.'. $phpEx);
        if(file_exists($phpbb_root_path . 'includes/functions_module.' . $phpEx))
	{
        	include_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);
	}
        $user->session_kill();
        $user->setup('ucp');
        if ($config['require_activation'] == USER_ACTIVATION_DISABLE || $mobiquo_config['sso_signin'] == 0)
        {
            $errors[] = $user->lang['UCP_REGISTER_DISABLE'];
        }
        $is_dst = $config['board_dst'];
        $timezone = $config['board_timezone'];
        $data = array(
            'username'            => utf8_normalize_nfc($username),
            'new_password'        => $password,
            'password_confirm'    => $password,
            'email'                => strtolower($email),
            'email_confirm'        => strtolower($email),
            'lang'                => basename($user->lang_name),
            'tz'                => (float) $timezone,
        );

        //check eve api
        if(!empty($config['eveapi_version']))
        {
            $data['eveapi_keyid'] = 0;
            $data['eveapi_vcode'] = '';
            $data['eveapi_ts'] = '';
            $config['eveapi_validation'] = 0;
        }

        //passwrod with any
        $config['pass_complex'] = 'PASS_TYPE_ANY';

        $errors = validate_data($data, array(
            'username'            => array(
                array('string', false, $config['min_name_chars'], $config['max_name_chars']),
                array('username', '')),
            'email'                => array(
                array('string', false, 6, 60),
                array('email')),
            'email_confirm'        => array('string', false, 6, 60),
            'tz'                => array('num', false, -14, 14),
            'lang'                => array('language_iso_name'),
        ));

        // Replace "error" strings with their real, localised form
        $errors = preg_replace_callback('#^([A-Z_]+)$#', function($matches){
            $tmp = $matches[1];
            if(!empty($user->lang[$tmp]))
            {
                return $user->lang[$tmp];
            }
            return $matches[1];
        }, $errors);
        // DNSBL check
        if ($config['check_dnsbl'])
        {
            if (($dnsbl = $user->check_dnsbl('register')) !== false)
            {
                $errors[] = sprintf($user->lang['IP_BLACKLISTED'], $user->ip, $dnsbl[1]);
            }
        }



        $cp = $phpbb_container->get('profilefields.manager');
        $cp_data = $cp_error = array();

        foreach($custom_register_fields as $key=>$value)
        {
            if(strpos($key, 'date_') === 0)
            {
                $dateValues = explode('-', $value);
                overwriteRequestParam(substr($key, 5) . '_year' ,$dateValues[0]);
                overwriteRequestParam(substr($key, 5) . '_month' ,$dateValues[1]);
                overwriteRequestParam(substr($key, 5) . '_day' ,$dateValues[2]);
            }
            else
            {
                overwriteRequestParam($key,$value);
            }
        }
        // validate custom profile fields
        $cp->submit_cp_field('register', $user->get_iso_lang_id(), $cp_data, $errors);

        if (!sizeof($errors))
        {

            // Which group by default?
            $group_name = 'REGISTERED';

            $sql = 'SELECT group_id
                FROM ' . GROUPS_TABLE . "
                WHERE group_name = '" . $db->sql_escape($group_name) . "'
                AND group_type = " . GROUP_SPECIAL;
            $result = $db->sql_query($sql);
            $row = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);

            if (!$row)
            {
                $errors[] = $user->lang['NO_GROUP'];
            }

            $group_id =  $row['group_id'];

            $auto_approve = (int) (isset($config['tapatalk_auto_approve']) ? $config['tapatalk_auto_approve'] : 1);
            if(($auto_approve && $verified) || ($config['require_activation'] == USER_ACTIVATION_NONE )
            || ($config['require_activation'] == USER_ACTIVATION_SELF && $verified)
            )
            {
                $user_type = USER_NORMAL;
                $user_actkey = '';
                $user_inactive_reason = 0;
                $user_inactive_time = 0;
            }
            else
            {
                $user_type = USER_INACTIVE;
                $user_actkey =  gen_rand_string(mt_rand(6, 10));
                $user_inactive_reason = INACTIVE_REGISTER;
                $user_inactive_time = time();
            }


            $user_row = array(
                'username'                => $data['username'],
                'user_password'            => phpbb_hash($data['new_password']),
                'user_email'            => $data['email'],
                'group_id'                => (int) $group_id,
                'user_timezone'            => (float) $data['tz'],
                //'user_dst'                => $is_dst,
                'user_actkey'                => $user_actkey,
                'user_lang'                => $data['lang'],
                'user_type'                => $user_type,
                'user_ip'                => $user->ip,
                'user_regdate'            => time(),
                'user_inactive_reason'    => $user_inactive_reason,
                'user_inactive_time'    => $user_inactive_time,
            );
            if ($config['new_member_post_limit'])
            {
                $user_row['user_new'] = 1;
            }

            if(!empty($profile))
            {
                if(!empty($profile['birthday']) && $config['allow_birthdays'])
                {
                    $birth_arr = explode('-', $profile['birthday']);
                    $user_row['user_birthday'] = sprintf('%2d-%2d-%4d', $birth_arr[2], $birth_arr[1], $birth_arr[0]);
                }

            //    $user_row['user_from'] = $profile['location'];
                //$user_row['user_website'] = $profile['link'];
                if(isset($profile['signature']))
                {
                    $user_row['user_sig'] = $profile['signature'];
                }

            }

            // Register user...
            $user_id = user_add($user_row,$cp_data);
            if(!empty($config['tapatalk_register_group']) && $config['tapatalk_register_group'] != $group_id)
            {
                group_user_add($config['tapatalk_register_group'], $user_id);
            }
            //copy avatar
            if(isset($profile['avatar_url']))
            {
                $this->tt_copy_avatar($user_id, $profile['avatar_url']);
            }
            // This should not happen, because the required variables are listed above...
            if ($user_id === false)
            {
                $errors[] = $user->lang['NO_USER'];
            }
            else
            {
                if ($config['require_activation'] == USER_ACTIVATION_SELF && $config['email_enable'])
                {
                    $message = $user->lang['ACCOUNT_INACTIVE'];
                    $email_template = 'user_welcome_inactive';
                }
                else if ($config['require_activation'] == USER_ACTIVATION_ADMIN && $config['email_enable'])
                {
                    $message = $user->lang['ACCOUNT_INACTIVE_ADMIN'];
                    $email_template = 'admin_welcome_inactive';
                }

                if ($config['email_enable'] && $user_type == USER_INACTIVE)
                {
                    $server_url = generate_board_url();
                    include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

                    $messenger = new messenger(false);

                    $messenger->template($email_template, $data['lang']);

                    $messenger->to($data['email'], $data['username']);

                    if(!method_exists($messenger, 'anti_abuse_headers'))
                    {
                        $messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
                        $messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
                        $messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
                        $messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);
                    }
                    else
                    {
                        $messenger->anti_abuse_headers($config, $user);
                    }

                    $messenger->assign_vars(array(
                        'WELCOME_MSG'    => htmlspecialchars_decode(sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename'])),
                        'USERNAME'        => htmlspecialchars_decode($data['username']),
                        'PASSWORD'        => htmlspecialchars_decode($data['new_password']),
                        'U_ACTIVATE'    => "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
                    );


                    $messenger->send(NOTIFY_EMAIL);

                    if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
                    {
                        // Grab an array of user_id's with a_user permissions ... these users can activate a user
                        $admin_ary = $auth->acl_get_list(false, 'a_user', false);
                        $admin_ary = (!empty($admin_ary[0]['a_user'])) ? $admin_ary[0]['a_user'] : array();

                        // Also include founders
                        $where_sql = ' WHERE user_type = ' . USER_FOUNDER;

                        if (sizeof($admin_ary))
                        {
                            $where_sql .= ' OR ' . $db->sql_in_set('user_id', $admin_ary);
                        }

                        $sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
                            FROM ' . USERS_TABLE . ' ' .
                            $where_sql;
                        $result = $db->sql_query($sql);

                        while ($row = $db->sql_fetchrow($result))
                        {
                            $messenger->template('admin_activate', $row['user_lang']);
                            $messenger->to($row['user_email'], $row['username']);
                            $messenger->im($row['user_jabber'], $row['username']);

                            $messenger->assign_vars(array(
                                'USERNAME'            => htmlspecialchars_decode($data['username']),
                                'U_USER_DETAILS'    => "$server_url/memberlist.$phpEx?mode=viewprofile&u=$user_id",
                                'U_ACTIVATE'        => "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
                            );

                            $messenger->send($row['user_notify_type']);
                        }
                        $db->sql_freeresult($result);
                    }
                }
                $user_info['user_id'] = $user_id;
                $user_info = array_merge($user_info,$user_row);
                $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
				return $oMbqRdEtUser->initOMbqEtUser($user_id, array('case'=>'byUserId'));
            }
        }
        return false;
    }

    public function updatePasswordDirectly($oMbqEtUser, $newPassword)
    {
        global $db, $phpbb_container, $config, $auth, $user;
	    if($config['min_pass_chars'] > strlen($newPassword))
        {
            return 'Password length should be more than ' . $config['min_pass_chars'] .' chars';
        }
        if($config['max_pass_chars'] < strlen($newPassword))
        {
            return 'Password length should be less than ' . $config['max_pass_chars'] .' chars';
        }
        $passwords_manager = $phpbb_container->get('passwords.manager');
        $hashedPassword = $passwords_manager->hash($newPassword);

        if ($auth->acl_get('u_chgpasswd') && $data['new_password'] && !$passwords_manager->check($data['new_password'], $user->data['user_password']))
        {
            $user->reset_login_keys();
            add_log('user', $user->data['user_id'], 'LOG_USER_NEW_PASSWORD', $data['username']);
        }
        $sql = 'UPDATE ' . USERS_TABLE . "
                                SET user_password = '" . $hashedPassword . "'
                                WHERE user_id = " . $user->data['user_id'];
        $db->sql_query($sql);

        return true;
    }
	/**
	 * update password
	 */
	public function updatePassword($oldPassword, $newPassword) {
		global $phpbb_root_path, $phpEx, $request, $user, $template;

		require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		require_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);
		overwriteRequestParam('i', $user->data['user_id']);
		overwriteRequestParam('new_password', $newPassword);
		overwriteRequestParam('password_confirm', $newPassword);
		overwriteRequestParam('cur_password', $oldPassword);
		$creation_time = time() - 3600;
		$token_sid = ($user->data['user_id'] == ANONYMOUS && !empty($config['form_token_sid_guests'])) ? $user->session_id : '';
		$key = sha1($creation_time . $user->data['user_form_salt'] . 'ucp_reg_details' . $token_sid);
		overwriteRequestParam('creation_time', $creation_time, \phpbb\request\request_interface::POST);
		overwriteRequestParam('form_token', $key, \phpbb\request\request_interface::POST);
		overwriteRequestParam('creation_time', $creation_time, \phpbb\request\request_interface::REQUEST);
		overwriteRequestParam('form_token', $key, \phpbb\request\request_interface::REQUEST);
		overwriteRequestParam('submit', true,\phpbb\request\request_interface::POST);

		$user->setup('ucp');
		$module = new p_master();
		requireExtLibrary('fake_template');
		$template = new fake_template();
		$module->load('ucp', 'profile', 'reg_details');
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

	/**
	 * update email
	 */
	public function updateEmail($password, $email, &$resultMessage) {
		global $phpbb_root_path, $phpEx, $request, $user, $template;

		require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		require_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);
		overwriteRequestParam('i', $user->data['user_id']);
		overwriteRequestParam('email', $email);
		overwriteRequestParam('cur_password', $password);
		$creation_time = time() - 3600;
		$token_sid = ($user->data['user_id'] == ANONYMOUS && !empty($config['form_token_sid_guests'])) ? $user->session_id : '';
		$key = sha1($creation_time . $user->data['user_form_salt'] . 'ucp_reg_details' . $token_sid);
		overwriteRequestParam('creation_time', $creation_time, \phpbb\request\request_interface::POST);
		overwriteRequestParam('form_token', $key, \phpbb\request\request_interface::POST);
		overwriteRequestParam('creation_time', $creation_time, \phpbb\request\request_interface::REQUEST);
		overwriteRequestParam('form_token', $key, \phpbb\request\request_interface::REQUEST);
		overwriteRequestParam('submit', true,\phpbb\request\request_interface::POST);
	        overwriteRequestParam('email_confirm', $email);
		requireExtLibrary('fake_template');
		$user->setup('ucp');
		$template = new fake_template();
		$module = new p_master();
		$module->load('ucp', 'profile', 'reg_details');
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

	/**
	 * upload avatar
	 */
	public function uploadAvatar() {
		global $phpbb_root_path, $phpEx, $request, $user, $template;

		require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		require_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);
		$creation_time = time() - 3600;
		$token_sid = ($user->data['user_id'] == ANONYMOUS && !empty($config['form_token_sid_guests'])) ? $user->session_id : '';
		$key = sha1($creation_time . $user->data['user_form_salt'] . 'ucp_avatar' . $token_sid);
		overwriteRequestParam('creation_time', $creation_time, \phpbb\request\request_interface::POST);
		overwriteRequestParam('form_token', $key, \phpbb\request\request_interface::POST);
		overwriteRequestParam('creation_time', $creation_time, \phpbb\request\request_interface::REQUEST);
		overwriteRequestParam('form_token', $key, \phpbb\request\request_interface::REQUEST);
		overwriteRequestParam('submit', true,\phpbb\request\request_interface::POST);

        overwriteRequestParam('avatar_driver', 'avatar.driver.upload');

        //this require apt-get install php5-gd

        overwriteRequestParam('avatar_upload_file', $_FILES['uploadfile'], \phpbb\request\request_interface::FILES);
        requireExtLibrary('fake_template');
		$user->setup('ucp');
		$template = new fake_template();
		$module = new p_master();
		$module->load('ucp', 'profile', 'avatar');
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

	function tt_copy_avatar($uid,$avatar_url)
	{
		global $config,$phpbb_root_path,$db,$user, $phpEx;
		$can_upload = $config['allow_avatar_remote'];
		if($can_upload && !empty($avatar_url))
		{
			$avatar['user_id'] = $uid;
			$avatar['uploadurl'] = '';
			$avatar['remotelink'] = $avatar_url;
			$avatar['width'] = $config['avatar_max_width'];
			$avatar['height'] = $config['avatar_max_height'];
			$error = array();
			$upload_response = avatar_remote($avatar, $error);

            if(empty($error))
            {
                list($sql_ary['user_avatar_type'], $sql_ary['user_avatar'], $sql_ary['user_avatar_width'], $sql_ary['user_avatar_height']) = $upload_response;
                $sql = 'UPDATE ' . USERS_TABLE . '
            SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
            WHERE user_id = ' . $uid;
                $db->sql_query($sql);
            }
        }
    }

    public function ignoreUser($oMbqEtUser, $ignoremode)
    {
        global $phpbb_root_path, $phpEx, $request, $user, $auth;

        //setup fake template
        requireExtLibrary('fake_template');
        $template = new fake_template();

        require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
        include_once($phpbb_root_path . 'includes/ucp/ucp_zebra.' . $phpEx);

        $ignoremode = intval($ignoremode);

        $user->session_begin();
        $auth->acl($user->data);
        $user->setup('ucp');

        if(empty($user->data['user_last_confirm_key'])) $user->data['user_last_confirm_key'] =  request_var('confirm_key', '');

        overwriteRequestParam('confirm', $user->lang['YES'],  \phpbb\request\request_interface::POST);
        overwriteRequestParam('confirm_uid', $user->data['user_id'],  \phpbb\request\request_interface::POST);
        overwriteRequestParam('sess', $user->session_id,  \phpbb\request\request_interface::POST);
        overwriteRequestParam('confirm_key', $user->data['user_last_confirm_key'],  \phpbb\request\request_interface::POST);

        if($ignoremode == 1)
        {
            overwriteRequestParam('add', $oMbqEtUser->userName->oriValue,  \phpbb\request\request_interface::REQUEST);
            overwriteRequestParam('mode', 'foes',  \phpbb\request\request_interface::REQUEST);
            overwriteRequestParam('submit', 1,  \phpbb\request\request_interface::POST);
        }
        else
        {
            overwriteRequestParam('usernames', explode(',', $oMbqEtUser->userId->oriValue),  \phpbb\request\request_interface::REQUEST);
            overwriteRequestParam('submit', 'Submit',  \phpbb\request\request_interface::POST);
        }
        $ucp = new ucp_zebra();
        $ucp->main('zebra','foes');

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

    public function mBanUser($oMbqEtUser, $banmode, $reason, $expires)
    {
        global $phpbb_root_path, $phpEx, $request, $user, $auth, $action, $db;

        $banmode = intval($banmode);
        //setup fake template
        requireExtLibrary('fake_template');
        $template = new fake_template();

        $user->session_begin();
        $auth->acl($user->data);
        $user->setup('mcp');


        if(empty($user->data['user_last_confirm_key'])) $user->data['user_last_confirm_key'] = 'tapatalk';

        overwriteRequestParam('confirm', $user->lang['YES'],  \phpbb\request\request_interface::POST);
        overwriteRequestParam('confirm_uid', $user->data['user_id'],  \phpbb\request\request_interface::REQUEST);
        overwriteRequestParam('sess', $user->session_id,  \phpbb\request\request_interface::REQUEST);
        overwriteRequestParam('confirm_key', $user->data['user_last_confirm_key'],  \phpbb\request\request_interface::REQUEST);

        overwriteRequestParam('user_id', $user->data['user_id'],  \phpbb\request\request_interface::REQUEST);
        overwriteRequestParam('ban', $oMbqEtUser->userName->oriValue,  \phpbb\request\request_interface::REQUEST);
        overwriteRequestParam('i', 'ban',  \phpbb\request\request_interface::REQUEST);
        overwriteRequestParam('bansubmit', 1,  \phpbb\request\request_interface::POST);
        if(!empty($expires)) overwriteRequestParam('banlength', intval(($expires - time())/60),  \phpbb\request\request_interface::REQUEST);
        overwriteRequestParam('banreason', $reason,  \phpbb\request\request_interface::REQUEST);

        include_once($phpbb_root_path . 'includes/mcp/mcp_ban.' . $phpEx);
        $mcp = new mcp_ban();

        $mcp->main('ban', 'user');

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

        if($banmode == 2)
        {
            $ban_userid = $oMbqEtUser->userId->oriValue;
            //judge the user post num is or not > 50
            $sql = "SELECT COUNT(*) AS tp_count FROM " . POSTS_TABLE . " p WHERE p.poster_id = '".$ban_userid."'";
            $result = $db->sql_query($sql);
            $countRow = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);
            if($countRow['tp_count'] > 50) {
                return trigger_error('USER_POSTS_NUM GERATER THAN 50');
            }
            else {
                $sql = "SELECT post_id,topic_id,forum_id FROM " . POSTS_TABLE . " p WHERE p.poster_id = '".$ban_userid."'";
                $result1 = $db->sql_query($sql);
                while($row = $db->sql_fetchrow($result1)) {
                    $sql = 'SELECT f.*, t.*, p.*, u.username, u.username_clean, u.user_sig, u.user_sig_bbcode_uid, u.user_sig_bbcode_bitfield
                        FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f, ' . USERS_TABLE . " u
                        WHERE p.post_id = '".$row['post_id']."'
                            AND t.topic_id = p.topic_id
                            AND u.user_id = p.poster_id
                            AND (f.forum_id = t.forum_id
                                OR f.forum_id = '".$row['forum_id']."')" .
                            (($auth->acl_get('m_approve', $row['forum_id'])) ? '' : 'AND p.post_approved = 1');
                    $result2 = $db->sql_query($sql);
                    $post_data = $db->sql_fetchrow($result2);
                    $db->sql_freeresult($result2);
                    handle_post_delete($row['forum_id'], $row['topic_id'], $row['post_id'], $post_data);
                }
                unset($row);
                $db->sql_freeresult($result1);
            }
        }

        return true;
    }

    public function mUnBanUser($oMbqEtUser)
    {
        global $phpbb_root_path, $phpEx, $request, $user, $auth, $action, $db;

        //$mode = intval($mode);
        //setup fake template
        requireExtLibrary('fake_template');
        $template = new fake_template();

        $user->session_begin();
        $auth->acl($user->data);
        $user->setup('mcp');

        $sql = 'SELECT b.ban_id FROM ' . BANLIST_TABLE . ' b WHERE b.ban_userid = ' . intval($oMbqEtUser->userId->oriValue);
        $result = $db->sql_query($sql);
        $unban = array();
        while($row = $db->sql_fetchrow($result))
        {
            $unban[] = $row['ban_id'];
        }
        $db->sql_freeresult($result);

        if(empty($user->data['user_last_confirm_key'])) $user->data['user_last_confirm_key'] = 'tapatalk';

        overwriteRequestParam('confirm', $user->lang['YES'],  \phpbb\request\request_interface::POST);
        overwriteRequestParam('confirm_uid', $user->data['user_id'],  \phpbb\request\request_interface::POST);
        overwriteRequestParam('sess', $user->session_id,  \phpbb\request\request_interface::POST);
        overwriteRequestParam('confirm_key', $user->data['user_last_confirm_key'],  \phpbb\request\request_interface::POST);

        overwriteRequestParam('user_id', $user->data['user_id'],  \phpbb\request\request_interface::POST);
        overwriteRequestParam('unban', $unban,  \phpbb\request\request_interface::REQUEST);
        overwriteRequestParam('i', 'ban',  \phpbb\request\request_interface::POST);
        overwriteRequestParam('unbansubmit', 1,  \phpbb\request\request_interface::POST);

        include_once($phpbb_root_path . 'includes/mcp/mcp_ban.' . $phpEx);
        $mcp = new mcp_ban();

        $mcp->main('ban', 'user');

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
}

