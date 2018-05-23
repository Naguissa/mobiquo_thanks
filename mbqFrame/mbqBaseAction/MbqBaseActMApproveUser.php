<?php

defined('MBQ_IN_IT') or exit;

/**
 * m_approve_user
 * Class MbqBaseActMApproveUser
 */
Abstract Class MbqBaseActMApproveUser extends MbqBaseAct
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getInput()
    {
        $in = new stdClass();
        if (MbqMain::isJsonProtocol()) {
            $in->userId = $this->getInputParam('user_id');
            $in->userName = $this->getInputParam('user_name');
            $in->mode = $this->getInputParam('mode');
        } else {
            $in->userId = $this->getInputParam(0);
            $in->userName = $this->getInputParam(1);
            $in->mode = $this->getInputParam(2);
        }
        if ($in->mode != 1 && $in->mode != 2) {
            MbqError::alert('', "Need valid mode!", '', MBQ_ERR_APP);
        }
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
        if (!isset($in->userId) || !isset($in->userName) || !isset($in->mode)) {
            MbqError::alert('', "Request param invalid!", '', MBQ_ERR_APP);
        }

        /** @var MbqRdEtUser $oMbqRdEtUser */
        $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
        /** @var MbqEtUser $oMbqEtUser */
        $oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($in->userId, array('case' => 'byUserId'));

        if (!$oMbqEtUser || $oMbqEtUser->userName->oriValue != $in->userName) {
            MbqError::alert('', "User not found!", '', MBQ_ERR_APP);
        } else {
            /** @var MbqAclEtUser $oMbqAclEtUser */
            $oMbqAclEtUser = MbqMain::$oClk->newObj('MbqAclEtUser');
            $aclResult = $oMbqAclEtUser->canAclMApproveUser($oMbqEtUser, $in->mode);
            if ($aclResult !== true) {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            } else {

                /** @var MbqWrEtUser $oMbqWrEtUser */
                $oMbqWrEtUser = MbqMain::$oClk->newObj('MbqWrEtUser');
                $result = $oMbqWrEtUser->mApproveUser($oMbqEtUser, $in->mode);
                if ($result === true) {
                    $this->data['result'] = true;
                } else if ($result === false) {
                    $this->data['result'] = false;
                    $this->data['is_login_mod'] = true;
                    $this->data['result_text'] = 'You need to authenticate again to do the action';
                } else {
                    $this->data['result'] = false;
                    $this->data['result_text'] = $result;
                }
            }
        }
    }


}