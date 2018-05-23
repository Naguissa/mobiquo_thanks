<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_user_topic action
 */
Abstract Class MbqBaseActGetUserTopic extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->username = $this->getInputParam('username');
            $in->userId = $this->getInputParam('userId');
        }
        else
        {
            $in->username = $this->getInputParam(0);
            $in->userId = $this->getInputParam(1);
        }
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('user')) {
            MbqError::alert('', "Not support module user!", '', MBQ_ERR_NOT_SUPPORT);
        }
        if (!MbqMain::$oMbqConfig->moduleIsEnable('forum')) {
            MbqError::alert('', "Not support module forum!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        $oMbqDataPage->initByStartAndLast(0, 49);
        $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
        if ($in->userId) {
            $oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($in->userId, array('case' => 'byUserId'));
        } else {
            $oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($in->username, array('case' => 'byLoginName'));
        }
        if (is_a($oMbqEtUser,'MbqEtUser')) {
            $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
            $aclResult = $oMbqAclEtForumTopic->canAclGetUserTopic();
            if ($aclResult === true) {   //acl judge
                $supportAdvanceSearch = MbqMain::$oMbqConfig->getCfg('forum.advanced_search');
                if($supportAdvanceSearch->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.forum.advanced_search.range.support'))
                {
                    $oMbqRdForumSearch = MbqMain::$oClk->newObj('MbqRdForumSearch');
                    $in->searchId = null;
                    $in->keywords = null;
                    $in->searchId = null;
                    $in->searchUser = $in->username;
                    $in->forumId = null;
                    $in->topicId = null;
                    $in->titleOnly = null;
                    $in->showPosts = null;
                    $in->searchTime = null;
                    $in->onlyIn = null;
                    $in->notIn = null;
                    $in->startedBy  = null;
                    $result = $oMbqRdForumSearch->forumAdvancedSearch($in, $oMbqDataPage, array('case' => 'search'));
                }
                else
                {
                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    $result = $oMbqRdEtForumTopic->getObjsMbqEtForumTopic($oMbqEtUser, array('case' => 'byAuthor', 'oMbqDataPage' => $oMbqDataPage));
                }
                if(is_a($result,'MbqDataPage'))
                {
                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    $this->data = $oMbqRdEtForumTopic->returnApiArrDataForumTopic($result->datas);
                }
                else
                {
                    $this->data['result_text'] = $result;
                }
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid user key!", '', MBQ_ERR_APP);
        }
    }
  
}