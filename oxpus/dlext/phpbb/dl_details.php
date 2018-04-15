<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* connect to phpBB
*/
if ( !defined('IN_PHPBB') )
{
	exit;
}

if ($cancel)
{
	$action = '';
}

if (!$df_id)
{
	redirect($this->helper->route('oxpus_dlext_controller'));
}

/*
* default entry point for download details
*/
$dl_files = array();
$dl_files = \oxpus\dlext\phpbb\classes\ dl_files::all_files(0, '', 'ASC', '', $df_id, $modcp, '*');

if (!$dl_files)
{
	redirect($this->helper->route('oxpus_dlext_controller'));
}

$cat_id = $dl_files['cat'];

$cat_auth = array();
$cat_auth = \oxpus\dlext\phpbb\classes\ dl_auth::dl_cat_auth($cat_id);

/*
* check the permissions
*/
$user_can_alltimes_load = false;

if (($cat_auth['auth_mod'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered'])) && !\oxpus\dlext\phpbb\classes\ dl_auth::user_banned())
{
	$modcp = ($modcp) ? 1 : 0;
	$user_can_alltimes_load = true;
	$user_is_mod = true;
}
else
{
	$modcp = 0;
	$user_is_mod = false;
}

/*
* Prepare all permissions for the current user
*/
$captcha_active = true;
$user_is_guest = false;
$user_is_admin = false;
$user_is_founder = false;

if (!$this->user->data['is_registered'])
{
	$user_is_guest = true;
}
else
{
	if ($this->auth->acl_get('a_'))
	{
		$user_is_admin = true;
	}

	if ($this->user->data['user_type'] == USER_FOUNDER)
	{
		$user_is_founder = true;
	}
}

switch ($this->config['dl_download_vc'])
{
	case 0:
		$captcha_active = false;
	break;

	case 1:
		if (!$user_is_guest)
		{
			$captcha_active = false;
		}
	break;

	case 2:
		if ($user_is_mod || $user_is_admin || $user_is_founder)
		{
			$captcha_active = false;
		}
	break;

	case 3:
		if ($user_is_admin || $user_is_founder)
		{
			$captcha_active = false;
		}
	break;

	case 4:
		if ($user_is_founder)
		{
			$captcha_active = false;
		}
	break;
}

$check_status = array();
$check_status = \oxpus\dlext\phpbb\classes\ dl_status::status($df_id, $this->helper);

if (!$dl_files['id'])
{
	trigger_error('DL_NO_PERMISSION');
}

$this->language->add_lang('posting');

/*
* Check saved thumbs
*/
$s_dl_popupimage = false;

if ($dl_files['thumbnail'])
{
	$s_dl_popupimage = true;
}

$sql = 'SELECT * FROM ' . DL_IMAGES_TABLE . '
	WHERE dl_id = ' . (int) $df_id;
$result = $this->db->sql_query($sql);
$total_images = $this->db->sql_affectedrows($result);

if ($total_images)
{
	$s_dl_popupimage = true;

	$thumbs_ary = array();

	while ($row = $this->db->sql_fetchrow($result))
	{
		$thumbs_ary[] = $row;
	}
}

$this->db->sql_freeresult($result);

$inc_module = true;
page_header($this->language->lang('DOWNLOADS') . ' - ' . strip_tags($dl_files['description']));

/*
* User is banned?
*/
if (\oxpus\dlext\phpbb\classes\ dl_auth::user_banned())
{
	$s_dl_userban = true;
}
else
{
	$s_dl_userban = false;
}

/*
* Forum rules?
*/
if (isset($index[$cat_id]['rules']) && $index[$cat_id]['rules'] != '')
{
	$cat_rule = $index[$cat_id]['rules'];
	$cat_rule_uid = (isset($index[$cat_id]['rule_uid'])) ? $index[$cat_id]['rule_uid'] : '';
	$cat_rule_bitfield = (isset($index[$cat_id]['rule_bitfield'])) ? $index[$cat_id]['rule_bitfield'] : '';
	$cat_rule_flags = (isset($index[$cat_id]['rule_flags'])) ? $index[$cat_id]['rule_flags'] : 0;
	$cat_rule = censor_text($cat_rule);
	$cat_rule = generate_text_for_display($cat_rule, $cat_rule_uid, $cat_rule_bitfield, $cat_rule_flags);

	$s_cat_rule = true;
}
else
{
	$s_cat_rule = false;
	$cat_rule = '';
}

/*
* Cat Traffic?
*/
$cat_traffic = 0;
$s_cat_traffic = false;

if (!$this->config['dl_traffic_off'])
{
	if ($this->user->data['is_registered'])
	{
		$cat_overall_traffic = $this->config['dl_overall_traffic'];
		$cat_limit = DL_OVERALL_TRAFFICS;
	}
	else
	{
		$cat_overall_traffic = $this->config['dl_overall_guest_traffic'];
		$cat_limit = DL_GUESTS_TRAFFICS;
	}

	if (isset($index[$cat_id]['cat_traffic']) && isset($index[$cat_id]['cat_traffic_use']))
	{
		$cat_traffic = $index[$cat_id]['cat_traffic'] - $index[$cat_id]['cat_traffic_use'];

		if ($index[$cat_id]['cat_traffic'] && $cat_traffic > 0)
		{
			$cat_traffic = ($cat_traffic > $cat_overall_traffic && $cat_limit == true) ? $cat_overall_traffic : $cat_traffic;
			$cat_traffic = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($cat_traffic);

			$s_cat_traffic = false;
		}
	}
}
else
{
	unset($cat_traffic);
}

/*
* Read the ratings for this little download
*/
$rating = $s_hidden_fields = '';
$ratings = 0;
$rating_access = $user_have_rated = false;

if ($this->config['dl_enable_rate'])
{
	$sql = 'SELECT dl_id, user_id FROM ' . DL_RATING_TABLE . '
		WHERE dl_id = ' . (int) $df_id;
	$result = $this->db->sql_query($sql);

	while ($row = $this->db->sql_fetchrow($result))
	{
		$ratings++;
		$user_have_rated = ($row['user_id'] == $this->user->data['user_id']) ? true : false;
	}

	$this->db->sql_freeresult($result);

	if ($this->user->data['is_registered'] && !$user_have_rated)
	{
		$rating_access = true;
	}
}

/*
* fetch last comment, if exists
*/
$s_comments_tab = false;
$allow_manage = false;

if ($index[$cat_id]['comments'] && \oxpus\dlext\phpbb\classes\ dl_auth::cat_auth_comment_read($cat_id))
{
	/*
	* check permissions to manage comments
	*/
	$sql = 'SELECT user_id FROM ' . DL_COMMENTS_TABLE . '
		WHERE id = ' . (int) $df_id . '
			AND dl_id = ' . (int) $dl_id . '
			AND approve = ' . true . '
			AND cat_id = ' . (int) $cat_id;
	$result = $this->db->sql_query($sql);
	$row_user = $this->db->sql_fetchfield('user_id');
	$this->db->sql_freeresult($result);

	if (($row_user == $this->user->data['user_id'] || $cat_auth['auth_mod'] || $index[$cat_id]['auth_mod'] || $this->auth->acl_get('a_')) && $this->user->data['is_registered'])
	{
		$allow_manage = true;
	}

	$deny_post = false;

	if (!\oxpus\dlext\phpbb\classes\ dl_auth::cat_auth_comment_post($cat_id))
	{
		$allow_manage = false;
		$deny_post = true;
	}

	$s_comments_tab = true;

	$comment_text = $this->request->variable('message', '', true);

	if ($action == 'save' && !$deny_post && $comment_text)
	{
		// check form
		if (!check_form_key('dl_comment_posting'))
		{
			trigger_error($this->language->lang('FORM_INVALID'), E_USER_WARNING);
		}

		$allow_bbcode	= ($this->config['allow_bbcode']) ? true : false;
		$allow_urls		= true;
		$allow_smilies	= ($this->config['allow_smilies']) ? true : false;
		$com_uid		= '';
		$com_bitfield	= '';
		$com_flags		= 0;

		generate_text_for_storage($comment_text, $com_uid, $com_bitfield, $com_flags, $allow_bbcode, $allow_urls, $allow_smilies);

		if ($index[$cat_id]['approve_comments'] || \oxpus\dlext\phpbb\classes\ dl_auth::user_admin())
		{
			$approve = true;
		}
		else
		{
			$approve = 0;
		}

		if ($dl_id)
		{
			$sql = 'UPDATE ' . DL_COMMENTS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
				'comment_edit_time'	=> time(),
				'comment_text'		=> $comment_text,
				'com_uid'			=> $com_uid,
				'com_bitfield'		=> $com_bitfield,
				'com_flags'			=> $com_flags,
				'approve'			=> $approve)) . ' WHERE dl_id = ' . (int) $dl_id;
			$this->db->sql_query($sql);

			$comment_message = $this->language->lang('DL_COMMENT_UPDATED');
		}
		else
		{
			$sql = 'INSERT INTO ' . DL_COMMENTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
				'id'				=> $df_id,
				'cat_id'			=> $cat_id,
				'user_id'			=> $this->user->data['user_id'],
				'username'			=> $this->user->data['username'],
				'comment_time'		=> time(),
				'comment_edit_time'	=> time(),
				'comment_text'		=> $comment_text,
				'com_uid'			=> $com_uid,
				'com_bitfield'		=> $com_bitfield,
				'com_flags'			=> $com_flags,
				'approve'			=> $approve));
			$this->db->sql_query($sql);

			$comment_message = $this->language->lang('DL_COMMENT_ADDED');
		}

		if ($approve)
		{
			$processing_user = (\oxpus\dlext\phpbb\classes\ dl_auth::cat_auth_comment_read($cat_id) == 3) ? 0 : \oxpus\dlext\phpbb\classes\ dl_auth::dl_auth_users($cat_id, 'auth_mod');

			$sql = 'SELECT user_email, username, user_lang FROM ' . USERS_TABLE . '
				WHERE ' . $this->db->sql_in_set('user_id', explode(',', $processing_user)) . '
					OR user_type = ' . USER_FOUNDER;

			$mail_data = array(
				'query'				=> $sql,
				'email_template'	=> 'downloads_approve_comment',
				'cat_id'			=> $cat_id,
				'df_id'				=> $df_id,
				'description'		=> $description,
				'cat_name'			=> $index[$cat_id]['cat_name_nav'],
			);

			\oxpus\dlext\phpbb\classes\ dl_email::send_comment_notify($mail_data, $this->helper, $this->ext_path);

			$approve_message	= '';
			$return_parameters	= array('view' => 'comment', 'action' => 'view', 'cat_id' => $cat_id, 'df_id' => $df_id);
			$return_text		= $this->language->lang('CLICK_RETURN_COMMENTS');
		}
		else
		{
			$sql = 'SELECT fav_user_id FROM ' . DL_FAVORITES_TABLE . '
				WHERE fav_dl_id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);

			$fav_user = array();

			while ($row = $this->db->sql_fetchrow($result))
			{
				$fav_user[] = $row['fav_user_id'];
			}

			$this->db->sql_freeresult($result);

			if (!$this->config['dl_disable_email'] && $fav_user)
			{
				$sql_fav_user = (sizeof($fav_user)) ? ' AND ' . $this->db->sql_in_set('user_id', $fav_user) : '';
				$com_perms = $index[$cat_id]['auth_cread'];
				$sql_user = '';

				switch ($com_perms)
				{
					case 0:
					case 1:
						if ($sql_fav_user)
						{
							$sql_user = $sql_fav_user;
							$send_notify = true;
						}
						else
						{
							$send_notify = false;
						}
					break;

					case 2:
						$processing_user = \oxpus\dlext\phpbb\classes\ dl_auth::dl_auth_users($cat_id, 'auth_mod');
						if ($processing_user)
						{
							$sql_user .= ' AND ' . $this->db->sql_in_set('user_id', explode(',', $processing_user));
							$send_notify = true;
						}

						if ($sql_fav_user)
						{
							$sql_user .= $sql_fav_user;
							$send_notify = true;
						}
						else
						{
							$send_notify = false;
						}
					break;

					case 3:
					default:
						$sql_user = '';
						$send_notify = false;
					break;
				}

				if ($send_notify)
				{
					$sql = 'SELECT user_email, username, user_lang FROM ' . USERS_TABLE . '
						WHERE user_id <> ' . (int) $this->user->data['user_id'] . '
							AND user_allow_fav_download_email = 1
							AND user_allow_fav_comment_email = 1' . $sql_fav_user;

					$mail_data = array(
						'query'				=> $sql,
						'email_template'	=> 'downloads_comment_notify',
						'cat_id'			=> $cat_id,
						'df_id'				=> $df_id,
						'description'		=> $description,
						'cat_name'			=> $index[$cat_id]['cat_name_nav'],
					);

					\oxpus\dlext\phpbb\classes\ dl_email::send_comment_notify($mail_data, $this->helper, $this->ext_path);
				}
			}

			$approve_message	= '<br />' . $this->language->lang('DL_MUST_BE_APPROVE_COMMENT');
			$return_parameters	= array('view' => 'detail', 'df_id' => $df_id);
			$return_text		= $this->language->lang('CLICK_RETURN_DOWNLOAD_DETAILS');
		}

		$message = $comment_message . $approve_message . '<br /><br />' . sprintf($return_text, '<a href="' . $this->helper->route('oxpus_dlext_controller', $return_parameters) . '">', '</a>');

		meta_refresh(3, $this->helper->route('oxpus_dlext_controller', $return_parameters));

		trigger_error($message);
	}

	if ($action == 'delete' && $allow_manage)
	{
		// Delete comment by poster or admin or dl_mod
		if (!$confirm)
		{
			// Confirm deletion
			$s_hidden_fields = array(
				'cat_id'	=> $cat_id,
				'df_id'		=> $df_id,
				'dl_id'		=> $dl_id,
				'action'	=> 'delete',
				'view'		=> 'comment'
			);

			$this->template->set_filenames(array(
				'body' => 'dl_confirm_body.html')
			);

			page_header($this->language->lang('DOWNLOADS') . ' :: ' . $this->language->lang('DELETE_MESSAGE'));

			add_form_key('dl_com_del');

			$this->template->assign_vars(array(
				'MESSAGE_TITLE' => $this->language->lang('DELETE_MESSAGE'),
				'MESSAGE_TEXT' => $this->language->lang('DELETE_MESSAGE_CONFIRM'),

				'S_CONFIRM_ACTION' => $this->helper->route('oxpus_dlext_controller'),
				'S_HIDDEN_FIELDS' => build_hidden_fields($s_hidden_fields))
			);

			page_footer();
		}
		else
		{
			if (!check_form_key('dl_com_del'))
			{
				trigger_error('FORM_INVALID');
			}

			$sql = 'DELETE FROM ' . DL_COMMENTS_TABLE . '
				WHERE cat_id = ' . (int) $cat_id . '
					AND id = ' . (int) $df_id . '
					AND dl_id = ' . (int) $dl_id;
			$this->db->sql_query($sql);

			$sql = 'SELECT dl_id FROM ' . DL_COMMENTS_TABLE . '
				WHERE cat_id = ' . (int) $cat_id . '
					AND id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);
			$total_comments = $this->db->sql_affectedrows($result);
			$this->db->sql_freeresult($result);

			if (!$total_comments)
			{
				redirect($this->helper->route('oxpus_dlext_controller', array('view' => 'detail' , 'df_id' => $df_id)));
			}
			else
			{
				$action = 'view';
			}
		}
	}

	if (!$deny_post)
	{
		// Edit or add a comment
		if ($action == 'edit')
		{
			$sql = 'SELECT comment_text, com_uid, com_flags FROM ' . DL_COMMENTS_TABLE . '
				WHERE dl_id = ' . (int) $dl_id . '
					AND id = ' . (int) $df_id . '
					AND cat_id = ' . (int) $cat_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);

			$comment_text	= $row['comment_text'];
			$com_uid		= $row['com_uid'];
			$com_flags		= $row['com_flags'];

			$this->db->sql_freeresult($result);

			$text_ary		= generate_text_for_edit($comment_text, $com_uid, $com_flags);
			$comment_text	= $text_ary['text'];
		}

		$s_hidden_fields = array(
			'dl_id'		=> $dl_id,
			'df_id'		=> $df_id,
			'cat_id'	=> $cat_id,
			'action'	=> 'save',
			'view'		=> 'comment'
		);

		add_form_key('dl_comment_posting', '_COMMENT');

		// Status for HTML, BBCode, Smilies, Images and Flash
		$bbcode_status	= ($this->config['allow_bbcode']) ? true : false;
		$smilies_status	= ($bbcode_status && $this->config['allow_smilies']) ? true : false;
		$img_status		= ($bbcode_status) ? true : false;
		$url_status		= ($this->config['allow_post_links']) ? true : false;
		$flash_status	= ($bbcode_status && $this->config['allow_post_flash']) ? true : false;
		$quote_status	= true;

		// Smilies Block
		include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		generate_smilies('inline', 0);

		// BBCode-Block
		display_custom_bbcodes();

		$this->template->assign_vars(array(
			'COMMENT_TEXT'		=> $comment_text,

			'S_BBCODE_ALLOWED'	=> $bbcode_status,
			'S_BBCODE_IMG'		=> $img_status,
			'S_BBCODE_URL'		=> $url_status,
			'S_BBCODE_FLASH'	=> $flash_status,
			'S_BBCODE_QUOTE'	=> $quote_status,

			'S_COMMENT_POST_ACTION'	=> $this->helper->route('oxpus_dlext_controller'),
			'S_HIDDEN_POST_FIELDS'	=> build_hidden_fields($s_hidden_fields),

			'U_MORE_SMILIES'		=> $this->helper->route('oxpus_dlext_controller', array('action' => 'smilies')),
		));
	}

	$sql = 'SELECT * FROM ' . DL_COMMENTS_TABLE . '
		WHERE cat_id = ' . (int) $cat_id . '
			AND id = ' . (int) $df_id . '
			AND approve = ' . true;
	$result = $this->db->sql_query($sql);
	$real_comment_exists = $this->db->sql_affectedrows($result);
	$this->db->sql_freeresult($result);

	if ($real_comment_exists > $this->config['dl_links_per_page'])
	{
		$pagination = $this->phpbb_container->get('pagination');
		$pagination->generate_template_pagination(
			array(
				'routes' => array(
					'oxpus_dlext_controller',
					'oxpus_dlext_page_controller',
				),
				'params' => array('view' => 'comment', 'action' => 'view', 'cat_id' => $cat_id, 'df_id' => $df_id),
			), 'pagination', 'start', $real_comment_exists, $this->config['dl_links_per_page'], $page_start);

		$this->template->assign_vars(array(
			'PAGE_NUMBER'	=> $pagination->on_page($real_comment_exists, $this->config['dl_links_per_page'], $page_start),
			'TOTAL_DL'		=> $this->language->lang('DL_COMMENTS_COUNT', $real_comment_exists),
		));
	}

	if ($real_comment_exists)
	{
		$this->template->assign_var('S_VIEW_COMMENTS', true);

		$sql = 'SELECT c.*, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height FROM ' . DL_COMMENTS_TABLE . ' c
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = c.user_id
			WHERE cat_id = ' . (int) $cat_id . '
				AND id = ' . (int) $df_id . '
				AND approve = ' . true . '
			ORDER BY comment_time DESC';
		$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$avatar_row = array(
				'avatar'		=> $row['user_avatar'],
				'avatar_type'	=> $row['user_avatar_type'],
				'avatar_width'	=> $row['user_avatar_width'],
				'avatar_height'	=> $row['user_avatar_height'],
			);

			$poster_id			= $row['user_id'];
			$poster				= $row['username'];
			$poster_color		= $row['user_colour'];
			$poster_avatar		= phpbb_get_avatar($avatar_row, $poster, false, true);
			$dl_id				= $row['dl_id'];

			$message			= $row['comment_text'];
			$com_uid			= $row['com_uid'];
			$com_bitfield		= $row['com_bitfield'];
			$com_flags			= (isset($row['com_flags'])) ? $row['com_flags'] : 0;

			$message			= censor_text($message);
			$message			= generate_text_for_display($message, $com_uid, $com_bitfield, $com_flags);

			$comment_time		= $row['comment_time'];
			$comment_edit_time	= $row['comment_edit_time'];

			if($comment_time <> $comment_edit_time)
			{
				$edited_by = $this->language->lang('DL_COMMENT_EDITED', $this->user->format_date($comment_edit_time));
			}
			else
			{
				$edited_by = '';
			}

			$u_delete_comment	= $this->helper->route('oxpus_dlext_controller', array('view' => 'comment', 'action' => 'delete', 'cat_id' => $cat_id, 'df_id' => $df_id, 'dl_id' => $dl_id));
			$u_edit_comment		= $this->helper->route('oxpus_dlext_controller', array('view' => 'comment', 'action' => 'edit', 'cat_id' => $cat_id, 'df_id' => $df_id, 'dl_id' => $dl_id));

			$this->template->assign_block_vars('comment_row', array(
				'EDITED_BY'		=> $edited_by,
				'POSTER'		=> get_username_string('full', $poster_id, $poster, $poster_color),
				'POSTER_AVATAR'	=> $poster_avatar,
				'MESSAGE'		=> $message,
				'POST_TIME'		=> $this->user->format_date($comment_time),
				'DL_ID'			=> $dl_id,

				'U_DELETE_COMMENT'	=> $u_delete_comment,
				'U_EDIT_COMMENT'	=> ($deny_post) ? '' : $u_edit_comment,
			));

            if (($poster_id == $this->user->data['user_id'] || $cat_auth['auth_mod'] || $index[$cat_id]['auth_mod'] || $this->auth->acl_get('a_')) && $this->user->data['is_registered'] && !$deny_post)
			{
				$this->template->assign_block_vars('comment_row.action_button', array());
			}
		}

		$this->db->sql_freeresult($result);
	}
}

