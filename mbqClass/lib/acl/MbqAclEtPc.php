<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseAclEtPc');

/**
 * private conversation acl class
 */
Class MbqAclEtPc extends MbqBaseAclEtPc {
    
    public function __construct() {
    }
    /**
     * judge can get_inbox_stat
     *
     * @return  Boolean
     */
    public function canAclGetInboxStat() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE);
    }
    
    /**
     * judge can get_conversations
     *
     * @return  Boolean
     */
    public function canAclGetConversations() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE);
    }
    
    /**
     * judge can get_conversation
     *
     * @return  Boolean
     */
    public function canAclGetConversation() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE);
    }
    
    /**
     * judge can new_conversation
     *
     * @return  Boolean
     */
    public function canAclNewConversation() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE);
    }
    
    /**
     * judge can invite_participant
     *
     * @return  Boolean
     */
    public function canAclInviteParticipant() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE);
    }
    
    /**
     * judge can delete_conversation
     *
     * @return  Boolean
     */
    public function canAclDeleteConversation() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE);
    }
}
