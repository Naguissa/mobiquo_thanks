<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActInviteParticipant');

/**
 * invite_participant action
 */
Class MbqActInviteParticipant extends MbqBaseActInviteParticipant {
    
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
