<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtPoll');

/**
 * poll read class
 */
Class MbqRdEtPoll extends MbqBaseRdEtPoll {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtPoll, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }

    public function initOMbqEtPoll($var, $mbqOpt) {
        global $db, $user;

        $topicId = $var;
        $oMbqEtPoll = MbqMain::$oClk->newObj('MbqEtPoll');
        $oMbqEtPoll->topicId->setOriValue($topicId);

        $sql = "SELECT * FROM " . TOPICS_TABLE . " WHERE topic_id = '$topicId'";
        $result = $db->sql_query($sql);
        $topic = $db->sql_fetchrow($result);

        $oMbqEtPoll->pollTitle->setOriValue($topic['poll_title']);
        $oMbqEtPoll->pollLength->setOriValue($topic['poll_length']);
        $oMbqEtPoll->pollMaxOptions->setOriValue($topic['poll_max_options']);
        $oMbqEtPoll->canRevoting->setOriValue($topic['poll_vote_change']);

        $pollOptions = array();
        $sql = "SELECT * FROM " . POLL_OPTIONS_TABLE . " WHERE topic_id = '$topicId'";
        $result = $db->sql_query($sql);
        while ($pollOption = $db->sql_fetchrow($result)) {
            $pollOptions[] = array(
                'id' => $pollOption['poll_option_id'],
                'text' => $pollOption['poll_option_text'],
                'vote_count' => $pollOption['poll_option_total'],
            );
        }
        $oMbqEtPoll->pollOptions->setOriValue($pollOptions);

        $myVotes = array();
        $userId = isset($user->data['user_id']) ? intval($user->data['user_id']) : 0;
        if (!empty($userId)) {
            $sql = "SELECT * FROM " . POLL_VOTES_TABLE . " WHERE topic_id = '$topicId' AND vote_user_id = '$userId'";
            $result = $db->sql_query($sql);
            while ($vote = $db->sql_fetchrow($result)) {
                $myVotes[] = $vote['poll_option_id'];
            }
        }
        $oMbqEtPoll->myVotes->setOriValue($myVotes);
        
        return $oMbqEtPoll;
    }
}
