<?php

defined('MBQ_IN_IT') or exit;


Abstract Class MbqBaseActUploadAvatar extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('forum')) {
            MbqError::alert('', "Not support module forum!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqAclEtUser = MbqMain::$oClk->newObj('MbqAclEtUser');
        $aclResult = $oMbqAclEtUser->canAclUploadAvatar();
        if ($aclResult === true) {    //acl judge
            $oMbqWrEtUser = MbqMain::$oClk->newObj('MbqWrEtUser');
            $result = $oMbqWrEtUser->uploadAvatar();
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