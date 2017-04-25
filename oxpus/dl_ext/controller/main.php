<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dl_ext\controller;

use Symfony\Component\DependencyInjection\Container;

class main
{
	/* @var string phpBB root path */
	protected $root_path;

	/* @var string phpEx */
	protected $php_ext;

	/* @var string table_prefix */
	protected $table_prefix;

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

	/* @var \phpbb\log\log_interface */
	protected $log;

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

	/** @var \phpbb\language\language $language Language object */
	protected $language;

	/**
	* Constructor
	*
	* @param string									$root_path
	* @param string									$php_ext
	* @param string									$table_prefix
	* @param Container 								$phpbb_container
	* @param \phpbb\extension\manager				$phpbb_extension_manager
	* @param \phpbb\path_helper						$phpbb_path_helper
	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param \phpbb\config\config					$config
	* @param \phpbb\log\log_interface 				$log
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\auth\auth						$auth
	* @param \phpbb\request\request_interface 		$request
	* @param \phpbb\template\template				$template
	* @param \phpbb\user							$user
	*/
	public function __construct($root_path, $php_ext, $table_prefix, Container $phpbb_container, \phpbb\extension\manager $phpbb_extension_manager, \phpbb\path_helper $phpbb_path_helper, \phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\log\log_interface $log, \phpbb\controller\helper $helper, \phpbb\auth\auth $auth, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\language\language $language)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->table_prefix 			= $table_prefix;
		$this->phpbb_container 			= $phpbb_container;
		$this->phpbb_extension_manager 	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->phpbb_log 				= $log;
		$this->helper 					= $helper;
		$this->auth						= $auth;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;
	}

	public function handle($view = '')
	{
		global $language;

		if (isset($this->user->data['user_wrong_email']))
		{
			if ($this->user->data['user_wrong_email'] != 0)
			{
				trigger_error($this->language->lang('DL_NO_PERMISSION'));
			}
		}

		include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		include($this->root_path . 'includes/bbcode.' . $this->php_ext);

		// Define the ext path
		$ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dl_ext', true);
		$this->phpbb_path_helper	= $this->phpbb_container->get('path_helper');
		$ext_path_web				= $this->phpbb_path_helper->update_web_root_path($ext_path);
		$ext_path_ajax				= $ext_path_web . 'includes/js/ajax/';

		$table_prefix = $this->table_prefix;
		include_once($ext_path . '/includes/helpers/dl_constants.' . $this->php_ext);

		// Define the basic file storage placement
		if ($this->config['dl_download_dir'] == 2)
		{
			$filebase_prefix = $this->root_path . 'store/oxpus/dl_ext/';
			$filebase_web_prefix = generate_board_url() . '/store/oxpus/dl_ext/';
		}
		else
		{
			$filebase_prefix = $ext_path . 'files/';
			$filebase_web_prefix = $ext_path_web . 'files/';
		}

		define('DL_EXT_CACHE_FOLDER',		$filebase_prefix . 'cache/');
		define('DL_EXT_THUMBS_FOLDER',		$filebase_prefix . 'thumbs/');
		define('DL_EXT_THUMBS_WEB_FOLDER',	$filebase_web_prefix . 'thumbs/');
		define('DL_EXT_FILES_FOLDER',		$filebase_prefix . 'downloads/');
		define('DL_EXT_FILES_WEBFOLDER',	$filebase_web_prefix . 'downloads/');
		define('DL_EXT_VER_FILES_FOLDER',	$filebase_prefix . 'version/files/');
		define('DL_EXT_VER_FILES_WFOLDER',	$filebase_web_prefix . 'version/files/');
		define('DL_EXT_VER_IMAGES_FOLDER',	$filebase_prefix . 'version/images/');
		define('DL_EXT_VER_IMAGES_WFOLDER',	$filebase_web_prefix . 'version/images/');

		$this->template->assign_vars(array(
			'EXT_DL_PATH_WEB'	=> $ext_path_web,
			'EXT_DL_PATH_AJAX'	=> $ext_path_ajax,
			'ICON_DL_HELP'		=> '<i class="icon fa-info-circle fa-fw dl-yellow"></i>',
		));

		/*
		* init and get various values
		*/
		$submit		= $this->request->variable('submit', '');
		$preview	= $this->request->variable('preview', '');
		$cancel		= $this->request->variable('cancel', '');
		$confirm	= $this->request->variable('confirm', '');
		$delete		= $this->request->variable('delete', '');
		$cdelete	= $this->request->variable('cdelete', '');
		$save		= $this->request->variable('save', '');
		$post		= $this->request->variable('post', '');
		$view		= $this->request->variable('view', '');
		$show		= $this->request->variable('show', '');
		$order		= $this->request->variable('order', '');
		$action		= $this->request->variable('action', '');
		$save		= $this->request->variable('save', '');
		$goback		= $this->request->variable('goback', '');
		$edit		= $this->request->variable('edit', '');
		$bt_show	= $this->request->variable('bt_show', '');
		$move		= $this->request->variable('move', '');
		$fmove		= $this->request->variable('fmove', '');
		$lock		= $this->request->variable('lock', '');
		$sort		= $this->request->variable('sort', '');
		$code		= $this->request->variable('code', '');
		$sid		= $this->request->variable('sid', '');

		$df_id		= $this->request->variable('df_id', 0);
		$cat		= $this->request->variable('cat', 0);
		$new_cat	= $this->request->variable('new_cat', 0);
		$cat_id		= $this->request->variable('cat_id', 0);
		$fav_id		= $this->request->variable('fav_id', 0);
		$dl_id		= $this->request->variable('dl_id', 0);
		$start		= $this->request->variable('start', 0);
		$sort_by	= $this->request->variable('sort_by', 0);
		$rate_point	= $this->request->variable('rate_point', 0);
		$del_file	= $this->request->variable('del_file', 0);
		$bt_filter	= $this->request->variable('bt_filter', -1);
		$modcp		= $this->request->variable('modcp', 0);
		$next_id	= $this->request->variable('next_id', 0);

		$file_option	= $this->request->variable('file_ver_opt', 0);
		$file_version	= $this->request->variable('file_version', 0);
		$file_ver_del	= $this->request->variable('file_ver_del', array(0));

		$dl_mod_is_active = true;
		$dl_mod_link_show = true;
		$dl_mod_is_active_for_admins = false;

		$page_start = max($start - 1, 0) * $this->config['dl_links_per_page'];
		$start = $page_start;

		if ($cat < 0)
		{
			$cat = 0;
		}

		if (!$this->config['dl_active'])
		{
			if ($this->config['dl_off_now_time'])
			{
				$dl_mod_is_active = false;
			}
			else
			{
				$curr_time = (date('H', time()) * 60) + date('i', time());
				$off_from = (substr($this->config['dl_off_from'], 0, 2) * 60) + (substr($this->config['dl_off_from'], -2));
				$off_till = (substr($this->config['dl_off_till'], 0, 2) * 60) + (substr($this->config['dl_off_till'], -2));

				if ($curr_time >= $off_from && $curr_time < $off_till)
				{
					$dl_mod_is_active = false;
				}
			}
		}

		if (!$dl_mod_is_active && $this->auth->acl_get('a_') && $this->config['dl_on_admins'])
		{
			$dl_mod_is_active = true;
			$dl_mod_is_active_for_admins = true;
		}

		if (!$dl_mod_is_active && $this->config['dl_off_hide'])
		{
			$dl_mod_link_show = false;
		}

		if ($this->user->data['is_bot'] && in_array($view, array('ajax', 'broken', 'bug_tracker', 'fav', 'help', 'load', 'modcp', 'rss', 'search', 'stat', 'thumbs', 'todo', 'unbroken', 'unfav', 'upload', 'user_config', 'hacks', 'version')))
		{
			$view = '';
		}

		if ($view == 'help')
		{
			include($ext_path . '/includes/helpers/dl_help.' . $this->php_ext);
		}

		if ($view != 'bug_tracker')
		{
			if ($dl_mod_is_active_for_admins)
			{
				$this->template->assign_var('S_DL_MOD_OFFLINE_ADMINS', true);
			}
			else
			{
				if (!$dl_mod_is_active && $dl_mod_link_show)
				{
					trigger_error($this->language->lang('DL_OFF_MESSAGE'));
				}

				if (!$dl_mod_is_active)
				{
					redirect($this->root_path . 'index.' . $this->php_ext);
				}
			}
		}

		/*
		* Ajax functions
		*/
		if ($view == 'ajax' && $df_id)
		{
			include($ext_path . '/includes/helpers/dl_ajax.' . $this->php_ext);
			$this->db->sql_close();
			exit;
		}
		else if ($view == 'ajax')
		{
			$view = '';
		}

		/*
		* Only a little feed as RSS or RSS with feed or what?!? :P
		*/
		if ($view == 'rss')
		{
			include($ext_path . '/includes/modules/dl_rss.' . $this->php_ext);
		}

		/*
		* include and create the main class
		*/
		include($ext_path . '/includes/classes/class_dlmod.' . $this->php_ext);
		$dl_mod = new \oxpus\dl_ext\includes\classes\ dl_mod($this->root_path, $this->php_ext, $ext_path, DL_EXT_CACHE_FOLDER);
		$dl_mod->register();
		\oxpus\dl_ext\includes\classes\ dl_init::init($ext_path);

		/*
		* set the right values for comments
		*/
		if (!$action)
		{
			if ($post)
			{
				$view = 'comment';
				$action = 'post';
			}

			if ($show)
			{
				$view = 'comment';
				$action = 'view';
			}

			if ($save)
			{
				$view = 'comment';
				$action = 'save';
			}

			if ($delete)
			{
				$view = 'comment';
				$action = 'delete';
			}

			if ($edit)
			{
				$view = 'comment';
				$action = 'edit';
			}
		}

		/*
		* wanna have smilies ;-)
		*/
		if ($action == 'smilies')
		{
			include($this->root_path . '/includes/functions_posting.' . $this->php_ext);
			generate_smilies('window', 0);
		}

		/*
		* get the needed index
		*/
		$index = array();

		switch ($view)
		{
			case 'bug_tracker':
			case 'comment':
			case 'detail':
			case 'latest':
			case 'load':
			case 'modcp':
			case 'overall':
			case 'thumbs':
			case 'upload':

				$index = \oxpus\dl_ext\includes\classes\ dl_main::full_index($this->helper);
			break;

			default:

				$index = ($cat) ? \oxpus\dl_ext\includes\classes\ dl_main::index($this->helper, $cat) : \oxpus\dl_ext\includes\classes\ dl_main::index($this->helper);
		}

		/*
		* Build the navigation and check the permissions for the user
		*/
		$nav_string = array();
		if ($view != 'hacks')
		{
			$nav_string['link'][] = array();
			$nav_string['name'][] = $this->language->lang('DL_CAT_TITLE');
		}

		if ($dl_id || $df_id)
		{
			$file_id = ($df_id) ? $df_id : $dl_id;

			$sql = 'SELECT cat, description, desc_uid, desc_bitfield, desc_flags, hack_version FROM ' . DOWNLOADS_TABLE . '
				WHERE id = ' . (int) $file_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$cat_id = (!$cat_id) ? $row['cat'] : $cat_id;
			$description		= $row['description'];
			$desc_uid			= $row['desc_uid'];
			$desc_bitfield		= $row['desc_bitfield'];
			$desc_flags			= $row['desc_flags'];
			$description		= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

			$mini_icon			= \oxpus\dl_ext\includes\classes\ dl_status::mini_status_file($cat_id, $df_id);

			$hack_version		= '&nbsp;'.$row['hack_version'];

			$file_status	= array();
			$file_status	= \oxpus\dl_ext\includes\classes\ dl_status::status($df_id, $this->helper);

			$status			= $file_status['status_detail'];

			$this->db->sql_freeresult($result);
		}
		else
		{
			$description = $this->language->lang('DL_DOWNLOAD');
		}

		if ($cat_id || $cat)
		{
			$check_cat = ($cat_id) ? $cat_id : $cat;

			$check_index = \oxpus\dl_ext\includes\classes\ dl_main::full_index($this->helper);
			if (!isset($check_index[$check_cat]))
			{
				redirect($this->helper->route('dl_ext_controller'));
			}
		}

		if ($cat_id)
		{
			$cat_auth = \oxpus\dl_ext\includes\classes\ dl_auth::user_auth($cat_id, 'auth_view');

			if (!$cat_auth)
			{
				trigger_error($this->language->lang('DL_NO_PERMISSION'));
			}

			$tmp_nav = array();
			\oxpus\dl_ext\includes\classes\ dl_nav::nav($this->helper, $cat_id, 'url', $tmp_nav);

			if (isset($tmp_nav['link']))
			{
				for ($i = sizeof($tmp_nav['link']) - 1; $i >= 0; $i--)
				{
					$nav_string['link'][] = $tmp_nav['link'][$i];
					$nav_string['name'][] = $tmp_nav['name'][$i];
				}
			}
		}

		switch ($view)
		{
			case 'overall':
				$nav_string['link'][] = array('view' => 'overall');
				$nav_string['name'][] = $this->language->lang('DL_OVERVIEW');
			break;
			case 'latest':
				$nav_string['link'][] = array('view' => 'latest');
				$nav_string['name'][] = $this->language->lang('DL_LATEST_DOWNLOADS');
			break;
			case 'version':
			case 'detail':
			case 'broken':
			case 'comment':
				$nav_string['link'][] = array('view' => 'detail', 'df_id' => $df_id);
				$nav_string['name'][] = $this->language->lang('DL_DETAIL') . ': ' . $description;
			break;
			case 'thumbs':
				$nav_string['link'][] = array('view' => 'detail', 'df_id' => $df_id);
				$nav_string['name'][] = $this->language->lang('DL_DETAIL') . ': ' . $description;
				$nav_string['link'][] = array('view' => 'thumbs', 'df_id' => $df_id, 'cat_id' => $cat_id);
				$nav_string['name'][] = $this->language->lang('DL_EDIT_THUMBS');
			break;
			case 'upload':
				$nav_string['link'][] = array('view' => 'upload', 'cat_id' => $cat_id);
				$nav_string['name'][] = $this->language->lang('DL_UPLOAD');
			break;
			case 'modcp':
				if ($action == 'edit')
				{
					$nav_string['link'][] = array('view' => 'detail', 'df_id' => $df_id);
					$nav_string['name'][] = $this->language->lang('DL_DETAIL') . ': ' . $description;
					$nav_string['link'][] = array('view' => 'modcp', 'action' => $action, 'cat_id' => $cat_id, 'df_id' => $df_id);
					$nav_string['name'][] = $this->language->lang('DL_EDIT_FILE');
				}

				if ($action == 'approve')
				{
					$nav_string['link'][] = array('view' => 'modcp', 'action' => 'approve');
					$nav_string['name'][] = $this->language->lang('MCP') . ' <strong>&#8249;</strong> ' . $this->language->lang('DOWNLOADS') . ' ' . $this->language->lang('DL_APPROVE');
				}
				else if ($action == 'capprove')
				{
					$nav_string['link'][] = array('view' => 'modcp', 'action' => 'capprove');
					$nav_string['name'][] = $this->language->lang('MCP') . ' <strong>&#8249;</strong> ' . $this->language->lang('DL_APPROVE_COMMENTS');
				}
				else if ($action != 'edit')
				{
					if (!$cat_id)
					{
						$cat_id = '';
					}
					$nav_string['link'][] = array('view' => 'modcp', 'cat_id' => $cat_id);
					$nav_string['name'][] = $this->language->lang('MCP');
				}
			break;
			case 'bug_tracker':
				$nav_string['link'][] = array('view' => 'bug_tracker', 'df_id' => $df_id);
				$nav_string['name'][] = $this->language->lang('DL_BUG_TRACKER');
			break;
			case 'stat':
				$nav_string['link'][] = array('view' => 'stat');
				$nav_string['name'][] = $this->language->lang('DL_STATS');
			break;
			case 'user_config':
				$nav_string['link'][] = array('view' => 'user_config');
				$nav_string['name'][] = $this->language->lang('DL_CONFIG');
			break;
			case 'search':
				$nav_string['link'][] = array('view' => 'search');
				$nav_string['name'][] = $this->language->lang('SEARCH');
			break;
			case 'hacks':
				$nav_string['link'][] = array('view' => 'hacks');
				$nav_string['name'][] = $this->language->lang('DL_HACKS_LIST');
			break;
			case 'todo':
				$nav_string['link'][] = array('view' => 'todo');
				$nav_string['name'][] = $this->language->lang('DL_MOD_TODO');

				if ($action == 'edit')
				{
					$nav_string['link'][] = array('view' => 'todo', 'action' => 'edit');
					$nav_string['name'][] = $this->language->lang('DL_EDIT_FILE');
				}
			break;
			default:
				if ($cat)
				{
					$tmp_nav = array();
					$cat_auth = \oxpus\dl_ext\includes\classes\ dl_auth::user_auth($cat, 'auth_view');

					if (!$cat_auth)
					{
						redirect($ext_path);
					}

					\oxpus\dl_ext\includes\classes\ dl_nav::nav($this->helper, $cat, 'url', $tmp_nav);

					if (sizeof($tmp_nav['link']))
					{

						for ($i = sizeof($tmp_nav['link']) - 1; $i >= 0; $i--)
						{
							$nav_string['link'][] = $tmp_nav['link'][$i];
							$nav_string['name'][] = $tmp_nav['name'][$i];
							$index_cat_name = $tmp_nav['name'][$i];
						}
					}
				}
		}

		for ($i = 0; $i < sizeof($nav_string['link']); $i++)
		{
			$this->template->assign_block_vars('navlinks', array(
				'U_VIEW_FORUM'	=> $this->helper->route('dl_ext_controller', $nav_string['link'][$i]),
				'FORUM_NAME'	=> $nav_string['name'][$i],
			));
		}

		if (isset($index_cat_name))
		{
			$this->template->assign_var('INDEX_CAT_TITLE', $index_cat_name);
		}

		/*
		* Hacks list
		*/
		if ($view == 'hacks')
		{
			include($ext_path . '/includes/modules/dl_hacks_list.' . $this->php_ext);
		}

		if ($view != 'load' && $view != 'broken')
		{
			$sql_where = '';

			if (!$this->user->data['is_registered'])
			{
				$sql = 'SELECT session_id FROM ' . SESSIONS_TABLE . '
					WHERE session_user_id = ' . ANONYMOUS;
				$result = $this->db->sql_query($sql);

				$guest_sids = array();
				$guest_sids[0] = 0;

				while ($row = $this->db->sql_fetchrow($result))
				{
					$guest_sids[] = $row['session_id'];
				}
				$this->db->sql_freeresult($result);

				$sql_where = ' OR ' . $this->db->sql_in_set('session_id', array_map('intval', $guest_sids), true);
			}

			$sql = 'DELETE FROM ' . DL_HOTLINK_TABLE . '
				WHERE user_id = ' . (int) $this->user->data['user_id'] . "
					$sql_where";
			$this->db->sql_query($sql);
		}

		/*
		* create todo list
		*/
		if ($view == 'todo')
		{
			if (!$this->config['dl_todo_onoff'])
			{
				trigger_error($this->language->lang('DL_NO_PERMISSION'), E_USER_WARNING);
			}

			$todo_access_ids = \oxpus\dl_ext\includes\classes\ dl_main::full_index($this->helper, 0, 0, 0, 2);
			$total_todo_ids = sizeof($todo_access_ids);

			if ($action == 'edit')
			{
				if ($total_todo_ids > 0 && $this->user->data['is_registered'])
				{
					include($ext_path . '/includes/modules/dl_todo.' . $this->php_ext);
				}
				else
				{
					trigger_error($this->language->lang('DL_NO_PERMISSION'), E_USER_WARNING);
				}
			}
			else
			{
				$dl_todo = array();
				$dl_todo = \oxpus\dl_ext\includes\classes\ dl_extra::get_todo();

				page_header($this->language->lang('DL_MOD_TODO'));

				$this->template->set_filenames(array(
					'body' => 'dl_todo_body.html')
				);

				$this->template->assign_vars(array(
					'U_TODO_ADD'	=> $this->helper->route('dl_ext_controller', array('view' => 'todo', 'action' => 'edit')),
					'U_DL_TOP'		=> $ext_path,
				));

				if ($total_todo_ids > 0 && $this->user->data['is_registered'])
				{
					$this->template->assign_var('S_TODO_EDIT', true);
				}

				if (isset($dl_todo['file_name'][0]) && sizeof($dl_todo['file_name']))
				{
					for ($i = 0; $i < sizeof($dl_todo['file_name']); $i++)
					{
						$this->template->assign_block_vars('todolist_row', array(
							'FILENAME'		=> $dl_todo['file_name'][$i],
							'FILE_LINK'		=> $this->helper->route('dl_ext_controller', array('view' => 'detail', 'df_id' => $dl_todo['df_id'][$i])),
							'HACK_VERSION'	=> $dl_todo['hack_version'][$i],
							'TODO'			=> $dl_todo['todo'][$i],

							'U_TODO_EDIT'	=> $this->helper->route('dl_ext_controller', array('view' => 'todo', 'action' => 'edit', 'edit' => true, 'df_id' => $dl_todo['df_id'][$i])),
							'U_TODO_DELETE'	=> $this->helper->route('dl_ext_controller', array('view' => 'todo', 'action' => 'edit', 'delete' => true, 'submit' => true, 'df_id' => $dl_todo['df_id'][$i])),
						));
					}
				}
				else
				{
					$this->template->assign_var('S_NO_TODOLIST', true);
				}
			}
		}

		/*
		* handle reported broken download
		*/
		if ($view == 'broken' && $df_id && $cat_id && ($this->user->data['is_registered'] || (!$this->user->data['is_registered'] && $this->config['dl_report_broken'])))
		{
			// Prepare the captcha permissions for the current user
			$captcha_active = true;
			$user_is_guest = false;
			$user_is_mod = false;
			$user_is_admin = false;
			$user_is_founder = false;

			if (!$this->user->data['is_registered'])
			{
				$user_is_guest = true;
			}
			else
			{
				$cat_auth_tmp = array();
				$cat_auth_tmp = \oxpus\dl_ext\includes\classes\ dl_auth::dl_cat_auth($cat_id);

				if (($cat_auth_tmp['auth_mod'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered'])) && !\oxpus\dl_ext\includes\classes\ dl_auth::user_banned())
				{
					$user_is_mod = true;
				}

				if ($this->auth->acl_get('a_'))
				{
					$user_is_admin = true;
				}

				if ($this->user->data['user_type'] == USER_FOUNDER)
				{
					$user_is_founder = true;
				}
			}

			switch ($this->config['dl_report_broken_vc'])
			{
				case 0:
					$captcha_active = false;
				break;

				case 1:
					if (!$user_is_guest)
					{
						$captcha_active = false;
					}
				break;

				case 2:
					if ($user_is_mod || $user_is_admin || $user_is_founder)
					{
						$captcha_active = false;
					}
				break;

				case 3:
					if ($user_is_admin || $user_is_founder)
					{
						$captcha_active = false;
					}
				break;

				case 4:
					if ($user_is_founder)
					{
						$captcha_active = false;
					}
				break;
			}

			if ($captcha_active)
			{
				$code_match = false;

				$captcha = $this->phpbb_container->get('captcha.factory')->get_instance($this->config['captcha_plugin']);
				$captcha->init(CONFIRM_POST);

				$s_hidden_fields = $error = array();

				if ($confirm == 'code')
				{
					if (!check_form_key('dl_report'))
					{
						trigger_error('FORM_INVALID');
					}

			        $vc_response = $captcha->validate();

			        if ($vc_response)
			        {
			            $error[] = $vc_response;
			        }

			        if (!sizeof($error))
			        {
			            $captcha->reset();
			            $code_match = true;
			        }
			        else if ($captcha->is_solved())
			        {
			            $s_hidden_fields = array_merge($s_hidden_fields, $captcha->get_hidden_fields());
			            $code_match = false;
			        }
				}
				else if (!$captcha->is_solved())
				{
					add_form_key('dl_report');

					page_header();

					$this->template->set_filenames(array(
						'body' => 'dl_report_code_body.html')
					);

					$s_hidden_fields = array_merge($s_hidden_fields, array(
						'cat_id' => $cat_id,
						'df_id' => $df_id,
						'view' => 'broken',
						'confirm' => 'code'
					));

					$this->template->assign_vars(array(
						'DESCRIPTION'		=> $description,
						'MINI_IMG'			=> $mini_icon,
						'HACK_VERSION'		=> $hack_version,
						'STATUS'			=> $status,
						'MESSAGE_TITLE'		=> $this->language->lang('DL_BROKEN'),
						'MESSAGE_TEXT'		=> $this->language->lang('DL_REPORT_CONFIRM_CODE'),

						'S_CONFIRM_ACTION'	=> $this->helper->route('dl_ext_controller'),
						'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
			            'S_CONFIRM_CODE'	=> true,
			            'CAPTCHA_TEMPLATE'	=> $captcha->get_template(),
					));

					include($ext_path . '/includes/modules/dl_footer.' . $this->php_ext);
					page_footer();
				}
			}
			else if (!$submit)
			{
				page_header();

				$this->template->set_filenames(array(
					'body' => 'dl_report_code_body.html')
				);

				$s_hidden_fields = array(
					'cat_id' => $cat_id,
					'df_id' => $df_id,
					'view' => 'broken',
				);

				$this->template->assign_vars(array(
					'DESCRIPTION'		=> $description,
					'MINI_IMG'			=> $mini_icon,
					'HACK_VERSION'		=> $hack_version,
					'STATUS'			=> $status,
					'MESSAGE_TITLE'		=> $this->language->lang('DL_BROKEN'),
					'MESSAGE_TEXT'		=> $this->language->lang('DL_REPORT_CONFIRM_CODE'),

					'S_CONFIRM_ACTION'	=> $this->helper->route('dl_ext_controller'),
					'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
				));

				include($ext_path . '/includes/modules/dl_footer.' . $this->php_ext);
				page_footer();
			}

			if ($captcha_active && !$code_match)
			{
				trigger_error('DL_REPORT_BROKEN_VC_MISMATCH');
			}
			else
			{
				$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
					'broken' => true)) . ' WHERE id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				$processing_user = \oxpus\dl_ext\includes\classes\ dl_auth::dl_auth_users($cat_id, 'auth_mod');

				$report_notify_text = $this->request->variable('report_notify_text', '', true);
				$report_notify_text = ($report_notify_text) ? $this->language->lang('DL_REPORT_NOTIFY_TEXT', $report_notify_text) : '';

				$mail_data = array(
					'email_template'		=> 'downloads_report_broken',
					'processing_user'		=> $processing_user,
					'report_notify_text'	=> $report_notify_text,
					'cat_id'				=> $cat_id,
					'df_id'					=> $df_id,
				);

				\oxpus\dl_ext\includes\classes\ dl_email::send_report($mail_data, $this->helper, $ext_path);
			}

			redirect($this->helper->route('dl_ext_controller', array('view' => 'detail', 'df_id' => $df_id, 'cat_id' => $cat_id)));
		}

		/*
		* reset reported broken download if allowed
		*/
		if ($view == 'unbroken' && $df_id && $cat_id)
		{
			$cat_auth = array();
			$cat_auth = \oxpus\dl_ext\includes\classes\ dl_auth::dl_cat_auth($cat_id);

			if (isset($index[$cat_id]['auth_mod']) && $index[$cat_id]['auth_mod'] || isset($cat_auth['auth_mod']) && $cat_auth['auth_mod'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered']))
			{
				$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
					'broken' => 0)) . ' WHERE id = ' . (int) $df_id;
				$this->db->sql_query($sql);
			}

			redirect($this->helper->route('dl_ext_controller', array('view' => 'detail', 'df_id' => $df_id, 'cat_id' => $cat_id)));
		}

		/*
		* set favorite for the choosen download
		*/
		if ($view == 'fav' && $df_id && $cat_id && $this->user->data['is_registered'])
		{
			$sql = 'SELECT COUNT(fav_dl_id) AS total FROM ' . DL_FAVORITES_TABLE . '
				WHERE fav_dl_id = ' . (int) $df_id . '
					AND fav_user_id = ' . (int) $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);
			$fav_check = $this->db->sql_fetchfield('total');
			$this->db->sql_freeresult($result);

			if (!$fav_check)
			{
				$sql = 'INSERT INTO ' . DL_FAVORITES_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
					'fav_dl_id'		=> $df_id,
					'fav_dl_cat'	=> $cat_id,
					'fav_user_id'	=> $this->user->data['user_id']));
				$this->db->sql_query($sql);
			}

			redirect($this->helper->route('dl_ext_controller', array('view' => 'detail', 'df_id' => $df_id, 'cat_id' => $cat_id)));
		}

		/*
		* drop favorite for the choosen download
		*/
		if ($view == 'unfav' && $fav_id && $df_id && $cat_id && $this->user->data['is_registered'])
		{
			$sql = 'DELETE FROM ' . DL_FAVORITES_TABLE . '
				WHERE fav_id = ' . (int) $fav_id . '
					AND fav_dl_id = ' . (int) $df_id . '
					AND fav_user_id = ' . (int) $this->user->data['user_id'];
			$this->db->sql_query($sql);

			redirect($this->helper->route('dl_ext_controller', array('view' => 'detail', 'df_id' => $df_id, 'cat_id' => $cat_id)));
		}

		/*
		* open the bug tracker, if choosen and possible
		*/
		if ($view == 'bug_tracker' && $this->user->data['is_registered'])
		{
			$bug_tracker = \oxpus\dl_ext\includes\classes\ dl_auth::bug_tracker();
			if ($bug_tracker)
			{
				$inc_module = true;
				page_header($this->language->lang('DL_BUG_TRACKER'));
				include($ext_path . '/includes/modules/dl_bug_tracker.' . $this->php_ext);
			}
			else
			{
				$view = '';
			}
		}

		/*
		* No real hard work until here? Must at least run one of the default modules?
		*/
		$inc_module = false;

		if ($view == 'stat')
		{
			/*
			* getting some stats
			*/
			$inc_module = true;
			page_header($this->language->lang('DL_STATS'));
			include($ext_path . '/includes/modules/dl_stats.' . $this->php_ext);
		}
		else if ($view == 'user_config')
		{
			/*
			* display the user config for the downloads
			*/
			$inc_module = true;
			page_header($this->language->lang('DL_CONFIG'));
			include($ext_path . '/includes/modules/dl_user_config.' . $this->php_ext);
		}
		else if ($view == 'detail' || $view == 'comment')
		{
			include($ext_path . '/includes/modules/dl_details.' . $this->php_ext);
		}
		else if ($view == 'version')
		{
			include($ext_path . '/includes/modules/dl_version.' . $this->php_ext);
		}
		else if ($view == 'thumbs')
		{
			if (isset($index[$cat_id]['allow_thumbs']) && $index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
			{
				include($ext_path . '/includes/modules/dl_thumbs.' . $this->php_ext);
			}
			else
			{
				trigger_error('DL_NO_PERMISSION');
			}
		}
		else if ($view == 'search')
		{
			/*
			* open the search for downloads
			*/
			$inc_module = true;
			page_header($this->language->lang('SEARCH').' '.$this->language->lang('DOWNLOADS'));
			include($ext_path . '/includes/modules/dl_search.' . $this->php_ext);
		}
		else if ($view == 'load')
		{
			include($ext_path . '/includes/modules/dl_load.' . $this->php_ext);
		}
		else if ($view == 'upload')
		{
			$inc_module = true;
			page_header($this->language->lang('DL_UPLOAD'));
			include($ext_path . '/includes/modules/dl_upload.' . $this->php_ext);
		}
		else if ($view == 'modcp')
		{
			if (isset($index[$cat_id]['total']) && $index[$cat_id]['total'])
			{
				include($ext_path . '/includes/modules/dl_modcp.' . $this->php_ext);
			}
			else
			{
				redirect($this->helper->route('dl_ext_controller', array('cat' => $cat_id)));
			}
		}

		/*
		* sorting downloads
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
			case 1:
				$sql_sort_by = 'description';
				break;
			case 2:
				$sql_sort_by = 'file_name';
				break;
			case 3:
				$sql_sort_by = 'klicks';
				break;
			case 4:
				$sql_sort_by = 'free';
				break;
			case 5:
				$sql_sort_by = 'extern';
				break;
			case 6:
				$sql_sort_by = 'file_size';
				break;
			case 7:
				$sql_sort_by = 'change_time';
				break;
			case 8:
				$sql_sort_by = 'rating';
				break;
			default:
				$sql_sort_by = 'sort';
		}

		$sql_order = ($order == 'DESC') ? 'DESC' : 'ASC';

		if (!$this->config['dl_sort_preform'] && $this->user->data['user_dl_sort_opt'])
		{
			$this->template->assign_var('S_SORT_OPTIONS', true);

			$selected_0 = ($sort_by == 0) ? ' selected="selected"' : '';
			$selected_1 = ($sort_by == 1) ? ' selected="selected"' : '';
			$selected_2 = ($sort_by == 2) ? ' selected="selected"' : '';
			$selected_3 = ($sort_by == 3) ? ' selected="selected"' : '';
			$selected_4 = ($sort_by == 4) ? ' selected="selected"' : '';
			$selected_5 = ($sort_by == 5) ? ' selected="selected"' : '';
			$selected_6 = ($sort_by == 6) ? ' selected="selected"' : '';
			$selected_7 = ($sort_by == 7) ? ' selected="selected"' : '';
			$selected_8 = ($sort_by == 8) ? ' selected="selected"' : '';

			$selected_sort_0 = ($order == 'ASC') ? ' selected="selected"' : '';
			$selected_sort_1 = ($order == 'DESC') ? ' selected="selected"' : '';

			$this->template->assign_vars(array(
				'SELECTED_0'		=> $selected_0,
				'SELECTED_1'		=> $selected_1,
				'SELECTED_2'		=> $selected_2,
				'SELECTED_3'		=> $selected_3,
				'SELECTED_4'		=> $selected_4,
				'SELECTED_5'		=> $selected_5,
				'SELECTED_6'		=> $selected_6,
				'SELECTED_7'		=> $selected_7,
				'SELECTED_8'		=> $selected_8,

				'SELECTED_SORT_0'	=> $selected_sort_0,
				'SELECTED_SORT_1'	=> $selected_sort_1,
			));
		}
		else
		{
			$s_sort_by = '';
			$s_order = '';
		}

		if ($view == 'overall' && sizeof($index))
		{
			if ($this->config['dl_overview_link_onoff'])
			{
				include($ext_path . '/includes/modules/dl_overview.' . $this->php_ext);
			}
			else
			{
				redirect($this->helper->route('dl_ext_controller', array('view' => '')));
			}
		}

		if ($view == 'latest' && sizeof($index))
		{
			include($ext_path . '/includes/modules/dl_latest.' . $this->php_ext);
		}

		/*
		* default user entry. redirect to index or category
		*/
		if (empty($view) && !$inc_module)
		{
			include($ext_path . '/includes/modules/dl_cat.' . $this->php_ext);
		}

		$view_check = array('broken', 'bug_tracker', 'comment', 'detail', 'fav', 'latest', 'load', 'modcp', 'overall', 'rss', 'search', 'stat', 'thumbs', 'todo', 'unbroken', 'unfav', 'upload', 'user_config', 'view', 'version');

		if (in_array($view, $view_check))
		{
			\oxpus\dl_ext\includes\classes\ dl_extra::dl_ext_version();
		}
		else
		{
			trigger_error('DL_NO_PERMISSION');
		}

		$this->template->assign_vars(array(
			'U_HELP_POPUP' => $this->helper->route('dl_ext_controller', array('view' => 'help')),
		));

		/*
		* include the mod footer
		*/
		include($ext_path . '/includes/modules/dl_footer.' . $this->php_ext);

		/*
		* include the phpBB footer
		*/
		page_footer();
	}
}
