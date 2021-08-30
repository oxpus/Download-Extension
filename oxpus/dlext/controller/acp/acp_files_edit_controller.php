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
class acp_files_edit_controller implements acp_files_edit_interface
{
	/* phpbb objects */
	protected $request;
	protected $template;

	/* extension owned objects */
	public $u_action;

	protected $dlext_download;
	protected $dlext_main;
	protected $dlext_constants;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \oxpus\dlext\core\download			$dlext_download
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 */
	public function __construct(
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\oxpus\dlext\core\download $dlext_download,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\constants $dlext_constants
	)
	{
		$this->request					= $request;
		$this->template					= $template;

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
				$this->dlext_download->dl_delete_version('acp', $cat_id, $df_id, $this->u_action);
			}
			else
			{
				$this->dlext_download->dl_submit_download('acp', $df_id, $this->dlext_constants::DL_FALSE, $this->u_action);
			}
		}
	}
}
