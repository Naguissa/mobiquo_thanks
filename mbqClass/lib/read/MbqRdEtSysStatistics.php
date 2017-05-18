<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtSysStatistics');

/**
 * system statistics read class
 */
Class MbqRdEtSysStatistics extends MbqBaseRdEtSysStatistics {
    
    public function __construct() {
    }
  /**
     * init system statistics by condition
     *
     * @return  Object
     */
    public function initOMbqEtSysStatistics() {
        global $config, $phpbb_root_path, $phpEx;
        $oMbqEtSysStatistics = MbqMain::$oClk->newObj('MbqEtSysStatistics');
        $online_users = obtain_users_online();
        $oMbqEtSysStatistics->forumTotalThreads->setOriValue($config['num_topics']);
        $oMbqEtSysStatistics->forumTotalPosts->setOriValue($config['num_posts']);
        $oMbqEtSysStatistics->forumTotalMembers->setOriValue($config['num_users']);
        $oMbqEtSysStatistics->forumActiveMembers->setOriValue($online_users['total_online'] - $online_users['guests_online']);
        $oMbqEtSysStatistics->forumTotalOnline->setOriValue($online_users['total_online']);
        $oMbqEtSysStatistics->forumGuestOnline->setOriValue($online_users['guests_online']);
        return $oMbqEtSysStatistics;
    }
  
}
