<?php

defined('MBQ_IN_IT') or exit;

/**
 * forum topic acl class
 */
Abstract Class MbqBaseAclEtForumTopic extends MbqBaseAcl {

    public function __construct() {
    }

    /**
     * judge can get topic from the forum
     *
     * @return  Boolean
     */
    public function canAclGetTopic($oMbqEtForum) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can get topics by ids
     *
     * @return  Boolean
     */
    public function canAclGetTopicByIds() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can get thread
     *
     * @return  Boolean
     */
    public function canAclGetThread($oMbqEtForumTopic) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can new topic
     *
     * @return  Boolean
     */
    public function canAclNewTopic($oMbqEtForum) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can get subscribed topic
     *
     * @return  Boolean
     */
    public function canAclGetSubscribedTopic() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can mark all my unread topics as read
     *
     * @return  Boolean
     */
    public function canAclMarkTopicRead($oMbqEtForumTopic) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can get_unread_topic
     *
     * @return  Boolean
     */
    public function canAclGetUnreadTopic() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can get_participated_topic
     *
     * @return  Boolean
     */
    public function canAclGetParticipatedTopic() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can get_latest_topic
     *
     * @return  Boolean
     */
    public function canAclGetLatestTopic() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can search_topic
     *
     * @return  Boolean
     */
    public function canAclSearchTopic() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can subscribe_topic
     *
     * @return  Boolean
     */
    public function canAclSubscribeTopic($oMbqEtForumTopic, $receiveEmail) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can unsubscribe_topic
     *
     * @return  Boolean
     */
    public function canAclUnsubscribeTopic($oMbqEtForumTopic) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can get_user_topic
     *
     * @return  Boolean
     */
    public function canAclGetUserTopic() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can m_stick_topic
     *
     * @return  Boolean
     */
    public function canAclMStickTopic($oMbqEtForumTopic, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can m_close_topic
     *
     * @return  Boolean
     */
    public function canAclMCloseTopic($oMbqEtForumTopic, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can m_delete_topic
     *
     * @return  Boolean
     */
    public function canAclMDeleteTopic($oMbqEtForumTopic, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can m_undelete_topic
     *
     * @return  Boolean
     */
    public function canAclMUndeleteTopic($oMbqEtForumTopic) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can m_move_topic
     *
     * @return  Boolean
     */
    public function canAclMMoveTopic($oMbqEtForumTopic, $oMbqEtForum) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can m_rename_topic
     *
     * @return  Boolean
     */
    public function canAclMRenameTopic($oMbqEtForumTopic) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can m_approve_topic
     *
     * @return  Boolean
     */
    public function canAclMApproveTopic($oMbqEtForumTopic, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can m_merge_topic
     *
     * @return  Boolean
     */
    public function canAclMMergeTopic($oMbqEtForumTopicFrom, $oMbqEtForumTopicTo) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can m_get_moderate_topic
     *
     * @return  Boolean
     */
    public function canAclMGetModerateTopic() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * judge can m_get_delete_topic
     *
     * @return  Boolean
     */
    public function canAclMGetDeleteTopic() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
}