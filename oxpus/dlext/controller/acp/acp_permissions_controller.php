<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\acp;

/**
 * @package acp
 */
class acp_permissions_controller implements acp_permissions_interface
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames sql_array

	/* phpbb objects */
	protected $db;
	protected $user;
	protected $log;
	protected $language;
	protected $request;
	protected $template;
	protected $cache;

	/* extension owned objects */
	public $u_action;

	protected $dlext_extra;
	protected $dlext_main;
	protected $dlext_constants;

	protected $dlext_table_dl_auth;
	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\service					$cache
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\user							$user
	 * @param \oxpus\dlext\core\extra				$dlext_extra
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\constants 	$dlext_constants
	 * @param string								$dlext_table_dl_auth
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		\phpbb\cache\service $cache,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\user $user,
		\oxpus\dlext\core\extra $dlext_extra,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_auth,
		$dlext_table_dl_cat
	)
	{
		$this->cache				= $cache;
		$this->db					= $db;
		$this->log					= $log;
		$this->user					= $user;
		$this->language				= $language;
		$this->request				= $request;
		$this->template				= $template;

		$this->dlext_table_dl_auth	= $dlext_table_dl_auth;
		$this->dlext_table_dl_cat	= $dlext_table_dl_cat;

		$this->dlext_extra			= $dlext_extra;
		$this->dlext_main			= $dlext_main;
		$this->dlext_constants		= $dlext_constants;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$action				= $this->request->variable('action', '');
		$cancel				= $this->request->variable('cancel', '');
		$s_presel_cats		= $this->request->variable('cat_select', [0]);
		$s_presel_groups	= $this->request->variable('group_select', [0]);
		$view_perm			= $this->request->variable('view_perm', 0);

		if ($cancel)
		{
			$action = '';
		}

		$s_hidden_fields = [];

		$index = $this->dlext_main->full_index();

		if (empty($index))
		{
			$this->u_action = str_replace('mode=permissions', 'mode=assistant', $this->u_action);
			redirect($this->u_action);
		}

		$cat_id = (isset($s_presel_cats[0])) ? $s_presel_cats[0] : [];

		if ($view_perm > $this->dlext_constants::DL_PERM_VIEW)
		{
			$cat_list = '';
			$s_hidden_fields += ['view_perm' => $view_perm];

			if ($view_perm == $this->dlext_constants::DL_PERM_DROP_CATS && $cat_id)
			{
				for ($i = 0; $i < count($s_presel_cats); ++$i)
				{
					$cat_list .= $index[$s_presel_cats[$i]]['cat_name'] . '<br>';
					$s_hidden_fields += ['cat_select[' . $i . ']' => $s_presel_cats[$i]];
				}
			}

			if (confirm_box($this->dlext_constants::DL_TRUE))
			{
				if ($view_perm == $this->dlext_constants::DL_PERM_DROP_CATS)
				{
					$cat_ids = [];

					for ($i = 0; $i < count($s_presel_cats); ++$i)
					{
						$cat_ids[] = $s_presel_cats[$i];
					}

					$sql = 'DELETE FROM ' . $this->dlext_table_dl_auth . '
						WHERE ' . $this->db->sql_in_set('cat_id', $cat_ids);
					$this->db->sql_query($sql);

					$sql = 'UPDATE ' . $this->dlext_table_dl_cat . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'auth_view'		=> $this->dlext_constants::DL_PERM_GENERAL_ALL,
						'auth_dl'		=> $this->dlext_constants::DL_PERM_GENERAL_ALL,
						'auth_up'		=> $this->dlext_constants::DL_PERM_GENERAL_ALL,
						'auth_mod'		=> $this->dlext_constants::DL_PERM_GENERAL_ALL,
						'auth_cread'	=> $this->dlext_constants::DL_PERM_ADMIN,
						'auth_cpost'	=> $this->dlext_constants::DL_PERM_ADMIN
					]) . ' WHERE ' . $this->db->sql_in_set('id', $cat_ids);
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_PERM_DROP', false, [$cat_list]);
				}
				else
				{
					$sql = 'DELETE FROM ' . $this->dlext_table_dl_auth;
					$this->db->sql_query($sql);

					$sql = 'UPDATE ' . $this->dlext_table_dl_cat . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'auth_view'		=> $this->dlext_constants::DL_PERM_GENERAL_ALL,
						'auth_dl'		=> $this->dlext_constants::DL_PERM_GENERAL_ALL,
						'auth_up'		=> $this->dlext_constants::DL_PERM_GENERAL_ALL,
						'auth_mod'		=> $this->dlext_constants::DL_PERM_GENERAL_ALL,
						'auth_cread'	=> $this->dlext_constants::DL_PERM_ADMIN,
						'auth_cpost'	=> $this->dlext_constants::DL_PERM_ADMIN
					]);
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_PERM_ALL');
				}

				// Purge the auth cache
				$this->cache->destroy('_dlext_auth');
				$this->cache->destroy('_dlext_cats');
			}
			else
			{
				$confirm_text = ($view_perm == $this->dlext_constants::DL_PERM_DROP_CATS) ? $this->language->lang('DL_PERM_CATS_DROP_CONFIRM', $cat_list) : $this->language->lang('DL_PERM_ALL_DROP_CONFIRM');

				confirm_box($this->dlext_constants::DL_FALSE, $confirm_text, build_hidden_fields($s_hidden_fields), '@oxpus_dlext/dl_confirm_body.html');
			}

			if ($cancel)
			{
				$message = $this->language->lang('DL_PERM_DROP_ABORTED') . '<br><br>' . $this->language->lang('CLICK_RETURN_DOWNLOADADMIN', '<a href="' . $this->u_action . '">', '</a>') . adm_back_link($this->u_action);
			}
			else
			{
				$message = $this->language->lang('DL_PERM_DROP') . '<br><br>' . $this->language->lang('CLICK_RETURN_DOWNLOADADMIN', '<a href="' . $this->u_action . '">', '</a>') . adm_back_link($this->u_action);
			}

			trigger_error($message);
		}

		$auth_perm_downloads = [
			$this->dlext_constants::DL_PERM_GENERAL_ALL			=> 'DL_PERM_ALL',
			$this->dlext_constants::DL_PERM_GENERAL_REG_USER	=> 'DL_PERM_REG',
			'default'											=> 'DL_PERM_GRG',
		];

		$auth_perm_comments = [
			$this->dlext_constants::DL_PERM_USER	=> 'DL_STAT_PERM_USER',
			$this->dlext_constants::DL_PERM_MOD		=> 'DL_STAT_PERM_MOD',
			$this->dlext_constants::DL_PERM_ADMIN	=> 'DL_STAT_PERM_ADMIN',
			'default'								=> 'DL_STAT_PERM_ALL',
		];

		if ($view_perm == $this->dlext_constants::DL_PERM_VIEW)
		{
			if (!empty($s_presel_cats))
			{
				$this->template->assign_var('S_DL_SHOW_PERMS', $this->dlext_constants::DL_TRUE);

				$sql_array['SELECT'] = 'c.id as c_cat_id, c.cat_name, a.*, g.group_name, g.group_type';

				$sql_array['FROM'] = [$this->dlext_table_dl_cat	=> 'c'];

				$sql_array['LEFT_JOIN'][] = [
					'FROM'	=> [$this->dlext_table_dl_auth => 'a'],
					'ON'	=> 'a.cat_id = c.id'
				];

				$sql_array['LEFT_JOIN'][] = [
					'FROM'	=> [GROUPS_TABLE => 'g'],
					'ON'	=> 'g.group_id = a.group_id'
				];

				$sql_array['WHERE'] = $this->db->sql_in_set('c.id', $s_presel_cats) . ' AND g.group_name is not null';

				$sql_array['ORDER_BY']	= 'c.sort, c.cat_name, g.group_type DESC, g.group_name';

				$sql = $this->db->sql_build_query('SELECT', $sql_array);
				$result = $this->db->sql_query($sql);

				$cur_cat = 0;

				while ($row = $this->db->sql_fetchrow($result))
				{
					$cat_id = $row['c_cat_id'];

					if ($cat_id != $cur_cat)
					{
						$cur_cat = $cat_id;

						$l_auth_view	= $auth_perm_downloads[$index[$cat_id]['auth_view_real']] ?? $auth_perm_downloads['default'];
						$l_auth_dl		= $auth_perm_downloads[$index[$cat_id]['auth_dl_real']] ?? $auth_perm_downloads['default'];
						$l_auth_up		= $auth_perm_downloads[$index[$cat_id]['auth_up_real']] ?? $auth_perm_downloads['default'];
						$l_auth_mod		= $auth_perm_downloads[$index[$cat_id]['auth_mod_real']] ?? $auth_perm_downloads['default'];

						$l_auth_cread	= $auth_perm_comments[$index[$cat_id]['auth_cread']] ?? $auth_perm_comments['default'];
						$l_auth_cpost	= $auth_perm_comments[$index[$cat_id]['auth_cpost']] ?? $auth_perm_comments['default'];

						$this->template->assign_block_vars('cat_perm_block', [
							'DL_CAT_NAME'		=> $row['cat_name'],
							'DL_AUTH_VIEW'		=> $l_auth_view,
							'DL_AUTH_DL'		=> $l_auth_dl,
							'DL_AUTH_UP'		=> $l_auth_up,
							'DL_AUTH_MOD'		=> $l_auth_mod,
							'DL_AUTH_CREAD'		=> $l_auth_cread,
							'DL_AUTH_CPOST'		=> $l_auth_cpost,
						]);
					}

					$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $this->language->lang('G_' . $row['group_name']) : $row['group_name'];
					$group_sep = ($row['group_type'] == GROUP_SPECIAL) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

					$this->template->assign_block_vars('cat_perm_block.perm_row', [
						'DL_GROUP_NAME'		=> $group_name,
						'DL_GROUP_SEP'		=> $group_sep,
						'DL_AUTH_VIEW'		=> $row['auth_view'],
						'DL_AUTH_DL'		=> $row['auth_dl'],
						'DL_AUTH_UP'		=> $row['auth_up'],
						'DL_AUTH_MOD'		=> $row['auth_mod'],
					]);
				}

				$this->db->sql_freeresult($result);
			}
			else
			{
				$view_perm = $this->dlext_constants::DL_PERM_EDIT;
			}
		}
		else
		{
			$view_perm = $this->dlext_constants::DL_PERM_EDIT;
		}

		if ($action == 'save_perm')
		{
			if (!check_form_key('dl_adm_perm'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			$auth_cread		= $this->request->variable('auth_cread', $this->dlext_constants::DL_PERM_ADMIN);
			$auth_cpost		= $this->request->variable('auth_cpost', $this->dlext_constants::DL_PERM_ADMIN);
			$auth_dl		= $this->request->variable('auth_dl', $this->dlext_constants::DL_PERM_GENERAL_ALL);
			$auth_mod		= $this->request->variable('auth_mod', $this->dlext_constants::DL_PERM_GENERAL_ALL);
			$auth_up		= $this->request->variable('auth_up', $this->dlext_constants::DL_PERM_GENERAL_ALL);
			$auth_view		= $this->request->variable('auth_view', $this->dlext_constants::DL_PERM_GENERAL_ALL);

			$log_auth_view	= $this->language->lang($auth_perm_downloads[$auth_view] ?? $auth_perm_downloads['default']);
			$log_auth_dl	= $this->language->lang($auth_perm_downloads[$auth_dl] ?? $auth_perm_downloads['default']);
			$log_auth_up	= $this->language->lang($auth_perm_downloads[$auth_up] ?? $auth_perm_downloads['default']);
			$log_auth_mod	= $this->language->lang($auth_perm_downloads[$auth_mod] ?? $auth_perm_downloads['default']);

			$log_auth_cread	= $this->language->lang($auth_perm_comments[$auth_cread] ?? $auth_perm_comments['default']);
			$log_auth_cpost	= $this->language->lang($auth_perm_comments[$auth_cpost] ?? $auth_perm_comments['default']);

			if (isset($s_presel_groups[0]) && $s_presel_groups[0] == $this->dlext_constants::DL_NONE)
			{
				for ($i = 0; $i < count($s_presel_cats); ++$i)
				{
					$sql = 'SELECT cat_name FROM ' . $this->dlext_table_dl_cat . '
						WHERE id = ' . (int) $s_presel_cats[$i];
					$result = $this->db->sql_query($sql);
					$cat_name = $this->db->sql_fetchfield('cat_name');
					$this->db->sql_freeresult($result);

					$sql = 'UPDATE ' . $this->dlext_table_dl_cat . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'auth_view'		=> $auth_view,
						'auth_dl'		=> $auth_dl,
						'auth_up'		=> $auth_up,
						'auth_mod'		=> $auth_mod,
						'auth_cread'	=> $auth_cread,
						'auth_cpost'	=> $auth_cpost
					]) . ' WHERE id = ' . (int) $s_presel_cats[$i];
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_PERM_ALL', false, [$cat_name, $log_auth_view, $log_auth_dl, $log_auth_up, $log_auth_mod, $log_auth_cread, $log_auth_cpost]);
				}
			}
			else
			{
				for ($i = 0; $i < count($s_presel_cats); ++$i)
				{
					$sql = 'SELECT cat_name FROM ' . $this->dlext_table_dl_cat . '
						WHERE id = ' . (int) $s_presel_cats[$i];
					$result = $this->db->sql_query($sql);
					$cat_name = $this->db->sql_fetchfield('cat_name');
					$this->db->sql_freeresult($result);

					for ($j = 0; $j < count($s_presel_groups); ++$j)
					{
						$sql = 'DELETE FROM ' . $this->dlext_table_dl_auth . '
							WHERE cat_id = ' . (int) $s_presel_cats[$i] . '
								AND group_id = ' . (int) $s_presel_groups[$j];
						$this->db->sql_query($sql);

						$sql = 'INSERT INTO ' . $this->dlext_table_dl_auth . ' ' . $this->db->sql_build_array('INSERT', [
							'cat_id'	=> $s_presel_cats[$i],
							'group_id'	=> $s_presel_groups[$j],
							'auth_view'	=> $auth_view,
							'auth_dl'	=> $auth_dl,
							'auth_up'	=> $auth_up,
							'auth_mod'	=> $auth_mod
						]);
						$this->db->sql_query($sql);

						$sql = 'SELECT group_type, group_name FROM ' . GROUPS_TABLE . '
								WHERE group_id = ' . (int) $s_presel_groups[$j];
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);
						$group_name = ($row['group_type'] == GROUP_SPECIAL) ? '<strong>' . $this->language->lang('G_' . $row['group_name']) . '</strong>' : $row['group_name'];

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_PERM_GRP', false, [$cat_name, $group_name, $log_auth_view, $log_auth_dl, $log_auth_up, $log_auth_mod]);
					}
				}
			}

			// Purge the auth cache
			$this->cache->destroy('_dlext_auth');
			$this->cache->destroy('_dlext_cats');

			$message = $this->language->lang('CLICK_RETURN_DOWNLOADADMIN', '<a href="' . $this->u_action . '">', '</a>') . adm_back_link($this->u_action);

			trigger_error($message);
		}

		$total_groups = 0;
		$group_select_size = 0;

		if (isset($s_presel_groups[0]) && $s_presel_groups[0] == $this->dlext_constants::DL_NONE)
		{
			$group_all_select = $this->dlext_constants::DL_TRUE;
		}
		else
		{
			$group_all_select = $this->dlext_constants::DL_FALSE;
		}

		if (!empty($s_presel_cats))
		{
			$this->template->assign_var('S_DL_GROUP_SELECT', $this->dlext_constants::DL_TRUE);

			for ($i = 0; $i < count($s_presel_cats); ++$i)
			{
				if ($s_presel_cats[$i] != $this->dlext_constants::DL_NONE)
				{
					$this->template->assign_block_vars('preselected_cats', [
						'DL_CAT_TITLE' => $index[$s_presel_cats[$i]]['cat_name'],
					]);

					if ($view_perm == $this->dlext_constants::DL_PERM_EDIT)
					{
						$s_hidden_fields += ['cat_select[' . $i . ']' => $s_presel_cats[$i]];
					}
				}
			}

			if ($view_perm == $this->dlext_constants::DL_PERM_EDIT)
			{
				$sql = 'SELECT group_id, group_name, group_type FROM ' . GROUPS_TABLE . '
						ORDER BY group_type DESC, group_name';
				$result = $this->db->sql_query($sql);

				$total_groups = $this->db->sql_affectedrows();

				if ($total_groups)
				{
					if ($total_groups < $this->dlext_constants::DL_SELECT_MAX_SIZE - 3)
					{
						$group_select_size = $total_groups + 3;
					}
					else
					{
						$group_select_size = $this->dlext_constants::DL_SELECT_MAX_SIZE;
					}

					$group_data = [];
					$group_sepr = [];

					while ($row = $this->db->sql_fetchrow($result))
					{
						$group_id = $row['group_id'];
						$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $this->language->lang('G_' . $row['group_name']) : $row['group_name'];
						$group_sep = ($row['group_type'] == GROUP_SPECIAL) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

						$group_data[$group_id] = $group_name;
						$group_sepr[$group_id] = $group_sep;

						if (in_array($group_id, $s_presel_groups) && (isset($s_presel_groups[0]) && $s_presel_groups[0] != $this->dlext_constants::DL_NONE))
						{
							$selected = $this->dlext_constants::DL_TRUE;
						}
						else
						{
							$selected = $this->dlext_constants::DL_FALSE;
						}

						$this->template->assign_block_vars('dl_perm_group_select', [
							'DL_VALUE'		=> $group_id,
							'DL_SPECIAL'	=> $group_sep,
							'DL_SELECTED'	=> $selected,
							'DL_NAME'		=> $group_name,
						]);
					}

					$this->db->sql_freeresult($result);
				}

				if (!empty($s_presel_groups))
				{
					add_form_key('dl_adm_perm');

					for ($i = 0; $i < count($s_presel_groups); ++$i)
					{
						if ($s_presel_groups[$i] != $this->dlext_constants::DL_NONE)
						{
							$group_name = $group_data[$s_presel_groups[$i]];
							$group_sep = $group_sepr[$s_presel_groups[$i]];
						}
						else
						{
							$group_name = $this->language->lang('DL_ALL');
							$group_sep = '';
						}

						$this->template->assign_block_vars('preselected_groups', [
							'DL_GROUP_NAME'	=> $group_name,
							'DL_GROUP_SEP'	=> $group_sep,
						]);

						$s_hidden_fields += ['group_select[' . $i . ']' => $s_presel_groups[$i]];
					}

					$s_hidden_fields += ['action' => 'save_perm'];

					if ($s_presel_groups[0] == $this->dlext_constants::DL_NONE)
					{
						if (count($s_presel_cats) >= 1)
						{
							$s_auth_all = [];
							$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_GENERAL_NONE, 		'lang' => $this->language->lang('SELECT_OPTION')];
							$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_GENERAL_ALL, 		'lang' => $this->language->lang('DL_PERM_ALL')];
							$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_GENERAL_REG_USER,	'lang' => $this->language->lang('DL_PERM_REG')];
							$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_GENERAL_GROUPS, 	'lang' => $this->language->lang('DL_PERM_GRG')];

							for ($i = 0; $i < count($s_auth_all); ++$i)
							{
								$this->template->assign_block_vars('dl_perm_all_default', [
									'DL_VALUE'	=> $s_auth_all[$i]['value'],
									'DL_NAME'	=> $s_auth_all[$i]['lang'],
								]);
							}

							$s_auth_all = [];
							$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_GENERAL_NONE, 	'lang' => $this->language->lang('SELECT_OPTION')];
							$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_ALL, 			'lang' => $this->language->lang('DL_STAT_PERM_ALL')];
							$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_USER, 			'lang' => $this->language->lang('DL_STAT_PERM_USER')];
							$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_MOD, 			'lang' => $this->language->lang('DL_STAT_PERM_MOD')];
							$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_ADMIN, 			'lang' => $this->language->lang('DL_STAT_PERM_ADMIN')];

							for ($i = 0; $i < count($s_auth_all); ++$i)
							{
								$this->template->assign_block_vars('dl_perm_all_comments', [
									'DL_VALUE'	=> $s_auth_all[$i]['value'],
									'DL_NAME'	=> $s_auth_all[$i]['lang'],
								]);
							}

							$this->template->assign_var('S_DL_AUTH_ALL_USERS', $this->dlext_constants::DL_TRUE);

							$this->template->assign_vars([
								'S_DL_AUTH_VIEW'	=> $index[$s_presel_cats[0]]['auth_view_real'],
								'S_DL_AUTH_DL'		=> $index[$s_presel_cats[0]]['auth_dl_real'],
								'S_DL_AUTH_UP'		=> $index[$s_presel_cats[0]]['auth_up_real'],
								'S_DL_AUTH_MOD'		=> $index[$s_presel_cats[0]]['auth_mod_real'],
								'S_DL_AUTH_CREAD'	=> $index[$s_presel_cats[0]]['auth_cread'],
								'S_DL_AUTH_CPOST'	=> $index[$s_presel_cats[0]]['auth_cpost'],
							]);
						}
					}
					else
					{
						$this->template->assign_var('S_DL_AUTH_GROUPS', $this->dlext_constants::DL_TRUE);

						$sql = 'SELECT auth_view, auth_dl, auth_up, auth_mod FROM ' . $this->dlext_table_dl_auth . '
							WHERE ' . $this->db->sql_in_set('cat_id', $s_presel_cats) . '
								AND ' . $this->db->sql_in_set('group_id', $s_presel_groups) . '
							GROUP BY auth_view, auth_dl, auth_up, auth_mod';
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$total_auths = $this->db->sql_affectedrows();
						$this->db->sql_freeresult($result);

						if ($total_auths == 1)
						{
							$this->template->assign_vars([
								'DL_AUTH_VIEW'	=> $row['auth_view'],
								'DL_AUTH_DL'	=> $row['auth_dl'],
								'DL_AUTH_UP'	=> $row['auth_up'],
								'DL_AUTH_MOD'	=> $row['auth_mod'],
							]);
						}
					}
				}
			}
		}
		else
		{
			$this->template->assign_var('S_DL_VIEW_PERM', $this->dlext_constants::DL_TRUE);
		}

		if (count($index) < $this->dlext_constants::DL_SELECT_MAX_SIZE)
		{
			$cat_select_size = count($index);
		}
		else
		{
			$cat_select_size = $this->dlext_constants::DL_SELECT_MAX_SIZE;
		}

		$cat_select = $this->dlext_extra->dl_dropdown(0, 0, $s_presel_cats);

		if (!empty($cat_select) && is_array($cat_select))
		{
			foreach (array_keys($cat_select) as $key)
			{
				$this->template->assign_block_vars('cat_select', [
					'DL_VALUE'		=> $cat_select[$key]['cat_id'],
					'DL_SEPERATOR'	=> $cat_select[$key]['seperator'],
					'DL_SELECTED'	=> $cat_select[$key]['selected'],
					'DL_NAME'		=> $cat_select[$key]['cat_name'],
				]);
			}
		}

		$this->template->assign_vars([
			'DL_NONE'					=> $this->dlext_constants::DL_PERM_GENERAL_NONE,
			'DL_SELECT_SINGLE_GROUP'	=> (count($s_presel_groups) <= 1 && count($s_presel_cats) <= 1) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_GROUP_ALL'			=> $group_all_select,
			'S_DL_GROUPS_TOTAL'			=> $total_groups,
			'S_DL_GROUPS_SELECT_SIZE'	=> $group_select_size,
			'S_DL_CATS_SELECT_SIZE'		=> $cat_select_size,
			'S_DL_HIDDEN_FIELDS'		=> (isset($s_hidden_fields) && $view_perm != $this->dlext_constants::DL_PERM_VIEW) ? build_hidden_fields($s_hidden_fields) : '',
			'S_DL_PERM_ACTION'			=> $this->u_action,

			'U_DL_BACK'					=> (!empty($s_presel_cats)) ? $this->u_action : '',
		]);
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
