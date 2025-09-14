<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core\helpers;

class footer implements footer_interface
{
	/* phpbb objects */
	protected $language;
	protected $template;
	protected $helper;
	protected $config;
	protected $user;
	protected $request;

	/* extension owned objects */
	protected $nav_mode;
	protected $cat_id;
	protected $df_id;
	protected $index;

	protected $dlext_auth;
	protected $dlext_cache;
	protected $dlext_counter;
	protected $dlext_extra;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_physical;
	protected $dlext_constants;
	protected $dlext_navigation;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\user							$user
	 * @param \phpbb\request\request 				$request
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\cache				$dlext_cache
	 * @param \oxpus\dlext\core\counter				$dlext_counter
	 * @param \oxpus\dlext\core\extra				$dlext_extra
	 * @param \oxpus\dlext\core\files				$dlext_files
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\physical			$dlext_physical
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param \oxpus\dlext\core\helpers\navigation	$dlext_navigation
	 */
	public function __construct(
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\controller\helper $helper,
		\phpbb\config\config $config,
		\phpbb\user $user,
		\phpbb\request\request $request,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\cache $dlext_cache,
		\oxpus\dlext\core\counter $dlext_counter,
		\oxpus\dlext\core\extra $dlext_extra,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\physical $dlext_physical,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		\oxpus\dlext\core\helpers\navigation $dlext_navigation
	)
	{
		$this->language			= $language;
		$this->template 		= $template;
		$this->helper 			= $helper;
		$this->config 			= $config;
		$this->user 			= $user;
		$this->request 			= $request;

		$this->dlext_auth		= $dlext_auth;
		$this->dlext_cache		= $dlext_cache;
		$this->dlext_counter	= $dlext_counter;
		$this->dlext_extra		= $dlext_extra;
		$this->dlext_files		= $dlext_files;
		$this->dlext_format		= $dlext_format;
		$this->dlext_main		= $dlext_main;
		$this->dlext_physical	= $dlext_physical;
		$this->dlext_constants	= $dlext_constants;
		$this->dlext_navigation	= $dlext_navigation;
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
			/*
			* check and create link if we must approve downloads
			*/
			$broken_ary = $this->dlext_counter->count_dl_broken();
			$total_broken = (isset($broken_ary['total'])) ? $broken_ary['total'] : 0;
			$broken_id = (isset($broken_ary['df_id'])) ? $broken_ary['df_id'] : 0;

			if ($total_broken)
			{
				$broken_string = ($total_broken == 1) ? $this->language->lang('DL_BROKEN_OVERVIEW_ONE') : $this->language->lang('DL_BROKEN_OVERVIEW', $total_broken);
				$broken_url = ($total_broken == 1) ? $this->helper->route('oxpus_dlext_details', ['df_id' => $broken_id]) : $this->helper->route('oxpus_dlext_mcp_broken');

				$this->template->assign_vars([
					'L_DL_BROKEN_DOWNLOADS' => $broken_string,
					'U_DL_BROKEN_DOWNLOADS' => $broken_url,
				]);
			}

			/*
			* check and create link if we must approve downloads
			*/
			$total_approve = $this->dlext_counter->count_dl_approve();

			if ($total_approve)
			{
				$approve_string = ($total_approve == 1) ? $this->language->lang('DL_APPROVE_OVERVIEW_ONE') : $this->language->lang('DL_APPROVE_OVERVIEW', $total_approve);

				$this->template->assign_vars([
					'L_DL_APPROVE_DOWNLOADS' => $approve_string,
					'U_DL_APPROVE_DOWNLOADS' => $this->helper->route('oxpus_dlext_mcp_approve'),
				]);
			}

			/*
			* check and create link if we must approve comments
			*/
			$total_comment_approve = $this->dlext_counter->count_comments_approve();

			if ($total_comment_approve)
			{
				$approve_comment_string = ($total_comment_approve == 1) ? $this->language->lang('DL_APPROVE_OVERVIEW_ONE_COMMENT') : $this->language->lang('DL_APPROVE_OVERVIEW_COMMENTS', $total_comment_approve);

				$this->template->assign_vars([
					'L_DL_APPROVE_COMMENTS' => $approve_comment_string,
					'U_DL_APPROVE_COMMENTS' => $this->helper->route('oxpus_dlext_mcp_capprove'),
				]);
			}

			/*
			* check and create link if user have permissions to view statistics
			*/
			$stats_view = $this->dlext_auth->stats_perm();
			if ($stats_view)
			{
				$this->template->assign_var('S_DL_STATS_VIEW_ON', $this->dlext_constants::DL_TRUE);
			}

			$this->template->assign_var('S_DL_FOOTER_NAV_ON', $this->dlext_constants::DL_TRUE);

			/*
			* create overall mini statistics
			*/
			if ($this->config['dl_show_footer_stat'])
			{
				$total_size		= $this->dlext_physical->read_dl_sizes();
				$total_dl		= $this->dlext_main->get_sublevel_count();
				$total_extern	= $this->dlext_counter->count_external_files();

				$physical_limit	= $this->config['dl_physical_quota'];
				$total_size		= ($total_size > $physical_limit) ? $physical_limit : $total_size;

				$physical_limit	= $this->dlext_format->dl_size($physical_limit, 2);

				if ($total_dl && $total_size)
				{
					$total_size = $this->dlext_format->dl_size($total_size, 2);

					$this->template->assign_vars([
						'DL_TOTAL_STAT' => $this->language->lang('DL_TOTAL_STAT', $total_dl, $total_size, $physical_limit, $total_extern),
					]);

					$this->template->assign_var('S_DL_FOOTER_STATS', $this->dlext_constants::DL_TRUE);
				}
			}

			/*
			* create the overall dl mod jumpbox
			*/
			if ($this->config['dl_enable_jumpbox'])
			{
				$catlist	= [];
				$cat		= $this->request->variable('cat', 0);

				$this->dlext_extra->dl_dropdown(0, 0, $cat, 'auth_view', $this->dlext_constants::DL_FALSE, $catlist);

				foreach ($catlist as $cat_id => $data)
				{
					$this->template->assign_block_vars('dl_jumpbox', [
						'DL_CAT_NAME'	=> $data['cat_name'],
						'DL_CAT_SUB'	=> ($data['sub']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
						'DL_CAT_LEVEL'	=> $data['seperator'],
						'U_DL_CAT_LINK'	=> $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]),
					]);
				}

				$s_dl_jumpbox = $this->dlext_constants::DL_TRUE;
			}
			else
			{
				$s_dl_jumpbox = $this->dlext_constants::DL_FALSE;
			}

