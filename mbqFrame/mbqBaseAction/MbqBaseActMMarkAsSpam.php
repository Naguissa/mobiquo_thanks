<?php

defined('MBQ_IN_IT') or exit;

/**
 * m_mark_as_spam action
 */
Abstract Class MbqBaseActMMarkAsSpam extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->userId = $this->getInputParam('userId');
        }
        else
        {
            $in->userId = $this->getInputParam(0);
        }
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('user')) {
            MbqError::alert('', "Not support module user!", '', MBQ_ERR_NOT_SUPPORT);
        }
        
        $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
        if ($oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($in->userId, array('case' => 'byUserId'))) {
            $oMbqAclEtUser = MbqMain::$oClk->newObj('MbqAclEtUser');
            $aclResult = $oMbqAclEtUser->canAclMMarkAsSpam($oMbqEtUser);
            if ($aclResult === true) {   //acl judge
                $oMbqWrEtUser = MbqMain::$oClk->newObj('MbqWrEtUser');
                $oMbqWrEtUser->mMarkAsSpam($oMbqEtUser);
                $this->data['result'] = true;
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "User not found!", '', MBQ_ERR_APP);
        }
    }
}