<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseAclEtForumPost');

/**
 * forum post acl class
 */
Class MbqAclEtForumPost extends MbqBaseAclEtForumPost {

    public function __construct() {
    }
    /**
     * judge can reply post
     *
     * @param  Object  $oMbqEtForumPost
     * @return  Boolean
     */
    public function canAclReplyPost($oMbqEtForumPost) {
        global $user,$auth;
        if (!$user->data['is_registered'])  return getSystemString('LOGIN_EXPLAIN_POST');
        $post_data = $oMbqEtForumPost->oMbqEtForum->mbqBind;
        // Use post_row values in favor of submitted ones...
        $forum_id    = $oMbqEtForumPost->oMbqEtForum->forumId->oriValue;
        $topic_id    = $oMbqEtForumPost->topicId->oriValue;

        // Need to login to passworded forum first?
        if ($post_data['forum_password'] && !check_forum_password($forum_id))
             return getSystemString('LOGIN_FORUM');

        // Check permissions
        if ($user->data['is_bot'])  return getSystemString('NOT_AUTHORISED');

        // Is the user able to read within this forum?
        if (!$auth->acl_get('f_read', $forum_id))
        {
            if ($user->data['user_id'] != ANONYMOUS)
            {
                 return getSystemString('USER_CANNOT_READ');
            }

             return getSystemString('LOGIN_EXPLAIN_POST');
        }

        // Permission to do the reply
        if (!$auth->acl_get('f_reply', $forum_id))
        {
            if ($user->data['user_id'] != ANONYMOUS)
            {
                 return getSystemString('USER_CANNOT_REPLY');
            }

             return getSystemString('LOGIN_EXPLAIN_POST');
        }

        // Is the user able to post within this forum?
        if ($post_data['forum_type'] != FORUM_POST)
        {
             return getSystemString('USER_CANNOT_FORUM_POST');
        }

        // Forum/Topic locked?
        if (($post_data['forum_status'] == ITEM_LOCKED || (isset($post_data['topic_status']) && $post_data['topic_status'] == ITEM_LOCKED)) && !$auth->acl_get('m_edit', $forum_id))
        {
            return (($post_data['forum_status'] == ITEM_LOCKED) ? getSystemString('FORUM_LOCKED') : getSystemString('TOPIC_LOCKED'));
        }

        return true;
    }

    /**
     * judge can guest reply post
     *
     * @param  Object  $oMbqEtForumPost
     * @return  Boolean
     */
    public function canAclGuestReplyPost($oMbqEtForumPost) {
        global $user,$auth;
        $post_data = $oMbqEtForumPost->oMbqEtForum->mbqBind;
        // Use post_row values in favor of submitted ones...
        $forum_id    = $oMbqEtForumPost->oMbqEtForum->forumId->oriValue;
        $topic_id    = $oMbqEtForumPost->topicId->oriValue;

        // Need to login to passworded forum first?
        if ($post_data['forum_password'] && !check_forum_password($forum_id))
            return getSystemString('LOGIN_FORUM');

        // Check permissions
        if ($user->data['is_bot'])  return getSystemString('NOT_AUTHORISED');

        // Is the user able to read within this forum?
        if (!$auth->acl_get('f_read', $forum_id))
        {
            if ($user->data['user_id'] != ANONYMOUS)
            {
                return getSystemString('USER_CANNOT_READ');
            }

            return getSystemString('LOGIN_EXPLAIN_POST');
        }

        // Permission to do the reply
        if (!$auth->acl_get('f_reply', $forum_id))
        {
            if ($user->data['user_id'] != ANONYMOUS)
            {
                return getSystemString('USER_CANNOT_REPLY');
            }

            return getSystemString('LOGIN_EXPLAIN_POST');
        }

        // Is the user able to post within this forum?
        if ($post_data['forum_type'] != FORUM_POST)
        {
            return getSystemString('USER_CANNOT_FORUM_POST');
        }

        // Forum/Topic locked?
        if (($post_data['forum_status'] == ITEM_LOCKED || (isset($post_data['topic_status']) && $post_data['topic_status'] == ITEM_LOCKED)) && !$auth->acl_get('m_edit', $forum_id))
        {
            return (($post_data['forum_status'] == ITEM_LOCKED) ? getSystemString('FORUM_LOCKED') : getSystemString('TOPIC_LOCKED'));
        }

        return true;
    }

    /**
     * judge can get quote post
     *
     * @param  Object  $oMbqEtForumPost
     * @return  Boolean
     */
    public function canAclGetQuotePost($oMbqEtForumPost) {
        global $user,$auth;

        //if (!$user->data['is_registered'])  return getSystemString('LOGIN_EXPLAIN_POST');
        $post_data = $oMbqEtForumPost->oMbqEtForum->mbqBind;
        // Use post_row values in favor of submitted ones...
        $forum_id    = $oMbqEtForumPost->oMbqEtForum->forumId->oriValue;
        $topic_id    = $oMbqEtForumPost->oMbqEtForumTopic->topicId->oriValue;

        // Need to login to passworded forum first?
        if ($post_data['forum_password'] && !check_forum_password($forum_id))
        {
             return getSystemString('LOGIN_FORUM');
        }

        // Is the user able to read within this forum?
        if (!$auth->acl_get('f_read', $forum_id))
        {
             return getSystemString('USER_CANNOT_READ');
        }

        if (!$auth->acl_get('f_reply', $forum_id))
        {
             return getSystemString('USER_CANNOT_REPLY');
        }

        // Is the user able to post within this forum?
        if ($post_data['forum_type'] != FORUM_POST)
        {
             return getSystemString('USER_CANNOT_FORUM_POST');
        }

        // Forum/Topic locked?
        if (($post_data['forum_status'] == ITEM_LOCKED || (isset($post_data['topic_status']) && $post_data['topic_status'] == ITEM_LOCKED)) && !$auth->acl_get('m_edit', $forum_id))
        {
            return (($post_data['forum_status'] == ITEM_LOCKED) ?  getSystemString('FORUM_LOCKED') :  getSystemString('TOPIC_LOCKED'));
        }
        return true;
    }

    /**
     * judge can search_post
     *
     * @return  Boolean
     */
    public function canAclSearchPost() {
        if (MbqMain::$oMbqConfig->getCfg('forum.guest_search')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.forum.guest_search.range.support')) {
            return true;
        } else {
            return MbqMain::hasLogin();
        }
    }

    /**
     * judge can get_raw_post
     *
     * @param  Object  $oMbqEtForumPost
     * @return  Boolean
     */
    public function canAclGetRawPost($oMbqEtForumPost) {
        global $auth, $user, $phpbb_root_path, $phpEx,$config;
        $forum_id = $oMbqEtForumPost->forumId->oriValue;
        $post_data = $oMbqEtForumPost->mbqBind['bind'];
        $topic_data = $oMbqEtForumPost->mbqBind['bindTopicData'];
        require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
        require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
        if (!$auth->acl_get('f_read', $forum_id))
        {
            return getSystemString('USER_CANNOT_READ');
        }

        // Permission to do the action asked?
        if (!($user->data['is_registered'] && $auth->acl_gets('f_edit', 'm_edit', $forum_id)))
        {
            return getSystemString('USER_CANNOT_EDIT');
        }

        // Forum/Topic locked?
        if (($topic_data['forum_status'] == ITEM_LOCKED || (isset($topic_data['topic_status']) && $topic_data['topic_status'] == ITEM_LOCKED)) && !$auth->acl_get('m_edit', $forum_id))
        {
            return (($topic_data['forum_status'] == ITEM_LOCKED) ? getSystemString('FORUM_LOCKED') : getSystemString('TOPIC_LOCKED'));
        }

        // Can we edit this post ... if we're a moderator with rights then always yes
        // else it depends on editing times, lock status and if we're the correct user
        if (!$auth->acl_get('m_edit', $forum_id))
        {
            if ($user->data['user_id'] != $post_data['user_id'])
            {
                return getSystemString('USER_CANNOT_EDIT');
            }

            if (!($post_data['post_time'] > time() - ($config['edit_time'] * 60) || !$config['edit_time']))
            {
                return getSystemString('CANNOT_EDIT_TIME');
            }

            if ($post_data['post_edit_locked'])
            {
                return getSystemString('CANNOT_EDIT_POST_LOCKED');
            }
        }

        return true;
    }

    /**
     * judge can save_raw_post
     *
     * @param  Object  $oMbqEtForumPost
     * @return  Boolean
     */
    public function canAclSaveRawPost($oMbqEtForumPost) {
        global $auth, $user,$config;
        $forum_id = $oMbqEtForumPost->forumId->oriValue;
        $post_data = $oMbqEtForumPost->mbqBind['bind'];
        $topic_data = $oMbqEtForumPost->mbqBind['bindTopicData'];

        // Need to login to passworded forum first?
        if ($topic_data['forum_password'] && !check_forum_password($forum_id))
        {
             return getSystemString('LOGIN_FORUM');
        }

        // Is the user able to read within this forum?
        if (!$auth->acl_get('f_read', $forum_id))
        {
             return getSystemString('USER_CANNOT_READ');
        }

        // Permission to do the action asked?
        if (!($user->data['is_registered'] && $auth->acl_gets('f_edit', 'm_edit', $forum_id)))
        {
             return getSystemString('USER_CANNOT_EDIT');
        }

        // Forum/Topic locked?
        if (($topic_data['forum_status'] == ITEM_LOCKED || (isset($topic_data['topic_status']) && $topic_data['topic_status'] == ITEM_LOCKED)) && !$auth->acl_get('m_edit', $forum_id))
        {
            return (($topic_data['forum_status'] == ITEM_LOCKED) ? getSystemString('FORUM_LOCKED') : getSystemString('TOPIC_LOCKED'));
        }

        // Can we edit this post ... if we're a moderator with rights then always yes
        // else it depends on editing times, lock status and if we're the correct user
        if (!$auth->acl_get('m_edit', $forum_id))
        {
            if ($user->data['user_id'] != $post_data['user_id'])
            {
                 return getSystemString('USER_CANNOT_EDIT');
            }

            if (!($post_data['post_time'] > time() - ($config['edit_time'] * 60) || !$config['edit_time']))
            {
                 return getSystemString('CANNOT_EDIT_TIME');
            }

            if ($post_data['post_edit_locked'])
            {
                 return getSystemString('CANNOT_EDIT_POST_LOCKED');
            }
        }
        return true;
    }

    /**
     * judge can get_user_reply_post
     *
     * @return  Boolean
     */
    public function canAclGetUserReplyPost() {
        return MbqMain::hasLogin();
    }

    /**
     * judge can report_post
     *
     * @param  Object  $oMbqEtForumPost
     * @return  Boolean
     */
    public function canAclReportPost($oMbqEtForumPost) {
       return $oMbqEtForumPost->canReport->oriValue == 1;
    }

    /**
     * judge can thank_post
     *
     * @param  Object  $oMbqEtForumPost
     * @return  Boolean
     */
    public function canAclThankPost($oMbqEtForumPost) {
		return $oMbqEtForumPost->canThank->oriValue == 1;
//        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NOT_ACHIEVE);
    }

    /**
     * judge can m_delete_post
     *
     * @return  Boolean
     */
    public function canAclMDeletePost($oMbqEtForumPost, $mode) {
        return $oMbqEtForumPost->canDelete->oriValue == 1;
    }

    /**
     * judge can m_undelete_post
     *
     * @return  Boolean
     */
    public function canAclMUndeletePost($oMbqEtForumPost) {
        return $oMbqEtForumPost->canApprove->oriValue == 1;
    }

    /**
     * judge can m_move_post
     *
     * @param  Object  $oMbqEtForumPost
     * @param  Mixed  $oMbqEtForum
     * @param  Mixed  $oMbqEtForumTopic
     * @return  Boolean
     */
    public function canAclMMovePost($oMbqEtForumPosts, $oMbqEtForum, $oMbqEtForumTopic) {
        foreach($oMbqEtForumPosts as $oMbqEtForumPost)
        {
            if($oMbqEtForumPost->canMove->oriValue == false)
            {
                return false;
            }
        }
        return true;
    }



    /**
     * judge can m_approve_post
     *
     * @param  Object  $oMbqEtForumPost
     * @param  Integer  $mode
     * @return  Boolean
     */
    public function canAclMApprovePost($oMbqEtForumPost, $mode) {
        global $auth;
        if($mode == 1)
        {
            return $oMbqEtForumPost->canApprove->oriValue == 1;
        }
        else if($mode == 2)
        {
            return $auth->acl_get('m_approve', $oMbqEtForumPost->forumId->oriValue) == 1;
        }
    }

    /**
     * judge can m_get_moderate_post
     *
     * @return  Boolean
     */
    public function canAclMGetModeratePost() {
        global $auth;
        return $auth->acl_get('m_approve', 0) == 1;
    }
    /**
     * judge can m_get_delete_post
     *
     * @return  Boolean
     */
    public function canAclMGetDeletePost() {
        global $auth;
        return $auth->acl_get('m_delete', 0) == 1;
    }   /**
     * judge can m_get_report_post
     *
     * @return  Boolean
     */
    public function canAclMGetReportPost() {
        global $auth;
        return $auth->acl_get('m_report', 0) == 1;
    }

    /**
     * judege can close report
     *
     * @return Boolean
     */
    public function canAclMCloseReport($forum_id=false) {
    	global $auth;
        return $auth->acl_getf_global('m_') == 1;
    }
}
