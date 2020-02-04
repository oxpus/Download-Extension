<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
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
		return array('\oxpus\dlext\migrations\v720\release_7_2_2');
	}

	public function update_data()
	{
		return array(
			// Set the current version
			array('config.update', array('dl_ext_version', $this->dl_ext_version)),

			// Add new options
			array('config.add', array('dl_topic_type', POST_NORMAL)),

			// Remove deprecated options
			array('config.remove', array('dl_latest_comments')),

			// The needed permissions
			array('permission.add', array('a_dl_perm_check')),

			// Join permissions to administrators
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_dl_perm_check')),

			// Need to add the module later than the permission settings to avoid an error on uninstalling the extension
			array('module.add', array(
				'acp',
				'ACP_DOWNLOADS',
				array(
					'module_basename'	=> '\oxpus\dlext\acp\main_module',
					'modes'				=> array('perm_check'),
				),
			)),
		);
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'downloads_cat'		=> array(
					'dl_topic_type'			=> array('BOOL', POST_NORMAL),
				),
			),
		);
	}
}
