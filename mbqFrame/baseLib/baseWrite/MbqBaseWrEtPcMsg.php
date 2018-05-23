<?php

defined('MBQ_IN_IT') or exit;

/**
 * private conversation message write class
 */
Abstract Class MbqBaseWrEtPcMsg extends MbqBaseWr {
    
    public function __construct() {
    }
    
    /**
     * add private conversation message
     */
    public function addMbqEtPcMsg($oMbqEtPcMsg, $oMbqEtPc) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
  
}
