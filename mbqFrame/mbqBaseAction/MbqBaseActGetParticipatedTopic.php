<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_participated_topic action
 */
Abstract Class MbqBaseActGetParticipatedTopic extends MbqBaseAct {

    public function __construct() {
        parent::__construct();
    }

    function getInput()
    {
        $in = new stdClass();
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        if(MbqMain::isJsonProtocol())
        {
            $in->username = $this->getInputParam('username');
            $in->searchId = $this->getInputParam('searchId');
            $in->userId = $this->getInputParam('userId');
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam('page'), $this->getInputParam('perPage'));
        }
        else
        {
            $in->username = $this->getInputParam(0);
            $in->searchId = $this->getInputParam(3);
            $in->userId = $this->getInputParam(4);
            $startNum = (int) $this->getInputParam(1);
            $lastNum = (int) $this->getInputParam(2);
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

        $filter = array(
            'searchuser' => $in->username,
            'userid' => $in->userId,
            'searchid' => $in->searchId,
            'page' => $in->oMbqDataPage->curPage,
            'perpage' => $in->oMbqDataPage->numPerPage
        );
        $filter['showposts'] = 0;
        if(empty($in->userId))
        {
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            if ($oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($in->username, array('case' => 'byLoginName'))) {
                $filter['userid'] = $oMbqEtUser->userId->oriValue;
            }else{
                MbqError::alert('', "User not found!", '', MBQ_ERR_APP);
            }
        }
        $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
        $aclResult = $oMbqAclEtForumTopic->canAclGetParticipatedTopic();
        if ($aclResult === true) {    //acl judge
            $oMbqRdForumSearch = MbqMain::$oClk->newObj('MbqRdForumSearch');
            $in->oMbqDataPage = $oMbqRdForumSearch->forumAdvancedSearch($filter, $in->oMbqDataPage, array('case' => 'getParticipatedTopic', 'participated' => true));
            $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
            $this->data['result'] = true;
            $this->data['search_id'] = $in->oMbqDataPage->searchId;
            $this->data['total_topic_num'] = (int) $in->oMbqDataPage->totalNum;
            $this->data['total_unread_num'] = (int) $in->oMbqDataPage->totalUnreadNum;
            $this->data['topics'] = $oMbqRdEtForumTopic->returnApiArrDataForumTopic($in->oMbqDataPage->datas);
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
}