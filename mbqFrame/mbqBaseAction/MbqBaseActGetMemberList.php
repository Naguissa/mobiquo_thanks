<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_member_list
 *
 * Class MbqBaseActGetMemberList
 */
Abstract Class MbqBaseActGetMemberList extends MbqBaseAct
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getInput()
    {
        $in = new stdClass();
        /** @var MbqDataPage $oMbqDataPage */
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        if (MbqMain::isJsonProtocol()) {
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam('page'), $this->getInputParam('perPage'));
            $in->action = $this->getInputParam('action');
            $in->searchUserName = $this->getInputParam('keyword');
        } else {
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam(0), $this->getInputParam(1));
            $in->action = $this->getInputParam(2);
            $in->searchUserName = $this->getInputParam(3);
        }
        $in->oMbqDataPage = $oMbqDataPage;
        return $in;
    }

    /**
     * action implement
     *
     * @param $in
     */
    protected function actionImplement($in)
    {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('user')) {
            MbqError::alert('', "Not support module user!", '', MBQ_ERR_NOT_SUPPORT);
        }

        /** @var MbqAclEtUser $oMbqAclEtUser */
        $oMbqAclEtUser = MbqMain::$oClk->newObj('MbqAclEtUser');
        $aclResult = $oMbqAclEtUser->canAclGetMemberList();

        if ($aclResult !== true) {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        } else {

            /** @var MbqRdEtUser $oMbqRdEtUser */
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            /** @var MbqDataPage $oMbqDataPage */
            $oMbqDataPage = $oMbqRdEtUser->getObjsMbqEtUser($in,
                [
                    'case' => 'getMemberList',
                    'oMbqDataPage' => $in->oMbqDataPage,
                ]
            );

            if (!is_a($oMbqDataPage, 'MbqDataPage')) {
                $this->data['result'] = false;
            }else {
                $this->data['result'] = true;
                $this->data['member_count'] = $oMbqDataPage->totalNum;
                $this->data['list'] = $oMbqRdEtUser->returnApiArrDataUser($oMbqDataPage->datas);
            }
        }
    }


}