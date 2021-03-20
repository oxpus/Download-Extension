<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller;

class index
{
	/* phpbb objects */
	protected $root_path;
	protected $php_ext;
	protected $pagination;
	protected $db;
	protected $config;
	protected $helper;
	protected $request;
	protected $template;
	protected $user;
	protected $language;
	protected $dispatcher;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_physical;
	protected $dlext_status;
	protected $dlext_constants;
	protected $dlext_footer;

	protected $dlext_table_dl_ratings;
	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	* Constructor
	*
	* @param string									$root_path
	* @param string									$php_ext
	* @param \phpbb\pagination 						$pagination
	* @param \phpbb\db\driver\driver_interface		$db
	* @param \phpbb\config\config					$config
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\request\request 				$request
	* @param \phpbb\template\template				$template
	* @param \phpbb\user							$user
	* @param \phpbb\language\language				$language
	* @param \phpbb\event\dispatcher_interface		$dispatcher
	* @param \oxpus\dlext\core\auth					$dlext_auth
	* @param \oxpus\dlext\core\files				$dlext_files
	* @param \oxpus\dlext\core\format				$dlext_format
	* @param \oxpus\dlext\core\main					$dlext_main
	* @param \oxpus\dlext\core\physical				$dlext_physical
	* @param \oxpus\dlext\core\status				$dlext_status
	* @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	* @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	* @param string									$dlext_table_dl_ratings
	* @param string									$dlext_table_downloads
	* @param string									$dlext_table_dl_cat
	*/
	public function __construct(
		$root_path,
		$php_ext,
		\phpbb\pagination $pagination,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\event\dispatcher_interface $dispatcher,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\physical $dlext_physical,
		\oxpus\dlext\core\status $dlext_status,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		$dlext_table_dl_ratings,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->pagination 				= $pagination;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;
		$this->dispatcher				= $dispatcher;

		$this->dlext_table_dl_ratings	= $dlext_table_dl_ratings;
		$this->dlext_table_downloads	= $dlext_table_downloads;
		$this->dlext_table_dl_cat		= $dlext_table_dl_cat;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_physical			= $dlext_physical;
		$this->dlext_status				= $dlext_status;
		$this->dlext_constants			= $dlext_constants;
		$this->dlext_footer				= $dlext_footer;

		$this->dlext_main->dl_handle_active();
	}

	public function handle()
	{
		$cat		= $this->request->variable('cat', 0);
		$cat_id		= $this->request->variable('cat_id', 0);
		$cat_df_id	= $this->request->variable('cat_df_id', 0);
		$order		= $this->request->variable('order', '');
		$start		= $this->request->variable('start', 0);
		$sort_by	= $this->request->variable('sort_by', 0);

		if ($cat_df_id && !$cat_id && !$cat)
		{
			$sql = 'SELECT cat
					FROM ' . $this->dlext_table_downloads . '
					WHERE id = ' . (int) $cat_df_id;
			$result = $this->db->sql_query($sql);
			$cat_id = $cat = $this->db->sql_fetchfield('cat');
			$this->db->sql_freeresult($result);
		}

		if ($cat < 0)
		{
			$cat = 0;
		}

		if ($cat_id || $cat)
		{
			$check_cat = ($cat_id) ? $cat_id : $cat;
			$dl_index = $this->dlext_auth->dl_index();

			if (!isset($dl_index[$check_cat]))
			{
				redirect($this->helper->route('oxpus_dlext_index'));
			}

			unset($dl_index);
		}

		$index = ($cat) ? $this->dlext_main->index($cat) : $this->dlext_main->index();

		/*
		* init sorting the downloads
		*/
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
			case $this->dlext_constants::DL_SORT_DESCRIPTION:
				$sql_sort_by = 'description';
				break;
			case $this->dlext_constants::DL_SORT_FILE_NAME:
				$sql_sort_by = 'file_name';
				break;
			case $this->dlext_constants::DL_SORT_CLICKS:
				$sql_sort_by = 'klicks';
				break;
			case $this->dlext_constants::DL_SORT_FREE:
				$sql_sort_by = 'free';
				break;
			case $this->dlext_constants::DL_SORT_EXTERN:
				$sql_sort_by = 'extern';
				break;
			case $this->dlext_constants::DL_SORT_FILE_SIZE:
				$sql_sort_by = 'file_size';
				break;
			case $this->dlext_constants::DL_SORT_LAST_TIME:
				$sql_sort_by = 'change_time';
				break;
			case $this->dlext_constants::DL_SORT_RATING:
				$sql_sort_by = 'rating';
				break;
			default:
				$sql_sort_by = 'sort';
		}

