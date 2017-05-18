<?php
function TT_InitAccessLog()
{
    global $tapatalk_access_log;
    $logPath = MBQ_PATH . 'log';
    if(!empty($logPath) && is_writable($logPath) && file_exists(MBQ_PATH . 'log.on'))
    {
        include_once(MBQ_3RD_LIB_PATH .'KLogger.php');
        $tapatalk_access_log = new KLogger($logPath, KLogger::INFO, 'access_log_' . @date('Y-m-d') . '.txt');
    }
}
function TT_InitErrorLog()
{
    global $tapatalk_error_log, $tapatalk_old_error_handler, $tapatalk_old_exception_handler;
    $logPath = MBQ_PATH . 'log';
    if(!empty($logPath) && is_writable($logPath) && file_exists(MBQ_PATH . 'debug.on'))
    {
        include_once(MBQ_3RD_LIB_PATH .'KLogger.php');
        $tapatalk_error_log = new KLogger($logPath, KLogger::INFO, 'error_log_' . @date('Y-m-d') . '.txt');
        if($tapatalk_error_log instanceof KLogger && MBQ_DEBUG)
        {
            $tapatalk_old_error_handler = set_error_handler("TT_ErrorHandler", MBQ_DEBUG);
        }
        if(defined('IN_MOBIQUO') && MBQ_DEBUG)
        {
            if(!isset($tapatalk_old_error_handler))
            {
                $tapatalk_old_error_handler = set_error_handler("TT_ErrorHandler", error_reporting());
            }
            $tapatalk_old_exception_handler = set_exception_handler("TT_ExceptionHandler");
        }
    }
    if(defined('IN_MOBIQUO'))
    {
        if (defined('MBQ_DEBUG') && MBQ_DEBUG) {
            ini_set('display_errors','1');
            ini_set('display_startup_errors','1');
            error_reporting(MBQ_DEBUG);
        } else {    // Turn off all error reporting
            error_reporting(0);
            ini_set('display_errors','0');
            ini_set('display_startup_errors','0');
        }
    }

}
function TT_logCall($protocol, $method, $input)
{
    global $tapatalk_access_log;
    if($tapatalk_access_log instanceof KLogger && file_exists(MBQ_PATH . 'log.on'))
    {
        $ip= 'No ip available';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        unset($input['useragent']);

        $logString 	= $ip . ' - ' . strtoupper($protocol) . ' - '  . $method;
        if(!empty($input))
        {
            $logString .= ' => ' . base64_encode(print_r($input, true));
        }
        $tapatalk_access_log->logInfo($logString);
    }
}
function TT_ErrorHandler($errno, $errstr, $errfile, $errline)
{
    global $tapatalk_error_log, $tapatalk_old_error_handler, $TT_DEBUG_ERROR;
    if($tapatalk_error_log instanceof KLogger)
    {
        $error_string 	= 'PHP ' . $errno . '::' . $errstr . " in " . $errfile . " on line " . $errline;
        switch ($errno) {
            case E_ERROR:
            case E_USER_ERROR:
            case E_STRICT:
                $tapatalk_error_log->logError($error_string);
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $tapatalk_error_log->logWarn($error_string);
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $tapatalk_error_log->logNotice($error_string);
                break;

            default:
                $tapatalk_error_log->logInfo($error_string);
                break;
        }
        $TT_DEBUG_ERROR .= $error_string. PHP_EOL;
    }
    if(!defined('IN_MOBIQUO') && MBQ_DEBUG && isset($tapatalk_old_error_handler))
    {
        restore_error_handler();
        return false;
    }
    return true;
}
function TT_ExceptionHandler($ex)
{
    global $tapatalk_error_log, $tapatalk_old_exception_handler;
    if($ex instanceof Exception)
    {
        if($tapatalk_error_log instanceof KLogger)
        {
            TT_ErrorHandler($ex->getCode(), $ex->getMessage() . PHP_EOL . $ex->getTraceAsString() . PHP_EOL . PHP_EOL . "Method input receive: " . PHP_EOL . base64_encode(print_r(MbqMain::$input, true)), $ex->getFile(), $ex->getLine());
        }
    }
    if(!defined('IN_MOBIQUO') && MBQ_DEBUG && isset($tapatalk_old_exception_handler))
    {
        restore_exception_handler();
    }
    return false;
}