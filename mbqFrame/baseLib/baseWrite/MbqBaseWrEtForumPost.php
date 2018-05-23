<?php

defined('MBQ_IN_IT') or exit;

/**
 * forum post write class
 */
Abstract Class MbqBaseWrEtForumPost extends MbqBaseWr {
    
    public function __construct() {
    }
    
    /**
     * add forum post
     */
    public function addMbqEtForumPost($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * modify forum post
     */
    public function mdfMbqEtForumPost($oMbqEtForumPost, $mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * report post
     */
    public function reportPost($oMbqEtForumPost, $reason) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * thank post
     */
    public function thankPost($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * thank post
     */
    public function likePost($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * thank post
     */
    public function unlikePost($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * m_delete_post
     */
    public function mDeletePost($oMbqEtForumPost, $mode, $reason) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * m_undelete_post
     */
    public function mUndeletePost($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * m_move_post
     */
    public function mMovePost($oMbqEtForumPosts, $oMbqEtForum, $oMbqEtForumTopic, $topicTitle) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * m_approve_post
     */
    public function mApprovePost($oMbqEtForumPost, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * m_merge_post
     */
    public function mMergePost($objsMbqEtForumPost, $oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
  
}
