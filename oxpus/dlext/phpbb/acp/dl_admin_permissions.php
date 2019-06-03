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

$index = array();
$index = \oxpus\dlext\phpbb\classes\dl_main::full_index();

if (!sizeof($index))
{
	redirect($this->u_action . '&amp;mode=categories');
}

$s_presel_cats		= $request->variable('cat_select', array(0));
$s_presel_groups	= $request->variable('group_select', array(0));
$view_perm			= $request->variable('view_perm', 0);
$cancel				= $request->variable('cancel', '');

$cat_id = (isset($s_presel_cats[0])) ? $s_presel_cats[0] : array();

if ($view_perm > 1)
{
	$cat_list = '';
	$s_hidden_fields = array(
		'view_perm'	=> $view_perm,
	);

	if ($view_perm == 2 && $cat_id)
	{
		for ($i = 0; $i < sizeof($s_presel_cats); $i++)
		{
			$cat_list .= $index[$s_presel_cats[$i]]['cat_name'] . '<br />';
			$s_hidden_fields = array_merge($s_hidden_fields, array('cat_select[' . $i . ']' => $s_presel_cats[$i]));
		}
	}

	if (confirm_box(true))
	{
		if ($view_perm == 2)
		{
			$cat_ids = array();

			for ($i = 0; $i < sizeof($s_presel_cats); $i++)
			{
				$cat_ids[] = $s_presel_cats[$i];
			}

			$sql = 'DELETE FROM ' . DL_AUTH_TABLE . '
				WHERE ' . $db->sql_in_set('cat_id', $cat_ids);
			$db->sql_query($sql);

			$sql = 'UPDATE ' . DL_CAT_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
				'auth_view'		=> 0,
				'auth_dl'		=> 0,
				'auth_up'		=> 0,
				'auth_mod'		=> 0,
				'auth_cread'	=> 3,
				'auth_cpost'	=> 3)) . ' WHERE ' . $db->sql_in_set('id', $cat_ids);
			$db->sql_query($sql);


			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_CAT_PERM_DROP', false, array($cat_list));
		}
		else
		{
			$sql = 'DELETE FROM ' . DL_AUTH_TABLE;
			$db->sql_query($sql);

			$sql = 'UPDATE ' . DL_CAT_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
				'auth_view'		=> 0,
				'auth_dl'		=> 0,
				'auth_up'		=> 0,
				'auth_mod'		=> 0,
				'auth_cread'	=> 3,
				'auth_cpost'	=> 3));
			$db->sql_query($sql);

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_CAT_PERM_ALL');
		}

		// Purge the auth cache
		@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_auth.' . $phpEx);
		@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_cats.' . $phpEx);
	}
	else
	{
		$confirm_text = ($view_perm == 2) ? $language->lang('DL_PERM_CATS_DROP_CONFIRM', $cat_list) : $language->lang('DL_PERM_ALL_DROP_CONFIRM');

		confirm_box(false, $confirm_text, build_hidden_fields($s_hidden_fields));
	}

	if ($cancel)
	{
		$message = $language->lang('DL_PERM_DROP_ABORTED') . '<br /><br />' . $language->lang('CLICK_RETURN_DOWNLOADADMIN', '<a href="' . $basic_link . '">', '</a>') . adm_back_link($this->u_action);
	}
	else
	{
		$message = $language->lang('DL_PERM_DROP') . '<br /><br />' . $language->lang('CLICK_RETURN_DOWNLOADADMIN', '<a href="' . $basic_link . '">', '</a>') . adm_back_link($this->u_action);
	}

	trigger_error($message);
}

