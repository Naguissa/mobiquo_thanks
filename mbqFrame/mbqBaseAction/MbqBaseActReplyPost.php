<?php

defined('MBQ_IN_IT') or exit;

/**
 * reply_post action
 */
Abstract Class MbqBaseActReplyPost extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->forumId = $this->getInputParam('forumId');
            $in->topicId = $this->getInputParam('topicId');
            $in->subject = $this->getInputParam('subject');
            $in->body = $this->getInputParam('body');
            $in->attachmentIds = (array) $this->getInputParam('attachmentIds');
            $in->groupId = $this->getInputParam('groupId');
            $in->returnHtml = (boolean) $this->getInputParam('returnHtml');
        }
        else
        {
            $in->forumId = $this->getInputParam(0);
            $in->topicId = $this->getInputParam(1);
            $in->subject = $this->getInputParam(2);
            $in->body = $this->getInputParam(3);
            $in->attachmentIds = (array) $this->getInputParam(4);
            $in->groupId = $this->getInputParam(5);
            $in->returnHtml = (boolean) $this->getInputParam(6);
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
        
        $oMbqEtForumPost = MbqMain::$oClk->newObj('MbqEtForumPost');
    
        $oMbqEtForumPost->forumId->setOriValue($in->forumId);
        $oMbqEtForumPost->topicId->setOriValue($in->topicId);
        $oMbqEtForumPost->postTitle->setOriValue($in->subject);
        $oMbqEmoji = MbqMain::$oClk->newObj('MbqEmoji');
        $body = $oMbqEmoji->DoReplace($in->body);
        $oMbqEtForumPost->postContent->setOriValue($body);
        if (isset($in->attachmentIds)) $oMbqEtForumPost->attachmentIdArray->setOriValue($in->attachmentIds);
        if (isset($in->groupId)) $oMbqEtForumPost->groupId->setOriValue($in->groupId);
        $returnHtml = $in->returnHtml;
      
        $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
        if ($oMbqEtForum = $oMbqRdEtForum->initOMbqEtForum($oMbqEtForumPost->forumId->oriValue, array('case' => 'byForumId'))) {
            $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
            if ($oMbqEtForumTopic = $oMbqRdEtForumTopic->initOMbqEtForumTopic($oMbqEtForumPost->topicId->oriValue, array('case' => 'byTopicId'))) {
                if ($oMbqEtForumTopic->topicId->oriValue == $oMbqEtForumPost->topicId->oriValue && $oMbqEtForumTopic->forumId->oriValue == $oMbqEtForum->forumId->oriValue) {
                    $oMbqEtForumPost->oMbqEtForum = $oMbqEtForum;
                    $oMbqEtForumPost->oMbqEtForumTopic = $oMbqEtForumTopic;
                    $oMbqAclEtForumPost = MbqMain::$oClk->newObj('MbqAclEtForumPost');
                    $aclResult = $oMbqAclEtForumPost->canAclReplyPost($oMbqEtForumTopic);
                    if ($aclResult === true) {   //acl judge
                        $oMbqWrEtForumPost = MbqMain::$oClk->newObj('MbqWrEtForumPost');
                        $result = $oMbqWrEtForumPost->addMbqEtForumPost($oMbqEtForumPost);
                        if(is_a($result,'MbqEtForumPost'))
                        {
                            $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                            //reload post
                            if ($oMbqEtForumPost = $oMbqRdEtForumPost->initOMbqEtForumPost($result->postId->oriValue, array('case' => 'byPostId'))) {
                                $this->data['result'] = true;
                                $data1 = $oMbqRdEtForumPost->returnApiDataForumPost($oMbqEtForumPost, $returnHtml);
                                MbqMain::$oMbqCm->mergeApiData($this->data, $data1);
                                if($oMbqEtForumPost->state->hasSetOriValue())
                                {
                                    $this->data['state'] = $oMbqEtForumPost->state->oriValue;
                                }
                                $oTapatalkPush = new TapatalkPush();
                                $oTapatalkPush->callMethod('doInternalPushReply', array(
                                    'oMbqEtForumPost' => $oMbqEtForumPost
                                ));
                                
                            } else {
                                MbqError::alert('', "Can not load new post!", '', MBQ_ERR_APP);
                            }
                        }
                        else
                        {
                            MbqError::alert('', $result, '', MBQ_ERR_APP);
                        }
                    } else {
                        MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
                    }
                } else {
                    MbqError::alert('', "Data error!", '', MBQ_ERR_APP);
                }
            } else {
                MbqError::alert('', "Need valid topic id!", '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid forum id!", '', MBQ_ERR_APP);
        }
    }
    
}