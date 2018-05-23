<?php

defined('MBQ_IN_IT') or exit;

/**
 * search_topic action
 */
Abstract Class MbqBaseActSearchTopic extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        if(MbqMain::isJsonProtocol())
        {
            $in->keywords = $this->getInputParam('keywords');
            $in->searchId = $this->getInputParam('searchId');
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam('page',1), $this->getInputParam('perPage',20));
        }
        else
        {
            $in->keywords = $this->getInputParam(0);
            $in->searchId = $this->getInputParam(3);
            $startNum = (int) $this->getInputParam(1,0);
            $lastNum = (int) $this->getInputParam(2,20);
            $oMbqDataPage->initByStartAndLast($startNum, $lastNum);
        }
        if (strlen($in->keywords) < MbqBaseFdt::getFdt('MbqFdtConfig.forum.min_search_length.default')) {
            MbqError::alert('', "Search words too short!", '', MBQ_ERR_APP);
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
      
        $filter = array(
            'keywords' => $in->keywords,
            'searchid' => $in->searchId,
            'page' => $in->oMbqDataPage->curPage,
            'perpage' => $in->oMbqDataPage->numPerPage
        );
        $filter['showposts'] = 0;
       
        $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
        $aclResult = $oMbqAclEtForumTopic->canAclSearchTopic();
        if ($aclResult === true) {    //acl judge
            $oMbqRdForumSearch = MbqMain::$oClk->newObj('MbqRdForumSearch');
            $result = $oMbqRdForumSearch->forumAdvancedSearch($filter, $in->oMbqDataPage, array('case' => 'searchTopic'));
            if(is_a($result,'MbqDataPage'))
            {
                $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                $this->data['search_id'] = $result->searchId;
                $this->data['total_topic_num'] = (int) $result->totalNum;
                $this->data['topics'] = $oMbqRdEtForumTopic->returnApiArrDataForumTopic($result->datas);
            }
            else
            {
                $this->data['total_topic_num'] = 0;
                $this->data['result_text'] = $result;
            }

           
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
  
}