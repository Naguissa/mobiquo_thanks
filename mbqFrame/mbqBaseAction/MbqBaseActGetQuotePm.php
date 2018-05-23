<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_quote_pm action
 */
Abstract Class MbqBaseActGetQuotePm extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->messageId = $this->getInputParam('messageId');
        }
        else
        {
            $in->messageId = $this->getInputParam(0);
        }
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (MbqMain::$oMbqConfig->moduleIsEnable('pm')) {
            $oMbqAclEtPm = MbqMain::$oClk->newObj('MbqAclEtPm');
            $aclResult = $oMbqAclEtPm->canAclGetQuotePm();
            if($aclResult === true){
                $oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm');
                if($oMbqEtPm = $oMbqRdEtPm->initOMbqEtPm(array('msgId'=> $in->messageId), array('case' => 'byMsgId'))){
                    $oMbqRdEtPm->getQuotePm($oMbqEtPm);
                    $this->data = $oMbqRdEtPm->returnApiDataPm($oMbqEtPm, true);
                    $this->data['result'] = true;
                    $this->data['result_text'] = (string)'';
                }else{
                    MbqError::alert('', "Get message failed!", '', MBQ_ERR_APP);
                }
            }else{
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Not support module private message!", '', MBQ_ERR_NOT_SUPPORT);
        }
    }
  
}