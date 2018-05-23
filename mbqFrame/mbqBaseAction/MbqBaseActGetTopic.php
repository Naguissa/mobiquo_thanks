<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_topic action
 */
Abstract Class MbqBaseActGetTopic extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        if(MbqMain::isJsonProtocol())
        {
            $in->forumId = $this->getInputParam('forumId');
            $in->mode = $this->getInputParam('mode');
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam('page'), $this->getInputParam('perPage'));
        }
        else
        {
            $in->forumId = $this->getInputParam(0);
            $startNum = (int) $this->getInputParam(1);
            $lastNum = (int) $this->getInputParam(2);
            $in->mode = $this->getInputParam(3);
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
       
        $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
        if ($oMbqEtForum = $oMbqRdEtForum->initOMbqEtForum($in->forumId, array('case' => 'byForumId'))) {
            $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
            $aclResult = $oMbqAclEtForumTopic->canAclGetTopic($oMbqEtForum);
            if ($aclResult === true) {    //acl judge
                switch ($in->mode) {
                    case 'TOP':     /* returns sticky topics. */
                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    $in->oMbqDataPage = $oMbqRdEtForumTopic->getObjsMbqEtForumTopic($oMbqEtForum, array('case' => 'byForum', 'oMbqDataPage' => $in->oMbqDataPage, 'top' => true));
                    $this->data = $oMbqRdEtForum->returnApiDataForum($oMbqEtForum);
                    $this->data['total_topic_num'] = (int) $in->oMbqDataPage->totalNum;
                    $this->data['topics'] = $oMbqRdEtForumTopic->returnApiArrDataForumTopic($in->oMbqDataPage->datas);
                    break;
                    case 'ANN':     /* returns "Announcement" topics. */
                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    $in->oMbqDataPage = $oMbqRdEtForumTopic->getObjsMbqEtForumTopic($oMbqEtForum, array('case' => 'byForum', 'oMbqDataPage' => $in->oMbqDataPage, 'ann' => true));
                    $this->data = $oMbqRdEtForum->returnApiDataForum($oMbqEtForum);
                    $this->data['total_topic_num'] = (int) $in->oMbqDataPage->totalNum;
                    $this->data['topics'] = $oMbqRdEtForumTopic->returnApiArrDataForumTopic($in->oMbqDataPage->datas);
                    break;
                    default:        /* returns standard topics */
                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    $in->oMbqDataPage = $oMbqRdEtForumTopic->getObjsMbqEtForumTopic($oMbqEtForum, array('case' => 'byForum', 'oMbqDataPage' => $in->oMbqDataPage, 'notIncludeTop' => true));
                    $this->data = $oMbqRdEtForum->returnApiDataForum($oMbqEtForum);
                    $this->data['total_topic_num'] = (int) $in->oMbqDataPage->totalNum;
                    $this->data['topics'] = $oMbqRdEtForumTopic->returnApiArrDataForumTopic($in->oMbqDataPage->datas);
                    break;
                }
            } else {
                if (MbqMain::hasLogin()) {
                    $reason = ['reason' => MBQ_ERR_NOT_PERMISSION];
                }else{
                    $reason = ['reason' => MBQ_ERR_LOGIN_REQUIRED];
                }
                MbqError::alert('',$aclResult, $reason, MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid forum id!", ['reason' => MBQ_ERR_DATA_NOT_FOUND, 'error' => "Need valid forum id!"], MBQ_ERR_APP);
        }
    }
  
}