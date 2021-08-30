<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\acp;

/**
 * Interface for acp_config_controller
 *
 */
interface acp_config_interface
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
	 * Build radio select to enable/disable the mod
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild radio button input element
	 * @access public
	 */
	public function mod_disable($value);

	/**
	 * Build radio select for rss permissions
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild radio button input element
	 * @access public
	 */
	public function rss_perm($value);

	/**
	 * Build radio select for rss content selection
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild radio button input element
	 * @access public
	 */
	public function rss_select($value);

	/**
	 * Build custom select for cat edit links
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_dl_cat_edit($value);

	/**
	 * Build custom select for dl hash algorythmus
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_dl_hash_algo($value);

	/**
	 * Build custom select for forum select to post dl topic
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_dl_forum($dl_topic_forum);

	/**
	 * Build custom select for captcha control
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_dl_vc($value);

	/**
	 * Build custom select for hotlink action
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_hotlink_action($value);

	/**
	 * Build custom select for report action
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_report_action($value);

	/**
	 * Build custom select for report captcha control
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_report_vc($value);

	/**
	 * Build custom select for rss content control
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_rss_cats($value);

	/**
	 * Build custom select for rss content length
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_rss_length($value);

	/**
	 * Build custom select for actions on a disabled rss feed
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_rss_off_action($value);

	/**
	 * Custom value formatting
	 *
	 * @param string $value default value for the current setting
	 * @param string $field field name of the config data
	 * @param string $size size of input element
	 * @param string $maxlength maxsize of input element
	 * @param string $quote name of select element
	 * @param string $max_quote max value indicator (byte, kb, mb, gb)
	 * @param string $remain display the remain value for the given field on true, otherwise hide the remain data
	 * @return string input element for number settings with indicator select
	 * @access public
	 */
	public function select_size($value, $field, $size, $maxlength, $quote, $max_quote, $remain = false);

	/**
	 * Build custom select for sort downloads
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_sort($value);

	/**
	 * Build custom select for permissions on stats page
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_stat_perm($value);

	/**
	 * Build custom select for dl topic details
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_topic_details($value);

	/**
	 * Build custom select for dl topic poster
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_topic_user($value);

	/**
	 * Build custom select for global traffic settings
	 *
	 * @param string $value current value to preselect the right option
	 * @param string $total_groups additions select options
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_traffic($value, $total_groups);

	/**
	 * Build custom select for multi selects
	 *
	 * @param string $value current value to preselect the right option
	 * @param string $s_select complete prebuild select options
	 * @param string $select_size define multiselect rows
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_traffic_multi($field, $s_select, $select_size);

	/**
	 * Build custom select for textarea inputs
	 *
	 * @param string $value current value to preselect the right option
	 * @param string $field name of the text area
	 * @param string $cols cols for the text elements (width)
	 * @param string $rows rows for the text elements (height)
	 * @return string prebuild textarea element
	 * @access public
	 */
	public function textarea_input($value, $field, $cols, $rows);

	/**
	 * Build custom select for extension statistics
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_dl_ext_stats($value);

	/**
	 * Build custom select for dl topic type
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_topic_type($value);

	/**
	 * Build custom select for dl user select
	 *
	 * @param string $value current value to preselect the right option
	 * @return string prebuild select element
	 * @access public
	 */
	public function select_dl_user($value, $config);
}
