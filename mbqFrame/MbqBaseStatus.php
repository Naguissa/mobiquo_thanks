<?php

abstract class MbqBaseStatus
{
    abstract public function GetLoggedUserName();
    abstract protected function GetMobiquoDir();
    abstract protected function GetMobiquoFileSytemDir();
    abstract protected function GetApiKey();
    abstract protected function GetPushSlug();
    abstract protected function ResetPushSlug();
    abstract protected function GetForumUrl();
    abstract protected function GetBYOInfo();
    abstract protected function GetOtherPlugins();

    function __construct()
    {
        if(isset($_POST['command']))
        {
            if($this->CanAccess())
            {
                switch($_POST['command'])
                {
                    case 'testPluginVersion':
                        {
                            echo json_encode($this->TestPluginVersion());
                            break;
                        }
                    case 'testConnectivity':
                        {
                            echo json_encode($this->TestConnectivity());
                            break;
                        }
                    case 'testFilePermission':
                        {
                            echo json_encode($this->TestFilePermission());
                            break;
                        }
                    case 'resetPushSlug':
                        {
                            echo json_encode($this->ResetPushSlug());
                            break;
                        }
                    case 'testPush':
                        {
                            echo json_encode($this->TestPush());
                            break;
                        }
                    case 'resetBYOInfo':
                        {
                            echo json_encode($this->ResetBYOInfo());
                            break;
                        }
                    case 'testBYOInfo':
                        {
                            echo json_encode($this->TestBYOInfo());
                            break;
                        }
                    case 'testOtherPlugins':
                        {
                            echo json_encode($this->TestOtherPlugins());
                            break;
                        }
                }
            }
            die;
        }
        else
        {
            $this->Init($this);
        }
    }
    protected function GetXTTCode()
    {
        $code = '';
        if(isset($_GET['X_TT']))
        {
            $code = trim($_GET['X_TT']);
        }
        else if(isset($_POST['X_TT']))
        {
            $code = trim($_POST['X_TT']);
        }
        return $code;
    }
    protected function UserIsAdmin()
    {
        if(MbqMain::$oCurMbqEtUser != null && MbqMain::$oCurMbqEtUser->userType->oriValue == 'admin')
        {
            return true;
        }
        return false;
    }
    protected function CanAccess()
    {
        if($this->UserIsAdmin())
        {
            return true;
        }
        $code = $this->GetXTTCode();
        if(!empty($code))
        {
            include_once(MBQ_3RD_LIB_PATH . 'classTTConnection.php');
            $classTTConnection  = new classTTConnection();
            return $classTTConnection->actionVerification($code, 'status_page', false);
        }

        $sessionId = session_id();
        if(isset($_POST['sid']) && !empty($sessionId) && md5($sessionId . 'statuspanel') == $_POST['sid'])
        {
            return true;
        }
        return false;
    }
    protected function FilePermissionOthers()
    {
        return array("result"=>1, "description"=>"N/A");
    }
    private function Doupdate()
    {

    }
    protected function GetPluginVersion()
    {
        return MbqMain::$customConfig['base']['version'];;
    }
    protected function GetPluginHookVersion()
    {
        return MbqMain::$customConfig['base']['hook_version'];;
    }
    protected function GetForumVersion()
    {
        return MbqMain::$customConfig['base']['sys_version'];;
    }
    public function TestPluginVersion()
    {
        $installedVersion = $this->GetPluginVersion();
        $installedHookVersion = $this->GetPluginHookVersion();
        $installedForumVersion = $this->GetForumVersion();
        $version = explode('_', $installedVersion);
        $pluginName = $version[0];
        $pluginVersion = $version[1];
        ini_set('max_execution_time',60);
        $lastVersion = '';
        include_once(MBQ_3RD_LIB_PATH . 'classTTConnection.php');
        $classTTConnection  = new classTTConnection();
        if($versions = $classTTConnection->getContentFromSever('https://tapatalk.com/version.json'))
        {
            $versions = json_decode($versions);
            $lastPluginInfo = $versions->stable->$pluginName;
            $downloadUrl = 'https://www.tapatalk.com' . $lastPluginInfo->download_url;
            $lastVersion = $pluginName . '_' . $lastPluginInfo->version_string;
            return array('pluginName' =>  $pluginName, 'installedVersion' => $installedVersion, 'installedHookVersion' => $installedHookVersion, 'installedForumVersion' => $installedForumVersion, 'lastVersion' => $lastVersion, 'downloadUrl' => $downloadUrl);
        }
        else
        {
            return array('pluginName' =>  $pluginName, 'installedVersion' => $installedVersion, 'installedHookVersion' => $installedHookVersion, 'installedForumVersion' => $installedForumVersion, 'lastVersionError' => 'Cannot connect to Tapatalk server to get last version info');
        }
    }
    public function TestConnectivity()
    {
        $curl_enabled = function_exists('curl_exec') && is_callable('curl_exec') && !in_array('curl_exec', array_map('trim', explode(', ', ini_get('disable_functions')))) && strtolower(ini_get('safe_mode')) != 1;
        $result = array('curl'=> $curl_enabled ? curl_version() : false,
            'fopen' => ini_get('allow_url_fopen'),
            'socket' => function_exists('fsockopen')
            );
        include_once(MBQ_3RD_LIB_PATH . 'classTTConnection.php');
        $classTTConnection  = new classTTConnection();
        $url = 'https://directory.tapatalk.com/au_reg_verify.php';
        $data['test'] = true;
        $time = microtime(true);
        $response = $classTTConnection->getContentFromSever($url, $data, 'post', false);
        $result['directory_time'] = round(microtime(true) - $time, 3);
        $result['directory'] = $classTTConnection->success && $response === '1';
        $result['directory_response'] = $classTTConnection->raw_headers . PHP_EOL . PHP_EOL  . $response;
        if(!empty($classTTConnection->warnings))
        {
            $result['directory_warnings'] = $classTTConnection->warnings;
        }
        if(!empty($classTTConnection->errors))
        {
            $result['directory_errors'] = $classTTConnection->errors;
        }

        $url = 'http://push.tapatalk.com/push.php';
        $data['test'] = true;
        $time = microtime(true);
        $response = $classTTConnection->getContentFromSever($url, $data, 'post', false);
        $result['push_time'] = round(microtime(true) - $time, 3);
        $result['push'] = $classTTConnection->success && $response === '1';
        $result['push_response'] = $classTTConnection->raw_headers . PHP_EOL . PHP_EOL  . $response;
        if(!empty($classTTConnection->warnings))
        {
            $result['push_warnings'] = $classTTConnection->warnings;
        }
        if(!empty($classTTConnection->errors))
        {
            $result['push_errors'] = $classTTConnection->errors;
        }

        $url = 'https://tapatalk.com/plugin_verify.php';
        $data['method'] = 'verify_connection';
        $time = microtime(true);
        $response = $classTTConnection->getContentFromSever($url, $data, 'post', true);
        $result['tapatalk_time']= round(microtime(true) - $time, 3);
        $result['tapatalk'] = $classTTConnection->success && $response === '0';
        $result['tapatalk_response'] = $classTTConnection->raw_headers . PHP_EOL . PHP_EOL  . $response;
        if(!empty($classTTConnection->warnings))
        {
            $result['tapatalk_warnings'] = $classTTConnection->warnings;
        }
        if(!empty($classTTConnection->errors))
        {
            $result['tapatalk_errors'] = $classTTConnection->errors;
        }

        $url = 'https://verify.tapatalk.com/plugin_verify.php';
        $data['method'] = 'verify_connection';
        $time = microtime(true);
        $response = $classTTConnection->getContentFromSever($url, $data, 'post', true);
        $result['verify_time']= round(microtime(true) - $time, 3);
        $result['verify'] = $classTTConnection->success && $response === '0';
        $result['verify_response'] = $classTTConnection->raw_headers . PHP_EOL . PHP_EOL  . $response;
        if(!empty($classTTConnection->warnings))
        {
            $result['verify_warnings'] = $classTTConnection->warnings;
        }
        if(!empty($classTTConnection->errors))
        {
            $result['verify_errors'] = $classTTConnection->errors;
        }

        $url = "http://search.tapatalk.com/api/plugin/forum_info";
        $data = array(
            'key' => '00000000000000000000000000',
            'url' => 'http://testconnectivity',
        );
        $time = microtime(true);
        $response = $classTTConnection->getContentFromSever($url, $data, 'post', true);
        $result['search_time']= round(microtime(true) - $time, 3);
        $result['search'] = $classTTConnection->success && strpos($response,'{"status":true,"') == 0;
        $result['search_response'] = $classTTConnection->raw_headers . PHP_EOL . PHP_EOL . $response;
        if(!empty($classTTConnection->warnings))
        {
            $result['search_warnings'] = $classTTConnection->warnings;
        }
        if(!empty($classTTConnection->errors))
        {
            $result['search_errors'] = $classTTConnection->errors;
        }

        return $result;
    }
    public function TestFilePermission()
    {
        $mobiquoDirectory = $this->GetMobiquoFileSytemDir();
        $result = array('mobiquoDir'=> $mobiquoDirectory);
        clearstatcache();
        $forumUrl = $this->GetForumUrl();
        $forumUrl .= (substr($forumUrl, -1) == '/' ? '' : '/');
        $result['forumUrl'] = $forumUrl;
        $result['pluginUrl'] = $forumUrl . $this->GetMobiquoDir() . '/mobiquo.php';
        $result['dirPermission'] = substr(sprintf('%o', fileperms($mobiquoDirectory)), -4);
        $result['filePermission'] =substr(sprintf('%o', fileperms($mobiquoDirectory .'mobiquo.php')), -4);
        $result['canExecuteMobiquoFile'] = is_readable($mobiquoDirectory.'mobiquo.php') ? true : false;
        $result['canExecuteUploadFile'] = is_readable($mobiquoDirectory.'upload.php') ? true : false;
        $result['filePermissionOthers'] = $this->FilePermissionOthers();
        return $result;
    }
    public function TestPush()
    {
        $result = array('apiKey'=> $this->GetApiKey());
        $slug = @unserialize($this->GetPushSlug());
        if($slug)
        {
            $result['slugOrigin'] = 'DB';
            $result['slugMaxTimes'] = $slug[0];             //max push failed attempt times in period
            $result['slugMaxTimesInPeriod'] = $slug[1];     //the limitation period
            $result['slugResult'] = $slug[2];               //indicate if the output is valid of not
            $result['slugResultText'] = $slug[3];           //invalid reason
            $result['slugStickTimeQueue'] = $slug[4];       //failed attempt timestamps
            $result['slugStick'] = $slug[5];                //indicate if push attempt is allowed
            $result['slugStickTimestamp'] = $slug[6];       //when did push be sticked
            $result['slugStickTime'] = $slug[7];            //how long will it be sticked
            $result['slugSave'] = $slug[8];                 //indicate if you need to save the slug into db
        }
        else
        {
            $result['slugOrigin'] = 'default';
            $result['slugMaxTimes'] = 3;             //max push failed attempt times in period
            $result['slugMaxTimesInPeriod'] = 300;     //the limitation period
            $result['slugResult'] = 1;               //indicate if the output is valid of not
            $result['slugResultText'] = '';           //invalid reason
            $result['slugStickTimeQueue'] = array();       //failed attempt timestamps
            $result['slugStick'] = 0;                //indicate if push attempt is allowed
            $result['slugStickTimestamp'] = 0;       //when did push be sticked
            $result['slugStickTime'] = 600;            //how long will it be sticked
            $result['slugSave'] = 1;                 //indicate if you need to save the slug into db
        }
        include_once(MBQ_PUSH_PATH . 'TapatalkPush.php');
        $tapatalkPush  = new TapatalkPush();
        $result['pushTest'] = $tapatalkPush->testPush();
        $result['pushRawHeaders'] = '<pre>' . $tapatalkPush->connection->raw_headers . '</pre>';
        $result['pushError'] = implode('<br>',$tapatalkPush->connection->error);
        return $result;
    }
    public function ResetBYOInfo()
    {
        include_once(MBQ_3RD_LIB_PATH . 'classTTConnection.php');
        $classTTConnection  = new classTTConnection();
        $url = 'https://siteowners.tapatalk.com/api/setFourmInfo?api_key=' . $this->GetApiKey();
        $response = $classTTConnection->getContentFromSever($url);
    }
    public function TestBYOInfo()
    {
        $result = array();
        $byoInfo = $this->GetBYOInfo();
        $result['byoForumId'] = isset($byoInfo['forum_id']) ? $byoInfo['forum_id'] : 'N/A';
        $result['byoBannerEnabled'] = isset($byoInfo['banner_enable']) ? $byoInfo['banner_enable'] : 'N/A';
        $result['byoUpdate'] = isset($byoInfo['update']) ? $byoInfo['update'] : 'N/A';
        $result['byoFacebookEnabled'] = isset($byoInfo['facebook_enable']) ? $byoInfo['facebook_enable'] : 'N/A';
        $result['byoTwitterEnabled'] = isset($byoInfo['twitter_enable']) ? $byoInfo['twitter_enable'] : 'N/A';
        $result['byoGoogleEnabled'] = isset($byoInfo['google_enable']) ? $byoInfo['google_enable'] : 'N/A';
        $result['byoTwitterAccount'] = isset($byoInfo['twitter_account']) ? $byoInfo['twitter_account'] : 'N/A';
        $result['byoAppName']= isset($byoInfo['byo_info']['app_name']) ? $byoInfo['byo_info']['app_name'] : 'N/A';
        $result['byoAppRebrandingId'] = isset($byoInfo['byo_info']['app_rebranding_id']) ? $byoInfo['byo_info']['app_rebranding_id'] : 'N/A';
        $result['byoAppIconUrl'] = isset($byoInfo['byo_info']['app_icon_url']) ? $byoInfo['byo_info']['app_icon_url'] : 'N/A';
        $result['byoAppUrlScheme'] = isset($byoInfo['byo_info']['app_url_scheme']) ? $byoInfo['byo_info']['app_url_scheme'] : 'N/A';
        $result['byoAppIosId'] = isset($byoInfo['byo_info']['app_ios_id']) ? $byoInfo['byo_info']['app_ios_id'] : 'N/A';
        $result['byoAppAndroidId'] = isset($byoInfo['byo_info']['app_android_id']) ? $byoInfo['byo_info']['app_android_id'] : 'N/A';
        $result['byoAppIosDescription'] = isset($byoInfo['byo_info']['app_ios_description']) ? $byoInfo['byo_info']['app_ios_description'] : 'N/A';
        $result['byoAppAndroidDescription'] = isset($byoInfo['byo_info']['app_android_description']) ? $byoInfo['byo_info']['app_android_description'] : 'N/A';
        $result['byoAppBannerMessage'] = isset($byoInfo['byo_info']['app_banner_message']) ? $byoInfo['byo_info']['app_banner_message'] : 'N/A';
        $result['byoAppBannerMessageIos'] = isset($byoInfo['byo_info']['app_banner_message_ios']) ? $byoInfo['byo_info']['app_banner_message_ios'] : 'N/A';
        $result['byoAppBannerMessageAndroid'] = isset($byoInfo['byo_info']['app_banner_message_android']) ? $byoInfo['byo_info']['app_banner_message_android'] : 'N/A';
        $result['byoAppAlertStatus'] = isset($byoInfo['byo_info']['app_alert_status']) ? $byoInfo['byo_info']['app_alert_status'] : 'N/A';
        $result['byoAppAlertMessage'] = isset($byoInfo['byo_info']['app_alert_message']) ? $byoInfo['byo_info']['app_alert_message'] : 'N/A';
        $result['fileRedirect'] = isset($byoInfo['file_redirect']) ? $byoInfo['file_redirect'] : 'N/A';
        $result['imageRedirect'] = isset($byoInfo['image_redirect']) ? $byoInfo['image_redirect'] : 'N/A';
        return $result;
    }

