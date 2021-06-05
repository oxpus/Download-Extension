<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\acp;

/**
* @package acp
*/
class acp_files_edit_controller implements acp_files_edit_interface
{
	/* phpbb objects */
	protected $db;
	protected $user;
	protected $phpEx;
	protected $extension_manager;
	protected $log;
	protected $dispatcher;
	protected $root_path;
	protected $config;
	protected $config_text;
	protected $helper;
	protected $language;
	protected $request;
	protected $template;
	protected $cache;
	protected $notification;
	protected $files_factory;
	protected $filesystem;

	/* extension owned objects */
	protected $u_action;
	protected $ext_path;

	protected $dlext_auth;
	protected $dlext_extra;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_topic;
	protected $dlext_constants;

	protected $dlext_table_dl_comments;
	protected $dlext_table_dl_favorites;
	protected $dlext_table_dl_stats;
	protected $dlext_table_dl_ver_files;
	protected $dlext_table_dl_versions;
	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param string								$phpEx
	 * @param \phpbb\cache\service					$cache
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\config\db_text					$config_text
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\extension\manager				$extension_manager
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\user							$user
	 * @param \phpbb\event\dispatcher_interface		$dispatcher
	 * @param \phpbb\notification\manater			$notification
	 * @param \phpbb\files\factory					$files_factory
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\extra				$dlext_extra
	 * @param \oxpus\dlext\core\files				$dlext_files
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\topic				$dlext_topic
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_comments
	 * @param string								$dlext_table_dl_favorites
	 * @param string								$dlext_table_dl_stats
	 * @param string								$dlext_table_dl_ver_files
	 * @param string								$dlext_table_dl_versions
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		$root_path,
		$phpEx,
		\phpbb\cache\service $cache,
		\phpbb\config\config $config,
		\phpbb\config\db_text $config_text,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\extension\manager $extension_manager,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\user $user,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\notification\manager $notification,
		\phpbb\files\factory $files_factory,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\extra $dlext_extra,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\topic $dlext_topic,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_comments,
		$dlext_table_dl_favorites,
		$dlext_table_dl_stats,
		$dlext_table_dl_ver_files,
		$dlext_table_dl_versions,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->root_path				= $root_path;
		$this->phpEx					= $phpEx;
		$this->cache					= $cache;
		$this->extension_manager		= $extension_manager;
		$this->db						= $db;
		$this->log						= $log;
		$this->user						= $user;
		$this->dispatcher				= $dispatcher;
		$this->notification				= $notification;
		$this->config					= $config;
		$this->config_text				= $config_text;
		$this->helper					= $helper;
		$this->language					= $language;
		$this->request					= $request;
		$this->template					= $template;
		$this->files_factory			= $files_factory;
		$this->filesystem				= $filesystem;

		$this->dlext_table_dl_comments		= $dlext_table_dl_comments;
		$this->dlext_table_dl_favorites		= $dlext_table_dl_favorites;
		$this->dlext_table_dl_stats			= $dlext_table_dl_stats;
		$this->dlext_table_dl_ver_files		= $dlext_table_dl_ver_files;
		$this->dlext_table_dl_versions		= $dlext_table_dl_versions;
		$this->dlext_table_downloads		= $dlext_table_downloads;
		$this->dlext_table_dl_cat			= $dlext_table_dl_cat;

		$this->ext_path					= $this->extension_manager->get_extension_path('oxpus/dlext', $dlext_constants::DL_TRUE);

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_extra				= $dlext_extra;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_topic				= $dlext_topic;
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
		$cat_id				= $this->request->variable('cat_id', 0);
		$df_id				= $this->request->variable('df_id', 0);
		$file_option		= $this->request->variable('file_ver_opt', 0);

		$dl_file = $this->dlext_files->all_files(0, [], [], $df_id, 1, ['*']);

		if (isset($dl_file['id']) && !$dl_file['id'])
		{
			trigger_error($this->language->lang('DL_MUST_SELECT_DOWNLOAD'));
		}

		$index = $this->dlext_main->full_index($cat_id);

		if (empty($index))
		{
			redirect($this->u_action . '&amp;mode=categories');
		}

		if ($cancel)
		{
			$action = '';
		}

		include($this->ext_path . 'includes/fields.' . $this->phpEx);

		$cp = new \oxpus\dlext\includes\custom_profile();

