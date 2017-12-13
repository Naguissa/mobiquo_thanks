<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActSyncUser');

Class MbqActSyncUser extends MbqBaseActSyncUser {

    public function __construct() {
        parent::__construct();
    }

    /**
     * action implement
     */
    public function actionImplement($in) {
        include_once(MBQ_3RD_LIB_PATH . 'classTTCipherEncrypt.php');

        $users = explode(',', $in->userId);

        $this->data = array(
            'result'  => false,
            'encrypt' => true,
            'users'   => $users,
        );
    }

}
