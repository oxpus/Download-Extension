<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
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
		];
	}
}