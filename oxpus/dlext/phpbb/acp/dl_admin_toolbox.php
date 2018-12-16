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

add_form_key('dl_adm_tools');

if ($submit && !check_form_key('dl_adm_tools'))
{
	trigger_error('FORM_INVALID', E_USER_WARNING);
}

$files = $request->variable('files', array(''), true);

if (sizeof($files) && $file_assign)
{
	$file_names = $file_path = array();

	for ($i = 0; $i < sizeof($files); $i++)
	{
		$temp = strpos($files[$i], '|');
		$files_path[] = substr($files[$i],0,$temp);
		$files_name[] = substr($files[$i],$temp+1);
	}

	if ($file_assign == 'del')
	{
		for ($i = 0; $i < sizeof($files); $i++)
		{
			$dl_dir = ($files_path[$i]) ? substr(DL_EXT_FILES_FOLDER, 0, strlen(DL_EXT_FILES_FOLDER)-1) : DL_EXT_FILES_FOLDER;

			@unlink($dl_dir . $files_path[$i] . '/' . $files_name[$i]);

			$sql = 'DELETE FROM ' . DOWNLOADS_TABLE . "
				WHERE real_file = '" . $db->sql_escape($files_name[$i]) . "'
					AND " . $db->sql_in_set('cat', $index, true);
			$db->sql_query($sql);

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FILE_DROP', false, array($files_name[$i]));
		}
	}
	else
	{
		$dl_dir = substr(DL_EXT_FILES_FOLDER, 0, strlen(DL_EXT_FILES_FOLDER)-1);

		for ($i = 0; $i < sizeof($files); $i++)
		{
			$sql = 'SELECT path FROM ' . DL_CAT_TABLE . '
				WHERE id = ' . (int) $file_assign;
			$result = $db->sql_query($sql);
			$cat_path = $db->sql_fetchfield('path');
			$db->sql_freeresult($result);

			if ($cat_path != substr($files_path[$i], 1).'/')
			{
				@copy ($dl_dir . $files_path[$i] . '/' . $files_name[$i], DL_EXT_FILES_FOLDER . $cat_path . $files_name[$i]);
				@unlink($dl_dir . $files_path[$i] . '/' . $files_name[$i]);
			}

			$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
				'cat' => $file_assign)) . ' WHERE ' . $db->sql_in_set('cat', $index, true) . " AND real_file = '" . $db->sql_escape($files_name[$i]) . "'";
			$db->sql_query($sql);

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FILE_ADD', false, array($files_name[$i]));
		}
	}

	$file_action = $file_command = $new_path = '';
}

if (isset($index) && sizeof($index))
{
	$unas_files = $files_temp = array();

	$sql = 'SELECT description, real_file FROM ' . DOWNLOADS_TABLE . '
		WHERE ' . $db->sql_in_set('cat', $index, true);
	$result = $db->sql_query($sql);

	$total_unassigned_files = $db->sql_affectedrows($result);

	$i = 0;
	if ($action == 'unassigned')
	{
		while ($row = $db->sql_fetchrow($result))
		{
			$real_file = $row['real_file'];
			$file_desc = $row['description'];
			$unas_files[$i] = $real_file;
			$unas_files[$real_file] = $file_desc;
			$i++;
		}
	}

	$db->sql_freeresult($result);

	if ($action == 'unassigned' && sizeof($unas_files))
	{
		$read_files = \oxpus\dlext\phpbb\classes\ dl_physical::read_dl_files('', $unas_files);
		$read_files = substr($read_files, 0, strlen($read_files) - 1 );

		$files = explode('|', $read_files);

		for ($i = 0; $i < sizeof($files); $i++)
		{
			$temp = strripos($files[$i], '/');
			$files_data[] = substr($files[$i],0,$temp).'|'.substr($files[$i],$temp+1);
		}
	}
}

