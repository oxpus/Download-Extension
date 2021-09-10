<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright (c) 2021-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\tracker;

class view
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames bug_status_count

	/* phpbb objects */
	protected $db;
	protected $config;
	protected $helper;
	protected $request;
	protected $template;
	protected $user;
	protected $language;
	protected $notification;
	protected $pagination;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_main;
	protected $dlext_footer;
	protected $dlext_constants;

	protected $dlext_table_dl_bug_history;
	protected $dlext_table_dl_tracker;
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
	 * @param \phpbb\notification\manager			$notification
	 * @param \phpbb\pagination						$pagination
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_bug_history
	 * @param string								$dlext_table_dl_tracker
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
		\phpbb\notification\manager $notification,
		\phpbb\pagination $pagination,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_bug_history,
		$dlext_table_dl_tracker,
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
		$this->notification				= $notification;
		$this->pagination				= $pagination;

		$this->dlext_table_dl_bug_history	= $dlext_table_dl_bug_history;
		$this->dlext_table_dl_tracker		= $dlext_table_dl_tracker;
		$this->dlext_table_downloads		= $dlext_table_downloads;
		$this->dlext_table_dl_cat			= $dlext_table_dl_cat;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_main				= $dlext_main;
		$this->dlext_footer				= $dlext_footer;
		$this->dlext_constants			= $dlext_constants;
	}

	public function handle()
	{
		$this->dlext_main->dl_handle_active();

		if ($this->user->data['is_registered'])
		{
			$cancel		= $this->request->variable('cancel', '');
			$action		= $this->request->variable('action', '');
			$bt_show	= $this->request->variable('bt_show', '');
			$df_id		= $this->request->variable('df_id', 0);
			$fav_id		= $this->request->variable('fav_id', 0);
			$start		= $this->request->variable('start', 0);
			$bt_filter	= $this->request->variable('bt_filter', $this->dlext_constants::DL_NONE);

			$index 		= $this->dlext_main->full_index();

			$bug_tracker = $this->dlext_auth->bug_tracker();

			if ($cancel)
			{
				$action = '';
			}

			if ($bug_tracker)
			{
				/*
				* clean up bug tracker for unset categories
				* hard stuff to do this, but we must be sure to track downloads only in the choosen categories...
				*/
				$sql = 'SELECT d.id FROM ' . $this->dlext_table_dl_cat . ' c, ' . $this->dlext_table_downloads . ' d
					WHERE c.bug_tracker = 0
						AND c.id = d.cat';
				$result = $this->db->sql_query($sql);

				$dl_ids = [];

				while ($row = $this->db->sql_fetchrow($result))
				{
					$dl_ids[] = $row['id'];
				}
				$this->db->sql_freeresult($result);

				if (isset($fav_id) && $fav_id != 0)
				{
					$sql = 'SELECT * FROM ' . $this->dlext_table_dl_tracker . '
						WHERE report_id = ' . (int) $fav_id;
					$result = $this->db->sql_query($sql);
					$dl_exists = $this->db->sql_affectedrows();
					$this->db->sql_freeresult($result);

					if (!$dl_exists)
					{
						$fav_id = 0;
						$action = '';
					}
				}

				if (!empty($dl_ids))
				{
					$sql = 'SELECT report_id FROM ' . $this->dlext_table_dl_tracker . '
							WHERE ' . $this->db->sql_in_set('df_id', $dl_ids);
					$result = $this->db->sql_query($sql);

					$item_ids = [];

					while ($row = $this->db->sql_fetchrow($result))
					{
						$item_ids[] = $row['report_id'];
					}
					$this->db->sql_freeresult($result);

					$sql = 'DELETE FROM ' . $this->dlext_table_dl_tracker . '
						WHERE ' . $this->db->sql_in_set('df_id', $dl_ids);
					$this->db->sql_query($sql);

					$sql = 'DELETE FROM ' . $this->dlext_table_dl_bug_history . '
						WHERE ' . $this->db->sql_in_set('df_id', $dl_ids);
					$this->db->sql_query($sql);

					if (!empty($item_ids))
					{
						$this->notification->delete_notifications([
							'oxpus.dlext.notification.type.bt_assign',
							'oxpus.dlext.notification.type.bt_status',
						], $item_ids);
					}

					unset($dl_ids);
					unset($item_ids);
				}

				/*
				* check the current user for mod permissions
				*/
				$bt_access_cats = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_MOD);

				if ($this->dlext_auth->user_admin() || !empty($bt_access_cats))
				{
					$allow_bug_mod = $this->dlext_constants::DL_TRUE;
				}
				else
				{
					$allow_bug_mod = $this->dlext_constants::DL_FALSE;
				}

				/*
				* check the user permissions for all download categories
				*/
				$bug_access_cats	= $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_VIEW);

				/*
				* delete bug report - only if the report really will not be a bug ;)
				*/
				if ($action == 'delete' && $df_id && $fav_id && $allow_bug_mod && !$cancel)
				{
					if (confirm_box($this->dlext_constants::DL_TRUE))
					{
						$sql = 'DELETE FROM ' . $this->dlext_table_dl_tracker . '
							WHERE report_id = ' . (int) $fav_id;
						$this->db->sql_query($sql);

						$sql = 'DELETE FROM ' . $this->dlext_table_dl_bug_history . '
							WHERE report_id = ' . (int) $fav_id;
						$this->db->sql_query($sql);

						$this->notification->delete_notifications([
							'oxpus.dlext.notification.type.bt_assign',
							'oxpus.dlext.notification.type.bt_status',
						], $fav_id);
					}
					else
					{
						$s_hidden_fields = [
							'df_id'		=> $df_id,
							'fav_id'	=> $fav_id,
							'action'	=> 'delete'
						];

						confirm_box($this->dlext_constants::DL_FALSE, $this->language->lang('DL_CONFIRM_DELETE_BUG_REPORT'), build_hidden_fields($s_hidden_fields), '@oxpus_dlext/helpers/dl_confirm_body.html');
					}

					$df_id		= 0;
					$action		= '';
				}

				if (!$action)
				{
					$bug_status_count[0] = 0;
					$bug_status_count[1] = 0;
					$bug_status_count[2] = 0;
					$bug_status_count[3] = 0;
					$bug_status_count[4] = 0;
					$bug_status_count[5] = 0;

					$sql = 'SELECT b.report_status, COUNT(b.report_id) AS total FROM ' . $this->dlext_table_dl_tracker . ' b
							LEFT JOIN ' . $this->dlext_table_downloads . ' d ON d.id = b.df_id
						WHERE ' . $this->db->sql_in_set('d.cat', $bug_access_cats) . '
						GROUP BY report_status
						ORDER BY report_status';
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						$bug_status_count[$row['report_status']] = $row['total'];
					}

					$this->db->sql_freeresult($result);

					$this->template->assign_vars([
						'S_DL_SELECT_FILTER'			=> $bt_filter,
						'S_DL_FORM_ACTION'				=> $this->helper->route('oxpus_dlext_tracker_view'),
						'S_DL_FORM_ADD_ACTION'			=> $this->helper->route('oxpus_dlext_tracker_edit', ['action' => 'add', 'df_id' => 0, 'fav_id' => 0]),
						'S_DL_FORM_FILTER_ACTION'		=> $this->helper->route('oxpus_dlext_tracker_view', ['df_id' => $df_id]),
						'S_DL_FORM_OWN_ACTION'			=> $this->helper->route('oxpus_dlext_tracker_view', ['df_id' => $df_id, 'bt_show' => 'own']),
						'S_DL_FORM_ASSIGN_ACTION'		=> $this->helper->route('oxpus_dlext_tracker_view', ['df_id' => $df_id, 'bt_show' => 'assign']),

						'U_DL_DOWNLOAD'					=> $this->helper->route('oxpus_dlext_index'),
						'U_DL_BUG_TRACKER'				=> $this->helper->route('oxpus_dlext_tracker_view'),
					]);

					$filter_ary = [
						['value' => $this->dlext_constants::DL_PERM_GENERAL_NONE,		'lang' => $this->language->lang('DL_FILTER_OPEN'),		'count' => 0],
						['value' => $this->dlext_constants::DL_REPORT_STATUS_NEW,		'lang' => $this->language->lang('DL_REPORT_STATUS_0'),	'count' => $bug_status_count[0]],
						['value' => $this->dlext_constants::DL_REPORT_STATUS_VIEWED,	'lang' => $this->language->lang('DL_REPORT_STATUS_1'),	'count' => $bug_status_count[1]],
						['value' => $this->dlext_constants::DL_REPORT_STATUS_PROGRESS,	'lang' => $this->language->lang('DL_REPORT_STATUS_2'),	'count' => $bug_status_count[2]],
						['value' => $this->dlext_constants::DL_REPORT_STATUS_PENDING,	'lang' => $this->language->lang('DL_REPORT_STATUS_3'),	'count' => $bug_status_count[3]],
						['value' => $this->dlext_constants::DL_REPORT_STATUS_FINISHED,	'lang' => $this->language->lang('DL_REPORT_STATUS_4'),	'count' => $bug_status_count[4]],
						['value' => $this->dlext_constants::DL_REPORT_STATUS_DECLINED,	'lang' => $this->language->lang('DL_REPORT_STATUS_5'),	'count' => $bug_status_count[5]],
					];

					for ($i = 0; $i < count($filter_ary); ++$i)
					{
						$this->template->assign_block_vars('dl_report_filter', [
							'DL_VALUE'	=> $filter_ary[$i]['value'],
							'DL_LANG'	=> $filter_ary[$i]['lang'],
							'DL_COUNT'	=> $filter_ary[$i]['count'],
						]);
					}

					/*
					* view bug tracker - detail overview for given download
					*/
					if ($bt_filter == $this->dlext_constants::DL_PERM_GENERAL_NONE)
					{
						$sql_where = ' AND report_status < ' . $this->dlext_constants::DL_REPORT_STATUS_PENDING;
					}
					else
					{
						$sql_where = ' AND report_status = ' . (int) $bt_filter . ' ';
					}

					if ($bt_show == 'own')
					{
						$sql_where .= ' AND report_author_id = ' . (int) $this->user->data['user_id'] . ' ';
					}
					else
					{
						$this->template->assign_var('S_DL_OWN_REPORT', $this->dlext_constants::DL_TRUE);
					}

					if ($bt_show == 'assign')
					{
						$sql_where .= ' AND report_assign_id = ' . (int) $this->user->data['user_id'] . ' ';
					}
					else
					{
						$this->template->assign_var('S_DL_ASSIGN_REPORT', $this->dlext_constants::DL_TRUE);
					}

					if ($df_id)
					{
						$sql_first_where = ' AND df_id = ' . (int) $df_id . ' ';
						$this->template->assign_var('S_DL_REPORT_TEXT', $this->dlext_constants::DL_TRUE);
					}
					else
					{
						$sql_first_where = ' AND df_id <> 0 ';
					}

					$sql = 'SELECT b.* FROM ' . $this->dlext_table_dl_tracker . ' b
						LEFT JOIN ' . $this->dlext_table_downloads . ' d ON d.id = b.df_id
						WHERE ' . $this->db->sql_in_set('d.cat', $bug_access_cats) . "
						$sql_first_where
							$sql_where";
					$result = $this->db->sql_query($sql);
					$total_reports = $this->db->sql_affectedrows();
					$this->db->sql_freeresult($result);

					if ($total_reports > $this->config['dl_links_per_page'])
					{
						$this->pagination->generate_template_pagination(
							$this->helper->route('oxpus_dlext_tracker_view', ['df_id' => $df_id]),
							'pagination',
							'start',
							$total_reports,
							$this->config['dl_links_per_page'],
							$start
						);

						$this->template->assign_vars([
							'DL_PAGE_NUMBER'	=> $this->pagination->on_page($total_reports, $this->config['dl_links_per_page'], $start),
							'DL_TOTAL_DL'		=> $this->language->lang('DL_VIEW_BUG_REPORTS', $total_reports),
						]);
					}

					if ($total_reports)
					{
						if ($df_id)
						{
							$sql_where .= " AND b.df_id = $df_id ";
						}

						$sql_array = [
							'SELECT'	=> 'b.*, d.id, d.description AS report_file, u1.username AS report_author, u1.user_colour AS report_colour, u2.username AS report_assign, u2.user_colour AS assign_colour',
							'FROM'		=> [$this->dlext_table_dl_tracker => 'b']
						];
						$sql_array['LEFT_JOIN'] = [];
						$sql_array['LEFT_JOIN'][] = [
							'FROM'		=> [$this->dlext_table_downloads => 'd'],
							'ON'		=> 'b.df_id = d.id'
						];
						$sql_array['LEFT_JOIN'][] = [
							'FROM'		=> [USERS_TABLE => 'u1'],
							'ON'		=> 'u1.user_id = b.report_author_id'
						];
						$sql_array['LEFT_JOIN'][] = [
							'FROM'		=> [USERS_TABLE => 'u2'],
							'ON'		=> 'u2.user_id = b.report_assign_id'
						];

						if ($sql_where)
						{
							$sql_array['WHERE'] = str_replace('# AND', '', '#' . (string) $sql_where);
						}

						if (isset($sql_array['WHERE']))
						{
							$sql_array['WHERE'] .= ' AND ' . $this->db->sql_in_set('d.cat', $bug_access_cats);
						}
						else
						{
							$sql_array['WHERE'] = $this->db->sql_in_set('d.cat', $bug_access_cats);
						}

						$sql_array['ORDER_BY'] = 'b.report_date DESC';

						$sql = $this->db->sql_build_query('SELECT', $sql_array);

						$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);

						$reports_num = $this->db->sql_affectedrows();
					}
					else
					{
						$reports_num = 0;
					}

					if ($reports_num)
					{
						while ($row = $this->db->sql_fetchrow($result))
						{
							$report_dl_id		= $row['id'];
							$report_id			= $row['report_id'];
							$report_title		= $row['report_title'];
							$report_text		= $row['report_text'];
							$bug_uid			= $row['bug_uid'];
							$bug_bitfield		= $row['bug_bitfield'];
							$bug_flags			= $row['bug_flags'];
							$report_text		= generate_text_for_display($report_text, $bug_uid, $bug_bitfield, $bug_flags);
							$report_file_ver	= $row['report_file_ver'];
							$report_file		= $row['report_file'];
							$report_date		= $row['report_date'];
							$report_author_id	= $row['report_author_id'];
							$report_colour		= $row['report_colour'];
							$report_assign_id	= $row['report_assign_id'];
							$report_author		= $row['report_author'];
							$report_assign		= $row['report_assign'];
							$report_assign_col	= $row['assign_colour'];
							$report_assign_date	= $row['report_assign_date'];
							$report_status		= $row['report_status'];
							$report_status_date	= $row['report_status_date'];
							$report_php			= $row['report_php'];
							$report_db			= $row['report_db'];
							$report_forum		= $row['report_forum'];

							$report_title		= censor_text($report_title);
							$report_text		= censor_text($report_text);

							$this->template->assign_block_vars('bug_tracker_row', [
								'DL_REPORT_ID'				=> $report_id,
								'DL_REPORT_TITLE'			=> $report_title,
								'DL_REPORT_TEXT'			=> $report_text,
								'DL_REPORT_DATE'			=> $this->user->format_date($report_date),
								'DL_REPORT_DATE_RFC'		=> gmdate(DATE_RFC3339, $report_date),

								'DL_REPORT_PHP'				=> $report_php,
								'DL_REPORT_DB'				=> $report_db,
								'DL_REPORT_FORUM'			=> $report_forum,

								'DL_REPORT_FILE'			=> $report_file,
								'DL_REPORT_FILE_VER'		=> $report_file_ver,
								'DL_REPORT_FILE_LINK'		=> $this->helper->route('oxpus_dlext_details', ['df_id' => $report_dl_id]),

								'DL_REPORT_AUTHOR_LINK'		=> get_username_string('full', $report_author_id, $report_author, $report_colour),

								'DL_REPORT_STATUS'				=> $this->language->lang('DL_REPORT_STATUS_' . $report_status),
								'DL_REPORT_STATUS_DATE'		=> $this->user->format_date($report_status_date),
								'DL_REPORT_STATUS_DATE_RFC'	=> gmdate(DATE_RFC3339, $report_status_date),

								'DL_REPORT_DETAIL'			=> $this->helper->route('oxpus_dlext_tracker_main', ['df_id' => $report_dl_id, 'fav_id' => $report_id]),
							]);

							if ($report_assign_id)
							{
								$this->template->assign_block_vars('bug_tracker_row.assign', [
									'DL_REPORT_ASSIGN_LINK'		=> get_username_string('full', $report_assign_id, $report_assign, $report_assign_col),
									'DL_REPORT_ASSIGN_DATE'		=> $this->user->format_date($report_assign_date),
									'DL_REPORT_ASSIGN_DATE_RFC'	=> gmdate(DATE_RFC3339, $report_assign_date),
								]);
							}
							else
							{
								$this->template->assign_var('S_DL_NO_ASSIGN', $this->dlext_constants::DL_TRUE);
							}

							if ($allow_bug_mod)
							{
								$this->template->assign_block_vars('bug_tracker_row.modext', [
									'U_DL_DELETE' => $this->helper->route('oxpus_dlext_tracker_view', ['df_id' => $report_dl_id, 'fav_id' => $report_id, 'action' => 'delete']),
								]);

								$this->template->assign_block_vars('bug_tracker_row.status_mod', [
									'U_DL_STATUS' => $this->helper->route('oxpus_dlext_tracker_main', ['df_id' => $report_dl_id, 'fav_id' => $report_id, 'action' => 'status']),
								]);
							}
							else
							{
								$this->template->assign_block_vars('bug_tracker_row.no_status_mod', []);
							}
						}

						$this->db->sql_freeresult($result);
					}
					else
					{
						$this->template->assign_var('S_DL_NO_BUG_TRACKER', $this->dlext_constants::DL_TRUE);
					}
				}

				if ($this->user->data['is_registered'])
				{
					$this->template->assign_var('S_DL_ADD_NEW_REPORT', $this->dlext_constants::DL_TRUE);

					if ($df_id)
					{
						$this->template->assign_var('S_DL_HIDDEN_FIELD', build_hidden_fields(['df_id' => $df_id]));
					}
				}

				/*
				* include the mod footer
				*/
				$this->dlext_footer->set_parameter('tracker', 0, 0, $index);
				$this->dlext_footer->handle();

				/*
				* generate page
				*/
				return $this->helper->render('@oxpus_dlext/tracker/dl_tracker_view.html', $this->language->lang('DL_BUG_TRACKER'));
			}
		}

		redirect($this->helper->route('oxpus_dlext_index'));
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
