<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\classes;

use Symfony\Component\DependencyInjection\Container;

class dlext_privacy implements dlext_privacy_interface
{
	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/**
	* Constructor
	*
	* @param Container 								$phpbb_container

	* @param \phpbb\db\driver\driver_interfacer		$db
	*/
	public function __construct(
		Container $phpbb_container,

		\phpbb\db\driver\driver_interface $db
	)
	{
		$this->db 			= $db;
	}

	public function dl_privacy()
	{
		$sql = 'UPDATE ' . DL_STATS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
			'user_ip' => '127.0.0.1'
		));
		$this->db->sql_query($sql);

		return;
	}
}
