<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\acp;

/**
* @package acp
*/
class acp_files_edit_controller implements acp_files_edit_interface
{
	/* phpbb objects */
	protected $request;
	protected $template;
	protected $db;
	protected $filesystem;

	/* extension owned objects */
	protected $u_action;

	protected $dlext_download;
	protected $dlext_main;
	protected $dlext_constants;

	protected $dlext_table_dl_ver_files;
	protected $dlext_table_dl_versions;
	protected $dlext_table_downloads;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\filesystem\filesystem			$filesystem
	 * @param \oxpus\dlext\core\download			$dlext_download
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_ver_files
	 * @param string								$dlext_table_dl_versions
	 * @param string								$dlext_table_downloads
	 */
	public function __construct(
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\download $dlext_download,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_ver_files,
		$dlext_table_dl_versions,
		$dlext_table_dl_cat
	)
	{
		$this->db						= $db;
		$this->request					= $request;
		$this->template					= $template;
		$this->filesystem				= $filesystem;

		$this->dlext_table_dl_ver_files		= $dlext_table_dl_ver_files;
		$this->dlext_table_dl_versions		= $dlext_table_dl_versions;
		$this->dlext_table_dl_cat			= $dlext_table_dl_cat;

		$this->dlext_download			= $dlext_download;
		$this->dlext_main				= $dlext_main;
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
		$cat_id				= $this->request->variable('cat_id', 0);
		$df_id				= $this->request->variable('df_id', 0);
		$file_option		= $this->request->variable('file_ver_opt', 0);

		$index = $this->dlext_main->full_index($cat_id);

		if (empty($index))
		{
			redirect($this->u_action . '&amp;mode=categories');
		}

		if ($cancel)
		{
			$action = '';
		}

		if ($action == 'edit' || $action == 'add')
		{
			$this->dlext_download->dl_edit_download('acp', $df_id, 0, $this->u_action);

			$this->template->assign_var('S_DL_FILES_EDIT', $this->dlext_constants::DL_TRUE);
		}
		else if ($action == 'save')
		{
			if ($file_option == $this->dlext_constants::DL_VERSION_DELETE)
			{
				$del_file		= $this->request->variable('del_file', 0);
				$file_ver_del	= $this->request->variable('file_ver_del', [0]);

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
							if ($row['ver_real_file'])
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

					redirect($this->u_action . "&amp;cat_id=$cat_id");
				}
				else
				{
					$this->template->assign_var('S_DL_DELETE_FILES_CONFIRM', $this->dlext_constants::DL_TRUE);

					$s_hidden_fields = [
						'view'			=> 'modcp',
						'action'		=> 'save',
						'cat_id'		=> $cat_id,
						'df_id'			=> $df_id,
						'file_ver_opt'	=> 3,
					];

					for ($i = 0; $i < count($file_ver_del); ++$i)
					{
						$s_hidden_fields += ['file_ver_del[' . $i . ']' => $file_ver_del[$i]];
					}

					confirm_box($this->dlext_constants::DL_FALSE, 'DL_CONFIRM_DEL_VERSIONS', build_hidden_fields($s_hidden_fields), '@oxpus_dlext/dl_confirm_body.html');
				}
			}
			else
			{
				$this->dlext_download->dl_submit_download('acp', $df_id, $this->dlext_constants::DL_FALSE, $this->u_action);
			}
		}
	}
}
