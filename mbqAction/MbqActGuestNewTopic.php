<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGuestNewTopic');

/**
 * guest new topic
 */
Class MbqActGuestNewTopic extends MbqBaseActGuestNewTopic {
    
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
