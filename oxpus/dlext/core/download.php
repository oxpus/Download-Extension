<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

class download implements download_interface
{
	/* phpbb objects */
	protected $db;
	protected $user;
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
	public $u_action;

	protected $dlext_auth;
	protected $dlext_extra;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_physical;
	protected $dlext_topic;
	protected $dlext_constants;
	protected $dlext_fields;

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
	 * @param \phpbb\cache\service					$cache
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\config\db_text					$config_text
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
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
	 * @param \oxpus\dlext\core\physical			$dlext_physical
	 * @param \oxpus\dlext\core\topic				$dlext_topic
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param \oxpus\dlext\core\fields\fields		$dlext_fields
	 * @param string								$dlext_table_dl_favorites
	 * @param string								$dlext_table_dl_stats
	 * @param string								$dlext_table_dl_ver_files
	 * @param string								$dlext_table_dl_versions
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		$root_path,
		\phpbb\cache\service $cache,
		\phpbb\config\config $config,
		\phpbb\config\db_text $config_text,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
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
		\oxpus\dlext\core\physical $dlext_physical,
		\oxpus\dlext\core\topic $dlext_topic,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		\oxpus\dlext\core\fields\fields $dlext_fields,
		$dlext_table_dl_favorites,
		$dlext_table_dl_stats,
		$dlext_table_dl_ver_files,
		$dlext_table_dl_versions,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->root_path				= $root_path;
		$this->cache					= $cache;
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

