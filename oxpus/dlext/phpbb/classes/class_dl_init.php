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

class dl_init extends dl_mod
{
	public static function phpbb_root_path()
	{
		global $phpbb_root_path;
		return $phpbb_root_path;
	}

	public static function phpEx()
	{
		global $phpEx;
		return '.' . $phpEx;
	}

	public static function init($ext_path)
	{
		static $user_id, $user_regdate, $user_dl_update_time, $user_traffic, $user_posts, $user_client, $username, $user_ip;
		static $user_admin, $user_logged_in, $user_banned;
		static $ext_blacklist, $dl_index, $dl_auth, $dl_file_p, $dl_file_icon;

		global $user_id, $user_regdate, $user_dl_update_time, $user_traffic, $user_posts, $user_client, $username, $user_ip;
		global $user_admin, $user_logged_in, $user_banned;
		global $ext_blacklist, $dl_index, $dl_auth, $dl_file_p, $dl_file_icon;
		global $db, $auth, $user, $config;

		/*
		* define the current user
		*/
		$user_id = ($user->data['user_perm_from']) ? $user->data['user_perm_from'] : $user->data['user_id'];
		$user_regdate = $user->data['user_regdate'];
		$user_dl_update_time = $user->data['user_dl_update_time'];
		$user_traffic = $user->data['user_traffic'];
		$user_logged_in = $user->data['is_registered'];
		$user_posts = $user->data['user_posts'];
		$user_client = $user->data['session_browser'];
		$username = $user->data['username'];
		$user_ip = $user->data['session_ip'];
		$user_admin = ($auth->acl_get('a_') && $user->data['is_registered'] && !$user->data['user_perm_from']) ? true : false;

		// Check the founder status and traffic settings for this
		if (!defined('FOUNDER_TRAFFICS_OFF'))
		{
			define('FOUNDER_TRAFFICS_OFF', ($config['dl_traffics_founder'] && $user->data['user_type'] == USER_FOUNDER) ? true : false);
		}

		// get group ids for the current user
		if ($config['dl_traffics_overall'] > 1 || $config['dl_traffics_users'] > 1)
		{
			$sql = 'SELECT g.group_id FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
				WHERE g.group_id = ug.group_id
					AND ug.user_id = ' . (int) $user_id . '
					AND ug.user_pending <> ' . true;
			$result = $db->sql_query($sql);

			$user_group_ids = array();

			while ($row = $db->sql_fetchrow($result))
			{
				$user_group_ids[] = $row['group_id'];
			}

			$db->sql_freeresult($result);
		}

		// preset all traffic permissions and helper values
		$dl_overall_traffics = false;
		$dl_guests_traffics = false;
		$dl_users_traffics = false;
		$dl_overall_traffics_groups = explode(',', $config['dl_traffics_overall_groups']);
		$dl_users_traffics_groups = explode(',', $config['dl_traffics_users_groups']);

		// check the several settings for the traffic management
		if (!$config['dl_traffic_off'])
		{
			// check the overall traffic settings
			if ($config['dl_traffics_overall'] == 1)
			{
				// enable the overall traffic for all users
				$dl_overall_traffics = true;
			}
			else if ($config['dl_traffics_overall'] == 2)
			{
				// enable the overall traffics for all selected user groups
				foreach ($user_group_ids as $key => $value)
				{
					if (in_array($user_group_ids[$key], $dl_overall_traffics_groups))
					{
						$dl_overall_traffics = true;
					}
				}
			}
			else if ($config['dl_traffics_overall'] == 3)
			{
				// first enable the limit to be able to disable it
				$dl_overall_traffics = true;

				// disable the overall traffics for all selected user groups
				foreach ($user_group_ids as $key => $value)
				{
					if (in_array($user_group_ids[$key], $dl_overall_traffics_groups))
					{
						$dl_overall_traffics = false;
					}
				}
			}

			// check the user traffic settings
			if ($config['dl_traffics_users'] == 1)
			{
				// enable the user traffic for all users
				$dl_users_traffics = true;
			}
			else if ($config['dl_traffics_users'] == 2)
			{
				// enable the user traffics for all selected user groups
				foreach ($user_group_ids as $key => $value)
				{
					if (in_array($user_group_ids[$key], $dl_users_traffics_groups))
					{
						$dl_users_traffics = true;
					}
				}
			}
			else if ($config['dl_traffics_users'] == 3)
			{
				// first enable the limit to be able to disable it
				$dl_users_traffics = true;

				// disable the user traffics for all selected user groups
				foreach ($user_group_ids as $key => $value)
				{
					if (in_array($user_group_ids[$key], $dl_users_traffics_groups))
					{
						$dl_users_traffics = false;
					}
				}
			}
		}

		if (!$config['dl_traffic_off'] && $config['dl_traffics_guests'])
		{
			$dl_guests_traffics = true;
		}

		// at least set the right constants to use them in the complete mod
		if (!defined('DL_GUESTS_TRAFFICS'))
		{
			define('DL_GUESTS_TRAFFICS', $dl_guests_traffics);
			define('DL_OVERALL_TRAFFICS', $dl_overall_traffics);
			define('DL_USERS_TRAFFICS', $dl_users_traffics);
		}

		$dl_rem = dl_cache::obtain_dl_config();
		$config['dl_remain_guest_traffic']	= $dl_rem['dl_remain_guest_traffic'];
		$config['dl_remain_traffic']		= $dl_rem['dl_remain_traffic'];

		/*
		* read the extension blacklist if enabled
		*/
		if ($config['dl_use_ext_blacklist'])
		{
			$blacklist_ary = dl_cache::obtain_dl_blacklist();
			$ext_blacklist = array();
			if (is_array($blacklist_ary) && sizeof($blacklist_ary))
			{
				$ext_blacklist = array_unique($blacklist_ary);
			}
		}

		/*
		* disable the extension blacklist if it will be empty
		*/
		if (sizeof($ext_blacklist))
		{
			$config['dl_enable_blacklist'] = true;
		}
		else
		{
			$config['dl_enable_blacklist'] = 0;
		}

		$current_month	= @gmdate('Ym', time());

		/*
		* set the overall traffic and categories traffic if needed (each first day of a month)
		*/
		if (isset($config['dl_traffic_retime']) && !$config['dl_traffic_off'])
		{
			$auto_overall_traffic_month = @gmdate('Ym', $config['dl_traffic_retime'] + $zone_offset);

			if ($auto_overall_traffic_month < $current_month)
			{
				$config['dl_traffic_retime'] = time();
				$config['dl_remain_traffic'] = 0;
				$config['dl_remain_guest_traffic'] = 0;

				$sql = 'UPDATE ' . DL_REM_TRAF_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
					'config_value' => '0'));
				$db->sql_query($sql);

				$sql = 'UPDATE ' . DL_CAT_TRAF_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
					'cat_traffic_use' => 0));
				$db->sql_query($sql);

				$config->set('dl_traffic_retime', $config['dl_traffic_retime'], false);
			}
		}

		/*
		* reset download clicks (each first day of a month)
		*/
		if (isset($config['dl_click_reset_time']))
		{
			$auto_click_reset_month = @gmdate('Ym', $config['dl_click_reset_time'] + $zone_offset);

			if ($auto_click_reset_month < $current_month)
			{
				$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
					'klicks' => 0));
				$db->sql_query($sql);

				$config->set('dl_click_reset_time', time(), false);
			}
		}

		/*
		* set the user traffic if needed (each first day of the month)
		*/
		if ($user_id <> ANONYMOUS && !$config['dl_traffic_off'] && (intval($config['dl_delay_auto_traffic']) == 0 || (time() - $user_regdate) / 84600 > $config['dl_delay_auto_traffic']))
		{
			$user_auto_traffic_month = @gmdate('Ym', $user_dl_update_time + $zone_offset);

			if ($user_auto_traffic_month < $current_month)
			{
				$sql = 'SELECT max(g.group_dl_auto_traffic) AS max_traffic FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
					WHERE g.group_id = ug.group_id
						AND ug.user_id = ' . (int) $user_id . '
						AND ug.user_pending <> ' . true;
				$result = $db->sql_query($sql);
				$max_group_row = $db->sql_fetchfield('max_traffic');
				$db->sql_freeresult($result);

				$new_user_traffic = (intval($max_group_row) != 0) ? $max_group_row : $config['dl_user_dl_auto_traffic'];

				if ($new_user_traffic > $user_traffic)
				{
					$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
						'user_traffic'			=> $new_user_traffic,
						'user_dl_update_time'	=> time())) . ' WHERE user_id = ' . (int) $user_id;
					$db->sql_query($sql);

					$user_traffic = $new_user_traffic;
				}
			}
		}

		/*
		* read the index
		*/
		$dl_index = dl_cache::obtain_dl_cats();

		if (is_array($dl_index) && sizeof($dl_index) > 0)
		{
			foreach($dl_index as $key => $value)
			{
				// check the default cat permissions
				if (isset($dl_index[$key]['auth_view']) && ($dl_index[$key]['auth_view'] == 1 || ($dl_index[$key]['auth_view'] == 2 && $user_logged_in)))
				{
					$dl_index[$key]['auth_view'] = true;
				}
				else
				{
					$dl_index[$key]['auth_view'] = false;
				}

				if (isset($dl_index[$key]['auth_dl']) && ($dl_index[$key]['auth_dl'] == 1 || ($dl_index[$key]['auth_dl'] == 2 && $user_logged_in)))
				{
					$dl_index[$key]['auth_dl'] = true;
				}
				else
				{
					$dl_index[$key]['auth_dl'] = false;
				}

				if (isset($dl_index[$key]['auth_up']) && ($dl_index[$key]['auth_up'] == 1 || ($dl_index[$key]['auth_up'] == 2 && $user_logged_in)))
				{
					$dl_index[$key]['auth_up'] = true;
				}
				else
				{
					$dl_index[$key]['auth_up'] = false;
				}

				if (isset($dl_index[$key]['auth_mod']) && ($dl_index[$key]['auth_mod'] == 1 || ($dl_index[$key]['auth_mod'] == 2 && $user_logged_in)))
				{
					$dl_index[$key]['auth_mod'] = true;
				}
				else
				{
					$dl_index[$key]['auth_mod'] = false;
				}
			}
		}
		else
		{
			$dl_index = array();
		}

		/*
		* count all files per category
		*/
		$cat_counts = dl_cache::obtain_dl_cat_counts();

		if (is_array($cat_counts) && sizeof($cat_counts) > 0)
		{
			foreach($cat_counts as $key => $value)
			{
				$dl_index[$key]['total'] = $value;
			}
		}
		else
		{
			$cat_counts = array();
		}

		/*
		* get the user permissions
		*/
		$cat_auth_array = $group_ids = array();

		$dl_auth_perm = dl_cache::obtain_dl_auth();

		$auth_cat = (isset($dl_auth_perm['auth_cat'])) ? $dl_auth_perm['auth_cat'] : array();
		$group_perm_ids = (isset($dl_auth_perm['group_perm_ids'])) ? $dl_auth_perm['group_perm_ids'] : array();
		$auth_perm = (isset($dl_auth_perm['auth_perm'])) ? $dl_auth_perm['auth_perm'] : array();

		if (sizeof($group_perm_ids) != 0)
		{
			$sql = 'SELECT group_id FROM ' . USER_GROUP_TABLE . '
				WHERE ' . $db->sql_in_set('group_id', array_map('intval', $group_perm_ids)) . '
					AND user_id = ' . (int) $user_id . '
					AND user_pending <> ' . true;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$group_ids[] = $row['group_id'];
			}
			$db->sql_freeresult($result);

			for ($i = 0; $i < sizeof($auth_cat); $i++)
			{
				$auth_view = $auth_dl = $auth_up = $auth_mod = 0;
				$cat = $auth_cat[$i];

				for ($j = 0; $j < sizeof($group_ids); $j++)
				{
					$user_group = $group_ids[$j];

					if (isset($auth_perm[$cat][$user_group]['auth_view']) && $auth_perm[$cat][$user_group]['auth_view'] == true)
					{
						$auth_view = true;
					}
					if (isset($auth_perm[$cat][$user_group]['auth_dl']) && $auth_perm[$cat][$user_group]['auth_dl'] == true)
					{
						$auth_dl = true;
					}
					if (isset($auth_perm[$cat][$user_group]['auth_up']) && $auth_perm[$cat][$user_group]['auth_up'] == true)
					{
						$auth_up = true;
					}
					if (isset($auth_perm[$cat][$user_group]['auth_mod']) && $auth_perm[$cat][$user_group]['auth_mod'] == true)
					{
						$auth_mod = true;
					}
				}

				$cat_auth_array[$cat]['auth_view'] = $auth_view;
				$cat_auth_array[$cat]['auth_dl'] = $auth_dl;
				$cat_auth_array[$cat]['auth_up'] = $auth_up;
				$cat_auth_array[$cat]['auth_mod'] = $auth_mod;
			}
		}

		$dl_auth = $cat_auth_array;

		/*
		* preset all files
		*/
		$dl_file_p = array();

		$sql = 'SELECT id, cat, file_name, real_file, file_size, extern, free, file_traffic, klicks FROM ' . DOWNLOADS_TABLE . '
				WHERE approve = ' . true;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$dl_file_p[$row['id']] = $row;
		}
		$db->sql_freeresult($result);

		$dl_file_icon = dl_cache::obtain_dl_files(intval($config['dl_new_time']), intval($config['dl_edit_time']));

		/*
		* get ban status for current user
		*/
		$sql_guests = (!$user_logged_in) ? " OR guests = 1 " : '';

		$sql = 'SELECT ban_id FROM ' . DL_BANLIST_TABLE . '
			WHERE user_id = ' . (int) $user_id . "
				OR user_ip = '" . $db->sql_escape($user_ip) . "'
				OR user_agent " . $db->sql_like_expression(self::dl_client($user_client, $ext_path)) . "
				OR username = '" . $db->sql_escape($username) . "'
				$sql_guests";
		$result = $db->sql_query($sql);

		$total_ban_ids = $db->sql_affectedrows($result);
		$db->sql_freeresult($result);

		if ($total_ban_ids)
		{
			$user_banned = true;
		}

		return;
	}

	public static function dl_client($client, $ext_path)
	{
		$browser_name = 'n/a';

		if (file_exists($ext_path . 'phpbb/helpers/dl_user_agents' . self::phpEx()) && $client)
		{
			include($ext_path . 'phpbb/helpers/dl_user_agents' . self::phpEx());
		}
		else
		{
			return $browser_name;
		}

		if (isset($agent_title) && sizeof($agent_title) && isset($agent_strings) && sizeof($agent_strings))
		{
			$agent_id		= 0;
			$browser_name	= '';

			for ($i = 0; $i < sizeof($agent_strings); $i++)
			{
				$tmp_ary = explode('|', $agent_strings[$i]);
				$a_id	= $tmp_ary[0];
				$a_txt	= $tmp_ary[1];

				if (isset($a_id) && isset($a_txt) && stristr($client, $a_txt))
				{
					$agent_id = $a_id;
					break;
				}
			}

			if ($agent_id)
			{
				for ($i = 0; $i < sizeof($agent_title); $i++)
				{
					$tmp_ary = explode('|', $agent_title[$i]);
					$a_id	= $tmp_ary[0];
					$a_txt	= $tmp_ary[1];

					if ($a_id == $agent_id)
					{
						$browser_name = $a_txt;
						break;
					}
				}
			}

			unset($agent_title);
			unset($agent_strings);
		}

		return $browser_name;
	}
}
