<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\acp;

/**
 * Interface for acp_browser_controller
 *
 */
interface acp_browser_interface
{
	/**
	 * Set the global module path
	 *
	 * @param string $u_action the current module url
	 * @return void
	 * @access public
	 */
	public function set_action($u_action);

	/**
	 * Module main part
	 *
	 * @return void
	 * @access public
	 */
	public function handle();

	/**
	 * Save changed user agents file
	 *
	 * @param array $agent_title the titles of all submitted user agents
	 * @param array $agent_strings the descriptions of all submitted user agents
	 * @param string $data_file data file to save all agent data
	 * @return bool
	 * @access public
	 */
	public function _save_ua_file($agent_title, $agent_strings, $data_file);
}
