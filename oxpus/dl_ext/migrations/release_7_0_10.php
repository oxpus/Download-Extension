<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dl_ext\migrations;

class release_7_0_10 extends \phpbb\db\migration\migration
{
	var $dl_ext_version = '7.0.10';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	static public function depends_on()
	{
		return array('\oxpus\dl_ext\migrations\release_7_0_9');
	}

	public function update_data()
	{
		return array(
			// Set the current version
			array('config.update', array('dl_ext_version', $this->dl_ext_version)),

			array('custom', array(array($this, 'drop_wrong_cache_files'))),
		);
	}

	public function drop_wrong_cache_files()
	{
		$drop_files = array(
			'auth',
			'black',
			'cat_counts',
			'cats',
			'file_preset',
		);

		foreach($drop_files as $file)
		{
			if (@file_exists($this->phpbb_root_path . 'DL_EXT_CACHE_FOLDERdata_dl_' . $file . '.' . $this->php_ext))
			{
				@unlink($this->phpbb_root_path . 'DL_EXT_CACHE_FOLDERdata_dl_' . $file . '.' . $this->php_ext);
			}
		}							
	}
}
