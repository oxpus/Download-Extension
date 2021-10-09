<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v820;

class release_8_2_0 extends \phpbb\db\migration\migration
{
	protected $dl_ext_version = '8.2.0';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\v810\release_8_1_9'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.update', ['dl_ext_version', $this->dl_ext_version]],
		];
	}

	public function update_schema()
	{
		return [
			'change_columns' => [
				$this->table_prefix . 'dl_hotlink' => [
					'hotlink_id' => ['VCHAR:255', ''],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'change_columns' => [
				$this->table_prefix . 'dl_hotlink' => [
					'hotlink_id' => ['VCHAR:32', ''],
				],
			],
		];
	}
}
