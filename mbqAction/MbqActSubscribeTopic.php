<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActSubscribeTopic');

/**
 * subscribe_topic action
 */
Class MbqActSubscribeTopic extends MbqBaseActSubscribeTopic {
    
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
