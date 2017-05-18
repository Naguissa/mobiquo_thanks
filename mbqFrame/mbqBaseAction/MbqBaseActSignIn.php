<?php

defined('MBQ_IN_IT') or exit;

/**
 * sign in
 */
Abstract Class MbqBaseActSignIn extends MbqBaseAct {
    public $pwdLen = 8;

    public function __construct() {
        parent::__construct();
    }
    protected $errors = array();
    protected $register = false;
    protected $verified = false;
    protected $TTProfile = array();
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->token = $this->getInputParam('token');
            $in->code = $this->getInputParam('code');
            $in->email = $this->getInputParam('email');
            $in->username = $this->getInputParam('username');
            $in->password = $this->getInputParam('password');
            $in->customRegisterFields = (array)$this->getInputParam('customRegisterFields');
            $in->trustCode = $this->getInputParam('trustCode');
        }
        else
        {
            $in->token = $this->getInputParam(0);
            $in->code = $this->getInputParam(1);
            $in->email = $this->getInputParam(2);
            $in->username = $this->getInputParam(3);
            $in->password = $this->getInputParam(4);
            $in->customRegisterFields = $this->getInputParam(5);
            $in->trustCode = $this->getInputParam(6);
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
        if (!class_exists('classTTConnection')){
            include_once(MBQ_3RD_LIB_PATH . 'classTTConnection.php');
        }
        $oMbqRdCommon = MbqMain::$oClk->newObj('MbqRdCommon');
        $check_spam = $oMbqRdCommon->getCheckSpam();
        $this->register = false;

        $this->TTVerify($in->token, $in->code);

        $result = false;
	    if($this->verified)
	    {
            $this->setUserInfo($this->TTEmail);
            if (isset($this->oMbqEtUser))
            {
                $result = $this->loginUser($in->trustCode);
            }
            else
            {
                $this->register = true;
                $this->TTVerify($in->token, $in->code);
                $this->createUser($this->TTEmail, $in->username, $in->password, $in->customRegisterFields, $check_spam);
                $result = $this->loginUser($in->trustCode);
            }
	    }
        // verify failed can still do register, the new account maybe inactive in this case
        else
        {
            $this->setUserInfo($in->email);

            if ($in->email && $in->username && empty($this->oMbqEtUser))
            {
                $this->register = true;
                $this->createUser($in->email, $in->username,  $in->password, $in->customRegisterFields, $check_spam);
                $result = $this->loginUser($in->trustCode);
            }
            else if($in->email && $in->username && $in->password && !empty($this->oMbqEtUser))
            {
                $this->errors[] = 'The account already exists, please login with your username and password.';
            }
            else
            {
                $this->errors[] = 'Authentication failed, please login with your username and password.';
            }
        }


        if($result === true)
        {
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $this->data['result'] = true;
            $this->data['register'] = $this->register;
            $data1 = $oMbqRdEtUser->returnApiDataUser(MbqMain::$oCurMbqEtUser);
            MbqMain::$oMbqCm->mergeApiData($this->data, $data1);
            $oTapatalkPush = new TapatalkPush();
            $oTapatalkPush->callMethod('doAfterAppLogin',MbqMain::$oCurMbqEtUser->userId->oriValue);
        }
        else {
            if($this->register && $this->oMbqEtUser)
            {
                $this->data['result'] = $this->oMbqEtUser != null;
                $this->data['register'] = $this->register;
                $this->data['user_type'] = $this->oMbqEtUser->userType->oriValue;
                if(isset($this->errors) && empty($this->errors)== false)
                {
                    foreach($this->errors as $key=>$value)
                    {
                        $this->data['result_text'] = (isset( $this->data['result_text']) ?  $this->data['result_text'] : '') . $value . PHP_EOL;
                    }
                }
            }
            else
            {
                $this->data['result'] = false;
                if($result == 'two-step-required')
                {
                    $this->data['two_step_required'] = true;
                    $this->data['result_text'] = "This account require Two Step verification.";
                }
                else
                {
                    $this->data['result_text'] = $result. PHP_EOL;
                    foreach($this->errors as $key=>$value)
                    {
                        $this->data['result_text'] .= $value . PHP_EOL;
                    }
                }
            }
        }
    }

    public function createUser($email, $username, $password, $custom_register_fields, $check_spam = false)
    {
        if (empty($email))
        {
            $this->status = 2;
            $this->errors[] = 'Email not provided for registration';
        }
        else if (empty($username))
        {
            $this->status = 2;
            $this->errors[] = 'Username not provided for registration';
        }
        else
        {
            $connection = new classTTConnection();
            if($check_spam && $connection->checkSpam($email))
            {
                $this->errors[] = 'Your email or IP address matches that of a known spammer and therefore you cannot register here. If you feel this is an error, please contact the administrator or try again later.';
            }
            else
            {
                $oMbqWrEtUser = MbqMain::$oClk->newObj('MbqWrEtUser');
                if($this->validateUsername($username))
                {
                    if($password = $this->validatePassword($password, $this->verified))
                    {
                        $this->oMbqEtUser = $oMbqWrEtUser->registerUser($username,  $password, $email, $this->verified, $custom_register_fields, $this->TTProfile, $this->errors);
                    }
                    else
                    {
                        $this->status = 2;
                        $this->errors[] = 'Password does not comply with the password policy set by the forum Administrator.  Please try another.';
                    }
                }
                else
                {
                    $this->status = 2;
                    $this->errors[] = 'This username already exists. Please try another.';
                }
            }
        }
    }

    public function loginUser($trustCode)
    {
        if ($this->oMbqEtUser)
        {
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $result = $oMbqRdEtUser->loginDirectly($this->oMbqEtUser, $trustCode);
            if($result !== true)
            {
                if($result == 'two-step-required')
                {
                    $this->data['two_step_required'] = true;
                }
                else
                {
                    if(is_array($result))
                    {
                        foreach($result as $key=>$value)
                        {
                            $this->errors[$key] = $value;
                        }
                    }
                    else
                    {
                        $this->errors[] .= $result;
                    }
                }
            }
            return $result;
        }
        return false;
    }

    public function setUserInfo($email, $username = '')
    {
        $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
        if ($email){
            $this->oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($email, array('case' => 'byEmail'));
        }else if ($username){
            $this->oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($username, array('case' => 'byLoginName'));
        }
    }

    public function TTVerify($token, $code)
    {
        $this->verified  = false;
        if ($token && $code)
        {
            $oMbqRdCommon = MbqMain::$oClk->newObj('MbqRdCommon');

            $connection = new classTTConnection();
            $verifyResult = $connection->signinVerify($token, $code, $oMbqRdCommon->getForumUrl(), $oMbqRdCommon->getApiKey(), $this->register);

            // get valid response
            if ($connection->success && !empty($verifyResult))
            {
                // pass verify. can register without user activate or login without password
                if (isset($verifyResult['result']) && $verifyResult['result'] && isset($verifyResult['email']) && $verifyResult['email'])
                {
                    $this->verified = true;
                    $this->TTEmail = strtolower($verifyResult['email']);
                    $this->TTProfile = isset($verifyResult['profile']) ? $verifyResult['profile'] : array();
                }
                else if (isset($verifyResult['result_text']) && $verifyResult['result_text'])
                {
                    $this->errors[] = $verifyResult['result_text'];
                }
            }
            else
            {
                if($connection->success == false)
                {
                    $this->errors = $connection->errors;
                }
                $this->errors[] = 'Tapatalk authorization verify with no response';
            }
        }
        else
        {
            if(empty($token) && empty($code))
            {
                return;
            }
            $this->errors[] = 'Invalid Tapatalk authorization data';
        }
    }

    public function validateUsername($username)
    {
        $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
        if ($oMbqRdEtUser->validateUsername($username)){
            return $username;
        }
        return false;
    }

    public function validatePassword($password = '', $need_new_password_after_fail = true)
    {
        $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
        if ($oMbqRdEtUser->validatePassword($password)){
            return $password;
        } else if ($need_new_password_after_fail){
            return $this->generatePassword();
        }
        return false;
    }

    public function generatePassword()
    {
        $str = '';
        if(is_callable('openssl_random_pseudo_bytes')){
            $str = openssl_random_pseudo_bytes(255);
        }else{
            for($i = 0; $i < 256; $i++) $str .= chr(mt_rand(0,255));
        }
        return substr(preg_replace('/[^a-zA-Z0-9]/', '', base64_encode($str)), 0, $this->pwdLen);
    }

    public function setPasswordLength($pwdLen = 8)
    {
        if ($pwdLen >=3 ) $this->pwdLen = intval($pwdLen);
    }
}