<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

class cache implements cache_interface
{
	/* phpbb objects */
	protected $cache;
	protected $db;

	/* extension owned objects */
	protected $dlext_constants;

	protected $dlext_table_dl_auth;
	protected $dlext_table_dl_cat_traf;
	protected $dlext_table_dl_ext_blacklist;
	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\service					$cache
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_auth
	 * @param string								$dlext_table_dl_cat_traf
	 * @param string								$dlext_table_dl_ext_blacklist
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		\phpbb\cache\service $cache,
		\phpbb\db\driver\driver_interface $db,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_auth,
		$dlext_table_dl_cat_traf,
		$dlext_table_dl_ext_blacklist,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->cache			= $cache;
		$this->db 				= $db;

		$this->dlext_constants 	= $dlext_constants;

		$this->dlext_table_dl_auth			= $dlext_table_dl_auth;
		$this->dlext_table_dl_cat_traf		= $dlext_table_dl_cat_traf;
		$this->dlext_table_dl_ext_blacklist	= $dlext_table_dl_ext_blacklist;
		$this->dlext_table_downloads		= $dlext_table_downloads;
		$this->dlext_table_dl_cat			= $dlext_table_dl_cat;
	}

	/**
	 * Download Extension Category Cache
	 */
	public function obtain_dl_cats()
	{
		if (($dl_index = $this->cache->get('_dlext_cats')) === false)
		{
			$sql_array = [
				'SELECT'	=> 'c.*, t.cat_traffic_use',
				'FROM'		=> [$this->dlext_table_dl_cat => 'c'],
			];
			$sql_array['LEFT_JOIN'] = [];
			$sql_array['LEFT_JOIN'][] = [
				'FROM'	=> [$this->dlext_table_dl_cat_traf => 't'],
				'ON'	=> 't.cat_id = c.id'
			];
			$sql_array['ORDER_BY'] = 'parent, sort';

			$sql = $this->db->sql_build_query('SELECT', $sql_array);

			$result = $this->db->sql_query($sql);

			$dl_index = [];

			while ($row = $this->db->sql_fetchrow($result))
			{
				$dl_index[$row['id']] = $row;

				$dl_index[$row['id']]['auth_view_real'] = $dl_index[$row['id']]['auth_view'];
				$dl_index[$row['id']]['auth_dl_real'] = $dl_index[$row['id']]['auth_dl'];
				$dl_index[$row['id']]['auth_up_real'] = $dl_index[$row['id']]['auth_up'];
				$dl_index[$row['id']]['auth_mod_real'] = $dl_index[$row['id']]['auth_mod'];
				$dl_index[$row['id']]['cat_name_nav'] = $dl_index[$row['id']]['cat_name'];
			}

			$this->db->sql_freeresult($result);

			$this->cache->put('_dlext_cats', $dl_index);
		}

		return $dl_index;
	}

	/**
	 * Download Extension Blacklist Cache
	 */
	public function obtain_dl_blacklist()
	{
		if (($dl_black = $this->cache->get('_dlext_black')) === false)
		{
			$sql = 'SELECT extention FROM ' . $this->dlext_table_dl_ext_blacklist . '
					ORDER BY extention';
			$result = $this->db->sql_query($sql);

			$dl_black = [];

			while ($row = $this->db->sql_fetchrow($result))
			{
				$dl_black[] = $row['extention'];
			}

			$this->db->sql_freeresult($result);

			$this->cache->put('_dlext_black', $dl_black);
		}

		return $dl_black;
	}

	/**
	 * Download Extension Cat Filecount Cache
	 */
	public function obtain_dl_cat_counts()
	{
		if (($dl_cat_counts = $this->cache->get('_dlext_cat_counts')) === false)
		{
			$sql = 'SELECT COUNT(id) AS total, cat FROM ' . $this->dlext_table_downloads . '
				GROUP BY cat';
			$result = $this->db->sql_query($sql);

			$dl_cat_counts = [];

			while ($row = $this->db->sql_fetchrow($result))
			{
				$dl_cat_counts[$row['cat']] = $row['total'];
			}

			$this->db->sql_freeresult($result);

			$this->cache->put('_dlext_cat_counts', $dl_cat_counts);
		}

		return $dl_cat_counts;
	}

	/**
	 * Download Extension Files Cache
	 */
	public function obtain_dl_files($dl_new_time, $dl_edit_time)
	{
		if (!$dl_new_time && !$dl_edit_time)
		{
			return [];
		}

		$dl_new_time		= (int) $dl_new_time;
		$dl_edit_time		= (int) $dl_edit_time;

		$cache_release_time = $this->dlext_constants::DL_ONE_DAY;

		if (($dl_file = $this->cache->get('_dlext_file_preset')) === false)
		{
			$cur_time = time();
			$sql_time_preset = '';

			if ($dl_new_time && $dl_edit_time)
			{
				$sql_time_preset = " AND ((add_time = change_time AND (($cur_time - change_time) / $cache_release_time <= $dl_new_time)) OR ";
				$sql_time_preset .= " (add_time <> change_time AND (($cur_time - change_time) / $cache_release_time <= $dl_edit_time))) ";
			}

			if ($dl_new_time && !$dl_edit_time)
			{
				$sql_time_preset = " AND (add_time = change_time AND (($cur_time - change_time) / $cache_release_time <= $dl_new_time)) ";
			}

			if (!$dl_new_time && $dl_edit_time)
			{
				$sql_time_preset = " AND (add_time <> change_time AND (($cur_time - change_time) / $cache_release_time <= $dl_edit_time)) ";
			}

			$sql = 'SELECT id, cat, add_time, change_time FROM ' . $this->dlext_table_downloads . '
					WHERE approve = 1' . (string) $sql_time_preset;
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$dl_id = $row['id'];
				$cat_id = $row['cat'];
				$change_time = $row['change_time'];
				$add_time = $row['add_time'];

				if (!isset($dl_file['new'][$cat_id]))
				{
					$dl_file['new'][$cat_id][$dl_id] = 0;
				}
				if (!isset($dl_file['new_sum'][$cat_id]))
				{
					$dl_file['new_sum'][$cat_id] = 0;
				}
				if (!isset($dl_file['edit'][$cat_id]))
				{
					$dl_file['edit'][$cat_id][$dl_id] = 0;
				}
				if (!isset($dl_file['edit_sum'][$cat_id]))
				{
					$dl_file['edit_sum'][$cat_id] = 0;
				}

				$count_new = ($change_time == $add_time && ((time() - $change_time)) / $cache_release_time <= $dl_new_time && $dl_new_time > 0) ? 1 : 0;
				$count_edit = ($change_time != $add_time && ((time() - $change_time) / $cache_release_time) <= $dl_edit_time && $dl_edit_time > 0) ? 1 : 0;

				$dl_file['new'][$cat_id][$dl_id] = $count_new;
				$dl_file['new_sum'][$cat_id] += $count_new;
				$dl_file['edit'][$cat_id][$dl_id] = $count_edit;
				$dl_file['edit_sum'][$cat_id] += $count_edit;
			}

			$this->db->sql_freeresult($result);

			$this->cache->put('_dlext_file_preset', $dl_file, $cache_release_time);
		}

		return $dl_file;
	}

	/**
	 * Download Extension Auth Cache
	 */
	public function obtain_dl_auth()
	{
		$auth_cat		= [];
		$group_perm_ids	= [];
		$auth_perm		= [];

		if (($dl_auth_perm = $this->cache->get('_dlext_auth')) === false)
		{
			$sql = 'SELECT * FROM ' . $this->dlext_table_dl_auth;
			$result = $this->db->sql_query($sql);

			$total_perms = $this->db->sql_affectedrows();

			if ($total_perms)
			{
				while ($row = $this->db->sql_fetchrow($result))
				{
					$cat_id = $row['cat_id'];
					$group_id = $row['group_id'];

					$auth_cat[] = $cat_id;
					$group_perm_ids[] = $group_id;

					$auth_perm[$cat_id][$group_id]['auth_view'] = $row['auth_view'];
					$auth_perm[$cat_id][$group_id]['auth_dl'] = $row['auth_dl'];
					$auth_perm[$cat_id][$group_id]['auth_up'] = $row['auth_up'];
					$auth_perm[$cat_id][$group_id]['auth_mod'] = $row['auth_mod'];
				}

				$this->db->sql_freeresult($result);

				if ($total_perms > 1)
				{
					$auth_cat = array_unique($auth_cat);
					$group_perm_ids = array_unique($group_perm_ids);
					sort($auth_cat);
					sort($group_perm_ids);
				}
			}

			$dl_auth_perm['auth_cat'] = $auth_cat;
			$dl_auth_perm['group_perm_ids'] = $group_perm_ids;
			$dl_auth_perm['auth_perm'] = $auth_perm;

			$this->cache->put('_dlext_auth', $dl_auth_perm);
		}

		return $dl_auth_perm;
	}

	/**
	 * Download Extension Auth Group Settings Cache
	 */
	public function obtain_dl_access_groups($auth_cat, $user_id, $auth_perm, $group_perm_ids = [])
	{
		$cat_auth_array	= [];

		if (($dl_auth_groups = $this->cache->get('_dlext_auth_groups')) === false)
		{
			$sql = 'SELECT user_id, group_id FROM ' . USER_GROUP_TABLE . '
				WHERE ' . $this->db->sql_in_set('group_id', array_map('intval', $group_perm_ids)) . '
					AND user_pending <> 1';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$dl_auth_groups[$row['user_id']][]	= $row['group_id'];
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('_dlext_auth_groups', $dl_auth_groups);
		}

		if (!isset($dl_auth_groups[$user_id]) || !$dl_auth_groups[$user_id][0])
		{
			return [];
		}

		$group_ids = $dl_auth_groups[$user_id];

		for ($i = 0; $i < count($auth_cat); ++$i)
		{
			$auth_view = $auth_dl = $auth_up = $auth_mod = 0;
			$cat = $auth_cat[$i];

			for ($j = 0; $j < count($group_ids); ++$j)
			{
				$user_group = $group_ids[$j];

				if (isset($auth_perm[$cat][$user_group]['auth_view']) && $auth_perm[$cat][$user_group]['auth_view'] == 1)
				{
					$auth_view = 1;
				}
				if (isset($auth_perm[$cat][$user_group]['auth_dl']) && $auth_perm[$cat][$user_group]['auth_dl'] == 1)
				{
					$auth_dl = 1;
				}
				if (isset($auth_perm[$cat][$user_group]['auth_up']) && $auth_perm[$cat][$user_group]['auth_up'] == 1)
				{
					$auth_up = 1;
				}
				if (isset($auth_perm[$cat][$user_group]['auth_mod']) && $auth_perm[$cat][$user_group]['auth_mod'] == 1)
				{
					$auth_mod = 1;
				}
			}

			$cat_auth_array[$cat]['auth_view'] = $auth_view;
			$cat_auth_array[$cat]['auth_dl'] = $auth_dl;
			$cat_auth_array[$cat]['auth_up'] = $auth_up;
			$cat_auth_array[$cat]['auth_mod'] = $auth_mod;
		}

		return $cat_auth_array;
	}

	/**
	 * Download Extension File preload
	 */
	public function obtain_dl_file_p()
	{
		if (($dl_file_p = $this->cache->get('_dlext_file_p')) === false)
		{
			$sql = 'SELECT id as i, cat as c, file_size as s, extern as e, free as f, file_traffic as t, klicks as k 
					FROM ' . $this->dlext_table_downloads . '
					WHERE approve = 1';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$dl_file_p[$row['i']] = $row;
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('_dlext_file_p', $dl_file_p);
		}

		return $dl_file_p;
	}
}
