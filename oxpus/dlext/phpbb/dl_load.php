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

/*
* check for hotlinking
*/
$hotlink_disabled = false;
$sql_where = '';

if ($this->config['dl_prevent_hotlink'])
{
	$hotlink_id = $this->request->variable('hotlink_id', '');

	if (!$hotlink_id)
	{
		$hotlink_disabled = true;
	}
	else
	{
		if (!$this->user->data['is_registered'])
		{
			$sql_where = " AND session_id = '" . $this->db->sql_escape($this->user->data['session_id']) . "' ";
		}

		$sql = 'SELECT COUNT(hotlink_id) AS total FROM ' . DL_HOTLINK_TABLE . '
			WHERE user_id = ' . (int) $this->user->data['user_id'] . "
				AND hotlink_id = '" . $this->db->sql_escape($hotlink_id) . "'
				$sql_where";
		$result = $this->db->sql_query($sql);
		$total_hotlinks = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		if ($total_hotlinks <> 1)
		{
			$hotlink_disabled = true;
		}
	}

	if ($hotlink_disabled)
	{
		if ($this->config['dl_hotlink_action'])
		{
			redirect($this->helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)));
		}
		else
		{
			trigger_error('DL_HOTLINK_PERMISSION');
		}
	}
}

/*
* THE basic function to get the download!
*/
$dl_file = array();
$dl_file = \oxpus\dlext\phpbb\classes\ dl_files::all_files(0, '', 'ASC', '', $df_id, $modcp, '*');

$cat_id = ($modcp) ? $cat_id : $dl_file['cat'];

if ($modcp && $cat_id)
{
	$cat_auth = array();
	$cat_auth = \oxpus\dlext\phpbb\classes\ dl_auth::dl_cat_auth($cat_id);

	if (!$this->auth->acl_get('a_') && !$cat_auth['auth_mod'])
	{
		$modcp = 0;
	}
}
else
{
	$modcp = 0;
}

/*
* check the permissions
*/
$check_status = array();
$check_status = \oxpus\dlext\phpbb\classes\ dl_status::status($df_id, $this->helper);
$status = $check_status['auth_dl'];
$cat_auth = array();
$cat_auth = \oxpus\dlext\phpbb\classes\ dl_auth::dl_cat_auth($cat_id);

if ($modcp)
{
	$check_status['auth_dl'] = true;
}

$browser = \oxpus\dlext\phpbb\classes\ dl_init::dl_client($this->user->data['session_browser'], $this->ext_path);

// Prepare the captcha permissions for the current user
$captcha_active = false;
$user_is_guest = false;
$user_is_mod = false;
$user_is_admin = false;
$user_is_founder = false;

