<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtForumTopic');

/**
 * forum topic read class
 */
Class MbqRdEtForumTopic extends MbqBaseRdEtForumTopic {

    public function __construct() {
    }

    public function makeProperty(&$oMbqEtForumTopic, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
                MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
                break;
        }
    }
    /**
     * get forum topic objs
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byForum' means get data by forum obj.$var is the forum obj.
     * $mbqOpt['case'] = 'subscribed' means get subscribed data.$var is the user id.
     * $mbqOpt['case'] = 'byTopicIds' means get data by topic ids.$var is the ids.
     * $mbqOpt['case'] = 'byAuthor' means get data by author.$var is the MbqEtUser obj.
     * $mbqOpt['top'] = true means get sticky data.
     * $mbqOpt['notIncludeTop'] = true means get not sticky data.
     * $mbqOpt['oMbqDataPage'] = pagination class info.
     * $mbqOpt['ann'] = true means get anouncement data.
     * $mbqOpt['oFirstMbqEtForumPost'] = true means load oFirstMbqEtForumPost property of topic,default is true.This param used to prevent infinite recursion call for get oMbqEtForumTopic and oFirstMbqEtForumPost and make memory depleted
     * @return  Mixed
     */
    public function getObjsMbqEtForumTopic($var, $mbqOpt) {
        global $db, $auth, $user, $config;

        switch($mbqOpt['case'])
        {
            case 'byForum':
                {
                    $oMbqEtForum = $var;
                    $user->setup('viewforum');

                    list($start, $limit) = process_page($mbqOpt['oMbqDataPage']->startNum, $mbqOpt['oMbqDataPage']->lastNum);

                    // get forum id from parameters
                    $forum_id = intval($oMbqEtForum->forumId->oriValue);
                    if (!$forum_id) trigger_error('NO_FORUM');

                    // check if need sticky/announce topic only
                    $topic_type = '';

                    // check if need sticky topic only
                    if (isset($mbqOpt['top']) && $mbqOpt['top'] == true)
                    {
                        $topic_type = POST_STICKY;
                        $start = 0;
                        $limit = 20;
                    }
                    // check if need announce topic only
                    else if (isset($mbqOpt['ann']) && $mbqOpt['ann'] == true)
                    {
                        $topic_type = POST_ANNOUNCE . ', ' . POST_GLOBAL;
                        $start = 0;
                        $limit = 20;
                    }

                    $sort_days = 0;
                    $sort_key  = 't';
                    $sort_dir  = 'd';

                    ////------- Grab appropriate forum data --------
                    //$sql = "SELECT f.* FROM " . FORUMS_TABLE . " f WHERE f.forum_id = $forum_id";
                    //$result = $db->sql_query($sql);
                    //$forum_data = $db->sql_fetchrow($result);
                    //$db->sql_freeresult($result);

                    //// Forum does not exist
                    //if (!$forum_data) trigger_error('NO_FORUM');

                    //// Can not get topics from link forum
                    if ($oMbqEtForum->mbqBind['forum_type'] == FORUM_LINK)
                    {
                        trigger_error('NO_FORUM');
                    }

                    // Permissions check
                    if (!$auth->acl_gets('f_list', 'f_read', $forum_id) || ($oMbqEtForum->mbqBind['forum_type'] == FORUM_LINK && $oMbqEtForum->mbqBind['forum_link'] && !$auth->acl_get('f_read', $forum_id)))
                    {
                        if ($user->data['user_id'] != ANONYMOUS)
                        {
                            trigger_error('SORRY_AUTH_READ');
                        }

                        trigger_error('LOGIN_VIEWFORUM');
                    }


                    // Forum is passworded
                    if ($oMbqEtForum->mbqBind['forum_password'] && !check_forum_password($forum_id))
                    {
                        trigger_error('LOGIN_FORUM');
                    }

                    // Topic ordering options
                    $sort_by_sql = array('a' => 't.topic_first_poster_name',
                                         't' => 't.topic_last_post_time',   // default one
                                         'r' => 't.topic_replies',
                                         's' => 't.topic_title',
                                         'v' => 't.topic_views');

                    // Limit topics to certain time frame, obtain correct topic count
                    // global announcements must not be counted, normal announcements have to
                    // be counted, as forum_topics(_real) includes them
                    if(getPHPBBVersion() == '3.0')
                    {
                        $sql_approved = ($auth->acl_get('m_approve', $forum_id)) ? '' : ' AND t.topic_approved = 1 ';
                    }
                    else
                    {
                        $sql_approved = ($auth->acl_get('m_approve', $forum_id)) ? '' : ' AND t.topic_visibility = 1 ';
                    }
                    // Get all shadow topics in this forum
                    $sql = 'SELECT t.topic_moved_id, t.topic_id
                FROM ' . TOPICS_TABLE . ' t
                WHERE t.forum_id = ' . $forum_id . '
                AND t.topic_type IN (' . POST_NORMAL . ', ' . POST_STICKY . ', ' . POST_ANNOUNCE . ', ' . POST_GLOBAL . ')
                AND t.topic_status = ' . ITEM_MOVED . ' ' .
                            $sql_approved;
                    $result = $db->sql_query($sql);

                    $shadow_topic_list = array();
                    while ($row = $db->sql_fetchrow($result))
                    {
                        $shadow_topic_list[$row['topic_moved_id']] = $row['topic_id'];
                    }
                    $db->sql_freeresult($result);

                    // Pick out those shadow topics that the user has no permission to access
                    if (!empty($shadow_topic_list))
                    {
                        $sql = 'SELECT t.topic_id, t.forum_id
                    FROM ' . TOPICS_TABLE . ' t
                    WHERE ' . $db->sql_in_set('t.topic_id', array_keys($shadow_topic_list));
                        $result = $db->sql_query($sql);

                        while ($row = $db->sql_fetchrow($result))
                        {
                            if ($auth->acl_get('f_read', $row['forum_id']))
                            {
                                unset($shadow_topic_list[$row['topic_id']]);
                            }
                        }
                        $db->sql_freeresult($result);
                    }

                    // Grab all topic data
                    $topic_list = array();

                    $sql_limit = $limit;  // num of topics needs to be return, default is 20, at most 50
                    $sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');
                    $sql_shadow_out = empty($shadow_topic_list) ? '' : 'AND ' . $db->sql_in_set('t.topic_moved_id', $shadow_topic_list, true);

                    // If the user is trying to reach late pages, start searching from the end
                    $store_reverse = false;

                    $unread_sticky_num = $unread_announce_count = 0;

                    //get subscribe users
                    $user_watch_row = array();
                    $sql = 'SELECT * FROM ' . TOPICS_WATCH_TABLE .' WHERE user_id = ' . $user->data['user_id'];
                    $result = $db->sql_query($sql);
                    while ($row = $db->sql_fetchrow($result))
                    {
                        $user_watch_row[$row['topic_id']] = $row['notify_status'];
                    }
                    $db->sql_freeresult($result);

                    if (!empty($topic_type)) // get top 20 announce/sticky topics only if need
                    {
                        $sql = 'SELECT t.*, u.user_avatar, u.user_avatar_type,bm.topic_id as bookmarked
                    FROM ' . TOPICS_TABLE . ' t
                        LEFT JOIN ' . USERS_TABLE . ' u ON (t.topic_poster = u.user_id)
                        LEFT JOIN ' . BOOKMARKS_TABLE . ' bm ON (bm.user_id = ' . $user->data['user_id'] . ' AND t.topic_id = bm.topic_id)
                    WHERE t.forum_id IN (' . $forum_id . ', 0)
                    AND t.topic_type IN (' . $topic_type . ') ' .
                                $sql_shadow_out . ' ' .
                                $sql_approved . '
                    ORDER BY ' . $sql_sort_order;
                        $result = $db->sql_query_limit($sql, $sql_limit, $start);
                    }
                    else
                    {
                        if ($user->data['user_id'] != ANONYMOUS)
                        {
                            // get total number of unread sticky topics number
                            $sql = 'SELECT t.topic_id, t.topic_last_post_time
                        FROM ' . TOPICS_TABLE . ' t
                        WHERE t.forum_id = ' . $forum_id.'
                        AND t.topic_type = ' . POST_STICKY . ' ' .
                                    $sql_shadow_out . ' ' .
                                    $sql_approved;
                            $result = $db->sql_query($sql);
                            while ($row = $db->sql_fetchrow($result))
                            {
                                if(empty($forum_id) || empty($row['topic_id']))
                                {
                                    continue;
                                }
                                $topic_tracking = get_complete_topic_tracking($forum_id, $row['topic_id']);
                                if (isset($topic_tracking[$row['topic_id']]) && $topic_tracking[$row['topic_id']] < $row['topic_last_post_time'])
                                    $unread_sticky_num++;
                            }
                            $db->sql_freeresult($result);

                            // get total number of unread announce topics number
                            $sql = 'SELECT t.topic_id, t.topic_last_post_time
                        FROM ' . TOPICS_TABLE . ' t
                        WHERE t.forum_id IN (' . $forum_id . ', 0)
                        AND t.topic_type IN (' . POST_ANNOUNCE . ', ' . POST_GLOBAL . ') ' .
                                    $sql_shadow_out . ' ' .
                                    $sql_approved;
                            $result = $db->sql_query($sql);
                            while ($row = $db->sql_fetchrow($result))
                            {
                                if(empty($forum_id) || empty($row['topic_id']))
                                {
                                    continue;
                                }
                                $topic_tracking = get_complete_topic_tracking($forum_id, $row['topic_id']);
                                if (isset($topic_tracking[$row['topic_id']]) && $topic_tracking[$row['topic_id']] < $row['topic_last_post_time'])
                                    $unread_announce_count++;
                            }
                            $db->sql_freeresult($result);
                        }

                        // get total number of normal topics
                        $sql = 'SELECT count(t.topic_id) AS num_topics
                    FROM ' . TOPICS_TABLE . ' t
                    WHERE t.forum_id = ' . $forum_id.'
                    AND t.topic_type = ' . POST_NORMAL . ' ' .
                                $sql_shadow_out . ' ' .
                                $sql_approved;
                        $result = $db->sql_query($sql);
                        $topics_count = (int) $db->sql_fetchfield('num_topics');
                        $db->sql_freeresult($result);

                        if ($start > $topics_count / 2)
                        {
                            $store_reverse = true;

                            if ($start + $sql_limit > $topics_count)
                            {
                                $sql_limit = min($sql_limit, max(1, $topics_count - $start));
                            }

                            // Select the sort order
                            $sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'ASC' : 'DESC');
                            $start = max(0, $topics_count - $sql_limit - $start);
                        }

                        $sql = 'SELECT t.*, u.user_avatar, u.user_avatar_type,bm.topic_id as bookmarked
                    FROM ' . TOPICS_TABLE . ' t
                        LEFT JOIN ' . USERS_TABLE . ' u ON (t.topic_poster = u.user_id)
                        LEFT JOIN ' . BOOKMARKS_TABLE . ' bm ON (bm.user_id = ' . $user->data['user_id'] . ' AND t.topic_id = bm.topic_id)
                    WHERE t.forum_id = ' . $forum_id.'
                    AND t.topic_type = ' . POST_NORMAL . ' ' .
                                $sql_shadow_out . ' ' .
                                $sql_approved . '
                    ORDER BY ' . $sql_sort_order;

                        $result = $db->sql_query_limit($sql, $sql_limit, $start);
                    }

                    $tids = array();
                    $rowset = array();
                    while ($row = $db->sql_fetchrow($result))
                    {
                        $rowset[] = $row;
                        $tids[] = $row['topic_moved_id'] ? $row['topic_moved_id'] : $row['topic_id'];
                    }
                    $db->sql_freeresult($result);

                    // get participated users of each topic
                    //    get_participated_user_avatars($tids);
                    //    global $topic_users, $user_avatar;

                    $topic_list = array();
                    $objsMbqEtForumTopic = array();
                    $this->prepare($rowset);
                    foreach($rowset as $row)
                    {
                        $objsMbqEtForumTopic[] = $this->initOMbqEtForumTopic($row, array('case' => 'byRow','oMbqEtForum' => $oMbqEtForum, 'oMbqEtUser' => true, 'user_watch_row' => $user_watch_row));
                    }

                    if ($store_reverse)
                    {
                        $objsMbqEtForumTopic = array_reverse($objsMbqEtForumTopic);
                    }

                    if (!empty($topic_type))
                    {
                        $topic_num = count($objsMbqEtForumTopic);
                    }
                    else
                    {
                        $topic_num = $topics_count;
                    }

                    $allowed = $config['max_attachments'] && $auth->acl_get('f_attach', $forum_id) && $auth->acl_get('u_attach') && $config['allow_attachments'] && @ini_get('file_uploads') != '0' && strtolower(@ini_get('file_uploads')) != 'off';
                    $max_attachment = ($auth->acl_get('a_') || $auth->acl_get('m_', $forum_id)) ? 99 : ($allowed ? $config['max_attachments'] : 0);
                    $max_png_size = ($auth->acl_get('a_') || $auth->acl_get('m_', $forum_id)) ? 10485760 : ($allowed ? ($config['max_filesize'] === '0' ? 10485760 : $config['max_filesize']) : 0);
                    $max_jpg_size = ($auth->acl_get('a_') || $auth->acl_get('m_', $forum_id)) ? 10485760 : ($allowed ? ($config['max_filesize'] === '0' ? 10485760 : $config['max_filesize']) : 0);

                    $tapatalk_forum_read_only = getTapatalkConfigValue('tapatalk_forum_read_only');
                    $read_only_forums = explode(",", $tapatalk_forum_read_only);
                    $can_post = true;
                    if(empty($read_only_forums) || !is_array($read_only_forums))
                    {
                        $read_only_forums = array();
                    }
                    if(!$auth->acl_get('f_post', $forum_id) || in_array($forum_id, $read_only_forums))
                    {
                        $can_post = false;
                    }

                    if ($mbqOpt['oMbqDataPage']) {
                        $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                        $oMbqDataPage->totalNum = $topic_num;
                        $oMbqDataPage->datas = $objsMbqEtForumTopic;
                        return $oMbqDataPage;
                    } else {
                        return $objsMbqEtForumTopic;
                    }
                    break;
                }
            case 'byTopicIds':
                {
                    $topic_ids = $var;
                    if(is_array($topic_ids))
                    {
                        $topic_ids = array_map('intval', $topic_ids);
                        $topic_ids = implode(',',$topic_ids);
                    }
                    else
                        $topic_ids = intval($topic_ids);
                    $sql = 'SELECT t.*, u.user_avatar, u.user_avatar_type,bm.topic_id as bookmarked
                    FROM ' . TOPICS_TABLE . ' t
                        LEFT JOIN ' . USERS_TABLE . ' u ON (t.topic_poster = u.user_id)
                        LEFT JOIN ' . BOOKMARKS_TABLE . ' bm ON (bm.user_id = ' . $user->data['user_id'] . ' AND t.topic_id = bm.topic_id)
                    WHERE t.topic_id in (' . $topic_ids .')';

                    $result = $db->sql_query_limit($sql, 100000, 0);
                    $tids = array();
                    $rowset = array();
                    while ($row = $db->sql_fetchrow($result))
                    {
                        $rowset[] = $row;
                        $tids[] = $row['topic_moved_id'] ? $row['topic_moved_id'] : $row['topic_id'];
                    }
                    $db->sql_freeresult($result);

                    //get subscribe users
                    $user_watch_row = array();
                    $sql = 'SELECT * FROM ' . TOPICS_WATCH_TABLE .' WHERE user_id = ' . $user->data['user_id'];
                    $result = $db->sql_query($sql);
                    while ($row = $db->sql_fetchrow($result))
                    {
                        $user_watch_row[$row['topic_id']] = $row['notify_status'];
                    }
                    $db->sql_freeresult($result);

                    $topic_list = array();
                    $objsMbqEtForumTopic = array();
                    $this->prepare($rowset);
                    foreach($rowset as $row)
                    {
                        $objsMbqEtForumTopic[] = $this->initOMbqEtForumTopic($row, array('case' => 'byRow', 'oMbqEtForum' => true, 'user_watch_row' => $user_watch_row));
                    }
                    if(isset($mbqOpt['oMbqDataPage']))
                    {
                        $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                        $oMbqDataPage->datas = $objsMbqEtForumTopic;
                        $oMbqDataPage->totalNum = sizeof($objsMbqEtForumTopic);
                        return $oMbqDataPage;
                    }

                    return $objsMbqEtForumTopic;
                }
            case 'subscribed':
                {
                    global $db, $template, $user, $auth, $config, $can_subscribe, $show_results, $include_topic_num, $total_match_count, $request_method, $request, $searchResults;;

                    $include_topic_num = true;
                    $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                    overwriteRequestParam('page', $oMbqDataPage->curPage);
                    overwriteRequestParam('perpage', $oMbqDataPage->numPerPage);
                    overwriteRequestParam('search_id', 'subscribedtopics');

                    requireExtLibrary('search_clone');

                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    $newMbqOpt['case'] = 'byRow';
                    $newMbqOpt['oMbqEtForum'] = true;
                    $newMbqOpt['oMbqEtUser'] = true;
                    $newMbqOpt['oMbqDataPage'] = $oMbqDataPage;

                    //get subscribe users
                    $user_watch_row = array();
                    $sql = 'SELECT * FROM ' . TOPICS_WATCH_TABLE .' WHERE user_id = ' . $user->data['user_id'];
                    $result = $db->sql_query($sql);
                    while ($row = $db->sql_fetchrow($result))
                    {
                        $user_watch_row[$row['topic_id']] = $row['notify_status'];
                    }
                    $db->sql_freeresult($result);
                    $newMbqOpt['user_watch_row'] = $user_watch_row;
                    $rowset = array();
                    foreach($searchResults as $item)
                    {
                        $rowset[] = $item['bind'];
                    }
                    $this->prepare($rowset);
                    foreach($rowset as $row)
                    {
                        $oMbqDataPage->datas[] = $oMbqRdEtForumTopic->initOMbqEtForumTopic($row, $newMbqOpt);
                    }
                    $oMbqDataPage->totalNum = $total_match_count;
                    return $oMbqDataPage;
                }
            case 'awaitingModeration':
                {
                    global $user,$phpbb_root_path, $phpEx, $template, $request, $db, $phpbb_container,$config;
                    $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                   overwriteRequestParam('mode', 'unapproved_topics');
                    overwriteRequestParam('start',$oMbqDataPage->startNum);
                    $currentTopicsPerPage = $config['topics_per_page'];
                    $config['topics_per_page'] = $oMbqDataPage->numPerPage;

                    include_once($phpbb_root_path . 'includes/mcp/mcp_queue.' . $phpEx);
                    include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
                    if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
                    {
                        include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
                    }
                    include_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);
                    requireExtLibrary('fake_template');
                    $template = new fake_template();


                    $user->setup('mcp');
                    $pmaster = new p_master();
                    $mcp_queue = new mcp_queue($pmaster);

                    $mcp_queue->main(0,'unapproved_topics');

                    $config['topics_per_page'] = $currentTopicsPerPage;

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
                    $postRows = $template->getTemplateBlockVar('postrow');

                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    $newMbqOpt['case'] = 'byTopicId';
                    $newMbqOpt['oMbqEtForum'] = true;
                    $newMbqOpt['oMbqEtUser'] = true;
                    $newMbqOpt['oMbqDataPage'] = $oMbqDataPage;

                    //get subscribe users
                    $user_watch_row = array();
                    $sql = 'SELECT * FROM ' . TOPICS_WATCH_TABLE .' WHERE user_id = ' . $user->data['user_id'];
                    $result = $db->sql_query($sql);
                    while ($row = $db->sql_fetchrow($result))
                    {
                        $user_watch_row[$row['topic_id']] = $row['notify_status'];
                    }
                    $db->sql_freeresult($result);
                    $newMbqOpt['user_watch_row'] = $user_watch_row;


                    foreach($postRows as $postRow)
                    {
                        $sql = 'SELECT * FROM ' . POSTS_TABLE .' WHERE post_id = ' . $postRow['POST_ID'];
                        $result = $db->sql_query($sql);
                        $row = $db->sql_fetchrow($result);
                        $oMbqDataPage->datas[] = $oMbqRdEtForumTopic->initOMbqEtForumTopic($row['topic_id'], $newMbqOpt);
                    }
                    $vars = $template->getTemplateVars();
                    if(getPHPBBVersion() == '3.0')
                    {
                        list($total)  = sscanf($template->_tpldata['.'][0]['TOTAL'], $user->lang['VIEW_FORUM_TOPICS']);
                        $oMbqDataPage->totalNum = $total;
                    }
                    else
                    {
                        $oMbqDataPage->totalNum =  $template->pagination->total;
                    }
                    return $oMbqDataPage;
                }
            case 'deleted':
                {
                    global $user,$phpbb_root_path, $phpEx, $template, $request, $db, $phpbb_container,$config;
                    requireExtLibrary('fake_template');
                    $template = new fake_template();
                    $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                    overwriteRequestParam('mode', 'deleted_topics');
                    overwriteRequestParam('start',$oMbqDataPage->startNum);
                    $currentTopicsPerPage = $config['topics_per_page'];
                    $config['topics_per_page'] = $oMbqDataPage->numPerPage;

                    include_once($phpbb_root_path . 'includes/mcp/mcp_queue.' . $phpEx);
                    include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
                    if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
                    {
                        include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
                    }
                    include_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);

                    $user->setup('mcp');
                    $pmaster = new p_master();
                    $mcp_queue = new mcp_queue($pmaster);

                    $mcp_queue->main(0,'deleted_topics');

                    $config['topics_per_page'] = $currentTopicsPerPage;

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
                    $postRows = $template->getTemplateBlockVar('postrow');

                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    $newMbqOpt['case'] = 'byTopicId';
                    $newMbqOpt['oMbqEtForum'] = true;
                    $newMbqOpt['oMbqEtUser'] = true;
                    $newMbqOpt['oMbqDataPage'] = $oMbqDataPage;

                    //get subscribe users
                    $user_watch_row = array();
                    $sql = 'SELECT * FROM ' . TOPICS_WATCH_TABLE .' WHERE user_id = ' . $user->data['user_id'];
                    $result = $db->sql_query($sql);
                    while ($row = $db->sql_fetchrow($result))
                    {
                        $user_watch_row[$row['topic_id']] = $row['notify_status'];
                    }
                    $db->sql_freeresult($result);
                    $newMbqOpt['user_watch_row'] = $user_watch_row;
                    if(isset($postRows))
                    {
                        foreach($postRows as $postRow)
                        {
                            $oMbqDataPage->datas[] = $oMbqRdEtForumTopic->initOMbqEtForumTopic($postRow['TOPIC_ID'], $newMbqOpt);
                        }
                        $vars = $template->getTemplateVars();
                    }
                    $oMbqDataPage->totalNum =  $template->pagination->total;
                    return $oMbqDataPage;
                }
            case 'byAuthor':
                {
                    $oMbqEtUser = $var;
                    $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                    global $request, $db, $template, $user, $auth, $config, $can_subscribe, $show_results, $include_topic_num, $total_match_count, $request_method, $searchResults;
                    $request_method = 'search';
                    $include_topic_num = true;

                    overwriteRequestParam('page', $oMbqDataPage->curPage);
                    overwriteRequestParam('perpage', $oMbqDataPage->numPerPage);
                    overwriteRequestParam('submit', 'Search');
                    overwriteRequestParam('sr', 'topics');


                    overwriteRequestParam('sf', 'all');

                    overwriteRequestParam('author', $oMbqEtUser->userName->oriValue);
                    overwriteRequestParam('author_id', $oMbqEtUser->userId->oriValue);

                    requireExtLibrary('search_clone');
                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                    $newMbqOpt['case'] = 'byRow';
                    $newMbqOpt['oMbqEtForum'] = true;
                    $newMbqOpt['oMbqEtUser'] = true;
                    $newMbqOpt['oMbqDataPage'] = $oMbqDataPage;

                    //get subscribe users
                    $user_watch_row = array();
                    $sql = 'SELECT * FROM ' . TOPICS_WATCH_TABLE .' WHERE user_id = ' . $user->data['user_id'];
                    $result = $db->sql_query($sql);
                    while ($row = $db->sql_fetchrow($result))
                    {
                        $user_watch_row[$row['topic_id']] = $row['notify_status'];
                    }
                    $db->sql_freeresult($result);
                    $newMbqOpt['user_watch_row'] = $user_watch_row;

                    $newMbqOpt['oMbqEtForumTopic'] = true;
                    $newMbqOpt['oMbqEtUser'] = true;
                    $rowset = array();
                    foreach($searchResults as $item)
                    {
                        $rowset[] = $item['bind'];
                    }
                    $this->prepare($rowset);
                    foreach($rowset as $row)
                    {
                        $oMbqDataPage->datas[] = $oMbqRdEtForumTopic->initOMbqEtForumTopic($row, $newMbqOpt);
                    }
                    $oMbqDataPage->totalNum =  $total_match_count ? $total_match_count : 0;
                    return $oMbqDataPage;
                }
        }
    }
    /**
     * init one forum topic by condition
     *
     * @return  Mixed
     */
    public function initOMbqEtForumTopic($var, $mbqOpt) {
        global $db, $auth, $user, $config;
        if ($mbqOpt['case'] == 'byRow') {
            if(!isset($mbqOpt['user_watch_row']))
            {
                //get subscribe users
                $user_watch_row = array();
                $sql = 'SELECT * FROM ' . TOPICS_WATCH_TABLE .' WHERE user_id = ' . $user->data['user_id'];
                $result = $db->sql_query($sql);
                while ($row = $db->sql_fetchrow($result))
                {
                    $user_watch_row[$row['topic_id']] = $row['notify_status'];
                }
                $db->sql_freeresult($result);
                $mbqOpt['user_watch_row'] = $user_watch_row;
            }
            $row = isset($var['bind']) ? $var['bind'] : $var;
            $forum_id = $row['forum_id'];
            if(getPHPBBVersion() == '3.0')
            {
                $replies = ($auth->acl_get('m_approve', $forum_id)) ? $row['topic_replies_real']+1 : $row['topic_replies'] +1;

            }
            else
            {
                $replies = ($auth->acl_get('m_approve', $forum_id)) ? $row['topic_posts_approved'] + $row['topic_posts_unapproved'] + $row['topic_posts_softdeleted']   : $row['topic_posts_approved'];
            }

            $new_post = false;
            if ($user->data['user_id'] != ANONYMOUS)
            {
                if(empty($forum_id) || empty($row['topic_id']))
                {
                    return null;
                }
                $topic_tracking = get_complete_topic_tracking($forum_id, $row['topic_id']);
                $new_post = $topic_tracking[$row['topic_id']] < $row['topic_last_post_time'] ? true : false;
            }

            //$allow_change_type = ($auth->acl_get('m_', $forum_id) || ($user->data['is_registered'] && $user->data['user_id'] == $row['topic_poster'])) ? true : false;

            $topic_id = $row['topic_moved_id'] ? $row['topic_moved_id'] : $row['topic_id'];
            //        $icon_urls = array();
            //        foreach($topic_users[$topic_id] as $posterid){
            //            $icon_urls[] = new xmlrpcval($user_avatar[$posterid], 'string');
            //        }
            $can_rename = ($user->data['is_registered'] && ($auth->acl_get('m_edit', $forum_id) || (
                    $user->data['user_id'] == $row['topic_poster'] &&
                    $auth->acl_get('f_edit', $forum_id) &&
                    //!$item['post_edit_locked'] &&
                    ($row['topic_time'] > time() - ($config['edit_time'] * 60) || !$config['edit_time'])
                )));


            $oMbqEtForumTopic = MbqMain::$oClk->newObj('MbqEtForumTopic');
            $oMbqEtForumTopic->totalPostNum->setOriValue($replies);
            $oMbqEtForumTopic->topicId->setOriValue($row['topic_id']);
            $oMbqEtForumTopic->forumId->setOriValue($forum_id);
            $oMbqEtForumTopic->firstPostId->setOriValue($row['topic_first_post_id']);

            if($mbqOpt['oMbqEtForum'])
            {
                if(is_a($mbqOpt['oMbqEtForum'],'MbqEtForum'))
                {
                    $oMbqEtForumTopic->oMbqEtForum = $mbqOpt['oMbqEtForum'];
                }
                else
                {
                    $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
                    $oMbqEtForumTopic->oMbqEtForum = $oMbqRdEtForum->initOMbqEtForum($oMbqEtForumTopic->forumId->oriValue, array('case' => 'byForumId'));
                }

            }

            $oMbqEtForumTopic->topicTitle->setOriValue(html_entity_decode(strip_tags(censor_text($row['topic_title'])), ENT_QUOTES, 'UTF-8'));
            if($oMbqEtForumTopic->topicTitle->oriValue == '')
            {
                if($row['topic_title'] != '')
                {
                    $oMbqEtForumTopic->topicTitle->setOriValue($row['topic_title']);
                }
                else
                {
                    $oMbqEtForumTopic->topicTitle->setOriValue('--');
                }
            }
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $oMbqEtForumTopic->oAuthorMbqEtUser = $oMbqRdEtUser->initOMbqEtUSer($row['topic_poster'], array('case' => 'byUserId'));
            if ((int)$row['topic_poster'] == 0 && isset($row['topic_first_poster_name']) && $row['topic_first_poster_name']) {
                /** @var MbqEtForumTopic $oMbqEtForumTopic */
                $oMbqEtForumTopic->firstPosterName->setOriValue($row['topic_first_poster_name']);
            }
            $oMbqEtForumTopic->oLastReplyMbqEtUser = $oMbqRdEtUser->initOMbqEtUSer($row['topic_last_poster_id'], array('case' => 'byUserId'));

            $oMbqEtForumTopic->authorIconUrl->setOriValue($oMbqEtForumTopic->oAuthorMbqEtUser->iconUrl->oriValue);
            //         $oMbqEtForumTopic->topicContent->setOriValue($var->first_post_message);
            if(MbqMain::$cmd =='get_topic'){
                $short_content = basic_clean(get_short_content($row['topic_first_post_id']));
                $oMbqEtForumTopic->shortContent->setOriValue($short_content);
                $oMbqEtForumTopic->postTime->setOriValue($row['topic_time']);
            }else{
                $short_content = basic_clean(get_short_content($row['topic_last_post_id']));
                $oMbqEtForumTopic->shortContent->setOriValue($short_content);
                $oMbqEtForumTopic->postTime->setOriValue($row['topic_last_post_time']);
            }
            $oMbqEtForumTopic->topicAuthorId->setOriValue($row['topic_poster']);
            $oMbqEtForumTopic->lastReplyAuthorId->setOriValue($row['topic_last_poster_id']);
            $oMbqEtForumTopic->lastReplyTime->setOriValue($row['topic_last_post_time']);
            $oMbqEtForumTopic->replyNumber->setOriValue($replies-1);
            $oMbqEtForumTopic->newPost->setOriValue($new_post);
            $oMbqEtForumTopic->canRename->setOriValue($can_rename);
            $oMbqEtForumTopic->canReply->setOriValue($auth->acl_get('f_reply', $forum_id) && ($auth->acl_get('m_edit', $forum_id) || ($oMbqEtForumTopic->oMbqEtForum->mbqBind['forum_status'] != ITEM_LOCKED && $row['topic_status'] != ITEM_LOCKED)));
            $oMbqEtForumTopic->isSticky->setOriValue($row['topic_type'] == POST_STICKY);
            $oMbqEtForumTopic->canStick->setOriValue($auth->acl_get('f_sticky', $forum_id));
	    	if(getPHPBBVersion() == '3.0')
	        {
                $oMbqEtForumTopic->isDeleted->setOriValue($row['topic_status'] == 1);
	            $oMbqEtForumTopic->isApproved->setOriValue($row['topic_approved'] ? true : false);
	        }
	        else
	        {
                $oMbqEtForumTopic->isDeleted->setOriValue($row['topic_visibility'] == ITEM_DELETED);
                $oMbqEtForumTopic->isApproved->setOriValue($row['topic_visibility'] != ITEM_UNAPPROVED);
            }
            $oMbqEtForumTopic->canDelete->setOriValue($auth->acl_get('m_delete', $forum_id));
            $oMbqEtForumTopic->isClosed->setOriValue($row['topic_status'] == ITEM_LOCKED);
            $oMbqEtForumTopic->canClose->setOriValue($auth->acl_get('m_lock', $forum_id) || ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && $user->data['user_id'] == $row['topic_poster']));
            $oMbqEtForumTopic->canApprove->setOriValue($auth->acl_get('m_approve', $forum_id));
            $oMbqEtForumTopic->canMove->setOriValue($auth->acl_get('m_move', $forum_id));
            $oMbqEtForumTopic->isSubscribed->setOriValue(isset($mbqOpt['user_watch_row'][$topic_id]));
            $oMbqEtForumTopic->canSubscribe->setOriValue(($config['email_enable'] || $config['jab_enable']) && $config['allow_topic_notify'] && $user->data['is_registered']);
            $oMbqEtForumTopic->viewNumber->setOriValue($row['topic_views']);
            $oMbqEtForumTopic->isMoved->setOriValue(!empty($row['topic_moved_id']) ? true : false);
            $oMbqEtForumTopic->realTopicId->setOriValue($row['topic_moved_id'] ? $row['topic_moved_id'] : $row['topic_id']);
            $oMbqEtForumTopic->canBan->setOriValue($auth->acl_get('m_ban') && $row['topic_poster'] != $user->data['user_id']);
            $oMbqEtForumTopic->canMerge->setOriValue($auth->acl_get('m_merge', $forum_id));

            $oMbqEtForumTopic->hasPoll->setOriValue($row['poll_start'] ? 1 : 0);


            if(isset($mbqOpt['oMbqEtUser']))
            {
                $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
                $oMbqEtForumTopic->oAuthorMbqEtUser = $oMbqRdEtUser->initOMbqEtUSer($oMbqEtForumTopic->topicAuthorId->oriValue, array('case' => 'byUserId'));
                $oMbqEtForumTopic->oLastReplyMbqEtUser = $oMbqRdEtUser->initOMbqEtUSer($oMbqEtForumTopic->lastReplyAuthorId->oriValue, array('case' => 'byUserId'));
            }
            $oMbqEtForumTopic->mbqBind['TopicRow'] = $row;
            return $oMbqEtForumTopic;
        }
        elseif ($mbqOpt['case'] == 'byTopicId') {
            global $db, $user;
            if(MbqMain::$Cache->Exists('MbqEtForumTopic',$var))
            {
                return MbqMain::$Cache->Get('MbqEtForumTopic',$var);
            }
            else
            {
                $objsMbqEtForumTopic = $this->getObjsMbqEtForumTopic($var, array('case' => 'byTopicIds'));
                $objMbqEtForumTopic = null;
                if(sizeof($objsMbqEtForumTopic) == 1)
                {
                    $objMbqEtForumTopic = $objsMbqEtForumTopic[0];


                    $forum_id = $objMbqEtForumTopic->forumId->oriValue;
                    $topic_id = $objMbqEtForumTopic->topicId->oriValue;
                    $topic_traking_info = get_complete_topic_tracking($forum_id, $topic_id);
                    $timestamp = isset($topic_traking_info[$topic_id]) ? $topic_traking_info[$topic_id] : null;
                    $user_id = $user->data['user_id'];

                    if(getPHPBBVersion() == '3.0')
                    {
                        if(isset($timestamp))
                        {
                            $sql = "select t.total - u.unread as position, t.total, u.unread
                        from (
                        select count(post_id) as total from " . POSTS_TABLE . "
                        where topic_id = $topic_id and post_approved = 1) t
                        left join (
                        select count(post_id) as unread from " . POSTS_TABLE . "
                        where topic_id = $topic_id and post_approved = 1 and post_time > $timestamp) u on 1=1";
                        }
                        else
                        {
                            $sql = 'select t.total - u.unread as position, t.total, u.unread
                        from (
                        select count(post_id) as total from ' . POSTS_TABLE . '
                        where topic_id = ' . $topic_id . ' and post_approved = 1) t
                        left join (
                        select count(post_id) as unread from ' . POSTS_TABLE . '
                        where topic_id = ' . $topic_id . ' and post_approved = 1 and post_time > (select case when MAX(mark_time) is null then 0 else MAX(mark_time) end as mark_time from ' . TOPICS_TRACK_TABLE . ' where topic_id = ' . $topic_id . ' and user_id = ' . $user_id . ')) u on 1=1';

                        }
                    }
                    else
                    {
                        if(isset($timestamp))
                        {
                            $sql = "select t.total - u.unread as position, t.total, u.unread
                        from (
                        select count(post_id) as total from " . POSTS_TABLE . "
                        where topic_id = $topic_id and post_visibility = 1) t
                        left join (
                        select count(post_id) as unread from " . POSTS_TABLE . "
                        where topic_id = $topic_id and post_visibility = 1 and post_time > $timestamp) u on 1=1";
                        }
                        else
                        {
                            $sql = 'select t.total - u.unread as position, t.total, u.unread
                        from (
                        select count(post_id) as total from ' . POSTS_TABLE . '
                        where topic_id = ' . $topic_id . ' and post_visibility = 1) t
                        left join (
                        select count(post_id) as unread from ' . POSTS_TABLE . '
                        where topic_id = ' . $topic_id . ' and post_visibility = 1 and post_time > (select case when MAX(mark_time) is null then 0 else MAX(mark_time) end as mark_time from ' . TOPICS_TRACK_TABLE . ' where topic_id = ' . $topic_id . ' and user_id = ' . $user_id . ')) u on 1=1';

                        }
                    }

                    $result = $db->sql_query($sql);
                    $row = $db->sql_fetchrow($result);

                    $objMbqEtForumTopic->firstUnreadPosition->setOriValue($row['position']);
                    MbqMain::$Cache->Set('MbqEtForumTopic',$topic_id,$objMbqEtForumTopic);
                    return $objMbqEtForumTopic;
                }
                return null;
            }
        }
    }
    public function prepareUsers($topicRows)
    {
        $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
        $userIdsToPreload = array();
        foreach($topicRows as $row)
        {
            if(!in_array($row['topic_poster'], $userIdsToPreload))
            {
                $userIdsToPreload[] = $row['topic_poster'];
            }
            if(!in_array($row['topic_last_poster_id'], $userIdsToPreload))
            {
                $userIdsToPreload[] = $row['topic_last_poster_id'];
            }
        }
        $oMbqRdEtUser->getObjsMbqEtUser($userIdsToPreload, array('case'=>'byUserIds'));
    }
    public function prepare($topicRows)
    {
        if(!empty($topicRows))
        {
            $this->prepareUsers($topicRows);
            $this->prepareForums($topicRows);
            $this->preparePostsShortContent($topicRows);
        }
    }
    public function prepareForums($topicRows)
    {
        $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
        $forumsToPreload = array();
        foreach($topicRows as $row)
        {
            if(!in_array($row['forum_id'], $forumsToPreload))
            {
                $forumsToPreload[] = $row['forum_id'];
            }
        }
        $oMbqRdEtForum->getObjsMbqEtForum($forumsToPreload, array('case'=>'byForumIds'));
    }
    public function preparePostsShortContent($topicRows)
    {
        global $db;
        $shortContentToPreload = array();
        foreach($topicRows as $row)
        {
            if(!in_array($row['topic_first_post_id'], $shortContentToPreload))
            {
                $shortContentToPreload[] = $row['topic_first_post_id'];
            }
            if(!in_array($row['topic_last_post_id'], $shortContentToPreload))
            {
                $shortContentToPreload[] = $row['topic_last_post_id'];
            }
        }
        $sql = 'SELECT post_id,post_text,bbcode_uid,bbcode_bitfield
            FROM ' . POSTS_TABLE . '
            WHERE post_id in (' . implode(',',$shortContentToPreload) . ')';
        $result = $db->sql_query($sql);
        while($row = $db->sql_fetchrow())
        {
            MbqMain::$Cache->Set('MbqPostShortContent',$row['post_id'],$row);
        }
        $db->sql_freeresult($result);

    }
    public function getUrl($oMbqEtForumTopic)
    {
        global $phpbb_root_path, $phpEx, $db, $auth, $config;
        $base = new  \tas2580\seourls\event\base($auth, $config, $phpbb_root_path);
        $topicUrl = $base->generate_topic_link($oMbqEtForumTopic->forumId->oriValue, $oMbqEtForumTopic->oMbqEtForum->forumName->oriValue, $oMbqEtForumTopic->topicId->oriValue, $oMbqEtForumTopic->topicTitle->oriValue, 0, true);
        return $topicUrl;//append_sid("{$phpbb_home}viewtopic.$phpEx", "f=$forumId&t=$topicId");
    }
}
