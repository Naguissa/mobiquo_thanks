<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdCommon');

Class MbqRdCommon extends MbqBaseRdCommon {

    public function __construct() {
    }

    public function getApiKey()
    {
        global $config;
        return $config['tapatalk_push_key'];
    }
    public function getForumUrl()
    {
        global $phpbb_home;
        return $phpbb_home;
    }
    public function getCheckSpam()
    {
        global $config;
        return $config['tapatalk_spam_status'];
    }
    public function get_id_by_url($url)
    {
        global $phpbb_home;

        if (strpos($url, $phpbb_home) === 0)
        {
            $path = '/' . substr($url, strlen($phpbb_home));
            $fid = $tid = $pid = null;

            // get forum id
            if (preg_match('/(\?|&|;)(f|fid|board)=(\d+)(\W|$)/', $path, $match)) {
                $fid = $match['3'];
            } elseif (preg_match('/\W(f|forum)-?(\d+)(\W|$)/', $path, $match)) {
                $fid = $match['2'];
            } elseif (preg_match('/\/forum\/(\d+)-(\w|-)+(\W|$)/', $path, $match)) {
                $fid = $match['1'];
                $path = str_replace($match[0], $match[3], $path);
            } elseif (preg_match('/forumdisplay\.php(\?|\/)(\d+)(\W|$)/', $path, $match)) {
                $fid = $match['2'];
                $path = str_replace($match[0], $match[3], $path);
            } elseif (preg_match('/(index\.php\?|\/)forums\/.+\.(\d+)/', $path, $match)) {
                $fid = $match['2'];
            }

            // get topic id
            if (preg_match('/(\?|&|;)(t|tid|topic)=(\d+)(\W|$)/', $path, $match)) {
                $tid = $match['3'];
            } elseif (preg_match('/\W(t|(\w|-)+-t_|topic|article)-?(\d+)(\W|$)/', $path, $match)) {
                $tid = $match['3'];
            } elseif (preg_match('/showthread\.php(\?|\/)(\d+)(\W|$)/', $path, $match)) {
                $tid = $match['2'];
            } elseif (preg_match('/(\?|\/)(\d+)-(\w|-)+(\.|\/|$)/', $path, $match)) {
                $tid = $match['2'];
            } elseif (preg_match('/(\?|\/)(\w|-)+-(\d+)(\.|\/|$)/', $path, $match)) {
                $tid = $match['3'];
            } elseif (preg_match('/(index\.php\?|\/)threads\/.+\.(\d+)/', $path, $match)) {
                $tid = $match['2'];
            }

            // get post id
            if (preg_match('/(\?|&|;)(p|pid)=(\d+)(\W|$)/', $path, $match)) {
                $pid = $match['3'];
            } elseif (preg_match('/\W(p|(\w|-)+-p|post|msg)(-|_)?(\d+)(\W|$)/', $path, $match)) {
                $pid = $match['4'];
            } elseif (preg_match('/__p__(\d+)(\W|$)/', $path, $match)) {
                $pid = $match['1'];
            }
            if(!empty($pid) && preg_match('/i=pm/', $path, $match))
            {
                $oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm');
                return $oMbqRdEtPm->initOMbqEtPm($pid, array('case'=>'byMsgId'));
            }
            if (!empty($pid))
            {
                $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                return $oMbqRdEtForumPost->initOMbqEtForumPost($pid, array('case'=>'byPostId'));
            }
            if (!empty($tid))
            {
                $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                return $oMbqRdEtForumTopic->initOMbqEtForumTopic($tid, array('case'=>'byTopicId'));
            }
            if (!empty($fid))
            {
                $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
                return $oMbqRdEtForum->initOMbqEtForum($fid, array('case'=>'byForumId'));
            }
        }
        return null;
    }
    public function getPushSlug()
    {
        global $config;
        $slug = $config['tapatalk_push_slug'];
        if(isset($slug))
        {
            return unserialize($slug);
        }
        return null;
    }
    public function getSmartbannerInfo()
    {
        global $cache;
        $tapatalkBannerControl = getTapatalkConfigValue('tapatalk_banner_control');
        if(isset($tapatalkBannerControl))
        {
            return unserialize($tapatalkBannerControl);
        }
        return null;
    }
}