/*
* Check existing hashes and build the hash table if the category allowes it
*/
$hash_method = $this->config['dl_file_hash_algo'];
$func_hash = $hash_method . '_file';
$hash_table_tmp = $hash_table = $hash_ary = array();
$hash_tab = false;
$ver_tab = false;
$ver_can_edit = false;

if (($user_is_mod || $user_is_admin || $user_is_founder) || ($this->config['dl_edit_own_downloads'] && $dl_files['add_user'] == $this->user->data['user_id']))
{
	$ver_can_edit = true;
}

if (!$dl_files['extern'])
{
	if (!$dl_files['file_hash'])
	{
		if ($dl_files['real_file'] && file_exists(DL_EXT_FILES_FOLDER . $index[$cat_id]['cat_path'] . $dl_files['real_file']))
		{
			$dl_files['file_hash'] = $func_hash(DL_EXT_FILES_FOLDER . $index[$cat_id]['cat_path'] . $dl_files['real_file']);
			$sql = 'UPDATE ' . DOWNLOADS_TABLE . " SET file_hash = '" . $this->db->sql_escape($dl_files['file_hash']) . "' WHERE id = " . (int) $df_id;
			$this->db->sql_query($sql);
		}
	}

	if ($index[$cat_id]['show_file_hash'])
	{
		$dl_key = $dl_files['description'] . (($dl_files['hack_version']) ? ' ' . $dl_files['hack_version'] : ' (' . $this->language->lang('DL_CURRENT_VERSION') . ')');
		$hash_table_tmp[$dl_key]['hash'] = ($dl_files['file_hash']) ? $dl_files['file_hash'] : '';
		$hash_table_tmp[$dl_key]['file'] = $dl_files['file_name'];
		$hash_table_tmp[$dl_key]['type'] = ($dl_files['file_hash']) ? $hash_method : $this->language->lang('DL_FILE_NOT_FOUND', $dl_files['file_name'], DL_EXT_FILES_WEBFOLDER . $index[$cat_id]['cat_path']);
		$hash_ary[] = $dl_key;
	}

	$sql = 'SELECT * FROM ' . DL_VERSIONS_TABLE . '
		WHERE dl_id = ' . (int) $df_id . "
		ORDER BY ver_version DESC, ver_change_time DESC";
	$result = $this->db->sql_query($sql);
	$total_releases = $this->db->sql_affectedrows($result);

	if ($total_releases)
	{
		$version_array = $ver_key_ary = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
			$ver_file_hash = $row['ver_file_hash'];

			if (!$ver_file_hash)
			{
				if ($row['ver_real_file'] && file_exists(DL_EXT_FILES_FOLDER . $index[$cat_id]['cat_path'] . $row['ver_real_file']))
				{
					$ver_file_hash = $func_hash(DL_EXT_FILES_FOLDER . $index[$cat_id]['cat_path'] . $row['ver_real_file']);
					$sql = 'UPDATE ' . DL_VERSIONS_TABLE . " SET ver_file_hash = '" . $this->db->sql_escape($ver_file_hash) . "' WHERE ver_id = " . (int) $row['ver_id'];
					$this->db->sql_query($sql);
				}
			}

			$dl_key = $dl_files['description'] . (($row['ver_version']) ? ' ' . $row['ver_version'] : ' (' . $this->user->format_date($row['ver_change_time']) . ')');

			if ($index[$cat_id]['show_file_hash'] && ($row['ver_active'] || $ver_can_edit))
			{
				$hash_table_tmp[$dl_key]['hash'] = ($ver_file_hash) ? $ver_file_hash : '';
				$hash_table_tmp[$dl_key]['file'] = $row['ver_file_name'];
				$hash_table_tmp[$dl_key]['type'] = ($ver_file_hash) ? $hash_method : $this->language->lang('DL_FILE_NOT_FOUND', $row['ver_file_name'], DL_EXT_FILES_WEBFOLDER . $index[$cat_id]['cat_path']);
				$hash_ary[] = $dl_key;
			}

			if ($row['ver_active'] || $ver_can_edit)
			{
				$ver_tab = true;
				$ver_desc = censor_text($row['ver_text']);
				$flags = (isset($row['ver_flags'])) ? $row['ver_flags'] : 0;
				$ver_desc = generate_text_for_display($ver_desc, $row['ver_uid'], $row['ver_bitfield'], $flags);
				if (strlen($ver_desc) > 150)
				{
					$ver_desc = substr($ver_desc, 0, 100) . ' [...]';
				}

				$ver_tmp = ($row['ver_version']) ? $row['ver_version'] : $row['ver_change_time'];
				$ver_key_ary[] = $ver_tmp;
				$version_array[$ver_tmp] = array(
					'VER_TITLE'			=> $dl_key,
					'VER_TIME'			=> $this->user->format_date($row['ver_change_time']),
					'VER_DESC'			=> $ver_desc,
					'VER_ACTIVE'		=> $row['ver_active'],
					'S_USER_PERM'		=> $ver_can_edit,
					'U_VERSION'			=> $this->helper->route('oxpus_dlext_controller', array('view' => 'version', 'action' => 'detail', 'ver_id' => $row['ver_id'], 'df_id' => $df_id)),
					'U_VERSION_EDIT'	=> $this->helper->route('oxpus_dlext_controller', array('view' => 'version', 'action' => 'edit', 'ver_id' => $row['ver_id'], 'df_id' => $df_id)),
				);
			}
		}

		natsort($ver_key_ary);
		$ver_key_ary = array_reverse($ver_key_ary);
		foreach ($ver_key_ary as $key => $value)
		{
			$this->template->assign_block_vars('ver_cell', $version_array[$value]);
		}
		unset($ver_key_ary);
		unset($version_array);
	}

	natsort($hash_ary);
	$hash_ary = array_unique(array_reverse($hash_ary));
	foreach ($hash_ary as $key => $value)
	{
		$hash_table[$value] = $hash_table_tmp[$value];
	}
	unset($hash_ary);
	unset($hash_table_tmp);

	$this->db->sql_freeresult($result);

	if (sizeof($hash_table) && $index[$cat_id]['show_file_hash'])
	{
		foreach ($hash_table as $key => $value)
		{
			$this->template->assign_block_vars('hash_row', array(
				'DL_VERSION'		=> $key,
				'DL_FILE_NAME'		=> $value['file'],
				'DL_HASH_METHOD'	=> $value['type'],
				'DL_HASH'			=> $value['hash'],
			));
		}

		$hash_tab = true;
	}
}

