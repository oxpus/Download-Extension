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

$ver_id			= $this->request->variable('ver_id', 0);
$ver_file_id	= $this->request->variable('ver_file_id', 0);

$sql = 'SELECT * FROM ' . DL_VERSIONS_TABLE . '
	WHERE ver_id = ' . (int) $ver_id;
$result = $this->db->sql_query($sql);
$ver_exists = $this->db->sql_affectedrows($result);
$ver_data = $this->db->sql_fetchrow($result);
$this->db->sql_freeresult($result);

if (!$ver_exists)
{
	redirect($this->helper->route('oxpus_dlext_controller'));
}

$df_id = $ver_data['dl_id'];

/*
* default entry point for download details
*/
$dl_file = array();
$dl_file = \oxpus\dlext\phpbb\classes\ dl_files::all_files(0, '', 'ASC', '', $df_id, 0, '*');

if (!$dl_file)
{
	redirect($this->helper->route('oxpus_dlext_controller'));
}

$cat_id = $dl_file['cat'];

$cat_auth = array();
$cat_auth = \oxpus\dlext\phpbb\classes\ dl_auth::dl_cat_auth($cat_id);

$auth_view = \oxpus\dlext\phpbb\classes\ dl_auth::user_auth($cat_id, 'auth_view');
$auth_dl = \oxpus\dlext\phpbb\classes\ dl_auth::user_auth($cat_id, 'auth_dl');

/*
* check the permissions
*/
$user_can_alltimes_load = false;

