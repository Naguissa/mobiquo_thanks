<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseAclEtAtt');

/**
 * attachment acl class
 */
Class MbqAclEtAtt extends MbqBaseAclEtAtt {
    
    public function __construct() {
    }
    /**
     * judge can upload attachment
     *
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclUploadAttach($oMbqEtForumOrConvPm, $groupId, $type) {
        global $auth, $config, $user;
        if(MbqMain::hasLogin())
        {
            if (!$oMbqEtForumOrConvPm)  return getSystemString('NO_FORUM');
            $forum_id = $oMbqEtForumOrConvPm->forumId->oriValue;
            if ($oMbqEtForumOrConvPm->mbqBind['forum_type'] != FORUM_POST)  return getSystemString('USER_CANNOT_FORUM_POST');
            if (!$auth->acl_gets('f_read', $forum_id))  return getSystemString('USER_CANNOT_READ');
            if ($oMbqEtForumOrConvPm->mbqBind['forum_password'] && !check_forum_password($forum_id))  return getSystemString('LOGIN_FORUM');
            if (($user->data['is_bot'] || !$auth->acl_get('f_attach', $forum_id) || !$auth->acl_get('u_attach') || !$config['allow_attachments'] || @ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off'))
                 return getSystemString('NOT_AUTHORISED');
            
            if ((!$auth->acl_get('f_post', $forum_id) && !$auth->acl_gets('f_edit', 'm_edit', $forum_id) && !$auth->acl_get('f_reply', $forum_id)))
                 return getSystemString('USER_CANNOT_POST');
            return true;
        }
        return false;
    }
    
    /**
     * judge can remove attachment
     *
     * @param  Object  $oMbqEtAtt
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclRemoveAttachment($oMbqEtAtt, $oMbqEtForum) {
        global $db, $user, $auth, $config;
        $attachment_id  = $oMbqEtAtt->attId->oriValue;
        $forum_id       = $oMbqEtForum->forumId->oriValue;
        $group_id       = $oMbqEtAtt->groupId->oriValue;
        $post_id        = $oMbqEtAtt->postId->oriValue;
        $_POST['attachment_data'] = $group_id ? unserialize(base64_decode($group_id)) : array();
        
        // Forum does not exist
        if (!$forum_id)  return getSystemString('NO_FORUM');
        
        $sql = "SELECT f.* FROM " . FORUMS_TABLE . " f WHERE f.forum_id = $forum_id";
        $result = $db->sql_query($sql);
        $forum_data = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);   
        
        if (!$forum_data)  return getSystemString('NO_FORUM');
        if ($forum_data['forum_password'] && !check_forum_password($forum_id))
             return getSystemString('LOGIN_FORUM');
        
        if (!$auth->acl_gets('f_read', $forum_id))
        {
            if ($user->data['user_id'] != ANONYMOUS)
            {
                 return getSystemString('USER_CANNOT_READ');
            }
            
             return getSystemString('LOGIN_EXPLAIN_POST');
        }
        
        // Is the user able to post within this forum?
        if ($forum_data['forum_type'] != FORUM_POST)
        {
             return getSystemString('USER_CANNOT_FORUM_POST');
        }
        
        // Check permissions
        if (($user->data['is_bot'] || !$auth->acl_get('f_attach', $forum_id) || !$auth->acl_get('u_attach') || !$config['allow_attachments'] || @ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off'))
             return getSystemString('NOT_AUTHORISED');
        
        if ((!$auth->acl_get('f_post', $forum_id) && !$auth->acl_gets('f_edit', 'm_edit', $forum_id) && !$auth->acl_get('f_reply', $forum_id)))
            trigger_error('USER_CANNOT_POST');
        
        return true;
    }
}
