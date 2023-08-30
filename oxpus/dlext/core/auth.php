<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

class auth implements auth_interface
{
	/* phpbb objects */
	protected $user;
	protected $auth;
	protected $config;
	protected $db;

	/* extension owned objects */
	protected $dlext_cache;
	protected $dlext_constants;

	protected $cat_counts;

	protected $dlext_table_dl_auth;
	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param \phpbb\user							$user
	 * @param \phpbb\auth\auth						$auth
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \oxpus\dlext\core\cache				$dlext_cache
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_auth
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\oxpus\dlext\core\cache $dlext_cache,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_auth,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->user 					= $user;
		$this->auth						= $auth;
		$this->config 					= $config;
		$this->db 						= $db;

		$this->dlext_cache				= $dlext_cache;
		$this->dlext_constants			= $dlext_constants;

		$this->dlext_table_dl_auth		= $dlext_table_dl_auth;
		$this->dlext_table_downloads	= $dlext_table_downloads;
		$this->dlext_table_dl_cat		= $dlext_table_dl_cat;
	}

	public function dl_auth()
	{
		$dl_auth_perm = $this->dlext_cache->obtain_dl_auth();

		$cat_auth_array = [];

		$auth_cat = (isset($dl_auth_perm['auth_cat'])) ? $dl_auth_perm['auth_cat'] : [];
		$group_perm_ids = (isset($dl_auth_perm['group_perm_ids'])) ? $dl_auth_perm['group_perm_ids'] : [];
		$auth_perm = (isset($dl_auth_perm['auth_perm'])) ? $dl_auth_perm['auth_perm'] : [];

		$user_id = ($this->user->data['user_perm_from']) ? $this->user->data['user_perm_from'] : $this->user->data['user_id'];

		if (!empty($group_perm_ids))
		{
			$cat_auth_array = $this->dlext_cache->obtain_dl_access_groups($auth_cat, $user_id, $auth_perm, $group_perm_ids);
		}

		return $cat_auth_array;
	}

	public function dl_index()
	{
		$dl_index = $this->dlext_cache->obtain_dl_cats();

		if (!empty($dl_index))
		{
			foreach (array_keys($dl_index) as $key)
			{
				// check the default cat permissions
				if (isset($dl_index[$key]['auth_view']) && ($dl_index[$key]['auth_view'] == 1 || ($dl_index[$key]['auth_view'] == 2 && (!empty($this->user->data['is_registered']) && $this->user->data['is_registered']))))
				{
					$dl_index[$key]['auth_view'] = $this->dlext_constants::DL_TRUE;
				}
				else
				{
					$dl_index[$key]['auth_view'] = $this->dlext_constants::DL_FALSE;
				}

				if (isset($dl_index[$key]['auth_dl']) && ($dl_index[$key]['auth_dl'] == 1 || ($dl_index[$key]['auth_dl'] == 2 &&  (!empty($this->user->data['is_registered']) && $this->user->data['is_registered']))))
				{
					$dl_index[$key]['auth_dl'] = $this->dlext_constants::DL_TRUE;
				}
				else
				{
					$dl_index[$key]['auth_dl'] = $this->dlext_constants::DL_FALSE;
				}

				if (isset($dl_index[$key]['auth_up']) && ($dl_index[$key]['auth_up'] == 1 || ($dl_index[$key]['auth_up'] == 2 &&  (!empty($this->user->data['is_registered']) && $this->user->data['is_registered']))))
				{
					$dl_index[$key]['auth_up'] = $this->dlext_constants::DL_TRUE;
				}
				else
				{
					$dl_index[$key]['auth_up'] = $this->dlext_constants::DL_FALSE;
				}

				if (isset($dl_index[$key]['auth_mod']) && ($dl_index[$key]['auth_mod'] == 1 || ($dl_index[$key]['auth_mod'] == 2 &&  (!empty($this->user->data['is_registered']) && $this->user->data['is_registered']))))
				{
					$dl_index[$key]['auth_mod'] = $this->dlext_constants::DL_TRUE;
				}
				else
				{
					$dl_index[$key]['auth_mod'] = $this->dlext_constants::DL_FALSE;
				}
			}
		}
		else
		{
			$dl_index = [];
		}

		$this->cat_counts = $this->dlext_cache->obtain_dl_cat_counts();

		if (!empty($this->cat_counts))
		{
			foreach ($this->cat_counts as $key => $value)
			{
				$dl_index[$key]['total'] = $value;
			}
		}

		return $dl_index;
	}

	public function dl_cat_auth($cat_id)
	{
		$dl_auth = $this->dl_auth();

		$cat_perm = [];

		$cat_perm['auth_view']	= (isset($dl_auth[$cat_id]['auth_view'])) ? intval($dl_auth[$cat_id]['auth_view']) : $this->dlext_constants::DL_FALSE;
		$cat_perm['auth_dl']	= (isset($dl_auth[$cat_id]['auth_dl'])) ? intval($dl_auth[$cat_id]['auth_dl']) : $this->dlext_constants::DL_FALSE;
		$cat_perm['auth_mod']	= (isset($dl_auth[$cat_id]['auth_mod'])) ? intval($dl_auth[$cat_id]['auth_mod']) : $this->dlext_constants::DL_FALSE;
		$cat_perm['auth_up']	= (isset($dl_auth[$cat_id]['auth_up'])) ? intval($dl_auth[$cat_id]['auth_up']) : $this->dlext_constants::DL_FALSE;
		$cat_perm['auth_cread']	= (isset($dl_auth[$cat_id]['auth_cread'])) ? intval($dl_auth[$cat_id]['auth_cread']) : $this->dlext_constants::DL_FALSE;
		$cat_perm['auth_cpost']	= (isset($dl_auth[$cat_id]['auth_cpost'])) ? intval($dl_auth[$cat_id]['auth_cpost']) : $this->dlext_constants::DL_FALSE;

		return $cat_perm;
	}

	public function user_admin()
	{
		return ($this->auth->acl_get('a_') && !$this->user->data['user_perm_from']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
	}

	public function get_ext_blacklist()
	{
		$this->config->set('dl_enable_blacklist', 0);
		$ext_blacklist = [];

		if ($this->config['dl_use_ext_blacklist'])
		{
			$blacklist_ary = $this->dlext_cache->obtain_dl_blacklist();

			if (!empty($blacklist_ary))
			{
				$ext_blacklist = array_unique($blacklist_ary);
				$this->config->set('dl_enable_blacklist', 1);
			}
		}

		return $ext_blacklist;
	}

	public function user_auth($cat_id, $perm)
	{
		$dl_index	= $this->dlext_cache->obtain_dl_cats();
		$dl_auth	= $this->dl_auth();

		if ((isset($dl_auth[$cat_id][$perm]) && $dl_auth[$cat_id][$perm]) || (isset($dl_index[$cat_id][$perm]) && $dl_index[$cat_id][$perm]) || $this->user_admin())
		{
			return $this->dlext_constants::DL_TRUE;
		}

		return $this->dlext_constants::DL_FALSE;
	}

	public function stats_perm()
	{
		$dl_index = $this->dlext_cache->obtain_dl_cats();

		return $this->_check_perm($dl_index, $this->config['dl_stats_perm']);
	}

	public function cat_auth_comment_read($cat_id)
	{
		$dl_index = $this->dlext_cache->obtain_dl_cats();

		return $this->_check_perm($dl_index, $dl_index[$cat_id]['auth_cread'], $cat_id);
	}

	public function cat_auth_comment_post($cat_id)
	{
		$dl_index = $this->dlext_cache->obtain_dl_cats();

		return $this->_check_perm($dl_index, $dl_index[$cat_id]['auth_cpost'], $cat_id);
	}

	private function _check_perm($dl_index, $perm_value, $cat_id = 0)
	{
		$dl_perm = $this->dlext_constants::DL_FALSE;

		switch ($perm_value)
		{
			case $this->dlext_constants::DL_PERM_ALL:
				$dl_perm = $this->dlext_constants::DL_TRUE;
				break;

			case $this->dlext_constants::DL_PERM_USER:
				if ($this->user->data['is_registered'])
				{
					$dl_perm = $this->dlext_constants::DL_TRUE;
				}
				break;

			case $this->dlext_constants::DL_PERM_MOD:
				if ($cat_id)
				{
					if ($this->user_auth($cat_id, 'auth_mod'))
					{
						$dl_perm = $this->dlext_constants::DL_TRUE;
					}
				}
				else
				{
					foreach (array_keys($dl_index) as $key)
					{
						if ($this->user_auth($dl_index[$key]['id'], 'auth_mod'))
						{
							$dl_perm = $this->dlext_constants::DL_TRUE;
							break;
						}
					}
				}
				break;

			case $this->dlext_constants::DL_PERM_ADMIN:
				if ($this->user_admin())
				{
					$dl_perm = $this->dlext_constants::DL_TRUE;
				}
				break;
		}

		return $dl_perm;
	}

	public function dl_auth_users($cat_id, $perm)
	{
		$dl_index = $this->dlext_cache->obtain_dl_cats();
		$user_ids = [];

		if (!is_array($dl_index) || empty($dl_index))
		{
			return $user_ids;
		}

		if ($dl_index[$cat_id][$perm])
		{
			$sql = 'SELECT user_id FROM ' . USERS_TABLE . '
				WHERE user_id <> ' . ANONYMOUS . '
					AND user_type <> ' . USER_IGNORE . '
					AND user_id <> ' . (int) $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);
		}
		else
		{
			$sql = 'SELECT group_id FROM ' . $this->dlext_table_dl_auth . '
				WHERE cat_id = ' . (int) $cat_id . '
					AND ' . $this->db->sql_escape($perm) . ' = ' . $this->dlext_constants::DL_TRUE . '
				GROUP BY group_id';
			$result = $this->db->sql_query($sql);
			$total_group_perms = $this->db->sql_affectedrows();

			if (!$total_group_perms)
			{
				$this->db->sql_freeresult($result);
				return $user_ids;
			}

			$group_ids = [];

			while ($row = $this->db->sql_fetchrow($result))
			{
				$group_ids[] = $row['group_id'];
			}

			$this->db->sql_freeresult($result);

			if (empty($group_ids))
			{
				return $user_ids;
			}

			$sql = 'SELECT user_id FROM ' . USER_GROUP_TABLE . '
				WHERE user_id <> ' . (int) $this->user->data['user_id'] . '
					AND ' . $this->db->sql_in_set('group_id', array_map('intval', $group_ids)) . '
					AND user_pending <> 1';
			$result = $this->db->sql_query($sql);
		}

		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_ids[] = $row['user_id'];
		}

		$this->db->sql_freeresult($result);

		return array_unique($user_ids);
	}

	public function bug_tracker()
	{
		$dl_index = $this->dlext_cache->obtain_dl_cats();
		$bug_tracker = $this->dlext_constants::DL_FALSE;

		if (!is_array($dl_index) || empty($dl_index))
		{
			return $bug_tracker;
		}

		foreach (array_keys($dl_index) as $cat_id)
		{
			if (isset($dl_index[$cat_id]['bug_tracker']) && $dl_index[$cat_id]['bug_tracker'])
			{
				$bug_tracker = $this->dlext_constants::DL_TRUE;
				break;
			}
		}

		if ($bug_tracker)
		{
			$sql = 'SELECT count(d.id) as total
					FROM ' . $this->dlext_table_downloads . ' d, ' . $this->dlext_table_dl_cat . ' c
					WHERE c.id = d.cat
						AND c.bug_tracker = 1';
			$result = $this->db->sql_query($sql);
			$total = $this->db->sql_fetchfield('total');
			$this->db->sql_freeresult($result);

			if ($total == 0)
			{
				$bug_tracker = $this->dlext_constants::DL_FALSE;
			}
		}

		return $bug_tracker;
	}

	public function get_captcha_status($captcha_config, $cat_id)
	{
		$user_is_guest		= $this->dlext_constants::DL_FALSE;
		$user_is_mod		= $this->dlext_constants::DL_FALSE;
		$captcha_active		= $this->dlext_constants::DL_FALSE;
		$user_is_admin		= $this->dlext_constants::DL_FALSE;
		$user_is_founder	= $this->dlext_constants::DL_FALSE;

		if (!$this->user->data['is_registered'])
		{
			$user_is_guest = $this->dlext_constants::DL_TRUE;
		}
		else
		{
			$cat_auth_tmp = $this->dl_cat_auth($cat_id);

			if ($cat_auth_tmp['auth_mod'] || $this->user_admin())
			{
				$user_is_mod = $this->dlext_constants::DL_TRUE;
			}

			if ($this->user_admin())
			{
				$user_is_admin = $this->dlext_constants::DL_TRUE;
			}

			if ($this->user->data['user_type'] == USER_FOUNDER)
			{
				$user_is_founder = $this->dlext_constants::DL_TRUE;
			}
		}

		switch ($captcha_config)
		{
			case $this->dlext_constants::DL_CAPTCHA_PERM_OFF:
				$captcha_active = $this->dlext_constants::DL_FALSE;
				break;
			case $this->dlext_constants::DL_CAPTCHA_PERM_GUESTS:
				if (!$user_is_guest)
				{
					$captcha_active = $this->dlext_constants::DL_FALSE;
				}
				break;
			case $this->dlext_constants::DL_CAPTCHA_PERM_USER:
				if ($user_is_mod || $user_is_admin || $user_is_founder)
				{
					$captcha_active = $this->dlext_constants::DL_FALSE;
				}
				break;
			case $this->dlext_constants::DL_CAPTCHA_PERM_MODS:
				if ($user_is_admin || $user_is_founder)
				{
					$captcha_active = $this->dlext_constants::DL_FALSE;
				}
				break;
			case $this->dlext_constants::DL_CAPTCHA_PERM_ADMINS:
				if ($user_is_founder)
				{
					$captcha_active = $this->dlext_constants::DL_FALSE;
				}
				break;
			case $this->dlext_constants::DL_CAPTCHA_PERM_ALL:
			default:
				$captcha_active = $this->dlext_constants::DL_TRUE;
				break;
		}

		return $captcha_active;
	}
}
