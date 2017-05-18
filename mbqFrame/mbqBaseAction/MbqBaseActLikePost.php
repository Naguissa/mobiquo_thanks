<?php

defined('MBQ_IN_IT') or exit;

Abstract Class MbqBaseActLikePost extends MbqBaseAct {
    
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
    protected function actionImplement($in) {
        $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
        $oMbqEtForumPost = $oMbqRdEtForumPost->initOMbqEtForumPost($in->postId, array('case'=>'byPostId'));
        if (empty($oMbqEtForumPost)){
            MbqError::alert('', 'Need valid post id', '', MBQ_ERR_APP);
        }
        $oMbqAclEtForumPost = MbqMain::$oClk->newObj('MbqAclEtForumPost');
        $aclResult = $oMbqAclEtForumPost->canAclLikePost($oMbqEtForumPost);
        if ($aclResult === true) {
            if ($oMbqEtForumPost->isLiked->oriValue == 1){
                $this->data['result'] = true;
                return;
            }
            $oMbqWrEtForumPost = MbqMain::$oClk->newObj('MbqWrEtForumPost');
            $oMbqWrEtForumPost->likePost($oMbqEtForumPost);
            $oTapatalkPush = new TapatalkPush();
            $oTapatalkPush->callMethod('doInternalPushLike', array(
                'oMbqEtForumPost' => $oMbqEtForumPost,
            ));
            $this->data['result'] = true;
        }
        else
        {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
}