<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
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
* fetch strings
*/
$action				= $this->request->variable('action', '');
$add				= $this->request->variable('add', '');
$cancel				= $this->request->variable('cancel', '');
$cat_icon			= $this->request->variable('cat_icon', '', true);
$cat_name			= $this->request->variable('cat_name', '', true);
$cat_traffic_range	= $this->request->variable('cat_traffic_range', '');
$check_user			= $this->request->variable('check_user', '', true);
$confirm			= $this->request->variable('confirm', '');
$data_user_range	= $this->request->variable('data_user_range', '');
$del_real_thumbs	= $this->request->variable('del_real_thumbs', '', true);
$delete				= $this->request->variable('delete', '');
$dir_name			= $this->request->variable('dir_name', '', true);
$dircreate			= $this->request->variable('dircreate', '');
$description		= $this->request->variable('description', '', true);
$edit				= $this->request->variable('edit', '');
$extension			= $this->request->variable('extension', '', true);
$file_assign		= $this->request->variable('file_assign', '');
$file_command		= $this->request->variable('file_command', '');
$file_extern_size	= $this->request->variable('file_extern_size', '');
$file_name			= $this->request->variable('file_name', '', true);
$filter_string		= $this->request->variable('filter_string', '');
$filtering			= $this->request->variable('filtering', '');
$func				= $this->request->variable('func', '');
$hack_author		= $this->request->variable('hack_author', '', true);
$hack_author_email	= $this->request->variable('hack_author_email', '', true);
$hack_author_web	= $this->request->variable('hack_author_website', '', true);
$hack_dl_url		= $this->request->variable('hack_dl_url', '', true);
$hack_version		= $this->request->variable('hack_version', '');
$idx_type			= $this->request->variable('type', 'c');
$long_desc			= $this->request->variable('long_desc', '', true);
$mod_desc			= $this->request->variable('mod_desc', '', true);
$mode				= $this->request->variable('mode', 'overview');
$move				= $this->request->variable('move', '');
$new_cat			= $this->request->variable('new_cat', '');
$new_path			= $this->request->variable('new_path', '');
$path				= $this->request->variable('path', '');
$require			= $this->request->variable('require', '', true);
$rules				= $this->request->variable('rules', '', true);
$save_cat			= $this->request->variable('save_cat', '');
$set_user			= $this->request->variable('set_user', '', true);
$sort_order			= $this->request->variable('sort_order', '');
$sorting			= $this->request->variable('sorting', '');
$submit				= $this->request->variable('submit', '');
$test				= $this->request->variable('test', '', true);
$tmp_m1				= $this->request->variable('edit_banlist', '');
$tmp_m2				= $this->request->variable('delete_banlist', '');
$todo				= $this->request->variable('todo', '', true);
$topic_drop_mode	= $this->request->variable('topic_drop_mode', 'drop');
$topic_text			= $this->request->variable('dl_topic_text', '', true);
$topic_user			= $this->request->variable('dl_topic_user', '', true);
$user_ip			= $this->request->variable('user_ip', '');
$username			= $this->request->variable('username', '', true);
$view				= $this->request->variable('view', 'general');
$warning			= $this->request->variable('warning', '', true);
$x					= $this->request->variable('x', '');
$y					= $this->request->variable('y', '');
$z					= $this->request->variable('z', '');

