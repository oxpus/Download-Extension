<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\acp;

use Symfony\Component\DependencyInjection\Container;

/**
* @package acp
*/
class acp_overview_controller implements acp_overview_interface
{
	public $u_action;
	public $db;
	public $user;
	public $auth;
	public $phpEx;
	public $phpbb_extension_manager;
	public $phpbb_container;
	public $phpbb_path_helper;

	public $config;
	public $helper;
	public $language;
	public $request;
	public $template;

	public $ext_path;
	public $ext_path_web;
	public $ext_path_ajax;

	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_privacy;
	protected $dlext_physical;

	/*
	 * @param string								$phpEx
	 * @param Container 							$phpbb_container
	 * @param \phpbb\extension\manager				$phpbb_extension_manager
	 * @param \phpbb\path_helper					$phpbb_path_helper
	 * @param \phpbb\db\driver\driver_interfacer	$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\auth\auth						$auth
	 * @param \phpbb\user							$user
	 */
	public function __construct(
		$phpEx,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\auth\auth $auth,
		\phpbb\user $user,
		$dlext_files,
		$dlext_format,
		$dlext_main,
		$dlext_privacy,
		$dlext_physical
	)
	{
		$this->phpEx					= $phpEx;
		$this->phpbb_container			= $phpbb_container;
		$this->phpbb_extension_manager	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db						= $db;
		$this->auth						= $auth;
		$this->user						= $user;

		$this->config					= $this->phpbb_container->get('config');
		$this->helper					= $this->phpbb_container->get('controller.helper');
		$this->language					= $this->phpbb_container->get('language');
		$this->request					= $this->phpbb_container->get('request');
		$this->template					= $this->phpbb_container->get('template');

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_privacy			= $dlext_privacy;
		$this->dlext_physical			= $dlext_physical;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$this->auth->acl($this->user->data);
		if (!$this->auth->acl_get('a_dl_overview'))
		{
			trigger_error('DL_NO_PERMISSION', E_USER_WARNING);
		}

		include_once($this->ext_path . 'phpbb/includes/acm_init.' . $this->phpEx);

		if ($this->request->variable('reset_clicks', ''))
		{
			if (!confirm_box(true))
			{
				confirm_box(false, $this->language->lang('DL_ACP_CONFIRM_RESET_CLICKS'), build_hidden_fields(array(
					'mode'			=> $mode,
					'reset_clicks'	=> true,
				)));
			}
			else
			{
				$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array('klicks' => 0));
				$this->db->sql_query($sql);

				@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_p.' . $this->phpEx);

				trigger_error($this->language->lang('DL_ACP_CONFIRM_RESET_FINISH') . adm_back_link($this->u_action));
			}
		}

		if ($this->request->variable('reset_stats', ''))
		{
			if (confirm_box(true))
			{
				$sql = 'DELETE FROM ' . DL_STATS_TABLE;
				$this->db->sql_query($sql);

				trigger_error($this->language->lang('DL_ACP_CONFIRM_RESET_FINISH') . adm_back_link($this->u_action));
			}
			else
			{
				confirm_box(false, $this->language->lang('DL_ACP_CONFIRM_RESET_STATS'), build_hidden_fields(array(
					'mode'			=> $mode,
					'reset_stats'	=> true,
				)));
			}
		}

		if ($this->request->variable('reset_cache', ''))
		{
			if (confirm_box(true))
			{
				$cache = $this->phpbb_container->get('cache');

				$cache->destroy('config');

				@unlink(DL_EXT_CACHE_PATH . 'data_dl_auth.' . $this->phpEx);
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_auth_groups.' . $this->phpEx);
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_black.' . $this->phpEx);
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_cat_counts.' . $this->phpEx);
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_cats.' . $this->phpEx);
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_p.' . $this->phpEx);
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_presets.' . $this->phpEx);

				trigger_error($this->language->lang('DL_ACP_CONFIRM_RESET_FINISH') . adm_back_link($this->u_action));
			}
			else
			{
				confirm_box(false, $this->language->lang('DL_ACP_CONFIRM_RESET_CACHE'), build_hidden_fields(array(
					'mode'			=> $mode,
					'reset_cache'	=> true,
				)));
			}
		}

		if ($this->request->variable('dl_privacy', ''))
		{
			if (confirm_box(true))
			{
				$this->dlext_privacy->dl_privacy();
				trigger_error($this->language->lang('DL_ACP_CONFIRM_RESET_FINISH') . adm_back_link($this->u_action));
			}
			else
			{
				confirm_box(false, $this->language->lang('DL_ACP_CONFIRM_PRIVACY'), build_hidden_fields(array(
					'mode'			=> $mode,
					'dl_privacy'	=> true,
				)));
			}
		}

