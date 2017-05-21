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

if ($submit)
{
	if (!check_form_key('dl_adm_traffic'))
	{
		trigger_error('FORM_INVALID', E_USER_WARNING);
	}

	switch($action)
	{
		case 'single':

			switch ($x)
			{
				case 'B':
					$traffic_bytes = $user_traffic;
					break;
				case 'KB':
					$traffic_bytes = floor($user_traffic * 1024);
					break;
				case 'MB':
					$traffic_bytes = floor($user_traffic * 1048576);
					break;
				case 'GB':
					$traffic_bytes = floor($user_traffic * 1073741824);
					break;
				default:
					$traffic_bytes = 0;
			}

			if ($traffic_bytes)
			{
				$username = utf8_clean_string($username);

				$sql = 'SELECT user_traffic, user_id FROM ' . USERS_TABLE . "
					WHERE username_clean = '" . $db->sql_escape($username) . "'";
				$result			= $db->sql_query($sql);
				$row			= $db->sql_fetchrow($result);
				$user_id		= $row['user_id'];
				$user_traffic	= $row['user_traffic'];
				$db->sql_freeresult($result);

				if (!$user_id)
				{
					trigger_error($language->lang('USERNAME') . ' ' . $username . '<br /><br />' . $language->lang('NO_USER') . adm_back_link($this->u_action));
				}

				if ($func == 'add')
				{
					$user_traffic += $traffic_bytes;

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_USER_TR_ADD', false, array($username, $user_traffic, $x));
				}
				else
				{
					$user_traffic = $traffic_bytes;

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_USER_TR_SET', false, array($username, $user_traffic, $x));
				}

				$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
					'user_traffic' => $user_traffic)) . ' WHERE user_id = ' . (int) $user_id;
				$db->sql_query($sql);

				$message = $language->lang('DL_USER_AUTO_TRAFFIC_USER') . '<br /><br />' . $language->lang('CLICK_RETURN_USER_TRAFFIC_ADMIN', '<a href="' . $basic_link . '">', '</a>') . adm_back_link($this->u_action);

				trigger_error($message);
			}

			break;

		case 'all':

			switch ($y)
			{
				case 'B':
					$traffic_bytes = $all_traffic;
					break;
				case 'KB':
					$traffic_bytes = floor($all_traffic * 1024);
					break;
				case 'MB':
					$traffic_bytes = floor($all_traffic * 1048576);
					break;
				case 'GB':
					$traffic_bytes = floor($all_traffic * 1073741824);
					break;
				default:
					$traffic_bytes = 0;
			}

			if ($traffic_bytes)
			{
				if ($func == 'add')
				{
					$sql = 'SELECT user_id, user_traffic FROM ' . USERS_TABLE . '
						WHERE user_id <> ' . ANONYMOUS;
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$user_id = $row['user_id'];
						$user_traffic = $row['user_traffic'] + $traffic_bytes;

						$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
							'user_traffic' => $user_traffic)) . ' WHERE user_id = ' . (int) $user_id;
						$db->sql_query($sql);
					}

					$db->sql_freeresult($result);

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_ALL_TR_ADD', false, array($all_traffic, $y));
				}
				if ($func == 'set')
				{
					$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
						'user_traffic' => $traffic_bytes)) . ' WHERE user_id <> ' . ANONYMOUS;
					$db->sql_query($sql);

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_ALL_TR_SET', false, array($all_traffic, $y));
				}

				$message = $language->lang('DL_USER_AUTO_TRAFFIC_USER') . '<br /><br />' . $language->lang('CLICK_RETURN_USER_TRAFFIC_ADMIN', '<a href="' . $basic_link . '">', '</a>') . adm_back_link($this->u_action);

				trigger_error($message);
			}

			break;

		case 'group':

			switch ($z)
			{
				case 'B':
					$traffic_bytes = $group_traffic;
					break;
				case 'KB':
					$traffic_bytes = floor($group_traffic * 1024);
					break;
				case 'MB':
					$traffic_bytes = floor($group_traffic * 1048576);
					break;
				case 'GB':
					$traffic_bytes = floor($group_traffic * 1073741824);
					break;
				default:
					$traffic_bytes = 0;
			}

			if ($traffic_bytes)
			{
				$sql = 'SELECT group_type, group_name FROM ' . GROUPS_TABLE . '
					WHERE group_id = ' . (int) $group_id;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $language->lang('G_' . $row['group_name']) : $row['group_name'];

				$sql = 'SELECT u.user_traffic, u.user_id FROM ' . USER_GROUP_TABLE . ' ug, ' . USERS_TABLE . ' u
					WHERE ug.user_id = u.user_id
						AND ug.user_pending <> ' . true . '
						AND ug.group_id = ' . (int) $group_id;
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$user_id		= $row['user_id'];
					$user_traffic	= $row['user_traffic'];

					if ($func == 'add')
					{
						$user_traffic += $traffic_bytes;
					}
					else
					{
						$user_traffic = $traffic_bytes;
					}

					$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
						'user_traffic' => $user_traffic)) . ' WHERE user_id = ' . (int) $user_id;
					$db->sql_query($sql);
				}

				$db->sql_freeresult($result);

				if ($func == 'add')
				{
					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_GRP_TR_ADD', false, array($group_name, $group_traffic, $z));
				}
				else
				{
					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_GRP_TR_SET', false, array($group_name, $group_traffic, $z));
				}

				$message = $language->lang('DL_USER_AUTO_TRAFFIC_USER') . '<br /><br />' . $language->lang('CLICK_RETURN_USERGROUP_TRAFFIC_ADMIN', '<a href="' . $basic_link . '">', '</a>') . adm_back_link($this->u_action);

				trigger_error($message);
			}

			break;

		case 'auto':

			$sql = 'SELECT group_type, group_name, group_id FROM ' . GROUPS_TABLE . '
				ORDER BY group_name';
			$result = $db->sql_query($sql);

			while($row = $db->sql_fetchrow($result))
			{
				$group_id	= $row['group_id'];
				$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $language->lang('G_' . $row['group_name']) : $row['group_name'];

				$group_dl_auto_traffic	= $request->variable('group_dl_auto_traffic', array(0));
				$data_group_range		= $request->variable('data_group_range', array(''));

				if ($data_group_range[$group_id] == 'B')
				{
					$traffic = $group_dl_auto_traffic[$group_id];
				}
				else if ($data_group_range[$group_id] == 'KB')
				{
					$traffic = floor($group_dl_auto_traffic[$group_id] * 1024);
				}
				else if ($data_group_range[$group_id] == 'MB')
				{
					$traffic = floor($group_dl_auto_traffic[$group_id] * 1048576);
				}
				else if ($data_group_range[$group_id] == 'GB')
				{
					$traffic = floor($group_dl_auto_traffic[$group_id] * 1073741824);
				}
				else
				{
					$traffic = 0;
				}

				$sql = 'UPDATE ' . GROUPS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
					'group_dl_auto_traffic' => $traffic)) . ' WHERE group_id = ' . (int) $group_id;
				$db->sql_query($sql);

				$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_AUTO_TR_GRP', false, array($group_name, $group_dl_auto_traffic[$group_id], $data_group_range[$group_id]));
			}

			$user_dl_auto_traffic	= $request->variable('user_dl_auto_traffic', 0);
			$data_user_range		= $request->variable('data_user_range', '');

			if ($data_user_range == 'B')
			{
				$traffic = $user_dl_auto_traffic;
			}
			else if ($data_user_range == 'KB')
			{
				$traffic = floor($user_dl_auto_traffic * 1024);
			}
			else if ($data_user_range == 'MB')
			{
				$traffic = floor($user_dl_auto_traffic * 1048576);
			}
			else if ($data_user_range == 'GB')
			{
				$traffic = floor($user_dl_auto_traffic * 1073741824);
			}
			else
			{
				$traffic = 0;
			}

			$config->set('dl_user_dl_auto_traffic', $traffic, false);
			$cache->purge('config');

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_AUTO_TR_USER', false, array($user_dl_auto_traffic, $data_user_range));

			$message = $language->lang('DL_USER_AUTO_TRAFFIC_USER') . '<br /><br />' . $language->lang('CLICK_RETURN_USERGROUP_TRAFFIC_ADMIN', '<a href="' . $basic_link . '">', '</a>') . adm_back_link($this->u_action);

			trigger_error($message);

			break;
	}
}