/*
* generate page
*/
$this->template->set_filenames(array(
	'body' => 'view_dl_body.html')
);

$user_id = $this->user->data['user_id'];
$username = $this->user->data['username'];

/*
* prepare the download for displaying
*/
$long_desc			= $dl_files['long_desc'];
$long_desc_uid		= $dl_files['long_desc_uid'];
$long_desc_bitfield	= $dl_files['long_desc_bitfield'];
$long_desc_flags	= (isset($dl_files['long_desc_flags'])) ? $dl_files['long_desc_flags'] : 0;
$long_desc			= generate_text_for_display($long_desc, $long_desc_uid, $long_desc_bitfield, $long_desc_flags);

$file_name		= $file_status['file_detail'];
$file_load		= $file_status['auth_dl'];
$real_file		= $dl_files['real_file'];

if ($dl_files['extern'])
{
	if ($this->config['dl_shorten_extern_links'])
	{
		if (strlen($file_name) > $this->config['dl_shorten_extern_links'] && strlen($file_name) <= $this->config['dl_shorten_extern_links'] * 2)
		{
			$file_name = substr($file_name, strlen($file_name) - $this->config['dl_shorten_extern_links']);
		}
		else
		{
			$file_name = substr($file_name, 0, $this->config['dl_shorten_extern_links']) . '...' . substr($file_name, strlen($file_name) - $this->config['dl_shorten_extern_links']);
		}
	}
}

