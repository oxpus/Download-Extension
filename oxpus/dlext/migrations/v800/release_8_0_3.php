<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\v800;

class release_8_0_3 extends \phpbb\db\migration\migration
{
	var $dl_ext_version = '8.0.3';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	static public function depends_on()
	{
		return array('\oxpus\dlext\migrations\v800\release_8_0_2');
	}

	public function update_data()
	{
		return array(
			// Set the current version
			array('config.update', array('dl_ext_version', $this->dl_ext_version)),

			array('module.remove', array(
				'acp',
				'ACP_DOWNLOADS',
				array(
					'module_basename'	=> '\oxpus\dlext\acp\main_module',
					'modes'				=> array('browser'),
				),
			)),

			array('permission.remove', array('a_dl_browser')),
			array('permission.permission_unset', array('ROLE_ADMIN_FULL', 'a_dl_browser')),

			array('custom', array(array($this, 'remove_deprecated_files'))),
		);
	}

	public function update_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'dl_banlist' => array('user_agent'),
				$this->table_prefix . 'dl_stats' => array('browser'),
			),
		);
	}

	public function remove_deprecated_files()
	{
		global $phpbb_container, $phpEx, $phpbb_extension_manager;

		if ($phpbb_container->get('request')->variable('action', '') == 'delete_data')
		{
			return;
		}

		$ext_path = $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);

		@unlink($ext_path . 'adm/style/acp_dl_browser.html');
		@unlink($ext_path . 'controller/acp/acp_browser_controller.' . $phpEx);
		@unlink($ext_path . 'controller/acp/acp_browser_interface.' . $phpEx);
		@unlink($ext_path . 'phpbb/includes/user_agents.' . $phpEx);
	}
}
