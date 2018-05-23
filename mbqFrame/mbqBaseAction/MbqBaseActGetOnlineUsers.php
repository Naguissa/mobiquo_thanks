<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_online_users action
 */
Abstract Class MbqBaseActGetOnlineUsers extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        if(MbqMain::isJsonProtocol())
        {
            $in->id = $this->getInputParam('id');
            $in->area = $this->getInputParam('area');
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam('page'), $this->getInputParam('perPage'));
        }
        else
        {
            $in->id = $this->getInputParam(2);
            $in->area = $this->getInputParam(3);
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
        $aclResult = $oMbqAclEtUser->canAclGetOnlineUsers();
        if ($aclResult === true) {   //acl judge
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $oMbqDataPage = $oMbqRdEtUser->getObjsMbqEtUser($in, array('case' => 'online', 'oMbqDataPage' => $in->oMbqDataPage));
            $oMbqRdEtSysStatistics = MbqMain::$oClk->newObj('MbqRdEtSysStatistics');
            $oMbqEtSysStatistics = $oMbqRdEtSysStatistics->initOMbqEtSysStatistics();
            $this->data['list'] = $oMbqRdEtUser->returnApiArrDataUser($oMbqDataPage->datas);
            $this->data['member_count'] = (int) ($oMbqEtSysStatistics->forumTotalOnline->oriValue - $oMbqEtSysStatistics->forumGuestOnline->oriValue);
            //$this->data['member_count'] = (int) count($objsMbqEtUser);
            $this->data['guest_count'] = (int) $oMbqEtSysStatistics->forumGuestOnline->oriValue;
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
    }
  
}