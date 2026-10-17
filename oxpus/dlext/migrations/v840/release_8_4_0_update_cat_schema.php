<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2025 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\v840;

class release_8_4_0_update_cat_schema extends \phpbb\db\migration\migration
{
	protected $dl_ext_version = '8.4.0';

	public function effectively_installed()
	{
		return isset($this->config['dl_ext_version']) && version_compare($this->config['dl_ext_version'], $this->dl_ext_version, '>=');
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\v840\release_8_4_0_update_permissions'];
	}

	public function update_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'downloads_cat' => ['auth_view', 'auth_dl', 'auth_up', 'auth_mod', 'auth_cread', 'auth_cpost'],
			],

			['config.update', ['dl_ext_version', $this->dl_ext_version]],
		];
	}

	public function revert_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'downloads_cat'	=> [
						'auth_view'		=> ['BOOL', 1],
						'auth_dl'		=> ['BOOL', 1],
						'auth_up'		=> ['BOOL', 0],
						'auth_mod'		=> ['BOOL', 0],
						'auth_cread'	=> ['BOOL', 0],
						'auth_cpost'	=> ['BOOL', 1],
				],
			],
		];
	}
}
