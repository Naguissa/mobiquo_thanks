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
    protected $TTEmail = '';
    protected $ttId = 0;
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
        $this->register = !empty($in->password);
        $result = false;
        if($this->register)
        {
            if (MbqMain::$oMbqConfig->getCfg('user.inappreg')->oriValue != MbqBaseFdt::getFdt('MbqFdtConfig.user.inappreg.range.support')) {
				MbqError::alert('', 'Registration from app disabled', '', MBQ_ERR_APP);
			}

            if(!empty($in->email))
            {
                $this->setUserInfo($in->email);
            }
            if($in->email && $in->username && $in->password && !empty($this->oMbqEtUser))
            {
                $this->errors[] = 'The account already exists, please login with your username and password.';
            }
            else
            {
                if(MbqMain::$oMbqConfig->getCfg('user.sso_signin')->oriValue == 1)
                {
                    $this->TTVerify($in->token, $in->code);
                }
                $accountIsValidated =  $this->verified && $this->TTEmail == $in->email;
                $this->createUser($in->email, $in->username, $in->password, $in->customRegisterFields, $accountIsValidated);
                $result = $this->loginUser($in->trustCode);
            }
        }
        else
        {
             $this->TTVerify($in->token, $in->code);
             if($this->verified)
             {
                 if(!empty($this->ForumUserId))
                 {
                     $this->setUserInfoById($this->ForumUserId);
                 }
                 else
                 {
                     $this->setUserInfo($this->TTEmail);
                 }
                 if(isset($this->oMbqEtUser) && ($this->oMbqEtUser->userEmail->oriValue == $this->TTEmail || sha1($this->oMbqEtUser->userEmail->oriValue) == $this->TTEmail))
                 {
                         $result = $this->loginUser($in->trustCode);
                 }
                 else
                 {
                     $this->errors[] = 'Authentication failed, please login with your username and password.';
                 }
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
                        if ($value == $result) continue;
                        $this->data['result_text'] .= $value . PHP_EOL;
                    }
                }
            }
        }
    }

    public function createUser($email, $username, $password, $custom_register_fields, $verified)
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

            $oMbqWrEtUser = MbqMain::$oClk->newObj('MbqWrEtUser');
            if($this->validateUsername($username))
            {
                if($password = $this->validatePassword($password, $verified))
                {
                    $this->oMbqEtUser = $oMbqWrEtUser->registerUser($username,  $password, $email, $verified, $custom_register_fields, $this->TTProfile, $this->errors);
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


    public function setUserInfoById($userId)
    {
        $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');

        $this->oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($userId, array('case' => 'byUserId'));
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
                    $this->ttId = isset($verifyResult['ttid']) ? $verifyResult['ttid'] : '';
                    $this->ForumUserId = isset($verifyResult['uid']) ? $verifyResult['uid'] : '';
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