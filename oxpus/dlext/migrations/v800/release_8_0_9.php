<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\v800;

class release_8_0_9 extends \phpbb\db\migration\migration
{
	var $dl_ext_version = '8.0.9';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	static public function depends_on()
	{
		return array('\oxpus\dlext\migrations\v800\release_8_0_8');
	}

	public function update_data()
	{
		return array(
			// Set the current version
			array('config.update', array('dl_ext_version', $this->dl_ext_version)),

			array('module.remove', array(
				'mcp',
				'MCP_DOWNLOADS',
				array(
					'module_basename'   => '\oxpus\dlext\mcp\main_module',
					'module_langname'   => 'DL_MODCP_MANAGE',
					'module_mode'       => 'mcp_manage',
					'module_auth'       => 'ext_oxpus/dlext',
				),
			)),
			array('module.remove', array(
				'mcp',
				'MCP_DOWNLOADS',
				array(
					'module_basename'   => '\oxpus\dlext\mcp\main_module',
					'module_langname'   => 'DL_MODCP_EDIT',
					'module_mode'       => 'mcp_edit',
					'module_auth'       => 'ext_oxpus/dlext',
				),
			)),
			array('module.remove', array(
				'mcp',
				'MCP_DOWNLOADS',
				array(
					'module_basename'   => '\oxpus\dlext\mcp\main_module',
					'module_langname'   => 'DL_MODCP_APPROVE',
					'module_mode'       => 'mcp_approve',
					'module_auth'       => 'ext_oxpus/dlext',
				),
			)),
			array('module.remove', array(
				'mcp',
				'MCP_DOWNLOADS',
				array(
					'module_basename'   => '\oxpus\dlext\mcp\main_module',
					'module_langname'   => 'DL_MODCP_CAPPROVE',
					'module_mode'       => 'mcp_capprove',
					'module_auth'       => 'ext_oxpus/dlext',
				),
			)),
			array('module.remove', array(
				'mcp',
				0,
				'MCP_DOWNLOADS'
			)),
		);
	}
}
