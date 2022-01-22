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
class acp_overview_controller implements acp_overview_interface
{
	/* phpbb objects */
	protected $db;
	protected $config;
	protected $language;
	protected $request;
	protected $template;
	protected $cache;

	/* extension owned objects */
	public $u_action;

	protected $dlext_counter;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_privacy;
	protected $dlext_physical;
	protected $dlext_constants;

	protected $dlext_table_dl_stats;
	protected $dlext_table_dl_versions;
	protected $dlext_table_downloads;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\cache\service					$cache
	 * @param \oxpus\dlext\core\counter				$dlext_counter
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\privacy				$dlext_privacy
	 * @param \oxpus\dlext\core\physical			$dlext_physical
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_stats
	 * @param string								$dlext_table_dl_versions
	 * @param string								$dlext_table_downloads
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\cache\service $cache,
		\oxpus\dlext\core\counter $dlext_counter,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\privacy $dlext_privacy,
		\oxpus\dlext\core\physical $dlext_physical,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_stats,
		$dlext_table_dl_versions,
		$dlext_table_downloads
	)
	{
		$this->db						= $db;
		$this->cache					= $cache;
		$this->config					= $config;
		$this->language					= $language;
		$this->request					= $request;
		$this->template					= $template;

		$this->dlext_table_dl_stats		= $dlext_table_dl_stats;
		$this->dlext_table_dl_versions	= $dlext_table_dl_versions;
		$this->dlext_table_downloads	= $dlext_table_downloads;

		$this->dlext_counter			= $dlext_counter;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_privacy			= $dlext_privacy;
		$this->dlext_physical			= $dlext_physical;
		$this->dlext_constants			= $dlext_constants;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		if ($this->request->variable('reset_clicks', ''))
		{
			if (!confirm_box($this->dlext_constants::DL_TRUE))
			{
				confirm_box($this->dlext_constants::DL_FALSE, $this->language->lang('DL_ACP_CONFIRM_RESET_CLICKS'), build_hidden_fields([
					'reset_clicks'	=> $this->dlext_constants::DL_TRUE,
				]), '@oxpus_dlext/dl_confirm_body.html');
			}
			else
			{
				$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', ['klicks' => 0]);
				$this->db->sql_query($sql);

				$this->cache->destroy('_dlext_file_p');

				trigger_error($this->language->lang('DL_ACP_CONFIRM_RESET_FINISH') . adm_back_link($this->u_action));
			}
		}

		if ($this->request->variable('reset_stats', ''))
		{
			if (confirm_box($this->dlext_constants::DL_TRUE))
			{
				$sql = 'DELETE FROM ' . $this->dlext_table_dl_stats;
				$this->db->sql_query($sql);

				trigger_error($this->language->lang('DL_ACP_CONFIRM_RESET_FINISH') . adm_back_link($this->u_action));
			}
			else
			{
				confirm_box($this->dlext_constants::DL_FALSE, $this->language->lang('DL_ACP_CONFIRM_RESET_STATS'), build_hidden_fields([
					'reset_stats'	=> $this->dlext_constants::DL_TRUE,
				]), '@oxpus_dlext/dl_confirm_body.html');
			}
		}

		if ($this->request->variable('reset_cache', ''))
		{
			if (confirm_box($this->dlext_constants::DL_TRUE))
			{
				$this->cache->destroy('config');

				$this->cache->destroy('_dlext_auth');
				$this->cache->destroy('_dlext_auth_groups');
				$this->cache->destroy('_dlext_black');
				$this->cache->destroy('_dlext_cat_counts');
				$this->cache->destroy('_dlext_cats');
				$this->cache->destroy('_dlext_file_p');
				$this->cache->destroy('_dlext_file_presets');

				trigger_error($this->language->lang('DL_ACP_CONFIRM_RESET_FINISH') . adm_back_link($this->u_action));
			}
			else
			{
				confirm_box($this->dlext_constants::DL_FALSE, $this->language->lang('DL_ACP_CONFIRM_RESET_CACHE'), build_hidden_fields([
					'reset_cache'	=> $this->dlext_constants::DL_TRUE,
				]), '@oxpus_dlext/dl_confirm_body.html');
			}
		}

		if ($this->request->variable('dl_privacy', ''))
		{
			if (confirm_box($this->dlext_constants::DL_TRUE))
			{
				$this->dlext_privacy->dl_privacy();
				trigger_error($this->language->lang('DL_ACP_CONFIRM_RESET_FINISH') . adm_back_link($this->u_action));
			}
			else
			{
				confirm_box($this->dlext_constants::DL_FALSE, $this->language->lang('DL_ACP_CONFIRM_PRIVACY'), build_hidden_fields([
					'dl_privacy'	=> $this->dlext_constants::DL_TRUE,
				]), '@oxpus_dlext/dl_confirm_body.html');
			}
		}

		/*
		* create overall mini statistics
		*/
		$total_size = $this->dlext_physical->read_dl_sizes();
		$total_tsize = $this->dlext_physical->read_dl_sizes($this->dlext_constants->get_value('files_dir', $this->dlext_constants::DL_TRUE) . '/thumbs/');
		$total_vfsize = $this->dlext_physical->read_dl_sizes($this->dlext_constants->get_value('files_dir', $this->dlext_constants::DL_TRUE) . '/version/files/');
		$total_vtsize = $this->dlext_physical->read_dl_sizes($this->dlext_constants->get_value('files_dir', $this->dlext_constants::DL_TRUE) . '/version/images/');
		$total_dl = $this->dlext_main->get_sublevel_count();
		$total_extern = $this->dlext_counter->count_external_files();

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
				FROM " . $this->dlext_table_downloads . '
				WHERE approve = 1';
		$result	= $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		$mclick	= (int) $row['mclick'];
		$oclick	= (int) $row['oclick'];
		$todos	= (int) $row['todos'];
		$broken	= (int) $row['broken'];

		$index = $this->dlext_main->full_index();

		$cats = 0;
		$subs = 0;

		if (!empty($index))
		{
			foreach ($index as $data)
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

			$u_dl_assistant = '';
		}
		else
		{
			$u_dl_assistant = $this->u_action . '&amp;mode=assistant';
		}

		$sql = 'SELECT count(ver_id) as versions
				FROM ' . $this->dlext_table_dl_versions;
		$result	= $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		$total_versions	= $row['versions'];

		$this->template->assign_vars([
			'DL_MOD_VERSION_SIMPLE'		=> $this->config['dl_ext_version'],
			'DL_TOTAL_NUM'				=> $total_dl,
			'DL_TOTAL_SIZE'				=> $total_size,
			'DL_TOTAL_LIMIT'			=> $physical_limit,
			'DL_TOTAL_EXTERN'			=> $total_extern,
			'DL_REMAIN_TRAFFIC'			=> ($remain_traffic <= 0) ? $this->language->lang('DL_ACP_MAIN_STATS_REMAIN_OFF') : $remain_traffic,
			'DL_OVERALL_TRAFFIC'		=> $overall_traffic,
			'DL_REMAIN_GTRAFFIC'		=> ($remain_guest_traffic <= 0) ? $this->language->lang('DL_ACP_MAIN_STATS_REMAIN_OFF') : $remain_guest_traffic,
			'DL_OVERALL_GTRAFFIC'		=> $overall_guest_traffic,
			'DL_MCLICKS'				=> $mclick,
			'DL_OCLICKS'				=> $oclick,
			'DL_CATEGORIES'				=> $cats,
			'DL_SUBCATEGORIES'			=> $subs,
			'DL_TOTAL_TODOS'			=> $todos,
			'DL_TOTAL_BROKEN'			=> $broken,
			'DL_TOTAL_VERSIONS'			=> $total_versions,
			'DL_TOTAL_THUMBS_SIZE'		=> $total_tsize,
			'DL_TOTAL_VERSION_FSIZE'	=> $total_vfsize,
			'DL_TOTAL_VERSION_TSIZE'	=> $total_vtsize,

			'S_DL_TRAFFIC_OFF'			=> $this->config['dl_traffic_off'],
			'S_DL_ACP_MAIN_TYPE'		=> substr($this->config['version'], 0, 3),

			'U_DL_ASSISTANT'			=> $u_dl_assistant,
			'U_DL_ACTION'				=> $this->u_action,
		]);
	}
}
