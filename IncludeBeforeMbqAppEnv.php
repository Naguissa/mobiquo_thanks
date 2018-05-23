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

function TT_addNameValue($name, $value, &$list, $bind){
    // this statement is fixing the wrong drop down value in the custom field issue
    if (isset($bind['type']) && ($bind['type'] == 'drop' || $bind['type'] == 'radio') && !empty($bind['options']))
    {
        // the following logic compatible with the logic at MbqRdEtUser line 738
        // we read the dropdown value from the options
        // the real drop down value is stored in the table PROFILE_FIELDS_LANG_TABLE
        $options = explode('|', $bind['options']);

        if (is_array($options))
        {
            foreach ($options as $v) {
                $v = explode('=', $v);

                if (isset($v[0]) && isset($v[1]) && $v[0] == $value)
                {
                    $realValue = $v[1];
                }
            }
        }
    }

    $list[] = array(
        'name'  => $name,
        'value' => isset($realValue) ? $realValue : $value
    );
}
//checkPluginInitialized();