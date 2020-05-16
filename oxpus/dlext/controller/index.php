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

class index
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

	protected $dlext_auth;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_physical;
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
		$dlext_files,
		$dlext_format,
		$dlext_main,
		$dlext_physical,
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
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_physical			= $dlext_physical;
		$this->dlext_status				= $dlext_status;
	}

	public function handle()
	{
		$nav_view = 'index';

		// Include the default base init script
		include_once($this->ext_path . 'phpbb/includes/base_init.' . $this->php_ext);

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

		/*
		* init sorting the downloads
		*/
		include_once($this->ext_path . 'phpbb/includes/sort_init.' . $this->php_ext);

		/*
		* Hide subcategories if wanted by the user
		*/		
		if ($this->user->data['user_dl_sub_on_index'])
		{
			$this->template->assign_var('S_SUB_ON_INDEX', true);
		}

		/*
		* default user entry. redirect to index or category
		*/
		if (!$cat)
		{
			$this->template->set_filenames(['body' => 'view_dl_cat_body.html']);
		}
		else
		{
			$cat_auth = [];
			$cat_auth = $this->dlext_auth->dl_cat_auth($cat);

			$index_auth = [];
			$index_auth = $this->dlext_main->full_index($cat);
		
			if (!$cat_auth['auth_view'] && !$index_auth[$cat]['auth_view'] && !$this->auth->acl_get('a_'))
			{
				redirect($this->helper->route('oxpus_dlext_index'));
			}
		
			$this->template->set_filenames(['body' => 'downloads_body.html']);
		
			$ratings = [];
			if ($this->config['dl_enable_rate'])
			{
				$sql = "SELECT dl_id, user_id FROM " . DL_RATING_TABLE;
				$result = $this->db->sql_query($sql);
		
				while ($row = $this->db->sql_fetchrow($result))
				{
					$ratings[$row['dl_id']][] = $row['user_id'];
				}
				$this->db->sql_freeresult($result);
			}
		}
		
		$path_dl_array = [];
		
		page_header($this->language->lang('DOWNLOADS'));
		
		$user_id = $this->user->data['user_id'];
		$username = $this->user->data['username'];
		$user_traffic = $this->user->data['user_traffic'];
		
		$sql = 'SELECT c.parent, d.cat, d.id, d.change_time, d.description, d.change_user, u.user_id, u.user_colour, u.username
			FROM ' . DOWNLOADS_TABLE . ' d, ' . USERS_TABLE . ' u, ' . DL_CAT_TABLE . ' c
			WHERE d.change_user = u.user_id
				AND d.approve = ' . true . '
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
				$status = $this->dlext_status->status($row['id']);

				$last_dl[$last_id]['change_time'] = $row['change_time'];
				$last_dl[$last_id]['parent'] = $row['parent'];
				$last_dl[$last_id]['desc'] = $row['description'];
				$last_dl[$last_id]['user'] = get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']);
				$last_dl[$last_id]['time'] = $this->user->format_date($row['change_time']);
				$last_dl[$last_id]['time_rfc'] = gmdate(DATE_RFC3339, $row['change_time']);
				$last_dl[$last_id]['link'] = $this->helper->route('oxpus_dlext_details', ['df_id' => $row['id']]);
				$last_dl[$last_id]['user_link'] = append_sid($this->root_path . 'memberlist.' . $this->php_ext, 'mode=viewprofile&amp;u=' . $row['change_user']);
				$last_dl[$last_id]['icon'] = $status['status_detail'];
			}
		}
		$this->db->sql_freeresult($result);
		
		if (sizeof($index) > 0)
		{
			foreach(array_keys($index) as $cat_id)
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
						//$text_ary = generate_text_for_edit($cat_desc, $cat_uid, $cat_flags);
						//$cat_desc = $text_ary['text'];
					}
					else
					{
						$cat_desc = generate_text_for_display($cat_desc, $cat_uid, $cat_bitfield, $cat_flags);
					}
				}
		
				$cat_subs = (isset($cat_sublevel['cat_path'])) ? $cat_sublevel['cat_path'] : '';

				$folder_sub = '';

				if (!$this->user->data['user_dl_sub_on_index'])
				{
					if ($cat_subs)
					{
						$folder_sub = '-open';
					}
				}

				$mini_icon = [];
				$mini_icon = $this->dlext_status->mini_status_cat($cat_id, $cat_id);
		
				if ($mini_icon[$cat_id]['new'] && !$mini_icon[$cat_id]['edit'])
				{
					$mini_cat_icon = '<i class="icon fa-folder' . $folder_sub . ' fa-fw dl-red dl-big" title="' . $this->language->lang('DL_NEW') . '"></i>';
				}
				else if (!$mini_icon[$cat_id]['new'] && $mini_icon[$cat_id]['edit'])
				{
					$mini_cat_icon = '<i class="icon fa-folder' . $folder_sub . ' fa-fw dl-blue dl-big" title="' . $this->language->lang('DL_EDIT') . '"></i>';
				}
				else if ($mini_icon[$cat_id]['new'] && $mini_icon[$cat_id]['edit'])
				{
					$mini_cat_icon = '<i class="icon fa-folder' . $folder_sub . ' fa-fw dl-yellow dl-big" title="' . $this->language->lang('DL_NEW') . ' / ' . $this->language->lang('DL_EDIT') . '"></i>';
				}
				else
				{
					$mini_cat_icon = '<i class="icon fa-folder' . $folder_sub . ' fa-fw dl-big" title=""></i>';
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
					$this->template->set_filenames(['subcats' => 'view_dl_subcat_body.html']);
		
					$block = 'subcats';
		
					$this->template->assign_var('S_SUBCATS', true);
				}
				else
				{
					$block = 'downloads';
				}
		
				if (isset($index[$cat_id]['total']) && $index[$cat_id]['total'] > $this->config['dl_links_per_page'])
				{
					$pagination = $this->phpbb_container->get('pagination');
					$pagination->generate_template_pagination(
						[
							'routes' => [
								'oxpus_dlext_index',
								'oxpus_dlext_index',
							],
							'params' => ['cat' => $cat_id],
						], $block . 'pagination', 'start', $index[$cat_id]['total'], $this->config['dl_links_per_page'], $page_start);
		
					$cat_pages = true;
				}

				$this->template->assign_block_vars($block, [
					'MINI_IMG'			=> $mini_cat_icon,
					'SUBLEVEL'			=> $cat_sublevel,
					'CAT_DESC'			=> $cat_desc,
					'CAT_NAME'			=> $cat_name,
					'CAT_ICON'			=> $cat_icon,
					'CAT_ID'			=> $cat_id,
					'CAT_DL'			=> ((isset($index[$cat_id]['total'])) ? $index[$cat_id]['total'] : 0) + $this->dlext_main->get_sublevel_count($cat_id),
					'CAT_LAST_DL'		=> (isset($last_dl[$last_cat_id]['desc'])) ? $last_dl[$last_cat_id]['desc'] : '',
					'CAT_LAST_USER'		=> (isset($last_dl[$last_cat_id]['user'])) ? $last_dl[$last_cat_id]['user'] : '',
					'CAT_LAST_TIME'		=> (isset($last_dl[$last_cat_id]['time'])) ? $last_dl[$last_cat_id]['time'] : '',
					'CAT_LAST_TIME_RFC'	=> (isset($last_dl[$last_cat_id]['time_rfc'])) ? $last_dl[$last_cat_id]['time_rfc'] : '',
					'CAT_LAST_ICON'		=> (isset($last_dl[$last_cat_id]['icon'])) ? $last_dl[$last_cat_id]['icon'] : '',

					'U_CAT_VIEW'		=> $cat_view,
					'U_CAT_LAST_LINK'	=> (isset($last_dl[$last_cat_id]['link'])) ? $last_dl[$last_cat_id]['link'] : '',
					'U_CAT_LAST_USER'	=> (isset($last_dl[$last_cat_id]['user_link'])) ? $last_dl[$last_cat_id]['user_link'] : '',
				]);
		
				if ($cat_subs && $this->user->data['user_dl_sub_on_index'])
				{
					$this->template->assign_block_vars($block.'.sub', []);
		
					for ($j = 0; $j < sizeof($cat_subs); $j++)
					{
						$sub_id = $cat_sublevel['cat_id'][$j];
						$mini_icon = [];
						$mini_icon = $this->dlext_status->mini_status_cat($sub_id, $sub_id);

						$sub_sublevel = $this->dlext_main->get_sublevel($sub_id);
						$sub_subs = (isset($sub_sublevel['cat_path'])) ? true : false;

						$folder_sub = '';

						if ($sub_subs)
						{
							$folder_sub = '-open';
						}
		
						if ($mini_icon[$sub_id]['new'] && !$mini_icon[$sub_id]['edit'])
						{
							$mini_cat_icon = '<i class="icon fa-folder' . $folder_sub . ' fa-fw dl-red" title="' . $this->language->lang('DL_NEW') . '"></i>';
						}
						else if (!$mini_icon[$sub_id]['new'] && $mini_icon[$sub_id]['edit'])
						{
							$mini_cat_icon = '<i class="icon fa-folder' . $folder_sub . ' fa-fw dl-blue" title="' . $this->language->lang('DL_EDIT') . '"></i>';
						}
						else if ($mini_icon[$sub_id]['new'] && $mini_icon[$sub_id]['edit'])
						{
							$mini_cat_icon = '<i class="icon fa-folder' . $folder_sub . ' fa-fw dl-yellow" title="' . $this->language->lang('DL_NEW') . ' / ' . $this->language->lang('DL_EDIT') . '"></i>';
						}
						else
						{
							$mini_cat_icon = '<i class="icon fa-folder' . $folder_sub . ' fa-fw" title=""></i>';
						}
		
						$this->template->assign_block_vars($block.'.sub.sublevel_row', [
							'L_SUBLEVEL' => $cat_sublevel['cat_name'][$j],
							'SUBLEVEL_COUNT' => $cat_sublevel['total'][$j] + $this->dlext_main->get_sublevel_count($sub_id),
							'M_SUBLEVEL' => $mini_cat_icon,
							'M_SUBLEVEL_ICON' => (isset($cat_sublevel['cat_icon'][$j])) ? $cat_sublevel['cat_icon'][$j] : '',
							'U_SUBLEVEL' => $cat_sublevel['cat_path'][$j],
						]);
					}
				}
		
				if ($cat)
				{
					$this->template->assign_var('S_SUBCAT_BOX', true);
		
					$this->template->assign_display('subcats');
				}
			}
		}
		else
		{
			$this->template->assign_var('S_NO_CATEGORY', true);
		}
		
		if ($cat)
		{
			$index_cat = [];
			$index_cat = $this->dlext_main->full_index($cat);
			$total_downloads = (isset($index_cat[$cat]['total'])) ? $index_cat[$cat]['total'] : 0;
		
			if ($total_downloads > $this->config['dl_links_per_page'])
			{
				$pagination = $this->phpbb_container->get('pagination');
				$pagination->generate_template_pagination(
					[
						'routes' => [
							'oxpus_dlext_index',
							'oxpus_dlext_index',
						],
						'params' => ['cat' => $cat, 'sort_by' => $sort_by, 'order' => $order],
					], 'pagination', 'start', $total_downloads, $this->config['dl_links_per_page'], $page_start);
		
				$this->template->assign_vars([
					'PAGE_NUMBER'	=> $pagination->on_page($total_downloads, $this->config['dl_links_per_page'], $page_start),
					'TOTAL_DL'		=> $this->language->lang('VIEW_DOWNLOADS', $total_downloads),
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
		
				$this->template->assign_var('S_CAT_RULE', true);
			}
		
			if ($this->dlext_auth->user_auth($cat, 'auth_mod'))
			{
				$this->template->assign_var('S_MODCP', true);
			}
		
			$physical_size = $this->dlext_physical->read_dl_sizes();
			if ($physical_size < $this->config['dl_physical_quota'] && (!$this->config['dl_stop_uploads']) || ($this->auth->acl_get('a_') && $this->user->data['is_registered']))
			{
				if ($this->dlext_auth->user_auth($cat, 'auth_up'))
				{
					$this->template->assign_var('S_DL_UPLOAD', true);
				}
			}
		
			$cat_traffic = 0;
		
			if (!$this->config['dl_traffic_off'])
			{
				if ($this->user->data['is_registered'])
				{
					$cat_overall_traffic = $this->config['dl_overall_traffic'];
					$cat_limit = DL_OVERALL_TRAFFICS;
				}
				else
				{
					$cat_overall_traffic = $this->config['dl_overall_guest_traffic'];
					$cat_limit = DL_GUESTS_TRAFFICS;
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
					$cat_traffic = ($cat_traffic > $cat_overall_traffic && $cat_limit == true) ? $cat_overall_traffic : $cat_traffic;
					$cat_traffic = $this->dlext_format->dl_size($cat_traffic);
		
					$this->template->assign_var('S_CAT_TRAFFIC', true);
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
			$dl_files = [];
			$dl_files = $this->dlext_files->files($cat, $sql_sort_by, $sql_order, $start, $this->config['dl_links_per_page'], 'id, description, desc_uid, desc_bitfield, desc_flags, hack_version, extern, file_size, klicks, overall_klicks, rating, long_desc, long_desc_uid, long_desc_bitfield, long_desc_flags, add_user, broken');
		
			if ($this->dlext_auth->cat_auth_comment_read($cat))
			{
				$sql = 'SELECT COUNT(dl_id) AS total_comments, id FROM ' . DL_COMMENTS_TABLE . '
					WHERE cat_id = ' . (int) $cat . '
						AND approve = ' . true . '
					GROUP BY id';
				$result = $this->db->sql_query($sql);
		
				$comment_count = [];
				while ($row = $this->db->sql_fetchrow($result))
				{
					$comment_count[$row['id']] = $row['total_comments'];
				}
				$this->db->sql_freeresult($result);
			}
		
			for ($i = 0; $i < sizeof($dl_files); $i++)
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

				if ((int) $this->config['dl_limit_desc_on_index'] && strlen($long_desc) > (int) $this->config['dl_limit_desc_on_index'])
				{
					$long_desc = strip_tags($long_desc, '<br><br/>');
					$long_desc = substr($long_desc, 0, (int) $this->config['dl_limit_desc_on_index']) . ' [...]';
				}
		
				$dl_status = [];
				$dl_status = $this->dlext_status->status($file_id);
				$status = $dl_status['status'];

				$broken = $dl_files[$i]['broken'];
				
				if ($dl_files[$i]['file_size'])
				{
					$file_size = $this->dlext_format->dl_size($dl_files[$i]['file_size'], 2);
				}
				else
				{
					$file_size = $this->language->lang('DL_NOT_AVAILIBLE');
				}
		
				$file_klicks = $dl_files[$i]['klicks'];
				$file_overall_klicks = $dl_files[$i]['overall_klicks'];
		
				$s_rating_perm = false;
				$rating_count_text = '';
				$rating_points = 0;
		
				if ($cat && $this->config['dl_enable_rate'])
				{
					$rating_points = $dl_files[$i]['rating'];
		
					if ((!$rating_points || !@in_array($this->user->data['user_id'], $ratings[$file_id])) && $this->user->data['is_registered'])
					{
						$s_rating_perm = true;
					}
		
					if (isset($ratings[$file_id]))
					{
						$total_ratings = sizeof($ratings[$file_id]);
						if ($total_ratings == 1)
						{
							$rating_count_text = $this->language->lang('DL_RATING_ONE');
						}
						else
						{
							$rating_count_text = $this->language->lang('DL_RATING_MORE', $total_ratings);
						}
					}
					else
					{
						$rating_count_text = $this->language->lang('DL_RATING_NONE');
					}
				}
		
				$cat_edit_link = false;
		
				switch ($this->config['dl_cat_edit'])
				{
					case 1:
						if ($this->dlext_auth->user_admin())
						{
							$cat_edit_link = true;
						}
					break;
					case 2:
						if ($this->dlext_auth->user_admin() || $this->dlext_auth->user_auth($cat, 'auth_mod'))
						{
							$cat_edit_link = true;
						}
					break;
					case 3:
						if ($this->dlext_auth->user_admin() || $this->dlext_auth->user_auth($cat, 'auth_mod') || ($this->config['dl_edit_own_downloads'] && $dl_files[$i]['add_user'] == $this->user->data['user_id']))
						{
							$cat_edit_link = true;
						}
					break;
					default:
						$cat_edit_link = false;
				}
		
				$this->template->assign_block_vars('downloads', [
					'DESCRIPTION'			=> $description,
					'BROKEN'				=> $broken,
					'MINI_IMG'				=> $mini_file_icon,
					'HACK_VERSION'			=> $hack_version,
					'LONG_DESC'				=> ($this->config['dl_desc_index']) ? $long_desc : '',
					'RATING_IMG'			=> $this->dlext_format->rating_img($rating_points, $s_rating_perm, $file_id),
					'RATINGS'				=> $rating_count_text,
					'STATUS'				=> $status,
					'FILE_SIZE'				=> $file_size,
					'FILE_KLICKS'			=> $file_klicks,
					'FILE_OVERALL_KLICKS'	=> $file_overall_klicks,
					'DF_ID'					=> $file_id,
					'U_DIRECT_EDIT'			=> ($cat_edit_link) ? $this->helper->route('oxpus_dlext_mcp_edit', ['cat_id' => $cat, 'df_id' => $file_id]) : '',
					'U_FILE'				=> $file_url,
				]);
		
				if ($index_cat[$cat]['comments'] && ($this->dlext_auth->cat_auth_comment_read($cat) || $this->dlext_auth->cat_auth_comment_post($cat)))
				{
					$this->template->assign_block_vars('downloads.comments', ['U_COMMENT' => $this->helper->route('oxpus_dlext_details', ['view' => 'comment', 'action' => 'view', 'cat_id' => $cat, 'df_id' => $file_id])]);
				}
			}
		}
		
		if ($i)
		{
			$this->template->assign_var('S_DOWNLOAD_ROWS', true);
		
			if ($index_cat[$cat]['comments'] && $this->dlext_auth->cat_auth_comment_read($cat))
			{
				$sql = 'SELECT COUNT(dl_id) AS total_comments, id FROM ' . DL_COMMENTS_TABLE . '
					WHERE cat_id = ' . (int) $cat . '
						AND approve = ' . true . '
					GROUP BY id';
				$result = $this->db->sql_query($sql);
		
				$comment_count = [];
				while ($row = $this->db->sql_fetchrow($result))
				{
					$comment_count[$row['id']] = $row['total_comments'];
				}
				$this->db->sql_freeresult($result);
		
				$this->template->assign_block_vars('comment_header', []);
			}
		}
		
		if ($cat && !$total_downloads)
		{
			$this->template->assign_var('S_EMPTY_CATEGORY', true);
		}
		
		$this->template->assign_vars([
			'CAT_RULE'		=> (isset($cat_rule)) ? $cat_rule : '',
			'CAT_TRAFFIC'	=> (isset($cat_traffic)) ? $this->language->lang('DL_CAT_TRAFFIC_MAIN', $cat_traffic) : '',
			'T_DL_CAT'		=> (isset($index[$cat]['cat_name']) && $cat) ? $index[$cat]['cat_name'] : $this->language->lang('DL_CAT_NAME'),
			'DL_UPLOAD'		=> $this->helper->route('oxpus_dlext_upload', ['cat_id' => $cat]),
			'PHPEX'			=> $this->php_ext,
		
			'S_ENABLE_DESC_HIDE'	=> (isset($this->config['dl_index_desc_hide']) && $this->config['dl_index_desc_hide']) ? true : false,
			'S_ENABLE_RATE'			=> (isset($this->config['dl_enable_rate']) && $this->config['dl_enable_rate']) ? true : false,
		
			'U_DOWNLOADS'	=> ($cat) ? $this->helper->route('oxpus_dlext_index', ['cat' => $cat]) : $this->helper->route('oxpus_dlext_index'),
			'U_DL_SEARCH'	=> (sizeof($index) || $cat) ? $this->helper->route('oxpus_dlext_search') : '',
			'U_DL_AJAX'		=> $this->helper->route('oxpus_dlext_ajax'),
		]);

		/*
		* include the mod footer
		*/
		$dl_footer = $this->phpbb_container->get('oxpus.dlext.footer');
		$dl_footer->set_parameter($nav_view, $cat, 0, $index);
		$dl_footer->handle();
	}
}
