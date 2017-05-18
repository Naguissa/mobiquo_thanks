<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetSubscribedTopic');

/**
 * get_subscribed_topic action
 */
Class MbqActGetSubscribedTopic extends MbqBaseActGetSubscribedTopic {
    
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
