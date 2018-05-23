<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseAclEtForumTopic');

/**
 * forum topic acl class
 */
Class MbqAclEtForumTopic extends MbqBaseAclEtForumTopic {

    public function __construct() {
    }

    /**
     * judge can get topic from the forum
     *
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclGetTopic($oMbqEtForum) {
        global $db, $auth, $user, $config;
        return $auth->acl_get('f_read', $oMbqEtForum->forumId->oriValue) == 1;

    }
    /**
     * judge can get topic from the forum
     *
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclGetTopicByIds() {
        return true;
    }

    /**
     * judge can get thread
     *
     * @param  Object  $oMbqEtForumTopic
     * @return  Boolean
     */
    public function canAclGetThread($oMbqEtForumTopic) {
        global $db, $auth, $user, $config;
        return $auth->acl_get('f_read', $oMbqEtForumTopic->forumId->oriValue) == 1;
    }

    /**
     * judge can new topic
     *
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclNewTopic($oMbqEtForum) {
        global $db, $auth, $user, $config, $phpbb_root_path, $phpEx, $mobiquo_config;
        $forum_id = $oMbqEtForum->forumId->oriValue;
        $post_data = $oMbqEtForum->mbqBind;

        $user->setup('posting');
        if (!$user->data['is_registered'])return getSystemString('LOGIN_EXPLAIN_POST');

        if ($post_data['forum_password'] && !check_forum_password($forum_id))
            return getSystemString('LOGIN_FORUM');

        // Check permissions
        if ($user->data['is_bot'])  return getSystemString('NOT_AUTHORISED');

        // Is the user able to read and post within this forum?
        if (!$auth->acl_get('f_read', $forum_id))
        {
            if ($user->data['user_id'] != ANONYMOUS)
            {
                 return getSystemString('USER_CANNOT_READ');
            }

             return getSystemString('LOGIN_EXPLAIN_POST');
        }

        if (!$auth->acl_get('f_post', $forum_id))
        {
            if ($user->data['user_id'] != ANONYMOUS)
            {
                 return getSystemString('USER_CANNOT_POST');
            }

             return getSystemString('LOGIN_EXPLAIN_POST');
        }

        // Is the user able to post within this forum?
        if ($post_data['forum_type'] != FORUM_POST)
        {
             return getSystemString('USER_CANNOT_FORUM_POST');
        }

        // Forum/Topic locked?
        if ($post_data['forum_status'] == ITEM_LOCKED && !$auth->acl_get('m_edit', $forum_id))
        {
            return getSystemString('FORUM_LOCKED');
        }
        return  true;
    }

    /**
     * judge can guest new topic
     *
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclGuestNewTopic($oMbqEtForum) {
        global $db, $auth, $user, $config, $phpbb_root_path, $phpEx, $mobiquo_config;
        $forum_id = $oMbqEtForum->forumId->oriValue;
        $post_data = $oMbqEtForum->mbqBind;

        $user->setup('posting');

        if ($post_data['forum_password'] && !check_forum_password($forum_id))
            return getSystemString('LOGIN_FORUM');

        // Check permissions
        if ($user->data['is_bot'])  return getSystemString('NOT_AUTHORISED');
        // Is the user able to read and post within this forum?
        if (!$auth->acl_get('f_read', $forum_id))
        {
            if ($user->data['user_id'] != ANONYMOUS)
            {
                return getSystemString('USER_CANNOT_READ');
            }

            return getSystemString('LOGIN_EXPLAIN_POST');
        }

        if (!$auth->acl_get('f_post', $forum_id))
        {
            if ($user->data['user_id'] != ANONYMOUS)
            {
                return getSystemString('USER_CANNOT_POST');
            }

            return getSystemString('LOGIN_EXPLAIN_POST');
        }

        // Is the user able to post within this forum?
        if ($post_data['forum_type'] != FORUM_POST)
        {
            return getSystemString('USER_CANNOT_FORUM_POST');
        }

        // Forum/Topic locked?
        if ($post_data['forum_status'] == ITEM_LOCKED && !$auth->acl_get('m_edit', $forum_id))
        {
            return getSystemString('FORUM_LOCKED');
        }
        return  true;
    }

    /**
     * judge can get subscribed topic
     *
     * @return  Boolean
     */
    public function canAclGetSubscribedTopic() {
        return MbqMain::hasLogin();
    }

    /**
     * judge can mark all my unread topics as read
     *
     * @return  Boolean
     */
    public function canAclMarkTopicRead($topicIds) {
        return MbqMain::hasLogin();
    }


    /**
     * judge can get_unread_topic
     *
     * @return  Boolean
     */
    public function canAclGetUnreadTopic() {
        return MbqMain::hasLogin();
    }

    /**
     * judge can get_participated_topic
     *
     * @return  Boolean
     */
    public function canAclGetParticipatedTopic() {
        return MbqMain::hasLogin();
    }

    /**
     * judge can get_latest_topic
     *
     * @return  Boolean
     */
    public function canAclGetLatestTopic() {
        return true;
    }

    /**
     * judge can search_topic
     *
     * @return  Boolean
     */
    public function canAclSearchTopic() {
        return true;
    }

    /**
     * judge can subscribe_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @return  Boolean
     */
    public function canAclSubscribeTopic($oMbqEtForumTopic, $receiveEmail) {
        return MbqMain::hasLogin();
    }

    /**
     * judge can unsubscribe_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @return  Boolean
     */
    public function canAclUnsubscribeTopic($oMbqEtForumTopic) {
        return MbqMain::hasLogin();
    }

    /**
     * judge can get_user_topic
     *
     * @return  Boolean
     */
    public function canAclGetUserTopic() {
        return MbqMain::hasLogin();
    }

    /**
     * judge can m_stick_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Integer  $mode
     * @return  Boolean
     */
    public function canAclMStickTopic($oMbqEtForumTopic, $mode) {
        return $oMbqEtForumTopic->canStick->oriValue == 1;
    }

    /**
     * judge can m_close_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Integer  $mode
     * @return  Boolean
     */
    public function canAclMCloseTopic($oMbqEtForumTopic, $mode) {
        return $oMbqEtForumTopic->canClose->oriValue == 1;
    }

    /**
     * judge can m_delete_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Integer  $mode
     * @return  Boolean
     */
    public function canAclMDeleteTopic($oMbqEtForumTopic, $mode) {
        return $oMbqEtForumTopic->canDelete->oriValue && !$oMbqEtForumTopic->isDeleted->oriValue;
    }

    /**
     * judge can m_undelete_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @return  Boolean
     */
    public function canAclMUndeleteTopic($oMbqEtForumTopic) {
        return $oMbqEtForumTopic->canDelete->oriValue && $oMbqEtForumTopic->isDeleted->oriValue;
    }

    /**
     * judge can m_move_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclMMoveTopic($oMbqEtForumTopic, $oMbqEtForum) {
        return $oMbqEtForumTopic->canMove->oriValue == 1;
    }
    /**
     * judge can m_move_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclMMergeTopic($oMbqEtForumTopicFrom, $oMbqEtForumTopicTo) {
        return $oMbqEtForumTopicFrom->canMove->oriValue && $oMbqEtForumTopicTo->canMove->oriValue;
    }
    /**
     * judge can m_rename_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @return  Boolean
     */
    public function canAclMRenameTopic($oMbqEtForumTopic) {
        return $oMbqEtForumTopic->canRename->oriValue == 1;
    }

    /**
     * judge can m_approve_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Integer  $mode
     * @return  Boolean
     */
    public function canAclMApproveTopic($oMbqEtForumTopic, $mode) {
        global $auth;
        if($mode == 1)
        {
            return $oMbqEtForumTopic->canApprove->oriValue == 1;
        }
        else if($mode == 2)
        {
            return $auth->acl_get('m_approve', $oMbqEtForumTopic->forumId->oriValue) == 1;
        }
    }

    /**
     * judge can m_get_moderate_topic
     *
     * @return  Boolean
     */
    public function canAclMGetModerateTopic() {
        global $auth;
        return $auth->acl_get('m_approve', 0) == 1;
    }
    /**
     * judge can m_get_delete_topic
     *
     * @return  Boolean
     */
    public function canAclMGetDeleteTopic() {
        global $auth;
        return $auth->acl_get('m_delete', 0) == 1;
    }
}
