<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActMarkPmRead');

/**
 * mark_pm_unread action
 */
Class MbqActMarkPmRead extends MbqBaseActMarkPmRead {
    
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