			if ($this->config['dl_user_traffic_once'])
			{
				$l_can_download_again = $this->language->lang('DL_CAN_DOWNLOAD_TRAFFIC_FOOTER');
			}
			else
			{
				$l_can_download_again = '';
			}

			$ext_stats_enable = $this->dlext_constants::DL_FALSE;

			switch ($this->config['dl_mini_stats_ext'])
			{
				case $this->dlext_constants::DL_FOOTER_STATS_GUESTS_USER:
					$ext_stats_enable = $this->dlext_constants::DL_TRUE;
					break;
				case $this->dlext_constants::DL_FOOTER_STATS_ALL:
					if ($this->user->data['is_registered'])
					{
						$ext_stats_enable = $this->dlext_constants::DL_TRUE;
					}
					break;
				case $this->dlext_constants::DL_FOOTER_STATS_ADMIN_ONLY:
					if ($this->dlext_auth->user_admin())
					{
						$ext_stats_enable = $this->dlext_constants::DL_TRUE;
					}
					break;
				case $this->dlext_constants::DL_FOOTER_STATS_FOUNDER_ONLY:
					if ($this->user->data['user_type'] == USER_FOUNDER)
					{
						$ext_stats_enable = $this->dlext_constants::DL_TRUE;
					}
					break;
			}

			if ($ext_stats_enable)
			{
				$overall_traffic = $this->dlext_format->dl_size($this->config['dl_overall_traffic']);
				$overall_guest_traffic = $this->dlext_format->dl_size($this->config['dl_overall_guest_traffic']);

				$dl_file_p = $this->dlext_cache->obtain_dl_file_p();
				$total_cur_clicks = 0;

				if (!empty($dl_file_p))
				{
					foreach (array_keys($dl_file_p) as $key)
					{
						$total_cur_clicks += $dl_file_p[$key]['k'];
					}
				}

				$this->template->assign_vars([
					'DL_EXT_STATS_OVERALL_TRAFFIC'			=> $overall_traffic,
					'DL_EXT_STATS_OVERALL_GUESTS_TRAFFIC'	=> $overall_guest_traffic,
					'DL_EXT_STATS_MONTH_CLICKS'				=> $total_cur_clicks,
					'S_DL_FOOTER_STATS'						=> $this->dlext_constants::DL_TRUE,
				]);
			}

