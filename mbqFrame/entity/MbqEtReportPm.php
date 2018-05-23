<?php

defined('MBQ_IN_IT') or exit;

/**
 * report primary message class
 */
Class MbqEtReportPm extends MbqBaseEntity {
    
    public $reportId;
    public $msgId;
    public $reportByUserId;
    public $reportTime;     /* timestamp */
    public $reason;
    
    public function __construct() {
        parent::__construct();
        $this->reportId = clone MbqMain::$simpleV;
        $this->msgId = clone MbqMain::$simpleV;
        $this->reportByUserId = clone MbqMain::$simpleV;
        $this->reportTime = clone MbqMain::$simpleV;
        $this->reason = clone MbqMain::$simpleV;
    }
  
}
