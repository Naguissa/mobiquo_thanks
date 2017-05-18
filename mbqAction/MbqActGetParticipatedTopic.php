<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetParticipatedTopic');

/**
 * get_participated_topic action
 */
Class MbqActGetParticipatedTopic extends MbqBaseActGetParticipatedTopic {
    
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
