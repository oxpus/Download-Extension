<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller;

use Symfony\Component\DependencyInjection\Container;

class search
{
	/* @var string phpBB root path */
	protected $root_path;

	/* @var string phpEx */
	protected $php_ext;

	/* @var Container */
	protected $phpbb_container;

	/* @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/* @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\language\language */
	protected $language;

	/** @var extension owned objects */
	protected $ext_path;
	protected $ext_path_web;
	protected $ext_path_ajax;

	protected $dlext_auth;
	protected $dlext_extra;
	protected $dlext_main;
	protected $dlext_status;

	/**
	* Constructor
	*
	* @param string									$root_path
	* @param string									$php_ext
	* @param Container 								$phpbb_container
	* @param \phpbb\extension\manager				$phpbb_extension_manager
	* @param \phpbb\path_helper						$phpbb_path_helper
	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param \phpbb\config\config					$config
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\auth\auth						$auth
	* @param \phpbb\request\request_interface 		$request
	* @param \phpbb\template\template				$template
	* @param \phpbb\user							$user
	* @param \phpbb\language\language				$language
	*/
	public function __construct(
		$root_path,
		$php_ext,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\auth\auth $auth,
		\phpbb\request\request_interface $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		$dlext_auth,
		$dlext_extra,
		$dlext_main,
		$dlext_status
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->phpbb_container 			= $phpbb_container;
		$this->phpbb_extension_manager 	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->auth						= $auth;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_extra				= $dlext_extra;
		$this->dlext_main				= $dlext_main;
		$this->dlext_status				= $dlext_status;
	}

	public function handle()
	{
		$nav_view = 'search';

		// Include the default base init script
		include_once($this->ext_path . 'phpbb/includes/base_init.' . $this->php_ext);

		/*
		* open the search for downloads
		*/
		$inc_module = true;
		page_header($this->language->lang('SEARCH').' '.$this->language->lang('DOWNLOADS'));

		$this->language->add_lang('search');

		/*
		* define initial search vars
		*/
		$search_keywords	= $this->request->variable('search_keywords', '', true);
		$search_cat			= $this->request->variable('search_cat', -1);
		$sort_dir			= $this->request->variable('sort_dir', 'ASC');
		$search_in_fields	= $this->request->variable('search_fields', 'all');
		$search_author		= $this->request->variable('search_author', '', true);
		$search_user		= $this->request->variable('search_user_id', 0);
		
		$search_fnames		= [
			$this->language->lang('DL_ALL'),
			$this->language->lang('DL_FILE_NAME'),
			$this->language->lang('DL_FILE_DESCRIPTION'),
			$this->language->lang('DL_DETAIL'),
			$this->language->lang('DL_MOD_TEST'),
			$this->language->lang('DL_MOD_DESC'),
			$this->language->lang('DL_MOD_WARNING'),
			$this->language->lang('DL_MOD_TODO'),
			$this->language->lang('DL_MOD_REQUIRE'),
		];

		$search_fields		= ['all', 'file_name', 'description', 'long_desc', 'test', 'mod_desc', 'warning', 'todo', 'req'];
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
		if ($search_keywords != '' && !$search_author && !$search_user)
		{
			$this->template->set_filenames(['body' => 'dl_search_results.html']);
		
			$search_keywords = str_replace(['sql', 'union', '  ', ' ', '*', '?', '%'], ' ', strtolower($search_keywords));
		
			$access_cats		= [];
			$access_cats		= $this->dlext_main->full_index(0, 0, 0, 1);
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
				$search_ids = [];
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
					$this->helper->route('oxpus_dlext_search', ['search_keywords' => $search_keywords, 'search_cat' => $search_cat, 'sort_dir' => $sort_dir]),
					'pagination',
					'start',
					$search_counter,
					$this->config['dl_links_per_page'],
					$page_start
				);
		
				$this->template->assign_vars([
					'PAGE_NUMBER'	=> $pagination->on_page($search_counter, $this->config['dl_links_per_page'], $page_start),
					'TOTAL_DL'		=> $this->language->lang('VIEW_DOWNLOADS', $search_counter),
				]);
			}
		
