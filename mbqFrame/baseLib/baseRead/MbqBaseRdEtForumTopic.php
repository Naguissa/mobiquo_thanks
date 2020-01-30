<?php

defined('MBQ_IN_IT') or exit;

/**
 * forum topic read class
 */
Abstract Class MbqBaseRdEtForumTopic extends MbqBaseRd {

    public function __construct() {
    }

    /**
     * return forum topic api data
     *
     * @param  Object  $oMbqEtForumTopic
     * @return  Array
     */
    public function returnApiDataForumTopic($oMbqEtForumTopic) {
        if (MbqMain::isJsonProtocol()) return $this->returnJsonApiDataForumTopic($oMbqEtForumTopic);
        $data = array();
        if ($oMbqEtForumTopic->totalPostNum->hasSetOriValue()) {
            $data['total_post_num'] = (int) $oMbqEtForumTopic->totalPostNum->oriValue;
        }
        if ($oMbqEtForumTopic->topicId->hasSetOriValue()) {
            $data['topic_id'] = (string) $oMbqEtForumTopic->topicId->oriValue;
        }
        if ($oMbqEtForumTopic->forumId->hasSetOriValue()) {
            $data['forum_id'] = (string) $oMbqEtForumTopic->forumId->oriValue;
        }
        if ($oMbqEtForumTopic->oMbqEtForum) {
            $data['forum_name'] = (string) $oMbqEtForumTopic->oMbqEtForum->forumName->oriValue;
        }
        if ($oMbqEtForumTopic->topicTitle->hasSetOriValue()) {
            $data['topic_title'] = (string) $oMbqEtForumTopic->topicTitle->oriValue;
        }
        if ($oMbqEtForumTopic->prefixId->hasSetOriValue()) {
            $data['prefix_id'] = (string) $oMbqEtForumTopic->prefixId->oriValue;
        }
        if ($oMbqEtForumTopic->prefixName->hasSetOriValue()) {
            $data['prefix'] = (string) $oMbqEtForumTopic->prefixName->oriValue;
        }

        if ($oMbqEtForumTopic->attachmentIdArray->hasSetOriValue()) {
            $data['attachment_id_array'] = (array) $oMbqEtForumTopic->attachmentIdArray->oriValue;
        }
        if ($oMbqEtForumTopic->groupId->hasSetOriValue()) {
            $data['group_id'] = (string) $oMbqEtForumTopic->groupId->oriValue;
        }
        if ($oMbqEtForumTopic->state->hasSetOriValue()) {
            $data['state'] = (int) $oMbqEtForumTopic->state->oriValue;
        }
        if ($oMbqEtForumTopic->isSubscribed->hasSetOriValue()) {
            $data['is_subscribed'] = (boolean) $oMbqEtForumTopic->isSubscribed->oriValue;
            if($oMbqEtForumTopic->subscriptionEmail->hasSetOriValue())
            {
                $data['subscription_email'] = (boolean) $oMbqEtForumTopic->subscriptionEmail->oriValue;
            }
        }
        if ($oMbqEtForumTopic->canSubscribe->hasSetOriValue()) {
            $data['can_subscribe'] = (boolean) $oMbqEtForumTopic->canSubscribe->oriValue;
        } else {
            $data['can_subscribe'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canSubscribe.default');
        }
        if ($oMbqEtForumTopic->isClosed->hasSetOriValue()) {
            $data['is_closed'] = (boolean) $oMbqEtForumTopic->isClosed->oriValue;
        }
        if($oMbqEtForumTopic->readTimestamp->hasSetOriValue())
        {
            $data['read_timestamp'] = MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtForumTopic->readTimestamp->oriValue);
        }

        $methodNotNeedTopicAuthor = array('get_topic','get_user_topic','get_thread', 'get_thread_by_post','get_thread_by_unread');
        $data['short_content'] = (string) $oMbqEtForumTopic->shortContent->oriValue;
        if ($oMbqEtForumTopic->postTime->hasSetOriValue()) {
            $data['post_time'] = (string) MbqMain::$oMbqCm->datetimeIso8601Encode($oMbqEtForumTopic->postTime->oriValue);
            $data['timestamp'] = (string)MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtForumTopic->postTime->oriValue);
        }
        if($oMbqEtForumTopic->oAuthorMbqEtUser != null && $oMbqEtForumTopic->oAuthorMbqEtUser instanceof MbqEtUser)
        {
            if( $oMbqEtForumTopic->oAuthorMbqEtUser->iconUrl->hasSetOriValue())
            {
                $data['icon_url'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->iconUrl->oriValue;
            }
            if(in_array(MbqMain::$cmd,$methodNotNeedTopicAuthor)) {
                $data['topic_author_name'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->getDisplayName();
                $data['topic_author_id'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->userId->oriValue;
                if ($oMbqEtForumTopic->oAuthorMbqEtUser->userType->hasSetOriValue()) {
                    $data['topic_author_user_type'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->userType->oriValue;
                }
                if ($oMbqEtForumTopic->oAuthorMbqEtUser->isIgnored->hasSetOriValue()) {
                    $data['topic_author_is_ignored'] = (bool) $oMbqEtForumTopic->oAuthorMbqEtUser->isIgnored->oriValue;
                }
            }
            $data['post_author_name'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->getDisplayName();
            $data['post_author_id'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->userId->oriValue;
            if ($oMbqEtForumTopic->oAuthorMbqEtUser->userType->hasSetOriValue()) {
                $data['post_author_user_type'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->userType->oriValue;
            }
            if ($oMbqEtForumTopic->oAuthorMbqEtUser->isIgnored->hasSetOriValue()) {
                $data['post_author_is_ignored'] = (bool) $oMbqEtForumTopic->oAuthorMbqEtUser->isIgnored->oriValue;
            }

            $data['first_post_author_name'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->getDisplayName();
            $data['first_post_author_id'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->userId->oriValue;
            if( $oMbqEtForumTopic->oAuthorMbqEtUser->iconUrl->hasSetOriValue())
            {
                $data['first_post_icon_url'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->iconUrl->oriValue;
            }
            if ($oMbqEtForumTopic->oAuthorMbqEtUser->userType->hasSetOriValue()) {
                $data['first_post_author_user_type'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->userType->oriValue;
            }
            if ($oMbqEtForumTopic->oAuthorMbqEtUser->isIgnored->hasSetOriValue()) {
                $data['first_post_author_is_ignored'] = (bool) $oMbqEtForumTopic->oAuthorMbqEtUser->isIgnored->oriValue;
            }
        }
        else
        {
               if(in_array(MbqMain::$cmd,$methodNotNeedTopicAuthor)) {
                   $data['topic_author_id'] = (string) $oMbqEtForumTopic->topicAuthorId->oriValue;
                   $data['post_author_name'] = 'Unknown';
               }
               $data['post_author_id'] = (string) $oMbqEtForumTopic->topicAuthorId->oriValue;
               $data['post_author_name'] = 'Unknown';
               $data['icon_url'] = '';
        }
        if ($oMbqEtForumTopic->oLastReplyMbqEtUser != null && $oMbqEtForumTopic->oLastReplyMbqEtUser instanceof MbqEtUser) {
            if(!in_array(MbqMain::$cmd,$methodNotNeedTopicAuthor)) {
                if( $oMbqEtForumTopic->oLastReplyMbqEtUser->iconUrl->hasSetOriValue())
                {
                    $data['icon_url'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->iconUrl->oriValue;
                }
                $data['post_author_name'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->getDisplayName();
                $data['post_author_id'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->userId->oriValue;
                if ($oMbqEtForumTopic->oLastReplyMbqEtUser->userType->hasSetOriValue()) {
                    $data['post_author_user_type'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->userType->oriValue;
                }
                if ($oMbqEtForumTopic->oLastReplyMbqEtUser->isIgnored->hasSetOriValue()) {
                    $data['post_author_is_ignored'] = (bool) $oMbqEtForumTopic->oLastReplyMbqEtUser->isIgnored->oriValue;
                }
                if(isset($oMbqEtForumTopic->oLastMbqEtForumPost))
                {
                    $data['short_content'] = (string) $oMbqEtForumTopic->oLastMbqEtForumPost->shortContent->oriValue;
                }
                else if($oMbqEtForumTopic->lastPostShortContent->hasSetOriValue())
                {
                    $data['short_content'] = (string) $oMbqEtForumTopic->lastPostShortContent->oriValue;
                }
            }
            if ($oMbqEtForumTopic->lastReplyTime->hasSetOriValue()) {
                $data['post_time'] = (string) MbqMain::$oMbqCm->datetimeIso8601Encode($oMbqEtForumTopic->lastReplyTime->oriValue);
                $data['timestamp'] = (string)MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtForumTopic->lastReplyTime->oriValue);
            }
            $data['last_reply_author_name'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->getDisplayName();
            $data['last_reply_author_id'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->userId->oriValue;
            if( $oMbqEtForumTopic->oLastReplyMbqEtUser->iconUrl->hasSetOriValue())
            {
                $data['last_reply_icon_url'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->iconUrl->oriValue;
            }
            if ($oMbqEtForumTopic->oLastReplyMbqEtUser->userType->hasSetOriValue()) {
                $data['last_reply_author_user_type'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->userType->oriValue;
            }
            if ($oMbqEtForumTopic->oLastReplyMbqEtUser->isIgnored->hasSetOriValue()) {
                $data['last_reply_author_is_ignored'] = (bool) $oMbqEtForumTopic->oLastReplyMbqEtUser->isIgnored->oriValue;
            }
        }
        else
        {
            if(!in_array(MbqMain::$cmd,$methodNotNeedTopicAuthor)) {
                if(isset($oMbqEtForumTopic->oLastMbqEtForumPost))
                {
                    $data['short_content'] = (string) $oMbqEtForumTopic->oLastMbqEtForumPost->shortContent->oriValue;
                }
                else if($oMbqEtForumTopic->lastPostShortContent->hasSetOriValue())
                {
                    $data['short_content'] = (string) $oMbqEtForumTopic->lastPostShortContent->oriValue;
                }
            }
            if ($oMbqEtForumTopic->lastReplyTime->hasSetOriValue()) {
                $data['post_time'] = (string) MbqMain::$oMbqCm->datetimeIso8601Encode($oMbqEtForumTopic->lastReplyTime->oriValue);
                $data['timestamp'] = (string)MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtForumTopic->lastReplyTime->oriValue);
            }
            $data['last_reply_author_id'] = (string) $oMbqEtForumTopic->lastReplyAuthorId->oriValue;
            $data['last_reply_author_name'] = 'Unknown';
        }

        if ($oMbqEtForumTopic->replyNumber->hasSetOriValue()) {
            $data['reply_number'] = (int) $oMbqEtForumTopic->replyNumber->oriValue;
        }
        if ($oMbqEtForumTopic->newPost->hasSetOriValue()) {
            $data['new_post'] = (boolean) $oMbqEtForumTopic->newPost->oriValue;
        }
        if ($oMbqEtForumTopic->viewNumber->hasSetOriValue()) {
            $data['view_number'] = (int) $oMbqEtForumTopic->viewNumber->oriValue;
        }
        if ($oMbqEtForumTopic->participatedUids->hasSetOriValue()) {
            $data['participated_uids'] = (array) MbqMain::$oMbqCm->changeArrValueToString($oMbqEtForumTopic->participatedUids->oriValue);
        }
        if ($oMbqEtForumTopic->canThank->hasSetOriValue()) {
            $data['can_thank'] = (boolean) $oMbqEtForumTopic->canThank->oriValue;
        } else {
            $data['can_thank'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canThank.default');
        }
        if ($oMbqEtForumTopic->thankCount->hasSetOriValue()) {
            $data['thank_count'] = (int) $oMbqEtForumTopic->thankCount->oriValue;
        }
        if ($oMbqEtForumTopic->canLike->hasSetOriValue()) {
            $data['can_like'] = (boolean) $oMbqEtForumTopic->canLike->oriValue;
        } else {
            $data['can_like'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canLike.default');
        }
        if ($oMbqEtForumTopic->isLiked->hasSetOriValue()) {
            $data['is_liked'] = (boolean) $oMbqEtForumTopic->isLiked->oriValue;
        }
        if ($oMbqEtForumTopic->likeCount->hasSetOriValue()) {
            $data['like_count'] = (int) $oMbqEtForumTopic->likeCount->oriValue;
        }
        if ($oMbqEtForumTopic->participatedIn->hasSetOriValue()) {
            $data['participated_in'] = (boolean) $oMbqEtForumTopic->participatedIn->oriValue;
        }
        if ($oMbqEtForumTopic->canDelete->hasSetOriValue()) {
            $data['can_delete'] = (boolean) $oMbqEtForumTopic->canDelete->oriValue;
        } else {
            $data['can_delete'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canDelete.default');
        }
        if ($oMbqEtForumTopic->isDeleted->hasSetOriValue()) {
            $data['is_deleted'] = (boolean) $oMbqEtForumTopic->isDeleted->oriValue;
        }
        if ($oMbqEtForumTopic->canApprove->hasSetOriValue()) {
            $data['can_approve'] = (boolean) $oMbqEtForumTopic->canApprove->oriValue;
        } else {
            $data['can_approve'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canApprove.default');
        }
        if ($oMbqEtForumTopic->isApproved->hasSetOriValue()) {
            $data['is_approved'] = (boolean) $oMbqEtForumTopic->isApproved->oriValue;
        }
        if ($oMbqEtForumTopic->canStick->hasSetOriValue()) {
            $data['can_stick'] = (boolean) $oMbqEtForumTopic->canStick->oriValue;
        } else {
            $data['can_stick'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canStick.default');
        }
        if ($oMbqEtForumTopic->isSticky->hasSetOriValue()) {
            $data['is_sticky'] = (boolean) $oMbqEtForumTopic->isSticky->oriValue;
        }
        if ($oMbqEtForumTopic->canClose->hasSetOriValue()) {
            $data['can_close'] = (boolean) $oMbqEtForumTopic->canClose->oriValue;
        } else {
            $data['can_close'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canClose.default');
        }
        if ($oMbqEtForumTopic->canRename->hasSetOriValue()) {
            $data['can_rename'] = (boolean) $oMbqEtForumTopic->canRename->oriValue;
        } else {
            $data['can_rename'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canRename.default');
        }
        if ($oMbqEtForumTopic->canMove->hasSetOriValue()) {
            $data['can_move'] = (boolean) $oMbqEtForumTopic->canMove->oriValue;
        } else {
            $data['can_move'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canMove.default');
        }
        if ($oMbqEtForumTopic->isMoved->hasSetOriValue()) {
            $data['is_moved'] = (boolean) $oMbqEtForumTopic->isMoved->oriValue;
            if ($data['is_moved'] && $oMbqEtForumTopic->realTopicId->hasSetOriValue()){
                $data['topic_id'] = (string) $oMbqEtForumTopic->realTopicId->oriValue;
            }
        }
        if ($oMbqEtForumTopic->canMerge->hasSetOriValue()) {
            $data['can_merge'] = (boolean) $oMbqEtForumTopic->canMerge->oriValue;
        } else {
            $data['can_merge'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canMerge.default');
        }
        if ($oMbqEtForumTopic->realTopicId->hasSetOriValue()) {
            $data['real_topic_id'] = (string) $oMbqEtForumTopic->realTopicId->oriValue;
        }
        if ($oMbqEtForumTopic->modByUserId->hasSetOriValue()) {
            $data['moderated_by_id'] = (string) $oMbqEtForumTopic->modByUserId->oriValue;
        }
        if ($oMbqEtForumTopic->deleteByUserId->hasSetOriValue()) {
            $data['deleted_by_id'] = (string) $oMbqEtForumTopic->deleteByUserId->oriValue;
        }
        if ($oMbqEtForumTopic->deleteReason->hasSetOriValue()) {
            $data['delete_reason'] = (string) $oMbqEtForumTopic->deleteReason->oriValue;
        }
        if ($oMbqEtForumTopic->canReply->hasSetOriValue()) {
            $data['can_reply'] = (boolean) $oMbqEtForumTopic->canReply->oriValue;
        } else {
            $data['can_reply'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canReply.default');
        }
        if ($oMbqEtForumTopic->canBan->hasSetOriValue()) {
            $data['can_ban'] = (boolean) $oMbqEtForumTopic->canBan->oriValue;
        } else {
            $data['can_ban'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canBan.default');
        }
        if ($oMbqEtForumTopic->isBan->hasSetOriValue()) {
            $data['is_ban'] = (boolean) $oMbqEtForumTopic->isBan->oriValue;
        }
        if ($oMbqEtForumTopic->hasPoll->hasSetOriValue()) {
            $data['has_poll'] = (boolean) $oMbqEtForumTopic->hasPoll->oriValue;
        }
        if ($oMbqEtForumTopic->oMbqEtPoll != null && $oMbqEtForumTopic->oMbqEtPoll instanceof MbqEtPoll) {
            $oMbqRdEtPoll = MbqMain::$oClk->newObj('MbqRdEtPoll');
            $data['poll'] = $oMbqRdEtPoll->returnApiDataPoll($oMbqEtForumTopic->oMbqEtPoll);
        }
        return $data;
    }
    public function returnJsonApiDataForumTopic($oMbqEtForumTopic) {
        $data = array();
        if ($oMbqEtForumTopic->totalPostNum->hasSetOriValue()) {
            $data['total_post_num'] = (int) $oMbqEtForumTopic->totalPostNum->oriValue;
        }
        if ($oMbqEtForumTopic->topicId->hasSetOriValue()) {
            $data['topic_id'] = (string) $oMbqEtForumTopic->topicId->oriValue;
        }
        if ($oMbqEtForumTopic->forumId->hasSetOriValue()) {
            $data['forum_id'] = (string) $oMbqEtForumTopic->forumId->oriValue;
        }
        if ($oMbqEtForumTopic->oMbqEtForum) {
            $data['forum_name'] = (string) $oMbqEtForumTopic->oMbqEtForum->forumName->oriValue;
        }
        if ($oMbqEtForumTopic->topicTitle->hasSetOriValue()) {
            $data['topic_title'] = (string) $oMbqEtForumTopic->topicTitle->oriValue;
        }
        if ($oMbqEtForumTopic->prefixId->hasSetOriValue()) {
            $data['prefix_id'] = (string) $oMbqEtForumTopic->prefixId->oriValue;
        }
        if ($oMbqEtForumTopic->prefixName->hasSetOriValue()) {
            $data['prefix'] = (string) $oMbqEtForumTopic->prefixName->oriValue;
        }

        if ($oMbqEtForumTopic->attachmentIdArray->hasSetOriValue()) {
            $data['attachment_id_array'] = (array) $oMbqEtForumTopic->attachmentIdArray->oriValue;
        }
        if ($oMbqEtForumTopic->groupId->hasSetOriValue()) {
            $data['group_id'] = (string) $oMbqEtForumTopic->groupId->oriValue;
        }
        if ($oMbqEtForumTopic->state->hasSetOriValue()) {
            $data['state'] = (int) $oMbqEtForumTopic->state->oriValue;
        }
        if ($oMbqEtForumTopic->isSubscribed->hasSetOriValue()) {
            $data['is_subscribed'] = (boolean) $oMbqEtForumTopic->isSubscribed->oriValue;
            if($oMbqEtForumTopic->subscriptionEmail->hasSetOriValue())
            {
                $data['subscription_email'] = (boolean) $oMbqEtForumTopic->subscriptionEmail->oriValue;
            }
        }
        if ($oMbqEtForumTopic->canSubscribe->hasSetOriValue()) {
            $data['can_subscribe'] = (boolean) $oMbqEtForumTopic->canSubscribe->oriValue;
        } else {
            $data['can_subscribe'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canSubscribe.default');
        }
        if ($oMbqEtForumTopic->isClosed->hasSetOriValue()) {
            $data['is_closed'] = (boolean) $oMbqEtForumTopic->isClosed->oriValue;
        }
        if($oMbqEtForumTopic->readTimestamp->hasSetOriValue())
        {
            $data['read_timestamp'] = MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtForumTopic->readTimestamp->oriValue);
        }

        $methodNotNeedTopicAuthor = array('get_topic','get_user_topic','get_thread', 'get_thread_by_post','get_thread_by_unread');
        $data['short_content'] = (string) $oMbqEtForumTopic->shortContent->oriValue;
        if ($oMbqEtForumTopic->postTime->hasSetOriValue()) {
            $data['post_time'] = (string) MbqMain::$oMbqCm->datetimeIso8601Encode($oMbqEtForumTopic->postTime->oriValue);
            $data['timestamp'] = (string)MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtForumTopic->postTime->oriValue);
        }
        if($oMbqEtForumTopic->oAuthorMbqEtUser != null && $oMbqEtForumTopic->oAuthorMbqEtUser instanceof MbqEtUser)
        {
            if( $oMbqEtForumTopic->oAuthorMbqEtUser->iconUrl->hasSetOriValue())
            {
                $data['icon_url'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->iconUrl->oriValue;
            }
            if(in_array(MbqMain::$cmd,$methodNotNeedTopicAuthor)) {
                $data['topic_author_name'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->getDisplayName();
                $data['topic_author_id'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->userId->oriValue;
                if ($oMbqEtForumTopic->oAuthorMbqEtUser->userType->hasSetOriValue()) {
                    $data['topic_author_user_type'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->userType->oriValue;
                }
                if ($oMbqEtForumTopic->oAuthorMbqEtUser->isIgnored->hasSetOriValue()) {
                    $data['topic_author_is_ignored'] = (bool) $oMbqEtForumTopic->oAuthorMbqEtUser->isIgnored->oriValue;
                }
            }
            $data['post_author_name'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->getDisplayName();
            $data['post_author_id'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->userId->oriValue;
            if ($oMbqEtForumTopic->oAuthorMbqEtUser->userType->hasSetOriValue()) {
                $data['post_author_user_type'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->userType->oriValue;
            }
            if ($oMbqEtForumTopic->oAuthorMbqEtUser->isIgnored->hasSetOriValue()) {
                $data['post_author_is_ignored'] = (bool) $oMbqEtForumTopic->oAuthorMbqEtUser->isIgnored->oriValue;
            }

            $data['first_post_author_name'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->getDisplayName();
            $data['first_post_author_id'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->userId->oriValue;
            if( $oMbqEtForumTopic->oAuthorMbqEtUser->iconUrl->hasSetOriValue())
            {
                $data['first_post_icon_url'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->iconUrl->oriValue;
            }
            if ($oMbqEtForumTopic->oAuthorMbqEtUser->userType->hasSetOriValue()) {
                $data['first_post_author_user_type'] = (string) $oMbqEtForumTopic->oAuthorMbqEtUser->userType->oriValue;
            }
            if ($oMbqEtForumTopic->oAuthorMbqEtUser->isIgnored->hasSetOriValue()) {
                $data['first_post_author_is_ignored'] = (bool) $oMbqEtForumTopic->oAuthorMbqEtUser->isIgnored->oriValue;
            }
        }
        else
        {
            if(in_array(MbqMain::$cmd,$methodNotNeedTopicAuthor)) {
                $data['topic_author_id'] = (string) $oMbqEtForumTopic->topicAuthorId->oriValue;
                $data['post_author_name'] = 'Unknown';
            }
            $data['post_author_id'] = (string) $oMbqEtForumTopic->topicAuthorId->oriValue;
            $data['post_author_name'] = 'Unknown';
            $data['icon_url'] = '';
        }
        if ($oMbqEtForumTopic->oLastReplyMbqEtUser != null && $oMbqEtForumTopic->oLastReplyMbqEtUser instanceof MbqEtUser) {
            if(!in_array(MbqMain::$cmd,$methodNotNeedTopicAuthor)) {
                if( $oMbqEtForumTopic->oLastReplyMbqEtUser->iconUrl->hasSetOriValue())
                {
                    $data['icon_url'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->iconUrl->oriValue;
                }
                $data['post_author_name'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->getDisplayName();
                $data['post_author_id'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->userId->oriValue;
                if ($oMbqEtForumTopic->oLastReplyMbqEtUser->userType->hasSetOriValue()) {
                    $data['post_author_user_type'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->userType->oriValue;
                }
                if ($oMbqEtForumTopic->oLastReplyMbqEtUser->isIgnored->hasSetOriValue()) {
                    $data['post_author_is_ignored'] = (bool) $oMbqEtForumTopic->oLastReplyMbqEtUser->isIgnored->oriValue;
                }
                if(isset($oMbqEtForumTopic->oLastMbqEtForumPost))
                {
                    $data['short_content'] = (string) $oMbqEtForumTopic->oLastMbqEtForumPost->shortContent->oriValue;
                }
                else if($oMbqEtForumTopic->lastPostShortContent->hasSetOriValue())
                {
                    $data['short_content'] = (string) $oMbqEtForumTopic->lastPostShortContent->oriValue;
                }
            }
            if ($oMbqEtForumTopic->lastReplyTime->hasSetOriValue()) {
                $data['post_time'] = (string) MbqMain::$oMbqCm->datetimeIso8601Encode($oMbqEtForumTopic->lastReplyTime->oriValue);
                $data['timestamp'] = (string)MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtForumTopic->lastReplyTime->oriValue);
            }
            $data['last_reply_author_name'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->getDisplayName();
            $data['last_reply_author_id'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->userId->oriValue;
            if( $oMbqEtForumTopic->oLastReplyMbqEtUser->iconUrl->hasSetOriValue())
            {
                $data['last_reply_icon_url'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->iconUrl->oriValue;
            }
            if ($oMbqEtForumTopic->oLastReplyMbqEtUser->userType->hasSetOriValue()) {
                $data['last_reply_author_user_type'] = (string) $oMbqEtForumTopic->oLastReplyMbqEtUser->userType->oriValue;
            }
            if ($oMbqEtForumTopic->oLastReplyMbqEtUser->isIgnored->hasSetOriValue()) {
                $data['last_reply_author_is_ignored'] = (bool) $oMbqEtForumTopic->oLastReplyMbqEtUser->isIgnored->oriValue;
            }
        }
        else
        {
            if(!in_array(MbqMain::$cmd,$methodNotNeedTopicAuthor)) {
                if(isset($oMbqEtForumTopic->oLastMbqEtForumPost))
                {
                    $data['short_content'] = (string) $oMbqEtForumTopic->oLastMbqEtForumPost->shortContent->oriValue;
                }
                else if($oMbqEtForumTopic->lastPostShortContent->hasSetOriValue())
                {
                    $data['short_content'] = (string) $oMbqEtForumTopic->lastPostShortContent->oriValue;
                }
            }
            if ($oMbqEtForumTopic->lastReplyTime->hasSetOriValue()) {
                $data['post_time'] = (string) MbqMain::$oMbqCm->datetimeIso8601Encode($oMbqEtForumTopic->lastReplyTime->oriValue);
                $data['timestamp'] = (string)MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtForumTopic->lastReplyTime->oriValue);
            }
            $data['last_reply_author_id'] = (string) $oMbqEtForumTopic->lastReplyAuthorId->oriValue;
            $data['last_reply_author_name'] = 'Unknown';
        }

        if ($oMbqEtForumTopic->replyNumber->hasSetOriValue()) {
            $data['reply_number'] = (int) $oMbqEtForumTopic->replyNumber->oriValue;
        }
        if ($oMbqEtForumTopic->newPost->hasSetOriValue()) {
            $data['new_post'] = (boolean) $oMbqEtForumTopic->newPost->oriValue;
        }
        if ($oMbqEtForumTopic->viewNumber->hasSetOriValue()) {
            $data['view_number'] = (int) $oMbqEtForumTopic->viewNumber->oriValue;
        }
        if ($oMbqEtForumTopic->participatedUids->hasSetOriValue()) {
            $data['participated_uids'] = (array) MbqMain::$oMbqCm->changeArrValueToString($oMbqEtForumTopic->participatedUids->oriValue);
        }
        if ($oMbqEtForumTopic->canThank->hasSetOriValue()) {
            $data['can_thank'] = (boolean) $oMbqEtForumTopic->canThank->oriValue;
        } else {
            $data['can_thank'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canThank.default');
        }
        if ($oMbqEtForumTopic->thankCount->hasSetOriValue()) {
            $data['thank_count'] = (int) $oMbqEtForumTopic->thankCount->oriValue;
        }
        if ($oMbqEtForumTopic->canLike->hasSetOriValue()) {
            $data['can_like'] = (boolean) $oMbqEtForumTopic->canLike->oriValue;
        } else {
            $data['can_like'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canLike.default');
        }
        if ($oMbqEtForumTopic->isLiked->hasSetOriValue()) {
            $data['is_liked'] = (boolean) $oMbqEtForumTopic->isLiked->oriValue;
        }
        if ($oMbqEtForumTopic->likeCount->hasSetOriValue()) {
            $data['like_count'] = (int) $oMbqEtForumTopic->likeCount->oriValue;
        }
        if ($oMbqEtForumTopic->participatedIn->hasSetOriValue()) {
            $data['participated_in'] = (boolean) $oMbqEtForumTopic->participatedIn->oriValue;
        }
        if ($oMbqEtForumTopic->canDelete->hasSetOriValue()) {
            $data['can_delete'] = (boolean) $oMbqEtForumTopic->canDelete->oriValue;
        } else {
            $data['can_delete'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canDelete.default');
        }
        if ($oMbqEtForumTopic->isDeleted->hasSetOriValue()) {
            $data['is_deleted'] = (boolean) $oMbqEtForumTopic->isDeleted->oriValue;
        }
        if ($oMbqEtForumTopic->canApprove->hasSetOriValue()) {
            $data['can_approve'] = (boolean) $oMbqEtForumTopic->canApprove->oriValue;
        } else {
            $data['can_approve'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canApprove.default');
        }
        if ($oMbqEtForumTopic->isApproved->hasSetOriValue()) {
            $data['is_approved'] = (boolean) $oMbqEtForumTopic->isApproved->oriValue;
        }
        if ($oMbqEtForumTopic->canStick->hasSetOriValue()) {
            $data['can_stick'] = (boolean) $oMbqEtForumTopic->canStick->oriValue;
        } else {
            $data['can_stick'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canStick.default');
        }
        if ($oMbqEtForumTopic->isSticky->hasSetOriValue()) {
            $data['is_sticky'] = (boolean) $oMbqEtForumTopic->isSticky->oriValue;
        }
        if ($oMbqEtForumTopic->canClose->hasSetOriValue()) {
            $data['can_close'] = (boolean) $oMbqEtForumTopic->canClose->oriValue;
        } else {
            $data['can_close'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canClose.default');
        }
        if ($oMbqEtForumTopic->canRename->hasSetOriValue()) {
            $data['can_rename'] = (boolean) $oMbqEtForumTopic->canRename->oriValue;
        } else {
            $data['can_rename'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canRename.default');
        }
        if ($oMbqEtForumTopic->canMove->hasSetOriValue()) {
            $data['can_move'] = (boolean) $oMbqEtForumTopic->canMove->oriValue;
        } else {
            $data['can_move'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canMove.default');
        }
        if ($oMbqEtForumTopic->isMoved->hasSetOriValue()) {
            $data['is_moved'] = (boolean) $oMbqEtForumTopic->isMoved->oriValue;
            if ($data['is_moved'] && $oMbqEtForumTopic->realTopicId->hasSetOriValue()){
                $data['topic_id'] = (string) $oMbqEtForumTopic->realTopicId->oriValue;
            }
        }
        if ($oMbqEtForumTopic->canMerge->hasSetOriValue()) {
            $data['can_merge'] = (boolean) $oMbqEtForumTopic->canMerge->oriValue;
        } else {
            $data['can_merge'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canMerge.default');
        }
        if ($oMbqEtForumTopic->realTopicId->hasSetOriValue()) {
            $data['real_topic_id'] = (string) $oMbqEtForumTopic->realTopicId->oriValue;
        }
        if ($oMbqEtForumTopic->modByUserId->hasSetOriValue()) {
            $data['moderated_by_id'] = (string) $oMbqEtForumTopic->modByUserId->oriValue;
        }
        if ($oMbqEtForumTopic->deleteByUserId->hasSetOriValue()) {
            $data['deleted_by_id'] = (string) $oMbqEtForumTopic->deleteByUserId->oriValue;
        }
        if ($oMbqEtForumTopic->deleteReason->hasSetOriValue()) {
            $data['delete_reason'] = (string) $oMbqEtForumTopic->deleteReason->oriValue;
        }
        if ($oMbqEtForumTopic->canReply->hasSetOriValue()) {
            $data['can_reply'] = (boolean) $oMbqEtForumTopic->canReply->oriValue;
        } else {
            $data['can_reply'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canReply.default');
        }
        if ($oMbqEtForumTopic->canBan->hasSetOriValue()) {
            $data['can_ban'] = (boolean) $oMbqEtForumTopic->canBan->oriValue;
        } else {
            $data['can_ban'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canBan.default');
        }
        if ($oMbqEtForumTopic->isBan->hasSetOriValue()) {
            $data['is_ban'] = (boolean) $oMbqEtForumTopic->isBan->oriValue;
        }
        return $data;
    }
   
    /**
     * return forum topic array api data
     *
     * @param  Array  $objsMbqEtForumTopic
     * @return  Array
     */
    public function returnApiArrDataForumTopic($objsMbqEtForumTopic) {
        $data = array();
        foreach ($objsMbqEtForumTopic as $oMbqEtForumTopic) {
            $data[] = $this->returnApiDataForumTopic($oMbqEtForumTopic);
        }
        return $data;
    }

    /**
     * get forum topic objs
     *
     * @return  Mixed
     */
    public function getObjsMbqEtForumTopic($var, $mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * init one forum topic by condition
     *
     * @return  Mixed
     */
    public function initOMbqEtForumTopic($var, $mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * This function should return the real url of the topic following any seo rules forum have
     *
     * @param mixed $oMbqEtForumTopic
     */
    public function getUrl($oMbqEtForumTopic)
    {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
}
