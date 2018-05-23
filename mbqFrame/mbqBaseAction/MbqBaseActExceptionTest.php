<?php
defined('MBQ_IN_IT') or exit;

/**
 * exception_test action
 */
Abstract Class MbqBaseActExceptionTest extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        
        if(MbqMain::isRawPostProtocol())
        {
            $in->code = MbqMain::$input['code'];
            $in->exceptionType = MbqMain::$input['exception_type'];
        }
        include_once(MBQ_3RD_LIB_PATH . 'classTTConnection.php');
        $connection = new classTTConnection();
        $response = $connection->actionVerification($in->code,'exception_test');
        if(!$response)
        {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_PARAMS_ERROR);
        }
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        switch($in->exceptionType)
        {
            case 'error':
                {
                    trigger_error('ERROR', E_USER_ERROR);
                    break;
                }
            case 'warning':
                {
                    trigger_error('WARNING', E_USER_WARNING);
                    break;
                }
            case 'notice':
                {
                    trigger_error('NOTICE', E_USER_NOTICE);
                    break;
                }
        }
    }
}