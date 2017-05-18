<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActMDeleteTopic');

/**
 * m_delete_topic action
 */
Class MbqActMDeleteTopic extends MbqBaseActMDeleteTopic {
    
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
