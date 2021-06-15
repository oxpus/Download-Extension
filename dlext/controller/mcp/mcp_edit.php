<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\mcp;

class mcp_edit
{
	/* phpbb objects */
	protected $root_path;
	protected $php_ext;
	protected $extension_manager;
	protected $notification;
	protected $db;
	protected $config;
	protected $config_text;
	protected $dispatcher;
	protected $cache;
	protected $files_factory;
	protected $helper;
	protected $request;
	protected $template;
	protected $user;
	protected $language;
	protected $filesystem;

	/* extension owned objects */
	protected $ext_path;

	protected $dlext_auth;
	protected $dlext_extra;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_physical;
	protected $dlext_topic;
	protected $dlext_constants;
	protected $dlext_footer;

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
	* @param string									$root_path
	* @param string									$php_ext
	* @param \phpbb\extension\manager				$extension_manager
	* @param \phpbb\cache\service					$cache
	* @param \phpbb\notification\manager 			$notification
	* @param \phpbb\db\driver\driver_interface		$db
	* @param \phpbb\config\config					$config
	* @param \phpbb\config\db_text					$config_text
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\request\request 				$request
	* @param \phpbb\template\template				$template
	* @param \phpbb\user							$user
	* @param \phpbb\language\language				$language
	* @param \phpbb\event\dispatcher_interface		$dispatcher
	* @param \phpbb\files\factory					$files_factory
	* @param \phpbb\filesystem\filesystem			$filesystem
	* @param \oxpus\dlext\core\auth					$dlext_auth
	* @param \oxpus\dlext\core\extra				$dlext_extra
	* @param \oxpus\dlext\core\files				$dlext_files
	* @param \oxpus\dlext\core\format				$dlext_format
	* @param \oxpus\dlext\core\main					$dlext_main
	* @param \oxpus\dlext\core\physical				$dlext_physical
	* @param \oxpus\dlext\core\topic				$dlext_topic
	* @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	* @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	* @param string									$dlext_table_dl_comments
	* @param string									$dlext_table_dl_favorites
	* @param string									$dlext_table_dl_stats
	* @param string									$dlext_table_dl_ver_files
	* @param string									$dlext_table_dl_versions
	* @param string									$dlext_table_downloads
	* @param string									$dlext_table_dl_cat
	*/
	public function __construct(
		$root_path,
		$php_ext,
		\phpbb\extension\manager $extension_manager,
		\phpbb\cache\service $cache,
		\phpbb\notification\manager $notification,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\config\db_text $config_text,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\event\dispatcher_interface $dispatcher,
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
		\oxpus\dlext\core\helpers\footer $dlext_footer,
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
		$this->php_ext 					= $php_ext;
		$this->extension_manager 		= $extension_manager;
		$this->cache					= $cache;
		$this->notification 			= $notification;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->config_text 				= $config_text;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;
		$this->dispatcher				= $dispatcher;
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
		$this->dlext_physical			= $dlext_physical;
		$this->dlext_topic				= $dlext_topic;
		$this->dlext_constants			= $dlext_constants;
		$this->dlext_footer				= $dlext_footer;
	}

	public function handle()
	{
		$access_cat = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_MOD);

		$own_edit = $this->dlext_constants::DL_FALSE;

		$df_id			= $this->request->variable('df_id', 0);
		$cat_id			= $this->request->variable('cat_id', 0);
		$file_option	= $this->request->variable('file_ver_opt', 0);
		$file_ver_del	= $this->request->variable('file_ver_del', [0]);
		$submit			= $this->request->variable('submit', '');
		$cancel			= $this->request->variable('cancel', '');
		$action			= $this->request->variable('action', '');
		$new_cat		= $this->request->variable('new_cat', 0);
		$del_file		= $this->request->variable('del_file', 0);

		if ($this->config['dl_edit_own_downloads'])
		{
			$sql = 'SELECT add_user FROM ' . $this->dlext_table_downloads . '
				WHERE id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);
			$add_user = $this->db->sql_fetchfield('add_user');
			$this->db->sql_freeresult($result);

