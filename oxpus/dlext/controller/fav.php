<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller;

class fav
{
	/* phpbb objects */
	protected $db;
	protected $helper;
	protected $request;
	protected $user;

	/* extension owned objects */
	protected $dlext_table_dl_favorites;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\user							$user
	 * @param string								$dlext_table_dl_favorites
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\phpbb\user $user,
		$dlext_table_dl_favorites
	)
	{
		$this->db 						= $db;
		$this->helper 					= $helper;
		$this->request					= $request;
		$this->user 					= $user;

		$this->dlext_table_dl_favorites	= $dlext_table_dl_favorites;
	}

	public function handle()
	{
		$df_id		= $this->request->variable('df_id', 0);
		$cat_id		= $this->request->variable('cat_id', 0);

		/*
		* set favorite for the choosen download
		*/
		if ($df_id && $cat_id && $this->user->data['is_registered'])
		{
			$sql = 'SELECT COUNT(fav_dl_id) AS total FROM ' . $this->dlext_table_dl_favorites . '
				WHERE fav_dl_id = ' . (int) $df_id . '
					AND fav_user_id = ' . (int) $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);
			$fav_check = $this->db->sql_fetchfield('total');
			$this->db->sql_freeresult($result);

			if (!$fav_check)
			{
				$sql = 'INSERT INTO ' . $this->dlext_table_dl_favorites . ' ' . $this->db->sql_build_array('INSERT', [
					'fav_dl_id'		=> $df_id,
					'fav_dl_cat'	=> $cat_id,
					'fav_user_id'	=> $this->user->data['user_id']
				]);
				$this->db->sql_query($sql);
			}

			redirect($this->helper->route('oxpus_dlext_details', ['df_id' => $df_id, 'cat_id' => $cat_id]));
		}

		redirect($this->helper->route('oxpus_dlext_index'));
	}
}
