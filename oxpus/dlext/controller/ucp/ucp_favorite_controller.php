<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\ucp;

use Symfony\Component\DependencyInjection\Container;

class ucp_favorite_controller implements ucp_favorite_interface
{
	protected $u_action;

	/* @var string phpBB root path */
	protected $root_path;

	/* @var string phpEx */
	protected $php_ext;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var Container */
	protected $phpbb_container;

	/** @var extension owned objects */
	protected $ext_path;

	protected $dlext_main;
	protected $dlext_nav;

	/**
	* Constructor
	*
	* @param \phpbb\extension\manager				$phpbb_extension_manager
	* @param string									$root_path
	* @param string									$php_ext
	* @param \phpbb\config\config					$config
	* @param \phpbb\request\request_interface 		$request
	* @param \phpbb\db\driver\driver_interfacer		$db
	* @param \phpbb\user							$user
	* @param \phpbb\language\language				$language
	* @param \phpbb\template\template				$template
	* @param \phpbb\controller\helper				$helper
	* @param Container 								$phpbb_container
	*/
	public function __construct(
		\phpbb\extension\manager $phpbb_extension_manager,
		$root_path,
		$php_ext,
		\phpbb\config\config $config,
		\phpbb\request\request_interface $request,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\controller\helper $helper,
		Container $phpbb_container,
		$dlext_main,
		$dlext_nav
	)
	{
		$this->root_path		= $root_path;
		$this->php_ext 			= $php_ext;
		$this->config 			= $config;
		$this->request			= $request;
		$this->db 				= $db;
		$this->user 			= $user;
		$this->language			= $language;
		$this->template 		= $template;
		$this->helper 			= $helper;
		$this->phpbb_container 	= $phpbb_container;

		$this->ext_path			= $phpbb_extension_manager->get_extension_path('oxpus/dlext', true);

		$this->dlext_main		= $dlext_main;
		$this->dlext_nav		= $dlext_nav;
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

			if (sizeof($fav_id))
			{
				$sql = 'DELETE FROM ' . DL_FAVORITES_TABLE . '
					WHERE ' . $this->db->sql_in_set('fav_id', $fav_id) . '
						AND fav_user_id = ' . (int) $this->user->data['user_id'];
				$this->db->sql_query($sql);
			}

			$message = $this->language->lang('DL_USER_CONFIG_SAVED', '<a href="' . $this->u_action . '">', '</a>');

			trigger_error($message);
		}

		/*
		* drop all unaccessable favorites
		*/
		$access_cat = [];
		$access_cat = $this->dlext_main->full_index(0, 0, 0, 1);

		if (sizeof($access_cat))
		{
			$sql = 'DELETE FROM ' . DL_FAVORITES_TABLE . '
				WHERE ' . $this->db->sql_in_set('fav_dl_cat', $access_cat, true) . '
					AND fav_user_id = ' . (int) $this->user->data['user_id'];
			$this->db->sql_query($sql);
		}

		/*
		* fetch all favorite downloads
		*/
		$sql = 'SELECT f.fav_id, d.description, d.cat, d.id FROM ' . DL_FAVORITES_TABLE . ' f, ' . DOWNLOADS_TABLE . ' d
			WHERE f.fav_dl_id = d.id
				AND f.fav_user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);

		$total_favorites = $this->db->sql_affectedrows($result);

		$this->template->assign_var('S_FAV_BLOCK', true);

		if ($total_favorites)
		{
			while ($row = $this->db->sql_fetchrow($result))
			{
				$path_dl_array = [];
				$tmp_nav = [];
				$dl_nav = $this->dlext_nav->nav($row['cat'], 'links', $tmp_nav);

				$this->template->assign_block_vars('favorite_row', [
					'DL_ID'			=> $row['fav_id'],
					'DL_CAT'		=> $dl_nav,
					'DOWNLOAD'		=> $row['description'],
					'U_DOWNLOAD'	=> $this->helper->route('oxpus_dlext_details', ['df_id' => $row['id'], 'cat_id' => $row['cat']]),
				]);
			}
		}

		$this->db->sql_freeresult($result);

		add_form_key('dl_ucp');

		$this->template->assign_vars([
			'DL_MOD_RELEASE'	=> $this->language->lang('DL_MOD_VERSION_PUBLIC'),
			'S_FORM_ACTION'		=> $this->u_action,
		]);
	}
}
