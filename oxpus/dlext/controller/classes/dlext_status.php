<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\classes;

use Symfony\Component\DependencyInjection\Container;

class dlext_status implements dlext_status_interface
{
	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	protected $language;

	protected $dlext_auth;
	protected $dlext_format;
	protected $dlext_init;
	protected $dlext_main;
	protected $dl_file_p;
	protected $dl_file_icon;
	protected $dl_auth;
	protected $dl_index;

	/**
	* Constructor
	*
	* @param Container 								$phpbb_container

	* @param \phpbb\config\config					$config
	* @param \phpbb\controller\helper				$helper
	*/
	public function __construct(
		Container $phpbb_container,

		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		$dlext_auth,
		$dlext_format,
		$dlext_init,
		$dlext_main
		)
	{
		$this->config 		= $config;
		$this->helper 		= $helper;

		$this->language		= $phpbb_container->get('language');

		$this->dlext_auth	= $dlext_auth;
		$this->dlext_format = $dlext_format;
		$this->dlext_init 	= $dlext_init;
		$this->dlext_main 	= $dlext_main;

		$this->dl_file_p	= $this->dlext_init->dl_file_p();
		$this->dl_file_icon	= $this->dlext_init->dl_file_icon();
		$this->dl_auth		= $this->dlext_auth->dl_auth();
		$this->dl_index		= $this->dlext_auth->dl_index();
	}	

	public function mini_status_file($parent, $file_id, $rss = false)
	{
		if (isset($this->dl_file_icon['new'][$parent][$file_id]) && $this->dl_file_icon['new'][$parent][$file_id] == true)
		{
			$mini_icon_img = ($rss) ? $this->language->lang('DL_FILE_NEW') : '<i class="icon fa-comment-o fa-fw dl-red" title="' . $this->language->lang('DL_FILE_NEW') . '"></i>';
		}
		else if (isset($this->dl_file_icon['edit'][$parent][$file_id]) && $this->dl_file_icon['edit'][$parent][$file_id] == true)
		{
			$mini_icon_img = ($rss) ? $this->language->lang('DL_FILE_EDIT') : '<i class="icon fa-edit fa-fw dl-blue" title="' . $this->language->lang('DL_FILE_EDIT') . '"></i>';
		}
		else
		{
			$mini_icon_img = '';
		}

		return $mini_icon_img;
	}

	public function mini_status_cat($cur, $parent, $flag = 0)
	{
		$mini_status_icon[$cur]['new'] = 0;
		$mini_status_icon[$cur]['edit'] = 0;

		if (!is_array($this->dl_index) || !sizeof($this->dl_index))
		{
			return [];
		}

		foreach($this->dl_index as $cat_id => $value)
		{
			if ($cat_id == $parent && !$flag)
			{
				if ((isset($this->dl_index[$cat_id]['auth_view']) && $this->dl_index[$cat_id]['auth_view']) || (isset($this->dl_auth[$cat_id]['auth_view'])))
				{
					if (isset($this->dl_index[$cat_id]['total']))
					{
						$new_sum = (isset($this->dl_file_icon['new_sum'][$cat_id])) ? intval($this->dl_file_icon['new_sum'][$cat_id]) : 0;
						$edit_sum = (isset($this->dl_file_icon['edit_sum'][$cat_id])) ? intval($this->dl_file_icon['edit_sum'][$cat_id]) : 0;

						$mini_status_icon[$cur]['new'] += $new_sum;
						$mini_status_icon[$cur]['edit'] += $edit_sum;
					}
				}

				$mini_icon = $this->mini_status_cat($cur, $cat_id, 1);
				$mini_status_icon[$cur]['new'] += $mini_icon[$cur]['new'];
				$mini_status_icon[$cur]['edit'] += $mini_icon[$cur]['edit'];
			}

			if ((isset($this->dl_index[$cat_id]['parent']) && $this->dl_index[$cat_id]['parent'] == $parent) && $flag)
			{
				if ((isset($this->dl_index[$cat_id]['auth_view']) && $this->dl_index[$cat_id]['auth_view']) || (isset($this->dl_auth[$cat_id]['auth_view'])))
				{
					if (isset($this->dl_index[$cat_id]['total']))
					{
						$new_sum = (isset($this->dl_file_icon['new_sum'][$cat_id])) ? intval($this->dl_file_icon['new_sum'][$cat_id]) : 0;
						$edit_sum = (isset($this->dl_file_icon['edit_sum'][$cat_id])) ? intval($this->dl_file_icon['edit_sum'][$cat_id]) : 0;

						$mini_status_icon[$cur]['new'] += $new_sum;
						$mini_status_icon[$cur]['edit'] += $edit_sum;
					}
				}

				$mini_icon = $this->mini_status_cat($cur, $cat_id, 1);
				$mini_status_icon[$cur]['new'] += $mini_icon[$cur]['new'];
				$mini_status_icon[$cur]['edit'] += $mini_icon[$cur]['edit'];
			}
		}

		return $mini_status_icon;
	}

