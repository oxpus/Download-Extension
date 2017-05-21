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

$this->language->add_lang('search');

/*
* define initial search vars
*/
$search_keywords	= $this->request->variable('search_keywords', '', true);
$search_cat			= $this->request->variable('search_cat', -1);
$sort_dir			= $this->request->variable('sort_dir', 'ASC');
$search_in_fields	= $this->request->variable('search_fields', 'all');
$search_author		= $this->request->variable('search_author', '', true);

$search_fnames		= array(
	$this->language->lang('DL_ALL'),
	$this->language->lang('DL_FILE_NAME'),
	$this->language->lang('DL_FILE_DESCRIPTION'),
	$this->language->lang('DL_DETAIL'),
	$this->language->lang('DL_MOD_TEST'),
	$this->language->lang('DL_MOD_DESC'),
	$this->language->lang('DL_MOD_WARNING'),
	$this->language->lang('DL_MOD_TODO'),
	$this->language->lang('DL_MOD_REQUIRE')
);
$search_fields		= array('all', 'file_name', 'description', 'long_desc', 'test', 'mod_desc', 'warning', 'todo', 'req');
$search_type		= $this->request->variable('search_type', 0);

$submit = $this->request->variable('submit', '');

if ($submit)
{
	if (!check_form_key('dl_search'))
	{
		trigger_error('FORM_INVALID');
	}
}

