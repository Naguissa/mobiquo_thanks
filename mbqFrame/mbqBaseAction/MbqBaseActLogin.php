<?php

defined('MBQ_IN_IT') or exit;

/**
 * login action
 */
Abstract Class MbqBaseActLogin extends MbqBaseAct {

    public function __construct() {
        parent::__construct();
    }

    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->login = $this->getInputParam('login');
            $in->password = $this->getInputParam('password');
            $in->anonymous = $this->getInputParam('anonymous');
            $in->push = $this->getInputParam('push');
            $in->trustCode = $this->getInputParam('trustCode');
        }
        else
        {
            $in->login = $this->getInputParam(0);
            $in->password = $this->getInputParam(1);
            $in->anonymous = $this->getInputParam(2);
            $in->push = $this->getInputParam(3);
            $in->trustCode = $this->getInputParam(4);
        }
        return $in;
    }

    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('user')) {
            MbqError::alert('', "Not support module user!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
        if($in->login == null && $in->password == null && MbqMain::$oCurMbqEtUser != null)
		{
			$result = true;
		}
		else
		{
			$result = $oMbqRdEtUser->login($in->login, $in->password, $in->anonymous, $in->trustCode);
		}
        if ($result === true) {
            $this->data['result'] = true;
            $data1 = $oMbqRdEtUser->returnApiDataUser(MbqMain::$oCurMbqEtUser);
            MbqMain::$oMbqCm->mergeApiData($this->data, $data1);
            $oTapatalkPush = new TapatalkPush();
            $oTapatalkPush->callMethod('doAfterAppLogin',MbqMain::$oCurMbqEtUser->userId->oriValue);
        } else {
            $this->data['result'] = false;
            if($result == 'two-step-required')
            {
                $this->data['two_step_required'] = true;
            }
            else
            {
                $this->data['result_text'] = $result;
            }
            if ($oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($in->login, array('case' => 'byLoginName')))
            {
                $this->data['user_type'] = $oMbqEtUser->userType->oriValue;
            }
            else
            {
                $this->data['status'] = (string) 2; //!!! attention the (string)
            }
        }
    }

}