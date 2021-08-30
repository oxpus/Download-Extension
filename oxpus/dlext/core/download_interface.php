<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

/**
 * Interface for files_controller
 *
 */
interface download_interface
{
	/**
	 * Save a new or edited download in the database and store uploaded files into predefined folders
	 *
	 * @param string $module to decide between ACP and board functions
	 * @param int $df_id download id to update the dataset and physical files
	 * @param bool $own_edit true returns the download details, false returns to the mcp
	 * @param string $u_action return path on acp module
	 * @access public
	 */
	public function dl_submit_download($module, $df_id = 0, $own_edit = 0, $u_action = '');

	/**
	 * Fetch all data to prepare the download edit form
	 *
	 * @param string $module to decide between ACP and board functions
	 * @param int $df_id download id to fetch the dataset
	 * @param bool $own_edit permission to edit a download by a regular user
	 * @param string $u_action return path on acp module
	 * @access public
	 */
	public function dl_edit_download($module, $df_id = 0, $own_edit = 0, $u_action = '');

	/**
	 * Delete selected download version and attached files
	 *
	 * @param string $module to decide between ACP and board functions
	 * @param int $cat_id download category id
	 * @param int $df_id download id to update the dataset and physical files
	 * @param string $u_action return path on acp module
	 * @access public
	 */
	public function dl_delete_version($module, $cat_id, $df_id, $u_action = '');
}
