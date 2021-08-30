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
 * Interface for cache_controller
 *
 */
interface cache_interface
{
	/**
	 * Download Extension Category Cache
	 *
	 * @return array complete download index data
	 * @access public
	 */
	public function obtain_dl_cats();

	/**
	 * Download Extension File Extension Blacklist Cache
	 *
	 * @return array complete file extension blacklist data
	 * @access public
	 */
	public function obtain_dl_blacklist();

	/**
	 * Download Extension Cat Filecount Cache
	 *
	 * @return array number of downloads saved on each category
	 * @access public
	 */
	public function obtain_dl_cat_counts();

	/**
	 * Download Extension Files Cache
	 *
	 * @param int $dl_new_time timespan to indicate a download as new
	 * @param int $dl_edit_time timespan to indicate a download as updated
	 * @return array combined download data about new or updated status for each file
	 * @access public
	 */
	public function obtain_dl_files($dl_new_time, $dl_edit_time);

	/**
	 * Download Extension Auth Cache
	 *
	 * @return array complete download auth data based on user groups
	 * @access public
	 */
	public function obtain_dl_auth();

	/**
	 * Download Extension Auth Group Settings Cache
	 *
	 * @param array $auth_cat category ids used in auth table
	 * @param int $user_id user id to proof permissions fore
	 * @param array $auth_perm predefined permissions
	 * @param int $group_perm_ids group ids used in auth table
	 * @return array complete download auth group data based on user groups
	 * @access public
	 */
	public function obtain_dl_access_groups($auth_cat, $user_id, $auth_perm, $group_perm_ids = []);
}
