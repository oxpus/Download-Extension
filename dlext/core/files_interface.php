<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\core;

/**
 * Interface for acp_banlist_controller
 *
 */
interface files_interface
{
	/**
	 * Fetch the saves data from the given download files
	 *
	 * @param int $cat_id downloads only from this category
	 * @param string $sql_sort_by fields for sorting the files
	 * @param string $sql_order sort direction ASC or DESC
	 * @param int $start start value for a limited select
	 * @param int $limit amount of files for a limited select, 0 will select unlimited
	 * @param string $sql_fields fields which data are needed
	 * @return array all download data we asked for
	 * @access public
	*/
	public function files($cat_id, $sql_sort_by, $sql_order, $start, $limit, $sql_fields = '*');

	/**
	 * Fetch the saves data from the given download files with more control settings
	 *
	 * @param int $cat_id downloads only the given category
	 * @param array $sort_ary fields for sorting as paired fieldname => direction
	 * @param array $extra_where additional conditions fieldname => condition|operator|value (only one OR is allowed to follow to an AND!)
	 * @param int $df_id select only this download dataset
	 * @param bool $modcp true will fetch all downloads, false only approved ones
	 * @param array $fields fields which datas are needed
	 * @param int $limit amount of files for a limited select, 0 will select unlimited
	 * @param int $limit_start start row in the data rowset to select from, 0 will disable the limit
	 * @return array all download data we asked for
	 * @access public
	*/
	public function all_files($cat_id, $sort_ary, $extra_where, $df_id, $modcp, $fields, $limit = 0, $limit_start = 0);

	/**
	 * Check the given fields to be valid and to secure the sql statement
	 *
	 * @param string $table The name of the table to select from
	 * @param array $fields fieldnames for checking out
	 * @return string fieldlist preformatted for select statement
	 * @access public
	*/
	public function _dl_check_fields($table, $fields);

	/**
	 * Check the given fields to be valid and to secure the sql statement
	 *
	 * @param string $table The name of the table to select from
	 * @param array $fields_ary fieldname value pairs for checking out
	 * @param string $part choose where|order to take place in the sql statement
	 * @return string fieldlist preformatted for select statement
	 * @access public
	*/
	public function _dl_check_fields_ary($table, $fields_ary, $part);

	/**
	 * Returns the valid field names of a given table
	 *
	 * @param string $table The name of the table to select from
	 * @return array doible array with table alias/fields
	 * @access public
	*/
	public function _dl_sql_allowed_fields($table);
}