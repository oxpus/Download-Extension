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

if (isset($df_id) && $df_id)
{
	$dl_file = array();
	$dl_file = \oxpus\dlext\phpbb\classes\ dl_files::all_files(0, '', 'ASC', '', $df_id, 1, '*');
	if (isset($dl_file['id']) && !$dl_file['id'])
	{
		trigger_error($language->lang('MUST_SELECT_DOWNLOAD'));
	}
}

$index = array();
$index = \oxpus\dlext\phpbb\classes\ dl_main::full_index('', $cat_id);

if ($cancel)
{
	$action = '';
}

include($ext_path . 'phpbb/helpers/dl_fields.' . $phpEx);
$cp = new \oxpus\dlext\phpbb\helpers\ custom_profile();

if($action == 'edit' || $action == 'add')
{
	$s_hidden_fields = array('action' => 'save');

	$cat_id = ($cat_id) ? $cat_id : ((isset($dl_file['cat'])) ? $dl_file['cat'] : 0);

	$s_file_free_select = '<select name="file_free">';
	$s_file_free_select .= '<option value="0">' . $language->lang('NO') . '</option>';
	$s_file_free_select .= '<option value="1">' . $language->lang('YES') . '</option>';
	$s_file_free_select .= '<option value="2">' . $language->lang('DL_IS_FREE_REG') . '</option>';
	$s_file_free_select .= '</select>';

	$s_select_datasize = '<option value="byte">' . $language->lang('DL_BYTES') . '</option>';
	$s_select_datasize .= '<option value="kb">' . $language->lang('DL_KB') . '</option>';
	$s_select_datasize .= '<option value="mb">' . $language->lang('DL_MB') . '</option>';
	$s_select_datasize .= '<option value="gb">' . $language->lang('DL_GB') . '</option>';
	$s_select_datasize .= '</select>';

	$s_hacklist_select = '<select name="hacklist">';
	$s_hacklist_select .= '<option value="0">' . $language->lang('NO') . '</option>';
	$s_hacklist_select .= '<option value="1">' . $language->lang('YES') . '</option>';
	$s_hacklist_select .= '<option value="2">' . $language->lang('DL_MOD_LIST') . '</option>';
	$s_hacklist_select .= '</select>';

	if($action == 'edit')
	{
		$description			= (isset($dl_file['description'])) ? $dl_file['description'] : '';
		$file_traffic			= (isset($dl_file['file_traffic'])) ? $dl_file['file_traffic'] : 0;
		$dl_extern				= (isset($dl_file['extern'])) ? $dl_file['extern'] : 0;
		$dl_extern_size			= (isset($dl_file['file_size'])) ? $dl_file['file_size'] : 0;
		$file_name				= (isset($dl_file['file_name']) && $dl_extern) ? $dl_file['file_name'] : '';
		$cat_id					= (isset($dl_file['cat'])) ? $dl_file['cat'] : 0;
		$hacklist				= (isset($dl_file['hacklist'])) ? $dl_file['hacklist'] : 0;
		$hack_author			= (isset($dl_file['hack_author'])) ? $dl_file['hack_author'] : '';
		$hack_author_email		= (isset($dl_file['hack_author_email'])) ? $dl_file['hack_author_email'] : '';
		$hack_author_website	= (isset($dl_file['hack_author_website'])) ? $dl_file['hack_author_website'] : '';
		$hack_version			= (isset($dl_file['hack_version'])) ? $dl_file['hack_version'] : '';
		$hack_dl_url			= (isset($dl_file['hack_dl_url'])) ? $dl_file['hack_dl_url'] : '';
		$long_desc				= (isset($dl_file['long_desc'])) ? $dl_file['long_desc'] : '';
		$mod_test				= (isset($dl_file['test'])) ? $dl_file['test'] : '';
		$require				= (isset($dl_file['req'])) ? $dl_file['req'] : '';
		$todo					= (isset($dl_file['todo'])) ? $dl_file['todo'] : '';
		$warning				= (isset($dl_file['warning'])) ? $dl_file['warning'] : '';
		$mod_desc				= (isset($dl_file['mod_desc'])) ? $dl_file['mod_desc'] : '';
		$mod_list				= (isset($dl_file['mod_list']) && $dl_file['mod_list'] != 0) ? 'checked="checked"' : '';
		$dl_free				= (isset($dl_file['free'])) ? $dl_file['free'] : 0;
		$approve				= (isset($dl_file['approve'])) ? $dl_file['approve'] : 0;

		$mod_desc_uid		= (isset($dl_file['mod_desc_uid'])) ? $dl_file['mod_desc_uid'] : '';
		$mod_desc_flags		= (isset($dl_file['mod_desc_flags'])) ? $dl_file['mod_desc_flags'] : 0;
		$long_desc_uid		= (isset($dl_file['long_desc_uid'])) ? $dl_file['long_desc_uid'] : '';
		$long_desc_flags	= (isset($dl_file['long_desc_flags'])) ? $dl_file['long_desc_flags'] : 0;
		$desc_uid			= (isset($dl_file['desc_uid'])) ? $dl_file['desc_uid'] : '';
		$desc_flags			= (isset($dl_file['desc_flags'])) ? $dl_file['desc_flags'] : 0;
		$warn_uid			= (isset($dl_file['warn_uid'])) ? $dl_file['warn_uid'] : '';
		$warn_flags			= (isset($dl_file['warn_flags'])) ? $dl_file['warn_flags'] : 0;
		$todo_uid			= (isset($dl_file['todo_uid'])) ? $dl_file['todo_uid'] : '';
		$todo_flags			= (isset($dl_file['todo_flags'])) ? $dl_file['todo_flags'] : 0;

		$text_ary		= generate_text_for_edit($mod_desc, $mod_desc_uid, $mod_desc_flags);
		$mod_desc		= $text_ary['text'];

		$text_ary		= generate_text_for_edit($long_desc, $long_desc_uid, $long_desc_flags);
		$long_desc		= $text_ary['text'];

		$text_ary		= generate_text_for_edit($description, $desc_uid, $desc_flags);
		$description	= $text_ary['text'];

		$text_ary		= generate_text_for_edit($warning, $warn_uid, $warn_flags);
		$warning		= $text_ary['text'];

		$text_ary		= generate_text_for_edit($todo, $todo_uid, $todo_flags);
		$todo			= $text_ary['text'];

		$tmp_ary				= \oxpus\dlext\phpbb\classes\ dl_format::dl_size($file_traffic, 2, 'select');
		$file_traffic_out		= $tmp_ary['size_out'];
		$data_range_select		= $tmp_ary['range'];

		$tmp_ary				= \oxpus\dlext\phpbb\classes\ dl_format::dl_size($dl_extern_size, 2, 'select');
		$file_extern_size_out	= $tmp_ary['size_out'];
		$file_extern_size_range	= $tmp_ary['range'];

		unset($tmp_ary);

		$s_file_traffic_range	= str_replace('value="' . $data_range_select . '">', 'value="' . $data_range_select . '" selected="selected">', $s_select_datasize);
		$s_file_extsize_select	= str_replace('value="' . $file_extern_size_range . '">', 'value="' . $file_extern_size_range . '" selected="selected">', $s_select_datasize);
		$s_hacklist_select		= str_replace('value="' . $hacklist . '">', 'value="' . $hacklist . '" selected="selected">', $s_hacklist_select);
		$s_file_free_select		= str_replace('value="' . $dl_free . '">', 'value="' . $dl_free . '" selected="selected">', $s_file_free_select);

		if ($dl_extern)
		{
			$checkextern = 'checked="checked"';
		}
		else
		{
			$checkextern = '';
		}

		if ($approve)
		{
			$approve = 'checked="checked"';
		}
		else
		{
			$approve = '';
		}

		if (isset($config['dl_disable_popup']) && !$config['dl_disable_popup'])
		{
			$template->assign_var('S_POPUP_NOTIFY', true);
		}

		if (isset($config['dl_disable_email']) && !$config['dl_disable_email'])
		{
			$template->assign_var('S_EMAIL_BLOCK', true);
		}

		$template->assign_var('S_CHANGE_TIME', true);

		$thumbnail = (isset($dl_file['thumbnail'])) ? $dl_file['thumbnail'] : '';

		if ($thumbnail)
		{
			$template->assign_var('S_DEL_THUMB', true);
		}

		if ($thumbnail != $df_id . '_')
		{
			$template->assign_var('S_SHOW_THUMB', true);
		}

		$template->assign_var('S_CLICK_RESET', true);

		$s_hidden_fields = array_merge($s_hidden_fields, array('df_id' => $df_id));
	}
	else
	{
		$approve				= 'checked="checked"';
		$description			= '';
		$file_traffic			= 0;
		$file_name				= '';
		$hacklist				= 0;
		$hack_author			= '';
		$hack_author_email		= '';
		$hack_author_website	= '';
		$hack_version			= '';
		$hack_dl_url			= '';
		$long_desc				= '';
		$mod_test				= '';
		$require				= '';
		$todo					= '';
		$warning				= '';
		$mod_desc				= '';
		$mod_list				= '';
		$file_traffic_out		= 0;
		$checkextern			= '';
		$thumbnail				= '';
		$file_extern_size_out	= 0;

		$s_file_traffic_range	= str_replace('value="kb">', 'value="kb" selected="selected">', $s_select_datasize);
		$s_file_extsize_select	= str_replace('value="byte">', 'value="byte" selected="selected">', $s_select_datasize);
	}

	$s_file_traffic_range = '<select name="dl_t_quote">' . $s_file_traffic_range;
	$s_file_extsize_select = '<select name="dl_e_quote">' . $s_file_extsize_select;

	if (isset($index[$cat_id]['allow_thumbs']) && $index[$cat_id]['allow_thumbs'] && $config['dl_thumb_fsize'])
	{
		$template->assign_var('S_ALLOW_THUMB', true);

		$thumbnail_explain	= $language->lang('DL_THUMB_DIM_SIZE', $config['dl_thumb_xsize'], $config['dl_thumb_ysize'], \oxpus\dlext\phpbb\classes\ dl_format::dl_size($config['dl_thumb_fsize']));

		$enctype			= 'enctype="multipart/form-data"';
	}
	else
	{
		$enctype			= '';

		$thumbnail_explain	= '';
	}

	$select_code = '<select name="cat_id">';
	$select_code .= \oxpus\dlext\phpbb\classes\ dl_extra::dl_dropdown(0, 0, $cat_id, 'auth_up');
	$select_code .= '</select>';

	$template->set_filenames(array(
		'files' => 'dl_files_edit_body.html')
	);

	if ($df_id)
	{
		$template->assign_var('S_EDIT_VERSIONS', true);
	}

	if (isset($config['dl_use_hacklist']))
	{
		$template->assign_var('S_USE_HACKLIST', true);
	}

	if (isset($index[$cat_id]['allow_mod_desc']))
	{
		$template->assign_var('S_USE_MOD_DESC', true);
	}

	$ext_blacklist = \oxpus\dlext\phpbb\classes\ dl_auth::get_ext_blacklist();
	if (sizeof($ext_blacklist))
	{
		$blacklist_explain = '<br />' . $language->lang('DL_FORBIDDEN_EXT_EXPLAIN', implode(', ', $ext_blacklist));
	}
	else
	{
		$blacklist_explain = '';
	}

	$sql = 'SELECT ver_id, ver_change_time, ver_version FROM ' . DL_VERSIONS_TABLE . '
		WHERE dl_id = ' . (int) $df_id . '
		ORDER BY ver_version DESC, ver_change_time DESC';
	$result = $db->sql_query($sql);

	$total_versions = $db->sql_affectedrows($result);
	$multiple_size = ($total_versions > 10) ? 10 : $total_versions;

	$s_select_version = '<select name="file_version">';
	$s_select_ver_del = '<select name="file_ver_del[]" multiple="multiple" size="' . $multiple_size . '">';
	$s_select_version .= '<option value="0" selected="selected">' . $language->lang('DL_VERSION_CURRENT') . '</option>';

	while ($row = $db->sql_fetchrow($result))
	{
		$s_select_version .= '<option value="' . $row['ver_id'] . '">' . $row['ver_version'] . ' - ' . $user->format_date($row['ver_change_time']) . '</option>';
		$s_select_ver_del .= '<option value="' . $row['ver_id'] . '">' . $row['ver_version'] . ' - ' . $user->format_date($row['ver_change_time']) . '</option>';
	}

	$db->sql_freeresult($result);

	$s_select_version .= '</select>';
	$s_select_ver_del .= '</select>';

	if (!$total_versions)
	{
		$s_select_ver_del = '';
	}

	add_form_key('dl_adm_edit');

	$template->assign_vars(array(
		'L_DL_APPROVE_EXPLAIN'				=> 'DL_APPROVE',
		'L_DL_CAT_NAME_EXPLAIN'				=> 'DL_CHOOSE_CATEGORY',
		'L_DL_DESCRIPTION_EXPLAIN'			=> 'DL_FILE_DESCRIPTION',
		'L_DL_EXTERN_EXPLAIN'				=> 'DL_EXTERN',
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
		'L_DL_NAME_EXPLAIN'					=> 'DL_NAME',
		'L_DL_TRAFFIC_EXPLAIN'				=> 'DL_TRAFFIC',
		'L_LINK_URL_EXPLAIN'				=> 'DL_FILES_URL',
		'L_DL_THUMBNAIL_EXPLAIN'			=> 'DL_THUMB',
		'DL_THUMBNAIL_SECOND'				=> $thumbnail_explain,
		'L_CHANGE_TIME_EXPLAIN'				=> 'DL_NO_CHANGE_EDIT_TIME',
		'L_DISABLE_POPUP_EXPLAIN'			=> 'DL_DISABLE_POPUP_FILES',
		'L_DL_SEND_NOTIFY_EXPLAIN'			=> 'DL_DISABLE_EMAIL_FILES',
		'L_CLICK_RESET_EXPLAIN'				=> 'DL_KLICKS_RESET',

		'ACTION_MODE'						=> ($action == 'add') ? $language->lang('ADD') : $language->lang('EDIT'),

		'BLACKLIST_EXPLAIN'		=> $blacklist_explain,
		'CHECKEXTERN'			=> $checkextern,
		'DESCRIPTION'			=> $description,
		'FILE_NAME'				=> $file_name,
		'HACK_AUTHOR'			=> $hack_author,
		'HACK_AUTHOR_EMAIL'		=> $hack_author_email,
		'HACK_AUTHOR_WEBSITE'	=> $hack_author_website,
		'HACK_DL_URL'			=> $hack_dl_url,
		'HACK_VERSION'			=> $hack_version,
		'LONG_DESC'				=> $long_desc,
		'MOD_DESC'				=> $mod_desc,
		'MOD_LIST'				=> $mod_list,
		'MOD_REQUIRE'			=> $require,
		'MOD_TEST'				=> $mod_test,
		'MOD_TODO'				=> $todo,
		'MOD_WARNING'			=> $warning,
		'TRAFFIC'				=> $file_traffic_out,
		'URL'					=> $file_name,
		'APPROVE'				=> $approve,
		'SELECT_CAT'			=> $select_code,
		'ENCTYPE'				=> $enctype,
		'THUMBNAIL'				=> DL_EXT_THUMBS_FOLDER . $thumbnail,
		'FILE_EXT_SIZE'			=> $file_extern_size_out,

		'S_TODO_LINK_ONOFF'		=> ($config['dl_todo_onoff']) ? true : false,
		'S_SELECT_VERSION'		=> $s_select_version,
		'S_SELECT_VER_DEL'		=> $s_select_ver_del,
		'S_HACKLIST_SELECT'		=> $s_hacklist_select,
		'S_FILE_FREE_SELECT'	=> $s_file_free_select,
		'S_FILE_TRAFFIC_RANGE'	=> $s_file_traffic_range,
		'S_FILE_EXT_SIZE_RANGE'	=> $s_file_extsize_select,
		'S_DOWNLOADS_ACTION'	=> $basic_link,
		'S_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields),

		'U_BACK'				=> $this->u_action . '&amp;cat_id=' . $cat_id,
	));

	// Init and display the custom fields with the existing data
	$cp->get_profile_fields($df_id);
	$cp->generate_profile_fields($user->get_iso_lang_id());

	$template->assign_var('S_DL_FILES_EDIT', true);
}
else if($action == 'save')
{
	$file_option	= $request->variable('file_ver_opt', 0);
	$file_version	= $request->variable('file_version', 0);
	$file_ver_del	= $request->variable('file_ver_del', array(0));

	if ($file_option == 3)
	{
		if (!$confirm)
		{
			add_form_key('dl_adm_delete');

			/*
			* output confirmation page
			*/
			page_header($language->lang('DL_DELETE_FILE_CONFIRM'));

			$template->assign_var('S_DELETE_VERSIONS', true);

			$template->set_filenames(array(
				'body' => 'dl_confirm_body.html')
			);

			$template->assign_var('S_DELETE_FILES_CONFIRM', true);

			$s_hidden_fields = array(
				'view'			=> 'modcp',
				'action'		=> 'save',
				'cat_id'		=> $cat_id,
				'df_id'			=> $df_id,
				'submit'		=> 1,
				'file_ver_opt'	=> 3,
			);

			for ($i = 0; $i < sizeof($file_ver_del); $i++)
			{
				$s_hidden_fields = array_merge($s_hidden_fields, array('file_ver_del[' . $i . ']' => $file_ver_del[$i]));
			}

			$template->assign_vars(array(
				'MESSAGE_TITLE' => $language->lang('INFORMATION'),
				'MESSAGE_TEXT' => $language->lang('DL_CONFIRM_DELETE_VERSIONS'),

				'S_CONFIRM_ACTION' => $basic_link,
				'S_HIDDEN_FIELDS' => build_hidden_fields($s_hidden_fields))
			);

			page_footer();
		}
		else
		{
			if (!check_form_key('dl_adm_delete'))
			{
				trigger_error('FORM_INVALID');
			}

			$dl_ids = array();

			for ($i = 0; $i < sizeof($file_ver_del); $i++)
			{
				$dl_ids[] = intval($file_ver_del[$i]);
			}

			if ($del_file)
			{
				$sql = 'SELECT path FROM ' . DL_CAT_TABLE . '
					WHERE id = ' . (int) $cat_id;
				$result = $db->sql_query($sql);
				$path = $db->sql_fetchfield('path');
				$db->sql_freeresult($result);

				if (sizeof($dl_ids))
				{
					$sql = 'SELECT ver_real_file FROM ' . DL_VERSIONS_TABLE . '
						WHERE ' . $db->sql_in_set('ver_id', $dl_ids);
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						@unlink(DL_EXT_FILES_FOLDER . $path . $row['ver_real_file']);
					}

					$db->sql_freeresult($result);

					$sql = 'SELECT file_type, real_name FROM ' . DL_VER_FILES_TABLE . '
						WHERE ' . $db->sql_in_set('ver_id', $dl_ids);
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
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

					$db->sql_freeresult($result);
				}
			}

			if (sizeof($dl_ids))
			{
				$sql = 'DELETE FROM ' . DL_VERSIONS_TABLE . '
					WHERE ' . $db->sql_in_set('ver_id', $dl_ids);
				$db->sql_query($sql);

				$sql = 'DELETE FROM ' . DL_VER_FILES_TABLE . '
					WHERE ' . $db->sql_in_set('ver_id', $dl_ids);
				$db->sql_query($sql);
			}

			redirect($basic_link . "&amp;cat_id=$cat_id");
		}
	}
	else
	{
		if (!check_form_key('dl_adm_edit'))
		{
			trigger_error('FORM_INVALID');
		}

		$new_version			= false;

		$description			= $request->variable('description', '', true);
		$file_traffic			= $request->variable('file_traffic', 0);
		$approve				= $request->variable('approve', 0);

		$hacklist				= $request->variable('hacklist', 0);
		$hack_author			= $request->variable('hack_author', '', true);
		$hack_author_email		= $request->variable('hack_author_email', '', true);
		$hack_author_website	= $request->variable('hack_author_website', '', true);
		$hack_version			= $request->variable('hack_version', '');
		$hack_dl_url			= $request->variable('hack_dl_url', '', true);

		$test					= $request->variable('test', '', true);
		$require				= $request->variable('require', '', true);
		$todo					= $request->variable('todo', '', true);
		$warning				= $request->variable('warning', '', true);
		$mod_desc				= $request->variable('mod_desc', '', true);
		$mod_list				= $request->variable('mod_list', 0);
		$long_desc				= $request->variable('long_desc', '', true);
		$file_name				= $request->variable('file_name', '', true);
		$file_free				= $request->variable('file_free', 0);
		$file_extern			= $request->variable('file_extern', 0);
		$file_extern_size		= $request->variable('file_extern_size', '');

		$allow_bbcode			= ($config['allow_bbcode']) ? true : false;
		$allow_urls				= true;
		$allow_smilies			= ($config['allow_smilies']) ? true : false;
		$desc_uid				= $desc_bitfield = $mod_desc_uid = $mod_desc_bitfield = $long_desc_uid = $long_desc_bitfield = $warn_uid = $warn_bitfield = $todo_uid = $todo_bitfield = '';
		$desc_flags				= $mod_desc_flags = $long_desc_flags = $warn_flags = $todo_flags = 0;

		if ($description)
		{
			generate_text_for_storage($description, $desc_uid, $desc_bitfield, $desc_flags, $allow_bbcode, true, $allow_smilies);
		}
		else
		{
			trigger_error($language->lang('NO_SUBJECT'), E_USER_WARNING);
		}

		if ($long_desc)
		{
			generate_text_for_storage($long_desc, $long_desc_uid, $long_desc_bitfield, $long_desc_flags, $allow_bbcode, true, $allow_smilies);
		}

		if ($mod_desc)
		{
			generate_text_for_storage($mod_desc, $mod_desc_uid, $mod_desc_bitfield, $mod_desc_flags, $allow_bbcode, true, $allow_smilies);
		}

		if ($warning)
		{
			generate_text_for_storage($warning, $warn_uid, $warn_bitfield, $warn_flags, $allow_bbcode, true, $allow_smilies);
		}

		if ($todo)
		{
			generate_text_for_storage($todo, $todo_flags, $todo_bitfield, $todo_uid, $allow_bbcode, true, $allow_smilies);
		}

		$send_notify			= $request->variable('send_notify', 0);
		$change_time			= $request->variable('change_time', 0);
		$disable_popup_notify	= $request->variable('disable_popup_notify', 0);
		$del_thumb				= $request->variable('del_thumb', 0);

		$extension				= str_replace('.', '', trim(strrchr(strtolower($file_name), '.')));
		$ext_blacklist			= \oxpus\dlext\phpbb\classes\ dl_auth::get_ext_blacklist();

		$new_real_file			= '';

		if ($config['dl_enable_blacklist'])
		{
			if (in_array($extension, $ext_blacklist))
			{
				trigger_error($language->lang('DL_FORBIDDEN_EXTENSION'), E_USER_WARNING);
			}
		}

		if ($file_extern)
		{
			$file_traffic = 0;
		}
		else
		{
			$file_traffic = \oxpus\dlext\phpbb\classes\ dl_format::resize_value('dl_file_traffic', $file_traffic);
		}

		$file_path = $index[$cat_id]['cat_path'];
		$cat_name = $index[$cat_id]['cat_name'];

		if (!$file_extern)
		{
			$file_name = (strpos($file_name, '/')) ? substr($file_name, strrpos($file_name, '/') + 1) : $file_name;
		}

		// validate custom profile fields
		$error = $cp_data = $cp_error = array();
		$cp->submit_cp_field($user->get_iso_lang_id(), $cp_data, $error);

		// Stop here, if custom fields are invalid!
		if (sizeof($error))
		{
			trigger_error(implode('<br />', $error), E_USER_WARNING);
		}

		if ($df_id && !$file_extern)
		{
			$dl_file = array();
			$dl_file = \oxpus\dlext\phpbb\classes\ dl_files::all_files(0, 0, 'ASC', 0, $df_id, true, '*');

			$real_file_old	= (isset($dl_file['real_file'])) ? $dl_file['real_file'] : '';
			$file_cat_old	= (isset($dl_file['cat'])) ? $dl_file['cat'] : 0;

			$index_new = array();
			$index_new = \oxpus\dlext\phpbb\classes\ dl_main::full_index('', $file_cat_old);

			$file_path_old = (isset($index_new[$file_cat_old]['cat_path'])) ? $index_new[$file_cat_old]['cat_path'] : '';
			$file_path_new = (isset($index[$cat_id]['cat_path'])) ? $index[$cat_id]['cat_path'] : '';

			if ($file_name)
			{
				$new_real_file = md5($file_name);

				if ($file_option == 2 && !$file_version)
				{
					@unlink(DL_EXT_FILES_FOLDER . $file_path_old . $real_file_old);
				}

				$i = 0;
				while(@file_exists(DL_EXT_FILES_FOLDER . $file_path_new . $new_real_file))
				{
					$new_real_file = md5($i . $file_name);
					$i++;
				}

				@copy(DL_EXT_FILES_FOLDER . $file_path_old . $file_name, DL_EXT_FILES_FOLDER . $file_path_new . $new_real_file);
				@chmod(DL_EXT_FILES_FOLDER . $file_path_new . $new_real_file, 0777);
				@unlink(DL_EXT_FILES_FOLDER . $file_path_old . $file_name);

				$real_file_old = $new_real_file;
			}
			else
			{
				if ($dl_file['file_name'] == $dl_file['real_file'])
				{
					$new_real_file = md5($dl_file['real_file']);

					$i = 0;
					while(@file_exists(DL_EXT_FILES_FOLDER . $file_path_old . $new_real_file))
					{
						$new_real_file = md5($i . $dl_file['real_file']);
						$i++;
					}

					@copy(DL_EXT_FILES_FOLDER . $file_path_old . $real_file_old, DL_EXT_FILES_FOLDER . $file_path_old . $new_real_file);
					@chmod(DL_EXT_FILES_FOLDER . $file_path_old . $new_real_file, 0777);
					@unlink(DL_EXT_FILES_FOLDER . $file_path_old . $real_file_old);
				}
				else
				{
					$new_real_file = $dl_file['real_file'];
				}
			}

			if ($file_cat_old != $cat_id)
			{
				if ($file_path_old != $file_path_new)
				{
					@copy(DL_EXT_FILES_FOLDER . $file_path_old . $real_file_old, DL_EXT_FILES_FOLDER . $file_path_new . $new_real_file);
					@chmod(DL_EXT_FILES_FOLDER . $file_path_new . $new_real_file, 0777);
					@unlink(DL_EXT_FILES_FOLDER . $file_path_old . $real_file_old);

					$sql = 'SELECT ver_real_file FROM ' . DL_VERSIONS_TABLE . '
						WHERE dl_id = ' . (int) $df_id;
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$real_ver_file = $row['ver_real_file'];

						@copy(DL_EXT_FILES_FOLDER . $file_path_old . $real_ver_file, DL_EXT_FILES_FOLDER . $file_path_new . $real_ver_file);
						@chmod(DL_EXT_FILES_FOLDER . $file_path_new . $real_ver_file, 0777);
						@unlink(DL_EXT_FILES_FOLDER . $file_path_old . $real_ver_file);
					}

					$db->sql_freeresult($result);
				}

				$sql = 'UPDATE ' . DL_STATS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
					'cat_id' => $cat_id)) . ' WHERE id = ' . (int) $df_id;
				$db->sql_query($sql);

				$sql = 'UPDATE ' . DL_COMMENTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
					'cat_id' => $cat_id)) . ' WHERE id = ' . (int) $df_id;
				$db->sql_query($sql);
			}
		}
		else if (!$file_extern && $file_name)
		{
			$new_real_file = md5($file_name);

			$i = 0;
			while(@file_exists(DL_EXT_FILES_FOLDER . $file_path . $new_real_file))
			{
				$new_real_file = md5($i . $file_name);
				$i++;
			}

			@copy(DL_EXT_FILES_FOLDER . $file_path . $file_name, DL_EXT_FILES_FOLDER . $file_path . $new_real_file);
			@chmod(DL_EXT_FILES_FOLDER . $file_path . $new_real_file, 0777);
			@unlink(DL_EXT_FILES_FOLDER . $file_path . $file_name);
		}

		if (!$file_extern)
		{
			$file_size = sprintf("%u", @filesize(DL_EXT_FILES_FOLDER . $file_path . $new_real_file));

			if (!$file_size)
			{
				trigger_error($language->lang('DL_FILE_NOT_FOUND', $new_real_file, DL_EXT_FILES_FOLDER . $file_path), E_USER_WARNING);
			}
		}
		else
		{
			$new_real_file = '';
			$file_size = \oxpus\dlext\phpbb\classes\ dl_format::resize_value('dl_extern_size', $file_extern_size);
		}

		$current_time = time();
		$current_user = $user->data['user_id'];

		if ($config['dl_set_add'] == 1 && $config['dl_set_user'])
		{
			$current_user = $config['dl_set_user'];
		}

		if ($config['dl_set_add'] == 2 && $index[$cat_id]['dl_set_add'] && $index[$cat_id]['dl_set_user'])
		{
			$current_user = $index[$cat_id]['dl_set_user'];
		}

		if ($new_real_file)
		{
			$hash_method = $config['dl_file_hash_algo'];
			$func_hash = $hash_method . '_file';
			$file_hash = $func_hash(DL_EXT_FILES_FOLDER . $file_path . $new_real_file);
		}
		else
		{
			$file_hash = '';
		}

		/*
		* Enter new version if choosen
		*/
		if ($file_name && $df_id)
		{
			if (!$file_option || $file_option == 1)
			{
				$sql = 'INSERT INTO ' . DL_VERSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
					'dl_id'				=> $df_id,
					'ver_file_name'		=> ($file_option) ? $file_name : $dl_file['file_name'],
					'ver_real_file'		=> ($file_option) ? $new_real_file : $dl_file['real_file'],
					'ver_file_hash'		=> ($file_option) ? $file_hash : $dl_file['file_hash'],
					'ver_file_size'		=> ($file_option) ? $file_size : $dl_file['file_size'],
					'ver_version'		=> ($file_option) ? $hack_version : $dl_file['hack_version'],
					'ver_add_time'		=> ($file_option) ? time() : $dl_file['add_time'],
					'ver_change_time'	=> ($file_option) ? time() : $dl_file['change_time'],
					'ver_add_user'		=> ($file_option) ? $user->data['user_id'] : $dl_file['add_user'],
					'ver_change_user'	=> ($file_option) ? $user->data['user_id'] : $dl_file['change_user'],
					'ver_active'		=> 0,
					'ver_text'			=> '',
				));

				$db->sql_query($sql);
				$new_version = $db->sql_nextid();
			}
			else if ($file_option == 2 && $file_version)
			{
				$sql = 'SELECT ver_real_file FROM ' . DL_VERSIONS_TABLE . '
					WHERE dl_id = ' . (int) $df_id . '
						AND ver_id = ' . (int) $file_version;
				$result = $db->sql_query($sql);
				$real_old_file = $db->sql_fetchfield('ver_real_file');
				$db->sql_freeresult($result);

				@unlink(DL_EXT_FILES_FOLDER . $dl_path . $real_old_file);

				$sql = 'UPDATE ' . DL_VERSIONS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
					'ver_file_name'		=> $file_name,
					'ver_real_file'		=> $new_real_file,
					'ver_file_hash'		=> $file_hash,
					'ver_file_size'		=> $file_size,
					'ver_change_time'	=> time(),
					'ver_change_user'	=> $user->data['user_id'],
				)) . ' WHERE dl_id = ' . (int) $df_id . ' AND ver_id = ' . (int) $file_version;

				$db->sql_query($sql);
			}
		}

		$sql_array = array(
			'description'			=> $description,
			'file_traffic'			=> $file_traffic,
			'long_desc'				=> $long_desc,
			'free'					=> $file_free,
			'extern'				=> $file_extern,
			'cat'					=> $cat_id,
			'hacklist'				=> $hacklist,
			'hack_author'			=> $hack_author,
			'hack_author_email'		=> $hack_author_email,
			'hack_author_website'	=> $hack_author_website,
			'hack_dl_url'			=> $hack_dl_url,
			'test'					=> $test,
			'req'					=> $require,
			'todo'					=> $todo,
			'warning'				=> $warning,
			'mod_desc'				=> $mod_desc,
			'mod_list'				=> $mod_list,
			'desc_uid'				=> $desc_uid,
			'desc_bitfield'			=> $desc_bitfield,
			'desc_flags'			=> $desc_flags,
			'long_desc_uid'			=> $long_desc_uid,
			'long_desc_bitfield'	=> $long_desc_bitfield,
			'long_desc_flags'		=> $long_desc_flags,
			'mod_desc_uid'			=> $mod_desc_uid,
			'mod_desc_bitfield'		=> $mod_desc_bitfield,
			'mod_desc_flags'		=> $mod_desc_flags,
			'warn_uid'				=> $warn_uid,
			'warn_bitfield'			=> $warn_bitfield,
			'warn_flags'			=> $warn_flags,
			'approve'				=> $approve,
		);

		if ($df_id && (!$file_option || ($file_option == 2 && !$file_version)))
		{
			$sql_array = array_merge($sql_array, array(
				'file_name'		=> ($file_name) ? $file_name : $dl_file['file_name'],
				'real_file'		=> $new_real_file,
				'file_hash'		=> $file_hash,
				'file_size'		=> ($file_size) ? $file_size : $dl_file['file_size'],
				'hack_version'	=> ($hack_version) ? $hack_version : $dl_file['hack_version'],
			));
		}
		else
		{
			$sql_array = array_merge($sql_array, array(
				'file_name'		=> ($df_id) ? $dl_file['file_name'] : $file_name,
				'real_file'		=> ($df_id) ? $dl_file['real_file'] : $new_real_file,
				'file_hash'		=> ($df_id) ? $dl_file['file_hash'] : $file_hash,
				'file_size'		=> ($df_id) ? $dl_file['file_size'] : $file_size,
				'hack_version'	=> ($df_id) ? $dl_file['hack_version'] : $hack_version,
			));
		}

		if($df_id)
		{
			if (!$change_time)
			{
				$sql_array = array_merge($sql_array, array(
					'change_time' => $current_time,
					'change_user' => $current_user));
			}

			if ($click_reset)
			{
				$sql_array = array_merge($sql_array, array(
					'klicks' => 0));
			}

			$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_array) . ' WHERE id = ' . (int) $df_id;

			$message = $language->lang('DOWNLOAD_UPDATED');
		}
		else
		{
			$sql_array = array_merge($sql_array, array(
				'change_time'	=> $current_time,
				'change_user'	=> $current_user,
				'add_time'		=> $current_time,
				'add_user'		=> $current_user));

			$sql = 'INSERT INTO ' . DOWNLOADS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_array);

			$message = $language->lang('DOWNLOAD_ADDED');
		}

		$db->sql_query($sql);

		$dl_t_id = ($df_id) ? $df_id : $db->sql_nextid();

		if ($config['dl_enable_dl_topic'] && $approve)
		{
			if ($df_id)
			{
				\oxpus\dlext\phpbb\classes\ dl_topic::update_topic($dl_file['dl_topic'], $dl_t_id, $helper);
			}
			else
			{
				\oxpus\dlext\phpbb\classes\ dl_topic::gen_dl_topic($dl_t_id, $helper);
			}
		}

		$thumb_message = '';

		if ($index[$cat_id]['allow_thumbs'] && $config['dl_thumb_fsize'])
		{
			$thumb_error = false;

			if (!$del_thumb)
			{
				$user->add_lang('posting');

				$min_pic_width = 1;

				$factory = $phpbb_container->get('files.factory');
				$allowed_imagetypes = array('gif','png','jpg','bmp');
				$upload = $factory->get('upload')
					->set_allowed_extensions($allowed_imagetypes)
					->set_max_filesize($config['dl_thumb_fsize'])
					->set_allowed_dimensions(
						$min_pic_width,
						$min_pic_width,
						$config['dl_thumb_xsize'],
						$config['dl_thumb_ysize'])
					->set_disallowed_content((isset($config['mime_triggers']) ? explode('|', $config['mime_triggers']) : false));

				$form_name = 'thumb_name';

				$upload_file = $request->file($form_name);
				unset($upload_file['local_mode']);
				$thumb_file = $upload->handle_upload('files.types.form', $form_name);

				$thumb_size = $upload_file['size'];
				$thumb_temp = $upload_file['tmp_name'];
				$thumb_name = $upload_file['name'];

				if (sizeof($thumb_file->error) && $thumb_name)
				{
					$thumb_file->remove();
					trigger_error(implode('<br />', $thumb_file->error), E_USER_ERROR);
				}

				if ($thumb_name)
				{
					$pic_size = getimagesize($thumb_temp);
					$pic_width = $pic_size[0];
					$pic_height = $pic_size[1];

					if (!$pic_width || !$pic_height)
					{
						$thumb_file->remove();
						$thumb_error = true;
					}

					if ($pic_width > $config['dl_thumb_xsize'] || $pic_height > $config['dl_thumb_ysize'] || (sprintf("%u", @filesize($thumb_temp)) > $config['dl_thumb_fsize']))
					{
						$thumb_file->remove();
						$thumb_error = true;
					}

					if (!$thumb_error)
					{
						$df_id = ($df_id) ? $df_id : $db->sql_nextid();
						@unlink(DL_EXT_THUMBS_FOLDER . $dl_file['thumbnail']);
						@unlink(DL_EXT_THUMBS_FOLDER . $df_id . '_' . $thumb_name);

						$upload_file['name'] = $df_id . '_' . $thumb_name;
						$thumb_file->set_upload_ary($upload_file);
						$dest_folder = str_replace($phpbb_root_path, '', substr(DL_EXT_THUMBS_FOLDER, 0, -1));

						$error = $thumb_file->move_file($dest_folder, false, false, CHMOD_ALL);
						$thumb_message = '<br />' . $language->lang('DL_THUMB_UPLOAD');

						$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
							'thumbnail' => $df_id . '_' . $thumb_name)) . ' WHERE id = ' . (int) $df_id;
						$db->sql_query($sql);
					}
					else
					{
						$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
							'thumbnail' => '')) . ' WHERE id = ' . (int) $df_id;
						$db->sql_query($sql);
					}
				}
			}
			else if ($del_thumb)
			{
				$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
					'thumbnail' => '')) . ' WHERE id = ' . (int) $df_id;
				$db->sql_query($sql);

				@unlink(DL_EXT_THUMBS_FOLDER . $dl_file['thumbnail']);

				$thumb_message = '<br />'.$language->lang('DL_THUMB_DEL');
			}
		}

		if ($approve)
		{
			$processing_user = \oxpus\dlext\phpbb\classes\ dl_auth::dl_auth_users($cat_id, 'auth_dl');
			$email_template = ($df_id) ? 'downloads_change_notify' : 'downloads_new_notify';
		}
		else
		{
			$processing_user = \oxpus\dlext\phpbb\classes\ dl_auth::dl_auth_users($cat_id, 'auth_mod');
			$email_template = 'downloads_approve_notify';
		}

		$sql_fav_user = '';

		// Update Custom Fields
		$cp->update_profile_field_data($dl_t_id, $cp_data);

		if ($df_id)
		{
			$sql = 'SELECT fav_user_id FROM ' . DL_FAVORITES_TABLE . '
				WHERE fav_dl_id = ' . (int) $df_id;
			$result = $db->sql_query($sql);

			$fav_user = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$fav_user[] = $row['fav_user_id'];
			}
			$db->sql_freeresult($result);

			$sql_fav_user = (sizeof($fav_user)) ? ' AND ' . $db->sql_in_set('user_id', $fav_user) : '';
		}

		if (!$config['dl_disable_email'] && !$send_notify && $df_id && $sql_fav_user)
		{
			$sql = 'SELECT user_email, username, user_lang FROM ' . USERS_TABLE . '
				WHERE user_allow_fav_download_email = 1
					AND ' . $db->sql_in_set('user_id', explode(',', $processing_user)) . $sql_fav_user;

			$mail_data = array(
				'email_template'	=> $email_template,
				'query'				=> $sql,
				'description'		=> $description,
				'long_desc'			=> $long_desc,
				'cat_name'			=> $index[$cat_id]['cat_name_nav'],
				'cat_id'			=> $cat_id,
			);

			\oxpus\dlext\phpbb\classes\ dl_email::send_dl_notify($mail_data, $helper, $ext_path);
		}
		else if (!$config['dl_disable_email'] && !$send_notify && !$df_id)
		{
			$sql = 'SELECT user_email, username, user_lang FROM ' . USERS_TABLE . '
				WHERE user_allow_new_download_email = 1
					AND ' . $db->sql_in_set('user_id', explode(',', $processing_user));

			$mail_data = array(
				'email_template'	=> $email_template,
				'query'				=> $sql,
				'description'		=> $description,
				'long_desc'			=> $long_desc,
				'cat_name'			=> $index[$cat_id]['cat_name_nav'],
				'cat_id'			=> $cat_id,
			);

			\oxpus\dlext\phpbb\classes\ dl_email::send_dl_notify($mail_data, $helper, $ext_path);
		}

		if (!$config['dl_disable_popup'] && !$disable_popup_notify)
		{
			$sql = '';

			if ($df_id && $sql_fav_user)
			{
				$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
					'user_new_download' => 1)) . " WHERE user_allow_fav_download_popup = 1 $sql_fav_user AND " . $db->sql_in_set('user_id', explode(',', $processing_user));
			}
			else if (!$df_id)
			{
				$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
					'user_new_download' => 1)) . " WHERE user_allow_new_download_popup = 1 $sql_fav_user AND " . $db->sql_in_set('user_id', explode(',', $processing_user));
			}

			if ($sql)
			{
				$db->sql_query($sql);
			}
		}

		$notification = $phpbb_container->get('notification_manager');
		$notification_data = array('notification_id' => $dl_t_id);
		$notification->add_notifications('oxpus.dlext.notification.type.dlext', $notification_data);
	}

	if ($df_id)
	{
		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FILE_EDIT', false, array($description));
	}
	else
	{
		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FILE_ADD', false, array($description));
	}

	// Purge the files cache
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_cat_counts.' . $phpEx);
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_file_preset.' . $phpEx);

	$ver_message = '';

	if ($new_version)
	{
		$version_url	= $helper->route('oxpus_dlext_controller', array('view' => 'version', 'ver_id' => $new_version));
		$ver_message	= '<br /><br />' . $language->lang('CLICK_VIEW_NEW_VERSION', '<a href="' . $version_url . '">', '</a>');
	}

	$message .= $thumb_message . "<br /><br />" . $language->lang('CLICK_RETURN_DOWNLOADADMIN', '<a href="' . $basic_link . '&amp;cat_id=' . $cat_id . '">', '</a>') . $ver_message . adm_back_link($this->u_action);

	trigger_error($message);
}
else if($action == 'delete')
{
	if (!$confirm)
	{
		add_form_key('dl_adm_delete');

		$description = $dl_file['description'];

		$template->set_filenames(array(
			'confirm_body' => 'dl_confirm_body.html')
		);

		$template->assign_var('S_DELETE_FILES_CONFIRM', true);

		$s_hidden_fields = array(
			'cat_id'	=> $cat_id,
			'df_id'		=> $df_id,
			'action'	=> 'delete',
			'confirm'	=> 1
		);

		$template->assign_vars(array(
			'MESSAGE_TITLE' => $language->lang('INFORMATION'),
			'MESSAGE_TEXT' => $language->lang('DL_CONFIRM_DELETE_SINGLE_FILE', $description),

			'S_CONFIRM_ACTION' => $basic_link,
			'S_HIDDEN_FIELDS' => build_hidden_fields($s_hidden_fields))
		);

		$template->assign_var('S_DL_CONFIRM', true);

		$template->assign_display('confirm_body');

		$dl_confirm = true;
	}
	else
	{
		if (!check_form_key('dl_adm_delete'))
		{
			trigger_error('FORM_INVALID');
		}

		$sql = 'SELECT ver_id, dl_id, ver_real_file FROM ' . DL_VERSIONS_TABLE . '
			WHERE dl_id = ' . (int) $df_id;
		$result = $db->sql_query($sql);

		$ver_ids = array();
		$real_ver_file = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$real_ver_file[$row['dl_id']] = $row['ver_real_file'];
		}

		$db->sql_freeresult($result);

		if ($del_file)
		{
			$path = $index[$cat_id]['cat_path'];
			$file_name = $dl_file['real_file'];

			@unlink(DL_EXT_FILES_FOLDER . $path . $file_name);

			if (isset($real_ver_file[$df_id]))
			{
				for ($j = 0; $j < sizeof($real_ver_file[$df_id]); $j++)
				{
					@copy(DL_EXT_FILES_FOLDER . $old_path . $real_ver_file[$df_id][$j], DL_EXT_FILES_FOLDER . $new_path . $real_ver_file[$df_id][$j]);
					@chmod(DL_EXT_FILES_FOLDER . $new_path . $real_ver_file[$df_id][$j], 0777);
					@unlink(DL_EXT_FILES_FOLDER . $old_path . $real_ver_file[$df_id][$j]);
				}
			}

			$sql = 'SELECT file_type, real_name FROM ' . DL_VER_FILES_TABLE . '
					WHERE dl_id = ' . (int) $df_id;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
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

			$db->sql_freeresult($result);
		}

		@unlink(DL_EXT_THUMBS_FOLDER . $dl_file['thumbnail']);

		$topic_drop_mode = $request->variable('topic_drop_mode', 'drop');

		$sql = 'SELECT description, dl_topic FROM ' . DOWNLOADS_TABLE . '
			WHERE id = ' . (int) $df_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row['dl_topic'])
		{
			$del_t_id = array();
			$del_t_id[] = $row['dl_topic'];
			$dl_t_ids[$df_id] = $row['dl_topic'];
			\oxpus\dlext\phpbb\classes\ dl_topic::delete_topic($del_t_id, $topic_drop_mode, $dl_t_ids, $helper);
		}

		$dl_desc = $row['description'];

		$sql = 'DELETE FROM ' . DOWNLOADS_TABLE . '
			WHERE id = ' . (int) $df_id;
		$db->sql_query($sql);

		if (sizeof($ver_ids))
		{
			$sql = 'DELETE FROM ' . DL_VERSIONS_TABLE . '
				WHERE ' . $db->sql_in_set('ver_id', $ver_ids);
			$db->sql_query($sql);

			$sql = 'DELETE FROM ' . DL_VER_FILES_TABLE . '
				WHERE ' . $db->sql_in_set('ver_id', $ver_ids);
			$db->sql_query($sql);
		}

		$sql = 'DELETE FROM ' . DL_STATS_TABLE . '
			WHERE id = ' . (int) $df_id;
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . DL_COMMENTS_TABLE . '
			WHERE id = ' . (int) $df_id;
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . DL_NOTRAF_TABLE . '
			WHERE dl_id = ' . (int) $df_id;
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . DL_FIELDS_DATA_TABLE . '
			WHERE df_id = ' . (int) $df_id;
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . DL_RATING_TABLE . '
			WHERE dl_id = ' . (int) $df_id;
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . DL_FAVORITES_TABLE . '
			WHERE fav_dl_id = ' . (int) $df_id;
		$db->sql_query($sql);

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_DEL_FILE', false, array($dl_desc));

		// Purge the files cache
		@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_cat_counts.' . $phpEx);
		@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_file_preset.' . $phpEx);

		$message = $language->lang('DOWNLOAD_REMOVED') . "<br /><br />" . $language->lang('CLICK_RETURN_DOWNLOADADMIN', '<a href="' . $basic_link . '&amp;cat_id=' . $cat_id . '">', '</a>') . adm_back_link($this->u_action);

		trigger_error($message);
	}
}
else if($action == 'downloads_order')
{
	$sql = 'SELECT sort, description FROM ' . DOWNLOADS_TABLE . '
		WHERE id = ' . (int) $df_id;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$dl_desc = $row['description'];
	$dl_sort = $row['sort'] - $move;

	$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
		'sort' => $dl_sort)) . ' WHERE id = ' . (int) $df_id;
	$db->sql_query($sql);

	$sql = 'SELECT id FROM ' . DOWNLOADS_TABLE . '
		WHERE cat = ' . (int) $cat_id . '
		ORDER BY sort ASC';
	$result = $db->sql_query($sql);

	$i = 10;

	while($row = $db->sql_fetchrow($result))
	{
		$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
			'sort' => $i)) . ' WHERE id = ' . (int) $row['id'];
		$db->sql_query($sql);

		$i += 10;
	}

	$db->sql_freeresult($result);

	$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FILE_MOVE', false, array($dl_desc));

	$action = '';
}
else if($action == 'downloads_order_all')
{
	$sql = 'SELECT cat_name FROM ' . DL_CAT_TABLE . '
		WHERE id = ' . (int) $cat_id;
	$result = $db->sql_query($sql);
	$cat_name = $db->sql_fetchfield('cat_name');
	$db->sql_freeresult($result);

	$sql = 'SELECT id FROM ' . DOWNLOADS_TABLE . '
		WHERE cat = ' . (int) $cat_id . '
		ORDER BY description ASC';
	$result = $db->sql_query($sql);

	$i = 10;

	while($row = $db->sql_fetchrow($result))
	{
		$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
			'sort' => $i)) . ' WHERE id = ' . (int) $row['id'];
		$db->sql_query($sql);

		$i += 10;
	}

	$db->sql_freeresult($result);

	$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FILES_SORT', false, array($cat_name));

	$action = '';
}

