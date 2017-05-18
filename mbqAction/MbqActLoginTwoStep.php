<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActLoginTwoStep');

/**
 * login_two_step action
 */
Class MbqActLoginTwoStep extends MbqBaseActLoginTwoStep {
    
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