<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActUserSubscription');

Class MbqActUserSubscription extends MbqBaseActUserSubscription {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * action implement
     */
    public function actionImplement($in) {
        global $db;
        $forums = array();
        $topics = array();
      
        $sql = 'SELECT w.forum_id, f.forum_name
			FROM ' . FORUMS_WATCH_TABLE . ' AS w
            JOIN ' . FORUMS_TABLE . ' AS f on w.forum_id = f.forum_id
			WHERE w.user_id=' . $in->userId;
        $result = $db->sql_query($sql);
        
		while ($row = $db->sql_fetchrow($result))
		{
            $forums[] = array(
                           'fid'      => $row['forum_id'],
                           'name'    => $row['forum_name'],
                       );
		}
		$db->sql_freeresult($result);
        
        $sql = 'SELECT w.topic_id
			FROM ' . TOPICS_WATCH_TABLE . ' AS w
			WHERE w.user_id=' . $in->userId;
        $result = $db->sql_query($sql);
        
		while ($row = $db->sql_fetchrow($result))
		{
            $topics[] = $row['topic_id'];
		}
		$db->sql_freeresult($result);
        
        $this->data = array(
             'result'      => true,
             'forums'      => $forums,
             'topics'       => $topics,
         );
    }
  
}