		$this->dlext_table_dl_favorites		= $dlext_table_dl_favorites;
		$this->dlext_table_dl_stats			= $dlext_table_dl_stats;
		$this->dlext_table_dl_ver_files		= $dlext_table_dl_ver_files;
		$this->dlext_table_dl_versions		= $dlext_table_dl_versions;
		$this->dlext_table_downloads		= $dlext_table_downloads;
		$this->dlext_table_dl_cat			= $dlext_table_dl_cat;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_extra				= $dlext_extra;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_physical			= $dlext_physical;
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
		$file_name_extern		= $this->request->variable('file_name_extern', '', $this->dlext_constants::DL_TRUE);
		$file_traffic			= $this->request->variable('file_traffic', 0);
		$file_extern			= $this->request->variable('file_extern', 0);
		$file_extern_size		= $this->request->variable('file_extern_size', '');
		$file_free				= $this->request->variable('file_free', 0);
		$file_version			= $this->request->variable('file_version', 0);
		$file_option			= $this->request->variable('file_ver_opt', 0);
		$hacklist				= $this->request->variable('hacklist', 0);
		$hack_author			= $this->request->variable('hack_author', '', $this->dlext_constants::DL_TRUE);
		$hack_author_email		= $this->request->variable('hack_author_email', '', $this->dlext_constants::DL_TRUE);
		$hack_author_web		= $this->request->variable('hack_author_website', '', $this->dlext_constants::DL_TRUE);
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
			if ($module == 'acp')
			{
				$file_name = $file_name_extern;
			}
		}
		else
		{
			$file_traffic = $this->dlext_format->resize_value('dl_file_traffic', $file_traffic);
		}

		$index		= $this->dlext_main->full_index($cat_id);
		$cat_auth	= $this->dlext_auth->dl_cat_auth($cat_id);
		$file_path	= $index[$cat_id]['cat_path'];
		$file_new	= $this->dlext_constants::DL_FALSE;

		if ($df_id)
		{
			$dl_file		= $this->dlext_files->all_files(0, [], [], $df_id, 1, ['*']);
			$real_file_old	= $dl_file['real_file'];
			$file_cat_old	= $dl_file['cat'];
			$file_name_old	= $dl_file['file_name'];
			$file_size_old	= $dl_file['file_size'];
		}

		$this->language->add_lang('posting');

		if ($module == 'acp')
		{
			if ($file_name == '0')
			{
				$file_name = '';
			}

			if ($df_id && !$file_extern)
			{
				$index_new = $this->dlext_main->full_index($file_cat_old);

				$file_path_old = $index_new[$file_cat_old]['cat_path'];
				$file_path_new = $index[$cat_id]['cat_path'];

				if ($file_name)
				{
					$extension = str_replace('.', '', trim(strrchr(strtolower($file_name), '.')));
					$new_real_file = $this->dlext_format->dl_hash($file_name) . '.' . $extension;

					if ($file_option == $this->dlext_constants::DL_VERSION_REPLACE && !$file_version && $file_path_old && $real_file_old)
					{
						$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_old . $real_file_old);
					}

					while ($this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_new . $new_real_file))
					{
						$new_real_file = $this->dlext_format->dl_hash($file_name) . '.' . $extension;
					}

					$this->filesystem->rename($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_old . $file_name, $this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_new . $new_real_file);

					$real_file_old = $new_real_file;
				}
				else
				{
					if ($dl_file['file_name'] == $dl_file['real_file'])
					{
						$extension = str_replace('.', '', trim(strrchr(strtolower($dl_file['real_file']), '.')));
						$new_real_file = $this->dlext_format->dl_hash($dl_file['real_file']) . '.' . $extension;

						while ($this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path_old . $new_real_file))
						{
							$new_real_file = $this->dlext_format->dl_hash($dl_file['real_file']) . '.' . $extension;
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
				$new_real_file = $this->dlext_format->dl_hash($file_name) . '.' . $extension;

				while ($this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $new_real_file))
				{
					$new_real_file = $this->dlext_format->dl_hash($file_name) . '.' . $extension;
				}

				$this->filesystem->rename($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $file_name, $this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $new_real_file);
			}

			if (!$file_extern)
			{
				$file_size = sprintf('%u', filesize($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $new_real_file));

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
				$file_name = $file['name'];

				if ($this->config['dl_enable_blacklist'])
				{
					$extension = str_replace('.', '', trim(strrchr(strtolower($file_name), '.')));
					if (in_array($extension, $ext_blacklist))
					{
						trigger_error($this->language->lang('DL_FORBIDDEN_EXTENSION'), E_USER_WARNING);
					}
				}

				if (count($upload_file->error) > 1 && $file_name)
				{
					$upload_file->remove();
					trigger_error(implode('<br>', $upload_file->error), E_USER_WARNING);
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
							trigger_error($this->language->lang('DL_NO_UPLOAD_TRAFFIC'), E_USER_WARNING);
						}
					}

					if ($file_option == $this->dlext_constants::DL_VERSION_REPLACE && !$file_version && $file_path && $real_file_old)
					{
						$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $real_file_old);
					}

					$new_real_file	= $this->dlext_format->dl_hash($file_name) . '.' . $file_extension;
					$i				= 1;

					while ($this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $new_real_file))
					{
						$new_real_file = $this->dlext_format->dl_hash($i . $file_name) . '.' . $file_extension;
						++$i;
					}
				}
				else
				{
					if ($module == 'upload')
					{
						$upload_file->remove();
						trigger_error($this->language->lang('DL_NO_FILENAME_ENTERED'), E_USER_WARNING);
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

				$dest_path		= str_replace($this->root_path, '', $dest_path);
				$file['name']	= $new_real_file;

				$upload_file->set_upload_ary($file);
				$upload_file->move_file($dest_path, $this->dlext_constants::DL_FALSE, $this->dlext_constants::DL_FALSE);

				if (count($upload_file->error) > 1)
				{
					$upload_file->remove();
					trigger_error(implode('<br>', $upload_file->error), E_USER_WARNING);
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

		$file_hash = '';

		if (!empty($new_real_file))
		{
			$file_hash = $this->dlext_format->dl_hash($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $new_real_file, 'file', $this->config['dl_file_hash_algo']);
		}

		// validate custom profile fields
		$error = [];
		$cp_data = [];
		$this->dlext_fields->submit_cp_field($this->user->get_iso_lang_id(), $cp_data, $error);

		// Stop here, if custom fields are invalid!
		if (!empty($error))
		{
			trigger_error(implode('<br>', $error), E_USER_WARNING);
		}

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

				$new_version = $this->db->sql_last_inserted_id();
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
						'ver_real_file'		=> ($file_new) ? $new_real_file : $row['ver_real_file'],
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
			'hack_author_website'	=> $hack_author_web,
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
				'hack_version'	=> ($file_option == $this->dlext_constants::DL_VERSION_REPLACE) ? $hack_version : $dl_file['hack_version'],
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
				$sql_array += ['klicks' => 0];
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
			$next_id = $this->db->sql_last_inserted_id();

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
				$vars = [
					'next_id',
					'sql_array',
				];
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
				$vars = [
					'next_id',
					'sql_array',
				];
				extract($this->dispatcher->trigger_event('oxpus.dlext.upload_sql_insert_after', compact($vars)));
			}

			$message = $this->language->lang('DL_DOWNLOAD_ADDED');
		}

		$dl_t_id = ($df_id) ? $df_id : $next_id;
		$df_id = $dl_t_id;

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

		if ($index[$cat_id]['statistics'])
		{
			$this->dlext_main->dl_prune_stats($cat_id, $index[$cat_id]['stats_prune']);

			$sql = 'INSERT INTO ' . $this->dlext_table_dl_stats . ' ' . $this->db->sql_build_array('INSERT', [
				'cat_id'		=> $cat_id,
				'id'			=> $dl_t_id,
				'user_id'		=> $this->user->data['user_id'],
				'username'		=> $this->user->data['username'],
				'traffic'		=> $file_size,
				'direction'		=> ($df_id) ? $this->dlext_constants::DL_STATS_FILE_EDIT : $this->dlext_constants::DL_STATS_FILE_UPLOAD,
				'user_ip'		=> $this->user->data['session_ip'],
				'time_stamp'	=> time(),
			]);
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
				$processing_user = [];

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
			$ver_message	= '<br><br>' . $this->language->lang('CLICK_VIEW_NEW_VERSION', '<a href="' . $version_url . '">', '</a>');
		}

		if ($module == 'acp')
		{
			$message .= $this->language->lang('CLICK_RETURN_DOWNLOADADMIN', '<a href="' . $u_action . '&amp;cat_id=' . $cat_id . '">', '</a>') . $ver_message . adm_back_link($u_action);
		}
		else if ($module == 'mcp')
		{
			if ($own_edit)
			{
				$meta_url	= $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]);
				$message	= $this->language->lang('DL_DOWNLOAD_UPDATED') . $this->language->lang('CLICK_RETURN_DOWNLOAD_DETAILS', '<a href="' . $meta_url . '">', '</a>') . $ver_message;
			}
			else
			{
				$meta_url		= $this->helper->route('oxpus_dlext_mcp_manage', ['view' => 'toolbox', 'cat_id' => $cat_id]);
				$approve_string	= ($action == 'approve' || $approve) ? 'CLICK_RETURN_MODCP_APPROVE' : 'CLICK_RETURN_MODCP_MANAGE';
				$message		= $this->language->lang('DL_DOWNLOAD_UPDATED') . $this->language->lang($approve_string, '<a href="' . $meta_url . '">', '</a>') . $ver_message;
			}
		}
		else
		{
			$approve_string = ($approve) ? '' : $this->language->lang('DL_MUST_BE_APPROVED') . '<br><br>';
			$message		= $this->language->lang('DL_DOWNLOAD_ADDED') . $approve_string . $this->language->lang('CLICK_RETURN_DOWNLOADS', '<a href="' . $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]) . '">', '</a>');
		}

		$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);

		if ($cat_auth['auth_up'])
		{
			$message .= '<br><br>' . $this->language->lang('DL_UPLOAD_ONE_MORE', '<a href="' . $this->helper->route('oxpus_dlext_upload', ['cat_id' => $cat_id]) . '">', '</a>');
		}

		if ($module != 'acp' && !$new_version && $meta_url)
		{
			meta_refresh(3, $meta_url);
		}

		trigger_error($message);
	}

	public function dl_edit_download($module, $df_id = 0, $own_edit = 0, $u_action = '')
	{
		$s_hidden_fields = ['action' => 'save'];

		if ($df_id)
		{
			$dl_file = $this->dlext_files->all_files(0, [], [], $df_id, 1, ['*']);

			if (isset($dl_file['id']) && !$dl_file['id'])
			{
				trigger_error($this->language->lang('DL_MUST_SELECT_DOWNLOAD'));
			}

			$cat_id 	= $dl_file['cat'];
			$index		= $this->dlext_main->full_index($cat_id);
			$cat_auth	= $this->dlext_auth->dl_cat_auth($cat_id);

			$s_hidden_fields += [
				'cat_id'	=> $cat_id,
				'df_id'		=> $df_id
			];

			$description			= $dl_file['description'];
			$file_traffic			= $dl_file['file_traffic'];
			$dl_extern				= $dl_file['extern'];
			$dl_extern_size			= $dl_file['file_size'];
			$file_name				= ($dl_extern) ? $dl_file['file_name'] : '';
			$hacklist				= $dl_file['hacklist'];
			$hack_author			= $dl_file['hack_author'];
			$hack_author_email		= $dl_file['hack_author_email'];
			$hack_author_web		= $dl_file['hack_author_website'];
			$hack_version			= $dl_file['hack_version'];
			$hack_dl_url			= $dl_file['hack_dl_url'];
			$long_desc				= $dl_file['long_desc'];
			$mod_test				= $dl_file['test'];
			$require				= $dl_file['req'];
			$todo					= $dl_file['todo'];
			$warning				= $dl_file['warning'];
			$mod_desc				= $dl_file['mod_desc'];
			$mod_list				= ($dl_file['mod_list']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$dl_free				= $dl_file['free'];
			$approve				= $dl_file['approve'];

			$mod_desc_uid			= $dl_file['mod_desc_uid'];
			$mod_desc_flags			= $dl_file['mod_desc_flags'];
			$long_desc_uid			= $dl_file['long_desc_uid'];
			$long_desc_flags		= $dl_file['long_desc_flags'];
			$desc_uid				= $dl_file['desc_uid'];
			$desc_flags				= $dl_file['desc_flags'];
			$warn_uid				= $dl_file['warn_uid'];
			$warn_flags				= $dl_file['warn_flags'];
			$todo_uid				= $dl_file['todo_uid'];
			$todo_flags				= $dl_file['todo_flags'];

			$text_ary		= generate_text_for_edit($description, $desc_uid, $desc_flags);
			$description	= $text_ary['text'];

			$text_ary		= generate_text_for_edit($long_desc, $long_desc_uid, $long_desc_flags);
			$long_desc		= $text_ary['text'];

			$text_ary		= generate_text_for_edit($mod_desc, $mod_desc_uid, $mod_desc_flags);
			$mod_desc		= $text_ary['text'];

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

			if ($this->config['dl_disable_popup_notify'])
			{
				$this->template->assign_var('S_DL_CHANGE_TIME', $this->dlext_constants::DL_TRUE);
			}

			$this->template->assign_var('S_DL_CLICK_RESET', $this->dlext_constants::DL_TRUE);

			if ($this->config['dl_traffic_off'])
			{
				$s_hidden_fields += ['file_traffic' => 0];
			}
		}
		else
		{
			$cat_id		= $this->request->variable('cat_id', 0);
			$index		= $this->dlext_main->full_index($cat_id);
			$cat_auth	= $this->dlext_auth->dl_cat_auth($cat_id);

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
			$file_extern_size_out	= 0;
			$dl_free				= 0;
			$hacklist				= 0;
			$dl_extern				= 0;

			$approve				= $this->dlext_constants::DL_TRUE;
			$data_range_select		= $this->dlext_constants::DL_FILE_RANGE_KBYTE;
			$file_extern_size_range	= $this->dlext_constants::DL_FILE_RANGE_BYTE;
		}

		$filey = [];
		$filen = [];
		$sizes = [];
		$exist = [];
		$browse_dir = '';
		$unassigned_files = $this->dlext_constants::DL_FALSE;
		$existing_files = [];

		$this->dlext_physical->get_files_assignments($index[$cat_id]['cat_path'], $browse_dir, $exist, $filey, $filen, $sizes, $unassigned_files, $existing_files);

		if ($unassigned_files)
		{
			$this->template->assign_var('S_DL_CAT_UNASSIGNED', $this->dlext_constants::DL_TRUE);

			if ($module == 'acp')
			{
				foreach ($exist as $key => $value)
				{
					if (!$value)
					{
						$file_ary = explode('|~|', $filey[$key]);

						$this->template->assign_block_vars('dl_select_unassigned_file', [
							'DL_FILE_NAME'	=> $file_ary[1],
						]);
					}
				}
			}
			else
			{
				$this->template->assign_var('S_DL_CAT_UNASSIGNED_COUNT', $this->language->lang('DL_UNASSIGNED_EXISTS', $unassigned_files));
			}
		}

		if ($module == 'upload' || ($module == 'acp' && !$df_id))
		{
			$select_new_cat = $this->dlext_extra->dl_dropdown(0, 0, $cat_id, 'auth_up');

			if (!empty($select_new_cat) && is_array($select_new_cat))
			{
				$this->template->assign_var('S_DL_CAT_CHOOSE', $this->dlext_constants::DL_TRUE);

				foreach (array_keys($select_new_cat) as $key)
				{
					$this->template->assign_block_vars('search_cat_select', [
						'DL_CAT_ID'		=> $select_new_cat[$key]['cat_id'],
						'DL_SEPERATOR'	=> $select_new_cat[$key]['seperator'],
						'DL_SELECTED'	=> $select_new_cat[$key]['selected'],
						'DL_CAT_NAME'	=> $select_new_cat[$key]['cat_name'],
					]);
				}
			}
		}

		if (!$this->config['dl_disable_email'])
		{
			$this->template->assign_var('S_DL_EMAIL_BLOCK', $this->dlext_constants::DL_TRUE);
		}
		else
		{
			$s_hidden_fields += ['send_notify' => 0];
		}

		if (!$this->config['dl_disable_popup'])
		{
			$this->template->assign_var('S_DL_POPUP_NOTIFY', $this->dlext_constants::DL_TRUE);
		}

		if ($module != 'upload' && $cat_auth['auth_mod'] || $index[$cat_id]['auth_mod'] || $this->dlext_auth->user_admin())
		{
			$this->template->assign_var('S_DL_MODCP', $this->dlext_constants::DL_TRUE);
		}

		if ($df_id)
		{
			$this->template->assign_var('S_DL_VERSION_ON', $this->dlext_constants::DL_TRUE);
		}

		if ($this->config['dl_use_hacklist'] && $this->dlext_auth->user_admin())
		{
			$this->template->assign_var('S_DL_USE_HACKLIST', $this->dlext_constants::DL_TRUE);
		}

		if ($index[$cat_id]['allow_mod_desc'])
		{
			$this->template->assign_var('S_DL_ALLOW_EDIT_MOD_DESC', $this->dlext_constants::DL_TRUE);
		}

		$ext_blacklist		= $this->dlext_auth->get_ext_blacklist();
		$blacklist_explain	= '';
		$multiple_size		= 0;
		$total_versions		= 0;

		if (!empty($ext_blacklist))
		{
			$blacklist_explain = '<br>' . $this->language->lang('DL_FORBIDDEN_EXT_EXPLAIN', implode(', ', $ext_blacklist));
		}

		if ($module != 'upload' && $df_id)
		{
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
		}

		$dl_file_edit_hint	= $this->config_text->get('dl_file_edit_hint');
		$formated_hint_text	= '';

		if ($dl_file_edit_hint)
		{
			$dl_file_edit_hint_uid		= $this->config['dl_file_edit_hint_bbcode'];
			$dl_file_edit_hint_bitfield	= $this->config['dl_file_edit_hint_bitfield'];
			$dl_file_edit_hint_flags	= $this->config['dl_file_edit_hint_flags'];
			$formated_hint_text 		= generate_text_for_display($dl_file_edit_hint, $dl_file_edit_hint_uid, $dl_file_edit_hint_bitfield, $dl_file_edit_hint_flags);
		}

		$s_upload_traffic = $this->dlext_constants::DL_FALSE;

		if ($this->config['dl_upload_traffic_count'] && !$this->config['dl_traffic_off'])
		{
			$s_upload_traffic = $this->dlext_constants::DL_TRUE;
		}

		if ($module == 'upload' && !$cat_auth['auth_mod'] && !$index[$cat_id]['auth_mod'] && !$this->dlext_auth->user_admin())
		{
			$approve = ($index[$cat_id]['must_approve']) ? $this->dlext_constants::DL_FALSE : $this->dlext_constants::DL_TRUE;
			$s_hidden_fields += ['approve' => $approve];
		}

		switch ($module)
		{
			case 'acp':
				$form_check		= 'dl_adm_edit';
				$u_go_back		= $u_action . '&amp;cat_id=' . $cat_id;
				$s_form_action	= $u_action;
				break;
			case 'mcp':
				$form_check		= 'dl_modcp';
				$u_go_back		= $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]);
				$s_form_action	= $this->helper->route('oxpus_dlext_mcp_edit');
				break;
			default:
				$form_check		= 'dl_upload';
				$u_go_back		= '';
				$s_form_action	= $this->helper->route('oxpus_dlext_upload');
		}

		add_form_key($form_check);

		$template_ary = [
			'DL_ACTION_MODE'			=> ($df_id) ? $this->language->lang('DL_EDIT_DOWNLOAD') : $this->language->lang('DL_ADD_DOWNLOAD'),
			'DL_BLACKLIST_EXPLAIN'		=> $blacklist_explain,
			'DL_CHECKEXTERN'			=> $dl_extern,
			'DL_DESCRIPTION'			=> $description,
			'DL_LONG_DESC'				=> $long_desc,
			'DL_TRAFFIC'				=> $file_traffic_out,
			'DL_APPROVE'				=> $approve,
			'DL_FILE_NAME'				=> $file_name,
			'DL_URL'					=> $file_name,
			'DL_MOD_DESC'				=> $mod_desc,
			'DL_MOD_LIST'				=> $mod_list,
			'DL_MOD_REQUIRE'			=> $require,
			'DL_MOD_TEST'				=> $mod_test,
			'DL_MOD_TODO'				=> $todo,
			'DL_MOD_WARNING'			=> $warning,
			'DL_HACK_AUTHOR'			=> $hack_author,
			'DL_HACK_AUTHOR_EMAIL'		=> $hack_author_email,
			'DL_HACK_AUTHOR_WEBSITE'	=> $hack_author_web,
			'DL_HACK_DL_URL'			=> $hack_dl_url,
			'DL_HACK_VERSION'			=> $hack_version,
			'DL_FILE_EXT_SIZE'			=> $file_extern_size_out,
			'DL_FORMATED_HINT_TEXT'		=> $formated_hint_text,
			'DL_VERSION_SELECT_SIZE'	=> $multiple_size,
			'DL_MAX_UPLOAD_SIZE' 		=> $this->language->lang('DL_UPLOAD_MAX_FILESIZE', $this->dlext_physical->dl_max_upload_size()),
			'DL_CAT_NAME' 				=> $index[$cat_id]['cat_name'],

			'S_DL_TODO_LINK_ONOFF'		=> ($this->config['dl_todo_onoff']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_CHECK_FREE'			=> $dl_free,
			'S_DL_TRAFFIC_RANGE'		=> $data_range_select,
			'S_DL_FILE_EXT_SIZE_RANGE'	=> $file_extern_size_range,
			'S_DL_HACKLIST'				=> $hacklist,
			'S_DL_UPLOAD_TRAFFIC'		=> $s_upload_traffic,
			'S_DL_SELECT_VER_DEL'		=> $total_versions,
			'S_DL_DOWNLOADS_ACTION'		=> $s_form_action,
			'S_DL_TRAFFIC'				=> $this->config['dl_traffic_off'],
			'S_DL_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields),

			'U_DL_UNASSIGN'				=> $this->helper->route('oxpus_dlext_unassigned'),
			'U_DL_GO_BACK'				=> $u_go_back,
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

		if ($module == 'acp')
		{
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
		}
		else if ($module == 'mcp')
		{
			/**
			 * Display extra data to save them with the download
			 *
			 * @event oxpus.dlext.mcp_edit_template_before
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
			extract($this->dispatcher->trigger_event('oxpus.dlext.mcp_edit_template_before', compact($vars)));
		}
		else if ($module == 'upload')
		{
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
		}

		$this->template->assign_vars($template_ary);

		// Init and display the custom fields with the existing data
		$this->dlext_fields->get_profile_fields($df_id);
		$this->dlext_fields->generate_profile_fields($this->user->get_iso_lang_id());
	}

	public function dl_delete_version($module, $cat_id, $df_id, $u_action = '')
	{
		$del_file		= $this->request->variable('del_file', 0);
		$file_ver_del	= $this->request->variable('file_ver_del', [0]);

		if (empty($file_ver_del))
		{
			trigger_error($this->language->lang('DL_VER_DEL_ERROR'), E_USER_WARNING);
		}

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
					if ($path && $row['ver_real_file'])
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

			if ($module == 'acp')
			{
				$redirect = $u_action . "&amp;cat_id=$cat_id";
			}
			else
			{
				$redirect = $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]);
			}

			redirect($redirect);
		}
		else
		{
			$this->template->assign_var('S_DL_DELETE_FILES_CONFIRM', $this->dlext_constants::DL_TRUE);

			$s_hidden_fields = [
				'view'			=> 'modcp',
				'action'		=> 'save',
				'cat_id'		=> $cat_id,
				'df_id'			=> $df_id,
				'submit'		=> 1,
				'file_ver_opt'	=> 3,
			];

			for ($i = 0; $i < count($file_ver_del); ++$i)
			{
				$s_hidden_fields += ['file_ver_del[' . $i . ']' => $file_ver_del[$i]];
			}

			if ($module == 'acp')
			{
				$confirm_tpl = '@oxpus_dlext/dl_confirm_body.html';
			}
			else
			{
				$confirm_tpl = '@oxpus_dlext/helpers/dl_confirm_body.html';
			}

			confirm_box($this->dlext_constants::DL_FALSE, $this->language->lang('DL_CONFIRM_DEL_VERSIONS'), build_hidden_fields($s_hidden_fields), $confirm_tpl);
		}
	}
}
