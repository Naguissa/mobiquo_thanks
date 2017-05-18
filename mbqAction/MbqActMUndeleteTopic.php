<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActMUndeleteTopic');

/**
 * m_undelete_topic action
 */
Class MbqActMUndeleteTopic extends MbqBaseActMUndeleteTopic {
    
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
