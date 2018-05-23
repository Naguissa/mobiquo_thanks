<?php

defined('MBQ_IN_IT') or exit;

/**
 * private message write class
 */
Abstract Class MbqBaseWrEtPm extends MbqBaseWr {
    
    public function __construct() {
    }
    
    /**
     * add private message
     */
    public function addMbqEtPm($oMbqEtPm) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    public function processToSave($message){
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    public function deleteMbqEtPmMessage($oMbqEtPm) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    public function markPmUnread($oMbqEtPm){
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    public function markPmRead($oMbqEtPm){
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
  
}
