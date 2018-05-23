<?php

defined('MBQ_IN_IT') or exit;

/**
 * m_merge_post action
 */
Abstract Class MbqBaseActMMergePost extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->postIds = $this->getInputParam('postIds');
            $in->postId = $this->getInputParam('postId');
        }
        else
        {
            $in->postIds = $this->getInputParam(0);
            $in->postId = $this->getInputParam(1);
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
            $aclResult = $oMbqAclEtForumPost->canAclMMergePost($oMbqEtForumPost);
            if ($aclResult === true) {    //acl judge
                if(!empty($in->postIds)){
                    $objsMbqEtForumPost = $oMbqRdEtForumPost->getObjsMbqEtForumPost($in->postIds, array('case' => 'byPostIds'));
                    $oMbqWrEtForumPost = MbqMain::$oClk->newObj('MbqWrEtForumPost');
                    $result = $oMbqWrEtForumPost->mMergePost($objsMbqEtForumPost, $oMbqEtForumPost);
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
                }else{
                    MbqError::alert('', "Cannot merge only one post!", '', MBQ_ERR_APP);
                }  
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid post id!", '', MBQ_ERR_APP);
        }
    }
  
}