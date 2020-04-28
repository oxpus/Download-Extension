<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller;

use Symfony\Component\DependencyInjection\Container;

class stats
{
	/* @var string phpBB root path */
	protected $root_path;

	/* @var string phpEx */
	protected $php_ext;

	/* @var Container */
	protected $phpbb_container;

	/* @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/* @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\language\language */
	protected $language;

	/** @var extension owned objects */
	protected $ext_path;
	protected $ext_path_web;
	protected $ext_path_ajax;

	protected $dlext_auth;
	protected $dlext_format;
	protected $dlext_main;

	/**
	* Constructor
	*
	* @param string									$root_path
	* @param string									$php_ext
	* @param Container 								$phpbb_container
	* @param \phpbb\extension\manager				$phpbb_extension_manager
	* @param \phpbb\path_helper						$phpbb_path_helper
	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param \phpbb\config\config					$config
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\request\request_interface 		$request
	* @param \phpbb\template\template				$template
	* @param \phpbb\user							$user
	* @param \phpbb\language\language				$language
	*/
	public function __construct(
		$root_path,
		$php_ext,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\auth\auth $auth,
		\phpbb\request\request_interface $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		$dlext_auth,
		$dlext_format,
		$dlext_main
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->phpbb_container 			= $phpbb_container;
		$this->phpbb_extension_manager 	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->auth						= $auth;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
	}

