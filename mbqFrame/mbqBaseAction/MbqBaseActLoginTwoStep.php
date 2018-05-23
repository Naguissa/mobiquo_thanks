<?php

defined('MBQ_IN_IT') or exit;

/**
 * login_two_step action
 */
Abstract Class MbqBaseActLoginTwoStep extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->twoStepCode = $this->getInputParam('twoStepCode');
            $in->trust = $this->getInputParam('trust');
        }
        else
        {
            $in->twoStepCode = $this->getInputParam(0);
            $in->trust = $this->getInputParam(1);
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
        $trustCode = "";
        $result = $oMbqRdEtUser->loginTwoStep($in->twoStepCode, $in->trust, $trustCode);
        if ($result === true) {
            $this->data['result'] = true;
            $data1 = $oMbqRdEtUser->returnApiDataUser(MbqMain::$oCurMbqEtUser);
            MbqMain::$oMbqCm->mergeApiData($this->data, $data1);
            if(!empty($trustCode))
            {
                $this->data['trust_code'] = $trustCode;
            }
            $oTapatalkPush = new TapatalkPush();
            $oTapatalkPush->callMethod('doAfterAppLogin',MbqMain::$oCurMbqEtUser->userId->oriValue);
        } else {
            $this->data['result'] = false;
            $this->data['result_text'] = $result;
        }
    }
}