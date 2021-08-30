<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright (c) 2015-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\acp;

/**
 * @package acp
 */
class main_module
{
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		$request = $phpbb_container->get('request');

		$action = $request->variable('action', '');

		$module = $mode;

		if ($mode == 'categories' && ($action == 'edit' || $action == 'add' || $action == 'save_cat'))
		{
			$module = 'cat_edit';
		}

		if ($mode == 'files' && ($action == 'edit' || $action == 'add' || $action == 'save'))
		{
			$module = 'files_edit';
		}

		// Build the template page
		$this->tpl_name = '@oxpus_dlext/acp_dl_' . $module;
		$this->page_title = 'DL_ACP_' . strtoupper($module);

		// Mount the right acp container
		$dlext_container = $phpbb_container->get('oxpus.dlext.acp_' . $module . '_controller');

		// Set global module path
		$dlext_container->set_action($this->u_action);

		// Start the handling in the mounted container
		$dlext_container->handle();
	}
}
