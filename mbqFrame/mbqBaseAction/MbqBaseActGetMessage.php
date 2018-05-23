<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_message action
 */
Abstract Class MbqBaseActGetMessage extends MbqBaseAct {

    public function __construct() {
        parent::__construct();
    }

    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->messageId = $this->getInputParam('messageId');
            $in->boxId = $this->getInputParam('boxId');
            $in->returnHtml = $this->getInputParam('returnHtml');
        }
        else
        {
            $in->messageId = $this->getInputParam(0);
            $in->boxId = $this->getInputParam(1);
            $in->returnHtml = $this->getInputParam(2);
        }
        return $in;
    }

    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (MbqMain::$oMbqConfig->moduleIsEnable('pm')) {
            $oMbqAclEtPm = MbqMain::$oClk->newObj('MbqAclEtPm');
            $aclResult = $oMbqAclEtPm->canAclGetMessage();
            if($aclResult === true){
                $oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm');
                if($msg = $oMbqRdEtPm->initOMbqEtPm(array('msgId'=> $in->messageId, 'boxId'=> $in->boxId, 'returnHtml' => $in->returnHtml), array('case' => 'byMsgId'))){
                    $this->data = $oMbqRdEtPm->returnApiDataPm($msg, $in->returnHtml, true);
                    $this->data['result'] = true;
                    $this->data['result_text'] = (string)'';
                    $oMbqWrEtPm = MbqMain::$oClk->newObj('MbqWrEtPm');
                    $oMbqWrEtPm->markPmRead($msg);
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