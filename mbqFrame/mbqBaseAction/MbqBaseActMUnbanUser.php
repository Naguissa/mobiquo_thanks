<?php

defined('MBQ_IN_IT') or exit;

/**
 * m_unban_user action
 */
Abstract Class MbqBaseActMUnbanUser extends MbqBaseAct {
    
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
            $aclResult = $oMbqAclEtUser->canAclMUnbanUser($oMbqEtUser);
            if ($aclResult === true) {   //acl judge
                $oMbqWrEtUser = MbqMain::$oClk->newObj('MbqWrEtUser');
                $result = $oMbqWrEtUser->mUnBanUser($oMbqEtUser);
                if($result === true)
                {
                    $this->data['result'] = true;
                }
                else if($result === false)
                {
                    $this->data['result'] = false;
                    $this->data['is_login_mod'] = true;
                    $this->data['result_text'] = 'You need to authenticate again to do the action';
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