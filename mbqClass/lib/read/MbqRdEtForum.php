<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtForum');

/**
 * forum read class
 */
Class MbqRdEtForum extends MbqBaseRdEtForum {

    public function __construct() {
    }

    public function makeProperty(&$oMbqEtForum, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
    public function getForumTree($return_description = 0, $root_forum_id = 0) {

        global $db, $auth, $user, $config, $mobiquo_config, $phpbb_home;
        if ($root_forum_id != 0)
        {
            $forum_filter = " WHERE f.parent_id = '$root_forum_id'";
        }
        else
        {
            $forum_filter = '';
        }

        $user_watch_array = array();
        if($user->data['is_registered'])
        {
            $sql = "SELECT notify_status,forum_id FROM " . FORUMS_WATCH_TABLE . " WHERE user_id = '".$user->data['user_id']."'";
            $result_watch = $db->sql_query($sql);
            while($row_watch = $db->sql_fetchrow($result_watch))
            {
                if(isset($row_watch['notify_status']) && !is_null($row_watch['notify_status']) && $row_watch['notify_status'] !== '')
                {
                    $user_watch_array[] = $row_watch['forum_id'];
                }
            }
        }
        $forum_rows = array();
        $forum_rows[$root_forum_id] = array('forum_id' => $root_forum_id, 'parent_id' => -1, 'child' => array());
        $forum_hide_forum_arr = !empty($mobiquo_config['hide_forum_id']) ? $mobiquo_config['hide_forum_id'] : array();
        $sql = 'SELECT f.*  FROM ' . FORUMS_TABLE . ' f ' . $forum_filter . '
            ORDER BY f.left_id ASC';
        $result = $db->sql_query($sql, 600);
        while ($row = $db->sql_fetchrow($result))
        {
            $forum_id = $row['forum_id'];
            if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
            {
                // Non-postable forum with no subforums, don't display
                continue;
            }
            if(in_array($row['forum_id'], $forum_hide_forum_arr))
            {
                continue;
            }
            elseif(in_array($row['parent_id'], $forum_hide_forum_arr))
            {
                array_push($forum_hide_forum_arr, $row['forum_id']);
                continue;
            }
            // Skip branch
            if (isset($right_id))
            {
                if ($row['left_id'] < $right_id)
                {
                    continue;
                }
                unset($right_id);
            }

            if (!$auth->acl_get('f_list', $forum_id) || (isset($mobiquo_config['hide_forum_id']) && in_array($forum_id, $mobiquo_config['hide_forum_id'])))
            {
                // if the user does not have permissions to list this forum, skip everything until next branch
                $right_id = $row['right_id'];
                continue;
            }

            $row['unread_count'] = 0;

            if ($user->data['is_registered'] && ($config['email_enable'] || $config['jab_enable']) && $config['allow_forum_notify'] && $row['forum_type'] == FORUM_POST && $auth->acl_get('f_subscribe', $forum_id))
            {
                $row['can_subscribe'] = true;
                $row['is_subscribed'] = in_array($row['forum_id'], $user_watch_array) ? true : false;
            } else {
                $row['can_subscribe'] = false;
                $row['is_subscribed'] = false;
            }

            $forum_rows[$forum_id] = $row;
        }
        $db->sql_freeresult($result);
        $fids = array(-1);
        foreach($forum_rows as $id => $value)
        {
            if (!in_array($value['parent_id'], $fids))
                unset($forum_rows[$id]);
            else if (isset($value['left_id']) && isset($value['right_id']) && $value['left_id'] > $value['right_id'])
                unset($forum_rows[$id]);
            else
                $fids[] = $id;
        }

        while(empty($forum_rows[$root_forum_id]['child']) && count($forum_rows) > 1)
        {
            $current_parent_id = -1;
            $leaves_forum = array();
            foreach($forum_rows as $row)
            {
                $row_parent_id = $row['parent_id'];
                if ($row_parent_id != $current_parent_id)
                {
                    if(isset($leaves_forum[$row_parent_id]))
                    {
                        $leaves_forum[$row_parent_id] = array();
                    }
                    else
                    {
                        if(isset($leaves_forum[$forum_rows[$row_parent_id]['parent_id']]))
                        {
                            $leaves_forum[$forum_rows[$row_parent_id]['parent_id']] = array();
                        }
                        $leaves_forum[$row_parent_id][] = $row['forum_id'];
                    }
                    $current_parent_id = $row_parent_id;
                }
                else if ($row_parent_id == $current_parent_id)
                {
                    if(!empty($leaves_forum[$row_parent_id]))
                    {
                        $leaves_forum[$row_parent_id][] = $row['forum_id'];
                    }
                }
            }
            if(empty($config['tapatalkdir'])) $config['tapatalkdir'] = 'mobiquo';
            foreach($leaves_forum as $node_forum_id => $leaves)
            {
                foreach($leaves as $forum_id)
                {
                    $forum =& $forum_rows[$forum_id];
                    if (function_exists('get_unread_topics'))
                        $unread_count = count(get_unread_topics(false, "AND t.forum_id = $forum_id"));
                    else
                        $unread_count = count(tt_get_unread_topics(false, "AND t.forum_id = $forum_id"));

                    $forum['unread_count'] += $unread_count;
                    if ($forum['unread_count'])
                    {
                        $forum_rows[$forum['parent_id']]['unread_count'] = (isset($forum_rows[$forum['parent_id']]['unread_count']) ? $forum_rows[$forum['parent_id']]['unread_count'] : 0) + $forum['unread_count'];
                    }

                    $forum_type = $forum['forum_link'] ? 'link' : ($forum['forum_type'] != FORUM_POST ? 'category' : 'forum');

                    if ($logo_icon_name = tp_get_forum_icon($forum_id, $forum_type, $forum['forum_status'], $forum['unread_count']))
                        $logo_url = $phpbb_home.$config['tapatalkdir'] .'/forum_icons/'.$logo_icon_name;
                    else if ($forum['forum_image'])
                    {
                        if (preg_match('#^https?://#i', $forum['forum_image']))
                            $logo_url = $forum['forum_image'];
                        else
                            $logo_url = $phpbb_home.$forum['forum_image'];
                    }
                    else
                        $logo_url = '';


                    $forumNode = array(
                        'forum_id'      => $forum_id,
                        'forum_name'    => basic_clean($forum['forum_name']),
                        'parent_id'     => $node_forum_id,
                        'logo_url'      => $logo_url,
                        'url'           => $forum['forum_link'],
                    );

                    if ($forum['unread_count'])     $forumNode['unread_count']       = $forum['unread_count'];
                    if ($forum['unread_count'])     $forumNode['new_post']           = true;
                    if ($forum['forum_password'])   $forumNode['is_protected']       = true;
                    if (!empty($forum['can_subscribe']))    $forumNode['can_subscribe']      = true;
                    else $forumNode['can_subscribe']      = false;
                    if (!empty($forum['is_subscribed']))    $forumNode['is_subscribed']      = true;
                    else $forumNode['is_subscribed']      = false;
                    if ($forum['forum_type'] != FORUM_POST) $forumNode['sub_only']   = true;

                    if ($return_description)
                    {
                        $description = smiley_text($forum['forum_desc'], true);
                        $description = generate_text_for_display($description, $forum['forum_desc_uid'], $forum['forum_desc_bitfield'], $forum['forum_desc_options']);
                        $description = preg_replace('/<br *?\/?>/i', "\n", $description);
                        $forumNode['description'] =basic_clean($description);
                    }

                    if (isset($forum['child'])) {
                        $forumNode['child'] = $forum['child'];
                    }

                    $forum_rows[$node_forum_id]['child'][] = $forumNode;
                    unset($forum_rows[$forum_id]);
                }
            }
        }
        return $this->parseForumTree(null, $forum_rows[$root_forum_id]['child']);
    }
    /**
     * get forum objs
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byForumIds' means get data by forum ids.$var is the ids.
     * $mbqOpt['case'] = 'subscribed' means get subscribed data.$var is the user id.
     * @return  Array
     */
    public function getObjsMbqEtForum($var, $mbqOpt) {
        global $db, $auth, $user, $config, $mobiquo_config, $phpbb_home;
        if ($mbqOpt['case'] == 'byForumIds') {
            $forumIds = implode($var,',');
            $forum_filter = " WHERE f.forum_id IN ($forumIds)";
            $sql = 'SELECT f.*  FROM ' . FORUMS_TABLE . ' f ' . $forum_filter . '
            ORDER BY f.left_id ASC';
            $result = $db->sql_query($sql, 600);
            $objsMbqEtForum = array();
            while ($row = $db->sql_fetchrow($result))
            {
                $objsMbqEtForum[] = $this->initOMbqEtForum($row, array('case'=>'byRow'));
            }
            return $objsMbqEtForum;
        } elseif ($mbqOpt['case'] == 'subscribed') {
            global $config, $db, $user, $auth, $mobiquo_config, $phpbb_home;

            $user->setup('ucp');

            if (!$user->data['is_registered']) trigger_error('LOGIN_EXPLAIN_UCP');

            $forum_list = array();
            if ($config['allow_forum_notify'])
            {
                $forbidden_forums = $auth->acl_getf('!f_read', true);
                $forbidden_forums = array_unique(array_keys($forbidden_forums));

                if (isset($mobiquo_config['hide_forum_id']))
                {
                    $forbidden_forums = array_unique(array_merge($forbidden_forums, $mobiquo_config['hide_forum_id']));
                }

                $sql_array = array(
                    'SELECT' => 'f.*',

                    'FROM'   => array(
                        FORUMS_WATCH_TABLE    => 'fw',
                        FORUMS_TABLE        => 'f'
                    ),

                    'WHERE'  => 'fw.user_id = ' . $user->data['user_id'] . '
                AND f.forum_id = fw.forum_id
                AND ' . $db->sql_in_set('f.forum_id', $forbidden_forums, true, true),

                    'ORDER_BY'    => 'left_id'
                );

                if ($config['load_db_lastread'])
                {
                    $sql_array['LEFT_JOIN'] = array(
                        array(
                            'FROM'    => array(FORUMS_TRACK_TABLE => 'ft'),
                            'ON'    => 'ft.user_id = ' . $user->data['user_id'] . ' AND ft.forum_id = f.forum_id'
                        )
                    );

                    $sql_array['SELECT'] .= ', ft.mark_time ';
                }
                else
                {
                    $tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? ((STRIP) ? stripslashes($_COOKIE[$config['cookie_name'] . '_track']) : $_COOKIE[$config['cookie_name'] . '_track']) : '';
                    $tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();
                }

                $sql = $db->sql_build_query('SELECT', $sql_array);
                $result = $db->sql_query($sql);

                $forum_list = array();
                while ($row = $db->sql_fetchrow($result))
                {
                    $forum_id = $row['forum_id'];

                    if ($config['load_db_lastread'])
                    {
                        $forum_check = (!empty($row['mark_time'])) ? $row['mark_time'] : $user->data['user_lastmark'];
                    }
                    else
                    {
                        $forum_check = (isset($tracking_topics['f'][$forum_id])) ? (int) (base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate']) : $user->data['user_lastmark'];
                    }

                    $unread_forum = ($row['forum_last_post_time'] > $forum_check) ? true : false;

                    $logo_url = '';
                    if (file_exists("./forum_icons/$forum_id.png"))
                    {
                        $logo_url = $phpbb_home.$config['tapatalkdir']."/forum_icons/$forum_id.png";
                    }
                    else if (file_exists("./forum_icons/$forum_id.jpg"))
                    {
                        $logo_url = $phpbb_home.$config['tapatalkdir']."/forum_icons/$forum_id.jpg";
                    }
                    else if (file_exists("./forum_icons/default.png"))
                    {
                        $logo_url = $phpbb_home.$config['tapatalkdir']."/forum_icons/default.png";
                    }
                    else if ($row['forum_image'])
                    {
                        $logo_url = $phpbb_home.$row['forum_image'];
                    }

                    //$xmlrpc_forum = new xmlrpcval(array(
                    //    'forum_id'      => new xmlrpcval($forum_id),
                    //    'forum_name'    => new xmlrpcval(html_entity_decode($row['forum_name']), 'base64'),
                    //    'icon_url'      => new xmlrpcval($logo_url),
                    //    'is_protected'  => new xmlrpcval($row['forum_password'] ? true : false, 'boolean'),
                    //    'sub_only'      => new xmlrpcval(($row['forum_type'] == FORUM_POST) ? false : true, 'boolean'),
                    //    'new_post'      => new xmlrpcval($unread_forum, 'boolean'),
                    //), 'struct');

                    $oMbqEtForum = $this->initOMbqEtForum($row, array('case'=>'byRow'));
                    $oMbqEtForum->logoUrl->setOriValue($logo_url);
                    $oMbqEtForum->newPost->setOriValue($unread_forum);
                    $forum_list[] = $oMbqEtForum;
                }
                $db->sql_freeresult($result);
            }

            return $forum_list;
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
    function parseForumTree($parentForum, $forums)
    {
        $result = array();
        if($forums != null)
        {
            foreach($forums as $forum)
            {
                $oMbqEtForum = $this->initOMbqEtForum($forum, array('case'=>'byRow'));
                if(isset($forum['child']))
                {
                    $oMbqEtForum->objsSubMbqEtForum = $this->parseForumTree($oMbqEtForum, $forum['child']);
                }
                $result[] = $oMbqEtForum;
            }
        }
        return $result;
    }
    public function initOMbqEtForum($var, $mbqOpt)
    {
        if ($mbqOpt['case'] == 'byForumId') {
            $forumId = intval($var);
            if(MbqMain::$Cache->Exists('MbqEtForum',$forumId))
            {
                return MbqMain::$Cache->Get('MbqEtForum',$forumId);
            }
            global $db, $auth, $user, $config, $mobiquo_config, $phpbb_home;
            $forum_filter = " WHERE f.forum_id = $forumId";
            $sql = 'SELECT f.*  FROM ' . FORUMS_TABLE . ' f ' . $forum_filter . '
            ORDER BY f.left_id ASC';
            $result = $db->sql_query($sql, 600);
            if ($row = $db->sql_fetchrow($result))
            {
                $oMbqEtForum = $this->initOMbqEtForum($row, array('case'=>'byRow'));
                MbqMain::$Cache->Set('MbqEtForum', $forumId, $oMbqEtForum);
                return $oMbqEtForum;
            }
            return false;
        }
        else
        {
            global $db, $auth, $user, $config, $mobiquo_config, $phpbb_home;
            $row = $var;
            $forum_id = $row['forum_id'];
            if(MbqMain::$Cache->Exists('MbqEtForum',$forum_id))
            {
                return MbqMain::$Cache->Get('MbqEtForum',$forum_id);
            }
            $tapatalk_forum_read_only = getTapatalkConfigValue('tapatalk_forum_read_only');
            $read_only_forums = explode(",", $tapatalk_forum_read_only);
            $can_post = true;
            $can_upload = true;
            $can_create_poll = true;
            if(empty($read_only_forums) || !is_array($read_only_forums))
            {
                $read_only_forums = array();
            }
            if(!$auth->acl_get('f_post', $forum_id) || in_array($forum_id, $read_only_forums))
            {
                $can_post = false;
            }
            if(!$can_post||!$auth->acl_get('u_attach'))
            {
                $can_upload = false;
            }
            if(!$can_post||!$auth->acl_get('f_poll', $forum_id))
            {
                $can_create_poll = false;
            }
            $oMbqEtForum = MbqMain::$oClk->newObj('MbqEtForum');
            $oMbqEtForum->forumId->setOriValue($forum_id);
            $oMbqEtForum->forumName->setOriValue(html_entity_decode($row['forum_name']));
            $oMbqEtForum->parentId->setOriValue($row['parent_id']);
            if(isset($row['description']))
            {
                $oMbqEtForum->description->setOriValue($row['description']);
            }
            if(isset($row['logo_url']))
            {
                $oMbqEtForum->logoUrl->setOriValue($row['logo_url']);
            }
            $oMbqEtForum->newPost->setOriValue(isset($row['new_post']) && $row['new_post']);
            $oMbqEtForum->isProtected->setOriValue(isset($row['is_protected']) && $row['is_protected']);
            $oMbqEtForum->isSubscribed->setOriValue(isset($row['is_subscribed']) && $row['is_subscribed']);
            $oMbqEtForum->canSubscribe->setOriValue(isset($row['can_subscribe']) && $row['can_subscribe']);
            if(isset($row['url']))
            {
                $oMbqEtForum->url->setOriValue($row['url']);
            }
            $oMbqEtForum->subOnly->setOriValue(isset($row['sub_only']) && $row['sub_only']);
            $oMbqEtForum->canPost->setOriValue($can_post);
            $oMbqEtForum->canUpload->setOriValue($can_upload);
            $oMbqEtForum->canCreatePoll->setOriValue($can_create_poll);

            $oMbqEtForum->mbqBind = $row;
            MbqMain::$Cache->Set('MbqEtForum', $forum_id, $oMbqEtForum);
            return $oMbqEtForum;
        }
    }
    /**
     * login forum
     *
     * @return Array
     */
    public function loginForum($oMbqEtForum, $password) {
        global $db, $auth, $user, $config;

        $user->setup('viewforum');

        $forum_id = $oMbqEtForum->forumId->oriValue;


        $sql_from = FORUMS_TABLE . ' f';
        $lastread_select = '';

        // Grab appropriate forum data
        if ($config['load_db_lastread'] && $user->data['is_registered'])
        {
            $sql_from .= ' LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.user_id = ' . $user->data['user_id'] . '
            AND ft.forum_id = f.forum_id)';
            $lastread_select .= ', ft.mark_time';
        }

        if ($user->data['is_registered'])
        {
            $sql_from .= ' LEFT JOIN ' . FORUMS_WATCH_TABLE . ' fw ON (fw.forum_id = f.forum_id AND fw.user_id = ' . $user->data['user_id'] . ')';
            $lastread_select .= ', fw.notify_status';
        }

        $sql = "SELECT f.* $lastread_select
        FROM $sql_from
        WHERE f.forum_id = $forum_id";
        $result = $db->sql_query($sql);
        $forum_data = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        if (!$forum_data) trigger_error('NO_FORUM');

        // Permissions check
        if (!$auth->acl_gets('f_list', 'f_read', $forum_id) || ($forum_data['forum_type'] == FORUM_LINK && $forum_data['forum_link'] && !$auth->acl_get('f_read', $forum_id)))
        {
            if ($user->data['user_id'] != ANONYMOUS)
            {
                trigger_error('SORRY_AUTH_READ');
            }

            trigger_error('LOGIN_VIEWFORUM');
        }

        $login_status = false;
        // Forum is passworded ... check whether access has been granted to this
        // user this session, if not show login box
        if ($forum_data['forum_password'])
        {
            $sql = 'SELECT forum_id
            FROM ' . FORUMS_ACCESS_TABLE . '
            WHERE forum_id = ' . $forum_data['forum_id'] . '
                AND user_id = ' . $user->data['user_id'] . "
                AND session_id = '" . $db->sql_escape($user->session_id) . "'";
            $result = $db->sql_query($sql);
            $row = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);

            if ($row)
            {
                $login_status = true;
            }
            elseif ($password)
            {
                // Remove expired authorised sessions
                $sql = 'SELECT f.session_id
                FROM ' . FORUMS_ACCESS_TABLE . ' f
                LEFT JOIN ' . SESSIONS_TABLE . ' s ON (f.session_id = s.session_id)
                WHERE s.session_id IS NULL';
                $result = $db->sql_query($sql);

                if ($row = $db->sql_fetchrow($result))
                {
                    $sql_in = array();
                    do
                    {
                        $sql_in[] = (string) $row['session_id'];
                    }
                    while ($row = $db->sql_fetchrow($result));

                    // Remove expired sessions
                    $sql = 'DELETE FROM ' . FORUMS_ACCESS_TABLE . '
                    WHERE ' . $db->sql_in_set('session_id', $sql_in);
                    $db->sql_query($sql);
                }
                $db->sql_freeresult($result);

                if (phpbb_check_hash($password, $forum_data['forum_password']))
                {
                    $sql_ary = array(
                        'forum_id'        => (int) $forum_data['forum_id'],
                        'user_id'        => (int) $user->data['user_id'],
                        'session_id'    => (string) $user->session_id,
                    );

                    $db->sql_query('INSERT INTO ' . FORUMS_ACCESS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

                    $login_status = true;
                }
            }
        }
        if($login_status)
        {
            return $login_status;
        }
        return  'Password is wrong';
    }
    public function getUrl($oMbqEtForum)
    {
        global $phpbb_root_path, $phpEx, $db, $auth, $config;
        $base = new  \tas2580\seourls\event\base($auth, $config, $phpbb_root_path);
        $forumUrl = $base->generate_forum_link($oMbqEtForum->forumId->oriValue, $oMbqEtForum->forumName->oriValue, 0, true);
        return $forumUrl;
    }
}
