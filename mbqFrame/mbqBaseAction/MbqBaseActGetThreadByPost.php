<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_thread_by_post action
 */
Abstract Class MbqBaseActGetThreadByPost extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->postId = $this->getInputParam('postId');
            $in->postsPerRequest = (int) $this->getInputParam('perPage', 20);
            $in->returnHtml = $this->getInputParam('returnHtml');
        }
        else
        {
            $in->postId = $this->getInputParam(0);
            $in->postsPerRequest = (int) $this->getInputParam(1, 20);
            $in->returnHtml = $this->getInputParam(2);
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
       
        $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
        if ($oMbqEtForumPost = $oMbqRdEtForumPost->initOMbqEtForumPost($in->postId, array('case' => 'byPostId', 'perPage' => $in->postsPerRequest, 'requirePosition'=>true))) {
            $topicId = $oMbqEtForumPost->topicId->oriValue;
        } else {
            MbqError::alert('', "Need valid post id!", '', MBQ_ERR_APP);
        }
         $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
        if ($oMbqEtForumTopic = $oMbqRdEtForumTopic->initOMbqEtForumTopic($topicId, array('case' => 'byTopicId'))) {
            $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
            $aclResult = $oMbqAclEtForumTopic->canAclGetThread($oMbqEtForumTopic);
            if ($aclResult === true) {    //acl judge
                $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
                $oMbqDataPage->initByPositionAndPerPage($oMbqEtForumPost->position->oriValue, $in->postsPerRequest);
                $oMbqDataPage = $oMbqRdEtForumPost->getObjsMbqEtForumPost($oMbqEtForumTopic, array('case' => 'byTopic', 'oMbqDataPage' => $oMbqDataPage));
                $this->data = $oMbqRdEtForumTopic->returnApiDataForumTopic($oMbqEtForumTopic);
                if ($oMbqEtForumTopic->hasPoll->oriValue)
                {
                    $oMbqRdEtPoll = MbqMain::$oClk->newObj('MbqRdEtPoll');
                    $oMbqEtPoll = $oMbqRdEtPoll->initOMbqEtPoll($oMbqEtForumTopic->topicId->oriValue, false);
                    $this->data['poll'] = $oMbqRdEtPoll->returnApiDataPoll($oMbqEtPoll);
                }
                $this->data['position'] = (int) $oMbqEtForumPost->position->oriValue;
                if(isset($oMbqEtForumTopic->oMbqEtForum))
                {
                    $this->data['forum_name'] = (string) $oMbqEtForumTopic->oMbqEtForum->forumName->oriValue;
                    $this->data['can_upload'] = (boolean) $oMbqEtForumTopic->oMbqEtForum->canUpload->oriValue;
                }
                $this->data['posts'] = $oMbqRdEtForumPost->returnApiArrDataForumPost($oMbqDataPage->datas, $in->returnHtml);
                $oMbqWrEtForumTopic = MbqMain::$oClk->newObj('MbqWrEtForumTopic');
                /* add forum topic view num */
                $oMbqWrEtForumTopic->addForumTopicViewNum($oMbqEtForumTopic);
                /* mark forum topic read */
                $oMbqWrEtForumTopic->markForumTopicRead($oMbqEtForumTopic);
                /* reset forum topic subscription */
                $oMbqWrEtForumTopic->resetForumTopicSubscription($oMbqEtForumTopic);
            } else {
                if (MbqMain::hasLogin()) {
                    $reason = ['reason' => MBQ_ERR_NOT_PERMISSION];
                }else{
                    $reason = ['reason' => MBQ_ERR_LOGIN_REQUIRED];
                }
                MbqError::alert('', $aclResult, $reason, MBQ_ERR_APP);
            }
        } else {
            $reason = ['reason' => MBQ_ERR_DATA_NOT_FOUND];
            MbqError::alert('', "This topic does not exist or you do not have permission to access it!", $reason, MBQ_ERR_APP);
        }
    }
  
}