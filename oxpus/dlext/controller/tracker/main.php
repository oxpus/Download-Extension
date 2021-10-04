<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright (c) 2021-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\tracker;

class main
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames error_txt

	/* phpbb objects */
	protected $root_path;
	protected $php_ext;
	protected $extension_manager;
	protected $db;
	protected $helper;
	protected $request;
	protected $template;
	protected $user;
	protected $language;
	protected $notification;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_main;
	protected $dlext_footer;
	protected $dlext_constants;

	protected $dlext_table_dl_auth;
	protected $dlext_table_dl_bug_history;
	protected $dlext_table_dl_tracker;
	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param string								$php_ext
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\user							$user
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\notification\manager			$notification
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_auth
	 * @param string								$dlext_table_dl_bug_history
	 * @param string								$dlext_table_dl_tracker
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		$root_path,
		$php_ext,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\notification\manager $notification,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_auth,
		$dlext_table_dl_bug_history,
		$dlext_table_dl_tracker,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->db 						= $db;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;
		$this->notification				= $notification;

		$this->dlext_table_dl_auth			= $dlext_table_dl_auth;
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
			$action		= $this->request->variable('action', 'detail');
			$df_id		= $this->request->variable('df_id', 0);
			$fav_id		= $this->request->variable('fav_id', 0);

			$index 		= $this->dlext_main->full_index();

			$bug_tracker = $this->dlext_auth->bug_tracker();

			if ($cancel)
			{
				$action = 'detail';
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

				$new_user_id		= $this->request->variable('user_assign', 0);

				$error = $this->dlext_constants::DL_FALSE;

				if ($action == 'status' || $action == 'assign')
				{
					if (!check_form_key('bt_tracker'))
					{
						$error_txt[] = $this->language->lang('FORM_INVALID');
						$error = $this->dlext_constants::DL_TRUE;
					}

					if ($action == 'assign' && !$new_user_id)
					{
						$error_txt[] = $this->language->lang('DL_NO_PERMISSIONS');
						$error = $this->dlext_constants::DL_TRUE;
					}
				}

				if ($error)
				{
					if ($fav_id)
					{
						$action = 'edit';
					}
					else
					{
						$action = 'add';
					}
				}

				/*
				* add new status to report
				*/
				if (!$error && $action == 'status' && $fav_id && $allow_bug_mod)
				{
					$new_status			= $this->request->variable('new_status', '', $this->dlext_constants::DL_TRUE);
					$new_status_text	= $this->request->variable('new_status_text', '', $this->dlext_constants::DL_TRUE);
					$new_status_text	= str_replace(':', '', $new_status_text);

					$sql = 'SELECT b.df_id, b.report_status, b.report_author_id, b.report_title FROM ' . $this->dlext_table_dl_tracker . ' b, ' . $this->dlext_table_downloads . ' d
						WHERE d.id = b.df_id
							AND b.report_id = ' . (int) $fav_id;
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);

					$df_id				= $row['df_id'];
					$report_status		= $row['report_status'];
					$report_author_id	= $row['report_author_id'];
					$report_title		= $row['report_title'];

					$this->db->sql_freeresult($result);

					$sql = 'INSERT INTO ' . $this->dlext_table_dl_bug_history . ' ' . $this->db->sql_build_array('INSERT', [
						'df_id'					=> $df_id,
						'report_id'				=> $fav_id,
						'report_his_type'		=> 'status',
						'report_his_date'		=> time(),
						'report_his_status'		=> $new_status,
						'report_his_value'		=> $new_status_text,
						'report_his_user_id'	=> $this->user->data['user_id'],
					]);
					$this->db->sql_query($sql);

					$sql = 'UPDATE ' . $this->dlext_table_dl_tracker . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'report_status'			=> $new_status,
						'report_status_date'	=> time()
					]) . ' WHERE report_id = ' . (int) $fav_id;
					$this->db->sql_query($sql);

					// Send email to report author about new status if it will not be the current one
					if ($report_author_id != $this->user->data['user_id'])
					{
						$notification_data = [
							'user_ids'			=> [$report_author_id],
							'status_text'		=> $new_status_text,
							'report_title'		=> $report_title,
							'report_status'		=> $report_status,
							'fav_id'			=> $fav_id,
						];

						$this->notification->add_notifications('oxpus.dlext.notification.type.bt_status', $notification_data);
					}

					$action = 'detail';
				}

				/*
				* assign bug report to team member
				*/
				if (!$error && $action == 'assign' && $df_id && $fav_id && $allow_bug_mod)
				{
					$sql = 'SELECT b.report_title, d.cat FROM ' . $this->dlext_table_dl_tracker . ' b, ' . $this->dlext_table_downloads . ' d
						WHERE d.id = b.df_id
						AND b.report_id = ' . (int) $fav_id;
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);

					$report_title	= $row['report_title'];

					$this->db->sql_freeresult($result);

					$sql = 'INSERT INTO ' . $this->dlext_table_dl_bug_history . ' ' . $this->db->sql_build_array('INSERT', [
						'df_id'					=> $df_id,
						'report_id'				=> $fav_id,
						'report_his_type'		=> 'assign',
						'report_his_date'		=> time(),
						'report_his_value'		=> '',
						'report_his_status'		=> $this->dlext_constants::DL_REPORT_STATUS_NEW,
						'report_his_user_id'	=> $new_user_id,
					]);
					$this->db->sql_query($sql);

					$sql = 'UPDATE ' . $this->dlext_table_dl_tracker . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'report_assign_id'		=> $new_user_id,
						'report_assign_date'	=> time()
					]) . ' WHERE report_id = ' . (int) $fav_id;
					$this->db->sql_query($sql);

					// Send notification to new assigned user if it will not be the current one
					if ($new_user_id != $this->user->data['user_id'])
					{
						$notification_data = [
							'user_ids'		=> [$new_user_id],
							'fav_id'		=> $fav_id,
							'report_title'	=> $report_title,
						];

						$this->notification->add_notifications('oxpus.dlext.notification.type.bt_assign', $notification_data);
					}

					$action = 'detail';
				}

				/*
				* view current details from bug report
				*/
				if (!$error && $action == 'detail' && $fav_id)
				{
					$sql_array = [
						'SELECT'	=> 'b.*, d.description AS report_file, u1.username AS report_author, u1.user_colour AS report_colour, u2.username AS report_assign, u2.user_colour AS report_assign_col',
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

					$sql_array['WHERE'] = 'b.report_id = ' . (int) $fav_id . ' AND ' . $this->db->sql_in_set('d.cat', $bug_access_cats);

					$sql = $this->db->sql_build_query('SELECT', $sql_array);
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);

					$report_id			= $fav_id;
					$report_file_id		= $row['df_id'];
					$report_file		= $row['report_file'];
					$report_title		= $row['report_title'];
					$report_text		= $row['report_text'];
					$bug_uid			= $row['bug_uid'];
					$bug_bitfield		= $row['bug_bitfield'];
					$bug_flags			= $row['bug_flags'];
					$report_text		= generate_text_for_display($report_text, $bug_uid, $bug_bitfield, $bug_flags);
					$report_file_ver	= $row['report_file_ver'];
					$report_date		= $row['report_date'];
					$report_author_id	= $row['report_author_id'];
					$report_assign_id	= $row['report_assign_id'];
					$report_assign_date	= $row['report_assign_date'];
					$report_status		= $row['report_status'];
					$report_status_date	= $row['report_status_date'];
					$report_php			= $row['report_php'];
					$report_db			= $row['report_db'];
					$report_forum		= $row['report_forum'];
					$report_author		= $row['report_author'];
					$report_author_col	= $row['report_colour'];
					$report_assign		= $row['report_assign'];
					$report_assign_col	= $row['report_assign_col'];

					$this->db->sql_freeresult($result);

					// Change status in the report was new and a team member will open the details
					if (!$report_status && $allow_bug_mod)
					{
						$sql = 'INSERT INTO ' . $this->dlext_table_dl_bug_history . ' ' . $this->db->sql_build_array('INSERT', [
							'df_id'					=> $report_file_id,
							'report_id'				=> $report_id,
							'report_his_type'		=> 'status',
							'report_his_date'		=> time(),
							'report_his_status'		=> $this->dlext_constants::DL_REPORT_STATUS_VIEWED,
							'report_his_value'		=> '',
							'report_his_user_id'	=> $this->user->data['user_id'],
						]);
						$this->db->sql_query($sql);

						$report_status = 1;
						$report_status_date = time();

						$sql = 'UPDATE ' . $this->dlext_table_dl_tracker . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'report_status'			=> $report_status,
							'report_status_date'	=> $report_status_date
						]) . ' WHERE report_id = ' . (int) $report_id;
						$this->db->sql_query($sql);
					}

					$u_report_file_link		= $this->helper->route('oxpus_dlext_details', ['df_id' => $report_file_id]);
					$report_author_link		= get_username_string('full', $report_author_id, $report_author, $report_author_col);

					if ($report_assign_id)
					{
						$this->template->assign_block_vars('assign', [
							'DL_ASSIGN_TO'			=> get_username_string('full', $report_assign_id, $report_assign, $report_assign_col),
							'DL_ASSIGN_DATE'		=> $this->user->format_date($report_assign_date),
							'DL_ASSIGN_DATE_RFC'	=> gmdate(DATE_RFC3339, $report_assign_date),
							'U_DL_ASSIGN_TO'		=> append_sid($this->root_path . 'memberlist.' . $this->php_ext, "mode=viewprofile&amp;u=$report_assign_id"),
						]);
					}
					else
					{
						$this->template->assign_var('S_DL_NO_ASSIGN', $this->dlext_constants::DL_TRUE);
					}

					$report_date 		= $this->user->format_date($report_date);
					$report_date_rfc	= gmdate(DATE_RFC3339, $row['report_date']);

					$report_title	= censor_text($report_title);
					$report_text	= censor_text($report_text);

					$this->template->assign_vars([
						'DL_REPORT_ID'			=> $report_id,
						'DL_REPORT_FILE'		=> $report_file,
						'DL_REPORT_TITLE'		=> $report_title,
						'DL_REPORT_TEXT'		=> $report_text,
						'DL_REPORT_FILE_VER'	=> $report_file_ver,
						'DL_REPORT_DATE'		=> $report_date,
						'DL_REPORT_DATE_RFC'	=> $report_date_rfc,
						'DL_REPORT_PHP'			=> $report_php,
						'DL_REPORT_DB'			=> $report_db,
						'DL_REPORT_FORUM'		=> $report_forum,
						'DL_REPORT_AUTHOR'		=> $report_author_link,
						'DL_REPORT_STATUS'		=> $this->language->lang('DL_REPORT_STATUS_' . $report_status),
						'DL_STATUS_DATE'		=> $this->user->format_date($report_status_date),
						'DL_STATUS_DATE_RFC'	=> gmdate(DATE_RFC3339, $row['report_status_date']),

						'U_DL_DOWNLOAD_FILE'	=> $u_report_file_link,
						'U_DL_DOWNLOAD'			=> $this->helper->route('oxpus_dlext_index'),
						'U_DL_BUG_TRACKER'		=> $this->helper->route('oxpus_dlext_tracker_view'),
					]);

					// Begin report history
					$sql = 'SELECT *, username, user_colour FROM ' . $this->dlext_table_dl_bug_history . ', ' . USERS_TABLE . '
						WHERE report_id = ' . (int) $fav_id . '
							AND user_id = report_his_user_id
						ORDER BY report_his_id DESC';
					$result = $this->db->sql_query($sql);

					$total_history = $this->db->sql_affectedrows();

					if ($total_history)
					{
						$this->template->assign_var('S_DL_HISTORY', $this->dlext_constants::DL_TRUE);

						while ($row = $this->db->sql_fetchrow($result))
						{
							$report_his_type	= $row['report_his_type'];
							$report_his_value	= $row['report_his_value'];
							$report_his_user	= get_username_string('full', $row['report_his_user_id'], $row['username'], $row['user_colour']);
							$report_his_status	= $row['report_his_status'];

							$output_date		= $this->user->format_date($row['report_his_date']);
							$output_date_rfc	= gmdate(DATE_RFC3339, $row['report_his_date']);

							if ($report_his_type == 'assign')
							{
								$output_value = $this->language->lang('DL_BUG_REPORT_ASSIGN');
								$output_text = $this->language->lang('DL_BUG_REPORT_ASSIGNED');
							}
							else if ($report_his_type == 'status')
							{
								$output_value = $this->language->lang('DL_BUG_REPORT_STATUS');
								$output_text = $this->language->lang('DL_REPORT_STATUS_' . $report_his_status);
							}

							$this->template->assign_block_vars('history_row', [
								'DL_VALUE'		=> $output_value,
								'DL_DATE'		=> $output_date,
								'DL_DATE_RFC'	=> $output_date_rfc,
								'DL_TEXT'		=> $output_text,
								'DL_TEXT_VALUE'	=> $report_his_value,
								'DL_USER'		=> $report_his_user,
							]);
						}
					}

					$this->db->sql_freeresult($result);

					if ($allow_bug_mod)
					{
						$this->template->assign_var('U_DL_REPORT_DELETE', $this->helper->route('oxpus_dlext_tracker_main', ['df_id' => $report_file_id, 'fav_id' => $report_id, 'action' => 'delete']));

						if ($report_status < 4)
						{
							$sql = 'SELECT ug.user_id FROM ' . $this->dlext_table_dl_auth . ' dl, ' . USER_GROUP_TABLE . ' ug
								WHERE dl.auth_mod = 1
									AND dl.group_id = ug.group_id
									AND ug.user_pending <> 1
								GROUP BY ug.user_id';
							$result = $this->db->sql_query($sql);

							$user_ids = [0];

							while ($row = $this->db->sql_fetchrow($result))
							{
								$user_ids[] = $row['user_id'];
							}
							$this->db->sql_freeresult($result);

							// Codeblock to assign the report to a team member
							$sql = 'SELECT user_id, username_clean FROM ' . USERS_TABLE . '
								WHERE ((' . $this->db->sql_in_set('user_id', $user_ids) . '
									AND user_id <> ' . ANONYMOUS . ')
									OR user_type = ' . USER_FOUNDER . ')
									AND user_id <> ' . (int) $report_assign_id . '
								ORDER BY username';
							$result = $this->db->sql_query($sql);

							if ($this->db->sql_affectedrows())
							{
								$this->template->assign_var('S_DL_ASSIGN_MOD', $this->dlext_constants::DL_TRUE);

								while ($row = $this->db->sql_fetchrow($result))
								{
									$this->template->assign_block_vars('assign_users', [
										'DL_USER_ID'	=> $row['user_id'],
										'DL_USER_NAME'	=> $row['username_clean'],
									]);
								}

								$this->template->assign_vars([
									'S_DL_FORM_ASSIGN_ACTION' => $this->helper->route('oxpus_dlext_tracker_main', ['action' => 'assign', 'df_id' => $report_file_id, 'fav_id' => $fav_id]),
									'S_DL_SELECT_ASSIGNED_USER' => $this->user->data['user_id'],
								]);
							}

							$this->db->sql_freeresult($result);
						}

						// Create status select
						$s_select_status = [];

						switch ($report_status)
						{
							case $this->dlext_constants::DL_REPORT_STATUS_NEW:
							case $this->dlext_constants::DL_REPORT_STATUS_VIEWED:
							case $this->dlext_constants::DL_REPORT_STATUS_PROGRESS:
							case $this->dlext_constants::DL_REPORT_STATUS_PENDING:
								$s_select_status[] = ['value' => $this->dlext_constants::DL_REPORT_STATUS_PROGRESS, 'lang' => $this->language->lang('DL_REPORT_STATUS_2')];
								$s_select_status[] = ['value' => $this->dlext_constants::DL_REPORT_STATUS_PENDING, 'lang' => $this->language->lang('DL_REPORT_STATUS_3')];
								$s_select_status[] = ['value' => $this->dlext_constants::DL_REPORT_STATUS_FINISHED, 'lang' => $this->language->lang('DL_REPORT_STATUS_4')];
								$s_select_status[] = ['value' => $this->dlext_constants::DL_REPORT_STATUS_DECLINED, 'lang' => $this->language->lang('DL_REPORT_STATUS_5')];
								break;
							case $this->dlext_constants::DL_REPORT_STATUS_FINISHED:
							case $this->dlext_constants::DL_REPORT_STATUS_DECLINED:
								$s_select_status[] = ['value' => $this->dlext_constants::DL_REPORT_STATUS_PROGRESS, 'lang' => $this->language->lang('DL_REPORT_STATUS_2')];
								$s_select_status[] = ['value' => $this->dlext_constants::DL_REPORT_STATUS_PENDING, 'lang' => $this->language->lang('DL_REPORT_STATUS_3')];
								break;
						}

						if (!empty($s_select_status))
						{
							$this->template->assign_var('S_DL_SELECT_STATUS', $this->dlext_constants::DL_TRUE);

							$this->template->assign_vars([
								'S_DL_FORM_STATUS_ACTION' => $this->helper->route('oxpus_dlext_tracker_main', ['action' => 'status', 'df_id' => $report_file_id, 'fav_id' => $fav_id]),
							]);

							for ($i = 0; $i < count($s_select_status); ++$i)
							{
								$this->template->assign_block_vars('dl_report_new_status', [
									'DL_VALUE'	=> $s_select_status[$i]['value'],
									'DL_LANG'	=> $s_select_status[$i]['lang'],
								]);
							}
						}
					}

					if ($this->dlext_auth->user_admin())
					{
						$this->template->assign_vars([
							'I_DL_BUG_REPORT'		=> $this->user->img('icon_post_edit', 'EDIT_POST'),
							'U_DL_BUG_REPORT_EDIT'	=> $this->helper->route('oxpus_dlext_tracker_edit', ['fav_id' => $fav_id]),
						]);
					}

					$this->notification->delete_notifications([
						'oxpus.dlext.notification.type.bt_assign',
						'oxpus.dlext.notification.type.bt_status',
					], $fav_id, $this->dlext_constants::DL_FALSE, $this->user->data['user_id']);

					add_form_key('bt_tracker');
				}

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
				}

				if ($this->user->data['is_registered'] && $df_id)
				{
					$this->template->assign_var('S_DL_HIDDEN_FIELD', build_hidden_fields(['df_id' => $df_id]));
				}

				/*
				* include the mod footer
				*/
				$this->dlext_footer->set_parameter('tracker', 0, 0, $index);
				$this->dlext_footer->handle();

				/*
				* generate page
				*/
				return $this->helper->render('@oxpus_dlext/tracker/dl_tracker_main.html', $this->language->lang('DL_BUG_TRACKER'));
			}
		}

		redirect($this->helper->route('oxpus_dlext_index'));
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
