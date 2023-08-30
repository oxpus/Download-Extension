<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

class main implements main_interface
{
	/* phpbb objects */
	protected $root_path;
	protected $php_ext;
	protected $db;
	protected $helper;
	protected $config;
	protected $template;
	protected $user;
	protected $language;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_constants;

	protected $dl_auth;
	protected $dl_index;
	protected $user_admin;

	protected $dlext_table_dl_stats;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param string								$php_ext
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\user							$user
	 * @param \phpbb\language\language				$language
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_stats
	 */
	public function __construct(
		$root_path,
		$php_ext,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\config\config $config,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_stats
	)
	{
		$this->root_path		= $root_path;
		$this->php_ext 			= $php_ext;
		$this->db 				= $db;
		$this->helper 			= $helper;
		$this->config 			= $config;
		$this->template 		= $template;
		$this->user 			= $user;
		$this->language			= $language;

		$this->dlext_auth		= $dlext_auth;
		$this->dlext_constants	= $dlext_constants;

		$this->dlext_table_dl_stats	= $dlext_table_dl_stats;
	}

	public function full_index($only_cat = 0, $parent = 0, $level = 0, $auth_level = 0, &$tree_dl = [])
	{
		if (empty($this->dl_auth))
		{
			$this->dl_auth			= $this->dlext_auth->dl_auth();
			$this->dl_index			= $this->dlext_auth->dl_index();
			$this->user_admin		= $this->dlext_auth->user_admin();
		}

		if (empty($this->dl_index))
		{
			return [];
		}

		if ($only_cat > 0)
		{
			$tree_dl[$only_cat]					= $this->dl_index[$only_cat];
			$tree_dl[$only_cat]['nav_path']		= $this->helper->route('oxpus_dlext_index', ['cat' => $only_cat]);
			$tree_dl[$only_cat]['cat_path']		= $this->dl_index[$only_cat]['path'];
			$tree_dl[$only_cat]['cat_name_nav']	= $this->dl_index[$only_cat]['cat_name'];
		}
		else
		{
			if ($auth_level)
			{
				$access_ids = [];
			}

			foreach ($this->dl_index as $cat_id => $value)
			{
				if ((isset($value['auth_view']) && $value['auth_view']) || (isset($this->dl_auth[$cat_id]['auth_view']) && $this->dl_auth[$cat_id]['auth_view']) || $this->user_admin)
				{
					/*
					* $auth level will return the following data
					* 0 = Default values for each category
					* 1 = IDs from all viewable categories
					* 2 = IDs from moderated categories
					* 3 = IDs from upload categories
					*/

					if ($auth_level == $this->dlext_constants::DL_AUTH_CHECK_VIEW && isset($value['id']) && $value['id'] && ($value['auth_view'] || (isset($this->dl_auth[$cat_id]['auth_view']) && $this->dl_auth[$cat_id]['auth_view']) || $this->user_admin))
					{
						$access_ids[] = $cat_id;
					}
					else if ($auth_level == $this->dlext_constants::DL_AUTH_CHECK_MOD && isset($value['id']) && $value['id'] && ((isset($value['auth_mod']) && $value['auth_mod']) || (isset($this->dl_auth[$cat_id]['auth_mod']) && $this->dl_auth[$cat_id]['auth_mod']) || $this->user_admin))
					{
						$access_ids[] = $cat_id;
					}
					else if ($auth_level == $this->dlext_constants::DL_AUTH_CHECK_UPLOAD && isset($value['id']) && $value['id'] && ((isset($value['auth_up']) && $value['auth_up']) || (isset($this->dl_auth[$cat_id]['auth_up']) && $this->dl_auth[$cat_id]['auth_up']) || $this->user_admin))
					{
						$access_ids[] = $cat_id;
					}
					else if (isset($value['parent']) && $value['parent'] == $parent)
					{
						$seperator = '';

						if ($value['parent'] != 0)
						{
							for ($i = 1; $i < $level; ++$i)
							{
								$seperator .= $this->language->lang('DL_SEPERATOR_PREFIX');
							}

							$seperator .= $this->language->lang('DL_SEPERATOR_SUFFIX');
						}

						$tree_dl[$cat_id] = $value;
						$tree_dl[$cat_id]['nav_path']		= $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]);
						$tree_dl[$cat_id]['cat_path']		= $value['path'];
						$tree_dl[$cat_id]['cat_name']		= $seperator . $value['cat_name'];
						$tree_dl[$cat_id]['cat_name_nav']	= $value['cat_name'];

						++$level;
						$this->full_index(0, $cat_id, $level, 0, $tree_dl);
						--$level;
					}
				}
			}
		}

		return (!empty($auth_level) && $auth_level != 0) ? $access_ids : $tree_dl;
	}

	public function index($parent = 0)
	{
		if (empty($this->dl_auth))
		{
			$this->dl_auth			= $this->dlext_auth->dl_auth();
			$this->dl_index			= $this->dlext_auth->dl_index();
			$this->user_admin		= $this->dlext_auth->user_admin();
		}

		$tree_dl = [];

		if (empty($this->dl_index))
		{
			return $tree_dl;
		}

		foreach ($this->dl_index as $cat_id => $value)
		{
			if (((isset($value['auth_view']) && $value['auth_view']) || (isset($this->dl_auth[$cat_id]['auth_view']) && $this->dl_auth[$cat_id]['auth_view']) || $this->user_admin) && (isset($value['parent']) && $value['parent'] == $parent))
			{
				$tree_dl[$cat_id] = $value;
				$tree_dl[$cat_id]['nav_path']		= $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]);
				$tree_dl[$cat_id]['cat_path']		= $value['path'];
				$tree_dl[$cat_id]['cat_name_nav']	= $value['cat_name'];
				$tree_dl[$cat_id]['sublevel']		= $this->get_sublevel($cat_id);
			}
		}
		return $tree_dl;
	}

	public function get_sublevel($parent = 0)
	{
		if (empty($this->dl_auth))
		{
			$this->dl_auth			= $this->dlext_auth->dl_auth();
			$this->dl_index			= $this->dlext_auth->dl_index();
			$this->user_admin		= $this->dlext_auth->user_admin();
		}

		if (empty($this->dl_index))
		{
			return [];
		}

		$sublevel = [];
		$i = 0;

		foreach (array_keys($this->dl_index) as $cat_id)
		{
			if (((isset($this->dl_index[$cat_id]['auth_view']) && $this->dl_index[$cat_id]['auth_view']) || (isset($this->dl_auth[$cat_id]['auth_view']) && $this->dl_auth[$cat_id]['auth_view']) || $this->user_admin) && (isset($this->dl_index[$cat_id]['parent']) && $this->dl_index[$cat_id]['parent'] == $parent))
			{
				$sublevel['cat_name'][$i] = $this->dl_index[$cat_id]['cat_name'];
				$sublevel['total'][$i] = (isset($this->dl_index[$cat_id]['total'])) ? $this->dl_index[$cat_id]['total'] : 0;
				$sublevel['cat_id'][$i] = $this->dl_index[$cat_id]['id'];
				$sublevel['cat_path'][$i] = $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]);
				$sublevel['cat_sub'][$i] = $cat_id;

				$sublevel['description'][$i] = (isset($this->dl_index[$cat_id]['description'])) ? $this->dl_index[$cat_id]['description'] : '';
				$sublevel['desc_uid'][$i] = (isset($this->dl_index[$cat_id]['desc_uid'])) ? $this->dl_index[$cat_id]['desc_uid'] : '';
				$sublevel['desc_bitfield'][$i] = (isset($this->dl_index[$cat_id]['desc_bitfield'])) ? $this->dl_index[$cat_id]['desc_bitfield'] : '';
				$sublevel['desc_flags'][$i] = (isset($this->dl_index[$cat_id]['desc_flags'])) ? $this->dl_index[$cat_id]['desc_flags'] : '';

				++$i;
			}
		}

		return $sublevel;
	}

	public function get_sublevel_count($parent = 0)
	{
		if (empty($this->dl_auth))
		{
			$this->dl_auth			= $this->dlext_auth->dl_auth();
			$this->dl_index			= $this->dlext_auth->dl_index();
			$this->user_admin		= $this->dlext_auth->user_admin();
		}

		$sublevel_count = 0;

		if (empty($this->dl_index))
		{
			return $sublevel_count;
		}

		foreach (array_keys($this->dl_index) as $cat_id)
		{
			if (isset($this->dl_index[$cat_id]['parent']) && $this->dl_index[$cat_id]['parent'] == $parent && (isset($this->dl_index[$cat_id]['auth_view']) || isset($this->dl_auth[$cat_id]['auth_view']) || $this->user_admin))
			{
				$sublevel_count += (isset($this->dl_index[$cat_id]['total'])) ? $this->dl_index[$cat_id]['total'] : 0;
				$sublevel_count += $this->get_sublevel_count($cat_id);
			}
		}

		return $sublevel_count;
	}

	public function count_sublevel($parent)
	{
		if (empty($this->dl_auth))
		{
			$this->dl_auth			= $this->dlext_auth->dl_auth();
			$this->dl_index			= $this->dlext_auth->dl_index();
			$this->user_admin		= $this->dlext_auth->user_admin();
		}

		$sublevel = 0;

		if (empty($this->dl_index))
		{
			return $sublevel;
		}

		foreach (array_keys($this->dl_index) as $cat_id)
		{
			if ((isset($this->dl_index[$cat_id]['auth_view']) || isset($this->dl_auth[$cat_id]['auth_view']) || $this->user_admin) && (isset($this->dl_index[$cat_id]['parent']) && $this->dl_index[$cat_id]['parent'] == $parent))
			{
				++$sublevel;
			}
		}

		return $sublevel;
	}

	public function find_latest_dl($last_data, $parent, $main_cat, $last_dl_time)
	{
		if (empty($this->dl_auth))
		{
			$this->dl_auth			= $this->dlext_auth->dl_auth();
			$this->dl_index			= $this->dlext_auth->dl_index();
			$this->user_admin		= $this->dlext_auth->user_admin();
		}

		foreach (array_keys($last_data) as $cat_id)
		{
			if ($last_data[$cat_id]['parent'] == $parent || $main_cat == $cat_id)
			{
				$last_cat_time = (isset($last_data[$cat_id]['change_time'])) ? $last_data[$cat_id]['change_time'] : 0;
				$last_dl_times = (isset($last_dl_time['change_time'])) ? $last_dl_time['change_time'] : 0;

				if ($last_cat_time > $last_dl_times && ((isset($this->dl_index[$cat_id]['auth_view']) && $this->dl_index[$cat_id]['auth_view']) || (isset($this->dl_auth[$cat_id]['auth_view']) && $this->dl_auth[$cat_id]['auth_view']) || $this->user_admin))
				{
					$last_dl_time['change_time'] = $last_cat_time;
					$last_dl_time['cat_id'] = $cat_id;
				}

				$last_temp = $this->find_latest_dl($last_data, $cat_id, $this->dlext_constants::DL_NONE, $last_dl_time);
				$last_temp_time = (isset($last_temp['change_time'])) ? $last_temp['change_time'] : 0;
				$last_dl_times = (isset($last_dl_time['change_time'])) ? $last_dl_time['change_time'] : 0;

				if ($last_temp_time > $last_dl_times)
				{
					$last_dl_time['change_time'] = $last_temp['change_time'];
					$last_dl_time['cat_id'] = $last_temp['cat_id'];
				}
			}
		}

		return $last_dl_time;
	}

	public function dl_prune_stats($cat_id, $stats_prune)
	{
		--$stats_prune;

		if ($stats_prune)
		{
			$sql = 'SELECT time_stamp FROM ' . $this->dlext_table_dl_stats . '
				    WHERE cat_id = ' . (int) $cat_id . '
				    ORDER BY time_stamp DESC';
			$result = $this->db->sql_query_limit($sql, 1, $stats_prune);
			$first_time_stamp = $this->db->sql_fetchfield('time_stamp');
			$this->db->sql_freeresult($result);

			if ($first_time_stamp)
			{
				$sql = 'DELETE FROM ' . $this->dlext_table_dl_stats . '
					    WHERE time_stamp <= ' . (int) $first_time_stamp . '
						    AND cat_id = ' . (int) $cat_id;
				$this->db->sql_query($sql);
			}
		}

		return $this->dlext_constants::DL_TRUE;
	}

	public function dl_handle_active($process = 1)
	{
		$dl_mod_is_active = $this->dlext_constants::DL_TRUE;
		$dl_mod_link_show = $this->dlext_constants::DL_TRUE;
		$dl_mod_is_active_for_admins = $this->dlext_constants::DL_FALSE;

		if (!$this->config['dl_active'])
		{
			if ($this->config['dl_off_now_time'])
			{
				$dl_mod_is_active = $this->dlext_constants::DL_FALSE;
			}
			else
			{
				$curr_time = (date('H', time()) * 60) + date('i', time());
				$off_from = (substr($this->config['dl_off_from'], 0, 2) * 60) + (substr($this->config['dl_off_from'], -2));
				$off_till = (substr($this->config['dl_off_till'], 0, 2) * 60) + (substr($this->config['dl_off_till'], -2));

				if ($curr_time >= $off_from && $curr_time <= $off_till)
				{
					$dl_mod_is_active = $this->dlext_constants::DL_FALSE;
				}
			}
		}

		if (!$dl_mod_is_active && $this->config['dl_off_hide'])
		{
			$dl_mod_link_show = $this->dlext_constants::DL_FALSE;
		}

		if (!$dl_mod_is_active && $this->dlext_auth->user_admin() && $this->config['dl_on_admins'])
		{
			$dl_mod_link_show = $this->dlext_constants::DL_TRUE;
			$dl_mod_is_active_for_admins = $this->dlext_constants::DL_TRUE;
		}

		if (!$this->config['dl_global_bots'] && !empty($this->user->data['is_bot']) && $this->user->data['is_bot'])
		{
			$dl_mod_link_show = $this->dlext_constants::DL_FALSE;
			$dl_mod_is_active = $this->dlext_constants::DL_FALSE;
			$dl_mod_is_active_for_admins = $this->dlext_constants::DL_FALSE;
		}

		if (!$this->config['dl_global_guests'] && (!empty($this->user->data['is_registered']) && !$this->user->data['is_registered']) && (!empty($this->user->data['is_bot']) && !$this->user->data['is_bot']))
		{
			$dl_mod_link_show = $this->dlext_constants::DL_FALSE;
			$dl_mod_is_active = $this->dlext_constants::DL_FALSE;
			$dl_mod_is_active_for_admins = $this->dlext_constants::DL_FALSE;
		}

		if (isset($this->user->data['user_wrong_email']) && $this->user->data['user_wrong_email'])
		{
			$dl_mod_link_show = $this->dlext_constants::DL_FALSE;

			if ($process)
			{
				trigger_error('DL_NO_PERMISSION');
			}
		}
		else if (!$dl_mod_is_active)
		{
			if ($dl_mod_is_active_for_admins && $process)
			{
				$this->template->assign_var('S_DL_MOD_OFFLINE_ADMINS', $this->dlext_constants::DL_TRUE);
			}
			else if ($dl_mod_link_show && $process)
			{
				trigger_error('DL_OFF_MESSAGE_ADMIN');
			}
			else if ($process)
			{
				redirect(append_sid($this->root_path . 'index.' . $this->php_ext));
			}
		}

		return $dl_mod_link_show;
	}
}
