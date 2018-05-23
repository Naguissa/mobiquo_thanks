<?php

defined('MBQ_IN_IT') or exit;

/**
 * search_post action
 */
Abstract Class MbqBaseActSearchPost extends MbqBaseAct {
    
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
        $in->oMbqDataPage = $oMbqDataPage;
        if (strlen($in->keywords) < MbqBaseFdt::getFdt('MbqFdtConfig.forum.min_search_length.default')) {
            MbqError::alert('', "Search words too short!", '', MBQ_ERR_APP);
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
       
        $filter = array(
            'keywords' => $in->keywords,
            'searchid' => $in->searchId,
            'page' => $in->oMbqDataPage->curPage,
            'perpage' => $in->oMbqDataPage->numPerPage
        );
        $filter['showposts'] = 1;
       
        $oMbqAclEtForumPost = MbqMain::$oClk->newObj('MbqAclEtForumPost');
        $aclResult = $oMbqAclEtForumPost->canAclSearchPost();
        if ($aclResult === true) {    //acl judge
            $oMbqRdForumSearch = MbqMain::$oClk->newObj('MbqRdForumSearch');
            $result = $oMbqRdForumSearch->forumAdvancedSearch($filter, $in->oMbqDataPage, array('case' => 'searchPost'));
            if(is_a($result,'MbqDataPage'))
            {
                $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                $this->data['search_id'] = $result->searchId;
                $this->data['total_post_num'] = (int) $result->totalNum;
                $this->data['posts'] = $oMbqRdEtForumPost->returnApiArrDataForumPost($result->datas);
            }
            else
            {
                $this->data['total_post_num'] = 0;
                $this->data['result_text'] = $result;
            }
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
  
}