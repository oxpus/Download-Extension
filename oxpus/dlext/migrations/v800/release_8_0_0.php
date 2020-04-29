<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\v800;

class release_8_0_0 extends \phpbb\db\migration\migration
{
	var $dl_ext_version = '8.0.0';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	static public function depends_on()
	{
		return array('\oxpus\dlext\migrations\v800\prepare_8_0_0');
	}

	public function update_data()
	{
		global $phpbb_extension_manager, $phpEx;

		$ext_path = $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);

		if (@file_exists($ext_path . 'mcp\mcp_info.' . $phpEx))
		{
			return array(
				// Set the current version
				array('config.update', array('dl_ext_version', $this->dl_ext_version)),

				array('config.remove', array('dl_uconf_link_onoff')),

				array('module.remove', array(
					'ucp',
					'DOWNLOADS',
					array(
						'module_basename'   => '\oxpus\dlext\ucp\main_module',
						'module_langname'   => 'DL_CONFIG',
						'module_mode'       => 'config',
						'module_auth'       => 'ext_oxpus/dlext',
					),
				)),
				array('module.remove', array(
					'ucp',
					'DOWNLOADS',
					array(
						'module_basename'   => '\oxpus\dlext\ucp\main_module',
						'module_langname'   => 'DL_PRIVACY',
						'module_mode'       => 'dl_privacy',
						'module_auth'       => 'ext_oxpus/dlext',
					),
				)),
				array('module.remove', array(
					'ucp',
					'DOWNLOADS',
					array(
						'module_basename'   => '\oxpus\dlext\ucp\main_module',
						'module_langname'   => 'DL_FAVORITE',
						'module_mode'       => 'favorite',
						'module_auth'       => 'ext_oxpus/dlext',
					),
				)),
				array('module.add', array(
					'ucp',
					'DOWNLOADS',
					array(
						'module_basename'	=> '\oxpus\dlext\ucp\main_module',
						'modes'				=> array('ucp_config','ucp_privacy','ucp_favorite'),
					),
				)),
				array('module.add', array(
					'mcp',
					0,
					'MCP_DOWNLOADS'
				)),
				array('module.add', array(
					'mcp',
					'MCP_DOWNLOADS',
					array(
						'module_basename'	=> '\oxpus\dlext\mcp\main_module',
						'modes'				=> array('mcp_manage','mcp_edit','mcp_approve','mcp_capprove'),
					),
				)),
			);
		}
		else
		{
			return array(
				// Set the current version
				array('config.update', array('dl_ext_version', $this->dl_ext_version)),

				array('config.remove', array('dl_uconf_link_onoff')),

				array('module.remove', array(
					'ucp',
					'DOWNLOADS',
					array(
						'module_basename'   => '\oxpus\dlext\ucp\main_module',
						'module_langname'   => 'DL_CONFIG',
						'module_mode'       => 'config',
						'module_auth'       => 'ext_oxpus/dlext',
					),
				)),
				array('module.remove', array(
					'ucp',
					'DOWNLOADS',
					array(
						'module_basename'   => '\oxpus\dlext\ucp\main_module',
						'module_langname'   => 'DL_PRIVACY',
						'module_mode'       => 'dl_privacy',
						'module_auth'       => 'ext_oxpus/dlext',
					),
				)),
				array('module.remove', array(
					'ucp',
					'DOWNLOADS',
					array(
						'module_basename'   => '\oxpus\dlext\ucp\main_module',
						'module_langname'   => 'DL_FAVORITE',
						'module_mode'       => 'favorite',
						'module_auth'       => 'ext_oxpus/dlext',
					),
				)),
				array('module.add', array(
					'ucp',
					'DOWNLOADS',
					array(
						'module_basename'	=> '\oxpus\dlext\ucp\main_module',
						'modes'				=> array('ucp_config','ucp_privacy','ucp_favorite'),
					),
				)),
			);
		}
	}

	public function update_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'dl_rem_traf',
			),
		);
	}
}
