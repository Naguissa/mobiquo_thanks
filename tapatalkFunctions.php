<?php
function getPHPBBVersion()
{
       global $phpEx,$config;
       if(substr( $config['version'], 0, 3 ) === '3.2')
       {
           $version = '3.2';
       }
       else if(substr( $config['version'], 0, 3 ) === '3.1')
       {
           $version = '3.1';
       }
       else
       {
           $version = '3.0';
       }
       return $version;
}
function requireExtLibrary($fileName)
{
    global $phpEx,$config;
    $version = getPHPBBVersion();
    require(MBQ_APPEXTENTION_PATH . $version . '/'. $fileName . '.' . $phpEx);
}
function overwriteRequestParam($paramName, $paramValue, $super_global = \phpbb\request\request_interface::REQUEST)
{
    global $request, $config;
	$request->overwrite($paramName,$paramValue, $super_global);
}
function getSystemString($key)
{
    $version = getPHPBBVersion();
    if($version == '3.0')
    {

    }
    else
    {
        global $user;
        return $user->lang[$key];
    }
}
function basic_clean($str)
{
    $str = preg_replace('/<br\s*\/?>/si', "\n", $str);
    $str = strip_tags($str);
    $str = trim($str);
    return html_entity_decode($str, ENT_QUOTES, 'UTF-8');
}


function mobi_parse_requrest()
{
    global $request_method, $request_params, $params_num;

    $ver = phpversion();
    if ($ver[0] >= 5) {
        $data = file_get_contents('php://input');
    } else {
        $data = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
    }

    if (count($_SERVER) == 0)
    {
        $r = new xmlrpcresp('', 15, 'XML-RPC: '.__METHOD__.': cannot parse request headers as $_SERVER is not populated');
        echo $r->serialize('UTF-8');
        exit;
    }

    if(isset($_SERVER['HTTP_CONTENT_ENCODING'])) {
        $content_encoding = str_replace('x-', '', $_SERVER['HTTP_CONTENT_ENCODING']);
    } else {
        $content_encoding = '';
    }

    if($content_encoding != '' && strlen($data)) {
        if($content_encoding == 'deflate' || $content_encoding == 'gzip') {
            // if decoding works, use it. else assume data wasn't gzencoded
            if(function_exists('gzinflate')) {
                if ($content_encoding == 'deflate' && $degzdata = @gzuncompress($data)) {
                    $data = $degzdata;
                } elseif ($degzdata = @gzinflate(substr($data, 10))) {
                    $data = $degzdata;
                }
            } else {
                $r = new xmlrpcresp('', 106, 'Received from client compressed HTTP request and cannot decompress');
                echo $r->serialize('UTF-8');
                exit;
            }
        }
    }
    if(empty($data)) return false;
    $parsers = php_xmlrpc_decode_xml($data);
    $request_method = $parsers->methodname;
    $request_params = php_xmlrpc_decode(new xmlrpcval($parsers->params, 'array'));
    $params_num = count($request_params);
}

function get_short_content($post_id, $length = 200)
{
    global $db;

    $post_id = intval($post_id);
    if (empty($post_id)) return '';

    $sql = 'SELECT post_text,bbcode_uid
            FROM ' . POSTS_TABLE . '
            WHERE post_id = ' . $post_id;
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow();
    $db->sql_freeresult($result);
    $post_text = tapatalk_process_bbcode($row['post_text'], $row['bbcode_uid']);
    return process_short_content($post_text, 200);
}

function process_short_content($post_text, $length = 200)
{
    $post_text = censor_text($post_text);
    $post_text = MbqMain::$oMbqCm->getShortContent($post_text, $length);
    //$array_reg = array(
    //    array('reg' => '/\[quote(.*?)\](.*?)\[\/quote(.*?)\]/si','replace' => '[quote]'),
    //    array('reg' => '/\[code(.*?)\](.*?)\[\/code(.*?)\]/si','replace' => '[code]'),
    //    //array('reg' => '/\[url=(.*?):(.*?)\](.*?)\[\/url(.*?)\]/sei','replace' => '[url]'),
    //    array('reg' => '/\[video(.*?)\](.*?)\[\/video(.*?)\]/si','replace' => '[V]'),
    //    array('reg' => '/\[attachment(.*?)\](.*?)\[\/attachment(.*?)\]/si','replace' => '[attach]'),
    //    array('reg' => '/\[url.*?\].*?\[\/url.*?\]/','replace' => '[url]'),
    //    array('reg' => '/(https?|ftp|mms):\/\/([A-z0-9]+[_\-]?[A-z0-9]+\.)*[A-z0-9]+\-?[A-z0-9]+\.[A-z]{2,}(\/.*)*\/?/is','replace' => '[url]'),
    //    array('reg' => '/\[img.*?\].*?\[\/img.*?\]/','replace' => '[img]'),
    //    array('reg' => '/[\n\r\t]+/','replace' => ' '),
    //    array('reg' => '/\[flash(.*?)\](.*?)\[\/flash(.*?)\]/si','replace' => '[V]'),
    //    array('reg' => '/\[spoiler(.*?)\](.*?)\[\/spoiler(.*?)\]/si','replace' => '[spoiler]'),
    //    array('reg' => '/\[spoil(.*?)\](.*?)\[\/spoil(.*?)\]/si','replace' => '[spoiler]'),
    //);
    //echo $post_text;die();
    //foreach ($array_reg as $arr)
    //{
    //    $post_text = preg_replace($arr['reg'], $arr['replace'], $post_text);
    //}
    //strip_bbcode($post_text);
    //$post_text = html_entity_decode($post_text, ENT_QUOTES, 'UTF-8');
    //$post_text = function_exists('mb_substr') ? mb_substr($post_text, 0, $length) : substr($post_text, 0, $length);
    //$post_text = trim(strip_tags($post_text));
    //$post_text = preg_replace('/\\s+|\\r|\\n/', ' ', $post_text);
    return $post_text;
}

function mobi_url_convert($a,$b)
{
    if(html_entity_decode(trim($a)) == trim($b))
    {
        return '[url]';
    }
    else
    {
        return $b;
    }
}

