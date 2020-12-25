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
interface dlext_init_interface
{
	/**
	 * Get the forum root path
	 *
	 * @return string default phpBB root path
	 * @access public
	*/
	public function root_path();

	/**
	 * Get the default forum php-file extension
	 *
	 * @return string default php file extension
	 * @access public
	*/
	public function php_ext();

	/**
	 * Prefetch all basic download data
	 *
	 * @return array basic download data from each approved download dataset
	 * @access public
	*/
	public function dl_file_p();

	/**
	 * Fetch the download status icon for new or updated indications
	 *
	 * @return array new and updated indicators for download files
	 * @access public
	*/
	public function dl_file_icon();
}
