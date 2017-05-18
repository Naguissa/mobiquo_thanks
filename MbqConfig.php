<?php

defined('MBQ_IN_IT') or exit;

define('MBQ_DS', DIRECTORY_SEPARATOR);
if(!defined('MBQ_PATH'))
{
    define('MBQ_PATH', dirname($_SERVER['SCRIPT_FILENAME']).MBQ_DS);    /* mobiquo path */
}
define('MBQ_DIRNAME', basename(MBQ_PATH));    /* mobiquo dir name */
define('MBQ_PARENT_PATH', substr(MBQ_PATH, 0, strrpos(MBQ_PATH, MBQ_DIRNAME.MBQ_DS)));    /* mobiquo parent dir path */
define('MBQ_FRAME_PATH', MBQ_PATH.'mbqFrame'.MBQ_DS);    /* frame path */
require_once(MBQ_FRAME_PATH.'MbqBaseConfig.php');

$_SERVER['SCRIPT_FILENAME'] = str_replace(MBQ_DIRNAME.'/', '', $_SERVER['SCRIPT_FILENAME']);  /* Important!!! */
$_SERVER['PHP_SELF'] = str_replace(MBQ_DIRNAME.'/', '', $_SERVER['PHP_SELF']);  /* Important!!! */
$_SERVER['SCRIPT_NAME'] = str_replace(MBQ_DIRNAME.'/', '', $_SERVER['SCRIPT_NAME']);    /* Important!!! */
$_SERVER['REQUEST_URI'] = str_replace(MBQ_DIRNAME.'/', '', $_SERVER['REQUEST_URI']);    /* Important!!! */

/**
 * plugin config
 */
Class MbqConfig extends MbqBaseConfig {

    public function __construct() {
        parent::__construct();
        $this->initCfg();
    }
    
    /**
     * init cfg default value
     */
    protected function initCfg() {
        parent::initCfg();
    }
    
    /**
     * calculate the final config of $this->cfg through $this->cfg default value and MbqMain::$customConfig and MbqMain::$oMbqAppEnv and the plugin support degree
     */
    public function calCfg($isTTServerCall = false) {
        parent::calCfg($isTTServerCall);
      /* calculate the final config */
    }
    
}
