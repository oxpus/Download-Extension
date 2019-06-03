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
else
{
	$action = ($add) ? 'add' : $action;
	$action = ($edit) ? 'edit' : $action;
	$action = ($move) ? 'category_order' : $action;
	$action = ($save_cat) ? 'save_cat' : $action;
}

$index = array();
$index = \oxpus\dlext\phpbb\classes\ dl_main::full_index();

if (!sizeof($index) && $action != 'save_cat')
{
	$action = 'add';
}

if ($cat_id)
{
	$sql = 'SELECT cat_name FROM ' . DL_CAT_TABLE . '
		WHERE id = ' . (int) $cat_id;
	$result = $db->sql_query($sql);
	$log_cat_name = $db->sql_fetchfield('cat_name');
	$db->sql_freeresult($result);
}

$dl_template_in_use = false;

$cat_parent			= $request->variable('parent', 0);
$description		= $request->variable('description', '', true);
$rules				= $request->variable('rules', '', true);
$cat_name			= $request->variable('cat_name', '', true);
$path				= $request->variable('path', '/');
$must_approve		= $request->variable('must_approve', 1);
$allow_mod_desc		= $request->variable('allow_mod_desc', 0);
$statistics			= $request->variable('statistics', 1);
$stats_prune		= $request->variable('stats_prune', 100000);
$comments			= $request->variable('comments', 1);
$cat_traffic		= $request->variable('cat_traffic', 0);
$cat_traffic_range	= $request->variable('cat_traffic_range', '');
$allow_thumbs		= $request->variable('allow_thumbs', 0);
$approve_comments	= $request->variable('approve_comments', 0);
$bug_tracker		= $request->variable('bug_tracker', 0);
$perms_copy_from	= $request->variable('perms_copy_from', 0);
$topic_forum		= $request->variable('dl_topic_forum', 0);
$topic_text			= $request->variable('dl_topic_text', '', true);
$cat_icon			= $request->variable('cat_icon', '', true);
$diff_topic_user	= $request->variable('diff_topic_user', $config['dl_diff_topic_user']);
$topic_user			= $request->variable('dl_topic_user', '', true);
$topic_more_details	= $request->variable('topic_more_details', 1);
$show_file_hash		= $request->variable('show_file_hash', 0);
$idx_type			= $request->variable('type', 'c');
$topic_type			= $request->variable('topic_type', POST_NORMAL);
$set_add			= $request->variable('set_add', 0);
$set_user			= $request->variable('set_user', '', true);

$error = false;
$error_msg = '';

if ($action == 'save_cat' && $path && !@file_exists(DL_EXT_FILES_FOLDER . $path) || substr($path, -1, 1) <> '/')
{
	$error = true;
	$error_msg = $language->lang('DL_PATH_NOT_EXIST', $path, DL_EXT_FILES_FOLDER, DL_EXT_FILES_FOLDER . $path);
	$action = ($cat_id) ? 'edit' : 'add';
	$submit = true;
	$s_hidden_fields = array('cat_id' => $cat_id);
}

