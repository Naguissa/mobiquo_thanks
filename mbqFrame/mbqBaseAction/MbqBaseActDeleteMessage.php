<?php

defined('MBQ_IN_IT') or exit;

/**
 * delete_message action
 */
Abstract Class MbqBaseActDeleteMessage extends MbqBaseAct {
    
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
        }
        else
        {
            $in->messageId = $this->getInputParam(0);
            $in->boxId = $this->getInputParam(1);
        }
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (MbqMain::$oMbqConfig->moduleIsEnable('pm')) {
            $oMbqAclEtPm = MbqMain::$oClk->newObj('MbqAclEtPm');
            $oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm');
            if($oMbqEtPm = $oMbqRdEtPm->initOMbqEtPm(array('msgId'=> $in->messageId, 'boxId'=> $in->boxId), array('case' => 'byMsgId'))){
                $aclResult = $oMbqAclEtPm->canAclDeleteMessage($oMbqEtPm);
                if($aclResult === true){
                    $oMbqWrEtPm = MbqMain::$oClk->newObj('MbqWrEtPm'); //write class
                    if($oMbqWrEtPm->deleteMbqEtPmMessage($oMbqEtPm)){
                        $this->data['result'] = true;
                    }else{
                        MbqError::alert('', "Delete message failed!", '', MBQ_ERR_APP);
                    }
                }else{
                    MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
                }
            }else{
                MbqError::alert('', "Private message not found!", '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Not support module private message!", '', MBQ_ERR_NOT_SUPPORT);
        }
    }
    
}