<?php
if (isset($_GET['welcome'])) {
	if (!empty($_GET['referer']) && !empty($_REQUEST['SERVER_NAME'])) {
		$redirectURL = trim($_GET['referer']);

		if(!empty($redirectURL) && (strpos($redirectURL, $_REQUEST['SERVER_NAME']) !== false && strpos($redirectURL, $_REQUEST['SERVER_NAME']) !== false) || strpos($redirectURL, '/') === 0) {
			@header('Location: ' . $redirectURL);
			exit;
		}
	}
	else if (!empty($_GET['board_url'])) {
		$boardURL = trim($_GET['board_url']);

		if (!empty($boardURL)) {
			@header('Location: ' . $boardURL);
			exit;
		}
	}
}

if(!defined('MBQ_PROTOCOL'))
{
    define('MBQ_PROTOCOL','xmlrpc');
}
define('IN_MOBIQUO', true);
define('TT_ROOT', getcwd() . DIRECTORY_SEPARATOR);

require_once('mobiquoCommon.php');
require_once(MBQ_PATH . '/logger.php');
TT_InitAccessLog();
MbqMain::init(); // frame init
MbqMain::input(); // handle input data
require_once(MBQ_PATH.'IncludeBeforeMbqAppEnv.php');
MbqMain::initAppEnv(); // application environment init
if(method_exists($request,'enable_super_globals')){
    $request->enable_super_globals();
}
@ ob_start();
TT_InitErrorLog();
MbqMain::action(); // main program handle
MbqMain::beforeOutput(); // do something before output
MbqMain::output(); // handle output data
