<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\v720;

class release_7_2_11 extends \phpbb\db\migration\migration
{
	var $dl_ext_version = '7.2.11';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	static public function depends_on()
	{
		return ['\oxpus\dlext\migrations\v720\release_7_2_10'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.update', ['dl_ext_version', $this->dl_ext_version]],

			// Add new configurations
			['config.add', ['dl_set_add', 0]],
			['config.add', ['dl_set_user', 0]],
		];
	}

	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'downloads_cat'	=> [
					'dl_set_add'	=> ['UINT:11', 0],
					'dl_set_user'	=> ['UINT:11', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			['config.remove', ['dl_set_add']],
			['config.remove', ['dl_set_user']],
		];
	}
}
