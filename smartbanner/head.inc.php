<?php

$app_head_include = '';

/* byo app info */
$app_rebranding_id = isset($app_rebranding_id) ? intval($app_rebranding_id) : 0;
if ($app_rebranding_id)
{
    $app_url_scheme = isset($app_url_scheme) && $app_url_scheme ? $app_url_scheme : 'ttbyo-'.$app_rebranding_id;
    if(isset($app_ios_id) && intval($app_ios_id))
    {
        $app_ios_id = intval($app_ios_id);
        $app_ios_url_scheme = $app_url_scheme;
    }
    else
    {
        $app_ios_id = '307880732';
        $app_ios_url_scheme = 'tapatalk';
    }
    if(isset($app_android_id) && trim($app_android_id))
    {
        $app_android_id = trim($app_android_id);
        $app_android_url_scheme = $app_url_scheme;
    }
    else
    {
        $app_android_id = 'com.quoord.tapatalkpro.activity';
        $app_android_url_scheme = 'tapatalk';
    }
}
/* tapatalk app info */
else
{
    $app_ios_id = '307880732';
    $app_ios_url_scheme = 'tapatalk';
    $app_android_id = 'com.quoord.tapatalkpro.activity';
    $app_android_url_scheme = 'tapatalk';
}

/* valid page_type: index, forum, topic, post, pm, search, profile, online, other*/
$page_type = isset($page_type) && $page_type ? $page_type : 'other';
$app_location = isset($app_location) ? trim($app_location) : '';
$app_piwik_id = isset($app_piwik_id) ? trim($app_piwik_id) : 0;
$app_banner_version_id = isset($app_banner_version_id) ? trim($app_banner_version_id) : 0;

/* add google meta */
if(!isset($google_indexing_enabled) || $google_indexing_enabled)
{
    if (in_array($page_type, array('topic', 'post', 'home')) && $app_location)
    {
        // display google app indexing meta
        $app_head_include .= '
        <!-- App Indexing for Google Search -->';

        if ($app_android_id) $app_head_include .= '
        <link href="android-app://'.$app_android_id.'/'.$app_android_url_scheme.'/'.tt_html_escape(tt_add_channel($app_location, 'google-indexing')).'" rel="alternate" />';

        if ($app_ios_id) $app_head_include .= '
        <link href="ios-app://'.$app_ios_id.'/'.$app_ios_url_scheme.'/'.tt_html_escape(tt_add_channel($app_location, 'google-indexing')).'" rel="alternate" />
        ';
    }
}



// add android native banner and ios native banner
if(!isset($app_banner_enable) || $app_banner_enable)
{

    // add google native app manifest link
    if ($app_android_id == 'com.quoord.tapatalkpro.activity')
    {
        $app_head_include .= '
        <link href="' . $smartBannerPath . 'manifest.json" rel="manifest">
        ';
    }
    else
    {
        $app_head_include .= '
        <link href="https://groups.tapatalk-cdn.com/static/manifest/manifest-' . $app_rebranding_id . '.json" rel="manifest">
        ';
    }

    // add ios native app manifest link
    if ($app_ios_id == '307880732')
    {
        $app_head_include .= "
        <meta name=\"apple-itunes-app\" content=\"app-id=$app_ios_id, affiliate-data=at=10lR7C, app-argument=$app_ios_url_scheme://$app_location\" />
        ";
    }
    else
    {
        $app_head_include .= "
        <meta name=\"apple-itunes-app\" content=\"app-id=$app_ios_id, app-argument=$app_ios_url_scheme://$app_location\" />
        ";
    }

    /* don't include it when the request was not from mobile device */
    $useragent = tt_getenv('HTTP_USER_AGENT');
    if (!preg_match('/iPhone|iPod|iPad|Silk|Android|IEMobile|Windows Phone|Windows RT.*?ARM/i', $useragent))
        return;

    /* don't show banner for googlebot or twitterbot */
    if (tt_crawlerDetect($useragent))
        return;
    if($app_banner_enable == 2)
    {
        return;
    }

    /* display smart banner */
    if(isset($app_sharelink_url) && isset($app_sharelink_location) && isset($app_sharelink_ttforumid))
    {
        $app_banner_head = '';
        $app_banner_head = '
<!-- Tapatalk Banner head start -->
<script type="text/javascript">
    var app_location       = "'.tt_html_escape($app_location, true).'";
    var app_sharelink_url      = "'.tt_html_escape($app_sharelink_url).'";
    var app_sharelink_location       = "'.tt_html_escape($app_sharelink_location).'";
    var app_sharelink_ttforumid       = "'.tt_html_escape($app_sharelink_ttforumid).'";
    var app_sharelink_fid       = "'. (isset($app_sharelink_fid) ? tt_html_escape($app_sharelink_fid) : '') .'";
    var app_sharelink_tid       = "'. (isset($app_sharelink_tid) ? tt_html_escape($app_sharelink_tid) : '') . '";
    var app_sharelink_pid       = "'. (isset($app_sharelink_pid) ? tt_html_escape($app_sharelink_pid) : '') . '";
    var app_ios_url_scheme     = "'.tt_html_escape($app_ios_url_scheme).'";
    var app_android_url_scheme     = "'.tt_html_escape($app_android_url_scheme).'";
    var app_pagetype = "' . $page_type . '";
    var app_piwik_id = "' . $app_piwik_id . '";
</script>
<script src="https://groups.tapatalk-cdn.com/static/js/smartbanner_v'  . $app_banner_version_id . '.js" type="text/javascript"></script>
<!-- Tapatalk Banner head end-->';
        $app_head_include .= $app_banner_head;
    }
}

function tt_html_escape($str)
{
    return addslashes(str_replace('&amp;','&',htmlspecialchars($str, ENT_NOQUOTES, "UTF-8")));
}

function tt_add_channel($url, $channel)
{
    if (strpos($url, '?') === false)
        $url .= "?channel=$channel";
    else
        $url .= "&channel=$channel";

    return $url;
}
function tt_crawlerDetect($USER_AGENT)
{
    return preg_match('/bot|crawl|slurp|spider/i', $USER_AGENT);
}
function tt_getenv($key)
{
    $return = '';

    if ( is_array( $_SERVER ) && isset( $_SERVER[$key] ) && $_SERVER[$key])
    {
        $return = $_SERVER[$key];
    }
    else
    {
        $return = getenv($key);
    }

    return $return;
}
