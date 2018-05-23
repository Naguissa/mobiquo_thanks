<?php

defined('MBQ_IN_IT') or exit;

/**
 * m_move_post action
 */
Abstract Class MbqBaseActMMovePost extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->postId = $this->getInputParam('postId');
            $in->topicId = $this->getInputParam('topicId');
            $in->topicTitle = $this->getInputParam('topicTitle');
            $in->forumId = $this->getInputParam('forumId');
        }
        else
        {
            $in->postId = $this->getInputParam(0);
            $in->topicId = $this->getInputParam(1);
            $in->topicTitle = $this->getInputParam(2);
            $in->forumId = $this->getInputParam(3);
        }
        $in->postId = explode(',', $in->postId);
        $in->postId = array_map("trim", $in->postId);
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
        $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
        $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
        foreach($in->postId as $postId)
        {
            $oMbqEtForumPosts[] = $oMbqRdEtForumPost->initOMbqEtForumPost($postId, array('case' => 'byPostId'));
        }
        $oMbqEtForumTopic = null;
        if(!empty($in->topicId))
        {
            $oMbqEtForumTopic = $oMbqRdEtForumTopic->initOMbqEtForumTopic($in->topicId, array('case' => 'byTopicId'));
        }
        $oMbqEtForum = null;
        if(!empty($in->forumId))
        {
            $oMbqEtForum = $oMbqRdEtForum->initOMbqEtForum($in->forumId, array('case' => 'byForumId'));
        }
        if (!empty($oMbqEtForumPosts) && ($oMbqEtForum || $oMbqEtForumTopic)) {
            $oMbqAclEtForumPost = MbqMain::$oClk->newObj('MbqAclEtForumPost');
            $aclResult = $oMbqAclEtForumPost->canAclMMovePost($oMbqEtForumPosts, $oMbqEtForum, $oMbqEtForumTopic);
            if ($aclResult === true) {    //acl judge
                $oMbqWrEtForumPost = MbqMain::$oClk->newObj('MbqWrEtForumPost');
                $result = $oMbqWrEtForumPost->mMovePost($oMbqEtForumPosts, $oMbqEtForum, $oMbqEtForumTopic, $in->topicTitle);
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
            MbqError::alert('', "Need valid param!", '', MBQ_ERR_APP);
        }
    }
    
}