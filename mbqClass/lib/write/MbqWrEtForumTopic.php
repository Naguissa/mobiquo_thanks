<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrEtForumTopic');

/**
 * forum topic write class
 */
Class MbqWrEtForumTopic extends MbqBaseWrEtForumTopic {

    public function __construct() {
    }
    /**
     * add forum topic
     */
    public function addMbqEtForumTopic($oMbqEtForumTopic) {
        global $db, $auth, $user, $config, $phpbb_root_path, $phpEx, $mobiquo_config,$request;
        $post_data = $oMbqEtForumTopic->oMbqEtForum->mbqBind;

        $forum_id   = $oMbqEtForumTopic->forumId->oriValue;
        $subject    = $oMbqEtForumTopic->topicTitle->oriValue;
        $text_body  = $oMbqEtForumTopic->topicContent->oriValue;
        require_once MBQ_APPEXTENTION_PATH .'emoji.php';
        $text_body = emoji_unified_to_names($text_body);
        $attach_list = $oMbqEtForumTopic->attachmentIdArray->hasSetOriValue() ? $oMbqEtForumTopic->attachmentIdArray->oriValue : array();
        $attachment_data = $oMbqEtForumTopic->groupId->hasSetOriValue() ? unserialize(base64_decode($oMbqEtForumTopic->groupId->oriValue)) : array();
        overwriteRequestParam('attachment_data', $attachment_data, \phpbb\request\request_interface::POST);
        $post_data['quote_username']   = '';
        $post_data['post_edit_locked'] = 0;
        $post_data['post_subject']     = '';
        $post_data['topic_time_limit'] = 0;
        $post_data['icon_id']          = 0;

        include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
        $message_parser = new parse_message();
        $current_time = time();
        // Set some default variables
        $uninit = array('post_attachment' => 0,
                        'poster_id' => $user->data['user_id'],
                        'enable_magic_url' => 0,
                        'topic_status' => 0,
                        'topic_type' => POST_NORMAL,
                        'post_subject' => '',
                        'topic_title' => '',
                        'post_time' => 0,
                        'post_edit_reason' => '',
                        'notify_set' => 0);

        foreach ($uninit as $var_name => $default_value)
        {
            if (!isset($post_data[$var_name]))
            {
                $post_data[$var_name] = $default_value;
            }
        }
        unset($uninit);

        if ($config['allow_topic_notify'] && $user->data['is_registered'])
        {
            $notify = $user->data['user_notify'] ? true : false;
        }
        else
        {
            $notify = false;
        }

        // Always check if the submitted attachment data is valid and belongs to the user.
        // Further down (especially in submit_post()) we do not check this again.
        $message_parser->get_submitted_attachment_data($post_data['poster_id']);

        $post_data['username']          = '';
        // guest new topic
        if (!$user->data['is_registered'] && isset($post_data['guest_username'])) {
            $post_data['username'] = $post_data['guest_username'];
        }
        $post_data['enable_urls']       = $post_data['enable_magic_url'];
        $post_data['enable_sig']        = ($config['allow_sig'] && $user->optionget('attachsig')) ? true: false;
        $post_data['enable_smilies']    = ($config['allow_smilies'] && $user->optionget('smilies')) ? true : false;
        $post_data['enable_bbcode']        = ($config['allow_bbcode'] && $user->optionget('bbcode')) ? true : false;
        $post_data['enable_urls']        = true;
        $post_data['enable_magic_url']  = $post_data['drafts'] = false;

        $check_value = (($post_data['enable_bbcode']+1) << 8) + (($post_data['enable_smilies']+1) << 4) + (($post_data['enable_urls']+1) << 2) + (($post_data['enable_sig']+1) << 1);

        // HTML, BBCode, Smilies, Images and Flash status
        $bbcode_status    = ($config['allow_bbcode'] && $auth->acl_get('f_bbcode', $forum_id)) ? true : false;
        $smilies_status    = ($bbcode_status && $config['allow_smilies'] && $auth->acl_get('f_smilies', $forum_id)) ? true : false;
        $img_status        = ($bbcode_status && $auth->acl_get('f_img', $forum_id)) ? true : false;
        $url_status        = ($config['allow_post_links']) ? true : false;
        $flash_status    = ($bbcode_status && $auth->acl_get('f_flash', $forum_id) && $config['allow_post_flash']) ? true : false;
        $quote_status    = ($auth->acl_get('f_reply', $forum_id)) ? true : false;

        $post_data['topic_cur_post_id']    = request_var('topic_cur_post_id', 0);
        $post_data['post_subject']        = utf8_normalize_nfc($subject);
        $message_parser->message        = utf8_normalize_nfc(htmlspecialchars($text_body));

        $post_data['username']            = utf8_normalize_nfc(request_var('username', $post_data['username'], true));
        $post_data['post_edit_reason']    = '';

        $post_data['orig_topic_type']    = $post_data['topic_type'];
        $post_data['topic_type']        = request_var('topic_type', POST_NORMAL);
        $post_data['topic_time_limit']    = request_var('topic_time_limit', 0);
        $post_data['icon_id']            = request_var('icon', 0);

        $post_data['enable_bbcode']        = (!$bbcode_status || isset($_POST['disable_bbcode'])) ? false : true;
        $post_data['enable_smilies']    = (!$smilies_status || isset($_POST['disable_smilies'])) ? false : true;
        $post_data['enable_urls']        = (isset($_POST['disable_magic_url'])) ? 0 : 1;
        $post_data['enable_sig']        = (!$config['allow_sig'] || !$auth->acl_get('f_sigs', $forum_id) || !$auth->acl_get('u_sig')) ? false : ($user->data['is_registered'] ? true : false);

        $topic_lock            = (isset($_POST['lock_topic'])) ? true : false;
        $post_lock            = (isset($_POST['lock_post'])) ? true : false;
        $poll_delete        = (isset($_POST['poll_delete'])) ? true : false;

        $status_switch = (($post_data['enable_bbcode']+1) << 8) + (($post_data['enable_smilies']+1) << 4) + (($post_data['enable_urls']+1) << 2) + (($post_data['enable_sig']+1) << 1);
        $status_switch = ($status_switch != $check_value);

        $post_data['poll_start']       = 0;
        $post_data['poll_options']     = array();
        $post_data['poll_title']       = utf8_normalize_nfc(request_var('poll_title', '', true));
        $post_data['poll_length']      = request_var('poll_length', 0);
        $post_data['poll_option_text'] = utf8_normalize_nfc(request_var('poll_option_text', '', true));
        $post_data['poll_max_options'] = request_var('poll_max_options', 1);
        $post_data['poll_vote_change'] = ($auth->acl_get('f_votechg', $forum_id) && isset($_POST['poll_vote_change'])) ? 1 : 0;

        // Parse Attachments - before checksum is calculated
        $message_parser->parse_attachments('fileupload', 'post', $forum_id, true, false, false);

        // Grab md5 'checksum' of new message
        $message_md5 = md5($message_parser->message);

        if (sizeof($message_parser->warn_msg))
        {
            trigger_error(join("\n", $message_parser->warn_msg));
        }

        $message_parser->parse($post_data['enable_bbcode'], ($config['allow_post_links']) ? $post_data['enable_urls'] : false, $post_data['enable_smilies'], $img_status, $flash_status, $quote_status, $config['allow_post_links']);

        if ($config['flood_interval'] && !$auth->acl_get('f_ignoreflood', $forum_id))
        {
            // Flood check
            $last_post_time = 0;

            if ($user->data['is_registered'])
            {
                $last_post_time = $user->data['user_lastpost_time'];
            }
            else
            {
                $sql = 'SELECT post_time AS last_post_time
                FROM ' . POSTS_TABLE . "
                WHERE poster_ip = '" . $user->ip . "'
                    AND post_time > " . ($current_time - $config['flood_interval']);
                $result = $db->sql_query_limit($sql, 1);
                if ($row = $db->sql_fetchrow($result))
                {
                    $last_post_time = $row['last_post_time'];
                }
                $db->sql_freeresult($result);
            }

            if ($last_post_time && ($current_time - $last_post_time) < intval($config['flood_interval']))
            {
                trigger_error('FLOOD_ERROR');
            }
        }

        if ($user->data['is_registered'])
        {
            $sql = 'SELECT topic_title
				FROM ' . TOPICS_TABLE . "
				WHERE topic_poster = " . $user->data['user_id'] . "
				ORDER BY topic_time DESC";
            $result = $db->sql_query_limit($sql, 1);
            if ($row = $db->sql_fetchrow($result))
            {
                $last_topic_title = $row['topic_title'];
            }
            $db->sql_freeresult($result);
            if($last_topic_title == $subject)
            {
                trigger_error('FLOOD_ERROR');
            }
        }
        // Validate username
        if (($post_data['username'] && !$user->data['is_registered']))
        {
            include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);

            if (($result = validate_username($post_data['username'], (!empty($post_data['post_username'])) ? $post_data['post_username'] : '')) !== false)
            {
                $user->add_lang('ucp');
                trigger_error($result . '_USERNAME');
            }
        }

        if (sizeof($message_parser->warn_msg))
        {
            trigger_error(join("\n", $message_parser->warn_msg));
        }

        // DNSBL check
        if ($config['check_dnsbl'] && $mobiquo_config['check_dnsbl'])
        {
            if (($dnsbl = $user->check_dnsbl('post')) !== false)
            {
                trigger_error(sprintf(getSystemString('IP_BLACKLISTED'), $user->ip, $dnsbl[1]));
            }
        }

        // Store message, sync counters
        $data = array(
            'topic_title'           => (empty($post_data['topic_title'])) ? $post_data['post_subject'] : $post_data['topic_title'],
            'topic_first_post_id'   => (isset($post_data['topic_first_post_id'])) ? (int) $post_data['topic_first_post_id'] : 0,
            'topic_last_post_id'    => (isset($post_data['topic_last_post_id'])) ? (int) $post_data['topic_last_post_id'] : 0,
            'topic_time_limit'      => (int) $post_data['topic_time_limit'],
            'topic_attachment'      => (isset($post_data['topic_attachment'])) ? (int) $post_data['topic_attachment'] : 0,
            'post_id'               => 0,
            'topic_id'              => 0,
            'forum_id'              => (int) $forum_id,
            'icon_id'               => (int) $post_data['icon_id'],
            'poster_id'             => (int) $post_data['poster_id'],
            'enable_sig'            => (bool) $post_data['enable_sig'],
            'enable_bbcode'         => (bool) $post_data['enable_bbcode'],
            'enable_smilies'        => (bool) $post_data['enable_smilies'],
            'enable_urls'           => (bool) $post_data['enable_urls'],
            'enable_indexing'       => (bool) $post_data['enable_indexing'],
            'message_md5'           => (string) $message_md5,
            'post_time'             => (isset($post_data['post_time'])) ? (int) $post_data['post_time'] : $current_time,
            'post_checksum'         => (isset($post_data['post_checksum'])) ? (string) $post_data['post_checksum'] : '',
            'post_edit_reason'      => $post_data['post_edit_reason'],
            'post_edit_user'        => (isset($post_data['post_edit_user'])) ? (int) $post_data['post_edit_user'] : 0,
            'forum_parents'         => $post_data['forum_parents'],
            'forum_name'            => $post_data['forum_name'],
            'notify'                => $notify,
            'notify_set'            => $post_data['notify_set'],
            'poster_ip'             => (isset($post_data['poster_ip'])) ? $post_data['poster_ip'] : $user->ip,
            'post_edit_locked'      => (int) $post_data['post_edit_locked'],
            'bbcode_bitfield'       => $message_parser->bbcode_bitfield,
            'bbcode_uid'            => $message_parser->bbcode_uid,
            'message'               => $message_parser->message,
            'attachment_data'       => $message_parser->attachment_data,
            'filename_data'         => $message_parser->filename_data,
            'topic_approved'        => (isset($post_data['topic_approved'])) ? $post_data['topic_approved'] : false,
            'post_approved'         => (isset($post_data['post_approved'])) ? $post_data['post_approved'] : false,

            // for mod post expire compatibility
            'post_expire_time'      => -1,
            'topic_status'          => 0,
        );

        $poll = array(
            'poll_title'        => $post_data['poll_title'],
            'poll_option_text'  => $post_data['poll_option_text'],
            'poll_max_options'  => $post_data['poll_max_options'],
            'poll_vote_change'  => $post_data['poll_vote_change'],

            'poll_start'        => $post_data['poll_start'],
            'poll_length'       => $post_data['poll_length'],

            'poll_last_vote'    => $post_data['poll_last_vote'],

            'enable_bbcode'     => $post_data['enable_bbcode'],
            'enable_urls'       => $post_data['enable_urls'],
            'enable_smilies'    => $post_data['enable_smilies'],
            'img_status'        => $img_status
        );

        if (isset($oMbqEtForumTopic->oMbqEtPoll))
        {
            $oMbqEtPoll = $oMbqEtForumTopic->oMbqEtPoll;
            $poll['poll_title'] = $oMbqEtPoll->pollTitle->hasSetOriValue() ? $oMbqEtPoll->pollTitle->oriValue : $poll['poll_title'];
            $poll['poll_length'] = $oMbqEtPoll->pollLength->hasSetOriValue() ? ($oMbqEtPoll->pollLength->oriValue / 86400) : $poll['poll_length'];
            $poll['poll_max_options'] = $oMbqEtPoll->pollMaxOptions->hasSetOriValue() ? $oMbqEtPoll->pollMaxOptions->oriValue : $poll['poll_max_options'];
            $poll['poll_vote_change'] = $oMbqEtPoll->canRevoting->hasSetOriValue() ? $oMbqEtPoll->canRevoting->oriValue : $poll['poll_vote_change'];

            if ($oMbqEtPoll->pollOptions->hasSetOriValue()) {
                $poll['poll_option_text'] = '';
                foreach ($oMbqEtPoll->pollOptions->oriValue as $option) {
                    $poll['poll_option_text'] .= $option . "\n";
                }
            }

            $poll['poll_title'] = utf8_normalize_nfc($poll['poll_title']);
            $poll['poll_option_text'] = utf8_normalize_nfc($poll['poll_option_text']);

            $message_parser->parse_poll($poll);
        } else {
            $poll = array();
        }

        $post_data['poll_options'] = (isset($poll['poll_options'])) ? $poll['poll_options'] : array();
        $post_data['poll_title'] = (isset($poll['poll_title'])) ? $poll['poll_title'] : '';


        // $poll = array();
        include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

        $update_message = true;
        $cwd = getcwd();
        //chdir('../../../');
        $phpbb_root_path_tmp = $phpbb_root_path;
        $phpbb_root_path = './';
        $redirect_url = submit_post('post', $post_data['post_subject'], $post_data['username'], $post_data['topic_type'], $poll, $data, $update_message);
        chdir($cwd);
        $phpbb_root_path = $phpbb_root_path_tmp;

        // Check the permissions for post approval, as well as the queue trigger where users are put on approval with a post count lower than specified. Moderators are not affected.
        $approved = true;
        if (((isset($config['enable_queue_trigger']) && $config['enable_queue_trigger'] && $user->data['user_posts'] < $config['queue_trigger_posts']) || !$auth->acl_get('f_noapprove', $data['forum_id'])) && !$auth->acl_get('m_approve', $data['forum_id']))
        {
            $approved = false;
        }

        $posted_success = true;
        $topic_id = $data['topic_id'];
        if($approved == false)
        {
            $oMbqEtForumTopic->state->setOriValue(1);
        }
        $oMbqEtForumTopic->topicId->setOriValue($topic_id);
        if(!$posted_success)
        {
            MbqError::alert('', 'Create new topic failed!', '', MBQ_ERR_APP);
        }
        return $oMbqEtForumTopic;
    }

    /**
     * mark forum topic read
     */
    public function markForumTopicRead($oMbqEtForumTopic) {
        global $request_params, $db;
        $topic_data = $oMbqEtForumTopic->oMbqEtForum->mbqBind;
        markread('topic', $oMbqEtForumTopic->forumId->oriValue, $oMbqEtForumTopic->topicId->oriValue);
        update_forum_tracking_info($oMbqEtForumTopic->forumId->oriValue, $topic_data['forum_last_post_time'], (isset($topic_data['forum_mark_time'])) ? $topic_data['forum_mark_time'] : false, false);
        return true;
    }

    /**
     * reset forum topic subscription
     */
    public function resetForumTopicSubscription($oMbqEtForumTopic) {
    }
    /**
     * add forum topic view num
     */
    public function addForumTopicViewNum($oMbqEtForumTopic) {
        global $user,$db;
        $topic_id = $oMbqEtForumTopic->topicId->oriValue;
        $update_count = $oMbqEtForumTopic->attachmentIdArray->hasSetOriValue() ? $oMbqEtForumTopic->attachmentIdArray->oriValue : array();
        // Update topic view and if necessary attachment view counters ... but only for humans and if this is the first 'page view'
        if (isset($user->data['session_page']) && !$user->data['is_bot'] && (strpos($user->data['session_page'], '&t=' . $topic_id) === false || isset($user->data['session_created'])))
        {
            $sql = 'UPDATE ' . TOPICS_TABLE . '
		SET topic_views = topic_views + 1, topic_last_view_time = ' . time() . "
		WHERE topic_id = $topic_id";
            $db->sql_query($sql);

            // Update the attachment download counts
            if (sizeof($update_count))
            {
                $sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
			SET download_count = download_count + 1
			WHERE ' . $db->sql_in_set('attach_id', array_unique($update_count));
                $db->sql_query($sql);
            }
        }
    }

    public function subscribeTopic($oMbqEtForumTopic, $receiveEmail) {
        global $db, $user;
        $user->setup('viewtopic');
        $user_id = $user->data['user_id'];
        $topic_id = $oMbqEtForumTopic->topicId->oriValue;
        if ($user_id != ANONYMOUS)
        {
            $sql = 'SELECT notify_status
                FROM ' . TOPICS_WATCH_TABLE . "
                WHERE topic_id = $topic_id
                AND user_id = $user_id";
            $result = $db->sql_query($sql);

            $notify_status = ($row = $db->sql_fetchrow($result)) ? $row['notify_status'] : NULL;
            $db->sql_freeresult($result);

            if (!is_null($notify_status) && $notify_status !== '')
            {
                if ($notify_status)
                {
                    $sql = 'UPDATE ' . TOPICS_WATCH_TABLE . "
                        SET notify_status = 0
                        WHERE topic_id = $topic_id
                        AND user_id = $user_id";
                    $db->sql_query($sql);
                }
            }
            else
            {
                $sql = 'INSERT INTO ' . TOPICS_WATCH_TABLE . " (user_id, topic_id, notify_status)
                    VALUES ($user_id, $topic_id, 0)";
                $db->sql_query($sql);
            }
            return true;
        }
        return 'You are not allowed to do this operation';
    }

    public function unsubscribeTopic($oMbqEtForumTopic)
    {
        global $db, $user, $request_params;

        $user->setup('viewtopic');


        // get topic id from parameters
        $topic_id = $oMbqEtForumTopic->topicId->oriValue;
        if (!$topic_id) trigger_error('NO_TOPIC');
        $user_id = $user->data['user_id'];
        $uns_result = false;

        // Is user login?
        if ($user_id != ANONYMOUS)
        {
            $sql = 'SELECT notify_status
                FROM ' . TOPICS_WATCH_TABLE . "
                WHERE topic_id = $topic_id
                AND user_id = $user_id";
            $result = $db->sql_query($sql);

            $notify_status = ($row = $db->sql_fetchrow($result)) ? $row['notify_status'] : NULL;
            $db->sql_freeresult($result);

            if (!is_null($notify_status) && $notify_status !== '')
            {
                $sql = 'DELETE FROM ' . TOPICS_WATCH_TABLE . "
                WHERE topic_id = $topic_id
                    AND user_id = $user_id";
                $db->sql_query($sql);
                $uns_result = true;
            }
        }
        return true;
    }

    /**
     * m_close_topic
     */
    public function mCloseTopic($oMbqEtForumTopic, $actionmode) {
        global $user,$phpbb_root_path, $phpEx, $template, $request;



        include_once($phpbb_root_path . 'includes/mcp/mcp_main.' . $phpEx);
        if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
	{
        	include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
	}
        require_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);

        $user->setup('mcp');
        //setup fake template
        requireExtLibrary('fake_template');
        $template = new fake_template();
        if($actionmode == 1) //open
        {
            $action = 'unlock';
        }
        else if($actionmode == 2) //close
        {
            $action = 'lock';
        }
        $pmaster = new p_master();
        $mcpMain = new mcp_main($pmaster);
        lock_unlock($action, array($oMbqEtForumTopic->topicId->oriValue));
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
     * m_stick_topic
     */
    public function mStickTopic($oMbqEtForumTopic, $actionmode) {
        global $user,$phpbb_root_path, $phpEx, $template, $request;


        include_once($phpbb_root_path . 'includes/mcp/mcp_main.' . $phpEx);
        if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
	    {
        	    include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
	    }
        require_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);

        $user->setup('mcp');

        if($actionmode == 1) //stick
        {
            $action = 'make_sticky';
        }
        else if($actionmode == 2) //unstick
        {
            $action = 'make_normal';
        }
        //setup fake template
        requireExtLibrary('fake_template');
        $template = new fake_template();
        $pmaster = new p_master();
        $mcpMain = new mcp_main($pmaster);
        change_topic_type($action, array($oMbqEtForumTopic->topicId->oriValue));
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
     * m_rename_topic
     */
    public function mRenameTopic($oMbqEtForumTopic, $title) {
        global $db;

        $title = $db->sql_escape($title);
        $title = truncate_string($title, 120); // the same phpBB
        $sql = "UPDATE " .TOPICS_TABLE ." SET topic_title = '$title' WHERE topic_id = '" . $oMbqEtForumTopic->topicId->oriValue . "' ";
        $db->sql_query($sql);
        $sql = "UPDATE " . POSTS_TABLE . " SET post_subject = '$title' WHERE post_id = '" . $oMbqEtForumTopic->firstPostId->oriValue . "'";
        $db->sql_query($sql);
        return true;
    }

    /**
     * m_delete_topic
     */
    public function mDeleteTopic($oMbqEtForumTopic, $deletemode, $reason) {
        global $user,$phpbb_root_path, $phpEx, $template, $request;



        include_once($phpbb_root_path . 'includes/mcp/mcp_main.' . $phpEx);
        include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
        if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
        {
        	include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
        }
        include_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);

        $user->setup('mcp');

        if($deletemode == 1) //soft
        {
            $is_soft = true;
        }
        else if($deletemode == 2) //hard
        {
            $is_soft = false;
        }
        if(empty($reason))
        {
            $reason = 'not specified';
        }
        $pmaster = new p_master();
        $mcpMain = new mcp_main($pmaster);
        //setup fake template
        requireExtLibrary('fake_template');
        $template = new fake_template();
        mcp_delete_topic(array($oMbqEtForumTopic->topicId->oriValue), $is_soft, $reason);
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
     * m_approve_topic
     */
    public function mApproveTopic($oMbqEtForumTopic, $approvemode) {
        if($approvemode == 1)
        {
            global $user,$phpbb_root_path, $phpEx, $request;
            global $action;

            //setup fake template
            requireExtLibrary('fake_template');
            $template = new fake_template();



            include_once($phpbb_root_path . 'includes/mcp/mcp_queue.' . $phpEx);
            if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
            {
                include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
            }
            include_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);

            $action = 'approve';
            overwriteRequestParam('topic_id_list', array($oMbqEtForumTopic->topicId->oriValue));
            $user->setup('mcp');
            $pmaster=new p_master();
            $mcp_queue = new mcp_queue($pmaster);
            $mcp_queue->main(null, null);
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
        else if($approvemode == 2)
        {
            global $user,$phpbb_root_path, $phpEx, $request;
            global $action;

            include_once($phpbb_root_path . 'includes/mcp/mcp_queue.' . $phpEx);
            if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
            {
                include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
            }
            include_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);

            $action = 'disapprove';
            overwriteRequestParam('topic_id_list', array($oMbqEtForumTopic->topicId->oriValue));
            $user->setup('mcp');
            $pmaster=new p_master();
            $mcp_queue = new mcp_queue($pmaster);
            //setup fake template
            requireExtLibrary('fake_template');
            $template = new fake_template();
            $mcp_queue->main(null, null);
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

    /**
     * m_undelete_topic
     */
    public function mUndeleteTopic($oMbqEtForumTopic) {
        global $user,$phpbb_root_path, $phpEx;

        include_once($phpbb_root_path . 'includes/mcp/mcp_queue.' . $phpEx);
        if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
        {
        	include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
        }
        include_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);

        $user->setup('mcp');
        $pmaster=new p_master();
        $mcp_queue = new mcp_queue($pmaster);
        //setup fake template
        requireExtLibrary('fake_template');
        $template = new fake_template();    $mcp_queue->approve_topics('restore', array($oMbqEtForumTopic->topicId->oriValue), null, null);
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
     * m_move_topic
     */
    public function mMoveTopic($oMbqEtForumTopic, $oMbqEtForum, $redirect) {
        global $user,$phpbb_root_path, $phpEx, $template, $request;

        include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
        $user->setup('mcp');
        $topicIds = array();
        $topicIds[] = $oMbqEtForumTopic->topicId->oriValue;
        //setup fake template
        requireExtLibrary('fake_template');
        $template = new fake_template();
        move_topics($topicIds, $oMbqEtForum->forumId->oriValue);
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
     * m_merge_topic
     */
    public function mMergeTopic($oMbqEtForumTopicFrom, $oMbqEtForumTopicTo, $redirect) {
        global $user,$phpbb_root_path, $phpEx, $template, $request;

        include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
        if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
        {
        	include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
        }
        include_once($phpbb_root_path . 'includes/mcp/mcp_forum.' . $phpEx);
        $user->setup('mcp');
        //setup fake template
        requireExtLibrary('fake_template');
        $template = new fake_template();
        merge_topics(null, array($oMbqEtForumTopicFrom->topicId->oriValue), $oMbqEtForumTopicTo->topicId->oriValue);
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
