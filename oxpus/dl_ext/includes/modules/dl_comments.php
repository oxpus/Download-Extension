<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* connect to phpBB
*/
if ( !defined('IN_PHPBB') )
{
	exit;
}

/*
* check general permissions
*/
if (!\oxpus\dl_ext\includes\classes\ dl_auth::cat_auth_comment_read($cat_id))
{
	trigger_error('DL_NO_PERMISSION');
}

$cat_auth = array();
$cat_auth = \oxpus\dl_ext\includes\classes\ dl_auth::dl_cat_auth($cat_id);

if (!$cat_auth['auth_view'] && !$index[$cat_id]['auth_view'] && !$this->auth->acl_get('a_'))
{
	trigger_error('DL_NO_PERMISSION');
}

/*
* redirect to download details if comments are disabled for this category
*/
if (!$index[$cat_id]['comments'])
{
	$view = 'detail';
	$action = '';
}

/*
* redirect to comments list if comment editing was canceled
*/
if ($goback)
{
	$view = 'comment';
	$action = 'view';
	$cancel = '';
}

/*
* someone cancel a job? list the list again and again...
*/
if ($cancel && $action == 'delete')
{
	$action = 'view';
}

/*
* take the message if entered
*/
$comment_text = $this->request->variable('message', '', true);

$dl_files = array();
$dl_files = \oxpus\dl_ext\includes\classes\ dl_files::all_files(0, '', 'ASC', '', $df_id, $modcp, '*');

if (!$dl_files)
{
	redirect($this->helper->route('dl_ext_controller'));
}

$this->template->assign_vars(array(
	'DESCRIPTION'			=> $description,
	'MINI_IMG'				=> $mini_icon,
	'HACK_VERSION'			=> $hack_version,
	'STATUS'				=> $status,
));

/*
* check permissions to manage comments
*/
$sql = 'SELECT user_id FROM ' . DL_COMMENTS_TABLE . '
	WHERE id = ' . (int) $df_id . '
		AND dl_id = ' . (int) $dl_id . '
		AND approve = ' . true . '
		AND cat_id = ' . (int) $cat_id;
$result = $this->db->sql_query($sql);
$row_user = $this->db->sql_fetchfield('user_id');
$this->db->sql_freeresult($result);

$allow_manage = 0;
if (($row_user == $this->user->data['user_id'] || $cat_auth['auth_mod'] || $index[$cat_id]['auth_mod'] || $this->auth->acl_get('a_')) && $this->user->data['is_registered'])
{
	$allow_manage = true;
}

$deny_post = false;
if (!\oxpus\dl_ext\includes\classes\ dl_auth::cat_auth_comment_post($cat_id))
{
	$allow_manage = 0;
	$deny_post = true;
}

