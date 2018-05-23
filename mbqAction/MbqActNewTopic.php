<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActNewTopic');

/**
 * new_topic action
 */
Class MbqActNewTopic extends MbqBaseActNewTopic {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * action implement
     */
    public function actionImplement($in) {
        return parent::actionImplement($in);
    }
  
}
