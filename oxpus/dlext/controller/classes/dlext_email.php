<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\classes;

use Symfony\Component\DependencyInjection\Container;

class dlext_email implements dlext_email_interface
{
	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\controller\helper */
	protected $helper;

	protected $root_path;
	protected $php_ext;
	protected $ext_path;

	protected $language;
	protected $dlext_auth;
	protected $dlext_init;

	/**
	* Constructor
	*
	* @param Container 								$phpbb_container
	* @param \phpbb\extension\manager				$phpbb_extension_manager

	* @param \phpbb\user							$user
	* @param \phpbb\config\config					$config
	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param \phpbb\controller\helper				$helper
	*/
	public function __construct(
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,

		\phpbb\user $user,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		$dlext_auth,
		$dlext_init
	)
	{
		$this->user 		= $user;
		$this->config 		= $config;
		$this->db 			= $db;
		$this->helper 		= $helper;

		$this->language		= $phpbb_container->get('language');
		$this->dlext_auth	= $dlext_auth;
		$this->dlext_init	= $dlext_init;

		$this->root_path	= $this->dlext_init->root_path();
		$this->php_ext		= $this->dlext_init->php_ext();
		$this->ext_path		= $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
	}

	/**
	 * Send user information about a new/updated download
	 * Used in controllers
	 *  oxpus.dlext.acp_files_controller	(add/update)
	 *  oxpus.dlext.mcp_edit_controller  (update)
	 *  oxpus.dlext.upload				(add)
	*/
	public function send_dl_notify($mail_data)
	{
		include_once($this->root_path . 'includes/functions_messenger' . $this->php_ext);
		$messenger = new \messenger();

		$result = $this->db->sql_query($mail_data['query']);

		$cat_id = (int) $mail_data['cat_id'];

		while ($row = $this->db->sql_fetchrow($result))
		{
			$mail_template_path = $this->ext_path . 'language/' . $row['user_lang'] . '/email/';
			$messenger->template($mail_data['email_template'], $row['user_lang'], $mail_template_path);
			$messenger->to($row['user_email'], $row['username']);

			$messenger->assign_vars([
				'SITENAME'		=> $this->config['sitename'],
				'BOARD_EMAIL'	=> $this->config['board_email_sig'],
				'USERNAME'		=> htmlspecialchars_decode($row['username']),
				'DOWNLOAD'		=> strip_tags(htmlspecialchars_decode($mail_data['description'])),
				'DESCRIPTION'	=> strip_tags(htmlspecialchars_decode($mail_data['long_desc'])),
				'CATEGORY'		=> strip_tags(htmlspecialchars_decode($mail_data['cat_name'])),
				'U_APPROVE'		=> generate_board_url(true) . $this->helper->route('oxpus_dlext_mcp_approve'),
				'U_CATEGORY'	=> generate_board_url(true) . $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id], false, ''),
			]);

