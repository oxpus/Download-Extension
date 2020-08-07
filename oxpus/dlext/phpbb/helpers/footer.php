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

class footer implements footer_interface
{
	/* @var string phpBB root path */
	protected $root_path;

	/* @var string phpEx */
	protected $php_ext;

	/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var Container */
	protected $phpbb_container;

	/* var extension owned variables */
	protected $nav_mode;
	protected $cat_id;
	protected $df_id;
	protected $index;
	protected $ext_path;
	protected $ext_path_web;
	protected $ext_path_ajax;
	protected $request;

	protected $dlext_auth;
	protected $dlext_extra;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_init;
	protected $dlext_main;
	protected $dlext_physical;

	/**
	* Constructor
	*
	* @param string									$root_path
	* @param string									$php_ext
	* @param \phpbb\language\language				$language
	* @param \phpbb\template\template				$template
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\db\driver\driver_interfacer		$db
	*/
	public function __construct(
		$root_path,
		$php_ext,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\controller\helper $helper,
		\phpbb\config\config $config,
		\phpbb\auth\auth $auth,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		$dlext_auth,
		$dlext_counter,
		$dlext_extra,
		$dlext_files,
		$dlext_format,
		$dlext_init,
		$dlext_main,
		$dlext_physical
	)
	{
		$this->language			= $language;
		$this->template 		= $template;
		$this->helper 			= $helper;
		$this->config 			= $config;
		$this->auth				= $auth;
		$this->user 			= $user;
		$this->root_path 		= $root_path;
		$this->db 				= $db;
		$this->phpbb_container 	= $phpbb_container;

		$this->request 			= $this->phpbb_container->get('request');

		$this->ext_path			= $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web		= $phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax	= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_auth		= $dlext_auth;
		$this->dlext_counter	= $dlext_counter;
		$this->dlext_extra		= $dlext_extra;
		$this->dlext_files		= $dlext_files;
		$this->dlext_format		= $dlext_format;
		$this->dlext_init		= $dlext_init;
		$this->dlext_main		= $dlext_main;
		$this->dlext_physical	= $dlext_physical;

		$this->php_ext			= substr($this->dlext_init->php_ext(),1);
	}

	public function set_parameter($nav_view = '', $cat_id = 0, $df_id = 0, $index = [])
	{
		$this->nav_mode = $nav_view;
		$this->cat_id	= $cat_id;
		$this->df_id	= $df_id;
		$this->index	= $index;
	}

