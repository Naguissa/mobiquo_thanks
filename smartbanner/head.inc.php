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

/* add google meta */
if(!isset($google_indexing_enabled) || $google_indexing_enabled)
{
    /* valid page_type: index, forum, topic, post, pm, search, profile, online, other*/
    $page_type = isset($page_type) && $page_type ? $page_type : 'other';
    $app_location = isset($app_location) ? trim($app_location) : '';

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
        <link href="https://groups.tapatalk-cdn.com/static/manifest/manifest.json" rel="manifest">
        ';
    }
    else
    {
        $app_head_include .= '
        <link href="https://groups.tapatalk-cdn.com/static/manifest/manifest-' . $app_rebranding_id . '.json" rel="manifest">
        ';
    }

    // add google native app manifest link
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
}

function tt_html_escape($str)
{
    return addslashes(htmlspecialchars($str, ENT_NOQUOTES, "UTF-8"));
}

function tt_add_channel($url, $channel)
{
    if (strpos($url, '?') === false)
        $url .= "?channel=$channel";
    else
        $url .= "&channel=$channel";

    return $url;
}