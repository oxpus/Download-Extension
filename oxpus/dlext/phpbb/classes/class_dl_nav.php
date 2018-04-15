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

class dl_nav extends dl_mod
{
	public static function nav($helper = '', $parent, $disp_art, $tmp_nav, $basic_link = '')
	{
		static $dl_index, $dl_auth, $user_admin;

		global $config, $phpEx, $dl_index, $dl_auth, $user_admin;

		if (!is_array($dl_index) || !sizeof($dl_index))
		{
			return;
		}

		$cat_id = (isset($dl_index[$parent]['id'])) ? $dl_index[$parent]['id'] : 0;

		if (!$cat_id)
		{
			return;;
		}

		$temp_url = array('cat' => $cat_id);
		$temp_url_2 = ($helper) ? $helper->route('oxpus_dlext_controller', array('cat' => $cat_id)) : 'app.' . $phpEx . '/dlext/?cat=' . $cat_id;
		$temp_url_3 = str_replace('#CAT#', $dl_index[$cat_id]['parent'], $basic_link);
		$temp_title = $dl_index[$parent]['cat_name_nav'];

		if (((isset($dl_index[$cat_id]['auth_view']) && $dl_index[$cat_id]['auth_view']) || (isset($dl_auth[$cat_id]['auth_view']) && $dl_auth[$cat_id]['auth_view']) || $user_admin) && $disp_art == 'url')
		{
			$tmp_nav['link'][] = $temp_url;
			$tmp_nav['name'][] = $temp_title;
		}
		if (((isset($dl_index[$cat_id]['auth_view']) && $dl_index[$cat_id]['auth_view']) || (isset($dl_auth[$cat_id]['auth_view']) && $dl_auth[$cat_id]['auth_view']) || $user_admin) && $disp_art == 'links')
		{
			$path_dl_array[] = '<span class="crumb"><a href="' . $temp_url_2 . '">' . $temp_title . '</a></span>';
		}
		else if ($disp_art == 'acp')
		{
			$path_dl_array[] = '<a href="' . $temp_url_3 . '"><strong>' . $temp_title . '</strong></a>';
		}
		else
		{
			$path_dl_array[] = '<span class="crumb">' . $temp_title . '</span>';
		}

		if (isset($dl_index[$parent]['parent']) && $dl_index[$parent]['parent'] != 0)
		{
			$return = self::nav($helper, $dl_index[$parent]['parent'], $disp_art, $tmp_nav, $basic_link);

			if ($disp_art == 'url')
			{
				$tmp_nav = array_merge($tmp_nav, $return);
			}
			else
			{
				$path_dl_array[] = $return;
			}
		}

		$path_dl = '';

		if ($disp_art != 'url')
		{
			$tmp_navi_ary = array();
			for ($i = sizeof($path_dl_array); $i >= 0; --$i)
			{
				if (isset($path_dl_array[$i]))
				{
					$tmp_navi_ary[] = $path_dl_array[$i];
				}
			}

			if ($disp_art == 'acp')
			{
				$path_dl = implode('<strong> -> </strong>', $tmp_navi_ary);
			}
			else
			{
				$path_dl = implode('&nbsp;&raquo;&nbsp;', $tmp_navi_ary);
			}

			unset($tmp_navi_ary);
		}

		return ($disp_art == 'url') ? $tmp_nav : $path_dl;
	}
}
