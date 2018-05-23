<?php

defined('MBQ_IN_IT') or exit;

/**
 * update password
 */
Abstract Class MbqBaseActUpdatePassword extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    protected $errors = array();
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->oldPassword = $this->getInputParam('oldPassword');
            $in->newPassword = $this->getInputParam('newPassword');
            $in->token = $this->getInputParam('token');
            $in->code = $this->getInputParam('code');
        }
        else
        {
            if(sizeof(MbqMain::$input) == 3)
            {
                $in->newPassword = $this->getInputParam(0);
                $in->token = $this->getInputParam(1);
                $in->code = $this->getInputParam(2);
            }
            else
            {
                $in->oldPassword = $this->getInputParam(0);
                $in->newPassword = $this->getInputParam(1);
            }
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
       
        $oMbqAclEtUser = MbqMain::$oClk->newObj('MbqAclEtUser');
        $aclResult = $oMbqAclEtUser->canAclUpdatePassword();
        if ($aclResult === true || isset($in->code) && isset($in->token) && !isset($in->oldPassword)) {
            $oMbqRdCommon = MbqMain::$oClk->newObj('MbqRdCommon');
            $oMbqWrEtUser = MbqMain::$oClk->newObj('MbqWrEtUser');
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $result = false;
            if(isset($in->oldPassword) && !isset($in->code)&& !isset($in->token))
            {
                $result = $oMbqWrEtUser->updatePassword($in->oldPassword, $in->newPassword);
            }
            else
            {
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
                    $this->oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($email, array('case' => 'byEmail'));
                }
                if(isset($this->oMbqEtUser) && $this->oMbqEtUser->userId->hasSetOriValue())
                {
                    $result = $oMbqWrEtUser->updatePasswordDirectly($this->oMbqEtUser, $in->newPassword);
                }
            }
            if($result === true)
            {
                $this->data['result'] = true;
                $this->data['result_text'] = 'Password changed';
            }
            else
            {
                $this->data['result'] = false;
                $this->data['result_text'] = $result;
            }
        } else {
            MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
        }
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