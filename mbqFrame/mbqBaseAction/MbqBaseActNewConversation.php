<?php

defined('MBQ_IN_IT') or exit;

/**
 * new_conversation action
 */
Abstract Class MbqBaseActNewConversation extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->usernames = (array) $this->getInputParam('username');
            $in->subject = $this->getInputParam('subject');
            $in->body = $this->getInputParam('body');
            $in->attachmentIds = $this->getInputParam('attachmentIds');
            $in->groupId = $this->getInputParam('groupId');
        }
        else
        {
            $in->usernames = (array) $this->getInputParam(0);
            $in->subject = $this->getInputParam(1);
            $in->body = $this->getInputParam(2);
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
        $oMbqEtPc = MbqMain::$oClk->newObj('MbqEtPc');
        $oMbqEtPc->userNames->setOriValue($in->usernames);
        $oMbqEtPc->convTitle->setOriValue($in->subject);
        $oMbqEmoji = MbqMain::$oClk->newObj('MbqEmoji');
        $body = $oMbqEmoji->DoReplace($in->body);
        $oMbqEtPc->convContent->setOriValue($body);
        if (isset($in->attachmentIds)) $oMbqEtPc->attachmentIdArray->setOriValue($in->attachmentIds);
        if (isset($in->groupId)) $oMbqEtPc->groupId->setOriValue($in->groupId);
        
        $oMbqAclEtPc = MbqMain::$oClk->newObj('MbqAclEtPc');
        $aclResult = $oMbqAclEtPc->canAclNewConversation($oMbqEtPc);
        if ($aclResult === true) {    //acl judge
            $oMbqWrEtPc = MbqMain::$oClk->newObj('MbqWrEtPc');
            $result = $oMbqWrEtPc->addMbqEtPc($oMbqEtPc);
            if(is_a($result,'MbqEtPc'))
            {
                $this->data['result'] = true;
                $this->data['conv_id'] = (string) $result->convId->oriValue;
                $oMbqRdEtPcMsg = MbqMain::$oClk->newObj('MbqRdEtPcMsg');
                $oMbqEtPcMsg = $oMbqRdEtPcMsg->initOMbqEtPcMsg($result, array('case' => 'byPcMsgId', 'pcMsgId' => $result->firstMsgId->oriValue));
                $oTapatalkPush = new TapatalkPush();
                $oTapatalkPush->callMethod('doInternalPushNewConversation', array(
                    'oMbqEtPc' => $result,
                    'oMbqEtPcMsg' => $oMbqEtPcMsg,
                ));
            }
            else
            {
                $this->data['result'] = false;
                $this->data['result_text'] = $result;
            }
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
    
}