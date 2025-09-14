<?php

/**
 *
 * @package phpBB3
 * @version $Id$
 * @copyright (c) 2005 phpBB Group
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 * taken and modified for
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core\fields;

/**
 * Custom Profile Fields
 * @package phpBB3
 */
class fields
{
	/* phpbb objects */
	protected $db;
	protected $language;
	protected $request;
	protected $template;
	protected $user;
	protected $user_ident;

	/* default field arrays */
	public $profile_types = [FIELD_INT => 'int', FIELD_STRING => 'string', FIELD_TEXT => 'text', FIELD_BOOL => 'bool', FIELD_DROPDOWN => 'dropdown', FIELD_DATE => 'date'];
	public $profile_cache = [];
	public $options_lang = [];

	/* extension owned objects */
	public $field_id;

	protected $dlext_table_dl_fields;
	protected $dlext_table_dl_fields_data;
	protected $dlext_table_dl_fields_lang;
	protected $dlext_table_dl_lang;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\language\language 				$language
	 * @param \phpbb\request\request 				$request
	 * @param phpbb\template\template 				$template
	 * @param \phpbb\user							$user
	 * @param string								$dlext_table_dl_fields
	 * @param string								$dlext_table_dl_fields_data
	 * @param string								$dlext_table_dl_fields_lang
	 * @param string								$dlext_table_dl_lang
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$dlext_table_dl_fields,
		$dlext_table_dl_fields_data,
		$dlext_table_dl_fields_lang,
		$dlext_table_dl_lang
	)
	{
		$this->db 			= $db;
		$this->language 	= $language;
		$this->request 		= $request;
		$this->template 	= $template;
		$this->user 		= $user;

		$this->dlext_table_dl_fields		= $dlext_table_dl_fields;
		$this->dlext_table_dl_fields_data	= $dlext_table_dl_fields_data;
		$this->dlext_table_dl_fields_lang	= $dlext_table_dl_fields_lang;
		$this->dlext_table_dl_lang			= $dlext_table_dl_lang;
	}

	/**
	 * Assign editable fields to template, mode can be profile (for profile change) or register (for registration)
	 * Called by ucp_profile and ucp_register
	 * @access public
	 */
	public function generate_profile_fields($lang_id)
	{
		$sql = 'SELECT l.*, f.*
			FROM ' . $this->dlext_table_dl_lang . ' l, ' . $this->dlext_table_dl_fields . ' f
			WHERE f.field_active = 1
				AND l.lang_id = ' . (int) $lang_id . '
				AND l.field_id = f.field_id
			ORDER BY f.field_order';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Return templated field
			$tpl_snippet = $this->process_field_row('change', $row);

			// Some types are multivalue, we can't give them a field_id as we would not know which to pick
			$type = (int) $row['field_type'];

			$this->template->assign_block_vars('download_fields', [
				'LANG_NAME'		=> $row['lang_name'],
				'LANG_EXPLAIN'	=> $row['lang_explain'],
				'FIELD'			=> $tpl_snippet,
				'FIELD_ID'		=> ($type == FIELD_DATE || ($type == FIELD_BOOL && $row['field_length'] == '1')) ? '' : 'pf_' . $row['field_ident'],
				'S_REQUIRED'	=> $row['field_required'] <> 0,
			]);
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Validate entered profile field data
	 * @access public
	 */
	public function validate_profile_field($field_type, &$field_value, $field_data)
	{
		switch ($field_type)
		{
			case FIELD_DATE:
				$field_validate = explode('-', $field_value);

				$day = (isset($field_validate[0])) ? (int) $field_validate[0] : 0;
				$month = (isset($field_validate[1])) ? (int) $field_validate[1] : 0;
				$year = (isset($field_validate[2])) ? (int) $field_validate[2] : 0;

				if ((!$day || !$month || !$year) && !$field_data['field_required'])
				{
					return false;
				}

				if ((!$day || !$month || !$year) && $field_data['field_required'])
				{
					return 'DL_FIELD_REQUIRED';
				}

				if ($day < 0 || $day > 31 || $month < 0 || $month > 12 || ($year < 1901 && $year > 0) || $year > gmdate('Y', time()) + 50)
				{
					return 'DL_FIELD_INVALID_DATE';
				}

				if (checkdate($month, $day, $year) === false)
				{
					return 'DL_FIELD_INVALID_DATE';
				}
				break;

			case FIELD_BOOL:
				$field_value = (bool) $field_value;

				if (!$field_value && $field_data['field_required'])
				{
					return 'DL_FIELD_REQUIRED';
				}
				break;

			case FIELD_INT:
				if (trim($field_value) === '' && !$field_data['field_required'])
				{
					return false;
				}

				$field_value = (int) $field_value;

				if ($field_value < $field_data['field_minlen'])
				{
					return 'DL_FIELD_TOO_SMALL';
				}
				else if ($field_value > $field_data['field_maxlen'])
				{
					return 'DL_FIELD_TOO_LARGE';
				}
				break;

			case FIELD_DROPDOWN:
				$field_value = (int) $field_value;

				if ($field_value == $field_data['field_novalue'] && $field_data['field_required'])
				{
					return 'DL_FIELD_REQUIRED';
				}
				break;

			case FIELD_STRING:
			case FIELD_TEXT:
				if (trim($field_value) === '' && !$field_data['field_required'])
				{
					return false;
				}
				else if (trim($field_value) === '' && $field_data['field_required'])
				{
					return 'DL_FIELD_REQUIRED';
				}

				if ($field_data['field_minlen'] && utf8_strlen($field_value) < $field_data['field_minlen'])
				{
					return 'DL_FIELD_TOO_SHORT';
				}
				else if ($field_data['field_maxlen'] && utf8_strlen($field_value) > $field_data['field_maxlen'])
				{
					return 'DL_FIELD_TOO_LONG';
				}

				if (!empty($field_data['field_validation']) && $field_data['field_validation'] != '.*')
				{
					$field_validate = ($field_type == FIELD_STRING) ? $field_value : bbcode_nl2br($field_value);
					if (!preg_match('#^' . str_replace('\\\\', '\\', $field_data['field_validation']) . '$#i', $field_validate))
					{
						return 'FIELD_INVALID_CHARS';
					}
				}
				break;
		}

		return false;
	}

	/**
	 * Build profile cache, used for display
	 * @access private
	 */
	public function build_cache()
	{
		$this->profile_cache = [];

		// Display hidden/no_view fields for admin/moderator
		$sql = 'SELECT l.*, f.*
			FROM ' . $this->dlext_table_dl_lang . ' l, ' . $this->dlext_table_dl_fields . ' f
			WHERE l.lang_id = ' . (int) $this->user->get_iso_lang_id() . '
				AND f.field_active = 1
				AND l.field_id = f.field_id
			ORDER BY f.field_order';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->profile_cache[$row['field_ident']] = $row;
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Get language entries for options and store them here for later use
	 */
	public function get_option_lang($field_id, $lang_id, $field_type, $preview)
	{
		if ($preview)
		{
			$lang_options = (!is_array($this->vars['lang_options'])) ? explode("\n", $this->vars['lang_options']) : $this->vars['lang_options'];

			foreach ($lang_options as $num => $var)
			{
				$this->options_lang[$field_id][$lang_id][($num + 1)] = $var;
			}
		}
		else
		{
			$sql = 'SELECT option_id, lang_value
				FROM ' . $this->dlext_table_dl_fields_lang . '
					WHERE field_id = ' . (int) $field_id . '
					AND lang_id = ' . (int) $lang_id . "
					AND field_type = $field_type
				ORDER BY option_id";
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->options_lang[$field_id][$lang_id][($row['option_id'] + 1)] = $row['lang_value'];
			}
			$this->db->sql_freeresult($result);
		}
	}

	/**
	 * Submit profile field for validation
	 * @access public
	 */
	public function submit_cp_field($lang_id, &$cp_data, &$cp_error)
	{
		$sql = 'SELECT l.*, f.*
			FROM ' . $this->dlext_table_dl_lang . ' l, ' . $this->dlext_table_dl_fields . ' f
			WHERE l.lang_id = ' . (int) $lang_id . '
				AND f.field_active = 1
				AND l.field_id = f.field_id
			ORDER BY f.field_order';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$cp_data['pf_' . $row['field_ident']] = $this->get_profile_field($row);
			$check_value = $cp_data['pf_' . $row['field_ident']];

			if (($cp_result = $this->validate_profile_field($row['field_type'], $check_value, $row)) !== false)
			{
				// If not and only showing common error messages, use this one
				$error = '';
				switch ($cp_result)
				{
					case 'DL_FIELD_INVALID_DATE':
					case 'DL_FIELD_REQUIRED':
						$error = $this->language->lang($cp_result, $row['lang_name']);
						break;

					case 'DL_FIELD_TOO_SHORT':
					case 'DL_FIELD_TOO_SMALL':
						$error = $this->language->lang($cp_result, $row['lang_name'], $row['field_minlen']);
						break;

					case 'DL_FIELD_TOO_LONG':
					case 'DL_FIELD_TOO_LARGE':
						$error = $this->language->lang($cp_result, $row['lang_name'], $row['field_maxlen']);
						break;

					case 'FIELD_INVALID_CHARS':
						switch ($row['field_validation'])
						{
							case '[0-9]+':
								$error = $this->language->lang($cp_result . '_NUMBERS_ONLY', $row['lang_name']);
								break;

							case '[\w]+':
								$error = $this->language->lang($cp_result . '_ALPHA_ONLY', $row['lang_name']);
								break;

							case '[\w_\+\. \-\[\]]+':
								$error = $this->language->lang($cp_result . '_SPACERS_ONLY', $row['lang_name']);
								break;
						}
						break;
				}

				if ($error != '')
				{
					$cp_error[] = $error;
				}
			}
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Update profile field data directly
	 */
	public function update_profile_field_data($df_id, &$cp_data)
	{
		$this->dbms = $this->db->get_sql_layer();

		if (empty($cp_data))
		{
			return;
		}

		switch ($this->dbms)
		{
			case 'oracle':
			case 'postgres':
				$right_delim = $left_delim = '"';
				break;

			case 'sqlite':
			case 'sqlite3':
			case 'mssql':
			case 'mssql_odbc':
			case 'mssqlnative':
				$right_delim = ']';
				$left_delim = '[';
				break;

			case 'mysql':
			case 'mysqli':
				$right_delim = $left_delim = '';
				break;
		}

		// use new array for the UPDATE; changes in the key do not affect the original array
		$cp_data_sql = [];
		foreach ($cp_data as $key => $value)
		{
			// Firebird is case sensitive with delimiter
			$cp_data_sql[$left_delim . (($this->dbms == 'oracle') ? strtoupper($key) : $key) . $right_delim] = $value;
		}

		$sql = 'UPDATE ' . $this->dlext_table_dl_fields_data . '
			SET ' . $this->db->sql_build_array('UPDATE', $cp_data_sql) . '
			WHERE df_id = ' . (int) $df_id;
		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows())
		{
			$cp_data_sql['df_id'] = (int) $df_id;

			$sql = 'INSERT INTO ' . $this->dlext_table_dl_fields_data . ' ' . $this->db->sql_build_array('INSERT', $cp_data_sql);
			$this->db->sql_query($sql);
		}
	}

	/**
	 * Assign fields to template, used for viewprofile, viewtopic and memberlist (if load setting is enabled)
	 * This is directly connected to the user -> mode == grab is to grab the user specific fields, mode == show is for assigning the row to the template
	 * @access public
	 */
	public function generate_profile_fields_template($mode, $df_id = 0, $profile_row = [])
	{
		if ($mode == 'grab')
		{
			if (!is_array($df_id))
			{
				$df_id = [$df_id];
			}

			if (empty($this->profile_cache))
			{
				$this->build_cache();
			}

			if (empty($df_id))
			{
				return [];
			}

			$sql = 'SELECT *
				FROM ' . $this->dlext_table_dl_fields_data . '
				WHERE ' . $this->db->sql_in_set('df_id', array_map('intval', $df_id));
			$result = $this->db->sql_query($sql);

			$field_data = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$field_data[$row['df_id']] = $row;
			}
			$this->db->sql_freeresult($result);

			$this->user_fields = [];

			// Go through the fields in correct order
			foreach (array_keys($this->profile_cache) as $used_ident)
			{
				foreach ($field_data as $df_id => $row)
				{
					$this->user_fields[$df_id][$used_ident]['value'] = $row['pf_' . $used_ident];
					$this->user_fields[$df_id][$used_ident]['data'] = $this->profile_cache[$used_ident];
				}
			}

			return $this->user_fields;
		}
		else if ($mode == 'show')
		{
			$tpl_fields = [];
			$tpl_fields['row'] = $tpl_fields['blockrow'] = [];

			foreach ($profile_row as $ident => $ident_ary)
			{
				$value = $this->get_profile_value($ident_ary);

				if ($value === null)
				{
					continue;
				}

				$tpl_fields['row'] += [
					'DL_' . strtoupper($ident) . '_VALUE'	=> $value,
					'DL_' . strtoupper($ident) . '_TYPE'	=> $ident_ary['data']['field_type'],
					'DL_' . strtoupper($ident) . '_NAME'	=> $ident_ary['data']['lang_name'],
					'DL_' . strtoupper($ident) . '_EXPLAIN' => $ident_ary['data']['lang_explain'],

					'S_DL_' . strtoupper($ident)			=> true
				];

				$tpl_fields['blockrow'][] = [
					'DL_FIELD_VALUE'	=> $value,
					'DL_FIELD_TYPE'		=> $ident_ary['data']['field_type'],
					'DL_FIELD_NAME'		=> $ident_ary['data']['lang_name'],
					'DL_FIELD_EXPLAIN'	=> $ident_ary['data']['lang_explain'],

					'S_DL_' . strtoupper($ident)		=> true
				];
			}

			return $tpl_fields;
		}
		else
		{
			trigger_error($this->language->lang('NO_MODE'), E_USER_WARNING);
		}
	}

	/**
	 * Get Profile Value for display
	 */
	public function get_profile_value($ident_ary)
	{
		$value = $ident_ary['value'];
		$field_type = $ident_ary['data']['field_type'];

		switch ($this->profile_types[$field_type])
		{
			case 'int':
				if ($value === '')
				{
					return null;
				}
				return (int) $value;
				break;

			case 'string':
			case 'text':
				if (!$value)
				{
					return null;
				}

				$value = make_clickable($value);
				$value = censor_text($value);
				$value = bbcode_nl2br($value);
				return $value;
				break;

				// case 'datetime':
			case 'date':
				$date = explode('-', $value);
				$day = (isset($date[0])) ? (int) $date[0] : 0;
				$month = (isset($date[1])) ? (int) $date[1] : 0;
				$year = (isset($date[2])) ? (int) $date[2] : 0;

				if (!$day && !$month && !$year)
				{
					return null;
				}
				else if ($day && $month && $year)
				{
					// d/m/y 00:00 GMT isn't necessarily on the same d/m/y in the user's timezone, so add the timezone seconds
					return $this->user->format_date(gmmktime(0, 0, 0, $month, $day, $year), $this->language->lang('DATE_FORMAT'), true);
				}

				return $value;
				break;

			case 'dropdown':
				$this->field_id = $ident_ary['data']['field_id'];
				$lang_id = $ident_ary['data']['lang_id'];
				if (!isset($this->options_lang[$this->field_id][$lang_id]))
				{
					$this->get_option_lang($this->field_id, $lang_id, FIELD_DROPDOWN, false);
				}

				if ($value == $ident_ary['data']['field_novalue'])
				{
					return null;
				}

				$value = (int) $value;

				// User not having a value assigned
				if (!isset($this->options_lang[$this->field_id][$lang_id][$value]))
				{
					return null;
				}

				return $this->options_lang[$this->field_id][$lang_id][$value];
				break;

			case 'bool':
				$this->field_id = $ident_ary['data']['field_id'];
				$lang_id = $ident_ary['data']['lang_id'];
				if (!isset($this->options_lang[$this->field_id][$lang_id]))
				{
					$this->get_option_lang($this->field_id, $lang_id, FIELD_BOOL, false);
				}

				if ($ident_ary['data']['field_length'] == 1)
				{
					return (isset($this->options_lang[$this->field_id][$lang_id][(int) $value])) ? $this->options_lang[$this->field_id][$lang_id][(int) $value] : null;
				}
				else if (!$value || $value == 2)
				{
					return null;
				}
				else
				{
					return $this->options_lang[$this->field_id][$lang_id][(int) ($value) + 1];
				}
				break;

			default:
				trigger_error($this->language->lang('NO_MODE'), E_USER_WARNING);
				break;
		}
	}

	/**
	 * Get field value for registration/profile
	 * @access private
	 */
	public function get_var($field_validation, &$profile_row, $default_value, $preview)
	{
		$profile_row['field_ident'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];
		$this->user_ident = $profile_row['field_ident'];

		$req_field_ident = $this->request->variable($profile_row['field_ident'], (is_numeric($default_value) ? 0 : ''));

		// checkbox - only testing for isset
		if ($profile_row['field_type'] == FIELD_BOOL && $profile_row['field_length'] == 2)
		{
			$value = ($this->request->variable($profile_row['field_ident'], 0)) ? true : ((!isset($this->user->profile_fields[$this->user_ident]) || $preview) ? $default_value : $this->user->profile_fields[$this->user_ident]);
		}
		else if ($profile_row['field_type'] == FIELD_INT)
		{
			if ($req_field_ident)
			{
				$value = $this->request->variable($profile_row['field_ident'], $default_value);
			}
			else
			{
				if (!$preview && array_key_exists($this->user_ident, $this->profile_fields) && is_null($this->profile_fields[$this->user_ident]))
				{
					$value = null;
				}
				else if (!isset($this->profile_fields[$this->user_ident]) || $preview)
				{
					$value = $default_value;
				}
				else
				{
					$value = $this->profile_fields[$this->user_ident];
				}
			}

			return (is_null($value) || $value === '') ? '' : (int) $value;
		}
		else
		{
			$value = ($req_field_ident) ? $this->request->variable($profile_row['field_ident'], $default_value, true) : ((!isset($this->profile_fields[$this->user_ident]) || $preview) ? $default_value : $this->profile_fields[$this->user_ident]);

			if (gettype($value) == 'string')
			{
				$value = utf8_normalize_nfc($value);
			}
		}

		switch ($field_validation)
		{
			case 'int':
				return (int) $value;
				break;
		}

		return $value;
	}

	/**
	 * Process int-type
	 * @access private
	 */
	public function generate_int($profile_row, $preview = false)
	{
		$profile_row['field_value'] = $this->get_var('int', $profile_row, $profile_row['field_default_value'], $preview);
		$this->template->assign_block_vars($this->profile_types[FIELD_INT], array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	 * Process date-type
	 * @access private
	 */
	public function generate_date($profile_row, $preview = false)
	{
		$profile_row['field_ident'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];
		$this->user_ident = $profile_row['field_ident'];

		$now = getdate();

		$req_fd = $this->request->variable($profile_row['field_ident'] . '_day', 0);

		if (!$req_fd)
		{
			if ($profile_row['field_default_value'] == 'now')
			{
				$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
			}
			list($day, $month, $year) = explode('-', ((!isset($this->profile_fields[$this->user_ident]) || $preview) ? $profile_row['field_default_value'] : $this->profile_fields[$this->user_ident]));
		}
		else
		{
			if ($preview && $profile_row['field_default_value'] == 'now')
			{
				$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
				list($day, $month, $year) = explode('-', ((!isset($this->profile_fields[$this->user_ident]) || $preview) ? $profile_row['field_default_value'] : $this->profile_fields[$this->user_ident]));
			}
			else
			{
				$day = $this->request->variable($profile_row['field_ident'] . '_day', 0);
				$month = $this->request->variable($profile_row['field_ident'] . '_month', 0);
				$year = $this->request->variable($profile_row['field_ident'] . '_year', 0);
			}
		}

		$profile_row['s_day_options'] = '<option value="0"' . ((!$day) ? ' selected' : '') . '>--</option>';
		for ($i = 1; $i < 32; ++$i)
		{
			$profile_row['s_day_options'] .= '<option value="' . $i . '"' . (($i == $day) ? ' selected' : '') . ">$i</option>";
		}

		$profile_row['s_month_options'] = '<option value="0"' . ((!$month) ? ' selected' : '') . '>--</option>';
		for ($i = 1; $i < 13; ++$i)
		{
			$profile_row['s_month_options'] .= '<option value="' . $i . '"' . (($i == $month) ? ' selected' : '') . ">$i</option>";
		}

		$profile_row['s_year_options'] = '<option value="0"' . ((!$year) ? ' selected' : '') . '>--</option>';
		for ($i = $now['year'] - 100; $i <= $now['year'] + 100; ++$i)
		{
			$profile_row['s_year_options'] .= '<option value="' . $i . '"' . (($i == $year) ? ' selected' : '') . ">$i</option>";
		}
		unset($now);

		$profile_row['field_value'] = 0;
		$this->template->assign_block_vars($this->profile_types[FIELD_DATE], array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	 * Process bool-type
	 * @access private
	 */
	public function generate_bool($profile_row, $preview = false)
	{
		$value = $this->get_var('int', $profile_row, $profile_row['field_default_value'], $preview);
		$profile_row['field_value'] = $value;
		$this->template->assign_block_vars($this->profile_types[FIELD_BOOL], array_change_key_case($profile_row, CASE_UPPER));

		if ($profile_row['field_length'] == 1)
		{
			if (!isset($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']]) || empty($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']]))
			{
				$this->get_option_lang($profile_row['field_id'], $profile_row['lang_id'], FIELD_BOOL, $preview);
			}

			foreach ($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']] as $option_id => $option_value)
			{
				$this->template->assign_block_vars('bool.options', [
					'OPTION_ID'	=> $option_id,
					'CHECKED'	=> ($value == $option_id) ? ' checked' : '',
					'VALUE'		=> $option_value,
				]);
			}
		}
	}

	/**
	 * Process string-type
	 * @access private
	 */
	public function generate_string($profile_row, $preview = false)
	{
		$profile_row['field_value'] = $this->get_var('string', $profile_row, $profile_row['lang_default_value'], $preview);
		$this->template->assign_block_vars($this->profile_types[FIELD_STRING], array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	 * Process text-type
	 * @access private
	 */
	public function generate_text($profile_row, $preview = false)
	{
		$field_length = explode('|', $profile_row['field_length']);
		$profile_row['field_rows'] = $field_length[0];
		$profile_row['field_cols'] = $field_length[1];

		$profile_row['field_value'] = $this->get_var('string', $profile_row, $profile_row['lang_default_value'], $preview);
		$this->template->assign_block_vars($this->profile_types[FIELD_TEXT], array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	 * Process dropdown-type
	 * @access private
	 */
	public function generate_dropdown($profile_row, $preview = false)
	{
		$value = $this->get_var('int', $profile_row, $profile_row['field_default_value'], $preview);

		if (!isset($this->options_lang[$profile_row['field_id']]) || !isset($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']]) || empty($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']]))
		{
			$this->get_option_lang($profile_row['field_id'], $profile_row['lang_id'], FIELD_DROPDOWN, $preview);
		}

		$profile_row['field_value'] = $value;
		$this->template->assign_block_vars($this->profile_types[FIELD_DROPDOWN], array_change_key_case($profile_row, CASE_UPPER));

		foreach ($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']] as $option_id => $option_value)
		{
			$this->template->assign_block_vars('dropdown.options', [
				'OPTION_ID'	=> $option_id,
				'SELECTED'	=> ($value == $option_id) ? ' selected' : '',
				'VALUE'		=> $option_value,
			]);
		}
	}

	/**
	 * Return Templated value/field. Possible values for $mode are:
	 * change == user is able to set/enter profile values; preview == just show the value
	 * @access private
	 */
	public function process_field_row($mode, $profile_row)
	{
		$preview = $mode == 'preview';

		// set template filename
		$this->template->set_filenames(['cp_body' => '@oxpus_dlext/helpers/dl_custom_fields.html']);

		// empty previously filled blockvars
		foreach ($this->profile_types as $field_type)
		{
			$this->template->destroy_block_vars($field_type);
		}

		// Assign template variables
		$type_func = 'generate_' . $this->profile_types[$profile_row['field_type']];
		$this->$type_func($profile_row, $preview);

		// Return templated data
		return $this->template->assign_display('cp_body');
	}

	/**
	 * Build Array for user insertion into custom profile fields table
	 */
	public function build_insert_sql_array($cp_data)
	{
		$sql_not_in = [];

		foreach (array_keys($cp_data) as $key)
		{
			$sql_not_in[] = (strncmp($key, 'pf_', 3) === 0) ? substr($key, 3) : $key;
		}

		$sql = 'SELECT f.field_type, f.field_ident, f.field_default_value, l.lang_default_value
			FROM ' . $this->dlext_table_dl_lang . ' l, ' . $this->dlext_table_dl_fields . ' f
			WHERE l.lang_id = ' . (int) $this->user->get_iso_lang_id() . '
				' . ((!empty($sql_not_in)) ? ' AND ' . $this->db->sql_in_set('f.field_ident', $sql_not_in, true) : '') . '
				AND l.field_id = f.field_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['field_default_value'] == 'now' && $row['field_type'] == FIELD_DATE)
			{
				$now = getdate();
				$row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
			}

			if ($row['field_default_value'] == 2 && $row['field_type'] == FIELD_BOOL && $row['field_length'] == 2)
			{
				$row['field_default_value'] = false;
			}

			$cp_data['pf_' . $row['field_ident']] = (in_array($row['field_type'], [FIELD_TEXT, FIELD_STRING])) ? $row['lang_default_value'] : $row['field_default_value'];
		}
		$this->db->sql_freeresult($result);

		return $cp_data;
	}

	/**
	 * Get profile field value on submit
	 * @access private
	 */
	public function get_profile_field($profile_row)
	{
		$var_name = 'pf_' . $profile_row['field_ident'];

		switch ($profile_row['field_type'])
		{
			case FIELD_DATE:

				$req_fd = $this->request->variable($var_name . '_day', 0);

				if (!$req_fd)
				{
					if ($profile_row['field_default_value'] == 'now')
					{
						$now = getdate();
						$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
					}
					list($day, $month, $year) = explode('-', $profile_row['field_default_value']);
				}
				else
				{
					$day = $this->request->variable($var_name . '_day', 0);
					$month = $this->request->variable($var_name . '_month', 0);
					$year = $this->request->variable($var_name . '_year', 0);
				}

				$var = sprintf('%2d-%2d-%4d', $day, $month, $year);
				break;

			case FIELD_BOOL:
				// Checkbox
				if ($profile_row['field_length'] == 2)
				{
					$var = ($this->request->variable($var_name, 0)) ? 1 : 2;
				}
				else
				{
					$var = $this->request->variable($var_name, (int) $profile_row['field_default_value']);
				}
				break;

			case FIELD_STRING:
			case FIELD_TEXT:
				$var = $this->request->variable($var_name, (string) $profile_row['field_default_value'], true);
				break;

			case FIELD_INT:
				$req_fl = $this->request->variable($var_name, '', true);
				if ($req_fl === '')
				{
					$var = null;
				}
				else
				{
					$var = $this->request->variable($var_name, (int) $profile_row['field_default_value']);
				}
				break;

			case FIELD_DROPDOWN:
				$var = $this->request->variable($var_name, (int) $profile_row['field_default_value']);
				break;

			default:
				$var = $this->request->variable($var_name, $profile_row['field_default_value']);
				break;
		}

		return $var;
	}

	/**
	 * Get users profile fields
	 */
	public function get_profile_fields($df_id)
	{
		if (isset($this->profile_fields))
		{
			return;
		}

		$sql = 'SELECT *
			FROM ' . $this->dlext_table_dl_fields_data . '
			WHERE df_id = ' . (int) $df_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$this->profile_fields = (!($row = $this->db->sql_fetchrow($result))) ? [] : $row;
		$this->db->sql_freeresult($result);
	}
}
