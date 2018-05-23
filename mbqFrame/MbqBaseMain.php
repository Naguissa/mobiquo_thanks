<?php

defined('MBQ_IN_IT') or exit;

/**
 * frame main program base class
 */
Abstract Class MbqBaseMain {

    public static $oMbqCm;
    public static $oMbqConfig;
    public static $customConfig;    /* user custom config,defined in customConfig.php or customAdvConfig.php */
    public static $oMbqAppEnv;
    public static $oClk;  /* instance of class MbqClassLink */
    public static $oMbqCookie;
    public static $oMbqSession;
    public static $oMbqIo;
    public static $simpleV;   /* an empty MbqValue object for simple value initialization */
    public static $Cache;

    /* please always use isJsonProtocol() or isXmlRpcProtocol() in your code,instead of directly access this property */
    public static $protocol;    /* xmlrpc/json */
    public static $module;  /* module name */
    public static $cmd; /* action command name,must unique in all actions. */
    public static $input;   /* input params array */
    public static $client; /* client doing the request, can be 'ios', 'android', 'windows' or 'unknown' */
    public static $data;   /* data need return */
    public static $oAct;   /* action object */

    public static $oCurMbqEtUser;  /* current user obj after login. */
    private static $timestart;
    private static $memstart;
    private static $rusagestart;
    public static function init() {
        self::$simpleV = new MbqValue();
        self::$oClk = new MbqClassLink();
        self::$oMbqConfig = new MbqConfig();
        self::$oMbqCm = self::$oClk->newObj('MbqCm');
        self::$oMbqAppEnv = self::$oClk->newObj('MbqAppEnv');
        self::$oMbqCookie = self::$oClk->newObj('MbqCookie');
        self::$oMbqSession = self::$oClk->newObj('MbqSession');
        self::$oMbqIo = self::$oClk->newObj('MbqIo');
        self::$Cache = new MbqCallCache();
        if(isset($_SERVER['HTTP_MOBIQUOID']))
        {
            $mobiquoId = intval($_SERVER['HTTP_MOBIQUOID']);
            switch($mobiquoId)
            {
                case 16:
                case 18:
                    {
                        self::$client = 'windows';
                        break;
                    }
                case 2:
                case 3:
                case 11:
                    {
                        self::$client = 'ios';
                        break;
                    }
                case 4:
                    {
                        self::$client = 'android';
                        break;
                    }
                default:
                    {
                        self::$client = 'unknown';
                        break;
                    }
            }
        }
        else
        {
            self::$client = 'unknown';
        }
        if((isset($_SERVER['HTTP_TAPATALKSTATISTICS']) && $_SERVER['HTTP_TAPATALKSTATISTICS'])|| file_exists(MBQ_PATH . 'debug.on'))
        {
            self::$memstart = memory_get_usage();
            self::$timestart = microtime(true);
            if(function_exists('getrusage'))
            {
                $dat = getrusage();
                self::$rusagestart = $dat["ru_utime.tv_sec"]*1e6+$dat["ru_utime.tv_usec"];
            }
        }
    }

    public function camelCaseCmd()
    {
        if (preg_match('/[A-Za-z0-9_]{1,128}/', self::$cmd)) {
            $arr = explode('_', self::$cmd);
            foreach ($arr as &$v) {
                $v = ucfirst(strtolower($v));
            }
            return implode('', $arr);
        }
        else
        {
            return self::$cmd;
        }
    }
    /**
     * judge is using json protocol
     *
     * @return  Boolean
     */
    public static function isRawPostProtocol() {
        return (self::$protocol == 'post') ? TRUE : FALSE;
    }
    /**
     * judge is using json protocol
     *
     * @return  Boolean
     */
    public static function isWebProtocol() {
        return (self::$protocol == 'web') ? TRUE : FALSE;
    }
    /**
     * judge is using json protocol
     *
     * @return  Boolean
     */
    public static function isJsonProtocol() {
        return (self::$protocol == 'json') ? TRUE : FALSE;
    }

    /**
     * judge is using xmlrpc protocol
     *
     * @return  Boolean
     */
    public static function isXmlRpcProtocol() {
        return (self::$protocol == 'xmlrpc') ? TRUE : FALSE;
    }

    /**
     * data input
     */
    public static function input() {
        self::$oMbqIo->input();
    }

    /**
     * init application environment
     */
    public static function initAppEnv() {
        self::$oMbqAppEnv->init();
    }

    /**
     * action
     */
    public static function action() {
    }


    /**
     * data output
     */
    public static function output() {
        self::$oMbqIo->output();
    }

    /**
     * judge if has login
     *
     * @return  Boolean
     */
    public static function hasLogin() {
        return self::$oCurMbqEtUser ? true : false;
    }
    public static function isActiveMember()
    {
        return self::$oCurMbqEtUser && self::$oCurMbqEtUser->userType->oriValue != 'banned' && self::$oCurMbqEtUser->userType->oriValue != 'unapproved'? true : false;
    }
    public static function canViewBoard()
    {
        if(MbqMain::isActiveMember())
        {
            return true;
        }
        if(MbqMain::hasLogin() == false && self::$oMbqConfig->getCfg('user.guest_okay')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.user.guest_okay.range.support'))
        {
            return true;
        }
        return false;
    }
    public static function isAdmin()
    {
        return self::$oCurMbqEtUser && self::$oCurMbqEtUser->userType->oriValue == 'admin' ? true : false;
    }
    public static function isNotBanned()
    {
        return self::$oCurMbqEtUser == null  || self::$oCurMbqEtUser->userType->oriValue != 'banned';
    }
    /**
     * do something before output
     */
    public static function beforeOutPut() {
        if((isset($_SERVER['HTTP_TAPATALKSTATISTICS']) && $_SERVER['HTTP_TAPATALKSTATISTICS']) || file_exists(MBQ_PATH . 'debug.on'))
        {
            $memend = memory_get_usage();
            $timeend = microtime(true);
            header('TapatalkMemoryUsage: ' . ($memend - self::$memstart));
            header('TapatalkTimeTaken: ' . round(($timeend - self::$timestart) * 1000));
            if(function_exists('getrusage'))
            {
                header('TapatalkCPUUsage: ' . self::getCpuUsage());
            }
            header("TapatalkCacheHits: " . MbqMain::$Cache->cacheHits);
            header("TapatalkCacheMiss: " . MbqMain::$Cache->cacheMiss);
            header("TapatalkCacheObjects: " . MbqMain::$Cache->cacheObjects);
        }
        @ ob_end_clean();
    }
    static function getCpuUsage() {
        $cpu = '0.00';
        if(function_exists('getrusage'))
        {
            $dat = getrusage();
            $dat["ru_utime.tv_usec"] = ($dat["ru_utime.tv_sec"]*1e6 + $dat["ru_utime.tv_usec"]) - self::$rusagestart;
            $time = (microtime(true) - self::$timestart) * 1000000;

            // cpu per request
            if($time > 0) {
                $cpu = sprintf("%01.2f", ($dat["ru_utime.tv_usec"] / $time) * 100);
            } else {
                $cpu = '0.00';
            }
        }
        return $cpu;
    }

    /**
     * regist shutdown function
     */
    public static function regShutDown() {
        if (MBQ_REG_SHUTDOWN && function_exists('mbqShutdownHandle') && function_exists('register_shutdown_function'))
        {
            register_shutdown_function('mbqShutdownHandle');
        }
    }

}
class MbqCallCache
{
    public function __construct() {
        $this->callCache = array();
    }
    private $callCache;
    public $cacheHits = 0;
    public $cacheObjects = 0;
    public $cacheMiss = 0;
    private function CurrentUserId()
    {
        $currentUserId = -1;
        if(isset(MbqMain::$oCurMbqEtUser))
        {
            $currentUserId = MbqMain::$oCurMbqEtUser->userId->oriValue;
        }
        return $currentUserId;
    }
    public function Exists($type, $key)
    {
        $currentUserId = $this->CurrentUserId();
        if(isset( $this->callCache[$currentUserId]))
        {
            if(isset($this->callCache[$currentUserId][$type]))
            {
                if(isset( $this->callCache[$currentUserId][$type][$key]))
                {
                    return true;
                }
            }
        }
        $this->cacheMiss++;
        return false;
    }
    public function Set($type,$key,$value)
    {
        $currentUserId = $this->CurrentUserId();
        if(!isset($this->callCache[$currentUserId]))
        {
            $this->callCache[$currentUserId] = array();
        }
        if(!isset($this->callCache[$currentUserId][$type]))
        {
            $this->callCache[$currentUserId][$type] = array();
        }
        $this->callCache[$currentUserId][$type][$key] = $value;
        $this->cacheObjects++;
    }
    public function Get($type, $key)
    {
        if($this->Exists($type, $key))
        {
            $this->cacheHits++;
            $currentUserId = $this->CurrentUserId();
            return $this->callCache[$currentUserId][$type][$key];
        }
        return null;
    }
    public function Del($type, $key)
    {
        if($this->Exists($type, $key))
        {
            $this->cacheHits++;
            $currentUserId = $this->CurrentUserId();
            unset($this->callCache[$currentUserId][$type][$key]);
            $this->cacheObjects--;
            return true;
        }
        return false;
    }
    public function Reset()
    {
        $this->callCache = array();
    }
}