		/*
		* create overall mini statistics
		*/
		$total_size = $this->dlext_physical->read_dl_sizes();
		$total_tsize = $this->dlext_physical->read_dl_sizes(DL_EXT_FILEBASE_PATH . 'thumbs/');
		$total_vfsize = $this->dlext_physical->read_dl_sizes(DL_EXT_FILEBASE_PATH. 'version/files/');
		$total_vtsize = $this->dlext_physical->read_dl_sizes(DL_EXT_FILEBASE_PATH. 'version/images/');
		$total_dl = $this->dlext_main->get_sublevel_count();
		$total_extern = sizeof($this->dlext_files->all_files(0, '', 'ASC', "AND extern = 1", 0, true, 'id'));

		$physical_limit = $this->config['dl_physical_quota'];
		$total_size = ($total_size > $physical_limit) ? $physical_limit : $total_size;

		$physical_limit = $this->dlext_format->dl_size($physical_limit, 2);

		$total_size = $this->dlext_format->dl_size($total_size, 2);
		$total_tsize = $this->dlext_format->dl_size($total_tsize, 2);
		$total_vfsize = $this->dlext_format->dl_size($total_vfsize, 2);
		$total_vtsize = $this->dlext_format->dl_size($total_vtsize, 2);

		$remain_traffic = $this->dlext_format->dl_size($this->config['dl_overall_traffic'] - (int) $this->config['dl_remain_traffic'], 2);
		$overall_traffic = $this->dlext_format->dl_size($this->config['dl_overall_traffic']);
		$overall_guest_traffic = $this->dlext_format->dl_size($this->config['dl_overall_guest_traffic']);
		$remain_guest_traffic = $this->dlext_format->dl_size($this->config['dl_overall_guest_traffic'] - (int) $this->config['dl_remain_guest_traffic'], 2);

		$sql = "SELECT
					SUM(CASE WHEN todo <> '' THEN 1 ELSE 0 END) as todos, 
					SUM(broken) as broken, 
					sum(klicks) as mclick, 
					sum(overall_klicks) as oclick 
				FROM " . DOWNLOADS_TABLE . '
				WHERE approve = ' . true;
		$result	= $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		$mclick	= (int) $row['mclick'];
		$oclick	= (int) $row['oclick'];
		$todos	= (int) $row['todos'];
		$broken	= (int) $row['broken'];

		$index = array();
		$index = $this->dlext_main->full_index();

		$cats = 0;
		$subs = 0;

		if (sizeof($index))
		{
			foreach($index as $cat_id => $data)
			{
				if ($data['parent'] == 0)
				{
					++$cats;
				}
				else
				{
					++$subs;
				}
			}
		}

		$sql = 'SELECT count(ver_id) as versions
				FROM ' . DL_VERSIONS_TABLE;
		$result	= $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		$total_versions	= $row['versions'];

		$this->template->assign_vars(array(
			'DL_MOD_VERSION_SIMPLE'	=> $this->config['dl_ext_version'],
			'TOTAL_NUM'				=> $total_dl,
			'TOTAL_SIZE'			=> $total_size,
			'TOTAL_LIMIT'			=> $physical_limit,
			'TOTAL_EXTERN'			=> $total_extern,
			'REMAIN_TRAFFIC'		=> ($remain_traffic <= 0) ? $this->language->lang('DL_ACP_MAIN_STATS_REMAIN_OFF') : $remain_traffic,
			'OVERALL_TRAFFIC'		=> $overall_traffic,
			'REMAIN_GTRAFFIC'		=> ($remain_guest_traffic <= 0) ? $this->language->lang('DL_ACP_MAIN_STATS_REMAIN_OFF') : $remain_guest_traffic,
			'OVERALL_GTRAFFIC'		=> $overall_guest_traffic,
			'MCLICKS'				=> $mclick,
			'OCLICKS'				=> $oclick,
			'CATEGORIES'			=> $cats,
			'SUBCATEGORIES'			=> $subs,
			'TOTAL_TODOS'			=> $todos,
			'TOTAL_BROKEN'			=> $broken,
			'TOTAL_VERSIONS'		=> $total_versions,
			'TOTAL_THUMBS_SIZE'		=> $total_tsize,
			'TOTAL_VERSION_FSIZE'	=> $total_vfsize,
			'TOTAL_VERSION_TSIZE'	=> $total_vtsize,

			'S_DL_TRAFFIC_OFF'		=> $this->config['dl_traffic_off'],

			'U_ACTION'				=> $this->u_action,
		));
	}
}
