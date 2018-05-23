<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_subscribed_topic action
 */
Abstract Class MbqBaseActGetSubscribedTopic extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
        $this->supportLevels = array(4);
        $this->currLevel = 4;
    }
    
    function getInput()
    {
        $in = new stdClass();
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        if(MbqMain::isJsonProtocol())
        {
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam('page'), $this->getInputParam('perPage'));
        }
        else
        {
            $startNum = (int) $this->getInputParam(0);
            $lastNum = (int) $this->getInputParam(1);
            $oMbqDataPage->initByStartAndLast($startNum, $lastNum);
        }
        $in->oMbqDataPage = $oMbqDataPage;
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('subscribe')) {
            MbqError::alert('', "Not support module subscribe!", '', MBQ_ERR_NOT_SUPPORT);
        }
        if (!MbqMain::$oMbqConfig->moduleIsEnable('forum')) {
            MbqError::alert('', "Not support module forum!", '', MBQ_ERR_NOT_SUPPORT);
        }
       
        $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
        $aclResult = $oMbqAclEtForumTopic->canAclGetSubscribedTopic();
        if ($aclResult === true) {     //acl judge
            $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
            $in->oMbqDataPage = $oMbqRdEtForumTopic->getObjsMbqEtForumTopic(MbqMain::$oCurMbqEtUser->userId->oriValue, array('case' => 'subscribed', 'oMbqDataPage' => $in->oMbqDataPage));
            $this->data['total_topic_num'] = (int) $in->oMbqDataPage->totalNum;
            $this->data['topics'] = $oMbqRdEtForumTopic->returnApiArrDataForumTopic($in->oMbqDataPage->datas);
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
  
}