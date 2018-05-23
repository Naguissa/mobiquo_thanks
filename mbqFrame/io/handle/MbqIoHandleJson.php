<?php

defined('MBQ_IN_IT') or exit;

/**
 * io handle for Json class
 */
Class MbqIoHandleJson {

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
        $ver = phpversion();
        if ($ver[0] >= 5) {
            $data = file_get_contents('php://input');
        } else {
            $data = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
        }

        if (count($_SERVER) == 0)
        {
            self::alert('JSON: '.__METHOD__.': cannot parse request headers as $_SERVER is not populated');
        }

        if(isset($_SERVER['HTTP_CONTENT_ENCODING'])) {
            $content_encoding = str_replace('x-', '', $_SERVER['HTTP_CONTENT_ENCODING']);
        } else {
            $content_encoding = '';
        }

        if($content_encoding != '' && strlen($data)) {
            if($content_encoding == 'deflate' || $content_encoding == 'gzip') {
                // if decoding works, use it. else assume data wasn't gzencoded
                if(function_exists('gzinflate')) {
                    if ($content_encoding == 'deflate' && $degzdata = @gzuncompress($data)) {
                        $data = $degzdata;
                    } elseif ($degzdata = @gzinflate(substr($data, 10))) {
                        $data = $degzdata;
                    }
                } else {
                    self::alert('JSON: '.__METHOD__.': Received from client compressed HTTP request and cannot decompress');
                }
            }
        }
        if(isset($_GET['method_name']))
        {
            $this->cmd = $_GET['method_name'];
        }
        else if(isset($_POST['method_name']))
        {
            $this->cmd = $_POST['method_name'];
        }

        $this->input = json_decode($data);
        if(empty($this->cmd) && isset($this->input->method_name))
        {
            $this->cmd = $this->input->method_name;
        }
        $this->input = (object)array_merge((array) $this->input, $_GET);
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
        global $TT_DEBUG_ERROR;
        header('Content-Type: application/json');
        if (ob_get_length()) ob_end_clean();
        if(defined('MBQ_DEBUG') && MBQ_DEBUG && !empty($TT_DEBUG_ERROR))
        {
            header('TTDEBUGERROR: ' . base64_encode($TT_DEBUG_ERROR));
        }
        if(!empty($data))
        {
            if(MBQ_DEBUG)
            {
                echo json_encode($data, JSON_PRETTY_PRINT);
            }
            else
            {
                echo json_encode($data);
            }
        }
        exit;
    }

    /**
     * output error message
     *
     * @return string default as json
     */
    public static function alert($message, $result = false, $error_detail='') {
        header('Content-Type: application/json');
        $response = array(
            'result'        => $result,
            'result_text'   => $message,
            'error'   => $message,
        );
        if (is_array($error_detail)) {
            if (isset($error_detail['reason'])) {
                $response['reason'] = $error_detail['reason'];
            }
        }

        echo json_encode($response);
        exit;
    }
}