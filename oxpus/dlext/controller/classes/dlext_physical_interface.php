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
interface dlext_physical_interface
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
	 * @param string $path the asked folder to be read out
	 * @param string $cur for internal uses, needs to be $path on start
	 * @return string option list element
	 * @access public
	*/
	public function read_dl_dirs($path = '', $cur = '');

	/**
	 * Read the real files from the download folders which are nor assigned
	 * 
	 * @param string $path the asked folder to be read out
	 * @param array $unas_files for internal uses, needs to be $path on start
	 * @return array existing and unassigned file names
	 * @access public
	*/
	public function read_dl_files($path = '', $unas_files = []);

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
	 * Fetch the number of unapproved downloads
	 * 
	 * @param string $path physical generate the folder path
	 * @return void
	 * @access public
	*/
	public function _create_folder($path);

	/**
	 * Check all required download folders and create missing ones
	 * 
	 * @return void
	 * @access public
	*/
	public function check_folders();

	/**
	 * Drop the old download folder structures with all files and folders
	 * 
	 * @param string $source_path the source path from previous extension settings
	 * @return bool false if the removing fails, otherwise true
	 * @access public
	*/
	public function _drop_dl_basis($source_path);

	/**
	 * Read the existing downloads folder tree to select a path within the categories management
	 * instead enter the complete path "old school" manually.
	 * 
	 * @param string $file_base the start folder to be read out
	 * @param string $path the path to be preselected
	 * @param int $level for internal uses only
	 * @return array folder tree with seperated values
	 * @access public
	*/
	public function get_file_base_tree($file_base, $path, $level = 0);
}
