<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActMGetDeletePost');

/**
 * m_get_delete_post action
 */
Class MbqActMGetDeletePost extends MbqBaseActMGetDeletePost {
    
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
