<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtLike');

/**
 * Like read class
 */
Class MbqRdEtLike extends MbqBaseRdEtLike {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtLike, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
  
}
