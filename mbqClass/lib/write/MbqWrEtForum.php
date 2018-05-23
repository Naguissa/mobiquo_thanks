<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrEtForum');

/**
 * forum write class
 */
Class MbqWrEtForum extends MbqBaseWrEtForum {

    public function __construct() {
    }
    /**
     * subscribe forum
     */
    public function subscribeForum($oMbqEtForum, $receiveEmail) {
        global $db, $user, $config, $auth, $request_params;

        $user->setup('viewforum');


        $forum_id = $oMbqEtForum->forumId->oriValue;
        $forum_data = $oMbqEtForum->mbqBind;
        if (!$forum_id) trigger_error('NO_FORUM');
        $user_id = $user->data['user_id'];
        $s_result = false;

        if (($config['email_enable'] || $config['jab_enable']) && $config['allow_forum_notify'] && $forum_data['forum_type'] == FORUM_POST && $auth->acl_get('f_subscribe', $forum_id))
        {
            $notify_status = (isset($forum_data['notify_status'])) ? $forum_data['notify_status'] : 'unset';

            $table_sql = FORUMS_WATCH_TABLE;
            $where_sql = 'forum_id';
            $match_id = $forum_id;

            // Is user watching this thread?
            if ($user_id != ANONYMOUS)
            {
                if ($notify_status == 'unset')
                {
                    $sql = "SELECT notify_status
                    FROM $table_sql
                    WHERE $where_sql = $match_id
                        AND user_id = $user_id";
                    $result = $db->sql_query($sql);

                    $notify_status = ($row = $db->sql_fetchrow($result)) ? $row['notify_status'] : NULL;
                    $db->sql_freeresult($result);
                }

                if (!is_null($notify_status) && $notify_status !== '')
                {
                    if ($notify_status)
                    {
                        $sql = 'UPDATE ' . $table_sql . "
                        SET notify_status = 0
                        WHERE $where_sql = $match_id
                            AND user_id = $user_id";
                        $db->sql_query($sql);
                    }
                }
                else
                {
                    $sql = 'INSERT INTO ' . $table_sql . " (user_id, $where_sql, notify_status)
                    VALUES ($user_id, $match_id, 0)";
                    $db->sql_query($sql);
                }
                $s_result = true;
            }
            else
            {
                return getSystemString('LOGIN_VIEWFORUM');
            }
        }
        else
        {
            $s_result = false;
        }

        return $s_result;
    }

    /**
     * unsubscribe forum
     */
    public function unsubscribeForum($oMbqEtForum) {
        global $db, $user, $config, $auth, $request_params;

        $user->setup('viewforum');

        $forum_id = $oMbqEtForum->forumId->oriValue;
        $forum_data = $oMbqEtForum->mbqBind;
        if (!$forum_id) trigger_error('NO_FORUM');
        $user_id = $user->data['user_id'];

        $s_result = false;
        if (($config['email_enable'] || $config['jab_enable']) && $config['allow_forum_notify'] && $forum_data['forum_type'] == FORUM_POST && $auth->acl_get('f_subscribe', $forum_id))
        {
            $notify_status = (isset($forum_data['notify_status'])) ? $forum_data['notify_status'] : 'unset';

            $table_sql = FORUMS_WATCH_TABLE;
            $where_sql = 'forum_id';
            $match_id = $forum_id;

            // Is user watching this thread?
            if ($user_id != ANONYMOUS)
            {
                $can_watch = true;

                if ($notify_status == 'unset')
                {
                    $sql = "SELECT notify_status
                    FROM $table_sql
                    WHERE $where_sql = $match_id
                        AND user_id = $user_id";
                    $result = $db->sql_query($sql);

                    $notify_status = ($row = $db->sql_fetchrow($result)) ? $row['notify_status'] : NULL;
                    $db->sql_freeresult($result);
                }

                if (!is_null($notify_status) && $notify_status !== '')
                {
                    $sql = 'DELETE FROM ' . $table_sql . "
                    WHERE $where_sql = $match_id
                        AND user_id = $user_id";
                    $db->sql_query($sql);
                }

                $s_result = true;
            }
            else
            {
                return getSystemString('LOGIN_VIEWFORUM');
            }
        }
        else
        {
            $s_result = false;
        }


        return $s_result;
    }

    public function markForumRead($oMbqEtForum){
        global $db, $auth;

        if($oMbqEtForum != null)
        {
            $forum_id = $oMbqEtForum->forumId->oriValue;
            $forum_ids[] = $forum_id;

            $sql = "SELECT *
                FROM " . FORUMS_TABLE . "
                WHERE forum_id = " . $forum_id;
            $result = $db->sql_query($sql);
            if ($forum_data = $db->sql_fetchrow($result))
            {
                $sub_sql = "SELECT *
                    FROM " . FORUMS_TABLE . "
                    WHERE left_id > " . $forum_data['left_id'] . " AND left_id < " . $forum_data['right_id'];
                $sub_result = $db->sql_query($sub_sql);
                while ($subforum_data = $db->sql_fetchrow($sub_result))
                {
                    if ($auth->acl_get('f_list', $subforum_data['forum_id']))
                    {
                        $forum_ids[] = $subforum_data['forum_id'];
                    }
                }
            }
            $db->sql_freeresult($result);

            markread('topics', $forum_ids);
        }
        else
        {
            markread('all');
        }
        return true;
    }

}
