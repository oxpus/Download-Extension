<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\mcp;

use Symfony\Component\DependencyInjection\Container;

class mcp_edit
{
	/* @var string phpBB root path */
	protected $root_path;

	/* @var string phpEx */
	protected $php_ext;

	/* @var Container */
	protected $phpbb_container;

	/* @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/* @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\language\language */
	protected $language;

	/** @var extension owned objects */
	protected $ext_path;
	protected $ext_path_web;
	protected $ext_path_ajax;

	protected $dlext_auth;
	protected $dlext_email;
	protected $dlext_extra;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_init;
	protected $dlext_main;
	protected $dlext_physical;
	protected $dlext_topic;

	/**
	* Constructor
	*
	* @param string									$root_path
	* @param string									$php_ext
	* @param Container 								$phpbb_container
	* @param \phpbb\extension\manager				$phpbb_extension_manager
	* @param \phpbb\path_helper						$phpbb_path_helper
	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param \phpbb\config\config					$config
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\auth\auth						$auth
	* @param \phpbb\request\request_interface 		$request
	* @param \phpbb\template\template				$template
	* @param \phpbb\user							$user
	* @param \phpbb\language\language				$language
	*/
	public function __construct(
		$root_path,
		$php_ext,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\auth\auth $auth,
		\phpbb\request\request_interface $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		$dlext_auth,
		$dlext_email,
		$dlext_extra,
		$dlext_files,
		$dlext_format,
		$dlext_init,
		$dlext_main,
		$dlext_physical,
		$dlext_topic
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->phpbb_container 			= $phpbb_container;
		$this->phpbb_extension_manager 	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->auth						= $auth;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_email				= $dlext_email;
		$this->dlext_extra				= $dlext_extra;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_init				= $dlext_init;
		$this->dlext_main				= $dlext_main;
		$this->dlext_physical			= $dlext_physical;
		$this->dlext_topic				= $dlext_topic;
	}

	public function handle()
	{
		$nav_view = 'modcp';
		$modcp_mode = 'edit';

		// Include the default base init script
		include_once($this->ext_path . 'phpbb/includes/base_init.' . $this->php_ext);

		if (!$df_id)
		{
			redirect($this->helper->route('oxpus_dlext_mcp_manage', array('cat_id' => $cat_id)));
		}

		if ($cancel && $file_option == 3)
		{
			redirect($this->helper->route('oxpus_dlext_details', array('view' => 'detail', 'df_id' => $df_id)));
		}

		$own_edit = false;
		
		if ($this->config['dl_edit_own_downloads'])
		{
			$sql = 'SELECT add_user FROM ' . DOWNLOADS_TABLE . '
				WHERE id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);
			$add_user = $this->db->sql_fetchfield('add_user');
			$this->db->sql_freeresult($result);
		
			if ($add_user == $this->user->data['user_id'])
			{
				$own_edit = true;
			}
		}
		
		if ($own_edit == true)
		{
			$access_cat[0] = $cat_id;
			$deny_modcp = false;
		}

		unset($dl_index);

		if ($deny_modcp)
		{
			trigger_error($this->language->lang('DL_NO_PERMISSION'));
		}

		add_form_key('dl_modcp');

		// Initiate custom fields
		include($this->ext_path . 'phpbb/includes/fields.' . $this->php_ext);
		$cp = new \oxpus\dlext\phpbb\includes\custom_profile();

