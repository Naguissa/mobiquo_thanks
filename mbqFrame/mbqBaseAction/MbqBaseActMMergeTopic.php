<?php

defined('MBQ_IN_IT') or exit;

/**
 * m_merge_topic action
 */
Abstract Class MbqBaseActMMergeTopic extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->topicIdA = $this->getInputParam('topicIdFrom');
            $in->topicIdB = $this->getInputParam('topicIdTo');
            $in->redirect = $this->getInputParam('redirect');
        }
        else
        {
            $in->topicIdA = $this->getInputParam(0);
            $in->topicIdB = $this->getInputParam(1);
            $in->redirect = $this->getInputParam(2);
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
      
      
        $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
        $oMbqEtForumTopicFrom = $oMbqRdEtForumTopic->initOMbqEtForumTopic($in->topicIdA, array('case' => 'byTopicId'));
        $oMbqEtForumTopicTo = $oMbqRdEtForumTopic->initOMbqEtForumTopic($in->topicIdB, array('case' => 'byTopicId'));
        
        if ( $oMbqEtForumTopicFrom &&  $oMbqEtForumTopicTo) {
            $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
            $aclResult = $oMbqAclEtForumTopic->canAclMMergeTopic($oMbqEtForumTopicFrom, $oMbqEtForumTopicTo);
            if ($aclResult === true) {    //acl judge
                $oMbqWrEtForumTopic = MbqMain::$oClk->newObj('MbqWrEtForumTopic');
                $result = $oMbqWrEtForumTopic->mMergeTopic($oMbqEtForumTopicFrom, $oMbqEtForumTopicTo ,$in->redirect);
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
            MbqError::alert('', "Need valid topic id!", '', MBQ_ERR_APP);
        }
    }
  
}