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
interface dlext_thumbs_interface
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
