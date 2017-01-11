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
	redirect($this->helper->route('dl_ext_controller'));
}

/*
* default entry point for download details
*/
$dl_files = array();
$dl_files = \oxpus\dl_ext\includes\classes\ dl_files::all_files(0, '', 'ASC', '', $df_id, $modcp, '*');

if (!$dl_files)
{
	redirect($this->helper->route('dl_ext_controller'));
}

$cat_id = $dl_files['cat'];

$cat_auth = array();
$cat_auth = \oxpus\dl_ext\includes\classes\ dl_auth::dl_cat_auth($cat_id);

/*
* check the permissions
*/
$user_can_alltimes_load = false;

if (($cat_auth['auth_mod'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered'])) && !\oxpus\dl_ext\includes\classes\ dl_auth::user_banned())
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
$check_status = \oxpus\dl_ext\includes\classes\ dl_status::status($df_id, $this->helper, $ext_path_images);

if (!$dl_files['id'])
{
	trigger_error('DL_NO_PERMISSION');
}

/*
* Check saved thumbs
*/
$sql = 'SELECT * FROM ' . DL_IMAGES_TABLE . '
	WHERE dl_id = ' . (int) $df_id;
$result = $this->db->sql_query($sql);
$total_images = $this->db->sql_affectedrows($result);

if ($total_images)
{
	$this->template->assign_var('S_DL_POPUPIMAGE', true);

	$thumbs_ary = array();

	while ($row = $this->db->sql_fetchrow($result))
	{
		$thumbs_ary[] = $row;
	}
}

$this->db->sql_freeresult($result);

$inc_module = true;
page_header($this->language->lang('DOWNLOADS') . ' - ' . $dl_files['description']);

/*
* User is banned?
*/
if (\oxpus\dl_ext\includes\classes\ dl_auth::user_banned())
{
	$this->template->assign_var('S_DL_USERBAN', true);
}

/*
* Forum rules?
*/
if (isset($index[$cat_id]['rules']) && $index[$cat_id]['rules'] != '')
{
	$cat_rule = $index[$cat_id]['rules'];
	$cat_rule_uid = (isset($index[$cat_id]['rule_uid'])) ? $index[$cat_id]['rule_uid'] : '';
	$cat_rule_bitfield = (isset($index[$cat_id]['rule_bitfield'])) ? $index[$cat_id]['rule_bitfield'] : '';
	$cat_rule_flags = (isset($index[$cat_id]['rule_flags'])) ? $index[$cat_id]['rule_flags'] : '';
	$cat_rule = censor_text($cat_rule);
	$cat_rule = generate_text_for_display($cat_rule, $cat_rule_uid, $cat_rule_bitfield, $cat_rule_flags);

	$this->template->assign_var('S_CAT_RULE', true);
}
else
{
	$cat_rule = '';
}

/*
* Cat Traffic?
*/
$cat_traffic = 0;

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
			$cat_traffic = \oxpus\dl_ext\includes\classes\ dl_format::dl_size($cat_traffic);
	
			$this->template->assign_var('S_CAT_TRAFFIC', true);
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

if ($index[$cat_id]['comments'] && \oxpus\dl_ext\includes\classes\ dl_auth::cat_auth_comment_read($cat_id))
{
	$s_comments_tab = true;
	$this->template->assign_var('S_COMMENTS_TAB', $s_comments_tab);

	$s_hidden_fields = array(
		'cat_id'	=> $cat_id,
		'df_id'		=> $df_id,
		'view'		=> 'comment'
	);

	$this->template->assign_vars(array(
		'S_COMMENT_ACTION'			=> $this->helper->route('dl_ext_controller'),
		'S_HIDDEN_COMMENT_FIELDS'	=> build_hidden_fields($s_hidden_fields))
	);

	$sql = 'SELECT * FROM ' . DL_COMMENTS_TABLE . '
		WHERE cat_id = ' . (int) $cat_id . '
			AND id = ' . (int) $df_id . '
			AND approve = ' . true;
	$result = $this->db->sql_query($sql);
	$real_comment_exists = $this->db->sql_affectedrows($result);
	$this->db->sql_freeresult($result);

	if ($real_comment_exists)
	{
		$this->template->assign_var('S_VIEW_COMMENTS', true);
	}

	if ($this->config['dl_latest_comments'] && $real_comment_exists)
	{
		$this->template->assign_var('S_COMMENTS_ON', true);

		$sql = 'SELECT c.*, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height FROM ' . DL_COMMENTS_TABLE . ' c
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = c.user_id
			WHERE cat_id = ' . (int) $cat_id . '
				AND id = ' . (int) $df_id . '
				AND approve = ' . true . '
			ORDER BY comment_time DESC';
		$result = $this->db->sql_query_limit($sql, $this->config['dl_latest_comments']);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$poster_id			= $row['user_id'];
			$poster				= $row['username'];
			$poster_color		= $row['user_colour'];
			$poster_avatar		= ($this->user->optionget('viewavatars')) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']) : '';

			$message			= $row['comment_text'];
			$com_uid			= $row['com_uid'];
			$com_bitfield		= $row['com_bitfield'];
			$com_flags			= $row['com_flags'];

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

			$this->template->assign_block_vars('comment_row', array(
				'EDITED_BY'		=> $edited_by,
				'POSTER'		=> get_username_string('full', $poster_id, $poster, $poster_color),
				'POSTER_AVATAR'	=> $poster_avatar,
				'MESSAGE'		=> $message,
				'POST_TIME'		=> $this->user->format_date($comment_time))
			);
		}

		$this->db->sql_freeresult($result);
	}

	if (\oxpus\dl_ext\includes\classes\ dl_auth::cat_auth_comment_post($cat_id))
	{
		$s_hidden_fields = array(
			'cat_id'	=> $cat_id,
			'df_id'		=> $df_id,
			'view'		=> 'comment'
		);

		$this->template->assign_var('S_POST_COMMENT', true);

		$this->template->assign_vars(array(
			'S_COMMENT_POST_ACTION'	=> $this->helper->route('dl_ext_controller'),
			'S_HIDDEN_POST_FIELDS'	=> build_hidden_fields($s_hidden_fields))
		);
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
				$ver_desc = generate_text_for_display($ver_desc, $row['ver_uid'], $row['ver_bitfield'], $row['ver_flags']);
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
					'U_VERSION'			=> $this->helper->route('dl_ext_controller', array('view' => 'version', 'action' => 'detail', 'ver_id' => $row['ver_id'], 'df_id' => $df_id)),
					'U_VERSION_EDIT'	=> $this->helper->route('dl_ext_controller', array('view' => 'version', 'action' => 'edit', 'ver_id' => $row['ver_id'], 'df_id' => $df_id)),
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
$long_desc_flags	= $dl_files['long_desc_flags'];
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
	$file_size_out = \oxpus\dl_ext\includes\classes\ dl_format::dl_size($dl_files['file_size'], 2);
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
$hack_author			= ( $dl_files['hack_author'] != '' ) ? $dl_files['hack_author'] : 'n/a';
$hack_author_website	= $dl_files['hack_author_website'];
$hack_dl_url			= $dl_files['hack_dl_url'];

$test					= $dl_files['test'];
$require				= $dl_files['req'];
$todo					= $dl_files['todo'];
$todo_uid				= $dl_files['todo_uid'];
$todo_bitfield			= $dl_files['todo_bitfield'];
$todo_flags				= $dl_files['todo_flags'];
$todo					= generate_text_for_display($todo, $todo_uid, $todo_bitfield, $todo_flags);
$warning				= $dl_files['warning'];
$warn_uid				= $dl_files['warn_uid'];
$warn_bitfield			= $dl_files['warn_bitfield'];
$warn_flags				= $dl_files['warn_flags'];
$warning				= generate_text_for_display($warning, $warn_uid, $warn_bitfield, $warn_flags);

/*
* Hacklist
*/
if ($dl_files['hacklist'] && $this->config['dl_use_hacklist'])
{
	$this->template->assign_block_vars('hacklist', array(
		'HACK_AUTHOR'			=> ( $hack_author_email != '' ) ? '<a href="mailto:'.$hack_author_email.'">'.$hack_author.'</a>' : $hack_author,
		'HACK_AUTHOR_WEBSITE'	=> ( $hack_author_website != '' ) ? ' [ <a href="'.$hack_author_website.'">'.$this->language->lang('WEBSITE').'</a> ]' : '',
		'HACK_DL_URL'	=> ( $hack_dl_url != '' ) ? '<a href="' . $hack_dl_url . '">'.$this->language->lang('DL_DOWNLOAD').'</a>' : 'n/a')
	);
}

/*
* Block for extra informations - The MOD Block ;-)
*/
if ($dl_files['mod_list'])
{
	$mod_desc			= $dl_files['mod_desc'];
	$mod_desc_uid		= $dl_files['mod_desc_uid'];
	$mod_desc_bitfield	= $dl_files['mod_desc_bitfield'];
	$mod_desc_flags		= $dl_files['mod_desc_flags'];
	$mod_desc			= generate_text_for_display($mod_desc, $mod_desc_uid, $mod_desc_bitfield, $mod_desc_flags);

	if ($index[$cat_id]['allow_mod_desc'])
	{
		$this->template->assign_var('S_MOD_LIST', true);
	
		if ($test)
		{
			$this->template->assign_block_vars('modlisttest', array('MOD_TEST' => $test));
		}
	
		if ($mod_desc)
		{
			$this->template->assign_block_vars('modlistdesc', array('MOD_DESC' => $mod_desc));
		}
	
		if ($warning)
		{
			$this->template->assign_block_vars('modwarning', array('MOD_WARNING' => $warning));
		}
	
		if ($require)
		{
			$this->template->assign_block_vars('modrequire', array('MOD_REQUIRE' => $require));
		}
	}
}

/*
* ToDO's? ToDo's!
*/
if ($todo)
{
	$this->template->assign_var('S_MOD_TODO', true);
	$this->template->assign_block_vars('modtodo', array('MOD_TODO' => $todo));
}

/*
* Check for recurring downloads
*/
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

		$this->template->assign_var('S_ALLOW_TRAFFICFREE_DOWNLOAD', true);
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
			'U_DOWNLOAD'		=> $this->helper->route('dl_ext_controller'),
		));

		add_form_key('dl_load');

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

			redirect($this->helper->route('dl_ext_controller', array('view' => 'load', 'hotlink_id' => $hotlink_id, 'code' => $code, 'df_id' => $df_id, 'modcp' => $modcp, 'cat_id' => $cat_id, 'file_version' => $file_version)));
		}
	}
}

