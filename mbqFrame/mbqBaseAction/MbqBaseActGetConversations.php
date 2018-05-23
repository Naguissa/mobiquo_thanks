<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_conversations action
 */
Abstract Class MbqBaseActGetConversations extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        if(MbqMain::isJsonProtocol())
        {
               $oMbqDataPage->initByPageAndPerPage($this->getInputParam('page'), $this->getInputParam('perPage'));
        }
        else
        {
            $startNum = (int) $this->getInputParam(0);
            $lastNum = (int) $this->getInputParam(1);
            $oMbqDataPage->initByStartAndLast($startNum, $lastNum);
        }
        $in->oMbqDataPage = $oMbqDataPage;
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (MbqMain::$oMbqConfig->moduleIsEnable('pc') && (MbqMain::$oMbqConfig->getCfg('pc.conversation')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.pc.conversation.range.support'))) {
        } else {
            MbqError::alert('', "Not support module private conversation!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqAclEtPc = MbqMain::$oClk->newObj('MbqAclEtPc');
        $aclResult = $oMbqAclEtPc->canAclGetConversations();
        if ($aclResult === true) {    //acl judge
            $oMbqRdEtPc = MbqMain::$oClk->newObj('MbqRdEtPc');
            $in->oMbqDataPage = $oMbqRdEtPc->getObjsMbqEtPc(NULL, array('case' => 'all', 'oMbqDataPage' => $in->oMbqDataPage));
            $this->data['conversation_count'] = (int) $in->oMbqDataPage->totalNum;
            $this->data['unread_count'] = $in->oMbqDataPage->totalUnreadNum != null ? $in->oMbqDataPage->totalUnreadNum : (int) $oMbqRdEtPc->getUnreadPcNum();
            $this->data['can_upload'] = (boolean)$oMbqRdEtPc->canUpload();
            $this->data['list'] = $oMbqRdEtPc->returnApiArrDataPc($in->oMbqDataPage->datas);
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
  
}