if ($dl_files['file_size'])
{
	$file_size_out = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($dl_files['file_size'], 2);
}
else
{
	$file_size_out = $this->language->lang('DL_NOT_AVAILIBLE');
}

$file_klicks			= $dl_files['klicks'];
$file_overall_klicks	= $dl_files['overall_klicks'];

$cat_name = $index[$cat_id]['cat_name'];
$cat_view = $index[$cat_id]['nav_path'];
$cat_desc = $index[$cat_id]['description'];

$add_user		= $add_time = '';
$change_user	= $change_time = '';

$sql = 'SELECT username, user_id, user_colour FROM ' . USERS_TABLE . '
	WHERE user_id = ' . (int) $dl_files['add_user'];
$result = $this->db->sql_query($sql);

$row			= $this->db->sql_fetchrow($result);
$add_user		= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
$add_time		= $this->user->format_date($dl_files['add_time']);

$this->db->sql_freeresult($result);

if ($dl_files['add_time'] != $dl_files['change_time'])
{
	$sql = 'SELECT username, user_id, user_colour FROM ' . USERS_TABLE . '
		WHERE user_id = ' . (int) $dl_files['change_user'];
	$result = $this->db->sql_query($sql);

	$row			= $this->db->sql_fetchrow($result);
	$change_user	= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
	$change_time	= $this->user->format_date($dl_files['change_time']);

	$this->db->sql_freeresult($result);
}

