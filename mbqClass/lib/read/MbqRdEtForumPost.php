<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtForumPost');

/**
 * forum post read class
 */
Class MbqRdEtForumPost extends MbqBaseRdEtForumPost {

    public function __construct() {
    }

    public function makeProperty(&$oMbqEtForumPost, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
    /**
     * get forum post objs
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byTopic' means get data by forum topic obj.$var is the forum topic obj.
     * $mbqOpt['case'] = 'byPostIds' means get data by post ids.$var is the ids.
     * $mbqOpt['case'] = 'byReplyUser' means get data by reply user.$var is the MbqEtUser obj.
     * @return  Mixed
     */
    public function getObjsMbqEtForumPost($var, $mbqOpt) {
        switch($mbqOpt['case'])
        {
            case 'byTopic':
                {
                    global $request, $template, $user, $auth, $phpbb_home, $config, $attachment_by_id, $forum_id, $topic_id, $support_post_thanks, $topic_data, $total_posts, $can_subscribe, $post_data;
                    $topic_data = null;
                    $post_data = null;
                    $oMbqEtForumTopic = $var;
                    $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                    $request_file = 'viewtopic';
                    overwriteRequestParam('st',0);
                    overwriteRequestParam('sk','t');
                    overwriteRequestParam('sd','a');
                    overwriteRequestParam('t', $oMbqEtForumTopic->topicId->oriValue);
                    overwriteRequestParam('start',$oMbqDataPage->startNum);
                    overwriteRequestParam('limit',$oMbqDataPage->numPerPage);
                    requireExtLibrary('viewtopic_clone');
                    $newMbqOpt['case'] = 'byRow';
                    $newMbqOpt['oMbqEtForum'] = true;
                    $newMbqOpt['oMbqEtForumTopic'] = true;
                    $newMbqOpt['oMbqEtUser'] = true;
                    $newMbqOpt['oMbqDataPage'] = $oMbqDataPage;
                    $objsMbqEtForumPost = array();
                    $this->prepare($post_data);
                    foreach($post_data as $item)
                    {
                        $objsMbqEtForumPost[] = $this->initOMbqEtForumPost($item, $newMbqOpt);
                    }
                    if (isset($mbqOpt['oMbqDataPage'])) {
                        $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                        $oMbqDataPage->datas = $objsMbqEtForumPost;
                        return $oMbqDataPage;
                    } else {
                        return $objsMbqEtForumPost;
                    }

                    break;
                }
            case 'byPostId':
                {
                    $id = $var;
                    global $request, $template, $user, $auth, $phpbb_home, $config, $attachment_by_id, $forum_id, $topic_id, $support_post_thanks, $topic_data, $post_data, $total_posts, $can_subscribe, $last_error;
                    $topic_data = null;
                    $post_data = null;
                    $oMbqEtForumTopic = $var;
                    $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                    $request_file = 'viewtopic';
                    overwriteRequestParam('st', 0);
                    overwriteRequestParam('sk', 't');
                    overwriteRequestParam('sd', 'a');
                    overwriteRequestParam('p', $id);
                    overwriteRequestParam('limit', $oMbqDataPage->perPage);
                    requireExtLibrary('viewtopic_clone');
                    $newMbqOpt['case'] = 'byRow';
                    $newMbqOpt['oMbqEtForum'] = true;
                    $newMbqOpt['oMbqEtForumTopic'] = true;
                    $newMbqOpt['oMbqEtUser'] = true;
                    $newMbqOpt['oMbqDataPage'] = $oMbqDataPage;
                    $objsMbqEtForumPost = array();
                    foreach($post_data as $item)
                    {
                        $objsMbqEtForumPost[] = $this->initOMbqEtForumPost($item, $newMbqOpt);
                    }
                    if (isset($mbqOpt['oMbqDataPage'])) {
                        $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                        $oMbqDataPage->datas = $objsMbqEtForumPost;
                        return $oMbqDataPage;
                    } else {
                        return $objsMbqEtForumPost;
                    }

                    break;
                }
            case 'byReplyUser':
                {
                    $oMbqEtUser = $var;
                    $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                    global $request, $db, $template, $user, $auth, $config, $can_subscribe, $show_results, $include_topic_num, $total_match_count, $request_method, $searchResults;
                    $request_method = 'search';
                    $include_topic_num = true;

                    overwriteRequestParam('page', $oMbqDataPage->curPage);
                    overwriteRequestParam('perpage', $oMbqDataPage->numPerPage);
                    overwriteRequestParam('submit', 'Search');
                    overwriteRequestParam('sr', 'posts');


                    overwriteRequestParam('sf', 'all');

                    overwriteRequestParam('author', $oMbqEtUser->userName->oriValue);
                    overwriteRequestParam('author_id', $oMbqEtUser->userId->oriValue);

                    requireExtLibrary('search_clone');
                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                    $newMbqOpt['case'] = 'byRow';
                    $newMbqOpt['oMbqEtForum'] = true;
                    $newMbqOpt['oMbqEtUser'] = true;
                    $newMbqOpt['oMbqDataPage'] = $oMbqDataPage;

                        $newMbqOpt['oMbqEtForumTopic'] = true;
                        $newMbqOpt['oMbqEtUser'] = true;
                        foreach($searchResults as $item)
                        {
                            $oMbqDataPage->datas[] = $oMbqRdEtForumPost->initOMbqEtForumPost($item, $newMbqOpt);
                        }
                        $oMbqDataPage->totalNum = $total_match_count ? $total_match_count : 0;

                    return $oMbqDataPage;
                }
            case 'awaitingModeration':
                {
                    global $user,$phpbb_root_path, $phpEx, $template, $request, $db, $phpbb_container,$config;
                    $oMbqDataPage = $mbqOpt['oMbqDataPage'];

                    overwriteRequestParam('mode', 'unapproved_posts');
                    overwriteRequestParam('start',$oMbqDataPage->startNum);
                    $currentTopicsPerPage = $config['topics_per_page'];
                    $config['topics_per_page'] = $oMbqDataPage->numPerPage;

                    include_once($phpbb_root_path . 'includes/mcp/mcp_queue.' . $phpEx);
                    include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		    if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
		    {
	                    include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
		    }
                    include_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);

                    $user->setup('mcp');
                    $pmaster = new p_master();
                    $mcp_queue = new mcp_queue($pmaster);
                    requireExtLibrary('fake_template');
                    $template = new fake_template();

                    $mcp_queue->main(0,'unapproved_posts');

                    $config['topics_per_page'] = $currentTopicsPerPage;

                    $error = $template->getTemplateVar('ERROR');
                    if(isset($error) && !empty($error))
                    {
                            $errors = explode('<br />', $error);
                            if(is_array($errors))
                            {
                                    return $errors[0];
                                }
                            return $error;
                        }
                    $postRows = $template->getTemplateBlockVar('postrow');

                    $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                    $newMbqOpt['case'] = 'byPostId';
                    $newMbqOpt['oMbqEtForum'] = true;
                    $newMbqOpt['oMbqEtUser'] = true;
                    $newMbqOpt['oMbqDataPage'] = $oMbqDataPage;
                    foreach($postRows as $postRow)
                    {
                        $oMbqDataPage->datas[] = $oMbqRdEtForumPost->initOMbqEtForumPost($postRow['POST_ID'], $newMbqOpt);
                    }
                    $vars = $template->getTemplateVars();
	if(getPHPBBVersion() == '3.0')
		{
		    list($total)  = sscanf($template->_tpldata['.'][0]['TOTAL'], $user->lang['VIEW_TOPIC_POSTS']);
                    $oMbqDataPage->totalNum =  $total;
		}
		else
		{
                	 $oMbqDataPage->totalNum =  $template->pagination->total;
		}

                    return $oMbqDataPage;
                }
            case 'deleted':
                {
                    global $user,$phpbb_root_path, $phpEx, $template, $request, $db, $phpbb_container,$config;
                    requireExtLibrary('fake_template');
                    $template = new fake_template();
                    $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                    overwriteRequestParam('mode', 'deleted_posts');
                    overwriteRequestParam('start',$oMbqDataPage->startNum);
                    $currentTopicsPerPage = $config['topics_per_page'];
                    $config['topics_per_page'] = $oMbqDataPage->numPerPage;

                    include_once($phpbb_root_path . 'includes/mcp/mcp_queue.' . $phpEx);
                    include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
                    if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
		    {
	                    include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
		    }
                    include_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);

                    $user->setup('mcp');
                    $pmaster= new p_master();
                    $mcp_queue = new mcp_queue($pmaster);

                    $mcp_queue->main(0,'deleted_posts');

                    $config['topics_per_page'] = $currentTopicsPerPage;

                    $error = $template->getTemplateVar('ERROR');
                    if(isset($error) && !empty($error))
                    {
                            $errors = explode('<br />', $error);
                            if(is_array($errors))
                            {
                                    return $errors[0];
                                }
                            return $error;
                        }
                    $postRows = $template->getTemplateBlockVar('postrow');

                    $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                    $newMbqOpt['case'] = 'byPostId';
                    $newMbqOpt['oMbqEtForum'] = true;
                    $newMbqOpt['oMbqEtUser'] = true;
                    $newMbqOpt['oMbqDataPage'] = $oMbqDataPage;
                    if(isset($postRows))
                    {
                        foreach($postRows as $postRow)
                        {
                            $oMbqDataPage->datas[] = $oMbqRdEtForumPost->initOMbqEtForumPost($postRow['POST_ID'], $newMbqOpt);
                        }
                        $vars = $template->getTemplateVars();
                    }
                    $oMbqDataPage->totalNum =  $template->pagination->total > sizeof($oMbqDataPage->datas) ? $template->pagination->total : sizeof($oMbqDataPage->datas);
                    return $oMbqDataPage;
                }
            case 'reported':
                {
                    global $user,$phpbb_root_path, $phpEx, $template, $request, $db, $phpbb_container,$config;
                    $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                    overwriteRequestParam('mode', 'reports');
                    overwriteRequestParam('start',$oMbqDataPage->startNum);
                    $currentTopicsPerPage = $config['topics_per_page'];
                    $config['topics_per_page'] = $oMbqDataPage->numPerPage;

                    include_once($phpbb_root_path . 'includes/mcp/mcp_reports.' . $phpEx);
                    include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
                    if(file_exists($phpbb_root_path . 'includes/functions_mcp.' . $phpEx))
                    {
	                    include_once($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
                    }
                    include_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);

                    $user->setup('mcp');
                    $pmaster = new p_master();
                    $mcp_queue = new mcp_reports($pmaster);
                    requireExtLibrary('fake_template');
                    $template = new fake_template();

                    $mcp_queue->main(0,'reports');

                    $config['topics_per_page'] = $currentTopicsPerPage;

                    $error = $template->getTemplateVar('ERROR');
                    if(isset($error) && !empty($error))
                    {
                            $errors = explode('<br />', $error);
                            if(is_array($errors))
                            {
                                    return $errors[0];
                                }
                            return $error;
                        }
                    $postRows = $template->getTemplateBlockVar('postrow');

                    $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                    $newMbqOpt['case'] = 'byPostId';
                    $newMbqOpt['oMbqEtForum'] = true;
                    $newMbqOpt['oMbqEtUser'] = true;
                    $newMbqOpt['oMbqDataPage'] = $oMbqDataPage;
                    foreach($postRows as $postRow)
                    {
                        $oMbqDataPage->datas[] = $oMbqRdEtForumPost->initOMbqEtForumPost($postRow['POST_ID'], $newMbqOpt);
                    }
                    $vars = $template->getTemplateVars();
		if(getPHPBBVersion() == '3.0')
		{
		    list($total)  = sscanf($template->_tpldata['.'][0]['TOTAL'], $user->lang['VIEW_TOPIC_POSTS']);
                    $oMbqDataPage->totalNum =  $total;
		}
		else
		{
                	 $oMbqDataPage->totalNum =  $template->pagination->total;
		}

                    return $oMbqDataPage;
                }
            case 'byObjs':
                {
                    $postList = $var;
                    $objsMbqEtForumPost = array();
                    $authorUserIds = array();
                    $forumIds = array();
                    $topicIds = array();
                    foreach ($postList as $postNode) {
                            $objsMbqEtForumPost[] = $postNode;
                    }
                    foreach ($objsMbqEtForumPost as $oMbqEtForumPost) {
                        $authorUserIds[$oMbqEtForumPost->postAuthorId->oriValue] = $oMbqEtForumPost->postAuthorId->oriValue;
                        $forumIds[$oMbqEtForumPost->forumId->oriValue] = $oMbqEtForumPost->forumId->oriValue;
                        $topicIds[$oMbqEtForumPost->topicId->oriValue] = $oMbqEtForumPost->topicId->oriValue;
                    }
                    /* load oMbqEtForum property */
                    $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
                    $objsMbqEtForum = $oMbqRdEtForum->getObjsMbqEtForum($forumIds, array('case' => 'byForumIds'));
                    foreach ($objsMbqEtForum as $oNewMbqEtForum) {
                        foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
                            if ($oNewMbqEtForum->forumId->oriValue == $oMbqEtForumPost->forumId->oriValue) {
                                $oMbqEtForumPost->oMbqEtForum = $oNewMbqEtForum;
                            }
                        }
                    }
                    /* load oMbqEtForumTopic property */
                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    $objsMbqEtFroumTopic = $oMbqRdEtForumTopic->getObjsMbqEtForumTopic($topicIds, array('case' => 'byTopicIds', 'oFirstMbqEtForumPost' => false));  /* must set 'oFirstMbqEtForumPost' to false,otherwise will cause infinite recursion call for get oMbqEtForumTopic and oFirstMbqEtForumPost and make memory depleted!!! */
                    foreach ($objsMbqEtFroumTopic as $oNewMbqEtFroumTopic) {
                        foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
                            if ($oNewMbqEtFroumTopic->topicId->oriValue == $oMbqEtForumPost->topicId->oriValue) {
                                $oMbqEtForumPost->oMbqEtForumTopic = $oNewMbqEtFroumTopic;
                            }
                        }
                    }
                    /* load post author */
                    $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
                    $objsAuthorMbqEtUser = $oMbqRdEtUser->getObjsMbqEtUser($authorUserIds, array('case' => 'byUserIds'));
                    $postIds = array();
                    foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
                        $postIds[] = $oMbqEtForumPost->postId->oriValue;
                        foreach ($objsAuthorMbqEtUser as $oAuthorMbqEtUser) {
                            if ($oMbqEtForumPost->postAuthorId->oriValue == $oAuthorMbqEtUser->userId->oriValue) {
                                $oMbqEtForumPost->oAuthorMbqEtUser = $oAuthorMbqEtUser;
                                break;
                            }
                        }
                    }
                    ///* load attachment */
                    //$oMbqRdEtAtt = MbqMain::$oClk->newObj('MbqRdEtAtt');
                    //$objsMbqEtAtt = $oMbqRdEtAtt->getObjsMbqEtAtt($postIds, array('case' => 'byForumPostIds'));
                    //foreach ($objsMbqEtAtt as $oMbqEtAtt) {
                    //    foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
                    //        if ($oMbqEtAtt->isForumPostAtt() && ($oMbqEtAtt->postId->oriValue == $oMbqEtForumPost->postId->oriValue)) {
                    //            $oMbqEtForumPost->objsMbqEtAtt[] = $oMbqEtAtt;
                    //            break;
                    //        }
                    //    }
                    //}
                    ///* load objsNotInContentMbqEtAtt */
                    //foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
                    //    $this->makeProperty($oMbqEtForumPost, 'objsNotInContentMbqEtAtt');
                    //}
                    //foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
                    //    $this->makeProperty($oMbqEtForumPost, 'byOAuthorMbqEtUser');
                    //}
                    ///* load objsMbqEtThank property and make related properties/flags */
					$oMbqRdEtThank = MbqMain::$oClk->newObj('MbqRdEtThank');
					$objsMbqEtThank = $oMbqRdEtThank->getObjsMbqEtThank($postIds, array('case' => 'byForumPostIds'));
					foreach ($objsMbqEtThank as $oMbqEtThank) {
						foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
							if ($oMbqEtThank->key->oriValue == $oMbqEtForumPost->postId->oriValue) {
								$oMbqEtForumPost->objsMbqEtThank[] = $oMbqEtThank;
								break;
							}
						}
					}
					foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
						$oMbqEtForumPost->thankCount->setOriValue(count($oMbqEtForumPost->objsMbqEtThank));
						$isThankedByMe = false;
						if (MbqMain::hasLogin()) {
							foreach ($oMbqEtForumPost->objsMbqEtThank as $oMbqEtThank) {
								if ($oMbqEtThank->userId->oriValue == MbqMain::$oCurMbqEtUser->userId->oriValue) {
									$isThankedByMe = true;
								}
							}
						}
						if ($oMbqEtForumPost->mbqBind['oKunenaForumMessage']->authorise('thankyou') && !$isThankedByMe) {
							$oMbqEtForumPost->canThank->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canThank.range.yes'));
						} else {
							$oMbqEtForumPost->canThank->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canThank.range.no'));
						}
					}
                    /* common end */
                    if (isset($mbqOpt['oMbqDataPage'])) {
                        $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                        $oMbqDataPage->datas = $objsMbqEtForumPost;
                        return $oMbqDataPage;
                    } else {
                        return $objsMbqEtForumPost;
                    }
                    break;
                }
        }
    }
    /**
     * init one forum post by condition
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byObj' means init forum post by obj from viewtopic.php page
     * $mbqOpt['case'] = 'byPostId' means init forum post by post id
     * $mbqOpt['withAuthor'] = true means load post author,default is true
     * $mbqOpt['withAtt'] = true means load post attachments,default is true
     * $mbqOpt['withObjsNotInContentMbqEtAtt'] = true means load the attachement objs not in the content,default is true
     * $mbqOpt['oMbqEtForum'] = true means load oMbqEtForum property of this post,default is true
     * $mbqOpt['oMbqEtForumTopic'] = true means load oMbqEtForumTopic property of this post,default is true
     * $mbqOpt['objsMbqEtThank'] = true means load objsMbqEtThank property of this post,default is true
     * @return  Mixed
     */
    public function initOMbqEtForumPost($var, $mbqOpt) {
        global $db, $auth, $user, $config, $template, $cache, $phpEx, $phpbb_root_path, $phpbb_home, $topic_data, $support_post_thanks;
        if($mbqOpt['case'] == 'byPostId') {
            global $request, $template, $user, $auth, $phpbb_home, $config, $attachment_by_id, $forum_id, $topic_id, $topic_data, $total_posts, $can_subscribe, $post_data;
            $topic_data = null;
            $post_data = null;
            $postId = $var;
            $request_file = 'viewtopic';
            overwriteRequestParam('p', $postId);
            overwriteRequestParam('onlyOnePost', true);
            requireExtLibrary('viewtopic_clone');
            $newMbqOpt['case'] = 'byRow';
            $newMbqOpt['oMbqEtForum'] = true;
            $newMbqOpt['oMbqEtForumTopic'] = true;
            $newMbqOpt['oMbqEtUser'] = true;
            $posts = array();
            $oMbqEtForumPost = null;
            if(isset($post_data))
            {
                foreach($post_data as $item)
                {
                    if($item['POST_ID'] == $postId)
                    {
                        $oMbqEtForumPost = $this->initOMbqEtForumPost($item, $newMbqOpt);
                        break;
                    }
                }
            }
            if(isset($oMbqEtForumPost) && isset($topic_data))
            {
                $oMbqEtForumPost->position->setOriValue($topic_data['prev_posts'] + 1);
            }
            return $oMbqEtForumPost;
        }
        else if($mbqOpt['case'] == 'byRow') {

            $row = $var;


            if (isset($row['S_IGNORE_POST']) && $row['S_IGNORE_POST'])
            {
                $row['MESSAGE'] =  $row['L_IGNORE_POST'] . "[spoiler]{$row[MESSAGE]}[/spoiler]";
            }

            $can_ban_user = $auth->acl_get('m_ban') && $row['POST_AUTHOR_ID'] != $user->data['user_id'];


            $forum_id = $row['bind']['forum_id'];
            $topic_id = $row['bind']['topic_id'];

            $oMbqEtForumPost = MbqMain::$oClk->newObj('MbqEtForumPost');
            $oMbqEtForumPost->postId->setOriValue($row['POST_ID']);
            if(isset($row['PARENT_POST_ID']))
            {
                $oMbqEtForumPost->parentPostId->setOriValue($row['PARENT_POST_ID']);
            }
            $oMbqEtForumPost->forumId->setOriValue($row['bind']['forum_id']);
            $oMbqEtForumPost->topicId->setOriValue($row['bind']['topic_id']);
            $oMbqEtForumPost->postTitle->setOriValue(basic_clean($row['POST_SUBJECT']));
            $message = $row['MESSAGE'];
            if (isset($row['S_HAS_ATTACHMENTS']) && $row['S_HAS_ATTACHMENTS'])
            {
                $attachCount = 0;
                foreach($row['bind']['post_attachments'] as $attachment)
                {
                    if(preg_match('/\[attachment=' . $attachCount  . '\](.*?)\[\/attachment(.*?)\]/si', $message))
                    {
                       $oMbqRdAtt = MbqMain::$oClk->newObj('MbqRdEtAtt');
                        $attachment['forum_id'] = $row['bind']['forum_id'];
                        $oMbqEtAtt = $oMbqRdAtt->initOMbqEtAtt($attachment, array('case' => 'byRow'));
                        $oMbqEtAtt->forumId->setOriValue( $oMbqEtForumPost->forumId->oriValue);
                        $message = preg_replace('/\[attachment=' . $attachCount  . '\](.*?)\[\/attachment(.*?)\]/si',  $oMbqEtAtt->contentType->oriValue == MbqBaseFdt::getFdt('MbqFdtAtt.MbqEtAtt.contentType.range.image') ?  '[img]' . $oMbqEtAtt->url->oriValue . '[/img]' : '[url]' . $oMbqEtAtt->url->oriValue . '[/url]',$message);
                        $oMbqEtForumPost->objsMbqEtAtt[] = $oMbqEtAtt;
                    }
                    else
                    {
                        $oMbqRdAtt = MbqMain::$oClk->newObj('MbqRdEtAtt');
                        $attachment['forum_id'] = $row['bind']['forum_id'];
                        $oMbqEtAtt = $oMbqRdAtt->initOMbqEtAtt($attachment, array('case' => 'byRow'));
                        $oMbqEtAtt->forumId->setOriValue( $oMbqEtForumPost->forumId->oriValue);
                        $oMbqEtForumPost->objsNotInContentMbqEtAtt[] = $oMbqEtAtt;
                    }
                    $attachCount++;
                }
            }

            $message = preg_replace('#\[attachment=([0-9]+)\](.*?)\[\/attachment\]#uis', '\\2', $message);

            $message =  preg_replace('/\x1A/', '', $message);
            $messageHtml = post_html_clean($message, true);
            $messageNoHtml = post_html_clean($message, false);
            $messageAdvancedHtml = post_html_clean($message, 2);

            $oMbqEtForumPost->postContent->setOriValue($message);
            $oMbqEtForumPost->postContent->setAppDisplayValue($message);
            $oMbqEtForumPost->postContent->setTmlDisplayValue($messageHtml);
            $oMbqEtForumPost->postContent->setTmlDisplayValueNoHtml($messageNoHtml);
            $oMbqEtForumPost->postContent->setTmlDisplayValueAdvancedHtml($messageAdvancedHtml);

            $oMbqEtForumPost->shortContent->setOriValue(basic_clean(process_short_content($messageNoHtml)));
            $oMbqEtForumPost->postAuthorId->setOriValue(isset($row['POST_AUTHOR_ID']) ? $row['POST_AUTHOR_ID'] : $row['bind']['user_id']);
            //$oMbqEtForumPost->attachmentIdArray->setOriValue($var['post_author_id']);
            //$oMbqEtForumPost->groupId->setOriValue($var['post_author_id']);
            $oMbqEtForumPost->state->setOriValue(isset($var['S_POST_UNAPPROVED']) && $var['S_POST_UNAPPROVED'] ? 1 : 0);
            $oMbqEtForumPost->isOnline->setOriValue(isset($row['S_ONLINE']) && $row['S_ONLINE']);
            $oMbqEtForumPost->canEdit->setOriValue(isset($row['U_EDIT']) && $row['U_EDIT']);
            $oMbqEtForumPost->postTime->setOriValue($row['bind']['post_time']);
            //$oMbqEtForumPost->allowSmilies->setOriValue($row['bind']['enable_smilies'] ? true : false);
            //$oMbqEtForumPost->position->setOriValue($row['prev_posts'] + 1);
            $oMbqEtForumPost->canThank->setOriValue(isset($row['bind']['can_thank']) && $row['bind']['can_thank']);
			$oMbqEtForumPost->thankCount->setOriValue($row['bind']['post_author_id']);
			$oMbqEtForumPost->canUnthank->setOriValue(isset($row['bind']['can_unthank']) && $row['bind']['can_unthank']);
            //$oMbqEtForumPost->canLike->setOriValue($var['post_author_id']);
            //$oMbqEtForumPost->isLiked->setOriValue($var['post_author_id']);
            $oMbqEtForumPost->isThanked->setOriValue(isset($row['bind']['thanks_info']));
            //$oMbqEtForumPost->likeCount->setOriValue($var['post_author_id']);

            $oMbqEtForumPost->showReason->setOriValue($auth->acl_get('m_edit', $forum_id));
            $oMbqEtForumPost->editReason->setOriValue(isset($row['bind']['post_edit_reason']) ? $row['bind']['post_edit_reason'] : '');

            if(getPHPBBVersion() == '3.0')
            {
                $oMbqEtForumPost->isDeleted->setOriValue(false);
            }
            else
            {
                $oMbqEtForumPost->isDeleted->setOriValue($row['bind']['post_visibility'] == ITEM_DELETED);
            }
            $oMbqEtForumPost->canDelete->setOriValue($auth->acl_get('m_delete', $forum_id) || ($auth->acl_get('m_softdelete', $forum_id) && $row['bind']['post_visibility'] != ITEM_DELETED));
            $oMbqEtForumPost->isApproved->setOriValue(isset($row['S_POST_UNAPPROVED']) && !$row['S_POST_UNAPPROVED']);
            $oMbqEtForumPost->canApprove->setOriValue($auth->acl_get('m_approve', $forum_id));
            $oMbqEtForumPost->canMove->setOriValue($auth->acl_get('m_split', $forum_id));
            $oMbqEtForumPost->canReport->setOriValue($auth->acl_get('f_report', $oMbqEtForumPost->forumId->oriValue));
            //$oMbqEtForumPost->modByUserId->setOriValue($var['post_author_id']);
            //$oMbqEtForumPost->deleteByUserId->setOriValue($var['post_author_id']);
            //$oMbqEtForumPost->deleteReason->setOriValue($var['post_author_id']);
            //$oMbqEtForumPost->authorIconUrl->setOriValue($row['POSTER_AVATAR']);
            //$oMbqEtForumPost->canUnlike->setOriValue($var['post_author_id']);
            //$oMbqEtForumPost->canUnthank->setOriValue($var['post_author_id']);

            if(!empty($row['EDITER_UID']) && $config['display_last_edited'])
            {
                $oMbqEtForumPost->modByUserId->setOriValue($row['EDITER_UID']);
            }

			/** COMMENTED START * */
			if ($support_post_thanks) {
				if (!$row['S_GLOBAL_POST_THANKS'] && !$row['S_POST_ANONYMOUS'] && $auth->acl_get('f_thanks', $forum_id) && $user->data['user_id'] != ANONYMOUS && $user->data['user_id'] != $row['POSTER_ID'] && $row['S_ALREADY_THANKED']) {
					$row['bind']['can_unthank'] = true;
					$row['bind']['can_thank'] = false;
				} elseif (!$row['S_GLOBAL_POST_THANKS'] && !$row['S_POST_ANONYMOUS'] && $auth->acl_get('f_thanks', $forum_id) && $user->data['user_id'] != ANONYMOUS && $user->data['user_id'] != $row['POSTER_ID'] && !$row['S_ALREADY_THANKED']) {
					if (!empty($config['thanks_only_first_post']) && $key == 0) {
						$row['bind']['can_thank'] = true;
						$row['bind']['can_unthank'] = false;
					} else if (!empty($config['thanks_only_first_post'])) {
						$row['bind']['can_thank'] = false;
						$row['bind']['can_unthank'] = false;
					} else {
						$row['bind']['can_thank'] = true;
						$row['bind']['can_unthank'] = false;
					}
				}
				if ($row['THANKS'] && $row['THANKS_POSTLIST_VIEW'] && !$row['S_POST_ANONYMOUS'] && empty($user->data['is_bot'])) {
					global $thanksHelper;

					$count = 0;
					$thank_list = array();
					$thank_object_list = array();
					$maxcount = isset($config['thanks_number_post']) ? $config['thanks_number_post'] : (
						isset($config['thanks_number']) ? $config['thanks_number'] : 10);
					$thankers = NULL;
					if ($thanksHelper) {
						$thankers = $thanksHelper->get_thankers();
						foreach ($thankers as $thanker) {
							if ($count >= $maxcount) {
								break;
							}
							if ($thanker['post_id'] == $row['POST_ID']) {
								$oMbqRdEtUserT = MbqMain::$oClk->newObj('MbqRdEtUser');
								if ($objsAuthorMbqEtUserT = $oMbqRdEtUserT->getObjsMbqEtUser(array($thanker['user_id']), array('case' => 'byUserIds'))) {
									$thanks_object = MbqMain::$oClk->newObj('MbqEtThank');
									$thanks_object->key->setOriValue($row['POST_ID']);
									$thanks_object->userId->setOriValue($thanker['user_id']);
									$objsAuthorMbqEtUserTU = array_values($objsAuthorMbqEtUserT);
									$thanks_object->oMbqEtUser = $objsAuthorMbqEtUserTU[0];
									$thank_object_list[] = $thanks_object;


            //    }
            //    if ($row['THANKS'] && $row['THANKS_POSTLIST_VIEW'] && !$row['S_POST_ANONYMOUS'] && empty($user->data['is_bot']))
            //    {
            //        global $thankers;

            //        $count = 0;
									$thank_list[] = array(
										'userid' => $thanker['user_id'],
										'username' => basic_clean($thanker['username']),
										'user_type' => check_return_user_type($thanker['user_id']),
										'tapatalk'  => new xmlrpcval(is_tapatalk_user($row['user_id']), 'string'),
									);
									$count++;
								}
							}
						}
					}
					if (!empty($thank_list)) {
						$row['bind']['thanks_info'] = $thank_list;
						$row['bind']['thank_count'] = count($thank_list);
						$oMbqEtForumPost->thankCount->setOriValue(count($thank_list));
						$oMbqEtForumPost->objsMbqEtThank = $thank_object_list;
					}
				}
			}
			$oMbqEtForumPost->canThank->setOriValue(isset($row['bind']['can_thank']) && $row['bind']['can_thank']);
			$oMbqEtForumPost->canUnthank->setOriValue(isset($row['bind']['can_unthank']) && $row['bind']['can_unthank']);
//			$oMbqEtForumPost->canUnthank->setOriValue($var['post_author_id']);
			$oMbqEtForumPost->isThanked->setOriValue(isset($row['bind']['thanks_info']) && count($row['bind']['thanks_info']));
			/** COMMENTED END * */
            if($mbqOpt['oMbqEtForum'])
            {
                $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
                $oMbqEtForumPost->oMbqEtForum = $oMbqRdEtForum->initOMbqEtForum($forum_id, array('case' => 'byForumId'));
            }
            if($mbqOpt['oMbqEtForumTopic'])
            {
                $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                $oMbqEtForumPost->oMbqEtForumTopic = $oMbqRdEtForumTopic->initOMbqEtForumTopic($topic_id, array('case' => 'byTopicId'));
            }
            if($mbqOpt['oMbqEtUser'])
            {
                $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
                $oMbqEtForumPost->oAuthorMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($oMbqEtForumPost->postAuthorId->oriValue, array('case' => 'byUserId'));
                if ($oMbqEtForumPost->postAuthorId->oriValue == ANONYMOUS && isset($row['bind']['post_username']) && $row['bind']['post_username']) {
                    /** @var MbqEtForumPost $oMbqEtForumPost */
                    $oMbqEtForumPost->postAuthorName->setOriValue($row['bind']['post_username']);
                }
            }
            $oMbqEtForumPost->mbqBind = $row;
            return $oMbqEtForumPost;
        }

    }
    public function prepare($postRows)
    {
        if(!empty($postRows))
        {
            $this->prepareUsers($postRows);
        }
    }
    public function prepareUsers($postRows)
    {
        $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
        $userIdsToPreload = array();
        foreach($postRows as $row)
        {
            $authorId = isset($row['POST_AUTHOR_ID']) ? $row['POST_AUTHOR_ID'] : $row['bind']['user_id'];
            if(!in_array($authorId, $userIdsToPreload))
            {
                $userIdsToPreload[] = $authorId;
            }
        }
        $oMbqRdEtUser->getObjsMbqEtUser($userIdsToPreload, array('case'=>'byUserIds'));
    }
    /**
     * return raw post content
     *
     * @return  String
     */
    public function getRawPostContent($oMbqEtForumPost) {
        global $phpbb_root_path, $phpEx;
        $post_data = $oMbqEtForumPost->mbqBind['bind'];
        include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

        $message_parser = new parse_message();
        $message_parser->message = $post_data['post_text'];
        $message_parser->decode_message($post_data['bbcode_uid']);
        return html_entity_decode($message_parser->message);
    }
     /**
     * return raw post content
     *
     * @return  String
     */
    public function getRawPostContentOriginal($oMbqEtForumPost) {
        global $phpbb_root_path, $phpEx;
        $post_data = $oMbqEtForumPost->mbqBind['bind'];
        include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

        $message_parser = new parse_message();
        $message_parser->message = $post_data['post_text'];
        $message_parser->decode_message($post_data['bbcode_uid']);
        return html_entity_decode($message_parser->message);
    }

     /**
     * return raw post content
     *
     * @return  String
     */
    public function getQuotePostContent($oMbqEtForumPost) {
        global $phpbb_root_path, $phpEx;
        $post_data = $oMbqEtForumPost->mbqBind['bind'];
        include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
        // Determine some vars
        if (isset($post_data['user_id']) && $post_data['user_id'] == ANONYMOUS)
        {
            $post_data['quote_username'] = (!empty($post_data['post_username'])) ? $post_data['post_username'] : $user->lang['GUEST'];
        }
        else
        {
            $post_data['quote_username'] = isset($post_data['username']) ? $post_data['username'] : '';
        }

        $message_parser = new parse_message();

        $message_parser->message = $post_data['post_text'];
        $message_parser->decode_message($post_data['bbcode_uid']);
        $message_parser->message = str_replace("&quot;", '"', $message_parser->message);
        $message_parser->message = '[quote="' . $post_data['quote_username'] . '"]' . censor_text(trim(html_entity_decode($message_parser->message))) . "[/quote]";
        return  $message_parser->message;
    }

    public function getUrl($oMbqEtForumPost)
    {
        global $phpbb_root_path, $phpEx, $db, $auth, $config;
        $postId = $oMbqEtForumPost->postId->oriValue;
        $base = new  \tas2580\seourls\event\base($auth, $config, $phpbb_root_path);
        $topicUrl = $base->generate_topic_link($oMbqEtForumPost->forumId->oriValue, $oMbqEtForumPost->oMbqEtForum->forumName->oriValue, $oMbqEtForumPost->topicId->oriValue, $oMbqEtForumPost->oMbqEtForumTopic->topicTitle->oriValue, 0, true);
        return $topicUrl . "#p$postId";
    }

}