function post_html_clean($str, $returnHtml = false)
{

    global $phpbb_root_path, $phpbb_home, $mobiquo_config,$config;

    $search = array(
        "/<b>(.*?)<\/b>/si",
        "/<i>(.*?)<\/i>/si",
        "/<u>(.*?)<\/u>/si",
        "/<strong>(.*?)<\/strong>/si",
        "/<em>(.*?)<\/em>/si",
        "/<img .*?src=\"(.*?)\".*?\/?>/si",
         "/<br\s*\/?>|<\/cite>|<\/dt>|<\/dd>/si",
        "/<object .*?data=\"(http:\/\/www\.youtube\.com\/.*?)\" .*?>.*?<\/object>/si",
        "/<object .*?data=\"(http:\/\/video\.google\.com\/.*?)\" .*?>.*?<\/object>/si",
        "/<iframe .*?src=\"(http.*?)\" .*?>.*?<\/iframe>/si",
        "/<script( [^>]*)?>([^<]*?)<\/script>/si",
        "/<param name=\"movie\" value=\"(.*?)\" \/>/si"
    );

    $replace = array(
        '[b]$1[/b]',
        '[i]$1[/i]',
        '[u]$1[/u]',
	'[b]$1[/b]',
        '[i]$1[/i]',
        '[img]$1[/img]',
        "\n",
        '[url=$1]YouTube Video[/url]',
        '[url=$1]Google Video[/url]',
        '[url=$1]$1[/url]',
        '',
        '[url=$1]Flash Video[/url]',
    );

    //$str = preg_replace('/\n|\r/si', '', $str);
    $str = preg_replace('/>\s+</si', '><', $str);
    // remove smile
    /*
    $str = preg_replace('/<img [^>]*?src=\"[^"]*?images\/smilies\/[^"]*?\"[^>]*?alt=\"([^"]*?)\"[^>]*?\/?>/', ' $1 ', $str);
    $str = preg_replace('/<img [^>]*?alt=\"([^"]*?)\"[^>]*?src=\"[^"]*?images\/smilies\/[^"]*?\"[^>]*?\/?>/', ' $1 ', $str);
    */
    $str = censor_text($str);
	$str = bbcode_nl2br($str);
    $str = preg_replace_callback('#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/([^"]*?)\".*?\/><!\-\- s\1 \-\->#', function ($match) {
        global $phpbb_root_path, $phpEx, $config;
        $url = $match[2];

        if(!function_exists("generate_board_url"))
        {
            require_once($phpbb_root_path. '/includes/functions.' . $phpEx);
        }
        $url =generate_board_url() . '/' . $config["smilies_path"] . '/' . $url;
        return '###img src="'. $url .'?ttinline=true"###';
    }, $str);

    $str = preg_replace('/<null.*?\/>/', '', $str);

    $str = preg_replace($search, $replace, $str);
    $str = preg_replace_callback("/<a .*?href=\"(.*?)\"(.*?)?>(.*?)<\/a>/si", function ($match) { return '[url='.url_encode($match[1]).']' . $match[3] . '[/url]';}, $str);


    $str = strip_tags($str);

    $str = preg_replace_callback('/\[code\](.*?)\[\/code\]/si', function ($match) { return '[code]'.base64_encode($match[1]).'[/code]';}, $str);
    $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');

    // remove attach icon image
    $str = preg_replace('/\[img\][^\[\]]+icon_topic_attach\.gif\[\/img\]/si', '', $str);

    // change relative path to absolute URL and encode url
    $str = preg_replace_callback('/\[img\](.*?)\[\/img\]/si', function ($match) { return '[img]'.url_encode($match[1]).'[/img]';}, $str);

    $str = preg_replace('/\[\/img\]\s*/si', "[/img]\n", $str);

    $str = preg_replace('/\[\/img\]\s+\[img\]/si', '[/img][img]', $str);

    // remove link on img
    //$str = preg_replace('/\[url=[^\]]*?\]\s*(\[img\].*?\[\/img\])\s*\[\/url\]/si', '$1', $str);

    // change url to image resource to img bbcode
    $str = preg_replace('/\[url\](http[^\[\]]+\.(jpg|png|bmp|gif))\[\/url\]/si', '[img]$1[/img]', $str);
    $str = preg_replace('/\[url=(http[^\]]+\.(jpg|png|bmp|gif))\]([^\[\]]+)\[\/url\]/si', '[img]$1[/img]', $str);



    // cut quote content to 100 charactors
    if (isset($mobiquo_config['shorten_quote']) && $mobiquo_config['shorten_quote'])
    {
        $str = cut_quote($str, 100);
    }

    $str = parse_bbcode($str, $returnHtml);
    $str = preg_replace('/###img .*?src=\"(.*?)\".*?\/?###/si', '<img src="$1"/>', $str);
    return $str;
}

function parse_bbcode($str, $returnHtml = false)
{
    global $config;
    $search = array(
        '#\[(b)\](.*?)\[/b\]#si',
        '#\[(u)\](.*?)\[/u\]#si',
        '#\[(i)\](.*?)\[/i\]#si',

    );

    if ($returnHtml) {
        $str = htmlspecialchars($str, ENT_NOQUOTES);
        $replace = array(
            '<$1>$2</$1>',
            '<$1>$2</$1>',
            '<$1>$2</$1>',
        );
        $str = str_replace("\n", '<br />', $str);
    } else {
        $replace = array('$2', '$2', '$2', "'$2'");
    }

    $str = preg_replace($search, $replace, $str);
    $str = preg_replace_callback('#\[color=(\#[\da-fA-F]{3}|\#[\da-fA-F]{6}|[A-Za-z]{1,20}|rgb\(\d{1,3}, ?\d{1,3}, ?\d{1,3}\))\](.*?)\[/color\]#si', function ($match) { return mobi_color_convert($match[1], $match[2]);}, $str);
    $str = preg_replace_callback('/\[code\](.*?)\[\/code\]/si', function ($match) { return '[code]'.html_entity_decode(base64_decode($match[1]), ENT_QUOTES, 'UTF-8').'[/code]';}, $str);
    return $str;
}

