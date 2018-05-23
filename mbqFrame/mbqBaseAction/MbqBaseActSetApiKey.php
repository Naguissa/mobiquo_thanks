<?php

defined('MBQ_IN_IT') or exit;

/**
 * forget password
 */
Abstract Class MbqBaseActSetApiKey extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        
        if(MbqMain::isRawPostProtocol())
        {
            $in->code = MbqMain::$input['code'];
            $in->key = MbqMain::$input['key'];
        }
        include_once(MBQ_3RD_LIB_PATH . 'classTTConnection.php');
        $connection = new classTTConnection();
        $response = $connection->actionVerification($in->code,'set_api_key');
        if(!$response)
        {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_PARAMS_ERROR);
        }
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        $oMbqWrCommon = MbqMain::$oClk->newObj('MbqWrCommon');
        $result = $oMbqWrCommon->setApikey($in->key);
        $this->data = array('result' => $result);
    }
  
}