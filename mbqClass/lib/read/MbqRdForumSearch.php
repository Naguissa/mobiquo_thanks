<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdForumSearch');

/**
 * forum search class
 */
Class MbqRdForumSearch extends MbqBaseRdForumSearch {

    public function __construct() {
    }
    /**
     * forum advanced search
     *
     * @param  Array  $filter  search filter
     * @param  Object  $oMbqDataPage
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'advanced' means advanced search
     * $mbqOpt['participated'] = true means get participated data
     * $mbqOpt['unread'] = true means get unread data
     * @return  Object  $oMbqDataPage
     */
    public function forumAdvancedSearch($filter, $oMbqDataPage, $mbqOpt) {
        global $request, $db, $template, $user, $auth, $config, $can_subscribe, $show_results, $include_topic_num, $total_match_count, $request_method, $searchResults;

        if ($mbqOpt['case'] == 'getLatestTopic') {

            $request_method = 'get_latest_topic';
            $include_topic_num = true;
            overwriteRequestParam('page', $filter['page']);
            overwriteRequestParam('perpage', $filter['perpage']);
            overwriteRequestParam('search_id', 'latesttopics');

            if (isset($filter['only_in']) && is_array($filter['only_in']))
            {
                overwriteRequestParam('fid', array_map('intval', $filter['only_in']));
            }

            if (isset($filter['not_in']) && is_array($filter['not_in']))
            {
                overwriteRequestParam('exclude', array_map('intval', $filter['not_in']));
            }
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
            $topicRows = array();
            foreach($searchResults as $item)
            {
                $topicRows[] = $item['bind'];
            }
            $oMbqRdEtForumTopic->prepare($topicRows);
            foreach($topicRows as $topicRow)
            {
               $oMbqDataPage->datas[] = $oMbqRdEtForumTopic->initOMbqEtForumTopic($topicRow, $newMbqOpt);
            }
            $oMbqDataPage->totalNum = $total_match_count;
            return $oMbqDataPage;
        }
        elseif ($mbqOpt['case'] == 'getUnreadTopic')
        {
            $request_method = 'get_unread_topic';

            $include_topic_num = true;
            overwriteRequestParam('page', $filter['page']);
            overwriteRequestParam('perpage', $filter['perpage']);
            overwriteRequestParam('search_id', 'unreadposts');

            if (isset($filter['only_in']) && is_array($filter['only_in']))
            {
                overwriteRequestParam('fid', array_map('intval', $filter['only_in']));
            }

            if (isset($filter['not_in']) && is_array($filter['not_in']))
            {
                overwriteRequestParam('exclude', array_map('intval', $filter['not_in']));
            }
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
            if(isset($searchResults))
            {
                $topicRows = array();
                foreach($searchResults as $item)
                {
                    $topicRows[] = $item['bind'];
                }
                $oMbqRdEtForumTopic->prepare($topicRows);
                foreach($topicRows as $topicRow)
                {
                    $oMbqDataPage->datas[] = $oMbqRdEtForumTopic->initOMbqEtForumTopic($topicRow, $newMbqOpt);
                }
            }
            $oMbqDataPage->totalNum = $total_match_count;
            return $oMbqDataPage;
        }
        elseif ($mbqOpt['case'] == 'getParticipatedTopic')
        {
            $request_method = 'get_participated_topic';

            $include_topic_num = true;
            overwriteRequestParam('page', $filter['page']);
            overwriteRequestParam('perpage', $filter['perpage']);

            overwriteRequestParam('sr', 'topics');
            overwriteRequestParam('submit', 'Search');
            overwriteRequestParam('search_id', 'getParticipatedTopic');

            if (isset($filter['userid']) && $filter['userid']) {
                overwriteRequestParam('author_id', intval($filter['userid']));
            } else if (isset($filter['searchuser']) && $filter['searchuser']) {
                overwriteRequestParam('author', $filter['searchuser']);
            } else {
                overwriteRequestParam('search_id', 'egosearch');
            }

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

            $topicRows = array();
            foreach($searchResults as $item)
            {
                $topicRows[] = $item['bind'];
            }
            $oMbqRdEtForumTopic->prepare($topicRows);
            foreach($topicRows as $topicRow)
            {
                $oMbqDataPage->datas[] = $oMbqRdEtForumTopic->initOMbqEtForumTopic($topicRow, $newMbqOpt);
            }
            $oMbqDataPage->totalNum = $total_match_count;
            return $oMbqDataPage;
        }
        elseif ($mbqOpt['case'] == 'getSubscribedTopic')
        {
            $request_method = 'get_subscribed_topic';

            $include_topic_num = true;
            overwriteRequestParam('page', $filter['page']);
            overwriteRequestParam('perpage', $filter['perpage']);

            overwriteRequestParam('search_id', 'subscribedtopics');

            requireExtLibrary('search_clone');
            $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
            $newMbqOpt['case'] = 'byRow';
            $newMbqOpt['oMbqEtForum'] = true;
            $newMbqOpt['oMbqEtUser'] = true;
            $newMbqOpt['oMbqDataPage'] = $oMbqDataPage;
            $searchResults = $template->_tpldata['searchresults'];
            $topicRows = array();
            foreach($searchResults as $item)
            {
                $topicRows[] = $item['bind'];
            }
            $oMbqRdEtForumTopic->prepare($topicRows);
            foreach($topicRows as $topicRow)
            {
                $oMbqDataPage->datas[] = $oMbqRdEtForumTopic->initOMbqEtForumTopic($topicRow, $newMbqOpt);
            }
            $oMbqDataPage->totalNum = $total_match_count;
            return $oMbqDataPage;
        }
        elseif ($mbqOpt['case'] == 'searchTopic')
        {
            $include_topic_num = true;
            overwriteRequestParam('page', $oMbqDataPage->curPage);
            overwriteRequestParam('perpage',  $oMbqDataPage->numPerPage);
            overwriteRequestParam('submit',  'Search');
            overwriteRequestParam('sr', 'topics');
            overwriteRequestParam('sf', 'all');
            overwriteRequestParam('keywords', $filter['keywords']);

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
            if(isset($searchResults))
            {
                $oMbqRdEtForumTopic->prepare($searchResults);
                foreach($searchResults as $topicRow)
                {
                    $oMbqDataPage->datas[] = $oMbqRdEtForumTopic->initOMbqEtForumTopic($topicRow, $newMbqOpt);
                }
            }
            $oMbqDataPage->totalNum = $total_match_count;
            return $oMbqDataPage;
        } elseif ($mbqOpt['case'] == 'searchPost') {

            $include_topic_num = true;
            $request_file = 'search';
            overwriteRequestParam('page', $oMbqDataPage->curPage);
            overwriteRequestParam('perpage', $oMbqDataPage->numPerPage);
            overwriteRequestParam('submit', 'Search');
            overwriteRequestParam('sr', 'posts');
            overwriteRequestParam('sf', 'all');
            overwriteRequestParam('keywords', $filter['keywords']);

            requireExtLibrary('search_clone');

            $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
            $newMbqOpt['case'] = 'byRow';
            $newMbqOpt['oMbqDataPage'] = $oMbqDataPage;
            $newMbqOpt['oMbqEtForum'] = true;
            $newMbqOpt['oMbqEtForumTopic'] = true;
            $newMbqOpt['oMbqEtUser'] = true;
            if(isset($searchResults))
            {
                foreach($searchResults as $item)
                {
                    $oMbqDataPage->datas[] = $oMbqRdEtForumPost->initOMbqEtForumPost($item, $newMbqOpt);
                }
            }
            $oMbqDataPage->totalNum = $total_match_count;
            return $oMbqDataPage;
        } elseif ($mbqOpt['case'] == 'search') {

            $request_method = 'search';
            $include_topic_num = true;

            overwriteRequestParam('page', $oMbqDataPage->curPage);
            overwriteRequestParam('perpage', $oMbqDataPage->numPerPage);
            overwriteRequestParam('submit', 'Search');
            overwriteRequestParam('sr', $filter->showPosts ? 'posts' : 'topics');
            overwriteRequestParam('started_by', $filter->startedBy);
            if($filter->titleOnly)
            {
                overwriteRequestParam('sf', 'titleonly');
            }
            else if(($filter->userId || $filter->searchUser) && $filter->showPosts == false)
            {
                overwriteRequestParam('sf', 'firstpost');
            }
            else
            {
                overwriteRequestParam('sf', 'all');
            }
            overwriteRequestParam('searchid', $filter->searchId);
            overwriteRequestParam('keywords', $filter->keywords);
            if(isset($filter->username))
            {
                overwriteRequestParam('author', $filter->username);
            }
            else
            {
                overwriteRequestParam('author', $filter->searchUser);
            }
            overwriteRequestParam('author_id', $filter->userId);
            overwriteRequestParam('fid', isset($filter->forumId) ? array($filter->forumId) : $filter->forumId);

            if (!empty($filter->topicId))
            {
                overwriteRequestParam('t', $filter->topicId);
                overwriteRequestParam('sf', 'msgonly');
                overwriteRequestParam('showresults', 'posts');
            }

            if (!empty($filter->searchTime) && is_numeric($filter->searchTime))
            {
               overwriteRequestParam('st', $filter->searchTime/86400);
            }

            if (isset($in->onlyIn) && is_array($in->onlyIn))
            {
                overwriteRequestParam('fid', array_map('intval', $in->onlyIn));
            }

            if (isset($in->notIn) && is_array($in->notIn))
            {
                //add for tapatalk
                foreach ($in->notIn as $key=>$value)
                {
                    if($value != 0)
                    {
                        $ex_fid_ary[]=$in->notIn[$key];
                    }
                }
                overwriteRequestParam('exclude', array_map('intval', $ex_fid_ary));
            }
            requireExtLibrary('search_clone');
            $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
            $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
            $newMbqOpt['case'] = 'byRow';
            $newMbqOpt['oMbqEtForum'] = true;
            $newMbqOpt['oMbqEtUser'] = true;
            $newMbqOpt['oMbqDataPage'] = $oMbqDataPage;
            if($filter->showPosts)
            {
                $newMbqOpt['oMbqEtForumTopic'] = true;
                $newMbqOpt['oMbqEtUser'] = true;
                if($searchResults)
                {
                    foreach($searchResults as $item)
                    {
                        $oMbqDataPage->datas[] = $oMbqRdEtForumPost->initOMbqEtForumPost($item, $newMbqOpt);
                    }
                }
                $oMbqDataPage->totalNum = $total_match_count ? $total_match_count : 0;
            }
            else
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
                $newMbqOpt['user_watch_row'] = $user_watch_row;

                $newMbqOpt['oMbqEtForumTopic'] = true;
                $newMbqOpt['oMbqEtUser'] = true;
                if($searchResults)
                {
                    $topicRows = array();
                    foreach($searchResults as $item)
                    {
                        $topicRows[] = $item['bind'];
                    }
                    $oMbqRdEtForumTopic->prepare($topicRows);
                    foreach($topicRows as $topicRow)
                    {
                        $oMbqDataPage->datas[] = $oMbqRdEtForumTopic->initOMbqEtForumTopic($topicRow, $newMbqOpt);
                    }
                }
                $oMbqDataPage->totalNum =  $total_match_count ? $total_match_count : 0;
            }

            return $oMbqDataPage;
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }

}

