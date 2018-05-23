<?php

defined('MBQ_IN_IT') or exit;

/**
 * alert class
 */
Class MbqEtAlert extends MbqBaseEntity {
    
    public $userId; /* Id of user who triggered this alert */
    public $username;    /* Name of user who triggered this alert */
    public $iconUrl;    /* Avatar url of user who triggered this alert */
    public $message;   /* alert message, like ('test' replied to thread 'test thread') */
    public $timestamp;   /* timestamp of alert trigger time. */
    public $contentType;    /* Alert type, like post or user or pm.  */
    public $contentId;   /* Id of the alert content (It will always be post id if the target is thread) */
    public $topicId;   /* Topic id if the target is a thread. */
    public $position;   /* For conversation only, indicate the position of the new message in conversation */
    public $isUnread;   /* Indicate if the content is unread */
    
    public function __construct() {
        parent::__construct();
        $this->userId = clone MbqMain::$simpleV;
        $this->username = clone MbqMain::$simpleV;
        $this->iconUrl = clone MbqMain::$simpleV;
        $this->message = clone MbqMain::$simpleV;
        $this->timestamp = clone MbqMain::$simpleV;
        $this->contentType = clone MbqMain::$simpleV;
        $this->contentId = clone MbqMain::$simpleV;
        $this->topicId = clone MbqMain::$simpleV;
        $this->position = clone MbqMain::$simpleV;
        $this->isUnread = clone MbqMain::$simpleV;
    }
  
}
