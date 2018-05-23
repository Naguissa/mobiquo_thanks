<?php

defined('MBQ_IN_IT') or exit;

/**
 * call_tapatalk_api action
 */
Abstract Class MbqBaseActCallTapatalkApi extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->apiUrl = $this->getInputParam('apiUrl');
        }
        else
        {
            $in->apiUrl = $this->getInputParam(0);
        }
        $host = parse_url ($in->apiUrl, PHP_URL_HOST);
        $hostSplit = explode('.', $host);
        if(sizeof($hostSplit) == 2)
        {
            if($hostSplit[0] != 'tapatalk' && $hostSplit[1] != 'com')
            {
                 MbqError::alert('', "This method require that url be *.tapatalk.com", '', MBQ_ERR_NOT_SUPPORT);
            }
        }
        else if(sizeof($hostSplit) == 3)
        {
            if($hostSplit[1] != 'tapatalk' && $hostSplit[2] != 'com')
            {
                 MbqError::alert('', "This method require that url be *.tapatalk.com", '', MBQ_ERR_NOT_SUPPORT);
            }
        }
        else
        {
             MbqError::alert('', "This method require that url be *.tapatalk.com", '', MBQ_ERR_NOT_SUPPORT);
        }
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (!MbqMain::hasLogin()) {
            MbqError::alert('', "This method is only for logged users", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqRdCommon = MbqMain::$oClk->newObj('MbqRdCommon');
        $apiKey = $oMbqRdCommon->getApiKey();
        $userId = MbqMain::$oCurMbqEtUser->userId->oriValue;
        $requestData['forumapi_key'] = $apiKey;
        $requestData['forumapi_uid'] = $userId;
        $inputParams = $this->getAllInputParams();
        foreach($inputParams as $key => $value)
        {
            if($key == 'vars' || $key == 'method_name' || $key == 'apiUrl' || $key=="0")
            {
                continue;
            }
            $requestData[$key] = $value;
        }
        if (!class_exists('classTTConnection')){
            require_once(MBQ_3RD_LIB_PATH.'classTTConnection.php');
        }
        $connection = new classTTConnection();
        $result = $connection->getContentFromSever($in->apiUrl, $requestData);
        $this->data = json_decode($result);
    }
}