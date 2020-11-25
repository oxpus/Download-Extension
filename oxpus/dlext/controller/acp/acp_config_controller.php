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
class acp_config_controller implements acp_config_interface
{
	public $u_action;
	public $db;
	public $user;
	public $auth;
	public $phpEx;
	public $root_path;
	public $phpbb_extension_manager;
	public $phpbb_container;
	public $phpbb_path_helper;
	public $phpbb_log;

	public $config;
	public $config_text;
	public $helper;
	public $language;
	public $request;
	public $template;

	public $ext_path;
	public $ext_path_web;
	public $ext_path_ajax;

	protected $dlext_extra;
	protected $dlext_format;
	protected $dlext_physical;

	/*
	 * @param string								$root_path
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
		$root_path,
		$phpEx,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\auth\auth $auth,
		\phpbb\user $user,
		$dlext_extra,
		$dlext_format,
		$dlext_physical
	)
	{
		$this->root_path				= $root_path;
		$this->phpEx					= $phpEx;
		$this->phpbb_container			= $phpbb_container;
		$this->phpbb_extension_manager	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db						= $db;
		$this->phpbb_log				= $log;
		$this->auth						= $auth;
		$this->user						= $user;

		$this->config					= $this->phpbb_container->get('config');
		$this->config_text				= $this->phpbb_container->get('config_text');
		$this->helper					= $this->phpbb_container->get('controller.helper');
		$this->language					= $this->phpbb_container->get('language');
		$this->request					= $this->phpbb_container->get('request');
		$this->template					= $this->phpbb_container->get('template');

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_extra				= $dlext_extra;
		$this->dlext_format				= $dlext_format;
		$this->dlext_physical			= $dlext_physical;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$this->auth->acl($this->user->data);
		if (!$this->auth->acl_get('a_dl_config'))
		{
			trigger_error('DL_NO_PERMISSION', E_USER_WARNING);
		}

		include_once($this->ext_path . 'phpbb/includes/acm_init.' . $this->phpEx);

		if ($submit && !check_form_key('dl_adm_config'))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
		}
		
		if (!$submit)
		{
			add_form_key('dl_adm_config');
		}
		
		$this->language->add_lang('posting');
		
		$s_hidden_fields = [];

		$dl_file_edit_hint			= $this->config_text->get('dl_file_edit_hint');
		$dl_file_edit_hint_uid		= $this->config['dl_file_edit_hint_bbcode'];
		$dl_file_edit_hint_bitfield	= $this->config['dl_file_edit_hint_bitfield'];
		$dl_file_edit_hint_flags	= $this->config['dl_file_edit_hint_flags'];
		$formated_hint_text 		= generate_text_for_display($dl_file_edit_hint, $dl_file_edit_hint_uid, $dl_file_edit_hint_bitfield, $dl_file_edit_hint_flags);

		switch ($view)
		{
			default:
			case 'general':
				$display_vars = [
					'title'	=> 'DL_ACP_CONF_GENERAL',
					'vars'	=> [
						'legend1'				=> '',
		
						'dl_active'			=> ['lang' => 'DL_ACTIVE',			'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_ACTIVE'],
						'dl_traffic_off'	=> ['lang' => 'DL_TRAFFIC_OFF',		'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_TRAFFIC_OFF'],
						'dl_stop_uploads'	=> ['lang' => 'DL_STOP_UPLOADS',	'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_STOP_UPLOADS'],
						'dl_use_hacklist'	=> ['lang' => 'DL_USE_HACKLIST',	'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_USE_HACKLIST'],
						'dl_todo_onoff'		=> ['lang' => 'DL_USE_TODOLIST',	'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_USE_TODOLIST'],
		
						'legend2'				=> '',
		
						'dl_off_now_time'	=> ['lang' => 'DL_OFF_NOW_TIME',		'validate' => 'bool',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_OFF_NOW_TIME', 		'function' => [$this, 'mod_disable'],	'params' => ['{CONFIG_VALUE}']],
						'dl_off_from'		=> ['lang' => 'DL_OFF_PERIOD',			'validate' => 'string',	'type' => 'text:5:5',		'explain' => false,		'help_key' => 'DL_OFF_PERIOD'],
						'dl_off_till'		=> ['lang' => 'DL_OFF_PERIOD_TILL',		'validate' => 'string',	'type' => 'text:5:5',		'explain' => false,		'help_key' => 'DL_OFF_PERIOD_TILL'],
						'dl_on_admins'		=> ['lang' => 'DL_ON_ADMINS',			'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_ON_ADMINS'],
						'dl_off_hide'		=> ['lang' => 'DL_OFF_HIDE',			'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_OFF_HIDE'],
		
						'legend3'				=> '',
		
						'dl_set_add'		=> ['lang' => 'DL_SET_ADD',				'validate' => 'int',	'type' => 'select',		'explain' => false,		'help_key' => 'DL_SET_ADD', 				'function' => [$this, 'select_topic_user'],	'params' => ['{CONFIG_VALUE}']],
						'dl_set_user'		=> ['lang' => 'DL_TOPIC_USER_OTHER',	'validate' => 'string',	'type' => 'custom',		'explain' => false,		'help_key' => 'DL_SET_ADD',					'function' => [$this, 'select_dl_user'], 	'params' => ['{CONFIG_VALUE}', 'dl_set_user']],
					]
				];
			break;
			case 'view':
				$display_vars = [
					'title'	=> 'DL_ACP_CONF_VIEW',
					'vars'	=> [
						'legend1'				=> '',
		
						'dl_icon_free_for_reg'		=> ['lang' => 'DL_ICON_FREE_FOR_REG',		'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_ICON_FREE_FOR_REG'],
						'dl_new_time'				=> ['lang' => 'DL_NEW_TIME',				'validate' => 'string',	'type' => 'text:3:4',		'explain' => false,		'help_key' => 'DL_NEW_TIME'],
						'dl_edit_time'				=> ['lang' => 'DL_EDIT_TIME',				'validate' => 'string',	'type' => 'text:3:4',		'explain' => false,		'help_key' => 'DL_EDIT_TIME'],
						'dl_show_footer_legend'		=> ['lang' => 'DL_SHOW_FOOTER_LEGEND',		'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_SHOW_FOOTER_LEGEND'],
						'dl_show_footer_stat'		=> ['lang' => 'DL_SHOW_FOOTER_STAT',		'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_SHOW_FOOTER_STAT'],
						'dl_mini_stats_ext'			=> ['lang' => 'DL_SHOW_FOOTER_EXT_STATS',	'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_SHOW_FOOTER_EXT_STATS',	'function' => [$this, 'select_dl_ext_stats'],	'params' => ['{CONFIG_VALUE}']],
						'dl_overview_link_onoff'	=> ['lang' => 'DL_OVERVIEW_LINK',			'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_OVERVIEW_LINK'],
						'dl_todo_link_onoff'		=> ['lang' => 'DL_TODO_LINK',				'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_TODO_LINK'],
						'dl_enable_jumpbox'			=> ['lang' => 'DL_ENABLE_JUMPBOX',			'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_ENABLE_JUMPBOX'],
						'dl_cat_edit'				=> ['lang' => 'DL_CAT_EDIT_LINK',			'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_CAT_EDIT_LINK',		'function' => [$this, 'select_dl_cat_edit'],	'params' => ['{CONFIG_VALUE}']],
		
						'legend2'				=> '',
		
						'dl_links_per_page'			=> ['lang' => 'DL_LINKS_PER_PAGE',			'validate' => 'string',	'type' => 'text:3:4',		'explain' => false,		'help_key' => 'DL_LINKS_PER_PAGE'],
						'dl_shorten_extern_links'	=> ['lang' => 'DL_SHORTEN_EXTERN_LINKS',	'validate' => 'string',	'type' => 'text:3:4',		'explain' => false,		'help_key' => 'DL_SHORTEN_EXTERN_LINKS'],
						'dl_index_desc_hide'		=> ['lang' => 'DL_INDEX_DESC_HIDE',			'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_INDEX_DESC_HIDE'],
						'dl_desc_index'				=> ['lang' => 'DL_ENABLE_INDEX_DESC',		'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_ENABLE_INDEX_DESC'],
						'dl_limit_desc_on_index'	=> ['lang' => 'DL_LIMIT_DESC_ON_INDEX',		'validate' => 'string',	'type' => 'text:5:10',		'explain' => false,		'help_key' => 'DL_LIMIT_DESC_ON_INDEX'],
						'dl_desc_search'			=> ['lang' => 'DL_ENABLE_SEARCH_DESC',		'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_ENABLE_SEARCH_DESC'],
						'dl_limit_desc_on_search'	=> ['lang' => 'DL_LIMIT_DESC_ON_SEARCH',	'validate' => 'string',	'type' => 'text:5:10',		'explain' => false,		'help_key' => 'DL_LIMIT_DESC_ON_SEARCH'],
		
						'legend3'				=> '',
		
						'dl_show_real_filetime'		=> ['lang' => 'DL_SHOW_REAL_FILETIME',		'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_SHOW_REAL_FILETIME'],
						'dl_file_hash_algo'			=> ['lang' => 'DL_FILE_HASH_ALGO',			'validate' => 'string',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_FILE_HASH_ALGO',		'function' => [$this, 'select_dl_hash_algo'],	'params' => ['{CONFIG_VALUE}']],
						'dl_ext_new_window'			=> ['lang' => 'DL_EXT_NEW_WINDOW',			'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_EXT_NEW_WINDOW'],
						'dl_report_broken_message'	=> ['lang' => 'DL_REPORT_BROKEN_MESSAGE',	'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_REPORT_BROKEN_MESSAGE'],

						'legend4'				=> '',
		
						'dl_nav_link_main'			=> ['lang' => 'DL_NAV_LINK_MAIN',			'validate' => 'string',	'type' => 'select',			'explain' => false,		'help_key' => false,		'function' => [$this, 'select_nav_link_pos'],	'params' => ['{CONFIG_VALUE}']],
						'dl_nav_link_hacks'			=> ['lang' => 'DL_NAV_LINK_HACKS',			'validate' => 'string',	'type' => 'select',			'explain' => false,		'help_key' => false,		'function' => [$this, 'select_nav_link_pos'],	'params' => ['{CONFIG_VALUE}']],
						'dl_nav_link_tracker'		=> ['lang' => 'DL_NAV_LINK_TRACKER',		'validate' => 'string',	'type' => 'select',			'explain' => false,		'help_key' => false,		'function' => [$this, 'select_nav_link_pos'],	'params' => ['{CONFIG_VALUE}']],
					]
				];
		
				$fulltext_dl_search_enabled = false;
				global $dbms;
		
				if (strpos(strtolower($dbms), 'mysql') !== false)
				{
					$sql = 'SHOW INDEX FROM ' . DOWNLOADS_TABLE;
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						if ($row['Key_name'] == 'desc_search')
						{
							$fulltext_dl_search_enabled = true;
						}
					}
					$this->db->sql_freeresult($result);
				}
		
				if ($fulltext_dl_search_enabled)
				{
					$display_vars['vars'] += [
						'legend5'				=> '',
		
						'dl_similar_dl'		=> ['lang' => 'DL_SIMILAR_DL_OPTION',		'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_SIMILAR_DL'],
						'dl_similar_limit'	=> ['lang' => 'DL_SIMILAR_DL_LIMIT',		'validate' => 'int',	'type' => 'text:3:5',		'explain' => false,		'help_key' => 'DL_SIMILAR_DL_LIMIT'],
					];
				}
				else
				{
					$s_hidden_fields = ['dl_similar_limit' => 0, 'dl_similar_dl' => 0];
				}
		
			break;
			case 'protect':
				$display_vars = [
					'title'	=> 'DL_ACP_CONF_PROTECT',
					'vars'	=> [
						'legend1'				=> '',
		
						'dl_global_guests'		=> ['lang' => 'DL_GLOBAL_GUESTS',		'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_GLOBAL_GUESTS'],
		
						'legend2'				=> '',
		
						'dl_use_ext_blacklist'	=> ['lang' => 'DL_USE_EXT_BLACKLIST',	'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_USE_EXT_BLACKLIST'],
		
						'legend3'				=> 'DL_ANTISPAM',
		
						'dl_antispam_posts'		=> ['lang' => 'DL_ANTISPAM_POSTS',		'validate' => 'int',	'type' => 'text:5:10',		'explain' => false,		'help_key' => 'DL_ANTISPAM'],
						'dl_antispam_hours'		=> ['lang' => 'DL_ANTISPAM_HOURS',		'validate' => 'int',	'type' => 'text:5:10',		'explain' => false,		'help_key' => 'DL_ANTISPAM'],
		
						'legend4'				=> '',
		
						'dl_download_vc'		=> ['lang' => 'DL_VISUAL_CONFIRMATION',	'validate' => 'int',	'type' => 'select',		'explain' => true,		'help_key' => 'DL_VISUAL_CONFIRMATION',		'function' => [$this, 'select_dl_vc'],		'params' => ['{CONFIG_VALUE}']],
						'dl_report_broken_vc'	=> ['lang' => 'DL_REPORT_BROKEN_VC',	'validate' => 'int',	'type' => 'select',		'explain' => false,		'help_key' => 'DL_REPORT_BROKEN_VC',		'function' => [$this, 'select_report_vc'],	'params' => ['{CONFIG_VALUE}']],
		
						'legend5'				=> '',
		
						'dl_stats_perm'			=> ['lang' => 'DL_STAT_PERM',	'validate' => 'int',	'type' => 'select',		'explain' => false,		'help_key' => 'DL_STAT_PERM',	'function' => [$this, 'select_stat_perm'],	'params' => ['{CONFIG_VALUE}']],
		
						'legend6'				=> '',
		
						'dl_prevent_hotlink'	=> ['lang' => 'DL_PREVENT_HOTLINK',	'validate' => 'int',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_PREVENT_HOTLINK'],
						'dl_hotlink_action'		=> ['lang' => 'DL_HOTLINK_ACTION',	'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_HOTLINK_ACTION',		'function' => [$this, 'select_hotlink_action'],	'params' => ['{CONFIG_VALUE}']],
					]
				];
			break;
			case 'limit':
				$display_vars = [
					'title'	=> 'DL_ACP_CONF_LIMIT',
					'vars'	=> [
						'legend1'				=> '',

						'dl_physical_quota'		=> ['lang' => 'DL_PHYSICAL_QUOTA',		'validate' => 'int',	'type' => 'custom',		'explain' => false,		'help_key' => 'DL_PHYSICAL_QUOTA',	'function' => [$this, 'select_size'],	'params' => ['{CONFIG_VALUE}', 'dl_physical_quota', '10', '20', 'dl_x_quota', 'gb', true]],

						'legend2'				=> '',
		
						'dl_report_broken'			=> ['lang' => 'DL_REPORT_BROKEN',		'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_REPORT_BROKEN',			'function' => [$this, 'select_report_action'],	'params' => ['{CONFIG_VALUE}']],
						'dl_report_broken_lock'		=> ['lang' => 'DL_REPORT_BROKEN_LOCK',	'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_REPORT_BROKEN_LOCK'],
						'dl_sort_preform'			=> ['lang' => 'DL_SORT_PREFORM',		'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_SORT_PREFORM',			'function' => [$this, 'select_sort'],			'params' => ['{CONFIG_VALUE}']],
						'dl_posts'					=> ['lang' => 'DL_POSTS',				'validate' => 'int',	'type' => 'text:3:4',		'explain' => false,		'help_key' => 'DL_POSTS'],
		
						'legend3'				=> '',
		
						'dl_edit_own_downloads'		=> ['lang' => 'DL_EDIT_OWN_DOWNLOADS',	'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_EDIT_OWN_DOWNLOADS'],
		
						'legend4'				=> '',
		
						'dl_guest_stats_show'		=> ['lang' => 'DL_GUEST_STATS_SHOW',	'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_GUEST_STATS_SHOW'],
		
						'legend5'				=> '',
		
						'dl_latest_type'		=> ['lang' => 'DL_LATEST_DOWNLOADS',		'validate' => 'int',	'type' => 'select',		'explain' => false,		'help_key' => 'DL_LATEST_DOWNLOADS',		'function' => [$this, 'select_latest_type'],		'params' => ['{CONFIG_VALUE}']],

						'legend6'				=> '',
		
						'dl_thumb_fsize'				=> ['lang' => 'DL_THUMB_MAX_SIZE',	'validate' => 'int',	'type' => 'custom',		'explain' => false,		'help_key' => 'DL_THUMB_MAX_SIZE',	 	'function' => [$this, 'select_size'],	'params' => ['{CONFIG_VALUE}', 'dl_thumb_fsize', '10', '20', 'dl_f_quote', 'mb', false]],
						'dl_thumb_xsize'				=> ['lang' => 'DL_THUMB_MAX_DIM_X',	'validate' => 'int',	'type' => 'text:5:5',	'explain' => false,		'help_key' => 'DL_THUMB_MAX_DIM_X'],
						'dl_thumb_ysize'				=> ['lang' => 'DL_THUMB_MAX_DIM_Y',	'validate' => 'int',	'type' => 'text:5:5',	'explain' => false,		'help_key' => 'DL_THUMB_MAX_DIM_Y'],
		
						'legend7'				=> '',
		
						'dl_enable_rate'			=> ['lang' => 'DL_ENABLE_RATE',	'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_ENABLE_RATE'],
						'dl_rate_points'			=> ['lang' => 'DL_RATE_POINTS',	'validate' => 'int',	'type' => 'text:3:3',		'explain' => false,		'help_key' => 'DL_RATE_POINTS'],
					]
				];
		
				$sql = 'SELECT ext_name FROM ' . EXT_TABLE;
				$result = $this->db->sql_query($sql);
				$portal_exists = false;
				while ($row = $this->db->sql_fetchrow($result))
				{
					if (strtolower(strpos($row['ext_name'], 'portal')))
					{
						$portal_exists = true;
						break;
					}
				}
				$this->db->sql_freeresult($result);
		
				if ($portal_exists)
				{
					$display_vars['vars'] += [
						'legend8'				=> '',
		
						'dl_recent_downloads'	=> ['lang' => 'NUMBER_RECENT_DL_ON_PORTAL',	'validate' => 'int',	'type' => 'text:3:4',	'explain' => false,		'help_key' => 'NUMBER_RECENT_DL_ON_PORTAL'],
					];
				}
		
			break;
			case 'traffic':
				$sql = 'SELECT group_id, group_name, group_type FROM ' . GROUPS_TABLE . '
						WHERE ' . $this->db->sql_in_set('group_name', ['GUESTS', 'BOTS'], true) . '
						ORDER BY group_type DESC, group_name';
				$result = $this->db->sql_query($sql);
				$total_groups = $this->db->sql_affectedrows($result);
		
				$traffics_overall_group_ids = explode(',', $this->config['dl_traffics_overall_groups']);
				$traffics_users_group_ids = explode(',', $this->config['dl_traffics_users_groups']);
		
				$s_groups_overall_select = $s_groups_users_select = '';
		
				while ($row = $this->db->sql_fetchrow($result))
				{
					$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $this->language->lang('G_' . $row['group_name']) : $row['group_name'];
					$group_sep = ($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '';
					$group_id = $row['group_id'];
		
					if (in_array($group_id, $traffics_overall_group_ids) && $this->config['dl_traffics_overall'] > 1)
					{
						$s_groups_overall_select .= '<option value="' . $group_id . '" selected="selected"' . $group_sep . '>' . $group_name . '</option>';
					}
					else
					{
						$s_groups_overall_select .= '<option value="' . $group_id . '"' . $group_sep . '>' . $group_name . '</option>';
					}
		
					if (in_array($group_id, $traffics_users_group_ids) && $this->config['dl_traffics_users'] > 1)
					{
						$s_groups_users_select .= '<option value="' . $group_id . '" selected="selected"' . $group_sep . '>' . $group_name . '</option>';
					}
					else
					{
						$s_groups_users_select .= '<option value="' . $group_id . '"' . $group_sep . '>' . $group_name . '</option>';
					}
				}
		
				$this->db->sql_freeresult($result);
		
				$select_size = ($total_groups < 10) ? $total_groups : 10;
		
				$display_vars = [
					'title'	=> 'DL_ACP_CONF_TRAFFIC',
					'vars'	=> [
						'legend1'				=> '',
		
						'dl_traffics_founder'			=> ['lang' => 'DL_TRAFFICS_FOUNDER',			'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_TRAFFICS_FOUNDER'],
						'dl_traffics_overall'			=> ['lang' => 'DL_TRAFFICS_OVERALL',			'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_TRAFFICS_OVERALL',			'function' => [$this, 'select_traffic'],		'params' => ['{CONFIG_VALUE}', $total_groups]],
						'dl_traffics_overall_groups'	=> ['lang' => 'DL_TRAFFICS_OVERALL_GROUPS',								'type' => 'custom',			'explain' => false,		'help_key' => 'DL_TRAFFICS_OVERALL_GROUPS',		'function' => [$this, 'select_traffic_multi'],	'params' => ['dl_traffics_overall_groups', $s_groups_overall_select, $select_size]],
						'dl_traffics_users'				=> ['lang' => 'DL_TRAFFICS_USERS',				'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_TRAFFICS_USERS',				'function' => [$this, 'select_traffic'],		'params' => ['{CONFIG_VALUE}', $total_groups]],
						'dl_traffics_users_groups'		=> ['lang' => 'DL_TRAFFICS_USERS_GROUPS',								'type' => 'custom',			'explain' => false,		'help_key' => 'DL_TRAFFICS_USERS_GROUPS',		'function' => [$this, 'select_traffic_multi'],	'params' => ['dl_traffics_users_groups', $s_groups_users_select, $select_size]],
						'dl_traffics_guests'			=> ['lang' => 'DL_TRAFFICS_GUESTS',				'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_TRAFFICS_GUESTS'],
		
						'legend2'				=> '',
		
						'dl_overall_traffic'			=> ['lang' => 'DL_OVERALL_TRAFFIC',			'validate' => 'int',	'type' => 'custom',		'explain' => false,		'help_key' => 'DL_OVERALL_TRAFFIC',			'function' => [$this, 'select_size'],	'params' => ['{CONFIG_VALUE}', 'dl_overall_traffic', '10', '20', 'dl_x_over', 'gb', true]],
						'dl_overall_guest_traffic'		=> ['lang' => 'DL_OVERALL_GUEST_TRAFFIC',	'validate' => 'int',	'type' => 'custom',		'explain' => false,		'help_key' => 'DL_OVERALL_GUEST_TRAFFIC',	'function' => [$this, 'select_size'],	'params' => ['{CONFIG_VALUE}', 'dl_overall_guest_traffic', '10', '20', 'dl_x_g_over', 'gb', true]],
		
						'legend3'				=> '',
		
						'dl_enable_post_dl_traffic'		=> ['lang' => 'DL_ENABLE_POST_TRAFFIC',		'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_ENABLE_POST_TRAFFIC'],
						'dl_newtopic_traffic'			=> ['lang' => 'DL_NEWTOPIC_TRAFFIC',		'validate' => 'int',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_NEWTOPIC_TRAFFIC',		'function' => [$this, 'select_size'],	'params' => ['{CONFIG_VALUE}', 'dl_newtopic_traffic', '10', '20', 'dl_x_new', 'gb', false]],
						'dl_reply_traffic'				=> ['lang' => 'DL_REPLY_TRAFFIC',			'validate' => 'int',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_REPLY_TRAFFIC',			'function' => [$this, 'select_size'],	'params' => ['{CONFIG_VALUE}', 'dl_reply_traffic', '10', '20', 'dl_x_reply', 'gb', false]],
						'dl_drop_traffic_postdel'		=> ['lang' => 'DL_DROP_TRAFFIC_POSTDEL',	'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_DROP_TRAFFIC_POSTDEL'],
		
						'legend4'				=> '',
		
						'dl_delay_auto_traffic'		=> ['lang' => 'DL_DELAY_AUTO_TRAFFIC',		'validate' => 'int',	'type' => 'text:3:4',	'explain' => false,		'help_key' => 'DL_DELAY_AUTO_TRAFFIC'],
						'dl_delay_post_traffic'		=> ['lang' => 'DL_DELAY_POST_TRAFFIC',		'validate' => 'int',	'type' => 'text:3:4',	'explain' => false,		'help_key' => 'DL_DELAY_POST_TRAFFIC'],
		
						'legend5'				=> '',
		
						'dl_user_traffic_once'		=> ['lang' => 'DL_USER_TRAFFIC_ONCE',		'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_USER_TRAFFIC_ONCE'],
						'dl_upload_traffic_count'	=> ['lang' => 'DL_UPLOAD_TRAFFIC_COUNT',	'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_UPLOAD_TRAFFIC_COUNT'],
					]
				];
			break;
			case 'message':
				$display_vars = [
					'title'	=> 'DL_ACP_CONF_MESSAGE',
					'vars'	=> [
						'legend1'				=> '',

						'dl_disable_email'			=> ['lang' => 'DL_DISABLE_NOTIFY',			'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_DISABLE_NOTIFY'],
						'dl_disable_popup_notify'	=> ['lang' => 'DL_DISABLE_POPUP_NOTIFY',	'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_DISABLE_POPUP_NOTIFY'],

						'legend2'				=> '',

						'dl_file_edit_hint'			=> ['lang' => 'DL_FILE_EDIT_HINT',			'validate' => 'string',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_FILE_EDIT_HINT',		'preview' => $formated_hint_text,	'function' => [$this, 'textarea_input'],		'params' => ['{CONFIG_VALUE}', 'dl_file_edit_hint', 75, 5]],
					]
				];
			break;
			case 'topic':
				$display_vars = [
					'title'	=> 'DL_ACP_CONF_TOPIC',
					'vars'	=> [
						'legend1'				=> '',
		
						'dl_enable_dl_topic'		=> ['lang' => 'DL_ENABLE_TOPIC',			'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_ENABLE_TOPIC'],
						'dl_diff_topic_user'		=> ['lang' => 'DL_TOPIC_USER',				'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_TOPIC_USER',			'function' => [$this, 'select_topic_user'],		'params' => ['{CONFIG_VALUE}']],
						'dl_topic_user'				=> ['lang' => 'DL_TOPIC_USER_OTHER',		'validate' => 'string',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_TOPIC_USER',			'function' => [$this, 'select_dl_user'],		'params' => ['{CONFIG_VALUE}', 'dl_topic_user']],
						'dl_topic_forum'			=> ['lang' => 'DL_TOPIC_FORUM',				'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_TOPIC_FORUM',			'function' => [$this, 'select_dl_forum'],		'params' => ['{CONFIG_VALUE}']],
						'dl_topic_text'				=> ['lang' => 'DL_TOPIC_TEXT',				'validate' => 'string',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_TOPIC_TEXT',			'function' => [$this, 'textarea_input'],		'params' => ['{CONFIG_VALUE}', 'dl_topic_text', 75, 5]],
						'dl_topic_more_details'		=> ['lang' => 'DL_TOPIC_DETAILS',			'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_TOPIC_DETAILS',		'function' => [$this, 'select_topic_details'],	'params' => ['{CONFIG_VALUE}']],
						'dl_topic_title_catname'	=> ['lang' => 'DL_TOPIC_TITLE_CATNAME',		'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_TOPIC_TITLE_CATNAME'],
						'dl_topic_post_catname'		=> ['lang' => 'DL_TOPIC_POST_CATNAME',		'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_TOPIC_POST_CATNAME'],
						'dl_topic_type'				=> ['lang' => 'POST_TOPIC_AS',				'validate' => 'bool',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_TOPIC_TYPE', 			'function' => [$this, 'select_topic_type'],		'params' => ['{CONFIG_VALUE}']],
					]
				];
			break;
			case 'rss':
				$display_vars = [
					'title'	=> 'DL_ACP_CONF_RSS',
					'vars'	=> [
						'legend1'				=> '',
		
						'dl_rss_enable'			=> ['lang' => 'DL_RSS_ENABLE',					'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_RSS_ENABLE'],
						'dl_rss_off_action'		=> ['lang' => 'DL_RSS_OFF_ACTION',				'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_RSS_OFF_ACTION',			'function' => [$this, 'select_rss_off_action'],	'params' => ['{CONFIG_VALUE}']],
						'dl_rss_off_text'		=> ['lang' => 'DL_RSS_OFF_TEXT',				'validate' => 'string',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_RSS_OFF_TEXT',			'function' => [$this, 'textarea_input'],		'params' => ['{CONFIG_VALUE}', 'dl_rss_off_text', 75, 5]],
						'dl_rss_cats'			=> ['lang' => 'DL_RSS_CATS',					'validate' => 'int',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_RSS_CATS', 				'function' => [$this, 'select_rss_cats'],		'params' => ['{CONFIG_VALUE}']],
						'dl_rss_perms'			=> ['lang' => 'DL_RSS_PERMS',					'validate' => 'bool',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_RSS_PERMS', 				'function' => [$this, 'rss_perm'],				'params' => ['{CONFIG_VALUE}']],
						'dl_rss_number'			=> ['lang' => 'DL_RSS_NUMBER',					'validate' => 'int',	'type' => 'text:3:5',		'explain' => false,		'help_key' => 'DL_RSS_NUMBER'],
						'dl_rss_select'			=> ['lang' => 'DL_RSS_SELECT',					'validate' => 'bool',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_RSS_SELECT', 				'function' => [$this, 'rss_select'],			'params' => ['{CONFIG_VALUE}']],
						'dl_rss_new_update'		=> ['lang' => 'DL_RSS_NEW_UPDATE',				'validate' => 'bool',	'type' => 'switch:yes_no',	'explain' => false,		'help_key' => 'DL_RSS_NEW_UPDATE'],
						'dl_rss_desc_length'	=> ['lang' => 'DL_RSS_DESC_LENGTH',				'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_RSS_DESC_LENGTH',			'function' => [$this, 'select_rss_length'],		'params' => ['{CONFIG_VALUE}']],
						'dl_rss_desc_shorten'	=> ['lang' => 'DL_RSS_DESC_LENGTH_SHORTEN',		'validate' => 'int',	'type' => 'text:5:5',		'explain' => false,		'help_key' => 'DL_RSS_DESC_LENGTH_SHORTEN'],
					]
				];
			break;
		}
		
		$this->new_config = $this->config;
		$cfg_array = (isset($_REQUEST['config'])) ? $this->request->variable('config', ['' => ''], true) : $this->new_config;
		$error = [];

		// We validate the complete config if whished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);
		
		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]))
			{
				$this->new_config[$config_name] = $config_value = 0;
			}
			else if (strpos($config_name, 'legend') !== false && strpos($config_name, '_legend') === false)
			{
				continue;
			}
			else
			{
				$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];
			}

			if ($submit)
			{
				if ($config_name == 'dl_set_user' ||$config_name == 'dl_topic_user')
				{
					$this->new_config[$config_name] = $config_value = $this->dlext_extra->dl_user_switch(0, $config_value, $submit);
				}
		
				if ($config_name == 'dl_thumb_xsize' || $config_name == 'dl_thumb_ysize')
				{
					$this->new_config[$config_name] = $config_value = intval($config_value);
				}
		
				if (in_array($config_name, ['dl_thumb_fsize', 'dl_physical_quota', 'dl_overall_traffic', 'dl_overall_guest_traffic', 'dl_newtopic_traffic', 'dl_reply_traffic', 'dl_method_quota']))
				{
					$this->new_config[$config_name] = $config_value = $this->dlext_format->resize_value($config_name, $config_value);
				}
		
				if ($config_name == 'dl_enable_rate')
				{
					$cur_rate_points = $this->config['dl_rate_points'];
					$new_rate_points = $config_value;
		
					if (isset($cur_rate_points) && $cur_rate_points <> $new_rate_points)
					{
						$sql = 'DELETE FROM ' . DL_RATING_TABLE;
						$this->db->sql_query($sql);
		
						$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET rating = 0';
						$this->db->sql_query($sql);
					}
				}
		
				if ($config_name == 'dl_rss_cats')
				{
					$this->new_config['dl_rss_cats_select'] = '-';
					$rss_cats_select = $this->request->variable('dl_rss_cats_select', [0]);
		
					if (!empty($rss_cats_select))
					{
						$this->config->set('dl_rss_cats_select', implode(',', array_map('intval', $rss_cats_select)), false);
					}
		
					unset($rss_cats_select);
				}
		
				if ($config_name == 'dl_topic_user')
				{
					if (!$this->new_config[$config_name])
					{
						$this->new_config[$config_name] = $this->user->data['user_id'];
					}
					else
					{
						$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $this->new_config[$config_name];
						$result = $this->db->sql_query($sql);
						$user_exists = $this->db->sql_affectedrows($result);
						$this->db->sql_freeresult($result);
		
						if (!$user_exists)
						{
							$this->new_config[$config_name] = $this->user->data['user_id'];
						}
					}
				}
		
				if ($config_name == 'dl_file_hash_algo')
				{
					if ($this->new_config[$config_name] != $this->config['dl_file_hash_algo'])
					{
						$sql = 'UPDATE ' . DOWNLOADS_TABLE . " SET file_hash = ''";
						$this->db->sql_query($sql);
						$sql = 'UPDATE ' . DL_VERSIONS_TABLE . " SET ver_file_hash = ''";
						$this->db->sql_query($sql);
					}
				}

				if ($config_name == 'dl_file_edit_hint')
				{
					$allow_bbcode	= ($this->config['allow_bbcode']) ? true : false;
					$allow_urls		= true;
					$allow_smilies	= ($this->config['allow_smilies']) ? true : false;
					$hint_uid		= '';
					$hint_bitfield	= '';
					$hint_flags		= 0;
			
					if ($config_value)
					{
						generate_text_for_storage($config_value, $hint_uid, $hint_bitfield, $hint_flags, $allow_bbcode, true, $allow_smilies);
					}
		
					$this->config_text->set($config_name, $config_value, false);
					$this->config->set('dl_file_edit_hint_bbcode', $hint_uid, false);
					$this->config->set('dl_file_edit_hint_bitfield', $hint_bitfield, false);
					$this->config->set('dl_file_edit_hint_flags', $hint_flags, false);
				}
				else
				{
					$this->config->set($config_name, $config_value, false);
				}
			}
			else
			{
				if ($config_name == 'dl_set_user' || $config_name == 'dl_topic_user')
				{
					$this->new_config[$config_name] = $config_value = $this->dlext_extra->dl_user_switch($config_value);
				}	
			}
		}
		
		if ($submit)
		{
			// Refetch all multi select fields which are not provided by the forum default methods
			if ($view == 'traffic')
			{
				$dl_traffic_overall_groups	= $this->request->variable('dl_traffics_overall_groups', [0]);
				$dl_traffics_users_groups	= $this->request->variable('dl_traffics_users_groups', [0]);
		
				$this->new_config['dl_traffics_overall_groups'] = implode(',', $dl_traffic_overall_groups);
				$this->new_config['dl_traffics_users_groups'] = implode(',', $dl_traffics_users_groups);
		
				if (!empty($dl_traffic_overall_groups) && $cfg_array['dl_traffics_overall'] <= 1)
				{
					$this->new_config['dl_traffics_overall_groups'] = '';
				}
		
				if (!empty($dl_traffics_users_groups) && $cfg_array['dl_traffics_users'] <= 1)
				{
					$this->new_config['dl_traffics_users_groups'] = '';
				}
		
				$this->config->set('dl_traffics_overall_groups', $this->new_config['dl_traffics_overall_groups'], false);
				$this->config->set('dl_traffics_users_groups', $this->new_config['dl_traffics_users_groups'], false);
			}
		
			$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CONFIG');
		
			$cache = $this->phpbb_container->get('cache');

			$cache->destroy('config');
		
			// Purge the extension cache
			@unlink(DL_EXT_CACHE_PATH . 'data_dl_auth.' . $this->phpEx);
			@unlink(DL_EXT_CACHE_PATH . 'data_dl_auth_groups.' . $this->phpEx);
			@unlink(DL_EXT_CACHE_PATH . 'data_dl_black.' . $this->phpEx);
			@unlink(DL_EXT_CACHE_PATH . 'data_dl_cat_counts.' . $this->phpEx);
			@unlink(DL_EXT_CACHE_PATH . 'data_dl_cats.' . $this->phpEx);
			@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_preset.' . $this->phpEx);
		
			$message = $this->language->lang('DL_CONFIG_UPDATED') . adm_back_link($this->u_action . '&amp;view=' . $view);
			trigger_error($message);
		}
		
		if ($this->config['dl_traffic_off'])
		{
			$error[] = $this->language->lang('DL_TRAFFIC_OFF_EXPLAIN');
		}
		
		$acl_cat_names = [
			0 => [$this->language->lang('DL_ACP_CONF_GENERAL'),	'general'],
			1 => [$this->language->lang('DL_ACP_CONF_VIEW'),	'view'],
			2 => [$this->language->lang('DL_ACP_CONF_PROTECT'),	'protect'],
			3 => [$this->language->lang('DL_ACP_CONF_LIMIT'),	'limit'],
			4 => [$this->language->lang('DL_ACP_CONF_TRAFFIC'),	'traffic'],
			5 => [$this->language->lang('DL_ACP_CONF_MESSAGE'),	'message'],
			6 => [$this->language->lang('DL_ACP_CONF_TOPIC'),	'topic'],
			7 => [$this->language->lang('DL_ACP_CONF_RSS'),		'rss'],
		];
		
		$mode_select = '';
		
		for ($i = 0; $i < count($acl_cat_names); ++$i)
		{
			if ($view == $acl_cat_names[$i][1])
			{
				$mode_select .= '<option value="' . $acl_cat_names[$i][1] . '" selected="selected">' . $acl_cat_names[$i][0] . '</option>';
			}
			else
			{
				$mode_select .= '<option value="' . $acl_cat_names[$i][1] . '">' . $acl_cat_names[$i][0] . '</option>';
			}
		}
		
		$this->user->add_lang('acp/users');
		
		$this->template->assign_vars([
			'L_TITLE'			=> $this->language->lang('DL_CONFIG'),
			'L_TITLE_PAGE'		=> $this->language->lang($display_vars['title']),

			'EXT_FILES_PATH'	=> DL_EXT_FILEBASE_PATH,
			
			'S_ERROR'			=> (!empty($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),
			'S_HIDDEN_FIELDS'	=> (!empty($s_hidden_fields)) ? build_hidden_fields($s_hidden_fields) : '',
			'S_MODE_SELECT'		=> $mode_select,
			'U_MODE_SELECT'		=> $this->u_action,
		
			'U_ACTION'			=> $this->u_action . '&amp;view=' . $view,
		]);
		
		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && (strpos($config_key, 'legend') === false && strpos($config_key, '_legend') === false))
			{
				continue;
			}
		
			if (strpos($config_key, 'legend') !== false && strpos($config_key, '_legend') === false)
			{
				$this->template->assign_block_vars('options', [
					'S_LEGEND'		=> true,
					'LEGEND'		=> ($this->language->lang($vars)) ? $this->language->lang($vars) : $vars,
				]);
		
				continue;
			}
		
			$type = explode(':', $vars['type']);
		
			$l_explain = '';
			if ($vars['explain'])
			{
				$l_explain = ($this->language->lang($vars['lang'] . '_EXPLAIN') != $vars['lang'] . '_EXPLAIN') ? $this->language->lang($vars['lang'] . '_EXPLAIN') : '';
			}

			if ($config_key == 'dl_file_edit_hint')
			{
				$text_ary = generate_text_for_edit($dl_file_edit_hint, $dl_file_edit_hint_uid, $dl_file_edit_hint_flags);

				$this->new_config['dl_file_edit_hint'] = $text_ary['text'];
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);
		
			if (empty($content))
			{
				continue;
			}
		
			$help_key = $vars['help_key'];
		
			$this->template->assign_block_vars('options', [
				'KEY'			=> $config_key,
				'TITLE'			=> ($this->language->lang($vars['lang'])) ? $this->language->lang($vars['lang']) : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
				'PREVIEW'		=> (isset($vars['preview'])) ? $vars['preview'] : '',
				'HELP_KEY'		=> $help_key,
			]);
		
			unset($display_vars['vars'][$config_key]);
		}
	}

	/*
	* Helpers - Functions to enable custom layout for several options
	*/
	public function mod_disable($value)
	{
		$user = $this->user;

		$radio_ary = [1 => 'DL_OFF_NOW', 0 => 'DL_OFF_TIME'];

		return h_radio('config[dl_off_now_time]', $radio_ary, $value, 'dl_off_now_time');
	}

