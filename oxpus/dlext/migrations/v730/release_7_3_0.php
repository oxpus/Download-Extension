<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\v730;

class release_7_3_0 extends \phpbb\db\migration\migration
{
	var $dl_ext_version = '7.3.0';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	static public function depends_on()
	{
		return ['\oxpus\dlext\migrations\v720\release_7_2_13'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.update', ['dl_ext_version', $this->dl_ext_version]],

			['module.add', [
				'ucp',
				'DOWNLOADS',
				[
					'module_basename'	=> '\oxpus\dlext\ucp\main_module',
					'modes'				=> ['dl_privacy'],
				],
			]],
		];
	}
}
