<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetContact');

Class MbqActGetContact extends MbqBaseActGetContact {

    public function __construct() {
        parent::__construct();
    }

    /**
     * action implement
     */
    public function actionImplement($in) {
        $email = "";//get user email for $in->userId

        $this->data = array(
             'result'      => false,
             'email'      => $email
         );
    }

}