	public function handle()
	{
		if (!empty($this->index) || $this->cat_id)
		{
			include($this->ext_path . 'phpbb/includes/base_init' . $this->dlext_init->php_ext());
			include($this->ext_path . 'phpbb/includes/sort_init' . $this->dlext_init->php_ext());

			/*
			* check and create link if we must approve downloads
			*/
			$broken_ary = $this->dlext_counter->count_dl_broken();
			$total_broken = $broken_ary['total'];
			$broken_id = $broken_ary['df_id'];

			if ($total_broken)
			{
				$broken_string = ($total_broken == 1) ? $this->language->lang('DL_BROKEN_OVERVIEW_ONE') : $this->language->lang('DL_BROKEN_OVERVIEW', $total_broken);
				$broken_url = ($total_broken == 1) ? $this->helper->route('oxpus_dlext_details', ['df_id' => $broken_id]) : $this->helper->route('oxpus_dlext_mcp_broken');
				$this->template->assign_block_vars('broken', [
					'L_BROKEN_DOWNLOADS' => $broken_string,
					'U_BROKEN_DOWNLOADS' => $broken_url,
				]);
			}

			/*
			* check and create link if we must approve downloads
			*/
			$total_approve = $this->dlext_counter->count_dl_approve();

			if ($total_approve)
			{
				$approve_string = ($total_approve == 1) ? $this->language->lang('DL_APPROVE_OVERVIEW_ONE') : $this->language->lang('DL_APPROVE_OVERVIEW', $total_approve);
				$this->template->assign_block_vars('approve', [
					'L_APPROVE_DOWNLOADS' => $approve_string,
					'U_APPROVE_DOWNLOADS' => $this->helper->route('oxpus_dlext_mcp_approve'),
				]);
			}

			/*
			* check and create link if we must approve comments
			*/
			$total_comment_approve = $this->dlext_counter->count_comments_approve();

			if ($total_comment_approve)
			{
				$approve_comment_string = ($total_comment_approve == 1) ? $this->language->lang('DL_APPROVE_OVERVIEW_ONE_COMMENT') : $this->language->lang('DL_APPROVE_OVERVIEW_COMMENTS', $total_comment_approve);
				$this->template->assign_block_vars('approve_comments', [
					'L_APPROVE_COMMENTS' => $approve_comment_string,
					'U_APPROVE_COMMENTS' => $this->helper->route('oxpus_dlext_mcp_capprove'),
				]);
			}

			/*
			* check and create link if user have permissions to view statistics
			*/
			$stats_view = $this->dlext_auth->stats_perm();
			if ($stats_view)
			{
				$this->template->assign_var('S_STATS_VIEW_ON', true);
			}

			$this->template->assign_var('S_FOOTER_NAV_ON', true);

			/*
			* create overall mini statistics
			*/
			if ($this->config['dl_show_footer_stat'])
			{
				$total_size		= $this->dlext_physical->read_dl_sizes();
				$total_dl		= $this->dlext_main->get_sublevel_count();
				$total_extern	= count($this->dlext_files->all_files(0, '', 'ASC', "AND extern = 1", 0, true, 'id'));

				$physical_limit	= $this->config['dl_physical_quota'];
				$total_size		= ($total_size > $physical_limit) ? $physical_limit : $total_size;

				$physical_limit	= $this->dlext_format->dl_size($physical_limit, 2);

				if ($total_dl && $total_size)
				{
					$total_size = $this->dlext_format->dl_size($total_size, 2);

					$this->template->assign_block_vars('total_stat', [
						'TOTAL_STAT' => $this->language->lang('DL_TOTAL_STAT', $total_dl, $total_size, $physical_limit, $total_extern),
					]);

					$this->template->assign_vars(['S_FOOTER_STATS' => true]);
				}
			}

			/*
			* create the overall dl mod jumpbox
			*/
			if ($this->config['dl_enable_jumpbox'])
			{
				$catlist = [];
				$this->dlext_extra->dl_jumpbox(0, 0, $cat, 'auth_view', $catlist);

				foreach($catlist as $cat_id => $data)
				{
					$this->template->assign_block_vars('dl_jumpbox', [
						'DL_CAT_NAME'	=> $data['name'],
						'DL_CAT_SUB'	=> ($data['sub']) ? true : false,
						'U_CAT_LINK'	=> $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]),
					]);

					if ($data['sub'])
					{
						for ($i = 1; $i < $data['level']; ++$i)
						{
							$this->template->assign_block_vars('dl_jumpbox.level', []);
						}
					}
				}

				$s_dl_jumpbox = true;
			}
			else
			{
				$s_dl_jumpbox = false;
			}

			if ($this->config['dl_user_traffic_once'])
			{
				$l_can_download_again = $this->language->lang('DL_CAN_DOWNLOAD_TRAFFIC_FOOTER');
			}
			else
			{
				$l_can_download_again = '';
			}

			$ext_stats_enable = false;
			switch($this->config['dl_mini_stats_ext'])
			{
				case 1:
					$ext_stats_enable = true;
				break;
				case 2:
					if ($this->user->data['is_registered'])
					{
						$ext_stats_enable = true;
					}
				break;
				case 3:
					if ($this->auth->acl_get('a_') && $this->user->data['is_registered'])
					{
						$ext_stats_enable = true;
					}
				break;
				case 4:
					if ($this->user->data['user_type'] == USER_FOUNDER)
					{
						$ext_stats_enable = true;
					}
				break;
				default:
					$ext_stats_enable = false;
			}

			if ($ext_stats_enable)
			{
				$overall_traffic = $this->dlext_format->dl_size($this->config['dl_overall_traffic']);
				$overall_guest_traffic = $this->dlext_format->dl_size($this->config['dl_overall_guest_traffic']);

				$dl_file_p = $this->dlext_init->dl_file_p();
				$total_cur_clicks = 0;
				
				if (!empty($dl_file_p))
				{
					foreach ($dl_file_p as $dl_id => $row)
					{
						$total_cur_clicks += $row['klicks'];
					}
				}

				$this->template->assign_vars([
					'EXT_STATS_OVERALL_TRAFFIC'			=> $this->language->lang('DL_OVERALL_TRAFFIC') . ': ' . $overall_traffic,
					'EXT_STATS_OVERALL_GUESTS_TRAFFIC'	=> $this->language->lang('DL_OVERALL_GUEST_TRAFFIC') . ': ' . $overall_guest_traffic,
					'EXT_STATS_MONTH_CLICKS'			=> $this->language->lang('DL_KLICKS') . ': ' . $total_cur_clicks,
					'S_FOOTER_STATS'					=> true,
				]);
			}

