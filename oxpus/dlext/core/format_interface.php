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
 * Interface for format_controller
 *
 */
interface format_interface
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
	 * @param string $config_name config name setting
	 * @param int $config_value config value
	 * @return int recalculated config value
	 * @access public
	 */
	public function resize_value($config_name, $config_value);

	/**
	 * Transform traffic value into bytes for storage
	 *
	 * @param int $traffic_amount amount of traffic value
	 * @param string $traffic_range indicator for value range e. g. KB or MB
	 * @return int recalculated value in bytes for storage
	 * @access public
	 */
	public function get_traffic_save_value($traffic_amount, $traffic_range);

	/**
	 * Format traffic values from bytes into readable size
	 *
	 * @param int $traffic_amount amount of stored traffic value
	 * @return mixed recalculated amount into range value
	 * @access public
	 */
	public function get_traffic_display_value($traffic_amount);

	/**
	 * Generate hash value for posts or other cases e. g. for file names
	 *
	 * @param string $value string to generate hash about
	 * @param string $type 'post' for posts message, 'file' for file hash, empty for other cases
	 * @param string $method hash method, md5 as default if empty
	 * @return string generated hash string
	 * @access public
	 */
	public function dl_hash($value, $type = '', $method = '');

	/**
	 * Shorten texts if option is setting to this
	 *
	 * @param string $text the text which will be reformatted with full length or shorton
	 * @param string $mode workflow for the given page
	 * @param string $uid bbcode uid
	 * @param string $bitfield bbcode bitfield
	 * @param int $flags bbcode flags
	 * @return string reformatted text
	 * @access public
	 */
	public function dl_shorten_string($text, $mode, $uid, $bitfield, $flags);
}
