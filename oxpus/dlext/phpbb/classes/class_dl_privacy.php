<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2018 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\phpbb\classes;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class dl_privacy extends dl_mod
{
	public static function dl_privacy($db)
	{
		$sql = 'UPDATE ' . DL_STATS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
			'user_ip' => '127.0.0.1'
		));
		$db->sql_query($sql);

		return;
	}
}
