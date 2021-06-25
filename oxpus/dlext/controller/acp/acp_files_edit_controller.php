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
	protected $dlext_download;
	protected $dlext_extra;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_topic;
	protected $dlext_constants;
	protected $dlext_fields;

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
	 * @param \phpbb\notification\manager			$notification
	 * @param \phpbb\files\factory					$files_factory
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\download			$dlext_download
	 * @param \oxpus\dlext\core\extra				$dlext_extra
	 * @param \oxpus\dlext\core\files				$dlext_files
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\topic				$dlext_topic
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param \oxpus\dlext\core\fields\fields		$dlext_fields
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
		\oxpus\dlext\core\download $dlext_download,
		\oxpus\dlext\core\extra $dlext_extra,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\topic $dlext_topic,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		\oxpus\dlext\core\fields\fields $dlext_fields,
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

		$this->dlext_download			= $dlext_download;
		$this->dlext_auth				= $dlext_auth;
		$this->dlext_extra				= $dlext_extra;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_topic				= $dlext_topic;
		$this->dlext_constants			= $dlext_constants;
		$this->dlext_fields				= $dlext_fields;
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
				$mod_list				= (isset($dl_file['mod_list']) && $dl_file['mod_list'] != 0) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
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
			$s_hacklist[] = ['value' => $this->dlext_constants::DL_HACKLIST_EXTRA,	'lang'	=> $this->language->lang('DL_MOD_LIST_SHORT')];

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
			$this->dlext_fields->get_profile_fields($df_id);
			$this->dlext_fields->generate_profile_fields($this->user->get_iso_lang_id());

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
				$mode = ($df_id) ? 'edit' : 'add';

				$this->dlext_download->dl_submit_download('acp', $df_id, $this->dlext_constants::DL_FALSE, $this->u_action);
			}
		}
	}
}
