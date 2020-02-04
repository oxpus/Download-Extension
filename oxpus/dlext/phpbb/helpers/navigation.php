<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\phpbb\helpers;

use Symfony\Component\DependencyInjection\Container;

class navigation implements navigation_interface
{
	/* @var string phpEx */
	protected $php_ext;

	/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* var extension owned variables */
	protected $nav_mode;
	protected $cat_id;
	protected $df_id;

	protected $dlext_auth;
	protected $dlext_nav;

	/**
	* Constructor
	*
	* @param string									$root_path
	* @param string									$php_ext
	* @param \phpbb\language\language				$language
	* @param \phpbb\template\template				$template
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\db\driver\driver_interfacer		$db
	*/
	public function __construct(
		$root_path,
		$php_ext,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\controller\helper $helper,
		\phpbb\user $user,
		Container $phpbb_container,
		\phpbb\db\driver\driver_interface $db,
		$dlext_auth,
		$dlext_nav
	)
	{
		$this->language			= $language;
		$this->template 		= $template;
		$this->helper 			= $helper;
		$this->user 			= $user;
		$this->phpbb_container 	= $phpbb_container;
		$this->db 				= $db;

		$this->dlext_auth		= $dlext_auth;
		$this->dlext_nav		= $dlext_nav;
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
			$nav_string = array();

			$nav_string['link'][] =  $this->helper->route('oxpus_dlext_index');
			$nav_string['name'][] = $this->language->lang('DL_CAT_TITLE');

			if ($this->cat_id)
			{
				$cat_auth = $this->dlext_auth->user_auth($this->cat_id, 'auth_view');
	
				if (!$cat_auth)
				{
					redirect($this->helper->route('oxpus_dlext_index'));
				}
	
				$tmp_nav = array();
				$dl_nav = $this->dlext_nav->nav($this->cat_id, 'url', $tmp_nav);
	
				if (isset($dl_nav['link']))
				{
					for ($i = sizeof($dl_nav['link']) - 1; $i >= 0; $i--)
					{
						$nav_string['link'][] = $dl_nav['link'][$i];
						$nav_string['name'][] = $dl_nav['name'][$i];
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
					$sql = 'SELECT description, desc_uid, desc_bitfield, desc_flags FROM ' . DOWNLOADS_TABLE . '
							WHERE id = ' . (int) $this->df_id;
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$description		= $row['description'];
					$desc_uid			= $row['desc_uid'];
					$desc_bitfield		= $row['desc_bitfield'];
					$desc_flags			= $row['desc_flags'];
					$description		= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

					$nav_string['name'][] = $this->language->lang('DL_DETAIL') . ': ' . $description;
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_details', array('df_id' => $this->df_id));
				break;
				case 'thumbs':
					$sql = 'SELECT description, desc_uid, desc_bitfield, desc_flags FROM ' . DOWNLOADS_TABLE . '
							WHERE id = ' . (int) $this->df_id;
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$description		= $row['description'];
					$desc_uid			= $row['desc_uid'];
					$desc_bitfield		= $row['desc_bitfield'];
					$desc_flags			= $row['desc_flags'];
					$description		= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

					$nav_string['name'][] = $this->language->lang('DL_DETAIL') . ' ' . $description;
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_details', array('df_id' => $this->df_id));
					$nav_string['name'][] = $this->language->lang('DL_EDIT_THUMBS');
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_thumbs', array('df_id' => $this->df_id, 'cat_id' => $this->cat_id));
				break;
				case 'upload':
					$nav_string['name'][] = $this->language->lang('DL_UPLOAD');
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_upload', array('cat_id' => $this->cat_id));
				break;
				case 'tracker':
					$nav_string['name'][] = $this->language->lang('DL_BUG_TRACKER');
					$nav_string['link'][] = $this->helper->route('oxpus_dlext_tracker', array('df_id' => $this->df_id));
				break;
				case 'stat':
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

					if (isset($action) && $action == 'edit')
					{
						$nav_string['name'][] = $this->language->lang('DL_EDIT_FILE');
						$nav_string['link'][] = $this->helper->route('oxpus_dlext_todo', array('action' => 'edit'));
					}
				break;
			}

			for ($i = 0; $i < sizeof($nav_string['name']); $i++)
			{
				$this->template->assign_block_vars('navlinks', array(
					'FORUM_NAME'	=> $nav_string['name'][$i],
					'U_VIEW_FORUM'	=> $nav_string['link'][$i],
				));
			}

			if (isset($index_cat_name))
			{
				$this->template->assign_var('INDEX_CAT_TITLE', $index_cat_name);
			}
		}
	}
}
