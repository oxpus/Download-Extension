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
interface dlext_counter_interface
{
	/**
	 * Fetch the number of unapproved downloads
	 * 
	 * @return int number of unapproved downloads
	 * @access public
	*/
	public function count_dl_approve();

	/**
	 * Fetch the number of unapproved download comments
	 * 
	 * @return int number of unapproved download comments
	 * @access public
	*/
	public function count_comments_approve();
}
