<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller;

use Symfony\Component\DependencyInjection\Container;

class upload
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

	protected $config_text;
	protected $phpbb_dispatcher;

	protected $dlext_auth;
	protected $dlext_extra;
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
	* @param \phpbb\event\dispatcher_interface		$phpbb_dispatcher
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
		\phpbb\event\dispatcher_interface $phpbb_dispatcher,
		$dlext_auth,
		$dlext_extra,
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
		$this->phpbb_dispatcher			= $phpbb_dispatcher;

		$this->config_text				= $this->phpbb_container->get('config_text');

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_extra				= $dlext_extra;
		$this->dlext_format				= $dlext_format;
		$this->dlext_init				= $dlext_init;
		$this->dlext_main				= $dlext_main;
		$this->dlext_physical			= $dlext_physical;
		$this->dlext_topic				= $dlext_topic;
	}

	public function handle()
	{
		$nav_view = 'upload';

		// Include the default base init script
		include_once($this->ext_path . 'phpbb/includes/base_init.' . $this->php_ext);

		$inc_module = true;
		page_header($this->language->lang('DL_UPLOAD'));

		$cat_auth = [];
		$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);

		$physical_size = $this->dlext_physical->read_dl_sizes();

		if ($physical_size >= $this->config['dl_physical_quota'])
		{
			trigger_error('DL_BLUE_EXPLAIN');
		}

		if (($this->config['dl_stop_uploads'] && !$this->auth->acl_get('a_')) || empty($index) || (!$cat_auth['auth_up'] && !$index[$cat_id]['auth_up'] && !$this->auth->acl_get('a_')))
		{
			trigger_error('DL_NO_PERMISSION');
		}

		// Initiate custom fields
		include($this->ext_path . 'phpbb/includes/fields.' . $this->php_ext);
		$cp = new \oxpus\dlext\phpbb\includes\ custom_profile();

		if ($submit)
		{
			if (!check_form_key('dl_upload'))
			{
				trigger_error('FORM_INVALID');
			}

			$approve			= $this->request->variable('approve', 0);
			$description		= $this->request->variable('description', '', true);
			$file_traffic		= $this->request->variable('file_traffic', 0);
			$long_desc			= $this->request->variable('long_desc', '', true);
			$file_name_name		= $this->request->variable('file_name', '', true);

			$file_free			= $this->request->variable('file_free', 0);
			$file_extern		= $this->request->variable('file_extern', 0);
			$file_extern_size	= $this->request->variable('file_extern_size', '');

			$test				= $this->request->variable('test', '', true);
			$require			= $this->request->variable('require', '', true);
			$todo				= $this->request->variable('todo', '', true);
			$warning			= $this->request->variable('warning', '', true);
			$mod_desc			= $this->request->variable('mod_desc', '', true);
			$mod_list			= $this->request->variable('mod_list', 0);
			$mod_list			= ($mod_list) ? 1 : 0;

			$send_notify			= $this->request->variable('send_notify', 0);

			$hacklist				= $this->request->variable('hacklist', 0);
			$hack_author			= $this->request->variable('hack_author', '', true);
			$hack_author_email		= $this->request->variable('hack_author_email', '', true);
			$hack_author_website	= $this->request->variable('hack_author_website', '', true);
			$hack_version			= $this->request->variable('hack_version', '', true);
			$hack_dl_url			= $this->request->variable('hack_dl_url', '', true);

			if (!$description)
			{
				trigger_error($this->language->lang('NO_SUBJECT'), E_USER_WARNING);
			}

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

			if ($file_extern)
			{
				$file_traffic = 0;
			}
			else
			{
				$file_traffic = $this->dlext_format->resize_value('dl_file_traffic', $file_traffic);
			}

			$ext_blacklist = $this->dlext_auth->get_ext_blacklist();

			$this->language->add_lang('posting');

			if (!$file_extern)
			{
				$factory = $this->phpbb_container->get('files.factory');

				$form_name = 'dl_name';
				$file = $this->request->file($form_name);
				$extension = str_replace('.', '', trim(strrchr(strtolower($file['name']), '.')));
				$allowed_extensions = [$extension];
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

				$upload_file->error = [];

				$error_count = count($upload_file->error);

				if ($error_count > 1 && $file_name)
				{
					$upload_file->remove();
					trigger_error(implode('<br />', $upload_file->error), E_USER_ERROR);
				}

				if (!$file_name)
				{
					$upload_file->remove();
					trigger_error($this->language->lang('DL_NO_FILENAME_ENTERED'), E_USER_ERROR);
				}

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

					if($file_size == 0 || ($remain_traffic && $file_size > $remain_traffic && $this->config['dl_upload_traffic_count']))
					{
						$upload_file->remove();
						trigger_error($this->language->lang('DL_NO_UPLOAD_TRAFFIC'), E_USER_ERROR);
					}
				}

				$dl_path = $index[$cat_id]['cat_path'];

				$real_file = md5($file_name) . '.' . $upload_file->get_extension($file_name);

				$i = 0;

				while(@file_exists(DL_EXT_FILEBASE_PATH. 'downloads/' . $dl_path . $real_file))
				{
					$real_file = md5($i . $file_name);
					++$i;
				}
			}
			else
			{
				if (empty($file_name_name))
				{
					trigger_error($this->language->lang('DL_NO_EXTERNAL_URL'), E_USER_ERROR);
				}

				$file_name = $file_name_name;
				$file_size = $this->dlext_format->resize_value('dl_extern_size', $file_extern_size);
				$real_file = '';
			}

			if ($this->config['dl_thumb_fsize'] && $index[$cat_id]['allow_thumbs'])
			{
				$allow_thumbs_upload = true;
			}
			else
			{
				$allow_thumbs_upload = false;
			}

			$thumb_form_name = 'thumb_name';

			/**
			 * Manipulate thumbnail upload
			 *
			 * @event 		dlext.upload_thumbnail_before
			 * @var bool  	thumb_form_name			thumbnail upload form field
			 * @var bool  	allow_thumbs_upload		enable/disable thumbnail upload
			 * @since 8.1.0-RC2
			 */
			
			$vars = array(
				'thumb_form_name',
				'allow_thumbs_upload',
			);
			extract($this->phpbb_dispatcher->trigger_event('dlext.upload_thumbnail_before', compact($vars)));

			if ($allow_thumbs_upload)
			{
				$min_pic_width = 10;
				$allowed_imagetypes = ['gif','png','jpg','bmp'];

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

				$upload_thumb_file = $this->request->file($thumb_form_name);
				unset($upload_thumb_file['local_mode']);
				$thumb_file = $upload_image->handle_upload('files.types.form', $thumb_form_name);

				$thumb_size = $upload_thumb_file['size'];
				$thumb_temp = $upload_thumb_file['tmp_name'];
				$thumb_name = $upload_thumb_file['name'];

				$error_count = count($thumb_file->error);

				if ($error_count > 1 && $thumb_name)
				{
					$thumb_file->remove();
					trigger_error(implode('<br />', $thumb_file->error), E_USER_ERROR);
				}

				$thumb_file->error = [];

				if ($thumb_name)
				{
					$pic_size = @getimagesize($thumb_temp);
					$pic_width = $pic_size[0];
					$pic_height = $pic_size[1];

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

			// validate custom profile fields
			$error = $cp_data = $cp_error = [];
			$cp->submit_cp_field($this->user->get_iso_lang_id(), $cp_data, $error);

			// Stop here, if custom fields are invalid!
			if (!empty($error))
			{
				trigger_error(implode('<br />', $error), E_USER_WARNING);
			}

			if($cat_id)
			{
				if (!$file_extern)
				{
					$file['name'] = $real_file;
					$upload_file->set_upload_ary($file);

					if (substr($dl_path, -1) == '/')
					{
						$dest_path = DL_EXT_FILEBASE_PATH. 'downloads/' . substr($dl_path, 0, -1);
					}
					else
					{
						$dest_path = DL_EXT_FILEBASE_PATH. 'downloads/' . $dl_path;
					}
					$dest_path = str_replace($this->root_path, '', $dest_path);
					$upload_file->move_file($dest_path, false, false, CHMOD_ALL);

					$error_count = count($upload_file->error);
					if ($error_count)
					{
						$upload_file->remove();
						trigger_error(implode('<br />', $upload_file->error), E_USER_ERROR);
					}

					$hash_method = $this->config['dl_file_hash_algo'];
					$func_hash = $hash_method . '_file';
					$file_hash = $func_hash(DL_EXT_FILEBASE_PATH. 'downloads/' . $dl_path . $real_file);
				}
				else
				{
					$file_hash = '';
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

				$approve = ($index[$cat_id]['must_approve'] && !$cat_auth['auth_mod'] && !$index[$cat_id]['auth_mod'] && !($this->auth->acl_get('a_') && $this->user->data['is_registered'])) ? 0 : $approve;

				unset($sql_array);

				if (!$cat_auth['auth_mod'] && !$index[$cat_id]['auth_mod'] && !$index[$cat_id]['allow_mod_desc'] && !($this->auth->acl_get('a_') && $this->user->data['is_registered']))
				{
					$test = $require = $warning = $mod_desc = '';
				}

				$sql_array = [
					'file_name'				=> $file_name,
					'real_file'				=> $real_file,
					'file_hash'				=> $file_hash,
					'cat'					=> $cat_id,
					'description'			=> $description,
					'long_desc'				=> $long_desc,
					'free'					=> $file_free,
					'extern'				=> $file_extern,
					'desc_uid'				=> $desc_uid,
					'desc_bitfield'			=> $desc_bitfield,
					'desc_flags'			=> $desc_flags,
					'long_desc_uid'			=> $long_desc_uid,
					'long_desc_bitfield'	=> $long_desc_bitfield,
					'long_desc_flags'		=> $long_desc_flags,
					'hacklist'				=> $hacklist,
					'hack_author'			=> $hack_author,
					'hack_author_email'		=> $hack_author_email,
					'hack_author_website'	=> $hack_author_website,
					'hack_version'			=> $hack_version,
					'hack_dl_url'			=> $hack_dl_url,
					'todo'					=> $todo,
					'approve'				=> $approve,
					'file_size'				=> $file_size,
					'change_time'			=> $current_time,
					'add_time'				=> $current_time,
					'change_user'			=> $current_user,
					'add_user'				=> $current_user,
					'test'					=> $test,
					'req'					=> $require,
					'warning'				=> $warning,
					'mod_desc'				=> $mod_desc,
					'file_traffic'			=> $file_traffic,
					'todo_uid'				=> $todo_uid,
					'todo_bitfield'			=> $todo_bitfield,
					'todo_flags'			=> $todo_flags,
				];

				if ($cat_auth['auth_mod'] || $index[$cat_id]['auth_mod'] || $index[$cat_id]['allow_mod_desc'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered']))
				{
					$sql_array += [
						'mod_list'				=> $mod_list,
						'mod_desc_uid'			=> $mod_desc_uid,
						'mod_desc_bitfield'		=> $mod_desc_bitfield,
						'mod_desc_flags'		=> $mod_desc_flags,
						'warn_uid'				=> $warn_uid,
						'warn_bitfield'			=> $warn_bitfield,
						'warn_flags'			=> $warn_flags,
					];
				}

				/**
				 * Save additional data for the download
				 *
				 * @event 		dlext.upload_sql_insert_before
				 * @var array	sql_array		array of download's data for storage
				 * @since 8.1.0-RC2
				 */
				$vars = array(
					'sql_array',
				);
				extract($this->phpbb_dispatcher->trigger_event('dlext.upload_sql_insert_before', compact($vars)));

				$sql = 'INSERT INTO ' . DOWNLOADS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_array);

				$this->db->sql_query($sql);

				$next_id = $this->db->sql_nextid();

				/**
				 * Save additional data for the download
				 *
				 * @event 		dlext.upload_sql_insert_after
				 * @var string	next_id			download ID
				 * @var array	sql_array		array of download's data for storage
				 * @since 8.1.0-RC2
				 */
				$vars = array(
					'next_id',
					'sql_array',
				);
				extract($this->phpbb_dispatcher->trigger_event('dlext.upload_sql_insert_after', compact($vars)));

				// Update Custom Fields
				$cp->update_profile_field_data($next_id, $cp_data);

				/**
				 * Manipulate thumbnail data before storage
				 *
				 * @event 		dlext.upload_sql_thumbnail_before
				 * @var string	foreign_thumb_message	message after manipulate thumbnail
				 * @var string	thumb_name				thumbnail name (empty to avoid overwrite foreign storage)
				 * @var string	next_id					download ID
				 * @var array	sql_array				array of download's data for storage
				 * @since 8.1.0-RC2
				 */
				$foreign_thumb_message = '';
				$vars = array(
					'foreign_thumb_message',
					'thumb_name',
					'next_id',
					'sql_array',
				);
				extract($this->phpbb_dispatcher->trigger_event('dlext.upload_sql_thumbnail_before', compact($vars)));

				if (isset($thumb_name) && $thumb_name != '')
				{
					$dest_folder = str_replace($this->root_path, '', substr(DL_EXT_FILEBASE_PATH . 'thumbs/', 0, -1));

					$upload_thumb_file['name'] = $next_id . '_' . $thumb_name;
					$thumb_file->set_upload_ary($upload_thumb_file);

					$thumb_file->move_file($dest_folder, false, false, CHMOD_ALL);

					$thumb_message = '<br />' . $this->language->lang('DL_THUMB_UPLOAD');

					$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'thumbnail' => $next_id . '_' . $thumb_name]) . ' WHERE id = ' . (int) $next_id;
					$this->db->sql_query($sql);
				}
				else
				{
					$thumb_message = '';
				}

				if ($foreign_thumb_message)
				{
					$thumb_message = '<br />' . $foreign_thumb_message;
				}

				/**
				 * Manipulate thumbnail data after storage
				 *
				 * @event 		dlext.upload_sql_thumbnail_after
				 * @var string	thumb_name		thumbnail name
				 * @var string	next_id			download ID
				 * @var array	sql_array		array of download's data for storage
				 * @since 8.1.0-RC2
				 */
				$vars = array(
					'thumb_name',
					'next_id',
					'sql_array',
				);
				extract($this->phpbb_dispatcher->trigger_event('dlext.upload_sql_thumbnail_after', compact($vars)));

				if ($index[$cat_id]['statistics'])
				{
					if ($index[$cat_id]['stats_prune'])
					{
						$stat_prune = $this->dlext_main->dl_prune_stats($cat_id, $index[$cat_id]['stats_prune']);
					}

					$sql = 'INSERT INTO ' . DL_STATS_TABLE . ' ' . $this->db->sql_build_array('INSERT', [
						'cat_id'		=> $cat_id,
						'id'			=> $next_id,
						'user_id'		=> $this->user->data['user_id'],
						'username'		=> $this->user->data['username'],
						'traffic'		=> $file_size,
						'direction'		=> 1,
						'user_ip'		=> $this->user->data['session_ip'],
						'time_stamp'	=> time()]);
					$this->db->sql_query($sql);
				}

				$notification = $this->phpbb_container->get('notification_manager');

				if (!$this->config['dl_disable_email'] && !$send_notify && $approve)
				{
					$notification_data = [
						'user_ids'			=> $this->dlext_auth->dl_auth_users($cat_id, 'auth_view'),
						'description'		=> $description,
						'long_desc'			=> $long_desc,
						'df_id'				=> $next_id,
						'cat_name'			=> $index[$cat_id]['cat_name_nav'],
					];

					$notification->add_notifications('oxpus.dlext.notification.type.dlext', $notification_data);

					$this->dlext_topic->gen_dl_topic($next_id);
				}

				if (!$approve)
				{
					$notification_data = [
						'user_ids'			=> $this->dlext_auth->dl_auth_users($cat_id, 'auth_mod'),
						'description'		=> $description,
						'long_desc'			=> $long_desc,
						'df_id'				=> $next_id,
						'cat_name'			=> $index[$cat_id]['cat_name_nav'],
					];

					$notification->add_notifications('oxpus.dlext.notification.type.approve', $notification_data);
				}

				if ($this->config['dl_upload_traffic_count'] && !$file_extern && !$this->config['dl_traffic_off'])
				{
					if ($this->user->data['is_registered'] && DL_OVERALL_TRAFFICS == true)
					{
						$this->config['dl_remain_traffic'] += $file_size;

						$this->config->set('dl_remain_traffic', $this->config['dl_remain_traffic']);
					}
					else if (!$this->user->data['is_registered'] && DL_GUESTS_TRAFFICS == true)
					{
						$this->config['dl_remain_guest_traffic'] += $file_size;

						$this->config->set('dl_remain_guest_traffic', $this->config['dl_remain_guest_traffic']);
					}
				}

				$approve_message = ($approve) ? '' : '<br />' . $this->language->lang('DL_MUST_BE_APPROVED');

				$message = $this->language->lang('DOWNLOAD_ADDED') . $thumb_message . $approve_message . '<br /><br />' . $this->language->lang('CLICK_RETURN_DOWNLOADS', '<a href="' . $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]) . '">', '</a>');
				if ($cat_auth['auth_up'])
				{
					$message .= '<br /><br />' . $this->language->lang('DL_UPLOAD_ONE_MORE', '<a href="' . $this->helper->route('oxpus_dlext_upload', ['cat_id' => $cat_id]) . '">', '</a>');
				}

				// Purge the files cache
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_cat_counts.' . $this->php_ext);
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_p.' . $this->php_ext);
				@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_preset.' . $this->php_ext);

				meta_refresh(3, $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]));

				trigger_error($message);
			}
		}

		$this->template->set_filenames(['body' => 'dl_edit_body.html']);

		$bg_row = 0;

		if ($cat_auth['auth_mod'] || $index[$cat_id]['auth_mod'] || ($this->auth->acl_get('a_') && $this->user->data['is_registered']))
		{
			$this->template->assign_var('S_MODCP', true);
			$bg_row = 1;
		}

		if (!$this->config['dl_disable_email'])
		{
			$this->template->assign_var('S_EMAIL_BLOCK', true);
			$bg_row = 1;
		}

		if (!$this->config['dl_disable_popup'])
		{
			$this->template->assign_var('S_POPUP_NOTIFY', true);
			$bg_row = 1;
		}

		if ($index[$cat_id]['allow_thumbs'] && $this->config['dl_thumb_fsize'])
		{
			$this->template->assign_var('S_ALLOW_THUMBS', true);
		}

		if ($this->config['dl_use_hacklist'] && $this->auth->acl_get('a_') && $this->user->data['is_registered'])
		{
			$this->template->assign_var('S_USE_HACKLIST', true);
			$hacklist_on = ($bg_row) ? true : 0;
			$bg_row = 1 - $bg_row;
		}

		if ($index[$cat_id]['allow_mod_desc'])
		{
			$this->template->assign_var('S_ALLOW_EDIT_MOD_DESC', true);
			$mod_block_bg = ($bg_row) ? true : 0;
		}

		if ($this->config['dl_upload_traffic_count'] && !$this->config['dl_traffic_off'])
		{
			$this->template->assign_var('S_UPLOAD_TRAFFIC', true);
		}

		$s_cat_select = '<select name="cat_id">';
		$s_cat_select .= $this->dlext_extra->dl_dropdown(0, 0, $cat_id, 'auth_up');
		$s_cat_select .= '</select>';

		$thumbnail_explain = $this->language->lang('DL_THUMB_DIM_SIZE', $this->config['dl_thumb_xsize'], $this->config['dl_thumb_ysize'], $this->dlext_format->dl_size($this->config['dl_thumb_fsize']));

		$s_hidden_fields = [];

		if (!$cat_auth['auth_mod'] && !$index[$cat_id]['auth_mod'] && !($this->auth->acl_get('a_') && $this->user->data['is_registered']))
		{
			$approve = ($index[$cat_id]['must_approve']) ? 0 : true;
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

		$this->template->assign_var('S_CAT_CHOOSE', true);

		add_form_key('dl_upload');

		$dl_files_page_title = $this->language->lang('DL_UPLOAD');

		$file_size_ary		= $this->dlext_format->dl_size(0, 2, 'select');
		$file_size			= $file_size_ary['size_out'];
		$file_size_range	= $file_size_ary['range'];

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

		$template_ary = [
			'DL_FILES_TITLE'			=> $dl_files_page_title,
			'DL_THUMBNAIL_SECOND'		=> $thumbnail_explain,
			'EXT_BLACKLIST'				=> $blacklist_explain,

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

			'DESCRIPTION'			=> '',
			'SELECT_CAT'			=> $s_cat_select,
			'LONG_DESC'				=> '',
			'URL'					=> '',
			'CHECKEXTERN'			=> '',
			'TRAFFIC'				=> 0,
			'APPROVE'				=> 'checked="checked"',
			'MOD_DESC'				=> '',
			'MOD_LIST'				=> '',
			'MOD_REQUIRE'			=> '',
			'MOD_TEST'				=> '',
			'MOD_TODO'				=> '',
			'MOD_WARNING'			=> '',
			'FILE_EXT_SIZE'			=> $file_size,

			'HACKLIST_BG'			=> (isset($hacklist_on) && $hacklist_on) ? ' bg2' : '',
			'MOD_BLOCK_BG'			=> (isset($mod_block_bg) && $mod_block_bg) ? ' bg2' : '',

			'MAX_UPLOAD_SIZE'		=> $this->language->lang('DL_UPLOAD_MAX_FILESIZE', $this->dlext_physical->dl_max_upload_size()),
			'FORMATED_HINT_TEXT'	=> $formated_hint_text,

			'ENCTYPE'				=> 'enctype="multipart/form-data"',

			'S_TODO_LINK_ONOFF'		=> ($this->config['dl_todo_onoff']) ? true : false,
			'S_CHECK_FREE'			=> $s_check_free,
			'S_TRAFFIC_RANGE'		=> $s_traffic_range,
			'S_FILE_EXT_SIZE_RANGE'	=> $s_file_ext_size_range,
			'S_HACKLIST'			=> $s_hacklist,
			'S_DOWNLOADS_ACTION'	=> $this->helper->route('oxpus_dlext_upload'),
			'S_DL_TRAFFIC'			=> $this->config['dl_traffic_off'],
			'S_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields),
			'S_ADD_DL'				=> true,
		];

		/**
		 * Display extra data to save them with the download
		 *
		 * @event 		dlext.upload_template_before
		 * @var string	cat_id			download category ID
		 * @var array	template_ary	array of download's data for edit
		 * @since 8.1.0-RC2
		 */
		$vars = array(
			'cat_id',
			'template_ary',
		);
		extract($this->phpbb_dispatcher->trigger_event('dlext.upload_template_before', compact($vars)));

		$this->template->assign_vars($template_ary);

		// Init and display the custom fields with the existing data
		$cp->get_profile_fields($df_id);
		$cp->generate_profile_fields($this->user->get_iso_lang_id());

		/*
		* include the mod footer
		*/
		$dl_footer = $this->phpbb_container->get('oxpus.dlext.footer');
		$dl_footer->set_parameter($nav_view, $cat_id, 0, $index);
		$dl_footer->handle();
	}
}
