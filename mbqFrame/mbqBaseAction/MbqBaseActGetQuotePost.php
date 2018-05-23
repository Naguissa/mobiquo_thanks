<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_quote_post action
 */
Abstract Class MbqBaseActGetQuotePost extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->postId = $this->getInputParam('postId');
        }
        else
        {
            $in->postId = $this->getInputParam(0);
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
        $postIds = explode('-',$in->postId);
        $postTitle  = '';
        $postContent = '';
        foreach($postIds as $postId)
        {
            if ($oMbqEtForumPost = $oMbqRdEtForumPost->initOMbqEtForumPost($postId, array('case' => 'byPostId'))) {
                $oMbqAclEtForumPost = MbqMain::$oClk->newObj('MbqAclEtForumPost');
                $aclResult = $oMbqAclEtForumPost->canAclGetQuotePost($oMbqEtForumPost);
                if ($aclResult === true) {    //acl judge
                    $postTitle = $oMbqEtForumPost->postTitle->oriValue;
                    $postContent .= $oMbqRdEtForumPost->getQuotePostContent($oMbqEtForumPost) . PHP_EOL;
                } else {
                    MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
                }
            } else {
                MbqError::alert('', "Need valid post id!", '', MBQ_ERR_APP);
            }
        }
        $this->data['post_id'] = implode('-', $postIds);
        $this->data['post_title'] = $postTitle;
        $this->data['post_content'] = $postContent;
    }
}