<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetOnlineUsers');

/**
 * get_online_users action
 */
Class MbqActGetOnlineUsers extends MbqBaseActGetOnlineUsers {
    
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
