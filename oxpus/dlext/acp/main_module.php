<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\acp;

/**
* @package acp
*/
class main_module
{
	var $u_action;
	var $edit_lang_id;
	var $lang_defs;
	var $user;
	var $request;

	function main($id, $mode)
	{
		global $db, $user, $auth, $cache, $dl_cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $phpbb_log, $language;
		global $phpbb_extension_manager, $table_prefix, $phpbb_container, $phpbb_path_helper;

		$config			= $phpbb_container->get('config');
		$language		= $phpbb_container->get('language');
		$request		= $phpbb_container->get('request');
		$template		= $phpbb_container->get('template');
		$helper			= $phpbb_container->get('controller.helper');

		$action			= $request->variable('action', '');
		$submit			= $request->variable('submit', '');
		$cancel			= $request->variable('cancel', '');
		$confirm		= $request->variable('confirm', '');
		$mode			= $request->variable('mode', 'overview');
		$delete			= $request->variable('delete', '');
		$sorting		= $request->variable('sorting', '');
		$sort_order		= $request->variable('sort_order', '');
		$filtering		= $request->variable('filtering', '');
		$filter_string	= $request->variable('filter_string', '');
		$func			= $request->variable('func', '');
		$username		= $request->variable('username', '');
		$add			= $request->variable('add', '');
		$edit			= $request->variable('edit', '');
		$move			= $request->variable('move', '');
		$save_cat		= $request->variable('save_cat', '');
		$path			= $request->variable('path', '');
		$dircreate		= $request->variable('dircreate', '');
		$dir_name		= $request->variable('dir_name', '', true);
		$new_path		= $request->variable('new_path', '');
		$new_cat		= $request->variable('new_cat', '');
		$file_command	= $request->variable('file_command', '');
		$file_assign	= $request->variable('file_assign', '');
		$x				= $request->variable('x', '');
		$y				= $request->variable('y', '');
		$z				= $request->variable('z', '');

		$df_id			= $request->variable('df_id', 0);
		$cat_id			= $request->variable('cat_id', 0);
		$new_cat_id		= $request->variable('new_cat_id', 0);
		$start			= $request->variable('start', 0);
		$show_guests	= $request->variable('show_guests', 0);
		$user_id		= $request->variable('user_id', 0);
		$user_traffic	= $request->variable('user_traffic', 0);
		$all_traffic	= $request->variable('all_traffic', 0);
		$group_id		= $request->variable('group_id', 0);
		$group_traffic	= $request->variable('group_traffic', 0);
		$group_id		= $request->variable('g', 0);
		$auth_view		= $request->variable('auth_view', 0);
		$auth_dl		= $request->variable('auth_dl', 0);
		$auth_up		= $request->variable('auth_up', 0);
		$auth_mod		= $request->variable('auth_mod', 0);
		$del_file		= $request->variable('del_file', 0);
		$click_reset	= $request->variable('click_reset', 0);

		// Define the ext path
		$ext_path					= $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$ext_path_web				= $phpbb_path_helper->update_web_root_path($ext_path);
		$ext_path_ajax				= $ext_path_web . 'assets/javascript/';

		// Define the basic file storage placement
		if ($config['dl_download_dir'] == 2)
		{
			$filebase_prefix = $filebase_web_prefix = $folder_base = $phpbb_root_path . 'store/oxpus/dlext/';
			$folder_desc = $ext_path_web . 'files/';
		}
		else
		{
			$filebase_prefix = $folder_base = $ext_path . 'files/';
			$filebase_web_prefix = $ext_path_web . 'files/';
			$folder_desc = $phpbb_root_path . 'store/oxpus/dlext/';
		}

		define('DL_EXT_CACHE_FOLDER',		$filebase_prefix . 'cache/');
		define('DL_EXT_THUMBS_FOLDER',		$filebase_prefix . 'thumbs/');
		define('DL_EXT_FILES_FOLDER',		$filebase_prefix . 'downloads/');
		define('DL_EXT_FILES_WEBFOLDER',	$filebase_web_prefix . 'downloads/');
		define('DL_EXT_VER_FILES_FOLDER',	$filebase_prefix . 'version/files/');
		define('DL_EXT_VER_FILES_WFOLDER',	$filebase_web_prefix . 'version/files/');
		define('DL_EXT_VER_IMAGES_FOLDER',	$filebase_prefix . 'version/images/');
		define('DL_EXT_VER_IMAGES_WFOLDER',	$filebase_web_prefix . 'version/images/');

		include_once($ext_path . 'phpbb/helpers/dl_constants.' . $phpEx);

		$auth->acl($user->data);
		if (!$auth->acl_get('a_'))
		{
			trigger_error('DL_NO_PERMISSION', E_USER_WARNING);
		}

		$this->tpl_name = 'acp_downloads';

		$user->data['dl_enable_desc'] = $user->data['dl_enable_rule'] = true;

		/*
		* initiate the help system
		*/
		$helper = $phpbb_container->get('controller.helper');
		$template->assign_vars(array(
			'ICON_DL_HELP'	=> '<i class="icon fa-info-circle fa-fw"></i>',
			'U_HELP_POPUP'	=> $helper->route('oxpus_dlext_controller', array('view' => 'help')),
		));

		/*
		* include and create the main class
		*/
		include($ext_path . 'phpbb/classes/class_dlmod.' . $phpEx);
		$dl_mod = new \oxpus\dlext\phpbb\classes\ dl_mod($phpbb_root_path, $phpEx, $ext_path);
		$dl_mod->register();
		\oxpus\dlext\phpbb\classes\ dl_init::init($ext_path);

		if ($action == 'edit')
		{
			$enable_desc = $enable_rule = true;
		}

		$basic_link = $this->u_action;

		if ($cancel)
		{
			redirect($basic_link);
		}

		/*
		* create overall mini statistics
		*/
		$total_size = \oxpus\dlext\phpbb\classes\ dl_physical::read_dl_sizes();
		$total_tsize = \oxpus\dlext\phpbb\classes\ dl_physical::read_dl_sizes(DL_EXT_THUMBS_FOLDER);
		$total_vfsize = \oxpus\dlext\phpbb\classes\ dl_physical::read_dl_sizes(DL_EXT_VER_FILES_FOLDER);
		$total_vtsize = \oxpus\dlext\phpbb\classes\ dl_physical::read_dl_sizes(DL_EXT_VER_IMAGES_FOLDER);
		$total_dl = \oxpus\dlext\phpbb\classes\ dl_main::get_sublevel_count();
		$total_extern = sizeof(\oxpus\dlext\phpbb\classes\ dl_files::all_files(0, '', 'ASC', "AND extern = 1", 0, true, 'id'));

		$physical_limit = $config['dl_physical_quota'];
		$total_size = ($total_size > $physical_limit) ? $physical_limit : $total_size;

		$physical_limit = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($physical_limit, 2);

		$acp_module_path = $ext_path . 'phpbb/acp/dl_admin_';

		/*
		* include the choosen module
		*/
		switch($mode)
		{
			case 'overview':
				$this->page_title = 'ACP_DOWNLOADS';

				if ($request->variable('reset_clicks', ''))
				{
					if (!confirm_box(true))
					{
						confirm_box(false, $language->lang('DL_ACP_CONFIRM_RESET_CLICKS'), build_hidden_fields(array(
							'i'				=> $id,
							'mode'			=> $mode,
							'reset_clicks'	=> true,
						)));
					 }
					 else
					 {
					 	$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array('klicks' => 0));
					 	$db->sql_query($sql);

						trigger_error($language->lang('DL_ACP_CONFIRM_RESET_FINISH') . adm_back_link($this->u_action));
					 }
				}

				if ($request->variable('reset_stats', ''))
				{
					if (!confirm_box(true))
					{
						confirm_box(false, $language->lang('DL_ACP_CONFIRM_RESET_STATS'), build_hidden_fields(array(
							'i'				=> $id,
							'mode'			=> $mode,
							'reset_stats'	=> true,
						)));
					 }
					 else
					 {
					 	$sql = 'DELETE FROM ' . DL_STATS_TABLE;
					 	$db->sql_query($sql);

						trigger_error($language->lang('DL_ACP_CONFIRM_RESET_FINISH') . adm_back_link($this->u_action));
					 }
				}

				if ($request->variable('reset_cache', ''))
				{
					if (!confirm_box(true))
					{
						confirm_box(false, $language->lang('DL_ACP_CONFIRM_RESET_CACHE'), build_hidden_fields(array(
							'i'				=> $id,
							'mode'			=> $mode,
							'reset_cache'	=> true,
						)));
					 }
					 else
					 {
						$cache->destroy('config');
						@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_auth.' . $phpEx);
						@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_black.' . $phpEx);
						@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_cat_counts.' . $phpEx);
						@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_cats.' . $phpEx);
						@unlink(DL_EXT_CACHE_FOLDER . 'data_dl_file_presets.' . $phpEx);

						trigger_error($language->lang('DL_ACP_CONFIRM_RESET_FINISH') . adm_back_link($this->u_action));
					 }
				}

				if ($request->variable('dl_privacy', ''))
				{
					if (!confirm_box(true))
					{
						confirm_box(false, $language->lang('DL_ACP_CONFIRM_PRIVACY'), build_hidden_fields(array(
							'i'				=> $id,
							'mode'			=> $mode,
							'dl_privacy'	=> true,
						)));
					 }
					 else
					 {
						\oxpus\dlext\phpbb\classes\ dl_privacy::dl_privacy($db);
						trigger_error($language->lang('DL_ACP_CONFIRM_RESET_FINISH') . adm_back_link($this->u_action));
					 }
				}

				$total_size = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($total_size, 2);
				$total_tsize = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($total_tsize, 2);
				$total_vfsize = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($total_vfsize, 2);
				$total_vtsize = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($total_vtsize, 2);

				$remain_traffic = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($config['dl_overall_traffic'] - $config['dl_remain_traffic'], 2);
				$overall_traffic = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($config['dl_overall_traffic']);
				$overall_guest_traffic = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($config['dl_overall_guest_traffic']);
				$remain_guest_traffic = \oxpus\dlext\phpbb\classes\ dl_format::dl_size($config['dl_overall_guest_traffic'] - $config['dl_remain_guest_traffic'], 2);

				$sql = "SELECT SUM(CASE WHEN todo <> '' THEN 1 ELSE 0 END) as todos, SUM(broken) as broken, sum(klicks) as mclick, sum(overall_klicks) as oclick FROM " . DOWNLOADS_TABLE . '
						WHERE approve = ' . true;
				$result	= $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				$mclick	= $row['mclick'];
				$oclick	= $row['oclick'];
				$todos	= $row['todos'];
				$broken	= $row['broken'];

				$index = array();
				$index = \oxpus\dlext\phpbb\classes\ dl_main::full_index();

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

				$sql = 'SELECT count(ver_id) as versions FROM ' . DL_VERSIONS_TABLE;
				$result	= $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				$total_versions	= $row['versions'];

				$template->assign_vars(array(
					'TOTAL_NUM'				=> $total_dl,
					'TOTAL_SIZE'			=> $total_size,
					'TOTAL_LIMIT'			=> $physical_limit,
					'TOTAL_EXTERN'			=> $total_extern,
					'REMAIN_TRAFFIC'		=> ($remain_traffic <= 0) ? $language->lang('DL_ACP_MAIN_STATS_REMAIN_OFF') : $remain_traffic,
					'OVERALL_TRAFFIC'		=> $overall_traffic,
					'REMAIN_GTRAFFIC'		=> ($remain_guest_traffic <= 0) ? $language->lang('DL_ACP_MAIN_STATS_REMAIN_OFF') : $remain_guest_traffic,
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

					'DL_MANAGEMENT_TITLE'	=> $language->lang('DL_ACP_MANAGEMANT_PAGE'),
					'DL_MANAGEMENT_EXPLAIN'	=> $language->lang('DL_ACP_MANAGEMANT_PAGE_EXPLAIN'),
					'DL_MOD_VERSION'		=> $language->lang('DL_MOD_VERSION', $config['dl_ext_version']),
					'DL_MOD_VERSION_SIMPLE'	=> $config['dl_ext_version'],

					'S_DL_TRAFFIC_OFF'	=> ($config['dl_traffic_off']) ? true : false,
				));

				$template->assign_var('S_DL_OVERVIEW', true);
			break;
			case 'config':
				$this->page_title = 'DL_ACP_CONFIG_MANAGEMENT';
			case 'traffic':
				$this->page_title = 'DL_ACP_TRAFFIC_MANAGEMENT';
			case 'categories':
				$this->page_title = 'DL_ACP_CATEGORIES_MANAGEMENT';
			case 'files':
				$this->page_title = 'DL_ACP_FILES_MANAGEMENT';
			case 'permissions':
				$this->page_title = 'DL_ACP_PERMISSIONS';
			case 'toolbox':
				$this->page_title = 'DL_MANAGE';
			case 'stats':
				$this->page_title = 'DL_ACP_STATS_MANAGEMENT';
			case 'ext_blacklist':
				$this->page_title = 'DL_EXT_BLACKLIST';
			case 'banlist':
				$this->page_title = 'DL_ACP_BANLIST';
			case 'fields':
				$this->page_title = 'DL_ACP_FIELDS';
			case 'browser':
				$this->page_title = 'DL_ACP_BROWSER';
			case 'perm_check':
				$this->page_title = 'DL_ACP_PERM_CHECK';

				include($acp_module_path . $mode . '.' . $phpEx);
			break;
		}

