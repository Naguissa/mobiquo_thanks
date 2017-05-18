<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/

global $phpbb_root_path, $phpEx, $cache,$phpbb_container,$phpbb_dispatcher, $searchResults, $mobiquo_config, $user, $auth, $db, $template, $request_method, $total_match_count, $request;
//define('IN_PHPBB', true);
//$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
//$phpEx = substr(strrchr(__FILE__, '.'), 1);
//include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
//$user->session_begin();
//$auth->acl($user->data);
$user->setup('search');


// Define initial vars
$mode			= request_var('mode', '');
$search_id		= request_var('search_id', '');
$page			= request_var('page', 1);
$per_page		= request_var('perpage', 20);
$start			= max(($page - 1) * $per_page, 0);

$post_id		= request_var('p', 0);
$topic_id		= request_var('t', 0);
$view			= request_var('view', '');

$submit			= request_var('submit', false);
$keywords		= utf8_normalize_nfc(request_var('keywords', '', true));
$add_keywords	= utf8_normalize_nfc(request_var('add_keywords', '', true));
$author			= request_var('author', '', true);
$author_id		= request_var('author_id', 0);
$show_results	= ($topic_id) ? 'posts' : request_var('sr', 'posts');
$show_results	= ($show_results == 'posts') ? 'posts' : 'topics';
$search_terms	= request_var('terms', 'all');
$search_fields	= request_var('sf', 'all');
$search_child	= request_var('sc', true);

$sort_days		= request_var('st', 0);
$sort_key		= request_var('sk', 't');
$sort_dir		= request_var('sd', 'd');

$return_chars	= request_var('ch', ($topic_id) ? -1 : 200);
$search_forum	= request_var('fid', array(0));
$exclude_forum	= request_var('exclude', array(0));

//added by tapatalk
$started_by	= request_var('started_by', 0);

// We put login boxes for the case if search_id is newposts, egosearch or unreadposts
// because a guest should be able to log in even if guests search is not permitted
if($request_method == 'get_latest_topic' || $request_method == 'get_unread_topic' || $request_method == 'get_participated_topic' ||
$request_method == 'get_user_topic' || $request_method == 'get_user_reply_post' || $request_method == 'get_subscribed_topic')
{
	$config['load_search'] = 1;
}
switch ($search_id)
{
	// Egosearch is an author search
	case 'egosearch':
		$author_id = $user->data['user_id'];
		if ($user->data['user_id'] == ANONYMOUS)
		{
            trigger_error('LOGIN_EXPLAIN_EGOSEARCH');
			//login_box('', getSystemString('LOGIN_EXPLAIN_EGOSEARCH'));
		}
	break;

	// Search for unread posts needs to be allowed and user to be logged in if topics tracking for guests is disabled
	case 'unreadposts':
		if (isset($config['load_unreads_search']) && !$config['load_unreads_search'])
		{
			$template->assign_var('S_NO_SEARCH', true);
            $errmsg = getSystemString('NO_SEARCH_UNREADS') ? 'NO_SEARCH_UNREADS' : 'Sorry but searching for unread posts has been disabled on this board.';
            trigger_error($errmsg);
		}
		else if (!$config['load_anon_lastread'] && !$user->data['is_registered'])
		{
            $errmsg = getSystemString('LOGIN_EXPLAIN_UNREADSEARCH') ? 'LOGIN_EXPLAIN_UNREADSEARCH' : 'The board requires you to be registered and logged in to view your unread posts.';
			trigger_error($errmsg);
			//login_box('', getSystemString('LOGIN_EXPLAIN_UNREADSEARCH'));
		}
	break;

	// The "new posts" search uses user_lastvisit which is user based, so it should require user to log in.
	case 'newposts':
		if ($user->data['user_id'] == ANONYMOUS)
		{
            trigger_error('LOGIN_EXPLAIN_NEWPOSTS');
			//login_box('', getSystemString('LOGIN_EXPLAIN_NEWPOSTS'));
		}
	break;

	default:
		// There's nothing to do here for now ;)
	break;
}
if(empty($search_id))
{
    // Is user able to search? Has search been disabled?
    if (!$auth->acl_get('u_search') || !$auth->acl_getf_global('f_search') || !$config['load_search'])
    {
	    $template->assign_var('S_NO_SEARCH', true);
	    trigger_error('NO_SEARCH');
    }

    // Check search load limit
    if ($user->load && $config['limit_search_load'] && ($user->load > doubleval($config['limit_search_load'])))
    {
	    $template->assign_var('S_NO_SEARCH', true);
	    trigger_error('NO_SEARCH_LOAD');
    }

    // It is applicable if the configuration setting is non-zero, and the user cannot
    // ignore the flood setting, and the search is a keyword search.
    $interval = ($user->data['user_id'] == ANONYMOUS) ? $config['search_anonymous_interval'] : $config['search_interval'];
    if ($interval && !$auth->acl_get('u_ignoreflood'))
    {
        if ($user->data['user_last_search'] > time() - $interval)
        {
            $template->assign_var('S_NO_SEARCH', true);
            trigger_error($user->lang('NO_SEARCH_TIME', (int) ($user->data['user_last_search'] + $interval - time())));
        }
    }

}


// Define some vars
$limit_days		= array(0 => getSystemString('ALL_RESULTS'), 1 => getSystemString('1_DAY'), 7 => getSystemString('7_DAYS'), 14 => getSystemString('2_WEEKS'), 30 => getSystemString('1_MONTH'), 90 => getSystemString('3_MONTHS'), 180 => getSystemString('6_MONTHS'), 365 => getSystemString('1_YEAR'));
$sort_by_text	= array('a' => getSystemString('SORT_AUTHOR'), 't' => getSystemString('SORT_TIME'), 'f' => getSystemString('SORT_FORUM'), 'i' => getSystemString('SORT_TOPIC_TITLE'), 's' => getSystemString('SORT_POST_SUBJECT'));

$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

$phpbb_content_visibility = $phpbb_container->get('content.visibility');
$pagination = $phpbb_container->get('pagination');

