<?php

defined('MBQ_IN_IT') or exit;

/**
 * delete_conversation action
 */
Abstract Class MbqBaseActDeleteConversation extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->conversationId = $this->getInputParam('conversationId');
            $in->mode = (int) $this->getInputParam('mode');
        }
        else
        {
            $in->conversationId = $this->getInputParam(0);
            $in->mode = (int) $this->getInputParam(1);
        }
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (MbqMain::$oMbqConfig->moduleIsEnable('pc') && (MbqMain::$oMbqConfig->getCfg('pc.conversation')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.pc.conversation.range.support'))) {
            $oMbqRdEtPc = MbqMain::$oClk->newObj('MbqRdEtPc');
            if ($oMbqEtPc = $oMbqRdEtPc->initOMbqEtPc($in->conversationId, array('case' => 'byConvId'))) {
                $oMbqAclEtPc = MbqMain::$oClk->newObj('MbqAclEtPc');
                $aclResult = $oMbqAclEtPc->canAclDeleteConversation($oMbqEtPc, $in->mode);
                if ($aclResult === true) {    //acl judge
                    $oMbqWrEtPc = MbqMain::$oClk->newObj('MbqWrEtPc');
                    $oMbqWrEtPc->deleteConversation($oMbqEtPc, $in->mode);
                    $this->data['result'] = true;
                } else {
                    MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
                } 
            } else {
                MbqError::alert('', 'Private conversation not found', '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Not support module private conversation!", '', MBQ_ERR_NOT_SUPPORT);
        }
    }
  
}