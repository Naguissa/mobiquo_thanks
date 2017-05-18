<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActMarkPmUnread');

/**
 * mark_pm_unread action
 */
Class MbqActMarkPmUnread extends MbqBaseActMarkPmUnread {
    
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