/*
* search for keywords if entered
*/
if ($search_keywords != '' && !$search_author)
{
	$this->template->set_filenames(array(
		'body' => 'dl_search_results.html')
	);

	$search_keywords = str_replace(array('sql', 'union', '  ', ' ', '*', '?', '%'), ' ', strtolower($search_keywords));

	$access_cats		= array();
	$access_cats		= \oxpus\dlext\includes\classes\ dl_main::full_index($this->helper, 0, 0, 0, 1);
	$sql_access_cats	= ($this->auth->acl_get('a_') && $this->user->data['is_registered']) ? '' : ' AND ' . $this->db->sql_in_set('d.cat', $access_cats) . ' ';

	$sql_cat			= ($search_cat == -1) ? '' : ' AND d.cat = ' . (int) $search_cat;

	switch($search_in_fields)
	{
		case 'all':
			$sql_fields = 'd.file_name, d.description, d.long_desc, d.test, d.mod_desc, d.warning, d.todo, d.req';
			break;
		case 'file_name':
		case 'description':
		case 'long_desc':
		case 'test':
		case 'mod_desc':
		case 'warning':
		case 'todo':
		case 'req':
			$sql_fields = "d.$search_in_fields";
			break;
		default:
			trigger_error($this->language->lang('DL_NO_PERMISSION'));
	}

	$search_words = array_unique(explode(' ', $search_keywords));

	$sql = "SELECT d.id, $sql_fields FROM " . DOWNLOADS_TABLE . ' d
		WHERE d.approve = ' . true . "
		$sql_access_cats
		$sql_cat";
	$result = $this->db->sql_query($sql);
	$total_found_dl = $this->db->sql_affectedrows($result);

	$search_counter = 0;

	if ($total_found_dl)
	{
		$search_ids = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($search_in_fields == 'all')
			{
				$search_result = $row['file_name'] . $row['description'] . $row['long_desc'] . $row['test'] . $row['mod_desc'] . $row['warning'] . $row['todo'] . $row['req'];
			}
			else
			{
				$search_result = $row[$search_in_fields];
			}

			$counter = 0;
			for ($i = 0; $i < sizeof($search_words); $i++)
			{
				if (preg_match_all('/' . preg_quote($search_words[$i], '/') . '/iu', $search_result, $matches))
				{
					$counter++;
				}
			}

			switch ($search_type)
			{
				case 0:
					if ($counter == sizeof($search_words))
					{
						$search_ids[] = $row['id'];
						$search_counter++;
					}
				break;

				default:
					$search_ids[] = $row['id'];
					$search_counter++;
			}
		}
	}

	$this->db->sql_freeresult($result);

	if ($search_counter > $this->config['dl_links_per_page'])
	{
		$pagination = $this->phpbb_container->get('pagination');
		$pagination->generate_template_pagination(
			array(
				'routes' => array(
					'oxpus_dlext_controller',
					'oxpus_dlext_page_controller',
				),
				'params' => array('view' => 'search', 'search_keywords' => $search_keywords, 'search_cat' => $search_cat, 'sort_dir' => $sort_dir),
			), 'pagination', 'start', $search_counter, $this->config['dl_links_per_page'], $page_start);

		$this->template->assign_vars(array(
			'PAGE_NUMBER'	=> $pagination->on_page($search_counter, $this->config['dl_links_per_page'], $page_start),
			'TOTAL_DL'		=> $this->language->lang('VIEW_DOWNLOADS', $search_counter),
		));
	}

	if (!$search_counter)
	{
		$this->template->assign_var('S_NO_RESULTS', true);
	}
	else
	{
		$sql = 'SELECT d.*, c.cat_name FROM ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . ' c
			WHERE d.cat = c.id
				AND ' . $this->db->sql_in_set('d.id', $search_ids);
		$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);

		while ( $row = $this->db->sql_fetchrow($result) )
		{
			$cat_id			= $row['cat'];
			$file_id		= $row['id'];
			$u_file_link	= $this->helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $file_id));

			$dl_status		= array();
			$dl_status		= \oxpus\dlext\includes\classes\ dl_status::status($file_id, $this->helper);

			$status			= $dl_status['status'];
			$file_name		= $dl_status['file_name'];

			if (isset($index[$cat_id]['parent']))
			{
				$mini_icon = \oxpus\dlext\includes\classes\ dl_status::mini_status_file($index[$cat_id]['parent'], $file_id);
			}
			else
			{
				$mini_icon = '';
			}

			$cat_name			= $row['cat_name'];
			$u_cat_link			= $this->helper->route('oxpus_dlext_controller', array('cat' => $cat_id));

			$description		= $row['description'];
			$desc_uid			= $row['desc_uid'];
			$desc_bitfield		= $row['desc_bitfield'];
			$desc_flags			= $row['desc_flags'];
			$description		= censor_text($description);
			$description		= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

			$long_desc			= $row['long_desc'];
			$long_desc_uid		= $row['long_desc_uid'];
			$long_desc_bitfield	= $row['long_desc_bitfield'];
			$long_desc_flags	= $row['long_desc_flags'];
			$long_desc			= censor_text($long_desc);
			$long_desc			= generate_text_for_display($long_desc, $long_desc_uid, $long_desc_bitfield, $long_desc_flags);

			$this->template->assign_block_vars('searchresults', array(
				'STATUS'		=> $status,
				'CAT_NAME'		=> $cat_name,
				'DESCRIPTION'	=> $description,
				'MINI_ICON'		=> $mini_icon,
				'FILE_NAME'		=> $file_name,
				'LONG_DESC'		=> $long_desc,

				'U_CAT_LINK'	=> $u_cat_link,
				'U_FILE_LINK'	=> $u_file_link)
			);
		}
	}
}
else if ($search_author)
{
	$this->template->set_filenames(array(
		'body' => 'dl_search_results.html')
	);

	$search_author = str_replace('sql', '', $search_author);
	$search_author = str_replace('union', '', $search_author);
	$search_author = str_replace('*', '%', trim($search_author));

	$sql_cat		= ($search_cat == -1) ? '' : ' AND cat = ' . $search_cat;
	$sql_cat_count	= ($search_cat == -1) ? '' : ' AND cat = ' . $search_cat;

	$sql = 'SELECT user_id FROM ' . USERS_TABLE . '
		WHERE username ' . $this->db->sql_like_expression($this->db->any_char . $search_author . $this->db->any_char);
	$result = $this->db->sql_query($sql);
	$total_users = $this->db->sql_affectedrows($result);

	if ($total_users)
	{
		while ($row = $this->db->sql_fetchrow($result))
		{
			$matching_userids[] = $row['user_id'];
		}

		$this->db->sql_freeresult($result);
	}
	else
	{
		$this->db->sql_freeresult($result);
		trigger_error('NO_USER');
	}

	if (sizeof($matching_userids))
	{
		$sql_add_users = $this->db->sql_in_set('add_user', $matching_userids);
		$sql_change_users = $this->db->sql_in_set('change_user', $matching_userids);

		$sql_matching_users = ' AND ( ' . $sql_add_users . ' OR ' . $sql_change_users . ' ) ';
	}
	else
	{
		$sql_matching_users = '';
	}

	$access_cats		= array();
	$access_cats		= \oxpus\dlext\includes\classes\ dl_main::full_index($this->helper, 0, 0, 0, 1);

	$sql_access_cats	= ($this->auth->acl_get('a_') && $this->user->data['is_registered']) ? '' : ' AND ' . $this->db->sql_in_set('cat', $access_cats);
	$sql_access_dls		= ($this->auth->acl_get('a_') && $this->user->data['is_registered']) ? '' : ' AND ' . $this->db->sql_in_set('d.cat', $access_cats);

	$sql = 'SELECT id FROM ' . DOWNLOADS_TABLE . '
		WHERE approve = ' . true . "
			$sql_matching_users
			$sql_access_cats
			$sql_cat_count";
	$result = $this->db->sql_query($sql);
	$total_found_dl = $this->db->sql_affectedrows($result);
	$this->db->sql_freeresult($result);

	if ($total_found_dl > $this->config['dl_links_per_page'])
	{
		$pagination = $this->phpbb_container->get('pagination');
		$pagination->generate_template_pagination(
			array(
				'routes' => array(
					'oxpus_dlext_controller',
					'oxpus_dlext_page_controller',
				),
				'params' => array('view' => 'search', 'search_author' => $search_author, 'search_cat' => $search_cat, 'sort_dir' => $sort_dir),
			), 'pagination', 'start', $total_found_dl, $this->config['dl_links_per_page'], $page_start);

		$this->template->assign_vars(array(
			'PAGE_NUMBER'	=> $pagination->on_page($total_found_dl, $this->config['dl_links_per_page'], $page_start),
			'TOTAL_DL'		=> $this->language->lang('VIEW_DOWNLOADS', $total_found_dl),
		));
	}

	if ($total_found_dl == 0)
	{
		$this->template->assign_var('S_NO_RESULTS', true);
	}
	else
	{
		$sql = 'SELECT d.*, c.cat_name FROM ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . ' c
			WHERE d.cat = c.id
				AND d.approve = ' . true . "
				$sql_matching_users
				$sql_access_dls
				$sql_cat
			ORDER BY c.cat_name, d.sort $sort_dir";
		$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);

		while ( $row = $this->db->sql_fetchrow($result) )
		{
			$cat_id			= $row['cat'];
			$file_id		= $row['id'];
			$u_file_link	= $this->helper->route('oxpus_dlext_controller', array('view' => 'detail', 'df_id' => $file_id));

			$dl_status		= array();
			$dl_status		= \oxpus\dlext\includes\classes\ dl_status::status($file_id, $this->helper);

			$status			= $dl_status['status'];
			$file_name		= $dl_status['file_name'];

			$mini_icon		= (isset($index[$cat_id]['parent'])) ? \oxpus\dlext\includes\classes\ dl_status::mini_status_file($index[$cat_id]['parent'], $file_id) : '';

			$cat_name		= $row['cat_name'];
			$u_cat_link		= $this->helper->route('oxpus_dlext_controller', array('cat' => $cat_id));

			$description	= $row['description'];
			$desc_uid		= $row['desc_uid'];
			$desc_bitfield	= $row['desc_bitfield'];
			$desc_flags		= $row['desc_flags'];
			$description	= censor_text($description);
			$description	= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

			$long_desc			= $row['long_desc'];
			$long_desc_uid		= $row['long_desc_uid'];
			$long_desc_bitfield	= $row['long_desc_bitfield'];
			$long_desc_flags	= $row['long_desc_flags'];
			$long_desc			= censor_text($long_desc);
			$long_desc			= generate_text_for_display($long_desc, $long_desc_uid, $long_desc_bitfield, $long_desc_flags);

			$this->template->assign_block_vars('searchresults', array(
				'STATUS'		=> $status,
				'CAT_NAME'		=> $cat_name,
				'DESCRIPTION'	=> $description,
				'MINI_ICON'		=> $mini_icon,
				'FILE_NAME'		=> $file_name,
				'LONG_DESC'		=> $long_desc,

				'U_CAT_LINK'	=> $u_cat_link,
				'U_FILE_LINK'	=> $u_file_link)
			);
		}
	}
}
else
{
	/*
	* default entry point of download searching
	*/
	$select_categories = '<select name="search_cat"><option value="-1">' . $this->language->lang('DL_ALL') . '</option>';
	$select_categories .= \oxpus\dlext\includes\classes\ dl_extra::dl_dropdown(0, 0, 0, 'auth_view');
	$select_categories .= '</select>';

	$s_sort_dir = '<select name="sort_dir">';
	if($sort_dir == 'ASC')
	{
		$s_sort_dir .= '<option value="ASC" selected="selected">' . $this->language->lang('ASCENDING') . '</option><option value="DESC">' . $this->language->lang('DESCENDING') . '</option>';
	}
	else
	{
		$s_sort_dir .= '<option value="ASC">' . $this->language->lang('ASCENDING') . '</option><option value="DESC" selected="selected">' . $this->language->lang('DESCENDING') . '</option>';
	}
	$s_sort_dir .= '</select>';

	$s_search_fields = '<select name="search_fields">';

	for ($i = 0; $i < sizeof($search_fields); $i++)
	{
		$s_search_fields .= '<option value="'.$search_fields[$i].'">'.$search_fnames[$i].'</option>';
	}
	$s_search_fields .= '</select>';

	$this->template->set_filenames(array(
		'body' => 'dl_search_body.html')
	);

	add_form_key('dl_search');

	$this->template->assign_vars(array(
		'S_DL_SEARCH_ACTION'	=> $this->helper->route('oxpus_dlext_controller', array('view' => 'search')),
		'S_DL_CATEGORY_OPTIONS'	=> $select_categories,
		'S_DL_SORT_ORDER'		=> $s_sort_dir,
		'S_DL_SORT_OPTIONS'		=> $s_search_fields)
	);
}
