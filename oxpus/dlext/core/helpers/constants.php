<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core\helpers;

class constants
{
	/* extension constants */
	const DL_AUTH_CHECK_MOD = 2;
	const DL_AUTH_CHECK_UPLOAD = 3;
	const DL_AUTH_CHECK_VIEW = 1;

	const DL_AUTOADD_FAV_NONE = 0;
	const DL_AUTOADD_FAV_ALL = 1;
	const DL_AUTOADD_FAV_SELECT = 2;

	const DL_CAPTCHA_PERM_ADMINS = 4;
	const DL_CAPTCHA_PERM_ALL = 5;
	const DL_CAPTCHA_PERM_GUESTS = 1;
	const DL_CAPTCHA_PERM_MODS = 3;
	const DL_CAPTCHA_PERM_OFF = 0;
	const DL_CAPTCHA_PERM_USER = 2;

	const DL_CAT_DELETE_FILES = -1;
	const DL_CAT_DELETE_ONLY = 0;
	const DL_CAT_EDIT_ADMIN_MOD = 2;
	const DL_CAT_EDIT_ADMIN_MOD_OWN = 3;
	const DL_CAT_EDIT_ADMIN_ONLY = 1;
	const DL_CAT_EDIT_OFF = 0;

	const DL_FILE_FREE_ALL = 1;
	const DL_FILE_FREE_NONE = 0;
	const DL_FILE_FREE_REG_USER = 2;
	const DL_FILE_HASH_MD5 = 'md5';
	const DL_FILE_HASH_SHA = 'sha1';
	const DL_FILE_HASH_PHPBB = 'md5';
	const DL_FILE_RANGE_BYTE = 'B';
	const DL_FILE_RANGE_GBYTE = 'GB';
	const DL_FILE_RANGE_KBYTE = 'KB';
	const DL_FILE_RANGE_MBYTE = 'MB';
	const DL_FILE_SIZE_GBYTE = 1073741824;
	const DL_FILE_SIZE_KBYTE = 1024;
	const DL_FILE_SIZE_MBYTE = 1048576;
	const DL_FILE_TYPE_IMAGE = 1;

	const DL_FOOTER_STATS_ADMIN_ONLY = 3;
	const DL_FOOTER_STATS_ALL = 2;
	const DL_FOOTER_STATS_FOUNDER_ONLY = 4;
	const DL_FOOTER_STATS_GUESTS_USER = 1;
	const DL_FOOTER_STATS_OFF = 0;

	const DL_HACKLIST_EXTRA = 2;
	const DL_HACKLIST_NO = 0;
	const DL_HACKLIST_SORT_AUTHOR = 2;
	const DL_HACKLIST_SORT_DESC = 1;
	const DL_HACKLIST_YES = 1;

	const DL_HOTLINK_DETAILS = 1;
	const DL_HOTLINK_MESSAGE = 0;

	const DL_LATEST_TYPE_COMPLETE = 2;
	const DL_LATEST_TYPE_DEFAULT = 1;
	const DL_LATEST_TYPE_NEW = 3;
	const DL_LATEST_TYPE_OFF = 0;

	const DL_MOVE_DOWN = 15;
	const DL_MOVE_UP = -15;

	const DL_PERM_ADMIN = 3;
	const DL_PERM_ALL = 0;
	const DL_PERM_DROP_CATS = 2;
	const DL_PERM_EDIT = 0;
	const DL_PERM_GENERAL_ALL = 1;
	const DL_PERM_GENERAL_GROUPS = 0;
	const DL_PERM_GENERAL_NONE = -1;
	const DL_PERM_GENERAL_REG_USER = 2;
	const DL_PERM_GENERAL_ZERO = 0;
	const DL_PERM_MOD = 2;
	const DL_PERM_OFF = 9;
	const DL_PERM_USER = 1;
	const DL_PERM_VIEW = 1;

	const DL_REPORT_ALL = 1;
	const DL_REPORT_OFF = 0;
	const DL_REPORT_REG_USER = 2;
	const DL_REPORT_STATUS_DECLINED = 5;
	const DL_REPORT_STATUS_FINISHED = 4;
	const DL_REPORT_STATUS_NEW = 0;
	const DL_REPORT_STATUS_PENDING = 3;
	const DL_REPORT_STATUS_PROGRESS = 2;
	const DL_REPORT_STATUS_VIEWED = 1;

	const DL_RSS_ACTION_D_TXT = 2;
	const DL_RSS_ACTION_R_DLX = 0;
	const DL_RSS_ACTION_R_IDX = 1;
	const DL_RSS_CATS_ALL = 0;
	const DL_RSS_CATS_OTHER = 2;
	const DL_RSS_CATS_SELECTED = 1;
	const DL_RSS_DESC_LENGTH_FULL = 1;
	const DL_RSS_DESC_LENGTH_NONE = 0;
	const DL_RSS_DESC_LENGTH_SHORT = 2;

	const DL_SEARCH_TYPE_ALL = 0;

