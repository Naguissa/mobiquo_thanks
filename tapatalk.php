<?php
define('MBQ_PROTOCOL', 'json');   //define is using json protocol,if not defined(default) means is using xmlrpc protocol
define('IN_MOBIQUO', true);
define('TT_ROOT', getcwd() . DIRECTORY_SEPARATOR);
/*START allow cors calls from other domain*/
if (isset($_SERVER['HTTP_ORIGIN']))
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 1000');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, x-tapatalk-web');
header('Access-Control-Expose-Headers: Mobiquo_is_login');
/*END allow cors calls from other domain*/

require_once('mobiquoCommon.php');

MbqMain::init();  /* frame init */
MbqMain::input();     /* handle input data */
require_once(MBQ_PATH.'IncludeBeforeMbqAppEnv.php');
MbqMain::initAppEnv();    /* application environment init */
@ ob_start();
require_once(MBQ_PATH . '/logger.php');
MbqMain::action();    /* main program handle */
MbqMain::beforeOutput();  /* do something before output */
MbqMain::output();    /* handle output data */
