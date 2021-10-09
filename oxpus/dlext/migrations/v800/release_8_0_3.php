<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v800;

class release_8_0_3 extends \phpbb\db\migration\migration
{
	protected $dl_ext_version = '8.0.3';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\v800\release_8_0_0'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.update', ['dl_ext_version', $this->dl_ext_version]],

			['module.remove', [
				'acp',
				'ACP_DOWNLOADS',
				[
					'module_basename'	=> '\oxpus\dlext\acp\main_module',
					'modes'				=> ['browser'],
				],
			]],

			['permission.remove', ['a_dl_browser']],
		];
	}

	public function update_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'dl_banlist' => ['user_agent'],
				$this->table_prefix . 'dl_stats' => ['browser'],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'dl_banlist'	=> [
					'user_agent'			=> ['VCHAR:50', ''],
				],
				$this->table_prefix . 'dl_stats'	=> [
					'browser'			=> ['VCHAR:255', ''],
				],
			],
		];
	}
}
