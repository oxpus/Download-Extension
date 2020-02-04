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
interface dlext_extra_interface
{
	/**
	 * Fetch the todo data
	 * 
	 * @return array the complete todo data to be displayed
	 * @access public
	*/
	public function get_todo();

	/**
	 * Build the download selection element for all categories used in acp settings or for the jumpbox
	 * 
	 * @param int $parent number of parent category
	 * @param int $level current level, internal use only
	 * @param int $select_cat current category to be preselected in the dropdown
	 * @param int $perm fetch only categories for this user permission
	 * @param int $rem_cat current category to preselect the option list
	 * @return string prebuild options for select element
	 * @access public
	*/
	public function dl_dropdown($parent = 0, $level = 0, $select_cat = 0, $perm, $rem_cat = 0);

	/**
	 * Build the download multi election element for all categories
	 * 
	 * @param int $parent number of parent category
	 * @param int $level current level, internal use only
	 * @param array $select_cat category ids to preselect them in the option list
	 * @return string prebuild options for select element
	 * @access public
	*/
	public function dl_cat_select($parent = 0, $level = 0, $select_cat = array());

	/**
	 * Switch user id to username and back
	 * 
	 * @param int $user_id user_id to be converted to username
	 * @param string $username username to be converted to user_id
	 * @param bool $update true for change an username into user_id
	 * @return mixed user id or username based on the entered data
	 * @access public
	*/
	public function dl_user_switch($user_id = 0, $username = '', $update = false);
}