		$sql_order = ($order == 'DESC') ? 'DESC' : 'ASC';

		if (!$this->config['dl_sort_preform'] && $this->user->data['user_dl_sort_opt'])
		{
			$this->template->assign_var('S_DL_SORT_OPTIONS', $this->dlext_constants::DL_TRUE);

			$selected_0 = ($sort_by == $this->dlext_constants::DL_SORT_DEFAULT) ? ' selected="selected"' : '';
			$selected_1 = ($sort_by == $this->dlext_constants::DL_SORT_DESCRIPTION) ? ' selected="selected"' : '';
			$selected_2 = ($sort_by == $this->dlext_constants::DL_SORT_FILE_NAME) ? ' selected="selected"' : '';
			$selected_3 = ($sort_by == $this->dlext_constants::DL_SORT_CLICKS) ? ' selected="selected"' : '';
			$selected_4 = ($sort_by == $this->dlext_constants::DL_SORT_FREE) ? ' selected="selected"' : '';
			$selected_5 = ($sort_by == $this->dlext_constants::DL_SORT_EXTERN) ? ' selected="selected"' : '';
			$selected_6 = ($sort_by == $this->dlext_constants::DL_SORT_FILE_SIZE) ? ' selected="selected"' : '';
			$selected_7 = ($sort_by == $this->dlext_constants::DL_SORT_LAST_TIME) ? ' selected="selected"' : '';
			$selected_8 = ($sort_by == $this->dlext_constants::DL_SORT_RATING) ? ' selected="selected"' : '';

			$selected_sort_0 = ($order == 'ASC') ? ' selected="selected"' : '';
			$selected_sort_1 = ($order == 'DESC') ? ' selected="selected"' : '';

			$this->template->assign_vars([
				'DL_SELECTED_0'		=> $selected_0,
				'DL_SELECTED_1'		=> $selected_1,
				'DL_SELECTED_2'		=> $selected_2,
				'DL_SELECTED_3'		=> $selected_3,
				'DL_SELECTED_4'		=> $selected_4,
				'DL_SELECTED_5'		=> $selected_5,
				'DL_SELECTED_6'		=> $selected_6,
				'DL_SELECTED_7'		=> $selected_7,
				'DL_SELECTED_8'		=> $selected_8,

				'DL_SELECTED_SORT_0'	=> $selected_sort_0,
				'DL_SELECTED_SORT_1'	=> $selected_sort_1,
			]);
		}
		else
		{
			$s_sort_by = '';
			$s_order = '';
		}

		/*
		* Hide subcategories if wanted by the user
		*/
		if ($this->user->data['user_dl_sub_on_index'])
		{
			$this->template->assign_var('S_DL_SUB_ON_INDEX', $this->dlext_constants::DL_TRUE);
		}

