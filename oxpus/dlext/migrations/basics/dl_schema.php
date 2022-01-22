<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\basics;

class dl_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['dl_active']);
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v320\v320'];
	}

	public function update_data()
	{
		return [
			// Preset the config data
			['config.add', ['dl_active', '1']],
			['config.add', ['dl_antispam_hours', '24']],
			['config.add', ['dl_antispam_posts', '50']],
			['config.add', ['dl_click_reset_time', '0']],
			['config.add', ['dl_delay_auto_traffic', '30']],
			['config.add', ['dl_delay_post_traffic', '30']],
			['config.add', ['dl_diff_topic_user', '0']],
			['config.add', ['dl_disable_email', '1']],
			['config.add', ['dl_disable_popup', '0']],
			['config.add', ['dl_disable_popup_notify', '0']],
			['config.add', ['dl_download_dir', '1']],
			['config.add', ['dl_download_vc', '1']],
			['config.add', ['dl_drop_traffic_postdel', '0']],
			['config.add', ['dl_edit_own_downloads', '1']],
			['config.add', ['dl_edit_time', '3']],
			['config.add', ['dl_enable_dl_topic', '0']],
			['config.add', ['dl_enable_jumpbox', '1']],
			['config.add', ['dl_enable_post_dl_traffic', '1']],
			['config.add', ['dl_enable_rate', '1']],
			['config.add', ['dl_ext_new_window', '0']],
			['config.add', ['dl_file_hash_algo', 'md5']],
			['config.add', ['dl_guest_stats_show', '1']],
			['config.add', ['dl_global_guests', 1]],
			['config.add', ['dl_hotlink_action', '1']],
			['config.add', ['dl_icon_free_for_reg', '0']],
			['config.add', ['dl_index_desc_hide', '0']],
			['config.add', ['dl_limit_desc_on_index', '0']],
			['config.add', ['dl_links_per_page', '10']],
			['config.add', ['dl_mini_stats_ext', '0']],
			['config.add', ['dl_newtopic_traffic', '524288']],
			['config.add', ['dl_new_time', '3']],
			['config.add', ['dl_off_from', '00:00']],
			['config.add', ['dl_off_hide', '1']],
			['config.add', ['dl_off_now_time', '0']],
			['config.add', ['dl_off_till', '23:59']],
			['config.add', ['dl_on_admins', '1']],
			['config.add', ['dl_overall_guest_traffic', '0']],
			['config.add', ['dl_overall_traffic', '104857600']],
			['config.add', ['dl_overview_link_onoff', '1']],
			['config.add', ['dl_physical_quota', '524288000']],
			['config.add', ['dl_posts', '25']],
			['config.add', ['dl_prevent_hotlink', '1']],
			['config.add', ['dl_rate_points', '5']],
			['config.add', ['dl_recent_downloads', '10']],
			['config.add', ['dl_reply_traffic', '262144']],
			['config.add', ['dl_report_broken', '1']],
			['config.add', ['dl_report_broken_lock', '1']],
			['config.add', ['dl_report_broken_message', '1']],
			['config.add', ['dl_report_broken_vc', '1']],
			['config.add', ['dl_rss_cats', '0']],
			['config.add', ['dl_rss_cats_select', '-']],
			['config.add', ['dl_rss_desc_length', '0']],
			['config.add', ['dl_rss_desc_shorten', '150']],
			['config.add', ['dl_rss_enable', '0']],
			['config.add', ['dl_rss_new_update', '0']],
			['config.add', ['dl_rss_number', '10']],
			['config.add', ['dl_rss_off_action', '0']],
			['config.add', ['dl_rss_off_text', 'Dieser Feed ist aktuell offline. / This feed is currently offline.']],
			['config.add', ['dl_rss_perms', '1']],
			['config.add', ['dl_rss_select', '0']],
			['config.add', ['dl_set_add', '0']],
			['config.add', ['dl_set_user', '0']],
			['config.add', ['dl_shorten_extern_links', '10']],
			['config.add', ['dl_show_footer_legend', '1']],
			['config.add', ['dl_show_footer_stat', '1']],
			['config.add', ['dl_show_real_filetime', '1']],
			['config.add', ['dl_similar_dl', '1']],
			['config.add', ['dl_similar_limit', '10']],
			['config.add', ['dl_sort_preform', '0']],
			['config.add', ['dl_stats_perm', '0']],
			['config.add', ['dl_stop_uploads', '0']],
			['config.add', ['dl_thumb_fsize', '0']],
			['config.add', ['dl_thumb_xsize', '200']],
			['config.add', ['dl_thumb_ysize', '150']],
			['config.add', ['dl_todo_link_onoff', '1']],
			['config.add', ['dl_todo_onoff', '1']],
			['config.add', ['dl_topic_forum', '']],
			['config.add', ['dl_topic_more_details', '1']],
			['config.add', ['dl_topic_post_catname', '0']],
			['config.add', ['dl_topic_text', '']],
			['config.add', ['dl_topic_title_catname', '0']],
			['config.add', ['dl_topic_type', POST_NORMAL]],
			['config.add', ['dl_topic_user', '0']],
			['config.add', ['dl_traffics_founder', '1']],
			['config.add', ['dl_traffics_guests', '1']],
			['config.add', ['dl_traffics_overall', '1']],
			['config.add', ['dl_traffics_overall_groups', '']],
			['config.add', ['dl_traffics_users', '1']],
			['config.add', ['dl_traffics_users_groups', '']],
			['config.add', ['dl_traffic_off', '0']],
			['config.add', ['dl_traffic_retime', '0']],
			['config.add', ['dl_uconf_link_onoff', '1']],
			['config.add', ['dl_upload_traffic_count', '1']],
			['config.add', ['dl_user_dl_auto_traffic', '0']],
			['config.add', ['dl_user_traffic_once', '0']],
		];
	}

	public function update_schema()
	{
		return [
			'add_tables'	=> [
				$this->table_prefix . 'dl_auth' => [
					'COLUMNS'		=> [
						'cat_id'	=> ['INT:11', 0],
						'group_id'	=> ['INT:11', 0],
						'auth_view'	=> ['BOOL', 1],
						'auth_dl'	=> ['BOOL', 1],
						'auth_up'	=> ['BOOL', 1],
						'auth_mod'	=> ['BOOL', 0],
					],
				],

				$this->table_prefix . 'dl_banlist' => [
					'COLUMNS'		=> [
						'ban_id'		=> ['UINT:11', null, 'auto_increment'],
						'user_id'		=> ['UINT', 0],
						'user_ip'		=> ['VCHAR:40', ''],
						'user_agent'	=> ['VCHAR:50', ''],
						'username'		=> ['VCHAR:25', ''],
						'guests'		=> ['BOOL', 0],
					],
					'PRIMARY_KEY'	=> 'ban_id'
				],

				$this->table_prefix . 'dl_bug_history' => [
					'COLUMNS'		=> [
						'report_his_id'		=> ['UINT:11', null, 'auto_increment'],
						'df_id'				=> ['INT:11', 0],
						'report_id'			=> ['INT:11', 0],
						'report_his_type'	=> ['CHAR:10', ''],
						'report_his_date'	=> ['TIMESTAMP', 0],
						'report_his_value'	=> ['MTEXT_UNI', ''],
					],
					'PRIMARY_KEY'	=> 'report_his_id'
				],

				$this->table_prefix . 'dl_bug_tracker' => [
					'COLUMNS'		=> [
						'report_id'				=> ['UINT:11', null, 'auto_increment'],
						'df_id'					=> ['INT:11', 0],
						'report_title'			=> ['VCHAR', ''],
						'report_text'			=> ['MTEXT_UNI', ''],
						'report_file_ver'		=> ['VCHAR:50', ''],
						'report_date'			=> ['TIMESTAMP', 0],
						'report_author_id'		=> ['UINT', 0],
						'report_assign_id'		=> ['UINT', 0],
						'report_assign_date'	=> ['TIMESTAMP', 0],
						'report_status'			=> ['BOOL', 0],
						'report_status_date'	=> ['TIMESTAMP', 0],
						'report_php'			=> ['VCHAR:50', ''],
						'report_db'				=> ['VCHAR:50', ''],
						'report_forum'			=> ['VCHAR:50', ''],
						'bug_uid'				=> ['CHAR:8', ''],
						'bug_bitfield'			=> ['VCHAR', ''],
						'bug_flags'				=> ['UINT:11', 0],
					],
					'PRIMARY_KEY'	=> 'report_id'
				],

				$this->table_prefix . 'dl_cat_traf' => [
					'COLUMNS'		=> [
						'cat_id'			=> ['UINT:11', 0],
						'cat_traffic_use'	=> ['BINT', 0],
					],
					'PRIMARY_KEY'	=> 'cat_id'
				],

				$this->table_prefix . 'dl_comments' => [
					'COLUMNS'		=> [
						'dl_id'				=> ['BINT', null, 'auto_increment'],
						'id'				=> ['INT:11', 0],
						'cat_id'			=> ['INT:11', 0],
						'user_id'			=> ['UINT', 0],
						'username'			=> ['VCHAR:32', ''],
						'comment_time'		=> ['TIMESTAMP', 0],
						'comment_edit_time'	=> ['TIMESTAMP', 0],
						'comment_text'		=> ['MTEXT_UNI', ''],
						'approve'			=> ['BOOL', 0],
						'com_uid'			=> ['CHAR:8', ''],
						'com_bitfield'		=> ['VCHAR', ''],
						'com_flags'			=> ['UINT:11', 0],
					],
					'PRIMARY_KEY'	=> 'dl_id'
				],

				$this->table_prefix . 'dl_ext_blacklist' => [
					'COLUMNS'		=> [
						'extention'	=> ['CHAR:10', ''],
					],
				],

				$this->table_prefix . 'dl_favorites' => [
					'COLUMNS'		=> [
						'fav_id'		=> ['UINT:11', null, 'auto_increment'],
						'fav_dl_id'		=> ['INT:11', 0],
						'fav_dl_cat'	=> ['INT:11', 0],
						'fav_user_id'	=> ['UINT', 0],
					],
					'PRIMARY_KEY'	=> 'fav_id'
				],

				$this->table_prefix . 'dl_fields' => [
					'COLUMNS'		=> [
						'field_id'				=> ['UINT:8', null, 'auto_increment'],
						'field_name'			=> ['MTEXT_UNI', ''],
						'field_type'			=> ['INT:4', 0],
						'field_ident'			=> ['VCHAR:20', ''],
						'field_length'			=> ['VCHAR:20', ''],
						'field_minlen'			=> ['VCHAR', ''],
						'field_maxlen'			=> ['VCHAR', ''],
						'field_novalue'			=> ['MTEXT_UNI', ''],
						'field_default_value'	=> ['MTEXT_UNI', ''],
						'field_validation'		=> ['VCHAR:60', ''],
						'field_required'		=> ['BOOL', 0],
						'field_active'			=> ['BOOL', 0],
						'field_order'			=> ['UINT:8', 0],
					],
					'PRIMARY_KEY'	=> 'field_id'
				],

				$this->table_prefix . 'dl_fields_data' => [
					'COLUMNS'		=> [
						'df_id'			=> ['UINT:11', 0],
					],
					'PRIMARY_KEY'	=> 'df_id'
				],

				$this->table_prefix . 'dl_fields_lang' => [
					'COLUMNS'		=> [
						'field_id'		=> ['UINT:8', 0],
						'lang_id'		=> ['UINT:8', 0],
						'option_id'		=> ['UINT:8', 0],
						'field_type'	=> ['INT:4', 0],
						'lang_value'	=> ['MTEXT_UNI', ''],
					],
					'PRIMARY_KEY'	=> ['field_id', 'lang_id', 'option_id'],
				],

				$this->table_prefix . 'dl_hotlink' => [
					'COLUMNS'		=> [
						'user_id'		=> ['UINT', 0],
						'session_id'	=> ['VCHAR:32', ''],
						'hotlink_id'	=> ['VCHAR:32', ''],
						'code'			=> ['VCHAR:10', '-'],
					],
				],

				$this->table_prefix . 'dl_images' => [
					'COLUMNS'		=> [
						'img_id'				=> ['UINT:8', null, 'auto_increment'],
						'dl_id'					=> ['UINT:11', 0],
						'img_name'				=> ['VCHAR:255', ''],
						'img_title'				=> ['MTEXT_UNI', ''],
					],
					'PRIMARY_KEY'	=> 'img_id'
				],

				$this->table_prefix . 'dl_lang' => [
					'COLUMNS'		=> [
						'field_id'				=> ['UINT:8', 0],
						'lang_id'				=> ['UINT:8', 0],
						'lang_name'				=> ['MTEXT_UNI', ''],
						'lang_explain'			=> ['MTEXT_UNI', ''],
						'lang_default_value'	=> ['MTEXT_UNI', ''],
					],
					'PRIMARY_KEY'	=> ['field_id', 'lang_id'],
				],

				$this->table_prefix . 'dl_notraf' => [
					'COLUMNS'		=> [
						'user_id'	=> ['UINT', 0],
						'dl_id'		=> ['INT:11', 0],
					],
				],

				$this->table_prefix . 'dl_ratings' => [
					'COLUMNS'		=> [
						'dl_id'			=> ['INT:11', 0],
						'user_id'		=> ['UINT', 0],
						'rate_point'	=> ['CHAR:10', ''],
					],
				],

				$this->table_prefix . 'dl_rem_traf' => [
					'COLUMNS'		=> [
						'config_name'	=> ['VCHAR', ''],
						'config_value'	=> ['VCHAR', ''],
					],
					'PRIMARY_KEY'	=> 'config_name'
				],

				$this->table_prefix . 'dl_stats' => [
					'COLUMNS'		=> [
						'dl_id'			=> ['BINT', null, 'auto_increment'],
						'id'			=> ['INT:11', 0],
						'cat_id'		=> ['INT:11', 0],
						'user_id'		=> ['UINT', 0],
						'username'		=> ['VCHAR:32', ''],
						'traffic'		=> ['BINT', 0],
						'direction'		=> ['BOOL', 0],
						'user_ip'		=> ['VCHAR:40', ''],
						'browser'		=> ['VCHAR:255', ''],
						'time_stamp'	=> ['INT:11', 0],
					],
					'PRIMARY_KEY'	=> 'dl_id'
				],

				$this->table_prefix . 'dl_versions' => [
					'COLUMNS'		=> [
						'ver_id'			=> ['UINT:11', null, 'auto_increment'],
						'dl_id'				=> ['UINT:11', 0],
						'ver_file_name'		=> ['VCHAR', ''],
						'ver_file_hash'		=> ['VCHAR:255', ''],
						'ver_real_file'		=> ['VCHAR', ''],
						'ver_file_size'		=> ['BINT', 0],
						'ver_version'		=> ['VCHAR:32', ''],
						'ver_change_time'	=> ['TIMESTAMP', 0],
						'ver_add_time'		=> ['TIMESTAMP', 0],
						'ver_add_user'		=> ['UINT', 0],
						'ver_change_user'	=> ['UINT', 0],
						'ver_text'			=> ['MTEXT_UNI', ''],
						'ver_uid'			=> ['CHAR:8', ''],
						'ver_bitfield'		=> ['VCHAR', ''],
						'ver_flags'			=> ['UINT:11', 0],
						'ver_active'		=> ['BOOL', 0],
					],
					'PRIMARY_KEY'	=> 'ver_id'
				],

				$this->table_prefix . 'dl_ver_files' => [
					'COLUMNS'		=> [
						'ver_file_id'	=> ['UINT', null, 'auto_increment'],
						'dl_id'			=> ['INT:11', 0],
						'ver_id'		=> ['INT:11', 0],
						'real_name'		=> ['VCHAR', ''],
						'file_name'		=> ['VCHAR', ''],
						'file_title'	=> ['VCHAR', ''],
						'file_type'		=> ['BOOL', 0],	// 0 = files, 1 = images
					],
					'PRIMARY_KEY'	=> 'ver_file_id'
				],

				$this->table_prefix . 'downloads' => [
					'COLUMNS'		=> [
						'id'					=> ['UINT:11', null, 'auto_increment'],
						'description'			=> ['MTEXT_UNI', ''],
						'file_name'				=> ['VCHAR', ''],
						'klicks'				=> ['INT:11', 0],
						'free'					=> ['BOOL', 0],
						'extern'				=> ['BOOL', 0],
						'long_desc'				=> ['MTEXT_UNI', ''],
						'sort'					=> ['INT:11', 0],
						'cat'					=> ['INT:11', 0],
						'hacklist'				=> ['BOOL', 0],
						'hack_author'			=> ['VCHAR', ''],
						'hack_author_email'		=> ['VCHAR', ''],
						'hack_author_website'	=> ['TEXT_UNI', ''],
						'hack_version'			=> ['VCHAR:32', ''],
						'hack_dl_url'			=> ['TEXT_UNI', ''],
						'test'					=> ['VCHAR:50', ''],
						'req'					=> ['MTEXT_UNI', ''],
						'todo'					=> ['MTEXT_UNI', ''],
						'warning'				=> ['MTEXT_UNI', ''],
						'mod_desc'				=> ['MTEXT_UNI', ''],
						'mod_list'				=> ['BOOL', 0],
						'file_size'				=> ['BINT', 0],
						'change_time'			=> ['TIMESTAMP', 0],
						'rating'				=> ['INT:5', 0],
						'file_traffic'			=> ['BINT', 0],
						'overall_klicks'		=> ['INT:11', 0],
						'approve'				=> ['BOOL', 0],
						'add_time'				=> ['TIMESTAMP', 0],
						'add_user'				=> ['UINT', 0],
						'change_user'			=> ['UINT', 0],
						'last_time'				=> ['TIMESTAMP', 0],
						'down_user'				=> ['UINT', 0],
						'thumbnail'				=> ['VCHAR', ''],
						'broken'				=> ['BOOL', 0],
						'mod_desc_uid'			=> ['CHAR:8', ''],
						'mod_desc_bitfield'		=> ['VCHAR', ''],
						'mod_desc_flags'		=> ['UINT:11', 0],
						'long_desc_uid'			=> ['CHAR:8', ''],
						'long_desc_bitfield'	=> ['VCHAR', ''],
						'long_desc_flags'		=> ['UINT:11', 0],
						'desc_uid'				=> ['CHAR:8', ''],
						'desc_bitfield'			=> ['VCHAR', ''],
						'desc_flags'			=> ['UINT:11', 0],
						'warn_uid'				=> ['CHAR:8', ''],
						'warn_bitfield'			=> ['VCHAR', ''],
						'warn_flags'			=> ['UINT:11', 0],
						'dl_topic'				=> ['UINT:11', 0],
						'real_file'				=> ['VCHAR', ''],
						'todo_uid'				=> ['CHAR:8', ''],
						'todo_bitfield'			=> ['VCHAR', ''],
						'todo_flags'			=> ['UINT:11', 0],
						'file_hash'				=> ['VCHAR:255', ''],
					],
					'PRIMARY_KEY'	=> 'id'
				],

				$this->table_prefix . 'downloads_cat' => [
					'COLUMNS'		=> [
						'id'					=> ['UINT:11', null, 'auto_increment'],
						'parent'				=> ['INT:11', 0],
						'path'					=> ['VCHAR', ''],
						'cat_name'				=> ['VCHAR', ''],
						'sort'					=> ['INT:11', 0],
						'description'			=> ['MTEXT_UNI', ''],
						'rules'					=> ['MTEXT_UNI', ''],
						'auth_view'				=> ['BOOL', 1],
						'auth_dl'				=> ['BOOL', 1],
						'auth_up'				=> ['BOOL', 0],
						'auth_mod'				=> ['BOOL', 0],
						'must_approve'			=> ['BOOL', 0],
						'allow_mod_desc'		=> ['BOOL', 0],
						'statistics'			=> ['BOOL', 1],
						'stats_prune'			=> ['UINT', 0],
						'comments'				=> ['BOOL', 1],
						'cat_traffic'			=> ['BINT', 0],
						'allow_thumbs'			=> ['BOOL', 0],
						'auth_cread'			=> ['BOOL', 0],
						'auth_cpost'			=> ['BOOL', 1],
						'approve_comments'		=> ['BOOL', 1],
						'bug_tracker'			=> ['BOOL', 0],
						'desc_uid'				=> ['CHAR:8', ''],
						'desc_bitfield'			=> ['VCHAR', ''],
						'desc_flags'			=> ['UINT:11', 0],
						'rules_uid'				=> ['CHAR:8', ''],
						'rules_bitfield'		=> ['VCHAR', ''],
						'rules_flags'			=> ['UINT:11', 0],
						'dl_topic_forum'		=> ['INT:11', 0],
						'dl_topic_text'			=> ['MTEXT_UNI', ''],
						'cat_icon'				=> ['VCHAR', ''],
						'diff_topic_user'		=> ['BOOL', 0],
						'topic_user'			=> ['UINT:11', 0],
						'topic_more_details'	=> ['BOOL', 1],
						'dl_topic_type'			=> ['BOOL', POST_NORMAL],
						'show_file_hash'		=> ['BOOL', 1],
						'dl_set_add'			=> ['UINT:11', 0],
						'dl_set_user'			=> ['UINT:11', 0],
					],
					'PRIMARY_KEY'	=> 'id'
				],
			],

			'add_columns'	=> [
				$this->table_prefix . 'groups'		=> [
					'group_dl_auto_traffic'			=> ['BINT', 0],
				],
				$this->table_prefix . 'users'		=> [
					'user_allow_fav_download_email'	=> ['BOOL', 1],
					'user_allow_fav_download_popup'	=> ['BOOL', 1],
					'user_allow_new_download_email'	=> ['BOOL', 0],
					'user_allow_new_download_popup'	=> ['BOOL', 1],
					'user_dl_note_type'				=> ['BOOL', 1],
					'user_dl_sort_dir'				=> ['BOOL', 0],
					'user_dl_sort_fix'				=> ['BOOL', 0],
					'user_dl_sort_opt'				=> ['BOOL', 0],
					'user_dl_sub_on_index'			=> ['BOOL', 1],
					'user_dl_update_time'			=> ['TIMESTAMP', 0],
					'user_new_download'				=> ['BOOL', 0],
					'user_traffic'					=> ['BINT', 0],
					'user_allow_fav_comment_email'	=> ['BOOL', 1],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
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
				$this->table_prefix . 'dl_ver_files',
				$this->table_prefix . 'downloads',
				$this->table_prefix . 'downloads_cat',
			],

			'drop_columns'	=> [
				$this->table_prefix . 'groups' => ['group_dl_auto_traffic'],
				$this->table_prefix . 'users' => [
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
				],
			],
		];
	}
}
