<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\ucp;

/**
* @package acp
*/
class main_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $cache, $dl_cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;
		global $phpbb_container, $phpbb_extension_manager, $phpbb_log, $phpbb_path_helper;

		$config				= $phpbb_container->get('config');
		$language			= $phpbb_container->get('language');
		$request			= $phpbb_container->get('request');
		$template			= $phpbb_container->get('template');

		// Define the ext path
		$ext_path					= $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$ext_path_web				= $phpbb_path_helper->update_web_root_path($ext_path);
		$ext_path_ajax				= $ext_path_web . 'includes/js/ajax/';

		include_once($ext_path . '/includes/helpers/dl_constants.' . $phpEx);

		// Define the basic file storage placement
		if ($config['dl_download_dir'] == 2)
		{
			$filebase_prefix = $phpbb_root_path . 'store/oxpus/dlext/';
		}
		else
		{
			$filebase_prefix = $ext_path . 'files/';
		}

		define('DL_EXT_CACHE_FOLDER',	$filebase_prefix . 'cache/');
		define('DL_EXT_THUMBS_FOLDER',	$filebase_prefix . 'thumbs/');
		define('DL_EXT_FILES_FOLDER',	$filebase_prefix . 'downloads/');

		$this->tpl_name = 'dl_user_config_body';

		$user->data['dl_enable_desc'] = false;
		$user->data['dl_enable_rule'] = false;

		/*
		* include and create the main class
		*/
		include($ext_path . 'includes/classes/class_dlmod.' . $phpEx);
		$dl_mod = new \oxpus\dlext\includes\classes\ dl_mod($phpbb_root_path, $phpEx, $ext_path);
		$dl_mod->register();
		\oxpus\dlext\includes\classes\ dl_init::init($ext_path);

		$submit			= $request->variable('submit', '');
		$cancel			= $request->variable('cancel', '');
		$confirm		= $request->variable('confirm', '');
		$mode			= $request->variable('mode', 'config');

		$basic_link = $this->u_action . "&amp;mode=$mode";

		$template->assign_var('S_DL_UCP', true);

		/*
		* include the choosen module
		*/
		switch($mode)
		{
			case 'config':
				$this->page_title = 'DL_CONFIG';

				$template->assign_var('S_DL_UCP_CONFIG', true);

				if ($submit)
				{
					if (!check_form_key('dl_ucp'))
					{
						trigger_error('FORM_INVALID');
					}

					$user_allow_new_download_popup	= $request->variable('user_allow_new_download_popup', 0);
					$user_allow_fav_download_popup	= $request->variable('user_allow_fav_download_popup', 0);
					$user_allow_new_download_email	= $request->variable('user_allow_new_download_email', 0);
					$user_allow_fav_download_email	= $request->variable('user_allow_fav_download_email', 0);
					$user_allow_fav_comment_email	= $request->variable('user_allow_fav_comment_email', 0);
					$user_dl_note_type				= $request->variable('user_dl_note_type', 0);
					$user_dl_sort_fix				= $request->variable('user_dl_sort_fix', 0);
					$user_dl_sort_opt				= $request->variable('user_dl_sort_opt', 0);
					$user_dl_sort_dir				= $request->variable('user_dl_sort_dir', 0);
					$user_dl_sub_on_index			= $request->variable('user_dl_sub_on_index', 0);

					$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
						'user_allow_new_download_popup'	=> $user_allow_new_download_popup,
						'user_allow_fav_download_popup'	=> $user_allow_fav_download_popup,
						'user_allow_new_download_email'	=> $user_allow_new_download_email,
						'user_allow_fav_download_email'	=> $user_allow_fav_download_email,
						'user_allow_fav_comment_email'	=> $user_allow_fav_comment_email,
						'user_dl_note_type'				=> $user_dl_note_type,
						'user_dl_sort_fix'				=> $user_dl_sort_fix,
						'user_dl_sort_opt'				=> $user_dl_sort_opt,
						'user_dl_sort_dir'				=> $user_dl_sort_dir,
						'user_dl_sub_on_index'			=> $user_dl_sub_on_index)) . ' WHERE user_id = ' . (int) $user->data['user_id'];
					$db->sql_query($sql);

					$message = $language->lang('DL_USER_CONFIG_SAVED', '<a href="' . $basic_link . '">', '</a>');

					trigger_error($message);
				}

				add_form_key('dl_ucp');

				$allow_new_popup_yes	= ($user->data['user_allow_new_download_popup']) ? 'checked="checked"' : '';
				$allow_new_popup_no		= (!$user->data['user_allow_new_download_popup']) ? 'checked="checked"' : '';
				$allow_fav_popup_yes	= ($user->data['user_allow_fav_download_popup']) ? 'checked="checked"' : '';
				$allow_fav_popup_no		= (!$user->data['user_allow_fav_download_popup']) ? 'checked="checked"' : '';
				$allow_new_email_yes	= ($user->data['user_allow_new_download_email']) ? 'checked="checked"' : '';
				$allow_new_email_no		= (!$user->data['user_allow_new_download_email']) ? 'checked="checked"' : '';
				$allow_fav_email_yes	= ($user->data['user_allow_fav_download_email']) ? 'checked="checked"' : '';
				$allow_fav_email_no		= (!$user->data['user_allow_fav_download_email']) ? 'checked="checked"' : '';
				$allow_com_email_yes	= ($user->data['user_allow_fav_comment_email']) ? 'checked="checked"' : '';
				$allow_com_email_no		= (!$user->data['user_allow_fav_comment_email']) ? 'checked="checked"' : '';

				$user_dl_note_type_popup	= ($user->data['user_dl_note_type'] == 1) ? 'checked="checked"' : '';
				$user_dl_note_type_message	= ($user->data['user_dl_note_type'] == 0) ? 'checked="checked"' : '';
				$user_dl_note_type_notify	= ($user->data['user_dl_note_type'] == 2) ? 'checked="checked"' : '';
				$user_dl_sort_opt			= ($user->data['user_dl_sort_opt']) ? 'checked="checked"' : '';

				$user_dl_sub_on_index_yes	= ($user->data['user_dl_sub_on_index']) ? 'checked="checked"' : '';
				$user_dl_sub_on_index_no	= (!$user->data['user_dl_sub_on_index']) ? 'checked="checked"' : '';

				$s_user_dl_sort_fix = '<select name="user_dl_sort_fix">';
				$s_user_dl_sort_fix .= '<option value="0">'.$language->lang('DL_DEFAULT_SORT').'</option>';
				$s_user_dl_sort_fix .= '<option value="1">'.$language->lang('DL_FILE_DESCRIPTION').'</option>';
				$s_user_dl_sort_fix .= '<option value="2">'.$language->lang('DL_FILE_NAME').'</option>';
				$s_user_dl_sort_fix .= '<option value="3">'.$language->lang('DL_KLICKS').'</option>';
				$s_user_dl_sort_fix .= '<option value="4">'.$language->lang('DL_FREE').'</option>';
				$s_user_dl_sort_fix .= '<option value="5">'.$language->lang('DL_EXTERN').'</option>';
				$s_user_dl_sort_fix .= '<option value="6">'.$language->lang('DL_FILE_SIZE').'</option>';
				$s_user_dl_sort_fix .= '<option value="7">'.$language->lang('LAST_UPDATED').'</option>';
				$s_user_dl_sort_fix .= '<option value="8">'.$language->lang('DL_RATING').'</option>';
				$s_user_dl_sort_fix .= '</select>';
				$s_user_dl_sort_fix = str_replace('value="'.$user->data['user_dl_sort_fix'].'">', 'value="'.$user->data['user_dl_sort_fix'].'" selected="selected">', $s_user_dl_sort_fix);

				$s_user_dl_sort_dir = '<select name="user_dl_sort_dir">';
				$s_user_dl_sort_dir .= '<option value="0">'.$language->lang('ASCENDING').'</option>';
				$s_user_dl_sort_dir .= '<option value="1">'.$language->lang('DESCENDING').'</option>';
				$s_user_dl_sort_dir .= '</select>';
				$s_user_dl_sort_dir = str_replace('value="'.$user->data['user_dl_sort_dir'].'">', 'value="'.$user->data['user_dl_sort_dir'].'" selected="selected">', $s_user_dl_sort_dir);

				if (!$config['dl_disable_email'])
				{
					$template->assign_var('S_NO_DL_EMAIL_NOTIFY', true);
				}

				if (!$config['dl_disable_popup'])
				{
					$template->assign_var('S_NO_DL_POPUP_NOTIFY', true);
				}

				if (!$config['dl_sort_preform'])
				{
					$template->assign_var('S_SORT_CONFIG_OPTIONS', true);
				}

				$template->assign_vars(array(
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

					'S_FORM_ACTION'						=> $basic_link,
				));

			break;
			case 'favorite':
				$this->page_title = 'DL_FAVORITE';

				if ($submit)
				{
					if (!check_form_key('dl_ucp'))
					{
						trigger_error('FORM_INVALID');
					}

					/*
					* drop all choosen favorites
					*/
					$fav_id = $request->variable('fav_id', array(0 => ''));

					$sql_drop_fav = implode(',', array_map('intval',$fav_id));

					if ($sql_drop_fav)
					{
						$sql = "DELETE FROM " . DL_FAVORITES_TABLE . "
							WHERE fav_id IN ($sql_drop_fav)
								AND fav_user_id = " . (int) $user->data['user_id'];
						$db->sql_query($sql);
					}

					$message = $language->lang('DL_USER_CONFIG_SAVED', '<a href="' . $basic_link . '">', '</a>');

					trigger_error($message);
				}

				/*
				* drop all unaccessable favorites
				*/
				$access_cat = array();
				$access_cat = \oxpus\dlext\includes\classes\ dl_main::full_index('', 0, 0, 0, 1);
				if (sizeof($access_cat))
				{
					$sql_access_cat = implode(', ', array_map('intval', $access_cat));

					$sql = "DELETE FROM " . DL_FAVORITES_TABLE . "
						WHERE fav_dl_cat NOT IN ($sql_access_cat)
							AND fav_user_id = " . (int) $user->data['user_id'];
					$db->sql_query($sql);
				}

				/*
				* fetch all favorite downloads
				*/
				$sql = 'SELECT f.fav_id, d.description, d.cat, d.id FROM ' . DL_FAVORITES_TABLE . ' f, ' . DOWNLOADS_TABLE . ' d
					WHERE f.fav_dl_id = d.id
						AND f.fav_user_id = ' . (int) $user->data['user_id'];
				$result = $db->sql_query($sql);

				$total_favorites = $db->sql_affectedrows($result);

				$template->assign_var('S_FAV_BLOCK', true);

				if ($total_favorites)
				{
					while ($row = $db->sql_fetchrow($result))
					{
						$path_dl_array = $tmp_nav = array();
						$dl_nav = \oxpus\dlext\includes\classes\ dl_nav::nav('', $row['cat'], 'links', $tmp_nav).'&nbsp;&raquo;';

						$template->assign_block_vars('favorite_row', array(
							'DL_ID'			=> $row['fav_id'],
							'DL_CAT'		=> $dl_nav,
							'DOWNLOAD'		=> $row['description'],
							'U_DOWNLOAD'	=> 'app.' . $phpEx . '/dlext/?view=detail&amp;df_id=' . $row['id'] . '&amp;cat_id=' . $row['cat'],
						));
					}
				}
				$db->sql_freeresult($result);

				add_form_key('dl_ucp');

				$template->assign_vars(array(
					'S_FORM_ACTION'	=> $basic_link,
				));

			break;
		}

		$template->assign_vars(array(
			'DL_MOD_RELEASE' => $language->lang('DL_MOD_VERSION_PUBLIC'),
		));
	}
}
