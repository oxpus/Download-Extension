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
class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\oxpus\dlext\mcp\main_module',
			'title'		=> 'MCP_DOWNLOADS',
			'modes'		=> array(
				'mcp_manage'	=> array(
					'title' => 'DL_MODCP_MANAGE',	'auth' => 'ext_oxpus/dlext',	'cat' => array('MCP_DOWNLOADS')
				),
				'mcp_edit'	=> array(
					'title' => 'DL_MODCP_EDIT',		'auth' => 'ext_oxpus/dlext',	'cat' => array('MCP_DOWNLOADS')
				),
				'mcp_approve'	=> array(
					'title' => 'DL_MODCP_APPROVE',	'auth' => 'ext_oxpus/dlext',	'cat' => array('MCP_DOWNLOADS')
				),
				'mcp_capprove'	=> array(
					'title' => 'DL_MODCP_CAPPROVE',	'auth' => 'ext_oxpus/dlext',	'cat' => array('MCP_DOWNLOADS')
				),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}
