<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class search
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames sql_array matching_userids
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUnusedVariableNames block

	/* phpbb objects */
	protected $db;
	protected $config;
	protected $helper;
	protected $request;
	protected $template;
	protected $user;
	protected $language;
	protected $dispatcher;
	protected $pagination;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_extra;
	protected $dlext_main;
	protected $dlext_status;
	protected $dlext_format;
	protected $dlext_footer;
	protected $dlext_constants;

	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;
	protected $dlext_dlext_images_table;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\user							$user
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\event\dispatcher_interface		$dispatcher
	 * @param \phpbb\pagination						$pagination
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\extra				$dlext_extra
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\status				$dlext_status
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 * @param string								$dlext_dlext_images_table
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\pagination $pagination,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\extra $dlext_extra,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\status $dlext_status,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_downloads,
		$dlext_table_dl_cat,
		$dlext_dlext_images_table
	)
	{
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;
		$this->dispatcher				= $dispatcher;
		$this->pagination				= $pagination;

		$this->dlext_table_downloads	= $dlext_table_downloads;
		$this->dlext_table_dl_cat		= $dlext_table_dl_cat;
		$this->dlext_dlext_images_table	= $dlext_dlext_images_table;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_extra				= $dlext_extra;
		$this->dlext_main				= $dlext_main;
		$this->dlext_status				= $dlext_status;
		$this->dlext_format				= $dlext_format;
		$this->dlext_footer				= $dlext_footer;
		$this->dlext_constants			= $dlext_constants;
	}

	public function handle()
	{
		$this->dlext_main->dl_handle_active();

		$cat		= $this->request->variable('cat', 0);
		$start		= $this->request->variable('start', 0);

		$index 		= ($cat) ? $this->dlext_main->full_index($cat) : $this->dlext_main->full_index();

		$this->language->add_lang('search');

		/*
		* define initial search vars
		*/
		$search_keywords	= $this->request->variable('search_keywords', '', $this->dlext_constants::DL_TRUE);
		$search_cat			= $this->request->variable('search_cat', $this->dlext_constants::DL_NONE);
		$sort_dir			= $this->request->variable('sort_dir', 'ASC');
		$search_in_fields	= $this->request->variable('search_fields', 'all');
		$search_author		= $this->request->variable('search_author', '', $this->dlext_constants::DL_TRUE);
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
			$tpl_filename = '@oxpus_dlext/dl_search_results.html';

			$search_keywords = str_replace(['sql', 'union', '  ', ' ', '*', '?', '%'], ' ', strtolower($search_keywords));

			$access_cats		= $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_VIEW);
			$sql_access_cats	= ($this->dlext_auth->user_admin()) ? '' : ' AND ' . $this->db->sql_in_set('d.cat', $access_cats) . ' ';

			$sql_cat			= ($search_cat == $this->dlext_constants::DL_NONE) ? '' : ' AND d.cat = ' . (int) $search_cat;

			switch ($search_in_fields)
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
					$sql_fields = 'd.' . $this->db->sql_escape($search_in_fields);
					break;
				default:
					trigger_error($this->language->lang('DL_NO_PERMISSION'));
			}

			$search_words = array_unique(explode(' ', $search_keywords));

			$sql = "SELECT d.id, $sql_fields FROM " . $this->dlext_table_downloads . ' d
				WHERE d.approve = 1 ' . (string) $sql_access_cats . (string) $sql_cat;
			$result = $this->db->sql_query($sql);
			$total_found_dl = $this->db->sql_affectedrows();

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
					for ($i = 0; $i < count($search_words); ++$i)
					{
						if (preg_match_all('/' . preg_quote($search_words[$i], '/') . '/iu', $search_result, $matches))
						{
							++$counter;
						}
					}

					switch ($search_type)
					{
						case $this->dlext_constants::DL_SEARCH_TYPE_ALL:
							if ($counter == count($search_words))
							{
								$search_ids[] = $row['id'];
								++$search_counter;
							}
							break;

						default:
							$search_ids[] = $row['id'];
							++$search_counter;
					}
				}
			}

			$this->db->sql_freeresult($result);

			/**
			 * Fetch additional data for the downloads
			 *
			 * @event oxpus.dlext.search_keywords_fetch_download_data
			 * @var array	search_ids		download ids
			 * @since 8.1.0-RC2
			 */
			$vars = array(
				'search_ids',
			);
			extract($this->dispatcher->trigger_event('oxpus.dlext.search_keywords_fetch_download_data', compact($vars)));

			if ($search_counter > $this->config['dl_links_per_page'])
			{
				$this->pagination->generate_template_pagination(
					$this->helper->route('oxpus_dlext_search', ['search_keywords' => $search_keywords, 'search_cat' => $search_cat, 'sort_dir' => $sort_dir]),
					'pagination',
					'start',
					$search_counter,
					$this->config['dl_links_per_page'],
					$start
				);

				$this->template->assign_vars([
					'DL_PAGE_NUMBER'	=> $this->pagination->on_page($search_counter, $this->config['dl_links_per_page'], $start),
					'DL_TOTAL_DL'		=> $this->language->lang('DL_VIEW_DOWNLOADS_NUM', $search_counter),
				]);
			}

			if (!$search_counter)
			{
				$this->template->assign_var('S_DL_NO_RESULTS', $this->dlext_constants::DL_TRUE);
			}
			else
			{
				$sql_array['SELECT'] = 'd.*, c.cat_name, u.username, u.user_colour, img.img_name as thumbnail';

				$sql_array['FROM'][$this->dlext_table_downloads] = 'd';
				$sql_array['FROM'][$this->dlext_table_dl_cat] = 'c';

				$sql_array['LEFT_JOIN'][] = [
					'FROM'	=> [USERS_TABLE => 'u'],
					'ON'	=> 'd.add_user = u.user_id'
				];

				$sql_array['LEFT_JOIN'][] = [
					'FROM'	=> [$this->dlext_dlext_images_table => 'img'],
					'ON'	=> 'img.dl_id = d.id AND img.img_lists = 1'
				];

				$sql_array['WHERE'] = 'd.cat = c.id AND ' . $this->db->sql_in_set('d.id', $search_ids);
				$sql_array['ORDER_BY'] = ' c.cat_name, d.sort ' . (string) $this->db->sql_escape($sort_dir);

				$sql = $this->db->sql_build_query('SELECT', $sql_array);

				$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$cat_id				= $row['cat'];
					$file_id			= $row['id'];
					$cat_name			= $row['cat_name'];
					$file_name			= $row['file_name'];

					$check_status		= $this->dlext_status->status($file_id);
					$file_status		= $check_status['file_status'];

					$mini_icon			= $this->dlext_status->mini_status_file($cat_id, $file_id);

					$u_cat_link			= $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]);
					$u_file_link		= $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]);

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
					$long_desc			= $this->dlext_format->dl_shorten_string($row['long_desc'], 'search', $row['long_desc_uid'], $row['long_desc_bitfield'], $row['long_desc_flags']);

					$s_display_thumbnail = $this->dlext_constants::DL_FALSE;

					if (!empty($row['thumbnail']) && (($this->config['dl_thumbs_display_search'] == $this->dlext_constants::DL_THUMBS_DISPLAY_ON) || ($this->config['dl_thumbs_display_search'] == $this->dlext_constants::DL_THUMBS_DISPLAY_CAT && (!empty($index[$cat_id]['display_thumbs']) && $index[$cat_id]['display_thumbs']))))
					{
						$s_display_thumbnail = $this->dlext_constants::DL_TRUE;
					}

					$this->template->assign_block_vars('downloads', [
						'DL_FILE_STATUS'		=> $file_status,
						'DL_CAT_NAME'			=> $cat_name,
						'DL_DESCRIPTION'		=> $description,
						'DL_MINI_IMG'			=> $mini_icon,
						'DL_FILE_NAME'			=> $file_name,
						'DL_LONG_DESC'			=> ($this->config['dl_desc_search']) ? $long_desc : '',
						'DL_ADD_USER'			=> $add_user,
						'DL_ADD_TIME'			=> $add_time,
						'DL_ADD_TIME_RFC'		=> $add_time_rfc,
						'DL_THUMBNAIL_PIC'		=> $this->helper->route('oxpus_dlext_thumbnail', ['pic' => $file_id, 'img_type' => 'thumb', 'disp_art' => $this->dlext_constants::DL_TRUE]),

						'S_DISPLAY_THUMBNAIL'	=> $s_display_thumbnail,

						'U_DL_CAT_LINK'			=> $u_cat_link,
						'U_DL_FILE_LINK'		=> $u_file_link,
					]);

					/**
					 * Fetch additional data for the downloads
					 *
					 * @event oxpus.dlext.search_keywords_display_data_after
					 * @var string	block		template row key
					 * @var int		file_id		download id
					 * @since 8.1.0-RC2
					 */
					$block = 'downloads';
					$vars = array(
						'block',
						'file_id',
					);
					extract($this->dispatcher->trigger_event('oxpus.dlext.search_keywords_display_data_after', compact($vars)));
				}

				$this->db->sql_freeresult($result);
			}
		}
		else if ($search_author || $search_user)
		{
			$tpl_filename = '@oxpus_dlext/dl_search_results.html';

			$sql_cat_count	= ($search_cat == $this->dlext_constants::DL_NONE) ? '' : ' AND cat = ' . $search_cat;

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
				$total_users = $this->db->sql_affectedrows();

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

				if (!empty($matching_userids))
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

			$access_cats		= $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_VIEW);

			$sql_access_cats	= ($this->dlext_auth->user_admin()) ? '' : ' AND ' . $this->db->sql_in_set('cat', $access_cats);

			$sql = 'SELECT id FROM ' . $this->dlext_table_downloads . '
				WHERE approve = 1 ' . (string) $sql_matching_users . (string) $sql_access_cats . (string) $sql_cat_count;
			$result = $this->db->sql_query($sql);
			$total_found_dl = $this->db->sql_affectedrows();

			$search_ids = [];

			while ($row = $this->db->sql_fetchrow($result))
			{
				$search_ids[] = $row['id'];
			}

			$this->db->sql_freeresult($result);

			if ($total_found_dl > $this->config['dl_links_per_page'])
			{
				if ($search_user)
				{
					$pagination_url = $this->helper->route('oxpus_dlext_search', ['search_user_id' => $search_user, 'search_cat' => $search_cat, 'sort_dir' => $sort_dir]);
				}
				else
				{
					$pagination_url = $this->helper->route('oxpus_dlext_search', ['search_author' => $search_author, 'search_cat' => $search_cat, 'sort_dir' => $sort_dir]);
				}

				$this->pagination->generate_template_pagination(
					$pagination_url,
					'pagination',
					'start',
					$total_found_dl,
					$this->config['dl_links_per_page'],
					$start
				);

				$this->template->assign_vars([
					'DL_PAGE_NUMBER'	=> $this->pagination->on_page($total_found_dl, $this->config['dl_links_per_page'], $start),
					'DL_TOTAL_DL'		=> $this->language->lang('DL_VIEW_DOWNLOADS_NUM', $total_found_dl),
				]);
			}

			if ($total_found_dl == 0)
			{
				$this->template->assign_var('S_DL_NO_RESULTS', $this->dlext_constants::DL_TRUE);
			}
			else
			{
				/**
				 * Fetch additional data for the downloads
				 *
				 * @event oxpus.dlext.search_user_fetch_download_data
				 * @var array	search_ids		download ids
				 * @since 8.1.0-RC2
				 */
				$vars = array(
					'search_ids',
				);
				extract($this->dispatcher->trigger_event('oxpus.dlext.search_user_fetch_download_data', compact($vars)));

				$sql_array['SELECT'] = 'd.*, c.cat_name, u.username, u.user_colour, img.img_name as thumbnail';

				$sql_array['FROM'][$this->dlext_table_downloads] = 'd';
				$sql_array['FROM'][$this->dlext_table_dl_cat] = 'c';

				$sql_array['LEFT_JOIN'][] = [
					'FROM'	=> [USERS_TABLE => 'u'],
					'ON'	=> 'd.add_user = u.user_id'
				];

				$sql_array['LEFT_JOIN'][] = [
					'FROM'	=> [$this->dlext_dlext_images_table => 'img'],
					'ON'	=> 'img.dl_id = d.id AND img.img_lists = 1'
				];

				$sql_array['WHERE'] = 'd.cat = c.id AND d.approve = 1 AND ' . $this->db->sql_in_set('d.id', $search_ids);
				$sql_array['ORDER_BY'] = ' c.cat_name, d.sort ' . (string) $this->db->sql_escape($sort_dir);

				$sql = $this->db->sql_build_query('SELECT', $sql_array);

				$result = $this->db->sql_query_limit($sql, $this->config['dl_links_per_page'], $start);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$cat_id			= $row['cat'];
					$file_id		= $row['id'];
					$cat_name		= $row['cat_name'];
					$file_name		= $row['file_name'];

					$check_status	= $this->dlext_status->status($file_id);
					$file_status	= $check_status['file_status'];

					$mini_icon		= $this->dlext_status->mini_status_file($cat_id, $file_id);

					$u_cat_link		= $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]);
					$u_file_link	= $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]);

					if (!$row['username'])
					{
						$add_user = $this->language->lang('GUEST');
					}
					else
					{
						$add_user = get_username_string('full', $row['add_user'], $row['username'], $row['user_colour']);
					}
					$add_time		= $this->user->format_date($row['add_time']);
					$add_time_rfc	= gmdate(DATE_RFC3339, $row['add_time']);

					$description	= $row['description'];
					$desc_uid		= $row['desc_uid'];
					$desc_bitfield	= $row['desc_bitfield'];
					$desc_flags		= $row['desc_flags'];
					$description	= censor_text($description);
					$description	= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);
					$long_desc		= $this->dlext_format->dl_shorten_string($row['long_desc'], 'search', $row['long_desc_uid'], $row['long_desc_bitfield'], $row['long_desc_flags']);

					$s_display_thumbnail = $this->dlext_constants::DL_FALSE;

					if (!empty($row['thumbnail']) && (($this->config['dl_thumbs_display_search'] == $this->dlext_constants::DL_THUMBS_DISPLAY_ON) || ($this->config['dl_thumbs_display_search'] == $this->dlext_constants::DL_THUMBS_DISPLAY_CAT && $index[$cat_id]['display_thumbs'])))
					{
						$s_display_thumbnail = $this->dlext_constants::DL_TRUE;
					}

					$this->template->assign_block_vars('downloads', [
						'DL_FILE_STATUS'		=> $file_status,
						'DL_CAT_NAME'			=> $cat_name,
						'DL_DESCRIPTION'		=> $description,
						'DL_MINI_ICON'			=> $mini_icon,
						'DL_FILE_NAME'			=> $file_name,
						'DL_LONG_DESC'			=> ($this->config['dl_desc_search']) ? $long_desc : '',
						'DL_ADD_USER'			=> $add_user,
						'DL_ADD_TIME'			=> $add_time,
						'DL_ADD_TIME_RFC'		=> $add_time_rfc,
						'DL_THUMBNAIL_PIC'		=> $this->helper->route('oxpus_dlext_thumbnail', ['pic' => $file_id, 'img_type' => 'thumb_list', 'disp_art' => $this->dlext_constants::DL_TRUE]),

						'S_DISPLAY_THUMBNAIL'	=> $s_display_thumbnail,

						'U_DL_CAT_LINK'			=> $u_cat_link,
						'U_DL_FILE_LINK'		=> $u_file_link,
					]);

					/**
					 * Fetch additional data for the downloads
					 *
					 * @event oxpus.dlext.search_user_display_data_after
					 * @var string	block		template row key
					 * @var int		file_id		download id
					 * @since 8.1.0-RC2
					 */
					$block = 'downloads';
					$vars = array(
						'block',
						'file_id',
					);
					extract($this->dispatcher->trigger_event('oxpus.dlext.search_user_display_data_after', compact($vars)));
				}
			}
		}
		else
		{
			/*
			* default entry point of download searching
			*/
			$select_categories = $this->dlext_extra->dl_dropdown(0, 0, 0, 'auth_view');

			if (!empty($select_categories) && is_array($select_categories))
			{
				foreach (array_keys($select_categories) as $key)
				{
					$this->template->assign_block_vars('search_cat_select', [
						'DL_CAT_ID'			=> $select_categories[$key]['cat_id'],
						'DL_SEPERATOR'		=> $select_categories[$key]['seperator'],
						'DL_CAT_NAME'		=> $select_categories[$key]['cat_name'],
					]);
				}
			}

			for ($i = 0; $i < count($search_fields); ++$i)
			{
				$this->template->assign_block_vars('search_field_select', [
					'DL_FIELD'	=> $search_fields[$i],
					'DL_NAME'	=> $search_fnames[$i],
				]);
			}

			$tpl_filename = '@oxpus_dlext/dl_search_body.html';

			add_form_key('dl_search');

			$this->template->assign_vars([
				'S_DL_SEARCH_ACTION'	=> $this->helper->route('oxpus_dlext_search'),
				'S_DL_CATEGORY_OPTIONS'	=> $this->dlext_constants::DL_NONE,
				'S_DL_SORT_ORDER'		=> $sort_dir,
			]);
		}

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter('search', 0, 0, $index);
		$this->dlext_footer->handle();

		/*
		* generate page
		*/
		return $this->helper->render($tpl_filename, $this->language->lang('DL_SEARCH_DOWNLOAD'));
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUnusedVariableNames
}
