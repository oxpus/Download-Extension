<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\acp;

use Symfony\Component\DependencyInjection\Container;

/**
* @package acp
*/
class acp_toolbox_controller implements acp_toolbox_interface
{
	public $u_action;
	public $db;
	public $user;
	public $auth;
	public $phpEx;
	public $root_path;
	public $phpbb_extension_manager;
	public $phpbb_container;
	public $phpbb_path_helper;
	public $phpbb_log;
	public $phpbb_dispatcher;

	public $config;
	public $helper;
	public $language;
	public $request;
	public $template;

	public $ext_path;
	public $ext_path_web;
	public $ext_path_ajax;

	protected $dlext_extra;
	protected $dlext_format;
	protected $dlext_physical;

	/*
	 * @param string								$root_path
	 * @param string								$phpEx
	 * @param Container 							$phpbb_container
	 * @param \phpbb\extension\manager				$phpbb_extension_manager
	 * @param \phpbb\path_helper					$phpbb_path_helper
	 * @param \phpbb\db\driver\driver_interfacer	$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\auth\auth						$auth
	 * @param \phpbb\user							$user
	 * @param \phpbb\event\dispatcher_interface		$phpbb_dispatcher
	 */
	public function __construct(
		$root_path,
		$phpEx,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\auth\auth $auth,
		\phpbb\user $user,
		\phpbb\event\dispatcher_interface $phpbb_dispatcher,
		$dlext_extra,
		$dlext_format,
		$dlext_physical
	)
	{
		$this->root_path				= $root_path;
		$this->phpEx					= $phpEx;
		$this->phpbb_container			= $phpbb_container;
		$this->phpbb_extension_manager	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db						= $db;
		$this->phpbb_log				= $log;
		$this->auth						= $auth;
		$this->user						= $user;
		$this->phpbb_dispatcher			= $phpbb_dispatcher;

		$this->config					= $this->phpbb_container->get('config');
		$this->helper					= $this->phpbb_container->get('controller.helper');
		$this->language					= $this->phpbb_container->get('language');
		$this->request					= $this->phpbb_container->get('request');
		$this->template					= $this->phpbb_container->get('template');

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_extra				= $dlext_extra;
		$this->dlext_format				= $dlext_format;
		$this->dlext_physical			= $dlext_physical;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$this->auth->acl($this->user->data);
		if (!$this->auth->acl_get('a_dl_toolbox'))
		{
			trigger_error('DL_NO_PERMISSION', E_USER_WARNING);
		}

		include_once($this->ext_path . 'phpbb/includes/acm_init.' . $this->phpEx);

		if ($cancel)
		{
			redirect($this->u_action);
		}

		add_form_key('dl_adm_tools');

		if ($submit && !check_form_key('dl_adm_tools'))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
		}

		if ($action == 'dl' && $file_name && $path)
		{
			if (!$description)
			{
				$description = $file_name;
			}

			$description	= base64_decode($description);
			$file_name		= base64_decode($file_name);
			$path			= base64_decode($path);

			include_once($this->root_path . 'includes/functions_download.' . $this->phpEx);

			$this->language->add_lang('viewtopic');

			$file_path = DL_EXT_FILEBASE_PATH. 'downloads/' . $path . '/';

			$dl_data = [
				'physical_file'		=> $file_path . $file_name,
				'real_filename'		=> $description,
				'mimetype'			=> 'application/octetstream',
				'filesize'			=> sprintf("%u", @filesize($file_path . $file_name)),
				'filetime'			=> @filemtime($file_path . $file_name),
			];

			if (@file_exists($file_path . $file_name))
			{
				$this->dlext_physical->send_file_to_browser($dl_data);
			}
			else
			{
				trigger_error($this->language->lang('FILE_NOT_FOUND_404', $description) . adm_back_link($this->u_action . '&amp;path=/' . $path), E_USER_WARNING);
			}
		}