if ($view_perm == 1)
{
	if (isset($s_presel_cats[0]))
	{
		$sql = 'SELECT a.*, g.group_name, g.group_type FROM ' . DL_AUTH_TABLE . ' a, ' . GROUPS_TABLE . ' g
			WHERE a.cat_id = ' . (int) $cat_id . '
				AND a.group_id = g.group_id
			ORDER BY g.group_name';
		$result = $db->sql_query($sql);

		$template->assign_var('S_SHOW_PERMS', true);

		while ($row = $db->sql_fetchrow($result))
		{
			$auth_view	= ($row['auth_view']) ? '<strong>' . $language->lang('YES') . '</strong>' : $language->lang('NO');
			$auth_dl	= ($row['auth_dl']) ? '<strong>' . $language->lang('YES') . '</strong>' : $language->lang('NO');
			$auth_up	= ($row['auth_up']) ? '<strong>' . $language->lang('YES') . '</strong>' : $language->lang('NO');
			$auth_mod	= ($row['auth_mod']) ? '<strong>' . $language->lang('YES') . '</strong>' : $language->lang('NO');

			$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $language->lang('G_' . $row['group_name']) : $row['group_name'];

			$template->assign_block_vars('perm_row', array(
				'GROUP_NAME'	=> $group_name,
				'AUTH_VIEW'		=> $auth_view,
				'AUTH_DL'		=> $auth_dl,
				'AUTH_UP'		=> $auth_up,
				'AUTH_MOD'		=> $auth_mod,
			));
		}

		$db->sql_freeresult($result);

		switch ($index[$cat_id]['auth_cread'])
		{
			case 0:
				$l_auth_cread = $language->lang('DL_STAT_PERM_ALL');
			break;
			case 1:
				$l_auth_cread = $language->lang('DL_STAT_PERM_USER');
			break;
			case 2:
				$l_auth_cread = $language->lang('DL_STAT_PERM_MOD');
			break;
			case 3:
				$l_auth_cread = $language->lang('DL_STAT_PERM_ADMIN');
			break;
		}

		switch ($index[$cat_id]['auth_cpost'])
		{
			case 0:
				$l_auth_cpost = $language->lang('DL_STAT_PERM_ALL');
			break;
			case 1:
				$l_auth_cpost = $language->lang('DL_STAT_PERM_USER');
			break;
			case 2:
				$l_auth_cpost = $language->lang('DL_STAT_PERM_MOD');
			break;
			case 3:
				$l_auth_cpost = $language->lang('DL_STAT_PERM_ADMIN');
			break;
		}

		switch ($index[$cat_id]['auth_view_real'])
		{
			case 1:
				$l_auth_view = $language->lang('DL_PERM_ALL');
			break;
			case 2:
				$l_auth_view = $language->lang('DL_PERM_REG');
			break;
			default:
				$l_auth_view = $language->lang('DL_PERM_GRG');
		}

		switch ($index[$cat_id]['auth_dl_real'])
		{
			case 1:
				$l_auth_dl = $language->lang('DL_PERM_ALL');
			break;
			case 2:
				$l_auth_dl = $language->lang('DL_PERM_REG');
			break;
			default:
				$l_auth_dl = $language->lang('DL_PERM_GRG');
		}

		switch ($index[$cat_id]['auth_up_real'])
		{
			case 1:
				$l_auth_up = $language->lang('DL_PERM_ALL');
			break;
			case 2:
				$l_auth_up = $language->lang('DL_PERM_REG');
			break;
			default:
				$l_auth_up = $language->lang('DL_PERM_GRG');
		}

		switch ($index[$cat_id]['auth_mod_real'])
		{
			case 1:
				$l_auth_mod = $language->lang('DL_PERM_ALL');
			break;
			case 2:
				$l_auth_mod = $language->lang('DL_PERM_REG');
			break;
			default:
				$l_auth_mod = $language->lang('DL_PERM_GRG');
		}

		$template->assign_vars(array(
			'AUTH_VIEW'		=> $l_auth_view,
			'AUTH_DL'		=> $l_auth_dl,
			'AUTH_UP'		=> $l_auth_up,
			'AUTH_MOD'		=> $l_auth_mod,
			'AUTH_CREAD'	=> $l_auth_cread,
			'AUTH_CPOST'	=> $l_auth_cpost,
		));
	}
	else
	{
		$view_perm = false;
	}
}
else
{
	$view_perm = false;
}

