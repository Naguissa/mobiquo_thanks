<?php

defined('MBQ_IN_IT') or exit;

/**
 * prefetch account
 */
Abstract Class MbqBaseActPrefetchAccount extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->email = $this->getInputParam('email');
        }
        else
        { 
            $in->email = $this->getInputParam(0);
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
        $result = $oMbqRdEtUser->initOMbqEtUser($in->email, array('case' => 'byEmail'));
        if ($result) {
            $oMbqEtUser = $result;
            $this->data['result'] = true;
            if ($oMbqEtUser->userId->hasSetOriValue()) {
                $this->data['user_id'] = (string) $oMbqEtUser->userId->oriValue;
            }
            if ($oMbqEtUser->loginName->hasSetOriValue()) {
                $this->data['login_name'] = (string) $oMbqEtUser->loginName->oriValue;
            }
            $this->data['display_name'] = (string) $oMbqEtUser->getDisplayName();
            if ($oMbqEtUser->iconUrl->hasSetOriValue()) {
                $this->data['avatar'] = (string) $oMbqEtUser->iconUrl->oriValue;
            } else {
                $this->data['avatar'] = '';
            }
           
            
        } else {
            $this->data['result'] = false;
        }
        $custom_register_fields = $oMbqRdEtUser->getCustomRegisterFields();
        if(isset($custom_register_fields))
        {
            $this->data['custom_register_fields'] = $custom_register_fields;
        }
    }
    
}