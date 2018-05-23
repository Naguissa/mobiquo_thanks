<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetConfig');

/**
 * get_config action
 */
Class MbqActGetConfig extends MbqBaseActGetConfig {

    public function __construct() {
        parent::__construct();
    }

    /**
     * action implement
     */
    public function actionImplement($in) {
        global $config, $cache;

        parent::actionImplement($in);


        $this->setCustomConfig('ban_expires', 1);
        $this->setCustomConfig('ban_delete_type', 'hard_delete');
        $this->setCustomConfig('sso_activate', 1);
        $this->setCustomConfig('api_key', md5($config['tapatalk_push_key']));
        $this->setCustomConfig('login_type', 'username');
        $this->setCustomConfig('advance_register', 1);
        $this->setCustomconfig('close_report',1);
        $this->setCustomconfig('get_contact',1);
        $this->setCustomconfig('emoji_support',1);
        $this->setCustomconfig('stats',array(
            'topic'  => $config['num_topics'],
            'user'   => $config['num_users'],
            'post'   => $config['num_posts'],
            'active' => $config['record_online_users'],
        ));
        $this->setCustomconfig('started_by',1);
        $this->setCustomconfig('poll_options_max_count', $config['max_poll_options']);
        $this->setCustomconfig('get_member_list', 1);
        $this->setCustomconfig('m_get_inactive_users', 1);
        $this->setCustomconfig('m_approve_user', 1);
        $this->setCustomconfig('guest_reply_post', 1);
        $this->setCustomconfig('guest_new_topic', 1);
        $this->setCustomconfig('unban', 1);
    }

    public function setCustomConfig($name, $value)
    {
        $this->data[$name] = $value;
    }

}
