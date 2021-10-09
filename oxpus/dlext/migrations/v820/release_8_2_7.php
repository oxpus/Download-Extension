<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v820;

class release_8_2_7 extends \phpbb\db\migration\migration
{
	protected $dl_ext_version = '8.2.7';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\v820\release_8_2_5'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.update', ['dl_ext_version', $this->dl_ext_version]],

			['config.remove', ['dl_recent_downloads']],

			['custom', [[$this, 'update_reports_history']]],
		];
	}

	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'dl_bug_history'		=> [
					'report_his_user_id'	=> ['UINT', 0],
					'report_his_status'		=> ['TINT:2', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'dl_bug_history' => ['report_his_user_id', 'report_his_status'],
			],
		];
	}

	public function update_reports_history()
	{
		$user_ids_ary = [];

		$sql = 'SELECT username, user_id FROM ' . $this->table_prefix . 'users';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_ids_ary[strtolower($row['username'])] = $row['user_id'];
		}

		$this->db->sql_freeresult($result);

		$sql = 'SELECT report_his_id, report_his_type, report_his_value FROM ' . $this->table_prefix . 'dl_bug_history';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$report_id		= $row['report_his_id'];
			$report_type	= $row['report_his_type'];
			$report_value	= $row['report_his_value'];

			$report_data_ary = explode(':', $report_value);

			if ($report_type == 'assign')
			{
				$user_id		= (int) $report_data_ary[0];
				$report_status	= 0;
				$report_value	= '';
			}
			else
			{
				$user_id		= (isset($user_ids_ary[strtolower($report_data_ary[1])])) ? (int) $user_ids_ary[strtolower($report_data_ary[1])] : ANONYMOUS;
				$report_status	= (int) $report_data_ary[0];
				$report_value	= (isset($report_data_ary[2])) ? $report_data_ary[2] : '';
			}

			$sql_update = 'UPDATE ' . $this->table_prefix . 'dl_bug_history SET ' . $this->db->sql_build_array('UPDATE', [
				'report_his_value'		=> $report_value,
				'report_his_user_id'	=> $user_id,
				'report_his_status'		=> $report_status
			]) . ' WHERE report_his_id = ' . (int) $report_id;
			$this->db->sql_query($sql_update);
		}

		$this->db->sql_freeresult($result);

		unset($user_ids_ary);
	}
}
