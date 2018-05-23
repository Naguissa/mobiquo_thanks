<?php

defined('MBQ_IN_IT') or exit;

/**
 * user write class
 */
Abstract Class MbqBaseWrEtUser extends MbqBaseWr {
    
    public function __construct() {
    }
    
    /**
     * m_ban_user
     */
    public function mBanUser($oMbqEtUser, $mode, $reason, $expires) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * m_ban_user
     */
    public function mUnbanUser($oMbqEtUser) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * m_mark_as_spam
     */
    public function mMarkAsSpam($oMbqEtUser) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * register user
     */
    public function registerUser($username, $password, $email, $isVerified, $customRegisterFields, $tapatalkProfile, &$errors) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * update password
     */
    public function updatePassword($oldPassword, $newPassword) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * update password directly
     */
    public function updatePasswordDirectly($oMbqEtUser, $newPassword) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * update email
     */
    public function updateEmail($password, $email, &$resultMessage) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * upload avatar
     */
    public function uploadAvatar() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * ignoreUser
     */
    public function ignoreUser($oMbqEtUser, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
}
