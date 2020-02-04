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
interface dlext_nav_interface
{
	/**
	 * Build the breadcrumbs
	 * 
	 * @param int $parent category id to starts from
	 * @param string $disp_art url will return the combined linked breadcrumbs, otherwise an array with the separated values will be returned
	 * @param string $tmp_nav contains the array data if they should be returned
	 * @param string $basic_link start link in front of the breadcrumbs, needs to be contain #CAT# to insert $parent in the url
	 * @return mixed array with the breadcrumb data or combined string with linked breadcrumbs
	 * @access public
	*/
	public function nav($parent, $disp_art, &$tmp_nav, $basic_link = '');
}
