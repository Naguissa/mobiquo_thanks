<?php

defined('MBQ_IN_IT') or exit;

/**
 * forum acl class
 */
Abstract Class MbqBaseAclEtForum extends MbqBaseAcl {

    public function __construct() {
    }

    /**
     * judge can get subscribed forum
     *
     * @return  Boolean
     */
    public function canAclGetSubscribedForum() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can subscribe forum
     *
     * @return  Boolean
     */
    public function canAclSubscribeForum($oMbqEtForum, $receiveEmail) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can unsubscribe forum
     *
     * @return  Boolean
     */
    public function canAclUnsubscribeForum($oMbqEtForum) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * judge can mark all my unread topics as read
     *
     * @return  Boolean
     */
    public function canAclMarkAllAsRead($oMbqEtForum) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
}