if ($action == 'check_file_sizes')
{
	$sql = 'SELECT dl.*, c.path FROM ' . DOWNLOADS_TABLE . ' dl, ' . DL_CAT_TABLE . ' c
		WHERE dl.cat = c.id
			AND dl.extern <> 1
		ORDER BY dl.id';
	$result = $db->sql_query($sql);

	$message = '';

	while ( $row = $db->sql_fetchrow($result) )
	{
		$file_size	= $row['file_size'];
		$file_desc	= $row['description'];
		$real_file	= $row['real_file'];
		$file_path	= $row['path'];
		$file_id	= $row['id'];

		$check_file_size = sprintf("%u", @filesize(DL_EXT_FILES_FOLDER . $file_path . $real_file));
		if ( $check_file_size == 0 || $check_file_size == '' )
		{
			$message .= $file_desc . '<br />';
		}
		else if ($check_file_size <> $file_size)
		{
			$sql_new = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
					'file_size' => $check_file_size)) . ' WHERE id = ' . (int) $file_id;
			$result_new = $db->sql_query($sql_new);

			if (!$result_new)
			{
				$message .= $file_desc . '<br />';
			}
		}
	}
	$action = '';

	if ( $message != '' )
	{
		$check_message = $language->lang('DL_CHECK_FILESIZES_RESULT_ERROR') . '<br /><br />' . $message;
	}
	else
	{
		$check_message = $language->lang('DL_CHECK_FILESIZES_RESULT');
	}

	$check_message .= "<br /><br />" . $language->lang('CLICK_RETURN_FILE_MANAGEMENT', '<a href="' . $basic_link . '">', '</a>') . adm_back_link($this->u_action);

	$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FILES_CHECK');

	if ($message)
	{
		trigger_error($check_message, E_USER_WARNING);
	}
	else
	{
		trigger_error($check_message);
	}
}

if ($action == 'check_thumbnails')
{
	$thumbs = $request->variable('thumb', array(''), true);

	$del_real_thumbs = $request->variable('del_real_thumbs', '', true);

	if ($del_real_thumbs)
	{
		for ($i = 0; $i < sizeof($thumbs); $i++)
		{
			@unlink(DL_EXT_THUMBS_FOLDER . base64_decode($thumbs[$i]));
		}

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_THUMBS_DEL');
	}

	$real_thumbnails['file_name'] = array();
	$real_thumbnails['file_size'] = array();

	@$dir = opendir(DL_EXT_THUMBS_FOLDER);

	while (false !== ($file=@readdir($dir)))
	{
		if ($file{0} != "." && !is_dir($file) && $file != 'index.html' && $file != 'index.htm')
		{
			$real_thumbnails['file_name'][] = $file;
			$real_thumbnails['file_size'][] = sprintf("%u", @filesize(DL_EXT_THUMBS_FOLDER . $file));
		}
	}

	@closedir($dir);

	$dl_thumbs = array();

	$sql = 'SELECT thumbnail FROM ' . DOWNLOADS_TABLE . "
		WHERE thumbnail <> ''";
	$result = $db->sql_query($sql);

	while ($dl_thumbs[] = $db->sql_fetchfield('thumbnail') );
	$db->sql_freeresult($result);

	$sql = 'SELECT img_name FROM ' . DL_IMAGES_TABLE;
	$result = $db->sql_query($sql);

	while ($dl_thumbs[] = $db->sql_fetchfield('img_name') );
	$db->sql_freeresult($result);

	if (sizeof($real_thumbnails['file_name']))
	{
		$template->set_filenames(array(
			'toolbox' => 'dl_thumbs_body.html')
		);

		$template->assign_vars(array(
			'S_MANAGE_ACTION'	=> "{$basic_link}&amp;action=check_thumbnails",

			'U_BACK'			=> ($action == 'check_thumbnails') ? $this->u_action : '',
		));

		$j = -1;

		for ($i = 0; $i < sizeof($real_thumbnails['file_name']); $i++)
		{
			$real_file = $real_thumbnails['file_name'][$i];
			if (!in_array($real_file, $dl_thumbs))
			{
				$j++;
				$checkbox = '<input type="checkbox" class="permissions-checkbox" name="thumb[' . $j . ']" value="' . base64_encode($real_file) . '" />';
			}
			else
			{
				$checkbox = '';
			}

			$template->assign_block_vars('thumbnails', array(
				'CHECKBOX'		=> $checkbox,
				'REAL_FILE'		=> $real_file,
				'FILE_SIZE'		=> \oxpus\dlext\phpbb\classes\ dl_format::dl_size($real_thumbnails['file_size'][$i]),

				'U_REAL_FILE'	=> DL_EXT_THUMBS_FOLDER . $real_file,
			));
		}

		$template->assign_var('S_DL_THUMBS', true);
	}
	else
	{
		$action = 'browse';
	}
}

