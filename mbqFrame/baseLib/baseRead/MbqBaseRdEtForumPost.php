<?php

defined('MBQ_IN_IT') or exit;

/**
 * forum post read class
 */
Abstract Class MbqBaseRdEtForumPost extends MbqBaseRd {

    public function __construct() {
    }

    /**
     * return forum post api data
     *
     * @param  Object  $oMbqEtForumPost
     * @param  Boolean  $returnHtml
     * @return  Array
     */
    public function returnApiDataForumPost($oMbqEtForumPost, $returnHtml = true) {
        if (MbqMain::isJsonProtocol()) return $this->returnJsonApiDataForumPost($oMbqEtForumPost);
        $data = array();
        if($oMbqEtForumPost != null)
        {
            if ($oMbqEtForumPost->postId->hasSetOriValue()) {
                $data['post_id'] = (string) $oMbqEtForumPost->postId->oriValue;
            }
            if ($oMbqEtForumPost->forumId->hasSetOriValue()) {
                $data['forum_id'] = (string) $oMbqEtForumPost->forumId->oriValue;
            }
            if ($oMbqEtForumPost->oMbqEtForum  && $oMbqEtForumPost->oMbqEtForum instanceof MbqEtForum) {
                $data['forum_name'] = (string) $oMbqEtForumPost->oMbqEtForum->forumName->oriValue;
            }
            if ($oMbqEtForumPost->topicId->hasSetOriValue()) {
                $data['topic_id'] = (string) $oMbqEtForumPost->topicId->oriValue;
            }
            if ($oMbqEtForumPost->oMbqEtForumTopic != null && $oMbqEtForumPost->oMbqEtForumTopic instanceof MbqEtForumTopic) {
                $data['topic_title'] = (string) $oMbqEtForumPost->oMbqEtForumTopic->topicTitle->oriValue;
                if ($oMbqEtForumPost->oMbqEtForumTopic->replyNumber->hasSetOriValue()) {
                    $data['reply_number'] = (int) $oMbqEtForumPost->oMbqEtForumTopic->replyNumber->oriValue;
                }
                if ($oMbqEtForumPost->oMbqEtForumTopic->newPost->hasSetOriValue()) {
                    $data['new_post'] = (boolean) $oMbqEtForumPost->oMbqEtForumTopic->newPost->oriValue;
                }
                if ($oMbqEtForumPost->oMbqEtForumTopic->viewNumber->hasSetOriValue()) {
                    $data['view_number'] = (int) $oMbqEtForumPost->oMbqEtForumTopic->viewNumber->oriValue;
                }
            }
            if ($oMbqEtForumPost->postTitle->hasSetOriValue()) {
                $data['post_title'] = (string) $oMbqEtForumPost->postTitle->oriValue;
            }
            if ($returnHtml) {
                if($returnHtml === 2)
                {
                    if ($oMbqEtForumPost->postContent->hasSetTmlDisplayValueAdvancedHtml()) {
                        $data['post_content'] = (string) $oMbqEtForumPost->postContent->tmlDisplayValueAdvancedHtml;
                    }
                }
                else
                {
                    if ($oMbqEtForumPost->postContent->hasSetTmlDisplayValue()) {
                        $data['post_content'] = (string) $oMbqEtForumPost->postContent->tmlDisplayValue;
                    }
                }
            } else {
                if ($oMbqEtForumPost->postContent->hasSetTmlDisplayValueNoHtml()) {
                    $data['post_content'] = (string) $oMbqEtForumPost->postContent->tmlDisplayValueNoHtml;
                }
            }
            $data['short_content'] = (string) $oMbqEtForumPost->shortContent->oriValue;
            if ($oMbqEtForumPost->postAuthorId->hasSetOriValue()) {
                $data['post_author_id'] = (string) $oMbqEtForumPost->postAuthorId->oriValue;
            }
            if ($oMbqEtForumPost->authorIconUrl->hasSetOriValue()) {
                $data['icon_url'] = (string) $oMbqEtForumPost->authorIconUrl->oriValue;
            }
            if ($oMbqEtForumPost->oAuthorMbqEtUser != null && $oMbqEtForumPost->oAuthorMbqEtUser instanceof MbqEtUser) {
                $data['post_author_name'] = (string) $oMbqEtForumPost->oAuthorMbqEtUser->getDisplayName();
            
                if ($oMbqEtForumPost->authorIconUrl->hasSetOriValue() == false) {
                    $data['icon_url'] = (string) $oMbqEtForumPost->oAuthorMbqEtUser->iconUrl->oriValue;
                }
                if ($oMbqEtForumPost->oAuthorMbqEtUser->userType->hasSetOriValue()) {
                    $data['post_author_user_type'] = (string) $oMbqEtForumPost->oAuthorMbqEtUser->userType->oriValue;
                }
                if ($oMbqEtForumPost->oAuthorMbqEtUser->isIgnored->hasSetOriValue()) {
                    $data['post_author_is_ignored'] = (bool) $oMbqEtForumPost->oAuthorMbqEtUser->isIgnored->oriValue;
                }
            }
            else if($oMbqEtForumPost->postAuthorName->hasSetOriValue())
            {
                $data['post_author_name'] = (string) $oMbqEtForumPost->postAuthorName->oriValue;
            }
            if ($oMbqEtForumPost->attachmentIdArray->hasSetOriValue()) {
                $data['attachment_id_array'] = (array) $oMbqEtForumPost->attachmentIdArray->oriValue;
            }
            if ($oMbqEtForumPost->groupId->hasSetOriValue()) {
                $data['group_id'] = (string) $oMbqEtForumPost->groupId->oriValue;
            }
            if ($oMbqEtForumPost->state->hasSetOriValue()) {
                $data['state'] = (int) $oMbqEtForumPost->state->oriValue;
            }
            if ($oMbqEtForumPost->isOnline->hasSetOriValue()) {
                $data['is_online'] = (boolean) $oMbqEtForumPost->isOnline->oriValue;
            }
            if ($oMbqEtForumPost->canEdit->hasSetOriValue()) {
                $data['can_edit'] = (boolean) $oMbqEtForumPost->canEdit->oriValue;
            } else {
                $data['can_edit'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canEdit.default');
            }

            if ($oMbqEtForumPost->postTime->hasSetOriValue()) {
                $data['post_time'] = (string) MbqMain::$oMbqCm->datetimeIso8601Encode($oMbqEtForumPost->postTime->oriValue);
                $data['timestamp'] = (string)MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtForumPost->postTime->oriValue);
            }
            if ($oMbqEtForumPost->allowSmilies->hasSetOriValue()) {
                $data['allow_smilies'] = (boolean) $oMbqEtForumPost->allowSmilies->oriValue;
            }
            if ($oMbqEtForumPost->position->hasSetOriValue()) {
                $data['position'] = (int) $oMbqEtForumPost->position->oriValue;
            }
            if ($oMbqEtForumPost->canThank->hasSetOriValue()) {
                $data['can_thank'] = (boolean) $oMbqEtForumPost->canThank->oriValue;
            } else {
                $data['can_thank'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canThank.default');
            }
            if ($oMbqEtForumPost->thankCount->hasSetOriValue()) {
                $data['thank_count'] = (int) $oMbqEtForumPost->thankCount->oriValue;
            }
            if ($oMbqEtForumPost->isLiked->hasSetOriValue()) {
                $data['is_liked'] = (boolean) $oMbqEtForumPost->isLiked->oriValue;
            }
            if ($oMbqEtForumPost->canLike->hasSetOriValue()) {
                $data['can_like'] = (boolean) $oMbqEtForumPost->canLike->oriValue;
            } else {
                $data['can_like'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canLike.default');
            }
            if ($oMbqEtForumPost->likeCount->hasSetOriValue()) {
                $data['like_count'] = (int) $oMbqEtForumPost->likeCount->oriValue;
            }
            if ($oMbqEtForumPost->canDelete->hasSetOriValue()) {
                $data['can_delete'] = (boolean) $oMbqEtForumPost->canDelete->oriValue;
            } else {
                $data['can_delete'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canDelete.default');
            }
            if ($oMbqEtForumPost->isDeleted->hasSetOriValue()) {
                $data['is_deleted'] = (boolean) $oMbqEtForumPost->isDeleted->oriValue;
            }
            if ($oMbqEtForumPost->canBan->hasSetOriValue()) {
                $data['can_ban'] = (boolean) $oMbqEtForumPost->canBan->oriValue;
            } else {
                $data['can_ban'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canBan.default');
            }
            if ($oMbqEtForumPost->isBan->hasSetOriValue()) {
                $data['is_ban'] = (boolean) $oMbqEtForumPost->isBan->oriValue;
            }
            if ($oMbqEtForumPost->canApprove->hasSetOriValue()) {
                $data['can_approve'] = (boolean) $oMbqEtForumPost->canApprove->oriValue;
            } else {
                $data['can_approve'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canApprove.default');
            }
            if ($oMbqEtForumPost->isApproved->hasSetOriValue()) {
                $data['is_approved'] = (boolean) $oMbqEtForumPost->isApproved->oriValue;
            }
            if ($oMbqEtForumPost->canMove->hasSetOriValue()) {
                $data['can_move'] = (boolean) $oMbqEtForumPost->canMove->oriValue;
            } else {
                $data['can_move'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canMove.default');
            }
            if ($oMbqEtForumPost->modByUserId->hasSetOriValue()) {
                $data['moderated_by_id'] = (string) $oMbqEtForumPost->modByUserId->oriValue;
            }
            if ($oMbqEtForumPost->deleteByUserId->hasSetOriValue()) {
                $data['deleted_by_id'] = (string) $oMbqEtForumPost->deleteByUserId->oriValue;
            }
            if ($oMbqEtForumPost->deleteReason->hasSetOriValue()) {
                $data['delete_reason'] = (string) $oMbqEtForumPost->deleteReason->oriValue;
            }
            if ($oMbqEtForumPost->canReport->hasSetOriValue()) {
                $data['can_report'] = (boolean) $oMbqEtForumPost->canReport->oriValue;
            } else {
                $data['can_report'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canReport.default');
            }
            if ($oMbqEtForumPost->canAddEditReason->hasSetOriValue()) {
                $data['show_reason'] = (boolean) $oMbqEtForumPost->canAddEditReason->oriValue;
            }
            if ($oMbqEtForumPost->editedByUserId->hasSetOriValue()) {
                $data['editor_id'] = (string) $oMbqEtForumPost->editedByUserId->oriValue;
            }
            if ($oMbqEtForumPost->editedByUsername->hasSetOriValue()) {
                $data['editor_name'] = (string) $oMbqEtForumPost->editedByUsername->oriValue;
            }
            if ($oMbqEtForumPost->editedByTime->hasSetOriValue()) {
                $data['edit_time'] =  (string) MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtForumPost->editedByTime->oriValue);
            }
            if ($oMbqEtForumPost->showReason->hasSetOriValue()) {
                $data['show_reason'] = (boolean) $oMbqEtForumPost->showReason->oriValue;
            }
            if ($oMbqEtForumPost->editReason->hasSetOriValue()) {
                $data['edit_reason'] = (string) $oMbqEtForumPost->editReason->oriValue;
            }
            $this->editedByUserId = clone MbqMain::$simpleV;
            $this->editedByUsername = clone MbqMain::$simpleV;
            $this->editedByTime = clone MbqMain::$simpleV;

            if ($oMbqEtForumPost->clientType->hasSetOriValue()) {
                $data['client_type'] = $oMbqEtForumPost->clientType->oriValue;
            }

            /* attachments */
            $oMbqRdEtAtt = MbqMain::$oClk->newObj('MbqRdEtAtt');
            $data['attachments'] = (array) $oMbqRdEtAtt->returnApiArrDataAttachment($oMbqEtForumPost->objsNotInContentMbqEtAtt);
            /* inline attachments*/
            $oMbqRdEtAtt = MbqMain::$oClk->newObj('MbqRdEtAtt');
            $data['inlineattachments'] = (array) $oMbqRdEtAtt->returnApiArrDataAttachment($oMbqEtForumPost->objsMbqEtAtt);
            /* thanks_info */
            $oMbqRdEtThank = MbqMain::$oClk->newObj('MbqRdEtThank');
            $data['thanks_info'] = (array) $oMbqRdEtThank->returnApiArrDataThank($oMbqEtForumPost->objsMbqEtThank);
            /* likes_info.TODO */
            $oMbqRdEtLike = MbqMain::$oClk->newObj('MbqRdEtLike');
            $data['likes_info'] = (array) $oMbqRdEtLike->returnApiArrDataLike($oMbqEtForumPost->objsMbqEtLike);
        }
        return $data;
    }
    public function returnJsonApiDataForumPost($oMbqEtForumPost, $returnHtml = true) {
        $data = array();
        if ($oMbqEtForumPost->postId->hasSetOriValue()) {
            $data['post_id'] = (string) $oMbqEtForumPost->postId->oriValue;
        }
        if ($oMbqEtForumPost->forumId->hasSetOriValue()) {
            $data['forum_id'] = (string) $oMbqEtForumPost->forumId->oriValue;
        }
        if ($oMbqEtForumPost->oMbqEtForum && $oMbqEtForumPost->oMbqEtForum instanceof MbqEtForum) {
            $data['forum_name'] = (string) $oMbqEtForumPost->oMbqEtForum->forumName->oriValue;
        }
        if ($oMbqEtForumPost->topicId->hasSetOriValue()) {
            $data['topic_id'] = (string) $oMbqEtForumPost->topicId->oriValue;
        }
        if ($oMbqEtForumPost->oMbqEtForumTopic != null && $oMbqEtForumPost->oMbqEtForumTopic instanceof MbqEtForumTopic) {
            $data['topic_title'] = (string) $oMbqEtForumPost->oMbqEtForumTopic->topicTitle->oriValue;
            if ($oMbqEtForumPost->oMbqEtForumTopic->replyNumber->hasSetOriValue()) {
                $data['reply_number'] = (int) $oMbqEtForumPost->oMbqEtForumTopic->replyNumber->oriValue;
            }
            if ($oMbqEtForumPost->oMbqEtForumTopic->newPost->hasSetOriValue()) {
                $data['new_post'] = (boolean) $oMbqEtForumPost->oMbqEtForumTopic->newPost->oriValue;
            }
            if ($oMbqEtForumPost->oMbqEtForumTopic->viewNumber->hasSetOriValue()) {
                $data['view_number'] = (int) $oMbqEtForumPost->oMbqEtForumTopic->viewNumber->oriValue;
            }
        }
        if ($oMbqEtForumPost->postTitle->hasSetOriValue()) {
            $data['post_title'] = (string) $oMbqEtForumPost->postTitle->oriValue;
        }
        if ($returnHtml) {
            if($returnHtml === 2)
            {
                if ($oMbqEtForumPost->postContent->hasSetTmlDisplayValueAdvancedHtml()) {
                    $data['post_content'] = (string) $oMbqEtForumPost->postContent->tmlDisplayValueAdvancedHtml;
                }
            }
            else
            {
                if ($oMbqEtForumPost->postContent->hasSetTmlDisplayValue()) {
                    $data['post_content'] = (string) $oMbqEtForumPost->postContent->tmlDisplayValue;
                }
            }
        } else {
            if ($oMbqEtForumPost->postContent->hasSetTmlDisplayValueNoHtml()) {
                $data['post_content'] = (string) $oMbqEtForumPost->postContent->tmlDisplayValueNoHtml;
            }
        }
        $data['short_content'] = (string) $oMbqEtForumPost->shortContent->oriValue;
        if ($oMbqEtForumPost->postAuthorId->hasSetOriValue()) {
            $data['post_author_id'] = (string) $oMbqEtForumPost->postAuthorId->oriValue;
        }
        if ($oMbqEtForumPost->authorIconUrl->hasSetOriValue()) {
            $data['icon_url'] = (string) $oMbqEtForumPost->authorIconUrl->oriValue;
        }
        if ($oMbqEtForumPost->oAuthorMbqEtUser != null && $oMbqEtForumPost->oAuthorMbqEtUser instanceof MbqEtUser) {
            $data['post_author_name'] = (string) $oMbqEtForumPost->oAuthorMbqEtUser->getDisplayName();
            if ($oMbqEtForumPost->authorIconUrl->hasSetOriValue() == false) {
                $data['icon_url'] = (string) $oMbqEtForumPost->oAuthorMbqEtUser->iconUrl->oriValue;
            }
            if ($oMbqEtForumPost->oAuthorMbqEtUser->userType->hasSetOriValue()) {
                $data['post_author_user_type'] = (string) $oMbqEtForumPost->oAuthorMbqEtUser->userType->oriValue;
            }
        }
        else if($oMbqEtForumPost->postAuthorName->hasSetOriValue())
        {
            $data['post_author_name'] = (string) $oMbqEtForumPost->postAuthorName->oriValue;
        }
        if ($oMbqEtForumPost->attachmentIdArray->hasSetOriValue()) {
            $data['attachment_id_array'] = (array) $oMbqEtForumPost->attachmentIdArray->oriValue;
        }
        if ($oMbqEtForumPost->groupId->hasSetOriValue()) {
            $data['group_id'] = (string) $oMbqEtForumPost->groupId->oriValue;
        }
        if ($oMbqEtForumPost->state->hasSetOriValue()) {
            $data['state'] = (int) $oMbqEtForumPost->state->oriValue;
        }
        if ($oMbqEtForumPost->isOnline->hasSetOriValue()) {
            $data['is_online'] = (boolean) $oMbqEtForumPost->isOnline->oriValue;
        }
        if ($oMbqEtForumPost->canEdit->hasSetOriValue()) {
            $data['can_edit'] = (boolean) $oMbqEtForumPost->canEdit->oriValue;
        } else {
            $data['can_edit'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canEdit.default');
        }

        if ($oMbqEtForumPost->postTime->hasSetOriValue()) {
            $data['post_time'] = (string) MbqMain::$oMbqCm->datetimeIso8601Encode($oMbqEtForumPost->postTime->oriValue);
            $data['timestamp'] = (string)MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtForumPost->postTime->oriValue);
        }
        if ($oMbqEtForumPost->allowSmilies->hasSetOriValue()) {
            $data['allow_smilies'] = (boolean) $oMbqEtForumPost->allowSmilies->oriValue;
        }
        if ($oMbqEtForumPost->position->hasSetOriValue()) {
            $data['position'] = (int) $oMbqEtForumPost->position->oriValue;
        }
        if ($oMbqEtForumPost->canThank->hasSetOriValue()) {
            $data['can_thank'] = (boolean) $oMbqEtForumPost->canThank->oriValue;
        } else {
            $data['can_thank'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canThank.default');
        }
        if ($oMbqEtForumPost->thankCount->hasSetOriValue()) {
            $data['thank_count'] = (int) $oMbqEtForumPost->thankCount->oriValue;
        }
        if ($oMbqEtForumPost->isLiked->hasSetOriValue()) {
            $data['is_liked'] = (boolean) $oMbqEtForumPost->isLiked->oriValue;
        }
        if ($oMbqEtForumPost->canLike->hasSetOriValue()) {
            $data['can_like'] = (boolean) $oMbqEtForumPost->canLike->oriValue;
        } else {
            $data['can_like'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canLike.default');
        }
        if ($oMbqEtForumPost->likeCount->hasSetOriValue()) {
            $data['like_count'] = (int) $oMbqEtForumPost->likeCount->oriValue;
        }
        if ($oMbqEtForumPost->canDelete->hasSetOriValue()) {
            $data['can_delete'] = (boolean) $oMbqEtForumPost->canDelete->oriValue;
        } else {
            $data['can_delete'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canDelete.default');
        }
        if ($oMbqEtForumPost->isDeleted->hasSetOriValue()) {
            $data['is_deleted'] = (boolean) $oMbqEtForumPost->isDeleted->oriValue;
        }
        if ($oMbqEtForumPost->canBan->hasSetOriValue()) {
            $data['can_ban'] = (boolean) $oMbqEtForumPost->canBan->oriValue;
        } else {
            $data['can_ban'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canBan.default');
        }
        if ($oMbqEtForumPost->isBan->hasSetOriValue()) {
            $data['is_ban'] = (boolean) $oMbqEtForumPost->isBan->oriValue;
        }
        if ($oMbqEtForumPost->canApprove->hasSetOriValue()) {
            $data['can_approve'] = (boolean) $oMbqEtForumPost->canApprove->oriValue;
        } else {
            $data['can_approve'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canApprove.default');
        }
        if ($oMbqEtForumPost->isApproved->hasSetOriValue()) {
            $data['is_approved'] = (boolean) $oMbqEtForumPost->isApproved->oriValue;
        }
        if ($oMbqEtForumPost->canMove->hasSetOriValue()) {
            $data['can_move'] = (boolean) $oMbqEtForumPost->canMove->oriValue;
        } else {
            $data['can_move'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canMove.default');
        }
        if ($oMbqEtForumPost->modByUserId->hasSetOriValue()) {
            $data['moderated_by_id'] = (string) $oMbqEtForumPost->modByUserId->oriValue;
        }
        if ($oMbqEtForumPost->deleteByUserId->hasSetOriValue()) {
            $data['deleted_by_id'] = (string) $oMbqEtForumPost->deleteByUserId->oriValue;
        }
        if ($oMbqEtForumPost->deleteReason->hasSetOriValue()) {
            $data['delete_reason'] = (string) $oMbqEtForumPost->deleteReason->oriValue;
        }
        if ($oMbqEtForumPost->canReport->hasSetOriValue()) {
            $data['can_report'] = (boolean) $oMbqEtForumPost->canReport->oriValue;
        } else {
            $data['can_report'] = (boolean) MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canReport.default');
        }
        if ($oMbqEtForumPost->canAddEditReason->hasSetOriValue()) {
            $data['show_reason'] = (boolean) $oMbqEtForumPost->canAddEditReason->oriValue;
        }
        if ($oMbqEtForumPost->editedByUserId->hasSetOriValue()) {
            $data['editor_id'] = (string) $oMbqEtForumPost->editedByUserId->oriValue;
        }
        if ($oMbqEtForumPost->editedByUsername->hasSetOriValue()) {
            $data['editor_name'] = (string) $oMbqEtForumPost->editedByUsername->oriValue;
        }
        if ($oMbqEtForumPost->editedByTime->hasSetOriValue()) {
            $data['edit_time'] =  (string) MbqMain::$oMbqCm->datetimeTimestampEncode($oMbqEtForumPost->editedByTime->oriValue);
        }
        if ($oMbqEtForumPost->showReason->hasSetOriValue()) {
            $data['show_reason'] = (boolean) $oMbqEtForumPost->showReason->oriValue;
        }
        if ($oMbqEtForumPost->editReason->hasSetOriValue()) {
            $data['edit_reason'] = (string) $oMbqEtForumPost->editReason->oriValue;
        }
        $this->editedByUserId = clone MbqMain::$simpleV;
        $this->editedByUsername = clone MbqMain::$simpleV;
        $this->editedByTime = clone MbqMain::$simpleV;

        if ($oMbqEtForumPost->clientType->hasSetOriValue()) {
            $data['client_type'] = $oMbqEtForumPost->clientType->oriValue;
        }

        /* attachments */
        $oMbqRdEtAtt = MbqMain::$oClk->newObj('MbqRdEtAtt');
        $data['attachments'] = (array) $oMbqRdEtAtt->returnApiArrDataAttachment($oMbqEtForumPost->objsNotInContentMbqEtAtt);
        /* inline attachments*/
        $oMbqRdEtAtt = MbqMain::$oClk->newObj('MbqRdEtAtt');
        $data['inlineattachments'] = (array) $oMbqRdEtAtt->returnApiArrDataAttachment($oMbqEtForumPost->objsMbqEtAtt);
        /* thanks_info */
        $oMbqRdEtThank = MbqMain::$oClk->newObj('MbqRdEtThank');
        $data['thanks_info'] = (array) $oMbqRdEtThank->returnApiArrDataThank($oMbqEtForumPost->objsMbqEtThank);
        /* likes_info.TODO */
        $oMbqRdEtLike = MbqMain::$oClk->newObj('MbqRdEtLike');
        $data['likes_info'] = (array) $oMbqRdEtLike->returnApiArrDataLike($oMbqEtForumPost->objsMbqEtLike);
        return $data;
    }
   
    /**
     * return forum post array api data
     *
     * @param  Array  $objsMbqEtForumPost
     * @param  Boolean  $returnHtml
     * @return  Array
     */
    public function returnApiArrDataForumPost($objsMbqEtForumPost, $returnHtml = true) {
        $data = array();
        foreach ($objsMbqEtForumPost as $oMbqEtForumPost) {
            $data[] = $this->returnApiDataForumPost($oMbqEtForumPost, $returnHtml);
        }
        return $data;
    }

    /**
     * get forum post objs
     *
     * @return  Mixed
     */
    public function getObjsMbqEtForumPost($var, $mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * init one forum post by condition
     *
     * @return  Mixed
     */
    public function initOMbqEtForumPost($var, $mbqOpt) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * process content for display in mobile app
     *
     * @return  String
     */
    public function processContentForDisplay($content, $returnHtml, $obj) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * return quote post content
     *
     * @return  String
     */
    public function getQuotePostContent($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * return raw post content
     *
     * @return  String
     */
    public function getRawPostContent($oMbqEtForumPost) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

    /**
     * This function should return the real url of the post following any seo rules forum have
     *
     * @param mixed $oMbqEtForumPost
     */
    public function getUrl($oMbqEtForumPost)
    {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
}
