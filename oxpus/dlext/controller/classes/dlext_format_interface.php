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
interface dlext_format_interface
{
	/**
	 * Format a given number to readable file size value
	 *
	 * @param int $input_value number to be formatted
	 * @param int $rnd format number with the given decimals
	 * @param string $out_type return 'combine'd string or array with seperated values
	 * @return mixed string with amount and indicator or array with both values
	 * @access public
	*/
	public function dl_size($input_value, $rnd = 2, $out_type = 'combine');

	/**
	 * Build the rating stars - yeah rating... very important :-D
	 *
	 * @param int $rating_points entered rating points from download
	 * @param bool $rate true if the user can rate, false if not
	 * @param int $df_id download id used when the user can rate
	 * @return mixed rating images or html a element for rating
	 * @access public
	*/
	public function rating_img($rating_points, $rate = false, $df_id = 0);

	/**
	 * Resize several config values before saving
	 *
	 * @return string $config_name config name setting
	 * @return int $config_value config value
	 * @return int recalculated config value
	 * @access public
	*/
	public function resize_value($config_name, $config_value);
}
