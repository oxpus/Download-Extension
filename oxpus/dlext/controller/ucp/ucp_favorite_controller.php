<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace oxpus\dlext\controller\ucp;

class ucp_favorite_controller implements ucp_favorite_interface
{
	/* phpbb objects */
	protected $request;
	protected $db;
	protected $user;
	protected $language;
	protected $template;
	protected $helper;
	protected $notification;

	/* extension owned objects */
	public $u_action;
	protected $ext_path;

	protected $dlext_main;
	protected $dlext_nav;
	protected $dlext_constants;

	protected $dlext_table_dl_favorites;
	protected $dlext_table_downloads;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\user							$user
	 * @param \phpbb\language\language				$language
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\notification\manager			$notification
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\nav					$dlext_nav
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 * @param string								$dlext_table_dl_favorites
	 * @param string								$dlext_table_downloads
	 */
	public function __construct(
		\phpbb\request\request $request,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\controller\helper $helper,
		\phpbb\notification\manager $notification,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\nav $dlext_nav,
		\oxpus\dlext\core\helpers\constants $dlext_constants,
		$dlext_table_dl_favorites,
		$dlext_table_downloads
	)
	{
		$this->request			= $request;
		$this->db 				= $db;
		$this->user 			= $user;
		$this->language			= $language;
		$this->template 		= $template;
		$this->helper 			= $helper;
		$this->notification 	= $notification;

		$this->dlext_table_dl_favorites	= $dlext_table_dl_favorites;
		$this->dlext_table_downloads	= $dlext_table_downloads;

		$this->dlext_main		= $dlext_main;
		$this->dlext_nav		= $dlext_nav;
		$this->dlext_constants	= $dlext_constants;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		/*
		* init and get various values
		*/
		$submit = $this->request->variable('submit', '');

		if ($submit)
		{
			if (!check_form_key('dl_ucp'))
			{
				trigger_error('FORM_INVALID');
			}

			/*
			* drop all choosen favorites
			*/
			$fav_id = $this->request->variable('fav_id', [0 => '']);

			if (!empty($fav_id))
			{
				$sql = 'SELECT fav_dl_id FROM ' . $this->dlext_table_dl_favorites . '
					WHERE ' . $this->db->sql_in_set('fav_id', $fav_id) . '
						AND fav_user_id = ' . (int) $this->user->data['user_id'];
				$result = $this->db->sql_query($sql);

				$dl_ids = [];

				while ($row = $this->db->sql_fetchrow($result))
				{
					$dl_ids[] = $row['fav_dl_id'];
				}

				$this->db->sql_freeresult($result);

				$sql = 'DELETE FROM ' . $this->dlext_table_dl_favorites . '
					WHERE ' . $this->db->sql_in_set('fav_id', $fav_id) . '
						AND fav_user_id = ' . (int) $this->user->data['user_id'];
				$this->db->sql_query($sql);

				$this->notification->delete_notifications([
					'oxpus.dlext.notification.type.update',
					'oxpus.dlext.notification.type.comments',
				], $dl_ids, $this->dlext_constants::DL_FALSE, $this->user->data['user_id']);
			}

			$message = $this->language->lang('DL_USER_CONFIG_SAVED', '<a href="' . $this->u_action . '">', '</a>');

			trigger_error($message);
		}

		/*
		* drop all unaccessable favorites
		*/
		$access_cat = $this->dlext_main->full_index(0, 0, 0, $this->dlext_constants::DL_AUTH_CHECK_VIEW);

		if (!empty($access_cat))
		{
			$sql = 'SELECT fav_dl_id FROM ' . $this->dlext_table_dl_favorites . '
				WHERE ' . $this->db->sql_in_set('fav_dl_cat', $access_cat, $this->dlext_constants::DL_TRUE) . '
					AND fav_user_id = ' . (int) $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);

			$dl_ids = [];

			while ($row = $this->db->sql_fetchrow($result))
			{
				$dl_ids[] = $row['fav_dl_id'];
			}

			$this->db->sql_freeresult($result);

			$sql = 'DELETE FROM ' . $this->dlext_table_dl_favorites . '
				WHERE ' . $this->db->sql_in_set('fav_dl_cat', $access_cat, $this->dlext_constants::DL_TRUE) . '
					AND fav_user_id = ' . (int) $this->user->data['user_id'];
			$this->db->sql_query($sql);

			if (!empty($dl_ids))
			{
				$this->notification->delete_notifications([
					'oxpus.dlext.notification.type.update',
					'oxpus.dlext.notification.type.comments',
				], $dl_ids, $this->dlext_constants::DL_FALSE, $this->user->data['user_id']);
			}
		}

		/*
		* fetch all favorite downloads
		*/
		$sql = 'SELECT f.fav_id, d.description, d.cat, d.id FROM ' . $this->dlext_table_dl_favorites . ' f, ' . $this->dlext_table_downloads . ' d
			WHERE f.fav_dl_id = d.id
				AND f.fav_user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);

		$total_favorites = $this->db->sql_affectedrows();

		$this->template->assign_var('S_DL_FAV_BLOCK', $this->dlext_constants::DL_TRUE);

		if ($total_favorites)
		{
			while ($row = $this->db->sql_fetchrow($result))
			{
				$dl_nav = [];
				$this->dlext_nav->nav($row['cat'], $dl_nav);

				$this->template->assign_block_vars('dl_favorite_row', [
					'DL_ID'			=> $row['fav_id'],
					'DL_DOWNLOAD'	=> $row['description'],
					'U_DL_DOWNLOAD'	=> $this->helper->route('oxpus_dlext_details', ['df_id' => $row['id'], 'cat_id' => $row['cat']]),
				]);

				for ($i = count($dl_nav); $i > 0; --$i)
				{
					$key = $i - 1;

					$this->template->assign_block_vars('dl_favorite_row.dl_cat_path', [
						'DL_LINK'	=> $this->helper->route('oxpus_dlext_index', ['cat' => $dl_nav[$key]['cat_id']]),
						'DL_NAME'	=> $dl_nav[$key]['name'],
					]);
				}
			}
		}

		$this->db->sql_freeresult($result);

		add_form_key('dl_ucp');

		$this->template->assign_vars([
			'DL_MOD_RELEASE'	=> $this->language->lang('DL_MOD_VERSION_PUBLIC'),
			'S_DL_FORM_ACTION'		=> $this->u_action,
		]);
	}
}
