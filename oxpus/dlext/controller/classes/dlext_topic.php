<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\classes;

use Symfony\Component\DependencyInjection\Container;

class dlext_topic implements dlext_topic_interface
{
	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\controller\helper */
	protected $helper;

	protected $language;

	protected $dlext_auth;
	protected $dlext_format;
	protected $dlext_init;
	protected $dl_index;

	/**
	* Constructor
	*
	* @param Container 								$phpbb_container

	* @param \phpbb\user							$user
	* @param \phpbb\auth\auth						$auth
	* @param \phpbb\config\config					$config
	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param \phpbb\controller\helper				$helper
	*/
	public function __construct(
		Container $phpbb_container,

		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		$dlext_auth,
		$dlext_format,
		$dlext_init
		)
	{
		$this->user 		= $user;
		$this->auth			= $auth;
		$this->config 		= $config;
		$this->db 			= $db;
		$this->helper 		= $helper;

		$this->language		= $phpbb_container->get('language');

		$this->dlext_auth 	= $dlext_auth;
		$this->dlext_format = $dlext_format;
		$this->dlext_init 	= $dlext_init;

		$this->root_path	= $this->dlext_init->root_path();
		$this->php_ext		= $this->dlext_init->php_ext();

		$this->dl_index		= $this->dlext_auth->dl_index();
	}

