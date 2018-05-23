<?php

defined('MBQ_IN_IT') or exit;

/**
 * m_ban_user action
 */
Abstract Class MbqBaseActMBanUser extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->username = $this->getInputParam('username');
            $in->mode = $this->getInputParam('mode');
            $in->reason = $this->getInputParam('reason');
            $in->expires = $this->getInputParam('expires');
        }
        else
        {
            $in->username = $this->getInputParam(0);
            $in->mode = $this->getInputParam(1);
            $in->reason = $this->getInputParam(2);
            $in->expires = $this->getInputParam(3);
        }
        if ($in->mode != 1 && $in->mode != 2) {
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
      
        if ($oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($in->username, array('case' => 'byLoginName'))) {
            $oMbqAclEtUser = MbqMain::$oClk->newObj('MbqAclEtUser');
            $aclResult = $oMbqAclEtUser->canAclMBanUser($oMbqEtUser, $in->mode);
            if ($aclResult === true) {   //acl judge
                $oMbqWrEtUser = MbqMain::$oClk->newObj('MbqWrEtUser');
                $result = $oMbqWrEtUser->mBanUser($oMbqEtUser, $in->mode, $in->reason, $in->expires);
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