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
class acp_files_controller implements acp_files_interface
{
	public $u_action;
	public $db;
	public $user;
	public $auth;
	public $phpEx;
	public $phpbb_extension_manager;
	public $phpbb_container;
	public $phpbb_path_helper;
	public $phpbb_log;
	public $phpbb_dispatcher;

	public $root_path;
	public $config;
	public $config_text;
	public $helper;
	public $language;
	public $request;
	public $template;

	public $ext_path;
	public $ext_path_web;
	public $ext_path_ajax;

	public $dlext_auth;

	protected $dlext_extra;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_topic;

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
		$dlext_auth,
		$dlext_extra,
		$dlext_files,
		$dlext_format,
		$dlext_main,
		$dlext_topic
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
		$this->config_text				= $this->phpbb_container->get('config_text');
		$this->helper					= $this->phpbb_container->get('controller.helper');
		$this->language					= $this->phpbb_container->get('language');
		$this->request					= $this->phpbb_container->get('request');
		$this->template					= $this->phpbb_container->get('template');

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_extra				= $dlext_extra;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_topic				= $dlext_topic;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$this->auth->acl($this->user->data);
		if (!$this->auth->acl_get('a_dl_files'))
		{
			trigger_error('DL_NO_PERMISSION', E_USER_WARNING);
		}

		include_once($this->ext_path . 'phpbb/includes/acm_init.' . $this->phpEx);

		if (isset($df_id) && $df_id)
		{
			$dl_file = [];
			$dl_file = $this->dlext_files->all_files(0, '', 'ASC', '', $df_id, 1, '*');
			if (isset($dl_file['id']) && !$dl_file['id'])
			{
				trigger_error($this->language->lang('MUST_SELECT_DOWNLOAD'));
			}
		}
		
		$index = [];
		$index = $this->dlext_main->full_index($cat_id);
		
		if ($cancel)
		{
			$action = '';
		}

		$notification = $this->phpbb_container->get('notification_manager');

		include($this->ext_path . 'phpbb/includes/fields.' . $this->phpEx);
		$cp = new \oxpus\dlext\phpbb\includes\custom_profile();
		
