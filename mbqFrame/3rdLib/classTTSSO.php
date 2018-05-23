<?php

interface TTSSOForumInterface
{
    // return user info array, including key 'email', 'id', etc.
    public function getUserByEmail($email);
    public function getUserByName($username);

    // the response should be bool to indicate if the username meet the forum requirement
    public function validateUsernameHandle($username);

    // the response should be bool to indicate if the password meet the forum requirement
    public function validatePasswordHandle($password);

    // create a user, $verified indicate if it need user activation
    public function createUserHandle($email, $username, $password, $verified, $custom_register_fields, $profile, &$errors);

    // login to an existing user, return result as bool
    public function loginUserHandle($userInfo, $register);

    // return forum api key
    public function getAPIKey();

    // return forum url
    public function getForumUrl();

    // email obtain from userInfo for compared with TTEmail
    public function getEmailByUserInfo($userInfo);
}

class TTSSOBase
{
    public $result      = false;
    public $register    = 0;
    public $verified    = false;
    public $TTEmail     = '';//note: this email has been convert to lowercase
    public $TTProfile   = array();
    public $userInfo    = array();
    public $errors      = array();
    public $pwdLen      = 8;
    public $status      = 0;
    public $forumInterface;

    public function __construct($forumInterface){
        if (!($forumInterface instanceof TTSSOForumInterface)) {
            return null;
        }
        $this->forumInterface = $forumInterface;
    }

    public function signIn($params)
    {
        $token    = isset($params['token'])? $params['token'] : (isset($params[0]) ? $params[0] : '');
        $code     = isset($params['code'])? $params['code'] : (isset($params[1]) ? $params[1] : '');
        $email    = isset($params['email'])? $params['email'] : (isset($params[2]) ? strtolower($params[2]) : '');
        $username = isset($params['username'])? $params['username'] : (isset($params[3]) ? $params[3] : '');
        $password = isset($params['password'])? $params['password'] : (isset($params[4]) ? $params[4] : '');
        $custom_register_fields  = isset($params['custom_register_fields'])? $params['custom_register_fields'] : (isset($params[5]) ? $params[5] : '');

        $this->TTVerify($token, $code);

        if ($this->verified)
        {
            $this->setUserInfo($this->TTEmail);

            if ($this->userInfo) // user exists, do login
            {
                $this->loginUser();
            }
            else
            {
                $this->register = 1;
                $this->createUser($this->TTEmail, $username, $password, $custom_register_fields);
                $this->loginUser();
            }
        }
        // verify failed can still do register, the new account maybe inactive in this case
        else
        {
            $this->setUserInfo($email);

            if ($email && $username && empty($this->userInfo))
            {
                $this->register = 1;
                $this->createUser($email, $username, $password, $custom_register_fields);
                $this->loginUser();
            }
            else if($email && $username && $password && !empty($this->userInfo))
            {
                $this->errors[] = 'The account already exists, please login with your username and password.';
            }
            else
            {
                $this->errors[] = 'Authentication failed, please login with your username and password.';
            }
        }
    }

    public function createUser($email, $username, $password, $custom_register_fields)
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

            if($this->validateUsername($username))
            {
                if($password = $this->validatePassword($password, $this->verified))
                {
                    $this->userInfo = $this->forumInterface->createUserHandle($email, $username, $password, $this->verified, $custom_register_fields, $this->TTProfile, $this->errors);
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

    public function loginUser()
    {
        if ($this->userInfo)
        {
            $this->result = $this->forumInterface->loginUserHandle($this->userInfo, $this->register);
        }
    }

    public function setUserInfo($email, $username = '')
    {
        if ($email){
            $this->userInfo = $this->forumInterface->getUserByEmail($email);
        }else if ($username){
            $this->userInfo = $this->forumInterface->getUserByName($username);
        }
    }

    public function TTVerify($token, $code)
    {
        if ($token && $code)
        {
            $connection = new classTTConnection();
            $verifyResult = $connection->signinVerify($token, $code, $this->forumInterface->getForumUrl(), $this->forumInterface->getAPIKey(), $this->register);

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
        if ($this->forumInterface->validateUsernameHandle($username)){
            return $username;
        }
        return false;
    }

    public function validatePassword($password = '', $need_new_password_after_fail = true)
    {
        if ($this->forumInterface->validatePasswordHandle($password)){
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