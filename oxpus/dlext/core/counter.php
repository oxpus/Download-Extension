<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

class counter implements counter_interface
{
	/* phpbb objects */
	protected $db;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_main;
	protected $dlext_constants;

	protected $dlext_table_dl_comments;
	protected $dlext_table_downloads;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_comments
	 * @param string								$dlext_table_downloads
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_comments,
		$dlext_table_downloads
	)
	{
		$this->db 				= $db;

		$this->dlext_auth		= $dlext_auth;
		$this->dlext_main		= $dlext_main;
		$this->dlext_constants	= $dlext_constants;

		$this->dlext_table_dl_comments	= $dlext_table_dl_comments;
		$this->dlext_table_downloads	= $dlext_table_downloads;
	}

	public function count_dl_broken()
	{
		$access_cats = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_MOD);

		if (empty($access_cats))
		{
			return 0;
		}

		$sql_access_cats = ($this->dlext_auth->user_admin()) ? '' : ' AND ' . $this->db->sql_in_set('cat', $access_cats);

		$sql = 'SELECT COUNT(id) AS total, min(id) as first_dl FROM ' . $this->dlext_table_downloads . "
			WHERE broken = 1
				$sql_access_cats";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$total = $row['total'];
		$first_dl = $row['first_dl'];
		$this->db->sql_freeresult($result);

		return ['total' => $total, 'df_id' => $first_dl];
	}

	public function count_dl_approve()
	{
		$access_cats = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_MOD);

		if (empty($access_cats))
		{
			return 0;
		}

		$sql_access_cats = ($this->dlext_auth->user_admin()) ? '' : ' AND ' . $this->db->sql_in_set('cat', $access_cats);

		$sql = 'SELECT COUNT(id) AS total FROM ' . $this->dlext_table_downloads . "
			WHERE approve = 0
				$sql_access_cats";
		$result = $this->db->sql_query($sql);
		$total = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	public function count_comments_approve()
	{
		$access_cats = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_MOD);

		if (empty($access_cats))
		{
			return 0;
		}

		$sql_access_cats = ($this->dlext_auth->user_admin()) ? '' : ' AND ' . $this->db->sql_in_set('cat_id', $access_cats);

		$sql = 'SELECT COUNT(dl_id) AS total FROM ' . $this->dlext_table_dl_comments . "
			WHERE approve = 0
				$sql_access_cats";
		$result = $this->db->sql_query($sql);
		$total = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	public function count_external_files()
	{
		$access_cats = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_VIEW);

		if (empty($access_cats))
		{
			return 0;
		}

		$sql_access_cats = ($this->dlext_auth->user_admin()) ? '' : ' AND ' . $this->db->sql_in_set('cat', $access_cats);

		$sql = 'SELECT COUNT(id) AS total FROM ' . $this->dlext_table_downloads . "
			WHERE extern = 1
				$sql_access_cats";
		$result = $this->db->sql_query($sql);
		$total = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}
}
