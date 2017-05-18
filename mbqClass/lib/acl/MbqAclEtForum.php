<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseAclEtForum');

/**
 * forum acl class
 */
Class MbqAclEtForum extends MbqBaseAclEtForum {

    public function __construct() {
    }

    /**
     * judge can get subscribed forum
     *
     * @return  Boolean
     */
    public function canAclGetSubscribedForum() {
        return MbqMain::hasLogin();
    }

    /**
     * judge can subscribe forum
     *
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclSubscribeForum($oMbqEtForum, $receiveEmail) {
        global $db, $user, $config, $auth, $request_params;

        $user->setup('viewforum');

        $forum_id = $oMbqEtForum->forumId->oriValue;
        if (!$forum_id)  return getSystemString('NO_FORUM');
        $user_id = $user->data['user_id'];

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

        if (!$forum_data)  return getSystemString('NO_FORUM');

        // Permissions check
        if (!$auth->acl_gets('f_list', 'f_read', $forum_id) || ($forum_data['forum_type'] == FORUM_LINK && $forum_data['forum_link'] && !$auth->acl_get('f_read', $forum_id)))
        {
            if ($user->data['user_id'] != ANONYMOUS)
            {
                 return getSystemString('SORRY_AUTH_READ');
            }

             return getSystemString('LOGIN_VIEWFORUM');
        }

        // Forum is passworded ... check whether access has been granted to this
        // user this session, if not show login box
        if ($forum_data['forum_password'] && !check_forum_password($forum_id))
             return getSystemString('LOGIN_FORUM');

        // Is this forum a link? ... User got here either because the
        // number of clicks is being tracked or they guessed the id
        if ($forum_data['forum_type'] == FORUM_LINK && $forum_data['forum_link'])
        {
             return getSystemString('NO_FORUM');
        }

        // Not postable forum or showing active topics?
        if (!($forum_data['forum_type'] == FORUM_POST || (($forum_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS) && $forum_data['forum_type'] == FORUM_CAT)))
        {
             return getSystemString('NO_FORUM');
        }

        // Ok, if someone has only list-access, we only display the forum list.
        // We also make this circumstance available to the template in case we want to display a notice. ;)
        if (!$auth->acl_get('f_read', $forum_id))
        {
             return getSystemString('SORRY_AUTH_READ');
        }
        return true;
    }

    /**
     * judge can unsubscribe forum
     *
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclUnsubscribeForum($oMbqEtForum) {
        global $db, $user, $config, $auth, $request_params;

        $user->setup('viewforum');

        $forum_id = $oMbqEtForum->forumId->oriValue;
        if (!$forum_id)  return getSystemString('NO_FORUM');
        $user_id = $user->data['user_id'];

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

        if (!$forum_data)  return getSystemString('NO_FORUM');

        // Permissions check
        if (!$auth->acl_gets('f_list', 'f_read', $forum_id) || ($forum_data['forum_type'] == FORUM_LINK && $forum_data['forum_link'] && !$auth->acl_get('f_read', $forum_id)))
        {
            if ($user->data['user_id'] != ANONYMOUS)
            {
                 return getSystemString('SORRY_AUTH_READ');
            }

             return getSystemString('LOGIN_VIEWFORUM');
        }

        // Forum is passworded ... check whether access has been granted to this
        // user this session, if not show login box
        if ($forum_data['forum_password'] && !check_forum_password($forum_id))
             return getSystemString('LOGIN_FORUM');

        // Is this forum a link? ... User got here either because the
        // number of clicks is being tracked or they guessed the id
        if ($forum_data['forum_type'] == FORUM_LINK && $forum_data['forum_link'])
             return getSystemString('NO_FORUM');

        // Not postable forum or showing active topics?
        if (!($forum_data['forum_type'] == FORUM_POST || (($forum_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS) && $forum_data['forum_type'] == FORUM_CAT)))
             return getSystemString('NO_FORUM');

        // Ok, if someone has only list-access, we only display the forum list.
        // We also make this circumstance available to the template in case we want to display a notice. ;)
        if (!$auth->acl_get('f_read', $forum_id))
        {
             return getSystemString('SORRY_AUTH_READ');
        }

        return true;
    }
    /**
     * judge can mark all my unread topics as read
     *
     * @return  Boolean
     */
    public function canAclMarkAllAsRead($oMbqEtForum) {
        return MbqMain::hasLogin();
    }
}