		/*
		* And now the different work from here
		*/
		if ($action == 'save' && $submit)
		{
			if (!check_form_key('dl_modcp'))
			{
				trigger_error('FORM_INVALID');
			}

			if ($file_option == 3)
			{
				if (!sizeof($file_ver_del))
				{
					trigger_error($this->language->lang('DL_VER_DEL_ERROR'), E_USER_ERROR);
				}

				if (confirm_box(true))
				{
					$dl_ids = array();

					for ($i = 0; $i < sizeof($file_ver_del); $i++)
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

						if (sizeof($dl_ids))
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

					if (sizeof($dl_ids))
					{
						$sql = 'DELETE FROM ' . DL_VERSIONS_TABLE . '
							WHERE ' . $this->db->sql_in_set('ver_id', $dl_ids);
						$this->db->sql_query($sql);

						$sql = 'DELETE FROM ' . DL_VER_FILES_TABLE . '
							WHERE ' . $this->db->sql_in_set('ver_id', $dl_ids);
						$this->db->sql_query($sql);
					}

					redirect($this->helper->route('oxpus_dlext_details', array('df_id' => $df_id)));
				}
				else
				{
					$this->template->assign_var('S_DELETE_FILES_CONFIRM', true);

					$s_hidden_fields = array(
						'action'		=> 'save',
						'cat_id'		=> $cat_id,
						'df_id'			=> $df_id,
						'submit'		=> 1,
						'file_ver_opt'	=> 3,
					);

					for ($i = 0; $i < sizeof($file_ver_del); $i++)
					{
						$s_hidden_fields = array_merge($s_hidden_fields, array('file_ver_del[' . $i . ']' => $file_ver_del[$i]));
					}

					confirm_box(false, $this->language->lang('DL_CONFIRM_DEL_VERSIONS'), build_hidden_fields($s_hidden_fields), 'dl_confirm_body.html');
				}
			}
			else
			{
				$new_version			= false;

				$approve				= $this->request->variable('approve', 0);
				$description			= $this->request->variable('description', '', true);
				$file_traffic			= $this->request->variable('file_traffic', 0);
				$long_desc				= $this->request->variable('long_desc', '', true);
				$file_name				= $this->request->variable('file_name', '', true);

				$file_free				= $this->request->variable('file_free', 0);
				$file_extern			= $this->request->variable('file_extern', 0);
				$file_extern_size		= $this->request->variable('file_extern_size', '');

				$test					= $this->request->variable('test', '', true);
				$require				= $this->request->variable('require', '', true);
				$todo					= $this->request->variable('todo', '', true);
				$warning				= $this->request->variable('warning', '', true);
				$mod_desc				= $this->request->variable('mod_desc', '', true);
				$mod_list				= $this->request->variable('mod_list', 0);
				$mod_list				= ($mod_list) ? 1 : 0;

				$send_notify			= $this->request->variable('send_notify', 0);
				$disable_popup_notify	= $this->request->variable('disable_popup_notify', 0);
				$change_time			= $this->request->variable('change_time', 0);
				$del_thumb				= $this->request->variable('del_thumb', 0);
				$click_reset			= $this->request->variable('click_reset', 0);

				$hacklist				= $this->request->variable('hacklist', 0);
				$hack_author			= $this->request->variable('hack_author', '', true);
				$hack_author_email		= $this->request->variable('hack_author_email', '', true);
				$hack_author_website	= $this->request->variable('hack_author_website', '', true);
				$hack_version			= $this->request->variable('hack_version', '', true);
				$hack_dl_url			= $this->request->variable('hack_dl_url', '', true);

				$file_hash			= '';

				$allow_bbcode		= ($this->config['allow_bbcode']) ? true : false;
				$allow_urls			= true;
				$allow_smilies		= ($this->config['allow_smilies']) ? true : false;

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

				$dl_file = array();
				$dl_file = $this->dlext_files->all_files(0, 0, 'ASC', 0, $df_id, true, '*');

				$real_file_old	= $dl_file['real_file'];
				$file_name_old	= $dl_file['file_name'];
				$file_size_old	= $dl_file['file_size'];
				$file_cat_old	= $dl_file['cat'];

				$ext_blacklist = $this->dlext_auth->get_ext_blacklist();

				$this->language->add_lang('posting');

				if (!$file_extern)
				{
					$factory = $this->phpbb_container->get('files.factory');
					$form_name = 'dl_name';
					$file = $this->request->file($form_name);
					$file_extension = str_replace('.', '', trim(strrchr(strtolower($file['name']), '.')));
					$allowed_extensions = array($file_extension);
					$upload = $factory->get('upload')
						->set_allowed_extensions($allowed_extensions)
						->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : false));

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

					$error_count = sizeof($upload_file->error);
					if ($error_count > 1 && $file_name)
					{
						$upload_file->remove();
						trigger_error(implode('<br />', $upload_file->error), E_USER_ERROR);
					}

					$upload_file->error = array();

					if ($file_name)
					{
						if (!$this->config['dl_traffic_off'])
						{
							$remain_traffic = 0;

							if ($this->user->data['is_registered'] && DL_OVERALL_TRAFFICS == true)
							{
								$remain_traffic = $this->config['dl_overall_traffic'] - $this->config['dl_remain_traffic'];
							}
							else if (!$this->user->data['is_registered'] && DL_GUESTS_TRAFFICS == true)
							{
								$remain_traffic = $this->config['dl_overall_guest_traffic'] - $this->config['dl_remain_guest_traffic'];
							}

							if(!$file_size || ($remain_traffic && $file_size > $remain_traffic && $this->config['dl_upload_traffic_count']))
							{
								$upload_file->remove();
								trigger_error($this->language->lang('DL_NO_UPLOAD_TRAFFIC'), E_USER_ERROR);
							}
						}

						$dl_path = $index[$cat_id]['cat_path'];

						if ($file_option == 2 && !$file_version)
						{
							@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $dl_path . $real_file_old);
						}

						$real_file_new = md5($file_name) . '.' . $file_extension;

						$i = 1;
						while (file_exists(DL_EXT_FILEBASE_PATH. 'downloads/' . $dl_path . $real_file_new))
						{
							$real_file_new = $i . md5($file_name);
							$i++;
						}

						if ($index[$cat_id]['statistics'])
						{
							if ($index[$cat_id]['stats_prune'])
							{
								$stat_prune = $this->dlext_main->dl_prune_stats($cat_id, $index[$cat_id]['stats_prune']);
							}

							$sql = 'INSERT INTO ' . DL_STATS_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
								'cat_id'		=> $new_cat,
								'id'			=> $df_id,
								'user_id'		=> $this->user->data['user_id'],
								'username'		=> $this->user->data['username'],
								'traffic'		=> $file_size,
								'direction'		=> 2,
								'user_ip'		=> $this->user->data['session_ip'],
								'time_stamp'	=> time()));
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
					$file_new = false;
				}
				else
				{
					$file_new = true;
				}

				if ($file_extern)
				{
					$file_size = $this->dlext_format->resize_value('dl_extern_size', $file_extern_size);
				}

				if (!$file_extern && $file_name && $file_new)
				{
					if (substr($dl_path, -1) == '/')
					{
						$dest_path = DL_EXT_FILEBASE_PATH. 'downloads/' . substr($dl_path, 0, -1);
					}
					else
					{
						$dest_path = DL_EXT_FILEBASE_PATH. 'downloads/' . $dl_path;
					}
					$dest_path = str_replace($this->root_path, '', $dest_path);

					$file['name'] = $real_file_new;
					$upload_file->set_upload_ary($file);
					$upload_file->move_file($dest_path, false, false, CHMOD_ALL);

					$error_count = sizeof($upload_file->error);
					if ($error_count)
					{
						$upload_file->remove();
						trigger_error(implode('<br />', $upload_file->error), E_USER_ERROR);
					}

					$hash_method = $this->config['dl_file_hash_algo'];
					$func_hash = $hash_method . '_file';
					$file_hash = @$func_hash(DL_EXT_FILEBASE_PATH. 'downloads/' . $dl_path . $real_file_new);
				}

				if ($this->config['dl_thumb_fsize'] && $index[$cat_id]['allow_thumbs'] && !$del_thumb)
				{
					$min_pic_width = 10;
					$allowed_imagetypes = array('gif','png','jpg','bmp');

					$factory = $this->phpbb_container->get('files.factory');
					$upload_image = $factory->get('upload')
						->set_allowed_extensions($allowed_imagetypes)
						->set_max_filesize($this->config['dl_thumb_fsize'])
						->set_allowed_dimensions(
							$min_pic_width,
							$min_pic_width,
							$this->config['dl_thumb_xsize'],
							$this->config['dl_thumb_ysize'])
						->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : false));

					$form_name = 'thumb_name';

					$upload_thumb_file = $this->request->file($form_name);
					unset($upload_thumb_file['local_mode']);
					$thumb_file = $upload_image->handle_upload('files.types.form', $form_name);

					$thumb_size = $upload_thumb_file['size'];
					$thumb_temp = $upload_thumb_file['tmp_name'];
					$thumb_name = $upload_thumb_file['name'];

					$error_count = sizeof($thumb_file->error);
					if ($error_count > 1 && $thumb_name)
					{
						$thumb_file->remove();
						trigger_error(implode('<br />', $thumb_file->error), E_USER_ERROR);
					}

					$thumb_file->error = array();

					if ($thumb_name)
					{
						$pic_size 	= @getimagesize($thumb_temp);
						$pic_width	= $pic_size[0];
						$pic_height	= $pic_size[1];

						if (!$pic_width || !$pic_height)
						{
							$thumb_file->remove();
							trigger_error($this->language->lang('DL_UPLOAD_ERROR'), E_USER_ERROR);
						}

						if ($pic_width > $this->config['dl_thumb_xsize'] || $pic_height > $this->config['dl_thumb_ysize'] || (sprintf("%u", @filesize($thumb_temp) > $this->config['dl_thumb_fsize'])))
						{
							$thumb_file->remove();
							trigger_error($this->language->lang('DL_THUMB_TO_BIG'), E_USER_ERROR);
						}
					}
				}

				if (isset($thumb_name) && $thumb_name != '')
				{
					@unlink(DL_EXT_FILEBASE_PATH . 'thumbs/' . $dl_file['thumbnail']);
					@unlink(DL_EXT_FILEBASE_PATH . 'thumbs/' . $df_id . '_' . $thumb_name);

					$upload_thumb_file['name'] = $df_id . '_' . $thumb_name;
					$dest_folder = str_replace($this->root_path, '', substr(DL_EXT_FILEBASE_PATH . 'thumbs/', 0, -1));

					$thumb_file->set_upload_ary($upload_thumb_file);
					$thumb_file->move_file($dest_folder, false, false, CHMOD_ALL);

					$thumb_message = '<br />' . $this->language->lang('DL_THUMB_UPLOAD');

					$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
						'thumbnail' => $df_id . '_' . $thumb_name)) . ' WHERE id = ' . (int) $df_id;
					$this->db->sql_query($sql);
				}
				else if ($del_thumb)
				{
					$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
						'thumbnail' => '')) . ' WHERE id = ' . (int) $df_id;
					$this->db->sql_query($sql);

					@unlink(DL_EXT_FILEBASE_PATH . 'thumbs/' . $dl_file['thumbnail']);

					$thumb_message = '<br />'.$this->language->lang('DL_THUMB_DEL');
				}
				else
				{
					$thumb_message = '';
				}

				// validate custom profile fields
				$error = $cp_data = $cp_error = array();
				$cp->submit_cp_field($this->user->get_iso_lang_id(), $cp_data, $error);

				// Stop here, if custom fields are invalid!
				if (sizeof($error))
				{
					trigger_error(implode('<br />', $error), E_USER_WARNING);
				}

				if ($df_id && $new_cat)
				{
					/*
					* Enter new version if choosen
					*/
					if (!$file_option || $file_option == 1)
					{
						$sql = 'INSERT INTO ' . DL_VERSIONS_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
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
						));

						$this->db->sql_query($sql);

						$new_version = $this->db->sql_nextid();
					}
					else if ($file_option == 2 && $file_version)
					{
						$sql = 'SELECT * FROM ' . DL_VERSIONS_TABLE . '
							WHERE dl_id = ' . (int) $df_id . '
								AND ver_id = ' . (int) $file_version;
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						if ($file_new)
						{
							@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $dl_path . $real_old_file);
						}

						$sql = 'UPDATE ' . DL_VERSIONS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
							'ver_file_name'		=> ($file_new) ? $file_name : $row['ver_file_name'],
							'ver_real_file'		=> ($file_new) ? $real_file_new : $row['ver_real_file'],
							'ver_file_hash'		=> ($file_new) ? $file_hash : $row['ver_file_hash'],
							'ver_file_size'		=> ($file_new) ? $file_size : $row['ver_file_size'],
							'ver_change_time'	=> time(),
							'ver_change_user'	=> $this->user->data['user_id'],
							'ver_version'		=> $hack_version,
						)) . ' WHERE dl_id = ' . (int) $df_id . ' AND ver_id = ' . (int) $file_version;

						$this->db->sql_query($sql);
					}

					unset($sql_array);

					if (!$index[$cat_id]['allow_mod_desc'] && !($this->auth->acl_get('a_') && $this->user->data['is_registered']))
					{
						$test = $require = $warning = $mod_desc = '';
					}

					$sql_array = array(
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
						'mod_desc'				=> $mod_desc);

					if (!$file_option || ($file_option == 2 && !$file_version && $hack_version))
					{
						$sql_array = array_merge($sql_array, array(
							'file_name'				=> $file_name,
							'real_file'				=> $real_file_new,
							'file_hash'				=> $file_hash,
							'file_size'				=> $file_size,
							'hack_version'			=> $hack_version));
					}
					else
					{
						$sql_array = array_merge($sql_array, array(
							'file_name'		=> $dl_file['file_name'],
							'real_file'		=> $dl_file['real_file'],
							'file_hash'		=> $dl_file['file_hash'],
							'file_size'		=> $dl_file['file_size'],
							'hack_version'	=> $dl_file['hack_version'],
						));
					}

					if (!$change_time)
					{
						$sql_array = array_merge($sql_array, array(
							'change_time'	=> time(),
							'change_user'	=> $this->user->data['user_id']));
					}

					if ($click_reset)
					{
						$sql_array = array_merge($sql_array, array(
							'klicks' => 0));
					}

					if ($index[$cat_id]['allow_mod_desc'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered']))
					{
						$sql_array = array_merge($sql_array, array(
							'mod_list'			=> $mod_list,
							'mod_desc_uid'		=> $mod_desc_uid,
							'mod_desc_bitfield'	=> $mod_desc_bitfield,
							'mod_desc_flags'	=> $mod_desc_flags,
							'warn_uid'			=> $warn_uid,
							'warn_bitfield'		=> $warn_bitfield,
							'warn_flags'		=> $warn_flags));
					}

					$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE id = ' . (int) $df_id;
					$this->db->sql_query($sql);

					$this->dlext_topic->update_topic($dl_file['dl_topic'], $df_id);

					if ($approve)
					{
						$processing_user	= $this->dlext_auth->dl_auth_users($cat_id, 'auth_view');
						$email_template		= 'downloads_change_notify';

						$this->dlext_topic->gen_dl_topic($df_id);
					}
					else
					{
						$processing_user	= $this->dlext_auth->dl_auth_users($cat_id, 'auth_mod');
						$email_template		= 'downloads_approve_notify';
					}

					$sql = 'SELECT fav_user_id FROM ' . DL_FAVORITES_TABLE . '
						WHERE fav_dl_id = ' . (int) $df_id;
					$result = $this->db->sql_query($sql);

					$fav_user = array();

					while ($row = $this->db->sql_fetchrow($result))
					{
						$fav_user[] = $row['fav_user_id'];
					}

					$this->db->sql_freeresult($result);

					$sql_fav_user = (sizeof($fav_user)) ? ' AND ' . $this->db->sql_in_set('user_id', $fav_user) : '';

					if (!$this->config['dl_disable_email'] && !$send_notify && $sql_fav_user)
					{
						$sql = 'SELECT user_email, username, user_lang FROM ' . USERS_TABLE . "
							WHERE user_allow_fav_download_email = 1
								$sql_fav_user
								AND (" . $this->db->sql_in_set('user_id', explode(',', $processing_user)) . '
									OR user_type = ' . USER_FOUNDER . ')';

						$mail_data = array(
							'query'				=> $sql,
							'email_template'	=> $email_template,
							'description'		=> $description,
							'long_desc'			=> $long_desc,
							'cat_name'			=> $index[$cat_id]['cat_name_nav'],
							'cat_id'			=> $cat_id,
						);

						$this->dlext_email->send_dl_notify($mail_data);
					}

					if (!$this->config['dl_disable_popup'] && !$disable_popup_notify)
					{
						$sql = 'UPDATE ' . USERS_TABLE . "
							SET user_new_download = 1
							WHERE user_allow_fav_download_popup = 1
								$sql_fav_user
								AND " . $this->db->sql_in_set('user_id', explode(',', $processing_user));
						$this->db->sql_query($sql);

						$notification = $this->phpbb_container->get('notification_manager');
						$notification_data = array('notification_id' => $df_id);
						$notification->add_notifications('oxpus.dlext.notification.type.dlext', $notification_data);
						}

					if ($this->config['dl_upload_traffic_count'] && !$file_extern && !$this->config['dl_traffic_off'])
					{
						$this->config['dl_remain_traffic'] += $file_size;

						$this->config->set('dl_remain_traffic', $this->config['dl_remain_traffic']);
					}

					if ($file_cat_old <> $new_cat && !$file_extern && !$file_temp)
					{
						$old_path = $index[$file_cat_old]['cat_path'];
						$new_path = $index[$new_cat]['cat_path'];

						if ($new_path != $old_path)
						{
							@copy(DL_EXT_FILEBASE_PATH. 'downloads/' . $old_path . $real_file_old, DL_EXT_FILEBASE_PATH. 'downloads/' . $new_path . $real_file_new);
							@chmod(DL_EXT_FILEBASE_PATH. 'downloads/' . $new_path . $real_file_new, 0777);
							@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $old_path . $real_file_old);

							$sql = 'SELECT ver_real_file FROM ' . DL_VERSIONS_TABLE . '
								WHERE dl_id = ' . (int) $df_id;
							$result = $this->db->sql_query($sql);

							while ($row = $this->db->sql_fetchrow($result))
							{
								$real_ver_file = $row['ver_real_file'];
								@copy(DL_EXT_FILEBASE_PATH. 'downloads/' . $old_path . $real_ver_file, DL_EXT_FILEBASE_PATH. 'downloads/' . $new_path . $real_ver_file);
								@chmod(DL_EXT_FILEBASE_PATH. 'downloads/' . $new_path . $real_ver_file, 0777);
								@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $old_path . $real_ver_file);
							}

							$this->db->sql_freeresult($result);
						}

						$sql = 'UPDATE ' . DL_STATS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
							'cat_id' => $new_cat)) . ' WHERE id = ' . (int) $df_id;
						$this->db->sql_query($sql);

						$sql = 'UPDATE ' . DL_COMMENTS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
							'cat_id' => $new_cat)) . ' WHERE id = ' . (int) $df_id;
						$this->db->sql_query($sql);
					}

					// Purge the files cache
					@unlink(DL_EXT_CACHE_PATH . 'data_dl_cat_counts.' . $this->php_ext);
					@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_p.' . $this->php_ext);
					@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_preset.' . $this->php_ext);
				}
			}

			// Update Custom Fields
			$cp->update_profile_field_data($df_id, $cp_data);

			$ver_message = '';

			if ($new_version)
			{
				$version_url	= $this->helper->route('oxpus_dlext_version', array('ver_id' => $new_version));
				$ver_message	= '<br /><br />' . $this->language->lang('CLICK_VIEW_NEW_VERSION', '<a href="' . $version_url . '">', '</a>');
			}

			if ($own_edit)
			{
				$meta_url	= $this->helper->route('oxpus_dlext_details', array('df_id' => $df_id));
				$message	= $this->language->lang('DOWNLOAD_UPDATED') . $thumb_message . '<br /><br />' . $this->language->lang('CLICK_RETURN_DOWNLOAD_DETAILS', '<a href="' . $meta_url . '">', '</a>') . $ver_message;
			}
			else
			{
				$meta_url		= $this->helper->route('oxpus_dlext_mcp_manage', array('cat_id' => $cat_id));
				$return_string	= ($action == 'approve') ? $this->language->lang('CLICK_RETURN_MODCP_APPROVE') : $this->language->lang('CLICK_RETURN_MODCP_MANAGE');
				$message		= $this->language->lang('DOWNLOAD_UPDATED') . $thumb_message . '<br /><br />' . sprintf($return_string, '<a href="' . $meta_url . '">', '</a>') . $ver_message;
			}

			if ($cat_auth['auth_up'])
			{
				$message .= '<br /><br />' . $this->language->lang('DL_UPLOAD_ONE_MORE', '<a href="' . $this->helper->route('oxpus_dlext_upload', array('cat_id' => $cat_id)) . '">', '</a>');
			}

			if (!$new_version)
			{
				meta_refresh(3, $meta_url);
			}

			trigger_error($message);
		}

		$dl_file = array();
		$dl_file = $this->dlext_files->all_files(0, '', 'ASC', '', $df_id, true, '*');

		$s_hidden_fields = array(
			'action'	=> 'save',
			'cat_id'	=> $cat_id,
			'df_id'		=> $df_id
		);

		$description			= $dl_file['description'];
		$file_traffic			= $dl_file['file_traffic'];
		$file_size				= $dl_file['file_size'];
		$cat					= $dl_file['cat'];
		$long_desc				= $dl_file['long_desc'];
		$approve				= $dl_file['approve'];
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
		$mod_list				= ($dl_file['mod_list']) ? 'checked="checked"' : '';

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
			$this->template->assign_var('S_ALLOW_THUMBS', true);

			$thumbnail = DL_EXT_FILEBASE_PATH . 'thumbs/' . $thumbnail;
			if ($dl_file['thumbnail'] && file_exists($thumbnail))
			{
				$this->template->assign_var('S_THUMBNAIL', true);
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

		$s_check_free = '<select name="file_free">';
		$s_check_free .= '<option value="0">' . $this->language->lang('NO') . '</option>';
		$s_check_free .= '<option value="1">' . $this->language->lang('YES') . '</option>';
		$s_check_free .= '<option value="2">' . $this->language->lang('DL_IS_FREE_REG') . '</option>';
		$s_check_free .= '</select>';

		$s_traffic_range = '<select name="dl_t_quote">';
		$s_traffic_range .= '<option value="byte">' . $this->language->lang('DL_BYTES') . '</option>';
		$s_traffic_range .= '<option value="kb">' . $this->language->lang('DL_KB') . '</option>';
		$s_traffic_range .= '<option value="mb">' . $this->language->lang('DL_MB') . '</option>';
		$s_traffic_range .= '<option value="gb">' . $this->language->lang('DL_GB') . '</option>';
		$s_traffic_range .= '</select>';

		$s_file_ext_size_range = '<select name="dl_e_quote">';
		$s_file_ext_size_range .= '<option value="byte">' . $this->language->lang('DL_BYTES') . '</option>';
		$s_file_ext_size_range .= '<option value="kb">' . $this->language->lang('DL_KB') . '</option>';
		$s_file_ext_size_range .= '<option value="mb">' . $this->language->lang('DL_MB') . '</option>';
		$s_file_ext_size_range .= '<option value="gb">' . $this->language->lang('DL_GB') . '</option>';
		$s_file_ext_size_range .= '</select>';

		$s_hacklist = '<select name="hacklist">';
		$s_hacklist .= '<option value="0">' . $this->language->lang('NO') . '</option>';
		$s_hacklist .= '<option value="1">' . $this->language->lang('YES') . '</option>';
		$s_hacklist .= '<option value="2">' . $this->language->lang('DL_MOD_LIST') . '</option>';
		$s_hacklist .= '</select>';

		$tmp_ary				= $this->dlext_format->dl_size($file_traffic, 2, 'select');
		$file_traffic_out		= $tmp_ary['size_out'];
		$data_range_select		= $tmp_ary['range'];

		$tmp_ary				= $this->dlext_format->dl_size($file_size, 2, 'select');
		$file_extern_size_out	= $tmp_ary['size_out'];
		$file_extern_size_range	= $tmp_ary['range'];

		unset($tmp_ary);

		$s_traffic_range		= str_replace('value="' . $data_range_select . '">', 'value="' . $data_range_select . '" selected="selected">', $s_traffic_range);
		$s_file_ext_size_range	= str_replace('value="' . $file_extern_size_range . '">', 'value="' . $file_extern_size_range . '" selected="selected">', $s_file_ext_size_range);
		$s_hacklist				= str_replace('value="' . $hacklist . '">', 'value="' . $hacklist . '" selected="selected">', $s_hacklist);
		$s_check_free			= str_replace('value="' . $dl_file['free'] . '">', 'value="' . $dl_file['free'] . '" selected="selected">', $s_check_free);

		if (!$own_edit)
		{
			$select_code = '<select name="new_cat">';
			$select_code .= $this->dlext_extra->dl_dropdown(0, 0, $cat_id, 'auth_up');
			$select_code .= '</select>';

			$this->template->assign_var('S_CAT_CHOOSE', true);
		}
		else
		{
			$select_code = '';

			$s_hidden_fields = array_merge($s_hidden_fields, array('new_cat' => $cat_id));
		}

		if ($dl_file['extern'])
		{
			$checkextern = 'checked="checked"';
			$dl_extern_url = $dl_file['file_name'];
		}
		else
		{
			$checkextern = '';
			$dl_extern_url = '';
		}

		$this->template->assign_var('S_MODCP', true);

		if (!$this->config['dl_disable_email'])
		{
			$this->template->assign_var('S_EMAIL_BLOCK', true);
		}

		if (!$this->config['dl_disable_popup'])
		{
			$this->template->assign_var('S_CHANGE_TIME', true);
		}

		if (!$this->config['dl_disable_popup_notify'])
		{
			$this->template->assign_var('S_POPUP_NOTIFY', true);
		}

		$this->template->assign_var('S_CLICK_RESET', true);

		$bg_row			= 1;
		$hacklist_on	= 0;
		$mod_block_bg	= 0;

		if ($this->config['dl_use_hacklist'])
		{
			$this->template->assign_var('S_USE_HACKLIST', true);
			$hacklist_on = true;
			$bg_row = 1 - $bg_row;
		}

		if ($index[$cat_id]['allow_mod_desc'])
		{
			$this->template->assign_var('S_ALLOW_EDIT_MOD_DESC', true);
			$mod_block_bg = ($bg_row) ? true : 0;
		}

		$ext_blacklist = $this->dlext_auth->get_ext_blacklist();

		if (sizeof($ext_blacklist))
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
		$s_select_ver_del = '<select name="file_ver_del[]" style="max-width: 75%" multiple="multiple" size="' . $multiple_size . '">';
		$s_select_version .= '<option value="0" selected="selected">' . $this->language->lang('DL_VERSION_CURRENT') . '</option>';

		$version_array = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
			$version_array[$row['ver_version'] . ' - ' . $this->user->format_date($row['ver_change_time'])] = $row['ver_id'];
		}

		$this->db->sql_freeresult($result);

		natsort($version_array);
		$version_array = array_unique(array_reverse($version_array));

		foreach($version_array as $key => $value)
		{
			$s_select_version .= '<option value="' . $value . '">' . $key . '</option>';
			$s_select_ver_del .= '<option value="' . $value . '">' . $key . '</option>';
		}

		$s_select_version .= '</select>';
		$s_select_ver_del .= '</select>';

		if (!$total_versions)
		{
			$s_select_ver_del = '';
		}

		if ($this->config['dl_upload_traffic_count'] && !$this->config['dl_traffic_off'])
		{
			$s_upload_traffic = true;
		}
		else
		{
			$s_upload_traffic = false;
		}

		$dl_files_page_title = $this->language->lang('DL_FILES_TITLE');

		$this->template->assign_vars(array(
			'DL_FILES_TITLE'					=> $dl_files_page_title,

			'DL_THUMBNAIL_SECOND'				=> $thumbnail_explain,
			'EXT_BLACKLIST'						=> $blacklist_explain,
			'L_DL_NAME_EXPLAIN'					=> 'DL_NAME',
			'L_DL_APPROVE_EXPLAIN'				=> 'DL_APPROVE',
			'L_DL_CAT_NAME_EXPLAIN'				=> 'DL_CHOOSE_CATEGORY',
			'L_DL_DESCRIPTION_EXPLAIN'			=> 'DL_FILE_DESCRIPTION',
			'L_DL_EXTERN_EXPLAIN'				=> 'DL_EXTERN_UP',
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
			'L_DL_TRAFFIC_EXPLAIN'				=> 'DL_TRAFFIC',
			'L_DL_UPLOAD_FILE_EXPLAIN'			=> 'DL_UPLOAD_FILE',
			'L_DL_THUMBNAIL_EXPLAIN'			=> 'DL_THUMB',
			'L_CHANGE_TIME_EXPLAIN'				=> 'DL_NO_CHANGE_EDIT_TIME',
			'L_DISABLE_POPUP_EXPLAIN'			=> 'DL_DISABLE_POPUP',
			'L_DL_SEND_NOTIFY_EXPLAIN'			=> 'DL_DISABLE_EMAIL',
			'L_CLICK_RESET_EXPLAIN'				=> 'DL_KLICKS_RESET',

			'DESCRIPTION'			=> $description,
			'SELECT_CAT'			=> $select_code,
			'LONG_DESC'				=> $long_desc,
			'TRAFFIC'				=> $file_traffic_out,
			'APPROVE'				=> ($approve) ? 'checked="checked"' : '',
			'MOD_DESC'				=> $mod_desc,
			'MOD_LIST'				=> $mod_list,
			'MOD_REQUIRE'			=> $require,
			'MOD_TEST'				=> $mod_test,
			'MOD_TODO'				=> $todo,
			'MOD_WARNING'			=> $warning,
			'HACK_AUTHOR'			=> $hack_author,
			'HACK_AUTHOR_EMAIL'		=> $hack_author_email,
			'HACK_AUTHOR_WEBSITE'	=> $hack_author_website,
			'HACK_DL_URL'			=> $hack_dl_url,
			'HACK_VERSION'			=> $hack_version,
			'HACKLIST_EVER'			=> ($hacklist == 2) ? 'checked="checked"' : '',
			'HACKLIST_NO'			=> ($hacklist == 0) ? 'checked="checked"' : '',
			'HACKLIST_YES'			=> ($hacklist == 1) ? 'checked="checked"' : '',
			'THUMBNAIL'				=> $thumbnail,
			'URL'					=> $dl_extern_url,
			'CHECKEXTERN'			=> $checkextern,
			'FILE_EXT_SIZE'			=> $file_extern_size_out,

			'HACKLIST_BG'	=> ($hacklist_on) ? ' bg2' : '',
			'MOD_BLOCK_BG'	=> ($mod_block_bg) ? ' bg2' : '',

			'MAX_UPLOAD_SIZE' => $this->language->lang('DL_UPLOAD_MAX_FILESIZE', $this->dlext_physical->dl_max_upload_size()),

			'ENCTYPE' => 'enctype="multipart/form-data"',

			'S_TODO_LINK_ONOFF'		=> ($this->config['dl_todo_onoff']) ? true : false,
			'S_SELECT_VERSION'		=> $s_select_version,
			'S_SELECT_VER_DEL'		=> $s_select_ver_del,
			'S_CHECK_FREE'			=> $s_check_free,
			'S_TRAFFIC_RANGE'		=> $s_traffic_range,
			'S_FILE_EXT_SIZE_RANGE'	=> $s_file_ext_size_range,
			'S_HACKLIST'			=> $s_hacklist,
			'S_UPLOAD_TRAFFIC'		=> $s_upload_traffic,
			'S_DOWNLOADS_ACTION'	=> $this->helper->route('oxpus_dlext_mcp_edit'),
			'S_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields))
		);

		// Init and display the custom fields with the existing data
		$cp->get_profile_fields($df_id);
		$cp->generate_profile_fields($this->user->get_iso_lang_id());

		$this->template->assign_var('S_VERSION_ON', true);

		return $this->helper->render('dl_mcp_edit.html', $this->language->lang('MCP'));
	}
}
