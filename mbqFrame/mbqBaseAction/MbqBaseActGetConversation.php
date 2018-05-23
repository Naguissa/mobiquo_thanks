<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_conversation action
 */
Abstract Class MbqBaseActGetConversation extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        if(MbqMain::isJsonProtocol())
        {
            $in->convId = $this->getInputParam('conversationId');
            $in->returnHtml = (boolean) $this->getInputParam('returnHtml', true);
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam('page'), $this->getInputParam('perPage'));
        }
        else
        {
            $in->convId = $this->getInputParam(0);
            $startNum = (int) $this->getInputParam(1);
            $lastNum = (int) $this->getInputParam(2);
            $in->returnHtml = (boolean) $this->getInputParam(3);
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
        
        $oMbqRdEtPc = MbqMain::$oClk->newObj('MbqRdEtPc');
        if ($oMbqEtPc = $oMbqRdEtPc->initOMbqEtPc($in->convId, array('case' => 'byConvId'))) {
            $oMbqAclEtPc = MbqMain::$oClk->newObj('MbqAclEtPc');
            $aclRestult = $oMbqAclEtPc->canAclGetConversation($oMbqEtPc);
            if ($aclRestult === true) {    //acl judge
                $oMbqRdEtPcMsg = MbqMain::$oClk->newObj('MbqRdEtPcMsg');
                $in->oMbqDataPage = $oMbqRdEtPcMsg->getObjsMbqEtPcMsg($oMbqEtPc, array('case' => 'byPc', 'oMbqDataPage' => $in->oMbqDataPage));
                $this->data = $oMbqRdEtPc->returnApiDataPc($oMbqEtPc);
                $this->data['list'] = $oMbqRdEtPcMsg->returnApiArrDataPcMsg($in->oMbqDataPage->datas,  $in->returnHtml);
                $oMbqWrEtPc = MbqMain::$oClk->newObj('MbqWrEtPc');
                /* mark pc read */
                $oMbqWrEtPc->markPcRead($oMbqEtPc);
            } else {
                MbqError::alert('', $aclRestult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid conversation id!", '', MBQ_ERR_APP);
        }
    }
  
}