$template->set_filenames(array(
	'traffic' => 'dl_traffic_body.html')
);

add_form_key('dl_adm_traffic');

$s_select_datasize = '<option value="B">' . $language->lang('DL_BYTES_LONG') . '</option>';
$s_select_datasize .= '<option value="KB">' . $language->lang('DL_KB') . '</option>';
$s_select_datasize .= '<option value="MB">' . $language->lang('DL_MB') . '</option>';
$s_select_datasize .= '<option value="GB">' . $language->lang('DL_GB') . '</option>';
$s_select_datasize .= '</select>';

$sql = 'SELECT group_id, group_name, group_dl_auto_traffic, group_type FROM ' . GROUPS_TABLE . '
	ORDER BY group_name';
$result = $db->sql_query($sql);
$total_groups = $db->sql_affectedrows($result);

if ($total_groups)
{
	$template->assign_var('S_GROUP_BLOCK', true);

	$s_select_list = '<select name="g">';

	while ($row = $db->sql_fetchrow($result))
	{
		$group_dl_auto_traffic = ($row['group_dl_auto_traffic']) ? $row['group_dl_auto_traffic'] : 0;
		$data_range_select = 'B';

		if ($group_dl_auto_traffic > 1073741823)
		{
			$group_traffic = number_format($group_dl_auto_traffic / 1073741824, 2);
			$data_range_select = 'GB';
		}
		if ($group_dl_auto_traffic < 1073741824)
		{
			$group_traffic = number_format($group_dl_auto_traffic / 1048576, 2);
			$data_range_select = 'MB';
		}
		if ($group_dl_auto_traffic < 1048576)
		{
			$group_traffic = number_format($group_dl_auto_traffic / 1024, 2);
			$data_range_select = 'KB';
		}
		if ($group_dl_auto_traffic < 1024)
		{
			$group_traffic = number_format($group_dl_auto_traffic, 2);
			$data_range_select = 'B';
		}

		$s_group_data_range = str_replace('value="' . $data_range_select . '">', 'value="' . $data_range_select . '" selected="selected">', $s_select_datasize);
		$s_group_data_range = '<select name="data_group_range[' . $row['group_id'] . ']">' . $s_group_data_range;

		$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $language->lang('G_' . $row['group_name']) : $row['group_name'];

		$template->assign_block_vars('group_row',array(
			'GROUP_ID'				=> $row['group_id'],
			'GROUP_NAME'			=> $group_name,
			'GROUP_DL_AUTO_TRAFFIC'	=> $group_traffic,

			'S_GROUP_DATA_RANGE'	=> $s_group_data_range)
		);

		$s_select_list .= '<option value="' . $row['group_id'] . '">' . $group_name . '</option>';
	}

	$s_select_list .= '</select>';
}
$db->sql_freeresult($result);

