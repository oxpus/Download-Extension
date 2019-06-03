<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* connect to phpBB
*/
if ( !defined('IN_PHPBB') )
{
	exit;
}

include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
include($ext_path . 'phpbb/helpers/dl_fields.' . $phpEx);

$user->add_lang(array('ucp', 'acp/profile'));

$create = $request->variable('create', '', true);
$action = ($create) ? 'create' : $action;

if ($cancel)
{
	$action = '';
}

$error = array();
$s_hidden_fields = '';

// Define some default values for each field type
$default_values = array(
	FIELD_STRING	=> array('field_length' => 10, 'field_minlen' => 0, 'field_maxlen' => 20, 'field_validation' => '.*', 'field_novalue' => '', 'field_default_value' => ''),
	FIELD_TEXT		=> array('field_length' => '5|80', 'field_minlen' => 0, 'field_maxlen' => 1000, 'field_validation' => '.*', 'field_novalue' => '', 'field_default_value' => ''),
	FIELD_INT		=> array('field_length' => 5, 'field_minlen' => 0, 'field_maxlen' => 100, 'field_validation' => '', 'field_novalue' => 0, 'field_default_value' => 0),
	FIELD_DATE		=> array('field_length' => 10, 'field_minlen' => 10, 'field_maxlen' => 10, 'field_validation' => '', 'field_novalue' => ' 0- 0-   0', 'field_default_value' => ' 0- 0-   0'),
	FIELD_BOOL		=> array('field_length' => 1, 'field_minlen' => 0, 'field_maxlen' => 0, 'field_validation' => '', 'field_novalue' => 0, 'field_default_value' => 0),
	FIELD_DROPDOWN	=> array('field_length' => 0, 'field_minlen' => 0, 'field_maxlen' => 5, 'field_validation' => '', 'field_novalue' => 0, 'field_default_value' => 0),
);

$cp = new \oxpus\dlext\phpbb\helpers\ custom_profile_admin();

// Build Language array
// Based on this, we decide which elements need to be edited later and which language items are missing
$this->lang_defs = array();

$sql = 'SELECT lang_id, lang_iso
	FROM ' . LANG_TABLE . '
	ORDER BY lang_english_name';
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	// Make some arrays with all available languages
	$this->lang_defs['id'][$row['lang_id']] = $row['lang_iso'];
	$this->lang_defs['iso'][$row['lang_iso']] = $row['lang_id'];
}
$db->sql_freeresult($result);

$sql = 'SELECT field_id, lang_id
	FROM ' . DL_LANG_TABLE . '
	ORDER BY lang_id';
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	// Which languages are available for each item
	$this->lang_defs['entry'][$row['field_id']][] = $row['lang_id'];
}
$db->sql_freeresult($result);

// Have some fields been defined?
if (isset($this->lang_defs['entry']))
{
	foreach ($this->lang_defs['entry'] as $field_id => $field_ary)
	{
		// Fill an array with the languages that are missing for each field
		$this->lang_defs['diff'][$field_id] = array_diff(array_values($this->lang_defs['iso']), $field_ary);
	}
}

