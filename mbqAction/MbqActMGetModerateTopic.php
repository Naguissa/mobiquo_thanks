<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActMGetModerateTopic');

/**
 * m_get_moderate_topic action
 */
Class MbqActMGetModerateTopic extends MbqBaseActMGetModerateTopic {
    
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
