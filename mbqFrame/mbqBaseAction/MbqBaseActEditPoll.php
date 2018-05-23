<?php

defined('MBQ_IN_IT') or exit;

/**
 * edit_poll action
 */
Abstract Class MbqBaseActEditPoll extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->topicId = $this->getInputParam('topicId');
            $in->title = $this->getInputParam('title');
            $in->options = (array) $this->getInputParam('options');
            $in->newOptions = (array) $this->getInputParam('newOptions');
            $in->length = $this->getInputParam('length');
            $in->maxOptions = (boolean) $this->getInputParam('maxOptions');
            $in->canRevoting = (boolean) $this->getInputParam('canRevoting');
            $in->canViewBeforeVote = (boolean) $this->getInputParam('canViewBeforeVote');
            $in->canPublic = (boolean) $this->getInputParam('canPublic');
        }
        else
        {
            $in->topicId = $this->getInputParam(0);
            $in->title = $this->getInputParam(1);
            $in->options = (array) $this->getInputParam(2);
            $in->newOptions = (array) $this->getInputParam(3);
            $in->length = $this->getInputParam(4);
            $in->maxOptions = (boolean) $this->getInputParam(5);
            $in->canRevoting = (boolean) $this->getInputParam(6);
            $in->canViewBeforeVote = (boolean) $this->getInputParam(7);
            $in->canPublic = (boolean) $this->getInputParam(8);
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

        $oMbqEtPoll = MbqMain::$oClk->newObj('MbqEtPoll');
        $oMbqEtPoll->topicId->setOriValue($in->topicId);
        $oMbqEtPoll->pollTitle->setOriValue($in->title);
        $oMbqEtPoll->pollOptions->setOriValue($in->options);
        $oMbqEtPoll->newOptions->setOriValue($in->newOptions);
        $oMbqEtPoll->pollLength->setOriValue($in->length);
        if (isset($in->maxOptions)) $oMbqEtPoll->pollMaxOptions->setOriValue($in->maxOptions);
        if (isset($in->canRevoting)) $oMbqEtPoll->canRevoting->setOriValue($in->canRevoting);
        if (isset($in->canViewBeforeVote)) $oMbqEtPoll->canViewBeforeVote->setOriValue($in->canViewBeforeVote);
        if (isset($in->canPublic)) $oMbqEtPoll->canPublic->setOriValue($in->canPublic);

        $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
        if ($oMbqEtForumTopic = $oMbqRdEtForumTopic->initOMbqEtForumTopic($oMbqEtPoll->topicId->oriValue, array('case' => 'byTopicId'))) {
            $oMbqEtPoll->oMbqEtForumTopic = $oMbqEtForumTopic;
            
            $oMbqAclEtPoll = MbqMain::$oClk->newObj('MbqAclEtPoll');
            $aclResult = $oMbqAclEtPoll->canAclEditPoll($oMbqEtPoll);
            if ($aclResult === true) {    //acl judge
                $oMbqWrEtPoll = MbqMain::$oClk->newObj('MbqWrEtPoll');
                $result = $oMbqWrEtPoll->editPoll($oMbqEtPoll);
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