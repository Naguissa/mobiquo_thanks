<?php

defined('MBQ_IN_IT') or exit;

/**
 * invite_participant action
 */
Abstract Class MbqBaseActInviteParticipant extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->usernames = $this->getInputParam('username');
            $in->conversationId = $this->getInputParam('conversationId');
            $in->reason = $this->getInputParam('reason');
        }
        else
        {
            $in->usernames = $this->getInputParam(0);
            $in->conversationId = $this->getInputParam(1);
            $in->reason = $this->getInputParam(2);
        }
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (MbqMain::$oMbqConfig->moduleIsEnable('pc') && (MbqMain::$oMbqConfig->getCfg('pc.conversation')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.pc.conversation.range.support'))) {
        } else {
            MbqError::alert('', "Not support module private conversation!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqEtPcInviteParticipant = MbqMain::$oClk->newObj('MbqEtPcInviteParticipant');
        $oMbqEtPcInviteParticipant->userNames->setOriValue($in->usernames);
        $oMbqEtPcInviteParticipant->convId->setOriValue($in->conversationId);
        $oMbqEtPcInviteParticipant->inviteReasonText->setOriValue($in->reason);
        $oMbqRdEtPc = MbqMain::$oClk->newObj('MbqRdEtPc');
        if ($oMbqEtPcInviteParticipant->oMbqEtPc = $oMbqRdEtPc->initOMbqEtPc($oMbqEtPcInviteParticipant->convId->oriValue, array('case' => 'byConvId'))) {
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            foreach ($oMbqEtPcInviteParticipant->userNames->oriValue as $userName) {
                if ($oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($userName, array('case' => 'byLoginName'))) {
                    $oMbqEtPcInviteParticipant->objsMbqEtUser[] = $oMbqEtUser;
                }
            }
            $oMbqAclEtPc = MbqMain::$oClk->newObj('MbqAclEtPc');
            $aclResult = $oMbqAclEtPc->canAclInviteParticipant($oMbqEtPcInviteParticipant);
            if ($aclResult === true) {    //acl judge
                $oMbqWrEtPc = MbqMain::$oClk->newObj('MbqWrEtPc');
                $oMbqWrEtPc->inviteParticipant($oMbqEtPcInviteParticipant);
                $this->data['result'] = true;
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            } 
        } else {
            MbqError::alert('', "Need valid conversation id!", '', MBQ_ERR_APP);
        }
    }
  
}