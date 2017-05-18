<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseAclEtPm');

/**
 * private message acl class
 */
Class MbqAclEtPm extends MbqBaseAclEtPm {

    public function __construct() {
    }
    /**
     * judge can report_pm
     *
     * @return  Boolean
     */
    public function canAclReportPm($oMbqEtPm) {
        return $oMbqEtPm->canReport->oriValue == 1;
    }

    /**
     * judge can create_message
     *
     * @return  Boolean
     */
    public function canAclCreateMessage() {
        global $user, $config;
        if (!$user->data['is_registered']) return false;
        if (!$config['allow_privmsg'])  return false;
        return true;
    }

    /**
     * judge can get_box_info
     *
     * @return  Boolean
     */
    public function canAclGetBoxInfo() {
        global $user, $config;
        if (!$user->data['is_registered']) return false;
        if (!$config['allow_privmsg']) return false;
        return true;
    }

    /**
     * judge can get_box
     *
     * @return  Boolean
     */
    public function canAclGetBox() {
        global $user, $config;
        if (!$user->data['is_registered']) return false;
        if (!$config['allow_privmsg']) return false;
        return true;
    }

    /**
     * judge can get_message
     *
     * @return  Boolean
     */
    public function canAclGetMessage() {
        global $user, $config;
        if (!$user->data['is_registered']) return false;
        if (!$config['allow_privmsg']) return false;
        return true;
    }

    /**
     * judge can get_quote_pm
     *
     * @return  Boolean
     */
    public function canAclGetQuotePm() {
        global $user, $config;
        if (!$user->data['is_registered']) return false;
        if (!$config['allow_privmsg']) return false;
        return true;
    }

    /**
     * judge can delete_message
     *
     * @return  Boolean
     */
    public function canAclDeleteMessage() {
        global $user, $config;
        if (!$user->data['is_registered']) return false;
        if (!$config['allow_privmsg'])  return false;
        return true;
    }

    /**
     * judge can mark_pm_unread
     *
     * @return  Boolean
     */
    public function canAclMarkPmUnread($oMbqEtPm) {
        global $user, $config;
        if (!$user->data['is_registered']) return false;
        if (!$config['allow_privmsg'])  return false;
        return true;
    }

    /**
     * judge can mark_pm_read
     *
     * @return  Boolean
     */
    public function canAclMarkPmRead($oMbqEtPm) {
        global $user, $config;
        if (!$user->data['is_registered']) return false;
        if (!$config['allow_privmsg'])  return false;
        return true;
    }
    /**
     * judge can mark_pm_read
     *
     * @return  Boolean
     */
    public function canAclMarkAllPmRead() {
        global $user, $config;
        if (!$user->data['is_registered']) return false;
        if (!$config['allow_privmsg'])  return false;
        return true;
    }
      /**
     * judge can mark_pm_read
     *
     * @return  Boolean
     */
    public function canAclUpload($oMbqEtPm) {
        global $user, $config;
        if (!$user->data['is_registered']) return false;
        if (!$config['allow_privmsg']) return false;
        return true;
    }
}
