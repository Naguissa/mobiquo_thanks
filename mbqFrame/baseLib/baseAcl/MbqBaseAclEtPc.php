<?php

defined('MBQ_IN_IT') or exit;

/**
 * private conversation acl class
 */
Abstract Class MbqBaseAclEtPc extends MbqBaseAcl {
    
    public function __construct() {
    }
    
    /**
     * judge can get_inbox_stat
     *
     * @return  Boolean
     */
    public function canAclGetInboxStat() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can get_conversations
     *
     * @return  Boolean
     */
    public function canAclGetConversations() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can get_conversation
     *
     * @return  Boolean
     */
    public function canAclGetConversation($oMbqEtPc) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can new_conversation
     *
     * @return  Boolean
     */
    public function canAclNewConversation($oMbqEtPc) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can invite_participant
     *
     * @return  Boolean
     */
    public function canAclInviteParticipant($oMbqEtPcInviteParticipant) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can delete_conversation
     *
     * @return  Boolean
     */
    public function canAclDeleteConversation($oMbqEtPc, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
  
}