$last_time_string		= ($dl_files['extern']) ? $this->language->lang('DL_LAST_TIME_EXTERN') : $this->language->lang('DL_LAST_TIME');
$last_time				= ($dl_files['last_time']) ? sprintf($last_time_string, $this->user->format_date($dl_files['last_time'])) : $this->language->lang('DL_NO_LAST_TIME');

$hack_author_email		= $dl_files['hack_author_email'];
$hack_author			= ($dl_files['hack_author'] != '') ? $dl_files['hack_author'] : 'n/a';
$hack_author_website	= $dl_files['hack_author_website'];
$hack_dl_url			= $dl_files['hack_dl_url'];

$mod_test				= $dl_files['test'];
$mod_require			= $dl_files['req'];
$mod_warning			= $dl_files['warning'];
$mod_desc				= $dl_files['mod_desc'];

/*
* Hacklist
*/
$s_hacklist = false;
if ($dl_files['hacklist'] && $this->config['dl_use_hacklist'])
{
	$s_hacklist = true;
}

/*
* Block for extra informations - The MOD Block ;-)
*/
$s_mod_test = false;
$s_mod_desc = false;
$s_mod_warning = false;
$s_mod_require = false;

if ($dl_files['mod_list'])
{
	if ($index[$cat_id]['allow_mod_desc'])
	{
		$this->template->assign_var('S_MOD_LIST', true);

		if ($mod_test)
		{
			$s_mod_test = true;
		}

		if ($mod_desc)
		{
			$s_mod_desc			= true;
			$mod_desc_uid		= $dl_files['mod_desc_uid'];
			$mod_desc_bitfield	= $dl_files['mod_desc_bitfield'];
			$mod_desc_flags		= (isset($dl_files['mod_desc_flags'])) ? $dl_files['mod_desc_flags'] : 0;
			$mod_desc			= generate_text_for_display($mod_desc, $mod_desc_uid, $mod_desc_bitfield, $mod_desc_flags);
		}

		if ($mod_warning)
		{
			$s_mod_warning		= true;
			$mod_warn_uid		= $dl_files['warn_uid'];
			$mod_warn_bitfield	= $dl_files['warn_bitfield'];
			$mod_warn_flags		= (isset($dl_files['warn_flags'])) ? $dl_files['warn_flags'] : 0;
			$mod_warning		= generate_text_for_display($mod_warning, $mod_warn_uid, $mod_warn_bitfield, $mod_warn_flags);
		}

		if ($mod_require)
		{
			$s_mod_require = true;
		}
	}
}

/*
* ToDO's? ToDo's!
*/
$mod_todo = $dl_files['todo'];

if ($mod_todo)
{
	$s_mod_todo			= true;
	$mod_todo_uid		= $dl_files['todo_uid'];
	$mod_todo_bitfield	= $dl_files['todo_bitfield'];
	$mod_todo_flags		= (isset($dl_files['todo_flags'])) ? $dl_files['todo_flags'] : 0;
	$mod_todo			= generate_text_for_display($mod_todo, $mod_todo_uid, $mod_todo_bitfield, $mod_todo_flags);
}
else
{
	$s_mod_todo = false;
}

/*
* Check for recurring downloads
*/
$s_trafficfree_dl = false;

if ($this->config['dl_user_traffic_once'] && !$file_load && !$dl_files['free'] && !$dl_files['extern'] && ($dl_files['file_size'] > $this->user->data['user_traffic'] ) && !$this->config['dl_traffic_off'] && DL_USERS_TRAFFICS == true)
{
	$sql = 'SELECT * FROM ' . DL_NOTRAF_TABLE . '
		WHERE user_id = ' . (int) $this->user->data['user_id'] . '
			AND dl_id = ' . (int) $df_id;
	$result = $this->db->sql_query($sql);
	$still_count = $this->db->sql_affectedrows($result);
	$this->db->sql_freeresult($result);

	if ($still_count)
	{
		$file_load = true;
		$s_trafficfree_dl = true;
	}
}

