<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetQuotePm');

/**
 * get_quote_pm action
 */
Class MbqActGetQuotePm extends MbqBaseActGetQuotePm {
    
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
