<?php

defined('MBQ_IN_IT') or exit;

/**
 * private conversation write class
 */
Abstract Class MbqBaseWrEtPc extends MbqBaseWr {
    
    public function __construct() {
    }
    
    /**
     * mark private conversation read
     */
    public function markPcRead($oMbqEtPc) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * mark all private conversations read
     */
    public function markAllPcRead(){
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * add private conversation
     */
    public function addMbqEtPc($oMbqEtPc) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * invite participant
     */
    public function inviteParticipant($oMbqEtPcInviteParticipant) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * delete conversation
     */
    public function deleteConversation($oMbqEtPc, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
  
}
