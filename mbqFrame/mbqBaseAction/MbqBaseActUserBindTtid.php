<?php

defined('MBQ_IN_IT') or exit;

/**
 * sign in
 */
Abstract Class MbqBaseActUserBindTtid extends MbqBaseAct {
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
        }
        else
        {
            $in->token = $this->getInputParam(0);
            $in->code = $this->getInputParam(1);
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

        $this->data['result'] = false;
        $this->data['result_text'] = '';

        if (!$in->token || !$in->code) {

            $this->errors[] = 'Invalid request.';

        }else{

            $loginUser = MbqMain::$oCurMbqEtUser;
            if (!$loginUser || !$loginUser->userId->oriValue) {

                $this->errors[] = 'Please login.';

            }else{

                $this->register = false;

                $this->TTVerify($in->token, $in->code);
                if(!$this->verified)
                {
                    if (!$this->errors) {
                        $this->data['result_text'] = 'Authentication failed.';
                    }

                }else{
                    /** @var MbqRdEtUser $oMbqRdEtUser */
                    $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
                    $ttid_provider = $oMbqRdEtUser::getTtIdProvider();
                    $rst = [
                        'message' => 'fail',
                        'status' => false
                    ];
                    if ($ttid_provider) {
                        $rst = $ttid_provider->userBindTtId($this->ttId, $loginUser->userId->oriValue);
                    }

                    $this->data['result_text'] = $rst['message'];
                    $this->data['result'] = $rst['status'];
                }

            }
        }

        if ($this->errors) {
            foreach ($this->errors as $v) {
                $this->data['result_text'].= $v . PHP_EOL;
            }
        }

    }

    protected function TTVerify($token, $code)
    {
        $this->verified  = false;
        if ($token && $code)
        {
            /** @var MbqRdCommon $oMbqRdCommon */
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


}