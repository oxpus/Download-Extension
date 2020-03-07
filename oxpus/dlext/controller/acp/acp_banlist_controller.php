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
class acp_banlist_controller implements acp_banlist_interface
{
	public $u_action;
	public $db;
	public $user;
	public $auth;
	public $phpEx;
	public $phpbb_extension_manager;
	public $phpbb_container;
	public $phpbb_path_helper;
	public $phpbb_log;

	public $config;
	public $helper;
	public $language;
	public $request;
	public $template;

	public $ext_path;
	public $ext_path_web;
	public $ext_path_ajax;

	/*
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
		$phpEx,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\auth\auth $auth,
		\phpbb\user $user
	)
	{
		$this->phpEx					= $phpEx;
		$this->phpbb_container			= $phpbb_container;
		$this->phpbb_extension_manager	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db						= $db;
		$this->phpbb_log				= $log;
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
	}

	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}

	public function handle()
	{
		$this->auth->acl($this->user->data);
		if (!$this->auth->acl_get('a_dl_banlist'))
		{
			trigger_error('DL_NO_PERMISSION', E_USER_WARNING);
		}

		include_once($this->ext_path . 'phpbb/includes/acm_init.' . $this->phpEx);

		if ($cancel)
		{
			$action = '';
		}

		$action = ($tmp_m1) ? 'edit' : $action;
		$action = ($tmp_m2) ? 'delete' : $action;
		
		if($action == 'add')
		{
			$user_ip = ($user_ip != '') ? $user_ip : '';
		
			if (!check_form_key('dl_adm_ban'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}
		
			if ($ban_id)
			{
				$sql = 'UPDATE ' . DL_BANLIST_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
					'user_id'		=> $user_id,
					'user_ip'		=> $user_ip,
					'username'		=> $username,
					'guests'		=> $guests)) . ' WHERE ban_id = ' . (int) $ban_id;
		
				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_BAN_EDIT', false, array($this->user->data['user_id'] . ' ~ ' . $username, $user_ip, $guests));
			}
			else
			{
				$sql = 'INSERT INTO ' . DL_BANLIST_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
					'user_id'		=> $user_id,
					'user_ip'		=> $user_ip,
					'username'		=> $username,
					'guests'		=> $guests));
		
				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_BAN_ADD', false, array($user_id . ' ~ ' . $username, $user_ip, $guests));
			}
		
			$this->db->sql_query($sql);
		
			$action = '';
		}
		else if($action == 'delete')
		{
			if (confirm_box(true))
			{
				$sql_ext_in = array();
		
				for ($i = 0; $i < sizeof($ban_id_ary); $i++)
				{
					$sql_ext_in[] = intval($ban_id_ary[$i]);
				}
		
				if (sizeof($sql_ext_in))
				{
					$sql = 'DELETE FROM ' . DL_BANLIST_TABLE . '
						WHERE ' . $this->db->sql_in_set('ban_id', $sql_ext_in);
					$this->db->sql_query($sql);
		
					$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_BAN_DEL', false, array(implode(', ', $sql_ext_in)));
		
					$message = $this->language->lang('DL_BANLIST_UPDATED') . adm_back_link($this->u_action);
		
					trigger_error($message);
				}
			}
			else
			{
				$s_hidden_fields = array('action' => 'delete');
		
				for ($i = 0; $i < sizeof($ban_id_ary); $i++)
				{
					$s_hidden_fields = array_merge($s_hidden_fields, array('ban_id[' . $i . ']' => intval($ban_id_ary[$i])));
				}

				confirm_box(false, 'DL_CONFIRM_DEL_BAN_VALUES', build_hidden_fields($s_hidden_fields));
			}
		}
		
		if ($action == '' || $action == 'edit')
		{
			$sql = 'SELECT b.*, u.username AS user2 FROM ' . DL_BANLIST_TABLE . ' b
				LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = b.user_id
				ORDER BY ban_id';
			$result = $this->db->sql_query($sql);
		
			while ($row = $this->db->sql_fetchrow($result))
			{
				$ban_id = $row['ban_id'];
				$user2 = $row['user2'];
				$user_id = $row['user_id'];
				$user_ip = $row['user_ip'];
				$username = $row['username'];
				$guests = ($row['guests']) ? $this->language->lang('YES') : $this->language->lang('NO');
		
				$this->template->assign_block_vars('banlist_row', array(
					'BAN_ID'		=> $ban_id,
					'USER_ID'		=> $user_id . (($user2) ? ' &raquo; ' . $user2 : ''),
					'USER_IP'		=> ($user_ip != '') ? $user_ip : '',
					'USERNAME'		=> $username,
					'GUESTS'		=> $guests)
				);
			}
		
			$s_ban_list = ($this->db->sql_affectedrows($result)) ? true : false;
		
			$this->db->sql_freeresult($result);
		
			$banlist_id = (isset($ban_id_ary[0])) ? intval($ban_id_ary[0]) : 0;
		
			$s_hidden_fields = array('action' => 'add');
		
			if ($action == 'edit' && $banlist_id)
			{
				$sql = 'SELECT * FROM ' . DL_BANLIST_TABLE . '
					WHERE ban_id = ' . (int) $banlist_id;
				$result = $this->db->sql_query($sql);
		
				while ($row = $this->db->sql_fetchrow($result))
				{
					$ban_id		= $row['ban_id'];
					$user_id	= $row['user_id'];
					$user_ip	= ($row['user_ip'] != '') ? $row['user_ip'] : '';
					$username	= $row['username'];
					$guests		= $row['guests'];
		
					$s_hidden_fields	= array_merge($s_hidden_fields, array('ban_id' => $ban_id));
				}
				$this->db->sql_freeresult($result);
			}
			else
			{
				$ban_id		= '';
				$user_id	= '';
				$user_ip	= '';
				$username	= '';
				$guests		= '';
			}
		
			add_form_key('dl_adm_ban');
		
			$this->template->assign_vars(array(
				'BANLIST_ACTION'		=> ($action == 'edit') ? $this->language->lang('EDIT') : $this->language->lang('ADD'),
		
				'DL_USER_ID'			=> $user_id,
				'DL_USER_IP'			=> $user_ip,
				'DL_USERNAME'			=> $username,
				'CHECKED_YES'			=> ($guests) ? 'checked="checked"' : '',
				'CHECKED_NO'			=> (!$guests) ? 'checked="checked"' : '',
		
				'S_BAN_LIST'			=> $s_ban_list,
				'S_HIDDEN_FIELDS'		=> build_hidden_fields($s_hidden_fields),
				'S_DOWNLOADS_ACTION'	=> $this->u_action,
		
				'U_BACK'				=> ($action) ? $this->u_action : '',
			));
		}
	}
}
