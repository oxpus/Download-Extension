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

namespace oxpus\dlext\controller\acp;

/**
 * @package acp
 */
class acp_fields_controller implements acp_fields_interface
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames field_order_ary l_lang_options l_lang_name l_lang_explain l_lang_default_value

	/* phpbb objects */
	protected $db;
	protected $user;
	protected $phpex;
	protected $root_path;
	protected $extension_manager;
	protected $log;
	protected $config;
	protected $language;
	protected $request;
	protected $template;
	protected $edit_lang_id;

	/* extension owned objects */
	public $u_action;
	protected $ext_path;

	protected $lang_defs;

	protected $dlext_fields;
	protected $dlext_fields_admin;
	protected $dlext_main;
	protected $dlext_constants;

	protected $dlext_table_dl_fields;
	protected $dlext_table_dl_fields_data;
	protected $dlext_table_dl_fields_lang;
	protected $dlext_table_dl_lang;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param string								$phpex
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\extension\manager				$extension_manager
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\user							$user
	 * @param \oxpus\dlext\core\fields\fields		$dlext_fields
	 * @param \oxpus\dlext\core\fields\admin		$dlext_fields_admin
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_fields
	 * @param string								$dlext_table_dl_fields_data
	 * @param string								$dlext_table_dl_fields_lang
	 * @param string								$dlext_table_dl_lang
	 */
	public function __construct(
		$root_path,
		$phpex,
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\extension\manager $extension_manager,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\user $user,
		\oxpus\dlext\core\fields\fields $dlext_fields,
		\oxpus\dlext\core\fields\admin $dlext_fields_admin,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_fields,
		$dlext_table_dl_fields_data,
		$dlext_table_dl_fields_lang,
		$dlext_table_dl_lang
	)
	{
		$this->root_path				= $root_path;
		$this->phpEx					= $phpex;
		$this->extension_manager		= $extension_manager;
		$this->db						= $db;
		$this->log						= $log;
		$this->user						= $user;

		$this->config					= $config;
		$this->language					= $language;
		$this->request					= $request;
		$this->template					= $template;

		$this->dlext_fields				= $dlext_fields;
		$this->dlext_fields_admin		= $dlext_fields_admin;
		$this->dlext_main				= $dlext_main;
		$this->dlext_constants			= $dlext_constants;

		$this->dlext_table_dl_fields		= $dlext_table_dl_fields;
		$this->dlext_table_dl_fields_data	= $dlext_table_dl_fields_data;
		$this->dlext_table_dl_fields_lang	= $dlext_table_dl_fields_lang;
		$this->dlext_table_dl_lang			= $dlext_table_dl_lang;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$this->ext_path		= $this->extension_manager->get_extension_path('oxpus/dlext', $this->dlext_constants::DL_TRUE);

		$action				= $this->request->variable('action', '');
		$cancel				= $this->request->variable('cancel', '');
		$mode				= $this->request->variable('mode', 'overview');

		$index = $this->dlext_main->full_index();

		if (empty($index))
		{
			$this->u_action = str_replace('mode=fields', 'mode=assistant', $this->u_action);
			redirect($this->u_action);
		}

		unset($index);

		if (!function_exists('generate_smilies'))
		{
			include($this->root_path . 'includes/functions_posting.' . $this->phpEx);
		}

		if (!function_exists('user_get_id_name'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->phpEx);
		}

		$this->user->add_lang(['ucp', 'acp/profile']);

		$create = $this->request->variable('create', '', $this->dlext_constants::DL_TRUE);
		$action = ($create) ? 'create' : $action;

		if ($cancel)
		{
			$action = '';
		}

		$error = [];

		// Define some default values for each field type
		$default_values = [
			FIELD_STRING	=> ['field_length' => 10, 'field_minlen' => 0, 'field_maxlen' => 20, 'field_validation' => '.*', 'field_novalue' => '', 'field_default_value' => ''],
			FIELD_TEXT		=> ['field_length' => '5|80', 'field_minlen' => 0, 'field_maxlen' => 1000, 'field_validation' => '.*', 'field_novalue' => '', 'field_default_value' => ''],
			FIELD_INT		=> ['field_length' => 5, 'field_minlen' => 0, 'field_maxlen' => 100, 'field_validation' => '', 'field_novalue' => 0, 'field_default_value' => 0],
			FIELD_DATE		=> ['field_length' => 10, 'field_minlen' => 10, 'field_maxlen' => 10, 'field_validation' => '', 'field_novalue' => ' 0- 0-   0', 'field_default_value' => ' 0- 0-   0'],
			FIELD_BOOL		=> ['field_length' => 1, 'field_minlen' => 0, 'field_maxlen' => 0, 'field_validation' => '', 'field_novalue' => 0, 'field_default_value' => 0],
			FIELD_DROPDOWN	=> ['field_length' => 0, 'field_minlen' => 0, 'field_maxlen' => 5, 'field_validation' => '', 'field_novalue' => 0, 'field_default_value' => 0],
		];

		// Build Language array
		// Based on this, we decide which elements need to be edited later and which language items are missing
		$this->lang_defs = [];

		$sql = 'SELECT lang_id, lang_iso
			FROM ' . LANG_TABLE . '
			ORDER BY lang_english_name';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Make some arrays with all available languages
			$this->lang_defs['id'][$row['lang_id']] = $row['lang_iso'];
			$this->lang_defs['iso'][$row['lang_iso']] = $row['lang_id'];
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT field_id, lang_id
			FROM ' . $this->dlext_table_dl_lang . '
			ORDER BY lang_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Which languages are available for each item
			$this->lang_defs['entry'][$row['field_id']][] = $row['lang_id'];
		}
		$this->db->sql_freeresult($result);

		// Have some fields been defined?
		if (isset($this->lang_defs['entry']))
		{
			foreach ($this->lang_defs['entry'] as $field_id => $field_ary)
			{
				// Fill an array with the languages that are missing for each field
				$this->lang_defs['diff'][$field_id] = array_diff(array_values($this->lang_defs['iso']), $field_ary);
			}
		}

		$this->dlext_fields_admin->set_lang_defs($this->lang_defs);

		switch ($action)
		{
			case 'delete':
				$field_id = $this->request->variable('field_id', 0);

				if (!$field_id)
				{
					trigger_error($this->language->lang('NO_FIELD_ID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (confirm_box($this->dlext_constants::DL_TRUE))
				{
					$sql = 'SELECT field_ident
						FROM ' . $this->dlext_table_dl_fields . '
						WHERE field_id = ' . (int) $field_id;
					$result = $this->db->sql_query($sql);
					$field_ident = (string) $this->db->sql_fetchfield('field_ident');
					$this->db->sql_freeresult($result);

					$this->db->sql_transaction('begin');

					$this->db->sql_query('DELETE FROM ' . $this->dlext_table_dl_fields . ' WHERE field_id = ' . (int) $field_id);
					$this->db->sql_query('DELETE FROM ' . $this->dlext_table_dl_fields_lang . ' WHERE field_id = ' . (int) $field_id);
					$this->db->sql_query('DELETE FROM ' . $this->dlext_table_dl_lang . ' WHERE field_id = ' . (int) $field_id);

					$this->db->sql_query('ALTER TABLE ' . $this->dlext_table_dl_fields_data . " DROP COLUMN pf_$field_ident");

					$this->db->sql_transaction('commit');

					$order = 0;

					$sql = 'SELECT *
						FROM ' . $this->dlext_table_dl_fields . '
						ORDER BY field_order';
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						++$order;

						if ($row['field_order'] != $order)
						{
							$sql = 'UPDATE ' . $this->dlext_table_dl_fields . "
								SET field_order = $order
								WHERE field_id = {$row['field_id']}";
							$this->db->sql_query($sql);
						}
					}

					$this->db->sql_freeresult($result);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FIELD_REMOVED', false, [$field_ident]);
					trigger_error($this->language->lang('DL_FIELD_REMOVED') . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box($this->dlext_constants::DL_FALSE, 'DL_FIELD_DELETE', build_hidden_fields([
						'mode'		=> $mode,
						'action'	=> $action,
						'field_id'	=> $field_id,
					]), '@oxpus_dlext/dl_confirm_body.html');
				}

				break;

			case 'activate':
				$field_id = $this->request->variable('field_id', 0);

				if (!$field_id)
				{
					trigger_error($this->language->lang('NO_FIELD_ID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql = 'SELECT lang_id
					FROM ' . LANG_TABLE . "
					WHERE lang_iso = '" . $this->db->sql_escape($this->config['default_lang']) . "'";
				$result = $this->db->sql_query($sql);
				$default_lang_id = (int) $this->db->sql_fetchfield('lang_id');
				$this->db->sql_freeresult($result);

				if (!in_array($default_lang_id, $this->lang_defs['entry'][$field_id]))
				{
					trigger_error($this->language->lang('DEFAULT_LANGUAGE_NOT_FILLED') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql = 'UPDATE ' . $this->dlext_table_dl_fields . '
					SET field_active = 1
					WHERE field_id = ' . (int) $field_id;
				$this->db->sql_query($sql);

				$sql = 'SELECT field_ident
					FROM ' . $this->dlext_table_dl_fields . '
					WHERE field_id = ' . (int) $field_id;
				$result = $this->db->sql_query($sql);
				$field_ident = (string) $this->db->sql_fetchfield('field_ident');
				$this->db->sql_freeresult($result);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FIELD_ACTIVATE', false, [$field_ident]);
				trigger_error($this->language->lang('DL_FIELD_ACTIVATED') . adm_back_link($this->u_action));

				break;

			case 'deactivate':
				$field_id = $this->request->variable('field_id', 0);

				if (!$field_id)
				{
					trigger_error($this->language->lang('NO_FIELD_ID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql = 'UPDATE ' . $this->dlext_table_dl_fields . '
					SET field_active = 0
					WHERE field_id = ' . (int) $field_id;
				$this->db->sql_query($sql);

				$sql = 'SELECT field_ident
					FROM ' . $this->dlext_table_dl_fields . '
					WHERE field_id = ' . (int) $field_id;
				$result = $this->db->sql_query($sql);
				$field_ident = (string) $this->db->sql_fetchfield('field_ident');
				$this->db->sql_freeresult($result);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FIELD_DEACT', false, [$field_ident]);
				trigger_error($this->language->lang('DL_FIELD_DEACTIVATED') . adm_back_link($this->u_action));

				break;

			case 'move_up':
			case 'move_down':
				$field_order = $this->request->variable('order', 0);
				$order_total = $field_order * 2 + (($action == 'move_up') ? -1 : 1);

				$field_order_ary[] = $field_order;
				if ($action == 'move_up')
				{
					$field_order_ary[] = $field_order - 1;
				}
				else
				{
					$field_order_ary[] = $field_order + 1;
				}

				$sql = 'UPDATE ' . $this->dlext_table_dl_fields . '
					SET field_order = ' . (int) $order_total . ' - field_order
					WHERE ' . $this->db->sql_in_set('field_order', $field_order_ary);
				$this->db->sql_query($sql);

				break;

			case 'create':
			case 'edit':

				$field_id = $this->request->variable('field_id', 0);
				$step = $this->request->variable('step', 1);

				$req_next = $this->request->variable('next', '', $this->dlext_constants::DL_TRUE);
				$req_prev = $this->request->variable('prev', '', $this->dlext_constants::DL_TRUE);
				$req_save = $this->request->variable('save', '', $this->dlext_constants::DL_TRUE);
				$submit = ($req_next || $req_prev) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
				$save = ($req_save) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

				// The language id of default language
				$this->edit_lang_id = $this->lang_defs['iso'][$this->config['default_lang']];

				// We are editing... we need to grab basic things
				if ($action == 'edit')
				{
					if (!$field_id)
					{
						trigger_error($this->language->lang('NO_FIELD_ID') . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$sql = 'SELECT l.*, f.*
						FROM ' . $this->dlext_table_dl_lang . ' l, ' . $this->dlext_table_dl_fields . ' f
						WHERE l.lang_id = ' . (int) $this->edit_lang_id . '
							AND f.field_id = ' . (int) $field_id . '
							AND l.field_id = f.field_id';
					$result = $this->db->sql_query($sql);
					$field_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if (!$field_row)
					{
						// Some admin changed the default language?
						$sql = 'SELECT l.*, f.*
							FROM ' . $this->dlext_table_dl_lang . ' l, ' . $this->dlext_table_dl_fields . ' f
							WHERE l.lang_id <> ' . (int) $this->edit_lang_id . '
							AND f.field_id = ' . (int) $field_id . '
							AND l.field_id = f.field_id';
						$result = $this->db->sql_query($sql);
						$field_row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						if (!$field_row)
						{
							trigger_error($this->language->lang('FIELD_NOT_FOUND') . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$this->edit_lang_id = $field_row['lang_id'];
					}
					$field_type = $field_row['field_type'];

					// Get language entries
					$sql = 'SELECT *
						FROM ' . $this->dlext_table_dl_fields_lang . '
						WHERE lang_id = ' . (int) $this->edit_lang_id . '
							AND field_id = ' . (int) $field_id . '
						ORDER BY option_id ASC';
					$result = $this->db->sql_query($sql);

					$lang_options = [];
					while ($row = $this->db->sql_fetchrow($result))
					{
						$lang_options[$row['option_id']] = $row['lang_value'];
					}
					$this->db->sql_freeresult($result);

					$s_hidden_fields = ['field_id' => $field_id];
				}
				else
				{
					// We are adding a new field, define basic params
					$lang_options = $field_row = [];

					$field_type = $this->request->variable('field_type', 0);

					if (!$field_type)
					{
						trigger_error($this->language->lang('NO_FIELD_TYPE') . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$field_row = array_merge($default_values[$field_type], [
						'field_ident'		=> str_replace(' ', '_', utf8_clean_string($this->request->variable('field_ident', '', $this->dlext_constants::DL_TRUE))),
						'field_required'	=> 0,
						'lang_name'			=> $this->request->variable('field_ident', '', $this->dlext_constants::DL_TRUE),
						'lang_explain'		=> '',
						'lang_default_value' => '',
					]);

					$s_hidden_fields = ['field_type' => $field_type];
				}

				$s_hidden_fields += ['action' => $action];

				// $exclude contains the data we gather in each step
				$exclude = [
					1	=> ['field_ident', 'lang_name', 'lang_explain', 'field_option_none', 'field_required'],
					2	=> ['field_length', 'field_maxlen', 'field_minlen', 'field_validation', 'field_novalue', 'field_default_value'],
					3	=> ['l_lang_name', 'l_lang_explain', 'l_lang_default_value', 'l_lang_options']
				];

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

				$this->dlext_fields_admin->vars['field_ident']		= ($action == 'create' && $step == 1) ? $this->request->variable('field_ident', $field_row['field_ident'], $this->dlext_constants::DL_TRUE) : $this->request->variable('field_ident', $field_row['field_ident']);
				$this->dlext_fields_admin->vars['lang_name']			= $this->request->variable('lang_name', $field_row['lang_name'], $this->dlext_constants::DL_TRUE);
				$this->dlext_fields_admin->vars['lang_explain']		= $this->request->variable('lang_explain', $field_row['lang_explain'], $this->dlext_constants::DL_TRUE);
				$this->dlext_fields_admin->vars['lang_default_value']	= $this->request->variable('lang_default_value', $field_row['lang_default_value'], $this->dlext_constants::DL_TRUE);

				// Visibility Options...
				$visibility_ary = ['field_required'];

				foreach ($visibility_ary as $val)
				{
					$this->dlext_fields_admin->vars[$val] = ($submit || $save) ? $this->request->variable($val, 0) : $field_row[$val];
				}

				// A boolean field expects an array as the lang options
				if ($field_type == FIELD_BOOL)
				{
					$options = $this->request->variable('lang_options', [''], $this->dlext_constants::DL_TRUE);
				}
				else
				{
					$options = $this->request->variable('lang_options', '', $this->dlext_constants::DL_TRUE);
				}

				// If the user has submitted a form with options (i.e. dropdown field)
				if ($options)
				{
					$exploded_options = (is_array($options)) ? $options : explode("\n", $options);

					if (count($exploded_options) == count($lang_options) || $action == 'create')
					{
						// The number of options in the field is equal to the number of options already in the database
						// Or we are creating a new dropdown list.
						$this->dlext_fields_admin->vars['lang_options'] = $exploded_options;
					}
					else if ($action == 'edit')
					{
						// Changing the number of options? (We remove and re-create the option fields)
						$this->dlext_fields_admin->vars['lang_options'] = $exploded_options;
					}
				}
				else
				{
					$this->dlext_fields_admin->vars['lang_options'] = $lang_options;
				}

				// step 2
				foreach ($exclude[2] as $key)
				{
					$var = $this->request->variable($key, $field_row[$key], $this->dlext_constants::DL_TRUE);

					// Manipulate the intended variables a little bit if needed
					if ($field_type == FIELD_DROPDOWN && $key == 'field_maxlen')
					{
						// Get the number of options if this key is 'field_maxlen'
						$var = count(explode("\n", $this->request->variable('lang_options', '', $this->dlext_constants::DL_TRUE)));
					}
					else if ($field_type == FIELD_TEXT && $key == 'field_length')
					{
						$rows = $this->request->variable('rows', 0);
						if ($rows)
						{
							$this->dlext_fields_admin->vars['rows'] = $rows;
							$this->dlext_fields_admin->vars['columns'] = $this->request->variable('columns', 0);
							$var = $this->dlext_fields_admin->vars['rows'] . '|' . $this->dlext_fields_admin->vars['columns'];
						}
						else
						{
							$row_col = explode('|', $var);
							$this->dlext_fields_admin->vars['rows'] = $row_col[0];
							$this->dlext_fields_admin->vars['columns'] = $row_col[1];
						}
					}
					else if ($field_type == FIELD_DATE && $key == 'field_default_value')
					{
						$always_now = $this->request->variable('always_now', -1);

						if ($always_now == 1 || ($always_now === -1 && $var == 'now'))
						{
							$now = getdate();

							$this->dlext_fields_admin->vars['field_default_value_day'] = $now['mday'];
							$this->dlext_fields_admin->vars['field_default_value_month'] = $now['mon'];
							$this->dlext_fields_admin->vars['field_default_value_year'] = $now['year'];
							$var = 'now';
							$this->request->overwrite('field_default_value', $var, \phpbb\request\request_interface::POST);
							$this->request->overwrite('field_default_value', $var, \phpbb\request\request_interface::REQUEST);
						}
						else
						{
							$reg_def_day = $this->request->variable('field_default_value_day', 0);
							if ($reg_def_day)
							{
								$this->dlext_fields_admin->vars['field_default_value_day'] = $reg_def_day;
								$this->dlext_fields_admin->vars['field_default_value_month'] = $this->request->variable('field_default_value_month', 0);
								$this->dlext_fields_admin->vars['field_default_value_year'] = $this->request->variable('field_default_value_year', 0);
								$var = sprintf('%2d-%2d-%4d', $this->dlext_fields_admin->vars['field_default_value_day'], $this->dlext_fields_admin->vars['field_default_value_month'], $this->dlext_fields_admin->vars['field_default_value_year']);
								$this->request->overwrite('field_default_value', $var, \phpbb\request\request_interface::POST);
								$this->request->overwrite('field_default_value', $var, \phpbb\request\request_interface::REQUEST);
								}
							else
							{
								list($this->dlext_fields_admin->vars['field_default_value_day'], $this->dlext_fields_admin->vars['field_default_value_month'], $this->dlext_fields_admin->vars['field_default_value_year']) = explode('-', $var);
							}
						}
					}
					else if ($field_type == FIELD_INT && $key == 'field_default_value')
					{
						// Permit an empty string
						if ($this->request->variable('field_default_value', $field_row['field_default_value']) === '')
						{
							$var = '';
						}
					}

					$this->dlext_fields_admin->vars[$key] = $var;
				}

				// step 3 - all arrays
				if ($action == 'edit')
				{
					// Get language entries
					$sql = 'SELECT *
						FROM ' . $this->dlext_table_dl_fields_lang . '
						WHERE lang_id <> ' . (int) $this->edit_lang_id . '
							AND field_id = ' . (int) $field_id . '
						ORDER BY option_id ASC';
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						$l_lang_options[$row['lang_id']][$row['option_id']] = $row['lang_value'];
					}
					$this->db->sql_freeresult($result);

					$sql = 'SELECT lang_id, lang_name, lang_explain, lang_default_value
						FROM ' . $this->dlext_table_dl_lang . '
						WHERE lang_id <> ' . (int) $this->edit_lang_id . '
							AND field_id = ' . (int) $field_id . '
						ORDER BY lang_id ASC';
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						$l_lang_name[$row['lang_id']] = $row['lang_name'];
						$l_lang_explain[$row['lang_id']] = $row['lang_explain'];
						$l_lang_default_value[$row['lang_id']] = $row['lang_default_value'];
					}
					$this->db->sql_freeresult($result);
				}

				foreach ($exclude[3] as $key)
				{
					$this->dlext_fields_admin->vars[$key] = $this->request->variable($key, [0 => ''], $this->dlext_constants::DL_TRUE);

					if (!$this->dlext_fields_admin->vars[$key] && $action == 'edit')
					{
						if (($key == 'l_lang_options' && !empty($l_lang_options)) || $key != 'l_lang_options')
						{
							$this->dlext_fields_admin->vars[$key] = $$key;
						}
					}
					else if ($key == 'l_lang_options' && $field_type == FIELD_BOOL)
					{
						$this->dlext_fields_admin->vars[$key] = $this->request->variable($key, [0 => ['']], $this->dlext_constants::DL_TRUE);
					}
					else if ($key == 'l_lang_options' && is_array($this->dlext_fields_admin->vars[$key]))
					{
						foreach ($this->dlext_fields_admin->vars[$key] as $lang_id => $options)
						{
							$this->dlext_fields_admin->vars[$key][$lang_id] = explode("\n", $options);
						}
					}
				}

				// Check for general issues in every step
				if ($submit) //  && $step == 1
				{
					// Check values for step 1
					if ($this->dlext_fields_admin->vars['field_ident'] == '')
					{
						$error[] = $this->language->lang('EMPTY_FIELD_IDENT');
					}

					if (!preg_match('/^[a-z_]+$/', $this->dlext_fields_admin->vars['field_ident']))
					{
						$error[] = $this->language->lang('INVALID_CHARS_FIELD_IDENT');
					}

					if (strlen($this->dlext_fields_admin->vars['field_ident']) > 17)
					{
						$error[] = $this->language->lang('INVALID_FIELD_IDENT_LEN');
					}

					if ($this->dlext_fields_admin->vars['lang_name'] == '')
					{
						$error[] = $this->language->lang('EMPTY_USER_FIELD_NAME');
					}

					if ($field_type == FIELD_DROPDOWN && empty($this->dlext_fields_admin->vars['lang_options']))
					{
						$error[] = $this->language->lang('NO_FIELD_ENTRIES');
					}

					if ($field_type == FIELD_BOOL && (empty($this->dlext_fields_admin->vars['lang_options'][0]) || empty($this->dlext_fields_admin->vars['lang_options'][1])))
					{
						$error[] = $this->language->lang('NO_FIELD_ENTRIES');
					}

					// Check for already existing field ident
					if ($action != 'edit')
					{
						$sql = 'SELECT field_ident
							FROM ' . $this->dlext_table_dl_fields . "
							WHERE field_ident = '" . $this->db->sql_escape($this->dlext_fields_admin->vars['field_ident']) . "'";
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						if ($row)
						{
							$error[] = $this->language->lang('FIELD_IDENT_ALREADY_EXIST');
						}
					}
				}

				$step = ($req_next) ? $step + 1 : (($req_prev) ? $step - 1 : $step);

				if (!empty($error))
				{
					--$step;
				}

				// Build up the specific hidden fields
				foreach ($exclude as $num => $key_ary)
				{
					if ($num == $step)
					{
						continue;
					}

					$_new_key_ary = [];

					foreach ($key_ary as $key)
					{
						$req_lang_opt = $this->request->variable($key, [['']], $this->dlext_constants::DL_TRUE);
						$reg_def_day = $this->request->variable('field_default_value_day', 0);

						if ($field_type == FIELD_TEXT && $key == 'field_length' && $rows)
						{
							$this->dlext_fields_admin->vars['rows'] = $this->request->variable('rows', 0);
							$this->dlext_fields_admin->vars['columns'] = $this->request->variable('columns', 0);
							$_new_key_ary[$key] = $this->dlext_fields_admin->vars['rows'] . '|' . $this->dlext_fields_admin->vars['columns'];
						}
						else if ($field_type == FIELD_DATE && $key == 'field_default_value')
						{
							$always_now = $this->request->variable('always_now', 0);

							if ($always_now)
							{
								$_new_key_ary[$key] = 'now';
							}
							else if ($reg_def_day)
							{
								$this->dlext_fields_admin->vars['field_default_value_day'] = $reg_def_day;
								$this->dlext_fields_admin->vars['field_default_value_month'] = $this->request->variable('field_default_value_month', 0);
								$this->dlext_fields_admin->vars['field_default_value_year'] = $this->request->variable('field_default_value_year', 0);
								$_new_key_ary[$key]  = sprintf('%2d-%2d-%4d', $this->dlext_fields_admin->vars['field_default_value_day'], $this->dlext_fields_admin->vars['field_default_value_month'], $this->dlext_fields_admin->vars['field_default_value_year']);
							}
						}
						else if ($field_type == FIELD_BOOL && $key == 'l_lang_options' && is_array($req_lang_opt))
						{
							$_new_key_ary[$key] = $this->request->variable($key, [['']], $this->dlext_constants::DL_TRUE);
						}
						else
						{
							$req_key = $this->request->variable($key, '', $this->dlext_constants::DL_TRUE);
							$req_ary = $this->request->variable($key, [''], $this->dlext_constants::DL_TRUE);

							if (!$req_key && empty($req_ary))
							{
								continue;
							}
							else if ($key == 'field_ident' && isset($this->dlext_fields_admin->vars[$key]))
							{
								$_new_key_ary[$key] = $this->dlext_fields_admin->vars[$key];
							}
							else
							{
								$_new_key_ary[$key] = (!empty($req_ary)) ? $req_ary : $req_key;
							}
						}
					}

					$s_hidden_fields += $_new_key_ary;
				}

				if (empty($error))
				{
					if ($step == 3 && (count($this->lang_defs['iso']) == 1 || $save))
					{
						$this->save_profile_field($field_type, $action);
					}
					else if ($action == 'edit' && $save)
					{
						$this->save_profile_field($field_type, $action);
					}
				}

				$this->template->assign_vars([
					'S_DL_EDIT'			=> $this->dlext_constants::DL_TRUE,
					'S_DL_EDIT_MODE'	=> ($action == 'edit') ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
					'DL_ERROR_MSG'		=> (!empty($error)) ? implode('<br>', $error) : '',

					'L_DL_TITLE'		=> $this->language->lang('DL_FIELDS_STEP' . $step . '_TITLE_' . strtoupper($action)),
					'L_DL_EXPLAIN'		=> $this->language->lang('DL_FIELDS_STEP' . $step . '_EXPLAIN'),

					'U_DL_ACTION'		=> $this->u_action . "&amp;action=$action&amp;step=$step",
					'U_DL_BACK'			=> $this->u_action,
				]);

				// Now go through the steps
				switch ($step)
				{
						// Create basic options - only small differences between field types
					case 1:

						// Build common create options
						$this->template->assign_vars([
							'S_DL_STEP_ONE'			=> $this->dlext_constants::DL_TRUE,
							'S_DL_FIELD_REQUIRED'	=> ($this->dlext_fields_admin->vars['field_required']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,

							'L_DL_LANG_SPECIFIC'	=> $this->language->lang('LANG_SPECIFIC_OPTIONS', $this->config['default_lang']),
							'DL_FIELD_TYPE'			=> $this->language->lang('FIELD_' . strtoupper($this->dlext_fields_admin->profile_types[$field_type])),
							'DL_FIELD_IDENT'		=> $this->dlext_fields_admin->vars['field_ident'],
							'DL_LANG_NAME'			=> $this->dlext_fields_admin->vars['lang_name'],
							'DL_LANG_EXPLAIN'		=> $this->dlext_fields_admin->vars['lang_explain'],
						]);

						// String and Text needs to set default values here...
						if ($field_type == FIELD_STRING || $field_type == FIELD_TEXT)
						{
							$this->template->assign_vars([
								'S_DL_TEXT'		=> ($field_type == FIELD_TEXT) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
								'S_DL_STRING'	=> ($field_type == FIELD_STRING) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,

								'L_DL_DEFAULT_VALUE_EXPLAIN'	=> $this->language->lang(strtoupper($this->dlext_fields_admin->profile_types[$field_type]) . '_DEFAULT_VALUE_EXPLAIN'),
								'DL_LANG_DEFAULT_VALUE'			=> $this->dlext_fields_admin->vars['lang_default_value'],
							]);
						}

						if ($field_type == FIELD_BOOL || $field_type == FIELD_DROPDOWN)
						{
							// Initialize these array elements if we are creating a new field
							if (empty($this->dlext_fields_admin->vars['lang_options']))
							{
								if ($field_type == FIELD_BOOL)
								{
									// No options have been defined for a boolean field.
									$this->dlext_fields_admin->vars['lang_options'][0] = '';
									$this->dlext_fields_admin->vars['lang_options'][1] = '';
								}
								else
								{
									// No options have been defined for the dropdown menu
									$this->dlext_fields_admin->vars['lang_options'] = [];
								}
							}

							$lang_option_explain = $this->language->lang(strtoupper($this->dlext_fields_admin->profile_types[$field_type]) . '_ENTRIES_EXPLAIN');
							if ($lang_option_explain == strtoupper($this->dlext_fields_admin->profile_types[$field_type]) . '_ENTRIES_EXPLAIN')
							{
								$lang_option_explain = '';
							}

							$this->template->assign_vars([
								'S_DL_BOOL'		=> ($field_type == FIELD_BOOL) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,
								'S_DL_DROPDOWN'	=> ($field_type == FIELD_DROPDOWN) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE,

								'L_DL_LANG_OPTIONS_EXPLAIN'	=> $lang_option_explain,
								'DL_LANG_OPTIONS'			=> ($field_type == FIELD_DROPDOWN) ? implode("\n", $this->dlext_fields_admin->vars['lang_options']) : '',
								'DL_FIRST_LANG_OPTION'		=> ($field_type == FIELD_BOOL) ? $this->dlext_fields_admin->vars['lang_options'][0] : '',
								'DL_SECOND_LANG_OPTION'		=> ($field_type == FIELD_BOOL) ? $this->dlext_fields_admin->vars['lang_options'][1] : '',
							]);
						}

						break;

					case 2:

						$this->template->assign_vars([
							'S_DL_STEP_TWO'		=> $this->dlext_constants::DL_TRUE,
							'L_DL_NEXT_STEP'	=> (count($this->lang_defs['iso']) == 1) ? $this->language->lang('SAVE') : $this->language->lang('PROFILE_LANG_OPTIONS'),
						]);

						// Build options based on profile type
						$function = 'get_' . $this->dlext_fields_admin->profile_types[$field_type] . '_options';
						$options = $this->dlext_fields_admin->$function();

						foreach ($options as $num => $option_ary)
						{
							$this->template->assign_block_vars('dl_option', $option_ary);
						}

						break;

						// Define remaining language variables
					case 3:

						$this->template->assign_var('S_DL_STEP_THREE', $this->dlext_constants::DL_TRUE);
						$options = $this->build_language_options($field_type, $action);

						foreach ($options as $lang_id => $lang_ary)
						{
							$this->template->assign_block_vars('options', [
								'DL_LANGUAGE'		=> $this->language->lang((($lang_id == $this->edit_lang_id) ? 'DEFAULT_' : '') . 'ISO_LANGUAGE', $lang_ary['lang_iso'])
							]);

							foreach ($lang_ary['fields'] as $field_ident => $field_ary)
							{
								$this->template->assign_block_vars('options.field', [
									'L_DL_TITLE'		=> $field_ary['TITLE'],
									'L_DL_EXPLAIN'		=> (isset($field_ary['EXPLAIN']) && $field_ary['EXPLAIN'] != 'CP_LANG_NAME_EXPLAIN') ? $field_ary['EXPLAIN'] : '',
									'DL_FIELD'			=> $field_ary['FIELD'],
								]);
							}
						}

						break;
				}

				$s_hidden_fields += ['step' => $step];

				$this->template->assign_vars(['S_DL_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields)]);

				break;
		}

		$sql = 'SELECT *
			FROM ' . $this->dlext_table_dl_fields . '
			ORDER BY field_order';
		$result = $this->db->sql_query($sql);

		$s_one_need_edit = $this->dlext_constants::DL_FALSE;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$active_lang = (!$row['field_active']) ? 'ACTIVATE' : 'DEACTIVATE';
			$active_value = (!$row['field_active']) ? 'activate' : 'deactivate';
			$id = $row['field_id'];

			$s_need_edit = (!empty($this->lang_defs['diff'][$row['field_id']])) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;

			if ($s_need_edit)
			{
				$s_one_need_edit = $this->dlext_constants::DL_TRUE;
			}

			$this->template->assign_block_vars('fields', [
				'DL_FIELD_IDENT'		=> $row['field_ident'],
				'DL_FIELD_TYPE'			=> $this->language->lang('FIELD_' . strtoupper($this->dlext_fields_admin->profile_types[$row['field_type']])),

				'L_DL_ACTIVATE_DEACTIVATE'		=> $this->language->lang($active_lang),
				'U_DL_ACTIVATE_DEACTIVATE'		=> $this->u_action . "&amp;action=$active_value&amp;field_id=$id",
				'U_DL_EDIT'						=> $this->u_action . "&amp;action=edit&amp;field_id=$id",
				'U_DL_TRANSLATE'				=> $this->u_action . "&amp;action=edit&amp;field_id=$id&amp;step=3",
				'U_DL_DELETE'					=> $this->u_action . "&amp;action=delete&amp;field_id=$id",
				'U_DL_MOVE_UP'					=> $this->u_action . "&amp;action=move_up&amp;order={$row['field_order']}",
				'U_DL_MOVE_DOWN'				=> $this->u_action . "&amp;action=move_down&amp;order={$row['field_order']}",

				'S_DL_NEED_EDIT'				=> $s_need_edit,
			]);
		}
		$this->db->sql_freeresult($result);

		// At least one option field needs editing?
		if ($s_one_need_edit)
		{
			$this->template->assign_var('S_DL_NEED_EDIT', $this->dlext_constants::DL_TRUE);
		}

		$s_select_type = '';
		foreach ($this->dlext_fields_admin->profile_types as $key => $value)
		{
			$s_select_type .= '<option value="' . $key . '">' . $this->language->lang('FIELD_' . strtoupper($value)) . '</option>';
		}

		$this->template->assign_vars([
			'U_DL_ACTION'		=> $this->u_action,
			'S_DL_TYPE_OPTIONS'	=> $s_select_type,
		]);
	}

	/**
	 * Build all Language specific options
	 * Taken from acp_profile.php (c) by phpbb.com
	 */
	public function build_language_options($field_type, $action = 'create')
	{
		$default_lang_id = (!empty($this->edit_lang_id)) ? $this->edit_lang_id : $this->lang_defs['iso'][$this->config['default_lang']];

		$sql = 'SELECT lang_id, lang_iso
			FROM ' . LANG_TABLE . '
			WHERE lang_id <> ' . (int) $default_lang_id . '
			ORDER BY lang_english_name';
		$result = $this->db->sql_query($sql);

		$languages = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$languages[$row['lang_id']] = $row['lang_iso'];
		}
		$this->db->sql_freeresult($result);

		$options = [];
		$options['lang_name'] = 'string';
		if ($this->dlext_fields_admin->vars['lang_explain'])
		{
			$options['lang_explain'] = 'text';
		}

		switch ($field_type)
		{
			case FIELD_BOOL:
				$options['lang_options'] = 'two_options';
				break;

			case FIELD_DROPDOWN:
				$options['lang_options'] = 'optionfield';
				break;

			case FIELD_TEXT:
			case FIELD_STRING:
				if (strlen($this->dlext_fields_admin->vars['lang_default_value']))
				{
					$options['lang_default_value'] = ($field_type == FIELD_STRING) ? 'string' : 'text';
				}
				break;
		}

		$lang_options = [];

		foreach ($options as $field => $field_type)
		{
			$lang_options[1]['lang_iso'] = $this->lang_defs['id'][$default_lang_id];
			$lang_options[1]['fields'][$field] = [
				'TITLE'		=> $this->language->lang('CP_' . strtoupper($field)),
				'FIELD'		=> '<dd>' . ((is_array($this->dlext_fields_admin->vars[$field])) ? implode('<br>', $this->dlext_fields_admin->vars[$field]) : bbcode_nl2br($this->dlext_fields_admin->vars[$field])) . '</dd>'
			];

			if ($this->language->lang('CP_' . strtoupper($field) . '_EXPLAIN'))
			{
				$lang_options[1]['fields'][$field]['EXPLAIN'] = $this->language->lang('CP_' . strtoupper($field) . '_EXPLAIN');

				if ($lang_options[1]['fields'][$field]['EXPLAIN'] == 'CP_' . strtoupper($field) . '_EXPLAIN')
				{
					$lang_options[1]['fields'][$field]['EXPLAIN'] = '';
				}
			}
		}

		foreach ($languages as $lang_id => $lang_iso)
		{
			$lang_options[$lang_id]['lang_iso'] = $lang_iso;
			foreach ($options as $field => $field_type)
			{
				$value = ($action == 'create') ? $this->request->variable('l_' . $field, [0 => ''], $this->dlext_constants::DL_TRUE) : $this->dlext_fields_admin->vars['l_' . $field];
				if ($field == 'lang_options')
				{
					$var = (!isset($this->dlext_fields_admin->vars['l_lang_options'][$lang_id]) || !is_array($this->dlext_fields_admin->vars['l_lang_options'][$lang_id])) ? $this->dlext_fields_admin->vars['lang_options'] : $this->dlext_fields_admin->vars['l_lang_options'][$lang_id];

					switch ($field_type)
					{
						case 'two_options':

							$lang_options[$lang_id]['fields'][$field] = [
								'TITLE'		=> $this->language->lang('CP_' . strtoupper($field)),
								'FIELD'		=> '
											<dd><input class="medium" name="l_' . $field . '[' . $lang_id . '][]" value="' . ((isset($value[$lang_id][0])) ? $value[$lang_id][0] : $var[0]) . '"> ' . $this->language->lang('FIRST_OPTION') . '</dd>
											<dd><input class="medium" name="l_' . $field . '[' . $lang_id . '][]" value="' . ((isset($value[$lang_id][1])) ? $value[$lang_id][1] : $var[1]) . '"> ' . $this->language->lang('SECOND_OPTION') . '</dd>'
							];
							break;

						case 'optionfield':
							$value = ((isset($value[$lang_id])) ? ((is_array($value[$lang_id])) ?  implode("\n", $value[$lang_id]) : $value[$lang_id]) : implode("\n", $var));
							$lang_options[$lang_id]['fields'][$field] = [
								'TITLE'		=> $this->language->lang('CP_' . strtoupper($field)),
								'FIELD'		=> '<dd><textarea name="l_' . $field . '[' . $lang_id . ']" rows="7" cols="80">' . $value . '</textarea></dd>'
							];
							break;
					}

					if ($this->language->lang('CP_' . strtoupper($field) . '_EXPLAIN'))
					{
						$lang_options[$lang_id]['fields'][$field]['EXPLAIN'] = $this->language->lang('CP_' . strtoupper($field) . '_EXPLAIN');

						if ($lang_options[$lang_id]['fields'][$field]['EXPLAIN'] == 'CP_' . strtoupper($field) . '_EXPLAIN')
						{
							$lang_options[$lang_id]['fields'][$field]['EXPLAIN'] = '';
						}
					}
				}
				else
				{
					$var = ($action == 'create' || !is_array($this->dlext_fields_admin->vars[$field])) ? $this->dlext_fields_admin->vars[$field] : $this->dlext_fields_admin->vars[$field][$lang_id];

					$lang_options[$lang_id]['fields'][$field] = [
						'TITLE'		=> $this->language->lang('CP_' . strtoupper($field)),
						'FIELD'		=> ($field_type == 'string') ? '<dd><input class="medium" type="text" name="l_' . $field . '[' . $lang_id . ']" value="' . ((isset($value[$lang_id])) ? $value[$lang_id] : $var) . '"></dd>' : '<dd><textarea name="l_' . $field . '[' . $lang_id . ']" rows="3" cols="80">' . ((isset($value[$lang_id])) ? $value[$lang_id] : $var) . '</textarea></dd>'
					];

					if ($this->language->lang('CP_' . strtoupper($field) . '_EXPLAIN') != 'CP_' . strtoupper($field) . '_EXPLAIN')
					{
						$lang_options[$lang_id]['fields'][$field]['EXPLAIN'] = $this->language->lang('CP_' . strtoupper($field) . '_EXPLAIN');

						if ($lang_options[$lang_id]['fields'][$field]['EXPLAIN'] == 'CP_' . strtoupper($field) . '_EXPLAIN')
						{
							$lang_options[$lang_id]['fields'][$field]['EXPLAIN'] = '';
						}
					}
				}
			}
		}

		return $lang_options;
	}

	/**
	 * Save Profile Field
	 * Taken from acp_profile.php (c) by phpbb.com
	 */
	public function save_profile_field($field_type, $action = 'create')
	{
		$field_id = $this->request->variable('field_id', 0);

		// Collect all information, if something is going wrong, abort the operation
		$profile_sql = $profile_lang = $empty_lang = $profile_lang_fields = [];

		$default_lang_id = (!empty($this->edit_lang_id)) ? $this->edit_lang_id : $this->lang_defs['iso'][$this->config['default_lang']];

		if ($action == 'create')
		{
			$sql = 'SELECT MAX(field_order) as max_field_order
				FROM ' . $this->dlext_table_dl_fields;
			$result = $this->db->sql_query($sql);
			$new_field_order = (int) $this->db->sql_fetchfield('max_field_order');
			$this->db->sql_freeresult($result);

			$field_ident = $this->dlext_fields_admin->vars['field_ident'];
		}

		// Save the field
		$profile_fields = [
			'field_length'			=> $this->dlext_fields_admin->vars['field_length'],
			'field_minlen'			=> $this->dlext_fields_admin->vars['field_minlen'],
			'field_maxlen'			=> $this->dlext_fields_admin->vars['field_maxlen'],
			'field_novalue'			=> $this->dlext_fields_admin->vars['field_novalue'],
			'field_default_value'	=> $this->dlext_fields_admin->vars['field_default_value'],
			'field_validation'		=> $this->dlext_fields_admin->vars['field_validation'],
			'field_required'		=> $this->dlext_fields_admin->vars['field_required'],
		];

		if ($action == 'create')
		{
			$profile_fields += [
				'field_type'		=> $field_type,
				'field_ident'		=> $field_ident,
				'field_name'		=> $field_ident,
				'field_order'		=> $new_field_order + 1,
				'field_active'		=> 1
			];

			$sql = 'INSERT INTO ' . $this->dlext_table_dl_fields . ' ' . $this->db->sql_build_array('INSERT', $profile_fields);
			$this->db->sql_query($sql);

			$field_id = $this->db->sql_last_inserted_id();
		}
		else
		{
			$sql = 'UPDATE ' . $this->dlext_table_dl_fields . '
				SET ' . $this->db->sql_build_array('UPDATE', $profile_fields) . '
				WHERE field_id = ' . (int) $field_id;
			$this->db->sql_query($sql);
		}

		if ($action == 'create')
		{
			$field_ident = 'pf_' . $field_ident;
			$profile_sql[] = $this->add_field_ident($field_ident, $field_type);
		}

		$sql_ary = [
			'lang_name'				=> $this->dlext_fields_admin->vars['lang_name'],
			'lang_explain'			=> $this->dlext_fields_admin->vars['lang_explain'],
			'lang_default_value'	=> $this->dlext_fields_admin->vars['lang_default_value']
		];

		if ($action == 'create')
		{
			$sql_ary['field_id'] = $field_id;
			$sql_ary['lang_id'] = $default_lang_id;

			$profile_sql[] = 'INSERT INTO ' . $this->dlext_table_dl_lang . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		}
		else
		{
			$this->update_insert($this->dlext_table_dl_lang, $sql_ary, ['field_id' => $field_id, 'lang_id' => $default_lang_id]);
		}

		if (is_array($this->dlext_fields_admin->vars['l_lang_name']) && !empty($this->dlext_fields_admin->vars['l_lang_name']))
		{
			foreach (array_keys($this->dlext_fields_admin->vars['l_lang_name']) as $lang_id)
			{
				if (($this->dlext_fields_admin->vars['lang_name'] != '' && $this->dlext_fields_admin->vars['l_lang_name'][$lang_id] == '')
					|| ($this->dlext_fields_admin->vars['lang_explain'] != '' && $this->dlext_fields_admin->vars['l_lang_explain'][$lang_id] == '')
					|| ($this->dlext_fields_admin->vars['lang_default_value'] != '' && $this->dlext_fields_admin->vars['l_lang_default_value'][$lang_id] == '')
				)
				{
					$empty_lang[$lang_id] = $this->dlext_constants::DL_TRUE;
					break;
				}

				if (!isset($empty_lang[$lang_id]))
				{
					$profile_lang[] = [
						'field_id'		=> $field_id,
						'lang_id'		=> $lang_id,
						'lang_name'		=> $this->dlext_fields_admin->vars['l_lang_name'][$lang_id],
						'lang_explain'	=> (isset($this->dlext_fields_admin->vars['l_lang_explain'][$lang_id])) ? $this->dlext_fields_admin->vars['l_lang_explain'][$lang_id] : '',
						'lang_default_value'	=> (isset($this->dlext_fields_admin->vars['l_lang_default_value'][$lang_id])) ? $this->dlext_fields_admin->vars['l_lang_default_value'][$lang_id] : ''
					];
				}
			}

			foreach (array_keys($empty_lang) as $lang_id)
			{
				$sql = 'DELETE FROM ' . $this->dlext_table_dl_lang . '
					WHERE field_id = ' . (int) $field_id . '
					AND lang_id = ' . (int) $lang_id;
				$this->db->sql_query($sql);
			}
		}

		// These are always arrays because the key is the language id...
		$this->dlext_fields_admin->vars['l_lang_name']			= $this->request->variable('l_lang_name', [0 => ''], $this->dlext_constants::DL_TRUE);
		$this->dlext_fields_admin->vars['l_lang_explain']			= $this->request->variable('l_lang_explain', [0 => ''], $this->dlext_constants::DL_TRUE);
		$this->dlext_fields_admin->vars['l_lang_default_value']	= $this->request->variable('l_lang_default_value', [0 => ''], $this->dlext_constants::DL_TRUE);

		if ($field_type != FIELD_BOOL)
		{
			$this->dlext_fields_admin->vars['l_lang_options']		= $this->request->variable('l_lang_options', [0 => ''], $this->dlext_constants::DL_TRUE);
		}
		else
		{
			$this->dlext_fields_admin->vars['l_lang_options']		= $this->request->variable('l_lang_options', [0 => ['']], $this->dlext_constants::DL_TRUE);
		}

		if ($this->dlext_fields_admin->vars['lang_options'])
		{
			if (!is_array($this->dlext_fields_admin->vars['lang_options']))
			{
				$this->dlext_fields_admin->vars['lang_options'] = explode("\n", $this->dlext_fields_admin->vars['lang_options']);
			}

			if ($action != 'create')
			{
				$sql = 'DELETE FROM ' . $this->dlext_table_dl_fields_lang . '
					WHERE field_id = ' . (int) $field_id . '
						AND lang_id = ' . (int) $default_lang_id;
				$this->db->sql_query($sql);
			}

			foreach ($this->dlext_fields_admin->vars['lang_options'] as $option_id => $value)
			{
				$sql_ary = [
					'field_type'	=> (int) $field_type,
					'lang_value'	=> $value
				];

				if ($action == 'create')
				{
					$sql_ary['field_id'] = $field_id;
					$sql_ary['lang_id'] = $default_lang_id;
					$sql_ary['option_id'] = (int) $option_id;

					$profile_sql[] = 'INSERT INTO ' . $this->dlext_table_dl_fields_lang . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
				}
				else
				{
					$this->update_insert($this->dlext_table_dl_fields_lang, $sql_ary, [
						'field_id'	=> $field_id,
						'lang_id'	=> (int) $default_lang_id,
						'option_id'	=> (int) $option_id,
					]);
				}
			}
		}

		if (is_array($this->dlext_fields_admin->vars['l_lang_options']) && !empty($this->dlext_fields_admin->vars['l_lang_options']))
		{
			$empty_lang = [];

			foreach ($this->dlext_fields_admin->vars['l_lang_options'] as $lang_id => $lang_ary)
			{
				if (!is_array($lang_ary))
				{
					$lang_ary = explode("\n", $lang_ary);
				}

				if (count($lang_ary) != count($this->dlext_fields_admin->vars['lang_options']))
				{
					$empty_lang[$lang_id] = $this->dlext_constants::DL_TRUE;
				}

				if (!isset($empty_lang[$lang_id]))
				{
					if ($action != 'create')
					{
						$sql = 'DELETE FROM ' . $this->dlext_table_dl_fields_lang . '
							WHERE field_id = ' . (int) $field_id . '
							AND lang_id = ' . (int) $lang_id;
						$this->db->sql_query($sql);
					}

					foreach ($lang_ary as $option_id => $value)
					{
						$profile_lang_fields[] = [
							'field_id'		=> (int) $field_id,
							'lang_id'		=> (int) $lang_id,
							'option_id'		=> (int) $option_id,
							'field_type'	=> (int) $field_type,
							'lang_value'	=> $value,
						];
					}
				}
			}

			foreach (array_keys($empty_lang) as $lang_id)
			{
				$sql = 'DELETE FROM ' . $this->dlext_table_dl_fields_lang . '
					WHERE field_id = ' . (int) $field_id . '
					AND lang_id = ' . (int) $lang_id;
				$this->db->sql_query($sql);
			}
		}

		foreach ($profile_lang as $sql)
		{
			if ($action == 'create')
			{
				$profile_sql[] = 'INSERT INTO ' . $this->dlext_table_dl_lang . ' ' . $this->db->sql_build_array('INSERT', $sql);
			}
			else
			{
				$lang_id = $sql['lang_id'];
				unset($sql['lang_id'], $sql['field_id']);

				$this->update_insert($this->dlext_table_dl_lang, $sql, ['lang_id' => (int) $lang_id, 'field_id' => $field_id]);
			}
		}

		if (!empty($profile_lang_fields))
		{
			foreach ($profile_lang_fields as $sql)
			{
				if ($action == 'create')
				{
					$profile_sql[] = 'INSERT INTO ' . $this->dlext_table_dl_fields_lang . ' ' . $this->db->sql_build_array('INSERT', $sql);
				}
				else
				{
					$lang_id = $sql['lang_id'];
					$option_id = $sql['option_id'];
					unset($sql['lang_id'], $sql['field_id'], $sql['option_id']);

					$this->update_insert($this->dlext_table_dl_fields_lang, $sql, [
						'lang_id'	=> $lang_id,
						'field_id'	=> $field_id,
						'option_id'	=> $option_id,
					]);
				}
			}
		}

		$this->db->sql_transaction('begin');

		if ($action == 'create')
		{
			foreach ($profile_sql as $sql)
			{
				$this->db->sql_query($sql);
			}
		}

		$this->db->sql_transaction('commit');

		if ($action == 'edit')
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FIELD_EDIT', false, [$this->dlext_fields_admin->vars['field_ident'] . ':' . $this->dlext_fields_admin->vars['lang_name']]);
			trigger_error($this->language->lang('DL_FIELD_CHANGED') . adm_back_link($this->u_action));
		}
		else
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_FIELD_CREATE', false, [substr($field_ident, 3) . ':' . $this->dlext_fields_admin->vars['lang_name']]);
			trigger_error($this->language->lang('DL_FIELD_ADDED') . adm_back_link($this->u_action));
		}
	}

	/**
	 * Update, then insert if not successfull
	 * Taken from acp_profile.php (c) by phpbb.com
	 */
	public function update_insert($table, $sql_ary, $where_fields)
	{
		$where_sql = [];
		$check_key = '';

		foreach ($where_fields as $key => $value)
		{
			$check_key = (!$check_key) ? $key : $check_key;
			$where_sql[] = $key . ' = ' . ((is_string($value)) ? "'" . $this->db->sql_escape($value) . "'" : (int) $value);
		}

		if (empty($where_sql))
		{
			return;
		}

		$sql = "SELECT $check_key
			FROM $table
			WHERE " . implode(' AND ', $where_sql);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			$sql_ary = array_merge($where_fields, $sql_ary);

			if (!empty($sql_ary))
			{
				$this->db->sql_query("INSERT INTO $table " . $this->db->sql_build_array('INSERT', $sql_ary));
			}
		}
		else
		{
			if (!empty($sql_ary))
			{
				$sql = "UPDATE $table SET " . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . implode(' AND ', $where_sql);
				$this->db->sql_query($sql);
			}
		}
	}

	/**
	 * Return sql statement for adding a new field ident (profile field) to the profile fields data table
	 * Taken from acp_profile.php (c) by phpbb.com
	 */
	public function add_field_ident($field_ident, $field_type)
	{
		$sql = '';

		switch ($this->db->get_sql_layer())
		{
			case 'mysql':
			case 'mysqli':

				// We are defining the biggest common value, because of the possibility to edit the min/max values of each field.
				$sql = 'ALTER TABLE ' . $this->dlext_table_dl_fields_data . " ADD $field_ident ";

				switch ($field_type)
				{
					case FIELD_STRING:
						$sql .= ' VARCHAR(255) ';
						break;

					case FIELD_DATE:
						$sql .= 'VARCHAR(10) ';
						break;

					case FIELD_TEXT:
						$sql .= 'TEXT';
						break;

					case FIELD_BOOL:
						$sql .= 'TINYINT(2) ';
						break;

					case FIELD_DROPDOWN:
						$sql .= 'MEDIUMINT(8) ';
						break;

					case FIELD_INT:
						$sql .= 'BIGINT(20) ';
						break;
				}

				break;

			case 'sqlite':
			case 'sqlite3':

				switch ($field_type)
				{
					case FIELD_STRING:
						$type = ' VARCHAR(255) ';
						break;

					case FIELD_DATE:
						$type = 'VARCHAR(10) ';
						break;

					case FIELD_TEXT:
						$type = 'TEXT(65535)';
						break;

					case FIELD_BOOL:
						$type = 'TINYINT(2) ';
						break;

					case FIELD_DROPDOWN:
						$type = 'MEDIUMINT(8) ';
						break;

					case FIELD_INT:
						$type = 'BIGINT(20) ';
						break;
				}

				// We are defining the biggest common value, because of the possibility to edit the min/max values of each field.
				$sql = 'ALTER TABLE ' . $this->dlext_table_dl_fields_data . " ADD $field_ident [$type]";

				break;

			case 'mssql':
			case 'mssql_odbc':
			case 'mssqlnative':

				// We are defining the biggest common value, because of the possibility to edit the min/max values of each field.
				$sql = 'ALTER TABLE [' . $this->dlext_table_dl_fields_data . '] ADD [\'' . $field_ident . '\'] ';

				switch ($field_type)
				{
					case FIELD_STRING:
						$sql .= ' [VARCHAR] (255) ';
						break;

					case FIELD_DATE:
						$sql .= '[VARCHAR] (10) ';
						break;

					case FIELD_TEXT:
						$sql .= '[TEXT]';
						break;

					case FIELD_BOOL:
					case FIELD_DROPDOWN:
						$sql .= '[INT] ';
						break;

					case FIELD_INT:
						$sql .= '[FLOAT] ';
						break;
				}

				break;

			case 'postgres':

				// We are defining the biggest common value, because of the possibility to edit the min/max values of each field.
				$sql = 'ALTER TABLE ' . $this->dlext_table_dl_fields_data . ' ADD COLUMN "$field_ident" ';

				switch ($field_type)
				{
					case FIELD_STRING:
						$sql .= ' VARCHAR(255) ';
						break;

					case FIELD_DATE:
						$sql .= 'VARCHAR(10) ';
						break;

					case FIELD_TEXT:
						$sql .= 'TEXT';
						break;

					case FIELD_BOOL:
						$sql .= 'INT2 ';
						break;

					case FIELD_DROPDOWN:
						$sql .= 'INT4 ';
						break;

					case FIELD_INT:
						$sql .= 'INT8 ';
						break;
				}

				break;

			case 'oracle':

				// We are defining the biggest common value, because of the possibility to edit the min/max values of each field.
				$sql = 'ALTER TABLE ' . $this->dlext_table_dl_fields_data . ' ADD ' . $field_ident . ' ';

				switch ($field_type)
				{
					case FIELD_STRING:
						$sql .= ' VARCHAR2(255) ';
						break;

					case FIELD_DATE:
						$sql .= 'VARCHAR2(10) ';
						break;

					case FIELD_TEXT:
						$sql .= 'CLOB';
						break;

					case FIELD_BOOL:
						$sql .= 'NUMBER(2) ';
						break;

					case FIELD_DROPDOWN:
						$sql .= 'NUMBER(8) ';
						break;

					case FIELD_INT:
						$sql .= 'NUMBER(20) ';
						break;
				}

				break;
		}

		return $sql;
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
