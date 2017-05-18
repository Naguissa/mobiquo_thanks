<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrCommon');


Class MbqWrCommon extends MbqBaseWrCommon {

    public function __construct() {
    }
    public function setApiKey($apiKey)
    {
	setTapatalkConfigValue('tapatalk_push_key', $apiKey);
        return true;
    }
    public function SetSmartbannerInfo($smartbannerInfo)
    {
        global $cache;
        $cache->put('_tapatalk_banner_control', $smartbannerInfo);
        setTapatalkConfigValue('tapatalk_banner_control',  serialize($smartbannerInfo));
        setTapatalkConfigValue('tapatalk_banner_update',  time());
        if (serialize($smartbannerInfo) == getTapatalkConfigValue('tapatalk_banner_control')){
            return true;
        }
        return false;
    }
}
