<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActMarkAllAsRead');

/**
 * mark_all_as_read action
 */
Class MbqActMarkAllAsRead extends MbqBaseActMarkAllAsRead {
    
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
