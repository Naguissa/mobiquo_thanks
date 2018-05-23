<?php

defined('MBQ_IN_IT') or exit;

/**
 * private message acl class
 */
Abstract Class MbqBaseAclEtPm extends MbqBaseAcl {
    
    public function __construct() {
    }
    
    /**
     * judge can report_pm
     *
     * @return  Boolean
     */
    public function canAclReportPm($oMbqEtPm) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can create_message
     *
     * @return  Boolean
     */
    public function canAclCreateMessage() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can get_box_info
     *
     * @return  Boolean
     */
    public function canAclGetBoxInfo() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can get_box
     *
     * @return  Boolean
     */
    public function canAclGetBox() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can get_message
     *
     * @return  Boolean
     */
    public function canAclGetMessage() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can get_quote_pm
     *
     * @return  Boolean
     */
    public function canAclGetQuotePm() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can delete_message
     *
     * @return  Boolean
     */
    public function canAclDeleteMessage() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can mark_pm_unread
     *
     * @return  Boolean
     */
    public function canAclMarkPmUnread($oMbqEtPm) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * judge can mark_pm_read
     *
     * @return  Boolean
     */
    public function canAclMarkPmRead($oMbqEtPm) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
     /**
     * judge can mark_pm_read
     *
     * @return  Boolean
     */
    public function canAclMarkAllPmRead() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
}