			/*
			* Check for latest downloads and prepare link
			*/
			if ($this->config['dl_latest_type'])
			{
				if ($this->config['dl_latest_type'] == $this->dlext_constants::DL_LATEST_TYPE_DEFAULT)
				{
					$check_add_time		= time() - ($this->config['dl_new_time'] * $this->dlext_constants::DL_ONE_DAY);
					$check_edit_time	= time() - ($this->config['dl_edit_time'] * $this->dlext_constants::DL_ONE_DAY);

					$sql_latest_where = ['add_time' => ['AND', '>=', (int) $check_add_time], 'change_time' => ['OR', '>=', (int) $check_edit_time]];
					$dl_latest_files = $this->dlext_files->all_files(0, [], $sql_latest_where, 0, 0, ['id'], 1);

					if (!empty($dl_latest_files))
					{
						$this->template->assign_var('U_DL_LATEST_DOWNLOADS', $this->helper->route('oxpus_dlext_latest'));
					}
				}
				else if ($this->config['dl_latest_type'] == $this->dlext_constants::DL_LATEST_TYPE_NEW)
				{
					$check_add_time		= time() - ($this->config['dl_new_time'] * $this->dlext_constants::DL_ONE_DAY);

					$sql_latest_where = ['add_time' => ['AND', '>=', (int) $check_add_time]];
					$dl_latest_files = $this->dlext_files->all_files(0, [], $sql_latest_where, 0, 0, ['id'], 1);

					if (!empty($dl_latest_files))
					{
						$this->template->assign_var('U_DL_LATEST_DOWNLOADS', $this->helper->route('oxpus_dlext_latest'));
					}
				}
				else
				{
					$this->template->assign_var('U_DL_LATEST_DOWNLOADS', $this->helper->route('oxpus_dlext_latest'));
				}
			}

			$translation = $this->language->lang('DL_TRANSLATION');

			$this->template->assign_vars([
				'L_DL_GREEN_EXPLAIN'		=> ($this->config['dl_traffic_off']) ? $this->language->lang('DL_GREEN_EXPLAIN_T_OFF') : $this->language->lang('DL_GREEN_EXPLAIN'),
				'L_DL_WHITE_EXPLAIN'		=> ($this->config['dl_traffic_off']) ? $this->language->lang('DL_WHITE_EXPLAIN_T_OFF') : $this->language->lang('DL_WHITE_EXPLAIN'),
				'L_DL_GREY_EXPLAIN'			=> ($this->config['dl_traffic_off']) ? $this->language->lang('DL_GREY_EXPLAIN_T_OFF') : $this->language->lang('DL_GREY_EXPLAIN'),
				'L_DL_RED_EXPLAIN'			=> sprintf((($this->config['dl_traffic_off']) ? $this->language->lang('DL_RED_EXPLAIN_T_OFF') : $this->language->lang('DL_RED_EXPLAIN')), $this->config['dl_posts']),
				'L_CAN_DOWNLOAD_AGAIN'		=> $l_can_download_again,

				'DL_MOD_RELEASE'			=> $this->language->lang('DL_MOD_VERSION_PUBLIC'),
				'DL_LIGHTBOX_RESIZE_WIDTH'	=> 0,

				'S_DL_JUMPBOX'				=> $s_dl_jumpbox,
				'S_DL_TRANSLATION'			=> ($translation) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,

				'U_DL_STATS'				=> $this->helper->route('oxpus_dlext_stats'),
				'U_DL_TODOLIST'				=> $this->helper->route('oxpus_dlext_todo'),
				'U_DL_OVERALL_VIEW'			=> ($this->config['dl_overview_link_onoff']) ? $this->helper->route('oxpus_dlext_overall') : '',
			]);

