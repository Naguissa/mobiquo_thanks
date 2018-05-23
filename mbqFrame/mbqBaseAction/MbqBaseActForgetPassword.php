<?php

defined('MBQ_IN_IT') or exit;

/**
 * forget password
 */
Abstract Class MbqBaseActForgetPassword extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->login = $this->getInputParam('username');
            $in->token = $this->getInputParam('token');
            $in->code = $this->getInputParam('code');
        }
        else
        {
            $in->login = $this->getInputParam(0);
            $in->token = $this->getInputParam(1);
            $in->code = $this->getInputParam(2);
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
        
        $oMbqRdCommon = MbqMain::$oClk->newObj('MbqRdCommon');
        $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
        $result = false;
        $verified = false;
        $email_response = '';
        $email = '';
        $this->oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($in->login, array('case' => 'byLoginName'));
       
        if(isset($in->token) && !empty($in->token))
        {
            $apiKey = $oMbqRdCommon->getApiKey();
            $forumUrl = $oMbqRdCommon->getForumUrl();
            if(isset($apiKey) &&!empty($apiKey))
            {
                $email_response = $this->getEmailFromScription($in->token, $in->code, $apiKey, $forumUrl);
                if(empty($email_response))
                {
                    $this->data['result'] = false;
                    $this->data['result_text'] = 'Failed to connect to tapatalk server, please try again later.';
                    return;
                }
            }
            $email = (!isset($email_response['email']) || empty($email_response['email'])) ? '': $email_response['email'];
        }
        if($this->oMbqEtUser && is_a($this->oMbqEtUser,'MbqEtUser') && $this->oMbqEtUser->userId->hasSetOriValue())
        {
            $result_text = 'Validate successfully!';
            if(!empty($email) && $this->oMbqEtUser->userEmail->hasSetOriValue() && $this->oMbqEtUser->userEmail->oriValue ==  $email)
            {
                $verified = true;
                $result = true;
                if($this->oMbqEtUser->userType->hasSetOriValue() && $this->oMbqEtUser->userType->oriValue =='admin')
                {
                    $result = false;
                    $result_text = 'Sorry, you are administrator of this forum, please try to get password via browser!';
                }
            }
            else
            {
                if($this->oMbqEtUser->userType->hasSetOriValue() && $this->oMbqEtUser->userType->oriValue =='admin')
                {
                    $result = false;
                    $result_text = 'Sorry, you are administrator of this forum, please try to get password via browser!';
                }
                else
                {
                    $forgetPasswordResult = $oMbqRdEtUser->forgetPassword($this->oMbqEtUser);
                    if($forgetPasswordResult === true)
                    {
                        $result = true;
                        $result_text = "Mail with password reset sent";
                    }
                    else
                    {
                        $result = false;
                        $result_text = $forgetPasswordResult;
                    }
                }
            }
        }
        else if($this->oMbqEtUser)
        {
            $result = false;
            $result_text = $this->oMbqEtUser;
        }
        else
        {
            $result = false;
            $result_text = 'User does not exist';
        }
        $this->data['result'] = $result;
        $this->data['result_text'] = $result_text;
        $this->data['verified'] = $verified;
  
    }
    function getEmailFromScription($token, $code, $key, $boardurl)
    {
        $verification_url = 'http://directory.tapatalk.com/au_reg_verify.php?token='.$token.'&'.'code='.$code.'&key='.$key.'&url='.$boardurl;
        require_once(MBQ_3RD_LIB_PATH . 'classTTConnection.php');
        $connection = new classTTConnection();
        $connection->timeout = 10;
        $response = $connection->getContentFromSever($verification_url, array(), 'get');
        if($response)
            $result = json_decode($response, true);
        if(isset($result) && isset($result['result']))
            return $result;
        else
        {
            $data = array(
                'token' => $token,
                'code'  => $code,
                'key'   => $key,
                'url'   => $boardurl,
            );
            $response = $connection->getContentFromSever('http://directory.tapatalk.com/au_reg_verify.php', $data, 'post');
            if($response)
                $result = json_decode($response, true);
            if(isset($result) && isset($result['result']))
                return $result;
            else
                return 0; //No connection to Tapatalk Server.
        }
    }

}