<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetIgnoredUsers');

/**
 * get_ignored_users action
 */
Class MbqActGetIgnoredUsers extends MbqBaseActGetIgnoredUsers {
    
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
