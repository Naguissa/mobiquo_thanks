<?php

defined('MBQ_IN_IT') or exit;

/**
 * mark_pm_unread action
 */
Abstract Class MbqBaseActMarkPmUnread extends MbqBaseAct {

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
        if (!MbqMain::$oMbqConfig->moduleIsEnable('pm')) {
            MbqError::alert('', "Not support module private message!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm');
        if ($oMbqEtPm = $oMbqRdEtPm->initOMbqEtPm(array('msgId'=> $in->messageId), array('case' => 'byMsgId'))) {
            $oMbqAclEtPm = MbqMain::$oClk->newObj('MbqAclEtPm');
            $aclResult = $oMbqAclEtPm->canAclMarkPmUnread($oMbqEtPm);
            if ($aclResult === true) {    //acl judge
                $oMbqWrEtPm = MbqMain::$oClk->newObj('MbqWrEtPm');
                $result = $oMbqWrEtPm->markPmUnread($oMbqEtPm);
                if($result === true)
                {
                    $this->data['result'] = true;
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