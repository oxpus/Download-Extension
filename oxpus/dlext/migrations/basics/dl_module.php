<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\migrations\basics;

class dl_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['dl_use_hacklist']);
	}

	public static function depends_on()
	{
		return ['\oxpus\dlext\migrations\basics\dl_schema'];
	}

	public function update_data()
	{
		return [
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_DOWNLOADS'
			]],
			['module.add', [
				'acp',
				'ACP_DOWNLOADS',
				[
					'module_basename'	=> '\oxpus\dlext\acp\main_module',
					'modes'				=> ['overview', 'config', 'traffic', 'categories', 'files', 'permissions', 'stats', 'banlist', 'ext_blacklist', 'toolbox', 'fields', 'browser', 'perm_check'],
				],
			]],
			['module.add', [
				'ucp',
				false,
				'DOWNLOADS'
			]],
			['module.add', [
				'ucp',
				'DOWNLOADS',
				[
					'module_basename'	=> '\oxpus\dlext\ucp\main_module',
					'modes'				=> ['ucp_config', 'ucp_favorite', 'ucp_privacy'],
				],
			]],

			['config.add', ['dl_use_hacklist', '1']],
		];
	}
}
