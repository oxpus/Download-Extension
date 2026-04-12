<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2026 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v830;

class remove_traffic extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return !$this->db_tools->sql_table_exists($this->table_prefix . 'dl_notraf');
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\v830\release_8_3_0'];
	}

	public function update_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'dl_notraf',
				$this->table_prefix . 'dl_cat_traf',
			],
			'drop_columns' => [
				$this->table_prefix . 'downloads' => [
					'file_traffic',
				],
				$this->table_prefix . 'downloads_cat' => [
					'cat_traffic',
					'cat_traffic_use',
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
			['config.remove', ['dl_traffic_off']],
			['config.remove', ['dl_overall_traffic']],
			['config.remove', ['dl_overall_guest_traffic']],
			['config.remove', ['dl_remain_traffic']],
			['config.remove', ['dl_remain_guest_traffic']],
			['config.remove', ['dl_upload_traffic_count']],
			['config.remove', ['dl_posts']],
			['config.remove', ['dl_antispam_posts']],
			['config.remove', ['dl_antispam_hours']],
			['config.remove', ['dl_enable_post_traffic']],
			['config.remove', ['dl_newtopic_traffic']],
			['config.remove', ['dl_reply_traffic']],
			['config.remove', ['dl_drop_traffic_postdel']],
			['config.remove', ['dl_delay_auto_traffic']],
			['config.remove', ['dl_delay_post_traffic']],
			['config.remove', ['dl_traffics_founder']],
			['config.remove', ['dl_traffics_overall']],
			['config.remove', ['dl_traffics_overall_guests']],
			['config.remove', ['dl_traffics_users']],
			['config.remove', ['dl_traffics_guests']],
			['permission.remove', ['a_dl_traffic']],
		];
	}
}
