<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2002-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace oxpus\dlext\controller\acp;

use Symfony\Component\DependencyInjection\Container;

/**
* @package acp
*/
class acp_perm_check_controller implements acp_perm_check_interface
{
	public $u_action;
	public $db;
	public $user;
	public $auth;
	public $phpEx;
	public $root_path;
	public $phpbb_extension_manager;
	public $phpbb_container;
	public $phpbb_path_helper;

	public $config;
	public $helper;
	public $language;
	public $request;
	public $template;

	public $ext_path;
	public $ext_path_web;
	public $ext_path_ajax;

	protected $dlext_auth;
	protected $dlext_format;
	protected $dlext_main;

	/*
	 * @param string								$root_path
	 * @param string								$phpEx
	 * @param Container 							$phpbb_container
	 * @param \phpbb\extension\manager				$phpbb_extension_manager
	 * @param \phpbb\path_helper					$phpbb_path_helper
	 * @param \phpbb\db\driver\driver_interfacer	$db
	 * @param \phpbb\log\log_interface 				$log
	 * @param \phpbb\auth\auth						$auth
	 * @param \phpbb\user							$user
	 */
	public function __construct(
		$root_path,
		$phpEx,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\auth\auth $auth,
		\phpbb\user $user,
		$dlext_auth,
		$dlext_format,
		$dlext_main
	)
	{
		$this->root_path				= $root_path;
		$this->phpEx					= $phpEx;
		$this->phpbb_container			= $phpbb_container;
		$this->phpbb_extension_manager	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db						= $db;
		$this->auth						= $auth;
		$this->user						= $user;

		$this->config					= $this->phpbb_container->get('config');
		$this->helper					= $this->phpbb_container->get('controller.helper');
		$this->language					= $this->phpbb_container->get('language');
		$this->request					= $this->phpbb_container->get('request');
		$this->template					= $this->phpbb_container->get('template');

		$this->ext_path					= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);
		$this->ext_path_web				= $this->phpbb_path_helper->update_web_root_path($this->ext_path);
		$this->ext_path_ajax			= $this->ext_path_web . 'assets/javascript/';

		$this->dlext_auth				= $dlext_auth;
		$this->dlext_format				= $dlext_format;
		$this->dlext_main				= $dlext_main;
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$this->auth->acl($this->user->data);
		if (!$this->auth->acl_get('a_dl_perm_check'))
		{
			trigger_error('DL_NO_PERMISSION', E_USER_WARNING);
		}

		include_once($this->ext_path . 'phpbb/includes/acm_init.' . $this->phpEx);

		$s_display_perms = false;
		
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
				foreach($row as $key => $value)
				{
					$$key = $value;
				}
		
				// Check for selected user and reinit the download classes to get the right content
				$reset_user_data = false;
				if ($user_id <> $this->user->data['user_id'])
				{
					$tmp_user_data = $this->user->data;
					$this->user->data = $row;
					$this->user->data['is_registered'] = true;
					$this->user->data['session_browser'] = $tmp_user_data['session_browser'];
					$this->user->data['session_ip'] = $tmp_user_data['session_ip'];
					$this->auth->acl($this->user->data);
					$reset_user_data = true;
				}
		
				// Fetch category permissions
				$cat_perm_ary   = [];
				$dl_index       = [];
				$dl_index       = $this->dlext_main->full_index();
		
				foreach ($dl_index as $cat_id => $value)
				{
					$cat_perm_ary[$cat_id]['cat_name']		= $dl_index[$cat_id]['cat_name'];
					$cat_perm_ary[$cat_id]['auth_view']		= $this->dlext_auth->user_auth($cat_id, 'auth_view');
					$cat_perm_ary[$cat_id]['auth_dl']		= $this->dlext_auth->user_auth($cat_id, 'auth_dl');
					$cat_perm_ary[$cat_id]['auth_up']		= $this->dlext_auth->user_auth($cat_id, 'auth_up');
					$cat_perm_ary[$cat_id]['auth_mod']		= $this->dlext_auth->user_auth($cat_id, 'auth_mod');
					$cat_perm_ary[$cat_id]['comment_read']	= $this->dlext_auth->cat_auth_comment_read($cat_id);
					$cat_perm_ary[$cat_id]['comment_post']	= $this->dlext_auth->cat_auth_comment_post($cat_id);
		
					$cat_perm_ary[$cat_id]['cat_remain']    = ($this->config['dl_traffic_off']) ? true : false;
					if (($dl_index[$cat_id]['cat_traffic'] && ($dl_index[$cat_id]['cat_traffic'] - $dl_index[$cat_id]['cat_traffic_use'] <= 0)) && !$this->config['dl_traffic_off'])
					{
						if (FOUNDER_TRAFFICS_OFF == true)
						{
							$cat_perm_ary[$cat_id]['cat_remain'] = true;
						}
					}
				}
		
				// General user permissions
				$this->template->assign_vars([
					'USER_IS_ADMIN'         => $this->dlext_auth->user_admin(),
					'USER_IS_BANNED'        => $this->dlext_auth->user_banned(),
					'USER_CAN_VIEW_STATS'   => $this->dlext_auth->stats_perm(),
					'USER_CAN_SEE_TRACKER'  => $this->dlext_auth->bug_tracker(),
					'USER_HAVE_TRAFFIC'     => $this->dlext_format->dl_size($this->user->data['user_traffic']),
					'USER_HAVE_POSTS'       => $this->user->data['user_posts'] . ' / ' .$this->config['dl_posts'],
					'CHECK_USERNAME'        => $this->user->data['username'],
		
					'U_BACK'				=> $this->u_action,
				]);
		
				foreach($cat_perm_ary as $cat_id => $data_ary)
				{
					$this->template->assign_block_vars('cat_row', [
						'CAT_NAME'  => $data_ary['cat_name'],
						'CAT_VIEW'  => $data_ary['auth_view'],
						'CAT_DL'    => $data_ary['auth_dl'],
						'CAT_UP'    => $data_ary['auth_up'],
						'CAT_MOD'   => $data_ary['auth_mod'],
						'CAT_CREAD' => $data_ary['comment_read'],
						'CAT_CPOST' => $data_ary['comment_post'],
					]);
				}
		
				// Reset userdata to the real current user and reinit the download classes to get the right content
				if ($reset_user_data)
				{
					$this->user->data = $tmp_user_data;
					unset($tmp_user_data);
					$this->auth->acl($this->user->data);
				}
		
				$s_display_perms = true;
			}
		}
		
		$u_user_select = append_sid("{$this->root_path}memberlist.{$this->phpEx}", "mode=searchuser&amp;form=select_user&amp;field=check_user&amp;select_single=true");
		
		$this->template->assign_vars([
			'S_FORM_ACTION'     => $this->u_action,
			'S_DL_PERM_CHECK'   => true,
			'S_DISPLAY_PERMS'   => $s_display_perms,
		
			'U_FIND_USERNAME'   => $u_user_select,
		]);
	}
}