		if($action == 'edit' || $action == 'add')
		{
			$s_hidden_fields = ['action' => 'save'];
		
			$cat_id = ($cat_id) ? $cat_id : ((isset($dl_file['cat'])) ? $dl_file['cat'] : 0);
		
			$s_file_free_select = '<select name="file_free">';
			$s_file_free_select .= '<option value="0">' . $this->language->lang('NO') . '</option>';
			$s_file_free_select .= '<option value="1">' . $this->language->lang('YES') . '</option>';
			$s_file_free_select .= '<option value="2">' . $this->language->lang('DL_IS_FREE_REG') . '</option>';
			$s_file_free_select .= '</select>';
		
			$s_select_datasize = '<option value="byte">' . $this->language->lang('DL_BYTES') . '</option>';
			$s_select_datasize .= '<option value="kb">' . $this->language->lang('DL_KB') . '</option>';
			$s_select_datasize .= '<option value="mb">' . $this->language->lang('DL_MB') . '</option>';
			$s_select_datasize .= '<option value="gb">' . $this->language->lang('DL_GB') . '</option>';
			$s_select_datasize .= '</select>';
		
			$s_hacklist_select = '<select name="hacklist">';
			$s_hacklist_select .= '<option value="0">' . $this->language->lang('NO') . '</option>';
			$s_hacklist_select .= '<option value="1">' . $this->language->lang('YES') . '</option>';
			$s_hacklist_select .= '<option value="2">' . $this->language->lang('DL_MOD_LIST') . '</option>';
			$s_hacklist_select .= '</select>';
		
			if($action == 'edit')
			{
				$description			= (isset($dl_file['description'])) ? $dl_file['description'] : '';
				$file_traffic			= (isset($dl_file['file_traffic'])) ? $dl_file['file_traffic'] : 0;
				$dl_extern				= (isset($dl_file['extern'])) ? $dl_file['extern'] : 0;
				$dl_extern_size			= (isset($dl_file['file_size'])) ? $dl_file['file_size'] : 0;
				$file_name				= (isset($dl_file['file_name']) && $dl_extern) ? $dl_file['file_name'] : '';
				$cat_id					= (isset($dl_file['cat'])) ? $dl_file['cat'] : 0;
				$hacklist				= (isset($dl_file['hacklist'])) ? $dl_file['hacklist'] : 0;
				$hack_author			= (isset($dl_file['hack_author'])) ? $dl_file['hack_author'] : '';
				$hack_author_email		= (isset($dl_file['hack_author_email'])) ? $dl_file['hack_author_email'] : '';
				$hack_author_web		= (isset($dl_file['hack_author_website'])) ? $dl_file['hack_author_website'] : '';
				$hack_version			= (isset($dl_file['hack_version'])) ? $dl_file['hack_version'] : '';
				$hack_dl_url			= (isset($dl_file['hack_dl_url'])) ? $dl_file['hack_dl_url'] : '';
				$long_desc				= (isset($dl_file['long_desc'])) ? $dl_file['long_desc'] : '';
				$mod_test				= (isset($dl_file['test'])) ? $dl_file['test'] : '';
				$require				= (isset($dl_file['req'])) ? $dl_file['req'] : '';
				$todo					= (isset($dl_file['todo'])) ? $dl_file['todo'] : '';
				$warning				= (isset($dl_file['warning'])) ? $dl_file['warning'] : '';
				$mod_desc				= (isset($dl_file['mod_desc'])) ? $dl_file['mod_desc'] : '';
				$mod_list				= (isset($dl_file['mod_list']) && $dl_file['mod_list'] != 0) ? 'checked="checked"' : '';
				$dl_free				= (isset($dl_file['free'])) ? $dl_file['free'] : 0;
				$approve				= (isset($dl_file['approve'])) ? $dl_file['approve'] : 0;
		
				$mod_desc_uid		= (isset($dl_file['mod_desc_uid'])) ? $dl_file['mod_desc_uid'] : '';
				$mod_desc_flags		= (isset($dl_file['mod_desc_flags'])) ? $dl_file['mod_desc_flags'] : 0;
				$long_desc_uid		= (isset($dl_file['long_desc_uid'])) ? $dl_file['long_desc_uid'] : '';
				$long_desc_flags	= (isset($dl_file['long_desc_flags'])) ? $dl_file['long_desc_flags'] : 0;
				$desc_uid			= (isset($dl_file['desc_uid'])) ? $dl_file['desc_uid'] : '';
				$desc_flags			= (isset($dl_file['desc_flags'])) ? $dl_file['desc_flags'] : 0;
				$warn_uid			= (isset($dl_file['warn_uid'])) ? $dl_file['warn_uid'] : '';
				$warn_flags			= (isset($dl_file['warn_flags'])) ? $dl_file['warn_flags'] : 0;
				$todo_uid			= (isset($dl_file['todo_uid'])) ? $dl_file['todo_uid'] : '';
				$todo_flags			= (isset($dl_file['todo_flags'])) ? $dl_file['todo_flags'] : 0;
		
				$text_ary		= generate_text_for_edit($mod_desc, $mod_desc_uid, $mod_desc_flags);
				$mod_desc		= $text_ary['text'];
		
				$text_ary		= generate_text_for_edit($long_desc, $long_desc_uid, $long_desc_flags);
				$long_desc		= $text_ary['text'];
		
				$text_ary		= generate_text_for_edit($description, $desc_uid, $desc_flags);
				$description	= $text_ary['text'];
		
				$text_ary		= generate_text_for_edit($warning, $warn_uid, $warn_flags);
				$warning		= $text_ary['text'];
		
				$text_ary		= generate_text_for_edit($todo, $todo_uid, $todo_flags);
				$todo			= $text_ary['text'];
		
				$tmp_ary				= $this->dlext_format->dl_size($file_traffic, 2, 'select');
				$file_traffic_out		= $tmp_ary['size_out'];
				$data_range_select		= $tmp_ary['range'];
		
				$tmp_ary				= $this->dlext_format->dl_size($dl_extern_size, 2, 'select');
				$file_extern_size_out	= $tmp_ary['size_out'];
				$file_extern_size_range	= $tmp_ary['range'];
		
				unset($tmp_ary);
		
				$s_file_traffic_range	= str_replace('value="' . $data_range_select . '">', 'value="' . $data_range_select . '" selected="selected">', $s_select_datasize);
				$s_file_extsize_select	= str_replace('value="' . $file_extern_size_range . '">', 'value="' . $file_extern_size_range . '" selected="selected">', $s_select_datasize);
				$s_hacklist_select		= str_replace('value="' . $hacklist . '">', 'value="' . $hacklist . '" selected="selected">', $s_hacklist_select);
				$s_file_free_select		= str_replace('value="' . $dl_free . '">', 'value="' . $dl_free . '" selected="selected">', $s_file_free_select);
		
				if ($dl_extern)
				{
					$checkextern = 'checked="checked"';
				}
				else
				{
					$checkextern = '';
				}
		
				if ($approve)
				{
					$approve = 'checked="checked"';
				}
				else
				{
					$approve = '';
				}
		
				if (isset($this->config['dl_disable_popup']) && !$this->config['dl_disable_popup'])
				{
					$this->template->assign_var('S_POPUP_NOTIFY', true);
				}
		
				$this->template->assign_var('S_CHANGE_TIME', true);
		
				$thumbnail = (isset($dl_file['thumbnail'])) ? $dl_file['thumbnail'] : '';
		
				if ($thumbnail)
				{
					$this->template->assign_var('S_DEL_THUMB', true);
				}
		
				if ($thumbnail != $df_id . '_')
				{
					$this->template->assign_var('S_SHOW_THUMB', true);
				}
		
				$this->template->assign_var('S_CLICK_RESET', true);
		
				$s_hidden_fields += ['df_id' => $df_id];
			}
			else
			{
				$approve				= 'checked="checked"';
				$description			= '';
				$file_traffic			= 0;
				$file_name				= '';
				$hacklist				= 0;
				$hack_author			= '';
				$hack_author_email		= '';
				$hack_author_web		= '';
				$hack_version			= '';
				$hack_dl_url			= '';
				$long_desc				= '';
				$mod_test				= '';
				$require				= '';
				$todo					= '';
				$warning				= '';
				$mod_desc				= '';
				$mod_list				= '';
				$file_traffic_out		= 0;
				$checkextern			= '';
				$thumbnail				= '';
				$file_extern_size_out	= 0;
		
				$s_file_traffic_range	= str_replace('value="kb">', 'value="kb" selected="selected">', $s_select_datasize);
				$s_file_extsize_select	= str_replace('value="byte">', 'value="byte" selected="selected">', $s_select_datasize);
			}

			if (isset($this->config['dl_disable_email']) && !$this->config['dl_disable_email'])
			{
				$this->template->assign_var('S_EMAIL_BLOCK', true);
			}

			if ($this->config['dl_traffic_off'])
			{
				$s_hidden_fields += ['file_traffic' => 0];
			}

			$s_file_traffic_range = '<select name="dl_t_quote">' . $s_file_traffic_range;
			$s_file_extsize_select = '<select name="dl_e_quote">' . $s_file_extsize_select;
		
			if (isset($index[$cat_id]['allow_thumbs']) && $index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
			{
				$this->template->assign_var('S_ALLOW_THUMB', true);
		
				$thumbnail_explain	= $this->language->lang('DL_THUMB_DIM_SIZE', $this->config['dl_thumb_xsize'], $this->config['dl_thumb_ysize'], $this->dlext_format->dl_size($this->config['dl_thumb_fsize']));
		
				$enctype			= 'enctype="multipart/form-data"';
			}
			else
			{
				$enctype			= '';
		
				$thumbnail_explain	= '';
			}
		
			$select_code = '<select name="cat_id">';
			$select_code .= $this->dlext_extra->dl_dropdown(0, 0, $cat_id, 'auth_up');
			$select_code .= '</select>';
		
			if ($df_id)
			{
				$this->template->assign_var('S_EDIT_VERSIONS', true);
			}
		
			if (isset($this->config['dl_use_hacklist']))
			{
				$this->template->assign_var('S_USE_HACKLIST', true);
			}
		
			if (isset($index[$cat_id]['allow_mod_desc']))
			{
				$this->template->assign_var('S_USE_MOD_DESC', true);
			}
		
			$ext_blacklist = $this->dlext_auth->get_ext_blacklist();

			if (!empty($ext_blacklist))
			{
				$blacklist_explain = '<br />' . $this->language->lang('DL_FORBIDDEN_EXT_EXPLAIN', implode(', ', $ext_blacklist));
			}
			else
			{
				$blacklist_explain = '';
			}
		
			$sql = 'SELECT ver_id, ver_change_time, ver_version FROM ' . DL_VERSIONS_TABLE . '
				WHERE dl_id = ' . (int) $df_id . '
				ORDER BY ver_version DESC, ver_change_time DESC';
			$result = $this->db->sql_query($sql);
		
			$total_versions = $this->db->sql_affectedrows($result);
			$multiple_size = ($total_versions > 10) ? 10 : $total_versions;
		
			$s_select_version = '<select name="file_version">';
			$s_select_ver_del = '<select name="file_ver_del[]" multiple="multiple" size="' . $multiple_size . '">';
			$s_select_version .= '<option value="0" selected="selected">' . $this->language->lang('DL_VERSION_CURRENT') . '</option>';
		
			while ($row = $this->db->sql_fetchrow($result))
			{
				$s_select_version .= '<option value="' . $row['ver_id'] . '">' . $row['ver_version'] . ' - ' . $this->user->format_date($row['ver_change_time']) . '</option>';
				$s_select_ver_del .= '<option value="' . $row['ver_id'] . '">' . $row['ver_version'] . ' - ' . $this->user->format_date($row['ver_change_time']) . '</option>';
			}
		
			$this->db->sql_freeresult($result);
		
			$s_select_version .= '</select>';
			$s_select_ver_del .= '</select>';
		
			if (!$total_versions)
			{
				$s_select_ver_del = '';
			}

			$dl_file_edit_hint				= $this->config_text->get('dl_file_edit_hint');

			if ($dl_file_edit_hint)
			{
				$dl_file_edit_hint_uid		= $this->config['dl_file_edit_hint_bbcode'];
				$dl_file_edit_hint_bitfield	= $this->config['dl_file_edit_hint_bitfield'];
				$dl_file_edit_hint_flags	= $this->config['dl_file_edit_hint_flags'];
				$formated_hint_text 		= generate_text_for_display($dl_file_edit_hint, $dl_file_edit_hint_uid, $dl_file_edit_hint_bitfield, $dl_file_edit_hint_flags);
			}
			else
			{
				$formated_hint_text			= '';
			}

			add_form_key('dl_adm_edit');
		
			$template_ary = [
				'L_DL_APPROVE_EXPLAIN'				=> 'DL_APPROVE',
				'L_DL_CAT_NAME_EXPLAIN'				=> 'DL_CHOOSE_CATEGORY',
				'L_DL_DESCRIPTION_EXPLAIN'			=> 'DL_FILE_DESCRIPTION',
				'L_DL_EXTERN_EXPLAIN'				=> 'DL_EXTERN',
				'L_DL_HACK_AUTHOR_EXPLAIN'			=> 'DL_HACK_AUTOR',
				'L_DL_HACK_AUTHOR_EMAIL_EXPLAIN'	=> 'DL_HACK_AUTOR_EMAIL',
				'L_DL_HACK_AUTHOR_WEBSITE_EXPLAIN'	=> 'DL_HACK_AUTOR_WEBSITE',
				'L_DL_HACK_DL_URL_EXPLAIN'			=> 'DL_HACK_DL_URL',
				'L_DL_HACK_VERSION_EXPLAIN'			=> 'DL_HACK_VERSION',
				'L_DL_HACKLIST_EXPLAIN'				=> 'DL_HACKLIST',
				'L_DL_IS_FREE_EXPLAIN'				=> 'DL_IS_FREE',
				'L_DL_MOD_DESC_EXPLAIN'				=> 'DL_MOD_DESC',
				'L_DL_MOD_LIST_EXPLAIN'				=> 'DL_MOD_LIST',
				'L_DL_MOD_REQUIRE_EXPLAIN'			=> 'DL_MOD_REQUIRE',
				'L_DL_MOD_TEST_EXPLAIN'				=> 'DL_MOD_TEST',
				'L_DL_MOD_TODO_EXPLAIN'				=> 'DL_MOD_TODO',
				'L_DL_MOD_WARNING_EXPLAIN'			=> 'DL_MOD_WARNING',
				'L_DL_NAME_EXPLAIN'					=> 'DL_NAME',
				'L_DL_TRAFFIC_EXPLAIN'				=> 'DL_TRAFFIC',
				'L_LINK_URL_EXPLAIN'				=> 'DL_FILES_URL',
				'L_DL_THUMBNAIL_EXPLAIN'			=> 'DL_THUMB',
				'DL_THUMBNAIL_SECOND'				=> $thumbnail_explain,
				'L_CHANGE_TIME_EXPLAIN'				=> 'DL_NO_CHANGE_EDIT_TIME',
				'L_DISABLE_POPUP_EXPLAIN'			=> 'DL_DISABLE_POPUP_FILES',
				'L_DL_SEND_NOTIFY_EXPLAIN'			=> 'DL_DISABLE_EMAIL_FILES',
				'L_CLICK_RESET_EXPLAIN'				=> 'DL_KLICKS_RESET',
		
				'ACTION_MODE'						=> ($action == 'add') ? $this->language->lang('ADD') : $this->language->lang('EDIT'),
		
				'BLACKLIST_EXPLAIN'		=> $blacklist_explain,
				'CHECKEXTERN'			=> $checkextern,
				'DESCRIPTION'			=> $description,
				'FILE_NAME'				=> $file_name,
				'HACK_AUTHOR'			=> $hack_author,
				'HACK_AUTHOR_EMAIL'		=> $hack_author_email,
				'HACK_AUTHOR_WEBSITE'	=> $hack_author_web,
				'HACK_DL_URL'			=> $hack_dl_url,
				'HACK_VERSION'			=> $hack_version,
				'LONG_DESC'				=> $long_desc,
				'MOD_DESC'				=> $mod_desc,
				'MOD_LIST'				=> $mod_list,
				'MOD_REQUIRE'			=> $require,
				'MOD_TEST'				=> $mod_test,
				'MOD_TODO'				=> $todo,
				'MOD_WARNING'			=> $warning,
				'TRAFFIC'				=> $file_traffic_out,
				'URL'					=> $file_name,
				'APPROVE'				=> $approve,
				'SELECT_CAT'			=> $select_code,
				'ENCTYPE'				=> $enctype,
				'THUMBNAIL'				=> DL_EXT_FILEBASE_PATH . 'thumbs/' . $thumbnail,
				'FILE_EXT_SIZE'			=> $file_extern_size_out,
				'FORMATED_HINT_TEXT'	=> $formated_hint_text,
		
				'S_TODO_LINK_ONOFF'		=> ($this->config['dl_todo_onoff']) ? true : false,
				'S_SELECT_VERSION'		=> $s_select_version,
				'S_SELECT_VER_DEL'		=> $s_select_ver_del,
				'S_HACKLIST_SELECT'		=> $s_hacklist_select,
				'S_FILE_FREE_SELECT'	=> $s_file_free_select,
				'S_FILE_TRAFFIC_RANGE'	=> $s_file_traffic_range,
				'S_FILE_EXT_SIZE_RANGE'	=> $s_file_extsize_select,
				'S_DOWNLOADS_ACTION'	=> $this->u_action,
				'S_DL_TRAFFIC'			=> $this->config['dl_traffic_off'],
				'S_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields),
		
				'U_BACK'				=> $this->u_action . '&amp;cat_id=' . $cat_id,
			];

			/**
			 * Display extra data to save them with the download
			 *
			 * @event 		dlext.acp_files_template_before
			 * @var int		df_id			download ID
			 * @var int		cat_id			download category ID
			 * @var array	template_ary	array of download's data for edit
			 * @since 8.1.0-RC2
			 */
			$vars = array(
				'df_id',
				'cat_id',
				'template_ary',
			);
			extract($this->phpbb_dispatcher->trigger_event('dlext.acp_files_template_before', compact($vars)));

			$this->template->assign_vars($template_ary);

			// Init and display the custom fields with the existing data
			$cp->get_profile_fields($df_id);
			$cp->generate_profile_fields($this->user->get_iso_lang_id());
		
			$this->template->assign_var('S_DL_FILES_EDIT', true);
		}
		else if($action == 'save')
		{
			if ($file_option == 3)
			{
				if (confirm_box(true))
				{
					$dl_ids = [];
		
					for ($i = 0; $i < count($file_ver_del); ++$i)
					{
						$dl_ids[] = intval($file_ver_del[$i]);
					}
		
					if ($del_file)
					{
						$sql = 'SELECT path FROM ' . DL_CAT_TABLE . '
							WHERE id = ' . (int) $cat_id;
						$result = $this->db->sql_query($sql);
						$path = $this->db->sql_fetchfield('path');
						$this->db->sql_freeresult($result);
		
						if (!empty($dl_ids))
						{
							$sql = 'SELECT ver_real_file FROM ' . DL_VERSIONS_TABLE . '
								WHERE ' . $this->db->sql_in_set('ver_id', $dl_ids);
							$result = $this->db->sql_query($sql);
		
							while ($row = $this->db->sql_fetchrow($result))
							{
								@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . $row['ver_real_file']);
							}
		
							$this->db->sql_freeresult($result);
		
							$sql = 'SELECT file_type, real_name FROM ' . DL_VER_FILES_TABLE . '
								WHERE ' . $this->db->sql_in_set('ver_id', $dl_ids);
							$result = $this->db->sql_query($sql);
		
							while ($row = $this->db->sql_fetchrow($result))
							{
								switch ($row['file_type'])
								{
									case 1:
										@unlink(DL_EXT_FILEBASE_PATH. 'version/images/' . $row['real_name']);
									break;
									default:
										@unlink(DL_EXT_FILEBASE_PATH. 'version/files/' . $row['real_name']);
								}
							}
		
							$this->db->sql_freeresult($result);
						}
					}
		
					if (!empty($dl_ids))
					{
						$sql = 'DELETE FROM ' . DL_VERSIONS_TABLE . '
							WHERE ' . $this->db->sql_in_set('ver_id', $dl_ids);
						$this->db->sql_query($sql);
		
						$sql = 'DELETE FROM ' . DL_VER_FILES_TABLE . '
							WHERE ' . $this->db->sql_in_set('ver_id', $dl_ids);
						$this->db->sql_query($sql);
					}
		
					redirect($this->u_action . "&amp;cat_id=$cat_id");
				}
				else
				{
					$this->template->assign_var('S_DELETE_FILES_CONFIRM', true);
		
					$s_hidden_fields = [
						'view'			=> 'modcp',
						'action'		=> 'save',
						'cat_id'		=> $cat_id,
						'df_id'			=> $df_id,
						'file_ver_opt'	=> 3,
					];
		
					for ($i = 0; $i < count($file_ver_del); ++$i)
					{
						$s_hidden_fields += ['file_ver_del[' . $i . ']' => $file_ver_del[$i]];
					}
		
					confirm_box(false, 'DL_CONFIRM_DEL_VERSIONS', build_hidden_fields($s_hidden_fields), 'dl_confirm_body.html');
				}
			}
			else
			{
				if (!check_form_key('dl_adm_edit'))
				{
					trigger_error('FORM_INVALID');
				}
		
				$new_version			= false;
		
				$allow_bbcode			= ($this->config['allow_bbcode']) ? true : false;
				$allow_urls				= true;
				$allow_smilies			= ($this->config['allow_smilies']) ? true : false;
				$desc_uid				= $desc_bitfield = $mod_desc_uid = $mod_desc_bitfield = $long_desc_uid = $long_desc_bitfield = $warn_uid = $warn_bitfield = $todo_uid = $todo_bitfield = '';
				$desc_flags				= $mod_desc_flags = $long_desc_flags = $warn_flags = $todo_flags = 0;
		
				if ($description)
				{
					generate_text_for_storage($description, $desc_uid, $desc_bitfield, $desc_flags, $allow_bbcode, true, $allow_smilies);
				}
				else
				{
					trigger_error($this->language->lang('NO_SUBJECT'), E_USER_WARNING);
				}
		
				if ($long_desc)
				{
					generate_text_for_storage($long_desc, $long_desc_uid, $long_desc_bitfield, $long_desc_flags, $allow_bbcode, true, $allow_smilies);
				}
		
				if ($mod_desc)
				{
					generate_text_for_storage($mod_desc, $mod_desc_uid, $mod_desc_bitfield, $mod_desc_flags, $allow_bbcode, true, $allow_smilies);
				}
		
				if ($warning)
				{
					generate_text_for_storage($warning, $warn_uid, $warn_bitfield, $warn_flags, $allow_bbcode, true, $allow_smilies);
				}
		
				if ($todo)
				{
					generate_text_for_storage($todo, $todo_flags, $todo_bitfield, $todo_uid, $allow_bbcode, true, $allow_smilies);
				}
		
		
				$extension				= str_replace('.', '', trim(strrchr(strtolower($file_name), '.')));
				$ext_blacklist			= $this->dlext_auth->get_ext_blacklist();
		
				$new_real_file			= '';
		
				if ($this->config['dl_enable_blacklist'])
				{
					if (in_array($extension, $ext_blacklist))
					{
						trigger_error($this->language->lang('DL_FORBIDDEN_EXTENSION'), E_USER_WARNING);
					}
				}
		
				if ($file_extern)
				{
					$file_traffic = 0;
				}
				else
				{
					$file_traffic = $this->dlext_format->resize_value('dl_file_traffic', $file_traffic);
				}
		
				$file_path = $index[$cat_id]['cat_path'];
				$cat_name = $index[$cat_id]['cat_name'];
		
				if (!$file_extern)
				{
					$file_name = (strpos($file_name, '/')) ? substr($file_name, strrpos($file_name, '/') + 1) : $file_name;
				}
		
				// validate custom profile fields
				$error = $cp_data = $cp_error = [];
				$cp->submit_cp_field($this->user->get_iso_lang_id(), $cp_data, $error);
		
				// Stop here, if custom fields are invalid!
				if (!empty($error))
				{
					trigger_error(implode('<br />', $error), E_USER_WARNING);
				}
		
				if ($df_id && !$file_extern)
				{
					$dl_file = [];
					$dl_file = $this->dlext_files->all_files(0, 0, 'ASC', 0, $df_id, true, '*');
		
					$real_file_old	= (isset($dl_file['real_file'])) ? $dl_file['real_file'] : '';
					$file_cat_old	= (isset($dl_file['cat'])) ? $dl_file['cat'] : 0;
		
					$index_new = [];
					$index_new = $this->dlext_main->full_index($file_cat_old);
		
					$file_path_old = (isset($index_new[$file_cat_old]['cat_path'])) ? $index_new[$file_cat_old]['cat_path'] : '';
					$file_path_new = (isset($index[$cat_id]['cat_path'])) ? $index[$cat_id]['cat_path'] : '';
		
					if ($file_name)
					{
						$extension = str_replace('.', '', trim(strrchr(strtolower($file_name), '.')));
						$new_real_file = md5($file_name) . '.' . $extension;
	
						if ($file_option == 2 && !$file_version)
						{
							@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_old . $real_file_old);
						}
		
						$i = 0;

						while(@file_exists(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_new . $new_real_file))
						{
							$new_real_file = md5($i . $file_name) . '.' . $extension;
							++$i;
						}

						copy(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_old . $file_name, DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_new . $new_real_file);
						chmod(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_new . $new_real_file, 0777);
						unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_old . $file_name);
		
						$real_file_old = $new_real_file;
					}
					else
					{
						if ($dl_file['file_name'] == $dl_file['real_file'])
						{
							$extension = str_replace('.', '', trim(strrchr(strtolower($dl_file['real_file']), '.')));
							$new_real_file = md5($dl_file['real_file']) . '.' . $extension;

							$i = 0;

							while(@file_exists(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_old . $new_real_file))
							{
								$new_real_file = md5($i . $dl_file['real_file']) . '.' . $extension;
								++$i;
							}
		
							@copy(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_old . $real_file_old, DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_old . $new_real_file);
							@chmod(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_old . $new_real_file, 0777);
							@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_old . $real_file_old);
						}
						else
						{
							$new_real_file = $dl_file['real_file'];
						}
					}
		
