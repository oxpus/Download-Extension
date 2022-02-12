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
class acp_categories_controller implements acp_categories_interface
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames real_ver_file

	/* phpbb objects */
	protected $db;
	protected $user;
	protected $log;
	protected $dispatcher;
	protected $language;
	protected $request;
	protected $template;
	protected $cache;
	protected $notification;
	protected $filesystem;

	/* extension owned objects */
	public $u_action;

	protected $dlext_extra;
	protected $dlext_main;
	protected $dlext_nav;
	protected $dlext_constants;

	protected $dlext_table_dl_auth;
	protected $dlext_table_dl_cat_traf;
	protected $dlext_table_dl_comments;
	protected $dlext_table_dl_stats;
	protected $dlext_table_dl_versions;
	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\service					$cache
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\user							$user
	 * @param \phpbb\event\dispatcher_interface		$dispatcher
	 * @param \phpbb\notification\manager			$notification
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \oxpus\dlext\core\extra				$dlext_extra
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\nav					$dlext_nav
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_auth
	 * @param string								$dlext_table_dl_cat_traf
	 * @param string								$dlext_table_dl_comments
	 * @param string								$dlext_table_dl_stats
	 * @param string								$dlext_table_dl_versions
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		\phpbb\cache\service $cache,
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
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\nav $dlext_nav,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_auth,
		$dlext_table_dl_cat_traf,
		$dlext_table_dl_comments,
		$dlext_table_dl_stats,
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
		$this->language					= $language;
		$this->request					= $request;
		$this->template					= $template;
		$this->notification				= $notification;
		$this->filesystem				= $filesystem;

		$this->dlext_table_dl_auth		= $dlext_table_dl_auth;
		$this->dlext_table_dl_cat_traf	= $dlext_table_dl_cat_traf;
		$this->dlext_table_dl_comments	= $dlext_table_dl_comments;
		$this->dlext_table_dl_stats		= $dlext_table_dl_stats;
		$this->dlext_table_dl_versions	= $dlext_table_dl_versions;
		$this->dlext_table_downloads	= $dlext_table_downloads;
		$this->dlext_table_dl_cat		= $dlext_table_dl_cat;

		$this->dlext_extra				= $dlext_extra;
		$this->dlext_main				= $dlext_main;
		$this->dlext_nav				= $dlext_nav;
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
		$idx_type			= $this->request->variable('type', 'c');
		$move				= $this->request->variable('move', '');
		$cat_id				= $this->request->variable('cat_id', 0);
		$new_cat_id			= $this->request->variable('new_cat_id', 0);
		$cat_parent			= $this->request->variable('parent', 0);
		$cat_name			= $this->request->variable('cat_name', '', $this->dlext_constants::DL_TRUE);

		if ($cancel)
		{
			$action = '';
		}
		else
		{
			$action = ($move) ? 'category_order' : $action;
		}

		$index = $this->dlext_main->full_index();

		if (empty($index) && $action != 'save_cat')
		{
			$this->u_action = str_replace('mode=categories', 'mode=assistant', $this->u_action);
			redirect($this->u_action);
		}

		if (isset($index[$cat_id]['cat_name']))
		{
			$log_cat_name = $index[$cat_id]['cat_name'];
		}

		if ($action == 'delete' && $cat_id && !$this->dlext_main->get_sublevel_count($cat_id))
		{
			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type . '&amp;t=' . microtime();

			if (confirm_box($this->dlext_constants::DL_TRUE))
			{
				if ($new_cat_id <= 0)
				{
					$sql = 'SELECT dl_id, ver_real_file FROM ' . $this->dlext_table_dl_versions;
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						$real_ver_file[$row['dl_id']][] = $row['ver_real_file'];
					}

					$this->db->sql_freeresult($result);

					$sql = 'SELECT c.cat_name, c.path, d.real_file, d.id AS df_id FROM ' . $this->dlext_table_dl_cat . ' c, ' . $this->dlext_table_downloads . ' d
						WHERE d.cat = c.id
							AND c.id = ' . (int) $cat_id . '
							AND d.extern = 0';
					$result = $this->db->sql_query($sql);

					$dl_ids = [];

					while ($row = $this->db->sql_fetchrow($result))
					{
						$df_id		= $row['df_id'];
						$dl_ids[]	= $df_id;
						$path		= $row['path'];
						$real_file	= $row['real_file'];

						if (!$new_cat_id && $path)
						{
							if ($real_file)
							{
								$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $path . $real_file);
							}

							if (isset($real_ver_file[$df_id]))
							{
								for ($i = 0; $i < count($real_ver_file[$df_id]); ++$i)
								{
									if ($real_ver_file[$df_id][$i])
									{
										$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $path . $real_ver_file[$df_id][$i]);
									}
								}
							}
						}
					}

					$this->db->sql_freeresult($result);

					$sql = 'DELETE FROM ' . $this->dlext_table_downloads . '
						WHERE cat = ' . (int) $cat_id;
					$this->db->sql_query($sql);

					/**
					 * Workflow after deleting downloads
					 *
					 * @event oxpus.dlext.acp_categories_delete_downloads_after
					 * @var array	dl_ids		download ID's
					 * @var int		cat_id		download category ID
					 * @since 8.1.0-RC2
					 */
					$vars = array(
						'dl_ids',
						'cat_id',
					);
					extract($this->dispatcher->trigger_event('oxpus.dlext.acp_categories_delete_downloads_after', compact($vars)));

					if (!empty($dl_ids))
					{
						$sql = 'DELETE FROM ' . $this->dlext_table_dl_versions . '
							WHERE ' . $this->db->sql_in_set('dl_id', $dl_ids);
						$this->db->sql_query($sql);

						$this->notification->delete_notifications([
							'oxpus.dlext.notification.type.approve',
							'oxpus.dlext.notification.type.broken',
							'oxpus.dlext.notification.type.dlext',
							'oxpus.dlext.notification.type.update',
							'oxpus.dlext.notification.type.capprove',
							'oxpus.dlext.notification.type.comments',
						], $dl_ids);
					}
				}

				if ($new_cat_id > 0)
				{
					$sql = 'SELECT path FROM ' . $this->dlext_table_dl_cat . '
						WHERE id = ' . (int) $new_cat_id;
					$result = $this->db->sql_query($sql);
					$new_path = $this->db->sql_fetchfield('path');
					$this->db->sql_freeresult($result);

					$sql = 'SELECT dl_id, ver_real_file FROM ' . $this->dlext_table_dl_versions;
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						$real_ver_file[$row['dl_id']][] = $row['ver_real_file'];
					}

					$this->db->sql_freeresult($result);

					$sql = 'SELECT c.cat_name, c.path, d.real_file, d.id AS df_id FROM ' . $this->dlext_table_dl_cat . ' c, ' . $this->dlext_table_downloads . ' d
						WHERE d.cat = c.id
							AND c.id = ' . (int) $cat_id . '
							AND d.extern = 0';
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						$df_id = $row['df_id'];
						$dl_ids[] = $df_id;
						$path = $row['path'];
						$real_file = $row['real_file'];

						$this->filesystem->rename($this->dlext_constants->get_value('files_dir') . '/downloads/' . $path . $real_file, $this->dlext_constants->get_value('files_dir') . '/downloads/' . $new_path . $real_file);

						if (isset($real_ver_file[$df_id]))
						{
							for ($i = 0; $i < count($real_ver_file[$df_id]); ++$i)
							{
								$this->filesystem->rename($this->dlext_constants->get_value('files_dir') . '/downloads/' . $path . $real_ver_file[$df_id][$i], $this->dlext_constants->get_value('files_dir') . '/downloads/' . $new_path . $real_ver_file[$df_id][$i]);
							}
						}
					}

					$this->db->sql_freeresult($result);

					$sql = 'UPDATE ' . $this->dlext_table_downloads . '
						SET cat = ' . (int) $new_cat_id . '
						WHERE cat = ' . (int) $cat_id;
					$this->db->sql_query($sql);

					$sql = 'UPDATE ' . $this->dlext_table_dl_stats . '
						SET cat_id = ' . (int) $new_cat_id . '
						WHERE cat_id = ' . (int) $cat_id;
					$this->db->sql_query($sql);

					$sql = 'UPDATE ' . $this->dlext_table_dl_comments . '
						SET cat_id = ' . (int) $new_cat_id . '
						WHERE cat_id = ' . (int) $cat_id;
					$this->db->sql_query($sql);
				}
				else
				{
					$sql = 'DELETE FROM ' . $this->dlext_table_dl_stats . '
						WHERE cat_id = ' . (int) $cat_id;
					$this->db->sql_query($sql);
				}

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_cat . '
					WHERE id = ' . (int) $cat_id;
				$this->db->sql_query($sql);

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_cat_traf . '
					WHERE cat_id = ' . (int) $cat_id;
				$this->db->sql_query($sql);

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_comments . '
					WHERE cat_id = ' . (int) $cat_id;
				$this->db->sql_query($sql);

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_auth . '
					WHERE cat_id = ' . (int) $cat_id;
				$this->db->sql_query($sql);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_DEL', false, [$log_cat_name]);

				// Purge the categories cache
				$this->cache->destroy('_dlext_cats');
				$this->cache->destroy('_dlext_auth');
				$this->cache->destroy('_dlext_file_p');
				$this->cache->destroy('_dlext_file_preset');
				$this->cache->destroy('_dlext_cat_counts');

				$message = $this->language->lang('DL_CATEGORY_REMOVED') . adm_back_link($this->u_action);

				trigger_error($message);
			}
			else
			{
				$cat_name = $index[$cat_id]['cat_name_nav'];

				$s_hidden_fields = [
					'cat_id'	=> $cat_id,
					'action'	=> 'delete',
					'parent'	=> $cat_parent,
					'type'		=> $idx_type,
				];

				$confirm_title = $this->language->lang('DL_CONFIRM_CAT_DELETE', $cat_name);

				$this->template->assign_vars([
					'DL_DELETE_CAT'		=> $this->dlext_constants::DL_CAT_DELETE_ONLY,
					'DL_DELETE_ALL'		=> $this->dlext_constants::DL_CAT_DELETE_FILES,
					'S_DL_SWITCH_CAT'	=> true,
				]);

				$select_cat_target = $this->dlext_extra->dl_dropdown(0, 0, $cat_id, 'auth_move');

				if (!empty($select_cat_target) && is_array($select_cat_target))
				{
					foreach (array_keys($select_cat_target) as $key)
					{
						$this->template->assign_block_vars('target_cat_select', [
							'DL_CAT_ID'			=> $select_cat_target[$key]['cat_id'],
							'DL_SEPERATOR'		=> $select_cat_target[$key]['seperator'],
							'DL_CAT_NAME'		=> $select_cat_target[$key]['cat_name'],
						]);
					}
				}

				confirm_box($this->dlext_constants::DL_FALSE, $confirm_title, build_hidden_fields($s_hidden_fields), '@oxpus_dlext/dl_confirm_body.html');
			}
		}
		else if ($action == 'delete_stats')
		{
			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type . '&amp;t=' . microtime();

			if (confirm_box($this->dlext_constants::DL_TRUE))
			{
				$sql = 'DELETE FROM ' . $this->dlext_table_dl_stats;

				if ($cat_id >= 1)
				{
					$sql .= ' WHERE cat_id = ' . (int) $cat_id;
				}

				$this->db->sql_query($sql);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_DEL_CAT_STATS', false, [$log_cat_name]);

				redirect($this->u_action);
			}
			else
			{
				if (isset($index[$cat_id]['cat_name']))
				{
					$cat_name = $index[$cat_id]['cat_name'];
					$cat_name = str_replace('&nbsp;&nbsp;|___&nbsp;', '', $cat_name);
				}

				$s_hidden_fields = [
					'cat_id' 	=> $cat_id,
					'action' 	=> 'delete_stats',
					'parent'	=> $cat_parent,
					'type'		=> $idx_type,
				];

				$confirm_title = ($cat_id == $this->dlext_constants::DL_NONE) ? $this->language->lang('DL_CONFIRM_ALL_STATS_DELETE') : $this->language->lang('DL_CONFIRM_CAT_STATS_DELETE', $cat_name);

				confirm_box($this->dlext_constants::DL_FALSE, $confirm_title, build_hidden_fields($s_hidden_fields), '@oxpus_dlext/dl_confirm_body.html');
			}
		}
		else if ($action == 'delete_comments')
		{
			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type . '&amp;t=' . microtime();

			if (confirm_box($this->dlext_constants::DL_TRUE))
			{
				if ($cat_id >= 1)
				{
					$sql_second = ' WHERE cat_id = ' . (int) $cat_id;
				}
				else
				{
					$sql_second = '';
				}

				$sql = 'SELECT dl_id FROM ' . $this->dlext_table_dl_comments;
				$sql .= $sql_second;
				$result = $this->db->sql_query($sql);

				$dl_ids = [];

				while ($row = $this->db->sql_fetchrow($result))
				{
					$dl_ids[] = $row['dl_id'];
				}

				$this->db->sql_freeresult($result);

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_comments;
				$sql .= $sql_second;
				$this->db->sql_query($sql);

				if (!empty($dl_ids))
				{
					$this->notification->delete_notifications([
						'oxpus.dlext.notification.type.capprove',
						'oxpus.dlext.notification.type.comments',
					], $dl_ids);
				}

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_DEL_CAT_COM', false, [$log_cat_name]);

				redirect($this->u_action);
			}
			else
			{
				if (isset($index[$cat_id]['cat_name']))
				{
					$cat_name = $index[$cat_id]['cat_name'];
					$cat_name = str_replace('&nbsp;&nbsp;|___&nbsp;', '', $cat_name);
				}

				$s_hidden_fields = [
					'cat_id'	=> $cat_id,
					'action'	=> 'delete_comments',
					'parent'	=> $cat_parent,
					'type'		=> $idx_type,
				];

				$confirm_title = ($cat_id == $this->dlext_constants::DL_NONE) ? $this->language->lang('DL_CONFIRM_ALL_COMMENTS_DELETE') : $this->language->lang('DL_CONFIRM_CAT_COMMENTS_DELETE', $cat_name);

				confirm_box($this->dlext_constants::DL_FALSE, $confirm_title, build_hidden_fields($s_hidden_fields), '@oxpus_dlext/dl_confirm_body.html');
			}
		}
		else if ($action == 'category_order')
		{
			$sql = 'SELECT sort FROM ' . $this->dlext_table_dl_cat . '
				WHERE id = ' . (int) $cat_id;
			$result = $this->db->sql_query($sql);
			$sql_move = $this->db->sql_fetchfield('sort');
			$this->db->sql_freeresult($result);

			if ($move == $this->dlext_constants::DL_MOVE_DOWN)
			{
				$sql_move += $this->dlext_constants::DL_MOVE_DOWN;
			}
			else if ($move == $this->dlext_constants::DL_MOVE_UP)
			{
				$sql_move += $this->dlext_constants::DL_MOVE_UP;
			}

			$sql = 'UPDATE ' . $this->dlext_table_dl_cat . '
				SET sort = ' . (int) $sql_move . '
				WHERE id = ' . (int) $cat_id;
			$this->db->sql_query($sql);

			$par_cat = $index[$cat_id]['parent'];

			$sql = 'SELECT id FROM ' . $this->dlext_table_dl_cat . '
				WHERE parent = ' . (int) $par_cat . '
				ORDER BY sort';
			$result = $this->db->sql_query($sql);

			$i = $this->dlext_constants::DL_SORT_RANGE;

			while ($row = $this->db->sql_fetchrow($result))
			{
				$sql_move = 'UPDATE ' . $this->dlext_table_dl_cat . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'sort' => $i
				]) . ' WHERE id = ' . (int) $row['id'];
				$this->db->sql_query($sql_move);

				$i += $this->dlext_constants::DL_SORT_RANGE;
			}

			$this->db->sql_freeresult($result);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_MOVE', false, [$log_cat_name]);

			// Purge the categories cache
			$this->cache->destroy('_dlext_cats');
			$this->cache->destroy('_dlext_auth');

			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type . '&amp;t=' . microtime();

			redirect($this->u_action);
		}
		else if ($action == 'asc_sort')
		{
			$sql = 'SELECT id FROM ' . $this->dlext_table_dl_cat . '
				WHERE parent = ' . (int) $cat_id . '
				ORDER BY cat_name ASC';
			$result = $this->db->sql_query($sql);

			$i = $this->dlext_constants::DL_SORT_RANGE;

			while ($row = $this->db->sql_fetchrow($result))
			{
				$sql_move = 'UPDATE ' . $this->dlext_table_dl_cat . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'sort' => $i
				]) . ' WHERE id = ' . (int) $row['id'];
				$this->db->sql_query($sql_move);

				$i += $this->dlext_constants::DL_SORT_RANGE;
			}

			$this->db->sql_freeresult($result);

			// Purge the categories cache
			$this->cache->destroy('_dlext_cats');
			$this->cache->destroy('_dlext_auth');

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_CAT_SORT_ASC');

			$this->u_action	.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type . '&amp;t=' . microtime();

			redirect($this->u_action);
		}
		else
		{
			$stats_cats = [];
			$comments_cats = [];

			$sql = 'SELECT cat_id, COUNT(dl_id) AS total_stats FROM ' . $this->dlext_table_dl_stats . '
				GROUP BY cat_id';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$stats_cats[$row['cat_id']] = $row['total_stats'];
			}

			$this->db->sql_freeresult($result);

			$sql = 'SELECT cat_id, COUNT(dl_id) AS total_comments FROM ' . $this->dlext_table_dl_comments . '
				GROUP BY cat_id';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$comments_cats[$row['cat_id']] = $row['total_comments'];
			}

			$this->db->sql_freeresult($result);

			if ($idx_type == 'c')
			{
				$this->u_action_idx		= $this->u_action . '&amp;type=';
			}
			else
			{
				$this->u_action_idx		= $this->u_action . '&amp;parent=' . $cat_parent . '&amp;type=';
			}

			$this->u_action_open	= $this->u_action . '&amp;parent=#CAT#&amp;type=' . $idx_type;
			$this->u_action			.= '&amp;parent=' . $cat_parent . '&amp;type=' . $idx_type;

			$u_delete_stats_all = '';
			$u_delete_comments_all = '';

			foreach (array_keys($index) as $key)
			{
				$cur_cat = $index[$key];
				$cat_id = $cur_cat['id'];

				if (($idx_type == 'c' && $cur_cat['parent'] == $cat_parent) || $idx_type == 'f')
				{
					$cat_name			= ($idx_type == 'c') ? $cur_cat['cat_name_nav'] : $cur_cat['cat_name'];
					$cat_desc			= $cur_cat['description'];
					$cat_uid			= $cur_cat['desc_uid'];
					$cat_bitfield		= $cur_cat['desc_bitfield'];
					$cat_flags			= $cur_cat['desc_flags'];
					$cat_description	= generate_text_for_display($cat_desc, $cat_uid, $cat_bitfield, $cat_flags);

					$cat_icon			= $cur_cat['cat_icon'];

					$cat_edit = $this->u_action . '&amp;action=edit&amp;cat_id=' . $cat_id;

					$cat_sub		= $this->dlext_main->get_sublevel_count($cat_id);
					$cat_sub_count	= $this->dlext_main->count_sublevel($cat_id);

					if ($cat_sub)
					{
						$cat_delete = '';
					}
					else
					{
						$cat_delete = $this->u_action . ' &amp;action=delete&amp;cat_id=' . $cat_id;
					}

					$dl_move_up = $this->u_action . '&amp;action=category_order&amp;move=' . $this->dlext_constants::DL_MOVE_UP . '&amp;cat_id=' . $cat_id;
					$dl_move_down = $this->u_action . '&amp;action=category_order&amp;move=' . $this->dlext_constants::DL_MOVE_DOWN . '&amp;cat_id=' . $cat_id;

					$cat_folder = 'images/icon_folder.gif';

					if ($cat_sub_count)
					{
						$cat_folder = 'images/icon_subfolder.gif';
					}

					if ($cat_sub_count > 1)
					{
						$l_sort_asc = $this->language->lang('DL_SUB_SORT_ASC');
						$dl_sort_asc = $this->u_action . '&amp;action=asc_sort&amp;cat_id=' . $cat_id;
					}
					else
					{
						$l_sort_asc = '';
						$dl_sort_asc = '';
					}

					$l_delete_stats = '';
					$l_delete_comments = '';
					$u_delete_stats = '';
					$u_delete_comments = '';

					if (isset($stats_cats[$cat_id]))
					{
						$l_delete_stats = $this->language->lang('DL_STATS_DELETE');
						$u_delete_stats = $this->u_action . '&amp;action=delete_stats&amp;cat_id=' . $cat_id;
					}

					if (isset($comments_cats[$cat_id]))
					{
						$l_delete_comments = $this->language->lang('DL_COMMENTS_DELETE');
						$u_delete_comments = $this->u_action . '&amp;action=delete_comments&amp;cat_id=' . $cat_id;
					}

					$this->template->assign_block_vars('categories', [
						'L_DL_DELETE_STATS'			=> $l_delete_stats,
						'L_DL_DELETE_COMMENTS'		=> $l_delete_comments,
						'L_DL_SORT_ASC'				=> $l_sort_asc,

						'DL_CAT_NAME'				=> $cat_name,
						'DL_CAT_DESCRIPTION'		=> $cat_description,
						'DL_CAT_FOLDER'				=> $cat_folder,
						'DL_CAT_ICON'				=> $cat_icon,

						'U_DL_CAT_EDIT'				=> $cat_edit,
						'U_DL_CAT_DELETE'			=> $cat_delete,
						'U_DL_CATEGORY_MOVE_UP'		=> $dl_move_up,
						'U_DL_CATEGORY_MOVE_DOWN'	=> $dl_move_down,
						'U_DL_CATEGORY_ASC_SORT'	=> $dl_sort_asc,
						'U_DL_DELETE_STATS'			=> $u_delete_stats,
						'U_DL_DELETE_COMMENTS'		=> $u_delete_comments,
						'U_DL_CAT_OPEN'				=> ($cat_sub_count && $idx_type == 'c') ? str_replace('#CAT#', $cat_id, $this->u_action_open) : '',
					]);
				}

				if (count($stats_cats))
				{
					$u_delete_stats_all = $this->u_action . '&amp;action=delete_stats&amp;cat_id=' . $this->dlext_constants::DL_NONE;
					$this->template->assign_var('S_DL_TOTAL_STATS', $this->dlext_constants::DL_TRUE);
				}

				if (count($comments_cats))
				{
					$u_delete_comments_all = $this->u_action . '&amp;action=delete_comments&amp;cat_id=' . $this->dlext_constants::DL_NONE;
					$this->template->assign_var('S_DL_TOTAL_COMMENTS', $this->dlext_constants::DL_TRUE);
				}
			}

			$tmp_nav = [];

			if ($cat_parent != 0)
			{
				$this->dlext_nav->nav($cat_parent, $tmp_nav);

				for ($i = count($tmp_nav); $i > 0; --$i)
				{
					$key = $i - 1;

					$this->template->assign_block_vars('dl_acp_cat_navi', [
						'DL_LINK'	=> str_replace('#CAT#', $tmp_nav[$key]['parent_id'], $this->u_action_open),
						'DL_NAME'	=> $tmp_nav[$key]['name'],
					]);
				}
			}

			$this->template->assign_vars([
				'DL_CAT_NAME'				=> $cat_name,

				'S_DL_CATEGORY_ACTION'		=> $this->u_action,
				'S_DL_IDX_TYPE'				=> (!empty($index)) ? $idx_type : '',
				'S_DL_SORT_MAIN'			=> ($cat_parent == 0) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
				'S_DL_CAT_PATH'				=> count($tmp_nav),

				'U_DL_SORT_LEVEL_ZERO'		=> $this->u_action . '&amp;action=asc_sort',
				'U_DL_DELETE_STATS_ALL'		=> $u_delete_stats_all,
				'U_DL_DELETE_COMMENTS_ALL'	=> $u_delete_comments_all,
				'U_DL_IDX_ACTION'			=> $this->u_action_idx,
			]);
		}
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
