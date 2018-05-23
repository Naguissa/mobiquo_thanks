<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_user_topic action
 */
Abstract Class MbqBaseActGetTopicByIds extends MbqBaseAct {

    public function __construct() {
        parent::__construct();
    }

    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->topidIds = $this->getInputParam('topicIds');
        }
        else
        {
            $in->topidIds = $this->getInputParam(0);
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
        $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
        $aclResult = $oMbqAclEtForumTopic->canAclGetTopicByIds();
        if ($aclResult === true) {   //acl judge
            $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
            $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
            $result = $oMbqRdEtForumTopic->getObjsMbqEtForumTopic($in->topidIds, array('case' => 'byTopicIds', 'oMbqDataPage' => $oMbqDataPage));
            if(is_a($result,'MbqDataPage'))
            {
                $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                $this->data['result'] = true;
                $this->data['total_topic_num'] = (int) $result->totalNum;
                $this->data['topics'] =  $oMbqRdEtForumTopic->returnApiArrDataForumTopic($result->datas);
            }
            else
            {
                $this->data['result_text'] = $result;
            }
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
}