function mobi_color_convert($color, $str)
{
    static $colorlist;

    if (preg_match('/#[\da-fA-F]{6}/is', $color))
    {
        if (!$colorlist)
        {
            $colorlist = array(
                '#000000' => 'Black',             '#708090' => 'SlateGray',       '#C71585' => 'MediumVioletRed', '#FF4500' => 'OrangeRed',
                '#000080' => 'Navy',              '#778899' => 'LightSlateGrey',  '#CD5C5C' => 'IndianRed',       '#FF6347' => 'Tomato',
                '#00008B' => 'DarkBlue',          '#778899' => 'LightSlateGray',  '#CD853F' => 'Peru',            '#FF69B4' => 'HotPink',
                '#0000CD' => 'MediumBlue',        '#7B68EE' => 'MediumSlateBlue', '#D2691E' => 'Chocolate',       '#FF7F50' => 'Coral',
                '#0000FF' => 'Blue',              '#7CFC00' => 'LawnGreen',       '#D2B48C' => 'Tan',             '#FF8C00' => 'Darkorange',
                '#006400' => 'DarkGreen',         '#7FFF00' => 'Chartreuse',      '#D3D3D3' => 'LightGrey',       '#FFA07A' => 'LightSalmon',
                '#008000' => 'Green',             '#7FFFD4' => 'Aquamarine',      '#D3D3D3' => 'LightGray',       '#FFA500' => 'Orange',
                '#008080' => 'Teal',              '#800000' => 'Maroon',          '#D87093' => 'PaleVioletRed',   '#FFB6C1' => 'LightPink',
                '#008B8B' => 'DarkCyan',          '#800080' => 'Purple',          '#D8BFD8' => 'Thistle',         '#FFC0CB' => 'Pink',
                '#00BFFF' => 'DeepSkyBlue',       '#808000' => 'Olive',           '#DA70D6' => 'Orchid',          '#FFD700' => 'Gold',
                '#00CED1' => 'DarkTurquoise',     '#808080' => 'Grey',            '#DAA520' => 'GoldenRod',       '#FFDAB9' => 'PeachPuff',
                '#00FA9A' => 'MediumSpringGreen', '#808080' => 'Gray',            '#DC143C' => 'Crimson',         '#FFDEAD' => 'NavajoWhite',
                '#00FF00' => 'Lime',              '#87CEEB' => 'SkyBlue',         '#DCDCDC' => 'Gainsboro',       '#FFE4B5' => 'Moccasin',
                '#00FF7F' => 'SpringGreen',       '#87CEFA' => 'LightSkyBlue',    '#DDA0DD' => 'Plum',            '#FFE4C4' => 'Bisque',
                '#00FFFF' => 'Aqua',              '#8A2BE2' => 'BlueViolet',      '#DEB887' => 'BurlyWood',       '#FFE4E1' => 'MistyRose',
                '#00FFFF' => 'Cyan',              '#8B0000' => 'DarkRed',         '#E0FFFF' => 'LightCyan',       '#FFEBCD' => 'BlanchedAlmond',
                '#191970' => 'MidnightBlue',      '#8B008B' => 'DarkMagenta',     '#E6E6FA' => 'Lavender',        '#FFEFD5' => 'PapayaWhip',
                '#1E90FF' => 'DodgerBlue',        '#8B4513' => 'SaddleBrown',     '#E9967A' => 'DarkSalmon',      '#FFF0F5' => 'LavenderBlush',
                '#20B2AA' => 'LightSeaGreen',     '#8FBC8F' => 'DarkSeaGreen',    '#EE82EE' => 'Violet',          '#FFF5EE' => 'SeaShell',
                '#228B22' => 'ForestGreen',       '#90EE90' => 'LightGreen',      '#EEE8AA' => 'PaleGoldenRod',   '#FFF8DC' => 'Cornsilk',
                '#2E8B57' => 'SeaGreen',          '#9370D8' => 'MediumPurple',    '#F08080' => 'LightCoral',      '#FFFACD' => 'LemonChiffon',
                '#2F4F4F' => 'DarkSlateGrey',     '#9400D3' => 'DarkViolet',      '#F0E68C' => 'Khaki',           '#FFFAF0' => 'FloralWhite',
                '#2F4F4F' => 'DarkSlateGray',     '#98FB98' => 'PaleGreen',       '#F0F8FF' => 'AliceBlue',       '#FFFAFA' => 'Snow',
                '#32CD32' => 'LimeGreen',         '#9932CC' => 'DarkOrchid',      '#F0FFF0' => 'HoneyDew',        '#FFFF00' => 'Yellow',
                '#3CB371' => 'MediumSeaGreen',    '#9ACD32' => 'YellowGreen',     '#F0FFFF' => 'Azure',           '#FFFFE0' => 'LightYellow',
                '#40E0D0' => 'Turquoise',         '#A0522D' => 'Sienna',          '#F4A460' => 'SandyBrown',      '#FFFFF0' => 'Ivory',
                '#4169E1' => 'RoyalBlue',         '#A52A2A' => 'Brown',           '#F5DEB3' => 'Wheat',           '#FFFFFF' => 'White',
                '#4682B4' => 'SteelBlue',         '#A9A9A9' => 'DarkGrey',        '#F5F5DC' => 'Beige',
                '#483D8B' => 'DarkSlateBlue',     '#A9A9A9' => 'DarkGray',        '#F5F5F5' => 'WhiteSmoke',
                '#48D1CC' => 'MediumTurquoise',   '#ADD8E6' => 'LightBlue',       '#F5FFFA' => 'MintCream',
                '#4B0082' => 'Indigo',            '#ADFF2F' => 'GreenYellow',     '#F8F8FF' => 'GhostWhite',
                '#556B2F' => 'DarkOliveGreen',    '#AFEEEE' => 'PaleTurquoise',   '#FA8072' => 'Salmon',
                '#5F9EA0' => 'CadetBlue',         '#B0C4DE' => 'LightSteelBlue',  '#FAEBD7' => 'AntiqueWhite',
                '#6495ED' => 'CornflowerBlue',    '#B0E0E6' => 'PowderBlue',      '#FAF0E6' => 'Linen',
                '#66CDAA' => 'MediumAquaMarine',  '#B22222' => 'FireBrick',       '#FAFAD2' => 'LightGoldenRodYellow',
                '#696969' => 'DimGrey',           '#B8860B' => 'DarkGoldenRod',   '#FDF5E6' => 'OldLace',
                '#696969' => 'DimGray',           '#BA55D3' => 'MediumOrchid',    '#FF0000' => 'Red',
                '#6A5ACD' => 'SlateBlue',         '#BC8F8F' => 'RosyBrown',       '#FF00FF' => 'Fuchsia',
                '#6B8E23' => 'OliveDrab',         '#BDB76B' => 'DarkKhaki',       '#FF00FF' => 'Magenta',
                '#708090' => 'SlateGrey',         '#C0C0C0' => 'Silver',          '#FF1493' => 'DeepPink',
            );
        }

        if (isset($colorlist[strtoupper($color)])) $color = $colorlist[strtoupper($color)];
    }

    return "<font color=\"$color\">$str</font>";
}


