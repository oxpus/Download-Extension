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

if ($this->config['dl_sort_preform'])
{
	$sort_by = 0;
	$order = 'ASC';
}
else
{
	$sort_by = (!$sort_by) ? $this->user->data['user_dl_sort_fix'] : $sort_by;
	$order = (!$order) ? (($this->user->data['user_dl_sort_dir']) ? 'DESC' : 'ASC') : $order;
}

switch ($sort_by)
{
	case 1:
		$sql_sort_by = 'description';
		break;
	case 2:
		$sql_sort_by = 'file_name';
		break;
	case 3:
		$sql_sort_by = 'klicks';
		break;
	case 4:
		$sql_sort_by = 'free';
		break;
	case 5:
		$sql_sort_by = 'extern';
		break;
	case 6:
		$sql_sort_by = 'file_size';
		break;
	case 7:
		$sql_sort_by = 'change_time';
		break;
	case 8:
		$sql_sort_by = 'rating';
		break;
	default:
		$sql_sort_by = 'sort';
}

$sql_order = ($order == 'DESC') ? 'DESC' : 'ASC';

if (!$this->config['dl_sort_preform'] && $this->user->data['user_dl_sort_opt'])
{
	$this->template->assign_var('S_SORT_OPTIONS', true);

	$selected_0 = ($sort_by == 0) ? ' selected="selected"' : '';
	$selected_1 = ($sort_by == 1) ? ' selected="selected"' : '';
	$selected_2 = ($sort_by == 2) ? ' selected="selected"' : '';
	$selected_3 = ($sort_by == 3) ? ' selected="selected"' : '';
	$selected_4 = ($sort_by == 4) ? ' selected="selected"' : '';
	$selected_5 = ($sort_by == 5) ? ' selected="selected"' : '';
	$selected_6 = ($sort_by == 6) ? ' selected="selected"' : '';
	$selected_7 = ($sort_by == 7) ? ' selected="selected"' : '';
	$selected_8 = ($sort_by == 8) ? ' selected="selected"' : '';

	$selected_sort_0 = ($order == 'ASC') ? ' selected="selected"' : '';
	$selected_sort_1 = ($order == 'DESC') ? ' selected="selected"' : '';

	$this->template->assign_vars(array(
		'SELECTED_0'		=> $selected_0,
		'SELECTED_1'		=> $selected_1,
		'SELECTED_2'		=> $selected_2,
		'SELECTED_3'		=> $selected_3,
		'SELECTED_4'		=> $selected_4,
		'SELECTED_5'		=> $selected_5,
		'SELECTED_6'		=> $selected_6,
		'SELECTED_7'		=> $selected_7,
		'SELECTED_8'		=> $selected_8,

		'SELECTED_SORT_0'	=> $selected_sort_0,
		'SELECTED_SORT_1'	=> $selected_sort_1,
	));
}
else
{
	$s_sort_by = '';
	$s_order = '';
}
