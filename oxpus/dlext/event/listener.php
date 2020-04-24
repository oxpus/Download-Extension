<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
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
class listener implements EventSubscriberInterface
{
	/* @var string phpbb_root_path */
	protected $root_path;

	/* @var string phpEx */
	protected $php_ext;

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

	/** @var extension owned objects */
	protected $ext_path;
	protected $ext_path_web;

	protected $dlext_auth;
	protected $dlext_format;
	protected $dlext_privacy;

	protected $dl_index;

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
	public function __construct(
		$root_path,
		$php_ext,
		$table_prefix,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		Container $phpbb_container,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\auth\auth $auth,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		$dlext_auth,
		$dlext_format,
		$dlext_privacy
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
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

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->phpbb_path_helper		= $this->phpbb_container->get('path_helper');
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_format				= $dlext_format;
		$this->dlext_privacy			= $dlext_privacy;

		$this->dl_index					= $this->dlext_auth->dl_index();
	}

	static public function getSubscribedEvents()
	{
		return array(
			// Board default events
			'core.user_setup'							=> 'core_user_setup',
			'core.page_header'							=> 'core_page_header',
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

			// Events by extensions
			'tas2580.privacyprotection_delete_ip_after'		=> 'tas2580_privacyprotection_delete_ip_after',
		);
	}

