<?php

defined('MBQ_IN_IT') or exit;

/**
 * SetForumInfo
 */
Abstract Class MbqBaseActSetForumInfo extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        
        if(MbqMain::isRawPostProtocol())
        {
            $in->code = isset(MbqMain::$input['code']) ? MbqMain::$input['code'] : "";
            $in->apikey =  isset(MbqMain::$input['api_key']) ? MbqMain::$input['api_key'] : null;
            $in->banner_info = isset(MbqMain::$input['banner_info']) ? json_decode(MbqMain::$input['banner_info'], true) : null;
        }
        include_once(MBQ_3RD_LIB_PATH . 'classTTConnection.php');
        $connection = new classTTConnection();
        $response = $connection->actionVerification($in->code,'set_forum_info');
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
        $oMbqRdCommon = MbqMain::$oClk->newObj('MbqRdCommon');
        $result = false;
        if(isset($in->apikey) && !empty($in->apikey))
        {
            $result = $oMbqWrCommon->SetApiKey($in->apikey);
        }
        if(isset($in->banner_info) && !empty($in->banner_info))
        {
            if($in->banner_info === true)
            {
                $connection = new classTTConnection();
                $apiKey = $oMbqRdCommon->getApiKey();
                $boardUrl = $oMbqRdCommon->getForumUrl();
                $in->banner_info = $connection->getForumInfo($boardUrl,$apiKey);
                $result = $oMbqWrCommon->SetSmartbannerInfo($in->banner_info);
            }
            else
            {
                $result = $oMbqWrCommon->SetSmartbannerInfo($in->banner_info);
            }
        }
        $this->data = array('result' => $result, 'api_key' => $in->apikey, 'forum_info' => $in->banner_info);
    }
  
}