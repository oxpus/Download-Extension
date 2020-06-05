<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\v810;

class release_8_1_0RC1 extends \phpbb\db\migration\migration
{
	var $dl_ext_version = '8.1.0-RC1';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	static public function depends_on()
	{
		return ['\oxpus\dlext\migrations\v800\release_8_0_14'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.update', ['dl_ext_version', $this->dl_ext_version]],

			// Add new confif option
			['config.remove', ['dl_disable_popup']],

			// Remove deprecated files
			['custom', [[$this, 'remove_deprecated_files']]],
		];
	}

	public function update_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'users' => [
					'user_allow_new_download_email',
					'user_allow_new_download_popup',
					'user_allow_fav_download_email',
					'user_allow_fav_download_popup',
					'user_allow_fav_comment_email',
					'user_dl_note_type',
					'user_new_download',
				],
			],
		];
	}

	public function remove_deprecated_files()
	{
		global $phpbb_container, $phpEx, $phpbb_extension_manager;

		if ($phpbb_container->get('request')->variable('action', '') == 'delete_data')
		{
			return;
		}

		$ext_path = $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);

		@unlink($ext_path . 'controller/classes/dlext_email_interface.' . $phpEx);
		@unlink($ext_path . 'controller/classes/dlext_email.' . $phpEx);
	}
}
