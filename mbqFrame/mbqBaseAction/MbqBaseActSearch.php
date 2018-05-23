<?php

defined('MBQ_IN_IT') or exit;

/**
 * search_topic action
 */
Abstract Class MbqBaseActSearch extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        if(MbqMain::isJsonProtocol())
        {
            $filters = $this->getInputParam('filters');
            $search_filter = $filters[0];
            $in->searchId = $this->getValue($search_filter, 'searchId');
            $in->keywords = $this->getValue($search_filter,'keywords');
            $in->userId = $this->getValue($search_filter,'userId');
            $in->searchId = $this->getValue($search_filter,'searchId');
            $in->searchUser = $this->getValue($search_filter,'searchUser');
            $in->forumId = $this->getValue($search_filter,'forumId');
            $in->topicId = $this->getValue($search_filter,'topicId');
            $in->titleOnly = $this->getValue($search_filter,'titleOnly');
            $in->showPosts = $this->getValue($search_filter,'showPosts');
            $in->searchTime = $this->getValue($search_filter,'searchTime');
            $in->onlyIn = $this->getValue($search_filter,'onlyIn');
            $in->notIn = $this->getValue($search_filter,'notIn');
            $in->startedBy  = $this->getValue($search_filter,'started_by');
            
            $page = $this->getValue($search_filter, 'page', 1);
            $perpage = $this->getValue($search_filter, 'perpage', 20);
            $oMbqDataPage->initByPageAndPerPage($page, $perpage);
            if($in->startedBy) $in->showPosts = 0;
         }
        else
        {
            $search_filter = $this->getInputParam(0);
            $in->searchId = $this->getValue($search_filter, 'searchid');
            $in->keywords = $this->getValue($search_filter,'keywords');
            $in->userId = $this->getValue($search_filter,'userid');
            $in->searchId = $this->getValue($search_filter,'searchid');
            $in->searchUser = $this->getValue($search_filter,'searchuser');
            $in->forumId = $this->getValue($search_filter,'forumid');
            $in->topicId = $this->getValue($search_filter,'threadid');
            $in->titleOnly = $this->getValue($search_filter,'titleonly');
            $in->showPosts = $this->getValue($search_filter,'showposts');
            $in->searchTime = $this->getValue($search_filter,'searchtime');
            $in->onlyIn = $this->getValue($search_filter,'only_in');
            $in->notIn = $this->getValue($search_filter,'not_in');
            $in->startedBy  = $this->getValue($search_filter,'started_by');
            if($in->startedBy) $in->showPosts = 0;
            
            $page = $this->getValue($search_filter, 'page', 1);
            $perpage = $this->getValue($search_filter, 'perpage', 20);
            $oMbqDataPage->initByPageAndPerPage($page, $perpage);
        }
        if (isset($in->keywords) && strlen($in->keywords) < MbqBaseFdt::getFdt('MbqFdtConfig.forum.min_search_length.default')) {
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
        
        $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
        $aclResult = $oMbqAclEtForumTopic->canAclSearchTopic();
        if ($aclResult) {    //acl judge
            $oMbqRdForumSearch = MbqMain::$oClk->newObj('MbqRdForumSearch');
            $result = $oMbqRdForumSearch->forumAdvancedSearch($in, $in->oMbqDataPage, array('case' => 'search'));
            if(is_a($result,'MbqDataPage'))
            {
                if($in->showPosts)
                {
                    $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                    $this->data['search_id'] = $result->searchId;
                    $this->data['posts'] = $oMbqRdEtForumPost->returnApiArrDataForumPost($result->datas);
                    $this->data['total_post_num'] = (int) $result->totalNum;
                }
                else
                {
                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    $this->data['search_id'] = $result->searchId;
                    $this->data['topics'] = $oMbqRdEtForumTopic->returnApiArrDataForumTopic($result->datas);
                    $this->data['total_topic_num'] = (int) $result->totalNum;
                }
            }
            else
            {
                if($in->showPosts)
                {
                    $this->data['total_post_num'] = 0;
                }
                else
                {
                    $this->data['total_topic_num'] = 0;
                    
                }
                $this->data['result_text'] = $result;
            }
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
  
}