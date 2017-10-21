<?php

define('MBQ_IN_IT', true);  /* is in mobiquo flag */
define('MBQ_REG_SHUTDOWN', true);  /* register shutdown function flag */
require_once('MbqConfig.php');

$mbqDebug = false;
if(isset($_SERVER['HTTP_X_PHPDEBUG']))
{
    if(isset($_SERVER['HTTP_X_PHPDEBUGCODE']))
    {
        $code = trim($_SERVER['HTTP_X_PHPDEBUGCODE']);
        if (!class_exists('classTTConnection')){
            require_once(MBQ_3RD_LIB_PATH.'classTTConnection.php');
        }
        $connection = new classTTConnection();
        $response = $connection->actionVerification($code,'PHPDEBUG');
        if($response)
        {
            $mbqDebug = $_SERVER['HTTP_X_PHPDEBUG'];
        }
    }
    else if(file_exists(MBQ_PATH . 'debug.on'))
    {
        $mbqDebug = $_SERVER['HTTP_X_PHPDEBUG'];
    }
}


define('MBQ_DEBUG', $mbqDebug);  /* is in debug mode flag */
if (MBQ_DEBUG) {
    ini_set('display_errors','1');
    ini_set('display_startup_errors','1');
    error_reporting($mbqDebug);
} else {    // Turn off all error reporting
    error_reporting(0);
    ini_set('display_errors','0');
    ini_set('display_startup_errors','0');
}

define('PHPBB_MSG_HANDLER', 'mobiquo_error_handler');

function mobiquo_error_handler($errno, $msg_text, $errfile, $errline)
{
    global $auth, $user, $msg_long_text;

    // Do not display notices if we suppress them via @
    if (MBQ_DEBUG == 0 && $errno != E_USER_ERROR && $errno != E_USER_WARNING  && $errno != E_USER_NOTICE)
    {
        return;
    }
    /*if(strpos($errfile, 'session.php') !== false)
    {
    return ;
    }*/
    if ($msg_text == 'NO_SEARCH_RESULTS')
    {
        $response = search_func();
        echo $response;
        exit;
    }

    if(strstr(strip_tags($msg_text),$user->lang['REPORTS_CLOSED_SUCCESS']) || strstr(strip_tags($msg_text),$user->lang['REPORT_CLOSED_SUCCESS']))
    {
        MbqError::alert('', basic_clean($user->lang['REPORTS_CLOSED_SUCCESS']));

        exit;
    }

    if(preg_match('/^SQL ERROR/', $msg_text))
    {
        MbqMain::$oMbqIo->alert("An SQL error occurred while fetching this page. Please contact the administrator if the problem persists.", true, null, $msg_text);
    }

    // Message handler is stripping text. In case we need it, we are possible to define long text...
    if (isset($msg_long_text) && $msg_long_text && !$msg_text)
    {
        $msg_text = $msg_long_text;
    }

    $msg_text = basic_clean($msg_text);

    if (!defined('E_DEPRECATED'))
    {
        define('E_DEPRECATED', 8192);
    }

    switch ($errno)
    {
        case E_NOTICE:
        case E_WARNING:

            // Check the error reporting level and return if the error level does not match
            // If DEBUG is defined the default level is E_ALL
            if (MBQ_DEBUG == 0)
            {
                return;
            }

            if (strpos($errfile, 'cache') === false && strpos($errfile, 'template.') === false)
            {
                $errfile = phpbb_filter_root_path($errfile);
                $msg_text = phpbb_filter_root_path($msg_text);
                $error_name = ($errno === E_WARNING) ? 'PHP Warning' : 'PHP Notice';
                echo '[phpBB Debug] ' . $error_name . ': in file ' . $errfile . ' on line ' . $errline . ': ' . $msg_text . "\n";
            }

            return;

            break;

        case E_USER_ERROR:

            if (!empty($user) && !empty($user->lang))
            {
                $msg_text = (!empty($user->lang[$msg_text])) ? $user->lang[$msg_text] : $msg_text;
            }
            garbage_collection();
            break;

        case E_USER_WARNING:
        case E_USER_NOTICE:

            define('IN_ERROR_HANDLER', true);

            if (empty($user->data))
            {
                $user->session_begin();
            }

            // We re-init the auth array to get correct results on login/logout
            $auth->acl($user->data);

            if (empty($user->lang))
            {
                $user->setup();
            }

            if ($msg_text == 'ERROR_NO_ATTACHMENT' || $msg_text == 'NO_FORUM' || $msg_text == 'NO_TOPIC' || $msg_text == 'NO_USER')
            {
                //send_status_line(404, 'Not Found');
            }

            $msg_text = (!empty($user->lang[$msg_text])) ? $user->lang[$msg_text] : $msg_text;
            break;

        // PHP4 compatibility
        case E_DEPRECATED:
            return true;
            break;
    }

    if (in_array($errno, array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE)))
     {
         if(!check_error_status($msg_text))
         {
             if (MBQ_DEBUG == -1) $msg_text .= " > $errfile, $errline";
             
             MbqError::alert('', $msg_text);
             exit;
         }
     }

     // If we notice an error not handled here we pass this back to PHP by returning false
     // This may not work for all php versions
    return false;
}


