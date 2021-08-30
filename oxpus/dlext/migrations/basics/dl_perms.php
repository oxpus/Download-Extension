<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\basics;

class dl_perms extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['dl_use_ext_blacklist']);
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\basics\dl_module'];
	}

	public function update_data()
	{
		return [
			// The needed permissions
			['permission.add', ['a_dl_overview']],
			['permission.add', ['a_dl_config']],
			['permission.add', ['a_dl_traffic']],
			['permission.add', ['a_dl_categories']],
			['permission.add', ['a_dl_files']],
			['permission.add', ['a_dl_permissions']],
			['permission.add', ['a_dl_stats']],
			['permission.add', ['a_dl_banlist']],
			['permission.add', ['a_dl_blacklist']],
			['permission.add', ['a_dl_toolbox']],
			['permission.add', ['a_dl_fields']],
			['permission.add', ['a_dl_perm_check']],

			// Join permissions to administrators
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_dl_overview']],
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_dl_config']],
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_dl_traffic']],
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_dl_categories']],
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_dl_files']],
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_dl_permissions']],
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_dl_stats']],
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_dl_banlist']],
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_dl_blacklist']],
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_dl_toolbox']],
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_dl_fields']],
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_dl_perm_check']],

			// Set the next config
			['config.add', ['dl_use_ext_blacklist', '1']],
		];
	}
}
