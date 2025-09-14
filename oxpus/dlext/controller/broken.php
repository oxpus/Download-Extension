<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class broken
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames error

	/* phpbb objects */
	protected $db;
	protected $config;
	protected $helper;
	protected $request;
	protected $template;
	protected $user;
	protected $language;
	protected $notification;
	protected $captcha;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_main;
	protected $dlext_status;
	protected $dlext_constants;
	protected $dlext_footer;

	protected $dlext_table_downloads;
	protected $dlext_table_dl_reports;

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
	 * @param \phpbb\captcha\factory				$captcha
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\status				$dlext_status
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_reports
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
		\phpbb\captcha\factory $captcha,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\status $dlext_status,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		$dlext_table_downloads,
		$dlext_table_dl_reports
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
		$this->captcha					= $captcha;

		$this->dlext_table_downloads	= $dlext_table_downloads;
		$this->dlext_table_dl_reports	= $dlext_table_dl_reports;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_main				= $dlext_main;
		$this->dlext_status				= $dlext_status;
		$this->dlext_constants			= $dlext_constants;
		$this->dlext_footer				= $dlext_footer;
	}

	public function handle()
	{
		$this->dlext_main->dl_handle_active();

		$cat		= $this->request->variable('cat', 0);
		$submit		= $this->request->variable('submit', '');
		$cancel		= $this->request->variable('cancel', '');
		$confirm	= $this->request->variable('confirm', '');
		$df_id		= $this->request->variable('df_id', 0);
		$cat_id		= $this->request->variable('cat_id', 0);

		$index 		= ($cat) ? $this->dlext_main->index($cat) : $this->dlext_main->index();

		if ($cancel)
		{
			if ($df_id)
			{
				redirect($this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]));
			}
			else
			{
				redirect($this->helper->route('oxpus_dlext_index'));
			}
		}

		/*
		* handle reported broken download
		*/
		if ($df_id && $cat_id && ($this->user->data['is_registered'] || (!$this->user->data['is_registered'] && $this->config['dl_report_broken'])))
		{
			$sql = 'SELECT description, desc_uid, desc_bitfield, desc_flags, hack_version
					FROM ' . $this->dlext_table_downloads . '
					WHERE approve = 1
						AND id = ' . (int) $df_id . '
					ORDER BY cat, sort';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$hack_version	= $row['hack_version'];

				$description	= generate_text_for_display($row['description'], $row['desc_uid'], $row['desc_bitfield'], $row['desc_flags']);

				$mini_icon		= $this->dlext_status->mini_status_file($cat_id, $df_id);
				$check_status	= $this->dlext_status->status($df_id);
			}

			$this->db->sql_freeresult($result);

			// Prepare the captcha permissions for the current user
			$captcha_active = $this->dlext_auth->get_captcha_status($this->config['dl_report_broken_vc'], $cat_id);

			if ($captcha_active)
			{
				$code_match = $this->dlext_constants::DL_FALSE;

				$captcha = $this->captcha->get_instance($this->config['captcha_plugin']);
				$captcha->init(CONFIRM_POST);

				if ($confirm == 'code')
				{
					if (!check_form_key('dl_report'))
					{
						trigger_error('FORM_INVALID');
					}

					$vc_response = $captcha->validate();

					if ($vc_response)
					{
						$error[] = $vc_response;
					}

					if (empty($error))
					{
						$captcha->reset();
						$code_match = $this->dlext_constants::DL_TRUE;
					}
					else if ($captcha->is_solved())
					{
						$code_match = $this->dlext_constants::DL_FALSE;
					}
				}
				else if (!$captcha->is_solved())
				{
					add_form_key('dl_report');

					$s_hidden_fields = [
						'cat_id'	=> $cat_id,
						'df_id'		=> $df_id,
						'view'		=> 'broken',
						'confirm'	=> 'code'
					];

					$this->template->assign_vars([
						'DL_DESCRIPTION'		=> $description,
						'DL_MINI_IMG'			=> $mini_icon,
						'DL_HACK_VERSION'		=> $hack_version,
						'DL_FILE_STATUS'		=> $check_status['file_status'],
						'DL_MESSAGE_TITLE'		=> $this->language->lang('DL_BROKEN'),
						'DL_CAPTCHA_TEMPLATE'	=> $captcha->get_template(),

						'S_DL_CONFIRM_ACTION'	=> $this->helper->route('oxpus_dlext_broken'),
						'S_DL_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
						'S_DL_CONFIRM_CODE'		=> $this->dlext_constants::DL_TRUE,
					]);

					/*
					* include the mod footer
					*/
					$this->dlext_footer->set_parameter('broken', $cat_id, $df_id, $index);
					$this->dlext_footer->handle();

					return $this->helper->render('@oxpus_dlext/dl_report_code_body.html');
				}
			}
			else if (!$submit)
			{
				$s_hidden_fields = [
					'cat_id'	=> $cat_id,
					'df_id'		=> $df_id,
					'view'		=> 'broken',
				];

				$this->template->assign_vars([
					'DL_DESCRIPTION'		=> $description,
					'DL_MINI_IMG'			=> $mini_icon,
					'DL_HACK_VERSION'		=> $hack_version,
					'DL_FILE_STATUS'		=> $check_status['file_status'],
					'DL_MESSAGE_TITLE'		=> $this->language->lang('DL_BROKEN'),

					'S_DL_CONFIRM_ACTION'	=> $this->helper->route('oxpus_dlext_broken'),
					'S_DL_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
				]);

				/*
				* include the mod footer
				*/
				$this->dlext_footer->set_parameter('broken', $cat_id, $df_id, $index);
				$this->dlext_footer->handle();

				return $this->helper->render('@oxpus_dlext/dl_report_code_body.html');
			}

			if ($captcha_active && !$code_match)
			{
				trigger_error('DL_REPORT_BROKEN_VC_MISMATCH');
			}
			else
			{
				$processing_user	= $this->dlext_auth->dl_auth_users($cat_id, 'auth_mod');
				$reporter_name		= ($this->user->data['is_registered']) ? $this->user->data['username'] : $this->language->lang('DL_A_GUEST');
				$reporter_user_id	= ($this->user->data['is_registered']) ? $this->user->data['user_id'] : ANONYMOUS;

				$report_notify_text = censor_text($this->request->variable('report_notify_text', '', $this->dlext_constants::DL_TRUE));

				$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'broken' => $this->dlext_constants::DL_TRUE
				]) . ' WHERE id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				$sql_data = [
					'dl_id'					=> $df_id,
					'user_id'				=> $reporter_user_id,
					'report_time'			=> time(),
					'report_text'			=> $report_notify_text,
				];

				$sql = 'INSERT INTO ' . $this->dlext_table_dl_reports . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
				$this->db->sql_query($sql);

				$report_notify_text = ($report_notify_text) ? $this->language->lang('DL_REPORT_NOTIFY_TEXT', $report_notify_text) : '';

				$notification_data = [
					'user_ids'				=> $processing_user,
					'df_id'					=> $df_id,
					'description'			=> $description,
					'reporter'				=> $reporter_name,
					'report_notify_text'	=> $report_notify_text,
				];

				$this->notification->add_notifications('oxpus.dlext.notification.type.broken', $notification_data);
			}
		}

		redirect($this->helper->route('oxpus_dlext_details', ['df_id' => $df_id, 'cat_id' => $cat_id]));
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
