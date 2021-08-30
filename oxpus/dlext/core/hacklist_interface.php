<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

/**
 * Interface for hacklist_controller
 *
 */
interface hacklist_interface
{
	/**
	 * Fetch the number of unapproved downloads
	 *
	 * @return array index with all accessable download categories
	 * @access public
	 */
	public function hacks_index();

	/**
	 * Fetch the number of unapproved downloads
	 *
	 * @param string $sql_sort_by fields for sorting the files
	 * @param string $sql_order sort direction ASC or DESC
	 * @param int $start start value for a limited select
	 * @param int $total limit value for a limited select, 0 returns just the number of downloads
	 * @return mixed all download files data or if total = 0 just the number of downloads for pagination
	 * @access public
	 */
	public function all_files($sql_sort_by, $sql_order, $start = 0, $total = 0);
}
