<?php

defined('MBQ_IN_IT') or exit;

/**
 * like class
 */
Class MbqEtLike extends MbqBaseEntity {
    /** @var  MbqValue */
    public $key;    /* topicId or postId */
    /** @var  MbqValue */
    public $userId; /* user id who liked this */
    /** @var  MbqValue */
    public $type;   /* like forum topic/post or other anything */
    /** @var  MbqValue */
    public $postTime;   /* timestamp */
    /** @var MbqEtUser */
    public $oMbqEtUser; /* user who like this */
    
    public function __construct() {
        parent::__construct();
        $this->key = clone MbqMain::$simpleV;
        $this->userId = clone MbqMain::$simpleV;
        $this->type = clone MbqMain::$simpleV;
        $this->postTime = clone MbqMain::$simpleV;
        
        $this->oMbqEtUser = NULL;
    }
  
}
