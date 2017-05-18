<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActPrefetchAccount');

/**
 * prefetch account
 */
Class MbqActPrefetchAccount extends MbqBaseActPrefetchAccount {
    
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
