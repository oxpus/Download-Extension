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
 * Interface for nav_controller
 *
 */
interface nav_interface
{
	/**
	 * Build the breadcrumbs
	 *
	 * @param int $parent category id to starts from
	 * @param string $tmp_nav contains the array data if they should be returned
	 * @return mixed array with the breadcrumb data or combined string with linked breadcrumbs
	 * @access public
	 */
	public function nav($parent, &$tmp_nav);
}