	public function rss_perm($value)
	{
		$user = $this->user;

		$radio_ary = [1 => 'DL_RSS_USER', 0 => 'DL_RSS_GUESTS'];

		return h_radio('config[dl_rss_perms]', $radio_ary, $value, 'dl_rss_perms');
	}

	public function rss_select($value)
	{
		$user = $this->user;

		$radio_ary = [1 => 'DL_RSS_SELECT_LAST', 0 => 'DL_RSS_SELECT_RANDOM'];

		return h_radio('config[dl_rss_select]', $radio_ary, $value, 'dl_rss_select');
	}

	public function select_dl_cat_edit($value)
	{
		$s_select = '<option value="0">' . $this->language->lang('DL_CAT_EDIT_LINK_0') . '</option>';
		$s_select .= '<option value="1">' . $this->language->lang('DL_CAT_EDIT_LINK_1') . '</option>';
		$s_select .= '<option value="2">' . $this->language->lang('DL_CAT_EDIT_LINK_2') . '</option>';
		$s_select .= '<option value="3">' . $this->language->lang('DL_CAT_EDIT_LINK_3') . '</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_dl_hash_algo($value)
	{
		$s_select = '<option value="md5">MD5</option>';
		$s_select .= '<option value="sha1">SHA1</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_dl_forum($dl_topic_forum)
	{
		$config = $this->config;

		$forum_select_tmp = get_forum_list('f_list', false);
		$select = '';

		foreach ($forum_select_tmp as $key => $value)
		{
			switch ($value['forum_type'])
			{
				case FORUM_CAT:
					if ($select)
					{
						$select .= '</optgroup>';
					}
					$select .= '<optgroup label="' . $value['forum_name'] . '">';
				break;
				case FORUM_POST:
					$select .= '<option value="' . $value['forum_id'] . '">' . $value['forum_name'] . '</option>';
				break;
			}
		}

		$select = '<option value="-1">' . $this->language->lang('DL_TOPIC_FORUM_C') . '</option><option value="0">' . $this->language->lang('DEACTIVATE') . '</option>' . $select . '</optgroup>';
		$select = str_replace('value="' . $dl_topic_forum . '">', 'value="' . $dl_topic_forum . '" selected="selected">', $select);

		return $select;
	}

	public function select_dl_vc($value)
	{
		$s_select = '<option value="0">' . $this->language->lang('DL_CAPTCHA_PERM_0') . '</option>';
		$s_select .= '<option value="1">' . $this->language->lang('DL_CAPTCHA_PERM_1') . '</option>';
		$s_select .= '<option value="2">' . $this->language->lang('DL_CAPTCHA_PERM_2') . '</option>';
		$s_select .= '<option value="3">' . $this->language->lang('DL_CAPTCHA_PERM_3') . '</option>';
		$s_select .= '<option value="4">' . $this->language->lang('DL_CAPTCHA_PERM_4') . '</option>';
		$s_select .= '<option value="5">' . $this->language->lang('DL_CAPTCHA_PERM_5') . '</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_hotlink_action($value)
	{
		$s_select = '<option value="1">' . $this->language->lang('DL_HOTLINK_ACTION_ONE') . '</option>';
		$s_select .= '<option value="0">' . $this->language->lang('DL_HOTLINK_ACTION_TWO') . '</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_report_action($value)
	{
		$s_select = '<option value="0">' . $this->language->lang('NO') . '</option>';
		$s_select .= '<option value="1">' . $this->language->lang('YES') . '</option>';
		$s_select .= '<option value="2">' . $this->language->lang('DL_OFF_GUESTS') . '</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_report_vc($value)
	{
		$s_select = '<option value="0">' . $this->language->lang('DL_CAPTCHA_PERM_0') . '</option>';
		$s_select .= '<option value="1">' . $this->language->lang('DL_CAPTCHA_PERM_1') . '</option>';
		$s_select .= '<option value="2">' . $this->language->lang('DL_CAPTCHA_PERM_2') . '</option>';
		$s_select .= '<option value="3">' . $this->language->lang('DL_CAPTCHA_PERM_3') . '</option>';
		$s_select .= '<option value="4">' . $this->language->lang('DL_CAPTCHA_PERM_4') . '</option>';
		$s_select .= '<option value="5">' . $this->language->lang('DL_CAPTCHA_PERM_5') . '</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_rss_cats($value)
	{
		$s_select = '<label><input type="radio" name="config[dl_rss_cats]" id="dl_rss_cats" class="radio" value="0" ' . (($value == 0) ? 'checked="checked"' : '' ) . ' />' . $this->language->lang('DL_RSS_CATS_ALL') . '</label>&nbsp;';
		$s_select .= '<label><input type="radio" name="config[dl_rss_cats]" class="radio" value="1" ' . (($value == 1) ? 'checked="checked"' : '' ) . ' />' . $this->language->lang('DL_RSS_CATS_SELECTED') . '</label>&nbsp;';
		$s_select .= '<label><input type="radio" name="config[dl_rss_cats]" class="radio" value="2" ' . (($value == 2) ? 'checked="checked"' : '' ) . ' />' . $this->language->lang('DL_RSS_CATS_NOT_SELECTED') . '</label>&nbsp;';

		if ($value <> 0)
		{
			$rss_cats = $this->dlext_extra->dl_cat_select(0, 0, array_map('intval', explode(',', $this->config['dl_rss_cats_select'])));
			$s_select .= '<br /><select name="dl_rss_cats_select[]" id="dl_rss_cats_select" multiple="multiple" size="5">' . $rss_cats . '</select>';
		}

		return $s_select;
	}

	public function select_rss_length($value)
	{
		$s_select = '<option value="0">' . $this->language->lang('DL_RSS_DESC_LENGTH_NONE') . '</option>';
		$s_select .= '<option value="1">' . $this->language->lang('DL_RSS_DESC_LENGTH_FULL') . '</option>';
		$s_select .= '<option value="2">' . $this->language->lang('DL_RSS_DESC_LENGTH_SHORT') . '</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_rss_off_action($value)
	{
		$s_select = '<option value="0">' . $this->language->lang('DL_RSS_ACTION_R_DLX') . '</option>';
		$s_select .= '<option value="1">' . $this->language->lang('DL_RSS_ACTION_R_IDX') . '</option>';
		$s_select .= '<option value="2">' . $this->language->lang('DL_RSS_ACTION_D_TXT') . '</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_size($value, $field, $size, $maxlength, $quote, $max_quote, $remain = false)
	{
		$quota_tmp = $this->dlext_format->dl_size($this->config[$field], 2, 'select');
		$quota_out = $quota_tmp['size_out'];
		$range_select = $quota_tmp['range'];

		$s_select = '<select name="' . $quote . '" id="' . $quote . '">';
		$s_select .= '<option value="byte">' . $this->language->lang('DL_BYTES_LONG') . '</option>';
		$s_select .= '<option value="kb">' . $this->language->lang('DL_KB') . '</option>';
		if ($max_quote == 'mb' || $max_quote == 'gb')
		{
			$s_select .= '<option value="mb">' . $this->language->lang('DL_MB') . '</option>';
		}
		if ($max_quote == 'gb')
		{
			$s_select .= '<option value="gb">' . $this->language->lang('DL_GB') . '</option>';
		}
		$s_select .= '</select>';

		$s_select = str_replace('value="' . $range_select . '">', 'value="' . $range_select . '" selected="selected">', $s_select);

		$remain_text_out = '';

		if ($remain)
		{
			switch ($field)
			{
				case 'dl_overall_traffic':
					$remain_traffic_text = $this->language->lang('DL_REMAIN_OVERALL_TRAFFIC');
					$remain_traffic = $this->config['dl_overall_traffic'] - (int) $this->config['dl_remain_traffic'];
					$remain_traffic = ($remain_traffic <= 0) ? 0 : $remain_traffic;

					$remain_traffic_tmp = $this->dlext_format->dl_size($remain_traffic, 2, 'none');
					$remain_traffic_out = $remain_traffic_tmp['size_out'];
					$x_rem = $remain_traffic_tmp['range'];
					$remain_text_out = $remain_traffic_text . $remain_traffic_out . $x_rem;
				break;
				case 'dl_overall_guest_traffic':
					$remain_traffic_text = $this->language->lang('DL_REMAIN_OVERALL_GUEST_TRAFFIC');
					$remain_traffic = $this->config['dl_overall_guest_traffic'] - (int) $this->config['dl_remain_guest_traffic'];
					$remain_traffic = ($remain_traffic <= 0) ? 0 : $remain_traffic;

					$remain_traffic_tmp = $this->dlext_format->dl_size($remain_traffic, 2, 'none');
					$remain_traffic_out = $remain_traffic_tmp['size_out'];
					$x_rem = $remain_traffic_tmp['range'];
					$remain_text_out = $remain_traffic_text . $remain_traffic_out . $x_rem;
				break;
				case 'dl_physical_quota':
					$remain_text_out = $this->language->lang('DL_PHYSICAL_QUOTA_EXPLAIN', $this->dlext_format->dl_size($this->dlext_physical->read_dl_sizes(), 2));
				break;
			}

			$remain_text_out = '<br /><span>&nbsp;' . $remain_text_out . '</span>';
		}

		return '<input type="text" size="' . $size . '" maxlength="' . $maxlength . '" name="config[' . $field . ']" id="' . $field . '" value="' . $quota_out . '" class="post" />&nbsp;' . $s_select . '' . $remain_text_out;
	}

	public function select_sort($value)
	{
		$s_select = '<option value="1">' . $this->language->lang('DL_SORT_ACP') . '</option>';
		$s_select .= '<option value="0">' . $this->language->lang('DL_SORT_USER') . '</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_stat_perm($value)
	{
		$s_select = '<option value="9">' . $this->language->lang('DL_EXT_STATS_0') . '</option>';
		$s_select .= '<option value="0">' . $this->language->lang('DL_STAT_PERM_ALL') . '</option>';
		$s_select .= '<option value="1">' . $this->language->lang('DL_STAT_PERM_USER') . '</option>';
		$s_select .= '<option value="2">' . $this->language->lang('DL_STAT_PERM_MOD') . '</option>';
		$s_select .= '<option value="3">' . $this->language->lang('DL_STAT_PERM_ADMIN') . '</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_topic_details($value)
	{
		$s_select = '<option value="0">' . $this->language->lang('DL_TOPIC_NO_MORE_DETAILS') . '</option>';
		$s_select .= '<option value="1">' . $this->language->lang('DL_TOPIC_MORE_DETAILS_UNDER') . '</option>';
		$s_select .= '<option value="2">' . $this->language->lang('DL_TOPIC_MORE_DETAILS_OVER') . '</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_topic_user($value)
	{
		$s_select = '<option value="0">' . $this->language->lang('DL_TOPIC_USER_SELF') . '</option>';
		$s_select .= '<option value="1">' . $this->language->lang('DL_TOPIC_USER_OTHER') . '</option>';
		$s_select .= '<option value="2">' . $this->language->lang('DL_TOPIC_USER_CAT') . '</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_traffic($value, $total_groups)
	{
		$s_select = '<option value="1">' . $this->language->lang('DL_TRAFFICS_ON_ALL') . '</option>';
		if ($total_groups)
		{
			$s_select .= '<option value="2">' . $this->language->lang('DL_TRAFFICS_ON_GROUPS') . '</option>';
			$s_select .= '<option value="3">' . $this->language->lang('DL_TRAFFICS_OFF_GROUPS') . '</option>';
		}
		$s_select .= '<option value="0">' . $this->language->lang('DL_TRAFFICS_OFF_ALL') . '</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_traffic_multi($field, $s_select, $select_size)
	{
		return '<select name="' . $field . '[]" id="' . $field . '" multiple="multiple" size="' . $select_size . '">' . $s_select . '</select>';
	}

	public function textarea_input($value, $field, $cols, $rows)
	{
		return '<label><textarea cols="' . $cols . '" rows="' . $rows . '" id="' . $field . '" class="inputbox autowidth" name="config[' . $field . ']">' . $value . '</textarea></label>';
	}

	public function select_dl_ext_stats($value)
	{
		$s_select = '<option value="0">' . $this->language->lang('DL_EXT_STATS_0') . '</option>';
		$s_select .= '<option value="1">' . $this->language->lang('DL_EXT_STATS_1') . '</option>';
		$s_select .= '<option value="2">' . $this->language->lang('DL_EXT_STATS_2') . '</option>';
		$s_select .= '<option value="3">' . $this->language->lang('DL_EXT_STATS_3') . '</option>';
		$s_select .= '<option value="4">' . $this->language->lang('DL_EXT_STATS_4') . '</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_topic_type($value)
	{
		$s_select = '<option value="' . POST_NORMAL . '">' . $this->language->lang('POST_NORMAL') . '</option>';
		$s_select .= '<option value="' . POST_STICKY . '">' . $this->language->lang('POST_STICKY') . '</option>';
		$s_select .= '<option value="' . POST_ANNOUNCE . '">' . $this->language->lang('POST_ANNOUNCEMENT') . '</option>';
		$s_select .= '<option value="' . POST_GLOBAL . '">' . $this->language->lang('POST_GLOBAL') . '</option>';
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_dl_user($value, $config)
	{
		$input_field = '<input class="text medium" type="text" id="' . $config . '" name="config[' . $config . ']" value="' . $value . '" />';
		$input_field .= '<br />&nbsp;&nbsp;&nbsp;&nbsp;[ <a href="' . append_sid("{$this->root_path}memberlist.{$this->phpEx}", 'mode=searchuser&amp;form=acp_dl_config&amp;field=' . $config . '&amp;select_single=true') . '" onclick="find_username(this.href); return false;">' . $this->language->lang('FIND_USERNAME') . '</a> ]';

		return $input_field;
	}

	public function select_nav_link_pos($value)
	{
		$s_select = '<option value="HIDE">'   . $this->language->lang('DL_NAV_LINK_HIDE')  . '</option>';	// Link hidden / for uses in extensions
		$s_select .= '<option value="NHQLB">' . $this->language->lang('DL_NAV_LINK_NHQLB') . '</option>';	// navbar_header_quick_links_before
		$s_select .= '<option value="NHQLA">' . $this->language->lang('DL_NAV_LINK_NHQLA') . '</option>';	// navbar_header_quick_links_after
		$s_select .= '<option value="OHNP">'  . $this->language->lang('DL_NAV_LINK_OHNP')  . '</option>';	// overall_header_navigation_prepend
		$s_select .= '<option value="OHNA">'  . $this->language->lang('DL_NAV_LINK_OHNA')  . '</option>';	// overall_header_navigation_append [DEFAULT]
		$s_select .= '<option value="NHUPA">' . $this->language->lang('DL_NAV_LINK_NHUPA') . '</option>';	// navbar_header_user_profile_append
		$s_select .= '<option value="NHPLB">' . $this->language->lang('DL_NAV_LINK_NHPLB') . '</option>';	// navbar_header_profile_list_before
		$s_select .= '<option value="NHPLA">' . $this->language->lang('DL_NAV_LINK_NHPLA') . '</option>';	// navbar_header_profile_list_after
		$s_select .= '<option value="NHUPP">' . $this->language->lang('DL_NAV_LINK_NHUPP') . '</option>';	// navbar_header_user_profile_prepend
		$s_select .= '<option value="OFTlA">' . $this->language->lang('DL_NAV_LINK_OFTlA') . '</option>';	// overall_footer_teamlink_after
		$s_select .= '<option value="OFTlB">' . $this->language->lang('DL_NAV_LINK_OFTlB') . '</option>';	// overall_footer_teamlink_before
		$s_select .= '<option value="OFTzA">' . $this->language->lang('DL_NAV_LINK_OFTzA') . '</option>';	// overall_footer_timezone_after
		$s_select .= '<option value="OFTzB">' . $this->language->lang('DL_NAV_LINK_OFTzB') . '</option>';	// overall_footer_timezone_before
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}

	public function select_latest_type($value)
	{
		$s_select = '<option value="0">' . $this->language->lang('DL_LATEST_TYPE_OFF') . '</option>'; 
		$s_select .= '<option value="1">' . $this->language->lang('DL_LATEST_TYPE_DEFAULT') . '</option>'; 
		$s_select .= '<option value="2">' . $this->language->lang('DL_LATEST_TYPE_COMPLETE') . '</option>'; 
		$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

		return $s_select;
	}
}
