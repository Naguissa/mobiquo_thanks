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
        $code = '';
        if(isset($_COOKIE['X-TT']))
        {
            $code = trim($_COOKIE['X-TT']);
        }
        else if(isset($_SERVER['HTTP_X_TT']))
        {
            $code = trim($_SERVER['HTTP_X_TT']);
        }
        include_once(MBQ_3RD_LIB_PATH . 'classTTConnection.php');
        $connection = new classTTConnection();
        $response = $connection->actionVerification($code, 'login');
        if($connection->success && $response !== true)
        {
            if ($response === false && empty($connection->errors)){
                $this->data['result_text'] = "Unauthorized app detected.";
            }
            else
            {
                $this->data['result_text'] = "The site failed to connect to Tapatalk servers and some functions will not work properly. Please contact the forum admin to resolve this issue.";
            }
        }
        $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
        $result = $oMbqRdEtUser->login($in->login, $in->password, $in->anonymous, $in->trustCode);
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