<?php

defined('MBQ_IN_IT') or exit;

/**
 * Social read class
 */
Abstract Class MbqBaseRdEtSocial extends MbqBaseRd {
    
    public function __construct() {
    }
    
    /**
     * return Alert api data
     *
     * @param  Object  $oMbqEtAlert
     * @return  Array
     */
    public function returnApiDataAlert($oMbqEtAlert) {
        if (MbqMain::isJsonProtocol()) return $this->returnJsonApiDataAlert($oMbqEtAlert);
        $data = array();
        if ($oMbqEtAlert->userId->hasSetOriValue()) {
            $data['user_id'] = (string) $oMbqEtAlert->userId->oriValue;
        }
        if ($oMbqEtAlert->username->hasSetOriValue()) {
            $data['username'] = (string) $oMbqEtAlert->username->oriValue;
        }
        if ($oMbqEtAlert->iconUrl->hasSetOriValue()) {
            $data['icon_url'] = (string) $oMbqEtAlert->iconUrl->oriValue;
        }
        if ($oMbqEtAlert->message->hasSetOriValue()) {
            $data['message'] = (string) $oMbqEtAlert->message->oriValue;
        }
        if ($oMbqEtAlert->timestamp->hasSetOriValue()) {
            $data['timestamp'] = (string) $oMbqEtAlert->timestamp->oriValue;
        }
        if ($oMbqEtAlert->contentType->hasSetOriValue()) {
            $data['content_type'] = (string) $oMbqEtAlert->contentType->oriValue;
        }
        if ($oMbqEtAlert->contentId->hasSetOriValue()) {
            $data['content_id'] = (string) $oMbqEtAlert->contentId->oriValue;
        }
        if ($oMbqEtAlert->topicId->hasSetOriValue()) {
            $data['topic_id'] = (string) $oMbqEtAlert->topicId->oriValue;
        }
        if ($oMbqEtAlert->position->hasSetOriValue()) {
            $data['position'] = (int) $oMbqEtAlert->position->oriValue;
        }
        if ($oMbqEtAlert->isUnread->hasSetOriValue()) {
            $data['unread'] = (boolean) $oMbqEtAlert->isUnread->oriValue;
        }
        return $data;
    }
    public function returnJsonApiDataAlert($oMbqEtAlert) {
        $data = array();
        if ($oMbqEtAlert->userId->hasSetOriValue()) {
            $data['user_id'] = (string) $oMbqEtAlert->userId->oriValue;
        }
        if ($oMbqEtAlert->username->hasSetOriValue()) {
            $data['username'] = (string) $oMbqEtAlert->username->oriValue;
        }
        if ($oMbqEtAlert->iconUrl->hasSetOriValue()) {
            $data['icon_url'] = (string) $oMbqEtAlert->iconUrl->oriValue;
        }
        if ($oMbqEtAlert->message->hasSetOriValue()) {
            $data['message'] = (string) $oMbqEtAlert->message->oriValue;
        }
        if ($oMbqEtAlert->timestamp->hasSetOriValue()) {
            $data['timestamp'] = (string) $oMbqEtAlert->timestamp->oriValue;
        }
        if ($oMbqEtAlert->contentType->hasSetOriValue()) {
            $data['content_type'] = (string) $oMbqEtAlert->contentType->oriValue;
        }
        if ($oMbqEtAlert->contentId->hasSetOriValue()) {
            $data['content_id'] = (string) $oMbqEtAlert->contentId->oriValue;
        }
        if ($oMbqEtAlert->topicId->hasSetOriValue()) {
            $data['topic_id'] = (string) $oMbqEtAlert->topicId->oriValue;
        }
        if ($oMbqEtAlert->position->hasSetOriValue()) {
            $data['position'] = (int) $oMbqEtAlert->position->oriValue;
        }
        if ($oMbqEtAlert->isUnread->hasSetOriValue()) {
            $data['unread'] = (boolean) $oMbqEtAlert->isUnread->oriValue;
        }
        return $data;
    }
  
    /**
     * return alert array api data
     *
     * @param  Array  $objsMbqEtAlert
     * @return  Array
     */
    public function returnApiArrDataAlert($objsMbqEtAlert) {
        $data = array();
        foreach ($objsMbqEtAlert as $oMbqEtAlert) {
            $data[] = $this->returnApiDataAlert($oMbqEtAlert);
        }
        return $data;
    }
   
    
    /**
     * get social objs
     *
     * @return  Array
     */
    public function getObjsMbqEtSocial($var, $mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * init one social by condition
     *
     * @return  Mixed
     */
    public function initOMbqEtSocial($var, $mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
}
