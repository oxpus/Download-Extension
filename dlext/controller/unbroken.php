<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2021 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller;

class unbroken
{
	/* phpbb objects */
	protected $notification;
	protected $db;
	protected $helper;
	protected $request;

	/* extension owned objects */
	protected $dlext_auth;
	protected $dlext_main;

	protected $dlext_table_downloads;

	/**
	* Constructor
	*
	* @param \phpbb\notification\manager			$notification
	* @param \phpbb\db\driver\driver_interface		$db
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\request\request 				$request
	* @param \oxpus\dlext\core\auth					$dlext_auth
	* @param \oxpus\dlext\core\main					$dlext_main
	* @param string									$dlext_table_downloads
	*/
	public function __construct(
		\phpbb\notification\manager $notification,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\main $dlext_main,
		$dlext_table_downloads
	)
	{
		$this->notification				= $notification;
		$this->db 						= $db;
		$this->helper 					= $helper;
		$this->request					= $request;

		$this->dlext_table_downloads	= $dlext_table_downloads;

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_main				= $dlext_main;
	}

	public function handle()
	{
		$cat		= $this->request->variable('cat', 0);
		$df_id		= $this->request->variable('df_id', 0);
		$cat_id		= $this->request->variable('cat_id', 0);

		$index 		= ($cat) ? $this->dlext_main->index($cat) : $this->dlext_main->index();

		/*
		* reset reported broken download if allowed
		*/
		if ($df_id && $cat_id)
		{
			$cat_auth = $this->dlext_auth->dl_cat_auth($cat_id);

			if (isset($index[$cat_id]['auth_mod']) && $index[$cat_id]['auth_mod'] || isset($cat_auth['auth_mod']) && $cat_auth['auth_mod'] || $this->dlext_auth->user_admin())
			{
				$sql = 'UPDATE ' . $this->dlext_table_downloads . ' SET ' . $this->db->sql_build_array('UPDATE', [
					'broken' => 0]) . ' WHERE id = ' . (int) $df_id;
				$this->db->sql_query($sql);
			}

			$this->notification->delete_notifications('oxpus.dlext.notification.type.broken', $df_id);

			redirect($this->helper->route('oxpus_dlext_details', ['df_id' => $df_id, 'cat_id' => $cat_id]));
		}

		redirect($this->helper->route('oxpus_dlext_index'));
	}
}