	public function core_user_setup($event)
	{
		$this->phpbb_container->get('oxpus.dlext.constants')->init();

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

	public function core_page_header($event)
	{
		$dl_mod_is_active = true;
		$dl_mod_link_show = true;

		$this->template->assign_vars(array(
			'EXT_DL_PATH'			=> $this->ext_path,
			'EXT_DL_PATH_WEB'		=> $this->ext_path_web,
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

		if (!$this->config['dl_global_guests'] && !$this->user->data['is_registered'])
		{
			$dl_mod_link_show = false;
		}

		$dl_index = $this->dlext_auth->dl_index();

		if (!is_array($dl_index) || !sizeof($dl_index))
		{
			$dl_mod_link_show = false;
		}

		if ($dl_mod_link_show)
		{
			$dl_main_link = $this->helper->route('oxpus_dlext_index');

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
						$dl_hacks_link = $this->helper->route('oxpus_dlext_hacklist');

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
					$dl_bt_link = $this->helper->route('oxpus_dlext_tracker');

					$this->template->assign_vars(array(
						'U_DL_BUG_TRACKER'	=> $dl_bt_link,
					));
				}
			}

			$this->_dl_add_download_message($event);
			$this->_dl_reset_values();
			$this->_dl_navi_links();
		}
	}

	public function core_viewonline_overwrite_location($event)
	{
		if (strpos($event['row']['session_page'], 'hacklist') !== false)
		{
			$event['location'] = $this->language->lang('DL_PAGE_DL_HACKSLIST');
			$event['location_url'] = $this->helper->route('oxpus_dlext_hacklist');
		}
		else if (strpos($event['row']['session_page'], 'dlext/tracker') !== false)
		{
			$event['location'] = $this->language->lang('DL_PAGE_BUG_TRACKER');
			$event['location_url'] = $this->helper->route('oxpus_dlext_tracker');
		}
		else if (strpos($event['row']['session_page'], 'dlext') !== false)
		{
			$event['location'] = $this->language->lang('DL_PAGE_DOWNLOADS');
			$event['location_url'] = $this->helper->route('oxpus_dlext_index');
		}
	}

	public function core_memberlist_view_profile($event)
	{
		if (!$this->config['dl_traffic_off'])
		{
			$user_traffic = $this->dlext_format->dl_size($event['member']['user_traffic'], 2, 'combine');

			$this->template->assign_block_vars('custom_fields', array(
				'PROFILE_FIELD_NAME'	=> $this->language->lang('DL_REMAIN_USER_TRAFFIC'),
				'PROFILE_FIELD_VALUE'	=> $user_traffic,
			));
		}
	}

	public function core_update_username($event)
	{
		if (!defined('DL_BANLIST_TABLE'))
		{
			$this->phpbb_container->get('oxpus.dlext.constants')->init();
		}

		$update_ary = array(DL_BANLIST_TABLE, DL_COMMENTS_TABLE, DL_STATS_TABLE);

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
		if (!defined('DL_NOTRAF_TABLE'))
		{
			$this->phpbb_container->get('oxpus.dlext.constants')->init();
		}

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

	public function core_submit_post_end($event)
	{
		if (!defined('UL_USERS_TRAFFICS'))
		{
			$this->phpbb_container->get('oxpus.dlext.constants')->init();
		}

		$user_traffics_on = DL_USERS_TRAFFICS;
		$founder_traffics_off = FOUNDER_TRAFFICS_OFF;

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

	public function core_modify_posting_parameters($event)
	{
		if (!defined('UL_USERS_TRAFFICS'))
		{
			$this->phpbb_container->get('oxpus.dlext.constants')->init();
		}

		$user_traffics_on = DL_USERS_TRAFFICS;
		$founder_traffics_off = FOUNDER_TRAFFICS_OFF;

		if ($this->config['dl_drop_traffic_postdel'] && !$this->config['dl_traffic_off'] && $user_traffics_on && !$founder_traffics_off)
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

		$content = preg_replace_callback('#<a href="((.*?)">(.*?)(df_id=)(\d+))<\/a>#i', array('self', '_dl_mod_callback'), $content);
		$content = preg_replace_callback('#<\/s>(.*?)(df_id=)(\d+)<e>#i', array('self', '_dl_mod_callback'), $content);
		$content = preg_replace_callback('#<LINK_TEXT text="(.*?)">(.*?)(df_id=)(\d+)<\/LINK_TEXT>#i', array('self', '_dl_mod_callback'), $content);

		$event['text'] = $content;
	}

	public function core_add_permission_cat($event)
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
		$permission['a_dl_perm_check']	= array('lang' => 'ACL_A_DL_PERM_CHECK',	'cat' => 'downloads');
		$event['permissions'] = $permission;
	}

	// Using privacy protection by tas2580
	public function tas2580_privacyprotection_delete_ip_after($event)
	{
		$this->dlext_privacy->dl_privacy();
	}

	private function _dl_add_download_message($event)
	{
		if ( isset($this->user->data['user_new_download']) && $this->user->data['user_new_download'] && $this->user->data['user_dl_note_type'] <> 2)
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_new_download = 0
				WHERE user_id = ' . (int)$this->user->data['user_id'];
			$this->db->sql_query($sql);

			$new_dl_link = $this->helper->route('oxpus_dlext_latest');

			$this->template->assign_vars(array(
				'NEW_DOWNLOAD_MESSAGE'	=> $this->language->lang('NEW_DOWNLOAD', $new_dl_link),
				'S_NEW_DL_POPUP'		=> ($this->user->data['user_dl_note_type'] == 1) ? true : false,
				'S_NEW_DL_MESSAGE'		=> ($this->user->data['user_dl_note_type'] == 0) ? true : false,
			));
		}
	}

	private function _dl_mod_callback($part)
	{
		if (!defined('DOWNLOADS_TABLE'))
		{
			$this->phpbb_container->get('oxpus.dlext.constants')->init();
		}

		if (isset($part[5]) && (int) $part[5])
		{
			$dl_id = $part[5];
			$link_text = 'preview';
		}
		else if (isset($part[4]) && (int) $part[4])
		{
			$dl_id = $part[4];
			$link_text = 'link_text';
		}
		else
		{
			$dl_id = $part[3];
			$link_text = 'postlink';
		}

		$sql = 'SELECT cat, description, desc_uid, desc_bitfield, desc_flags FROM ' . DOWNLOADS_TABLE . '
			WHERE id = ' . (int) $dl_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);

		$title			= $row['description'];
		$desc_uid		= $row['desc_uid'];
		$desc_bitfield	= $row['desc_bitfield'];
		$desc_flags		= $row['desc_flags'];
		$cat_id			= $row['cat'];

		$this->db->sql_freeresult($result);

		$title = generate_text_for_display($title, $desc_uid, $desc_bitfield, $desc_flags);

		if ($title)
		{
			if ($this->config['dl_topic_title_catname'])
			{
				$title .= ' (' . $this->dl_index[$cat_id]['cat_name_nav'] . ')';
			}
	
			switch ($link_text)
			{
				case 'preview':
					$link_title = '<a href="' . $part[2] . '">' . $title . '</a>';
				break;
				case 'link_text':
					$link_title = '<LINK_TEXT text="' . $title . '">' . $title . '</LINK_TEXT>';
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
		if (!defined('DL_CAT_TRAF_TABLE'))
		{
			$this->phpbb_container->get('oxpus.dlext.constants')->init();
		}

		$current_month	= @gmdate('Ym', time());

		/*
		* set the overall traffic and categories traffic if needed (each first day of a month)
		*/
		if (isset($this->config['dl_traffic_retime']) && !$this->config['dl_traffic_off'])
		{
			$auto_overall_traffic_month = @gmdate('Ym', $this->config['dl_traffic_retime']);

			if ($auto_overall_traffic_month < $current_month)
			{
				$this->config['dl_traffic_retime'] = time();
				$this->config['dl_remain_traffic'] = 0;
				$this->config['dl_remain_guest_traffic'] = 0;

				$this->config->set('dl_remain_traffic', 0);
				$this->config->set('dl_remain_guest_traffic', 0);

				$sql = 'UPDATE ' . DL_CAT_TRAF_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
					'cat_traffic_use' => 0));
				$this->db->sql_query($sql);

				$this->config->set('dl_traffic_retime', $this->config['dl_traffic_retime'], false);
			}
		}

		/*
		* reset download clicks (each first day of a month)
		*/
		if (isset($this->config['dl_click_reset_time']))
		{
			$auto_click_reset_month = @gmdate('Ym', $this->config['dl_click_reset_time']);

			if ($auto_click_reset_month < $current_month)
			{
				$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
					'klicks' => 0));
				$this->db->sql_query($sql);

				@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_p.' . $this->php_ext);

				$this->config->set('dl_click_reset_time', time(), false);
			}
		}

