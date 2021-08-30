<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\core\helpers;

/**
 * Interface for footer
 *
 */
interface navigation_interface
{
	/**
	 * Set the module parameters for navigation path settings
	 *
	 * @param string $nav_view current page on breadcrumps
	 * @param int $cat_id current category
	 * @param int $df_id current download
	 * @return void
	 * @access public
	 */
	public function set_parameter($nav_view = '', $cat_id = 0, $df_id = 0);

	/**
	 * Module main part
	 *
	 * @return void
	 * @access public
	 */
	public function handle();
}
