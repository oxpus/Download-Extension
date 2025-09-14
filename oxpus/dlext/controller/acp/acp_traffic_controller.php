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
class acp_traffic_controller implements acp_traffic_interface
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames select_datasize

	/* phpbb objects */
	protected $db;
	protected $user;
	protected $phpex;
	protected $root_path;
	protected $log;
	protected $cache;
	protected $config;
	protected $language;
	protected $request;
	protected $template;

	/* extension owned objects */
	public $u_action;

	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_constants;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param string								$phpex
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\cache\service					$cache
	 * @param \phpbb\user							$user
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 */
	public function __construct(
		$root_path,
		$phpex,
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\cache\service $cache,
		\phpbb\user $user,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\constants $dlext_constants
	)
	{
		$this->root_path				= $root_path;
		$this->phpEx					= $phpex;
		$this->db						= $db;
		$this->log						= $log;
		$this->cache					= $cache;
		$this->user						= $user;

		$this->config					= $config;
		$this->language					= $language;
		$this->request					= $request;
		$this->template					= $template;

		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_constants			= $dlext_constants;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$submit				= $this->request->variable('submit', '');
		$action				= $this->request->variable('action', '');
		$func				= $this->request->variable('func', '');

		$traffic_range		= $this->request->variable('traffic_range', '');
		$user_auto_traffic	= $this->request->variable('user_dl_auto_traffic', 0);
		$group_traffic_ary	= $this->request->variable('group_dl_auto_traffic', [0]);
		$data_group_range	= $this->request->variable('data_group_range', ['']);
		$data_user_range	= $this->request->variable('data_user_range', '');

		$user_traffic		= $this->request->variable('user_traffic', 0);
		$all_traffic		= $this->request->variable('all_traffic', 0);
		$group_traffic		= $this->request->variable('group_traffic', 0);
		$username			= $this->request->variable('username', '', $this->dlext_constants::DL_TRUE);
		$group_id			= $this->request->variable('g', 0);

		$index = $this->dlext_main->full_index();

		if (empty($index))
		{
			$this->u_action = str_replace('mode=traffic', 'mode=assistant', $this->u_action);
			redirect($this->u_action);
		}

		unset($index);

		if ($submit)
		{
			if (!check_form_key('dl_adm_traffic'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			switch ($action)
			{
				case 'single':

					$traffic_bytes = $this->dlext_format->get_traffic_save_value($user_traffic, $traffic_range);

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
							trigger_error($this->language->lang('USERNAME') . ' ' . $username . '<br><br>' . $this->language->lang('NO_USER') . adm_back_link($this->u_action));
						}

						if ($func == 'add')
						{
							$user_traffic += $traffic_bytes;

							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_USER_TR_ADD', false, [$username, $user_traffic, $traffic_range]);
						}
						else
						{
							$user_traffic = $traffic_bytes;

							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_USER_TR_SET', false, [$username, $user_traffic, $traffic_range]);
						}

						$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'user_traffic' => $user_traffic
						]) . ' WHERE user_id = ' . (int) $user_id;
						$this->db->sql_query($sql);

						$message = $this->language->lang('DL_USER_AUTO_TRAFFIC_USER') . adm_back_link($this->u_action);

						trigger_error($message);
					}

					break;

				case 'all':

					$traffic_bytes = $this->dlext_format->get_traffic_save_value($all_traffic, $traffic_range);

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
									'user_traffic' => $user_traffic
								]) . ' WHERE user_id = ' . (int) $user_id;
								$this->db->sql_query($sql);
							}

							$this->db->sql_freeresult($result);

							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_ALL_TR_ADD', false, [$all_traffic, $traffic_range]);
						}
						if ($func == 'set')
						{
							$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
								'user_traffic' => $traffic_bytes
							]) . ' WHERE user_id <> ' . ANONYMOUS;
							$this->db->sql_query($sql);

							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_ALL_TR_SET', false, [$all_traffic, $traffic_range]);
						}

						$message = $this->language->lang('DL_USER_AUTO_TRAFFIC_USER') . adm_back_link($this->u_action);

						trigger_error($message);
					}

					break;

				case 'group':

					$traffic_bytes = $this->dlext_format->get_traffic_save_value($group_traffic, $traffic_range);

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
								AND ug.user_pending <> ' . (int) $this->dlext_constants::DL_TRUE . '
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
								'user_traffic' => $user_traffic
							]) . ' WHERE user_id = ' . (int) $user_id;
							$this->db->sql_query($sql);
						}

						$this->db->sql_freeresult($result);

						if ($func == 'add')
						{
							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_GRP_TR_ADD', false, [$group_name, $group_traffic, $traffic_range]);
						}
						else
						{
							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_GRP_TR_SET', false, [$group_name, $group_traffic, $traffic_range]);
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

						$traffic = $this->dlext_format->get_traffic_save_value($group_traffic_ary[$group_id], $data_group_range[$group_id]);

						$sql = 'UPDATE ' . GROUPS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'group_dl_auto_traffic' => $traffic
						]) . ' WHERE group_id = ' . (int) $group_id;
						$this->db->sql_query($sql);

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_AUTO_TR_GRP', false, [$group_name, $group_traffic_ary[$group_id], $data_group_range[$group_id]]);
					}

					$this->db->sql_freeresult($result);

					$traffic = $this->dlext_format->get_traffic_save_value($user_auto_traffic, $data_user_range);

					$this->config->set('dl_user_dl_auto_traffic', $traffic);

					$this->cache->purge('config');

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_AUTO_TR_USER', false, [$user_auto_traffic, $data_user_range]);

					$message = $this->language->lang('DL_USER_AUTO_TRAFFIC_USER') . adm_back_link($this->u_action);

					trigger_error($message);

					break;
			}
		}

		add_form_key('dl_adm_traffic');

		$sql = 'SELECT group_id, group_name, group_dl_auto_traffic, group_type FROM ' . GROUPS_TABLE . '
				ORDER BY group_type DESC, group_name';
		$result = $this->db->sql_query($sql);

		if ($this->db->sql_affectedrows())
		{
			$this->template->assign_var('S_DL_GROUP_BLOCK', $this->dlext_constants::DL_TRUE);

			$s_select_groups = [];

			while ($row = $this->db->sql_fetchrow($result))
			{
				$traffic_ary		= $this->dlext_format->get_traffic_display_value($row['group_dl_auto_traffic']);

				$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $this->language->lang('G_' . $row['group_name']) : $row['group_name'];
				$group_sep = ($row['group_type'] == GROUP_SPECIAL) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

				$this->template->assign_block_vars('group_row', [
					'DL_GROUP_ID'				=> $row['group_id'],
					'DL_GROUP_NAME'				=> $group_name,
					'DL_GROUP_SPECIAL'			=> $group_sep,
					'DL_GROUP_DL_AUTO_TRAFFIC'	=> $traffic_ary['traffic_value'],

					'S_DL_GROUP_DATA_RANGE'		=> $traffic_ary['traffic_range'],
				]);

				$s_select_groups[] = ['value' => $row['group_id'], 'special' => $group_sep, 'name' => $group_name];
			}
		}

		$this->db->sql_freeresult($result);

		if (isset($s_select_groups))
		{
			for ($i = 0; $i < count($s_select_groups); ++$i)
			{
				$this->template->assign_block_vars('group_select', [
					'DL_VALUE'		=> $s_select_groups[$i]['value'],
					'DL_SPECIAL'	=> $s_select_groups[$i]['special'],
					'DL_NAME'		=> $s_select_groups[$i]['name'],
				]);
			}
		}

		$traffic_ary		= $this->dlext_format->get_traffic_display_value($this->config['dl_user_dl_auto_traffic']);

		$select_datasize[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_BYTE, 	'lang' => $this->language->lang('DL_BYTES_LONG')];
		$select_datasize[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_KBYTE, 	'lang' => $this->language->lang('DL_KB')];
		$select_datasize[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_MBYTE, 	'lang' => $this->language->lang('DL_MB')];
		$select_datasize[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_GBYTE, 	'lang' => $this->language->lang('DL_GB')];

		for ($i = 0; $i < count($select_datasize); ++$i)
		{
			$this->template->assign_block_vars('data_range_select', [
				'DL_VALUE'	=> $select_datasize[$i]['value'],
				'DL_LANG'	=> $select_datasize[$i]['lang'],
			]);
		}

		$u_user_select = append_sid($this->root_path . 'memberlist.' . $this->phpEx, 'mode=searchuser&amp;form=user_traffic&amp;field=username&amp;select_single=1');

		$this->template->assign_vars([
			'DL_USER_DL_AUTO_TRAFFIC'	=> $traffic_ary['traffic_value'],

			'S_DL_USER_DATA_RANGE'		=> $traffic_ary['traffic_range'],
			'S_DL_TRAFFIC_RANGE'		=> $this->dlext_constants::DL_FILE_RANGE_MBYTE,
			'S_DL_TRAFFIC_MANAGEMENT'	=> $this->config['dl_traffic_off'],

			'S_DL_PROFILE_ACTION_ALL'	=> $this->u_action . '&amp;action=all',
			'S_DL_PROFILE_ACTION_USER'	=> $this->u_action . '&amp;action=single',
			'S_DL_PROFILE_ACTION_GROUP'	=> $this->u_action . '&amp;action=group',
			'S_DL_CONFIG_ACTION'		=> $this->u_action . '&amp;action=auto',
			'S_DL_TRAFFIC_TABS'			=> $this->dlext_constants::DL_TRUE,

			'U_DL_FIND_USERNAME'		=> $u_user_select,
		]);

		$acl_cat_names = [
			0 => $this->language->lang('DL_ACP_TRAF_AUTO'),
			1 => $this->language->lang('DL_ACP_TRAF_ALL'),
			2 => $this->language->lang('DL_ACP_TRAF_USER'),
			3 => $this->language->lang('DL_ACP_TRAF_GRP'),
		];

		for ($i = 0; $i < count($acl_cat_names); ++$i)
		{
			$this->template->assign_block_vars('category', ['DL_CAT_NAME' => $acl_cat_names[$i]]);
		}
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
