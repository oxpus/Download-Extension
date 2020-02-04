<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2015-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\acp;

/**
* @package acp
*/
class main_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $phpbb_container;

		$request = $phpbb_container->get('request');

		// Build the template page
		$this->tpl_name = 'acp_dl_' . $mode;
		$this->page_title = 'DL_ACP_' . strtoupper($mode);

		if ($mode == 'categories' && ($request->variable('action', '') == 'edit' || $request->variable('action', '') == 'add'))
		{
			$this->tpl_name = 'acp_dl_cat_edit';
		}

		if ($mode == 'files' && ($request->variable('action', '') == 'edit' || $request->variable('action', '') == 'add'))
		{
			$this->tpl_name = 'acp_dl_files_edit';
		}

		// Mount the right acp container
		$dlext_container = $phpbb_container->get('oxpus.dlext.acp_' . $mode . '_controller');

		// Set global module path
		$dlext_container->set_action($this->u_action);

		// Start the handling in the mounted container
		$dlext_container->handle();
	}
}
