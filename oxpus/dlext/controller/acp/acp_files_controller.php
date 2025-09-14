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
class acp_files_controller implements acp_files_interface
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames del_t_id dl_t_ids

	/* phpbb objects */
	protected $db;
	protected $user;
	protected $log;
	protected $dispatcher;
	protected $config;
	protected $language;
	protected $request;
	protected $template;
	protected $cache;
	protected $notification;
	protected $filesystem;

	/* extension owned objects */
	public $u_action;

	protected $dlext_extra;
	protected $dlext_files;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_topic;
	protected $dlext_constants;

	protected $dlext_table_dl_comments;
	protected $dlext_table_dl_favorites;
	protected $dlext_table_dl_fields_data;
	protected $dlext_table_dl_images;
	protected $dlext_table_dl_notraf;
	protected $dlext_table_dl_ratings;
	protected $dlext_table_dl_stats;
	protected $dlext_table_dl_ver_files;
	protected $dlext_table_dl_versions;
	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\service					$cache
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\user							$user
	 * @param \phpbb\event\dispatcher_interface		$dispatcher
	 * @param \phpbb\notification\manater			$notification
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \oxpus\dlext\core\extra				$dlext_extra
	 * @param \oxpus\dlext\core\files				$dlext_files
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\topic				$dlext_topic
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_comments
	 * @param string								$dlext_table_dl_favorites
	 * @param string								$dlext_table_dl_fields_data
	 * @param string								$dlext_table_dl_images
	 * @param string								$dlext_table_dl_notraf
	 * @param string								$dlext_table_dl_ratings
	 * @param string								$dlext_table_dl_stats
	 * @param string								$dlext_table_dl_ver_files
	 * @param string								$dlext_table_dl_versions
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		\phpbb\cache\service $cache,
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\user $user,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\notification\manager $notification,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\extra $dlext_extra,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\topic $dlext_topic,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_comments,
		$dlext_table_dl_favorites,
		$dlext_table_dl_fields_data,
		$dlext_table_dl_images,
		$dlext_table_dl_notraf,
		$dlext_table_dl_ratings,
		$dlext_table_dl_stats,
		$dlext_table_dl_ver_files,
		$dlext_table_dl_versions,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->cache					= $cache;
		$this->db						= $db;
		$this->log						= $log;
		$this->user						= $user;
		$this->dispatcher				= $dispatcher;
		$this->notification				= $notification;
		$this->config					= $config;
		$this->language					= $language;
		$this->request					= $request;
		$this->template					= $template;
		$this->filesystem				= $filesystem;

		$this->dlext_table_dl_comments		= $dlext_table_dl_comments;
		$this->dlext_table_dl_favorites		= $dlext_table_dl_favorites;
		$this->dlext_table_dl_fields_data	= $dlext_table_dl_fields_data;
		$this->dlext_table_dl_images		= $dlext_table_dl_images;
		$this->dlext_table_dl_notraf		= $dlext_table_dl_notraf;
		$this->dlext_table_dl_ratings		= $dlext_table_dl_ratings;
		$this->dlext_table_dl_stats			= $dlext_table_dl_stats;
		$this->dlext_table_dl_ver_files		= $dlext_table_dl_ver_files;
		$this->dlext_table_dl_versions		= $dlext_table_dl_versions;
		$this->dlext_table_downloads		= $dlext_table_downloads;
		$this->dlext_table_dl_cat			= $dlext_table_dl_cat;

		$this->dlext_extra				= $dlext_extra;
		$this->dlext_files				= $dlext_files;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
		$this->dlext_topic				= $dlext_topic;
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
		$dl_cat				= $this->request->variable('cat_id', 0);
		$df_id				= $this->request->variable('df_id', 0);

		$dl_file = $this->dlext_files->all_files(0, [], [], $df_id, 1, ['*']);

		if (isset($dl_file['id']) && !$dl_file['id'])
		{
			trigger_error($this->language->lang('DL_MUST_SELECT_DOWNLOAD'));
		}

		$index = $this->dlext_main->full_index($dl_cat);

		if (empty($index))
		{
			$this->u_action = str_replace('mode=files', 'mode=assistant', $this->u_action);
			redirect($this->u_action);
		}

		if ($cancel)
		{
			$action = '';
		}

		if ($action == 'delete')
		{
			if (confirm_box($this->dlext_constants::DL_TRUE))
			{
				$sql = 'SELECT ver_id, dl_id, ver_real_file FROM ' . $this->dlext_table_dl_versions . '
					WHERE dl_id = ' . (int) $df_id;
				$result = $this->db->sql_query($sql);

				$ver_ids = [];
				$real_ver_file = [];
				while ($row = $this->db->sql_fetchrow($result))
				{
					$real_ver_file[$row['dl_id']] = $row['ver_real_file'];
				}

				$this->db->sql_freeresult($result);

				$del_file = $this->request->variable('del_file', 0);

				if ($del_file)
				{
					$path = $index[$dl_cat]['cat_path'];
					$file_name = $dl_file['real_file'];

					if ($path && $file_name)
					{
						$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $path . $file_name);
					}

					if (isset($real_ver_file[$df_id]))
					{
						for ($j = 0; $j < count($real_ver_file[$df_id]); ++$j)
						{
							if ($path && $real_ver_file[$df_id][$j])
							{
								$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $path . $real_ver_file[$df_id][$j]);
							}
						}
					}

					$sql = 'SELECT file_type, real_name FROM ' . $this->dlext_table_dl_ver_files . '
							WHERE dl_id = ' . (int) $df_id;
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

					$sql = 'SELECT img_name FROM ' . $this->dlext_table_dl_images . '
							WHERE dl_id = ' . (int) $df_id;
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						if ($row['img_name'])
						{
							$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/thumbs/' . $row['img_name']);
						}
					}

					$this->db->sql_freeresult($result);
				}

				$sql = 'SELECT cat, description, dl_topic FROM ' . $this->dlext_table_downloads . '
					WHERE id = ' . (int) $df_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row['dl_topic'])
				{
					$topic_drop_mode	= $this->request->variable('topic_drop_mode', 'drop');
					$del_t_id[]			= $row['dl_topic'];
					$dl_t_ids[]			= $df_id;

					$this->dlext_topic->delete_dl_topic($del_t_id, $topic_drop_mode, $dl_t_ids);
				}

				$dl_desc	= $row['description'];

				$sql = 'DELETE FROM ' . $this->dlext_table_downloads . '
					WHERE id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				if (!empty($ver_ids))
				{
					$sql = 'DELETE FROM ' . $this->dlext_table_dl_versions . '
						WHERE ' . $this->db->sql_in_set('ver_id', $ver_ids);
					$this->db->sql_query($sql);

					$sql = 'DELETE FROM ' . $this->dlext_table_dl_ver_files . '
						WHERE ' . $this->db->sql_in_set('ver_id', $ver_ids);
					$this->db->sql_query($sql);
				}

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_stats . '
					WHERE id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_comments . '
					WHERE id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_notraf . '
					WHERE dl_id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_fields_data . '
					WHERE df_id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_ratings . '
					WHERE dl_id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_favorites . '
					WHERE fav_dl_id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_images . '
					WHERE dl_id = ' . (int) $df_id;
				$this->db->sql_query($sql);

				/**
				 * Workflow after delete download
				 *
				 * @event oxpus.dlext.acp_files_delete_download_after
				 * @var int df_id		download ID
				 * @var int dl_cat		download category ID
				 * @since 8.1.0-RC2
				 */
				$vars = [
					'df_id',
					'dl_cat',
				];
				extract($this->dispatcher->trigger_event('oxpus.dlext.acp_files_delete_download_after', compact($vars)));

				$this->notification->delete_notifications([
					'oxpus.dlext.notification.type.approve',
					'oxpus.dlext.notification.type.broken',
					'oxpus.dlext.notification.type.dlext',
					'oxpus.dlext.notification.type.update',
					'oxpus.dlext.notification.type.capprove',
					'oxpus.dlext.notification.type.comments',
				], $df_id);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_DEL_FILE', false, [$dl_desc]);

				// Purge the files cache
				$this->cache->destroy('_dlext_cat_counts');
				$this->cache->destroy('_dlext_file_preset');

				$message = $this->language->lang('DL_DOWNLOAD_REMOVED') . '<br><br>' . $this->language->lang('CLICK_RETURN_DOWNLOADADMIN', '<a href="' . $this->u_action . '&amp;cat_id=' . $dl_cat . '">', '</a>') . adm_back_link($this->u_action);

				trigger_error($message);
			}
			else
			{
				$description = $dl_file['description'];

				$this->template->assign_var('S_DL_DELETE_FILES_CONFIRM', $this->dlext_constants::DL_TRUE);
				$this->template->assign_var('S_DL_DELETE_TOPIC_CONFIRM', $this->dlext_constants::DL_TRUE);

				$s_hidden_fields = [
					'cat_id'	=> $dl_cat,
					'df_id'		=> $df_id,
					'action'	=> 'delete',
				];

				confirm_box($this->dlext_constants::DL_FALSE, $this->language->lang('DL_CONFIRM_DELETE_SINGLE_FILE', $description), build_hidden_fields($s_hidden_fields), '@oxpus_dlext/dl_confirm_body.html');
			}
		}
		else if ($action == 'downloads_order')
		{
			$move = $this->request->variable('move', '');

			$sql = 'SELECT sort, description FROM ' . $this->dlext_table_downloads . '
				WHERE id = ' . (int) $df_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$dl_desc = $row['description'];
			$dl_sort = $row['sort'];

			if ($move == $this->dlext_constants::DL_MOVE_UP)
			{
				$dl_sort += $this->dlext_constants::DL_MOVE_UP;
			}
			else if ($move == $this->dlext_constants::DL_MOVE_DOWN)
			{
				$dl_sort += $this->dlext_constants::DL_MOVE_DOWN;
			}

			$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
				'sort' => $dl_sort
			]) . ' WHERE id = ' . (int) $df_id;
			$this->db->sql_query($sql);

			$sql = 'SELECT id FROM ' . $this->dlext_table_downloads . '
				WHERE cat = ' . (int) $dl_cat . '
				ORDER BY sort ASC';
			$result = $this->db->sql_query($sql);

			$i = $this->dlext_constants::DL_SORT_RANGE;

			while ($row = $this->db->sql_fetchrow($result))
			{
				$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'sort' => $i
				]) . ' WHERE id = ' . (int) $row['id'];
				$this->db->sql_query($sql);

				$i += $this->dlext_constants::DL_SORT_RANGE;
			}

			$this->db->sql_freeresult($result);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILE_MOVE', false, [$dl_desc]);

			$action = '';
		}
		else if ($action == 'downloads_order_all')
		{
			$sql = 'SELECT cat_name FROM ' . $this->dlext_table_dl_cat . '
				WHERE id = ' . (int) $dl_cat;
			$result = $this->db->sql_query($sql);
			$cat_name = $this->db->sql_fetchfield('cat_name');
			$this->db->sql_freeresult($result);

			$sql = 'SELECT id FROM ' . $this->dlext_table_downloads . '
				WHERE cat = ' . (int) $dl_cat . '
				ORDER BY description ASC';
			$result = $this->db->sql_query($sql);

			$i = $this->dlext_constants::DL_SORT_RANGE;

			while ($row = $this->db->sql_fetchrow($result))
			{
				$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'sort' => $i
				]) . ' WHERE id = ' . (int) $row['id'];
				$this->db->sql_query($sql);

				$i += $this->dlext_constants::DL_SORT_RANGE;
			}

			$this->db->sql_freeresult($result);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FILES_SORT', false, [$cat_name]);

			$action = '';
		}

		if ($action == '')
		{
			$sql = 'SELECT hacklist, hack_version, file_name, real_file, description, desc_uid, desc_bitfield, desc_flags, id, free, extern, test, cat, klicks, overall_klicks, file_traffic, file_size, approve
					FROM ' . $this->dlext_table_downloads . '
				WHERE cat = ' . (int) $dl_cat . '
				ORDER BY sort';
			$result = $this->db->sql_query($sql);
			$total_files = $this->db->sql_affectedrows();

			while ($row = $this->db->sql_fetchrow($result))
			{
				$hacklist		= ($row['hacklist'] == 1) ? $this->language->lang('YES') : $this->language->lang('NO');
				$version		= $row['hack_version'];
				$description	= $row['description'];
				$file_id		= $row['id'];
				$file_free		= $row['free'];
				$file_extern	= $row['extern'];
				$test			= ($row['test']) ? '[' . $row['test'] . ']' : '';
				$dl_cat			= $row['cat'];
				$file_name		= ($file_extern) ? $this->language->lang('DL_EXTERN') : $this->language->lang('DL_DOWNLOAD') . ': ' . $row['file_name'] . '<br>{' . $row['real_file'] . '}';

				$desc_uid		= $row['desc_uid'];
				$desc_bitfield	= $row['desc_bitfield'];
				$desc_flags		= $row['desc_flags'];
				$description	= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

				switch ($file_free)
				{
					case $this->dlext_constants::DL_FILE_FREE_ALL:
						$file_free_out = $this->language->lang('DL_FREE');
						break;

					case $this->dlext_constants::DL_FILE_FREE_REG_USER:
						$file_free_out = $this->language->lang('DL_YES_REG');
						break;

					default:
						$file_free_out = $this->language->lang('DL_NO');
				}

				$file_free_extern_out	= ($file_extern) ? $this->language->lang('DL_EXTERN') : $file_free_out;

				$file_klicks			= $row['klicks'];
				$file_overall_klicks	= $row['overall_klicks'];
				$file_traffic			= $row['file_traffic'];

				if ($file_traffic && !$this->config['dl_traffic_off'])
				{
					$file_traffic = $this->dlext_format->dl_size($file_traffic);
				}
				else
				{
					$file_traffic = $this->language->lang('DL_NOT_AVAILABLE');
				}

				if ($row['file_size'])
				{
					$file_size_kb	= $this->dlext_format->dl_size($row['file_size']);
				}
				else
				{
					$file_size_kb	= $this->language->lang('DL_NOT_AVAILABLE');
				}

				$unapprove = ($row['approve']) ? '' : $this->language->lang('DL_UNAPPROVED');

				$dl_edit	= $this->u_action . '&amp;action=edit&amp;df_id=' . $file_id;
				$dl_delete	= $this->u_action . '&amp;action=delete&amp;df_id=' . $file_id . '&amp;cat_id=' . $dl_cat;

				$dl_move_up		= $this->u_action . '&amp;action=downloads_order&amp;move=' . $this->dlext_constants::DL_MOVE_UP . '&amp;df_id=' . $file_id . '&amp;cat_id=' . $dl_cat;
				$dl_move_down	= $this->u_action . '&amp;action=downloads_order&amp;move=' . $this->dlext_constants::DL_MOVE_DOWN . '&amp;df_id=' . $file_id . '&amp;cat_id=' . $dl_cat;

				$this->template->assign_block_vars('downloads', [
					'DL_DESCRIPTION'		=> $description,
					'DL_TEST'				=> $test,
					'DL_FILE_ID'			=> $file_id,
					'DL_FILE_SIZE'			=> $file_size_kb,
					'DL_FILE_FREE_EXTERN'	=> $file_free_extern_out,
					'DL_FILE_KLICKS'		=> $file_klicks,
					'DL_FILE_TRAFFIC'		=> $file_traffic,
					'DL_UNAPPROVED'			=> $unapprove,
					'DL_OVERALL_KLICKS'		=> $file_overall_klicks,
					'DL_HACKLIST'			=> $hacklist,
					'DL_VERSION'			=> $version,
					'DL_FILE_NAME'			=> $file_name,

					'U_DL_EDIT'				=> $dl_edit,
					'U_DL_DELETE'			=> $dl_delete,
					'U_DL_MOVE_UP'			=> $dl_move_up,
					'U_DL_MOVE_DOWN'		=> $dl_move_down,
				]);
			}

			$categories = $this->dlext_extra->dl_dropdown(0, 0, $dl_cat, 'auth_up');

			if (!empty($categories) && is_array($categories))
			{
				foreach (array_keys($categories) as $key)
				{
					$this->template->assign_block_vars('cat_select_row', [
						'DL_CAT_ID'			=> $categories[$key]['cat_id'],
						'DL_SEPERATOR'		=> $categories[$key]['seperator'],
						'DL_SELECTED'		=> $categories[$key]['selected'],
						'DL_CAT_NAME'		=> $categories[$key]['cat_name'],
					]);
				}

				$this->template->assign_vars([
					'DL_CAT'					=> $dl_cat,
					'DL_CATEGORIES'				=> $categories,
					'DL_COUNT'					=> $total_files . '&nbsp;' . $this->language->lang('DL_DOWNLOADS'),
					'DL_NONE'					=> $this->dlext_constants::DL_NONE,
					'DL_PERM_NONE'				=> $this->dlext_constants::DL_PERM_GENERAL_NONE,

					'S_DL_DOWNLOADS_ACTION'		=> $this->u_action,
					'S_DL_HIDDEN_FIELDS'		=> build_hidden_fields(['cat_id' => $dl_cat]),

					'U_DL_DOWNLOAD_ORDER_ALL'	=> $this->u_action . '&amp;action=downloads_order_all&amp;cat_id=' . $dl_cat,
				]);

				if ($total_files)
				{
					$this->template->assign_var('S_DL_LIST_DOWNLOADS', $this->dlext_constants::DL_TRUE);
				}
			}
		}
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