if ($files && $file_command)
{
	$path_temp = $path;
	$path .= ($path) ? '/' : '';

	if ($file_command == 'del')
	{
		for ($i = 0; $i < sizeof($files); $i++)
		{
			@unlink(DL_EXT_FILES_FOLDER . $path . $files[$i]);

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FILE_DROP', false, array($files[$i]));
		}
	}
	else
	{
		for ($i = 0; $i < sizeof($files); $i++)
		{
			@copy (DL_EXT_FILES_FOLDER . $path . $files[$i], $file_command . $files[$i]);
			@unlink(DL_EXT_FILES_FOLDER . $path . $files[$i]);

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FILE_MOVE', false, array($files[$i]));
		}
	}

	$path = $path_temp;
	$file_action = $file_command = $new_path = '';
}

if ($dir_name && $dircreate)
{
	$upas = array('�' => 'ae', '�' => 'ue', '�' => 'oe', '�' => 'Ae', '�' => 'Ue', '�' => 'Oe', '�' => 'ss');
	$upass = array(' ' => '', '+' => '', '%' => '');
	$dir_name = strtr(urlencode(strtr(utf8_decode($dir_name), $upas)), $upass);

	@mkdir(DL_EXT_FILES_FOLDER . $path . '/' . $dir_name);
	@chmod(DL_EXT_FILES_FOLDER . $path . '/' . $dir_name, 0777);

	$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FOLDER_CREATE', false, array($path . '/' . $dir_name));
}

if ($action == 'dirdelete')
{
	$file_name = basename($path);

	$content_count = 0;

	$sh = @opendir(DL_EXT_FILES_FOLDER . $path . '/' . $file);

	while (false !== ($subfile=@readdir($sh)))
	{
		if (substr($subfile,0,1)!=".")
		{
			$content_count++;
		}
	}

	@closedir($sh);

	if ($content_count == 0)
	{
		@rmdir(DL_EXT_FILES_FOLDER . $path);

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FOLDER_DROP', false, array($path));
	}

	$action = '';

	$path = ($path != $file_name) ? substr($path, 0, strlen($path) - strlen($file_name)-1) : '';
}

