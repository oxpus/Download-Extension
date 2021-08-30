<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class unfav
{
	/* phpbb objects */
	protected $db;
	protected $helper;
	protected $request;
	protected $user;
	protected $notification;

	/* extension owned objects */
	protected $dlext_constants;

	protected $dlext_table_dl_favorites;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\user							$user
	 * @param \phpbb\notification\manager			$notification
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_favorites
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\user $user,
		\phpbb\notification\manager $notification,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_favorites
	)
	{
		$this->db 						= $db;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->user 					= $user;
		$this->notification				= $notification;

		$this->dlext_constants			= $dlext_constants;

		$this->dlext_table_dl_favorites	= $dlext_table_dl_favorites;
	}

	public function handle()
	{
		$df_id		= $this->request->variable('df_id', 0);
		$cat_id		= $this->request->variable('cat_id', 0);
		$fav_id		= $this->request->variable('fav_id', 0);

		/*
		* drop favorite for the choosen download
		*/
		if ($fav_id && $df_id && $cat_id && $this->user->data['is_registered'])
		{
			$sql = 'DELETE FROM ' . $this->dlext_table_dl_favorites . '
				WHERE fav_id = ' . (int) $fav_id . '
					AND fav_dl_id = ' . (int) $df_id . '
					AND fav_user_id = ' . (int) $this->user->data['user_id'];
			$this->db->sql_query($sql);

			$this->notification->delete_notifications([
				'oxpus.dlext.notification.type.update',
				'oxpus.dlext.notification.type.comments',
			], $df_id, $this->dlext_constants::DL_FALSE, $this->user->data['user_id']);

			redirect($this->helper->route('oxpus_dlext_details', ['df_id' => $df_id, 'cat_id' => $cat_id]));
		}

		redirect($this->helper->route('oxpus_dlext_index'));
	}
}
