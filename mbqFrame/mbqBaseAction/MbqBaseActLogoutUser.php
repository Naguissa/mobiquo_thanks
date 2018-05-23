<?php

defined('MBQ_IN_IT') or exit;

/**
 * logout_user action
 */
Abstract Class MbqBaseActLogoutUser extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            
        }
        else
        {
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
        $result = $oMbqRdEtUser->logout();
        if ($result) {
            $this->data['result'] = true;
        } else {
            $this->data['result'] = false;
        }
    }
  
}