/*
* fetch numbers
*/
$all_traffic		= $this->request->variable('all_traffic', 0);
$allow_mod_desc		= $this->request->variable('allow_mod_desc', 0);
$allow_thumbs		= $this->request->variable('allow_thumbs', 0);
$approve			= $this->request->variable('approve', 0);
$approve_comments	= $this->request->variable('approve_comments', 0);
$auth_cread			= $this->request->variable('auth_cread', 3);
$auth_cpost			= $this->request->variable('auth_cpost', 3);
$auth_dl			= $this->request->variable('auth_dl', 0);
$auth_mod			= $this->request->variable('auth_mod', 0);
$auth_up			= $this->request->variable('auth_up', 0);
$auth_view			= $this->request->variable('auth_view', 0);
$ban_id				= $this->request->variable('ban_id', 0);
$bug_tracker		= $this->request->variable('bug_tracker', 0);
$cat_id				= $this->request->variable('cat_id', 0);
$cat_parent			= $this->request->variable('parent', 0);
$cat_traffic		= $this->request->variable('cat_traffic', 0);
$change_time		= $this->request->variable('change_time', 0);
$click_reset		= $this->request->variable('click_reset', 0);
$comments			= $this->request->variable('comments', 1);
$del_file			= $this->request->variable('del_file', 0);
$del_stat			= $this->request->variable('del_stat', 0);
$del_thumb			= $this->request->variable('del_thumb', 0);
$df_id				= $this->request->variable('df_id', 0);
$diff_topic_user	= $this->request->variable('diff_topic_user', $this->config['dl_diff_topic_user']);
$disable_pnotify	= $this->request->variable('disable_popup_notify', 0);
$file_extern		= $this->request->variable('file_extern', 0);
$file_free			= $this->request->variable('file_free', 0);
$file_option		= $this->request->variable('file_ver_opt', 0);
$file_traffic		= $this->request->variable('file_traffic', 0);
$file_version		= $this->request->variable('file_version', 0);
$group_id			= $this->request->variable('g', 0);
$group_traffic		= $this->request->variable('group_traffic', 0);
$guests				= $this->request->variable('guests', 0);
$hacklist			= $this->request->variable('hacklist', 0);
$mod_list			= $this->request->variable('mod_list', 0);
$new_cat_id			= $this->request->variable('new_cat_id', 0);
$must_approve		= $this->request->variable('must_approve', 1);
$perms_copy_from	= $this->request->variable('perms_copy_from', 0);
$send_notify		= $this->request->variable('send_notify', 0);
$set_add			= $this->request->variable('set_add', 0);
$show_guests		= $this->request->variable('show_guests', 0);
$show_file_hash		= $this->request->variable('show_file_hash', 0);
$start				= $this->request->variable('start', 0);
$statistics			= $this->request->variable('statistics', 1);
$stats_prune		= $this->request->variable('stats_prune', 100000);
$topic_forum		= $this->request->variable('dl_topic_forum', 0);
$topic_more_details	= $this->request->variable('topic_more_details', 1);
$topic_type			= $this->request->variable('topic_type', POST_NORMAL);
$user_auto_traffic	= $this->request->variable('user_dl_auto_traffic', 0);
$user_id			= $this->request->variable('user_id', 0);
$user_traffic		= $this->request->variable('user_traffic', 0);
$view_perm			= $this->request->variable('view_perm', 0);

/*
* fetch arrays
*/
$ban_id_ary			= $this->request->variable('ban_id', [0]);
$data_group_range	= $this->request->variable('data_group_range', ['']);
$del_id				= $this->request->variable('del_id', [0]);
$extension_ary		= $this->request->variable('extension', [''], true);
$file_ver_del		= $this->request->variable('file_ver_del', [0]);
$files				= $this->request->variable('files', [''], true);
$group_traffic_ary	= $this->request->variable('group_dl_auto_traffic', [0]);
$s_presel_cats		= $this->request->variable('cat_select', [0]);
$s_presel_groups	= $this->request->variable('group_select', [0]);
$thumbs				= $this->request->variable('thumb', [''], true);

/*
* initiate the help system
*/
$this->template->assign_vars([
	'ICON_DL_HELP'		=> '<i class="icon fa-info-circle fa-fw"></i>',
	'DL_MOD_RELEASE'	=> $this->language->lang('DL_MOD_VERSION', $this->config['dl_ext_version']),

	'U_HELP_POPUP'		=> $this->helper->route('oxpus_dlext_help'),
]);
