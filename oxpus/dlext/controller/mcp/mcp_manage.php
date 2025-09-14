<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\mcp;

class mcp_manage
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames real_ver_file

	/* phpbb objects */
	protected $root_path;
	protected $php_ext;
	protected $notification;
	protected $pagination;
	protected $db;
	protected $config;
	protected $helper;
	protected $request;
	protected $template;
	protected $language;
	protected $dispatcher;
	protected $cache;
	protected $filesystem;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_counter;
	protected $dlext_extra;
	protected $dlext_files;
	protected $dlext_main;
	protected $dlext_topic;
	protected $dlext_status;
	protected $dlext_constants;
	protected $dlext_footer;

	protected $dlext_table_dl_comments;
	protected $dlext_table_dl_favorites;
	protected $dlext_table_dl_fields_data;
	protected $dlext_table_dl_images;
	protected $dlext_table_dl_notraf;
	protected $dlext_table_dl_ratings;
	protected $dlext_table_dl_reports;
	protected $dlext_table_dl_stats;
	protected $dlext_table_dl_ver_files;
	protected $dlext_table_dl_versions;
	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param string								$php_ext
	 * @param \phpbb\cache\service					$cache
	 * @param \phpbb\notification\manager 			$notification
	 * @param \phpbb\pagination						$pagination
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\event\dispatcher_interface		$dispatcher
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\counter				$dlext_counter
	 * @param \oxpus\dlext\core\extra				$dlext_extra
	 * @param \oxpus\dlext\core\files				$dlext_files
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\topic				$dlext_topic
	 * @param \oxpus\dlext\core\status				$dlext_status
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	 * @param string								$dlext_table_dl_comments
	 * @param string								$dlext_table_dl_favorites
	 * @param string								$dlext_table_dl_fields_data
	 * @param string								$dlext_table_dl_images
	 * @param string								$dlext_table_dl_notraf
	 * @param string								$dlext_table_dl_ratings
	 * @param string								$dlext_table_dl_reports
	 * @param string								$dlext_table_dl_stats
	 * @param string								$dlext_table_dl_ver_files
	 * @param string								$dlext_table_dl_versions
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		$root_path,
		$php_ext,
		\phpbb\cache\service $cache,
		\phpbb\notification\manager $notification,
		\phpbb\pagination $pagination,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\language\language $language,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\counter $dlext_counter,
		\oxpus\dlext\core\extra $dlext_extra,
		\oxpus\dlext\core\files $dlext_files,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\topic $dlext_topic,
		\oxpus\dlext\core\status $dlext_status,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		$dlext_table_dl_comments,
		$dlext_table_dl_favorites,
		$dlext_table_dl_fields_data,
		$dlext_table_dl_images,
		$dlext_table_dl_notraf,
		$dlext_table_dl_ratings,
		$dlext_table_dl_reports,
		$dlext_table_dl_stats,
		$dlext_table_dl_ver_files,
		$dlext_table_dl_versions,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->cache					= $cache;
		$this->notification 			= $notification;
		$this->pagination 				= $pagination;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->template 				= $template;
		$this->language					= $language;
		$this->dispatcher				= $dispatcher;
		$this->filesystem				= $filesystem;

		$this->dlext_table_dl_comments		= $dlext_table_dl_comments;
		$this->dlext_table_dl_favorites		= $dlext_table_dl_favorites;
		$this->dlext_table_dl_fields_data	= $dlext_table_dl_fields_data;
		$this->dlext_table_dl_images		= $dlext_table_dl_images;
		$this->dlext_table_dl_notraf		= $dlext_table_dl_notraf;
		$this->dlext_table_dl_ratings		= $dlext_table_dl_ratings;
		$this->dlext_table_dl_reports		= $dlext_table_dl_reports;
		$this->dlext_table_dl_stats			= $dlext_table_dl_stats;
		$this->dlext_table_dl_ver_files		= $dlext_table_dl_ver_files;
		$this->dlext_table_dl_versions		= $dlext_table_dl_versions;
		$this->dlext_table_downloads		= $dlext_table_downloads;
		$this->dlext_table_dl_cat			= $dlext_table_dl_cat;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_counter			= $dlext_counter;
		$this->dlext_extra				= $dlext_extra;
		$this->dlext_files				= $dlext_files;
		$this->dlext_main				= $dlext_main;
		$this->dlext_topic				= $dlext_topic;
		$this->dlext_status				= $dlext_status;
		$this->dlext_constants			= $dlext_constants;
		$this->dlext_footer				= $dlext_footer;
	}

	public function handle()
	{
		$access_cat = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_MOD);

		if (empty($access_cat))
		{
			trigger_error($this->language->lang('DL_NO_PERMISSION'));
		}

		$this->template->assign_vars([
			'DL_MCP_TAB_MODULE'		=> $this->language->lang('DL_MANAGE'),

			'S_DL_MCP'				=> $this->dlext_constants::DL_TRUE,
			'S_DL_MCP_TAB_MANAGE'	=> $this->dlext_constants::DL_TRUE,
		]);

		$index		= $this->dlext_main->full_index();

		$df_id		= $this->request->variable('df_id', 0);
		$dlo_id		= $this->request->variable('dlo_id', [0 => 0]);
		$cancel		= $this->request->variable('cancel', '');
		$delete		= $this->request->variable('delete', '');
		$view		= $this->request->variable('view', '');
		$action		= $this->request->variable('action', '');
		$fmove		= $this->request->variable('fmove', '');
		$lock		= $this->request->variable('lock', '');
		$sort		= $this->request->variable('sort', '');
		$new_cat	= $this->request->variable('new_cat', 0);
		$cat_id		= $this->request->variable('cat_id', 0);
		$start		= $this->request->variable('start', 0);
		$del_file	= $this->request->variable('del_file', 0);
		$modcp		= $this->request->variable('modcp', 0);

		$action		= ($delete) ? 'delete' : $action;
		$action		= ($lock) ? 'lock' : $action;
		$action		= ($cancel) ? '' : $action;

		if (($action == 'delete' || $delete) && $cancel && $modcp == $this->dlext_constants::DL_RETURN_MCP_APPROVE)
		{
			redirect($this->helper->route('oxpus_dlext_mcp_approve'));
		}

		if ($view == 'toolbox')
		{
			if (isset($index[$cat_id]['total']) && $index[$cat_id]['total'] && $cat_id)
			{
				if ($action == 'sort')
				{
					$sort = $this->dlext_constants::DL_TRUE;
					$action = '';
				}

				if ($action == 'move' && $new_cat && $cat_id)
				{
					if (!empty($dlo_id))
					{
						$new_path = $index[$new_cat]['cat_path'];

						$sql = 'SELECT dl_id, ver_real_file FROM ' . $this->dlext_table_dl_versions . '
							WHERE ' . $this->db->sql_in_set('dl_id', $dlo_id);
						$result = $this->db->sql_query($sql);

						while ($row = $this->db->sql_fetchrow($result))
						{
							$real_ver_file[$row['dl_id']][] = $row['ver_real_file'];
						}

						$this->db->sql_freeresult($result);

						for ($i = 0; $i < count($dlo_id); ++$i)
						{
							$df_id = intval($dlo_id[$i]);

							$sql = 'SELECT c.path, d.real_file FROM ' . $this->dlext_table_downloads . ' d, ' . $this->dlext_table_dl_cat . ' c
								WHERE d.cat = c.id
									AND d.id = ' . (int) $df_id . '
									AND c.id = ' . (int) $cat_id . '
									AND d.extern = 0';
							$result = $this->db->sql_query($sql);
							$row = $this->db->sql_fetchrow($result);

							if ($this->db->sql_affectedrows())
							{
								$old_path = $row['path'];
								$real_file = $row['real_file'];

								if ($new_path != $old_path)
								{
									$this->filesystem->rename($this->dlext_constants->get_value('files_dir') . '/downloads/' . $old_path . $real_file, $this->dlext_constants->get_value('files_dir') . '/downloads/' . $new_path . $real_file);

									if (isset($real_ver_file[$df_id]))
									{
										for ($j = 0; $j < count($real_ver_file[$df_id]); ++$j)
										{
											$this->filesystem->rename($this->dlext_constants->get_value('files_dir') . '/downloads/' . $old_path . $real_ver_file[$df_id][$j], $this->dlext_constants->get_value('files_dir') . '/downloads/' . $new_path . $real_ver_file[$df_id][$j]);
										}
									}
								}
							}

							$this->db->sql_freeresult($result);
						}

						$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'cat' => $new_cat
						]) . ' WHERE ' . $this->db->sql_in_set('id', $dlo_id) . ' AND cat = ' . (int) $cat_id;
						$this->db->sql_query($sql);

						$sql = 'UPDATE ' . $this->dlext_table_dl_stats . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'cat_id' => $new_cat
						]) . ' WHERE ' . $this->db->sql_in_set('id', $dlo_id) . ' AND cat_id = ' . (int) $cat_id;
						$this->db->sql_query($sql);

						$sql = 'UPDATE ' . $this->dlext_table_dl_comments . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'cat_id' => $new_cat
						]) . ' WHERE ' . $this->db->sql_in_set('id', $dlo_id) . ' AND cat_id = ' . (int) $cat_id;
						$this->db->sql_query($sql);

						// Purge the files cache
						$this->cache->destroy('_dlext_cat_counts');
						$this->cache->destroy('_dlext_file_p');
						$this->cache->destroy('_dlext_file_preset');
					}

					$fmove = '';
					$action = '';
				}

				if ($action == 'lock')
				{
					if (!empty($dlo_id))
					{
						$sql = 'SELECT id, cat, description, long_desc
								FROM ' . $this->dlext_table_downloads . '
								WHERE ' . $this->db->sql_in_set('id', $dlo_id);
						$result = $this->db->sql_query($sql);

						while ($row = $this->db->sql_fetchrow($result))
						{
							$df_id 			= $row['id'];
							$cat_name		= $index[$row['cat']]['cat_name_nav'];
							$description	= $row['description'];
							$long_desc		= $row['long_desc'];

							$sql_update = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
								'approve' => 0
							]) . ' WHERE id = ' . (int) $df_id;
							$this->db->sql_query($sql_update);

							$notification_data = [
								'user_ids'			=> $this->dlext_auth->dl_auth_users($cat_id, 'auth_mod'),
								'description'		=> $description,
								'long_desc'			=> $long_desc,
								'df_id'				=> $df_id,
								'cat_name'			=> $cat_name,
							];

							$this->notification->add_notifications('oxpus.dlext.notification.type.approve', $notification_data);
						}

						$this->db->sql_freeresult($result);
					}

					$fmove = '';
					$action = '';
				}

				if ($action == 'assign')
				{
					$achanged	= $this->request->variable('assign_changed', 0);
					$username	= $this->request->variable('username', '', $this->dlext_constants::DL_TRUE);

					if (!empty($dlo_id) && $username)
					{
						$sql = 'SELECT user_id FROM ' . USERS_TABLE . "
								WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
						$result = $this->db->sql_query($sql);
						$user_id = (int) $this->db->sql_fetchfield('user_id');
						$this->db->sql_freeresult($result);

						if ($user_id)
						{
							$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
								'add_user' => $user_id
							]) . ' WHERE ' . $this->db->sql_in_set('id', $dlo_id);
							$this->db->sql_query($sql);

							if ($achanged)
							{
								$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
									'change_user' => $user_id
								]) . ' WHERE ' . $this->db->sql_in_set('id', $dlo_id);
								$this->db->sql_query($sql);
							}
						}
					}

					$fmove = '';
					$action = '';
				}

				if ($action == 'delete' && !empty($dlo_id))
				{
					if (confirm_box($this->dlext_constants::DL_TRUE))
					{
						if ($del_file)
						{
							$sql = 'SELECT ver_id, dl_id, ver_real_file FROM ' . $this->dlext_table_dl_versions . '
								WHERE ' . $this->db->sql_in_set('dl_id', $dlo_id);
							$result = $this->db->sql_query($sql);

							while ($row = $this->db->sql_fetchrow($result))
							{
								$real_ver_file[$row['dl_id']][] = $row['ver_real_file'];
							}

							$this->db->sql_freeresult($result);
						}

						$sql = 'SELECT c.path, d.cat, d.real_file, d.dl_topic, d.id AS df_id FROM ' . $this->dlext_table_dl_cat . ' c, ' . $this->dlext_table_downloads . ' d
							WHERE c.id = d.cat
								AND ' . $this->db->sql_in_set('d.id', $dlo_id);
						$result = $this->db->sql_query($sql);

						$dl_topics	= [];
						$dl_t_ids	= [];

						while ($row = $this->db->sql_fetchrow($result))
						{
							$cat_id = $row['cat'];

							$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);

							if (!$this->dlext_auth->user_admin() && isset($index[$cat_id]['auth_mod']) && !$index[$cat_id]['auth_mod'] && !$cat_auth['auth_mod'])
							{
								trigger_error($this->language->lang('DL_NO_PERMISSION'));
							}

							$path		= $row['path'];
							$real_file	= $row['real_file'];
							$df_id		= $row['df_id'];

							if ($del_file)
							{
								if ($path && $real_file)
								{
									$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $path . $real_file);
								}

								if (isset($real_ver_file[$df_id]))
								{
									for ($i = 0; $i < count($real_ver_file[$df_id]); ++$i)
									{
										if ($path && $real_ver_file[$df_id][$i])
										{
											$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/downloads/' . $path . $real_ver_file[$df_id][$i]);
										}
									}
								}

								$sql_real = 'SELECT file_type, real_name FROM ' . $this->dlext_table_dl_ver_files . '
									WHERE ' . $this->db->sql_in_set('dl_id', $dlo_id);
								$result_real = $this->db->sql_query($sql_real);

								while ($row_real = $this->db->sql_fetchrow($result_real))
								{
									if ($row_real['real_name'])
									{
										switch ($row_real['file_type'])
										{
											case $this->dlext_constants::DL_FILE_TYPE_IMAGE:
												$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/version/images/' . $row_real['real_name']);
												break;
											default:
												$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/version/files/' . $row_real['real_name']);
										}
									}
								}

								$this->db->sql_freeresult($result_real);

								$sql_image = 'SELECT img_name FROM ' . $this->dlext_table_dl_images . '
										WHERE ' . $this->db->sql_in_set('dl_id', $dlo_id);
								$result_image = $this->db->sql_query($sql_image);

								while ($row_image = $this->db->sql_fetchrow($result_image))
								{
									if ($row_image['img_name'])
									{
										$this->filesystem->remove($this->dlext_constants->get_value('files_dir') . '/thumbs/' . $row_image['img_name']);
									}
								}

								$this->db->sql_freeresult($result_image);
							}

							if ($row['dl_topic'])
							{
								$dl_topics[]	= $row['dl_topic'];
								$dl_t_ids[]		= $df_id;
							}
						}

						$this->db->sql_freeresult($result);

						$topic_drop_mode = $this->request->variable('topic_drop_mode', 'drop');

						$this->dlext_topic->delete_dl_topic($dl_topics, $topic_drop_mode, $dl_t_ids);

						$sql = 'DELETE FROM ' . $this->dlext_table_downloads . '
							WHERE ' . $this->db->sql_in_set('id', $dlo_id) . '
								AND cat = ' . (int) $cat_id;
						$this->db->sql_query($sql);

						$sql = 'DELETE FROM ' . $this->dlext_table_dl_stats . '
							WHERE ' . $this->db->sql_in_set('id', $dlo_id) . '
								AND cat_id = ' . (int) $cat_id;
						$this->db->sql_query($sql);

						$sql = 'DELETE FROM ' . $this->dlext_table_dl_comments . '
							WHERE ' . $this->db->sql_in_set('id', $dlo_id) . '
								AND cat_id = ' . (int) $cat_id;
						$this->db->sql_query($sql);

						$sql = 'DELETE FROM ' . $this->dlext_table_dl_notraf . '
							WHERE ' . $this->db->sql_in_set('dl_id', $dlo_id);
						$this->db->sql_query($sql);

						$sql = 'DELETE FROM ' . $this->dlext_table_dl_versions . '
							WHERE ' . $this->db->sql_in_set('dl_id', $dlo_id);
						$this->db->sql_query($sql);

						$sql = 'DELETE FROM ' . $this->dlext_table_dl_ver_files . '
							WHERE ' . $this->db->sql_in_set('dl_id', $dlo_id);
						$this->db->sql_query($sql);

						$sql = 'DELETE FROM ' . $this->dlext_table_dl_fields_data . '
							WHERE ' . $this->db->sql_in_set('df_id', $dlo_id);
						$this->db->sql_query($sql);

						$sql = 'DELETE FROM ' . $this->dlext_table_dl_ratings . '
							WHERE ' . $this->db->sql_in_set('dl_id', $dlo_id);
						$this->db->sql_query($sql);

						$sql = 'DELETE FROM ' . $this->dlext_table_dl_favorites . '
							WHERE ' . $this->db->sql_in_set('fav_dl_id', $dlo_id);
						$this->db->sql_query($sql);

						$sql = 'DELETE FROM ' . $this->dlext_table_dl_images . '
							WHERE ' . $this->db->sql_in_set('dl_id', $dlo_id);
						$this->db->sql_query($sql);

						$sql = 'DELETE FROM ' . $this->dlext_table_dl_reports . '
							WHERE ' . $this->db->sql_in_set('dl_id', $dlo_id);
						$this->db->sql_query($sql);

						/**
						 * Workflow after delete download
						 *
						 * @event oxpus.dlext.mcp_manage_delete_downloads_after
						 * @var array	dl_ids		download ID's
						 * @var int		cat_id		download category ID
						 * @since 8.1.0-RC2
						 */
						$dl_ids = $dlo_id;
						$vars = array(
							'dl_ids',
							'cat_id',
						);
						extract($this->dispatcher->trigger_event('oxpus.dlext.mcp_manage_delete_downloads_after', compact($vars)));

						$dlo_id = $dl_ids;

						$this->notification->delete_notifications([
							'oxpus.dlext.notification.type.approve',
							'oxpus.dlext.notification.type.broken',
							'oxpus.dlext.notification.type.dlext',
							'oxpus.dlext.notification.type.update',
							'oxpus.dlext.notification.type.capprove',
							'oxpus.dlext.notification.type.comments',
						], $dlo_id);

						// Purge the files cache
						$this->cache->destroy('_dlext_cat_counts');
						$this->cache->destroy('_dlext_file_p');
						$this->cache->destroy('_dlext_file_preset');
					}
					else
					{
						$s_hidden_fields = ['action' => 'delete', 'cat_id' => $cat_id];

						if (count($dlo_id) == 1)
						{
							$dl_file						= $this->dlext_files->all_files(0, [], [], $dlo_id[0], 1, ['description']);
							$delete_confirm_text			= $this->language->lang('DL_CONFIRM_DELETE_SINGLE_FILE', $dl_file['description']);
							$s_hidden_fields['dlo_id[0]']	= $dlo_id[0];
						}
						else
						{
							$delete_confirm_text	= $this->language->lang('DL_CONFIRM_DELETE_MULTIPLE_FILES', count($dlo_id));

							$i = 0;

							foreach ($dlo_id as $value)
							{
								$s_hidden_fields['dlo_id[' . $i . ']'] = $value;

								++$i;
							}
						}

						$this->template->assign_vars([
							'S_DL_DELETE_FILES_CONFIRM'	=> $this->dlext_constants::DL_TRUE,
							'S_DL_DELETE_TOPIC_CONFIRM'	=> $this->dlext_constants::DL_TRUE,
						]);

						confirm_box($this->dlext_constants::DL_FALSE, $delete_confirm_text, build_hidden_fields($s_hidden_fields), '@oxpus_dlext/helpers/dl_confirm_body.html');
					}

					$fmove = '';
				}

				if ($fmove && $this->dlext_auth->user_admin())
				{
					if ($fmove == 'ABC')
					{
						$sql = 'SELECT id FROM ' . $this->dlext_table_downloads . '
							WHERE cat = ' . (int) $cat_id . '
							ORDER BY description ASC';
						$result = $this->db->sql_query($sql);
					}
					else
					{
						$sql = 'SELECT sort FROM ' . $this->dlext_table_downloads . '
							WHERE id = ' . (int) $df_id;
						$result = $this->db->sql_query($sql);
						$sql_move = $this->db->sql_fetchfield('sort');
						$this->db->sql_freeresult($result);

						if ($fmove == $this->dlext_constants::DL_MOVE_DOWN)
						{
							$sql_move += $this->dlext_constants::DL_MOVE_DOWN;
						}
						else if ($fmove == $this->dlext_constants::DL_MOVE_UP)
						{
							$sql_move += $this->dlext_constants::DL_MOVE_UP;
						}

						$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'sort' => $sql_move
						]) . ' WHERE id = ' . (int) $df_id;
						$this->db->sql_query($sql);

						$sql = 'SELECT id FROM ' . $this->dlext_table_downloads . '
							WHERE cat = ' . (int) $cat_id . '
							ORDER BY sort ASC';
						$result = $this->db->sql_query($sql);
					}

					$i = $this->dlext_constants::DL_SORT_RANGE;

					while ($row = $this->db->sql_fetchrow($result))
					{
						$sql_sort = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
							'sort' => $i
						]) . ' WHERE id = ' . (int) $row['id'];
						$this->db->sql_query($sql_sort);
						$i += $this->dlext_constants::DL_SORT_RANGE;
					}

					$this->db->sql_freeresult($result);
				}

				$total_downloads = $index[$cat_id]['total'];

				if ($sort && $this->dlext_auth->user_admin())
				{
					$per_page	= $total_downloads;
					$start		= 0;

					$this->template->assign_var('S_DL_MODCP_BUTTON', $this->dlext_constants::DL_TRUE);
				}
				else
				{
					$per_page = $this->config['dl_links_per_page'];

					if ($this->dlext_auth->user_admin())
					{
						$this->template->assign_var('S_DL_ORDER_BUTTON', $this->dlext_constants::DL_TRUE);
					}
				}

				if ($this->dlext_auth->user_admin())
				{
					$this->template->assign_var('S_DL_SORT_ASC', $this->dlext_constants::DL_TRUE);
				}

				$sql = 'SELECT d.id, d.description, d.desc_uid, d.desc_bitfield, d.desc_flags, d.broken, d.add_user, u.username, u.user_colour  FROM ' . $this->dlext_table_downloads . ' d
					LEFT JOIN ' . USERS_TABLE . ' u ON (u.user_id = d.add_user)
					WHERE d.approve = 1
						AND d.cat = ' . (int) $cat_id . '
					ORDER BY d.cat, d.sort';
				$result = $this->db->sql_query_limit($sql, $per_page, $start);
				$max_downloads = $this->db->sql_affectedrows();

				while ($row = $this->db->sql_fetchrow($result))
				{
					$description	= $row['description'];
					$desc_uid		= $row['desc_uid'];
					$desc_bitfield	= $row['desc_bitfield'];
					$desc_flags		= $row['desc_flags'];
					$username		= $row['username'];
					$user_id		= $row['add_user'];
					$user_colour	= $row['user_colour'];
					$broken			= $row['broken'];

					$description	= generate_text_for_display($description, $desc_uid, $desc_bitfield, $desc_flags);

					$file_id		= $row['id'];

					$mini_icon		= $this->dlext_status->mini_status_file($cat_id, $file_id);

					$this->template->assign_block_vars('manage_row', [
						'DL_FILE_ID'		=> $file_id,
						'DL_MINI_ICON'		=> $mini_icon,
						'DL_DESCRIPTION'	=> $description,
						'DL_USERNAME'		=> get_username_string('full', $user_id, $username, $user_colour),
						'DL_BROKEN'			=> $broken,

						'U_DL_UP'			=> ($this->dlext_auth->user_admin()) ? $this->helper->route('oxpus_dlext_mcp_manage', ['view' => 'toolbox', 'fmove' => $this->dlext_constants::DL_MOVE_UP, 'sort' => $this->dlext_constants::DL_TRUE, 'df_id' => $file_id, 'cat_id' => $cat_id]) : '',
						'U_DL_DOWN'			=> ($this->dlext_auth->user_admin()) ? $this->helper->route('oxpus_dlext_mcp_manage', ['view' => 'toolbox', 'fmove' => $this->dlext_constants::DL_MOVE_DOWN, 'sort' => $this->dlext_constants::DL_TRUE, 'df_id' => $file_id, 'cat_id' => $cat_id]) : '',
						'U_DL_EDIT'			=> $this->helper->route('oxpus_dlext_mcp_edit', ['df_id' => $file_id, 'cat_id' => $cat_id]),
						'U_DL_DOWNLOAD'		=> $this->helper->route('oxpus_dlext_details', ['df_id' => $file_id]),
					]);
				}
				$this->db->sql_freeresult($result);

				if (!isset($file_id))
				{
					$file_id = '';
				}

				$s_hidden_fields = [
					'cat_id'	=> $cat_id,
					'start'		=> $start
				];

				if ($total_downloads > $per_page)
				{
					$this->pagination->generate_template_pagination(
						$this->helper->route('oxpus_dlext_mcp_manage', ['view' => 'toolbox', 'cat_id' => $cat_id]),
						'pagination',
						'start',
						$total_downloads,
						$this->config['dl_links_per_page'],
						$start
					);

					$this->template->assign_vars([
						'DL_PAGE_NUMBER'	=> $this->pagination->on_page($total_downloads, $per_page, $start),
						'DL_TOTAL_DL'		=> $this->language->lang('DL_VIEW_DOWNLOADS_NUM', $total_downloads),
					]);
				}

				$this->template->assign_vars([
					'DL_ABC'				=> ($this->dlext_auth->user_admin()) ? $this->language->lang('ASCENDING') : '',
					'DL_SORT'				=> $sort,
					'DL_MAX_DOWNLOADS'		=> $max_downloads,

					'U_DL_FIND_USERNAME'	=> append_sid($this->root_path . 'memberlist.' . $this->php_ext, 'mode=searchuser&amp;form=select_user&amp;field=username&amp;select_single=1'),
					'U_DL_SORT_ASC'			=> ($this->dlext_auth->user_admin()) ? $this->helper->route('oxpus_dlext_mcp_manage', ['view' => 'toolbox', 'fmove' => 'ABC', 'sort' => (($sort) ? 1 : ''), 'df_id' => $file_id,  'cat_id' => $cat_id]) : '',
					'S_DL_MODCP_ACTION'		=> $this->helper->route('oxpus_dlext_mcp_manage', ['view' => 'toolbox']),
					'S_DL_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
				]);

				$s_cat_select_move = $this->dlext_extra->dl_dropdown(0, 0, $cat_id, 'auth_mod');

				if (!empty($s_cat_select_move) && is_array($s_cat_select_move))
				{
					foreach (array_keys($s_cat_select_move) as $key)
					{
						$this->template->assign_block_vars('mcp_cat_move', [
							'DL_CAT_ID'			=> $s_cat_select_move[$key]['cat_id'],
							'DL_SELECTED'		=> $s_cat_select_move[$key]['selected'],
							'DL_SEPERATOR'		=> $s_cat_select_move[$key]['seperator'],
							'DL_CAT_NAME'		=> $s_cat_select_move[$key]['cat_name'],
						]);
					}
				}
			}

			$s_cat_select = [];
			$this->dlext_extra->dl_dropdown(0, 0, $cat_id, 'auth_mod', $this->dlext_constants::DL_NONE, $s_cat_select);

			$this->template->assign_vars([
				'DL_CAT_NONE'			=> $this->dlext_constants::DL_NONE,

				'S_DL_TOTAL'			=> (isset($index[$cat_id]['total'])) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
				'S_DL_SELECT_MCP_CAT'	=> count($s_cat_select),
				'S_DL_MCP_TOOLBOX'		=> $this->dlext_constants::DL_TRUE,
				'S_DL_FORM_ACTION'		=> $this->helper->route('oxpus_dlext_mcp_manage', ['view' => 'toolbox']),
			]);

			if (!empty($s_cat_select) && is_array($s_cat_select))
			{
				foreach (array_keys($s_cat_select) as $key)
				{
					$this->template->assign_block_vars('mcp_cat_select', [
						'DL_CAT_ID'			=> $s_cat_select[$key]['cat_id'],
						'DL_SELECTED'		=> $s_cat_select[$key]['selected'],
						'DL_SEPERATOR'		=> $s_cat_select[$key]['seperator'],
						'DL_CAT_NAME'		=> $s_cat_select[$key]['cat_name'],
					]);
				}
			}
		}
		else
		{
			/*
			* check and create link if we must approve downloads
			*/
			$broken_ary = $this->dlext_counter->count_dl_broken();
			$total_broken = $broken_ary['total'];
			$broken_id = $broken_ary['df_id'];

			if ($total_broken)
			{
				$broken_url = ($total_broken == 1) ? $this->helper->route('oxpus_dlext_details', ['df_id' => $broken_id]) : $this->helper->route('oxpus_dlext_mcp_broken');
				$this->template->assign_var('U_DL_BROKEN_DOWNLOADS', $broken_url);
			}

			/*
			* check and create link if we must approve downloads
			*/
			if ($this->dlext_counter->count_dl_approve())
			{
				$this->template->assign_var('U_DL_APPROVE_DOWNLOADS', $this->helper->route('oxpus_dlext_mcp_approve'));
			}

			/*
			* check and create link if we must approve comments
			*/
			if ($this->dlext_counter->count_comments_approve())
			{
				$this->template->assign_var('U_DL_APPROVE_COMMENTS', $this->helper->route('oxpus_dlext_mcp_capprove'));
			}

			$this->template->assign_vars([
				'S_DL_MCP_OVERVIEW'	=> $this->dlext_constants::DL_TRUE,
			]);
		}

		// Apply module pages
		$this->template->assign_vars([
			'U_DL_MCP_MANAGE_OVERVIEW'	=> $this->helper->route('oxpus_dlext_mcp_manage'),
			'U_DL_MCP_MANAGE_TOOLBOX'	=> $this->helper->route('oxpus_dlext_mcp_manage', ['view' => 'toolbox']),
		]);

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter('mcp');
		$this->dlext_footer->handle();

		return $this->helper->render('@oxpus_dlext/mcp/dl_mcp_manage.html', $this->language->lang('MCP'));
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
