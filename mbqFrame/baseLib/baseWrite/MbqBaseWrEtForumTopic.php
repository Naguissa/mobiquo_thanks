<?php

defined('MBQ_IN_IT') or exit;

/**
 * forum topic write class
 */
Class MbqBaseWrEtForumTopic extends MbqBaseWr {

    public function __construct() {
    }

    /**
     * add forum topic view num
     */
    public function addForumTopicViewNum($oMbqEtForumTopic) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * mark forum topic read
     */
    public function markForumTopicRead($oMbqEtForumTopic) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * reset forum topic subscription
     */
    public function resetForumTopicSubscription($oMbqEtForumTopic) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * add forum topic
     */
    public function addMbqEtForumTopic($oMbqEtForumTopic) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * subscribe topic
     */
    public function subscribeTopic($oMbqEtForumTopic, $receiveEmail) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * unsubscribe topic
     */
    public function unsubscribeTopic($oMbqEtForumTopic) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * m_stick_topic
     */
    public function mStickTopic($oMbqEtForumTopic, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * m_close_topic
     */
    public function mCloseTopic($oMbqEtForumTopic, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * m_delete_topic
     */
    public function mDeleteTopic($oMbqEtForumTopic, $mode, $reason) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * m_undelete_topic
     */
    public function mUndeleteTopic($oMbqEtForumTopic) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * m_move_topic
     */
    public function mMoveTopic($oMbqEtForumTopic, $oMbqEtForum, $redirect) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * m_rename_topic
     */
    public function mRenameTopic($oMbqEtForumTopic, $title) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * m_approve_topic
     */
    public function mApproveTopic($oMbqEtForumTopic, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * m_merge_topic
     */
    public function mMergeTopic($oMbqEtForumTopicFrom, $oMbqEtForumTopicTo ,$redirect) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
}
