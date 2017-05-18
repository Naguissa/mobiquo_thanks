<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActMGetModeratePost');

/**
 * m_get_moderate_post action
 */
Class MbqActMGetModeratePost extends MbqBaseActMGetModeratePost {
    
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
