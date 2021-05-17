<?php

defined('MBQ_IN_IT') or exit;

/**
 * save_raw_post action
 */
Abstract Class MbqBaseActSaveRawPost extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->postId = $this->getInputParam('postId');
            $in->postTitle = $this->getInputParam('subject');
            $in->postContent = $this->getInputParam('body');
            $in->returnHtml = (boolean) $this->getInputParam('returnHtml');
            $in->attachmentIds = $this->getInputParam('attachmentIds');
            $in->groupId = $this->getInputParam('groupId');
            $in->reason = $this->getInputParam('reason');
        }
        else
        {
            $in->postId = $this->getInputParam(0);
            $in->postTitle = $this->getInputParam(1);
            $in->postContent = $this->getInputParam(2);
            $in->returnHtml = (boolean) $this->getInputParam(3);
            $in->attachmentIds = $this->getInputParam(4);
            $in->groupId = $this->getInputParam(5);
            $in->reason = $this->getInputParam(6);
        }
        $in->reason =str_replace(array('<','>'),array('&lt;','&gt;'),$in->reason);
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
            $aclResult = $oMbqAclEtForumPost->canAclSaveRawPost($oMbqEtForumPost);
            if ($aclResult === true) {   //acl judge
                $oMbqEtForumPost->postTitle->setOriValue($in->postTitle);
                $oMbqEmoji = MbqMain::$oClk->newObj('MbqEmoji');
                $body = $oMbqEmoji->DoReplace($in->postContent);
                $oMbqEtForumPost->postContent->setOriValue($body);
             
                $oMbqWrEtForumPost = MbqMain::$oClk->newObj('MbqWrEtForumPost');
                $oMbqEtForumPost = $oMbqWrEtForumPost->mdfMbqEtForumPost($oMbqEtForumPost, array('case' => 'edit', 'in' => $in));
                if(is_a($oMbqEtForumPost,'MbqEtForumPost'))
                {
                    $state = $oMbqEtForumPost->state->oriValue;
                    //reload post
                    $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                    if ($oMbqEtForumPostResult = $oMbqRdEtForumPost->initOMbqEtForumPost($oMbqEtForumPost->postId->oriValue, array('case' => 'byPostId'))) {
                        $this->data['result'] = true;
                        $data1 = $oMbqRdEtForumPost->returnApiDataForumPost($oMbqEtForumPostResult, $in->returnHtml);
                        MbqMain::$oMbqCm->mergeApiData($this->data, $data1);
                        if($state != null)
                        {
                            $this->data['state'] = $state;
                        }
                    } else {
                        MbqError::alert('', "Can not load modified post!", '', MBQ_ERR_APP);
                    }
                }
                else
                {
                    MbqError::alert('', $oMbqEtForumPost, '', MBQ_ERR_APP);
                }
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid post id!", '', MBQ_ERR_APP);
        }
    }
  
}