/*
* Hotlink or not hotlink, that is the question :-P
* And we will check a broken download inclusive the visual confirmation here ...
*/
if (($file_load || $user_can_alltimes_load) && !$this->user->data['is_bot'])
{
	if (!$dl_files['broken'] || ($dl_files['broken'] && !$this->config['dl_report_broken_lock']) || $user_can_alltimes_load)
	{
		if ($this->config['dl_prevent_hotlink'])
		{
			$hotlink_id = md5($this->user->data['user_id'] . time() . $df_id . $this->user->data['session_id']);

			$sql = 'INSERT INTO ' . DL_HOTLINK_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
				'user_id'		=> $this->user->data['user_id'],
				'session_id'	=> $this->user->data['session_id'],
				'hotlink_id'	=> $hotlink_id));
			$this->db->sql_query($sql);
		}
		else
		{
			$hotlink_id = '';
		}

		$error = array();

		$s_hidden_fields = array(
			'df_id'			=> $df_id,
			'modcp'			=> $modcp,
			'cat_id'		=> $cat_id,
			'hotlink_id'	=> $hotlink_id,
			'submit'		=> true,
		);

		if (!$captcha_active)
		{
			$s_hidden_fields = array_merge($s_hidden_fields, array('view' => 'load'));
		}
		else
		{
			$s_hidden_fields = array_merge($s_hidden_fields, array('view' => 'detail'));
		}

		if (!$ver_can_edit && !$user_can_alltimes_load)
		{
			$sql_ver_where = ' AND v.ver_active = 1 ';
		}
		else
		{
			$sql_ver_where = '';
		}

		$sql = 'SELECT v.ver_id, v.ver_change_time, v.ver_version, u.username FROM ' . DL_VERSIONS_TABLE . ' v
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = v.ver_change_user
			WHERE v.dl_id = ' . (int) $df_id . $sql_ver_where . '
			ORDER BY v.ver_version DESC, v.ver_change_time DESC';
		$result = $this->db->sql_query($sql);
		$total_releases = $this->db->sql_affectedrows($result);

		if ($total_releases)
		{
			$s_select_version = '<select name="file_version">';
			$s_select_version .= '<option value="0" selected="selected">' . $this->language->lang('DL_VERSION_CURRENT') . '</option>';
			$version_array = array();

			while ($row = $this->db->sql_fetchrow($result))
			{
				$ver_id			= $row['ver_id'];
				$ver_version	= $row['ver_version'];
				$ver_time		= $this->user->format_date($row['ver_change_time']);
				$ver_username	= ($row['username']) ? ' [ ' . $row['username'] . ' ]' : '';

				$version_array[$ver_version . ' - ' . $ver_time . $ver_username] = $ver_id;
			}

			natsort($version_array);
			$version_array = array_unique(array_reverse($version_array));
			foreach($version_array as $key => $value)
			{
				$s_select_version .= '<option value="' . $value . '">' . $key . '</option>';
			}

			$s_select_version .= '</select>';
		}
		else
		{
			$s_select_version = '<input type="hidden" name="file_version" value="0" />';
		}

		$this->db->sql_freeresult($result);

		$this->template->assign_block_vars('download_button', array(
			'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
			'S_HOTLINK_ID'		=> $hotlink_id,
			'S_DL_WINDOW'		=> ($dl_files['extern'] && $this->config['dl_ext_new_window']) ? 'target="_blank"' : '',
			'S_DL_VERSION'		=> $s_select_version,
			'U_DOWNLOAD'		=> $this->helper->route('oxpus_dlext_controller'),
		));

		add_form_key('dl_load', '_DOWNLOAD');

		if ($captcha_active)
		{
			$code_match = false;

			$this->template->assign_var('S_VC', true);

			$captcha_factory = $this->phpbb_container->get('captcha.factory');
			$captcha = $captcha_factory->get_instance($this->config['captcha_plugin']);
			$captcha->init(CONFIRM_POST);

	        if ($submit)
	        {
				$vc_response = $captcha->validate();

		        if ($vc_response)
		        {
		            $error[] = $vc_response;
		        }

		        if (!sizeof($error))
		        {
					$captcha->reset();
					$code_match = true;
		        }
				else if (sizeof($error))
		        {
		        	$this->template->assign_block_vars('dl_error', array(
						'DL_ERROR' => $error[0],
					));
		        }
				else if ($captcha->is_solved())
		        {
		            $s_hidden_c_fields = $captcha->get_hidden_fields();
					$code_match = false;
		        }
			}

			if (!$captcha->is_solved() || !$code_match)
			{
				$this->template->assign_vars(array(
					'S_HIDDEN_FIELDS'	=> (isset($s_hidden_c_fields)) ? build_hidden_fields($s_hidden_c_fields) : '',
		            'S_CONFIRM_CODE'	=> true,
		            'CAPTCHA_TEMPLATE'	=> $captcha->get_template(),
				));
			}
		}
		else
		{
			$code_match = true;
		}

		if ($submit && $code_match)
		{
			// check form
			if (!check_form_key('dl_load'))
			{
				trigger_error($this->language->lang('FORM_INVALID'), E_USER_WARNING);
			}

			$code = $this->request->variable('confirm_code', '');

			if ($code)
			{
				$sql = 'INSERT INTO ' . DL_HOTLINK_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
					'user_id'		=> $this->user->data['user_id'],
					'session_id'	=> $this->user->data['session_id'],
					'hotlink_id'	=> 'dlvc',
					'code'			=> $code));
				$this->db->sql_query($sql);
			}

			redirect($this->helper->route('oxpus_dlext_controller', array('view' => 'load', 'hotlink_id' => $hotlink_id, 'code' => $code, 'df_id' => $df_id, 'modcp' => $modcp, 'cat_id' => $cat_id, 'file_version' => $file_version)));
		}
	}
}

/*
* Display the link ro report the download as broken
*/
$s_report_broken = false;
if ($this->config['dl_report_broken'] && !$dl_files['broken'] && !$this->user->data['is_bot'])
{
	if ($this->user->data['is_registered'] || (!$this->user->data['is_registered'] && $this->config['dl_report_broken'] == 1))
	{
		$s_report_broken = true;
	}
}

