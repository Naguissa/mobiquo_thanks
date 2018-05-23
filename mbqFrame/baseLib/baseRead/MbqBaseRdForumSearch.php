<?php

defined('MBQ_IN_IT') or exit;

/**
 * forum search class
 */
Abstract Class MbqBaseRdForumSearch {
    
    public function __construct() {
    }
    
    /**
     * forum advanced search
     *
     * @return  Object  $oMbqDataPage
     */
    public function forumAdvancedSearch($filter, $oMbqDataPage, $mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
  
}
