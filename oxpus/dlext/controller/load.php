<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class load
{
	/* phpbb objects */
	protected $db;
	protected $config;
	protected $helper;
	protected $request;
	protected $user;
	protected $language;
	protected $dispatcher;
	protected $cache;
	protected $filesystem;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_files;
	protected $dlext_main;
	protected $dlext_physical;
	protected $dlext_status;
	protected $dlext_constants;

	protected $dlext_table_dl_cat_traf;
	protected $dlext_table_dl_favorites;
	protected $dlext_table_dl_hotlink;
	protected $dlext_table_dl_notraf;
	protected $dlext_table_dl_stats;
	protected $dlext_table_dl_versions;
	protected $dlext_table_downloads;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\service					$cache
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\user							$user
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\event\dispatcher_interface		$dispatcher
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\files				$dlext_files
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\physical			$dlext_physical
	 * @param \oxpus\dlext\core\status				$dlext_status
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_cat_traf
	 * @param string								$dlext_table_dl_favorites
	 * @param string								$dlext_table_dl_hotlink
	 * @param string								$dlext_table_dl_notraf
	 * @param string								$dlext_table_dl_stats
	 * @param string								$dlext_table_dl_versions
	 * @param string								$dlext_table_downloads
	 */
	public function __construct(
		\phpbb\cache\service $cache,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\physical $dlext_physical,
		\oxpus\dlext\core\status $dlext_status,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_cat_traf,
		$dlext_table_dl_favorites,
		$dlext_table_dl_hotlink,
		$dlext_table_dl_notraf,
		$dlext_table_dl_stats,
		$dlext_table_dl_versions,
		$dlext_table_downloads
	)
	{
		$this->cache					= $cache;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->user 					= $user;
		$this->language					= $language;
		$this->dispatcher				= $dispatcher;
		$this->filesystem				= $filesystem;

		$this->dlext_table_dl_cat_traf	= $dlext_table_dl_cat_traf;
		$this->dlext_table_dl_favorites	= $dlext_table_dl_favorites;
		$this->dlext_table_dl_hotlink	= $dlext_table_dl_hotlink;
		$this->dlext_table_dl_notraf	= $dlext_table_dl_notraf;
		$this->dlext_table_dl_stats		= $dlext_table_dl_stats;
		$this->dlext_table_dl_versions	= $dlext_table_dl_versions;
		$this->dlext_table_downloads	= $dlext_table_downloads;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_files				= $dlext_files;
		$this->dlext_main				= $dlext_main;
		$this->dlext_physical			= $dlext_physical;
		$this->dlext_status				= $dlext_status;
		$this->dlext_constants			= $dlext_constants;
	}

	public function handle()
	{
		$this->dlext_main->dl_handle_active();

		$index = $this->dlext_main->full_index();

		$file_version	= $this->request->variable('file_version', 0);
		$code			= $this->request->variable('code', '');
		$df_id			= $this->request->variable('df_id', 0);
		$cat_id			= $this->request->variable('cat_id', 0);
		$modcp			= $this->request->variable('modcp', 0);
		$dl_add_fav		= $this->request->variable('dl_add_fav', 0);

		/*
		* check for hotlinking
		*/
		$hotlink_disabled = $this->dlext_constants::DL_FALSE;
		$sql_where = '';

		if ($this->config['dl_prevent_hotlink'])
		{
			$hotlink_id = $this->request->variable('hotlink_id', '');

			if (!$hotlink_id)
			{
				$hotlink_disabled = $this->dlext_constants::DL_TRUE;
			}
			else
			{
				if (!$this->user->data['is_registered'])
				{
					$sql_where = " AND session_id = '" . $this->db->sql_escape($this->user->data['session_id']) . "' ";
				}

				$sql = 'SELECT COUNT(hotlink_id) AS total FROM ' . $this->dlext_table_dl_hotlink . '
					WHERE user_id = ' . (int) $this->user->data['user_id'] . "
						AND hotlink_id = '" . $this->db->sql_escape($hotlink_id) . "'
						$sql_where";
				$result = $this->db->sql_query($sql);
				$total_hotlinks = $this->db->sql_fetchfield('total');
				$this->db->sql_freeresult($result);

				if ($total_hotlinks != $this->dlext_constants::DL_TRUE)
				{
					$hotlink_disabled = $this->dlext_constants::DL_TRUE;
				}
			}

			if ($hotlink_disabled)
			{
				if ($this->config['dl_hotlink_action'])
				{
					redirect($this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]));
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
		$dl_file = $this->dlext_files->all_files(0, [], [], $df_id, $modcp, ['*']);

		$cat_id = ($modcp) ? $cat_id : $dl_file['cat'];

		if ($modcp && $cat_id)
		{
			$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);

			if (!$this->dlext_auth->user_admin() && !$cat_auth['auth_mod'])
			{
				$modcp = $this->dlext_constants::DL_FALSE;
			}
		}
		else
		{
			$modcp = $this->dlext_constants::DL_FALSE;
		}

		/*
		* check the permissions
		*/
		$check_status	= $this->dlext_status->status($df_id);
		$cat_auth		= $this->dlext_auth->dl_cat_auth($cat_id);

		if (!$this->dlext_auth->user_auth($cat_id, 'auth_view'))
		{
			trigger_error('DL_NO_PERMISSION');
		}

		if ($modcp)
		{
			$check_status['file_auth'] = $this->dlext_constants::DL_TRUE;
		}

		// Prepare the captcha permissions for the current user
		$user_is_mod		= $this->dlext_constants::DL_FALSE;

		if (($cat_auth['auth_mod'] || $this->dlext_auth->user_admin()))
		{
			$user_is_mod = $this->dlext_constants::DL_TRUE;
		}

		$captcha_active = $this->dlext_auth->get_captcha_status($this->config['dl_download_vc'], $cat_id);

		$ver_can_load = $this->dlext_constants::DL_FALSE;

		if (($user_is_mod || $this->dlext_auth->user_admin() || $this->user->data['user_type'] == USER_FOUNDER) || ($this->config['dl_edit_own_downloads'] && $dl_file['add_user'] == $this->user->data['user_id']))
		{
			$ver_can_load = $this->dlext_constants::DL_TRUE;
		}

		if ($captcha_active)
		{
			if (!$this->user->data['is_registered'])
			{
				$sql_where = " AND session_id = '" . $this->db->sql_escape($this->user->data['session_id']) . "' ";
			}

			$sql = 'SELECT hotlink_id FROM ' . $this->dlext_table_dl_hotlink . '
				WHERE user_id = ' . (int) $this->user->data['user_id'] . "
					AND code = 'dlvc'
					$sql_where";
			$result = $this->db->sql_query($sql);
			$row_code = $this->db->sql_fetchfield('hotlink_id');
			$this->db->sql_freeresult($result);

			if ($row_code != $code)
			{
				redirect($this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]));
			}
		}

		if ($check_status['file_auth'] && $dl_file['id'])
		{
			/*
			* fix the mod and admin auth if needed
			*/
			if (!$dl_file['approve'])
			{
				if ((($cat_auth['auth_mod'] || $index[$cat_id]['auth_mod']) && !$this->dlext_auth->user_admin()) || $this->dlext_auth->user_admin())
				{
					$check_status['file_auth'] = $this->dlext_constants::DL_TRUE;
				}
			}

			if (!$this->config['dl_traffic_off'])
			{
				if ($this->dlext_constants->get_value('founder_traffics'))
				{
					$check_status['file_auth'] = $this->dlext_constants::DL_TRUE;
				}
				else if ($this->user->data['is_registered'] && $this->dlext_constants->get_value('overall_traffics') == $this->dlext_constants::DL_TRUE)
				{
					if (($this->config['dl_overall_traffic'] - $this->config['dl_remain_traffic']) < $dl_file['file_size'])
					{
						$check_status['file_auth'] = $this->dlext_constants::DL_FALSE;
					}
				}
				else if (!$this->user->data['is_registered'] && $this->dlext_constants->get_value('guests_traffics') == $this->dlext_constants::DL_TRUE)
				{
					if (($this->config['dl_overall_guest_traffic'] - $this->config['dl_remain_guest_traffic']) < $dl_file['file_size'])
					{
						$check_status['file_auth'] = $this->dlext_constants::DL_FALSE;
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
						AND post_time >= ' . (time() - ((int) $this->config['dl_antispam_hours'] * $this->dlext_constants::DL_ONE_HOUR));
				$result = $this->db->sql_query($sql);
				$post_count = $this->db->sql_fetchfield('total_posts');
				$this->db->sql_freeresult($result);

				if ($post_count >= $this->config['dl_antispam_posts'])
				{
					$check_status['file_auth'] = $this->dlext_constants::DL_FALSE;
				}
			}

			// Prepare correct file for download
			if ($file_version)
			{
				$sql = 'SELECT ver_file_name, ver_real_file, ver_file_size, ver_active FROM ' . $this->dlext_table_dl_versions . '
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
			if ($check_status['file_auth'])
			{
				$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'klicks'			=> $dl_file['klicks'] + 1,
					'overall_klicks'	=> $dl_file['overall_klicks'] + 1,
					'last_time'			=> time(),
					'down_user'			=> $this->user->data['user_id']
				]) . ' WHERE id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				$this->cache->destroy('_dlext_file_p');

				if ($this->user->data['is_registered'] && !$dl_file['free'] && !$dl_file['extern'] && !$this->config['dl_traffic_off'] && $this->dlext_constants->get_value('users_traffics'))
				{
					$count_user_traffic = $this->dlext_constants::DL_TRUE;

					if ($this->config['dl_user_traffic_once'])
					{
						$sql = 'SELECT COUNT(dl_id) AS total FROM ' . $this->dlext_table_dl_notraf . '
							WHERE user_id = ' . (int) $this->user->data['user_id'] . '
								AND dl_id = ' . (int) $dl_file['id'];
						$result = $this->db->sql_query($sql);
						$still_count = $this->db->sql_fetchfield('total');
						$this->db->sql_freeresult($result);

						if ($still_count)
						{
							$count_user_traffic = $this->dlext_constants::DL_FALSE;
						}
					}

					if ($count_user_traffic && $this->dlext_constants->get_value('founder_traffics') == $this->dlext_constants::DL_FALSE)
					{
						$this->user->data['user_traffic'] -= $dl_file['file_size'];

						$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'user_traffic' => $this->user->data['user_traffic']
						]) . ' WHERE user_id = ' . (int) $this->user->data['user_id'];
						$this->db->sql_query($sql);

						if ($this->config['dl_user_traffic_once'])
						{
							$sql = 'INSERT INTO ' . $this->dlext_table_dl_notraf . ' ' . $this->db->sql_build_array('INSERT', [
								'user_id'	=> $this->user->data['user_id'],
								'dl_id'		=> $dl_file['id']
							]);
							$this->db->sql_query($sql);
						}
					}
				}

				if ($this->user->data['is_registered'])
				{
					$load_limit = $this->dlext_constants->get_value('overall_traffics');
					$used_traffic = 'dl_remain_traffic';
				}
				else
				{
					$load_limit = $this->dlext_constants->get_value('guests_traffics');
					$used_traffic = 'dl_remain_guest_traffic';
				}

				if (!$dl_file['extern'] && !$this->config['dl_traffic_off'] && $this->dlext_constants->get_value('founder_traffics') == $this->dlext_constants::DL_FALSE)
				{
					if ($load_limit == $this->dlext_constants::DL_TRUE)
					{
						$new_used_traffic = $this->config[$used_traffic] + $dl_file['file_size'];

						$this->config->set($used_traffic, $new_used_traffic);
					}

					$cat_traffic = $index[$cat_id]['cat_traffic'];

					if ($cat_traffic)
					{
						$sql = 'SELECT cat_traffic_use FROM ' . $this->dlext_table_dl_cat_traf . '
							WHERE cat_id = ' . (int) $cat_id;
						$result = $this->db->sql_query($sql);
						$cat_traffic_use = $this->db->sql_fetchfield('cat_traffic_use');
						$this->db->sql_freeresult($result);

						if (($cat_traffic - $cat_traffic_use) < $dl_file['file_size'])
						{
							$check_status['file_auth'] = $this->dlext_constants::DL_FALSE;
						}
						else
						{
							$cat_traffic_use += $dl_file['file_size'];

							$sql = 'UPDATE ' . $this->dlext_table_dl_cat_traf . ' SET ' . $this->db->sql_build_array('UPDATE', [
								'cat_traffic_use' => $cat_traffic_use
							]) . ' WHERE cat_id = ' . (int) $cat_id;
							$this->db->sql_query($sql);

							$this->cache->destroy('_dlext_cats');
						}
					}
				}

				if (!empty($index[$cat_id]['statistics']) && $index[$cat_id]['statistics'] && $check_status['file_auth'])
				{
					if ($index[$cat_id]['stats_prune'])
					{
						$this->dlext_main->dl_prune_stats($cat_id, $index[$cat_id]['stats_prune']);
					}

					$sql = 'INSERT INTO ' . $this->dlext_table_dl_stats . ' ' . $this->db->sql_build_array('INSERT', [
						'cat_id'		=> $cat_id,
						'id'			=> $df_id,
						'user_id'		=> $this->user->data['user_id'],
						'username'		=> $this->user->data['username'],
						'traffic'		=> $dl_file['file_size'],
						'direction'		=> 0,
						'user_ip'		=> $this->user->data['session_ip'],
						'time_stamp'	=> time()
					]);
					$this->db->sql_query($sql);
				}
			}

			/**
			 * Additional actions before really download the file / open the webpage
			 *
			 * @event oxpus.dlext.load_download_prepend
			 * @var array	dl_file			download data array
			 * @var int		df_id			download ID
			 * @var int		cat_id			download category ID
			 * @since 8.1.1
			 */
			$vars = array(
				'dl_file',
				'df_id',
				'cat_id',
			);
			extract($this->dispatcher->trigger_event('oxpus.dlext.load_download_prepend', compact($vars)));

			// Purge the files cache
			$this->cache->destroy('_dlext_cat_counts');
			$this->cache->destroy('_dlext_file_p');
			$this->cache->destroy('_dlext_file_preset');

			/*
			* Add download to favorites on user selection
			*/
			if ($this->user->data['user_dl_auto_fav'] == $this->dlext_constants::DL_AUTOADD_FAV_ALL || ($this->user->data['user_dl_auto_fav'] == $this->dlext_constants::DL_AUTOADD_FAV_SELECT && $dl_add_fav))
			{
				$sql = 'SELECT COUNT(fav_dl_id) AS total FROM ' . $this->dlext_table_dl_favorites . '
						WHERE fav_dl_id = ' . (int) $df_id . '
							AND fav_user_id = ' . (int) $this->user->data['user_id'];
				$result = $this->db->sql_query($sql);
				$fav_check = $this->db->sql_fetchfield('total');
				$this->db->sql_freeresult($result);

				if (!$fav_check)
				{
					$sql = 'INSERT INTO ' . $this->dlext_table_dl_favorites . ' ' . $this->db->sql_build_array('INSERT', array(
						'fav_dl_id'		=> $df_id,
						'fav_dl_cat'	=> $cat_id,
						'fav_user_id'	=> $this->user->data['user_id']));
					$this->db->sql_query($sql);
				}
			}

			/*
			* now it is time and we are ready to rumble: send the file to the user client to download it there!
			*/
			if ($dl_file['extern'])
			{
				$file_url = $dl_file['file_name'];

				$file_protocol_prefix = '';

				if (strpos(strtolower($file_url), 'http') !== 0)
				{
					$file_protocol_prefix = '//';
				}

				header('HTTP/1.1 301 Moved Permanently');
				header('Location: ' . $file_protocol_prefix . $file_url);

				garbage_collection();
				exit_handler();
			}
			else if ($check_status['file_auth'] && !empty($index[$cat_id]['cat_path']))
			{
				$dl_file_url = $this->dlext_constants->get_value('files_dir') . '/downloads/' . $index[$cat_id]['cat_path'];

				$this->language->add_lang('viewtopic');

				if ($dl_file['real_file'] && $this->filesystem->exists($dl_file_url . $dl_file['real_file']))
				{
					$dl_file_data = [
						'physical_file'		=> $dl_file_url . $dl_file['real_file'],
						'real_filename'		=> $dl_file['file_name'],
						'mimetype'			=> 'application/octetstream',
						'filesize'			=> sprintf('%u', filesize($dl_file_url . $dl_file['real_file'])),
						'filetime'			=> $dl_file['change_time'],
					];

					$this->dlext_physical->send_file_to_browser($dl_file_data);
				}
				else
				{
					trigger_error($this->language->lang('FILE_NOT_FOUND_404', $dl_file['file_name']));
				}
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
	}
}
