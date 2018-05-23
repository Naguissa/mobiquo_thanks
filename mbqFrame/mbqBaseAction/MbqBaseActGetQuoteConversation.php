<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_quote_conversation action
 */
Abstract Class MbqBaseActGetQuoteConversation extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->convId = $this->getInputParam('conversationId');
            $in->msgId = $this->getInputParam('messageId');
        }
        else
        {
            $in->convId = $this->getInputParam(0);
            $in->msgId = $this->getInputParam(1);
        }
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (MbqMain::$oMbqConfig->moduleIsEnable('pc') && (MbqMain::$oMbqConfig->getCfg('pc.conversation')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.pc.conversation.range.support'))) {
            $oMbqRdEtPc = MbqMain::$oClk->newObj('MbqRdEtPc');
            if ($oMbqEtPc = $oMbqRdEtPc->initOMbqEtPc($in->convId, array('case' => 'byConvId'))) {
                $oMbqRdEtPcMsg = MbqMain::$oClk->newObj('MbqRdEtPcMsg');
                if ($oMbqEtPcMsg = $oMbqRdEtPcMsg->initOMbqEtPcMsg($oMbqEtPc, array('case' => 'byPcMsgId', 'pcMsgId' => $in->msgId))) {
                    $oMbqAclEtPcMsg = MbqMain::$oClk->newObj('MbqAclEtPcMsg');
                    $aclResult = $oMbqAclEtPcMsg->canAclGetQuoteConversation($oMbqEtPcMsg, $oMbqEtPc);
                    if ($aclResult) {
                        $this->data['text_body'] = (string) $oMbqRdEtPcMsg->getQuoteConversation($oMbqEtPcMsg);
                    } else {
                        MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
                    }
                } else {
                    MbqError::alert('', "Need valid conversation message id!", '', MBQ_ERR_APP);
                }
            } else {
                MbqError::alert('', "Need valid conversation id!", '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Not support module private conversation!", '', MBQ_ERR_NOT_SUPPORT);
        }
    }
}