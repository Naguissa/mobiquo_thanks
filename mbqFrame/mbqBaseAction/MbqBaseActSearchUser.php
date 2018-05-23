<?php

defined('MBQ_IN_IT') or exit;


Abstract Class MbqBaseActSearchUser extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        if(MbqMain::isJsonProtocol())
        {
            $in->keywords = $this->getInputParam('keywords');
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam('page'), $this->getInputParam('perPage'));
        }
        else
        {
            $in->keywords = $this->getInputParam(0);
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam(1), $this->getInputParam(2));
        }
        $in->oMbqDataPage = $oMbqDataPage;
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('user')) {
            MbqError::alert('', "Not support module user!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqAclEtUser = MbqMain::$oClk->newObj('MbqAclEtUser');
        $aclResult = $oMbqAclEtUser->canAclSearchUser();
        if ($aclResult === true) {   //acl judge
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $in->oMbqDataPage = $oMbqRdEtUser->getObjsMbqEtUser($in->keywords, array('case' => 'searchByName', 'oMbqDataPage' => $in->oMbqDataPage));
            $this->data['list'] = $oMbqRdEtUser->returnApiArrDataUser($in->oMbqDataPage->datas);
            $this->data['total'] = (int) $in->oMbqDataPage->totalNum;
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
  
}