	public function status($df_id)
	{
		$t_red = $this->language->lang('DL_RED_EXPLAIN_PERM');
		$t_blue = $this->language->lang('DL_BLUE_EXPLAIN_FOOT');
		$t_grey = $this->language->lang('DL_GREY_EXPLAIN');
		$t_white = $this->language->lang('DL_WHITE_EXPLAIN');
		$t_yellow = $this->language->lang('DL_YELLOW_EXPLAIN');
		$t_green = $this->language->lang('DL_GREEN_EXPLAIN');

		if (!isset($this->dl_file_p[$df_id]['cat']))
		{
			return ['status' => '', 'file_name' => '', 'auth_dl' => 0, 'file_detail' => '', 'status_detail' => '<i class="icon fa-ban fa-fw dl-red" title="' . $t_red . '"></i>'];
		}

		$cat_id = $this->dl_file_p[$df_id]['cat'];
		$cat_auth = [];
		$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);
		$index = [];
		$index = $this->dlext_main->full_index($cat_id);
		$status = '';
		$status_detail = '<i class="icon fa-ban fa-fw dl-red" title="' . $t_red . '"></i>';
		$file_name = '';
		$auth_dl = 0;

		$file_name = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $this->dl_file_p[$df_id]['file_name'] . '</a>';
		$file_detail = $this->dl_file_p[$df_id]['file_name'];

		if ($this->dlext_auth->user_banned())
		{
			$status_detail = '<i class="icon fa-ban fa-fw dl-red" title="' . $t_red . '"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $status_detail . '</a>';
			$auth_dl = 0;
			return ['status' => $status, 'file_name' => $file_detail, 'auth_dl' => $auth_dl, 'file_detail' => $file_detail, 'status_detail' => $status_detail];
		}

		if (!$this->config['dl_traffic_off'] && (DL_USERS_TRAFFICS == true || FOUNDER_TRAFFICS_OFF == true))
		{
			if (FOUNDER_TRAFFICS_OFF == true)
			{
				$status_detail = '<i class="icon fa-download fa-fw dl-yellow" title="' . $t_yellow . '"></i>';
				$status = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $status_detail . '</a>';
				$auth_dl = true;
			}
			else if ($this->dlext_auth->user_logged_in() && intval($this->dlext_auth->user_traffic()) >= $this->dl_file_p[$df_id]['file_size'] && !$this->dl_file_p[$df_id]['extern'])
			{
				$status_detail = '<i class="icon fa-download fa-fw dl-yellow" title="' . $t_yellow . '"></i>';
				$status = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $status_detail . '</a>';
				$auth_dl = true;
			}
			else if ($this->dlext_auth->user_logged_in() && intval($this->dlext_auth->user_traffic()) < $this->dl_file_p[$df_id]['file_size'] && !$this->dl_file_p[$df_id]['extern'])
			{
				$status_detail = '<i class="icon fa-ban fa-fw dl-red" title="' . $t_red . '"></i>';
				$status = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $status_detail . '</a>';
				$auth_dl = 0;
			}
		}
		else
		{
			$status_detail = '<i class="icon fa-download fa-fw dl-green" title="' . $t_green . '"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $status_detail . '</a>';
			$auth_dl = true;
		}

		if ($this->dlext_auth->user_posts() < $this->config['dl_posts'] && !$this->dl_file_p[$df_id]['extern'] && !$this->dl_file_p[$df_id]['free'])
		{
			$status_detail = '<i class="icon fa-ban fa-fw dl-red" title="' . $t_red . '"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $status_detail . '</a>';
			$auth_dl = 0;
		}

