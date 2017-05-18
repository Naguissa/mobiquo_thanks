<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetThreadByPost');

/**
 * get_thread_by_post action
 */
Class MbqActGetThreadByPost extends MbqBaseActGetThreadByPost {
    
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