/*
* open the comments view for this download if allowed
*/
if ($action)
{
	$inc_module = true;
	$page_title = $this->language->lang('DL_COMMENTS');

	if ($action == 'save' && !$deny_post)
	{
		// check form
		if (!check_form_key('posting'))
		{
			trigger_error($this->language->lang('FORM_INVALID'), E_USER_WARNING);
		}
	
		$allow_bbcode	= ($this->config['allow_bbcode']) ? true : false;
		$allow_urls		= true;
		$allow_smilies	= ($this->config['allow_smilies']) ? true : false;
		$com_uid		= '';
		$com_bitfield	= '';
		$com_flags		= 0;
	
		generate_text_for_storage($comment_text, $com_uid, $com_bitfield, $com_flags, $allow_bbcode, $allow_urls, $allow_smilies);
	
		$sql = 'SELECT description FROM ' . DOWNLOADS_TABLE . '
			WHERE id = ' . (int) $df_id;
		$result = $this->db->sql_query($sql);
		$description = $this->db->sql_fetchfield('description');
		$this->db->sql_freeresult($result);
	
		if ($index[$cat_id]['approve_comments'] || \oxpus\dl_ext\includes\classes\ dl_auth::user_admin())
		{
			$approve = true;
		}
		else
		{
			$approve = 0;
		}
	
		if ($dl_id)
		{
			$sql = 'UPDATE ' . DL_COMMENTS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
				'comment_edit_time'	=> time(),
				'comment_text'		=> $comment_text,
				'com_uid'			=> $com_uid,
				'com_bitfield'		=> $com_bitfield,
				'com_flags'			=> $com_flags,
				'approve'			=> $approve)) . ' WHERE dl_id = ' . (int) $dl_id;
			$this->db->sql_query($sql);
	
			$comment_message = $this->language->lang('DL_COMMENT_UPDATED');
		}
		else
		{
			$sql = 'INSERT INTO ' . DL_COMMENTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
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
				'approve'			=> $approve));
			$this->db->sql_query($sql);
	
			$comment_message = $this->language->lang('DL_COMMENT_ADDED');
		}
	
		if (!$approve)
		{
			$processing_user = (\oxpus\dl_ext\includes\classes\ dl_auth::cat_auth_comment_read($cat_id) == 3) ? 0 : \oxpus\dl_ext\includes\classes\ dl_auth::dl_auth_users($cat_id, 'auth_mod');
	
			$sql = 'SELECT user_email, username, user_lang FROM ' . USERS_TABLE . '
				WHERE ' . $this->db->sql_in_set('user_id', explode(',', $processing_user)) . '
					OR user_type = ' . USER_FOUNDER;

			$mail_data = array(
				'query'				=> $sql,
				'email_template'	=> 'downloads_approve_comment',
				'cat_id'			=> $cat_id,
				'df_id'				=> $df_id,
				'description'		=> $description,
				'cat_name'			=> $index[$cat_id]['cat_name_nav'],
			);						

			\oxpus\dl_ext\includes\classes\ dl_email::send_comment_notify($mail_data, $this->helper, $ext_path);

			$approve_message	= '';
			$return_parameters	= array('view' => 'comment', 'action' => 'view', 'cat_id' => $cat_id, 'df_id' => $df_id);
			$return_text		= $this->language->lang('CLICK_RETURN_COMMENTS');
		}
		else
		{
			$sql = 'SELECT fav_user_id FROM ' . DL_FAVORITES_TABLE . '
				WHERE fav_dl_id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);
	
			$fav_user = array();
	
			while ($row = $this->db->sql_fetchrow($result))
			{
				$fav_user[] = $row['fav_user_id'];
			}
	
			$this->db->sql_freeresult($result);
	
			if (!$this->config['dl_disable_email'] && $fav_user)
			{
				$sql_fav_user = (sizeof($fav_user)) ? ' AND ' . $this->db->sql_in_set('user_id', $fav_user) : '';
				$com_perms = $index[$cat_id]['auth_cread'];
				$sql_user = '';
	
				switch ($com_perms)
				{
					case 0:
					case 1:
						if ($sql_fav_user)
						{
							$sql_user = $sql_fav_user;
							$send_notify = true;
						}
						else
						{
							$send_notify = false;
						}
					break;
	
					case 2:
						$processing_user = \oxpus\dl_ext\includes\classes\ dl_auth::dl_auth_users($cat_id, 'auth_mod');
						if ($processing_user)
						{
							$sql_user .= ' AND ' . $this->db->sql_in_set('user_id', explode(',', $processing_user));
							$send_notify = true;
						}
	
						if ($sql_fav_user)
						{
							$sql_user .= $sql_fav_user;
							$send_notify = true;
						}
						else
						{
							$send_notify = false;
						}
					break;
	
					case 3:
					default:
						$sql_user = '';
						$send_notify = false;
					break;
				}
	
				if ($send_notify)
				{
					$sql = 'SELECT user_email, username, user_lang FROM ' . USERS_TABLE . '
						WHERE user_id <> ' . (int) $this->user->data['user_id'] . ' 
							AND user_allow_fav_download_email = 1 
							AND user_allow_fav_comment_email = 1' . $sql_fav_user;

					$mail_data = array(
						'query'				=> $sql,
						'email_template'	=> 'downloads_comment_notify',
						'cat_id'			=> $cat_id,
						'df_id'				=> $df_id,
						'description'		=> $description,
						'cat_name'			=> $index[$cat_id]['cat_name_nav'],
					);						

					\oxpus\dl_ext\includes\classes\ dl_email::send_comment_notify($mail_data, $this->helper, $ext_path);
				}
			}
	
			$approve_message	= '<br />' . $this->language->lang('DL_MUST_BE_APPROVE_COMMENT');
			$return_parameters	= array('view' => 'detail', 'df_id' => $df_id);
			$return_text		= $this->language->lang('CLICK_RETURN_DOWNLOAD_DETAILS');
		}
	
		$message = $comment_message . $approve_message . '<br /><br />' . sprintf($return_text, '<a href="' . $this->helper->route('dl_ext_controller', $return_parameters) . '">', '</a>');
	
		meta_refresh(3, $this->helper->route('dl_ext_controller', $return_parameters));
	
		trigger_error($message);
	}
	
	if ($action == 'delete' && $allow_manage)
	{
		// Delete comment by poster or admin or dl_mod
		if (!$confirm)
		{
			// Confirm deletion
			$s_hidden_fields = array(
				'cat_id'	=> $cat_id,
				'df_id'		=> $df_id,
				'dl_id'		=> $dl_id,
				'action'	=> 'delete',
				'view'		=> 'comment'
			);
	
			$this->language->add_lang('posting');
	
			$this->template->set_filenames(array(
				'body' => 'dl_confirm_body.html')
			);
	
			page_header($this->language->lang('DOWNLOADS') . ' :: ' . $this->language->lang('DELETE_MESSAGE'));
	
			add_form_key('dl_com_del');
	
			$this->template->assign_vars(array(
				'MESSAGE_TITLE' => $this->language->lang('DELETE_MESSAGE'),
				'MESSAGE_TEXT' => $this->language->lang('DELETE_MESSAGE_CONFIRM'),
	
				'S_CONFIRM_ACTION' => $this->helper->route('dl_ext_controller'),
				'S_HIDDEN_FIELDS' => build_hidden_fields($s_hidden_fields))
			);
	
			page_footer();
		}
		else
		{
			if (!check_form_key('dl_com_del'))
			{
				trigger_error('FORM_INVALID');
			}
	
			$sql = 'DELETE FROM ' . DL_COMMENTS_TABLE . '
				WHERE cat_id = ' . (int) $cat_id . '
					AND id = ' . (int) $df_id . '
					AND dl_id = ' . (int) $dl_id;
			$this->db->sql_query($sql);
	
			$sql = 'SELECT dl_id FROM ' . DL_COMMENTS_TABLE . '
				WHERE cat_id = ' . (int) $cat_id . '
					AND id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);
			$total_comments = $this->db->sql_affectedrows($result);
			$this->db->sql_freeresult($result);
	
			if (!$total_comments)
			{
				redirect($this->helper->route('dl_ext_controller', array('view' => 'detail' , 'df_id' => $df_id)));
			}
			else
			{
				$action = 'view';
			}
		}
	}
	
	if (($action == 'edit' && $allow_manage) || ($action == 'post' && !$deny_post))
	{
		// Edit or add a comment
		if ($action == 'edit')
		{
			$sql = 'SELECT comment_text, com_uid, com_flags FROM ' . DL_COMMENTS_TABLE . '
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
	
		$s_hidden_fields = array(
			'dl_id'		=> $dl_id,
			'df_id'		=> $df_id,
			'cat_id'	=> $cat_id,
			'action'	=> 'save',
			'view'		=> 'comment'
		);
	
		$this->template->set_filenames(array(
			'body' => 'dl_edit_comments_body.html')
		);
	
		add_form_key('posting');
	
		// Status for HTML, BBCode, Smilies, Images and Flash
		$bbcode_status	= ($this->config['allow_bbcode']) ? true : false;
		$smilies_status	= ($bbcode_status && $this->config['allow_smilies']) ? true : false;
		$img_status		= ($bbcode_status) ? true : false;
		$url_status		= ($this->config['allow_post_links']) ? true : false;
		$flash_status	= ($bbcode_status && $this->config['allow_post_flash']) ? true : false;
		$quote_status	= true;
	
		// Smilies Block
		include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		generate_smilies('inline', 0);
	
		// BBCode-Block
		$this->language->add_lang('posting');
		display_custom_bbcodes();
	
		$this->template->assign_vars(array(
			'COMMENT_TEXT'		=> $comment_text,
	
			'S_BBCODE_ALLOWED'	=> $bbcode_status,
			'S_BBCODE_IMG'		=> $img_status,
			'S_BBCODE_URL'		=> $url_status,
			'S_BBCODE_FLASH'	=> $flash_status,
			'S_BBCODE_QUOTE'	=> $quote_status,
	
			'S_FORM_ACTION'		=> $this->helper->route('dl_ext_controller'),
			'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
	
			'U_MORE_SMILIES'	=> $this->helper->route('dl_ext_controller', array('action' => 'smilies')),
		));
	
		page_header($this->language->lang('DOWNLOADS') . ' :: ' . $this->language->lang('DL_COMMENT'));
	}
	
	if ($action == 'view' || !$action)
	{
		/*
		* view the comments - users default entry point
		*/
		$sql = 'SELECT * FROM ' . DL_COMMENTS_TABLE . '
			WHERE cat_id = ' . (int) $cat_id . '
				AND id = ' . (int) $df_id . '
				AND approve = ' . true;
		$result = $this->db->sql_query($sql);
		$total_comments = $this->db->sql_affectedrows($result);
		$this->db->sql_freeresult($result);

		if ($total_comments)
		{
			$comment_row = array();
	
			$sql = 'SELECT c.*, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height FROM ' . DL_COMMENTS_TABLE . ' c
				LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = c.user_id
				WHERE c.cat_id = ' . (int) $cat_id . '
					AND c.id = ' . (int) $df_id . '
					AND c.approve = ' . true . '
				ORDER BY c.comment_time DESC';
			$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);
	
			while ($row = $this->db->sql_fetchrow($result))
			{
				$comment_row[] = $row;
			}
			$this->db->sql_freeresult($result);
	
			if ($total_comments > $this->config['dl_links_per_page'])
			{
				$pagination = $this->phpbb_container->get('pagination');
				$pagination->generate_template_pagination(
					array(
						'routes' => array(
							'dl_ext_controller',
							'dl_ext_page_controller',
						),
						'params' => array('view' => 'comment', 'action' => 'view', 'cat_id' => $cat_id, 'df_id' => $df_id),
					), 'pagination', 'start', $total_comments, $this->config['dl_links_per_page'], $page_start);
					
				$this->template->assign_vars(array(
					'PAGE_NUMBER'	=> $pagination->on_page($total_comments, $this->config['dl_links_per_page'], $page_start),
					'TOTAL_DL'		=> $this->language->lang('VIEW_COMMENTS', $total_comments),
				));
			}
	
			$sql = 'SELECT description, desc_uid, desc_bitfield, desc_flags FROM ' . DOWNLOADS_TABLE . '
				WHERE id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
	
			$description	= $row['description'];
			$desc_uid		= $row['desc_uid'];
			$desc_bitfield	= $row['desc_bitfield'];
			$desc_flags		= $row['desc_flags'];
	
			$this->db->sql_freeresult($result);
	
			$cat_name = $index[$cat_id]['cat_name'];
			$cat_name = str_replace("&nbsp;&nbsp;|___&nbsp;", "", $cat_name);
	
			page_header($this->language->lang('DOWNLOADS') . ' :: ' . $this->language->lang('DL_COMMENTS'));
	
			$this->template->set_filenames(array(
				'body' => 'dl_view_comments_body.html')
			);
	
			$s_hidden_fields = array(
				'cat_id'	=> $cat_id,
				'df_id'		=> $df_id
			);
	
			$this->template->assign_vars(array(
				'L_POST_COMMENT'	=> ($deny_post) ? '' : $this->language->lang('DL_COMMENT_WRITE'),
	
				'CAT_NAME'			=> $cat_name,
				'DESCRIPTION'		=> generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags),
				'EDIT_IMG' 			=> $this->user->img('icon_post_edit', 'EDIT_POST'),
				'DELETE_IMG' 		=> $this->user->img('icon_post_delete', 'DELETE'),
	
				'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
				'S_FORM_ACTION'		=> $this->helper->route('dl_ext_controller', array('view' => 'comment')),
			));
	
			if (!$deny_post)
			{
				$this->template->assign_var('S_COMMENT_BUTTON', true);
			}
	
			for($i = 0; $i < sizeof($comment_row); $i++)
			{
				$poster_id			= $comment_row[$i]['user_id'];
				$poster_colour		= $comment_row[$i]['user_colour'];
				$poster_avatar		= ($this->user->optionget('viewavatars')) ? get_user_avatar($comment_row[$i]['user_avatar'], $comment_row[$i]['user_avatar_type'], $comment_row[$i]['user_avatar_width'], $comment_row[$i]['user_avatar_height']) : '';
				$poster				= $comment_row[$i]['username'];
				$dl_id				= $comment_row[$i]['dl_id'];
	
				$message			= $comment_row[$i]['comment_text'];
				$com_uid			= $comment_row[$i]['com_uid'];
				$com_bitfield		= $comment_row[$i]['com_bitfield'];
				$com_flags			= $comment_row[$i]['com_flags'];
				$comment_time		= $comment_row[$i]['comment_time'];
				$comment_edit_time	= $comment_row[$i]['comment_edit_time'];
	
				$message = censor_text($message);
				$message = generate_text_for_display($message, $com_uid, $com_bitfield, $com_flags);
	
				if($comment_time <> $comment_edit_time)
				{
					$edited_by = $this->language->lang('DL_COMMENT_EDITED', $this->user->format_date($comment_edit_time));
				}
				else
				{
					$edited_by = '';
				}
	
				if ($poster_id == 1)
				{
				}
				else
				{
					$poster		= get_username_string('full', $poster_id, $poster, $poster_colour);
				}
	
				$post_time = $this->user->format_date($comment_time);
	
				$u_delete_comment	= $this->helper->route('dl_ext_controller', array('view' => 'comment', 'action' => 'delete', 'cat_id' => $cat_id, 'df_id' => $df_id, 'dl_id' => $dl_id));
				$u_edit_comment		= $this->helper->route('dl_ext_controller', array('view' => 'comment', 'action' => 'edit', 'cat_id' => $cat_id, 'df_id' => $df_id, 'dl_id' => $dl_id));
	
				$this->template->assign_block_vars('comment_row', array(
					'EDITED_BY'			=> $edited_by,
					'POSTER'			=> $poster,
					'POSTER_AVATAR'		=> $poster_avatar,
					'MESSAGE'			=> $message,
					'POST_TIME'			=> $post_time,
					'DL_ID'				=> $dl_id,
	
					'U_DELETE_COMMENT'	=> $u_delete_comment,
					'U_EDIT_COMMENT'	=> ($deny_post) ? '' : $u_edit_comment)
				);
	
                if (($poster_id == $this->user->data['user_id'] || $cat_auth['auth_mod'] || $index[$cat_id]['auth_mod'] || $this->auth->acl_get('a_')) && $this->user->data['is_registered'] && !$deny_post)
				{
					$this->template->assign_block_vars('comment_row.action_button', array());
				}
			}
		}
		else
		{
			redirect($this->helper->route('dl_ext_controller', array('view' => 'detail', 'df_id' => $df_id)));
		}
	}
}
else
{
	if ($df_id)
	{
		redirect($this->helper->route('dl_ext_controller', array('view' => 'detail', 'df_id' => $df_id)));
	}
	else if ($cat_id)
	{
		redirect($this->helper->route('dl_ext_controller', array('cat' => $cat_id)));
	}
	else
	{
		redirect($this->helper->route('dl_ext_controller'));
	}
}