			if ($add_user == $this->user->data['user_id'])
			{
				$own_edit = $this->dlext_constants::DL_TRUE;
			}
		}

		if ($own_edit == $this->dlext_constants::DL_TRUE)
		{
			$access_cat[] = $cat_id;
		}

		if (empty($access_cat))
		{
			trigger_error($this->language->lang('DL_NO_PERMISSION'));
		}

		$this->template->assign_vars([
			'DL_MCP_TAB_MODULE'		=> $this->language->lang('DL_EDIT_FILE'),

			'S_DL_MCP'				=> $this->dlext_constants::DL_TRUE,
			'S_DL_MCP_TAB_EDIT'		=> $this->dlext_constants::DL_TRUE,
		]);

		if (!$df_id)
		{
			redirect($this->helper->route('oxpus_dlext_mcp_manage', ['view' => 'toolbox', 'cat_id' => $cat_id]));
		}

		if ($cancel && $file_option == $this->dlext_constants::DL_VERSION_DELETE)
		{
			redirect($this->helper->route('oxpus_dlext_details', ['view' => 'detail', 'df_id' => $df_id]));
		}

		$index = $this->dlext_main->full_index();

		add_form_key('dl_modcp');

		// Initiate custom fields
		if (!class_exists('custom_profile'))
		{
			include($this->ext_path . 'includes/fields.' . $this->php_ext);
		}

		$cp = new \oxpus\dlext\includes\custom_profile();

		/*
		* And now the different work from here
		*/
		if ($action == 'save' && $submit)
		{
			if (!check_form_key('dl_modcp'))
			{
				trigger_error('FORM_INVALID');
			}

			if ($file_option == $this->dlext_constants::DL_VERSION_DELETE)
			{
				if (empty($file_ver_del))
				{
					trigger_error($this->language->lang('DL_VER_DEL_ERROR'), E_USER_ERROR);
				}

				if (confirm_box($this->dlext_constants::DL_TRUE))
				{
					$dl_ids = [];

					for ($i = 0; $i < count($file_ver_del); ++$i)
					{
						$dl_ids[] = intval($file_ver_del[$i]);
					}

					if ($del_file)
					{
						$sql = 'SELECT path FROM ' . $this->dlext_table_dl_cat . '
							WHERE id = ' . (int) $cat_id;
						$result = $this->db->sql_query($sql);
						$path = $this->db->sql_fetchfield('path');
						$this->db->sql_freeresult($result);

						if (!empty($dl_ids))
						{
							$sql = 'SELECT ver_real_file FROM ' . $this->dlext_table_dl_versions . '
								WHERE ' . $this->db->sql_in_set('ver_id', $dl_ids);
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
								WHERE ' . $this->db->sql_in_set('ver_id', $dl_ids);
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
					}

					if (!empty($dl_ids))
					{
						$sql = 'DELETE FROM ' . $this->dlext_table_dl_versions . '
							WHERE ' . $this->db->sql_in_set('ver_id', $dl_ids);
						$this->db->sql_query($sql);

						$sql = 'DELETE FROM ' . $this->dlext_table_dl_ver_files . '
							WHERE ' . $this->db->sql_in_set('ver_id', $dl_ids);
						$this->db->sql_query($sql);
					}

					redirect($this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]));
				}
				else
				{
					$this->template->assign_var('S_DL_DELETE_FILES_CONFIRM', $this->dlext_constants::DL_TRUE);

					$s_hidden_fields = [
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

					confirm_box($this->dlext_constants::DL_FALSE, $this->language->lang('DL_CONFIRM_DEL_VERSIONS'), build_hidden_fields($s_hidden_fields), '@oxpus_dlext/helpers/dl_confirm_body.html');
				}
			}
			else
			{
				$new_version			= $this->dlext_constants::DL_FALSE;

				$approve				= $this->request->variable('approve', 0);
				$description			= $this->request->variable('description', '', $this->dlext_constants::DL_TRUE);
				$file_traffic			= $this->request->variable('file_traffic', 0);
				$long_desc				= $this->request->variable('long_desc', '', $this->dlext_constants::DL_TRUE);
				$file_name				= $this->request->variable('file_name', '', $this->dlext_constants::DL_TRUE);

				$file_free				= $this->request->variable('file_free', 0);
				$file_extern			= $this->request->variable('file_extern', 0);
				$file_extern_size		= $this->request->variable('file_extern_size', '');
				$file_version			= $this->request->variable('file_version', 0);

				$test					= $this->request->variable('test', '', $this->dlext_constants::DL_TRUE);
				$require				= $this->request->variable('require', '', $this->dlext_constants::DL_TRUE);
				$todo					= $this->request->variable('todo', '', $this->dlext_constants::DL_TRUE);
				$warning				= $this->request->variable('warning', '', $this->dlext_constants::DL_TRUE);
				$mod_desc				= $this->request->variable('mod_desc', '', $this->dlext_constants::DL_TRUE);
				$mod_list				= $this->request->variable('mod_list', 0);
				$mod_list				= ($mod_list) ? 1 : 0;

				$send_notify			= $this->request->variable('send_notify', 0);
				$change_time			= $this->request->variable('change_time', 0);
				$del_thumb				= $this->request->variable('del_thumb', 0);
				$click_reset			= $this->request->variable('click_reset', 0);

				$hacklist				= $this->request->variable('hacklist', 0);
				$hack_author			= $this->request->variable('hack_author', '', $this->dlext_constants::DL_TRUE);
				$hack_author_email		= $this->request->variable('hack_author_email', '', $this->dlext_constants::DL_TRUE);
				$hack_author_website	= $this->request->variable('hack_author_website', '', $this->dlext_constants::DL_TRUE);
				$hack_version			= $this->request->variable('hack_version', '', $this->dlext_constants::DL_TRUE);
				$hack_dl_url			= $this->request->variable('hack_dl_url', '', $this->dlext_constants::DL_TRUE);

				$file_hash			= '';

				$allow_bbcode		= ($this->config['allow_bbcode']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
				$allow_urls			= $this->dlext_constants::DL_TRUE;
				$allow_smilies		= ($this->config['allow_smilies']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

				$desc_uid			= '';
				$desc_bitfield		= '';
				$long_desc_uid		= '';
				$long_desc_bitfield	= '';
				$mod_desc_uid		= '';
				$mod_desc_bitfield	= '';
				$warn_uid			= '';
				$warn_bitfield		= '';
				$todo_uid			= '';
				$todo_bitfield		= '';

				$desc_flags			= 0;
				$long_desc_flags	= 0;
				$mod_desc_flags		= 0;
				$warn_flags			= 0;
				$todo_flags			= 0;

				if ($description)
				{
					generate_text_for_storage($description, $desc_uid, $desc_bitfield, $desc_flags, $allow_bbcode, $allow_urls, $allow_smilies);
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

				if (!$description)
				{
					trigger_error($this->language->lang('NO_SUBJECT'), E_USER_WARNING);
				}

				if ($file_extern)
				{
					$file_traffic = 0;
				}
				else
				{
					$file_traffic = $this->dlext_format->resize_value('dl_file_traffic', $file_traffic);
				}

				$dl_file = $this->dlext_files->all_files(0, [], [], $df_id, 1, ['*']);

				$real_file_old	= $dl_file['real_file'];
				$file_name_old	= $dl_file['file_name'];
				$file_size_old	= $dl_file['file_size'];
				$file_cat_old	= $dl_file['cat'];

				$ext_blacklist = $this->dlext_auth->get_ext_blacklist();

				$this->language->add_lang('posting');

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

						$dl_path = $index[$cat_id]['cat_path'];

						if ($file_option == $this->dlext_constants::DL_VERSION_REPLACE && !$file_version && $dl_path && $real_file_old)
						{
							$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $dl_path . $real_file_old);
						}

						$real_file_new = $this->dlext_format->encrypt($file_name) . '.' . $file_extension;

						$i = 1;

						while ($this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/downloads/' . $dl_path . $real_file_new))
						{
							$real_file_new = $this->dlext_format->encrypt($i . $file_name) . '.' . $file_extension;

							++$i;
						}

						if ($index[$cat_id]['statistics'])
						{
							if ($index[$cat_id]['stats_prune'])
							{
								$this->dlext_main->dl_prune_stats($cat_id, $index[$cat_id]['stats_prune']);
							}

							$sql = 'INSERT INTO ' . $this->dlext_table_dl_stats . ' ' . $this->db->sql_build_array('INSERT', [
								'cat_id'		=> $new_cat,
								'id'			=> $df_id,
								'user_id'		=> $this->user->data['user_id'],
								'username'		=> $this->user->data['username'],
								'traffic'		=> $file_size,
								'direction'		=> 2,
								'user_ip'		=> $this->user->data['session_ip'],
								'time_stamp'	=> time()]);
							$this->db->sql_query($sql);
						}
					}
					else
					{
						$real_file_new = $real_file_old;
					}
				}
				else
				{
					$file_size = 0;
					$real_file_new = '';
				}

				if (!$file_name)
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
					if (substr($dl_path, -1) == '/')
					{
						$dest_path = $this->dlext_constants->get_value('files_dir') . '/downloads/' . substr($dl_path, 0, -1);
					}
					else
					{
						$dest_path = $this->dlext_constants->get_value('files_dir') . '/downloads/' . $dl_path;
					}
					$dest_path = str_replace($this->root_path, '', $dest_path);

					$file['name'] = $real_file_new;
					$upload_file->set_upload_ary($file);
					$upload_file->move_file($dest_path, $this->dlext_constants::DL_FALSE, $this->dlext_constants::DL_FALSE);

					$error_count = count($upload_file->error);

					if ($error_count)
					{
						$upload_file->remove();
						trigger_error(implode('<br />', $upload_file->error), E_USER_ERROR);
					}

					$file_hash = $this->dlext_format->encrypt($this->dlext_constants->get_value('files_dir') . '/downloads/' . $dl_path . $real_file_new, 'file', $this->config['dl_file_hash_algo']);
				}

				if ($this->config['dl_thumb_fsize'] && $index[$cat_id]['allow_thumbs'] && !$del_thumb)
				{
					$allow_thumbs_upload = $this->dlext_constants::DL_TRUE;
				}
				else
				{
					$allow_thumbs_upload = $this->dlext_constants::DL_FALSE;
				}

				$thumb_form_name = 'thumb_name';

				/**
				 * Manipulate thumbnail upload
				 *
				 * @event oxpus.dlext.mcp_edit_thumbnail_before
				 * @var string	thumb_form_name			thumbnail upload form field
				 * @var bool	allow_thumbs_upload		enable/disable thumbnail upload
				 * @since 8.1.0-RC2
				 */
				$vars = array(
					'thumb_form_name',
					'allow_thumbs_upload',
				);
				extract($this->dispatcher->trigger_event('oxpus.dlext.mcp_edit_thumbnail_before', compact($vars)));

				$thumb_name = '';

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

					$error_count = count($thumb_file->error);

					if ($error_count > 1 && $thumb_name)
					{
						$thumb_file->remove();
						trigger_error(implode('<br />', $thumb_file->error), E_USER_ERROR);
					}

					if ($thumb_name)
					{
						$pic_size 	= getimagesize($thumb_temp);
						$pic_width	= $pic_size[0];
						$pic_height	= $pic_size[1];

						if (!$pic_width || !$pic_height)
						{
							$thumb_file->remove();
							trigger_error($this->language->lang('DL_UPLOAD_ERROR'), E_USER_ERROR);
						}

						if ($pic_width > $this->config['dl_thumb_xsize'] || $pic_height > $this->config['dl_thumb_ysize'] || (sprintf("%u", filesize($thumb_temp) > $this->config['dl_thumb_fsize'])))
						{
							$thumb_file->remove();
							trigger_error($this->language->lang('DL_THUMB_TO_BIG'), E_USER_ERROR);
						}
					}
				}

				// validate custom profile fields
				$error = [];
				$cp_data = [];
				$cp->submit_cp_field($this->user->get_iso_lang_id(), $cp_data, $error);

				// Stop here, if custom fields are invalid!
				if (!empty($error))
				{
					trigger_error(implode('<br />', $error), E_USER_WARNING);
				}

				if ($df_id && $new_cat)
				{
					/*
					* Enter new version if choosen
					*/
					if ($file_option <= $this->dlext_constants::DL_VERSION_ADD_OLD)
					{
						$sql = 'INSERT INTO ' . $this->dlext_table_dl_versions . ' ' . $this->db->sql_build_array('INSERT', [
							'dl_id'				=> $df_id,
							'ver_file_name'		=> ($file_option) ? $file_name : $dl_file['file_name'],
							'ver_real_file'		=> ($file_option) ? $real_file_new : $dl_file['real_file'],
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

					unset($sql_array);

					if (!$index[$cat_id]['allow_mod_desc'] && !$this->dlext_auth->user_admin())
					{
						$test = $require = $warning = $mod_desc = '';
					}

					$sql_array = [
						'description'			=> $description,
						'file_traffic'			=> $file_traffic,
						'long_desc'				=> $long_desc,
						'free'					=> $file_free,
						'extern'				=> $file_extern,
						'cat'					=> $new_cat,
						'desc_uid'				=> $desc_uid,
						'desc_bitfield'			=> $desc_bitfield,
						'desc_flags'			=> $desc_flags,
						'long_desc_uid'			=> $long_desc_uid,
						'long_desc_bitfield'	=> $long_desc_bitfield,
						'long_desc_flags'		=> $long_desc_flags,
						'approve'				=> $approve,
						'hacklist'				=> $hacklist,
						'hack_author'			=> $hack_author,
						'hack_author_email'		=> $hack_author_email,
						'hack_author_website'	=> $hack_author_website,
						'hack_dl_url'			=> $hack_dl_url,
						'todo'					=> $todo,
						'todo_uid'				=> $todo_uid,
						'todo_bitfield'			=> $todo_bitfield,
						'todo_flags'			=> $todo_flags,
						'test'					=> $test,
						'req'					=> $require,
						'warning'				=> $warning,
						'mod_desc'				=> $mod_desc,
					];

					if (!$file_option || ($file_option == $this->dlext_constants::DL_VERSION_REPLACE && !$file_version))
					{
						$sql_array += [
							'file_name'				=> $file_name,
							'real_file'				=> $real_file_new,
							'file_hash'				=> $file_hash,
							'file_size'				=> $file_size,
							'hack_version'			=> $hack_version,
						];
					}
					else
					{
						$sql_array += [
							'file_name'		=> $dl_file['file_name'],
							'real_file'		=> $dl_file['real_file'],
							'file_hash'		=> $dl_file['file_hash'],
							'file_size'		=> $dl_file['file_size'],
							'hack_version'	=> $dl_file['hack_version'],
						];
					}

					if (!$change_time)
					{
						$sql_array += [
							'change_time'	=> time(),
							'change_user'	=> $this->user->data['user_id'],
						];
					}

					if ($click_reset)
					{
						$sql_array += [
							'klicks' => 0,
						];
					}

					if ($index[$cat_id]['allow_mod_desc'] || $this->dlext_auth->user_admin())
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

					/**
					 * Save additional data for the download
					 *
					 * @event oxpus.dlext.mcp_edit_sql_insert_before
					 * @var int		df_id			download ID
					 * @var array	sql_array		array of download's data for storage
					 * @since 8.1.0-RC2
					 */
					$vars = array(
						'df_id',
						'sql_array',
					);
					extract($this->dispatcher->trigger_event('oxpus.dlext.mcp_edit_sql_insert_before', compact($vars)));

					$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE id = ' . (int) $df_id;
					$this->db->sql_query($sql);

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
					$foreign_thumb_message = '';
					$vars = array(
						'foreign_thumb_message',
						'thumb_name',
						'df_id',
						'sql_array',
					);
					extract($this->dispatcher->trigger_event('oxpus.dlext.mcp_sql_thumbnail_before', compact($vars)));

					if (isset($thumb_name) && $thumb_name != '')
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

						$thumb_message = '<br />'.$this->language->lang('DL_THUMB_DEL');
					}
					else
					{
						$thumb_message = '';
					}

					if ($foreign_thumb_message)
					{
						$thumb_message = '<br />' . $foreign_thumb_message;
					}

					if ($this->config['dl_upload_traffic_count'] && !$file_extern && !$this->config['dl_traffic_off'])
					{
						$this->config['dl_remain_traffic'] += $file_size;

						$this->config->set('dl_remain_traffic', $this->config['dl_remain_traffic']);
					}

					if ($file_cat_old != $new_cat && !$file_extern && !$file_temp)
					{
						$old_path = $index[$file_cat_old]['cat_path'];
						$new_path = $index[$new_cat]['cat_path'];

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
							'cat_id' => $new_cat]) . ' WHERE id = ' . (int) $df_id;
						$this->db->sql_query($sql);

						$sql = 'UPDATE ' . $this->dlext_table_dl_comments . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'cat_id' => $new_cat]) . ' WHERE id = ' . (int) $df_id;
						$this->db->sql_query($sql);
					}

					// Purge the files cache
					$this->cache->destroy('_dlext_cat_counts');
					$this->cache->destroy('_dlext_file_p');
					$this->cache->destroy('_dlext_file_preset');

					if (!$this->config['dl_disable_email'] && !$send_notify && $approve)
					{
						$sql = 'SELECT fav_user_id FROM ' . $this->dlext_table_dl_favorites . '
								WHERE fav_dl_id = ' . (int) $df_id . '
								AND (' . $this->db->sql_in_set('fav_user_id', $this->dlext_auth->dl_auth_users($cat_id, 'auth_view')) . ')';
						$result = $this->db->sql_query($sql);

						$processing_user = [];

						while ($row = $this->db->sql_fetchrow($result))
						{
							$processing_user[] = $row['fav_user_id'];
						}

						$this->db->sql_freeresult($result);

						$notification_data = [
							'user_ids'			=> $processing_user,
							'description'		=> $description,
							'long_desc'			=> $long_desc,
							'df_id'				=> $df_id,
							'cat_name'			=> $index[$cat_id]['cat_name_nav'],
						];

						$this->notification->add_notifications('oxpus.dlext.notification.type.update', $notification_data);
						$this->notification->delete_notifications('oxpus.dlext.notification.type.approve', $df_id);
					}

					if (!$approve)
					{
						$notification_data = [
							'user_ids'			=> $this->dlext_auth->dl_auth_users($cat_id, 'auth_mod'),
							'description'		=> $description,
							'long_desc'			=> $long_desc,
							'df_id'				=> $df_id,
							'cat_name'			=> $index[$cat_id]['cat_name_nav'],
						];

						$this->notification->add_notifications('oxpus.dlext.notification.type.approve', $notification_data);
						$this->notification->delete_notifications('oxpus.dlext.notification.type.update', $df_id);
					}
					else
					{
						$this->dlext_topic->gen_dl_topic('edit', $df_id);
					}
				}
			}

			// Update Custom Fields
			$cp->update_profile_field_data($df_id, $cp_data);

			$ver_message = '';

			if ($new_version)
			{
				$version_url	= $this->helper->route('oxpus_dlext_version', ['ver_id' => $new_version]);
				$ver_message	= '<br /><br />' . $this->language->lang('CLICK_VIEW_NEW_VERSION', '<a href="' . $version_url . '">', '</a>');
			}

			if ($own_edit)
			{
				$meta_url	= $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id]);
				$message	= $this->language->lang('DL_DOWNLOAD_UPDATED') . $thumb_message . '<br /><br />' . $this->language->lang('CLICK_RETURN_DOWNLOAD_DETAILS', '<a href="' . $meta_url . '">', '</a>') . $ver_message;
			}
			else
			{
				$meta_url		= $this->helper->route('oxpus_dlext_mcp_manage', ['view' => 'toolbox', 'cat_id' => $cat_id]);
				$return_string	= ($action == 'approve') ? $this->language->lang('CLICK_RETURN_MODCP_APPROVE') : $this->language->lang('CLICK_RETURN_MODCP_MANAGE');
				$message		= $this->language->lang('DL_DOWNLOAD_UPDATED') . $thumb_message . '<br /><br />' . sprintf($return_string, '<a href="' . $meta_url . '">', '</a>') . $ver_message;
			}

			$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);

			if ($cat_auth['auth_up'])
			{
				$message .= '<br /><br />' . $this->language->lang('DL_UPLOAD_ONE_MORE', '<a href="' . $this->helper->route('oxpus_dlext_upload', ['cat_id' => $cat_id]) . '">', '</a>');
			}

			if (!$new_version)
			{
				meta_refresh(3, $meta_url);
			}

			trigger_error($message);
		}

		$dl_file = $this->dlext_files->all_files(0, [], [], $df_id, 1, ['*']);

		$s_hidden_fields = [
			'action'	=> 'save',
			'cat_id'	=> $cat_id,
			'df_id'		=> $df_id
		];

		$description			= $dl_file['description'];
		$file_traffic			= $dl_file['file_traffic'];
		$file_size				= $dl_file['file_size'];
		$long_desc				= $dl_file['long_desc'];
		$hacklist				= $dl_file['hacklist'];
		$hack_author			= $dl_file['hack_author'];
		$hack_author_email		= $dl_file['hack_author_email'];
		$hack_author_website	= $dl_file['hack_author_website'];
		$hack_version			= $dl_file['hack_version'];
		$hack_dl_url			= $dl_file['hack_dl_url'];
		$mod_test				= $dl_file['test'];
		$require				= $dl_file['req'];
		$todo					= $dl_file['todo'];
		$warning				= $dl_file['warning'];
		$mod_desc				= $dl_file['mod_desc'];
		$mod_list				= ($dl_file['mod_list']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

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

		$text_ary		= generate_text_for_edit($mod_desc, $mod_desc_uid, $mod_desc_flags);
		$mod_desc		= $text_ary['text'];
		$text_ary		= generate_text_for_edit($long_desc, $long_desc_uid, $long_desc_flags);
		$long_desc		= $text_ary['text'];
		$text_ary		= generate_text_for_edit($description, $desc_uid, $desc_flags);
		$description	= $text_ary['text'];
		$text_ary		= generate_text_for_edit($warning, $warn_uid, $warn_flags);
		$warning		= $text_ary['text'];
		$text_ary		= generate_text_for_edit($todo, $todo_uid, $warn_flags);
		$todo			= $text_ary['text'];

		if ($index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
		{
			$thumbnail			= $dl_file['thumbnail'];
			$thumbnail_explain	= $this->language->lang('DL_THUMB_DIM_SIZE', $this->config['dl_thumb_xsize'], $this->config['dl_thumb_ysize'], $this->dlext_format->dl_size($this->config['dl_thumb_fsize']));
			$this->template->assign_var('S_DL_ALLOW_THUMBS', $this->dlext_constants::DL_TRUE);

			$thumbnail = $this->dlext_constants->get_value('files_dir') . '/thumbs/' . $thumbnail;
			if ($dl_file['thumbnail'] && $this->filesystem->exists($thumbnail))
			{
				$this->template->assign_var('S_DL_THUMBNAIL', $this->dlext_constants::DL_TRUE);
			}
			else
			{
				$thumbnail = '';
			}
		}
		else
		{
			$thumbnail_explain	= '';
			$thumbnail			= '';
		}

		if (!$own_edit)
		{
			$select_new_cat = $this->dlext_extra->dl_dropdown(0, 0, $cat_id, 'auth_up');

			if (!empty($select_new_cat) && is_array($select_new_cat))
			{
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

			$this->template->assign_var('S_DL_CAT_CHOOSE', $this->dlext_constants::DL_TRUE);
		}
		else
		{
			$s_hidden_fields += ['new_cat' => $cat_id];
		}

		if ($dl_file['extern'])
		{
			$checkextern = $this->dlext_constants::DL_TRUE;
			$dl_extern_url = $dl_file['file_name'];
		}
		else
		{
			$checkextern = $this->dlext_constants::DL_FALSE;
			$dl_extern_url = '';
		}

		if ($dl_file['approve'])
		{
			$approve = $this->dlext_constants::DL_TRUE;
		}
		else
		{
			$approve = $this->dlext_constants::DL_FALSE;
		}

		$this->template->assign_var('S_DL_MODCP', $this->dlext_constants::DL_TRUE);

		if ($this->config['dl_disable_popup_notify'])
		{
			$this->template->assign_var('S_DL_CHANGE_TIME', $this->dlext_constants::DL_TRUE);
		}

		if (!$this->config['dl_disable_email'])
		{
			$this->template->assign_var('S_DL_EMAIL_BLOCK', $this->dlext_constants::DL_TRUE);
		}

		if (!$this->config['dl_disable_popup'])
		{
			$this->template->assign_var('S_DL_POPUP_NOTIFY', $this->dlext_constants::DL_TRUE);
		}

		$this->template->assign_var('S_DL_CLICK_RESET', $this->dlext_constants::DL_TRUE);

		$bg_row			= 1;
		$hacklist_on	= 0;
		$mod_block_bg	= 0;

		if ($this->config['dl_use_hacklist'])
		{
			$this->template->assign_var('S_DL_USE_HACKLIST', $this->dlext_constants::DL_TRUE);
			$hacklist_on = $this->dlext_constants::DL_TRUE;
			$bg_row = 1 - $bg_row;
		}

		if ($index[$cat_id]['allow_mod_desc'])
		{
			$this->template->assign_var('S_DL_ALLOW_EDIT_MOD_DESC', $this->dlext_constants::DL_TRUE);
			$mod_block_bg = ($bg_row) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
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

		if ($this->config['dl_upload_traffic_count'] && !$this->config['dl_traffic_off'])
		{
			$s_upload_traffic = $this->dlext_constants::DL_TRUE;
		}
		else
		{
			$s_upload_traffic = $this->dlext_constants::DL_FALSE;
		}

		if ($this->config['dl_traffic_off'])
		{
			$s_hidden_fields += ['file_traffic' => 0];
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

		$tmp_ary				= $this->dlext_format->dl_size($file_traffic, 2, 'select');
		$file_traffic_out		= $tmp_ary['size_out'];
		$data_range_select		= $tmp_ary['range'];

		$tmp_ary				= $this->dlext_format->dl_size($file_size, 2, 'select');
		$file_extern_size_out	= $tmp_ary['size_out'];
		$file_extern_size_range	= $tmp_ary['range'];

		unset($tmp_ary);

		$template_ary = [
			'DL_THUMBNAIL_SECOND'		=> $thumbnail_explain,
			'DL_EXT_BLACKLIST'			=> $blacklist_explain,

			'DL_DESCRIPTION'			=> $description,
			'DL_LONG_DESC'				=> $long_desc,
			'DL_TRAFFIC'				=> $file_traffic_out,
			'DL_APPROVE'				=> $approve,
			'DL_MOD_DESC'				=> $mod_desc,
			'DL_MOD_LIST'				=> $mod_list,
			'DL_MOD_REQUIRE'			=> $require,
			'DL_MOD_TEST'				=> $mod_test,
			'DL_MOD_TODO'				=> $todo,
			'DL_MOD_WARNING'			=> $warning,
			'DL_HACK_AUTHOR'			=> $hack_author,
			'DL_HACK_AUTHOR_EMAIL'		=> $hack_author_email,
			'DL_HACK_AUTHOR_WEBSITE'	=> $hack_author_website,
			'DL_HACK_DL_URL'			=> $hack_dl_url,
			'DL_HACK_VERSION'			=> $hack_version,
			'DL_THUMBNAIL'				=> append_sid($thumbnail),
			'DL_URL'					=> $dl_extern_url,
			'DL_CHECKEXTERN'			=> $checkextern,
			'DL_FILE_EXT_SIZE'			=> $file_extern_size_out,
			'DL_FORMATED_HINT_TEXT'		=> $formated_hint_text,

			'DL_HACKLIST_BG'			=> ($hacklist_on) ? ' bg2' : '',
			'DL_MOD_BLOCK_BG'			=> ($mod_block_bg) ? ' bg2' : '',
			'DL_VERSION_SELECT_SIZE'	=> $multiple_size,
			'DL_MAX_UPLOAD_SIZE' 		=> $this->language->lang('DL_UPLOAD_MAX_FILESIZE', $this->dlext_physical->dl_max_upload_size()),

			'S_DL_CAT_ID_NAME'			=> 'new_cat',
			'S_DL_TODO_LINK_ONOFF'		=> ($this->config['dl_todo_onoff']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
			'S_DL_CHECK_FREE'			=> $dl_file['free'],
			'S_DL_TRAFFIC_RANGE'		=> $data_range_select,
			'S_DL_FILE_EXT_SIZE_RANGE'	=> $file_extern_size_range,
			'S_DL_HACKLIST'				=> $hacklist,
			'S_DL_UPLOAD_TRAFFIC'		=> $s_upload_traffic,
			'S_DL_SELECT_VER_DEL'		=> $total_versions,
			'S_DL_DOWNLOADS_ACTION'		=> $this->helper->route('oxpus_dlext_mcp_edit'),
			'S_DL_TRAFFIC'				=> $this->config['dl_traffic_off'],
			'S_DL_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields),

			'U_DL_GO_BACK'				=> $this->helper->route('oxpus_dlext_details', ['df_id' => $df_id])
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

		$this->template->assign_vars($template_ary);

		// Init and display the custom fields with the existing data
		$cp->get_profile_fields($df_id);
		$cp->generate_profile_fields($this->user->get_iso_lang_id());

		$this->template->assign_var('S_DL_VERSION_ON', $this->dlext_constants::DL_TRUE);

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter('mcp');
		$this->dlext_footer->handle();

		return $this->helper->render('@oxpus_dlext/mcp/dl_mcp_edit.html', $this->language->lang('MCP'));
	}
}
