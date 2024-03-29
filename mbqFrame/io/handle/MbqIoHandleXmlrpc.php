<?php

defined('MBQ_IN_IT') or exit;

include_once(MBQ_3RD_LIB_PATH . 'xmlrpc/xmlrpc.inc');
include_once(MBQ_3RD_LIB_PATH . 'xmlrpc/xmlrpcs.inc');

/**
 * io handle for xmlrpc class
 */
Class MbqIoHandleXmlrpc {

    protected $cmd;   /* action command name,must unique in all action. */
    protected $input;   /* input params array */
    protected $base64Keys;  /* array of keys that need base64 encode */

    public function __construct() {
        $this->init();
        $this->setBase64Keys();
    }

    /**
     * Get request protocol based on Content-Type
     *
     * @return string default as xmlrpc
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
            self::alert('XML-RPC: '.__METHOD__.': cannot parse request headers as $_SERVER is not populated');
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
                    self::alert('XML-RPC: '.__METHOD__.': Received from client compressed HTTP request and cannot decompress');
                }
            }
        }
        if($data != '')
        {
            $originalLocales = explode(";", setlocale(LC_ALL, 0));
            setlocale(LC_ALL, "C");

            $parsers = php_xmlrpc_decode_xml($data);
            $this->cmd = $parsers->methodname;
            $this->input = php_xmlrpc_decode(new xmlrpcval($parsers->params, 'array'));

            foreach ($originalLocales as $localeSetting) {
                if (strpos($localeSetting, "=") !== false) {
                    list ($category, $locale) = explode("=", $localeSetting);
                }
                else {
                    $category = 'LC_ALL';
                    $locale   = $localeSetting;
                }
                if(defined("$category")){
                    setlocale(constant($category), $locale);
                }
            }
            constant('LC_ALL');

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
        header('Content-Type: text/xml');
        $this->resetGlobals();
        $options = array('auto_dates', 'extension_api');
        $options['base64keys'] = $this->base64Keys;
        if(defined('MBQ_DEBUG') && MBQ_DEBUG && !empty($TT_DEBUG_ERROR))
        {
            header('TTDEBUGERROR: ' . base64_encode(gzcompress($TT_DEBUG_ERROR)));
        }
        $xmlrpcData = php_xmlrpc_encode($data, $options);
        $response = new xmlrpcresp($xmlrpcData);
        if (ob_get_length()) ob_end_clean();
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".$response->serialize('UTF-8');
        exit;
    }

    /**
     * output error/success message
     *
     * @param  String  $message
     * @param  Boolean  $result
     * @patam  Integer  $errorCode
     * @return string default as xmlrpc
     */
    public static function alert($message, $result = false, $errorCode = NULL, $error_detail='') {
        header('Content-Type: text/xml');
        self::resetGlobals();
        $result_reason = -2;
        if (is_array($error_detail) && isset($error_detail['reason'])) {
            $result_reason = $error_detail['reason'];
            if (isset($error_detail['error'])) {
                $error_detail = $error_detail['error'];
            }else{
                $error_detail = '';
            }
        }
        $response = new xmlrpcresp(new xmlrpcval(array(
            'result'        => new xmlrpcval($result, 'boolean'),
            'result_text'   => new xmlrpcval(print_r($message, true), 'base64'),
	        'error'         => new xmlrpcval($error_detail, 'base64'),
            'result_reason' => new xmlrpcval($result_reason, 'int')
        ), 'struct'));
        if (ob_get_length()) ob_end_clean();
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".$response->serialize('UTF-8');
        exit;
    }

    private function setBase64Keys() {
        $this->base64Keys = array(
            'box_name',
            'code',
            'conv_subject',
            'conv_title',
            'current_action',
            'current_activity',
            'description',
            'deleted_by_name',
            'display_text',
            'forum_name',
            'last_reply_author_name',
            'message',
            'moderated_by_name',
            'moderated_reason',
            'msg_content',
            'msg_from',
            'msg_subject',
            'name',
            'post_author_name',
            'post_content',
            'edit_reason',
            'post_content_original',
            'filename',
            'post_title',
            'prefix',
            'prefix_display_name',
            'report_reason',
            'reported_by_name',
            'result_text',
            'short_content',
            'text_body',
            'title',
            'topic_author_name',
            'topic_title',
            'user_name',
            'username',
            'value',
            'login_name',
            'display_name',
            'email',
            'user_type',
            'options',
            'DEBUG_ERROR',
            'text',
            'post_debug_raw_content'
        );

        // compatibility fix, 'delete_reason' should be string in get_config, and base64 in others
        if ($this->cmd != 'get_config')
            $this->base64Keys[] = 'delete_reason';
    }

    private static function resetGlobals() {
        if (!isset($GLOBALS['xmlrpcI4'])) {
            $GLOBALS['xmlrpcI4']='i4';
            $GLOBALS['xmlrpcInt']='int';
            $GLOBALS['xmlrpcBoolean']='boolean';
            $GLOBALS['xmlrpcDouble']='double';
            $GLOBALS['xmlrpcString']='string';
            $GLOBALS['xmlrpcDateTime']='dateTime.iso8601';
            $GLOBALS['xmlrpcBase64']='base64';
            $GLOBALS['xmlrpcArray']='array';
            $GLOBALS['xmlrpcStruct']='struct';
            $GLOBALS['xmlrpcValue']='undefined';

            $GLOBALS['xmlrpcTypes']=array(
                $GLOBALS['xmlrpcI4']       => 1,
                $GLOBALS['xmlrpcInt']      => 1,
                $GLOBALS['xmlrpcBoolean']  => 1,
                $GLOBALS['xmlrpcString']   => 1,
                $GLOBALS['xmlrpcDouble']   => 1,
                $GLOBALS['xmlrpcDateTime'] => 1,
                $GLOBALS['xmlrpcBase64']   => 1,
                $GLOBALS['xmlrpcArray']    => 2,
                $GLOBALS['xmlrpcStruct']   => 3
            );

            $GLOBALS['xmlrpc_internalencoding']='ISO-8859-1';
        }
    }
}