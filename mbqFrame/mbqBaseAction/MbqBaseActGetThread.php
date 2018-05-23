<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_thread action
 */
Abstract Class MbqBaseActGetThread extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        if(MbqMain::isJsonProtocol())
        {
            $in->topicId = $this->getInputParam('topicId');
            $in->returnHtml = $this->getInputParam('returnHtml');
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam('page'), $this->getInputParam('perPage'));
        }
        else
        {
            $in->topicId = $this->getInputParam(0);
            $startNum = (int) $this->getInputParam(1);
            $lastNum = (int) $this->getInputParam(2);
            $in->returnHtml = $this->getInputParam(3);
            $oMbqDataPage->initByStartAndLast($startNum, $lastNum);
        }
        $in->oMbqDataPage = $oMbqDataPage;
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
            $aclResult = $oMbqAclEtForumTopic->canAclGetThread($oMbqEtForumTopic);
            if ($aclResult === true) {    //acl judge
                $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                $in->oMbqDataPage = $oMbqRdEtForumPost->getObjsMbqEtForumPost($oMbqEtForumTopic, array('case' => 'byTopic', 'oMbqDataPage' => $in->oMbqDataPage, 'returnHtml' => $in->returnHtml));
                $this->data = $oMbqRdEtForumTopic->returnApiDataForumTopic($oMbqEtForumTopic);
                if ($oMbqEtForumTopic->hasPoll->oriValue)
                {
                    $oMbqRdEtPoll = MbqMain::$oClk->newObj('MbqRdEtPoll');
                    $oMbqEtPoll = $oMbqRdEtPoll->initOMbqEtPoll($oMbqEtForumTopic->topicId->oriValue, false);
                    $this->data['poll'] = $oMbqRdEtPoll->returnApiDataPoll($oMbqEtPoll);
                }
                if(isset($oMbqEtForumTopic->oMbqEtForum))
                {
                    $this->data['forum_name'] = (string) $oMbqEtForumTopic->oMbqEtForum->forumName->oriValue;
                    $this->data['can_upload'] = (boolean) $oMbqEtForumTopic->oMbqEtForum->canUpload->oriValue;
                }
                $this->data['posts'] = $oMbqRdEtForumPost->returnApiArrDataForumPost($in->oMbqDataPage->datas, $in->returnHtml);
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