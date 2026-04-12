<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2026 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v830;

class remove_hacklist extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return !$this->db_tools->sql_table_exists($this->table_prefix . 'downloads')
			|| !$this->db_tools->sql_column_exists($this->table_prefix . 'downloads', 'hacklist');
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\v830\release_8_3_0'];
	}

	public function update_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'downloads' => [
					'hacklist',
				],
			],
		];
	}

	public function revert_schema()
	{
		// dl_schema handles full table recreation on purge
		return [];
	}

	public function update_data()
	{
		return [
			['config.remove', ['dl_use_hacklist']],
			['config.remove', ['dl_nav_link_hacks']],
		];
	}
}
