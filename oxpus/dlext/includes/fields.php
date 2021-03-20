<?php

/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
* taken and modified for
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\includes;

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Custom Profile Fields
* @package phpBB3
*/
class custom_profile
{
	var $profile_types = [FIELD_INT => 'int', FIELD_STRING => 'string', FIELD_TEXT => 'text', FIELD_BOOL => 'bool', FIELD_DROPDOWN => 'dropdown', FIELD_DATE => 'date'];
	var $profile_cache = [];
	var $options_lang = [];

	const DL_FIELDS_TABLE		= 'dl_fields';
	const DL_FIELDS_DATA_TABLE	= 'dl_fields_data';
	const DL_FIELDS_LANG_TABLE	= 'dl_fields_lang';
	const DL_LANG_TABLE			= 'dl_lang';

	/**
	* Assign editable fields to template, mode can be profile (for profile change) or register (for registration)
	* Called by ucp_profile and ucp_register
	* @access public
	*/
	function generate_profile_fields($lang_id)
	{
		global $db, $template, $auth, $table_prefix;

		$sql = 'SELECT l.*, f.*
			FROM ' . $table_prefix . self::DL_LANG_TABLE . ' l, ' . $table_prefix . self::DL_FIELDS_TABLE . ' f
			WHERE f.field_active = 1
				AND l.lang_id = ' . (int) $lang_id . '
				AND l.field_id = f.field_id
			ORDER BY f.field_order';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// Return templated field
			$tpl_snippet = $this->process_field_row('change', $row);

			// Some types are multivalue, we can't give them a field_id as we would not know which to pick
			$type = (int) $row['field_type'];

			$template->assign_block_vars('download_fields', [
				'LANG_NAME'		=> $row['lang_name'],
				'LANG_EXPLAIN'	=> $row['lang_explain'],
				'FIELD'			=> $tpl_snippet,
				'FIELD_ID'		=> ($type == FIELD_DATE || ($type == FIELD_BOOL && $row['field_length'] == '1')) ? '' : 'pf_' . $row['field_ident'],
				'S_REQUIRED'	=> ($row['field_required']) ? true : false,
			]);
		}
		$db->sql_freeresult($result);
	}

	/**
	* Validate entered profile field data
	* @access public
	*/
	function validate_profile_field($field_type, &$field_value, $field_data)
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
	function build_cache()
	{
		global $db, $user, $auth, $table_prefix;

		$this->profile_cache = [];

		// Display hidden/no_view fields for admin/moderator
		$sql = 'SELECT l.*, f.*
			FROM ' . $table_prefix . self::DL_LANG_TABLE . ' l, ' . $table_prefix . self::DL_FIELDS_TABLE . ' f
			WHERE l.lang_id = ' . (int) $user->get_iso_lang_id() . '
				AND f.field_active = 1
				AND l.field_id = f.field_id
			ORDER BY f.field_order';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$this->profile_cache[$row['field_ident']] = $row;
		}
		$db->sql_freeresult($result);
	}

	/**
	* Get language entries for options and store them here for later use
	*/
	function get_option_lang($field_id, $lang_id, $field_type, $preview)
	{
		global $db, $table_prefix;

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
				FROM ' . $table_prefix . self::DL_FIELDS_LANG_TABLE . '
					WHERE field_id = ' . (int) $field_id . '
					AND lang_id = ' . (int) $lang_id . "
					AND field_type = $field_type
				ORDER BY option_id";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$this->options_lang[$field_id][$lang_id][($row['option_id'] + 1)] = $row['lang_value'];
			}
			$db->sql_freeresult($result);
		}
	}

	/**
	* Submit profile field for validation
	* @access public
	*/
	function submit_cp_field($lang_id, &$cp_data, &$cp_error)
	{
		global $auth, $db, $user, $language, $table_prefix;

		$sql = 'SELECT l.*, f.*
			FROM ' . $table_prefix . self::DL_LANG_TABLE . ' l, ' . $table_prefix . self::DL_FIELDS_TABLE . ' f
			WHERE l.lang_id = ' . (int) $lang_id . '
				AND f.field_active = 1
				AND l.field_id = f.field_id
			ORDER BY f.field_order';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
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
						$error = $language->lang($cp_result, $row['lang_name']);
					break;

					case 'DL_FIELD_TOO_SHORT':
					case 'DL_FIELD_TOO_SMALL':
						$error = $language->lang($cp_result, $row['lang_name'], $row['field_minlen']);
					break;

					case 'DL_FIELD_TOO_LONG':
					case 'DL_FIELD_TOO_LARGE':
						$error = $language->lang($cp_result, $row['lang_name'], $row['field_maxlen']);
					break;

					case 'FIELD_INVALID_CHARS':
						switch ($row['field_validation'])
						{
							case '[0-9]+':
								$error = $language->lang($cp_result . '_NUMBERS_ONLY', $row['lang_name']);
							break;

							case '[\w]+':
								$error = $language->lang($cp_result . '_ALPHA_ONLY', $row['lang_name']);
							break;

							case '[\w_\+\. \-\[\]]+':
								$error = $language->lang($cp_result . '_SPACERS_ONLY', $row['lang_name']);
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
		$db->sql_freeresult($result);
	}

	/**
	* Update profile field data directly
	*/
	function update_profile_field_data($df_id, &$cp_data)
	{
		global $db, $dbms, $table_prefix;

		if (empty($cp_data))
		{
			return;
		}

		switch ($dbms)
		{
			case 'phpbb\\db\\driver\\oracle':
			case 'phpbb\\db\\driver\\postgres':
				$right_delim = $left_delim = '"';
			break;

			case 'phpbb\\db\\driver\\sqlite':
			case 'phpbb\\db\\driver\\sqlite3':
			case 'phpbb\\db\\driver\\mssql':
			case 'phpbb\\db\\driver\\mssql_odbc':
			case 'phpbb\\db\\driver\\mssqlnative':
				$right_delim = ']';
				$left_delim = '[';
			break;

			case 'phpbb\\db\\driver\\mysql':
			case 'phpbb\\db\\driver\\mysqli':
				$right_delim = $left_delim = '';
			break;
		}

		// use new array for the UPDATE; changes in the key do not affect the original array
		$cp_data_sql = [];
		foreach ($cp_data as $key => $value)
		{
			// Firebird is case sensitive with delimiter
			$cp_data_sql[$left_delim . (($dbms == 'oracle') ? strtoupper($key) : $key) . $right_delim] = $value;
		}

		$sql = 'UPDATE ' . $table_prefix . self::DL_FIELDS_DATA_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $cp_data_sql) . '
			WHERE df_id = ' . (int) $df_id;
		$db->sql_query($sql);

		if (!$db->sql_affectedrows())
		{
			$cp_data_sql['df_id'] = (int) $df_id;

			$db->sql_return_on_error(true);

			$sql = 'INSERT INTO ' . $table_prefix . self::DL_FIELDS_DATA_TABLE . ' ' . $db->sql_build_array('INSERT', $cp_data_sql);
			$db->sql_query($sql);

			$db->sql_return_on_error(false);
		}
	}

	/**
	* Assign fields to template, used for viewprofile, viewtopic and memberlist (if load setting is enabled)
	* This is directly connected to the user -> mode == grab is to grab the user specific fields, mode == show is for assigning the row to the template
	* @access public
	*/
	function generate_profile_fields_template($mode, $df_id = 0, $profile_row = [])
	{
		global $db, $user, $table_prefix, $language;

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
				FROM ' . $table_prefix . self::DL_FIELDS_DATA_TABLE . '
				WHERE ' . $db->sql_in_set('df_id', array_map('intval', $df_id));
			$result = $db->sql_query($sql);

			$field_data = [];
			while ($row = $db->sql_fetchrow($result))
			{
				$field_data[$row['df_id']] = $row;
			}
			$db->sql_freeresult($result);

			$user_fields = [];

			// Go through the fields in correct order
			foreach (array_keys($this->profile_cache) as $used_ident)
			{
				foreach ($field_data as $df_id => $row)
				{
					$user_fields[$df_id][$used_ident]['value'] = $row['pf_' . $used_ident];
					$user_fields[$df_id][$used_ident]['data'] = $this->profile_cache[$used_ident];
				}
			}

			return $user_fields;
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
					'DL_' . strtoupper($ident) . '_EXPLAIN'=> $ident_ary['data']['lang_explain'],

					'S_DL_' . strtoupper($ident)			=> true
				];

				$tpl_fields['blockrow'][] = [
					'DL_FIELD_VALUE'	=> $value,
					'DL_FIELD_TYPE'	=> $ident_ary['data']['field_type'],
					'DL_FIELD_NAME'	=> $ident_ary['data']['lang_name'],
					'DL_FIELD_EXPLAIN'	=> $ident_ary['data']['lang_explain'],

					'S_DL_' . strtoupper($ident)		=> true
				];
			}

			return $tpl_fields;
		}
		else
		{
			trigger_error($language->lang('NO_MODE'), E_USER_ERROR);
		}
	}

	/**
	* Get Profile Value for display
	*/
	function get_profile_value($ident_ary)
	{
		global $user, $language;

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
					global $user;
					// d/m/y 00:00 GMT isn't necessarily on the same d/m/y in the user's timezone, so add the timezone seconds
					return $user->format_date(gmmktime(0, 0, 0, $month, $day, $year) + $user->timezone + $user->dst, $language->lang('DATE_FORMAT'), true);
				}

				return $value;
			break;

			case 'dropdown':
				$field_id = $ident_ary['data']['field_id'];
				$lang_id = $ident_ary['data']['lang_id'];
				if (!isset($this->options_lang[$field_id][$lang_id]))
				{
					$this->get_option_lang($field_id, $lang_id, FIELD_DROPDOWN, false);
				}

				if ($value == $ident_ary['data']['field_novalue'])
				{
					return null;
				}

				$value = (int) $value;

				// User not having a value assigned
				if (!isset($this->options_lang[$field_id][$lang_id][$value]))
				{
					return null;
				}

				return $this->options_lang[$field_id][$lang_id][$value];
			break;

			case 'bool':
				$field_id = $ident_ary['data']['field_id'];
				$lang_id = $ident_ary['data']['lang_id'];
				if (!isset($this->options_lang[$field_id][$lang_id]))
				{
					$this->get_option_lang($field_id, $lang_id, FIELD_BOOL, false);
				}

				if ($ident_ary['data']['field_length'] == 1)
				{
					return (isset($this->options_lang[$field_id][$lang_id][(int) $value])) ? $this->options_lang[$field_id][$lang_id][(int) $value] : null;
				}
				else if (!$value || $value == 2)
				{
					return null;
				}
				else
				{
					return $this->options_lang[$field_id][$lang_id][(int) ($value) + 1];
				}
			break;

			default:
				trigger_error($language->lang('NO_MODE'), E_USER_ERROR);
			break;
		}
	}

	/**
	* Get field value for registration/profile
	* @access private
	*/
	function get_var($field_validation, &$profile_row, $default_value, $preview)
	{
		global $user, $request;

		$profile_row['field_ident'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];
		$user_ident = $profile_row['field_ident'];

		$req_field_ident = $request->variable($profile_row['field_ident'], (is_numeric($default_value) ? 0 : ''));

		// checkbox - only testing for isset
		if ($profile_row['field_type'] == FIELD_BOOL && $profile_row['field_length'] == 2)
		{
			$value = (isset($_REQUEST[$profile_row['field_ident']])) ? true : ((!isset($user->profile_fields[$user_ident]) || $preview) ? $default_value : $user->profile_fields[$user_ident]);
		}
		else if ($profile_row['field_type'] == FIELD_INT)
		{
			if ($req_field_ident)
			{
				$value = $request->variable($profile_row['field_ident'], $default_value);
			}
			else
			{
				if (!$preview && array_key_exists($user_ident, $this->profile_fields) && is_null($this->profile_fields[$user_ident]))
				{
					$value = null;
				}
				else if (!isset($this->profile_fields[$user_ident]) || $preview)
				{
					$value = $default_value;
				}
				else
				{
					$value = $this->profile_fields[$user_ident];
				}
			}

			return (is_null($value) || $value === '') ? '' : (int) $value;
		}
		else
		{
			$value = ($req_field_ident) ? $request->variable($profile_row['field_ident'], $default_value, true) : ((!isset($this->profile_fields[$user_ident]) || $preview) ? $default_value : $this->profile_fields[$user_ident]);

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
	function generate_int($profile_row, $preview = false)
	{
		global $template;

		$profile_row['field_value'] = $this->get_var('int', $profile_row, $profile_row['field_default_value'], $preview);
		$template->assign_block_vars($this->profile_types[$profile_row['field_type']], array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	* Process date-type
	* @access private
	*/
	function generate_date($profile_row, $preview = false)
	{
		global $user, $template, $request;

		$profile_row['field_ident'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];
		$user_ident = $profile_row['field_ident'];

		$now = getdate();

		$req_fd = $request->variable($profile_row['field_ident'] . '_day', 0);

		if (!$req_fd)
		{
			if ($profile_row['field_default_value'] == 'now')
			{
				$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
			}
			list($day, $month, $year) = explode('-', ((!isset($this->profile_fields[$user_ident]) || $preview) ? $profile_row['field_default_value'] : $this->profile_fields[$user_ident]));
		}
		else
		{
			if ($preview && $profile_row['field_default_value'] == 'now')
			{
				$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
				list($day, $month, $year) = explode('-', ((!isset($this->profile_fields[$user_ident]) || $preview) ? $profile_row['field_default_value'] : $this->profile_fields[$user_ident]));
			}
			else
			{
				$day = $request->variable($profile_row['field_ident'] . '_day', 0);
				$month = $request->variable($profile_row['field_ident'] . '_month', 0);
				$year = $request->variable($profile_row['field_ident'] . '_year', 0);
			}
		}

		$profile_row['s_day_options'] = '<option value="0"' . ((!$day) ? ' selected="selected"' : '') . '>--</option>';
		for ($i = 1; $i < 32; ++$i)
		{
			$profile_row['s_day_options'] .= '<option value="' . $i . '"' . (($i == $day) ? ' selected="selected"' : '') . ">$i</option>";
		}

		$profile_row['s_month_options'] = '<option value="0"' . ((!$month) ? ' selected="selected"' : '') . '>--</option>';
		for ($i = 1; $i < 13; ++$i)
		{
			$profile_row['s_month_options'] .= '<option value="' . $i . '"' . (($i == $month) ? ' selected="selected"' : '') . ">$i</option>";
		}

		$profile_row['s_year_options'] = '<option value="0"' . ((!$year) ? ' selected="selected"' : '') . '>--</option>';
		for ($i = $now['year'] - 100; $i <= $now['year'] + 100; ++$i)
		{
			$profile_row['s_year_options'] .= '<option value="' . $i . '"' . (($i == $year) ? ' selected="selected"' : '') . ">$i</option>";
		}
		unset($now);

		$profile_row['field_value'] = 0;
		$template->assign_block_vars($this->profile_types[$profile_row['field_type']], array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	* Process bool-type
	* @access private
	*/
	function generate_bool($profile_row, $preview = false)
	{
		global $template, $db;

		$value = $this->get_var('int', $profile_row, $profile_row['field_default_value'], $preview);
		$profile_row['field_value'] = $value;
		$template->assign_block_vars($this->profile_types[$profile_row['field_type']], array_change_key_case($profile_row, CASE_UPPER));

		if ($profile_row['field_length'] == 1)
		{
			if (!isset($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']]) || empty($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']]))
			{
				$this->get_option_lang($profile_row['field_id'], $profile_row['lang_id'], FIELD_BOOL, $preview);
			}

			foreach ($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']] as $option_id => $option_value)
			{
				$template->assign_block_vars('bool.options', [
					'OPTION_ID'	=> $option_id,
					'CHECKED'	=> ($value == $option_id) ? ' checked="checked"' : '',
					'VALUE'		=> $option_value,
				]);
			}
		}
	}

	/**
	* Process string-type
	* @access private
	*/
	function generate_string($profile_row, $preview = false)
	{
		global $template;

		$profile_row['field_value'] = $this->get_var('string', $profile_row, $profile_row['lang_default_value'], $preview);
		$template->assign_block_vars($this->profile_types[$profile_row['field_type']], array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	* Process text-type
	* @access private
	*/
	function generate_text($profile_row, $preview = false)
	{
		global $template;
		global $user, $phpEx, $phpbb_root_path;

		$field_length = explode('|', $profile_row['field_length']);
		$profile_row['field_rows'] = $field_length[0];
		$profile_row['field_cols'] = $field_length[1];

		$profile_row['field_value'] = $this->get_var('string', $profile_row, $profile_row['lang_default_value'], $preview);
		$template->assign_block_vars($this->profile_types[$profile_row['field_type']], array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	* Process dropdown-type
	* @access private
	*/
	function generate_dropdown($profile_row, $preview = false)
	{
		global $user, $template;

		$value = $this->get_var('int', $profile_row, $profile_row['field_default_value'], $preview);

		if (!isset($this->options_lang[$profile_row['field_id']]) || !isset($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']]) || empty($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']]))
		{
			$this->get_option_lang($profile_row['field_id'], $profile_row['lang_id'], FIELD_DROPDOWN, $preview);
		}

		$profile_row['field_value'] = $value;
		$template->assign_block_vars($this->profile_types[$profile_row['field_type']], array_change_key_case($profile_row, CASE_UPPER));

		foreach ($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']] as $option_id => $option_value)
		{
			$template->assign_block_vars('dropdown.options', [
				'OPTION_ID'	=> $option_id,
				'SELECTED'	=> ($value == $option_id) ? ' selected="selected"' : '',
				'VALUE'		=> $option_value,
			]);
		}
	}

	/**
	* Return Templated value/field. Possible values for $mode are:
	* change == user is able to set/enter profile values; preview == just show the value
	* @access private
	*/
	function process_field_row($mode, $profile_row)
	{
		global $template;

		$preview = ($mode == 'preview') ? true : false;

		// set template filename
		$template->set_filenames(['cp_body' => '@oxpus_dlext/helpers/dl_custom_fields.html']);

		// empty previously filled blockvars
		foreach ($this->profile_types as $field_case => $field_type)
		{
			$template->destroy_block_vars($field_type);
		}

		// Assign template variables
		$type_func = 'generate_' . $this->profile_types[$profile_row['field_type']];
		$this->$type_func($profile_row, $preview);

		// Return templated data
		return $template->assign_display('cp_body');
	}

	/**
	* Build Array for user insertion into custom profile fields table
	*/
	function build_insert_sql_array($cp_data)
	{
		global $db, $user, $auth, $table_prefix;

		$sql_not_in = [];

		foreach ($cp_data as $key => $null)
		{
			$sql_not_in[] = (strncmp($key, 'pf_', 3) === 0) ? substr($key, 3) : $key;
		}

		$sql = 'SELECT f.field_type, f.field_ident, f.field_default_value, l.lang_default_value
			FROM ' . $table_prefix . self::DL_LANG_TABLE . ' l, ' . $table_prefix . self::DL_FIELDS_TABLE . ' f
			WHERE l.lang_id = ' . (string) $user->get_iso_lang_id() . '
				' . ((!empty($sql_not_in)) ? ' AND ' . $db->sql_in_set('f.field_ident', $sql_not_in, true) : '') . '
				AND l.field_id = f.field_id';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
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
		$db->sql_freeresult($result);

		return $cp_data;
	}

	/**
	* Get profile field value on submit
	* @access private
	*/
	function get_profile_field($profile_row)
	{
		global $phpbb_root_path, $phpEx, $config, $request;

		$var_name = 'pf_' . $profile_row['field_ident'];

		switch ($profile_row['field_type'])
		{
			case FIELD_DATE:

				$req_fd = $request->variable($var_name . '_day', 0);

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
					$day = $request->variable($var_name . '_day', 0);
					$month = $request->variable($var_name . '_month', 0);
					$year = $request->variable($var_name . '_year', 0);
				}

				$var = sprintf('%2d-%2d-%4d', $day, $month, $year);
			break;

			case FIELD_BOOL:
				// Checkbox
				if ($profile_row['field_length'] == 2)
				{
					$var = ($request->variable($var_name, 0)) ? 1 : 2;
				}
				else
				{
					$var = $request->variable($var_name, (int) $profile_row['field_default_value']);
				}
			break;

			case FIELD_STRING:
			case FIELD_TEXT:
				$var = $request->variable($var_name, (string) $profile_row['field_default_value'], true);
			break;

			case FIELD_INT:
				$req_fl = $request->variable($var_name, '', true);
				if ($req_fl === '')
				{
					$var = null;
				}
				else
				{
					$var = $request->variable($var_name, (int) $profile_row['field_default_value']);
				}
			break;

			case FIELD_DROPDOWN:
				$var = $request->variable($var_name, (int) $profile_row['field_default_value']);
			break;

			default:
				$var = $request->variable($var_name, $profile_row['field_default_value']);
			break;
		}

		return $var;
	}

	/**
	* Get users profile fields
	*/
	function get_profile_fields($df_id)
	{
		global $db, $table_prefix;

		if (isset($this->profile_fields))
		{
			return;
		}

		$sql = 'SELECT *
			FROM ' . $table_prefix . self::DL_FIELDS_DATA_TABLE . '
			WHERE df_id = ' . (int) $df_id;
		$result = $db->sql_query_limit($sql, 1);
		$this->profile_fields = (!($row = $db->sql_fetchrow($result))) ? [] : $row;
		$db->sql_freeresult($result);
	}
}

$cp = new custom_profile();