$user_dl_auto_traffic = $config['dl_user_dl_auto_traffic'];

if ($user_dl_auto_traffic > 1073741823)
{
	$user_traffic = number_format($user_dl_auto_traffic / 1073741824, 2);
	$data_range_select = 'GB';
}
if ($user_dl_auto_traffic < 1073741824)
{
	$user_traffic = number_format($user_dl_auto_traffic / 1048576, 2);
	$data_range_select = 'MB';
}
if ($user_dl_auto_traffic < 1048576)
{
	$user_traffic = number_format($user_dl_auto_traffic / 1024, 2);
	$data_range_select = 'KB';
}
if ($user_dl_auto_traffic < 1024)
{
	$user_traffic = $user_dl_auto_traffic;
	$data_range_select = 'B';
}

$s_user_data_range	= str_replace('value="' . $data_range_select . '">', 'value="' . $data_range_select . '" selected="selected">', $s_select_datasize);
$s_user_range		= str_replace('value="KB">', 'value="KB" selected="selected">', $s_select_datasize);

$s_user_data_range		= '<select name="data_user_range">' . $s_user_data_range;
$s_user_single_range	= '<select name="x">' . $s_user_range;
$s_user_all_range		= '<select name="y">' . $s_user_range;
$s_user_group_range		= '<select name="z">' . $s_user_range;

$u_user_select = append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=searchuser&amp;form=user_traffic&amp;field=username&amp;select_single=true");

$template->assign_vars(array(
	'USER_DL_AUTO_TRAFFIC'		=> $user_traffic,

	'S_GROUP_SELECT'			=> $s_select_list,
	'S_USER_DATA_RANGE'			=> $s_user_data_range,
	'S_USER_SINGLE_RANGE'		=> $s_user_single_range,
	'S_USER_ALL_RANGE'			=> $s_user_all_range,
	'S_USER_GROUP_RANGE'		=> $s_user_group_range,

	'S_PROFILE_ACTION_ALL'		=> $basic_link . '&amp;action=all',
	'S_PROFILE_ACTION_USER'		=> $basic_link . '&amp;action=single',
	'S_PROFILE_ACTION_GROUP'	=> $basic_link . '&amp;action=group',
	'S_CONFIG_ACTION'			=> $basic_link . '&amp;action=auto',

	'U_FIND_USERNAME'			=> $u_user_select,
));

$acl_cat_names = array(
	0 => $language->lang('DL_ACP_TRAF_AUTO'),
	1 => $language->lang('DL_ACP_TRAF_ALL'),
	2 => $language->lang('DL_ACP_TRAF_USER'),
	3 => $language->lang('DL_ACP_TRAF_GRP'),
);

for ($i = 0; $i < sizeof($acl_cat_names); $i++)
{
	$template->assign_block_vars('category', array('CAT_NAME' => $acl_cat_names[$i]));
}

$template->assign_var('S_DL_TRAFFIC', true);

$template->assign_display('traffic');
