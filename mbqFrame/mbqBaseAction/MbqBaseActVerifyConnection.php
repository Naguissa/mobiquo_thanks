<?php

defined('MBQ_IN_IT') or exit;

/**
 * forget password
 */
Abstract Class MbqBaseActVerifyConnection extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        
        if(MbqMain::isRawPostProtocol())
        {
            $in->type = !empty(MbqMain::$input['type']) ? MbqMain::$input['type'] : "both";
            $in->code = MbqMain::$input['code'];
        }
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        require_once(MBQ_3RD_LIB_PATH . 'classTTConnection.php');
        $ttConnection = new classTTConnection();
        $this->data = $ttConnection->verify_connection($in->type, $in->code);
    }
  
}