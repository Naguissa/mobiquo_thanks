<?php

defined('MBQ_IN_IT') or exit;

/**
 * forum read class
 */
Abstract Class MbqBaseRdEtForum extends MbqBaseRd {

    public function __construct() {
    }

    /**
     * return forum api data
     *
     * @param  Object  $oMbqEtForum
     * @return  Array
     */
    public function returnApiDataForum($oMbqEtForum) {
        if (MbqMain::isJsonProtocol()) return $this->returnJsonApiDataForum($oMbqEtForum);
        $data = array();
        if ($oMbqEtForum->forumId->hasSetOriValue()) {
            $data['forum_id'] = (string) $oMbqEtForum->forumId->oriValue;
        }
        if ($oMbqEtForum->forumName->hasSetOriValue()) {
            $data['forum_name'] = (string) $oMbqEtForum->forumName->oriValue;
        }
        if ($oMbqEtForum->description->hasSetOriValue()) {
            $data['description'] = (string) $oMbqEtForum->description->oriValue;
        }
        if ($oMbqEtForum->totalTopicNum->hasSetOriValue()) {
            $data['total_topic_num'] = (int) $oMbqEtForum->totalTopicNum->oriValue;
        }
        if ($oMbqEtForum->parentId->hasSetOriValue()) {
            $data['parent_id'] = (string) $oMbqEtForum->parentId->oriValue;
        }
        if ($oMbqEtForum->logoUrl->hasSetOriValue()) {
            $data['logo_url'] = (string) $oMbqEtForum->logoUrl->oriValue;
        }
        if ($oMbqEtForum->newPost->hasSetOriValue()) {
            $data['new_post'] = (boolean) $oMbqEtForum->newPost->oriValue;
        }
        if ($oMbqEtForum->isProtected->hasSetOriValue()) {
            $data['is_protected'] = (boolean) $oMbqEtForum->isProtected->oriValue;
        }
        if ($oMbqEtForum->isSubscribed->hasSetOriValue()) {
            $data['is_subscribed'] = (boolean) $oMbqEtForum->isSubscribed->oriValue;
            if($oMbqEtForum->subscriptionEmail->hasSetOriValue())
            {
                $data['subscription_email'] = (boolean) $oMbqEtForum->subscriptionEmail->oriValue;
            }
        }
        if ($oMbqEtForum->canSubscribe->hasSetOriValue()) {
            $data['can_subscribe'] = (boolean) $oMbqEtForum->canSubscribe->oriValue;
        } else {
            $data['can_subscribe'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canSubscribe.default');
        }
        if ($oMbqEtForum->url->hasSetOriValue()) {
            $data['url'] = (string) $oMbqEtForum->url->oriValue;
        }
        if ($oMbqEtForum->subOnly->hasSetOriValue()) {
            $data['sub_only'] = (boolean) $oMbqEtForum->subOnly->oriValue;
        }
        if ($oMbqEtForum->canPost->hasSetOriValue()) {
            $data['can_post'] = (boolean) $oMbqEtForum->canPost->oriValue;
        } else {
            $data['can_post'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canPost.default');
        }
        if ($oMbqEtForum->unreadStickyCount->hasSetOriValue()) {
            $data['unread_sticky_count'] = (int) $oMbqEtForum->unreadStickyCount->oriValue;
        }
        if ($oMbqEtForum->unreadAnnounceCount->hasSetOriValue()) {
            $data['unread_announce_count'] = (int) $oMbqEtForum->unreadAnnounceCount->oriValue;
        }
        if ($oMbqEtForum->requirePrefix->hasSetOriValue()) {
            $data['require_prefix'] = (boolean) $oMbqEtForum->requirePrefix->oriValue;
        }
        if ($oMbqEtForum->prefixes->hasSetOriValue()) {
            $tempArr = array();
            foreach ($oMbqEtForum->prefixes->oriValue as $prefix) {
                $tempArr[] = array('prefix_id' => (string) $prefix['id'], 'prefix_display_name' => (string) $prefix['name']);
            }
            $data['prefixes'] = (array) $tempArr;
        }
        if ($oMbqEtForum->canUpload->hasSetOriValue()) {
            $data['can_upload'] = (boolean) $oMbqEtForum->canUpload->oriValue;
        } else {
            $data['can_upload'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canUpload.default');
        }
        if ($oMbqEtForum->canCreatePoll->hasSetOriValue()) {
            $data['can_create_poll'] = (boolean) $oMbqEtForum->canCreatePoll->oriValue;
        } else {
            $data['can_create_poll'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canCreatePoll.default');
        }
        $data['child'] = array();
        $this->recurMakeApiTreeDataForum($data['child'], $oMbqEtForum->objsSubMbqEtForum);
        return $data;
    }
    public function returnJsonApiDataForum($oMbqEtForum) {
        $data = array();
        if ($oMbqEtForum->forumId->hasSetOriValue()) {
            $data['forum_id'] = (string) $oMbqEtForum->forumId->oriValue;
        }
        if ($oMbqEtForum->forumName->hasSetOriValue()) {
            $data['forum_name'] = (string) $oMbqEtForum->forumName->oriValue;
        }
        if ($oMbqEtForum->description->hasSetOriValue()) {
            $data['description'] = (string) $oMbqEtForum->description->oriValue;
        }
        if ($oMbqEtForum->totalTopicNum->hasSetOriValue()) {
            $data['total_topic_num'] = (int) $oMbqEtForum->totalTopicNum->oriValue;
        }
        if ($oMbqEtForum->parentId->hasSetOriValue()) {
            $data['parent_id'] = (string) $oMbqEtForum->parentId->oriValue;
        }
        if ($oMbqEtForum->logoUrl->hasSetOriValue()) {
            $data['logo_url'] = (string) $oMbqEtForum->logoUrl->oriValue;
        }
        if ($oMbqEtForum->newPost->hasSetOriValue()) {
            $data['new_post'] = (boolean) $oMbqEtForum->newPost->oriValue;
        }
        if ($oMbqEtForum->isProtected->hasSetOriValue()) {
            $data['is_protected'] = (boolean) $oMbqEtForum->isProtected->oriValue;
        }
        if ($oMbqEtForum->isSubscribed->hasSetOriValue()) {
            $data['is_subscribed'] = (boolean) $oMbqEtForum->isSubscribed->oriValue;
            if($oMbqEtForum->subscriptionEmail->hasSetOriValue())
            {
                $data['subscription_email'] = (boolean) $oMbqEtForum->subscriptionEmail->oriValue;
            }
        }
        if ($oMbqEtForum->canSubscribe->hasSetOriValue()) {
            $data['can_subscribe'] = (boolean) $oMbqEtForum->canSubscribe->oriValue;
        } else {
            $data['can_subscribe'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canSubscribe.default');
        }
        if ($oMbqEtForum->url->hasSetOriValue()) {
            $data['url'] = (string) $oMbqEtForum->url->oriValue;
        }
        if ($oMbqEtForum->subOnly->hasSetOriValue()) {
            $data['sub_only'] = (boolean) $oMbqEtForum->subOnly->oriValue;
        }
        if ($oMbqEtForum->canPost->hasSetOriValue()) {
            $data['can_post'] = (boolean) $oMbqEtForum->canPost->oriValue;
        } else {
            $data['can_post'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canPost.default');
        }
        if ($oMbqEtForum->unreadStickyCount->hasSetOriValue()) {
            $data['unread_sticky_count'] = (int) $oMbqEtForum->unreadStickyCount->oriValue;
        }
        if ($oMbqEtForum->unreadAnnounceCount->hasSetOriValue()) {
            $data['unread_announce_count'] = (int) $oMbqEtForum->unreadAnnounceCount->oriValue;
        }
        if ($oMbqEtForum->requirePrefix->hasSetOriValue()) {
            $data['require_prefix'] = (boolean) $oMbqEtForum->requirePrefix->oriValue;
        }
        if ($oMbqEtForum->prefixes->hasSetOriValue()) {
            $tempArr = array();
            foreach ($oMbqEtForum->prefixes->oriValue as $prefix) {
                $tempArr[] = array('prefix_id' => (string) $prefix['id'], 'prefix_display_name' => (string) $prefix['name']);
            }
            $data['prefixes'] = (array) $tempArr;
        }
        if ($oMbqEtForum->canUpload->hasSetOriValue()) {
            $data['can_upload'] = (boolean) $oMbqEtForum->canUpload->oriValue;
        } else {
            $data['can_upload'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canUpload.default');
        }
        $data['child'] = array();
        $this->recurMakeApiTreeDataForum($data['child'], $oMbqEtForum->objsSubMbqEtForum);
        return $data;
    }
 

    /**
     * recur make forum tree api data
     *
     * @param  Array  $dataChild
     * @param  Array  $objsSubMbqEtForum
     */
    protected function recurMakeApiTreeDataForum(&$dataChild, $objsSubMbqEtForum) {
        $j = 0;
        foreach ($objsSubMbqEtForum as $oMbqEtForum) {
            $dataChild[$j] = $this->returnApiDataForum($oMbqEtForum);
            $j ++;
        }
    }

    /**
     * return forum tree api data
     *
     * @param  Array  $tree  forum tree
     * @return  Array
     */
    public function returnApiTreeDataForum($tree) {
        $data = array();
        $i = 0;
        foreach ($tree as $oMbqEtForum) {
            $data[$i] = $this->returnApiDataForum($oMbqEtForum);
            $i ++;
        }
        return $data;
    }

    /**
     * get forum tree structure
     *
     * @return  Array
     */
    public function getForumTree($returnDescription, $forumId) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * get forum objs
     *
     * @return  Array
     */
    public function getObjsMbqEtForum($var, $mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * init one forum by condition
     *
     * @return  Mixed
     */
    public function initOMbqEtForum($var, $mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * get breadcrumb forums
     *
     * @return Array
     */
    public function getObjsBreadcrumbMbqEtForum() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
    * get sub forums in a special forum
    *
    * @return Array
    */
    public function getObjsSubMbqEtForum() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * login forum
     *
     * @return Array
     */
    public function loginForum($oMbqEtForum, $password) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    /**
     * This function should return the real url of the forum following any seo rules forum have
     *
     * @param mixed $oMbqEtForum
     */
    public function getUrl($oMbqEtForum)
    {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
}
