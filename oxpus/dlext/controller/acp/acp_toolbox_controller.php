<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\acp;

/**
 * @package acp
 */
class acp_toolbox_controller implements acp_toolbox_interface
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames files_name files_path real_thumbnails

	/* phpbb objects */
	protected $db;
	protected $user;
	protected $root_path;
	protected $extension_manager;
	protected $log;
	protected $dispatcher;
	protected $language;
	protected $request;
	protected $template;
	protected $cache;
	protected $notification;
	protected $filesystem;
	protected $finder;

	/* extension owned objects */
	public $u_action;

	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_physical;
	protected $dlext_constants;

	protected $dlext_table_dl_images;
	protected $dlext_table_dl_versions;
	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param \phpbb\cache\service					$cache
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\extension\manager				$extension_manager
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\user							$user
	 * @param \phpbb\event\dispatcher_interface		$dispatcher
	 * @param \phpbb\notification\manager			$notification
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\physical			$dlext_physical
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_images
	 * @param string								$dlext_table_dl_versions
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		$root_path,
		\phpbb\cache\service $cache,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\extension\manager $extension_manager,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\user $user,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\notification\manager $notification,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\physical $dlext_physical,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_images,
		$dlext_table_dl_versions,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->root_path				= $root_path;
		$this->cache					= $cache;
		$this->extension_manager		= $extension_manager;
		$this->db						= $db;
		$this->log						= $log;
		$this->user						= $user;
		$this->dispatcher				= $dispatcher;
		$this->notification				= $notification;
		$this->language					= $language;
		$this->request					= $request;
		$this->template					= $template;
		$this->filesystem				= $filesystem;

		$this->finder					= $this->extension_manager->get_finder();

		$this->dlext_table_dl_images	= $dlext_table_dl_images;
		$this->dlext_table_dl_versions	= $dlext_table_dl_versions;
		$this->dlext_table_downloads	= $dlext_table_downloads;
		$this->dlext_table_dl_cat		= $dlext_table_dl_cat;

		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_physical			= $dlext_physical;
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
		$dir_name			= $this->request->variable('dir_name', '', $this->dlext_constants::DL_TRUE);
		$dircreate			= $this->request->variable('dircreate', '');
		$description		= $this->request->variable('description', '', $this->dlext_constants::DL_TRUE);
		$file_assign		= $this->request->variable('file_assign', '');
		$file_command		= $this->request->variable('file_command', '');
		$file_name			= $this->request->variable('file_name', '', $this->dlext_constants::DL_TRUE);
		$path				= $this->request->variable('path', '', $this->dlext_constants::DL_TRUE);
		$submit				= $this->request->variable('submit', '');
		$files				= $this->request->variable('files', [''], $this->dlext_constants::DL_TRUE);

		$index = $this->dlext_main->full_index();

		if (empty($index))
		{
			$this->u_action = str_replace('mode=toolbox', 'mode=assistant', $this->u_action);
			redirect($this->u_action);
		}

		unset($index);

		if ($action == 'dl' && $file_name && $path)
		{
			if (!$description)
			{
				$description = $file_name;
			}

			$file_path = $this->root_path;

			$dl_data = [
				'physical_file'		=> $file_path . $path,
				'real_filename'		=> $description,
				'mimetype'			=> 'application/octetstream',
				'filesize'			=> sprintf('%u', filesize($file_path . $path)),
				'filetime'			=> filemtime($file_path . $path),
			];

			if (!$this->filesystem->exists($file_path . $path))
			{
				trigger_error($this->language->lang('FILE_NOT_FOUND_404', $description) . adm_back_link($this->u_action . '&amp;path=/' . $path), E_USER_WARNING);
			}

			$this->dlext_physical->send_file_to_browser($dl_data);
		}

		if ($cancel)
		{
			redirect($this->u_action);
		}

		add_form_key('dl_adm_tools');

		if ($submit && !check_form_key('dl_adm_tools'))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
		}

		if (!empty($files) && $file_assign)
		{
			for ($i = 0; $i < count($files); ++$i)
			{
				$temp = strpos($files[$i], '|');
				$files_path[] = substr($files[$i], 0, $temp);
				$files_name[] = substr($files[$i], $temp + 1);
			}

			if ($file_assign == 'del')
			{
				for ($i = 0; $i < count($files); ++$i)
				{
					$dl_dir = ($files_path[$i]) ? substr($this->dlext_constants->get_value('files_dir') . '/downloads/', 0, strlen($this->dlext_constants->get_value('files_dir') . '/downloads/') - 1) : $this->dlext_constants->get_value('files_dir') . '/downloads/';

					if ($files_path[$i] && $files_name[$i])
					{
						$this->filesystem->remove($dl_dir . $files_path[$i] . '/' . $files_name[$i]);
					}

					$sql = 'SELECT id, cat FROM ' . $this->dlext_table_downloads . "
						WHERE real_file = '" . $this->db->sql_escape($files_name[$i]) . "'";
					$result = $this->db->sql_query($sql);

					$dl_ids = [];

					while ($row = $this->db->sql_fetchrow($result))
					{
						$dl_ids[] = $row['id'];
					}

					$this->db->sql_freeresult($result);

					$sql = 'DELETE FROM ' . $this->dlext_table_downloads . '
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
					extract($this->dispatcher->trigger_event('oxpus.dlext.acp_toolbox_delete_downloads_after', compact($vars)));

					$this->notification->delete_notifications([
						'oxpus.dlext.notification.type.approve',
						'oxpus.dlext.notification.type.broken',
						'oxpus.dlext.notification.type.dlext',
						'oxpus.dlext.notification.type.update',
						'oxpus.dlext.notification.type.capprove',
						'oxpus.dlext.notification.type.comments',
					], $dl_ids);

					$this->cache->destroy('_dlext_file_p');

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILE_DROP', false, [$files_name[$i]]);
				}
			}
			else
			{
				$dl_dir = substr($this->dlext_constants->get_value('files_dir') . '/downloads/', 0, strlen($this->dlext_constants->get_value('files_dir') . '/downloads/') - 1);

				for ($i = 0; $i < count($files); ++$i)
				{
					$sql = 'SELECT path FROM ' . $this->dlext_table_dl_cat . '
						WHERE id = ' . (int) $file_assign;
					$result = $this->db->sql_query($sql);
					$cat_path = $this->db->sql_fetchfield('path');
					$this->db->sql_freeresult($result);

					if ($cat_path != substr($files_path[$i], 1) . '/')
					{
						$this->filesystem->rename($dl_dir . $files_path[$i] . '/' . $files_name[$i], $this->dlext_constants->get_value('files_dir') . '/downloads/' . $cat_path . $files_name[$i]);
					}

					$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'cat' => $file_assign
					]) . " WHERE real_file = '" . $this->db->sql_escape($files_name[$i]) . "'";
					$this->db->sql_query($sql);

					$this->cache->destroy('_dlext_file_p');

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILE_ADD', false, [$files_name[$i]]);
				}
			}

			$file_command = '';
		}

		if ($action == 'check_file_sizes')
		{
			$sql = 'SELECT dl.*, c.path FROM ' . $this->dlext_table_downloads . ' dl, ' . $this->dlext_table_dl_cat . ' c
				WHERE dl.cat = c.id
					AND dl.extern <> 1
				ORDER BY dl.id';
			$result = $this->db->sql_query($sql);

			$message = '';

			while ($row = $this->db->sql_fetchrow($result))
			{
				$file_size	= $row['file_size'];
				$file_desc	= $row['description'];
				$real_file	= $row['real_file'];
				$file_path	= $row['path'];
				$file_id	= $row['id'];

				$check_file_size = sprintf('%u', @filesize($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $real_file));
				if ($check_file_size == 0 || $check_file_size == '')
				{
					$message .= str_replace('/', ' / ', $file_path) . ((!$real_file) ? '(' . $file_desc . ')' : $real_file) . '<br>';
				}
				else if ($check_file_size != $file_size)
				{
					$sql_new = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
						'file_size' => $check_file_size
					]) . ' WHERE id = ' . (int) $file_id;
					$result_new = $this->db->sql_query($sql_new);

					$this->cache->destroy('_dlext_file_p');

					if (!$result_new)
					{
						$message .= str_replace('/', ' / ', $file_path) . ((!$real_file) ? '(' . $file_desc . ')' : $real_file) . '<br>';
					}
				}
			}

			$action = '';

			if ($message != '')
			{
				$check_message = $this->language->lang('DL_CHECK_FILESIZES_RESULT_ERROR') . '<br><br>' . $message;
			}
			else
			{
				$check_message = $this->language->lang('DL_CHECK_FILESIZES_RESULT');
			}

			$check_message .= adm_back_link($this->u_action);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILES_CHECK');

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
			$del_real_thumbs	= $this->request->variable('del_real_thumbs', '');
			$thumbs				= $this->request->variable('thumb', [''], $this->dlext_constants::DL_TRUE);

			if ($del_real_thumbs)
			{
				foreach (array_keys($thumbs) as $key)
				{
					if ($thumbs[$key])
					{
						$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/thumbs/' . $thumbs[$key]);
					}
				}

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_THUMBS_DEL');
			}

			$real_thumbnails['file_name'] = [];
			$real_thumbnails['file_size'] = [];

			$thumbs_path = $this->dlext_constants->get_value('files_dir', $this->dlext_constants::DL_TRUE) . '/thumbs/';

			$files = $this->finder
				->set_extensions([])
				->core_path($thumbs_path)
				->find(false);

			foreach (array_keys($files) as $file)
			{
				$check_file = basename($file);

				if ($check_file != 'index.html' && $check_file != 'index.htm')
				{
					$real_thumbnails['file_name'][] = $check_file;
					$real_thumbnails['file_size'][] = sprintf('%u', filesize($this->root_path . $file));
				}
			}

			$dl_thumbs = [];

			$sql = 'SELECT img_name FROM ' . 	$this->dlext_table_dl_images;
			$result = $this->db->sql_query($sql);

			while ($dl_thumbs[] = $this->db->sql_fetchfield('img_name'));
			$this->db->sql_freeresult($result);

			if (!empty($real_thumbnails['file_name']) && count($real_thumbnails['file_name']))
			{
				$this->template->assign_vars([
					'S_DL_THUMBS'			=> $this->dlext_constants::DL_TRUE,
					'S_DL_MANAGE_ACTION'	=> $this->u_action . '&amp;action=check_thumbnails',

					'U_DL_BACK'				=> $this->u_action,
				]);

				$j = $this->dlext_constants::DL_NONE;

				for ($i = 0; $i < count($real_thumbnails['file_name']); ++$i)
				{
					$real_file = $real_thumbnails['file_name'][$i];

					if (!in_array($real_file, $dl_thumbs))
					{
						$checkbox = $this->dlext_constants::DL_NONE;
					}
					else
					{
						++$j;
						$checkbox = $j;
					}

					$this->template->assign_block_vars('thumbnails', [
						'DL_CHECKBOX'			=> $checkbox,
						'DL_REAL_FILE'			=> $real_file,
						'DL_REAL_FILE_CODE'		=> $real_file,
						'DL_FILE_SIZE'			=> $this->dlext_format->dl_size($real_thumbnails['file_size'][$i]),

						'U_DL_REAL_FILE'		=> $this->dlext_constants->get_value('files_dir') . '/thumbs/' . $real_file,
					]);
				}
			}
			else
			{
				$action = '';
				$path = '';
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
					if ($path && $files[$i])
					{
						$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $path . $files[$i]);

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILE_DROP', false, [$files[$i]]);
					}
				}
			}
			else
			{
				for ($i = 0; $i < count($files); ++$i)
				{
					$this->filesystem->rename($this->dlext_constants->get_value('files_dir') . '/downloads/' . $path . $files[$i], $file_command . $files[$i]);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILE_MOVE', false, [$files[$i]]);
				}
			}

			$path = $path_temp;
		}

		if ($dir_name && $dircreate)
		{
			if ($path != '/')
			{
				$file_path = $path . '/';
			}
			else
			{
				$file_path = '';
				$path = '';
			}

			$this->filesystem->mkdir($this->dlext_constants->get_value('files_dir') . '/downloads/' . $file_path . $dir_name . '/');

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FOLDER_CREATE', false, [$path . '/' . $dir_name]);
		}

		if ($action == 'dirdelete')
		{
			$file_name = basename($path);

			$files = $this->finder
				->set_extensions([])
				->core_path($this->dlext_constants->get_value('files_dir', $this->dlext_constants::DL_TRUE) . '/downloads/' . $path)
				->find(false);

			if (count($files) === 0 && $path)
			{
				$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $path);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FOLDER_DROP', false, [$path]);
			}

			$action = '';

			$path = ($path != $file_name) ? substr($path, 0, strlen($path) - strlen($file_name) - 1) : '';
		}

		if ($action == 'browse' || $action == '' || $action == 'unassigned')
		{
			$dirs  = [];
			$filey = [];
			$filen = [];
			$sizes = [];
			$exist = [];
			$dirs_delete = [];

			$browse_dir = '';
			$unassigned_files = $this->dlext_constants::DL_FALSE;
			$existing_files = [];

			if ($action != 'unassigned')
			{
				$temp_url = '';
				$dl_navi = [];

				$dl_navi[] = ['link' => $this->u_action . '&amp;action=browse', 'name' => str_replace('/', ' / ', $this->dlext_constants->get_value('files_dir') . '/downloads')];

				if ($path)
				{
					$path = ($path[0] == '/') ? substr($path, 1) : $path;

					$temp_dir = explode('/', $path);

					if (!empty($temp_dir))
					{
						for ($i = 0; $i < count($temp_dir); ++$i)
						{
							$temp_url .= '/' . $temp_dir[$i];
							$temp_path = preg_replace('#[/]*#', '', $temp_dir[$i]);

							$dl_navi[] = ['link' => $this->u_action . '&amp;action=browse&amp;path=' . $temp_url, 'name' => $temp_path];
						}
					}
				}

				$this->dlext_physical->get_files_assignments($path, $browse_dir, $exist, $filey, $filen, $sizes, $unassigned_files, $existing_files, $this->u_action);

				$files = $this->finder
					->set_extensions([])
					->core_path($browse_dir)
					->find(false, true);

				natcasesort($files);

				foreach ($files as $file => $new_path)
				{
					$file_name	= basename($file);
					$dirname	= dirname($file) . '/';
					$file_path	= $path . $new_path;

					$check_path	= ($dirname == $browse_dir) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

					if ($check_path)
					{
						$dirs[] = $this->root_path . $file . '|~|<a href="' . $this->u_action . '&amp;action=browse&amp;path=' . $file_path . $file_name . '">' . $file_name . '</a>';

						$subfiles = $this->finder
							->set_extensions([])
							->core_path($file)
							->find(false);

						natcasesort($subfiles);

						$count_sub_files = 0;

						foreach (array_keys($subfiles) as $subfile)
						{
							$subfile = basename($subfile);

							if ($subfile != 'index.html' && $subfile != 'index.htm')
							{
								++$count_sub_files;
							}
						}

						$dirs_delete[] = ($count_sub_files) ?  $this->language->lang('DL_MANAGE_CONTENT_COUNT', $count_sub_files) : '<a href="' . $this->u_action . '&amp;action=dirdelete&amp;path=' . $file_path . $file_name . '">' . $this->language->lang('DL_DELETE') . '</a>';
					}
				}

				for ($i = 0; $i < count($dl_navi); ++$i)
				{
					$this->template->assign_block_vars('dl_toolbox_navi', [
						'DL_LINK'	=> $dl_navi[$i]['link'],
						'DL_NAME'	=> $dl_navi[$i]['name'],
					]);
				}

				$this->template->assign_var('S_DL_CREATE_DIR_COMMAND', $this->dlext_constants::DL_TRUE);
			}

			$this->template->assign_vars([
				'S_DL_MANAGE_ACTION'			=> $this->u_action . '&amp;path=' . $path,
				'S_DL_UNASSIGNED_FILES'			=> $unassigned_files,

				'U_DL_DOWNLOADS_CHECK_FILES'	=> $this->u_action . '&amp;action=check_file_sizes',
				'U_DL_DOWNLOADS_CHECK_THUMB'	=> $this->u_action . '&amp;action=check_thumbnails',
			]);

			$existing_thumbs = $this->dlext_constants::DL_FALSE;

			$thumbs = $this->finder
				->set_extensions([])
				->core_path($this->dlext_constants->get_value('files_dir', $this->dlext_constants::DL_TRUE) . '/thumbs/')
				->find(false);

			if (count($thumbs))
			{
				$existing_thumbs = $this->dlext_constants::DL_TRUE;
			}

			if ($existing_thumbs)
			{
				$this->template->assign_var('S_DL_THUMBNAIL_CHECK', $this->dlext_constants::DL_TRUE);
			}

			if (empty($dirs) && empty($filey) && !$unassigned_files)
			{
				$this->template->assign_var('S_DL_EMPTY_FOLDER', $this->dlext_constants::DL_TRUE);
			}

			if ($dirs)
			{
				natcasesort($dirs);
				foreach ($dirs as $i => $value)
				{
					$dir_ary = explode('|~|', $value);
					$this->template->assign_block_vars('dirs_row', [
						'DL_DIR_LINK' 			=> $dir_ary[1],
						'DL_DIR_DELETE_LINK'	=> $dirs_delete[$i],
					]);
				}
			}

			if (!empty($filey))
			{
				natcasesort($filey);
				$overall_size = 0;
				$missing_count = 0;

				foreach ($filey as $i => $value)
				{
					$files_ary = explode('|~|', $value);
					$file_size = ($action != 'unassigned') ? $sizes[$i] : sprintf('%u', filesize($this->root_path . $files_ary[1]));

					$file_size_tmp = $this->dlext_format->dl_size($file_size, 2, 'no');
					$file_size_out = $file_size_tmp['size_out'];
					$file_size_range = $file_size_tmp['range'];

					if ($action != 'unassigned')
					{
						$this->template->assign_block_vars('files_row', [
							'DL_FILE_NAME'			=> $files_ary[1],
							'DL_FILE_SIZE'			=> $file_size_out,
							'DL_FILE_SIZE_RANGE'	=> $file_size_range,
							'DL_FILE_EXIST'			=> (!$exist[$i]) ? $filen[$i] : $this->dlext_constants::DL_FALSE,
						]);

						if (!$exist[$i])
						{
							++$missing_count;
						}
					}

					$overall_size += $file_size;
				}

				$overall_size_tmp = $this->dlext_format->dl_size($overall_size, 2, 'no');
				$overall_size_out = $overall_size_tmp['size_out'];
				$file_size_range = $overall_size_tmp['range'];

				if ($missing_count)
				{
					$s_folder_tree = $this->dlext_physical->read_dl_dirs();

					if (!empty($s_folder_tree) && is_array($s_folder_tree))
					{
						foreach (array_keys($s_folder_tree) as $key)
						{
							$this->template->assign_block_vars('folder_tree_select', [
								'DL_PATH'	=> $s_folder_tree[$key]['path'],
								'DL_TARGET'	=> $s_folder_tree[$key]['target'],
							]);
						}

						$this->template->assign_var('S_DL_FILE_COMMAND', true);
					}
				}

				$this->template->assign_block_vars('overall_size', [
					'DL_OVERALL_SIZE'		=> $overall_size_out,
					'DL_OVERALL_SIZE_RANGE'	=> $file_size_range,
				]);
			}
		}
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
