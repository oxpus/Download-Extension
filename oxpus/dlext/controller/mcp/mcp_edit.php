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
	protected $db;
	protected $config;
	protected $helper;
	protected $request;
	protected $template;
	protected $user;
	protected $language;
	protected $filesystem;

	/* extension owned objects */
	protected $dlext_download;
	protected $dlext_main;
	protected $dlext_constants;
	protected $dlext_footer;

	protected $dlext_table_dl_ver_files;
	protected $dlext_table_dl_versions;
	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface		$db
	* @param \phpbb\config\config					$config
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\request\request 				$request
	* @param \phpbb\template\template				$template
	* @param \phpbb\user							$user
	* @param \phpbb\language\language				$language
	* @param \phpbb\filesystem\filesystem			$filesystem
	* @param \oxpus\dlext\core\download				$dlext_download
	* @param \oxpus\dlext\core\main					$dlext_main
	* @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	* @param \oxpus\dlext\core\helpers\footer		$dlext_footer
	* @param string									$dlext_table_dl_ver_files
	* @param string									$dlext_table_dl_versions
	* @param string									$dlext_table_downloads
	* @param string									$dlext_table_dl_cat
	*/
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\filesystem\filesystem $filesystem,
		\oxpus\dlext\core\download $dlext_download,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		\oxpus\dlext\core\helpers\footer $dlext_footer,
		$dlext_table_dl_ver_files,
		$dlext_table_dl_versions,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->db 						= $db;
		$this->config 					= $config;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->language					= $language;
		$this->filesystem				= $filesystem;

		$this->dlext_table_dl_ver_files		= $dlext_table_dl_ver_files;
		$this->dlext_table_dl_versions		= $dlext_table_dl_versions;
		$this->dlext_table_downloads		= $dlext_table_downloads;
		$this->dlext_table_dl_cat			= $dlext_table_dl_cat;

		$this->dlext_download			= $dlext_download;
		$this->dlext_main				= $dlext_main;
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

		/*
		* And now the different work from here
		*/
		if ($action == 'save' && $submit)
		{
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
				$this->dlext_download->dl_submit_download('mcp', $df_id, $own_edit);
			}
		}

		$this->dlext_download->dl_edit_download('mcp', $df_id, $own_edit);

		/*
		* include the mod footer
		*/
		$this->dlext_footer->set_parameter('mcp');
		$this->dlext_footer->handle();

		return $this->helper->render('@oxpus_dlext/mcp/dl_mcp_edit.html', $this->language->lang('MCP'));
	}
}
