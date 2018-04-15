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

// disable the feed until it is enabled and contains at least one entry
$display_feed = false;

if ($this->config['dl_rss_enable'])
{
	// Switch user to anonymous to prepare the correct permissions, if wanted
	if (!$this->config['dl_rss_perms'])
	{
		$perm_backup = array('user_backup' => $this->user->data);

		// sql to get the users info
		$sql = 'SELECT *
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . ANONYMOUS;
		$result	= $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// reset the current users info to that of the bot
		$this->user->data = array_merge($this->user->data, $row);
		$this->user->data['user_permissions'] = '';
		$this->user->data['session_user_id'] = ANONYMOUS;
		$this->user->data['session_admin'] = 0;
		$this->user->data['is_registered'] = false;
		$this->user->data['user_perm_from'] = ANONYMOUS;

		unset($row);
	}

	// Define the basic file storage placement
	if ($this->config['dl_download_dir'] == 2)
	{
		$filebase_prefix = $this->root_path . 'store/oxpus/dlext/';
		$filebase_web_prefix = generate_board_url() . '/store/oxpus/dlext/';
	}
	else
	{
		$filebase_prefix = $this->ext_path . 'files/';
		$filebase_web_prefix = $this->ext_path_web . 'files/';
	}

	define('DL_EXT_CACHE_FOLDER',		$filebase_prefix . 'cache/');
	define('DL_EXT_THUMBS_FOLDER',		$filebase_prefix . 'thumbs/');
	define('DL_EXT_THUMBS_WEB_FOLDER',	$filebase_web_prefix . 'thumbs/');
	define('DL_EXT_FILES_FOLDER',		$filebase_prefix . 'downloads/');
	define('DL_EXT_FILES_WEBFOLDER',	$filebase_web_prefix . 'downloads/');

	/*
	* include and create the main class
	*/
	include($this->ext_path . 'phpbb/classes/class_dlmod.' . $this->php_ext);
	$dl_mod = new \oxpus\dlext\phpbb\classes\ dl_mod($this->root_path, $this->php_ext, $this->ext_path);
	$dl_mod->register();
	\oxpus\dlext\phpbb\classes\ dl_init::init($this->ext_path);

	// Get the possible categories
	$access_cats = array();
	$access_cats = \oxpus\dlext\phpbb\classes\ dl_main::full_index($this->helper, 0, 0, 0, 1);

	// Does we have some cats? miau ...
	if (sizeof($access_cats))
	{
		$sql_where_cats = ' AND ' . $this->db->sql_in_set('cat', $access_cats);

		$rss_cats_ary = array_map('intval', explode(',', $this->config['dl_rss_cats_select']));

		switch ($this->config['dl_rss_cats'])
		{
			case 1:
				$sql_where_cats .= ' AND ' . $this->db->sql_in_set('cat', $rss_cats_ary);
			break;

			case 2:
				$sql_where_cats .= ' AND ' . $this->db->sql_in_set('cat', $rss_cats_ary, true);
			break;

			default:
				$sql_where_cats .= '';
		}

		$sql_sort_by = ($this->config['dl_rss_select']) ? 'rand()' : 'change_time';
		$sql_order_by = ($this->config['dl_rss_select']) ? '' : 'DESC';
		$sql_limit = intval($this->config['dl_rss_number']);

		if ($this->config['dl_rss_desc_length'])
		{
			$sql_fields = 'id, cat, description, desc_uid, hack_version, add_time, change_time, long_desc, long_desc_uid';
		}
		else
		{
			$sql_fields = 'id, cat, description, desc_uid, hack_version, add_time, change_time';
		}

		$rss = true;

		$dl_files = array();
		$dl_files = \oxpus\dlext\phpbb\classes\ dl_files::all_files(0, $sql_sort_by, $sql_order_by , $sql_where_cats, 0, 0, $sql_fields, $sql_limit);

		if (sizeof($dl_files))
		{
			header("Content-Type: application/rss+xml");

			$this->template->set_filenames(array(
				'body'	=> 'dl_rss.xml',
			));

			for ($i = 0; $i < sizeof($dl_files); $i++)
			{
				$dl_id			= $dl_files[$i]['id'];
				$dl_cat			= $dl_files[$i]['cat'];
				$hack_version	= $dl_files[$i]['hack_version'];
				$last_time		= date('r', $dl_files[$i]['change_time']);

				if ($i == 0)
				{
					$timetmp	= $last_time;
				}

				$description	= $dl_files[$i]['description'];
				$desc_uid		= $dl_files[$i]['desc_uid'];
				$description	= censor_text($description);
				strip_bbcode($description, $desc_uid);
				$description	.= ' ' . $hack_version;

				if ($this->config['dl_rss_desc_length'])
				{
					$long_desc			= $dl_files[$i]['long_desc'];
					$long_desc_uid		= $dl_files[$i]['long_desc_uid'];

					if ($this->config['dl_rss_desc_length'] == 2)
					{
						if (intval($this->config['dl_rss_desc_shorten']) && strlen($long_desc) > intval($this->config['dl_rss_desc_shorten']))
						{
							$long_desc = substr($long_desc, 0, intval($this->config['dl_rss_desc_shorten'])) . ' [...]';
						}
						else
						{
							$long_desc = '';
						}
					}

					if ($long_desc)
					{
						$long_desc = censor_text($long_desc);
						strip_bbcode($long_desc, $long_desc_uid);
					}
				}
				else
				{
					$long_desc = '';
				}

				if ($this->config['dl_rss_new_update'])
				{
					$mini_status = \oxpus\dlext\phpbb\classes\ dl_status::mini_status_file($dl_cat, $dl_id, $rss);
				}
				else
				{
					$mini_status = '';
				}

				$this->template->assign_block_vars('dl_rss_feed', array(
					'DL_RSS_TITLE'	=> $description,
					'DL_RSS_MINI_S'	=> $mini_status,
					'DL_RSS_DESC'	=> $long_desc,
					'DL_RSS_TIME'	=> $last_time,

					'U_DL_RSS'		=> generate_board_url(true) . $this->helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $dl_id)),
				));
			}

			$display_feed = true;
		}
	}

	// Restore the user data to the original one to finish the feed here correctly
	if (!$this->config['dl_rss_perms'])
	{
		$this->user->data = $perm_backup['user_backup'];
		unset($perm_backup);
	}
}

if (!$this->config['dl_rss_enable'] || !$display_feed)
{
	switch ($this->config['dl_rss_off_action'])
	{
		case 0:
			redirect($this->helper->route('oxpus_dlext_controller'));
		break;

		case 1:
			redirect(append_sid($this->root_path . 'index.' . $this->php_ext));
		break;

		default:
			trigger_error($this->config['dl_rss_off_text']);
	}
}

$this->template->assign_vars(array(
	'SITENAME'				=> $this->config['sitename'],
	'BOARD_URL'				=> generate_board_url() . '/',
	'BOARD_SIG'				=> $this->config['board_email'],
	'DL_RSS_TIME_TMP'   	=> $timetmp,
	'SITE_DESCRIPTION'		=> $this->config['site_desc'],

	'S_CONTENT_ENCODING'	=> 'UTF-8',

	'U_DL_RSS'				=> generate_board_url() . "/downloads.$this->php_ext?view=rss",
));

page_footer();
