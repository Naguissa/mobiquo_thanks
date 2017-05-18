<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetSubscribedForum');

/**
 * get_subscribed_forum action
 */
Class MbqActGetSubscribedForum extends MbqBaseActGetSubscribedForum {
    
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