			if (!$search_counter)
			{
				$this->template->assign_var('S_NO_RESULTS', true);
			}
			else
			{
				$sql_array['SELECT'] = 'd.*, c.cat_name, u.username, u.user_colour';

				$sql_array['FROM'][DOWNLOADS_TABLE] = 'd';
				$sql_array['FROM'][DL_CAT_TABLE] = 'c';

				$sql_array['LEFT_JOIN'][] = [
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'd.add_user = u.user_id'
				];

				$sql_array['WHERE'] = 'd.cat = c.id AND ' . $this->db->sql_in_set('d.id', $search_ids);
				$sql_arrry['ORDER_BY'] = ' c.cat_name, d.sort ' . (string) $sort_dir;

				$sql = $this->db->sql_build_query('SELECT', $sql_array);
				
				$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);
		
				while ( $row = $this->db->sql_fetchrow($result) )
				{
					$cat_id				= $row['cat'];
					$file_id			= $row['id'];
					$u_file_link		= $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]);
		
					$dl_status			= [];
					$dl_status			= $this->dlext_status->status($file_id);
		
					$status				= $dl_status['status'];
					$file_name			= $dl_status['file_name'];
		
					$mini_icon			= $this->dlext_status->mini_status_file($cat_id, $file_id);
		
					$cat_name			= $row['cat_name'];
					$u_cat_link			= $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]);

					if (!$row['username'])
					{
						$add_user = $this->language->lang('GUEST');
					}
					else
					{
						$add_user = get_username_string('full', $row['add_user'], $row['username'], $row['user_colour']);
					}
					$add_time			= $this->user->format_date($row['add_time']);
					$add_time_rfc		= gmdate(DATE_RFC3339, $row['add_time']);
	
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
	
					if ((int) $this->config['dl_limit_desc_on_search'] && strlen($long_desc) > (int) $this->config['dl_limit_desc_on_search'])
					{
						$long_desc = strip_tags($long_desc, '<br><br/>');
						$long_desc = substr($long_desc, 0, (int) $this->config['dl_limit_desc_on_search']) . ' [...]';
					}

					$this->template->assign_block_vars('searchresults', [
						'STATUS'		=> $status,
						'CAT_NAME'		=> $cat_name,
						'DESCRIPTION'	=> $description,
						'MINI_ICON'		=> $mini_icon,
						'FILE_NAME'		=> $file_name,
						'LONG_DESC'		=> ($this->config['dl_desc_search']) ? $long_desc : '',
						'ADD_USER'		=> $add_user,
						'ADD_TIME'		=> $add_time,
						'ADD_TIME_RFC'	=> $add_time_rfc,
		
						'U_CAT_LINK'	=> $u_cat_link,
						'U_FILE_LINK'	=> $u_file_link,
					]);
				}

				$this->db->sql_freeresult($result);
			}
		}
		else if ($search_author || $search_user)
		{
			$this->template->set_filenames(['body' => 'dl_search_results.html']);
		
			$sql_cat		= ($search_cat == -1) ? '' : ' AND cat = ' . $search_cat;
			$sql_cat_count	= ($search_cat == -1) ? '' : ' AND cat = ' . $search_cat;
		
			if ($search_user)
			{
				$sql_matching_users = ' AND add_user = ' . (int) $search_user;
			}
			else
			{
				$search_author = str_replace('sql', '', $search_author);
				$search_author = str_replace('union', '', $search_author);
				$search_author = str_replace('*', '%', trim($search_author));
			
				$sql = 'SELECT user_id FROM ' . USERS_TABLE . '
					WHERE username ' . $this->db->sql_like_expression($this->db->get_any_char() . $search_author . $this->db->get_any_char());
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
			}

			$access_cats		= [];
			$access_cats		= $this->dlext_main->full_index(0, 0, 0, 1);
		
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
					$this->helper->route('oxpus_dlext_search', ['search_author' => $search_author, 'search_cat' => $search_cat, 'sort_dir' => $sort_dir]),
					'pagination',
					'start',
					$total_found_dl,
					$this->config['dl_links_per_page'],
					$page_start
				);
		
				$this->template->assign_vars([
					'PAGE_NUMBER'	=> $pagination->on_page($total_found_dl, $this->config['dl_links_per_page'], $page_start),
					'TOTAL_DL'		=> $this->language->lang('VIEW_DOWNLOADS', $total_found_dl),
				]);
			}
		
			if ($total_found_dl == 0)
			{
				$this->template->assign_var('S_NO_RESULTS', true);
			}
			else
			{
				$sql_array['SELECT'] = 'd.*, c.cat_name, u.username, u.user_colour';

				$sql_array['FROM'][DOWNLOADS_TABLE] = 'd';
				$sql_array['FROM'][DL_CAT_TABLE] = 'c';

				$sql_array['LEFT_JOIN'][] = [
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'd.add_user = u.user_id'
				];

				$sql_array['WHERE'] = 'd.cat = c.id AND d.approve = ' . true . $sql_matching_users . $sql_access_dls . $sql_cat;
				$sql_arrry['ORDER_BY'] = ' c.cat_name, d.sort ' . (string) $sort_dir;

				$sql = $this->db->sql_build_query('SELECT', $sql_array);

				$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);
		
				while ( $row = $this->db->sql_fetchrow($result) )
				{
					$cat_id			= $row['cat'];
					$file_id		= $row['id'];
					$u_file_link	= $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]);
		
					$dl_status		= [];
					$dl_status		= $this->dlext_status->status($file_id);
		
					$status			= $dl_status['status'];
					$file_name		= $dl_status['file_name'];
		
					$mini_icon		= $this->dlext_status->mini_status_file($cat_id, $file_id);
		
					$cat_name		= $row['cat_name'];
					$u_cat_link		= $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]);

					$add_user		= get_username_string('full', $row['add_user'], $row['username'], $row['user_colour']);
					$add_time		= $this->user->format_date($row['add_time']);
					$add_time_rfc	= gmdate(DATE_RFC3339, $row['add_time']);

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
					if ((int) $this->config['dl_limit_desc_on_search'] && utf8_strlen($long_desc) > (int) $this->config['dl_limit_desc_on_search'])
					{
						$long_desc			= truncate_string($long_desc, (int) $this->config['dl_limit_desc_on_search'], 16777215, false, '[...]');
						$long_desc 			= htmlspecialchars_decode(str_replace(['<br>', '<br />'], "\n", $long_desc));
					}

					$this->template->assign_block_vars('searchresults', [
						'STATUS'		=> $status,
						'CAT_NAME'		=> $cat_name,
						'DESCRIPTION'	=> $description,
						'MINI_ICON'		=> $mini_icon,
						'FILE_NAME'		=> $file_name,
						'LONG_DESC'		=> ($this->config['dl_desc_search']) ? $long_desc : '',
						'ADD_USER'		=> $add_user,
						'ADD_TIME'		=> $add_time,
						'ADD_TIME_RFC'	=> $add_time_rfc,
	
						'U_CAT_LINK'	=> $u_cat_link,
						'U_FILE_LINK'	=> $u_file_link,
					]);
				}
			}
		}
		else
		{
			/*
			* default entry point of download searching
			*/
			$select_categories = '<select name="search_cat" size="10"><option value="-1" selected="selected">' . $this->language->lang('DL_ALL') . '</option>';
			$select_categories .= $this->dlext_extra->dl_dropdown(0, 0, 0, 'auth_view');
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
				$s_search_fields .= '<option value="' . $search_fields[$i] . '">' . $search_fnames[$i] . '</option>';
			}
			$s_search_fields .= '</select>';
		
			$this->template->set_filenames(['body' => 'dl_search_body.html']);
		
			add_form_key('dl_search');
		
			$this->template->assign_vars([
				'S_DL_SEARCH_ACTION'	=> $this->helper->route('oxpus_dlext_search'),
				'S_DL_CATEGORY_OPTIONS'	=> $select_categories,
				'S_DL_SORT_ORDER'		=> $s_sort_dir,
				'S_DL_SORT_OPTIONS'		=> $s_search_fields,
			]);
		}

		/*
		* include the mod footer
		*/
		$dl_footer = $this->phpbb_container->get('oxpus.dlext.footer');
		$dl_footer->set_parameter($nav_view, 0, 0, $index);
		$dl_footer->handle();
	}
}
