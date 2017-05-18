<?php

$app_head_include = '';

/* don't include it when the request was from inside app */
$in_app = tt_getenv('HTTP_IN_APP');
$referer = tt_getenv('HTTP_REFERER');
if ($in_app || preg_match('#^https?://link.tapatalk.com#i', $referer))
    return;

/* forum info */
$tapatalk_dir_name = isset($tapatalk_dir) ? $tapatalk_dir : basename(dirname(dirname(__FILE__)));
$is_mobile_skin = isset($is_mobile_skin) && $is_mobile_skin ? 1 : 0;
$page_type = isset($page_type) && $page_type ? $page_type : 'other';    /* valid page_type: index, forum, topic, post, pm, search, profile, online, other*/
$app_forum_name = isset($app_forum_name) && trim($app_forum_name) ? html_entity_decode(trim($app_forum_name)) : 'this forum';
$board_url = isset($board_url) ? preg_replace('#/$#', '', trim($board_url)) : '';
$app_icon_url = isset($app_icon_url) && $app_icon_url ? $app_icon_url : $board_url . "/$tapatalk_dir_name/smartbanner/images/tapatalk-banner-logo.png";

/* byo app info */
$app_rebranding_id = isset($app_rebranding_id) ? intval($app_rebranding_id) : 0;
if ($app_rebranding_id)
{
    $is_byo = 1;
    $app_url_scheme = isset($app_url_scheme) && $app_url_scheme ? $app_url_scheme : 'ttbyo-'.$app_rebranding_id;
    $app_alert_status = isset($app_alert_status) && $app_alert_status ? 1 : 0;
    $app_name = isset($app_name) && $app_name ? $app_name : '';
    if(isset($app_ios_id) && intval($app_ios_id))
    {
        $is_ios_byo = 1;
        $app_ios_id = intval($app_ios_id);
        $app_ios_name = $app_name;
        $app_ios_icon_url = $app_icon_url;
        $app_ios_url_scheme = $app_url_scheme;
        $twc_image = $app_icon_url;
    }
    else
    {
        $is_ios_byo = 0;
        $app_ios_id = '307880732';
        $app_ios_name = 'Tapatalk';
        $app_ios_icon_url = $board_url . "/$tapatalk_dir_name/smartbanner/images/tapatalk-banner-logo.png";
        $app_ios_url_scheme = 'tapatalk';
    }
    if(isset($app_android_id) && trim($app_android_id))
    {
        $is_android_byo = 1;
        $app_android_id = trim($app_android_id);
        $app_android_name = $app_name;
        $app_android_icon_url = $app_icon_url;
        $app_android_url_scheme = $app_url_scheme;
        $twc_image = $app_icon_url;
    }
    else
    {
        $is_android_byo = 0;
        $app_android_id = 'com.quoord.tapatalkpro.activity';
        $app_android_name = 'Tapatalk';
        $app_android_icon_url = $board_url . "/$tapatalk_dir_name/smartbanner/images/tapatalk-banner-logo.png";
        $app_android_url_scheme = 'tapatalk';
    }
}
/* tapatalk app info */
else
{
    $is_byo = $app_alert_status = $is_ios_byo = $is_android_byo = 0;
    $app_ios_id = '307880732';
    $app_ios_name = 'Tapatalk';
    $app_icon_url = $board_url . "/$tapatalk_dir_name/smartbanner/images/tapatalk-banner-logo.png";
    $app_ios_icon_url = $board_url . "/$tapatalk_dir_name/smartbanner/images/tapatalk-banner-logo.png";
    $app_ios_url_scheme = 'tapatalk';
    $app_android_id = 'com.quoord.tapatalkpro.activity';
    $app_android_name = 'Tapatalk';
    $app_android_icon_url = $board_url . "/$tapatalk_dir_name/smartbanner/images/tapatalk-banner-logo.png";
    $app_android_url_scheme = 'tapatalk';
}

