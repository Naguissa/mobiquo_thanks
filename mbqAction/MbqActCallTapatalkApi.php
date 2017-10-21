<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActCallTapatalkApi');

/**
 * call_tapatalk_api action
 */
Class MbqActCallTapatalkApi extends MbqBaseActCallTapatalkApi {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * action implement
     */
    public function actionImplement($in) {
        parent::actionImplement($in);
    }
  
}
