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
 * @package acp
 */
class acp_perm_check_controller implements acp_perm_check_interface
{
	/* phpbb objects */
	protected $db;
	protected $user;
	protected $auth;
	protected $phpex;
	protected $root_path;
	protected $config;
	protected $request;
	protected $template;

	/* extension owned objects */
	public $u_action;

	protected $dlext_auth;
	protected $dlext_format;
	protected $dlext_main;
	protected $dlext_constants;

	/**
	 * Constructor
	 *
	 * @param string								$root_path
	 * @param string								$phpex
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\request\request 				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\auth\auth						$auth
	 * @param \phpbb\user							$user
	 * @param \oxpus\dlext\core\auth				$dlext_auth
	 * @param \oxpus\dlext\core\format				$dlext_format
	 * @param \oxpus\dlext\core\main				$dlext_main
	 * @param \oxpus\dlext\core\helpers\constants	$dlext_constants
	 */
	public function __construct(
		$root_path,
		$phpex,
		\phpbb\config\config $config,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\auth\auth $auth,
		\phpbb\user $user,
		\oxpus\dlext\core\auth $dlext_auth,
		\oxpus\dlext\core\format $dlext_format,
		\oxpus\dlext\core\main $dlext_main,
		\oxpus\dlext\core\helpers\constants $dlext_constants
	)
	{
		$this->root_path			= $root_path;
		$this->phpEx				= $phpex;
		$this->db					= $db;
		$this->auth					= $auth;
		$this->user					= $user;

		$this->config				= $config;
		$this->request				= $request;
		$this->template				= $template;

		$this->dlext_auth			= $dlext_auth;
		$this->dlext_format			= $dlext_format;
		$this->dlext_main			= $dlext_main;
		$this->dlext_constants		= $dlext_constants;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$check_user			= $this->request->variable('check_user', '', $this->dlext_constants::DL_TRUE);
		$submit				= $this->request->variable('submit', '');
		$user_id			= $this->request->variable('user_id', 0);

		$s_display_perms = $this->dlext_constants::DL_FALSE;

		$dl_index = $this->dlext_main->full_index();

		if (empty($dl_index))
		{
			$this->u_action = str_replace('mode=perm_check', 'mode=assistant', $this->u_action);
			redirect($this->u_action);
		}

		if ($submit && $check_user)
		{
			$username = utf8_clean_string($check_user);

			$sql = 'SELECT * FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . $this->db->sql_escape($username) . "'";
			$result			= $this->db->sql_query($sql);
			$row			= $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				// First fetch ALL category permissions
				$cat_perm_ary   = [];

				// Check for selected user and reinit the download classes to get the right content
				$reset_user_data = $this->dlext_constants::DL_FALSE;
				if ($user_id != $this->user->data['user_id'])
				{
					$tmp_user_data = $this->user->data;
					$this->user->data = $row;
					$this->user->data['is_registered'] = $this->dlext_constants::DL_TRUE;
					$this->user->data['session_browser'] = $tmp_user_data['session_browser'];
					$this->user->data['session_ip'] = $tmp_user_data['session_ip'];
					$this->auth->acl($this->user->data);
					$reset_user_data = $this->dlext_constants::DL_TRUE;
				}

				foreach (array_keys($dl_index) as $cat_id)
				{
					$cat_perm_ary[$cat_id]['cat_name']		= $dl_index[$cat_id]['cat_name'];
					$cat_perm_ary[$cat_id]['auth_view']		= $this->dlext_auth->user_auth($cat_id, 'auth_view');
					$cat_perm_ary[$cat_id]['auth_dl']		= $this->dlext_auth->user_auth($cat_id, 'auth_dl');
					$cat_perm_ary[$cat_id]['auth_up']		= $this->dlext_auth->user_auth($cat_id, 'auth_up');
					$cat_perm_ary[$cat_id]['auth_mod']		= $this->dlext_auth->user_auth($cat_id, 'auth_mod');
					$cat_perm_ary[$cat_id]['comment_read']	= $this->dlext_auth->cat_auth_comment_read($cat_id);
					$cat_perm_ary[$cat_id]['comment_post']	= $this->dlext_auth->cat_auth_comment_post($cat_id);

					$cat_perm_ary[$cat_id]['cat_remain']    = ($this->config['dl_traffic_off']) ? $this->dlext_constants::DL_TRUE : $this->dlext_constants::DL_FALSE;
					if (($dl_index[$cat_id]['cat_traffic'] && ($dl_index[$cat_id]['cat_traffic'] - $dl_index[$cat_id]['cat_traffic_use'] <= 0)) && !$this->config['dl_traffic_off'])
					{
						if ($this->dlext_constants->get_value('founder_traffics'))
						{
							$cat_perm_ary[$cat_id]['cat_remain'] = $this->dlext_constants::DL_TRUE;
						}
					}
				}

				// General user permissions
				$this->template->assign_vars([
					'DL_USER_IS_ADMIN'         => $this->dlext_auth->user_admin(),
					'DL_USER_CAN_VIEW_STATS'   => $this->dlext_auth->stats_perm(),
					'DL_USER_CAN_SEE_TRACKER'  => $this->dlext_auth->bug_tracker(),
					'DL_USER_HAVE_TRAFFIC'     => $this->dlext_format->dl_size($this->user->data['user_traffic']),
					'DL_USER_HAVE_POSTS'       => $this->user->data['user_posts'] . ' / ' . $this->config['dl_posts'],
					'DL_CHECK_USERNAME'        => $this->user->data['username'],

					'U_DL_BACK'					=> $this->u_action,
				]);

				foreach ($cat_perm_ary as $data_ary)
				{
					$this->template->assign_block_vars('dl_cat_row', [
						'DL_CAT_NAME'  => $data_ary['cat_name'],
						'DL_CAT_VIEW'  => $data_ary['auth_view'],
						'DL_CAT_DL'    => $data_ary['auth_dl'],
						'DL_CAT_UP'    => $data_ary['auth_up'],
						'DL_CAT_MOD'   => $data_ary['auth_mod'],
						'DL_CAT_CREAD' => $data_ary['comment_read'],
						'DL_CAT_CPOST' => $data_ary['comment_post'],
					]);
				}

				// Reset userdata to the real current user and reinit the download classes to get the right content
				if ($reset_user_data)
				{
					$this->user->data = $tmp_user_data;
					unset($tmp_user_data);
					$this->auth->acl($this->user->data);
				}

				$s_display_perms = $this->dlext_constants::DL_TRUE;
			}
		}

		$u_user_select = append_sid($this->root_path . 'memberlist.' . $this->phpEx, 'mode=searchuser&amp;form=select_user&amp;field=check_user&amp;select_single=' . $this->dlext_constants::DL_TRUE);

		$this->template->assign_vars([
			'S_DL_FORM_ACTION'		=> $this->u_action,
			'S_DL_PERM_CHECK'   	=> $this->dlext_constants::DL_TRUE,
			'S_DL_DISPLAY_PERMS'	=> $s_display_perms,

			'U_DL_FIND_USERNAME'	=> $u_user_select,
		]);
	}
}
