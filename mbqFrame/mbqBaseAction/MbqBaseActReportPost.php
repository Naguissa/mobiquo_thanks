<?php

defined('MBQ_IN_IT') or exit;

/**
 * report_post action
 */
Abstract Class MbqBaseActReportPost extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->postId = $this->getInputParam('postId');
            $in->reason = $this->getInputParam('reason');
        }
        else
        {
            $in->postId = $this->getInputParam(0);
            $in->reason = $this->getInputParam(1);
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
        if ($oMbqEtForumPost = $oMbqRdEtForumPost->initOMbqEtForumPost($in->postId, array('case' => 'byPostId'))) {
            $oMbqAclEtForumPost = MbqMain::$oClk->newObj('MbqAclEtForumPost');
            $aclResult = $oMbqAclEtForumPost->canAclReportPost($oMbqEtForumPost);
            if ($aclResult === true) {    //acl judge
                $oMbqWrEtForumPost = MbqMain::$oClk->newObj('MbqWrEtForumPost');
                $result = $oMbqWrEtForumPost->reportPost($oMbqEtForumPost, $in->reason);
                if($result === true)
                {
                    $this->data['result'] = true;
                    $this->data['result_text'] = 'Post reported';
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
            MbqError::alert('', "Need valid post id!", '', MBQ_ERR_APP);
        }
    }
    
}