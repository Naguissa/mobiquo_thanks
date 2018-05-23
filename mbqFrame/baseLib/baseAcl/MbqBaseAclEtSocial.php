<?php

defined('MBQ_IN_IT') or exit;

/**
 * social acl class
 */
Abstract Class MbqBaseAclEtSocial extends MbqBaseAcl {
    
    public function __construct() {
    }
    
    /**
     * judge getalert
     *
     * @return  Boolean
     */
    public function canAclGetAlert() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
}