if($action == 'save_perm')
{
	if (!check_form_key('dl_adm_perm'))
	{
		trigger_error('FORM_INVALID', E_USER_WARNING);
	}

	$auth_view	= $request->variable('auth_view', 0);
	$auth_dl	= $request->variable('auth_dl', 0);
	$auth_up	= $request->variable('auth_up', 0);
	$auth_mod	= $request->variable('auth_mod', 0);
	$auth_cread	= $request->variable('auth_cread', 3);
	$auth_cpost	= $request->variable('auth_cpost', 3);

	switch($auth_view)
	{
		case 1:
			$log_auth_view = $language->lang('DL_PERM_ALL');
		break;
		case 2:
			$log_auth_view = $language->lang('DL_PERM_REG');
		break;
		default:
			$log_auth_view = $language->lang('DL_PERM_GRG');
		break;
	}

	switch($auth_dl)
	{
		case 1:
			$log_auth_dl = $language->lang('DL_PERM_ALL');
		break;
		case 2:
			$log_auth_dl = $language->lang('DL_PERM_REG');
		break;
		default:
			$log_auth_dl = $language->lang('DL_PERM_GRG');
		break;
	}

	switch($auth_up)
	{
		case 1:
			$log_auth_up = $language->lang('DL_PERM_ALL');
		break;
		case 2:
			$log_auth_up = $language->lang('DL_PERM_REG');
		break;
		default:
			$log_auth_up = $language->lang('DL_PERM_GRG');
		break;
	}

	switch($auth_mod)
	{
		case 1:
			$log_auth_mod = $language->lang('DL_PERM_ALL');
		break;
		case 2:
			$log_auth_mod = $language->lang('DL_PERM_REG');
		break;
		default:
			$log_auth_mod = $language->lang('DL_PERM_GRG');
		break;
	}

	switch($auth_cread)
	{
		case 1:
			$log_auth_cread = $language->lang('DL_STAT_PERM_USER');
		break;
		case 2:
			$log_auth_cread = $language->lang('DL_STAT_PERM_MOD');
		break;
		case 3:
			$log_auth_cread = $language->lang('DL_STAT_PERM_ADMIN');
		break;
		default:
			$log_auth_cread = $language->lang('DL_STAT_PERM_ALL');
			$auth_cread = 0;
		break;
	}

	switch($auth_cpost)
	{
		case 1:
			$log_auth_cpost = $language->lang('DL_STAT_PERM_USER');
		break;
		case 2:
			$log_auth_cpost = $language->lang('DL_STAT_PERM_MOD');
		break;
		case 3:
			$log_auth_cpost = $language->lang('DL_STAT_PERM_ADMIN');
		break;
		default:
			$log_auth_cpost = $language->lang('DL_STAT_PERM_ALL');
			$auth_cpost = 0;
		break;
	}

	if (isset($s_presel_groups[0]) && $s_presel_groups[0] == -1)
	{
		for ($i = 0; $i < sizeof($s_presel_cats); $i++)
		{
			$sql = 'SELECT cat_name FROM ' . DL_CAT_TABLE . '
				WHERE id = ' . (int) $s_presel_cats[$i];
			$result = $db->sql_query($sql);
			$cat_name = $db->sql_fetchfield('cat_name');
			$db->sql_freeresult($result);

			$sql = 'UPDATE ' . DL_CAT_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
				'auth_view'		=> $auth_view,
				'auth_dl'		=> $auth_dl,
				'auth_up'		=> $auth_up,
				'auth_mod'		=> $auth_mod,
				'auth_cread'	=> $auth_cread,
				'auth_cpost'	=> $auth_cpost)) . ' WHERE id = ' . (int) $s_presel_cats[$i];
			$db->sql_query($sql);

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_CAT_PERM_ALL', false, array($cat_name, $log_auth_view, $log_auth_dl, $log_auth_up, $log_auth_mod, $log_auth_cread, $log_auth_cpost));
		}
	}
	else
	{
		for ($i = 0; $i < sizeof($s_presel_cats); $i++)
		{
			$sql = 'SELECT cat_name FROM ' . DL_CAT_TABLE . '
				WHERE id = ' . (int) $s_presel_cats[$i];
			$result = $db->sql_query($sql);
			$cat_name = $db->sql_fetchfield('cat_name');
			$db->sql_freeresult($result);

			for ($j = 0; $j < sizeof($s_presel_groups); $j++)
			{
				$sql = 'DELETE FROM ' . DL_AUTH_TABLE . '
					WHERE cat_id = ' . (int) $s_presel_cats[$i] . '
						AND group_id = ' . (int) $s_presel_groups[$j];
				$db->sql_query($sql);

				$sql = 'INSERT INTO ' . DL_AUTH_TABLE . ' ' . $db->sql_build_array('INSERT', array(
					'cat_id'	=> $s_presel_cats[$i],
					'group_id'	=> $s_presel_groups[$j],
					'auth_view'	=> $auth_view,
					'auth_dl'	=> $auth_dl,
					'auth_up'	=> $auth_up,
					'auth_mod'	=> $auth_mod));
				$db->sql_query($sql);

				$sql = 'SELECT group_type, group_name FROM ' . GROUPS_TABLE . '
					WHERE group_id = ' . (int) $s_presel_groups[$j];
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $language->lang('G_' . $row['group_name']) : $row['group_name'];

				$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_CAT_PERM_GRP', false, array($cat_name, $group_name, $log_auth_view, $log_auth_dl, $log_auth_up, $log_auth_mod));
			}
		}
	}

	// Purge the auth cache
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_auth.' . $phpEx);
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_cats.' . $phpEx);

	$message = $language->lang('CLICK_RETURN_DOWNLOADADMIN', '<a href="' . $basic_link . '">', '</a>') . adm_back_link($this->u_action);

	trigger_error($message);
}

