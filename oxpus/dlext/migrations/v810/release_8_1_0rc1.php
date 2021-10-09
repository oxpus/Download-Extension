<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v810;

class release_8_1_0rc1 extends \phpbb\db\migration\migration
{
	protected $dl_ext_version = '8.1.0-RC1';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\v800\release_8_0_14'];
	}

	public function update_data()
	{
		return [
			// Set the current version
			['config.update', ['dl_ext_version', $this->dl_ext_version]],

			['config.remove', ['dl_disable_popup']],
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

	public function revert_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'users' => [
					'user_allow_new_download_email'	=> ['BOOL', 0],
					'user_allow_new_download_popup'	=> ['BOOL', 1],
					'user_allow_fav_download_email'	=> ['BOOL', 1],
					'user_allow_fav_download_popup'	=> ['BOOL', 1],
					'user_allow_fav_comment_email'	=> ['BOOL', 1],
					'user_dl_note_type'				=> ['BOOL', 1],
					'user_new_download'				=> ['BOOL', 0],
				],
			],
		];
	}
}