if ($action == '')
{
	$sql = 'SELECT hacklist, hack_version, file_name, real_file, description, desc_uid, desc_bitfield, desc_flags, id, free, extern, test, cat, klicks, overall_klicks, file_traffic, file_size, approve FROM ' . DOWNLOADS_TABLE . '
		WHERE cat = ' . (int) $cat_id . '
		ORDER BY sort';
	$result = $db->sql_query($sql);
	$total_files = $db->sql_affectedrows($result);

	while ($row = $db->sql_fetchrow($result))
	{
		$file_path		= $index[$cat_id]['cat_path'];
		$hacklist		= ($row['hacklist'] == 1) ? $language->lang('YES') : $language->lang('NO');
		$version		= $row['hack_version'];
		$description	= $row['description'];
		$file_id		= $row['id'];
		$file_free		= $row['free'];
		$file_extern	= $row['extern'];
		$test			= ($row['test']) ? '['.$row['test'].']' : '';
		$cat_id			= $row['cat'];
		$file_name		= ($file_extern) ? $language->lang('DL_EXTERN') : $language->lang('DOWNLOAD') . ': ' . $row['file_name'] . '<br />{' . $row['real_file'] . '}';

		$desc_uid		= $row['desc_uid'];
		$desc_bitfield	= $row['desc_bitfield'];
		$desc_flags		= $row['desc_flags'];
		$description	= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

		switch ($file_free)
		{
			case 1:
				$file_free_out = $language->lang('DL_FREE');
				break;

			case 2:
				$file_free_out = $language->lang('DL_YES_REG');
				break;

			default:
				$file_free_out = $language->lang('DL_NO');
		}

		$file_free_extern_out	= ($file_extern) ? $language->lang('DL_EXTERN') : $file_free_out;

		$file_klicks			= $row['klicks'];
		$file_overall_klicks	= $row['overall_klicks'];
		$file_traffic			= $row['file_traffic'];

		if ($file_traffic)
		{
			$file_traffic = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($file_traffic);
		}
		else
		{
			$file_traffic = $language->lang('DL_NOT_AVAILIBLE');
		}

		if ($row['file_size'])
		{
			$file_size_kb	= \oxpus\dlext\phpbb\classes\ dl_format::dl_size($row['file_size']);
		}
		else
		{
			$file_size_kb	= $language->lang('DL_NOT_AVAILIBLE');
		}

		$unapprove = ($row['approve']) ? '' : $language->lang('DL_UNAPPROVED');

		$dl_edit	= "{$basic_link}&amp;action=edit&amp;df_id=$file_id";
		$dl_delete	= "{$basic_link}&amp;action=delete&amp;df_id=$file_id&amp;cat_id=$cat_id";

		$dl_move_up		= "{$basic_link}&amp;action=downloads_order&amp;move=15&amp;df_id=$file_id&amp;cat_id=$cat_id";
		$dl_move_down	= "{$basic_link}&amp;action=downloads_order&amp;move=-15&amp;df_id=$file_id&amp;cat_id=$cat_id";

		$template->assign_block_vars('downloads', array(
			'DESCRIPTION'			=> $description,
			'TEST'					=> $test,
			'FILE_ID'				=> $file_id,
			'FILE_SIZE'				=> $file_size_kb,
			'FILE_FREE_EXTERN'		=> $file_free_extern_out,
			'FILE_KLICKS'			=> $file_klicks,
			'FILE_TRAFFIC'			=> $file_traffic,
			'UNAPPROVED'			=> $unapprove,
			'FILE_OVERALL_KLICKS'	=> $file_overall_klicks,
			'HACKLIST'				=> $hacklist,
			'VERSION'				=> $version,
			'FILE_NAME'				=> $file_name,

			'U_FILE_EDIT'			=> $dl_edit,
			'U_FILE_DELETE'			=> $dl_delete,
			'U_DOWNLOAD_MOVE_UP'	=> $dl_move_up,
			'U_DOWNLOAD_MOVE_DOWN'	=> $dl_move_down)
		);
	}

	$categories = '<select name="cat_id" onchange="if(this.options[this.selectedIndex].value != -1){ forms[\'cat_id\'].submit() }">';
	$categories .= '<option value="-1">'.$language->lang('DL_CHOOSE_CATEGORY').'</option>';
	$categories .= '<option value="-1">----------</option>';
	$categories .= \oxpus\dlext\phpbb\classes\ dl_extra::dl_dropdown(0, 0, $cat_id, 'auth_up');
	$categories .= '</select>';

	$template->set_filenames(array(
		'files' => 'dl_files_body.html')
	);

	$template->assign_vars(array(
		'DL_FILE_SIZE'			=> $language->lang('DL_FILE_SIZE'),
		'SORT'					=> $language->lang('SORT_BY') . ' ' . $language->lang('DL_NAME') . ' / ' . $language->lang('DL_FILE_NAME'),

		'CAT'					=> $cat_id,
		'CATEGORIES'			=> $categories,
		'DL_COUNT'				=> $total_files . '&nbsp;' . $language->lang('DOWNLOADS'),

		'S_DOWNLOADS_ACTION'	=> $basic_link,
		'S_HIDDEN_FIELDS'		=> build_hidden_fields(array('cat_id' => $cat_id)),

		'U_DOWNLOAD_ORDER_ALL'	=> "{$basic_link}&amp;action=downloads_order_all&amp;cat_id=$cat_id")
	);

	if ($total_files)
	{
		$template->assign_var('S_LIST_DOWNLOADS', true);
	}

	$template->assign_var('S_DL_FILES', true);
}

if (!isset($dl_confirm))
{
	$template->assign_display('files');
}