		if ($action == 'edit' || $action == 'add')
		{
			$s_hidden_fields = ['action' => 'save'];

			$cat_id = ($cat_id) ? $cat_id : ((isset($dl_file['cat'])) ? $dl_file['cat'] : 0);

			if ($action == 'edit')
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

				if ($dl_extern)
				{
					$checkextern = $this->dlext_constants::DL_TRUE;
				}
				else
				{
					$checkextern = $this->dlext_constants::DL_FALSE;
				}

				if ($approve)
				{
					$approve = $this->dlext_constants::DL_TRUE;
				}
				else
				{
					$approve = $this->dlext_constants::DL_FALSE;
				}

				if (isset($this->config['dl_disable_popup']) && !$this->config['dl_disable_popup'])
				{
					$this->template->assign_var('S_DL_POPUP_NOTIFY', $this->dlext_constants::DL_TRUE);
				}

				$this->template->assign_var('S_DL_CHANGE_TIME', $this->dlext_constants::DL_TRUE);

				$thumbnail = (isset($dl_file['thumbnail'])) ? $dl_file['thumbnail'] : '';

				if ($thumbnail)
				{
					$this->template->assign_var('S_DL_DEL_THUMB', $this->dlext_constants::DL_TRUE);
				}

				if ($thumbnail != $df_id . '_')
				{
					$this->template->assign_var('S_DL_SHOW_THUMB', $this->dlext_constants::DL_TRUE);
				}

				$this->template->assign_var('S_DL_CLICK_RESET', $this->dlext_constants::DL_TRUE);

				$s_hidden_fields += ['df_id' => $df_id];
			}
			else
			{
				$approve				= $this->dlext_constants::DL_TRUE;
				$description			= '';
				$file_name				= '';
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
				$checkextern			= $this->dlext_constants::DL_FALSE;
				$thumbnail				= '';
				$file_extern_size_out	= 0;
				$dl_free				= 0;
				$hacklist				= 0;

				$data_range_select		= $this->dlext_constants::DL_FILE_RANGE_KBYTE;
				$file_extern_size_range	= $this->dlext_constants::DL_FILE_RANGE_BYTE;
			}

			if (isset($this->config['dl_disable_email']) && !$this->config['dl_disable_email'])
			{
				$this->template->assign_var('S_DL_EMAIL_BLOCK', $this->dlext_constants::DL_TRUE);
			}

			if ($this->config['dl_traffic_off'])
			{
				$s_hidden_fields += ['file_traffic' => 0];
			}

