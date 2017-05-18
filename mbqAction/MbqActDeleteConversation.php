<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActDeleteConversation');

/**
 * delete_conversation action
 */
Class MbqActDeleteConversation extends MbqBaseActDeleteConversation {
    
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
