<?php

defined('MBQ_IN_IT') or exit;

/**
 * poll read class
 */
Abstract Class MbqBaseRdEtPoll extends MbqBaseRd {
    
    public function __construct() {
    }
    
    /**
     * return poll api data
     *
     * @param  Object  $oMbqEtPoll
     * @return  Array
     */
    public function returnApiDataPoll($oMbqEtPoll) {
        $data = array();
        if ($oMbqEtPoll->pollTitle->hasSetOriValue()) {
            $data['title'] = (string) $oMbqEtPoll->pollTitle->oriValue;
        }
        if ($oMbqEtPoll->pollOptions->hasSetOriValue()) {
            $data['options'] = (array) $oMbqEtPoll->pollOptions->oriValue;
        }
        if ($oMbqEtPoll->pollLength->hasSetOriValue()) {
            $data['length'] = (string) $oMbqEtPoll->pollLength->oriValue;
        }
        if ($oMbqEtPoll->pollMaxOptions->hasSetOriValue()) {
            $data['max_options'] = (string) $oMbqEtPoll->pollMaxOptions->oriValue;
        }
        if ($oMbqEtPoll->canRevoting->hasSetOriValue()) {
            $data['can_revoting'] = (boolean) $oMbqEtPoll->canRevoting->oriValue;
        } else {
            $data['can_revoting'] = (boolean) MbqBaseFdt::getFdt('MbqFdtPoll.MbqEtPoll.canRevoting.default');
        }
        if ($oMbqEtPoll->canViewBeforeVote->hasSetOriValue()) {
            $data['can_view_before_vote'] = (boolean) $oMbqEtPoll->canViewBeforeVote->oriValue;
        } else {
            $data['can_view_before_vote'] = (boolean) MbqBaseFdt::getFdt('MbqFdtPoll.MbqEtPoll.canViewBeforeVote.default');
        }
        if ($oMbqEtPoll->canPublic->hasSetOriValue()) {
            $data['can_public'] = (boolean) $oMbqEtPoll->canPublic->oriValue;
        } else {
            $data['can_public'] = (boolean) MbqBaseFdt::getFdt('MbqFdtPoll.MbqEtPoll.canPublic.default');
        }
        if ($oMbqEtPoll->myVotes->hasSetOriValue()) {
            $data['my_votes'] = (array) $oMbqEtPoll->myVotes->oriValue;
        }
        return $data;
    }
    
    /**
     * return poll array api data
     *
     * @param  Array  $objsMbqEtPoll
     * @return  Array
     */
    public function returnApiArrDataPoll($objsMbqEtPoll) {
        $data = array();
        foreach ($objsMbqEtPoll as $oMbqEtPoll) {
            $data[] = $this->returnApiDataPoll($oMbqEtPoll);
        }
        return $data;
    }
    
    /**
     * get poll objs
     *
     * @return  Mixed
     */
    public function getObjsMbqEtPoll($var, $mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * init one poll by condition
     *
     * @return  Mixed
     */
    public function initOMbqEtPoll() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
  
}

