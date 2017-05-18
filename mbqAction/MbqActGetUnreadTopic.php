<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetUnreadTopic');

/**
 * get_unread_topic action
 */
Class MbqActGetUnreadTopic extends MbqBaseActGetUnreadTopic {
    
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
