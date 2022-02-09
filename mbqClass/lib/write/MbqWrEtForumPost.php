<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrEtForumPost');

/**
 * forum post write class
 */
Class MbqWrEtForumPost extends MbqBaseWrEtForumPost {

    public function __construct() {
    }
    /**
     * add forum post
     */
    public function addMbqEtForumPost($oMbqEtForumPost) {

        global $db, $auth, $user, $config, $phpbb_root_path, $phpEx, $mobiquo_config, $phpbb_home, $request;
        require_once MBQ_APPEXTENTION_PATH .'emoji.php';
        $user->setup('posting');
        // get parameters
        $forum_id   = $oMbqEtForumPost->forumId->oriValue;
        $topic_id   = $oMbqEtForumPost->topicId->oriValue;
        $subject    = $oMbqEtForumPost->postTitle->oriValue;
        $text_body  = $oMbqEtForumPost->postContent->oriValue;
        if(MbqMain::$oMbqConfig->getCfg('user.emoji_support')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.user.emoji_support.range.support'))
        {
            $text_body = emoji_unified_to_names($text_body);
        }
        $attach_list = $oMbqEtForumPost->attachmentIdArray->hasSetOriValue() ? $oMbqEtForumPost->attachmentIdArray->oriValue : array();
        $attachment_data = $oMbqEtForumPost->groupId->hasSetOriValue() ? unserialize(base64_decode($oMbqEtForumPost->groupId->oriValue)) : array();
        overwriteRequestParam('attachment_data', $attachment_data, \phpbb\request\request_interface::POST);

        if (!$topic_id) trigger_error('NO_TOPIC');
        if (utf8_clean_string($text_body) === '') trigger_error('TOO_FEW_CHARS');

        $post_data = array();
        $current_time = time();

        // get topic data
        $sql = 'SELECT *
            FROM ' . TOPICS_TABLE . '
            WHERE topic_id = ' . $topic_id;
        $result = $db->sql_query($sql);
        $post_data = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // get forum data
        $sql = 'SELECT *
            FROM ' . FORUMS_TABLE . "
            WHERE forum_type = " . FORUM_POST . ($post_data['forum_id'] ? "
            AND forum_id = '$post_data[forum_id]' " : '');
        $result = $db->sql_query_limit($sql, 1);
        $forum_data = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        $post_data = array_merge($post_data, $forum_data);

        $subject = ((strpos($subject, 'Re: ') !== 0) ? 'Re: ' : '') . ($subject ? $subject : censor_text($post_data['topic_title']));

        $post_data['post_edit_locked']  = (isset($post_data['post_edit_locked'])) ? (int) $post_data['post_edit_locked'] : 0;
        $post_data['post_subject']      = (isset($post_data['topic_title'])) ? $post_data['topic_title'] : '';
        $post_data['topic_time_limit']  = (isset($post_data['topic_time_limit'])) ? (($post_data['topic_time_limit']) ? (int) $post_data['topic_time_limit'] / 86400 : (int) $post_data['topic_time_limit']) : 0;
        $post_data['poll_length']       = (!empty($post_data['poll_length'])) ? (int) $post_data['poll_length'] / 86400 : 0;
        $post_data['poll_start']        = (!empty($post_data['poll_start'])) ? (int) $post_data['poll_start'] : 0;
        $post_data['icon_id']           = 0;
        $post_data['poll_options']      = array();

        // Get Poll Data
        if ($post_data['poll_start'])
        {
            $sql = 'SELECT poll_option_text
            FROM ' . POLL_OPTIONS_TABLE . "
            WHERE topic_id = $topic_id
            ORDER BY poll_option_id";
            $result = $db->sql_query($sql);

            while ($row = $db->sql_fetchrow($result))
            {
                $post_data['poll_options'][] = trim($row['poll_option_text']);
            }
            $db->sql_freeresult($result);
        }

        $orig_poll_options_size = sizeof($post_data['poll_options']);

        include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
        $message_parser = new parse_message();

        // Set some default variables
        $uninit = array('post_attachment' => 0, 'poster_id' => $user->data['user_id'], 'enable_magic_url' => 0, 'topic_status' => 0, 'topic_type' => POST_NORMAL, 'post_subject' => '', 'topic_title' => '', 'post_time' => 0, 'post_edit_reason' => '', 'notify_set' => 0);

        foreach ($uninit as $var_name => $default_value)
        {
            if (!isset($post_data[$var_name]))
            {
                $post_data[$var_name] = $default_value;
            }
        }
        unset($uninit);

        // Always check if the submitted attachment data is valid and belongs to the user.
        // Further down (especially in submit_post()) we do not check this again.
        $message_parser->get_submitted_attachment_data($post_data['poster_id']);

        $post_data['username']       = '';
        // guest reply post
        if (!$user->data['is_registered'] && isset($oMbqEtForumPost->oMbqEtForum->mbqBind['guest_username'])) {
            $post_data['username'] = $oMbqEtForumPost->oMbqEtForum->mbqBind['guest_username'];
        }
        $post_data['enable_urls']    = $post_data['enable_magic_url'];
        $post_data['enable_sig']     = ($config['allow_sig'] && $user->optionget('attachsig')) ? true: false;
        $post_data['enable_smilies'] = ($config['allow_smilies'] && $user->optionget('smilies')) ? true : false;
        $post_data['enable_bbcode']  = ($config['allow_bbcode'] && $user->optionget('bbcode')) ? true : false;
        $post_data['enable_urls']    = true;

        $post_data['enable_magic_url'] = $post_data['drafts'] = false;

        $check_value = (($post_data['enable_bbcode']+1) << 8) + (($post_data['enable_smilies']+1) << 4) + (($post_data['enable_urls']+1) << 2) + (($post_data['enable_sig']+1) << 1);

        // Check if user is watching this topic
        if ($config['allow_topic_notify'] && $user->data['is_registered'])
        {
            $sql = 'SELECT topic_id
                FROM ' . TOPICS_WATCH_TABLE . '
                WHERE topic_id = ' . $topic_id . '
                AND user_id = ' . $user->data['user_id'];
            $result = $db->sql_query($sql);
            $post_data['notify_set'] = (int) $db->sql_fetchfield('topic_id');
            $db->sql_freeresult($result);
        }

        // HTML, BBCode, Smilies, Images and Flash status
        $bbcode_status  = ($config['allow_bbcode'] && $auth->acl_get('f_bbcode', $forum_id)) ? true : false;
        $smilies_status = ($bbcode_status && $config['allow_smilies'] && $auth->acl_get('f_smilies', $forum_id)) ? true : false;
        $img_status     = ($bbcode_status && $auth->acl_get('f_img', $forum_id)) ? true : false;
        $url_status     = ($config['allow_post_links']) ? true : false;
        $flash_status   = ($bbcode_status && $auth->acl_get('f_flash', $forum_id) && $config['allow_post_flash']) ? true : false;
        $quote_status   = ($auth->acl_get('f_reply', $forum_id)) ? true : false;

        $post_data['topic_cur_post_id'] = request_var('topic_cur_post_id', 0);
        $post_data['post_subject']      = utf8_normalize_nfc($subject);
        $message_parser->message        = utf8_normalize_nfc(htmlspecialchars($text_body));
        $post_data['username']          = utf8_normalize_nfc(request_var('username', $post_data['username'], true));
        $post_data['post_edit_reason']  = '';

        $post_data['orig_topic_type']   = $post_data['topic_type'];
        $post_data['topic_type']        = request_var('topic_type', (int) $post_data['topic_type']);
        $post_data['topic_time_limit']  = request_var('topic_time_limit', (int) $post_data['topic_time_limit']);
        $post_data['icon_id']           = request_var('icon', 0);

        $post_data['enable_bbcode']     = (!$bbcode_status || isset($_POST['disable_bbcode'])) ? false : true;
        $post_data['enable_smilies']    = (!$smilies_status || isset($_POST['disable_smilies'])) ? false : true;
        $post_data['enable_urls']       = (isset($_POST['disable_magic_url'])) ? 0 : 1;
        $post_data['enable_sig']        = (!$config['allow_sig'] || !$auth->acl_get('f_sigs', $forum_id) || !$auth->acl_get('u_sig')) ? false : ($user->data['is_registered'] ? true : false);

        if ($config['allow_topic_notify'] && $user->data['is_registered'])
        {
            $notify = (!$post_data['notify_set'] ? $user->data['user_notify'] : $post_data['notify_set']) ? true : false;
        }
        else
        {
            $notify = false;
        }

        $post_data['poll_title']        = utf8_normalize_nfc(request_var('poll_title', '', true));
        $post_data['poll_length']       = request_var('poll_length', 0);
        $post_data['poll_option_text']  = utf8_normalize_nfc(request_var('poll_option_text', '', true));
        $post_data['poll_max_options']  = request_var('poll_max_options', 1);
        $post_data['poll_vote_change']  = ($auth->acl_get('f_votechg', $forum_id) && isset($_POST['poll_vote_change'])) ? 1 : 0;

        // Parse Attachments - before checksum is calculated
        $message_parser->parse_attachments('fileupload', 'reply', $forum_id, true, false, false);

        // Grab md5 'checksum' of new message
        $message_md5 = md5($message_parser->message);

        // Check checksum ... don't re-parse message if the same
        if (sizeof($message_parser->warn_msg))
        {
            trigger_error(join("\n", $message_parser->warn_msg));
        }

        $message_parser->parse($post_data['enable_bbcode'], ($config['allow_post_links']) ? $post_data['enable_urls'] : false, $post_data['enable_smilies'], $img_status, $flash_status, $quote_status, $config['allow_post_links']);

        if ($config['flood_interval'])
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
        $sql = 'SELECT post_checksum FROM  ' . POSTS_TABLE . ' WHERE poster_id = ' .  $user->data['user_id'] . ' ORDER BY post_id DESC';
        $result = $db->sql_query_limit($sql,1);
        $row = $db->sql_fetchrow($result);
        if($row && $row['post_checksum'] == $message_md5)
        {
            trigger_error('FLOOD_ERROR');
        }
        $db->sql_freeresult($result);
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

        $post_data['poll_last_vote'] = (isset($post_data['poll_last_vote'])) ? $post_data['poll_last_vote'] : 0;

        $poll = array();

        //    if (sizeof($message_parser->warn_msg))
        //    {
        //        return get_error();
        //    }

        // DNSBL check
        if ($config['check_dnsbl'] && $mobiquo_config['check_dnsbl'])
        {
            if (($dnsbl = $user->check_dnsbl('post')) !== false)
            {
                trigger_error(sprintf($user->lang['IP_BLACKLISTED'], $user->ip, $dnsbl[1]));
            }
        }

        // Store message, sync counters
        $data = array(
            'topic_title'         => (empty($post_data['topic_title'])) ? $post_data['post_subject'] : $post_data['topic_title'],
            'topic_first_post_id' => (isset($post_data['topic_first_post_id'])) ? (int) $post_data['topic_first_post_id'] : 0,
            'topic_last_post_id'  => (isset($post_data['topic_last_post_id'])) ? (int) $post_data['topic_last_post_id'] : 0,
            'topic_time_limit'    => (int) $post_data['topic_time_limit'],
            'topic_attachment'    => (isset($post_data['topic_attachment'])) ? (int) $post_data['topic_attachment'] : 0,
            'post_id'             => 0,
            'topic_id'            => (int) $topic_id,
            'forum_id'            => (int) $forum_id,
            'icon_id'             => (int) $post_data['icon_id'],
            'poster_id'           => (int) $post_data['poster_id'],
            'enable_sig'          => (bool) $post_data['enable_sig'],
            'enable_bbcode'       => (bool) $post_data['enable_bbcode'],
            'enable_smilies'      => (bool) $post_data['enable_smilies'],
            'enable_urls'         => (bool) $post_data['enable_urls'],
            'enable_indexing'     => (bool) $post_data['enable_indexing'],
            'message_md5'         => (string) $message_md5,
            'post_time'           => (isset($post_data['post_time'])) ? (int) $post_data['post_time'] : $current_time,
            'post_checksum'       => (isset($post_data['post_checksum'])) ? (string) $post_data['post_checksum'] : '',
            'post_edit_reason'    => $post_data['post_edit_reason'],
            'post_edit_user'      => (isset($post_data['post_edit_user']) ? (int) $post_data['post_edit_user'] : 0),
            'forum_parents'       => $post_data['forum_parents'],
            'forum_name'          => $post_data['forum_name'],
            'notify'              => $notify,
            'notify_set'          => $post_data['notify_set'],
            'poster_ip'           => (isset($post_data['poster_ip'])) ? $post_data['poster_ip'] : $user->ip,
            'post_edit_locked'    => (int) $post_data['post_edit_locked'],
            'bbcode_bitfield'     => $message_parser->bbcode_bitfield,
            'bbcode_uid'          => $message_parser->bbcode_uid,
            'message'             => $message_parser->message,
            'attachment_data'     => $message_parser->attachment_data,
            'filename_data'       => $message_parser->filename_data,

            'topic_approved'      => (isset($post_data['topic_approved'])) ? $post_data['topic_approved'] : false,
            'post_approved'       => (isset($post_data['post_approved'])) ? $post_data['post_approved'] : false,

            // for mod post expire compatibility
            'post_expire_time'      => -1,
        );

        include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

        $update_message = true;
        $phpbb_root_path_tmp = $phpbb_root_path;
        $phpbb_root_path = './';
        preg_match_all('/quote=&quot;(.*?)&quot;/is', $data['message'],$quote_matches);
        if(!empty($quote_matches['1']))
        {
            $postmode = 'quote';
        }
        else
        {
            $postmode = 'reply';
        }
        $redirect_url = submit_post($postmode, $post_data['post_subject'], $post_data['username'], $post_data['topic_type'], $poll, $data, $update_message);
        $phpbb_root_path = $phpbb_root_path_tmp;

        // Check the permissions for post approval, as well as the queue trigger where users are put on approval with a post count lower than specified. Moderators are not affected.
        $approved = true;
        if (((isset($config['enable_queue_trigger']) && $config['enable_queue_trigger'] && $user->data['user_posts'] < $config['queue_trigger_posts']) || !$auth->acl_get('f_noapprove', $data['forum_id'])) && !$auth->acl_get('m_approve', $data['forum_id']))
        {
            $approved = false;
        }
        $reply_success = true;
        if($approved == false)
        {
            $oMbqEtForumPost->state->setOriValue(1);
        }
        $oMbqEtForumPost->postId->setOriValue($data['post_id']);

        return $oMbqEtForumPost;
    }

    public function mdfMbqEtForumPost($oMbqEtForumPost, $oMbqOpt) {
        global $db, $auth, $user, $config, $phpbb_root_path, $phpEx, $mobiquo_config, $phpbb_home;
        $user->setup('posting');

        include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);


        $submit     = true;
        $preview    = false;
        $refresh    = false;
        $postmode       = 'edit';

        // get post information from parameters
        $post_id        = $oMbqEtForumPost->postId->oriValue;
        $post_title     = $oMbqEtForumPost->postTitle->oriValue;
        $post_content   = $oMbqEtForumPost->postContent->oriValue;
        $attach_list = $oMbqOpt['in']->attachmentIds;
        $_POST['attachment_data'] = $oMbqOpt['in']->groupId;
        $_POST['edit_reason'] = $oMbqOpt['in']->reason;

        $post_data = array();

        $sql = 'SELECT p.*, t.*, f.*, u.username
            FROM ' . POSTS_TABLE . ' p
                LEFT JOIN ' . TOPICS_TABLE . ' t ON (p.topic_id = t.topic_id)
                LEFT JOIN ' . FORUMS_TABLE . ' f ON (t.forum_id = f.forum_id OR (t.topic_type = ' . POST_GLOBAL . ' AND f.forum_type = ' . FORUM_POST . '))
                LEFT JOIN ' . USERS_TABLE  . ' u ON (p.poster_id = u.user_id)' . "
            WHERE p.post_id = $post_id";

        $result = $db->sql_query_limit($sql, 1);
        $post_data = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        if (!$post_data) trigger_error('NO_POST');
        if(empty($post_title))
        {
            $post_title = $post_data['post_subject'];
        }
        // Use post_row values in favor of submitted ones...
        $forum_id = (int) $post_data['forum_id'];
        $topic_id = (int) $post_data['topic_id'];
        $post_id  = (int) $post_id;

        // Determine some vars
        if (isset($post_data['poster_id']) && $post_data['poster_id'] == ANONYMOUS)
        {
            $post_data['quote_username'] = (!empty($post_data['post_username'])) ? $post_data['post_username'] : $user->lang['GUEST'];
        }
        else
        {
            $post_data['quote_username'] = isset($post_data['username']) ? $post_data['username'] : '';
        }

        $post_data['post_edit_locked']    = (isset($post_data['post_edit_locked'])) ? (int) $post_data['post_edit_locked'] : 0;
        $post_data['post_subject']        = (in_array($postmode, array('quote', 'edit'))) ? $post_data['post_subject'] : ((isset($post_data['topic_title'])) ? $post_data['topic_title'] : '');
        $post_data['topic_time_limit']    = (isset($post_data['topic_time_limit'])) ? (($post_data['topic_time_limit']) ? (int) $post_data['topic_time_limit'] / 86400 : (int) $post_data['topic_time_limit']) : 0;
        $post_data['poll_length']        = (!empty($post_data['poll_length'])) ? (int) $post_data['poll_length'] / 86400 : 0;
        $post_data['poll_start']        = (!empty($post_data['poll_start'])) ? (int) $post_data['poll_start'] : 0;
        $post_data['icon_id']            = (!isset($post_data['icon_id']) || in_array($postmode, array('quote', 'reply'))) ? 0 : (int) $post_data['icon_id'];
        $post_data['poll_options']        = array();

        // Get Poll Data
        if ($post_data['poll_start'])
        {
            $sql = 'SELECT poll_option_text
            FROM ' . POLL_OPTIONS_TABLE . "
            WHERE topic_id = $topic_id
            ORDER BY poll_option_id";
            $result = $db->sql_query($sql);

            while ($row = $db->sql_fetchrow($result))
            {
                $post_data['poll_options'][] = trim($row['poll_option_text']);
            }
            $db->sql_freeresult($result);
        }

        $orig_poll_options_size = sizeof($post_data['poll_options']);

        $message_parser = new parse_message();

        if (isset($post_data['post_text']))
        {
            $message_parser->message = &$post_data['post_text'];
            unset($post_data['post_text']);
        }

        // Set some default variables
        $uninit = array('post_attachment' => 0, 'poster_id' => $user->data['user_id'], 'enable_magic_url' => 0, 'topic_status' => 0, 'topic_type' => POST_NORMAL, 'post_subject' => '', 'topic_title' => '', 'post_time' => 0, 'post_edit_reason' => '', 'notify_set' => 0);

        foreach ($uninit as $var_name => $default_value)
        {
            if (!isset($post_data[$var_name]))
            {
                $post_data[$var_name] = $default_value;
            }
        }
        unset($uninit);

        // Always check if the submitted attachment data is valid and belongs to the user.
        // Further down (especially in submit_post()) we do not check this again.
        $message_parser->get_submitted_attachment_data($post_data['poster_id']);

        if ($post_data['post_attachment'] && !$refresh && !$preview && $postmode == 'edit')
        {
            // Do not change to SELECT *
            $sql = 'SELECT attach_id, is_orphan, attach_comment, real_filename
            FROM ' . ATTACHMENTS_TABLE . "
            WHERE post_msg_id = $post_id
                AND in_message = 0
                AND is_orphan = 0
            ORDER BY filetime DESC";
            $result = $db->sql_query($sql);
            $message_parser->attachment_data = array_merge($message_parser->attachment_data, $db->sql_fetchrowset($result));
            $db->sql_freeresult($result);
        }

        if ($post_data['poster_id'] == ANONYMOUS)
        {
            $post_data['username'] = ($postmode == 'quote' || $postmode == 'edit') ? trim($post_data['post_username']) : '';
        }
        else
        {
            $post_data['username'] = ($postmode == 'quote' || $postmode == 'edit') ? trim($post_data['username']) : '';
        }

        $post_data['enable_urls'] = $post_data['enable_magic_url'];

        $post_data['enable_magic_url'] = $post_data['drafts'] = false;

        $check_value = (($post_data['enable_bbcode']+1) << 8) + (($post_data['enable_smilies']+1) << 4) + (($post_data['enable_urls']+1) << 2) + (($post_data['enable_sig']+1) << 1);

        // Check if user is watching this topic
        /*if ($postmode != 'post' && $config['allow_topic_notify'] && $user->data['is_registered'])
        {
        $sql = 'SELECT topic_id
        FROM ' . TOPICS_WATCH_TABLE . '
        WHERE topic_id = ' . $topic_id . '
        AND user_id = ' . $user->data['user_id'];
        $result = $db->sql_query($sql);
        $post_data['notify_set'] = (int) $db->sql_fetchfield('topic_id');
        $db->sql_freeresult($result);
        }*/

        // Do we want to edit our post ?
        if ($post_data['bbcode_uid'])
        {
            $message_parser->bbcode_uid = $post_data['bbcode_uid'];
        }

        // HTML, BBCode, Smilies, Images and Flash status
        $bbcode_status    = ($config['allow_bbcode'] && $auth->acl_get('f_bbcode', $forum_id)) ? true : false;
        $smilies_status    = ($bbcode_status && $config['allow_smilies'] && $auth->acl_get('f_smilies', $forum_id)) ? true : false;
        $img_status        = ($bbcode_status && $auth->acl_get('f_img', $forum_id)) ? true : false;
        $url_status        = ($config['allow_post_links']) ? true : false;
        $flash_status    = ($bbcode_status && $auth->acl_get('f_flash', $forum_id) && $config['allow_post_flash']) ? true : false;
        $quote_status    = ($auth->acl_get('f_reply', $forum_id)) ? true : false;

        $solved_captcha = false;

        $post_data['topic_cur_post_id']    = request_var('topic_cur_post_id', 0);
        $post_data['post_subject']        = utf8_normalize_nfc($post_title);
        $message_parser->message        = utf8_normalize_nfc(htmlspecialchars($post_content));

        $post_data['username']            = utf8_normalize_nfc(request_var('username', $post_data['username'], true));
        $post_data['post_edit_reason']    = (!empty($_POST['edit_reason']) && $postmode == 'edit' && $auth->acl_get('m_edit', $forum_id)) ? utf8_normalize_nfc($_POST['edit_reason']) : '';

        $post_data['orig_topic_type']    = $post_data['topic_type'];
        $post_data['topic_type']        = request_var('topic_type', (($postmode != 'post') ? (int) $post_data['topic_type'] : POST_NORMAL));
        $post_data['topic_time_limit']    = request_var('topic_time_limit', (($postmode != 'post') ? (int) $post_data['topic_time_limit'] : 0));
        $post_data['icon_id']            = request_var('icon', 0);

        $post_data['enable_bbcode']        = (!$bbcode_status || isset($_POST['disable_bbcode'])) ? false : true;
        $post_data['enable_smilies']    = (!$smilies_status || isset($_POST['disable_smilies'])) ? false : true;
        $post_data['enable_urls']        = (isset($_POST['disable_magic_url'])) ? 0 : 1;
        $post_data['enable_sig']        = (!$config['allow_sig'] || !$auth->acl_get('f_sigs', $forum_id) || !$auth->acl_get('u_sig')) ? false : ($user->data['is_registered'] ? true : false);

        if ($config['allow_topic_notify'] && $user->data['is_registered'])
        {
            $notify = (isset($_POST['notify'])) ? true : false;
        }
        else
        {
            $notify = false;
        }

        $topic_lock     = (isset($_POST['lock_topic'])) ? true : $post_data['topic_status'];
        $post_lock      = (isset($_POST['lock_post'])) ? true : $post_data['post_edit_locked'];
        $poll_delete    = (isset($_POST['poll_delete'])) ? true : false;

        $status_switch = (($post_data['enable_bbcode']+1) << 8) + (($post_data['enable_smilies']+1) << 4) + (($post_data['enable_urls']+1) << 2) + (($post_data['enable_sig']+1) << 1);
        $status_switch = ($status_switch != $check_value);

        //$post_data['poll_title']        = utf8_normalize_nfc(request_var('poll_title', '', true));
        //$post_data['poll_length']        = request_var('poll_length', 0);
        //$post_data['poll_option_text']    = utf8_normalize_nfc(request_var('poll_option_text', '', true));
        $post_data['poll_option_text'] = implode("\n", $post_data['poll_options']);
        //$post_data['poll_max_options']    = request_var('poll_max_options', 1);
        //$post_data['poll_vote_change']    = ($auth->acl_get('f_votechg', $forum_id) && isset($_POST['poll_vote_change'])) ? 1 : 0;

        // Parse Attachments - before checksum is calculated
        $message_parser->parse_attachments('fileupload', $postmode, $forum_id, $submit, $preview, $refresh);

        // Grab md5 'checksum' of new message
        $message_md5 = md5($message_parser->message);

        // Check checksum ... don't re-parse message if the same
        $update_message = ($postmode != 'edit' || $message_md5 != $post_data['post_checksum'] || $status_switch || strlen($post_data['bbcode_uid']) < BBCODE_UID_LEN) ? true : false;

        // Parse message
        if ($update_message)
        {
            if (sizeof($message_parser->warn_msg))
            {
                trigger_error(join("\n", $message_parser->warn_msg));
            }

            $message_parser->parse($post_data['enable_bbcode'], ($config['allow_post_links']) ? $post_data['enable_urls'] : false, $post_data['enable_smilies'], $img_status, $flash_status, $quote_status, $config['allow_post_links']);
        }
        else
        {
            $message_parser->bbcode_bitfield = $post_data['bbcode_bitfield'];
        }

        // Validate username
        if (($post_data['username'] && !$user->data['is_registered']) || ($postmode == 'edit' && $post_data['poster_id'] == ANONYMOUS && $post_data['username'] && $post_data['post_username'] && $post_data['post_username'] != $post_data['username']))
        {
            include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);

            if (($result = validate_username($post_data['username'], (!empty($post_data['post_username'])) ? $post_data['post_username'] : '')) !== false)
            {
                $user->add_lang('ucp');
                trigger_error($result . '_USERNAME');
            }
        }

        // Parse subject
        if (utf8_clean_string($post_data['post_subject']) === '' && $post_data['topic_first_post_id'] == $post_id)
        {
            trigger_error('EMPTY_SUBJECT');
        }

        $post_data['poll_last_vote'] = (isset($post_data['poll_last_vote'])) ? $post_data['poll_last_vote'] : 0;

        if ($post_data['poll_option_text'] && $post_id == $post_data['topic_first_post_id']
            && $auth->acl_get('f_poll', $forum_id))
        {
            $poll = array(
                'poll_title'        => $post_data['poll_title'],
                'poll_length'        => $post_data['poll_length'],
                'poll_max_options'    => $post_data['poll_max_options'],
                'poll_option_text'    => $post_data['poll_option_text'],
                'poll_start'        => $post_data['poll_start'],
                'poll_last_vote'    => $post_data['poll_last_vote'],
                'poll_vote_change'    => $post_data['poll_vote_change'],
                'enable_bbcode'        => $post_data['enable_bbcode'],
                'enable_urls'        => $post_data['enable_urls'],
                'enable_smilies'    => $post_data['enable_smilies'],
                'img_status'        => $img_status
            );

            $message_parser->parse_poll($poll);

            $post_data['poll_options'] = (isset($poll['poll_options'])) ? $poll['poll_options'] : '';
            $post_data['poll_title'] = (isset($poll['poll_title'])) ? $poll['poll_title'] : '';
        }
        else
        {
            $poll = array();
        }

        // Check topic type
        if ($post_data['topic_type'] != POST_NORMAL && $post_data['topic_first_post_id'] == $post_id)
        {
            switch ($post_data['topic_type'])
            {
                case POST_GLOBAL:
                case POST_ANNOUNCE:
                    $auth_option = 'f_announce';
                    break;

                case POST_STICKY:
                    $auth_option = 'f_sticky';
                    break;

                default:
                    $auth_option = '';
                    break;
            }

            if (!$auth->acl_get($auth_option, $forum_id))
            {
                // There is a special case where a user edits his post whereby the topic type got changed by an admin/mod.
                // Another case would be a mod not having sticky permissions for example but edit permissions.
                // To prevent non-authed users messing around with the topic type we reset it to the original one.
                $post_data['topic_type'] = $post_data['orig_topic_type'];
            }
        }

        // DNSBL check
        if ($config['check_dnsbl'])
        {
            if (($dnsbl = $user->check_dnsbl('post')) !== false)
            {
                trigger_error(sprintf($user->lang['IP_BLACKLISTED'], $user->ip, $dnsbl[1]));
            }
        }

        // Check if we want to de-globalize the topic... and ask for new forum
        if ($post_data['topic_type'] != POST_GLOBAL)
        {
            $sql = 'SELECT topic_type, forum_id
            FROM ' . TOPICS_TABLE . "
            WHERE topic_id = $topic_id";
            $result = $db->sql_query($sql);
            $row = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);

            if ($row && !$row['forum_id'] && $row['topic_type'] == POST_GLOBAL)
            {
                $to_forum_id = request_var('to_forum_id', 0);

                if ($to_forum_id)
                {
                    $sql = 'SELECT forum_type
                    FROM ' . FORUMS_TABLE . '
                    WHERE forum_id = ' . $to_forum_id;
                    $result = $db->sql_query($sql);
                    $forum_type = (int) $db->sql_fetchfield('forum_type');
                    $db->sql_freeresult($result);

                    if ($forum_type != FORUM_POST || !$auth->acl_get('f_post', $to_forum_id))
                    {
                        $to_forum_id = 0;
                    }
                }

                if (!$to_forum_id)
                {
                    include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

                    $template->assign_vars(array(
                        'S_FORUM_SELECT'    => make_forum_select(false, false, false, true, true, true),
                        'S_UNGLOBALISE'        => true)
                    );

                    $submit = false;
                    $refresh = true;
                }
                else
                {
                    if (!$auth->acl_get('f_post', $to_forum_id))
                    {
                        // This will only be triggered if the user tried to trick the forum.
                        trigger_error('NOT_AUTHORISED');
                    }

                    $forum_id = $to_forum_id;
                }
            }
        }

        // Lock/Unlock Topic
        $change_topic_status = $post_data['topic_status'];
        $perm_lock_unlock = ($auth->acl_get('m_lock', $forum_id) || ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && !empty($post_data['topic_poster']) && $user->data['user_id'] == $post_data['topic_poster'] && $post_data['topic_status'] == ITEM_UNLOCKED)) ? true : false;

        if ($post_data['topic_status'] == ITEM_LOCKED && !$topic_lock && $perm_lock_unlock)
        {
            $change_topic_status = ITEM_UNLOCKED;
        }
        else if ($post_data['topic_status'] == ITEM_UNLOCKED && $topic_lock && $perm_lock_unlock)
        {
            $change_topic_status = ITEM_LOCKED;
        }

        if ($change_topic_status != $post_data['topic_status'])
        {
            $sql = 'UPDATE ' . TOPICS_TABLE . "
            SET topic_status = $change_topic_status
            WHERE topic_id = $topic_id
                AND topic_moved_id = 0";
            $db->sql_query($sql);

            $user_lock = ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && $user->data['user_id'] == $post_data['topic_poster']) ? 'USER_' : '';

            add_log('mod', $forum_id, $topic_id, 'LOG_' . $user_lock . (($change_topic_status == ITEM_LOCKED) ? 'LOCK' : 'UNLOCK'), $post_data['topic_title']);
        }

        // Lock/Unlock Post Edit
        if ($postmode == 'edit' && $post_data['post_edit_locked'] == ITEM_LOCKED && !$post_lock && $auth->acl_get('m_edit', $forum_id))
        {
            $post_data['post_edit_locked'] = ITEM_UNLOCKED;
        }
        else if ($postmode == 'edit' && $post_data['post_edit_locked'] == ITEM_UNLOCKED && $post_lock && $auth->acl_get('m_edit', $forum_id))
        {
            $post_data['post_edit_locked'] = ITEM_LOCKED;
        }

        $data = array(
            'topic_title'            => (empty($post_data['topic_title'])) ? $post_data['post_subject'] : $post_data['topic_title'],
            'topic_first_post_id'    => (isset($post_data['topic_first_post_id'])) ? (int) $post_data['topic_first_post_id'] : 0,
            'topic_last_post_id'    => (isset($post_data['topic_last_post_id'])) ? (int) $post_data['topic_last_post_id'] : 0,
            'topic_time_limit'        => (int) $post_data['topic_time_limit'],
            'topic_attachment'        => (isset($post_data['topic_attachment'])) ? (int) $post_data['topic_attachment'] : 0,
            'post_id'                => (int) $post_id,
            'topic_id'                => (int) $topic_id,
            'forum_id'                => (int) $forum_id,
            'icon_id'                => (int) $post_data['icon_id'],
            'poster_id'                => (int) $post_data['poster_id'],
            'enable_sig'            => (bool) $post_data['enable_sig'],
            'enable_bbcode'            => (bool) $post_data['enable_bbcode'],
            'enable_smilies'        => (bool) $post_data['enable_smilies'],
            'enable_urls'            => (bool) $post_data['enable_urls'],
            'enable_indexing'        => (bool) $post_data['enable_indexing'],
            'message_md5'            => (string) $message_md5,
            'post_time'                => (isset($post_data['post_time'])) ? (int) $post_data['post_time'] : time(),
            'post_checksum'            => (isset($post_data['post_checksum'])) ? (string) $post_data['post_checksum'] : '',
            'post_edit_reason'        => str_replace(array('<','>'),array('&lt;','&gt;'),$post_data['post_edit_reason']),
            'post_edit_user'        => ($postmode == 'edit') ? $user->data['user_id'] : ((isset($post_data['post_edit_user'])) ? (int) $post_data['post_edit_user'] : 0),
            'forum_parents'            => $post_data['forum_parents'],
            'forum_name'            => $post_data['forum_name'],
            'notify'                => $notify,
            //'notify_set'            => $post_data['notify_set'],
            'poster_ip'                => (isset($post_data['poster_ip'])) ? $post_data['poster_ip'] : $user->ip,
            'post_edit_locked'        => (int) $post_data['post_edit_locked'],
            'bbcode_bitfield'        => $message_parser->bbcode_bitfield,
            'bbcode_uid'            => $message_parser->bbcode_uid,
            'message'                => $message_parser->message,
            'attachment_data'        => $message_parser->attachment_data,
            'filename_data'            => $message_parser->filename_data,

            'topic_approved'        => (isset($post_data['topic_approved'])) ? $post_data['topic_approved'] : false,
            'post_approved'            => (isset($post_data['post_approved'])) ? $post_data['post_approved'] : false,
        );
	if(isset($post_data['topic_replies_real']))
	{
	        $data['topic_replies_real'] = $post_data['topic_replies_real'];
	        $data['topic_replies'] = $post_data['topic_replies'];
	}
        include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
        $redirect_url = submit_post($postmode, $post_data['post_subject'], $post_data['username'], $post_data['topic_type'], $poll, $data, $update_message);
        // Check the permissions for post approval, as well as the queue trigger where users are put on approval with a post count lower than specified. Moderators are not affected.
        $approved = true;
        if (((isset($config['enable_queue_trigger']) && $config['enable_queue_trigger'] && $user->data['user_posts'] < $config['queue_trigger_posts']) || !$auth->acl_get('f_noapprove', $data['forum_id'])) && !$auth->acl_get('m_approve', $data['forum_id']))
        {
            $approved = false;
        }

        $reply_success = false;
        $post_id = '';
        if ($redirect_url)
        {
            preg_match('/&amp;p=(\d+)/', $redirect_url, $matches);
            $post_id = $matches[1];
            $reply_success = true;
            $oMbqEtForumPost->postId->setOriValue($post_id);
            if($approved ==false)
            {
                $oMbqEtForumPost->state->setOriValue(1);
            }

        }
        return $oMbqEtForumPost;
    }
    /**
     * m_delete_post
     */
    public function mDeletePost($oMbqEtForumPost, $deletemode, $reason) {
        global $user,$phpbb_root_path, $phpEx, $template, $request;


        include_once($phpbb_root_path . 'includes/mcp/mcp_main.' . $phpEx);
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
        $pmaster = new p_master();
        $mcpMain = new mcp_main($pmaster);
        //setup fake template
        requireExtLibrary('fake_template');
        $template = new fake_template();
        mcp_delete_post(array($oMbqEtForumPost->postId->oriValue), $is_soft, $reason);
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
     * m_approve_post
     */
    public function mApprovePost($oMbqEtForumPost, $approvemode) {
        if($approvemode == 1)
        {
            global $user,$phpbb_root_path, $phpEx, $request;
            global $action;

            include_once($phpbb_root_path . 'includes/mcp/mcp_queue.' . $phpEx);
            if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
            {
                include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
            }
            include_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);

            $action = 'approve';
            overwriteRequestParam('post_id_list', array($oMbqEtForumPost->postId->oriValue));
            $user->setup('mcp');
            $pmaster = new p_master();
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
            overwriteRequestParam('post_id_list', array($oMbqEtForumPost->postId->oriValue));
            $user->setup('mcp');
            $pmaster = new p_master();
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
     * m_undelete_post
     */
    public function mUndeletePost($oMbqEtForumPost) {
        global $user,$phpbb_root_path, $phpEx, $template, $request;

        include_once($phpbb_root_path . 'includes/mcp/mcp_queue.' . $phpEx);
        if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
        {
        	include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
        }
        include_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);

        $user->setup('mcp');
        $pmaster = new p_master();
        $mcp_queue = new mcp_queue($pmaster);
        //setup fake template
        requireExtLibrary('fake_template');
        $template = new fake_template();
        $mcp_queue->approve_posts('restore', array($oMbqEtForumPost->postId->oriValue), null, null);
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
     * m_move_post
     */
    public function mMovePost($oMbqEtForumPosts, $oMbqEtForum, $oMbqEtForumTopic, $topicTitle) {
        global $user,$phpbb_root_path, $phpEx, $template, $request;

        if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
	{
        	include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
	}
        include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
        include_once($phpbb_root_path . 'includes/mcp/mcp_topic.' . $phpEx);
        $user->setup('mcp');
        $postIds = array();
        foreach($oMbqEtForumPosts as $oMbqEtForumPost)
        {
            $postIds[] = $oMbqEtForumPost->postId->oriValue;
        }

        if($oMbqEtForum == null && $oMbqEtForumTopic != null)
        {
            move_posts($postIds, $oMbqEtForumTopic->topicId->oriValue);
        }
        else
        {
            requireExtLibrary('fake_template');
            $template = new fake_template();

            overwriteRequestParam('post_id_list', $postIds);
            overwriteRequestParam('forum_id', $oMbqEtForum->forumId->oriValue);

            split_topic('split_all', $oMbqEtForumTopic != null ? $oMbqEtForumTopic->topicId->oriValue : $oMbqEtForumPosts[0]->topicId->oriValue, $oMbqEtForum->forumId->oriValue, empty($topicTitle) ? "---": $topicTitle);
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
        }
        return true;
    }

    /**
     * report post
     */
    public function reportPost($oMbqEtForumPost, $reason) {
        global $user,$phpbb_root_path, $phpEx, $template, $request;

        overwriteRequestParam('f', $oMbqEtForumPost->forumId->oriValue);
        overwriteRequestParam('p', $oMbqEtForumPost->postId->oriValue);
        overwriteRequestParam('reason_id', 4);
        overwriteRequestParam('report_text', $reason);
        overwriteRequestParam('submit', true, \phpbb\request\request_interface::POST);
        requireExtLibrary('fake_template');
        requireExtLibrary('report_clone');
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

    public function mCloseReport($oMbqEtForumPost)
    {
        global $phpbb_root_path, $phpEx, $request, $user, $auth, $action, $db;

        $user->session_begin();
        $auth->acl($user->data);
        $user->setup('mcp');

        if(!empty($oMbqEtForumPost->postId->oriValue))
        {
            $sql = "SELECT report_id
            FROM " . REPORTS_TABLE . ' r
            WHERE ' . $db->sql_in_set('r.post_id', array($oMbqEtForumPost->postId->oriValue));
            $result = $db->sql_query($sql);

            while ($row = $db->sql_fetchrow($result))
            {
                $report_id_list[] = $row['report_id'];
            }

        }
        else
        {
            return false;
        }

        if(empty($report_id_list)) return trigger_error('NO_REPORT_SELECTED');

        if(empty($user->data['user_last_confirm_key'])) $user->data['user_last_confirm_key'] = 'tapatalk';

        overwriteRequestParam('confirm', $user->lang['YES'],  \phpbb\request\request_interface::POST);
        overwriteRequestParam('confirm_uid', $user->data['user_id'],  \phpbb\request\request_interface::POST);
        overwriteRequestParam('sess', $user->session_id,  \phpbb\request\request_interface::POST);
        overwriteRequestParam('confirm_key', $user->data['user_last_confirm_key'],  \phpbb\request\request_interface::POST);

        overwriteRequestParam('mode', 'reports',  \phpbb\request\request_interface::POST);
        overwriteRequestParam('i', 'reports',  \phpbb\request\request_interface::POST);
        overwriteRequestParam('action', 'close',  \phpbb\request\request_interface::POST);
        overwriteRequestParam('report_id_list', $report_id_list,  \phpbb\request\request_interface::POST);

        include_once($phpbb_root_path . 'includes/mcp/mcp_reports.' . $phpEx);
        $p_master = false;
        $mcp = new mcp_reports($p_master);
        //setup fake template
        requireExtLibrary('fake_template');
        $template = new fake_template();

        $action = 'close';
        $mcp->main('reports', 'close');

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
