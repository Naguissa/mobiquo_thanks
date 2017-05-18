<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActRemoveAttachment');

/**
 * remove_attachment action
 */
Class MbqActRemoveAttachment extends MbqBaseActRemoveAttachment {
    
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
