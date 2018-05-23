<?php

defined('MBQ_IN_IT') or exit;

/**
 * m_delete_post action
 */
Abstract Class MbqBaseActMDeletePost extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->postId = $this->getInputParam('postId');
            $in->mode = (int) $this->getInputParam('mode');
            $in->reason = $this->getInputParam('reason');
        }
        else
        {
            $in->postId = $this->getInputParam(0);
            $in->mode = (int) $this->getInputParam(1);
            $in->reason = $this->getInputParam(2);
        }
        if ($in->mode != 1 && $in->mode != 2) {
            MbqError::alert('', "Need valid mode!", '', MBQ_ERR_APP);
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
            $aclResult = $oMbqAclEtForumPost->canAclMDeletePost($oMbqEtForumPost, $in->mode);
            if ($aclResult) {    //acl judge
                $oMbqWrEtForumPost = MbqMain::$oClk->newObj('MbqWrEtForumPost');
                $result = $oMbqWrEtForumPost->mDeletePost($oMbqEtForumPost, $in->mode, $in->reason);
                if($result === true)
                {
                    $this->data['result'] = true;
                    $oTapatalkPush = new TapatalkPush();
                    $oTapatalkPush->callMethod('doInternalPushDeletePost', array(
                        'oMbqEtForumPost' => $oMbqEtForumPost,
                        'mode' => $in->mode,
                        'reason' => $in->reason
                    ));
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

                    if(empty($result))
                    {
                        $this->data['result_text'] = 'You do not have permission to do this action';
                    }
                }
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid post id!", '', MBQ_ERR_APP);
        }
    }
  
}