			/*
			* Check for latest downloads and prepare link
			*/
			if ($this->config['dl_latest_type'])
			{
				if ($this->config['dl_latest_type'] == 1)
				{
					$check_add_time		= time() - ($this->config['dl_new_time'] * 86400);
					$check_edit_time	= time() - ($this->config['dl_edit_time'] * 86400);

					$sql_latest_where = 'AND (add_time >= ' . (int) $check_add_time . ' OR change_time >= ' . (int) $check_edit_time . ')';
				}
				else
				{
					$sql_latest_where = '';
				}

				$dl_latest_files = [];
				$dl_latest_files = $this->dlext_files->all_files(0, '', '', $sql_latest_where, 0, 0, 'id', 1);

				if (!empty($dl_latest_files))
				{
					$this->template->assign_var('U_LATEST_DOWNLOADS', $this->helper->route('oxpus_dlext_latest'));
				}
			}

			/*
			* load footer template and send default values
			*/
			$this->template->set_filenames(['dl_footer' => 'dl_footer.html']);

			$translation = $this->language->lang('DL_TRANSLATION');

			$this->template->assign_vars([
				'L_DL_GREEN_EXPLAIN'	=> ($this->config['dl_traffic_off']) ? $this->language->lang('DL_GREEN_EXPLAIN_T_OFF') : $this->language->lang('DL_GREEN_EXPLAIN'),
				'L_DL_WHITE_EXPLAIN'	=> ($this->config['dl_traffic_off']) ? $this->language->lang('DL_WHITE_EXPLAIN_T_OFF') : $this->language->lang('DL_WHITE_EXPLAIN'),
				'L_DL_GREY_EXPLAIN'		=> ($this->config['dl_traffic_off']) ? $this->language->lang('DL_GREY_EXPLAIN_T_OFF') : $this->language->lang('DL_GREY_EXPLAIN'),
				'L_DL_RED_EXPLAIN'		=> sprintf((($this->config['dl_traffic_off']) ? $this->language->lang('DL_RED_EXPLAIN_T_OFF') : $this->language->lang('DL_RED_EXPLAIN')), $this->config['dl_posts']),
				'L_CAN_DOWNLOAD_AGAIN'	=> $l_can_download_again,

				'DL_MOD_RELEASE'		=> $this->language->lang('DL_MOD_VERSION_PUBLIC'),
				'LIGHTBOX_RESIZE_WIDTH'	=> 0,

				'S_DL_JUMPBOX'			=> $s_dl_jumpbox,
				'S_DL_TRANSLATION'		=> ($translation) ? true : false,

				'U_DL_STATS'			=> $this->helper->route('oxpus_dlext_stats'),
				'U_DL_TODOLIST'			=> $this->helper->route('oxpus_dlext_todo'),
				'U_DL_OVERALL_VIEW'		=> ($this->config['dl_overview_link_onoff']) ? $this->helper->route('oxpus_dlext_overall') : '',
			]);

			$s_separate_stats = false;

