<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetTopicByIds');

/**
 * get_topic action
 */
Class MbqActGetTopicByIds extends MbqBaseActGetTopicByIds {

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
