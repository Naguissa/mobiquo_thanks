<?php
define('MBQ_PROTOCOL','web');
global $tapatalk_cmd;
$tapatalk_cmd = 'update';
define('IN_MOBIQUO', true);
define('TT_ROOT', getcwd() . DIRECTORY_SEPARATOR);

require_once('mobiquoCommon.php');

MbqMain::init(); // frame init
MbqMain::input(); // handle input data
require_once(MBQ_PATH.'IncludeBeforeMbqAppEnv.php');
MbqMain::initAppEnv(); // application environment init
MbqMain::$oMbqConfig->calCfg();
@ ob_start();
require_once(MBQ_PATH . '/logger.php');
require_once(MBQ_FRAME_PATH . '/MbqBaseStatus.php');
class MbqStatus extends MbqBaseStatus
{

    public function GetLoggedUserName()
    {
        if(MbqMain::$oCurMbqEtUser != null)
        {
            return MbqMain::$oCurMbqEtUser->loginName->oriValue;
        }
        return 'anonymous';
    }
    protected function GetMobiquoFileSytemDir()
    {
        return TT_ROOT;
    }
    protected function GetMobiquoDir()
    {
        global $config;
        if(empty($config['tapatalkdir'])) $config['tapatalkdir'] = 'mobiquo';
        return $config['tapatalkdir'];
    }
    protected function GetApiKey()
    {
        global $config;
        return $config['tapatalk_push_key'];
    }
    protected function GetForumUrl()
    {
       global $phpbb_home;
        return $phpbb_home;
    }
    protected function GetPushSlug()
    {
        global $config;
        return $config['tapatalk_push_slug'];
    }

    protected function ResetPushSlug()
    {
        global $config;
        $config->set('tapatalk_push_slug', 0);
        return true;
    }

    protected function GetBYOInfo()
    {
        global $config, $cache;
        $app_banner_enable =  $config['tapatalk_app_banner_enable'];
        $google_indexing_enabled = $config['tapatalk_google_indexing_enabled'];
        $facebook_indexing_enabled = $config['tapatalk_facebook_indexing_enabled'];
        $twitter_indexing_enabled = $config['tapatalk_twitter_indexing_enabled'];
        $TT_bannerControlData = getTapatalkConfigValue('tapatalk_banner_control');
        $TT_bannerControlData = !empty($TT_bannerControlData) ? unserialize($TT_bannerControlData) : false;
	$TT_updateTime = getTapatalkConfigValue('tapatalk_banner_update');

        if (file_exists(MBQ_3RD_LIB_PATH .'/classTTConnection.php')){
             include_once(MBQ_3RD_LIB_PATH .'/classTTConnection.php');
        }
        $TT_connection = new classTTConnection();
        $TT_connection->calcSwitchOptions($TT_bannerControlData, $app_banner_enable, $google_indexing_enabled, $facebook_indexing_enabled, $twitter_indexing_enabled);
        $TT_bannerControlData['update'] = $TT_updateTime;
        $TT_bannerControlData['banner_enable'] = $app_banner_enable;
        $TT_bannerControlData['google_enable'] = $google_indexing_enabled;
        $TT_bannerControlData['facebook_enable'] = $facebook_indexing_enabled;
        $TT_bannerControlData['twitter_enable'] = $twitter_indexing_enabled;
        return $TT_bannerControlData;
    }
  
    protected function GetOtherPlugins()
    {
    if(getPHPBBVersion() == '3.0')
	{
	  global $db,$table_prefix;
        $result = array();

        $sql = 'SELECT * FROM ' . $table_prefix . 'mods';
        $sqlresult = $db->sql_query($sql);

        while ($row = $db->sql_fetchrow($sqlresult))
        {
            $modName = unserialize($row['mod_name']);
            $result[] = array('name'=> $modName['en'], 'version'=>$row['mod_version']);
        }
	    return $result;
	}
	else
	{		
	 global $phpbb_extension_manager;
        $result = array();
        foreach ($phpbb_extension_manager->all_enabled() as $name => $location)
		{
			$md_manager = $phpbb_extension_manager->create_extension_metadata_manager($name, $this->template);

			try
			{
				$meta = $md_manager->get_metadata('all');
                $result[] = array('name'=>$md_manager->get_metadata('display-name'), 'version'=>$meta['version']);
			}
			catch (\phpbb\extension\exception $e)
			{
			}
		}
        return $result;
	}
       
    }

}
$mbqStatus = new MbqStatus();

