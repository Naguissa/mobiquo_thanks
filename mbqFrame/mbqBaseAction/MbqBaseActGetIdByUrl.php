<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_raw_post action
 */
Abstract Class MbqBaseActGetIdByURl extends MbqBaseAct {

    public function __construct() {
        parent::__construct();
    }

    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->url = urldecode($this->getInputParam('url'));
        }
        else
        {
            $in->url = urldecode($this->getInputParam(0));
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

        $oMbqRdCommon = MbqMain::$oClk->newObj('MbqRdCommon');
        $result = $oMbqRdCommon->get_id_by_url($in->url);
        if(is_a($result,'MbqEtForum'))
        {
            $this->data['result'] = true;
            $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
            $this->data = $oMbqRdEtForum->returnApiDataForum($result);
        }
        else if(is_a($result,'MbqEtForumTopic'))
        {
            $this->data['result'] = true;
            $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
            $this->data = $oMbqRdEtForumTopic->returnApiDataForumTopic($result);
        }
        else if(is_a($result,'MbqEtForumPost'))
        {
            $this->data['result'] = true;
            $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
            $this->data = $oMbqRdEtForumPost->returnApiDataForumPost($result);
        }
        else if(is_a($result,'MbqEtPm'))
        {
            $this->data['result'] = true;
            $oMbqRdEtPM = MbqMain::$oClk->newObj('MbqRdEtPm');
            $this->data = $oMbqRdEtPM->returnApiDataPM($result);
        }
        else if(is_a($result,'MbqEtPcMsg'))
        {
            $this->data['result'] = true;
            $oMbqRdEtPcMsg = MbqMain::$oClk->newObj('MbqRdEtPcMsg');
            $this->data = $oMbqRdEtPcMsg->returnApiDataPcMsg($result);
        }
        else if(is_a($result,'MbqEtPc'))
        {
            $this->data['result'] = true;
            $oMbqRdEtPc = MbqMain::$oClk->newObj('MbqRdEtPc');
            $this->data = $oMbqRdEtPc->returnApiDataPc($result);
        }
        else
        {
            $this->data['result'] = false;
            $this->data['result_text'] = $result;
        }
    }

}