	public function handle()
	{
		$nav_view = 'stats';

		// Include the default base init script
		include_once($this->ext_path . 'phpbb/includes/base_init.' . $this->php_ext);

		/*
		* getting some stats
		*/
		$inc_module = true;
		page_header($this->language->lang('DL_STATS'));

		/*
		* check permissions and redirect if missing
		*/
		$stats_view = $this->dlext_auth->stats_perm();
		if (!$stats_view)
		{
			redirect($this->helper->route('oxpus_dlext_index'));
		}

		if (sizeof($index))
		{
			$access_cats = array();
			$access_cats = $this->dlext_main->full_index(0, 0, 0, 1);

			if (sizeof($access_cats))
			{
				/*
				* enable/disable guest data on basic statistics
				*/
				$sql_where = ($this->config['dl_guest_stats_show'] == 1) ? '' : ' AND u.user_id <> ' . ANONYMOUS;

				/*
				* latest downloads
				*/
				$sql = 'SELECT d.*, u.username, u.user_colour, c.cat_name FROM ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . ' c, ' . USERS_TABLE . ' u
					WHERE d.cat = c.id
						AND d.down_user = u.user_id
						AND ' . $this->db->sql_in_set('c.id', $access_cats) . "
						$sql_where
					ORDER BY d.last_time DESC";
				$result = $this->db->sql_query_limit($sql, 10);
				$total_top_ten = $this->db->sql_affectedrows($result);

				if ($total_top_ten)
				{
					$this->template->assign_var('S_LATEST_DOWNLOADS', true);

					$dl_pos = 1;

					while ($row = $this->db->sql_fetchrow($result))
					{
						$file_id		= $row['id'];
						$cat_id			= $row['cat'];
						$file_name_name	= $row['file_name'];
						$description	= $row['description'];
						$cat_name		= $row['cat_name'];

						$dl_time		= $this->user->format_date($row['last_time']);
						$dl_time_rfc	= gmdate(DATE_RFC3339, $row['last_time']);

						$file_link		= $this->helper->route('oxpus_dlext_details', array('df_id' => $file_id));

						$user_link		= get_username_string('full', $row['down_user'], $row['username'], $row['user_colour']);

						$this->template->assign_block_vars('top_ten_latest', array(
							'POS'			=> $dl_pos,
							'DESCRIPTION'	=> $description,
							'U_FILE_LINK'	=> $file_link,
							'CAT_NAME'		=> $cat_name,
							'USER_LINK'		=> $user_link,
							'DL_TIME'		=> $dl_time,
							'DL_TIME_RFC'	=> $dl_time_rfc)
						);

						$dl_pos++;
					}
					$this->db->sql_freeresult($result);
				}

				/*
				* lastest uploads
				*/
				$sql = 'SELECT d.*, u.username, u.user_colour, c.cat_name FROM ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . ' c, ' . USERS_TABLE . ' u
					WHERE d.cat = c.id
						AND d.add_user = u.user_id
						AND approve = ' . true . '
						AND ' . $this->db->sql_in_set('c.id', $access_cats) . '
					ORDER BY d.add_time DESC';
				$result = $this->db->sql_query_limit($sql, 10);
				$total_top_ten = $this->db->sql_affectedrows($result);

				if ($total_top_ten)
				{
					$this->template->assign_var('S_LATEST_UPLOADS', true);

					$dl_pos = 1;

					while ($row = $this->db->sql_fetchrow($result))
					{
						$file_id		= $row['id'];
						$cat_id			= $row['cat'];
						$file_name_name	= $row['file_name'];
						$description	= $row['description'];
						$cat_name		= $row['cat_name'];

						$dl_time		= $this->user->format_date($row['add_time']);
						$dl_time_rfc	= gmdate(DATE_RFC3339, $row['add_time']);

						$file_link		= $this->helper->route('oxpus_dlext_details', array('df_id' => $file_id));

						$user_link		= get_username_string('full', $row['add_user'], $row['username'], $row['user_colour']);

						$this->template->assign_block_vars('top_ten_uploads', array(
							'POS'			=> $dl_pos,
							'DESCRIPTION'	=> $description,
							'U_FILE_LINK'	=> $file_link,
							'CAT_NAME'		=> $cat_name,
							'USER_LINK'		=> $user_link,
							'DL_TIME'		=> $dl_time,
							'DL_TIME_RFC'	=> $dl_time_rfc)
						);

						$dl_pos++;
					}
					$this->db->sql_freeresult($result);
				}

				/*
				* top ten downloads this month
				*/
				$sql = 'SELECT d.*, c.cat_name FROM ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . ' c
					WHERE d.cat = c.id
						AND ' . $this->db->sql_in_set('c.id', $access_cats) . '
					ORDER BY d.klicks DESC';
				$result = $this->db->sql_query_limit($sql, 10);
				$total_top_ten = $this->db->sql_affectedrows($result);

				if ($total_top_ten)
				{
					$this->template->assign_var('S_TOP10_DOWN_MONTH', true);

					$dl_pos = 1;

					while ($row = $this->db->sql_fetchrow($result))
					{
						$file_id		= $row['id'];
						$cat_id			= $row['cat'];
						$file_name_name	= $row['file_name'];
						$description	= $row['description'];
						$cat_name		= $row['cat_name'];
						$dl_klicks		= $row['klicks'];

						$file_link		= $this->helper->route('oxpus_dlext_details', array('df_id' => $file_id));

						$this->template->assign_block_vars('top_ten_dl_cur_month', array(
							'POS'			=> $dl_pos,
							'DESCRIPTION'	=> $description,
							'U_FILE_LINK'	=> $file_link,
							'CAT_NAME'		=> $cat_name,
							'DL_KLICKS'		=> $dl_klicks)
						);

						$dl_pos++;
					}
					$this->db->sql_freeresult($result);
				}

				/*
				* top ten downloads overall
				*/
				$sql = 'SELECT d.*, c.cat_name FROM ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . ' c
					WHERE d.cat = c.id
						AND ' . $this->db->sql_in_set('c.id', $access_cats) . '
					ORDER BY d.overall_klicks DESC';
				$result = $this->db->sql_query_limit($sql, 10);
				$total_top_ten = $this->db->sql_affectedrows($result);

				if ($total_top_ten)
				{
					$this->template->assign_var('S_TOP10_DOWN_ALL', true);

					$dl_pos = 1;

					while ($row = $this->db->sql_fetchrow($result))
					{
						$file_id		= $row['id'];
						$cat_id			= $row['cat'];
						$file_name_name	= $row['file_name'];
						$description	= $row['description'];
						$cat_name		= $row['cat_name'];
						$dl_klicks		= $row['overall_klicks'];

						$file_link		= $this->helper->route('oxpus_dlext_details', array('df_id' => $file_id));

						$this->template->assign_block_vars('top_ten_dl_overall', array(
							'POS'			=> $dl_pos,
							'DESCRIPTION'	=> $description,
							'U_FILE_LINK'	=> $file_link,
							'CAT_NAME'		=> $cat_name,
							'DL_KLICKS'		=> $dl_klicks)
						);

						$dl_pos++;
					}
					$this->db->sql_freeresult($result);
				}

				if (!$this->config['dl_traffic_off'])
				{
					/*
					* top ten traffic this month
					*/
					$sql = 'SELECT (d.klicks * d.file_size) AS month_traffic, d.*, c.cat_name FROM ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . ' c
						WHERE d.cat = c.id
							AND ' . $this->db->sql_in_set('c.id', $access_cats) . '
						ORDER BY month_traffic DESC';
					$result = $this->db->sql_query_limit($sql, 10);
					$total_top_ten = $this->db->sql_affectedrows($result);

					if ($total_top_ten)
					{
						$this->template->assign_var('S_TOP10_TRAFFIC_MONTH', true);

						$dl_pos = 1;

						while ($row = $this->db->sql_fetchrow($result))
						{
							$file_id		= $row['id'];
							$cat_id			= $row['cat'];
							$file_name_name	= $row['file_name'];
							$description	= $row['description'];
							$cat_name		= $row['cat_name'];
							$dl_traffic		= $this->dlext_format->dl_size($row['month_traffic']);

							$file_link		= $this->helper->route('oxpus_dlext_details', array('df_id' => $file_id));

							$this->template->assign_block_vars('top_ten_traffic_cur_month', array(
								'POS'			=> $dl_pos,
								'DESCRIPTION'	=> $description,
								'U_FILE_LINK'	=> $file_link,
								'CAT_NAME'		=> $cat_name,
								'DL_TRAFFIC'	=> $dl_traffic,)
							);

							$dl_pos++;
						}
						$this->db->sql_freeresult($result);
					}

					/*
					* top ten traffic overall
					*/
					$sql = 'SELECT (d.overall_klicks * d.file_size) AS overall_traffic, d.*, c.cat_name FROM ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . ' c
						WHERE d.cat = c.id
							AND ' . $this->db->sql_in_set('c.id', $access_cats) . '
						ORDER BY overall_traffic DESC';
					$result = $this->db->sql_query_limit($sql, 10);
					$total_top_ten = $this->db->sql_affectedrows($result);

					if ($total_top_ten)
					{
						$this->template->assign_var('S_TOP10_TRAFFIC_ALL', true);

						$dl_pos = 1;

						while ($row = $this->db->sql_fetchrow($result))
						{
							$file_id		= $row['id'];
							$cat_id			= $row['cat'];
							$file_name_name	= $row['file_name'];
							$description	= $row['description'];
							$cat_name		= $row['cat_name'];
							$dl_traffic		= $this->dlext_format->dl_size($row['overall_traffic']);

							$file_link		= $this->helper->route('oxpus_dlext_details', array('df_id' => $file_id));

							$this->template->assign_block_vars('top_ten_traffic_overall', array(
								'POS'			=> $dl_pos,
								'DESCRIPTION'	=> $description,
								'U_FILE_LINK'	=> $file_link,
								'CAT_NAME'		=> $cat_name,
								'DL_TRAFFIC'	=> $dl_traffic)
							);

							$dl_pos++;
						}
						$this->db->sql_freeresult($result);
					}
				}

				/*
				* enable/disable guest data on extended statistics
				*/
				$sql_where = ($this->config['dl_guest_stats_show'] == 1) ? '' : ' AND s.user_id <> ' . ANONYMOUS;

				/*
				* top ten download counts
				*/
				unset($sql_array);

				$sql = 'SELECT COUNT(s.id) AS dl_counts, s.user_id, s.username, u.user_colour
					FROM ' . DL_STATS_TABLE . ' s
					LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.user_id
					WHERE s.direction = 0
						AND ' . $this->db->sql_in_set('s.cat_id', $access_cats) . "
						$sql_where
					GROUP BY s.user_id, s.username, u.user_colour
					ORDER BY dl_counts DESC";
				$result = $this->db->sql_query_limit($sql, 10);

				$total_top_ten = $this->db->sql_affectedrows($result);

				if ($total_top_ten)
				{
					$this->template->assign_var('S_TOP10_DOWN_CLICKS', true);

					$dl_pos = 1;

					while ($row = $this->db->sql_fetchrow($result))
					{
						$user_link		= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

						$this->template->assign_block_vars('top_ten_dl_counts', array(
							'POS'			=> $dl_pos,
							'USER_LINK'		=> $user_link,
							'DL_COUNTS'		=> $row['dl_counts'])
						);

						$dl_pos++;
					}
					$this->db->sql_freeresult($result);
				}

				if (!$this->config['dl_traffic_off'])
				{
					/*
					* top ten download traffic
					*/
					unset($sql_array);

					$sql = 'SELECT SUM(s.traffic) AS dl_traffic, s.user_id, s.username, u.user_colour
						FROM ' . DL_STATS_TABLE . ' s
						LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.user_id
						WHERE s.direction = 0
							AND ' . $this->db->sql_in_set('s.cat_id', $access_cats) . "
							$sql_where
						GROUP BY s.user_id, s.username, u.user_colour
						ORDER BY dl_traffic DESC";
					$result = $this->db->sql_query_limit($sql, 10);
					$total_top_ten = $this->db->sql_affectedrows($result);

					if ($total_top_ten)
					{
						$this->template->assign_var('S_TOP10_DOWN_TRAFFIC', true);

						$dl_pos = 1;

						while ($row = $this->db->sql_fetchrow($result))
						{
							$user_link		= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

							$dl_traffic	= $this->dlext_format->dl_size($row['dl_traffic']);

							$this->template->assign_block_vars('top_ten_dl_traffic', array(
								'POS'			=> $dl_pos,
								'USER_LINK'		=> $user_link,
								'DL_TRAFFIC'	=> $dl_traffic)
							);

							$dl_pos++;
						}
						$this->db->sql_freeresult($result);
					}
				}

				/*
				* top ten upload counts
				*/
				unset($sql_array);

				$sql = 'SELECT COUNT(s.id) AS dl_counts, s.user_id, s.username, u.user_colour
					FROM ' . DL_STATS_TABLE . ' s
					LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.user_id
					WHERE s.direction = 1
						AND ' . $this->db->sql_in_set('s.cat_id', $access_cats) . "
						$sql_where
					GROUP BY s.user_id, s.username, u.user_colour
					ORDER BY dl_counts DESC";
				$result = $this->db->sql_query_limit($sql, 10);
				$total_top_ten = $this->db->sql_affectedrows($result);

				if ($total_top_ten)
				{
					$this->template->assign_var('S_TOP10_UP_COUNT', true);

					$dl_pos = 1;

					while ($row = $this->db->sql_fetchrow($result))
					{
						$user_link		= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

						$this->template->assign_block_vars('top_ten_up_counts', array(
							'POS'			=> $dl_pos,
							'USER_LINK'		=> $user_link,
							'DL_COUNTS'		=> $row['dl_counts'])
						);

						$dl_pos++;
					}
					$this->db->sql_freeresult($result);
				}

				if (!$this->config['dl_traffic_off'])
				{
					/*
					* top ten upload traffic
					*/
					unset($sql_array);

					$sql = 'SELECT SUM(s.traffic) AS dl_traffic, s.user_id, s.username, u.user_colour
						FROM ' . DL_STATS_TABLE . ' s
						LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.user_id
						WHERE s.direction = 1
							AND ' . $this->db->sql_in_set('s.cat_id', $access_cats) . "
							$sql_where
						GROUP BY s.user_id, s.username, u.user_colour
						ORDER BY dl_traffic DESC";
					$result = $this->db->sql_query_limit($sql, 10);
					$total_top_ten = $this->db->sql_affectedrows($result);

					if ($total_top_ten)
					{
						$this->template->assign_var('S_TOP10_UP_TRAFFIC', true);

						$dl_pos = 1;

						while ($row = $this->db->sql_fetchrow($result))
						{
							$user_link		= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

							$dl_traffic	= $this->dlext_format->dl_size($row['dl_traffic']);

							$this->template->assign_block_vars('top_ten_up_traffic', array(
								'POS'			=> $dl_pos,
								'USER_LINK'	=> $user_link,
								'DL_TRAFFIC'	=> $dl_traffic)
							);

							$dl_pos++;
						}
						$this->db->sql_freeresult($result);
					}
				}
			}
			else
			{
				redirect($this->helper->route('oxpus_dlext_index'));
			}
		}
		else
		{
			redirect($this->helper->route('oxpus_dlext_index'));
		}

		$this->template->set_filenames(array(
			'body' => 'dl_stat_body.html')
		);

		/*
		* include the mod footer
		*/
		$dl_footer = $this->phpbb_container->get('oxpus.dlext.footer');
		$dl_footer->set_parameter($nav_view, 0, 0, $index);
		$dl_footer->handle();
	}
}
