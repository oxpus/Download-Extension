<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller;

class upload
{
	/* phpbb objects */
	protected $root_path;
	protected $php_ext;
	protected $extension_manager;
	protected $db;
	protected $config;
	protected $helper;
	protected $request;
	protected $template;
	protected $user;
	protected $language;
	protected $config_text;
	protected $dispatcher;
	protected $cache;
	protected $notification;
	protected $files_factory;
	protected $filesystem;

	/* extension owned objects */
	protected $ext_path;

	protected $dlext_auth;
	protected $dlext_download;
	protected $dlext_extra;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_physical;
	protected $dlext_topic;
	protected $dlext_constants;
	protected $dlext_footer;
	protected $dlext_fields;

	protected $dlext_table_dl_stats;
	protected $dlext_table_downloads;

	/**
	* Constructor
	*
	* @param string									$root_path
	* @param string									$php_ext
	* @param \phpbb\cache\service					$cache
	* @param \phpbb\extension\manager				$extension_manager
	* @param \phpbb\db\driver\driver_interface		$db
	* @param \phpbb\config\config					$config
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\request\request 				$request
	* @param \phpbb\template\template				$template
	* @param \phpbb\user							$user
	* @param \phpbb\language\language				$language
	* @param \phpbb\event\dispatcher_interface		$dispatcher
	* @param \phpbb\notification\manager			$notification
	* @param \phpbb\files\factory					$files_factory
	* @param \phpbb\config\db_text					$config_text
	* @param \phpbb\filesystem\filesystem			$filesystem
	* @param \oxpus\dlext\core\auth					$dlext_auth
	* @param \oxpus\dlext\core\download				$dlext_download
	* @param \oxpus\dlext\core\extra				$dlext_extra
	* @param \oxpus\dlext\core\files				$dlext_files
	* @param \oxpus\dlext\core\format				$dlext_format
	* @param \oxpus\dlext\core\main					$dlext_main
	* @param \oxpus\dlext\core\physical				$dlext_physical
	* @param \oxpus\dlext\core\topic				$dlext_topic
	* @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	* @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	* @param \oxpus\dlext\core\fields\fields		$dlext_fields
	* @param string									$dlext_table_dl_stats
	* @param string									$dlext_table_downloads
	*/
	public function __construct(
		$root_path,
		$php_ext,
		\phpbb\cache\service $cache,
		\phpbb\extension\manager $extension_manager,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\notification\manager $notification,
		\phpbb\files\factory $files_factory,
		\phpbb\config\db_text $config_text,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\download $dlext_download,
		\oxpus\dlext\core\extra $dlext_extra,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\physical $dlext_physical,
		\oxpus\dlext\core\topic $dlext_topic,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		\oxpus\dlext\core\fields\fields $dlext_fields,
		$dlext_table_dl_stats,
		$dlext_table_downloads
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->cache					= $cache;
		$this->extension_manager 		= $extension_manager;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;
		$this->dispatcher				= $dispatcher;
		$this->notification				= $notification;
		$this->files_factory			= $files_factory;
		$this->config_text				= $config_text;
		$this->filesystem				= $filesystem;

		$this->dlext_table_dl_stats		= $dlext_table_dl_stats;
		$this->dlext_table_downloads	= $dlext_table_downloads;

		$this->ext_path					= $this->extension_manager->get_extension_path('oxpus/dlext', $dlext_constants::DL_TRUE);

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_download			= $dlext_download;
		$this->dlext_extra				= $dlext_extra;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_physical			= $dlext_physical;
		$this->dlext_topic				= $dlext_topic;
		$this->dlext_constants			= $dlext_constants;
		$this->dlext_footer				= $dlext_footer;
		$this->dlext_fields				= $dlext_fields;
	}

	public function handle()
	{
		$this->dlext_main->dl_handle_active();

		$submit		= $this->request->variable('submit', '');
		$df_id		= $this->request->variable('df_id', 0);
		$cat_id		= $this->request->variable('cat_id', 0);

		$index		= $this->dlext_main->full_index();

		$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);

		$physical_size = $this->dlext_physical->read_dl_sizes();