/* app deep link url and banner/alert message */
$app_banner_enable = !isset($app_banner_enable) || $app_banner_enable ? 1 : 0;
$app_location = isset($app_location) ? trim($app_location) : '';
$app_ios_location_url = $app_ios_url_scheme . '://' . $app_location;
$app_android_location_url = $app_android_url_scheme . '://' . $app_location;
$app_banner_message = isset($app_banner_message) && trim($app_banner_message) ? preg_replace('/\s+/s', ' ', trim($app_banner_message)) : '';
$app_banner_message_android = isset($app_banner_message_android) && trim($app_banner_message_android) ? preg_replace('/\s+/s', ' ', trim($app_banner_message_android)) : '';
$app_banner_message_ios = isset($app_banner_message_ios) && trim($app_banner_message_ios) ? preg_replace('/\s+/s', ' ', trim($app_banner_message_ios)) : '';
$app_alert_message = isset($app_alert_message) && trim($app_alert_message) ? preg_replace('/\s+/s', ' ', trim($app_alert_message)) : '';
$app_banner_view_button = isset($app_banner_view_button) && trim($app_banner_view_button) ? trim($app_banner_view_button) : '';
$google_indexing_enabled = !isset($google_indexing_enabled) ? true : $google_indexing_enabled;
$facebook_indexing_enabled = !isset($facebook_indexing_enabled) ? true : $facebook_indexing_enabled;
$twitter_indexing_enabled = !isset($twitter_indexing_enabled) ? true : $twitter_indexing_enabled;


/* add google/facebook/twitter meta */
if (in_array($page_type, array('topic', 'post', 'home')) && $app_location)
{
    if ($google_indexing_enabled)
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
    if($facebook_indexing_enabled)
    {
        /* display facebook deeping link */
        $app_head_include .= '
        <meta property="al:android:package" content="'.tt_html_escape($app_android_id).'" />
        <meta property="al:android:url" content="'.tt_html_escape(tt_add_channel($app_android_location_url, 'facebook-indexing')).'" />
        <meta property="al:android:app_name" content="'.tt_html_escape($app_android_name).'" />
        <meta property="al:ios:url" content="'.tt_html_escape(tt_add_channel($app_ios_location_url, 'facebook-indexing')).'" />
        <meta property="al:ios:app_store_id" content="'.tt_html_escape($app_ios_id).'" />
        <meta property="al:ios:app_name" content="'.tt_html_escape($app_ios_name).'" />
        ';
    }
    if ($twitter_indexing_enabled)
    {
        $twc_title = isset($twc_title) && $twc_title ? $twc_title : '';
        $twc_site =  isset($twc_site) && $twc_site ? $twc_site : 'tapatalk';
        $twc_description = isset($twc_description) && $twc_description ? $twc_description : $app_forum_name;

    /* display twitter card */
        $app_head_include .= '
        <!-- twitter app card start-->
        <!-- https://dev.twitter.com/docs/cards/types/app-card -->
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:site" content="@' . tt_html_escape($twc_site,true) . '" />
        <meta name="twitter:title" content="'.tt_html_escape($twc_title,true).'" />
        <meta name="twitter:description" content="'.tt_html_escape($twc_description,true).'" />
        ';

        if (isset($twc_image) && strpos($twc_image, 'tapatalk-banner-logo') === false)
        {
            $app_head_include .= '<meta name="twitter:image" content="'.tt_html_escape($twc_image,true).'" />';
        }

        $app_head_include .= '
        <meta name="twitter:app:id:iphone" content="'.tt_html_escape($app_ios_id).'" />
        <meta name="twitter:app:url:iphone" content="'.tt_html_escape(tt_add_channel($app_ios_location_url, 'twitter-indexing')).'" />
        <meta name="twitter:app:id:ipad" content="'.tt_html_escape($app_ios_id).'" />
        <meta name="twitter:app:url:ipad" content="'.tt_html_escape(tt_add_channel($app_ios_location_url, 'twitter-indexing')).'" />
        <meta name="twitter:app:id:googleplay" content="'.tt_html_escape($app_android_id).'" />
        <meta name="twitter:app:url:googleplay" content="'.tt_html_escape(tt_add_channel($app_android_location_url, 'twitter-indexing')).'" />
        <!-- twitter app card -->
        ';
    }
}
/* don't include it when the request was not from mobile device */
$useragent = tt_getenv('HTTP_USER_AGENT');
if (!preg_match('/iPhone|iPod|iPad|Silk|Android|IEMobile|Windows Phone|Windows RT.*?ARM/i', $useragent))
    return;

