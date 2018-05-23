<?php

defined('MBQ_IN_IT') or exit;

/**
 * mark_all_as_read action
 */
Abstract Class MbqBaseActMarkTopicRead extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->topicIds = $this->getInputParam('topicIds');
        }
        else
        {
            $in->topicIds = $this->getInputParam(0);
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
        $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
        $oMbqWrEtForumTopic = MbqMain::$oClk->newObj('MbqWrEtForumTopic');
        foreach($in->topicIds as $topicId)
        {
            if ($oMbqEtForumTopic = $oMbqRdEtForumTopic->initOMbqEtForumTopic($topicId, array('case' => 'byTopicId'))) {
                $aclResult = $oMbqAclEtForumTopic->canAclMarkTopicRead($oMbqEtForumTopic);
                if ($aclResult === true) {    //acl judge
                    $result  = $oMbqWrEtForumTopic->markForumTopicRead($oMbqEtForumTopic);
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
    }
  
}