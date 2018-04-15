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

$tmp_m1 = $request->variable('edit_banlist', '');
$tmp_m2 = $request->variable('delete_banlist', '');

$action = ($tmp_m1) ? 'edit' : $action;
$action = ($tmp_m2) ? 'delete' : $action;

if($action == 'add')
{
	$ban_id		= $request->variable('ban_id', 0);
	$user_id	= $request->variable('user_id', 0);
	$user_ip	= $request->variable('user_ip', '');
	$user_agent	= $request->variable('user_agent', '');
	$username	= $request->variable('username', '', true);
	$guests		= $request->variable('guests', 0);

	$user_ip = ($user_ip != '') ? $user_ip : '';

	if (!check_form_key('dl_adm_ban'))
	{
		trigger_error('FORM_INVALID', E_USER_WARNING);
	}

	if ($ban_id)
	{
		$sql = 'UPDATE ' . DL_BANLIST_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
			'user_id'		=> $user_id,
			'user_ip'		=> $user_ip,
			'user_agent'	=> $user_agent,
			'username'		=> $username,
			'guests'		=> $guests)) . ' WHERE ban_id = ' . (int) $ban_id;

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_BAN_EDIT', false, array($user_id . ' ~ ' . $username, $user_ip, $user_agent, $guests));
	}
	else
	{
		$sql = 'INSERT INTO ' . DL_BANLIST_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'user_id'		=> $user_id,
			'user_ip'		=> $user_ip,
			'user_agent'	=> $user_agent,
			'username'		=> $username,
			'guests'		=> $guests));

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_BAN_ADD', false, array($user_id . ' ~ ' . $username, $user_ip, $user_agent, $guests));
	}

	$db->sql_query($sql);

	$action = '';
}
else if($action == 'delete')
{
	$ban_id = $request->variable('ban_id', array(0));

	if (!$confirm)
	{
		$template->set_filenames(array(
			'confirm_body' => 'dl_confirm_body.html')
		);

		$s_hidden_fields = array('action' => 'delete');

		for ($i = 0; $i < sizeof($ban_id); $i++)
		{
			$s_hidden_fields = array_merge($s_hidden_fields, array('ban_id[' . $i . ']' => intval($ban_id[$i])));
		}

		add_form_key('dl_adm_ban');

		$template->assign_vars(array(
			'MESSAGE_TITLE' => $language->lang('INFORMATION'),
			'MESSAGE_TEXT' => $language->lang('DL_CONFIRM_DELETE_BAN_VALUES'),

			'S_CONFIRM_ACTION' => $basic_link,
			'S_HIDDEN_FIELDS' => build_hidden_fields($s_hidden_fields))
		);

		$template->assign_var('S_DL_CONFIRM', true);

		$template->assign_display('confirm_body');

		$confirm_delete = true;
	}
	else
	{
		if (!check_form_key('dl_adm_ban'))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
		}

		$sql_ext_in = array();

		for ($i = 0; $i < sizeof($ban_id); $i++)
		{
			$sql_ext_in[] = intval($ban_id[$i]);
		}

		if (sizeof($sql_ext_in))
		{
			$sql = 'DELETE FROM ' . DL_BANLIST_TABLE . '
				WHERE ' . $db->sql_in_set('ban_id', $sql_ext_in);
			$db->sql_query($sql);

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_BAN_DEL', false, array(implode(', ', $sql_ext_in)));

			$message = $language->lang('DL_BANLIST_UPDATED') . "<br /><br />" . $language->lang('CLICK_RETURN_BANLISTADMIN', '<a href="' . $basic_link . '">', '</a>') . adm_back_link($this->u_action);

			trigger_error($message);
		}

		$action = '';
	}
}

if ($action == '' || $action == 'edit')
{
	$template->set_filenames(array(
		'banlist' => 'dl_banlist_body.html')
	);

	$sql = 'SELECT b.*, u.username AS user2 FROM ' . DL_BANLIST_TABLE . ' b
		LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = b.user_id
		ORDER BY ban_id';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$ban_id = $row['ban_id'];
		$user2 = $row['user2'];
		$user_id = $row['user_id'];
		$user_ip = $row['user_ip'];
		$user_agent = $row['user_agent'];
		$username = $row['username'];
		$guests = ($row['guests']) ? $language->lang('YES') : $language->lang('NO');

		$template->assign_block_vars('banlist_row', array(
			'BAN_ID'		=> $ban_id,
			'USER_ID'		=> $user_id . (($user2) ? ' &raquo; ' . $user2 : ''),
			'USER_IP'		=> ($user_ip != '') ? $user_ip : '',
			'USER_AGENT'	=> $user_agent,
			'USERNAME'		=> $username,
			'GUESTS'		=> $guests)
		);
	}

	$s_ban_list = ($db->sql_affectedrows($result)) ? true : false;

	$db->sql_freeresult($result);

	$ban_id = $request->variable('ban_id', array(0));
	$banlist_id = (isset($ban_id[0])) ? intval($ban_id[0]) : 0;

	$s_hidden_fields = array('action' => 'add');

	if ($action == 'edit' && $banlist_id)
	{
		$sql = 'SELECT * FROM ' . DL_BANLIST_TABLE . '
			WHERE ban_id = ' . (int) $banlist_id;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$ban_id		= $row['ban_id'];
			$user_id	= $row['user_id'];
			$user_ip	= ($row['user_ip'] != '') ? $row['user_ip'] : '';
			$user_agent	= $row['user_agent'];
			$username	= $row['username'];
			$guests		= $row['guests'];

			$s_hidden_fields	= array_merge($s_hidden_fields, array('ban_id' => $ban_id));
		}
		$db->sql_freeresult($result);
	}
	else
	{
		$ban_id		= '';
		$user_id	= '';
		$user_ip	= '';
		$user_agent	= '';
		$username	= '';
		$guests		= '';
	}

	add_form_key('dl_adm_ban');

	$template->assign_vars(array(
		'BANLIST_ACTION'		=> ($action == 'edit') ? $language->lang('EDIT') : $language->lang('ADD'),

		'DL_USER_ID'	=> $user_id,
		'DL_USER_IP'	=> $user_ip,
		'DL_USER_AGENT'	=> $user_agent,
		'DL_USERNAME'	=> $username,
		'CHECKED_YES'	=> ($guests) ? 'checked="checked"' : '',
		'CHECKED_NO'	=> (!$guests) ? 'checked="checked"' : '',

		'S_BAN_LIST'			=> $s_ban_list,
		'S_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields),
		'S_DOWNLOADS_ACTION'	=> $basic_link,

		'U_BACK'				=> ($action) ? $this->u_action : '',
	));
}

if (!isset($confirm_delete))
{
	$template->assign_var('S_DL_BANLIST', true);

	$template->assign_display('banlist');
}