function parse_quote($str)
{
    $blocks = preg_split('/(<blockquote.*?>|<\/blockquote>)/i', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

    $quote_level = 0;
    $message = '';

    foreach($blocks as $block)
    {
        if (preg_match('/<blockquote.*?>/i', $block)) {
            if ($quote_level == 0) $message .= '[quote]';
            $quote_level++;
        } else if (preg_match('/<\/blockquote>/i', $block)) {
            if ($quote_level <= 1) $message .= '[/quote]';
            if ($quote_level >= 1) {
                $quote_level--;
                $message .= "\n";
            }
        } else {
            if ($quote_level <= 1) $message .= $block;
        }
    }

    return $message;
}
function process_page($start_num, $end)
{
    global $start, $limit, $page;

    $start = intval($start_num);
    $end = intval($end);
    $start = empty($start) ? 0 : max($start, 0);
    $end = (empty($end) || $end < $start) ? ($start + 19) : max($end, $start);
    if ($end - $start >= 50) {
        $end = $start + 49;
    }
    $limit = $end - $start + 1;
    $page = intval($start/$limit) + 1;

    return array($start, $limit, $page);
}

function tapatalk_process_bbcode($message, $uid)
{
    global $user,$config, $phpbb_container;

    $message = str_replace("&quot;", '"', $message);
    $message = preg_replace('/:'.$uid.'/si', '', $message);

    //add custom
    if(class_exists('MbqMain') && MbqMain::$Cache->Exists('Config','tapatalk_custom_replace'))
    {
        $TT_bbcodereplace = MbqMain::$Cache->Get('Config','tapatalk_custom_replace');
    }
    else
    {
        $TT_bbcodereplace = getTapatalkConfigValue('tapatalk_custom_replace');
        if(class_exists('MbqMain'))
        {
            MbqMain::$Cache->Set('Config','tapatalk_custom_replace', $TT_bbcodereplace);
        }
    }

	if(!empty($TT_bbcodereplace))
    {
        $replace_arr = explode("\n", $TT_bbcodereplace);
        foreach ($replace_arr as $replace)
        {
            preg_match('/^\s*(\'|")((\#|\/|\!).+\3[ismexuADUX]*?)\1\s*,\s*(\'|")(.*?)\4\s*$/', $replace,$matches);
            if(count($matches) == 6)
            {
                $temp_str = $message;
                $message = @preg_replace($matches[2], $matches[5], $message);
                if(empty($message))
                {
                    $message = $temp_str;
                }
            }
        }
    }


    // process bbcode: quote

    $message =  preg_replace_callback('/\[quote(?:="?(.*?)"?)?\]/is', 'tt_tapatalk_process_bbcode_quote_callback', $message);

    //$quote_wrote_string = $user->lang['WROTE'];
    //$message = preg_replace('/\[quote(?:=&quot;(.*?)&quot;)?\]/ise', "'[quote name=\"$1\"]' . ('$1' ? '$1' . ' $quote_wrote_string:\n' : '\n')", $message);
    $blocks = preg_split('/(\[\/?quote\])/i', $message, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

    $quote_level = 0;
    $message = '';

    foreach($blocks as $block)
    {
        if ($block == '[quote]') {
            if ($quote_level == 0) $message .= $block;
            $quote_level++;
        } else if ($block == '[/quote]') {
            if ($quote_level <= 1) $message .= $block;
            if ($quote_level >= 1) $quote_level--;
        } else {
            if ($quote_level <= 1) $message .= $block;
        }
    }

    //if(class_exists('MbqMain'))
    //{
    //    if(MbqMain::$client =='ios')
    //    {
    //        if(!defined('PHPBB_USE_BOARD_URL_PATH'))
    //        {
    //            define('PHPBB_USE_BOARD_URL_PATH', true);
    //        }
    //        $message = smiley_text($message, false);
    //    }
    //}
    // prcess bbcode: list
    $message = preg_replace('/\[\*\]/si', '[*]', $message);
    $message = preg_replace('/\[\/\*(:m)?\]/si', '', $message);
    $message = tt_covert_list($message, '/\[list\](.*?)\[\/list:u\]/si', '1');
    $message = tt_covert_list($message, '/\[list=[^\]]*?\](.*?)\[\/list:o\]/si', '2');

    // process video bbcode\
    $message = preg_replace_callback('/\[(youtube|yt|video|googlevideo|gvideo)\](.*?)\[\/\1\]/si', function ($match) { return video_bbcode_format($match[1], $match[2]);}, $message);
    $message = preg_replace('/\[BBvideo(.*)\](.*?)\[\/BBvideo\]/si', "[url=$2]Video[/url]", $message);
    $message = preg_replace_callback('/\[MEDIA=(.*?)\](.*?)\[\/MEDIA\]/si', function ($match) { return video_bbcode_format($match[1], $match[2]);}, $message);
    $message = preg_replace('/\[(spoil|spoiler)\](.*?)\[\/\1\]/si', "[spoiler]$2[/spoiler]", $message);
    $message = preg_replace('/\[spoiler=(.*?)\](.*?)\[\/spoiler\]/si', '[spoiler]$2[/spoiler]', $message);
    $message = preg_replace('/\[HiddenText=(.*?)\](.*?)\[\/HiddenText\]/si', '[spoiler]$2[/spoiler]', $message);
    $message = preg_replace('/\[mp3preview\](.*?)\[\/mp3preview\]/si', '[url=$1]MP3 Preview[/url]', $message);
    $message = preg_replace('/\[flash(.*?)\](.*?)\[\/flash(.*?)\]/si', '[url=$2]Flash Video[/url]', $message);
    $message = preg_replace('/<iframe .*?src=\"(http.*?)\" .*?>.*?<\/iframe>/si', '[url=$1]$1[/url]', $message);

    $message = preg_replace('/\n|\r/si', '<br />', $message);
    $message = preg_replace('#\[(b)\](.*?)\[/b\]#si', '<$1>$2</$1>', $message);
    $message = preg_replace('#\[(u)\](.*?)\[/u\]#si', '<$1>$2</$1>', $message);
    $message = preg_replace('#\[(i)\](.*?)\[/i\]#si', '<$1>$2</$1>', $message);
    $message = preg_replace_callback('#\[color=(\#[\da-fA-F]{3}|\#[\da-fA-F]{6}|[A-Za-z]{1,20}|rgb\(\d{1,3}, ?\d{1,3}, ?\d{1,3}\))\](.*?)\[/color\]#si', function ($match) { return mobi_color_convert($match[1], $match[2]);}, $message);
    $message = preg_replace('/\[size(.*?)\](.*?)\[\/size(.*?)\]/si', '$2', $message);



    // remove attach icon image
    $message = preg_replace('/\[img\][^\[\]]+icon_topic_attach\.gif\[\/img\]/si', '', $message);
    $message = preg_replace('/\[attachment(.*?)\](.*?)\[\/attachment(.*?)\]/si', '', $message);

    // change url to image resource to img bbcode
    $message = preg_replace('/\[url\](http[^\[\]]+\.(jpg|png|bmp|gif))\[\/url\]/si', '[img]$1[/img]', $message);
    $message = preg_replace('/\[url=(http[^\]]+\.(jpg|png|bmp|gif))\]([^\[\]]+)\[\/url\]/si', '[img]$1[/img]', $message);
    $message = preg_replace_callback('/\[img\](.*?)\[\/img\]/si', function ($match) { return '[img]'.html_entity_decode($match[1]).'[/img]';}, $message);
    $message = preg_replace_callback('/\[url\](.*?)\[\/url\]/si', function ($match) { return '[url]'.html_entity_decode($match[1]).'[/url]';}, $message);
    $message = preg_replace_callback('/\[url=(.*)\](.*?)\[\/url\]/si', function ($match) { return '[url='.html_entity_decode($match[1]).']'.$match[2].'[/url]';}, $message);
    $message = preg_replace_callback('/\[code\](.*?)\[\/code\]/si',  function ($match) { return '[code]'.html_entity_decode($match[1]).'[/code]';}, $message);


    return $message;
}
function tt_tapatalk_process_bbcode_quote_callback($matches)
{
    if(count($matches) > 1)
    {
        $userid = get_user_id_by_name($matches[1]);
        if(!empty($userid))
        {
            return "[quote uid=".$userid." name=\"".$matches[1]."\" ]";
        }
    }
    return "[quote]";
}
function tt_covert_list($message,$preg,$type)
{
    while(preg_match($preg, $message, $blocks))
    {
        $list_str = "";
        $list_arr = explode('[*]', $blocks[1]);
        foreach ($list_arr as $key => $value)
        {
            $value = trim($value);
            if(!empty($value) && $key != 0)
            {
                if($type == '1')
                {
                    $key = ' * ';
                }
                else
                {
                    $key = $key.'. ';
                }
                $list_str .= $key.$value ."\n";
            }
            else if(!empty($value))
            {
                $list_str .= $value ."\n";
            }
        }
        $message = str_replace($blocks[0], $list_str, $message);
    }
    return $message;
}
function url_encode($url)
{
    global $phpbb_home, $phpbb_root_path;

	//check is domain
    $is_domain = false;
    if(preg_match('/^\//', $url) && !preg_match('/download\/file\.php/', $url))
    {
    	$is_domain = true;
    	$server_url = $_SERVER['HTTP_HOST'];
    }
    $url = rawurlencode($url);

    $from = array('/%3A/', '/%2F/', '/%3F/', '/%2C/', '/%3D/', '/%26/', '/%25/', '/%23/', '/%2B/', '/%3B/', '/%5C/', '/%20/');
    $to   = array(':',     '/',     '?',     ',',     '=',     '&',     '%',     '#',     '+',     ';',     '\\',    ' ');
    $url = preg_replace($from, $to, $url);
    $root_path = preg_replace('/^\//', '', $phpbb_root_path);
    if($root_path == '/')
    {
        $url = preg_replace('#^\.\./|^/#si', '', $url);
    }
    else
    {
        $url = preg_replace('#^\.\./|^/|'.addslashes($root_path).'#si', '', $url);
    }

    $url = preg_replace('#^.*?(?=download/file\.php)#si', '', $url);



    if (strpos($url, 'http') !== 0 && strpos($url, 'https') !== 0 && strpos($url, 'mailto') !== 0)
    {
    	if(!$is_domain)
        	$url = $phpbb_home.$url;
        else
        	$url = "http://".$server_url.'/'.$url;
    }

    return htmlspecialchars_decode($url);
}

function get_user_avatar_url($avatar, $avatar_type, $ignore_config = false)
{
    global $config, $phpbb_home, $phpEx;

    if (empty($avatar) || (isset($config['allow_avatar']) && !$config['allow_avatar'] && !$ignore_config))
    {
        return '';
    }

    $avatar_img = '';
    $version = getPHPBBVersion();
    if($version == '3.0')
    {
	    switch ($avatar_type)
	    {
	        case AVATAR_UPLOAD:
	            if (isset($config['allow_avatar_upload']) && !$config['allow_avatar_upload'] && !$ignore_config)
	            {
	                return '';
	            }
	            $avatar_img = $phpbb_home . "download/file.$phpEx?avatar=";
	            break;

	        case AVATAR_GALLERY:
	            if (isset($config['allow_avatar_local']) && !$config['allow_avatar_local'] && !$ignore_config)
	            {
	                return '';
	            }
	            $avatar_img = $phpbb_home . $config['avatar_gallery_path'] . '/';
	            break;

	        case AVATAR_REMOTE:
	            if (isset($config['allow_avatar_remote']) && !$config['allow_avatar_remote'] && !$ignore_config)
	            {
	                return '';
	            }
	            break;
	        default:
	            $avatar_img = $phpbb_home . "download/file.$phpEx?avatar=";
	        	break;
	    }

	    $avatar_img .= $avatar;
	    $avatar_img = str_replace(' ', '%20', $avatar_img);

	    return $avatar_img;
    }
    else
    {
        if(preg_match('/^' . preg_quote('http', '/') . '/', $avatar))
	    {
	        return $avatar;
	    }
	    switch ($avatar_type)
	    {
	        case "avatar.driver.gravatar":
	            {
	                return "http://secure.gravatar.com/avatar/" .  md5(strtolower(trim($avatar)));;
	            }
	        default:
	            return $phpbb_home . "download/file.$phpEx?avatar=" . $avatar;
	    }

    }
}


function get_user_id_by_name($username)
{
    global $db;

    if (!$username)
    {
        return false;
    }
    $sql = 'SELECT user_id
            FROM ' . USERS_TABLE . "
            WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
    $result = $db->sql_query($sql);
    $user_id = $db->sql_fetchfield('user_id');
    $db->sql_freeresult($result);
    return $user_id;
}
function get_name_by_userid($userid)
{
    global $db;

    if (!$userid)
    {
        return false;
    }
    $sql = 'SELECT username_clean
            FROM ' . USERS_TABLE . "
            WHERE user_id = '$userid'";
    $result = $db->sql_query($sql);
    $username_clean = $db->sql_fetchfield('username_clean');
    $db->sql_freeresult($result);
    return $username_clean;
}
function cut_quote($str, $keep_size)
{
    $str_array = preg_split('/(\[quote\].*?\[\/quote\])/is', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

    $str = '';

    foreach($str_array as $block)
    {
        if (preg_match('/\[quote\](.*?)\[\/quote\]/is', $block, $block_matches))
        {
            $quote_array = preg_split('/(\[img\].*?\[\/img\]|\[url=.*?\].*?\[\/url\])/is', $block_matches[1], -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            $short_str = '';
            $current_size = 0;
            $img_flag = true; // just keep at most one img in the quote
            for ($i = 0, $size = sizeof($quote_array); $i < $size; $i++)
            {
                if (preg_match('/^\[img\].*?\[\/img\]$/is', $quote_array[$i]))
                {
                    if ($img_flag)
                    {
                        $short_str .= $quote_array[$i];
                        $img_flag = false;
                    }
                }
                else if (preg_match('/^\[url=.*?\](.*?)\[\/url\]$/is', $quote_array[$i], $matches))
                {
                    $short_str .= $quote_array[$i];
                    $current_size += strlen($matches[1]);
                    if ($current_size > $keep_size)
                    {
                        $short_str .= "...";
                        break;
                    }
                }
                else
                {
                    if ($current_size + strlen($quote_array[$i]) > $keep_size)
                    {
                        $short_str .= substr($quote_array[$i], 0, $keep_size - $current_size);
                        $short_str .= "...";
                        break;
                    }
                    else
                    {
                        $short_str .= $quote_array[$i];
                        $current_size += strlen($quote_array[$i]);
                    }
                }
            }
            $str .= '[quote]' . $short_str . '[/quote]';
        } else {
            $str .= $block;
        }
    }

    return $str;
}

function video_bbcode_format($type, $url)
{
    $url = trim($url);
    $url = html_entity_decode($url);
    switch (strtolower($type)) {
        case 'yt':
        case 'youtube':
            if (preg_match('#^(http(s|)://)?((www|m)\.)?(youtube\.com/(watch\?.*?v=|v/)|youtu\.be/)([-\w]+)#', $url, $matches)) {
                $url = preg_replace("/^https:/i", "http:", $url);
            	$message = '[url='.$url.']YouTube Video[/url]';
            } else if (preg_match('/^[-\w]+$/', $url)) {
                $url = 'http://www.youtube.com/watch?v='.$url;
                $message = '[url='.$url.']YouTube Video[/url]';
            } else {
                $message = '';
            }
            break;
        case 'facebook':
            if (preg_match('#^(http(s|)://)?((www|m)\.)?(facebook\.com/(watch\?.*?v=|v/))([-\w]+)#', $url, $matches)) {
                $url = preg_replace("/^https:/i", "http:", $url);
            	$message = '[url='.$url.']Facebook Video[/url]';
            } else if (preg_match('/^[-\w]+$/', $url)) {
                $url = 'http://www.facebook.com/video.php?v='.$url;
                $message = '[url='.$url.']Facebook Video[/url]';
            } else {
                $message = '';
            }
            break;
        case 'video':
            if (preg_match('#^http(s)?://#', $url)) {
                $message = '[url='.$url.']Video[/url]';
            } else {
                $message = '';
            }
            break;
        case 'gvideo':
        case 'googlevideo':
            if (preg_match('#^http://video.google.com/(googleplayer.swf|videoplay)?docid=-#', $url)) {
                $message = '[url='.$url.']Google Video[/url]';
            } else if (preg_match('/^-?(\d+)/', $url, $matches)) {
                $message = '[url=http://video.google.com/videoplay?docid=-'.$matches['1'].']Google Video[/url]';
            } else {
                $message = '';
            }
            break;
        default: $message = '';
    }

    return $message;
}

function check_forum_password($forum_id)
{
    global $user, $db;

    $sql = 'SELECT forum_id
            FROM ' . FORUMS_ACCESS_TABLE . '
            WHERE forum_id = ' . $forum_id . '
                AND user_id = ' . $user->data['user_id'] . "
                AND session_id = '" . $db->sql_escape($user->session_id) . "'";
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);
    $db->sql_freeresult($result);

    if (!$row)
    {
        return false;
    }

    return true;
}

function get_participated_user_avatars($tids)
{
    global $db, $topic_users, $user_avatar;

    $topic_users = array();
    $user_avatar = array();
    if (!empty($tids))
    {
        $posters = array();
        foreach($tids as $tid)
        {
            $sql = 'SELECT poster_id, count(post_id) as num FROM ' . POSTS_TABLE . '
                    WHERE topic_id=' . $tid . '
                    GROUP BY poster_id
                    ORDER BY num DESC';
            $result = $db->sql_query_limit($sql, 10);
            while ($row = $db->sql_fetchrow($result))
            {
                $posters[$row['poster_id']] = $row['num'];
                $topic_users[$tid][] = $row['poster_id'];
            }
            $db->sql_freeresult($result);
        }

        if (!empty($posters))
        {
            $user_avatar = get_user_avatars(array_keys($posters));
        }
    }
}

function get_user_avatars($users, $is_username = false)
{
    global $db;

    if (empty($users)) return array();

    if (!is_array($users)) $users = array($users);

    if($is_username)
        foreach($users as $key => $username)
            $users[$key] = $db->sql_escape(utf8_clean_string($username));

    $sql = 'SELECT user_id, username, user_avatar, user_avatar_type
            FROM ' . USERS_TABLE . '
            WHERE ' . $db->sql_in_set($is_username ? 'username_clean' : 'user_id', $users);
    $result = $db->sql_query($sql);
    $user_avatar = array();
    $user_key = $is_username ? 'username' : 'user_id';
    while ($row = $db->sql_fetchrow($result))
    {
        $user_avatar[$row[$user_key]] = get_user_avatar_url($row['user_avatar'], $row['user_avatar_type']);
    }
    $db->sql_freeresult($result);

    return $user_avatar;
}

function check_error_status(&$str)
{
    global $user;

    switch (MbqMain::$cmd) {
        case 'thank_post':
            if (strpos($str, $user->lang['THANKS_INFO_GIVE']) !== false || $str == "Insert thanks") {
                $str = '';
                return true;
            } elseif (strpos($str, $user->lang['GLOBAL_INCORRECT_THANKS']) !== false) {
                $str = $user->lang['GLOBAL_INCORRECT_THANKS'];
                return false;
            } elseif (strpos($str, $user->lang['INCORRECT_THANKS']) !== false) {
                $str = $user->lang['INCORRECT_THANKS'];
                return false;
            }
            else if(strpos($str, 'Tried to redirect to potentially insecure url')  !== false )
            {
                $str = '';
                return true;
            }
            else {
                return false;
            }

        case 'm_stick_topic':
            if (strpos($str, $user->lang['TOPIC_TYPE_CHANGED']) === false)
                return false;
            else {
                $str = $user->lang['TOPIC_TYPE_CHANGED'];
                return true;
            }
        case 'm_close_topic':
            if (strpos($str, $user->lang['TOPIC_LOCKED_SUCCESS']) === false && strpos($str, $user->lang['TOPIC_UNLOCKED_SUCCESS']) === false)
                return false;
            elseif (strpos($str, $user->lang['TOPIC_LOCKED_SUCCESS']) !== false) {
                $str = $user->lang['TOPIC_LOCKED_SUCCESS'];
                return true;
            } else {
                $str = $user->lang['TOPIC_UNLOCKED_SUCCESS'];
                return true;
            }
        case 'm_delete_topic':
            if (strpos($str, $user->lang['TOPIC_DELETED_SUCCESS']) === false)
                return false;
            else {
                $str = $user->lang['TOPIC_DELETED_SUCCESS'];
                return true;
            }
        case 'm_delete_post':
            if (strpos($str, $user->lang['POST_DELETED_SUCCESS']) === false && strpos($str, $user->lang['TOPIC_DELETED_SUCCESS']) === false)
                return false;
            elseif (strpos($str, $user->lang['POST_DELETED_SUCCESS']) !== false) {
                $str = $user->lang['POST_DELETED_SUCCESS'];
                return true;
            } else {
                $str = $user->lang['TOPIC_DELETED_SUCCESS'];
                return true;
            }
        case 'm_move_topic':
            if (strpos($str, $user->lang['TOPIC_MOVED_SUCCESS']) === false && strpos($str, $user->lang['TOPICS_MOVED_SUCCESS']) === false)
                return false;
            else {
                $str = $user->lang['TOPIC_MOVED_SUCCESS'];
                return true;
            }
        case 'm_move_post':
            if (strpos($str, $user->lang['TOPIC_SPLIT_SUCCESS']) === false && strpos($str, $user->lang['POSTS_MERGED_SUCCESS']) === false)
                return false;
            elseif (strpos($str, $user->lang['TOPIC_SPLIT_SUCCESS']) !== false) {
                $str = $user->lang['TOPIC_SPLIT_SUCCESS'];
                return true;
            } else {
                $str = $user->lang['POSTS_MERGED_SUCCESS'];
                return true;
            }
        case 'm_merge_topic':
            if (strpos($str, $user->lang['POSTS_MERGED_SUCCESS']) === false)
                return false;
            else {
                $str = $user->lang['POSTS_MERGED_SUCCESS'];
                return true;
            }
        case 'm_approve_topic':
            if (strpos($str, $user->lang['TOPIC_APPROVED_SUCCESS']) === false)
                return false;
            else {
                $str = $user->lang['TOPIC_APPROVED_SUCCESS'];
                return true;
            }
        case 'm_approve_post':
            if (strpos($str, $user->lang['POST_APPROVED_SUCCESS']) === false)
                return false;
            else {
                $str = $user->lang['POST_APPROVED_SUCCESS'];
                return true;
            }
        case 'm_ban_user':
            if (strpos($str, $user->lang['BAN_UPDATE_SUCCESSFUL']) === false)
                return false;
            else {
                $str = $user->lang['BAN_UPDATE_SUCCESSFUL'];
                return true;
            }
    }

    return false;
}

function tt_get_unread_topics($user_id = false, $sql_extra = '', $sql_sort = '', $sql_limit = 1001, $sql_limit_offset = 0)
{
    global $config, $db, $user;

    $user_id = ($user_id === false) ? (int) $user->data['user_id'] : (int) $user_id;

    // Data array we're going to return
    $unread_topics = array();

    if (empty($sql_sort))
    {
        $sql_sort = 'ORDER BY t.topic_last_post_time DESC';
    }

    if ($config['load_db_lastread'] && $user->data['is_registered'])
    {
        // Get list of the unread topics
        $last_mark = (int) $user->data['user_lastmark'];

        $sql_array = array(
            'SELECT'        => 't.topic_id, t.topic_last_post_time, tt.mark_time as topic_mark_time, ft.mark_time as forum_mark_time',

            'FROM'            => array(TOPICS_TABLE => 't'),

            'LEFT_JOIN'        => array(
                array(
                    'FROM'    => array(TOPICS_TRACK_TABLE => 'tt'),
                    'ON'    => "tt.user_id = $user_id AND t.topic_id = tt.topic_id",
                ),
                array(
                    'FROM'    => array(FORUMS_TRACK_TABLE => 'ft'),
                    'ON'    => "ft.user_id = $user_id AND t.forum_id = ft.forum_id",
                ),
            ),

            'WHERE'            => "
                 t.topic_last_post_time > $last_mark AND
                (
                (tt.mark_time IS NOT NULL AND t.topic_last_post_time > tt.mark_time) OR
                (tt.mark_time IS NULL AND ft.mark_time IS NOT NULL AND t.topic_last_post_time > ft.mark_time) OR
                (tt.mark_time IS NULL AND ft.mark_time IS NULL)
                )
                $sql_extra
                $sql_sort",
        );

        $sql = $db->sql_build_query('SELECT', $sql_array);
        $result = $db->sql_query_limit($sql, $sql_limit, $sql_limit_offset);

        while ($row = $db->sql_fetchrow($result))
        {
            $topic_id = (int) $row['topic_id'];
            $unread_topics[$topic_id] = ($row['topic_mark_time']) ? (int) $row['topic_mark_time'] : (($row['forum_mark_time']) ? (int) $row['forum_mark_time'] : $last_mark);
        }
        $db->sql_freeresult($result);
    }
    else if ($config['load_anon_lastread'] || $user->data['is_registered'])
    {
        global $tracking_topics;

        if (empty($tracking_topics))
        {
            $tracking_topics = request_var($config['cookie_name'] . '_track', '', false, true);
            $tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();
        }

        if (!$user->data['is_registered'])
        {
            $user_lastmark = (isset($tracking_topics['l'])) ? base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate'] : 0;
        }
        else
        {
            $user_lastmark = (int) $user->data['user_lastmark'];
        }

        $sql = 'SELECT t.topic_id, t.forum_id, t.topic_last_post_time
            FROM ' . TOPICS_TABLE . ' t
            WHERE t.topic_last_post_time > ' . $user_lastmark . "
            $sql_extra
            $sql_sort";
        $result = $db->sql_query_limit($sql, $sql_limit, $sql_limit_offset);

        while ($row = $db->sql_fetchrow($result))
        {
            $forum_id = (int) $row['forum_id'];
            $topic_id = (int) $row['topic_id'];
            $topic_id36 = base_convert($topic_id, 10, 36);

            if (isset($tracking_topics['t'][$topic_id36]))
            {
                $last_read = base_convert($tracking_topics['t'][$topic_id36], 36, 10) + $config['board_startdate'];

                if ($row['topic_last_post_time'] > $last_read)
                {
                    $unread_topics[$topic_id] = $last_read;
                }
            }
            else if (isset($tracking_topics['f'][$forum_id]))
            {
                $mark_time = base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate'];

                if ($row['topic_last_post_time'] > $mark_time)
                {
                    $unread_topics[$topic_id] = $mark_time;
                }
            }
            else
            {
                $unread_topics[$topic_id] = $user_lastmark;
            }
        }
        $db->sql_freeresult($result);
    }

    return $unread_topics;
}

function tp_get_forum_icon($id, $type = 'forum', $lock = false, $new = false)
{
    if ($type == 'link')
    {
        if ($filename = tp_get_forum_icon_by_name('link'))
            return $filename;
    }
    else
    {
        if ($lock && $new && $filename = tp_get_forum_icon_by_name('lock_new_'.$id))
            return $filename;
        if ($lock && $filename = tp_get_forum_icon_by_name('lock_'.$id))
            return $filename;
        if ($new && $filename = tp_get_forum_icon_by_name('new_'.$id))
            return $filename;
        if ($filename = tp_get_forum_icon_by_name($id))
            return $filename;

        if ($type == 'category')
        {
            if ($lock && $new && $filename = tp_get_forum_icon_by_name('category_lock_new'))
                return $filename;
            if ($lock && $filename = tp_get_forum_icon_by_name('category_lock'))
                return $filename;
            if ($new && $filename = tp_get_forum_icon_by_name('category_new'))
                return $filename;
            if ($filename = tp_get_forum_icon_by_name('category'))
                return $filename;
        }
        else
        {
            if ($lock && $new && $filename = tp_get_forum_icon_by_name('forum_lock_new'))
                return $filename;
            if ($lock && $filename = tp_get_forum_icon_by_name('forum_lock'))
                return $filename;
            if ($new && $filename = tp_get_forum_icon_by_name('forum_new'))
                return $filename;
            if ($filename = tp_get_forum_icon_by_name('forum'))
                return $filename;
        }

        if ($lock && $new && $filename = tp_get_forum_icon_by_name('lock_new'))
            return $filename;
        if ($lock && $filename = tp_get_forum_icon_by_name('lock'))
            return $filename;
        if ($new && $filename = tp_get_forum_icon_by_name('new'))
            return $filename;
    }

    return tp_get_forum_icon_by_name('default');
}

function tp_get_forum_icon_by_name($icon_name)
{
    $tapatalk_forum_icon_dir = './forum_icons/';

    if (file_exists($tapatalk_forum_icon_dir.$icon_name.'.png'))
        return $icon_name.'.png';

    if (file_exists($tapatalk_forum_icon_dir.$icon_name.'.jpg'))
        return $icon_name.'.jpg';

    return '';
}

function check_return_user_type($user_id, $is_xmlrpc = true)
{
    global $db, $user, $config;
    //$session = new user();
    $user_id = intval($user_id);
    $user_row = TT_get_user_by_id($user_id);
    $sql = "SELECT group_name FROM " . USER_GROUP_TABLE . " AS ug LEFT JOIN " .GROUPS_TABLE. " AS g ON ug.group_id = g.group_id WHERE user_id = " . $user_id;
    $query = $db->sql_query($sql);
    $is_ban = $user->check_ban($user_id,false,false,true);
    $user_groups = array();
    while($row = $db->sql_fetchrow($query))
    {
        $user_groups[] = $row['group_name'];
    }
    if(!empty($is_ban ))
    {
        $user_type = 'banned';
    }
    else if(in_array('ADMINISTRATORS', $user_groups))
    {
        $user_type = 'admin';
    }
    else if(in_array('GLOBAL_MODERATORS', $user_groups))
    {
        $user_type = 'mod';
    }
    else if($user_row['user_type'] == USER_INACTIVE && $config['require_activation'] == USER_ACTIVATION_ADMIN)
    {
    	$user_type = 'unapproved';
    }
    else if($user_row['user_type'] == USER_INACTIVE)
    {
    	$user_type = 'inactive';
    }
    else
    {
        $user_type = 'normal';
    }

    if($is_xmlrpc) return new xmlrpcval(basic_clean($user_type), 'base64');

    return $user_type;
}

//function tt_register_verify($tt_token,$tt_code)
//{
//    global $config;

//    $key = isset($config['tapatalk_push_key']) ? $config['tapatalk_push_key'] : '';
//    $board_url = generate_board_url();

//    require_once TT_ROOT."include/classTTJson.php";
//    require_once TT_ROOT."include/classConnection.php";
//    $connection = new classFileManagement();
//    $result = $connection->signinVerify($tt_token,$tt_code,$board_url,$key);
//    $result = json_encode($result);
//    $result = json_decode($result);
//    return $result;
//}

///**
// * Get content from remote server
// *
// * @param string $url      NOT NULL          the url of remote server, if the method is GET, the full url should include parameters; if the method is POST, the file direcotry should be given.
// * @param string $holdTime [default 0]       the hold time for the request, if holdtime is 0, the request would be sent and despite response.
// * @param string $error_msg                  return error message
// * @param string $method   [default GET]     the method of request.
// * @param string $data     [default array()] post data when method is POST.
// *
// * @exmaple: getContentFromRemoteServer('http://push.tapatalk.com/push.php', 0, $error_msg, 'POST', $ttp_post_data)
// * @return string when get content successfully|false when the parameter is invalid or connection failed.
// */
//function getContentFromRemoteServer($url, $holdTime = 0, &$error_msg, $method = 'GET', $data = array(), $retry = true)
//{
//    //Validate input.
//    global $config, $phpbb_root_path;
//    if(!defined("TT_ROOT"))
//    {
//        if(!defined('IN_MOBIQUO')) define('IN_MOBIQUO', true);
//        if(empty($config['tapatalkdir'])) $config['tapatalkdir'] = 'mobiquo';
//        define('TT_ROOT',$phpbb_root_path . $config['tapatalkdir'] . '/');
//    }
//    include_once TT_ROOT."include/classConnection.php";
//    $connection = new classFileManagement();
//    $connection->timeout = $holdTime;
//    $response = $connection->getContentFromSever($url,$data,$method,$retry);
//    if(!empty($connection->errors))
//    {
//        $error_msg = $connection->errors[0];
//    }
//    return $response;
//}

function tt_get_user_by_email($email)
{
    global $db;
    $sql = 'SELECT *
        FROM ' . USERS_TABLE . "
        WHERE user_email = '" . $db->sql_escape($email) . "'";
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);
    $db->sql_freeresult($result);
    return $row;
}


function tt_get_ignore_users($user_id)
{
	global $db;

	$sql_and = 'z.foe = 1';
	$sql = 'SELECT z.*
		FROM ' . ZEBRA_TABLE . ' z
		WHERE z.user_id = ' . $user_id . "
			AND $sql_and ";
	$result = $db->sql_query($sql);

	$ignore_users = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$ignore_users[] = $row['zebra_id'];
	}
	$db->sql_freeresult($result);
	return $ignore_users;
}

function is_tapatalk_user($user_id)
{
	global $db,$table_prefix;
	if(!push_table_exists())
	{
		return false;
	}
	$sql = "SELECT userid FROM " . $table_prefix . "tapatalk_users WHERE userid = '".$user_id."'";
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);
    if(empty($row))
    {
    	return false;
    }
    return true;
}
function getTextConfigKeys()
{
    return array('tapatalk_custom_replace','tapatalk_forum_read_only','mobiquo_hide_forum_id','tapatalk_banner_control');
}
function existsTapatalkConfigValue($key)
{
    global $config, $phpbb_container;
    if(getPHPBBVersion() == '3.0')
    {
        return isset($config[$key]);
    }
    else
    {
        if(in_array($key, getTextConfigKeys()))
        {
            $isset = isset($config[$key]);
            if ($isset)
            {
                return true;
            }
            $value = $phpbb_container->get('config_text')->get($key);
            return !empty($value);
        }
        else
        {
            return isset($config[$key]);
        }
    }
}
function getTapatalkConfigValue($key)
{
    global $config, $phpbb_container;
    if(getPHPBBVersion() == '3.0')
    {
        $value = $config[$key];
    }
    else
    {
        if(in_array($key, getTextConfigKeys()))
        {
            $value = $config[$key];
            if(empty($value))
            {
                $value = $phpbb_container->get('config_text')->get($key);
            }
            else
            {
                $phpbb_container->get('config_text')->set($key, $value);
                $config->set($key, '');
            }
        }
        else
        {
            $value = $config[$key];
        }
    }
    return $value;
}
function setTapatalkConfigValue($key, $value)
{
    global $config, $phpbb_container;
    if(getPHPBBVersion() == '3.0')
    {
        set_config($key, $value);
    }
    else
    {

        if(in_array($key, getTextConfigKeys()))
        {
            $phpbb_container->get('config_text')->set($key, $value);
            $config->set($key, '');
        }
        else
        {
            $config->set($key, $value);
        }
    }
    return $value;
}

