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

if (isset($this->user->data['user_wrong_email']))
{
	if ($this->user->data['user_wrong_email'] != 0)
	{
		trigger_error($this->language->lang('DL_NO_PERMISSION'));
	}
}

/*
* init and get various values
*/
$submit		= $this->request->variable('submit', '');
$preview	= $this->request->variable('preview', '');
$cancel		= $this->request->variable('cancel', '');
$confirm	= $this->request->variable('confirm', '');
$delete		= $this->request->variable('delete', '');
$cdelete	= $this->request->variable('cdelete', '');
$save		= $this->request->variable('save', '');
$post		= $this->request->variable('post', '');
$view		= $this->request->variable('view', '');
$show		= $this->request->variable('show', '');
$order		= $this->request->variable('order', '');
$action		= $this->request->variable('action', '');
$save		= $this->request->variable('save', '');
$goback		= $this->request->variable('goback', '');
$edit		= $this->request->variable('edit', '');
$bt_show	= $this->request->variable('bt_show', '');
$move		= $this->request->variable('move', '');
$fmove		= $this->request->variable('fmove', '');
$lock		= $this->request->variable('lock', '');
$sort		= $this->request->variable('sort', '');
$code		= $this->request->variable('code', '');
$sid		= $this->request->variable('sid', '');

$df_id		= $this->request->variable('df_id', 0);
$new_cat	= $this->request->variable('new_cat', 0);
$cat		= $this->request->variable('cat', 0);
$cat_id		= $this->request->variable('cat_id', 0);
$fav_id		= $this->request->variable('fav_id', 0);
$dl_id		= $this->request->variable('dl_id', 0);
$start		= $this->request->variable('start', 0);
$sort_by	= $this->request->variable('sort_by', 0);
$del_file	= $this->request->variable('del_file', 0);
$bt_filter	= $this->request->variable('bt_filter', -1);
$modcp		= $this->request->variable('modcp', 0);
$next_id	= $this->request->variable('next_id', 0);

$dlo_id		= $this->request->variable('dlo_id', array(0 => 0));

$file_option	= $this->request->variable('file_ver_opt', 0);
$file_version	= $this->request->variable('file_version', 0);
$file_ver_del	= $this->request->variable('file_ver_del', array(0));

$dl_mod_is_active = true;
$dl_mod_link_show = true;
$dl_mod_is_active_for_admins = false;

$page_start = max($start - 1, 0) * $this->config['dl_links_per_page'];
$start = $page_start;

if ($cat < 0)
{
	$cat = 0;
}

if (!$this->config['dl_active'])
{
	if ($this->config['dl_off_now_time'])
	{
		$dl_mod_is_active = false;
	}
	else
	{
		$curr_time = (date('H', time()) * 60) + date('i', time());
		$off_from = (substr($this->config['dl_off_from'], 0, 2) * 60) + (substr($this->config['dl_off_from'], -2));
		$off_till = (substr($this->config['dl_off_till'], 0, 2) * 60) + (substr($this->config['dl_off_till'], -2));

		if ($curr_time >= $off_from && $curr_time < $off_till)
		{
			$dl_mod_is_active = false;
		}
	}
}

if (!$dl_mod_is_active && $this->auth->acl_get('a_') && $this->config['dl_on_admins'])
{
	$dl_mod_is_active = true;
	$dl_mod_is_active_for_admins = true;
}

if (!$dl_mod_is_active && $this->config['dl_off_hide'])
{
	$dl_mod_link_show = false;
}

if ($this->user->data['is_bot'])
{
	$nav_view = '';
	$dl_mod_link_show = false;
	$dl_mod_is_active = false;
	$dl_mod_is_active_for_admins = false;
}

if (!$this->config['dl_global_guests'] && !$this->user->data['is_registered'])
{
	$nav_view = '';
	$dl_mod_link_show = false;
	$dl_mod_is_active = false;
	$dl_mod_is_active_for_admins = false;
}

if (!isset($nav_view))
{
	$nav_view = '';
}

if ($nav_view != 'bug_tracker')
{
	if ($dl_mod_is_active_for_admins)
	{
		$this->template->assign_var('S_DL_MOD_OFFLINE_ADMINS', true);
	}
	else
	{
		if (!$dl_mod_is_active && $dl_mod_link_show)
		{
			trigger_error($this->language->lang('DL_OFF_MESSAGE'));
		}

		if (!$dl_mod_is_active)
		{
			redirect($this->root_path . 'index.' . $this->php_ext);
		}
	}
}

include_once($this->root_path . 'includes/functions_user.' . $this->php_ext);
include_once($this->root_path . 'includes/functions_display.' . $this->php_ext);
include_once($this->root_path . 'includes/bbcode.' . $this->php_ext);

/*
* get the needed index
*/
$index = array();

switch ($nav_view)
{
	case 'details':
	case 'latest':
	case 'load':
	case 'modcp':
	case 'overall':
	case 'tracker':
	case 'thumbs':
	case 'upload':

		$index = $this->dlext_main->full_index();
	break;

	default:

		$index = ($cat) ? $this->dlext_main->index($cat) : $this->dlext_main->index();
}

if ($nav_view <> 'broken' && $nav_view <> 'load')
{
	$sql_where = '';

	if (!$this->user->data['is_registered'])
	{
		$sql = 'SELECT session_id FROM ' . SESSIONS_TABLE . '
			WHERE session_user_id = ' . ANONYMOUS;
		$result = $this->db->sql_query($sql);

		$guest_sids = array('0');

		while ($row = $this->db->sql_fetchrow($result))
		{
			$guest_sids[] = $row['session_id'];
		}
		$this->db->sql_freeresult($result);

		$sql_where = ' OR ' . $this->db->sql_in_set('session_id', $guest_sids, true);
	}

	$sql = 'DELETE FROM ' . DL_HOTLINK_TABLE . '
		WHERE user_id = ' . (int) $this->user->data['user_id'] . "
			$sql_where";
	$this->db->sql_query($sql);
}

$u_mcp_link	= append_sid("{$this->root_path}mcp.$this->php_ext", 'i=-oxpus-dlext-mcp-main_module&amp;mode=mcp_manage', true, $this->user->session_id);
if (!isset($mcp_cat))
{
	$mcp_cat = ($cat_id) ? $cat_id : $cat;
}

$this->template->assign_vars(array(
	'EXT_DL_PATH_WEB'	=> $this->ext_path_web,
	'EXT_DL_PATH_AJAX'	=> $this->ext_path_ajax,
	'ICON_DL_HELP'		=> '<i class="icon fa-info-circle fa-fw dl-icon-yellow"></i>',

	'U_MCP'				=> ($mcp_cat && $this->dlext_auth->user_auth($mcp_cat, 'auth_mod')) ? $u_mcp_link . '&amp;cat_id=' . $mcp_cat : $u_mcp_link,
	'U_HELP_POPUP'		=> $this->helper->route('oxpus_dlext_help'),
));
