<?php

defined('MBQ_IN_IT') or exit;

/**
 * m_get_report_post action

 */
Abstract Class MbqBaseActMGetReportPost extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
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
        if (!MbqMain::$oMbqConfig->moduleIsEnable('forum')) {
            MbqError::alert('', "Not support module forum!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqAclEtForumPost = MbqMain::$oClk->newObj('MbqAclEtForumPost');
        $aclResult = $oMbqAclEtForumPost->canAclMGetModeratePost();
        if ($aclResult === true) {    //acl judge
            $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
            $in->oMbqDataPage = $oMbqRdEtForumPost->getObjsMbqEtForumPost(null, array('case' => 'reported', 'oMbqDataPage' => $in->oMbqDataPage));
            $this->data['total_report_num'] = (int) $in->oMbqDataPage->totalNum;
            $this->data['reports'] = $oMbqRdEtForumPost->returnApiArrDataForumPost($in->oMbqDataPage->datas);
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
  
}