/* don't show banner for googlebot or twitterbot */
if (tt_crawlerDetect($useragent))
    return;

if (isset($_GET['display_banner']) && $_GET['display_banner'])
    $app_banner_enable = 1;

/* don't include js if banner was disabled */
if (empty($app_banner_enable) && empty($app_alert_status))
    return;

/* display smart banner */
$app_banner_head = '';
if (file_exists(dirname(__FILE__) . '/appbanner.js'))
{
    $app_banner_js_link = $board_url . '/' . tt_html_escape($tapatalk_dir_name) .'/smartbanner/appbanner.js?v=5.2';

    $app_banner_head = '
        <!-- Tapatalk Banner head start -->
        <script type="text/javascript">
            var is_mobile_skin     = '.$is_mobile_skin.';
            var tapatalk_dir_name  = "'.tt_html_escape($tapatalk_dir_name,true).'";
            var app_forum_name     = "'.tt_html_escape($app_forum_name, true).'";
            var app_board_url      = "'.tt_html_escape($board_url, true).'";
            var app_banner_enable  = '.$app_banner_enable.';
            var app_location       = "'.tt_html_escape($app_location, true).'";
            var is_byo             = '.$is_byo.';
            var is_android_byo     = '.$is_android_byo.';
            var is_ios_byo         = '.$is_ios_byo.';
            var app_alert_status   = '.$app_alert_status.';
            var app_alert_message  = "'.tt_html_escape($app_alert_message, true).'";
            var app_ios_id         = "'.$app_ios_id.'";
            var app_ios_name           = "'.tt_html_escape($app_ios_name, true).'";
            var app_ios_icon_url       = "'.tt_html_escape($app_ios_icon_url, true).'";
            var app_ios_url_scheme     = "'.tt_html_escape($app_ios_url_scheme).'";
            var app_android_id     = "'.tt_html_escape($app_android_id).'";
            var app_android_name           = "'.tt_html_escape($app_android_name, true).'";
            var app_android_icon_url       = "'.tt_html_escape($app_android_icon_url, true).'";
            var app_android_url_scheme     = "'.tt_html_escape($app_android_url_scheme).'";
            var app_banner_message = "'.tt_html_escape($app_banner_message, true).'";
            var app_banner_message_android = "'.tt_html_escape($app_banner_message_android, true).'";
            var app_banner_message_ios = "'.tt_html_escape($app_banner_message_ios, true).'";
            var app_banner_view_button = "'.tt_html_escape($app_banner_view_button, true).'";
        </script>
        <script src="' . tt_html_escape($app_banner_js_link) . '" type="text/javascript"></script>
        <!-- Tapatalk Banner head end-->
    ';
}

$app_head_include .= $app_banner_head;
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

function tt_is_https()
{
    return (isset($_SERVER['HTTPS']) && trim($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
}

function tt_html_escape($str, $special = false)
{
    $str = addslashes(htmlspecialchars($str, ENT_NOQUOTES, "UTF-8"));
    if($special)
    {
        $str = str_replace('&amp;', '&', $str);
    }
    return $str;
}

function tt_add_channel($url, $channel)
{
    if (strpos($url, '?') === false)
        $url .= "?channel=$channel";
    else
        $url .= "&channel=$channel";

    return $url;
}