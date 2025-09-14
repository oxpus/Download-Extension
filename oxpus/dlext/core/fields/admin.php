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
 * Custom Profile Fields ACP
 * @package phpBB3
 */
class admin extends fields
{
	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames profile_row

	public $vars = [];

	/* phpbb objects */
	protected $config;
	protected $language;
	protected $request;
	protected $template;

	/* extension owned objects */
	protected $default_lang_id;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config			$config
	 * @param \phpbb\language\language 		$language
	 * @param \phpbb\request\request 		$request
	 * @param phpbb\template\template 		$template
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template
	)
	{
		$this->config 		= $config;
		$this->language 	= $language;
		$this->request 		= $request;
		$this->template 	= $template;
	}

	/**
	 * Return Templated value/field. Possible values for $mode are:
	 * change == user is able to set/enter profile values; preview == just show the value
	 * @access private
	 */
	public function process_admin_field_row($mode, $profile_row)
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
		$type_func = 'generate_' . $profile_row['field_type'];
		$this->$type_func($profile_row, $preview);

		// Return templated data
		return $this->template->assign_display('cp_body');
	}

	public function set_lang_defs($lang_defs)
	{
		$this->default_lang_id = $lang_defs['iso'][$this->config['default_lang']];
	}

	/**
	 * Return possible validation options
	 */
	public function validate_options()
	{
		$validate_ary = ['CHARS_ANY' => '.*', 'NUMBERS_ONLY' => '[0-9]+', 'ALPHA_ONLY' => '[\w]+', 'ALPHA_SPACERS' => '[\w_\+\. \-\[\]]+'];

		$validate_options = '';
		foreach ($validate_ary as $lang => $value)
		{
			$selected = ($this->vars['field_validation'] == $value) ? ' selected' : '';
			$validate_options .= '<option value="' . $value . '"' . $selected . '>' . $this->language->lang($lang) . '</option>';
		}

		return $validate_options;
	}

	/**
	 * Get string options for second step in ACP
	 */
	public function get_string_options()
	{
		$options = [
			0 => ['TITLE' => $this->language->lang('FIELD_LENGTH'),		'FIELD' => '<input type="text" name="field_length" size="5" value="' . $this->vars['field_length'] . '">'],
			1 => ['TITLE' => $this->language->lang('MIN_FIELD_CHARS'),	'FIELD' => '<input type="text" name="field_minlen" size="5" value="' . $this->vars['field_minlen'] . '">'],
			2 => ['TITLE' => $this->language->lang('MAX_FIELD_CHARS'),	'FIELD' => '<input type="text" name="field_maxlen" size="5" value="' . $this->vars['field_maxlen'] . '">'],
			3 => ['TITLE' => $this->language->lang('FIELD_VALIDATION'),	'FIELD' => '<select name="field_validation">' . $this->validate_options() . '</select>'],
		];

		return $options;
	}

	/**
	 * Get text options for second step in ACP
	 */
	public function get_text_options()
	{
		$options = [
			0 => ['TITLE' => $this->language->lang('FIELD_LENGTH'),		'FIELD' => '<input name="rows" size="5" value="' . $this->vars['rows'] . '"> ' . $this->language->lang('ROWS') . '</dd><dd><input name="columns" size="5" value="' . $this->vars['columns'] . '"> ' . $this->language->lang('COLUMNS') . ' <input type="hidden" name="field_length" value="' . $this->vars['field_length'] . '">'],
			1 => ['TITLE' => $this->language->lang('MIN_FIELD_CHARS'),	'FIELD' => '<input type="text" name="field_minlen" size="10" value="' . $this->vars['field_minlen'] . '">'],
			2 => ['TITLE' => $this->language->lang('MAX_FIELD_CHARS'),	'FIELD' => '<input type="text" name="field_maxlen" size="10" value="' . $this->vars['field_maxlen'] . '">'],
			3 => ['TITLE' => $this->language->lang('FIELD_VALIDATION'),	'FIELD' => '<select name="field_validation">' . $this->validate_options() . '</select>'],
		];

		return $options;
	}

	/**
	 * Get int options for second step in ACP
	 */
	public function get_int_options()
	{
		$options = [
			0 => ['TITLE' => $this->language->lang('FIELD_LENGTH'),		'FIELD' => '<input type="text" name="field_length" size="5" value="' . $this->vars['field_length'] . '">'],
			1 => ['TITLE' => $this->language->lang('MIN_FIELD_NUMBER'),	'FIELD' => '<input type="text" name="field_minlen" size="5" value="' . $this->vars['field_minlen'] . '">'],
			2 => ['TITLE' => $this->language->lang('MAX_FIELD_NUMBER'),	'FIELD' => '<input type="text" name="field_maxlen" size="5" value="' . $this->vars['field_maxlen'] . '">'],
			3 => ['TITLE' => $this->language->lang('DEFAULT_VALUE'),	'FIELD' => '<input type="post" name="field_default_value" value="' . $this->vars['field_default_value'] . '">'],
		];

		return $options;
	}

	/**
	 * Get bool options for second step in ACP
	 */
	public function get_bool_options()
	{
		$profile_row = [
			'var_name'				=> 'field_default_value',
			'field_id'				=> $this->field_id,
			'lang_name'				=> $this->vars['lang_name'],
			'lang_explain'			=> $this->vars['lang_explain'],
			'lang_id'				=> $this->default_lang_id,
			'field_ident'			=> 'field_default_value',
			'field_type'			=> 'bool',
			'field_length'			=> $this->vars['field_length'],
			'lang_options'			=> $this->vars['lang_options'],
			'field_default_value'	=> $this->vars['field_default_value'],
		];

		$options = [
			0 => ['TITLE' => $this->language->lang('FIELD_TYPE'), 'EXPLAIN' => $this->language->lang('BOOL_TYPE_EXPLAIN'), 'FIELD' => '<label><input type="radio" class="radio" name="field_length" value="1"' . (($this->vars['field_length'] == 1) ? ' checked' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();"> ' . $this->language->lang('RADIO_BUTTONS') . '</label><label><input type="radio" class="radio" name="field_length" value="2"' . (($this->vars['field_length'] == 2) ? ' checked' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();"> ' . $this->language->lang('CHECKBOX') . '</label>'],
			1 => ['TITLE' => $this->language->lang('DEFAULT_VALUE'), 'FIELD' => $this->process_admin_field_row('preview', $profile_row)],
		];

		return $options;
	}

	/**
	 * Get dropdown options for second step in ACP
	 */
	public function get_dropdown_options()
	{
		$profile_row[0] = [
			'var_name'				=> 'field_default_value',
			'field_id'				=> 1,
			'lang_name'				=> $this->vars['lang_name'],
			'lang_explain'			=> $this->vars['lang_explain'],
			'lang_id'				=> $this->default_lang_id,
			'field_default_value'	=> $this->vars['field_default_value'],
			'field_ident'			=> 'field_default_value',
			'field_type'			=> 'dropdown',
			'lang_options'			=> $this->vars['lang_options']
		];

		$profile_row[1] = $profile_row[0];
		$profile_row[1]['var_name'] = 'field_novalue';
		$profile_row[1]['field_ident'] = 'field_novalue';
		$profile_row[1]['field_default_value']	= $this->vars['field_novalue'];

		$options = [
			0 => ['TITLE' => $this->language->lang('DEFAULT_VALUE'), 'FIELD' => $this->process_admin_field_row('preview', $profile_row[0])],
			1 => ['TITLE' => $this->language->lang('NO_VALUE_OPTION'), 'EXPLAIN' => $this->language->lang('NO_VALUE_OPTION_EXPLAIN'), 'FIELD' => $this->process_admin_field_row('preview', $profile_row[1])],
		];

		return $options;
	}

	/**
	 * Get date options for second step in ACP
	 */
	public function get_date_options()
	{
		$profile_row = [
			'var_name'				=> 'field_default_value',
			'lang_name'				=> $this->vars['lang_name'],
			'lang_explain'			=> $this->vars['lang_explain'],
			'lang_id'				=> $this->default_lang_id,
			'field_default_value'	=> $this->vars['field_default_value'],
			'field_ident'			=> 'field_default_value',
			'field_type'			=> 'date',
			'field_length'			=> $this->vars['field_length']
		];

		$always_now = $this->request->variable('always_now', -1);
		if ($always_now == -1)
		{
			$s_checked = $this->vars['field_default_value'] == 'now';
		}
		else
		{
			$s_checked = $always_now > 0;
		}

		$options = [
			0 => ['TITLE' => $this->language->lang('DEFAULT_VALUE'),	'FIELD' => $this->process_admin_field_row('preview', $profile_row)],
			1 => ['TITLE' => $this->language->lang('ALWAYS_TODAY'),	'FIELD' => '<label><input type="radio" class="radio" name="always_now" value="1"' . (($s_checked) ? ' checked' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();"> ' . $this->language->lang('YES') . '</label><label><input type="radio" class="radio" name="always_now" value="0"' . ((!$s_checked) ? ' checked' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();"> ' . $this->language->lang('NO') . '</label>'],
		];

		return $options;
	}

	// phpcs:set VariableAnalysis.CodeAnalysis.VariableAnalysis validUndefinedVariableNames
}
