<?php

defined('MBQ_IN_IT') or exit;

/**
 * m_delete_topic action
 */
Abstract Class MbqBaseActMDeleteTopic extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->topicId = $this->getInputParam('topicId');
            $in->mode = (int) $this->getInputParam('mode');
            $in->reason = $this->getInputParam('reason');
        }
        else
        {
            $in->topicId = $this->getInputParam(0);
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
        
        $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
        if ($oMbqEtForumTopic = $oMbqRdEtForumTopic->initOMbqEtForumTopic($in->topicId, array('case' => 'byTopicId'))) {
            $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
            $aclResult = $oMbqAclEtForumTopic->canAclMDeleteTopic($oMbqEtForumTopic, $in->mode);
            if ($aclResult === true) {    //acl judge
                $oMbqWrEtForumTopic = MbqMain::$oClk->newObj('MbqWrEtForumTopic');
                $result = $oMbqWrEtForumTopic->mDeleteTopic($oMbqEtForumTopic, $in->mode, $in->reason);
                if($result === true)
                {
                    $this->data['result'] = true;
                    $oTapatalkPush = new TapatalkPush();
                    $oTapatalkPush->callMethod('doInternalPushDeleteTopic', array(
                        'oMbqEtForumTopic' => $oMbqEtForumTopic,
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
                }
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid topic id!", '', MBQ_ERR_APP);
        }
    }
    
}