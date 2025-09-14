<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class overall
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUnusedVariableNames cat block

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
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_status;
	protected $dlext_footer;
	protected $dlext_constants;

	protected $dlext_table_dl_ratings;

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
	 * @param \oxpus\dlext\core\files				$dlext_files
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\status				$dlext_status
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_ratings
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
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\status $dlext_status,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_ratings
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

		$this->dlext_table_dl_ratings	= $dlext_table_dl_ratings;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_status				= $dlext_status;
		$this->dlext_footer				= $dlext_footer;
		$this->dlext_constants			= $dlext_constants;
	}

	public function overview()
	{
		$this->handle('overall');

		return $this->helper->render('@oxpus_dlext/dl_overview_body.html', $this->language->lang('DL_OVERVIEW_TITLE'));
	}

	public function latest()
	{
		if (!$this->config['dl_latest_type'])
		{
			redirect($this->helper->route('oxpus_dlext_index'));
		}

		$this->handle('latest');

		return $this->helper->render('@oxpus_dlext/dl_overview_body.html', $this->language->lang('DL_LATEST_DOWNLOADS'));
	}

	private function handle($mode)
	{
		$this->dlext_main->dl_handle_active();

		$order		= $this->request->variable('order', '');
		$start		= $this->request->variable('start', 0);
		$sort_by	= $this->request->variable('sort_by', 0);

		$index		= $this->dlext_main->full_index();

		if ($mode == 'overall' && (empty($index) || !$this->config['dl_overview_link_onoff']))
		{
			redirect($this->helper->route('oxpus_dlext_index'));
		}

		$sql = 'SELECT dl_id, user_id FROM ' . $this->dlext_table_dl_ratings;
		$result = $this->db->sql_query($sql);

		$ratings = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ratings[$row['dl_id']][] = $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'S_DL_FORM_ACTION'	=> $this->helper->route('oxpus_dlext_' . $mode),

			'U_DL_INDEX'		=> $this->helper->route('oxpus_dlext_index'),
			'U_DL_AJAX'			=> $this->helper->route('oxpus_dlext_rate'),
		]);

		$total_files = 0;
		$access_cats = [];

		foreach (array_keys($index) as $cat_id)
		{
			if (!empty($index[$cat_id]['total']))
			{
				$total_files += $index[$cat_id]['total'];
			}
			$access_cats[] = $cat_id;
		}

		if ($total_files)
		{
			$this->template->assign_var('S_DL_OVERALL_VIEW', $this->dlext_constants::DL_TRUE);
		}
		else
		{
			redirect($this->helper->route('oxpus_dlext_index'));
		}

		if ($mode == 'latest')
		{
			$latest_where = ['{cat_perm}' => ['AND', 'IN', $this->db->sql_in_set('cat', $access_cats)]];

			if ($this->config['dl_latest_type'] == $this->dlext_constants::DL_LATEST_TYPE_DEFAULT)
			{
				$sql_sort_by = '';
				$sql_order = '';

				$this->dlext_files->dl_sorting($sort_by, $order, $sql_sort_by, $sql_order);

				$check_add_time		= time() - ($this->config['dl_new_time'] * $this->dlext_constants::DL_ONE_DAY);
				$check_edit_time	= time() - ($this->config['dl_edit_time'] * $this->dlext_constants::DL_ONE_DAY);

				$latest_where += ['add_time' => ['AND', '>=', (int) $check_add_time]];
				$latest_where += ['change_time' => ['OR', '>=', (int) $check_edit_time]];

				if ($sql_sort_by == 'sort')
				{
					$sort_ary = ['cat' => $sql_order, 'sort' => $sql_order];
				}
				else
				{
					$sort_ary = [$sql_sort_by => $sql_order];
				}
			}
			else if ($this->config['dl_latest_type'] == $this->dlext_constants::DL_LATEST_TYPE_NEW)
			{
				$sql_sort_by = '';
				$sql_order = '';

				$this->dlext_files->dl_sorting($sort_by, $order, $sql_sort_by, $sql_order);

				$check_add_time		= time() - ($this->config['dl_new_time'] * $this->dlext_constants::DL_ONE_DAY);

				$latest_where += ['add_time' => ['AND', '>=', (int) $check_add_time]];

				if ($sql_sort_by == 'sort')
				{
					$sort_ary = ['cat' => $sql_order, 'sort' => $sql_order];
				}
				else
				{
					$sort_ary = [$sql_sort_by => $sql_order];
				}
			}
			else
			{
				$sort_ary = ['change_time' => 'DESC'];
			}

			$fields = ['cat', 'id', 'description', 'desc_uid', 'desc_bitfield', 'desc_flags', 'hack_version', 'extern', 'file_size', 'klicks', 'overall_klicks', 'rating'];
			$dl_files = $this->dlext_files->all_files(0, $sort_ary, $latest_where, 0, 0, $fields, $this->config['dl_links_per_page'], $start);
		}
		else
		{
			$sql_sort_by = '';
			$sql_order = '';

			$this->dlext_files->dl_sorting($sort_by, $order, $sql_sort_by, $sql_order);

			if ($sql_sort_by == 'sort')
			{
				$sort_ary = [
					'cat'	=> $sql_order,
					'sort'	=> $sql_order,
				];
			}
			else
			{
				$sort_ary = [
					$sql_sort_by => $sql_order,
				];
			}

			$fields = ['cat', 'id', 'description', 'desc_uid', 'desc_bitfield', 'desc_flags', 'hack_version', 'extern', 'file_size', 'klicks', 'overall_klicks', 'rating'];
			$where_cats = ['{cat_perm}' => ['AND', 'IN', $this->db->sql_in_set('cat', $access_cats)]];
			$dl_files = $this->dlext_files->all_files(0, $sort_ary, $where_cats, 0, 0, $fields, $this->config['dl_links_per_page'], $start);
		}

		if ($total_files > $this->config['dl_links_per_page'])
		{
			$this->pagination->generate_template_pagination(
				$this->helper->route('oxpus_dlext_' . $mode, ['sort_by' => $sort_by, 'order' => $order]),
				'pagination',
				'start',
				$total_files,
				$this->config['dl_links_per_page'],
				$start
			);

			$this->template->assign_vars([
				'DL_PAGE_NUMBER'	=> $this->pagination->on_page($total_files, $this->config['dl_links_per_page'], $start),
				'DL_TOTAL_DL'		=> $this->language->lang('DL_VIEW_DOWNLOADS_NUM', $total_files),
			]);
		}

		/**
		 * Fetch additional data for the downloads
		 *
		 * @event oxpus.dlext.overall_fetch_download_data
		 * @var int		cat					download category ID
		 * @var array	sort_ary		order paired filename => direction
		 * @var array	latest_where	additional where conditions filename => condition|operator|value
		 * @since 8.1.0-RC2
		 */
		$cat = 0;
		$latest_where = '';
		$vars = array(
			'cat',
			'sort_ary',
			'latest_where',
		);
		extract($this->dispatcher->trigger_event('oxpus.dlext.overall_fetch_download_data', compact($vars)));

		if (!empty($dl_files))
		{
			for ($i = 0; $i < count($dl_files); ++$i)
			{
				$cat_id = $dl_files[$i]['cat'];
				$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);

				if (isset($cat_auth['auth_view']) && $cat_auth['auth_view'] || isset($index[$cat_id]['auth_view']) && $index[$cat_id]['auth_view'] || $this->dlext_auth->user_admin())
				{
					$cat_name		= $index[$cat_id]['cat_name_nav'];
					$cat_view		= $index[$cat_id]['nav_path'];

					$file_id		= $dl_files[$i]['id'];
					$mini_file_icon	= $this->dlext_status->mini_status_file($cat_id, $file_id);

					$description	= $dl_files[$i]['description'];
					$desc_uid		= $dl_files[$i]['desc_uid'];
					$desc_bitfield	= $dl_files[$i]['desc_bitfield'];
					$desc_flags		= $dl_files[$i]['desc_flags'];
					$description	= censor_text($description);
					$description	= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

					$dl_link		= $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]);

					$hack_version	= '&nbsp;' . $dl_files[$i]['hack_version'];

					$check_status	= $this->dlext_status->status($file_id);
					$file_status	= $check_status['file_status'];

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

					$rating_points = $dl_files[$i]['rating'];
					$s_rating_perm = $this->dlext_constants::DL_FALSE;

					if ($this->config['dl_enable_rate'] && ($rating_points == 0 || !in_array($this->user->data['user_id'], $ratings[$file_id])) && $this->user->data['is_registered'])
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

					$s_display_thumbnail = $this->dlext_constants::DL_FALSE;

					if (!empty($dl_files[$i]['thumbnail']) && (($this->config['dl_thumbs_display_' . $mode] == $this->dlext_constants::DL_THUMBS_DISPLAY_ON) || ($this->config['dl_thumbs_display_' . $mode] == $this->dlext_constants::DL_THUMBS_DISPLAY_CAT && $index[$cat_id]['display_thumbs'])))
					{
						$s_display_thumbnail = $this->dlext_constants::DL_TRUE;
					}

					/*
					* Build rating imageset
					*/
					$rating_img_data = $this->dlext_format->rating_img($rating_points, $s_rating_perm, $file_id, $total_ratings);

					$this->template->assign_block_vars('downloads', [
						'DL_CAT_NAME'				=> $cat_name,
						'DL_MINI_IMG'				=> $mini_file_icon,
						'DL_DESCRIPTION'			=> $description,
						'DL_FILE_KLICKS'			=> $file_klicks,
						'DL_FILE_OVERALL_KLICKS'	=> $file_overall_klicks,
						'DL_FILE_SIZE'				=> $file_size,
						'DL_HACK_VERSION'			=> $hack_version,
						'DL_FILE_STATUS'			=> $file_status,
						'DL_DF_ID'					=> $file_id,
						'DL_RATE_COUNT'				=> ($rating_img_data != $this->dlext_constants::DL_FALSE) ? $rating_img_data['count']['count'] : '',
						'DL_RATE_UNDO'				=> ($rating_img_data != $this->dlext_constants::DL_FALSE) ? $rating_img_data['count']['undo'] : '',
						'DL_RATE_TITLE'				=> ($rating_img_data != $this->dlext_constants::DL_FALSE) ? $rating_img_data['count']['title'] : '',
						'DL_THUMBNAIL_PIC'			=> $this->helper->route('oxpus_dlext_thumbnail', ['pic' => $file_id, 'img_type' => 'thumb_list', 'disp_art' => $this->dlext_constants::DL_TRUE]),

						'S_DISPLAY_THUMBNAIL'		=> $s_display_thumbnail,

						'U_DL_CAT_VIEW'				=> $cat_view,
						'U_DL_LINK'					=> $dl_link,
					]);

					if ($rating_img_data != $this->dlext_constants::DL_FALSE)
					{
						foreach (array_keys($rating_img_data['stars']) as $key)
						{
							$this->template->assign_block_vars('downloads.rating_img', [
								'DL_RATE_STAR' 	=> $rating_img_data['stars'][$key]['icon'],
								'DL_RATE_AJAX'	=> $rating_img_data['stars'][$key]['ajax'],
							]);
						}
					}

					/**
					 * Fetch additional data for the downloads
					 *
					 * @event oxpus.dlext.overall_display_data_after
					 * @var string	block		template row key
					 * @var int		file_id		download id
					 * @since 8.1.0-RC2
					 */
					$block = 'downloads';
					$vars = array(
						'block',
						'file_id',
					);
					extract($this->dispatcher->trigger_event('oxpus.dlext.overall_display_data_after', compact($vars)));
				}
			}

			$this->template->assign_var('S_DL_ENABLE_RATE', $this->config['dl_enable_rate']);
		}

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter($mode, 0, 0, $index);
		$this->dlext_footer->handle();
	}
}
