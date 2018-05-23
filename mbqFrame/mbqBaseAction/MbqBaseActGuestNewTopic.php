<?php

defined('MBQ_IN_IT') or exit;

/**
 * guest new topic
 */
Abstract Class MbqBaseActGuestNewTopic extends MbqBaseAct
{
    public function __construct()
    {
        parent::__construct();
    }

    function getInput()
    {
        $in = new stdClass();
        if (MbqMain::isJsonProtocol()) {
            $in->forumId = $this->getInputParam('forumId');
            $in->subject = $this->getInputParam('subject');
            $in->body = $this->getInputParam('body');
            $in->username = $this->getInputParam('username');
        } else {
            $in->forumId = $this->getInputParam(0);
            $in->subject = $this->getInputParam(1);
            $in->body = $this->getInputParam(2);
            $in->username = $this->getInputParam(3);
        }
        return $in;
    }

    /**
     * action implement
     */
    protected function actionImplement($in)
    {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('forum')) {
            MbqError::alert('', "Not support module forum!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqEtForumTopic = MbqMain::$oClk->newObj('MbqEtForumTopic');

        $oMbqEtForumTopic->forumId->setOriValue($in->forumId);
        $oMbqEtForumTopic->topicTitle->setOriValue($in->subject);
        $oMbqEmoji = MbqMain::$oClk->newObj('MbqEmoji');
        $body = $oMbqEmoji->DoReplace($in->body);
        $oMbqEtForumTopic->topicContent->setOriValue($body);

        $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
        if ($oMbqEtForum = $oMbqRdEtForum->initOMbqEtForum($oMbqEtForumTopic->forumId->oriValue, array('case' => 'byForumId'))) {
            $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
            $aclResult = $oMbqAclEtForumTopic->canAclGuestNewTopic($oMbqEtForum);
            if ($aclResult === true) {    //acl judge
                if (is_array($oMbqEtForum->mbqBind)) {
                    $oMbqEtForum->mbqBind += ['guest_username' => $in->username];
                }
                $oMbqEtForumTopic->oMbqEtForum = $oMbqEtForum;
                $oMbqWrEtForumTopic = MbqMain::$oClk->newObj('MbqWrEtForumTopic');
                $result = $oMbqWrEtForumTopic->addMbqEtForumTopic($oMbqEtForumTopic);
                if (is_a($result, 'MbqEtForumTopic')) {
                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    $this->data['result'] = true;
                    $data1 = $oMbqRdEtForumTopic->returnApiDataForumTopic($result);
                    MbqMain::$oMbqCm->mergeApiData($this->data, $data1);
                    $this->data['state'] = $result->state->oriValue;
                    $oTapatalkPush = new TapatalkPush();
                    $oTapatalkPush->callMethod('doInternalPushNewTopic', array(
                        'oMbqEtForumTopic' => $result
                    ));
                } else {
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