<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\phpbb\classes;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class dl_topic extends dl_mod
{
	public static function gen_dl_topic($dl_id, $helper, $force = false)
	{
		static $dl_index;

		global $db, $user, $config, $auth;
		global $dl_index;
		global $phpbb_container;

		$language = $phpbb_container->get('language');

		if (!$config['dl_enable_dl_topic'])
		{
			return;
		}

		$sql = 'SELECT id, description, dl_topic, long_desc, file_name, extern, file_size, cat, hack_version, add_user, long_desc_uid, long_desc_flags, desc_uid, desc_flags
			FROM ' . DOWNLOADS_TABLE . '
			WHERE id = ' . (int) $dl_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row['dl_topic'] && !$force)
		{
			return;
		}

		$description	= $row['description'];
		$long_desc		= $row['long_desc'];
		$file_name		= $row['file_name'];
		$file_size		= $row['file_size'];
		$extern			= $row['extern'];
		$version		= $row['hack_version'];
		$add_user		= $row['add_user'];

		$long_desc_uid		= $row['long_desc_uid'];
		$long_desc_flags	= $row['long_desc_flags'];
		$desc_uid			= $row['desc_uid'];
		$desc_flags			= $row['desc_flags'];

		$long_text		= generate_text_for_edit($long_desc, $long_desc_uid, $long_desc_flags);
		$long_desc		= $long_text['text'];
		$desc_text		= generate_text_for_edit($description, $desc_uid, $desc_flags);
		$description	= $desc_text['text'];

		$cat_id		= $row['cat'];
		$dl_title	= $description;

		if ($config['dl_topic_title_catname'])
		{
			$dl_title .= ' - ' . $dl_index[$cat_id]['cat_name_nav'];
		}

		$topic_text_add = "\n[b]" . $language->lang('DL_NAME') . ":[/b] " . $description;

		if ($config['dl_topic_post_catname'])
		{
			$topic_text_add .= "\n[b]" . $language->lang('DL_CAT_NAME') . ":[/b] " . $dl_index[$cat_id]['cat_name_nav'];
		}

		$sql = 'SELECT username, user_colour
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $add_user;
		$result = $db->sql_query($sql);

		if ($db->sql_affectedrows($result))
		{
			$row = $db->sql_fetchrow($result);
			$username = $row['username'];
			$user_colour = $row['user_colour'];
			$user_id = $add_user;
		}
		else
		{
			$username = $user->data['username'];
			$user_colour = $user->data['user_colour'];
			$user_id = $user->data['user_id'];
		}

		$db->sql_freeresult($result);

		$author_url		= get_username_string('profile', $user_id, $username, $user_colour);

		if ($user_colour)
		{
			$author_link = '[url=' . $author_url . '][color=#' . $user_colour . ']' . $username . '[/color][/url]';
		}
		else
		{
			$author_link = '[url=' . $author_url . ']' . $username . '[/url]';
		}

		$topic_text_add .= "\n[b]" . $language->lang('DL_HACK_AUTOR') . ":[/b] " . $author_link;

		$topic_text_add .= "\n[b]" . $language->lang('DL_FILE_DESCRIPTION') . ":[/b] " . html_entity_decode($long_desc);
		$topic_text_add .= "\n\n[b]" . $language->lang('DL_HACK_VERSION') . ":[/b] " . $version;
		$topic_text_add .= "\n[b]" . (($extern) ? $language->lang('DL_EXTERN') : $language->lang('DL_FILE_NAME')) . ":[/b] " . $file_name;
		$topic_text_add .= (($extern) ? '' : "\n[b]" . $language->lang('DL_FILE_SIZE') . ":[/b] " . str_replace('&nbsp;', ' ', dl_format::dl_size($file_size)));

		if ($config['dl_topic_forum'] == -1)
		{
			$topic_forum	= $dl_index[$cat_id]['dl_topic_forum'];
			$topic_text		= $dl_index[$cat_id]['dl_topic_text'];
			$topic_type		= $dl_index[$cat_id]['dl_topic_type'];

			if ($dl_index[$cat_id]['topic_more_details'] == 1)
			{
				$topic_text .= $topic_text_add;
			}
			else if ($dl_index[$cat_id]['topic_more_details'] == 2)
			{
				$topic_text = $topic_text_add . "\n\n" . $topic_text;
			}
		}
		else
		{
			$topic_forum	= $config['dl_topic_forum'];
			$topic_text		= $config['dl_topic_text'];
			$topic_type		= $config['dl_topic_type'];

			if ($config['dl_topic_more_details'] == 1)
			{
				$topic_text .= $topic_text_add;
			}
			else if ($config['dl_topic_more_details'] == 2)
			{
				$topic_text = $topic_text_add . "\n\n" . $topic_text;
			}
		}

		if (!$topic_forum)
		{
			return;
		}

		$reset_perms = false;

		if (!$config['dl_diff_topic_user'] || ($config['dl_diff_topic_user'] == 2 && !$dl_index[$cat_id]['diff_topic_user']))
		{
			$sql_tmp = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $add_user;
			$result_tmp = $db->sql_query($sql_tmp);

			if ($db->sql_affectedrows($result_tmp))
			{
				//Get add_user permissions
				$dl_topic_user_id = $add_user;
				$reset_perms = true;
			}
			else
			{
				$dl_topic_user_id = $user->data['user_id'];
			}

			$db->sql_freeresult($result_tmp);
		}
		else if ($config['dl_diff_topic_user'] == 1 && $config['dl_topic_user'])
		{
			$sql_tmp = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $config['dl_topic_user'];
			$result_tmp = $db->sql_query($sql_tmp);

			if ($db->sql_affectedrows($result_tmp))
			{
				//Get dl_topic_user permissions
				$dl_topic_user_id = $config['dl_topic_user'];
				$reset_perms = true;
			}
			else
			{
				$dl_topic_user_id = $user->data['user_id'];
			}

			$db->sql_freeresult($result_tmp);
		}
		else if ($config['dl_diff_topic_user'] == 2 && $dl_index[$cat_id]['diff_topic_user'])
		{
			$sql_tmp = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $dl_index[$cat_id]['topic_user'];
			$result_tmp = $db->sql_query($sql_tmp);

			if ($db->sql_affectedrows($result_tmp))
			{
				//Get category topic_user permissions
				$dl_topic_user_id = $dl_index[$cat_id]['topic_user'];
				$reset_perms = true;
			}
			else
			{
				$dl_topic_user_id = $user->data['user_id'];
			}

			$db->sql_freeresult($result_tmp);
		}

		if ($reset_perms)
		{
			$perms = self::_change_auth($dl_topic_user_id);
		}

		if ($config['dl_topic_title_catname'])
		{
			$topic_title = utf8_normalize_nfc($dl_title);
		}
		else
		{
			$topic_title = utf8_normalize_nfc($language->lang('DL_TOPIC_SUBJECT', $dl_title));
		}

		$topic_text .= "\n\n[b]" . $language->lang('DL_VIEW_LINK') . ':[/b] [url=' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $dl_id), true, '') . ']' . $dl_title . '[/url]';

		$poll			= array();
		$update_message	= false;

		$sql = 'SELECT forum_parents, forum_name FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $topic_forum;
		$result = $db->sql_query($sql);
		$post_data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$message = utf8_normalize_nfc($topic_text);

		$bbcode_status	= true;
		$smilies_status	= true;
		$img_status		= true;
		$url_status		= true;
		$flash_status	= ($auth->acl_get('f_flash', $topic_forum) && $config['allow_post_flash']) ? true : false;
		$quote_status	= true;
		$enable_sig		= true;

		if (!class_exists('parse_message'))
		{
			include(dl_init::phpbb_root_path() . 'includes/message_parser' . dl_init::phpEx());
		}

		$message_parser = new \parse_message();

		if (isset($message))
		{
			$message_parser->message = &$message;
			unset($message);
		}

		$message_parser->parse($bbcode_status, $url_status, $smilies_status, $img_status, $flash_status, $quote_status, $url_status);
		$message_md5 = md5($message_parser->message);

		$data = array(
			'topic_title'			=> $topic_title,
			'topic_first_post_id'	=> 0,
			'topic_last_post_id'	=> 0,
			'topic_time_limit'		=> 0,
			'topic_attachment'		=> 0,
			'post_id'				=> 0,
			'topic_id'				=> 0,
			'forum_id'				=> (int) $topic_forum,
			'icon_id'				=> 0,
			'poster_id'				=> (int) $dl_topic_user_id,
			'enable_sig'			=> (bool) $enable_sig,
			'enable_bbcode'			=> (bool) $bbcode_status,
			'enable_smilies'		=> (bool) $smilies_status,
			'enable_urls'			=> (bool) $url_status,
			'enable_indexing'		=> 0,
			'message_md5'			=> (string) $message_md5,
			'post_time'				=> time(),
			'post_checksum'			=> '',
			'post_edit_reason'		=> '',
			'post_edit_user'		=> 0,
			'forum_parents'			=> $post_data['forum_parents'],
			'forum_name'			=> $post_data['forum_name'],
			'notify'				=> false,
			'notify_set'			=> 0,
			'poster_ip'				=> $user->data['user_ip'],
			'post_edit_locked'		=> 0,
			'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
			'bbcode_uid'			=> $message_parser->bbcode_uid,
			'message'				=> $message_parser->message,
			'topic_status'			=> ITEM_UNLOCKED,
		);

		if (!function_exists('submit_post'))
		{
			include(dl_init::phpbb_root_path() . 'includes/functions_posting' . dl_init::phpEx());
		}

		submit_post('post', $topic_title, $user->data['username'], $topic_type, $poll, $data, $update_message, true);

		$dl_topic_id = (int) $data['topic_id'];

		$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
			'dl_topic' => $dl_topic_id)) . ' WHERE id = ' . (int) $dl_id;
		$db->sql_query($sql);

		if ($reset_perms)
		{
			//Restore user permissions
			self::_change_auth('', 'restore', $perms);
		}

		return;
	}

	public static function delete_topic($topic_ids, $topic_drop_mode = 'drop', $dl_ids = array(), $helper = '')
	{
		if (!$topic_ids)
		{
			return;
		}

		if (!is_array($topic_ids))
		{
			$topic_ids = array($topic_ids);
		}

		if ($topic_drop_mode == 'drop')
		{
			if (!function_exists('recalc_nested_sets'))
			{
				include(dl_init::phpbb_root_path() . 'includes/functions_admin' . dl_init::phpEx());
			}

			return delete_topics('topic_id', $topic_ids);
		}
		else if ($topic_drop_mode == 'close')
		{
			foreach($dl_ids as $dl_id => $topic_id)
			{
				$return = self::update_topic($topic_id, $dl_id, $helper, 'close');
			}
		}

		return;
	}

	public static function update_topic($topic_id, $dl_id, $helper, $topic_drop_mode = '')
	{
		static $dl_index;

		global $db, $user, $config, $auth;
		global $dl_index;
		global $phpbb_container;

		$language = $phpbb_container->get('language');

		if (!$topic_id || !$dl_id)
		{
			return;
		}

		$sql = 'SELECT topic_id FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . (int) $topic_id;
		$result = $db->sql_query($sql);
		$topic_exists = $db->sql_affectedrows($result);
		$db->sql_freeresult($result);

		if (!$topic_exists && $config['dl_enable_dl_topic'])
		{
			self::gen_dl_topic($dl_id, $helper, true);
			return;
		}

		$sql = 'SELECT id, description, dl_topic, long_desc, file_name, extern, file_size, cat, hack_version, add_user, long_desc_uid, long_desc_flags, desc_uid, desc_flags, dl_topic
			FROM ' . DOWNLOADS_TABLE . '
			WHERE id = ' . (int) $dl_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$description	= $row['description'];
		$long_desc		= $row['long_desc'];
		$file_name		= $row['file_name'];
		$file_size		= $row['file_size'];
		$extern			= $row['extern'];
		$version		= $row['hack_version'];
		$add_user		= $row['add_user'];

		$long_desc_uid		= $row['long_desc_uid'];
		$long_desc_flags	= $row['long_desc_flags'];
		$desc_uid			= $row['desc_uid'];
		$desc_flags			= $row['desc_flags'];

		$long_text		= generate_text_for_edit($long_desc, $long_desc_uid, $long_desc_flags);
		$long_desc		= $long_text['text'];
		$desc_text		= generate_text_for_edit($description, $desc_uid, $desc_flags);
		$description	= $desc_text['text'];

		$cat_id		= $row['cat'];
		$dl_title	= $description;

		if ($config['dl_topic_title_catname'])
		{
			$dl_title .= ' - ' . $dl_index[$cat_id]['cat_name_nav'];
		}

		$topic_text_add = "\n[b]" . $language->lang('DL_NAME') . ":[/b] " . $description;

		if ($config['dl_topic_post_catname'])
		{
			$topic_text_add .= "\n[b]" . $language->lang('DL_CAT_NAME') . ":[/b] " . $dl_index[$cat_id]['cat_name_nav'];
		}

		$sql = 'SELECT username, user_colour
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $add_user;
		$result = $db->sql_query($sql);

		if ($db->sql_affectedrows($result))
		{
			$row = $db->sql_fetchrow($result);
			$username = $row['username'];
			$user_colour = $row['user_colour'];
			$user_id = $add_user;
		}
		else
		{
			$username = $user->data['username'];
			$user_colour = $user->data['user_colour'];
			$user_id = $user->data['user_id'];
		}

		$db->sql_freeresult($result);

		$author_url		= get_username_string('profile', $user_id, $username, $user_colour);

		if ($user_colour)
		{
			$author_link = '[url=' . $author_url . '][color=#' . $user_colour . ']' . $username . '[/color][/url]';
		}
		else
		{
			$author_link = '[url=' . $author_url . ']' . $username . '[/url]';
		}

		$topic_text_add .= "\n[b]" . $language->lang('DL_HACK_AUTOR') . ":[/b] " . $author_link;

		$topic_text_add .= ($long_desc) ? "\n[b]" . $language->lang('DL_FILE_DESCRIPTION') . ":[/b] " . html_entity_decode($long_desc) : '';
		$topic_text_add .= ($version) ? "\n\n[b]" . $language->lang('DL_HACK_VERSION') . ":[/b] " . $version : '';
		$topic_text_add .= (!$topic_drop_mode) ? "\n[b]" . ((($extern) ? $language->lang('DL_EXTERN') : $language->lang('DL_FILE_NAME')) . ":[/b] " . $file_name) : '';
		$topic_text_add .= (!$topic_drop_mode) ? (($extern) ? '' : "\n[b]" . $language->lang('DL_FILE_SIZE') . ":[/b] " . str_replace('&nbsp;', ' ', dl_format::dl_size($file_size))) : '';

		if ($config['dl_topic_forum'] == -1)
		{
			$topic_forum		= $dl_index[$cat_id]['dl_topic_forum'];
			$topic_text			= $dl_index[$cat_id]['dl_topic_text'];
			$topic_type			= $dl_index[$cat_id]['dl_topic_type'];

			if ($dl_index[$cat_id]['topic_more_details'] == 1)
			{
				$topic_text .= $topic_text_add;
			}
			else if ($dl_index[$cat_id]['topic_more_details'] == 2)
			{
				$topic_text = $topic_text_add . "\n\n" . $topic_text;
			}
		}
		else
		{
			$topic_forum		= $config['dl_topic_forum'];
			$topic_text			= $config['dl_topic_text'];
			$topic_type			= $config['dl_topic_type'];

			if ($config['dl_topic_more_details'] == 1)
			{
				$topic_text .= $topic_text_add;
			}
			else if ($config['dl_topic_more_details'] == 2)
			{
				$topic_text = $topic_text_add . "\n\n" . $topic_text;
			}
		}

		if (!$topic_forum)
		{
			return;
		}

		$reset_perms = false;

		if (!$config['dl_diff_topic_user'] || ($config['dl_diff_topic_user'] == 2 && !$dl_index[$cat_id]['diff_topic_user']))
		{
			$sql_tmp = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $add_user;
			$result_tmp = $db->sql_query($sql_tmp);

			if ($db->sql_affectedrows($result_tmp))
			{
				//Get add_user permissions
				$dl_topic_user_id = $add_user;
				$reset_perms = true;
			}
			else
			{
				$dl_topic_user_id = $user->data['user_id'];
			}

			$db->sql_freeresult($result_tmp);
		}
		else if ($config['dl_diff_topic_user'] == 1 && $config['dl_topic_user'])
		{
			$sql_tmp = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $config['dl_topic_user'];
			$result_tmp = $db->sql_query($sql_tmp);

			if ($db->sql_affectedrows($result_tmp))
			{
				//Get dl_topic_user permissions
				$dl_topic_user_id = $config['dl_topic_user'];
				$reset_perms = true;
			}
			else
			{
				$dl_topic_user_id = $user->data['user_id'];
			}

			$db->sql_freeresult($result_tmp);
		}
		else if ($config['dl_diff_topic_user'] == 2 && $dl_index[$cat_id]['diff_topic_user'])
		{
			$sql_tmp = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $dl_index[$cat_id]['topic_user'];
			$result_tmp = $db->sql_query($sql_tmp);

			if ($db->sql_affectedrows($result_tmp))
			{
				//Get category topic_user permissions
				$dl_topic_user_id = $dl_index[$cat_id]['topic_user'];
				$reset_perms = true;
			}
			else
			{
				$dl_topic_user_id = $user->data['user_id'];
			}

			$db->sql_freeresult($result_tmp);
		}

		if ($reset_perms)
		{
			$perms = self::_change_auth($dl_topic_user_id);
		}

		if ($config['dl_topic_title_catname'])
		{
			$topic_title = utf8_normalize_nfc($dl_title);
		}
		else
		{
			$topic_title = utf8_normalize_nfc($language->lang('DL_TOPIC_SUBJECT', $dl_title));
		}

		if ($topic_drop_mode)
		{
			$topic_text .= "\n\n[b]" . $language->lang('DL_VIEW_LINK') . ':[/b] ' . $language->lang('DL_TOPIC_DROP_MODE_MISSING');
		}
		else
		{
			$topic_text .= "\n\n[b]" . $language->lang('DL_VIEW_LINK') . ':[/b] [url=' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $dl_id), true, '') . ']' . $dl_title . '[/url]';
		}

		$poll = $forum_data = $post_data = array();
		$update_message	= true;

		$sql = 'SELECT forum_parents, forum_name FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $topic_forum;
		$result = $db->sql_query($sql);
		$forum_data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$sql = 'SELECT topic_first_post_id, topic_last_post_id, topic_time, topic_posts_approved, topic_posts_unapproved, topic_posts_softdeleted FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id;
		$result = $db->sql_query($sql);
		$post_data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		$post_id = $post_data['topic_first_post_id'];
		$message = utf8_normalize_nfc($topic_text);

		$bbcode_status	= true;
		$smilies_status	= true;
		$img_status		= true;
		$url_status		= true;
		$flash_status	= ($auth->acl_get('f_flash', $topic_forum) && $config['allow_post_flash']) ? true : false;
		$quote_status	= true;
		$enable_sig		= true;

		if (!class_exists('parse_message'))
		{
			include(dl_init::phpbb_root_path() . 'includes/message_parser' . dl_init::phpEx());
		}

		$message_parser = new \parse_message();

		if (isset($message))
		{
			$message_parser->message = &$message;
			unset($message);
		}

		$message_parser->parse($bbcode_status, $url_status, $smilies_status, $img_status, $flash_status, $quote_status, $url_status);
		$message_md5 = md5($message_parser->message);

		$data = array(
			'topic_title'				=> $topic_title,
			'topic_first_post_id'		=> (int) $post_data['topic_first_post_id'],
			'topic_last_post_id'		=> (int) $post_data['topic_last_post_id'],
			'topic_time_limit'			=> 0,
			'topic_attachment'			=> 0,
			'post_id'					=> (int) $post_id,
			'topic_id'					=> (int) $topic_id,
			'forum_id'					=> (int) $topic_forum,
			'icon_id'					=> 0,
			'poster_id'					=> (int) $dl_topic_user_id,
			'enable_sig'				=> (bool) $enable_sig,
			'enable_bbcode'				=> (bool) $bbcode_status,
			'enable_smilies'			=> (bool) $smilies_status,
			'enable_urls'				=> (bool) $url_status,
			'enable_indexing'			=> 0,
			'message_md5'				=> (string) $message_md5,
			'post_time'					=> (int) $post_data['topic_time'],
			'post_checksum'				=> '',
			'post_edit_reason'			=> $language->lang('DOWNLOAD_UPDATED'),
			'post_edit_user'			=> (int) $user->data['user_id'],
			'forum_parents'				=> $forum_data['forum_parents'],
			'forum_name'				=> $forum_data['forum_name'],
			'notify'					=> false,
			'notify_set'				=> 0,
			'poster_ip'					=> $user->data['user_ip'],
			'post_edit_locked'			=> 0,
			'bbcode_bitfield'			=> $message_parser->bbcode_bitfield,
			'bbcode_uid'				=> $message_parser->bbcode_uid,
			'message'					=> $message_parser->message,
			'topic_posts_approved'		=> $post_data['topic_posts_approved'],
			'topic_posts_unapproved'	=> $post_data['topic_posts_unapproved'],
			'topic_posts_softdeleted'	=> $post_data['topic_posts_softdeleted'],
		);

		if (!function_exists('submit_post'))
		{
			include(dl_init::phpbb_root_path() . 'includes/functions_posting' . dl_init::phpEx());
		}

		submit_post('edit', $topic_title, $user->data['username'], $topic_type, $poll, $data, $update_message, true);

		if ($topic_drop_mode)
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array('topic_status' => ITEM_LOCKED)) . ' WHERE topic_id = ' . (int) $topic_id;
			$db->sql_query($sql);
		}

		// We need to sync the forum if we changed from current user to user id and back to get the correct colour, so do this for every updated download
		if (!function_exists('sync'))
		{
			include(dl_init::phpbb_root_path() . 'includes/functions_admin' . dl_init::phpEx());
		}

		sync('topic', 'topic_id', $topic_id, false, false);
		sync('forum', 'forum_id', $topic_forum, false, false);

		if ($reset_perms)
		{
			//Restore user permissions
			self::_change_auth('', 'restore', $perms);
		}
	}

	/**
	* _change_auth
	* Added by Mickroz for changing permissions
	* code by poppertom69 & RMcGirr83
	* private - not for public use!
	*/
	private static function _change_auth($user_id, $mode = 'replace', $bkup_data = false)
	{
		global $auth, $db, $config, $user;

		switch($mode)
		{
			case 'replace':

				$bkup_data['user_backup'] = $user->data;

				// sql to get the users info
				$sql = 'SELECT *
					FROM ' . USERS_TABLE . '
					WHERE user_id = ' . (int) $user_id;
				$result	= $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				// reset the current users info to that of the bot
				$user->data = array_merge($user->data, $row);

				unset($row);
				$bkup_data['user_new'] = $user->data;

				return $bkup_data;

			break;

			// now we restore the users stuff
			case 'restore':

				$user->data = $bkup_data['user_backup'];

				unset($bkup_data);

			break;
		}
	}
}
