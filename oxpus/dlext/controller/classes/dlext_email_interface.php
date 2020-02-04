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
interface dlext_email_interface
{
	/**
	 * Send user information about a new/updated download
	 * Used in controllers
	 *  oxpus.dlext.acp_files_controller	(add/update)
	 *  oxpus.dlext.mcp_edit_controller  (update)
	 *  oxpus.dlext.upload				(add)
	 * 
 	 * @param mixed prepared mail data 
 	 * @return void
	 * @access public
	*/
	public function send_dl_notify($mail_data);

	/**
	 * Send user new status about a bug tracker entry
	 * Used in controller
	 *  oxpus.dlext.tracker
	 * 
 	 * @param mixed prepared mail data 
 	 * @return void
	 * @access public
	*/
	public function send_bt_status($mail_data);

	/**
	 * Send user the assignment to a bug tracker entry
	 * Used in controller
	 *  oxpus.dlext.tracker
	 * 
 	 * @param mixed prepared mail data 
 	 * @return void
	 * @access public
	*/
	public function send_bt_assign($mail_data);

	/**
	 * Send user notification about new comment to approve or just for information
	 * Used in controller
	 *  oxpus.dlext.details.php
	 * 
 	 * @param mixed prepared mail data 
 	 * @return void
	 * @access public
	*/
	public function send_comment_notify($mail_data);

	/**
	 * Send user notification to report a broken download
	 * Used in controller
	 *  oxpus.dlext.dlext_main
	 * 
 	 * @param mixed prepared mail data 
 	 * @return void
	 * @access public
	*/
	public function send_report($mail_data);
}
