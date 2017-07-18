<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\v720;

class release_7_2_11 extends \phpbb\db\migration\migration
{
	var $dl_ext_version = '7.2.11';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	static public function depends_on()
	{
		return array('\oxpus\dlext\migrations\v720\release_7_2_10');
	}

	public function update_data()
	{
		return array(
			// Set the current version
			array('config.update', array('dl_ext_version', $this->dl_ext_version)),

			// Add new configurations
			array('config.add', array('dl_set_add', 0)),
			array('config.add', array('dl_set_user', 0)),
		);
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'downloads_cat'		=> array(
					'dl_set_add'	=> array('UINT:11', 0),
					'dl_set_user'	=> array('UINT:11', 0),
				),
			),
		);
	}
}
