<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\v800;

class release_8_0_14 extends \phpbb\db\migration\migration
{
	var $dl_ext_version = '8.0.14';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	static public function depends_on()
	{
		return ['\oxpus\dlext\migrations\v800\release_8_0_13'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.update', ['dl_ext_version', $this->dl_ext_version]],

			// Add new confif option
			['config.add', ['dl_latest_type', 1]],
			['config.add', ['dl_desc_index', 1]],
			['config.add', ['dl_desc_search', 1]],
			['config.add', ['dl_limit_desc_on_search', '0']],

			// Remove deprecated files
			['custom', [[$this, 'remove_deprecated_files']]],
		];
	}

	public function remove_deprecated_files()
	{
		global $phpbb_container, $phpEx, $phpbb_extension_manager;

		if ($phpbb_container->get('request')->variable('action', '') == 'delete_data')
		{
			return;
		}

		$ext_path = $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);

		$styles_folder = $ext_path . 'styles/';

		$styles = scandir($styles_folder);

		foreach($styles as $key => $style)
		{
			if (is_dir($styles_folder . $style) && $style{0} <> '.')
			{
				@unlink($styles_folder . $style . '/template/dl_todo_edit_body.html');
			}
		}
	}
}
