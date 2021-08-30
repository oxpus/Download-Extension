<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class stats
{
	/* phpbb objects */
	protected $db;
	protected $config;
	protected $helper;
	protected $request;
	protected $template;
	protected $user;
	protected $language;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_footer;
	protected $dlext_constants;

	protected $dlext_table_dl_stats;
	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\user							$user
	 * @param \phpbb\language\language				$language
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_stats,
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_stats,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;

		$this->dlext_table_dl_stats		= $dlext_table_dl_stats;
		$this->dlext_table_downloads	= $dlext_table_downloads;
		$this->dlext_table_dl_cat		= $dlext_table_dl_cat;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_footer				= $dlext_footer;
		$this->dlext_constants			= $dlext_constants;
	}

	public function handle()
	{
		$this->dlext_main->dl_handle_active();

		$cat		= $this->request->variable('cat', 0);
		$index 		= ($cat) ? $this->dlext_main->index($cat) : $this->dlext_main->index();

		/*
		* check permissions and redirect if missing
		*/
		$stats_view = $this->dlext_auth->stats_perm();
		if (!$stats_view)
		{
			redirect($this->helper->route('oxpus_dlext_index'));
		}

		if (!empty($index))
		{
			$access_cats = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_VIEW);

			if (!empty($access_cats))
			{
				/*
				* enable/disable guest data on basic statistics
				*/
				$sql_where = ($this->config['dl_guest_stats_show'] == 1) ? '' : ' AND u.user_id <> ' . ANONYMOUS;

				/*
				* latest downloads
				*/
				$sql = 'SELECT d.*, u.username, u.user_colour, c.cat_name FROM ' . $this->dlext_table_downloads . ' d, ' . $this->dlext_table_dl_cat . ' c, ' . USERS_TABLE . ' u
					WHERE d.cat = c.id
						AND d.down_user = u.user_id
						AND ' . $this->db->sql_in_set('c.id', $access_cats) . "
						$sql_where
					ORDER BY d.last_time DESC";
				$result = $this->db->sql_query_limit($sql, $this->dlext_constants::DL_STATS_POS_LIMIT);
				$total_top_ten = $this->db->sql_affectedrows();

				if ($total_top_ten)
				{
					$this->template->assign_var('S_DL_LATEST_DOWNLOADS', $this->dlext_constants::DL_TRUE);

					$dl_pos = 1;

					while ($row = $this->db->sql_fetchrow($result))
					{
						$file_id		= $row['id'];
						$description	= $row['description'];
						$cat_name		= $row['cat_name'];

						$dl_time		= $this->user->format_date($row['last_time']);
						$dl_time_rfc	= gmdate(DATE_RFC3339, $row['last_time']);

						$file_link		= $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]);

						$user_link		= get_username_string('full', $row['down_user'], $row['username'], $row['user_colour']);

						$this->template->assign_block_vars('top_ten_latest', [
							'DL_POS'			=> $dl_pos,
							'DL_DESCRIPTION'	=> $description,
							'U_DL_FILE_LINK'	=> $file_link,
							'DL_CAT_NAME'		=> $cat_name,
							'DL_USER_LINK'		=> $user_link,
							'DL_TIME'			=> $dl_time,
							'DL_TIME_RFC'		=> $dl_time_rfc,
						]);

						++$dl_pos;
					}
					$this->db->sql_freeresult($result);
				}

				/*
				* lastest uploads
				*/
				$sql = 'SELECT d.*, u.username, u.user_colour, c.cat_name FROM ' . $this->dlext_table_downloads . ' d, ' . $this->dlext_table_dl_cat . ' c, ' . USERS_TABLE . ' u
					WHERE d.cat = c.id
						AND d.add_user = u.user_id
						AND approve = 1
						AND ' . $this->db->sql_in_set('c.id', $access_cats) . '
					ORDER BY d.add_time DESC';
				$result = $this->db->sql_query_limit($sql, $this->dlext_constants::DL_STATS_POS_LIMIT);
				$total_top_ten = $this->db->sql_affectedrows();

				if ($total_top_ten)
				{
					$this->template->assign_var('S_DL_LATEST_UPLOADS', $this->dlext_constants::DL_TRUE);

					$dl_pos = 1;

					while ($row = $this->db->sql_fetchrow($result))
					{
						$file_id		= $row['id'];
						$description	= $row['description'];
						$cat_name		= $row['cat_name'];

						$dl_time		= $this->user->format_date($row['add_time']);
						$dl_time_rfc	= gmdate(DATE_RFC3339, $row['add_time']);

						$file_link		= $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]);

						$user_link		= get_username_string('full', $row['add_user'], $row['username'], $row['user_colour']);

						$this->template->assign_block_vars('top_ten_uploads', [
							'DL_POS'			=> $dl_pos,
							'DL_DESCRIPTION'	=> $description,
							'U_DL_FILE_LINK'	=> $file_link,
							'DL_CAT_NAME'		=> $cat_name,
							'DL_USER_LINK'		=> $user_link,
							'DL_TIME'			=> $dl_time,
							'DL_TIME_RFC'		=> $dl_time_rfc,
						]);

						++$dl_pos;
					}
					$this->db->sql_freeresult($result);
				}

				/*
				* top ten downloads this month
				*/
				$sql = 'SELECT d.*, c.cat_name FROM ' . $this->dlext_table_downloads . ' d, ' . $this->dlext_table_dl_cat . ' c
					WHERE d.cat = c.id
						AND ' . $this->db->sql_in_set('c.id', $access_cats) . '
					ORDER BY d.klicks DESC';
				$result = $this->db->sql_query_limit($sql, $this->dlext_constants::DL_STATS_POS_LIMIT);
				$total_top_ten = $this->db->sql_affectedrows();

				if ($total_top_ten)
				{
					$this->template->assign_var('S_DL_TOP10_DOWN_MONTH', $this->dlext_constants::DL_TRUE);

					$dl_pos = 1;

					while ($row = $this->db->sql_fetchrow($result))
					{
						$file_id		= $row['id'];
						$description	= $row['description'];
						$cat_name		= $row['cat_name'];
						$dl_klicks		= $row['klicks'];

						$file_link		= $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]);

						$this->template->assign_block_vars('top_ten_dl_cur_month', [
							'DL_POS'			=> $dl_pos,
							'DL_DESCRIPTION'	=> $description,
							'U_DL_FILE_LINK'	=> $file_link,
							'DL_CAT_NAME'		=> $cat_name,
							'DL_KLICKS'		=> $dl_klicks,
						]);

						++$dl_pos;
					}
					$this->db->sql_freeresult($result);
				}

				/*
				* top ten downloads overall
				*/
				$sql = 'SELECT d.*, c.cat_name FROM ' . $this->dlext_table_downloads . ' d, ' . $this->dlext_table_dl_cat . ' c
					WHERE d.cat = c.id
						AND ' . $this->db->sql_in_set('c.id', $access_cats) . '
					ORDER BY d.overall_klicks DESC';
				$result = $this->db->sql_query_limit($sql, $this->dlext_constants::DL_STATS_POS_LIMIT);
				$total_top_ten = $this->db->sql_affectedrows();

				if ($total_top_ten)
				{
					$this->template->assign_var('S_DL_TOP10_DOWN_ALL', $this->dlext_constants::DL_TRUE);

					$dl_pos = 1;

					while ($row = $this->db->sql_fetchrow($result))
					{
						$file_id		= $row['id'];
						$description	= $row['description'];
						$cat_name		= $row['cat_name'];
						$dl_klicks		= $row['overall_klicks'];

						$file_link		= $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]);

						$this->template->assign_block_vars('top_ten_dl_overall', [
							'DL_POS'			=> $dl_pos,
							'DL_DESCRIPTION'	=> $description,
							'U_DL_FILE_LINK'	=> $file_link,
							'DL_CAT_NAME'		=> $cat_name,
							'DL_KLICKS'			=> $dl_klicks,
						]);

						++$dl_pos;
					}
					$this->db->sql_freeresult($result);
				}

				if (!$this->config['dl_traffic_off'])
				{
					/*
					* top ten traffic this month
					*/
					$sql = 'SELECT (d.klicks * d.file_size) AS month_traffic, d.*, c.cat_name FROM ' . $this->dlext_table_downloads . ' d, ' . $this->dlext_table_dl_cat . ' c
						WHERE d.cat = c.id
							AND ' . $this->db->sql_in_set('c.id', $access_cats) . '
						ORDER BY month_traffic DESC';
					$result = $this->db->sql_query_limit($sql, $this->dlext_constants::DL_STATS_POS_LIMIT);
					$total_top_ten = $this->db->sql_affectedrows();

					if ($total_top_ten)
					{
						$this->template->assign_var('S_DL_TOP10_TRAFFIC_MONTH', $this->dlext_constants::DL_TRUE);

						$dl_pos = 1;

						while ($row = $this->db->sql_fetchrow($result))
						{
							$file_id		= $row['id'];
							$description	= $row['description'];
							$cat_name		= $row['cat_name'];
							$dl_traffic		= $this->dlext_format->dl_size($row['month_traffic']);

							$file_link		= $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]);

							$this->template->assign_block_vars('top_ten_traffic_cur_month', [
								'DL_POS'			=> $dl_pos,
								'DL_DESCRIPTION'	=> $description,
								'U_DL_FILE_LINK'	=> $file_link,
								'DL_CAT_NAME'		=> $cat_name,
								'DL_TRAFFIC'		=> $dl_traffic,
							]);

							++$dl_pos;
						}
						$this->db->sql_freeresult($result);
					}

					/*
					* top ten traffic overall
					*/
					$sql = 'SELECT (d.overall_klicks * d.file_size) AS overall_traffic, d.*, c.cat_name FROM ' . $this->dlext_table_downloads . ' d, ' . $this->dlext_table_dl_cat . ' c
						WHERE d.cat = c.id
							AND ' . $this->db->sql_in_set('c.id', $access_cats) . '
						ORDER BY overall_traffic DESC';
					$result = $this->db->sql_query_limit($sql, $this->dlext_constants::DL_STATS_POS_LIMIT);
					$total_top_ten = $this->db->sql_affectedrows();

					if ($total_top_ten)
					{
						$this->template->assign_var('S_DL_TOP10_TRAFFIC_ALL', $this->dlext_constants::DL_TRUE);

						$dl_pos = 1;

						while ($row = $this->db->sql_fetchrow($result))
						{
							$file_id		= $row['id'];
							$description	= $row['description'];
							$cat_name		= $row['cat_name'];
							$dl_traffic		= $this->dlext_format->dl_size($row['overall_traffic']);

							$file_link		= $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]);

							$this->template->assign_block_vars('top_ten_traffic_overall', [
								'DL_POS'			=> $dl_pos,
								'DL_DESCRIPTION'	=> $description,
								'U_DL_FILE_LINK'	=> $file_link,
								'DL_CAT_NAME'		=> $cat_name,
								'DL_TRAFFIC'		=> $dl_traffic,
							]);

							++$dl_pos;
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
				$sql = 'SELECT COUNT(s.id) AS dl_counts, s.user_id, s.username, u.user_colour
					FROM ' . $this->dlext_table_dl_stats . ' s
					LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.user_id
					WHERE s.direction = 0
						AND ' . $this->db->sql_in_set('s.cat_id', $access_cats) . "
						$sql_where
					GROUP BY s.user_id, s.username, u.user_colour
					ORDER BY dl_counts DESC";
				$result = $this->db->sql_query_limit($sql, $this->dlext_constants::DL_STATS_POS_LIMIT);

				$total_top_ten = $this->db->sql_affectedrows();

				if ($total_top_ten)
				{
					$this->template->assign_var('S_DL_TOP10_DOWN_CLICKS', $this->dlext_constants::DL_TRUE);

					$dl_pos = 1;

					while ($row = $this->db->sql_fetchrow($result))
					{
						$user_link		= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

						$this->template->assign_block_vars('top_ten_dl_counts', [
							'DL_POS'			=> $dl_pos,
							'DL_USER_LINK'		=> $user_link,
							'DL_COUNTS'			=> $row['dl_counts'],
						]);

						++$dl_pos;
					}
					$this->db->sql_freeresult($result);
				}

				if (!$this->config['dl_traffic_off'])
				{
					/*
					* top ten download traffic
					*/
					$sql = 'SELECT SUM(s.traffic) AS dl_traffic, s.user_id, s.username, u.user_colour
						FROM ' . $this->dlext_table_dl_stats . ' s
						LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.user_id
						WHERE s.direction = 0
							AND ' . $this->db->sql_in_set('s.cat_id', $access_cats) . "
							$sql_where
						GROUP BY s.user_id, s.username, u.user_colour
						ORDER BY dl_traffic DESC";
					$result = $this->db->sql_query_limit($sql, $this->dlext_constants::DL_STATS_POS_LIMIT);
					$total_top_ten = $this->db->sql_affectedrows();

					if ($total_top_ten)
					{
						$this->template->assign_var('S_DL_TOP10_DOWN_TRAFFIC', $this->dlext_constants::DL_TRUE);

						$dl_pos = 1;

						while ($row = $this->db->sql_fetchrow($result))
						{
							$user_link		= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

							$dl_traffic	= $this->dlext_format->dl_size($row['dl_traffic']);

							$this->template->assign_block_vars('top_ten_dl_traffic', [
								'DL_POS'			=> $dl_pos,
								'DL_USER_LINK'		=> $user_link,
								'DL_TRAFFIC'		=> $dl_traffic,
							]);

							++$dl_pos;
						}
						$this->db->sql_freeresult($result);
					}
				}

				/*
				* top ten upload counts
				*/
				$sql = 'SELECT COUNT(s.id) AS dl_counts, s.user_id, s.username, u.user_colour
					FROM ' . $this->dlext_table_dl_stats . ' s
					LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.user_id
					WHERE s.direction = 1
						AND ' . $this->db->sql_in_set('s.cat_id', $access_cats) . "
						$sql_where
					GROUP BY s.user_id, s.username, u.user_colour
					ORDER BY dl_counts DESC";
				$result = $this->db->sql_query_limit($sql, $this->dlext_constants::DL_STATS_POS_LIMIT);
				$total_top_ten = $this->db->sql_affectedrows();

				if ($total_top_ten)
				{
					$this->template->assign_var('S_DL_TOP10_UP_COUNT', $this->dlext_constants::DL_TRUE);

					$dl_pos = 1;

					while ($row = $this->db->sql_fetchrow($result))
					{
						$user_link		= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

						$this->template->assign_block_vars('top_ten_up_counts', [
							'DL_POS'			=> $dl_pos,
							'DL_USER_LINK'		=> $user_link,
							'DL_COUNTS'			=> $row['dl_counts'],
						]);

						++$dl_pos;
					}
					$this->db->sql_freeresult($result);
				}

				if (!$this->config['dl_traffic_off'])
				{
					/*
					* top ten upload traffic
					*/
					$sql = 'SELECT SUM(s.traffic) AS dl_traffic, s.user_id, s.username, u.user_colour
						FROM ' . $this->dlext_table_dl_stats . ' s
						LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.user_id
						WHERE s.direction = 1
							AND ' . $this->db->sql_in_set('s.cat_id', $access_cats) . "
							$sql_where
						GROUP BY s.user_id, s.username, u.user_colour
						ORDER BY dl_traffic DESC";
					$result = $this->db->sql_query_limit($sql, $this->dlext_constants::DL_STATS_POS_LIMIT);
					$total_top_ten = $this->db->sql_affectedrows();

					if ($total_top_ten)
					{
						$this->template->assign_var('S_DL_TOP10_UP_TRAFFIC', $this->dlext_constants::DL_TRUE);

						$dl_pos = 1;

						while ($row = $this->db->sql_fetchrow($result))
						{
							$user_link		= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

							$dl_traffic	= $this->dlext_format->dl_size($row['dl_traffic']);

							$this->template->assign_block_vars('top_ten_up_traffic', [
								'DL_POS'			=> $dl_pos,
								'DL_USER_LINK'		=> $user_link,
								'DL_TRAFFIC'		=> $dl_traffic,
							]);

							++$dl_pos;
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

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter('stats', 0, 0, $index);
		$this->dlext_footer->handle();

		/*
		* generate page
		*/
		return $this->helper->render('@oxpus_dlext/dl_stat_body.html', $this->language->lang('DL_STATS'));
	}
}
