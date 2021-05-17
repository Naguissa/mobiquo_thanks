<?php

defined('MBQ_IN_IT') or exit;

function mbqInitGetConfigValues($isTTServerCall = false)
{
    /**
     * user custom config,to replace some config of MbqMain::$oMbqConfig->cfg.
     * you can change any config if you need,please refer to MbqConfig.php for more details.
     */
    global $config, $auth, $user, $mobiquo_config, $db, $phpbb_extension_manager;

    if($config['board_disable'] || $phpbb_extension_manager->is_enabled('tapatalk/tapatalk') == false)
    {
        MbqMain::$customConfig['base']['is_open'] = MbqBaseFdt::getFdt('MbqFdtConfig.base.is_open.range.no');
        MbqMain::$customConfig['base']['result_text'] = (string)$config['board_disable_msg'];
    }
    else
    {
        MbqMain::$customConfig['base']['is_open'] = MbqBaseFdt::getFdt('MbqFdtConfig.base.is_open.range.yes');
    }
    MbqMain::$customConfig['base']['version'] = 'pb31_2.1.8';
 
    MbqMain::$customConfig['base']['api_level'] = 4;
    MbqMain::$customConfig['base']['json_support'] = MbqBaseFdt::getFdt('MbqFdtConfig.base.json_support.range.yes');
    MbqMain::$customConfig['base']['inbox_stat'] = MbqBaseFdt::getFdt('MbqFdtConfig.base.inbox_stat.range.support');
    MbqMain::$customConfig['base']['sys_version'] = $config['version'];
    MbqMain::$customConfig['base']['announcement'] = MbqBaseFdt::getFdt('MbqFdtConfig.base.announcement.range.support');
    MbqMain::$customConfig['base']['push'] = 1;
    MbqMain::$customConfig['base']['push_type'] = 'pm,sub,quote,newtopic,tag,newsub';
    MbqMain::$customConfig['base']['banner_control'] = MbqBaseFdt::getFdt('MbqFdtConfig.base.banner_control.range.support');
    MbqMain::$customConfig['base']['api_key'] = isset($config['tapatalk_push_key']) && !empty($config['tapatalk_push_key']) ? md5($config['tapatalk_push_key']) : "";
    MbqMain::$customConfig['base']['set_api_key'] = 1;
    MbqMain::$customConfig['base']['set_forum_info'] = 1;
    MbqMain::$customConfig['base']['user_subscription'] = 1;
    MbqMain::$customConfig['base']['push_content_check'] = 1;

    MbqMain::$customConfig['base']['ads_disabled_group']  = isset($config['tapatalk_ad_filter']) ? $config['tapatalk_ad_filter'] : "";

    if($isTTServerCall)
    {
        MbqMain::$customConfig['base']['hook_version'] = MbqMain::$customConfig['base']['version'];
        MbqMain::$customConfig['base']['release_timestamp'] = 1463490207;
        $oMbqRdCommon = MbqMain::$oClk->newObj('MbqRdCommon');
        MbqMain::$customConfig['base']['smartbanner_info'] = json_encode($oMbqRdCommon->getSmartbannerInfo());
        MbqMain::$customConfig['base']['push_slug'] =json_encode($oMbqRdCommon->getPushSlug());
    }

    MbqMain::$customConfig['subscribe']['module_enable'] = MbqBaseFdt::getFdt('MbqFdtConfig.subscribe.module_enable.range.enable');

    MbqMain::$customConfig['user']['user_id'] = MbqBaseFdt::getFdt('MbqFdtConfig.user.user_id.range.support');

    $guest_okay = false;
    $sql = 'SELECT f.*  FROM ' . FORUMS_TABLE . ' f ORDER BY f.left_id ASC';
    $result = $db->sql_query($sql, 600);
    while ($row = $db->sql_fetchrow($result))
    {
        $forum_id = $row['forum_id'];
        if($auth->acl_get('f_list', $forum_id))
        {
            $guest_okay = true;
            break;
        }
    }
    $db->sql_freeresult($result);


    $sql = "SELECT group_id
		FROM " . GROUPS_TABLE . "
		WHERE group_name = 'GUESTS'";
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);
    $db->sql_freeresult($result);

    MbqMain::$customConfig['user']['guest_okay'] = $guest_okay ? MbqBaseFdt::getFdt('MbqFdtConfig.user.guest_okay.range.support') : MbqBaseFdt::getFdt('MbqFdtConfig.user.guest_okay.range.notSupport');
    MbqMain::$customConfig['user']['guest_group_id'] = (string)$row['group_id'];
    MbqMain::$customConfig['user']['search_user'] = MbqBaseFdt::getFdt('MbqFdtConfig.user.search_user.range.support');
    MbqMain::$customConfig['user']['ignore_user'] = MbqBaseFdt::getFdt('MbqFdtConfig.user.ignore_user.range.support');
    MbqMain::$customConfig['user']['emoji_support'] = MbqBaseFdt::getFdt('MbqFdtConfig.user.emoji_support.range.support');
    MbqMain::$customConfig['user']['get_ignored_users'] = MbqBaseFdt::getFdt('MbqFdtConfig.user.get_ignored_users.range.support');

    MbqMain::$customConfig['user']['advanced_online_users'] = MbqBaseFdt::getFdt('MbqFdtConfig.user.advanced_online_users.range.support');
    if ($auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel'))
    {
        MbqMain::$customConfig['user']['guest_whosonline'] = MbqBaseFdt::getFdt('MbqFdtConfig.user.guest_whosonline.range.support');
    }
    else
    {
        MbqMain::$customConfig['user']['guest_whosonline'] = MbqBaseFdt::getFdt('MbqFdtConfig.user.guest_whosonline.range.notSupport');
    }
    MbqMain::$customConfig['user']['anonymous'] = MbqBaseFdt::getFdt('MbqFdtConfig.user.anonymous.range.support');
    MbqMain::$customConfig['user']['avatar'] = MbqBaseFdt::getFdt('MbqFdtConfig.user.avatar.range.support');
    MbqMain::$customConfig['user']['upload_avatar'] = MbqBaseFdt::getFdt('MbqFdtConfig.user.upload_avatar.range.support');
    MbqMain::$customConfig['forum']['no_refresh_on_post'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.no_refresh_on_post.range.support');
    MbqMain::$customConfig['forum']['get_latest_topic'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.get_latest_topic.range.support');
    if ($auth->acl_get('u_search') && $auth->acl_getf_global('f_search') && $config['load_search'])
    {
        MbqMain::$customConfig['forum']['guest_search'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.guest_search.range.support');
    }
    else
    {
        MbqMain::$customConfig['forum']['guest_search'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.guest_search.range.notSupport');
    }
    MbqMain::$customConfig['forum']['mark_read'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.mark_read.range.support');
    MbqMain::$customConfig['forum']['mark_topic_read'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.mark_topic_read.range.support');
    MbqMain::$customConfig['forum']['report_post'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.report_post.range.support');
    MbqMain::$customConfig['forum']['goto_post'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.report_post.range.support');
    MbqMain::$customConfig['forum']['goto_unread'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.goto_unread.range.support');
    MbqMain::$customConfig['forum']['can_unread'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.can_unread.range.support');
    MbqMain::$customConfig['forum']['first_unread'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.first_unread.range.support');
    MbqMain::$customConfig['forum']['get_id_by_url'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.get_id_by_url.range.support');
    MbqMain::$customConfig['forum']['mark_forum'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.mark_forum.range.support');
    MbqMain::$customConfig['forum']['mod_report'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.mod_report.range.support');
    MbqMain::$customConfig['forum']['multi_quote'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.multi_quote.range.support');
    MbqMain::$customConfig['forum']['advanced_move'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.advanced_move.range.support');
    MbqMain::$customConfig['forum']['get_participated_forum'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.get_participated_forum.range.support');
    MbqMain::$customConfig['forum']['advanced_html'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.advanced_html.range.support');
   if(getPHPBBVersion() == '3.0')
    {
        MbqMain::$customConfig['forum']['mod_delete'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.mod_delete.range.notSupport');
        MbqMain::$customConfig['forum']['advanced_delete'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.advanced_delete.range.notSupport');
        MbqMain::$customConfig['forum']['mod_approve'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.mod_approve.range.notSupport');
    }
    else
    {
        MbqMain::$customConfig['forum']['mod_delete'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.mod_delete.range.support');
        MbqMain::$customConfig['forum']['advanced_delete'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.advanced_delete.range.support');
        MbqMain::$customConfig['forum']['mod_approve'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.mod_approve.range.support');
    }
    MbqMain::$customConfig['forum']['get_id_by_url'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.get_id_by_url.range.support');
    MbqMain::$customConfig['forum']['get_url_by_id'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.get_url_by_id.range.support');
    MbqMain::$customConfig['forum']['search_started_by'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.search_started_by.range.support');
    MbqMain::$customConfig['forum']['get_topic_by_ids'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.get_topic_by_ids.range.support');
    if ($config['search_type'] == '\phpbb\search\fulltext_native')
    {
        MbqMain::$customConfig['forum']['min_search_length'] = (int)$config['fulltext_native_min_chars'];
    }
    else if ($config['search_type'] == '\phpbb\search\fulltext_mysql')
    {
        MbqMain::$customConfig['forum']['min_search_length'] = (int)$config['fulltext_mysql_min_word_len'];
    }
    MbqMain::$customConfig['forum']['advanced_search'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.advanced_search.range.support');
    MbqMain::$customConfig['forum']['subscribe_forum'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.subscribe_forum.range.support');
    MbqMain::$customConfig['forum']['subscribe_load'] = MbqBaseFdt::getFdt('MbqFdtConfig.forum.subscribe_load.range.support');
    MbqMain::$customConfig['forum']['alert'] = 0;

    MbqMain::$customConfig['pm']['module_enable'] = MbqBaseFdt::getFdt('MbqFdtConfig.pm.module_enable.range.enable');
    MbqMain::$customConfig['pm']['mark_pm_unread'] = MbqBaseFdt::getFdt('MbqFdtConfig.pm.mark_pm_unread.range.support');
    MbqMain::$customConfig['pm']['mark_pm_read'] = MbqBaseFdt::getFdt('MbqFdtConfig.pm.mark_pm_read.range.support');
    MbqMain::$customConfig['pm']['pm_load'] = MbqBaseFdt::getFdt('MbqFdtConfig.pm.pm_load.range.support');
    MbqMain::$customConfig['pm']['report_pm'] = MbqBaseFdt::getFdt('MbqFdtConfig.pm.report_pm.range.support');


    $mobiquo_config['sign_in'] = 1;
    $mobiquo_config['inappreg'] = 1;
    $mobiquo_config['sso_login'] = 1;
    $mobiquo_config['sso_signin'] = 1;
    $mobiquo_config['sso_register'] = 1;
    $mobiquo_config['native_register'] = 1;

    if($config['require_activation'] == USER_ACTIVATION_DISABLE)
    {
        $mobiquo_config['sign_in'] = 0;
        $mobiquo_config['inappreg'] = 0;
        $mobiquo_config['sso_signin'] = 0;
        $mobiquo_config['sso_register'] = 0;
        $mobiquo_config['native_register'] = 0;
    }
    if (!function_exists('curl_init') && !@ini_get('allow_url_fopen'))
    {
        $mobiquo_config['sign_in'] = 0;
        $mobiquo_config['inappreg'] = 0;

        $mobiquo_config['sso_login'] = 0;
        $mobiquo_config['sso_signin'] = 0;
        $mobiquo_config['sso_register'] = 0;
    }
    if(isset($config['tapatalk_sso_enabled']))
    {
        if($config['tapatalk_sso_enabled'] == 0)
        {
            $mobiquo_config['sign_in'] = 0;
            $mobiquo_config['inappreg'] = 0;
            $mobiquo_config['sso_signin'] = 0;
            $mobiquo_config['sso_register'] = 0;
            $mobiquo_config['native_register'] = 0;
        }
    }
    //    elseif($config['tapatalk_register_status'] == 1)
    //    {
    //        $mobiquo_config['inappreg'] = 0;
    //        $mobiquo_config['sign_in'] = 0;

    //        $mobiquo_config['sso_signin'] = 0;
    //        $mobiquo_config['sso_register'] = 0;
    //    }
    //}

    MbqMain::$customConfig['user']['sign_in'] = $mobiquo_config['sign_in'] == 0 ? MbqBaseFdt::getFdt('MbqFdtConfig.user.sign_in.range.notSupport') : MbqBaseFdt::getFdt('MbqFdtConfig.user.sign_in.range.support');
    MbqMain::$customConfig['user']['inappreg'] = $mobiquo_config['inappreg'] == 0 ? MbqBaseFdt::getFdt('MbqFdtConfig.user.inappreg.range.notSupport') : MbqBaseFdt::getFdt('MbqFdtConfig.user.inappreg.range.support');
    MbqMain::$customConfig['user']['sso_login'] = $mobiquo_config['sso_login'] == 0 ? MbqBaseFdt::getFdt('MbqFdtConfig.user.sso_login.range.notSupport') : MbqBaseFdt::getFdt('MbqFdtConfig.user.sso_login.range.support');
    MbqMain::$customConfig['user']['sso_signin'] = $mobiquo_config['sso_signin'] == 0 ? MbqBaseFdt::getFdt('MbqFdtConfig.user.sso_signin.range.notSupport') : MbqBaseFdt::getFdt('MbqFdtConfig.user.sso_signin.range.support');
    MbqMain::$customConfig['user']['sso_register'] = $mobiquo_config['sso_register'] == 0 ? MbqBaseFdt::getFdt('MbqFdtConfig.user.sso_register.range.notSupport') : MbqBaseFdt::getFdt('MbqFdtConfig.user.sso_register.range.support');
    MbqMain::$customConfig['user']['native_register'] = $mobiquo_config['native_register'] == 0 ? MbqBaseFdt::getFdt('MbqFdtConfig.user.native_register.range.notSupport') : MbqBaseFdt::getFdt('MbqFdtConfig.user.native_register.range.support');
    $mobiquo_config['hide_forum_id'] = array();
    $mobiquoHideForumId = getTapatalkConfigValue('mobiquo_hide_forum_id');
    if(isset($mobiquoHideForumId))
    {
        $hideForum =  explode(',', $mobiquoHideForumId);
        foreach ($hideForum as $hideForumId)
        {
            if(is_numeric($hideForumId) && $hideForumId != 0)
            {
                $mobiquo_config['hide_forum_id'][] = $hideForumId;
            }
        }
    }
}