switch ($action)
{
	case 'delete':
		$field_id = $request->variable('field_id', 0);

		if (!$field_id)
		{
			trigger_error($language->lang('NO_FIELD_ID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		if (confirm_box(true))
		{
			$sql = 'SELECT field_ident
				FROM ' . DL_FIELDS_TABLE . '
				WHERE field_id = ' . (int) $field_id;
			$result = $db->sql_query($sql);
			$field_ident = (string) $db->sql_fetchfield('field_ident');
			$db->sql_freeresult($result);

			$db->sql_transaction('begin');

			$db->sql_query('DELETE FROM ' . DL_FIELDS_TABLE . ' WHERE field_id = ' . (int) $field_id);
			$db->sql_query('DELETE FROM ' . DL_FIELDS_LANG_TABLE . ' WHERE field_id = ' . (int) $field_id);
			$db->sql_query('DELETE FROM ' . DL_LANG_TABLE . ' WHERE field_id = ' . (int) $field_id);

			$db->sql_query('ALTER TABLE ' . DL_FIELDS_DATA_TABLE . " DROP COLUMN pf_$field_ident");

			$order = 0;

			$sql = 'SELECT *
				FROM ' . DL_FIELDS_TABLE . '
				ORDER BY field_order';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$order++;
				if ($row['field_order'] != $order)
				{
					$sql = 'UPDATE ' . DL_FIELDS_TABLE . "
						SET field_order = $order
						WHERE field_id = {$row['field_id']}";
					$db->sql_query($sql);
				}
			}
			$db->sql_freeresult($result);

			$db->sql_transaction('commit');

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FIELD_REMOVED', false, array($field_ident));
			trigger_error($language->lang('DL_FIELD_REMOVED') . adm_back_link($this->u_action));
		}
		else
		{
			confirm_box(false, 'DL_FIELD_DELETE', build_hidden_fields(array(
				'i'			=> $id,
				'mode'		=> $mode,
				'action'	=> $action,
				'field_id'	=> $field_id,
			)));
		}

	break;

	case 'activate':
		$field_id = $request->variable('field_id', 0);

		if (!$field_id)
		{
			trigger_error($language->lang('NO_FIELD_ID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$sql = 'SELECT lang_id
			FROM ' . LANG_TABLE . "
			WHERE lang_iso = '" . $db->sql_escape($config['default_lang']) . "'";
		$result = $db->sql_query($sql);
		$default_lang_id = (int) $db->sql_fetchfield('lang_id');
		$db->sql_freeresult($result);

		if (!in_array($default_lang_id, $this->lang_defs['entry'][$field_id]))
		{
			trigger_error($language->lang('DEFAULT_LANGUAGE_NOT_FILLED') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$sql = 'UPDATE ' . DL_FIELDS_TABLE . '
			SET field_active = 1
			WHERE field_id = ' . (int) $field_id;
		$db->sql_query($sql);

		$sql = 'SELECT field_ident
			FROM ' . DL_FIELDS_TABLE . '
			WHERE field_id = ' . (int) $field_id;
		$result = $db->sql_query($sql);
		$field_ident = (string) $db->sql_fetchfield('field_ident');
		$db->sql_freeresult($result);

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FIELD_ACTIVATE', false, array($field_ident));
		trigger_error($language->lang('DL_FIELD_ACTIVATED') . adm_back_link($this->u_action));

	break;

	case 'deactivate':
		$field_id = $request->variable('field_id', 0);

		if (!$field_id)
		{
			trigger_error($language->lang('NO_FIELD_ID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$sql = 'UPDATE ' . DL_FIELDS_TABLE . '
			SET field_active = 0
			WHERE field_id = ' . (int) $field_id;
		$db->sql_query($sql);

		$sql = 'SELECT field_ident
			FROM ' . DL_FIELDS_TABLE . '
			WHERE field_id = ' . (int) $field_id;
		$result = $db->sql_query($sql);
		$field_ident = (string) $db->sql_fetchfield('field_ident');
		$db->sql_freeresult($result);

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DL_LOG_FIELD_DEACT', false, array($field_ident));
		trigger_error($language->lang('DL_FIELD_DEACTIVATED') . adm_back_link($this->u_action));

	break;

	case 'move_up':
	case 'move_down':
		$field_order = $request->variable('order', 0);
		$order_total = $field_order * 2 + (($action == 'move_up') ? -1 : 1);

		$sql = 'UPDATE ' . DL_FIELDS_TABLE . '
			SET field_order = ' . (int) $order_total . ' - field_order
			WHERE field_order IN (' . (int) $field_order . ', ' . (($action == 'move_up') ? (int) $field_order - 1 : (int) $field_order + 1) . ')';
		$db->sql_query($sql);

	break;

	case 'create':
	case 'edit':

		$field_id = $request->variable('field_id', 0);
		$step = $request->variable('step', 1);

		$req_next = $request->variable('next', '', true);
		$req_prev = $request->variable('prev', '', true);
		$req_save = $request->variable('save', '', true);
		$submit = ($req_next || $req_prev) ? true : false;
		$save = ($req_save) ? true : false;

		// The language id of default language
		$this->edit_lang_id = $this->lang_defs['iso'][$config['default_lang']];

		// We are editing... we need to grab basic things
		if ($action == 'edit')
		{
			if (!$field_id)
			{
				trigger_error($language->lang('NO_FIELD_ID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'SELECT l.*, f.*
				FROM ' . DL_LANG_TABLE . ' l, ' . DL_FIELDS_TABLE . ' f
				WHERE l.lang_id = ' . (int) $this->edit_lang_id . '
					AND f.field_id = ' . (int) $field_id . '
					AND l.field_id = f.field_id';
			$result = $db->sql_query($sql);
			$field_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$field_row)
			{
				// Some admin changed the default language?
				$sql = 'SELECT l.*, f.*
					FROM ' . DL_LANG_TABLE . ' l, ' . DL_FIELDS_TABLE . ' f
					WHERE l.lang_id <> ' . (int) $this->edit_lang_id . '
					AND f.field_id = ' . (int) $field_id . '
					AND l.field_id = f.field_id';
				$result = $db->sql_query($sql);
				$field_row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$field_row)
				{
					trigger_error($language->lang('FIELD_NOT_FOUND') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$this->edit_lang_id = $field_row['lang_id'];
			}
			$field_type = $field_row['field_type'];

			// Get language entries
			$sql = 'SELECT *
				FROM ' . DL_FIELDS_LANG_TABLE . '
				WHERE lang_id = ' . (int) $this->edit_lang_id . '
					AND field_id = ' . (int) $field_id . '
				ORDER BY option_id ASC';
			$result = $db->sql_query($sql);

			$lang_options = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$lang_options[$row['option_id']] = $row['lang_value'];
			}
			$db->sql_freeresult($result);

			$s_hidden_fields = '<input type="hidden" name="field_id" value="' . $field_id . '" />';
		}
		else
		{
			// We are adding a new field, define basic params
			$lang_options = $field_row = array();

			$field_type = $request->variable('field_type', 0);

			if (!$field_type)
			{
				trigger_error($language->lang('NO_FIELD_TYPE') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$field_row = array_merge($default_values[$field_type], array(
				'field_ident'		=> str_replace(' ', '_', utf8_clean_string($request->variable('field_ident', '', true))),
				'field_required'	=> 0,
				'lang_name'			=> $request->variable('field_ident', '', true),
				'lang_explain'		=> '',
				'lang_default_value'=> '')
			);

			$s_hidden_fields = '<input type="hidden" name="field_type" value="' . $field_type . '" />';
		}

		$s_hidden_fields .= '<input type="hidden" name="action" value="' . $action . '" />';

		// $exclude contains the data we gather in each step
		$exclude = array(
			1	=> array('field_ident', 'lang_name', 'lang_explain', 'field_option_none', 'field_required'),
			2	=> array('field_length', 'field_maxlen', 'field_minlen', 'field_validation', 'field_novalue', 'field_default_value'),
			3	=> array('l_lang_name', 'l_lang_explain', 'l_lang_default_value', 'l_lang_options')
		);

		// Text-based fields require the lang_default_value to be excluded
		if ($field_type == FIELD_STRING || $field_type == FIELD_TEXT)
		{
			$exclude[1][] = 'lang_default_value';
		}

		// option-specific fields require lang_options to be excluded
		if ($field_type == FIELD_BOOL || $field_type == FIELD_DROPDOWN)
		{
			$exclude[1][] = 'lang_options';
		}

		$cp->vars['field_ident']		= ($action == 'create' && $step == 1) ? $request->variable('field_ident', $field_row['field_ident'], true) : $request->variable('field_ident', $field_row['field_ident']);
		$cp->vars['lang_name']			= $request->variable('lang_name', $field_row['lang_name'], true);
		$cp->vars['lang_explain']		= $request->variable('lang_explain', $field_row['lang_explain'], true);
		$cp->vars['lang_default_value']	= $request->variable('lang_default_value', $field_row['lang_default_value'], true);

		// Visibility Options...
		$visibility_ary = array(
			'field_required',
		);

		foreach ($visibility_ary as $val)
		{
			$cp->vars[$val] = ($submit || $save) ? $request->variable($val, 0) : $field_row[$val];
		}

		// A boolean field expects an array as the lang options
		if ($field_type == FIELD_BOOL)
		{
			$options = $request->variable('lang_options', array(''), true);
		}
		else
		{
			$options = $request->variable('lang_options', '', true);
		}

		// If the user has submitted a form with options (i.e. dropdown field)
		if ($options)
		{
			$exploded_options = (is_array($options)) ? $options : explode("\n", $options);

			if (sizeof($exploded_options) == sizeof($lang_options) || $action == 'create')
			{
				// The number of options in the field is equal to the number of options already in the database
				// Or we are creating a new dropdown list.
				$cp->vars['lang_options'] = $exploded_options;
			}
			else if ($action == 'edit')
			{
				// Changing the number of options? (We remove and re-create the option fields)
				$cp->vars['lang_options'] = $exploded_options;
			}
		}
		else
		{
			$cp->vars['lang_options'] = $lang_options;
		}

		// step 2
		foreach ($exclude[2] as $key)
		{
			$var = $request->variable($key, $field_row[$key], true);

			// Manipulate the intended variables a little bit if needed
			if ($field_type == FIELD_DROPDOWN && $key == 'field_maxlen')
			{
				// Get the number of options if this key is 'field_maxlen'
				$var = sizeof(explode("\n", $request->variable('lang_options', '', true)));
			}
			else if ($field_type == FIELD_TEXT && $key == 'field_length')
			{
				$rows = $request->variable('rows', 0);
				if ($rows)
				{
					$cp->vars['rows'] = $rows;
					$cp->vars['columns'] = $request->variable('columns', 0);
					$var = $cp->vars['rows'] . '|' . $cp->vars['columns'];
				}
				else
				{
					$row_col = explode('|', $var);
					$cp->vars['rows'] = $row_col[0];
					$cp->vars['columns'] = $row_col[1];
				}
			}
			else if ($field_type == FIELD_DATE && $key == 'field_default_value')
			{
				$always_now = $request->variable('always_now', -1);

				if ($always_now == 1 || ($always_now === -1 && $var == 'now'))
				{
					$now = getdate();

					$cp->vars['field_default_value_day'] = $now['mday'];
					$cp->vars['field_default_value_month'] = $now['mon'];
					$cp->vars['field_default_value_year'] = $now['year'];
					$var = $_POST['field_default_value'] = 'now';
				}
				else
				{
					$reg_def_day = $request->variable('field_default_value_day', 0);
					if ($reg_def_day)
					{
						$cp->vars['field_default_value_day'] = $reg_def_day;
						$cp->vars['field_default_value_month'] = $request->variable('field_default_value_month', 0);
						$cp->vars['field_default_value_year'] = $request->variable('field_default_value_year', 0);
						$var = $_POST['field_default_value'] = sprintf('%2d-%2d-%4d', $cp->vars['field_default_value_day'], $cp->vars['field_default_value_month'], $cp->vars['field_default_value_year']);
					}
					else
					{
						list($cp->vars['field_default_value_day'], $cp->vars['field_default_value_month'], $cp->vars['field_default_value_year']) = explode('-', $var);
					}
				}
			}
			else if ($field_type == FIELD_INT && $key == 'field_default_value')
			{
				// Permit an empty string
				if ($request->variable('field_default_value', $field_row['field_default_value']) === '')
				{
					$var = '';
				}
			}

			$cp->vars[$key] = $var;
		}

		// step 3 - all arrays
		if ($action == 'edit')
		{
			// Get language entries
			$sql = 'SELECT *
				FROM ' . DL_FIELDS_LANG_TABLE . '
				WHERE lang_id <> ' . (int) $this->edit_lang_id . '
					AND field_id = ' . (int) $field_id . '
				ORDER BY option_id ASC';
			$result = $db->sql_query($sql);

			$l_lang_options = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$l_lang_options[$row['lang_id']][$row['option_id']] = $row['lang_value'];
			}
			$db->sql_freeresult($result);


			$sql = 'SELECT lang_id, lang_name, lang_explain, lang_default_value
				FROM ' . DL_LANG_TABLE . '
				WHERE lang_id <> ' . (int) $this->edit_lang_id . '
					AND field_id = ' . (int) $field_id . '
				ORDER BY lang_id ASC';
			$result = $db->sql_query($sql);

			$l_lang_name = $l_lang_explain = $l_lang_default_value = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$l_lang_name[$row['lang_id']] = $row['lang_name'];
				$l_lang_explain[$row['lang_id']] = $row['lang_explain'];
				$l_lang_default_value[$row['lang_id']] = $row['lang_default_value'];
			}
			$db->sql_freeresult($result);
		}

		foreach ($exclude[3] as $key)
		{
			$cp->vars[$key] = $request->variable($key, array(0 => ''), true);

			if (!$cp->vars[$key] && $action == 'edit')
			{
				$cp->vars[$key] = $$key;
			}
			else if ($key == 'l_lang_options' && $field_type == FIELD_BOOL)
			{
				$cp->vars[$key] = $request->variable($key, array(0 => array('')), true);
			}
			else if ($key == 'l_lang_options' && is_array($cp->vars[$key]))
			{
				foreach ($cp->vars[$key] as $lang_id => $options)
				{
					$cp->vars[$key][$lang_id] = explode("\n", $options);
				}

			}
		}

		// Check for general issues in every step
		if ($submit) //  && $step == 1
		{
			// Check values for step 1
			if ($cp->vars['field_ident'] == '')
			{
				$error[] = $language->lang('EMPTY_FIELD_IDENT');
			}

			if (!preg_match('/^[a-z_]+$/', $cp->vars['field_ident']))
			{
				$error[] = $language->lang('INVALID_CHARS_FIELD_IDENT');
			}

			if (strlen($cp->vars['field_ident']) > 17)
			{
				$error[] = $language->lang('INVALID_FIELD_IDENT_LEN');
			}

			if ($cp->vars['lang_name'] == '')
			{
				$error[] = $language->lang('EMPTY_USER_FIELD_NAME');
			}

			if ($field_type == FIELD_DROPDOWN && !sizeof($cp->vars['lang_options']))
			{
				$error[] = $language->lang('NO_FIELD_ENTRIES');
			}

			if ($field_type == FIELD_BOOL && (empty($cp->vars['lang_options'][0]) || empty($cp->vars['lang_options'][1])))
			{
				$error[] = $language->lang('NO_FIELD_ENTRIES');
			}

			// Check for already existing field ident
			if ($action != 'edit')
			{
				$sql = 'SELECT field_ident
					FROM ' . DL_FIELDS_TABLE . "
					WHERE field_ident = '" . $db->sql_escape($cp->vars['field_ident']) . "'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if ($row)
				{
					$error[] = $language->lang('FIELD_IDENT_ALREADY_EXIST');
				}
			}
		}

		$step = ($req_next) ? $step + 1 : (($req_prev) ? $step - 1 : $step);

		if (sizeof($error))
		{
			$step--;
			$submit = false;
		}

		// Build up the specific hidden fields
		foreach ($exclude as $num => $key_ary)
		{
			if ($num == $step)
			{
				continue;
			}

			$_new_key_ary = array();

			foreach ($key_ary as $key)
			{
				$req_lang_opt = $request->variable($key, array(array('')), true);
				$reg_def_day = $request->variable('field_default_value_day', 0);

				if ($field_type == FIELD_TEXT && $key == 'field_length' && $rows)
				{
					$cp->vars['rows'] = $request->variable('rows', 0);
					$cp->vars['columns'] = $request->variable('columns', 0);
					$_new_key_ary[$key] = $cp->vars['rows'] . '|' . $cp->vars['columns'];
				}
				else if ($field_type == FIELD_DATE && $key == 'field_default_value')
				{
					$always_now = $request->variable('always_now', 0);

					if ($always_now)
					{
						$_new_key_ary[$key] = 'now';
					}
					else if ($reg_def_day)
					{
						$cp->vars['field_default_value_day'] = $reg_def_day;
						$cp->vars['field_default_value_month'] = $request->variable('field_default_value_month', 0);
						$cp->vars['field_default_value_year'] = $request->variable('field_default_value_year', 0);
						$_new_key_ary[$key]  = sprintf('%2d-%2d-%4d', $cp->vars['field_default_value_day'], $cp->vars['field_default_value_month'], $cp->vars['field_default_value_year']);
					}
				}
				else if ($field_type == FIELD_BOOL && $key == 'l_lang_options' && is_array($req_lang_opt))
				{
					$_new_key_ary[$key] = $request->variable($key, array(array('')), true);
				}
				else
				{
					$req_key = $request->variable($key, '', true);
					$req_ary = $request->variable($key, array(''), true);

					if (!$req_key && !sizeof($req_ary))
					{
						$var = false;
					}
					else if ($key == 'field_ident' && isset($cp->vars[$key]))
					{
						$_new_key_ary[$key] = $cp->vars[$key];
					}
					else
					{
						$_new_key_ary[$key] = (sizeof($req_ary)) ? $req_ary : $req_key;
					}
				}
			}

			$s_hidden_fields .= build_hidden_fields($_new_key_ary);
		}

		if (!sizeof($error))
		{
			if ($step == 3 && (sizeof($this->lang_defs['iso']) == 1 || $save))
			{
				$this->save_profile_field($cp, $field_type, $action);
			}
			else if ($action == 'edit' && $save)
			{
				$this->save_profile_field($cp, $field_type, $action);
			}
		}

		$template->assign_vars(array(
			'S_EDIT'			=> true,
			'S_EDIT_MODE'		=> ($action == 'edit') ? true : false,
			'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',

			'L_TITLE'			=> $language->lang('DL_FIELDS_STEP' . $step . '_TITLE_' . strtoupper($action)),
			'L_EXPLAIN'			=> $language->lang('DL_FIELDS_STEP' . $step . '_EXPLAIN'),

			'U_ACTION'			=> $this->u_action . "&amp;action=$action&amp;step=$step",
			'U_BACK'			=> $this->u_action)
		);

		// Now go through the steps
		switch ($step)
		{
			// Create basic options - only small differences between field types
			case 1:

				// Build common create options
				$template->assign_vars(array(
					'S_STEP_ONE'		=> true,
					'S_FIELD_REQUIRED'	=> ($cp->vars['field_required']) ? true : false,

					'L_LANG_SPECIFIC'	=> $language->lang('LANG_SPECIFIC_OPTIONS', $config['default_lang']),
					'FIELD_TYPE'		=> $language->lang('FIELD_' . strtoupper($cp->profile_types[$field_type])),
					'FIELD_IDENT'		=> $cp->vars['field_ident'],
					'LANG_NAME'			=> $cp->vars['lang_name'],
					'LANG_EXPLAIN'		=> $cp->vars['lang_explain'])
				);

				// String and Text needs to set default values here...
				if ($field_type == FIELD_STRING || $field_type == FIELD_TEXT)
				{
					$template->assign_vars(array(
						'S_TEXT'		=> ($field_type == FIELD_TEXT) ? true : false,
						'S_STRING'		=> ($field_type == FIELD_STRING) ? true : false,

						'L_DEFAULT_VALUE_EXPLAIN'	=> $language->lang(strtoupper($cp->profile_types[$field_type]) . '_DEFAULT_VALUE_EXPLAIN'),
						'LANG_DEFAULT_VALUE'		=> $cp->vars['lang_default_value'])
					);
				}

				if ($field_type == FIELD_BOOL || $field_type == FIELD_DROPDOWN)
				{
					// Initialize these array elements if we are creating a new field
					if (!sizeof($cp->vars['lang_options']))
					{
						if ($field_type == FIELD_BOOL)
						{
							// No options have been defined for a boolean field.
							$cp->vars['lang_options'][0] = '';
							$cp->vars['lang_options'][1] = '';
						}
						else
						{
							// No options have been defined for the dropdown menu
							$cp->vars['lang_options'] = array();
						}
					}

					$template->assign_vars(array(
						'S_BOOL'		=> ($field_type == FIELD_BOOL) ? true : false,
						'S_DROPDOWN'	=> ($field_type == FIELD_DROPDOWN) ? true : false,

						'L_LANG_OPTIONS_EXPLAIN'	=> $language->lang(strtoupper($cp->profile_types[$field_type]) . '_ENTRIES_EXPLAIN'),
						'LANG_OPTIONS'				=> ($field_type == FIELD_DROPDOWN) ? implode("\n", $cp->vars['lang_options']) : '',
						'FIRST_LANG_OPTION'			=> ($field_type == FIELD_BOOL) ? $cp->vars['lang_options'][0] : '',
						'SECOND_LANG_OPTION'		=> ($field_type == FIELD_BOOL) ? $cp->vars['lang_options'][1] : '')
					);
				}

			break;

			case 2:

				$template->assign_vars(array(
					'S_STEP_TWO'		=> true,
					'L_NEXT_STEP'			=> (sizeof($this->lang_defs['iso']) == 1) ? $language->lang('SAVE') : $language->lang('PROFILE_LANG_OPTIONS'))
				);

				// Build options based on profile type
				$function = 'get_' . $cp->profile_types[$field_type] . '_options';
				$options = $cp->$function();

				foreach ($options as $num => $option_ary)
				{
					$template->assign_block_vars('option', $option_ary);
				}

			break;

			// Define remaining language variables
			case 3:

				$template->assign_var('S_STEP_THREE', true);
				$options = $this->build_language_options($cp, $field_type, $action);

				foreach ($options as $lang_id => $lang_ary)
				{
					$template->assign_block_vars('options', array(
						'LANGUAGE'		=> $language->lang((($lang_id == $this->edit_lang_id) ? 'DEFAULT_' : '') . 'ISO_LANGUAGE', $lang_ary['lang_iso'])
					));

					foreach ($lang_ary['fields'] as $field_ident => $field_ary)
					{
						$template->assign_block_vars('options.field', array(
							'L_TITLE'		=> $field_ary['TITLE'],
							'L_EXPLAIN'		=> (isset($field_ary['EXPLAIN'])) ? $field_ary['EXPLAIN'] : '',
							'FIELD'			=> $field_ary['FIELD'])
						);
					}
				}

			break;
		}

		$s_hidden_fields .= '<input type="hidden" name="step" value="' . $step . '" /> ';

		$template->assign_vars(array(
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields)
		);

	break;
}

$sql = 'SELECT *
	FROM ' . DL_FIELDS_TABLE . '
	ORDER BY field_order';
$result = $db->sql_query($sql);

$s_one_need_edit = false;
while ($row = $db->sql_fetchrow($result))
{
	$active_lang = (!$row['field_active']) ? 'ACTIVATE' : 'DEACTIVATE';
	$active_value = (!$row['field_active']) ? 'activate' : 'deactivate';
	$id = $row['field_id'];

	$s_need_edit = (sizeof($this->lang_defs['diff'][$row['field_id']])) ? true : false;

	if ($s_need_edit)
	{
		$s_one_need_edit = true;
	}

	$template->assign_block_vars('fields', array(
		'FIELD_IDENT'		=> $row['field_ident'],
		'FIELD_TYPE'		=> $language->lang('FIELD_' . strtoupper($cp->profile_types[$row['field_type']])),

		'L_ACTIVATE_DEACTIVATE'		=> $language->lang($active_lang),
		'U_ACTIVATE_DEACTIVATE'		=> $this->u_action . "&amp;action=$active_value&amp;field_id=$id",
		'U_EDIT'					=> $this->u_action . "&amp;action=edit&amp;field_id=$id",
		'U_TRANSLATE'				=> $this->u_action . "&amp;action=edit&amp;field_id=$id&amp;step=3",
		'U_DELETE'					=> $this->u_action . "&amp;action=delete&amp;field_id=$id",
		'U_MOVE_UP'					=> $this->u_action . "&amp;action=move_up&amp;order={$row['field_order']}",
		'U_MOVE_DOWN'				=> $this->u_action . "&amp;action=move_down&amp;order={$row['field_order']}",

		'S_NEED_EDIT'				=> $s_need_edit)
	);
}
$db->sql_freeresult($result);

// At least one option field needs editing?
if ($s_one_need_edit)
{
	$template->assign_var('S_NEED_EDIT', true);
}

$s_select_type = '';
foreach ($cp->profile_types as $key => $value)
{
	$s_select_type .= '<option value="' . $key . '">' . $language->lang('FIELD_' . strtoupper($value)) . '</option>';
}

$template->assign_vars(array(
	'U_ACTION'			=> $this->u_action,
	'S_TYPE_OPTIONS'	=> $s_select_type)
);

// Template einbinden
$template->assign_var('S_DL_FIELDS', true);
