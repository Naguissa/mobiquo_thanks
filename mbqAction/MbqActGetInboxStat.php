<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetInboxStat');

/**
 * get_inbox_stat action
 */
Class MbqActGetInboxStat extends MbqBaseActGetInboxStat {
    
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