	const DL_SORT_ACP = 1;
	const DL_SORT_ASC = 0;
	const DL_SORT_CLICKS = 3;
	const DL_SORT_DEFAULT = 0;
	const DL_SORT_DESC = 1;
	const DL_SORT_DESCRIPTION = 1;
	const DL_SORT_EXTERN = 5;
	const DL_SORT_FILE_NAME = 2;
	const DL_SORT_FILE_SIZE = 6;
	const DL_SORT_FREE = 4;
	const DL_SORT_LAST_TIME = 7;
	const DL_SORT_RANGE = 10;
	const DL_SORT_RATING = 8;
	const DL_SORT_USER = 0;

	const DL_STATS_DEL_ALL = 1;
	const DL_STATS_DEL_GUESTS = 2;
	const DL_STATS_FILE_DOWNLOAD = 0;
	const DL_STATS_FILE_EDIT = 2;
	const DL_STATS_FILE_UPLOAD = 1;
	const DL_STATS_POS_LIMIT = 10;

	const DL_THUMBS_DISPLAY_OFF = 0;
	const DL_THUMBS_DISPLAY_ON = 1;
	const DL_THUMBS_DISPLAY_CAT = 2;

	const DL_TOPIC_MORE_DETAILS_OVER = 2;
	const DL_TOPIC_MORE_DETAILS_UNDER = 1;
	const DL_TOPIC_NO_MORE_DETAILS = 0;
	const DL_TOPIC_USER_CAT = 2;
	const DL_TOPIC_USER_OTHER = 1;
	const DL_TOPIC_USER_SELF = 0;

	const DL_TRAFFICS_OFF_ALL = 0;
	const DL_TRAFFICS_OFF_GROUPS = 3;
	const DL_TRAFFICS_ON_ALL = 1;
	const DL_TRAFFICS_ON_GROUPS = 2;

	const DL_VERSION_ADD_OLD = 1;
	const DL_VERSION_DELETE = 3;
	const DL_VERSION_REPLACE = 2;

	const DL_DEFAULT_PANEL = 2;
	const DL_FALSE = 0;
	const DL_MAX_STRING_LENGTH = 16777215;
	const DL_NONE = -1;
	const DL_ONE_DAY = 86400;
	const DL_ONE_HOUR = 3600;
	const DL_PIC_MIN_SIZE = 10;
	const DL_RATING_MULTIFIER = 10;
	const DL_RETURN_MCP_APPROVE = 99;
	const DL_SELECT_MAX_SIZE = 10;
	const DL_TRUE = 1;
	const DL_VER_DESC_LIMIT = 100;

	/* phpbb objects */
	protected $root_path;
	protected $config;
	protected $user;
	protected $db;
	protected $cache;
	protected $filesystem;
	protected $extension_manager;

	/* extension owned objects */
	protected $dl_overall_traffics;
	protected $dl_users_traffics;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\user							$user
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\cache\service					$cache
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \phpbb\extension\manager				$extension_manager
	 */
	public function __construct(
		$root_path,
		\phpbb\config\config $config,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\cache\service $cache,
		\phpbb\filesystem\filesystem $filesystem,
		\phpbb\extension\manager $extension_manager
	)
	{
		$this->root_path				= $root_path;
		$this->config					= $config;
		$this->user						= $user;
		$this->db 						= $db;
		$this->cache					= $cache;
		$this->filesystem 				= $filesystem;
		$this->extension_manager		= $extension_manager;

		define('DL_THUMBS_MAX_HEIGHT', $config['dl_thumb_xsize_max']);
		define('DL_THUMBS_MAX_WIDTH', $config['dl_thumb_ysize_max']);
	}

	public function init()
	{
		$this->check_version();
		$this->check_folders();
	}

	public function check_version()
	{
		$ext_path = $this->extension_manager->get_extension_path('oxpus/dlext', self::DL_TRUE);
		$ext_metadata = json_decode(file_get_contents($ext_path . 'composer.json'), true);

		if (version_compare($this->config['dl_ext_version'], $ext_metadata['version'], '<'))
		{
			$this->config->set('dl_ext_version', $ext_metadata['version']);
			$this->cache->destroy('config');
		}
	}

	public function _create_folder($path)
	{
		if ($this->filesystem->exists($path))
		{
			return;
		}

		$this->filesystem->mkdir($path);

		$f = fopen($path . 'index.htm', 'w');
		fclose($f);
	}

	public function check_folders()
	{
		$file_dir_base = $this->get_value('files_dir');

		if (!$this->filesystem->exists($file_dir_base))
		{
			$this->_create_folder($file_dir_base . '/');
			$this->_create_folder($file_dir_base . '/downloads/');
			$this->_create_folder($file_dir_base . '/thumbs/');
			$this->_create_folder($file_dir_base . '/version/');
			$this->_create_folder($file_dir_base . '/version/files/');
			$this->_create_folder($file_dir_base . '/version/images/');
		}

		if (!$this->filesystem->exists($file_dir_base . '/downloads/'))
		{
			$this->_create_folder($file_dir_base . '/downloads/');
		}

		if (!$this->filesystem->exists($file_dir_base . '/thumbs/'))
		{
			$this->_create_folder($file_dir_base . '/thumbs/');
		}

		if (!$this->filesystem->exists($file_dir_base . '/version/'))
		{
			$this->_create_folder($file_dir_base . '/version/');
		}

		if (!$this->filesystem->exists($file_dir_base . '/version/files/'))
		{
			$this->_create_folder($file_dir_base . '/version/files/');
		}

		if (!$this->filesystem->exists($file_dir_base . '/version/images/'))
		{
			$this->_create_folder($file_dir_base . '/version/images/');
		}
	}

