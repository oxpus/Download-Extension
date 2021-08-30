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
 * Interface for main_controller
 *
 */
interface main_interface
{
	/**
	 * Fetch the complete download index without sublevel data or category ids with for a given permission
	 *
	 * @param int $only_cat return data only for the given category, 0 returns all category data
	 * @param int $parent index tree start id
	 * @param int $level index level, only for internal uses
	 * @param int $auth_level 0 return data for all categories, otherwise for the given permission
	 * @return array complete index data or category ids
	 * @access public
	 */
	public function full_index($only_cat = 0, $parent = 0, $level = 0, $auth_level = 0);

	/**
	 * Fetch the complete download index with sublevel data
	 *
	 * @param int $parent index tree start id
	 * @return array complete index data
	 * @access public
	 */
	public function index($parent = 0);

	/**
	 * Fetch the sublevel index data
	 *
	 * @param int $parent index tree start id
	 * @return array sublevel index data
	 * @access public
	 */
	public function get_sublevel($parent = 0);

	/**
	 * Fetch the number of downloads in subcategories
	 *
	 * @param int $parent index tree start id
	 * @return int number of downloads
	 * @access public
	 */
	public function get_sublevel_count($parent = 0);

	/**
	 * Fetch the number of subcategories
	 *
	 * @param int $parent index tree start id
	 * @return int number of subcategories
	 * @access public
	 */
	public function count_sublevel($parent);

	/**
	 * Calculate the latest download based on the displayed category
	 *
	 * @param array $last_data prefetched last download data
	 * @param int $parent index tree start id
	 * @param int $main_cat current category id, used internal only, on start this must be equal to $parent
	 * @param int $last_dl_time for internal use only, on start this must be 0
	 * @return array last time and category id from last download
	 * @access public
	 */
	public function find_latest_dl($last_data, $parent, $main_cat, $last_dl_time);

	/**
	 * Prund old stats data
	 *
	 * @param int $cat category id which statistic data should be pruned
	 * @param int $stats_prune number of latest statistic data which will be keeped
	 * @return bool true on successfull pruning, otherwise a default phpBB database error message
	 * @access public
	 */
	public function dl_prune_stats($cat_id, $stats_prune);
}
