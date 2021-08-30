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
 * Interface for privacy_controller
 *
 */
interface privacy_interface
{
	/**
	 * Anonymise ip adresses on statistical data if events fired
	 *
	 * @return void
	 * @access public
	 */
	public function dl_privacy();
}
