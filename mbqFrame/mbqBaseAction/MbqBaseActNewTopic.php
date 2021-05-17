<?php

defined('MBQ_IN_IT') or exit;

/**
 * new_topic action
 */
Abstract Class MbqBaseActNewTopic extends MbqBaseAct {

    public function __construct() {
        parent::__construct();
    }

    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->forumId = $this->getInputParam('forumId');
            $in->subject = $this->getInputParam('subject');
            $in->body = $this->getInputParam('body');
            $in->prefixId = $this->getInputParam('prefixId');
            $in->attachmentIds = $this->getInputParam('attachmentIds');
            $in->groupId = $this->getInputParam('groupId');
            $in->poll = $this->getInputParam('poll');
        }
        else
        {
            $in->forumId = $this->getInputParam(0);
            $in->subject = $this->getInputParam(1);
            $in->body = $this->getInputParam(2);
            $in->prefixId = $this->getInputParam(3);
            $in->attachmentIds = $this->getInputParam(4);
            $in->groupId = $this->getInputParam(5);
            $in->poll = $this->getInputParam(6);
        }
        $in->subject =str_replace(array('<','>'),array('&lt;','&gt;'),$in->subject);
        return $in;
    }

    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('forum')) {
            MbqError::alert('', "Not support module forum!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqEtForumTopic = MbqMain::$oClk->newObj('MbqEtForumTopic');

        $oMbqEtForumTopic->forumId->setOriValue($in->forumId);
        $oMbqEtForumTopic->topicTitle->setOriValue($in->subject);
        $oMbqEmoji = MbqMain::$oClk->newObj('MbqEmoji');
        $body = $oMbqEmoji->DoReplace($in->body);
        $oMbqEtForumTopic->topicContent->setOriValue($body);
        $oMbqEtForumTopic->prefixId->setOriValue($in->prefixId);
        if (isset($in->attachmentIds)) $oMbqEtForumTopic->attachmentIdArray->setOriValue($in->attachmentIds);
        if (isset($in->groupId)) $oMbqEtForumTopic->groupId->setOriValue($in->groupId);
        if (isset($in->poll)) {
            $oMbqEtPoll = MbqMain::$oClk->newObj('MbqEtPoll');
            $oMbqEtPoll->pollTitle->setOriValue($in->poll['title']);
            $oMbqEtPoll->pollOptions->setOriValue($in->poll['options']);
            $oMbqEtPoll->pollLength->setOriValue($in->poll['length']);
            $oMbqEtPoll->pollMaxOptions->setOriValue($in->poll['max_options'] ? $in->poll['max_options'] : 1);
            $oMbqEtPoll->canRevoting->setOriValue($in->poll['can_revoting'] ? $in->poll['can_revoting'] : false);
            $oMbqEtPoll->canViewBeforeVote->setOriValue($in->poll['can_view_before_vote'] ? $in->poll['can_view_before_vote'] : false);
            $oMbqEtPoll->canPublic->setOriValue($in->poll['can_public'] ? $in->poll['can_public'] : false);
            $oMbqEtForumTopic->oMbqEtPoll = $oMbqEtPoll;
        }

        $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
        if ($oMbqEtForum = $oMbqRdEtForum->initOMbqEtForum($oMbqEtForumTopic->forumId->oriValue, array('case' => 'byForumId'))) {
            $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
            $aclResult = $oMbqAclEtForumTopic->canAclNewTopic($oMbqEtForum);
            if ($aclResult === true) {    //acl judge
                $oMbqEtForumTopic->oMbqEtForum = $oMbqEtForum;
                $oMbqWrEtForumTopic = MbqMain::$oClk->newObj('MbqWrEtForumTopic');
                $result = $oMbqWrEtForumTopic->addMbqEtForumTopic($oMbqEtForumTopic);
                if(is_a($result,'MbqEtForumTopic'))
                {
                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    $this->data['result'] = true;
                    $data1 = $oMbqRdEtForumTopic->returnApiDataForumTopic($result);
                    MbqMain::$oMbqCm->mergeApiData($this->data, $data1);
                    $this->data['state'] = $result->state->oriValue;
                    $oTapatalkPush = new TapatalkPush();
                    $oTapatalkPush->callMethod('doInternalPushNewTopic', array(
                        'oMbqEtForumTopic' => $result
                    ));
                    return $result;
                }
                else
                {
                    MbqError::alert('', $result, '', MBQ_ERR_APP);
                }
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid forum id!", '', MBQ_ERR_APP);
        }
    }

}