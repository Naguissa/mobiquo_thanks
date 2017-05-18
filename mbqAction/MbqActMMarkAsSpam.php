<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActMMarkAsSpam');

/**
 * m_mark_as_spam action
 */
Class MbqActMMarkAsSpam extends MbqBaseActMMarkAsSpam {
    
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
