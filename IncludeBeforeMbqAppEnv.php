<?php

defined('MBQ_IN_IT') or exit;
/**
 * This file is not needed by default!
 * Run this first before call MbqMain::initAppEnv() when you need!

 */
/* Please write any codes you need in the following area before call MbqMain::initAppEnv()! */
global $phpbb_root_path;

$phpbb_root_path = dirname(dirname(__FILE__)).'/';
$mobiquo_root_path = dirname(__FILE__).'/';
$phpEx = 'php';
define('IN_PHPBB', true);
define('PHPBB_ROOT_PATH',$phpbb_root_path);
define('MOBIQUO_ROOT_PATH',$mobiquo_root_path);
require_once($phpbb_root_path . 'common.' . $phpEx);
require_once($mobiquo_root_path . 'helper.php');
require_once($mobiquo_root_path . 'tapatalkFunctions.php');
if(getPHPBBVersion() == '3.0')
{
	require_once(MBQ_APPEXTENTION_PATH . '/3.0/fake_request.php');
	require_once(MBQ_APPEXTENTION_PATH . '/3.0/fake_phpbbcontainer.php');
}
else
{		
           
}
//checkPluginInitialized();