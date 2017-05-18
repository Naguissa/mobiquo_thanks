<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActReplyPost');

/**
 * reply_post action
 */
Class MbqActReplyPost extends MbqBaseActReplyPost {
    
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
