<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\ucp;

use Symfony\Component\DependencyInjection\Container;

class ucp_config_controller implements ucp_config_interface
{
	protected $u_action;

	/* @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\template\template */
	protected $template;

	/**
	* Constructor
	*
	* @param \phpbb\request\request_interface 		$request
	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param \phpbb\user							$user
	* @param \phpbb\language\language				$language
	* @param \phpbb\config\config					$config
	* @param \phpbb\template\template				$template
	*/
	public function __construct(
		\phpbb\request\request_interface $request,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\config\config $config,
		\phpbb\template\template $template
	)
	{
		$this->request		= $request;
		$this->db 			= $db;
		$this->user 		= $user;
		$this->language		= $language;
		$this->config 		= $config;
		$this->template 	= $template;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		/*
		* init and get various values
		*/
		$submit = $this->request->variable('submit', '');

		if ($submit)
		{
			if (!check_form_key('dl_ucp'))
			{
				trigger_error('FORM_INVALID');
			}

			$user_allow_new_download_popup	= $this->request->variable('user_allow_new_download_popup', 0);
			$user_allow_fav_download_popup	= $this->request->variable('user_allow_fav_download_popup', 0);
			$user_allow_new_download_email	= $this->request->variable('user_allow_new_download_email', 0);
			$user_allow_fav_download_email	= $this->request->variable('user_allow_fav_download_email', 0);
			$user_allow_fav_comment_email	= $this->request->variable('user_allow_fav_comment_email', 0);
			$user_dl_note_type				= $this->request->variable('user_dl_note_type', 0);
			$user_dl_sort_fix				= $this->request->variable('user_dl_sort_fix', 0);
			$user_dl_sort_opt				= $this->request->variable('user_dl_sort_opt', 0);
			$user_dl_sort_dir				= $this->request->variable('user_dl_sort_dir', 0);
			$user_dl_sub_on_index			= $this->request->variable('user_dl_sub_on_index', 0);

			$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
				'user_allow_new_download_popup'	=> $user_allow_new_download_popup,
				'user_allow_fav_download_popup'	=> $user_allow_fav_download_popup,
				'user_allow_new_download_email'	=> $user_allow_new_download_email,
				'user_allow_fav_download_email'	=> $user_allow_fav_download_email,
				'user_allow_fav_comment_email'	=> $user_allow_fav_comment_email,
				'user_dl_note_type'				=> $user_dl_note_type,
				'user_dl_sort_fix'				=> $user_dl_sort_fix,
				'user_dl_sort_opt'				=> $user_dl_sort_opt,
				'user_dl_sort_dir'				=> $user_dl_sort_dir,
				'user_dl_sub_on_index'			=> $user_dl_sub_on_index)) . ' WHERE user_id = ' . (int) $this->user->data['user_id'];
			$this->db->sql_query($sql);

			$message = $this->language->lang('DL_USER_CONFIG_SAVED', '<a href="' . $this->u_action . '">', '</a>');

