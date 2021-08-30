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
 * Interface for status_controller
 *
 */
interface status_interface
{
	/**
	 * Fetch the number of unapproved downloads
	 *
	 * @param int $parent index tree start id
	 * @param int $file_id download id
	 * @param bool $rss false will return the status icon, true text for the rss feed
	 * @return array mini status data
	 * @access public
	 */
	public function mini_status_file($parent, $file_id, $rss = false);

	/**
	 * Fetch the status icon for categories about new and/or updated downloads
	 *
	 * @param int $cur id from current category
	 * @param int $parent index tree start id
	 * @param int $flag 0 start with the current parent id, otherwise the sub categories
	 * @return array mini status data
	 * @access public
	 */
	public function mini_status_cat($cur, $parent, $flag = 0);

	/**
	 * Fetch the download access status and status icon
	 *
	 * @param int $df_id download id to be checked
	 * @return array access, status icon and link for given download
	 * @access public
	 */
	public function status($df_id);
}
