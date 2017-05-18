<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActSubscribeForum');

/**
 * subscribe_forum action
 */
Class MbqActSubscribeForum extends MbqBaseActSubscribeForum {
    
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
