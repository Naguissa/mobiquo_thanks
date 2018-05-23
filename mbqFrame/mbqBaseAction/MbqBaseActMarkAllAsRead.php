<?php

defined('MBQ_IN_IT') or exit;

/**
 * mark_all_as_read action
 */
Abstract Class MbqBaseActMarkAllAsRead extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->forumId = $this->getInputParam('forumId');
        }
        else
        {
            $in->forumId = $this->getInputParam(0);
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
        $oMbqAclEtForum = MbqMain::$oClk->newObj('MbqAclEtForum');
        $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
        $oMbqWrEtForum = MbqMain::$oClk->newObj('MbqWrEtForum');
        if (isset($in->forumId) && !empty($in->forumId)){
            if ($oMbqEtForum = $oMbqRdEtForum->initOMbqEtForum($in->forumId, array('case' => 'byForumId'))) {
                $aclResult = $oMbqAclEtForum->canAclMarkAllAsRead($oMbqEtForum);
                if ($aclResult === true) {    
                    $oMbqWrEtForum->markForumRead($oMbqEtForum);
                }
                else {
                    MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
                }
            }
        }
        else
        {
            if ($oMbqAclEtForum->canAclMarkAllAsRead(null)) {    
                $oMbqWrEtForum->markForumRead(null);
            }
            else {
                MbqError::alert('', '', '', MBQ_ERR_APP);
            }
        }
        $this->data['result'] = true;
    }
}