	public function get_value($value, $raw = false)
	{
		if (in_array($value, ['overall_traffics', 'users_traffics']))
		{
			// get group ids for the current user
			$user_group_ids = [];

			if (($this->config['dl_traffics_overall'] > self::DL_TRAFFICS_ON_ALL || $this->config['dl_traffics_users'] > self::DL_TRAFFICS_ON_ALL) && empty($user_group_ids))
			{
				$sql = 'SELECT g.group_id FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
					WHERE g.group_id = ug.group_id
						AND ug.user_id = ' . (int) $this->user->data['user_id'] . '
						AND ug.user_pending <> 1';
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$user_group_ids[] = $row['group_id'];
				}

				$this->db->sql_freeresult($result);
			}

			// preset all traffic permissions and helper values
			$this->dl_overall_traffics = self::DL_FALSE;
			$this->dl_users_traffics = self::DL_FALSE;
			$dl_overall_traffics_groups = explode(',', $this->config['dl_traffics_overall_groups']);
			$dl_users_traffics_groups = explode(',', $this->config['dl_traffics_users_groups']);

			// check the several settings for the traffic management
			if (!$this->config['dl_traffic_off'])
			{
				// check the overall traffic settings
				if ($this->config['dl_traffics_overall'] == self::DL_TRAFFICS_ON_ALL)
				{
					// enable the overall traffic for all users
					$this->dl_overall_traffics = self::DL_TRUE;
				}
				else if ($this->config['dl_traffics_overall'] == self::DL_TRAFFICS_ON_GROUPS)
				{
					// enable the overall traffics for all selected user groups
					foreach (array_keys($user_group_ids) as $key)
					{
						if (in_array($user_group_ids[$key], $dl_overall_traffics_groups))
						{
							$this->dl_overall_traffics = self::DL_TRUE;
						}
					}
				}
				else if ($this->config['dl_traffics_overall'] == self::DL_TRAFFICS_OFF_GROUPS)
				{
					// first enable the limit to be able to disable it
					$this->dl_overall_traffics = self::DL_TRUE;

					// disable the overall traffics for all selected user groups
					foreach (array_keys($user_group_ids) as $key)
					{
						if (in_array($user_group_ids[$key], $dl_overall_traffics_groups))
						{
							$this->dl_overall_traffics = self::DL_FALSE;
						}
					}
				}

				// check the user traffic settings
				if ($this->config['dl_traffics_users'] == self::DL_TRAFFICS_ON_ALL)
				{
					// enable the user traffic for all users
					$this->dl_users_traffics = self::DL_TRUE;
				}
				else if ($this->config['dl_traffics_users'] == self::DL_TRAFFICS_ON_GROUPS)
				{
					// enable the user traffics for all selected user groups
					foreach (array_keys($user_group_ids) as $key)
					{
						if (in_array($user_group_ids[$key], $dl_users_traffics_groups))
						{
							$this->dl_users_traffics = self::DL_TRUE;
						}
					}
				}
				else if ($this->config['dl_traffics_users'] == self::DL_TRAFFICS_OFF_GROUPS)
				{
					// first enable the limit to be able to disable it
					$this->dl_users_traffics = self::DL_TRUE;

					// disable the user traffics for all selected user groups
					foreach (array_keys($user_group_ids) as $key)
					{
						if (in_array($user_group_ids[$key], $dl_users_traffics_groups))
						{
							$this->dl_users_traffics = self::DL_FALSE;
						}
					}
				}
			}
		}

		switch ($value)
		{
			case 'files_dir':
				$core_upload_path = $this->config['upload_path'];

				if ($raw)
				{
					$return = $core_upload_path . '/dlext';
				}
				else
				{
					$return = $this->root_path . $core_upload_path . '/dlext';
				}
				break;

			case 'founder_traffics':
				$return = ($this->config['dl_traffics_founder'] && $this->user->data['user_type'] == USER_FOUNDER) ? self::DL_TRUE : self::DL_FALSE;
				break;

			case 'guests_traffics':
				$return = (!$this->config['dl_traffic_off'] && $this->config['dl_traffics_guests']) ? self::DL_TRUE : self::DL_FALSE;
				break;

			case 'overall_traffics':
				$return = $this->dl_overall_traffics;
				break;

			case 'users_traffics':
				$return = $this->dl_users_traffics;
				break;

			default:
				$return = '';
		}

		return $return;
	}
}
