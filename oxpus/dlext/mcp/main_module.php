<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\mcp;

/**
* @package mcp
*/
class main_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $phpbb_container;

		// Build the template page
		$this->tpl_name = 'dl_' . $mode;
		$this->page_title = 'DL_MODCP_' . strtoupper($mode);

		// Mount the right mcp container
		$dlext_container = $phpbb_container->get('oxpus.dlext.' . $mode);

		// Set the action path
		$dlext_container->set_action(str_replace('&amp;mode=' . $mode, '', $this->u_action));

		// Start the handling in the mounted container
		$dlext_container->handle();
	}
}
