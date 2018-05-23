<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_ignored_users action
 */
Abstract Class MbqBaseActGetIgnoredUsers extends MbqBaseAct {

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
        if (!MbqMain::$oMbqConfig->moduleIsEnable('user')) {
            MbqError::alert('', "Not support module user!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqAclEtUser = MbqMain::$oClk->newObj('MbqAclEtUser');
        $aclResult = $oMbqAclEtUser->canAclGetIgnoredUsers();
        if ($aclResult === true) {   //acl judge
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $oMbqDataPage = $oMbqRdEtUser->getObjsMbqEtUser($in, array('case' => 'ignored', 'oMbqDataPage' => $in->oMbqDataPage));
            $this->data['list'] = $oMbqRdEtUser->returnApiArrDataUser($oMbqDataPage->datas);
            $this->data['total'] = (int) $oMbqDataPage->totalNum;
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }

}