<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtThank');

/**
 * thank read class
 */
Class MbqRdEtThank extends MbqBaseRdEtThank {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtThank, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
  
}
