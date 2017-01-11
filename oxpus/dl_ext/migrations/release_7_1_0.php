<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dl_ext\migrations;

class release_7_1_0 extends \phpbb\db\migration\migration
{
	var $dl_ext_version = '7.1.0';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	static public function depends_on()
	{
		return array('\oxpus\dl_ext\migrations\release_7_0_19');
	}

	public function update_data()
	{
		return array(
			// Set the current version
			array('config.update', array('dl_ext_version', $this->dl_ext_version)),
		);
	}

	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'dl_ver_files' => array(
					'COLUMNS'		=> array(
						'ver_file_id'	=> array('UINT', null, 'auto_increment'),
						'dl_id'			=> array('INT:11', 0),
						'ver_id'		=> array('INT:11', 0),
						'real_name'		=> array('VCHAR', ''),
						'file_name'		=> array('VCHAR', ''),
						'file_title'	=> array('VCHAR', ''),
						'file_type'		=> array('BOOL', 0),	// 0 = files, 1 = images
					),
					'PRIMARY_KEY'	=> 'ver_file_id'
				),
			),

			'add_columns'	=> array(
				$this->table_prefix . 'dl_versions'		=> array(
					'ver_text'			=> array('MTEXT_UNI', ''),
					'ver_uid'			=> array('CHAR:8', ''),
					'ver_bitfield'		=> array('VCHAR', ''),
					'ver_flags'			=> array('UINT:11', 0),
					'ver_active'		=> array('BOOL', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'dl_ver_files',
			),
		);
	}
}
