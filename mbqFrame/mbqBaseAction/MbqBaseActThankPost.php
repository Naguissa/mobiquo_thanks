<?php

defined('MBQ_IN_IT') or exit;

/**
 * thank_post action
 */
Abstract Class MbqBaseActThankPost extends MbqBaseAct {
    
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
        $oMbqEtThank = MbqMain::$oClk->newObj('MbqEtThank');
        $oMbqEtThank->key->setOriValue($in->postId);
        $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
        if ($oMbqEtForumPost = $oMbqRdEtForumPost->initOMbqEtForumPost($oMbqEtThank->key->oriValue, array('case' => 'byPostId'))) {
            $oMbqAclEtForumPost = MbqMain::$oClk->newObj('MbqAclEtForumPost');
            $aclResult = $oMbqAclEtForumPost->canAclThankPost($oMbqEtForumPost);
            if ($aclResult === true) {    //acl judge
                $oMbqWrEtForumPost = MbqMain::$oClk->newObj('MbqWrEtForumPost');
                $oMbqEtThank->userId->setOriValue(MbqMain::$oCurMbqEtUser->userId->oriValue);
                $oMbqWrEtForumPost->thankPost($oMbqEtForumPost, $oMbqEtThank);
                $this->data['result'] = true;
                $oTapatalkPush = new TapatalkPush();
                $oTapatalkPush->callMethod('doInternalPushThank', array(
                    'oMbqEtForumPost' => $oMbqEtForumPost,
                    'oMbqEtThank' => $oMbqEtThank
                ));
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid post id!", '', MBQ_ERR_APP);
        }
    }
  
}