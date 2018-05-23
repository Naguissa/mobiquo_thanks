<?php

defined('MBQ_IN_IT') or exit;

/**
 * attachment write class
 */
Abstract Class MbqBaseWrEtAtt extends MbqBaseWr {
    
    public function __construct() {
    }
    
    /**
     * upload attachment
     */
    public function uploadAttachment($oMbqEtForumOrConvPm, $groupId, $type) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * delete attachment
     */
    public function deleteAttachment($oMbqEtAtt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
  
}
