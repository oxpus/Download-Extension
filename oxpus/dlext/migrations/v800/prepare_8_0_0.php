<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\v800;

class prepare_8_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['dl_remain_traffic']);
	}

	static public function depends_on()
	{
		return ['\oxpus\dlext\migrations\v730\release_7_3_5'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'move_remain_traffic']]],

			['config.add', ['dl_remain_guest_traffic', '0']],
			['config.add', ['dl_remain_traffic', '0']],
			['config.add', ['dl_enable_blacklist', '0']],

			['config.remove', ['dl_download_dir']],
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
