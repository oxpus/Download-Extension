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
class acp_ext_blacklist_controller implements acp_ext_blacklist_interface
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
		$php_ext,
		Container $phpbb_container,
		\phpbb\extension\manager $phpbb_extension_manager,
		\phpbb\path_helper $phpbb_path_helper,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\log\log_interface $log,
		\phpbb\auth\auth $auth,
		\phpbb\user $user
	)
	{
		$this->phpEx					= $php_ext;
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
		if (!$this->auth->acl_get('a_dl_blacklist'))
		{
			trigger_error('DL_NO_PERMISSION', E_USER_WARNING);
		}

		// Define the ext path
		$ext_path	= $this->phpbb_extension_manager->get_extension_path('oxpus/dlext', true);

		include_once($this->ext_path . 'phpbb/includes/acm_init.' . $this->phpEx);

		if ($cancel)
		{
			$action = '';
		}

		if ($action == 'add')
		{
			if (!check_form_key('dl_adm_ext'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			if ($extension)
			{
				$sql = 'SELECT * FROM ' . DL_EXT_BLACKLIST . "
					WHERE extention = '" . $this->db->sql_escape($extension) . "'";
				$result = $this->db->sql_query($sql);
				$ext_exist = $this->db->sql_affectedrows($result);
				$this->db->sql_freeresult($result);

				if (!$ext_exist)
				{
					$sql = 'INSERT INTO ' . DL_EXT_BLACKLIST . ' ' . $this->db->sql_build_array('INSERT', [
						'extention' => $extension]);
					$this->db->sql_query($sql);

					// Purge the blacklist cache
					@unlink(DL_EXT_CACHE_PATH . 'data_dl_black.' . $this->phpEx);

					$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_EXT_ADD', false, [$extension]);
				}
			}

			$action = '';
		}

		if ($action == 'delete')
		{
			if (confirm_box(true))
			{
				$sql_ext_in = [];

				for ($i = 0; $i < count($extension_ary); ++$i)
				{
					$sql_ext_in[] = $extension_ary[$i];
				}

				if (!empty($sql_ext_in))
				{
					$sql = 'DELETE FROM ' . DL_EXT_BLACKLIST . '
						WHERE ' . $this->db->sql_in_set('extention', $sql_ext_in);
					$this->db->sql_query($sql);

					// Purge the blacklist cache
					@unlink(DL_EXT_CACHE_PATH . 'data_dl_black.' . $this->phpEx);

					$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DL_LOG_EXT_DEL', false, [implode(', ', $sql_ext_in)]);
				}

				$message = ((count($extension_ary) == 1) ? $this->language->lang('EXTENSION_REMOVED') : $this->language->lang('EXTENSIONS_REMOVED')) . adm_back_link($this->u_action);

				trigger_error($message);
			}
			else
			{
				$s_hidden_fields = ['action' => 'delete'];

				for ($i = 0; $i < count($extension_ary); ++$i)
				{
					$s_hidden_fields += ['extension[' . $i . ']' => $extension_ary[$i]];
				}

				$confirm_title = (count($extension_ary) == 1) ? $this->language->lang('DL_CONFIRM_DELETE_EXTENSION', $extension_ary[0]) : $this->language->lang('DL_CONFIRM_DELETE_EXTENSIONS', implode(', ', $extension_ary));

				confirm_box(false, $confirm_title, build_hidden_fields($s_hidden_fields));
			}
		}

		if ($action == '')
		{
			$sql = 'SELECT extention FROM ' . DL_EXT_BLACKLIST . '
				ORDER BY extention';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->template->assign_block_vars('extension_row', [
					'EXTENSION' => $row['extention'],
				]);
			}

			$ext_yes = ($this->db->sql_affectedrows($result)) ? true : false;

			$this->db->sql_freeresult($result);

			add_form_key('dl_adm_ext');

			$this->template->assign_vars([
				'S_EXT_YES'				=> $ext_yes,
				'S_DOWNLOADS_ACTION'	=> $this->u_action,
			]);
		}
	}
}
