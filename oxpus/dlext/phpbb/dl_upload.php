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

$cat_auth = array();
$cat_auth = \oxpus\dlext\phpbb\classes\ dl_auth::dl_cat_auth($cat_id);

$physical_size = \oxpus\dlext\phpbb\classes\ dl_physical::read_dl_sizes();

if ($physical_size >= $this->config['dl_physical_quota'])
{
	trigger_error('DL_BLUE_EXPLAIN');
}

if (($this->config['dl_stop_uploads'] && !$this->auth->acl_get('a_')) || !sizeof($index) || (!$cat_auth['auth_up'] && !$index[$cat_id]['auth_up'] && !$this->auth->acl_get('a_')))
{
	trigger_error('DL_NO_PERMISSION');
}

// Initiate custom fields
include($this->ext_path . 'phpbb/helpers/dl_fields.' . $this->php_ext);

$cp = new \oxpus\dlext\phpbb\helpers\ custom_profile();

if ($submit)
{
	if (!check_form_key('dl_upload'))
	{
		trigger_error('FORM_INVALID');
	}

	$approve			= $this->request->variable('approve', 0);
	$description		= $this->request->variable('description', '', true);
	$file_traffic		= $this->request->variable('file_traffic', 0);
	$long_desc			= $this->request->variable('long_desc', '', true);
	$file_name_name		= $this->request->variable('file_name', '', true);

	$file_free			= $this->request->variable('file_free', 0);
	$file_extern		= $this->request->variable('file_extern', 0);
	$file_extern_size	= $this->request->variable('file_extern_size', '');

	$test				= $this->request->variable('test', '', true);
	$require			= $this->request->variable('require', '', true);
	$todo				= $this->request->variable('todo', '', true);
	$warning			= $this->request->variable('warning', '', true);
	$mod_desc			= $this->request->variable('mod_desc', '', true);
	$mod_list			= $this->request->variable('mod_list', 0);
	$mod_list			= ($mod_list) ? 1 : 0;

	$send_notify			= $this->request->variable('send_notify', 0);
	$disable_popup_notify	= $this->request->variable('disable_popup_notify', 0);

	$hacklist				= $this->request->variable('hacklist', 0);
	$hack_author			= $this->request->variable('hack_author', '', true);
	$hack_author_email		= $this->request->variable('hack_author_email', '', true);
	$hack_author_website	= $this->request->variable('hack_author_website', '', true);
	$hack_version			= $this->request->variable('hack_version', '', true);
	$hack_dl_url			= $this->request->variable('hack_dl_url', '', true);

	if (!$description)
	{
		trigger_error($this->language->lang('NO_SUBJECT'), E_USER_WARNING);
	}

	$allow_bbcode		= ($this->config['allow_bbcode']) ? true : false;
	$allow_urls			= true;
	$allow_smilies		= ($this->config['allow_smilies']) ? true : false;
	$desc_uid			= '';
	$desc_bitfield		= '';
	$long_desc_uid		= '';
	$long_desc_bitfield	= '';
	$mod_desc_uid		= '';
	$mod_desc_bitfield	= '';
	$warn_uid			= '';
	$warn_bitfield		= '';
	$todo_uid			= '';
	$todo_bitfield		= '';
	$desc_flags			= 0;
	$long_desc_flags	= 0;
	$mod_desc_flags		= 0;
	$warn_flags			= 0;
	$todo_flags			= 0;

	if ($description)
	{
		generate_text_for_storage($description, $desc_uid, $desc_bitfield, $desc_flags, $allow_bbcode, $allow_urls, $allow_smilies);
	}
	if ($long_desc)
	{
		generate_text_for_storage($long_desc, $long_desc_uid, $long_desc_bitfield, $long_desc_flags, $allow_bbcode, $allow_urls, $allow_smilies);
	}
	if ($mod_desc)
	{
		generate_text_for_storage($mod_desc, $mod_desc_uid, $mod_desc_bitfield, $mod_desc_flags, $allow_bbcode, $allow_urls, $allow_smilies);
	}
	if ($warning)
	{
		generate_text_for_storage($warning, $warn_uid, $warn_bitfield, $warn_flags, $allow_bbcode, $allow_urls, $allow_smilies);
	}
	if ($todo)
	{
		generate_text_for_storage($todo, $todo_uid, $todo_bitfield, $todo_flags, $allow_bbcode, $allow_urls, $allow_smilies);
	}

	if ($file_extern)
	{
		$file_traffic = 0;
	}
	else
	{
		$file_traffic = \oxpus\dlext\phpbb\classes\ dl_format::resize_value('dl_file_traffic', $file_traffic);
	}

	$ext_blacklist = \oxpus\dlext\phpbb\classes\ dl_auth::get_ext_blacklist();

	$this->language->add_lang('posting');

	if (!$file_extern)
	{
		$factory = $this->phpbb_container->get('files.factory');

		$form_name = 'dl_name';
		$file = $this->request->file($form_name);
		$extension = str_replace('.', '', trim(strrchr(strtolower($file['name']), '.')));
		$allowed_extensions = array($extension);
		$upload = $factory->get('upload')
			->set_allowed_extensions($allowed_extensions)
			->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : false));

		unset($file['local_mode']);
		$upload_file = $upload->handle_upload('files.types.form', $form_name);

		$file_size = $file['size'];
		$file_temp = $file['tmp_name'];
		$file_name = $file['name'];

		if ($this->config['dl_enable_blacklist'])
		{
			$extension = str_replace('.', '', trim(strrchr(strtolower($file_name), '.')));

			if (in_array($extension, $ext_blacklist))
			{
				trigger_error($this->language->lang('DL_FORBIDDEN_EXTENSION'), E_USER_ERROR);
			}
		}

		$upload_file->error = array();

		$error_count = sizeof($upload_file->error);
		if ($error_count > 1 && $file_name)
		{
			$upload_file->remove();
			trigger_error(implode('<br />', $upload_file->error), E_USER_ERROR);
		}

		if (!$file_name)
		{
			$upload_file->remove();
			trigger_error($this->language->lang('DL_NO_FILENAME_ENTERED'), E_USER_ERROR);
		}

		if (!$this->config['dl_traffic_off'])
		{
			$remain_traffic = 0;

			if ($this->user->data['is_registered'] && DL_OVERALL_TRAFFICS == true)
			{
				$remain_traffic = $this->config['dl_overall_traffic'] - $this->config['dl_remain_traffic'];
			}
			else if (!$this->user->data['is_registered'] && DL_GUESTS_TRAFFICS == true)
			{
				$remain_traffic = $this->config['dl_overall_guest_traffic'] - $this->config['dl_remain_guest_traffic'];
			}

			if($file_size == 0 || ($remain_traffic && $file_size > $remain_traffic && $this->config['dl_upload_traffic_count']))
			{
				$upload_file->remove();
				trigger_error($this->language->lang('DL_NO_UPLOAD_TRAFFIC'), E_USER_ERROR);
			}
		}

		$dl_path = $index[$cat_id]['cat_path'];

		$real_file = md5($file_name) . '.' . $upload_file->get_extension($file_name);

		$i = 0;
		while(@file_exists(DL_EXT_FILES_FOLDER . $dl_path . $real_file))
		{
			$real_file = md5($i . $file_name);
			$i++;
		}
	}
	else
	{
		if (empty($file_name_name))
		{
			trigger_error($this->language->lang('DL_NO_EXTERNAL_URL'), E_USER_ERROR);
		}

		$file_name = $file_name_name;
		$file_size = \oxpus\dlext\phpbb\classes\ dl_format::resize_value('dl_extern_size', $file_extern_size);
		$real_file = '';
	}

	if ($this->config['dl_thumb_fsize'] && $index[$cat_id]['allow_thumbs'])
	{
		$min_pic_width = 10;
		$allowed_imagetypes = array('gif','png','jpg','bmp');

		$factory = $this->phpbb_container->get('files.factory');
		$upload_image = $factory->get('upload')
			->set_allowed_extensions($allowed_imagetypes)
			->set_max_filesize($this->config['dl_thumb_fsize'])
			->set_allowed_dimensions(
				$min_pic_width,
				$min_pic_width,
				$this->config['dl_thumb_xsize'],
				$this->config['dl_thumb_ysize'])
			->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : false));

		$form_name = 'thumb_name';

		$upload_thumb_file = $this->request->file($form_name);
		unset($upload_thumb_file['local_mode']);
		$thumb_file = $upload_image->handle_upload('files.types.form', $form_name);

		$thumb_size = $upload_thumb_file['size'];
		$thumb_temp = $upload_thumb_file['tmp_name'];
		$thumb_name = $upload_thumb_file['name'];

		$error_count = sizeof($thumb_file->error);
		if ($error_count > 1 && $thumb_name)
		{
			$thumb_file->remove();
			trigger_error(implode('<br />', $thumb_file->error), E_USER_ERROR);
		}

		$thumb_file->error = array();

		if ($thumb_name)
		{
			$pic_size = @getimagesize($thumb_temp);
			$pic_width = $pic_size[0];
			$pic_height = $pic_size[1];

			if (!$pic_width || !$pic_height)
			{
				$thumb_file->remove();
				trigger_error($this->language->lang('DL_UPLOAD_ERROR'), E_USER_ERROR);
			}

			if ($pic_width > $this->config['dl_thumb_xsize'] || $pic_height > $this->config['dl_thumb_ysize'] || (sprintf("%u", @filesize($thumb_temp) > $this->config['dl_thumb_fsize'])))
			{
				$thumb_file->remove();
				trigger_error($this->language->lang('DL_THUMB_TO_BIG'), E_USER_ERROR);
			}
		}
	}

	// validate custom profile fields
	$error = $cp_data = $cp_error = array();
	$cp->submit_cp_field($this->user->get_iso_lang_id(), $cp_data, $error);

	// Stop here, if custom fields are invalid!
	if (sizeof($error))
	{
		trigger_error(implode('<br />', $error), E_USER_WARNING);
	}

	if($cat_id)
	{
		if (!$file_extern)
		{
			$file['name'] = $real_file;
			$upload_file->set_upload_ary($file);

			if (substr($dl_path, -1) == '/')
			{
				$dest_path = DL_EXT_FILES_FOLDER . substr($dl_path, 0, -1);
			}
			else
			{
				$dest_path = DL_EXT_FILES_FOLDER . $dl_path;
			}
			$dest_path = str_replace($this->root_path, '', $dest_path);
			$upload_file->move_file($dest_path, false, false, CHMOD_ALL);

			$error_count = sizeof($upload_file->error);
			if ($error_count)
			{
				$upload_file->remove();
				trigger_error(implode('<br />', $upload_file->error), E_USER_ERROR);
			}

			$hash_method = $this->config['dl_file_hash_algo'];
			$func_hash = $hash_method . '_file';
			$file_hash = $func_hash(DL_EXT_FILES_FOLDER . $dl_path . $real_file);
		}
		else
		{
			$file_hash = '';
		}

		$current_time = time();
		$current_user = $this->user->data['user_id'];

		if ($this->config['dl_set_add'] == 1 && $this->config['dl_set_user'])
		{
			$current_user = $this->config['dl_set_user'];
		}

		if ($this->config['dl_set_add'] == 2 && $index[$cat_id]['dl_set_add'] && $index[$cat_id]['dl_set_user'])
		{
			$current_user = $index[$cat_id]['dl_set_user'];
		}

		$approve = ($index[$cat_id]['must_approve'] && !$cat_auth['auth_mod'] && !$index[$cat_id]['auth_mod'] && !($this->auth->acl_get('a_') && $this->user->data['is_registered'])) ? 0 : $approve;

		unset($sql_array);

		if (!$cat_auth['auth_mod'] && !$index[$cat_id]['auth_mod'] && !$index[$cat_id]['allow_mod_desc'] && !($this->auth->acl_get('a_') && $this->user->data['is_registered']))
		{
			$test = $require = $warning = $mod_desc = '';
		}

		$sql_array = array(
				'file_name'				=> $file_name,
				'real_file'				=> $real_file,
				'file_hash'				=> $file_hash,
				'cat'					=> $cat_id,
				'description'			=> $description,
				'long_desc'				=> $long_desc,
				'free'					=> $file_free,
				'extern'				=> $file_extern,
				'desc_uid'				=> $desc_uid,
				'desc_bitfield'			=> $desc_bitfield,
				'desc_flags'			=> $desc_flags,
				'long_desc_uid'			=> $long_desc_uid,
				'long_desc_bitfield'	=> $long_desc_bitfield,
				'long_desc_flags'		=> $long_desc_flags,
				'hacklist'				=> $hacklist,
				'hack_author'			=> $hack_author,
				'hack_author_email'		=> $hack_author_email,
				'hack_author_website'	=> $hack_author_website,
				'hack_version'			=> $hack_version,
				'hack_dl_url'			=> $hack_dl_url,
				'todo'					=> $todo,
				'approve'				=> $approve,
				'file_size'				=> $file_size,
				'change_time'			=> $current_time,
				'add_time'				=> $current_time,
				'change_user'			=> $current_user,
				'add_user'				=> $current_user,
				'test'					=> $test,
				'req'					=> $require,
				'warning'				=> $warning,
				'mod_desc'				=> $mod_desc,
				'file_traffic'			=> $file_traffic,
				'todo_uid'				=> $todo_uid,
				'todo_bitfield'			=> $todo_bitfield,
				'todo_flags'			=> $todo_flags);

		if (!$cat_auth['auth_mod'] && !$index[$cat_id]['auth_mod'] && !$index[$cat_id]['allow_mod_desc'] && !($this->auth->acl_get('a_') && $this->user->data['is_registered']))
		{
			$sql = 'INSERT INTO ' . DOWNLOADS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_array);
		}
		else
		{
			$sql_array = array_merge($sql_array, array(
				'mod_list'				=> $mod_list,
				'mod_desc_uid'			=> $mod_desc_uid,
				'mod_desc_bitfield'		=> $mod_desc_bitfield,
				'mod_desc_flags'		=> $mod_desc_flags,
				'warn_uid'				=> $warn_uid,
				'warn_bitfield'			=> $warn_bitfield,
				'warn_flags'			=> $warn_flags));

			$sql = 'INSERT INTO ' . DOWNLOADS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_array);
		}

		$this->db->sql_query($sql);

		$next_id = $this->db->sql_nextid();

		// Update Custom Fields
		$cp->update_profile_field_data($next_id, $cp_data);

		if (isset($thumb_name) && $thumb_name != '')
		{
			$dest_folder = str_replace($this->root_path, '', substr(DL_EXT_THUMBS_FOLDER, 0, -1));

			$upload_thumb_file['name'] = $next_id . '_' . $thumb_name;
			$thumb_file->set_upload_ary($upload_thumb_file);

			$thumb_file->move_file($dest_folder, false, false, CHMOD_ALL);

			$thumb_message = '<br />' . $this->language->lang('DL_THUMB_UPLOAD');

			$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
				'thumbnail' => $next_id . '_' . $thumb_name)) . ' WHERE id = ' . (int) $next_id;
			$this->db->sql_query($sql);
		}
		else
		{
			$thumb_message = '';
		}

		if ($index[$cat_id]['statistics'])
		{
			if ($index[$cat_id]['stats_prune'])
			{
				$stat_prune = \oxpus\dlext\phpbb\classes\ dl_main::dl_prune_stats($cat_id, $index[$cat_id]['stats_prune']);
			}

			$browser = \oxpus\dlext\phpbb\classes\ dl_init::dl_client($this->user->data['session_browser'], $this->ext_path);

			$sql = 'INSERT INTO ' . DL_STATS_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
				'cat_id'		=> $cat_id,
				'id'			=> $next_id,
				'user_id'		=> $this->user->data['user_id'],
				'username'		=> $this->user->data['username'],
				'traffic'		=> $file_size,
				'direction'		=> 1,
				'user_ip'		=> $this->user->data['session_ip'],
				'browser'		=> $browser,
				'time_stamp'	=> time()));
			$this->db->sql_query($sql);
		}

		if ($approve)
		{
			$processing_user = \oxpus\dlext\phpbb\classes\ dl_auth::dl_auth_users($cat_id, 'auth_dl');

			$email_template = 'downloads_new_notify';

			$sql = 'SELECT user_email, username, user_lang FROM ' . USERS_TABLE . '
				WHERE user_allow_new_download_email = 1
					AND ' . $this->db->sql_in_set('user_id', explode(',', $processing_user));

			$notification = $this->phpbb_container->get('notification_manager');
			$notification_data = array('notification_id' => $next_id);
			$notification->add_notifications('oxpus.dlext.notification.type.dlext', $notification_data);

			\oxpus\dlext\phpbb\classes\ dl_topic::gen_dl_topic($next_id, $this->helper);
		}
		else
		{
			$processing_user = \oxpus\dlext\phpbb\classes\ dl_auth::dl_auth_users($cat_id, 'auth_mod');

			$email_template = 'downloads_approve_notify';

			$sql = 'SELECT user_email, username, user_lang FROM ' . USERS_TABLE . '
				WHERE user_allow_new_download_email = 1
					AND (' . $this->db->sql_in_set('user_id', explode(',', $processing_user)) . '
					OR user_type = ' . USER_FOUNDER . ')';
		}

		if (!$this->config['dl_disable_email'] && !$send_notify)
		{
			$mail_data = array(
				'query'				=> $sql,
				'email_template'	=> $email_template,
				'description'		=> $description,
				'long_desc'			=> $long_desc,
				'cat_name'			=> $index[$cat_id]['cat_name_nav'],
				'cat_id'			=> $cat_id,
			);

			\oxpus\dlext\phpbb\classes\ dl_email::send_dl_notify($mail_data, $this->helper, $this->ext_path);
		}

		if (!$this->config['dl_disable_popup'] && !$disable_popup_notify && $approve)
		{
			$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
				'user_new_download' => 1)) . '
					WHERE user_allow_new_download_popup = 1
					AND ' . $this->db->sql_in_set('user_id', explode(',', $processing_user));
			$this->db->sql_query($sql);
		}


		if ($this->config['dl_upload_traffic_count'] && !$file_extern && !$this->config['dl_traffic_off'])
		{
			if ($this->user->data['is_registered'] && DL_OVERALL_TRAFFICS == true)
			{
				$this->config['dl_remain_traffic'] += $file_size;

				$sql = 'UPDATE ' . DL_REM_TRAF_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
					'config_value' => $this->config['dl_remain_traffic'])) . " WHERE config_name = 'dl_remain_traffic'";
				$this->db->sql_query($sql);
			}
			else if (!$this->user->data['is_registered'] && DL_GUESTS_TRAFFICS == true)
			{
				$this->config['dl_remain_guest_traffic'] += $file_size;

				$sql = 'UPDATE ' . DL_REM_TRAF_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
					'config_value' => $this->config['dl_remain_guest_traffic'])) . " WHERE config_name = 'dl_remain_guest_traffic'";
				$this->db->sql_query($sql);
			}
		}

		$approve_message = ($approve) ? '' : '<br />' . $this->language->lang('DL_MUST_BE_APPROVED');

		$message = $this->language->lang('DOWNLOAD_ADDED') . $thumb_message . $approve_message . '<br /><br />' . $this->language->lang('CLICK_RETURN_DOWNLOADS', '<a href="' . $this->helper->route('oxpus_dlext_controller', array('cat' => $cat_id)) . '">', '</a>');
		if ($cat_auth['auth_up'])
		{
			$message .= '<br /><br />' . $this->language->lang('DL_UPLOAD_ONE_MORE', '<a href="' . $this->helper->route('oxpus_dlext_controller', array('view' => 'upload', 'cat_id' => $cat_id)) . '">', '</a>');
		}

		// Purge the files cache
		@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_cat_counts.' . $this->php_ext);
		@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_file_preset.' . $this->php_ext);

		meta_refresh(3, $this->helper->route('oxpus_dlext_controller', array('cat' => $cat_id)));

		trigger_error($message);
	}
}

