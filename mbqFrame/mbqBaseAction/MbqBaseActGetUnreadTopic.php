<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_unread_topic action
 */
Abstract Class MbqBaseActGetUnreadTopic extends MbqBaseAct {
    
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
            $in->searchId = $this->getInputParam('searchId');
            $in->filters = $this->getInputParam('filters');
        }
        else
        {
            $startNum = (int) $this->getInputParam(0);
            $lastNum = (int) $this->getInputParam(1);
            $oMbqDataPage->initByStartAndLast($startNum, $lastNum);
            $in->searchId = $this->getInputParam(2);
            $in->filters = $this->getInputParam(3);
        }
        $in->oMbqDataPage= $oMbqDataPage;
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('forum')) {
            MbqError::alert('', "Not support module forum!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $filter = array(
               'searchid' =>  $in->searchId,
               'page' =>  $in->oMbqDataPage->curPage,
               'perpage' =>  $in->oMbqDataPage->numPerPage
           );
        if ($in->filters && is_array($in->filters)) {
            $filter = array_merge($filter, $in->filters);
        }
        $filter['showposts'] = 0;
        $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
        $aclResult = $oMbqAclEtForumTopic->canAclGetUnreadTopic();
        if ($aclResult === true) {    //acl judge
            $oMbqRdForumSearch = MbqMain::$oClk->newObj('MbqRdForumSearch');
            $in->oMbqDataPage = $oMbqRdForumSearch->forumAdvancedSearch($filter, $in->oMbqDataPage, array('case' => 'getUnreadTopic', 'unread' => true));
            $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
            $this->data['result'] = true;
            $this->data['total_topic_num'] = (int) $in->oMbqDataPage->totalNum;
            $this->data['topics'] = $oMbqRdEtForumTopic->returnApiArrDataForumTopic($in->oMbqDataPage->datas);
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
  
}