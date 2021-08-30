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
 * Interface for auth_controller
 *
 */
interface auth_interface
{
	/**
	 * Fetch the global usergroup auth settings
	 *
	 * @param string $u_action the current module url
	 * @return array the default extension permissions for each category for user groups
	 * @access public
	 */
	public function dl_auth();

	/**
	 * Prepare the download index with all default permissions based on categories
	 *
	 * @return array download index data with download numbers and default permission settings
	 * @access public
	 */
	public function dl_index();

	/**
	 * Fetch the category auth settings
	 *
	 * @param int $cat_id category id for checks
	 * @return array all user permissions for the given category
	 * @access public
	 */
	public function dl_cat_auth($cat_id);

	/**
	 * Fetch the user administrator permissions
	 *
	 * @param string $u_action the current module url
	 * @return bool true when the user is an administrator, otherwise false
	 * @access public
	 */
	public function user_admin();

	/**
	 * Fetch the file extension blacklist
	 *
	 * @param string $u_action the current module url
	 * @return array unallowed file extensions for uploads
	 * @access public
	 */
	public function get_ext_blacklist();

	/**
	 * Fetch the user permissions for a given category and permission
	 *
	 * @param int $cat_id category id for checks
	 * @param string $perm the permission to ask for
	 * @return bool true when the user have the given permission on the given category, otherwise false
	 * @access public
	 */
	public function user_auth($cat_id, $perm);

	/**
	 * Fetch the permission to access the static page
	 *
	 * @param string $u_action the current module url
	 * @return bool true when the user can access the statistic page, otherwise false
	 * @access public
	 */
	public function stats_perm();

	/**
	 * Fetch the permission to read download comments
	 *
	 * @param int $cat_id category id for checks
	 * @return bool true when the user can read comments in the given category, otherwise false
	 * @access public
	 */
	public function cat_auth_comment_read($cat_id);

	/**
	 * Fetch the permission to post download comments
	 *
	 * @param int $cat_id category id for checks
	 * @return bool true when the user can post comments in the given category, otherwise false
	 * @access public
	 */
	public function cat_auth_comment_post($cat_id);

	/**
	 * Fetch the users which have the given permissions on the given category
	 *
	 * @param int $cat_id category id for checks
	 * @param string $perm the permission to ask for
	 * @return mixed (string and int) 0 if no user owns the given permission on the given category, otherwise string with all user ids for using in sql statements
	 * @access public
	 */
	public function dl_auth_users($cat_id, $perm);

	/**
	 * Fetch the bug tracker status
	 *
	 * @param string $u_action the current module url
	 * @return bool true if the bug tracker is enabled (by category and at least one download), otherwise false
	 * @access public
	 */
	public function bug_tracker();

	/**
	 * Chech the permission to enable/disable the captcha
	 *
	 * @param string $captcha_config setting for captcha status from constants DL_CAPTCHA_PERM_...
	 * @param int $cat_id category id for checks
	 * @return bool true to use the captcha, false to disable it
	 * @access public
	 */
	public function get_captcha_status($captcha_config, $cat_id);
}
