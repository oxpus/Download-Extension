<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\v710;

class release_7_1_0 extends \phpbb\db\migration\migration
{
	var $dl_ext_version = '7.1.0';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	static public function depends_on()
	{
		return ['\oxpus\dlext\migrations\v700\release_7_0_19'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.update', ['dl_ext_version', $this->dl_ext_version]],
		];
	}

	public function update_schema()
	{
		return [
			'add_tables'	=> [
				$this->table_prefix . 'dl_ver_files' => [
					'COLUMNS'		=> [
						'ver_file_id'	=> ['UINT', null, 'auto_increment'],
						'dl_id'			=> ['INT:11', 0],
						'ver_id'		=> ['INT:11', 0],
						'real_name'		=> ['VCHAR', ''],
						'file_name'		=> ['VCHAR', ''],
						'file_title'	=> ['VCHAR', ''],
						'file_type'		=> ['BOOL', 0],	// 0 = files, 1 = images
					],
					'PRIMARY_KEY'	=> 'ver_file_id'
				],
			],

			'add_columns'	=> [
				$this->table_prefix . 'dl_versions'		=> [
					'ver_text'			=> ['MTEXT_UNI', ''],
					'ver_uid'			=> ['CHAR:8', ''],
					'ver_bitfield'		=> ['VCHAR', ''],
					'ver_flags'			=> ['UINT:11', 0],
					'ver_active'		=> ['BOOL', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'dl_ver_files',
			],
		];
	}
}
