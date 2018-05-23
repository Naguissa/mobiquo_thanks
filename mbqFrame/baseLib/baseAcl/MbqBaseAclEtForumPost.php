<?php

defined('MBQ_IN_IT') or exit;

/**
 * forum post acl class
 */
Abstract Class MbqBaseAclEtForumPost extends MbqBaseAcl {
    
    public function __construct() {
    }
    
    /**
     * judge can reply post
     *
     * @return  Boolean
     */
    public function canAclReplyPost($oMbqEtForumTopic) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can get quote post
     *
     * @return  Boolean
     */
    public function canAclGetQuotePost($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can search_post
     *
     * @return  Boolean
     */
    public function canAclSearchPost() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can get_raw_post
     *
     * @return  Boolean
     */
    public function canAclGetRawPost($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can save_raw_post
     *
     * @return  Boolean
     */
    public function canAclSaveRawPost($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can get_user_reply_post
     *
     * @return  Boolean
     */
    public function canAclGetUserReplyPost() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can report_post
     *
     * @return  Boolean
     */
    public function canAclReportPost($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can thank_post
     *
     * @return  Boolean
     */
    public function canAclThankPost($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can can like
     *
     * @return  Boolean
     */
    public function canAclLikePost($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * judge can can unlike
     *
     * @return  Boolean
     */
    public function canAclUnlikePost($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * judge can m_delete_post
     *
     * @return  Boolean
     */
    public function canAclMDeletePost($oMbqEtForumPost, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can m_undelete_post
     *
     * @return  Boolean
     */
    public function canAclMUndeletePost($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can m_move_post
     *
     * @return  Boolean
     */
    public function canAclMMovePost($oMbqEtForumPost, $oMbqEtForum, $oMbqEtForumTopic) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can m_approve_post
     *
     * @return  Boolean
     */
    public function canAclMApprovePost($oMbqEtForumPost, $mode) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can m_merge_post
     *
     * @return  Boolean
     */
    public function canAclMMergePost($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * judge can m_get_moderate_post
     *
     * @return  Boolean
     */
    public function canAclMGetModeratePost() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * judge can m_get_delete_post
     *
     * @return  Boolean
     */
    public function canAclMGetDeletePost() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }   /**
     * judge can m_get_report_post
     *
     * @return  Boolean
     */
    public function canAclMGetReportPost() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
  
}