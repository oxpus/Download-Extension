<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\acp;

use Symfony\Component\DependencyInjection\Container;

/**
* @package acp
*/
class acp_traffic_controller implements acp_traffic_interface
{
	public $u_action;
	public $db;
	public $user;
	public $auth;
	public $phpEx;
	public $phpbb_extension_manager;
	public $phpbb_container;
	public $phpbb_path_helper;
	public $phpbb_log;

	public $config;
	public $helper;
	public $language;
	public $request;
	public $template;

	protected $ext_path;
	protected $ext_path_web;
	protected $ext_path_ajax;

	protected $dlext_init;

	/*
	 * @param string								$phpEx
	 * @param Container 							$phpbb_container
	 * @param \phpbb\extension\manager				$phpbb_extension_manager
	 * @param \phpbb\path_helper					$phpbb_path_helper
	 * @param \phpbb\db\driver\driver_interfacer	$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\auth\auth						$auth
	 * @param \phpbb\user							$user
	 */
	public function __construct(
		$phpEx,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\auth\auth $auth,
		\phpbb\user $user,
		$dlext_init
	)
	{
		$this->phpEx					= $phpEx;
		$this->phpbb_container			= $phpbb_container;
		$this->phpbb_extension_manager	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db						= $db;
		$this->phpbb_log				= $log;
		$this->auth						= $auth;
		$this->user						= $user;

		$this->config					= $this->phpbb_container->get('config');
		$this->helper					= $this->phpbb_container->get('controller.helper');
		$this->language					= $this->phpbb_container->get('language');
		$this->request					= $this->phpbb_container->get('request');
		$this->template					= $this->phpbb_container->get('template');

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_init				= $dlext_init;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$this->auth->acl($this->user->data);
		if (!$this->auth->acl_get('a_dl_traffic'))
		{
			trigger_error('DL_NO_PERMISSION', E_USER_WARNING);
		}

		include_once($this->ext_path . 'phpbb/includes/acm_init.' . $this->phpEx);

		if ($submit)
		{
			if (!check_form_key('dl_adm_traffic'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			switch ($action)
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
							WHERE username_clean = '" . $this->db->sql_escape($username) . "'";
						$result			= $this->db->sql_query($sql);
						$row			= $this->db->sql_fetchrow($result);
						$user_id		= $row['user_id'];
						$user_traffic	= $row['user_traffic'];
						$this->db->sql_freeresult($result);

						if (!$user_id)
						{
							trigger_error($this->language->lang('USERNAME') . ' ' . $username . '<br /><br />' . $this->language->lang('NO_USER') . adm_back_link($this->u_action));
						}

						if ($func == 'add')
						{
							$user_traffic += $traffic_bytes;

							$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_USER_TR_ADD', false, [$username, $user_traffic, $x]);
						}
						else
						{
							$user_traffic = $traffic_bytes;

							$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_USER_TR_SET', false, [$username, $user_traffic, $x]);
						}

						$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'user_traffic' => $user_traffic]) . ' WHERE user_id = ' . (int) $user_id;
						$this->db->sql_query($sql);

						$message = $this->language->lang('DL_USER_AUTO_TRAFFIC_USER') . adm_back_link($this->u_action);

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
							$result = $this->db->sql_query($sql);

							while ($row = $this->db->sql_fetchrow($result))
							{
								$user_id = $row['user_id'];
								$user_traffic = $row['user_traffic'] + $traffic_bytes;

								$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
									'user_traffic' => $user_traffic]) . ' WHERE user_id = ' . (int) $user_id;
								$this->db->sql_query($sql);
							}

							$this->db->sql_freeresult($result);

