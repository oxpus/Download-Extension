<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\phpbb\classes;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class dl_email extends dl_mod
{
	/**
	* Send user information about a new/updated download
	* Used in
	*  dlext/acp/modules/dl_admin_files.php	(add/update)
	*  dlext/phpbb/dl_modcp.php		(update)
	*  dlext/phpbb/dl_upload.php	(add)
	*/
	public static function send_dl_notify($mail_data, $helper, $ext_path)
	{
		global $db, $config, $user;

		include_once(dl_init::phpbb_root_path() . 'includes/functions_messenger' . dl_init::phpEx());
		$messenger = new \messenger();

		$result = $db->sql_query($mail_data['query']);

		$cat_id = (int) $mail_data['cat_id'];

		while ($row = $db->sql_fetchrow($result))
		{
			$mail_template_path = $ext_path . 'language/' . $row['user_lang'] . '/email/';
			$messenger->template($mail_data['email_template'], $row['user_lang'], $mail_template_path);
			$messenger->to($row['user_email'], $row['username']);

			$messenger->assign_vars(array(
				'SITENAME'		=> $config['sitename'],
				'BOARD_EMAIL'	=> $config['board_email_sig'],
				'USERNAME'		=> htmlspecialchars_decode($row['username']),
				'DOWNLOAD'		=> strip_tags(htmlspecialchars_decode($mail_data['description'])),
				'DESCRIPTION'	=> strip_tags(htmlspecialchars_decode($mail_data['long_desc'])),
				'CATEGORY'		=> strip_tags(htmlspecialchars_decode($mail_data['cat_name'])),
				'U_APPROVE'		=> generate_board_url(true) . $helper->route('oxpus_dlext_controller', array('view' => 'modcp', 'action' => 'approve'), false, ''),
				'U_CATEGORY'	=> generate_board_url(true) . $helper->route('oxpus_dlext_controller', array('cat' => $cat_id), false, ''),
			));

			$messenger->send(NOTIFY_EMAIL);
		}

		$db->sql_freeresult($result);

		$messenger->save_queue();
	}

	/**
	* Send user new status about a bug tracker entry
	* Used in
	*  dlext/phpbb/dl_bug_tracker.php
	*/
	public static function send_bt_status($mail_data, $helper, $ext_path)
	{
		global $db, $config, $user;
		global $phpbb_container;
		$language = $phpbb_container->get('language');

		include_once(dl_init::phpbb_root_path() . 'includes/functions_messenger' . dl_init::phpEx());
		$messenger = new \messenger();

		$sql = 'SELECT user_email, user_lang, username FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $mail_data['report_author_id'];
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($mail_data['new_status_text'])
		{
			$status_text = $language->lang('DL_BUG_REPORT_EMAIL_STATUS', $mail_data['new_status_text']);
		}
		else
		{
			$status_text = '';
		}

		$mail_template_path = $ext_path . 'language/' . $row['user_lang'] . '/email/';
		$messenger->template($mail_data['email_template'], $row['user_lang'], $mail_template_path);
		$messenger->to($row['user_email'], $row['username']);

		$messenger->assign_vars(array(
			'SITENAME'		=> $config['sitename'],
			'BOARD_EMAIL'	=> $config['board_email_sig'],
			'USERNAME'		=> $user->data['username'],
			'REPORT_TITLE'	=> strip_tags(htmlspecialchars_decode($mail_data['report_title'])),
			'STATUS'		=> strip_tags(htmlspecialchars_decode($language->lang('DL_REPORT_STATUS_' . $mail_data['report_status']))),
			'STATUS_TEXT'	=> strip_tags(htmlspecialchars_decode($status_text)),
			'U_BUG_REPORT'	=> generate_board_url(true) . $helper->route('oxpus_dlext_controller', array('view' => 'bug_tracker', 'action' => 'detail', 'fav_id' => (int) $mail_data['fav_id']), false, ''),
		));

		$messenger->send(NOTIFY_EMAIL);
		$messenger->save_queue();
	}

	/**
	* Send user the assignment to a bug tracker entry
	* Used in
	*  dlext/phpbb/dl_bug_tracker.php
	*/
	public static function send_bt_assign($mail_data, $helper, $ext_path)
	{
		global $db, $config, $user;

		include_once(dl_init::phpbb_root_path() . 'includes/functions_messenger' . dl_init::phpEx());
		$messenger = new \messenger();

		$mail_template_path = $ext_path . 'language/' . $row['user_lang'] . '/email/';
		$messenger->template($mail_data['email_template'], $row['user_lang'], $mail_template_path);
		$messenger->to($mail_data['user_mail'], $mail_data['username']);

		$messenger->assign_vars(array(
			'SITENAME'		=> $config['sitename'],
			'BOARD_EMAIL'	=> $config['board_email_sig'],
			'USERNAME'		=> $user->data['username'],
			'U_BUG_REPORT'	=> generate_board_url(true) . $helper->route('oxpus_dlext_controller', array('view' => 'bug_tracker', 'action' => 'detail', 'fav_id' => (int) $mail_data['fav_id']), false, ''),
		));

		$messenger->send(NOTIFY_EMAIL);
		$messenger->save_queue();
	}

	/**
	* Send user notification about new comment to approve or just for information
	* Used in
	*  dlext/phpbb/dl_comments.php
	*/
	public static function send_comment_notify($mail_data, $helper, $ext_path)
	{
		global $db, $config, $user;

		include_once(dl_init::phpbb_root_path() . 'includes/functions_messenger' . dl_init::phpEx());
		$messenger = new \messenger();

		$result = $db->sql_query($mail_data['query']);

		$cat_id	= (int) $mail_data['cat_id'];
		$df_id	= (int) $mail_data['df_id'];

		while ($row = $db->sql_fetchrow($result))
		{
			$mail_template_path = $ext_path . 'language/' . $row['user_lang'] . '/email/';
			$messenger->template($mail_data['email_template'], $row['user_lang'], $mail_template_path);
			$messenger->to($row['user_email'], $row['username']);

			$messenger->assign_vars(array(
				'SITENAME'		=> $config['sitename'],
				'BOARD_EMAIL'	=> $config['board_email_sig'],
				'CATEGORY'		=> strip_tags(htmlspecialchars_decode($mail_data['cat_name'])),
				'USERNAME'		=> strip_tags(htmlspecialchars_decode($row['username'])),
				'DOWNLOAD'		=> strip_tags(htmlspecialchars_decode($mail_data['description'])),
				'U_APPROVE'		=> generate_board_url(true) . $helper->route('oxpus_dlext_controller', array('view' => 'modcp', 'action' => 'capprove'), false, ''),
				'U_DOWNLOAD'	=> generate_board_url(true) . $helper->route('oxpus_dlext_controller', array('view' => 'comment', 'action' => 'view', 'cat_id' => $cat_id, 'df_id' => $df_id), false, ''),
			));
			$messenger->send(NOTIFY_EMAIL);
		}

		$db->sql_freeresult($result);

		$messenger->save_queue();
	}

	/**
	* Send user notification to report a broken download
	* Used in
	*  dlext/controller/main.php
	*/
	public static function send_report($mail_data, $helper, $ext_path)
	{
		global $db, $config, $user;

		include_once(dl_init::phpbb_root_path() . 'includes/functions_messenger' . dl_init::phpEx());
		$messenger = new \messenger();

		$cat_id	= (int) $mail_data['cat_id'];
		$df_id	= (int) $mail_data['df_id'];

		$username = (!$user->data['is_registered']) ? $language->lang('DL_A_GUEST') : $user->data['username'];

		$sql = 'SELECT user_email, username, user_lang FROM ' . USERS_TABLE . '
			WHERE ' . $db->sql_in_set('user_id', explode(', ', $mail_data['processing_user'])) . '
				OR user_type = ' . USER_FOUNDER . '
			GROUP BY user_email, username, user_lang';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$mail_template_path = $ext_path . 'language/' . $row['user_lang'] . '/email/';
			$messenger->template($mail_data['email_template'], $row['user_lang'], $mail_template_path);
			$messenger->to($row['user_email'], $row['username']);

			$messenger->assign_vars(array(
				'SITENAME'				=> $config['sitename'],
				'BOARD_EMAIL'			=> $config['board_email_sig'],
				'REPORTER'				=> strip_tags(htmlspecialchars_decode($username)),
				'REPORT_NOTIFY_TEXT'	=> strip_tags(htmlspecialchars_decode($mail_data['report_notify_text'])),
				'USERNAME'				=> strip_tags(htmlspecialchars_decode($row['username'])),
				'U_DOWNLOAD'			=> generate_board_url(true) . $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'cat_id' => $cat_id, 'df_id' => $df_id), false, ''),
			));

			$messenger->send(NOTIFY_EMAIL);
		}

		$db->sql_freeresult($result);

		$messenger->save_queue();
	}
}
