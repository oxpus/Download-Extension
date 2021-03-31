<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller;

class overall
{
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
	* @param \oxpus\dlext\core\auth					$dlext_auth
	* @param \oxpus\dlext\core\files				$dlext_files
	* @param \oxpus\dlext\core\format				$dlext_format
	* @param \oxpus\dlext\core\main					$dlext_main
	* @param \oxpus\dlext\core\status				$dlext_status
	* @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	* @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	* @param string									$dlext_table_dl_ratings
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

		$this->dlext_main->dl_handle_active();
	}

	public function handle()
	{
		$order		= $this->request->variable('order', '');
		$start		= $this->request->variable('start', 0);
		$sort_by	= $this->request->variable('sort_by', 0);

		$index		= $this->dlext_main->full_index();

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

		if (!empty($index) && $this->config['dl_overview_link_onoff'])
		{
			$sql = 'SELECT dl_id, user_id FROM ' . $this->dlext_table_dl_ratings;
			$result = $this->db->sql_query($sql);

			$ratings = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$ratings[$row['dl_id']][] = $row['user_id'];
			}
			$this->db->sql_freeresult($result);

			$this->template->assign_vars([
				'S_DL_FORM_ACTION'	=> $this->helper->route('oxpus_dlext_overall'),

				'U_DL_INDEX'		=> $this->helper->route('oxpus_dlext_index'),
				'U_DL_AJAX'			=> $this->helper->route('oxpus_dlext_rate'),
			]);

			$dl_files = $this->dlext_files->all_files(0, [], [], 0, 0, ['id', 'cat']);

			$total_files = 0;

			if (!empty($dl_files))
			{
				for ($i = 0; $i < count($dl_files); ++$i)
				{
					$cat_id = $dl_files[$i]['cat'];
					$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);
					if (isset($cat_auth['auth_view']) && $cat_auth['auth_view'] || isset($index[$cat_id]['auth_view']) && $index[$cat_id]['auth_view'] || $this->dlext_auth->user_admin())
					{
						++$total_files;
					}
				}
			}

			if ($total_files)
			{
				$this->template->assign_var('S_DL_OVERALL_VIEW', $this->dlext_constants::DL_TRUE);
			}

			if ($total_files > $this->config['dl_links_per_page'])
			{
				$this->pagination->generate_template_pagination(
					$this->helper->route('oxpus_dlext_overall', ['sort_by' => $sort_by, 'order' => $order]),
					'pagination',
					'start',
					$total_files,
					$this->config['dl_links_per_page'],
					$start
				);

				$this->template->assign_vars([
					'DL_PAGE_NUMBER'	=> $this->pagination->on_page($total_files, $this->config['dl_links_per_page'], $start),
					'DL_TOTAL_DL'		=> $this->language->lang('DL_VIEW_DOWNLOADS', $total_files),
				]);
			}

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
			$dl_files = $this->dlext_files->all_files(0, $sort_ary, [], 0, 0, $fields, $this->config['dl_links_per_page'], $start);

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

						$hack_version	= '&nbsp;'.$dl_files[$i]['hack_version'];

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
	
							'U_DL_CAT_VIEW'				=> $cat_view,
							'U_DL_LINK'					=> $dl_link,
						]);

						if ($rating_img_data != $this->dlext_constants::DL_FALSE)
						{
							foreach ($rating_img_data['stars'] as $key => $data)
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

				$this->template->assign_var('S_DL_ENABLE_RATE', (isset($this->config['dl_enable_rate']) && $this->config['dl_enable_rate']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE);
			}

			/*
			* include the mod footer
			*/
			$this->dlext_footer->set_parameter('overall', 0, 0, $index);
			$this->dlext_footer->handle();

			/*
			* generate page
			*/
			return $this->helper->render('@oxpus_dlext/dl_overview_body.html', $this->language->lang('DL_OVERVIEW_TITLE'));
		}

		redirect($this->helper->route('oxpus_dlext_index'));
	}
}