			trigger_error($message);
		}

		add_form_key('dl_ucp');

		$allow_new_popup_yes	= ($this->user->data['user_allow_new_download_popup']) ? 'checked="checked"' : '';
		$allow_new_popup_no		= (!$this->user->data['user_allow_new_download_popup']) ? 'checked="checked"' : '';
		$allow_fav_popup_yes	= ($this->user->data['user_allow_fav_download_popup']) ? 'checked="checked"' : '';
		$allow_fav_popup_no		= (!$this->user->data['user_allow_fav_download_popup']) ? 'checked="checked"' : '';
		$allow_new_email_yes	= ($this->user->data['user_allow_new_download_email']) ? 'checked="checked"' : '';
		$allow_new_email_no		= (!$this->user->data['user_allow_new_download_email']) ? 'checked="checked"' : '';
		$allow_fav_email_yes	= ($this->user->data['user_allow_fav_download_email']) ? 'checked="checked"' : '';
		$allow_fav_email_no		= (!$this->user->data['user_allow_fav_download_email']) ? 'checked="checked"' : '';
		$allow_com_email_yes	= ($this->user->data['user_allow_fav_comment_email']) ? 'checked="checked"' : '';
		$allow_com_email_no		= (!$this->user->data['user_allow_fav_comment_email']) ? 'checked="checked"' : '';

		$user_dl_note_type_popup	= ($this->user->data['user_dl_note_type'] == 1) ? 'checked="checked"' : '';
		$user_dl_note_type_message	= ($this->user->data['user_dl_note_type'] == 0) ? 'checked="checked"' : '';
		$user_dl_note_type_notify	= ($this->user->data['user_dl_note_type'] == 2) ? 'checked="checked"' : '';
		$user_dl_sort_opt			= ($this->user->data['user_dl_sort_opt']) ? 'checked="checked"' : '';

		$user_dl_sub_on_index_yes	= ($this->user->data['user_dl_sub_on_index']) ? 'checked="checked"' : '';
		$user_dl_sub_on_index_no	= (!$this->user->data['user_dl_sub_on_index']) ? 'checked="checked"' : '';

		$s_user_dl_sort_fix = '<select name="user_dl_sort_fix">';
		$s_user_dl_sort_fix .= '<option value="0">' . $this->language->lang('DL_DEFAULT_SORT') . '</option>';
		$s_user_dl_sort_fix .= '<option value="1">' . $this->language->lang('DL_FILE_DESCRIPTION') . '</option>';
		$s_user_dl_sort_fix .= '<option value="2">' . $this->language->lang('DL_FILE_NAME') . '</option>';
		$s_user_dl_sort_fix .= '<option value="3">' . $this->language->lang('DL_KLICKS') . '</option>';
		$s_user_dl_sort_fix .= '<option value="4">' . $this->language->lang('DL_FREE') . '</option>';
		$s_user_dl_sort_fix .= '<option value="5">' . $this->language->lang('DL_EXTERN') . '</option>';
		$s_user_dl_sort_fix .= '<option value="6">' . $this->language->lang('DL_FILE_SIZE') . '</option>';
		$s_user_dl_sort_fix .= '<option value="7">' . $this->language->lang('LAST_UPDATED') . '</option>';
		$s_user_dl_sort_fix .= '<option value="8">' . $this->language->lang('DL_RATING') . '</option>';
		$s_user_dl_sort_fix .= '</select>';
		$s_user_dl_sort_fix = str_replace('value="' . $this->user->data['user_dl_sort_fix'] . '">', 'value="' . $this->user->data['user_dl_sort_fix'] . '" selected="selected">', $s_user_dl_sort_fix);

		$s_user_dl_sort_dir = '<select name="user_dl_sort_dir">';
		$s_user_dl_sort_dir .= '<option value="0">'.$this->language->lang('ASCENDING').'</option>';
		$s_user_dl_sort_dir .= '<option value="1">'.$this->language->lang('DESCENDING').'</option>';
		$s_user_dl_sort_dir .= '</select>';
		$s_user_dl_sort_dir = str_replace('value="' . $this->user->data['user_dl_sort_dir'] . '">', 'value="' . $this->user->data['user_dl_sort_dir'] . '" selected="selected">', $s_user_dl_sort_dir);

		if (!$this->config['dl_disable_email'])
		{
			$this->template->assign_var('S_NO_DL_EMAIL_NOTIFY', true);
		}

		if (!$this->config['dl_disable_popup'])
		{
			$this->template->assign_var('S_NO_DL_POPUP_NOTIFY', true);
		}

		if (!$this->config['dl_sort_preform'])
		{
			$this->template->assign_var('S_SORT_CONFIG_OPTIONS', true);
		}

		$this->template->assign_var('S_DL_UCP_CONFIG', true);

		add_form_key('dl_ucp');

		$this->template->assign_vars(array(
			'DL_MOD_RELEASE'					=> $this->language->lang('DL_MOD_VERSION_PUBLIC'),

			'ALLOW_NEW_DOWNLOAD_POPUP_YES'		=> $allow_new_popup_yes,
			'ALLOW_NEW_DOWNLOAD_POPUP_NO'		=> $allow_new_popup_no,
			'ALLOW_FAV_DOWNLOAD_POPUP_YES'		=> $allow_fav_popup_yes,
			'ALLOW_FAV_DOWNLOAD_POPUP_NO'		=> $allow_fav_popup_no,

			'ALLOW_NEW_DOWNLOAD_EMAIL_YES'		=> $allow_new_email_yes,
			'ALLOW_NEW_DOWNLOAD_EMAIL_NO'		=> $allow_new_email_no,
			'ALLOW_FAV_DOWNLOAD_EMAIL_YES'		=> $allow_fav_email_yes,
			'ALLOW_FAV_DOWNLOAD_EMAIL_NO'		=> $allow_fav_email_no,
			'ALLOW_FAV_COMMENT_EMAIL_YES'		=> $allow_com_email_yes,
			'ALLOW_FAV_COMMENT_EMAIL_NO'		=> $allow_com_email_no,

			'USER_DL_NOTE_TYPE_POPUP'			=> $user_dl_note_type_popup,
			'USER_DL_NOTE_TYPE_MESSAGE'			=> $user_dl_note_type_message,
			'USER_DL_NOTE_TYPE_NOTIFY'			=> $user_dl_note_type_notify,

			'USER_DL_SUB_ON_INDEX_YES'			=> $user_dl_sub_on_index_yes,
			'USER_DL_SUB_ON_INDEX_NO'			=> $user_dl_sub_on_index_no,

			'S_DL_SORT_USER_OPT'				=> $s_user_dl_sort_fix,
			'S_DL_SORT_USER_EXT'				=> $user_dl_sort_opt,
			'S_DL_SORT_USER_DIR'				=> $s_user_dl_sort_dir,

			'S_FORM_ACTION'						=> $this->u_action,
		));
	}
}
