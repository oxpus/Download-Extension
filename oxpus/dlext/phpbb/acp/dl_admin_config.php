<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* connect to phpBB
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

$submit	= $request->variable('submit', '');
$view	= $request->variable('view', 'general');

if ($submit && !check_form_key('dl_adm_config'))
{
	trigger_error('FORM_INVALID', E_USER_WARNING);
}

if (!$submit)
{
	add_form_key('dl_adm_config');
}

$language->add_lang('posting');

$s_hidden_fields = array();

switch ($view)
{
	default:
	case 'general':
		$display_vars = array(
			'title'	=> 'DL_ACP_CONF_GENERAL',
			'vars'	=> array(
				'legend1'				=> '',

				'dl_download_dir'	=> array('lang' => 'DOWNLOAD_PATH',		'validate' => 'int',	'type' => 'select',			'explain' => true,		'help_key' => 'DOWNLOAD_PATH',				'function' => 'select_filebase',	'params' => array('{CONFIG_VALUE}')),
				'dl_base_switch'	=> array('lang' => 'DL_BASE_SWITCH',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => ''),

				'legend2'				=> '',

				'dl_active'			=> array('lang' => 'DL_ACTIVE',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_ACTIVE'),
				'dl_traffic_off'	=> array('lang' => 'DL_TRAFFIC_OFF',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_TRAFFIC_OFF'),
				'dl_stop_uploads'	=> array('lang' => 'DL_STOP_UPLOADS',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_STOP_UPLOADS'),
				'dl_use_hacklist'	=> array('lang' => 'DL_USE_HACKLIST',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_USE_HACKLIST'),
				'dl_todo_onoff'		=> array('lang' => 'DL_USE_TODOLIST',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_USE_TODOLIST'),

				'legend3'				=> '',

				'dl_off_now_time'	=> array('lang' => 'DL_OFF_NOW_TIME',		'validate' => 'bool',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_OFF_NOW_TIME', 		'function' => 'mod_disable',	'params' => array('{CONFIG_VALUE}')),
				'dl_off_from'		=> array('lang' => 'DL_OFF_PERIOD',			'validate' => 'string',	'type' => 'text:5:5',		'explain' => false,		'help_key' => 'DL_OFF_PERIOD'),
				'dl_off_till'		=> array('lang' => 'DL_OFF_PERIOD_TILL',	'validate' => 'string',	'type' => 'text:5:5',		'explain' => false,		'help_key' => 'DL_OFF_PERIOD_TILL'),
				'dl_on_admins'		=> array('lang' => 'DL_ON_ADMINS',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_ON_ADMINS'),
				'dl_off_hide'		=> array('lang' => 'DL_OFF_HIDE',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_OFF_HIDE'),

				'legend4'				=> '',

				'dl_set_add'		=> array('lang' => 'DL_SET_ADD',			'validate' => 'int',	'type' => 'select',		'explain' => false,		'help_key' => 'DL_SET_ADD', 				'function' => 'select_topic_user',	'params' => array('{CONFIG_VALUE}')),
				'dl_set_user'		=> array('lang' => 'DL_TOPIC_USER_OTHER',	'validate' => 'string',	'type' => 'custom',		'explain' => false,		'help_key' => 'DL_SET_ADD',					'function' => 'select_dl_user', 	'params' => array('{CONFIG_VALUE}', 'dl_set_user')),
			)
		);
	break;
	case 'view':
		$display_vars = array(
			'title'	=> 'DL_ACP_CONF_VIEW',
			'vars'	=> array(
				'legend1'				=> '',

				'dl_icon_free_for_reg'		=> array('lang' => 'DL_ICON_FREE_FOR_REG',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_ICON_FREE_FOR_REG'),
				'dl_new_time'				=> array('lang' => 'DL_NEW_TIME',				'validate' => 'string',	'type' => 'text:3:4',		'explain' => false,		'help_key' => 'DL_NEW_TIME'),
				'dl_edit_time'				=> array('lang' => 'DL_EDIT_TIME',				'validate' => 'string',	'type' => 'text:3:4',		'explain' => false,		'help_key' => 'DL_EDIT_TIME'),
				'dl_show_footer_legend'		=> array('lang' => 'DL_SHOW_FOOTER_LEGEND',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_SHOW_FOOTER_LEGEND'),
				'dl_show_footer_stat'		=> array('lang' => 'DL_SHOW_FOOTER_STAT',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_SHOW_FOOTER_STAT'),
				'dl_mini_stats_ext'			=> array('lang' => 'DL_SHOW_FOOTER_EXT_STATS',	'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_SHOW_FOOTER_EXT_STATS',	'function' => 'select_dl_ext_stats',	'params' => array('{CONFIG_VALUE}')),
				'dl_overview_link_onoff'	=> array('lang' => 'DL_OVERVIEW_LINK',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_OVERVIEW_LINK'),
				'dl_todo_link_onoff'		=> array('lang' => 'DL_TODO_LINK',				'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_TODO_LINK'),
				'dl_uconf_link_onoff'		=> array('lang' => 'DL_UCONF_LINK',				'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_UCONF_LINK'),
				'dl_enable_jumpbox'			=> array('lang' => 'DL_ENABLE_JUMPBOX',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_ENABLE_JUMPBOX'),
				'dl_cat_edit'				=> array('lang' => 'DL_CAT_EDIT_LINK',			'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_CAT_EDIT_LINK',		'function' => 'select_dl_cat_edit',	'params' => array('{CONFIG_VALUE}')),

				'legend2'				=> '',

				'dl_links_per_page'			=> array('lang' => 'DL_LINKS_PER_PAGE',			'validate' => 'string',	'type' => 'text:3:4',		'explain' => false,		'help_key' => 'DL_LINKS_PER_PAGE'),
				'dl_shorten_extern_links'	=> array('lang' => 'DL_SHORTEN_EXTERN_LINKS',	'validate' => 'string',	'type' => 'text:3:4',		'explain' => false,		'help_key' => 'DL_SHORTEN_EXTERN_LINKS'),
				'dl_index_desc_hide'		=> array('lang' => 'DL_INDEX_DESC_HIDE',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_INDEX_DESC_HIDE'),
				'dl_limit_desc_on_index'	=> array('lang' => 'DL_LIMIT_DESC_ON_INDEX',	'validate' => 'string',	'type' => 'text:5:10',		'explain' => false,		'help_key' => 'DL_LIMIT_DESC_ON_INDEX'),

				'legend3'				=> '',

				'dl_show_real_filetime'		=> array('lang' => 'DL_SHOW_REAL_FILETIME',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_SHOW_REAL_FILETIME'),
				'dl_file_hash_algo'			=> array('lang' => 'DL_FILE_HASH_ALGO',			'validate' => 'string',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_FILE_HASH_ALGO',					'function' => 'select_dl_hash_algo',	'params' => array('{CONFIG_VALUE}')),
				'dl_ext_new_window'			=> array('lang' => 'DL_EXT_NEW_WINDOW',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_EXT_NEW_WINDOW'),
				'dl_report_broken_message'	=> array('lang' => 'DL_REPORT_BROKEN_MESSAGE',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_REPORT_BROKEN_MESSAGE'),
			)
		);

		$fulltext_dl_search_enabled = false;
		global $dbms;

		if (strpos(strtolower($dbms), 'mysql') !== false)
		{
			$sql = 'SHOW INDEX FROM ' . DOWNLOADS_TABLE;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['Key_name'] == 'desc_search')
				{
					$fulltext_dl_search_enabled = true;
				}
			}
			$db->sql_freeresult($result);
		}

		if ($fulltext_dl_search_enabled)
		{
			$display_vars['vars'] = array_merge($display_vars['vars'], array(
				'legend4'				=> '',

				'dl_similar_dl'		=> array('lang' => 'DL_SIMILAR_DL_OPTION',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_SIMILAR_DL'),
				'dl_similar_limit'	=> array('lang' => 'DL_SIMILAR_DL_LIMIT',		'validate' => 'int',	'type' => 'text:3:5',		'explain' => false,		'help_key' => 'DL_SIMILAR_DL_LIMIT'),
			));
		}
		else
		{
			$s_hidden_fields = array('dl_similar_limit' => 0, 'dl_similar_dl' => 0);
		}

	break;
	case 'protect':
		$display_vars = array(
			'title'	=> 'DL_ACP_CONF_PROTECT',
			'vars'	=> array(
				'legend1'				=> '',

				'dl_global_guests'		=> array('lang' => 'DL_GLOBAL_GUESTS',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_GLOBAL_GUESTS'),

				'legend2'				=> '',

				'dl_use_ext_blacklist'	=> array('lang' => 'DL_USE_EXT_BLACKLIST',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_USE_EXT_BLACKLIST'),

				'legend3'				=> 'DL_ANTISPAM',

				'dl_antispam_posts'		=> array('lang' => 'DL_ANTISPAM_POSTS',		'validate' => 'int',	'type' => 'text:5:10',		'explain' => false,		'help_key' => 'DL_ANTISPAM'),
				'dl_antispam_hours'		=> array('lang' => 'DL_ANTISPAM_HOURS',		'validate' => 'int',	'type' => 'text:5:10',		'explain' => false,		'help_key' => 'DL_ANTISPAM'),

				'legend4'				=> '',

				'dl_download_vc'		=> array('lang' => 'DL_VISUAL_CONFIRMATION',	'validate' => 'int',	'type' => 'select',		'explain' => true,		'help_key' => 'DL_VISUAL_CONFIRMATION',		'function' => 'select_dl_vc',		'params' => array('{CONFIG_VALUE}')),
				'dl_report_broken_vc'	=> array('lang' => 'DL_REPORT_BROKEN_VC',		'validate' => 'int',	'type' => 'select',		'explain' => false,		'help_key' => 'DL_REPORT_BROKEN_VC',		'function' => 'select_report_vc',	'params' => array('{CONFIG_VALUE}')),

				'legend5'				=> '',

				'dl_stats_perm'		=> array('lang' => 'DL_STAT_PERM',	'validate' => 'int',	'type' => 'select',		'explain' => false,		'help_key' => 'DL_STAT_PERM',	'function' => 'select_stat_perm',	'params' => array('{CONFIG_VALUE}')),

				'legend6'				=> '',

				'dl_prevent_hotlink'	=> array('lang' => 'DL_PREVENT_HOTLINK',	'validate' => 'int',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_PREVENT_HOTLINK'),
				'dl_hotlink_action'		=> array('lang' => 'DL_HOTLINK_ACTION',		'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_HOTLINK_ACTION',		'function' => 'select_hotlink_action',	'params' => array('{CONFIG_VALUE}')),
			)
		);
	break;
	case 'limit':
		$display_vars = array(
			'title'	=> 'DL_ACP_CONF_LIMIT',
			'vars'	=> array(
				'legend1'				=> '',

				'dl_report_broken'			=> array('lang' => 'DL_REPORT_BROKEN',		'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_REPORT_BROKEN_LOCK',		'function' => 'select_report_action',	'params' => array('{CONFIG_VALUE}')),
				'dl_report_broken_lock'		=> array('lang' => 'DL_REPORT_BROKEN_LOCK',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_REPORT_BROKEN'),
				'dl_sort_preform'			=> array('lang' => 'DL_SORT_PREFORM',		'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_SORT_PREFORM',			'function' => 'select_sort',			'params' => array('{CONFIG_VALUE}')),
				'dl_posts'					=> array('lang' => 'DL_POSTS',				'validate' => 'int',	'type' => 'text:3:4',		'explain' => false,		'help_key' => 'DL_POSTS'),

				'legend2'				=> '',

				'dl_edit_own_downloads'		=> array('lang' => 'DL_EDIT_OWN_DOWNLOADS',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_EDIT_OWN_DOWNLOADS'),

				'legend3'				=> '',

				'dl_guest_stats_show'		=> array('lang' => 'DL_GUEST_STATS_SHOW',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_GUEST_STATS_SHOW'),

				'legend4'				=> '',

				'dl_thumb_fsize'				=> array('lang' => 'DL_THUMB_MAX_SIZE',		'validate' => 'int',	'type' => 'custom',		'explain' => false,		'help_key' => 'DL_THUMB_MAX_SIZE',	 	'function' => 'select_size',	'params' => array('{CONFIG_VALUE}', 'dl_thumb_fsize', '10', '20', 'dl_f_quote', 'mb', false, $ext_path)),
				'dl_thumb_xsize'				=> array('lang' => 'DL_THUMB_MAX_DIM_X',	'validate' => 'int',	'type' => 'text:5:5',	'explain' => false,		'help_key' => 'DL_THUMB_MAX_DIM_X'),
				'dl_thumb_ysize'				=> array('lang' => 'DL_THUMB_MAX_DIM_Y',	'validate' => 'int',	'type' => 'text:5:5',	'explain' => false,		'help_key' => 'DL_THUMB_MAX_DIM_Y'),

				'legend5'				=> '',

				'dl_enable_rate'			=> array('lang' => 'DL_ENABLE_RATE',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_ENABLE_RATE'),
				'dl_rate_points'			=> array('lang' => 'DL_RATE_POINTS',	'validate' => 'int',	'type' => 'text:3:3',		'explain' => false,		'help_key' => 'DL_RATE_POINTS'),
			)
		);

		$sql = 'SELECT ext_name FROM ' . EXT_TABLE;
		$result = $db->sql_query($sql);
		$portal_exists = false;
		while ($row = $db->sql_fetchrow($result))
		{
			if (strtolower(strpos($row['ext_name'], 'portal')))
			{
				$portal_exists = true;
				break;
			}
		}
		$db->sql_freeresult($result);

		if ($portal_exists)
		{
			$display_vars['vars'] = array_merge($display_vars['vars'], array(
				'legend6'				=> '',

				'dl_recent_downloads'	=> array('lang' => 'NUMBER_RECENT_DL_ON_PORTAL',	'validate' => 'int',	'type' => 'text:3:4',	'explain' => false,		'help_key' => 'NUMBER_RECENT_DL_ON_PORTAL'),
			));
		}

	break;
	case 'traffic':
		$sql = 'SELECT group_id, group_name, group_type FROM ' . GROUPS_TABLE . "
			WHERE group_name NOT IN ('GUESTS', 'BOTS')";
		$result = $db->sql_query($sql);
		$total_groups = $db->sql_affectedrows($result);

		$traffics_overall_group_ids = explode(',', $config['dl_traffics_overall_groups']);
		$traffics_users_group_ids = explode(',', $config['dl_traffics_users_groups']);

		$s_groups_overall_select = $s_groups_users_select = '';

		while ($row = $db->sql_fetchrow($result))
		{
			$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $language->lang('G_' . $row['group_name']) : $row['group_name'];
			$group_id = $row['group_id'];

			if (in_array($group_id, $traffics_overall_group_ids) && $config['dl_traffics_overall'] > 1)
			{
				$s_groups_overall_select .= '<option value="' . $group_id . '" selected="selected">' . $group_name . '</option>';
			}
			else
			{
				$s_groups_overall_select .= '<option value="' . $group_id . '">' . $group_name . '</option>';
			}

			if (in_array($group_id, $traffics_users_group_ids) && $config['dl_traffics_users'] > 1)
			{
				$s_groups_users_select .= '<option value="' . $group_id . '" selected="selected">' . $group_name . '</option>';
			}
			else
			{
				$s_groups_users_select .= '<option value="' . $group_id . '">' . $group_name . '</option>';
			}
		}

		$db->sql_freeresult($result);

		$select_size = ($total_groups < 10) ? $total_groups : 10;

		$display_vars = array(
			'title'	=> 'DL_ACP_CONF_TRAFFIC',
			'vars'	=> array(
				'legend1'				=> '',

				'dl_physical_quota'		=> array('lang' => 'DL_PHYSICAL_QUOTA',		'validate' => 'int',	'type' => 'custom',		'explain' => false,		'help_key' => 'DL_PHYSICAL_QUOTA',	'function' => 'select_size',	'params' => array('{CONFIG_VALUE}', 'dl_physical_quota', '10', '20', 'dl_x_quota', 'gb', true, $ext_path)),

				'legend2'				=> '',

				'dl_traffics_founder'			=> array('lang' => 'DL_TRAFFICS_FOUNDER',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_TRAFFICS_FOUNDER'),
				'dl_traffics_overall'			=> array('lang' => 'DL_TRAFFICS_OVERALL',			'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_TRAFFICS_OVERALL',			'function' => 'select_traffic',	'params' => array('{CONFIG_VALUE}', $total_groups)),
				'dl_traffics_overall_groups'	=> array('lang' => 'DL_TRAFFICS_OVERALL_GROUPS',							'type' => 'custom',			'explain' => false,		'help_key' => 'DL_TRAFFICS_OVERALL_GROUPS',		'function' => 'select_traffic_multi',	'params' => array('dl_traffics_overall_groups', $s_groups_overall_select, $select_size)),
				'dl_traffics_users'				=> array('lang' => 'DL_TRAFFICS_USERS',				'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_TRAFFICS_USERS',				'function' => 'select_traffic',	'params' => array('{CONFIG_VALUE}', $total_groups)),
				'dl_traffics_users_groups'		=> array('lang' => 'DL_TRAFFICS_USERS_GROUPS',								'type' => 'custom',			'explain' => false,		'help_key' => 'DL_TRAFFICS_USERS_GROUPS',		'function' => 'select_traffic_multi',	'params' => array('dl_traffics_users_groups', $s_groups_users_select, $select_size)),
				'dl_traffics_guests'			=> array('lang' => 'DL_TRAFFICS_GUESTS',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_TRAFFICS_GUESTS'),

				'legend3'				=> '',

				'dl_overall_traffic'			=> array('lang' => 'DL_OVERALL_TRAFFIC',		'validate' => 'int',	'type' => 'custom',		'explain' => false,		'help_key' => 'DL_OVERALL_TRAFFIC',			'function' => 'select_size',	'params' => array('{CONFIG_VALUE}', 'dl_overall_traffic', '10', '20', 'dl_x_over', 'gb', true, $ext_path)),
				'dl_overall_guest_traffic'		=> array('lang' => 'DL_OVERALL_GUEST_TRAFFIC',	'validate' => 'int',	'type' => 'custom',		'explain' => false,		'help_key' => 'DL_OVERALL_GUEST_TRAFFIC',	'function' => 'select_size',	'params' => array('{CONFIG_VALUE}', 'dl_overall_guest_traffic', '10', '20', 'dl_x_g_over', 'gb', true, $ext_path)),

				'legend4'				=> '',

				'dl_enable_post_dl_traffic'		=> array('lang' => 'DL_ENABLE_POST_TRAFFIC',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_ENABLE_POST_TRAFFIC'),
				'dl_newtopic_traffic'			=> array('lang' => 'DL_NEWTOPIC_TRAFFIC',		'validate' => 'int',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_NEWTOPIC_TRAFFIC',		'function' => 'select_size',	'params' => array('{CONFIG_VALUE}', 'dl_newtopic_traffic', '10', '20', 'dl_x_new', 'gb', false, $ext_path)),
				'dl_reply_traffic'				=> array('lang' => 'DL_REPLY_TRAFFIC',			'validate' => 'int',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_REPLY_TRAFFIC',			'function' => 'select_size',	'params' => array('{CONFIG_VALUE}', 'dl_reply_traffic', '10', '20', 'dl_x_reply', 'gb', false, $ext_path)),
				'dl_drop_traffic_postdel'		=> array('lang' => 'DL_DROP_TRAFFIC_POSTDEL',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_DROP_TRAFFIC_POSTDEL'),

				'legend5'				=> '',

				'dl_delay_auto_traffic'		=> array('lang' => 'DL_DELAY_AUTO_TRAFFIC',		'validate' => 'int',	'type' => 'text:3:4',	'explain' => false,		'help_key' => 'DL_DELAY_AUTO_TRAFFIC'),
				'dl_delay_post_traffic'		=> array('lang' => 'DL_DELAY_POST_TRAFFIC',		'validate' => 'int',	'type' => 'text:3:4',	'explain' => false,		'help_key' => 'DL_DELAY_POST_TRAFFIC'),

				'legend6'				=> '',

				'dl_user_traffic_once'		=> array('lang' => 'DL_USER_TRAFFIC_ONCE',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_USER_TRAFFIC_ONCE'),
				'dl_upload_traffic_count'	=> array('lang' => 'DL_UPLOAD_TRAFFIC_COUNT',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_UPLOAD_TRAFFIC_COUNT'),
			)
		);
	break;
	case 'message':
		$display_vars = array(
			'title'	=> 'DL_ACP_CONF_MESSAGE',
			'vars'	=> array(
				'legend1'				=> '',

				'dl_disable_email'			=> array('lang' => 'DL_DISABLE_EMAIL',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_DISABLE_EMAIL'),
				'dl_disable_popup'			=> array('lang' => 'DL_DISABLE_POPUP',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_DISABLE_POPUP'),
				'dl_disable_popup_notify'	=> array('lang' => 'DL_DISABLE_POPUP_NOTIFY',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_DISABLE_POPUP_NOTIFY'),
			)
		);
	break;
	case 'topic':
		$display_vars = array(
			'title'	=> 'DL_ACP_CONF_TOPIC',
			'vars'	=> array(
				'legend1'				=> '',

				'dl_enable_dl_topic'		=> array('lang' => 'DL_ENABLE_TOPIC',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_ENABLE_TOPIC'),
				'dl_diff_topic_user'		=> array('lang' => 'DL_TOPIC_USER',				'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_TOPIC_USER',			'function' => 'select_topic_user',		'params' => array('{CONFIG_VALUE}')),
				'dl_topic_user'				=> array('lang' => 'DL_TOPIC_USER_OTHER',		'validate' => 'string',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_TOPIC_USER',			'function' => 'select_dl_user',			'params'  => array('{CONFIG_VALUE}', 'dl_topic_user')),
				'dl_topic_forum'			=> array('lang' => 'DL_TOPIC_FORUM',			'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_TOPIC_FORUM',			'function' => 'select_dl_forum',		'params' => array('{CONFIG_VALUE}')),
				'dl_topic_text'				=> array('lang' => 'DL_TOPIC_TEXT',				'validate' => 'string',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_TOPIC_TEXT',			'function' => 'textarea_input',			'params' => array('{CONFIG_VALUE}', 'dl_topic_text', 75, 5)),
				'dl_topic_more_details'		=> array('lang' => 'DL_TOPIC_DETAILS',			'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_TOPIC_DETAILS',		'function' => 'select_topic_details',	'params' => array('{CONFIG_VALUE}')),
				'dl_topic_title_catname'	=> array('lang' => 'DL_TOPIC_TITLE_CATNAME',	'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_TOPIC_TITLE_CATNAME'),
				'dl_topic_post_catname'		=> array('lang' => 'DL_TOPIC_POST_CATNAME',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_TOPIC_POST_CATNAME'),
				'dl_topic_type'				=> array('lang' => 'POST_TOPIC_AS',				'validate' => 'bool',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_TOPIC_TYPE', 			'function' => 'select_topic_type',		'params' => array('{CONFIG_VALUE}')),
			)
		);
	break;
	case 'rss':
		$display_vars = array(
			'title'	=> 'DL_ACP_CONF_RSS',
			'vars'	=> array(
				'legend1'				=> '',

				'dl_rss_enable'			=> array('lang' => 'DL_RSS_ENABLE',					'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_RSS_ENABLE'),
				'dl_rss_off_action'		=> array('lang' => 'DL_RSS_OFF_ACTION',				'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_RSS_OFF_ACTION',			'function' => 'select_rss_off_action',	'params' => array('{CONFIG_VALUE}')),
				'dl_rss_off_text'		=> array('lang' => 'DL_RSS_OFF_TEXT',				'validate' => 'string',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_RSS_OFF_TEXT',			'function' => 'textarea_input',			'params' => array('{CONFIG_VALUE}', 'dl_rss_off_text', 75, 5)),
				'dl_rss_cats'			=> array('lang' => 'DL_RSS_CATS',					'validate' => 'int',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_RSS_CATS', 				'function' => 'select_rss_cats',		'params' => array('{CONFIG_VALUE}')),
				'dl_rss_perms'			=> array('lang' => 'DL_RSS_PERMS',					'validate' => 'bool',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_RSS_PERMS', 				'function' => 'rss_perm',				'params' => array('{CONFIG_VALUE}')),
				'dl_rss_number'			=> array('lang' => 'DL_RSS_NUMBER',					'validate' => 'int',	'type' => 'text:3:5',		'explain' => false,		'help_key' => 'DL_RSS_NUMBER'),
				'dl_rss_select'			=> array('lang' => 'DL_RSS_SELECT',					'validate' => 'bool',	'type' => 'custom',			'explain' => false,		'help_key' => 'DL_RSS_SELECT', 				'function' => 'rss_select',				'params' => array('{CONFIG_VALUE}')),
				'dl_rss_new_update'		=> array('lang' => 'DL_RSS_NEW_UPDATE',				'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false,		'help_key' => 'DL_RSS_NEW_UPDATE'),
				'dl_rss_desc_length'	=> array('lang' => 'DL_RSS_DESC_LENGTH',			'validate' => 'int',	'type' => 'select',			'explain' => false,		'help_key' => 'DL_RSS_DESC_LENGTH',			'function' => 'select_rss_length',		'params' => array('{CONFIG_VALUE}')),
				'dl_rss_desc_shorten'	=> array('lang' => 'DL_RSS_DESC_LENGTH_SHORTEN',	'validate' => 'int',	'type' => 'text:5:5',		'explain' => false,		'help_key' => 'DL_RSS_DESC_LENGTH_SHORTEN'),
			)
		);
	break;
}

$this->new_config = $config;
$cfg_array = (isset($_REQUEST['config'])) ? $request->variable('config', array('' => ''), true) : $this->new_config;
$error = array();
$movement_error = false;
$movement_message = '';
$cur_dl_dir = $config['dl_download_dir'];

// We validate the complete config if whished
validate_config_vars($display_vars['vars'], $cfg_array, $error);

// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
foreach ($display_vars['vars'] as $config_name => $null)
{
	if (!isset($cfg_array[$config_name]) || (strpos($config_name, 'legend') !== false && strpos($config_name, '_legend') === false))
	{
		continue;
	}

	$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

	if ($submit)
	{
		if ($config_name == 'dl_download_dir' && $config_value <> $cur_dl_dir)
		{
			if (!$cfg_array['dl_base_switch'])
			{
				// disable the board to avoid new/edit downloads while moving the file base!!
				$config->set('board_disable', true);
				$success = \oxpus\dlext\phpbb\classes\ dl_physical::switch_ext_file_path($folder_base, $folder_desc, $cur_dl_dir, $phpbb_root_path, $ext_path);

				switch ($success)
				{
					case 1:
						$movement_error = true;
						$movement_message = 'DL_FILEBASE_NOT_MOVED';
					break;
					case 2:
						$movement_error = false;
						$movement_message = 'DL_FILEBASE_NOT_DROPPED';
					break;
					default:
						$movement_error = false;
						$movement_message = 'DL_FILEBASE_MOVED';
				}

				// enable the board after doing all the work
				$config->set('board_disable', false);

				$movement_message = $language->lang($movement_message);
			}
			else
			{
				$movement_message = $language->lang('DL_FILEBASE_MANUALLY');
			}

			if ($movement_error)
			{
				trigger_error($movement_message);
			}
		}

		if ($config_name == 'dl_set_user' ||$config_name == 'dl_topic_user')
		{
			$this->new_config[$config_name] = $config_value = \oxpus\dlext\phpbb\classes\ dl_extra::dl_user_switch(0, $config_value, $submit);
		}

		if ($config_name == 'dl_thumb_xsize' || $config_name == 'dl_thumb_ysize')
		{
			$this->new_config[$config_name] = $config_value = intval($config_value);
		}

		if (in_array($config_name, array('dl_thumb_fsize', 'dl_physical_quota', 'dl_overall_traffic', 'dl_overall_guest_traffic', 'dl_newtopic_traffic', 'dl_reply_traffic', 'dl_method_quota')))
		{
			$this->new_config[$config_name] = $config_value = \oxpus\dlext\phpbb\classes\ dl_format::resize_value($config_name, $config_value);
		}

		if ($config_name == 'dl_enable_rate')
		{
			$cur_rate_points = $config['dl_rate_points'];
			$new_rate_points = $config_value;

			if (isset($cur_rate_points) && $cur_rate_points <> $new_rate_points)
			{
				$sql = 'DELETE FROM ' . DL_RATING_TABLE;
				$db->sql_query($sql);

				$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET rating = 0';
				$db->sql_query($sql);
			}
		}

		if ($config_name == 'dl_rss_cats')
		{
			$this->new_config['dl_rss_cats_select'] = '-';
			$rss_cats_select = $request->variable('dl_rss_cats_select', array(0));

			if (sizeof($rss_cats_select))
			{
				$config->set('dl_rss_cats_select', implode(',', array_map('intval', $rss_cats_select)), false);
			}

			unset($rss_cats_select);
		}

		if ($config_name == 'dl_topic_user')
		{
			if (!$this->new_config[$config_name])
			{
				$this->new_config[$config_name] = $user->data['user_id'];
			}
			else
			{
				$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $this->new_config[$config_name];
				$result = $db->sql_query($sql);
				$user_exists = $db->sql_affectedrows($result);
				$db->sql_freeresult($result);

				if (!$user_exists)
				{
					$this->new_config[$config_name] = $user->data['user_id'];
				}
			}
		}

		if ($config_name == 'dl_file_hash_algo')
		{
			if ($this->new_config[$config_name] != $config['dl_file_hash_algo'])
			{
				$sql = 'UPDATE ' . DOWNLOADS_TABLE . " SET file_hash = ''";
				$db->sql_query($sql);
				$sql = 'UPDATE ' . DL_VERSIONS_TABLE . " SET ver_file_hash = ''";
				$db->sql_query($sql);
			}
		}

		$config->set($config_name, $config_value, false);
	}
	else
	{
		if ($config_name == 'dl_set_user' || $config_name == 'dl_topic_user')
		{
			$this->new_config[$config_name] = $config_value = \oxpus\dlext\phpbb\classes\ dl_extra::dl_user_switch($config_value);
		}	
	}
}

if ($submit && !$movement_error)
{
	// Refetch all multi select fields which are not provided by the forum default methods
	if ($view == 'traffic')
	{
		$dl_traffic_overall_groups	= $request->variable('dl_traffics_overall_groups', array(0));
		$dl_traffics_users_groups	= $request->variable('dl_traffics_users_groups', array(0));

		$this->new_config['dl_traffics_overall_groups'] = implode(',', $dl_traffic_overall_groups);
		$this->new_config['dl_traffics_users_groups'] = implode(',', $dl_traffics_users_groups);

		if (sizeof($dl_traffic_overall_groups) && $cfg_array['dl_traffics_overall'] <= 1)
		{
			$this->new_config['dl_traffics_overall_groups'] = '';
		}

		if (sizeof($dl_traffics_users_groups) && $cfg_array['dl_traffics_users'] <= 1)
		{
			$this->new_config['dl_traffics_users_groups'] = '';
		}

		$config->set('dl_traffics_overall_groups', $this->new_config['dl_traffics_overall_groups'], false);
		$config->set('dl_traffics_users_groups', $this->new_config['dl_traffics_users_groups'], false);
	}

	$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_CONFIG');

	$cache->destroy('config');

	// Purge the extension cache
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_auth.' . $phpEx);
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_black.' . $phpEx);
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_cat_counts.' . $phpEx);
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_cats.' . $phpEx);
	@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_file_preset.' . $phpEx);

	$message = $language->lang('DL_CONFIG_UPDATED') . '<br />' . $movement_message . '<br />' . $language->lang('CLICK_RETURN_DL_CONFIG', '<a href="' . $basic_link . '&amp;view=' . $view . '">', '</a>') . adm_back_link($this->u_action);
	trigger_error($message);
}

if ($config['dl_traffic_off'])
{
	$error[] = $language->lang('DL_TRAFFIC_OFF_EXPLAIN');
}

$acl_cat_names = array(
	0 => array($language->lang('DL_ACP_CONF_GENERAL'),	'general'),
	1 => array($language->lang('DL_ACP_CONF_VIEW'),		'view'),
	2 => array($language->lang('DL_ACP_CONF_PROTECT'),	'protect'),
	3 => array($language->lang('DL_ACP_CONF_LIMIT'),	'limit'),
	4 => array($language->lang('DL_ACP_CONF_TRAFFIC'),	'traffic'),
	5 => array($language->lang('DL_ACP_CONF_MESSAGE'),	'message'),
	6 => array($language->lang('DL_ACP_CONF_TOPIC'),	'topic'),
	7 => array($language->lang('DL_ACP_CONF_RSS'),		'rss'),
);

$mode_select = '';

for ($i = 0; $i < sizeof($acl_cat_names); $i++)
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

$template->set_filenames(array(
	'config' => 'dl_config_body.html')
);
$template->assign_var('S_DL_CONFIG', true);
$template->assign_display('config');

$user->add_lang('acp/users');

$template->assign_vars(array(
	'L_TITLE'			=> $language->lang('DL_CONFIG'),
	'L_TITLE_PAGE'		=> $language->lang($display_vars['title']),

	'S_ERROR'			=> (sizeof($error)) ? true : false,
	'ERROR_MSG'			=> implode('<br />', $error),
	'S_HIDDEN_FIELDS'	=> (sizeof($s_hidden_fields)) ? build_hidden_fields($s_hidden_fields) : '',
	'S_MODE_SELECT'		=> $mode_select,
	'U_MODE_SELECT'		=> $this->u_action,

	'U_ACTION'			=> $this->u_action . '&amp;view=' . $view)
);

// Output relevant page
foreach ($display_vars['vars'] as $config_key => $vars)
{
	if (!is_array($vars) && (strpos($config_key, 'legend') === false && strpos($config_key, '_legend') === false))
	{
		continue;
	}

	if (strpos($config_key, 'legend') !== false && strpos($config_key, '_legend') === false)
	{
		$template->assign_block_vars('options', array(
			'S_LEGEND'		=> true,
			'LEGEND'		=> ($language->lang($vars)) ? $language->lang($vars) : $vars)
		);

		continue;
	}

	$type = explode(':', $vars['type']);

	$l_explain = '';
	if ($vars['explain'])
	{
		$l_explain = ($language->lang($vars['lang'] . '_EXPLAIN') != $vars['lang'] . '_EXPLAIN') ? $language->lang($vars['lang'] . '_EXPLAIN') : '';
	}

	$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

	if (empty($content))
	{
		continue;
	}

	$help_key = $vars['help_key'];

	$template->assign_block_vars('options', array(
		'KEY'			=> $config_key,
		'TITLE'			=> ($language->lang($vars['lang'])) ? $language->lang($vars['lang']) : $vars['lang'],
		'S_EXPLAIN'		=> $vars['explain'],
		'TITLE_EXPLAIN'	=> $l_explain,
		'CONTENT'		=> $content,
		'HELP_KEY'		=> $help_key,
		)
	);

	unset($display_vars['vars'][$config_key]);
}

/*
* Helpers - Functions to enable custom layout for several options
*/
function mod_disable($value)
{
	global $user;

	$radio_ary = array(1 => 'DL_OFF_NOW', 0 => 'DL_OFF_TIME');

	return h_radio('config[dl_off_now_time]', $radio_ary, $value, 'dl_off_now_time');
}

function rss_perm($value)
{
	global $user;

	$radio_ary = array(1 => 'DL_RSS_USER', 0 => 'DL_RSS_GUESTS');

	return h_radio('config[dl_rss_perms]', $radio_ary, $value, 'dl_rss_perms');
}

function rss_select($value)
{
	global $user;

	$radio_ary = array(1 => 'DL_RSS_SELECT_LAST', 0 => 'DL_RSS_SELECT_RANDOM');

	return h_radio('config[dl_rss_select]', $radio_ary, $value, 'dl_rss_select');
}

function select_dl_cat_edit($value)
{
	global $language;

	$s_select = '<option value="0">' . $language->lang('DL_CAT_EDIT_LINK_0') . '</option>';
	$s_select .= '<option value="1">' . $language->lang('DL_CAT_EDIT_LINK_1') . '</option>';
	$s_select .= '<option value="2">' . $language->lang('DL_CAT_EDIT_LINK_2') . '</option>';
	$s_select .= '<option value="3">' . $language->lang('DL_CAT_EDIT_LINK_3') . '</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_dl_hash_algo($value)
{
	global $user;

	$s_select = '<option value="md5">MD5</option>';
	$s_select .= '<option value="sha1">SHA1</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_dl_forum($dl_topic_forum)
{
	global $language, $config;

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

	$select = '<option value="-1">' . $language->lang('DL_TOPIC_FORUM_C') . '</option><option value="0">' . $language->lang('DEACTIVATE') . '</option>' . $select . '</optgroup>';
	$select = str_replace('value="' . $dl_topic_forum . '">', 'value="' . $dl_topic_forum . '" selected="selected">', $select);

	return $select;
}

function select_dl_vc($value)
{
	global $language;

	$s_select = '<option value="0">' . $language->lang('DL_CAPTCHA_PERM_0') . '</option>';
	$s_select .= '<option value="1">' . $language->lang('DL_CAPTCHA_PERM_1') . '</option>';
	$s_select .= '<option value="2">' . $language->lang('DL_CAPTCHA_PERM_2') . '</option>';
	$s_select .= '<option value="3">' . $language->lang('DL_CAPTCHA_PERM_3') . '</option>';
	$s_select .= '<option value="4">' . $language->lang('DL_CAPTCHA_PERM_4') . '</option>';
	$s_select .= '<option value="5">' . $language->lang('DL_CAPTCHA_PERM_5') . '</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_hotlink_action($value)
{
	global $language;

	$s_select = '<option value="1">' . $language->lang('DL_HOTLINK_ACTION_ONE') . '</option>';
	$s_select .= '<option value="0">' . $language->lang('DL_HOTLINK_ACTION_TWO') . '</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_report_action($value)
{
	global $language;

	$s_select = '<option value="0">' . $language->lang('NO') . '</option>';
	$s_select .= '<option value="1">' . $language->lang('YES') . '</option>';
	$s_select .= '<option value="2">' . $language->lang('DL_OFF_GUESTS') . '</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_report_vc($value)
{
	global $language;

	$s_select = '<option value="0">' . $language->lang('DL_CAPTCHA_PERM_0') . '</option>';
	$s_select .= '<option value="1">' . $language->lang('DL_CAPTCHA_PERM_1') . '</option>';
	$s_select .= '<option value="2">' . $language->lang('DL_CAPTCHA_PERM_2') . '</option>';
	$s_select .= '<option value="3">' . $language->lang('DL_CAPTCHA_PERM_3') . '</option>';
	$s_select .= '<option value="4">' . $language->lang('DL_CAPTCHA_PERM_4') . '</option>';
	$s_select .= '<option value="5">' . $language->lang('DL_CAPTCHA_PERM_5') . '</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_rss_cats($value)
{
	global $language, $config;

	$s_select = '<label><input type="radio" name="config[dl_rss_cats]" id="dl_rss_cats" class="radio" value="0" ' . (($value == 0) ? 'checked="checked"' : '' ) . ' />' . $language->lang('DL_RSS_CATS_ALL') . '</label>&nbsp;';
	$s_select .= '<label><input type="radio" name="config[dl_rss_cats]" class="radio" value="1" ' . (($value == 1) ? 'checked="checked"' : '' ) . ' />' . $language->lang('DL_RSS_CATS_SELECTED') . '</label>&nbsp;';
	$s_select .= '<label><input type="radio" name="config[dl_rss_cats]" class="radio" value="2" ' . (($value == 2) ? 'checked="checked"' : '' ) . ' />' . $language->lang('DL_RSS_CATS_NOT_SELECTED') . '</label>&nbsp;';

	if ($value <> 0)
	{
		$rss_cats = \oxpus\dlext\phpbb\classes\ dl_extra::dl_cat_select(0, 0, array_map('intval', explode(',', $config['dl_rss_cats_select'])));
		$s_select .= '<br /><select name="dl_rss_cats_select[]" id="dl_rss_cats_select" multiple="multiple" size="5">' . $rss_cats . '</select>';
	}

	return $s_select;
}

function select_rss_length($value)
{
	global $language;

	$s_select = '<option value="0">' . $language->lang('DL_RSS_DESC_LENGTH_NONE') . '</option>';
	$s_select .= '<option value="1">' . $language->lang('DL_RSS_DESC_LENGTH_FULL') . '</option>';
	$s_select .= '<option value="2">' . $language->lang('DL_RSS_DESC_LENGTH_SHORT') . '</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_rss_off_action($value)
{
	global $language;

	$s_select = '<option value="0">' . $language->lang('DL_RSS_ACTION_R_DLX') . '</option>';
	$s_select .= '<option value="1">' . $language->lang('DL_RSS_ACTION_R_IDX') . '</option>';
	$s_select .= '<option value="2">' . $language->lang('DL_RSS_ACTION_D_TXT') . '</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_size($value, $field, $size, $maxlength, $quote, $max_quote, $remain = false, $ext_path)
{
	global $language, $config;

	$quota_tmp = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($config[$field], 2, 'select');
	$quota_out = $quota_tmp['size_out'];
	$range_select = $quota_tmp['range'];

	$s_select = '<select name="' . $quote . '" id="' . $quote . '">';
	$s_select .= '<option value="byte">' . $language->lang('DL_BYTES_LONG') . '</option>';
	$s_select .= '<option value="kb">' . $language->lang('DL_KB') . '</option>';
	if ($max_quote == 'mb' || $max_quote == 'gb')
	{
		$s_select .= '<option value="mb">' . $language->lang('DL_MB') . '</option>';
	}
	if ($max_quote == 'gb')
	{
		$s_select .= '<option value="gb">' . $language->lang('DL_GB') . '</option>';
	}
	$s_select .= '</select>';

	$s_select = str_replace('value="' . $range_select . '">', 'value="' . $range_select . '" selected="selected">', $s_select);

	$remain_text_out = '';

	if ($remain)
	{
		switch ($field)
		{
			case 'dl_overall_traffic':
				$remain_traffic_text = $language->lang('DL_REMAIN_OVERALL_TRAFFIC');
				$remain_traffic = $config['dl_overall_traffic'] - $config['dl_remain_traffic'];
				$remain_traffic = ($remain_traffic <= 0) ? 0 : $remain_traffic;

				$remain_traffic_tmp = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($remain_traffic, 2, 'none');
				$remain_traffic_out = $remain_traffic_tmp['size_out'];
				$x_rem = $remain_traffic_tmp['range'];
				$remain_text_out = $remain_traffic_text . $remain_traffic_out . $x_rem;
			break;
			case 'dl_overall_guest_traffic':
				$remain_traffic_text = $language->lang('DL_REMAIN_OVERALL_GUEST_TRAFFIC');
				$remain_traffic = $config['dl_overall_guest_traffic'] - $config['dl_remain_guest_traffic'];
				$remain_traffic = ($remain_traffic <= 0) ? 0 : $remain_traffic;

				$remain_traffic_tmp = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($remain_traffic, 2, 'none');
				$remain_traffic_out = $remain_traffic_tmp['size_out'];
				$x_rem = $remain_traffic_tmp['range'];
				$remain_text_out = $remain_traffic_text . $remain_traffic_out . $x_rem;
			break;
			case 'dl_physical_quota':
				$remain_text_out = $language->lang('DL_PHYSICAL_QUOTA_EXPLAIN', \oxpus\dlext\phpbb\classes\ dl_format::dl_size(\oxpus\dlext\phpbb\classes\ dl_physical::read_dl_sizes(), 2));
			break;
		}

		$remain_text_out = '<br /><span>&nbsp;' . $remain_text_out . '</span>';
	}

	return '<input type="text" size="' . $size . '" maxlength="' . $maxlength . '" name="config[' . $field . ']" id="' . $field . '" value="' . $quota_out . '" class="post" />&nbsp;' . $s_select . '' . $remain_text_out;
}

function select_sort($value)
{
	global $language;

	$s_select = '<option value="1">' . $language->lang('DL_SORT_ACP') . '</option>';
	$s_select .= '<option value="0">' . $language->lang('DL_SORT_USER') . '</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_stat_perm($value)
{
	global $language;

	$s_select = '<option value="0">' . $language->lang('DL_STAT_PERM_ALL') . '</option>';
	$s_select .= '<option value="1">' . $language->lang('DL_STAT_PERM_USER') . '</option>';
	$s_select .= '<option value="2">' . $language->lang('DL_STAT_PERM_MOD') . '</option>';
	$s_select .= '<option value="3">' . $language->lang('DL_STAT_PERM_ADMIN') . '</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_topic_details($value)
{
	global $language;

	$s_select = '<option value="0">' . $language->lang('DL_TOPIC_NO_MORE_DETAILS') . '</option>';
	$s_select .= '<option value="1">' . $language->lang('DL_TOPIC_MORE_DETAILS_UNDER') . '</option>';
	$s_select .= '<option value="2">' . $language->lang('DL_TOPIC_MORE_DETAILS_OVER') . '</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_topic_user($value)
{
	global $language;

	$s_select = '<option value="0">' . $language->lang('DL_TOPIC_USER_SELF') . '</option>';
	$s_select .= '<option value="1">' . $language->lang('DL_TOPIC_USER_OTHER') . '</option>';
	$s_select .= '<option value="2">' . $language->lang('DL_TOPIC_USER_CAT') . '</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_traffic($value, $total_groups)
{
	global $language;

	$s_select = '<option value="1">' . $language->lang('DL_TRAFFICS_ON_ALL') . '</option>';
	if ($total_groups)
	{
		$s_select .= '<option value="2">' . $language->lang('DL_TRAFFICS_ON_GROUPS') . '</option>';
		$s_select .= '<option value="3">' . $language->lang('DL_TRAFFICS_OFF_GROUPS') . '</option>';
	}
	$s_select .= '<option value="0">' . $language->lang('DL_TRAFFICS_OFF_ALL') . '</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_traffic_multi($field, $s_select, $select_size)
{
	return '<select name="' . $field . '[]" id="' . $field . '" multiple="multiple" size="' . $select_size . '">' . $s_select . '</select>';
}

function textarea_input($value, $field, $cols, $rows)
{
	return '<label><textarea cols="' . $cols . '" rows="' . $rows . '" id="' . $field . '" class="inputbox autowidth" name="config[' . $field . ']">' . $value . '</textarea></label>';
}

function select_filebase($value)
{
	$s_select = '<option value="1">/forumroot/ext/oxpus/dlext/files/</option>';
	$s_select .= '<option value="2">/forumroot/store/oxpus/dlext/</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_dl_ext_stats($value)
{
	global $language;

	$s_select = '<option value="0">' . $language->lang('DL_EXT_STATS_0') . '</option>';
	$s_select .= '<option value="1">' . $language->lang('DL_EXT_STATS_1') . '</option>';
	$s_select .= '<option value="2">' . $language->lang('DL_EXT_STATS_2') . '</option>';
	$s_select .= '<option value="3">' . $language->lang('DL_EXT_STATS_3') . '</option>';
	$s_select .= '<option value="4">' . $language->lang('DL_EXT_STATS_4') . '</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_topic_type($value)
{
	global $language;

	$s_select = '<option value="' . POST_NORMAL . '">' . $language->lang('POST_NORMAL') . '</option>';
	$s_select .= '<option value="' . POST_STICKY . '">' . $language->lang('POST_STICKY') . '</option>';
	$s_select .= '<option value="' . POST_ANNOUNCE . '">' . $language->lang('POST_ANNOUNCEMENT') . '</option>';
	$s_select .= '<option value="' . POST_GLOBAL . '">' . $language->lang('POST_GLOBAL') . '</option>';
	$s_select = str_replace('value="' . $value . '">', 'value="' . $value . '" selected="selected">', $s_select);

	return $s_select;
}

function select_dl_user($value, $config)
{
	global $language, $phpbb_root_path, $phpEx;

	$input_field = '<input class="text medium" type="text" id="' . $config . '" name="config[' . $config . ']" value="' . $value . '" />';
	$input_field .= '<br />&nbsp;&nbsp;&nbsp;&nbsp;[ <a href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=acp_dl_config&amp;field=' . $config . '&amp;select_single=true') . '" onclick="find_username(this.href); return false;">' . $language->lang('FIND_USERNAME') . '</a> ]';

	return $input_field;
}
