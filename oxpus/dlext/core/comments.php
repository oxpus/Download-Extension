<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright (c) 2021-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

class comments implements comments_interface
{
	/* phpbb objects */
	protected $root_path;
	protected $php_ext;
	protected $db;
	protected $config;
	protected $helper;
	protected $request;
	protected $template;
	protected $user;
	protected $language;
	protected $dispatcher;
	protected $notification;
	protected $pagination;

	/* extension owned objects */
	protected $ext_path;
	protected $dl_manage_comments;
	protected $dl_deny_post;

	protected $dlext_auth;
	protected $dlext_main;
	protected $dlext_constants;

	protected $dlext_table_dl_comments;
	protected $dlext_table_dl_favorites;
	protected $dlext_table_downloads;

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
	 * @param \phpbb\event\dispatcher_interface		$dispatcher
	 * @param \phpbb\notification\manager			$notification
	 * @param \phpbb\pagination						$pagination
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_comments
	 * @param string								$dlext_table_dl_favorites
	 * @param string								$dlext_table_downloads
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
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\notification\manager $notification,
		\phpbb\pagination $pagination,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_comments,
		$dlext_table_dl_favorites,
		$dlext_table_downloads
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
		$this->dispatcher				= $dispatcher;
		$this->notification				= $notification;
		$this->pagination				= $pagination;

