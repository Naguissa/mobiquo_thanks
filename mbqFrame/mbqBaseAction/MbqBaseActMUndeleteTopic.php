<?php

defined('MBQ_IN_IT') or exit;

/**
 * m_undelete_topic action
 */
Abstract Class MbqBaseActMUndeleteTopic extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->topicId = $this->getInputParam('topicId');
        }
        else
        {
            $in->topicId = $this->getInputParam(0);
        }
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('forum')) {
            MbqError::alert('', "Not support module forum!", '', MBQ_ERR_NOT_SUPPORT);
        }
      
        $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
        if ($oMbqEtForumTopic = $oMbqRdEtForumTopic->initOMbqEtForumTopic($in->topicId, array('case' => 'byTopicId'))) {
            $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
            $aclResult = $oMbqAclEtForumTopic->canAclMUndeleteTopic($oMbqEtForumTopic);
            if ($aclResult === true) {    //acl judge
                $oMbqWrEtForumTopic = MbqMain::$oClk->newObj('MbqWrEtForumTopic');
                $result = $oMbqWrEtForumTopic->mUndeleteTopic($oMbqEtForumTopic);
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
            MbqError::alert('', "Need valid topic id!", '', MBQ_ERR_APP);
        }
    }
    
}