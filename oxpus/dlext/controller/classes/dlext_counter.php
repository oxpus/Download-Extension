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

class dlext_counter implements dlext_counter_interface
{
	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	protected $dlext_auth;
	protected $dlext_main;

	/**
	* Constructor
	*
	* @param Container 								$phpbb_container

	* @param \phpbb\db\driver\driver_interfacer		$db
	*/
	public function __construct(
		Container $phpbb_container,

		\phpbb\db\driver\driver_interface $db,
		$dlext_auth,
		$dlext_main
	)
	{
		$this->db 			= $db;

		$this->dlext_auth	= $dlext_auth;
		$this->dlext_main	= $dlext_main;
	}

	public function count_dl_approve()
	{
		if (!$this->dlext_auth->user_logged_in())
		{
			return 0;
		}

		$access_cats = array();
		$access_cats = $this->dlext_main->full_index(0, 0, 0, 2);

		if ((!isset($access_cats[0]) || !$access_cats[0] || !sizeof($access_cats)) && !$this->dlext_auth->user_admin())
		{
			return 0;
		}

		$sql_access_cats = ($this->dlext_auth->user_admin()) ? '' : ' AND ' . $this->db->sql_in_set('cat', array_map('intval', $access_cats));

		$sql = 'SELECT COUNT(id) AS total FROM ' . DOWNLOADS_TABLE . "
			WHERE approve = 0
				$sql_access_cats";
		$result = $this->db->sql_query($sql);
		$total = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	public function count_comments_approve()
	{
		if (!$this->dlext_auth->user_logged_in())
		{
			return 0;
		}

		$access_cats = array();
		$access_cats = $this->dlext_main->full_index(0, 0, 0, 2);

		if ((!isset($access_cats[0]) || !$access_cats[0] || !sizeof($access_cats)) && !$this->dlext_auth->user_admin())
		{
			return 0;
		}

		$sql_access_cats = ($this->dlext_auth->user_admin()) ? '' : ' AND ' . $this->db->sql_in_set('cat_id', array_map('intval', $access_cats));

		$sql = 'SELECT COUNT(dl_id) AS total FROM ' . DL_COMMENTS_TABLE . "
			WHERE approve = 0
				$sql_access_cats";
		$result = $this->db->sql_query($sql);
		$total = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}
}
