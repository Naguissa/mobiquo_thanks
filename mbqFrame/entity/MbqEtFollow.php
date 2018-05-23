<?php

defined('MBQ_IN_IT') or exit;

/**
 * follow class
 */
Class MbqEtFollow extends MbqBaseEntity {
    
    public $userIdFrom;    /* user id who follow to another user */
    public $userIdTo; /* user id who be followed */
    
    public function __construct() {
        parent::__construct();
        $this->userIdFrom = clone MbqMain::$simpleV;
        $this->userIdTo = clone MbqMain::$simpleV;
    }
  
}