			if ($this->config['dl_show_footer_stat'] && !$this->config['dl_traffic_off'])
			{
				$remain_traffic = $this->config['dl_overall_traffic'] - (int) $this->config['dl_remain_traffic'];

				if ($this->user->data['is_registered'] && $this->dlext_constants->get_value('overall_traffics'))
				{
					if ($remain_traffic <= 0)
					{
						$overall_traffic = $this->dlext_format->dl_size($this->config['dl_overall_traffic']);

						$text_no_more_remain_traffic = $this->language->lang('DL_NO_MORE_REMAIN_TRAFFIC', $overall_traffic);

						if ($this->dlext_constants->get_value('founder_traffics'))
						{
							$text_no_more_remain_traffic = $this->language->lang('DL_TRAFFICS_FOUNDER_INFO', $text_no_more_remain_traffic);
						}

						$this->template->assign_var('DL_NO_OVERALL_TRAFFIC', $text_no_more_remain_traffic);
					}
					else
					{
						$remain_text_out = $this->language->lang('DL_REMAIN_OVERALL_TRAFFIC_FOOTER', $this->dlext_format->dl_size($remain_traffic, 2));

						if ($this->dlext_constants->get_value('founder_traffics'))
						{
							$remain_text_out = $this->language->lang('DL_TRAFFICS_FOUNDER_INFO', $remain_text_out);
						}

						$this->template->assign_var('DL_REMAIN_TRAFFIC', $remain_text_out);
					}
				}

				if ($this->user->data['is_registered'] && $this->dlext_constants->get_value('users_traffics'))
				{
					$user_traffic			= ($this->user->data['user_traffic'] > $remain_traffic && $this->dlext_constants->get_value('overall_traffics')) ? $remain_traffic : $this->user->data['user_traffic'];
					$user_traffic_out		= $this->dlext_format->dl_size($user_traffic, 2);
					$user_account_traffic	= $this->language->lang('DL_ACCOUNT', $user_traffic_out);

					if ($this->dlext_constants->get_value('founder_traffics'))
					{
						$user_account_traffic = $this->language->lang('DL_TRAFFICS_FOUNDER_INFO', $user_account_traffic);
					}

					$this->template->assign_var('DL_ACCOUNT_TRAFFIC', ($this->user->data['user_id'] != ANONYMOUS) ? $user_account_traffic : '');
				}

				if ($this->user->data['user_type'] == USER_FOUNDER || $this->dlext_constants->get_value('guests_traffics'))
				{
					if ($this->config['dl_overall_guest_traffic'] - (int) $this->config['dl_remain_guest_traffic'] <= 0)
					{
						$overall_guest_traffic			= $this->dlext_format->dl_size($this->config['dl_overall_guest_traffic']);
						$text_no_overall_guest_traffic	= $this->language->lang('DL_NO_MORE_REMAIN_GUEST_TRAFFIC', $overall_guest_traffic);

						if ($this->user->data['user_type'] == USER_FOUNDER)
						{
							$text_no_overall_guest_traffic = $this->language->lang('DL_TRAFFICS_FOUNDER_INFO', $text_no_overall_guest_traffic);
						}

						$this->template->assign_var('DL_NO_OVERALL_GUEST_TRAFFIC', $text_no_overall_guest_traffic);
					}
					else
					{
						$remain_guest_traffic	= $this->config['dl_overall_guest_traffic'] - $this->config['dl_remain_guest_traffic'];
						$remain_guest_text_out	= $this->language->lang('DL_REMAIN_OVERALL_GUEST_TRAFFIC_F', $this->dlext_format->dl_size($remain_guest_traffic, 2));

						if ($this->user->data['user_type'] == USER_FOUNDER)
						{
							$remain_guest_text_out = $this->language->lang('DL_TRAFFICS_FOUNDER_INFO', $remain_guest_text_out);
						}

						$this->template->assign_var('DL_REMAIN_GUEST_TRAFFIC', $remain_guest_text_out);
					}
				}
			}

			if ($this->config['dl_show_footer_legend'])
			{
				$this->template->assign_var('S_DL_FOOTER_LEGEND', $this->dlext_constants::DL_TRUE);
			}

			if ($this->config['dl_todo_link_onoff'] && $this->config['dl_todo_onoff'])
			{
				$todo_access_ids = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_MOD);

				if (count($todo_access_ids) > 0 && $this->user->data['is_registered'])
				{
					$this->template->assign_var('S_DL_TODO_LINK', $this->dlext_constants::DL_TRUE);
				}
			}

			if ($this->config['dl_rss_enable'])
			{
				$this->template->assign_var('U_DL_RSS_FEED', $this->helper->route('oxpus_dlext_feed'));
			}
		}

		// Overwrite the mcp link with the extension module
		$this->_dl_mcp_link();

		// Display the navigation
		if ($this->nav_mode)
		{
			$this->dlext_navigation->set_parameter($this->nav_mode, $this->cat_id, $this->df_id);
			$this->dlext_navigation->handle();
		}
	}

	private function _dl_mcp_link()
	{
		$access_cat = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_MOD);

		if (empty($access_cat))
		{
			return;
		}

		$cat		= $this->request->variable('cat', 0);
		$cat_id		= $this->request->variable('cat_id', 0);

		$mcp_cat	= ($cat_id) ? $cat_id : $cat;

		if ($mcp_cat && $this->dlext_auth->user_auth($mcp_cat, 'auth_mod'))
		{
			$u_dl_mcp = $this->helper->route('oxpus_dlext_mcp_manage', ['view' => 'toolbox', 'cat_id' => $mcp_cat]);
		}
		else
		{
			$u_dl_mcp = $this->helper->route('oxpus_dlext_mcp_manage');
		}

		$this->template->assign_vars([
			'U_DL_MCP_MANAGE'		=> $this->helper->route('oxpus_dlext_mcp_manage'),
			'U_DL_MCP_EDIT'			=> $this->helper->route('oxpus_dlext_mcp_edit'),
			'U_DL_MCP_APPROVE'		=> $this->helper->route('oxpus_dlext_mcp_approve'),
			'U_DL_MCP_BROKEN'		=> $this->helper->route('oxpus_dlext_mcp_broken'),
			'U_DL_MCP_CAPPROVE'		=> $this->helper->route('oxpus_dlext_mcp_capprove'),

			'U_MCP'					=> $u_dl_mcp,
		]);
	}
}
