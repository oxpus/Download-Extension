<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2025 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v830;

class release_8_3_0_common extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['dl_thumb_xsize_max']);
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\v820\release_8_2_15'];
	}

	public function update_data()
	{
		return [
			// Add new configurations
			['config.add', ['dl_thumb_xsize_max', 150]],
			['config.add', ['dl_thumb_ysize_max', 150]],

			['custom', [[$this, 'move_thumbnails']]],
		];
	}

	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'downloads_cat'	=> [
					'max_thumbs'	=> ['UINT', 10],
				],
				$this->table_prefix . 'dl_images' 		=> [
					'img_index'		=> ['BOOL', 0],
					'img_lists'		=> ['BOOL', 0],
				],
			],

			'add_tables'	=> [
				$this->table_prefix . 'dl_reports' => [
					'COLUMNS'		=> [
						'report_id'		=> ['UINT:11', null, 'auto_increment'],
						'dl_id'			=> ['UINT', 0],
						'user_id'		=> ['UINT:10', 0],
						'report_time'	=> ['UINT:11', 0],
						'report_text'	=> ['MTEXT_UNI', ''],
						'report_closed'	=> ['BOOL', 0],
						'report_cuser'	=> ['UINT:10', 0],
						'report_ctime'	=> ['UINT:11', 0],
					],
					'PRIMARY_KEY'	=> 'report_id'
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'downloads_cat' => ['max_thumbs'],
				$this->table_prefix . 'downloads_cat' => ['img_index', 'img_lists'],
			],

			'drop_tables' => [
				$this->table_prefix . 'dl_reports',
			],
		];
	}

	public function move_thumbnails()
	{
		$sql = 'SELECT id, thumbnail FROM ' . $this->table_prefix . "downloads
				WHERE thumbnail <> ''";
		$result = $this->db->sql_query($sql);
		$found_thumbnails = $this->db->sql_affectedrows();

		if ($found_thumbnails)
		{
			$sql_insert = [];

			while ($row = $this->db->sql_fetchrow($result))
			{
				$sql_insert[] = [
					'dl_id' => $row['id'],
					'img_name' => $row['thumbnail'],
					'img_index' => 1,
					'img_lists' => 1,
					'img_title'	=> ''
				];
			}

			$this->db->sql_multi_insert($this->table_prefix . 'dl_images', $sql_insert);
		}

		$this->db->sql_freeresult($result);
	}
}
