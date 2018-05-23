<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_votes_list action
 */
Abstract Class MbqBaseActGetVotesList extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        if(MbqMain::isJsonProtocol())
        {
            $in->optionId = $this->getInputParam('optionId');
            $in->topicId = $this->getInputParam('topicId');
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam('page'), $this->getInputParam('perPage'));
        }
        else
        {
            $in->optionId = $this->getInputParam(0);
            $in->topicId = $this->getInputParam(1);
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam(2), $this->getInputParam(3));
        }
        $in->oMbqDataPage = $oMbqDataPage;
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('user')) {
            MbqError::alert('', "Not support module user!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqAclEtPoll = MbqMain::$oClk->newObj('MbqAclEtPoll');
        $aclResult = $oMbqAclEtPoll->canAclGetVotesList();
        if ($aclResult === true) {   //acl judge
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $in->oMbqDataPage = $oMbqRdEtUser->getObjsMbqEtUser($in, array('case' => 'voted', 'oMbqDataPage' => $in->oMbqDataPage));
            $this->data['list'] = $oMbqRdEtUser->returnApiArrDataUser($in->oMbqDataPage->datas);
            $this->data['total'] = (int) $in->oMbqDataPage->totalNum;
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
  
}