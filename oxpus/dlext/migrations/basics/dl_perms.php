<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\basics;

class dl_perms extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['dl_use_ext_blacklist']);
	}

	static public function depends_on()
	{
		return array('\oxpus\dlext\migrations\basics\dl_module');
	}

	public function update_data()
	{
		return array(
			// The needed permissions
			array('permission.add', array('a_dl_overview')),
			array('permission.add', array('a_dl_config')),
			array('permission.add', array('a_dl_traffic')),
			array('permission.add', array('a_dl_categories')),
			array('permission.add', array('a_dl_files')),
			array('permission.add', array('a_dl_permissions')),
			array('permission.add', array('a_dl_stats')),
			array('permission.add', array('a_dl_banlist')),
			array('permission.add', array('a_dl_blacklist')),
			array('permission.add', array('a_dl_toolbox')),
			array('permission.add', array('a_dl_fields')),
			array('permission.add', array('a_dl_browser')),

			// Join permissions to administrators
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_dl_overview')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_dl_config')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_dl_traffic')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_dl_categories')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_dl_files')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_dl_permissions')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_dl_stats')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_dl_banlist')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_dl_blacklist')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_dl_toolbox')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_dl_fields')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_dl_browser')),

			// Set the next config
			array('config.add', array('dl_use_ext_blacklist', '1')),
		);
	}
}