if (($cat_auth['auth_mod'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered'])) && !\oxpus\dlext\phpbb\classes\ dl_auth::user_banned())
{
	$user_can_alltimes_load = true;
	$user_is_mod = true;
}
else
{
	$user_is_mod = false;
}

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

$check_status = array();
$check_status = \oxpus\dlext\phpbb\classes\ dl_status::status($df_id, $this->helper);

if (!$dl_file['id'] || !$auth_view)
{
	trigger_error('DL_NO_PERMISSION');
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

$inc_module = true;
page_header($this->language->lang('DOWNLOADS') . ' - ' . $dl_file['description']);

$ver_can_edit = false;

if (($user_is_mod || $user_is_admin || $user_is_founder) || ($this->config['dl_edit_own_downloads'] && $dl_file['add_user'] == $this->user->data['user_id']))
{
	$ver_can_edit = true;
}

/*
* prepare the download version for displaying
*/
$description	= generate_text_for_display($dl_file['description'], $dl_file['desc_uid'], $dl_file['desc_bitfield'], $dl_file['desc_flags']) . '&nbsp;' . $dl_file['hack_version'];
$mini_icon		= \oxpus\dlext\phpbb\classes\ dl_status::mini_status_file($cat_id, $df_id);
$ver_version	= '&nbsp;' . $ver_data['ver_version'];
$ver_desc		= generate_text_for_display($ver_data['ver_text'], $ver_data['ver_uid'], $ver_data['ver_bitfield'], $ver_data['ver_flags']);
$file_status	= array();
$file_status	= \oxpus\dlext\phpbb\classes\ dl_status::status($df_id, $this->helper);
$status			= $file_status['status_detail'];
$file_name		= $ver_data['ver_file_name'];
$file_load		= $file_status['auth_dl'];

if ($ver_data['ver_file_size'])
{
	$file_size_out = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($ver_data['ver_file_size'], 2);
}
else
{
	$file_size_out = $this->language->lang('DL_NOT_AVAILIBLE');
}

$cat_name = $index[$cat_id]['cat_name'];
$cat_view = $index[$cat_id]['nav_path'];
$cat_desc = $index[$cat_id]['description'];

/*
* Download an attached file?
*/
if ($action == 'load' && $ver_id && $ver_file_id && $auth_dl)
{
	$sql = 'SELECT ver_id, real_name, file_name, file_type FROM ' . DL_VER_FILES_TABLE . '
		WHERE ver_file_id = ' . (int) $ver_file_id;
	$result = $this->db->sql_query($sql);
	$row = $this->db->sql_fetchrow($result);
	$this->db->sql_freeresult($result);

	if ($row['file_type'] == 0 && $row['ver_id'] == $ver_id)
	{
		include_once($this->root_path . 'includes/functions_download.' . $this->php_ext);

		$this->language->add_lang('viewtopic');

		$dl_file_url = str_replace($this->root_path, '', DL_EXT_VER_FILES_FOLDER);

		$dl_file_data = array(
			'attach_id'				=> 0,
			'is_orphan'				=> false,
			'physical_filename'		=> $row['real_name'],
			'real_filename'			=> $row['file_name'],
			'mimetype'				=> 'application/octetstream',
			'filesize'				=> sprintf("%u", @filesize($dl_file_url . $row['real_name'])),
			'filetime'				=> @filemtime($dl_file_url . $row['real_name']),
		);

		send_file_to_browser($dl_file_data, $dl_file_url, ATTACHMENT_CATEGORY_NONE);
		file_gc();
	}

	trigger_error('DL_NO_ACCESS');
}
else if ($action == 'load')
{
	$action = $submit = '';
}

/*
* Save the version - update existing one or insert new given
*/
if ($submit && $action == 'save' && $ver_can_edit && $ver_id)
{
	$this->language->add_lang('posting');

	// Drop attachments
	$del_files = $this->request->variable('ver_title_del', array(0 => 0));

	$dropped_files = array(0);

	foreach ($del_files as $key => $value)
	{
		$sql = 'SELECT file_type, real_name FROM ' . DL_VER_FILES_TABLE . '
			WHERE ver_file_id = ' . (int) $value;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			switch ($row['file_type'])
			{
				case 1:
					@unlink(DL_EXT_VER_IMAGES_FOLDER . $row['real_name']);
				break;
				default:
					@unlink(DL_EXT_VER_FILES_FOLDER . $row['real_name']);
			}
		}

		$this->db->sql_freeresult($result);

		$dropped_files[] = $value;
	}

	$sql = 'DELETE FROM ' . DL_VER_FILES_TABLE . '
		WHERE ' . $this->db->sql_in_set('ver_file_id', $dropped_files);
	$this->db->sql_query($sql);

	// Update file titles
	$ver_title = $this->request->variable('ver_title', array(0 => ''));

	foreach($ver_title as $key => $value)
	{
		if (!in_array($key, $dropped_files))
		{
			$sql = 'UPDATE ' . DL_VER_FILES_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
				'file_title'	=> $value,
			)) . ' WHERE ver_file_id = ' . (int) $key;
			$this->db->sql_query($sql);
		}
	}

	// Upload new file
	$factory = $this->phpbb_container->get('files.factory');
	$form_name = 'ver_new_file';
	$file = $this->request->file($form_name);
	$extension = str_replace('.', '', trim(strrchr(strtolower($file['name']), '.')));
	$allowed_extensions = array($extension);
	$upload = $factory->get('upload')
		->set_allowed_extensions($allowed_extensions)
		->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : false));

	unset($file['local_mode']);
	$ver_file = $upload->handle_upload('files.types.form', $form_name);

	$ver_file_size = $file['size'];
	$ver_file_temp = $file['tmp_name'];
	$ver_file_name = $file['name'];

	$error_count = sizeof($ver_file->error);
	if ($error_count > 1 && $ver_file_name)
	{
		$ver_file->remove();
		trigger_error(implode('<br />', $ver_file->error), E_USER_ERROR);
	}

	$ver_file->error = array();

	if ($ver_file_name)
	{
		do
		{
			$file_name = $df_id . '_' . $ver_id . '_' . (md5(microtime() . $ver_file_name) . '.' . $extension);
		}
		while (file_exists(DL_EXT_VER_FILES_FOLDER . $file_name));

		$file['name'] = $file_name;
		$dest_folder = str_replace($this->root_path, '', substr(DL_EXT_VER_FILES_FOLDER, 0, -1));

		$ver_file->set_upload_ary($file);
		$ver_file->move_file($dest_folder, false, false, CHMOD_ALL);

		$ver_file_title = $this->request->variable('ver_new_file_title', '', true);

		$sql = 'INSERT INTO ' . DL_VER_FILES_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
			'dl_id'			=> $df_id,
			'ver_id'		=> $ver_id,
			'real_name'		=> $file_name,
			'file_name'		=> $ver_file_name,
			'file_title'	=> $ver_file_title,
			'file_type'		=> 0,
		));
		$this->db->sql_query($sql);
	}
	else
	{
		$ver_file->remove();
	}

	// Upload new image
	$factory = $this->phpbb_container->get('files.factory');
	$form_name = 'ver_new_image';
	$file = $this->request->file($form_name);
	$extension = str_replace('.', '', trim(strrchr(strtolower($file['name']), '.')));
	$allowed_extensions = array($extension);
	$upload_image = $factory->get('upload')
		->set_allowed_extensions($allowed_extensions)
		->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : false));

	unset($file['local_mode']);
	$ver_image = $upload_image->handle_upload('files.types.form', $form_name);

	$ver_image_size = $file['size'];
	$ver_image_temp = $file['tmp_name'];
	$ver_image_name = $file['name'];

	$error_count = sizeof($ver_image->error);
	if ($error_count > 1 && $ver_image_name)
	{
		$ver_image->remove();
		trigger_error(implode('<br />', $ver_image->error), E_USER_ERROR);
	}

	$ver_image->error = array();

	if ($ver_image_name)
	{
		$pic_size = @getimagesize($ver_image_temp);
		$pic_width = $pic_size[0];
		$pic_height = $pic_size[1];

		if (!$pic_width || !$pic_height)
		{
			$ver_image->remove();
			trigger_error($this->language->lang('DL_UPLOAD_ERROR'), E_USER_ERROR);
		}

		if ($pic_width > $this->config['dl_thumb_xsize'] || $pic_height > $this->config['dl_thumb_ysize'] || (sprintf("%u", @filesize($ver_image_temp) > $this->config['dl_thumb_fsize'])))
		{
			$ver_image->remove();
			trigger_error($this->language->lang('DL_THUMB_TO_BIG'), E_USER_ERROR);
		}

		do
		{
			$img_name = $df_id . '_' . $ver_id . '_' . (md5(microtime() . $ver_image_name)) . '.' . $extension;
		}
		while (file_exists(DL_EXT_VER_IMAGES_FOLDER . $img_name));

		$file['name'] = $img_name;
		$dest_folder = str_replace($this->root_path, '', substr(DL_EXT_VER_IMAGES_FOLDER, 0, -1));

		$ver_image->set_upload_ary($file);
		$ver_image->move_file($dest_folder, false, false, CHMOD_ALL);

		$ver_file_title = $this->request->variable('ver_new_image_title', '', true);

		$sql = 'INSERT INTO ' . DL_VER_FILES_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
			'dl_id'			=> $df_id,
			'ver_id'		=> $ver_id,
			'real_name'		=> $img_name,
			'file_name'		=> $ver_image_name,
			'file_title'	=> $ver_file_title,
			'file_type'		=> 1,
		));
		$this->db->sql_query($sql);
	}
	else
	{
		$ver_image->remove();
	}

	// Update release itself
	$ver_version	= $this->request->variable('ver_version', '', true);
	$ver_text		= $this->request->variable('ver_text', '', true);
	$ver_active		= $this->request->variable('ver_active', 0);

	$allow_bbcode		= ($this->config['allow_bbcode']) ? true : false;
	$allow_urls			= true;
	$allow_smilies		= ($this->config['allow_smilies']) ? true : false;
	$ver_uid			= '';
	$ver_bitfield		= '';
	$ver_flags			= 0;

	generate_text_for_storage($ver_text, $ver_uid, $ver_bitfield, $ver_flags, $allow_bbcode, $allow_urls, $allow_smilies);

	$sql = 'UPDATE ' . DL_VERSIONS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
		'ver_version'		=> $ver_version,
		'ver_text'			=> $ver_text,
		'ver_uid'			=> $ver_uid,
		'ver_bitfield'		=> $ver_bitfield,
		'ver_flags'			=> $ver_flags,
		'ver_active'		=> $ver_active,
		'ver_change_time'	=> time(),
		'ver_change_user'	=> $this->user->data['user_id'],
	)) . ' WHERE dl_id = ' . (int) $df_id . ' AND ver_id = ' . (int) $ver_id;

	$this->db->sql_query($sql);

	$s_redirect_params = array(
		'view'		=> 'version',
		'action'	=> 'save',
		'ver_id'	=> $ver_id,
		'df_id'		=> $df_id,
	);

	$s_redirect = $this->helper->route('oxpus_dlext_controller', $s_redirect_params);
	redirect($s_redirect);
}