/**
 * frame main program
 */
Abstract Class MbqMain extends MbqBaseMain {
   public static function init() {
        parent::init();
        self::$oMbqCm->changeWorkDir('..');  /* change work dir to parent dir.Important!!! */
        self::regShutDown();
    }
    public static function getCurrentCmd()
    {
        global $tapatalk_cmd;
        if (isset($_GET['method_name']) && $_GET['method_name']) {     //for more flexibility
            self::$cmd = $_GET['method_name'];
        }
        else if (isset($_POST['method_name']) && $_POST['method_name']) {    //for upload_attach and other post method
            self::$cmd = $_POST['method_name'];
            foreach ($_POST as $k => $v) {
                self::$input[$k] = $v;
            }
        }
        if(!self::$cmd && isset($_SERVER['PATH_INFO']))
        {
            $splitArray = preg_split('[&?]',$_SERVER['PATH_INFO']);
            $pathInfoCmd = $splitArray[0];
            $pathInfoCmd = substr($pathInfoCmd, 1);
            self::$cmd = $pathInfoCmd;
        }
        if(!self::$cmd && isset($tapatalk_cmd)) //for avatar.php
        {
            self::$cmd = $tapatalk_cmd;
        }
        return self::$cmd;
    }
    /**
     * action
     */
    public static function action() {
        parent::action();
        if (self::hasLogin()) {
            header('Mobiquo_is_login: true');
        } else {
            header('Mobiquo_is_login: false');
        }
        self::$oMbqConfig->calCfg();    /* you should do some modify within this function in multiple different type applications! */
        if (!self::$oMbqConfig->pluginIsOpen() && self::$cmd != 'get_config' && self::$cmd != 'set_forum_info' && self::$cmd != 'call_tapatalk_api') {
            MbqError::alert('', self::$oMbqConfig->getPluginClosedMessage());
        }
        self::$cmd = self::getCurrentCmd();
        if (self::$cmd) {
            self::$cmd = (string) self::$cmd;
            //MbqError::alert('', self::$cmd);
            if (preg_match('/[A-Za-z0-9_]{1,128}/', self::$cmd)) {
                $arr = explode('_', self::$cmd);
                foreach ($arr as &$v) {
                    $v = ucfirst(strtolower($v));
                }
                $hookClassName = 'MbqHook'.implode('', $arr);
                $actionClassName = 'MbqAct'.implode('', $arr);
               if (self::$oClk->hasReg($actionClassName)) {
                    self::$oAct = self::$oClk->newObj($actionClassName);
                    if(file_exists(MBQ_HOOK_PATH . $hookClassName . '.php'))
                    {
                        include_once(MBQ_HOOK_PATH . $hookClassName . '.php');
                        self::$oAct = new $hookClassName();
                        self::$oAct->actionImplement(self::$oAct->getInput());
                    }
                    else
                    {
                        self::$oAct->actionImplement(self::$oAct->getInput());
                    }
               } else {
                    //MbqError::alert('', "Not support action for ".self::$cmd."!", '', MBQ_ERR_NOT_SUPPORT);
                    MbqError::alert('', "Sorry!This feature is not available in this forum.Method name:".self::$cmd, '', MBQ_ERR_NOT_SUPPORT);
                }
            } else {
                MbqError::alert('', "Need valid cmd!");
            }
        } else {
            if(empty($_POST) && empty($_GET))
            {
              //  include(MBQ_PATH . 'pluginstatus.php');
            }
            else
            {
                MbqError::alert('', "Need not empty cmd!");
            }
        }
    }

    /**
     * do something before output
     */
    public static function beforeOutPut() {
        parent::beforeOutput();
    }

}
