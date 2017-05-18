<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActUnsubscribeForum');

/**
 * unsubscribe_forum action
 */
Class MbqActUnsubscribeForum extends MbqBaseActUnsubscribeForum {
    
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
