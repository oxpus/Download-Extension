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

class hacklist
{
	/* @var string phpBB root path */
	protected $root_path;

	/* @var string phpEx */
	protected $php_ext;

	/* @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var Container */
	protected $phpbb_container;

	/* @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/* @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var extension owned objects */
	protected $ext_path;
	protected $ext_path_web;
	protected $ext_path_ajax;

	protected $dlext_auth;
	protected $dlext_hacklist;
	protected $dlext_main;

	/**
	* Constructor
	*
	* @param string									$root_path
	* @param string									$php_ext
	* @param \phpbb\request\request_interface 		$request
	* @param \phpbb\config\config					$config
	* @param \phpbb\language\language				$language
	* @param \phpbb\template\template				$template
	* @param Container 								$phpbb_container
	* @param \phpbb\extension\manager				$phpbb_extension_manager
	* @param \phpbb\path_helper						$phpbb_path_helper
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\user							$user
	* @param \phpbb\db\driver\driver_interfacer		$db
	*/
	public function __construct(
		$root_path,
		$php_ext,
		\phpbb\request\request_interface $request,
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\controller\helper $helper,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		$dlext_auth,
		$dlext_hacklist,
		$dlext_main
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->request					= $request;
		$this->config 					= $config;
		$this->language					= $language;
		$this->template 				= $template;
		$this->phpbb_container 			= $phpbb_container;
		$this->phpbb_extension_manager 	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->helper 					= $helper;
		$this->user						= $user;
		$this->db						= $db;

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_hacklist			= $dlext_hacklist;
		$this->dlext_main				= $dlext_main;
	}

	public function handle()
	{
		$nav_view = 'hacks';

		// Include the default base init script
		include_once($this->ext_path . 'phpbb/includes/base_init.' . $this->php_ext);

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
		$hacklist = $this->dlext_hacklist->hacks_index();
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
		$dl_files = $this->dlext_hacklist->all_files($sql_sort_by, $sql_order, $start, $this->config['dl_links_per_page']);

		$all_files = array();
		$all_files = $this->dlext_hacklist->all_files('id', 'ASC');

		if ($all_files > $this->config['dl_links_per_page'])
		{
			$pagination = $this->phpbb_container->get('pagination');
			$pagination->generate_template_pagination(
				array(
					'routes' => array(
						'oxpus_dlext_hacklist',
						'oxpus_dlext_hacklist',
					),
					'params' => array('sort_by' => $sort_by, 'order' => $order),
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

			'S_FORM_ACTION'		=> $this->helper->route('oxpus_dlext_hacklist'),
		));

		if (sizeof($dl_files))
		{
			for ($i = 0; $i < sizeof($dl_files); $i++)
			{
				$cat_id = $dl_files[$i]['cat'];
				if ($hacklist[$cat_id])
				{
					$hack_name				= $dl_files[$i]['description'];
					$desc_uid				= $dl_files[$i]['desc_uid'];
					$desc_bitfield			= $dl_files[$i]['desc_bitfield'];
					$desc_flags				= $dl_files[$i]['desc_flags'];
					$hack_name			= generate_text_for_display($hack_name, $desc_uid, $desc_bitfield, $desc_flags);
			
					$hack_author			= ($dl_files[$i]['hack_author'] != '') ? $dl_files[$i]['hack_author'] : 'n/a';
					$hack_author_email		= $dl_files[$i]['hack_author_email'];
					$hack_author_website	= $dl_files[$i]['hack_author_website'];
					$hackname				= ($dl_files[$i]['hacklist'] != '') ? '&nbsp;'.$dl_files[$i]['description'] : '';
					$hack_version			= ($dl_files[$i]['hacklist'] != '') ? '&nbsp;'.$dl_files[$i]['hack_version'] : '';
					$hack_dl_url			= $dl_files[$i]['hack_dl_url'];
					$description			= $dl_files[$i]['long_desc'];
					$uid					= $dl_files[$i]['long_desc_uid'];
					$bitfield				= $dl_files[$i]['long_desc_bitfield'];
					$flags					= (isset($dl_files[$i]['long_desc_flags'])) ? $dl_files[$i]['long_desc_flags'] : 0;

					$description = generate_text_for_display($description, $uid, $bitfield, $flags);

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

		/*
		* include the mod footer
		*/
		$dl_footer = $this->phpbb_container->get('oxpus.dlext.footer');
		$dl_footer->set_parameter($nav_view);
		$dl_footer->handle();
	}
}
