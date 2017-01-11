<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dl_ext\ucp;

class main_info
{
	function module()
	{
		global $config;

		return array(
			'filename'	=> '\oxpus\dl_ext\ucp\main_info',
			'title'		=> 'DOWNLOADS',
			'version'	=> $config['dl_ext_version'],
			'modes'		=> array(
				'config'	=> array('title' => 'DL_CONFIG',	'auth' => 'ext_oxpus/dl_ext',	'cat' => array('DOWNLOADS')),
				'favorite'	=> array('title' => 'DL_FAVORITE',	'auth' => 'ext_oxpus/dl_ext',	'cat' => array('DOWNLOADS')),
			),
		);
	}
}
