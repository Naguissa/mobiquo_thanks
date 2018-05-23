<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_alert action
 */
Abstract Class MbqBaseActGetAlert extends MbqBaseAct {
    
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
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam(0), $this->getInputParam(1));
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
        $oMbqAclEtSocial = MbqMain::$oClk->newObj('MbqAclEtSocial');
        $aclResult = $oMbqAclEtSocial->canAclGetAlert();
        if ($aclResult === true) {    //acl judge
            $oMbqRdEtSocial = MbqMain::$oClk->newObj('MbqRdEtSocial');
            $in->oMbqDataPage = $oMbqRdEtSocial->getObjsMbqEtSocial(null, array('oMbqDataPage' => $in->oMbqDataPage,'case' => 'alert'));
            $this->data['result'] = true;
            $this->data['total'] = (int) $in->oMbqDataPage->totalNum;
            $this->data['items'] = $oMbqRdEtSocial->returnApiArrDataAlert($in->oMbqDataPage->datas);
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
  
}