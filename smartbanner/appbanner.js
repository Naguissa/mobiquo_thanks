// add document ready function
var add_app_event = function (fn) {
    if (document.addEventListener) {
        document.addEventListener("DOMContentLoaded", function () {
            document.removeEventListener("DOMContentLoaded", arguments.callee, false);
            fn();
        }, false);
    }
}

// ---- params check ----
if (typeof (app_banner_enable) == "undefined") var app_banner_enable = 1;
if (typeof (is_mobile_skin) == "undefined") var is_mobile_skin = false;
if (typeof (tapatalk_dir_name) == "undefined") var tapatalk_dir_name = 'mobiquo';
if (typeof (app_forum_name) == "undefined") var app_forum_name = "this forum";
if (typeof (app_location) == "undefined") var app_location = '';
if (typeof (app_board_url) == "undefined") var app_board_url = '';
if (typeof (app_icon_url) == "undefined" || !app_icon_url) var app_icon_url = app_board_url + '/' + tapatalk_dir_name + '/smartbanner/images/tapatalk-banner-logo.png';

if (typeof (is_byo) == "undefined") var is_byo = 0;
if (typeof (app_alert_status) == "undefined") var app_alert_status = 0;
if (typeof (app_alert_message) == "undefined") var app_alert_message = '{your_forum_name} mobile app is now available on iPhone and Android. Download it now?';
if (typeof (app_android_id) == "undefined") var app_android_id = '';
if (typeof (app_android_name) == "undefined" || !app_android_name) var app_android_name = 'App';
if (typeof (app_android_icon_url) == "undefined" || !app_android_icon_url) var app_android_icon_url = app_icon_url;
if (typeof (app_android_url_scheme) == "undefined" || !app_android_url_scheme) var app_android_url_scheme = 'tapatalk';
if (typeof (app_ios_id) == "undefined") var app_ios_id = '';
if (typeof (app_ios_name) == "undefined" || !app_ios_name) var app_ios_name = 'App';
if (typeof (app_ios_icon_url) == "undefined" || !app_ios_icon_url) var app_ios_icon_url = app_icon_url;
if (typeof (app_ios_url_scheme) == "undefined" || !app_ios_url_scheme) var app_ios_url_scheme = 'tapatalk';
if (typeof (app_banner_message) == "undefined" || !app_banner_message) var app_banner_message = "Follow {your_forum_name} <br /> with {app_name} for [os_platform]";
if (typeof (app_banner_message_android) == "undefined" || !app_banner_message_android) var app_banner_message_android = app_banner_message;
if (typeof (app_banner_message_ios) == "undefined" || !app_banner_message_ios) var app_banner_message_ios = app_banner_message;

if (typeof (app_banner_view_button) == "undefined" || !app_banner_view_button) var app_banner_view_button = "VIEW";

var is_android = false;
var is_ios = false;
var is_wp = false;
tapatalkBrowserDetect();
if (is_ios) {
    var app_location_url = app_ios_url_scheme + '://' + app_location;
}
else if(is_android) {
    var app_location_url = app_android_url_scheme + '://' + app_location;
}



// Support native iOS Smartbanner
var native_ios_banner = false;
if (app_ios_id && navigator.userAgent.match(/Safari/i) != null && app_banner_enable &&
    (navigator.userAgent.match(/CriOS/i) == null && window.Number(navigator.userAgent.substr(navigator.userAgent.indexOf('OS ') + 3, 3).replace('_', '.')) >= 6)) {
    if (navigator.userAgent.match(/iPod|iPhone|iPad/i) != null) {
        var meta = document.createElement('meta');
        meta.name = "apple-itunes-app";
        if (is_byo) {
            meta.content = "app-id=" + app_ios_id + ", app-argument=" + app_location_url;
        }
        else {
            meta.content = "app-id=" + app_ios_id + ", affiliate-data=at=10lR7C, app-argument=" + app_location_url;
        }
        document.getElementsByTagName('head')[0].appendChild(meta);
        native_ios_banner = true;
    }
}

