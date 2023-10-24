<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v820;

class release_8_2_14 extends \phpbb\db\migration\migration
{
	protected $dl_ext_version = '8.2.14';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\v820\release_8_2_9'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.update', ['dl_ext_version', $this->dl_ext_version]],

			// Add new configurations
			['config.add', ['dl_thumbs_display_cat', '2']],
			['config.add', ['dl_thumbs_display_latest', '2']],
			['config.add', ['dl_thumbs_display_overall', '2']],
			['config.add', ['dl_thumbs_display_search', '2']],
		];
	}

	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'downloads_cat'		=> [
					'display_thumbs'	=> ['BOOL', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'downloads_cat' => ['display_thumbs'],
			],
		];
	}
}
