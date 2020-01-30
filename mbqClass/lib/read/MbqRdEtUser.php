<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtUser');

/**
 * user read class
 */
Class MbqRdEtUser extends MbqBaseRdEtUser {

    public function __construct() {
    }

    public function makeProperty(&$oMbqEtUser, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
                MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
                break;
        }
    }
    public function login($login, $password, $anonymous, $push) {
        global $auth, $user, $config, $db, $phpbb_root_path, $phpEx;
	if(getPHPBBVersion() == '3.0')
	{
	}
	else
	{
	      require_once($phpbb_root_path .'/includes/functions_user.'. $phpEx);
	}
        if($login == null && $password == null)
        {
            $userId = $user->data['user_id'];
            if($userId != ANONYMOUS)
            {
                MbqMain::$oMbqAppEnv->currentUserInfo = $user->data;
                $this->initOCurMbqEtUser($userId);
                return true;
            }
        }
        $user->session_kill();

        $user->setup('ucp');

        set_var($login, $login, 'string', true);
        set_var($password, $password, 'string', true);
        header('Set-Cookie: mobiquo_a=0');
        header('Set-Cookie: mobiquo_b=0');
        header('Set-Cookie: mobiquo_c=0');

        if(!get_user_id_by_name($login))
        {
            return 'Username does not exists';
        }
        $config['max_login_attempts'] = 20;
        $config['ip_login_limit_max'] = 50;
        $login_result = $auth->login($login, $password, true, !$anonymous);
        if ($login_result['status'] == LOGIN_SUCCESS)
        {
            $auth->acl($user->data);

        } else {

            if ($login_result['status'] == LOGIN_ERROR_ACTIVE && $login_result['error_msg'] == 'ACTIVE_ERROR') {
                if ($config['require_activation'] == USER_ACTIVATION_ADMIN) {
                    $login_result['error_msg'] = 'ACCOUNT_INACTIVE_ADMIN';
                }
            }

            return str_replace('%s', '', strip_tags($user->lang[$login_result['error_msg']]));
        }



        return $this->doLogin($user);
    }
    public function loginDirectly($oMbqEtUser, $trustCode) {
        global $auth, $request, $user, $config, $db, $phpbb_root_path, $phpEx, $phpbb_container;

//        if($oMbqEtUser->userType->oriValue === 'unapproved' || $oMbqEtUser->userType->oriValue === 'inactive')
//        {
//            // inactive user can login
//            // return 'The user is currently inactive or forum require admin approval for new accounts. Please check for account verification email or wait admin approval. If you have problems activating your account, please contact a board administrator.';
//        }

        $user->session_kill();

        $user->setup('ucp');

        $login = $oMbqEtUser->userName->oriValue;
        $password = "";
        set_var($login, $login, 'string', true);
        set_var($password, $password, 'string', true);
        header('Set-Cookie: mobiquo_a=0');
        header('Set-Cookie: mobiquo_b=0');
        header('Set-Cookie: mobiquo_c=0');
        $user_id = get_user_id_by_name($login);
        if(!$user_id)
        {
            return 'Username does not exists';
        }
        $config['max_login_attempts'] = 20;
        $config['ip_login_limit_max'] = 50;

        $old_session_id = $user->session_id;

        $admin = 0;
        if ($admin)
        {
            global $SID, $_SID;

            $cookie_expire = time() - 31536000;
            $user->set_cookie('u', '', $cookie_expire);
            $user->set_cookie('sid', '', $cookie_expire);
            unset($cookie_expire);

            $SID = '?sid=';
            $user->session_id = $_SID = '';
        }

        $result = $user->session_create($user_id, $admin, false, 1);

        // Successful session creation
        if ($result === true)
        {
            // If admin re-authentication we remove the old session entry because a new one has been created...
            if ($admin)
            {
                // the login array is used because the user ids do not differ for re-authentication
                $sql = 'DELETE FROM ' . SESSIONS_TABLE . "
                            WHERE session_id = '" . $db->sql_escape($old_session_id) . "'
                            AND session_user_id = {$login['user_row']['user_id']}";
                $db->sql_query($sql);
            }
        }

        return $this->doLogin($user);

    }
    private function doLogin($user)
    {
        global $auth, $config, $db, $phpbb_root_path, $phpEx;
        $auth->acl($user->data);
        if ($config['max_attachments'] == 0) $config['max_attachments'] = 100;

        $flood_interval = 0;
        if ($config['flood_interval'] && !$auth->acl_get('u_ignoreflood'))
        {
            $flood_interval = intval($config['flood_interval']);
        }
        $userId = $user->data['user_id'];
        if (isset($user->data['origin_user_id']) && $user->data['origin_user_id']) {
            $userId = $user->data['origin_user_id']; // inactive user can login
            $user->data['user_id'] = $userId;
        }
        if($userId == ANONYMOUS)
        {
            return "User is banned and cannot login";
        }
        MbqMain::$oMbqAppEnv->currentUserInfo = $user->data;
        $this->initOCurMbqEtUser($userId);
        return true;
    }
    public function logout()
    {
        global $user;
        $user->session_kill();
        return true;
    }
    public function getDisplayName($oMbqEtUser) {
        //return $oMbqEtUser->loginName->oriValue;
        return htmlspecialchars_decode($oMbqEtUser->loginName->oriValue);
    }

    public function initOCurMbqEtUser($userId) {
        if (MbqMain::$oMbqAppEnv->currentUserInfo) {
            MbqMain::$Cache->Del('MbqEtUser',MbqMain::$oMbqAppEnv->currentUserInfo['user_id']);
            MbqMain::$oCurMbqEtUser = $this->initOMbqEtUser(MbqMain::$oMbqAppEnv->currentUserInfo, array('case'=>'user_row'));
        }
    }
    /**
     * get user objs
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byUserIds' means get data by user ids.$var is the ids.
     * @mbqOpt['case'] = 'online' means get online user.
     * @return  Array
     */
    public function getObjsMbqEtUser($var, $mbqOpt) {
        global $auth, $user, $config, $db, $phpbb_root_path, $phpEx;
        $onlinetime = time() - ($config['load_online_time'] * 60);
        if ($mbqOpt['case'] == 'byUserIds') {
            $result = array();
            $user_ids = implode(',',$var);
            $sql = "    SELECT u.*
                        FROM " . USERS_TABLE . " AS u
                        WHERE u.user_id in ($user_ids)
                    ";
            $sqlresult = $db->sql_query($sql);
            $userRows = array();
            while($row = $db->sql_fetchrow($sqlresult))
            {
                $userRows[] = $row;
            }
            $db->sql_freeresult($sqlresult);
            if ($config['load_onlinetrack'] && sizeof($var))
            {
                $sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
		FROM ' . SESSIONS_TABLE . '
		WHERE ' . $db->sql_in_set('session_user_id', $user_ids) . '
		GROUP BY session_user_id';
                $result = $db->sql_query($sql);

                $update_time = $config['load_online_time'] * 60;
                while ($row = $db->sql_fetchrow($result))
                {
                    MbqMain::$Cache->Set('MbqEtUserOnline', $row['session_user_id'], (time() - $update_time < $row['online_time'] && (($row['viewonline']) || $auth->acl_get('u_viewonline'))) ? true : false);
                }
                $db->sql_freeresult($result);
            }
            return $this->getObjsMbqEtUser($userRows, array('case'=>'byUserRows'));
        } elseif ($mbqOpt['case'] == 'byUserRows') {
            $userRows = $var;
            $result = array();
            $this->preloadUserGroups($userRows);
            foreach($userRows as $userRow)
            {
                $result[] = $this->initOMbqEtUser($userRow, array('case'=>'user_row'));
            }
            return $result;
        } elseif ($mbqOpt['case'] == 'online') {
            $oMbqDataPage = $mbqOpt['oMbqDataPage'];
            $sql = 'SELECT s.session_user_id, s.session_ip, s.session_viewonline
		            FROM ' . SESSIONS_TABLE . ' s
		            WHERE s.session_time >= ' . $onlinetime .
		            ' AND s.session_user_id <> ' . ANONYMOUS . ' GROUP BY s.session_user_id, s.session_ip, s.session_viewonline ';
            $result = $db->sql_query_limit($sql, $oMbqDataPage->numPerPage, $oMbqDataPage->startNum);
            $oMbqDataPage->datas = array();
            while($member = $db->sql_fetchrow($result))
            {
                $oMbqDataPage->datas[] = $this->initOMbqEtUser($member['session_user_id'], array('case'=>'byUserId'));
            }
            $db->sql_freeresult($result);
            return $oMbqDataPage;
        }
        elseif ($mbqOpt['case'] == 'recommended') {
            $mode = $var;
            $oMbqDataPage = $mbqOpt['oMbqDataPage'];
            $sql = 'SELECT u.*
                    FROM ' . USERS_TABLE . ' u
                    WHERE u.user_type <> 2
                    ORDER BY u.username
                    LIMIT 20';

            $result = $db->sql_query($sql);
            $members = array();
            while($member = $db->sql_fetchrow($result))
            {
                $members[] = $this->initOMbqEtUser($member, array('case'=>'user_row'));
            }
            $db->sql_freeresult($result);
            $oMbqDataPage->datas = $members;
            $oMbqDataPage->totalNum = sizeof($members);
            return $oMbqDataPage;
        }
        elseif ($mbqOpt['case'] == 'voted') {
            $optionId = $var->optionId;
            $topicId = $var->topicId;
            $oMbqDataPage = $mbqOpt['oMbqDataPage'];
            $sql = 'SELECT u.*
                    FROM ' . USERS_TABLE . ' u
                    LEFT JOIN ' . POLL_VOTES_TABLE . ' v ON u.user_id = v.vote_user_id
                    WHERE v.poll_option_id = ' . $optionId . '
                        AND v.topic_id = ' . $topicId;
            $result = $db->sql_query_limit($sql, $oMbqDataPage->numPerPage, $oMbqDataPage->startNum);
            $members = array();
            while ($member = $db->sql_fetchrow($result))
            {
                $members[] = $this->initOMbqEtUser($member, array('case' => 'user_row'));
            }
            $db->sql_freeresult($result);
            $oMbqDataPage->datas = $members;
            $oMbqDataPage->totalNum = sizeof($members);
            return $oMbqDataPage;
        }
        elseif ($mbqOpt['case'] == 'searchByName') {
            $keywords = $db->sql_escape($var);
            $oMbqDataPage = $mbqOpt['oMbqDataPage'];
            $sql = 'SELECT u.user_id
                    FROM ' . USERS_TABLE . ' u
                    WHERE u.user_type <> 2 and u.username COLLATE UTF8_GENERAL_CI LIKE \'%' . $keywords . '%\'
                    ORDER BY u.username
                    LIMIT 20';

            $result = $db->sql_query($sql);
            $members = array();
            while($member = $db->sql_fetchrow($result))
            {
                $members[] = $this->initOMbqEtUser($member['user_id'], array('case'=>'byUserId'));
            }
            $db->sql_freeresult($result);
            $oMbqDataPage->datas = $members;
            $oMbqDataPage->totalNum = sizeof($members);
            return $oMbqDataPage;
        }
        elseif ($mbqOpt['case'] == 'ignored') {
            // What colour is the zebra
            $oMbqDataPage = $mbqOpt['oMbqDataPage'];
            $sql = 'SELECT zebra_id
			        FROM ' . ZEBRA_TABLE . "
			        WHERE user_id = {$user->data['user_id']} and foe=1";

            $result = $db->sql_query($sql);
            $members = array();
            while($row = $db->sql_fetchrow($result))
            {
                $members[] = $this->initOMbqEtUser($row['zebra_id'], array('case'=>'byUserId'));
            }
            $db->sql_freeresult($result);
            $oMbqDataPage->datas = $members;
            $oMbqDataPage->totalNum = sizeof($members);
            return $oMbqDataPage;
        }
        elseif ($mbqOpt['case'] == 'getMemberList')
        {
            return self::_getObjsMbqEtUserGetMemberList($var, $mbqOpt);
        }
        elseif ($mbqOpt['case'] == 'getInactiveUsers')
        {
            return self::_getObjsMbqEtUserGetInactiveUsers($var, $mbqOpt);
        }

        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }

    /**
     * @param $var
     * @param $mbqOpt
     * @return MbqDataPage
     */
    protected function _getObjsMbqEtUserGetInactiveUsers($var, $mbqOpt)
    {
        global $phpbb_root_path, $phpEx;

        /** @var MbqDataPage $oMbqDataPage */
        $oMbqDataPage = $mbqOpt['oMbqDataPage'];

        if (!function_exists('view_inactive_users')) {
            require_once($phpbb_root_path . '/includes/functions_admin.' . $phpEx);
        }
        $oMbqDataPage->datas = array();
        $sql_where = '';
        $sql_sort = 'user_inactive_time DESC';
        $per_page = $oMbqDataPage->numPerPage;
        $start = $oMbqDataPage->startNum;
        $inactiveUsers =  array();
        $inactive_count = 0;
        $start = view_inactive_users($inactiveUsers, $inactive_count, $per_page, $start, $sql_where, $sql_sort, true);

        $oMbqDataPage->totalNum = $inactive_count;
        if ($inactiveUsers) {
            foreach ($inactiveUsers as $member) {
                $oMbqDataPage->datas[] = $this->initOMbqEtUser($member, array('case' => 'user_row'));
            }
        }

        return $oMbqDataPage;
    }

    /**
     * @param $var
     * @param $mbqOpt
     * @return MbqDataPage
     */
    protected function _getObjsMbqEtUserGetMemberList($var, $mbqOpt)
    {
        /** @var \phpbb\db\driver\mysql $db */
        global $config, $db, $auth;

        /** @var MbqDataPage $oMbqDataPage */
        $oMbqDataPage = $mbqOpt['oMbqDataPage'];
        $isOnlineValidTime = time() - ($config['load_online_time'] * 60);

        $action = 'all'; // default
        if (isset($var->action)) {
            $action = strtolower($var->action);
        }
        $user_types = array(USER_NORMAL, USER_FOUNDER);
        $adminPermission = false;
        if ($auth->acl_gets('a_')) {
            $adminPermission = true;
            $user_types[] = USER_INACTIVE;
        }
        $sqlWhere = '';
        switch ($action) {
            case 'all':
                $sqlWhere = '';
                break;
            case 'search':
                if (isset($var->searchUserName)) {
                    $searchUserName = $db->sql_escape(utf8_clean_string($var->searchUserName));
                    $sqlWhere.= ($searchUserName) ? ' AND u.username_clean ' . 'LIKE \'%' . $searchUserName . '%\' '  : '';
                    // $db->sql_like_expression(str_replace('*', $db->get_any_char(), utf8_clean_string($searchUserName)))
                }
                break;
            default:
                // unSupport action type
                return $oMbqDataPage;
                break;
        }
        // Count the users
        $sql = 'SELECT COUNT(u.user_id) AS total_users
                FROM ' . USERS_TABLE . " u
                WHERE " . $db->sql_in_set('u.user_type', $user_types) . " $sqlWhere ";
        $result = $db->sql_query($sql);
        $total_users = (int) $db->sql_fetchfield('total_users');
        $db->sql_freeresult($result);

        $oMbqDataPage->datas = array();
        if ($total_users) {
            // Select
            $sql = 'SELECT IFNULL(s.session_time,u.user_lastvisit) as last_active,!ISNULL(s.session_time) as is_online, u.*, s.session_page, s.session_forum_id
                FROM  ' . USERS_TABLE . ' u
                LEFT JOIN  (SELECT session_user_id, MAX(session_time) AS session_time,session_page,session_forum_id
                    FROM ' . SESSIONS_TABLE . '
                    WHERE session_time >= ' . $isOnlineValidTime . '
                        GROUP BY session_user_id, session_page, session_forum_id
                    ) s on s.session_user_id = user_id
                WHERE ' . $db->sql_in_set('u.user_type', $user_types) . ' ' . $sqlWhere . '
                ORDER BY last_active DESC, u.user_lastvisit DESC';

            $result = $db->sql_query_limit($sql, $oMbqDataPage->numPerPage, $oMbqDataPage->startNum);

            while ($member = $db->sql_fetchrow($result)) {
                if ($adminPermission) $member['check_ban'] = true;
                $oMbqDataPage->datas[] = $this->initOMbqEtUser($member, array('case' => 'user_row'));
            }
            $db->sql_freeresult($result);
        }

        $oMbqDataPage->totalNum = $total_users;

        return $oMbqDataPage;
    }

    public function initOMbqEtUser($var, $mbqOpt) {
        global $auth, $user, $config, $db, $phpbb_root_path, $phpEx;
        $onlinetime = time() - ($config['load_online_time'] * 60);
        if($mbqOpt['case'] == 'user_row')
        {
            if($var == false || !isset($var['user_id']) || $var['user_id'] == '')
            {
                return null;
            }
            if(MbqMain::$Cache->Exists('MbqEtUser',$var['user_id']))
            {
                return MbqMain::$Cache->Get('MbqEtUser',$var['user_id']);
            }
            global $auth, $user, $config, $db, $phpbb_root_path, $phpEx, $cache;
            $isCurrentLoggedUser = false;
            if($user->data['user_id'] == $var['user_id'])
            {
                $isCurrentLoggedUser = true;
            }
            $oMbqEtUser = MbqMain::$oClk->newObj('MbqEtUser');
            $oMbqEtUser->userId->setOriValue($var['user_id']);
            $oMbqEtUser->loginName->setOriValue($var['username']);
            $oMbqEtUser->userName->setOriValue($var['username']);
            if(MbqMain::$Cache->Exists('MbqEtUser_UserGroups',$var['user_id']))
            {
                $usergroups = MbqMain::$Cache->Get('MbqEtUser_UserGroups',$var['user_id']);
            }
            else
            {
                $sql = 'SELECT ug.*
		        FROM ' . USER_GROUP_TABLE . " ug
		        WHERE ug.user_id = {$var['user_id']}";
                $result = $db->sql_query($sql);
                $usergroups = array();
                while($row = $db->sql_fetchrow($result))
                {
                    $usergroups[] = $row['group_id'];
                }
                MbqMain::$Cache->Set('MbqEtUser_UserGroups',$var['user_id'], $usergroups);
            }
            $oMbqEtUser->userGroupIds->setOriValue($usergroups);
            $oMbqEtUser->userEmail->setOriValue(strtolower($var['user_email']));
            if(isset($var['user_avatar']))
            {
                $oMbqEtUser->iconUrl->setOriValue(get_user_avatar_url($var['user_avatar'],$var['user_avatar_type']));
            }

            $oMbqEtUser->postCount->setOriValue($var['user_posts']);
            $oMbqEtUser->userType->setOriValue(TT_check_return_user_type($var, $isCurrentLoggedUser || MbqMain::$cmd == 'get_user_info' || (isset($var['check_ban']) && $var['check_ban'])));
            if (isset($var['is_online'])) {
                $isOnline = $var['is_online'];
            }else{
                $isOnline = !$config['load_onlinetrack'] || (isset($var['session_viewonline']) && !$var['session_viewonline']) ? false : time() - ($config['load_online_time'] * 60) < $var['session_start'];
            }
            $oMbqEtUser->isOnline->setOriValue($isOnline);

            $oMbqEtUser->canBan->setOriValue($auth->acl_get('m_ban') && $var['user_id'] != $user->data['user_id'] ? true : false);
            $oMbqEtUser->isBan->setOriValue($oMbqEtUser->userType->oriValue == 'banned');

            if($isCurrentLoggedUser)
            {
                $can_readpm = $config['allow_privmsg'] && $auth->acl_get('u_readpm') && ($user->data['user_allow_pm'] || $auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_'));
                $can_sendpm = $config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($user->data['user_allow_pm'] || $auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_'));
                $can_upload = ($config['allow_avatar_upload'] && file_exists($phpbb_root_path . $config['avatar_path']) && (function_exists('phpbb_is_writable') ? phpbb_is_writable($phpbb_root_path . $config['avatar_path']) : 1) && $auth->acl_get('u_chgavatar') && (@ini_get('file_uploads') || strtolower(@ini_get('file_uploads')) == 'on')) ? true : false;
                $can_search = $auth->acl_get('u_search') && $auth->acl_getf_global('f_search') && $config['load_search'];
                $can_whosonline = $auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel');
                $max_filesize   = ($config['max_filesize'] === '0' || $config['max_filesize'] > 10485760) ? 10485760 : $config['max_filesize'];

                $oMbqEtUser->canPm->setOriValue($can_readpm);
                $oMbqEtUser->canSendPm->setOriValue($can_sendpm);
                $oMbqEtUser->canModerate->setOriValue($auth->acl_get('m_'));
                $oMbqEtUser->canSearch->setOriValue($can_search);
                $oMbqEtUser->canWhosonline->setOriValue($can_whosonline);
                $oMbqEtUser->canProfile->setOriValue($can_whosonline);
                $oMbqEtUser->canUploadAvatar->setOriValue($can_upload);
                $oMbqEtUser->canActive->setOriValue($auth->acl_get('a_'));

                $oMbqEtUser->maxAvatarSize->setOriValue($config['avatar_filesize']);
                $oMbqEtUser->maxAvatarWidth->setOriValue($config['avatar_max_width']);
                $oMbqEtUser->maxAvatarHeight->setOriValue($config['avatar_max_height']);
                $oMbqEtUser->maxAttachment->setOriValue($config['max_attachments']);
                $attach_extensions = array();
                $cachedExtensions = $cache->obtain_attach_extensions(true);
                if(is_array($cachedExtensions))
                {
                    foreach($cachedExtensions['_allowed_post'] as $key=>$value)
                    {
                        $attach_extensions[] = $key;
                    }
                }
                if (method_exists('\phpbb\files\upload', 'get_refuse_extensions')) {
                    $disableExtensions = \phpbb\files\upload::get_refuse_extensions();
                    $oMbqEtUser->disableExtensions->setOriValue(implode(',',$disableExtensions));
                }
                if (method_exists('\phpbb\files\upload', 'get_allow_extensions')) {
                    $allow_extensions = \phpbb\files\upload::get_allow_extensions();
                    if ($allow_extensions && is_array($allow_extensions)) {
                        $attach_extensions = $allow_extensions;
                    }
                }
                $oMbqEtUser->allowedExtensions->setOriValue(implode(',',$attach_extensions));
                $oMbqEtUser->maxPngSize->setOriValue($max_filesize);
                $oMbqEtUser->maxJpgSize->setOriValue($max_filesize);

                // What colour is the zebra
                $sql = 'SELECT zebra_id
			        FROM ' . ZEBRA_TABLE . "
			        WHERE user_id = {$user->data['user_id']} and foe=1";

                $result = $db->sql_query($sql);
                $foe = array();
                while($row = $db->sql_fetchrow($result))
                {
                    $foe[] = $row['zebra_id'];
                }
                $db->sql_freeresult($result);

                $oMbqEtUser->ignoredUids->setOriValue(implode(',',$foe));
                $oMbqEtUser->isIgnored->setOriValue(false);
            }
            else
            {
                $oMbqEtUser->isIgnored->setOriValue(MbqCm::checkIfUserIsIgnored($var['user_id']));
            }
            if($isCurrentLoggedUser || MbqMain::$cmd == 'get_user_info')
            {
                $profileFields = $this->getCustomRegisterFields(false);
                if(!empty($profileFields))
                {
                    $sql = 'SELECT f.* FROM ' . PROFILE_FIELDS_DATA_TABLE . ' f
	                    WHERE f.user_id = ' . $oMbqEtUser->userId->oriValue;
                    $sqlresult = $db->sql_query($sql);
                    $profileFieldData =  $db->sql_fetchrow($sqlresult);
                    $db->sql_freeresult($sqlresult);
                    $custom_fields_list = array();
                    if($profileFieldData)
                    {
                        foreach($profileFields as $profileField)
                        {
                            if($profileField['mbqBind']['field_active'] == 1 && $profileField['mbqBind']['field_no_view'] == 0 && $profileField['mbqBind']['field_hide'] == 0)
                            {
                                if(isset($profileFieldData[$profileField['key']]))
                                {
                                    if ($profileFieldData[$profileField['key']] == null && $profileField['default'] == '') {
                                        continue; // no show if value is empty
                                    }
                                    TT_addNameValue($profileField['name'], $profileFieldData[$profileField['key']], $custom_fields_list, $profileField);
                                }
                            }
                        }
                    }
                    $oMbqEtUser->customFieldsList->setOriValue($custom_fields_list);
                }
                $extraFields = $this->getExtraCustomFields($var);
                if (!empty($custom_fields_list))
                {
                    $extraFields = array_merge($extraFields, $custom_fields_list);
                }
                $oMbqEtUser->customFieldsList->setOriValue($extraFields);
            }
            $oMbqEtUser->postCountdown->setOriValue($config['flood_interval']);
            $oMbqEtUser->currentAction->setOriValue($this->getUserLocation($var));
            $oMbqEtUser->regTime->setOriValue($var['user_regdate']);
            $oMbqEtUser->lastActivityTime->setOriValue($var['user_lastvisit']);
            $oMbqEtUser->mbqBind['userRecord'] = $var;
            MbqMain::$Cache->Set('MbqEtUser',$var['user_id'],$oMbqEtUser);
            return $oMbqEtUser;
        }
        else if($mbqOpt['case'] == 'byLoginName')
        {
            $username = $var;
            $sql = "SELECT u.user_id FROM " . USERS_TABLE . " u
                WHERE u.username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'
                ";
            $result = $db->sql_query($sql);
            $member = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);
            if($member)
            {
                return $this->initOMbqEtUser($member['user_id'], array('case'=>'byUserId'));
            }
            return null;
        }
        elseif($mbqOpt['case'] == 'byEmail')
        {
            $email = strtolower($var);
            $sql = "SELECT u.user_id
                FROM " . USERS_TABLE . " u
                WHERE LOWER(u.user_email) = '" . $db->sql_escape($email) . "'";
            $result = $db->sql_query($sql);
            $member = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);
            if($member)
            {
                return $this->initOMbqEtUser($member['user_id'], array('case'=>'byUserId'));
            }
            return null;
        }
        else if ($mbqOpt['case'] == 'byUserId') {
            $user_id = intval($var);
            if(MbqMain::$Cache->Exists('MbqEtUser',$user_id))
            {
                return MbqMain::$Cache->Get('MbqEtUser',$user_id);
            }

            $sql = "SELECT * FROM " . USERS_TABLE . " AS u WHERE u.user_id = '$user_id'";
            $result = $db->sql_query($sql);
            $member = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);

            if ($config['load_onlinetrack'])
            {
                $sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
		FROM ' . SESSIONS_TABLE . '
		WHERE ' . $db->sql_in_set('session_user_id', $user_id) . '
		GROUP BY session_user_id';
                $result = $db->sql_query($sql);

                $update_time = $config['load_online_time'] * 60;
                while ($row = $db->sql_fetchrow($result))
                {
                    MbqMain::$Cache->Set('MbqEtUserOnline', $row['session_user_id'], (time() - $update_time < $row['online_time'] && (($row['viewonline']) || $auth->acl_get('u_viewonline'))) ? true : false);
                }
                $db->sql_freeresult($result);
            }
            return $this->initOMbqEtUser($member, array('case'=>'user_row'));
        }
    }

    public function getCustomRegisterFields($onlyRequired = true)
    {
        global $db, $user, $phpbb_container,$cache;
        $lang_id = $user->get_iso_lang_id();
        if($onlyRequired)
        {
            $sql = 'SELECT l.*, f.*
	FROM ' . PROFILE_LANG_TABLE . ' l, ' . PROFILE_FIELDS_TABLE . " f
	WHERE f.field_active = 1
		AND f.field_show_on_reg = 1
		AND l.lang_id = $lang_id
		AND l.field_id = f.field_id
		AND f.field_required = 1
	ORDER BY f.field_order";
        }
        else
        {
            $sql = 'SELECT l.*, f.*
	FROM ' . PROFILE_LANG_TABLE . ' l, ' . PROFILE_FIELDS_TABLE . " f
	WHERE f.field_active = 1
		AND l.lang_id = $lang_id
		AND l.field_id = f.field_id
		ORDER BY f.field_order";
        }
        $result = $db->sql_query($sql);
        $cp = $phpbb_container->get('profilefields.manager');
        $required_custom_fields = array();
        while ($row = $db->sql_fetchrow($result))
        {
            $type = $row['field_type'];
            $field_id = (int) $row['field_id'];
            $custom_field_data = array(
                'name'          => basic_clean($row['lang_name']),
                'description'   => basic_clean($row['lang_explain']),
                'key'           => 'pf_' . $row['field_ident'],
                'type'          => 'input',
                'default'       => $row['lang_default_value'] == '' ? $row['field_default_value'] : $row['lang_default_value'],
            );
            $custom_field_data['mbqBind'] = $row;
	    	if(getPHPBBVersion() == '3.0')
	{
	  if($type === "5")//'profilefields.type.dropdown')
            {
                $custom_field_data['type'] ='drop';
                $custom_field_data['default'] = $custom_field_data['default'];
            }
            if($type === "4")//'profilefields.type.bool')
            {
                if($row['field_length'] == 1)
                    $custom_field_data['type'] = 'radio';
                else
                    $custom_field_data['type'] = 'cbox';
            }
            if($type  === "3")//'profilefields.type.text')
            {
                $custom_field_data ['type'] = 'textarea';
            }
            if($type === "6")// 'profilefields.type.date')
            {
                $custom_field_data['key'] ='date_pf_' . $row['field_ident'];
                $default_value = explode('-', $row['field_default_value']);
                $default_value[0] = (int) $default_value[0];
                if(empty($default_value[0]))
                {
                    $custom_field_data['default'] =  date("Y-m-d",time());
                }
                $custom_field_data['format'] = "nnnn-nn-nn";
            }
            $option_str = '';
            $sql = 'SELECT f.* FROM ' . PROFILE_FIELDS_LANG_TABLE . ' f
	                    WHERE f.lang_id = ' . $lang_id . '
		                AND f.field_id = ' . $field_id;
            $optionResult = $db->sql_query($sql);
            while ($option = $db->sql_fetchrow($optionResult))
            {
                $option_str .= ($option_str == ''? '':'|') . $option['option_id']."=".$option['lang_value'];
            }
	}
	else
	{
	       if($type === 'profilefields.type.dropdown')
            {
                $custom_field_data['type'] ='drop';
                $custom_field_data['default'] = $custom_field_data['default'];
            }
            if($type === 'profilefields.type.bool')
            {
                if($row['field_length'] == 1)
                    $custom_field_data['type'] = 'radio';
                else
                    $custom_field_data['type'] = 'cbox';
            }
            if($type  === 'profilefields.type.text')
            {
                $custom_field_data ['type'] = 'textarea';
            }
            if($type === 'profilefields.type.date')
            {
                $custom_field_data['key'] ='date_pf_' . $row['field_ident'];
                $default_value = explode('-', $row['field_default_value']);
                $default_value[0] = (int) $default_value[0];
                if(empty($default_value[0]))
                {
                    $custom_field_data['default'] =  date("Y-m-d",time());
                }
                $custom_field_data['format'] = "nnnn-nn-nn";
            }
            $option_str = '';
            $sql = 'SELECT f.* FROM ' . PROFILE_FIELDS_LANG_TABLE . ' f
	                    WHERE f.lang_id = ' . $lang_id . '
		                AND f.field_id = ' . $field_id;
            $optionResult = $db->sql_query($sql);
            while ($option = $db->sql_fetchrow($optionResult))
            {
                $option_str .= ($option_str == ''? '':'|') . ($option['option_id'] + 1)."=".$option['lang_value'];
            }
    	}

            if(!empty($option_str)) $custom_field_data['options'] = $option_str;
            $required_custom_fields[] = $custom_field_data;
        }

        $db->sql_freeresult($result);
        return $required_custom_fields;
    }

    public function preloadUserGroups($userRows)
    {
        global $db;
        $userIds = array();
        foreach($userRows as $userRow)
        {
            if(!in_array($userRow['user_id'], $userIds))
            {
                $userIds[] = $userRow['user_id'];
            }
        }
        $userIds = implode(',', $userIds);
        $sql = 'SELECT ug.*
		        FROM ' . USER_GROUP_TABLE . " ug
		        WHERE ug.user_id in ($userIds)";
        $result = $db->sql_query($sql);
        $usergroups = array();
        while($row = $db->sql_fetchrow($result))
        {
            $usergroups[$row['user_id']][] = $row['group_id'];
        }
        foreach($usergroups as $key=>$value)
        {
            MbqMain::$Cache->Set('MbqEtUser_UserGroups',$key, $value);
        }
    }
    /**
     * forget_password this function should send the email password change to this user
     *
     * @return Array
     */
    public function forgetPassword($oMbqEtUser) {
        global $phpbb_root_path, $phpEx, $request, $user, $config;

        requireExtLibrary('fake_template');
        $template = new fake_template();

        require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
        require_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);
        overwriteRequestParam('i', $oMbqEtUser->userId->oriValue);
        overwriteRequestParam('mode', 'sendpassword');
        overwriteRequestParam('username', $oMbqEtUser->loginName->oriValue);
        overwriteRequestParam('email', $oMbqEtUser->userEmail->oriValue);
        overwriteRequestParam('submit', true,\phpbb\request\request_interface::POST);

        // fix bug
        $form_name = 'ucp_remind';
        $creation_time	= abs(time() - 50);
        $token_sid = ($user->data['user_id'] == ANONYMOUS && !empty($config['form_token_sid_guests'])) ? $user->session_id : '';
        $key = sha1($creation_time . $user->data['user_form_salt'] . $form_name . $token_sid);
        overwriteRequestParam('form_token', $key, \phpbb\request\request_interface::POST);
        overwriteRequestParam('form_token', $key);
        overwriteRequestParam('creation_time', $creation_time, \phpbb\request\request_interface::POST);
        overwriteRequestParam('creation_time', $creation_time);

        $user->setup('ucp');
        $module = new p_master();
        $module->load('ucp', 'remind','sendpassword');

        $error = $template->getTemplateVar('ERROR');

        if(isset($error) && !empty($error))
        {
            $returnError = null;
            $errors = explode('<br />', $error);
            if(is_array($errors))
            {
                $returnError = strip_tags($errors[0]);
            }else {
                $returnError = strip_tags($error);
            }

            if (is_string($returnError) && strpos($returnError, str_replace('%s','',$user->lang['RETURN_INDEX'])) !== false) {
                return true;
            }
            if (is_string($returnError) && strpos($returnError, $user->lang['PASSWORD_UPDATED']) !== false) {
                return true;
            }
            if (!$returnError) {
                return true;
            }

            return $returnError;
        }

        return true;
    }
    /**
     * the response should be bool to indicate if the username meet the forum requirement
     *
     * @param string $username
     */
    public function validateUsername($username){
        global $db;
         $sql = "SELECT u.*
                FROM " . USERS_TABLE . " u
                WHERE u.username = '" . $db->sql_escape($username) . "'";
        $result = $db->sql_query($sql);
        $count = $db->sql_affectedrows();
        if($count > 0)
        {
            return false;
        }
        return true;
    }

    /**
     * the response should be bool to indicate if the password meet the forum requirement
     *
     * @param string $password
     */
    public function validatePassword($password){
        if(trim($password) == '')
        {
            return false;
        }
        return true;
    }

    function getUserLocation($member)
    {
        global $user, $auth;
        $location = '';
        // get user current activity
        if(isset($member['session_page']))
        {
            preg_match('#^([a-z0-9/_-]+)#i', $member['session_page'], $on_page);
            if (!sizeof($on_page))
            {
                $on_page[1] = '';
            }


            switch ($on_page[1])
            {
                case 'index':
                    $location = $user->lang['INDEX'];
                    break;

                case 'adm/index':
                    $location = $user->lang['ACP'];
                    break;

                case 'posting':
                case 'viewforum':
                case 'viewtopic':
                    $forum_id = $member['session_forum_id'];

                    if ($forum_id && $auth->acl_get('f_list', $forum_id))
                    {
                        $location = '';
                        $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
                        $oMbqEtForum = $oMbqRdEtForum->initOMbqEtForum($forum_id, array('case' => 'byForumId'));
                        if ($oMbqEtForum->mbqBind['forum_type'] == FORUM_LINK)
                        {
                            $location = sprintf($user->lang['READING_LINK'], $oMbqEtForum->mbqBind['forum_name']);
                            break;
                        }

                        switch ($on_page[1])
                        {
                            case 'posting':
                                preg_match('#mode=([a-z]+)#', $member['session_page'], $on_page);
                                $posting_mode = (!empty($on_page[1])) ? $on_page[1] : '';

                                switch ($posting_mode)
                                {
                                    case 'reply':
                                    case 'quote':
                                        $location = sprintf($user->lang['REPLYING_MESSAGE'], $oMbqEtForum->mbqBind['forum_name']);
                                        break;

                                    default:
                                        $location = sprintf($user->lang['POSTING_MESSAGE'], $oMbqEtForum->mbqBind['forum_name']);
                                        break;
                                }
                                break;

                            case 'viewtopic':
                                $location = sprintf($user->lang['READING_TOPIC'], $oMbqEtForum->mbqBind['forum_name']);
                                break;

                            case 'viewforum':
                                $location = sprintf($user->lang['READING_FORUM'], $oMbqEtForum->mbqBind['forum_name']);
                                break;
                        }
                    }
                    else
                    {
                        $location = $user->lang['INDEX'];
                    }
                    break;

                case 'search':
                    $location = $user->lang['SEARCHING_FORUMS'];
                    break;

                case 'faq':
                    $location = $user->lang['VIEWING_FAQ'];
                    break;

                case 'viewonline':
                    $location = $user->lang['VIEWING_ONLINE'];
                    break;

                case 'memberlist':
                    $location = (strpos($member['session_page'], 'mode=viewprofile') !== false) ? $user->lang['VIEWING_MEMBER_PROFILE'] : $user->lang['VIEWING_MEMBERS'];
                    break;

                case 'mcp':
                    $location = $user->lang['VIEWING_MCP'];
                    break;

                case 'ucp':
                    $location = $user->lang['VIEWING_UCP'];

                    // Grab some common modules
                    $url_params = array(
                        'mode=register'        => 'VIEWING_REGISTER',
                        'i=pm&mode=compose'    => 'POSTING_PRIVATE_MESSAGE',
                        'i=pm&'                => 'VIEWING_PRIVATE_MESSAGES',
                        'i=profile&'        => 'CHANGING_PROFILE',
                        'i=prefs&'            => 'CHANGING_PREFERENCES',
                    );

                    foreach ($url_params as $param => $lang)
                    {
                        if (strpos($member['session_page'], $param) !== false)
                        {
                            $location = $user->lang[$lang];
                            break;
                        }
                    }

                    break;

                case 'download/file':
                    $location = $user->lang['DOWNLOADING_FILE'];
                    break;

                case 'report':
                    $location = $user->lang['REPORTING_POST'];
                    break;

                case 'mobiquo/mobiquo':
                case 'mobiquo':
                    $location = 'On Tapatalk';
                    break;

                default:
                    $location = $user->lang['INDEX'];
                    break;
            }
        }
        return $location;
    }

    /**
     * add 4 extra fields to profile custom fields
     * user_group,
     * birthday/age,
     * warnings,
     * like_received,
     * @param $var
     * @return array
     */
    private function getExtraCustomFields($var){
        /** @var $auth phpbb\auth\auth */
        global $user, $auth;

        $birthday = $this->processUserBirthday($var['user_birthday']);
        $extra = array();
        if ($birthday)
        {
            $extra[] = array(
                'name'  => 'Birthday',
                'value' => $birthday,
            );
        }

        if ($auth->acl_getf_global('m_') || $auth->acl_get('m_warn'))
        {
            $extra[] =  array(
                'name'  => $user->lang['WARNINGS'],
                'value' => $var['user_warnings'],
            );
        }

        return $extra;
    }

    /**
     * calculate user's birthday, if both of his month and day filled up
     * @param $user_birthday
     * @return string
     */
    private function processUserBirthday($user_birthday)
    {
        $birthday = '';

        if ($user_birthday)
        {
	        $user_birthday = preg_replace('/\s/s', '', $user_birthday);
            $user_birthday = explode('-', $user_birthday);

            if (isset($user_birthday[0]) && isset($user_birthday[1]) && $user_birthday[0] && $user_birthday[1])
            {
                $dateObj       = \DateTime::createFromFormat('!m', $user_birthday[1]);
                $birthdayMonth = $dateObj->format('M');
                $birthdayDay   = $user_birthday[0];
                if (isset($user_birthday[2]) && $user_birthday[2])
                {
                    $birthday = $birthdayDay . ' ' . $birthdayMonth . ' ' . $user_birthday[2];
                }
                else
                {
                    $birthday = $birthdayDay . ' ' . $birthdayMonth;
                }
            }
        }

        return $birthday;
    }
}
