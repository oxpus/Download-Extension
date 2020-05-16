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
		return ['\oxpus\dlext\migrations\v800\release_8_0_8'];
	}

	public function update_data()
	{
		global $phpbb_container;

		if ($phpbb_container->get('request')->variable('action', '') == 'delete_data')
		{
			return;
		}
		else
		{
			return [
				// Set the current version
				['config.update', ['dl_ext_version', $this->dl_ext_version]],

				['module.remove', [
					'mcp',
					'MCP_DOWNLOADS',
					[
						'module_basename'   => '\oxpus\dlext\mcp\main_module',
						'module_langname'   => 'DL_MODCP_MANAGE',
						'module_mode'       => 'mcp_manage',
						'module_auth'       => 'ext_oxpus/dlext',
					],
				]],
				['module.remove', [
					'mcp',
					'MCP_DOWNLOADS',
					[
						'module_basename'   => '\oxpus\dlext\mcp\main_module',
						'module_langname'   => 'DL_MODCP_EDIT',
						'module_mode'       => 'mcp_edit',
						'module_auth'       => 'ext_oxpus/dlext',
					],
				]],
				['module.remove', [
					'mcp',
					'MCP_DOWNLOADS',
					[
						'module_basename'   => '\oxpus\dlext\mcp\main_module',
						'module_langname'   => 'DL_MODCP_APPROVE',
						'module_mode'       => 'mcp_approve',
						'module_auth'       => 'ext_oxpus/dlext',
					],
				]],
				['module.remove', [
					'mcp',
					'MCP_DOWNLOADS',
					[
						'module_basename'   => '\oxpus\dlext\mcp\main_module',
						'module_langname'   => 'DL_MODCP_CAPPROVE',
						'module_mode'       => 'mcp_capprove',
						'module_auth'       => 'ext_oxpus/dlext',
					],
				]],
				['module.remove', [
					'mcp',
					0,
					'MCP_DOWNLOADS'
				]],

				['custom', [[$this, 'remove_deprecated_files']]],
			];
		}
	}

	public function remove_deprecated_files()
	{
		global $phpbb_container, $phpEx, $phpbb_extension_manager;

		if ($phpbb_container->get('request')->variable('action', '') == 'delete_data')
		{
			return;
		}

		$ext_path = $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);

		@unlink($ext_path . 'mcp/main_info.' . $phpEx);
		@unlink($ext_path . 'mcp/main_module.' . $phpEx);
		@rmdir($ext_path . 'mcp');
	}
}
