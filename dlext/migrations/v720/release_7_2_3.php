<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\v720;

class release_7_2_3 extends \phpbb\db\migration\migration
{
	var $dl_ext_version = '7.2.3';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	static public function depends_on()
	{
		return ['\oxpus\dlext\migrations\v720\release_7_2_2'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.update', ['dl_ext_version', $this->dl_ext_version]],

			// Add new options
			['config.add', ['dl_topic_type', POST_NORMAL]],

			// Remove deprecated options
			['config.remove', ['dl_latest_comments']],

			// The needed permissions
			['permission.add', ['a_dl_perm_check']],

			// Join permissions to administrators
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_dl_perm_check']],

			// Need to add the module later than the permission settings to avoid an error on uninstalling the extension
			['module.add', [
				'acp',
				'ACP_DOWNLOADS',
				[
					'module_basename'	=> '\oxpus\dlext\acp\main_module',
					'modes'				=> ['perm_check'],
				],
			]],
		];
	}

	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'downloads_cat'	=> [
					'dl_topic_type'		=> ['BOOL', POST_NORMAL],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'downloads_cat' => ['dl_topic_type'],
			],
		];
	}
}