$this->template->set_filenames(array(
	'body' => 'dl_edit_body.html')
);

$bg_row = 0;

if ($cat_auth['auth_mod'] || $index[$cat_id]['auth_mod'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered']))
{
	$this->template->assign_var('S_MODCP', true);
	$bg_row = 1;
}

if (!$this->config['dl_disable_email'])
{
	$this->template->assign_var('S_EMAIL_BLOCK', true);
	$bg_row = 1;
}

if (!$this->config['dl_disable_popup'] && $this->config['dl_disable_popup_notify'])
{
	$this->template->assign_var('S_POPUP_NOTIFY', true);
	$bg_row = 1;
}

if ($index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
{
	$this->template->assign_var('S_ALLOW_THUMBS', true);
}

if ($this->config['dl_use_hacklist'] && $this->auth->acl_get('a_') && $this->user->data['is_registered'])
{
	$this->template->assign_var('S_USE_HACKLIST', true);
	$hacklist_on = ($bg_row) ? true : 0;
	$bg_row = 1 - $bg_row;
}

if ($index[$cat_id]['allow_mod_desc'])
{
	$this->template->assign_var('S_ALLOW_EDIT_MOD_DESC', true);
	$mod_block_bg = ($bg_row) ? true : 0;
}

if ($this->config['dl_upload_traffic_count'] && !$this->config['dl_traffic_off'])
{
	$this->template->assign_var('S_UPLOAD_TRAFFIC', true);
}

$s_cat_select = '<select name="cat_id">';
$s_cat_select .= \oxpus\dlext\phpbb\classes\ dl_extra::dl_dropdown(0, 0, $cat_id, 'auth_up');
$s_cat_select .= '</select>';

$thumbnail_explain = $this->language->lang('DL_THUMB_DIM_SIZE', $this->config['dl_thumb_xsize'], $this->config['dl_thumb_ysize'], \oxpus\dlext\phpbb\classes\ dl_format::dl_size($this->config['dl_thumb_fsize']));

$s_hidden_fields = array();

if (!$cat_auth['auth_mod'] && !$index[$cat_id]['auth_mod'] && !($this->auth->acl_get('a_') && $this->user->data['is_registered']))
{
	$approve = ($index[$cat_id]['must_approve']) ? 0 : true;
	$s_hidden_fields = array_merge($s_hidden_fields, array('approve' => $approve));
}

if ($this->config['dl_disable_email'])
{
	$s_hidden_fields = array_merge($s_hidden_fields, array('send_notify' => 0));
}

$ext_blacklist = \oxpus\dlext\phpbb\classes\ dl_auth::get_ext_blacklist();
if (sizeof($ext_blacklist))
{
	$blacklist_explain = '<br />' . $this->language->lang('DL_FORBIDDEN_EXT_EXPLAIN', implode(', ', $ext_blacklist));
}
else
{
	$blacklist_explain = '';
}

$s_check_free = '<select name="file_free">';
$s_check_free .= '<option value="0">' . $this->language->lang('NO') . '</option>';
$s_check_free .= '<option value="1">' . $this->language->lang('YES') . '</option>';
$s_check_free .= '<option value="2">' . $this->language->lang('DL_IS_FREE_REG') . '</option>';
$s_check_free .= '</select>';

$s_traffic_range = '<select name="dl_t_quote">';
$s_traffic_range .= '<option value="byte">' . $this->language->lang('DL_BYTES') . '</option>';
$s_traffic_range .= '<option value="kb">' . $this->language->lang('DL_KB') . '</option>';
$s_traffic_range .= '<option value="mb">' . $this->language->lang('DL_MB') . '</option>';
$s_traffic_range .= '<option value="gb">' . $this->language->lang('DL_GB') . '</option>';
$s_traffic_range .= '</select>';

$s_file_ext_size_range = '<select name="dl_e_quote">';
$s_file_ext_size_range .= '<option value="byte">' . $this->language->lang('DL_BYTES') . '</option>';
$s_file_ext_size_range .= '<option value="kb">' . $this->language->lang('DL_KB') . '</option>';
$s_file_ext_size_range .= '<option value="mb">' . $this->language->lang('DL_MB') . '</option>';
$s_file_ext_size_range .= '<option value="gb">' . $this->language->lang('DL_GB') . '</option>';
$s_file_ext_size_range .= '</select>';

$s_hacklist = '<select name="hacklist">';
$s_hacklist .= '<option value="0">' . $this->language->lang('NO') . '</option>';
$s_hacklist .= '<option value="1">' . $this->language->lang('YES') . '</option>';
$s_hacklist .= '<option value="2">' . $this->language->lang('DL_MOD_LIST') . '</option>';
$s_hacklist .= '</select>';

$this->template->assign_var('S_CAT_CHOOSE', true);

add_form_key('dl_upload');

$dl_files_page_title = $this->language->lang('DL_UPLOAD');

$file_size_ary		= \oxpus\dlext\phpbb\classes\ dl_format::dl_size(0, 2, 'select');
$file_size			= $file_size_ary['size_out'];
$file_size_range	= $file_size_ary['range'];

$this->template->assign_vars(array(
	'DL_FILES_TITLE'			=> $dl_files_page_title,
	'DL_THUMBNAIL_SECOND'		=> $thumbnail_explain,
	'EXT_BLACKLIST'				=> $blacklist_explain,

	'L_DL_NAME_EXPLAIN'					=> 'DL_NAME',
	'L_DL_APPROVE_EXPLAIN'				=> 'DL_APPROVE',
	'L_DL_CAT_NAME_EXPLAIN'				=> 'DL_CHOOSE_CATEGORY',
	'L_DL_DESCRIPTION_EXPLAIN'			=> 'DL_FILE_DESCRIPTION',
	'L_DL_EXTERN_EXPLAIN'				=> 'DL_EXTERN_UP',
	'L_DL_HACK_AUTHOR_EXPLAIN'			=> 'DL_HACK_AUTOR',
	'L_DL_HACK_AUTHOR_EMAIL_EXPLAIN'	=> 'DL_HACK_AUTOR_EMAIL',
	'L_DL_HACK_AUTHOR_WEBSITE_EXPLAIN'	=> 'DL_HACK_AUTOR_WEBSITE',
	'L_DL_HACK_DL_URL_EXPLAIN'			=> 'DL_HACK_DL_URL',
	'L_DL_HACK_VERSION_EXPLAIN'			=> 'DL_HACK_VERSION',
	'L_DL_HACKLIST_EXPLAIN'				=> 'DL_HACKLIST',
	'L_DL_IS_FREE_EXPLAIN'				=> 'DL_IS_FREE',
	'L_DL_MOD_DESC_EXPLAIN'				=> 'DL_MOD_DESC',
	'L_DL_MOD_LIST_EXPLAIN'				=> 'DL_MOD_LIST',
	'L_DL_MOD_REQUIRE_EXPLAIN'			=> 'DL_MOD_REQUIRE',
	'L_DL_MOD_TEST_EXPLAIN'				=> 'DL_MOD_TEST',
	'L_DL_MOD_TODO_EXPLAIN'				=> 'DL_MOD_TODO',
	'L_DL_MOD_WARNING_EXPLAIN'			=> 'DL_MOD_WARNING',
	'L_DL_TRAFFIC_EXPLAIN'				=> 'DL_TRAFFIC',
	'L_DL_UPLOAD_FILE_EXPLAIN'			=> 'DL_UPLOAD_FILE',
	'L_DL_THUMBNAIL_EXPLAIN'			=> 'DL_THUMB',
	'L_CHANGE_TIME_EXPLAIN'				=> 'DL_NO_CHANGE_EDIT_TIME',
	'L_DISABLE_POPUP_EXPLAIN'			=> 'DL_DISABLE_POPUP',
	'L_DL_SEND_NOTIFY_EXPLAIN'			=> 'DL_DISABLE_EMAIL',

	'DESCRIPTION'			=> '',
	'SELECT_CAT'			=> $s_cat_select,
	'LONG_DESC'				=> '',
	'URL'					=> '',
	'CHECKEXTERN'			=> '',
	'TRAFFIC'				=> 0,
	'APPROVE'				=> 'checked="checked"',
	'MOD_DESC'				=> '',
	'MOD_LIST'				=> '',
	'MOD_REQUIRE'			=> '',
	'MOD_TEST'				=> '',
	'MOD_TODO'				=> '',
	'MOD_WARNING'			=> '',
	'FILE_EXT_SIZE'			=> $file_size,

	'HACKLIST_BG'			=> (isset($hacklist_on) && $hacklist_on) ? ' bg2' : '',
	'MOD_BLOCK_BG'			=> (isset($mod_block_bg) && $mod_block_bg) ? ' bg2' : '',

	'MAX_UPLOAD_SIZE'		=> $this->language->lang('DL_UPLOAD_MAX_FILESIZE', \oxpus\dlext\phpbb\classes\ dl_physical::dl_max_upload_size()),

	'ENCTYPE'	=> 'enctype="multipart/form-data"',

	'S_TODO_LINK_ONOFF'		=> ($this->config['dl_todo_onoff']) ? true : false,
	'S_CHECK_FREE'			=> $s_check_free,
	'S_TRAFFIC_RANGE'		=> $s_traffic_range,
	'S_FILE_EXT_SIZE_RANGE'	=> $s_file_ext_size_range,
	'S_HACKLIST'			=> $s_hacklist,
	'S_DOWNLOADS_ACTION'	=> $this->helper->route('oxpus_dlext_controller', array('view' => 'upload')),
	'S_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields),
	'S_ADD_DL'				=> true,
));

// Init and display the custom fields with the existing data
$cp->get_profile_fields($df_id);
$cp->generate_profile_fields($this->user->get_iso_lang_id());
