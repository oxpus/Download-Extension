<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/


namespace oxpus\dlext\core;

/**
 * Interface for acp_banlist_controller
 *
 */
interface physical_interface
{
	/**
	 * Grab all real file names from the download data
	 *
	 * @return array real filenames from downloads
	 * @access public
	*/
	public function read_exist_files();

	/**
	 * Read the folder tree from download folders and prepare option list
	 *
	 * @return string option list element
	 * @access public
	*/
	public function read_dl_dirs();

	/**
	 * Read the physical file sizes from the download files
	 *
	 * @param string $download_dir the asked folder to be read out
	 * @return int sumerized download file sizes
	 * @access public
	*/
	public function read_dl_sizes($download_dir = '');

	/**
	 * Fetch the number of unapproved downloads
	 *
	 * @return string combined amount of maximum upload file size limit by PHP itself
	 * @access public
	*/
	public function dl_max_upload_size();

	/**
	 * Read the existing downloads folder tree to select a path within the categories management
	 * instead enter the complete path "old school" manually.
	 *
	 * @param string $path the path to be preselected
	 * @param bool $check if set will return only dirlist
	 * @return array folder tree with seperated values
	 * @access public
	*/
	public function get_file_base_tree($selected_path = '', $check = 0);

	/**
	 * Send the selected file to the user client (webbrowser) = download
	 *
	 * @param array $dl_file_data data array with all informations about the download file
	 * @return void
	 * @access public
	*/
	public function send_file_to_browser($dl_file_data);
}
