<?php

defined('MBQ_IN_IT') or exit;

/**
 * mark_pm_unread action
 */
Abstract Class MbqBaseActMarkPmRead extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->messageIds = $this->getInputParam('messageIds');
        }
        else
        {
            $in->messageIds = $this->getInputParam(0);
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
        if(!empty($in->messageIds))
        {
            $mesageIds = explode(',',$in->messageIds);
            foreach($mesageIds as $msgId)
            {
                if ($oMbqEtPm = $oMbqRdEtPm->initOMbqEtPm(array('msgId'=>$msgId), array('case' => 'byMsgId'))) {
                    $oMbqAclEtPm = MbqMain::$oClk->newObj('MbqAclEtPm');
                    $aclResult = $oMbqAclEtPm->canAclMarkPmRead($oMbqEtPm);
                    if ($aclResult === true) {    //acl judge
                        $oMbqWrEtPm = MbqMain::$oClk->newObj('MbqWrEtPm');
                        $result = $oMbqWrEtPm->markPmRead($oMbqEtPm);
                        if($result === true)
                        {
                            $this->data['result'] = true;
                        }
                        else
                        {
                            $this->data['result'] = false;
                            $this->data['result_text'] = $result;
                            break;
                        }
                    } else {
                        MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
                    }
                } else {
                    MbqError::alert('', "Need valid private message id!", '', MBQ_ERR_APP);
                }
            }
        }
        else
        {
            $oMbqAclEtPm = MbqMain::$oClk->newObj('MbqAclEtPm');
            $aclResult = $oMbqAclEtPm->canAclMarkAllPmRead();
            if ($aclResult === true) {    //acl judge
                $oMbqWrEtPm = MbqMain::$oClk->newObj('MbqWrEtPm');
                $result = $oMbqWrEtPm->markAllPmRead();
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
        }
    }
    
}