<?php

defined('MBQ_IN_IT') or exit;

/**
 * private conversation message acl class
 */
Abstract Class MbqBaseAclEtPcMsg extends MbqBaseAcl {
    
    public function __construct() {
    }
    
    /**
     * judge can reply_conversation
     *
     * @return  Boolean
     */
    public function canAclReplyConversation($oMbqEtPcMsg, $oMbqEtPc) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can get_quote_conversation
     *
     * @return  Boolean
     */
    public function canAclGetQuoteConversation($oMbqEtPcMsg, $oMbqEtPc) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
}