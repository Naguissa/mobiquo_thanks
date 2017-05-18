<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetUserReplyPost');

/**
 * get_user_reply_post action
 */
Class MbqActGetUserReplyPost extends MbqBaseActGetUserReplyPost {
    
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
