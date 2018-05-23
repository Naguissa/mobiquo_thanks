<?php

defined('MBQ_IN_IT') or exit;

/**
 * reply_conversation action
 */
Abstract Class MbqBaseActReplyConversation extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->conversationId = $this->getInputParam('conversationId');
            $in->body = $this->getInputParam('body');
            $in->subject = $this->getInputParam('subject');
            $in->attachmentIds = $this->getInputParam('attachmentIds');
            $in->groupId = $this->getInputParam('groupId');
        }
        else
        {
            $in->conversationId = $this->getInputParam(0);
            $in->body = $this->getInputParam(1);
            $in->subject = $this->getInputParam(2);
            $in->attachmentIds = $this->getInputParam(3);
            $in->groupId = $this->getInputParam(4);
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
        $oMbqEtPcMsg = MbqMain::$oClk->newObj('MbqEtPcMsg');
       
        $oMbqEtPcMsg->convId->setOriValue($in->conversationId);
        $oMbqEmoji = MbqMain::$oClk->newObj('MbqEmoji');
        $body = $oMbqEmoji->DoReplace($in->body);
        $oMbqEtPcMsg->msgContent->setOriValue($body);
        $oMbqEtPcMsg->msgTitle->setOriValue($in->subject);
        if (isset($in->attachmentIds)) $oMbqEtPcMsg->attachmentIdArray->setOriValue($in->attachmentIds);
        if (isset($in->groupId)) $oMbqEtPcMsg->groupId->setOriValue($in->groupId);
        
        $oMbqRdEtPc = MbqMain::$oClk->newObj('MbqRdEtPc');
        if ($oMbqEtPc = $oMbqRdEtPc->initOMbqEtPc($oMbqEtPcMsg->convId->oriValue, array('case' => 'byConvId'))) {
            $oMbqAclEtPcMsg = MbqMain::$oClk->newObj('MbqAclEtPcMsg');
            $aclResult = $oMbqAclEtPcMsg->canAclReplyConversation($oMbqEtPcMsg, $oMbqEtPc);
            if ($aclResult === true) {
                $oMbqWrEtPcMsg = MbqMain::$oClk->newObj('MbqWrEtPcMsg');
                $oMbqWrEtPcMsg->addMbqEtPcMsg($oMbqEtPcMsg, $oMbqEtPc);
                $this->data['result'] = true;
                $this->data['msg_id'] = (string) $oMbqEtPcMsg->msgId->oriValue;
                $oTapatalkPush = new TapatalkPush();
                $oTapatalkPush->callMethod('doInternalPushReplyConversation', array(
                    'oMbqEtPc' => $oMbqEtPc,
                    'oMbqEtPcMsg' => $oMbqEtPcMsg
                ));
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid conversation id!", '', MBQ_ERR_APP);
        }
    }
    
}