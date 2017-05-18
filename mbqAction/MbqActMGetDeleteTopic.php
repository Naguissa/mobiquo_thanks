<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActMGetDeleteTopic');

/**
 * m_get_delete_topic action
 */
Class MbqActMGetDeleteTopic extends MbqBaseActMGetDeleteTopic {
    
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
