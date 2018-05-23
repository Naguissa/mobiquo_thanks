<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrEtPoll');

/**
 * poll write class
 */
Class MbqWrEtPoll extends MbqBaseWrEtPoll {

    public function __construct() {
    }

    /**
     * vote
     */
    public function vote($oMbqEtPoll) {
        global $db, $user;

        $topic_id = $oMbqEtPoll->topicId->oriValue;
        $voted_id = $oMbqEtPoll->voteOptions->oriValue;
        $poll_max_options = $oMbqEtPoll->pollMaxOptions->oriValue;

        if (!sizeof($voted_id) || sizeof($voted_id) > $poll_max_options || in_array(VOTE_CONVERTED, $cur_voted_id))
        {
            if (!sizeof($voted_id))
            {
                $message = 'NO_VOTE_OPTION';
            }
            else if (sizeof($voted_id) > $poll_max_options)
            {
                $message = 'TOO_MANY_VOTE_OPTIONS';
            }
            else if (in_array(VOTE_CONVERTED, $cur_voted_id))
            {
                $message = 'VOTE_CONVERTED';
            }
            if (!empty($message)) return $message;
        }

        $sql = 'SELECT poll_option_id
            FROM ' . POLL_VOTES_TABLE . '
            WHERE topic_id = ' . $topic_id . '
                AND vote_user_id = ' . $user->data['user_id'];
        $result = $db->sql_query($sql);

        while ($row = $db->sql_fetchrow($result))
        {
            $cur_voted_id[] = $row['poll_option_id'];
        }
        $db->sql_freeresult($result);

        foreach ($voted_id as $option)
        {
            if (in_array($option, $cur_voted_id))
            {
                continue;
            }

            $sql = 'UPDATE ' . POLL_OPTIONS_TABLE . '
                SET poll_option_total = poll_option_total + 1
                WHERE poll_option_id = ' . (int) $option . '
                    AND topic_id = ' . (int) $topic_id;
            $db->sql_query($sql);

            if ($user->data['is_registered'])
            {
                $sql_ary = array(
                    'topic_id'          => (int) $topic_id,
                    'poll_option_id'    => (int) $option,
                    'vote_user_id'      => (int) $user->data['user_id'],
                    'vote_user_ip'      => (string) $user->ip,
                );

                $sql = 'INSERT INTO ' . POLL_VOTES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
                $db->sql_query($sql);
            }
        }

        foreach ($cur_voted_id as $option)
        {
            if (!in_array($option, $voted_id))
            {
                $sql = 'UPDATE ' . POLL_OPTIONS_TABLE . '
                    SET poll_option_total = poll_option_total - 1
                    WHERE poll_option_id = ' . (int) $option . '
                        AND topic_id = ' . (int) $topic_id;
                $db->sql_query($sql);

                if ($user->data['is_registered'])
                {
                    $sql = 'DELETE FROM ' . POLL_VOTES_TABLE . '
                        WHERE topic_id = ' . (int) $topic_id . '
                            AND poll_option_id = ' . (int) $option . '
                            AND vote_user_id = ' . (int) $user->data['user_id'];
                    $db->sql_query($sql);
                }
            }
        }

        if ($user->data['user_id'] == ANONYMOUS && !$user->data['is_bot'])
        {
            $user->set_cookie('poll_' . $topic_id, implode(',', $voted_id), time() + 31536000);
        }

        $sql = 'UPDATE ' . TOPICS_TABLE . '
            SET poll_last_vote = ' . time() . "
            WHERE topic_id = $topic_id";
        //, topic_last_post_time = ' . time() . " -- for bumping topics with new votes, ignore for now
        $db->sql_query($sql);

        return true;
    }

    /**
     * edit_poll
     */
    public function editPoll($oMbqEtPoll) {
        global $db;

        $topic_id = $oMbqEtPoll->topicId->oriValue;
        $poll_title = $oMbqEtPoll->pollTitle->oriValue;
        $poll_length =  $oMbqEtPoll->pollLength->oriValue;

        $poll_options = $oMbqEtPoll->pollOptions->oriValue;
        $new_options = $oMbqEtPoll->newOptions->oriValue;
        
        // if (isset($in->canViewBeforeVote)) $oMbqEtPoll->canViewBeforeVote->setOriValue($in->canViewBeforeVote);
        // if (isset($in->canPublic)) $oMbqEtPoll->canPublic->setOriValue($in->canPublic);

        $topic_sql = array(
            'poll_title' => $poll_title,
            'poll_length' => $poll_length,
        );
        if ($oMbqEtPoll->pollMaxOptions->hasSetOriValue()) $topic_sql['poll_max_options'] = $oMbqEtPoll->pollMaxOptions->oriValue;
        if ($oMbqEtPoll->canRevoting->hasSetOriValue()) $topic_sql['poll_vote_change'] = $oMbqEtPoll->canRevoting->oriValue;

        $sql = 'UPDATE ' . TOPICS_TABLE . '
            SET ' . $db->sql_build_array('UPDATE', $topic_sql) . "
            WHERE topic_id = $topic_id";
        $db->sql_query($sql);

        // logic from function_posting.php
        $sql = "SELECT *
            FROM " . POLL_OPTIONS_TABLE . '
            WHERE topic_id = ' . $topic_id . '
            ORDER BY poll_option_id';
        $result = $db->sql_query($sql);
        $cur_poll_options = array();
        while ($row = $db->sql_fetchrow($result))
        {
            $cur_poll_options[] = $row;
        }

        $options = array();
        foreach ($poll_options as $option)
        {
            if (!empty($option['text']))
            {
                $options[] = $option['text'];
            }
        }
        if (!empty($new_options))
        {
            foreach ($new_options as $option)
            {
                $options[] = $option;
            }
        }

        for ($i = 0, $size = sizeof($options); $i < $size; $i++)
        {
            if (strlen(trim($options[$i])))
            {
                if (empty($cur_poll_options[$i]))
                {
                    $sql_insert_ary[] = array(
                        'poll_option_id'     => (int) sizeof($cur_poll_options) + 1 + sizeof($sql_insert_ary),
                        'topic_id'         => (int) $topic_id,
                        'poll_option_text' => (string) $options[$i],
                    );
                }
                else if ($options[$i] != $cur_poll_options[$i])
                {
                    $sql = 'UPDATE ' . POLL_OPTIONS_TABLE . "
                        SET poll_option_text = '" . $db->sql_escape($options[$i]) . "'
                        WHERE poll_option_id = " . $cur_poll_options[$i]['poll_option_id'] . '
                            AND topic_id = ' . $topic_id;
                    $db->sql_query($sql);
                }
            }
        }

        $db->sql_multi_insert(POLL_OPTIONS_TABLE, $sql_insert_ary);

        if (sizeof($options) < sizeof($cur_poll_options))
        {
            $sql = 'DELETE FROM ' . POLL_OPTIONS_TABLE . '
                WHERE poll_option_id > ' . sizeof($options) . '
                    AND topic_id = ' . $topic_id;
            $db->sql_query($sql);
        }

        if (sizeof($options) != sizeof($cur_poll_options))
        {
            $db->sql_query('DELETE FROM ' . POLL_VOTES_TABLE . ' WHERE topic_id = ' . $topic_id);
            $db->sql_query('UPDATE ' . POLL_OPTIONS_TABLE . ' SET poll_option_total = 0 WHERE topic_id = ' . $topic_id);
        }

        return true;
    }
}