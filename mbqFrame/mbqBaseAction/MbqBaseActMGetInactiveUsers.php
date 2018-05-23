<?php

defined('MBQ_IN_IT') or exit;

/**
 * m_get_inactive_users
 *
 * Class MbqBaseActMGetInactiveUsers
 */
Abstract Class MbqBaseActMGetInactiveUsers extends MbqBaseAct
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
        } else {
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam(0), $this->getInputParam(1));
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
        $aclResult = $oMbqAclEtUser->canAclMGetInactiveUsers();

        if ($aclResult !== true) {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        } else {

            /** @var MbqRdEtUser $oMbqRdEtUser */
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            /** @var MbqDataPage $oMbqDataPage */
            $oMbqDataPage = $oMbqRdEtUser->getObjsMbqEtUser($in,
                [
                    'case' => 'getInactiveUsers',
                    'oMbqDataPage' => $in->oMbqDataPage,
                ]
            );

            if (!is_a($oMbqDataPage, 'MbqDataPage')) {
                $this->data['result'] = false;
                
                if ($oMbqDataPage === false) {
                    $this->data['is_login_mod'] = true;
                    $this->data['result_text'] = 'You need to authenticate again to do the action';
                }
            }else {
                $this->data['result'] = true;
                $this->data['member_count'] = $oMbqDataPage->totalNum;
                $this->data['list'] = $oMbqRdEtUser->returnApiArrDataUser($oMbqDataPage->datas);
            }
        }

    }


}