/*
* Display the link ro report the download as broken
*/
if ($this->config['dl_report_broken'] && !$dl_files['broken'] && !$this->user->data['is_bot'])
{
	if ($this->user->data['is_registered'] || (!$this->user->data['is_registered'] && $this->config['dl_report_broken'] == 1))
	{
		$this->template->assign_var('S_REPORT_BROKEN_DL', true);
		$this->template->assign_block_vars('report_broken_dl', array(
			'U_BROKEN_DOWNLOAD' => $this->helper->route('dl_ext_controller', array('view' => 'broken', 'df_id' => $df_id, 'cat_id' => $cat_id)),
		));
	}
}

/*
* Second part of the report link
*/
if ($dl_files['broken'] && !$this->user->data['is_bot'])
{
	if ($index[$cat_id]['auth_mod'] || $cat_auth['auth_mod'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered']))
	{
		$this->template->assign_var('S_DL_BROKEN_MOD', true);
		$this->template->assign_block_vars('dl_broken_mod', array(
			'U_REPORT' => $this->helper->route('dl_ext_controller', array('view' => 'unbroken', 'df_id' => $df_id, 'cat_id' => $cat_id)),
		));
	}

	if (!$this->config['dl_report_broken_message'] || ($this->config['dl_report_broken_lock'] && $this->config['dl_report_broken_message']))
	{
		$this->template->assign_var('S_DL_BROKEN_CUR', true);
	}
}

/*
* Send the values to the template to be able to read something *g*
*/
$this->template->assign_block_vars('downloads', array(
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
	'CHANGE_USER'			=> ($change_user != '') ? $this->language->lang('DL_CHANGE_USER', $change_time, $change_user) : '')
);

/*
* Enabled Bug Tracker for this download category?
*/
if ($index[$cat_id]['bug_tracker'] && !$this->user->data['is_bot'] && $this->user->data['is_registered'])
{
	$this->template->assign_block_vars('downloads.bug_tracker', array(
		'U_BUG_TRACKER'			=> $this->helper->route('dl_ext_controller', array('view' => 'bug_tracker', 'df_id' => $df_id)),
	));
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
				$this->template->assign_block_vars('downloads.thumbnail', array(
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
if ($this->config['dl_show_real_filetime'] && !$dl_files['extern'])
{
	if (@file_exists(DL_EXT_FILES_FOLDER . $index[$cat_id]['cat_path'] . $real_file))
	{
		$this->template->assign_block_vars('downloads.real_filetime', array(
			'REAL_FILETIME'		=> $this->user->format_date(@filemtime(DL_EXT_FILES_FOLDER . $index[$cat_id]['cat_path'] . $real_file)))
		);
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

	$this->template->assign_vars(array(
		'RATING_IMG'	=> \oxpus\dl_ext\includes\classes\ dl_format::rating_img($rating_points, $s_rating_perm, $df_id, $ext_path_images),
		'RATINGS'		=> $rating_count_text,
		'DF_ID'			=> $df_id,
		'PHPEX'			=> $this->php_ext,
	));
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
		$u_favorite = $this->helper->route('dl_ext_controller', array('view' => 'unfav', 'df_id' => $df_id, 'cat_id' => $cat_id, 'fav_id' => $fav_id));
		$this->template->assign_var('S_FAV_ACTIVE', true);
	}
	else
	{
		$l_favorite = $this->language->lang('DL_FAVORITE_ADD');
		$u_favorite = $this->helper->route('dl_ext_controller', array('view' => 'fav', 'df_id' => $df_id, 'cat_id' => $cat_id, 'fav_id' => $fav_id));
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
if (!$this->user->data['is_bot'] && \oxpus\dl_ext\includes\classes\ dl_auth::user_auth($dl_files['cat'], 'auth_mod') || ($this->config['dl_edit_own_downloads'] && $dl_files['add_user'] == $this->user->data['user_id']))
{
	$this->template->assign_var('S_EDIT_BUTTON', true);

	if ($index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
	{
		$this->template->assign_var('S_EDIT_THUMBS_BUTTON', true);
	}
}

/*
* A little bit more values and strings for the template *bfg*
*/
$this->template->assign_vars(array(
	'HASH_TAB'			=> $hash_tab,
	'FAVORITE'			=> $l_favorite,
	'EDIT_IMG'			=> $this->language->lang('DL_EDIT_FILE'),
	'CAT_RULE'			=> (isset($cat_rule)) ? $cat_rule : '',
	'CAT_TRAFFIC'		=> (isset($cat_traffic)) ? $this->language->lang('DL_CAT_TRAFFIC_MAIN', $cat_traffic) : '',
	'VER_TAB'			=> ($ver_tab) ? true : false,

	'I_DL_BUTTON'		=> '<img src="' . $ext_path_images . 'dl_button.png" alt="' . $this->language->lang('DL_DOWNLOAD') . '" />',

	'S_DL_ACTION'		=> $this->helper->route('dl_ext_controller'),
	'S_ENABLE_RATE'		=> (isset($this->config['dl_enable_rate']) && $this->config['dl_enable_rate']) ? true : false,
	'S_SHOW_TOPIC_LINK'	=> ($dl_files['dl_topic']) ? true : false,

	'U_TOPIC'			=> append_sid($this->root_path . 'viewtopic.' . $this->php_ext, 't=' . $dl_files['dl_topic']),
	'U_EDIT'			=> $this->helper->route('dl_ext_controller', array('view' => 'modcp', 'action' => 'edit', 'df_id' => $file_id, 'cat_id' => $cat_id)),
	'U_EDIT_THUMBS'		=> $this->helper->route('dl_ext_controller', array('view' => 'thumbs', 'df_id' => $file_id, 'cat_id' => $cat_id)),
	'U_FAVORITE'		=> $u_favorite,
	'U_DL_SEARCH'		=> $this->helper->route('dl_ext_controller', array('view' => 'search')),
	'U_DL_AJAX'			=> $this->helper->route('dl_ext_controller', array('view' => 'ajax')),
));

/**
* Custom Download Fields
* Taken from memberlist.php phpBB 3.0.7-PL1
*/
$dl_fields = array();
include($ext_path . '/includes/helpers/dl_fields.' . $this->php_ext);
$cp = new \oxpus\dl_ext\includes\helpers\ custom_profile();
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

if (($dl_files['mod_list'] && $index[$cat_id]['allow_mod_desc']) || $todo || (isset($dl_fields['row']) && sizeof($dl_fields['row'])))
{
	$extra_tab = true;
}
else
{
	$extra_tab = false;
}

$detail_cat_names = array(
	0 => $this->language->lang('DL_FILE_DESCRIPTION'),
	1 => $this->language->lang('DL_DETAIL'),
	2 => ($ver_tab) ? $this->language->lang('DL_VERSIONS') : '',
	3 => ($extra_tab) ? $this->language->lang('DL_MOD_LIST_SHORT') : '',
	4 => ($hash_tab) ? $this->language->lang('DL_MOD_FILE_HASH_TABLE') : '',
	5 => ($s_comments_tab) ? $this->language->lang('DL_LAST_COMMENT') : '',
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
	$stopword_file = $ext_path . '/helpers/dl_stopwords.txt';
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
		$desc_flags		= $dl_files['desc_flags'];
		$similar_desc	= generate_text_for_display($similar_desc, $desc_uid, $desc_bitfield, $desc_flags);
	
		$this->template->assign_block_vars('similar_dl', array(
			'DOWNLOAD'		=> $similar_desc,
			'U_DOWNLOAD'	=> $this->helper->route('dl_ext_controller', array('view' => 'detail', 'df_id' => $similar_id)),
		));
	}
	
	$this->db->sql_freeresult($result);	
}

/*
* The end... Yes? Yes! Puh...
*/
