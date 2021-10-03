<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\basics;

class dl_commons extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['dl_cat_edit']);
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\basics\dl_perms'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'add_default_blacklist_extentions']]],
			['custom', [[$this, 'first_reset_remain_traffic']]],

			// Preset the config data
			['config.add', ['dl_cat_edit', '1']],
		];
	}

	public function add_default_blacklist_extentions()
	{
		$sql_insert = [
			['extention'	=> 'asp'],
			['extention'	=> 'cgi'],
			['extention'	=> 'dhtm'],
			['extention'	=> 'dhtml'],
			['extention'	=> 'exe'],
			['extention'	=> 'htm'],
			['extention'	=> 'html'],
			['extention'	=> 'jar'],
			['extention'	=> 'js'],
			['extention'	=> 'php'],
			['extention'	=> 'php3'],
			['extention'	=> 'pl'],
			['extention'	=> 'sh'],
			['extention'	=> 'shtm'],
			['extention'	=> 'shtml'],
		];

		$this->db->sql_multi_insert($this->table_prefix . 'dl_ext_blacklist', $sql_insert);
	}

	public function first_reset_remain_traffic()
	{
		$sql_insert = [
			['config_name' => 'dl_remain_guest_traffic', 'config_value' => '0'],
			['config_name' => 'dl_remain_traffic', 'config_value' => '0'],
		];

		$this->db->sql_multi_insert($this->table_prefix . 'dl_rem_traf', $sql_insert);
	}
}
