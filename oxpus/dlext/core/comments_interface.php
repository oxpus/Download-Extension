<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright (c) 2021-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core;

/**
 * Interface for comments_controller
 *
 */
interface comments_interface
{
	/**
	 * Initialize the comments auth
	 *
	 * @param int $cat_id number of download category
	 * @access public
	 */
	public function set_auth_comments($cat_id);

	/**
	 * Fetch the comment post permission
	 *
	 * @param int $cat_id number of download category
	 * @return bool true/false for comment post permission
	 * @access public
	 */
	public function get_auth_comment_post($cat_id);

	/**
	 * Fetch the comment moderate permission
	 *
	 * @param int $cat_id number of download category
	 * @return bool true/false for comment moderate permission
	 * @access public
	 */
	public function get_auth_comment_manage($cat_id);

	/**
	 * Save an entered comment post
	 *
	 * @param int $cat_id number of download category
	 * @param int $df_id number of download
	 * @param int $dl_id number of download comment post
	 * @return string returns "view" if failed on saving
	 * @access public
	 */
	public function save_comment($cat_id, $df_id, $dl_id = 0);

	/**
	 * Delete a saved comment post
	 *
	 * @param int $cat_id number of download category
	 * @param int $df_id number of download
	 * @param int $dl_id number of download comment post
	 * @return string returns "view" on failure or no other comments exists for download
	 * @access public
	 */
	public function delete_comment($cat_id, $df_id, $dl_id = 0);

	/**
	 * Display the comment post form
	 *
	 * @param string $action current action mode e.g. "edit" to update a post
	 * @param int $cat_id number of download category
	 * @param int $df_id number of download
	 * @param int $dl_id number of download comment post
	 * @access public
	 */
	public function display_post_form($action, $cat_id, $df_id, $dl_id = 0);

	/**
	 * Display all saved comments of the download
	 *
	 * @param int $cat_id number of download category
	 * @param int $df_id number of download
	 * @param int $start the current page of comment post list
	 * @return int number of existing comments
	 * @access public
	 */
	public function display_comments($cat_id, $df_id, $start);
}
