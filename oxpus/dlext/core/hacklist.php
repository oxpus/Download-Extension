<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

class hacklist implements hacklist_interface
{
	/* phpbb objects */
	protected $db;

	/* extension owned objects */
	protected $dlext_auth;

	protected $dlext_table_downloads;
	protected $dlext_table_dl_cat;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_table_dl_cat
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\oxpus\dlext\core\auth $dlext_auth,
		$dlext_table_downloads,
		$dlext_table_dl_cat
	)
	{
		$this->db 						= $db;

		$this->dlext_auth				= $dlext_auth;

		$this->dlext_table_downloads	= $dlext_table_downloads;
		$this->dlext_table_dl_cat		= $dlext_table_dl_cat;
	}

	public function hacks_index()
	{
		$dl_auth = $this->dlext_auth->dl_auth();

		$tree_dl = [];

		$sql = 'SELECT id, cat_name, auth_view FROM ' . $this->dlext_table_dl_cat . '
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
		$sql = 'SELECT * FROM ' . $this->dlext_table_downloads . '
				WHERE approve = 1
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
			$total = $this->db->sql_affectedrows();
			$this->db->sql_freeresult($result);

			return $total;
		}
	}
}
