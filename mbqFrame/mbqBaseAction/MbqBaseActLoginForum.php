<?php

defined('MBQ_IN_IT') or exit;

/**
 * login forum action
 */
Abstract Class MbqBaseActLoginForum extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->forumId = $this->getInputParam('forumId');
            $in->password = $this->getInputParam('password');
        }
        else
        {
            $in->forumId = $this->getInputParam(0);
            $in->password = $this->getInputParam(1);
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
        if (!MbqMain::$oMbqConfig->moduleIsEnable('forum')) {
            MbqError::alert('', "Not support module forum!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
        if ($oMbqEtForum = $oMbqRdEtForum->initOMbqEtForum($in->forumId, array('case' => 'byForumId'))) {
            $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
            $result = $oMbqRdEtForum->loginForum($oMbqEtForum, $in->password);
            if ($result === true) {
                $this->data['result'] = true;
            } else {
                $this->data['result'] = false;
                $this->data['result_text'] = $result;
            }
        } else {
            MbqError::alert('', "Need valid forum id!", '', MBQ_ERR_APP);
        }
    }
    
}