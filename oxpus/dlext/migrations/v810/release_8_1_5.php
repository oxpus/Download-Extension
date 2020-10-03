<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\v810;

class release_8_1_5 extends \phpbb\db\migration\migration
{
	var $dl_ext_version = '8.1.5';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	static public function depends_on()
	{
		return ['\oxpus\dlext\migrations\v810\release_8_1_4'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.update', ['dl_ext_version', $this->dl_ext_version]],

			// Remove deprecated files
			['custom', [[$this, 'remove_deprecated_files']]],
		];
	}

	public function remove_deprecated_files()
	{
		global $phpbb_container, $phpbb_root_path, $phpEx, $phpbb_extension_manager;

		if ($phpbb_container->get('request')->variable('action', '') == 'delete_data')
		{
			return;
		}

		$ext_path = $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);

		@unlink($phpbb_root_path . $this->config['upload_path'] . '/dlext/thumbs/.htaccess');
		@unlink($phpbb_root_path . $this->config['upload_path'] . '/dlext/version/images/.htaccess');
		@unlink($ext_path . 'htaccess.txt');
		@unlink($ext_path . 'styles/prosilver/template/event/overall_footer_after.html');
	}
}
