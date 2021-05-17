<?php

define('MBQ_PUSH_BLOCK_TIME', 60);    /* push block time(minutes) */
if(!class_exists('TapatalkBasePush'))
{
    require_once(dirname(__FILE__) . '/../mbqFrame/basePush/TapatalkBasePush.php');
}

if(!function_exists("tapatalk_process_bbcode"))
{
    require_once dirname(__FILE__).'/../tapatalkFunctions.php';
}

require_once(dirname(__FILE__).'/../helper.php');
/**
 * push class

 */
Class TapatalkPush extends TapatalkBasePush {

    //init
    public function __construct()
    {
    	global $request,$config,$phpbb_home, $phpbb_root_path,$phpEx;
    	if(!function_exists("generate_board_url"))
        {
            require_once($phpbb_root_path. '/includes/functions.' . $phpEx);
        }
    	$request->enable_super_globals();
        $this->pushKey =  isset($config['tapatalk_push_key']) ? $config['tapatalk_push_key'] : "";
        $this->siteUrl = generate_board_url();

        // We do not send push to banned users
        if (!function_exists('phpbb_get_banned_user_ids'))
        {
            include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
        }

        parent::__construct($this);
    }

    function get_push_slug()
    {
        global $config;
        return $config['tapatalk_push_slug'];
    }

    function set_push_slug($slug = null)
    {
    	global $config;

    	// phpbb config_value max length is 255
    	if (strlen($slug) > 255) return true;

        $config->set('tapatalk_push_slug', $slug);
        return true;
    }

    function doAfterAppLogin($userId)
    {
        global $config, $db;
        //add tapatalk_users here,for push service
        if(push_table_exists())
        {
            global $table_prefix;
            $sql = "SELECT * FROM " . $table_prefix . "tapatalk_users where userid = '".$userId."'";
            $result = $db->sql_query($sql);
            $userInfo = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);
            if(empty($userInfo))
            {
                $sql_data[$table_prefix . "tapatalk_users"]['sql'] = array(
                    'userid' => $userId,
                    'announcement' => 1,
                    'pm' => 1,
                    'subscribe' => 1,
                    'quote' => 1,
                    'tag' => 1,
                    'newtopic' => 1,
                    'updated' => time()
                );
                $sql = 'INSERT INTO ' . $table_prefix . "tapatalk_users" . ' ' .
                $db->sql_build_array('INSERT', $sql_data[$table_prefix . "tapatalk_users"]['sql']);
                $db->sql_query($sql);
            }
        }
    }

    public function doPushPm($data, $unprocessedrecipients)
    {
        global $table_prefix, $db;
        if(!empty($this->pushKey) && push_table_exists())
        {
            $recipients = array();
            foreach($unprocessedrecipients as $key => $value)
            {
                if($this->isIgnoreUser($key)) continue;
                $recipients[] = $key;
            }

            if(sizeof($recipients))
            {
                $sql = "SELECT userid FROM " . $table_prefix . "tapatalk_users WHERE " . $db->sql_in_set('userid',$recipients) ;
                $result = $db->sql_query($sql);
                $ttrecipients = array();
                while($row = $db->sql_fetchrow($result))
                {
                    $ttrecipients[] = $row['userid'];
                }
                $db->sql_freeresult($result);

                $banned_users = phpbb_get_banned_user_ids($ttrecipients);

                $ttrecipients = array_diff($ttrecipients, $banned_users);

                $ttp_data = array(
                    'id'        => $data['msg_id'],
                    'title'     => self::push_clean($data['message_subject']),
                    'content'   => $data['message'],
                    'authorid'       => $data['from_user_id'],
                );

                $this->push($ttp_data, $ttrecipients, 'pm', $data);

            }
        }

    }

    public function doPushPost($data)
    {
        global $table_prefix, $db;
        if(!empty($this->pushKey) && push_table_exists())
        {
            $sql = "SELECT w.user_id FROM " . FORUMS_WATCH_TABLE . " w JOIN " . $table_prefix . "tapatalk_users ttu ON ttu.userid = w.user_id WHERE w.forum_id=" . $data['forum_id'];
            $result = $db->sql_query($sql);
            $subscribedUsers = array();
            while($row = $db->sql_fetchrow($result))
            {
                if($this->isIgnoreUser($row['user_id'])) continue;

                $subscribedUsers[] = $row['user_id'];
            }

            $db->sql_freeresult($result);

            $banned_users = phpbb_get_banned_user_ids($subscribedUsers);

            $subscribedUsers = array_diff($subscribedUsers, $banned_users);

            $ttp_data = array(
                'id'             => $data['topic_id'],
                'subid'          => $data['post_id'],
                'subfid'         => $data['forum_id'],
                'sub_forum_name' => self::push_clean($data['forum_name']),
                'title'          => self::push_clean($data['topic_title']),
                'content'        => $data['message'],
                'authorid'       => $data['poster_id'],
            );

            $this->push($ttp_data, $subscribedUsers, 'newtopic', $data);
        }
    }

    public function doPushReply($data)
    {
        global $table_prefix, $db;
        if(!empty($this->pushKey) && push_table_exists())
        {
            $sql = "SELECT w.user_id FROM " . TOPICS_WATCH_TABLE . " w JOIN " . $table_prefix . "tapatalk_users ttu ON ttu.userid = w.user_id WHERE w.topic_id=" . $data['topic_id'];

            $result = $db->sql_query($sql);
            $subscribedTopicUsers = array();
            while($row = $db->sql_fetchrow($result))
            {
                if($this->isIgnoreUser($row['user_id'])) continue;

                $subscribedTopicUsers[] = $row['user_id'];
            }

            $db->sql_freeresult($result);


            $banned_users = phpbb_get_banned_user_ids($subscribedTopicUsers);

            $subscribedTopicUsers = array_diff($subscribedTopicUsers, $banned_users);

            $ttp_data = array(
                'id'             => $data['topic_id'],
                'subid'          => $data['post_id'],
                'subfid'         => $data['forum_id'],
                'sub_forum_name' => self::push_clean($data['forum_name']),
                'title'          => self::push_clean($data['topic_title']),
                'content'        => $data['message'],
                'authorid'       => $data['poster_id'],
             );

            $this->push($ttp_data, $subscribedTopicUsers, 'sub', $data);
        }
    }

    public function doPushQuote($data)
    {
        global $table_prefix, $db,$auth;
        if(!empty($this->pushKey) && push_table_exists())
        {
            preg_match_all('/quote=&quot;(.*?)&quot;/is', $data['message'],$matches);
            $user_name_arr = array_unique($matches[1]);
            if(empty($user_name_arr)) return false;
            $sql = "SELECT w.user_id FROM " . USERS_TABLE . " w JOIN " . $table_prefix . "tapatalk_users ttu ON ttu.userid = w.user_id WHERE " . $db->sql_in_set('w.username',$user_name_arr);
            $result = $db->sql_query($sql);
            $quotedUsers = array();
            while($row = $db->sql_fetchrow($result))
            {
                if($this->isIgnoreUser($row['user_id'])) continue;
                $permissions = $auth->acl_get_list(array($row['user_id']), 'f_read', $data['forum_id']);
                if(empty($permissions))
                {
                    continue;
                }
                $quotedUsers[] = $row['user_id'];
            }
            $db->sql_freeresult($result);

            $ttp_data = array(
                'id'             => $data['topic_id'],
                'subid'          => $data['post_id'],
                'subfid'         => $data['forum_id'],
                'sub_forum_name' => self::push_clean($data['forum_name']),
                'title'          => self::push_clean($data['topic_title']),
                'content'        => $data['message'],
                'authorid'       => $data['poster_id'],
           );

            $this->push($ttp_data, $quotedUsers, 'quote', $data);
        }
    }

    public function doPushSubTopic($data)
    {
        global $table_prefix, $db;
        if(!empty($this->pushKey) && push_table_exists())
        {
            $sql = "SELECT ttu.userid FROM " . $table_prefix . "tapatalk_users ttu WHERE ttu.userid =" . $data['topic_poster'];
            $result = $db->sql_query($sql);
            $pushUsers = array();
            while($row = $db->sql_fetchrow($result))
            {
                if($this->isIgnoreUser($row['userid'])) continue;
                $pushUsers[] = $row['userid'];
            }
            $db->sql_freeresult($result);

            $banned_users = phpbb_get_banned_user_ids($pushUsers);

            $pushUsers = array_diff($pushUsers, $banned_users);

            $ttp_data = array(
                'id'             => $data['topic_id'],
                'subid'          => $data['topic_first_post_id'],
                'subfid'         => $data['forum_id'],
                'sub_forum_name' => self::push_clean($data['forum_name']),
                'title'          => self::push_clean($data['topic_title']),
                'content'        => '',
               'authorid'       => $data['topic_poster'],
            );

            $this->push($ttp_data, $pushUsers, 'newsub', $data);
        }
    }

    public function isIgnoreUser($uid)
    {
        global $user;

        if ($uid == $user->data['user_id']) return true;

        if(defined("TAPATALK_PUSH" . $uid))
        {
            return true;
        }

        define("TAPATALK_PUSH" . $uid, 1);

        return false;
    }

    public function doPushTag($data)
    {
        global $user, $config, $table_prefix, $db, $auth;
        if(!empty($this->pushKey) && push_table_exists())
        {
            $user_name_arr = $this->getTagList($data['message']);
            if(empty($user_name_arr)) return false;
            $sql = "SELECT w.user_id FROM " . USERS_TABLE . " w JOIN " . $table_prefix . "tapatalk_users ttu ON ttu.userid = w.user_id WHERE " . $db->sql_in_set('w.username',$user_name_arr);
            $result = $db->sql_query($sql);
            $quotedUsers = array();
            while($row = $db->sql_fetchrow($result))
            {
                if($this->isIgnoreUser($row['user_id'])) continue;

                $quotedUsers[] = $row['user_id'];
            }
            $db->sql_freeresult($result);
            if (empty($quotedUsers))
            {
                return false;
            }
            $auth_read = $auth->acl_raw_data($quotedUsers, 'f_read', $data['forum_id']);

            if (empty($auth_read))
            {
                return false;
            }

            $allowedQuotedUsers = array();

            foreach($auth_read as $user_id => $perm)
            {
                if($perm[$data['forum_id']]['f_read'] == ACL_YES)
                {
                    $allowedQuotedUsers[] = $user_id;
                }
            }

            $banned_users = phpbb_get_banned_user_ids($allowedQuotedUsers);

            $allowedQuotedUsers = array_diff($allowedQuotedUsers, $banned_users);

            if (empty($allowedQuotedUsers))
            {
                return false;
            }

            $ttp_data = array(
                'id'             => $data['topic_id'],
                'subid'          => $data['post_id'],
                'subfid'         => $data['forum_id'],
                'sub_forum_name' => self::push_clean($data['forum_name']),
                'title'          => self::push_clean($data['topic_title']),
                'content'        => $data['message'],
            'authorid'       => $data['poster_id'],
               );

            $this->push($ttp_data, $allowedQuotedUsers, 'tag', $data);
        }
    }

    public function convertContent($data)
    {
        global $user,$config,$phpbb_root_path,$phpEx;

        // Define the global bbcode bitfield, will be used to load bbcodes
        $bbcode_bitfield = '';
        $bbcode_bitfield = $bbcode_bitfield | base64_decode($data['bbcode_bitfield']);
        $bbcode = '';
        // Is a signature attached? Are we going to display it?
        if ($data['enable_sig'] && $config['allow_sig'] && $user->optionget('viewsigs') && isset($data['user_sig_bbcode_bitfield']))
        {
            $bbcode_bitfield = $bbcode_bitfield | base64_decode($data['user_sig_bbcode_bitfield']);
        }
        if ($bbcode_bitfield !== '')
        {
            $bbcode = new bbcode(base64_encode($bbcode_bitfield));
        }

        // Parse the message and subject
        $message = censor_text($data['message']);

        $message = tapatalk_process_bbcode($message, $data['bbcode_uid']);

        // Second parse bbcode here
        if ($data['bbcode_bitfield'] && $bbcode)
        {
            if(!class_exists("bbcode"))
            {
                include_once($phpbb_root_path . 'includes/bbcode.' . $phpEx);
            }
            $bbcode->bbcode_second_pass($message, $data['bbcode_uid'], $data['bbcode_bitfield']);
        }

        $message = bbcode_nl2br($message);
        $message = smiley_text($message);
        $message = post_html_clean($message);
        return $message;
    }
    public function doPushDelete($data)
    {
        global $user, $config, $table_prefix, $db;
        if(!empty($this->pushKey) && push_table_exists())
        {
            if(isset($data['post_ids']))
            {
                $ttp_data = array(
                     'id'             => implode(',',$data['post_ids']),
                );
                $this->push($ttp_data, "", 'delpost', $data);
            }
            else if(isset($data['topic_ids']))
            {
                $ttp_data = array(
                     'id'             => implode(',',$data['topic_ids']),
                );
                $this->push($ttp_data, "", 'deltopic', $data);
            }
        }
    }
    public function doPushPendingTopic($data)
    {
        global $user, $config, $table_prefix, $db;
        if(!empty($this->pushKey) && push_table_exists())
        {
            $ttp_data = array(
                'forum_id'          => $data['data']['forum_id'],
                'id'                => $data['data']['topic_id'], // topic id
                'subid'             => $data['data']['post_id'],        // post id
                'authorid'          => $data['data']['poster_id'],
                'author'            => $this->push_clean($data['data']['post_author']),
                'dateline'          => $data['data']['post_time'],
                'subfid'            => $data['data']['forum_id'],
                'sub_forum_name'    => $this->push_clean($data['data']['forum_name']),
                'title'             => $this->push_clean($data['data']['topic_title']),
                 );
            $this->push($ttp_data, array_keys($data['notify_users']), 'pending_topic', $data);
        }
    }
    public function doPushPendingPost($data)
    {
        global $user, $config, $table_prefix, $db;
        if(!empty($this->pushKey) && push_table_exists())
        {
            $ttp_data = array(
                'forum_id'          => $data['data']['forum_id'],
                'id'                => $data['data']['topic_id'], // topic id
                'subid'             => $data['data']['post_id'],        // post id
                'authorid'          => $data['data']['poster_id'],
                'author'            => $this->push_clean($data['data']['post_author']),
                'dateline'          => $data['data']['post_time'],
                'subfid'            => $data['data']['forum_id'],
                'sub_forum_name'    => $this->push_clean($data['data']['forum_name']),
                'title'             => $this->push_clean($data['data']['topic_title']),
                 );
            $this->push($ttp_data, array_keys($data['notify_users']), 'pending_post', $data);
        }
    }
    public function push($data, $push_user, $type, $origin_data=array())
    {
        global $user, $config;

        if(empty($this->pushKey)) return false;

        $data['type']        = $type;
        $data['key']         = $this->pushKey;
        $data['url']         = $this->siteUrl;
        if($type != 'delpost' && $type != 'deltopic')
        {
            $data['dateline']    = time();
            $data['author_ua']   = self::getClienUserAgent();
            $data['author_type'] = TT_check_return_user_type($user->data);
            $data['authorid']    = $user->data['user_id'];
            $data['author']      = $user->data['username'];
            $data['author_postcount'] = $user->data['user_posts'];

            if(!empty($data['content']))
            {
                $data['content'] = self::convertContent($origin_data);
            }
        }
        $data['from_app']    = self::getIsFromApp();


        if(!empty($push_user))
        {
            $data['userid'] = implode(',', $push_user);
            $data['push'] = 1;
        }
        else
        {
            $data['push'] = 0;
        }

        self::do_push_request($data);
    }

    protected function doInternalPushThank($p){}

    protected function doInternalPushReply($p){}

    protected function doInternalPushReplyConversation($p){}

    protected function doInternalPushNewTopic($p){}

    protected function doInternalPushNewConversation($p){}

    protected function doInternalPushNewMessage($p){}

    protected function doInternalPushLike($p){}

    protected function doInternalPushDeleteTopic($p){}

    protected function doInternalPushDeletePost($p){}

    protected function doInternalPushNewSubscription($p){
        global $config;

        $this->pushKey =$config['tapatalk_push_key'];
        $this->siteUrl =  generate_board_url();

        $data = array();
        $oMbqEtForumTopic = $p['oMbqEtForumTopic'];
        $data['topic_poster'] = $oMbqEtForumTopic->topicAuthorId->oriValue;
        $data['topic_id'] = $oMbqEtForumTopic->topicId->oriValue;
        $data['topic_first_post_id'] = $oMbqEtForumTopic->firstPostId->oriValue;
        $data['forum_id'] = $oMbqEtForumTopic->forumId->oriValue;
        $data['forum_name'] = $oMbqEtForumTopic->oMbqEtForum->forumName->oriValue;
        $data['topic_title'] = $oMbqEtForumTopic->topicTitle->oriValue;
        $this->doPushSubTopic($data);
    }
}
