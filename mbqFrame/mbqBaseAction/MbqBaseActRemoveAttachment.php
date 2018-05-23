<?php

defined('MBQ_IN_IT') or exit;

/**
 * remove_attachment action
 */
Abstract Class MbqBaseActRemoveAttachment extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->attId = $this->getInputParam('attachmentId');
            $in->forumId = $this->getInputParam('forumId');
            $in->groupId = $this->getInputParam('groupId');
            $in->postId = $this->getInputParam('postId');
        }
        else
        {
            $in->attId = $this->getInputParam(0);
            $in->forumId = $this->getInputParam(1);
            $in->groupId = $this->getInputParam(2);
            $in->postId = $this->getInputParam(3);
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
      
        $oMbqRdEtAtt = MbqMain::$oClk->newObj('MbqRdEtAtt');
        $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
        $oMbqEtForum = $oMbqRdEtForum->initOMbqEtForum($in->forumId, array('case' => 'byForumId'));
        if (($oMbqEtAtt = $oMbqRdEtAtt->initOMbqEtAtt($in->attId, array('case' => 'byAttId'))) && $oMbqEtForum) {
            $oMbqEtAtt->groupId->setOriValue($in->groupId);
            $oMbqAclEtAtt = MbqMain::$oClk->newObj('MbqAclEtAtt');
            $aclResult = $oMbqAclEtAtt->canAclRemoveAttachment($oMbqEtAtt, $oMbqEtForum);
            if ($aclResult === true) {   //acl judge
                $oMbqWrEtAtt = MbqMain::$oClk->newObj('MbqWrEtAtt');
                $groupId = $oMbqWrEtAtt->deleteAttachment($oMbqEtAtt, $oMbqEtForum);
                $this->data['result'] = true;
                if(isset($groupId))
                {
                    $this->data['group_id'] = $groupId;
                }
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid attachment id or forum id!", '', MBQ_ERR_APP);
        }
    }
    
}