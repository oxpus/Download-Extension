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
interface dlext_cache_interface
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
	 * Download MOD Auth Group Settings Cache
	 * @param array $auth_cat category ids used in auth table
	 * @param int $group_perm_ids group ids used in auth table
	 * @param int $user_id user id to proof permissions fore
	 * @return array complete download auth group data based on user groups
	 * @access public
	*/
	public function obtain_dl_access_groups($auth_cat, $group_perm_ids, $user_id);

	/**
	 * Get saved cache object
	 * 
	 * @param string $var_name name of the cache file
	 * @return array cache data if exists, otherwise false
	 * @access public
	*/
	public function get($var_name);

	/**
	 * Put data into cache
	 * 
	 * @param string $var_name name of the cache file
	 * @param mixed $var cache content
	 * @param string $ttl Timestamp when the data expires
	 * @return void
	 * @access public
	*/
	public function put($var_name, $var, $ttl = 31536000);

	/**
	 * Check if a given cache entry exist
	 * 
	 * @param string $var_name name of the cache file
	 * @return bool true when the cache exists and is valid, otherwise false
	 * @access public
	*/
	public function _exists($var_name);

	/**
	 * Read cached data from a specified file
	 *
	 * @param string $filename Filename to read
	 * @return mixed False if an error was encountered, otherwise the data type of the cached data
	 * @access public
	 */
	public function _read($filename);

	/**
	 * Write cache data to a specified file
	 *
	 * @param string $filename Filename to write
	 * @param mixed $data Data to store
	 * @param int $expires Timestamp when the data expires
	 * @param string $query Query when caching SQL queries
	 * @return bool True if the file was successfully created, otherwise false
	 * @access public
	 */
	public function _write($filename, $data = null, $expires = 0, $query = '');

	/**
	 * Removes/unlinks file
	 * 
	 * @param string $filename Filename to delete
	 * @param bool $check check if the file is writeable, returns message if fails
	 * @return mixed true if the files was removed/inlinked, otherwise false
	 * @access public
	*/
	public function remove_file($filename, $check = false);
}
