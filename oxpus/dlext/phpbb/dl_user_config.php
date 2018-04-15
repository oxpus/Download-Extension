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

if (!$this->user->data['is_registered'])
{
      redirect($this->helper->route('oxpus_dlext_controller'));
}

/*
* drop choosen favorites
*/
$fav_ids = $this->request->variable('fav_id', array(0));

if ($action == 'drop' && sizeof($fav_ids))
{
	$sql_fav_ids = array();
	for ($i = 0; $i < sizeof($fav_ids); $i++)
	{
		$sql_fav_ids[] = intval($fav_ids[$i]);
	}

	$sql = 'DELETE FROM ' . DL_FAVORITES_TABLE . '
		WHERE ' . $this->db->sql_in_set('fav_id', $sql_fav_ids) . '
			AND fav_user_id = ' . (int) $this->user->data['user_id'];
	$this->db->sql_query($sql);

	$action = '';
	$submit = '';
	$fav_ids = array();
}

//
// Pull all user config data
//
if ($submit)
{
	if (!check_form_key('dl_u_config'))
	{
		trigger_error('FORM_INVALID');
	}

	/*
	* save general configuration for current user
	*/
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

	/*
	* drop all choosen favorites
	*/
	$fav_id = $this->request->variable('fav_id', array(0 => ''));

	if (sizeof($fav_id))
	{
		$sql = 'DELETE FROM ' . DL_FAVORITES_TABLE . '
			WHERE ' . $this->db->sql_in_set('fav_id', $fav_id) . '
				AND fav_user_id = ' . (int) $this->user->data['user_id'];
		$this->db->sql_query($sql);
	}

	$message = $this->language->lang('DL_USER_CONFIG_SAVED', '<a href="' . $this->helper->route('oxpus_dlext_controller', array('view' => 'user_config')) . '">', '</a>');

	trigger_error($message);
}

add_form_key('dl_u_config');

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
$s_user_dl_sort_fix .= '<option value="0">'.$this->language->lang('DL_DEFAULT_SORT').'</option>';
$s_user_dl_sort_fix .= '<option value="1">'.$this->language->lang('DL_FILE_DESCRIPTION').'</option>';
$s_user_dl_sort_fix .= '<option value="2">'.$this->language->lang('DL_FILE_NAME').'</option>';
$s_user_dl_sort_fix .= '<option value="3">'.$this->language->lang('DL_KLICKS').'</option>';
$s_user_dl_sort_fix .= '<option value="4">'.$this->language->lang('DL_FREE').'</option>';
$s_user_dl_sort_fix .= '<option value="5">'.$this->language->lang('DL_EXTERN').'</option>';
$s_user_dl_sort_fix .= '<option value="6">'.$this->language->lang('DL_FILE_SIZE').'</option>';
$s_user_dl_sort_fix .= '<option value="7">'.$this->language->lang('LAST_UPDATED').'</option>';
$s_user_dl_sort_fix .= '<option value="8">'.$this->language->lang('DL_RATING').'</option>';
$s_user_dl_sort_fix .= '</select>';
$s_user_dl_sort_fix = str_replace('value="'.$this->user->data['user_dl_sort_fix'].'">', 'value="'.$this->user->data['user_dl_sort_fix'].'" selected="selected">', $s_user_dl_sort_fix);

$s_user_dl_sort_dir = '<select name="user_dl_sort_dir">';
$s_user_dl_sort_dir .= '<option value="0">'.$this->language->lang('ASCENDING').'</option>';
$s_user_dl_sort_dir .= '<option value="1">'.$this->language->lang('DESCENDING').'</option>';
$s_user_dl_sort_dir .= '</select>';
$s_user_dl_sort_dir = str_replace('value="'.$this->user->data['user_dl_sort_dir'].'">', 'value="'.$this->user->data['user_dl_sort_dir'].'" selected="selected">', $s_user_dl_sort_dir);

/*
* drop all unaccessable favorites
*/
$access_cat = array();
$access_cat = \oxpus\dlext\phpbb\classes\ dl_main::full_index($this->helper, 0, 0, 0, 1);

if (sizeof($access_cat))
{
	$sql = 'DELETE FROM ' . DL_FAVORITES_TABLE . '
		WHERE ' . $this->db->sql_in_set('fav_dl_cat', $access_cat, true) . '
			AND fav_user_id = ' . (int) $this->user->data['user_id'];
	$this->db->sql_query($sql);
}

/*
* fetch all favorite downloads
*/
$sql = 'SELECT f.fav_id, d.description, d.cat, d.id FROM ' . DL_FAVORITES_TABLE . ' f, ' . DOWNLOADS_TABLE . ' d
	WHERE f.fav_dl_id = d.id
		AND f.fav_user_id = ' . (int) $this->user->data['user_id'];
$result = $this->db->sql_query($sql);
$total_favorites = $this->db->sql_affectedrows($result);

if ($total_favorites)
{
	$this->template->assign_var('S_FAV_BLOCK', true);

	while ($row = $this->db->sql_fetchrow($result))
	{
		$tmp_nav	= $path_dl_array = array();
		$dl_nav		= \oxpus\dlext\phpbb\classes\ dl_nav::nav($this->helper, $row['cat'], 'links', $tmp_nav);

		$this->template->assign_block_vars('favorite_row', array(
			'DL_ID'			=> $row['fav_id'],
			'DL_CAT'		=> $dl_nav,
			'DOWNLOAD'		=> $row['description'],
			'U_DOWNLOAD'	=> $this->helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $row['id'], 'cat_id' => $row['cat'])),
		));
	}
}

$this->db->sql_freeresult($result);

$this->template->set_filenames(array(
	'body' => 'dl_user_config_body.html')
);

$this->template->assign_vars(array(
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

	'S_FORM_ACTION'						=> $this->helper->route('oxpus_dlext_controller', array('view' => 'user_config')),
));

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