		$template->assign_vars(array(
			'DL_MOD_RELEASE'	=> $language->lang('DL_MOD_VERSION', $config['dl_ext_version']),
		));
	}

	/**
	* Build all Language specific options
	* Taken from acp_profile.php (c) by phpbb.com
	*/
	function build_language_options(&$cp, $field_type, $action = 'create')
	{
		global $language, $config, $db, $request;

		$default_lang_id = (!empty($this->edit_lang_id)) ? $this->edit_lang_id : $this->lang_defs['iso'][$config['default_lang']];

		$sql = 'SELECT lang_id, lang_iso
			FROM ' . LANG_TABLE . '
			WHERE lang_id <> ' . (int) $default_lang_id . '
			ORDER BY lang_english_name';
		$result = $db->sql_query($sql);

		$languages = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$languages[$row['lang_id']] = $row['lang_iso'];
		}
		$db->sql_freeresult($result);

		$options = array();
		$options['lang_name'] = 'string';
		if ($cp->vars['lang_explain'])
		{
			$options['lang_explain'] = 'text';
		}

		switch ($field_type)
		{
			case FIELD_BOOL:
				$options['lang_options'] = 'two_options';
			break;

			case FIELD_DROPDOWN:
				$options['lang_options'] = 'optionfield';
			break;

			case FIELD_TEXT:
			case FIELD_STRING:
				if (strlen($cp->vars['lang_default_value']))
				{
					$options['lang_default_value'] = ($field_type == FIELD_STRING) ? 'string' : 'text';
				}
			break;
		}

		$lang_options = array();

		foreach ($options as $field => $field_type)
		{
			$lang_options[1]['lang_iso'] = $this->lang_defs['id'][$default_lang_id];
			$lang_options[1]['fields'][$field] = array(
				'TITLE'		=> $language->lang('CP_' . strtoupper($field)),
				'FIELD'		=> '<dd>' . ((is_array($cp->vars[$field])) ? implode('<br />', $cp->vars[$field]) : bbcode_nl2br($cp->vars[$field])) . '</dd>'
			);

			if ($language->lang('CP_' . strtoupper($field) . '_EXPLAIN'))
			{
				$lang_options[1]['fields'][$field]['EXPLAIN'] = $language->lang('CP_' . strtoupper($field) . '_EXPLAIN');
			}
		}

		foreach ($languages as $lang_id => $lang_iso)
		{
			$lang_options[$lang_id]['lang_iso'] = $lang_iso;
			foreach ($options as $field => $field_type)
			{
				$value = ($action == 'create') ? utf8_normalize_nfc($request->variable('l_' . $field, array(0 => ''), true)) : $cp->vars['l_' . $field];
				if ($field == 'lang_options')
				{
					$var = (!isset($cp->vars['l_lang_options'][$lang_id]) || !is_array($cp->vars['l_lang_options'][$lang_id])) ? $cp->vars['lang_options'] : $cp->vars['l_lang_options'][$lang_id];

					switch ($field_type)
					{
						case 'two_options':

							$lang_options[$lang_id]['fields'][$field] = array(
								'TITLE'		=> $language->lang('CP_' . strtoupper($field)),
								'FIELD'		=> '
											<dd><input class="medium" name="l_' . $field . '[' . $lang_id . '][]" value="' . ((isset($value[$lang_id][0])) ? $value[$lang_id][0] : $var[0]) . '" /> ' . $language->lang('FIRST_OPTION') . '</dd>
											<dd><input class="medium" name="l_' . $field . '[' . $lang_id . '][]" value="' . ((isset($value[$lang_id][1])) ? $value[$lang_id][1] : $var[1]) . '" /> ' . $language->lang('SECOND_OPTION') . '</dd>'
							);
						break;

						case 'optionfield':
							$value = ((isset($value[$lang_id])) ? ((is_array($value[$lang_id])) ?  implode("\n", $value[$lang_id]) : $value[$lang_id]) : implode("\n", $var));
							$lang_options[$lang_id]['fields'][$field] = array(
								'TITLE'		=> $language->lang('CP_' . strtoupper($field)),
								'FIELD'		=> '<dd><textarea name="l_' . $field . '[' . $lang_id . ']" rows="7" cols="80">' . $value . '</textarea></dd>'
							);
						break;
					}

					if ($language->lang('CP_' . strtoupper($field) . '_EXPLAIN'))
					{
						$lang_options[$lang_id]['fields'][$field]['EXPLAIN'] = $language->lang('CP_' . strtoupper($field) . '_EXPLAIN');
					}
				}
				else
				{
					$var = ($action == 'create' || !is_array($cp->vars[$field])) ? $cp->vars[$field] : $cp->vars[$field][$lang_id];

					$lang_options[$lang_id]['fields'][$field] = array(
						'TITLE'		=> $language->lang('CP_' . strtoupper($field)),
						'FIELD'		=> ($field_type == 'string') ? '<dd><input class="medium" type="text" name="l_' . $field . '[' . $lang_id . ']" value="' . ((isset($value[$lang_id])) ? $value[$lang_id] : $var) . '" /></dd>' : '<dd><textarea name="l_' . $field . '[' . $lang_id . ']" rows="3" cols="80">' . ((isset($value[$lang_id])) ? $value[$lang_id] : $var) . '</textarea></dd>'
					);

					if ($language->lang('CP_' . strtoupper($field) . '_EXPLAIN') != 'CP_' . strtoupper($field) . '_EXPLAIN')
					{
						$lang_options[$lang_id]['fields'][$field]['EXPLAIN'] = $language->lang('CP_' . strtoupper($field) . '_EXPLAIN');
					}
				}
			}
		}

		return $lang_options;
	}

	/**
	* Save Profile Field
	* Taken from acp_profile.php (c) by phpbb.com
	*/
	function save_profile_field(&$cp, $field_type, $action = 'create')
	{
		global $db, $config, $user, $request, $phpbb_log, $language;

		$field_id = $request->variable('field_id', 0);

		// Collect all information, if something is going wrong, abort the operation
		$profile_sql = $profile_lang = $empty_lang = $profile_lang_fields = array();

		$default_lang_id = (!empty($this->edit_lang_id)) ? $this->edit_lang_id : $this->lang_defs['iso'][$config['default_lang']];

		if ($action == 'create')
		{
			$sql = 'SELECT MAX(field_order) as max_field_order
				FROM ' . DL_FIELDS_TABLE;
			$result = $db->sql_query($sql);
			$new_field_order = (int) $db->sql_fetchfield('max_field_order');
			$db->sql_freeresult($result);

			$field_ident = $cp->vars['field_ident'];
		}

		// Save the field
		$profile_fields = array(
			'field_length'			=> $cp->vars['field_length'],
			'field_minlen'			=> $cp->vars['field_minlen'],
			'field_maxlen'			=> $cp->vars['field_maxlen'],
			'field_novalue'			=> $cp->vars['field_novalue'],
			'field_default_value'	=> $cp->vars['field_default_value'],
			'field_validation'		=> $cp->vars['field_validation'],
			'field_required'		=> $cp->vars['field_required'],
		);

		if ($action == 'create')
		{
			$profile_fields += array(
				'field_type'		=> $field_type,
				'field_ident'		=> $field_ident,
				'field_name'		=> $field_ident,
				'field_order'		=> $new_field_order + 1,
				'field_active'		=> 1
			);

			$sql = 'INSERT INTO ' . DL_FIELDS_TABLE . ' ' . $db->sql_build_array('INSERT', $profile_fields);
			$db->sql_query($sql);

			$field_id = $db->sql_nextid();
		}
		else
		{
			$sql = 'UPDATE ' . DL_FIELDS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $profile_fields) . '
				WHERE field_id = ' . (int) $field_id;
			$db->sql_query($sql);
		}

		if ($action == 'create')
		{
			$field_ident = 'pf_' . $field_ident;
			$profile_sql[] = $this->add_field_ident($field_ident, $field_type);
		}

		$sql_ary = array(
			'lang_name'				=> $cp->vars['lang_name'],
			'lang_explain'			=> $cp->vars['lang_explain'],
			'lang_default_value'	=> $cp->vars['lang_default_value']
		);

		if ($action == 'create')
		{
			$sql_ary['field_id'] = $field_id;
			$sql_ary['lang_id'] = $default_lang_id;

			$profile_sql[] = 'INSERT INTO ' . DL_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		}
		else
		{
			$this->update_insert(DL_LANG_TABLE, $sql_ary, array('field_id' => $field_id, 'lang_id' => $default_lang_id));
		}

		if (is_array($cp->vars['l_lang_name']) && sizeof($cp->vars['l_lang_name']))
		{
			foreach ($cp->vars['l_lang_name'] as $lang_id => $data)
			{
				if (($cp->vars['lang_name'] != '' && $cp->vars['l_lang_name'][$lang_id] == '')
					|| ($cp->vars['lang_explain'] != '' && $cp->vars['l_lang_explain'][$lang_id] == '')
					|| ($cp->vars['lang_default_value'] != '' && $cp->vars['l_lang_default_value'][$lang_id] == ''))
				{
					$empty_lang[$lang_id] = true;
					break;
				}

				if (!isset($empty_lang[$lang_id]))
				{
					$profile_lang[] = array(
						'field_id'		=> $field_id,
						'lang_id'		=> $lang_id,
						'lang_name'		=> $cp->vars['l_lang_name'][$lang_id],
						'lang_explain'	=> (isset($cp->vars['l_lang_explain'][$lang_id])) ? $cp->vars['l_lang_explain'][$lang_id] : '',
						'lang_default_value'	=> (isset($cp->vars['l_lang_default_value'][$lang_id])) ? $cp->vars['l_lang_default_value'][$lang_id] : ''
					);
				}
			}

			foreach ($empty_lang as $lang_id => $NULL)
			{
				$sql = 'DELETE FROM ' . DL_LANG_TABLE . '
					WHERE field_id = ' . (int) $field_id . '
					AND lang_id = ' . (int) $lang_id;
				$db->sql_query($sql);
			}
		}

		// These are always arrays because the key is the language id...
		$cp->vars['l_lang_name']			= utf8_normalize_nfc($request->variable('l_lang_name', array(0 => ''), true));
		$cp->vars['l_lang_explain']			= utf8_normalize_nfc($request->variable('l_lang_explain', array(0 => ''), true));
		$cp->vars['l_lang_default_value']	= utf8_normalize_nfc($request->variable('l_lang_default_value', array(0 => ''), true));

		if ($field_type != FIELD_BOOL)
		{
			$cp->vars['l_lang_options']			= utf8_normalize_nfc($request->variable('l_lang_options', array(0 => ''), true));
		}
		else
		{
			$cp->vars['l_lang_options']	= utf8_normalize_nfc($request->variable('l_lang_options', array(0 => array('')), true));
		}

		if ($cp->vars['lang_options'])
		{
			if (!is_array($cp->vars['lang_options']))
			{
				$cp->vars['lang_options'] = explode("\n", $cp->vars['lang_options']);
			}

			if ($action != 'create')
			{
				$sql = 'DELETE FROM ' . DL_FIELDS_LANG_TABLE . '
					WHERE field_id = ' . (int) $field_id . '
						AND lang_id = ' . (int) $default_lang_id;
				$db->sql_query($sql);
			}

			foreach ($cp->vars['lang_options'] as $option_id => $value)
			{
				$sql_ary = array(
					'field_type'	=> (int) $field_type,
					'lang_value'	=> $value
				);

				if ($action == 'create')
				{
					$sql_ary['field_id'] = $field_id;
					$sql_ary['lang_id'] = $default_lang_id;
					$sql_ary['option_id'] = (int) $option_id;

					$profile_sql[] = 'INSERT INTO ' . DL_FIELDS_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
				}
				else
				{
					$this->update_insert(DL_FIELDS_LANG_TABLE, $sql_ary, array(
						'field_id'	=> $field_id,
						'lang_id'	=> (int) $default_lang_id,
						'option_id'	=> (int) $option_id)
					);
				}
			}
		}

		if (is_array($cp->vars['l_lang_options']) && sizeof($cp->vars['l_lang_options']))
		{
			$empty_lang = array();

			foreach ($cp->vars['l_lang_options'] as $lang_id => $lang_ary)
			{
				if (!is_array($lang_ary))
				{
					$lang_ary = explode("\n", $lang_ary);
				}

				if (sizeof($lang_ary) != sizeof($cp->vars['lang_options']))
				{
					$empty_lang[$lang_id] = true;
				}

				if (!isset($empty_lang[$lang_id]))
				{
					if ($action != 'create')
					{
						$sql = 'DELETE FROM ' . DL_FIELDS_LANG_TABLE . '
							WHERE field_id = ' . (int) $field_id . '
							AND lang_id = ' . (int) $lang_id;
						$db->sql_query($sql);
					}

					foreach ($lang_ary as $option_id => $value)
					{
						$profile_lang_fields[] = array(
							'field_id'		=> (int) $field_id,
							'lang_id'		=> (int) $lang_id,
							'option_id'		=> (int) $option_id,
							'field_type'	=> (int) $field_type,
							'lang_value'	=> $value
						);
					}
				}
			}

			foreach ($empty_lang as $lang_id => $NULL)
			{
				$sql = 'DELETE FROM ' . DL_FIELDS_LANG_TABLE . '
					WHERE field_id = ' . (int) $field_id . '
					AND lang_id = ' . (int) $lang_id;
				$db->sql_query($sql);
			}
		}

		foreach ($profile_lang as $sql)
		{
			if ($action == 'create')
			{
				$profile_sql[] = 'INSERT INTO ' . DL_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql);
			}
			else
			{
				$lang_id = $sql['lang_id'];
				unset($sql['lang_id'], $sql['field_id']);

				$this->update_insert(DL_LANG_TABLE, $sql, array('lang_id' => (int) $lang_id, 'field_id' => $field_id));
			}
		}

		if (sizeof($profile_lang_fields))
		{
			foreach ($profile_lang_fields as $sql)
			{
				if ($action == 'create')
				{
					$profile_sql[] = 'INSERT INTO ' . DL_FIELDS_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql);
				}
				else
				{
					$lang_id = $sql['lang_id'];
					$option_id = $sql['option_id'];
					unset($sql['lang_id'], $sql['field_id'], $sql['option_id']);

					$this->update_insert(DL_FIELDS_LANG_TABLE, $sql, array(
						'lang_id'	=> $lang_id,
						'field_id'	=> $field_id,
						'option_id'	=> $option_id)
					);
				}
			}
		}


		$db->sql_transaction('begin');

		if ($action == 'create')
		{
			foreach ($profile_sql as $sql)
			{
				$db->sql_query($sql);
			}
		}

		$db->sql_transaction('commit');

		if ($action == 'edit')
		{
			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FIELD_EDIT', false, array($cp->vars['field_ident'] . ':' . $cp->vars['lang_name']));
			trigger_error($language->lang('DL_FIELD_CHANGED') . adm_back_link($this->u_action));
		}
		else
		{
			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FIELD_CREATE', false, array(substr($field_ident, 3) . ':' . $cp->vars['lang_name']));
			trigger_error($language->lang('DL_FIELD_ADDED') . adm_back_link($this->u_action));
		}
	}

	/**
	* Update, then insert if not successfull
	* Taken from acp_profile.php (c) by phpbb.com
	*/
	function update_insert($table, $sql_ary, $where_fields)
	{
		global $db;

		$where_sql = array();
		$check_key = '';

		foreach ($where_fields as $key => $value)
		{
			$check_key = (!$check_key) ? $key : $check_key;
			$where_sql[] = $key . ' = ' . ((is_string($value)) ? "'" . $db->sql_escape($value) . "'" : (int) $value);
		}

		if (!sizeof($where_sql))
		{
			return;
		}

		$sql = "SELECT $check_key
			FROM $table
			WHERE " . implode(' AND ', $where_sql);
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			$sql_ary = array_merge($where_fields, $sql_ary);

			if (sizeof($sql_ary))
			{
				$db->sql_query("INSERT INTO $table " . $db->sql_build_array('INSERT', $sql_ary));
			}
		}
		else
		{
			if (sizeof($sql_ary))
			{
				$sql = "UPDATE $table SET " . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . implode(' AND ', $where_sql);
				$db->sql_query($sql);
			}
		}
	}

	/**
	* Return sql statement for adding a new field ident (profile field) to the profile fields data table
	* Taken from acp_profile.php (c) by phpbb.com
	*/
	function add_field_ident($field_ident, $field_type)
	{
		global $db, $dbms;

		$sql = '';

		switch ($dbms)
		{
			case 'phpbb\\db\\driver\\mysql':
			case 'phpbb\\db\\driver\\mysqli':

				// We are defining the biggest common value, because of the possibility to edit the min/max values of each field.
				$sql = 'ALTER TABLE ' . DL_FIELDS_DATA_TABLE . " ADD $field_ident ";

				switch ($field_type)
				{
					case FIELD_STRING:
						$sql .= ' VARCHAR(255) ';
					break;

					case FIELD_DATE:
						$sql .= 'VARCHAR(10) ';
					break;

					case FIELD_TEXT:
						$sql .= "TEXT";
					break;

					case FIELD_BOOL:
						$sql .= 'TINYINT(2) ';
					break;

					case FIELD_DROPDOWN:
						$sql .= 'MEDIUMINT(8) ';
					break;

					case FIELD_INT:
						$sql .= 'BIGINT(20) ';
					break;
				}

			break;

			case 'phpbb\\db\\driver\\sqlite':
			case 'phpbb\\db\\driver\\sqlite3':

				switch ($field_type)
				{
					case FIELD_STRING:
						$type = ' VARCHAR(255) ';
					break;

					case FIELD_DATE:
						$type = 'VARCHAR(10) ';
					break;

					case FIELD_TEXT:
						$type = "TEXT(65535)";
					break;

					case FIELD_BOOL:
						$type = 'TINYINT(2) ';
					break;

					case FIELD_DROPDOWN:
						$type = 'MEDIUMINT(8) ';
					break;

					case FIELD_INT:
						$type = 'BIGINT(20) ';
					break;
				}

				// We are defining the biggest common value, because of the possibility to edit the min/max values of each field.
				$sql = 'ALTER TABLE ' . DL_FIELDS_DATA_TABLE . " ADD $field_ident [$type]";

			break;

			case 'phpbb\\db\\driver\\mssql':
			case 'phpbb\\db\\driver\\mssql_odbc':
			case 'phpbb\\db\\driver\\mssqlnative':

				// We are defining the biggest common value, because of the possibility to edit the min/max values of each field.
				$sql = 'ALTER TABLE [' . DL_FIELDS_DATA_TABLE . "] ADD [$field_ident] ";

				switch ($field_type)
				{
					case FIELD_STRING:
						$sql .= ' [VARCHAR] (255) ';
					break;

					case FIELD_DATE:
						$sql .= '[VARCHAR] (10) ';
					break;

					case FIELD_TEXT:
						$sql .= "[TEXT]";
					break;

					case FIELD_BOOL:
					case FIELD_DROPDOWN:
						$sql .= '[INT] ';
					break;

					case FIELD_INT:
						$sql .= '[FLOAT] ';
					break;
				}

			break;

			case 'phpbb\\db\\driver\\postgres':

				// We are defining the biggest common value, because of the possibility to edit the min/max values of each field.
				$sql = 'ALTER TABLE ' . DL_FIELDS_DATA_TABLE . " ADD COLUMN \"$field_ident\" ";

				switch ($field_type)
				{
					case FIELD_STRING:
						$sql .= ' VARCHAR(255) ';
					break;

					case FIELD_DATE:
						$sql .= 'VARCHAR(10) ';
					break;

					case FIELD_TEXT:
						$sql .= "TEXT";
					break;

					case FIELD_BOOL:
						$sql .= 'INT2 ';
					break;

					case FIELD_DROPDOWN:
						$sql .= 'INT4 ';
					break;

					case FIELD_INT:
						$sql .= 'INT8 ';
					break;
				}

			break;

			case 'phpbb\\db\\driver\\oracle':

				// We are defining the biggest common value, because of the possibility to edit the min/max values of each field.
				$sql = 'ALTER TABLE ' . DL_FIELDS_DATA_TABLE . " ADD $field_ident ";

				switch ($field_type)
				{
					case FIELD_STRING:
						$sql .= ' VARCHAR2(255) ';
					break;

					case FIELD_DATE:
						$sql .= 'VARCHAR2(10) ';
					break;

					case FIELD_TEXT:
						$sql .= "CLOB";
					break;

					case FIELD_BOOL:
						$sql .= 'NUMBER(2) ';
					break;

					case FIELD_DROPDOWN:
						$sql .= 'NUMBER(8) ';
					break;

					case FIELD_INT:
						$sql .= 'NUMBER(20) ';
					break;
				}

			break;
		}

		return $sql;
	}
}
