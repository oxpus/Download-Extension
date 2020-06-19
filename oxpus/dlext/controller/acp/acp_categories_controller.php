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
class acp_categories_controller implements acp_categories_interface
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
	public $phpbb_dispatcher;

	public $config;
	public $helper;
	public $language;
	public $request;
	public $template;

	public $ext_path;
	public $ext_path_web;
	public $ext_path_ajax;

	protected $dlext_extra;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_nav;
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
	 * @param \phpbb\event\dispatcher_interface		$phpbb_dispatcher
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
		\phpbb\event\dispatcher_interface $phpbb_dispatcher,
		$dlext_extra,
		$dlext_format,
		$dlext_main,
		$dlext_nav,
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
		$this->phpbb_dispatcher			= $phpbb_dispatcher;

		$this->config					= $this->phpbb_container->get('config');
		$this->helper					= $this->phpbb_container->get('controller.helper');
		$this->language					= $this->phpbb_container->get('language');
		$this->request					= $this->phpbb_container->get('request');
		$this->template					= $this->phpbb_container->get('template');

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_extra				= $dlext_extra;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_nav				= $dlext_nav;
		$this->dlext_physical			= $dlext_physical;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$this->auth->acl($this->user->data);
		if (!$this->auth->acl_get('a_dl_categories'))
		{
			trigger_error('DL_NO_PERMISSION', E_USER_WARNING);
		}

		// Define the ext path
		$ext_path	= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);

		include_once($this->ext_path . 'phpbb/includes/acm_init.' . $this->phpEx);

		$notification = $this->phpbb_container->get('notification_manager');

		if ($cancel)
		{
			$action = '';
		}
		else
		{
			$action = ($add) ? 'add' : $action;
			$action = ($edit) ? 'edit' : $action;
			$action = ($move) ? 'category_order' : $action;
			$action = ($save_cat) ? 'save_cat' : $action;
		}
		
		$index = [];
		$index = $this->dlext_main->full_index();

		if (empty($index) && $action != 'save_cat')
		{
			$action = 'add';
		}

		if ($cat_id)
		{
			$log_cat_name = $index[$cat_id]['cat_name'];
		}

		$error = false;
		$error_msg = '';

		if (!$path)
		{
			$path = '/';
		}

		$s_hidden_fields = [];

		if ($action == 'save_cat' && $path && !@file_exists(DL_EXT_FILEBASE_PATH. 'downloads/' . $path) || substr($path, -1, 1) <> '/')
		{
			$error = true;
			$error_msg = $this->language->lang('DL_PATH_NOT_EXIST', $path, DL_EXT_FILEBASE_PATH. 'downloads/', DL_EXT_FILEBASE_PATH. 'downloads/' . $path);
			$action = ($cat_id) ? 'edit' : 'add';
			$submit = true;
			$s_hidden_fields += ['cat_id' => $cat_id];
		}

		if ($action == 'edit' || $action == 'add')
		{
			$s_hidden_fields += ['action' => 'save_cat'];
		
			if($action == 'edit' && $cat_id && !$submit)
			{
				$cat_name			= $index[$cat_id]['cat_name'];
				$cat_name			= str_replace('&nbsp;&nbsp;|___&nbsp;', '', $cat_name);
				$description		= $index[$cat_id]['description'];
				$rules				= $index[$cat_id]['rules'];
				$cat_path			= $index[$cat_id]['cat_path'];
				$s_cat_parent		= '<select name="parent">';
				$s_cat_parent		.= '<option value="0">&nbsp;»&nbsp;'.$this->language->lang('DL_CAT_INDEX').'</option>';
				$s_cat_parent		.= $this->dlext_extra->dl_dropdown(0, 0, $index[$cat_id]['parent'], 'auth_view', $cat_id);
				$s_cat_parent		.= '</select>';
				$desc_uid			= $index[$cat_id]['desc_uid'];
				$rules_uid			= $index[$cat_id]['rules_uid'];
				$desc_bitfield		= $index[$cat_id]['desc_bitfield'];
				$rules_bitfield		= $index[$cat_id]['rules_bitfield'];
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
		
				$perms_copy_from	= '<select name="perms_copy_from">';
				$perms_copy_from	.= '<option value="-1">&nbsp;»&nbsp;'.$this->language->lang('DL_NO_PERMS_COPY').'</option>';
				$perms_copy_from	.= '<option value="0">&nbsp;»&nbsp;'.$this->language->lang('DL_CAT_PARENT').'</option>';
				$perms_copy_from	.= $this->dlext_extra->dl_dropdown(0, 0, $index[$cat_id]['parent'], 'auth_view', $cat_id);
				$perms_copy_from	.= '</select>';
		
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
				if ($cat_traffic_range == 'KB')
				{
					$cat_traffic = $cat_traffic * 1024;
				}
				else if ($cat_traffic_range == 'MB')
				{
					$cat_traffic = $cat_traffic * 1048576;
				}
				else if ($cat_traffic_range == 'GB')
				{
					$cat_traffic = $cat_traffic * 1073741824;
				}
		
				$cat_path			= ($path) ? $path : '/';
				$cat_parent_id		= $cat_parent;
				$s_cat_parent		= '<select name="parent">';
				$s_cat_parent		.= '<option value="0">&nbsp;»&nbsp;'.$this->language->lang('DL_CAT_INDEX').'</option>';
				$s_cat_parent		.= $this->dlext_extra->dl_dropdown(0, 0, $cat_parent_id, 'auth_view', -1);
				$s_cat_parent		.= '</select>';
				$cat_remain_traffic	= $cat_traffic;
				$perm_cat_id		= $perms_copy_from;
				$perms_copy_from	= '<select name="perms_copy_from">';
				$perms_copy_from	.= '<option value="0">&nbsp;»&nbsp;'.$this->language->lang('DL_CAT_PARENT').'</option>';
				$perms_copy_from	.= $this->dlext_extra->dl_dropdown(0, 0, $perm_cat_id, 'auth_view', -1);
				$perms_copy_from	.= '</select>';
			}
		
			$t_path_select = $this->dlext_physical->get_file_base_tree(DL_EXT_FILEBASE_PATH. 'downloads/', $cat_path);
			$s_path_select = '<select name="path">';
			$s_path_select .= '<option value="/">' . $this->language->lang('DL_CAT_PATH_SELECT') . '</option>';
		
			sort($t_path_select);
			foreach ($t_path_select as $key => $value)
			{
				$tree_data = $t_path_select[$key];
				$s_path_select .= '<option value="' . $tree_data['cat_path'] . '"' . $tree_data['selected'] . '>' . $tree_data['entry'] . '</option>';
			}
			$s_path_select .= '</select>';
			$s_path_select = str_replace('value="' . $cat_path . '">', 'value="' . $cat_path . '" selected="selected">', $s_path_select);
		
			$s_topic_user_select = '<select name="diff_topic_user">';
			$s_topic_user_select .= '<option value="0">' . $this->language->lang('DL_TOPIC_USER_SELF') . '</option>';
			$s_topic_user_select .= '<option value="1">' . $this->language->lang('DL_TOPIC_USER_OTHER') . '</option>';
			$s_topic_user_select .= '</select>';
			$s_topic_user_select = str_replace('value="' . $diff_topic_user . '">', 'value="' . $diff_topic_user . '" selected="selected">', $s_topic_user_select);
		
			$s_set_user_select = '<select name="set_add">';
			$s_set_user_select .= '<option value="0">' . $this->language->lang('DL_TOPIC_USER_SELF') . '</option>';
			$s_set_user_select .= '<option value="1">' . $this->language->lang('DL_TOPIC_USER_OTHER') . '</option>';
			$s_set_user_select .= '</select>';
			$s_set_user_select = str_replace('value="' . $set_add . '">', 'value="' . $set_add . '" selected="selected">', $s_set_user_select);
		
			$cat_traffic_out	= 0;
			$cat_remain_traffic	= ($cat_remain_traffic < 0) ? 0 : $cat_remain_traffic;
			$cat_remain_traffic	= $this->dlext_format->dl_size($cat_remain_traffic);
		
			$s_select_datasize	= '<option value="KB">' . $this->language->lang('DL_KB') . '</option>';
			$s_select_datasize	.= '<option value="MB">' . $this->language->lang('DL_MB') . '</option>';
			$s_select_datasize	.= '<option value="GB">' . $this->language->lang('DL_GB') . '</option>';
			$s_select_datasize	.= '</select>';
		
			if ($cat_traffic > 1073741823)
			{
				$cat_traffic_out	= number_format($cat_traffic / 1073741824, 2);
				$data_range_select	= 'GB';
			}
			else if ($cat_traffic > 1048575)
			{
				$cat_traffic_out	= number_format($cat_traffic / 1048576, 2);
				$data_range_select	= 'MB';
			}
			else if ($cat_traffic > 1023)
			{
				$cat_traffic_out	= number_format($cat_traffic / 1024, 2);
				$data_range_select	= 'KB';
			}
			else
			{
				$data_range_select	= 'KB';
			}
		
			$cat_traffic_range	= str_replace('value="' . $data_range_select . '">', 'value="' . $data_range_select . '" selected="selected">', $s_select_datasize);
			$cat_traffic_range	= '<select name="cat_traffic_range">' . $cat_traffic_range;
		
			$approve_yes	= ($must_approve) ? 'checked="checked"' : '';
			$approve_no		= (!$must_approve) ? 'checked="checked"' : '';
		
			$allow_mod_desc_yes	= ($allow_mod_desc) ? 'checked="checked"' : '';
			$allow_mod_desc_no	= (!$allow_mod_desc) ? 'checked="checked"' : '';
		
			$stats_yes	= ($statistics) ? 'checked="checked"' : '';
			$stats_no	= (!$statistics) ? 'checked="checked"' : '';
		
			$comments_yes	= ($comments) ? 'checked="checked"' : '';
			$comments_no	= (!$comments) ? 'checked="checked"' : '';
		
			$allow_thumbs_yes	= ($allow_thumbs) ? 'checked="checked"' : '';
			$allow_thumbs_no	= (!$allow_thumbs) ? 'checked="checked"' : '';
		
			$approve_comments_yes	= ($approve_comments) ? 'checked="checked"' : '';
			$approve_comments_no	= (!$approve_comments) ? 'checked="checked"' : '';
		
			$bug_tracker_yes	= ($bug_tracker) ? 'checked="checked"' : '';
			$bug_tracker_no		= (!$bug_tracker) ? 'checked="checked"' : '';
		
			$show_file_hash_yes	= ($show_file_hash) ? 'checked="checked"' : '';
			$show_file_hash_no	= (!$show_file_hash) ? 'checked="checked"' : '';
		
			$this->language->add_lang('posting');
		
			$s_topic_type = '<select name="topic_type">';
			$s_topic_type .= '<option value="' . POST_NORMAL . '">' . $this->language->lang('POST_NORMAL') . '</option>';
			$s_topic_type .= '<option value="' . POST_STICKY . '">' . $this->language->lang('POST_STICKY') . '</option>';
			$s_topic_type .= '<option value="' . POST_ANNOUNCE . '">' . $this->language->lang('POST_ANNOUNCEMENT') . '</option>';
			$s_topic_type .= '<option value="' . POST_GLOBAL . '">' . $this->language->lang('POST_GLOBAL') . '</option>';
			$s_topic_type .= '</select>';
			$s_topic_type = str_replace('value="' . $topic_type . '">', 'value="' . $topic_type . '" selected="selected">', $s_topic_type);
		
			if ($this->config['dl_thumb_fsize'])
			{
				$this->template->assign_var('S_THUMNAILS', true);
			}
		
			if ($this->config['dl_topic_forum'] == -1)
			{
				$this->template->assign_var('S_ENTER_TOPIC_FORUM', true);
		
				$forum_select_tmp = get_forum_list('f_list', false);
				$s_forum_select = '';
		
				foreach ($forum_select_tmp as $key => $value)
				{
					switch ($value['forum_type'])
					{
						case FORUM_CAT:
							if ($s_forum_select)
							{
								$s_forum_select .= '</optgroup>';
							}
							$s_forum_select .= '<optgroup label="' . $value['forum_name'] . '">';
						break;
						case FORUM_POST:
							$s_forum_select .= '<option value="' . $value['forum_id'] . '">' . $value['forum_name'] . '</option>';
						break;
					}
				}
		
				$s_forum_select = '<select name="dl_topic_forum"><option value="0">' . $this->language->lang('DEACTIVATE') . '</option>' . $s_forum_select . '</optgroup></select>';
				$s_forum_select = str_replace('value="' . $topic_forum . '">', 'value="' . $topic_forum . '" selected="selected">', $s_forum_select);
		
				$this->template->assign_var('S_TOPIC_DETAILS', true);
		
				$s_topic_more_details = '<select name="topic_more_details">';
				$s_topic_more_details .= '<option value="0">' . $this->language->lang('DL_TOPIC_NO_MORE_DETAILS') . '</option>';
				$s_topic_more_details .= '<option value="1">' . $this->language->lang('DL_TOPIC_MORE_DETAILS_UNDER') . '</option>';
				$s_topic_more_details .= '<option value="2">' . $this->language->lang('DL_TOPIC_MORE_DETAILS_OVER') . '</option>';
				$s_topic_more_details .= '</select>';
				$s_topic_more_details = str_replace('value="' . $topic_more_details . '">', 'value="' . $topic_more_details . '" selected="selected">', $s_topic_more_details);
			}
			else
			{
				$s_forum_select = '';
				$s_topic_more_details = '';
			}
		
			if ($this->config['dl_diff_topic_user'] == 2)
			{
				$this->template->assign_var('S_TOPIC_USER_ON', true);
			}
		
			if ($this->config['dl_set_add'] == 2)
			{
				$this->template->assign_var('S_SET_USER_ON', true);
			}
		
			add_form_key('dl_adm_cats');
		
			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

			$this->template->assign_vars([
				'L_DL_CAT_MODE'					=> ($action == 'edit') ? $this->language->lang('EDIT') : $this->language->lang('ADD'),
				'L_DL_CAT_TRAFFIC'				=> (isset($index[$cat_id]['cat_traffic']) && $index[$cat_id]['cat_traffic'] && isset($cat_remain_traffic) && $cat_remain_traffic) ? $this->language->lang('DL_CAT_TRAFFIC', $cat_remain_traffic) : $this->language->lang('DL_CAT_TRAFFIC_OFF'),
				'L_DL_CAT_TRAFFIC_HELP'			=> htmlentities((isset($index[$cat_id]['cat_traffic']) && $index[$cat_id]['cat_traffic'] && isset($cat_remain_traffic) && $cat_remain_traffic) ? $this->language->lang('DL_CAT_TRAFFIC', $cat_remain_traffic) : $this->language->lang('DL_CAT_TRAFFIC_OFF')),
		
				'ERROR_MSG'				=> $error_msg,
				'CATEGORY'				=> (isset($index[$cat_id]['cat_name'])) ? $this->language->lang('DL_PERMISSIONS', $index[$cat_id]['cat_name']) : '',
				'MUST_APPROVE_YES'		=> $approve_yes,
				'MUST_APPROVE_NO'		=> $approve_no,
				'ALLOW_MOD_DESC_YES'	=> $allow_mod_desc_yes,
				'ALLOW_MOD_DESC_NO'		=> $allow_mod_desc_no,
				'STATS_YES'				=> $stats_yes,
				'STATS_NO'				=> $stats_no,
				'STATS_PRUNE'			=> $stats_prune,
				'COMMENTS_YES'			=> $comments_yes,
				'COMMENTS_NO'			=> $comments_no,
				'CAT_NAME'				=> $cat_name,
				'DESCRIPTION'			=> $description,
				'RULES'					=> $rules,
				'CAT_PARENT'			=> $s_cat_parent,
				'CAT_TRAFFIC'			=> $cat_traffic_out,
				'ALLOW_THUMBS_YES'		=> $allow_thumbs_yes,
				'ALLOW_THUMBS_NO'		=> $allow_thumbs_no,
				'APPROVE_COMMENTS_YES'	=> $approve_comments_yes,
				'APPROVE_COMMENTS_NO'	=> $approve_comments_no,
				'BUG_TRACKER_YES'		=> $bug_tracker_yes,
				'BUG_TRACKER_NO'		=> $bug_tracker_no,
				'PERMS_COPY_FROM'		=> $perms_copy_from,
				'TOPIC_TEXT'			=> $topic_text,
				'CAT_ICON'				=> $cat_icon,
				'TOPIC_USER'			=> $this->dlext_extra->dl_user_switch($topic_user),
				'SHOW_FILE_HASH_YES'	=> $show_file_hash_yes,
				'SHOW_FILE_HASH_NO'		=> $show_file_hash_no,
				'SET_USER'				=> $this->dlext_extra->dl_user_switch($set_user),
		
				'S_TOPIC_TYPE'			=> $s_topic_type,
				'S_CAT_PATH'			=> $s_path_select,
				'S_DL_TOPIC_FORUM'		=> $s_forum_select,
				'S_CAT_TRAFFIC_RANGE'	=> $cat_traffic_range,
				'S_CATEGORY_ACTION'		=> $this->u_action,
				'S_DL_DIFF_TOPIC_USER'	=> $s_topic_user_select,
				'S_SET_USER'			=> $s_set_user_select,
				'S_USER_SELECT'			=> append_sid("{$this->root_path}memberlist.$this->phpEx", 'mode=searchuser&amp;form=dl_edit_cat&amp;field=set_user&amp;select_single=true'),
				'S_USER_SELECT_2'		=> append_sid("{$this->root_path}memberlist.$this->phpEx", 'mode=searchuser&amp;form=dl_edit_cat&amp;field=dl_topic_user&amp;select_single=true'),
				'S_TOPIC_MORE_DETAILS'	=> $s_topic_more_details,
				'S_ERROR'				=> $error,
				'S_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields),
		
				'U_BACK'				=> $this->u_action,
			]);
		}
		else if($action == 'save_cat')
		{
			if (!check_form_key('dl_adm_cats'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}
		
			if (strpos(strtolower($cat_icon), "http"))
			{
				$cat_icon = '';
			}
		
			$allow_bbcode	= ($this->config['allow_bbcode']) ? true : false;
			$allow_urls		= true;
			$allow_smilies	= ($this->config['allow_smilies']) ? true : false;
			$desc_uid		= $desc_bitfield = $rules_uid = $rules_bitfield = '';
			$desc_flags		= $rules_flags = 0;
	
			if ($description)
			{
				generate_text_for_storage($description, $desc_uid, $desc_bitfield, $desc_flags, $allow_bbcode, true, $allow_smilies);
			}
		
			if ($rules)
			{
				generate_text_for_storage($rules, $rules_uid, $rules_bitfield, $rules_flags, $allow_bbcode, true, $allow_smilies);
			}
		
			if ($cat_traffic_range == 'KB')
			{
				$cat_traffic = $cat_traffic * 1024;
			}
			else if ($cat_traffic_range == 'MB')
			{
				$cat_traffic = $cat_traffic * 1048576;
			}
			else if ($cat_traffic_range == 'GB')
			{
				$cat_traffic = $cat_traffic * 1073741824;
			}
		
			// Move files, if the path was changed
			if ($cat_id && $index[$cat_id]['path'] != $path)
			{
				$old_path = DL_EXT_FILEBASE_PATH. 'downloads/' . $index[$cat_id]['path'];
				$new_path = DL_EXT_FILEBASE_PATH. 'downloads/' . $path;
		
				$move_mode = (@ini_get('open_basedir') || @ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'on') ? 'move' : 'copy';
		
				$sql = 'SELECT v.ver_real_file, d.real_file FROM ' . DOWNLOADS_TABLE . ' d
					LEFT JOIN ' . DL_VERSIONS_TABLE . ' v ON v.dl_id = d.id
					WHERE extern = 0
						AND cat = ' . (int) $cat_id;
				$result = $this->db->sql_query($sql);
		
				while ($row = $this->db->sql_fetchrow($result))
				{
					$real_file = $row['real_file'];
		
					if ($real_file)
					{
						if (@file_exists($old_path . $real_file) && strpos($old_path, '//') === false)
						{
							$move_mode($old_path . $real_file, $new_path . $real_file);
							@unlink($old_path . $real_file);
							phpbb_chmod($new_path . $real_file, CHMOD_ALL);
						}
					}
		
					$ver_real_file = $row['ver_real_file'];
		
					if ($ver_real_file)
					{
						if (@file_exists($old_path . $ver_real_file) && strpos($old_path, '//') === false)
						{
							$move_mode($old_path . $ver_real_file, $new_path . $ver_real_file);
							@unlink($old_path . $ver_real_file);
							phpbb_chmod($new_path . $ver_real_file, CHMOD_ALL);
						}
					}
				}
		
				$this->db->sql_freeresult($result);
			}
		
			$topic_user = $this->dlext_extra->dl_user_switch(0, $topic_user, true);
			$set_user = $this->dlext_extra->dl_user_switch(0, $set_user, true);
		
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
					$user_exists = $this->db->sql_affectedrows($result);
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
					$user_exists = $this->db->sql_affectedrows($result);
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
			];

			if($cat_id)
			{
				$sql_cat_data['diff_topic_user'] = $diff_topic_user;

				$sql = 'UPDATE ' . DL_CAT_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_cat_data) . ' WHERE id = ' . (int) $cat_id;

				$message = $this->language->lang('DL_CATEGORY_UPDATED');
		
				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_EDIT', false, [$cat_name]);
			}
			else
			{
				$sql = 'INSERT INTO ' . DL_CAT_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_cat_data);
		
				$message = $this->language->lang('DL_CATEGORY_ADDED');
		
				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_ADD', false, [$cat_name]);
			}

			$this->db->sql_query($sql);
		
			if (!$cat_id)
			{
				$cat_id = $this->db->sql_nextid();
		
				$sql = 'INSERT INTO ' . DL_CAT_TRAF_TABLE . ' ' . $this->db->sql_build_array('INSERT', [
					'cat_id'			=> $cat_id,
					'cat_traffic_use'	=> 0,
				]);
		
				$this->db->sql_query($sql);
			}
		
			// Copy permissions if needed
			if ($perms_copy_from !== -1)
			{
				$copy_from = ($perms_copy_from === 0) ? $cat_parent : $perms_copy_from;
		
				if ($copy_from !== 0)
				{
					// At first copy the general permissions for all users
					$sql = 'SELECT cat_name, auth_view, auth_dl, auth_up, auth_mod, auth_cread, auth_cpost FROM ' . DL_CAT_TABLE . '
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
		
					$sql = 'SELECT cat_name FROM ' . DL_CAT_TABLE . '
						WHERE id = ' . (int) $cat_id;
					$result = $this->db->sql_query($sql);
					$dest_cat = $this->db->sql_fetchfield('cat_name');
					$this->db->sql_freeresult($result);
		
					$sql = 'UPDATE ' . DL_CAT_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'auth_view'		=> $auth_view,
						'auth_dl'		=> $auth_dl,
						'auth_up'		=> $auth_up,
						'auth_mod'		=> $auth_mod,
						'auth_cread'	=> $auth_cread,
						'auth_cpost'	=> $auth_cpost]) . ' WHERE id = ' . (int) $cat_id;
					$this->db->sql_query($sql);
		
					// And now copy all permissions for usergroups
					$sql = 'SELECT * FROM ' . DL_AUTH_TABLE . '
						WHERE cat_id = ' . (int) $copy_from;
					$result = $this->db->sql_query($sql);
		
					while ($row = $this->db->sql_fetchrow($result))
					{
						$group_id	= $row['group_id'];
						$auth_view	= $row['auth_view'];
						$auth_dl	= $row['auth_dl'];
						$auth_up	= $row['auth_up'];
						$auth_mod	= $row['auth_mod'];
		
						$sql = 'INSERT INTO ' . DL_AUTH_TABLE . ' ' . $this->db->sql_build_array('INSERT', [
							'cat_id'	=> $cat_id,
							'group_id'	=> $group_id,
							'auth_view'	=> $auth_view,
							'auth_dl'	=> $auth_dl,
							'auth_up'	=> $auth_up,
							'auth_mod'	=> $auth_mod]);
						$this->db->sql_query($sql);
					}
		
					$this->db->sql_freeresult($result);
		
					$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_PERM_COPY', false, [$source_cat, $dest_cat]);
				}
			}
		
			// Purge the categories cache
			@unlink(DL_EXT_CACHE_PATH . 'data_dl_cats.' . $this->phpEx);
			@unlink(DL_EXT_CACHE_PATH . 'data_dl_auth.' . $this->phpEx);
		
			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;
		
			$message .= adm_back_link($this->u_action);
		
			trigger_error($message);
		}
		else if($action == 'delete' && $cat_id && !$this->dlext_main->get_sublevel_count($cat_id))
		{
			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type . '&amp;t=' . microtime();

			if (confirm_box(true))
			{
				if( $new_cat_id <= 0 )
				{
					$sql = 'SELECT dl_id, ver_real_file FROM ' . DL_VERSIONS_TABLE;
					$result = $this->db->sql_query($sql);
		
					while ($row = $this->db->sql_fetchrow($result))
					{
						$real_ver_file[$row['dl_id']][] = $row['ver_real_file'];
					}
		
					$this->db->sql_freeresult($result);
		
					$sql = 'SELECT c.cat_name, c.path, d.real_file, d.id AS df_id FROM ' . DL_CAT_TABLE . ' c, ' . DOWNLOADS_TABLE . ' d
						WHERE d.cat = c.id
							AND c.id = ' . (int) $cat_id . '
							AND d.extern = 0';
					$result = $this->db->sql_query($sql);
		
					$dl_ids = [];
		
					while ($row = $this->db->sql_fetchrow($result))
					{
						$df_id = $row['df_id'];
						$dl_ids[] = $df_id;
						$path = $row['path'];
						$real_file = $row['real_file'];
		
						if (!$new_cat_id)
						{
							@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . $real_file);
		
							if (isset($real_ver_file[$df_id]))
							{
								for ($i = 0; $i < count($real_ver_file[$df_id]); ++$i)
								{
									@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . $real_ver_file[$df_id][$i]);
								}
							}
						}
					}
		
					$this->db->sql_freeresult($result);
		
					$sql = 'DELETE FROM ' . DOWNLOADS_TABLE . '
						WHERE cat = ' . (int) $cat_id;
					$this->db->sql_query($sql);

					/**
					 * Workflow after deleting downloads
					 *
					 * @event 		dlext.acp_categories_delete_downloads_after
					 * @var array	dl_ids		download ID's
					 * @var int		cat_id		download category ID
					 * @since 8.1.0-RC2
					 */
					$vars = array(
						'dl_ids',
						'cat_id',
					);
					extract($this->phpbb_dispatcher->trigger_event('dlext.acp_categories_delete_downloads_after', compact($vars)));

					if (!empty($dl_ids))
					{
						$sql = 'DELETE FROM ' . DL_VERSIONS_TABLE . '
							WHERE ' . $this->db->sql_in_set('dl_id', $dl_ids);
						$this->db->sql_query($sql);

						$notification->delete_notifications([
							'oxpus.dlext.notification.type.approve',
							'oxpus.dlext.notification.type.broken',
							'oxpus.dlext.notification.type.dlext',
							'oxpus.dlext.notification.type.update',
							'oxpus.dlext.notification.type.capprove',
							'oxpus.dlext.notification.type.comments',
						], $dl_ids);
					}
				}
		
				if ($new_cat_id > 0)
				{
					$sql = 'SELECT path FROM ' . DL_CAT_TABLE . '
						WHERE id = ' . (int) $new_cat_id;
					$result = $this->db->sql_query($sql);
					$new_path = $this->db->sql_fetchfield('path');
					$this->db->sql_freeresult($result);

					$sql = 'SELECT dl_id, ver_real_file FROM ' . DL_VERSIONS_TABLE;
					$result = $this->db->sql_query($sql);
		
					while ($row = $this->db->sql_fetchrow($result))
					{
						$real_ver_file[$row['dl_id']][] = $row['ver_real_file'];
					}
		
					$this->db->sql_freeresult($result);
		
					$sql = 'SELECT c.cat_name, c.path, d.real_file, d.id AS df_id FROM ' . DL_CAT_TABLE . ' c, ' . DOWNLOADS_TABLE . ' d
						WHERE d.cat = c.id
							AND c.id = ' . (int) $cat_id . '
							AND d.extern = 0';
					$result = $this->db->sql_query($sql);

					$dl_ids = [];
		
					while ($row = $this->db->sql_fetchrow($result))
					{
						$df_id = $row['df_id'];
						$dl_ids[] = $df_id;
						$path = $row['path'];
						$real_file = $row['real_file'];
		
						@copy(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . $real_file, DL_EXT_FILEBASE_PATH. 'downloads/' . $new_path . $real_file);
						@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . $real_file);
	
						if (isset($real_ver_file[$df_id]))
						{
							for ($i = 0; $i < count($real_ver_file[$df_id]); ++$i)
							{
								@copy(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . $real_ver_file[$df_id][$i], DL_EXT_FILEBASE_PATH. 'downloads/' . $new_path . $real_ver_file[$df_id][$i]);
								@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . $real_ver_file[$df_id][$i]);
							}
						}
					}
		
					$this->db->sql_freeresult($result);

					$sql = 'UPDATE ' . DOWNLOADS_TABLE . '
						SET cat = ' . (int) $new_cat_id . '
						WHERE cat = ' . (int) $cat_id;
					$this->db->sql_query($sql);
		
					$sql = 'UPDATE ' . DL_STATS_TABLE . '
						SET cat_id = ' . (int) $new_cat_id . '
						WHERE cat_id = ' . (int) $cat_id;
					$this->db->sql_query($sql);
		
					$sql = 'UPDATE ' . DL_COMMENTS_TABLE . '
						SET cat_id = ' . (int) $new_cat_id . '
						WHERE cat_id = ' . (int) $cat_id;
					$this->db->sql_query($sql);

					$options['item_parent_id'] = $new_cat_id;
				}
				else
				{
					$sql = 'DELETE FROM ' . DL_STATS_TABLE . '
						WHERE cat_id = ' . (int) $cat_id;
					$this->db->sql_query($sql);
				}
		
				$sql = 'DELETE FROM ' . DL_CAT_TABLE . '
					WHERE id = ' . (int) $cat_id;
				$this->db->sql_query($sql);
		
				$sql = 'DELETE FROM ' . DL_CAT_TRAF_TABLE . '
					WHERE cat_id = ' . (int) $cat_id;
				$this->db->sql_query($sql);
		
				$sql = 'DELETE FROM ' . DL_COMMENTS_TABLE . '
					WHERE cat_id = ' . (int) $cat_id;
				$this->db->sql_query($sql);
		
				$sql = 'DELETE FROM ' . DL_AUTH_TABLE . '
					WHERE cat_id = ' . (int) $cat_id;
				$this->db->sql_query($sql);
		
				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_DEL', false, [$log_cat_name]);
		
				// Purge the categories cache
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_cats.' . $this->phpEx);
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_auth.' . $this->phpEx);
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_p.' . $this->phpEx);
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_preset.' . $this->phpEx);
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_cat_counts.' . $this->phpEx);
		
				$message = $this->language->lang('DL_CATEGORY_REMOVED') . adm_back_link($this->u_action);
		
				trigger_error($message);
			}
			else
			{
				$cat_name = $index[$cat_id]['cat_name'];
				$cat_name = str_replace('&nbsp;&nbsp;|___&nbsp;', '', $cat_name);
		
				$s_switch_cat = '<select name="new_cat_id">';
				$s_switch_cat .= '<option value="0">' . $this->language->lang('DL_DELETE_CAT_ONLY') . '</option>';
				$s_switch_cat .= '<option value="-1" selected="selected">' . $this->language->lang('DL_DELETE_CAT_AND_FILES') . '</option>';
				$s_switch_cat .= '<option value="---">----------------------------------------</option>';
				$s_switch_cat .= $this->dlext_extra->dl_dropdown(0, 0, $cat_id, 'auth_move');
				$s_switch_cat .= '</select>';
		
				$s_hidden_fields = [
					'cat_id'	=> $cat_id,
					'action'	=> 'delete',
					'parent'	=> $cat_parent,
					'type'		=> $idx_type,
				];
		
				$confirm_title = $this->language->lang('DL_CONFIRM_CAT_DELETE', $cat_name);

				$this->template->assign_var('S_SWITCH_CAT', $s_switch_cat);
	
				confirm_box(false, $confirm_title, build_hidden_fields($s_hidden_fields), 'dl_confirm_body.html');
			}
		}
		else if($action == 'delete_stats')
		{
			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type . '&amp;t=' . microtime();

			if (confirm_box(true))
			{
				$sql = 'DELETE FROM ' . DL_STATS_TABLE;

				if ($cat_id >= 1)
				{
					$sql .= ' WHERE cat_id = ' . (int) $cat_id;
				}

				$this->db->sql_query($sql);
	
				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_DEL_CAT_STATS', false, [$log_cat_name]);

				redirect($this->u_action);
			}
			else
			{
				$cat_name = $index[$cat_id]['cat_name'];
				$cat_name = str_replace('&nbsp;&nbsp;|___&nbsp;', '', $cat_name);

				$s_hidden_fields = [
					'cat_id' 	=> $cat_id,
					'action' 	=> 'delete_stats',
					'parent'	=> $cat_parent,
					'type'		=> $idx_type,
				];

				$confirm_title = ($cat_id == -1) ? $this->language->lang('DL_CONFIRM_ALL_STATS_DELETE') : $this->language->lang('DL_CONFIRM_CAT_STATS_DELETE', $cat_name);

				confirm_box(false, $confirm_title, build_hidden_fields($s_hidden_fields));
			}
		}
		else if ($action == 'delete_comments')
		{
			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type . '&amp;t=' . microtime();
	
			if (confirm_box(true))
			{
				if ($cat_id >= 1)
				{
					$sql_second = ' WHERE cat_id = ' . (int) $cat_id;
				}
				else
				{
					$sql_second = '';
				}

				$sql = 'SELECT dl_id FROM ' . DL_COMMENTS_TABLE;
				$sql .= $sql_second;
				$result = $this->db->sql_query($sql);

				$dl_ids = [];

				while($row = $db->sql_fetchrow($result))
				{
					$dl_ids[] = $row['dl_id'];
				}

				$this->db->sql_freeresult($result);

				$sql = 'DELETE FROM ' . DL_COMMENTS_TABLE;
				$sql .= $sql_second;
				$this->db->sql_query($sql);

				if (!empty($dl_ids))
				{
					$notification->delete_notifications([
						'oxpus.dlext.notification.type.capprove',
						'oxpus.dlext.notification.type.comments',
					], $dl_ids);
				}

				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_DEL_CAT_COM', false, [$log_cat_name]);

				redirect($this->u_action);
			}
			else
			{
				$cat_name = $index[$cat_id]['cat_name'];
				$cat_name = str_replace('&nbsp;&nbsp;|___&nbsp;', '', $cat_name);

				$s_hidden_fields = [
					'cat_id'	=> $cat_id,
					'action'	=> 'delete_comments',
					'confirm'	=> 1,
					'parent'	=> $cat_parent,
					'type'		=> $idx_type,
				];

				$confirm_title = ($cat_id == -1) ? $this->language->lang('DL_CONFIRM_ALL_COMMENTS_DELETE') : $this->language->lang('DL_CONFIRM_CAT_COMMENTS_DELETE', $cat_name);

				confirm_box(false, $confirm_title, build_hidden_fields($s_hidden_fields));
			}
		}
		else if($action == 'category_order')
		{
			$sql = 'SELECT sort FROM ' . DL_CAT_TABLE . '
				WHERE id = ' . (int) $cat_id;
			$result = $this->db->sql_query($sql);
			$sql_move = $this->db->sql_fetchfield('sort');
			$this->db->sql_freeresult($result);
		
			if ($move)
			{
				$sql_move += 15;
			}
			else
			{
				$sql_move -= 15;
			}
		
			$sql = 'UPDATE ' . DL_CAT_TABLE . '
				SET sort = ' . (int) $sql_move . '
				WHERE id = ' . (int) $cat_id;
			$this->db->sql_query($sql);
		
			$par_cat = $index[$cat_id]['parent'];
		
			$sql = 'SELECT id FROM ' . DL_CAT_TABLE . '
				WHERE parent = ' .(int) $par_cat . '
				ORDER BY sort';
			$result = $this->db->sql_query($sql);
		
			$i = 10;
		
			while($row = $this->db->sql_fetchrow($result))
			{
				$sql_move = 'UPDATE ' . DL_CAT_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'sort' => $i]) . ' WHERE id = ' . (int) $row['id'];
				$this->db->sql_query($sql_move);
		
				$i += 10;
			}
		
			$this->db->sql_freeresult($result);
		
			$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_MOVE', false, [$log_cat_name]);
		
			// Purge the categories cache
			unlink(DL_EXT_CACHE_PATH . 'data_dl_cats.' . $this->phpEx);
			unlink(DL_EXT_CACHE_PATH . 'data_dl_auth.' . $this->phpEx);
		
			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type . '&amp;t=' . microtime();
		
			redirect($this->u_action);
		}
		else if($action == 'asc_sort')
		{
			$sql = 'SELECT id FROM ' . DL_CAT_TABLE . '
				WHERE parent = ' . (int) $cat_id . '
				ORDER BY cat_name ASC';
			$result = $this->db->sql_query($sql);
		
			$i = 10;
		
			while($row = $this->db->sql_fetchrow($result))
			{
				$sql_move = 'UPDATE ' . DL_CAT_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'sort' => $i]) . ' WHERE id = ' . (int) $row['id'];
				$this->db->sql_query($sql_move);
		
				$i += 10;
			}
		
			$this->db->sql_freeresult($result);
		
			// Purge the categories cache
			@unlink(DL_EXT_CACHE_PATH . 'data_dl_cats.' . $this->phpEx);
			@unlink(DL_EXT_CACHE_PATH . 'data_dl_auth.' . $this->phpEx);
		
			$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_SORT_ASC');
		
			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type . '&amp;t=' . microtime();
		
			redirect($this->u_action);
		}
		else 
		{
			$stats_cats = [];
			$comments_cats = [];
		
			$sql = 'SELECT cat_id, COUNT(dl_id) AS total_stats FROM ' . DL_STATS_TABLE . '
				GROUP BY cat_id';
			$result = $this->db->sql_query($sql);
		
			while($row = $this->db->sql_fetchrow($result))
			{
				$stats_cats[$row['cat_id']] = $row['total_stats'];
			}
		
			$this->db->sql_freeresult($result);
		
			$sql = 'SELECT cat_id, COUNT(dl_id) AS total_comments FROM ' . DL_COMMENTS_TABLE . '
				GROUP BY cat_id';
			$result = $this->db->sql_query($sql);
		
			while($row = $this->db->sql_fetchrow($result))
			{
				$comments_cats[$row['cat_id']] = $row['total_comments'];
			}
		
			$this->db->sql_freeresult($result);
		
			$stats_total = 0;
			$comments_total = 0;
		
			$this->u_action_idx		= $this->u_action . '&amp;parent=' . $cat_parent . '&amp;type=';
			$this->u_action_open	= $this->u_action . '&amp;parent=#CAT#&amp;type=' . $idx_type;
			$this->u_action			.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

			foreach (array_keys($index) as $key)
			{
				$cur_cat = $index[$key];
				$cat_id = $cur_cat['id'];
		
				if (($idx_type == 'c' && $cur_cat['parent'] == $cat_parent) || $idx_type == 'f')
				{
					$cat_name			= ($idx_type == 'c') ? $cur_cat['cat_name_nav'] : $cur_cat['cat_name'];
					$cat_desc			= $cur_cat['description'];
					$cat_uid			= $cur_cat['desc_uid'];
					$cat_bitfield		= $cur_cat['desc_bitfield'];
					$cat_flags			= $cur_cat['desc_flags'];
					$cat_description	= generate_text_for_display($cat_desc, $cat_uid, $cat_bitfield, $cat_flags);

					$cat_icon = $cur_cat['cat_icon'];
		
					$cat_edit = "{$this->u_action}&amp;action=edit&amp;cat_id=$cat_id";
		
					$cat_sub = $this->dlext_main->get_sublevel_count($cat_id);
					$cat_sub_count = $this->dlext_main->count_sublevel($cat_id);
		
					if ($cat_sub)
					{
						$cat_delete = '';
					}
					else
					{
						$cat_delete = "{$this->u_action}&amp;action=delete&amp;cat_id=$cat_id";
					}
		
					$dl_move_up = "{$this->u_action}&amp;action=category_order&amp;move=0&amp;cat_id=$cat_id";
					$dl_move_down = "{$this->u_action}&amp;action=category_order&amp;move=1&amp;cat_id=$cat_id";
		
					$cat_folder = 'images/icon_folder.gif';
					if ($cat_sub_count)
					{
						$cat_folder = 'images/icon_subfolder.gif';
					}
		
					if ($cat_sub_count > 1)
					{
						$l_sort_asc = $this->language->lang('DL_SUB_SORT_ASC');
						$dl_sort_asc = "{$this->u_action}&amp;action=asc_sort&amp;cat_id=$cat_id";
					}
					else
					{
						$l_sort_asc = '';
						$dl_sort_asc = '';
					}
		
					$l_delete_stats = '';
					$l_delete_comments = '';
					$u_delete_stats = '';
					$u_delete_comments = '';
		
					if (isset($stats_cats[$cat_id]))
					{
						$l_delete_stats = $this->language->lang('DL_STATS_DELETE');
						$u_delete_stats = "{$this->u_action}&amp;action=delete_stats&amp;cat_id=$cat_id";
						++$stats_total;
					}
		
					if (isset($comments_cats[$cat_id]))
					{
						$l_delete_comments = $this->language->lang('DL_COMMENTS_DELETE');
						$u_delete_comments = "{$this->u_action}&amp;action=delete_comments&amp;cat_id=$cat_id";
						++$comments_total;
					}
		
					$this->template->assign_block_vars('categories', [
						'L_DELETE_STATS'		=> $l_delete_stats,
						'L_DELETE_COMMENTS'		=> $l_delete_comments,
						'L_SORT_ASC'			=> $l_sort_asc,
		
						'CAT_NAME'				=> $cat_name,
						'CAT_DESCRIPTION'		=> $cat_description,
						'CAT_FOLDER'			=> $cat_folder,
						'CAT_ICON'				=> $cat_icon,
		
						'U_CAT_EDIT'			=> $cat_edit,
						'U_CAT_DELETE'			=> $cat_delete,
						'U_CATEGORY_MOVE_UP'	=> $dl_move_up,
						'U_CATEGORY_MOVE_DOWN'	=> $dl_move_down,
						'U_CATEGORY_ASC_SORT'	=> $dl_sort_asc,
						'U_DELETE_STATS'		=> $u_delete_stats,
						'U_DELETE_COMMENTS'		=> $u_delete_comments,
						'U_CAT_OPEN'			=> ($cat_sub_count && $idx_type == 'c') ? str_replace('#CAT#', $cat_id, $this->u_action_open) : '',
					]);
				}
		
				if ($stats_total)
				{
					$l_delete_stats_all = $this->language->lang('DL_STATS_DELETE_ALL');
					$u_delete_stats_all = "{$this->u_action}&amp;action=delete_stats&amp;cat_id=-1";
					$this->template->assign_var('S_TOTAL_STATS', true);
				}
				else
				{
					$l_delete_stats_all = '';
					$u_delete_stats_all = '';
				}
		
				if ($comments_total)
				{
					$l_delete_comments_all = $this->language->lang('DL_COMMENTS_DELETE_ALL');
					$u_delete_comments_all = "{$this->u_action}&amp;action=delete_comments&amp;cat_id=-1";
					$this->template->assign_var('S_TOTAL_COMMENTS', true);
				}
				else
				{
					$l_delete_comments_all = '';
					$u_delete_comments_all = '';
				}
			}
		
			$cat_navi = '';
			if ($cat_parent <> 0)
			{
				$tmp_nav = [];
				$cat_navi = $this->dlext_nav->nav($cat_parent, 'acp', $tmp_nav, $this->u_action_open);
			}
		
			$this->template->assign_vars([
				'L_DELETE_STATS_ALL'	=> $l_delete_stats_all,
				'L_DELETE_COMMENTS_ALL'	=> $l_delete_comments_all,
		
				'CAT_PATH'				=> (isset($cat_path)) ? $cat_path : '/',
				'CAT_NAME'				=> $cat_name,
		
				'S_CATEGORY_ACTION'		=> $this->u_action,
				'S_IDX_TYPE'			=> (!empty($index)) ? $idx_type : '',
				'S_SORT_MAIN'			=> ($cat_parent == 0) ? true : false,
		
				'U_SORT_LEVEL_ZERO'		=> "{$this->u_action}&amp;action=asc_sort&amp;cat_id=0",
				'U_DELETE_STATS_ALL'	=> $u_delete_stats_all,
				'U_DELETE_COMMENTS_ALL'	=> $u_delete_comments_all,
				'U_IDX_ACTION'			=> $this->u_action_idx,
				'U_CAT_NAV'				=> $cat_navi,
			]);
		}
	}
}
