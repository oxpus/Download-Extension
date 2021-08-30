<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\ucp;

/**
 * Interface for ucp_config_controller
 *
 */
interface ucp_config_interface
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
}
