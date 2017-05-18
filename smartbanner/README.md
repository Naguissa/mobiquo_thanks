# App Banner & Twitter Card Integration


## Normal Usage ##

First you need to upload the 'smartbanner' directory onto your server in tapatalk plugin directory. Normally it should be at 'mobiquo/smartbanner/'

### Include Twitter App Card support

To get your site support twitter card with your App, you just need to include some meta data in your page source head like below.
What you need to change is the parameters in content section. For more details, [check here](https://dev.twitter.com/docs/cards/types/app-card)

    <html>
        <head>
        ...
        <!-- twitter app card start-->
        <!-- https://dev.twitter.com/docs/cards/types/app-card -->
        <meta name="twitter:card"               content="app">
        <meta name="twitter:app:id:iphone"      content="307880732">
        <meta name="twitter:app:url:iphone"     content="tapatalk://support.tapatalk.com/?user_id=169&location=index">
        <meta name="twitter:app:id:ipad"        content="307880732">
        <meta name="twitter:app:url:ipad"       content="tapatalk://support.tapatalk.com/?user_id=169&location=index">
        <meta name="twitter:app:id:googleplay"  content="com.quoord.tapatalkpro.activity">
        <meta name="twitter:app:url:googleplay" content="tapatalk://support.tapatalk.com/?user_id=169&location=index">
        <!-- twitter app card -->
        ...
        </head>
        ...
    </html>
    
The url parameter above for iphone/ipad/android should be the same, and the format follow the [App Scheme Rules](#app-scheme-rules)


### Include App Banner

The App banner is a brief prompt to users on mobile browser that the site has a native App to work with. 
Also it provides a buttion named 'Open In App' to users who already installed the app to open current page inside the app, and another button named 'Install' to redirect users to download the app in store.
Currently the app banner will work on iOS/Android/Windows Phone devices.
Simply add two pieces of blow html code in head and body will get everything done. For the banner body part, it's better to add it as earlier as in the body section.

    <html>
        <head>
        ...
        <!-- Tapatalk Banner head start -->
        <script type="text/javascript">
            var is_mobile_skin     = 0;
            var tapatalk_dir_name  = "mobiquo";
            var app_forum_name     = "Test forum";
            var app_location       = "";
            var app_board_url      = "http://yoursite.com/forum/";
            var app_banner_enable  = "1";
            
            var is_byo             = 0;
            var app_alert_status   = "1";
            var app_ios_id         = "307880732";
            var app_android_id     = "com.quoord.tapatalkpro.activity";
            var app_name           = "Tapatalk";
            var app_icon_url       = "./mobiquo/smartbanner/images/tapatalk-banner-logo.png";
            var app_url_scheme     = "tapatalk";
            var app_banner_message = "";
            var app_banner_view_button = "";
        </script>
        <script src="mobiquo/smartbanner/appbanner.js" type="text/javascript"></script>
        <!-- Tapatalk Banner head end-->
        ...
        </head>
        
        <body>
        ...
        <!-- Tapatalk Banner body start -->
            <script type="text/javascript">if (typeof(tapatalkDetect) == "function") tapatalkDetect()</script>
        <!-- Tapatalk Banner body end -->
        ...
        </body>
    </html>

**Parameter Specification**

* `is_mobile_skin`: Specify if it's on a mobile skin. App Banner has a little size adjustment for mobile skin
* `tapatalk_dir_name`: optional, the directory where mobiquo files are, if not included, default as 'mobiquo'
* `app_forum_name`: Your forum name
* `app_location`: deep-link url (without scheme) associate with 'Open In App' button, check [App Scheme Rules](#app-scheme-rules)
* `app_board_url`: Your forum url
* `app_banner_enable`: app banner display status
* `is_byo`: is it a BYO site
* `app_alert_status`: for BYO only, if app alert need to popup
* `app_ios_id`: BYO app id in Apple Store.
* `app_android_id`: BYO app package name in Google Play.
* `app_name`: app name
* `app_icon_url`: app icon url
* `app_url_scheme`: app url scheme, for tapatalk it's 'tapatalk'
* `app_banner_message`: The message displayed on App Banner and BYO alert popup. Do not change the [os_platform] tag as it is displayed dynamically based on user's device platform.


## For PHP site only ##

This package provides a simple way to generate banner head html code for php.
Here is the php code samle

    $is_mobile_skin = {this is on a mobile skin};
    $page_type = {current page type}; // valid data: home, forum, topic, post, pm, search, profile, online, other
    $app_forum_name = {forum name};
    $board_url = {forum url to root};
    $app_banner_enable = {smartbanner option status}; // optional, default to be enable
    $app_location = {deep-link url without scheme};
    
    // optional, for BYO only, get from daily request to http://verify.tapatalk.com/forum_info.php
    $app_rebranding_id = {byo rebranding id};
    $app_url_scheme = {byo url scheme};
    $app_alert_status = {byo alert popup status};
    $app_name = {byo app name};
    $app_icon_url = {byo app icon url};
    $app_ios_id = {byo iOS app id};
    $app_android_id = {byo Android app id};
    $app_banner_message = {byo app banner/alert-popup message};

    
    $google_indexing_enabled = {google indexing option status} //optional to enable/disable app indexing, enabled by default
    $facebook_indexing_enabled = {facebook indexing option status} //optional to enable/disable facebook indexing, enabled by default
    $twitter_indexing_enabled = {twitter indexing option status} //optional to enable/disable twitter card, enabled by default
    $twc_title = {page title}
    $twc_description = {page description} // optional
    $twc_image = {page preview image}  // optional
    
    
    if (file_exists($tapatalk_dir . '/smartbanner/head.inc.php'))
        include($tapatalk_dir . '/smartbanner/head.inc.php');
    
    // you'll get $app_head_include set here and you need add it into html head


## App Scheme Rules

**Format:**  
`scheme`://`url-to-forum-root`/?`user_id`={user-id}&`location`={location}&`fid`={fid}&`tid`={tid}&`pid`={pid}&`uid`={uid}&`mid`={mid}

**URL:**  
* **scheme**: app scheme name, default as 'tapatalk'  
* **url-to-forum-root**: used to search if the forum was in app account/history list. If not, search it in tapatalk/byo App network.

**Params: all params are optional**  
* **user_id**: Indicate app should open the content with which account. When there is no account for this forum, open content as guest.  When the user_id was not in one of the accounts for this forum, app side decide open with which account.  
* **location**: Valid value: `index` `forum` `topic` `post` `profile` `message` `online` `search` `login`. Default as `index`.  
* **fid**: Forum board id. **Required** if location is `forum` `topic` `post`  
* **tid**: Topic id. **Required** if location is `topic` `post`  
* **pid**: Post id. **Required** if location is `post`  
* **uid**: User id. **Required** if location is `profile`  
* **mid**: PM id or Conversation id. **Required** if location is `message`  
* **page**: Page number. **Required** if location is `forum``topic``post`  
* **perpage**: Topic/Post number per-page. **Required** if location is `forum``topic`  