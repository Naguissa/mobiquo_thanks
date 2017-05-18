<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetIdByUrl');

/**
 * get_id_by_url action
 */
Class MbqActGetIdByUrl extends MbqBaseActGetIdByUrl {
    
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
