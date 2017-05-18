<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtSocial');

/**
 * Social read class
 */
Class MbqRdEtSocial extends MbqBaseRdEtSocial {
    
    public function __construct() {
    }
    
    /**
     * get social objs
     *
     * @return  Array
     */
    public function getObjsMbqEtSocial($var, $mbqOpt) {
        if($mbqOpt['case'] == 'alert')
        {
            global $db,$user, $phpbb_root_path,$phpEx, $phpbb_container;
            $oMbqDataPage = $mbqOpt['oMbqDataPage'];
            
            $phpbb_notifications = $phpbb_container->get('notification_manager');
            $notifications = $phpbb_notifications->load_notifications(array(
                      'start'			=> $oMbqDataPage->startNum,
                      'limit'			=> $oMbqDataPage->numPerPage,
                      'count_total'	=> true,
                      'user_id' => $user->data['user_id'],
                  ));

            $oMbqDataPage->totalNum = $notifications['total_count'];
            $oMbqDataPage->datas = array();
            foreach($notifications['notifications'] as $notificationRow)
            {
               $oMbqDataPage->datas[] = $this->initOMbqEtSocial($notificationRow, $mbqOpt);
            }
            return $oMbqDataPage;
        }
        else
        {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
        }
    }
    
    /**
     * init one social by condition
     *
     * @return  Mixed
     */
    public function initOMbqEtSocial($row, $mbqOpt) {
        if($mbqOpt['case'] == 'alert')
        {
            global $db;
            $notification = $row->__get('notification_data');
            
            $lang = array(
		        'reply_to_you' => "%s replied to \"%s\"",
		        'quote_to_you' => '%s quoted your post in thread "%s"',
	            'tag_to_you' => '%s mentioned you in thread "%s"',
	            'post_new_topic' => '%s started a new thread "%s"',
	            'like_your_thread' => '%s liked your post in thread "%s"',
		        'pm_to_you' => '%s sent you a message "%s"',
	        );
           
            $oMbqEtAlert = MbqMain::$oClk->newObj('MbqEtAlert');
          
            switch ($row->__get('notification_type_name'))
            {
                case 'notification.type.topic':
                    $oMbqEtAlert->userId->setOriValue($notification['poster_id']);
                    $oMbqEtAlert->message->setOriValue(sprintf($lang['post_new_topic'],$oMbqEtAlert->username->oriValue,$notification['topic_title']));
                    $oMbqEtAlert->contentType->setOriValue('newtopic');
                    $oMbqEtAlert->topicId->setOriValue($row->__get('item_id'));
                    $sql = 'SELECT topic_first_post_id
                    FROM ' . TOPICS_TABLE . ' t
                    WHERE t.forum_id = ' . $row->__get('item_parent_id').'
                    AND t.topic_id = ' . $row->__get('item_id');
                    $result = $db->sql_query($sql);
                    $topic_first_post_id = (int) $db->sql_fetchfield('topic_first_post_id');
                    $db->sql_freeresult($result);
                    $oMbqEtAlert->contentId->setOriValue($topic_first_post_id);
                    break;
                case 'notification.type.quote':
                    $oMbqEtAlert->userId->setOriValue($notification['poster_id']);
                    $oMbqEtAlert->message->setOriValue(sprintf($lang['quote_to_you'],$oMbqEtAlert->username->oriValue,$notification['topic_title']));
                    $oMbqEtAlert->contentType->setOriValue('quote');
                    $oMbqEtAlert->contentId->setOriValue($row->__get('item_id'));
                    $oMbqEtAlert->topicId->setOriValue($row->__get('item_parent_id'));
                    break;
                case 'notification.type.post':
                    $oMbqEtAlert->userId->setOriValue($notification['poster_id']);
                    $oMbqEtAlert->message->setOriValue(sprintf($lang['reply_to_you'],$oMbqEtAlert->username->oriValue,$notification['topic_title']));
                    $oMbqEtAlert->contentType->setOriValue('sub');
                    $oMbqEtAlert->contentId->setOriValue($row->__get('item_id'));
                    $oMbqEtAlert->topicId->setOriValue($row->__get('item_parent_id'));
                    break;
                case 'notification.type.pm':
                    $oMbqEtAlert->userId->setOriValue($notification['from_user_id']);
                    $oMbqEtAlert->message->setOriValue(sprintf($lang['pm_to_you'],$oMbqEtAlert->username->oriValue,$notification['message_subject']));
                    $oMbqEtAlert->contentType->setOriValue('pm');
                    $oMbqEtAlert->contentId->setOriValue($row->__get('item_id'));
                    break;
            }
            $oMbqEtAlert->username->setOriValue(get_name_by_userid($oMbqEtAlert->userId->oriValue));
            $avatars = get_user_avatars($oMbqEtAlert->userId->oriValue);
            if(sizeof($avatars) == 1)
            {
                $oMbqEtAlert->iconUrl->setOriValue($avatars[$oMbqEtAlert->userId->oriValue]);
            }
            $oMbqEtAlert->timestamp->setOriValue($row->__get('notification_time'));
            $oMbqEtAlert->isUnread->setOriValue($row->__get('notification_read') == 0);
            return $oMbqEtAlert;
        }
        else
        {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
        }
        
    }
    
}

