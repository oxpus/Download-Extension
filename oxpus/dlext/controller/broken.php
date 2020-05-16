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

class broken
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
	protected $dlext_email;
	protected $dlext_main;
	protected $dlext_status;

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
	* @param \phpbb\auth\auth						$auth
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
		$dlext_email,
		$dlext_main,
		$dlext_status
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
		$this->dlext_email				= $dlext_email;
		$this->dlext_main				= $dlext_main;
		$this->dlext_status				= $dlext_status;
	}

	public function handle()
	{
		$nav_view = 'broken';

		// Include the default base init script
		include_once($this->ext_path . 'phpbb/includes/base_init.' . $this->php_ext);

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
					FROM ' . DOWNLOADS_TABLE . '
					WHERE approve = ' . true . '
						AND id = ' . (int) $df_id . '
					ORDER BY cat, sort';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$description	= $row['description'];
				$hack_version	= $row['hack_version'];

				$description	= generate_text_for_display($description, $row['desc_uid'], $row['desc_bitfield'], $row['desc_flags']);

				$mini_icon		= $this->dlext_status->mini_status_file($cat_id, $df_id);

				$file_status	= [];
				$file_status	= $this->dlext_status->status($df_id);

				$status			= $file_status['status_detail'];
			}

			$this->db->sql_freeresult($result);

			// Prepare the captcha permissions for the current user
			$captcha_active = true;
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
				$cat_auth_tmp = [];
				$cat_auth_tmp = $this->dlext_auth->dl_cat_auth($cat_id);

				if (($cat_auth_tmp['auth_mod'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered'])) && !$this->dlext_auth->user_banned())
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

			switch ($this->config['dl_report_broken_vc'])
			{
				case 0:
					$captcha_active = false;
				break;

				case 1:
					if (!$user_is_guest)
					{
						$captcha_active = false;
					}
				break;

				case 2:
					if ($user_is_mod || $user_is_admin || $user_is_founder)
					{
						$captcha_active = false;
					}
				break;

				case 3:
					if ($user_is_admin || $user_is_founder)
					{
						$captcha_active = false;
					}
				break;

				case 4:
					if ($user_is_founder)
					{
						$captcha_active = false;
					}
				break;
			}

			if ($captcha_active)
			{
				$code_match = false;

				$captcha = $this->phpbb_container->get('captcha.factory')->get_instance($this->config['captcha_plugin']);
				$captcha->init(CONFIRM_POST);

				$s_hidden_fields = [];
				$error = [];

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

			        if (!sizeof($error))
			        {
			            $captcha->reset();
			            $code_match = true;
			        }
			        else if ($captcha->is_solved())
			        {
			            $s_hidden_fields += $captcha->get_hidden_fields();
			            $code_match = false;
			        }
				}
				else if (!$captcha->is_solved())
				{
					add_form_key('dl_report');

					page_header();

					$this->template->set_filenames(['body' => 'dl_report_code_body.html']);

					$s_hidden_fields += [
						'cat_id' => $cat_id,
						'df_id' => $df_id,
						'view' => 'broken',
						'confirm' => 'code'
					];

					$this->template->assign_vars([
						'DESCRIPTION'		=> $description,
						'MINI_IMG'			=> $mini_icon,
						'HACK_VERSION'		=> $hack_version,
						'STATUS'			=> $status,
						'MESSAGE_TITLE'		=> $this->language->lang('DL_BROKEN'),
						'MESSAGE_TEXT'		=> $this->language->lang('DL_REPORT_CONFIRM_CODE'),

			            'CAPTCHA_TEMPLATE'	=> $captcha->get_template(),

						'S_CONFIRM_ACTION'	=> $this->helper->route('oxpus_dlext_broken'),
						'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
			            'S_CONFIRM_CODE'	=> true,
					]);

					/*
					* include the mod footer
					*/
					$dl_footer = $this->phpbb_container->get('oxpus.dlext.footer');
					$dl_footer->set_parameter($nav_view, $cat_id, $df_id, $index);
					$dl_footer->handle();
				}
			}
			else if (!$submit)
			{
				page_header();

				$this->template->set_filenames(['body' => 'dl_report_code_body.html']);

				$s_hidden_fields = [
					'cat_id' => $cat_id,
					'df_id' => $df_id,
					'view' => 'broken',
				];

				$this->template->assign_vars([
					'DESCRIPTION'		=> $description,
					'MINI_IMG'			=> $mini_icon,
					'HACK_VERSION'		=> $hack_version,
					'STATUS'			=> $status,
					'MESSAGE_TITLE'		=> $this->language->lang('DL_BROKEN'),
					'MESSAGE_TEXT'		=> $this->language->lang('DL_REPORT_CONFIRM_CODE'),

					'S_CONFIRM_ACTION'	=> $this->helper->route('oxpus_dlext_broken'),
					'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
				]);

				/*
				* include the mod footer
				*/
				$dl_footer = $this->phpbb_container->get('oxpus.dlext.footer');
				$dl_footer->set_parameter($nav_view, $cat_id, $df_id, $index);
				$dl_footer->handle();
			}

			if ($captcha_active && !$code_match)
			{
				trigger_error('DL_REPORT_BROKEN_VC_MISMATCH');
			}
			else
			{
				$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'broken' => true]) . ' WHERE id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				$processing_user = $this->dlext_auth->dl_auth_users($cat_id, 'auth_mod');

				$report_notify_text = $this->request->variable('report_notify_text', '', true);
				$report_notify_text = ($report_notify_text) ? $this->language->lang('DL_REPORT_NOTIFY_TEXT', $report_notify_text) : '';

				$mail_data = [
					'email_template'		=> 'downloads_report_broken',
					'processing_user'		=> $processing_user,
					'report_notify_text'	=> $report_notify_text,
					'cat_id'				=> $cat_id,
					'df_id'					=> $df_id,
				];

				$this->dlext_email->send_report($mail_data);
			}
		}

		redirect($this->helper->route('oxpus_dlext_details', ['df_id' => $df_id, 'cat_id' => $cat_id]));
	}
}
