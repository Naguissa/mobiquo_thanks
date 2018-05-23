<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseAclEtPoll');

/**
 * poll acl class
 */
Class MbqAclEtPoll extends MbqBaseAclEtPoll {
    
    public function __construct() {
    }
    
    /**
     * judge getvoteslist
     *
     * @return  Boolean
     */
    public function canAclGetVotesList() {
        global $user;
        if (!$user->data['is_registered']) return false;
        return true;
    }

    /**
     * judge vote
     *
     * @return  Boolean
     */
    public function canAclVote($oMbqEtPoll) {
        global $auth, $user, $config, $db;

        if (!$user->data['is_registered']) return false;

        $oMbqEtForumTopic = $oMbqEtPoll->oMbqEtForumTopic;
        $topic_id = $oMbqEtForumTopic->topicId->oriValue;
        $forum_id = $oMbqEtForumTopic->forumId->oriValue;
        $topic_data = $oMbqEtForumTopic->mbqBind['TopicRow'];
        $cur_voted_id = $oMbqEtForumTopic->oMbqEtPoll->myVotes->oriValue;

        $s_can_vote = ($auth->acl_get('f_vote', $forum_id) &&
            (($topic_data['poll_length'] != 0 && $topic_data['poll_start'] + $topic_data['poll_length'] > time()) || $topic_data['poll_length'] == 0) &&
            $topic_data['topic_status'] != ITEM_LOCKED &&
            $topic_data['forum_status'] != ITEM_LOCKED &&
            (!sizeof($cur_voted_id) ||
            ($auth->acl_get('f_votechg', $forum_id) && $topic_data['poll_vote_change']))) ? true : false;

        return $s_can_vote;
    }

    /**
     * judge editpoll
     *
     * @return  Boolean
     */
    public function canAclEditPoll($oMbqEtPoll) {
        global $auth, $user,$config;

        $oMbqEtForumTopic = $oMbqEtPoll->oMbqEtForumTopic;
        $post_id = $oMbqEtForumTopic->firstPostId->oriValue;
        $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
        $oMbqEtForumPost = $oMbqRdEtForumPost->initOMbqEtForumPost($post_id, array('case' => 'byPostId'));

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
}