					if ($file_cat_old != $cat_id)
					{
						if ($file_path_old != $file_path_new)
						{
							@copy(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_old . $real_file_old, DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_new . $new_real_file);
							@chmod(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_new . $new_real_file, 0777);
							@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_old . $real_file_old);
		
							$sql = 'SELECT ver_real_file FROM ' . DL_VERSIONS_TABLE . '
								WHERE dl_id = ' . (int) $df_id;
							$result = $this->db->sql_query($sql);
		
							while ($row = $this->db->sql_fetchrow($result))
							{
								$real_ver_file = $row['ver_real_file'];
		
								@copy(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_old . $real_ver_file, DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_new . $real_ver_file);
								@chmod(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_new . $real_ver_file, 0777);
								@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path_old . $real_ver_file);
							}
		
							$this->db->sql_freeresult($result);
						}
		
						$sql = 'UPDATE ' . DL_STATS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'cat_id' => $cat_id]) . ' WHERE id = ' . (int) $df_id;
						$this->db->sql_query($sql);
		
						$sql = 'UPDATE ' . DL_COMMENTS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'cat_id' => $cat_id]) . ' WHERE id = ' . (int) $df_id;
						$this->db->sql_query($sql);
					}
				}
				else if (!$file_extern && $file_name)
				{
					$extension = str_replace('.', '', trim(strrchr(strtolower($file_name), '.')));
					$new_real_file = md5($file_name) . '.' . $extension;
					
					$i = 0;

					while(@file_exists(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path . $new_real_file))
					{
						$new_real_file = md5($i . $file_name) . '.' . $extension;
						++$i;
					}
		
					@copy(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path . $file_name, DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path . $new_real_file);
					@chmod(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path . $new_real_file, 0777);
					@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path . $file_name);
				}
		
				if (!$file_extern && $file_name)
				{
					$file_size = sprintf("%u", @filesize(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path . $new_real_file));
		
					if (!$file_size)
					{
						trigger_error('796: ' . $this->language->lang('DL_FILE_NOT_FOUND', $new_real_file, DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path), E_USER_WARNING);
					}
				}
				else
				{
					$new_real_file = '';
					$file_size = $this->dlext_format->resize_value('dl_extern_size', $file_extern_size);
				}
		
				$current_time = time();
				$current_user = $this->user->data['user_id'];
		
				if ($this->config['dl_set_add'] == 1 && $this->config['dl_set_user'])
				{
					$current_user = $this->config['dl_set_user'];
				}
		
				if ($this->config['dl_set_add'] == 2 && $index[$cat_id]['dl_set_add'] && $index[$cat_id]['dl_set_user'])
				{
					$current_user = $index[$cat_id]['dl_set_user'];
				}
		
				if ($new_real_file)
				{
					$hash_method = $this->config['dl_file_hash_algo'];
					$func_hash = $hash_method . '_file';
					$file_hash = $func_hash(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path . $new_real_file);
				}
				else
				{
					$file_hash = '';
				}
		
				/*
				* Enter new version if choosen
				*/
				if ($file_name && $df_id)
				{
					if (!$file_option || $file_option == 1)
					{
						$sql = 'INSERT INTO ' . DL_VERSIONS_TABLE . ' ' . $this->db->sql_build_array('INSERT', [
							'dl_id'				=> $df_id,
							'ver_file_name'		=> ($file_option) ? $file_name : $dl_file['file_name'],
							'ver_real_file'		=> ($file_option) ? $new_real_file : $dl_file['real_file'],
							'ver_file_hash'		=> ($file_option) ? $file_hash : $dl_file['file_hash'],
							'ver_file_size'		=> ($file_option) ? $file_size : $dl_file['file_size'],
							'ver_version'		=> ($file_option) ? $hack_version : $dl_file['hack_version'],
							'ver_add_time'		=> ($file_option) ? time() : $dl_file['add_time'],
							'ver_change_time'	=> ($file_option) ? time() : $dl_file['change_time'],
							'ver_add_user'		=> ($file_option) ? $this->user->data['user_id'] : $dl_file['add_user'],
							'ver_change_user'	=> ($file_option) ? $this->user->data['user_id'] : $dl_file['change_user'],
							'ver_active'		=> 0,
							'ver_text'			=> '',
						]);
		
						$this->db->sql_query($sql);
						$new_version = $this->db->sql_nextid();
					}
					else if ($file_option == 2 && $file_version)
					{
						$sql = 'SELECT ver_real_file FROM ' . DL_VERSIONS_TABLE . '
							WHERE dl_id = ' . (int) $df_id . '
								AND ver_id = ' . (int) $file_version;
						$result = $this->db->sql_query($sql);
						$real_old_file = $this->db->sql_fetchfield('ver_real_file');
						$this->db->sql_freeresult($result);
		
						@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $dl_path . $real_old_file);
		
						$sql = 'UPDATE ' . DL_VERSIONS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'ver_file_name'		=> $file_name,
							'ver_real_file'		=> $new_real_file,
							'ver_file_hash'		=> $file_hash,
							'ver_file_size'		=> $file_size,
							'ver_change_time'	=> time(),
							'ver_change_user'	=> $this->user->data['user_id'],
						]) . ' WHERE dl_id = ' . (int) $df_id . ' AND ver_id = ' . (int) $file_version;
		
						$this->db->sql_query($sql);
					}
				}
		
				$sql_array = [
					'description'			=> $description,
					'file_traffic'			=> $file_traffic,
					'long_desc'				=> $long_desc,
					'free'					=> $file_free,
					'extern'				=> $file_extern,
					'cat'					=> $cat_id,
					'hacklist'				=> $hacklist,
					'hack_author'			=> $hack_author,
					'hack_author_email'		=> $hack_author_email,
					'hack_author_website'	=> $hack_author_web,
					'hack_dl_url'			=> $hack_dl_url,
					'test'					=> $test,
					'req'					=> $require,
					'todo'					=> $todo,
					'warning'				=> $warning,
					'mod_desc'				=> $mod_desc,
					'mod_list'				=> $mod_list,
					'desc_uid'				=> $desc_uid,
					'desc_bitfield'			=> $desc_bitfield,
					'desc_flags'			=> $desc_flags,
					'long_desc_uid'			=> $long_desc_uid,
					'long_desc_bitfield'	=> $long_desc_bitfield,
					'long_desc_flags'		=> $long_desc_flags,
					'mod_desc_uid'			=> $mod_desc_uid,
					'mod_desc_bitfield'		=> $mod_desc_bitfield,
					'mod_desc_flags'		=> $mod_desc_flags,
					'warn_uid'				=> $warn_uid,
					'warn_bitfield'			=> $warn_bitfield,
					'warn_flags'			=> $warn_flags,
					'approve'				=> $approve,
				];
		
				if ($df_id && (!$file_option || ($file_option == 2 && !$file_version)))
				{
					$sql_array += [
						'file_name'		=> ($file_name) ? $file_name : $dl_file['file_name'],
						'real_file'		=> $new_real_file,
						'file_hash'		=> $file_hash,
						'file_size'		=> ($file_size) ? $file_size : $dl_file['file_size'],
						'hack_version'	=> ($hack_version) ? $hack_version : $dl_file['hack_version'],
					];
				}
				else
				{
					$sql_array += [
						'file_name'		=> ($df_id) ? $dl_file['file_name'] : $file_name,
						'real_file'		=> ($df_id) ? $dl_file['real_file'] : $new_real_file,
						'file_hash'		=> ($df_id) ? $dl_file['file_hash'] : $file_hash,
						'file_size'		=> ($df_id) ? $dl_file['file_size'] : $file_size,
						'hack_version'	=> ($df_id) ? $dl_file['hack_version'] : $hack_version,
					];
				}
		
				if($df_id)
				{
					if (!$change_time)
					{
						$sql_array += [
							'change_time' => $current_time,
							'change_user' => $current_user,
						];
					}
		
					if ($click_reset)
					{
						$sql_array += [
							'klicks' => 0,
						];
					}

					/**
					 * Save additional data for the download
					 *
					 * @event 		dlext.acp_files_edit_sql_insert_before
					 * @var int		df_id			download ID
					 * @var array	sql_array		array of download's data for storage
					 * @since 8.1.0-RC2
					 */
					$vars = array(
						'df_id',
						'sql_array',
					);
					extract($this->phpbb_dispatcher->trigger_event('dlext.acp_files_edit_sql_insert_before', compact($vars)));

					$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE id = ' . (int) $df_id;
					$this->db->sql_query($sql);
		
					$message = $this->language->lang('DOWNLOAD_UPDATED');
				}
				else
				{
					$sql_array += [
						'change_time'	=> $current_time,
						'change_user'	=> $current_user,
						'add_time'		=> $current_time,
						'add_user'		=> $current_user,
					];
		
					$sql = 'INSERT INTO ' . DOWNLOADS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_array);
					$this->db->sql_query($sql);
					$next_id = $this->db->sql_nextid();

					/**
					 * Save additional data for the download
					 *
					 * @event 		dlext.acp_files_add_sql_insert_after
					 * @var int		next_id			download ID
					 * @var array	sql_array		array of download's data for storage
					 * @since 8.1.0-RC2
					 */
					$vars = array(
						'next_id',
						'sql_array',
					);
					extract($this->phpbb_dispatcher->trigger_event('dlext.acp_files_add_sql_insert_after', compact($vars)));

					$message = $this->language->lang('DOWNLOAD_ADDED');
				}
		
				$dl_t_id = ($df_id) ? $df_id : $next_id;

				$thumb_form_name	= 'thumb_name';
				$thumb_message		= '';

				if ($this->config['dl_thumb_fsize'] && $index[$cat_id]['allow_thumbs'])
				{
					$allow_thumbs_upload = true;
				}
				else
				{
					$allow_thumbs_upload = false;
				}

				/**
				 * Manipulate thumbnail upload
				 *
				 * @event 		dlext.acp_edit_thumbnail_before
			 	 * @var string 	thumb_form_name			thumbnail upload form field
				 * @var bool  	allow_thumbs_upload		enable/disable thumbnail upload
				 * @since 8.1.0-RC2
				 */
				
				$vars = array(
					'thumb_form_name',
					'allow_thumbs_upload',
				);
				extract($this->phpbb_dispatcher->trigger_event('dlext.acp_edit_thumbnail_before', compact($vars)));

				if ($allow_thumbs_upload)
				{
					$thumb_error = false;

					$this->user->add_lang('posting');

					$min_pic_width = 1;

					$factory = $this->phpbb_container->get('files.factory');
					$allowed_imagetypes = ['gif','png','jpg','bmp'];
					$upload = $factory->get('upload')
						->set_allowed_extensions($allowed_imagetypes)
						->set_max_filesize($this->config['dl_thumb_fsize'])
						->set_allowed_dimensions(
							$min_pic_width,
							$min_pic_width,
							$this->config['dl_thumb_xsize'],
							$this->config['dl_thumb_ysize'])
						->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : false));


					$upload_file = $this->request->file($thumb_form_name);
					unset($upload_file['local_mode']);
					$thumb_file = $upload->handle_upload('files.types.form', $thumb_form_name);

					$thumb_size = $upload_file['size'];
					$thumb_temp = $upload_file['tmp_name'];
					$thumb_name = $upload_file['name'];

					if (!empty($thumb_file->error) && $thumb_name)
					{
						$thumb_file->remove();
						trigger_error(implode('<br />', $thumb_file->error), E_USER_ERROR);
					}

					if ($thumb_name)
					{
						$pic_size = getimagesize($thumb_temp);
						$pic_width = $pic_size[0];
						$pic_height = $pic_size[1];

						if (!$pic_width || !$pic_height)
						{
							$thumb_file->remove();
							$thumb_error = true;
						}

						if ($pic_width > $this->config['dl_thumb_xsize'] || $pic_height > $this->config['dl_thumb_ysize'] || (sprintf("%u", @filesize($thumb_temp)) > $this->config['dl_thumb_fsize']))
						{
							$thumb_file->remove();
							$thumb_error = true;
						}
					}
				}

				/**
				 * Manipulate thumbnail data before storage
				 *
				 * @event 		dlext.acp_files_sql_thumbnail_before
				 * @var string	foreign_thumb_message	message after manipulate thumbnail
				 * @var bool	thumb_error				thumbnail error (true to break here)
				 * @var string	thumb_name				thumbnail name (true to avoid overwrite foreign storage)
				 * @var int		df_id					download ID
				 * @var array	sql_array				array of download's data for storage
				 * @since 8.1.0-RC2
				 */
				$foreign_thumb_message = '';
				$vars = array(
					'foreign_thumb_message',
					'thumb_error',
					'thumb_name',
					'df_id',
					'sql_array',
				);
				extract($this->phpbb_dispatcher->trigger_event('dlext.acp_files_sql_thumbnail_before', compact($vars)));

				if (!$thumb_error && isset($thumb_name) && $thumb_name != '')
				{
					$df_id = ($df_id) ? $df_id : $this->db->sql_nextid();
					@unlink(DL_EXT_FILEBASE_PATH . 'thumbs/' . $dl_file['thumbnail']);
					@unlink(DL_EXT_FILEBASE_PATH . 'thumbs/' . $df_id . '_' . $thumb_name);

					$upload_file['name'] = $df_id . '_' . $thumb_name;
					$thumb_file->set_upload_ary($upload_file);
					$dest_folder = str_replace($this->root_path, '', substr(DL_EXT_FILEBASE_PATH . 'thumbs/', 0, -1));

					$error = $thumb_file->move_file($dest_folder, false, false, CHMOD_ALL);
					$thumb_message = '<br />' . $this->language->lang('DL_THUMB_UPLOAD');

					$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'thumbnail' => $df_id . '_' . $thumb_name]) . ' WHERE id = ' . (int) $df_id;
						$this->db->sql_query($sql);
				}

				if ($foreign_thumb_message)
				{
					$thumb_message = '<br />' . $foreign_thumb_message;
				}

				if ($del_thumb)
				{
					$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'thumbnail' => '']) . ' WHERE id = ' . (int) $df_id;
					$this->db->sql_query($sql);

					@unlink(DL_EXT_FILEBASE_PATH . 'thumbs/' . $dl_file['thumbnail']);

					$thumb_message = '<br />' . $this->language->lang('DL_THUMB_DEL');
				}

				// Update Custom Fields
				$cp->update_profile_field_data($dl_t_id, $cp_data);

				if (!$this->config['dl_disable_email'] && !$send_notify && $approve)
				{
					if ($df_id)
					{
						$sql = 'SELECT fav_user_id FROM ' . DL_FAVORITES_TABLE . '
								WHERE fav_dl_id = ' . (int) $df_id . '
								AND ' . $this->db->sql_in_set('fav_user_id', $this->dlext_auth->dl_auth_users($cat_id, 'auth_view'));
						$result = $this->db->sql_query($sql);

						$processing_user = [];

						while ($row = $this->db->sql_fetchrow($result))
						{
							$processing_user[] = $row['fav_user_id'];
						}

						$this->db->sql_freeresult($result);

						$notification_data = [
							'user_ids'		=> $processing_user,
							'description'	=> $description,
							'long_desc'		=> $long_desc,
							'df_id'			=> $df_id,
							'cat_name'		=> $index[$cat_id]['cat_name_nav'],
						];

						$notification->add_notifications('oxpus.dlext.notification.type.update', $notification_data);
						$notification->delete_notifications('oxpus.dlext.notification.type.approve', $df_id);

						$this->dlext_topic->update_topic($dl_file['dl_topic'], $df_id);
					}
					else
					{
						$notification_data = [
							'user_ids'		=> $this->dlext_auth->dl_auth_users($cat_id, 'auth_view'),
							'description'	=> $description,
							'long_desc'		=> $long_desc,
							'df_id'			=> $dl_t_id,
							'cat_name'		=> $index[$cat_id]['cat_name_nav'],
						];
	
						$notification->add_notifications('oxpus.dlext.notification.type.dlext', $notification_data);
	
						$this->dlext_topic->gen_dl_topic($dl_t_id);
					}
				}

				if (!$approve)
				{
					$item_id = ($dl_t_id) ? $dl_t_id : $df_id;
	
					$notification_data = [
						'user_ids'		=> $this->dlext_auth->dl_auth_users($cat_id, 'auth_mod'),
						'description'	=> $description,
						'long_desc'		=> $long_desc,
						'df_id'			=> $item_id,
						'cat_name'		=> $index[$cat_id]['cat_name_nav'],
					];

					$notification->add_notifications('oxpus.dlext.notification.type.approve', $notification_data);
					$notification->delete_notifications('oxpus.dlext.notification.type.update', $item_id);
				}
			}
		
			if ($df_id)
			{
				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILE_EDIT', false, [$description]);
			}
			else
			{
				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILE_ADD', false, [$description]);
			}
		
			// Purge the files cache
			@unlink(DL_EXT_CACHE_PATH . 'data_dl_cat_counts.' . $this->phpEx);
			@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_p.' . $this->phpEx);
			@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_preset.' . $this->phpEx);
		
			$ver_message = '';
		
			if ($new_version)
			{
				$version_url	= $this->helper->route('oxpus_dlext_version', ['ver_id' => $new_version]);
				$ver_message	= '<br /><br />' . $this->language->lang('CLICK_VIEW_NEW_VERSION', '<a href="' . $version_url . '">', '</a>');
			}
		
			$message .= $thumb_message . "<br /><br />" . $this->language->lang('CLICK_RETURN_DOWNLOADADMIN', '<a href="' . $this->u_action . '&amp;cat_id=' . $cat_id . '">', '</a>') . $ver_message . adm_back_link($this->u_action);
		
			trigger_error($message);
		}
		else if($action == 'delete')
		{
			if (confirm_box(true))
			{
				$sql = 'SELECT ver_id, dl_id, ver_real_file FROM ' . DL_VERSIONS_TABLE . '
					WHERE dl_id = ' . (int) $df_id;
				$result = $this->db->sql_query($sql);
		
				$ver_ids = [];
				$real_ver_file = [];
				while ($row = $this->db->sql_fetchrow($result))
				{
					$real_ver_file[$row['dl_id']] = $row['ver_real_file'];
				}
		
				$this->db->sql_freeresult($result);
		
				if ($del_file)
				{
					$path = $index[$cat_id]['cat_path'];
					$file_name = $dl_file['real_file'];
		
					@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . $file_name);
		
					if (isset($real_ver_file[$df_id]))
					{
						for ($j = 0; $j < count($real_ver_file[$df_id]); ++$j)
						{
							@copy(DL_EXT_FILEBASE_PATH. 'downloads/' . $old_path . $real_ver_file[$df_id][$j], DL_EXT_FILEBASE_PATH. 'downloads/' . $new_path . $real_ver_file[$df_id][$j]);
							@chmod(DL_EXT_FILEBASE_PATH. 'downloads/' . $new_path . $real_ver_file[$df_id][$j], 0777);
							@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $old_path . $real_ver_file[$df_id][$j]);
						}
					}
		
					$sql = 'SELECT file_type, real_name FROM ' . DL_VER_FILES_TABLE . '
							WHERE dl_id = ' . (int) $df_id;
					$result = $this->db->sql_query($sql);
		
					while ($row = $this->db->sql_fetchrow($result))
					{
						switch ($row['file_type'])
						{
							case 1:
								@unlink(DL_EXT_FILEBASE_PATH. 'version/images/' . $row['real_name']);
							break;
							default:
								@unlink(DL_EXT_FILEBASE_PATH. 'version/files/' . $row['real_name']);
						}
					}
		
					$this->db->sql_freeresult($result);
				}
		
				@unlink(DL_EXT_FILEBASE_PATH . 'thumbs/' . $dl_file['thumbnail']);
		
				$sql = 'SELECT cat, description, dl_topic FROM ' . DOWNLOADS_TABLE . '
					WHERE id = ' . (int) $df_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);
		
				if ($row['dl_topic'])
				{
					$del_t_id = [];
					$del_t_id[] = $row['dl_topic'];
					$dl_t_ids[$df_id] = $row['dl_topic'];
					$this->dlext_topic->delete_topic($del_t_id, $topic_drop_mode, $dl_t_ids);
				}
		
				$dl_desc	= $row['description'];
				$dl_cat		= $row['cat'];
		
				$sql = 'DELETE FROM ' . DOWNLOADS_TABLE . '
					WHERE id = ' . (int) $df_id;
				$this->db->sql_query($sql);
		
				if (!empty($ver_ids))
				{
					$sql = 'DELETE FROM ' . DL_VERSIONS_TABLE . '
						WHERE ' . $this->db->sql_in_set('ver_id', $ver_ids);
					$this->db->sql_query($sql);
		
					$sql = 'DELETE FROM ' . DL_VER_FILES_TABLE . '
						WHERE ' . $this->db->sql_in_set('ver_id', $ver_ids);
					$this->db->sql_query($sql);
				}
		
				$sql = 'DELETE FROM ' . DL_STATS_TABLE . '
					WHERE id = ' . (int) $df_id;
				$this->db->sql_query($sql);
		
				$sql = 'DELETE FROM ' . DL_COMMENTS_TABLE . '
					WHERE id = ' . (int) $df_id;
				$this->db->sql_query($sql);
		
				$sql = 'DELETE FROM ' . DL_NOTRAF_TABLE . '
					WHERE dl_id = ' . (int) $df_id;
				$this->db->sql_query($sql);
		
				$sql = 'DELETE FROM ' . DL_FIELDS_DATA_TABLE . '
					WHERE df_id = ' . (int) $df_id;
				$this->db->sql_query($sql);
		
				$sql = 'DELETE FROM ' . DL_RATING_TABLE . '
					WHERE dl_id = ' . (int) $df_id;
				$this->db->sql_query($sql);
		
				$sql = 'DELETE FROM ' . DL_FAVORITES_TABLE . '
					WHERE fav_dl_id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				/**
				 * Workflow after delete download
				 *
				 * @event 		dlext.acp_files_delete_download_after
				 * @var int		df_id		download ID
				 * @var int		dl_cat		download category ID
				 * @since 8.1.0-RC2
				 */
				$vars = array(
					'df_id',
					'dl_cat',
				);
				extract($this->phpbb_dispatcher->trigger_event('dlext.acp_files_delete_download_after', compact($vars)));

				$notification->delete_notifications([
					'oxpus.dlext.notification.type.approve',
					'oxpus.dlext.notification.type.broken',
					'oxpus.dlext.notification.type.dlext',
					'oxpus.dlext.notification.type.update',
					'oxpus.dlext.notification.type.capprove',
					'oxpus.dlext.notification.type.comments',
				], $df_id);

				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_DEL_FILE', false, [$dl_desc]);
		
				// Purge the files cache
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_cat_counts.' . $this->phpEx);
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_preset.' . $this->phpEx);
		
				$message = $this->language->lang('DOWNLOAD_REMOVED') . "<br /><br />" . $this->language->lang('CLICK_RETURN_DOWNLOADADMIN', '<a href="' . $this->u_action . '&amp;cat_id=' . $cat_id . '">', '</a>') . adm_back_link($this->u_action);
		
				trigger_error($message);
			}
			else
			{
				$description = $dl_file['description'];

				$this->template->assign_var('S_DELETE_FILES_CONFIRM', true);
				$this->template->assign_var('S_DELETE_TOPIC_CONFIRM', true);

				$s_hidden_fields = [
					'cat_id'	=> $cat_id,
					'df_id'		=> $df_id,
					'action'	=> 'delete',
				];

				confirm_box(false, $this->language->lang('DL_CONFIRM_DELETE_SINGLE_FILE', $description), build_hidden_fields($s_hidden_fields), 'dl_confirm_body.html');
			}
		}
		else if($action == 'downloads_order')
		{
			$sql = 'SELECT sort, description FROM ' . DOWNLOADS_TABLE . '
				WHERE id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
		
			$dl_desc = $row['description'];
			$dl_sort = $row['sort'] - $move;
		
			$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
				'sort' => $dl_sort]) . ' WHERE id = ' . (int) $df_id;
			$this->db->sql_query($sql);
		
			$sql = 'SELECT id FROM ' . DOWNLOADS_TABLE . '
				WHERE cat = ' . (int) $cat_id . '
				ORDER BY sort ASC';
			$result = $this->db->sql_query($sql);
		
			$i = 10;
		
			while($row = $this->db->sql_fetchrow($result))
			{
				$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'sort' => $i]) . ' WHERE id = ' . (int) $row['id'];
				$this->db->sql_query($sql);
		
				$i += 10;
			}
		
			$this->db->sql_freeresult($result);
		
			$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILE_MOVE', false, [$dl_desc]);
		
			$action = '';
		}
		else if($action == 'downloads_order_all')
		{
			$sql = 'SELECT cat_name FROM ' . DL_CAT_TABLE . '
				WHERE id = ' . (int) $cat_id;
			$result = $this->db->sql_query($sql);
			$cat_name = $this->db->sql_fetchfield('cat_name');
			$this->db->sql_freeresult($result);
		
			$sql = 'SELECT id FROM ' . DOWNLOADS_TABLE . '
				WHERE cat = ' . (int) $cat_id . '
				ORDER BY description ASC';
			$result = $this->db->sql_query($sql);
		
			$i = 10;
		
			while($row = $this->db->sql_fetchrow($result))
			{
				$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'sort' => $i]) . ' WHERE id = ' . (int) $row['id'];
				$this->db->sql_query($sql);
		
				$i += 10;
			}
		
			$this->db->sql_freeresult($result);
		
			$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILES_SORT', false, [$cat_name]);
		
			$action = '';
		}
		
		if ($action == '')
		{
			$sql = 'SELECT hacklist, hack_version, file_name, real_file, description, desc_uid, desc_bitfield, desc_flags, id, free, extern, test, cat, klicks, overall_klicks, file_traffic, file_size, approve
					FROM ' . DOWNLOADS_TABLE . '
				WHERE cat = ' . (int) $cat_id . '
				ORDER BY sort';
			$result = $this->db->sql_query($sql);
			$total_files = $this->db->sql_affectedrows($result);
		
			while ($row = $this->db->sql_fetchrow($result))
			{
				$file_path		= $index[$cat_id]['cat_path'];
				$hacklist		= ($row['hacklist'] == 1) ? $this->language->lang('YES') : $this->language->lang('NO');
				$version		= $row['hack_version'];
				$description	= $row['description'];
				$file_id		= $row['id'];
				$file_free		= $row['free'];
				$file_extern	= $row['extern'];
				$test			= ($row['test']) ? '['.$row['test'].']' : '';
				$cat_id			= $row['cat'];
				$file_name		= ($file_extern) ? $this->language->lang('DL_EXTERN') : $this->language->lang('DOWNLOAD') . ': ' . $row['file_name'] . '<br />{' . $row['real_file'] . '}';
		
				$desc_uid		= $row['desc_uid'];
				$desc_bitfield	= $row['desc_bitfield'];
				$desc_flags		= $row['desc_flags'];
				$description	= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);
		
				switch ($file_free)
				{
					case 1:
						$file_free_out = $this->language->lang('DL_FREE');
						break;
		
					case 2:
						$file_free_out = $this->language->lang('DL_YES_REG');
						break;
		
					default:
						$file_free_out = $this->language->lang('DL_NO');
				}
		
				$file_free_extern_out	= ($file_extern) ? $this->language->lang('DL_EXTERN') : $file_free_out;
		
				$file_klicks			= $row['klicks'];
				$file_overall_klicks	= $row['overall_klicks'];
				$file_traffic			= $row['file_traffic'];
		
				if ($file_traffic)
				{
					$file_traffic = $this->dlext_format->dl_size($file_traffic);
				}
				else
				{
					$file_traffic = $this->language->lang('DL_NOT_AVAILIBLE');
				}
		
				if ($row['file_size'])
				{
					$file_size_kb	= $this->dlext_format->dl_size($row['file_size']);
				}
				else
				{
					$file_size_kb	= $this->language->lang('DL_NOT_AVAILIBLE');
				}
		
				$unapprove = ($row['approve']) ? '' : $this->language->lang('DL_UNAPPROVED');
		
				$dl_edit	= "{$this->u_action}&amp;action=edit&amp;df_id=$file_id";
				$dl_delete	= "{$this->u_action}&amp;action=delete&amp;df_id=$file_id&amp;cat_id=$cat_id";
		
				$dl_move_up		= "{$this->u_action}&amp;action=downloads_order&amp;move=15&amp;df_id=$file_id&amp;cat_id=$cat_id";
				$dl_move_down	= "{$this->u_action}&amp;action=downloads_order&amp;move=-15&amp;df_id=$file_id&amp;cat_id=$cat_id";
		
				$this->template->assign_block_vars('downloads', [
					'DESCRIPTION'			=> $description,
					'TEST'					=> $test,
					'FILE_ID'				=> $file_id,
					'FILE_SIZE'				=> $file_size_kb,
					'FILE_FREE_EXTERN'		=> $file_free_extern_out,
					'FILE_KLICKS'			=> $file_klicks,
					'FILE_TRAFFIC'			=> $file_traffic,
					'UNAPPROVED'			=> $unapprove,
					'FILE_OVERALL_KLICKS'	=> $file_overall_klicks,
					'hacklist'				=> $hacklist,
					'VERSION'				=> $version,
					'FILE_NAME'				=> $file_name,
		
					'U_FILE_EDIT'			=> $dl_edit,
					'U_FILE_DELETE'			=> $dl_delete,
					'U_DOWNLOAD_MOVE_UP'	=> $dl_move_up,
					'U_DOWNLOAD_MOVE_DOWN'	=> $dl_move_down,
				]);
			}
		
			$categories = '<select name="cat_id" onchange="if(this.options[this.selectedIndex].value != -1){ forms[\'cat_id\'].submit() }">';
			$categories .= '<option value="-1">'.$this->language->lang('DL_CHOOSE_CATEGORY').'</option>';
			$categories .= '<option value="-1">----------</option>';
			$categories .= $this->dlext_extra->dl_dropdown(0, 0, $cat_id, 'auth_up');
			$categories .= '</select>';
		
			$this->template->assign_vars([
				'DL_FILE_SIZE'			=> $this->language->lang('DL_FILE_SIZE'),
				'SORT'					=> $this->language->lang('SORT_BY') . ' ' . $this->language->lang('DL_NAME') . ' / ' . $this->language->lang('DL_FILE_NAME'),
		
				'CAT'					=> $cat_id,
				'CATEGORIES'			=> $categories,
				'DL_COUNT'				=> $total_files . '&nbsp;' . $this->language->lang('DOWNLOADS'),
		
				'S_DOWNLOADS_ACTION'	=> $this->u_action,
				'S_HIDDEN_FIELDS'		=> build_hidden_fields(['cat_id' => $cat_id]),
		
				'U_DOWNLOAD_ORDER_ALL'	=> "{$this->u_action}&amp;action=downloads_order_all&amp;cat_id=$cat_id",
			]);
		
			if ($total_files)
			{
				$this->template->assign_var('S_LIST_DOWNLOADS', true);
			}
		}
	}
}