if($action == 'edit' || $action == 'add')
{
	$s_hidden_fields = (isset($s_hidden_fields)) ? array_merge($s_hidden_fields, array('action' => 'save_cat')) : array('action' => 'save_cat');

	if($action == 'edit' && $cat_id && !$submit)
	{
		$cat_name			= $index[$cat_id]['cat_name'];
		$cat_name			= str_replace('&nbsp;&nbsp;|___&nbsp;', '', $cat_name);
		$description		= $index[$cat_id]['description'];
		$rules				= $index[$cat_id]['rules'];
		$cat_path			= $index[$cat_id]['cat_path'];
		$s_cat_parent		= '<select name="parent">';
		$s_cat_parent		.= '<option value="0">&nbsp;»&nbsp;'.$language->lang('DL_CAT_INDEX').'</option>';
		$s_cat_parent		.= \oxpus\dlext\phpbb\classes\ dl_extra::dl_dropdown(0, 0, $index[$cat_id]['parent'], 'auth_view', $cat_id);
		$s_cat_parent		.= '</select>';
		$desc_uid			= $index[$cat_id]['desc_uid'];
		$rules_uid			= $index[$cat_id]['rules_uid'];
		$desc_bitfield		= $index[$cat_id]['desc_bitfield'];
		$rules_bitfield		= $index[$cat_id]['rules_bitfield'];
		$desc_flags			= $index[$cat_id]['desc_flags'];
		$rules_flags		= $index[$cat_id]['rules_flags'];
		$statistics			= $index[$cat_id]['statistics'];
		$stats_prune		= $index[$cat_id]['stats_prune'];
		$comments			= $index[$cat_id]['comments'];
		$must_approve		= $index[$cat_id]['must_approve'];
		$allow_mod_desc		= $index[$cat_id]['allow_mod_desc'];
		$cat_traffic		= $index[$cat_id]['cat_traffic'];
		$cat_remain_traffic	= $index[$cat_id]['cat_traffic'] - $index[$cat_id]['cat_traffic_use'];
		$allow_thumbs		= $index[$cat_id]['allow_thumbs'];
		$approve_comments	= $index[$cat_id]['approve_comments'];
		$bug_tracker		= $index[$cat_id]['bug_tracker'];
		$topic_more_details	= $index[$cat_id]['topic_more_details'];
		$topic_forum		= $index[$cat_id]['dl_topic_forum'];
		$topic_text			= $index[$cat_id]['dl_topic_text'];
		$diff_topic_user	= $index[$cat_id]['diff_topic_user'];
		$topic_user			= $index[$cat_id]['topic_user'];
		$show_file_hash		= $index[$cat_id]['show_file_hash'];
		$cat_icon			= $index[$cat_id]['cat_icon'];
		$topic_type			= $index[$cat_id]['dl_topic_type'];
		$set_add			= $index[$cat_id]['dl_set_add'];
		$set_user			= $index[$cat_id]['dl_set_user'];

		$perms_copy_from	= '<select name="perms_copy_from">';
		$perms_copy_from	.= '<option value="-1">&nbsp;»&nbsp;'.$language->lang('DL_NO_PERMS_COPY').'</option>';
		$perms_copy_from	.= '<option value="0">&nbsp;»&nbsp;'.$language->lang('DL_CAT_PARENT').'</option>';
		$perms_copy_from	.= \oxpus\dlext\phpbb\classes\ dl_extra::dl_dropdown(0, 0, $index[$cat_id]['parent'], 'auth_view', $cat_id);
		$perms_copy_from	.= '</select>';

		$text_ary		= generate_text_for_edit($description, $desc_uid, $desc_flags);
		$description	= $text_ary['text'];

		$text_ary		= generate_text_for_edit($rules, $rules_uid, $rules_flags);
		$rules			= $text_ary['text'];

		$s_hidden_fields = (!$submit) ? array_merge($s_hidden_fields, array('cat_id' => $cat_id)) : $s_hidden_fields;
	}
	else
	{
		if ($cat_traffic_range == 'KB')
		{
			$cat_traffic = $cat_traffic * 1024;
		}
		else if ($cat_traffic_range == 'MB')
		{
			$cat_traffic = $cat_traffic * 1048576;
		}
		else if ($cat_traffic_range == 'GB')
		{
			$cat_traffic = $cat_traffic * 1073741824;
		}

		$cat_path			= ($path) ? $path : '/';
		$cat_parent_id		= $cat_parent;
		$s_cat_parent		= '<select name="parent">';
		$s_cat_parent		.= '<option value="0">&nbsp;»&nbsp;'.$language->lang('DL_CAT_INDEX').'</option>';
		$s_cat_parent		.= \oxpus\dlext\phpbb\classes\ dl_extra::dl_dropdown(0, 0, $cat_parent_id, 'auth_view', -1);
		$s_cat_parent		.= '</select>';
		$cat_remain_traffic	= $cat_traffic;
		$perm_cat_id		= $perms_copy_from;
		$perms_copy_from	= '<select name="perms_copy_from">';
		$perms_copy_from	.= '<option value="0">&nbsp;»&nbsp;'.$language->lang('DL_CAT_PARENT').'</option>';
		$perms_copy_from	.= \oxpus\dlext\phpbb\classes\ dl_extra::dl_dropdown(0, 0, $perm_cat_id, 'auth_view', -1);
		$perms_copy_from	.= '</select>';
	}

	$t_path_select = \oxpus\dlext\phpbb\classes\ dl_physical::get_file_base_tree(DL_EXT_FILES_WEBFOLDER, $cat_path);
	$s_path_select = '<select name="path">';
	$s_path_select .= '<option value="/">' . $language->lang('DL_CAT_PATH_SELECT') . '</option>';

	sort($t_path_select);
	foreach ($t_path_select as $key => $value)
	{
		$tree_data = $t_path_select[$key];
		$s_path_select .= '<option value="' . $tree_data['cat_path'] . '"' . $tree_data['selected'] . '>' . $tree_data['entry'] . '</option>';
	}
	$s_path_select .= '</select>';
	$s_path_select = str_replace('value="' . $cat_path . '">', 'value="' . $cat_path . '" selected="selected">', $s_path_select);

	$s_topic_user_select = '<select name="diff_topic_user">';
	$s_topic_user_select .= '<option value="0">' . $language->lang('DL_TOPIC_USER_SELF') . '</option>';
	$s_topic_user_select .= '<option value="1">' . $language->lang('DL_TOPIC_USER_OTHER') . '</option>';
	$s_topic_user_select .= '</select>';
	$s_topic_user_select = str_replace('value="' . $diff_topic_user . '">', 'value="' . $diff_topic_user . '" selected="selected">', $s_topic_user_select);

	$s_set_user_select = '<select name="set_add">';
	$s_set_user_select .= '<option value="0">' . $language->lang('DL_TOPIC_USER_SELF') . '</option>';
	$s_set_user_select .= '<option value="1">' . $language->lang('DL_TOPIC_USER_OTHER') . '</option>';
	$s_set_user_select .= '</select>';
	$s_set_user_select = str_replace('value="' . $set_add . '">', 'value="' . $set_add . '" selected="selected">', $s_set_user_select);

	$cat_traffic_out	= 0;
	$cat_remain_traffic	= ($cat_remain_traffic < 0) ? 0 : $cat_remain_traffic;
	$cat_remain_traffic	= \oxpus\dlext\phpbb\classes\ dl_format::dl_size($cat_remain_traffic);

	$s_select_datasize	= '<option value="KB">' . $language->lang('DL_KB') . '</option>';
	$s_select_datasize	.= '<option value="MB">' . $language->lang('DL_MB') . '</option>';
	$s_select_datasize	.= '<option value="GB">' . $language->lang('DL_GB') . '</option>';
	$s_select_datasize	.= '</select>';

	if ($cat_traffic > 1073741823)
	{
		$cat_traffic_out	= number_format($cat_traffic / 1073741824, 2);
		$data_range_select	= 'GB';
	}
	else if ($cat_traffic > 1048575)
	{
		$cat_traffic_out	= number_format($cat_traffic / 1048576, 2);
		$data_range_select	= 'MB';
	}
	else if ($cat_traffic > 1023)
	{
		$cat_traffic_out	= number_format($cat_traffic / 1024, 2);
		$data_range_select	= 'KB';
	}
	else
	{
		$data_range_select	= 'KB';
	}

	$cat_traffic_range	= str_replace('value="' . $data_range_select . '">', 'value="' . $data_range_select . '" selected="selected">', $s_select_datasize);
	$cat_traffic_range	= '<select name="cat_traffic_range">' . $cat_traffic_range;

	$approve_yes	= ($must_approve) ? 'checked="checked"' : '';
	$approve_no		= (!$must_approve) ? 'checked="checked"' : '';

	$allow_mod_desc_yes	= ($allow_mod_desc) ? 'checked="checked"' : '';
	$allow_mod_desc_no	= (!$allow_mod_desc) ? 'checked="checked"' : '';

	$stats_yes	= ($statistics) ? 'checked="checked"' : '';
	$stats_no	= (!$statistics) ? 'checked="checked"' : '';

	$comments_yes	= ($comments) ? 'checked="checked"' : '';
	$comments_no	= (!$comments) ? 'checked="checked"' : '';

	$allow_thumbs_yes	= ($allow_thumbs) ? 'checked="checked"' : '';
	$allow_thumbs_no	= (!$allow_thumbs) ? 'checked="checked"' : '';

	$approve_comments_yes	= ($approve_comments) ? 'checked="checked"' : '';
	$approve_comments_no	= (!$approve_comments) ? 'checked="checked"' : '';

	$bug_tracker_yes	= ($bug_tracker) ? 'checked="checked"' : '';
	$bug_tracker_no		= (!$bug_tracker) ? 'checked="checked"' : '';

	$show_file_hash_yes	= ($show_file_hash) ? 'checked="checked"' : '';
	$show_file_hash_no	= (!$show_file_hash) ? 'checked="checked"' : '';

	$language->add_lang('posting');

	$s_topic_type = '<select name="topic_type">';
	$s_topic_type .= '<option value="' . POST_NORMAL . '">' . $language->lang('POST_NORMAL') . '</option>';
	$s_topic_type .= '<option value="' . POST_STICKY . '">' . $language->lang('POST_STICKY') . '</option>';
	$s_topic_type .= '<option value="' . POST_ANNOUNCE . '">' . $language->lang('POST_ANNOUNCEMENT') . '</option>';
	$s_topic_type .= '<option value="' . POST_GLOBAL . '">' . $language->lang('POST_GLOBAL') . '</option>';
	$s_topic_type .= '</select>';
	$s_topic_type = str_replace('value="' . $topic_type . '">', 'value="' . $topic_type . '" selected="selected">', $s_topic_type);

	$template->set_filenames(array(
		'category' => 'dl_cat_edit_body.html')
	);

	if ($config['dl_thumb_fsize'])
	{
		$template->assign_var('S_THUMNAILS', true);
	}

	if ($config['dl_topic_forum'] == -1)
	{
		$template->assign_var('S_ENTER_TOPIC_FORUM', true);

		$forum_select_tmp = get_forum_list('f_list', false);
		$s_forum_select = '';

		foreach ($forum_select_tmp as $key => $value)
		{
			switch ($value['forum_type'])
			{
				case FORUM_CAT:
					if ($s_forum_select)
					{
						$s_forum_select .= '</optgroup>';
					}
					$s_forum_select .= '<optgroup label="' . $value['forum_name'] . '">';
				break;
				case FORUM_POST:
					$s_forum_select .= '<option value="' . $value['forum_id'] . '">' . $value['forum_name'] . '</option>';
				break;
			}
		}

		$s_forum_select = '<select name="dl_topic_forum"><option value="0">' . $language->lang('DEACTIVATE') . '</option>' . $s_forum_select . '</optgroup></select>';
		$s_forum_select = str_replace('value="' . $topic_forum . '">', 'value="' . $topic_forum . '" selected="selected">', $s_forum_select);

		$template->assign_var('S_TOPIC_DETAILS', true);

		$s_topic_more_details = '<select name="topic_more_details">';
		$s_topic_more_details .= '<option value="0">' . $language->lang('DL_TOPIC_NO_MORE_DETAILS') . '</option>';
		$s_topic_more_details .= '<option value="1">' . $language->lang('DL_TOPIC_MORE_DETAILS_UNDER') . '</option>';
		$s_topic_more_details .= '<option value="2">' . $language->lang('DL_TOPIC_MORE_DETAILS_OVER') . '</option>';
		$s_topic_more_details .= '</select>';
		$s_topic_more_details = str_replace('value="' . $topic_more_details . '">', 'value="' . $topic_more_details . '" selected="selected">', $s_topic_more_details);
	}
	else
	{
		$s_forum_select = '';
		$s_topic_more_details = '';
	}

	if ($config['dl_diff_topic_user'] == 2)
	{
		$template->assign_var('S_TOPIC_USER_ON', true);
	}

	if ($config['dl_set_add'] == 2)
	{
		$template->assign_var('S_SET_USER_ON', true);
	}

	add_form_key('dl_adm_cats');

	$basic_link	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

	$template->assign_vars(array(
		'L_DL_CAT_MODE'					=> ($action == 'edit') ? $language->lang('EDIT') : $language->lang('ADD'),
		'L_DL_CAT_TRAFFIC'				=> (isset($index[$cat_id]['cat_traffic']) && $index[$cat_id]['cat_traffic'] && isset($cat_remain_traffic) && $cat_remain_traffic) ? $language->lang('DL_CAT_TRAFFIC', $cat_remain_traffic) : $language->lang('DL_CAT_TRAFFIC_OFF'),
		'L_DL_CAT_TRAFFIC_HELP'			=> htmlentities((isset($index[$cat_id]['cat_traffic']) && $index[$cat_id]['cat_traffic'] && isset($cat_remain_traffic) && $cat_remain_traffic) ? $language->lang('DL_CAT_TRAFFIC', $cat_remain_traffic) : $language->lang('DL_CAT_TRAFFIC_OFF')),
		'L_DL_UCONF_LINK_EXPLAIN'		=> 'DL_UCONF_LINK',

		'ERROR_MSG'				=> $error_msg,
		'CATEGORY'				=> (isset($index[$cat_id]['cat_name'])) ? $language->lang('DL_PERMISSIONS', $index[$cat_id]['cat_name']) : '',
		'MUST_APPROVE_YES'		=> $approve_yes,
		'MUST_APPROVE_NO'		=> $approve_no,
		'ALLOW_MOD_DESC_YES'	=> $allow_mod_desc_yes,
		'ALLOW_MOD_DESC_NO'		=> $allow_mod_desc_no,
		'STATS_YES'				=> $stats_yes,
		'STATS_NO'				=> $stats_no,
		'STATS_PRUNE'			=> $stats_prune,
		'COMMENTS_YES'			=> $comments_yes,
		'COMMENTS_NO'			=> $comments_no,
		'CAT_NAME'				=> $cat_name,
		'DESCRIPTION'			=> $description,
		'RULES'					=> $rules,
		'CAT_PARENT'			=> $s_cat_parent,
		'CAT_TRAFFIC'			=> $cat_traffic_out,
		'ALLOW_THUMBS_YES'		=> $allow_thumbs_yes,
		'ALLOW_THUMBS_NO'		=> $allow_thumbs_no,
		'APPROVE_COMMENTS_YES'	=> $approve_comments_yes,
		'APPROVE_COMMENTS_NO'	=> $approve_comments_no,
		'BUG_TRACKER_YES'		=> $bug_tracker_yes,
		'BUG_TRACKER_NO'		=> $bug_tracker_no,
		'PERMS_COPY_FROM'		=> $perms_copy_from,
		'TOPIC_TEXT'			=> $topic_text,
		'CAT_ICON'				=> $cat_icon,
		'TOPIC_USER'			=> \oxpus\dlext\phpbb\classes\ dl_extra::dl_user_switch($topic_user),
		'SHOW_FILE_HASH_YES'	=> $show_file_hash_yes,
		'SHOW_FILE_HASH_NO'		=> $show_file_hash_no,
		'SET_USER'				=> \oxpus\dlext\phpbb\classes\ dl_extra::dl_user_switch($set_user),

		'S_TOPIC_TYPE'			=> $s_topic_type,
		'S_CAT_PATH'			=> $s_path_select,
		'S_DL_TOPIC_FORUM'		=> $s_forum_select,
		'S_CAT_TRAFFIC_RANGE'	=> $cat_traffic_range,
		'S_CATEGORY_ACTION'		=> $basic_link,
		'S_DL_DIFF_TOPIC_USER'	=> $s_topic_user_select,
		'S_SET_USER'			=> $s_set_user_select,
		'S_USER_SELECT'			=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=dl_edit_cat&amp;field=set_user&amp;select_single=true'),
		'S_USER_SELECT_2'			=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=dl_edit_cat&amp;field=dl_topic_user&amp;select_single=true'),
		'S_TOPIC_MORE_DETAILS'	=> $s_topic_more_details,
		'S_ERROR'				=> $error,
		'S_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields),

		'U_BACK'				=> $this->u_action,
	));

	$template->assign_var('S_DL_CATEGORY_EDIT', true);

	$template->assign_display('category');

	$dl_template_in_use = true;
}
else if($action == 'save_cat')
{
	if (!check_form_key('dl_adm_cats'))
	{
		trigger_error('FORM_INVALID', E_USER_WARNING);
	}

	if (strpos(strtolower($cat_icon), "http"))
	{
		$cat_icon = '';
	}

	$allow_bbcode	= ($config['allow_bbcode']) ? true : false;
	$allow_urls		= true;
	$allow_smilies	= ($config['allow_smilies']) ? true : false;
	$desc_uid		= $desc_bitfield = $rules_uid = $rules_bitfield = '';
	$desc_flags		= $rules_flags = 0;

	if ($description)
	{
		generate_text_for_storage($description, $desc_uid, $desc_bitfield, $desc_flags, $allow_bbcode, true, $allow_smilies);
	}

	if ($rules)
	{
		generate_text_for_storage($rules, $rules_uid, $rules_bitfield, $rules_flags, $allow_bbcode, true, $allow_smilies);
	}

	if ($cat_traffic_range == 'KB')
	{
		$cat_traffic = $cat_traffic * 1024;
	}
	else if ($cat_traffic_range == 'MB')
	{
		$cat_traffic = $cat_traffic * 1048576;
	}
	else if ($cat_traffic_range == 'GB')
	{
		$cat_traffic = $cat_traffic * 1073741824;
	}

	// Move files, if the path was changed
	if ($cat_id && $index[$cat_id]['path'] != $path)
	{
		$old_path = DL_EXT_FILES_FOLDER . $index[$cat_id]['path'];
		$new_path = DL_EXT_FILES_FOLDER . $path;

		$move_mode = (@ini_get('open_basedir') || @ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'on') ? 'move' : 'copy';

		$sql = 'SELECT v.ver_real_file, d.real_file FROM ' . DOWNLOADS_TABLE . ' d
			LEFT JOIN ' . DL_VERSIONS_TABLE . ' v ON v.dl_id = d.id
			WHERE extern = 0
				AND cat = ' . (int) $cat_id;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$real_file = $row['real_file'];

			if ($real_file)
			{
				if (@file_exists($old_path . $real_file) && strpos($old_path, '//') === false)
				{
					$move_mode($old_path . $real_file, $new_path . $real_file);
					@unlink($old_path . $real_file);
					phpbb_chmod($new_path . $real_file, CHMOD_ALL);
				}
			}

			$ver_real_file = $row['ver_real_file'];

			if ($ver_real_file)
			{
				if (@file_exists($old_path . $ver_real_file) && strpos($old_path, '//') === false)
				{
					$move_mode($old_path . $ver_real_file, $new_path . $ver_real_file);
					@unlink($old_path . $ver_real_file);
					phpbb_chmod($new_path . $ver_real_file, CHMOD_ALL);
				}
			}
		}

		$db->sql_freeresult($result);
	}

	$topic_user = \oxpus\dlext\phpbb\classes\ dl_extra::dl_user_switch(0, $topic_user, true);
	$set_user = \oxpus\dlext\phpbb\classes\ dl_extra::dl_user_switch(0, $set_user, true);

	// Check download user-id
	if ($set_add)
	{
		if (!$set_user)
		{
			$set_user = 0;
			$set_add = 0;
		}
		else
		{
			$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $set_user;
			$result = $db->sql_query($sql);
			$user_exists = $db->sql_affectedrows($result);
			$db->sql_freeresult($result);

			if (!$user_exists)
			{
				$set_user = 0;
				$set_add = 0;
			}
		}
	}
	else
	{
		$set_user = 0;
	}

	// Check topic user-id
	if ($diff_topic_user)
	{
		if (!$topic_user)
		{
			$topic_user = 0;
			$diff_topic_user = 0;
		}
		else
		{
			$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $topic_user;
			$result = $db->sql_query($sql);
			$user_exists = $db->sql_affectedrows($result);
			$db->sql_freeresult($result);

			if (!$user_exists)
			{
				$topic_user = 0;
				$diff_topic_user = 0;
			}
		}
	}
	else
	{
		$topic_user = 0;
	}

	if($cat_id)
	{
		$sql = 'UPDATE ' . DL_CAT_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
			'description'			=> $description,
			'rules'					=> $rules,
			'parent'				=> $cat_parent,
			'cat_name'				=> $cat_name,
			'path'					=> $path,
			'desc_uid'				=> $desc_uid,
			'desc_bitfield'			=> $desc_bitfield,
			'desc_flags'			=> $desc_flags,
			'rules_uid'				=> $rules_uid,
			'rules_bitfield'		=> $rules_bitfield,
			'rules_flags'			=> $rules_flags,
			'must_approve'			=> $must_approve,
			'allow_mod_desc'		=> $allow_mod_desc,
			'statistics'			=> $statistics,
			'stats_prune'			=> $stats_prune,
			'comments'				=> $comments,
			'cat_traffic'			=> $cat_traffic,
			'allow_thumbs'			=> $allow_thumbs,
			'approve_comments'		=> $approve_comments,
			'dl_topic_forum'		=> $topic_forum,
			'dl_topic_text'			=> $topic_text,
			'approve_comments'		=> $approve_comments,
			'cat_icon'				=> $cat_icon,
			'diff_topic_user'		=> $diff_topic_user,
			'topic_user'			=> $topic_user,
			'topic_more_details'	=> $topic_more_details,
			'show_file_hash'		=> $show_file_hash,
			'bug_tracker'			=> $bug_tracker,
			'dl_set_add'			=> $set_add,
			'dl_set_user'			=> $set_user,
			'dl_topic_type'			=> $topic_type)) . ' WHERE id = ' . (int) $cat_id;

		$message = $language->lang('DL_CATEGORY_UPDATED');

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_CAT_EDIT', false, array($cat_name));
	}
	else
	{
		$sql = 'INSERT INTO ' . DL_CAT_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'cat_name'				=> $cat_name,
			'parent'				=> $cat_parent,
			'description'			=> $description,
			'rules'					=> $rules,
			'path'					=> $path,
			'desc_uid'				=> $desc_uid,
			'rules_uid'				=> $rules_uid,
			'desc_bitfield'			=> $desc_bitfield,
			'rules_bitfield'		=> $rules_bitfield,
			'desc_flags'			=> $desc_flags,
			'rules_flags'			=> $rules_flags,
			'must_approve'			=> $must_approve,
			'allow_mod_desc'		=> $allow_mod_desc,
			'statistics'			=> $statistics,
			'stats_prune'			=> $stats_prune,
			'comments'				=> $comments,
			'cat_traffic'			=> $cat_traffic,
			'allow_thumbs'			=> $allow_thumbs,
			'approve_comments'		=> $approve_comments,
			'dl_topic_forum'		=> $topic_forum,
			'dl_topic_text'			=> $topic_text,
			'cat_icon'				=> $cat_icon,
			'topic_user'			=> $topic_user,
			'topic_more_details'	=> $topic_more_details,
			'show_file_hash'		=> $show_file_hash,
			'bug_tracker'			=> $bug_tracker,
			'dl_set_add'			=> $set_add,
			'dl_set_user'			=> $set_user,
			'dl_topic_type'			=> $topic_type));

		$message = $language->lang('DL_CATEGORY_ADDED');

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_CAT_ADD', false, array($cat_name));
	}

	$db->sql_query($sql);

	if (!$cat_id)
	{
		$cat_id = $db->sql_nextid();

		$sql = 'INSERT INTO ' . DL_CAT_TRAF_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'cat_id'			=> $cat_id,
			'cat_traffic_use'	=> 0,
		));

		$db->sql_query($sql);
	}

	// Copy permissions if needed
	if ($perms_copy_from !== -1)
	{
		$copy_from = ($perms_copy_from === 0) ? $cat_parent : $perms_copy_from;

		if ($copy_from !== 0)
		{
			// At first copy the general permissions for all users
			$sql = 'SELECT cat_name, auth_view, auth_dl, auth_up, auth_mod, auth_cread, auth_cpost FROM ' . DL_CAT_TABLE . '
				WHERE id = ' . (int) $copy_from;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);

			$auth_view	= $row['auth_view'];
			$auth_dl	= $row['auth_dl'];
			$auth_up	= $row['auth_up'];
			$auth_mod	= $row['auth_mod'];
			$auth_cread	= $row['auth_cread'];
			$auth_cpost	= $row['auth_cpost'];
			$source_cat	= $row['cat_name'];

			$db->sql_freeresult($result);

			$sql = 'SELECT cat_name FROM ' . DL_CAT_TABLE . '
				WHERE id = ' . (int) $cat_id;
			$result = $db->sql_query($sql);
			$dest_cat = $db->sql_fetchfield('cat_name');
			$db->sql_freeresult($result);

			$sql = 'UPDATE ' . DL_CAT_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
				'auth_view'		=> $auth_view,
				'auth_dl'		=> $auth_dl,
				'auth_up'		=> $auth_up,
				'auth_mod'		=> $auth_mod,
				'auth_cread'	=> $auth_cread,
				'auth_cpost'	=> $auth_cpost)) . ' WHERE id = ' . (int) $cat_id;
			$db->sql_query($sql);

			// And now copy all permissions for usergroups
			$sql = 'SELECT * FROM ' . DL_AUTH_TABLE . '
				WHERE cat_id = ' . (int) $copy_from;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$group_id	= $row['group_id'];
				$auth_view	= $row['auth_view'];
				$auth_dl	= $row['auth_dl'];
				$auth_up	= $row['auth_up'];
				$auth_mod	= $row['auth_mod'];

				$sql = 'INSERT INTO ' . DL_AUTH_TABLE . ' ' . $db->sql_build_array('INSERT', array(
					'cat_id'	=> $cat_id,
					'group_id'	=> $group_id,
					'auth_view'	=> $auth_view,
					'auth_dl'	=> $auth_dl,
					'auth_up'	=> $auth_up,
					'auth_mod'	=> $auth_mod));
				$db->sql_query($sql);
			}

			$db->sql_freeresult($result);

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_CAT_PERM_COPY', false, array($source_cat, $dest_cat));
		}
	}

	// Purge the categories cache
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_cats.' . $phpEx);
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_auth.' . $phpEx);

	$basic_link	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

	$message .= "<br /><br />" . $language->lang('CLICK_RETURN_CATEGORYADMIN', '<a href="' . $basic_link . '">', '</a>') . adm_back_link($this->u_action);

	trigger_error($message);
}
else if($action == 'delete' && $cat_id && !\oxpus\dlext\phpbb\classes\ dl_main::get_sublevel_count($cat_id))
{
	if ($confirm)
	{
		if (!check_form_key('dl_adm_cats'))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
		}

		if( $new_cat_id <= 0 )
		{
			$sql = 'SELECT dl_id, ver_real_file FROM ' . DL_VERSIONS_TABLE;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$real_ver_file[$row['dl_id']][] = $row['ver_real_file'];
			}

			$db->sql_freeresult($result);

			$sql = 'SELECT c.cat_name, c.path, d.real_file, d.id AS df_id FROM ' . DL_CAT_TABLE . ' c, ' . DOWNLOADS_TABLE . ' d
				WHERE d.cat = c.id
					AND c.id = ' . (int) $cat_id . '
					AND d.extern = 0';
			$result = $db->sql_query($sql);

			$dl_ids = array();

			while ($row = $db->sql_fetchrow($result))
			{
				$df_id = $row['df_id'];
				$dl_ids[] = $df_id;
				$path = $row['path'];
				$real_file = $row['real_file'];

				if (!$new_cat_id)
				{
					@unlink(DL_EXT_FILES_FOLDER . $path . $real_file);

					if (isset($real_ver_file[$df_id]))
					{
						for ($i = 0; $i < sizeof($real_ver_file[$df_id]); $i++)
						{
							@unlink(DL_EXT_FILES_FOLDER . $path . $real_ver_file[$df_id][$i]);
						}
					}
				}
			}

			$db->sql_freeresult($result);

			$sql = 'DELETE FROM ' . DOWNLOADS_TABLE . '
				WHERE cat = ' . (int) $cat_id;
			$db->sql_query($sql);

			if (sizeof($dl_ids))
			{
				$sql = 'DELETE FROM ' . DL_VERSIONS_TABLE . '
					WHERE ' . $db->sql_in_set('dl_id', $dl_ids);
				$db->sql_query($sql);
			}
		}

		if ($new_cat_id > 0)
		{
			$sql = 'UPDATE ' . DOWNLOADS_TABLE . '
				SET cat = ' . (int) $new_cat_id . '
				WHERE cat = ' . (int) $cat_id;
			$db->sql_query($sql);

			$sql = 'UPDATE ' . DL_STATS_TABLE . '
				SET cat_id = ' . (int) $new_cat_id . '
				WHERE cat_id = ' . (int) $cat_id;
			$db->sql_query($sql);

			$sql = 'UPDATE ' . DL_COMMENTS_TABLE . '
				SET cat_id = ' . (int) $new_cat_id . '
				WHERE cat_id = ' . (int) $cat_id;
			$db->sql_query($sql);
		}
		else
		{
			$sql = 'DELETE FROM ' . DL_STATS_TABLE . '
				WHERE cat_id = ' . (int) $cat_id;
			$db->sql_query($sql);
		}

		$sql = 'DELETE FROM ' . DL_CAT_TABLE . '
			WHERE id = ' . (int) $cat_id;
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . DL_CAT_TRAF_TABLE . '
			WHERE cat_id = ' . (int) $cat_id;
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . DL_COMMENTS_TABLE . '
			WHERE cat_id = ' . (int) $cat_id;
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . DL_AUTH_TABLE . '
			WHERE cat_id = ' . (int) $cat_id;
		$db->sql_query($sql);

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_CAT_DEL', false, array($log_cat_name));

		// Purge the categories cache
		@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_cats.' . $phpEx);
		@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_auth.' . $phpEx);
		@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_file_preset.' . $phpEx);
		@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_cat_counts.' . $phpEx);

		$basic_link	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

		$message = $language->lang('DL_CATEGORY_REMOVED') . "<br /><br />" . $language->lang('CLICK_RETURN_CATEGORYADMIN', '<a href="' . $basic_link . '">', '</a>') . adm_back_link($this->u_action);

		trigger_error($message);
	}
	else
	{
		$cat_name = $index[$cat_id]['cat_name'];
		$cat_name = str_replace('&nbsp;&nbsp;|___&nbsp;', '', $cat_name);

		$s_switch_cat = '<select name="new_cat_id">';
		$s_switch_cat .= '<option value="0">'.$language->lang('DL_DELETE_CAT_ONLY').'</option>';
		$s_switch_cat .= '<option value="-1" selected="selected">'.$language->lang('DL_DELETE_CAT_AND_FILES').'</option>';
		$s_switch_cat .= '<option value="---">----------------------------------------</option>';
		$s_switch_cat .= \oxpus\dlext\phpbb\classes\ dl_extra::dl_dropdown(0, 0, $cat_id, 'auth_move');
		$s_switch_cat .= '</select>';

		$template->set_filenames(array(
			'confirm_body' => 'dl_confirm_body.html')
		);

		$s_hidden_fields = array(
			'cat_id' => $cat_id,
			'action' => 'delete',
			'confirm' => 1
		);

		add_form_key('dl_adm_cats');

		$basic_link	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

		$template->assign_vars(array(
			'MESSAGE_TITLE' => $language->lang('INFORMATION'),
			'MESSAGE_TEXT' => $language->lang('DL_CONFIRM_CAT_DELETE', $cat_name),

			'L_SWITCH_CAT' => $language->lang('DL_DELETE_CAT_CONFIRM'),
			'S_SWITCH_CAT' => $s_switch_cat,

			'S_CONFIRM_ACTION' => $basic_link,
			'S_HIDDEN_FIELDS' => build_hidden_fields($s_hidden_fields))
		);

		$template->assign_var('S_CHOOSE_NEW_CAT', true);

		$template->assign_var('S_DL_CONFIRM', true);

		$template->assign_display('confirm_body');

		$dl_template_in_use = true;
	}
}
else if($action == 'delete_stats')
{
	$dl_confirm_stat_delete = 0;

	if($cat_id == '-1')
	{
		$sql = 'DELETE FROM ' . DL_STATS_TABLE;
		$db->sql_query($sql);

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_DEL_ALL_STATS');
	}
	else
	{
		if(!$confirm)
		{
			$cat_name = $index[$cat_id]['cat_name'];
			$cat_name = str_replace('&nbsp;&nbsp;|___&nbsp;', '', $cat_name);

			$template->set_filenames(array(
				'confirm_body' => 'dl_confirm_body.html')
			);

			$s_hidden_fields = array(
				'cat_id' => $cat_id,
				'action' => 'delete_stats',
				'confirm' => 1
			);

			add_form_key('dl_adm_cats');

			$basic_link	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

			$template->assign_vars(array(
				'MESSAGE_TITLE' => $language->lang('INFORMATION'),
				'MESSAGE_TEXT' => ($cat_id == 'all') ? $language->lang('DL_CONFIRM_ALL_STATS_DELETE') : $language->lang('DL_CONFIRM_CAT_STATS_DELETE', $cat_name),

				'S_CONFIRM_ACTION' => $basic_link,
				'S_HIDDEN_FIELDS' => build_hidden_fields($s_hidden_fields))
			);

			$template->assign_var('S_DL_CONFIRM', true);

			$template->assign_display('confirm_body');

			$dl_template_in_use = true;
			$dl_confirm_stat_delete = true;
		}
		else
		{
			if (!check_form_key('dl_adm_cats'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			if ($cat_id)
			{
				$sql = 'DELETE FROM ' . DL_STATS_TABLE . '
					WHERE cat_id = ' . (int) $cat_id;
				$db->sql_query($sql);

				$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_DEL_CAT_STATS', false, array($log_cat_name));
			}
		}
	}

	if (!$dl_confirm_stat_delete)
	{
		$basic_link	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

		redirect($basic_link);
	}
}
else if($action == 'delete_comments')
{
	$dl_confirm_comm_delete = 0;

	if($cat_id == '-1')
	{
		$sql = 'DELETE FROM ' . DL_COMMENTS_TABLE;
		$db->sql_query($sql);

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_DEL_ALL_COM');
	}
	else if(!$confirm)
	{
		$cat_name = $index[$cat_id]['cat_name'];
		$cat_name = str_replace('&nbsp;&nbsp;|___&nbsp;', '', $cat_name);

		$template->set_filenames(array(
			'confirm_body' => 'dl_confirm_body.html')
		);

		$s_hidden_fields = array(
			'cat_id' => $cat_id,
			'action' => 'delete_comments',
			'confirm' => 1
		);

		add_form_key('dl_adm_cats');

		$basic_link	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

		$template->assign_vars(array(
			'MESSAGE_TITLE' => $language->lang('INFORMATION'),
			'MESSAGE_TEXT' => ($cat_id == 'all') ? $language->lang('DL_CONFIRM_ALL_COMMENTS_DELETE') : $language->lang('DL_CONFIRM_CAT_COMMENTS_DELETE', $cat_name),

			'S_CONFIRM_ACTION' => $basic_link,
			'S_HIDDEN_FIELDS' => build_hidden_fields($s_hidden_fields))
		);

		$template->assign_var('S_DL_CONFIRM', true);

		$template->assign_display('confirm_body');

		$dl_template_in_use = true;
		$dl_confirm_comm_delete = true;
	}
	else
	{
		if (!check_form_key('dl_adm_cats'))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
		}

		if ($cat_id)
		{
			$sql = 'DELETE FROM ' . DL_COMMENTS_TABLE . '
				WHERE cat_id = ' . (int) $cat_id;
			$db->sql_query($sql);

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_DEL_CAT_COM', false, array($log_cat_name));
		}
	}

	if (!$dl_confirm_comm_delete)
	{
		$basic_link	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;
		redirect($basic_link);
	}
}
else if($action == 'category_order')
{
	$sql = 'SELECT sort FROM ' . DL_CAT_TABLE . '
		WHERE id = ' . (int) $cat_id;
	$result = $db->sql_query($sql);
	$sql_move = $db->sql_fetchfield('sort');
	$db->sql_freeresult($result);

	if ($move)
	{
		$sql_move += 15;
	}
	else
	{
		$sql_move -= 15;
	}

	$sql = 'UPDATE ' . DL_CAT_TABLE . '
		SET sort = ' . (int) $sql_move . '
		WHERE id = ' . (int) $cat_id;
	$db->sql_query($sql);

	$par_cat = $index[$cat_id]['parent'];

	$sql = 'SELECT id FROM ' . DL_CAT_TABLE . '
		WHERE parent = ' .(int) $par_cat . '
		ORDER BY sort';
	$result = $db->sql_query($sql);

	$i = 10;

	while($row = $db->sql_fetchrow($result))
	{
		$sql_move = 'UPDATE ' . DL_CAT_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
			'sort' => $i)) . ' WHERE id = ' . (int) $row['id'];
		$db->sql_query($sql_move);

		$i += 10;
	}

	$db->sql_freeresult($result);

	$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_CAT_MOVE', false, array($log_cat_name));

	// Purge the categories cache
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_cats.' . $phpEx);
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_auth.' . $phpEx);

	$basic_link	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

	redirect($basic_link);
}
else if($action == 'asc_sort')
{
	$sql = 'SELECT id FROM ' . DL_CAT_TABLE . '
		WHERE parent = ' . (int) $cat_id . '
		ORDER BY cat_name ASC';
	$result = $db->sql_query($sql);

	$i = 10;

	while($row = $db->sql_fetchrow($result))
	{
		$sql_move = 'UPDATE ' . DL_CAT_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
				'sort' => $i)) . ' WHERE id = ' . (int) $row['id'];
		$db->sql_query($sql_move);

		$i += 10;
	}

	$db->sql_freeresult($result);

	// Purge the categories cache
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_cats.' . $phpEx);
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_auth.' . $phpEx);

	$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_CAT_SORT_ASC');

	$basic_link	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

	redirect($basic_link);
}

