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

class overall
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

	protected $phpbb_dispatcher;

	protected $dlext_auth;
	protected $dlext_files;
	protected $dlext_format;
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
	* @param \phpbb\event\dispatcher_interface		$phpbb_dispatcher
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
		\phpbb\event\dispatcher_interface $phpbb_dispatcher,
		$dlext_auth,
		$dlext_files,
		$dlext_format,
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
		$this->phpbb_dispatcher			= $phpbb_dispatcher;

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_status				= $dlext_status;
	}

	public function handle()
	{
		$nav_view = 'overall';

		// Include the default base init script
		include_once($this->ext_path . 'phpbb/includes/base_init.' . $this->php_ext);

		/*
		* init sorting the downloads
		*/
		include_once($this->ext_path . 'phpbb/includes/sort_init.' . $this->php_ext);

		if (!empty($index) && $this->config['dl_overview_link_onoff'])
		{
			page_header($this->language->lang('DOWNLOADS') . ' ' . $this->language->lang('DL_OVERVIEW'));

			$sql = 'SELECT dl_id, user_id FROM ' . DL_RATING_TABLE;
			$result = $this->db->sql_query($sql);
			
			$ratings = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$ratings[$row['dl_id']][] = $row['user_id'];
			}
			$this->db->sql_freeresult($result);
			
			$this->template->set_filenames(['body' => 'dl_overview_body.html']);
			
			$this->template->assign_vars([
				'S_FORM_ACTION'		=> $this->helper->route('oxpus_dlext_overall'),
			
				'U_DL_INDEX'		=> $this->helper->route('oxpus_dlext_index'),
				'U_DL_AJAX'			=> $this->helper->route('oxpus_dlext_ajax'),
			
				'PAGE_NAME'			=> $this->language->lang('DOWNLOADS') . ' ' . $this->language->lang('DL_OVERVIEW'),
			]);
			
			$dl_files = [];
			$dl_files = $this->dlext_files->all_files(0, '', '', '', 0, 0, 'id, cat');
			
			$total_files = 0;
			
			if (!empty($dl_files))
			{
				for ($i = 0; $i < count($dl_files); ++$i)
				{
					$cat_id = $dl_files[$i]['cat'];
					$cat_auth = [];
					$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);
					if (isset($cat_auth['auth_view']) && $cat_auth['auth_view'] || isset($index[$cat_id]['auth_view']) && $index[$cat_id]['auth_view'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered']))
					{
						++$total_files;
					}
				}
			}
			
			if ($total_files)
			{
				$this->template->assign_var('S_OVERALL_VIEW', true);
			}
			
			if ($total_files > $this->config['dl_links_per_page'])
			{
				$pagination = $this->phpbb_container->get('pagination');
				$pagination->generate_template_pagination(
					$this->helper->route('oxpus_dlext_overall', ['sort_by' => $sort_by, 'order' => $order]),
					'pagination',
					'start',
					$total_files,
					$this->config['dl_links_per_page'],
					$page_start
				);
			
				$this->template->assign_vars([
					'PAGE_NUMBER'	=> $pagination->on_page($total_files, $this->config['dl_links_per_page'], $page_start),
					'TOTAL_DL'		=> $this->language->lang('VIEW_DOWNLOADS', $total_files),
				]);
			}
			
			$sql_sort_by = ($sql_sort_by == 'sort') ? 'cat, sort' : $sql_sort_by;
			
			$dl_files = [];
			$dl_files = $this->dlext_files->all_files(0, '', '', ' ORDER BY ' . $sql_sort_by . ' ' . $sql_order . ' LIMIT ' . $start . ', ' . $this->config['dl_links_per_page'], 0, 0, 'cat, id, description, desc_uid, desc_bitfield, desc_flags, hack_version, extern, file_size, klicks, overall_klicks, rating');

			/**
			 * Fetch additional data for the downloads
			 *
			 * @event oxpus.dlext.overall_fetch_download_data
			 * @var int		cat					download category ID
			 * @var string	sql_sort_by			sql order by fields
			 * @var string	sql_order			sql order by direction
			 * @var string	sql_latest_where	additional where conditions
			 * @since 8.1.0-RC2
			 */
			$cat = 0;
			$sql_latest_where = '';
			$vars = array(
				'cat',
				'sql_sort_by',
				'sql_order',
				'sql_latest_where',
			);
			extract($this->phpbb_dispatcher->trigger_event('oxpus.dlext.overall_fetch_download_data', compact($vars)));

			if (!empty($dl_files))
			{
				for ($i = 0; $i < count($dl_files); ++$i)
				{
					$cat_id = $dl_files[$i]['cat'];
					$cat_auth = [];
					$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);
					if (isset($cat_auth['auth_view']) && $cat_auth['auth_view'] || isset($index[$cat_id]['auth_view']) && $index[$cat_id]['auth_view'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered']))
					{
						$cat_name = $index[$cat_id]['cat_name'];
						$cat_name = str_replace('&nbsp;&nbsp;|', '', $cat_name);
						$cat_name = str_replace('___&nbsp;', '', $cat_name);
						$cat_view = $index[$cat_id]['nav_path'];
			
						$file_id = $dl_files[$i]['id'];
						$mini_file_icon = $this->dlext_status->mini_status_file($cat_id, $file_id);
			
						$description = $dl_files[$i]['description'];
						$desc_uid = $dl_files[$i]['desc_uid'];
						$desc_bitfield = $dl_files[$i]['desc_bitfield'];
						$desc_flags = $dl_files[$i]['desc_flags'];
						$description = censor_text($description);
						$description = generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);
			
						$dl_link = $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]);
			
						$hack_version = '&nbsp;'.$dl_files[$i]['hack_version'];
			
						$dl_status = [];
						$dl_status = $this->dlext_status->status($file_id);
						$status = $dl_status['status'];
			
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
			
						$rating_points = $dl_files[$i]['rating'];
						$s_rating_perm = false;
			
						if ($this->config['dl_enable_rate'] && ($rating_points == 0 || !@in_array($this->user->data['user_id'], $ratings[$file_id])) && $this->user->data['is_registered'])
						{
							$s_rating_perm = true;
						}
			
						if (!empty($ratings[$file_id]) && $this->config['dl_enable_rate'])
						{
							$total_ratings = count($ratings[$file_id]);
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
			
						$this->template->assign_block_vars('downloads', [
							'CAT_NAME'				=> $cat_name,
							'DESCRIPTION'			=> $mini_file_icon.$description,
							'FILE_KLICKS'			=> $file_klicks,
							'FILE_OVERALL_KLICKS'	=> $file_overall_klicks,
							'FILE_SIZE'				=> $file_size,
							'HACK_VERSION'			=> $hack_version,
							'RATING_IMG'			=> $this->dlext_format->rating_img($rating_points, $s_rating_perm, $file_id),
							'RATINGS'				=> $rating_count_text,
							'STATUS'				=> $status,
							'DF_ID'					=> $file_id,
			
							'U_CAT_VIEW'			=> $cat_view,
							'U_DL_LINK'				=> $dl_link,
						]);

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
						extract($this->phpbb_dispatcher->trigger_event('oxpus.dlext.overall_display_data_after', compact($vars)));
					}
				}
			
				$this->template->assign_var('S_ENABLE_RATE', (isset($this->config['dl_enable_rate']) && $this->config['dl_enable_rate']) ? true : false);
			}
			
			/*
			* include the mod footer
			*/
			$dl_footer = $this->phpbb_container->get('oxpus.dlext.footer');
			$dl_footer->set_parameter($nav_view, 0, 0, $index);
			$dl_footer->handle();
		}

		redirect($this->helper->route('oxpus_dlext_index'));
	}
}