		/*
		* default user entry. redirect to index or category
		*/
		if (!$cat)
		{
			$tpl_filename = '@oxpus_dlext/view_dl_cat_body.html';
		}
		else
		{
			$cat_auth = $this->dlext_auth->dl_cat_auth($cat);

			$index_auth = $this->dlext_main->full_index($cat);

			if (!$cat_auth['auth_view'] && !$index_auth[$cat]['auth_view'] && !$this->dlext_auth->user_admin())
			{
				redirect($this->helper->route('oxpus_dlext_index'));
			}

			$tpl_filename = '@oxpus_dlext/downloads_body.html';

			$ratings = [];
			if ($this->config['dl_enable_rate'])
			{
				$sql = "SELECT dl_id, user_id FROM " . $this->dlext_table_dl_ratings;
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$ratings[$row['dl_id']][] = $row['user_id'];
				}
				$this->db->sql_freeresult($result);
			}
		}

		$sql = 'SELECT c.parent, d.cat, d.id, d.change_time, d.description, d.change_user, u.user_id, u.user_colour, u.username
			FROM ' . $this->dlext_table_downloads . ' d, ' . USERS_TABLE . ' u, ' . $this->dlext_table_dl_cat . ' c
			WHERE d.change_user = u.user_id
				AND d.approve = 1
				AND d.cat = c.id
			ORDER BY cat, change_time DESC, id DESC';
		$result = $this->db->sql_query($sql);

		$last_dl = [];
		$last_id = 0;

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['cat'] != $last_id)
			{
				$last_id = $row['cat'];
				$check_status = $this->dlext_status->status($row['id']);

				$last_dl[$last_id]['change_time'] = $row['change_time'];
				$last_dl[$last_id]['parent'] = $row['parent'];
				$last_dl[$last_id]['desc'] = $row['description'];
				$last_dl[$last_id]['user'] = get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']);
				$last_dl[$last_id]['time'] = $this->user->format_date($row['change_time']);
				$last_dl[$last_id]['time_rfc'] = gmdate(DATE_RFC3339, $row['change_time']);
				$last_dl[$last_id]['link'] = $this->helper->route('oxpus_dlext_details', ['df_id' => $row['id']]);
				$last_dl[$last_id]['user_link'] = append_sid($this->root_path . 'memberlist.' . $this->php_ext, 'mode=viewprofile&amp;u=' . $row['change_user']);
				$last_dl[$last_id]['icon'] = $check_status['file_status'];
			}
		}
		$this->db->sql_freeresult($result);

		if (!empty($index))
		{
			foreach (array_keys($index) as $cat_id)
			{
				$parent_id = (isset($index[$cat_id]['parent'])) ? $index[$cat_id]['parent'] : 0;
				$cat_name = (isset($index[$cat_id]['cat_name'])) ? $index[$cat_id]['cat_name'] : '';
				$cat_desc = (isset($index[$cat_id]['description'])) ? $index[$cat_id]['description'] : '';
				$cat_view = (isset($index[$cat_id]['nav_path'])) ? $index[$cat_id]['nav_path'] : '';
				$cat_uid = (isset($index[$cat_id]['desc_uid'])) ? $index[$cat_id]['desc_uid'] : '';
				$cat_bitfield = (isset($index[$cat_id]['desc_bitfield'])) ? $index[$cat_id]['desc_bitfield'] : '';
				$cat_flags = (isset($index[$cat_id]['desc_flags'])) ? $index[$cat_id]['desc_flags'] : 0;
				$cat_sublevel = (isset($index[$cat_id]['sublevel'])) ? $index[$cat_id]['sublevel'] : '';
				$cat_icon = (isset($index[$cat_id]['cat_icon'])) ? $index[$cat_id]['cat_icon'] : '';

				if ($cat_desc)
				{
					$cat_desc = censor_text($cat_desc);

					if (isset($this->config['dl_index_desc_hide']) && $this->config['dl_index_desc_hide'])
					{
						strip_bbcode($cat_desc, $cat_uid);
					}
					else
					{
						$cat_desc = generate_text_for_display($cat_desc, $cat_uid, $cat_bitfield, $cat_flags);
					}
				}

				$cat_subs = (isset($cat_sublevel['cat_path'])) ? $cat_sublevel['cat_path'] : '';

				$folder_sub = 0;

				if (!$this->user->data['user_dl_sub_on_index'])
				{
					if ($cat_subs)
					{
						$folder_sub = 1;
					}
				}

				$mini_icon = $this->dlext_status->mini_status_cat($cat_id, $cat_id);

				if ($mini_icon[$cat_id]['new'] && !$mini_icon[$cat_id]['edit'])
				{
					$mini_cat_icon = 'new';
				}
				else if (!$mini_icon[$cat_id]['new'] && $mini_icon[$cat_id]['edit'])
				{
					$mini_cat_icon = 'edit';
				}
				else if ($mini_icon[$cat_id]['new'] && $mini_icon[$cat_id]['edit'])
				{
					$mini_cat_icon = 'new_edit';
				}
				else
				{
					$mini_cat_icon = 'default';
				}

				$last_dl_time = $this->dlext_main->find_latest_dl($last_dl, $cat_id, $cat_id, []);
				$last_cat_id = (isset($last_dl_time['cat_id'])) ? $last_dl_time['cat_id'] : 0;

				if (isset($last_dl[$cat_id]['change_time']) && isset($last_dl_time['change_time']))
				{
					if ($last_dl[$cat_id]['change_time'] > $last_dl_time['change_time'])
					{
						$last_cat_id = $cat_id;
					}
				}

				if ($cat)
				{
					$block = 'subcats';

					$this->template->assign_var('S_DL_SUBCATS', $this->dlext_constants::DL_TRUE);
				}
				else
				{
					$block = 'downloads';
				}

				if (isset($index[$cat_id]['total']) && $index[$cat_id]['total'] > $this->config['dl_links_per_page'])
				{
					$this->pagination->generate_template_pagination(
						$this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]),
						$block . 'pagination',
						'start',
						$index[$cat_id]['total'],
						$this->config['dl_links_per_page'],
						$start
					);
				}

				$this->template->assign_block_vars($block, [
					'DL_MINI_IMG'			=> $mini_cat_icon,
					'DL_SUBLEVEL'			=> $cat_sublevel,
					'DL_SUBLEVEL_SUB'		=> $folder_sub,
					'DL_CAT_DESC'			=> $cat_desc,
					'DL_CAT_NAME'			=> $cat_name,
					'DL_CAT_ICON'			=> $cat_icon,
					'DL_CAT_ID'				=> $cat_id,
					'DL_CAT_DL'				=> ((isset($index[$cat_id]['total'])) ? $index[$cat_id]['total'] : 0) + $this->dlext_main->get_sublevel_count($cat_id),
					'DL_CAT_LAST_DL'		=> (isset($last_dl[$last_cat_id]['desc'])) ? $last_dl[$last_cat_id]['desc'] : '',
					'DL_CAT_LAST_USER'		=> (isset($last_dl[$last_cat_id]['user'])) ? $last_dl[$last_cat_id]['user'] : '',
					'DL_CAT_LAST_TIME'		=> (isset($last_dl[$last_cat_id]['time'])) ? $last_dl[$last_cat_id]['time'] : '',
					'DL_CAT_LAST_TIME_RFC'	=> (isset($last_dl[$last_cat_id]['time_rfc'])) ? $last_dl[$last_cat_id]['time_rfc'] : '',
					'DL_CAT_LAST_ICON'		=> (isset($last_dl[$last_cat_id]['icon'])) ? $last_dl[$last_cat_id]['icon'] : '',

					'U_DL_CAT_VIEW'			=> $cat_view,
					'U_DL_CAT_LAST_LINK'	=> (isset($last_dl[$last_cat_id]['link'])) ? $last_dl[$last_cat_id]['link'] : '',
					'U_DL_CAT_LAST_USER'	=> (isset($last_dl[$last_cat_id]['user_link'])) ? $last_dl[$last_cat_id]['user_link'] : '',
				]);

				if ($cat_subs && $this->user->data['user_dl_sub_on_index'])
				{
					$this->template->assign_block_vars($block.'.sub', []);

					for ($j = 0; $j < count($cat_subs); ++$j)
					{
						$sub_id = $cat_sublevel['cat_id'][$j];
						$mini_icon = $this->dlext_status->mini_status_cat($sub_id, $sub_id);

						$sub_sublevel = $this->dlext_main->get_sublevel($sub_id);
						$sub_subs = (isset($sub_sublevel['cat_path'])) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

						if ($mini_icon[$sub_id]['new'] && !$mini_icon[$sub_id]['edit'])
						{
							$mini_cat_icon = 'new';
						}
						else if (!$mini_icon[$sub_id]['new'] && $mini_icon[$sub_id]['edit'])
						{
							$mini_cat_icon = 'edit';
						}
						else if ($mini_icon[$sub_id]['new'] && $mini_icon[$sub_id]['edit'])
						{
							$mini_cat_icon = 'new_edit';
						}
						else
						{
							$mini_cat_icon = 'default';
						}

						$this->template->assign_block_vars($block.'.sub.sublevel_row', [
							'L_DL_SUBLEVEL'			=> $cat_sublevel['cat_name'][$j],
							'DL_SUBLEVEL_COUNT'		=> $cat_sublevel['total'][$j] + $this->dlext_main->get_sublevel_count($sub_id),
							'M_DL_SUBLEVEL'			=> $mini_cat_icon,
							'M_DL_SUBLEVEL_SUB'		=> $sub_subs,
							'M_DL_SUBLEVEL_ICON'	=> (isset($cat_sublevel['cat_icon'][$j])) ? $cat_sublevel['cat_icon'][$j] : '',
							'U_DL_SUBLEVEL'			=> $cat_sublevel['cat_path'][$j],
						]);
					}
				}

				if ($cat)
				{
					$this->template->assign_var('S_DL_SUBCAT_BOX', $this->dlext_constants::DL_TRUE);
				}
			}
		}
		else
		{
			$this->template->assign_var('S_DL_NO_CATEGORY', $this->dlext_constants::DL_TRUE);
		}

		if ($cat)
		{
			$index_cat = $this->dlext_main->full_index($cat);
			$total_downloads = (isset($index_cat[$cat]['total'])) ? $index_cat[$cat]['total'] : 0;

			if ($total_downloads > $this->config['dl_links_per_page'])
			{
				$this->pagination->generate_template_pagination(
					$this->helper->route('oxpus_dlext_index', ['cat' => $cat, 'sort_by' => $sort_by, 'order' => $order]),
					'pagination',
					'start',
					$total_downloads,
					$this->config['dl_links_per_page'],
					$start
				);

				$this->template->assign_vars([
					'DL_PAGE_NUMBER'	=> $this->pagination->on_page($total_downloads, $this->config['dl_links_per_page'], $start),
					'DL_TOTAL_DL'		=> $this->language->lang('DL_VIEW_DOWNLOADS', $total_downloads),
				]);
			}

			if (isset($index_cat[$cat]['rules']) && $index_cat[$cat]['rules'] != '')
			{
				$cat_rule = $index_cat[$cat]['rules'];
				$cat_rule_uid = (isset($index_cat[$cat]['rule_uid'])) ? $index_cat[$cat]['rule_uid'] : '';
				$cat_rule_bitfield = (isset($index_cat[$cat]['rule_bitfield'])) ? $index_cat[$cat]['rule_bitfield'] : '';
				$cat_rule_flags = (isset($index_cat[$cat]['rule_flags'])) ? $index_cat[$cat]['rule_flags'] : 0;
				$cat_rule = censor_text($cat_rule);
				$cat_rule = generate_text_for_display($cat_rule, $cat_rule_uid, $cat_rule_bitfield, $cat_rule_flags);

				$this->template->assign_var('S_DL_CAT_RULE', $this->dlext_constants::DL_TRUE);
			}

			if ($this->dlext_auth->user_auth($cat, 'auth_mod'))
			{
				$this->template->assign_var('S_DL_MODCP', $this->dlext_constants::DL_TRUE);
			}

			$physical_size = $this->dlext_physical->read_dl_sizes();
			if ($physical_size < $this->config['dl_physical_quota'] && (!$this->config['dl_stop_uploads']) || $this->dlext_auth->user_admin())
			{
				if ($this->dlext_auth->user_auth($cat, 'auth_up'))
				{
					$this->template->assign_var('S_DL_UPLOAD', $this->dlext_constants::DL_TRUE);
				}
			}

			$cat_traffic = 0;

			if (!$this->config['dl_traffic_off'])
			{
				if ($this->user->data['is_registered'])
				{
					$cat_overall_traffic = $this->config['dl_overall_traffic'];
					$cat_limit = $this->dlext_constants->get_value('overall_traffics');
				}
				else
				{
					$cat_overall_traffic = $this->config['dl_overall_guest_traffic'];
					$cat_limit = $this->dlext_constants->get_value('guests_traffics');
				}

				if (isset($index_cat[$cat]['cat_traffic_use']))
				{
					$cat_traffic = $index_cat[$cat]['cat_traffic'] - $index_cat[$cat]['cat_traffic_use'];
				}
				else
				{
					$cat_traffic = 0;
				}

				if ($index_cat[$cat]['cat_traffic'] && $cat_traffic > 0)
				{
					$cat_traffic = ($cat_traffic > $cat_overall_traffic && $cat_limit == $this->dlext_constants::DL_TRUE) ? $cat_overall_traffic : $cat_traffic;
					$cat_traffic = $this->dlext_format->dl_size($cat_traffic);

					$this->template->assign_var('S_DL_CAT_TRAFFIC', $this->dlext_constants::DL_TRUE);
				}
			}
			else
			{
				unset($cat_traffic);
			}
		}

		$i = 0;

		if ($cat && $total_downloads)
		{
			$dl_files = $this->dlext_files->files($cat, $sql_sort_by, $sql_order, $start, $this->config['dl_links_per_page'], 'id, description, desc_uid, desc_bitfield, desc_flags, hack_version, extern, file_size, klicks, overall_klicks, rating, long_desc, long_desc_uid, long_desc_bitfield, long_desc_flags, add_user, add_time, broken', $this->dlext_constants::DL_TRUE);

			/**
			 * Fetch additional data for the downloads
			 *
			 * @event oxpus.dlext.index_fetch_download_data
			 * @var int 	cat				download category ID
			 * @var array	sort_ary		order paired filename => direction
			 * @var array	latest_where	additional where conditions filename => condition|operator|value
			 * @since 8.1.0-RC2
			 */
			$latest_where = '';
			$sort_ary = [$sql_sort_by, $sql_order];
			$vars = array(
				'cat',
				'sort_ary',
				'latest_where',
			);
			extract($this->dispatcher->trigger_event('oxpus.dlext.index_fetch_download_data', compact($vars)));

			for ($i = 0; $i < count($dl_files); ++$i)
			{
				$file_id = $dl_files[$i]['id'];
				$mini_file_icon = $this->dlext_status->mini_status_file($cat, $file_id);

				$description = $dl_files[$i]['description'];
				$file_url = $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]);

				$hack_version = '&nbsp;'.$dl_files[$i]['hack_version'];

				$long_desc_uid = $dl_files[$i]['long_desc_uid'];
				$long_desc_bitfield = $dl_files[$i]['long_desc_bitfield'];
				$long_desc_flags = (isset($dl_files[$i]['long_desc_flags'])) ? $dl_files[$i]['long_desc_flags'] : 0;

				$desc_uid = $dl_files[$i]['desc_uid'];
				$desc_bitfield = $dl_files[$i]['desc_bitfield'];
				$desc_flags = (isset($dl_files[$i]['desc_flags'])) ? $dl_files[$i]['desc_flags'] : 0;

				$description = censor_text($description);
				$description = generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

				$long_desc = $dl_files[$i]['long_desc'];
				$long_desc = censor_text($long_desc);

				$long_desc = generate_text_for_display($long_desc, $long_desc_uid, $long_desc_bitfield, $long_desc_flags);
				if ((int) $this->config['dl_limit_desc_on_index'] && utf8_strlen($long_desc) > (int) $this->config['dl_limit_desc_on_index'])
				{
					$long_desc = truncate_string($long_desc, (int) $this->config['dl_limit_desc_on_index'], $this->dlext_constants::DL_MAX_STRING_LENGTH, $this->dlext_constants::DL_FALSE, '[...]');
					$long_desc = htmlspecialchars_decode(str_replace(['<br>', '<br />'], "\n", $long_desc));
				}

				if (!$dl_files[$i]['username'])
				{
					$add_user = $this->language->lang('GUEST');
				}
				else
				{
					$add_user = get_username_string('full', $dl_files[$i]['add_user'], $dl_files[$i]['username'], $dl_files[$i]['user_colour']);
				}
				$add_time = $this->user->format_date($dl_files[$i]['add_time']);
				$add_time_rfc = gmdate(DATE_RFC3339, $dl_files[$i]['add_time']);

				$check_status = $this->dlext_status->status($file_id);
				$file_status = $check_status['file_status'];

				$broken = $dl_files[$i]['broken'];

				if ($dl_files[$i]['file_size'])
				{
					$file_size = $this->dlext_format->dl_size($dl_files[$i]['file_size'], 2);
				}
				else
				{
					$file_size = $this->language->lang('DL_NOT_AVAILABLE');
				}

				$file_klicks = $dl_files[$i]['klicks'];
				$file_overall_klicks = $dl_files[$i]['overall_klicks'];

				$s_rating_perm = $this->dlext_constants::DL_FALSE;
				$rating_points = 0;

				if ($cat && $this->config['dl_enable_rate'])
				{
					$rating_points = $dl_files[$i]['rating'];

					if ((!$rating_points || !in_array($this->user->data['user_id'], $ratings[$file_id])) && $this->user->data['is_registered'])
					{
						$s_rating_perm = $this->dlext_constants::DL_TRUE;
					}

					if (isset($ratings[$file_id]))
					{
						$total_ratings = count($ratings[$file_id]);
					}
					else
					{
						$total_ratings = 0;
					}
				}

				$cat_edit_link = $this->dlext_constants::DL_FALSE;

				switch ($this->config['dl_cat_edit'])
				{
					case $this->dlext_constants::DL_CAT_EDIT_ADMIN_ONLY:
						if ($this->dlext_auth->user_admin())
						{
							$cat_edit_link = $this->dlext_constants::DL_TRUE;
						}
					break;
					case $this->dlext_constants::DL_CAT_EDIT_ADMIN_MOD:
						if ($this->dlext_auth->user_admin() || $this->dlext_auth->user_auth($cat, 'auth_mod'))
						{
							$cat_edit_link = $this->dlext_constants::DL_TRUE;
						}
					break;
					case $this->dlext_constants::DL_CAT_EDIT_ADMIN_MOD_OWN:
						if ($this->dlext_auth->user_admin() || $this->dlext_auth->user_auth($cat, 'auth_mod') || ($this->config['dl_edit_own_downloads'] && $dl_files[$i]['add_user'] == $this->user->data['user_id']))
						{
							$cat_edit_link = $this->dlext_constants::DL_TRUE;
						}
					break;
					default:
						$cat_edit_link = $this->dlext_constants::DL_FALSE;
				}

				/*
				* Build rating imageset
				*/
				$rating_img_data	= $this->dlext_format->rating_img($rating_points, $s_rating_perm, $file_id, $total_ratings);
				$rating_stars		= $rating_img_data['stars'];
				$rating_data		= $rating_img_data['count'];

				$this->template->assign_block_vars('downloads', [
					'DL_DESCRIPTION'			=> $description,
					'DL_BROKEN'					=> $broken,
					'DL_MINI_IMG'				=> $mini_file_icon,
					'DL_HACK_VERSION'			=> $hack_version,
					'DL_LONG_DESC'				=> ($this->config['dl_desc_index']) ? $long_desc : '',
					'DL_FILE_STATUS'			=> $file_status,
					'DL_FILE_SIZE'				=> $file_size,
					'DL_FILE_KLICKS'			=> $file_klicks,
					'DL_FILE_OVERALL_KLICKS'	=> $file_overall_klicks,
					'DL_DF_ID'					=> $file_id,
					'DL_ADD_USER'				=> $add_user,
					'DL_ADD_TIME'				=> $add_time,
					'DL_ADD_TIME_RFC'			=> $add_time_rfc,
					'DL_RATE_COUNT'				=> $rating_data['count'],
					'DL_RATE_UNDO'				=> $rating_data['undo'],
					'DL_RATE_TITLE'				=> $rating_data['title'],

					'U_DL_DIRECT_EDIT'			=> ($cat_edit_link) ? $this->helper->route('oxpus_dlext_mcp_edit', ['cat_id' => $cat, 'df_id' => $file_id]) : '',
					'U_DL_FILE'					=> $file_url,
				]);

				foreach ($rating_stars as $key => $data)
				{
					$this->template->assign_block_vars('downloads.rating_img', [
						'DL_RATE_STAR' 	=> $rating_stars[$key]['icon'],
						'DL_RATE_AJAX'	=> $rating_stars[$key]['ajax'],
					]);
				}

				if ($index_cat[$cat]['comments'] && ($this->dlext_auth->cat_auth_comment_read($cat) || $this->dlext_auth->cat_auth_comment_post($cat)))
				{
					$this->template->assign_block_vars('downloads.comments', ['U_DL_COMMENT' => $this->helper->route('oxpus_dlext_details', ['view' => 'comment', 'action' => 'view', 'cat_id' => $cat, 'df_id' => $file_id])]);
				}

				/**
				 * Fetch additional data for the downloads
				 *
				 * @event oxpus.dlext.index_display_data_after
				 * @var string	block		template row key
				 * @var int 	file_id		download id
				 * @since 8.1.0-RC2
				 */
				$block = 'downloads';
				$vars = array(
					'block',
					'file_id',
				);
				extract($this->dispatcher->trigger_event('oxpus.dlext.index_display_data_after', compact($vars)));
			}
		}

		if ($i)
		{
			$this->template->assign_var('S_DL_DOWNLOAD_ROWS', $this->dlext_constants::DL_TRUE);

			if ($index_cat[$cat]['comments'] && $this->dlext_auth->cat_auth_comment_read($cat))
			{
				$this->template->assign_block_vars('comment_header', []);
			}
		}

		if ($cat && !$total_downloads)
		{
			$this->template->assign_var('S_DL_EMPTY_CATEGORY', $this->dlext_constants::DL_TRUE);
		}

		$this->template->assign_vars([
			'DL_CAT_RULE'		=> (isset($cat_rule)) ? $cat_rule : '',
			'DL_CAT_TRAFFIC'	=> (isset($cat_traffic)) ? $this->language->lang('DL_CAT_TRAFFIC_MAIN', $cat_traffic) : '',
			'T_DL_CAT'			=> (isset($index[$cat]['cat_name']) && $cat) ? $index[$cat]['cat_name'] : $this->language->lang('DL_CAT_NAME'),
			'DL_UPLOAD'			=> $this->helper->route('oxpus_dlext_upload', ['cat_id' => $cat]),
			'DL_PHPEX'			=> $this->php_ext,

			'S_DL_ENABLE_DESC_HIDE'	=> (isset($this->config['dl_index_desc_hide']) && $this->config['dl_index_desc_hide']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_ENABLE_RATE'		=> (isset($this->config['dl_enable_rate']) && $this->config['dl_enable_rate']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,

			'U_DOWNLOADS'	=> ($cat) ? $this->helper->route('oxpus_dlext_index', ['cat' => $cat]) : $this->helper->route('oxpus_dlext_index'),
			'U_DL_SEARCH'	=> (!empty($index) || $cat) ? $this->helper->route('oxpus_dlext_search') : '',
			'U_DL_AJAX'		=> $this->helper->route('oxpus_dlext_rate'),
		]);

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter('index', $cat, 0, $index);
		$this->dlext_footer->handle();

		/*
		* generate page
		*/
		return $this->helper->render($tpl_filename, $this->language->lang('DL_DOWNLOADS'));
	}
}
