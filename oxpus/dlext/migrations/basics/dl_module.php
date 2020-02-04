<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\migrations\basics;

class dl_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['dl_use_hacklist']);
	}

	static public function depends_on()
	{
		return array('\oxpus\dlext\migrations\basics\dl_schema');
	}

	public function update_data()
	{
		return array(
			array('module.add', array(
 				'acp',
 				'ACP_CAT_DOT_MODS',
 				'ACP_DOWNLOADS'
 			)),
			array('module.add', array(
				'acp',
				'ACP_DOWNLOADS',
				array(
					'module_basename'	=> '\oxpus\dlext\acp\main_module',
					'modes'				=> array('overview','config','traffic','categories','files','permissions','stats','banlist','ext_blacklist','toolbox','fields','browser'),
				),
			)),
			array('module.add', array(
				 'ucp',
				 false,
 				'DOWNLOADS'
 			)),
			array('module.add', array(
				'ucp',
				'DOWNLOADS',
				array(
					'module_basename'	=> '\oxpus\dlext\ucp\main_module',
					'modes'				=> array('config','favorite'),
				),
			)),

			array('config.add', array('dl_use_hacklist', '1')),
		);
	}
}
