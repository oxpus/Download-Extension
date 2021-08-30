<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v810;

class release_8_1_0rc2 extends \phpbb\db\migration\migration
{
	protected $dl_ext_version = '8.1.0-RC2';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\v810\release_8_1_0rc1'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.update', ['dl_ext_version', $this->dl_ext_version]],

			['config_text.add', ['dl_file_edit_hint', '']],
			['config.add', ['dl_file_edit_hint_bbcode', '']],
			['config.add', ['dl_file_edit_hint_bitfield', '']],
			['config.add', ['dl_file_edit_hint_flags', '0']],

			// Recalculate rating points
			['custom', [[$this, 'update_ratings']]],
		];
	}

	public function update_ratings()
	{
		$sql = 'SELECT dl_id, AVG(rate_point) AS rating FROM ' . $this->table_prefix . 'dl_ratings
			GROUP BY dl_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql_update = 'UPDATE ' . $this->table_prefix . 'downloads SET ' . $this->db->sql_build_array('UPDATE', [
				'rating'	=> ceil($row['rating'] * 10)
			]) . ' WHERE id = ' . (int) $row['dl_id'];
			$this->db->sql_query($sql_update);
		}

		$this->db->sql_freeresult($result);
	}
}
