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
	}	

	public function mini_status_file($parent, $file_id, $rss = false)
	{
		$dl_file_icon = $this->dlext_init->dl_file_icon();

		if (isset($dl_file_icon['new'][$parent][$file_id]) && $dl_file_icon['new'][$parent][$file_id] == true)
		{
			$mini_icon_img = ($rss) ? $this->language->lang('DL_FILE_NEW') : '<i class="icon fa-comment-o fa-fw dl-red"></i>';
		}
		else if (isset($dl_file_icon['edit'][$parent][$file_id]) && $dl_file_icon['edit'][$parent][$file_id] == true)
		{
			$mini_icon_img = ($rss) ? $this->language->lang('DL_FILE_EDIT') : '<i class="icon fa-edit fa-fw dl-blue"></i>';
		}
		else
		{
			$mini_icon_img = '';
		}

		return $mini_icon_img;
	}

	public function mini_status_cat($cur, $parent, $flag = 0)
	{
		$dl_file_icon	= $this->dlext_init->dl_file_icon();
		$dl_auth		= $this->dlext_auth->dl_auth();
		$dl_index		= $this->dlext_auth->dl_index();

		$mini_status_icon[$cur]['new'] = 0;
		$mini_status_icon[$cur]['edit'] = 0;

		if (!is_array($dl_index) || !sizeof($dl_index))
		{
			return array();
		}

		foreach($dl_index as $cat_id => $value)
		{
			if ($cat_id == $parent && !$flag)
			{
				if ((isset($dl_index[$cat_id]['auth_view']) && $dl_index[$cat_id]['auth_view']) || (isset($dl_auth[$cat_id]['auth_view'])))
				{
					if (isset($dl_index[$cat_id]['total']))
					{
						$new_sum = (isset($dl_file_icon['new_sum'][$cat_id])) ? intval($dl_file_icon['new_sum'][$cat_id]) : 0;
						$edit_sum = (isset($dl_file_icon['edit_sum'][$cat_id])) ? intval($dl_file_icon['edit_sum'][$cat_id]) : 0;

						$mini_status_icon[$cur]['new'] += $new_sum;
						$mini_status_icon[$cur]['edit'] += $edit_sum;
					}
				}

				$mini_icon = $this->mini_status_cat($cur, $cat_id, 1);
				$mini_status_icon[$cur]['new'] += $mini_icon[$cur]['new'];
				$mini_status_icon[$cur]['edit'] += $mini_icon[$cur]['edit'];
			}

			if ((isset($dl_index[$cat_id]['parent']) && $dl_index[$cat_id]['parent'] == $parent) && $flag)
			{
				if ((isset($dl_index[$cat_id]['auth_view']) && $dl_index[$cat_id]['auth_view']) || (isset($dl_auth[$cat_id]['auth_view'])))
				{
					if (isset($dl_index[$cat_id]['total']))
					{
						$new_sum = (isset($dl_file_icon['new_sum'][$cat_id])) ? intval($dl_file_icon['new_sum'][$cat_id]) : 0;
						$edit_sum = (isset($dl_file_icon['edit_sum'][$cat_id])) ? intval($dl_file_icon['edit_sum'][$cat_id]) : 0;

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
		$dl_file_p = $this->dlext_init->dl_file_p();

		if (!isset($dl_file_p[$df_id]['cat']))
		{
			return array('status' => '', 'file_name' => '', 'auth_dl' => 0, 'file_detail' => '', 'status_detail' => '<i class="icon fa-ban fa-fw dl-red"></i>');
		}

		$cat_id = $dl_file_p[$df_id]['cat'];
		$cat_auth = array();
		$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);
		$index = array();
		$index = $this->dlext_main->full_index($cat_id);
		$status = '';
		$status_detail = '<i class="icon fa-ban fa-fw dl-red"></i>';
		$file_name = '';
		$auth_dl = 0;

		$file_name = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $dl_file_p[$df_id]['file_name'] . '</a>';
		$file_detail = $dl_file_p[$df_id]['file_name'];

		if ($this->dlext_auth->user_banned())
		{
			$status_detail = '<i class="icon fa-ban fa-fw dl-red"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $status_detail . '</a>';
			$auth_dl = 0;
			return array('status' => $status, 'file_name' => $file_detail, 'auth_dl' => $auth_dl, 'file_detail' => $file_detail, 'status_detail' => $status_detail);
		}

		if (!$this->config['dl_traffic_off'] && (DL_USERS_TRAFFICS == true || FOUNDER_TRAFFICS_OFF == true))
		{
			if (FOUNDER_TRAFFICS_OFF == true)
			{
				$status_detail = '<i class="icon fa-download fa-fw dl-yellow"></i>';
				$status = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $status_detail . '</a>';
				$auth_dl = true;
			}
			else if ($this->dlext_auth->user_logged_in() && intval($this->dlext_auth->user_traffic()) >= $dl_file_p[$df_id]['file_size'] && !$dl_file_p[$df_id]['extern'])
			{
				$status_detail = '<i class="icon fa-download fa-fw dl-yellow"></i>';
				$status = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $status_detail . '</a>';
				$auth_dl = true;
			}
			else if ($this->dlext_auth->user_logged_in() && intval($this->dlext_auth->user_traffic()) < $dl_file_p[$df_id]['file_size'] && !$dl_file_p[$df_id]['extern'])
			{
				$status_detail = '<i class="icon fa-ban fa-fw dl-red"></i>';
				$status = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $status_detail . '</a>';
				$auth_dl = 0;
			}
		}
		else
		{
			$status_detail = '<i class="icon fa-download fa-fw dl-grey"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $status_detail . '</a>';
			$auth_dl = true;
		}

		if ($this->dlext_auth->user_posts() < $this->config['dl_posts'] && !$dl_file_p[$df_id]['extern'] && !$dl_file_p[$df_id]['free'])
		{
			$status_detail = '<i class="icon fa-ban fa-fw dl-red"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $status_detail . '</a>';
			$auth_dl = 0;
		}

		if (!$this->dlext_auth->user_logged_in() && !$dl_file_p[$df_id]['extern'] && !$dl_file_p[$df_id]['free'])
		{
			$status_detail = '<i class="icon fa-ban fa-fw dl-red"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $status_detail . '</a>';
			$auth_dl = 0;
		}

		if ($dl_file_p[$df_id]['free'] == 1)
		{
			$status_detail = '<i class="icon fa-download fa-fw dl-green"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $status_detail . '</a>';
			$auth_dl = true;
		}

		if ($dl_file_p[$df_id]['free'] == 2)
		{
			if (($this->config['dl_icon_free_for_reg'] && !$this->dlext_auth->user_logged_in()) || (!$this->config['dl_icon_free_for_reg'] && $this->dlext_auth->user_logged_in()))
			{
				$status_detail = '<i class="icon fa-download fa-fw dl-grey"></i>';
				$status = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $status_detail . '</a>';
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
			$status_detail = '<i class="icon fa-ban fa-fw dl-red"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $status_detail . '</a>';
			$auth_dl = 0;
		}

		if ($dl_file_p[$df_id]['file_traffic'] && $dl_file_p[$df_id]['klicks'] * $dl_file_p[$df_id]['file_size'] >= $dl_file_p[$df_id]['file_traffic'] && !$this->config['dl_traffic_off'])
		{
			$status_detail = '<i class="icon fa-info-circle fa-fw dl-blue"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $status_detail . '</a>';

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

		if (($overall_traffic - (int) $remain_traffic <= $dl_file_p[$df_id]['file_size']) && !$this->config['dl_traffic_off'] && $load_limit == true)
		{
			$status_detail = '<i class="icon fa-info-circle fa-fw dl-blue"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $status_detail . '</a>';

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
			$status_detail = '<i class="icon fa-info-circle fa-fw dl-blue"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $status_detail . '</a>';

			if (FOUNDER_TRAFFICS_OFF == true)
			{
				$auth_dl = true;
			}
			else
			{
				$auth_dl = 0;
			}
		}

		if ($dl_file_p[$df_id]['extern'])
		{
			$status_detail = '<i class="icon fa-globe fa-fw dl-blue"></i>';
			$status = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $status_detail . '</a>';
			$file_name = '<a href="' . $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)) . '">' . $this->language->lang('DL_EXTERN') . '</a>';
			$auth_dl = true;
		}

		return array('status' => $status, 'file_name' => $file_name, 'auth_dl' => $auth_dl, 'file_detail' => $file_detail, 'status_detail' => $status_detail);
	}
}
