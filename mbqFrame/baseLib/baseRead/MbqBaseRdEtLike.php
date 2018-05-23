<?php

defined('MBQ_IN_IT') or exit;

/**
 * Like read class
 */
Abstract Class MbqBaseRdEtLike extends MbqBaseRd {

    public function __construct() {
    }

    /**
     * return thank api data
     *
     * @param  Object  $oMbqEtLike
     * @return  Array
     */
    public function returnApiDataLike($oMbqEtLike) {
        $data = array();
        if ($oMbqEtLike->userId->hasSetOriValue()) {
            $data['userid'] = (string) $oMbqEtLike->userId->oriValue;
        }
        if ($oMbqEtLike->oMbqEtUser &&  is_a($oMbqEtLike->oMbqEtUser, 'MbqEtUser')) {
            $data['username'] = (string) $oMbqEtLike->oMbqEtUser->getDisplayName();
        }
        return $data;
    }

    /**
     * return Like array api data
     *
     * @param  Array  $objsMbqEtLike
     * @return  Array
     */
    public function returnApiArrDataLike($objsMbqEtLike) {
        $data = array();
        foreach ($objsMbqEtLike as $oMbqEtLike) {
            $data[] = $this->returnApiDataLike($oMbqEtLike);
        }
        return $data;
    }

    /**
     * get Like objs
     *
     * @return  Mixed
     */
    public function getObjsMbqEtLike($var, $mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * init one Like by condition
     *
     * @return  Mixed
     */
    public function initOMbqEtLike($var,$mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

}