// initialize app download url
if (is_byo) {
    var app_install_url = 'https://tapatalk.com/m/?id=6';
    if (app_ios_id) app_install_url = app_install_url + '&app_ios_id=' + app_ios_id;
    if (app_android_id) app_install_url = app_install_url + '&app_android_id=' + app_android_id;
    if (app_board_url) app_install_url = app_install_url + '&referer=' + app_board_url;
}
else
    var app_install_url = 'https://tapatalk.com/m/?id=6&referer=' + encodeURIComponent(app_location);

// for those forum system which can not add js in html body
add_app_event(tapatalkDetectAfterLoad)


var bannerLoaded = false
var bannerScale
var bannerHeight
var tapatalk_logo_height
function tapatalkDetectAfterLoad() {

    tapatalkDetect(true);
    // display byo app alert
    if (navigator.cookieEnabled
        && app_alert_status
        && app_alert_message
        && is_byo
        && ((is_ios && is_ios_byo) || (is_android && is_android_byo))
        && (typeof (navigator.standalone) == "undefined" || !navigator.standalone)
        && document.cookie.indexOf("byo-alert-closed=true") < 0
        ) {
        byo_alert_message = app_alert_message.replace(/<br \/> /gi, '');
        setBannerCookies('byo-alert-closed', 'true', 1000);
        if (confirm(byo_alert_message)) {
            openOrInstall();
        }
    }
}
function tapatalkBrowserDetect()
{
    if (navigator.userAgent.match(/iPhone|iPod/i) || navigator.userAgent.match(/iPad/i)) {
        is_ios = true;
    }
    else if (navigator.userAgent.match(/Android/i)) {
        is_android = true;
    }
    else if (navigator.userAgent.match(/IEMobile|Windows Phone/i)) {
        is_wp = true;
    }
}
function tapatalkDetect(afterLoad) {
    if (bannerLoaded) return;

    if (app_ios_id && navigator.userAgent.match(/iPhone|iPod/i)) {
        if (is_byo && is_ios_byo) {
            app_banner_message = app_banner_message_ios.replace(/\[os_platform\]/gi, 'iPhone');
        }
        else {
            app_banner_message = app_banner_message.replace(/\[os_platform\]/gi, 'iPhone');
        }
    }
    else if (app_ios_id && navigator.userAgent.match(/iPad/i)) {
        if (is_byo && is_ios_byo) {
            app_banner_message = app_banner_message_ios.replace(/\[os_platform\]/gi, 'iPad');
        }
        else {
            app_banner_message = app_banner_message.replace(/\[os_platform\]/gi, 'iPad');
        }
    }
    else if (app_android_id && navigator.userAgent.match(/Android/i)) {
        if (is_byo && is_android_byo) {
            app_banner_message = app_banner_message_android.replace(/\[os_platform\]/gi, 'Android');
        }
        else {
            app_banner_message = app_banner_message.replace(/\[os_platform\]/gi, 'Android');
        }
    }
    else if (!is_byo && navigator.userAgent.match(/IEMobile|Windows Phone/i)) {
        app_banner_message = app_banner_message.replace(/\[os_platform\]/gi, 'Windows Phone');
    }
    else
        return

    // work only when browser support cookie
    if (!navigator.cookieEnabled
        || !app_banner_enable
        || bannerLoaded
        || navigator.standalone
        || document.cookie.indexOf("banner-closed=true") >= 0
        || native_ios_banner)
        return

    // build up real banner/alert content
    if (window.screen.width < 600 && app_forum_name.length > 20) {
        app_forum_name = "this forum";
    }
    else if (app_forum_name.length > 40 && window.screen.width >= 600) {
        app_forum_name = app_forum_name.substr(0, 40) + "...";
    }

    app_banner_message = app_banner_message.replace(/\{your_forum_name\}/gi, app_forum_name);
    if (is_ios) {
        app_banner_message = app_banner_message.replace(/\{app_name\}/gi, app_ios_name);
        app_icon_url = app_ios_icon_url;
    }
    else if (is_android) {
        app_banner_message = app_banner_message.replace(/\{app_name\}/gi, app_android_name);
        app_icon_url = app_android_icon_url;
    }

    bannerLoaded = true
    getBannerScale();

    //init css style
    tapatalk_link = document.createElement("link");
    tapatalk_link.href = app_board_url + '/' + tapatalk_dir_name + '/smartbanner/appbanner.css';
    tapatalk_link.type = "text/css";
    tapatalk_link.rel = "stylesheet";
    document.getElementsByTagName("head")[0].appendChild(tapatalk_link);
    style_mobile_banner = 'position:fixed;margin:0;padding:0;top:0;left:0;right:0;width:100%;font-size:1em;z-index:2147483647;color:#000000;    background-color: #f2f2f2;text-align:left;';
    style_mobile_banner_heading = 'font-size:1.75em;padding:0;line-height:1.3em;margin:0;text-align:left;color:#000000;';
    style_mobile_banner_heading_android = style_mobile_banner_heading + 'font-family: Roboto;font-weight:normal;';
    style_mobile_banner_heading_ios = style_mobile_banner_heading + 'font-family: Helvetica;font-weight:normal;';
    style_mobile_banner_app_desc = 'font-family: Roboto;font-size:1.75em;font-weight:300;color:#000000;';
    style_mobile_banner_app_desc_ios = style_mobile_banner_app_desc + 'font-family: Helvetica;font-size:1.75em;font-weight:300;color:#000000;'
    style_mobile_banner_open = 'background-color:#32c7e7;color:#ffffff;font-family: Roboto;';
    style_mobile_banner_open_ios = 'background-color:#f2f2f2;color:#007aff;font-family: Helvetica;border:1px solid #007aff;';

    bodyItem = document.body
    appBanner = document.createElement("div")
    appBanner.id = "tt_mobile_banner"
    if (is_android) {
        //class_ext = '_android';
        app_desc = 'FREE - on Google Play';
        var css = '@import url(https://fonts.googleapis.com/css?family=Roboto:100,100italic,300,300italic,400,400italic,500,500italic,700,700italic,900,900italic&subset=latin,latin-ext,cyrillic,cyrillic-ext,greek-ext,greek,vietnamese);' +
            '@import url(https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300italic,400,400italic,700,700italic&subset=latin,latin-ext,cyrillic-ext,cyrillic,greek-ext,greek,vietnamese);' +
            '@import url(https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700&subset=latin,latin-ext,greek-ext,greek,vietnamese,cyrillic,cyrillic-ext);';
        tapatalk_style = document.createElement('style');
        tapatalk_head = document.head || document.getElementsByTagName('head')[0],
        tapatalk_style.type = 'text/css';
        if (tapatalk_style.styleSheet) {
            tapatalk_style.styleSheet.cssText = css;
        } else {
            tapatalk_style.appendChild(document.createTextNode(css));
        }
        tapatalk_head.appendChild(tapatalk_style);
        style_mobile_banner_heading = style_mobile_banner_heading_android
    }
    else if (is_ios) {
        //class_ext = '_ios';
        app_desc = 'FREE - on App Store';
        style_mobile_banner_heading = style_mobile_banner_heading_ios;
        style_mobile_banner_app_desc = style_mobile_banner_app_desc_ios;
        style_mobile_banner_open = style_mobile_banner_open_ios;
    }
    else if (is_wp) {
        return;
        app_desc = 'FREE - on WP App Store';
    }
    else {
        app_desc = '';
        //class_ext = ''
    }

    appBanner.className = 'mobile_banner_tt';
    appBanner.style = style_mobile_banner;

    if (!isMobileStyle()) {
        tapatalk_logo_height = 8 * 8 * bannerScale;
        appBanner.innerHTML =
            '<table class="mobile_banner_inner" style="border-width:0;table-layout:auto;background-color:#f2f2f2;margin:0;width:auto;border-collapse:separate;padding:1.5em 0;position:relative;margin-left:auto;margin-right:auto;line-height:normal;border:0px none;vertical-align: middle;" align="center" cellpadding="0" cellspacing="0" border="0"  id="mobile_banner_inner" >' +
               '<tr style="border:0px none;padding:0;margin:0;">' +
              '<td style="padding:0;margin:0;width:2.5em; border:0px none;vertical-align: middle;line-height:normal;"> ' +
                  '<div onclick="closeBanner()" id="mobile_banner_close" style="cursor:pointer;text-align:right;margin:0;padding:0;overflow:hidden;color:rgb(121,121,121);"><img style="width:2.0em;opacity:0.5;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAx0lEQVR4AdXXMQqDQBCGUSvvkjQ5iOBFhYCQy1mZLRZsBh/8zSbCV8nsa0TH6aeu1/Mxt7bW0prCln7GXN2vBubW3jpbh/Ab9Ohn7BV+h57AifZKHKhxozUOVLjRGr/gDQPCV6K9bk0YBJ7Nrh3OD0jRC07xAC1g4MyoYOMxath4iho2HqCGjRv9S9hoiI9/uHI0w3M0xIe+Mod/JFI0n+UiYDTBN6w+QIl79TFu1HhHtd4aNe71tsA/Ro17oa/xN1Di/oUZ0BcewHSWZrEeJgAAAABJRU5ErkJggg==" /></div></td>' +
              '<td style="padding:0;margin:0;width:1.0em; border:0px none;vertical-align: middle;line-height:normal;"></td>' +
              '<td style="padding:0;margin:0;min-width:8.0em;border:0px none;vertical-align: middle;line-height:normal;">' +
                '<div id="mobile_banner_logo" style="text-align:left"><img style="max-height:' + tapatalk_logo_height + 'px" id="mobile_banner_logo_img" src="' + app_icon_url + '"></div>' +
              '</td>' +
              '<td style="padding:0;margin:0;width:1.0em;border:0px none;vertical-align: middle;line-height:normal;"></td>' +
              '<td style="padding:0;margin:0;min-width:22em;border:0px none;vertical-align: middle;line-height:normal;">' +
                '<table style="border-width:0;table-layout:auto;background-color:#f2f2f2;padding:0;margin:0;min-width:22em;border-collapse:separate;" cellpadding="0" cellspacing="0" border="0">' +
                    '<tr style="padding:0;margin:0;border:0px none;">' +
                        '<td style="padding:0;margin:0;border:0px none;vertical-align: middle;line-height:normal;">' +
                            '<div style="' + style_mobile_banner_heading + '" >' + app_banner_message + '</div>' +
                        '</td>' +
                    '</tr>' +
                    '<tr style="padding:0;margin:0;border:0px none;">' +
                        '<td style="padding:0;margin:0;border:0px none;vertical-align: middle;line-height:normal;">' +
                            '<div><img style="width:7.9em;max-height:1.4em" src="' + app_board_url + '/' + tapatalk_dir_name + '/smartbanner/images/star.png' + '"></div>' +
                        '</td>' +
                    '</tr>' +
                    '<tr style="padding:0;margin:0;border:0px none;">' +
                        '<td style="padding:0;margin:0;border:0px none;vertical-align: middle;line-height:normal;">' +
                            '<div style="' + style_mobile_banner_app_desc + '" >' + app_desc + '</div>' +
                        '</td>' +
                    '</tr>' +
                 '</table>' +
              '</td>' +
              '<td style="padding:0;margin:0;width:2.0em;border:0px none;vertical-align: middle;line-height:normal;"></td>' +
              '<td style="padding:0;margin:0;width:8.0em;border:0px none;vertical-align: middle;line-height:normal;">' +
                     '<a style="display: inline-block;width:100%;text-decoration:none;font-size:1.75em;line-height:2.2em;margin:0;position:relative;border-radius:0.2em;z-index:100;background-color:#32c7e7;color:#ffffff;cursor:pointer;text-align:center;padding:0;' +
                      style_mobile_banner_open
                      + '"  href="javascript:openOrInstall()" id="mobile_banner_open">' + app_banner_view_button + '</a>' +
              '</td>' +
              '<td style="padding:0;margin:0;width:1.5em;border:0px none;vertical-align: middle;line-height:normal;"></td>' +
             '</tr>' +
            '</table>';
        bannerHeight = tapatalk_logo_height + 3 * 8 * bannerScale;
    }
    else {
        tapatalk_logo_height = 8 * 8 * bannerScale * 0.67;
        bannerHeight = tapatalk_logo_height + 1.5 * 8 * bannerScale;
        appBanner.innerHTML =
            '<table class="mobile_banner_inner" style="border-width:0;background-color:#f2f2f2;table-layout:auto;margin:0;width:auto;border-collapse:separate;padding:0.75em 0;position:relative;margin-left:auto;margin-right:auto;line-height:normal;border:0px none;vertical-align: middle;" align="center" cellpadding="0" cellspacing="0" border="0"  id="mobile_banner_inner" >' +
               '<tr style="border:0px none;padding:0;margin:0;">' +
              '<td style="padding:0;margin:0;width:0.8em; border:0px none;vertical-align: middle;line-height:normal;"> ' +
                  '<div onclick="closeBanner()" id="mobile_banner_close" style="cursor:pointer;text-align:right;margin:0;padding:0;overflow:hidden;color:rgb(121,121,121);"><img style="width:0.8em;opacity:0.5;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAx0lEQVR4AdXXMQqDQBCGUSvvkjQ5iOBFhYCQy1mZLRZsBh/8zSbCV8nsa0TH6aeu1/Mxt7bW0prCln7GXN2vBubW3jpbh/Ab9Ohn7BV+h57AifZKHKhxozUOVLjRGr/gDQPCV6K9bk0YBJ7Nrh3OD0jRC07xAC1g4MyoYOMxath4iho2HqCGjRv9S9hoiI9/uHI0w3M0xIe+Mod/JFI0n+UiYDTBN6w+QIl79TFu1HhHtd4aNe71tsA/Ro17oa/xN1Di/oUZ0BcewHSWZrEeJgAAAABJRU5ErkJggg==" /></div></td>' +
              '<td style="padding:0;margin:0;width:0.5em; border:0px none;vertical-align: middle;line-height:normal;"></td>' +
              '<td style="padding:0;margin:0;min-width:4.0em;border:0px none;vertical-align: middle;line-height:normal;">' +
                '<div id="mobile_banner_logo" style="text-align:left"><img style="max-height:' + tapatalk_logo_height + 'px" id="mobile_banner_logo_img" src="' + app_icon_url + '"></div>' +
              '</td>' +
              '<td style="padding:0;margin:0;width:1.0em;border:0px none;vertical-align: middle;line-height:normal;"></td>' +
              '<td style="padding:0;margin:0;min-width:11em;border:0px none;vertical-align: middle;line-height:normal;">' +
                '<table style="border-width:0;table-layout:auto;background-color:#f2f2f2;padding:0;margin:0;min-width:11em;border-collapse:separate;" cellpadding="0" cellspacing="0" border="0">' +
                    '<tr style="padding:0;margin:0;border:0px none;">' +
                        '<td style="padding:0;margin:0;border:0px none;vertical-align: middle;line-height:normal;">' +
                            '<div style="' + style_mobile_banner_heading + 'font-size:1.1em" >' + app_banner_message + '</div>' +
                        '</td>' +
                    '</tr>' +
                    '<tr style="padding:0;margin:0;border:0px none;">' +
                        '<td style="padding:0;margin:0;border:0px none;vertical-align: middle;line-height:normal;">' +
                            '<div><img style="max-width:5.0em;max-height:1em" src="' + app_board_url + '/' + tapatalk_dir_name + '/smartbanner/images/star.png' + '"></div>' +
                        '</td>' +
                    '</tr>' +
                    '<tr style="padding:0;margin:0;border:0px none;">' +
                        '<td style="padding:0;margin:0;border:0px none;vertical-align: middle;line-height:normal;">' +
                            '<div style="' + style_mobile_banner_app_desc + 'font-size:1.0em" >' + app_desc + '</div>' +
                        '</td>' +
                    '</tr>' +
                 '</table>' +
              '</td>' +
              '<td style="padding:0;margin:0;width:1.0em;border:0px none;vertical-align: middle;line-height:normal;"></td>' +
              '<td style="padding:0;margin:0;width:5.0em;border:0px none;vertical-align: middle;line-height:normal;">' +
                     '<a style="display: inline-block;width:100%;text-decoration:none;font-size:1.2em;line-height:2.2em;margin:0;position:relative;border-radius:0.2em;z-index:100;background-color:#32c7e7;color:#ffffff;cursor:pointer;text-align:center;padding:0;' +
                      style_mobile_banner_open
                      + '"  href="javascript:openOrInstall()" id="mobile_banner_open">' + app_banner_view_button + '</a>' +
              '</td>' +
              '<td style="padding:0;margin:0;width:1.0em;border:0px none;vertical-align: middle;line-height:normal;"></td>' +
             '</tr>' +
            '</table>';
    }

    bodyItem.insertBefore(appBanner, bodyItem.firstChild)
    setFontSize(1)

    resetBannerStyle();

    //if (navigator.userAgent.match(/chrome/i) && is_android) {
    //    open_or_install_button = document.getElementById("mobile_banner_open");
    //    version = parseInt(window.navigator.appVersion.match(/Chrome\/(\d+)\./i)[1], 10);
    //    if (version > 25) {
    //        banner_location_url = "intent://" + app_location + "#Intent;scheme=" + app_android_url_scheme + ";package=" + app_android_id + ";end";
    //        open_or_install_button.href = banner_location_url;
    //    }
    //}
    //Detect whether device supports orientationchange event, otherwise fall back to
    var supportsOrientationChange = "onorientationchange" in window,
        orientationEvent = supportsOrientationChange ? "orientationchange" : "resize";

    window.addEventListener(orientationEvent, function () {
        getBannerScale();
        tapatalk_logo_height = 8 * 8 * bannerScale;
        setFontSize(1);
        bannerLogo = document.getElementById("mobile_banner_logo_img");
        bannerDiv = document.getElementById("banner_div_empty");
        bannerLogo.style.height = tapatalk_logo_height + 'px';
        bannerHeight = appBanner.clientHeight;
        bannerDiv.style.height = bannerHeight + "px";
    });
    if (typeof onTapatalkBannerShow == 'function') {
        onTapatalkBannerShow();
    }
}

