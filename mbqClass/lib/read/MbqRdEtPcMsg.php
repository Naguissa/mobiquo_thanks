<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtPcMsg');

/**
 * private conversation message read class
 */
Class MbqRdEtPcMsg extends MbqBaseRdEtPcMsg {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtPcMsg, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
  
}