		$this->dlext_table_dl_comments	= $dlext_table_dl_comments;
		$this->dlext_table_dl_favorites	= $dlext_table_dl_favorites;
		$this->dlext_table_downloads	= $dlext_table_downloads;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_main				= $dlext_main;
		$this->dlext_constants			= $dlext_constants;
	}

	public function set_auth_comments($cat_id)
	{
		$this->dl_manage_comments	= $this->dlext_constants::DL_FALSE;
		$this->dl_deny_post			= $this->dlext_constants::DL_FALSE;

		if ($this->dlext_auth->user_auth($cat_id, 'auth_mod'))
		{
			$this->dl_manage_comments = $this->dlext_constants::DL_TRUE;
		}

		if (!$this->dlext_auth->cat_auth_comment_post($cat_id))
		{
			$this->dl_manage_comments = $this->dlext_constants::DL_FALSE;
			$this->dl_deny_post = $this->dlext_constants::DL_TRUE;
		}
	}

	public function get_auth_comment_post($cat_id)
	{
		$this->set_auth_comments($cat_id);

		return $this->dl_deny_post;
	}

	public function get_auth_comment_manage($cat_id)
	{
		$this->set_auth_comments($cat_id);

		return $this->dl_manage_comments;
	}

	public function save_comment($cat_id, $df_id, $dl_id = 0)
	{
		if (!check_form_key('dl_comment_posting'))
		{
			trigger_error($this->language->lang('FORM_INVALID'), E_USER_WARNING);
		}

		$this->set_auth_comments($cat_id);

		if ($this->dl_deny_post)
		{
			return 'view';
		}

		$comment_text 	= $this->request->variable('message', '', $this->dlext_constants::DL_TRUE);

		$allow_bbcode	= ($this->config['allow_bbcode']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
		$allow_urls		= $this->dlext_constants::DL_TRUE;
		$allow_smilies	= ($this->config['allow_smilies']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
		$com_uid		= '';
		$com_bitfield	= '';
		$com_flags		= 0;

		generate_text_for_storage($comment_text, $com_uid, $com_bitfield, $com_flags, $allow_bbcode, $allow_urls, $allow_smilies);

		$index = $this->dlext_main->full_index($cat_id);

		if ($index[$cat_id]['approve_comments'] || $this->dlext_auth->user_admin())
		{
			$approve = $this->dlext_constants::DL_TRUE;
		}
		else
		{
			$approve = $this->dlext_constants::DL_FALSE;
		}

		/**
		 * Additional actions before storage a download comment
		 *
		 * @event oxpus.dlext.details_comment_storage_before
		 * @var int		df_id			download ID
		 * @var int		dl_id			download comment ID
		 * @var int		cat_id			download category ID
		 * @var string	comment_text	comment text, unformatted
		 * @var bool	approve			download approval
		 * @since 8.1.1
		 */
		$vars = array(
			'df_id',
			'dl_id',
			'cat_id',
			'comment_text',
			'approve',
		);
		extract($this->dispatcher->trigger_event('oxpus.dlext.details_comment_storage_before', compact($vars)));

		if ($dl_id)
		{
			$sql = 'UPDATE ' . $this->dlext_table_dl_comments . ' SET ' . $this->db->sql_build_array('UPDATE', [
				'comment_edit_time'	=> time(),
				'comment_text'		=> $comment_text,
				'com_uid'			=> $com_uid,
				'com_bitfield'		=> $com_bitfield,
				'com_flags'			=> $com_flags,
				'approve'			=> $approve
			]) . ' WHERE dl_id = ' . (int) $dl_id;
			$this->db->sql_query($sql);

			$comment_message = $this->language->lang('DL_COMMENT_UPDATED');
		}
		else
		{
			$sql = 'INSERT INTO ' . $this->dlext_table_dl_comments . ' ' . $this->db->sql_build_array('INSERT', [
				'id'				=> $df_id,
				'cat_id'			=> $cat_id,
				'user_id'			=> $this->user->data['user_id'],
				'username'			=> $this->user->data['username'],
				'comment_time'		=> time(),
				'comment_edit_time'	=> time(),
				'comment_text'		=> $comment_text,
				'com_uid'			=> $com_uid,
				'com_bitfield'		=> $com_bitfield,
				'com_flags'			=> $com_flags,
				'approve'			=> $approve
			]);
			$this->db->sql_query($sql);

			$dl_id = $this->db->sql_last_inserted_id();

			$comment_message = $this->language->lang('DL_COMMENT_ADDED');
		}

		/**
		 * Additional actions after storage a download comment
		 *
		 * @event oxpus.dlext.details_comment_storage_after
		 * @var int		df_id			download ID
		 * @var int		dl_id			download comment ID
		 * @var int		cat_id			download category ID
		 * @var string	comment_text	comment text prepared for storage
		 * @var string	com_uid			comment bbcode uid
		 * @var string	com_bitfield	comment bbcode bitfields
		 * @var int		com_flags		comment bbcode flags
		 * @var bool	approve			download approval
		 * @var string	comment_message	success message after save/update comment
		 * @since 8.1.1
		 */
		$vars = array(
			'df_id',
			'dl_id',
			'cat_id',
			'comment_text',
			'com_uid',
			'com_bitfield',
			'com_flags',
			'approve',
			'comment_message',
		);
		extract($this->dispatcher->trigger_event('oxpus.dlext.details_comment_storage_after', compact($vars)));

		$sql = 'SELECT description, desc_uid, desc_bitfield, desc_flags
				FROM ' . $this->dlext_table_downloads . '
				WHERE id = ' . (int) $df_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$description		= $row['description'];
		$desc_uid			= $row['desc_uid'];
		$desc_bitfield		= $row['desc_bitfield'];
		$desc_flags			= $row['desc_flags'];
		$description		= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

		$approve_message	= '';

		if (!$approve)
		{
			$processing_user = ($index[$cat_id]['auth_cread'] == $this->dlext_constants::DL_PERM_ADMIN) ? $this->dlext_constants::DL_FALSE : $this->dlext_auth->dl_auth_users($cat_id, 'auth_mod');

			if (is_array($processing_user))
			{
				$notification_data = [
					'user_ids'			=> $processing_user,
					'df_id'				=> $df_id,
					'dl_id'				=> $dl_id,
					'description'		=> $description,
					'cat_name'			=> $index[$cat_id]['cat_name_nav'],
				];

				$this->notification->add_notifications('oxpus.dlext.notification.type.capprove', $notification_data);
				$approve_message	= '<br>' . $this->language->lang('DL_MUST_BE_APPROVE_COMMENT');
			}

			$return_parameters	= ['df_id' => $df_id];
			$return_text		= 'CLICK_RETURN_DOWNLOAD_DETAILS';
		}
		else
		{
			$sql = 'SELECT fav_user_id FROM ' . $this->dlext_table_dl_favorites . '
				WHERE fav_dl_id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);

			$fav_user = [];

			while ($row = $this->db->sql_fetchrow($result))
			{
				$fav_user[] = $row['fav_user_id'];
			}

			$this->db->sql_freeresult($result);

			if (!$this->config['dl_disable_email'] && $fav_user)
			{
				switch ($index[$cat_id]['auth_cread'])
				{
					case $this->dlext_constants::DL_PERM_ALL:
					case $this->dlext_constants::DL_PERM_USER:
						$processing_user = $fav_user;
						$send_notify = $this->dlext_constants::DL_TRUE;
						break;

					case $this->dlext_constants::DL_PERM_MOD:
						$processing_user = $this->dlext_auth->dl_auth_users($cat_id, 'auth_mod');
						$processing_user = array_merge($fav_user, $processing_user);
						$send_notify = $this->dlext_constants::DL_TRUE;
						break;

					default:
						$send_notify = $this->dlext_constants::DL_FALSE;
				}

				if ($send_notify)
				{
					$notification_data = [
						'user_ids'			=> $processing_user,
						'df_id'				=> $df_id,
						'dl_id'				=> $dl_id,
						'description'		=> $description,
						'cat_name'			=> $index[$cat_id]['cat_name_nav'],
					];

					$this->notification->add_notifications('oxpus.dlext.notification.type.comments', $notification_data);
				}
			}

			$return_parameters	= ['view' => 'comment', 'action' => 'view', 'df_id' => $df_id];
			$return_text		= 'CLICK_RETURN_COMMENTS';
		}

		$message = $comment_message . $approve_message . '<br><br>' . $this->language->lang($return_text, '<a href="' . $this->helper->route('oxpus_dlext_details', $return_parameters) . '">', '</a>');

		meta_refresh(3, $this->helper->route('oxpus_dlext_details', $return_parameters));

		trigger_error($message);
	}

	public function delete_comment($cat_id, $df_id, $dl_id = 0)
	{
		$this->set_auth_comments($cat_id);

		if (!$this->dl_manage_comments)
		{
			return 'view';
		}

		// Delete comment by poster or admin or dl_mod
		if (confirm_box($this->dlext_constants::DL_TRUE))
		{
			$sql = 'DELETE FROM ' . $this->dlext_table_dl_comments . '
				WHERE cat_id = ' . (int) $cat_id . '
					AND id = ' . (int) $df_id . '
					AND dl_id = ' . (int) $dl_id;
			$this->db->sql_query($sql);

			$sql = 'SELECT dl_id FROM ' . $this->dlext_table_dl_comments . '
				WHERE cat_id = ' . (int) $cat_id . '
					AND id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);
			$total_comments = $this->db->sql_affectedrows();
			$this->db->sql_freeresult($result);

			$this->notification->delete_notifications([
				'oxpus.dlext.notification.type.capprove',
				'oxpus.dlext.notification.type.comments',
			], $dl_id);

			if (!$total_comments)
			{
				redirect($this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]));
			}
			else
			{
				return 'view';
			}
		}
		else
		{
			$s_hidden_fields = [
				'cat_id'	=> $cat_id,
				'df_id'		=> $df_id,
				'dl_id'		=> $dl_id,
				'action'	=> 'delete',
				'view'		=> 'comment'
			];

			confirm_box($this->dlext_constants::DL_FALSE, $this->language->lang('DELETE_MESSAGE_CONFIRM'), build_hidden_fields($s_hidden_fields), '@oxpus_dlext/helpers/dl_confirm_body.html');
		}
	}

	public function display_post_form($action, $cat_id, $df_id, $dl_id = 0)
	{
		$this->set_auth_comments($cat_id);

		if ($this->dl_deny_post)
		{
			return 'view';
		}

		if ($action == 'edit')
		{
			$sql = 'SELECT comment_text, com_uid, com_flags FROM ' . $this->dlext_table_dl_comments . '
				WHERE dl_id = ' . (int) $dl_id . '
					AND id = ' . (int) $df_id . '
					AND cat_id = ' . (int) $cat_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);

			$comment_text	= $row['comment_text'];
			$com_uid		= $row['com_uid'];
			$com_flags		= $row['com_flags'];

			$this->db->sql_freeresult($result);

			$text_ary		= generate_text_for_edit($comment_text, $com_uid, $com_flags);
			$comment_text	= $text_ary['text'];
		}
		else
		{
			$comment_text = '';
		}

		$s_hidden_fields = [
			'dl_id'		=> $dl_id,
			'df_id'		=> $df_id,
			'cat_id'	=> $cat_id,
			'action'	=> 'save',
			'view'		=> 'comment'
		];

		add_form_key('dl_comment_posting', '_COMMENT');

		// Status for HTML, BBCode, Smilies, Images and Flash
		$bbcode_status	= ($this->config['allow_bbcode']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
		$smilies_status	= ($bbcode_status && $this->config['allow_smilies']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
		$img_status		= ($bbcode_status) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
		$url_status		= ($this->config['allow_post_links']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
		$flash_status	= ($bbcode_status && $this->config['allow_post_flash']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
		$quote_status	= $this->dlext_constants::DL_TRUE;

		// Smilies Block
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

		$this->template->assign_vars([
			'DL_COMMENT_TEXT'		=> $comment_text,

			'S_BBCODE_ALLOWED'		=> $bbcode_status,
			'S_BBCODE_IMG'			=> $img_status,
			'S_BBCODE_URL'			=> $url_status,
			'S_BBCODE_FLASH'		=> $flash_status,
			'S_BBCODE_QUOTE'		=> $quote_status,

			'S_DL_COMMENT_POST_ACTION'	=> $this->helper->route('oxpus_dlext_details'),
			'S_DL_HIDDEN_POST_FIELDS'	=> build_hidden_fields($s_hidden_fields),

			'U_DL_MORE_SMILIES'		=> $this->helper->route('oxpus_dlext_details', ['action' => 'smilies']),
		]);
	}

	public function display_comments($cat_id, $df_id, $start)
	{
		$index		= $this->dlext_main->full_index($cat_id);
		$cat_auth	= $this->dlext_auth->dl_cat_auth($cat_id);

		$this->set_auth_comments($cat_id);

		$sql = 'SELECT * FROM ' . $this->dlext_table_dl_comments . '
			WHERE cat_id = ' . (int) $cat_id . '
				AND id = ' . (int) $df_id . '
				AND approve = 1';
		$result = $this->db->sql_query($sql);
		$real_comment_exists = $this->db->sql_affectedrows();
		$this->db->sql_freeresult($result);

		if ($real_comment_exists > $this->config['dl_links_per_page'])
		{
			$this->pagination->generate_template_pagination(
				$this->helper->route('oxpus_dlext_details', ['view' => 'comment', 'action' => 'view', 'cat_id' => $cat_id, 'df_id' => $df_id]),
				'pagination',
				'start',
				$real_comment_exists,
				$this->config['dl_links_per_page'],
				$start
			);

			$this->template->assign_vars([
				'DL_PAGE_NUMBER'	=> $this->pagination->on_page($real_comment_exists, $this->config['dl_links_per_page'], $start),
				'DL_TOTAL_DL'		=> $this->language->lang('DL_COMMENTS_COUNT', $real_comment_exists),
			]);
		}

		if ($real_comment_exists)
		{
			$this->template->assign_var('S_VIEW_COMMENTS', $this->dlext_constants::DL_TRUE);

			$sql = 'SELECT c.*, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height FROM ' . $this->dlext_table_dl_comments . ' c
				LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = c.user_id
				WHERE cat_id = ' . (int) $cat_id . '
					AND id = ' . (int) $df_id . '
					AND approve = 1
				ORDER BY comment_time DESC';
			$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);

			$dl_ids = [];

			while ($row = $this->db->sql_fetchrow($result))
			{
				$avatar_row = [
					'avatar'		=> $row['user_avatar'],
					'avatar_type'	=> $row['user_avatar_type'],
					'avatar_width'	=> $row['user_avatar_width'],
					'avatar_height'	=> $row['user_avatar_height'],
				];

				$poster_id			= $row['user_id'];
				$poster				= $row['username'];
				$poster_color		= $row['user_colour'];
				$poster_avatar		= phpbb_get_avatar($avatar_row, $poster, $this->dlext_constants::DL_FALSE, $this->dlext_constants::DL_TRUE);
				$dl_id				= $row['dl_id'];

				$message			= $row['comment_text'];
				$com_uid			= $row['com_uid'];
				$com_bitfield		= $row['com_bitfield'];
				$com_flags			= (isset($row['com_flags'])) ? $row['com_flags'] : 0;

				$message			= censor_text($message);
				$message			= generate_text_for_display($message, $com_uid, $com_bitfield, $com_flags);

				$comment_time		= $row['comment_time'];
				$comment_edit_time	= $row['comment_edit_time'];

				if ($comment_time != $comment_edit_time)
				{
					$edited_by = $this->language->lang('DL_COMMENT_EDITED', $this->user->format_date($comment_edit_time));
				}
				else
				{
					$edited_by = '';
				}

				$u_delete_comment	= $this->helper->route('oxpus_dlext_details', ['view' => 'comment', 'action' => 'delete', 'cat_id' => $cat_id, 'df_id' => $df_id, 'dl_id' => $dl_id]);
				$u_edit_comment		= $this->helper->route('oxpus_dlext_details', ['view' => 'comment', 'action' => 'edit', 'cat_id' => $cat_id, 'df_id' => $df_id, 'dl_id' => $dl_id]);

				$decoded_message = censor_text($row['comment_text']);
				decode_message($decoded_message, $row['com_uid']);

				$this->template->assign_block_vars('dl_comment_row', [
					'DL_EDITED_BY'		=> $edited_by,
					'DL_POSTER'			=> get_username_string('full', $poster_id, $poster, $poster_color),
					'DL_POSTER_ID'		=> $poster_id,
					'DL_POSTER_NAME'	=> $poster,
					'DL_POSTER_AVATAR'	=> $poster_avatar,
					'DL_MESSAGE'		=> $message,
					'DL_POST_TIME'		=> $this->user->format_date($comment_time),
					'DL_POST_TIME_RAW'	=> $comment_time,
					'DL_POST_TIME_RFC'	=> gmdate(DATE_RFC3339, $comment_time),
					'DL_ID'				=> $dl_id,
					'DL_DECODED_TEXT'	=> $decoded_message,

					'U_DL_DELETE_COMMENT'	=> $u_delete_comment,
					'U_DL_EDIT_COMMENT'		=> ($this->dl_deny_post) ? '' : $u_edit_comment,
				]);

				$dl_ids[] = $dl_id;

				if (($poster_id == $this->user->data['user_id'] || $cat_auth['auth_mod'] || $index[$cat_id]['auth_mod'] || $this->dlext_auth->user_admin()) && $this->user->data['is_registered'] && !$this->dl_deny_post)
				{
					$this->template->assign_block_vars('dl_comment_row.dl_action_button', []);
				}
			}

			$this->db->sql_freeresult($result);

			$this->notification->delete_notifications('oxpus.dlext.notification.type.comments', $dl_ids, $this->dlext_constants::DL_FALSE, $this->user->data['user_id']);
		}

		return $real_comment_exists;
	}
}
