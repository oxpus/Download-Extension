<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright (c) 2021-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\tracker;

class edit
{
	/* phpbb objects */
	protected $root_path;
	protected $php_ext;
	protected $extension_manager;
	protected $db;
	protected $config;
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
	 * @param \phpbb\config\config					$config
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
	 * @param string								$dlext_table_dl_bug_history
	 * @param string								$dlext_table_dl_tracker
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		$root_path,
		$php_ext,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
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
		$dlext_table_dl_bug_history,
		$dlext_table_dl_tracker,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;
		$this->notification				= $notification;

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
			$preview	= $this->request->variable('preview', '');
			$cancel		= $this->request->variable('cancel', '');
			$action		= $this->request->variable('action', '');
			$df_id		= $this->request->variable('df_id', 0);
			$fav_id		= $this->request->variable('fav_id', 0);

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
				* check the user permissions for all download categories
				*/
				$bug_access_cats	= $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_VIEW);

				$report_title		= $this->request->variable('report_title', '', $this->dlext_constants::DL_TRUE);
				$report_text		= $this->request->variable('message', '', $this->dlext_constants::DL_TRUE);
				$report_file_ver	= $this->request->variable('report_file_ver', '', $this->dlext_constants::DL_TRUE);
				$report_php			= $this->request->variable('report_php', '', $this->dlext_constants::DL_TRUE);
				$report_db			= $this->request->variable('report_db', '', $this->dlext_constants::DL_TRUE);
				$report_forum		= $this->request->variable('report_forum', '', $this->dlext_constants::DL_TRUE);
				$new_user_id		= $this->request->variable('user_assign', 0);

				$allow_bbcode	= ($this->config['allow_bbcode']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
				$allow_urls		= $this->dlext_constants::DL_TRUE;
				$allow_smilies	= ($this->config['allow_smilies']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
				$bug_uid		=
				$bug_bitfield	= '';
				$bug_flags		= 0;

				$error_txt = [];
				$error = $this->dlext_constants::DL_FALSE;

				if ($preview || $action == 'save')
				{
					if (!check_form_key('bt_tracker'))
					{
						$error_txt[] = $this->language->lang('FORM_INVALID');
						$error = $this->dlext_constants::DL_TRUE;
					}

					if (!$report_title)
					{
						$error_txt[] = $this->language->lang('DL_BUG_REPORT_NO_TITLE');
						$error = $this->dlext_constants::DL_TRUE;
					}

					if (!$report_text)
					{
						$error_txt[] = $this->language->lang('DL_BUG_REPORT_NO_TEXT');
						$error = $this->dlext_constants::DL_TRUE;
					}
				}
				else if ($action == 'status' || $action == 'assign')
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

				if (!$error && $preview && $this->user->data['is_registered'])
				{
					$preview_title	= $report_title;
					$preview_text	= $report_text;

					generate_text_for_storage($preview_text, $bug_uid, $bug_bitfield, $bug_flags, $allow_bbcode, $allow_urls, $allow_smilies);
					$preview_text	= generate_text_for_display($preview_text, $bug_uid, $bug_bitfield, $bug_flags);

					$this->template->assign_vars([
						'DL_PREVIEW_TITLE'	=> $preview_title,
						'DL_PREVIEW_TEXT'	=> $preview_text,

						'S_DL_PREVIEW'		=> $this->dlext_constants::DL_TRUE,
					]);

					$action = ($fav_id && $this->dlext_auth->user_admin()) ? 'edit' : 'add';
				}

				/*
				* save new or edited bug report
				*/
				if (!$error && $action == 'save' && $this->user->data['is_registered'])
				{
					generate_text_for_storage($report_text, $bug_uid, $bug_bitfield, $bug_flags, $allow_bbcode, $allow_urls, $allow_smilies);

					if ($fav_id && $this->dlext_auth->user_admin())
					{
						$sql = 'UPDATE ' . $this->dlext_table_dl_tracker . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'df_id'					=> $df_id,
							'report_title'			=> $report_title,
							'report_text'			=> $report_text,
							'bug_uid'				=> $bug_uid,
							'bug_bitfield'			=> $bug_bitfield,
							'bug_flags'				=> $bug_flags,
							'report_file_ver'		=> $report_file_ver,
							'report_date'			=> time(),
							'report_author_id'		=> $this->user->data['user_id'],
							'report_status_date'	=> time(),
							'report_php'			=> $report_php,
							'report_db'				=> $report_db,
							'report_forum'			=> $report_forum
						]) . ' WHERE report_id = ' . (int) $fav_id;
						$this->db->sql_query($sql);
					}
					else
					{
						$sql = 'INSERT INTO ' . $this->dlext_table_dl_tracker . ' ' . $this->db->sql_build_array('INSERT', [
							'df_id'					=> $df_id,
							'report_title'			=> $report_title,
							'report_text'			=> $report_text,
							'bug_uid'				=> $bug_uid,
							'bug_bitfield'			=> $bug_bitfield,
							'bug_flags'				=> $bug_flags,
							'report_file_ver'		=> $report_file_ver,
							'report_date'			=> time(),
							'report_author_id'		=> $this->user->data['user_id'],
							'report_status_date'	=> time(),
							'report_php'			=> $report_php,
							'report_db'				=> $report_db,
							'report_forum'			=> $report_forum
						]);
						$this->db->sql_query($sql);

						$fav_id = $this->db->sql_last_inserted_id();

						$sql = 'INSERT INTO ' . $this->dlext_table_dl_bug_history . ' ' . $this->db->sql_build_array('INSERT', [
							'df_id'					=> $df_id,
							'report_id'				=> $fav_id,
							'report_his_type'		=> 'status',
							'report_his_date'		=>  time(),
							'report_his_value'		=> '',
							'report_his_status'		=> $this->dlext_constants::DL_REPORT_STATUS_NEW,
							'report_his_user_id'	=> $this->user->data['user_id']
						]);
						$this->db->sql_query($sql);
					}

					$link_array = ['df_id' => $df_id];
					$controller = 'oxpus_dlext_tracker_list';

					if ($fav_id && $this->dlext_auth->user_admin())
					{
						$link_array += ['fav_id' => $fav_id];
						$controller = 'oxpus_dlext_tracker_main';
					}

					$message = $this->language->lang('DL_BUG_REPORT_ADDED') . '<br><br>' . $this->language->lang('CLICK_RETURN_BUG_TRACKER', '<a href="' . $this->helper->route($controller, $link_array) . '">', '</a>');

					trigger_error($message);
				}

				/*
				* display form to add a bug report or let an admin edit an existing report
				*/
				if (($action == 'add' && $this->user->data['is_registered']) || ($action == 'edit' && $this->dlext_auth->user_admin() && $fav_id))
				{
					$s_hidden_fields = ['action' => 'save'];

					if ($action == 'edit')
					{
						$s_hidden_fields += ['fav_id' => $fav_id];
					}

					$sql = 'SELECT c.cat_name, d.id, d.description, d.desc_uid, d.desc_bitfield, d.desc_flags FROM ' . $this->dlext_table_downloads . ' d, ' . $this->dlext_table_dl_cat . ' c
						WHERE d.cat = c.id
							AND c.bug_tracker = 1
							AND ' . $this->db->sql_in_set('c.id', $bug_access_cats) . '
						ORDER BY c.sort ASC, d.sort ASC';
					$result = $this->db->sql_query($sql);

					$cur_cat = '';

					while ($row = $this->db->sql_fetchrow($result))
					{
						$cat_name = $row['cat_name'];

						if ($cat_name != $cur_cat)
						{
							if ($cur_cat != '')
							{
								$this->template->assign_block_vars('dl_report_select', [
									'DL_TYPE'	=> 'optend',
								]);
							}

							$this->template->assign_block_vars('dl_report_select', [
								'DL_TYPE'	=> 'optgrp',
								'DL_VALUE'	=> $cat_name,
							]);

							$cur_cat = $cat_name;
						}

						$download_name = $row['description'];
						$desc_uid = $row['desc_uid'];
						$desc_bitfield = $row['desc_bitfield'];
						$desc_flags = $row['desc_flags'];
						$description = generate_text_for_display($download_name, $desc_uid, $desc_bitfield, $desc_flags);

						$this->template->assign_block_vars('dl_report_select', [
							'DL_TYPE'	=> 'option',
							'DL_KEY'	=> $row['id'],
							'DL_VALUE'	=> $description,
						]);
					}

					$this->template->assign_block_vars('dl_report_select', [
						'DL_TYPE'	=> 'optend',
					]);

					$this->db->sql_freeresult($result);

					add_form_key('bt_tracker');

					// Status for HTML, BBCode, Smilies, Images and Flash
					$bbcode_status	= ($this->config['allow_bbcode']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
					$smilies_status	= ($bbcode_status && $this->config['allow_smilies']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
					$img_status		= ($bbcode_status) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
					$url_status		= ($this->config['allow_post_links']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
					$flash_status	= ($bbcode_status && $this->config['allow_post_flash']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
					$quote_status	= $this->dlext_constants::DL_TRUE;

					$this->language->add_lang('posting');

					// Smilies Block,
					if ($smilies_status)
					{
						if (!function_exists('generate_smilies'))
						{
							include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
						}

						generate_smilies('inline', 0);
					}

					// Display functions
					if (!function_exists('display_custom_bbcodes'))
					{
						include($this->root_path . 'includes/functions_display.' . $this->php_ext);
					}

					display_custom_bbcodes();

					if ($action == 'edit' && !$preview)
					{
						$preview = $this->dlext_constants::DL_TRUE;

						$sql = 'SELECT * FROM ' . $this->dlext_table_dl_tracker . '
							WHERE report_id = ' . (int) $fav_id;
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						$report_title		= $row['report_title'];
						$report_text		= $row['report_text'];
						$report_file_ver	= $row['report_file_ver'];
						$report_php			= $row['report_php'];
						$report_db			= $row['report_db'];
						$report_forum		= $row['report_forum'];

						$bug_uid			= $row['bug_uid'];
						$bug_flags			= $row['bug_flags'];

						$text_ary			= generate_text_for_edit($report_text, $bug_uid, $bug_flags);
						$report_text		= $text_ary['text'];
					}

					$this->template->assign_vars([
						'DL_ERROR'				=> ($error) ? implode('<br>', $error_txt) : $this->dlext_constants::DL_FALSE,

						'DL_REPORT_TITLE'		=> ($preview) ? $report_title : '',
						'DL_REPORT_TEXT'		=> ($preview) ? $report_text : '',
						'DL_REPORT_FILE_VER'	=> ($preview) ? $report_file_ver : '',
						'DL_REPORT_PHP'			=> ($preview) ? $report_php : '',
						'DL_REPORT_DB'			=> ($preview) ? $report_db : '',
						'DL_REPORT_FORUM'		=> ($preview) ? $report_forum : '',

						'S_BBCODE_ALLOWED'		=> $bbcode_status,
						'S_BBCODE_IMG'			=> $img_status,
						'S_BBCODE_URL'			=> $url_status,
						'S_BBCODE_FLASH'		=> $flash_status,
						'S_BBCODE_QUOTE'		=> $quote_status,

						'S_DL_FORM_ACTION'		=> $this->helper->route('oxpus_dlext_tracker_edit'),
						'S_DL_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
						'S_DL_SELECT_DOWNLOAD'	=> $df_id,

						'U_DL_MORE_SMILIES'		=> $this->helper->route('oxpus_dlext_details', ['action' => 'smilies']),
						'U_DL_DOWNLOAD'			=> $this->helper->route('oxpus_dlext_index'),
						'U_DL_BUG_TRACKER'		=> $this->helper->route('oxpus_dlext_tracker_edit'),
					]);
				}

				/*
				* include the mod footer
				*/
				$this->dlext_footer->set_parameter('tracker', 0, 0, $index);
				$this->dlext_footer->handle();

				/*
				* generate page
				*/
				return $this->helper->render('@oxpus_dlext/tracker/dl_tracker_edit.html', $this->language->lang('DL_BUG_TRACKER'));
			}
		}

		redirect($this->helper->route('oxpus_dlext_index'));
	}
}
