<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\phpbb\helpers;

use Symfony\Component\DependencyInjection\Container;

class constants
{
	private $root_path;
	private $table_prefix;
	private $config;
	private $user;
	private $ext_path;
	private $ext_path_web;
	private $db;

	private $dlext_physical;

	/**
	* Constructor
	*
	* @param Container 								$phpbb_container
	* @param string									$root_path
	* @param string									$table_prefix
	* @param \phpbb\config\config					$config
	* @param \phpbb\user							$user
	* @param \phpbb\extension\manager				$phpbb_extension_manager
	* @param \phpbb\path_helper						$phpbb_path_helper
	* @param \phpbb\db\driver\driver_interfacer		$db
	*/
	public function __construct(
		Container $phpbb_container,
		$root_path,
		$table_prefix,
		\phpbb\config\config $config,
		\phpbb\user $user,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\db\driver\driver_interface $db,
		$dlext_physical
	)
	{
		$this->root_path		= $root_path;
		$this->table_prefix		= $table_prefix;
		$this->config			= $config;
		$this->user				= $user;
		$this->db 				= $db;

		$this->ext_path			= $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web		= $phpbb_path_helper->update_web_root_path($this->ext_path);

		$this->dlext_physical	= $dlext_physical;
	}

	public function init()
	{
		if (defined('DL_AUTH_TABLE'))
		{
			return;
		}

		// define extension database table constants
		define('DL_AUTH_TABLE',			$this->table_prefix . 'dl_auth');
		define('DL_BANLIST_TABLE',		$this->table_prefix . 'dl_banlist');
		define('DL_BUGS_TABLE',			$this->table_prefix . 'dl_bug_tracker');
		define('DL_BUG_HISTORY_TABLE',	$this->table_prefix . 'dl_bug_history');
		define('DL_CAT_TABLE',			$this->table_prefix . 'downloads_cat');
		define('DL_CAT_TRAF_TABLE',		$this->table_prefix . 'dl_cat_traf');
		define('DL_COMMENTS_TABLE',		$this->table_prefix . 'dl_comments');
		define('DL_EXT_BLACKLIST',		$this->table_prefix . 'dl_ext_blacklist');
		define('DL_FAVORITES_TABLE',	$this->table_prefix . 'dl_favorites');
		define('DL_FIELDS_DATA_TABLE',	$this->table_prefix . 'dl_fields_data');
		define('DL_FIELDS_LANG_TABLE',	$this->table_prefix . 'dl_fields_lang');
		define('DL_FIELDS_TABLE',		$this->table_prefix . 'dl_fields');
		define('DL_HOTLINK_TABLE',		$this->table_prefix . 'dl_hotlink');
		define('DL_IMAGES_TABLE',		$this->table_prefix . 'dl_images');
		define('DL_LANG_TABLE',			$this->table_prefix . 'dl_lang');
		define('DL_NOTRAF_TABLE',		$this->table_prefix . 'dl_notraf');
		define('DL_RATING_TABLE',		$this->table_prefix . 'dl_ratings');
		define('DL_STATS_TABLE',		$this->table_prefix . 'dl_stats');
		define('DL_VERSIONS_TABLE',		$this->table_prefix . 'dl_versions');
		define('DL_VER_FILES_TABLE',	$this->table_prefix . 'dl_ver_files');
		define('DOWNLOADS_TABLE',		$this->table_prefix . 'downloads');

		// define extension folder constants
		define('DL_EXT_CACHE_PATH',			$this->root_path . 'cache/' . PHPBB_ENVIRONMENT . '/dlext/');
		define('DL_EXT_FILEBASE_PATH', 		$this->root_path . $this->config['upload_path'] . '/dlext/');

		$this->dlext_physical->check_folders();

		// Check the founder status and traffic settings for this
		define('FOUNDER_TRAFFICS_OFF', ($this->config['dl_traffics_founder'] && $this->user->data['user_type'] == USER_FOUNDER) ? true : false);

		// get group ids for the current user
		if ($this->config['dl_traffics_overall'] > 1 || $this->config['dl_traffics_users'] > 1)
		{
			$sql = 'SELECT g.group_id FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
				WHERE g.group_id = ug.group_id
					AND ug.user_id = ' . (int) $this->user->data['user_id'] . '
					AND ug.user_pending <> ' . true;
			$result = $this->db->sql_query($sql);

			$user_group_ids = array();

			while ($row = $this->db->sql_fetchrow($result))
			{
				$user_group_ids[] = $row['group_id'];
			}

			$this->db->sql_freeresult($result);
		}

		// preset all traffic permissions and helper values
		$dl_overall_traffics = false;
		$dl_guests_traffics = false;
		$dl_users_traffics = false;
		$dl_overall_traffics_groups = explode(',', $this->config['dl_traffics_overall_groups']);
		$dl_users_traffics_groups = explode(',', $this->config['dl_traffics_users_groups']);

		// check the several settings for the traffic management
		if (!$this->config['dl_traffic_off'])
		{
			// check the overall traffic settings
			if ($this->config['dl_traffics_overall'] == 1)
			{
				// enable the overall traffic for all users
				$dl_overall_traffics = true;
			}
			else if ($this->config['dl_traffics_overall'] == 2)
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
			else if ($this->config['dl_traffics_overall'] == 3)
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
			if ($this->config['dl_traffics_users'] == 1)
			{
				// enable the user traffic for all users
				$dl_users_traffics = true;
			}
			else if ($this->config['dl_traffics_users'] == 2)
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
			else if ($this->config['dl_traffics_users'] == 3)
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

		// at least set the right constants to use them in the complete extension
		define('DL_GUESTS_TRAFFICS', ((!$this->config['dl_traffic_off'] && $this->config['dl_traffics_guests']) ? true : false));
		define('DL_OVERALL_TRAFFICS', $dl_overall_traffics);
		define('DL_USERS_TRAFFICS', $dl_users_traffics);
	}
}
