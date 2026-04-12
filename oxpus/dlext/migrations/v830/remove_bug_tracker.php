<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2026 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v830;

class remove_bug_tracker extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return !$this->db_tools->sql_table_exists($this->table_prefix . 'dl_bug_tracker');
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\v830\release_8_3_0'];
	}

	public function update_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'dl_bug_tracker',
				$this->table_prefix . 'dl_bug_history',
			],
			'drop_columns' => [
				$this->table_prefix . 'downloads_cat' => [
					'bug_tracker',
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.remove', ['dl_nav_link_tracker']],
		];
	}
}