/*
* Edit existing version or add new one
*/

if ($action == 'edit' && $ver_can_edit)
{
	$text_ary				= generate_text_for_edit($ver_data['ver_text'], $ver_data['ver_uid'], $ver_data['ver_flags']);
	$ver_data['ver_text']	= $text_ary['text'];

	// Fetch all attachments for this release
	$sql = 'SELECT ver_file_id, file_type, real_name, file_title, file_name FROM ' . DL_VER_FILES_TABLE . '
		WHERE ver_id = ' . (int) $ver_id . '
		ORDER BY file_title';
	$result = $this->db->sql_query($sql);

	$images_exists = false;

	while ($row = $this->db->sql_fetchrow($result))
	{
		switch ($row['file_type'])
		{
			case 1:
				$file_path = DL_EXT_VER_IMAGES_WFOLDER . $row['real_name'];
				$tpl_block = 'images';
				$images_exists = true;
			break;
			default:
				$file_path = $row['real_name'];
				$tpl_block = 'files';
		}

		$this->template->assign_block_vars($tpl_block, array(
			'LINK'			=> $file_path,
			'FILE_NAME'		=> $row['file_name'],
			'NAME'			=> $row['file_title'],
			'VER_FILE_ID'	=> $row['ver_file_id'],
		));
	}

	$this->db->sql_freeresult($result);

	$s_form_ary = array(
		'view'		=> 'version',
		'action'	=> 'save',
		'ver_id'	=> $ver_id,
		'df_id'		=> $df_id,
	);

	$s_form_action = $this->helper->route('oxpus_dlext_controller', $s_form_ary);

	$this->template->set_filenames(array(
		'body' => 'dl_version_edit.html')
	);

	$this->template->assign_vars(array(
		'CAT_RULE'			=> $cat_rule,
		'DESCRIPTION'		=> $description,
		'ENCTYPE'			=> 'enctype="multipart/form-data"',
		'VER_VERSION'		=> $ver_version,
		'MINI_IMG'			=> $mini_icon,
		'STATUS'			=> $status,
		'VER_ACTIVE'		=> ($ver_data['ver_active']) ? 'checked="checked"' : '',
		'VER_TEXT'			=> $ver_data['ver_text'],
		'VER_VERSION'		=> $ver_data['ver_version'],

		'S_CAT_RULE'		=> ($cat_rule) ? true : false,
		'S_DL_POPUPIMAGE'	=> $images_exists,
		'S_FORM_ACTION'		=> $s_form_action,
	));

	page_footer();
}

