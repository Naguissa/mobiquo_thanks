<?php

defined('MBQ_IN_IT') or exit;

/**
 * thank class
 */
Class MbqEtThank extends MbqBaseEntity {
    
    public $key;    /* now only postId */
    public $userId; /* user id who thanked this */
    public $type;   /* thank forum post or other anything */
    
    public $oMbqEtUser; /* user who thanked this */
    
    public function __construct() {
        parent::__construct();
        $this->key = clone MbqMain::$simpleV;
        $this->userId = clone MbqMain::$simpleV;
        $this->type = clone MbqMain::$simpleV;
        
        $this->oMbqEtUser = NULL;
    }
  
}
