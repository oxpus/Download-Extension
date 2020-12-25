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

class dlext_hacklist implements dlext_hacklist_interface
{
	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	protected $dlext_cache;

	/**
	* Constructor
	*
	* @param Container 								$phpbb_container
	* @param \phpbb\extension\manager				$phpbb_extension_manager

	* @param \phpbb\db\driver\driver_interfacer		$db
	*/
	public function __construct(
		Container $phpbb_container,

		\phpbb\db\driver\driver_interface $db,
		$dlext_auth
	)
	{
		$this->db 			= $db;

		$this->dlext_auth	= $dlext_auth;
	}

	public function hacks_index()
	{
		$dl_auth = $this->dlext_auth->dl_auth();

		$tree_dl = [];

		$sql = 'SELECT id, cat_name, auth_view FROM ' . DL_CAT_TABLE . '
				ORDER BY parent, sort';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$cat_id = $row['id'];

			if ($row['auth_view'] || (isset($dl_auth[$cat_id]['auth_view']) && $dl_auth[$cat_id]['auth_view']) || $this->dlext_auth->user_admin())
			{
				$tree_dl[$cat_id] = $row['cat_name'];
			}
		}

		return $tree_dl;
	}

	public function all_files($sql_sort_by, $sql_order, $start = 0, $total = 0)
	{
		$sql = 'SELECT * FROM ' . DOWNLOADS_TABLE . '
				WHERE approve = ' . true . '
					AND hacklist = 1
				ORDER BY ' . $this->db->sql_escape($sql_sort_by) . ' ' . $this->db->sql_escape($sql_order);
		if ($total)
		{
			$result = $this->db->sql_query_limit($sql, $total, $start);

			$dl_files = [];

			while ($row = $this->db->sql_fetchrow($result))
			{
				$dl_files[] = $row;
			}

			$this->db->sql_freeresult($result);

			return $dl_files;
		}
		else
		{
			$result = $this->db->sql_query($sql);
			$total = $this->db->sql_affectedrows($result);
			$this->db->sql_freeresult($result);

			return $total;
		}
	}
}
