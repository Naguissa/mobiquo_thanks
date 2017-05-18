<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetThreadByUnread');

/**
 * get_thread_by_unread action
 */
Class MbqActGetThreadByUnread extends MbqBaseActGetThreadByUnread {
    
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
