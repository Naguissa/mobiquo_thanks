<?php

defined('MBQ_IN_IT') or exit;

/**
 * report_pm action
 */
Abstract Class MbqBaseActReportPm extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->msgId = $this->getInputParam('messageId');
            $in->reason = $this->getInputParam('reason');
        }
        else
        {
            $in->msgId = $this->getInputParam(0);
            $in->reason = $this->getInputParam(1);
        }
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('pm')) {
            MbqError::alert('', "Not support module private message!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm');
        if ($oMbqEtPm = $oMbqRdEtPm->initOMbqEtPm($in->msgId, array('case' => 'byMsgId'))) {
            $oMbqAclEtPm = MbqMain::$oClk->newObj('MbqAclEtPm');
            $aclResult = $oMbqAclEtPm->canAclReportPm($oMbqEtPm);
            if ($aclResult === true) {    //acl judge
                $oMbqWrEtPm = MbqMain::$oClk->newObj('MbqWrEtPm');
                $result = $oMbqWrEtPm->reportPm($oMbqEtPm, $in->reason);
                if($result === true)
                {
                    $this->data['result'] = true;
                    $this->data['result_text'] = 'Private message reported';
                }
                else
                {
                    $this->data['result'] = false;
                    $this->data['result_text'] = $result;
                }
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid private message id!", '', MBQ_ERR_APP);
        }
    }
  
}