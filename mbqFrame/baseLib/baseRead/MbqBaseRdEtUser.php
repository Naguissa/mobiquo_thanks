<?php

defined('MBQ_IN_IT') or exit;

/**
 * user read class
 */
Abstract Class MbqBaseRdEtUser extends MbqBaseRd {

    public function __construct() {
    }

    /**
     * return user api data
     *
     * @param  Object  $oMbqEtUser
     * @return  Array
     */
    public function returnApiDataUser($oMbqEtUser) {
        if (MbqMain::isJsonProtocol()) return $this->returnJsonApiDataUser($oMbqEtUser);
        $data = array();
        if ($oMbqEtUser->userId->hasSetOriValue()) {
            $data['user_id'] = (string) $oMbqEtUser->userId->oriValue;
        }
        $data['username'] = (string) $oMbqEtUser->getDisplayName();
        $data['user_name'] = (string) $oMbqEtUser->getDisplayName();
        $data['login_name'] = (string) $oMbqEtUser->loginName->oriValue;
        if ($oMbqEtUser->userGroupIds->hasSetOriValue()) {
            $data['usergroup_id'] = (array) MbqMain::$oMbqCm->changeArrValueToString($oMbqEtUser->userGroupIds->oriValue);
        }
        if ($oMbqEtUser->iconUrl->hasSetOriValue()) {
            $data['icon_url'] = (string) $oMbqEtUser->iconUrl->oriValue;
        }

        //Added to ensure we will never send back user email due GDPR, only on signing and login
        $methodNeedEmail = array('login','sign_in');
        if(in_array(MbqMain::$cmd,$methodNeedEmail))
        {
            if ($oMbqEtUser->userEmail->hasSetOriValue()) {
                $data['email'] = (string)sha1($oMbqEtUser->userEmail->oriValue);
            }
        }
        if ($oMbqEtUser->postCount->hasSetOriValue()) {
            $data['post_count'] = (int) $oMbqEtUser->postCount->oriValue;
        }
        if ($oMbqEtUser->canPm->hasSetOriValue()) {
            $data['can_pm'] = (boolean) $oMbqEtUser->canPm->oriValue;
        } else {
            $data['can_pm'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canPm.default');
        }
        if ($oMbqEtUser->canSendPm->hasSetOriValue()) {
            $data['can_send_pm'] = (boolean) $oMbqEtUser->canSendPm->oriValue;
        } else {
            $data['can_send_pm'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canSendPm.default');
        }
        if ($oMbqEtUser->canModerate->hasSetOriValue()) {
            $data['can_moderate'] = (boolean) $oMbqEtUser->canModerate->oriValue;
        } else {
            $data['can_moderate'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canModerate.default');
        }
        if ($oMbqEtUser->canSearch->hasSetOriValue()) {
            $data['can_search'] = (boolean) $oMbqEtUser->canSearch->oriValue;
        } else {
            $data['can_search'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canSearch.default');
        }
        if ($oMbqEtUser->canWhosonline->hasSetOriValue()) {
            $data['can_whosonline'] = (boolean) $oMbqEtUser->canWhosonline->oriValue;
        } else {
            $data['can_whosonline'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canWhosonline.default');
        }
        if ($oMbqEtUser->canProfile->hasSetOriValue()) {
            $data['can_profile'] = (boolean) $oMbqEtUser->canProfile->oriValue;
        } else {
            $data['can_profile'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canProfile.default');
        }
        if ($oMbqEtUser->canUploadAvatar->hasSetOriValue()) {
            $data['can_upload_avatar'] = (boolean) $oMbqEtUser->canUploadAvatar->oriValue;
        } else {
            $data['can_upload_avatar'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canUploadAvatar.default');
        }
        if ($oMbqEtUser->maxAvatarSize->hasSetOriValue()) {
            $data['max_avatar_size'] = (int) $oMbqEtUser->maxAvatarSize->oriValue;
        }
        if ($oMbqEtUser->maxAvatarWidth->hasSetOriValue()) {
            $data['max_avatar_width'] = (int) $oMbqEtUser->maxAvatarWidth->oriValue;
        }
        if ($oMbqEtUser->maxAvatarHeight->hasSetOriValue()) {
            $data['max_avatar_height'] = (int) $oMbqEtUser->maxAvatarHeight->oriValue;
        }
        if ($oMbqEtUser->maxAttachment->hasSetOriValue()) {
            $data['max_attachment'] = (int) $oMbqEtUser->maxAttachment->oriValue;
        }
        if ($oMbqEtUser->maxAttachmentSize->hasSetOriValue()) {
            $data['max_attachment_size'] = (int) $oMbqEtUser->maxAttachmentSize->oriValue;
        }
        if ($oMbqEtUser->allowedExtensions->hasSetOriValue()) {
            $data['allowed_extensions'] = $oMbqEtUser->allowedExtensions->oriValue;
        }
        if ($oMbqEtUser->maxPngSize->hasSetOriValue()) {
            $data['max_png_size'] = (int) $oMbqEtUser->maxPngSize->oriValue;
        }
        if ($oMbqEtUser->maxJpgSize->hasSetOriValue()) {
            $data['max_jpg_size'] = (int) $oMbqEtUser->maxJpgSize->oriValue;
        }
        if ($oMbqEtUser->ignoredUids->hasSetOriValue()) {
            $data['ignored_uids'] = (string) $oMbqEtUser->ignoredUids->oriValue;
        }
        if ($oMbqEtUser->displayText->hasSetOriValue()) {
            $data['display_text'] = (string) $oMbqEtUser->displayText->oriValue;
        }
        if ($oMbqEtUser->regTime->hasSetOriValue()) {
            $data['reg_time'] = (string) MbqMain::$oMbqCm->datetimeIso8601Encode($oMbqEtUser->regTime->oriValue);
            $data['timestamp_reg'] = (string)MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtUser->regTime->oriValue);
        }
        if ($oMbqEtUser->lastActivityTime->hasSetOriValue()) {
            $data['last_activity_time'] = (string) MbqMain::$oMbqCm->datetimeIso8601Encode($oMbqEtUser->lastActivityTime->oriValue);
            $data['timestamp'] = (string)MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtUser->lastActivityTime->oriValue);
        }
        if ($oMbqEtUser->isOnline->hasSetOriValue()) {
            $data['is_online'] = (boolean) $oMbqEtUser->isOnline->oriValue;
        }
        if ($oMbqEtUser->acceptPm->hasSetOriValue()) {
            $data['accept_pm'] = (boolean) $oMbqEtUser->acceptPm->oriValue;
        } else {
            $data['accept_pm'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.acceptPm.default');
        }
        if ($oMbqEtUser->iFollowU->hasSetOriValue()) {
            $data['i_follow_u'] = (boolean) $oMbqEtUser->iFollowU->oriValue;
        } else {
            $data['i_follow_u'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.iFollowU.default');
        }
        if ($oMbqEtUser->uFollowMe->hasSetOriValue()) {
            $data['u_follow_me'] = (boolean) $oMbqEtUser->uFollowMe->oriValue;
        } else {
            $data['u_follow_me'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.uFollowMe.default');
        }
        if ($oMbqEtUser->acceptFollow->hasSetOriValue()) {
            $data['accept_follow'] = (boolean) $oMbqEtUser->acceptFollow->oriValue;
        } else {
            $data['accept_follow'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.acceptFollow.default');
        }
        if ($oMbqEtUser->followingCount->hasSetOriValue()) {
            $data['following_count'] = (int) $oMbqEtUser->followingCount->oriValue;
        }
        if ($oMbqEtUser->follower->hasSetOriValue()) {
            $data['follower'] = (int) $oMbqEtUser->follower->oriValue;
        }
        if ($oMbqEtUser->currentAction->hasSetOriValue()) {
            $data['current_activity'] = (string) $oMbqEtUser->currentAction->oriValue;
            $data['current_action'] = (string) $oMbqEtUser->currentAction->oriValue;
        }
        else
        {
            $data['current_activity'] = "";
            $data['current_action'] = "";
        }
        if ($oMbqEtUser->topicId->hasSetOriValue()) {
            $data['topic_id'] = (string) $oMbqEtUser->topicId->oriValue;
        }
        if ($oMbqEtUser->canBan->hasSetOriValue()) {
            $data['can_ban'] = (boolean) $oMbqEtUser->canBan->oriValue;
        } else {
            $data['can_ban'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canBan.default');
        }
        if ($oMbqEtUser->isBan->hasSetOriValue()) {
            $data['is_ban'] = (boolean) $oMbqEtUser->isBan->oriValue;
        }
        if ($oMbqEtUser->canMarkSpam->hasSetOriValue()) {
            $data['can_mark_spam'] = (boolean) $oMbqEtUser->canMarkSpam->oriValue;
        } else {
            $data['can_mark_spam'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canMarkSpam.default');
        }
        if ($oMbqEtUser->isSpam->hasSetOriValue()) {
            $data['is_spam'] = (boolean) $oMbqEtUser->isSpam->oriValue;
        }
        if ($oMbqEtUser->reputation->hasSetOriValue()) {
            $data['reputation'] = (int) $oMbqEtUser->reputation->oriValue;
        }
        if ($oMbqEtUser->customFieldsList->hasSetOriValue()) {
            $data['custom_fields_list'] = (array) MbqMain::$oMbqCm->changeArrValueToString($oMbqEtUser->customFieldsList->oriValue);
        }

        if ($oMbqEtUser->postCountdown->hasSetOriValue()) {
            $data['post_countdown'] = (int) $oMbqEtUser->postCountdown->oriValue;
        }

        if ($oMbqEtUser->userType->hasSetOriValue()) {
            $data['user_type'] = (string) $oMbqEtUser->userType->oriValue;
        }
        if ($oMbqEtUser->isIgnored->hasSetOriValue()) {
            $data['is_ignored'] = (boolean) $oMbqEtUser->isIgnored->oriValue;
        }
        $data['from'] = 'browser';
        if ($oMbqEtUser->canActive->hasSetOriValue()) {
            $data['can_active'] = (boolean) $oMbqEtUser->canActive->oriValue;
        } else {
            $data['can_active'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canActive.default');
        }

        return $data;
    }

    public function returnJsonApiDataUser($oMbqEtUser) {
        $data = array();
        if ($oMbqEtUser->userId->hasSetOriValue()) {
            $data['user_id'] = (string) $oMbqEtUser->userId->oriValue;
        }
        $data['username'] = (string) $oMbqEtUser->getDisplayName();
        $data['user_name'] = (string) $oMbqEtUser->getDisplayName();
        $data['login_name'] = (string) $oMbqEtUser->loginName->oriValue;
        if ($oMbqEtUser->userGroupIds->hasSetOriValue()) {
            $data['usergroup_id'] = (array) MbqMain::$oMbqCm->changeArrValueToString($oMbqEtUser->userGroupIds->oriValue);
        }
        if ($oMbqEtUser->iconUrl->hasSetOriValue()) {
            $data['icon_url'] = (string) $oMbqEtUser->iconUrl->oriValue;
        }
        //Added to ensure we will never send back user email due GDPR, only on signing and login
        $methodNeedEmail = array('login','sign_in');
        if(in_array(MbqMain::$cmd,$methodNeedEmail))
        {
            if ($oMbqEtUser->userEmail->hasSetOriValue()) {
                 $data['email'] = (string)sha1($oMbqEtUser->userEmail->oriValue);
            }
        }
        if ($oMbqEtUser->postCount->hasSetOriValue()) {
            $data['post_count'] = (int) $oMbqEtUser->postCount->oriValue;
        }
        if ($oMbqEtUser->canPm->hasSetOriValue()) {
            $data['can_pm'] = (boolean) $oMbqEtUser->canPm->oriValue;
        } else {
            $data['can_pm'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canPm.default');
        }
        if ($oMbqEtUser->canSendPm->hasSetOriValue()) {
            $data['can_send_pm'] = (boolean) $oMbqEtUser->canSendPm->oriValue;
        } else {
            $data['can_send_pm'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canSendPm.default');
        }
        if ($oMbqEtUser->canModerate->hasSetOriValue()) {
            $data['can_moderate'] = (boolean) $oMbqEtUser->canModerate->oriValue;
        } else {
            $data['can_moderate'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canModerate.default');
        }
        if ($oMbqEtUser->canSearch->hasSetOriValue()) {
            $data['can_search'] = (boolean) $oMbqEtUser->canSearch->oriValue;
        } else {
            $data['can_search'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canSearch.default');
        }
        if ($oMbqEtUser->canWhosonline->hasSetOriValue()) {
            $data['can_whosonline'] = (boolean) $oMbqEtUser->canWhosonline->oriValue;
        } else {
            $data['can_whosonline'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canWhosonline.default');
        }
        if ($oMbqEtUser->canUploadAvatar->hasSetOriValue()) {
            $data['can_upload_avatar'] = (boolean) $oMbqEtUser->canUploadAvatar->oriValue;
        } else {
            $data['can_upload_avatar'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canUploadAvatar.default');
        }
        if ($oMbqEtUser->maxAttachment->hasSetOriValue()) {
            $data['max_attachment'] = (int) $oMbqEtUser->maxAttachment->oriValue;
        }
        if ($oMbqEtUser->maxAttachmentSize->hasSetOriValue()) {
            $data['max_attachment_size'] = (int) $oMbqEtUser->maxAttachment->oriValue;
        }
        if ($oMbqEtUser->maxPngSize->hasSetOriValue()) {
            $data['max_png_size'] = (int) $oMbqEtUser->maxPngSize->oriValue;
        }
        if ($oMbqEtUser->maxJpgSize->hasSetOriValue()) {
            $data['max_jpg_size'] = (int) $oMbqEtUser->maxJpgSize->oriValue;
        }
        if ($oMbqEtUser->ignoredUids->hasSetOriValue()) {
            $data['ignored_uids'] = (string) $oMbqEtUser->ignoredUids->oriValue;
        }
        if ($oMbqEtUser->displayText->hasSetOriValue()) {
            $data['display_text'] = (string) $oMbqEtUser->displayText->oriValue;
        }
        if ($oMbqEtUser->regTime->hasSetOriValue()) {
            $data['reg_time'] = (string) MbqMain::$oMbqCm->datetimeIso8601Encode($oMbqEtUser->regTime->oriValue);
            $data['timestamp_reg'] = (string)MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtUser->regTime->oriValue);
        }
        if ($oMbqEtUser->lastActivityTime->hasSetOriValue()) {
            $data['last_activity_time'] = (string) MbqMain::$oMbqCm->datetimeIso8601Encode($oMbqEtUser->lastActivityTime->oriValue);
            $data['timestamp'] = (string)MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtUser->lastActivityTime->oriValue);
        }
        if ($oMbqEtUser->isOnline->hasSetOriValue()) {
            $data['is_online'] = (boolean) $oMbqEtUser->isOnline->oriValue;
        }
        if ($oMbqEtUser->acceptPm->hasSetOriValue()) {
            $data['accept_pm'] = (boolean) $oMbqEtUser->acceptPm->oriValue;
        } else {
            $data['accept_pm'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.acceptPm.default');
        }
        if ($oMbqEtUser->iFollowU->hasSetOriValue()) {
            $data['i_follow_u'] = (boolean) $oMbqEtUser->iFollowU->oriValue;
        } else {
            $data['i_follow_u'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.iFollowU.default');
        }
        if ($oMbqEtUser->uFollowMe->hasSetOriValue()) {
            $data['u_follow_me'] = (boolean) $oMbqEtUser->uFollowMe->oriValue;
        } else {
            $data['u_follow_me'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.uFollowMe.default');
        }
        if ($oMbqEtUser->acceptFollow->hasSetOriValue()) {
            $data['accept_follow'] = (boolean) $oMbqEtUser->acceptFollow->oriValue;
        } else {
            $data['accept_follow'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.acceptFollow.default');
        }
        if ($oMbqEtUser->followingCount->hasSetOriValue()) {
            $data['following_count'] = (int) $oMbqEtUser->followingCount->oriValue;
        }
        if ($oMbqEtUser->follower->hasSetOriValue()) {
            $data['follower'] = (int) $oMbqEtUser->follower->oriValue;
        }
        if ($oMbqEtUser->currentAction->hasSetOriValue()) {
            $data['current_activity'] = (string) $oMbqEtUser->currentAction->oriValue;
            $data['current_action'] = (string) $oMbqEtUser->currentAction->oriValue;
        }
        else
        {
            $data['current_activity'] = "";
            $data['current_action'] = "";
        }
        if ($oMbqEtUser->topicId->hasSetOriValue()) {
            $data['topic_id'] = (string) $oMbqEtUser->topicId->oriValue;
        }
        if ($oMbqEtUser->canBan->hasSetOriValue()) {
            $data['can_ban'] = (boolean) $oMbqEtUser->canBan->oriValue;
        } else {
            $data['can_ban'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canBan.default');
        }
        if ($oMbqEtUser->isBan->hasSetOriValue()) {
            $data['is_ban'] = (boolean) $oMbqEtUser->isBan->oriValue;
        }
        if ($oMbqEtUser->canMarkSpam->hasSetOriValue()) {
            $data['can_mark_spam'] = (boolean) $oMbqEtUser->canMarkSpam->oriValue;
        } else {
            $data['can_mark_spam'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canMarkSpam.default');
        }
        if ($oMbqEtUser->isSpam->hasSetOriValue()) {
            $data['is_spam'] = (boolean) $oMbqEtUser->isSpam->oriValue;
        }
        if ($oMbqEtUser->reputation->hasSetOriValue()) {
            $data['reputation'] = (int) $oMbqEtUser->reputation->oriValue;
        }
        if ($oMbqEtUser->customFieldsList->hasSetOriValue()) {
            $data['custom_fields_list'] = (array) MbqMain::$oMbqCm->changeArrValueToString($oMbqEtUser->customFieldsList->oriValue);
        }

        if ($oMbqEtUser->postCountdown->hasSetOriValue()) {
            $data['post_countdown'] = (int) $oMbqEtUser->postCountdown->oriValue;
        }

        if ($oMbqEtUser->userType->hasSetOriValue()) {
            $data['user_type'] = (string) $oMbqEtUser->userType->oriValue;
        }
        if ($oMbqEtUser->isIgnored->hasSetOriValue()) {
            $data['is_ignored'] = (boolean) $oMbqEtUser->isIgnored->oriValue;
        }
        $data['from'] = 'browser';
        if ($oMbqEtUser->canActive->hasSetOriValue()) {
            $data['can_active'] = (boolean) $oMbqEtUser->canActive->oriValue;
        } else {
            $data['can_active'] = (boolean) MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canActive.default');
        }

        return $data;
    }

    /**
     * return user avatar
     *
     * @param  Object  $oMbqEtUser
     * @return  url string
     */
    public function returnUserAvatar($oMbqEtUser)
    {
        return $oMbqEtUser->iconUrl->hasSetOriValue() ? (string) $oMbqEtUser->iconUrl->oriValue : '';
    }

  
    /**
     * return user array api data
     *
     * @param  Array  $objsMbqEtUser
     * @param  Boolean  $forceHash mark whether return hash data
     * @return  Array
     */
    public function returnApiArrDataUser($objsMbqEtUser, $forceHash = false) {
        $data = array();
        foreach ($objsMbqEtUser as $oMbqEtUser) {
            if ($forceHash) {
                $data[$oMbqEtUser->userId->oriValue] = $this->returnApiDataUser($oMbqEtUser);
            } else {
                $data[] = $this->returnApiDataUser($oMbqEtUser);
            }
        }
        return $data;
    }

    public function returnApiDataCustomRegisterField($customRegisterFields)
    {
    }
    /**
     * login
     *
     * @return  Boolean  return true when login success.
     */
    public function login($login, $password, $anonymous, $push) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * logout
     *
     * @return  Boolean  return true when logout success.
     */
    public function logout() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * get user objs
     *
     * @return  Array
     */
    public function getObjsMbqEtUser($var, $mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * init one user by condition
     *
     * @return  Mixed
     */
    public function initOMbqEtUser($var, $mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * init current user obj if login
     */
    public function initOCurMbqEtUser($userId) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * get user display name
     *
     * @return  String
     */
    public function getDisplayName($oMbqEtUser) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }


    /**
    * login directly without password
    * only used for sign_in method
    *
    * @return true or error message
    */
    public function loginDirectly($oMbqEtUser, $trustCode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
    * forget_password this function should send the email password change to this user
    *
    * @return Array
    */
    public function forgetPassword($oMbqEtUser) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
    * judge is admin role
    *
    * @return Boolean
    */
    public function isAdminRole() {
    }

    /**
     * return custom register fields needed.
     * Return null if not needed
     * sample result is array of fields like:
     * array(
        'name'        => 'name',
        'description' => 'description',
        'key'         => 'field_id',
        'type'        => input,textarea,drop,radio,cbox,
        'options'     => '',
        'format'      => '',
        )
     */
    public function getCustomRegisterFields()
    {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * the response should be bool to indicate if the username meet the forum requirement
     *
     * @param string $username
     */
    public function validateUsername($username){
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * the response should be bool to indicate if the password meet the forum requirement
     *
     * @param string $password
     */
    public function validatePassword($password){
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
}