		if ($physical_size >= $this->config['dl_physical_quota'])
		{
			trigger_error('DL_BLUE_EXPLAIN');
		}

		if (($this->config['dl_stop_uploads'] && !$this->dlext_auth->user_admin()) || empty($index) || (!$cat_auth['auth_up'] && !$index[$cat_id]['auth_up'] && !$this->dlext_auth->user_admin()))
		{
			trigger_error('DL_NO_PERMISSION');
		}

		if ($submit)
		{
			$this->dlext_download->dl_submit_download('upload');
		}

		$bg_row = 0;

		if ($cat_auth['auth_mod'] || $index[$cat_id]['auth_mod'] || $this->dlext_auth->user_admin())
		{
			$this->template->assign_var('S_DL_MODCP', $this->dlext_constants::DL_TRUE);
			$bg_row = 1;
		}

		if (!$this->config['dl_disable_email'])
		{
			$this->template->assign_var('S_DL_EMAIL_BLOCK', $this->dlext_constants::DL_TRUE);
			$bg_row = 1;
		}

		if (!$this->config['dl_disable_popup'])
		{
			$this->template->assign_var('S_DL_POPUP_NOTIFY', $this->dlext_constants::DL_TRUE);
			$bg_row = 1;
		}

		if ($index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
		{
			$this->template->assign_var('S_DL_ALLOW_THUMBS', $this->dlext_constants::DL_TRUE);
		}

		if ($this->config['dl_use_hacklist'] && $this->dlext_auth->user_admin())
		{
			$this->template->assign_var('S_DL_USE_HACKLIST', $this->dlext_constants::DL_TRUE);
			$hacklist_on = ($bg_row) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$bg_row = 1 - $bg_row;
		}

		if ($index[$cat_id]['allow_mod_desc'])
		{
			$this->template->assign_var('S_DL_ALLOW_EDIT_MOD_DESC', $this->dlext_constants::DL_TRUE);
			$mod_block_bg = ($bg_row) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
		}

		if ($this->config['dl_upload_traffic_count'] && !$this->config['dl_traffic_off'])
		{
			$this->template->assign_var('S_DL_UPLOAD_TRAFFIC', $this->dlext_constants::DL_TRUE);
		}

		$thumbnail_explain = $this->language->lang('DL_THUMB_DIM_SIZE', $this->config['dl_thumb_xsize'], $this->config['dl_thumb_ysize'], $this->dlext_format->dl_size($this->config['dl_thumb_fsize']));

		$s_hidden_fields = [];

		if (!$cat_auth['auth_mod'] && !$index[$cat_id]['auth_mod'] && !$this->dlext_auth->user_admin())
		{
			$approve = ($index[$cat_id]['must_approve']) ? $this->dlext_constants::DL_FALSE : $this->dlext_constants::DL_TRUE;
			$s_hidden_fields += ['approve' => $approve];
		}

		if ($this->config['dl_disable_email'])
		{
			$s_hidden_fields += ['send_notify' => 0];
		}

		if ($this->config['dl_traffic_off'])
		{
			$s_hidden_fields += ['file_traffic' => 0];
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

		add_form_key('dl_upload');

		$file_size_ary		= $this->dlext_format->dl_size(0, 2, 'select');
		$file_size			= $file_size_ary['size_out'];

		$dl_file_edit_hint	= $this->config_text->get('dl_file_edit_hint');

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

		$template_ary = [
			'DL_THUMBNAIL_SECOND'		=> $thumbnail_explain,
			'DL_EXT_BLACKLIST'			=> $blacklist_explain,

			'DL_TRAFFIC'				=> 0,
			'DL_APPROVE'				=> $this->dlext_constants::DL_TRUE,
			'DL_FILE_EXT_SIZE'			=> $file_size,

			'DL_HACKLIST_BG'			=> (isset($hacklist_on) && $hacklist_on) ? ' bg2' : '',
			'DL_MOD_BLOCK_BG'			=> (isset($mod_block_bg) && $mod_block_bg) ? ' bg2' : '',

			'DL_MAX_UPLOAD_SIZE'		=> $this->language->lang('DL_UPLOAD_MAX_FILESIZE', $this->dlext_physical->dl_max_upload_size()),
			'DL_FORMATED_HINT_TEXT'		=> $formated_hint_text,

			'S_DL_CAT_CHOOSE'			=> $this->dlext_constants::DL_TRUE,
			'S_DL_CAT_ID_NAME'			=> 'cat_id',
			'S_DL_TODO_LINK_ONOFF'		=> ($this->config['dl_todo_onoff']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_DOWNLOADS_ACTION'		=> $this->helper->route('oxpus_dlext_upload'),
			'S_DL_TRAFFIC'				=> $this->config['dl_traffic_off'],
			'S_DL_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields),
			'S_DL_ADD_DL'				=> $this->dlext_constants::DL_TRUE,
		];

		$select_categories = $this->dlext_extra->dl_dropdown(0, 0, $cat_id, 'auth_up');

		if (!empty($select_categories) && is_array($select_categories))
		{
			foreach (array_keys($select_categories) as $key)
			{
				$this->template->assign_block_vars('search_cat_select', [
					'DL_CAT_ID'		=> $select_categories[$key]['cat_id'],
					'DL_SELECTED'	=> $select_categories[$key]['selected'],
					'DL_SEPERATOR'	=> $select_categories[$key]['seperator'],
					'DL_CAT_NAME'	=> $select_categories[$key]['cat_name'],
				]);
			}
		}

		$s_check_free = [];
		$s_check_free[] = ['value' => $this->dlext_constants::DL_FILE_FREE_NONE,		'lang'	=> $this->language->lang('NO')];
		$s_check_free[] = ['value' => $this->dlext_constants::DL_FILE_FREE_ALL,			'lang'	=> $this->language->lang('YES')];
		$s_check_free[] = ['value' => $this->dlext_constants::DL_FILE_FREE_REG_USER,	'lang'	=> $this->language->lang('DL_IS_FREE_REG')];

		for ($i = 0; $i < count($s_check_free); ++$i)
		{
			$this->template->assign_block_vars('dl_file_free_select', [
				'DL_VALUE'	=> $s_check_free[$i]['value'],
				'DL_LANG'	=> $s_check_free[$i]['lang'],
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
				'DL_VALUE'	=> $s_traffic_range[$i]['value'],
				'DL_LANG'	=> $s_traffic_range[$i]['lang'],
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
				'DL_VALUE'	=> $s_file_ext_size_range[$i]['value'],
				'DL_LANG'	=> $s_file_ext_size_range[$i]['lang'],
			]);
		}

		$s_hacklist = [];
		$s_hacklist[] = ['value' => $this->dlext_constants::DL_HACKLIST_NO,		'lang'	=> $this->language->lang('NO')];
		$s_hacklist[] = ['value' => $this->dlext_constants::DL_HACKLIST_YES,	'lang'	=> $this->language->lang('YES')];
		$s_hacklist[] = ['value' => $this->dlext_constants::DL_HACKLIST_EXTRA,	'lang'	=> $this->language->lang('DL_MOD_LIST_SHORT')];

		for ($i = 0; $i < count($s_hacklist); ++$i)
		{
			$this->template->assign_block_vars('dl_hacklist_select', [
				'DL_VALUE'	=> $s_hacklist[$i]['value'],
				'DL_LANG'	=> $s_hacklist[$i]['lang'],
			]);
		}

		/**
		 * Display extra data to save them with the download
		 *
		 * @event oxpus.dlext.upload_template_before
		 * @var int		cat_id			download category ID
		 * @var array	template_ary	array of download's data for edit
		 * @since 8.1.0-RC2
		 */
		$vars = array(
			'cat_id',
			'template_ary',
		);
		extract($this->dispatcher->trigger_event('oxpus.dlext.upload_template_before', compact($vars)));

		$this->template->assign_vars($template_ary);

		// Init and display the custom fields with the existing data
		$this->dlext_fields->get_profile_fields($df_id);
		$this->dlext_fields->generate_profile_fields($this->user->get_iso_lang_id());

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter('upload', $cat_id, 0, $index);
		$this->dlext_footer->handle();

		/*
		* generate page
		*/
		return $this->helper->render('@oxpus_dlext/dl_edit_body.html', $this->language->lang('DL_UPLOAD'));
	}
}