			if (isset($index[$cat_id]['allow_thumbs']) && $index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
			{
				$this->template->assign_var('S_DL_ALLOW_THUMB', $this->dlext_constants::DL_TRUE);

				$thumbnail_explain	= $this->language->lang('DL_THUMB_DIM_SIZE', $this->config['dl_thumb_xsize'], $this->config['dl_thumb_ysize'], $this->dlext_format->dl_size($this->config['dl_thumb_fsize']));
			}
			else
			{
				$thumbnail_explain	= '';
			}

			$s_select_cat = $this->dlext_extra->dl_dropdown(0, 0, $cat_id, 'auth_up');

			if (!empty($s_select_cat) && is_array($s_select_cat))
			{
				foreach (array_keys($s_select_cat) as $key)
				{
					$this->template->assign_block_vars('cats_row', [
						'DL_CAT_ID'			=> $s_select_cat[$key]['cat_id'],
						'DL_SEPERATOR'		=> $s_select_cat[$key]['seperator'],
						'DL_SELECTED'		=> $s_select_cat[$key]['selected'],
						'DL_CAT_NAME'		=> $s_select_cat[$key]['cat_name'],
					]);
				}
			}

			if ($df_id)
			{
				$this->template->assign_var('S_DL_EDIT_VERSIONS', $this->dlext_constants::DL_TRUE);
			}

			if (isset($this->config['dl_use_hacklist']))
			{
				$this->template->assign_var('S_DL_USE_HACKLIST', $this->dlext_constants::DL_TRUE);
			}

			if (isset($index[$cat_id]['allow_mod_desc']))
			{
				$this->template->assign_var('S_DL_USE_MOD_DESC', $this->dlext_constants::DL_TRUE);
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

			$sql = 'SELECT ver_id, ver_change_time, ver_version FROM ' . $this->dlext_table_dl_versions . '
				WHERE dl_id = ' . (int) $df_id . '
				ORDER BY ver_version DESC, ver_change_time DESC';
			$result = $this->db->sql_query($sql);

			$total_versions = $this->db->sql_affectedrows();
			$select_versions_count = $total_versions + 1;
			$multiple_size = ($select_versions_count > $this->dlext_constants::DL_SELECT_MAX_SIZE) ? $this->dlext_constants::DL_SELECT_MAX_SIZE : $select_versions_count;

			$version_array = [];

			while ($row = $this->db->sql_fetchrow($result))
			{
				$version_array[$row['ver_version'] . ' - ' . $this->user->format_date($row['ver_change_time'])] = $row['ver_id'];
			}

			$this->db->sql_freeresult($result);

			natsort($version_array);
			$version_array = array_unique(array_reverse($version_array));

			foreach ($version_array as $name => $value)
			{
				$this->template->assign_block_vars('dl_version_select', [
					'DL_VALUE'	=> $value,
					'DL_NAME'	=> $name,
				]);
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
				'DL_THUMBNAIL_SECOND'		=> $thumbnail_explain,
				'DL_ACTION_MODE'			=> ($action == 'add') ? $this->language->lang('ADD') : $this->language->lang('EDIT'),

				'DL_BLACKLIST_EXPLAIN'		=> $blacklist_explain,
				'DL_CHECKEXTERN'			=> $checkextern,
				'DL_DESCRIPTION'			=> $description,
				'DL_FILE_NAME'				=> $file_name,
				'DL_HACK_AUTHOR'			=> $hack_author,
				'DL_HACK_AUTHOR_EMAIL'		=> $hack_author_email,
				'DL_HACK_AUTHOR_WEBSITE'	=> $hack_author_web,
				'DL_HACK_DL_URL'			=> $hack_dl_url,
				'DL_HACK_VERSION'			=> $hack_version,
				'DL_LONG_DESC'				=> $long_desc,
				'DL_MOD_DESC'				=> $mod_desc,
				'DL_MOD_LIST'				=> $mod_list,
				'DL_MOD_REQUIRE'			=> $require,
				'DL_MOD_TEST'				=> $mod_test,
				'DL_MOD_TODO'				=> $todo,
				'DL_MOD_WARNING'			=> $warning,
				'DL_TRAFFIC'				=> $file_traffic_out,
				'DL_URL'					=> $file_name,
				'DL_APPROVE'				=> $approve,
				'DL_THUMBNAIL'				=> $this->dlext_constants->get_value('files_dir') . '/thumbs/' . $thumbnail,
				'DL_FILE_EXT_SIZE'			=> $file_extern_size_out,
				'DL_FORMATED_HINT_TEXT'		=> $formated_hint_text,
				'DL_VERSION_SELECT_SIZE'	=> $multiple_size,

				'S_DL_TODO_LINK_ONOFF'		=> ($this->config['dl_todo_onoff']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
				'S_DL_CHECK_FREE'			=> $dl_free,
				'S_DL_TRAFFIC_RANGE'		=> $data_range_select,
				'S_DL_FILE_EXT_SIZE_RANGE'	=> $file_extern_size_range,
				'S_DL_HACKLIST'				=> $hacklist,
				'S_DL_SELECT_VER_DEL'		=> $total_versions,
				'S_DL_DOWNLOADS_ACTION'		=> $this->u_action,
				'S_DL_TRAFFIC'				=> $this->config['dl_traffic_off'],
				'S_DL_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields),

				'U_DL_BACK'					=> $this->u_action . '&amp;cat_id=' . $cat_id,
			];

			$s_check_free = [];
			$s_check_free[] = ['value' => $this->dlext_constants::DL_FILE_FREE_NONE,		'lang'	=> $this->language->lang('NO')];
			$s_check_free[] = ['value' => $this->dlext_constants::DL_FILE_FREE_ALL,			'lang'	=> $this->language->lang('YES')];
			$s_check_free[] = ['value' => $this->dlext_constants::DL_FILE_FREE_REG_USER,	'lang'	=> $this->language->lang('DL_IS_FREE_REG')];

			for ($i = 0; $i < count($s_check_free); ++$i)
			{
				$this->template->assign_block_vars('dl_file_free_select', [
					'DL_VALUE'		=> $s_check_free[$i]['value'],
					'DL_LANG'		=> $s_check_free[$i]['lang'],
				]);
			}

			$s_traffic_range = [];
			$s_traffic_range[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_BYTE,	'lang'	=> $this->language->lang('DL_BYTES')];
			$s_traffic_range[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_KBYTE,	'lang'	=> $this->language->lang('DL_KB')];
			$s_traffic_range[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_MBYTE,	'lang'	=> $this->language->lang('DL_MB')];
			$s_traffic_range[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_GBYTE,	'lang'	=> $this->language->lang('DL_GB')];

			for ($i = 0; $i < count($s_traffic_range); ++$i)
			{
				$this->template->assign_block_vars('dl_t_quote_select', [
					'DL_VALUE'		=> $s_traffic_range[$i]['value'],
					'DL_LANG'		=> $s_traffic_range[$i]['lang'],
				]);
			}

			$s_file_ext_size_range = [];
			$s_file_ext_size_range[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_BYTE,	'lang'	=> $this->language->lang('DL_BYTES')];
			$s_file_ext_size_range[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_KBYTE,	'lang'	=> $this->language->lang('DL_KB')];
			$s_file_ext_size_range[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_MBYTE,	'lang'	=> $this->language->lang('DL_MB')];
			$s_file_ext_size_range[] = ['value' => $this->dlext_constants::DL_FILE_RANGE_GBYTE,	'lang'	=> $this->language->lang('DL_GB')];

			for ($i = 0; $i < count($s_file_ext_size_range); ++$i)
			{
				$this->template->assign_block_vars('dl_e_quote_select', [
					'DL_VALUE'		=> $s_file_ext_size_range[$i]['value'],
					'DL_LANG'		=> $s_file_ext_size_range[$i]['lang'],
				]);
			}

			$s_hacklist = [];
			$s_hacklist[] = ['value' => $this->dlext_constants::DL_HACKLIST_NO,		'lang'	=> $this->language->lang('NO')];
			$s_hacklist[] = ['value' => $this->dlext_constants::DL_HACKLIST_YES,	'lang'	=> $this->language->lang('YES')];
			$s_hacklist[] = ['value' => $this->dlext_constants::DL_HACKLIST_EXTRA,	'lang'	=> $this->language->lang('DL_MOD_LIST')];

			for ($i = 0; $i < count($s_hacklist); ++$i)
			{
				$this->template->assign_block_vars('dl_hacklist_select', [
					'DL_VALUE'		=> $s_hacklist[$i]['value'],
					'DL_LANG'		=> $s_hacklist[$i]['lang'],
				]);
			}

			/**
			 * Display extra data to save them with the download
			 *
			 * @event oxpus.dlext.acp_files_template_before
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
			extract($this->dispatcher->trigger_event('oxpus.dlext.acp_files_template_before', compact($vars)));

			$this->template->assign_vars($template_ary);

			// Init and display the custom fields with the existing data
			$cp->get_profile_fields($df_id);
			$cp->generate_profile_fields($this->user->get_iso_lang_id());

			$this->template->assign_var('S_DL_FILES_EDIT', $this->dlext_constants::DL_TRUE);
		}
		else if ($action == 'save')
		{
			if ($file_option == $this->dlext_constants::DL_VERSION_DELETE)
			{
				$del_file		= $this->request->variable('del_file', 0);
				$file_ver_del	= $this->request->variable('file_ver_del', [0]);

				if (confirm_box($this->dlext_constants::DL_TRUE))
				{
					if ($del_file && count($file_ver_del))
					{
						$sql = 'SELECT path FROM ' . $this->dlext_table_dl_cat . '
							WHERE id = ' . (int) $cat_id;
						$result = $this->db->sql_query($sql);
						$path = $this->db->sql_fetchfield('path');
						$this->db->sql_freeresult($result);

						$sql = 'SELECT ver_real_file FROM ' . $this->dlext_table_dl_versions . '
							WHERE ' . $this->db->sql_in_set('ver_id', $file_ver_del);
						$result = $this->db->sql_query($sql);

						while ($row = $this->db->sql_fetchrow($result))
						{
							if ($row['ver_real_file'])
							{
								$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $path . $row['ver_real_file']);
							}
						}

						$this->db->sql_freeresult($result);

						$sql = 'SELECT file_type, real_name FROM ' . $this->dlext_table_dl_ver_files . '
							WHERE ' . $this->db->sql_in_set('ver_id', $file_ver_del);
						$result = $this->db->sql_query($sql);

						while ($row = $this->db->sql_fetchrow($result))
						{
							if ($row['real_name'])
							{
								switch ($row['file_type'])
								{
									case $this->dlext_constants::DL_FILE_TYPE_IMAGE:
										$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/version/images/' . $row['real_name']);
									break;
									default:
										$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/version/files/' . $row['real_name']);
								}
							}
						}

						$this->db->sql_freeresult($result);
					}

					$sql = 'DELETE FROM ' . $this->dlext_table_dl_versions . '
						WHERE ' . $this->db->sql_in_set('ver_id', $file_ver_del);
					$this->db->sql_query($sql);

					$sql = 'DELETE FROM ' . $this->dlext_table_dl_ver_files . '
						WHERE ' . $this->db->sql_in_set('ver_id', $file_ver_del);
					$this->db->sql_query($sql);

					redirect($this->u_action . "&amp;cat_id=$cat_id");
				}
				else
				{
					$this->template->assign_var('S_DL_DELETE_FILES_CONFIRM', $this->dlext_constants::DL_TRUE);

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

					confirm_box($this->dlext_constants::DL_FALSE, 'DL_CONFIRM_DEL_VERSIONS', build_hidden_fields($s_hidden_fields), '@oxpus_dlext/dl_confirm_body.html');
				}
			}
			else
			{
				if (!check_form_key('dl_adm_edit'))
				{
					trigger_error('FORM_INVALID');
				}

				$new_version		= $this->dlext_constants::DL_FALSE;

				$allow_bbcode		= ($this->config['allow_bbcode']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
				$allow_urls			= $this->dlext_constants::DL_TRUE;
				$allow_smilies		= ($this->config['allow_smilies']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
				$desc_uid			= $desc_bitfield = $mod_desc_uid = $mod_desc_bitfield = $long_desc_uid = $long_desc_bitfield = $warn_uid = $warn_bitfield = $todo_uid = $todo_bitfield = '';
				$desc_flags			= $mod_desc_flags = $long_desc_flags = $warn_flags = $todo_flags = 0;

				$description		= $this->request->variable('description', '', $this->dlext_constants::DL_TRUE);
				$file_name			= $this->request->variable('file_name', '', $this->dlext_constants::DL_TRUE);
				$hack_author		= $this->request->variable('hack_author', '', $this->dlext_constants::DL_TRUE);
				$hack_author_email	= $this->request->variable('hack_author_email', '', $this->dlext_constants::DL_TRUE);
				$hack_author_web	= $this->request->variable('hack_author_website', '', $this->dlext_constants::DL_TRUE);
				$hack_dl_url		= $this->request->variable('hack_dl_url', '', $this->dlext_constants::DL_TRUE);
				$hack_version		= $this->request->variable('hack_version', '');
				$long_desc			= $this->request->variable('long_desc', '', $this->dlext_constants::DL_TRUE);
				$mod_desc			= $this->request->variable('mod_desc', '', $this->dlext_constants::DL_TRUE);
				$require			= $this->request->variable('require', '', $this->dlext_constants::DL_TRUE);
				$test				= $this->request->variable('test', '', $this->dlext_constants::DL_TRUE);
				$todo				= $this->request->variable('todo', '', $this->dlext_constants::DL_TRUE);
				$warning			= $this->request->variable('warning', '', $this->dlext_constants::DL_TRUE);
				$approve			= $this->request->variable('approve', 0);
				$change_time		= $this->request->variable('change_time', 0);
				$click_reset		= $this->request->variable('click_reset', 0);
				$file_extern		= $this->request->variable('file_extern', 0);
				$file_free			= $this->request->variable('file_free', 0);
				$file_traffic		= $this->request->variable('file_traffic', 0);
				$file_version		= $this->request->variable('file_version', 0);
				$hacklist			= $this->request->variable('hacklist', 0);
				$mod_list			= $this->request->variable('mod_list', 0);
				$send_notify		= $this->request->variable('send_notify', 0);

				if ($description)
				{
					generate_text_for_storage($description, $desc_uid, $desc_bitfield, $desc_flags, $allow_bbcode, $this->dlext_constants::DL_TRUE, $allow_smilies);
				}
				else
				{
					trigger_error($this->language->lang('NO_SUBJECT'), E_USER_WARNING);
				}

				if ($long_desc)
				{
					generate_text_for_storage($long_desc, $long_desc_uid, $long_desc_bitfield, $long_desc_flags, $allow_bbcode, $this->dlext_constants::DL_TRUE, $allow_smilies);
				}

				if ($mod_desc)
				{
					generate_text_for_storage($mod_desc, $mod_desc_uid, $mod_desc_bitfield, $mod_desc_flags, $allow_bbcode, $this->dlext_constants::DL_TRUE, $allow_smilies);
				}

				if ($warning)
				{
					generate_text_for_storage($warning, $warn_uid, $warn_bitfield, $warn_flags, $allow_bbcode, $this->dlext_constants::DL_TRUE, $allow_smilies);
				}

				if ($todo)
				{
					generate_text_for_storage($todo, $todo_flags, $todo_bitfield, $todo_uid, $allow_bbcode, $this->dlext_constants::DL_TRUE, $allow_smilies);
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

				if (!$file_extern)
				{
					$file_name = (strpos($file_name, '/')) ? substr($file_name, strrpos($file_name, '/') + 1) : $file_name;
				}

				// validate custom profile fields
				$error = [];
				$cp_data = [];
				$cp_error = [];
				$cp->submit_cp_field($this->user->get_iso_lang_id(), $cp_data, $error);

				// Stop here, if custom fields are invalid!
				if (!empty($error))
				{
					trigger_error(implode('<br />', $error), E_USER_WARNING);
				}

				if ($df_id && !$file_extern)
				{
					$dl_file = $this->dlext_files->all_files(0, [], [], $df_id, 1, ['*']);

					$real_file_old	= (isset($dl_file['real_file'])) ? $dl_file['real_file'] : '';
					$file_cat_old	= (isset($dl_file['cat'])) ? $dl_file['cat'] : 0;

					$index_new = $this->dlext_main->full_index($file_cat_old);

					$file_path_old = (isset($index_new[$file_cat_old]['cat_path'])) ? $index_new[$file_cat_old]['cat_path'] : '';
					$file_path_new = (isset($index[$cat_id]['cat_path'])) ? $index[$cat_id]['cat_path'] : '';

					if ($file_name)
					{
						$extension = str_replace('.', '', trim(strrchr(strtolower($file_name), '.')));
						$new_real_file = $this->dlext_format->encrypt($file_name) . '.' . $extension;

						if ($file_option == $this->dlext_constants::DL_VERSION_REPLACE && !$file_version && $file_path_old && $real_file_old)
						{
							$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_old . $real_file_old);
						}

						while ($this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_new . $new_real_file))
						{
							$new_real_file = $this->dlext_format->encrypt($file_name) . '.' . $extension;
						}

						$this->filesystem->rename($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_old . $file_name, $this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_new . $new_real_file);

						$real_file_old = $new_real_file;
					}
					else
					{
						if ($dl_file['file_name'] == $dl_file['real_file'])
						{
							$extension = str_replace('.', '', trim(strrchr(strtolower($dl_file['real_file']), '.')));
							$new_real_file = $this->dlext_format->encrypt($dl_file['real_file']) . '.' . $extension;

							while ($this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_old . $new_real_file))
							{
								$new_real_file = $this->dlext_format->encrypt($dl_file['real_file']) . '.' . $extension;
							}

							$this->filesystem->rename($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_old . $real_file_old, $this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_old . $new_real_file);
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
							$this->filesystem->rename($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_old . $real_file_old, $this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_new . $new_real_file);

							$sql = 'SELECT ver_real_file FROM ' . $this->dlext_table_dl_versions . '
								WHERE dl_id = ' . (int) $df_id;
							$result = $this->db->sql_query($sql);

							while ($row = $this->db->sql_fetchrow($result))
							{
								$real_ver_file = $row['ver_real_file'];

								$this->filesystem->rename($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_old . $real_ver_file, $this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_new . $real_ver_file);
							}

							$this->db->sql_freeresult($result);
						}

						$sql = 'UPDATE ' . $this->dlext_table_dl_stats . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'cat_id' => $cat_id]) . ' WHERE id = ' . (int) $df_id;
						$this->db->sql_query($sql);

						$sql = 'UPDATE ' . $this->dlext_table_dl_comments . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'cat_id' => $cat_id]) . ' WHERE id = ' . (int) $df_id;
						$this->db->sql_query($sql);
					}
				}
				else if (!$file_extern && $file_name)
				{
					$extension = str_replace('.', '', trim(strrchr(strtolower($file_name), '.')));
					$new_real_file = $this->dlext_format->encrypt($file_name) . '.' . $extension;

					while ($this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $new_real_file))
					{
						$new_real_file = $this->dlext_format->encrypt($file_name) . '.' . $extension;
					}

					$this->filesystem->rename($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $file_name, $this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $new_real_file);
				}

				if (!$file_extern && $file_name)
				{
					$file_size = sprintf("%u", filesize($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $new_real_file));

					if (!$file_size)
					{
						trigger_error($this->language->lang('DL_FILE_NOT_FOUND', $new_real_file, $this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path), E_USER_WARNING);
					}
				}
				else
				{
					$new_real_file		= '';
					$file_extern_size	= $this->request->variable('file_extern_size', '');
					$file_size			= $this->dlext_format->resize_value('dl_extern_size', $file_extern_size);
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
					$file_hash = $this->dlext_format->encrypt($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $new_real_file, 'file', $this->config['dl_file_hash_algo']);
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
					if ($file_option <= $this->dlext_constants::DL_VERSION_ADD_OLD)
					{
						$sql = 'INSERT INTO ' . $this->dlext_table_dl_versions . ' ' . $this->db->sql_build_array('INSERT', [
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
					else if ($file_option == $this->dlext_constants::DL_VERSION_REPLACE && $file_version)
					{
						$sql = 'SELECT ver_real_file FROM ' . $this->dlext_table_dl_versions . '
							WHERE dl_id = ' . (int) $df_id . '
								AND ver_id = ' . (int) $file_version;
						$result = $this->db->sql_query($sql);
						$real_old_file = $this->db->sql_fetchfield('ver_real_file');
						$this->db->sql_freeresult($result);

						if ($dl_path && $real_old_file)
						{
							$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $dl_path . $real_old_file);
						}

						$sql = 'UPDATE ' . $this->dlext_table_dl_versions . ' SET ' . $this->db->sql_build_array('UPDATE', [
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

				if ($df_id && (!$file_option || ($file_option == $this->dlext_constants::DL_VERSION_REPLACE && !$file_version)))
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

				if ($df_id)
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
					 * @event oxpus.dlext.acp_files_edit_sql_insert_before
					 * @var int		df_id			download ID
					 * @var array	sql_array		array of download's data for storage
					 * @since 8.1.0-RC2
					 */
					$vars = array(
						'df_id',
						'sql_array',
					);
					extract($this->dispatcher->trigger_event('oxpus.dlext.acp_files_edit_sql_insert_before', compact($vars)));

					$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE id = ' . (int) $df_id;
					$this->db->sql_query($sql);

					$message = $this->language->lang('DL_DOWNLOAD_UPDATED');
				}
				else
				{
					$sql_array += [
						'change_time'	=> $current_time,
						'change_user'	=> $current_user,
						'add_time'		=> $current_time,
						'add_user'		=> $current_user,
					];

					$sql = 'INSERT INTO ' . $this->dlext_table_downloads . ' ' . $this->db->sql_build_array('INSERT', $sql_array);
					$this->db->sql_query($sql);
					$next_id = $this->db->sql_nextid();

					/**
					 * Save additional data for the download
					 *
					 * @event oxpus.dlext.acp_files_add_sql_insert_after
					 * @var int		next_id			download ID
					 * @var array	sql_array		array of download's data for storage
					 * @since 8.1.0-RC2
					 */
					$vars = array(
						'next_id',
						'sql_array',
					);
					extract($this->dispatcher->trigger_event('oxpus.dlext.acp_files_add_sql_insert_after', compact($vars)));

					$message = $this->language->lang('DL_DOWNLOAD_ADDED');
				}

				$dl_t_id = ($df_id) ? $df_id : $next_id;

				$thumb_form_name	= 'thumb_name';
				$thumb_message		= '';

				if ($this->config['dl_thumb_fsize'] && $index[$cat_id]['allow_thumbs'])
				{
					$allow_thumbs_upload = $this->dlext_constants::DL_TRUE;
				}
				else
				{
					$allow_thumbs_upload = $this->dlext_constants::DL_FALSE;
				}

				/**
				 * Manipulate thumbnail upload
				 *
				 * @event oxpus.dlext.acp_edit_thumbnail_before
				 * @var string	thumb_form_name			thumbnail upload form field
				 * @var bool	allow_thumbs_upload		enable/disable thumbnail upload
				 * @since 8.1.0-RC2
				 */

				$vars = array(
					'thumb_form_name',
					'allow_thumbs_upload',
				);
				extract($this->dispatcher->trigger_event('oxpus.dlext.acp_edit_thumbnail_before', compact($vars)));

				if ($allow_thumbs_upload)
				{
					$thumb_error = $this->dlext_constants::DL_FALSE;

					$this->user->add_lang('posting');

					$min_pic_width = 1;

					$allowed_imagetypes = ['gif','png','jpg'];
					$upload = $this->files_factory->get('upload')
						->set_allowed_extensions($allowed_imagetypes)
						->set_max_filesize($this->config['dl_thumb_fsize'])
						->set_allowed_dimensions(
							$min_pic_width,
							$min_pic_width,
							$this->config['dl_thumb_xsize'],
							$this->config['dl_thumb_ysize'])
						->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : $this->dlext_constants::DL_FALSE));

					$upload_file = $this->request->file($thumb_form_name);
					unset($upload_file['local_mode']);
					$thumb_file = $upload->handle_upload('files.types.form', $thumb_form_name);

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
							$thumb_error = $this->dlext_constants::DL_TRUE;
						}

