<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetQuoteConversation');

/**
 * get_quote_conversation action
 */
Class MbqActGetQuoteConversation extends MbqBaseActGetQuoteConversation {
    
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
