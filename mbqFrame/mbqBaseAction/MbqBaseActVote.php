<?php

defined('MBQ_IN_IT') or exit;

/**
 * vote action
 */
Abstract Class MbqBaseActVote extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->topicId = $this->getInputParam('topicId');
            $in->voteOptions = $this->getInputParam('voteOptions');
        }
        else
        {
            $in->topicId = $this->getInputParam(0);
            $in->voteOptions = $this->getInputParam(1);
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

        $oMbqRdEtPoll = MbqMain::$oClk->newObj('MbqRdEtPoll');
        $oMbqEtPoll = $oMbqRdEtPoll->initOMbqEtPoll($in->topicId, false);

        $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
        if ($oMbqEtForumTopic = $oMbqRdEtForumTopic->initOMbqEtForumTopic($oMbqEtPoll->topicId->oriValue, array('case' => 'byTopicId'))) {
            $oMbqEtPoll->oMbqEtForumTopic = $oMbqEtForumTopic;
            $oMbqAclEtPoll = MbqMain::$oClk->newObj('MbqAclEtPoll');
            $aclResult = $oMbqAclEtPoll->canAclVote($oMbqEtPoll);
            if ($aclResult === true) {    //acl judge
                
                $oMbqEtPoll->voteOptions->setOriValue($in->voteOptions);
                $oMbqWrEtPoll = MbqMain::$oClk->newObj('MbqWrEtPoll');
                $result = $oMbqWrEtPoll->vote($oMbqEtPoll);
                if ($result === true) {
                    $this->data['result'] = true;
                } else {
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