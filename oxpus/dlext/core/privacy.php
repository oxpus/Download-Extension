<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

class privacy implements privacy_interface
{
	/* phpbb objects */
	protected $db;

	/* extension owned objects */
	protected $dlext_table_dl_stats;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param string								$dlext_table_dl_stats
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		$dlext_table_dl_stats
	)
	{
		$this->db 					= $db;

		$this->dlext_table_dl_stats	= $dlext_table_dl_stats;
	}

	public function dl_privacy()
	{
		$sql = 'UPDATE ' . $this->dlext_table_dl_stats . ' SET ' . $this->db->sql_build_array('UPDATE', [
			'user_ip' => '127.0.0.1'
		]);

		$this->db->sql_query($sql);
	}
}