		if (!empty($files) && $file_assign)
		{
			$file_names = $file_path = [];

			for ($i = 0; $i < count($files); ++$i)
			{
				$temp = strpos($files[$i], '|');
				$files_path[] = substr($files[$i],0,$temp);
				$files_name[] = substr($files[$i],$temp+1);
			}

			if ($file_assign == 'del')
			{
				for ($i = 0; $i < count($files); ++$i)
				{
					$dl_dir = ($files_path[$i]) ? substr(DL_EXT_FILEBASE_PATH. 'downloads/', 0, strlen(DL_EXT_FILEBASE_PATH. 'downloads/')-1) : DL_EXT_FILEBASE_PATH. 'downloads/';

					@unlink($dl_dir . $files_path[$i] . '/' . $files_name[$i]);

					$sql = 'SELECT id, cat FROM ' . DOWNLOADS_TABLE . "
						WHERE real_file = '" . $this->db->sql_escape($files_name[$i]) . "'
							AND " . $this->db->sql_in_set('cat', $index, true);
					$result = $this->db->sql_query($sql);

					$dl_ids = [];
					$dl_cats = [];

					while ($row = $this->db->sql_fetchrow($result))
					{
						$dl_ids[] = $row['id'];
						$dl_cats[] = $row['cat'];
					}

					$this->db->sql_freeresult($result);

					$sql = 'DELETE FROM ' . DOWNLOADS_TABLE . '
						WHERE ' . $this->db->sql_in_set('id', $dl_ids);
					$this->db->sql_query($sql);

					/**
					 * Workflow after delete download
					 *
					 * @event oxpus.dlext.acp_toolbox_delete_downloads_after
					 * @var array	dl_ids		download ID's
					 * @var array	dl_cats		download category ID's
					 * @since 8.1.0-RC2
					 */
					$vars = array(
						'dl_ids',
						'dl_cats',
					);
					extract($this->phpbb_dispatcher->trigger_event('oxpus.dlext.acp_toolbox_delete_downloads_after', compact($vars)));

					$notification = $this->phpbb_container->get('notification_manager');

					$notification->delete_notifications([
						'oxpus.dlext.notification.type.approve',
						'oxpus.dlext.notification.type.broken',
						'oxpus.dlext.notification.type.dlext',
						'oxpus.dlext.notification.type.update',
						'oxpus.dlext.notification.type.capprove',
						'oxpus.dlext.notification.type.comments',
					], $dl_ids);
	
					@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_p.' . $this->phpEx);

					$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILE_DROP', false, [$files_name[$i]]);
				}
			}
			else
			{
				$dl_dir = substr(DL_EXT_FILEBASE_PATH. 'downloads/', 0, strlen(DL_EXT_FILEBASE_PATH. 'downloads/')-1);

				for ($i = 0; $i < count($files); ++$i)
				{
					$sql = 'SELECT path FROM ' . DL_CAT_TABLE . '
						WHERE id = ' . (int) $file_assign;
					$result = $this->db->sql_query($sql);
					$cat_path = $this->db->sql_fetchfield('path');
					$this->db->sql_freeresult($result);

					if ($cat_path != substr($files_path[$i], 1).'/')
					{
						@copy ($dl_dir . $files_path[$i] . '/' . $files_name[$i], DL_EXT_FILEBASE_PATH. 'downloads/' . $cat_path . $files_name[$i]);
						@unlink($dl_dir . $files_path[$i] . '/' . $files_name[$i]);
					}

					$sql = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'cat' => $file_assign]) . ' WHERE ' . $this->db->sql_in_set('cat', $index, true) . " AND real_file = '" . $this->db->sql_escape($files_name[$i]) . "'";
					$this->db->sql_query($sql);

					@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_p.' . $this->phpEx);

					$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILE_ADD', false, [$files_name[$i]]);
				}
			}

			$file_action = $file_command = $new_path = '';
		}

		if (!empty($index))
		{
			$unas_files = $files_temp = [];

			$sql = 'SELECT description, real_file FROM ' . DOWNLOADS_TABLE . '
				WHERE ' . $this->db->sql_in_set('cat', $index, true);
			$result = $this->db->sql_query($sql);

			$i = 0;

			if ($action == 'unassigned')
			{
				while ($row = $this->db->sql_fetchrow($result))
				{
					$real_file = $row['real_file'];
					$file_desc = $row['description'];
					$unas_files[$i] = $real_file;
					$unas_files[$real_file] = $file_desc;
					++$i;
				}
			}

			$this->db->sql_freeresult($result);

			if ($action == 'unassigned' && !empty($unas_files))
			{
				$read_files = $this->dlext_physical->read_dl_files('', $unas_files);
				$read_files = substr($read_files, 0, strlen($read_files) - 1 );

				$files = explode('|', $read_files);

				for ($i = 0; $i < count($files); ++$i)
				{
					$temp = strripos($files[$i], '/');
					$files_data[] = substr($files[$i],0,$temp).'|'.substr($files[$i],$temp+1);
				}
			}
		}

		if ($action == 'check_file_sizes')
		{
			$sql = 'SELECT dl.*, c.path FROM ' . DOWNLOADS_TABLE . ' dl, ' . DL_CAT_TABLE . ' c
				WHERE dl.cat = c.id
					AND dl.extern <> 1
				ORDER BY dl.id';
			$result = $this->db->sql_query($sql);

			$message = '';

			while ( $row = $this->db->sql_fetchrow($result) )
			{
				$file_size	= $row['file_size'];
				$file_desc	= $row['description'];
				$real_file	= $row['real_file'];
				$file_path	= $row['path'];
				$file_id	= $row['id'];

				$check_file_size = sprintf("%u", @filesize(DL_EXT_FILEBASE_PATH. 'downloads/' . $file_path . $real_file));
				if ( $check_file_size == 0 || $check_file_size == '' )
				{
					$message .= $file_desc . '<br />';
				}
				else if ($check_file_size <> $file_size)
				{
					$sql_new = 'UPDATE ' . DOWNLOADS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'file_size' => $check_file_size]) . ' WHERE id = ' . (int) $file_id;
					$result_new = $this->db->sql_query($sql_new);

					@unlink(DL_EXT_CACHE_PATH . 'data_dl_file_p.' . $this->phpEx);

					if (!$result_new)
					{
						$message .= $file_desc . '<br />';
					}
				}
			}

			$action = '';

			if ( $message != '' )
			{
				$check_message = $this->language->lang('DL_CHECK_FILESIZES_RESULT_ERROR') . '<br /><br />' . $message;
			}
			else
			{
				$check_message = $this->language->lang('DL_CHECK_FILESIZES_RESULT');
			}

			$check_message .= adm_back_link($this->u_action);

			$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILES_CHECK');

			if ($message)
			{
				trigger_error($check_message, E_USER_WARNING);
			}
			else
			{
				trigger_error($check_message);
			}
		}

		if ($action == 'check_thumbnails')
		{
			if ($del_real_thumbs)
			{
				for ($i = 0; $i < count($thumbs); ++$i)
				{
					@unlink(DL_EXT_FILEBASE_PATH . 'thumbs/' . base64_decode($thumbs[$i]));
				}

				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_THUMBS_DEL');
			}

			$real_thumbnails['file_name'] = [];
			$real_thumbnails['file_size'] = [];

			@$dir = opendir(DL_EXT_FILEBASE_PATH . 'thumbs/');

			while (false !== ($file=@readdir($dir)))
			{
				if ($file[0] != "." && !is_dir($file) && $file != 'index.html' && $file != 'index.htm')
				{
					$real_thumbnails['file_name'][] = $file;
					$real_thumbnails['file_size'][] = sprintf("%u", @filesize(DL_EXT_FILEBASE_PATH . 'thumbs/' . $file));
				}
			}

			@closedir($dir);

			$dl_thumbs = [];

			$sql = 'SELECT thumbnail FROM ' . DOWNLOADS_TABLE . "
				WHERE thumbnail <> ''";
			$result = $this->db->sql_query($sql);

			while ($dl_thumbs[] = $this->db->sql_fetchfield('thumbnail') );
			$this->db->sql_freeresult($result);

			$sql = 'SELECT img_name FROM ' . DL_IMAGES_TABLE;
			$result = $this->db->sql_query($sql);

			while ($dl_thumbs[] = $this->db->sql_fetchfield('img_name') );
			$this->db->sql_freeresult($result);

			if (!empty($real_thumbnails['file_name']))
			{
				$this->tpl_name = 'acp_dl_thumbs';

				$this->template->assign_vars([
					'S_MANAGE_ACTION'	=> "{$this->u_action}&amp;action=check_thumbnails",

					'U_BACK'			=> ($action == 'check_thumbnails') ? $this->u_action : '',
				]);

				$j = -1;

				for ($i = 0; $i < count($real_thumbnails['file_name']); ++$i)
				{
					$real_file = $real_thumbnails['file_name'][$i];

					if (!in_array ($real_file, $dl_thumbs))
					{
						++$j;
						$checkbox = '<input type="checkbox" class="permissions-checkbox" name="thumb[' . $j . ']" value="' . base64_encode($real_file) . '" />';
					}
					else
					{
						$checkbox = '';
					}

					$this->template->assign_block_vars('thumbnails', [
						'CHECKBOX'		=> $checkbox,
						'REAL_FILE'		=> $real_file,
						'FILE_SIZE'		=> $this->dlext_format->dl_size($real_thumbnails['file_size'][$i]),

						'U_REAL_FILE'	=> DL_EXT_FILEBASE_PATH . 'thumbs/' . $real_file,
					]);
				}
			}
			else
			{
				$action = 'browse';
			}
		}

		if ($files && $file_command)
		{
			$path_temp = $path;
			$path .= ($path) ? '/' : '';

			if ($file_command == 'del')
			{
				for ($i = 0; $i < count($files); ++$i)
				{
					@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . $files[$i]);

					$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILE_DROP', false, [$files[$i]]);
				}
			}
			else
			{
				for ($i = 0; $i < count($files); ++$i)
				{
					@copy(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . $files[$i], $file_command . $files[$i]);
					@unlink(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . $files[$i]);
		
					$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILE_MOVE', false, [$files[$i]]);
				}
			}

			$path = $path_temp;
			$file_action = $file_command = $new_path = '';
		}
		
		if ($dir_name && $dircreate)
		{
			$upas = ['�' => 'ae', '�' => 'ue', '�' => 'oe', '�' => 'Ae', '�' => 'Ue', '�' => 'Oe', '�' => 'ss'];
			$upass = [' ' => '', '+' => '', '%' => ''];
			$dir_name = strtr(urlencode(strtr(utf8_decode($dir_name), $upas)), $upass);

			$this->dlext_physical->_create_folder(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . '/' . $dir_name . '/');

			$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FOLDER_CREATE', false, [$path . '/' . $dir_name]);
		}

		if ($action == 'dirdelete')
		{
			$file_name = basename($path);

			$content_count = 0;

			$sh = @opendir(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . '/' . $file);

			while (false !== ($subfile=@readdir($sh)))
			{
				if ($subfile[0] != '.' && $subfile != 'index.htm')
				{
					++$content_count;
				}
			}

			@closedir($sh);

			if ($content_count == 0)
			{
				$this->dlext_physical->_drop_dl_basis(DL_EXT_FILEBASE_PATH. 'downloads/' . $path);

				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FOLDER_DROP', false, [$path]);
			}

			$action = '';

			$path = ($path != $file_name) ? substr($path, 0, strlen($path) - strlen($file_name)-1) : '';
		}

		if ($action == 'browse' || $action == '' || $action == 'unassigned')
		{
			if ($action != 'unassigned')
			{
				$temp_url = '';
				$temp_dir = [];

				$dl_navi = '<a href="' . $this->u_action . '&amp;action=browse">' . DL_EXT_FILEBASE_PATH. 'downloads/' . '</a>';

				$dirs = $dirs_delete = $files = $filen = $sizes = $exist = [];

				$existing_files = [];
				$existing_files = $this->dlext_physical->read_exist_files();

				if ($path)
				{
					$path = ($path[0] == '/') ? substr($path, 1) : $path;

					$temp_dir = explode('/', $path);

					if (!empty($temp_dir))
					{
						for ($i = 0; $i < count($temp_dir); ++$i)
						{
							$temp_url .= '/'.$temp_dir[$i];
							$temp_path = preg_replace('#[/]*#', '', $temp_dir[$i]);
							$dl_navi .= '<a href="' . $this->u_action . '&amp;action=browse&amp;path=' . $temp_url . '">' . $temp_path . '/</a>';
						}
					}

					$real_file_array = [];
					$real_file_title = [];

					$sql = 'SELECT d.description, d.file_name, d.real_file FROM ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . " c
						WHERE d.cat = c.id
							AND c.path = '" . $this->db->sql_escape(utf8_decode($path)) . "/'";
					$result = $this->db->sql_query($sql);
					$total_files = $this->db->sql_affectedrows($result);

					if ($total_files)
					{
						while ($row = $this->db->sql_fetchrow($result))
						{
							$real_file_array[$row['real_file']] = '<strong>' . $row['description'] . '</strong><br />[' . $row['file_name'] . ']';
							$real_file_title[$row['real_file']] = $row['file_name'];
						}
					}

					$this->db->sql_freeresult($result);

					$sql = 'SELECT d.description, v.ver_file_name, v.ver_real_file FROM ' . DL_VERSIONS_TABLE . ' v, ' . DOWNLOADS_TABLE . ' d, ' . DL_CAT_TABLE . " c
						WHERE d.cat = c.id
							AND v.dl_id = d.id
							AND c.path = '" . $this->db->sql_escape(utf8_decode($path)) . "/'";
					$result = $this->db->sql_query($sql);
					$total_files = $this->db->sql_affectedrows($result);

					if ($total_files)
					{
						while ($row = $this->db->sql_fetchrow($result))
						{
							$real_file_array[$row['ver_real_file']] = '<strong>' . $row['description'] . '</strong><br />[' . $row['ver_file_name'] . ']';
							$real_file_title[$row['ver_real_file']] = $row['ver_file_name'];
						}
					}

					$this->db->sql_freeresult($result);
				}

				$dh = @opendir(DL_EXT_FILEBASE_PATH. 'downloads/' . $path);

				$total_unassigned_files = 0;

				while (false !== ($file = @readdir($dh)))
				{
					if ($file[0] != '.' &&  $file != 'index.htm')
					{
						if (is_dir(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . '/' . $file))
						{
							$slash = ($path) ? '/' : '';
							$dirs[] = $path . $slash . $file . '|~|<a href="' . $this->u_action . '&amp;action=browse&amp;path=' . $path . $slash . $file . '">' . $file . '</a>';

							$sh = @opendir(DL_EXT_FILEBASE_PATH. 'downloads/' . $path . '/' . $file);

							$content_count = 0;

							while (false !== ($subfile = @readdir($sh)))
							{
								if ($subfile[0] != '.' && $subfile != 'index.htm')
								{
									++$content_count;
								}
							}

							@closedir($sh);

							$dirs_delete[] = ($content_count == 0) ? '<a href="' . $this->u_action . '&amp;action=dirdelete&amp;path=' . $path . $slash . $file . '">' . $this->language->lang('DL_DELETE') . '</a>' : $this->language->lang('DL_MANAGE_CONTENT_COUNT', $content_count);
						}
						else
						{
							$file_desc		= (isset($real_file_title[$file])) ? $real_file_title[$file] : $file;
							$file_desc		= base64_encode($file_desc);
							$file_name		= base64_encode($file);
							$file_path		= base64_encode($path);
							$real_file_name = (isset($real_file_array[$file])) ? $real_file_array[$file] : $file;
							$files_url		= "{$this->u_action}&amp;action=dl&amp;description=$file_desc&amp;file_name=$file_name&amp;path=$file_path";
							$files[] = $real_file_name . '|~|<a href="' . $files_url . '">' . $real_file_name . '</a>';
							$filen[] = $file;
							$sizes[] = sprintf("%u", @filesize(DL_EXT_FILEBASE_PATH. 'downloads/' . $path .'/' . $file));
							if (in_array($file, $existing_files))
							{
								$exist[] = true;
							}
							else
							{
								$exist[] = 0;
								++$total_unassigned_files;
							}
						}
					}
				}

				@closedir($dh);

				$this->template->assign_var('S_CREATE_DIR_COMMAND', true);
			}
			else
			{
				$dl_navi = $this->language->lang('DL_UNASSIGNED_FILES');
			}

			$this->template->assign_vars([
				'DL_NAVI'					=> $dl_navi,

				'S_MANAGE_ACTION'			=> "{$this->u_action}&amp;path=$path",

				'U_UNASSIGNED_FILES'		=> "{$this->u_action}&amp;action=unassigned",
				'U_DOWNLOADS_CHECK_FILES'	=> "{$this->u_action}&amp;action=check_file_sizes",
				'U_DOWNLOADS_CHECK_THUMB'	=> "{$this->u_action}&amp;action=check_thumbnails",
			]);

			$existing_thumbs = 0;
			@$dir = opendir(DL_EXT_FILEBASE_PATH . 'thumbs/');

			while (false !== ($file = @readdir($dir)))
			{
				if ($file[0] != '.' && !is_dir($file) && $file != 'index.htm')
				{
					$existing_thumbs = true;
					break;
				}
			}

			@closedir($dir);

			if ($existing_thumbs)
			{
				$this->template->assign_var('S_THUMBNAIL_CHECK', true);
			}

			if (!$dirs && !$files)
			{
				$this->template->assign_var('S_EMPTY_FOLDER', true);
			}

			if ($total_unassigned_files && $action != 'unassigned')
			{
				$this->template->assign_var('S_UNASSIGNED_FILES', true);
			}

			if ($dirs)
			{
				natcasesort($dirs);
				foreach ($dirs as $i => $value)
				{
					$dir_ary = explode('|~|', $value);
					$this->template->assign_block_vars('dirs_row', [
						'DIR_LINK' => $dir_ary[1],
						'DIR_DELETE_LINK' => $dirs_delete[$i],
					]);
				}
			}

			if ($files)
			{
				natcasesort($files);
				$overall_size = 0;
				$missing_count = 0;

				foreach ($files as $i => $value)
				{
					$files_ary = explode('|~|', $value);
					$file_size = ($action != 'unassigned') ? $sizes[$i] : sprintf("%u", @filesize(DL_EXT_FILEBASE_PATH. 'downloads/' . $files_ary[1]));

					$file_size_tmp = $this->dlext_format->dl_size($file_size, 2, 'no');
					$file_size_out = $file_size_tmp['size_out'];
					$file_size_range = $file_size_tmp['range'];

					if ($action != 'unassigned')
					{
						$this->template->assign_block_vars('files_row', [
							'FILE_NAME' => $files_ary[1],
							'FILE_SIZE' => $file_size_out,
							'FILE_SIZE_RANGE' => $file_size_range,
							'FILE_EXIST' => (!$exist[$i]) ? '<input type="checkbox" class="permissions-checkbox" name="files[]" value="' . $filen[$i] . '" />' : '<input type="checkbox" class="permissions-checkbox" value="" disabled="disabled" />',
							'S_UNKNOWN_FILE' => (!$exist[$i]) ? true : false,
						]);

						if (!$exist[$i])
						{
							++$missing_count;
						}
					}
					else
					{
						$this->template->assign_block_vars('files_row', [
							'FILE_NAME' => $unas_files[substr($files_ary[1], strrpos($files_ary[1], '/') + 1)],
							'FILE_NAME_REAL' => $files_ary[1],
							'FILE_SIZE' => $file_size_out,
							'FILE_SIZE_RANGE' => $file_size_range,
							'FILE_EXIST' => '<input type="checkbox" class="permissions-checkbox" name="files[]" value="' . $files_data[$i] . '" />',
						]);

						++$missing_count;
					}

					$overall_size += $file_size;
				}

				$overall_size_tmp = [];
				$overall_size_tmp = $this->dlext_format->dl_size($overall_size, 2, 'no');
				$overall_size_out = $overall_size_tmp['size_out'];
				$file_size_range = $overall_size_tmp['range'];

				if ($action != 'unassigned')
				{
					$s_file_action = '<select name="file_command">';
					$s_file_action .= '<option value="del">'.$this->language->lang('DL_DELETE').'</option>';
					$s_file_action .= '<option value="---">---------------</option>';
					$s_file_action .= $this->dlext_physical->read_dl_dirs('', $path);
				}
				else
				{
					$s_file_action = '<select name="file_assign">';
					$s_file_action .= '<option value="del">'.$this->language->lang('DL_DELETE').'</option>';
					$s_file_action .= '<option value="---">---------------</option>';
					$s_file_action .= $this->dlext_extra->dl_dropdown(0, 0, 0, 'auth_view');
				}

				$s_file_action .= '</select>';

				$this->template->assign_block_vars('overall_size', [
					'OVERALL_SIZE' => $overall_size_out,
					'OVERALL_SIZE_RANGE' => $file_size_range,
				]);

				if ($missing_count)
				{
					$this->template->assign_block_vars('file_move_delete', [
						'S_FILE_ACTION' => $s_file_action,
					]);
				}
			}
		}
	}
}
