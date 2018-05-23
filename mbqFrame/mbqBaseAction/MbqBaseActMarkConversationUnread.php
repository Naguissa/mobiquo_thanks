<?php

defined('MBQ_IN_IT') or exit;

Abstract Class MbqBaseActMarkConversationUnread extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->convId = $this->getInputParam('conversationId');
        }
        else
        {
            $in->convId = $this->getInputParam(0);
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
        
        $oMbqRdEtPc = MbqMain::$oClk->newObj('MbqRdEtPc');
        if($in->convId != null)
        {
            $in->convId = explode(',', $in->convId);
            foreach($in->convId as $convId)
            {
                if ($oMbqEtPc = $oMbqRdEtPc->initOMbqEtPc($convId, array('case' => 'byConvId'))) {
                    $oMbqWrEtPc = MbqMain::$oClk->newObj('MbqWrEtPc');
                    $oMbqWrEtPc->markPcUnread($oMbqEtPc);
                } else {
                    MbqError::alert('', "Need valid conversation id!", '', MBQ_ERR_APP);
                }
            }
        }
        $this->data['result'] = true;
    }
}