<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v820;

class release_8_2_5 extends \phpbb\db\migration\migration
{
	protected $dl_ext_version = '8.2.5';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\v820\release_8_2_0'];
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
					'modes'				=> ['banlist'],
				],
			]],

			['permission.remove', ['a_dl_banlist']],
		];
	}

	public function update_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'dl_banlist',
			],
		];
	}

	public function revert_schema()
	{
		return [
			['permission.add', ['a_dl_banlist']],
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_dl_banlist']],

			'add_tables'	=> [
				$this->table_prefix . 'dl_banlist' => [
					'COLUMNS'		=> [
						'ban_id'		=> ['UINT:11', null, 'auto_increment'],
						'user_id'		=> ['UINT', 0],
						'user_ip'		=> ['VCHAR:40', ''],
						'user_agent'	=> ['VCHAR:50', ''],
						'username'		=> ['VCHAR:25', ''],
						'guests'		=> ['BOOL', 0],
					],
					'PRIMARY_KEY'	=> 'ban_id'
				],
			],
		];
	}
}
