<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @ignore
 */

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	/* phpbb objects */
	protected $extension_manager;
	protected $db;
	protected $config;
	protected $helper;
	protected $template;
	protected $user;
	protected $language;
	protected $request;
	protected $cache;
	protected $filesystem;

	/* extension owned objects */
	protected $ext_path;

	protected $dlext_auth;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_privacy;
	protected $dlext_physical;
	protected $dlext_constants;

	protected $dl_index;

	protected $dlext_table_dl_cat_traf;
	protected $dlext_table_dl_comments;
	protected $dlext_table_dl_favorites;
	protected $dlext_table_dl_hotlink;
	protected $dlext_table_dl_notraf;
	protected $dlext_table_dl_reports;
	protected $dlext_table_dl_stats;
	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param \phpbb\extension\manager				$extension_manager
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\user							$user
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\cache\service					$cache
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\format 				$dlext_format
	 * @param \oxpus\dlext\core\main 				$dlext_main
	 * @param \oxpus\dlext\core\privacy 			$dlext_privacy
	 * @param \oxpus\dlext\core\physical			$dlext_physical
	 * @param \oxpus\dlext\core\helpers\constants 	$dlext_constants
	 * @param string								$dlext_table_dl_cat_traf
	 * @param string								$dlext_table_dl_comments
	 * @param string								$dlext_table_dl_favorites
	 * @param string								$dlext_table_dl_hotlink
	 * @param string								$dlext_table_dl_notraf
	 * @param string								$dlext_table_dl_reports
	 * @param string								$dlext_table_dl_stats
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		\phpbb\extension\manager $extension_manager,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\cache\service $cache,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\privacy $dlext_privacy,
		\oxpus\dlext\core\physical $dlext_physical,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_cat_traf,
		$dlext_table_dl_comments,
		$dlext_table_dl_favorites,
		$dlext_table_dl_hotlink,
		$dlext_table_dl_notraf,
		$dlext_table_dl_reports,
		$dlext_table_dl_stats,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->extension_manager		= $extension_manager;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;
		$this->request					= $request;
		$this->cache					= $cache;
		$this->filesystem				= $filesystem;

		$this->dlext_table_dl_cat_traf		= $dlext_table_dl_cat_traf;
		$this->dlext_table_dl_comments		= $dlext_table_dl_comments;
		$this->dlext_table_dl_favorites		= $dlext_table_dl_favorites;
		$this->dlext_table_dl_hotlink		= $dlext_table_dl_hotlink;
		$this->dlext_table_dl_notraf		= $dlext_table_dl_notraf;
		$this->dlext_table_dl_reports		= $dlext_table_dl_reports;
		$this->dlext_table_dl_stats			= $dlext_table_dl_stats;
		$this->dlext_table_downloads		= $dlext_table_downloads;
		$this->dlext_table_dl_cat			= $dlext_table_dl_cat;

		$this->ext_path					= $this->extension_manager->get_extension_path('oxpus/dlext', $dlext_constants::DL_TRUE);

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_privacy			= $dlext_privacy;
		$this->dlext_physical			= $dlext_physical;
		$this->dlext_constants			= $dlext_constants;
	}

	public static function getSubscribedEvents()
	{
		return [
			// Board default events
			'core.user_setup'							=> 'core_user_setup',
			'core.page_header'							=> 'core_page_header',
			'core.adm_page_header_after'				=> 'core_adm_page_header_after',
			'core.viewonline_overwrite_location'		=> 'core_viewonline_overwrite_location',
			'core.memberlist_view_profile'				=> 'core_memberlist_view_profile',
			'core.update_username'						=> 'core_update_username',
			'core.delete_user_after'					=> 'core_delete_user_after',
			'core.submit_post_end'						=> 'core_submit_post_end',
			'core.modify_posting_parameters'			=> 'core_modify_posting_parameters',
			'core.modify_text_for_display_before'		=> 'dlext_modify_text_for_dl_link',
			'core.modify_format_display_text_before'	=> 'dlext_modify_text_for_dl_link',
			'core.permissions'							=> 'core_add_permission_cat',
			'core.group_add_user_after'					=> 'core_group_change_user_after',
			'core.group_delete_user_after'				=> 'core_group_change_user_after',
			'core.ucp_display_module_before'			=> 'core_ucp_display_module_before',
			'core.build_config_template'				=> 'core_build_config_template',
			'core.adm_page_footer'						=> 'core_adm_page_footer',

			// Events by extensions
			'tas2580.privacyprotection_delete_ip_after'	=> 'tas2580_privacyprotection_delete_ip_after',
		];
	}

	public function core_user_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'oxpus/dlext',
			'lang_set' => 'common',
		];

		if (defined('ADMIN_START'))
		{
			$lang_set_ext[] = [
				'ext_name' => 'oxpus/dlext',
				'lang_set' => 'dlext_acp',
			];
		}

		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function core_page_header()
	{
		$this->dlext_constants->init();

		$dl_mod_link_show = $this->dlext_main->dl_handle_active($this->dlext_constants::DL_FALSE);

		$this->template->assign_vars([
			'U_DL_HELP_POPUP'	=> $this->helper->route('oxpus_dlext_help'),
		]);

		$dl_index = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_VIEW);

		if (empty($dl_index))
		{
			$dl_mod_link_show = $this->dlext_constants::DL_FALSE;
		}

		if ($dl_mod_link_show)
		{
			$sql = 'SELECT id
					FROM ' . $this->dlext_table_downloads . '
					WHERE add_user = ' . (int) $this->user->data['user_id'];
			$result = $this->db->sql_query_limit($sql, 1);
			$total_downloads = $this->db->sql_affectedrows($result);
			$this->db->sql_freeresult($result);

			$dl_main_link = $this->helper->route('oxpus_dlext_index');
			$dl_main_self = $this->helper->route('oxpus_dlext_search', ['search_user_id' => $this->user->data['user_id']]);

			$this->template->assign_vars([
				'U_DL_NAVI'		=> $dl_main_link,
				'U_DL_SELF'		=> ($total_downloads) ? $dl_main_self : '',
			]);

			if ($this->config['dl_use_hacklist'])
			{
				$sql = 'SELECT id FROM ' . $this->dlext_table_downloads . '
					WHERE hacklist = 1';
				$result = $this->db->sql_query_limit($sql, 1);
				$total_hl = $this->db->sql_affectedrows($result);
				$this->db->sql_freeresult($result);

				if ($total_hl)
				{
					$this->template->assign_vars([
						'U_DL_HACKS_LIST'	=> $this->helper->route('oxpus_dlext_hacklist'),
					]);
				}
			}

			if (!empty($this->user->data['is_registered']) && $this->user->data['is_registered'])
			{
				$sql = 'SELECT d.id FROM ' . $this->dlext_table_downloads . ' d, ' . $this->dlext_table_dl_cat . ' c
					WHERE c.id = d.cat
						AND c.bug_tracker = 1';
				$result = $this->db->sql_query_limit($sql, 1);
				$total_bl = $this->db->sql_affectedrows($result);
				$this->db->sql_freeresult($result);

				if ($total_bl)
				{
					$this->template->assign_vars([
						'U_DL_BUG_TRACKER'	=> $this->helper->route('oxpus_dlext_tracker_view'),
					]);
				}
			}

			$this->_dl_reset_values();
			$this->_dl_navi_links();
			$this->_dl_purge_hotlinks();
		}
	}

	public function core_adm_page_header_after()
	{
		$file_uploads			= ini_get('file_uploads');
		$max_file_uploads		= ini_get('max_file_uploads');
		$max_execution_time		= ini_get('max_execution_time');
		$max_input_time			= ini_get('max_input_time');
		$memory_limit			= ini_get('memory_limit');
		$post_max_size			= ini_get('post_max_size');
		$upload_max_filesize	= ini_get('upload_max_filesize');

		$physical_limit = $this->config['dl_physical_quota'];
		$total_size = $this->dlext_physical->read_dl_sizes();
		$total_size = ($total_size > $physical_limit) ? $physical_limit : $total_size;
		$total_limit_remain = $this->dlext_format->dl_size($physical_limit - $total_size, 2);

		$remain_traffic = $this->dlext_format->dl_size($this->config['dl_overall_traffic'] - (int) $this->config['dl_remain_traffic'], 2);
		$remain_guest_traffic = $this->dlext_format->dl_size($this->config['dl_overall_guest_traffic'] - (int) $this->config['dl_remain_guest_traffic'], 2);

		$this->template->assign_vars([
			'DL_LIMIT_PHP_FILE_UPLOAD'			=> $file_uploads,
			'DL_LIMIT_PHP_MAX_FILE_UPLOAD'		=> $max_file_uploads,
			'DL_LIMIT_PHP_MAX_INPUT_TIME'		=> $max_input_time,
			'DL_LIMIT_PHP_MAX_EXECUTION_TIME'	=> $max_execution_time,
			'DL_LIMIT_PHP_MEMORY_LIMIT'			=> $memory_limit,
			'DL_LIMIT_PHP_POST_MAX_SIZE'		=> $post_max_size,
			'DL_LIMIT_PHP_UPLOAD_MAX_FILESIZE'	=> $upload_max_filesize,

			'DL_LIMIT_TRAFFIC_USER_REMAIN'		=> $remain_traffic,
			'DL_LIMIT_TRAFFIC_GUESTS_REMAIN'	=> $remain_guest_traffic,
			'DL_LIMIT_TOTAL_REMAIN'				=> $total_limit_remain,
			'DL_LIMIT_THUMBNAIL_SIZE'			=> $this->dlext_format->dl_size($this->config['dl_thumb_fsize'],2),
			'DL_LIMIT_THUMBNAIL_XY_SIZE'		=> $this->language->lang('DL_LIMIT_THUMBNAIL_XYSIZE', $this->config['dl_thumb_xsize'], $this->config['dl_thumb_ysize']),
			'DL_PHP_INI'						=> $this->language->lang('DL_PHP_INI_EXPLAIN', php_ini_loaded_file()),

			'S_DL_TRAFFIC_OFF'					=> $this->config['dl_traffic_off'],
		]);
	}

	public function core_viewonline_overwrite_location($event)
	{
		if (strpos($event['row']['session_page'], 'hacklist') !== false)
		{
			$event['location'] = $this->language->lang('DL_PAGE_DL_HACKSLIST');
			$event['location_url'] = $this->helper->route('oxpus_dlext_hacklist');
		}
		else if (strpos($event['row']['session_page'], 'dlext/tracker/edit') !== false)
		{
			$event['location'] = $this->language->lang('DL_PAGE_BUG_TRACKER');
			$event['location_url'] = $this->helper->route('oxpus_dlext_tracker_edit');
		}
		else if (strpos($event['row']['session_page'], 'dlext/tracker/view') !== false)
		{
			$event['location'] = $this->language->lang('DL_PAGE_BUG_TRACKER');
			$event['location_url'] = $this->helper->route('oxpus_dlext_tracker_view');
		}
		else if (strpos($event['row']['session_page'], 'dlext/tracker/main') !== false)
		{
			$event['location'] = $this->language->lang('DL_PAGE_BUG_TRACKER');
			$event['location_url'] = $this->helper->route('oxpus_dlext_tracker_main');
		}
		else if (strpos($event['row']['session_page'], 'dlext') !== false)
		{
			$event['location'] = $this->language->lang('DL_PAGE_DOWNLOADS');
			$event['location_url'] = $this->helper->route('oxpus_dlext_index');
		}
	}

	public function core_memberlist_view_profile($event)
	{
		$member	= $event['member'];

		$user_id	= $member['user_id'];

		$sql = 'SELECT COUNT(id) as total
				FROM ' . $this->dlext_table_downloads . '
				WHERE add_user = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$total_downloads = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(id) as total
				FROM ' . $this->dlext_table_dl_comments . '
				WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$total_comments = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'DL_COMMENTS'		=> $total_comments,
			'DL_DOWNLOADS'		=> $total_downloads,
			'U_DL_DOWNLOADS'	=> ($total_downloads) ? $this->helper->route('oxpus_dlext_search', ['search_user_id' => $user_id]) : '',
		]);

		if (!$this->config['dl_traffic_off'])
		{
			$user_traffic = $this->dlext_format->dl_size($member['user_traffic'], 2, 'combine');

			$this->template->assign_block_vars('custom_fields', [
				'PROFILE_FIELD_NAME'	=> $this->language->lang('DL_REMAIN_USER_TRAFFIC'),
				'PROFILE_FIELD_VALUE'	=> $user_traffic,
			]);
		}
	}

	public function core_update_username($event)
	{
		$update_ary = [$this->dlext_table_dl_comments, $this->dlext_table_dl_stats];

		foreach ($update_ary as $table)
		{
			$sql = "UPDATE $table
				SET username = '" . $this->db->sql_escape($event['new_name']) . "'
				WHERE username = '" . $this->db->sql_escape($event['old_name']) . "'";
			$this->db->sql_query($sql);
		}
	}

	public function core_delete_user_after($event)
	{
		$table_ary = [$this->dlext_table_dl_notraf, $this->dlext_table_dl_reports];

		// Delete the miscellaneous (non-post) data for the user
		foreach ($table_ary as $table)
		{
			$sql = "DELETE FROM $table
				WHERE " . $this->db->sql_in_set('user_id', $event['user_ids']);
			$this->db->sql_query($sql);
		}

		$sql = 'DELETE FROM ' . $this->dlext_table_dl_favorites . '
			WHERE ' . $this->db->sql_in_set('fav_user_id', $event['user_ids']);
		$this->db->sql_query($sql);
	}

	public function core_submit_post_end($event)
	{
		if ($this->config['dl_enable_post_dl_traffic'] && !$this->config['dl_traffic_off'] && $this->dlext_constants->get_value('users_traffics') && !$this->dlext_constants->get_value('founder_traffics'))
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

	public function core_modify_posting_parameters($event)
	{
		if ($this->config['dl_drop_traffic_postdel'] && !$this->config['dl_traffic_off'] && $this->dlext_constants->get_value('users_traffics') && !$this->dlext_constants->get_value('founder_traffics'))
		{
			if ($event['mode'] == 'delete')
			{
				if ($event['topic_id'] && !$event['post_id'])
				{
					$drop_traffic_amount = $this->config['dl_newtopic_traffic'];

					$sql = 'SELECT topic_poster
						FROM ' . TOPICS_TABLE . '
						WHERE topic_id = ' . (int) $event['topic_id'];
					$result = $this->db->sql_query($sql);
					$poster_id = $this->db->sql_fetchfield('topic_poster');
					$this->db->sql_freeresult($result);
				}
				else if ($event['post_id'])
				{
					$drop_traffic_amount = $this->config['dl_reply_traffic'];

					$sql = 'SELECT poster_id
						FROM ' . POSTS_TABLE . '
						WHERE post_id = ' . (int) $event['post_id'];
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

	public function dlext_modify_text_for_dl_link($event)
	{
		$content = $event['text'];

		$content = preg_replace_callback('#<a href="((.*?)">(.*?)(df_id=)(\d+))<\/a>#i', ['self', '_dl_mod_callback'], $content);
		$content = preg_replace_callback('#<\/s>(.*?)(df_id=)(\d+)<e>#i', ['self', '_dl_mod_callback'], $content);
		$content = preg_replace_callback('#<LINK_TEXT text="(.*?)">(.*?)(df_id=)(\d+)<\/LINK_TEXT>#i', ['self', '_dl_mod_callback'], $content);
		$content = preg_replace_callback('#<URL url="(.*?)(df_id=)(\d+)">(.*?)(df_id=)(\d+)<\/url>#i', ['self', '_dl_mod_callback'], $content);

		$event['text'] = $content;
	}

	public function core_add_permission_cat($event)
	{
		$perm_cat = $event['categories'];
		$perm_cat['downloads'] = 'ACP_DOWNLOADS';
		$event['categories'] = $perm_cat;

		$permission = $event['permissions'];
		$permission['a_dl_overview']	= ['lang' => 'ACL_A_DL_OVERVIEW',		'cat' => 'downloads'];
		$permission['a_dl_config']		= ['lang' => 'ACL_A_DL_CONFIG',			'cat' => 'downloads'];
		$permission['a_dl_traffic']		= ['lang' => 'ACL_A_DL_TRAFFIC',		'cat' => 'downloads'];
		$permission['a_dl_categories']	= ['lang' => 'ACL_A_DL_CATEGORIES',		'cat' => 'downloads'];
		$permission['a_dl_files']		= ['lang' => 'ACL_A_DL_FILES',			'cat' => 'downloads'];
		$permission['a_dl_permissions']	= ['lang' => 'ACL_A_DL_PERMISSIONS',	'cat' => 'downloads'];
		$permission['a_dl_stats']		= ['lang' => 'ACL_A_DL_STATS',			'cat' => 'downloads'];
		$permission['a_dl_blacklist']	= ['lang' => 'ACL_A_DL_BLACKLIST',		'cat' => 'downloads'];
		$permission['a_dl_toolbox']		= ['lang' => 'ACL_A_DL_TOOLBOX',		'cat' => 'downloads'];
		$permission['a_dl_fields']		= ['lang' => 'ACL_A_DL_FIELDS',			'cat' => 'downloads'];
		$permission['a_dl_perm_check']	= ['lang' => 'ACL_A_DL_PERM_CHECK',		'cat' => 'downloads'];
		$permission['a_dl_assistant']	= ['lang' => 'ACL_A_DL_ASSISTANT',		'cat' => 'downloads'];
		$event['permissions'] = $permission;
	}

	// Using privacy protection by tas2580
	public function tas2580_privacyprotection_delete_ip_after()
	{
		$this->dlext_privacy->dl_privacy();
	}

	private function _dl_mod_callback($part)
	{
		if (!empty($part[5]))
		{
			$dl_id = $part[5];
			$link_text = 'preview';
		}
		else if (!empty($part[4]))
		{
			$dl_id = $part[4];
			$link_text = 'link_text';
		}
		else if (!empty($part[6]))
		{
			$dl_id = $part[6];
			$link_text = 'url';
		}
		else
		{
			$dl_id = $part[3];
			$link_text = 'postlink';
		}

		$sql = 'SELECT c.cat_name, d.description, d.desc_uid, d.desc_bitfield, d.desc_flags
				FROM ' . $this->dlext_table_downloads . ' d, ' . $this->dlext_table_dl_cat . ' c
				WHERE c.id = d.cat
					AND d.id = ' . (int) $dl_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$dl_found = $this->db->sql_affectedrows();
		$this->db->sql_freeresult($result);

		if (!$dl_found)
		{
			return $part[0];
		}

		$title			= $row['description'];
		$desc_uid		= $row['desc_uid'];
		$desc_bitfield	= $row['desc_bitfield'];
		$desc_flags		= $row['desc_flags'];
		$cat_name		= $row['cat_name'];

		$title = generate_text_for_display($title, $desc_uid, $desc_bitfield, $desc_flags);

		if ($title)
		{
			if ($this->config['dl_topic_post_catname'])
			{
				$title .= ' (' . $cat_name . ')';
			}

			$title = strip_tags($title);

			switch ($link_text)
			{
				case 'preview':
					$link_title = '<a href="' . $part[2] . '">' . $title . '</a>';
					break;
				case 'link_text':
					$link_title = '<LINK_TEXT text="' . $title . '">' . $title . '</LINK_TEXT>';
					break;
				case 'url':
					$link_title = '<URL url="' .  $part[1] .  $part[2] .  $part[3]  . '">' . $title . '</URL>';
					break;
				case 'postlink':
					$link_title = '</s>' . $title . '<e>';
			}

			return $link_title;
		}
		else
		{
			return $part[0];
		}
	}

	private function _dl_reset_values()
	{
		$current_month	= gmdate('Ym', time());

		/*
		* set the overall traffic and categories traffic if needed (each first day of a month)
		*/
		if (isset($this->config['dl_traffic_retime']) && !$this->config['dl_traffic_off'])
		{
			$auto_overall_traffic_month = gmdate('Ym', $this->config['dl_traffic_retime']);

			if ($auto_overall_traffic_month < $current_month)
			{
				$this->config['dl_traffic_retime'] = time();
				$this->config['dl_remain_traffic'] = 0;
				$this->config['dl_remain_guest_traffic'] = 0;

				$this->config->set('dl_remain_traffic', 0);
				$this->config->set('dl_remain_guest_traffic', 0);

				$sql = 'UPDATE ' . $this->dlext_table_dl_cat_traf . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'cat_traffic_use' => 0
				]);
				$this->db->sql_query($sql);

				$this->config->set('dl_traffic_retime', $this->config['dl_traffic_retime']);
			}
		}

		/*
		* reset download clicks (each first day of a month)
		*/
		if (isset($this->config['dl_click_reset_time']))
		{
			$auto_click_reset_month = gmdate('Ym', $this->config['dl_click_reset_time']);

			if ($auto_click_reset_month < $current_month)
			{
				$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'klicks' => 0
				]);
				$this->db->sql_query($sql);

				$this->cache->destroy('_dlext_file_p.');

				$this->config->set('dl_click_reset_time', time());
			}
		}

		/*
		* set the user traffic if needed (each first day of the month)
		*/
		if ($this->user->data['user_id'] != ANONYMOUS && !$this->config['dl_traffic_off'] && (intval($this->config['dl_delay_auto_traffic']) == 0 || (time() - $this->user->data['user_regdate']) / 84600 > $this->config['dl_delay_auto_traffic']))
		{
			$user_auto_traffic_month = gmdate('Ym', $this->user->data['user_dl_update_time']);

			if ($user_auto_traffic_month < $current_month)
			{
				$sql = 'SELECT max(g.group_dl_auto_traffic) AS max_traffic FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
					WHERE g.group_id = ug.group_id
						AND ug.user_id = ' . (int) $this->user->data['user_id'] . '
						AND ug.user_pending <> 1';
				$result = $this->db->sql_query($sql);
				$max_group_row = $this->db->sql_fetchfield('max_traffic');
				$this->db->sql_freeresult($result);

				$new_user_traffic = (intval($max_group_row) != 0) ? $max_group_row : $this->config['dl_user_dl_auto_traffic'];

				if ($new_user_traffic > $this->user->data['user_traffic'])
				{
					$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'user_traffic'			=> $new_user_traffic,
						'user_dl_update_time'	=> time()
					]) . ' WHERE user_id = ' . (int) $this->user->data['user_id'];
					$this->db->sql_query($sql);
				}
			}
		}
	}

	public function core_group_change_user_after()
	{
		$this->cache->destroy('_dlext_auth_groups');
	}

	public function core_ucp_display_module_before($event)
	{
		$mode = $event['mode'];

		if ($mode == '' || $mode == 'front')
		{
			$user_id = $this->user->data['user_id'];

			$sql = 'SELECT COUNT(id) as total
					FROM ' . $this->dlext_table_downloads . '
					WHERE add_user = ' . (int) $user_id;
			$result = $this->db->sql_query($sql);
			$total_downloads = $this->db->sql_fetchfield('total');
			$this->db->sql_freeresult($result);

			$sql = 'SELECT COUNT(id) as total
					FROM ' . $this->dlext_table_dl_comments . '
					WHERE user_id = ' . (int) $user_id;
			$result = $this->db->sql_query($sql);
			$total_comments = $this->db->sql_fetchfield('total');
			$this->db->sql_freeresult($result);

			$this->template->assign_vars([
				'DL_COMMENTS'		=> $total_comments,
				'DL_DOWNLOADS'		=> $total_downloads,
				'U_DL_DOWNLOADS'	=> ($total_downloads) ? $this->helper->route('oxpus_dlext_search', ['search_user_id' => $user_id]) : '',
			]);
		}
	}

	private function _dl_navi_links()
	{
		$this->template->assign_vars([
			'S_DL_NAV_MAIN_NHQLB'		=> ($this->config['dl_nav_link_main'] == 'NHQLB') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_MAIN_NHQLA'		=> ($this->config['dl_nav_link_main'] == 'NHQLA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_MAIN_OHNP' 		=> ($this->config['dl_nav_link_main'] == 'OHNP') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_MAIN_OHNA' 		=> ($this->config['dl_nav_link_main'] == 'OHNA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_MAIN_NHUPP'		=> ($this->config['dl_nav_link_main'] == 'NHUPP') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_MAIN_NHUP' 		=> ($this->config['dl_nav_link_main'] == 'NHUP') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_MAIN_NHPLB'		=> ($this->config['dl_nav_link_main'] == 'NHPLB') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_MAIN_NHPLA'		=> ($this->config['dl_nav_link_main'] == 'NHPLA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_MAIN_NHUA'		=> ($this->config['dl_nav_link_main'] == 'NHUA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_MAIN_NHUPA'		=> ($this->config['dl_nav_link_main'] == 'NHUPA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_MAIN_OFTzB'		=> ($this->config['dl_nav_link_main'] == 'OFTzB') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_MAIN_OFTzA'		=> ($this->config['dl_nav_link_main'] == 'OFTzA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_MAIN_OFTlB'		=> ($this->config['dl_nav_link_main'] == 'OFTlB') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_MAIN_OFTlA'		=> ($this->config['dl_nav_link_main'] == 'OFTlA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,

			'S_DL_NAV_HACKS_NHQLB'		=> ($this->config['dl_nav_link_hacks'] == 'NHQLB') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_HACKS_NHQLA'		=> ($this->config['dl_nav_link_hacks'] == 'NHQLA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_HACKS_OHNP' 		=> ($this->config['dl_nav_link_hacks'] == 'OHNP') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_HACKS_OHNA' 		=> ($this->config['dl_nav_link_hacks'] == 'OHNA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_HACKS_NHUPP'		=> ($this->config['dl_nav_link_hacks'] == 'NHUPP') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_HACKS_NHUP' 		=> ($this->config['dl_nav_link_hacks'] == 'NHUP') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_HACKS_NHPLB'		=> ($this->config['dl_nav_link_hacks'] == 'NHPLB') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_HACKS_NHPLA'		=> ($this->config['dl_nav_link_hacks'] == 'NHPLA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_HACKS_NHUA'		=> ($this->config['dl_nav_link_hacks'] == 'NHUA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_HACKS_NHUPA'		=> ($this->config['dl_nav_link_hacks'] == 'NHUPA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_HACKS_OFTzB'		=> ($this->config['dl_nav_link_hacks'] == 'OFTzB') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_HACKS_OFTzA'		=> ($this->config['dl_nav_link_hacks'] == 'OFTzA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_HACKS_OFTlB'		=> ($this->config['dl_nav_link_hacks'] == 'OFTlB') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_HACKS_OFTlA'		=> ($this->config['dl_nav_link_hacks'] == 'OFTlA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,

			'S_DL_NAV_TRACKER_NHQLB'	=> ($this->config['dl_nav_link_tracker'] == 'NHQLB') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_TRACKER_NHQLA'	=> ($this->config['dl_nav_link_tracker'] == 'NHQLA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_TRACKER_OHNP' 	=> ($this->config['dl_nav_link_tracker'] == 'OHNP') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_TRACKER_OHNA' 	=> ($this->config['dl_nav_link_tracker'] == 'OHNA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_TRACKER_NHUPP'	=> ($this->config['dl_nav_link_tracker'] == 'NHUPP') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_TRACKER_NHUP' 	=> ($this->config['dl_nav_link_tracker'] == 'NHUP') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_TRACKER_NHPLB'	=> ($this->config['dl_nav_link_tracker'] == 'NHPLB') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_TRACKER_NHPLA'	=> ($this->config['dl_nav_link_tracker'] == 'NHPLA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_TRACKER_NHUA'		=> ($this->config['dl_nav_link_tracker'] == 'NHUA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_TRACKER_NHUPA'	=> ($this->config['dl_nav_link_tracker'] == 'NHUPA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_TRACKER_OFTzB'	=> ($this->config['dl_nav_link_tracker'] == 'OFTzB') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_TRACKER_OFTzA'	=> ($this->config['dl_nav_link_tracker'] == 'OFTzA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_TRACKER_OFTlB'	=> ($this->config['dl_nav_link_tracker'] == 'OFTlB') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_NAV_TRACKER_OFTlA'	=> ($this->config['dl_nav_link_tracker'] == 'OFTlA') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
		]);
	}

	public function core_build_config_template($event)
	{
		$tpl_type	= $event['tpl_type'];
		$new_ary	= $event['new'];
		$key		= $event['key'];
		$tpl		= $event['tpl'];
		$name		= 'config[' . $key . ']';
		$checked	= ($new_ary[$key]) ? 'checked' : '';

		if ($tpl_type[0] == 'switch')
		{
			$tpl = '<input type="checkbox" name="' . $name . '"  value="1" ' . $checked . ' class="radio switch" id="switch_' . $key . '"><label class="switch" for="switch_' . $key . '">&nbsp;</label>';
		}

		$event['tpl'] = $tpl;
	}

	private function _dl_purge_hotlinks()
	{
		$user_ids = [ANONYMOUS, $this->user->data['user_id']];

		$sql = 'DELETE FROM ' . $this->dlext_table_dl_hotlink . '
			WHERE ' . $this->db->sql_in_set('user_id', $user_ids);
		$this->db->sql_query($sql);
	}

	public function core_adm_page_footer()
	{
		// It is necessary to check whether the extension is enabled,
		// otherwise error messages will be displayed after deactivation
		if ($this->extension_manager->is_enabled('oxpus/dlext'))
		{
			$this->dlext_constants->init();

			$this->template->assign_vars([
				'U_DL_HELP_POPUP'	=> $this->helper->route('oxpus_dlext_help'),
			]);
		}
	}
}