if ($keywords || $author || $author_id || $search_id || $submit)
{
	// clear arrays
	$id_ary = array();

	// If we are looking for authors get their ids
	$author_id_ary = array();
	$sql_author_match = '';
	if ($author_id)
	{
		$author_id_ary[] = $author_id;
	}
	else if ($author)
	{
		if ((strpos($author, '*') !== false) && (utf8_strlen(str_replace(array('*', '%'), '', $author)) < $config['min_search_author_chars']))
		{
			trigger_error($user->lang('TOO_FEW_AUTHOR_CHARS', (int) $config['min_search_author_chars']));
		}

		$sql_where = (strpos($author, '*') !== false) ? ' username_clean ' . $db->sql_like_expression(str_replace('*', $db->get_any_char(), utf8_clean_string($author))) : " username_clean = '" . $db->sql_escape(utf8_clean_string($author)) . "'";

		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . "
			WHERE $sql_where
				AND user_type <> " . USER_IGNORE;
		$result = $db->sql_query_limit($sql, 100);

		while ($row = $db->sql_fetchrow($result))
		{
			$author_id_ary[] = (int) $row['user_id'];
		}
		$db->sql_freeresult($result);

		$sql_where = (strpos($author, '*') !== false) ? ' post_username ' . $db->sql_like_expression(str_replace('*', $db->get_any_char(), utf8_clean_string($author))) : " post_username = '" . $db->sql_escape(utf8_clean_string($author)) . "'";

		$sql = 'SELECT 1 as guest_post
			FROM ' . POSTS_TABLE . "
			WHERE $sql_where
				AND poster_id = " . ANONYMOUS;
		$result = $db->sql_query_limit($sql, 1);
		$found_guest_post = $db->sql_fetchfield('guest_post');
		$db->sql_freeresult($result);

		if ($found_guest_post && !$started_by)
		{
			$author_id_ary[] = ANONYMOUS;
			$sql_author_match = (strpos($author, '*') !== false) ? ' ' . $db->sql_like_expression(str_replace('*', $db->get_any_char(), utf8_clean_string($author))) : " = '" . $db->sql_escape(utf8_clean_string($author)) . "'";
		}

		if (!sizeof($author_id_ary))
		{
			trigger_error('NO_SEARCH_RESULTS');
		}
	}

	// if we search in an existing search result just add the additional keywords. But we need to use "all search terms"-mode
	// so we can keep the old keywords in their old mode, but add the new ones as required words
	if ($add_keywords)
	{
		if ($search_terms == 'all')
		{
			$keywords .= ' ' . $add_keywords;
		}
		else
		{
			$search_terms = 'all';
			$keywords = implode(' |', explode(' ', preg_replace('#\s+#u', ' ', $keywords))) . ' ' .$add_keywords;
		}
	}

	// Which forums should not be searched? Author searches are also carried out in unindexed forums
	if (empty($keywords) && sizeof($author_id_ary))
	{
		$ex_fid_ary = array_keys($auth->acl_getf('!f_read', true));
	}
	else
	{
		$ex_fid_ary = array_unique(array_merge(array_keys($auth->acl_getf('!f_read', true)), array_keys($auth->acl_getf('!f_search', true))));
	}

    // add for tapatalk
    if (isset($mobiquo_config['hide_forum_id']))
    {
        $ex_fid_ary = array_unique(array_merge($ex_fid_ary, $mobiquo_config['hide_forum_id']));
    }
    if ($exclude_forum)
    {
        $ex_fid_ary = array_unique(array_merge($ex_fid_ary, $exclude_forum));
    }
    // end

	$not_in_fid = (sizeof($ex_fid_ary)) ? 'WHERE ' . $db->sql_in_set('f.forum_id', $ex_fid_ary, true) . " OR (f.forum_password <> '' AND fa.user_id <> " . (int) $user->data['user_id'] . ')' : "";

	$sql = 'SELECT f.forum_id, f.forum_name, f.parent_id, f.forum_type, f.right_id, f.forum_password, f.forum_flags, fa.user_id
		FROM ' . FORUMS_TABLE . ' f
		LEFT JOIN ' . FORUMS_ACCESS_TABLE . " fa ON (fa.forum_id = f.forum_id
			AND fa.session_id = '" . $db->sql_escape($user->session_id) . "')
		$not_in_fid
		ORDER BY f.left_id";
	$result = $db->sql_query($sql);

	$right_id = 0;
	$reset_search_forum = true;
	$hide_forum_arr = is_array($mobiquo_config['hide_forum_id']) ? $mobiquo_config['hide_forum_id'] : array();
	while ($row = $db->sql_fetchrow($result))
	{
        if(in_array($row['parent_id'], $hide_forum_arr))
		{
			array_push($hide_forum_arr,$row['forum_id']);
		}
		if ($row['forum_password'] && $row['user_id'] != $user->data['user_id'])
		{
			$ex_fid_ary[] = (int) $row['forum_id'];
			continue;
		}

		// Exclude forums from active topics
		if (!($row['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS) && ($search_id == 'active_topics'))
		{
			$ex_fid_ary[] = (int) $row['forum_id'];
			continue;
		}

		if (sizeof($search_forum))
		{
			if ($search_child)
			{
				if (in_array($row['forum_id'], $search_forum) && $row['right_id'] > $right_id)
				{
					$right_id = (int) $row['right_id'];
				}
				else if ($row['right_id'] < $right_id)
				{
					continue;
				}
			}

			if (!in_array($row['forum_id'], $search_forum))
			{
				$ex_fid_ary[] = (int) $row['forum_id'];
				$reset_search_forum = false;
			}
		}
		if (sizeof($exclude_forum))
		{
			//if parent_id in extute add
			if($row['parent_id'] > 0 && in_array($row['parent_id'], $ex_fid_ary))
			{
				$ex_fid_ary[] = (int) $row['forum_id'];
			}
		}
	}
	$ex_fid_ary = array_unique(array_merge($ex_fid_ary,$hide_forum_arr));
	$db->sql_freeresult($result);
	$m_approve_fid_ary = array(-1);
	// find out in which forums the user is allowed to view posts
	$m_approve_posts_fid_sql = $phpbb_content_visibility->get_global_visibility_sql('post', $ex_fid_ary, 'p.');
	$m_approve_topics_fid_sql = $phpbb_content_visibility->get_global_visibility_sql('topic', $ex_fid_ary, 't.');

	if ($reset_search_forum)
	{
		$search_forum = array();
	}

	// Select which method we'll use to obtain the post_id or topic_id information
	$search_type = $config['search_type'];

	if (!class_exists($search_type))
	{
		return 'NO_SUCH_SEARCH_MODULE';
	}
	// We do some additional checks in the module to ensure it can actually be utilised
	$error = false;
	$search = new $search_type($error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher);

	if ($error)
	{
		return $error;
	}

	// let the search module split up the keywords
	if ($keywords)
	{
		$correct_query = $search->split_keywords($keywords, $search_terms);
		$common_words = $search->get_common_words();
		if (!$correct_query || (!$search->get_search_query() && !sizeof($author_id_ary) && !$search_id))
		{
			$ignored = (sizeof($common_words)) ? sprintf(getSystemString('IGNORED_TERMS_EXPLAIN'), implode(' ', $common_words)) . '<br />' : '';
			$word_length = $search->get_word_length();
			if ($word_length)
			{
				return ($ignored . $user->lang('NO_KEYWORDS', $user->lang('CHARACTERS', (int) $word_length['min']), $user->lang('CHARACTERS', (int) $word_length['max'])));
			}
			else
			{
				return ($ignored);
			}
		}
	}

	if (!$keywords && sizeof($author_id_ary))
	{
		// if it is an author search we want to show topics by default
		$show_results = ($topic_id) ? 'posts' : request_var('sr', ($search_id == 'egosearch') ? 'topics' : 'posts');
		$show_results = ($show_results == 'posts') ? 'posts' : 'topics';
	}

	// define some variables needed for retrieving post_id/topic_id information
	$sort_by_sql = array('a' => 'u.username_clean', 't' => (($show_results == 'posts') ? 'p.post_time' : 't.topic_last_post_time'), 'f' => 'f.forum_id', 'i' => 't.topic_title', 's' => (($show_results == 'posts') ? 'p.post_subject' : 't.topic_title'));

	/**
	* Event to modify the SQL parameters before pre-made searches
	*
	* @event core.search_modify_param_before
	* @var	string	keywords		String of the specified keywords
	* @var	array	sort_by_sql		Array of SQL sorting instructions
	* @var	array	ex_fid_ary		Array of excluded forum ids
	* @var	array	author_id_ary	Array of exclusive author ids
	* @var	string	search_id		The id of the search request
	* @since 3.1.3-RC1
	*/
	$vars = array(
		'keywords',
		'sort_by_sql',
		'ex_fid_ary',
		'author_id_ary',
		'search_id',
	);
	extract($phpbb_dispatcher->trigger_event('core.search_modify_param_before', compact($vars)));

	// pre-made searches
	$sql = $field = $l_search_title = '';
	if ($search_id)
	{
		switch ($search_id)
		{
			// Oh holy Bob, bring us some activity...
			case 'active_topics':
				$l_search_title = getSystemString('SEARCH_ACTIVE_TOPICS');
				$show_results = 'topics';
				$sort_key = 't';
				$sort_dir = 'd';
				$sort_days = request_var('st', 7);
				$sort_by_sql['t'] = 't.topic_last_post_time';

				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
				$s_sort_key = $s_sort_dir = '';

				$last_post_time_sql = ($sort_days) ? ' AND t.topic_last_post_time > ' . (time() - ($sort_days * 24 * 3600)) : '';

				$sql = 'SELECT t.topic_last_post_time, t.topic_id
					FROM ' . TOPICS_TABLE . " t
					WHERE t.topic_moved_id = 0
						$last_post_time_sql
						AND " . $m_approve_topics_fid_sql . '
						' . ((sizeof($ex_fid_ary)) ? ' AND ' . $db->sql_in_set('t.forum_id', $ex_fid_ary, true) : '') . '
					ORDER BY t.topic_last_post_time DESC';
				$field = 'topic_id';
			break;

			case 'unanswered':
				$l_search_title = getSystemString('SEARCH_UNANSWERED');
				$show_results = request_var('sr', 'topics');
				$show_results = ($show_results == 'posts') ? 'posts' : 'topics';
				$sort_by_sql['t'] = ($show_results == 'posts') ? 'p.post_time' : 't.topic_last_post_time';
				$sort_by_sql['s'] = ($show_results == 'posts') ? 'p.post_subject' : 't.topic_title';
				$sql_sort = 'ORDER BY ' . $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');

				$sort_join = ($sort_key == 'f') ? FORUMS_TABLE . ' f, ' : '';
				$sql_sort = ($sort_key == 'f') ? ' AND f.forum_id = p.forum_id ' . $sql_sort : $sql_sort;

				if ($sort_days)
				{
					$last_post_time = 'AND p.post_time > ' . (time() - ($sort_days * 24 * 3600));
				}
				else
				{
					$last_post_time = '';
				}

				if ($sort_key == 'a')
				{
					$sort_join = USERS_TABLE . ' u, ';
					$sql_sort = ' AND u.user_id = p.poster_id ' . $sql_sort;
				}
				if ($show_results == 'posts')
				{
					$sql = "SELECT p.post_id
						FROM $sort_join" . POSTS_TABLE . ' p, ' . TOPICS_TABLE . " t
						WHERE t.topic_posts_approved = 1
							AND p.topic_id = t.topic_id
							$last_post_time
							AND $m_approve_posts_fid_sql
							" . ((sizeof($ex_fid_ary)) ? ' AND ' . $db->sql_in_set('p.forum_id', $ex_fid_ary, true) : '') . "
							$sql_sort";
					$field = 'post_id';
				}
				else
				{
					$sql = 'SELECT DISTINCT ' . $sort_by_sql[$sort_key] . ", p.topic_id
						FROM $sort_join" . POSTS_TABLE . ' p, ' . TOPICS_TABLE . " t
						WHERE t.topic_posts_approved = 1
							AND t.topic_moved_id = 0
							AND p.topic_id = t.topic_id
							$last_post_time
							AND $m_approve_topics_fid_sql
							" . ((sizeof($ex_fid_ary)) ? ' AND ' . $db->sql_in_set('p.forum_id', $ex_fid_ary, true) : '') . "
						$sql_sort";
					$field = 'topic_id';
				}
			break;

			case 'unreadposts':
				$l_search_title = getSystemString('SEARCH_UNREAD');
				// force sorting
				$show_results = 'topics';
				$sort_key = 't';
				$sort_by_sql['t'] = 't.topic_last_post_time';
				$sql_sort = 'ORDER BY ' . $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');

				$sql_where = 'AND t.topic_moved_id = 0
					AND ' . $m_approve_topics_fid_sql . '
					' . ((sizeof($ex_fid_ary)) ? 'AND ' . $db->sql_in_set('t.forum_id', $ex_fid_ary, true) : '');

				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
				$s_sort_key = $s_sort_dir = $u_sort_param = $s_limit_days = '';
			break;

			case 'newposts':
				$l_search_title = getSystemString('SEARCH_NEW');
				// force sorting
				$show_results = (request_var('sr', 'topics') == 'posts') ? 'posts' : 'topics';
				$sort_key = 't';
				$sort_dir = 'd';
				$sort_by_sql['t'] = ($show_results == 'posts') ? 'p.post_time' : 't.topic_last_post_time';
				$sql_sort = 'ORDER BY ' . $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');

				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
				$s_sort_key = $s_sort_dir = $u_sort_param = $s_limit_days = '';

				if ($show_results == 'posts')
				{
					$sql = 'SELECT p.post_id
						FROM ' . POSTS_TABLE . ' p
						WHERE p.post_time > ' . $user->data['user_lastvisit'] . '
							AND ' . $m_approve_posts_fid_sql . '
							' . ((sizeof($ex_fid_ary)) ? ' AND ' . $db->sql_in_set('p.forum_id', $ex_fid_ary, true) : '') . "
						$sql_sort";
					$field = 'post_id';
				}
				else
				{
					$sql = 'SELECT t.topic_id
						FROM ' . TOPICS_TABLE . ' t
						WHERE t.topic_last_post_time > ' . $user->data['user_lastvisit'] . '
							AND t.topic_moved_id = 0
							AND ' . $m_approve_topics_fid_sql . '
							' . ((sizeof($ex_fid_ary)) ? 'AND ' . $db->sql_in_set('t.forum_id', $ex_fid_ary, true) : '') . "
						$sql_sort";
/*
		[Fix] queued replies missing from "view new posts" (Bug #42705 - Patch by Paul)
		- Creates temporary table, query is far from optimized

					$sql = 'SELECT t.topic_id
						FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
						WHERE p.post_time > ' . $user->data['user_lastvisit'] . '
							AND t.topic_id = p.topic_id
							AND t.topic_moved_id = 0
							AND ' . $m_approve_topics_fid_sql . "
						GROUP BY t.topic_id
						$sql_sort";
*/
					$field = 'topic_id';
				}
			break;
            // tapatalk add
			case 'latesttopics':
				// force sorting
				$show_results = 'topics';
				$sort_key = 't';
				$sort_dir = 'd';
				$sort_by_sql['t'] = 't.topic_last_post_time';
				$sql_sort = 'ORDER BY ' . $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');

				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
				$s_sort_key = $s_sort_dir = $u_sort_param = $s_limit_days = '';

				$sql = 'SELECT t.topic_id
					FROM ' . TOPICS_TABLE . ' t
					WHERE t.topic_moved_id = 0
                        	AND ' . $m_approve_topics_fid_sql  . '
						' . ((sizeof($ex_fid_ary)) ? 'AND ' . $db->sql_in_set('t.forum_id', $ex_fid_ary, true) : '') . "
					$sql_sort";
				$field = 'topic_id';
                break;
			case 'subscribedtopics':
				// force sorting
				$show_results = 'topics';
				$sort_key = 't';
				$sort_dir = 'd';
				$sort_by_sql['t'] = 't.topic_last_post_time';
				$sql_sort = 'ORDER BY ' . $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');

				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
				$s_sort_key = $s_sort_dir = $u_sort_param = $s_limit_days = '';

				$sql = 'SELECT tw.topic_id
					FROM ' . TOPICS_WATCH_TABLE . ' tw
						LEFT JOIN ' . TOPICS_TABLE . ' t ON (tw.topic_id = t.topic_id)
						LEFT JOIN ' . FORUMS_TABLE . ' f ON (t.forum_id = f.forum_id)
					WHERE tw.user_id = ' . $user->data['user_id'] . '
					AND ' . $m_approve_topics_fid_sql  . '
						' . ((sizeof($ex_fid_ary)) ? 'AND ' . $db->sql_in_set('t.forum_id', $ex_fid_ary, true) : '') . "
					$sql_sort";
				$field = 'topic_id';
			case 'egosearch':
				$l_search_title = getSystemString('SEARCH_SELF');
			break;
		}
	}

	// show_results should not change after this
	//$per_page = ($show_results == 'posts') ? $config['posts_per_page'] : $config['topics_per_page'];
	$total_match_count = 0;

	// Set limit for the $total_match_count to reduce server load
	$total_matches_limit = 1000;
	$found_more_search_matches = false;

	if ($search_id)
	{
		if ($sql)
		{
			// Only return up to $total_matches_limit+1 ids (the last one will be removed later)
			$result = $db->sql_query_limit($sql, $total_matches_limit + 1);

			while ($row = $db->sql_fetchrow($result))
			{
				$id_ary[] = (int) $row[$field];
			}
			$db->sql_freeresult($result);
		}
		else if ($search_id == 'unreadposts')
		{
			// Only return up to $total_matches_limit+1 ids (the last one will be removed later)
			$id_ary = array_keys(get_unread_topics($user->data['user_id'], $sql_where, $sql_sort, $total_matches_limit + 1));
		}
		else
		{
			$search_id = '';
		}

		$total_match_count = sizeof($id_ary);
		if ($total_match_count)
		{
			// Limit the number to $total_matches_limit for pre-made searches
			if ($total_match_count > $total_matches_limit)
			{
				$found_more_search_matches = true;
				$total_match_count = $total_matches_limit;
			}

			// Make sure $start is set to the last page if it exceeds the amount
			$start = $pagination->validate_start($start, $per_page, $total_match_count);

			$id_ary = array_slice($id_ary, $start, $per_page);
		}
		else
		{
			// Set $start to 0 if no matches were found
			//$start = 0;
		}
	}

	// make sure that some arrays are always in the same order
	sort($ex_fid_ary);
	sort($author_id_ary);

	if ($search->get_search_query())
	{
		$total_match_count = $search->keyword_search($show_results, $search_fields, $search_terms, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $m_approve_posts_fid_sql, $topic_id, $author_id_ary, $sql_author_match, $id_ary, $start, $per_page);
	}
	else if (sizeof($author_id_ary))
	{
		$firstpost_only = ($search_fields === 'firstpost' || $search_fields == 'titleonly') ? true : false;
		$total_match_count = $search->author_search($show_results, $firstpost_only, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $m_approve_posts_fid_sql, $topic_id, $author_id_ary, $sql_author_match, $id_ary, $start, $per_page);
	}

	$sql_where = '';

	if (sizeof($id_ary))
	{
		$sql_where .= $db->sql_in_set(($show_results == 'posts') ? 'p.post_id' : 't.topic_id', $id_ary);
		$sql_where .= (sizeof($ex_fid_ary)) ? ' AND (' . $db->sql_in_set('f.forum_id', $ex_fid_ary, true) . ' OR f.forum_id IS NULL)' : '';
		$sql_where .= ' AND ' . (($show_results == 'posts') ? $m_approve_posts_fid_sql : $m_approve_topics_fid_sql);
	}

	if ($show_results == 'posts')
	{
		include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
	}
	else
	{
		include_once($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	}

	$user->add_lang('viewtopic');

	// Grab icons
	$icons = $cache->obtain_icons();

	// define some vars for urls
	// A single wildcard will make the search results look ugly
	$hilit = phpbb_clean_search_string(str_replace(array('+', '-', '|', '(', ')', '&quot;'), ' ', $keywords));
	$hilit = str_replace(' ', '|', $hilit);

	$u_hilit = urlencode(htmlspecialchars_decode(str_replace('|', ' ', $hilit)));
	$u_show_results = '&amp;sr=' . $show_results;
	$u_search_forum = implode('&amp;fid%5B%5D=', $search_forum);

	$u_search = append_sid("{$phpbb_root_path}search.$phpEx", $u_sort_param . $u_show_results);
	$u_search .= ($search_id) ? '&amp;search_id=' . $search_id : '';
	$u_search .= ($u_hilit) ? '&amp;keywords=' . urlencode(htmlspecialchars_decode($keywords)) : '';
	$u_search .= ($search_terms != 'all') ? '&amp;terms=' . $search_terms : '';
	$u_search .= ($topic_id) ? '&amp;t=' . $topic_id : '';
	$u_search .= ($author) ? '&amp;author=' . urlencode(htmlspecialchars_decode($author)) : '';
	$u_search .= ($author_id) ? '&amp;author_id=' . $author_id : '';
	$u_search .= ($u_search_forum) ? '&amp;fid%5B%5D=' . $u_search_forum : '';
	$u_search .= (!$search_child) ? '&amp;sc=0' : '';
	$u_search .= ($search_fields != 'all') ? '&amp;sf=' . $search_fields : '';
	$u_search .= ($return_chars != 300) ? '&amp;ch=' . $return_chars : '';

	if ($sql_where)
	{
		if ($show_results == 'posts')
		{
			// @todo Joining this query to the one below?
			$sql = 'SELECT zebra_id, friend, foe
				FROM ' . ZEBRA_TABLE . '
				WHERE user_id = ' . $user->data['user_id'];
			$result = $db->sql_query($sql);

			$zebra = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$zebra[($row['friend']) ? 'friend' : 'foe'][] = $row['zebra_id'];
			}
			$db->sql_freeresult($result);

			$sql_array = array(
				'SELECT'	=> 'p.*, f.forum_id, f.forum_name, t.*, u.username, u.username_clean, u.user_sig, u.user_sig_bbcode_uid, u.user_colour',
				'FROM'		=> array(
					POSTS_TABLE		=> 'p',
				),
				'LEFT_JOIN' => array(
					array(
						'FROM'	=> array(TOPICS_TABLE => 't'),
						'ON'	=> 'p.topic_id = t.topic_id',
					),
					array(
						'FROM'	=> array(FORUMS_TABLE => 'f'),
						'ON'	=> 'p.forum_id = f.forum_id',
					),
					array(
						'FROM'	=> array(USERS_TABLE => 'u'),
						'ON'	=> 'p.poster_id = u.user_id',
					),
				),
				'WHERE'	=> $sql_where,
				'ORDER_BY' => $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC'),
			);

			/**
			* Event to modify the SQL query before the posts data is retrieved
			*
			* @event core.search_get_posts_data
			* @var	array	sql_array		The SQL array
			* @var	array	zebra			Array of zebra data for the current user
			* @var	int		total_match_count	The total number of search matches
			* @var	string	keywords		String of the specified keywords
			* @var	array	sort_by_sql		Array of SQL sorting instructions
			* @var	string	s_sort_dir		The sort direction
			* @var	string	s_sort_key		The sort key
			* @var	string	s_limit_days	Limit the age of results
			* @var	array	ex_fid_ary		Array of excluded forum ids
			* @var	array	author_id_ary	Array of exclusive author ids
			* @var	string	search_fields	The data fields to search in
			* @var	int		search_id		The id of the search request
			* @var	int		start			The starting id of the results
			* @since 3.1.0-b3
			*/
			$vars = array(
				'sql_array',
				'zebra',
				'total_match_count',
				'keywords',
				'sort_by_sql',
				's_sort_dir',
				's_sort_key',
				's_limit_days',
				'ex_fid_ary',
				'author_id_ary',
				'search_fields',
				'search_id',
				'start',
			);
			extract($phpbb_dispatcher->trigger_event('core.search_get_posts_data', compact($vars)));

			$sql = $db->sql_build_query('SELECT', $sql_array);
		}
		else
		{
			$sql_from = TOPICS_TABLE . ' t
				LEFT JOIN ' . FORUMS_TABLE . ' f ON (f.forum_id = t.forum_id)
				LEFT JOIN ' . POSTS_TABLE . ' TT_lp ON (TT_lp.post_id = t.topic_last_post_id)
			' . (($sort_key == 'a') ? ' LEFT JOIN ' . USERS_TABLE . ' u ON (u.user_id = t.topic_poster) ' : '');
			$sql_select = 't.*, f.forum_id, f.forum_name, TT_lp.post_text,TT_lp.bbcode_uid';

			if ($user->data['is_registered'])
			{
				if ($config['load_db_track'] && $author_id !== $user->data['user_id'])
				{
					$sql_from .= ' LEFT JOIN ' . TOPICS_POSTED_TABLE . ' TT_tp ON (TT_tp.user_id = ' . $user->data['user_id'] . '
						AND t.topic_id = TT_tp.topic_id)';
					$sql_select .= ', TT_tp.topic_posted';
				}

				if ($config['load_db_lastread'])
				{
					$sql_from .= ' LEFT JOIN ' . TOPICS_TRACK_TABLE . ' TT_tt ON (TT_tt.user_id = ' . $user->data['user_id'] . '
							AND t.topic_id = TT_tt.topic_id)
						LEFT JOIN ' . FORUMS_TRACK_TABLE . ' TT_ft ON (TT_ft.user_id = ' . $user->data['user_id'] . '
							AND TT_ft.forum_id = f.forum_id)';
					$sql_select .= ', TT_tt.mark_time, TT_ft.mark_time as f_mark_time';
				}
			}

            if ($config['load_anon_lastread'] || ($user->data['is_registered'] && !$config['load_db_lastread']))
            {
               $tracking_topics = $request->variable($config['cookie_name'] . '_track', '', true, \phpbb\request\request_interface::COOKIE);
               $tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();
            }

			/**
			* Event to modify the SQL query before the topic data is retrieved
			*
			* @event core.search_get_topic_data
			* @var	string	sql_select		The SQL SELECT string used by search to get topic data
			* @var	string	sql_from		The SQL FROM string used by search to get topic data
			* @var	string	sql_where		The SQL WHERE string used by search to get topic data
			* @var	int		total_match_count	The total number of search matches
			* @since 3.1.0-a1
			* @changed 3.1.0-RC5 Added total_match_count
			*/
			$vars = array('sql_select', 'sql_from', 'sql_where', 'total_match_count');
			extract($phpbb_dispatcher->trigger_event('core.search_get_topic_data', compact($vars)));

			$sql = "SELECT $sql_select
				FROM $sql_from
				WHERE $sql_where";
			$sql .= ' ORDER BY ' . $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');
		}
		$result = $db->sql_query($sql);
		$result_topic_id = 0;

		$rowset = array();

		if ($show_results == 'topics')
		{
			$forums = $rowset = $shadow_topic_list = array();
			while ($row = $db->sql_fetchrow($result))
			{
                // tapatalk add
                // We pre-process some variables here for later usage
                $row['post_text'] = censor_text($row['post_text']);

                $text_only_message = $row['post_text'];
                // make list items visible as such
                if ($row['bbcode_uid'])
                {
                    $text_only_message = str_replace('[*:' . $row['bbcode_uid'] . ']', '&sdot;&nbsp;', $text_only_message);
                    // no BBCode in text only message
                    strip_bbcode($text_only_message, $row['bbcode_uid']);
                }

                $row['post_text'] = $text_only_message;
                $row['display_text_only'] = true;
                // tapatalk end

				$row['forum_id'] = (int) $row['forum_id'];
				$row['topic_id'] = (int) $row['topic_id'];

				if ($row['topic_status'] == ITEM_MOVED)
				{
					$shadow_topic_list[$row['topic_moved_id']] = $row['topic_id'];
				}

				$rowset[$row['topic_id']] = $row;

				if (!isset($forums[$row['forum_id']]) && $user->data['is_registered'] && $config['load_db_lastread'])
				{
					$forums[$row['forum_id']]['mark_time'] = $row['f_mark_time'];
				}
				$forums[$row['forum_id']]['topic_list'][] = $row['topic_id'];
				$forums[$row['forum_id']]['rowset'][$row['topic_id']] = &$rowset[$row['topic_id']];
			}
			$db->sql_freeresult($result);

			// If we have some shadow topics, update the rowset to reflect their topic information
			if (sizeof($shadow_topic_list))
			{
				$sql = 'SELECT *
					FROM ' . TOPICS_TABLE . '
					WHERE ' . $db->sql_in_set('topic_id', array_keys($shadow_topic_list));
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$orig_topic_id = $shadow_topic_list[$row['topic_id']];

					// We want to retain some values
					$row = array_merge($row, array(
						'topic_moved_id'	=> $rowset[$orig_topic_id]['topic_moved_id'],
						'topic_status'		=> $rowset[$orig_topic_id]['topic_status'],
						'forum_name'		=> $rowset[$orig_topic_id]['forum_name'])
					);

					$rowset[$orig_topic_id] = $row;
				}
				$db->sql_freeresult($result);
			}
			unset($shadow_topic_list);

			foreach ($forums as $forum_id => $forum)
			{
				if ($user->data['is_registered'] && $config['load_db_lastread'])
				{
					$topic_tracking_info[$forum_id] = get_topic_tracking($forum_id, $forum['topic_list'], $forum['rowset'], array($forum_id => $forum['mark_time']));
				}
				else if ($config['load_anon_lastread'] || $user->data['is_registered'])
				{
                    if(empty($forum_id))
					{
						continue;
					}
					$topic_tracking_info[$forum_id] = get_complete_topic_tracking($forum_id, $forum['topic_list']);

					if (!$user->data['is_registered'])
					{
						$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? (int) (base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate']) : 0;
					}
				}
			}
			unset($forums);
		}
		else
		{
			$bbcode_bitfield = $text_only_message = '';
			$attach_list = array();

			while ($row = $db->sql_fetchrow($result))
			{
				// We pre-process some variables here for later usage
				$row['post_text'] = censor_text($row['post_text']);

				$text_only_message = $row['post_text'];
				// make list items visible as such
				if ($row['bbcode_uid'])
				{
					$text_only_message = str_replace('[*:' . $row['bbcode_uid'] . ']', '&sdot;&nbsp;', $text_only_message);
					// no BBCode in text only message
					strip_bbcode($text_only_message, $row['bbcode_uid']);
				}
/*
				if ($return_chars == -1 || utf8_strlen($text_only_message) < ($return_chars + 3))
				{
					$row['display_text_only'] = false;
					$bbcode_bitfield = $bbcode_bitfield | base64_decode($row['bbcode_bitfield']);

					// Does this post have an attachment? If so, add it to the list
					if ($row['post_attachment'] && $config['allow_attachments'])
					{
						$attach_list[$row['forum_id']][] = $row['post_id'];
					}
				}
				else
				{*/
					$row['post_text'] = $text_only_message;
					$row['display_text_only'] = true;
				//}

				$rowset[] = $row;
			}
			$db->sql_freeresult($result);

			unset($text_only_message);

			// Instantiate BBCode if needed
			if ($bbcode_bitfield !== '')
			{
				include_once($phpbb_root_path . 'includes/bbcode.' . $phpEx);
				$bbcode = new bbcode(base64_encode($bbcode_bitfield));
			}

			// Pull attachment data
			if (sizeof($attach_list))
			{
				$use_attach_list = $attach_list;
				$attach_list = array();

				foreach ($use_attach_list as $forum_id => $_list)
				{
					if ($auth->acl_get('u_download') && $auth->acl_get('f_download', $forum_id))
					{
						$attach_list = array_merge($attach_list, $_list);
					}
				}
			}

			if (sizeof($attach_list))
			{
				$sql = 'SELECT *
					FROM ' . ATTACHMENTS_TABLE . '
					WHERE ' . $db->sql_in_set('post_msg_id', $attach_list) . '
						AND in_message = 0
					ORDER BY filetime DESC, post_msg_id ASC';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$attachments[$row['post_msg_id']][] = $row;
				}
				$db->sql_freeresult($result);
			}
		}
/*
		if ($hilit)
		{
			// Remove bad highlights
			$hilit_array = array_filter(explode('|', $hilit), 'strlen');
			foreach ($hilit_array as $key => $value)
			{
				$hilit_array[$key] = phpbb_clean_search_string($value);
				$hilit_array[$key] = str_replace('\*', '\w*?', preg_quote($hilit_array[$key], '#'));
				$hilit_array[$key] = preg_replace('#(^|\s)\\\\w\*\?(\s|$)#', '$1\w+?$2', $hilit_array[$key]);
			}
			$hilit = implode('|', $hilit_array);
		}
*/
		/**
		* Modify the rowset data
		*
		* @event core.search_modify_rowset
		* @var	array	attachments				Array with posts attachments data
		* @var	string	hilit					String to highlight
		* @var	array	rowset					Array with the search results data
		* @var	string	show_results			String indicating the show results mode
		* @var	array	topic_tracking_info		Array with the topics tracking data
		* @var	string	u_hilit					Highlight string to be injected into URL
		* @var	string	view					Search results view mode
		* @var	array	zebra					Array with zebra data for the current user
		* @since 3.1.0-b4
		* @changed 3.1.0-b5 Added var show_results
		*/
        //$vars = array(
        //    'attachments',
        //    'hilit',
        //    'rowset',
        //    'show_results',
        //    'topic_tracking_info',
        //    'u_hilit',
        //    'view',
        //    'zebra',
        //);
        //extract($phpbb_dispatcher->trigger_event('core.search_modify_rowset', compact($vars)));

		foreach ($rowset as $row)
		{
			$forum_id = $row['forum_id'];
			$result_topic_id = $row['topic_id'];
			$topic_title = censor_text($row['topic_title']);
			$replies = $phpbb_content_visibility->get_count('topic_posts', $row, $forum_id) - 1;

			$view_topic_url_params = "f=$forum_id&amp;t=$result_topic_id" . (($u_hilit) ? "&amp;hilit=$u_hilit" : '');
			$view_topic_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", $view_topic_url_params);

			if ($show_results == 'topics')
			{
				if ($config['load_db_track'] && $author_id === $user->data['user_id'])
				{
					$row['topic_posted'] = 1;
				}

				$folder_img = $folder_alt = $topic_type = '';
				topic_status($row, $replies, (isset($topic_tracking_info[$forum_id][$row['topic_id']]) && $row['topic_last_post_time'] > $topic_tracking_info[$forum_id][$row['topic_id']]) ? true : false, $folder_img, $folder_alt, $topic_type);

				$unread_topic = (isset($topic_tracking_info[$forum_id][$row['topic_id']]) && $row['topic_last_post_time'] > $topic_tracking_info[$forum_id][$row['topic_id']]) ? true : false;

				$topic_unapproved = (($row['topic_visibility'] == ITEM_UNAPPROVED || $row['topic_visibility'] == ITEM_REAPPROVE) && $auth->acl_get('m_approve', $forum_id)) ? true : false;
				$posts_unapproved = ($row['topic_visibility'] == ITEM_APPROVED && $row['topic_posts_unapproved'] && $auth->acl_get('m_approve', $forum_id)) ? true : false;
				$topic_deleted = $row['topic_visibility'] == ITEM_DELETED;
				$u_mcp_queue = ($topic_unapproved || $posts_unapproved) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=' . (($topic_unapproved) ? 'approve_details' : 'unapproved_posts') . "&amp;t=$result_topic_id", true, $user->session_id) : '';
				$u_mcp_queue = (!$u_mcp_queue && $topic_deleted) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=queue&amp;mode=deleted_topics&amp;t=$result_topic_id", true, $user->session_id) : $u_mcp_queue;
/*
				$row['topic_title'] = preg_replace('#(?!<.*)(?<!\w)(' . $hilit . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">$1</span>', $row['topic_title']);

				// tapatalk add
				$row['post_text'] = get_context($row['post_text'], array_filter(explode('|', $hilit), 'strlen'), $return_chars);
 */
				$tpl_ary = array(
					'TOPIC_AUTHOR'				=> get_username_string('username', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
					'TOPIC_AUTHOR_COLOUR'		=> get_username_string('colour', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
					'TOPIC_AUTHOR_FULL'			=> get_username_string('full', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
					'FIRST_POST_TIME'			=> $row['topic_time'],
					'LAST_POST_SUBJECT'			=> $row['topic_last_post_subject'],
					'LAST_POST_TIME'			=> $row['topic_last_post_time'],
					'LAST_VIEW_TIME'			=> $row['topic_last_view_time'],

                    // tapatalk add
					'LAST_POST_ID'              => $row['topic_last_post_id'],
					'TOPIC_AUTHOR_ID'			=> $row['topic_poster'],
					'FIRST_POST_TIMESTAMP'		=> $row['topic_time'],
					'LAST_POST_TIMESTAMP'		=> $row['topic_last_post_time'],
					'LAST_POSTER_ID'			=> $row['topic_last_poster_id'],
					//'LAST_POSTER_AVATAR'		=> get_user_avatar_url($row['user_avatar'], $row['user_avatar_type']),
					'LAST_POST_PREV'			=> $row['post_text'],
					'S_POST_STICKY'				=> ($row['topic_type'] == POST_STICKY) ? true : false,
					'S_TOPIC_LOCKED'			=> ($row['topic_status'] == ITEM_LOCKED) ? true : false,

                    'LAST_POST_AUTHOR'			=> get_username_string('username', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
					'LAST_POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
					'LAST_POST_AUTHOR_FULL'		=> get_username_string('full', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),

					'TOPIC_TYPE'		=> $topic_type,

					'TOPIC_IMG_STYLE'		=> $folder_img,
					'TOPIC_FOLDER_IMG'		=> $user->img($folder_img, $folder_alt),
					'TOPIC_FOLDER_IMG_ALT'	=> $user->lang[$folder_alt],

					'TOPIC_ICON_IMG'		=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : '',
					'TOPIC_ICON_IMG_WIDTH'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['width'] : '',
					'TOPIC_ICON_IMG_HEIGHT'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['height'] : '',
					'ATTACH_ICON_IMG'		=> ($auth->acl_get('u_download') && $auth->acl_get('f_download', $forum_id) && $row['topic_attachment']) ? $user->img('icon_topic_attach', getSystemString('TOTAL_ATTACHMENTS')) : '',
					'UNAPPROVED_IMG'		=> ($topic_unapproved || $posts_unapproved) ? $user->img('icon_topic_unapproved', ($topic_unapproved) ? 'TOPIC_UNAPPROVED' : 'POSTS_UNAPPROVED') : '',

					'S_TOPIC_TYPE'			=> $row['topic_type'],
					'S_USER_POSTED'			=> (!empty($row['topic_posted'])) ? true : false,
					'S_UNREAD_TOPIC'		=> $unread_topic,

					'S_TOPIC_REPORTED'		=> (!empty($row['topic_reported']) && $auth->acl_get('m_report', $forum_id)) ? true : false,
					'S_TOPIC_UNAPPROVED'	=> $topic_unapproved,
					'S_POSTS_UNAPPROVED'	=> $posts_unapproved,
					'S_TOPIC_DELETED'		=> $topic_deleted,
					'S_HAS_POLL'			=> ($row['poll_start']) ? true : false,

					'U_LAST_POST'			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", $view_topic_url_params . '&amp;p=' . $row['topic_last_post_id']) . '#p' . $row['topic_last_post_id'],
					'U_LAST_POST_AUTHOR'	=> get_username_string('profile', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
					'U_TOPIC_AUTHOR'		=> get_username_string('profile', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
					'U_NEWEST_POST'			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", $view_topic_url_params . '&amp;view=unread') . '#unread',
					'U_MCP_REPORT'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports&amp;mode=reports&amp;t=' . $result_topic_id, true, $user->session_id),
					'U_MCP_QUEUE'			=> $u_mcp_queue,
                     'bind' => $row,
				);
			}
			else
			{
				if ((isset($zebra['foe']) && in_array($row['poster_id'], $zebra['foe'])) && (!$view || $view != 'show' || $post_id != $row['post_id']))
				{
					$template->assign_block_vars('searchresults', array(
						'S_IGNORE_POST' => true,

						'L_IGNORE_POST' => sprintf(getSystemString('POST_BY_FOE'), $row['username'], "<a href=\"$u_search&amp;start=$start&amp;p=" . $row['post_id'] . '&amp;view=show#p' . $row['post_id'] . '">', '</a>'))
					);

					continue;
				}

				// Replace naughty words such as farty pants
				$row['post_subject'] = censor_text($row['post_subject']);
/*
				if ($row['display_text_only'])
				{

					// now find context for the searched words
					$row['post_text'] = get_context($row['post_text'], array_filter(explode('|', $hilit), 'strlen'), $return_chars);

					$row['post_text'] = bbcode_nl2br($row['post_text']);
				}
				else
				{
					$parse_flags = ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
					$row['post_text'] = generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $parse_flags, false);

					if (!empty($attachments[$row['post_id']]))
					{
						parse_attachments($forum_id, $row['post_text'], $attachments[$row['post_id']], $update_count);

						// we only display inline attachments
						unset($attachments[$row['post_id']]);
					}
				}

				if ($hilit)
				{
					// post highlighting
					$row['post_text'] = preg_replace('#(?!<.*)(?<!\w)(' . $hilit . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">$1</span>', $row['post_text']);
					$row['post_subject'] = preg_replace('#(?!<.*)(?<!\w)(' . $hilit . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">$1</span>', $row['post_subject']);
				}
*/
				$tpl_ary = array(
					'POST_AUTHOR_FULL'		=> get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
					'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
					'POST_AUTHOR'			=> get_username_string('username', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
					'U_POST_AUTHOR'			=> get_username_string('profile', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),

                    // tapatalk add
					'POST_AUTHOR_ID'	=> $row['poster_id'],
					'POST_TIMESTAMP'	=> $row['post_time'],
			//		'POSTER_AVATAR'		=> get_user_avatar_url($row['user_avatar'], $row['user_avatar_type']),
			//		'POST_APPROVED'		=> $row['post_approved'],
					'TOPIC_LAST_POST_ID'=> $row['topic_last_post_id'],
					'POST_EDIT_LOCKED'  => $row['post_edit_locked'],

					'POST_SUBJECT'		=> $row['post_subject'],
					'POST_DATE'			=> (!empty($row['post_time'])) ? $row['post_time'] : '',
					'MESSAGE'			=> $row['post_text'],
                    'bind' => $row,
				);
			}

			$tpl_ary = array_merge($tpl_ary, array(
                'bind' => $row,
				'FORUM_ID'			=> $forum_id,
				'TOPIC_ID'			=> $result_topic_id,
				'POST_ID'			=> ($show_results == 'posts') ? $row['post_id'] : false,

	    		'FORUM_TITLE'		=> $forum_id ? $row['forum_name'] : 'Global announcement',
				'TOPIC_TITLE'		=> $topic_title,
				'TOPIC_REPLIES'		=> $replies,
				'TOPIC_VIEWS'		=> $row['topic_views'],

				'U_VIEW_TOPIC'		=> $view_topic_url,
				'U_VIEW_FORUM'		=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id),
				'U_VIEW_POST'		=> (!empty($row['post_id'])) ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=" . $row['topic_id'] . '&amp;p=' . $row['post_id'] . (($u_hilit) ? '&amp;hilit=' . $u_hilit : '')) . '#p' . $row['post_id'] : '',
			));

			/**
			* Modify the topic data before it is assigned to the template
			*
			* @event core.search_modify_tpl_ary
			* @var	array	row				Array with topic data
			* @var	array	tpl_ary			Template block array with topic data
			* @var	string	show_results	Display topics or posts
			* @var	string	topic_title		Cleaned topic title
			* @var	int		replies			The number of topic replies
			* @var	string	view_topic_url	The URL to the topic
			* @var	string	folder_img		The folder image of the topic
			* @var	string	folder_alt		The alt attribute of the topic folder img
			* @var	int		topic_type		The topic type
			* @var	bool	unread_topic	Whether the topic has unread posts
			* @var	bool	topic_unapproved	Whether the topic is unapproved
			* @var	int		posts_unapproved	The number of unapproved posts
			* @var	bool	topic_deleted	Whether the topic has been deleted
			* @var	string	u_mcp_queue		The URL to the corresponding MCP queue page
			* @var	array	zebra			The zebra data of the current user
			* @var	array	attachments		All the attachments of the search results
			* @since 3.1.0-a1
			* @changed 3.1.0-b3 Added vars show_results, topic_title, replies,
			*		view_topic_url, folder_img, folder_alt, topic_type, unread_topic,
			*		topic_unapproved, posts_unapproved, topic_deleted, u_mcp_queue,
			*		zebra, attachments
			*/
			$vars = array(
				'row',
				'tpl_ary',
				'show_results',
				'topic_title',
				'replies',
				'view_topic_url',
				'folder_img',
				'folder_alt',
				'topic_type',
				'unread_topic',
				'topic_unapproved',
				'posts_unapproved',
				'topic_deleted',
				'u_mcp_queue',
				'zebra',
				'attachments',
			);
			extract($phpbb_dispatcher->trigger_event('core.search_modify_tpl_ary', compact($vars)));

			$template->assign_block_vars('searchresults', $tpl_ary);
            $searchResults[] = $tpl_ary;
			if ($show_results == 'topics')
			{
				$pagination->generate_template_pagination($view_topic_url, 'searchresults.pagination', 'start', $replies + 1, $config['posts_per_page'], 1, true, true);
			}
		}

		if ($topic_id && ($topic_id == $result_topic_id))
		{
			$template->assign_vars(array(
				'SEARCH_TOPIC'		=> $topic_title,
				'L_RETURN_TO_TOPIC'	=> $user->lang('RETURN_TO', $topic_title),
				'U_SEARCH_TOPIC'	=> $view_topic_url
			));
		}
	}
	unset($rowset);

}