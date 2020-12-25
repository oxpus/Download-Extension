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
interface dlext_privacy_interface
{
	/**
	 * Anonymise ip adresses on statistical data if events fired
	 *
	 * @return void
	 * @access public
	*/
	public function dl_privacy();
}