						if ($pic_width > $this->config['dl_thumb_xsize'] || $pic_height > $this->config['dl_thumb_ysize'] || (sprintf("%u", filesize($thumb_temp)) > $this->config['dl_thumb_fsize']))
						{
							$thumb_file->remove();
							$thumb_error = $this->dlext_constants::DL_TRUE;
						}
					}
				}

				/**
				 * Manipulate thumbnail data before storage
				 *
				 * @event oxpus.dlext.acp_files_sql_thumbnail_before
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
				extract($this->dispatcher->trigger_event('oxpus.dlext.acp_files_sql_thumbnail_before', compact($vars)));

				if (!$thumb_error && isset($thumb_name) && $thumb_name != '')
				{
					$df_id = ($df_id) ? $df_id : $this->db->sql_nextid();
					$thumb_pic_extension = trim(strrchr(strtolower($thumb_name), '.'));
					$thumb_upload_filename = $df_id . '_' . unique_id() . $thumb_pic_extension;

					if ($dl_file['thumbnail'])
					{
						$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/thumbs/' . $dl_file['thumbnail']);
					}

					if ($thumb_upload_filename)
					{
						$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/thumbs/' . $thumb_upload_filename);
					}

					$upload_file['name'] = $thumb_upload_filename;
					$thumb_file->set_upload_ary($upload_file);
					$dest_folder = str_replace($this->root_path, '', substr($this->dlext_constants->get_value('files_dir') . '/thumbs/', 0, -1));

					$error = $thumb_file->move_file($dest_folder, $this->dlext_constants::DL_FALSE, $this->dlext_constants::DL_FALSE);
					$thumb_message = '<br />' . $this->language->lang('DL_THUMB_UPLOAD');

					$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'thumbnail' => $thumb_upload_filename]) . ' WHERE id = ' . (int) $df_id;
						$this->db->sql_query($sql);
				}

				if ($foreign_thumb_message)
				{
					$thumb_message = '<br />' . $foreign_thumb_message;
				}

				$del_thumb = $this->request->variable('del_thumb', 0);

				if ($del_thumb)
				{
					$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'thumbnail' => '']) . ' WHERE id = ' . (int) $df_id;
					$this->db->sql_query($sql);

					if ($dl_file['thumbnail'])
					{
						$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/thumbs/' . $dl_file['thumbnail']);
					}

					$thumb_message = '<br />' . $this->language->lang('DL_THUMB_DEL');
				}

				// Update Custom Fields
				$cp->update_profile_field_data($dl_t_id, $cp_data);

				if (!$this->config['dl_disable_email'] && !$send_notify && $approve)
				{
					if ($df_id)
					{
						$sql = 'SELECT fav_user_id FROM ' . $this->dlext_table_dl_favorites . '
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

						$this->notification->add_notifications('oxpus.dlext.notification.type.update', $notification_data);
						$this->notification->delete_notifications('oxpus.dlext.notification.type.approve', $df_id);
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

						$this->notification->add_notifications('oxpus.dlext.notification.type.dlext', $notification_data);
					}
				}

				if (!$approve)
				{
					$notification_data = [
						'user_ids'		=> $this->dlext_auth->dl_auth_users($cat_id, 'auth_mod'),
						'description'	=> $description,
						'long_desc'		=> $long_desc,
						'df_id'			=> $dl_t_id,
						'cat_name'		=> $index[$cat_id]['cat_name_nav'],
					];

					$this->notification->add_notifications('oxpus.dlext.notification.type.approve', $notification_data);
					$this->notification->delete_notifications('oxpus.dlext.notification.type.update', $dl_t_id);
				}
				else
				{
					if ($df_id)
					{
						$this->dlext_topic->gen_dl_topic('edit', $df_id);
					}
					else
					{
						$this->dlext_topic->gen_dl_topic('post', $dl_t_id);
					}
				}
			}

			if ($df_id)
			{
				$log_method = 'DL_LOG_FILE_EDIT';
			}
			else
			{
				$log_method = 'DL_LOG_FILE_ADD';
			}

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $log_method, false, [$description]);

			// Purge the files cache
			$this->cache->destroy('_dlext_cat_counts');
			$this->cache->destroy('_dlext_file_p');
			$this->cache->destroy('_dlext_file_preset');

			$ver_message = '';

			if ($new_version)
			{
				$version_url	= $this->helper->route('oxpus_dlext_version', ['ver_id' => $new_version]);
				$ver_message	= '<br /><br />' . $this->language->lang('CLICK_VIEW_NEW_VERSION', '<a href="' . $version_url . '">', '</a>');
			}

			$message .= $thumb_message . "<br /><br />" . $this->language->lang('CLICK_RETURN_DOWNLOADADMIN', '<a href="' . $this->u_action . '&amp;cat_id=' . $cat_id . '">', '</a>') . $ver_message . adm_back_link($this->u_action);

			trigger_error($message);
		}
	}
}
