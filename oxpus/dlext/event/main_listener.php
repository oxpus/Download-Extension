<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\event;

/**
* @ignore
*/
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'						=> 'load_language_on_setup',
			'core.page_header'						=> 'add_page_header_links',
			'core.viewonline_overwrite_location'	=> 'add_dl_ext_viewonline',
			'core.memberlist_view_profile'			=> 'add_user_profile_traffic',
			'core.update_username'					=> 'change_username',
			'core.delete_user_after'				=> 'delete_user',
			'core.submit_post_end'					=> 'add_user_traffic_for_post',
			'core.modify_posting_parameters'		=> 'drop_user_traffic_for_post',
			'core.modify_text_for_display_before'	=> 'convert_link_to_download_name',
			'core.permissions'						=> 'add_permission_cat',
		);
	}

	/* @var string phpbb_root_path */
	protected $root_path;

	/* @var string phpEx */
	protected $php_ext;

	/* @var string table_prefix */
	protected $table_prefix;

	/* @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/* @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/* @var Container */
	protected $phpbb_container;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language $language Language object */
	protected $language;

	/**
	* Constructor
	*
	* @param string									$root_path
	* @param string									$php_ext
	* @param string									$table_prefix
	* @param \phpbb\extension\manager				$phpbb_extension_manager
	* @param \phpbb\path_helper						$phpbb_path_helper
	* @param Container								$phpbb_container
	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param \phpbb\config\config					$config
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\auth\auth						$auth
	* @param \phpbb\template\template				$template
	* @param \phpbb\user							$user
	*/
	public function __construct($root_path, $php_ext, $table_prefix, \phpbb\extension\manager $phpbb_extension_manager, \phpbb\path_helper $phpbb_path_helper, Container $phpbb_container, \phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\auth\auth $auth, \phpbb\template\template $template, \phpbb\user $user, \phpbb\language\language $language)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->table_prefix 			= $table_prefix;
		$this->phpbb_extension_manager	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->phpbb_container 			= $phpbb_container;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->auth						= $auth;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'oxpus/dlext',
			'lang_set' => 'common',
		);

		if (defined('ADMIN_START'))
		{
			$lang_set_ext[] = array(
				'ext_name' => 'oxpus/dlext',
				'lang_set' => 'permissions_dl_ext',
			);
		}

		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_download_message($event)
	{
		if ( isset($this->user->data['user_new_download']) && $this->user->data['user_new_download'] && $this->user->data['user_dl_note_type'] <> 2)
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_new_download = 0
				WHERE user_id = ' . (int)$this->user->data['user_id'];
			$this->db->sql_query($sql);

			$new_dl_link = $this->helper->route('oxpus_dlext_controller', array('view' => 'latest'));

			$this->template->assign_vars(array(
				'NEW_DOWNLOAD_MESSAGE'	=> $this->language->lang('NEW_DOWNLOAD', $new_dl_link),
				'S_NEW_DL_POPUP'		=> ($this->user->data['user_dl_note_type'] == 1) ? true : false,
				'S_NEW_DL_MESSAGE'		=> ($this->user->data['user_dl_note_type'] == 0) ? true : false,
			));
		}
	}

	public function add_page_header_links($event)
	{
		$ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->phpbb_path_helper	= $this->phpbb_container->get('path_helper');
		$ext_path_web				= $this->phpbb_path_helper->update_web_root_path($ext_path);

		$table_prefix = $this->table_prefix;
		include_once($ext_path . 'includes/helpers/dl_constants.' . $this->php_ext);

		$dl_mod_is_active = true;
		$dl_mod_link_show = true;

		$this->template->assign_vars(array(
			'EXT_DL_PATH'			=> $ext_path,
			'EXT_DL_PATH_WEB'		=> $ext_path_web,
		));

		if (isset($this->config['dl_active']) && !$this->config['dl_active'])
		{
			if (isset($this->config['dl_off_now_time']) && $this->config['dl_off_now_time'])
			{
				$dl_mod_is_active = false;
			}
			else
			{
				if (isset($this->config['dl_off_from']) && isset($this->config['dl_off_till']))
				{
					$curr_time = (date('H', time()) * 60) + date('i', time());
					$off_from = (substr($this->config['dl_off_from'], 0, 2) * 60) + (substr($this->config['dl_off_from'], -2));
					$off_till = (substr($this->config['dl_off_till'], 0, 2) * 60) + (substr($this->config['dl_off_till'], -2));

					if ($curr_time >= $off_from && $curr_time < $off_till)
					{
						$dl_mod_is_active = false;
					}
				}
			}
		}

		if (!$dl_mod_is_active && isset($this->config['dl_off_hide']) && $this->config['dl_off_hide'])
		{
			$dl_mod_link_show = false;
		}

		if (!$dl_mod_is_active && $this->auth->acl_get('a_') && isset($this->config['dl_on_admins']) && $this->config['dl_on_admins'])
		{
			$dl_mod_link_show = true;
		}

		if ($dl_mod_link_show)
		{
			$dl_main_link = $this->helper->route('oxpus_dlext_controller');

			$this->template->assign_vars(array(
				'U_DL_NAVI'		=> $dl_main_link,
			));

			if (isset($this->config['dl_use_hacklist']) && $this->config['dl_use_hacklist'])
			{
				$sql = 'SELECT COUNT(id) AS total FROM ' . DOWNLOADS_TABLE . '
					WHERE hacklist = 1';
				$result = $this->db->sql_query($sql);

				if ($result)
				{
					$row = $this->db->sql_fetchrow($result);
					$total = $row['total'];

					if ($total)
					{
						$dl_hacks_link = $this->helper->route('oxpus_dlext_controller', array('view' => 'hacks'));

						$this->template->assign_vars(array(
							'U_DL_HACKS_LIST'	=> $dl_hacks_link,
						));
					}
				}
				$this->db->sql_freeresult($result);
			}

			if ($this->user->data['is_registered'])
			{
				$sql = 'SELECT count(d.id) as total FROM ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . ' c
					WHERE c.id = d.cat
						AND c.bug_tracker = 1';
				$result = $this->db->sql_query($sql);

				if ($result)
				{
					$row = $this->db->sql_fetchrow($result);
				}
				$this->db->sql_freeresult($result);

				if (isset($row) && $row['total'] != 0)
				{
					$dl_bt_link = $this->helper->route('oxpus_dlext_controller', array('view' => 'bug_tracker'));

					$this->template->assign_vars(array(
						'U_DL_BUG_TRACKER'	=> $dl_bt_link,
					));
				}
			}
		}

		self::add_download_message($event);
	}

	public function add_dl_ext_viewonline($event)
	{
		if (strpos($event['row']['session_page'], '/dlext') !== false)
		{
			if (strpos($event['row']['session_page'], '/dlext/?view=hacks') !== false)
			{
				$dl_hacks_link = $this->helper->route('oxpus_dlext_controller', array('view' => 'hacks'));

				$event['location'] = $this->language->lang('DL_PAGE_DL_HACKSLIST');
				$event['location_url'] = $dl_hacks_link;
			}
			else if (strpos($event['row']['session_page'], '/dlext/?view=bug_tracker') !== false)
			{
				$dl_main_link = $this->helper->route('oxpus_dlext_controller', array('view' => 'bug_tracker'));

				$event['location'] = $this->language->lang('DL_PAGE_BUG_TRACKER');
				$event['location_url'] = $dl_main_link;
			}
			else
			{
				$dl_main_link = $this->helper->route('oxpus_dlext_controller');

				$event['location'] = $this->language->lang('DL_PAGE_DOWNLOADS');
				$event['location_url'] = $dl_main_link;
			}
		}
	}

	public function add_user_profile_traffic($event)
	{
		if (!$this->config['dl_traffic_off'])
		{
			if ($event['member']['user_traffic'] < 1024)
			{
				$output_value = $event['member']['user_traffic'];
				$output_desc = '&nbsp;&nbsp;' . $this->language->lang('DL_BYTES');
			}
			else if ($event['member']['user_traffic'] < 1048576)
			{
				$output_value = $event['member']['user_traffic'] / 1024;
				$output_desc = '&nbsp;' . $this->language->lang('DL_KB');
			}
			else if ($event['member']['user_traffic'] < 1073741824)
			{
				$output_value = $event['member']['user_traffic'] / 1048576;
				$output_desc = '&nbsp;' . $this->language->lang('DL_MB');
			}
			else
			{
				$output_value = $event['member']['user_traffic'] / 1073741824;
				$output_desc = '&nbsp;' . $this->language->lang('DL_GB');
			}

			$output_value = round($output_value, 2);

			$user_traffic = $output_value . $output_desc;

			$this->template->assign_block_vars('custom_fields', array(
				'PROFILE_FIELD_NAME'	=> $this->language->lang('DOWNLOADS') . ' ' . $this->language->lang('TRAFFIC'),
				'PROFILE_FIELD_VALUE'	=> $user_traffic,
			));
		}
	}

	public function change_username($event)
	{
		$ext_path = $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$table_prefix = $this->table_prefix;
		include_once($ext_path . '/includes/helpers/dl_constants.' . $this->php_ext);

		$update_ary = array(DL_BANLIST_TABLE, DL_COMMENTS_TABLE, DL_STATS_TABLE);

		foreach ($update_ary as $table)
		{
			$sql = "UPDATE $table
				SET username = '" . $this->db->sql_escape($event['new_name']) . "'
				WHERE username = '" . $this->db->sql_escape($event['old_name']) . "'";
			$this->db->sql_query($sql);
		}
	}

	public function delete_user($event)
	{
		$ext_path = $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$table_prefix = $this->table_prefix;
		include_once($ext_path . '/includes/helpers/dl_constants.' . $this->php_ext);

		$table_ary = array(DL_NOTRAF_TABLE);

		// Delete the miscellaneous (non-post) data for the user
		foreach ($table_ary as $table)
		{
			$sql = "DELETE FROM $table
				WHERE " . $this->db->sql_in_set('user_id', $event['user_ids']);
			$this->db->sql_query($sql);
		}

		$sql = 'DELETE FROM ' . DL_FAVORITES_TABLE . '
			WHERE ' . $this->db->sql_in_set('fav_user_id', $event['user_ids']);
		$this->db->sql_query($sql);
	}

	public function add_user_traffic_for_post($event)
	{
		$ext_path = $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$ext_path_web = $this->phpbb_path_helper->update_web_root_path($ext_path);
		$table_prefix = $this->table_prefix;
		include_once($ext_path . '/includes/helpers/dl_constants.' . $this->php_ext);

		if (!defined('DL_EXT_CACHE_FOLDER'))
		{
			// Define the basic file storage placement
			if ($this->config['dl_download_dir'] == 2)
			{
				$filebase_prefix = $this->root_path . 'store/oxpus/dlext/';
				$filebase_web_prefix = generate_board_url() . '/store/oxpus/dlext/';
			}
			else
			{
				$filebase_prefix = $ext_path . 'files/';
				$filebase_web_prefix = $ext_path_web . 'files/';
			}

			define('DL_EXT_CACHE_FOLDER',		$filebase_prefix . 'cache/');
			define('DL_EXT_THUMBS_FOLDER',		$filebase_prefix . 'thumbs/');
			define('DL_EXT_THUMBS_WEB_FOLDER',	$filebase_web_prefix . 'thumbs/');
			define('DL_EXT_FILES_FOLDER',		$filebase_prefix . 'downloads/');
			define('DL_EXT_FILES_WEBFOLDER',	$filebase_web_prefix . 'downloads/');
		}

		include_once($ext_path . '/includes/classes/class_dlmod.' . $this->php_ext);
		$dl_mod = new \oxpus\dlext\includes\classes\ dl_mod($this->root_path, $this->php_ext, $ext_path);
		$dl_mod->register();
		\oxpus\dlext\includes\classes\ dl_init::init($ext_path);
		$user_traffics_on = DL_USERS_TRAFFICS;
		$founder_traffics_off = FOUNDER_TRAFFICS_OFF;
		$dl_mod->unregister();
		unset($dl_mod);

		if ($this->config['dl_enable_post_dl_traffic'] && !$this->config['dl_traffic_off'] && $user_traffics_on && !$founder_traffics_off)
		{
			if (!$this->config['dl_delay_post_traffic'] || ((time() - $this->user->data['user_regdate']) / 84600) > $this->config['dl_delay_post_traffic'])
			{
				$dl_traffic = 0;

				if ($event['mode'] == 'post')
				{
					$dl_traffic = $this->config['dl_newtopic_traffic'];
				}
				else if ($event['mode'] == 'reply' || $event['mode'] == 'quote')
				{
					$dl_traffic = $this->config['dl_reply_traffic'];
				}

				if (intval($dl_traffic) > 0)
				{
					$sql = 'UPDATE ' . USERS_TABLE . '
						SET user_traffic = user_traffic + ' . (int) $dl_traffic . '
						WHERE user_id = ' . (int) $this->user->data['user_id'];
					$this->db->sql_query($sql);
				}
			}
		}
	}

	public function drop_user_traffic_for_post($event)
	{
		$ext_path = $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$ext_path_web = $this->phpbb_path_helper->update_web_root_path($ext_path);
		$table_prefix = $this->table_prefix;
		include_once($ext_path . '/includes/helpers/dl_constants.' . $this->php_ext);

		if (!defined('DL_EXT_CACHE_FOLDER'))
		{
			// Define the basic file storage placement
			if ($this->config['dl_download_dir'] == 2)
			{
				$filebase_prefix = $this->root_path . 'store/oxpus/dlext/';
				$filebase_web_prefix = generate_board_url() . '/store/oxpus/dlext/';
			}
			else
			{
				$filebase_prefix = $ext_path . 'files/';
				$filebase_web_prefix = $ext_path_web . 'files/';
			}

			define('DL_EXT_CACHE_FOLDER',		$filebase_prefix . 'cache/');
			define('DL_EXT_THUMBS_FOLDER',		$filebase_prefix . 'thumbs/');
			define('DL_EXT_THUMBS_WEB_FOLDER',	$filebase_web_prefix . 'thumbs/');
			define('DL_EXT_FILES_FOLDER',		$filebase_prefix . 'downloads/');
			define('DL_EXT_FILES_WEBFOLDER',	$filebase_web_prefix . 'downloads/');
		}

		include_once($ext_path . '/includes/classes/class_dlmod.' . $this->php_ext);
		$dl_mod = new \oxpus\dlext\includes\classes\ dl_mod($this->root_path, $this->php_ext, $ext_path);
		$dl_mod->register();
		\oxpus\dlext\includes\classes\ dl_init::init($ext_path);
		$user_traffics_on = DL_USERS_TRAFFICS;
		$founder_traffics_off = FOUNDER_TRAFFICS_OFF;
		$dl_mod->unregister();
		unset($dl_mod);

		if ($this->config['dl_drop_traffic_postdel'] && !$this->config['dl_traffic_off'] && $user_traffics_on && !$founder_traffics_off)
		{
			if ($event['mode'] == 'delete')
			{
				if ($event['topic_id'] && !$event['post_id'])
				{
					$drop_traffic_amount = $this->config['dl_newtopic_traffic'];

					$sql = 'SELECT topic_poster
						FROM ' . TOPICS_TABLE . '
						WHERE topic_id = ' . $event['topic_id'];
					$result = $this->db->sql_query($sql);
					$poster_id = $this->db->sql_fetchfield('topic_poster');
					$this->db->sql_freeresult($result);
				}
				else if ($event['post_id'])
				{
					$drop_traffic_amount = $this->config['dl_reply_traffic'];

					$sql = 'SELECT poster_id
						FROM ' . POSTS_TABLE . '
						WHERE post_id = ' . $event['post_id'];
					$result = $this->db->sql_query($sql);
					$poster_id = $this->db->sql_fetchfield('poster_id');
					$this->db->sql_freeresult($result);
				}

				if ($poster_id)
				{
					$sql = 'SELECT user_traffic FROM ' . USERS_TABLE . '
						WHERE user_id = ' . (int) $poster_id;
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$user_traffic = $row['user_traffic'];
					$this->db->sql_freeresult($result);

					if ($user_traffic < $drop_traffic_amount)
					{
						$user_traffic = 0;
					}
					else
					{
						$user_traffic -= $drop_traffic_amount;
					}

					$sql = 'UPDATE ' . USERS_TABLE . '
						SET user_traffic = ' . (int) $user_traffic . '
						WHERE user_id = ' . (int) $poster_id;
					$this->db->sql_query($sql);
				}
			}
		}
	}

	public function dl_mod_callback($part)
	{
		$ext_path = $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$table_prefix = $this->table_prefix;
		include_once($ext_path . '/includes/helpers/dl_constants.' . $this->php_ext);

		$sql = 'SELECT description, desc_uid, desc_bitfield, desc_flags FROM ' . DOWNLOADS_TABLE . '
			WHERE id = ' . (int) $part[4];
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$title = $row['description'];
		$desc_uid = $row['desc_uid'];
		$desc_bitfield = $row['desc_bitfield'];
		$desc_flags = $row['desc_flags'];
		$this->db->sql_freeresult($result);

		$title = generate_text_for_display($title, $desc_uid, $desc_bitfield, $desc_flags);

		if ($title)
		{
			return '">' . $title . '</URL>';
		}
		else
		{
			return $part[0];
		}
	}

	public function convert_link_to_download_name($event)
	{
		$content = $event['text'];
		$replacements = array('&amp;', '?');
		$placeholders = array('--AMPERSAND--', '--QUESTIONAIRE--');
		$content = str_replace($replacements, $placeholders, $content);
		$content = preg_replace_callback('#(">)(.*?)(\/dlext\/--QUESTIONAIRE--view=detail--AMPERSAND--df_id=)(\d+)(.*?)(<\/URL>)#i', array('self', 'dl_mod_callback'), $content);
		$content = str_replace($placeholders, $replacements, $content);
		$event['text'] = $content;
	}

	public function add_permission_cat($event)
	{
		$perm_cat = $event['categories'];
		$perm_cat['downloads'] = 'ACP_DOWNLOADS';
		$event['categories'] = $perm_cat;

		$permission = $event['permissions'];
		$permission['a_dl_overview']	= array('lang' => 'ACL_A_DL_OVERVIEW',		'cat' => 'downloads');
		$permission['a_dl_config']		= array('lang' => 'ACL_A_DL_CONFIG',		'cat' => 'downloads');
		$permission['a_dl_traffic']		= array('lang' => 'ACL_A_DL_TRAFFIC',		'cat' => 'downloads');
		$permission['a_dl_categories']	= array('lang' => 'ACL_A_DL_CATEGORIES',	'cat' => 'downloads');
		$permission['a_dl_files']		= array('lang' => 'ACL_A_DL_FILES',			'cat' => 'downloads');
		$permission['a_dl_permissions']	= array('lang' => 'ACL_A_DL_PERMISSIONS',	'cat' => 'downloads');
		$permission['a_dl_stats']		= array('lang' => 'ACL_A_DL_STATS',			'cat' => 'downloads');
		$permission['a_dl_banlist']		= array('lang' => 'ACL_A_DL_BANLIST',		'cat' => 'downloads');
		$permission['a_dl_blacklist']	= array('lang' => 'ACL_A_DL_BLACKLIST',		'cat' => 'downloads');
		$permission['a_dl_toolbox']		= array('lang' => 'ACL_A_DL_TOOLBOX',		'cat' => 'downloads');
		$permission['a_dl_fields']		= array('lang' => 'ACL_A_DL_FIELDS',		'cat' => 'downloads');
		$permission['a_dl_browser']		= array('lang' => 'ACL_A_DL_BROWSER',		'cat' => 'downloads');
		$permission['a_dl_perm_check']	= array('lang' => 'ACL_A_DL_PERM_CHECK',	'cat' => 'downloads');
		$event['permissions'] = $permission;
	}
}