function setFontSize(Scale) {
    if (bannerScale > 1) {
        appBanner.style.fontSize = (8 * bannerScale * Scale) + "px";
        tables = appBanner.getElementsByTagName("table");
        for (var i = 0; i < tables.length; i++) {
            table = tables[i];
            table.style.fontSize = (8 * bannerScale * Scale) + "px";
            tds = table.getElementsByTagName("td");
            for (var j = 0; j < tds.length; j++) {
                tds[j].style.fontSize = (8 * bannerScale * Scale) + "px";
            }
        }
    }
}

function getBannerScale() {
    bannerScale = document.body.clientWidth / window.screen.width
    if (bannerScale == 1 || isMobileStyle()) {
        bannerScale = 1.5;
        return;
    }
    if (bannerScale < 1.5 || (is_mobile_skin && navigator.userAgent.match(/mobile/i))) bannerScale = 1.5;

    // mobile portrait mode may need bigger scale
    if (window.innerWidth < window.innerHeight) {
        if (bannerScale < 2.0 && !is_mobile_skin && document.body.clientWidth > 600) {
            bannerScale = 1.5
        }
        else if (bannerScale < 2.5) {
            bannerScale = 2.0
        }
    }
    else {
        if (navigator.userAgent.match(/mobile/i) && bannerScale < 1.5 && !is_mobile_skin && document.body.clientWidth > 600) {
            bannerScale = 1.5
        }
    }

    if (bannerScale > 2.5) bannerScale = 2.5;
}

