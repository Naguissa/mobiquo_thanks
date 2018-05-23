<?php

defined('MBQ_IN_IT') or exit;

/**
 * user acl class
 */
Abstract Class MbqBaseAclEtUser extends MbqBaseAcl {

    public function __construct() {
    }

    /**
     * judge can get online users
     *
     * @return  Boolean
     */
    public function canAclGetOnlineUsers() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can get online users
     *
     * @return  Boolean
     */
    public function canAclGetIgnoredUsers() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can m_ban_user
     *
     * @return  Boolean
     */
    public function canAclMBanUser($oMbqEtUser, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can m_mark_as_spam
     *
     * @return  Boolean
     */
    public function canAclMMarkAsSpam($oMbqEtUser) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can m_ban_user
     *
     * @return  Boolean
     */
    public function canAclMUnbanUser($oMbqEtUser) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
    * judge can update_password
    *
    * @return Boolean
    */
    public function canAclUpdatePassword() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
    * judge can update_email
    *
    * @return Boolean
    */
    public function canAclUpdateEmail() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can upload avatar
     *
     * @return Boolean
     */
    public function canAclUploadAvatar() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can searc_user
     *
     * @return Boolean
     */
    public function canAclSearchUser() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * judge can get_recommended_user
     *
     * @return Boolean
     */
    public function canAclGetRecommendedUser() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * judge can ignore_user
     *
     * @return Boolean
     */
    public function canAclIgnoreUser($oMbqEtUser, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
}

