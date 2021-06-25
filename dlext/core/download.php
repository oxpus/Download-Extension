<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\core;

class download implements download_interface
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

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_extra				= $dlext_extra;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_topic				= $dlext_topic;
		$this->dlext_constants			= $dlext_constants;
		$this->dlext_fields				= $dlext_fields;
	}

	public function dl_submit_download($module, $df_id = 0, $own_edit = 0, $u_action = '')
	{
		switch ($module)
		{
			case 'acp':
				$form_check = 'dl_adm_edit';
			break;
			case 'mcp':
				$form_check = 'dl_modcp';
			break;
			default:
				$form_check = 'dl_upload';
		}

		if (!check_form_key($form_check))
		{
			trigger_error('FORM_INVALID');
		}

		$new_version			= $this->dlext_constants::DL_FALSE;
		$cat_id					= $this->request->variable('cat_id', 0);

		$description			= $this->request->variable('description', '', $this->dlext_constants::DL_TRUE);
		$long_desc				= $this->request->variable('long_desc', '', $this->dlext_constants::DL_TRUE);

		$file_name				= $this->request->variable('file_name', '', $this->dlext_constants::DL_TRUE);
		$file_traffic			= $this->request->variable('file_traffic', 0);
		$file_extern			= $this->request->variable('file_extern', 0);
		$file_extern_size		= $this->request->variable('file_extern_size', '');
		$file_free				= $this->request->variable('file_free', 0);
		$file_version			= $this->request->variable('file_version', 0);
		$file_option			= $this->request->variable('file_ver_opt', 0);

		$hacklist				= $this->request->variable('hacklist', 0);
		$hack_author			= $this->request->variable('hack_author', '', $this->dlext_constants::DL_TRUE);
		$hack_author_email		= $this->request->variable('hack_author_email', '', $this->dlext_constants::DL_TRUE);
		$hack_author_website	= $this->request->variable('hack_author_website', '', $this->dlext_constants::DL_TRUE);
		$hack_dl_url			= $this->request->variable('hack_dl_url', '', $this->dlext_constants::DL_TRUE);
		$hack_version			= $this->request->variable('hack_version', '', $this->dlext_constants::DL_TRUE);

		$mod_desc				= $this->request->variable('mod_desc', '', $this->dlext_constants::DL_TRUE);
		$mod_list				= $this->request->variable('mod_list', 0);

		$require				= $this->request->variable('require', '', $this->dlext_constants::DL_TRUE);
		$test					= $this->request->variable('test', '', $this->dlext_constants::DL_TRUE);
		$todo					= $this->request->variable('todo', '', $this->dlext_constants::DL_TRUE);
		$warning				= $this->request->variable('warning', '', $this->dlext_constants::DL_TRUE);

		$change_time			= $this->request->variable('change_time', 0);
		$click_reset			= $this->request->variable('click_reset', 0);
		$send_notify			= $this->request->variable('send_notify', 0);

		$approve				= $this->request->variable('approve', 0);
		$del_thumb				= $this->request->variable('del_thumb', 0);
		$action					= $this->request->variable('action', '');

		$allow_bbcode			= ($this->config['allow_bbcode']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
		$allow_urls				= $this->dlext_constants::DL_TRUE;
		$allow_smilies			= ($this->config['allow_smilies']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

		$desc_uid				= '';
		$desc_bitfield			= '';
		$long_desc_uid			= '';
		$long_desc_bitfield		= '';
		$mod_desc_uid			= '';
		$mod_desc_bitfield		= '';
		$warn_uid				= '';
		$warn_bitfield			= '';
		$todo_uid				= '';
		$todo_bitfield			= '';

		$desc_flags				= 0;
		$long_desc_flags		= 0;
		$mod_desc_flags			= 0;
		$warn_flags				= 0;
		$todo_flags				= 0;

		if ($description)
		{
			generate_text_for_storage($description, $desc_uid, $desc_bitfield, $desc_flags, $allow_bbcode, $allow_urls, $allow_smilies);
		}
		else
		{
			trigger_error($this->language->lang('NO_SUBJECT'), E_USER_WARNING);
		}

		if ($long_desc)
		{
			generate_text_for_storage($long_desc, $long_desc_uid, $long_desc_bitfield, $long_desc_flags, $allow_bbcode, $allow_urls, $allow_smilies);
		}

		if ($mod_desc)
		{
			generate_text_for_storage($mod_desc, $mod_desc_uid, $mod_desc_bitfield, $mod_desc_flags, $allow_bbcode, $allow_urls, $allow_smilies);
		}

		if ($warning)
		{
			generate_text_for_storage($warning, $warn_uid, $warn_bitfield, $warn_flags, $allow_bbcode, $allow_urls, $allow_smilies);
		}

		if ($todo)
		{
			generate_text_for_storage($todo, $todo_uid, $todo_bitfield, $todo_flags, $allow_bbcode, $allow_urls, $allow_smilies);
		}

		$ext_blacklist = $this->dlext_auth->get_ext_blacklist();

		if ($file_extern)
		{
			$file_traffic = 0;
		}
		else
		{
			$file_traffic = $this->dlext_format->resize_value('dl_file_traffic', $file_traffic);
		}

		$index		= $this->dlext_main->full_index($cat_id);
		$cat_auth	= $this->dlext_auth->dl_cat_auth($cat_id);
		$file_path	= $index[$cat_id]['cat_path'];

		if ($df_id)
		{
			$dl_file = $this->dlext_files->all_files(0, [], [], $df_id, 1, ['*']);

			$real_file_old	= $dl_file['real_file'];
			$file_cat_old	= $dl_file['cat'];
			$file_name_old	= $dl_file['file_name'];
			$file_size_old	= $dl_file['file_size'];
		}
		else
		{
			$dl_file = [];
		}

		$this->language->add_lang('posting');

		if ($module == 'acp')
		{
			$file_name = basename($file_name);

			if ($df_id && !$file_extern)
			{
				$index_new = $this->dlext_main->full_index($file_cat_old);

				$file_path_old = $index_new[$file_cat_old]['cat_path'];
				$file_path_new = $index[$cat_id]['cat_path'];

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
				$file_size			= $this->dlext_format->resize_value('dl_extern_size', $file_extern_size);
			}
		}
		else
		{
			if (!$file_extern)
			{
				$form_name = 'dl_name';
				$file = $this->request->file($form_name);
				$file_extension = str_replace('.', '', trim(strrchr(strtolower($file['name']), '.')));
				$allowed_extensions = [$file_extension];
				$upload = $this->files_factory->get('upload')
					->set_allowed_extensions($allowed_extensions)
					->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : $this->dlext_constants::DL_FALSE));

				unset($file['local_mode']);
				$upload_file = $upload->handle_upload('files.types.form', $form_name);

				$file_size = $file['size'];
				$file_temp = $file['tmp_name'];
				$file_name = $file['name'];

				if ($this->config['dl_enable_blacklist'])
				{
					$extension = str_replace('.', '', trim(strrchr(strtolower($file_name), '.')));
					if (in_array($extension, $ext_blacklist))
					{
						trigger_error($this->language->lang('DL_FORBIDDEN_EXTENSION'), E_USER_ERROR);
					}
				}

				$error_count = count($upload_file->error);

				if ($error_count > 1 && $file_name)
				{
					$upload_file->remove();
					trigger_error(implode('<br />', $upload_file->error), E_USER_ERROR);
				}

				$upload_file->error = [];

				if ($file_name)
				{
					if (!$this->config['dl_traffic_off'])
					{
						$remain_traffic = 0;

						if ($this->user->data['is_registered'] && $this->dlext_constants->get_value('overall_traffics'))
						{
							$remain_traffic = $this->config['dl_overall_traffic'] - $this->config['dl_remain_traffic'];
						}
						else if (!$this->user->data['is_registered'] && $this->dlext_constants->get_value('guests_traffics'))
						{
							$remain_traffic = $this->config['dl_overall_guest_traffic'] - $this->config['dl_remain_guest_traffic'];
						}

						if (!$file_size || ($remain_traffic && $file_size > $remain_traffic && $this->config['dl_upload_traffic_count']))
						{
							$upload_file->remove();
							trigger_error($this->language->lang('DL_NO_UPLOAD_TRAFFIC'), E_USER_ERROR);
						}
					}

					if ($file_option == $this->dlext_constants::DL_VERSION_REPLACE && !$file_version && $file_path && $real_file_old)
					{
						$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $real_file_old);
					}

					$new_real_file = $this->dlext_format->encrypt($file_name) . '.' . $file_extension;

					$i = 1;

					while ($this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $new_real_file))
					{
						$new_real_file = $this->dlext_format->encrypt($i . $file_name) . '.' . $file_extension;

						++$i;
					}
				}
				else
				{
					if ($module == 'upload')
					{
						$upload_file->remove();
						trigger_error($this->language->lang('DL_NO_FILENAME_ENTERED'), E_USER_ERROR);
					}

					$new_real_file = $real_file_old;
				}
			}
			else
			{
				$file_size = 0;
				$new_real_file = '';
			}

			if (!$file_name && $module != 'upload')
			{
				$file_name = $file_name_old;
				$file_size = $file_size_old;
				$file_new = $this->dlext_constants::DL_FALSE;
			}
			else
			{
				$file_new = $this->dlext_constants::DL_TRUE;
			}

			if ($file_extern)
			{
				$file_size = $this->dlext_format->resize_value('dl_extern_size', $file_extern_size);
			}

			if (!$file_extern && $file_name && $file_new)
			{
				if (substr($file_path, -1) == '/')
				{
					$dest_path = $this->dlext_constants->get_value('files_dir') . '/downloads/' . substr($file_path, 0, -1);
				}
				else
				{
					$dest_path = $this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path;
				}
				$dest_path = str_replace($this->root_path, '', $dest_path);

				$file['name'] = $new_real_file;
				$upload_file->set_upload_ary($file);
				$upload_file->move_file($dest_path, $this->dlext_constants::DL_FALSE, $this->dlext_constants::DL_FALSE);

				$error_count = count($upload_file->error);

				if ($error_count)
				{
					$upload_file->remove();
					trigger_error(implode('<br />', $upload_file->error), E_USER_ERROR);
				}
			}
		}

		$current_user = $this->user->data['user_id'];

		if ($this->config['dl_set_add'] == 1 && $this->config['dl_set_user'])
		{
			$current_user = $this->config['dl_set_user'];
		}

		if ($this->config['dl_set_add'] == 2 && $index[$cat_id]['dl_set_add'] && $index[$cat_id]['dl_set_user'])
		{
			$current_user = $index[$cat_id]['dl_set_user'];
		}

		$approve = ($index[$cat_id]['must_approve'] && !$cat_auth['auth_mod'] && !$index[$cat_id]['auth_mod'] && !$this->dlext_auth->user_admin()) ? 0 : $approve;

		if ($new_real_file)
		{
			$file_hash = $this->dlext_format->encrypt($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $new_real_file, 'file', $this->config['dl_file_hash_algo']);
		}
		else
		{
			$file_hash = '';
		}

		// validate custom profile fields
		$error = [];
		$cp_data = [];
		$this->dlext_fields->submit_cp_field($this->user->get_iso_lang_id(), $cp_data, $error);

		// Stop here, if custom fields are invalid!
		if (!empty($error))
		{
			trigger_error(implode('<br />', $error), E_USER_WARNING);
		}

		if ($this->config['dl_thumb_fsize'] && $index[$cat_id]['allow_thumbs'])
		{
			$allow_thumbs_upload = $this->dlext_constants::DL_TRUE;
		}
		else
		{
			$allow_thumbs_upload = $this->dlext_constants::DL_FALSE;
		}

		$thumb_form_name = 'thumb_name';

		$vars = [
			'thumb_form_name',
			'allow_thumbs_upload',
		];

		if ($module == 'acp')
		{
			/**
				* Manipulate thumbnail upload
				*
				* @event oxpus.dlext.acp_edit_thumbnail_before
				* @var string thumb_form_name		thumbnail upload form field
				* @var bool	allow_thumbs_upload		enable/disable thumbnail upload
				* @since 8.1.0-RC2
			*/
			extract($this->dispatcher->trigger_event('oxpus.dlext.acp_edit_thumbnail_before', compact($vars)));
		}
		else if ($module == 'mcp')
		{
			/**
				* Manipulate thumbnail upload
				*
				* @event oxpus.dlext.mcp_edit_thumbnail_before
				* @var string thumb_form_name		thumbnail upload form field
				* @var bool	allow_thumbs_upload		enable/disable thumbnail upload
				* @since 8.1.0-RC2
			*/
			extract($this->dispatcher->trigger_event('oxpus.dlext.mcp_edit_thumbnail_before', compact($vars)));
		}

		if ($allow_thumbs_upload)
		{
			$min_pic_width = $this->dlext_constants::DL_PIC_MIN_SIZE;
			$allowed_imagetypes = ['gif','png','jpg'];

			$upload_image = $this->files_factory->get('upload')
				->set_allowed_extensions($allowed_imagetypes)
				->set_max_filesize($this->config['dl_thumb_fsize'])
				->set_allowed_dimensions(
					$min_pic_width,
					$min_pic_width,
					$this->config['dl_thumb_xsize'],
					$this->config['dl_thumb_ysize'])
				->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : $this->dlext_constants::DL_FALSE));

			$upload_thumb_file = $this->request->file($thumb_form_name);
			unset($upload_thumb_file['local_mode']);
			$thumb_file = $upload_image->handle_upload('files.types.form', $thumb_form_name);

			if (isset($upload_thumb_file['name']))
			{
				$thumb_name = $upload_thumb_file['name'];
				$thumb_temp = $upload_thumb_file['tmp_name'];
			}
			else
			{
				$thumb_name = '';
			}

			if (!empty($thumb_file->error) && $thumb_name)
			{
				$thumb_file->remove();
				trigger_error(implode('<br />', $thumb_file->error), E_USER_ERROR);
			}

			if ($thumb_name)
			{
				$pic_size	= getimagesize($thumb_temp);
				$pic_width	= $pic_size[0];
				$pic_height	= $pic_size[1];

				if (!$pic_width || !$pic_height)
				{
					$thumb_file->remove();
					trigger_error($this->language->lang('DL_UPLOAD_ERROR'), E_USER_ERROR);
				}

				if ($pic_width > $this->config['dl_thumb_xsize'] || $pic_height > $this->config['dl_thumb_ysize'] || (sprintf("%u", filesize($thumb_temp)) > $this->config['dl_thumb_fsize']))
				{
					$thumb_file->remove();
					trigger_error($this->language->lang('DL_THUMB_TO_BIG'), E_USER_ERROR);
				}
			}
		}

		if ($file_name && $df_id)
		{
			/*
			* Enter new version if choosen
			*/
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
			else if ($file_option == $this->dlext_constants::DL_VERSION_REPLACE)
			{
				if ($file_new && $real_file_old)
				{
					$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $index[$cat_id]['cat_path'] . $real_file_old);
				}

				if ($file_version)
				{
					$sql = 'SELECT * FROM ' . $this->dlext_table_dl_versions . '
						WHERE dl_id = ' . (int) $df_id . '
							AND ver_id = ' . (int) $file_version;
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					$sql = 'UPDATE ' . $this->dlext_table_dl_versions . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'ver_file_name'		=> ($file_new) ? $file_name : $row['ver_file_name'],
						'ver_real_file'		=> ($file_new) ? $real_file_new : $row['ver_real_file'],
						'ver_file_hash'		=> ($file_new) ? $file_hash : $row['ver_file_hash'],
						'ver_file_size'		=> ($file_new) ? $file_size : $row['ver_file_size'],
						'ver_change_time'	=> time(),
						'ver_change_user'	=> $this->user->data['user_id'],
						'ver_version'		=> $hack_version,
					]) . ' WHERE dl_id = ' . (int) $df_id . ' AND ver_id = ' . (int) $file_version;

					$this->db->sql_query($sql);
				}
			}
		}

		if (!$index[$cat_id]['allow_mod_desc'] && !$this->dlext_auth->user_admin())
		{
			$test		= '';
			$require	= '';
			$warning	= '';
			$mod_desc	= '';
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
			'hack_author_website'	=> $hack_author_website,
			'hack_dl_url'			=> $hack_dl_url,
			'test'					=> $test,
			'req'					=> $require,
			'todo'					=> $todo,
			'warning'				=> $warning,
			'mod_desc'				=> $mod_desc,
			'desc_uid'				=> $desc_uid,
			'desc_bitfield'			=> $desc_bitfield,
			'desc_flags'			=> $desc_flags,
			'long_desc_uid'			=> $long_desc_uid,
			'long_desc_bitfield'	=> $long_desc_bitfield,
			'long_desc_flags'		=> $long_desc_flags,
			'todo_uid'				=> $todo_uid,
			'todo_bitfield'			=> $todo_bitfield,
			'todo_flags'			=> $todo_flags,
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
					'change_time'	=> time(),
					'change_user'	=> $current_user,
				];
			}

			if ($click_reset)
			{
				$sql_array += [
					'klicks' => 0,
				];
			}

			if ($cat_auth['auth_mod'] || $index[$cat_id]['auth_mod'] || $index[$cat_id]['allow_mod_desc'] || $this->dlext_auth->user_admin())
			{
				$sql_array += [
					'mod_list'			=> $mod_list,
					'mod_desc_uid'		=> $mod_desc_uid,
					'mod_desc_bitfield'	=> $mod_desc_bitfield,
					'mod_desc_flags'	=> $mod_desc_flags,
					'warn_uid'			=> $warn_uid,
					'warn_bitfield'		=> $warn_bitfield,
					'warn_flags'		=> $warn_flags,
				];
			}

			if ($module == 'acp')
			{
				/**
				* Save additional data for the download
				*
				* @event oxpus.dlext.acp_files_edit_sql_insert_before
				* @var int		df_id			download ID
				* @var array	sql_array		array of download's data for storage
				* @since 8.1.0-RC2
				*/
				$vars = [
					'df_id',
					'sql_array',
				];
				extract($this->dispatcher->trigger_event('oxpus.dlext.acp_files_edit_sql_insert_before', compact($vars)));
			}
			else if ($module == 'mcp')
			{
				/**
				* Save additional data for the download
				*
				* @event oxpus.dlext.mcp_edit_sql_insert_before
				* @var int		df_id			download ID
				* @var array	sql_array		array of download's data for storage
				* @since 8.1.0-RC2
				*/
				$vars = [
					'df_id',
					'sql_array',
				];
				extract($this->dispatcher->trigger_event('oxpus.dlext.mcp_edit_sql_insert_before', compact($vars)));
			}

			$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE id = ' . (int) $df_id;
			$this->db->sql_query($sql);

			$message = $this->language->lang('DL_DOWNLOAD_UPDATED');
		}
		else
		{
			$sql_array += [
				'change_time'	=> time(),
				'change_user'	=> $current_user,
				'add_time'		=> time(),
				'add_user'		=> $current_user,
			];

			if ($module == 'upload')
			{
				/**
				* Save additional data for the download
				*
				* @event oxpus.dlext.upload_sql_insert_before
				* @var string	sql_array		array of download's data for storage
				* @since 8.1.0-RC2
				*/
				$vars = [
					'sql_array',
				];
				extract($this->dispatcher->trigger_event('oxpus.dlext.upload_sql_insert_before', compact($vars)));
			}

			$sql = 'INSERT INTO ' . $this->dlext_table_downloads . ' ' . $this->db->sql_build_array('INSERT', $sql_array);
			$this->db->sql_query($sql);
			$next_id = $this->db->sql_nextid();

			$vars = [
				'next_id',
				'sql_array',
			];

			if ($module == 'acp')
			{
				/**
				* Save additional data for the download
				*
				* @event oxpus.dlext.acp_files_add_sql_insert_after
				* @var int		next_id			download ID
				* @var array	sql_array		array of download's data for storage
				* @since 8.1.0-RC2
				*/
				extract($this->dispatcher->trigger_event('oxpus.dlext.acp_files_add_sql_insert_after', compact($vars)));
			}
			else if ($module == 'upload')
			{
				/**
				* Save additional data for the download
				*
				* @event oxpus.dlext.upload_sql_insert_after
				* @var int		next_id			download ID
				* @var array	sql_array		array of download's data for storage
				* @since 8.1.0-RC2
				*/
				extract($this->dispatcher->trigger_event('oxpus.dlext.upload_sql_insert_after', compact($vars)));
			}

			$message = $this->language->lang('DL_DOWNLOAD_ADDED');
		}

		$dl_t_id = ($df_id) ? $df_id : $next_id;

		$foreign_thumb_message = '';
		$thumb_error = '';

		if ($module == 'acp')
		{
			/**
			* Manipulate thumbnail data before storage
			*
			* @event oxpus.dlext.acp_files_sql_thumbnail_before
			* @var string	foreign_thumb_message	message after manipulate thumbnail
			* @var bool		thumb_error				thumbnail error (true to break here)
			* @var string	thumb_name				thumbnail name (true to avoid overwrite foreign storage)
			* @var int		df_id					download ID
			* @var array	sql_array				array of download's data for storage
			* @since 8.1.0-RC2
			*/
			$vars = [
				'foreign_thumb_message',
				'thumb_error',
				'thumb_name',
				'df_id',
				'sql_array',
			];
			extract($this->dispatcher->trigger_event('oxpus.dlext.acp_files_sql_thumbnail_before', compact($vars)));
		}
		else if ($module == 'mcp')
		{
			/**
			* Manipulate thumbnail data before storage
			*
			* @event oxpus.dlext.mcp_sql_thumbnail_before
			* @var string	foreign_thumb_message	message after manipulate thumbnail
			* @var string	thumb_name				thumbnail name (empty to avoid overwrite foreign storage)
			* @var int		df_id					download ID
			* @var array	sql_array				array of download's data for storage
			* @since 8.1.0-RC2
			*/
			$vars = [
				'foreign_thumb_message',
				'thumb_name',
				'df_id',
				'sql_array',
			];
			extract($this->dispatcher->trigger_event('oxpus.dlext.mcp_sql_thumbnail_before', compact($vars)));
		}
		else if ($module == 'upload')
		{
			/**
			* Manipulate thumbnail data before storage
			*
			* @event oxpus.dlext.upload_sql_thumbnail_before
			* @var string	foreign_thumb_message	message after manipulate thumbnail
			* @var string	thumb_name				thumbnail name (empty to avoid overwrite foreign storage)
			* @var int		next_id					download ID
			* @var array	sql_array				array of download's data for storage
			* @since 8.1.0-RC2
			*/
			$vars = [
				'foreign_thumb_message',
				'thumb_name',
				'df_id',
				'sql_array',
			];
			extract($this->dispatcher->trigger_event('oxpus.dlext.upload_sql_thumbnail_before', compact($vars)));
		}

		if (!$thumb_error && isset($thumb_name) && $thumb_name != '')
		{
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

			$upload_thumb_file['name'] = $thumb_upload_filename;
			$dest_folder = str_replace($this->root_path, '', substr($this->dlext_constants->get_value('files_dir') . '/thumbs/', 0, -1));

			$thumb_file->set_upload_ary($upload_thumb_file);
			$thumb_file->move_file($dest_folder, $this->dlext_constants::DL_FALSE, $this->dlext_constants::DL_FALSE);

			$thumb_message = '<br />' . $this->language->lang('DL_THUMB_UPLOAD');

			$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
				'thumbnail' => $thumb_upload_filename]) . ' WHERE id = ' . (int) $df_id;
			$this->db->sql_query($sql);
		}
		else if ($del_thumb)
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
		else
		{
			$thumb_message = '';
		}

		if ($foreign_thumb_message)
		{
			$thumb_message = '<br />' . $foreign_thumb_message;
		}

		if ($module == 'upload')
		{
			/**
			* Manipulate thumbnail data after storage
			*
			* @event oxpus.dlext.upload_sql_thumbnail_after
			* @var string	thumb_name		thumbnail name
			* @var int		next_id			download ID
			* @var array	sql_array		array of download's data for storage
			* @since 8.1.0-RC2
			*/
			$vars = [
				'thumb_name',
				'next_id',
				'sql_array',
			];
			extract($this->dispatcher->trigger_event('oxpus.dlext.upload_sql_thumbnail_after', compact($vars)));
		}

		// Update Custom Fields
		$this->dlext_fields->update_profile_field_data($dl_t_id, $cp_data);

		if ($this->config['dl_upload_traffic_count'] && !$file_extern && !$this->config['dl_traffic_off'])
		{
			if ($this->user->data['is_registered'] && $this->dlext_constants->get_value('overall_traffics') == $this->dlext_constants::DL_TRUE)
			{
				$this->config['dl_remain_traffic'] += $file_size;

				$this->config->set('dl_remain_traffic', $this->config['dl_remain_traffic']);
			}
			else if (!$this->user->data['is_registered'] && $this->dlext_constants->get_value('guests_traffics') == $this->dlext_constants::DL_TRUE)
			{
				$this->config['dl_remain_guest_traffic'] += $file_size;

				$this->config->set('dl_remain_guest_traffic', $this->config['dl_remain_guest_traffic']);
			}
		}

		if (!empty($dl_file) && $file_cat_old != $cat_id && !$file_extern && $df_id)
		{
			$old_path = $index[$file_cat_old]['cat_path'];
			$new_path = $index[$cat_id]['cat_path'];

			if ($new_path != $old_path)
			{
				$this->filesystem->rename($this->dlext_constants->get_value('files_dir') . '/downloads/' . $old_path . $real_file_old, $this->dlext_constants->get_value('files_dir') . '/downloads/' . $new_path . $real_file_new);

				$sql = 'SELECT ver_real_file FROM ' . $this->dlext_table_dl_versions . '
					WHERE dl_id = ' . (int) $df_id;
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$real_ver_file = $row['ver_real_file'];

					$this->filesystem->rename($this->dlext_constants->get_value('files_dir') . '/downloads/' . $old_path . $real_ver_file, $this->dlext_constants->get_value('files_dir') . '/downloads/' . $new_path . $real_ver_file);
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

		if ($index[$cat_id]['statistics'])
		{
			$this->dlext_main->dl_prune_stats($cat_id, $index[$cat_id]['stats_prune']);

			if ($df_id)
			{
				$direction = $this->dlext_constants::DL_STATS_FILE_EDIT;
			}
			else
			{
				$direction = $this->dlext_constants::DL_STATS_FILE_UPLOAD;
			}

			$sql = 'INSERT INTO ' . $this->dlext_table_dl_stats . ' ' . $this->db->sql_build_array('INSERT', [
				'cat_id'		=> $cat_id,
				'id'			=> $dl_t_id,
				'user_id'		=> $this->user->data['user_id'],
				'username'		=> $this->user->data['username'],
				'traffic'		=> $file_size,
				'direction'		=> $direction,
				'user_ip'		=> $this->user->data['session_ip'],
				'time_stamp'	=> time()]);
			$this->db->sql_query($sql);
		}

		// Purge the files cache
		$this->cache->destroy('_dlext_cat_counts');
		$this->cache->destroy('_dlext_file_p');
		$this->cache->destroy('_dlext_file_preset');

		if (!$this->config['dl_disable_email'] && !$send_notify && $approve)
		{
			if ($df_id)
			{
				$sql = 'SELECT fav_user_id FROM ' . $this->dlext_table_dl_favorites . '
						WHERE fav_dl_id = ' . (int) $df_id . '
						AND ' . $this->db->sql_in_set('fav_user_id', $this->dlext_auth->dl_auth_users($cat_id, 'auth_view'));
				$result = $this->db->sql_query($sql);

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

		if ($module == 'acp')
		{
			if ($df_id)
			{
				$log_method = 'DL_LOG_FILE_EDIT';
			}
			else
			{
				$log_method = 'DL_LOG_FILE_ADD';
			}

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $log_method, false, [$description]);
		}

		$ver_message	= '';
		$meta_url		= '';

		if ($new_version)
		{
			$version_url	= $this->helper->route('oxpus_dlext_version', ['ver_id' => $new_version]);
			$ver_message	= '<br /><br />' . $this->language->lang('CLICK_VIEW_NEW_VERSION', '<a href="' . $version_url . '">', '</a>');
		}

		if ($module == 'acp')
		{
			$message .= $thumb_message . "<br /><br />" . $this->language->lang('CLICK_RETURN_DOWNLOADADMIN', '<a href="' . $u_action . '&amp;cat_id=' . $cat_id . '">', '</a>') . $ver_message . adm_back_link($u_action);
		}
		else if ($module == 'mcp')
		{
			if ($own_edit)
			{
				$meta_url	= $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]);
				$message	= $this->language->lang('DL_DOWNLOAD_UPDATED') . $thumb_message . '<br /><br />' . $this->language->lang('CLICK_RETURN_DOWNLOAD_DETAILS', '<a href="' . $meta_url . '">', '</a>') . $ver_message;
			}
			else
			{
				$meta_url		= $this->helper->route('oxpus_dlext_mcp_manage', ['view' => 'toolbox', 'cat_id' => $cat_id]);
				$approve_string	= ($action == 'approve' || $approve) ? $this->language->lang('CLICK_RETURN_MODCP_APPROVE') : $this->language->lang('CLICK_RETURN_MODCP_MANAGE');
				$message		= $this->language->lang('DL_DOWNLOAD_UPDATED') . $thumb_message . '<br /><br />' . sprintf($return_string, '<a href="' . $meta_url . '">', '</a>') . $ver_message;
			}
		}
		else
		{
			$approve_string = ($approve) ? '' : '<br />' . $this->language->lang('DL_MUST_BE_APPROVED');
			$message		= $this->language->lang('DL_DOWNLOAD_ADDED') . $thumb_message . $approve_string . '<br /><br />' . $this->language->lang('CLICK_RETURN_DOWNLOADS', '<a href="' . $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]) . '">', '</a>');
		}
			
		$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);

		if ($cat_auth['auth_up'])
		{
			$message .= '<br /><br />' . $this->language->lang('DL_UPLOAD_ONE_MORE', '<a href="' . $this->helper->route('oxpus_dlext_upload', ['cat_id' => $cat_id]) . '">', '</a>');
		}

		if ($module != 'acp' && !$new_version && $meta_url)
		{
			meta_refresh(3, $meta_url);
		}

		trigger_error($message);
	}

	public function dl_edit_download($module, $df_id = 0)
	{

	}
}
