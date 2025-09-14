<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\acp;

/**
 * @package acp
 */
class acp_assistant_controller implements acp_assistant_interface
{
	/* phpbb objects */
	protected $db;
	protected $user;
	protected $phpex;
	protected $root_path;
	protected $log;
	protected $config;
	protected $language;
	protected $request;
	protected $template;
	protected $cache;
	protected $filesystem;

	/* extension owned objects */
	public $u_action;

	protected $dlext_extra;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_physical;
	protected $dlext_constants;

	protected $dlext_table_dl_auth;
	protected $dlext_table_dl_cat_traf;
	protected $dlext_table_dl_versions;
	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param string								$phpex
	 * @param \phpbb\cache\service					$cache
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\user							$user
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \oxpus\dlext\core\extra				$dlext_extra
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\physical			$dlext_physical
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_auth
	 * @param string								$dlext_table_dl_cat_traf
	 * @param string								$dlext_table_dl_versions
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		$root_path,
		$phpex,
		\phpbb\cache\service $cache,
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\user $user,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\extra $dlext_extra,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\physical $dlext_physical,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_auth,
		$dlext_table_dl_cat_traf,
		$dlext_table_dl_versions,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->root_path				= $root_path;
		$this->phpEx					= $phpex;
		$this->cache					= $cache;
		$this->db						= $db;
		$this->log						= $log;
		$this->user						= $user;
		$this->config					= $config;
		$this->language					= $language;
		$this->request					= $request;
		$this->template					= $template;
		$this->filesystem				= $filesystem;

		$this->dlext_table_dl_auth		= $dlext_table_dl_auth;
		$this->dlext_table_dl_cat_traf	= $dlext_table_dl_cat_traf;
		$this->dlext_table_dl_versions	= $dlext_table_dl_versions;
		$this->dlext_table_downloads	= $dlext_table_downloads;
		$this->dlext_table_dl_cat		= $dlext_table_dl_cat;

		$this->dlext_extra				= $dlext_extra;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_physical			= $dlext_physical;
		$this->dlext_constants			= $dlext_constants;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$action				= $this->request->variable('action', 'add');
		$path				= $this->request->variable('path', 'files/');
		$cat_id				= $this->request->variable('cat_id', 0);
		$cat_parent			= $this->request->variable('parent', 0);
		$cat_name			= $this->request->variable('cat_name', '', $this->dlext_constants::DL_TRUE);
		$description		= $this->request->variable('description', '', $this->dlext_constants::DL_TRUE);
		$traffic_off		= $this->request->variable('traffic_off', 0);
		$must_approve		= $this->request->variable('must_approve', $this->dlext_constants::DL_FALSE);
		$statistics			= $this->request->variable('statistics', $this->dlext_constants::DL_FALSE);
		$comments			= $this->request->variable('comments', $this->dlext_constants::DL_FALSE);
		$approve_comments	= $this->request->variable('approve_comments', $this->dlext_constants::DL_FALSE);

		$auth_view			= $this->request->variable('auth_view', $this->dlext_constants::DL_PERM_GENERAL_ALL);
		$auth_dl			= $this->request->variable('auth_dl', $this->dlext_constants::DL_PERM_GENERAL_ALL);
		$auth_up			= $this->request->variable('auth_up', $this->dlext_constants::DL_PERM_GENERAL_GROUPS);
		$auth_mod			= $this->request->variable('auth_mod', $this->dlext_constants::DL_PERM_GENERAL_GROUPS);
		$auth_cread			= $this->request->variable('auth_cread', $this->dlext_constants::DL_PERM_ALL);
		$auth_cpost			= $this->request->variable('auth_cpost', $this->dlext_constants::DL_PERM_USER);

		$index = $this->dlext_main->full_index();

		if (empty($index) && $action != 'save_cat')
		{
			$action = 'add';
		}

		$error = $this->dlext_constants::DL_FALSE;
		$error_msg = [];

		if (!$path)
		{
			$path = 'files/';
		}

		if (!empty($path) && $path[strlen($path) - 1] != '/')
		{
			$path .= '/';
		}

		$s_hidden_fields = [];

		$cat_path		= ($path) ? $path : '/';
		$s_cat_parent	= $this->dlext_extra->dl_dropdown(0, 0, $cat_parent, 'auth_view', $this->dlext_constants::DL_NONE);

