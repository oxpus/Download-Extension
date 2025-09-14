<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

class files implements files_interface
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames sql_array table_fields

	/* phpbb objects */
	protected $db;
	protected $config;
	protected $user;
	protected $template;

	/* extension owned objects */
	protected $dlext_constants;

	protected $dlext_table_downloads;
	protected $dlext_dlext_images_table;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\user							$user
	 * @param \phpbb\template\template				$template
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_downloads
	 * @param string								$dlext_dlext_images_table
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\user $user,
		\phpbb\template\template $template,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_downloads,
		$dlext_dlext_images_table
	)
	{
		$this->db 						= $db;
		$this->config 					= $config;
		$this->user 					= $user;
		$this->template 				= $template;

		$this->dlext_constants			= $dlext_constants;

		$this->dlext_table_downloads	= $dlext_table_downloads;
		$this->dlext_dlext_images_table	= $dlext_dlext_images_table;
	}

	public function files($cat_id, $sql_sort_by, $sql_order, $start, $limit, $sql_fields = '*', $add_user = false, $thumb_art = 'thumb')
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

		$sql_array['SELECT'] = $sql_fields . ', img.img_name as thumbnail';

		$sql_array['FROM'][$this->dlext_table_downloads] = 'd';

		if ($add_user)
		{
			$sql_array['LEFT_JOIN'][] = [
				'FROM'	=> [USERS_TABLE => 'u'],
				'ON'	=> 'u.user_id = d.add_user'
			];
		}

		switch ($thumb_art)
		{
			case 'thumb':
				$sql_where_thumb = ' AND img.img_index = 1';
			break;
			case 'thumb_list':
				$sql_where_thumb = ' AND img.img_lists = 1';
			break;
			default:
				$sql_where_thumb = '';
		}

		$sql_array['LEFT_JOIN'][] = [
			'FROM'	=> [$this->dlext_dlext_images_table => 'img'],
			'ON'	=> 'img.dl_id = d.id' . (string) $sql_where_thumb
		];

		$sql_array['WHERE'] = 'd.cat = ' . (int) $cat_id . ' AND d.approve = 1';

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

	public function all_files($cat_id, $sort_ary, $extra_where, $df_id, $modcp, $fields, $limit = 0, $limit_start = 0, $thumb_art = 'thumb_list')
	{
		if (!is_array($fields))
		{
			$fields = [$fields];
		}

		if ($fields[0] == '*')
		{
			$sql_fields = 'd.*';
		}
		else
		{
			$sql_fields = $this->_dl_check_fields('downloads', $fields);
		}

		$dl_files = [];

		$sql_array['SELECT'] = $sql_fields . ', au.username add_username, au.user_colour add_user_colour, cu.username change_username, cu.user_colour change_user_colour, img.img_name as thumbnail';

		$sql_array['FROM'][$this->dlext_table_downloads] = 'd';

		$sql_array['LEFT_JOIN'][] = [
			'FROM'	=> [USERS_TABLE => 'au'],
			'ON'	=> 'au.user_id = d.add_user'
		];
		$sql_array['LEFT_JOIN'][] = [
			'FROM'	=> [USERS_TABLE => 'cu'],
			'ON'	=> 'cu.user_id = d.change_user'
		];

		switch ($thumb_art)
		{
			case 'thumb':
				$sql_where_thumb = ' AND img.img_index = 1 ';
			break;
			case 'thumb_list':
				$sql_where_thumb = ' AND img.img_lists = 1 ';
			break;
			default:
				$sql_where_thumb = '';
		}

		$sql_array['LEFT_JOIN'][] = [
			'FROM'	=> [$this->dlext_dlext_images_table => 'img'],
			'ON'	=> 'img.dl_id = d.id' . (string) $sql_where_thumb
		];

		$sql_array['WHERE'] = ($modcp) ? $this->db->sql_in_set('d.approve', [0, 1]) : 'd.approve = 1';
		$sql_array['WHERE'] .= ($cat_id) ? ' AND d.cat = ' . (int) $cat_id . ' ' : '';
		$sql_array['WHERE'] .= ($df_id) ? ' AND d.id = ' . (int) $df_id . ' ' : '';
		$sql_array['WHERE'] .= (empty($extra_where)) ? '' :  ' ' . (string) $this->_dl_check_fields_ary('downloads', $extra_where);

		$sql_array['ORDER_BY']	= (empty($sort_ary)) ? '' : (string) $this->_dl_check_fields_ary('downloads', $sort_ary, 'order');

		$sql = $this->db->sql_build_query('SELECT', $sql_array);

		$sql_limit_start	= intval($limit_start);
		$sql_limit			= intval($limit);

		if ($sql_limit_start && $sql_limit)
		{
			$result = $this->db->sql_query_limit($sql, $sql_limit, $sql_limit_start);
		}
		else if ($sql_limit)
		{
			$result = $this->db->sql_query_limit($sql, $sql_limit);
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

		return ($df_id) ? ((isset($dl_files[0])) ? $dl_files[0] : []) : $dl_files;
	}

	public function dl_sorting($sort_by, $order, &$sql_sort_by = '', &$sql_order = '')
	{
		if ($this->config['dl_sort_preform'])
		{
			$sort_by = 0;
			$order = 'ASC';
		}
		else
		{
			$sort_by = (!$sort_by) ? $this->user->data['user_dl_sort_fix'] : $sort_by;
			$order = (!$order) ? (($this->user->data['user_dl_sort_dir']) ? 'DESC' : 'ASC') : $order;
		}

		switch ($sort_by)
		{
			case $this->dlext_constants::DL_SORT_DESCRIPTION:
				$sql_sort_by = 'description';
				break;
			case $this->dlext_constants::DL_SORT_FILE_NAME:
				$sql_sort_by = 'file_name';
				break;
			case $this->dlext_constants::DL_SORT_CLICKS:
				$sql_sort_by = 'klicks';
				break;
			case $this->dlext_constants::DL_SORT_FREE:
				$sql_sort_by = 'free';
				break;
			case $this->dlext_constants::DL_SORT_EXTERN:
				$sql_sort_by = 'extern';
				break;
			case $this->dlext_constants::DL_SORT_FILE_SIZE:
				$sql_sort_by = 'file_size';
				break;
			case $this->dlext_constants::DL_SORT_LAST_TIME:
				$sql_sort_by = 'change_time';
				break;
			case $this->dlext_constants::DL_SORT_RATING:
				$sql_sort_by = 'rating';
				break;
			default:
				$sql_sort_by = 'sort';
		}

		$sql_order = ($order == 'DESC') ? 'DESC' : 'ASC';

		if (!$this->config['dl_sort_preform'] && $this->user->data['user_dl_sort_opt'])
		{
			$this->template->assign_var('S_DL_SORT_OPTIONS', $this->dlext_constants::DL_TRUE);

			$selected_default		= ($sort_by == $this->dlext_constants::DL_SORT_DEFAULT) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$selected_description	= ($sort_by == $this->dlext_constants::DL_SORT_DESCRIPTION) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$selected_filename		= ($sort_by == $this->dlext_constants::DL_SORT_FILE_NAME) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$selected_clicks		= ($sort_by == $this->dlext_constants::DL_SORT_CLICKS) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$selected_free			= ($sort_by == $this->dlext_constants::DL_SORT_FREE) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$selected_extern		= ($sort_by == $this->dlext_constants::DL_SORT_EXTERN) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$selected_filesize		= ($sort_by == $this->dlext_constants::DL_SORT_FILE_SIZE) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$selected_lasttime		= ($sort_by == $this->dlext_constants::DL_SORT_LAST_TIME) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$selected_rating		= ($sort_by == $this->dlext_constants::DL_SORT_RATING) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

			$selected_sort_asc		= ($order == 'ASC') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
			$selected_sort_desc		= ($order == 'DESC') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

			$this->template->assign_vars([
				'S_DL_SELECTED_DEFEAULT'	=> $selected_default,
				'S_DL_SELECTED_DESCRIPTION'	=> $selected_description,
				'S_DL_SELECTED_FILENAME'	=> $selected_filename,
				'S_DL_SELECTED_CLICKS'		=> $selected_clicks,
				'S_DL_SELECTED_FREE'		=> $selected_free,
				'S_DL_SELECTED_EXTERN'		=> $selected_extern,
				'S_DL_SELECTED_FILESIZE'	=> $selected_filesize,
				'S_DL_SELECTED_LASTTIME'	=> $selected_lasttime,
				'S_DL_SELECTED_RATING'		=> $selected_rating,

				'S_DL_SELECTED_SORT_ASC'	=> $selected_sort_asc,
				'S_DL_SELECTED_SORT_DESC'	=> $selected_sort_desc,
			]);
		}
	}

	private function _dl_check_fields($table, $fields)
	{
		$table_fields_data = $this->_dl_sql_allowed_fields($table);
		$table_fields = $table_fields_data['fields'];
		$table_alias = $table_fields_data['alias'];

		$fields_ary = [];

		foreach ($fields as $field)
		{
			$field = strtolower($field);

			if (in_array($field, $table_fields))
			{
				$fields_ary[] = $table_alias . $field;
			}
		}

		return implode(', ', $fields_ary);
	}

	private function _dl_check_fields_ary($table, $fields_ary, $part = 'where')
	{
		$table_fields_data = $this->_dl_sql_allowed_fields($table);
		$table_fields = $table_fields_data['fields'];
		$table_alias = $table_fields_data['alias'];

		$sql_ary = [];
		$last_ary = [];

		foreach ($fields_ary as $field => $data)
		{
			$field = strtolower($field);

			if ($field == '{cat_perm}')
			{
				$field = 'cat';
			}

			if (in_array($field, $table_fields))
			{
				if ($part == 'order')
				{
					$data = strtoupper($data);

					if (!in_array($data, ['ASC', 'DESC']))
					{
						return '';
					}

					$sql_ary[] = $table_alias . $field . ' ' . $data;
				}
				else
				{
					$prefix		= strtoupper($data[0]);
					$operator	= strtoupper($data[1]);
					$condition	= $this->db->sql_escape($data[2]);

					if (!in_array($prefix, ['AND', 'OR']))
					{
						return '';
					}

					if ($prefix == 'OR')
					{
						if (!empty($sql_ary) && !empty($last_ary))
						{
							$sql_id = count($sql_ary) - 1;

							$sql_ary[$sql_id] = $last_ary['prefix'] . ' (' . $last_ary['table_alias'] . $last_ary['field'] . ' ' . $last_ary['operator'] . ' ' . $last_ary['conditions'];
						}
					}

					if ($condition == '~')
					{
						$condition = "''";
					}

					if (in_array($operator, ['NULL', 'NOT NULL']))
					{
						$operator	= 'IS ' . $operator;
						$condition	= '';
					}

					if (in_array($operator, ['IN', 'NOT IN']))
					{
						$field		= $condition;
						$operator	= '';
						$condition	= '';
					}

					$last_ary = [
						'prefix'		=> $prefix,
						'table_alias'	=> $table_alias,
						'field'			=> $field,
						'operator'		=> $operator,
						'conditions'	=> $condition,
					];

					if ($prefix == 'OR')
					{
						$suffix = ')';
					}
					else
					{
						$suffix = '';
					}

					$sql_ary[] = $prefix . ' ' . $table_alias . $field . ' ' . $operator . ' ' . $condition . $suffix;
				}
			}
		}

		if (empty($sql_ary))
		{
			return '';
		}

		if ($part == 'order')
		{
			return implode(', ', $sql_ary);
		}

		return implode(' ', $sql_ary);
	}

	private function _dl_sql_allowed_fields($table)
	{
		$table_fields['downloads']['alias'] = 'd.';

		$table_fields['downloads']['fields'] = [
			'id',
			'description',
			'file_name',
			'klicks',
			'free',
			'extern',
			'long_desc',
			'sort',
			'cat',
			'hacklist',
			'hack_author',
			'hack_author_email',
			'hack_author_website',
			'hack_version',
			'hack_dl_url',
			'test',
			'req',
			'todo',
			'warning',
			'mod_desc',
			'mod_list',
			'file_size',
			'change_time',
			'rating',
			'file_traffic',
			'overall_klicks',
			'approve',
			'add_time',
			'add_user',
			'change_user',
			'last_time',
			'down_user',
			'broken',
			'mod_desc_uid',
			'mod_desc_bitfield',
			'mod_desc_flags',
			'long_desc_uid',
			'long_desc_bitfield',
			'long_desc_flags',
			'desc_uid',
			'desc_bitfield',
			'desc_flags',
			'warn_uid',
			'warn_bitfield',
			'warn_flags',
			'dl_topic',
			'real_file',
			'todo_uid',
			'todo_bitfield',
			'todo_flags',
			'file_hash',
			'dl_pdf_pages',
			'dl_pdf_done',
		];

		return $table_fields[$table];
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