							$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_ALL_TR_ADD', false, [$all_traffic, $y]);
						}
						if ($func == 'set')
						{
							$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
								'user_traffic' => $traffic_bytes]) . ' WHERE user_id <> ' . ANONYMOUS;
							$this->db->sql_query($sql);

							$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_ALL_TR_SET', false, [$all_traffic, $y]);
						}

						$message = $this->language->lang('DL_USER_AUTO_TRAFFIC_USER') . adm_back_link($this->u_action);

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
								WHERE group_id = ' . (int) $group_id . '
								ORDER BY group_type DESC, group_name';
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $this->language->lang('G_' . $row['group_name']) : $row['group_name'];

						$sql = 'SELECT u.user_traffic, u.user_id FROM ' . USER_GROUP_TABLE . ' ug, ' . USERS_TABLE . ' u
							WHERE ug.user_id = u.user_id
								AND ug.user_pending <> ' . true . '
								AND ug.group_id = ' . (int) $group_id;
						$result = $this->db->sql_query($sql);

						while ($row = $this->db->sql_fetchrow($result))
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

							$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
								'user_traffic' => $user_traffic]) . ' WHERE user_id = ' . (int) $user_id;
							$this->db->sql_query($sql);
						}

						$this->db->sql_freeresult($result);

						if ($func == 'add')
						{
							$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_GRP_TR_ADD', false, [$group_name, $group_traffic, $z]);
						}
						else
						{
							$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_GRP_TR_SET', false, [$group_name, $group_traffic, $z]);
						}

						$message = $this->language->lang('DL_USER_AUTO_TRAFFIC_USER') . adm_back_link($this->u_action);

						trigger_error($message);
					}

				break;

				case 'auto':

					$sql = 'SELECT group_type, group_name, group_id FROM ' . GROUPS_TABLE . '
							ORDER BY group_type DESC, group_name';
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						$group_id	= $row['group_id'];
						$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $this->language->lang('G_' . $row['group_name']) : $row['group_name'];

						if ($data_group_range[$group_id] == 'B')
						{
							$traffic = $group_traffic_ary[$group_id];
						}
						else if ($data_group_range[$group_id] == 'KB')
						{
							$traffic = floor($group_traffic_ary[$group_id] * 1024);
						}
						else if ($data_group_range[$group_id] == 'MB')
						{
							$traffic = floor($group_traffic_ary[$group_id] * 1048576);
						}
						else if ($data_group_range[$group_id] == 'GB')
						{
							$traffic = floor($group_traffic_ary[$group_id] * 1073741824);
						}
						else
						{
							$traffic = 0;
						}

						$sql = 'UPDATE ' . GROUPS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
								'group_dl_auto_traffic' => $traffic]) . ' WHERE group_id = ' . (int) $group_id;
						$this->db->sql_query($sql);

						$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_AUTO_TR_GRP', false, [$group_name, $group_traffic_ary[$group_id], $data_group_range[$group_id]]);
					}

					if ($data_user_range == 'B')
					{
						$traffic = $user_auto_traffic;
					}
					else if ($data_user_range == 'KB')
					{
						$traffic = floor($user_auto_traffic * 1024);
					}
					else if ($data_user_range == 'MB')
					{
						$traffic = floor($user_auto_traffic * 1048576);
					}
					else if ($data_user_range == 'GB')
					{
						$traffic = floor($user_auto_traffic * 1073741824);
					}
					else
					{
						$traffic = 0;
					}

					$this->config->set('dl_user_dl_auto_traffic', $traffic, false);

					$cache = $this->phpbb_container->get('cache');

					$cache->purge('config');

					$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_AUTO_TR_USER', false, [$user_auto_traffic, $data_user_range]);

					$message = $this->language->lang('DL_USER_AUTO_TRAFFIC_USER') . adm_back_link($this->u_action);

					trigger_error($message);

				break;
			}
		}

		add_form_key('dl_adm_traffic');

		$s_select_datasize = '<option value="B">' . $this->language->lang('DL_BYTES_LONG') . '</option>';
		$s_select_datasize .= '<option value="KB">' . $this->language->lang('DL_KB') . '</option>';
		$s_select_datasize .= '<option value="MB">' . $this->language->lang('DL_MB') . '</option>';
		$s_select_datasize .= '<option value="GB">' . $this->language->lang('DL_GB') . '</option>';
		$s_select_datasize .= '</select>';

		$sql = 'SELECT group_id, group_name, group_dl_auto_traffic, group_type FROM ' . GROUPS_TABLE . '
				ORDER BY group_type DESC, group_name';
		$result = $this->db->sql_query($sql);
		$total_groups = $this->db->sql_affectedrows($result);

		if ($total_groups)
		{
			$this->template->assign_var('S_GROUP_BLOCK', true);

			$s_select_list = '<select name="g">';

			while ($row = $this->db->sql_fetchrow($result))
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

				$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $this->language->lang('G_' . $row['group_name']) : $row['group_name'];
				$group_sep = ($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '';

				$this->template->assign_block_vars('group_row', [
					'GROUP_ID'				=> $row['group_id'],
					'GROUP_NAME'			=> $group_name,
					'GROUP_SPECIAL'			=> ($row['group_type'] == GROUP_SPECIAL) ? true : false,
					'GROUP_DL_AUTO_TRAFFIC'	=> $group_traffic,
		
					'S_GROUP_DATA_RANGE'	=> $s_group_data_range,
				]);

				$s_select_list .= '<option value="' . $row['group_id'] . '"' . $group_sep . '>' . $group_name . '</option>';
			}

			$s_select_list .= '</select>';
		}

		$this->db->sql_freeresult($result);

		$user_dl_auto_traffic = $this->config['dl_user_dl_auto_traffic'];

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

		$u_user_select = append_sid($this->dlext_init->root_path() . 'memberlist.' . $this->dlext_init->php_ext(), 'mode=searchuser&amp;form=user_traffic&amp;field=username&amp;select_single=true');

		$this->template->assign_vars([
			'USER_DL_AUTO_TRAFFIC'		=> $user_traffic,

			'S_GROUP_SELECT'			=> $s_select_list,
			'S_USER_DATA_RANGE'			=> $s_user_data_range,
			'S_USER_SINGLE_RANGE'		=> $s_user_single_range,
			'S_USER_ALL_RANGE'			=> $s_user_all_range,
			'S_USER_GROUP_RANGE'		=> $s_user_group_range,

			'S_PROFILE_ACTION_ALL'		=> $this->u_action . '&amp;action=all',
			'S_PROFILE_ACTION_USER'		=> $this->u_action . '&amp;action=single',
			'S_PROFILE_ACTION_GROUP'	=> $this->u_action . '&amp;action=group',
			'S_CONFIG_ACTION'			=> $this->u_action . '&amp;action=auto',

			'U_FIND_USERNAME'			=> $u_user_select,
		]);

		$acl_cat_names = [
			0 => $this->language->lang('DL_ACP_TRAF_AUTO'),
			1 => $this->language->lang('DL_ACP_TRAF_ALL'),
			2 => $this->language->lang('DL_ACP_TRAF_USER'),
			3 => $this->language->lang('DL_ACP_TRAF_GRP'),
		];

		for ($i = 0; $i < count($acl_cat_names); ++$i)
		{
			$this->template->assign_block_vars('category', ['CAT_NAME' => $acl_cat_names[$i]]);
		}
	}
}
