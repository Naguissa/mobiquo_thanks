<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActPushContentCheck');

Class MbqActPushContentCheck extends MbqBaseActPushContentCheck {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * action implement
     */
    public function actionImplement($in) {
        global $db;
        $result = false;
        switch($in->data['type'])
        {
            case 'newtopic':
            case 'sub':
            case 'quote':
            case 'tag':
            {
                $sql = "SELECT p.topic_id, p.poster_id, u.username FROM " . POSTS_TABLE . " p JOIN " . USERS_TABLE . " u ON u.user_id = p.poster_id WHERE p.post_id = ". $in->data['subid'];
                $sqlresult = $db->sql_query($sql);
                while($row = $db->sql_fetchrow($sqlresult))
                {
                    if($row['topic_id'] == $in->data['id'] && ($row['poster_id'] == $in->data['authorid'] || $row['username'] == $in->data['author']))
                    {
                        $result = true;
                    }
                }
                $db->sql_freeresult($sqlresult);
                break;
            } 
            case 'pm':
            {
                $sql = "SELECT p.author_id, u.username FROM " . PRIVMSGS_TABLE . " p JOIN " . USERS_TABLE . " u ON u.user_id = p.author_id WHERE p.msg_id = ". $in->data['id'];
                $sqlresult = $db->sql_query($sql);
                $row = $db->sql_fetchrow($sqlresult);
                if($row['author_id'] == $in->data['authorid'] || $row['username'] == $in->data['author'])
                {
                        $result = true;
                }
                $db->sql_freeresult($sqlresult);
                break;
            }
        
        }
        $this->data =  array(
            'result' => $result,
            'result_text' => $result ? '' : 'fail',
        );
    }
  
}
