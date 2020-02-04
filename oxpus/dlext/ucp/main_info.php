<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\ucp;

/**
* @package ucp
*/
class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\oxpus\dlext\ucp\main_module',
			'title'		=> 'DOWNLOADS',
			'modes'		=> array(
				'ucp_config'	=> array(
					'title' => 'DL_CONFIG',		'auth' => 'ext_oxpus/dlext',	'cat' => array('DOWNLOADS')
				),
				'ucp_privacy'	=> array(
					'title' => 'DL_PRIVACY',	'auth' => 'ext_oxpus/dlext',	'cat' => array('DOWNLOADS')
				),
				'ucp_favorite'	=> array(
					'title' => 'DL_FAVORITE',	'auth' => 'ext_oxpus/dlext',	'cat' => array('DOWNLOADS')
				),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}