		/*
		* set the user traffic if needed (each first day of the month)
		*/
		if ($this->user->data['user_id'] <> ANONYMOUS && !$this->config['dl_traffic_off'] && (intval($this->config['dl_delay_auto_traffic']) == 0 || (time() - $this->user->data['user_regdate']) / 84600 > $this->config['dl_delay_auto_traffic']))
		{
			$user_auto_traffic_month = @gmdate('Ym', $this->user->data['user_dl_update_time']);

			if ($user_auto_traffic_month < $current_month)
			{
				$sql = 'SELECT max(g.group_dl_auto_traffic) AS max_traffic FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
					WHERE g.group_id = ug.group_id
						AND ug.user_id = ' . (int) $this->user->data['user_id'] . '
						AND ug.user_pending <> ' . true;
				$result = $this->db->sql_query($sql);
				$max_group_row = $this->db->sql_fetchfield('max_traffic');
				$this->db->sql_freeresult($result);

				$new_user_traffic = (intval($max_group_row) != 0) ? $max_group_row : $this->config['dl_user_dl_auto_traffic'];

				if ($new_user_traffic > $this->user->data['user_traffic'])
				{
					$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
						'user_traffic'			=> $new_user_traffic,
						'user_dl_update_time'	=> time())) . ' WHERE user_id = ' . (int) $this->user->data['user_id'];
					$this->db->sql_query($sql);
				}
			}
		}
	}

	public function core_group_change_user_after($event)
	{
		if (!defined('DL_EXT_CACHE_PATH'))
		{
			$this->phpbb_container->get('oxpus.dlext.constants')->init();
		}

		@unlink(DL_EXT_CACHE_PATH . 'data_dl_auth_groups.' . $this->php_ext);
	}

	private function _dl_navi_links()
	{
		$this->template->assign_vars(array(
			'S_DL_NAV_MAIN_NHQLB'		=> ($this->config['dl_nav_link_main'] == 'NHQLB') ? true : false,
			'S_DL_NAV_MAIN_NHQLA'		=> ($this->config['dl_nav_link_main'] == 'NHQLA') ? true : false,
			'S_DL_NAV_MAIN_OHNP' 		=> ($this->config['dl_nav_link_main'] == 'OHNP') ? true : false,
			'S_DL_NAV_MAIN_OHNA' 		=> ($this->config['dl_nav_link_main'] == 'OHNA') ? true : false,
			'S_DL_NAV_MAIN_NHUPP'		=> ($this->config['dl_nav_link_main'] == 'NHUPP') ? true : false,
			'S_DL_NAV_MAIN_NHUP' 		=> ($this->config['dl_nav_link_main'] == 'NHUP') ? true : false,
			'S_DL_NAV_MAIN_NHPLB'		=> ($this->config['dl_nav_link_main'] == 'NHPLB') ? true : false,
			'S_DL_NAV_MAIN_NHPLA'		=> ($this->config['dl_nav_link_main'] == 'NHPLA') ? true : false,
			'S_DL_NAV_MAIN_NHUA'		=> ($this->config['dl_nav_link_main'] == 'NHUA') ? true : false,
			'S_DL_NAV_MAIN_NHUPA'		=> ($this->config['dl_nav_link_main'] == 'NHUPA') ? true : false,
			'S_DL_NAV_MAIN_OFTzB'		=> ($this->config['dl_nav_link_main'] == 'OFTzB') ? true : false,
			'S_DL_NAV_MAIN_OFTzA'		=> ($this->config['dl_nav_link_main'] == 'OFTzA') ? true : false,
			'S_DL_NAV_MAIN_OFTlB'		=> ($this->config['dl_nav_link_main'] == 'OFTlB') ? true : false,
			'S_DL_NAV_MAIN_OFTlA'		=> ($this->config['dl_nav_link_main'] == 'OFTlA') ? true : false,

			'S_DL_NAV_HACKS_NHQLB'		=> ($this->config['dl_nav_link_hacks'] == 'NHQLB') ? true : false,
			'S_DL_NAV_HACKS_NHQLA'		=> ($this->config['dl_nav_link_hacks'] == 'NHQLA') ? true : false,
			'S_DL_NAV_HACKS_OHNP' 		=> ($this->config['dl_nav_link_hacks'] == 'OHNP') ? true : false,
			'S_DL_NAV_HACKS_OHNA' 		=> ($this->config['dl_nav_link_hacks'] == 'OHNA') ? true : false,
			'S_DL_NAV_HACKS_NHUPP'		=> ($this->config['dl_nav_link_hacks'] == 'NHUPP') ? true : false,
			'S_DL_NAV_HACKS_NHUP' 		=> ($this->config['dl_nav_link_hacks'] == 'NHUP') ? true : false,
			'S_DL_NAV_HACKS_NHPLB'		=> ($this->config['dl_nav_link_hacks'] == 'NHPLB') ? true : false,
			'S_DL_NAV_HACKS_NHPLA'		=> ($this->config['dl_nav_link_hacks'] == 'NHPLA') ? true : false,
			'S_DL_NAV_HACKS_NHUA'		=> ($this->config['dl_nav_link_hacks'] == 'NHUA') ? true : false,
			'S_DL_NAV_HACKS_NHUPA'		=> ($this->config['dl_nav_link_hacks'] == 'NHUPA') ? true : false,
			'S_DL_NAV_HACKS_OFTzB'		=> ($this->config['dl_nav_link_hacks'] == 'OFTzB') ? true : false,
			'S_DL_NAV_HACKS_OFTzA'		=> ($this->config['dl_nav_link_hacks'] == 'OFTzA') ? true : false,
			'S_DL_NAV_HACKS_OFTlB'		=> ($this->config['dl_nav_link_hacks'] == 'OFTlB') ? true : false,
			'S_DL_NAV_HACKS_OFTlA'		=> ($this->config['dl_nav_link_hacks'] == 'OFTlA') ? true : false,

			'S_DL_NAV_TRACKER_NHQLB'	=> ($this->config['dl_nav_link_tracker'] == 'NHQLB') ? true : false,
			'S_DL_NAV_TRACKER_NHQLA'	=> ($this->config['dl_nav_link_tracker'] == 'NHQLA') ? true : false,
			'S_DL_NAV_TRACKER_OHNP' 	=> ($this->config['dl_nav_link_tracker'] == 'OHNP') ? true : false,
			'S_DL_NAV_TRACKER_OHNA' 	=> ($this->config['dl_nav_link_tracker'] == 'OHNA') ? true : false,
			'S_DL_NAV_TRACKER_NHUPP'	=> ($this->config['dl_nav_link_tracker'] == 'NHUPP') ? true : false,
			'S_DL_NAV_TRACKER_NHUP' 	=> ($this->config['dl_nav_link_tracker'] == 'NHUP') ? true : false,
			'S_DL_NAV_TRACKER_NHPLB'	=> ($this->config['dl_nav_link_tracker'] == 'NHPLB') ? true : false,
			'S_DL_NAV_TRACKER_NHPLA'	=> ($this->config['dl_nav_link_tracker'] == 'NHPLA') ? true : false,
			'S_DL_NAV_TRACKER_NHUA'		=> ($this->config['dl_nav_link_tracker'] == 'NHUA') ? true : false,
			'S_DL_NAV_TRACKER_NHUPA'	=> ($this->config['dl_nav_link_tracker'] == 'NHUPA') ? true : false,
			'S_DL_NAV_TRACKER_OFTzB'	=> ($this->config['dl_nav_link_tracker'] == 'OFTzB') ? true : false,
			'S_DL_NAV_TRACKER_OFTzA'	=> ($this->config['dl_nav_link_tracker'] == 'OFTzA') ? true : false,
			'S_DL_NAV_TRACKER_OFTlB'	=> ($this->config['dl_nav_link_tracker'] == 'OFTlB') ? true : false,
			'S_DL_NAV_TRACKER_OFTlA'	=> ($this->config['dl_nav_link_tracker'] == 'OFTlA') ? true : false,
		));
	}
}