    public function TestOtherPlugins()
    {
        $pluginsInfo = $this->GetOtherPlugins();
        return $pluginsInfo;
    }
    function Init($mbqStatus)
    {
        global $mobiquo_config;

        // Create Session
        $sessionId = session_id();
        if(empty($sessionId)) session_start();

        $canAccess = $this->CanAccess();
        if(!$canAccess)
        {
            echo '<p>This user does not have permission to access Tapatalk Plugin Status page</p>';
            die;
        }

?>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Tapatalk Plugin Status</title>
    <style type="text/css">
        body {
            min-width: 100%;
            width: 100%;
            background-color: #f3f3f5;
            padding: 0;
        }
    </style>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.2.0.min.js"></script>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous" />

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous" />

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
</head>
<style>
    .header-container {
        display: table;
        height: 50px;
    }

    .logo-container {
        display: table-cell;
        text-align: left;
        vertical-align: middle;
        width: 100%;
    }

    .username-container {
        display: table-cell;
        text-align: right;
        vertical-align: middle;
        width: 100%;
        color: #ffffff;
    }

    .btn-right {
        float: right;
        margin-top: -23px;
    }
</style>
<body role="document">
    <nav class="navbar navbar-fixed-top" style="background-color:#1fb8dc">
        <div class="container">
            <div class="navbar-header">
                <div class="container header-container">
                    <div class="logo-container">
                        <img src="https://tapatalk.com/imgs/logo.png" style="width:36px;margin-right:10px;margin-left:-15px;" />
                        <img src="https://tapatalk.com/imgs/tapatalk-logo.png" style="height:40px;margin-top:10px" />
                    </div>
                    <div class="username-container">
                        Welcome <?php echo htmlspecialchars($mbqStatus->GetLoggedUserName()); ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container theme-showcase" role="main" style="margin-top:80px">

        <p>This is the status of your Tapatalk plugin installation:</p>
        <div id="panelSummarySuccess" class="alert alert-success panelSummary" role="alert" style="display:none">
            <strong>Well done!</strong>
            Your plugin installation passed all tests.
        </div>
        <div id="panelSummaryWarning" class="alert alert-warning panelSummary" role="alert" style="display:none">
            <strong>Warning!</strong>
            There are some warnings about your plugin installation, but it will work.
        </div>
        <div id="panelSummaryDanger" class="alert alert-danger panelSummary" role="alert" style="display:none">
            <strong>Oh snap!</strong>
            There are some issues with your plugin installation, please review and solve these.
        </div>
        <div class="row">
            <div class="col-sm-6">
                   <div class="panel panel-default" id="panelVersion">
                        <div class="panel-heading">
                            <h3 class="panel-title">Plugin version</h3>
                            <button type="button" class="btn btn-sm btn-right" onclick="TestPluginVersion();">Refresh</button>
                        </div>
                        <div class="panel-body">
                            PHP version:
                            <span id="phpversion">
                                <b>
                                    <?php echo phpversion() ?>
                                </b>
                            </span>
                            <br />
                            Last plugin available at Tapatalk:
                            <span id="lastVersion">Loading...</span>
                            <br />
                            Current plugin installed version:
                            <span id="installedVersion">Loading...</span>
                            <br />
                            Current hook installed version:
                            <span id="installedHookVersion">Loading...</span>
                            <br />
                            Current forum version:
                            <span id="installedForumVersion">Loading...</span>
                            <br />
                            <span id="versionStatus"></span>

                        </div>
                    </div>
                    <div class="panel panel-default" id="panelBYO">
                        <div class="panel-heading">
                            <h3 class="panel-title">BYO status</h3>
                            <button type="button" class="btn btn-sm btn-right" onclick="TestBYOInfo();">Refresh</button>
                            <button type="button" class="btn btn-sm btn-right" style="margin-right:5px" onclick="ResetBYOInfo();">Reset BYO Data</button>
                        </div>
                        <div class="panel-body">
                            ForumId:
                            <span id="byoForumId">Loading...</span>
                            <br />
                            Current data update date:
                            <span id="byoUpdate">Loading...</span>
                            <br />
                            Banner enabled:
                            <span id="byoBannerEnabled">Loading...</span>
                            <br />
                            Google meta enabled:
                            <span id="byoGoogleEnabled">Loading...</span>
                            <br />
                            Facebook meta enabled:
                            <span id="byoFacebookEnabled">Loading...</span>
                            <br />
                            Twitter card meta enabled:
                            <span id="byoTwitterEnabled">Loading...</span>
                            <br />
                            Twitter account:
                            <span id="byoTwitterAccount">Loading...</span>
                            <br />
                            App name:
                            <span id="byoAppName">Loading...</span>
                            <br />
                            App rebranding Id:
                            <span id="byoAppRebrandingId">Loading...</span>
                            <br />
                            App icon:
                            <span id="byoAppIconUrl">Loading...</span>
                            <br />
                            App url scheme:
                            <span id="byoAppUrlScheme">Loading...</span>
                            <br />
                            App banner message:
                            <span id="byoAppBannerMessage">Loading...</span>
                            <br />
                            App iOS id:
                            <span id="byoAppIosId">Loading...</span>
                            <br />
                            App iOS description:
                            <span id="byoAppIosDescription">Loading...</span>
                            <br />
                            App iOS banner message :
                            <span id="byoAppBannerMessageIos">Loading...</span>
                            <br />
                            App Android id:
                            <span id="byoAppAndroidId">Loading...</span>
                            <br />
                            App Android description:
                            <span id="byoAppAndroidDescription">Loading...</span>
                            <br />
                            App Android banner message:
                            <span id="byoAppBannerMessageAndroid">Loading...</span>
                            <br />
                            App alert status:
                            <span id="byoAppAlertStatus">Loading...</span>
                            <br />
                            App alert message:
                            <span id="byoAppAlertMessage">Loading...</span>
                            <br />
                            Tapatalk file redirect:
                            <span id="fileRedirect">Loading...</span>
                            <br />
                            Tapatalk image redirect:
                            <span id="imageRedirect">Loading...</span>
                            <br />
                        </div>
                    </div>
                    <div class="panel panel-default" id="panelOtherPlugins">
                        <div class="panel-heading">
                            <h3 class="panel-title">Other plugins installed</h3>
                            <button type="button" class="btn btn-sm btn-right" onclick="TestOtherPlugins();">Refresh</button>
                        </div>
                        <div class="panel-body">
                            <div id="panelOtherPluginsDetail"></div>
                        </div>
                    </div>

                </div>
            <div class="col-sm-6">
             
                    <div class="panel panel-default" id="panelConnectivity">
                        <div class="panel-heading">
                            <h3 class="panel-title">Connectivity to Tapatalk servers</h3>
                            <button type="button" class="btn btn-sm btn-right" onclick="TestConnectivity();">Refresh</button>
                        </div>
                        <div class="panel-body">
                            cUrl available:
                            <span id="connectivityCurl">Loading...</span>
                            <br />
                            fopen available:
                            <span id="connectivityFopen">Loading...</span>
                            <br />
                            sockets available:
                            <span id="connectivitySocket">Loading...</span>
                            <br />
                            Connectivity to directory.tapatalk.com:
                            <span id="connectivityDirectory">Loading...</span>
                            <pre id="connectivityDirectoryResponse" style="display:none">Loading...</pre>
                            <br />
                            Connectivity to push.tapatalk.com:
                            <span id="connectivityPush">Loading...</span>
                            <pre id="connectivityPushResponse" style="display:none">Loading...</pre>
                            <br />
                            Connectivity to tapatalk.com:
                            <span id="connectivityTapatalk">Loading...</span>
                            <pre id="connectivityTapatalkResponse" style="display:none">Loading...</pre>
                            <br />
                            Connectivity to verify.tapatalk.com:
                            <span id="connectivityVerify">Loading...</span>
                            <pre id="connectivityVerifyResponse" style="display:none">Loading...</pre>
                            <br />
                            Connectivity to search.tapatalk.com:
                            <span id="connectivitySearch">Loading...</span>
                            <pre id="connectivitySearchResponse" style="display:none">Loading...</pre>
                            <br />
                        </div>
                    </div>
                    <div class="panel panel-default" id="panelFilePermissions">
                        <div class="panel-heading">
                            <h3 class="panel-title">Plugin directory/files status</h3>
                            <button type="button" class="btn btn-sm btn-right" onclick="TestFilePermission();">Refresh</button>
                        </div>
                        <div class="panel-body">
                            Forum Url:
                            <span id="filePermissionsForumUrl">Loading...</span>
                            <br />
                            Plugin Url:
                            <span id="filePermissionsPluginUrl">Loading...</span>
                            <br />
                            Plugin directory permissions:
                            <span id="filePermissionsDirectory">Loading...</span>
                            <br />
                            Plugin files permissions:
                            <span id="filePermissionsFiles">Loading...</span>
                            <br />
                            Can execute mobiquo.php file:
                            <span id="filePermissionsCanExecuteMobiquoFile">Loading...</span>
                            <br />
                            Can execute upload.php file:
                            <span id="filePermissionsCanExecuteUploadFile">Loading...</span>
                            <br />
                            Extra file permissions:
                            <span id="filePermissionsOthers">Loading...</span>
                        </div>
                    </div>
                    <div class="panel panel-default" id="panelPush">
                        <div class="panel-heading">
                            <h3 class="panel-title">Push notifications status</h3>
                            <button type="button" class="btn btn-sm btn-right" onclick="TestPush();">Refresh</button>
                            <button type="button" class="btn btn-sm btn-right" style="margin-right:5px" onclick="ResetPushSlug();">Reset Push slug Data</button>
                        </div>
                        <div class="panel-body">
                            Tapatalk Push API key set:
                            <span id="pushApikey">Loading...</span>
                            <br />
                            Origin:
                            <span id="slugOrigin">Loading...</span>
                            <br />
                            Max push failed attempt times in period:
                            <span id="slugMaxTimes">Loading...</span>
                            <br />
                            The limitation period:
                            <span id="slugMaxTimesInPeriod">Loading...</span>
                            <br />
                            Indicate if the output is valid of not:
                            <span id="slugResult">Loading...</span>
                            <br />
                            Invalid reason:
                            <span id="slugResultText">Loading...</span>
                            <br />
                            Failed attempt timestamps:
                            <span id="slugStickTimeQueue">Loading...</span>
                            <br />
                            Indicate if push attempt is allowed:
                            <span id="slugStick">Loading...</span>
                            <br />
                            When did push be sticked:
                            <span id="slugStickTimestamp">Loading...</span>
                            <br />
                            How long will it be sticked:
                            <span id="slugStickTime">Loading...</span>
                            <br />
                            Indicate if you need to save the slug into db:
                            <span id="slugSave">Loading...</span>
                            <br />
                            <br />
                            Push Test result:
                            <span id="pushTest">Loading...</span>
                            <br />
                            Push Test error:
                            <span id="pushError">Loading...</span>
                            <br />
                            Push Raw Headers:
                            <span id="pushRawHeaders">Loading...</span>
                        </div>
                    </div>

               
            </div>

        </div>
        <script language="javascript">
            var testResult = null;
            var sid = '<?php echo md5(session_id() . 'statuspanel') ?>';
            var xtt = '<?php echo htmlspecialchars($this->GetXTTCode()) ?>';
            $(document).ready(function () {
                ResetTestResult();
                TestPluginVersion();
                TestConnectivity();
                TestFilePermission();
                TestPush();
                TestOtherPlugins();
                TestBYOInfo();
            });
            function ResetTestResult() {
                testResult = { 'pluginVersion': null, 'connectivity': null, 'filePermission': null, 'push': null };
            }
            function SetTestResult(testResult, newValue) {
                if (testResult > newValue) {
                    testResult = newValue;
                }
                return testResult;
            }
            function ProcessSummary() {
                $('.panelSummary').hide();
                if (testResult.pluginVersion == null || testResult.connectivity == null || testResult.filePermission == null || testResult.push == null || testResult.byoinfo == null || testResult.otherplugins == null) {
                    return;
                }
                if (testResult.pluginVersion == 1 && testResult.connectivity == 1 && testResult.filePermission == 1 && testResult.push == 1 && testResult.byoinfo == 1 && testResult.otherplugins == 1) {
                    $('#panelSummarySuccess').show();
                }
                else if (testResult.pluginVersion == -1 || testResult.connectivity == -1 || testResult.filePermission == -1 || testResult.push == -1 || testResult.byoinfo == -1 || testResult.otherplugins == -1) {
                    $('#panelSummaryDanger').show();
                }
                else {
                    $('#panelSummaryWarning').show();
                }
            }
            function SetSpan(spanId, color, text) {
                $('#' + spanId).html(text);
                $('#' + spanId).css({ 'color': color, 'font-weight': 'bold' });
            }
            function SetElementHtml(elementId, text) {
                $('#' + elementId).html(text);
            }
            function SetPanel(panelId, css) {
                $('#' + panelId).removeClass().addClass('panel').addClass(css);
            }
            function SetPanelByStatus(panelId, testStatus) {
                switch (testStatus) {
                    case 1:
                        {
                            SetPanel(panelId, 'panel-success');
                            break;
                        }
                    case 0:
                        {
                            SetPanel(panelId, 'panel-warning');
                            break;
                        }
                    case -1:
                        {
                            SetPanel(panelId, 'panel-danger');
                            break;
                        }
                }
            }
            function TestPluginVersion() {
                testResult.pluginVersion = null;
                ProcessSummary();
                SetSpan('lastVersion', 'black', 'Loading...');
                SetSpan('installedVersion', 'black', 'Loading...');
                SetSpan('installedHookVersion', 'black', 'Loading...');
                SetSpan('installedForumVersion', 'black', 'Loading...');
                SetSpan('versionStatus', 'black', '');
                SetPanel('panelVersion', 'panel-default');

                $.post('status.php', { command: 'testPluginVersion', sid: sid, X_TT: xtt })
                .done(function (data) {
                    result = JSON.parse(data);
                    resultPluginVersion = 1;
                    if (result.lastVersionError == null) {
                        SetSpan('lastVersion', 'green', result.lastVersion);
                        if (result.installedVersion == result.lastVersion) {
                            SetSpan('installedVersion', 'green', result.installedVersion);
                            SetSpan('versionStatus', 'green', 'You have the latest plugin version installed :)');
                        }
                        else if (result.installedVersion > result.lastVersion) {
                            SetSpan('installedVersion', 'green', result.installedVersion);
                            SetSpan('versionStatus', 'green', 'You are in a beta version ahead of last plugin version :)');
                        }
                        else {
                            SetSpan('installedVersion', 'orange', result.installedVersion);
                            SetSpan('versionStatus', 'orange', 'There is a new version of the plugin available <button href= class="btn btn-sx btn-info" onclick="window.open(\'' + result.downloadUrl + '\');">Download</button>');
                            resultPluginVersion = SetTestResult(resultPluginVersion, 0);
                        }
                    }
                    else {
                        SetSpan('lastVersion', 'red', result.lastVersionError);
                        SetSpan('installedVersion', 'green', result.installedVersion);
                        resultPluginVersion = SetTestResult(resultPluginVersion, -1);
                    }
                    testResult.pluginVersion = resultPluginVersion;
                    SetPanelByStatus('panelVersion', testResult.pluginVersion);
                    SetSpan('installedHookVersion', 'green', result.installedHookVersion);
                    SetSpan('installedForumVersion', 'green', result.installedForumVersion);

                })
                .fail(function (error) {

                })
                .always(function () {
                    ProcessSummary();
                });

            }
            function TestConnectivity() {
                testResult.connectivity = null;
                ProcessSummary();

                SetSpan('connectivityCurl', 'black', 'Loading...');
                SetSpan('connectivityFopen', 'black', 'Loading...');
                SetSpan('connectivitySocket', 'black', 'Loading...');
                SetSpan('connectivityDirectory', 'black', 'Loading...');
                SetSpan('connectivityPush', 'black', 'Loading...');
                SetSpan('connectivityTapatalk', 'black', 'Loading...');
                SetSpan('connectivityVerify', 'black', 'Loading...');
                SetSpan('connectivitySearch', 'black', 'Loading...');
                SetElementHtml('connectivityDirectoryResponse', '');
                SetElementHtml('connectivityPushResponse', '');
                SetElementHtml('connectivityTapatalkResponse', '');
                SetElementHtml('connectivityVerifyResponse', '');
                SetElementHtml('connectivitySearchResponse', '');
                SetPanel('panelConnectivity', 'panel-default');

                $.post('status.php', { command: 'testConnectivity', sid: sid, X_TT: xtt })
                .done(function (data) {
                    resultConnectivity = 1; //1 ok, 0 warning, -1 fail
                    result = JSON.parse(data);
                    if (result.curl) {
                        SetSpan('connectivityCurl', 'green', 'OK!, version ' + result.curl['version'] + ' detected');
                    }
                    else {
                        SetSpan('connectivityCurl', 'orange', 'cUrl is not installed in your system. We will fallback to fopen or sockets, but its recomended you install cUrl in your system');
                        resultConnectivity = SetTestResult(resultConnectivity, 0);
                    }
                    if (result.fopen) {
                        SetSpan('connectivityFopen', 'green', 'OK!');
                    }
                    else {
                        SetSpan('connectivityFopen', 'red', result.fopen);
                        resultConnectivity = SetTestResult(resultConnectivity, !result.curl ? -1 : 0);
                    }
                    if (result.socket) {
                        SetSpan('connectivitySocket', 'green', 'OK!');
                    }
                    else {
                        SetSpan('connectivitySocket', 'red', result.socket);
                        resultConnectivity = SetTestResult(resultConnectivity, !result.curl && !result.socket ? -1 : 0);
                    }
                    if (result.directory) {
                        SetElementHtml('connectivityDirectoryResponse', result.directory_response);
                        if (result.directory_errors == null && result.directory_warnings == null) {
                            SetSpan('connectivityDirectory', 'green', 'OK! ' + result.directory_time + ' seg.');
                        }
                        else {
                            SetSpan('connectivityDirectory', 'orange', 'OK!, but ' + (result.directory_warnings == null ? '' : result.directory_warnings.join('</br>') + '</br>') + result.directory_errors.join('</br>') + ' ' + result.directory_time + ' seg' );
                            resultConnectivity = SetTestResult(resultConnectivity, 0);
                        }
                    }
                    else {
                        SetSpan('connectivityDirectory', 'red', 'FAIL! ' + (result.directory_warnings == null ? '' : result.directory_warnings.join('</br>') + '</br>') + result.directory_errors.join('</br>')+ ' ' + result.directory_time + ' seg');
                        resultConnectivity = SetTestResult(resultConnectivity, -1);
                    }

                    if (result.push) {
                        SetElementHtml('connectivityPushResponse', result.push_response);
                        if (result.push_errors == null && result.push_warnings == null) {
                            SetSpan('connectivityPush', 'green', 'OK! ' + result.push_time + ' seg.');
                        }
                        else {
                            SetSpan('connectivityPush', 'orange', 'OK!, but ' + (result.push_warnings == null ? '' : result.push_warnings.join('</br>') + '</br>') + result.push_errors.join('</br>')+ ' ' + result.push_time + ' seg');
                            resultConnectivity = SetTestResult(resultConnectivity, 0);
                        }
                    }
                    else {
                        SetSpan('connectivityPush', 'red', 'FAIL! ' + (result.push_warnings == null ? '' : result.push_warnings.join('</br>') + '</br>') + result.push_errors.join('</br>')+ ' ' + result.push_time + ' seg');
                        resultConnectivity = SetTestResult(resultConnectivity, -1);
                    }

                    if (result.tapatalk) {
                        SetElementHtml('connectivityTapatalkResponse', result.tapatalk_response);
                        if (result.tapatalk_errors == null && result.tapatalk_warnings == null) {
                            SetSpan('connectivityTapatalk', 'green', 'OK! ' + result.tapatalk_time + ' seg.');
                        }
                        else {
                            SetSpan('connectivityTapatalk', 'orange', 'OK!, but ' + (result.tapatalk_warnings == null ? '' : result.tapatalk_warnings.join('</br>') + '</br>') + result.tapatalk_errors.join('</br>')+ ' ' + result.tapatalk_time + ' seg');
                            resultConnectivity = SetTestResult(resultConnectivity, 0);
                        }
                    }
                    else {
                        SetSpan('connectivityTapatalk', 'red', 'FAIL! ' + (result.tapatalk_warnings == null ? '' : result.tapatalk_warnings.join('</br>') + '</br>') + result.tapatalk_errors.join('</br>')+ ' ' + result.tapatalk_time + ' seg');
                        resultConnectivity = SetTestResult(resultConnectivity, -1);
                    }

                    if (result.verify) {
                        SetElementHtml('connectivityVerifyResponse', result.verify_response);
                        if (result.verify_errors == null && result.verify_warnings == null) {
                            SetSpan('connectivityVerify', 'green', 'OK! ' + result.verify_time + ' seg.');
                        }
                        else {
                            SetSpan('connectivityVerify', 'orange', 'OK!, but ' + (result.verify_warnings == null ? '' : result.verify_warnings.join('</br>') + '</br>') + result.verify_errors.join('</br>')+ ' ' + result.verify_time + ' seg');
                            resultConnectivity = SetTestResult(resultConnectivity, 0);
                        }
                    }
                    else {
                        SetSpan('connectivityVerify', 'red', 'FAIL! ' + (result.verify_warnings == null ? '' : result.verify_warnings.join('</br>') + '</br>') + result.verify_errors.join('</br>')+ ' ' + result.verify_time + ' seg');
                        resultConnectivity = SetTestResult(resultConnectivity, -1);
                    }

                    if (result.search) {
                        SetElementHtml('connectivitySearchResponse', result.search_response);
                        if (result.search_errors == null && result.search_warnings == null) {
                            SetSpan('connectivitySearch', 'green', 'OK! ' + result.search_time + ' seg.');
                        }
                        else {
                            SetSpan('connectivitySearch', 'orange', 'OK!, but ' + (result.search_warnings == null ? '' : result.search_warnings.join('</br>') + '</br>') + result.search_errors.join('</br>')+ ' ' + result.search_time + ' seg');
                            resultConnectivity = SetTestResult(resultConnectivity, 0);
                        }
                    }
                    else {
                        SetSpan('connectivitySearch', 'red', 'FAIL! ' + (result.search_warnings == null ? '' : result.search_warnings.join('</br>') + '</br>') + result.search_errors.join('</br>')+ ' ' + result.search_time + ' seg');
                        resultConnectivity = SetTestResult(resultConnectivity, -1);
                    }

                    testResult.connectivity = resultConnectivity;
                    SetPanelByStatus('panelConnectivity', testResult.connectivity)
                })
                .fail(function (error) {

                })
                .always(function () {
                    ProcessSummary();
                });
            }
            function TestFilePermission() {
                testResult.filePermission = null;
                ProcessSummary();

                SetSpan('filePermissionsForumUrl', 'black', 'Loading...');
                SetSpan('filePermissionsPluginUrl', 'black', 'Loading...');
                SetSpan('filePermissionsDirectory', 'black', 'Loading...');
                SetSpan('filePermissionsFiles', 'black', 'Loading...');
                SetSpan('filePermissionsCanExecuteMobiquoFile', 'black', 'Loading...');
                SetSpan('filePermissionsCanExecuteUploadFile', 'black', 'Loading...');
                SetSpan('filePermissionsOthers', 'black', 'Loading...');
                        
                SetPanel('panelFilePermissions', 'panel-default');

                $.post('status.php', { command: 'testFilePermission', sid: sid, X_TT: xtt })
                .done(function (data) {
                    resultFilePermission = 1;
                    result = JSON.parse(data);
                    SetSpan('filePermissionsForumUrl', 'dark-gray', result.forumUrl);
                    SetSpan('filePermissionsPluginUrl', 'dark-gray', result.pluginUrl);
                    SetSpan('filePermissionsDirectory', 'dark-gray', result.dirPermission);
                    SetSpan('filePermissionsFiles', 'dark-gray', result.filePermission);
                    if (result.canExecuteMobiquoFile) {
                        SetSpan('filePermissionsCanExecuteMobiquoFile', 'green', 'Yes');
                    }
                    else {
                        SetSpan('filePermissionsCanExecuteMobiquoFile', 'red', 'No. The file mobiquo.php need to be executable to make the plugin work properly, please set his permission to something that allow execute that file (eg. 755).');
                        resultFilePermission = SetTestResult(resultFilePermission, -1);
                    }
                    if (result.canExecuteUploadFile) {
                        SetSpan('filePermissionsCanExecuteUploadFile', 'green', 'Yes');
                    }
                    else {
                        SetSpan('filePermissionsCanExecuteUploadFile', 'red', 'No. The file upload.php need to be executable to allow plugin accept attachments, please set his permission to something that allow execute that file (eg. 755).');
                        resultFilePermission = SetTestResult(resultFilePermission, -1);
                    }
                    SetSpan('filePermissionsOthers', 'dark-gray', result.filePermissionOthers.description);
                    if(result.filePermissionOthers.result === -1)
            {resultFilePermission = SetTestResult(resultFilePermission, -1);}
          
                    testResult.filePermission = resultFilePermission;
                    SetPanelByStatus('panelFilePermissions', testResult.filePermission);
                })
                .fail(function (error) {

                })
                .always(function () {
                    ProcessSummary();
                });
            }
            function ResetPushSlug() {
                $.post('status.php', { command: 'resetPushSlug', sid: sid, X_TT: xtt })
                    .done(function (data) {
                        TestPush();
                    });
            }
            function TestPush() {
                testResult.push = null;
                ProcessSummary();
                SetSpan('pushApikey', 'black', 'Loading...');
                SetSpan('pushSlug', 'black', 'Loading...');
                SetPanel('panelPush', 'panel-default');

                $.post('status.php', { command: 'testPush', sid: sid, X_TT: xtt })
                .done(function (data) {
                    result = JSON.parse(data);
                    resultPush = 1;
                    if (result.apiKey) {
                        SetSpan('pushApikey', 'green', result.apiKey);
                    }
                    else {
                        SetSpan('pushApikey', 'red', 'ApiKey is not set. Please setup it in your plugin Tapatalk admin panel so Push Notifications can work properly.');
                        resultPush = SetTestResult(resultPush, -1);
                    }

                    SetSpan('slugOrigin', 'green', result.slugOrigin);
                    SetSpan('slugMaxTimes', 'green', result.slugMaxTimes);
                    SetSpan('slugMaxTimesInPeriod', 'green', result.slugMaxTimesInPeriod);
                    SetSpan('slugResult', 'green', result.slugResult);
                    SetSpan('slugResultText', 'green', result.slugResultText);
                    SetSpan('slugStickTimeQueue', 'green', result.slugStickTimeQueue);
                    SetSpan('slugStick', 'green', result.slugStick);
                    SetSpan('slugStickTimestamp', 'green', result.slugStickTimestamp);
                    SetSpan('slugStickTime', 'green', result.slugStickTime);
                    SetSpan('slugSave', 'green', result.slugSave);
                    SetSpan('pushTest', 'green', result.pushTest);
                    SetSpan('pushError', 'green', result.pushError);
                    SetSpan('pushRawHeaders', 'black', result.pushRawHeaders);
                    testResult.push = resultPush;
                    SetPanelByStatus('panelPush', testResult.push);

                })
                .fail(function (error) {

                })
                .always(function () {
                    ProcessSummary();
                });

            }
            function ResetBYOInfo() {
                $.post('status.php', { command: 'resetBYOInfo', sid: sid, X_TT: xtt })
                    .done(function (data) {
                        TestBYOInfo();
                    });
            }
            function TestBYOInfo() {
                testResult.byoinfo = null;
                ProcessSummary();
                SetSpan('byoForumId', 'black', 'Loading...');
                SetSpan('byoUpdate', 'black', 'Loading...');
                SetSpan('byoBannerEnabled', 'black', 'Loading...');
                SetSpan('byoGoogleEnabled', 'black', 'Loading...');
                SetSpan('byoFacebookEnabled', 'black', 'Loading...');
                SetSpan('byoTwitterEnabled', 'black', 'Loading...');
                SetSpan('byoTwitterAccount', 'black', 'Loading...');
                SetSpan('byoAppName', 'black', 'Loading...');
                SetSpan('byoAppRebrandingId', 'black', 'Loading...');
                SetSpan('byoAppIconUrl', 'black', 'Loading...');
                SetSpan('byoAppUrlScheme', 'black', 'Loading...');
                SetSpan('byoAppBannerMessage', 'black', 'Loading...');
                SetSpan('byoAppIosId', 'black', 'Loading...');
                SetSpan('byoAppIosDescription', 'black', 'Loading...');
                SetSpan('byoAppBannerMessageIos', 'black', 'Loading...');
                SetSpan('byoAppAndroidId', 'black', 'Loading...');
                SetSpan('byoAppAndroidDescription', 'black', 'Loading...');
                SetSpan('byoAppBannerMessageAndroid', 'black', 'Loading...');
                SetSpan('byoAppAlertStatus', 'black', 'Loading...');
                SetSpan('byoAppAlertMessage', 'black', 'Loading...');
                SetSpan('fileRedirect', 'black', 'Loading...');
                SetSpan('imageRedirect', 'black', 'Loading...');
                SetPanel('panelBYO', 'panel-default');

                $.post('status.php', { command: 'testBYOInfo', sid: sid, X_TT: xtt })
                .done(function (data) {
                    result = JSON.parse(data);
                    resultBYOInfo = 1;

                    SetSpan('byoForumId', 'black', result.byoForumId);
                    SetSpan('byoUpdate', 'black', result.byoUpdate);
                    SetSpan('byoBannerEnabled', 'black', result.byoBannerEnabled);
                    SetSpan('byoGoogleEnabled', 'black', result.byoGoogleEnabled);
                    SetSpan('byoFacebookEnabled', 'black', result.byoFacebookEnabled);
                    SetSpan('byoTwitterEnabled', 'black', result.byoTwitterEnabled);
                    SetSpan('byoTwitterAccount', 'black', result.byoTwitterAccount);
                    SetSpan('byoAppName', 'black', result.byoAppName);
                    SetSpan('byoAppRebrandingId', 'black', result.byoAppRebrandingId);
                    SetSpan('byoAppIconUrl', 'black', result.byoAppIconUrl);
                    SetSpan('byoAppUrlScheme', 'black', result.byoAppUrlScheme);
                    SetSpan('byoAppBannerMessage', 'black', result.byoAppBannerMessage);
                    SetSpan('byoAppIosId', 'black', result.byoAppIosId);
                    SetSpan('byoAppIosDescription', 'black', result.byoAppIosDescription);
                    SetSpan('byoAppBannerMessageIos', 'black', result.byoAppBannerMessageIos);
                    SetSpan('byoAppAndroidId', 'black', result.byoAppAndroidId);
                    SetSpan('byoAppAndroidDescription', 'black', result.byoAppAndroidDescription);
                    SetSpan('byoAppBannerMessageAndroid', 'black', result.byoAppBannerMessageAndroid);
                    SetSpan('byoAppAlertStatus', 'black', result.byoAppAlertStatus);
                    SetSpan('byoAppAlertMessage', 'black', result.byoAppAlertMessage);
                    SetSpan('fileRedirect', 'black', result.fileRedirect);
                    SetSpan('imageRedirect', 'black', result.imageRedirect);

                    testResult.byoinfo = resultBYOInfo;
                    SetPanelByStatus('panelBYO', testResult.byoinfo);

                })
                .fail(function (error) {

                })
                .always(function () {
                    ProcessSummary();
                });

            }
            function TestOtherPlugins() {
                testResult.otherplugins = null;
                ProcessSummary();
                SetPanel('panelOtherPlugins', 'panel-default');
                $('#panelOtherPluginsDetail').empty();
                $.post('status.php', { command: 'testOtherPlugins', sid: sid, X_TT: xtt })
                .done(function (data) {
                    result = JSON.parse(data);
                    $.each(result, function (index, ele) {
                        $('#panelOtherPluginsDetail').append(ele.name + ' ' + ele.version + '</br>');
                    });
                    testResult.otherplugins = 1;
                    SetPanelByStatus('panelOtherPlugins', testResult.otherplugins);

                })
                .fail(function (error) {

                })
                .always(function () {
                    ProcessSummary();
                });

            }
        </script>
</body>

</html>

<?php
    }

}