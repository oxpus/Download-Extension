<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* connect to phpBB
*/
if ( !defined('IN_PHPBB') )
{
	exit;
}

if (sizeof($index) || $cat)
{
	/*
	* check and create link if we must approve downloads
	*/
	$total_approve = \oxpus\dlext\phpbb\classes\ dl_counter::count_dl_approve();
	if ($total_approve)
	{
		$approve_string = ($total_approve == 1) ? $this->language->lang('DL_APPROVE_OVERVIEW_ONE') : $this->language->lang('DL_APPROVE_OVERVIEW');
		$this->template->assign_block_vars('approve', array(
			'L_APPROVE_DOWNLOADS' => sprintf($approve_string, $total_approve),
			'U_APPROVE_DOWNLOADS' => $this->helper->route('oxpus_dlext_controller', array('view' => 'modcp', 'action' => 'approve')),
		));
	}

	/*
	* check and create link if we must approve comments
	*/
	$total_comment_approve = \oxpus\dlext\phpbb\classes\ dl_counter::count_comments_approve();
	if ($total_comment_approve)
	{
		$approve_comment_string = ($total_comment_approve == 1) ? $this->language->lang('DL_APPROVE_OVERVIEW_ONE_COMMENT') : $this->language->lang('DL_APPROVE_OVERVIEW_COMMENTS');
		$this->template->assign_block_vars('approve_comments', array(
			'L_APPROVE_COMMENTS' => sprintf($approve_comment_string, $total_comment_approve),
			'U_APPROVE_COMMENTS' => $this->helper->route('oxpus_dlext_controller', array('view' => 'modcp', 'action' => 'capprove')),
		));
	}

	/*
	* check and create link if user have permissions to view statistics
	*/
	$stats_view = \oxpus\dlext\phpbb\classes\ dl_auth::stats_perm();
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
		$total_size		= \oxpus\dlext\phpbb\classes\ dl_physical::read_dl_sizes();
		$total_dl		= \oxpus\dlext\phpbb\classes\ dl_main::get_sublevel_count();
		$total_extern	= sizeof(\oxpus\dlext\phpbb\classes\ dl_files::all_files(0, '', 'ASC', "AND extern = 1", 0, true, 'id'));

		$physical_limit	= $this->config['dl_physical_quota'];
		$total_size		= ($total_size > $physical_limit) ? $physical_limit : $total_size;

		$physical_limit	= \oxpus\dlext\phpbb\classes\ dl_format::dl_size($physical_limit, 2);

		if ($total_dl && $total_size)
		{
			$total_size = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($total_size, 2);

			$this->template->assign_block_vars('total_stat', array(
				'TOTAL_STAT' => $this->language->lang('DL_TOTAL_STAT', $total_dl, $total_size, $physical_limit, $total_extern))
			);
		}
	}

	/*
	* create the overall dl mod jumpbox
	*/
	if ($this->config['dl_enable_jumpbox'])
	{
		$dl_jumpbox = '<form method="post" id="dl_jumpbox" action="' . $this->helper->route('oxpus_dlext_controller', array('sort_by' => $sort_by, 'order' => $order)) . '" onsubmit="if(this.options[this.selectedIndex].value == -1){ return false; }">';
		$dl_jumpbox .= "\n<fieldset>" . $this->language->lang('DL_GOTO') . $this->language->lang('COLON') . ' <select name="cat" onchange="if(this.options[this.selectedIndex].value != -1){ forms[\'dl_jumpbox\'].submit() }">';
		$dl_jumpbox .= '<option value="-1">'.$this->language->lang('DL_CAT_NAME').'</option>';
		$dl_jumpbox .= '<option value="-1">----------</option>';
		$dl_jumpbox .= \oxpus\dlext\phpbb\classes\ dl_extra::dl_dropdown(0, 0, $cat, 'auth_view');
		$dl_jumpbox .= '</select>&nbsp;<input type="submit" value="'.$this->language->lang('GO').'" class="button2" /></fieldset></form>';
	}
	else
	{
		$dl_jumpbox = '';
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
		$overall_traffic = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($this->config['dl_overall_traffic']);
		$overall_guest_traffic = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($this->config['dl_overall_guest_traffic']);

		global $dl_file_p;
		$total_cur_clicks = 0;

		foreach ($dl_file_p as $dl_id => $row)
		{
			$total_cur_clicks += $row['klicks'];
		}

		$this->template->assign_vars(array(
			'EXT_STATS_OVERALL_TRAFFIC'			=> $this->language->lang('DL_OVERALL_TRAFFIC') . ': ' . $overall_traffic,
			'EXT_STATS_OVERALL_GUESTS_TRAFFIC'	=> $this->language->lang('DL_OVERALL_GUEST_TRAFFIC') . ': ' . $overall_guest_traffic,
			'EXT_STATS_MONTH_CLICKS'			=> $this->language->lang('DL_KLICKS') . ': ' . $total_cur_clicks,
		));
	}

	/*
	* Check for latest downloads and prepare link
	*/
	$check_add_time		= time() - ($this->config['dl_new_time'] * 86400);
	$check_edit_time	= time() - ($this->config['dl_edit_time'] * 86400);

	$sql_latest_where = 'AND (add_time >= ' . (int) $check_add_time . ' OR change_time >= ' . (int) $check_edit_time . ')';

	$dl_latest_files = array();
	$dl_latest_files = \oxpus\dlext\phpbb\classes\ dl_files::all_files(0, '', '', $sql_latest_where, 0, 0, 'id');

	if (sizeof($dl_latest_files))
	{
		$this->template->assign_var('U_LATEST_DOWNLOADS', $this->helper->route('oxpus_dlext_controller', array('view' => 'latest')));
	}

	/*
	* load footer template and send default values
	*/
	$this->template->set_filenames(array(
		'dl_footer' => 'dl_footer.html')
	);

	$translation = $this->language->lang('DL_TRANSLATION');

	$this->template->assign_vars(array(
		'L_DL_GREEN_EXPLAIN'	=> ($this->config['dl_traffic_off']) ? $this->language->lang('DL_GREEN_EXPLAIN_T_OFF') : $this->language->lang('DL_GREEN_EXPLAIN'),
		'L_DL_WHITE_EXPLAIN'	=> ($this->config['dl_traffic_off']) ? $this->language->lang('DL_WHITE_EXPLAIN_T_OFF') : $this->language->lang('DL_WHITE_EXPLAIN'),
		'L_DL_GREY_EXPLAIN'		=> ($this->config['dl_traffic_off']) ? $this->language->lang('DL_GREY_EXPLAIN_T_OFF') : $this->language->lang('DL_GREY_EXPLAIN'),
		'L_DL_RED_EXPLAIN'		=> sprintf((($this->config['dl_traffic_off']) ? $this->language->lang('DL_RED_EXPLAIN_T_OFF') : $this->language->lang('DL_RED_EXPLAIN')), $this->config['dl_posts']),
		'L_CAN_DOWNLOAD_AGAIN'	=> $l_can_download_again,

		'DL_MOD_RELEASE'		=> $this->language->lang('DL_MOD_VERSION_PUBLIC'),
		'LIGHTBOX_RESIZE_WIDTH'	=> 0,

		'S_DL_JUMPBOX'			=> $dl_jumpbox,
		'S_DL_TRANSLATION'		=> ($translation) ? true : false,

		'U_DL_STATS'			=> $this->helper->route('oxpus_dlext_controller', array('view' => 'stat')),
		'U_DL_CONFIG'			=> $this->helper->route('oxpus_dlext_controller', array('view' => 'user_config')),
		'U_DL_TODOLIST'			=> $this->helper->route('oxpus_dlext_controller', array('view' => 'todo')),
		'U_DL_OVERALL_VIEW'		=> ($this->config['dl_overview_link_onoff']) ? $this->helper->route('oxpus_dlext_controller', array('view' => 'overall')) : '',
	));

	$s_separate_stats = false;

	if ($this->config['dl_show_footer_stat'] && !$this->config['dl_traffic_off'])
	{
		$remain_traffic = $this->config['dl_overall_traffic'] - $this->config['dl_remain_traffic'];

		if ($this->user->data['is_registered'] && DL_OVERALL_TRAFFICS == true)
		{
			if ($remain_traffic <= 0)
			{
				$overall_traffic = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($this->config['dl_overall_traffic']);

				$text_no_more_remain_traffic = $this->language->lang('DL_NO_MORE_REMAIN_TRAFFIC', $overall_traffic);

				if ($this->user->data['user_type'] == USER_FOUNDER && FOUNDER_TRAFFICS_OFF)
				{
					$text_no_more_remain_traffic = '<strong>' . $this->language->lang('DL_TRAFFICS_FOUNDER_INFO') . ':</strong> ' . $text_no_more_remain_traffic;
				}

				$this->template->assign_block_vars('no_remain_traffic', array(
					'NO_OVERALL_TRAFFIC' => $text_no_more_remain_traffic)
				);

				$s_separate_stats = true;
			}
			else
			{
				$remain_text_out = $this->language->lang('DL_REMAIN_OVERALL_TRAFFIC') . '<b>' . \oxpus\dlext\phpbb\classes\ dl_format::dl_size($remain_traffic, 2) . '</b>';

				if ($this->user->data['user_type'] == USER_FOUNDER && FOUNDER_TRAFFICS_OFF)
				{
					$remain_text_out = '<strong>' . $this->language->lang('DL_TRAFFICS_FOUNDER_INFO') . ':</strong> ' . $remain_text_out;
				}

				$this->template->assign_block_vars('remain_traffic', array(
					'REMAIN_TRAFFIC' => $remain_text_out)
				);

				$s_separate_stats = true;
			}
		}

		if ($this->user->data['is_registered'] && DL_USERS_TRAFFICS == true)
		{
			$user_traffic			= ($this->user->data['user_traffic'] > $remain_traffic && DL_OVERALL_TRAFFICS == true) ? $remain_traffic : $this->user->data['user_traffic'];
			$user_traffic_out		= \oxpus\dlext\phpbb\classes\ dl_format::dl_size($user_traffic, 2);
			$user_account_traffic	= $this->language->lang('DL_ACCOUNT', $user_traffic_out);

			if ($this->user->data['user_type'] == USER_FOUNDER && FOUNDER_TRAFFICS_OFF)
			{
				$user_account_traffic = '<strong>' . $this->language->lang('DL_TRAFFICS_FOUNDER_INFO') . ':</strong> ' . $user_account_traffic;
			}

			$this->template->assign_block_vars('userdata', array(
				'ACCOUNT_TRAFFIC' => ($this->user->data['user_id'] <> ANONYMOUS) ? $user_account_traffic : '')
			);

			$s_separate_stats = true;
		}

		if ((!$this->user->data['is_registered'] || $this->user->data['user_type'] == USER_FOUNDER) && DL_GUESTS_TRAFFICS == true)
		{
			if ($this->config['dl_overall_guest_traffic'] - $this->config['dl_remain_guest_traffic'] <= 0)
			{
				$overall_guest_traffic			= \oxpus\dlext\phpbb\classes\ dl_format::dl_size($this->config['dl_overall_guest_traffic']);
				$text_no_overall_guest_traffic	= $this->language->lang('DL_NO_MORE_REMAIN_GUEST_TRAFFIC', $overall_guest_traffic);

				if ($this->user->data['user_type'] == USER_FOUNDER)
				{
					$text_no_overall_guest_traffic = '<strong>' . $this->language->lang('DL_TRAFFICS_FOUNDER_INFO') . ':</strong> ' . $text_no_overall_guest_traffic;
				}

				$this->template->assign_block_vars('no_remain_guest_traffic', array(
					'NO_OVERALL_GUEST_TRAFFIC' => $text_no_overall_guest_traffic,
				));

				$s_separate_stats = true;
			}
			else
			{
				$remain_guest_traffic	= $this->config['dl_overall_guest_traffic'] - $this->config['dl_remain_guest_traffic'];
				$remain_guest_text_out	= $this->language->lang('DL_REMAIN_OVERALL_GUEST_TRAFFIC') . '<b>' . \oxpus\dlext\phpbb\classes\ dl_format::dl_size($remain_guest_traffic, 2) . '</b>';

				if ($this->user->data['user_type'] == USER_FOUNDER)
				{
					$remain_guest_text_out = '<strong>' . $this->language->lang('DL_TRAFFICS_FOUNDER_INFO') . ':</strong> ' . $remain_guest_text_out;
				}

				$this->template->assign_block_vars('remain_guest_traffic', array(
					'REMAIN_GUEST_TRAFFIC' => $remain_guest_text_out)
				);

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

	if ($this->config['dl_show_footer_legend'] && (!($view == 'search' && $submit) && !(in_array($view, array('user_config', 'todo', 'stat', 'upload', 'bug_tracker'))) || $cat))
	{
		$this->template->assign_var('S_FOOTER_LEGEND', true);
	}

	if ($this->config['dl_todo_link_onoff'] && $this->config['dl_todo_onoff'])
	{
		$this->template->assign_var('S_TODO_LINK', true);
	}

	if ($this->config['dl_uconf_link_onoff'] && $this->user->data['is_registered'])
	{
		$this->template->assign_var('S_U_CONFIG_LINK', true);
	}

	if ($this->config['dl_rss_enable'])
	{
		$this->template->assign_var('U_DL_RSS_FEED', $this->helper->route('oxpus_dlext_controller', array('view' => 'rss')));
	}

	/*
	* display the page and return after this
	*/
	$this->template->assign_display('dl_footer');
}
