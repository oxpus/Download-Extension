<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\v810;

class release_8_1_3 extends \phpbb\db\migration\migration
{
	var $dl_ext_version = '8.1.3';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	static public function depends_on()
	{
		return ['\oxpus\dlext\migrations\v810\release_8_1_2'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.update', ['dl_ext_version', $this->dl_ext_version]],

			['custom', [[$this, 'create_htaccess']]],
		];
	}

	public function create_htaccess()
	{
		// define extension folder constants
		$dl_ext_filebase_path = $this->phpbb_root_path . $this->config['upload_path'] . '/dlext/';

		$this->_create_folder($dl_ext_filebase_path . 'thumbs/');
		$this->_create_folder($dl_ext_filebase_path . 'version/');
		$this->_create_folder($dl_ext_filebase_path . 'version/images/');

		$this->_create_htaccess($dl_ext_filebase_path . 'thumbs/');
		$this->_create_htaccess($dl_ext_filebase_path . 'version/images/');
	}

	public function _create_folder($path)
	{
		if (@file_exists($path))
		{
			return;
		}

		@mkdir($path);
		@phpbb_chmod($path, CHMOD_ALL);

		$f = fopen($path . '/index.htm', 'w');
		fclose($f);
	}

	public function _create_htaccess($path)
	{
		if (!@file_exists($path))
		{
			return;
		}

		global $phpbb_extension_manager;

		$ext_path = $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);

		@unlink($path . '.htaccess');
		@copy ($ext_path . 'htaccess', $path . '.htaccess');
		@phpbb_chmod($path . '.htaccess', CHMOD_READ);
	}
}
