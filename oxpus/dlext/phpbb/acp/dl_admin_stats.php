<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* Connect to phpBB
*/
if ( !defined('IN_PHPBB') )
{
	exit;
}

$sorting = (!$sorting) ? 'username' : $sorting;
$sql_order_dir = ($sort_order === '') ? 'ASC' : $sort_order;

$del_id			= $request->variable('del_id', array(0));
$del_stat		= $request->variable('del_stat', 0);

if ($delete)
{
	if ($del_stat == 1)
	{
		$sql = 'DELETE FROM ' . DL_STATS_TABLE;
		$db->sql_query($sql);

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_STATS_ALL');
	}
	else if ($del_stat == 2)
	{
		$sql = 'DELETE FROM ' . DL_STATS_TABLE . '
			WHERE user_id = ' . ANONYMOUS;
		$db->sql_query($sql);

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_STATS_ANONYM');
	}
	else if (is_array($del_id) && sizeof($del_id))
	{
		$dl_id = array();
		foreach($del_id as $key => $value)
		{
			$dl_id[] = (int) $value;
		}

		$sql = 'DELETE FROM ' . DL_STATS_TABLE . '
			WHERE ' . $db->sql_in_set('dl_id', $dl_id);
		$db->sql_query($sql);

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_STATS_SOME');
	}
}

switch($sorting)
{
	case 'cat':
		$sql_order_by = 'cat_name ' . $sql_order_dir . ', time_stamp DESC';
		break;

	case 'id':
		$sql_order_by = 'description ' . $sql_order_dir . ', time_stamp DESC';
		break;

	case 'size':
		$sql_order_by = 'traffic ' . $sql_order_dir . ', time_stamp DESC';
		break;

	case 'ip':
		$sql_order_by = 'user_ip ' . $sql_order_dir . ', time_stamp DESC';
		break;

	case 'agent':
		$sql_order_by = 'browser ' . $sql_order_dir . ', time_stamp DESC';
		break;

	case 'time':
		$sql_order_by = 'time_stamp ' . $sql_order_dir;
		break;

	default:
		$sql_order_by = 'username ' . $sql_order_dir . ', time_stamp DESC';
}

$s_sort_order = '<select name="sorting">';
$s_sort_order .= '<option value="username">' . $language->lang('USERNAME') . '</option>';
$s_sort_order .= '<option value="id">' . $language->lang('DOWNLOADS') . '</option>';
$s_sort_order .= '<option value="cat">' . $language->lang('DL_CAT_NAME') . '</option>';
$s_sort_order .= '<option value="size">' . $language->lang('TRAFFIC') . '</option>';
$s_sort_order .= '<option value="ip">' . $language->lang('DL_IP') . '</option>';
$s_sort_order .= '<option value="agent">' . $language->lang('DL_BROWSER') . '</option>';
$s_sort_order .= '<option value="time">' . $language->lang('TIME') . '</option>';
$s_sort_order .= '</select>';
$s_sort_order = str_replace('value="' . $sorting . '">', 'value="' . $sorting . '" selected="selected">', $s_sort_order);

$s_sort_dir = '<select name="sort_order">';
$s_sort_dir .= '<option value="ASC">' . $language->lang('ASCENDING') . '</option>';
$s_sort_dir .= '<option value="DESC">' . $language->lang('DESCENDING') . '</option>';
$s_sort_dir .= '</select>';
$s_sort_dir = str_replace('value="' . $sort_order . '">', 'value="' . $sort_order . '" selected="selected">', $s_sort_dir);

switch($filtering)
{
	case 'cat':
		$search_filter_by = 'cat_name';
		$filter_by = 'cat';
		break;

	case 'id':
		$search_filter_by = 'description';
		$filter_by = 'id';
		break;

	case 'agent':
		$search_filter_by = 'browser';
		$filter_by = 'agent';
		break;

	case 'username':
		$search_filter_by = 'username';
		$filter_by = 'username';
		break;

	default:
		$search_filter_by = $filter_by = '';
}

$sql_where = '';

$s_filter = '<select name="filtering">';
$s_filter .= '<option value="-1">' . $language->lang('DL_NO_FILTER') . '</option>';
$s_filter .= '<option value="username">' . $language->lang('USERNAME') . '</option>';
$s_filter .= '<option value="id">' . $language->lang('DOWNLOADS') . '</option>';
$s_filter .= '<option value="cat">' . $language->lang('DL_CAT_NAME') . '</option>';
$s_filter .= '<option value="agent">' . $language->lang('DL_BROWSER') . '</option>';
$s_filter .= '</select>';
$s_filter = str_replace('value="' . $filtering . '">', 'value="' . $filtering . '" selected="selected">', $s_filter);

$template->set_filenames(array(
	'stats' => 'dl_stats_admin_body.html')
);

if (!$show_guests)
{
	$sql_where = ' s.user_id <> ' . ANONYMOUS;
}

$sql_array = array(
	'SELECT'	=> 's.*, d.description, c.cat_name, u.user_colour',

	'FROM'		=> array(DL_STATS_TABLE => 's'));

