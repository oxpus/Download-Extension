<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v800;

class release_8_0_0 extends \phpbb\db\migration\migration
{
	protected $dl_ext_version = '8.0.0';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\basics\dl_commons'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.add', ['dl_ext_version', $this->dl_ext_version]],

			['config.add', ['dl_remain_guest_traffic', '0']],
			['config.add', ['dl_remain_traffic', '0']],
			['config.add', ['dl_enable_blacklist', '0']],

			['config.remove', ['dl_download_dir']],
			['config.remove', ['dl_uconf_link_onoff']],

			['module.remove', [
				'ucp',
				'DOWNLOADS',
				[
					'module_basename'   => '\oxpus\dlext\ucp\main_module',
					'module_langname'   => 'DL_CONFIG',
					'module_mode'       => 'config',
					'module_auth'       => 'ext_oxpus/dlext',
				],
			]],

			['module.remove', [
				'ucp',
				'DOWNLOADS',
				[
					'module_basename'   => '\oxpus\dlext\ucp\main_module',
					'module_langname'   => 'DL_PRIVACY',
					'module_mode'       => 'dl_privacy',
					'module_auth'       => 'ext_oxpus/dlext',
				],
			]],

			['module.remove', [
				'ucp',
				'DOWNLOADS',
				[
					'module_basename'   => '\oxpus\dlext\ucp\main_module',
					'module_langname'   => 'DL_FAVORITE',
					'module_mode'       => 'favorite',
					'module_auth'       => 'ext_oxpus/dlext',
				],
			]],

			['module.add', [
				'ucp',
				'DOWNLOADS',
				[
					'module_basename'	=> '\oxpus\dlext\ucp\main_module',
					'modes'				=> ['ucp_config', 'ucp_privacy', 'ucp_favorite'],
				],
			]],

			['custom', [[$this, 'move_remain_traffic']]],
		];
	}

	public function update_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'dl_rem_traf',
			],
		];
	}

	public function revert_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'dl_rem_traf' => [
					'COLUMNS'		=> [
						'config_name'	=> ['VCHAR', '0'],
						'config_value'	=> ['VCHAR', '0'],
					],
					'PRIMARY_KEY'	=> 'config_name'
				],
			],
		];
	}

	public function move_remain_traffic()
	{
		$this->db->sql_return_on_error(true);

		$sql = 'SELECT * FROM ' . $this->table_prefix . 'dl_rem_traf';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->config->set($row['config_name'], $row['config_value']);
		}
		$this->db->sql_freeresult($result);

		$this->db->sql_return_on_error(false);
	}
}