			if ($this->config['dl_show_footer_stat'] && !$this->config['dl_traffic_off'])
			{
				$remain_traffic = $this->config['dl_overall_traffic'] - (int) $this->config['dl_remain_traffic'];

				if ($this->user->data['is_registered'] && DL_OVERALL_TRAFFICS == true)
				{
					if ($remain_traffic <= 0)
					{
						$overall_traffic = $this->dlext_format->dl_size($this->config['dl_overall_traffic']);

						$text_no_more_remain_traffic = $this->language->lang('DL_NO_MORE_REMAIN_TRAFFIC', $overall_traffic);

						if ($this->user->data['user_type'] == USER_FOUNDER && FOUNDER_TRAFFICS_OFF)
						{
							$text_no_more_remain_traffic = '<strong>' . $this->language->lang('DL_TRAFFICS_FOUNDER_INFO') . ':</strong> ' . $text_no_more_remain_traffic;
						}

						$this->template->assign_block_vars('no_remain_traffic', [
							'NO_OVERALL_TRAFFIC' => $text_no_more_remain_traffic,
						]);

						$s_separate_stats = true;
					}
					else
					{
						$remain_text_out = $this->language->lang('DL_REMAIN_OVERALL_TRAFFIC') . '<b>' . $this->dlext_format->dl_size($remain_traffic, 2) . '</b>';

						if ($this->user->data['user_type'] == USER_FOUNDER && FOUNDER_TRAFFICS_OFF)
						{
							$remain_text_out = '<strong>' . $this->language->lang('DL_TRAFFICS_FOUNDER_INFO') . ':</strong> ' . $remain_text_out;
						}

						$this->template->assign_block_vars('remain_traffic', [
							'REMAIN_TRAFFIC' => $remain_text_out,
						]);

						$s_separate_stats = true;
					}
				}

				if ($this->user->data['is_registered'] && DL_USERS_TRAFFICS == true)
				{
					$user_traffic			= ($this->user->data['user_traffic'] > $remain_traffic && DL_OVERALL_TRAFFICS == true) ? $remain_traffic : $this->user->data['user_traffic'];
					$user_traffic_out		= $this->dlext_format->dl_size($user_traffic, 2);
					$user_account_traffic	= $this->language->lang('DL_ACCOUNT', $user_traffic_out);

					if ($this->user->data['user_type'] == USER_FOUNDER && FOUNDER_TRAFFICS_OFF)
					{
						$user_account_traffic = '<strong>' . $this->language->lang('DL_TRAFFICS_FOUNDER_INFO') . ':</strong> ' . $user_account_traffic;
					}

					$this->template->assign_block_vars('userdata', [
						'ACCOUNT_TRAFFIC' => ($this->user->data['user_id'] <> ANONYMOUS) ? $user_account_traffic : '',
					]);

					$s_separate_stats = true;
				}

				if ((!$this->user->data['is_registered'] || $this->user->data['user_type'] == USER_FOUNDER) && DL_GUESTS_TRAFFICS == true)
				{
					if ($this->config['dl_overall_guest_traffic'] - (int) $this->config['dl_remain_guest_traffic'] <= 0)
					{
						$overall_guest_traffic			= $this->dlext_format->dl_size($this->config['dl_overall_guest_traffic']);
						$text_no_overall_guest_traffic	= $this->language->lang('DL_NO_MORE_REMAIN_GUEST_TRAFFIC', $overall_guest_traffic);

						if ($this->user->data['user_type'] == USER_FOUNDER)
						{
							$text_no_overall_guest_traffic = '<strong>' . $this->language->lang('DL_TRAFFICS_FOUNDER_INFO') . ':</strong> ' . $text_no_overall_guest_traffic;
						}

						$this->template->assign_block_vars('no_remain_guest_traffic', [
							'NO_OVERALL_GUEST_TRAFFIC' => $text_no_overall_guest_traffic,
						]);

						$s_separate_stats = true;
					}
					else
					{
						$remain_guest_traffic	= $this->config['dl_overall_guest_traffic'] - $this->config['dl_remain_guest_traffic'];
						$remain_guest_text_out	= $this->language->lang('DL_REMAIN_OVERALL_GUEST_TRAFFIC') . '<b>' . $this->dlext_format->dl_size($remain_guest_traffic, 2) . '</b>';

						if ($this->user->data['user_type'] == USER_FOUNDER)
						{
							$remain_guest_text_out = '<strong>' . $this->language->lang('DL_TRAFFICS_FOUNDER_INFO') . ':</strong> ' . $remain_guest_text_out;
						}

						$this->template->assign_block_vars('remain_guest_traffic', [
							'REMAIN_GUEST_TRAFFIC' => $remain_guest_text_out,
						]);

						$s_separate_stats = true;
					}
				}
			}
			else
			{
				$this->template->assign_var('S_HIDE_FOOTER_DATA', true);
			}

			$this->template->assign_var('S_SERARATE_STATS', $s_separate_stats);

			if ($this->config['dl_traffic_off'])
			{
				$this->template->assign_var('S_DL_TRAFFIC_OFF', true);
			}

			if ($this->config['dl_show_footer_legend'])
			{
				$this->template->assign_var('S_FOOTER_LEGEND', true);
			}

			if ($this->config['dl_todo_link_onoff'] && $this->config['dl_todo_onoff'])
			{
				$todo_access_ids = $this->dlext_main->full_index(0, 0, 0, 2);
		
				if (!empty($todo_access_ids) && $this->user->data['is_registered'])
				{
					$this->template->assign_var('S_TODO_LINK', true);
				}
			}

			if ($this->config['dl_rss_enable'])
			{
				$this->template->assign_var('U_DL_RSS_FEED', $this->helper->route('oxpus_dlext_feed'));
			}

			/*
			* display the page and return after this
			*/
			$this->template->assign_display('dl_footer');
		}

		// Display the navigation
		if ($this->nav_mode)
		{
			$dlext_navigation = $this->phpbb_container->get('oxpus.dlext.navigation');
			$dlext_navigation->set_parameter($this->nav_mode, $this->cat_id, $this->df_id);
			$dlext_navigation->handle();
		}

		page_footer();
	}
}
