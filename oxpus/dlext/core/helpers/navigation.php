<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core\helpers;

class navigation implements navigation_interface
{
	/* phpbb objects */
	protected $language;
	protected $template;
	protected $helper;
	protected $db;

	/* extension owned objects */
	protected $nav_mode;
	protected $cat_id;
	protected $df_id;

	protected $dlext_auth;
	protected $dlext_nav;

	protected $dlext_table_downloads;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\nav					$dlext_nav
	 * @param string								$dlext_table_downloads
	 */
	public function __construct(
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\controller\helper $helper,
		\phpbb\db\driver\driver_interface $db,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\nav $dlext_nav,
		$dlext_table_downloads
	)
	{
		$this->language			= $language;
		$this->template 		= $template;
		$this->helper 			= $helper;
		$this->db 				= $db;

		$this->dlext_auth		= $dlext_auth;
		$this->dlext_nav		= $dlext_nav;

		$this->dlext_table_downloads	= $dlext_table_downloads;
	}

	public function set_parameter($nav_view = '', $cat_id = 0, $df_id = 0)
	{
		$this->nav_mode = $nav_view;
		$this->cat_id	= $cat_id;
		$this->df_id	= $df_id;
	}

	public function handle()
	{
		if ($this->nav_mode)
		{
			/*
			* Build the navigation and check the permissions for the user
			*/
			$nav_string = [];

			if ($this->nav_mode != 'hacks')
			{
				$nav_string['link'][] =  $this->helper->route('oxpus_dlext_index');
				$nav_string['name'][] = $this->language->lang('DL_CAT_TITLE');
			}

			if ($this->cat_id)
			{
				$cat_auth = $this->dlext_auth->user_auth($this->cat_id, 'auth_view');

				if (!$cat_auth)
				{
					redirect($this->helper->route('oxpus_dlext_index'));
				}

				$dl_nav = [];
				$this->dlext_nav->nav($this->cat_id, $dl_nav);

				if (!empty($dl_nav))
				{
					for ($i = count($dl_nav); $i > 0; --$i)
					{
						$key = $i - 1;

						$nav_string['name'][] = $dl_nav[$key]['name'];
						$nav_string['link'][] = $this->helper->route('oxpus_dlext_index', ['cat' => $dl_nav[$key]['cat_id']]);
					}
				}
			}

			switch ($this->nav_mode)
			{
				case 'overall':
					$nav_string['name'][] = $this->language->lang('DL_OVERVIEW');
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_overall');
					break;
				case 'latest':
					$nav_string['name'][] = $this->language->lang('DL_LATEST_DOWNLOADS');
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_latest');
					break;
				case 'version':
				case 'details':
				case 'broken':
				case 'comment':
					$sql = 'SELECT description, desc_uid, desc_bitfield, desc_flags FROM ' . $this->dlext_table_downloads . '
							WHERE id = ' . (int) $this->df_id;
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$description		= $row['description'];
					$desc_uid			= $row['desc_uid'];
					$desc_bitfield		= $row['desc_bitfield'];
					$desc_flags			= $row['desc_flags'];
					$description		= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

					$nav_string['name'][] = $description;
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_details', ['df_id' => $this->df_id]);
					break;
				case 'thumbs':
					$sql = 'SELECT description, desc_uid, desc_bitfield, desc_flags FROM ' . $this->dlext_table_downloads . '
							WHERE id = ' . (int) $this->df_id;
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$description		= $row['description'];
					$desc_uid			= $row['desc_uid'];
					$desc_bitfield		= $row['desc_bitfield'];
					$desc_flags			= $row['desc_flags'];
					$description		= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

					$nav_string['name'][] = $description;
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_details', ['df_id' => $this->df_id]);
					$nav_string['name'][] = $this->language->lang('DL_EDIT_THUMBS');
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_thumbs', ['df_id' => $this->df_id, 'cat_id' => $this->cat_id]);
					break;
				case 'upload':
					$nav_string['name'][] = $this->language->lang('DL_UPLOAD');
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_upload', ['cat_id' => $this->cat_id]);
					break;
				case 'tracker':
					$nav_string['name'][] = $this->language->lang('DL_BUG_TRACKER');
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_tracker_view', ['df_id' => $this->df_id]);
					break;
				case 'stats':
					$nav_string['name'][] = $this->language->lang('DL_STATS');
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_stats');
					break;
				case 'search':
					$nav_string['name'][] = $this->language->lang('SEARCH');
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_search');
					break;
				case 'hacks':
					$nav_string['name'][] = $this->language->lang('DL_HACKS_LIST');
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_hacklist');
					break;
				case 'todo':
					$nav_string['name'][] = $this->language->lang('DL_MOD_TODO');
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_todo');
					break;
				case 'mcp':
					$nav_string['name'][] = $this->language->lang('DL_MODCP_MANAGE');
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_mcp_manage');
					break;
			}

			for ($i = 0; $i < count($nav_string['name']); ++$i)
			{
				$this->template->assign_block_vars('navlinks', [
					'FORUM_NAME'	=> $nav_string['name'][$i],
					'U_VIEW_FORUM'	=> $nav_string['link'][$i],
				]);
			}
		}
	}
}