$s_group_select = '';

if (sizeof($s_presel_cats))
{
	$template->assign_var('S_GROUP_SELECT', true);

	for ($i = 0; $i < sizeof($s_presel_cats); $i++)
	{
		if ($s_presel_cats[$i] <> -1)
		{
			$template->assign_block_vars('preselected_cats', array(
				'CAT_TITLE' => $index[$s_presel_cats[$i]]['cat_name'])
			);

			$s_hidden_fields = (isset($s_hidden_fields)) ? array_merge($s_hidden_fields, array('cat_select[' . $i . ']' => $s_presel_cats[$i])) : array('cat_select[]' => $s_presel_cats[$i]);
		}
	}

	if (!$view_perm)
	{
		$sql = 'SELECT group_id, group_name, group_type FROM ' . GROUPS_TABLE . '
			ORDER BY group_name';
		$result = $db->sql_query($sql);

		$total_groups = $db->sql_affectedrows($result);

		if ($total_groups)
		{
			if ($total_groups < 7)
			{
				$size = $total_groups + 3;
			}
			else
			{
				$size = 10;
			}

			$s_group_select .= '<select name="group_select[]" multiple="multiple" size="' . $size . '" class="selectbox">';
			$s_group_select .= '<optgroup label="' . $language->lang('DL_PERMISSIONS_ALL') . '">';
			$s_group_select .= '<option value="-1">' . $language->lang('DL_ALL') . '</option>';
			$s_group_select .= '</optgroup>';
			$s_group_select .= '<optgroup label="' . $language->lang('USERGROUPS') . '">';

			$group_data = array();

			while($row = $db->sql_fetchrow($result))
			{
				$group_id = $row['group_id'];
				$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $language->lang('G_' . $row['group_name']) : $row['group_name'];

				$group_data[$group_id] = $group_name;

				if (in_array($group_id, $s_presel_groups) && (isset($s_presel_groups[0]) && $s_presel_groups[0] != -1))
				{
					$s_group_select .= '<option value="' . $group_id . '" selected="selected">' . $group_name . '</option>';
				}
				else
				{
					$s_group_select .= '<option value="' . $group_id . '">' . $group_name . '</option>';
				}
			}

			$s_group_select .= '</optgroup></select>';

			$s_group_select = (isset($s_presel_groups[0]) && $s_presel_groups[0] == -1) ? str_replace('value="-1">', 'value="-1" selected="selected">', $s_group_select) : $s_group_select;
		}

		if (sizeof($s_presel_groups))
		{
			add_form_key('dl_adm_perm');

			for ($i = 0; $i < sizeof($s_presel_groups); $i++)
			{
				if ($s_presel_groups[$i] <> -1)
				{
					$group_name = $group_data[$s_presel_groups[$i]];
				}
				else
				{
					$group_name = $language->lang('DL_ALL');
				}

				$template->assign_block_vars('preselected_groups', array(
					'GROUP_NAME' => $group_name)
				);

				$s_hidden_fields = array_merge($s_hidden_fields, array('group_select[' . $i . ']' => $s_presel_groups[$i]));
			}

			$s_hidden_fields = array_merge($s_hidden_fields, array('action' => 'save_perm'));

			if ($s_presel_groups[0] == -1)
			{
				$s_auth_view = '<select name="auth_view">';
				$s_auth_dl = '<select name="auth_dl">';
				$s_auth_up = '<select name="auth_up">';
				$s_auth_mod = '<select name="auth_mod">';

				$s_auth_all = '<option value="-1">' . $language->lang('SELECT_OPTION') . '</option>';
				$s_auth_all .= '<option value="1">'.$language->lang('DL_PERM_ALL').'</option>';
				$s_auth_all .= '<option value="2">'.$language->lang('DL_PERM_REG').'</option>';
				$s_auth_all .= '<option value="0">'.$language->lang('DL_PERM_GRG').'</option>';
				$s_auth_all .= '</select>';

				$s_auth_view .= $s_auth_all;
				$s_auth_dl .= $s_auth_all;
				$s_auth_up .= $s_auth_all;
				$s_auth_mod .= $s_auth_all;

				$s_auth_cread = '<select name="auth_cread">';
				$s_auth_cpost = '<select name="auth_cpost">';

				$s_auth_all = '<option value="-1">' . $language->lang('SELECT_OPTION') . '</option>';
				$s_auth_all .= '<option value="0">' . $language->lang('DL_STAT_PERM_ALL') . '</option>';
				$s_auth_all .= '<option value="1">' . $language->lang('DL_STAT_PERM_USER') . '</option>';
				$s_auth_all .= '<option value="2">' . $language->lang('DL_STAT_PERM_MOD') . '</option>';
				$s_auth_all .= '<option value="3">' . $language->lang('DL_STAT_PERM_ADMIN') . '</option>';
				$s_auth_all .= '</select>';

				$s_auth_cread .= $s_auth_all;
				$s_auth_cpost .= $s_auth_all;

				if (sizeof($s_presel_cats) == 1)
				{
					$s_cat_auth_view	= $index[$s_presel_cats[0]]['auth_view_real'];
					$s_cat_auth_dl		= $index[$s_presel_cats[0]]['auth_dl_real'];
					$s_cat_auth_up		= $index[$s_presel_cats[0]]['auth_up_real'];
					$s_cat_auth_mod		= $index[$s_presel_cats[0]]['auth_mod_real'];
					$s_cat_auth_cread	= $index[$s_presel_cats[0]]['auth_cread'];
					$s_cat_auth_cpost	= $index[$s_presel_cats[0]]['auth_cpost'];

					$s_auth_view = str_replace('value="' . $s_cat_auth_view . '">', 'value="' . $s_cat_auth_view . '" selected="selected">', $s_auth_view);
					$s_auth_dl = str_replace('value="' . $s_cat_auth_dl . '">', 'value="' . $s_cat_auth_dl . '" selected="selected">', $s_auth_dl);
					$s_auth_up = str_replace('value="' . $s_cat_auth_up . '">', 'value="' . $s_cat_auth_up . '" selected="selected">', $s_auth_up);
					$s_auth_mod = str_replace('value="' . $s_cat_auth_mod . '">', 'value="' . $s_cat_auth_mod . '" selected="selected">', $s_auth_mod);
					$s_auth_cread = str_replace('value="' . $s_cat_auth_cread . '">', 'value="' . $s_cat_auth_cread . '" selected="selected">', $s_auth_cread);
					$s_auth_cpost = str_replace('value="' . $s_cat_auth_cpost . '">', 'value="' . $s_cat_auth_cpost . '" selected="selected">', $s_auth_cpost);
				}

				$template->assign_var('S_AUTH_ALL_USERS', true);
				$template->assign_vars(array(
					'L_AUTH_EXPL'	=> (sizeof($s_presel_cats) == 1) ? $language->lang('DL_AUTH_SINGLE_EXPLAIN') : $language->lang('DL_AUTH_MULTI_EXPLAIN'),
					'L_OPTIONS'		=> $language->lang('SELECT_OPTION'),
					'S_AUTH_VIEW'	=> $s_auth_view,
					'S_AUTH_DL'		=> $s_auth_dl,
					'S_AUTH_UP'		=> $s_auth_up,
					'S_AUTH_MOD'	=> $s_auth_mod,
					'S_AUTH_CREAD'	=> $s_auth_cread,
					'S_AUTH_CPOST'	=> $s_auth_cpost,
				));
			}
			else
			{
				$template->assign_var('S_AUTH_GROUPS', true);

				if ($s_presel_cats[0] != -1 && $s_presel_groups[0] != -1)
				{
					$sql = 'SELECT auth_view, auth_dl, auth_up, auth_mod FROM ' . DL_AUTH_TABLE . '
						WHERE ' . $db->sql_in_set('cat_id', $s_presel_cats) . '
							AND ' . $db->sql_in_set('group_id', $s_presel_groups) . '
						GROUP BY auth_view, auth_dl, auth_up, auth_mod';
					$result = $db->sql_query($sql);

					$total_auths = $db->sql_affectedrows($result);

					if ($total_auths == 1)
					{
						while ($row = $db->sql_fetchrow($result))
						{
							$auth_view = $row['auth_view'];
							$auth_dl = $row['auth_dl'];
							$auth_up = $row['auth_up'];
							$auth_mod = $row['auth_mod'];
						}

						$template->assign_vars(array(
							'S_AUTH_VIEW_YES'	=> ($auth_view) ? 'checked="checked"' : '',
							'S_AUTH_VIEW_NO'	=> (!$auth_view) ? 'checked="checked"' : '',
							'S_AUTH_DL_YES'		=> ($auth_dl) ? 'checked="checked"' : '',
							'S_AUTH_DL_NO'		=> (!$auth_dl) ? 'checked="checked"' : '',
							'S_AUTH_UP_YES'		=> ($auth_up) ? 'checked="checked"' : '',
							'S_AUTH_UP_NO'		=> (!$auth_up) ? 'checked="checked"' : '',
							'S_AUTH_MOD_YES'	=> ($auth_mod) ? 'checked="checked"' : '',
							'S_AUTH_MOD_NO'		=> (!$auth_mod) ? 'checked="checked"' : '',
						));
					}

					$db->sql_freeresult($result);
				}
			}
		}
	}
}
else
{
	$template->assign_var('S_VIEW_PERM', true);
}

if (sizeof($index) < 10)
{
	$size = sizeof($index);
}
else
{
	$size = 10;
}

$s_cat_select = '<select name="cat_select[]" multiple="multiple" size="' . $size . '" class="selectbox">';
$s_cat_select .= \oxpus\dlext\phpbb\classes\dl_extra::dl_cat_select(0, 0, $s_presel_cats);
$s_cat_select .= '</select>';

$template->assign_vars(array(
	'S_CAT_SELECT'		=> (isset($s_cat_select)) ? $s_cat_select : '',
	'S_GROUP_SELECT'	=> (isset($s_group_select)) ? $s_group_select : '',
	'S_HIDDEN_FIELDS'	=> (isset($s_hidden_fields) && !$view_perm) ? build_hidden_fields($s_hidden_fields) : '',
	'S_PERM_ACTION'		=> $basic_link,

	'U_BACK'			=> (sizeof($s_presel_cats)) ? $this->u_action : '',
));

/*
* show the default page
*/
$template->set_filenames(array(
	'permissions' => 'dl_permissions_body.html')
);

$template->assign_var('S_DL_PERMISSIONS', true);

$template->assign_display('permissions');
