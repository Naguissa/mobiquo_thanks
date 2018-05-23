<?php

defined('MBQ_IN_IT') or exit;

/**
 * io handle for POST class
 */
Class MbqIoHandlePost {
    
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
        $this->cmd = $_POST['method_name'];
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
        $format = 'json';
        if(isset($_POST['format']))
        {
            $format = trim($_POST['format']);
        }
      
        switch($format)
        {
            case 'json':
                {
                    require_once('MbqIoHandleJson.php');
                    $mbqIoHandleJson = new MbqIoHandleJson();
                    $mbqIoHandleJson->output($data);
                    break;
                }
            case 'serialize':
                {
                    header('Content-type: text/plain');
                    echo serialize($data);
                    break;
                }
            case 'xmlrpc':
                {
                    require_once('MbqIoHandleXmlrpc.php');
                    $mbqIoHandleXmlrpc = new MbqIoHandleXmlrpc();
                    $mbqIoHandleXmlrpc->output($data);
                    break;
                }
           default:
                {
                    echo $data;
                    break;
                }
        }
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
         switch($format)
         {
             case 'json':
                 {
                     require_once('MbqIoHandleJson.php');
                     MbqIoHandleJson::alert($message, $result);
                     break;
                 }
             case 'serialize':
                 {
                     header('Content-type: text/plain');
                     echo serialize($response);
                     break;
                 }
             case 'xmlrpc':
                 {
                     require_once('MbqIoHandleXmlrpc.php');
                     MbqIoHandleXmlrpc::alert($message, $result);
                     break;
                 }
             default:
                 {
                     echo $format . ' is not valid format';
                     break;
                 }
         }
        exit;
    }
    
  
}