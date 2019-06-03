<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\phpbb\classes;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class dl_auth extends dl_mod
{
	public static function dl_cat_auth($cat_id)
	{
		static $dl_auth;
		global $dl_auth;

		$cat_perm = array();

		$cat_perm['auth_view'] = (isset($dl_auth[$cat_id]['auth_view'])) ? intval($dl_auth[$cat_id]['auth_view']) : 0;
		$cat_perm['auth_dl'] = (isset($dl_auth[$cat_id]['auth_dl'])) ? intval($dl_auth[$cat_id]['auth_dl']) : 0;
		$cat_perm['auth_mod'] = (isset($dl_auth[$cat_id]['auth_mod'])) ? intval($dl_auth[$cat_id]['auth_mod']) : 0;
		$cat_perm['auth_up'] = (isset($dl_auth[$cat_id]['auth_up'])) ? intval($dl_auth[$cat_id]['auth_up']) : 0;
		$cat_perm['auth_cread'] = (isset($dl_auth[$cat_id]['auth_cread'])) ? intval($dl_auth[$cat_id]['auth_cread']) : 0;
		$cat_perm['auth_cpost'] = (isset($dl_auth[$cat_id]['auth_cpost'])) ? intval($dl_auth[$cat_id]['auth_cpost']) : 0;

		return $cat_perm;
	}

	public static function user_admin()
	{
		static $user_admin;
		global $user_admin;

		return $user_admin;
	}

	public static function user_banned()
	{
		static $user_banned;
		global $user_banned;

		return $user_banned;
	}

	public static function get_ext_blacklist()
	{
		static $ext_blacklist;
		global $ext_blacklist;

		return $ext_blacklist;
	}

	public static function user_auth($cat_id, $perm)
	{
		static $dl_auth, $dl_index, $user_admin;
		global $dl_auth, $dl_index, $user_admin;

		if ((isset($dl_auth[$cat_id][$perm]) && $dl_auth[$cat_id][$perm]) || (isset($dl_index[$cat_id][$perm]) && $dl_index[$cat_id][$perm]) || $user_admin)
		{
			return true;
		}

		return false;
	}

	public static function stats_perm()
	{
		static $user_logged_in, $dl_index, $user_auth, $user_admin;

		global $config;
		global $user_logged_in, $dl_index, $user_auth, $user_admin;

		$stats_view = 0;

		switch($config['dl_stats_perm'])
		{
			case 0:
				$stats_view = true;
				break;

			case 1:
				if ($user_logged_in)
				{
					$stats_view = true;
				}
				break;

			case 2:
				foreach ($dl_index as $key => $value)
				{
					if (self::user_auth($dl_index[$key]['id'], 'auth_mod'))
					{
						$stats_view = true;
						break;
					}
				}
				break;

			case 3:
				if ($user_admin)
				{
					$stats_view = true;
				}
				break;

			default:
				$stats_view = 0;
		}

		return $stats_view;
	}

	public static function cat_auth_comment_read($cat_id)
	{
		static $dl_index, $user_logged_in, $user_admin;

		global $dl_index, $user_logged_in, $user_admin;

		$auth_cread = 0;

		switch($dl_index[$cat_id]['auth_cread'])
		{
			case 0:
				$auth_cread = true;
				break;

			case 1:
				if ($user_logged_in)
				{
					$auth_cread = true;
				}
				break;

			case 2:
				if (self::user_auth($cat_id, 'auth_mod'))
				{
					$auth_cread = true;
				}
				break;

			case 3:
				if ($user_admin)
				{
					$auth_cread = true;
				}
				break;

			default:
				$auth_cread = 0;
		}

		return $auth_cread;
	}

	public static function cat_auth_comment_post($cat_id)
	{
		static $dl_index, $user_logged_in, $user_admin;

		global $dl_index, $user_logged_in, $user_admin;

		$auth_cpost = 0;

		switch($dl_index[$cat_id]['auth_cpost'])
		{
			case 0:
				$auth_cpost = true;
				break;

			case 1:
				if ($user_logged_in)
				{
					$auth_cpost = true;
				}
				break;

			case 2:
				if (self::user_auth($cat_id, 'auth_mod'))
				{
					$auth_cpost = true;
				}
				break;

			case 3:
				if ($user_admin)
				{
					$auth_cpost = true;
				}
				break;

			default:
				$auth_cpost = 0;
		}

		return $auth_cpost;
	}

	public static function dl_auth_users($cat_id, $perm)
	{
		static $dl_index, $user_id;

		global $db;
		global $dl_index, $user_id;

		if (!is_array($dl_index) || !sizeof($dl_index))
		{
			return 0;
		}

		$user_ids = 0;

		if ($dl_index[$cat_id][$perm])
		{
			$sql = 'SELECT user_id FROM ' . USERS_TABLE . '
				WHERE user_id <> ' . ANONYMOUS . '
					AND user_id <> ' . (int) $user_id;
			$result = $db->sql_query($sql);
		}
		else
		{
			$sql = 'SELECT group_id FROM ' . DL_AUTH_TABLE . '
				WHERE cat_id = ' . (int) $cat_id . '
					AND ' . $db->sql_escape($perm) . ' = ' . true . '
				GROUP BY group_id';
			$result = $db->sql_query($sql);
			$total_group_perms = $db->sql_affectedrows($result);

			if (!$total_group_perms)
			{
				$db->sql_freeresult($result);
				return 0;
			}

			$group_ids = array();

			while ($row = $db->sql_fetchrow($result))
			{
				$group_ids[] = $row['group_id'];
			}

			$db->sql_freeresult($result);

			if (!sizeof($group_ids))
			{
				return 0;
			}

			$sql = 'SELECT user_id FROM ' . USER_GROUP_TABLE . '
				WHERE user_id <> ' . (int) $user_id . '
					AND ' . $db->sql_in_set('group_id', array_map('intval', $group_ids)) . '
					AND user_pending <> ' . true;
			$result = $db->sql_query($sql);

		}

		while ($row = $db->sql_fetchrow($result))
		{
			$user_ids .= ', ' . $row['user_id'];
		}
		$db->sql_freeresult($result);

		return $user_ids;
	}

	public static function bug_tracker()
	{
		static $dl_index;

		global $dl_index;

		if (!is_array($dl_index) || !sizeof($dl_index))
		{
			return false;
		}

		$bug_tracker = false;

		foreach($dl_index as $cat_id => $value)
		{
			if (isset($dl_index[$cat_id]['bug_tracker']) && $dl_index[$cat_id]['bug_tracker'])
			{
				$bug_tracker = true;
				break;
			}
		}

		if ($bug_tracker)
		{
			global $db;

			$sql = 'SELECT count(d.id) as total FROM ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . ' c
			WHERE c.id = d.cat
				AND c.bug_tracker = 1';
			$result = $db->sql_query($sql);
			$total = $db->sql_fetchfield('total');

			if ($total == 0)
			{
				$bug_tracker = false;
			}
		}

		return $bug_tracker;
	}
}
