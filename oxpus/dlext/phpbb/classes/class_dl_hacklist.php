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

class dl_hacklist extends dl_mod
{
	/*
	* init basic variables
	*/
	var $dl_auth = array();
	var $user_id = 0;
	var $user_admin = false;

	/*
	* run the class constructor
	*/
	public function __construct()
	{
		global $db, $user, $config, $auth;

		$user_id = ($user->data['user_perm_from']) ? $user->data['user_perm_from'] : $user->data['user_id'];
		$user_admin = ($auth->acl_get('a_') && $user->data['is_registered'] && !$user->data['user_perm_from']) ? true : false;

		/*
		* get the user permissions, if needed
		*/
		$auth_perm = $auth_cat = $cat_auth_array = $group_ids = $group_perm_ids = array();

		if ($config['dl_use_hacklist'])
		{
			$sql = 'SELECT * FROM ' . DL_AUTH_TABLE;
			$result = $db->sql_query($sql);

			$total_perms = $db->sql_affectedrows($result);

			if ($total_perms)
			{
				while ($row = $db->sql_fetchrow($result))
				{
					$cat_id = $row['cat_id'];
					$group_id = $row['group_id'];

					$auth_cat[] = $cat_id;
					$group_perm_ids[] = $group_id;

					$auth_perm[$cat_id][$group_id]['auth_view'] = $row['auth_view'];
				}
				$db->sql_freeresult($result);

				if ($total_perms > 1)
				{
					$auth_cat = array_unique($auth_cat);
					sort($auth_cat);
				}

				$sql = 'SELECT g.group_id FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . " ug
					WHERE " . $db->sql_in_set('g.group_id', array_map('intval', $group_perm_ids)) . '
						AND g.group_id = ug.group_id
						AND ug.user_id = ' . (int) $user_id . '
						AND ug.user_pending <> ' . true;
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$group_ids[] = $row['group_id'];
				}
				$db->sql_freeresult($result);

				for ($i = 0; $i < sizeof($auth_cat); $i++)
				{
					$auth_view = 0;
					$cat = $auth_cat[$i];

					for ($j = 0; $j < sizeof($group_ids); $j++)
					{
						$user_group = $group_ids[$j];

						if (isset($auth_perm[$cat][$user_group]['auth_view']) && $auth_perm[$cat][$user_group]['auth_view'] == true)
						{
							$auth_view = true;
						}
					}

					$cat_auth_array[$cat]['auth_view'] = $auth_view;
				}
			}
			else
			{
				$db->sql_freeresult($result);
			}
		}

		$dl_auth = $cat_auth_array;

		return;
	}

	public static function hacks_index()
	{
		static $dl_auth, $user_admin;
		global $db, $auth, $user, $dl_auth, $user_admin;

		$tree_dl = array();

		$sql = 'SELECT id, cat_name, auth_view FROM ' . DL_CAT_TABLE . '
			ORDER BY parent, sort';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$cat_id = $row['id'];

			if ($row['auth_view'] || (isset($dl_auth[$cat_id]['auth_view']) && $dl_auth[$cat_id]['auth_view']) || $user_admin)
			{
				$tree_dl[$cat_id] = $row['cat_name'];
			}
		}

		return $tree_dl;
	}

	public static function all_files($sql_sort_by, $sql_order, $start = 0, $total = 0)
	{
		global $db;

		$dl_files = array();

		$sql = 'SELECT * FROM ' . DOWNLOADS_TABLE . '
			WHERE approve = ' . true . '
				AND hacklist = 1
			ORDER BY ' . $db->sql_escape($sql_sort_by) . ' ' . $db->sql_escape($sql_order);
		if ($total)
		{
			$result = $db->sql_query_limit($sql, $total, $start);
		}
		else
		{
			$result = $db->sql_query($sql);

			$total = $db->sql_affectedrows($result);
			$db->sql_freeresult($result);

			return $total;
		}

		while ($row = $db->sql_fetchrow($result))
		{
			$dl_files[] = $row;
		}
		$db->sql_freeresult($result);

		return $dl_files;
	}
}
