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
class acp_cat_edit_controller implements acp_cat_edit_interface
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
		$action				= $this->request->variable('action', '');
		$cancel				= $this->request->variable('cancel', '');
		$cat_traffic_range	= $this->request->variable('cat_traffic_range', '');
		$edit				= $this->request->variable('edit', '');
		$idx_type			= $this->request->variable('type', 'c');
		$path				= $this->request->variable('path', '');
		$save_cat			= $this->request->variable('save_cat', '');
		$submit				= $this->request->variable('submit', '');
		$topic_type			= $this->request->variable('topic_type', POST_NORMAL);
		$cat_id				= $this->request->variable('cat_id', 0);
		$cat_parent			= $this->request->variable('parent', 0);
		$cat_name			= $this->request->variable('cat_name', '', $this->dlext_constants::DL_TRUE);
		$cat_icon			= $this->request->variable('cat_icon', '', $this->dlext_constants::DL_TRUE);
		$description		= $this->request->variable('description', '', $this->dlext_constants::DL_TRUE);
		$rules				= $this->request->variable('rules', '', $this->dlext_constants::DL_TRUE);
		$set_user			= $this->request->variable('set_user', '', $this->dlext_constants::DL_TRUE);
		$topic_text			= $this->request->variable('dl_topic_text', '', $this->dlext_constants::DL_TRUE);
		$topic_user			= $this->request->variable('dl_topic_user', '', $this->dlext_constants::DL_TRUE);
		$diff_topic_user	= $this->request->variable('diff_topic_user', $this->config['dl_diff_topic_user']);
		$allow_mod_desc		= $this->request->variable('allow_mod_desc', 0);
		$allow_thumbs		= $this->request->variable('allow_thumbs', 0);
		$approve_comments	= $this->request->variable('approve_comments', 0);
		$bug_tracker		= $this->request->variable('bug_tracker', 0);
		$cat_traffic		= $this->request->variable('cat_traffic', 0);
		$must_approve		= $this->request->variable('must_approve', 0);
		$set_add			= $this->request->variable('set_add', 0);
		$show_file_hash		= $this->request->variable('show_file_hash', 0);
		$statistics			= $this->request->variable('statistics', 0);
		$stats_prune		= $this->request->variable('stats_prune', 100000);
		$topic_forum		= $this->request->variable('dl_topic_forum', 0);
		$topic_more_details	= $this->request->variable('topic_more_details', 1);
		$comments			= $this->request->variable('comments', 0);
		$perms_copy_from	= $this->request->variable('perms_copy_from', 0);
		$display_thumbs		= $this->request->variable('display_thumbs', 0);
		$max_thumbs			= $this->request->variable('max_thumbs', 1);

		if ($cancel)
		{
			$action = '';
		}
		else
		{
			$add = $this->request->variable('add', '');

			$action = ($add) ? 'add' : $action;
			$action = ($edit) ? 'edit' : $action;
			$action = ($save_cat) ? 'save_cat' : $action;
		}

		$index = $this->dlext_main->full_index();

		if (empty($index) && $action != 'save_cat')
		{
			$action = 'add';
		}

		$error = $this->dlext_constants::DL_FALSE;
		$error_msg = '';

		if (!$path)
		{
			$path = '/';
		}

		$s_hidden_fields = [];

		if ($action == 'save_cat')
		{
			if (!$max_thumbs)
			{
				$max_thumbs = 1;
			}

			$check_tree = $this->dlext_physical->get_file_base_tree(0, $this->dlext_constants::DL_TRUE);

			if (empty($check_tree) || !in_array($path, $check_tree))
			{
				$error = $this->dlext_constants::DL_TRUE;
				$error_msg = $this->language->lang('DL_PATH_NOT_EXIST', $path, $this->dlext_constants->get_value('files_dir', $this->dlext_constants::DL_TRUE) . '/downloads/', $this->dlext_constants->get_value('files_dir', $this->dlext_constants::DL_TRUE) . '/downloads/' . $path);
				$action = ($cat_id) ? 'edit' : 'add';
				$submit = $this->dlext_constants::DL_TRUE;
				$s_hidden_fields += ['cat_id' => $cat_id];
			}
		}

		if ($action == 'edit' || $action == 'add')
		{
			$s_hidden_fields += ['action' => 'save_cat'];

			if ($action == 'edit' && $cat_id)
			{
				$cat_name			= $index[$cat_id]['cat_name_nav'];
				$description		= $index[$cat_id]['description'];
				$rules				= $index[$cat_id]['rules'];
				$cat_path			= $index[$cat_id]['cat_path'];
				$desc_uid			= $index[$cat_id]['desc_uid'];
				$rules_uid			= $index[$cat_id]['rules_uid'];
				$desc_flags			= $index[$cat_id]['desc_flags'];
				$rules_flags		= $index[$cat_id]['rules_flags'];
				$statistics			= $index[$cat_id]['statistics'];
				$stats_prune		= $index[$cat_id]['stats_prune'];
				$comments			= $index[$cat_id]['comments'];
				$must_approve		= $index[$cat_id]['must_approve'];
				$allow_mod_desc		= $index[$cat_id]['allow_mod_desc'];
				$cat_traffic		= $index[$cat_id]['cat_traffic'];
				$cat_remain_traffic	= $index[$cat_id]['cat_traffic'] - $index[$cat_id]['cat_traffic_use'];
				$allow_thumbs		= $index[$cat_id]['allow_thumbs'];
				$approve_comments	= $index[$cat_id]['approve_comments'];
				$bug_tracker		= $index[$cat_id]['bug_tracker'];
				$topic_more_details	= $index[$cat_id]['topic_more_details'];
				$topic_forum		= $index[$cat_id]['dl_topic_forum'];
				$topic_text			= $index[$cat_id]['dl_topic_text'];
				$diff_topic_user	= $index[$cat_id]['diff_topic_user'];
				$topic_user			= $index[$cat_id]['topic_user'];
				$show_file_hash		= $index[$cat_id]['show_file_hash'];
				$cat_icon			= $index[$cat_id]['cat_icon'];
				$topic_type			= $index[$cat_id]['dl_topic_type'];
				$set_add			= $index[$cat_id]['dl_set_add'];
				$set_user			= $index[$cat_id]['dl_set_user'];
				$display_thumbs		= $index[$cat_id]['display_thumbs'];
				$max_thumbs			= $index[$cat_id]['max_thumbs'];

				$s_cat_parent		= $this->dlext_extra->dl_dropdown(0, 0, $index[$cat_id]['parent'], 'auth_view', $cat_id);
				$perms_copy_from	= $this->dlext_extra->dl_dropdown(0, 0, 0, 'auth_view', $cat_id);

				$text_ary		= generate_text_for_edit($description, $desc_uid, $desc_flags);
				$description	= $text_ary['text'];

				$text_ary		= generate_text_for_edit($rules, $rules_uid, $rules_flags);
				$rules			= $text_ary['text'];

				if (!$submit && !isset($s_hidden_fields['cat_id']))
				{
					$s_hidden_fields += ['cat_id' => $cat_id];
				}
			}
			else
			{
				if ($cat_traffic_range == $this->dlext_constants::DL_FILE_RANGE_KBYTE)
				{
					$cat_traffic = $cat_traffic * $this->dlext_constants::DL_FILE_SIZE_KBYTE;
				}
				else if ($cat_traffic_range == $this->dlext_constants::DL_FILE_RANGE_MBYTE)
				{
					$cat_traffic = $cat_traffic * $this->dlext_constants::DL_FILE_SIZE_MBYTE;
				}
				else if ($cat_traffic_range == $this->dlext_constants::DL_FILE_RANGE_GBYTE)
				{
					$cat_traffic = $cat_traffic * $this->dlext_constants::DL_FILE_SIZE_GBYTE;
				}

				$cat_path			= ($path) ? $path : '/';
				$cat_parent_id		= $cat_parent;
				$cat_remain_traffic	= $cat_traffic;

				$s_cat_parent		= $this->dlext_extra->dl_dropdown(0, 0, $cat_parent_id, 'auth_view', $this->dlext_constants::DL_NONE);
				$perms_copy_from	= $this->dlext_extra->dl_dropdown(0, 0, 0, 'auth_view', $cat_id);
			}

			$t_path_select = $this->dlext_physical->get_file_base_tree($cat_path);

			if (!empty($t_path_select) && is_array($t_path_select))
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

			$cat_traffic_out	= 0;
			$cat_remain_traffic	= ($cat_remain_traffic < 0) ? 0 : $cat_remain_traffic;
			$cat_remain_traffic	= $this->dlext_format->dl_size($cat_remain_traffic);

			if ($cat_traffic > $this->dlext_constants::DL_FILE_SIZE_GBYTE)
			{
				$cat_traffic_out	= number_format($cat_traffic / $this->dlext_constants::DL_FILE_SIZE_GBYTE, 2);
				$data_range_select	=  $this->dlext_constants::DL_FILE_RANGE_GBYTE;
			}
			else if ($cat_traffic > $this->dlext_constants::DL_FILE_SIZE_MBYTE)
			{
				$cat_traffic_out	= number_format($cat_traffic / $this->dlext_constants::DL_FILE_SIZE_MBYTE, 2);
				$data_range_select	= $this->dlext_constants::DL_FILE_RANGE_MBYTE;
			}
			else if ($cat_traffic > $this->dlext_constants::DL_FILE_SIZE_KBYTE)
			{
				$cat_traffic_out	= number_format($cat_traffic / $this->dlext_constants::DL_FILE_SIZE_KBYTE, 2);
				$data_range_select	= $this->dlext_constants::DL_FILE_RANGE_KBYTE;
			}
			else
			{
				$data_range_select	= $this->dlext_constants::DL_FILE_RANGE_KBYTE;
			}

			$approve			= ($must_approve) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$allow_mod_desc		= ($allow_mod_desc) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$stats				= ($statistics) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$comments			= ($comments) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$allow_thumbs		= ($allow_thumbs) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$approve_comments	= ($approve_comments) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$bug_tracker		= ($bug_tracker) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$show_file_hash		= ($show_file_hash) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

			$this->language->add_lang('posting');

			if ($this->config['dl_thumb_fsize'])
			{
				$this->template->assign_var('S_DL_THUMBNAILS', $this->dlext_constants::DL_TRUE);

				if ($this->config['dl_thumbs_display_cat'] == $this->dlext_constants::DL_THUMBS_DISPLAY_CAT)
				{
					$this->template->assign_var('S_DL_DISPLAY_THUMBS', $this->dlext_constants::DL_TRUE);
				}
			}

			if ($this->config['dl_topic_forum'] == $this->dlext_constants::DL_NONE)
			{
				$forum_select_tmp = get_forum_list('f_list', $this->dlext_constants::DL_FALSE);

				$counter = 0;

				foreach ($forum_select_tmp as $value)
				{
					switch ($value['forum_type'])
					{
						case FORUM_CAT:
							if ($counter)
							{
								$this->template->assign_block_vars('s_forum_select', [
									'DL_TYPE'	=> 'optend'
								]);
							}

							$this->template->assign_block_vars('s_forum_select', [
								'DL_TYPE'	=> 'optgrp',
								'DL_VALUE'	=> $value['forum_name'],
							]);
							break;
						case FORUM_POST:
							$this->template->assign_block_vars('s_forum_select', [
								'DL_TYPE'	=> 'option',
								'DL_KEY'	=> $value['forum_id'],
								'DL_VALUE'	=> $value['forum_name'],
							]);
							break;
					}

					++$counter;
				}

				$this->template->assign_block_vars('s_forum_select', [
					'DL_TYPE'	=> 'optend'
				]);

				$this->template->assign_vars([
					'S_DL_ENTER_TOPIC_FORUM'	=> $this->dlext_constants::DL_TRUE,
					'S_DL_TOPIC_DETAILS'		=> $this->dlext_constants::DL_TRUE,
				]);
			}
			else
			{
				$topic_forum = '';
				$topic_more_details = '';
			}

			if ($this->config['dl_diff_topic_user'] == $this->dlext_constants::DL_TOPIC_USER_CAT)
			{
				$this->template->assign_var('S_DL_TOPIC_USER_ON', $this->dlext_constants::DL_TRUE);
			}

			if ($this->config['dl_set_add'] == $this->dlext_constants::DL_TOPIC_USER_CAT)
			{
				$this->template->assign_var('S_DL_SET_USER_ON', $this->dlext_constants::DL_TRUE);
			}

			add_form_key('dl_adm_cats');

			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

			$this->template->assign_vars([
				'L_DL_CAT_TRAFFIC'			=> (isset($index[$cat_id]['cat_traffic']) && $index[$cat_id]['cat_traffic'] && isset($cat_remain_traffic) && $cat_remain_traffic) ? $this->language->lang('DL_CAT_TRAFFIC', $cat_remain_traffic) : $this->language->lang('DL_CAT_TRAFFIC_OFF'),
				'L_DL_CAT_TRAFFIC_HELP'		=> htmlentities((isset($index[$cat_id]['cat_traffic']) && $index[$cat_id]['cat_traffic'] && isset($cat_remain_traffic) && $cat_remain_traffic) ? $this->language->lang('DL_CAT_TRAFFIC', $cat_remain_traffic) : $this->language->lang('DL_CAT_TRAFFIC_OFF'), ENT_QUOTES),

				'DL_ERROR_MSG'				=> $error_msg,
				'DL_CATEGORY'				=> (isset($index[$cat_id]['cat_name'])) ? $this->language->lang('DL_PERMISSIONS', $index[$cat_id]['cat_name']) : '',
				'DL_MUST_APPROVE'			=> $approve,
				'DL_ALLOW_MOD_DESC'			=> $allow_mod_desc,
				'DL_STATS'					=> $stats,
				'DL_STATS_PRUNE'			=> $stats_prune,
				'DL_COMMENTS'				=> $comments,
				'DL_CAT_NAME'				=> $cat_name,
				'DL_DESCRIPTION'			=> $description,
				'DL_RULES'					=> $rules,
				'DL_CAT_PARENT'				=> $s_cat_parent,
				'DL_CAT_TRAFFIC'			=> $cat_traffic_out,
				'DL_ALLOW_THUMBS'			=> $allow_thumbs,
				'DL_DISPLAY_THUMBS'			=> $display_thumbs,
				'DL_APPROVE_COMMENTS'		=> $approve_comments,
				'DL_BUG_TRACKER'			=> $bug_tracker,
				'DL_TOPIC_TEXT'				=> $topic_text,
				'DL_CAT_ICON'				=> $cat_icon,
				'DL_TOPIC_USER'				=> $this->dlext_extra->dl_user_switch($topic_user),
				'DL_SHOW_FILE_HASH'			=> $show_file_hash,
				'DL_SET_USER'				=> $this->dlext_extra->dl_user_switch($set_user),
				'DL_MAX_THUMBS'				=> $max_thumbs,

				'DL_PERM_COPY_NONE'			=> $this->dlext_constants::DL_PERM_GENERAL_NONE,
				'DL_PERM_COPY_PARENT'		=> $this->dlext_constants::DL_PERM_GENERAL_ZERO,
				'DL_TOPIC_DEACTIVATED'		=> $this->dlext_constants::DL_PERM_GENERAL_ZERO,

				'S_DL_CAT_MODE'				=> $action,
				'S_DL_TOPIC_TYPE'			=> $topic_type,
				'S_DL_TOPIC_FORUM'			=> $topic_forum,
				'S_DL_CAT_TRAFFIC_RANGE'	=> $data_range_select,
				'S_DL_CATEGORY_ACTION'		=> $this->u_action,
				'S_DL_DIFF_TOPIC_USER'		=> $diff_topic_user,
				'S_DL_SET_USER'				=> $set_add,
				'S_DL_USER_SELECT'			=> append_sid($this->root_path . 'memberlist.' . $this->phpEx, 'mode=searchuser&amp;form=dl_edit_cat&amp;field=set_user&amp;select_single=1'),
				'S_DL_USER_SELECT_2'		=> append_sid($this->root_path . 'memberlist.' . $this->phpEx, 'mode=searchuser&amp;form=dl_edit_cat&amp;field=dl_topic_user&amp;select_single=1'),
				'S_DL_TOPIC_MORE_DETAILS'	=> $topic_more_details,
				'S_DL_ERROR'				=> $error,
				'S_DL_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields),

				'U_DL_BACK'					=> $this->u_action,
			]);

			$s_cat_traffic_range = [];
			$s_cat_traffic_range[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_KBYTE, 'name' => $this->language->lang('DL_KB')];
			$s_cat_traffic_range[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_MBYTE, 'name' => $this->language->lang('DL_MB')];
			$s_cat_traffic_range[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_GBYTE, 'name' => $this->language->lang('DL_GB')];

			for ($i = 0; $i < count($s_cat_traffic_range); ++$i)
			{
				$this->template->assign_block_vars('dl_cat_traffic_range', [
					'DL_VALUE'	=> $s_cat_traffic_range[$i]['value'],
					'DL_NAME'	=> $s_cat_traffic_range[$i]['name'],
				]);
			}

			$s_topic_type = [];
			$s_topic_type[] = ['value' => POST_NORMAL, 		'name' => $this->language->lang('POST_NORMAL')];
			$s_topic_type[] = ['value' => POST_STICKY, 		'name' => $this->language->lang('POST_STICKY')];
			$s_topic_type[] = ['value' => POST_ANNOUNCE,	'name' => $this->language->lang('POST_ANNOUNCEMENT')];
			$s_topic_type[] = ['value' => POST_GLOBAL, 		'name' => $this->language->lang('POST_GLOBAL')];

			for ($i = 0; $i < count($s_topic_type); ++$i)
			{
				$this->template->assign_block_vars('dl_topic_type_select', [
					'DL_VALUE'	=> $s_topic_type[$i]['value'],
					'DL_NAME'	=> $s_topic_type[$i]['name'],
				]);
			}

			$s_set_user_select = [];
			$s_set_user_select[] = ['value' => $this->dlext_constants::DL_TOPIC_USER_SELF,	'name' => $this->language->lang('DL_TOPIC_USER_SELF')];
			$s_set_user_select[] = ['value' => $this->dlext_constants::DL_TOPIC_USER_OTHER,	'name' => $this->language->lang('DL_TOPIC_USER_OTHER')];

			for ($i = 0; $i < count($s_set_user_select); ++$i)
			{
				$this->template->assign_block_vars('dl_user_select', [
					'DL_VALUE'	=> $s_set_user_select[$i]['value'],
					'DL_NAME'	=> $s_set_user_select[$i]['name'],
				]);
			}

			$s_topic_user_select = [];
			$s_topic_user_select[] = ['value' => $this->dlext_constants::DL_TOPIC_USER_SELF,	'name' => $this->language->lang('DL_TOPIC_USER_SELF')];
			$s_topic_user_select[] = ['value' => $this->dlext_constants::DL_TOPIC_USER_OTHER,	'name' => $this->language->lang('DL_TOPIC_USER_OTHER')];

			for ($i = 0; $i < count($s_topic_user_select); ++$i)
			{
				$this->template->assign_block_vars('dl_topic_user_select', [
					'DL_VALUE'	=> $s_topic_user_select[$i]['value'],
					'DL_NAME'	=> $s_topic_user_select[$i]['name'],
				]);
			}

			$s_topic_details_select = [];
			$s_topic_details_select[] = ['value' => $this->dlext_constants::DL_TOPIC_NO_MORE_DETAILS,		'name' => $this->language->lang('DL_TOPIC_NO_MORE_DETAILS')];
			$s_topic_details_select[] = ['value' => $this->dlext_constants::DL_TOPIC_MORE_DETAILS_UNDER,	'name' => $this->language->lang('DL_TOPIC_MORE_DETAILS_UNDER')];
			$s_topic_details_select[] = ['value' => $this->dlext_constants::DL_TOPIC_MORE_DETAILS_OVER,		'name' => $this->language->lang('DL_TOPIC_MORE_DETAILS_OVER')];

			for ($i = 0; $i < count($s_topic_details_select); ++$i)
			{
				$this->template->assign_block_vars('dl_topic_details', [
					'DL_VALUE'	=> $s_topic_details_select[$i]['value'],
					'DL_NAME'	=> $s_topic_details_select[$i]['name'],
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

			if (!empty($perms_copy_from) && is_array($perms_copy_from))
			{
				foreach (array_keys($perms_copy_from) as $key)
				{
					$this->template->assign_block_vars('select_cat_perm_copy', [
						'DL_CAT_ID'			=> $perms_copy_from[$key]['cat_id'],
						'DL_SELECTED'		=> $perms_copy_from[$key]['selected'],
						'DL_SEPERATOR'		=> $perms_copy_from[$key]['seperator'],
						'DL_CAT_NAME'		=> $perms_copy_from[$key]['cat_name'],
					]);
				}
			}
		}
		else if ($action == 'save_cat')
		{
			if (!check_form_key('dl_adm_cats'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			if (strpos(strtolower($cat_icon), 'http'))
			{
				$cat_icon = '';
			}

			$allow_bbcode	= ($this->config['allow_bbcode']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$allow_smilies	= ($this->config['allow_smilies']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$desc_uid		= $desc_bitfield = $rules_uid = $rules_bitfield = '';
			$desc_flags		= $rules_flags = 0;

			if ($description)
			{
				generate_text_for_storage($description, $desc_uid, $desc_bitfield, $desc_flags, $allow_bbcode, $this->dlext_constants::DL_TRUE, $allow_smilies);
			}

			if ($rules)
			{
				generate_text_for_storage($rules, $rules_uid, $rules_bitfield, $rules_flags, $allow_bbcode, $this->dlext_constants::DL_TRUE, $allow_smilies);
			}

			if ($cat_traffic_range == $this->dlext_constants::DL_FILE_RANGE_KBYTE)
			{
				$cat_traffic = $cat_traffic * $this->dlext_constants::DL_FILE_SIZE_KBYTE;
			}
			else if ($cat_traffic_range == $this->dlext_constants::DL_FILE_RANGE_MBYTE)
			{
				$cat_traffic = $cat_traffic * $this->dlext_constants::DL_FILE_SIZE_MBYTE;
			}
			else if ($cat_traffic_range == $this->dlext_constants::DL_FILE_RANGE_GBYTE)
			{
				$cat_traffic = $cat_traffic * $this->dlext_constants::DL_FILE_SIZE_GBYTE;
			}

			// Move files, if the path was changed
			if ($cat_id && $index[$cat_id]['path'] != $path)
			{
				$old_path = $this->dlext_constants->get_value('files_dir') . '/downloads/' . $index[$cat_id]['path'];
				$new_path = $this->dlext_constants->get_value('files_dir') . '/downloads/' . $path;

				$sql = 'SELECT v.ver_real_file, d.real_file FROM ' . $this->dlext_table_downloads . ' d
					LEFT JOIN ' . $this->dlext_table_dl_versions . ' v ON v.dl_id = d.id
					WHERE extern = 0
						AND cat = ' . (int) $cat_id;
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$real_file = $row['real_file'];

					if ($real_file)
					{
						if ($this->filesystem->exists($old_path . $real_file) && strpos($old_path, '//') === false)
						{
							$this->filesystem->rename($old_path . $real_file, $new_path . $real_file);
						}
					}

					$ver_real_file = $row['ver_real_file'];

					if ($ver_real_file)
					{
						if ($this->filesystem->exists($old_path . $ver_real_file) && strpos($old_path, '//') === false)
						{
							$this->filesystem->rename($old_path . $ver_real_file, $new_path . $ver_real_file);
						}
					}
				}

				$this->db->sql_freeresult($result);
			}

			$topic_user = $this->dlext_extra->dl_user_switch(0, $topic_user, $this->dlext_constants::DL_TRUE);
			$set_user = $this->dlext_extra->dl_user_switch(0, $set_user, $this->dlext_constants::DL_TRUE);

			// Check download user-id
			if ($set_add)
			{
				if (!$set_user)
				{
					$set_user = 0;
					$set_add = 0;
				}
				else
				{
					$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $set_user;
					$result = $this->db->sql_query($sql);
					$user_exists = $this->db->sql_affectedrows();
					$this->db->sql_freeresult($result);

					if (!$user_exists)
					{
						$set_user = 0;
						$set_add = 0;
					}
				}
			}
			else
			{
				$set_user = 0;
			}

			// Check topic user-id
			if ($diff_topic_user)
			{
				if (!$topic_user)
				{
					$topic_user = 0;
					$diff_topic_user = 0;
				}
				else
				{
					$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $topic_user;
					$result = $this->db->sql_query($sql);
					$user_exists = $this->db->sql_affectedrows();
					$this->db->sql_freeresult($result);

					if (!$user_exists)
					{
						$topic_user = 0;
						$diff_topic_user = 0;
					}
				}
			}
			else
			{
				$topic_user = 0;
			}

			$sql_cat_data = [
				'allow_mod_desc'		=> $allow_mod_desc,
				'allow_thumbs'			=> $allow_thumbs,
				'approve_comments'		=> $approve_comments,
				'bug_tracker'			=> $bug_tracker,
				'cat_icon'				=> $cat_icon,
				'cat_name'				=> $cat_name,
				'cat_traffic'			=> $cat_traffic,
				'comments'				=> $comments,
				'desc_bitfield'			=> $desc_bitfield,
				'desc_flags'			=> $desc_flags,
				'desc_uid'				=> $desc_uid,
				'description'			=> $description,
				'dl_set_add'			=> $set_add,
				'dl_set_user'			=> $set_user,
				'dl_topic_forum'		=> $topic_forum,
				'dl_topic_text'			=> $topic_text,
				'dl_topic_type'			=> $topic_type,
				'must_approve'			=> $must_approve,
				'parent'				=> $cat_parent,
				'path'					=> $path,
				'rules'					=> $rules,
				'rules_bitfield'		=> $rules_bitfield,
				'rules_flags'			=> $rules_flags,
				'rules_uid'				=> $rules_uid,
				'show_file_hash'		=> $show_file_hash,
				'statistics'			=> $statistics,
				'stats_prune'			=> $stats_prune,
				'topic_more_details'	=> $topic_more_details,
				'topic_user'			=> $topic_user,
				'display_thumbs'		=> $display_thumbs,
				'max_thumbs'			=> $max_thumbs,
			];

			if ($cat_id)
			{
				$sql_cat_data['diff_topic_user'] = $diff_topic_user;

				$sql = 'UPDATE ' . $this->dlext_table_dl_cat . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_cat_data) . ' WHERE id = ' . (int) $cat_id;

				$message = $this->language->lang('DL_CATEGORY_UPDATED');

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_EDIT', false, [$cat_name]);
			}
			else
			{
				$sql = 'INSERT INTO ' . $this->dlext_table_dl_cat . ' ' . $this->db->sql_build_array('INSERT', $sql_cat_data);

				$message = $this->language->lang('DL_CATEGORY_ADDED');

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_ADD', false, [$cat_name]);
			}

			$this->db->sql_query($sql);

			if (!$cat_id)
			{
				$cat_id = $this->db->sql_last_inserted_id();

				$sql = 'INSERT INTO ' . $this->dlext_table_dl_cat_traf . ' ' . $this->db->sql_build_array('INSERT', [
					'cat_id'			=> $cat_id,
					'cat_traffic_use'	=> 0,
				]);

				$this->db->sql_query($sql);
			}

			// Copy permissions if needed
			if ($perms_copy_from !== $this->dlext_constants::DL_NONE)
			{
				$copy_from = ($perms_copy_from === 0) ? $cat_parent : $perms_copy_from;

				if ($copy_from !== 0)
				{
					// At first copy the general permissions for all users
					$sql = 'SELECT cat_name, auth_view, auth_dl, auth_up, auth_mod, auth_cread, auth_cpost FROM ' . $this->dlext_table_dl_cat . '
						WHERE id = ' . (int) $copy_from;
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);

					$auth_view	= $row['auth_view'];
					$auth_dl	= $row['auth_dl'];
					$auth_up	= $row['auth_up'];
					$auth_mod	= $row['auth_mod'];
					$auth_cread	= $row['auth_cread'];
					$auth_cpost	= $row['auth_cpost'];
					$source_cat	= $row['cat_name'];

					$this->db->sql_freeresult($result);

					$sql = 'SELECT cat_name FROM ' . $this->dlext_table_dl_cat . '
						WHERE id = ' . (int) $cat_id;
					$result = $this->db->sql_query($sql);
					$dest_cat = $this->db->sql_fetchfield('cat_name');
					$this->db->sql_freeresult($result);

					$sql = 'UPDATE ' . $this->dlext_table_dl_cat . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'auth_view'		=> $auth_view,
						'auth_dl'		=> $auth_dl,
						'auth_up'		=> $auth_up,
						'auth_mod'		=> $auth_mod,
						'auth_cread'	=> $auth_cread,
						'auth_cpost'	=> $auth_cpost
					]) . ' WHERE id = ' . (int) $cat_id;
					$this->db->sql_query($sql);

					// And now copy all permissions for usergroups
					$sql = 'DELETE FROM ' . $this->dlext_table_dl_auth . '
						WHERE cat_id = ' . (int) $cat_id;
					$this->db->sql_query($sql);

					$sql = 'SELECT * FROM ' . $this->dlext_table_dl_auth . '
						WHERE cat_id = ' . (int) $copy_from;
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						$group_id	= $row['group_id'];
						$auth_view	= $row['auth_view'];
						$auth_dl	= $row['auth_dl'];
						$auth_up	= $row['auth_up'];
						$auth_mod	= $row['auth_mod'];

						$sql = 'INSERT INTO ' . $this->dlext_table_dl_auth . ' ' . $this->db->sql_build_array('INSERT', [
							'cat_id'	=> $cat_id,
							'group_id'	=> $group_id,
							'auth_view'	=> $auth_view,
							'auth_dl'	=> $auth_dl,
							'auth_up'	=> $auth_up,
							'auth_mod'	=> $auth_mod
						]);
						$this->db->sql_query($sql);
					}

					$this->db->sql_freeresult($result);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_PERM_COPY', false, [$source_cat, $dest_cat]);
				}
			}

			// Purge the categories cache
			$this->cache->destroy('_dlext_cats');
			$this->cache->destroy('_dlext_auth');

			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

			$message .= adm_back_link($this->u_action);

			trigger_error($message);
		}
	}
}
