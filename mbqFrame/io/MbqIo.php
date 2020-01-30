<?php

defined('MBQ_IN_IT') or exit;
/**
 * input/output class
 */
Class MbqIo extends MbqBaseIo {

    protected $oHandle;    // real io data handle

    public function __construct() {
        parent::__construct();
        // identify the protocol
        $this->init();

    }

    /**
     * Get request protocol based on Content-Type
     *
     * @return string default as xmlrpcmo
     */
    protected function init() {
        if (defined('MBQ_PROTOCOL')) {
            $protocol = MBQ_PROTOCOL;
        } else {    //would be removed
            $contentType = MbqMain::$oMbqCm->getRequestHeader('Content-Type');
            switch ($contentType) {
                case 'text/xml':
                    $protocol = 'xmlrpc';
                    break;
                case 'application/json':
                    $protocol = 'json';
                    break;
                default:
                    $protocol = 'xmlrpc';
            }
        }
        if(isset($_POST['method_name']))
        {
            $ioHandleClass = 'MbqIoHandlePost';
            $protocol = 'post';
        }
        else
        {
            if ($protocol == 'xmlrpc') {
                $ioHandleClass = 'MbqIoHandleXmlrpc';
            } elseif ($protocol == 'json') {
                $ioHandleClass = 'MbqIoHandleJson';
            } elseif ($protocol == 'web') {
                $ioHandleClass = 'MbqIoHandleWeb';
            } else {
                MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.Unknown protocol.', '', MBQ_ERR_TOP_NOIO);
            }
        }
        $this->protocol = $protocol;
        $this->oHandle = MbqMain::$oClk->newObj($ioHandleClass);
        $this->cmd = $this->oHandle->getCmd();
        $this->input = $this->oHandle->getInput();
        //this pieze of code is to set a TapatalkParams in $_POST variable so if any error happens we have the input params recorded in logs, excluding password and other sensible info

        if(is_array($this->input))
        {
            $removeSensibleInfoFromInputParams = array('login'=> array(1,4), 'sign_in'=>array(1,4,6),'register'=>array(1,3,4));
            $_POST['TapatalkParamsLog'] = $this->input;
            if(in_array($this->cmd, array_keys($removeSensibleInfoFromInputParams)))
            {
                foreach($removeSensibleInfoFromInputParams[$this->cmd] as $removeParam)
                {
                    $_POST['TapatalkParamsLog'][$removeParam] = '********';
                }
            }
            if(isset($_SERVER['HTTP_USER_AGENT']))
            {
                $_POST['TapatalkParamsLog']['useragent'] = $_SERVER['HTTP_USER_AGENT'];
            }
        }
        if(function_exists('TT_logCall'))
        {
            TT_logCall($this->protocol, $this->cmd, isset($_POST['TapatalkParamsLog']) ? $_POST['TapatalkParamsLog'] : array());
        }
    }

    /**
     * intput data
     */
    public function input() {
    }

    /**
     * output data
     */
    public function output() {
        $this->oHandle->output(MbqMain::$data);
    }

    /**
     * output error/success message
     *
     * @param  String  $message
     * @param  Boolean  $result
     * @patam  Integer  $errorCode
     */
    public function alert($message, $result = false, $errorCode = NULL, $error_detail = null) {
        $this->oHandle->alert($message, $result, $errorCode,$error_detail);
    }
}
