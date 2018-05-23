<?php

defined('MBQ_IN_IT') or exit;

/**
 * forum write class
 */
Abstract Class MbqBaseWrEtForum extends MbqBaseWr {

    public function __construct() {
    }

    /**
     * subscribe forum
     */
    public function subscribeForum($oMbqEtForum, $receiveEmail) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * unsubscribe forum
     */
    public function unsubscribeForum($oMbqEtForum) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    public function markForumRead($oMbqEtForum){
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

}
