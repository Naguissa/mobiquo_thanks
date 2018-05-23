<?php

defined('MBQ_IN_IT') or exit;

Abstract Class MbqBaseWrCommon extends MbqBaseWr {
    
    public function __construct() {
    }
    
    public function setApiKey($apiKey) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    public function setPushSlug($apiKey) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    public function SetSmartbannerInfo($smartbannerInfo) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
}
