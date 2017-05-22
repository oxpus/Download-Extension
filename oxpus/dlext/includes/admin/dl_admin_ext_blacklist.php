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

if($action == 'add')
{
	$extension = $request->variable('extension', '', true);

	if (!check_form_key('dl_adm_ext'))
	{
		trigger_error('FORM_INVALID', E_USER_WARNING);
	}

	if ($extension)
	{
		$sql = 'SELECT * FROM ' . DL_EXT_BLACKLIST . "
			WHERE extention = '" . $db->sql_escape($extension) . "'";
		$result = $db->sql_query($sql);
		$ext_exist = $db->sql_affectedrows($result);
		$db->sql_freeresult($result);

		if (!$ext_exist)
		{
			$sql = 'INSERT INTO ' . DL_EXT_BLACKLIST . ' ' . $db->sql_build_array('INSERT', array(
				'extention' => $extension));
			$db->sql_query($sql);

			// Purge the blacklist cache
			@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_black.' . $phpEx);

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_EXT_ADD', false, array($extension));
		}
	}

	$action = '';
}

if($action == 'delete')
{
	if (!check_form_key('dl_adm_ext'))
	{
		trigger_error('FORM_INVALID', E_USER_WARNING);
	}

	$extension = $request->variable('extension', array(''), true);

	$confirm_delete = false;

	if (!$confirm)
	{
		$template->set_filenames(array(
			'confirm_body' => 'dl_confirm_body.html')
		);

		$s_hidden_fields = array('action' => 'delete');

		for ($i = 0; $i < sizeof($extension); $i++)
		{
			$s_hidden_fields = array_merge($s_hidden_fields, array('extension[' . $i . ']' => $extension[$i]));
		}

		add_form_key('dl_adm_ext');

		$template->assign_vars(array(
			'MESSAGE_TITLE' => $language->lang('INFORMATION'),
			'MESSAGE_TEXT' => (sizeof($extension) == 1) ? $language->lang('DL_CONFIRM_DELETE_EXTENSION', $extension[0]) : $language->lang('DL_CONFIRM_DELETE_EXTENSIONS', implode(', ', $extension)),

			'L_DELETE_FILE_TOO' => (sizeof($extension) == 1) ? $language->lang('DL_DELETE_EXTENSION_CONFIRM') : $language->lang('DL_DELETE_EXTENSIONS_CONFIRM'),

			'S_CONFIRM_ACTION' => $basic_link,
			'S_HIDDEN_FIELDS' => build_hidden_fields($s_hidden_fields))
		);

		$template->assign_var('S_DL_CONFIRM', true);

		$template->assign_display('confirm_body');

		$confirm_delete = true;
	}
	else
	{
		if (!check_form_key('dl_adm_ext'))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
		}

		$sql_ext_in = array();

		for ($i = 0; $i < sizeof($extension); $i++)
		{
			$sql_ext_in[] = $extension[$i];
		}

		if (sizeof($sql_ext_in))
		{
			$sql = 'DELETE FROM ' . DL_EXT_BLACKLIST . '
				WHERE ' . $db->sql_in_set('extention', $sql_ext_in);
			$db->sql_query($sql);

			// Purge the blacklist cache
			@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_black.' . $phpEx);

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_EXT_DEL', false, array(implode(', ', $sql_ext_in)));

			$message = ((sizeof($extension) == 1) ? $language->lang('EXTENSION_REMOVED') : $language->lang('EXTENSIONS_REMOVED')) . "<br /><br />" . $language->lang('CLICK_RETURN_EXTBLACKLISTADMIN', '<a href="' . $basic_link . '">', '</a>') . adm_back_link($this->u_action);

			trigger_error($message);
		}

		$action = '';
	}
}

if ($action == '')
{
	$template->set_filenames(array(
		'ext_bl' => 'dl_ext_blacklist_body.html')
	);

	$sql = 'SELECT extention FROM ' . DL_EXT_BLACKLIST . '
		ORDER BY extention';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$template->assign_block_vars('extension_row', array(
			'EXTENSION' => $row['extention'])
		);
	}

	$ext_yes = ($db->sql_affectedrows($result)) ? true : false;

	$db->sql_freeresult($result);

	add_form_key('dl_adm_ext');

	$template->assign_vars(array(
		'S_EXT_YES'				=> $ext_yes,
		'S_DOWNLOADS_ACTION'	=> $basic_link)
	);
}

if (!isset($confirm_delete))
{
	$template->assign_var('S_DL_BLACKLIST', true);

	$template->assign_display('ext_bl');
}
