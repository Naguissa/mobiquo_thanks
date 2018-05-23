<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_raw_post action
 */
Abstract Class MbqBaseActGetRawPost extends MbqBaseAct {

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
        if ($oMbqEtForumPost = $oMbqRdEtForumPost->initOMbqEtForumPost($in->postId, array('case' => 'byPostId'))) {
            $oMbqAclEtForumPost = MbqMain::$oClk->newObj('MbqAclEtForumPost');
            $aclResult = $oMbqAclEtForumPost->canAclGetRawPost($oMbqEtForumPost);
            if ($aclResult === true) {   //acl judge
                $this->data = $oMbqRdEtForumPost->returnApiDataForumPost($oMbqEtForumPost);
                $this->data['post_content'] = (string) $oMbqRdEtForumPost->getRawPostContent($oMbqEtForumPost);
                $this->data['post_content_original'] = (string) $oMbqRdEtForumPost->getRawPostContentOriginal($oMbqEtForumPost);
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid post id!", '', MBQ_ERR_APP);
        }
    }

}