function isMobileStyle() {
    /*check if is mobile style*/
    metas = document.getElementsByTagName("meta");
    var is_mobile_style = false
    for (i = 0; i < metas.length; i++) {
        if (metas[i].name && metas[i].name.toLowerCase() == 'viewport') {
            meta_content = metas[i].content;
            re = /width\s?=\s?device\-width/i;
            if ((re.test(meta_content))) {
                is_mobile_style = true;
            }
        }
    }
    if (document.body.clientWidth < 600) {
        is_mobile_style = true;
    }
    return is_mobile_style;
}

function openOrInstall() {
    iframe = document.createElement("iframe");
    iframe.id = 'open_in_app';
    document.body.insertBefore(iframe, document.body.firstChild);
    iframe.style.display = "none";

    iframe.src = app_location_url;
    setTimeout(function () {
        window.location.href = app_install_url;
    }, 1);
}

function resetBannerTop() {
    if (getComputedStyle(bodyItem, null).position !== 'static' || document.getElementById('google_translate_element'))
        appBanner.style.top = '-' + bannerTop
}

function closeBanner() {
    bannerDiv = document.getElementById("banner_div_empty");
    bodyItem.removeChild(appBanner);
    bodyItem.removeChild(bannerDiv);
    setBannerCookies('banner-closed', 'true', 90);
    if (typeof onTapatalkBannerClosed == 'function') {
        onTapatalkBannerClosed();
    }
}