$sql_array['LEFT_JOIN'] = array();
$sql_array['LEFT_JOIN'][] = array(
	'FROM'		=> array(DL_CAT_TABLE => 'c'),
	'ON'		=> 'c.id = s.cat_id');
$sql_array['LEFT_JOIN'][] = array(
	'FROM'		=> array(DOWNLOADS_TABLE => 'd'),
	'ON'		=> 'd.id = s.id');
$sql_array['LEFT_JOIN'][] = array(
	'FROM'		=> array(USERS_TABLE => 'u'),
	'ON'		=> 'u.user_id = s.user_id');

$sql_array['WHERE'] = $sql_where;

$sql = $db->sql_build_query('SELECT', $sql_array);

$result = $db->sql_query($sql);
$total_data = $db->sql_affectedrows($result);

$helper = $phpbb_container->get('controller.helper');

if ($total_data)
{
	$search_ids = array();
	$search_result = false;

	$filter_string = str_replace('*', '', str_replace('%', '', strtolower($filter_string)));

	if ($search_filter_by && $filter_string)
	{
		while ($row = $db->sql_fetchrow($result))
		{
			$sql_search_string = strtolower($row[$search_filter_by]);
			if (strpos($sql_search_string, $filter_string) !== false)
			{
				$search_ids[] = $row['dl_id'];
				$search_result = true;
			}
		}
	}

	$db->sql_freeresult($result);

	if ($search_filter_by && $filter_string && $search_result)
	{
		$sql_array['WHERE'] .= (($sql_where) ? ' AND ' : '') . $db->sql_in_set('s.dl_id', $search_ids);
	}

	$sql_array['ORDER_BY'] = $sql_order_by;

	if ($start >= $total_data && $start >= $config['dl_links_per_page'])
	{
		$start -= $config['dl_links_per_page'];
	}

	$page_data = (sizeof($search_ids)) ? sizeof($search_ids) : $total_data;

	if ($page_data > $config['dl_links_per_page'])
	{
		$pagination = $phpbb_container->get('pagination');
		$pagination->generate_template_pagination(
			$this->u_action . "&amp;sorting=$sorting&amp;sort_order=$sort_order&amp;show_guests=$show_guests&amp;filtering=$filter_by&amp;filter_string=$filter_string",
			'pagination', 'start', $page_data, $config['dl_links_per_page'], $start);
		$template->assign_vars(array(
			'PAGE_NUMBER'	=> $pagination->on_page($page_data, $config['dl_links_per_page'], $start),
			'TOTAL_DL'		=> $language->lang('VIEW_DL_STATS', $page_data),
		));
	}

	$sql = $db->sql_build_query('SELECT', $sql_array);

	$result = $db->sql_query_limit($sql, $config['dl_links_per_page'], $start);

	$i = 0;
	while ($row = $db->sql_fetchrow($result))
	{
		switch ($row['direction'])
		{
			case 1:
				$direction = $language->lang('DL_UPLOAD_FILE');
			break;

			case 2:
				$direction = $language->lang('DL_STAT_EDIT');
			break;

			default:
				$direction = $language->lang('DL_DOWNLOAD');
		}

		$template->assign_block_vars('dl_stat_row', array(
			'CAT_NAME'		=> $row['cat_name'],
			'DESCRIPTION'	=> $row['description'],
			'USERNAME'		=> ($row['user_id'] == ANONYMOUS) ? $language->lang('GUEST') : get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
			'TRAFFIC'		=> ($row['traffic'] == -1) ? $language->lang('DL_EXTERN') : \oxpus\dlext\phpbb\classes\ dl_format::dl_size($row['traffic']),
			'DIRECTION'		=> $direction,
			'USER_IP'		=> $row['user_ip'],
			'BROWSER'		=> $row['browser'],
			'TIME_STAMP'	=> $user->format_date($row['time_stamp']),
			'ID'			=> $row['dl_id'],

			'U_CAT_LINK'	=> $helper->route('oxpus_dlext_controller', array('cat' => $row['cat_id'])),
			'U_DL_LINK'		=> $helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $row['id'])),
		));

		$i++;
	}

	$db->sql_freeresult($result);

	$template->assign_var('S_FILLED_FOOTER', true);
}
else
{
	$template->assign_var('S_NO_DL_STAT_ROW', true);
}

$template->assign_vars(array(
	'ICON_DL_DELETE'	=> $phpbb_admin_path . 'images/icon_delete.gif" alt="' . $language->lang('DELETE') . '" title="' . $language->lang('DELETE') . '"',

	'TOTAL_DATA'		=> $total_data,
	'FILTER_STRING'		=> $filter_string,

	'S_FILTER'			=> $s_filter,
	'S_SHOW_GUESTS'		=> ($show_guests) ? 'checked="checked"' : '',
	'S_FORM_ACTION'		=> $basic_link,
	'S_SORT_ORDER'		=> $s_sort_order,
	'S_SORT_DIR'		=> $s_sort_dir)
);

$template->assign_var('S_DL_STATS', true);

$template->assign_display('stats');
