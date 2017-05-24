<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\includes\classes;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class dl_main extends dl_mod
{
	public static function full_index($helper = '', $only_cat = 0, $parent = 0, $level = 0, $auth_level = 0)
	{
		static $dl_index, $dl_auth, $user_admin;
		global $tree_dl, $access_ids, $phpEx;
		global $dl_index, $dl_auth, $user_admin;

		if (!is_array($dl_index) || !sizeof($dl_index))
		{
			return array();
		}

		if ($only_cat > 0)
		{
			$tree_dl[$only_cat] = $dl_index[$only_cat];
			$tree_dl[$only_cat]['nav_path'] = ($helper) ? $helper->route('oxpus_dlext_controller', array('cat' => $only_cat)) : 'app.' . $phpEx . '/dlext/?cat=' . $only_cat;
			$tree_dl[$only_cat]['cat_path'] = $dl_index[$only_cat]['path'];
			$tree_dl[$only_cat]['cat_name_nav'] = $dl_index[$only_cat]['cat_name'];
		}
		else
		{
			if ($auth_level)
			{
				unset($access_ids);
				$access_ids = array();
			}

			foreach($dl_index as $cat_id => $value)
			{
				if ((isset($value['auth_view']) && $value['auth_view']) || (isset($dl_auth[$cat_id]['auth_view']) && $dl_auth[$cat_id]['auth_view']) || $user_admin)
				{
					/*
					* $auth level will return the following data
					* 0 = Default values for each category
					* 1 = IDs from all viewable categories
					* 2 = IDs from moderated categories
					* 3 = IDs from upload categories
					*/

					if ($auth_level == 1 && isset($value['id']) && $value['id'] && ($value['auth_view'] || (isset($dl_auth[$cat_id]['auth_view']) && $dl_auth[$cat_id]['auth_view']) || $user_admin))
					{
						$access_ids[] = $cat_id;
					}
					else if ($auth_level == 2 && isset($value['id']) && $value['id'] && ((isset($value['auth_mod']) && $value['auth_mod']) || (isset($dl_auth[$cat_id]['auth_mod']) && $dl_auth[$cat_id]['auth_mod']) || $user_admin))
					{
						$access_ids[] = $cat_id;
					}
					else if ($auth_level == 3 && isset($value['id']) && $value['id'] && ((isset($value['auth_up']) && $value['auth_up']) || (isset($dl_auth[$cat_id]['auth_up']) && $dl_auth[$cat_id]['auth_up']) || $user_admin))
					{
						$access_ids[] = $cat_id;
					}
					else if (isset($value['parent']) && $value['parent'] == $parent)
					{
						$seperator = '';
						for ($i = 0; $i < $level; $i++)
						{
							$seperator .= ($value['parent'] != 0) ? '&nbsp;&nbsp;|___&nbsp;' : '';
						}

						$tree_dl[$cat_id] = $value;
						$tree_dl[$cat_id]['nav_path'] = ($helper) ? $helper->route('oxpus_dlext_controller', array('cat' => $cat_id)) : 'app.' . $phpEx . '/dlext/?cat=' . $cat_id;
						$tree_dl[$cat_id]['cat_path'] = $value['path'];
						$tree_dl[$cat_id]['cat_name'] = $seperator . $value['cat_name'];
						$tree_dl[$cat_id]['cat_name_nav'] = $value['cat_name'];

						$level++;
						self::full_index($helper, 0, $cat_id, $level, 0);
						$level--;
					}
				}
			}
		}

		return (isset($auth_level) && $auth_level <> 0) ? $access_ids : $tree_dl;
	}

	public static function index($helper = '', $parent = 0)
	{
		static $dl_index, $dl_auth, $user_admin;
		global $dl_index, $dl_auth, $user_admin, $phpEx;

		if (!is_array($dl_index) || !sizeof($dl_index))
		{
			return array();
		}

		$tree_dl = array();

		foreach($dl_index as $cat_id => $value)
		{
			if (((isset($value['auth_view']) && $value['auth_view']) || (isset($dl_auth[$cat_id]['auth_view']) && $dl_auth[$cat_id]['auth_view']) || $user_admin) && (isset($value['parent']) && $value['parent'] == $parent))
			{
				$tree_dl[$cat_id] = $value;
				$tree_dl[$cat_id]['nav_path'] = ($helper) ? $helper->route('oxpus_dlext_controller', array('cat' => $cat_id)) : 'app.' . $phpEx . '/dlext/?cat=' . $cat_id;
				$tree_dl[$cat_id]['cat_path'] = $value['path'];
				$tree_dl[$cat_id]['cat_name_nav'] = $value['cat_name'];
				$tree_dl[$cat_id]['sublevel'] = self::get_sublevel($helper, $cat_id);
			}
		}
		return $tree_dl;
	}

	public static function get_sublevel($helper = '', $parent = 0)
	{
		static $dl_index, $dl_auth, $user_admin;
		global $dl_index, $dl_auth, $user_admin, $phpEx;

		if (!is_array($dl_index) || !sizeof($dl_index))
		{
			return array();
		}

		$sublevel = array();
		$i = 0;

		foreach($dl_index as $cat_id => $value)
		{
			if (((isset($dl_index[$cat_id]['auth_view']) && $dl_index[$cat_id]['auth_view']) || (isset($dl_auth[$cat_id]['auth_view']) && $dl_auth[$cat_id]['auth_view']) || $user_admin) && (isset($dl_index[$cat_id]['parent']) && $dl_index[$cat_id]['parent'] == $parent))
			{
				$sublevel['cat_name'][$i] = $dl_index[$cat_id]['cat_name'];
				$sublevel['total'][$i] = (isset($dl_index[$cat_id]['total'])) ? $dl_index[$cat_id]['total'] : 0;
				$sublevel['cat_id'][$i] = $dl_index[$cat_id]['id'];
				$sublevel['cat_path'][$i] = ($helper) ? $helper->route('oxpus_dlext_controller', array('cat' => $cat_id)) : 'app.' . $phpEx . '/dlext/?cat=' . $cat_id;
				$sublevel['cat_sub'][$i] = $cat_id;

				$sublevel['description'][$i] = (isset($dl_index[$cat_id]['description'])) ? $dl_index[$cat_id]['description'] : '';
				$sublevel['desc_uid'][$i] = (isset($dl_index[$cat_id]['desc_uid'])) ? $dl_index[$cat_id]['desc_uid'] : '';
				$sublevel['desc_bitfield'][$i] = (isset($dl_index[$cat_id]['desc_bitfield'])) ? $dl_index[$cat_id]['desc_bitfield'] : '';
				$sublevel['desc_flags'][$i] = (isset($dl_index[$cat_id]['desc_flags'])) ? $dl_index[$cat_id]['desc_flags'] : '';
				$i++;
			}
		}

		return $sublevel;
	}

	public static function get_sublevel_count($parent = 0)
	{
		static $dl_index, $dl_auth, $user_admin;
		global $dl_index, $dl_auth, $user_admin;

		if (!is_array($dl_index) || !sizeof($dl_index))
		{
			return 0;
		}

		$sublevel_count = 0;

		foreach($dl_index as $cat_id => $value)
		{
			if (isset($dl_index[$cat_id]['parent']) && $dl_index[$cat_id]['parent'] == $parent && (isset($dl_index[$cat_id]['auth_view']) || isset($dl_auth[$cat_id]['auth_view']) || $user_admin))
			{
				$sublevel_count += (isset($dl_index[$cat_id]['total'])) ? $dl_index[$cat_id]['total'] : 0;
				$sublevel_count += self::get_sublevel_count($cat_id);
			}
		}

		return $sublevel_count;
	}

	public static function count_sublevel($parent)
	{
		static $dl_index, $dl_auth, $user_admin;
		global $dl_index, $dl_auth, $user_admin;

		if (!is_array($dl_index) || !sizeof($dl_index))
		{
			return 0;
		}

		$sublevel = 0;

		foreach($dl_index as $cat_id => $value)
		{
			if ((isset($dl_index[$cat_id]['auth_view']) || isset($dl_auth[$cat_id]['auth_view']) || $user_admin) && (isset($dl_index[$cat_id]['parent']) && $dl_index[$cat_id]['parent'] == $parent))
			{
				$sublevel++;
			}
		}

		return $sublevel;
	}

	public static function find_latest_dl($last_data, $parent, $main_cat, $last_dl_time)
	{
		static $dl_index, $dl_auth, $user_admin;
		global $dl_index, $dl_auth, $user_admin;

		foreach($last_data as $cat_id => $value)
		{
			if ($last_data[$cat_id]['parent'] == $parent || $main_cat == $cat_id)
			{
				$last_cat_time = (isset($last_data[$cat_id]['change_time'])) ? $last_data[$cat_id]['change_time'] : 0;
				$last_dl_times = (isset($last_dl_time['change_time'])) ? $last_dl_time['change_time'] : 0;

				if ($last_cat_time > $last_dl_times && ((isset($dl_index[$cat_id]['auth_view']) && $dl_index[$cat_id]['auth_view']) || (isset($dl_auth[$cat_id]['auth_view']) && $dl_auth[$cat_id]['auth_view']) || $user_admin))
				{
					$last_dl_time['change_time'] = $last_cat_time;
					$last_dl_time['cat_id'] = $cat_id;
				}

				$last_temp = self::find_latest_dl($last_data, $cat_id, -1, $last_dl_time);
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

	public static function dl_prune_stats($cat_id, $stats_prune)
	{
		global $db;

		$stats_prune--;

		if ($stats_prune)
		{
			$sql = 'SELECT time_stamp FROM ' . DL_STATS_TABLE . '
				WHERE cat_id = ' . (int) $cat_id . '
				ORDER BY time_stamp DESC';
			$result = $db->sql_query_limit($sql, 1, $stats_prune);
			$first_time_stamp = $db->sql_fetchfield('time_stamp');
			$db->sql_freeresult($result);

			if ($first_time_stamp)
			{
				$sql = 'DELETE FROM ' . DL_STATS_TABLE . '
					WHERE time_stamp <= ' . (int) $first_time_stamp . '
						AND cat_id = ' . (int) $cat_id;
				$db->sql_query($sql);
			}
		}

		return true;
	}
}
