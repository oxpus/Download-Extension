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

class dlext_files implements dlext_files_interface
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

	public function files($cat_id, $sql_sort_by, $sql_order, $start, $limit, $sql_fields = '*', $add_user = false)
	{
		$dl_files = [];

		if ($sql_fields == '*')
		{
			$sql_fields = 'd.*';
		}
		else
		{
			$fields = explode(', ', $sql_fields);
			$sql_fields = 'd.' . implode(', d.', $fields);
			if ($add_user)
			{
				$sql_fields .= ', u.username, u.user_colour';
			}
		}

		$sql_array['SELECT'] = $sql_fields;

		$sql_array['FROM'][DOWNLOADS_TABLE] = 'd';

		if ($add_user)
		{
			$sql_array['LEFT_JOIN'][] = [
				'FROM'	=> [USERS_TABLE => 'u'],
				'ON'	=> 'u.user_id = d.add_user'
			];
		}

		$sql_array['WHERE'] = 'd.cat = ' . (int) $cat_id . ' AND d.approve = ' . true;

		$sql_array['ORDER_BY']	= $this->db->sql_escape($sql_sort_by) . ' ' . $this->db->sql_escape($sql_order);

		$sql = $this->db->sql_build_query('SELECT', $sql_array);

		if ($limit)
		{
			$result = $this->db->sql_query_limit($sql, $limit, $start);
		}
		else
		{
			$result = $this->db->sql_query($sql);
		}

		while ($row = $this->db->sql_fetchrow($result))
		{
			$dl_files[] = $row;
		}

		$this->db->sql_freeresult($result);

		return $dl_files;
	}

	public function all_files($cat_id, $sql_sort_by, $sql_order, $extra_where, $df_id, $modcp, $sql_fields, $sql_limit = 0, $sql_limit_start = 0)
	{
		$dl_files = [];

		$sql = 'SELECT ' . $this->db->sql_escape($sql_fields) . ' FROM ' . DOWNLOADS_TABLE;
		$sql .= ($modcp) ? ' WHERE ' . $this->db->sql_in_set('approve', [0, 1]) : ' WHERE approve = ' . true;
		$sql .= ($cat_id) ? ' AND cat = ' . (int) $cat_id . ' ' : '';
		$sql .= ($df_id) ? ' AND id = ' . (int) $df_id . ' ' : '';
		$sql .= ($extra_where) ? ' ' . str_replace("\'\'", "''", $this->db->sql_escape($extra_where)) . ' ' : '';
		$sql .= ($sql_sort_by) ? ' ORDER BY ' . $this->db->sql_escape($sql_sort_by) . ' ' . $this->db->sql_escape($sql_order) : '';
		if ($sql_limit_start)
		{
			$sql .= ' LIMIT ' . (int) $sql_limit_start;
			if ($sql_limit)
			{
				$sql .= ', ' . (int) $sql_limit;
			}
		}
		else
		{
			$sql .= ($sql_limit) ? ' LIMIT ' . (int) $sql_limit : '';
		}

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$dl_files[] = $row;
		}

		$this->db->sql_freeresult($result);

		return ($df_id) ? ((isset($dl_files[0])) ? $dl_files[0] :[]) : $dl_files;
	}
}
