<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\classes;

/**
 * Interface for acp_banlist_controller
 *
 */
interface dlext_files_interface
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
	 * @param int $cat_id downloads only from this category
	 * @param string $sql_sort_by fields for sorting the files
	 * @param string $sql_order sort direction ASC or DESC
	 * @param string $extra_where additional conditions for selection
	 * @param int $df_id select only this download dataset
	 * @param bool $modcp true will fetch all downloads, false only approved ones
	 * @param string $sql_fields fields which data are needed
	 * @param int $sql_limit amount of files for a limited select, 0 will select unlimited
	 * @return array all download data we asked for
	 * @access public
	*/
	public function all_files($cat_id, $sql_sort_by, $sql_order, $extra_where, $df_id, $modcp, $sql_fields, $sql_limit = 0);
}
