<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_online_users action
 */
Abstract Class MbqBaseActGetRecommendedUser extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        if(MbqMain::isJsonProtocol())
        {
            $in->mode = $this->getInputParam('mode');
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam('page'), $this->getInputParam('perPage'));
        }
        else
        {
            $in->mode = $this->getInputParam(2);
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam(0), $this->getInputParam(1));
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
        $aclResult = $oMbqAclEtUser->canAclGetRecommendedUser();
        if ($aclResult === true) {   //acl judge
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $in->oMbqDataPage = $oMbqRdEtUser->getObjsMbqEtUser($in->mode, array('case' => 'recommended', 'oMbqDataPage' => $in->oMbqDataPage));
            $this->data['list'] = $oMbqRdEtUser->returnApiArrDataUser($in->oMbqDataPage->datas);
            $this->data['total'] = (int) $in->oMbqDataPage->totalNum;
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
  
}