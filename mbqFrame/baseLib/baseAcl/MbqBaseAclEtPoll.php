<?php

defined('MBQ_IN_IT') or exit;

/**
 * social acl class
 */
Abstract Class MbqBaseAclEtPoll extends MbqBaseAcl {
    
    public function __construct() {
    }
    
    /**
     * judge getvoteslist
     *
     * @return  Boolean
     */
    public function canAclGetVotesList() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge vote
     *
     * @return  Boolean
     */
    public function canAclVote($oMbqEtPoll) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge editpoll
     *
     * @return  Boolean
     */
    public function canAclEditPoll($oMbqEtPoll) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
}