	public function gen_dl_topic($dl_id, $force = false)
	{
		if (!$this->config['dl_enable_dl_topic'])
		{
			return;
		}

		$sql = 'SELECT id, description, dl_topic, long_desc, file_name, extern, file_size, cat, hack_version, add_user, long_desc_uid, long_desc_flags, desc_uid, desc_flags
				FROM ' . DOWNLOADS_TABLE . '
				WHERE id = ' . (int) $dl_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

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

		if ($this->config['dl_topic_title_catname'])
		{
			$dl_title .= ' (' . $this->dl_index[$cat_id]['cat_name_nav'] . ')';
		}

		$topic_text_add = "\n[b]" . $this->language->lang('DL_NAME') . ":[/b] " . $description;

		if ($this->config['dl_topic_post_catname'])
		{
			$topic_text_add .= "\n[b]" . $this->language->lang('DL_CAT_NAME') . ":[/b] " . $this->dl_index[$cat_id]['cat_name_nav'];
		}

		$sql = 'SELECT username, user_colour
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $add_user;
		$result = $this->db->sql_query($sql);

		if ($this->db->sql_affectedrows($result))
		{
			$row = $this->db->sql_fetchrow($result);
			$username = $row['username'];
			$user_colour = $row['user_colour'];
			$user_id = $add_user;
		}
		else
		{
			$username = $this->user->data['username'];
			$user_colour = $this->user->data['user_colour'];
			$user_id = $this->user->data['user_id'];
		}

		$this->db->sql_freeresult($result);

		$author_url		= get_username_string('profile', $user_id, $username, $user_colour);

		if ($user_colour)
		{
			$author_link = '[url=' . $author_url . '][color=#' . $user_colour . ']' . $username . '[/color][/url]';
		}
		else
		{
			$author_link = '[url=' . $author_url . ']' . $username . '[/url]';
		}

		$topic_text_add .= "\n[b]" . $this->language->lang('DL_HACK_AUTOR') . ":[/b] " . $author_link;

		$topic_text_add .= "\n[b]" . $this->language->lang('DL_FILE_DESCRIPTION') . ":[/b] " . html_entity_decode($long_desc);
		$topic_text_add .= "\n\n[b]" . $this->language->lang('DL_HACK_VERSION') . ":[/b] " . $version;
		$topic_text_add .= "\n[b]" . (($extern) ? $this->language->lang('DL_EXTERN') : $this->language->lang('DL_FILE_NAME')) . ":[/b] " . $file_name;
		$topic_text_add .= (($extern) ? '' : "\n[b]" . $this->language->lang('DL_FILE_SIZE') . ":[/b] " . str_replace('&nbsp;', ' ', $this->dlext_format->dl_size($file_size)));

		if ($this->config['dl_topic_forum'] == -1)
		{
			$topic_forum	= $this->dl_index[$cat_id]['dl_topic_forum'];
			$topic_text		= $this->dl_index[$cat_id]['dl_topic_text'];
			$topic_type		= $this->dl_index[$cat_id]['dl_topic_type'];

			if ($this->dl_index[$cat_id]['topic_more_details'] == 1)
			{
				$topic_text .= $topic_text_add;
			}
			else if ($this->dl_index[$cat_id]['topic_more_details'] == 2)
			{
				$topic_text = $topic_text_add . "\n\n" . $topic_text;
			}
		}
		else
		{
			$topic_forum	= $this->config['dl_topic_forum'];
			$topic_text		= $this->config['dl_topic_text'];
			$topic_type		= $this->config['dl_topic_type'];

			if ($this->config['dl_topic_more_details'] == 1)
			{
				$topic_text .= $topic_text_add;
			}
			else if ($this->config['dl_topic_more_details'] == 2)
			{
				$topic_text = $topic_text_add . "\n\n" . $topic_text;
			}
		}

		if (!$topic_forum)
		{
			return;
		}

		$reset_perms = false;

		if (!$this->config['dl_diff_topic_user'] || ($this->config['dl_diff_topic_user'] == 2 && !$this->dl_index[$cat_id]['diff_topic_user']))
		{
			$sql_tmp = 'SELECT user_id
						FROM ' . USERS_TABLE . '
						WHERE user_id = ' . (int) $add_user;
			$result_tmp = $this->db->sql_query($sql_tmp);

			if ($this->db->sql_affectedrows($result_tmp))
			{
				//Get add_user permissions
				$dl_topic_user_id = $add_user;
				$reset_perms = true;
			}
			else
			{
				$dl_topic_user_id = $this->user->data['user_id'];
			}

			$this->db->sql_freeresult($result_tmp);
		}
		else if ($this->config['dl_diff_topic_user'] == 1 && $this->config['dl_topic_user'])
		{
			$sql_tmp = 'SELECT user_id
						FROM ' . USERS_TABLE . '
						WHERE user_id = ' . (int) $this->config['dl_topic_user'];
			$result_tmp = $this->db->sql_query($sql_tmp);

			if ($this->db->sql_affectedrows($result_tmp))
			{
				//Get dl_topic_user permissions
				$dl_topic_user_id = $this->config['dl_topic_user'];
				$reset_perms = true;
			}
			else
			{
				$dl_topic_user_id = $this->user->data['user_id'];
			}

			$this->db->sql_freeresult($result_tmp);
		}
		else if ($this->config['dl_diff_topic_user'] == 2 && $this->dl_index[$cat_id]['diff_topic_user'])
		{
			$sql_tmp = 'SELECT user_id
						FROM ' . USERS_TABLE . '
						WHERE user_id = ' . (int) $this->dl_index[$cat_id]['topic_user'];
			$result_tmp = $this->db->sql_query($sql_tmp);

			if ($this->db->sql_affectedrows($result_tmp))
			{
				//Get category topic_user permissions
				$dl_topic_user_id = $this->dl_index[$cat_id]['topic_user'];
				$reset_perms = true;
			}
			else
			{
				$dl_topic_user_id = $this->user->data['user_id'];
			}

			$this->db->sql_freeresult($result_tmp);
		}

		if ($reset_perms)
		{
			$perms = $this->_change_auth($dl_topic_user_id);
		}

		if ($this->config['dl_topic_title_catname'])
		{
			$topic_title = utf8_normalize_nfc($dl_title);
		}
		else
		{
			$topic_title = utf8_normalize_nfc($this->language->lang('DL_TOPIC_SUBJECT', $dl_title));
		}

		$topic_text .= "\n\n[b]" . $this->language->lang('DL_VIEW_LINK') . ':[/b] [url=' . generate_board_url(true) . $this->helper->route('oxpus_dlext_details', ['df_id' => $dl_id], true, '') . ']' . $dl_title . '[/url]';

		$poll			= [];
		$update_message	= false;

		$sql = 'SELECT forum_parents, forum_name
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . (int) $topic_forum;
		$result = $this->db->sql_query($sql);
		$post_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$message = utf8_normalize_nfc($topic_text);

		$bbcode_status	= true;
		$smilies_status	= true;
		$img_status		= true;
		$url_status		= true;
		$flash_status	= ($this->auth->acl_get('f_flash', $topic_forum) && $this->config['allow_post_flash']) ? true : false;
		$quote_status	= true;
		$enable_sig		= true;

		if (!class_exists('parse_message'))
		{
			include($this->root_path . 'includes/message_parser' . $this->php_ext);
		}

		$message_parser = new \parse_message();

		if (isset($message))
		{
			$message_parser->message = &$message;
			unset($message);
		}

		$message_parser->parse($bbcode_status, $url_status, $smilies_status, $img_status, $flash_status, $quote_status, $url_status);
		$message_md5 = md5($message_parser->message);

		$data = [
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
			'poster_ip'				=> $this->user->data['user_ip'],
			'post_edit_locked'		=> 0,
			'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
			'bbcode_uid'			=> $message_parser->bbcode_uid,
			'message'				=> $message_parser->message,
			'topic_status'			=> ITEM_UNLOCKED,
		];

		if (!function_exists('submit_post'))
		{
			include($this->root_path . 'includes/functions_posting' . $this->php_ext);
		}

		submit_post('post', $topic_title, $this->user->data['username'], $topic_type, $poll, $data, $update_message, true);

		$dl_topic_id = (int) $data['topic_id'];

		$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
			'dl_topic' => $dl_topic_id]) . ' WHERE id = ' . (int) $dl_id;
		$this->db->sql_query($sql);

		if ($reset_perms)
		{
			//Restore user permissions
			$this->_change_auth('', 'restore', $perms);
		}

		return;
	}

	public function delete_topic($topic_ids, $topic_drop_mode = 'drop', $dl_ids = [])
	{
		if (!$topic_ids)
		{
			return;
		}

		if (!is_array($topic_ids))
		{
			$topic_ids = [$topic_ids];
		}

		if ($topic_drop_mode == 'drop')
		{
			if (!function_exists('recalc_nested_sets'))
			{
				include($this->root_path . 'includes/functions_admin' . $this->php_ext);
			}

			return delete_topics('topic_id', $topic_ids);
		}
		else if ($topic_drop_mode == 'close')
		{
			foreach($dl_ids as $dl_id => $topic_id)
			{
				$return = $this->update_topic($topic_id, $dl_id, 'close');
			}
		}

		return;
	}

	public function update_topic($topic_id, $dl_id, $topic_drop_mode = '')
	{
		if (!$topic_id || !$dl_id)
		{
			return;
		}

		$sql = 'SELECT topic_id
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		$topic_exists = $this->db->sql_affectedrows($result);
		$this->db->sql_freeresult($result);

		if (!$topic_exists && $this->config['dl_enable_dl_topic'])
		{
			$this->gen_dl_topic($dl_id, true);
			return;
		}

		$sql = 'SELECT id, description, dl_topic, long_desc, file_name, extern, file_size, cat, hack_version, add_user, long_desc_uid, long_desc_flags, desc_uid, desc_flags, dl_topic
				FROM ' . DOWNLOADS_TABLE . '
				WHERE id = ' . (int) $dl_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

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

		if ($this->config['dl_topic_title_catname'])
		{
			$dl_title .= ' - ' . $this->dl_index[$cat_id]['cat_name_nav'];
		}

		$topic_text_add = "\n[b]" . $this->language->lang('DL_NAME') . ":[/b] " . $description;

		if ($this->config['dl_topic_post_catname'])
		{
			$topic_text_add .= "\n[b]" . $this->language->lang('DL_CAT_NAME') . ":[/b] " . $this->dl_index[$cat_id]['cat_name_nav'];
		}

		$sql = 'SELECT username, user_colour
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $add_user;
		$result = $this->db->sql_query($sql);

		if ($this->db->sql_affectedrows($result))
		{
			$row = $this->db->sql_fetchrow($result);
			$username = $row['username'];
			$user_colour = $row['user_colour'];
			$user_id = $add_user;
		}
		else
		{
			$username = $this->user->data['username'];
			$user_colour = $this->user->data['user_colour'];
			$user_id = $this->user->data['user_id'];
		}

		$this->db->sql_freeresult($result);

		$author_url		= get_username_string('profile', $user_id, $username, $user_colour);

		if ($user_colour)
		{
			$author_link = '[url=' . $author_url . '][color=#' . $user_colour . ']' . $username . '[/color][/url]';
		}
		else
		{
			$author_link = '[url=' . $author_url . ']' . $username . '[/url]';
		}

		$topic_text_add .= "\n[b]" . $this->language->lang('DL_HACK_AUTOR') . ":[/b] " . $author_link;

		$topic_text_add .= ($long_desc) ? "\n[b]" . $this->language->lang('DL_FILE_DESCRIPTION') . ":[/b] " . html_entity_decode($long_desc) : '';
		$topic_text_add .= ($version) ? "\n\n[b]" . $this->language->lang('DL_HACK_VERSION') . ":[/b] " . $version : '';
		$topic_text_add .= (!$topic_drop_mode) ? "\n[b]" . ((($extern) ? $this->language->lang('DL_EXTERN') : $this->language->lang('DL_FILE_NAME')) . ":[/b] " . $file_name) : '';
		$topic_text_add .= (!$topic_drop_mode) ? (($extern) ? '' : "\n[b]" . $this->language->lang('DL_FILE_SIZE') . ":[/b] " . str_replace('&nbsp;', ' ', $this->dlext_format->dl_size($file_size))) : '';

		if ($this->config['dl_topic_forum'] == -1)
		{
			$topic_forum		= $this->dl_index[$cat_id]['dl_topic_forum'];
			$topic_text			= $this->dl_index[$cat_id]['dl_topic_text'];
			$topic_type			= $this->dl_index[$cat_id]['dl_topic_type'];

			if ($this->dl_index[$cat_id]['topic_more_details'] == 1)
			{
				$topic_text .= $topic_text_add;
			}
			else if ($this->dl_index[$cat_id]['topic_more_details'] == 2)
			{
				$topic_text = $topic_text_add . "\n\n" . $topic_text;
			}
		}
		else
		{
			$topic_forum		= $this->config['dl_topic_forum'];
			$topic_text			= $this->config['dl_topic_text'];
			$topic_type			= $this->config['dl_topic_type'];

			if ($this->config['dl_topic_more_details'] == 1)
			{
				$topic_text .= $topic_text_add;
			}
			else if ($this->config['dl_topic_more_details'] == 2)
			{
				$topic_text = $topic_text_add . "\n\n" . $topic_text;
			}
		}

		if (!$topic_forum)
		{
			return;
		}

		$reset_perms = false;

		if (!$this->config['dl_diff_topic_user'] || ($this->config['dl_diff_topic_user'] == 2 && !$this->dl_index[$cat_id]['diff_topic_user']))
		{
			$sql_tmp = 'SELECT user_id
						FROM ' . USERS_TABLE . '
						WHERE user_id = ' . (int) $add_user;
			$result_tmp = $this->db->sql_query($sql_tmp);

			if ($this->db->sql_affectedrows($result_tmp))
			{
				//Get add_user permissions
				$dl_topic_user_id = $add_user;
				$reset_perms = true;
			}
			else
			{
				$dl_topic_user_id = $this->user->data['user_id'];
			}

			$this->db->sql_freeresult($result_tmp);
		}
		else if ($this->config['dl_diff_topic_user'] == 1 && $this->config['dl_topic_user'])
		{
			$sql_tmp = 'SELECT user_id
						FROM ' . USERS_TABLE . '
						WHERE user_id = ' . (int) $this->config['dl_topic_user'];
			$result_tmp = $this->db->sql_query($sql_tmp);

			if ($this->db->sql_affectedrows($result_tmp))
			{
				//Get dl_topic_user permissions
				$dl_topic_user_id = $this->config['dl_topic_user'];
				$reset_perms = true;
			}
			else
			{
				$dl_topic_user_id = $this->user->data['user_id'];
			}

			$this->db->sql_freeresult($result_tmp);
		}
		else if ($this->config['dl_diff_topic_user'] == 2 && $this->dl_index[$cat_id]['diff_topic_user'])
		{
			$sql_tmp = 'SELECT user_id
						FROM ' . USERS_TABLE . '
						WHERE user_id = ' . (int) $this->dl_index[$cat_id]['topic_user'];
			$result_tmp = $this->db->sql_query($sql_tmp);

			if ($this->db->sql_affectedrows($result_tmp))
			{
				//Get category topic_user permissions
				$dl_topic_user_id = $this->dl_index[$cat_id]['topic_user'];
				$reset_perms = true;
			}
			else
			{
				$dl_topic_user_id = $this->user->data['user_id'];
			}

			$this->db->sql_freeresult($result_tmp);
		}

		if ($reset_perms)
		{
			$perms = $this->_change_auth($dl_topic_user_id);
		}

		if ($this->config['dl_topic_title_catname'])
		{
			$topic_title = utf8_normalize_nfc($dl_title);
		}
		else
		{
			$topic_title = utf8_normalize_nfc($this->language->lang('DL_TOPIC_SUBJECT', $dl_title));
		}

		if ($topic_drop_mode)
		{
			$topic_text .= "\n\n[b]" . $this->language->lang('DL_VIEW_LINK') . ':[/b] ' . $this->language->lang('DL_TOPIC_DROP_MODE_MISSING');
		}
		else
		{
			$topic_text .= "\n\n[b]" . $this->language->lang('DL_VIEW_LINK') . ':[/b] [url=' . generate_board_url(true) . $this->helper->route('oxpus_dlext_details', ['df_id' => $dl_id], true, '') . ']' . $dl_title . '[/url]';
		}

		$poll = $forum_data = $post_data = [];
		$update_message	= true;

		$sql = 'SELECT forum_parents, forum_name
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . (int) $topic_forum;
		$result = $this->db->sql_query($sql);
		$forum_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$sql = 'SELECT topic_first_post_id, topic_last_post_id, topic_time, topic_posts_approved, topic_posts_unapproved, topic_posts_softdeleted
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		$post_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		$post_id = $post_data['topic_first_post_id'];
		$message = utf8_normalize_nfc($topic_text);

		$bbcode_status	= true;
		$smilies_status	= true;
		$img_status		= true;
		$url_status		= true;
		$flash_status	= ($this->auth->acl_get('f_flash', $topic_forum) && $this->config['allow_post_flash']) ? true : false;
		$quote_status	= true;
		$enable_sig		= true;

		if (!class_exists('parse_message'))
		{
			include($this->root_path . 'includes/message_parser' . $this->php_ext);
		}

		$message_parser = new \parse_message();

		if (isset($message))
		{
			$message_parser->message = &$message;
			unset($message);
		}

		$message_parser->parse($bbcode_status, $url_status, $smilies_status, $img_status, $flash_status, $quote_status, $url_status);
		$message_md5 = md5($message_parser->message);

		$data = [
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
			'post_edit_reason'			=> $this->language->lang('DOWNLOAD_UPDATED'),
			'post_edit_user'			=> (int) $this->user->data['user_id'],
			'forum_parents'				=> $forum_data['forum_parents'],
			'forum_name'				=> $forum_data['forum_name'],
			'notify'					=> false,
			'notify_set'				=> 0,
			'poster_ip'					=> $this->user->data['user_ip'],
			'post_edit_locked'			=> 0,
			'bbcode_bitfield'			=> $message_parser->bbcode_bitfield,
			'bbcode_uid'				=> $message_parser->bbcode_uid,
			'message'					=> $message_parser->message,
			'topic_posts_approved'		=> $post_data['topic_posts_approved'],
			'topic_posts_unapproved'	=> $post_data['topic_posts_unapproved'],
			'topic_posts_softdeleted'	=> $post_data['topic_posts_softdeleted'],
		];

		if (!function_exists('submit_post'))
		{
			include($this->root_path . 'includes/functions_posting' . $this->php_ext);
		}

		submit_post('edit', $topic_title, $this->user->data['username'], $topic_type, $poll, $data, $update_message, true);

		if ($topic_drop_mode)
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', ['topic_status' => ITEM_LOCKED]) . ' WHERE topic_id = ' . (int) $topic_id;
			$this->db->sql_query($sql);
		}

		// We need to sync the forum if we changed from current user to user id and back to get the correct colour, so do this for every updated download
		if (!function_exists('sync'))
		{
			include($this->root_path . 'includes/functions_admin' . $this->php_ext);
		}

		sync('topic', 'topic_id', $topic_id, false, false);
		sync('forum', 'forum_id', $topic_forum, false, false);

		if ($reset_perms)
		{
			//Restore user permissions
			$this->_change_auth('', 'restore', $perms);
		}
	}

	/**
	* _change_auth
	* Added by Mickroz for changing permissions
	* code by poppertom69 & RMcGirr83
	* private - not for public use!
	*/
	public function _change_auth($user_id, $mode = 'replace', $bkup_data = false)
	{
		switch($mode)
		{
			case 'replace':

				$bkup_data['user_backup'] = $this->user->data;

				// sql to get the users info
				$sql = 'SELECT *
						FROM ' . USERS_TABLE . '
						WHERE user_id = ' . (int) $user_id;
				$result	= $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				// reset the current users info to that of the bot
				$this->user->data = array_merge($this->user->data, $row);

				unset($row);
				$bkup_data['user_new'] = $this->user->data;

				return $bkup_data;

			break;

			// now we restore the users stuff
			case 'restore':

				$this->user->data = $bkup_data['user_backup'];

				unset($bkup_data);

			break;
		}
	}
}
