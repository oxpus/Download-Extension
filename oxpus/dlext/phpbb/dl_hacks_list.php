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
* init and get various values
*/
$sort_by	= $this->request->variable('sort_by', '');
$order		= $this->request->variable('order', '');
$start		= $this->request->variable('start', 0);

switch ($sort_by)
{
	case 1:
		$sql_sort_by = 'long_desc';
		break;
	case 2:
		$sql_sort_by = 'hack_author';
		break;
	default:
		$sql_sort_by = 'description';
}

$sql_order = ($order) ? $order : 'ASC';

$hacklist = array();
$hacklist = \oxpus\dlext\phpbb\classes\ dl_hacklist::hacks_index();
$status = $this->config['dl_use_hacklist'];

if (!$status || !sizeof($hacklist))
{
	redirect(append_sid($this->root_path . 'index.' . $this->php_ext));
}

page_header($this->language->lang('DL_HACKS_LIST'));

$this->template->set_filenames(array(
	'body' => 'hacks_list_body.html')
);

$dl_files = array();
$dl_files = \oxpus\dlext\phpbb\classes\ dl_hacklist::all_files($sql_sort_by, $sql_order, $start, $this->config['dl_links_per_page']);

$all_files = array();
$all_files = \oxpus\dlext\phpbb\classes\ dl_hacklist::all_files('id', 'ASC');

if ($all_files > $this->config['dl_links_per_page'])
{
	$pagination = $this->phpbb_container->get('pagination');
	$pagination->generate_template_pagination(
		array(
			'routes' => array(
				'oxpus_dlext_controller',
				'oxpus_dlext_page_controller',
			),
			'params' => array('view' => 'hacks', 'sort_by' => $sort_by, 'order' => $order),
		), 'pagination', 'start', $all_files, $this->config['dl_links_per_page'], $page_start);
		
	$this->template->assign_vars(array(
		'PAGE_NUMBER'	=> $pagination->on_page($all_files, $this->config['dl_links_per_page'], $page_start),
		'TOTAL_DL'		=> $this->language->lang('VIEW_DL_STATS', $all_files),
	));
}

$selected_0 = ($sort_by == 0) ? ' selected="selected"' : '';
$selected_1 = ($sort_by == 1) ? ' selected="selected"' : '';
$selected_2 = ($sort_by == 2) ? ' selected="selected"' : '';

$selected_sort_0 = ($order == 'ASC') ? ' selected="selected"' : '';
$selected_sort_1 = ($order == 'DESC') ? ' selected="selected"' : '';

$this->template->assign_vars(array(
	'SELECTED_0'		=> $selected_0,
	'SELECTED_1'		=> $selected_1,
	'SELECTED_2'		=> $selected_2,

	'SELECTED_SORT_0'	=> $selected_sort_0,
	'SELECTED_SORT_1'	=> $selected_sort_1,

	'S_FORM_ACTION'		=> $this->helper->route('oxpus_dlext_controller', array('view' => 'hacks')),
));

if (sizeof($dl_files))
{
	for ($i = 0; $i < sizeof($dl_files); $i++)
	{
		$cat_id = $dl_files[$i]['cat'];
		if ($hacklist[$cat_id])
		{
			$hack_name				= $dl_files[$i]['description'];
			$hack_author			= ($dl_files[$i]['hack_author'] != '') ? $dl_files[$i]['hack_author'] : 'n/a';
			$hack_author_email		= $dl_files[$i]['hack_author_email'];
			$hack_author_website	= $dl_files[$i]['hack_author_website'];
			$hackname				= ($dl_files[$i]['hacklist'] != '') ? '&nbsp;'.$dl_files[$i]['description'] : '';
			$hack_version			= ($dl_files[$i]['hacklist'] != '') ? '&nbsp;'.$dl_files[$i]['hack_version'] : '';
			$hack_dl_url			= $dl_files[$i]['hack_dl_url'];
			$description			= $dl_files[$i]['long_desc'];
			$uid					= $dl_files[$i]['long_desc_uid'];
			$bitfield				= $dl_files[$i]['long_desc_bitfield'];
			$flags					= $dl_files[$i]['long_desc_flags'];

			if ($uid)
			{
				$text_ary = generate_text_for_display($description, $uid, $bitfield, $flags);
				$description = (isset($text_ary['text'])) ? $text_ary['text'] : $description;
			}

			$this->template->assign_block_vars('listrow', array(
				'CAT_NAME'				=> $hacklist[$cat_id],
				'HACK_NAME'				=> $hackname . $hack_version,
				'HACK_DESCRIPTION'		=> $description,
				'HACK_AUTHOR'			=> ($hack_author_email != '') ? '<a href="mailto:' . $hack_author_email . '">'.$hack_author.'</a>' : $hack_author,
				'HACK_AUTHOR_WEBSITE'	=> ($hack_author_website != '') ? '<a href="' . $hack_author_website . '">' . $this->language->lang('DL_HACK_AUTOR_WEBSITE') . '</a>' : '',
				'HACK_DL_URL'			=> ($hack_dl_url != '') ? '<a href="' . $hack_dl_url . '">' . $this->language->lang('DL_DOWNLOAD') . '</a>' : '')
			);
		}
	}
}

page_footer();