if (!$this->user->data['is_registered'])
{
	$user_is_guest = true;
}
else
{
	if (($cat_auth['auth_mod'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered'])) && !\oxpus\dlext\phpbb\classes\ dl_auth::user_banned())
	{
		$user_is_mod = true;
	}

	if ($this->auth->acl_get('a_'))
	{
		$user_is_admin = true;
	}

	if ($this->user->data['user_type'] == USER_FOUNDER)
	{
		$user_is_founder = true;
	}
}

$ver_can_load = false;

if (($user_is_mod || $user_is_admin || $user_is_founder) || ($this->config['dl_edit_own_downloads'] && $dl_file['add_user'] == $this->user->data['user_id']))
{
	$ver_can_load = true;
}

switch ($this->config['dl_download_vc'])
{
	case 1:
		if ($user_is_guest)
		{
			$captcha_active = true;
		}
	break;

	case 2:
		if (!$user_is_mod && !$user_is_admin && !$user_is_founder)
		{
			$captcha_active = true;
		}
	break;

	case 3:
		if ($user_is_mod && !$user_is_admin && !$user_is_founder)
		{
			$captcha_active = true;
		}
	break;

	case 4:
		if ($user_is_mod && $user_is_admin && !$user_is_founder)
		{
			$captcha_active = true;
		}
	break;

	case 5:
		$captcha_active = true;
	break;
}

if ($captcha_active)
{
	if (!$this->user->data['is_registered'])
	{
		$sql_where = " AND session_id = '" . $this->db->sql_escape($this->user->data['session_id']) . "' ";
	}

	$sql = 'SELECT code FROM ' . DL_HOTLINK_TABLE . '
		WHERE user_id = ' . (int) $this->user->data['user_id'] . "
			AND hotlink_id = 'dlvc'
			$sql_where";
	$result = $this->db->sql_query($sql);
	$row_code = $this->db->sql_fetchfield('code');
	$this->db->sql_freeresult($result);

	if ($row_code != $code)
	{
		redirect($this->helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $df_id)));
	}
}

if ($check_status['auth_dl'] && $dl_file['id'])
{
	/*
	* fix the mod and admin auth if needed
	*/
	if (!$dl_file['approve'])
	{
		if ((($cat_auth['auth_mod'] || $index[$cat_id]['auth_mod']) && !$this->auth->acl_get('a_')) || ($this->auth->acl_get('a_') && $this->user->data['is_registered']))
		{
			$status = true;
		}
	}

	if (!$this->config['dl_traffic_off'])
	{
		if (FOUNDER_TRAFFICS_OFF == true)
		{
			$status = true;
		}
		else if ($this->user->data['is_registered'] && DL_OVERALL_TRAFFICS == true)
		{
			if (($this->config['dl_overall_traffic'] - $this->config['dl_remain_traffic']) < $dl_file['file_size'])
			{
				$status = false;
			}
		}
		else if (!$this->user->data['is_registered'] && DL_GUESTS_TRAFFICS == true)
		{
			if (($this->config['dl_overall_guest_traffic'] - $this->config['dl_remain_guest_traffic']) < $dl_file['file_size'])
			{
				$status = false;
			}
		}
	}

	/*
	* Antispam-Modul
	*
	* Block downloads for users who must have at least the given number of posts to download a file
	* and tries to download after spamming in the forum more than the needed number of posts in the last 24 hours
	*/
	if ($this->user->data['user_posts'] >= $this->config['dl_posts'] && !$dl_file['extern'] && !$dl_file['free'] && $this->config['dl_antispam_posts'] && $this->config['dl_antispam_hours'])
	{
		$sql = 'SELECT COUNT(post_id) AS total_posts FROM ' . POSTS_TABLE . '
			WHERE poster_id = ' . (int) $this->user->data['user_id'] . '
				AND post_time >= ' . (time() - ((int) $this->config['dl_antispam_hours'] * 3600));
		$result = $this->db->sql_query($sql);
		$post_count = $this->db->sql_fetchfield('total_posts');
		$this->db->sql_freeresult($result);

		if ($post_count >= $this->config['dl_antispam_posts'])
		{
			$status = false;
		}
	}

	// Prepare correct file for download
	if ($file_version)
	{
		$sql = 'SELECT ver_file_name, ver_real_file, ver_file_size, ver_active FROM ' . DL_VERSIONS_TABLE . '
			WHERE ver_id = ' . (int) $file_version;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$dl_file_name	= $row['ver_file_name'];
		$dl_real_file	= $row['ver_real_file'];
		$dl_file_size	= $row['ver_file_size'];
		$dl_ver_active	= $row['ver_active'];
		$this->db->sql_freeresult($result);

		if (!$dl_file_name)
		{
			trigger_error('DL_NO_ACCESS');
		}
		else if (!$dl_ver_active && !$ver_can_load)
		{
			trigger_error('DL_NO_ACCESS');
		}
		else
		{
			if ($dl_file['extern'])
			{
				$dl_file['file_name'] = $dl_file_name;
			}
			else
			{
				$dl_file['file_name'] = $dl_file_name;
				$dl_file['real_file'] = $dl_real_file;
				$dl_file['file_size'] = $dl_file_size;
			}
		}
	}

	/*
	* update all statistics
	*/
	if ($status)
	{
		$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
			'klicks'			=> $dl_file['klicks'] + 1,
			'overall_klicks'	=> $dl_file['overall_klicks'] + 1,
			'last_time'			=> time(),
			'down_user'			=> $this->user->data['user_id'])) . ' WHERE id = ' . (int) $df_id;
		$this->db->sql_query($sql);

		if ($this->user->data['is_registered'] && !$dl_file['free'] && !$dl_file['extern'] && !$this->config['dl_traffic_off'] && DL_USERS_TRAFFICS == true)
		{
			$count_user_traffic = true;

			if ($this->config['dl_user_traffic_once'])
			{
				$sql = 'SELECT COUNT(dl_id) AS total FROM ' . DL_NOTRAF_TABLE . '
					WHERE user_id = ' . (int) $this->user->data['user_id'] . '
						AND dl_id = ' . (int) $dl_file['id'];
				$result = $this->db->sql_query($sql);
				$still_count = $this->db->sql_fetchfield('total');
				$this->db->sql_freeresult($result);

				if ($still_count)
				{
					$count_user_traffic = false;
				}
			}

			if ($count_user_traffic && FOUNDER_TRAFFICS_OFF == false)
			{
				$this->user->data['user_traffic'] -= $dl_file['file_size'];

				$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
					'user_traffic' => $this->user->data['user_traffic'])) . ' WHERE user_id = ' . (int) $this->user->data['user_id'];
				$this->db->sql_query($sql);

				if ($this->config['dl_user_traffic_once'])
				{
					$sql = 'INSERT INTO ' . DL_NOTRAF_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
						'user_id'	=> $this->user->data['user_id'],
						'dl_id'		=> $dl_file['id']));
					$this->db->sql_query($sql);
				}
			}
		}

		if ($this->user->data['is_registered'])
		{
			$load_limit = DL_OVERALL_TRAFFICS;
			$remain_traffic = 'dl_remain_traffic';
		}
		else
		{
			$load_limit = DL_GUESTS_TRAFFICS;
			$remain_traffic = 'dl_remain_guest_traffic';
		}

		if (!$dl_file['extern'] && !$this->config['dl_traffic_off'] && FOUNDER_TRAFFICS_OFF == false)
		{
			if ($load_limit == true)
			{
				$new_remain = $this->config[$remain_traffic] + $dl_file['file_size'];

				$sql = 'UPDATE ' . DL_REM_TRAF_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
					'config_value' => $new_remain)) . " WHERE config_name = '" . $this->db->sql_escape($remain_traffic) . "'";
				$this->db->sql_query($sql);
			}

			$cat_traffic = $index[$cat_id]['cat_traffic'];

			if ($cat_traffic)
			{
				$sql = 'SELECT cat_traffic_use FROM ' . DL_CAT_TRAF_TABLE . '
					WHERE cat_id = ' . (int) $cat_id;
				$result = $this->db->sql_query($sql);
				$cat_traffic_use = $this->db->sql_fetchfield('cat_traffic_use');
				$this->db->sql_freeresult($result);

				if (($cat_traffic - $cat_traffic_use) < $dl_file['file_size'])
				{
					$status = false;
				}
				else
				{
					$cat_traffic_use += $dl_file['file_size'];

					$sql = 'UPDATE ' . DL_CAT_TRAF_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
						'cat_traffic_use' => $cat_traffic_use)) . ' WHERE cat_id = ' . (int) $cat_id;
					$this->db->sql_query($sql);
				}
			}
		}

		if ($index[$cat_id]['statistics'] && $status)
		{
			if ($index[$cat_id]['stats_prune'])
			{
				$stat_prune = \oxpus\dlext\phpbb\classes\ dl_main::dl_prune_stats($cat_id, $index[$cat_id]['stats_prune']);
			}

			$sql = 'INSERT INTO ' . DL_STATS_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
				'cat_id'		=> $cat_id,
				'id'			=> $df_id,
				'user_id'		=> $this->user->data['user_id'],
				'username'		=> $this->user->data['username'],
				'traffic'		=> $dl_file['file_size'],
				'direction'		=> 0,
				'user_ip'		=> $this->user->data['session_ip'],
				'browser'		=> $browser,
				'time_stamp'	=> time()));
			$this->db->sql_query($sql);
		}
	}

	/*
	* now it is time and we are ready to rumble: send the file to the user client to download it there!
	*/
	if ($dl_file['extern'])
	{
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: " . str_replace('&amp;', '&', $dl_file['file_name']));

		garbage_collection();
		exit_handler();
	}
	else if ($status)
	{
		include_once($this->root_path . 'includes/functions_download.' . $this->php_ext);

		$this->language->add_lang('viewtopic');

		$dl_file_url = str_replace($this->root_path, '', DL_EXT_FILES_FOLDER . $index[$cat_id]['cat_path']);

		$dl_file_data = array(
			'attach_id'				=> 0,
			'is_orphan'				=> false,
			'physical_filename'		=> $dl_file['real_file'],
			'real_filename'			=> $dl_file['file_name'],
			'mimetype'				=> 'application/octetstream',
			'filesize'				=> sprintf("%u", @filesize($dl_file_url . $dl_file['real_file'])),
			'filetime'				=> $dl_file['change_time'],
		);

		send_file_to_browser($dl_file_data, $dl_file_url, ATTACHMENT_CATEGORY_NONE);
		file_gc();
	}
	else
	{
		trigger_error('DL_NO_ACCESS');
	}
}
else
{
	trigger_error('DL_NO_ACCESS');
}