function tapatalk_config_table_exists()
{
	if(defined('TAPATALK_CONFIG_TABLE_EXISTS'))
	{
		return TAPATALK_CONFIG_TABLE_EXISTS;
	}
	global $db,$table_prefix,$phpbb_root_path,$phpEx;
    $db->sql_return_on_error(true);
	$result = $db->sql_query_limit('SELECT * FROM ' . $table_prefix.'tapatalk_config', 1);
	$db->sql_return_on_error(false);

	if ($result)
	{
		$db->sql_freeresult($result);
		define('TAPATALK_CONFIG_TABLE_EXISTS',true);
		return true;
	}
    define('TAPATALK_CONFIG_TABLE_EXISTS',false);
    return false;
}

/**
 *
 * check tapatalk push table is exist or not
 */
function push_table_exists()
{
	if(defined('PUSH_TABLE_EXISTS'))
	{
		return PUSH_TABLE_EXISTS;
	}
	global $db,$table_prefix,$phpbb_root_path,$phpEx;
    $db->sql_return_on_error(true);
	$result = $db->sql_query_limit('SELECT * FROM ' . $table_prefix. 'tapatalk_users', 1);
	$db->sql_return_on_error(false);

	if ($result)
	{
		$db->sql_freeresult($result);
		define('PUSH_TABLE_EXISTS',true);
		return true;
	}
    else
    {
        $result = $db->sql_query('CREATE TABLE ' . $table_prefix.'tapatalk_users (userid int, updated timestamp)', 1);
        if ($result)
        {
        	$db->sql_freeresult($result);
            define('PUSH_TABLE_EXISTS',true);
            return true;
        }
    }
    define('PUSH_TABLE_EXISTS',false);
    return false;
}

