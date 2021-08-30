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
 * Interface for thumbnail_controller
 *
 */
interface thumbnail_interface
{
	/**
	 * Read and display the spezified thumbnail for a download
	 *
	 * @return void
	 * @access public
	 */
	public function handle();

	/**
	 * Prepare image from thumbnail file
	 *
	 * @param string $pic_filename image filename to be examined
	 * @param string $file_ext delete or close topics
	 * @return object $image
	 * @access private
	 */
	public function _get_image($pic_path, $file_ext);
}
