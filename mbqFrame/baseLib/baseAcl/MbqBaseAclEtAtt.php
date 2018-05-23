<?php

defined('MBQ_IN_IT') or exit;

/**
 * attachment acl class
 */
Abstract Class MbqBaseAclEtAtt extends MbqBaseAcl {
    
    public function __construct() {
    }
    
    /**
     * judge can upload attachment
     *
     * @return  Boolean
     */
    public function canAclUploadAttach($oMbqEtForumOrConvPm, $groupId, $type) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can remove attachment
     *
     * @return  Boolean
     */
    public function canAclRemoveAttachment($oMbqEtAtt, $oMbqEtForum) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
  
}