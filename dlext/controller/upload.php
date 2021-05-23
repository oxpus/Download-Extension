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
	protected $notifications;
	protected $files_factory;
	protected $filesystem;

	/* extension owned objects */
	protected $ext_path;

	protected $dlext_auth;
	protected $dlext_extra;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_physical;
	protected $dlext_topic;
	protected $dlext_constants;
	protected $dlext_footer;

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
	* @param \oxpus\dlext\core\extra				$dlext_extra
	* @param \oxpus\dlext\core\format				$dlext_format
	* @param \oxpus\dlext\core\main					$dlext_main
	* @param \oxpus\dlext\core\physical				$dlext_physical
	* @param \oxpus\dlext\core\topic				$dlext_topic
	* @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	* @param \oxpus\dlext\core\helpers\footer		$dlext_footer
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
		\oxpus\dlext\core\extra $dlext_extra,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\physical $dlext_physical,
		\oxpus\dlext\core\topic $dlext_topic,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
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
		$this->dlext_extra				= $dlext_extra;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_physical			= $dlext_physical;
		$this->dlext_topic				= $dlext_topic;
		$this->dlext_constants			= $dlext_constants;
		$this->dlext_footer				= $dlext_footer;

		$this->dlext_main->dl_handle_active();
	}

	public function handle()
	{
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

		// Initiate custom fields
		include($this->ext_path . 'includes/fields.' . $this->php_ext);

		$cp = new \oxpus\dlext\includes\custom_profile();

		if ($submit)
		{
			if (!check_form_key('dl_upload'))
			{
				trigger_error('FORM_INVALID');
			}

			$approve			= $this->request->variable('approve', 0);
			$description		= $this->request->variable('description', '', $this->dlext_constants::DL_TRUE);
			$file_traffic		= $this->request->variable('file_traffic', 0);
			$long_desc			= $this->request->variable('long_desc', '', $this->dlext_constants::DL_TRUE);
			$file_name_name		= $this->request->variable('file_name', '', $this->dlext_constants::DL_TRUE);

			$file_free			= $this->request->variable('file_free', 0);
			$file_extern		= $this->request->variable('file_extern', 0);
			$file_extern_size	= $this->request->variable('file_extern_size', 0);

			$test				= $this->request->variable('test', '', $this->dlext_constants::DL_TRUE);
			$require			= $this->request->variable('require', '', $this->dlext_constants::DL_TRUE);
			$todo				= $this->request->variable('todo', '', $this->dlext_constants::DL_TRUE);
			$warning			= $this->request->variable('warning', '', $this->dlext_constants::DL_TRUE);
			$mod_desc			= $this->request->variable('mod_desc', '', $this->dlext_constants::DL_TRUE);
			$mod_list			= $this->request->variable('mod_list', 0);
			$mod_list			= ($mod_list) ? 1 : 0;

			$send_notify			= $this->request->variable('send_notify', 0);

			$hacklist				= $this->request->variable('hacklist', 0);
			$hack_author			= $this->request->variable('hack_author', '', $this->dlext_constants::DL_TRUE);
			$hack_author_email		= $this->request->variable('hack_author_email', '', $this->dlext_constants::DL_TRUE);
			$hack_author_website	= $this->request->variable('hack_author_website', '', $this->dlext_constants::DL_TRUE);
			$hack_version			= $this->request->variable('hack_version', '', $this->dlext_constants::DL_TRUE);
			$hack_dl_url			= $this->request->variable('hack_dl_url', '', $this->dlext_constants::DL_TRUE);

			if (!$description)
			{
				trigger_error($this->language->lang('NO_SUBJECT'), E_USER_WARNING);
			}

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
				$form_name = 'dl_name';
				$file = $this->request->file($form_name);
				$extension = str_replace('.', '', trim(strrchr(strtolower($file['name']), '.')));

				if ($this->config['dl_enable_blacklist'])
				{
					if (in_array($extension, $ext_blacklist))
					{
						trigger_error($this->language->lang('DL_FORBIDDEN_EXTENSION'), E_USER_ERROR);
					}
				}

				$allowed_extensions = [$extension];

				$upload = $this->files_factory->get('upload')
					->set_allowed_extensions($allowed_extensions)
					->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : $this->dlext_constants::DL_FALSE));

				unset($file['local_mode']);
				$upload_file = $upload->handle_upload('files.types.form', $form_name);

				$file_size = $file['size'];
				$file_name = $file['name'];

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

					if ($this->user->data['is_registered'] && $this->dlext_constants->get_value('overall_traffics') == $this->dlext_constants::DL_TRUE)
					{
						$remain_traffic = $this->config['dl_overall_traffic'] - $this->config['dl_remain_traffic'];
					}
					else if (!$this->user->data['is_registered'] && $this->dlext_constants->get_value('guests_traffics') == $this->dlext_constants::DL_TRUE)
					{
						$remain_traffic = $this->config['dl_overall_guest_traffic'] - $this->config['dl_remain_guest_traffic'];
					}

					if ($file_size == 0 || ($remain_traffic && $file_size > $remain_traffic && $this->config['dl_upload_traffic_count']))
					{
						$upload_file->remove();
						trigger_error($this->language->lang('DL_NO_UPLOAD_TRAFFIC'), E_USER_ERROR);
					}
				}

				$dl_path = $index[$cat_id]['cat_path'];

				$real_file = $this->dlext_format->encrypt($file_name) . '.' . $extension;

				while ($this->filesystem->exists($this->dlext_constants->get_value('files_dir') . '/downloads/' . $dl_path . $real_file))
				{
					$real_file = $this->dlext_format->encrypt($file_name) . '.' . $extension;
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
			 * @event oxpus.dlext.upload_thumbnail_before
			 * @var string 	thumb_form_name			thumbnail upload form field
			 * @var bool  	allow_thumbs_upload		enable/disable thumbnail upload
			 * @since 8.1.0-RC2
			 */

			$vars = array(
				'thumb_form_name',
				'allow_thumbs_upload',
			);
			extract($this->dispatcher->trigger_event('oxpus.dlext.upload_thumbnail_before', compact($vars)));

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

				$thumb_temp = $upload_thumb_file['tmp_name'];
				$thumb_name = $upload_thumb_file['name'];

				$error_count = count($thumb_file->error);

				if ($error_count > 1 && $thumb_name)
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
			$cp_error = [];
			$cp->submit_cp_field($this->user->get_iso_lang_id(), $cp_data, $error);

			// Stop here, if custom fields are invalid!
			if (!empty($error))
			{
				trigger_error(implode('<br />', $error), E_USER_WARNING);
			}

			if ($cat_id)
			{
				if (!$file_extern)
				{
					$file['name'] = $real_file;
					$upload_file->set_upload_ary($file);

					if (substr($dl_path, -1) == '/')
					{
						$dest_path = $this->dlext_constants->get_value('files_dir') . '/downloads/' . substr($dl_path, 0, -1);
					}
					else
					{
						$dest_path = $this->dlext_constants->get_value('files_dir') . '/downloads/' . $dl_path;
					}
					$dest_path = str_replace($this->root_path, '', $dest_path);
					$upload_file->move_file($dest_path, $this->dlext_constants::DL_FALSE, $this->dlext_constants::DL_FALSE);

					$error_count = count($upload_file->error);
					if ($error_count)
					{
						$upload_file->remove();
						trigger_error(implode('<br />', $upload_file->error), E_USER_ERROR);
					}

					$file_hash = $this->dlext_format->encrypt($this->dlext_constants->get_value('files_dir') . '/downloads/' . $dl_path . $real_file, 'file', $this->config['dl_file_hash_algo']);
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

				$approve = ($index[$cat_id]['must_approve'] && !$cat_auth['auth_mod'] && !$index[$cat_id]['auth_mod'] && !$this->dlext_auth->user_admin()) ? 0 : $approve;

				unset($sql_array);

				if (!$cat_auth['auth_mod'] && !$index[$cat_id]['auth_mod'] && !$index[$cat_id]['allow_mod_desc'] && !$this->dlext_auth->user_admin())
				{
					$test = '';
					$require = '';
					$warning = '';
					$mod_desc = '';
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

				if ($cat_auth['auth_mod'] || $index[$cat_id]['auth_mod'] || $index[$cat_id]['allow_mod_desc'] || $this->dlext_auth->user_admin())
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
				 * @event oxpus.dlext.upload_sql_insert_before
				 * @var string	sql_array		array of download's data for storage
				 * @since 8.1.0-RC2
				 */
				$vars = array(
					'sql_array',
				);
				extract($this->dispatcher->trigger_event('oxpus.dlext.upload_sql_insert_before', compact($vars)));

				$sql = 'INSERT INTO ' . $this->dlext_table_downloads . ' ' . $this->db->sql_build_array('INSERT', $sql_array);

				$this->db->sql_query($sql);

				$next_id = $this->db->sql_nextid();

				/**
				 * Save additional data for the download
				 *
				 * @event oxpus.dlext.upload_sql_insert_after
				 * @var int		next_id			download ID
				 * @var array	sql_array		array of download's data for storage
				 * @since 8.1.0-RC2
				 */
				$vars = array(
					'next_id',
					'sql_array',
				);
				extract($this->dispatcher->trigger_event('oxpus.dlext.upload_sql_insert_after', compact($vars)));

				// Update Custom Fields
				$cp->update_profile_field_data($next_id, $cp_data);

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
				$foreign_thumb_message = '';
				$vars = array(
					'foreign_thumb_message',
					'thumb_name',
					'next_id',
					'sql_array',
				);
				extract($this->dispatcher->trigger_event('oxpus.dlext.upload_sql_thumbnail_before', compact($vars)));

				if (isset($thumb_name) && $thumb_name != '')
				{
					$dest_folder = str_replace($this->root_path, '', substr($this->dlext_constants->get_value('files_dir') . '/thumbs/', 0, -1));

					$thumb_pic_extension = trim(strrchr(strtolower($thumb_name), '.'));
					$thumb_upload_filename = $next_id . '_' . unique_id() . $thumb_pic_extension;

					$upload_thumb_file['name'] = $thumb_upload_filename;
					$thumb_file->set_upload_ary($upload_thumb_file);

					$thumb_file->move_file($dest_folder, $this->dlext_constants::DL_FALSE, $this->dlext_constants::DL_FALSE);

					$thumb_message = '<br />' . $this->language->lang('DL_THUMB_UPLOAD');

					$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'thumbnail' => $thumb_upload_filename]) . ' WHERE id = ' . (int) $next_id;
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
				 * @event oxpus.dlext.upload_sql_thumbnail_after
				 * @var string	thumb_name		thumbnail name
				 * @var int		next_id			download ID
				 * @var array	sql_array		array of download's data for storage
				 * @since 8.1.0-RC2
				 */
				$vars = array(
					'thumb_name',
					'next_id',
					'sql_array',
				);
				extract($this->dispatcher->trigger_event('oxpus.dlext.upload_sql_thumbnail_after', compact($vars)));

				if ($index[$cat_id]['statistics'])
				{
					if ($index[$cat_id]['stats_prune'])
					{
						$this->dlext_main->dl_prune_stats($cat_id, $index[$cat_id]['stats_prune']);
					}

					$sql = 'INSERT INTO ' . $this->dlext_table_dl_stats . ' ' . $this->db->sql_build_array('INSERT', [
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

				if (!$this->config['dl_disable_email'] && !$send_notify && $approve)
				{
					$notification_data = [
						'user_ids'			=> $this->dlext_auth->dl_auth_users($cat_id, 'auth_view'),
						'description'		=> $description,
						'long_desc'			=> $long_desc,
						'df_id'				=> $next_id,
						'cat_name'			=> $index[$cat_id]['cat_name_nav'],
					];

					$this->notification->add_notifications('oxpus.dlext.notification.type.dlext', $notification_data);
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

					$this->notification->add_notifications('oxpus.dlext.notification.type.approve', $notification_data);
				}
				else
				{
					$this->dlext_topic->gen_dl_topic('post', $next_id);
				}

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

				$approve_message = ($approve) ? '' : '<br />' . $this->language->lang('DL_MUST_BE_APPROVED');

				$message = $this->language->lang('DL_DOWNLOAD_ADDED') . $thumb_message . $approve_message . '<br /><br />' . $this->language->lang('CLICK_RETURN_DOWNLOADS', '<a href="' . $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]) . '">', '</a>');
				if ($cat_auth['auth_up'])
				{
					$message .= '<br /><br />' . $this->language->lang('DL_UPLOAD_ONE_MORE', '<a href="' . $this->helper->route('oxpus_dlext_upload', ['cat_id' => $cat_id]) . '">', '</a>');
				}

				// Purge the files cache
				$this->cache->destroy('_dlext_cat_counts');
				$this->cache->destroy('_dlext_file_p');
				$this->cache->destroy('_dlext_file_preset');

				meta_refresh(3, $this->helper->route('oxpus_dlext_index', ['cat' => $cat_id]));

				trigger_error($message);
			}
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
			'DL_APPROVE'				=> 'checked="checked"',
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
			foreach ($select_categories as $key => $value)
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
		$s_hacklist[] = ['value' => $this->dlext_constants::DL_HACKLIST_EXTRA,	'lang'	=> $this->language->lang('DL_MOD_LIST')];

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
		$cp->get_profile_fields($df_id);
		$cp->generate_profile_fields($this->user->get_iso_lang_id());

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
