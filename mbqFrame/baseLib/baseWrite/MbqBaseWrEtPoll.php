<?php

defined('MBQ_IN_IT') or exit;

/**
 * poll write class
 */
Abstract Class MbqBaseWrEtPoll extends MbqBaseWr {
    
    public function __construct() {
    }
    
    /**
     * vote
     */
    public function vote($oMbqEtPoll) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * edit_poll
     */
    public function editPoll($oMbqEtPoll) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
  
}
