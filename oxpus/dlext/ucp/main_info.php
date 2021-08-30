<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\ucp;

/**
 * @package ucp
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\oxpus\dlext\ucp\main_module',
			'title'		=> 'DOWNLOADS',
			'modes'		=> [
				'ucp_config'	=> [
					'title' => 'DL_CONFIG',		'auth' => 'ext_oxpus/dlext',	'cat' => ['DOWNLOADS']
				],
				'ucp_privacy'	=> [
					'title' => 'DL_PRIVACY',	'auth' => 'ext_oxpus/dlext',	'cat' => ['DOWNLOADS']
				],
				'ucp_favorite'	=> [
					'title' => 'DL_FAVORITE',	'auth' => 'ext_oxpus/dlext',	'cat' => ['DOWNLOADS']
				],
				'config'	=> [
					'title' => 'DL_CONFIG',		'auth' => 'ext_oxpus/dlext',	'cat' => ['DOWNLOADS']
				],
				'dl_privacy'	=> [
					'title' => 'DL_PRIVACY',	'auth' => 'ext_oxpus/dlext',	'cat' => ['DOWNLOADS']
				],
				'favorite'	=> [
					'title' => 'DL_FAVORITE',	'auth' => 'ext_oxpus/dlext',	'cat' => ['DOWNLOADS']
				],
			],
		];
	}
}
