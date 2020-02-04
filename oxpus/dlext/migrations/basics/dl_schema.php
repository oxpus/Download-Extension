<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\basics;

class dl_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['dl_active']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v320\v320');
	}

	public function update_data()
	{
		return array(
			// Preset the config data
			array('config.add', array('dl_active', '1')),
			array('config.add', array('dl_antispam_hours', '24')),
			array('config.add', array('dl_antispam_posts', '50')),
			array('config.add', array('dl_click_reset_time', '0')),
			array('config.add', array('dl_delay_auto_traffic', '30')),
			array('config.add', array('dl_delay_post_traffic', '30')),
			array('config.add', array('dl_diff_topic_user', '0')),
			array('config.add', array('dl_disable_email', '1')),
			array('config.add', array('dl_disable_popup', '0')),
			array('config.add', array('dl_disable_popup_notify', '0')),
			array('config.add', array('dl_download_dir', 'files/downloads/')),
			array('config.add', array('dl_download_vc', '1')),
			array('config.add', array('dl_drop_traffic_postdel', '0')),
			array('config.add', array('dl_edit_own_downloads', '1')),
			array('config.add', array('dl_edit_time', '3')),
			array('config.add', array('dl_enable_dl_topic', '0')),
			array('config.add', array('dl_enable_jumpbox', '1')),
			array('config.add', array('dl_enable_post_dl_traffic', '1')),
			array('config.add', array('dl_enable_rate', '1')),
			array('config.add', array('dl_ext_new_window', '0')),
			array('config.add', array('dl_file_hash_algo', 'md5')),
			array('config.add', array('dl_guest_stats_show', '1')),
			array('config.add', array('dl_hotlink_action', '1')),
			array('config.add', array('dl_icon_free_for_reg', '0')),
			array('config.add', array('dl_latest_comments', '1')),
			array('config.add', array('dl_limit_desc_on_index', '0')),
			array('config.add', array('dl_links_per_page', '10')),
			array('config.add', array('dl_method', '2')),
			array('config.add', array('dl_method_quota', '2097152')),
			array('config.add', array('dl_newtopic_traffic', '524288')),
			array('config.add', array('dl_new_time', '3')),
			array('config.add', array('dl_off_from', '00:00')),
			array('config.add', array('dl_off_hide', '1')),
			array('config.add', array('dl_off_now_time', '0')),
			array('config.add', array('dl_off_till', '23:59')),
			array('config.add', array('dl_on_admins', '1')),
			array('config.add', array('dl_overall_guest_traffic', '0')),
			array('config.add', array('dl_overall_traffic', '104857600')),
			array('config.add', array('dl_overview_link_onoff', '1')),
			array('config.add', array('dl_physical_quota', '524288000')),
			array('config.add', array('dl_posts', '25')),
			array('config.add', array('dl_prevent_hotlink', '1')),
			array('config.add', array('dl_rate_points', '5')),
			array('config.add', array('dl_recent_downloads', '10')),
			array('config.add', array('dl_reply_traffic', '262144')),
			array('config.add', array('dl_report_broken', '1')),
			array('config.add', array('dl_report_broken_lock', '1')),
			array('config.add', array('dl_report_broken_message', '1')),
			array('config.add', array('dl_report_broken_vc', '1')),
			array('config.add', array('dl_rss_cats', '0')),
			array('config.add', array('dl_rss_cats_select', '-')),
			array('config.add', array('dl_rss_desc_length', '0')),
			array('config.add', array('dl_rss_desc_shorten', '150')),
			array('config.add', array('dl_rss_enable', '0')),
			array('config.add', array('dl_rss_new_update', '0')),
			array('config.add', array('dl_rss_number', '10')),
			array('config.add', array('dl_rss_off_action', '0')),
			array('config.add', array('dl_rss_off_text', 'Dieser Feed ist aktuell offline. / This feed is currently offline.')),
			array('config.add', array('dl_rss_perms', '1')),
			array('config.add', array('dl_rss_select', '0')),
			array('config.add', array('dl_shorten_extern_links', '10')),
			array('config.add', array('dl_show_footer_legend', '1')),
			array('config.add', array('dl_show_footer_stat', '1')),
			array('config.add', array('dl_show_real_filetime', '1')),
			array('config.add', array('dl_similar_dl', '1')),
			array('config.add', array('dl_similar_limit', '10')),
			array('config.add', array('dl_sort_preform', '0')),
			array('config.add', array('dl_stats_perm', '0')),
			array('config.add', array('dl_stop_uploads', '0')),
			array('config.add', array('dl_thumb_fsize', '0')),
			array('config.add', array('dl_thumb_xsize', '200')),
			array('config.add', array('dl_thumb_ysize', '150')),
			array('config.add', array('dl_todo_link_onoff', '1')),
			array('config.add', array('dl_todo_onoff', '1')),
			array('config.add', array('dl_topic_forum', '')),
			array('config.add', array('dl_topic_more_details', '1')),
			array('config.add', array('dl_topic_post_catname', '0')),
			array('config.add', array('dl_topic_text', '')),
			array('config.add', array('dl_topic_title_catname', '0')),
			array('config.add', array('dl_topic_user', '0')),
			array('config.add', array('dl_traffics_founder', '1')),
			array('config.add', array('dl_traffics_guests', '1')),
			array('config.add', array('dl_traffics_overall', '1')),
			array('config.add', array('dl_traffics_overall_groups', '')),
			array('config.add', array('dl_traffics_users', '1')),
			array('config.add', array('dl_traffics_users_groups', '')),
			array('config.add', array('dl_traffic_off', '0')),
			array('config.add', array('dl_traffic_retime', '0')),
			array('config.add', array('dl_uconf_link_onoff', '1')),
			array('config.add', array('dl_upload_traffic_count', '1')),
			array('config.add', array('dl_user_dl_auto_traffic', '0')),
			array('config.add', array('dl_user_traffic_once', '0')),
		);
	}

	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'dl_auth' => array(
					'COLUMNS'		=> array(
						'cat_id'	=> array('INT:11', 0),
						'group_id'	=> array('INT:11', 0),
						'auth_view'	=> array('BOOL', 1),
						'auth_dl'	=> array('BOOL', 1),
						'auth_up'	=> array('BOOL', 1),
						'auth_mod'	=> array('BOOL', 0),
					),
				),

				$this->table_prefix . 'dl_banlist' => array(
					'COLUMNS'		=> array(
						'ban_id'		=> array('UINT:11', NULL, 'auto_increment'),
						'user_id'		=> array('UINT', 0),
						'user_ip'		=> array('VCHAR:40', ''),
						'user_agent'	=> array('VCHAR:50', ''),
						'username'		=> array('VCHAR:25', ''),
						'guests'		=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'ban_id'
				),

				$this->table_prefix . 'dl_bug_history' => array(
					'COLUMNS'		=> array(
						'report_his_id'		=> array('UINT:11', NULL, 'auto_increment'),
						'df_id'				=> array('INT:11', 0),
						'report_id'			=> array('INT:11', 0),
						'report_his_type'	=> array('CHAR:10', ''),
						'report_his_date'	=> array('TIMESTAMP', 0),
						'report_his_value'	=> array('MTEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'report_his_id'
				),

				$this->table_prefix . 'dl_bug_tracker' => array(
					'COLUMNS'		=> array(
						'report_id'				=> array('UINT:11', NULL, 'auto_increment'),
						'df_id'					=> array('INT:11', 0),
						'report_title'			=> array('VCHAR', ''),
						'report_text'			=> array('MTEXT_UNI', ''),
						'report_file_ver'		=> array('VCHAR:50', ''),
						'report_date'			=> array('TIMESTAMP', 0),
						'report_author_id'		=> array('UINT', 0),
						'report_assign_id'		=> array('UINT', 0),
						'report_assign_date'	=> array('TIMESTAMP', 0),
						'report_status'			=> array('BOOL', 0),
						'report_status_date'	=> array('TIMESTAMP', 0),
						'report_php'			=> array('VCHAR:50', ''),
						'report_db'				=> array('VCHAR:50', ''),
						'report_forum'			=> array('VCHAR:50', ''),
						'bug_uid'				=> array('CHAR:8', ''),
						'bug_bitfield'			=> array('VCHAR', ''),
						'bug_flags'				=> array('UINT:11', 0),
					),
					'PRIMARY_KEY'	=> 'report_id'
				),

				$this->table_prefix . 'dl_cat_traf' => array(
					'COLUMNS'		=> array(
						'cat_id'			=> array('UINT:11', 0),
						'cat_traffic_use'	=> array('BINT', 0),
					),
					'PRIMARY_KEY'	=> 'cat_id'
				),

				$this->table_prefix . 'dl_comments' => array(
					'COLUMNS'		=> array(
						'dl_id'				=> array('BINT', NULL, 'auto_increment'),
						'id'				=> array('INT:11', 0),
						'cat_id'			=> array('INT:11', 0),
						'user_id'			=> array('UINT', 0),
						'username'			=> array('VCHAR:32', ''),
						'comment_time'		=> array('TIMESTAMP', 0),
						'comment_edit_time'	=> array('TIMESTAMP', 0),
						'comment_text'		=> array('MTEXT_UNI', ''),
						'approve'			=> array('BOOL', 0),
						'com_uid'			=> array('CHAR:8', ''),
						'com_bitfield'		=> array('VCHAR', ''),
						'com_flags'			=> array('UINT:11', 0),
					),
					'PRIMARY_KEY'	=> 'dl_id'
				),

				$this->table_prefix . 'dl_ext_blacklist' => array(
					'COLUMNS'		=> array(
						'extention'	=> array('CHAR:10', ''),
					),
				),

				$this->table_prefix . 'dl_favorites' => array(
					'COLUMNS'		=> array(
						'fav_id'		=> array('UINT:11', NULL, 'auto_increment'),
						'fav_dl_id'		=> array('INT:11', 0),
						'fav_dl_cat'	=> array('INT:11', 0),
						'fav_user_id'	=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'fav_id'
				),

				$this->table_prefix . 'dl_fields' => array(
					'COLUMNS'		=> array(
						'field_id'				=> array('UINT:8', NULL, 'auto_increment'),
						'field_name'			=> array('MTEXT_UNI', ''),
						'field_type'			=> array('INT:4', 0),
						'field_ident'			=> array('VCHAR:20', ''),
						'field_length'			=> array('VCHAR:20', ''),
						'field_minlen'			=> array('VCHAR', ''),
						'field_maxlen'			=> array('VCHAR', ''),
						'field_novalue'			=> array('MTEXT_UNI', ''),
						'field_default_value'	=> array('MTEXT_UNI', ''),
						'field_validation'		=> array('VCHAR:60', ''),
						'field_required'		=> array('BOOL', 0),
						'field_active'			=> array('BOOL', 0),
						'field_order'			=> array('UINT:8', 0),
					),
					'PRIMARY_KEY'	=> 'field_id'
				),

				$this->table_prefix . 'dl_fields_data' => array(
					'COLUMNS'		=> array(
						'df_id'			=> array('UINT:11', 0),
					),
					'PRIMARY_KEY'	=> 'df_id'
				),

				$this->table_prefix . 'dl_fields_lang' => array(
					'COLUMNS'		=> array(
						'field_id'		=> array('UINT:8', 0),
						'lang_id'		=> array('UINT:8', 0),
						'option_id'		=> array('UINT:8', 0),
						'field_type'	=> array('INT:4', 0),
						'lang_value'	=> array('MTEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> array('field_id', 'lang_id', 'option_id'),
				),

				$this->table_prefix . 'dl_hotlink' => array(
					'COLUMNS'		=> array(
						'user_id'		=> array('UINT', 0),
						'session_id'	=> array('VCHAR:32', ''),
						'hotlink_id'	=> array('VCHAR:32', ''),
						'code'			=> array('VCHAR:10', '-'),
					),
				),

				$this->table_prefix . 'dl_images' => array(
					'COLUMNS'		=> array(
						'img_id'				=> array('UINT:8', NULL, 'auto_increment'),
						'dl_id'					=> array('UINT:11', 0),
						'img_name'				=> array('VCHAR:255', ''),
						'img_title'				=> array('MTEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'img_id'
				),

				$this->table_prefix . 'dl_lang' => array(
					'COLUMNS'		=> array(
						'field_id'				=> array('UINT:8', 0),
						'lang_id'				=> array('UINT:8', 0),
						'lang_name'				=> array('MTEXT_UNI', ''),
						'lang_explain'			=> array('MTEXT_UNI', ''),
						'lang_default_value'	=> array('MTEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> array('field_id', 'lang_id'),
				),

				$this->table_prefix . 'dl_notraf' => array(
					'COLUMNS'		=> array(
						'user_id'	=> array('UINT', 0),
						'dl_id'		=> array('INT:11', 0),
					),
				),

				$this->table_prefix . 'dl_ratings' => array(
					'COLUMNS'		=> array(
						'dl_id'			=> array('INT:11', 0),
						'user_id'		=> array('UINT', 0),
						'rate_point'	=> array('CHAR:10', ''),
					),
				),

				$this->table_prefix . 'dl_rem_traf' => array(
					'COLUMNS'		=> array(
						'config_name'	=> array('VCHAR', ''),
						'config_value'	=> array('VCHAR', ''),
					),
					'PRIMARY_KEY'	=> 'config_name'
				),

				$this->table_prefix . 'dl_stats' => array(
					'COLUMNS'		=> array(
						'dl_id'			=> array('BINT', NULL, 'auto_increment'),
						'id'			=> array('INT:11', 0),
						'cat_id'		=> array('INT:11', 0),
						'user_id'		=> array('UINT', 0),
						'username'		=> array('VCHAR:32', ''),
						'traffic'		=> array('BINT', 0),
						'direction'		=> array('BOOL', 0),
						'user_ip'		=> array('VCHAR:40', ''),
						'browser'		=> array('VCHAR:255', ''),
						'time_stamp'	=> array('INT:11', 0),
					),
					'PRIMARY_KEY'	=> 'dl_id'
				),

				$this->table_prefix . 'dl_versions' => array(
					'COLUMNS'		=> array(
						'ver_id'			=> array('UINT:11', NULL, 'auto_increment'),
						'dl_id'				=> array('UINT:11', 0),
						'ver_file_name'		=> array('VCHAR', ''),
						'ver_file_hash'		=> array('VCHAR:255', ''),
						'ver_real_file'		=> array('VCHAR', ''),
						'ver_file_size'		=> array('BINT', 0),
						'ver_version'		=> array('VCHAR:32', ''),
						'ver_change_time'	=> array('TIMESTAMP', 0),
						'ver_add_time'		=> array('TIMESTAMP', 0),
						'ver_add_user'		=> array('UINT', 0),
						'ver_change_user'	=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'ver_id'
				),

				$this->table_prefix . 'downloads' => array(
					'COLUMNS'		=> array(
						'id'					=> array('UINT:11', NULL, 'auto_increment'),
						'description'			=> array('MTEXT_UNI', ''),
						'file_name'				=> array('VCHAR', ''),
						'klicks'				=> array('INT:11', 0),
						'free'					=> array('BOOL', 0),
						'extern'				=> array('BOOL', 0),
						'long_desc'				=> array('MTEXT_UNI', ''),
						'sort'					=> array('INT:11', 0),
						'cat'					=> array('INT:11', 0),
						'hacklist'				=> array('BOOL', 0),
						'hack_author'			=> array('VCHAR', ''),
						'hack_author_email'		=> array('VCHAR', ''),
						'hack_author_website'	=> array('TEXT_UNI', ''),
						'hack_version'			=> array('VCHAR:32', ''),
						'hack_dl_url'			=> array('TEXT_UNI', ''),
						'test'					=> array('VCHAR:50', ''),
						'req'					=> array('MTEXT_UNI', ''),
						'todo'					=> array('MTEXT_UNI', ''),
						'warning'				=> array('MTEXT_UNI', ''),
						'mod_desc'				=> array('MTEXT_UNI', ''),
						'mod_list'				=> array('BOOL', 0),
						'file_size'				=> array('BINT', 0),
						'change_time'			=> array('TIMESTAMP', 0),
						'rating'				=> array('INT:5', 0),
						'file_traffic'			=> array('BINT', 0),
						'overall_klicks'		=> array('INT:11', 0),
						'approve'				=> array('BOOL', 0),
						'add_time'				=> array('TIMESTAMP', 0),
						'add_user'				=> array('UINT', 0),
						'change_user'			=> array('UINT', 0),
						'last_time'				=> array('TIMESTAMP', 0),
						'down_user'				=> array('UINT', 0),
						'thumbnail'				=> array('VCHAR', ''),
						'broken'				=> array('BOOL', 0),
						'mod_desc_uid'			=> array('CHAR:8', ''),
						'mod_desc_bitfield'		=> array('VCHAR', ''),
						'mod_desc_flags'		=> array('UINT:11', 0),
						'long_desc_uid'			=> array('CHAR:8', ''),
						'long_desc_bitfield'	=> array('VCHAR', ''),
						'long_desc_flags'		=> array('UINT:11', 0),
						'desc_uid'				=> array('CHAR:8', ''),
						'desc_bitfield'			=> array('VCHAR', ''),
						'desc_flags'			=> array('UINT:11', 0),
						'warn_uid'				=> array('CHAR:8', ''),
						'warn_bitfield'			=> array('VCHAR', ''),
						'warn_flags'			=> array('UINT:11', 0),
						'dl_topic'				=> array('UINT:11', 0),
						'real_file'				=> array('VCHAR', ''),
						'todo_uid'				=> array('CHAR:8', ''),
						'todo_bitfield'			=> array('VCHAR', ''),
						'todo_flags'			=> array('UINT:11', 0),
						'file_hash'				=> array('VCHAR:255', ''),
					),
					'PRIMARY_KEY'	=> 'id'
				),

				$this->table_prefix . 'downloads_cat' => array(
					'COLUMNS'		=> array(
						'id'					=> array('UINT:11', NULL, 'auto_increment'),
						'parent'				=> array('INT:11', 0),
						'path'					=> array('VCHAR', ''),
						'cat_name'				=> array('VCHAR', ''),
						'sort'					=> array('INT:11', 0),
						'description'			=> array('MTEXT_UNI', ''),
						'rules'					=> array('MTEXT_UNI', ''),
						'auth_view'				=> array('BOOL', 1),
						'auth_dl'				=> array('BOOL', 1),
						'auth_up'				=> array('BOOL', 0),
						'auth_mod'				=> array('BOOL', 0),
						'must_approve'			=> array('BOOL', 0),
						'allow_mod_desc'		=> array('BOOL', 0),
						'statistics'			=> array('BOOL', 1),
						'stats_prune'			=> array('UINT', 0),
						'comments'				=> array('BOOL', 1),
						'cat_traffic'			=> array('BINT', 0),
						'allow_thumbs'			=> array('BOOL', 0),
						'auth_cread'			=> array('BOOL', 0),
						'auth_cpost'			=> array('BOOL', 1),
						'approve_comments'		=> array('BOOL', 1),
						'bug_tracker'			=> array('BOOL', 0),
						'desc_uid'				=> array('CHAR:8', ''),
						'desc_bitfield'			=> array('VCHAR', ''),
						'desc_flags'			=> array('UINT:11', 0),
						'rules_uid'				=> array('CHAR:8', ''),
						'rules_bitfield'		=> array('VCHAR', ''),
						'rules_flags'			=> array('UINT:11', 0),
						'dl_topic_forum'		=> array('INT:11', 0),
						'dl_topic_text'			=> array('MTEXT_UNI', ''),
						'cat_icon'				=> array('VCHAR', ''),
						'diff_topic_user'		=> array('BOOL', 0),
						'topic_user'			=> array('UINT:11', 0),
						'topic_more_details'	=> array('BOOL', 1),
						'show_file_hash'		=> array('BOOL', 1),
					),
					'PRIMARY_KEY'	=> 'id'
				),
			),

			'add_columns'	=> array(
				$this->table_prefix . 'groups'		=> array(
					'group_dl_auto_traffic'			=> array('BINT', 0),
				),
				$this->table_prefix . 'users'		=> array(
					'user_allow_fav_download_email'	=> array('BOOL', 1),
					'user_allow_fav_download_popup'	=> array('BOOL', 1),
					'user_allow_new_download_email'	=> array('BOOL', 0),
					'user_allow_new_download_popup'	=> array('BOOL', 1),
					'user_dl_note_type'				=> array('BOOL', 1),
					'user_dl_sort_dir'				=> array('BOOL', 0),
					'user_dl_sort_fix'				=> array('BOOL', 0),
					'user_dl_sort_opt'				=> array('BOOL', 0),
					'user_dl_sub_on_index'			=> array('BOOL', 1),
					'user_dl_update_time'			=> array('TIMESTAMP', 0),
					'user_new_download'				=> array('BOOL', 0),
					'user_traffic'					=> array('BINT', 0),
					'user_allow_fav_comment_email'	=> array('BOOL', 1),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'dl_auth',
				$this->table_prefix . 'dl_banlist',
				$this->table_prefix . 'dl_bug_history',
				$this->table_prefix . 'dl_bug_tracker',
				$this->table_prefix . 'dl_cat_traf',
				$this->table_prefix . 'dl_comments',
				$this->table_prefix . 'dl_ext_blacklist',
				$this->table_prefix . 'dl_favorites',
				$this->table_prefix . 'dl_fields',
				$this->table_prefix . 'dl_fields_data',
				$this->table_prefix . 'dl_fields_lang',
				$this->table_prefix . 'dl_hotlink',
				$this->table_prefix . 'dl_images',
				$this->table_prefix . 'dl_lang',
				$this->table_prefix . 'dl_notraf',
				$this->table_prefix . 'dl_ratings',
				$this->table_prefix . 'dl_rem_traf',
				$this->table_prefix . 'dl_stats',
				$this->table_prefix . 'dl_versions',
				$this->table_prefix . 'downloads',
				$this->table_prefix . 'downloads_cat',
			),

			'drop_columns'	=> array(
				$this->table_prefix . 'groups' => array('group_dl_auto_traffic'),
				$this->table_prefix . 'users' => array(
					'user_allow_fav_download_email',
					'user_allow_new_download_email',
					'user_allow_new_download_popup',
					'user_dl_note_type',
					'user_dl_sort_dir',
					'user_dl_sort_fix',
					'user_dl_sort_opt',
					'user_dl_sub_on_index',
					'user_dl_update_time',
					'user_new_download',
					'user_traffic',
					'user_allow_fav_comment_email',
				),
			),
		);
	}
}