/*
* generate default page
*/
$this->template->set_filenames(array(
	'body' => 'dl_version.html')
);

/*
* Fetch all attachments for this release
*/
$sql = 'SELECT * FROM ' . DL_VER_FILES_TABLE . '
	WHERE ver_id = ' . (int) $ver_id . '
	ORDER BY file_title';
$result = $this->db->sql_query($sql);

$images_exists = false;

while ($row = $this->db->sql_fetchrow($result))
{
	switch ($row['file_type'])
	{
		case 1:
			$file_path = DL_EXT_VER_IMAGES_WFOLDER . $row['real_name'];
			$tpl_block = 'images';
			$images_exists = true;
		break;
		default:
			$load_link_ary = array(
				'view'			=> 'version',
				'action'		=> 'load',
				'ver_id'		=> $ver_id,
				'ver_file_id'	=> $row['ver_file_id'],
				'df_id'			=> $df_id,
			);
			$file_path = $this->helper->route('oxpus_dlext_controller', $load_link_ary);
			$tpl_block = 'files';
	}

	$this->template->assign_block_vars($tpl_block, array(
		'NAME'		=> $row['file_title'],
		'LINK'		=> $file_path,
		'S_AUTH'	=> $file_load,
	));
}

$this->db->sql_freeresult($result);

/*
* Send the release values themselves to the template to be able to read something *g*
*/
$this->template->assign_vars(array(
	'DESCRIPTION'		=> $description,
	'MINI_IMG'			=> $mini_icon,
	'VER_VERSION'		=> $ver_version,
	'VER_DESC'			=> ($ver_desc) ? $ver_desc : $this->language->lang('DL_NOT_AVAILIBLE'),
	'STATUS'			=> $status,
	'S_ACTIVE'			=> $ver_data['ver_active'],
	'FILE_SIZE'			=> $file_size_out,
	'FILE_NAME'			=> ($dl_file['extern']) ? $this->language->lang('DL_EXTERN') : $file_name,
	'CAT_RULE'			=> $cat_rule,
	'S_CAT_RULE'		=> ($cat_rule) ? true : false,
	'S_DL_POPUPIMAGE'	=> $images_exists,

	'U_VER_EDIT'		=> ($ver_can_edit) ? $this->helper->route('oxpus_dlext_controller', array('view' => 'version', 'action' => 'edit', 'ver_id' => $ver_id, 'df_id' => $df_id)) : '',
));

/*
* The end... Yes? Yes!
*/
