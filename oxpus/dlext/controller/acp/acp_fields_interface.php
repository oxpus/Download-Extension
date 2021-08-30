<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\acp;

/**
 * Interface for acp_fields_controller
 *
 */
interface acp_fields_interface
{
	/**
	 * Set the global module path
	 *
	 * @param string $u_action the current module url
	 * @return void
	 * @access public
	 */
	public function set_action($u_action);

	/**
	 * Module main part
	 *
	 * @return void
	 * @access public
	 */
	public function handle();

	/**
	 * Build all Language specific options
	 * Taken from acp_profile.php (c) by phpbb.com
	 *
	 * @param string $field_type defines the needed fieldtype
	 * @param string $action mode for workflow
	 * @return mixed prebuild input or display element
	 * @access public
	 */
	public function build_language_options($field_type, $action = 'create');

	/**
	 * Save Profile Field
	 * Taken from acp_profile.php (c) by phpbb.com
	 *
	 * @param string $field_type defines the needed fieldtype
	 * @param string $action mode for workflow
	 * @return void
	 * @access public
	 */
	public function save_profile_field($field_type, $action = 'create');

	/**
	 * Update, then insert if not successfull
	 * Taken from acp_profile.php (c) by phpbb.com
	 *
	 * @param string $table table name to save the data
	 * @param array $sql_ary predefined data to save
	 * @param array $where_fields existing data to merge for a new save
	 * @return void
	 * @access public
	 */
	public function update_insert($table, $sql_ary, $where_fields);

	/**
	 * Return sql statement for adding a new field ident (profile field) to the profile fields data table
	 * Taken from acp_profile.php (c) by phpbb.com
	 *
	 * @param string $field_ident field name to be extened in custom data table
	 * @param string $field_type field type for the new custom field
	 * @return string prebuild sql string to add new field
	 * @access public
	 */
	public function add_field_ident($field_ident, $field_type);
}
