<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetMemberList');


Class MbqActGetMemberList extends MbqBaseActGetMemberList {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * action implement
     *
     * @param $in
     */
    public function actionImplement($in) {
        parent::actionImplement($in);
    }
  
}
