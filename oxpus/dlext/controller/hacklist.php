<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class hacklist
{
	/* phpbb objects */
	protected $root_path;
	protected $php_ext;
	protected $request;
	protected $config;
	protected $language;
	protected $template;
	protected $helper;
	protected $pagination;

	/* extension owned objects */
	protected $dlext_hacklist;
	protected $dlext_footer;
	protected $dlext_constants;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param string								$php_ext
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\pagination						$pagination
	 * @param \oxpus\dlext\core\hacklist			$dlext_hacklist
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 */
	public function __construct(
		$root_path,
		$php_ext,
		\phpbb\request\request $request,
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\controller\helper $helper,
		\phpbb\pagination $pagination,
		\oxpus\dlext\core\hacklist $dlext_hacklist,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		\oxpus\dlext\core\helpers\constants $dlext_constants
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->request					= $request;
		$this->config 					= $config;
		$this->language					= $language;
		$this->template 				= $template;
		$this->helper 					= $helper;
		$this->pagination				= $pagination;

		$this->dlext_hacklist			= $dlext_hacklist;
		$this->dlext_footer				= $dlext_footer;
		$this->dlext_constants			= $dlext_constants;
	}

	public function handle()
	{
		$order		= $this->request->variable('order', '');
		$start		= $this->request->variable('start', 0);
		$sort_by	= $this->request->variable('sort_by', 0);

		switch ($sort_by)
		{
			case $this->dlext_constants::DL_HACKLIST_SORT_DESC:
				$sql_sort_by = 'long_desc';
				break;
			case $this->dlext_constants::DL_HACKLIST_SORT_AUTHOR:
				$sql_sort_by = 'hack_author';
				break;
			default:
				$sql_sort_by = 'description';
		}

		$sql_order = ($order) ? $order : 'ASC';

		$hacklist = $this->dlext_hacklist->hacks_index();
		$status = $this->config['dl_use_hacklist'];

		if (!$status || empty($hacklist))
		{
			redirect(append_sid($this->root_path . 'index.' . $this->php_ext));
		}

		$dl_files = $this->dlext_hacklist->all_files($sql_sort_by, $sql_order, $start, $this->config['dl_links_per_page']);

		$all_files = $this->dlext_hacklist->all_files('id', 'ASC');

		if ($all_files > $this->config['dl_links_per_page'])
		{
			$this->pagination->generate_template_pagination(
				$this->helper->route('oxpus_dlext_hacklist', ['sort_by' => $sort_by, 'order' => $order]),
				'pagination',
				'start',
				$all_files,
				$this->config['dl_links_per_page'],
				$start
			);

			$this->template->assign_vars([
				'DL_PAGE_NUMBER'	=> $this->pagination->on_page($all_files, $this->config['dl_links_per_page'], $start),
				'DL_TOTAL_DL'		=> $this->language->lang('DL_VIEW_DL_STATS', $all_files),
			]);
		}

		$selected_default		= ($sort_by == $this->dlext_constants::DL_SORT_DEFAULT) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
		$selected_description	= ($sort_by == $this->dlext_constants::DL_HACKLIST_SORT_DESC) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
		$selected_author		= ($sort_by == $this->dlext_constants::DL_HACKLIST_SORT_AUTHOR) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

		$selected_sort_asc		= ($order == 'ASC') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
		$selected_sort_desc		= ($order == 'DESC') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

		$this->template->assign_vars([
			'S_DL_SELECTED_DEFAULT'		=> $selected_default,
			'S_DL_SELECTED_DESCRIPTION'	=> $selected_description,
			'S_DL_SELECTED_AUTHOR'		=> $selected_author,

			'S_DL_SELECTED_SORT_ASC'		=> $selected_sort_asc,
			'S_DL_SELECTED_SORT_DESC'		=> $selected_sort_desc,

			'S_DL_FORM_ACTION'			=> $this->helper->route('oxpus_dlext_hacklist'),
		]);

		if (!empty($dl_files))
		{
			for ($i = 0; $i < count($dl_files); ++$i)
			{
				$cat_id = $dl_files[$i]['cat'];
				if ($hacklist[$cat_id])
				{
					$hack_name				= $dl_files[$i]['description'];
					$desc_uid				= $dl_files[$i]['desc_uid'];
					$desc_bitfield			= $dl_files[$i]['desc_bitfield'];
					$desc_flags				= $dl_files[$i]['desc_flags'];
					$hack_name				= generate_text_for_display($hack_name, $desc_uid, $desc_bitfield, $desc_flags);

					$hack_author			= ($dl_files[$i]['hack_author'] != '') ? $dl_files[$i]['hack_author'] : 'n/a';
					$hack_author_email		= $dl_files[$i]['hack_author_email'];
					$hack_author_website	= $dl_files[$i]['hack_author_website'];
					$hack_version			= ($dl_files[$i]['hacklist'] != '') ? '&nbsp;' . $dl_files[$i]['hack_version'] : '';
					$hack_dl_url			= $dl_files[$i]['hack_dl_url'];

					$description			= $dl_files[$i]['long_desc'];
					$uid					= $dl_files[$i]['long_desc_uid'];
					$bitfield				= $dl_files[$i]['long_desc_bitfield'];
					$flags					= (isset($dl_files[$i]['long_desc_flags'])) ? $dl_files[$i]['long_desc_flags'] : 0;
					$description			= generate_text_for_display($description, $uid, $bitfield, $flags);

					$this->template->assign_block_vars('dl_listrow', [
						'DL_CAT_NAME'				=> $hacklist[$cat_id],
						'DL_HACK_NAME'				=> $hack_name . $hack_version,
						'DL_HACK_DESCRIPTION'		=> $description,
						'DL_HACK_AUTHOR'			=> $hack_author,
						'DL_HACK_AUTHOR_MAIL'		=> $hack_author_email,
						'DL_HACK_AUTHOR_WEBSITE'	=> $hack_author_website,
						'DL_HACK_DL_URL'			=> $hack_dl_url,
					]);
				}
			}
		}

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter('hacks');
		$this->dlext_footer->handle();

		/*
		* generate page
		*/
		return $this->helper->render('@oxpus_dlext/hacks_list_body.html', $this->language->lang('DL_HACKS_LIST'));
	}
}