			$messenger->send(NOTIFY_EMAIL);
		}

		$this->db->sql_freeresult($result);

		$messenger->save_queue();
	}

	/**
	 * Send user new status about a bug tracker entry
	 * Used in controller
	 *  oxpus.dlext.tracker
	*/
	public function send_bt_status($mail_data)
	{
		include_once($this->root_path . 'includes/functions_messenger' . $this->php_ext);
		$messenger = new \messenger();

		$sql = 'SELECT user_email, user_lang, username FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $mail_data['report_author_id'];
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($mail_data['new_status_text'])
		{
			$status_text = $this->language->lang('DL_BUG_REPORT_EMAIL_STATUS', $mail_data['new_status_text']);
		}
		else
		{
			$status_text = '';
		}

		$mail_template_path = $this->ext_path . 'language/' . $row['user_lang'] . '/email/';
		$messenger->template($mail_data['email_template'], $row['user_lang'], $mail_template_path);
		$messenger->to($row['user_email'], $row['username']);

		$messenger->assign_vars([
			'SITENAME'		=> $this->config['sitename'],
			'BOARD_EMAIL'	=> $this->config['board_email_sig'],
			'USERNAME'		=> $this->user->data['username'],
			'REPORT_TITLE'	=> strip_tags(htmlspecialchars_decode($mail_data['report_title'])),
			'STATUS'		=> strip_tags(htmlspecialchars_decode($this->language->lang('DL_REPORT_STATUS_' . $mail_data['report_status']))),
			'STATUS_TEXT'	=> strip_tags(htmlspecialchars_decode($status_text)),
			'U_BUG_REPORT'	=> generate_board_url(true) . $this->helper->route('oxpus_dlext_tracker', ['action' => 'detail', 'fav_id' => (int) $mail_data['fav_id']], false, ''),
		]);

		$messenger->send(NOTIFY_EMAIL);
		$messenger->save_queue();
	}

	/**
	 * Send user the assignment to a bug tracker entry
	 * Used in controller
	 *  oxpus.dlext.tracker
	*/
	public function send_bt_assign($mail_data)
	{
		include_once($this->root_path . 'includes/functions_messenger' . $this->php_ext);
		$messenger = new \messenger();

		$mail_template_path = $this->ext_path . 'language/' . $row['user_lang'] . '/email/';
		$messenger->template($mail_data['email_template'], $row['user_lang'], $mail_template_path);
		$messenger->to($mail_data['user_mail'], $mail_data['username']);

		$messenger->assign_vars([
			'SITENAME'		=> $this->config['sitename'],
			'BOARD_EMAIL'	=> $this->config['board_email_sig'],
			'USERNAME'		=> $this->user->data['username'],
			'U_BUG_REPORT'	=> generate_board_url(true) . $this->helper->route('oxpus_dlext_tracker', ['action' => 'detail', 'fav_id' => (int) $mail_data['fav_id']], false, ''),
		]);

		$messenger->send(NOTIFY_EMAIL);
		$messenger->save_queue();
	}

	/**
	 * Send user notification about new comment to approve or just for information
	 * Used in controller
	 *  oxpus.dlext.details.php
	*/
	public function send_comment_notify($mail_data)
	{
		include_once($this->root_path . 'includes/functions_messenger' . $this->php_ext);
		$messenger = new \messenger();

		$result = $this->db->sql_query($mail_data['query']);

		$cat_id	= (int) $mail_data['cat_id'];
		$df_id	= (int) $mail_data['df_id'];

		while ($row = $this->db->sql_fetchrow($result))
		{
			$mail_template_path = $this->ext_path . 'language/' . $row['user_lang'] . '/email/';
			$messenger->template($mail_data['email_template'], $row['user_lang'], $mail_template_path);
			$messenger->to($row['user_email'], $row['username']);

			$messenger->assign_vars([
				'SITENAME'		=> $this->config['sitename'],
				'BOARD_EMAIL'	=> $this->config['board_email_sig'],
				'CATEGORY'		=> strip_tags(htmlspecialchars_decode($mail_data['cat_name'])),
				'USERNAME'		=> strip_tags(htmlspecialchars_decode($row['username'])),
				'DOWNLOAD'		=> strip_tags(htmlspecialchars_decode($mail_data['description'])),
				'U_APPROVE'		=> generate_board_url(true) . $this->helper->route('oxpus_dlext_mcp_capprove'),
				'U_DOWNLOAD'	=> generate_board_url(true) . $this->helper->route('oxpus_dlext_details', ['view' => 'comment', 'action' => 'view', 'cat_id' => $cat_id, 'df_id' => $df_id], false, ''),
			]);
			$messenger->send(NOTIFY_EMAIL);
		}

		$this->db->sql_freeresult($result);

		$messenger->save_queue();
	}

	/**
	 * Send user notification to report a broken download
	 * Used in controller
	 *  oxpus.dlext.dlext_main
	*/
	public function send_report($mail_data)
	{
		include_once($this->root_path . 'includes/functions_messenger' . $this->php_ext);
		$messenger = new \messenger();

		$cat_id	= (int) $mail_data['cat_id'];
		$df_id	= (int) $mail_data['df_id'];

		$username = ($this->dlext_auth->user_logged_in()) ? $this->user->data['username'] : $this->language->lang('DL_A_GUEST');

		$sql = 'SELECT user_email, username, user_lang FROM ' . USERS_TABLE . '
			WHERE ' . $this->db->sql_in_set('user_id', explode(', ', $mail_data['processing_user'])) . '
				OR user_type = ' . USER_FOUNDER . '
			GROUP BY user_email, username, user_lang';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$mail_template_path = $this->ext_path . 'language/' . $row['user_lang'] . '/email/';
			$messenger->template($mail_data['email_template'], $row['user_lang'], $mail_template_path);
			$messenger->to($row['user_email'], $row['username']);

			$messenger->assign_vars([
				'SITENAME'				=> $this->config['sitename'],
				'BOARD_EMAIL'			=> $this->config['board_email_sig'],
				'REPORTER'				=> strip_tags(htmlspecialchars_decode($username)),
				'REPORT_NOTIFY_TEXT'	=> strip_tags(htmlspecialchars_decode($mail_data['report_notify_text'])),
				'USERNAME'				=> strip_tags(htmlspecialchars_decode($row['username'])),
				'U_DOWNLOAD'			=> generate_board_url(true) . $this->helper->route('oxpus_dlext_details', ['cat_id' => $cat_id, 'df_id' => $df_id], false, ''),
			]);

			$messenger->send(NOTIFY_EMAIL);
		}

		$this->db->sql_freeresult($result);

		$messenger->save_queue();
	}
}