if ($action == 'browse' || $action == '' || $action == 'unassigned')
{
	if ($action != 'unassigned')
	{
		$temp_url = '';
		$temp_dir = array();

		$dl_navi = '<a href="' . $basic_link . '&amp;action=browse">' . DL_EXT_FILES_FOLDER . '</a>';

		$dirs = $dirs_delete = $files = $filen = $sizes = $exist = array();

		$existing_files = array();
		$existing_files = \oxpus\dlext\phpbb\classes\ dl_physical::read_exist_files();

		if ($path)
		{
			$path = ($path{0} == '/') ? substr($path, 1) : $path;

			$temp_dir = explode('/', $path);
			if (sizeof($temp_dir) > 0)
			{
				for ($i = 0; $i < sizeof($temp_dir); $i++)
				{
					$temp_url .= '/'.$temp_dir[$i];
					$temp_path = preg_replace('#[/]*#', '', $temp_dir[$i]);
					$dl_navi .= '<a href="' . $basic_link . '&amp;action=browse&amp;path=' . $temp_url . '">' . $temp_path . '/</a>';
				}
			}

			$real_file_array = array();

			$sql = 'SELECT d.file_name, d.real_file FROM ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . " c
				WHERE d.cat = c.id
					AND c.path = '" . $db->sql_escape(utf8_decode($path)) . "/'";
			$result = $db->sql_query($sql);
			$total_files = $db->sql_affectedrows($result);

			if ($total_files)
			{
				while ($row = $db->sql_fetchrow($result))
				{
					$real_file_array[$row['real_file']] = $row['file_name'];
				}
			}

			$db->sql_freeresult($result);

			$sql = 'SELECT v.ver_file_name, v.ver_real_file FROM ' . DL_VERSIONS_TABLE . ' v, ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . " c
				WHERE d.cat = c.id
					AND v.dl_id = d.id
					AND c.path = '" . $db->sql_escape(utf8_decode($path)) . "/'";
			$result = $db->sql_query($sql);
			$total_files = $db->sql_affectedrows($result);

			if ($total_files)
			{
				while ($row = $db->sql_fetchrow($result))
				{
					$real_file_array[$row['ver_real_file']] = $row['ver_file_name'];
				}
			}

			$db->sql_freeresult($result);
		}

		$dh = @opendir(DL_EXT_FILES_FOLDER . $path);

		while (false !== ($file = @readdir($dh)))
		{
			if (substr($file, 0, 1) != '.' && strpos($file, 'index.htm') === false)
			{
				if (is_dir(DL_EXT_FILES_FOLDER . $path . '/' . $file))
				{
					$slash = ($path) ? '/' : '';
					$dirs[] = $path . $slash . $file . '|~|<a href="' . $basic_link . '&amp;action=browse&amp;path=' . $path . $slash . $file . '">' . $file . '</a>';

					$sh = @opendir(DL_EXT_FILES_FOLDER . $path . '/' . $file);

					$content_count = 0;

					while (false !== ($subfile = @readdir($sh)))
					{
						if (substr($subfile,0,1)!="." && strpos($subfile, 'index.htm') !== false)
						{
							$content_count++;
						}
					}

					@closedir($sh);

					$dirs_delete[] = ($content_count == 0) ? '<a href="' . $basic_link . '&amp;action=dirdelete&amp;path=' . $path . $slash . $file . '">' . $language->lang('DL_DELETE') . '</a>' : $language->lang('DL_MANAGE_CONTENT_COUNT', $content_count);
				}
				else
				{
					$real_file_name = (isset($real_file_array[$file])) ? $real_file_array[$file] : $file;
					$files[] = $real_file_name . '|~|<a href="' . DL_EXT_FILES_FOLDER . $path . '/' . $file.'">' . $real_file_name . '</a>';
					$filen[] = $file;
					$sizes[] = sprintf("%u", @filesize(DL_EXT_FILES_FOLDER . $path .'/' . $file));
					$exist[] = (in_array($file, $existing_files)) ? true : 0;
				}
			}
		}

		@closedir($dh);

		$template->assign_var('S_CREATE_DIR_COMMAND', true);
	}
	else
	{
		$dl_navi = $language->lang('DL_UNASSIGNED_FILES');
	}

	$template->set_filenames(array(
		'toolbox' => 'dl_toolbox_body.html')
	);

	$template->assign_vars(array(
		'DL_NAVI'					=> $dl_navi,

		'S_MANAGE_ACTION'			=> "{$basic_link}&amp;path=$path",

		'U_UNASSIGNED_FILES'		=> "{$basic_link}&amp;action=unassigned",
		'U_DOWNLOADS_CHECK_FILES'	=> "{$basic_link}&amp;action=check_file_sizes",
		'U_DOWNLOADS_CHECK_THUMB'	=> "{$basic_link}&amp;action=check_thumbnails")
	);

	$existing_thumbs = 0;
	@$dir = opendir(DL_EXT_THUMBS_FOLDER);

	while (false !== ($file = @readdir($dir)))
	{
		if ($file{0} != "." && !is_dir($file))
		{
			$existing_thumbs = true;
			break;
		}
	}

	@closedir($dir);

	if ($existing_thumbs)
	{
		$template->assign_var('S_THUMBNAIL_CHECK', true);
	}

	if (!$dirs && !$files)
	{
		$template->assign_var('S_EMPTY_FOLDER', true);
	}

	if (isset($total_unassigned_files) && $total_unassigned_files && $action != 'unassigned')
	{
		$template->assign_var('S_UNASSIGNED_FILES', true);
	}

	if ($dirs)
	{
		natcasesort($dirs);
		foreach($dirs as $i => $value)
		{
			$dir_ary = explode('|~|', $value);
			$template->assign_block_vars('dirs_row', array(
				'DIR_LINK' => $dir_ary[1],
				'DIR_DELETE_LINK' => $dirs_delete[$i])
			);
		}
	}

	if ($files)
	{
		natcasesort($files);
		$overall_size = 0;
		$missing_count = 0;
		foreach($files as $i => $value)
		{
			$files_ary = explode('|~|', $value);
			$file_size = ($action != 'unassigned') ? $sizes[$i] : sprintf("%u", @filesize(DL_EXT_FILES_FOLDER . $files_ary[1]));

			$file_size_tmp = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($file_size, 2, 'no');
			$file_size_out = $file_size_tmp['size_out'];
			$file_size_range = $file_size_tmp['range'];

			if ($action != 'unassigned')
			{
				$template->assign_block_vars('files_row', array(
					'FILE_NAME' => $files_ary[1],
					'FILE_SIZE' => $file_size_out,
					'FILE_SIZE_RANGE' => $file_size_range,
					'FILE_EXIST' => (!$exist[$i]) ? '<input type="checkbox" class="permissions-checkbox" name="files[]" value="' . $filen[$i] . '" />' : '<input type="checkbox" class="permissions-checkbox" value="" disabled="disabled" />',
					'S_UNKNOWN_FILE' => (!$exist[$i]) ? true : false,
				));
				if (!$exist[$i])
				{
					$missing_count++;
				}
			}
			else
			{
				$template->assign_block_vars('files_row', array(
					'FILE_NAME' => $unas_files[substr($files_ary[1], strrpos($files_ary[1], '/') + 1)],
					'FILE_NAME_REAL' => $files_ary[1],
					'FILE_SIZE' => $file_size_out,
					'FILE_SIZE_RANGE' => $file_size_range,
					'FILE_EXIST' => '<input type="checkbox" class="permissions-checkbox" name="files[]" value="' . $files_data[$i] . '" />')
				);
				$missing_count++;
			}
			$overall_size += $file_size;
		}

		$overall_size_tmp = array();
		$overall_size_tmp = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($overall_size, 2, 'no');
		$overall_size_out = $overall_size_tmp['size_out'];
		$file_size_range = $overall_size_tmp['range'];

		$cur = $path;

		if ($action != 'unassigned')
		{
			$s_file_action = '<select name="file_command">';
			$s_file_action .= '<option value="del">'.$language->lang('DL_DELETE').'</option>';
			$s_file_action .= '<option value="---">---------------</option>';
			$s_file_action .= \oxpus\dlext\phpbb\classes\ dl_physical::read_dl_dirs();
		}
		else
		{
			$s_file_action = '<select name="file_assign">';
			$s_file_action .= '<option value="del">'.$language->lang('DL_DELETE').'</option>';
			$s_file_action .= '<option value="---">---------------</option>';
			$s_file_action .= \oxpus\dlext\phpbb\classes\ dl_extra::dl_dropdown(0, 0, 0, 'auth_view');
		}
		$s_file_action .= '</select>';

		$template->assign_block_vars('overall_size', array(
			'OVERALL_SIZE' => $overall_size_out,
			'OVERALL_SIZE_RANGE' => $file_size_range)
		);

		if ($missing_count)
		{
			$template->assign_block_vars('file_move_delete', array(
				'S_FILE_ACTION' => $s_file_action)
			);
		}
	}
}

if ($action <> 'check_thumbnails')
{
	$template->assign_var('S_DL_TOOLBOX', true);
}

$template->assign_display('toolbox');