if (!$dl_template_in_use)
{
	$stats_cats = array();
	$comments_cats = array();

	$sql = 'SELECT cat_id, COUNT(dl_id) AS total_stats FROM ' . DL_STATS_TABLE . '
		GROUP BY cat_id';
	$result = $db->sql_query($sql);

	while($row = $db->sql_fetchrow($result))
	{
		$stats_cats[$row['cat_id']] = $row['total_stats'];
	}

	$db->sql_freeresult($result);

	$sql = 'SELECT cat_id, COUNT(dl_id) AS total_comments FROM ' . DL_COMMENTS_TABLE . '
		GROUP BY cat_id';
	$result = $db->sql_query($sql);

	while($row = $db->sql_fetchrow($result))
	{
		$comments_cats[$row['cat_id']] = $row['total_comments'];
	}

	$db->sql_freeresult($result);

	$stats_total = 0;
	$comments_total = 0;

	$basic_link_idx		= $basic_link . '&amp;parent=' . $cat_parent . '&amp;type=';
	$basic_link_open	= $basic_link . '&amp;parent=#CAT#&amp;type=' . $idx_type;
	$basic_link			.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

	foreach (array_keys($index) as $key)
	{
		$cat_id = $key;

		if (($idx_type == 'c' && $index[$cat_id]['parent'] == $cat_parent) || $idx_type == 'f')
		{
			$cat_name = ($idx_type == 'c') ? $index[$cat_id]['cat_name_nav'] : $index[$cat_id]['cat_name'];
			$cat_description = nl2br($index[$cat_id]['description']);
			$cat_icon = $index[$cat_id]['cat_icon'];

			$cat_edit = "{$basic_link}&amp;action=edit&amp;cat_id=$cat_id";

			$cat_sub = \oxpus\dlext\phpbb\classes\ dl_main::get_sublevel_count($cat_id);
			$cat_sub_count = \oxpus\dlext\phpbb\classes\ dl_main::count_sublevel($cat_id);

			if ($cat_sub)
			{
				$cat_delete = '';
			}
			else
			{
				$cat_delete = "{$basic_link}&amp;action=delete&amp;cat_id=$cat_id";
			}

			$dl_move_up = "{$basic_link}&amp;action=category_order&amp;move=0&amp;cat_id=$cat_id";
			$dl_move_down = "{$basic_link}&amp;action=category_order&amp;move=1&amp;cat_id=$cat_id";

			$cat_folder = 'images/icon_folder.gif';
			if ($cat_sub_count)
			{
				$cat_folder = 'images/icon_subfolder.gif';
			}

			if ($cat_sub_count > 1)
			{
				$l_sort_asc = $language->lang('DL_SUB_SORT_ASC');
				$dl_sort_asc = "{$basic_link}&amp;action=asc_sort&amp;cat_id=$cat_id";
			}
			else
			{
				$l_sort_asc = '';
				$dl_sort_asc = '';
			}

			$l_delete_stats = '';
			$l_delete_comments = '';
			$u_delete_stats = '';
			$u_delete_comments = '';

			if (isset($stats_cats[$cat_id]))
			{
				$l_delete_stats = $language->lang('DL_STATS_DELETE');
				$u_delete_stats = "{$basic_link}&amp;action=delete_stats&amp;cat_id=$cat_id";
				$stats_total++;
			}

			if (isset($comments_cats[$cat_id]))
			{
				$l_delete_comments = $language->lang('DL_COMMENTS_DELETE');
				$u_delete_comments = "{$basic_link}&amp;action=delete_comments&amp;cat_id=$cat_id";
				$comments_total++;
			}

			$template->assign_block_vars('categories', array(
				'L_DELETE_STATS'		=> $l_delete_stats,
				'L_DELETE_COMMENTS'		=> $l_delete_comments,
				'L_SORT_ASC'			=> $l_sort_asc,

				'CAT_NAME'				=> $cat_name,
				'CAT_DESCRIPTION'		=> $cat_description,
				'CAT_FOLDER'			=> $cat_folder,
				'CAT_ICON'				=> $cat_icon,

				'U_CAT_EDIT'			=> $cat_edit,
				'U_CAT_DELETE'			=> $cat_delete,
				'U_CATEGORY_MOVE_UP'	=> $dl_move_up,
				'U_CATEGORY_MOVE_DOWN'	=> $dl_move_down,
				'U_CATEGORY_ASC_SORT'	=> $dl_sort_asc,
				'U_DELETE_STATS'		=> $u_delete_stats,
				'U_DELETE_COMMENTS'		=> $u_delete_comments,
				'U_CAT_OPEN'			=> ($cat_sub_count && $idx_type == 'c') ? str_replace('#CAT#', $cat_id, $basic_link_open) : '',
			));
		}

		if ($stats_total)
		{
			$l_delete_stats_all = $language->lang('DL_STATS_DELETE_ALL');
			$u_delete_stats_all = "{$basic_link}&amp;action=delete_stats&amp;cat_id=-1";
			$template->assign_var('S_TOTAL_STATS', true);
		}
		else
		{
			$l_delete_stats_all = '';
			$u_delete_stats_all = '';
		}

		if ($comments_total)
		{
			$l_delete_comments_all = $language->lang('DL_COMMENTS_DELETE_ALL');
			$u_delete_comments_all = "{$basic_link}&amp;action=delete_comments&amp;cat_id=-1";
			$template->assign_var('S_TOTAL_COMMENTS', true);
		}
		else
		{
			$l_delete_comments_all = '';
			$u_delete_comments_all = '';
		}
	}

	$cat_navi = '';
	if ($cat_parent <> 0)
	{
		$tmp_nav = array();
		$cat_navi = \oxpus\dlext\phpbb\classes\ dl_nav::nav($helper, $cat_parent, 'acp', $tmp_nav, $basic_link_open);
	}

	$template->assign_vars(array(
		'L_DELETE_STATS_ALL'	=> $l_delete_stats_all,
		'L_DELETE_COMMENTS_ALL'	=> $l_delete_comments_all,

		'CAT_PATH'				=> (isset($cat_path)) ? $cat_path : '/',
		'CAT_NAME'				=> $cat_name,

		'S_CATEGORY_ACTION'		=> $basic_link,
		'S_IDX_TYPE'			=> $idx_type,
		'S_SORT_MAIN'			=> ($cat_parent == 0) ? true : false,

		'U_SORT_LEVEL_ZERO'		=> "{$basic_link}&amp;action=asc_sort&amp;cat_id=0",
		'U_DELETE_STATS_ALL'	=> $u_delete_stats_all,
		'U_DELETE_COMMENTS_ALL'	=> $u_delete_comments_all,
		'U_IDX_ACTION'			=> $basic_link_idx,
		'U_CAT_NAV'				=> $cat_navi,
	));

	/*
	* show the default page
	*/
	$template->set_filenames(array(
		'categories' => 'dl_cat_body.html')
	);

	$template->assign_var('S_DL_CATEGORIES', true);

	$template->assign_display('categories');
}
