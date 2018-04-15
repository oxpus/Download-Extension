<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\phpbb\classes;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class dl_status extends dl_mod
{
	public static function mini_status_file($parent, $file_id, $rss = false)
	{
		static $dl_file_icon;

		global $user, $dl_file_icon;
		global $phpbb_container;
		$language = $phpbb_container->get('language');

		if (isset($dl_file_icon['new'][$parent][$file_id]) && $dl_file_icon['new'][$parent][$file_id] == true)
		{
			$mini_icon_img = ($rss) ? $language->lang('DL_FILE_NEW') : '<i class="icon fa-comment-o fa-fw dl-red"></i>';
		}
		else if (isset($dl_file_icon['edit'][$parent][$file_id]) && $dl_file_icon['edit'][$parent][$file_id] == true)
		{
			$mini_icon_img = ($rss) ? $language->lang('DL_FILE_EDIT') : '<i class="icon fa-edit fa-fw dl-blue"></i>';
		}
		else
		{
			$mini_icon_img = '';
		}

		return $mini_icon_img;
	}

	public static function mini_status_cat($cur, $parent, $flag = 0)
	{
		static $dl_index, $dl_auth, $dl_file_icon;
		global $dl_index, $dl_auth, $dl_file_icon;

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

				$mini_icon = self::mini_status_cat($cur, $cat_id, 1);
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

				$mini_icon = self::mini_status_cat($cur, $cat_id, 1);
				$mini_status_icon[$cur]['new'] += $mini_icon[$cur]['new'];
				$mini_status_icon[$cur]['edit'] += $mini_icon[$cur]['edit'];
			}
		}

		return $mini_status_icon;
	}

	public static function status($df_id, $helper)
	{
		static $dl_file_p, $user_banned, $user_logged_in, $user_traffic, $user_posts, $user_admin, $language;

		global $user, $config;
		global $dl_file_p, $user_banned, $user_logged_in, $user_traffic, $user_posts, $user_admin;
		global $phpbb_container;
		$language = $phpbb_container->get('language');

		if (!isset($dl_file_p[$df_id]['cat']))
		{
			return array('status' => '', 'file_name' => '', 'auth_dl' => 0, 'file_detail' => '', 'status_detail' => '<i class="icon fa-ban fa-fw dl-red"></i>');
		}

		$cat_id = $dl_file_p[$df_id]['cat'];
		$cat_auth = array();
		$cat_auth = dl_auth::dl_cat_auth($cat_id);
		$index = array();
		$index = dl_main::full_index($helper, $cat_id);
		$status = '';
		$status_detail = '<i class="icon fa-ban fa-fw dl-red"></i>';
		$file_name = '';
		$auth_dl = 0;

		$file_name = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $dl_file_p[$df_id]['file_name'] . '</a>';
		$file_detail = $dl_file_p[$df_id]['file_name'];

		if ($user_banned)
		{
			$status_detail = '<i class="icon fa-ban fa-fw dl-red"></i>';
			$status = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $status_detail . '</a>';
			$auth_dl = 0;
			return array('status' => $status, 'file_name' => $file_detail, 'auth_dl' => $auth_dl, 'file_detail' => $file_detail, 'status_detail' => $status_detail);
		}

		if (!$config['dl_traffic_off'] && (DL_USERS_TRAFFICS == true || FOUNDER_TRAFFICS_OFF == true))
		{
			if (FOUNDER_TRAFFICS_OFF == true)
			{
				$status_detail = '<i class="icon fa-download fa-fw dl-yellow"></i>';
				$status = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $status_detail . '</a>';
				$auth_dl = true;
			}
			else if ($user_logged_in && intval($user_traffic) >= $dl_file_p[$df_id]['file_size'] && !$dl_file_p[$df_id]['extern'])
			{
				$status_detail = '<i class="icon fa-download fa-fw dl-yellow"></i>';
				$status = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $status_detail . '</a>';
				$auth_dl = true;
			}
			else if ($user_logged_in && intval($user_traffic) < $dl_file_p[$df_id]['file_size'] && !$dl_file_p[$df_id]['extern'])
			{
				$status_detail = '<i class="icon fa-ban fa-fw dl-red"></i>';
				$status = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $status_detail . '</a>';
				$auth_dl = 0;
			}
		}
		else
		{
			$status_detail = '<i class="icon fa-download fa-fw dl-grey"></i>';
			$status = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $status_detail . '</a>';
			$auth_dl = true;
		}

		if ($user_posts < $config['dl_posts'] && !$dl_file_p[$df_id]['extern'] && !$dl_file_p[$df_id]['free'])
		{
			$status_detail = '<i class="icon fa-ban fa-fw dl-red"></i>';
			$status = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $status_detail . '</a>';
			$auth_dl = 0;
		}

		if (!$user_logged_in && !$dl_file_p[$df_id]['extern'] && !$dl_file_p[$df_id]['free'])
		{
			$status_detail = '<i class="icon fa-ban fa-fw dl-red"></i>';
			$status = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $status_detail . '</a>';
			$auth_dl = 0;
		}

		if ($dl_file_p[$df_id]['free'] == 1)
		{
			$status_detail = '<i class="icon fa-download fa-fw dl-green"></i>';
			$status = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $status_detail . '</a>';
			$auth_dl = true;
		}

		if ($dl_file_p[$df_id]['free'] == 2)
		{
			if (($config['dl_icon_free_for_reg'] && !$user_logged_in) || (!$config['dl_icon_free_for_reg'] && $user_logged_in))
			{
				$status_detail = '<i class="icon fa-download fa-fw dl-grey"></i>';
				$status = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $status_detail . '</a>';
			}

			if ($user_logged_in || FOUNDER_TRAFFICS_OFF == true)
			{
				$auth_dl = true;
			}
			else
			{
				$auth_dl = 0;
			}
		}

		if (!$cat_auth['auth_dl'] && !$index[$cat_id]['auth_dl'] && !$user_admin)
		{
			$status_detail = '<i class="icon fa-ban fa-fw dl-red"></i>';
			$status = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $status_detail . '</a>';
			$auth_dl = 0;
		}

		if ($dl_file_p[$df_id]['file_traffic'] && $dl_file_p[$df_id]['klicks'] * $dl_file_p[$df_id]['file_size'] >= $dl_file_p[$df_id]['file_traffic'] && !$config['dl_traffic_off'])
		{
			$status_detail = '<i class="icon fa-info-circle fa-fw dl-blue"></i>';
			$status = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $status_detail . '</a>';

			if (FOUNDER_TRAFFICS_OFF == true)
			{
				$auth_dl = true;
			}
			else
			{
				$auth_dl = 0;
			}
		}

		if ($user->data['is_registered'])
		{
			$load_limit = DL_OVERALL_TRAFFICS;
			$overall_traffic = $config['dl_overall_traffic'];
			$remain_traffic = $config['dl_remain_traffic'];
		}
		else
		{
			$load_limit = DL_GUESTS_TRAFFICS;
			$overall_traffic = $config['dl_overall_guest_traffic'];
			$remain_traffic = $config['dl_remain_guest_traffic'];
		}

		if (($overall_traffic - $remain_traffic <= $dl_file_p[$df_id]['file_size']) && !$config['dl_traffic_off'] && $load_limit == true)
		{
			$status_detail = '<i class="icon fa-info-circle fa-fw dl-blue"></i>';
			$status = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $status_detail . '</a>';

			if (FOUNDER_TRAFFICS_OFF == true)
			{
				$auth_dl = true;
			}
			else
			{
				$auth_dl = 0;
			}
		}

		if (($index[$cat_id]['cat_traffic'] && ($index[$cat_id]['cat_traffic'] - $index[$cat_id]['cat_traffic_use'] <= 0)) && !$config['dl_traffic_off'])
		{
			$status_detail = '<i class="icon fa-info-circle fa-fw dl-blue"></i>';
			$status = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $status_detail . '</a>';

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
			$status = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $status_detail . '</a>';
			$file_name = '<a href="' . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)) . '">' . $language->lang('DL_EXTERN') . '</a>';
			$auth_dl = true;
		}

		return array('status' => $status, 'file_name' => $file_name, 'auth_dl' => $auth_dl, 'file_detail' => $file_detail, 'status_detail' => $status_detail);
	}
}