function setBannerCookies(name, value, exdays) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + exdays);
    value = escape(value) + ((exdays == null) ? '' : '; expires=' + exdate.toUTCString());
    document.cookie = name + '=' + value + '; path=/;';
}

add_app_event(gestureChangeListener);

function gestureChangeListener() {
    appBanner = document.getElementById("tt_mobile_banner");
    if (appBanner == undefined) {
        return;
    }
    document.addEventListener("touchmove", touchMove, false);
    document.addEventListener("touchend", touchEnd, false);
    touchEnd();
}

function touchMove() {
    touchEnd();
}

function touchEnd() {
    resetBannerStyle();
}

function resetBannerStyle() {
    appBanner = document.getElementById("tt_mobile_banner");
    if (appBanner == undefined) {
        return;
    }
    Scale = window.innerWidth / document.body.clientWidth;
    if (Scale > 1) {
        Scale = 1;
    }

    setFontSize(Scale);
    newBannerHeight = bannerHeight * Scale;
    bannerDiv = document.getElementById("banner_div_empty");
    bannerLogo = document.getElementById("mobile_banner_logo_img");
    bannerLogo.style.height = tapatalk_logo_height * Scale + 'px';
    if (bannerDiv == undefined) {
        bannerDiv = document.createElement("div");
        bannerDiv.style.margin = 0;
        bannerDiv.style.padding = 0;
        bannerDiv.id = "banner_div_empty";
        document.body.insertBefore(bannerDiv, bodyItem.firstChild);
    }

    bannerDiv.style.height = newBannerHeight + "px";
}
