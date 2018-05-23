<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_forum action
 */
Abstract Class MbqBaseActGetForum extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->returnDescription = $this->getInputParam('returnHtml');
            $in->forumId = $this->getInputParam('forumId');
        }
        else
        {
            $in->returnDescription =  $this->getInputParam(0);
            $in->forumId =  $this->getInputParam(1);
        }
        if($in->returnDescription == null)
        {
            $in->returnDescription = true;
        }
        if($in->forumId == null)
        {
            $in->forumId = 0;
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
        $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
        $tree = $oMbqRdEtForum->getForumTree($in->returnDescription, $in->forumId);
        $this->data = $oMbqRdEtForum->returnApiTreeDataForum($tree);
    }
  
}