/*
* Second part of the report link
*/
$s_dl_broken_mod = false;
$s_dl_broken_cur = false;
if ($dl_files['broken'] && !$this->user->data['is_bot'])
{
	if ($index[$cat_id]['auth_mod'] || $cat_auth['auth_mod'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered']))
	{
		$s_dl_broken_mod = true;
	}

	if (!$this->config['dl_report_broken_message'] || ($this->config['dl_report_broken_lock'] && $this->config['dl_report_broken_message']))
	{
		$s_dl_broken_cur = true;
	}
}

/*
* Enabled Bug Tracker for this download category?
*/
$s_bug_tracker = false;
if ($index[$cat_id]['bug_tracker'] && !$this->user->data['is_bot'] && $this->user->data['is_registered'])
{
	$s_bug_tracker = true;
}

/*
* Thumbnails? Okay, getting some thumbs, if they exists...
*/
if ($index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
{
	$first_thumb_exists	= false;
	$more_thumbs_exists	= false;

	if (@file_exists(DL_EXT_THUMBS_FOLDER . $dl_files['thumbnail']) && $dl_files['thumbnail'])
	{
		if (!$total_images)
		{
			$this->template->assign_var('S_DL_POPUPIMAGE', true);
		}

		$first_thumb_exists = true;
	}

	if (isset($thumbs_ary) && sizeof($thumbs_ary))
	{
		$more_thumbs_exists = true;
	}

	if ($first_thumb_exists)
	{
		if ($more_thumbs_exists)
		{
			$thumbs_ary = array_merge(array(0 => array(
				'img_id'	=> 0,
				'dl_id'		=> $df_id,
				'img_name'	=> $dl_files['thumbnail'],
				'img_title'	=> $description,
			)), $thumbs_ary);

		}
		else
		{
			$thumbs_ary = array(0 => array(
				'img_id'	=> 0,
				'dl_id'		=> $df_id,
				'img_name'	=> $dl_files['thumbnail'],
				'img_title'	=> $description,
			));
		}
	}

	if ($first_thumb_exists || $more_thumbs_exists)
	{
		$drop_images = array();

		foreach ($thumbs_ary as $key => $value)
		{
			if (@file_exists(DL_EXT_THUMBS_FOLDER . $thumbs_ary[$key]['img_name']))
			{
				$this->template->assign_block_vars('thumbnail', array(
					'THUMBNAIL_LINK'	=> DL_EXT_THUMBS_WEB_FOLDER . str_replace(" ", "%20", $thumbs_ary[$key]['img_name']),
					'THUMBNAIL_NAME'	=> $thumbs_ary[$key]['img_title'])
				);
			}
			else
			{
				$drop_images[] = $thumbs_ary[$key]['img_id'];
			}
		}

		if (sizeof($drop_images))
		{
			$sql = 'DELETE FROM ' . DL_IMAGES_TABLE . '
				WHERE dl_id = ' . (int) $df_id . '
					AND ' . $this->db->sql_in_set('img_id', array_map('intval', $drop_images));
			$this->db->sql_query($sql);
		}
	}
}

/*
* Urgh, the real filetime..... Heavy information, very important :-D
*/
$s_real_filetime = false;
if ($this->config['dl_show_real_filetime'] && !$dl_files['extern'])
{
	if (@file_exists(DL_EXT_FILES_FOLDER . $index[$cat_id]['cat_path'] . $real_file))
	{
		$s_real_filetime = true;
	}
}

/*
* Like to rate? Do it!
*/
$rating_points = $dl_files['rating'];
$s_rating_perm = false;

if ($this->config['dl_enable_rate'])
{
	if ((!$rating_points || $rating_access) && $this->user->data['is_registered'])
	{
		$s_rating_perm = true;
	}

	if ($ratings)
	{
		if ($ratings == 1)
		{
			$rating_count_text = $this->language->lang('DL_RATING_ONE');
		}
		else
		{
			$rating_count_text = $this->language->lang('DL_RATING_MORE', $ratings);
		}
	}
	else
	{
		$rating_count_text = $this->language->lang('DL_RATING_NONE');
	}
}

/*
* Some user like to link to each favorite page, download, programm, friend, house friend... ahrrrrrrggggg...
*/
if ($this->user->data['is_registered'] && !$this->config['dl_disable_email'])
{
	$sql = 'SELECT fav_id FROM ' . DL_FAVORITES_TABLE . '
		WHERE fav_dl_id = ' . (int) $df_id . '
			AND fav_user_id = ' . (int) $this->user->data['user_id'];
	$result = $this->db->sql_query($sql);
	$fav_id = $this->db->sql_fetchfield('fav_id');
	$this->db->sql_freeresult($result);

	$this->template->assign_var('S_FAV_BLOCK', true);

	if ($fav_id)
	{
		$l_favorite = $this->language->lang('DL_FAVORITE_DROP');
		$u_favorite = $this->helper->route('oxpus_dlext_controller', array('view' => 'unfav', 'df_id' => $df_id, 'cat_id' => $cat_id, 'fav_id' => $fav_id));
		$this->template->assign_var('S_FAV_ACTIVE', true);
	}
	else
	{
		$l_favorite = $this->language->lang('DL_FAVORITE_ADD');
		$u_favorite = $this->helper->route('oxpus_dlext_controller', array('view' => 'fav', 'df_id' => $df_id, 'cat_id' => $cat_id, 'fav_id' => $fav_id));
	}
}
else
{
	$l_favorite = '';
	$u_favorite = '';
}

$file_id	= $dl_files['id'];
$cat_id		= $dl_files['cat'];

/*
* Can we edit the download? Yes we can, or not?
*/
$s_edit_button = false;
$s_edit_thumbs = false;
if (!$this->user->data['is_bot'] && \oxpus\dlext\phpbb\classes\ dl_auth::user_auth($dl_files['cat'], 'auth_mod') || ($this->config['dl_edit_own_downloads'] && $dl_files['add_user'] == $this->user->data['user_id']))
{
	$s_edit_button = true;

	if ($index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
	{
		$s_edit_thumbs = true;
	}
}

/*
* Send the values to the template to be able to read something *g*
*/
$this->template->assign_vars(array(
	'HASH_TAB'			=> $hash_tab,
	'FAVORITE'			=> $l_favorite,
	'EDIT_IMG'			=> $this->language->lang('DL_EDIT_FILE'),
	'CAT_RULE'			=> (isset($cat_rule)) ? $cat_rule : '',
	'CAT_TRAFFIC'		=> (isset($cat_traffic)) ? $this->language->lang('DL_CAT_TRAFFIC_MAIN', $cat_traffic) : '',
	'VER_TAB'			=> ($ver_tab) ? true : false,

	'DESCRIPTION'			=> $description,
	'MINI_IMG'				=> $mini_icon,
	'HACK_VERSION'			=> $hack_version,
	'LONG_DESC'				=> $long_desc,
	'STATUS'				=> $status,
	'FILE_SIZE'				=> $file_size_out,
	'FILE_KLICKS'			=> $file_klicks,
	'FILE_OVERALL_KLICKS'	=> $file_overall_klicks,
	'FILE_NAME'				=> ($dl_files['extern']) ? $this->language->lang('DL_EXTERN') : $file_name,
	'LAST_TIME'				=> $last_time,
	'ADD_USER'				=> ($add_user != '') ? $this->language->lang('DL_ADD_USER', $add_time, $add_user) : '',
	'CHANGE_USER'			=> ($change_user != '') ? $this->language->lang('DL_CHANGE_USER', $change_time, $change_user) : '',
	'REAL_FILETIME'			=> $this->user->format_date(@filemtime(DL_EXT_FILES_FOLDER . $index[$cat_id]['cat_path'] . $real_file)),
	'RATING_IMG'			=> \oxpus\dlext\phpbb\classes\ dl_format::rating_img($rating_points, $s_rating_perm, $df_id),
	'RATINGS'				=> $rating_count_text,
	'DF_ID'					=> $df_id,
	'PHPEX'					=> $this->php_ext,
	'MOD_TODO'				=> $mod_todo,
	'MOD_TEST'				=> ($s_mod_test) ? $mod_test : false,
	'MOD_DESC'				=> ($s_mod_desc) ? $mod_desc : false,
	'MOD_WARNING'			=> ($s_mod_warning) ? $mod_warning : false,
	'MOD_REQUIRE'			=> ($s_mod_require) ? $mod_require : false,
	'HACK_AUTHOR'			=> ($hack_author_email != '') ? '<a href="mailto:' . $hack_author_email . '">' . $hack_author . '</a>' : $hack_author,
	'HACK_AUTHOR_WEBSITE'	=> ($hack_author_website != '') ? ' [ <a href="' . $hack_author_website . '">' . $this->language->lang('WEBSITE') . '</a> ]' : '',
	'HACK_DL_URL'			=> ($hack_dl_url != '') ? '<a href="' . $hack_dl_url . '">' . $this->language->lang('DL_DOWNLOAD') . '</a>' : 'n/a',

	'S_DL_ACTION'		=> $this->helper->route('oxpus_dlext_controller'),
	'S_ENABLE_RATE'		=> (isset($this->config['dl_enable_rate']) && $this->config['dl_enable_rate']) ? true : false,
	'S_SHOW_TOPIC_LINK'	=> ($dl_files['dl_topic']) ? true : false,
	'S_DL_DETAIL_JS'	=> true,

	'S_DL_POPUPIMAGE'	=> $s_dl_popupimage,
	'S_DL_USERBAN'		=> $s_dl_userban,
	'S_CAT_RULE'		=> $s_cat_rule,
	'S_CAT_TRAFFIC'		=> $s_cat_traffic,
	'S_COMMENTS_TAB'	=> $s_comments_tab,
	'S_TRAFFICFREE_DL'	=> $s_trafficfree_dl,
	'S_REPORT_BROKEN'	=> $s_report_broken,
	'S_DL_BROKEN_MOD'	=> $s_dl_broken_mod,
	'S_DL_BROKEN_CUR'	=> $s_dl_broken_cur,
	'S_BUG_TRACKER'		=> $s_bug_tracker,
	'S_REAL_FILETIME'	=> $s_real_filetime,
	'S_EDIT_BUTTON'		=> $s_edit_button,
	'S_EDIT_THUMBS'		=> $s_edit_thumbs,
	'S_MOD_TODO'		=> $s_mod_todo,
	'S_HACKLIST'		=> $s_hacklist,
	'S_OPEN_PANEL'		=> ($view == 'comment' && $s_comments_tab) ? 2 : 0,
	'S_POST_COMMENT'	=> ($s_comments_tab && !$deny_post) ? true : false,

	'U_REPORT'			=> $this->helper->route('oxpus_dlext_controller', array('view' => 'unbroken', 'df_id' => $df_id, 'cat_id' => $cat_id)),
	'U_BROKEN_DOWNLOAD' => $this->helper->route('oxpus_dlext_controller', array('view' => 'broken', 'df_id' => $df_id, 'cat_id' => $cat_id)),
	'U_BUG_TRACKER'		=> $this->helper->route('oxpus_dlext_controller', array('view' => 'bug_tracker', 'df_id' => $df_id)),

	'U_TOPIC'			=> append_sid($this->root_path . 'viewtopic.' . $this->php_ext, 't=' . $dl_files['dl_topic']),
	'U_EDIT'			=> $this->helper->route('oxpus_dlext_controller', array('view' => 'modcp', 'action' => 'edit', 'df_id' => $file_id, 'cat_id' => $cat_id)),
	'U_EDIT_THUMBS'		=> $this->helper->route('oxpus_dlext_controller', array('view' => 'thumbs', 'df_id' => $file_id, 'cat_id' => $cat_id)),
	'U_FAVORITE'		=> $u_favorite,
	'U_DL_SEARCH'		=> $this->helper->route('oxpus_dlext_controller', array('view' => 'search')),
	'U_DL_AJAX'			=> $this->helper->route('oxpus_dlext_controller', array('view' => 'ajax')),
));

/**
* Custom Download Fields
* Taken from memberlist.php phpBB 3.0.7-PL1
*/
$dl_fields = array();
include($this->ext_path . 'phpbb/helpers/dl_fields.' . $this->php_ext);
$cp = new \oxpus\dlext\phpbb\helpers\ custom_profile();
$dl_fields = $cp->generate_profile_fields_template('grab', $file_id);
$dl_fields = (isset($dl_fields[$file_id])) ? $cp->generate_profile_fields_template('show', false, $dl_fields[$file_id]) : array();

if (isset($dl_fields['row']) && sizeof($dl_fields['row']))
{
	$this->template->assign_var('S_DL_FIELDS', true);

	if (!empty($dl_fields['row']))
	{
		$this->template->assign_vars($dl_fields['row']);
	}

	if (!empty($dl_fields['blockrow']))
	{
		foreach ($dl_fields['blockrow'] as $field_data)
		{
			$this->template->assign_block_vars('custom_fields', $field_data);
		}
	}
}

$detail_cat_names = array(
	0 => $this->language->lang('DL_DETAIL'),
	1 => ($ver_tab) ? $this->language->lang('DL_VERSIONS') : '',
	2 => ($s_comments_tab) ? $this->language->lang('DL_COMMENTS') : '',
);

for ($i = 0; $i < sizeof($detail_cat_names); $i++)
{
	if ($detail_cat_names[$i])
	{
		$this->template->assign_block_vars('category', array(
			'CAT_NAME'	=> $detail_cat_names[$i],
			'CAT_ID'	=> $i,
		));
	}
}

/**
* Find similar downloads
*/
if ($this->config['dl_similar_dl'])
{
	$stopword_file = $this->ext_path . '/helpers/dl_stopwords.txt';
	$stopwords = array();

	if (file_exists($stopword_file))
	{
		$stopwords = array_map('trim', file($stopword_file));
	}

	$description = $dl_files['description'];

	if (sizeof($stopwords))
	{
		foreach ($stopwords as $key => $value)
		{
			$description = preg_replace('/\b' . $stopwords[$key] . '\b/iu', '', $description);
		}

		$description = trim($description);
	}

	$sql = 'SELECT id, description, desc_uid, desc_bitfield, desc_flags FROM ' . DOWNLOADS_TABLE . "
		WHERE MATCH (description) AGAINST ('" . $this->db->sql_escape($description) . "')
			AND id <> " . (int) $df_id . '
			AND cat = ' . (int) $cat_id . '
		ORDER BY description';
	$result = $this->db->sql_query_limit($sql, $this->config['dl_similar_limit']);

	while ($row = $this->db->sql_fetchrow($result))
	{
		$similar_id		= $row['id'];
		$similar_desc	= $row['description'];
		$desc_uid		= $dl_files['desc_uid'];
		$desc_bitfield	= $dl_files['desc_bitfield'];
		$desc_flags		= (isset($dl_files['desc_flags'])) ? $dl_files['desc_flags'] : 0;
		$similar_desc	= generate_text_for_display($similar_desc, $desc_uid, $desc_bitfield, $desc_flags);

		$this->template->assign_block_vars('similar_dl', array(
			'DOWNLOAD'		=> $similar_desc,
			'U_DOWNLOAD'	=> $this->helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $similar_id)),
		));
	}

	$this->db->sql_freeresult($result);
}

/*
* The end... Yes? Yes! Puh...
*/
