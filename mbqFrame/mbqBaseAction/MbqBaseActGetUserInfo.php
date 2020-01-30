<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_user_info action
 */
Abstract Class MbqBaseActGetUserInfo extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->username = $this->getInputParam('username');
            $in->userId = $this->getInputParam('userId');
        }
        else
        {
            $in->username = $this->getInputParam(0);
            $in->userId = $this->getInputParam(1);
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
        if ($in->userId) {
            $result = $oMbqRdEtUser->initOMbqEtUser($in->userId, array('case' => 'byUserId'));
        } else {
            $result = $oMbqRdEtUser->initOMbqEtUser($in->username, array('case' => 'byLoginName'));
        }
        if (is_a($result,'MbqEtUser')) {
            $this->data = $oMbqRdEtUser->returnApiDataUser($result);
        } else if ($result != null) {
            MbqError::alert('', $result, '', MBQ_ERR_APP);
        } else {
            MbqError::alert('', "User not found!", ['reason' => MBQ_ERR_DATA_NOT_FOUND, 'error' => 'User not found!'], MBQ_ERR_APP);
        }
    }
  
}