function push_data_table_exists()
{
	if(defined('PUSH_DATA_TABLE_EXISTS'))
	{
		return PUSH_DATA_TABLE_EXISTS;
	}
	global $db,$table_prefix,$phpbb_root_path,$phpEx;
	require_once($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
	$db_tools = new phpbb_db_tools($db);
    if(method_exists($db_tools, 'sql_table_exists') && $db_tools->sql_table_exists($table_prefix.'tapatalk_push_data'))
    {
    	define('PUSH_DATA_TABLE_EXISTS',true);
    	return true;
    }
    elseif(!method_exists($db_tools, 'sql_table_exists'))
    {
    	$db->sql_return_on_error(true);
		$result = $db->sql_query_limit('SELECT * FROM ' . $table_prefix.'tapatalk_push_data', 1);
		$db->sql_return_on_error(false);

		if ($result)
		{
			$db->sql_freeresult($result);
			define('PUSH_DATA_TABLE_EXISTS',true);
			return true;
		}
    }
    define('PUSH_DATA_TABLE_EXISTS',false);
    return false;
}

function checkPluginInitialized()
{
    global $config,$phpbb_root_path,$phpEx;
    if(!isset($config['tapatalk_version']))
    {
        include_once $phpbb_root_path . 'includes/functions_convert.' . $phpEx;
        set_config('tapatalkdir','mobiquo');
        set_config('tapatalk_ad_filter','');
        set_config('tapatalk_auto_approve','1');
        set_config('tapatalk_banner_control','');
        set_config('tapatalk_banner_update','');
        set_config('tapatalk_custom_replace','');
        set_config('tapatalk_forum_read_only','');
        set_config('tapatalk_push_key','');
        set_config('tapatalk_push_slug','');
        set_config('tapatalk_push_type','1');
        set_config('tapatalk_register_group',get_group_id('REGISTERED'));
        set_config('tapatalk_register_status','2');
        set_config('tapatalk_spam_status','1');
        set_config('mobiquo_hide_forum_id','');
        set_config('tapatalk_version','5.0.0');
        push_table_exists();
    }
}