<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\ucp;

class main_info
{
	function module()
	{
		global $config;

		return array(
			'filename'	=> '\oxpus\dlext\ucp\main_module',
			'title'		=> 'DOWNLOADS',
			'version'	=> $config['dl_ext_version'],
			'modes'		=> array(
				'config'		=> array('title' => 'DL_CONFIG',	'auth' => 'ext_oxpus/dlext',	'cat' => array('DOWNLOADS')),
				'favorite'		=> array('title' => 'DL_FAVORITE',	'auth' => 'ext_oxpus/dlext',	'cat' => array('DOWNLOADS')),
				'dl_privacy'	=> array('title' => 'DL_PRIVACY',	'auth' => 'ext_oxpus/dlext',	'cat' => array('DOWNLOADS')),
			),
		);
	}
}
