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
 * Interface for counter_controller
 *
 */
interface counter_interface
{
	/**
	 * Fetch the number of broken downloads
	 *
	 * @return int number of broken downloads
	 * @access public
	 */
	public function count_dl_broken();

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

	/**
	 * Fetch the number of external download
	 *
	 * @return int number of external download
	 * @access public
	 */
	public function count_external_files();
}