		if ($action == 'save_cat')
		{
			if (!check_form_key('dl_adm_setup'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			$check_tree = $this->dlext_physical->get_file_base_tree('', $this->dlext_constants::DL_TRUE);

			if (! empty($check_tree) && !in_array($path, $check_tree))
			{
				$error = $this->dlext_constants::DL_TRUE;
				$error_msg[] = $this->language->lang('DL_PATH_NOT_EXIST', $path, $this->dlext_constants->get_value('files_dir', $this->dlext_constants::DL_TRUE) . '/downloads/', $this->dlext_constants->get_value('files_dir', $this->dlext_constants::DL_TRUE) . '/downloads/' . $path) . '<br>';
			}

			if (empty($cat_name))
			{
				$error = $this->dlext_constants::DL_TRUE;
				$error_msg[] = $this->language->lang('DL_ASSISTANT_NAME_MISSING');
			}

			if ($auth_view == -1)
			{
				$error = $this->dlext_constants::DL_TRUE;
				$error_msg[] = $this->language->lang('DL_ASSISTANT_PERM_MISSING', $this->language->lang('DL_AUTH_VIEW'));
			}

			if ($auth_dl == -1)
			{
				$error = $this->dlext_constants::DL_TRUE;
				$error_msg[] = $this->language->lang('DL_ASSISTANT_PERM_MISSING', $this->language->lang('DL_AUTH_DL'));
			}

			if ($auth_up == -1)
			{
				$error = $this->dlext_constants::DL_TRUE;
				$error_msg[] = $this->language->lang('DL_ASSISTANT_PERM_MISSING', $this->language->lang('DL_AUTH_UP'));
			}

			if ($auth_mod == -1)
			{
				$error = $this->dlext_constants::DL_TRUE;
				$error_msg[] = $this->language->lang('DL_ASSISTANT_PERM_MISSING', $this->language->lang('DL_AUTH_MOD'));
			}

			if ($auth_cread == -1)
			{
				$error = $this->dlext_constants::DL_TRUE;
				$error_msg[] = $this->language->lang('DL_ASSISTANT_PERM_MISSING', $this->language->lang('DL_AUTH_CREAD'));
			}

			if ($auth_cpost == -1)
			{
				$error = $this->dlext_constants::DL_TRUE;
				$error_msg[] = $this->language->lang('DL_ASSISTANT_PERM_MISSING', $this->language->lang('DL_AUTH_CPOST'));
			}

			if (!$error)
			{
				// Create new folder
				$this->filesystem->mkdir($this->dlext_constants->get_value('files_dir') . '/downloads/' . $path);
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FOLDER_CREATE', false, [$path]);

				$allow_bbcode	= ($this->config['allow_bbcode']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
				$allow_smilies	= ($this->config['allow_smilies']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
				$desc_uid		= $desc_bitfield  = '';
				$desc_flags		= 0;

				if ($description)
				{
					generate_text_for_storage($description, $desc_uid, $desc_bitfield, $desc_flags, $allow_bbcode, $this->dlext_constants::DL_TRUE, $allow_smilies);
				}

				$sql_cat_data = [
					'cat_name'				=> $cat_name,
					'comments'				=> $comments,
					'rules'					=> '',
					'dl_topic_text'			=> '',
					'desc_bitfield'			=> $desc_bitfield,
					'desc_flags'			=> $desc_flags,
					'desc_uid'				=> $desc_uid,
					'description'			=> $description,
					'dl_topic_type'			=> POST_NORMAL,
					'must_approve'			=> $must_approve,
					'approve_comments'		=> $approve_comments,
					'show_file_hash'		=> 0,
					'parent'				=> $cat_parent,
					'path'					=> $path,
					'statistics'			=> $statistics,
					'stats_prune'			=> 100000,
					'auth_view'				=> $auth_view,
					'auth_dl'				=> $auth_dl,
					'auth_up'				=> $auth_up,
					'auth_mod'				=> $auth_mod,
					'auth_cread'			=> $auth_cread,
					'auth_cpost'			=> $auth_cpost,
					];

				$sql = 'INSERT INTO ' . $this->dlext_table_dl_cat . ' ' . $this->db->sql_build_array('INSERT', $sql_cat_data);
				$this->db->sql_query($sql);

				$cat_id = $this->db->sql_last_inserted_id();

				$this->config->set('dl_traffic_off', $traffic_off);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_ADD', false, [$cat_name]);

				$message = $this->language->lang('DL_ASSISTANT_ENDS');

				$sql = 'INSERT INTO ' . $this->dlext_table_dl_cat_traf . ' ' . $this->db->sql_build_array('INSERT', [
					'cat_id'			=> $cat_id,
					'cat_traffic_use'	=> 0,
				]);

				$this->db->sql_query($sql);

				// Purge the categories cache
				$this->cache->destroy('_dlext_cats');
				$this->cache->destroy('_dlext_auth');

				$this->u_action = str_replace('mode=assistant', '', $this->u_action);

				$message .= $this->language->lang('DL_ASSISTANT_OPEN_PERM', $this->u_action . '&amp;mode=permissions&amp;cat_select[]=' . $cat_id);

				$this->u_action	.= '&amp;mode=categories&amp;parent=0&amp;type=c';

				$message .= $this->language->lang('DL_ASSISTANT_OPEN_INDEX', $this->u_action);

				$this->u_action	.= '&amp;mode=toolbox';

				$message .= $this->language->lang('DL_ASSISTANT_OPEN_TOOL', $this->u_action);

				trigger_error($message);
			}
		}

		if ($action == 'add' || $error)
		{
			$s_hidden_fields += ['action' => 'save_cat'];

			if (empty($error))
			{
				$cat_path ='';
			}
			else
			{
				$cat_path = $path;
			}

			$t_path_select	= $this->dlext_physical->get_file_base_tree($cat_path);

			if (!empty($t_path_select) && is_array($t_path_select))
			{
				if (count($t_path_select) == 1)
				{
					$this->template->assign_vars([
						'DL_CAT_PATH_NEW'	=> $this->dlext_constants::DL_TRUE,
						'DL_CAT_PATH_ONE'	=> $this->dlext_constants::DL_TRUE,
					]);

					$cat_path = $t_path_select[0]['cat_path'];
				}
				else
				{
					foreach (array_keys($t_path_select) as $key)
					{
						$this->template->assign_block_vars('dl_cat_path_select', [
							'DL_VALUE' 		=> $t_path_select[$key]['cat_path'],
							'DL_SELECTED'	=> $t_path_select[$key]['selected'],
							'DL_NAME'		=> $t_path_select[$key]['entry'],
						]);
					}
				}
			}
			else
			{
				$this->template->assign_var('DL_CAT_PATH_NEW', $this->dlext_constants::DL_TRUE);
				$cat_path = $path;
			}

			$this->language->add_lang('posting');

			add_form_key('dl_adm_setup');

			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=c';

			$this->template->assign_vars([
				'DL_ERROR_MSG'				=> (empty($error)) ? '' : implode('<br>', $error_msg),
				'DL_CATEGORY'				=> (isset($index[$cat_id]['cat_name'])) ? $this->language->lang('DL_PERMISSIONS', $index[$cat_id]['cat_name']) : '',
				'DL_MUST_APPROVE'			=> $must_approve,
				'DL_STATS'					=> $statistics,
				'DL_COMMENTS'				=> $comments,
				'DL_CAT_NAME'				=> $cat_name,
				'DL_DESCRIPTION'			=> $description,
				'DL_PATH'					=> $cat_path,
				'DL_TRAFFIC_CHECKED'		=> $traffic_off,
				'DL_APPROVE_COMMENTS'		=> $approve_comments,

				'S_DL_CATEGORY_ACTION'		=> $this->u_action,
				'S_DL_ERROR'				=> $error,
				'S_DL_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields),

				'S_DL_AUTH_VIEW'			=> $auth_view,
				'S_DL_AUTH_DL'				=> $auth_dl,
				'S_DL_AUTH_UP'				=> $auth_up,
				'S_DL_AUTH_MOD'				=> $auth_mod,
				'S_DL_AUTH_CREAD'			=> $auth_cread,
				'S_DL_AUTH_CPOST'			=> $auth_cpost,

				'U_DL_BACK'					=> $this->u_action,
			]);

			$s_auth_all = [];
			$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_GENERAL_NONE, 		'lang' => $this->language->lang('SELECT_OPTION')];
			$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_GENERAL_ALL, 		'lang' => $this->language->lang('DL_PERM_ALL')];
			$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_GENERAL_REG_USER,	'lang' => $this->language->lang('DL_PERM_REG')];
			$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_GENERAL_GROUPS, 	'lang' => $this->language->lang('DL_PERM_GRG')];

			for ($i = 0; $i < count($s_auth_all); ++$i)
			{
				$this->template->assign_block_vars('dl_auth', [
					'DL_VALUE'	=> $s_auth_all[$i]['value'],
					'DL_NAME'	=> $s_auth_all[$i]['lang'],
				]);
			}

			$s_auth_all = [];
			$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_GENERAL_NONE, 	'lang' => $this->language->lang('SELECT_OPTION')];
			$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_ALL, 			'lang' => $this->language->lang('DL_STAT_PERM_ALL')];
			$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_USER, 			'lang' => $this->language->lang('DL_STAT_PERM_USER')];
			$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_MOD, 			'lang' => $this->language->lang('DL_STAT_PERM_MOD')];
			$s_auth_all[] = ['value' => $this->dlext_constants::DL_PERM_ADMIN, 			'lang' => $this->language->lang('DL_STAT_PERM_ADMIN')];

			for ($i = 0; $i < count($s_auth_all); ++$i)
			{
				$this->template->assign_block_vars('dl_auth_comments', [
					'DL_VALUE'	=> $s_auth_all[$i]['value'],
					'DL_NAME'	=> $s_auth_all[$i]['lang'],
				]);
			}

			if (!empty($s_cat_parent) && is_array($s_cat_parent))
			{
				foreach (array_keys($s_cat_parent) as $key)
				{
					$this->template->assign_block_vars('select_cat_parent', [
						'DL_CAT_ID'			=> $s_cat_parent[$key]['cat_id'],
						'DL_SELECTED'		=> $s_cat_parent[$key]['selected'],
						'DL_SEPERATOR'		=> $s_cat_parent[$key]['seperator'],
						'DL_CAT_NAME'		=> $s_cat_parent[$key]['cat_name'],
					]);
				}
			}
			else
			{
				$this->template->assign_var('DL_CAT_ROOT', $this->dlext_constants::DL_TRUE);
			}
		}
	}
}
