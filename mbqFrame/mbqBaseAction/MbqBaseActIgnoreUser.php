<?php

defined('MBQ_IN_IT') or exit;

/**
 * invite_participant action
 */
Abstract Class MbqBaseActIgnoreUser extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->userId = $this->getInputParam('userId');
            $in->mode = $this->getInputParam('mode', 1);
        }
        else
        {
            $in->userId = $this->getInputParam(0);
            $in->mode = $this->getInputParam(1, 1);
        }
        if ($in->mode != 0 && $in->mode != 1) {
            MbqError::alert('', "Need valid mode!", '', MBQ_ERR_APP);
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
        $oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($in->userId, array('case' => 'byUserId'));
        if(is_a($oMbqEtUser, 'MbqEtUser')) {
            $oMbqAclEtUser = MbqMain::$oClk->newObj('MbqAclEtUser');
            $aclResult = $oMbqAclEtUser->canAclIgnoreUser($oMbqEtUser, $in->mode);
            if ($aclResult === true) {   //acl judge
                $oMbqWrEtUser = MbqMain::$oClk->newObj('MbqWrEtUser');
                $result = $oMbqWrEtUser->ignoreUser($oMbqEtUser, $in->mode);
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
            MbqError::alert('', "User not found!", '', MBQ_ERR_APP);
        }
    }
  
}