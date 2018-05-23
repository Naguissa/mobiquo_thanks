<?php

defined('MBQ_IN_IT') or exit;

Abstract Class MbqBaseRdEtSysStatistics extends MbqBaseRd {
    
    public function __construct() {
    }
    /**
     * return user api data
     *
     * @param  Object  $oMbqEtUser
     * @return  Array
     */
    public function returnApiDataSysStatistics($oMbqEtSysStatistics) {
        if (MbqMain::isJsonProtocol()) return $this->returnJsonApiDataSysStatistics($oMbqEtSysStatistics);
        $data = array();
        if ($oMbqEtSysStatistics->forumTotalThreads->hasSetOriValue()) {
            $data['total_threads'] = (int) $oMbqEtSysStatistics->forumTotalThreads->oriValue;
        }
        if ($oMbqEtSysStatistics->forumTotalPosts->hasSetOriValue()) {
            $data['total_posts'] = (int) $oMbqEtSysStatistics->forumTotalPosts->oriValue;
        }
        if ($oMbqEtSysStatistics->forumTotalMembers->hasSetOriValue()) {
            $data['total_members'] = (int) $oMbqEtSysStatistics->forumTotalMembers->oriValue;
        }
        if ($oMbqEtSysStatistics->forumActiveMembers->hasSetOriValue()) {
            $data['active_members'] = (int) $oMbqEtSysStatistics->forumActiveMembers->oriValue;
        }
        if ($oMbqEtSysStatistics->forumTotalOnline->hasSetOriValue()) {
            $data['total_online'] = (int) $oMbqEtSysStatistics->forumTotalOnline->oriValue;
        }
        if ($oMbqEtSysStatistics->forumGuestOnline->hasSetOriValue()) {
            $data['guest_online'] = (int) $oMbqEtSysStatistics->forumGuestOnline->oriValue;
        }
        return $data;
    }
    public function returnJsonApiDataSysStatistics($oMbqEtSysStatistics) {
        $data = array();
        if ($oMbqEtSysStatistics->forumTotalThreads->hasSetOriValue()) {
            $data['total_threads'] = (int) $oMbqEtSysStatistics->forumTotalThreads->oriValue;
        }
        if ($oMbqEtSysStatistics->forumTotalPosts->hasSetOriValue()) {
            $data['total_posts'] = (int) $oMbqEtSysStatistics->forumTotalPosts->oriValue;
        }
        if ($oMbqEtSysStatistics->forumTotalMembers->hasSetOriValue()) {
            $data['total_members'] = (int) $oMbqEtSysStatistics->forumTotalMembers->oriValue;
        }
        if ($oMbqEtSysStatistics->forumActiveMembers->hasSetOriValue()) {
            $data['active_members'] = (int) $oMbqEtSysStatistics->forumActiveMembers->oriValue;
        }
        if ($oMbqEtSysStatistics->forumTotalOnline->hasSetOriValue()) {
            $data['total_online'] = (int) $oMbqEtSysStatistics->forumTotalOnline->oriValue;
        }
        if ($oMbqEtSysStatistics->forumGuestOnline->hasSetOriValue()) {
            $data['guest_online'] = (int) $oMbqEtSysStatistics->forumGuestOnline->oriValue;
        }
        return $data;
    }
    /**
     * init system statistics by condition
     *
     * @return  Object
     */
    public function initOMbqEtSysStatistics() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
  
}
