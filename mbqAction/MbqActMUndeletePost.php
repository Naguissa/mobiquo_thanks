<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActMUndeletePost');

/**
 * m_undelete_post action
 */
Class MbqActMUndeletePost extends MbqBaseActMUndeletePost {
    
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
