<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActMRenameTopic');

/**
 * m_rename_topic action
 */
Class MbqActMRenameTopic extends MbqBaseActMRenameTopic {
    
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
