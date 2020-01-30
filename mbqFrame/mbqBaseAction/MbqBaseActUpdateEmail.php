<?php

defined('MBQ_IN_IT') or exit;

/**
 * update email
 */
Abstract Class MbqBaseActUpdateEmail extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->userPassword = $this->getInputParam('password');
            $in->userEmail = $this->getInputParam('newEmail');
        }
        else
        {
            $in->userPassword = $this->getInputParam(0);
            $in->userEmail = $this->getInputParam(1);
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

       
        $oMbqAclEtUser = MbqMain::$oClk->newObj('MbqAclEtUser');
        $aclResult = $oMbqAclEtUser->canAclUpdateEmail();
        if ($aclResult === true) {
            $oMbqWrEtUser = MbqMain::$oClk->newObj('MbqWrEtUser');
            $result = $oMbqWrEtUser->updateEmail($in->userPassword, $in->userEmail, $resultMessage);
            if($result === true)
            {
                $this->data['result'] = true;
                $this->data['result_text'] = $resultMessage;
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