		if (!$this->dlext_auth->user_logged_in() && !$this->dl_file_p[$df_id]['extern'] && !$this->dl_file_p[$df_id]['free'])
		{
			$status_detail = '<i class="icon fa-ban fa-fw dl-red" title="' . $t_red . '"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $status_detail . '</a>';
			$auth_dl = 0;
		}

		if ($this->dl_file_p[$df_id]['free'] == 1)
		{
			$status_detail = '<i class="icon fa-download fa-fw dl-green" title="' . $t_green . '"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $status_detail . '</a>';
			$auth_dl = true;
		}

		if ($this->dl_file_p[$df_id]['free'] == 2)
		{
			if (($this->config['dl_icon_free_for_reg'] && !$this->dlext_auth->user_logged_in()) || (!$this->config['dl_icon_free_for_reg'] && $this->dlext_auth->user_logged_in()))
			{
				$status_detail = '<i class="icon fa-download fa-fw dl-white" title="' . $t_white . '"></i>';
				$status = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $status_detail . '</a>';
			}

			if ($this->dlext_auth->user_logged_in() || FOUNDER_TRAFFICS_OFF == true)
			{
				$auth_dl = true;
			}
			else
			{
				$auth_dl = 0;
			}
		}

		if (!$cat_auth['auth_dl'] && !$index[$cat_id]['auth_dl'] && !$this->dlext_auth->user_admin())
		{
			$status_detail = '<i class="icon fa-ban fa-fw dl-red" title="' . $t_red . '"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $status_detail . '</a>';
			$auth_dl = 0;
		}

		if ($this->dl_file_p[$df_id]['file_traffic'] && $this->dl_file_p[$df_id]['klicks'] * $this->dl_file_p[$df_id]['file_size'] >= $this->dl_file_p[$df_id]['file_traffic'] && !$this->config['dl_traffic_off'])
		{
			$status_detail = '<i class="icon fa-info-circle fa-fw dl-blue" title="' . $t_blue . '"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $status_detail . '</a>';

			if (FOUNDER_TRAFFICS_OFF == true)
			{
				$auth_dl = true;
			}
			else
			{
				$auth_dl = 0;
			}
		}

		if ($this->dlext_auth->user_logged_in())
		{
			$load_limit = DL_OVERALL_TRAFFICS;
			$overall_traffic = $this->config['dl_overall_traffic'];
			$remain_traffic = $this->config['dl_remain_traffic'];
		}
		else
		{
			$load_limit = DL_GUESTS_TRAFFICS;
			$overall_traffic = $this->config['dl_overall_guest_traffic'];
			$remain_traffic = $this->config['dl_remain_guest_traffic'];
		}

		if (($overall_traffic - (int) $remain_traffic <= $this->dl_file_p[$df_id]['file_size']) && !$this->config['dl_traffic_off'] && $load_limit == true)
		{
			$status_detail = '<i class="icon fa-info-circle fa-fw dl-blue" title="' . $t_blue . '"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $status_detail . '</a>';

			if (FOUNDER_TRAFFICS_OFF == true)
			{
				$auth_dl = true;
			}
			else
			{
				$auth_dl = 0;
			}
		}

		if (($index[$cat_id]['cat_traffic'] && ($index[$cat_id]['cat_traffic'] - $index[$cat_id]['cat_traffic_use'] <= 0)) && !$this->config['dl_traffic_off'])
		{
			$status_detail = '<i class="icon fa-info-circle fa-fw dl-blue" title="' . $t_blue . '"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $status_detail . '</a>';

			if (FOUNDER_TRAFFICS_OFF == true)
			{
				$auth_dl = true;
			}
			else
			{
				$auth_dl = 0;
			}
		}

		if ($this->dl_file_p[$df_id]['extern'])
		{
			$status_detail = '<i class="icon fa-globe fa-fw dl-grey" title="' . $t_grey . '"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $status_detail . '</a>';
			$file_name = '<a href="' . $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]) . '">' . $this->language->lang('DL_EXTERN') . '</a>';
			$auth_dl = true;
		}

		return ['status' => $status, 'file_name' => $file_name, 'auth_dl' => $auth_dl, 'file_detail' => $file_detail, 'status_detail' => $status_detail];
	}
}
