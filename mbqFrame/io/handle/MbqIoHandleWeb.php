<?php

defined('MBQ_IN_IT') or exit;

/**
 * io handle for Web pages class
 */
Class MbqIoHandleWeb {
    
    protected $cmd;   /* action command name,must unique in all action. */
    protected $input;   /* input params array */
    
    public function __construct() {
        $this->init();
    }
    
    /**
     * Get request protocol based on Content-Type
     *
     * @return string default as json
     */
    protected function init() {
        $this->cmd = isset($_POST['method_name']) ? $_POST['method_name'] : null;
        $this->input = $_POST;
    }
    
    
    /**
     * return convert stdClass object to Array
     *
     * @return array
     */
    public function objectToArray($data) {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        
        if (is_array($data)) {
            return array_map(__FUNCTION__, $data);
        } else {
            return $data;
        }
    }
    
    /**
     * return current command
     *
     * @return string
     */
    public function getCmd() {
        return $this->cmd;
    }
    
    /**
     * return current input
     *
     * @return array
     */
    public function getInput() {
        return $this->input;
    }
    
    public function output(&$data) {
        exit;
    }
    
    /**
     * output error message
     *
     * @return string default as json
     */
    public static function alert($message, $result = false) {
        $format = 'json';
        if(isset($_POST['format']))
        {
            $format = trim($_POST['format']);
        }
        else
        {
            $format = MBQ_PROTOCOL;
        }
         $response = array(
            'result'        => $result,
            'result_text'   => $message,
        );
     
        require_once('MbqIoHandleJson.php');
        MbqIoHandleJson::alert($message, $result);
        exit;
    }
    
  
}